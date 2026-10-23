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

    /** Retained bytes once per exact clinic/path, including soft-deleted evidence.
     * Duplicate print metadata is not additional storage. Unknown paths retain a
     * per-row charge; conflicting sizes conservatively use the largest value.
     */
    public function getTotalSizeByClinic(int $clinicId): int
    {
        $total = $this->retainedPathBytes($clinicId, $this->table, 'file_path');
        if ($this->db->tableExists('attachment_variants')) {
            $total += $this->retainedPathBytes($clinicId, 'attachment_variants', 'relative_path');
        }
        return $total;
    }

    private function retainedPathBytes(int $clinicId, string $table, string $pathColumn): int
    {
        $table = $this->db->escapeIdentifiers($this->db->prefixTable($table));
        $path = $this->db->escapeIdentifiers($pathColumn);
        // BINARY preserves distinct Linux filenames despite case-insensitive DB collation.
        // Never collapse missing paths, or distinct files that happen to share a size.
        $row = $this->db->query(
            "SELECT COALESCE(SUM(retained_size), 0) AS total_size FROM (
                SELECT MAX(file_size) AS retained_size FROM {$table} WHERE clinic_id = ?
                GROUP BY BINARY {$path}, CASE WHEN {$path} IS NULL OR OCTET_LENGTH({$path}) = 0 THEN id ELSE 0 END
            ) retained_files", [$clinicId]
        )->getRowArray();
        return (int) ($row['total_size'] ?? 0);
    }
}
