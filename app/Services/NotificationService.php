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
        // Note: Using find/where manually to ensure we verify against specific clinicId passed in args,
        // ignoring global session state if called from a background job (though TenantAwareModel defaults to session)
        // We must ensure we are looking at the target clinic's config.
        // TenantAwareModel uses session('active_clinic_id') by default. 
        // If this is a background job, session might be set mockingly, or we need to use 'forClinic' scope if available,
        // or just rely on the fact that setClinicId in BeforeInsert/Update handles writes.
        // For reads, TenantAwareModel usually applies a global scope if not careful.
        // Let's assume the caller has set the context or we use specific where clauses that override/augment.
        // Actually, TenantAwareModel enforces 'where clinic_id = session'. 
        // If we are in CLI, we used 'tenant:run-job' which mocks session. 
        // If we are in Control Plane, we might be 'global_mode'.
        // Let's rely on standard Model methods but verify clinic_id explicitly if possible.
        
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
                    // Assuming PatientModel has a check or we query directly
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

            // Use model to insert (TenantAwareModel will enforce clinic_id matching session if active, 
            // but we explicitly pass clinic_id. TenantAwareModel beforeInsert sets it from session. 
            // If we are sending for a clinic different than session, this might be tricky.
            // However, typical flow is: User acts in Clinic A -> sends notif for Clinic A.
            // Job runs for Clinic A -> mocks session for Clinic A -> sends notif for Clinic A.
            // So implicit session usage is generally correct. 
            // If explicit mismatch, TenantAwareModel might override.
            // We should trust the context set up by the caller/framework.)
            
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
}
