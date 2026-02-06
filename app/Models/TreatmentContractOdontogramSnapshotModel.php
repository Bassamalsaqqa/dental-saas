<?php

namespace App\Models;

class TreatmentContractOdontogramSnapshotModel extends TenantAwareModel
{
    protected $table = 'treatment_contract_odontogram_snapshots';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'contract_id',
        'svg_snapshot',
        'data_snapshot_json'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];
}
