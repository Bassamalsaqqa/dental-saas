<?php

namespace App\Services;

use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationModel;
use App\Models\ClinicUserModel;
use App\Models\PatientModel;
use App\Services\PlanGuard;
use App\Services\DispatchService;

class NotificationService
{
    protected $channelModel;
    protected $notificationModel;
    protected $clinicUserModel;
    protected $patientModel;
    protected $planGuard;

    public function __construct()
    {
        $this->channelModel = new ClinicNotificationChannelModel();
        $this->notificationModel = new NotificationModel();
        $this->clinicUserModel = new ClinicUserModel();
        $this->patientModel = new PatientModel();
        $this->planGuard = new PlanGuard();
    }

        /**
         * Retry a failed/blocked notification (Governance Layer)
         * 
         * @param int $notificationId
         * @param int $actorUserId
         * @param int $clinicId Context check
         * @return array Result of enqueue
         * @throws \RuntimeException
         */
        public function retryNotification(int $notificationId, int $actorUserId, int $clinicId): array
        {
            $original = $this->notificationModel->find($notificationId);
    
            if (!$original) {
                throw new \RuntimeException("Notification not found.");
            }
    
            // Tenant Context Check
            if ($original['clinic_id'] != $clinicId) {
                throw new \RuntimeException("Access Denied: Cross-clinic retry attempt.");
            }
    
            // Status Check (Allowed: failed, blocked)
            if (!in_array($original['status'], ['failed', 'blocked'])) {
                throw new \RuntimeException("Cannot retry notification with status: " . $original['status']);
            }
    
            // Prepare Recipients
            $recipients = [[
                'type' => $original['recipient_type'],
                'id' => $original['recipient_id'],
                'address' => $original['recipient_address']
            ]];
    
            // Prepare Payload
            $payload = json_decode($original['payload_json'], true);
    
            // Enqueue (This will create new row, validate again, and set status)
            // We need to pass parent_notification_id. 
            // I need to modify enqueue to accept parent_notification_id first?
            // Or I can update the row after insert?
            // Enqueue is strict. Let's update enqueue signature to accept optional parentId.
            
            return $this->enqueue(
                $clinicId,
                $original['channel_type'],
                $recipients,
                $payload,
                $actorUserId,
                null, // jobAuditId
                $notificationId // parentNotificationId
            );
        }
    
