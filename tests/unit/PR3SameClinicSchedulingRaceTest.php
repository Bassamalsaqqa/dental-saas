<?php

declare(strict_types=1);
namespace Tests\Unit;

use CodeIgniter\I18n\Time;
use Tests\Support\PhaseBFixture;

/** A booking queued behind a schedule apply must see the committed new hours. */
final class PR3SameClinicSchedulingRaceTest extends PhaseBFixture
{
    protected function tearDown(): void
    {
        while ($this->db->transDepth > 0) { $this->db->transRollback(); }
        $this->db->table('control_plane_audits')->whereIn('actor_user_id', $this->users)->delete();
        Time::setTestNow();
        parent::tearDown();
    }

    public function testQueuedBookingRevalidatesAfterClinicHoursCommit(): void
    {
        Time::setTestNow(Time::parse('2030-01-07 06:00:00', 'UTC'));
        $directory = ROOTPATH . 'tmp/pr3-same-clinic-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700, true);
        $cases = [
            ['operation' => 'hours', 'actor' => $this->actor, 'email' => $this->email, 'clinic' => $this->clinic,
                'payload' => ['working_hours_start' => '11:00', 'working_hours_end' => '17:00', 'appointment_duration' => '30',
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                    'dynamic_doctor_scheduling_enabled' => '1']],
            ['operation' => 'booking', 'actor' => $this->actor, 'email' => $this->email, 'clinic' => $this->clinic,
                'payload' => $this->payload(['appointment_date' => '2030-01-08', 'appointment_time' => '10:00'])],
        ];
        $processes = $pipes = $outputs = [];
        try {
            $this->db->transBegin();
            $this->db->query('SELECT id FROM roles WHERE id=? FOR UPDATE', [$this->role]);
            // Start hours first. Its blocked role query proves it already owns the clinic mutex.
            foreach ($cases as $i => $case) {
                file_put_contents($directory . '/input' . $i . '.json', json_encode($case, JSON_THROW_ON_ERROR));
                $processes[$i] = proc_open([PHP_BINARY, ROOTPATH . 'tests/_support/pr3_lock_order_worker.php',
                    $directory . '/input' . $i . '.json', $directory . '/probe' . $i, $directory . '/ready' . $i],
                    [0 => ['pipe', 'r'], 1 => ['file', $directory . '/stdout' . $i, 'w'],
                        2 => ['file', $directory . '/stderr' . $i, 'w']], $pipes[$i], ROOTPATH);
                $this->assertIsResource($processes[$i]);
                fclose($pipes[$i][0]);
                $deadline = microtime(true) + 6;
                $entered = false;
                do {
                    clearstatcache();
                    $status = proc_get_status($processes[$i]);
                    if (!$status['running']) {
                        throw new \RuntimeException('Worker exited before deterministic barrier: '
                            . file_get_contents($directory . '/stderr' . $i) . ' ' . file_get_contents($directory . '/stdout' . $i));
                    }
                    $queries = '';
                    foreach (is_file($directory . '/probe' . $i) ? file($directory . '/probe' . $i, FILE_IGNORE_NEW_LINES) : [] as $line) {
                        $entry = json_decode($line, true);
                        $queries .= ' ' . ($entry['sql'] ?? '');
                    }
                    $entered = is_file($directory . '/ready' . $i)
                        && ($i === 0 ? preg_match('/\bFROM\s+roles\b/i', $queries) === 1 : preg_match('/\bFROM\s+clinics\b/i', $queries) === 1);
                    if ($entered) { break; }
                    usleep(10000);
                } while (microtime(true) < $deadline);
                $this->assertTrue($entered, 'Actual mutator must reach its deterministic SQL barrier.');
            }
            // Both workers are now live: hours owns clinic and waits role, booking waits clinic.
            $this->db->transCommit();
            $results = [];
            foreach ($processes as $i => $process) {
                $deadline = microtime(true) + 15;
                do {
                    $status = proc_get_status($process);
                    if (!$status['running']) { break; }
                    usleep(10000);
                } while (microtime(true) < $deadline);
                $this->assertFalse($status['running'], 'Scheduling worker exceeded finite deadline.');
                $out = file_get_contents($directory . '/stdout' . $i);
                $err = file_get_contents($directory . '/stderr' . $i);
                $exitCode = proc_close($process); unset($processes[$i]);
                $outputs[$i] = ['stdout' => $out, 'stderr' => $err];
                $this->assertSame(0, $status['exitcode'] >= 0 ? $status['exitcode'] : $exitCode, $err . ' ' . $out);
                $results[$i] = json_decode($out, true);
                $this->assertIsArray($results[$i], $err . ' ' . $out);
            }
            $this->assertTrue($results[0]['success'] ?? false, json_encode($results));
            $this->assertFalse($results[1]['success'] ?? true, json_encode($results));
            $this->assertSame('OUTSIDE_WORKING_HOURS', $results[1]['code'] ?? null, json_encode($results));
            $this->assertSame('11:00', $this->db->table('settings')->where(['clinic_id' => $this->clinic, 'setting_key' => 'working_hours_start'])->get()->getRow('setting_value'));
            $this->assertSame(0, $this->db->table('appointments')->where('clinic_id', $this->clinic)->countAllResults());
            $this->assertSame(1, $this->db->table('control_plane_audits')->where(['actor_user_id' => $this->actor,
                'event_type' => 'doctor_scheduling_settings_update'])->countAllResults());
            $this->assertGreaterThan(0, $this->db->table('control_plane_audits')->where(['actor_user_id' => $this->actor,
                'event_type' => 'schedule_impact_bundle_part'])->countAllResults());
        } catch (\Throwable $e) {
            $diagnostic = ['failure' => $e->getMessage(), 'outputs' => $outputs, 'workers' => []];
            foreach ([0, 1] as $i) {
                foreach (['stdout', 'stderr', 'probe', 'ready'] as $file) {
                    $path = $directory . '/' . $file . $i;
                    $diagnostic['workers'][$i][$file] = is_file($path) ? file_get_contents($path) : null;
                }
            }
            file_put_contents(ROOTPATH . 'tmp/pr3-same-clinic-debug.json', json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            throw $e;
        } finally {
            while ($this->db->transDepth > 0) { $this->db->transRollback(); }
            foreach ($processes as $process) { proc_terminate($process); proc_close($process); }
            foreach (glob($directory . '/*') ?: [] as $path) { if (is_file($path)) { unlink($path); } }
            rmdir($directory);
        }
    }
}
