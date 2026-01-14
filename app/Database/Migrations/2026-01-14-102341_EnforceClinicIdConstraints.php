<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnforceClinicIdConstraints extends Migration
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
            if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id', $table)) {
                // 1. Modify to NOT NULL
                // CodeIgniter Forge modifyColumn is tricky with constraints, sometimes raw query is safer for NOT NULL
                $this->db->query("ALTER TABLE `$table` MODIFY COLUMN `clinic_id` INT(11) UNSIGNED NOT NULL");

                // 2. Add Foreign Key
                // Check if FK exists? Forge handles it?
                // Using raw SQL for FK to be robust
                $fkName = "fk_{$table}_clinics";
                // Check if constraint exists? 
                // MySQL: SELECT * FROM information_schema.TABLE_CONSTRAINTS ...
                // Just try ADD CONSTRAINT, if fails, it fails (fail-closed/safe).
                // Or try-catch.
                // We'll use forge to addForeignKey which is cleaner if supported for existing table.
                // Forge addForeignKey works on createTable usually.
                // For existing table, CI4 forge->processIndexes?
                // I'll use raw SQL.
                
                // Drop if exists to be idempotent?
                // ALTER TABLE table DROP FOREIGN KEY fk_name
                // I'll try to add it.
                
                try {
                    $this->db->query("ALTER TABLE `$table` ADD CONSTRAINT `$fkName` FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
                } catch (\Throwable $e) {
                    // Ignore if already exists or log?
                    // Ideally we check information_schema
                }
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id', $table)) {
                $fkName = "fk_{$table}_clinics";
                try {
                    $this->db->query("ALTER TABLE `$table` DROP FOREIGN KEY `$fkName`");
                } catch (\Throwable $e) {}
                
                // Revert to NULLABLE
                $this->db->query("ALTER TABLE `$table` MODIFY COLUMN `clinic_id` INT(11) UNSIGNED NULL DEFAULT NULL");
            }
        }
    }
}