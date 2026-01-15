<?php

namespace App\Services;

use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationModel;
use App\Models\ClinicUserModel;
use App\Models\PatientModel;

class NotificationService
{
    protected $channelModel;
    protected $notificationModel;
    protected $clinicUserModel;
    protected $patientModel;

    public function __construct()
    {
        $this->channelModel = new ClinicNotificationChannelModel();
        $this->notificationModel = new NotificationModel();
        $this->clinicUserModel = new ClinicUserModel();
        $this->patientModel = new PatientModel();
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
     * @return array Summary of operations
     */
    public function enqueue(
        int $clinicId,
        string $channelType,
        array $recipients,
        array $payload,
        ?int $initiatedByUserId = null,
        ?int $jobAuditId = null
    ): array {
        $result = [
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

        // 2. Load Channel Registry
        $channel = $this->channelModel->where('clinic_id', $clinicId)
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
                    $patient = $this->patientModel->findByClinic($clinicId, $id);
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
                'job_audit_id' => $jobAuditId
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
        if (!$clinicId) {
            throw new \InvalidArgumentException("NotificationService: clinicId is required for dispatch.");
        }

        $result = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'blocked' => 0
        ];

        // 1. Load & Validate Channel Config
        $config = $this->channelModel->getConfig($clinicId, 'email');
        $channel = $this->channelModel->where('clinic_id', $clinicId)
                                      ->where('channel_type', 'email')
                                      ->first();

        $isReady = $config && 
                   ($channel['enabled_by_superadmin'] ?? 0) && 
                   ($channel['configured_by_clinic'] ?? 0) && 
                   ($channel['validated'] ?? 0);

        // 2. Fetch Pending Notifications
        $pending = $this->notificationModel->where('clinic_id', $clinicId)
                                           ->where('channel_type', 'email')
                                           ->where('status', 'pending')
                                           ->findAll();

        if (empty($pending)) {
            return $result;
        }

        // If channel not ready, fail-close all pending
        if (!$isReady) {
            foreach ($pending as $note) {
                $this->notificationModel->update($note['id'], [
                    'status' => 'failed',
                    'failure_reason' => 'CHANNEL_NOT_READY_AT_DISPATCH'
                ]);
                $result['failed']++;
            }
            return $result;
        }

        // 3. Process Each Notification
        foreach ($pending as $note) {
            $result['processed']++;
            $recipientEmail = null;
            $payload = json_decode($note['payload_json'], true);

            // Resolve Recipient Email
            if ($note['recipient_type'] === 'user') {
                // Get user email
                $user = model('UserModel')->find($note['recipient_id']);
                // Verify user is still in clinic (double check safety)
                if ($user && $this->clinicUserModel->isUserInClinic($clinicId, $user['id'])) {
                    $recipientEmail = $user['email'];
                }
            } elseif ($note['recipient_type'] === 'patient') {
                // Get patient email
                $patient = $this->patientModel->findByClinic($clinicId, $note['recipient_id']);
                if ($patient) {
                    $recipientEmail = $patient['email'];
                }
            }

            // Block if email missing
            if (empty($recipientEmail)) {
                $this->notificationModel->update($note['id'], [
                    'status' => 'blocked',
                    'failure_reason' => ($note['recipient_type'] === 'patient' ? 'PATIENT_EMAIL_MISSING' : 'USER_EMAIL_MISSING')
                ]);
                $result['blocked']++;
                continue;
            }

            // Prepare Email
            $emailService = \Config\Services::email();
            $emailService->initialize([
                'protocol' => 'smtp',
                'SMTPHost' => $config['smtp_host'] ?? '',
                'SMTPUser' => $config['smtp_user'] ?? '',
                'SMTPPass' => $config['smtp_pass'] ?? '',
                'SMTPPort' => (int)($config['smtp_port'] ?? 587),
                'SMTPCrypto' => $config['smtp_crypto'] ?? 'tls',
                'mailType' => 'html',
                'charset'  => 'utf-8',
                'newline'  => "\r\n"
            ]);

            $fromEmail = $config['smtp_from_email'] ?? $config['smtp_user']; // Fallback to user if from not set
            $fromName = $config['smtp_from_name'] ?? 'Clinic Notification';

            if (empty($fromEmail)) {
                 $this->notificationModel->update($note['id'], [
                    'status' => 'failed',
                    'failure_reason' => 'SMTP_FROM_ADDRESS_MISSING'
                ]);
                $result['failed']++;
                continue;
            }

            $emailService->setFrom($fromEmail, $fromName);
            $emailService->setTo($recipientEmail);
            $emailService->setSubject($payload['subject'] ?? 'Notification');
            $emailService->setMessage($payload['body'] ?? ($payload['message'] ?? ''));

            // Send
            try {
                if ($emailService->send()) {
                    $this->notificationModel->update($note['id'], [
                        'status' => 'sent'
                    ]);
                    $result['sent']++;
                } else {
                    $debugger = $emailService->printDebugger(['headers']);
                    // Sanitize debugger output slightly
                    $errorMsg = substr(strip_tags($debugger), 0, 255); 
                    
                    $this->notificationModel->update($note['id'], [
                        'status' => 'failed',
                        'failure_reason' => 'SMTP_SEND_FAILED: ' . $errorMsg
                    ]);
                    $result['failed']++;
                }
            } catch (\Exception $e) {
                $this->notificationModel->update($note['id'], [
                    'status' => 'failed',
                    'failure_reason' => 'SMTP_EXCEPTION: ' . substr($e->getMessage(), 0, 200)
                ]);
                $result['failed']++;
            }
        }

        return $result;
    }
}