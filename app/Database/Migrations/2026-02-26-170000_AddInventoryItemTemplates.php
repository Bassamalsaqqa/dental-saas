<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventoryItemTemplates extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('inventory_item_templates')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                ],
                'category' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'unit' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'pieces',
                ],
                'default_min_quantity' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 5,
                ],
                'default_unit_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0.00,
                ],
                'search_keywords' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'sort_order' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 100,
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
            $this->forge->addKey(['category', 'is_active']);
            $this->forge->addKey(['sort_order', 'name']);
            $this->forge->createTable('inventory_item_templates', true);
            try {
                $this->db->query('CREATE UNIQUE INDEX uniq_inventory_item_templates_name_category ON inventory_item_templates (name, category)');
            } catch (\Throwable $e) {
                // best effort
            }
        }

        $this->seedStarterTemplates();
    }

    public function down()
    {
        if ($this->db->tableExists('inventory_item_templates')) {
            try {
                $this->db->query('DROP INDEX uniq_inventory_item_templates_name_category ON inventory_item_templates');
            } catch (\Throwable $e) {
                // best effort
            }
            $this->forge->dropTable('inventory_item_templates', true);
        }
    }

    private function seedStarterTemplates(): void
    {
        $rows = [
            ['Gloves Nitrile (Box)', 'consumables', 'Disposable examination gloves', 'boxes', 10, 0.00, 'gloves nitrile exam ppe', 10],
            ['Surgical Masks (Box)', 'consumables', 'Disposable surgical masks', 'boxes', 8, 0.00, 'mask surgical ppe', 20],
            ['Saliva Ejector Tips', 'consumables', 'Disposable saliva ejector tips', 'packs', 5, 0.00, 'saliva ejector suction tips', 30],
            ['Cotton Rolls', 'supplies', 'Dental cotton rolls', 'packs', 6, 0.00, 'cotton rolls isolation', 40],
            ['Gauze Pads', 'supplies', 'Sterile gauze pads', 'packs', 6, 0.00, 'gauze pads sterile', 50],
            ['Local Anesthetic Carpules', 'medication', 'Local anesthetic cartridges/carpules', 'boxes', 4, 0.00, 'anesthetic carpule lidocaine', 60],
            ['Needles (Dental)', 'consumables', 'Dental anesthetic needles', 'boxes', 4, 0.00, 'needle dental anesthetic', 70],
            ['Composite Resin Syringe', 'supplies', 'Restorative composite resin', 'pieces', 3, 0.00, 'composite resin restorative', 80],
            ['Etchant Gel', 'supplies', 'Phosphoric acid etchant gel', 'tubes', 3, 0.00, 'etchant gel acid bond', 90],
            ['Bonding Agent', 'supplies', 'Dental adhesive bonding agent', 'bottles', 2, 0.00, 'bond adhesive dental', 100],
            ['Disinfectant Solution', 'supplies', 'Surface disinfectant solution', 'bottles', 4, 0.00, 'disinfectant surface sterilization', 110],
            ['Sterilization Pouches', 'consumables', 'Autoclave sterilization pouches', 'boxes', 3, 0.00, 'sterilization pouches autoclave', 120],
            ['Prophy Paste', 'supplies', 'Polishing paste for prophylaxis', 'tubes', 2, 0.00, 'prophy paste polishing', 130],
            ['Suction Traps/Filters', 'equipment', 'Replacement suction trap/filter components', 'pieces', 2, 0.00, 'suction trap filter', 140],
            ['Impression Material', 'supplies', 'Alginate/impression material', 'packs', 3, 0.00, 'impression alginate material', 150],
        ];

        foreach ($rows as $r) {
            [$name, $category, $description, $unit, $minQty, $price, $keywords, $sortOrder] = $r;
            $exists = $this->db->table('inventory_item_templates')
                ->where('name', $name)
                ->where('category', $category)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('inventory_item_templates')->insert([
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'unit' => $unit,
                'default_min_quantity' => $minQty,
                'default_unit_price' => $price,
                'search_keywords' => $keywords,
                'is_active' => 1,
                'sort_order' => $sortOrder,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}