        /**
         * Enqueue a notification (Governance Layer - No Sending)
         * 
         * @param int $clinicId
         * @param string $channelType 'email'|'sms'|'whatsapp'
         * @param array $recipients Array of ['type' => 'user|patient|external', 'id' => int, 'address' => string]
         * @param array $payload Content payload
         * @param int|null $initiatedByUserId
         * @param int|null $jobAuditId
         * @param int|null $parentNotificationId
         * @return array Summary of operations
         */
        public function enqueue(
            int $clinicId,
            string $channelType,
            array $recipients,
            array $payload,
            ?int $initiatedByUserId = null,
            ?int $jobAuditId = null,
            ?int $parentNotificationId = null
        ): array {        $result = [
            'total' => 0,
            'pending' => 0,
            'blocked' => 0,
            'failed' => 0,
            'notification_ids' => []
        ];

        // 1. Fail Closed / Basic Validation
        if (!$clinicId) {
            throw new \InvalidArgumentException("NotificationService: clinicId is required.");
        }

        // Plan Enforcement (Feature)
        $this->planGuard->assertFeature($clinicId, "notifications.{$channelType}.enabled");
        
        // Plan Enforcement (Quota)
        // We verify quota here but increment only? Or increment here?
        // "optional quota: assertQuota($clinicId,'notifications_email', count(recipients))"
        // If we increment here, we count "attempts". If we increment at dispatch, we count "sends".
        // Usually, SaaS charges for *attempts* or *enqueued* items if they consume storage/resources.
        // Let's increment here for simplicity and immediate feedback.
        // Note: assertQuota increments.
        $metricKey = "notifications_{$channelType}";
        $this->planGuard->assertQuota($clinicId, $metricKey, count($recipients));

        // 2. Load Channel Registry
        $channel = $this->channelModel->withoutTenantScope()->where('clinic_id', $clinicId)
                                      ->where('channel_type', $channelType)
                                      ->first();

        $channelStatus = 'active';
        $blockReason = null;

        if (!$channel) {
            $channelStatus = 'blocked';
            $blockReason = 'CHANNEL_NOT_REGISTERED';
        } elseif (!$channel['enabled_by_superadmin']) {
            $channelStatus = 'blocked';
            $blockReason = 'CHANNEL_DISABLED_BY_SUPERADMIN';
        } elseif (!$channel['configured_by_clinic']) {
            $channelStatus = 'blocked';
            $blockReason = 'CHANNEL_NOT_CONFIGURED';
        } elseif (!$channel['validated']) {
            $channelStatus = 'blocked';
            $blockReason = 'CHANNEL_NOT_VALIDATED';
        }

        // 3. Process Recipients
        foreach ($recipients as $recipient) {
            $result['total']++;
            $status = $channelStatus === 'blocked' ? 'blocked' : 'pending';
            $reason = $blockReason;

            // Recipient Ownership Validation (only if channel is ostensibly active)
            if ($status === 'pending') {
                $type = $recipient['type'] ?? 'unknown';
                $id = $recipient['id'] ?? null;

                if ($type === 'user') {
                    // Check if user belongs to clinic
                    if (!$id || !$this->clinicUserModel->isUserInClinic($clinicId, $id)) {
                        $status = 'blocked';
                        $reason = 'RECIPIENT_USER_NOT_IN_CLINIC';
                    }
                } elseif ($type === 'patient') {
                    // Check if patient belongs to clinic
                    $patient = (is_int($id) || is_string($id)) && preg_match('/^[1-9][0-9]*$/D', (string) $id)
                        ? $this->patientModel->withoutTenantScope()->where('clinic_id', $clinicId)->find($id) : null;
                    if (!$patient) {
                        $status = 'blocked';
                        $reason = 'RECIPIENT_PATIENT_NOT_FOUND';
                    }
                } elseif ($type === 'external') {
                    $status = 'blocked';
                    $reason = 'EXTERNAL_RECIPIENTS_DISABLED';
                } else {
                    $status = 'failed';
                    $reason = 'INVALID_RECIPIENT_TYPE';
                }
            }

            // 4. Ledger Write
            $ledgerData = [
                'clinic_id' => $clinicId,
                'channel_type' => $channelType,
                'recipient_type' => $recipient['type'] ?? 'unknown',
                'recipient_id' => $recipient['id'] ?? null,
                'recipient_address' => $recipient['address'] ?? null,
                'payload_json' => json_encode($payload),
                'status' => $status,
                'failure_reason' => $reason,
                'initiated_by_user_id' => $initiatedByUserId,
                'job_audit_id' => $jobAuditId,
                'parent_notification_id' => $parentNotificationId
            ];

            $notificationId = $this->notificationModel->insert($ledgerData);
            
            if ($notificationId) {
                $result['notification_ids'][] = $notificationId;
                $result[$status]++;
            } else {
                $result['failed']++;
                log_message('error', 'Failed to write notification ledger: ' . json_encode($this->notificationModel->errors()));
            }
        }

        return $result;
    }

    /**
     * Dispatch pending email notifications for a specific clinic via SMTP.
     * (P5-09b Implementation)
     * 
     * @param int $clinicId
     * @return array Summary of dispatch results
     */
    public function dispatchPendingEmails(int $clinicId): array
    {
        $dispatcher = new DispatchService();
        $summary = $dispatcher->dispatchPendingForClinic($clinicId, 'email');

        return [
            'processed' => $summary['processed'],
            'sent' => $summary['sent'],
            'failed' => $summary['failed'],
            'blocked' => $summary['blocked'],
        ];
    }
}
