<?php

declare(strict_types=1);
namespace Tests\Unit;

use CodeIgniter\I18n\Time;
use Tests\Support\PhaseBFixture;

/** Local certification only: real child mutators, synthetic clinics and owned test DB. */
final class PR3StagingConcurrencyTest extends PhaseBFixture
{
    private array $extraRoles = [];

    protected function tearDown(): void
    {
        while ($this->db->transDepth > 0) { $this->db->transRollback(); }
        foreach ($this->extraRoles as $role) {
            $this->db->table('clinic_users')->where('role_id', $role)->delete();
            $this->db->table('role_permissions')->where('role_id', $role)->delete();
            $this->db->table('roles')->where('id', $role)->delete();
        }
        Time::setTestNow();
        parent::tearDown();
    }

    public function testEightBookingsWithSharedGlobalAuthorityRemainTenantOwned(): void
    {
        $this->runWorkload(true);
    }

    public function testDisjointClinicCompletesWhileOtherClinicMutexIsHeld(): void
    {
        $this->runWorkload(false);
    }

    private function runWorkload(bool $shared): void
    {
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));
        // Only fixture-owned roles are modified. Product defaults remain untouched.
        $this->db->table('roles')->where('id', $this->role)->update(['clinic_id' => $shared ? null : $this->clinic, 'is_doctor' => 1]);
        $this->db->table('clinic_users')->where('clinic_id', $this->clinic)->whereIn('user_id', $this->providers)->update(['role_id' => $this->role]);
        $actorB = $this->newUser('Other clinic operator');
        $roleB = $this->role;
        if (!$shared) {
            $this->db->table('roles')->insert(['name' => $this->prefix . '_other', 'slug' => $this->prefix . '_other',
                'clinic_id' => $this->otherClinic, 'is_active' => 1, 'is_system' => 0, 'is_doctor' => 1, 'created_by' => $actorB]);
            $roleB = (int) $this->db->insertID(); $this->extraRoles[] = $roleB;
            foreach ($this->db->table('role_permissions')->where('role_id', $this->role)->get()->getResultArray() as $grant) {
                unset($grant['id']); $grant['role_id'] = $roleB; $this->db->table('role_permissions')->insert($grant);
            }
        }
        $this->membership($actorB, $this->otherClinic, $roleB);
        $providersB = [];
        foreach ($this->providers as $provider) {
            $providerB = $shared ? $provider : $this->newUser('Other clinic duplicate');
            $providersB[] = $providerB;
            $this->membership($providerB, $this->otherClinic, $roleB);
        }
        foreach ($this->db->table('settings')->where('clinic_id', $this->clinic)->get()->getResultArray() as $setting) {
            unset($setting['id']); $setting['clinic_id'] = $this->otherClinic; $this->db->table('settings')->insert($setting);
        }
        $this->db->table('patients')->insert(['clinic_id' => $this->otherClinic, 'patient_id' => $this->prefix . '_b',
            'first_name' => 'Synthetic', 'last_name' => 'Other patient', 'gender' => 'other', 'status' => 'active', 'created_by' => $actorB]);
        $patientB = (int) $this->db->insertID();
        $emailB = $this->db->table('users')->where('id', $actorB)->get()->getRow('email');
        $patientsBefore = $this->db->table('patients')->whereIn('clinic_id', [$this->clinic, $this->otherClinic])->orderBy('id')->get()->getResultArray();
        $cases = [];
        foreach ([[$this->clinic, $this->actor, $this->email, $this->patient, $this->providers],
            [$this->otherClinic, $actorB, $emailB, $patientB, $providersB]] as [$clinic, $actor, $email, $patient, $providers]) {
            foreach ($providers as $i => $provider) {
                $cases[] = ['operation' => 'booking', 'clinic' => $clinic, 'actor' => $actor, 'email' => $email,
                    'payload' => $this->payload(['patient_id' => $patient, 'provider_user_id' => $provider,
                        'appointment_date' => '2030-01-08', 'appointment_time' => sprintf('%02d:00', 10 + $i)])];
            }
        }
        $directory = ROOTPATH . 'tmp/pr3-staging-workers-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700, true);
        $processes = $pipes = $metrics = $completed = [];
        $started = microtime(true);
        try {
            $this->db->transBegin();
            if ($shared) {
                // Eight live mutations enter clinic barriers; each clinic head contends on the shared role.
                $this->db->query('SELECT id FROM roles WHERE id=? FOR UPDATE', [$this->role]);
            } else {
                $this->db->query('SELECT id FROM clinics WHERE id=? FOR UPDATE', [$this->clinic]);
            }
            foreach ($cases as $i => $case) {
                file_put_contents($directory . '/input' . $i . '.json', json_encode($case, JSON_THROW_ON_ERROR));
                $metrics[$i] = ['clinic_id' => $case['clinic'], 'actor_id' => $case['actor'], 'started_at' => microtime(true),
                    'expected_provider_id' => $case['payload']['provider_user_id'], 'expected_patient_id' => $case['payload']['patient_id']];
                $processes[$i] = proc_open([PHP_BINARY, ROOTPATH . 'tests/_support/pr3_lock_order_worker.php',
                    $directory . '/input' . $i . '.json', $directory . '/probe' . $i, $directory . '/ready' . $i],
                    [0 => ['pipe', 'r'], 1 => ['file', $directory . '/stdout' . $i, 'w'],
                        2 => ['file', $directory . '/stderr' . $i, 'w']], $pipes[$i], ROOTPATH);
                $this->assertIsResource($processes[$i]); fclose($pipes[$i][0]);
            }
            $deadline = microtime(true) + 6;
            do {
                clearstatcache(); $allReady = true;
                foreach ($processes as $i => $process) {
                    $sql = is_file($directory . '/probe' . $i) ? file_get_contents($directory . '/probe' . $i) : '';
                    $allReady = $allReady && is_file($directory . '/ready' . $i) && stripos($sql, 'clinics') !== false;
                }
                if ($allReady) { break; }
                usleep(10000);
            } while (microtime(true) < $deadline);
            $this->assertTrue($allReady, 'Eight actual child booking mutations must reach their clinic SQL barriers.');
            if (!$shared) {
                // Clinic B has disjoint fixture users/roles. It must finish while A remains locked.
                foreach ([4, 5, 6, 7] as $i) {
                    $completed[$i] = $this->finishWorker($processes, $i, $directory, $metrics);
                    $this->assertTrue($completed[$i]['success'] ?? false, json_encode($completed[$i]));
                }
                foreach ([0, 1, 2, 3] as $i) {
                    $status = proc_get_status($processes[$i]);
                    $this->assertTrue($status['running'], 'Clinic A booking must still wait for its held clinic mutex.');
                }
                // Change clinic-A hours under that same mutex. Queued 10:00 must revalidate and fail.
                $this->db->table('settings')->where(['clinic_id' => $this->clinic, 'setting_key' => 'working_hours_start'])->update(['setting_value' => '11:00']);
            }
            $this->db->transCommit();
            foreach (array_keys($processes) as $i) { $completed[$i] = $this->finishWorker($processes, $i, $directory, $metrics); }
            foreach ($cases as $i => $case) {
                if (!$shared && $i === 0) {
                    $this->assertFalse($completed[$i]['success'] ?? true, json_encode($completed[$i]));
                    $this->assertSame('OUTSIDE_WORKING_HOURS', $completed[$i]['code'] ?? null);
                    continue;
                }
                $this->assertTrue($completed[$i]['success'] ?? false, json_encode($completed[$i]));
                $id = (int) $completed[$i]['id'];
                $row = $this->db->table('appointments')->where('id', $id)->get()->getRowArray();
                $this->assertSame($case['clinic'], (int) $row['clinic_id']);
                $this->assertSame($case['payload']['provider_user_id'], (int) $row['provider_user_id']);
                $this->assertSame($case['payload']['patient_id'], (int) $row['patient_id']);
                $this->assertSame(1, $this->db->table('activity_logs')->where(['clinic_id' => $case['clinic'], 'user_id' => $case['actor'],
                    'action' => 'create', 'entity_type' => 'appointment', 'entity_id' => $id])->countAllResults());
            }
            $this->assertSame($shared ? 4 : 3, $this->db->table('appointments')->where('clinic_id', $this->clinic)->countAllResults());
            $this->assertSame(4, $this->db->table('appointments')->where('clinic_id', $this->otherClinic)->countAllResults());
            $this->assertSame($patientsBefore, $this->db->table('patients')->whereIn('clinic_id', [$this->clinic, $this->otherClinic])->orderBy('id')->get()->getResultArray());
        } finally {
            while ($this->db->transDepth > 0) { $this->db->transRollback(); }
            foreach ($processes as $process) { proc_terminate($process); proc_close($process); }
            foreach ($metrics as $i => &$metric) {
                $metric['lock_sql'] = is_file($directory . '/probe' . $i) ? file_get_contents($directory . '/probe' . $i) : '';
                $metric['stdout'] = is_file($directory . '/stdout' . $i) ? file_get_contents($directory . '/stdout' . $i) : '';
                $metric['stderr'] = is_file($directory . '/stderr' . $i) ? file_get_contents($directory . '/stderr' . $i) : '';
            }
            unset($metric);
            $path = ROOTPATH . 'tmp/pr3-staging-concurrency.json';
            $existing = is_file($path) ? json_decode(file_get_contents($path), true) : [];
            $existing[$shared ? 'shared_global_authority' : 'disjoint_clinic_mutex_isolation'] = ['elapsed_seconds' => microtime(true) - $started,
                'workers' => $metrics, 'results' => $completed, 'scope' => 'Synthetic owned loopback DB; no claim of global deadlock immunity.'];
            file_put_contents($path, json_encode($existing, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            foreach (glob($directory . '/*') ?: [] as $file) { if (is_file($file)) unlink($file); }
            rmdir($directory);
        }
    }

    private function finishWorker(array &$processes, int $i, string $directory, array &$metrics): array
    {
        $process = $processes[$i]; $deadline = microtime(true) + 15;
        do {
            $status = proc_get_status($process);
            if (!$status['running']) break;
            usleep(10000);
        } while (microtime(true) < $deadline);
        $this->assertFalse($status['running'], 'Finite worker deadline exceeded.');
        $exitCode = proc_close($process); unset($processes[$i]);
        $out = file_get_contents($directory . '/stdout' . $i); $err = file_get_contents($directory . '/stderr' . $i);
        $metrics[$i]['elapsed_seconds'] = microtime(true) - $metrics[$i]['started_at'];
        $metrics[$i]['exit_code'] = $status['exitcode'] >= 0 ? $status['exitcode'] : $exitCode;
        $this->assertSame(0, $metrics[$i]['exit_code'], $err . ' ' . $out);
        $result = json_decode($out, true); $this->assertIsArray($result, $err . ' ' . $out);
        return $result;
    }
}
