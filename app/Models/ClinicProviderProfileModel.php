<?php

namespace App\Models;

class ClinicProviderProfileModel extends TenantAwareModel
{
    protected $table = 'clinic_provider_profiles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'user_id',
        'is_bookable',
        'is_clinician',
        'weekly_hours_json',
        'exceptions_json',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];

    /**
     * Find profile for a user in a specific clinic.
     */
    public function findByClinicAndUser(int $clinicId, int $userId): ?array
    {
        return $this->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('user_id', $userId)
            ->first();
    }
}
