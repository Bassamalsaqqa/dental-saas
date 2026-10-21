<?php
declare(strict_types=1);
$options = getopt('', ['bootstrap:']);
$bootstrap = $options['bootstrap'] ?? '';
if (PHP_SAPI !== 'cli' || !is_file($bootstrap)) { exit(2); }
require $bootstrap;
$identity = $db->query('SELECT DATABASE() db, @@port port, @@datadir datadir, CURRENT_USER() actor')->getRowArray();
if ($db->hostname !== '127.0.0.1' || $identity['db'] !== 'm2c_synthetic_acceptance' || (int) $identity['port'] !== 3307
    || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')
    || !str_starts_with($identity['actor'], 'm2c_test@')) { throw new RuntimeException('Disposable identity required.'); }
// Read-only: use existing owned timezone fixtures; no DDL, grant or fixture mutation.
$service = new App\Services\InvoiceCorrectionService($db);
$method = new ReflectionMethod($service, 'businessNow');
$n = 0;
$cases = [
    [1, '2027-01-01T01:00:00Z', '2027-01-01 03:00:00 +02:00'],
    [2, '2027-01-01T01:00:00Z', '2026-12-31 15:00:00 -10:00'],
    [1, '2026-03-27T00:30:00Z', '2026-03-27 03:30:00 +03:00'],
    [1, '2026-10-24T23:30:00Z', '2026-10-25 01:30:00 +02:00'],
];
foreach ($cases as [$clinic, $instant, $expected]) {
    $service->clock = static fn () => new DateTimeImmutable($instant);
    if ($method->invoke($service, $clinic)->format('Y-m-d H:i:s P') !== $expected) { throw new RuntimeException('Business boundary mismatch.'); }
    $n++;
}
$service->clock = static fn () => 'invalid';
try { $method->invoke($service, 1); throw new LogicException('Invalid clock accepted.'); }
catch (RuntimeException $e) { if ($e->getMessage() !== 'Invalid operational correction clock.') { throw $e; } $n++; }
echo json_encode(['status' => 'PASS', 'checks' => $n, 'read_only' => true, 'scope' => 'Clinic-local year/day, explicit DST offsets and invalid clock; not issuance expiry SQL acceptance']), PHP_EOL;
