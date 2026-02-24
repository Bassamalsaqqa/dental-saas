<?php

namespace App\Models;

class WhatsAppOptoutModel extends TenantAwareModel
{
    protected $table = 'whatsapp_optouts';
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
        'source',
        'created_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $beforeInsert = ['setClinicId'];
}
