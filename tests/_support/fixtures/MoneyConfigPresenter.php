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
// Caller-supplied data must never override the actual permission helper.
$permissions = ['view'=>true, 'edit'=>true, 'delete'=>true, 'export'=>true, 'supplier'=>true];
$moneyMode = 'payouts';
require dirname(__DIR__, 3) . '/app/Views/components/studio_money_config.php';
