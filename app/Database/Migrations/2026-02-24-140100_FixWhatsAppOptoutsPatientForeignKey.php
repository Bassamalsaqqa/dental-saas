<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixWhatsAppOptoutsPatientForeignKey extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('whatsapp_optouts')) {
            return;
        }

        $this->db->query('ALTER TABLE `whatsapp_optouts` DROP FOREIGN KEY `whatsapp_optouts_patient_id_foreign`');
        $this->db->query(
            'ALTER TABLE `whatsapp_optouts` ADD CONSTRAINT `whatsapp_optouts_patient_id_foreign` ' .
            'FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        if (!$this->db->tableExists('whatsapp_optouts')) {
            return;
        }

        $this->db->query('ALTER TABLE `whatsapp_optouts` DROP FOREIGN KEY `whatsapp_optouts_patient_id_foreign`');
        $this->db->query(
            'ALTER TABLE `whatsapp_optouts` ADD CONSTRAINT `whatsapp_optouts_patient_id_foreign` ' .
            'FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE ON UPDATE SET NULL'
        );
    }
}
