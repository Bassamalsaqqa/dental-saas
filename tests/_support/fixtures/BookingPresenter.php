<?php
// CLI-only real booking presenter; explicit synthetic context, no framework/DB.
if (PHP_SAPI !== 'cli') exit(2);
define('APPPATH', dirname(__DIR__, 3) . '/app/');
$locale = ($argv[1] ?? 'en') === 'ar' ? 'ar' : 'en';
$scenario = $argv[2] ?? 'legacy';
$translations = [];
foreach (['UI','Studio','DoctorScheduling','DoctorScheduleSettings','Finance','UserRole'] as $group) {
    if (file_exists(APPPATH.'Language/'.$locale.'/'.$group.'.php')) {
        $translations[$group] = require APPPATH.'Language/'.$locale.'/'.$group.'.php';
    }
}
function lang($key, $args = []) { [$group,$name] = explode('.', $key, 2); $value = $GLOBALS['translations'][$group][$name] ?? $key; foreach($args as $k=>$v) $value=str_replace('{'.$k.'}',(string)$v,$value); return $value; }
function esc($value, $context = null) { return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function old($key, $default = null) { return $GLOBALS['scenario']==='reentry' && $key==='chair_name' ? 'Column B' : $default; }
function getAppointmentDuration() { return 30; }
function has_permission($module, $action) { return in_array($GLOBALS['scenario'],['settings','user-show'],true); }
function base_url($path) { return 'http://synthetic.invalid/'.$path; }
function csrf_field() { return '<input type="hidden" name="csrf_test" value="synthetic">'; }
function view($name, $data = []) { return ''; }
function helper($name) {}
function asset_version($file) { return 'synthetic'; }
function workflow_back($path, $return = null) { return base_url($path); }
function workflow_context_url($path, $return = null) { return base_url($path); }
function formatDate($date) { return (string)$date; }
function formatDateTime($date) { return (string)$date; }
function settings() { return new class { public function get($k, $d = null) { return $k === 'timezone' ? 'Asia/Jerusalem' : ($k === 'currency' ? 'ILS' : ($d ?? 'ILS')); } public function getCurrencySymbol() { return '₪'; } }; }
require_once APPPATH.'Services/ProviderCurrencySummary.php';

if ($scenario === 'user-show') {
    $renderer = function() use ($locale) {
        $viewed_user = (object)['id' => 15, 'first_name' => 'Ahmad', 'last_name' => 'Doctor', 'email' => 'ahmad@denta.local', 'phone' => '0599112233', 'active' => 1, 'membership_status' => 'active'];
        $viewed_user_roles = [['id' => 2, 'name' => 'Doctor']];
        $current_user = (object)['id' => 1];
        $membership_status = 'active';
        $is_self = false;
        $canViewFinance = true;
        $canEditFinance = true;
        $canEditUsers = true;
        $canDeleteUsers = false;
        $provider_finance_summary = ['provider_share_total' => 1000, 'provider_paid_total' => 600, 'provider_balance_total' => 400, 'payout_count' => 3];
        $provider_payout_rows = [];
        $provider_payment_rows = [];
        $departments = [];
        $return_to = 'users';
        $workflow_back = static fn($r) => base_url('users');
        $workflow_context_url = static fn($u) => base_url($u);
        $formatDate = static fn($d) => (string)$d;
        $formatDateTime = static fn($d) => (string)$d;
        $studioInitials = static fn($n) => 'AD';
        echo '<!doctype html><html lang="' . $locale . '" dir="' . ($locale === 'ar' ? 'rtl' : 'ltr') . '"><head><meta charset="utf-8"></head><body class="studio"><main>';
        require APPPATH . 'Views/users/show.php';
        echo '</main></body></html>';
    };
    $mockThis = new class {
        public function extend($l) { return ''; }
        public function section($s) { return ''; }
        public function endSection() { return ''; }
    };
    $bound = \Closure::bind($renderer, $mockThis);
    $bound();
    exit(0);
}

if ($scenario === 'hours') {
    $settings = [
        'working_hours_start' => '09:00',
        'working_hours_end' => '17:00',
        'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        'booking_chair_count' => 2,
        'booking_chair_names' => ['Doctor 1', 'Doctor 2'],
        'dynamic_doctor_scheduling_enabled' => '0',
        'appointment_duration' => 30
    ];
    $settingsEdit = true;
    $max_booking_chair_count = 3;
    $provider_settings = ['schema_ready' => false, 'members' => []];
    $is_rtl = $locale === 'ar';
    echo '<!doctype html><html lang="' . $locale . '" dir="' . ($locale === 'ar' ? 'rtl' : 'ltr') . '"><head><meta charset="utf-8"></head><body class="studio"><main>';
    require APPPATH.'Views/settings/partials/hours.php';
    echo '</main></body></html>';
    exit(0);
}
require APPPATH.'Libraries/ProviderDisplayLabel.php';
require_once APPPATH.'Services/ProviderCurrencySummary.php';
$editing = $scenario==='edit';
$appointment = $editing ? ['chair_name'=>'Historical column','duration'=>45] : [];
$booking_context = ['enabled'=>in_array($scenario,['dynamic','unavailable'],true),'schema_ready'=>$scenario!=='unavailable','providers'=>[['user_id'=>11,'name'=>'Synthetic Doctor']],'chair_options'=>['Column A','Column B']];
?><!doctype html><html lang="<?= $locale ?>" dir="<?= $locale==='ar'?'rtl':'ltr' ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><?php foreach(['studio','studio-theme','studio-scale','studio-forms','studio-care'] as $css): ?><link rel="stylesheet" href="/assets/css/<?= $css ?>.css"><?php endforeach; ?></head><body class="studio"><main style="padding:16px"><form class="studio-form booking-form"><?php require APPPATH.'Views/appointment/partials/studio_booking_fields.php'; ?></form></main></body></html>
