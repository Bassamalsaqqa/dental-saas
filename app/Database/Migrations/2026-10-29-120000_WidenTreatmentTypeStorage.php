<?php
declare(strict_types=1);
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Forward-only storage widening. Does not classify, remap, update or delete clinical records. */
final class WidenTreatmentTypeStorage extends Migration
{
    public const LENGTH = 100; // Catalog keys currently <=29 ASCII bytes; leaves bounded future room.
    private const OLD_TYPE = "enum('cleaning','extraction','filling','crown','root_canal','orthodontic','implant','other')";

    public function up()
    {
        // ALTER implicitly commits. Never let it commit somebody else's transaction.
        if ((int)$this->db->transDepth !== 0 || !$this->db->transStatus()) {
            throw new \RuntimeException('Treatment storage migration requires a healthy standalone DDL session.');
        }
        $name = $this->db->prefixTable('treatments');
        $table = $this->db->escapeIdentifiers($name);
        $meta = $this->db->query('SELECT ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$name])->getRowArray();
        $column = $this->db->query('SELECT DATA_TYPE,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,CHARACTER_SET_NAME,COLLATION_NAME,COLUMN_COMMENT,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?', [$name,'treatment_type'])->getRowArray();
        if (!$meta || strtoupper($meta['ENGINE']) !== 'INNODB' || !$column) throw new \RuntimeException('Expected InnoDB treatment storage is unavailable.');
        if ($column['IS_NULLABLE'] !== 'NO' || $column['COLUMN_DEFAULT'] !== null || $column['EXTRA'] !== '' || $column['COLUMN_COMMENT'] !== '' || $column['CHARACTER_SET_NAME'] !== 'utf8mb4' || !preg_match('/\Autf8mb4_[a-z0-9_]+\z/D', $column['COLLATION_NAME'])) {
            throw new \RuntimeException('Unexpected treatment type attributes; review metadata before DDL.');
        }
        if ($column['COLUMN_TYPE'] === 'varchar('.self::LENGTH.')') return; // Exact reviewed target only.
        if ($column['COLUMN_TYPE'] !== self::OLD_TYPE) throw new \RuntimeException('Unreviewed treatment storage definition; no automatic conversion.');
        $references = $this->db->query("SELECT TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA=DATABASE() AND ((TABLE_NAME=? AND COLUMN_NAME='treatment_type' AND REFERENCED_TABLE_NAME IS NOT NULL) OR (REFERENCED_TABLE_NAME=? AND REFERENCED_COLUMN_NAME='treatment_type'))", [$name,$name])->getResultArray();
        if ($references) throw new \RuntimeException('Treatment type foreign-key dependency needs separate review.');
        $checks = $this->db->query('SELECT cc.CHECK_CLAUSE FROM information_schema.CHECK_CONSTRAINTS cc JOIN information_schema.TABLE_CONSTRAINTS tc ON tc.CONSTRAINT_SCHEMA=cc.CONSTRAINT_SCHEMA AND tc.CONSTRAINT_NAME=cc.CONSTRAINT_NAME WHERE tc.TABLE_SCHEMA=DATABASE() AND tc.TABLE_NAME=?', [$name])->getResultArray();
        foreach ($checks as $check) if (stripos($check['CHECK_CLAUSE'],'treatment_type') !== false) throw new \RuntimeException('Treatment type check constraint requires separate review.');
        $indexes = $this->db->query('SHOW INDEX FROM '.$table)->getResultArray();
        foreach ($indexes as $index) {
            if ($index['Column_name'] !== 'treatment_type') continue;
            $parts = array_filter($indexes, static fn($part) => $part['Key_name'] === $index['Key_name']);
            if (count($parts) !== 1 || strtoupper($index['Index_type']) !== 'BTREE' || $index['Sub_part'] !== null || (int)$index['Non_unique'] !== 1) throw new \RuntimeException('Unreviewed treatment type index; no DDL.');
        }
        $mode = (string)$this->db->query('SELECT @@SESSION.sql_mode AS mode')->getRowArray()['mode'];
        $wait = (int)$this->db->query('SELECT @@SESSION.lock_wait_timeout AS seconds')->getRowArray()['seconds'];
        // COPY needs a reviewed maintenance window. An explicit WRITE table lock spans
        // the preservation snapshots: concurrent legitimate writes must not look like data loss.
        // This blocks table reads/writes briefly; it is NOT an instant/online migration.
        $locked = false;
        try {
            $this->db->query('SET SESSION sql_mode='.$this->db->escape($mode.($mode === ''?'':',').'STRICT_ALL_TABLES'));
            $this->db->query('SET SESSION lock_wait_timeout=5');
            $this->db->query('LOCK TABLES '.$table.' WRITE');
            $locked = true;
        $values = $this->db->query('SELECT HEX(treatment_type) AS value_hex,COUNT(*) AS n FROM '.$table.' GROUP BY BINARY treatment_type ORDER BY value_hex')->getResultArray();
        foreach ($values as $value) {
            $key = hex2bin($value['value_hex']);
            // Empty/invalid historical ENUM representations are not silently reinterpreted.
            if ($key === false || $key === '' || strlen($key) > self::LENGTH || !isset(\App\Libraries\TreatmentCatalog::all()[$key])) throw new \RuntimeException('Unrecognized historical treatment type; preserve and review before conversion.');
        }
            if (!$this->db->query('ALTER TABLE '.$table.' MODIFY COLUMN `treatment_type` VARCHAR('.self::LENGTH.') CHARACTER SET utf8mb4 COLLATE '.$column['COLLATION_NAME'].' NOT NULL, ALGORITHM=COPY, LOCK=SHARED')) throw new \RuntimeException('Treatment storage ALTER failed; inspect metadata before retry.');
            if ($this->db->query('SHOW WARNINGS')->getResultArray()) throw new \RuntimeException('Treatment ALTER returned warnings; inspect preserved evidence.');
            $after = $this->db->query('SELECT HEX(treatment_type) AS value_hex,COUNT(*) AS n FROM '.$table.' GROUP BY BINARY treatment_type ORDER BY value_hex')->getResultArray();
            if ($values !== $after) throw new \RuntimeException('Treatment type preservation mismatch; do not narrow or repair automatically.');
        } finally {
            if ($locked) $this->db->query('UNLOCK TABLES');
            $this->db->query('SET SESSION sql_mode='.$this->db->escape($mode));
            $this->db->query('SET SESSION lock_wait_timeout='.$wait);
            $this->db->resetDataCache();
        }
    }

    public function down()
    {
        throw new \RuntimeException('Preserve widened treatment storage and clinical keys. Automatic ENUM narrowing is prohibited.');
    }
}
