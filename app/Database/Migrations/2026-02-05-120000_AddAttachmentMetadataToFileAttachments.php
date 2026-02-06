<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttachmentMetadataToFileAttachments extends Migration
{
    public function up()
    {
        $fields = [
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'purpose'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'category'
            ],
        ];

        $this->forge->addColumn('file_attachments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('file_attachments', ['category', 'notes']);
    }
}
