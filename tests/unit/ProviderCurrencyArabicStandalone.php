<?php
declare(strict_types=1);
/** CLI-only UTF-8 corruption regression. No framework or database bootstrap. */
if (PHP_SAPI !== 'cli') { exit(2); }
$root = dirname(__DIR__, 2);
$keys = ['providerCurrencyWarning', 'providerCurrencyUnavailable', 'providerCurrencyUnknown'];
$valid = static function ($value): bool {
    return is_string($value) && trim($value) !== '' && preg_match('//u', $value) === 1
        && preg_match('/\p{Arabic}/u', $value) === 1
        && !str_contains($value, '?') && !str_contains($value, "\u{FFFD}")
        && preg_match('/[\x{00C2}\x{00C3}\x{00D8}\x{00D9}]/u', $value) === 0;
};
$checks = 0;
$check = static function (bool $ok, string $label) use (&$checks): void {
    if (!$ok) { throw new RuntimeException($label); }
    $checks++;
};
foreach (['en', 'ar'] as $locale) {
    $path = $root . '/app/Language/' . $locale . '/Finance.php';
    $bytes = file_get_contents($path);
    $check(is_string($bytes) && preg_match('//u', $bytes) === 1, $locale . ' UTF-8 source');
    if ($locale === 'ar') { $check(!str_starts_with($bytes, "\xEF\xBB\xBF"), 'Arabic no UTF-8 BOM'); }
    ob_start();
    try { $language[$locale] = require $path; } finally { ob_end_clean(); }
}
foreach ($keys as $key) {
    $check(isset($language['en'][$key]) && trim($language['en'][$key]) !== '', $key . ' English reference exists');
    $check($valid($language['ar'][$key] ?? null), $key . ' intact Arabic');
    $check(!$valid(str_repeat('?', 20)), $key . ' question-mark negative control');
}
foreach ([null, '', "\u{FFFD}", "\u{00D8}\u{00D9}", "\xFF"] as $bad) {
    $check(!$valid($bad), 'Missing/invalid/mojibake negative control');
}
echo json_encode(['status' => 'PASS', 'checks' => $checks, 'keys' => $keys,
    'database_bootstrapped' => false], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
