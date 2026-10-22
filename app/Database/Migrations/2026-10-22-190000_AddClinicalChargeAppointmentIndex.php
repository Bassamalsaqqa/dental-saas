<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Nonunique lookup expansion only; no charge/appointment or scheduling state changes. */
class AddClinicalChargeAppointmentIndex extends Migration
{
    private const INDEX = 'idx_ccl_clinic_appointment';

    public function up()
    {
        $this->assertParent();
        $indexes = $this->indexes();
        if (isset($indexes[self::INDEX])) {
            if (!$this->matches($indexes[self::INDEX], true)) {
                throw new \RuntimeException('Charge appointment index name conflicts; no automatic replacement.');
            }
            return;
        }
        foreach ($indexes as $parts) {
            if ($this->matches($parts, false)) {
                return; // An existing nonunique covering prefix already serves the query.
            }
        }
        $table = $this->table();
        if (!$this->db->query('ALTER TABLE ' . $table . ' ADD INDEX ' . self::INDEX . ' (clinic_id, appointment_id), ALGORITHM=INPLACE, LOCK=NONE')) {
            throw new \RuntimeException('Online charge appointment index expansion failed.');
        }
        if (!$this->matches($this->indexes()[self::INDEX] ?? [], true)) {
            throw new \RuntimeException('Charge appointment index postcondition failed; inspect retained DDL.');
        }
    }

    public function down()
    {
        // up() can reuse an existing index; definition equality is not ownership.
        throw new \RuntimeException('Charge lookup index is retained; automatic rollback refused because object ownership is unproven.');
    }

    private function table(): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable('clinical_charge_lines'));
    }

    private function assertParent(): void
    {
        if (!$this->db->tableExists('clinical_charge_lines')) {
            throw new \RuntimeException('Charge parent schema missing.');
        }
        $name = $this->db->prefixTable('clinical_charge_lines');
        $engine = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$name])->getRowArray();
        if (strtoupper($engine['ENGINE'] ?? '') !== 'INNODB') {
            throw new \RuntimeException('Charge lookup expansion requires InnoDB.');
        }
        $columns = $this->db->query("SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME IN ('clinic_id','appointment_id')", [$name])->getResultArray();
        if (count($columns) !== 2) {
            throw new \RuntimeException('Charge lookup columns missing.');
        }
        foreach ($columns as $column) {
            if (!in_array(strtolower($column['DATA_TYPE']), ['int', 'bigint', 'mediumint', 'smallint'], true)) {
                throw new \RuntimeException('Charge lookup column type incompatible.');
            }
        }
    }

    private function indexes(): array
    {
        $rows = $this->db->query('SHOW INDEX FROM ' . $this->table())->getResultArray();
        $indexes = [];
        foreach ($rows as $row) {
            $indexes[$row['Key_name']][(int) $row['Seq_in_index']] = $row;
        }
        foreach ($indexes as &$parts) {
            ksort($parts);
            $parts = array_values($parts);
        }
        unset($parts);
        return $indexes;
    }

    private function matches(array $parts, bool $exact): bool
    {
        if (count($parts) < 2 || ($exact && count($parts) !== 2)) {
            return false;
        }
        foreach (['clinic_id', 'appointment_id'] as $position => $column) {
            $part = $parts[$position];
            if ($part['Column_name'] !== $column || (int) $part['Non_unique'] !== 1
                || $part['Sub_part'] !== null || strtoupper($part['Index_type']) !== 'BTREE'
                || strtoupper($part['Collation'] ?? '') !== 'A'
                || strtoupper($part['Ignored'] ?? 'NO') !== 'NO'
                || strtoupper($part['Visible'] ?? 'YES') !== 'YES') {
                return false;
            }
        }
        return true;
    }
}
