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
            ->select('notification_attempts.*, clinics.name AS clinic_name')
            ->join('clinics', 'clinics.id = notification_attempts.clinic_id', 'left')
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

        return [
            'total' => (int) $total,
            'failed' => (int) $failed,
            'sentish' => (int) $sent,
        ];
    }
}
