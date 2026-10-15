<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;


class ProviderDirectoryService
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Check if dynamic doctor scheduling is enabled for a clinic.
     * Explicit clinic DB read, no session or bearer fallback.
     */
    public function isEnabled(int $clinicId): bool
    {
        if ($clinicId <= 0) {
            return false;
        }

        $value = (new ClinicSchedulingSettings($this->db))->get($clinicId, 'dynamic_doctor_scheduling_enabled', '0');
        return in_array($value, [true, 1, '1', 'true'], true);

    }

    /**
     * Check if the dynamic doctor scheduling database schema is present.
     */
    public function isSchemaReady(): bool
    {
        return $this->db->tableExists('clinic_provider_profiles')
            && $this->db->fieldExists('provider_user_id', 'appointments');
    }

    /**
     * List all eligible providers for a clinic.
     * Exclude super_admin/foreign/inactive roles, inactive users/memberships/clinics.
     * Canonical doctors auto-eligible without profile.
     */
    public function listForClinic(int $clinicId, bool $forUpdate = false): array
    {
        if ($clinicId <= 0) {
            return [];
        }

        $enabled = $this->isEnabled($clinicId);
        $schemaReady = $this->isSchemaReady();

        if ($enabled && !$schemaReady) {
            throw new \RuntimeException('DYNAMIC_SCHEDULING_SCHEMA_NOT_READY: Dynamic doctor scheduling is enabled but database schema is missing.');
        }

        // Verify active clinic
        $clinic = $this->db->table('clinics')
            ->where('id', $clinicId)
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        if (!$clinic) {
            return [];
        }

        $builder = $this->db->table('clinic_users')
            ->select('
                users.id as user_id,
                users.first_name,
                users.last_name,
                users.email,
                users.active,
                clinic_users.role_id,
                roles.name as role_name,
                roles.slug as role_slug,
                roles.is_doctor,
                roles.is_medical,
                roles.is_active as role_is_active,
                roles.clinic_id as role_clinic_id
            ')
            ->join('users', 'users.id = clinic_users.user_id', 'inner')
            ->join('roles', 'roles.id = clinic_users.role_id', 'inner')
            ->where('clinic_users.clinic_id', $clinicId)
            ->where('clinic_users.status', 'active')
            ->where('users.active', 1)
            ->where('roles.is_active', 1)
            ->where('roles.slug !=', 'super_admin')
            ->groupStart()
                ->where('roles.clinic_id IS NULL', null, false)
                ->orWhere('roles.clinic_id', $clinicId)
            ->groupEnd();

        if ($schemaReady) {
            $builder->select('
                cpp.id as profile_id,
                cpp.is_bookable,
                cpp.is_clinician,
                cpp.weekly_hours_json,
                cpp.exceptions_json
            ');
            $builder->join('clinic_provider_profiles cpp', "cpp.clinic_id = {$clinicId} AND cpp.user_id = users.id", 'left');
        }

        $builder->orderBy('users.first_name', 'ASC')
            ->orderBy('users.last_name', 'ASC')
            ->orderBy('users.id', 'ASC');

        $rows = $forUpdate ? $this->db->query($builder->getCompiledSelect() . ' FOR UPDATE')->getResultArray() : $builder->get()->getResultArray();
        $providers = [];

        foreach ($rows as $row) {
            $roleSlug = (string) ($row['role_slug'] ?? '');
            $isDoctorRole = ((int) ($row['is_doctor'] ?? 0) === 1);
            $isCanonicalDoctor = in_array($roleSlug, ['doctor', 'senior_doctor'], true);
            $hasExplicitClinician = $schemaReady && !empty($row['is_clinician']) && (int) $row['is_clinician'] === 1;

            // Reject assistant just because is_medical=1. Eligibility requires doctor/senior_doctor slug,
            // is_doctor=1, or explicit is_clinician profile.
            if (!$isCanonicalDoctor && !$isDoctorRole && !$hasExplicitClinician) {
                continue;
            }

            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            $name = trim($firstName . ' ' . $lastName);
            if ($name === '') {
                $name = trim((string) ($row['email'] ?? '')) ?: ('Doctor #' . $row['user_id']);
            }

            $hasProfile = $schemaReady && !empty($row['profile_id']);
            $isBookable = true;
            $isClinician = false;
            $weeklyHours = null;
            $exceptions = null;

            if ($hasProfile) {
                $isBookable = ((int) ($row['is_bookable'] ?? 1) === 1);
                $isClinician = ((int) ($row['is_clinician'] ?? 0) === 1);
                if (!empty($row['weekly_hours_json'])) {
                    $decoded = json_decode($row['weekly_hours_json'], true);
                    if (!is_array($decoded)) { throw new \DomainException('Invalid provider availability configuration.'); }
                    $weeklyHours = $decoded;
                }
                if (!empty($row['exceptions_json'])) {
                    $decoded = json_decode($row['exceptions_json'], true);
                    if (!is_array($decoded)) { throw new \DomainException('Invalid provider availability configuration.'); }
                    $exceptions = $decoded;
                }
            }

            $providers[] = [
                'user_id'       => (int) $row['user_id'],
                'name'          => $name,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'email'         => (string) ($row['email'] ?? ''),
                'role_id'       => (int) $row['role_id'],
                'role_name'     => (string) ($row['role_name'] ?? ''),
                'role_slug'     => $roleSlug,
                'is_bookable'   => $isBookable,
                'is_clinician'  => $isClinician,
                'has_profile'   => $hasProfile,
                'weekly_hours'  => $weeklyHours,
                'exceptions'    => $exceptions,
            ];
        }

        return $providers;
    }

    /**
     * Find an eligible and bookable provider in a clinic.
     */
    public function findBookable(int $clinicId, int $userId, bool $forUpdate = false): ?array
    {
        if ($clinicId <= 0 || $userId <= 0) {
            return null;
        }

        $providers = $this->listForClinic($clinicId, $forUpdate);
        foreach ($providers as $provider) {
            if ($provider['user_id'] === $userId) {
                return $provider['is_bookable'] ? $provider : null;
            }
        }

        return null;
    }

    /**
     * Get booking context for views and controllers.
     */
    public function getBookingContext(int $clinicId): array
    {
        $enabled = $this->isEnabled($clinicId);
        $schemaReady = $this->isSchemaReady();
        $providers = ($enabled && $schemaReady && $clinicId > 0) ? array_values(array_filter($this->listForClinic($clinicId), static fn(array $provider): bool => $provider['is_bookable'] === true)) : [];

        return [
            'enabled'       => $enabled,
            'schema_ready'  => $schemaReady,
            'providers'     => $providers,
            'chair_options' => $enabled ? [] : $this->getLegacyChairOptions($clinicId),
        ];
    }

    /**
     * Get configured legacy chair options for clinic strictly from DB.
     */
    private function getLegacyChairOptions(int $clinicId): array
    {
        return (new ClinicSchedulingSettings($this->db))->chairs($clinicId);
    }
}
