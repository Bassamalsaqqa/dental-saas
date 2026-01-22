<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClinicIdToSummaryTables extends Migration
{
    public function up()
    {
        $fields = [
            'clinic_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
        ];

        foreach (['appointment_summary', 'financial_summary', 'patient_examination_summary'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->addColumn($table, $fields);
                $this->forge->addKey('clinic_id');
                $this->forge->processIndexes($table);
            }
        }
    }

    public function down()
    {
        foreach (['appointment_summary', 'financial_summary', 'patient_examination_summary'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropColumn($table, 'clinic_id');
            }
        }
    }
}
