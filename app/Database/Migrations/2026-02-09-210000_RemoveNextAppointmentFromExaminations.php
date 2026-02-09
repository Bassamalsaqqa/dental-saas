<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveNextAppointmentFromExaminations extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('next_appointment', 'examinations')) {
            $this->forge->dropColumn('examinations', 'next_appointment');
        }
    }

    public function down()
    {
        if (!$this->db->fieldExists('next_appointment', 'examinations')) {
            $this->forge->addColumn('examinations', [
                'next_appointment' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'recommendations',
                ],
            ]);
        }
    }
}
