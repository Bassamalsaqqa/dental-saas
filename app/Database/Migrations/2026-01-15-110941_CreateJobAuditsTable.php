<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobAuditsTable extends Migration
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
            'job_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'clinic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // Nullable for fail-fast logs
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['success', 'fail'],
                'default'    => 'fail',
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'finished_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'attachment_ids' => [
                'type' => 'TEXT', // Store as JSON string
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clinic_id');
        $this->forge->addKey('status');
        $this->forge->createTable('job_audits');
    }

    public function down()
    {
        $this->forge->dropTable('job_audits');
    }
}
