<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPreferencesToClinicUsers extends Migration
{
    public function up()
    {
        $fields = [
            'preferences' => [
                'type' => 'JSON',
                'null' => true,
                'default' => null,
                'after' => 'status'
            ],
        ];
        $this->forge->addColumn('clinic_users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('clinic_users', 'preferences');
    }
}
