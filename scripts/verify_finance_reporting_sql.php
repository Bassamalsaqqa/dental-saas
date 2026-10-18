<?php
/** Actual-model SQL regression. Never chooses or creates a database connection. */
if (PHP_SAPI !== 'cli') { exit(2); }
$options = getopt('', ['bootstrap:']);
$bootstrap = $options['bootstrap'] ?? null;
if (!is_string($bootstrap) || !str_starts_with($bootstrap, '/home/u556956644/.denta-stage-20261017/') || !is_file($bootstrap) || is_link($bootstrap)) {
    fwrite(STDERR, "Explicit private synthetic bootstrap required: --bootstrap=/home/u556956644/.denta-stage-20261017/<guarded-bootstrap>.php\n");
    exit(2);
}
try {
    require $bootstrap;
    if (!isset($db) || !$db instanceof \CodeIgniter\Database\BaseConnection) { throw new RuntimeException('Guarded bootstrap must inject the synthetic CI connection.'); }
    require __DIR__ . '/fixtures/FinanceReportingSqlFixture.php';
    session()->set(['active_clinic_id'=>1,'user_id'=>1,'global_mode'=>false]);
    $before=[];
    foreach (['finances','finance_payments','finance_liquidity_transfers'] as $table) {
        $before[$table]=$db->query('CHECKSUM TABLE `'.$table.'`')->getRowArray()['Checksum'];
    }
    $result=F1IndependentSqlFixture::run($db);
    foreach ($before as $table=>$checksum) {
        if ($db->query('CHECKSUM TABLE `'.$table.'`')->getRowArray()['Checksum'] !== $checksum) { throw new RuntimeException('SQL fixture changed pre-existing table data.'); }
    }
    if ((int)$db->transDepth !== 0) { throw new RuntimeException('SQL fixture left a transaction open.'); }
    $result['checksums_unchanged']=true;
    echo json_encode($result,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, "Finance reporting SQL verification failed (".get_class($error)."). Check private logs; no credentials are printed.\n");
    exit(1);
}