<?php

namespace App\Services;

use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationAttemptModel;
use App\Models\NotificationModel;
use App\Services\NotificationDrivers\EmailDriver;
use App\Services\NotificationDrivers\WhatsAppDriver;
use CodeIgniter\Database\BaseConnection;

class DispatchService
{
    protected BaseConnection $db;
    protected NotificationModel $notificationModel;
    protected NotificationAttemptModel $attemptModel;
    protected ClinicNotificationChannelModel $channelModel;
    protected PlanGuard $planGuard;
    protected EmailDriver $emailDriver;
    protected WhatsAppDriver $whatsAppDriver;
    protected int $staleLockMinutes;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->notificationModel = new NotificationModel();
        $this->attemptModel = new NotificationAttemptModel();
        $this->channelModel = new ClinicNotificationChannelModel();
        $this->planGuard = new PlanGuard();
        $this->emailDriver = new EmailDriver();
        $this->whatsAppDriver = new WhatsAppDriver();
        $this->staleLockMinutes = $this->resolveStaleLockMinutes();
    }

    public function dispatchPendingForClinic(int $clinicId, ?string $channelType = null, int $limit = 100, ?string $dispatcherId = null): array
    {
        if ($clinicId <= 0) {
            throw new \InvalidArgumentException('DispatchService: clinicId is required.');
        }

        $dispatcherId = $dispatcherId ?: ('dispatch:' . gethostname() . ':' . getmypid());
        $limit = max(1, min($limit, 500));

        $summary = [
            'clinic_id' => $clinicId,
            'dispatcher_id' => $dispatcherId,
            'processed' => 0,
            'claimed' => 0,
            'skipped_claim' => 0,
            'sent' => 0,
            'failed' => 0,
            'blocked' => 0,
            'attempts_created' => 0,
            'channels' => [],
        ];

        $query = $this->notificationModel
            ->where('clinic_id', $clinicId)
            ->groupStart()
                ->where('status', 'pending')
                ->orGroupStart()
                    ->where('status', 'processing')
                    ->where(
                        'locked_at < DATE_SUB(NOW(), INTERVAL ' . $this->staleLockMinutes . ' MINUTE)',
                        null,
                        false
                    )
                ->groupEnd()
            ->groupEnd();

        if ($channelType) {
            $query->where('channel_type', $channelType);
        }

        $candidates = $query
            ->orderBy('id', 'ASC')
            ->findAll($limit);

        if (empty($candidates)) {
            return $summary;
        }

        $channelContexts = [];

        foreach ($candidates as $note) {
            $summary['processed']++;
            $noteChannel = (string) ($note['channel_type'] ?? '');

            if (!isset($summary['channels'][$noteChannel])) {
                $summary['channels'][$noteChannel] = [
                    'processed' => 0,
                    'sent' => 0,
                    'failed' => 0,
                    'blocked' => 0,
                ];
            }
            $summary['channels'][$noteChannel]['processed']++;

            if (!$this->claimNotification((int) $note['id'], $clinicId, $dispatcherId, $noteChannel)) {
                $summary['skipped_claim']++;
                continue;
            }

            $summary['claimed']++;

            $finalized = false;

            try {
                $context = $channelContexts[$noteChannel] ?? $this->loadChannelContext($clinicId, $noteChannel);
                $channelContexts[$noteChannel] = $context;

                if (!$context['feature_enabled']) {
                    $this->finalizeNotification((int) $note['id'], 'blocked', 'PLAN_FEATURE_DISABLED_AT_DISPATCH');
                    $finalized = true;
                    $summary['blocked']++;
                    $summary['channels'][$noteChannel]['blocked']++;
                    continue;
                }

                if (!$context['channel_ready']) {
                    $this->finalizeNotification((int) $note['id'], 'failed', 'CHANNEL_NOT_READY_AT_DISPATCH');
                    $finalized = true;
                    $summary['failed']++;
                    $summary['channels'][$noteChannel]['failed']++;
                    continue;
                }

                $attemptId = $this->createAttemptRecord($note, $clinicId);
                if ($attemptId) {
                    $summary['attempts_created']++;
                }

                $driverResult = $this->dispatchViaDriver($note, $clinicId, $context);

                if ($attemptId) {
                    $this->updateAttemptRecord($attemptId, $driverResult);
                }

                $this->finalizeNotification(
                    (int) $note['id'],
                    (string) ($driverResult['notification_status'] ?? 'failed'),
                    $driverResult['failure_reason'] ?? null
                );
                $finalized = true;

                $bucket = $driverResult['notification_status'] ?? 'failed';
                if (!in_array($bucket, ['sent', 'failed', 'blocked'], true)) {
                    $bucket = 'failed';
                }

                $summary[$bucket]++;
                $summary['channels'][$noteChannel][$bucket]++;
            } catch (\Throwable $e) {
                log_message('error', 'DispatchService unexpected failure for notification_id={notification_id} clinic_id={clinic_id}: {message}', [
                    'notification_id' => (int) ($note['id'] ?? 0),
                    'clinic_id' => $clinicId,
                    'message' => substr($e->getMessage(), 0, 255),
                ]);

                if (!$finalized) {
                    try {
                        $this->finalizeNotification((int) $note['id'], 'failed', 'DISPATCH_RUNTIME_EXCEPTION');
                    } catch (\Throwable $finalizeError) {
                        log_message('critical', 'DispatchService failed to release lock for notification_id={notification_id}: {message}', [
                            'notification_id' => (int) ($note['id'] ?? 0),
                            'message' => substr($finalizeError->getMessage(), 0, 255),
                        ]);
                    }
                }

                $summary['failed']++;
                $summary['channels'][$noteChannel]['failed']++;
            }
        }

        return $summary;
    }

    /**
     * Atomic claim using a single UPDATE ... WHERE statement.
     * If affectedRows() === 0, another dispatcher claimed it or it is no longer eligible.
     */
    public function claimNotification(int $notificationId, int $clinicId, string $dispatcherId, string $channelType): bool
    {
        $builder = $this->db->table('notifications');

        $builder->set('status', 'processing');
        $builder->set('locked_at', 'NOW()', false);
        $builder->set('locked_by', $dispatcherId);
        $builder->where('id', $notificationId);
        $builder->where('clinic_id', $clinicId);
        $builder->where('channel_type', $channelType);
        $builder->groupStart()
            ->groupStart()
                ->where('status', 'pending')
                ->groupStart()
                    ->where('locked_at', null)
                    ->orWhere(
                        'locked_at < DATE_SUB(NOW(), INTERVAL ' . $this->staleLockMinutes . ' MINUTE)',
                        null,
                        false
                    )
                ->groupEnd()
            ->groupEnd()
            ->orGroupStart()
                ->where('status', 'processing')
                ->where(
                    'locked_at < DATE_SUB(NOW(), INTERVAL ' . $this->staleLockMinutes . ' MINUTE)',
                    null,
                    false
                )
            ->groupEnd()
        ->groupEnd();

        $builder->update();

        return $this->db->affectedRows() === 1;
    }

    private function loadChannelContext(int $clinicId, string $channelType): array
    {
        $featureEnabled = true;
        try {
            $this->planGuard->assertFeature($clinicId, "notifications.{$channelType}.enabled");
        } catch (\Throwable $e) {
            $featureEnabled = false;
        }

        $channelRow = $this->channelModel->where('clinic_id', $clinicId)
            ->where('channel_type', $channelType)
            ->first();

        $config = null;
        if ($channelType === 'email') {
            $config = $this->channelModel->getConfig($clinicId, 'email');
        }

        $channelReady = false;
        if ($channelRow) {
            $channelReady = (bool) (
                ($channelRow['enabled_by_superadmin'] ?? 0)
                && ($channelRow['configured_by_clinic'] ?? 0)
                && ($channelRow['validated'] ?? 0)
            );
        }

        if ($channelType === 'email') {
            $channelReady = $channelReady && is_array($config);
        }

        return [
            'channel_type' => $channelType,
            'feature_enabled' => $featureEnabled,
            'channel_ready' => $channelReady,
            'channel' => $channelRow,
            'config' => $config,
        ];
    }

    private function createAttemptRecord(array $note, int $clinicId): ?int
    {
        $idempotencyKey = $this->generateIdempotencyKey((int) $note['id'], $clinicId, (string) $note['channel_type']);

        $attemptId = $this->attemptModel->insert([
            'clinic_id' => $clinicId,
            'notification_id' => (int) $note['id'],
            'channel_type' => (string) $note['channel_type'],
            'status' => 'sending',
            'idempotency_key' => $idempotencyKey,
            'retry_count' => 0,
            'max_retries' => 3,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$attemptId) {
            log_message('error', 'Failed to create notification_attempt for notification_id={id}: {errors}', [
                'id' => $note['id'] ?? 0,
                'errors' => json_encode($this->attemptModel->errors()),
            ]);
            return null;
        }

        return (int) $attemptId;
    }

    private function updateAttemptRecord(int $attemptId, array $driverResult): void
    {
        $status = (string) ($driverResult['attempt_status'] ?? 'failed');
        $data = [
            'status' => $status,
            'provider_message_id' => $driverResult['provider_message_id'] ?? null,
            'error_code' => $driverResult['error_code'] ?? null,
            'error_message' => $driverResult['error_message'] ?? null,
        ];

        if (!empty($driverResult['retryable']) && !empty($driverResult['next_retry_at'])) {
            $data['next_retry_at'] = $driverResult['next_retry_at'];
            $data['status'] = $driverResult['attempt_status'] ?? 'failed';
        }

        $this->attemptModel->update($attemptId, $data);
    }

    private function dispatchViaDriver(array $note, int $clinicId, array $context): array
    {
        $channelType = (string) ($note['channel_type'] ?? '');

        if ($channelType === 'email') {
            return $this->emailDriver->send($note, $clinicId, $context['config'] ?? []);
        }

        if ($channelType === 'whatsapp') {
            return $this->whatsAppDriver->send($note, $clinicId, $context);
        }

        return [
            'notification_status' => 'failed',
            'failure_reason' => 'UNSUPPORTED_CHANNEL_AT_DISPATCH',
            'attempt_status' => 'failed_permanent',
            'error_code' => 'UNSUPPORTED_CHANNEL',
            'error_message' => 'No dispatcher driver exists for channel: ' . $channelType,
            'retryable' => false,
        ];
    }

    private function finalizeNotification(int $notificationId, string $status, ?string $failureReason): void
    {
        $data = [
            'status' => $status,
            'locked_at' => null,
            'locked_by' => null,
        ];

        if ($failureReason !== null) {
            $data['failure_reason'] = substr($failureReason, 0, 100);
        } elseif ($status === 'sent') {
            $data['failure_reason'] = null;
        }

        $this->notificationModel->update($notificationId, $data);
    }

    private function generateIdempotencyKey(int $notificationId, int $clinicId, string $channelType): string
    {
        try {
            $rand = bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            $rand = uniqid('', true);
        }

        return substr(hash('sha256', $clinicId . '|' . $notificationId . '|' . $channelType . '|' . microtime(true) . '|' . $rand), 0, 64);
    }

    private function resolveStaleLockMinutes(): int
    {
        $value = env('notifications.dispatch_lock_stale_minutes');
        $minutes = is_numeric($value) ? (int) $value : 5;
        return max(1, min($minutes, 60));
    }
}
