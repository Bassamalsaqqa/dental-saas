<?php

namespace App\Jobs;

use App\Libraries\TenantJob;
use App\Services\DispatchService;
use CodeIgniter\CLI\CLI;

class DispatchNotificationsJob extends TenantJob
{
    public function run(array $params = [])
    {
        $channel = $params['channel'] ?? null;
        $limit = (int) ($params['limit'] ?? 100);

        CLI::write('Starting Unified Dispatch for Clinic: ' . $this->clinicId, 'yellow');

        $service = new DispatchService();
        $summary = $service->dispatchPendingForClinic($this->clinicId, $channel ?: null, $limit);

        CLI::write('Unified Dispatch Summary:', 'cyan');
        CLI::write('- Processed: ' . $summary['processed']);
        CLI::write('- Claimed: ' . $summary['claimed']);
        CLI::write('- Skipped Claim: ' . $summary['skipped_claim']);
        CLI::write('- Attempts Created: ' . $summary['attempts_created']);
        CLI::write('- Sent: ' . $summary['sent'], 'green');
        CLI::write('- Failed: ' . $summary['failed'], 'red');
        CLI::write('- Blocked: ' . $summary['blocked'], 'yellow');

        return $summary;
    }
}
