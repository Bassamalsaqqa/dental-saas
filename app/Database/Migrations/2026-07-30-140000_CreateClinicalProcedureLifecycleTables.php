<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClinicalProcedureLifecycleTables extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('clinical_procedures')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'clinic_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'patient_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'examination_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'condition_odontogram_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'treatment_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'appointment_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'contract_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'contract_item_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'clinical_charge_line_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'provider_type' => [
                    'type' => 'ENUM',
                    'constraint' => ['user', 'guest', 'none'],
                    'default' => 'none',
                ],
                'provider_user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'provider_name_snapshot' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'procedure_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 80,
                    'null' => true,
                ],
                'procedure_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'tooth_numbers' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'surfaces' => [
                    'type' => 'VARCHAR',
                    'constraint' => 80,
                    'null' => true,
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => [
                        'proposed',
                        'presented',
                        'accepted',
                        'rejected',
                        'scheduled',
                        'in_progress',
                        'completed',
                        'referred',
                        'cancelled',
                    ],
                    'default' => 'proposed',
                ],
                'status_reason' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                    'null' => true,
                ],
                'proposed_at' => ['type' => 'DATETIME', 'null' => true],
                'presented_at' => ['type' => 'DATETIME', 'null' => true],
                'accepted_at' => ['type' => 'DATETIME', 'null' => true],
                'rejected_at' => ['type' => 'DATETIME', 'null' => true],
                'scheduled_at' => ['type' => 'DATETIME', 'null' => true],
                'started_at' => ['type' => 'DATETIME', 'null' => true],
                'completed_at' => ['type' => 'DATETIME', 'null' => true],
                'referred_at' => ['type' => 'DATETIME', 'null' => true],
                'cancelled_at' => ['type' => 'DATETIME', 'null' => true],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'status_changed_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'status_changed_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['clinic_id', 'patient_id', 'status'], false, false, 'idx_cp_patient_status');
            $this->forge->addKey(['clinic_id', 'examination_id'], false, false, 'idx_cp_examination');
            $this->forge->addKey(['clinic_id', 'appointment_id'], false, false, 'idx_cp_appointment');
            $this->forge->addKey(['clinic_id', 'provider_type', 'provider_user_id'], false, false, 'idx_cp_provider');
            $this->forge->addKey(['clinic_id', 'treatment_id'], false, true, 'uq_cp_treatment');
            $this->forge->addKey(['clinic_id', 'contract_item_id'], false, true, 'uq_cp_contract_item');
            $this->forge->addKey(['clinic_id', 'clinical_charge_line_id'], false, true, 'uq_cp_charge_line');
            $this->forge->createTable('clinical_procedures');
        }

        if (!$this->db->tableExists('clinical_procedure_status_history')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'clinic_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'procedure_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'from_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                ],
                'to_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                ],
                'reason' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                    'null' => true,
                ],
                'changed_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['clinic_id', 'procedure_id', 'created_at'], false, false, 'idx_cpsh_procedure_time');
            $this->forge->addKey(['clinic_id', 'to_status'], false, false, 'idx_cpsh_status');
            $this->forge->createTable('clinical_procedure_status_history');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('clinical_procedure_status_history')) {
            $this->forge->dropTable('clinical_procedure_status_history', true);
        }
        if ($this->db->tableExists('clinical_procedures')) {
            $this->forge->dropTable('clinical_procedures', true);
        }
    }
}
