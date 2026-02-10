<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedByToCoreTables extends Migration
{
    public function up()
    {
        $fields = [
            'created_by' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'status'
            ]
        ];

        if (!$this->db->fieldExists('created_by', 'patients')) {
            $this->forge->addColumn('patients', $fields);
        }

        if (!$this->db->fieldExists('created_by', 'prescriptions')) {
            $this->forge->addColumn('prescriptions', $fields);
        }

        if (!$this->db->fieldExists('created_by', 'inventory')) {
            $this->forge->addColumn('inventory', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('created_by', 'patients')) {
            $this->forge->dropColumn('patients', 'created_by');
        }
        if ($this->db->fieldExists('created_by', 'prescriptions')) {
            $this->forge->dropColumn('prescriptions', 'created_by');
        }
        if ($this->db->fieldExists('created_by', 'inventory')) {
            $this->forge->dropColumn('inventory', 'created_by');
        }
    }
}
