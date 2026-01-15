<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunTenantJob extends BaseCommand
{
    protected $group       = 'SaaS';
    protected $name        = 'tenant:run-job';
    protected $description = 'Runs a tenant-aware background job.';
    protected $usage       = 'tenant:run-job [job_name] --clinic-id [id]';
    protected $arguments   = [
        'job_name'  => 'The name of the job to run (e.g. ExportReportsJob)',
    ];
    protected $options     = [
        '--clinic-id'  => 'REQUIRED: The ID of the clinic context',
        '--start-date' => 'Optional start date for reports',
        '--end-date'   => 'Optional end date for reports'
    ];

    public function run(array $params)
    {
        $jobName = $params[0] ?? 'UnknownJob';
        $clinicId = CLI::getOption('clinic-id');
        $auditModel = new \App\Models\JobAuditModel();
        
        // Start audit (even if we fail fast)
        $auditId = $auditModel->startAudit($jobName, $clinicId ? (int)$clinicId : null);

        // Check for forbidden user overrides
        $forbiddenOptions = ['user-id', 'user', 'acting-user', 'member-id'];
        foreach ($forbiddenOptions as $opt) {
            if (CLI::getOption($opt)) {
                $error = "REJECTED: Per-user overrides (--{$opt}) are not allowed in this execution model.";
                CLI::error($error);
                $auditModel->finishAudit($auditId, 'fail', $error);
                exit(1);
            }
        }

        if (!$clinicId) {
            $error = "FAIL-CLOSED: --clinic-id is REQUIRED for tenant-aware jobs.";
            CLI::error($error);
            $auditModel->finishAudit($auditId, 'fail', $error);
            exit(1);
        }

        $className = "\\App\\Jobs\\" . $jobName;
        if (!class_exists($className)) {
            $error = "Job class {$className} not found.";
            CLI::error($error);
            $auditModel->finishAudit($auditId, 'fail', $error);
            exit(1);
        }

        try {
            CLI::write("Initializing job {$jobName} for Clinic ID: {$clinicId}...");
            
            /** @var \App\Libraries\TenantJob $job */
            $job = new $className((int)$clinicId);
            
            $jobParams = [
                'start_date' => CLI::getOption('start-date'),
                'end_date'   => CLI::getOption('end-date')
            ];

            CLI::write("Running job...");
            $result = $job->run($jobParams);

            CLI::write("Job completed successfully.", 'green');
            
            $attachmentIds = [];
            if (isset($result['id'])) {
                $attachmentIds[] = $result['id'];
                CLI::write("Attachment created with ID: " . $result['id']);
            }

            $auditModel->finishAudit($auditId, 'success', null, $attachmentIds);

        } catch (\Exception $e) {
            CLI::error("Job failed: " . $e->getMessage());
            $auditModel->finishAudit($auditId, 'fail', $e->getMessage());
            log_message('error', "CLI Job {$jobName} failed for clinic {$clinicId}: " . $e->getMessage());
            exit(1);
        }
    }
}
