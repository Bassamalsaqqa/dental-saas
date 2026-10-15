<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ScheduleImpactService;
use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use Config\Database;
use Tests\Support\PhaseBFixture;

/** Independent scale and historical-lock acceptance on committed synthetic clinic rows. */
final class PR3HistoricalIsolationTest extends PhaseBFixture
{
    protected function tearDown(): void
    {
        while ($this->db->transDepth > 0) { $this->db->transRollback(); }
        foreach (['clinical_charge_lines', 'examinations', 'finances'] as $table) {
            $this->db->table($table)->whereIn('clinic_id', [$this->clinic, $this->otherClinic])->delete();
        }
        $this->db->table('control_plane_audits')->whereIn('actor_user_id', $this->users)->delete();
        Time::setTestNow();
        parent::tearDown();
    }

    public function testScaleReportDoesNotLockOrMutateUnrelatedHistory(): void
    {
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));
        $this->db->table('patients')->insert(['clinic_id' => $this->clinic, 'patient_id' => 'P'.bin2hex(random_bytes(8)),
            'first_name' => 'Historical', 'last_name' => 'Synthetic', 'gender' => 'other', 'status' => 'active', 'created_by' => $this->actor]);
        $historicalPatient = (int) $this->db->insertID();
        // An established clinic has unrelated patients as well as visit history.
        $patientBatch=[];
        for($i=0;$i<2000;$i++){
            $patientBatch[]=['clinic_id'=>$this->clinic,'patient_id'=>substr($this->prefix,-12).'H'.sprintf('%04d',$i),'first_name'=>'Synthetic','last_name'=>'Historical '.$i,'gender'=>'other','status'=>'active','created_by'=>$this->actor];
            if(count($patientBatch)===500){$this->db->table('patients')->insertBatch($patientBatch);$patientBatch=[];}
        }
        $identity = bin2hex(random_bytes(6));
        $batch = [];
        for ($i = 0; $i < 21000; $i++) {
            $future = $i >= 20000;
            $n = $future ? $i - 20000 : $i;
            $day = $future ? (new \DateTimeImmutable('2030-01-08'))->modify('+' . intdiv($n, 64) . ' days')->format('Y-m-d') : '2020-01-08';
            $slot = $n % 16;
            $batch[] = ['clinic_id' => $this->clinic, 'patient_id' => $future ? $this->patient : $historicalPatient,
                'appointment_id' => ($future ? 'F' : 'H') . $identity . sprintf('%06d', $n),
                'appointment_date' => $day, 'appointment_time' => sprintf('%02d:%02d:00', 9 + intdiv($slot, 2), ($slot % 2) * 30),
                'duration' => 30, 'appointment_type' => 'consultation', 'status' => $future ? 'scheduled' : 'completed',
                'chair_name' => 'Synthetic historical snapshot', 'provider_user_id' => $this->providers[intdiv($n % 64, 16)],
                'notes' => 'Preserved synthetic scale fixture', 'created_by' => $this->actor,
                'created_at' => '2020-01-01 00:00:00', 'updated_at' => '2020-01-01 00:00:00'];
            if (count($batch) === 500) {
                $this->db->table('appointments')->insertBatch($batch); $batch = [];
            }
        }
        $historicalAppointment = (int) $this->db->table('appointments')->where('clinic_id', $this->clinic)
            ->where('appointment_date', '2020-01-08')->orderBy('id')->get(1)->getRow('id');
        $futureAppointment = (int) $this->db->table('appointments')->where('clinic_id', $this->clinic)
            ->where('appointment_date >=', '2030-01-08')->orderBy('id')->get(1)->getRow('id');
        // Representative clinical/financial history, plus one active-horizon encounter dependency.
        $this->db->table('examinations')->insert(['clinic_id' => $this->clinic, 'patient_id' => $historicalPatient,
            'appointment_id' => $historicalAppointment, 'examination_id' => 'E' . $identity, 'examination_date' => '2020-01-08',
            'examination_type' => 'initial', 'chief_complaint' => 'Synthetic preserved history', 'status' => 'completed', 'created_by' => $this->actor]);
        $this->db->table('finances')->insert(['clinic_id' => $this->clinic, 'patient_id' => $historicalPatient,
            'transaction_id' => 'T' . $identity, 'transaction_type' => 'payment', 'amount' => '42.00', 'currency' => 'USD',
            'payment_method' => 'cash', 'payment_status' => 'paid', 'service_type' => 'consultation', 'notes' => 'Preserved synthetic financial record']);
        $historicalIds = $this->db->table('appointments')->select('id')->where('clinic_id', $this->clinic)
            ->where('appointment_date', '2020-01-08')->orderBy('id')->get()->getResultArray();
        $batch = [];
        foreach ($historicalIds as $row) {
            $batch[] = ['clinic_id' => $this->clinic, 'patient_id' => $historicalPatient, 'appointment_id' => (int) $row['id'],
                'procedure_name' => 'Synthetic history', 'charge_date' => '2020-01-08', 'status' => 'completed',
                'notes' => 'Preserved synthetic charge', 'created_by' => $this->actor];
            if (count($batch) === 500) { $this->db->table('clinical_charge_lines')->insertBatch($batch); $batch = []; }
        }
        unset($historicalIds);
        $historicalCharge = (int) $this->db->table('clinical_charge_lines')->where('clinic_id', $this->clinic)->orderBy('id')->get(1)->getRow('id');
        $this->db->table('clinical_charge_lines')->insert(['clinic_id' => $this->clinic, 'patient_id' => $this->patient,
            'appointment_id' => $futureAppointment, 'procedure_name' => 'Active encounter lock', 'charge_date' => '2030-01-08',
            'status' => 'accepted', 'created_by' => $this->actor]);

        $tables = ['appointments', 'patients', 'examinations', 'clinical_procedures', 'clinical_charge_lines', 'finances'];
        $before = [];
        foreach ($tables as $table) { $before[$table] = $this->hashOwnedRows($table); }
        require_once ROOTPATH.'scripts/TestDatabasePreflight.php';
        $probe = Database::connect('tests', false);
        $this->assertSame([], \Scripts\TestDatabasePreflight::validateLiveConnection($probe));
        $this->assertTrue(\Scripts\TestDatabasePreflight::verifyOwnershipForReset($probe)['allowed']);
        $probe->query('SET SESSION innodb_lock_wait_timeout = 1');
        $probeResults = [];
        try {
            $this->db->transBegin();
            $this->db->query('SELECT id FROM clinics WHERE id=? FOR UPDATE', [$this->clinic]);
            (new ScheduleImpactService($this->db))->lockState($this->clinic);
            foreach ([['appointments', $historicalAppointment], ['patients', $historicalPatient], ['clinical_charge_lines', $historicalCharge]] as [$table, $id]) {
                $probe->resetTransStatus();
                $probe->transBegin();
                $started = microtime(true);
                try {
                    $ok = $probe->table($table)->where(['clinic_id' => $this->clinic, 'id' => $id])
                        ->update([$table === 'patients' ? 'first_name' : 'notes' => 'Rolled back isolation probe']);
                    $probeResults[$table] = ['success' => $ok, 'elapsed_seconds' => microtime(true) - $started];
                } catch (\Throwable $e) {
                    $probeResults[$table] = ['success' => false, 'elapsed_seconds' => microtime(true) - $started,
                        'error_code' => $e->getCode(), 'error' => $e->getMessage()];
                } finally {
                    while ($probe->transDepth > 0) { $probe->transRollback(); }
                }
            }
        } finally {
            while ($this->db->transDepth > 0) { $this->db->transRollback(); }
            $this->db->resetTransStatus();
            $probe->close();
        }

        $this->db->transBegin();
        $previewStarted=microtime(true);
        $lockStarted=microtime(true);
        (new ScheduleImpactService($this->db))->lockState($this->clinic);
        $lockElapsed=microtime(true)-$lockStarted;
        $queries = [];
        $listener = static function ($query) use (&$queries): void { $queries[] = $query->getQuery(); };
        Events::on('DBQuery', $listener);
        $started = microtime(true);
        $memoryBefore = memory_get_usage(true);
        try {
            $report = (new ScheduleImpactService($this->db))->generateReport($this->db, $this->clinic, 'clinic_hours', [
                'working_hours_start' => '09:00', 'working_hours_end' => '16:00', 'appointment_duration' => '30',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'dynamic_doctor_scheduling_enabled' => '1']);
        } finally {
            $elapsed = microtime(true) - $started;
            Events::removeListener('DBQuery', $listener);
            $previewElapsed=microtime(true)-$previewStarted;
            while($this->db->transDepth>0){$this->db->transRollback();}
            $this->db->resetTransStatus();
        }
        $queryCount = count($queries);
        $explains = [];
        $seen = [];
        foreach ($queries as $sql) {
            if (preg_match('/^\s*SELECT\b/i', $sql) !== 1 || preg_match('/\bFROM\s+`?(appointments|clinical_charge_lines|examinations|clinical_procedures|patients)`?\b/i', $sql, $matches) !== 1) { continue; }
            $table = $matches[1];
            if (isset($seen[$table])) { continue; }
            $seen[$table] = true;
            $explains[$table] = ['query_sha256' => hash('sha256', $sql), 'query_prefix' => substr($sql, 0, 220),
                'plan' => $this->db->query('EXPLAIN ' . $sql)->getResultArray()];
        }
        $after = [];
        foreach ($tables as $table) { $after[$table] = $this->hashOwnedRows($table); }
        $metrics = ['fixture' => ['historical_appointments' => 20000, 'future_appointments' => 1000, 'historical_charges' => 20000,
                'unrelated_patients' => 2001, 'providers' => count($this->providers)], 'lock_elapsed_seconds' => $lockElapsed, 'locked_preview_elapsed_seconds' => $previewElapsed, 'report_elapsed_seconds' => $elapsed, 'report_query_count' => $queryCount,
            'memory_before_bytes' => $memoryBefore, 'peak_memory_bytes' => memory_get_peak_usage(true),
            'historical_write_probes' => $probeResults, 'before_rows' => $before, 'after_rows' => $after, 'explain' => $explains];
        file_put_contents(ROOTPATH . 'tmp/pr3-scale-metrics.json', json_encode($metrics, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        $this->assertSame(21000, $before['appointments']['count']);
        $this->assertSame(20001, $before['clinical_charge_lines']['count']);
        $this->assertSame(1, $before['examinations']['count']);
        $this->assertSame(1, $before['finances']['count']);
        $this->assertSame(1000, $report['total_future_visits']);
        $this->assertSame($before, $after, 'Report and rolled-back probes must preserve every appointment, clinical and financial row.');
        foreach ($probeResults as $table => $result) { $this->assertTrue($result['success'], $table . ': ' . json_encode($result)); }
        $this->assertLessThanOrEqual(150, $queryCount, 'Fixed-provider full report must batch dependencies, not issue SQL per visit.');
        $this->assertLessThan(15.0, $previewElapsed, '1,000 future visits with 20,000 historical visits should complete within focused local budget.');
    }

    /** Hash all columns in stable bounded batches without holding fixture history in PHP memory. */
    private function hashOwnedRows(string $table): array
    {
        $context = hash_init('sha256');
        $lastId = $count = 0;
        do {
            $rows = $this->db->table($table)->where('clinic_id', $this->clinic)->where('id >', $lastId)->orderBy('id')->get(500)->getResultArray();
            foreach ($rows as $row) {
                hash_update($context, json_encode($row, JSON_THROW_ON_ERROR) . "\n");
                $lastId = (int) $row['id']; $count++;
            }
        } while (count($rows) === 500);
        return ['count' => $count, 'sha256' => hash_final($context)];
    }
}
