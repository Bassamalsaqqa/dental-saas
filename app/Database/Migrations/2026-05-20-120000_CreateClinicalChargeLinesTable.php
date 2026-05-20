<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClinicalChargeLinesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('clinical_charge_lines')) {
            return;
        }

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
                'null' => false,
            ],
            'patient_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'examination_id' => [
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
            'finance_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'source_entity_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'manual',
                'null' => false,
            ],
            'source_entity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'provider_type' => [
                'type' => 'ENUM',
                'constraint' => ['user', 'guest', 'none'],
                'default' => 'none',
                'null' => false,
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
                'null' => false,
            ],
            'tooth_numbers' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'quantity' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '1.00',
                'null' => false,
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => '0.00',
                'null' => false,
            ],
            'gross_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => '0.00',
                'null' => false,
            ],
            'discount_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => '0.00',
                'null' => false,
            ],
            'adjustment_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => '0.00',
                'null' => false,
            ],
            'tax_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => '0.00',
                'null' => false,
            ],
            'net_production_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => '0.00',
                'null' => false,
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
                'default' => 'ILS',
                'null' => false,
            ],
            'service_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'charge_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['planned', 'accepted', 'completed', 'invoiced', 'voided'],
                'default' => 'planned',
                'null' => false,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'voided_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'voided_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['clinic_id', 'patient_id', 'status'], false, false, 'idx_ccl_patient_status');
        $this->forge->addKey(['clinic_id', 'examination_id'], false, false, 'idx_ccl_examination');
        $this->forge->addKey(['clinic_id', 'treatment_id'], false, false, 'idx_ccl_treatment');
        $this->forge->addKey(['clinic_id', 'finance_id'], false, false, 'idx_ccl_finance');
        $this->forge->addKey(['clinic_id', 'source_entity_type', 'source_entity_id'], false, false, 'idx_ccl_source_entity');
        $this->forge->addKey(['clinic_id', 'provider_type', 'provider_user_id'], false, false, 'idx_ccl_provider');
        $this->forge->addKey(['clinic_id', 'charge_date'], false, false, 'idx_ccl_charge_date');
        $this->forge->createTable('clinical_charge_lines');
    }

    public function down()
    {
        if ($this->db->tableExists('clinical_charge_lines')) {
            $this->forge->dropTable('clinical_charge_lines', true);
        }
    }
}
