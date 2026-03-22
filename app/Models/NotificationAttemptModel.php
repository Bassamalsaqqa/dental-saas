<?php

namespace App\Models;

class NotificationAttemptModel extends TenantAwareModel
{
    protected $table = 'notification_attempts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'notification_id',
        'channel_type',
        'status',
        'provider_message_id',
        'error_code',
        'error_message',
        'idempotency_key',
        'retry_count',
        'max_retries',
        'next_retry_at',
        'created_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $beforeInsert = ['setClinicId'];

    public function getControlPlaneRecentWhatsAppAttempts(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        return $this->withoutTenantScope()
            ->select("notification_attempts.*, clinics.name AS clinic_name, notifications.status AS notification_status, notifications.failure_reason AS notification_failure_reason, notifications.recipient_type, notifications.recipient_id, notifications.recipient_address, notifications.payload_json, notifications.initiated_by_user_id, patients.first_name AS patient_first_name, patients.last_name AS patient_last_name")
            ->join('clinics', 'clinics.id = notification_attempts.clinic_id', 'left')
            ->join('notifications', 'notifications.id = notification_attempts.notification_id', 'left')
            ->join('patients', "patients.id = notifications.recipient_id AND notifications.recipient_type = 'patient' AND patients.clinic_id = notifications.clinic_id", 'left')
            ->where('notification_attempts.channel_type', 'whatsapp')
            ->orderBy('notification_attempts.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getControlPlaneWhatsAppCountsSince(string $since): array
    {
        $total = $this->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->where('created_at >=', $since)
            ->countAllResults();

        $failed = $this->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->where('created_at >=', $since)
            ->whereIn('status', ['failed', 'failed_permanent', 'rate_limited'])
            ->countAllResults();

        $sent = $this->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->where('created_at >=', $since)
            ->whereIn('status', ['sent', 'delivered', 'read'])
            ->countAllResults();

        $read = $this->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->where('created_at >=', $since)
            ->where('status', 'read')
            ->countAllResults();

        $sessionRestricted = $this->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->where('created_at >=', $since)
            ->where('error_code', '131047')
            ->countAllResults();

        return [
            'total' => (int) $total,
            'failed' => (int) $failed,
            'sentish' => (int) $sent,
            'read' => (int) $read,
            'session_restricted' => (int) $sessionRestricted,
        ];
    }
}
