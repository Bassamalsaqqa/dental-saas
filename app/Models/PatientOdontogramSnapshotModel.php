<?php

namespace App\Models;

class PatientOdontogramSnapshotModel extends TenantAwareModel
{
    protected $table = 'patient_odontogram_snapshots';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'source_type',
        'source_id',
        'label',
        'snapshot_json',
        'snapshot_svg',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
