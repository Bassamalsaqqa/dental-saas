<?php

namespace App\Models;

class TreatmentContractModel extends TenantAwareModel
{
    protected $table = 'treatment_contracts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'status',
        'currency',
        'total_cost',
        'notes',
        'terms_conditions',
        'payment_terms',
        'duration_plan',
        'cost_details',
        'created_by',
        'signed_attachment_id',
        'signed_at',
        'signature_hash',
        'signature_meta_json'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $beforeInsert = ['setClinicId'];
}
