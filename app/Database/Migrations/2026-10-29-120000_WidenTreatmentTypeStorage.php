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
        $alreadyWide = $column['COLUMN_TYPE'] === 'varchar('.self::LENGTH.')';
        if (!$alreadyWide && $column['COLUMN_TYPE'] !== self::OLD_TYPE) throw new \RuntimeException('Unreviewed treatment storage definition; no automatic conversion.');
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
        $zone = (string)$this->db->query('SELECT @@SESSION.time_zone AS zone')->getRowArray()['zone'];
        $identity = $this->db->query('SELECT DATABASE() AS db, @@hostname AS server')->getRowArray();
        $journal = $this->journalPath(hash('sha256', $identity['server']."\0".$identity['db']."\0".$name));
        $locked = false;
        try {
            $this->db->query("SET SESSION time_zone='+00:00'");
            $this->db->query('SET SESSION sql_mode='.$this->db->escape($mode.($mode === ''?'':',').'STRICT_ALL_TABLES'));
            $this->db->query('SET SESSION lock_wait_timeout=5');
            $this->db->query('LOCK TABLES '.$table.' WRITE');
            $locked = true;
            $lockedType=$this->db->query('SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?',[$name,'treatment_type'])->getRowArray()['COLUMN_TYPE'];
            if ($lockedType!==$column['COLUMN_TYPE']) throw new \RuntimeException('Treatment storage changed while waiting; preserve and retry after metadata review.');
            $prepared = is_file($journal) ? $this->readJournal($journal) : null;
            if ($alreadyWide) {
                if (!$prepared) throw new \RuntimeException('Widened storage has no original ENUM provenance journal; preserve and reconcile.');
                $this->verifyJournalIdentity($prepared, $identity, $name);
                if (is_file($journal.'.complete')) {
                    $complete = $this->readJournal($journal.'.complete');
                    $this->verifyCompletionMetadata($complete, hash_file('sha256', $journal));
                    $this->verifyConvertedDefinition($prepared['source_ddl'], $table, true);
                    return; // Completed, recorded conversion; later legitimate rows are not a rerun failure.
                }
                $this->verifyRows($prepared['rows'], $this->snapshot($table, false));
                $this->verifyConvertedDefinition($prepared['source_ddl'], $table);
                $this->publishJournal($journal.'.complete', ['version'=>1, 'prepared_sha256'=>hash_file('sha256', $journal), 'target_type'=>'varchar(100)', 'verified_at'=>gmdate('c')]);
                return; // Recovery after ALTER committed but before completion was published.
            }
            $rows = $this->snapshot($table, true);
            $sourceDdl = $this->db->query('SHOW CREATE TABLE '.$table)->getRowArray()['Create Table'];
            foreach ($rows as $row) {
                $key = hex2bin($row['type_hex']);
                if ($key === false || strlen($key) > self::LENGTH
                    || ($key === '' ? $row['enum_index'] !== '0' : !isset(\App\Libraries\TreatmentCatalog::all()[$key]))) {
                    throw new \RuntimeException('Unrecognized historical treatment representation; preserve and review before conversion.');
                }
            }
            if ($prepared) {
                $this->verifyJournalIdentity($prepared, $identity, $name);
                if ($prepared['source_ddl'] !== $sourceDdl || $prepared['rows'] !== $rows) throw new \RuntimeException('Prepared provenance differs from current rows; no overwrite or automatic reconciliation.');
            } else {
                $prepared = ['version'=>1, 'database'=>$identity['db'], 'server'=>$identity['server'], 'table'=>$name,
                    'source_type'=>self::OLD_TYPE, 'target_type'=>'varchar(100)', 'source_ddl'=>$sourceDdl,
                    'captured_at'=>gmdate('c'), 'rows'=>$rows,
                    'meaning'=>'Persisted bytes and original ENUM index only. Original invalid input and clinical meaning are unknown.'];
                $this->publishJournal($journal, $prepared);
            }
            // DDL commits implicitly. The immutable original journal must exist before ALTER.
            if (!$this->db->query('ALTER TABLE '.$table.' MODIFY COLUMN `treatment_type` VARCHAR('.self::LENGTH.') CHARACTER SET utf8mb4 COLLATE '.$column['COLLATION_NAME'].' NOT NULL, ALGORITHM=COPY, LOCK=SHARED')) throw new \RuntimeException('Treatment storage ALTER failed; inspect metadata and journal before retry.');
            if ($this->db->query('SHOW WARNINGS')->getResultArray()) throw new \RuntimeException('Treatment ALTER returned warnings; preserve journal and inspect evidence.');
            $this->verifyRows($rows, $this->snapshot($table, false));
            $this->verifyConvertedDefinition($sourceDdl, $table);
            $this->publishJournal($journal.'.complete', ['version'=>1, 'prepared_sha256'=>hash_file('sha256', $journal), 'target_type'=>'varchar(100)', 'verified_at'=>gmdate('c')]);
        } finally {
            if ($locked) $this->db->query('UNLOCK TABLES');
            $this->db->query('SET SESSION sql_mode='.$this->db->escape($mode));
            $this->db->query('SET SESSION lock_wait_timeout='.$wait);
            $this->db->query('SET SESSION time_zone='.$this->db->escape($zone));
            $this->db->resetDataCache();
        }
    }

    /** Per-ID byte facts and full-row hashes; never store patient narratives in the journal. */
    private function snapshot(string $table, bool $enum): array
    {
        $rows = $this->db->query('SELECT *, HEX(treatment_type) AS __legacy_hex'
            .($enum ? ', treatment_type+0 AS __legacy_index' : '').' FROM '.$table.' ORDER BY id')->getResultArray();
        $out=[];
        foreach ($rows as $row) {
            $hex=(string)$row['__legacy_hex']; $index=$enum ? (string)$row['__legacy_index'] : null;
            unset($row['__legacy_hex'], $row['__legacy_index']); ksort($row, SORT_STRING);
            $out[]=['id'=>(string)$row['id'], 'clinic_id'=>(string)$row['clinic_id'], 'type_hex'=>$hex,
                'enum_index'=>$index, 'row_sha256'=>hash('sha256', json_encode($row, JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE))];
        }
        return $out;
    }

    private function verifyRows(array $original, array $current): void
    {
        foreach ($original as &$row) $row['enum_index']=null; unset($row);
        if ($original !== $current) throw new \RuntimeException('Per-record treatment preservation mismatch; retain original journal. No automatic repair/narrowing.');
    }

    private function verifyConvertedDefinition(string $sourceDdl, string $table, bool $completed = false): void
    {
        $afterDdl = $this->db->query('SHOW CREATE TABLE '.$table)->getRowArray()['Create Table'];
        $expectedDdl = str_replace('`treatment_type` '.self::OLD_TYPE, '`treatment_type` varchar(100)', $sourceDdl, $replacements);
        if ($completed) {
            // This counter advances on legitimate inserts/rolled-back reservations. It is
            // data, not schema. Preserve the AUTO_INCREMENT column attribute and every
            // other table/index/constraint option; normalize only MariaDB's exact option.
            $normalize = static fn(string $ddl): string => preg_replace(
                '/(\n\) ENGINE=InnoDB) AUTO_INCREMENT=[1-9][0-9]*(?= DEFAULT CHARSET=)/', '$1', $ddl
            );
            $afterDdl = $normalize($afterDdl);
            $expectedDdl = $normalize($expectedDdl);
        }
        if ($replacements !== 1 || $afterDdl !== $expectedDdl) throw new \RuntimeException('Unexpected post-DDL table/index/constraint definition; preserve journal and inspect.');
    }

    private function verifyCompletionMetadata(array $complete, string $preparedHash): void
    {
        $keys = array_keys($complete); sort($keys, SORT_STRING);
        $time = $complete['verified_at'] ?? null;
        $date = is_string($time) ? \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $time) : false;
        if ($keys !== ['prepared_sha256', 'target_type', 'verified_at', 'version']
            || ($complete['version'] ?? null) !== 1 || ($complete['target_type'] ?? null) !== 'varchar(100)'
            || !is_string($complete['prepared_sha256'] ?? null)
            || !hash_equals($preparedHash, $complete['prepared_sha256'])
            || !$date || $date->format('Y-m-d\TH:i:sP') !== $time || !str_ends_with($time, '+00:00')) {
            throw new \RuntimeException('Completion journal metadata is invalid; preserve original evidence.');
        }
    }

    private function verifyJournalIdentity(array $journal, array $identity, string $table): void
    {
        if (($journal['version']??null)!==1 || ($journal['database']??null)!==$identity['db']
            || ($journal['server']??null)!==$identity['server'] || ($journal['table']??null)!==$table
            || ($journal['source_type']??null)!==self::OLD_TYPE || ($journal['target_type']??null)!=='varchar(100)'
            || !is_array($journal['rows']??null) || !is_string($journal['source_ddl']??null)) throw new \RuntimeException('Original treatment journal identity is invalid.');
    }

    private function journalPath(string $identity): string
    {
        $base=rtrim(WRITEPATH, '/\\');
        foreach ([$base, $base.'/recovery', $base.'/recovery/treatment-type-storage'] as $path) {
            if (!file_exists($path) && !mkdir($path, 0700)) throw new \RuntimeException('Private provenance directory could not be created.');
            if (is_link($path) || !is_dir($path) || str_replace('\\','/',realpath($path))!==str_replace('\\','/',$path)) throw new \RuntimeException('Private canonical provenance directory required.');
        }
        return $base.'/recovery/treatment-type-storage/'.$identity.'.json';
    }

    private function readJournal(string $path): array
    {
        if (is_link($path) || !is_file($path) || filesize($path)>16*1024*1024) throw new \RuntimeException('Provenance journal is unsafe or unavailable.');
        $envelope=json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $body=json_encode($envelope['payload']??null, JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
        if (!is_string($envelope['sha256']??null) || !hash_equals($envelope['sha256'],hash('sha256',$body))) throw new \RuntimeException('Provenance journal integrity mismatch.');
        return $envelope['payload'];
    }

    private function publishJournal(string $path, array $payload): void
    {
        if (file_exists($path) || is_link($path)) throw new \RuntimeException('Never overwrite original provenance evidence.');
        $body=json_encode($payload, JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
        $bytes=json_encode(['payload'=>$payload,'sha256'=>hash('sha256',$body)], JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
        $temporary=$path.'.'.bin2hex(random_bytes(8)).'.staged'; $file=fopen($temporary,'x+b');
        if (!$file) throw new \RuntimeException('Private provenance publication failed.');
        try {
            if (!chmod($temporary,0600) || fwrite($file,$bytes)!==strlen($bytes) || !fflush($file) || !fsync($file)) throw new \RuntimeException('Private provenance durability failed; staged evidence retained.');
        } finally { fclose($file); }
        if (!link($temporary,$path)) throw new \RuntimeException('Exclusive provenance publication failed; staged evidence retained.');
        // The staging hard link is deliberately retained; recovery/backup includes both names.
        $directory=PHP_OS_FAMILY==='Windows' ? false : fopen(dirname($path),'r');
        if (PHP_OS_FAMILY!=='Windows' && $directory===false) throw new \RuntimeException('Provenance directory could not be opened for durability.');
        if ($directory!==false) { try { if (PHP_OS_FAMILY!=='Windows' && !fsync($directory)) throw new \RuntimeException('Provenance directory durability failed.'); } finally { fclose($directory); } }
        if (hash_file('sha256',$path)!==hash('sha256',$bytes)) throw new \RuntimeException('Published provenance verification failed.');
    }

    public function down()
    {
        throw new \RuntimeException('Preserve widened treatment storage and clinical keys. Automatic ENUM narrowing is prohibited.');
    }
}
