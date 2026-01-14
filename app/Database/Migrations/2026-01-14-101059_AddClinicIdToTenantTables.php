<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClinicIdToTenantTables extends Migration
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
        foreach ($this->tables as $table) {
            // Check if table exists first to avoid errors if a table is missing from previous dumps
            if ($this->db->tableExists($table)) {
                if (!$this->db->fieldExists('clinic_id', $table)) {
                    $fields = [
                        'clinic_id' => [
                            'type'       => 'INT',
                            'constraint' => 11,
                            'unsigned'   => true,
                            'null'       => true,
                            'default'    => null,
                            'after'      => 'id' // Try to put it near top, though not strictly required
                        ]
                    ];
                    $this->forge->addColumn($table, $fields);
                    
                    // Add index manually via SQL as addColumn with key can be tricky or driver dependent
                    // Or use processIndexes logic if forge supports it cleanly on existing table.
                    // simpler to run query for index
                    $this->db->query("ALTER TABLE `$table` ADD INDEX `clinic_id` (`clinic_id`)");
                }
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id', $table)) {
                // Drop Foreign Key if exists (Phase 3 adds it later, but good practice to check/drop if we were reverting S3-03 too)
                // Since this is S3-01 revert, we just drop column.
                // However, dropping column dropping index? Yes usually.
                $this->forge->dropColumn($table, 'clinic_id');
            }
        }
    }
}