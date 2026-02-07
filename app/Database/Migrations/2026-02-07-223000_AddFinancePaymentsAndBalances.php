<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFinancePaymentsAndBalances extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('amount_paid', 'finances')) {
            $this->forge->addColumn('finances', [
                'amount_paid' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => 0,
                    'null' => false,
                ],
            ]);
        }

        if (!$this->db->fieldExists('balance_due', 'finances')) {
            $this->forge->addColumn('finances', [
                'balance_due' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'default' => 0,
                    'null' => false,
                ],
            ]);
        }

        if (!$this->db->tableExists('finance_payments')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'auto_increment' => true,
                ],
                'clinic_id' => [
                    'type' => 'INT',
                    'null' => false,
                ],
                'finance_id' => [
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
                'payment_date' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'notes' => [
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
            $this->forge->addKey('finance_id');
            $this->forge->addForeignKey('finance_id', 'finances', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('finance_payments');
        }

        $totalExpr = $this->db->fieldExists('total_amount', 'finances')
            ? 'total_amount'
            : '(amount - discount_amount + tax_amount)';

        if ($this->db->fieldExists('amount_paid', 'finances') && $this->db->fieldExists('balance_due', 'finances')) {
            $this->db->query("
                UPDATE finances
                SET amount_paid = 0,
                    balance_due = CASE
                        WHEN transaction_type = 'invoice' THEN {$totalExpr}
                        ELSE 0
                    END
            ");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('finance_payments')) {
            $this->forge->dropTable('finance_payments', true);
        }

        if ($this->db->fieldExists('amount_paid', 'finances')) {
            $this->forge->dropColumn('finances', 'amount_paid');
        }

        if ($this->db->fieldExists('balance_due', 'finances')) {
            $this->forge->dropColumn('finances', 'balance_due');
        }
    }
}
