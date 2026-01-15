<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends TenantAwareModel
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'channel_type',
        'recipient_type',
        'recipient_id',
        'recipient_address',
        'payload_json',
        'status',
        'failure_reason',
        'initiated_by_user_id',
        'job_audit_id',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = ''; // No updated_at in schema

    protected $beforeInsert = ['setClinicId'];
    
    // Override to ensure created_at is set if not provided (though Model usually handles this if useTimestamps is true)
    // But we only have created_at.
    
    public function createLedgerEntry(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }
}
