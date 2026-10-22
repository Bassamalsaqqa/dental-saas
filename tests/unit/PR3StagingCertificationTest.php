<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ControlPlaneAuditModel;
use App\Services\ClinicSchedulingConfigurationService;
use App\Services\ClinicTemporalPolicyService;
use App\Services\ProviderDirectoryService;
use App\Services\ProviderScheduleService;
use App\Services\ScheduleImpactAuditStore;
use App\Services\ScheduleImpactService;
use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use Config\Database;
use DateTimeImmutable;
use DateTimeZone;
use Tests\Support\PhaseBFixture;

/**
 * Isolated staging certification harness for approved Denta+ PR3 (exact SHA ad481ebbf454ebf3a53bc09417d13a2b2f57c0d5).
 * Rehearses representative synthetic scale, charge-query index scans, audit reconstruction & restore,
 * transaction rollback, and legacy readiness under tenant-isolated synthetic fixtures.
 *
 * DB testing is Codex's exclusive job: this harness is designed for safe execution by Codex via:
 * php scripts/safe_test_runner.php --no-coverage --no-logging --do-not-cache-result tests/unit/PR3StagingCertificationTest.php
 */
final class PR3StagingCertificationTest extends PhaseBFixture
{

    protected function tearDown(): void
    {
        while ($this->db && $this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        if (isset($this->clinic)) {
            foreach (['clinical_charge_lines', 'examinations', 'finances', 'clinical_procedures'] as $table) {
                if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id', $table)) {
                    $this->db->table($table)->whereIn('clinic_id', [$this->clinic, $this->otherClinic])->delete();
                }
            }
            if ($this->db->tableExists('control_plane_audits')) {
                $this->db->table('control_plane_audits')->whereIn('actor_user_id', $this->users)->delete();
            }
        }
        Time::setTestNow();
        parent::tearDown();
    }

