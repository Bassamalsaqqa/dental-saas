<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddAppointmentContextToExaminations extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('appointment_id', 'examinations')) {
            $this->forge->addColumn('examinations', [
                'appointment_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'patient_id',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_examinations_clinic_appointment ON examinations (clinic_id, appointment_id)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('appointment_id', 'examinations')) {
            $this->db->query('DROP INDEX idx_examinations_clinic_appointment ON examinations');
            $this->forge->dropColumn('examinations', 'appointment_id');
        }
    }
}
