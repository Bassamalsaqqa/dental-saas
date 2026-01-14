<?php

namespace App\Models;

use CodeIgniter\Model;

class ClinicUserModel extends Model
{
    protected $table = 'clinic_users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['clinic_id', 'user_id', 'role_id', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    /**
     * Get active memberships for a user
     */
    public function getMemberships($userId)
    {
        return $this->select('clinic_users.*, clinics.name as clinic_name, clinics.slug as clinic_slug')
                    ->join('clinics', 'clinics.id = clinic_users.clinic_id')
                    ->where('user_id', $userId)
                    ->where('clinic_users.status', 'active')
                    ->where('clinics.status', 'active')
                    ->findAll();
    }

    /**
     * Get specific active membership
     */
    public function getMembership($userId, $clinicId)
    {
        return $this->select('clinic_users.*, clinics.name as clinic_name')
                    ->join('clinics', 'clinics.id = clinic_users.clinic_id')
                    ->where('user_id', $userId)
                    ->where('clinic_id', $clinicId)
                    ->where('clinic_users.status', 'active')
                    ->where('clinics.status', 'active')
                    ->first();
    }
}
