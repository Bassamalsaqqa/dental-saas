<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventorySuppliersAndMovements extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('suppliers')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'clinic_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                ],
                'contact_person' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'whatsapp' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
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
            $this->forge->addKey(['clinic_id', 'is_active']);
            $this->forge->addKey(['clinic_id', 'name']);
            $this->forge->createTable('suppliers', true);
            try {
                $this->db->query('CREATE UNIQUE INDEX uniq_suppliers_clinic_name ON suppliers (clinic_id, name)');
            } catch (\Throwable $e) {
                // best effort
            }
        }

        if (!$this->db->fieldExists('supplier_id', 'inventory')) {
            $this->forge->addColumn('inventory', [
                'supplier_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'supplier',
                ],
            ]);
            try {
                $this->db->query('CREATE INDEX idx_inventory_supplier_id ON inventory (clinic_id, supplier_id)');
            } catch (\Throwable $e) {
                // best effort
            }
        }

        if (!$this->db->tableExists('inventory_stock_movements')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'clinic_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'inventory_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'movement_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'quantity_change' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'quantity_before' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'quantity_after' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'unit_cost' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                ],
                'total_cost' => [
                    'type' => 'DECIMAL',
                    'constraint' => '12,2',
                    'null' => true,
                ],
                'reference_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'reference_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'notes' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
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
            $this->forge->addKey(['clinic_id', 'inventory_id', 'created_at']);
            $this->forge->addKey(['clinic_id', 'movement_type', 'created_at']);
            $this->forge->addKey(['clinic_id', 'reference_type', 'reference_id']);
            $this->forge->createTable('inventory_stock_movements', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('inventory_stock_movements')) {
            $this->forge->dropTable('inventory_stock_movements', true);
        }

        if ($this->db->fieldExists('supplier_id', 'inventory')) {
            try {
                $this->db->query('DROP INDEX idx_inventory_supplier_id ON inventory');
            } catch (\Throwable $e) {
                // best effort
            }
            $this->forge->dropColumn('inventory', 'supplier_id');
        }

        if ($this->db->tableExists('suppliers')) {
            try {
                $this->db->query('DROP INDEX uniq_suppliers_clinic_name ON suppliers');
            } catch (\Throwable $e) {
                // best effort
            }
            $this->forge->dropTable('suppliers', true);
        }
    }
}

