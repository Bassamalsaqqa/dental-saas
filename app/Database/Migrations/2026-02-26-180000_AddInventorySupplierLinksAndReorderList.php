<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventorySupplierLinksAndReorderList extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('inventory_supplier_links')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'clinic_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'inventory_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'supplier_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'supplier_sku' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'pack_size_label' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'preferred_order_qty' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'last_unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
                'currency' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'ILS'],
                'is_preferred' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['clinic_id', 'inventory_id', 'supplier_id']);
            $this->forge->addKey(['clinic_id', 'inventory_id', 'is_preferred']);
            $this->forge->createTable('inventory_supplier_links', true);
            try {
                $this->db->query('CREATE UNIQUE INDEX uniq_inventory_supplier_link ON inventory_supplier_links (clinic_id, inventory_id, supplier_id)');
            } catch (\Throwable $e) {}
        }

        if (!$this->db->tableExists('inventory_reorder_items')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'clinic_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'inventory_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'supplier_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'requested_qty' => ['type' => 'INT', 'constraint' => 11],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
                'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'ordered_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['clinic_id', 'status', 'created_at']);
            $this->forge->addKey(['clinic_id', 'inventory_id']);
            $this->forge->createTable('inventory_reorder_items', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('inventory_reorder_items')) {
            $this->forge->dropTable('inventory_reorder_items', true);
        }
        if ($this->db->tableExists('inventory_supplier_links')) {
            try {
                $this->db->query('DROP INDEX uniq_inventory_supplier_link ON inventory_supplier_links');
            } catch (\Throwable $e) {}
            $this->forge->dropTable('inventory_supplier_links', true);
        }
    }
}

