<?php

namespace App\Services;

use App\Models\ClinicUserModel;

class ClinicService
{
    protected $clinicUserModel;
    protected $session;

    public function __construct()
    {
        $this->clinicUserModel = new ClinicUserModel();
        $this->session = service('session');
    }

    /**
     * Get active memberships for a user
     */
    public function getMemberships($userId)
    {
        return $this->clinicUserModel->getMemberships($userId);
    }

    /**
     * Get specific membership
     */
    public function getMembership($userId, $clinicId)
    {
        return $this->clinicUserModel->getMembership($userId, $clinicId);
    }

    /**
     * Set Tenant Context Session
     */
    public function setContext(array $membership)
    {
        // Clear old context
        $this->clearContext();

        // Set new keys
        $this->session->set('active_clinic_id', $membership['clinic_id']);
        $this->session->set('active_clinic_role_id', $membership['role_id']);
        $this->session->set('active_clinic_membership_id', $membership['id']); // Recommendation #3
        
        // Regenerate ID for security
        $this->session->regenerate();
    }

    /**
     * Clear Tenant Context Session
     */
    public function clearContext()
    {
        $this->session->remove([
            'active_clinic_id', 
            'active_clinic_role_id',
            'active_clinic_membership_id'
        ]);
        $this->session->set('global_mode', false);
    }
}
