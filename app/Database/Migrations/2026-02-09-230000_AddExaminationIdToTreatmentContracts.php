<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExaminationIdToTreatmentContracts extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('examination_id', 'treatment_contracts')) {
            $this->forge->addColumn('treatment_contracts', [
                'examination_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'patient_id',
                ],
            ]);
            $this->forge->addKey('examination_id');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('examination_id', 'treatment_contracts')) {
            $this->forge->dropColumn('treatment_contracts', 'examination_id');
        }
    }
}
