<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProviderPayouts extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('provider_payouts')) {
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
                'finance_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => false,
                ],
                'patient_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'provider_type' => [
                    'type' => 'ENUM',
                    'constraint' => ['user', 'guest'],
                    'default' => 'user',
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
                'split_mode' => [
                    'type' => 'ENUM',
                    'constraint' => ['percent', 'fixed'],
                    'default' => 'percent',
                ],
                'split_value' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ],
                'gross_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => '0.00',
                ],
                'provider_share_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => '0.00',
                ],
                'clinic_share_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => '0.00',
                ],
                'provider_paid_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => '0.00',
                ],
                'provider_balance_due' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => '0.00',
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['unpaid', 'partial', 'paid', 'void'],
                    'default' => 'unpaid',
                ],
                'currency' => [
                    'type' => 'VARCHAR',
                    'constraint' => 8,
                    'default' => 'ILS',
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
            $this->forge->addKey(['clinic_id', 'status']);
            $this->forge->addKey(['clinic_id', 'provider_user_id']);
            $this->forge->addKey(['clinic_id', 'finance_id']);
            $this->forge->addForeignKey('finance_id', 'finances', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('provider_payouts');
        }

        if (!$this->db->tableExists('provider_payout_payments')) {
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
                'provider_payout_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => '0.00',
                ],
                'payment_method' => [
                    'type' => 'ENUM',
                    'constraint' => ['cash', 'card', 'bank_transfer', 'check', 'other'],
                    'default' => 'cash',
                ],
                'paid_date' => [
                    'type' => 'DATE',
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
            $this->forge->addKey(['clinic_id', 'provider_payout_id']);
            $this->forge->addForeignKey('provider_payout_id', 'provider_payouts', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('provider_payout_payments');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('provider_payout_payments')) {
            $this->forge->dropTable('provider_payout_payments', true);
        }
        if ($this->db->tableExists('provider_payouts')) {
            $this->forge->dropTable('provider_payouts', true);
        }
    }
}

