<?php

namespace App\Models;

use CodeIgniter\Model;

class FileAttachmentModel extends Model
{
    use \App\Traits\TenantTrait;

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
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $beforeInsert = ['setClinicId'];
}
