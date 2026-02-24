<?php

namespace App\Jobs;

use App\Libraries\TenantJob;
use App\Services\NotificationService;
use CodeIgniter\CLI\CLI;

class SendEmailNotificationsJob extends TenantJob
{
    public function run(array $params = [])
    {
        CLI::write("Starting Email Dispatch for Clinic: " . $this->clinicId, 'yellow');
        CLI::write("DEPRECATED: Use notifications:dispatch --clinic-id {$this->clinicId} --channel email (Phase 2 unified dispatcher).", 'light_red');

        $service = new NotificationService();
        
        try {
            $result = $service->dispatchPendingEmails($this->clinicId);
            
            CLI::write("Dispatch Summary:", 'cyan');
            CLI::write("- Processed: " . $result['processed']);
            CLI::write("- Sent: " . $result['sent'], 'green');
            CLI::write("- Failed: " . $result['failed'], 'red');
            CLI::write("- Blocked: " . $result['blocked'], 'yellow');
            
            return $result;
        } catch (\Exception $e) {
            CLI::error("Dispatch Error: " . $e->getMessage());
            throw $e;
        }
    }
}
