<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubscriptionLifecycleReasons extends Migration
{
    public function up()
    {
        $fields = [
            'paused_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'paused_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'canceled_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
        ];

        $this->forge->addColumn('clinic_subscriptions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('clinic_subscriptions', ['paused_at', 'paused_reason', 'canceled_reason']);
    }
}
