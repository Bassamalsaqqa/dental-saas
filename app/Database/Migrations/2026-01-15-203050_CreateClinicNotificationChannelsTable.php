<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClinicNotificationChannelsTable extends Migration
{
    public function up()
    {
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
            'channel_type' => [
                'type'       => 'ENUM',
                'constraint' => ['email', 'sms', 'whatsapp'],
            ],
            'enabled_by_superadmin' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'configured_by_clinic' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'validated' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'provider_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'config_encrypted' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'last_tested_at' => [
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
        $this->forge->addUniqueKey(['clinic_id', 'channel_type']);
        $this->forge->createTable('clinic_notification_channels');
    }

    public function down()
    {
        $this->forge->dropTable('clinic_notification_channels');
    }
}