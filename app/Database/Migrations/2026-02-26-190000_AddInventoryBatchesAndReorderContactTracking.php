<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventoryBatchesAndReorderContactTracking extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('inventory_reorder_items')) {
            $fields = $this->db->getFieldNames('inventory_reorder_items');
            if (!in_array('supplier_contacted_at', $fields, true)) {
                $this->forge->addColumn('inventory_reorder_items', [
                    'supplier_contacted_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'ordered_at'],
                ]);
            }
            if (!in_array('supplier_contact_method', $fields, true)) {
                $this->forge->addColumn('inventory_reorder_items', [
                    'supplier_contact_method' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'supplier_contacted_at'],
                ]);
            }
            if (!in_array('supplier_contacted_by', $fields, true)) {
                $this->forge->addColumn('inventory_reorder_items', [
                    'supplier_contacted_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'supplier_contact_method'],
                ]);
            }
        }

        if ($this->db->tableExists('inventory_stock_movements')) {
            $fields = $this->db->getFieldNames('inventory_stock_movements');
            if (!in_array('batch_id', $fields, true)) {
                $this->forge->addColumn('inventory_stock_movements', [
                    'batch_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'inventory_id'],
                ]);
                try {
                    $this->db->query('CREATE INDEX idx_inventory_stock_movements_batch_id ON inventory_stock_movements (batch_id)');
                } catch (\Throwable $e) {
                }
            }
        }

        if (!$this->db->tableExists('inventory_batches')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'clinic_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'inventory_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'lot_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'expiry_date' => ['type' => 'DATE', 'null' => true],
                'received_qty' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'remaining_qty' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
                'currency' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'ILS'],
                'source_movement_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['clinic_id', 'inventory_id']);
            $this->forge->addKey(['clinic_id', 'inventory_id', 'expiry_date']);
            $this->forge->addKey(['clinic_id', 'inventory_id', 'lot_number']);
            $this->forge->createTable('inventory_batches', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('inventory_batches')) {
            $this->forge->dropTable('inventory_batches', true);
        }

        if ($this->db->tableExists('inventory_stock_movements')) {
            $fields = $this->db->getFieldNames('inventory_stock_movements');
            if (in_array('batch_id', $fields, true)) {
                try {
                    $this->db->query('DROP INDEX idx_inventory_stock_movements_batch_id ON inventory_stock_movements');
                } catch (\Throwable $e) {
                }
                $this->forge->dropColumn('inventory_stock_movements', 'batch_id');
            }
        }

        if ($this->db->tableExists('inventory_reorder_items')) {
            $fields = $this->db->getFieldNames('inventory_reorder_items');
            foreach (['supplier_contacted_by', 'supplier_contact_method', 'supplier_contacted_at'] as $col) {
                if (in_array($col, $fields, true)) {
                    $this->forge->dropColumn('inventory_reorder_items', $col);
                }
            }
        }
    }
}

