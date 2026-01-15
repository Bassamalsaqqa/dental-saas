<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Config\Permissions;

class PurgeTenantRoles extends Migration
{
    public function up()
    {
        $defaultRoles = array_keys(Permissions::getDefaultRoles());
        
        // Safety: Always include super_admin
        if (!in_array('super_admin', $defaultRoles)) {
            $defaultRoles[] = 'super_admin';
        }

        $db = \Config\Database::connect();
        
        // 1. Get roles that are NOT in the default list and NOT marked as system
        $leakedRoles = $db->table('roles')
            ->whereNotIn('slug', $defaultRoles)
            ->where('is_system', 0)
            ->get()
            ->getResultArray();

        if (!empty($leakedRoles)) {
            $leakedIds = array_column($leakedRoles, 'id');
            
            // 2. Remove user assignments for these roles
            $db->table('user_roles')
                ->whereIn('role_id', $leakedIds)
                ->delete();
                
            // 3. Remove role permissions
            $db->table('role_permissions')
                ->whereIn('role_id', $leakedIds)
                ->delete();

            // 4. Delete the roles
            $db->table('roles')
                ->whereIn('id', $leakedIds)
                ->delete();
                
            log_message('info', 'Purged ' . count($leakedRoles) . ' leaked tenant roles.');
        }
    }

    public function down()
    {
        // No rollback for cleanup
    }
}