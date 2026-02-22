<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExaminationHealthUpdateFields extends Migration
{
    public function up()
    {
        $fields = [
            'health_update_medical_changes' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'medical_history',
            ],
            'health_update_medication_changes' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'health_update_medical_changes',
            ],
            'health_update_allergies_reactions' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'health_update_medication_changes',
            ],
            'health_update_pregnancy' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'health_update_allergies_reactions',
            ],
            'health_update_current_symptoms' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'health_update_pregnancy',
            ],
            'health_update_details' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'health_update_current_symptoms',
            ],
        ];

        foreach ($fields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'examinations')) {
                $this->forge->addColumn('examinations', [$name => $definition]);
            }
        }
    }

    public function down()
    {
        $columns = [
            'health_update_details',
            'health_update_current_symptoms',
            'health_update_pregnancy',
            'health_update_allergies_reactions',
            'health_update_medication_changes',
            'health_update_medical_changes',
        ];

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, 'examinations')) {
                $this->forge->dropColumn('examinations', $column);
            }
        }
    }
}
