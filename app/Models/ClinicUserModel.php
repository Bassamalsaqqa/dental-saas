<?php

namespace App\Models;

class ClinicUserModel extends TenantAwareModel
{
    protected $table = 'clinic_users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['clinic_id', 'user_id', 'role_id', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    /**
     * Get active memberships for a user
     */
    public function getMemberships($userId)
    {
        return $this->withoutTenantScope()
                    ->select('clinic_users.*, clinics.name as clinic_name, clinics.slug as clinic_slug')
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
        return $this->withoutTenantScope()
                    ->select('clinic_users.*, clinics.name as clinic_name')
                    ->join('clinics', 'clinics.id = clinic_users.clinic_id')
                    ->where('user_id', $userId)
                    ->where('clinic_id', $clinicId)
                    ->where('clinic_users.status', 'active')
                    ->where('clinics.status', 'active')
                    ->first();
    }

    /**
     * Check if a user belongs to a clinic
     */
    public function isUserInClinic($clinicId, $userId)
    {
        return $this->withoutTenantScope()
                    ->where('clinic_id', $clinicId)
                    ->where('user_id', $userId)
                    ->countAllResults() > 0;
    }

    /**
     * Count active users for a clinic.
     */
    public function countActiveByClinic(int $clinicId): int
    {
        return (int) $this->withoutTenantScope()
                          ->where('clinic_id', $clinicId)
                          ->where('status', 'active')
                          ->countAllResults();
    }

    public function findActiveMembershipByClinicAndUser(int $clinicId, int $userId): ?array
    {
        return $this->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    public function findClinicAdminEmailForClinic(int $clinicId): ?array
    {
        return $this->withoutTenantScope()
            ->select('users.email')
            ->join('users', 'users.id = clinic_users.user_id', 'left')
            ->join('roles', 'roles.id = clinic_users.role_id', 'left')
            ->where('clinic_users.clinic_id', $clinicId)
            ->where('clinic_users.status', 'active')
            ->where('roles.slug', 'clinic_admin')
            ->orderBy('clinic_users.id', 'ASC')
            ->first();
    }

    public function getActiveUserDirectoryByClinic(int $clinicId): array
    {
        // Use an explicit query builder path to avoid any tenant callback ambiguity when
        // this method is used from mixed contexts (tenant pages and control-plane code).
        return $this->db->table('clinic_users')
            ->distinct()
            ->select('users.id, users.first_name, users.last_name, users.email')
            ->join('users', 'users.id = clinic_users.user_id', 'inner')
            ->where('clinic_users.clinic_id', $clinicId)
            ->where('clinic_users.status', 'active')
            ->groupStart()
                ->where('users.active', 1)
                ->orWhere('users.active IS NULL', null, false)
            ->groupEnd()
            ->orderBy('users.first_name', 'ASC')
            ->orderBy('users.last_name', 'ASC')
            ->get()
            ->getResultArray();
    }
    public function getMembershipStatsByClinic(int $clinicId): array
    {
        if ($clinicId <= 0) {
            throw new \InvalidArgumentException('A clinic is required for membership statistics.');
        }
        $total = $this->db->table('clinic_users')->where('clinic_id', $clinicId)->countAllResults();
        $active = $this->db->table('clinic_users')->where('clinic_id', $clinicId)->where('status', 'active')->countAllResults();
        $byRole = $this->db->table('clinic_users cu')->select('r.name, count(cu.id) as count')
            ->join('roles r', 'r.id = cu.role_id')->where('cu.clinic_id', $clinicId)
            ->groupBy('r.id, r.name')->get()->getResultArray();
        return ['total' => $total, 'active' => $active, 'inactive' => $total - $active, 'by_group' => $byRole];
    }
}
