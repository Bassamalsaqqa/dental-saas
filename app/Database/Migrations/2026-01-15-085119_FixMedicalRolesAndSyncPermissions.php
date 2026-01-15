<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Services\PermissionSyncService;

class FixMedicalRolesAndSyncPermissions extends Migration
{
    public function up()
    {
        // 1. Ensure columns exist (safety check)
        if (!$this->db->fieldExists('slug', 'roles')) {
            $this->forge->addColumn('roles', [
                'slug' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'name'
                ]
            ]);
        }
        
        if (!$this->db->fieldExists('is_medical', 'roles')) {
            $this->forge->addColumn('roles', [
                'is_medical' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'is_doctor'
                ]
            ]);
        }

        // 2. Perform a full sync to populate/update roles and permissions
        // We use a try-catch because services might not be fully available during migration
        try {
            $syncService = new PermissionSyncService();
            $syncService->fullSync();
            
            // 3. Specifically ensure common slugs are set if they were missing
            $this->db->table('roles')
                ->where('slug', 'doctor')
                ->update(['is_medical' => 1]);
                
            $this->db->table('roles')
                ->where('slug', 'senior_doctor')
                ->update(['is_medical' => 1]);
                
            $this->db->table('roles')
                ->where('slug', 'dental_assistant')
                ->update(['is_medical' => 1]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to run PermissionSyncService in migration: ' . $e->getMessage());
            
            // Fallback: manually update at least the doctor role if it exists
            $this->db->query("UPDATE roles SET is_medical = 1 WHERE name = 'Doctor' OR slug = 'doctor'");
        }
    }

    public function down()
    {
        // No down needed for data fixes
    }
}