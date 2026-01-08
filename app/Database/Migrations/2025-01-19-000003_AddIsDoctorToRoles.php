<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsDoctorToRoles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('roles', [
            'is_doctor' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1=Doctor role, 0=Non-doctor role',
                'after' => 'is_system'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('roles', 'is_doctor');
    }
}
