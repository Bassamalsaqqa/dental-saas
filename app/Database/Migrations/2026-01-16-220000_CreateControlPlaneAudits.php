<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateControlPlaneAudits extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'actor_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'route' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'method' => [
                'type' => 'VARCHAR',
                'constraint' => 12,
                'null' => true,
            ],
            'ip' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'metadata_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addKey('actor_user_id');
        $this->forge->addKey('event_type');
        $this->forge->createTable('control_plane_audits', true);
    }

    public function down()
    {
        $this->forge->dropTable('control_plane_audits', true);
    }
}
