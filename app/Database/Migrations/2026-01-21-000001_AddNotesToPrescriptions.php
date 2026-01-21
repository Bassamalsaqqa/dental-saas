<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotesToPrescriptions extends Migration
{
    public function up()
    {
        $fields = [
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'instructions',
            ],
        ];

        $this->forge->addColumn('prescriptions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('prescriptions', 'notes');
    }
}
