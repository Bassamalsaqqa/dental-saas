<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTreatmentPlanFromExaminations extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('treatment_plan', 'examinations')) {
            $this->forge->dropColumn('examinations', 'treatment_plan');
        }
    }

    public function down()
    {
        if (!$this->db->fieldExists('treatment_plan', 'examinations')) {
            $this->forge->addColumn('examinations', [
                'treatment_plan' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'diagnosis',
                ],
            ]);
        }
    }
}
