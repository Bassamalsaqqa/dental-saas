<?php

namespace App\Commands;

use App\Models\NotificationAttemptModel;
use App\Models\WhatsAppTenantPolicyModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WhatsAppOpsCheck extends BaseCommand
{
    protected $group = 'SaaS';
    protected $name = 'whatsapp:ops-check';
    protected $description = 'Phase 6 readiness check for WhatsApp delivery health with threshold gates.';
    protected $usage = 'whatsapp:ops-check [--hours 24] [--clinic-id 19] [--min-attempts 10] [--max-failure-rate 30] [--max-rate-limited 10] [--max-stuck 0] [--json]';
    protected $options = [
        '--hours' => 'Rolling window in hours (default: 24).',
        '--clinic-id' => 'Optional tenant clinic scope. Omit for global control-plane scope.',
        '--min-attempts' => 'Minimum attempts before failure-rate gate applies (default: 10).',
        '--max-failure-rate' => 'Maximum allowed failure rate percent before FAIL (default: 30).',
        '--max-rate-limited' => 'Maximum allowed rate-limited attempts in window (default: 10).',
        '--max-stuck' => 'Maximum allowed stale processing locks in window (default: 0).',
        '--json' => 'Emit JSON summary for automation.',
    ];

    public function run(array $params)
    {
        $hours = max(1, min(168, (int) (CLI::getOption('hours') ?? 24)));
        $clinicId = (int) (CLI::getOption('clinic-id') ?? 0);
        $minAttempts = max(0, (int) (CLI::getOption('min-attempts') ?? 10));
        $maxFailureRate = max(0, min(100, (float) (CLI::getOption('max-failure-rate') ?? 30)));
        $maxRateLimited = max(0, (int) (CLI::getOption('max-rate-limited') ?? 10));
        $maxStuck = max(0, (int) (CLI::getOption('max-stuck') ?? 0));
        $asJson = CLI::getOption('json') !== null;

        $windowSince = date('Y-m-d H:i:s', time() - ($hours * 3600));
        $attemptModel = new NotificationAttemptModel();
        $policyModel = new WhatsAppTenantPolicyModel();

        $total = $this->countAttempts($attemptModel, $windowSince, $clinicId, null);
        $failed = $this->countAttempts($attemptModel, $windowSince, $clinicId, ['failed', 'failed_permanent']);
        $rateLimited = $this->countAttempts($attemptModel, $windowSince, $clinicId, ['rate_limited']);
        $sentish = $this->countAttempts($attemptModel, $windowSince, $clinicId, ['sent', 'delivered', 'read']);
        $staleLocks = $this->countStaleLocks($windowSince, $clinicId);
        $suspendedClinics = $this->countSuspendedClinics($policyModel, $clinicId);

        $failureRate = $total > 0 ? round(($failed / $total) * 100, 2) : 0.0;

        $checks = [
            'failure_rate' => [
                'pass' => $total < $minAttempts || $failureRate <= $maxFailureRate,
                'value' => $failureRate,
                'threshold' => $maxFailureRate,
                'note' => $total < $minAttempts
                    ? 'not evaluated (attempts below min-attempts threshold)'
                    : 'evaluated',
            ],
            'rate_limited' => [
                'pass' => $rateLimited <= $maxRateLimited,
                'value' => $rateLimited,
                'threshold' => $maxRateLimited,
                'note' => 'evaluated',
            ],
            'stale_locks' => [
                'pass' => $staleLocks <= $maxStuck,
                'value' => $staleLocks,
                'threshold' => $maxStuck,
                'note' => 'evaluated',
            ],
        ];

        $overallPass = $checks['failure_rate']['pass']
            && $checks['rate_limited']['pass']
            && $checks['stale_locks']['pass'];

        $summary = [
            'window_since' => $windowSince,
            'window_hours' => $hours,
            'clinic_id' => $clinicId > 0 ? $clinicId : null,
            'metrics' => [
                'attempts_total' => $total,
                'attempts_sentish' => $sentish,
                'attempts_failed' => $failed,
                'attempts_rate_limited' => $rateLimited,
                'stale_processing_locks' => $staleLocks,
                'suspended_clinics' => $suspendedClinics,
                'failure_rate_percent' => $failureRate,
            ],
            'thresholds' => [
                'min_attempts' => $minAttempts,
                'max_failure_rate_percent' => $maxFailureRate,
                'max_rate_limited' => $maxRateLimited,
                'max_stale_locks' => $maxStuck,
            ],
            'checks' => $checks,
            'status' => $overallPass ? 'PASS' : 'FAIL',
        ];

        if ($asJson) {
            CLI::write(json_encode($summary, JSON_UNESCAPED_SLASHES));
        } else {
            CLI::write('WhatsApp Ops Check', 'cyan');
            CLI::write('- Scope: ' . ($clinicId > 0 ? 'clinic_id=' . $clinicId : 'global'));
            CLI::write('- Window: last ' . $hours . 'h (since ' . $windowSince . ')');
            CLI::write('- Total attempts: ' . $total);
            CLI::write('- Sent-ish: ' . $sentish);
            CLI::write('- Failed: ' . $failed);
            CLI::write('- Rate-limited: ' . $rateLimited);
            CLI::write('- Stale processing locks: ' . $staleLocks);
            CLI::write('- Suspended clinics in scope: ' . $suspendedClinics);
            CLI::write('- Failure rate: ' . $failureRate . '%');
            CLI::newLine();

            foreach ($checks as $key => $check) {
                $status = $check['pass'] ? 'PASS' : 'FAIL';
                $color = $check['pass'] ? 'green' : 'red';
                CLI::write(
                    sprintf(
                        '[%s] %s: value=%s threshold=%s (%s)',
                        $status,
                        $key,
                        (string) $check['value'],
                        (string) $check['threshold'],
                        $check['note']
                    ),
                    $color
                );
            }

            CLI::newLine();
            CLI::write(
                'Overall status: ' . ($overallPass ? 'PASS' : 'FAIL'),
                $overallPass ? 'green' : 'red'
            );
        }

        return $overallPass ? EXIT_SUCCESS : EXIT_ERROR;
    }

    private function countAttempts(NotificationAttemptModel $model, string $since, int $clinicId, ?array $statuses): int
    {
        $query = $model->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->where('created_at >=', $since);

        if ($clinicId > 0) {
            $query->where('clinic_id', $clinicId);
        }

        if (is_array($statuses) && !empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        return (int) $query->countAllResults();
    }

    private function countStaleLocks(string $since, int $clinicId): int
    {
        $staleMinutesRaw = env('notifications.dispatch_lock_stale_minutes');
        $staleMinutes = is_numeric($staleMinutesRaw) ? (int) $staleMinutesRaw : 5;
        $staleMinutes = max(1, min($staleMinutes, 60));

        $db = \Config\Database::connect();
        $query = $db->table('notifications')
            ->where('channel_type', 'whatsapp')
            ->where('status', 'processing')
            ->where('created_at >=', $since)
            ->where('locked_at IS NOT NULL', null, false)
            ->where('locked_at < DATE_SUB(NOW(), INTERVAL ' . $staleMinutes . ' MINUTE)', null, false);

        if ($clinicId > 0) {
            $query->where('clinic_id', $clinicId);
        }

        return (int) $query->countAllResults();
    }

    private function countSuspendedClinics(WhatsAppTenantPolicyModel $policyModel, int $clinicId): int
    {
        $query = $policyModel->withoutTenantScope()->where('suspended', 1);

        if ($clinicId > 0) {
            $query->where('clinic_id', $clinicId);
        }

        return (int) $query->countAllResults();
    }
}

