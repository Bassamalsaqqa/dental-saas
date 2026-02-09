<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaxPercentageToFinances extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('tax_percentage', 'finances')) {
            $this->forge->addColumn('finances', [
                'tax_percentage' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'default' => 0,
                    'after' => 'discount_amount',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('tax_percentage', 'finances')) {
            $this->forge->dropColumn('finances', 'tax_percentage');
        }
    }
}
