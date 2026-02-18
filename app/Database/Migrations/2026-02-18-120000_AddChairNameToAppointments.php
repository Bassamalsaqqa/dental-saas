<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddChairNameToAppointments extends Migration
{
    public function up()
    {
        $fields = [
            'chair_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'appointment_type',
            ],
        ];

        $this->forge->addColumn('appointments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('appointments', 'chair_name');
    }
}

