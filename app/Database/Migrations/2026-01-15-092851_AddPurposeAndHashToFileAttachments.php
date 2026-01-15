<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurposeAndHashToFileAttachments extends Migration
{
    public function up()
    {
        $fields = [
            'purpose' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'entity_id'
            ],
            'file_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'after' => 'purpose'
            ],
        ];
        $this->forge->addColumn('file_attachments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('file_attachments', ['purpose', 'file_hash']);
    }
}