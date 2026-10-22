<?php
declare(strict_types=1);
/** Read-only existing data; owns only connection-local temporary synthetic tables. */
$bootstrap = getopt('', ['bootstrap:'])['bootstrap'] ?? '';
if (PHP_SAPI !== 'cli' || !is_file($bootstrap)) { exit(2); }
require $bootstrap;
$identity = $db->query('SELECT DATABASE() db, @@port port, @@datadir datadir')->getRowArray();
if ($db->hostname !== '127.0.0.1' || $identity['db'] !== 'm2c_finance_acceptance'
    || (int) $identity['port'] !== 3307
    || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')) {
    throw new RuntimeException('Owned synthetic MariaDB required');
}
$controller = file_get_contents(__DIR__ . '/../../app/Controllers/Patient.php');
$view = file_get_contents(__DIR__ . '/../../app/Views/patient/show.php');
$n = 0;
$check = static function (bool $ok, string $why) use (&$n): void {
    if (!$ok) { throw new RuntimeException($why); }
    $n++;
};
$definitions = [
    'examinations' => ['examinationModel', ['examinationItem', 'examRow', 'exam', 'examination']],
    'appointments' => ['appointmentModel', ['appointmentItem', 'appt', 'apt']],
    'treatments' => ['treatmentModel', ['treatmentItem', 'treatmentRow', 'treatment', 'trt']],
];
$prefix = 'projection_' . bin2hex(random_bytes(4)) . '_';
$metrics = [];
foreach ($definitions as $table => [$model, $variables]) {
    $pattern = '~\\$' . $table . ' = \\$this->' . $model . '\\s*->select\\(\'([^\']+)\'\\)~';
    if (!preg_match($pattern, $controller, $match)) { throw new RuntimeException('Projection not found: ' . $table); }
    $projection = $match[1];
    $check(!str_contains($projection, '*'), $table . ' explicit projection');
    $parts = array_map('trim', explode(',', $projection));
    $outputFields = [];
    $sourceFields = [];
    foreach ($parts as $part) {
        if (!preg_match('~^([a-z_]+)\\.([a-z_]+)(?: AS ([a-z_]+))?$~i', $part, $column)) {
            throw new RuntimeException('Unsupported projection expression');
        }
        $outputFields[] = $column[3] ?? $column[2];
        $sourceFields[$column[1]][$column[2]] = true;
    }
    foreach ($variables as $variable) {
        preg_match_all('~\\$' . $variable . '\\[[\'"]([^\'"]+)[\'"]\\]~', $view, $keys);
        foreach (array_unique($keys[1]) as $key) {
            $check(in_array($key, $outputFields, true), $table . ' preserves view field ' . $key);
        }
    }
    // A wide, unused clinical payload is intentionally present in the source table.
    // Joins have one matching row per record and exercise the actual selected aliases.
    $joins = [];
    foreach ($sourceFields as $alias => $fields) {
        $fields['id'] = true;
        if ($alias === $table) { $fields['clinic_id'] = true; $fields['patient_id'] = true; $fields['unused_clinical_payload'] = true; }
        $columns = [];
        foreach (array_keys($fields) as $field) { $columns[] = '`' . $field . '` ' . ($field === 'id' ? 'INT PRIMARY KEY' : 'LONGTEXT NULL'); }
        $physical = $prefix . $table . '_' . $alias;
        $db->query('CREATE TEMPORARY TABLE `' . $physical . '` (' . implode(',', $columns) . ') ENGINE=InnoDB');
        $rows = [];
        for ($id = 1; $id <= 120; $id++) {
            $row = [];
            foreach (array_keys($fields) as $field) {
                $row[$field] = match ($field) {
                    'id' => $id,
                    'clinic_id' => $id > 100 ? '2' : '1',
                    'patient_id' => $id > 90 ? '2' : '1',
                    'unused_clinical_payload' => str_repeat('sensitive-note-not-rendered;', 1000),
                    default => $id % 11 === 0 ? null : $field . '-' . $id,
                };
            }
            $rows[] = $row;
        }
        $db->table($physical)->insertBatch($rows);
        if ($alias === $table) { $from = '`' . $physical . '` ' . $alias; }
        else { $joins[] = ' LEFT JOIN `' . $physical . '` ' . $alias . ' ON ' . $alias . '.id = ' . $table . '.id'; }
    }
    $suffix = ' FROM ' . $from . implode('', $joins) . ' WHERE ' . $table . '.clinic_id=1 AND ' . $table . '.patient_id=1 ORDER BY ' . $table . '.id DESC';
    $wide = $db->query('SELECT ' . $table . '.*, ' . $projection . $suffix)->getResultArray();
    $narrow = $db->query('SELECT ' . $projection . $suffix)->getResultArray();
    $expected = [];
    foreach ($wide as $row) { $expected[] = array_intersect_key($row, array_flip($outputFields)); }
    $check($expected === $narrow, $table . ' exact projected values/order/nulls');
    $check(count($narrow) === 90, $table . ' complete matching history, foreign clinic/patient excluded');
    $check(!array_key_exists('unused_clinical_payload', $narrow[0]), $table . ' excludes unused private narrative');
    $before = strlen(json_encode($wide)); $after = strlen(json_encode($narrow));
    $check($after < $before / 10, $table . ' representative transfer reduction');
    $metrics[$table] = ['rows' => count($narrow), 'wide_json_bytes' => $before, 'projected_json_bytes' => $after];
}
echo json_encode(['status' => 'PASS', 'checks' => $n, 'measurements' => $metrics,
    'scope' => 'Actual MariaDB projection value/order/tenant parity with wide synthetic rows and all patient-view row-key coverage. Complete histories preserved; not bounded-row PHP loading or production performance certification.'], JSON_PRETTY_PRINT), PHP_EOL;
