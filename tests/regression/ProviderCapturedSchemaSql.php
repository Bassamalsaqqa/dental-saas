<?php
declare(strict_types=1);
// Opt-in fixture acceptance; never falls back to the application/business database.
$bootstrap = getopt('', ['bootstrap:'])['bootstrap'] ?? '';
if (PHP_SAPI !== 'cli' || !is_file($bootstrap)) exit(2);
require $bootstrap;
$identity = $db->query('SELECT DATABASE() db, @@port port, @@datadir datadir')->getRowArray();
if ($db->hostname !== '127.0.0.1' || $identity['db'] !== 'm3m4_host_schema_20261022_fab33c62'
    || (int) $identity['port'] !== 3307
    || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')
    || $db->transDepth !== 0) throw new RuntimeException('Owned disposable representative schema required');
$checks = 0;
$check = static function (bool $ok, string $message) use (&$checks): void {
    if (!$ok) throw new RuntimeException($message);
    ++$checks;
};
$tables = ['users', 'roles', 'clinic_users', 'clinic_provider_profiles', 'settings', 'appointments', 'finances', 'finance_payments'];
$snapshot = static function () use ($db, $tables): array {
    $rows = [];
    foreach ($tables as $table) $rows[$table] = $db->table($table)->orderBy('id')->get()->getResultArray();
    return $rows;
};
$before = $snapshot();
$directory = new App\Services\ProviderDirectoryService($db);
$check(!$directory->isEnabled(1) && !$directory->isEnabled(2), 'Both clinic flags must start OFF');
$check($directory->isSchemaReady(), 'Reviewed additive schema must already exist');
if (!$db->transBegin()) throw new RuntimeException('Fixture transaction failed');
try {
    $write = static function (bool $ok): void { if (!$ok) throw new RuntimeException('Synthetic fixture write failed'); };
    $write($db->table('users')->whereIn('id', [1, 2])->update(['first_name' => 'Same', 'last_name' => 'Doctor']));
    $write($db->table('roles')->whereIn('id', [1, 2])->update(['is_doctor' => 1]));
    $ids = static fn(array $rows): array => array_column($rows, 'user_id');
    $check($ids($directory->listForClinic(1)) === [1, 2], 'Canonical doctor flags auto-populate existing memberships without profiles');
    $check($directory->getBookingContext(1)['providers'] === [], 'OFF retains legacy booking resources');
    $write($db->table('settings')->insert(['clinic_id' => 1, 'setting_key' => 'dynamic_doctor_scheduling_enabled', 'setting_value' => '1', 'setting_type' => 'string']));
    $context = $directory->getBookingContext(1);
    $check($context['enabled'] && $ids($context['providers']) === [1, 2] && $context['chair_options'] === [], 'Explicit clinic opt-in selects stable doctor identities');
    foreach (['en', 'ar'] as $locale) {
        service('language')->setLocale($locale);
        $labels = array_map(static fn(array $r): string => App\Libraries\ProviderDisplayLabel::forOption($r['user_id'], $r['name']), $context['providers']);
        $check($labels[0] !== $labels[1] && str_starts_with($labels[0], '#1 - ') && str_starts_with($labels[1], '#2 - '), 'Duplicate names retain first-position identity in ' . $locale);
    }
    $check(!$directory->isEnabled(2) && $directory->findBookable(2, 1) === null, 'Other clinic never borrows active resources');
    $write($db->table('users')->where('id', 2)->update(['active' => 0]));
    $check($directory->findBookable(1, 2) === null, 'Inactive doctor excluded');
    $write($db->table('users')->where('id', 2)->update(['active' => 1]));
    $write($db->table('roles')->where('id', 2)->update(['clinic_id' => 2]));
    $check($directory->findBookable(1, 2) === null, 'Foreign clinic-scoped role excluded');
    $write($db->table('roles')->where('id', 2)->update(['clinic_id' => 1, 'is_active' => 0]));
    $check($directory->findBookable(1, 2) === null, 'Inactive role excluded');
    $write($db->table('roles')->where('id', 2)->update(['is_active' => 1, 'is_doctor' => 0, 'is_medical' => 1]));
    $check($directory->findBookable(1, 2) === null, 'Medical assistant flag alone is not doctor eligibility');
    $write($db->table('clinic_provider_profiles')->insert(['clinic_id' => 1, 'user_id' => 2, 'is_clinician' => 1, 'is_bookable' => 1,
        'weekly_hours_json' => json_encode(['monday' => [['start' => '08:00', 'end' => '12:00'], ['start' => '13:00', 'end' => '19:00']]], JSON_THROW_ON_ERROR),
        'exceptions_json' => json_encode(['2026-11-16' => []], JSON_THROW_ON_ERROR)]));
    $check($directory->findBookable(1, 2) !== null, 'Explicit approved clinician profile restores eligibility');
    session()->set('active_clinic_id', 2);
    $schedule = new App\Services\ProviderScheduleService($db);
    $check($schedule->getEffectiveSchedule(1, 2, '2026-11-09') === [['start' => '09:00', 'end' => '12:00'], ['start' => '13:00', 'end' => '17:00']], 'Availability intersects clinic hours and lunch without borrowing session clinic');
    $check($schedule->getEffectiveSchedule(1, 2, '2026-11-16') === [], 'Explicit leave removes availability');
    $write($db->table('clinic_provider_profiles')->where(['clinic_id' => 1, 'user_id' => 2])->update(['is_bookable' => 0]));
    $check($directory->findBookable(1, 2) === null, 'Unbookable clinician excluded');
    $check($db->transStatus(), 'All synthetic writes remain in successful fixture transaction');
} finally {
    if ($db->transDepth > 0 && !$db->transRollback()) throw new RuntimeException('Fixture rollback failed');
    session()->set('active_clinic_id', 1);
    service('language')->setLocale('en');
}
$check($snapshot() === $before, 'Every original user/role/membership/profile/setting/appointment/invoice/payment row preserved');
$check(!$directory->isEnabled(1) && !$directory->isEnabled(2), 'Both scheduling flags remain OFF after rollback');
echo json_encode(['status' => 'PASS', 'checks' => $checks, 'scope' => 'Actual production table definitions with synthetic-only transactional doctor directory and availability; no native Hostinger concurrency claim']), PHP_EOL;
