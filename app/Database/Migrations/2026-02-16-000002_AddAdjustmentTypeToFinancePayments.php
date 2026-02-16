<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdjustmentTypeToFinancePayments extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('finance_payments') || !$this->db->fieldExists('type', 'finance_payments')) {
            return;
        }

        $this->db->query("
            ALTER TABLE finance_payments
            MODIFY type ENUM('payment','refund','void','adjustment')
            COLLATE utf8mb4_unicode_ci
            NOT NULL DEFAULT 'payment'
        ");
    }

    public function down()
    {
        if (!$this->db->tableExists('finance_payments') || !$this->db->fieldExists('type', 'finance_payments')) {
            return;
        }

        $this->db->query("UPDATE finance_payments SET type='payment' WHERE type='adjustment'");
        $this->db->query("
            ALTER TABLE finance_payments
            MODIFY type ENUM('payment','refund','void')
            COLLATE utf8mb4_unicode_ci
            NOT NULL DEFAULT 'payment'
        ");
    }
}

