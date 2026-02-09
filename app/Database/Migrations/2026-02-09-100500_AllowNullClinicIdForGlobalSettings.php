<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AllowNullClinicIdForGlobalSettings extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('settings') || !$this->db->fieldExists('clinic_id', 'settings')) {
            return;
        }

        $this->db->query("UPDATE `settings` SET `clinic_id` = NULL WHERE `clinic_id` = 0");
        $this->db->query("ALTER TABLE `settings` MODIFY COLUMN `clinic_id` INT(11) UNSIGNED NULL");
    }

    public function down()
    {
        if (!$this->db->tableExists('settings') || !$this->db->fieldExists('clinic_id', 'settings')) {
            return;
        }

        $clinicId = null;
        if ($this->db->tableExists('clinics')) {
            $row = $this->db->table('clinics')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
            $clinicId = $row['id'] ?? null;
        }

        if ($clinicId !== null) {
            $this->db->query("UPDATE `settings` SET `clinic_id` = {$clinicId} WHERE `clinic_id` IS NULL");
            $this->db->query("ALTER TABLE `settings` MODIFY COLUMN `clinic_id` INT(11) UNSIGNED NOT NULL");
        }
    }
}
