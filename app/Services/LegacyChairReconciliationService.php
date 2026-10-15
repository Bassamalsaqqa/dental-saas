<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;

class LegacyChairReconciliationService
{
    protected BaseConnection $db;
    protected PermissionService $permissionService;
    protected ProviderDirectoryService $directoryService;

    public function __construct(
        ?BaseConnection $db = null,
        ?PermissionService $permissionService = null,
        ?ProviderDirectoryService $directoryService = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->permissionService = $permissionService ?? new PermissionService($this->db);
        $this->directoryService = $directoryService ?? new ProviderDirectoryService($this->db);
    }

    /**
     * Generate read-only reconciliation report for legacy chair names.
     * Strictly no-write; requires explicit clinic operator authority.
     */
    public function generateReport(int $clinicId, int $actorId): array
    {
        if ($clinicId <= 0 || $actorId <= 0) {
            throw new DomainException('Clinic ID and Actor ID required.');
        }

        // Require the authenticated operator in the explicitly selected clinic.
        $apiActor = \App\Libraries\ApiRuntimeContext::getUserId();
        $apiClinic = \App\Libraries\ApiRuntimeContext::getClinicId();
        if ($apiActor !== null || $apiClinic !== null) {
            $contextMatches = $apiActor === $actorId && $apiClinic === $clinicId;
        } else {
            $auth = new \App\Libraries\IonAuth();
            $contextMatches = $auth->loggedIn() && (int) $auth->getUserId() === $actorId
                && (int) session('active_clinic_id') === $clinicId && session('global_mode') !== true;
        }
        if (!$contextMatches || !$this->permissionService->getTenantRoleId($actorId, $clinicId)
            || !$this->permissionService->hasPermission($actorId, 'settings', 'edit')) {
            throw new DomainException('Unauthorized reconciliation report.');
        }
        if (!$this->directoryService->isSchemaReady()) {
            throw new DomainException('Scheduling expansion is required before reconciliation.');
        }

        // Query distinct chair labels with counts from appointments where provider_user_id IS NULL
        $chairQuery = $this->db->table('appointments')
            ->select('chair_name, COUNT(*) as appointment_count, MIN(appointment_date) as earliest_date, MAX(appointment_date) as latest_date')
            ->where('clinic_id', $clinicId)
            ->groupStart()
                ->where('provider_user_id IS NULL', null, false)
                ->orWhere('provider_user_id =', 0)
            ->groupEnd()
            ->groupBy('chair_name')
            ->orderBy('appointment_count', 'DESC')
            ->get();

        $chairRows = $chairQuery->getResultArray();

        // Get candidate eligible doctors in this clinic
        $candidates = $this->directoryService->listForClinic($clinicId);
        $candidateSummaries = array_map(static fn($p) => [
            'user_id' => $p['user_id'],
            'name'    => $p['name'],
            'email'   => $p['email'],
            'role'    => $p['role_slug'],
        ], $candidates);

        $reportEntries = [];
        $totalLegacyAppointments = 0;

        foreach ($chairRows as $row) {
            $label = (string) $row['chair_name'];
            $count = (int) $row['appointment_count'];
            $totalLegacyAppointments += $count;

            $reportEntries[] = [
                'chair_label'               => $label,
                'appointment_count'         => $count,
                'earliest_date'             => $row['earliest_date'],
                'latest_date'               => $row['latest_date'],
                'potential_candidate_ids'   => [], // Never infer an identity from a label.
                'ambiguity_status'          => 'unresolved', // Explicitly unresolved, no guessing or auto-mapping
            ];
        }

        return [
            'clinic_id'                   => $clinicId,
            'generated_at'                => date('Y-m-d H:i:s'),
            'total_legacy_appointments'   => $totalLegacyAppointments,
            'distinct_chair_labels_count' => count($reportEntries),
            'eligible_clinic_candidates'  => $candidateSummaries,
            'report_entries'              => $reportEntries,
            'policy'                      => 'Conservative blocking: legacy appointments block new availability until manual operator review.',
        ];
    }
}
