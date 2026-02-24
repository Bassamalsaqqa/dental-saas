<?php

namespace App\Models;

class PatientWhatsAppModel extends TenantAwareModel
{
    protected $table = 'patient_whatsapp';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'wa_number',
        'bsuid',
        'resolution_status',
        'verified_at',
        'last_error_code',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];
}
