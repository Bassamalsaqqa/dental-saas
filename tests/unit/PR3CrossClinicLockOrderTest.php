<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\I18n\Time;
use Tests\Support\PhaseBFixture;

/** Cross-clinic operators share a global role and each other's membership. */
final class PR3CrossClinicLockOrderTest extends PhaseBFixture
{
    protected function tearDown(): void
    {
        while ($this->db->transDepth > 0) { $this->db->transRollback(); }
        $this->db->table('control_plane_audits')->whereIn('actor_user_id', $this->users)->delete();
        Time::setTestNow();
        parent::tearDown();
    }

    public function testHoursChangeAndOtherClinicBookingDoNotInvertSharedUserRoleLocks(): void
    {
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));
        // Only this fixture-owned role is changed; no bootstrap role is modified.
        $this->db->table('roles')->where('id', $this->role)->update(['clinic_id' => null, 'is_doctor' => 1]);
        $otherActor = $this->newUser('Other operator');
        $this->membership($otherActor, $this->clinic, $this->role);
        $this->membership($otherActor, $this->otherClinic, $this->role);
        $this->membership($this->actor, $this->otherClinic, $this->role);
        foreach ($this->db->table('settings')->where('clinic_id', $this->clinic)->get()->getResultArray() as $setting) {
            unset($setting['id']);
            $setting['clinic_id'] = $this->otherClinic;
            $this->db->table('settings')->insert($setting);
        }
        $this->db->table('patients')->insert(['clinic_id' => $this->otherClinic, 'patient_id' => $this->prefix . '_other',
            'first_name' => 'Synthetic', 'last_name' => 'Other', 'gender' => 'other', 'status' => 'active', 'created_by' => $otherActor]);
        $otherPatient = (int) $this->db->insertID();
        $otherEmail = $this->db->table('users')->where('id', $otherActor)->get()->getRow('email');
        // The other actor is a genuinely relevant future provider in clinic A; a corrected
        // implementation cannot evade the ordering case by ignoring unused member rows.
        $existing = (new \App\Services\AppointmentSchedulingService($this->db))->createForClinic($this->clinic,
            $this->payload(['provider_user_id' => $otherActor, 'appointment_date' => '2030-01-09', 'appointment_time' => '10:00']), $this->actor);
        $this->assertTrue($existing['success'], json_encode($existing));
        $preserved = $this->db->table('appointments')->where('id', $existing['id'])->get()->getRowArray();
        $directory = ROOTPATH . 'tmp/pr3-shared-locks-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700, true);
        $cases = [
            ['operation' => 'hours', 'actor' => $this->actor, 'email' => $this->email, 'clinic' => $this->clinic,
                'payload' => ['working_hours_start' => '09:00', 'working_hours_end' => '17:00', 'appointment_duration' => '30',
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                    'dynamic_doctor_scheduling_enabled' => '1']],
            ['operation' => 'booking', 'actor' => $otherActor, 'email' => $otherEmail, 'clinic' => $this->otherClinic,
                'payload' => $this->payload(['patient_id' => $otherPatient, 'provider_user_id' => $this->actor,
                    'appointment_date' => '2030-01-08', 'appointment_time' => '10:00'])],
        ];
        $processes = $pipes = $outputs = [];
        try {
            $this->db->transBegin();
            // Force genuine overlap at the shared authority dependency. Workers record query entry,
            // so corrected ordering can block at a user row before it ever reaches this role.
            $this->db->query('SELECT id FROM roles WHERE id=? FOR UPDATE', [$this->role]);
            foreach ($cases as $i => $case) {
                file_put_contents($directory . '/input' . $i . '.json', json_encode($case, JSON_THROW_ON_ERROR));
                $processes[$i] = proc_open([PHP_BINARY, ROOTPATH . 'tests/_support/pr3_lock_order_worker.php',
                    $directory . '/input' . $i . '.json', $directory . '/probe' . $i, $directory . '/ready' . $i],
                    [0 => ['pipe', 'r'], 1 => ['file', $directory . '/stdout' . $i, 'w'],
                        2 => ['file', $directory . '/stderr' . $i, 'w']], $pipes[$i], ROOTPATH);
                $this->assertIsResource($processes[$i]);
                fclose($pipes[$i][0]);
                // Windows anonymous pipes do not reliably support nonblocking reads.
                // Worker output goes to files; the handshake reads only markers/status.
            }
            $deadline = microtime(true) + 6;
            $contended = false;
            do {
                clearstatcache();
                $markers = [];
                foreach ([0, 1] as $i) {
                    $status = proc_get_status($processes[$i]);
                    if (!$status['running']) {
                        $outputs[$i] = ['stdout' => file_get_contents($directory . '/stdout' . $i),
                            'stderr' => file_get_contents($directory . '/stderr' . $i)];
                        throw new \RuntimeException('Worker exited before lock contention: ' . json_encode($outputs[$i]));
                    }
                    $sql = '';
                    foreach (is_file($directory . '/probe' . $i) ? file($directory . '/probe' . $i, FILE_IGNORE_NEW_LINES) : [] as $line) {
                        $entry = json_decode($line, true);
                        $sql .= ' ' . ($entry['sql'] ?? '');
                    }
                    $markers[$i] = ['user' => stripos($sql, 'users') !== false, 'role' => stripos($sql, 'roles') !== false];
                }
                $bothReady = is_file($directory . '/ready0') && is_file($directory . '/ready1');
                $contended = $bothReady && (($markers[0]['role'] && $markers[1]['user']) || ($markers[1]['role'] && $markers[0]['user']));
                if ($contended) { break; }
                usleep(10000);
            } while (microtime(true) < $deadline);
            $this->db->transCommit();
            $this->assertTrue($contended, 'Both actual mutators must enter authority-lock contention before release.');
            foreach ($processes as $i => $process) {
                $deadline = microtime(true) + 15;
                do {
                    $status = proc_get_status($process);
                    if (!$status['running']) { break; }
                    usleep(10000);
                } while (microtime(true) < $deadline);
                $this->assertFalse($status['running'], 'Mutator exceeded finite concurrency deadline.');
                $out = file_get_contents($directory . '/stdout' . $i);
                $err = file_get_contents($directory . '/stderr' . $i);
                $exitCode = proc_close($process); unset($processes[$i]);
                $outputs[$i] = ['stdout' => $out, 'stderr' => $err, 'exitcode' => $status['exitcode'] >= 0 ? $status['exitcode'] : $exitCode];
                $this->assertSame(0, $status['exitcode'] >= 0 ? $status['exitcode'] : $exitCode, $err . ' ' . $out);
                $result = json_decode($out, true);
                $this->assertIsArray($result, $err . ' ' . $out);
                $this->assertTrue($result['success'] ?? false, $err . ' ' . $out);
            }
            $this->assertSame(1, $this->db->table('appointments')->where('clinic_id', $this->clinic)->countAllResults());
            $this->assertSame($preserved, $this->db->table('appointments')->where('id', $existing['id'])->get()->getRowArray());
            $this->assertSame(1, $this->db->table('appointments')->where('clinic_id', $this->otherClinic)->countAllResults());
            $appointment = $this->db->table('appointments')->where('clinic_id', $this->otherClinic)->get()->getRowArray();
            $this->assertSame($this->actor, (int) $appointment['provider_user_id']);
            $this->assertSame($otherPatient, (int) $appointment['patient_id']);
            $this->assertGreaterThan(0, $this->db->table('control_plane_audits')->where('actor_user_id', $this->actor)->countAllResults());
        } catch (\Throwable $e) {
            // Preserve synthetic query/output diagnostics before cleaning transient worker files.
            $diagnostic = ['failure' => $e->getMessage(), 'outputs' => $outputs, 'workers' => []];
            foreach ([0, 1] as $i) {
                if (isset($processes[$i])) {
                    $diagnostic['workers'][$i]['status'] = proc_get_status($processes[$i]);
                }
                $diagnostic['workers'][$i]['stdout'] = is_file($directory . '/stdout' . $i) ? file_get_contents($directory . '/stdout' . $i) : '';
                $diagnostic['workers'][$i]['stderr'] = is_file($directory . '/stderr' . $i) ? file_get_contents($directory . '/stderr' . $i) : '';
                $diagnostic['workers'][$i]['ready'] = is_file($directory . '/ready' . $i);
                $diagnostic['workers'][$i]['probe'] = is_file($directory . '/probe' . $i) ? file_get_contents($directory . '/probe' . $i) : '';
            }
            file_put_contents(ROOTPATH . 'tmp/pr3-lock-debug.json', json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            throw $e;
        } finally {
            while ($this->db->transDepth > 0) { $this->db->transRollback(); }
            foreach ($processes as $process) { proc_terminate($process); proc_close($process); }
            foreach (glob($directory . '/*') ?: [] as $path) { if (is_file($path)) { unlink($path); } }
            rmdir($directory);
        }
    }
}
