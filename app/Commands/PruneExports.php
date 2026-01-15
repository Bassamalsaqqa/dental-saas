<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PruneExports extends BaseCommand
{
    protected $group       = 'SaaS';
    protected $name        = 'exports:prune';
    protected $description = 'Physically deletes soft-deleted/expired export files from disk across all clinics.';
    protected $usage       = 'exports:prune';

    public function run(array $params)
    {
        CLI::write("Starting physical pruning of export files...", 'yellow');
        
        try {
            $retentionService = new \App\Services\RetentionService();
            $count = $retentionService->physicalCleanup();

            CLI::write("SUCCESS: {$count} files were permanently removed from disk.", 'green');
            log_message('info', "CLI exports:prune completed. Removed {$count} files.");
        } catch (\Exception $e) {
            CLI::error("ERROR during pruning: " . $e->getMessage());
            log_message('error', "CLI exports:prune failed: " . $e->getMessage());
        }
    }
}
