<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFinanceExchangeFields extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('amount_original', 'finances')) {
            $fields['amount_original'] = [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0,
                'after' => 'amount',
            ];
        }

        if (!$this->db->fieldExists('exchange_rate', 'finances')) {
            $fields['exchange_rate'] = [
                'type' => 'DECIMAL',
                'constraint' => '12,6',
                'default' => 1,
                'after' => 'amount_original',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('finances', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('exchange_rate', 'finances')) {
            $this->forge->dropColumn('finances', 'exchange_rate');
        }

        if ($this->db->fieldExists('amount_original', 'finances')) {
            $this->forge->dropColumn('finances', 'amount_original');
        }
    }
}
