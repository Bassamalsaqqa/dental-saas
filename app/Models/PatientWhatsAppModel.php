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

    public function findByPatient(int $clinicId, int $patientId): ?array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->first();
    }

    public function upsertForPatient(int $clinicId, int $patientId, array $data)
    {
        $existing = $this->findByPatient($clinicId, $patientId);

        $payload = array_intersect_key($data, array_flip($this->allowedFields));
        $payload['clinic_id'] = $clinicId;
        $payload['patient_id'] = $patientId;

        if ($existing) {
            return $this->update($existing['id'], $payload);
        }

        return $this->insert($payload);
    }
}
