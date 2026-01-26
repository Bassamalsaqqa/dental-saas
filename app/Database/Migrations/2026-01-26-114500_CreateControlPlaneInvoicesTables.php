<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateControlPlaneInvoicesTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'clinic_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'invoice_number' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => false,
            ],
            'status' => [
                'type' => "ENUM('draft','sent','paid','overdue','void','cancelled')",
                'default' => 'draft',
                'null' => false,
            ],
            'issue_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'currency' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'default' => 'USD',
                'null' => false,
            ],
            'subtotal' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0,
                'null' => false,
            ],
            'tax_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
                'null' => false,
            ],
            'tax_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0,
                'null' => false,
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => 0,
                'null' => false,
            ],
            'items_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'private_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clinic_id');
        $this->forge->addUniqueKey('invoice_number');
        $this->forge->createTable('control_plane_invoices');

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'invoice_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => false,
            ],
            'status' => [
                'type' => "ENUM('pending','completed','failed','refunded')",
                'default' => 'pending',
                'null' => false,
            ],
            'transaction_reference' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('invoice_id');
        $this->forge->addForeignKey('invoice_id', 'control_plane_invoices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('control_plane_invoice_payments');
    }

    public function down()
    {
        $this->forge->dropTable('control_plane_invoice_payments', true);
        $this->forge->dropTable('control_plane_invoices', true);
    }
}
