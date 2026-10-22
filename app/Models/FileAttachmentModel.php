<?php

namespace App\Models;

use CodeIgniter\Model;

class FileAttachmentModel extends TenantAwareModel
{
    protected $table = 'file_attachments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'file_name',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'entity_type',
        'entity_id',
        'purpose',
        'category',
        'notes',
        'file_hash',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $beforeInsert = ['setClinicId'];

    /**
     * Soft-delete previous attachments for same entity and purpose
     */
    public function supersedePrevious($clinicId, $entityType, $entityId, $purpose)
    {
        return $this->where('clinic_id', $clinicId)
                    ->where('entity_type', $entityType)
                    ->where('entity_id', $entityId)
                    ->where('purpose', $purpose)
                    ->delete();
    }

    /**
     * Permanently remove a soft-deleted record
     */
    public function deletePermanently($id)
    {
        throw new \DomainException('Permanent attachment deletion requires an approved retention and purge policy.');
    }

    /**
     * Sum retained attachment metadata bytes, including soft-deleted files.
     * This is quota accounting, not a filesystem/orphan inventory.
     */
    public function getTotalSizeByClinic(int $clinicId): int
    {
        $row = $this->db->table($this->table)
            ->selectSum('file_size', 'total_size')
            ->where('clinic_id', $clinicId)
            ->get()->getRowArray();

        $total = (int) ($row['total_size'] ?? 0);
        if ($this->db->tableExists('attachment_variants')) {
            $variants = $this->db->table('attachment_variants')->selectSum('file_size', 'total_size')
                ->where('clinic_id', $clinicId)->get()->getRowArray();
            $total += (int) ($variants['total_size'] ?? 0);
        }
        return $total;
    }
}
