<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateFinancePaymentsTypes extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('finance_payments')) {
            if (!$this->db->fieldExists('type', 'finance_payments')) {
                $this->forge->addColumn('finance_payments', [
                    'type' => [
                        'type' => "ENUM('payment','refund','void')",
                        'default' => 'payment',
                        'null' => false,
                    ],
                ]);
            }

            if (!$this->db->fieldExists('related_payment_id', 'finance_payments')) {
                $this->forge->addColumn('finance_payments', [
                    'related_payment_id' => [
                        'type' => 'INT',
                        'null' => true,
                    ],
                ]);
                $this->forge->addKey('related_payment_id');
            }

            if ($this->db->fieldExists('type', 'finance_payments')) {
                $this->db->query("UPDATE finance_payments SET type='payment' WHERE type IS NULL OR type = ''");
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('finance_payments')) {
            if ($this->db->fieldExists('related_payment_id', 'finance_payments')) {
                $this->forge->dropColumn('finance_payments', 'related_payment_id');
            }
            if ($this->db->fieldExists('type', 'finance_payments')) {
                $this->forge->dropColumn('finance_payments', 'type');
            }
        }
    }
}
