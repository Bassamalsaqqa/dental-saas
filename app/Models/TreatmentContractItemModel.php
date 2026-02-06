<?php

namespace App\Models;

class TreatmentContractItemModel extends TenantAwareModel
{
    protected $table = 'treatment_contract_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'contract_id',
        'procedure_code',
        'tooth_no',
        'description',
        'qty',
        'price'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $beforeInsert = ['setClinicId'];
}
