<?php
// Real presenter with a mocked permission helper, no framework/database bootstrap.
if (PHP_SAPI !== 'cli') { exit(2); }
$locale = ($argv[1] ?? 'en') === 'ar' ? 'ar' : 'en';
$translations = [];
foreach (['Finance', 'Studio', 'Provider'] as $group) {
    $translations[$group] = require dirname(__DIR__, 3) . '/app/Language/' . $locale . '/' . $group . '.php';
}
function lang($key, $args = []) { [$group, $name] = explode('.', $key, 2); return $GLOBALS['translations'][$group][$name] ?? $key; }
function base_url($path) { return 'http://synthetic.invalid/' . $path; }
function has_permission($module, $action) { return false; }
if (!function_exists('formatCurrency')) { function formatCurrency($amount) { return number_format((float) $amount, 2) . ' ILS'; } }
// Caller-supplied data must never override the actual permission helper.
$permissions = ['view'=>true, 'edit'=>true, 'delete'=>true, 'export'=>true, 'supplier'=>true];
$moneyMode = $argv[2] ?? 'payouts';
if ($moneyMode === 'payables') {
    $rows = [['id' => 1, 'expense_category' => 'salaries', 'total_amount' => 1000.0, 'balance_due' => 1000.0]];
}
echo '<!DOCTYPE html><meta charset="utf-8">';
require dirname(__DIR__, 3) . '/app/Views/components/studio_money_config.php';
