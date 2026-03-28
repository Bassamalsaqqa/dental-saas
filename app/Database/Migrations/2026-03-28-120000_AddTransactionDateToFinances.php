<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransactionDateToFinances extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('transaction_date', 'finances')) {
            $this->forge->addColumn('finances', [
                'transaction_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'paid_date',
                ],
            ]);
        }

        $this->db->query("
            UPDATE finances
            SET transaction_date = CASE
                WHEN transaction_type = 'invoice' THEN DATE(created_at)
                WHEN paid_date IS NOT NULL AND paid_date <> '' THEN paid_date
                ELSE DATE(created_at)
            END
            WHERE transaction_date IS NULL
        ");

        $this->forge->modifyColumn('finances', [
            'transaction_date' => [
                'name' => 'transaction_date',
                'type' => 'DATE',
                'null' => false,
                'after' => 'paid_date',
            ],
        ]);

        $this->db->query('CREATE INDEX idx_finances_clinic_transaction_date ON finances (clinic_id, transaction_date)');
    }

    public function down()
    {
        if ($this->db->fieldExists('transaction_date', 'finances')) {
            try {
                $this->db->query('DROP INDEX idx_finances_clinic_transaction_date ON finances');
            } catch (\Throwable $e) {
                // Ignore if the index does not exist.
            }

            $this->forge->dropColumn('finances', 'transaction_date');
        }
    }
}
