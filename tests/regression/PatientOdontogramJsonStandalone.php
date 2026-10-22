<?php
declare(strict_types=1);
/** Owner-authorized serialization boundary only. No chart code, assets, DB or snapshots mutate. */
if (PHP_SAPI !== 'cli') { exit(2); }
$source = file_get_contents(__DIR__ . '/../../app/Views/patient/show.php');
$payload = '</script><img src=x onerror="window.chartInjected=1"><script>window.chartInjected=2</script>';
$odontogram = [['tooth_number'=>12,'condition_type'=>$payload,'condition_description'=>$payload,'treatment_notes'=>$payload,'treatment_status'=>$payload,'treatment_date'=>$payload]];
$a = strpos($source, '    const patientMiniOdontogramData ='); $z = strpos($source, ';', $a);
$mini = substr($source, $a, $z - $a + 1);
$a = strpos($source, '    let odontogramData = ['); $z = strpos($source, '    ];', $a);
$full = substr($source, $a, $z - $a + strlen('    ];'));
ob_start(); eval('?>' . $mini . PHP_EOL . $full); $javascript = ob_get_clean();
$checks = 0;
$check = static function (bool $ok, string $message) use (&$checks): void { if (!$ok) { throw new RuntimeException($message); } ++$checks; };
$check(!str_contains(strtolower($javascript), '</script'), 'Neither chart data boundary permits script termination');
$check(!str_contains($javascript, '<img'), 'No injected HTML element');
$check(substr_count($mini . $full, 'JSON_HEX_TAG') === 6, 'Exactly six authorized data serialization slots hardened');
preg_match('/const patientMiniOdontogramData = (.+);/', $javascript, $match);
$check(json_decode($match[1], true, 512, JSON_THROW_ON_ERROR) === $odontogram, 'Entire mini chart retains exact original evidence values');
foreach (['condition_type','condition_description','treatment_notes','treatment_status','treatment_date'] as $field) {
    preg_match('/^\s*' . $field . ': (".*")/m', $javascript, $match);
    $check(json_decode($match[1], true, 512, JSON_THROW_ON_ERROR) === $payload, $field . ' exact decode retained');
}
$check(str_contains($javascript, 'tooth_number: 12'), 'Stable tooth identity unchanged');
$result=['status'=>'PASS','checks'=>$checks,'scope'=>'Actual protected JSON template evaluated with synthetic script-breakout payload; original decoded values and tooth identity unchanged'];
if (in_array('--browser-fixture', $argv, true)) {
    $result['html']='<!doctype html><meta charset="utf-8"><script>'.$javascript.' window.chartFixture={mini:patientMiniOdontogramData,full:odontogramData};</script>';
    $result['expected']=$odontogram;
}
echo json_encode($result,JSON_THROW_ON_ERROR),PHP_EOL;
