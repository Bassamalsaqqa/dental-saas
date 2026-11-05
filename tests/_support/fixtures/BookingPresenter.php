<?php
// CLI-only real booking presenter; explicit synthetic context, no framework/DB.
if (PHP_SAPI !== 'cli') exit(2);
define('APPPATH', dirname(__DIR__, 3) . '/app/');
$locale = ($argv[1] ?? 'en') === 'ar' ? 'ar' : 'en';
$scenario = $argv[2] ?? 'legacy';
$translations = [];
foreach (['UI','Studio','DoctorScheduling'] as $group) $translations[$group] = require APPPATH.'Language/'.$locale.'/'.$group.'.php';
function lang($key, $args = []) { [$group,$name] = explode('.', $key, 2); $value = $GLOBALS['translations'][$group][$name] ?? $key; foreach($args as $k=>$v) $value=str_replace('{'.$k.'}',(string)$v,$value); return $value; }
function esc($value, $context = null) { return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function old($key, $default = null) { return $GLOBALS['scenario']==='reentry' && $key==='chair_name' ? 'Column B' : $default; }
function getAppointmentDuration() { return 30; }
function has_permission($module, $action) { return false; }
function base_url($path) { return 'http://synthetic.invalid/'.$path; }
require APPPATH.'Libraries/ProviderDisplayLabel.php';
$editing = $scenario==='edit';
$appointment = $editing ? ['chair_name'=>'Historical column','duration'=>45] : [];
$booking_context = ['enabled'=>in_array($scenario,['dynamic','unavailable'],true),'schema_ready'=>$scenario!=='unavailable','providers'=>[['user_id'=>11,'name'=>'Synthetic Doctor']],'chair_options'=>['Column A','Column B']];
?><!doctype html><html lang="<?= $locale ?>" dir="<?= $locale==='ar'?'rtl':'ltr' ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body><form><?php require APPPATH.'Views/appointment/partials/studio_booking_fields.php'; ?></form></body></html>
