<?php
declare(strict_types=1);
/** CLI-only synthetic rendering fixture; never boots the app or connects to a database. */
if (PHP_SAPI !== 'cli') { exit(2); }
$options = getopt('', ['locale:', 'attack', 'baseline:']);
$locale = ($options['locale'] ?? 'en') === 'ar' ? 'ar' : 'en';
$translations = require __DIR__ . '/../../app/Language/' . $locale . '/Finance.php';
$attack = array_key_exists('attack', $options);
$payload = '</script><img src=x onerror="window.syntheticInjection=1"><script>window.syntheticInjection=2</script>';
$oldValues = [
    'provider_split_enabled' => '1',
    'provider_type' => ['user', 'guest'],
    'provider_payout_id' => ['7', '8'],
    'provider_user_id' => ['41', ''],
    'provider_name_snapshot' => ['', $attack ? $payload : 'Synthetic Guest'],
    'provider_split_mode' => ['percent', 'fixed'],
    'provider_split_value' => ['70.00', '12.50'],
    'provider_split_notes' => [$attack ? $payload : "A & B <ordinary text> 'quoted'", 'Second note'],
    'provider_mark_paid_now' => ['0', '1'],
    'provider_paid_amount' => ['', '5.25'],
    'provider_paid_method' => ['cash', 'bank_transfer'],
    'provider_paid_date' => ['2026-10-22', '2026-10-21'],
];
function old(string $key, $default = null) { return $GLOBALS['oldValues'][$key] ?? $default; }
function lang(string $key): string { return (string) ($GLOBALS['translations'][substr($key, strlen('Finance.'))] ?? $key); }
function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function getCurrencySymbol(): string { return "\u{20AA}"; }
$clinic_providers = [
    ['id' => 41, 'first_name' => $attack ? $payload : 'Synthetic', 'last_name' => 'Doctor'],
    ['id' => 42, 'first_name' => "\u{0637}\u{0628}\u{064a}\u{0628}", 'last_name' => 'Two'],
];
$is_rtl = $locale === 'ar'; $id_prefix = 'acceptance';
$view = $options['baseline'] ?? __DIR__ . '/../../app/Views/finance/partials/provider_split_section.php';
if (!is_file($view)) { throw new RuntimeException('View missing'); }
?>
<!doctype html><html lang="<?= esc($locale) ?>" dir="<?= $is_rtl ? 'rtl' : 'ltr' ?>"><head><meta charset="utf-8"><link rel="stylesheet" href="http://127.0.0.1:57112/assets/css/tailwind.css"></head><body>
<form><input name="total_amount" value="100.00"><?php require $view; ?></form>
</body></html>
