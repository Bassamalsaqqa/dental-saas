<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPatientMedicalAlertFields extends Migration
{
    public function up()
    {
        $fields = [
            'alert_has_allergies' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'allergies',
            ],
            'alert_allergies_details' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'alert_has_allergies',
            ],
            'alert_taking_medications' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'alert_allergies_details',
            ],
            'alert_medications_details' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'alert_taking_medications',
            ],
            'alert_blood_thinners' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'alert_medications_details',
            ],
            'alert_blood_thinners_details' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'alert_blood_thinners',
            ],
            'alert_diabetes' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'alert_blood_thinners_details',
            ],
            'alert_diabetes_details' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'alert_diabetes',
            ],
            'alert_high_blood_pressure' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'alert_diabetes_details',
            ],
            'alert_blood_pressure_details' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'alert_high_blood_pressure',
            ],
            'alert_heart_condition' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'alert_blood_pressure_details',
            ],
            'alert_heart_condition_details' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'alert_heart_condition',
            ],
            'alert_pregnant' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'alert_heart_condition_details',
            ],
            'alert_pregnancy_details' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'alert_pregnant',
            ],
            'alert_anesthesia_problem' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'after' => 'alert_pregnancy_details',
            ],
            'alert_anesthesia_problem_details' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'alert_anesthesia_problem',
            ],
        ];

        foreach ($fields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'patients')) {
                $this->forge->addColumn('patients', [$name => $definition]);
            }
        }
    }

    public function down()
    {
        $columns = [
            'alert_anesthesia_problem_details',
            'alert_anesthesia_problem',
            'alert_pregnancy_details',
            'alert_pregnant',
            'alert_heart_condition_details',
            'alert_heart_condition',
            'alert_blood_pressure_details',
            'alert_high_blood_pressure',
            'alert_diabetes_details',
            'alert_diabetes',
            'alert_blood_thinners_details',
            'alert_blood_thinners',
            'alert_medications_details',
            'alert_taking_medications',
            'alert_allergies_details',
            'alert_has_allergies',
        ];

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, 'patients')) {
                $this->forge->dropColumn('patients', $column);
            }
        }
    }
}
