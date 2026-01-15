<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlansAndSubscriptionsTables extends Migration
{
    public function up()
    {
        // Plans
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'features_json' => [
                'type' => 'JSON',
            ],
            'limits_json' => [
                'type' => 'JSON',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('plans');

        // Clinic Subscriptions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clinic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'plan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'paused', 'canceled', 'expired'],
                'default'    => 'active',
            ],
            'start_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'canceled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clinic_id');
        $this->forge->addKey('plan_id');
        $this->forge->createTable('clinic_subscriptions');

        // Plan Usage
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clinic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'metric_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'metric_value' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'period_start' => [
                'type' => 'DATE',
            ],
            'period_end' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clinic_id');
        // Unique constraint for metric per period per clinic
        $this->forge->addUniqueKey(['clinic_id', 'metric_key', 'period_start', 'period_end']);
        $this->forge->createTable('plan_usage');

        // Plan Audits
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clinic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'actor_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'reason_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'meta_json' => [
                'type' => 'JSON',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clinic_id');
        $this->forge->createTable('plan_audits');
    }

    public function down()
    {
        $this->forge->dropTable('plan_audits');
        $this->forge->dropTable('plan_usage');
        $this->forge->dropTable('clinic_subscriptions');
        $this->forge->dropTable('plans');
    }
}