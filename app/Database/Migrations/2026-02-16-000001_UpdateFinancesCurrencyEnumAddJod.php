<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateFinancesCurrencyEnumAddJod extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('finances') || !$this->db->fieldExists('currency', 'finances')) {
            return;
        }

        $this->db->query("
            ALTER TABLE finances
            MODIFY currency ENUM('USD','EUR','GBP','BDT','ILS','JOD')
            COLLATE utf8mb4_general_ci
            DEFAULT 'USD'
        ");
    }

    public function down()
    {
        if (!$this->db->tableExists('finances') || !$this->db->fieldExists('currency', 'finances')) {
            return;
        }

        $this->db->query("UPDATE finances SET currency='ILS' WHERE currency='JOD'");
        $this->db->query("
            ALTER TABLE finances
            MODIFY currency ENUM('USD','EUR','GBP','BDT','ILS')
            COLLATE utf8mb4_general_ci
            DEFAULT 'USD'
        ");
    }
}

