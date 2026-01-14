<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotalAmountToFinances extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('total_amount', 'finances')) {
            $fields = [
                'total_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0.00,
                    'after' => 'tax_amount'
                ],
            ];
            $this->forge->addColumn('finances', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('total_amount', 'finances')) {
            $this->forge->dropColumn('finances', 'total_amount');
        }
    }
}