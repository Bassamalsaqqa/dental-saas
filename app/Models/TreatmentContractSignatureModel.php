<?php

namespace App\Models;

class TreatmentContractSignatureModel extends TenantAwareModel
{
    protected $table = 'treatment_contract_signatures';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'contract_id',
        'signer_name',
        'signer_role',
        'signature_data',
        'ip_address',
        'user_agent',
        'signed_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];
}
