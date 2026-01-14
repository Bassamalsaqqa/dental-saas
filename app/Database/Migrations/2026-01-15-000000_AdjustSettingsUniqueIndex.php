<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdjustSettingsUniqueIndex extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('settings')) {
            try {
                $this->db->query("ALTER TABLE `settings` DROP INDEX `setting_key`");
            } catch (\Throwable $e) {
                // ignore if it already does not exist
            }

            try {
                $this->db->query("ALTER TABLE `settings` ADD UNIQUE KEY `clinic_setting_key` (`clinic_id`, `setting_key`)");
            } catch (\Throwable $e) {
                // ignore if the index already exists
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('settings')) {
            try {
                $this->db->query("ALTER TABLE `settings` DROP INDEX `clinic_setting_key`");
            } catch (\Throwable $e) {
            }

            try {
                $this->db->query("ALTER TABLE `settings` ADD UNIQUE KEY `setting_key` (`setting_key`)");
            } catch (\Throwable $e) {
            }
        }
    }
}
