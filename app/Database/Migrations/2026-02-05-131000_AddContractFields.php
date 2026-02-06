<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContractFields extends Migration
{
    public function up()
    {
        $fields = [
            'terms_conditions' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'notes'
            ],
            'payment_terms' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'terms_conditions'
            ],
            'duration_plan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'payment_terms'
            ],
            'cost_details' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'duration_plan'
            ],
        ];

        $this->forge->addColumn('treatment_contracts', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('treatment_contracts', ['terms_conditions', 'payment_terms', 'duration_plan', 'cost_details']);
    }
}
