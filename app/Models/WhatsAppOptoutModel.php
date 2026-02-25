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

    public function getControlPlaneRecentOptouts(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        return $this->withoutTenantScope()
            ->select('whatsapp_optouts.*, clinics.name AS clinic_name')
            ->join('clinics', 'clinics.id = whatsapp_optouts.clinic_id', 'left')
            ->orderBy('whatsapp_optouts.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function countSince(string $since): int
    {
        return (int) $this->withoutTenantScope()
            ->where('created_at >=', $since)
            ->countAllResults();
    }
}
