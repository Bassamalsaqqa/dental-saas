<?php

namespace App\Traits;

trait TenantTrait
{
    protected function setClinicId(array $data)
    {
        $session = service('session');
        $clinicId = $session->get('active_clinic_id');

        if ($clinicId && !isset($data['data']['clinic_id'])) {
            $data['data']['clinic_id'] = $clinicId;
        }

        return $data;
    }

    protected function initializeTenantTrait()
    {
        // Add to beforeInsert callbacks
        if (!isset($this->beforeInsert)) {
            $this->beforeInsert = [];
        }
        $this->beforeInsert[] = 'setClinicId';
        
        // Add to beforeUpdate callbacks?
        // Usually update doesn't need to change clinic_id (it stays same).
        // But if we want to enforce it matches session, that's read-scoping/write-scoping.
        // For now, only INSERT needs it to satisfy NOT NULL constraint.
        // Updating existing row shouldn't nullify it.
    }
}
