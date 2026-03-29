<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFinanceTreasuryCustody extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('liquidity_account', 'finances')) {
            $this->forge->addColumn('finances', [
                'liquidity_account' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => true,
                    'after' => 'payment_method',
                ],
            ]);
        }

        if ($this->db->fieldExists('payment_method', 'finances') && $this->db->fieldExists('liquidity_account', 'finances')) {
            $this->db->query("
                UPDATE finances
                SET liquidity_account = CASE
                    WHEN transaction_type = 'payment' AND payment_method = 'cash' THEN 'safe_cash'
                    WHEN transaction_type = 'payment' AND payment_method IN ('card','bank_transfer') THEN 'bank'
                    WHEN transaction_type = 'payment' AND payment_method = 'check' THEN 'undeposited_checks'
                    WHEN transaction_type = 'refund' AND payment_method = 'cash' THEN 'safe_cash'
                    WHEN transaction_type = 'refund' AND payment_method IN ('card','bank_transfer','check','insurance','other') THEN 'bank'
                    WHEN transaction_type = 'expense' AND payment_status = 'paid' AND payment_method = 'cash' THEN 'safe_cash'
                    WHEN transaction_type = 'expense' AND payment_status = 'paid' AND payment_method IN ('card','bank_transfer','check','insurance','other') THEN 'bank'
                    ELSE liquidity_account
                END
                WHERE liquidity_account IS NULL OR liquidity_account = ''
            ");
        }

        if (!$this->db->fieldExists('liquidity_account', 'finance_payments')) {
            $this->forge->addColumn('finance_payments', [
                'liquidity_account' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => true,
                    'after' => 'payment_method',
                ],
            ]);
        }

        if ($this->db->fieldExists('payment_method', 'finance_payments') && $this->db->fieldExists('liquidity_account', 'finance_payments')) {
            $this->db->query("
                UPDATE finance_payments
                SET liquidity_account = CASE
                    WHEN payment_method = 'cash' THEN 'safe_cash'
                    WHEN payment_method IN ('card','bank_transfer') THEN 'bank'
                    WHEN payment_method = 'check' THEN 'undeposited_checks'
                    WHEN payment_method IN ('insurance','other') THEN 'bank'
                    ELSE liquidity_account
                END
                WHERE liquidity_account IS NULL OR liquidity_account = ''
            ");
        }

        if (!$this->db->fieldExists('liquidity_account', 'provider_payout_payments')) {
            $this->forge->addColumn('provider_payout_payments', [
                'liquidity_account' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => true,
                    'after' => 'payment_method',
                ],
            ]);
        }

        if ($this->db->fieldExists('payment_method', 'provider_payout_payments') && $this->db->fieldExists('liquidity_account', 'provider_payout_payments')) {
            $this->db->query("
                UPDATE provider_payout_payments
                SET liquidity_account = CASE
                    WHEN payment_method = 'cash' THEN 'safe_cash'
                    WHEN payment_method IN ('card','bank_transfer','check','other') THEN 'bank'
                    ELSE liquidity_account
                END
                WHERE liquidity_account IS NULL OR liquidity_account = ''
            ");
        }

        if (!$this->db->tableExists('finance_liquidity_transfers')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => true,
                ],
                'clinic_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'from_account' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => false,
                ],
                'to_account' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => false,
                ],
                'amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'null' => false,
                ],
                'transfer_date' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'transfer_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => false,
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'related_finance_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
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
            $this->forge->addKey(['clinic_id', 'transfer_date']);
            $this->forge->addKey(['clinic_id', 'from_account']);
            $this->forge->addKey(['clinic_id', 'to_account']);
            $this->forge->addKey(['clinic_id', 'related_finance_id']);
            $this->forge->createTable('finance_liquidity_transfers');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('finance_liquidity_transfers')) {
            $this->forge->dropTable('finance_liquidity_transfers', true);
        }

        if ($this->db->fieldExists('liquidity_account', 'provider_payout_payments')) {
            $this->forge->dropColumn('provider_payout_payments', 'liquidity_account');
        }

        if ($this->db->fieldExists('liquidity_account', 'finance_payments')) {
            $this->forge->dropColumn('finance_payments', 'liquidity_account');
        }

        if ($this->db->fieldExists('liquidity_account', 'finances')) {
            $this->forge->dropColumn('finances', 'liquidity_account');
        }
    }
}
