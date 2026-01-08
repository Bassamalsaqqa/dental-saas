<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotalAmountToFinances extends Migration
{
    public function up()
    {
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

    public function down()
    {
        $this->forge->dropColumn('finances', 'total_amount');
    }
}
