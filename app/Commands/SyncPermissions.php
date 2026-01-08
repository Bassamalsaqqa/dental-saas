<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\PermissionSyncService;

class SyncPermissions extends BaseCommand
{
    protected $group       = 'rbac';
    protected $name        = 'rbac:sync';
    protected $description = 'Sync permissions and roles from config to database';

    public function run(array $params)
    {
        CLI::write('🔄 Starting RBAC synchronization...', 'yellow');
        
        $syncService = new PermissionSyncService();
        
        try {
            // Get sync status before
            $statusBefore = $syncService->getSyncStatus();
            
            CLI::write("📊 Status before sync:", 'blue');
            CLI::write("  - Config permissions: {$statusBefore['config_permissions']}");
            CLI::write("  - DB permissions: {$statusBefore['db_permissions']}");
            CLI::write("  - Config roles: {$statusBefore['config_roles']}");
            CLI::write("  - DB roles: {$statusBefore['db_roles']}");
            
            // Run full sync
            $result = $syncService->fullSync();
            
            CLI::write("✅ Synchronization completed!", 'green');
            CLI::write("  - Permissions synced: {$result['permissions_synced']}");
            CLI::write("  - Roles synced: {$result['roles_synced']}");
            
            // Get sync status after
            $statusAfter = $syncService->getSyncStatus();
            
            CLI::write("📊 Status after sync:", 'blue');
            CLI::write("  - Config permissions: {$statusAfter['config_permissions']}");
            CLI::write("  - DB permissions: {$statusAfter['db_permissions']}");
            CLI::write("  - Config roles: {$statusAfter['config_roles']}");
            CLI::write("  - DB roles: {$statusAfter['db_roles']}");
            
            if ($statusAfter['permissions_synced'] && $statusAfter['roles_synced']) {
                CLI::write("🎉 All permissions and roles are now synchronized!", 'green');
            } else {
                CLI::write("⚠️  Some items may not be synchronized. Check the logs.", 'yellow');
            }
            
        } catch (\Exception $e) {
            CLI::write("❌ Error during synchronization: " . $e->getMessage(), 'red');
            return EXIT_ERROR;
        }
        
        return EXIT_SUCCESS;
    }
}
