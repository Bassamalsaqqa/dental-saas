<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsDoctorToRoles extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('is_doctor', 'roles')) {
            $this->forge->addColumn('roles', [
                'is_doctor' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'comment' => '1=Doctor role, 0=Non-doctor role',
                    'after' => 'is_system' // Attempting to place after is_system, but if is_system missing, might fail. Assuming roles structure from previous migrations.
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_doctor', 'roles')) {
            $this->forge->dropColumn('roles', 'is_doctor');
        }
    }
}