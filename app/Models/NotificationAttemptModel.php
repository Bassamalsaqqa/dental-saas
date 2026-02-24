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
}
