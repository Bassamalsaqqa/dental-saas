<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFinanceSourceReferences extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('source_entity_type', 'finances')) {
            $this->forge->addColumn('finances', [
                'source_entity_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'counterparty_name',
                ],
                'source_entity_id' => [
                    'type' => 'INT',
                    'null' => true,
                    'after' => 'source_entity_type',
                ],
            ]);
        }

        try {
            $this->db->query('CREATE INDEX idx_finances_source_entity ON finances (clinic_id, source_entity_type, source_entity_id)');
        } catch (\Throwable $e) {
            // best effort if index already exists
        }
    }

    public function down()
    {
        try {
            $this->db->query('DROP INDEX idx_finances_source_entity ON finances');
        } catch (\Throwable $e) {
            // best effort
        }

        if ($this->db->fieldExists('source_entity_type', 'finances')) {
            $this->forge->dropColumn('finances', ['source_entity_type', 'source_entity_id']);
        }
    }
}