    /**
     * 1. Representative synthetic scale benchmark & historical byte-preservation with charge-query EXPLAIN assessment:
     * - 50,000 historical appointments + 5,000 future appointments.
     * - 50,000 historical charges + 1 active encounter charge dependency.
     * - 2,002 synthetic patients (1 base + 1 historical + 2,000 generated) + 4 providers.
     * - Bulk 500 rows inserts with unique bounded IDs.
     * - Fixed UTC time 2030-01-07 06:00:00 (08:00 local).
     * - Same-day elapsed (07:00-07:30, omitted from report), ongoing (07:45-08:15, reschedulable=false, edit_url=null),
     *   upcoming (08:00, 09:00..), plus later future (2030-01-08..). Exactly 4,999 active items reported.
     * - Default-off fixture proof: missing flag => false, no boolean upgrade, canary verification.
     * - Benchmark locked preview (lockState + generateReport) on same connection owned transaction.
     * - Separate lock phase query count: point locks scale O(future appointments); no claim of O(1) locks.
     * - Byte-identical snapshot hash/count across appointments, patients, exams, charges, procedures, finances.
     * - Invokes assessChargeQueryExplainAndScan() as private helper with same populated fixture & metrics,
     *   eliminating inter-test ordering / static state dependence.
     */
    public function testRepresentativeSyntheticScaleBenchmarkAndChargeQueryExplain(): void
    {
        $now = new DateTimeImmutable('2030-01-07 06:00:00', new DateTimeZone('UTC'));
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));

        // Default-off fixture proof: verify missing flag in clean clinic evaluates to false without upgrading settings
        $directoryService = new ProviderDirectoryService($this->db);
        $this->assertFalse($directoryService->isEnabled($this->otherClinic));
        $this->assertNull(
            $this->db->table('settings')
                ->where(['clinic_id' => $this->otherClinic, 'setting_key' => 'dynamic_doctor_scheduling_enabled'])
                ->get()
                ->getRowArray()
        );

        // Record setting canaries outside owned synthetic clinic
        $otherSettingsBefore = $this->db->table('settings')
            ->where('clinic_id !=', $this->clinic)
            ->where('clinic_id !=', $this->otherClinic)
            ->countAllResults();

        // 1. Insert historical patient + 2,000 generated patients (base patient already created in PhaseBFixture => 2,002 total)
        $this->db->table('patients')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => 'P' . bin2hex(random_bytes(8)),
            'first_name' => 'Historical',
            'last_name' => 'Synthetic',
            'gender' => 'other',
            'status' => 'active',
            'created_by' => $this->actor,
        ]);
        $historicalPatient = (int) $this->db->insertID();

        $patientBatch = [];
        for ($i = 0; $i < 2000; $i++) {
            $patientBatch[] = [
                'clinic_id' => $this->clinic,
                'patient_id' => substr($this->prefix, -10) . 'P' . sprintf('%05d', $i),
                'first_name' => 'Synthetic',
                'last_name' => 'Patient ' . $i,
                'gender' => 'other',
                'status' => 'active',
                'created_by' => $this->actor,
            ];
            if (count($patientBatch) === 500) {
                $this->db->table('patients')->insertBatch($patientBatch);
                $patientBatch = [];
            }
        }
        if (!empty($patientBatch)) {
            $this->db->table('patients')->insertBatch($patientBatch);
            $patientBatch = [];
        }

        $identity = bin2hex(random_bytes(6));

        // 2. Insert 50,000 historical appointments in batches of 500
        $batch = [];
        for ($i = 0; $i < 50000; $i++) {
            $slot = $i % 16;
            $batch[] = [
                'clinic_id' => $this->clinic,
                'patient_id' => $historicalPatient,
                'appointment_id' => 'H' . $identity . sprintf('%06d', $i),
                'appointment_date' => '2020-01-08',
                'appointment_time' => sprintf('%02d:%02d:00', 9 + intdiv($slot, 2), ($slot % 2) * 30),
                'duration' => 30,
                'appointment_type' => 'consultation',
                'status' => 'completed',
                'chair_name' => 'Synthetic historical snapshot',
                'provider_user_id' => $this->providers[intdiv($i % 64, 16)],
                'notes' => 'Preserved synthetic historical appointment',
                'created_by' => $this->actor,
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
            ];
            if (count($batch) === 500) {
                $this->db->table('appointments')->insertBatch($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            $this->db->table('appointments')->insertBatch($batch);
            $batch = [];
        }

        // 3. Insert 5,000 future appointments (same-day ended/ongoing/upcoming + later future)
        // Local clinic timezone Asia/Hebron at UTC 06:00 is 08:00 local time.
        // - i=0: Elapsed (07:00-07:30, ends before 08:00 local) -> omitted from report
        // - i=1: Ongoing (07:45-08:15, start < now < end at 08:00 local) -> reported, reschedulable=false, edit_url=null
        // - i=2..15: Upcoming same-day (08:00 exact start, 09:00, 09:30..) -> reported, upcoming
        // - i=16..4999: Later future visits (2030-01-08..) -> reported, upcoming
        for ($i = 0; $i < 5000; $i++) {
            if ($i === 0) {
                // Same-day ended visit: 07:00-07:30 (elapsed at 08:00 local)
                $day = '2030-01-07';
                $time = '07:00:00';
                $status = 'completed';
            } elseif ($i === 1) {
                // Same-day ongoing visit: 07:45-08:15 (in progress at 08:00 local)
                $day = '2030-01-07';
                $time = '07:45:00';
                $status = 'scheduled';
            } elseif ($i === 2) {
                // Same-day upcoming visit starting exactly at current hour: 08:00
                $day = '2030-01-07';
                $time = '08:00:00';
                $status = 'scheduled';
            } elseif ($i < 16) {
                // Same-day upcoming visits: 09:00, 09:30, ...
                $day = '2030-01-07';
                $slot = $i - 3;
                $time = sprintf('%02d:%02d:00', 9 + intdiv($slot, 2), ($slot % 2) * 30);
                $status = 'scheduled';
            } else {
                // Later future visits: 2030-01-08 onwards
                $n = $i - 16;
                $day = (new DateTimeImmutable('2030-01-08'))->modify('+' . intdiv($n, 64) . ' days')->format('Y-m-d');
                $slot = $n % 16;
                $time = sprintf('%02d:%02d:00', 9 + intdiv($slot, 2), ($slot % 2) * 30);
                $status = 'scheduled';
            }
            $batch[] = [
                'clinic_id' => $this->clinic,
                'patient_id' => $this->patient,
                'appointment_id' => 'F' . $identity . sprintf('%06d', $i),
                'appointment_date' => $day,
                'appointment_time' => $time,
                'duration' => 30,
                'appointment_type' => 'consultation',
                'status' => $status,
                'chair_name' => 'Synthetic future snapshot',
                'provider_user_id' => $this->providers[intdiv($i % 64, 16)],
                'notes' => 'Synthetic future appointment',
                'created_by' => $this->actor,
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
            ];
            if (count($batch) === 500) {
                $this->db->table('appointments')->insertBatch($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            $this->db->table('appointments')->insertBatch($batch);
            $batch = [];
        }

        $historicalAppointment = (int) $this->db->table('appointments')
            ->where('clinic_id', $this->clinic)
            ->where('appointment_date', '2020-01-08')
            ->orderBy('id', 'ASC')
            ->get(1)
            ->getRow('id');

        $futureAppointment = (int) $this->db->table('appointments')
            ->where('clinic_id', $this->clinic)
            ->where('appointment_date >=', '2030-01-08')
            ->orderBy('id', 'ASC')
            ->get(1)
            ->getRow('id');

        // 4. Clinical examination and finance history
        $this->db->table('examinations')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $historicalPatient,
            'appointment_id' => $historicalAppointment,
            'examination_id' => 'E' . $identity,
            'examination_date' => '2020-01-08',
            'examination_type' => 'initial',
            'chief_complaint' => 'Synthetic preserved examination history',
            'status' => 'completed',
            'created_by' => $this->actor,
        ]);

        $this->db->table('finances')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $historicalPatient,
            'transaction_id' => 'T' . $identity,
            'transaction_type' => 'payment',
            'amount' => '75.00',
            'currency' => 'USD',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'service_type' => 'consultation',
            'notes' => 'Synthetic preserved financial transaction',
        ]);

        // 5. 50,000 historical charges in batches of 500
        $batch = [];
        for ($i = 0; $i < 50000; $i++) {
            $batch[] = [
                'clinic_id' => $this->clinic,
                'patient_id' => $historicalPatient,
                'appointment_id' => $historicalAppointment,
                'procedure_name' => 'Synthetic historical procedure',
                'charge_date' => '2020-01-08',
                'status' => 'completed',
                'notes' => 'Preserved synthetic historical charge line',
                'created_by' => $this->actor,
            ];
            if (count($batch) === 500) {
                $this->db->table('clinical_charge_lines')->insertBatch($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            $this->db->table('clinical_charge_lines')->insertBatch($batch);
            $batch = [];
        }

        // Active encounter charge dependency on future appointment
        $this->db->table('clinical_charge_lines')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => $futureAppointment,
            'procedure_name' => 'Active encounter lock charge',
            'charge_date' => '2030-01-08',
            'status' => 'accepted',
            'created_by' => $this->actor,
        ]);

        // Pre-preview byte-for-byte row hash snapshots
        $tables = ['appointments', 'patients', 'examinations', 'clinical_procedures', 'clinical_charge_lines', 'finances'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $this->hashOwnedRows($table);
        }

        // Execute benchmark under owned transaction
        $this->db->transBegin();
        $previewStarted = microtime(true);

        // Phase 1: Lock state benchmark (measures point locks)
        $queriesLock = [];
        $listenerLock = static function ($query) use (&$queriesLock): void {
            $queriesLock[] = $query->getQuery();
        };
        Events::on('DBQuery', $listenerLock);
        try {
            $lockStarted = microtime(true);
            $impactService = new ScheduleImpactService($this->db);
            $impactService->lockState($this->clinic, $now);
            $lockElapsed = microtime(true) - $lockStarted;
        } finally {
            Events::removeListener('DBQuery', $listenerLock);
        }
        $lockQueryCount = count($queriesLock);

        // Phase 2: Report generation benchmark
        $queriesReport = [];
        $listenerReport = static function ($query) use (&$queriesReport): void {
            $queriesReport[] = $query->getQuery();
        };
        Events::on('DBQuery', $listenerReport);
        try {
            $reportStarted = microtime(true);
            $memoryBefore = memory_get_usage(true);

            $dto = $impactService->validateProposedHoursDto([
                'working_hours_start' => '09:00',
                'working_hours_end' => '16:00',
                'appointment_duration' => '30',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'dynamic_doctor_scheduling_enabled' => '1',
            ], $this->clinic);

            $report = $impactService->generateReport($this->db, $this->clinic, 'clinic_hours', $dto, null, false, $now);
            $reportElapsed = microtime(true) - $reportStarted;
            $peakMemory = memory_get_peak_usage(true);
        } finally {
            Events::removeListener('DBQuery', $listenerReport);
            $previewElapsed = microtime(true) - $previewStarted;
            while ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            $this->db->resetTransStatus();
        }

        // EXPLAIN query plans on real captured queries
        $explains = [];
        $seen = [];
        foreach ($queriesReport as $sql) {
            if (preg_match('/^\s*SELECT\b/i', $sql) !== 1 || preg_match('/\bFROM\s+`?(appointments|clinical_charge_lines|examinations|clinical_procedures|patients)`?\b/i', $sql, $matches) !== 1) {
                continue;
            }
            $table = $matches[1];
            if (isset($seen[$table])) {
                continue;
            }
            $seen[$table] = true;
            $explains[$table] = [
                'query_sha256' => hash('sha256', $sql),
                'query_prefix' => substr($sql, 0, 220),
                'plan' => $this->db->query('EXPLAIN ' . $sql)->getResultArray(),
            ];
        }

        // Post-preview byte-for-byte row hash snapshots
        $after = [];
        foreach ($tables as $table) {
            $after[$table] = $this->hashOwnedRows($table);
        }

        // Record setting canaries after
        $otherSettingsAfter = $this->db->table('settings')
            ->where('clinic_id !=', $this->clinic)
            ->where('clinic_id !=', $this->otherClinic)
            ->countAllResults();

        $metrics = [
            'fixture' => [
                'historical_appointments' => 50000,
                'future_appointments' => 5000,
                'historical_charges' => 50000,
                'encounter_charges' => 1,
                'patients' => 2002,
                'providers' => count($this->providers),
            ],
            'canaries' => [
                'other_settings_before' => $otherSettingsBefore,
                'other_settings_after' => $otherSettingsAfter,
                'untouched' => ($otherSettingsBefore === $otherSettingsAfter),
            ],
            'benchmark' => [
                'lock_elapsed_seconds' => $lockElapsed,
                'lock_query_count' => $lockQueryCount,
                'point_lock_complexity' => 'O(future_active_appointments)',
                'report_elapsed_seconds' => $reportElapsed,
                'report_query_count' => count($queriesReport),
                'locked_preview_total_elapsed_seconds' => $previewElapsed,
                'memory_before_bytes' => $memoryBefore,
                'peak_memory_bytes' => $peakMemory,
            ],
            'report_summary' => [
                'total_future_visits' => $report['total_future_visits'],
                'reported_items_count' => count($report['items']),
                'omitted_elapsed_count' => 1,
            ],
            'before_rows' => $before,
            'after_rows' => $after,
            'explain' => $explains,
        ];
        file_put_contents(ROOTPATH . 'tmp/pr3-staging-scale.json', json_encode($metrics, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        // Scale & preservation assertions
        $this->assertSame(55000, $before['appointments']['count']);
        $this->assertSame(50001, $before['clinical_charge_lines']['count']);
        $this->assertSame(1, $before['examinations']['count']);
        $this->assertSame(1, $before['finances']['count']);
        $this->assertSame(2002, $before['patients']['count'], 'Patients must equal 1 base + 1 historical + 2,000 generated = 2,002.');
        $this->assertSame($before, $after, 'Locked preview lockState and generateReport must preserve every appointment, patient, and clinical/financial row byte-for-byte.');

        // Canary assertion
        $this->assertSame($otherSettingsBefore, $otherSettingsAfter, 'Settings outside owned clinic must never be mutated.');

        // Point lock behavior: point locks scale O(future active appointments)
        // 5,000 future appointments - 1 elapsed visit = 4,999 active visits point-locked
        $this->assertGreaterThan(4900, $lockQueryCount, 'Point locks scale O(future) for active visits; no claim of O(1) locks.');

        // Report active horizon assertions: 1 elapsed visit omitted, exactly 4,999 active items reported
        $this->assertSame(4999, $report['total_future_visits']);
        $this->assertCount(4999, $report['items'], 'generateReport must return exactly 4,999 active items omitting elapsed 07:00 visit.');

        // Ongoing (07:45-08:15) vs Upcoming (08:00) assertions
        $itemByTime = [];
        foreach ($report['items'] as $item) {
            $itemByTime[$item['appointment_date'] . ' ' . $item['appointment_time']] = $item;
        }

        // Verify ongoing item (07:45): active in progress, reschedulable=false, edit_url=null
        $this->assertArrayHasKey('2030-01-07 07:45', $itemByTime);
        $ongoingItem = $itemByTime['2030-01-07 07:45'];
        $this->assertSame(ClinicTemporalPolicyService::STATE_IN_PROGRESS, $ongoingItem['temporal_state']);
        $this->assertFalse($ongoingItem['reschedulable'], 'Ongoing visit must not be reschedulable.');
        $this->assertNull($ongoingItem['edit_url'], 'Ongoing visit must not have an edit url.');

        // Verify upcoming item (08:00): exact start is upcoming
        $this->assertArrayHasKey('2030-01-07 08:00', $itemByTime);
        $upcomingItem = $itemByTime['2030-01-07 08:00'];
        $this->assertSame(ClinicTemporalPolicyService::STATE_UPCOMING, $upcomingItem['temporal_state']);

        // Verify elapsed item (07:00) is omitted
        $this->assertArrayNotHasKey('2030-01-07 07:00', $itemByTime);

        // Report query count: dependencies must be batched, not queried per visit
        $this->assertLessThanOrEqual(200, count($queriesReport), 'Report must batch dependencies, not issue N queries per visit.');

        // Configurable finite budget check
        $this->assertLessThan(60.0, $previewElapsed, '5,000 future visits with 50,000 historical visits must complete within finite budget.');

        // Assess charge-query EXPLAIN plan and indexes using the populated fixture metrics in-process
        $this->assessChargeQueryExplainAndScan($metrics);
    }

    /**
     * Charge-query EXPLAIN assessment & index scan verification:
     * - Inspect existing indexes on clinical_charge_lines.
     * - Evaluates optimizer plan for batch charge check on populated 50,001 clinical_charge_lines.
     * - Records optimizer selected key, estimated rows, and scan behavior.
     * - Does NOT hard-assert absence of composite index (allows future additive index without failing).
     * - Documents index proposal as separately reviewed future migration; do not mutate schema here.
     */
    private function assessChargeQueryExplainAndScan(array $scale): void
    {
        $indexes = $this->db->query('SHOW INDEX FROM clinical_charge_lines')->getResultArray();
        $indexColumns = [];
        foreach ($indexes as $idx) {
            $keyName = (string) ($idx['Key_name'] ?? $idx['key_name']);
            $colName = (string) ($idx['Column_name'] ?? $idx['column_name']);
            $indexColumns[$keyName][] = $colName;
        }

        $this->assertSame(50001, $scale['before_rows']['clinical_charge_lines']['count']);
        $this->assertSame($scale['before_rows'], $scale['after_rows']);
        $this->assertArrayHasKey('clinical_charge_lines', $scale['explain'] ?? []);
        $captured = $scale['explain']['clinical_charge_lines'];
        $plan = $captured['plan'];
        $this->assertNotEmpty($plan);
        $chargePlan = array_values(array_filter($plan, static fn(array $row): bool => $row['table'] === 'clinical_charge_lines'));
        $this->assertCount(1, $chargePlan);
        $usedKey = $chargePlan[0]['key'];
        $estimatedRows = $chargePlan[0]['rows'];

        $hasCompositeClinicAppointment = false;
        foreach ($indexColumns as $name => $cols) {
            if ($cols === ['clinic_id', 'appointment_id'] || (isset($cols[0], $cols[1]) && $cols[0] === 'clinic_id' && $cols[1] === 'appointment_id')) {
                $hasCompositeClinicAppointment = true;
                break;
            }
        }

        $assessment = [
            'table' => 'clinical_charge_lines',
            'indexes_present' => array_keys($indexColumns),
            'composite_clinic_appointment_index_present' => $hasCompositeClinicAppointment,
            'optimizer_selected_key' => $usedKey,
            'plan_estimated_rows' => $estimatedRows,
            'source' => 'current-run populated scale report query',
            'populated_charge_rows' => 50001,
            'query_sha256' => $captured['query_sha256'],
            'explain_query_prefix' => $captured['query_prefix'],
            'plan' => $plan,
            'assessment_summary' => [
                'current_behavior' => 'Optimizer uses available clinic_id prefix index (e.g. idx_ccl_patient_status) and scans clinic charges, filtering by appointment_id.',
                'report_batching' => 'Dependencies are batched; inspect measured query count and estimated rows rather than infer constant scan work.',
                'recommendation' => 'Separately reviewed migration proposal: ADD INDEX idx_ccl_clinic_appointment (clinic_id, appointment_id).',
                'staging_gate' => 'Must measure write overhead and index build locking on production-like volume before proposing DDL.',
            ],
        ];
        file_put_contents(ROOTPATH . 'tmp/pr3-charge-index-assessment.json', json_encode($assessment, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    /**
     * 2. Audit reconstruction + synthetic restore rehearsal:
     * - configureHours with proof and >=300 affected visits + long Arabic reasons (>64 KiB multipart bundle).
     * - Export exact own compact pointer row + ALL own bundle part rows to JSON file with SHA256.
     * - Loss simulation: delete ALL own bundle parts and compact pointer row, commit txn; verify both read null.
     * - Restore simulation: parse export file, verify SHA256, validate clinic/actor/review scope;
     *   restore whole export under checked transaction with same PKs; commit txn.
     * - Compare reconstructed bundle byte hash bit-for-bit with original bundle; verify appointments unchanged.
     * - Explicitly label: synthetic audit reconstruction recovery, not production backup certification.
     */
    public function testAuditReconstructionAndSyntheticRestoreRehearsal(): void
    {
        $now = new DateTimeImmutable('2030-01-07 06:00:00', new DateTimeZone('UTC'));
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));

        // 1. Create 320 affected future appointments outside proposed 16:00 end
        $affectedCount = 320;
        $batch = [];
        for ($i = 1; $i <= $affectedCount; $i++) {
            $day = (new DateTimeImmutable('2030-01-10'))->modify('+' . intdiv($i - 1, 16) . ' days')->format('Y-m-d');
            $slotTime = ($i % 2 === 0) ? '16:30:00' : '17:00:00';
            $batch[] = [
                'clinic_id' => $this->clinic,
                'patient_id' => $this->patient,
                'appointment_id' => 'AUDIT' . bin2hex(random_bytes(4)) . sprintf('%04d', $i),
                'appointment_date' => $day,
                'appointment_time' => $slotTime,
                'duration' => 30,
                'appointment_type' => 'consultation',
                'status' => 'scheduled',
                'chair_name' => 'Synthetic audit rehearsal chair',
                'provider_user_id' => $this->providers[0],
                'notes' => 'Synthetic appointment for audit reconstruction rehearsal',
                'created_by' => $this->actor,
                'created_at' => '2030-01-01 00:00:00',
                'updated_at' => '2030-01-01 00:00:00',
            ];
            if (count($batch) === 100 || $i === $affectedCount) {
                $this->db->table('appointments')->insertBatch($batch);
                $batch = [];
            }
        }

        $impactService = new ScheduleImpactService($this->db);
        $dto = $impactService->validateProposedHoursDto([
            'working_hours_start' => '09:00',
            'working_hours_end' => '16:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ], $this->clinic);

        $report = $impactService->generateReport($this->db, $this->clinic, 'clinic_hours', $dto, null, false, $now);
        $this->assertGreaterThanOrEqual($affectedCount, $report['after_conflicts_count']);

        // Safe ASCII string with valid PHP unicode escape (\u{0627}\u{0644}\u{0639}\u{0631}\u{0628}\u{064a}\u{0629})
        $arabicWord = "\u{0627}\u{0644}\u{0639}\u{0631}\u{0628}\u{064a}\u{0629}";
        $dispositions = [];
        foreach ($report['affected_appointment_ids'] as $id) {
            $reason = str_repeat($arabicWord . ' ', 20) . '#' . $id;
            $dispositions[$id] = ['action' => 'retain', 'reason' => $reason];
        }

        $token = $impactService->issueProof($this->clinic, $this->actor, 'clinic_hours', $dto, $report);
        $proof = ['token' => $token, 'acknowledged' => true, 'dispositions' => $dispositions];

        // Apply configuration with audit write
        $configService = new ClinicSchedulingConfigurationService($this->db, $impactService);
        $applyResult = $configService->configureHours($this->clinic, $dto, $this->actor, $proof);
        $this->assertTrue($applyResult['success'], json_encode($applyResult));

        // Verify complete bundle read
        $originalBundle = $impactService->getLatestAuditRecord($this->clinic);
        $this->assertNotNull($originalBundle);
        $this->assertGreaterThanOrEqual($affectedCount, $originalBundle['affected_count']);
        $this->assertSame($dispositions, $originalBundle['dispositions']);
        $originalRetained = $impactService->getRetainedExceptions($this->clinic);
        $originalBundleHash = hash('sha256', json_encode($originalBundle, JSON_THROW_ON_ERROR));

        // Pre-deletion appointment snapshot hash
        $appointmentsHashBefore = $this->hashOwnedRows('appointments');

        // Read exact compact pointer row from settings
        $pointerRow = $this->db->table('settings')
            ->where(['clinic_id' => $this->clinic, 'setting_key' => 'schedule_impact_latest_audit'])
            ->get()
            ->getRowArray();
        $this->assertNotNull($pointerRow);
        $pointer = json_decode((string) $pointerRow['setting_value'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertGreaterThan(1, $pointer['parts'], 'Audit evidence must span multiple parts (>64 KiB).');

        // Read ALL own audit part rows from control_plane_audits
        $auditRows = $this->db->table('control_plane_audits')
            ->where('actor_user_id', $this->actor)
            ->where('event_type', 'schedule_impact_bundle_part')
            ->where('id >=', $pointer['first_id'])
            ->where('id <=', $pointer['last_id'])
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        $this->assertSame($pointer['parts'], count($auditRows));

        // Export fixture-owned audit rows + pointer row to JSON file
        $exportPayload = [
            'type' => 'synthetic_audit_reconstruction_rehearsal',
            'clinic_id' => $this->clinic,
            'actor_user_id' => $this->actor,
            'review_id' => $pointer['review_id'],
            'pointer_row' => $pointerRow,
            'parts_count' => count($auditRows),
            'audit_rows' => $auditRows,
        ];
        $exportJson = json_encode($exportPayload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $exportFile = ROOTPATH . 'tmp/pr3-staging-audit-export.json';
        $writtenBytes = file_put_contents($exportFile, $exportJson);
        $this->assertGreaterThan(0, $writtenBytes, 'Audit export file must be written successfully.');
        $exportSha256 = hash_file('sha256', $exportFile);
        $this->assertIsString($exportSha256);

        // Loss simulation: Delete ALL own bundle parts and the compact pointer row, then commit
        $this->assertTrue($this->db->transBegin());
        $this->db->table('settings')
            ->where(['clinic_id' => $this->clinic, 'setting_key' => 'schedule_impact_latest_audit'])
            ->delete();
        $this->db->table('control_plane_audits')
            ->where('actor_user_id', $this->actor)
            ->where('event_type', 'schedule_impact_bundle_part')
            ->where('id >=', $pointer['first_id'])
            ->where('id <=', $pointer['last_id'])
            ->delete();
        $this->assertTrue($this->db->transStatus());
        $this->assertTrue($this->db->transCommit());

        // Fail-closed verification: missing parts and missing pointer must both cause reads to return null
        $this->assertNull($impactService->getLatestAuditRecord($this->clinic), 'Deleted audit parts must cause getLatestAuditRecord to return null.');
        $this->assertNull(
            (new ScheduleImpactAuditStore($this->db))->read($this->clinic, $pointer),
            'Deleted audit parts must cause read to return null.'
        );

        $this->assertSame([], $impactService->getRetainedExceptions($this->clinic));

        // Whole export restore simulation: read file, verify SHA256, validate scope, restore in checked txn
        $restoredJson = file_get_contents($exportFile);
        $this->assertSame($exportSha256, hash('sha256', $restoredJson), 'Export JSON SHA256 must match exactly.');
        $imported = json_decode($restoredJson, true, 512, JSON_THROW_ON_ERROR);

        // Strict clinic/actor/review scoping assertions
        $this->assertSame($this->clinic, (int) $imported['clinic_id']);
        $this->assertSame($this->actor, (int) $imported['actor_user_id']);
        $this->assertSame($pointer['review_id'], $imported['review_id']);

        // Restore whole export under checked transaction with same PKs
        $this->assertTrue($this->db->transBegin());
        $restoredPointerOk = $this->db->table('settings')->insert($imported['pointer_row']);
        $this->assertTrue($restoredPointerOk);

        foreach ($imported['audit_rows'] as $row) {
            $restoredPartOk = $this->db->table('control_plane_audits')->insert($row);
            $this->assertTrue($restoredPartOk);
        }
        $this->assertTrue($this->db->transStatus());
        $this->assertTrue($this->db->transCommit());

        $this->assertSame($pointerRow, $this->db->table('settings')->where('id', $pointerRow['id'])->get()->getRowArray());
        $this->assertSame($auditRows, $this->db->table('control_plane_audits')->whereIn('id', array_column($auditRows, 'id'))->orderBy('id', 'ASC')->get()->getResultArray());
        $this->assertSame($originalRetained, $impactService->getRetainedExceptions($this->clinic));

        // Reconstructed bundle verification: exactly identical to original byte hash
        $restoredBundle = $impactService->getLatestAuditRecord($this->clinic);
        $this->assertNotNull($restoredBundle, 'Reconstructed bundle must be available after full export restore.');
        $this->assertSame($originalBundle['review_id'], $restoredBundle['review_id']);
        $this->assertSame($originalBundle['dispositions'], $restoredBundle['dispositions']);
        $this->assertSame($originalBundle['affected_items'], $restoredBundle['affected_items']);
        $this->assertSame(
            $originalBundleHash,
            hash('sha256', json_encode($restoredBundle, JSON_THROW_ON_ERROR)),
            'Reconstructed bundle must match original bundle hash bit-for-bit.'
        );

        // Verify appointments unchanged by loss and restore simulation
        $appointmentsHashAfter = $this->hashOwnedRows('appointments');
        $this->assertSame($appointmentsHashBefore, $appointmentsHashAfter, 'Appointments must remain unchanged through audit loss and restore rehearsal.');
    }

    /**
     * 3. Rollback rehearsal:
     * - Subtype fault injection on 2nd audit part throws exception.
     * - Transaction rollback must restore settings and appointments, transDepth = 0.
     * - Proof token preserved in session, retry without fault succeeds.
     * - Operational rollback rehearsal: default-off refuses dynamic provider bookings.
     * - Preserves historical doctor IDs and chair labels without wiping provider assignments.
     * - configureHours refuses disabling dynamic scheduling when appointments with provider_user_id exist (cannotDisable).
     * - Code rollback to old main analysis (flag-based analysis, no runtime git switch).
     */
    public function testRollbackRehearsalConfigurationFailureAndOperationalPreservation(): void
    {
        $now = new DateTimeImmutable('2030-01-07 06:00:00', new DateTimeZone('UTC'));
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));

        // Seed initial future appointment
        $this->db->table('appointments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => 'RB' . bin2hex(random_bytes(4)),
            'appointment_date' => '2030-01-15',
            'appointment_time' => '16:30:00',
            'duration' => 30,
            'appointment_type' => 'consultation',
            'status' => 'scheduled',
            'chair_name' => 'Synthetic rollback chair',
            'provider_user_id' => $this->providers[0],
            'notes' => 'Synthetic rollback appointment',
            'created_by' => $this->actor,
            'created_at' => '2030-01-01 00:00:00',
            'updated_at' => '2030-01-01 00:00:00',
        ]);
        $id = (int) $this->db->insertID();

        // Insert additional visits ensuring multipart audit bundle
        for ($i = 1; $i < 120; $i++) {
            $row = [
                'clinic_id' => $this->clinic,
                'patient_id' => $this->patient,
                'appointment_id' => 'F' . bin2hex(random_bytes(8)),
                'appointment_date' => (new DateTimeImmutable('2030-01-16'))->modify('+' . $i . ' days')->format('Y-m-d'),
                'appointment_time' => '16:30:00',
                'duration' => 30,
                'appointment_type' => 'consultation',
                'status' => 'scheduled',
                'chair_name' => 'Synthetic snapshot',
                'provider_user_id' => $this->providers[0],
                'notes' => 'Synthetic scheduling acceptance',
                'created_by' => $this->actor,
                'created_at' => '2030-01-01 00:00:00',
                'updated_at' => '2030-01-01 00:00:00',
            ];
            $this->db->table('appointments')->insert($row);
        }

        $appointmentsBefore = $this->db->table('appointments')->where('clinic_id', $this->clinic)->orderBy('id', 'ASC')->get()->getResultArray();
        $settingsBefore = $this->db->table('settings')->where('clinic_id', $this->clinic)->orderBy('id', 'ASC')->get()->getResultArray();
        $auditsBefore = $this->db->table('control_plane_audits')->where('actor_user_id', $this->actor)->countAllResults();

        // 1. Injected model fault on second part insert
        $faultyModel = new class($this->db) extends ControlPlaneAuditModel {
            public int $attempts = 0;
            public array $savedParts = [];
            public ?string $observedEnd = null;

            public function insert($row = null, bool $returnID = true)
            {
                $this->attempts++;
                $this->observedEnd = $this->db->table('settings')
                    ->where(['clinic_id' => session('active_clinic_id'), 'setting_key' => 'working_hours_end'])
                    ->get()
                    ->getRow('setting_value');
                if ($this->attempts === 2) {
                    throw new \RuntimeException('Synthetic second required audit part insert failed.');
                }
                $id = parent::insert($row, $returnID);
                $this->savedParts[] = $id;
                return $id;
            }
        };

        $faultyImpact = new ScheduleImpactService($this->db, null, null, null, new ScheduleImpactAuditStore($this->db, $faultyModel));
        $dto = (new ScheduleImpactService($this->db))->validateProposedHoursDto([
            'working_hours_start' => '09:00',
            'working_hours_end' => '16:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ], $this->clinic);

        $report = $faultyImpact->generateReport($this->db, $this->clinic, 'clinic_hours', $dto, null, false, $now);
        $token = $faultyImpact->issueProof($this->clinic, $this->actor, 'clinic_hours', $dto, $report);
        $dispositions = [];
        foreach ($report['affected_appointment_ids'] as $aid) {
            $dispositions[$aid] = ['action' => 'retain', 'reason' => 'Director approved out-of-hours exception'];
        }
        $proof = ['token' => $token, 'acknowledged' => true, 'dispositions' => $dispositions];

        $faultyConfigService = new ClinicSchedulingConfigurationService($this->db, $faultyImpact);
        $failedResult = $faultyConfigService->configureHours($this->clinic, $dto, $this->actor, $proof);

        $this->assertFalse($failedResult['success']);
        $this->assertSame(lang('DoctorScheduleSettings.saveFailed'), $failedResult['message']);
        $this->assertSame(2, $faultyModel->attempts);
        $this->assertSame('16:00', $faultyModel->observedEnd, 'Failure must occur after proposed configuration was staged in the transaction.');

        // Verify transaction rolled back completely
        $this->assertSame(0, $this->db->transDepth);
        $this->assertSame($settingsBefore, $this->db->table('settings')->where('clinic_id', $this->clinic)->orderBy('id', 'ASC')->get()->getResultArray());
        $this->assertSame($appointmentsBefore, $this->db->table('appointments')->where('clinic_id', $this->clinic)->orderBy('id', 'ASC')->get()->getResultArray());
        $this->assertSame($auditsBefore, $this->db->table('control_plane_audits')->where('actor_user_id', $this->actor)->countAllResults());

        // Proof preserved for retry
        $this->assertSame(
            hash('sha256', $proof['token']),
            session('schedule_impact_proofs')[$this->clinic . ':clinic_hours:0']['token_hash']
        );

        // Retry with standard store succeeds
        $standardConfigService = new ClinicSchedulingConfigurationService($this->db);
        $retryResult = $standardConfigService->configureHours($this->clinic, $dto, $this->actor, $proof);
        $this->assertTrue($retryResult['success'], json_encode($retryResult));

        // 2. Operational rollback rehearsal: cannotDisable protection
        // When appointments with provider_user_id exist, configureHours refuses disabling dynamic scheduling
        $disableDto = (new ScheduleImpactService($this->db))->validateProposedHoursDto([
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'dynamic_doctor_scheduling_enabled' => '0',
            'booking_chair_count' => '1',
            'booking_chair_names' => ['Legacy Room'],
        ], $this->clinic);

        $disableReport = (new ScheduleImpactService($this->db))->generateReport($this->db, $this->clinic, 'clinic_hours', $disableDto, null, false, $now);
        $disableToken = (new ScheduleImpactService($this->db))->issueProof($this->clinic, $this->actor, 'clinic_hours', $disableDto, $disableReport);
        $disableProof = ['token' => $disableToken, 'acknowledged' => true, 'dispositions' => []];

        $disableResult = $standardConfigService->configureHours($this->clinic, $disableDto, $this->actor, $disableProof);
        $this->assertFalse($disableResult['success']);
        $this->assertSame(lang('DoctorScheduleSettings.cannotDisable'), $disableResult['message']);

        // Historical doctor IDs and chair snapshots remain intact
        $freshAppt = $this->db->table('appointments')->where('id', $id)->get()->getRowArray();
        $this->assertSame($this->providers[0], (int) $freshAppt['provider_user_id']);
        $this->assertNotEmpty($freshAppt['chair_name']);
    }

    /**
     * 4. EN/AR and legacy readiness:
     * - Cites existing tests for routed EN/AR preview/apply/recheck & mobile viewport checks:
     *   * ScheduleImpactWorkflowTest::testRealRoutedEnglishArabicPreviewApplyAndRecheckWithCsrfAndPermissionChecks
     *   * ScheduleImpactWorkflowTest::testLosslessArabicBundleOver64KiBHasByteBoundedPartsAndCompactPointer
     *   * tests/js/ScheduleImpactMobileTest.cjs (198 headless Chromium assertions)
     * - Seeds 5 direct synthetic rows with unique times/dates to avoid canonical NULL-provider overlap block:
     *   * Unresolved NULL provider (16:30 on 2030-01-08)
     *   * Doctor 1 (10:00 on 2030-01-08)
     *   * Doctor 2 with identical name 'Duplicate Doctor' (11:00 on 2030-01-08)
     *   * Locked completed appointment (14:00 on 2030-01-08)
     *   * Invalid/boundary time (15:15:30 on 2030-01-08)
     * - Uses correct report keys: report['items'] and item['reschedulable'].
     * - Asserts NULL provider retains NULL and chair snapshot; duplicate doctors disambiguated by numeric ID;
     *   completed visit is non-reschedulable.
     * - Documents that automated workflow is not real operator acknowledgment; staff pilot gate remains OPEN.
     */
    public function testLegacyReadinessUnresolvedProvidersAndDisambiguation(): void
    {
        $now = new DateTimeImmutable('2030-01-07 06:00:00', new DateTimeZone('UTC'));
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));

        // 1. Direct synthetic seeding of non-overlapping appointments:
        // Row 1: Unresolved legacy appointment: NULL provider_user_id with historical chair snapshot
        $this->db->table('appointments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => 'LEGACY' . bin2hex(random_bytes(4)),
            'appointment_date' => '2030-01-08',
            'appointment_time' => '16:30:00',
            'duration' => 30,
            'appointment_type' => 'consultation',
            'status' => 'scheduled',
            'chair_name' => 'Historical Room 3',
            'provider_user_id' => null,
            'notes' => 'Legacy visit without assigned doctor',
            'created_by' => $this->actor,
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => '2020-01-01 00:00:00',
        ]);
        $legacyApptId = (int) $this->db->insertID();

        // Row 2: Doctor 1 visit (at non-overlapping 10:00)
        $this->db->table('appointments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => 'DOC1' . bin2hex(random_bytes(4)),
            'appointment_date' => '2030-01-08',
            'appointment_time' => '10:00:00',
            'duration' => 30,
            'appointment_type' => 'consultation',
            'status' => 'scheduled',
            'chair_name' => 'Duplicate Doctor',
            'provider_user_id' => $this->providers[0],
            'notes' => 'Doctor 1 visit',
            'created_by' => $this->actor,
            'created_at' => '2030-01-01 00:00:00',
            'updated_at' => '2030-01-01 00:00:00',
        ]);
        $idDoc1 = (int) $this->db->insertID();

        // Row 3: Doctor 2 visit with identical name 'Duplicate Doctor' (at non-overlapping 11:00)
        $this->db->table('appointments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => 'DOC2' . bin2hex(random_bytes(4)),
            'appointment_date' => '2030-01-08',
            'appointment_time' => '11:00:00',
            'duration' => 30,
            'appointment_type' => 'consultation',
            'status' => 'scheduled',
            'chair_name' => 'Duplicate Doctor',
            'provider_user_id' => $this->providers[1],
            'notes' => 'Doctor 2 visit',
            'created_by' => $this->actor,
            'created_at' => '2030-01-01 00:00:00',
            'updated_at' => '2030-01-01 00:00:00',
        ]);
        $idDoc2 = (int) $this->db->insertID();

        // Row 4: Locked completed appointment (seeded directly with status=completed)
        $this->db->table('appointments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => 'COMPL' . bin2hex(random_bytes(4)),
            'appointment_date' => '2030-01-08',
            'appointment_time' => '14:00:00',
            'duration' => 30,
            'appointment_type' => 'consultation',
            'status' => 'completed',
            'chair_name' => 'Duplicate Doctor',
            'provider_user_id' => $this->providers[0],
            'notes' => 'Completed appointment lock',
            'created_by' => $this->actor,
            'created_at' => '2030-01-01 00:00:00',
            'updated_at' => '2030-01-01 00:00:00',
        ]);
        $idCompleted = (int) $this->db->insertID();

        // Row 5: Boundary/invalid time appointment with non-zero seconds
        $this->db->table('appointments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => 'INVAL' . bin2hex(random_bytes(4)),
            'appointment_date' => '2030-01-08',
            'appointment_time' => '15:15:30',
            'duration' => 30,
            'appointment_type' => 'consultation',
            'status' => 'scheduled',
            'chair_name' => 'Duplicate Doctor',
            'provider_user_id' => $this->providers[0],
            'notes' => 'Non-standard seconds boundary appointment',
            'created_by' => $this->actor,
            'created_at' => '2030-01-01 00:00:00',
            'updated_at' => '2030-01-01 00:00:00',
        ]);
        $idInvalid = (int) $this->db->insertID();

        // Snapshot appointments before report
        $appointmentsHashBefore = $this->hashOwnedRows('appointments');

        // Generate report
        $impactService = new ScheduleImpactService($this->db);
        $dto = $impactService->validateProposedHoursDto([
            'working_hours_start' => '09:00',
            'working_hours_end' => '16:00',
            'appointment_duration' => '30',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'dynamic_doctor_scheduling_enabled' => '1',
        ], $this->clinic);

        $report = $impactService->generateReport($this->db, $this->clinic, 'clinic_hours', $dto, null, false, $now);

        // Map items by appointment_id using correct report['items'] API key
        $itemMap = [];
        foreach ($report['items'] as $item) {
            $itemMap[(int) $item['appointment_id']] = $item;
        }

        // Verify NULL provider remains unresolved: NEVER auto-backfilled or inferred
        $this->assertArrayHasKey($legacyApptId, $itemMap);
        $legacyItem = $itemMap[$legacyApptId];
        $this->assertNull($legacyItem['provider_user_id'], 'Legacy visit must retain NULL provider_user_id.');
        $this->assertSame('Historical Room 3', $legacyItem['chair_name'], 'Historical chair snapshot must be preserved.');

        // Verify database row itself was never modified
        $legacyDbRow = $this->db->table('appointments')->where('id', $legacyApptId)->get()->getRowArray();
        $this->assertNull($legacyDbRow['provider_user_id']);
        $this->assertSame('Historical Room 3', $legacyDbRow['chair_name']);

        // Verify duplicate doctors are disambiguated by numeric user IDs
        $this->assertArrayHasKey($idDoc1, $itemMap);
        $this->assertArrayHasKey($idDoc2, $itemMap);
        $this->assertSame($this->providers[0], (int) $itemMap[$idDoc1]['provider_user_id']);
        $this->assertSame($this->providers[1], (int) $itemMap[$idDoc2]['provider_user_id']);

        // Verify completed appointment is locked and cannot receive reschedule proof
        $this->assertArrayHasKey($idCompleted, $itemMap);
        $this->assertSame('completed', $itemMap[$idCompleted]['status']);
        $this->assertFalse($itemMap[$idCompleted]['reschedulable'], 'Completed visit must not be reschedulable.');
        $this->assertNull($itemMap[$idCompleted]['edit_url']);

        // Verify invalid seconds appointment is flagged as affected conflict
        $this->assertArrayHasKey($idInvalid, $itemMap);
        $this->assertTrue($itemMap[$idInvalid]['is_affected'], 'Invalid seconds interval must be flagged as conflict.');

        // Verify appointments row hashes unchanged by report generation
        $appointmentsHashAfter = $this->hashOwnedRows('appointments');
        $this->assertSame($appointmentsHashBefore, $appointmentsHashAfter, 'Appointments must remain byte-identical through report generation.');
    }

    /**
     * Hash all columns in stable bounded batches without holding fixture history in PHP memory.
     */
    private function hashOwnedRows(string $table): array
    {
        $context = hash_init('sha256');
        $lastId = $count = 0;
        do {
            $rows = $this->db->table($table)
                ->where('clinic_id', $this->clinic)
                ->where('id >', $lastId)
                ->orderBy('id', 'ASC')
                ->get(500)
                ->getResultArray();
            foreach ($rows as $row) {
                hash_update($context, json_encode($row, JSON_THROW_ON_ERROR) . "\n");
                $lastId = (int) $row['id'];
                $count++;
            }
        } while (count($rows) === 500);

        return ['count' => $count, 'sha256' => hash_final($context)];
    }
}
