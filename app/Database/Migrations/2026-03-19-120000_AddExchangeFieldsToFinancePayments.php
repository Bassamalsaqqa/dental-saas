<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExchangeFieldsToFinancePayments extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('currency', 'finance_payments')) {
            $this->forge->addColumn('finance_payments', [
                'currency' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => true,
                    'after' => 'amount',
                ],
                'amount_original' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'null' => true,
                    'after' => 'currency',
                ],
                'exchange_rate' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,6',
                    'null' => true,
                    'after' => 'amount_original',
                ],
            ]);
        }
    }

    public function down()
    {
        foreach (['exchange_rate', 'amount_original', 'currency'] as $field) {
            if ($this->db->fieldExists($field, 'finance_payments')) {
                $this->forge->dropColumn('finance_payments', $field);
            }
        }
    }
}
