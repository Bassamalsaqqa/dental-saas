<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateFinancesCurrencyEnum extends Migration
{
    public function up()
    {
        // Update the currency enum to include ILS
        $this->db->query("ALTER TABLE finances MODIFY COLUMN currency ENUM('USD','EUR','GBP','BDT','ILS') DEFAULT 'USD'");
    }

    public function down()
    {
        // Revert to the original enum values
        $this->db->query("ALTER TABLE finances MODIFY COLUMN currency ENUM('USD','EUR','GBP','BDT') DEFAULT 'USD'");
    }
}