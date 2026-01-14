<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillClinicId extends Migration
{
    protected $tables = [
        'patients',
        'appointments',
        'examinations',
        'finances',
        'treatments',
        'prescriptions',
        'inventory',
        'inventory_usage',
        'odontograms',
        'activity_logs',
        'settings'
    ];

    public function up()
    {
        // 1. Get Default Clinic ID
        // We assume ID 1 is the default from Phase 2 seeding.
        $defaultClinicId = 1;
        
        // Ensure it exists just in case
        $exists = $this->db->table('clinics')->where('id', $defaultClinicId)->countAllResults();
        if ($exists == 0) {
            // Fallback: get first active clinic
            $first = $this->db->table('clinics')->orderBy('id', 'ASC')->limit(1)->get()->getRow();
            if ($first) {
                $defaultClinicId = $first->id;
            } else {
                // If NO clinics exist, we can't backfill correctly.
                // However, we shouldn't have tenant data if we have no clinics?
                // Or this is a fresh install.
                // We'll insert a default clinic if absolutely necessary? 
                // Phase 2 said "Backfill uses the default clinic only."
                // I will insert one if missing to be safe/robust.
                $this->db->table('clinics')->insert([
                    'name' => 'Default Clinic',
                    'slug' => 'default',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $defaultClinicId = $this->db->insertID();
            }
        }

        // 2. Backfill
        foreach ($this->tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->query("UPDATE `$table` SET `clinic_id` = $defaultClinicId WHERE `clinic_id` IS NULL");
            }
        }
    }

    public function down()
    {
        // No real down for data backfill (we don't want to nullify data usually).
        // But strict revert would be setting them back to NULL?
        // Risky if new data was added.
        // We leave it as no-op or maybe set NULL where it equals default? No.
    }
}