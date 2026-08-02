<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScheduledAppointmentToClinicalProcedures extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('scheduled_appointment_id', 'clinical_procedures')) {
            $this->forge->addColumn('clinical_procedures', [
                'scheduled_appointment_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'appointment_id',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_cp_scheduled_appointment ON clinical_procedures (clinic_id, scheduled_appointment_id)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('scheduled_appointment_id', 'clinical_procedures')) {
            $this->forge->dropColumn('clinical_procedures', 'scheduled_appointment_id');
        }
    }
}
