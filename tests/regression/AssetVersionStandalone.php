<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . '/denta-assets-' . bin2hex(random_bytes(8));
if (!mkdir($root . '/assets', 0700, true)) { throw new RuntimeException('Fixture directory unavailable'); }
define('FCPATH', $root . DIRECTORY_SEPARATOR);
require __DIR__ . '/../../app/Helpers/asset_helper.php';
$n = 0;
function check(bool $ok, string $message): void { global $n; if (!$ok) { throw new RuntimeException($message); } $n++; }
$file = $root . '/assets/example.css';
try {
    file_put_contents($file, 'body{}');
    touch($file, 1700000000);
    check(asset_version('assets/example.css') === '1700000000-6', 'Stable metadata version');
    check(asset_version('assets/example.css') === asset_version('assets/example.css'), 'Repeated calls stable');
    check(asset_version('assets/absent.css') === 'missing', 'Missing asset deterministic');
    foreach (['../example.css','/assets/example.css',"assets\\example.css",'assets/example.css?x=1','assets/example.css#x',"assets/invalid\0.css"] as $bad) {
        check(asset_version($bad) === 'missing', 'Invalid path denied');
    }
    $layout = file_get_contents(__DIR__ . '/../../app/Views/layouts/main_auth.php');
    check(!str_contains($layout, '<?= time() ?>'), 'No every-request cache busting');
    check(substr_count($layout, 'asset_version(') === 8, 'All existing versioned assets preserved');
    echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Stable versions, invalid paths and actual layout asset inventory']), PHP_EOL;
} finally {
    unlink($file);
    rmdir($root . '/assets');
    rmdir($root);
}
