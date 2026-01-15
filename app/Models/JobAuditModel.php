<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * JobAuditModel
 * 
 * Tracks background job executions for governance and troubleshooting.
 * This model does NOT extend TenantAwareModel because it must log 
 * fail-fast events where clinic_id is missing.
 */
class JobAuditModel extends Model
{
    protected $table = 'job_audits';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'job_name',
        'clinic_id',
        'status',
        'started_at',
        'finished_at',
        'error_message',
        'attachment_ids',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = ''; // Not used

    /**
     * Helper to start an audit entry
     */
    public function startAudit(string $jobName, ?int $clinicId = null)
    {
        $id = $this->insert([
            'job_name'   => $jobName,
            'clinic_id'  => $clinicId,
            'status'     => 'fail', // Default to fail until finished successfully
            'started_at' => date('Y-m-d H:i:s'),
        ]);
        return $id;
    }

    /**
     * Helper to finish an audit entry
     */
    public function finishAudit(int $id, string $status, ?string $errorMessage = null, ?array $attachmentIds = null)
    {
        return $this->update($id, [
            'status'         => $status,
            'finished_at'    => date('Y-m-d H:i:s'),
            'error_message'  => $errorMessage,
            'attachment_ids' => $attachmentIds ? json_encode($attachmentIds) : null,
        ]);
    }
}
