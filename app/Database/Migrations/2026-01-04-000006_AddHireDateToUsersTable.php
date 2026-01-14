<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHireDateToUsersTable extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('hire_date', 'users')) {
            // Add hire_date column to users table
            $this->forge->addColumn('users', [
                'hire_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'address'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('hire_date', 'users')) {
            // Remove hire_date column from users table
            $this->forge->dropColumn('users', 'hire_date');
        }
    }
}
