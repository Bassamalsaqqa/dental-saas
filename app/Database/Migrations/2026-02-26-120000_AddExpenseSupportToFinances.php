<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpenseSupportToFinances extends Migration
{
    public function up()
    {
        // patient_id must be nullable for expense rows that are not tied to a patient.
        $this->forge->modifyColumn('finances', [
            'patient_id' => [
                'type' => 'INT',
                'null' => true,
            ],
        ]);

        if (!$this->db->fieldExists('expense_category', 'finances')) {
            $this->forge->addColumn('finances', [
                'expense_category' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'service_type',
                ],
                'counterparty_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'expense_category',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_finances_expense_category ON finances (expense_category)');
        }

        $this->db->query(
            "ALTER TABLE finances MODIFY transaction_type ENUM('payment','invoice','refund','adjustment','expense') NOT NULL"
        );
    }

    public function down()
    {
        if ($this->db->fieldExists('counterparty_name', 'finances')) {
            try {
                $this->db->query('DROP INDEX idx_finances_expense_category ON finances');
            } catch (\Throwable $e) {
                // best effort rollback
            }
            $this->forge->dropColumn('finances', ['expense_category', 'counterparty_name']);
        }

        $this->db->query(
            "ALTER TABLE finances MODIFY transaction_type ENUM('payment','invoice','refund','adjustment') NOT NULL"
        );

        $this->forge->modifyColumn('finances', [
            'patient_id' => [
                'type' => 'INT',
                'null' => false,
            ],
        ]);
    }
}

