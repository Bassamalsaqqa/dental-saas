<?php
declare(strict_types=1);
/** Manual real-SQL PR21 regression. Requires the existing private, guarded synthetic bootstrap.
 * No HTTP authorization claim; routed evidence is documented separately. Retains owned fixtures.
 */
if (PHP_SAPI !== 'cli') exit(2);
$options = getopt('', ['bootstrap:']);
$bootstrap = $options['bootstrap'] ?? '';
$expected = str_replace('\\', '/', sys_get_temp_dir()) . '/denta-ui-v3-20261023/final-acceptance/clinical-cli-bootstrap.php';
if (str_replace('\\', '/', $bootstrap) !== $expected || !is_file($bootstrap) || is_link($bootstrap)) {
    throw new RuntimeException('Explicit existing guarded synthetic bootstrap required.');
}
require $bootstrap;
if (realpath(APPPATH . '..') !== realpath(dirname(__DIR__, 2)) || !isset($db)
    || $db->database !== 'denta_pr21_acceptance_20261027' || (int) $db->port !== 3307
    || $db->hostname !== '127.0.0.1' || $db->username === 'root' || (int) $db->transDepth !== 0) {
    throw new RuntimeException('Isolated source/database ownership mismatch.');
}
$identity = $db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if ($identity['db'] !== $db->database || (int) $identity['port'] !== 3307
    || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')) {
    throw new RuntimeException('Dedicated MariaDB instance mismatch.');
}
$patient = $db->table('patients')->where('clinic_id', 1)->where('last_name', 'SyntheticRun')->get()->getRowArray();
if (!$patient) throw new RuntimeException('Owned synthetic workflow patient required.');
$checks = [];
$check = static function (bool $ok, string $name) use (&$checks): void {
    if (!$ok) throw new RuntimeException($name);
    $checks[] = $name;
};
$exams = new App\Models\ExaminationModel($db);
$id = $exams->insert(['clinic_id'=>1, 'patient_id'=>$patient['id'], 'examination_id'=>'PR21-'.bin2hex(random_bytes(6)),
    'examination_date'=>'2026-10-27', 'examination_type'=>'initial', 'chief_complaint'=>'Owned synthetic SQL preservation baseline',
    'status'=>'pending', 'created_by'=>1], true);
$check((bool) $id, 'Owned draft inserted');
$service = new App\Services\ExaminationNoteGovernanceService($db);
$state = static fn(): array => $db->table('examinations')->where('id', $id)->get()->getRowArray();
$before = $state();
try {
    $service->updateUnsignedWithLock(1, (int)$id, (int)$patient['id'], static function () use ($exams, $id): bool {
        if (!$exams->update($id, ['chief_complaint'=>'This required-write failure must roll back'])) throw new RuntimeException('Fixture update');
        throw new DomainException('Injected required-work failure');
    });
    throw new RuntimeException('Failed write committed');
} catch (DomainException $e) { $check($e->getMessage()==='Injected required-work failure', 'Required-write failure surfaced'); }
$check($state()===$before && (int)$db->transDepth===0, 'Required-write failure preserves original row and releases transaction');
try {
    $service->updateUnsignedWithLock(1, (int)$id, (int)$patient['id'], static function () use ($db, $exams, $id): bool {
        $exams->update($id, ['chief_complaint'=>'Nested transaction must never report committed success']);
        $db->transBegin();
        return false;
    });
    throw new RuntimeException('Nested write reported success');
} catch (DomainException $e) { $check(str_contains($e->getMessage(), 'commit'), 'Nested ownership violation fails closed'); }
$check($state()===$before && (int)$db->transDepth===0, 'Nested write fully unwinds without row mutation');
try {
    $service->updateUnsignedWithLock(2, (int)$id, (int)$patient['id'], static fn(): bool => false);
    throw new RuntimeException('Cross-clinic mutation accepted');
} catch (DomainException $e) { $check($state()===$before, 'Cross-clinic mutation preserves original row'); }
$completed = $service->updateUnsignedWithLock(1, (int)$id, (int)$patient['id'], static function ($locked) use ($exams, $id): bool {
    if (!$exams->update($id, ['status'=>'completed', 'chief_complaint'=>'Verified synthetic complete narrative'])) throw new RuntimeException('Completion write');
    return $locked['status']!=='completed';
});
$check($completed && $state()['status']==='completed', 'Successful locked update commits actual state');
$signed = $service->sign(1, (int)$id, 1, 'Synthetic SQL acceptance actor');
$beforeSigned = $state();
$check(json_decode($signed['signature']['snapshot_json'],true)['chief_complaint']===$beforeSigned['chief_complaint'], 'Signed snapshot equals persisted narrative');
try {
    $service->updateUnsignedWithLock(1, (int)$id, (int)$patient['id'], static fn(): bool => false);
    throw new RuntimeException('Signed edit accepted');
} catch (DomainException $e) { $check($state()===$beforeSigned && str_contains($e->getMessage(),'signed'), 'Signed edit denied with unchanged evidence'); }
echo json_encode(['passed'=>count($checks), 'checks'=>$checks, 'owned_fixture_retained'=>(int)$id,
    'scope'=>'Actual isolated MariaDB service transactions; HTTP/RBAC/CSRF are separate routed checks'], JSON_PRETTY_PRINT) . PHP_EOL;
