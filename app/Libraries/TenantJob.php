<?php

namespace App\Libraries;

use CodeIgniter\CLI\CLI;

/**
 * TenantJob
 * 
 * Base class for all background/CLI jobs that operate on tenant data.
 * Enforces clinic context requirement and bootstraps the environment.
 */
abstract class TenantJob
{
    protected $clinicId;
    protected $clinic;

    /**
     * Constructor
     * 
     * @param int $clinicId
     */
    public function __construct(int $clinicId)
    {
        $this->setClinicContext($clinicId);
    }

    /**
     * Set the clinic context and bootstrap the environment
     * 
     * @param int $clinicId
     * @throws \RuntimeException if clinic_id is missing or invalid
     */
    public function setClinicContext(int $clinicId)
    {
        $this->clinicId = $clinicId;
        $this->bootstrap();
    }

    /**
     * Bootstrap the tenant context
     * 
     * @throws \RuntimeException
     */
    protected function bootstrap()
    {
        if (!$this->clinicId) {
            throw new \RuntimeException("TENANT_CONTEXT_MISSING: Clinic ID is required for background jobs touching tenant data.");
        }

        // Verify clinic exists using a scoped-like check (just existence in this case as we are root in CLI)
        $db = \Config\Database::connect();
        $clinic = $db->table('clinics')->where('id', $this->clinicId)->get()->getRowArray();
        
        if (!$clinic) {
            throw new \RuntimeException("INVALID_TENANT: Clinic ID {$this->clinicId} does not exist.");
        }

        $this->clinic = $clinic;

        // Mock the session for models that rely on session()->get('active_clinic_id')
        $session = \Config\Services::session();
        $session->set('active_clinic_id', $this->clinicId);
        $session->set('is_background_job', true);

        log_message('info', "TenantJob bootstrapped for Clinic ID: {$this->clinicId} ({$clinic['name']})");
    }

    /**
     * Main execution logic
     * 
     * @param array $params
     * @return mixed
     */
    abstract public function run(array $params = []);

    /**
     * Helper to get clinic data
     */
    public function getClinic()
    {
        return $this->clinic;
    }
}
