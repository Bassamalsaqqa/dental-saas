<?php

namespace App\Commands;

use App\Libraries\TenantJob;
use App\Services\DispatchService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DispatchNotifications extends BaseCommand
{
    protected $group = 'SaaS';
    protected $name = 'notifications:dispatch';
    protected $description = 'Unified notification dispatcher (Phase 2) for tenant-scoped notification delivery.';
    protected $usage = 'notifications:dispatch --clinic-id [id] [--channel email|whatsapp|sms] [--limit 100]';
    protected $options = [
        '--clinic-id' => 'REQUIRED: clinic context for dispatch.',
        '--channel' => 'Optional channel filter (email|whatsapp|sms).',
        '--limit' => 'Optional max rows to scan (default 100, max 500).',
        '--dispatcher-id' => 'Optional dispatcher instance identifier for lock ownership.',
    ];

    public function run(array $params)
    {
        $clinicId = (int) ($this->readOption('clinic-id') ?? 0);
        $channel = $this->readOption('channel');
        $limit = (int) ($this->readOption('limit') ?? 100);
        $dispatcherId = $this->readOption('dispatcher-id') ?: null;

        if ($clinicId <= 0) {
            CLI::error('FAIL-CLOSED: --clinic-id is REQUIRED.');
            return;
        }

        if ($channel !== null && !in_array($channel, ['email', 'sms', 'whatsapp'], true)) {
            CLI::error('Invalid --channel. Expected one of: email, sms, whatsapp.');
            return;
        }

        // Reuse TenantJob bootstrap behavior without creating a separate wrapper job.
        $bootstrap = new class($clinicId) extends TenantJob {
            public function run(array $params = [])
            {
                return true;
            }
        };
        unset($bootstrap);

        $service = new DispatchService();
        $summary = $service->dispatchPendingForClinic($clinicId, $channel ?: null, $limit, $dispatcherId);

        CLI::write('Unified Dispatch Summary:', 'cyan');
        CLI::write('- Clinic ID: ' . $summary['clinic_id']);
        CLI::write('- Dispatcher ID: ' . $summary['dispatcher_id']);
        CLI::write('- Processed: ' . $summary['processed']);
        CLI::write('- Claimed: ' . $summary['claimed']);
        CLI::write('- Skipped Claim: ' . $summary['skipped_claim']);
        CLI::write('- Attempts Created: ' . $summary['attempts_created']);
        CLI::write('- Sent: ' . $summary['sent'], 'green');
        CLI::write('- Failed: ' . $summary['failed'], 'red');
        CLI::write('- Blocked: ' . $summary['blocked'], 'yellow');

        if (!empty($summary['channels'])) {
            foreach ($summary['channels'] as $name => $stats) {
                CLI::write(sprintf(
                    '  * %s => processed:%d sent:%d failed:%d blocked:%d',
                    $name,
                    $stats['processed'] ?? 0,
                    $stats['sent'] ?? 0,
                    $stats['failed'] ?? 0,
                    $stats['blocked'] ?? 0
                ));
            }
        }
    }

    private function readOption(string $name): ?string
    {
        $value = CLI::getOption($name);

        // CI CLI may parse `--flag value` as boolean true in some cases.
        if (is_string($value) && $value !== '') {
            return $value;
        }

        $argv = $_SERVER['argv'] ?? [];
        $needle = '--' . $name;
        $count = count($argv);

        for ($i = 0; $i < $count; $i++) {
            $arg = (string) $argv[$i];
            if ($arg === $needle) {
                $next = $argv[$i + 1] ?? null;
                if (is_string($next) && !str_starts_with($next, '--')) {
                    return $next;
                }
            }

            if (str_starts_with($arg, $needle . '=')) {
                $parts = explode('=', $arg, 2);
                return $parts[1] ?? null;
            }
        }

        return null;
    }
}
