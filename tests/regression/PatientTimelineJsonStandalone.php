<?php
declare(strict_types=1);
/** Actual timeline template, synthetic payload only; no application boot or database. */
if (PHP_SAPI !== 'cli') { exit(2); }
$source = file_get_contents(__DIR__ . '/../../app/Views/patient/show.php');
$start = strpos($source, '        allTimelineData = [');
$end = strpos($source, '        filteredTimelineData = ', $start);
$template = substr($source, $start, $end - $start);
$payload = '</script><img src=x onerror="window.timelineInjected=1"><script>window.timelineInjected=2</script>';
$item = array_fill_keys(['type','date','title','description','status','status_label','id','doctor','icon','color','url'], $payload);
$item['meta'] = [['label' => $payload, 'value' => $payload]];
$timeline = [$item];
ob_start();
eval('?>' . $template);
$javascript = ob_get_clean();
$checks = 0;
$check = static function (bool $ok, string $message) use (&$checks): void {
    if (!$ok) { throw new RuntimeException($message); }
    ++$checks;
};
$check(!str_contains(strtolower($javascript), '</script'), 'No HTML script terminator in serialized content');
$check(!str_contains($javascript, '<img'), 'No injected HTML markup');
$check(substr_count($template, 'JSON_HEX_TAG') === 12, 'Every timeline field and metadata uses safe JSON flags');
foreach ($item as $field => $expected) {
    preg_match('/^\s*' . preg_quote($field, '/') . ': (.+?),?\r?$/m', $javascript, $matches);
    $literal = rtrim($matches[1] ?? '', ',');
    if ($field === 'meta') { $literal = preg_replace('/\s*\}\s*$/', '', $literal); }
    $check(json_decode($literal, true, 512, JSON_THROW_ON_ERROR) === $expected, $field . ' retains exact original value');
}
// Optional integration proof against the pinned pre-fix source; not a runtime dependency.
if (in_array('--preservation-baseline', $argv, true)) {
    $baseline = str_replace("\r\n", "\n", shell_exec('git show ac5daa149db91d46d59c763ee4f8a0c6f24bd174:app/Views/patient/show.php'));
    $normalized = str_replace("\r\n", "\n", $source);
    $start = strpos($normalized, '        allTimelineData = [');
    $end = strpos($normalized, '        filteredTimelineData = ', $start);
    $baselineStart = strpos($baseline, '        allTimelineData = [');
    $baselineEnd = strpos($baseline, '        filteredTimelineData = ', $baselineStart);
    $check(substr($normalized, 0, $start) === substr($baseline, 0, $baselineStart), 'Preceding source, including odontogram, unchanged');
    $check(substr($normalized, $end) === substr($baseline, $baselineEnd), 'Following source, including odontogram, unchanged');
}
$output = ['status' => 'PASS', 'checks' => $checks, 'scope' => 'Actual timeline serialization; script terminators and exact payload roundtrip; protected source unchanged'];
if (in_array('--browser-fixture', $argv, true)) {
    $output['html'] = '<!doctype html><meta charset="utf-8"><script>let allTimelineData; ' . $javascript . ' window.timelineResult=allTimelineData;</script><p>synthetic timeline</p>';
    $output['payload'] = $payload;
}
echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
