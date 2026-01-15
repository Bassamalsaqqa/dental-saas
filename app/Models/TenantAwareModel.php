<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TenantAwareModel
 * 
 * Base model for all tenant-specific tables.
 * Enforces clinic_id scoping and provides helpers to reduce manual where() calls.
 */
abstract class TenantAwareModel extends Model
{
    protected $tenantField = 'clinic_id';

    /**
     * Scope the query to a specific clinic.
     * 
     * @param int $clinicId
     * @return $this
     */
    public function forClinic(int $clinicId)
    {
        return $this->where($this->table . '.' . $this->tenantField, $clinicId);
    }

    /**
     * Find a specific record by ID within a clinic's scope.
     * 
     * @param int $clinicId
     * @param mixed $id
     * @return array|object|null
     */
    public function findByClinic(int $clinicId, $id)
    {
        return $this->forClinic($clinicId)->find($id);
    }

    /**
     * Count records for a clinic with optional criteria.
     * 
     * @param int $clinicId
     * @param array $criteria
     * @return int
     */
    public function countByClinic(int $clinicId, array $criteria = [])
    {
        $builder = $this->forClinic($clinicId);
        if (!empty($criteria)) {
            $builder->where($criteria);
        }
        return $builder->countAllResults();
    }

    /**
     * Ensure clinic_id is set before insertion.
     * This is a callback for beforeInsert.
     */
    public function setClinicId(array $data)
    {
        if (isset($data['data']) && !isset($data['data'][$this->tenantField])) {
            $clinicId = session()->get('active_clinic_id');
            if ($clinicId) {
                $data['data'][$this->tenantField] = $clinicId;
            } else {
                // Fail-closed: If we are inserting into a tenant table without a clinic ID, abort.
                throw new \RuntimeException("TENANT_CONTEXT_MISSING: Cannot insert into {$this->table} without clinic_id.");
            }
        }
        return $data;
    }

    /**
     * Validation helper to ensure the entity belongs to the clinic
     * 
     * @param int $clinicId
     * @param mixed $id
     * @throws \RuntimeException if entity not found in clinic
     */
    public function checkClinicId(int $clinicId, $id)
    {
        $exists = $this->forClinic($clinicId)->where($this->primaryKey, $id)->countAllResults();
        if ($exists === 0) {
            throw new \RuntimeException("TENANT_ACCESS_DENIED: Entity {$id} in {$this->table} does not belong to clinic {$clinicId}.");
        }
    }
}
