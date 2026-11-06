<?php
// CLI-only synthetic presenter for patient workspace, examination, and treatment workflow views.
if (PHP_SAPI !== 'cli') exit(2);
define('APPPATH', dirname(__DIR__, 3) . '/app/');
$locale = ($argv[1] ?? 'en') === 'ar' ? 'ar' : 'en';
$pageType = $argv[2] ?? 'workspace';
$permissionMode = $argv[3] ?? 'all';
$moneyVariant = $argv[4] ?? 'missing';

$translations = [];
foreach (['Finance', 'Studio', 'UI', 'Examination', 'Treatment', 'PatientHistory'] as $group) {
    $translations[$group] = require APPPATH . 'Language/' . $locale . '/' . $group . '.php';
}

function helper($name) {}
function esc($value, $context = null) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function lang($key, $args = []) {
    [$group, $name] = explode('.', $key, 2);
    $value = $GLOBALS['translations'][$group][$name] ?? $key;
    foreach ($args as $k => $v) $value = str_replace('{' . $k . '}', (string) $v, $value);
    return $value;
}
function base_url($path = '') {
    return 'http://127.0.0.1:57526/' . ltrim($path, '/');
}
function workflow_context_url($path, $returnTo = null) {
    return base_url($path);
}
function workflow_cancel($fallback, $returnTo = null) {
    return base_url($fallback);
}
function workflow_back($fallback, $returnTo = null) {
    return base_url($fallback);
}
function asset_version($path) {
    return 'synthetic-review';
}
function has_permission($module, $action) {
    return match ($GLOBALS['permissionMode']) {
        'none' => false,
        'treatment-only' => $module === 'treatments' && $action === 'create',
        default => true,
    };
}
function csrf_field() {
    return '<input type="hidden" name="csrf_test_name" value="synthetic">';
}
function csrf_hash() {
    return 'synthetic';
}
function csrf_token() {
    return 'csrf_test_name';
}
function csrf_header() {
    return 'X-CSRF-TOKEN';
}
require_once APPPATH . 'Services/ProviderCurrencySummary.php';
function formatDate($date) {
    return (string) $date;
}
function formatDateTime($date) {
    return (string) $date;
}
function formatTime($time) {
    return (string) $time;
}
function formatCurrency($amount) {
    return 'ILS ' . number_format((float) $amount, 2);
}
function getAppointmentDuration($default = 30) {
    return 30;
}
function getCurrencySymbol($curr = 'ILS') {
    return '₪';
}
function old($key, $fallback = '') {
    return $fallback;
}
function session($key = null) {
    $sess = new class {
        public function getFlashdata($k = null) { return null; }
        public function get($k = null) { return $k === 'active_clinic_id' ? 1 : null; }
    };
    if ($key !== null) {
        return $sess->get($key);
    }
    return $sess;
}

class PatientWorkflowReviewView {
    public function extend($layout) { return ''; }
    private string $sectionName = '';
    private array $data = [];
    public function setVar($name, $value = null) {
        $this->data[$name] = $value;
        return $this;
    }
    public function section($name) {
        $this->sectionName = $name;
        ob_start();
        return '';
    }
    public function endSection() {
        $content = ob_get_clean();
        if ($this->sectionName === 'scripts') {
            $GLOBALS['reviewScripts'] = ($GLOBALS['reviewScripts'] ?? '') . $content;
            return '';
        }
        return $content;
    }
    public function getData() {
        return $this->data;
    }
    public function render($path, $data) {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        ob_start();
        include APPPATH . 'Views/' . $path . '.php';
        return ob_get_clean();
    }
}
function view($path, $data = []) {
    return (new PatientWorkflowReviewView())->render($path, $data);
}
function service($name) {
    return new class {
        public function getLocale() { return $GLOBALS['locale']; }
        public function getUri() {
            return new class {
                public function getPath() { return 'patient/101'; }
            };
        }
    };
}
function config($name) {
    return (object) ['enabled' => false, 'cookieName' => 'csrf_cookie_name', 'headerName' => 'X-CSRF-TOKEN', 'tokenName' => 'csrf_test_name'];
}
function settings() {
    return new class {
        public function get($key, $default = null) {
            return ['currency' => 'ILS', 'timezone' => 'Asia/Hebron', 'currency_position' => 'before'][$key] ?? $default;
        }
        public function getCurrencySymbol() {
            return '₪';
        }
    };
}

$patient = [
    'id' => 101,
    'first_name' => 'Sara',
    'last_name' => 'Ahmad',
    'gender' => 'female',
    'phone' => '0599123456',
    'patient_id' => 'PAT-101',
    'date_of_birth' => '1990-05-15',
    'status' => 'active'
];

$examination = [
    'id' => 201,
    'examination_id' => 'EXAM-201',
    'patient_id' => 101,
    'first_name' => 'Sara',
    'last_name' => 'Ahmad',
    'examination_type' => 'initial',
    'status' => 'completed',
    'examination_date' => '2026-11-06 10:00:00',
    'chief_complaint' => 'Routine dental checkup and mild sensitivity in upper molar',
    'dental_history' => 'Previous composite restorations',
    'clinical_findings' => 'Tooth #16 minor occlusal caries',
    'diagnosis' => 'Dental caries',
    'recommendations' => 'Composite restoration on #16',
    'examination_notes' => 'Patient advised on oral hygiene',
    'money_trail' => ['summary' => ['provider_currency_summary' => \App\Services\ProviderCurrencySummary::summarize([])]]
];

$treatment = [
    'id' => 301,
    'treatment_id' => 'TRT-301',
    'patient_id' => 101,
    'examination_id' => 201,
    'treatment_name' => 'Composite Restoration',
    'treatment_type' => 'filling',
    'tooth_number' => '16',
    'status' => 'active',
    'start_date' => '2026-11-06',
    'estimated_duration' => 1,
    'treatment_description' => 'Composite resin restoration for tooth #16 occlusal surface'
];

?>
<!doctype html>
<html lang="<?= $locale ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-name" content="csrf_test_name">
    <meta name="csrf-token" content="synthetic">
    <meta name="csrf-header" content="X-CSRF-TOKEN">
    <title>Patient Workflow Presenter</title>
    <link rel="stylesheet" href="/assets/css/studio.css">
    <link rel="stylesheet" href="/assets/css/studio-theme.css">
    <link rel="stylesheet" href="/assets/css/studio-workspace.css">
    <link rel="stylesheet" href="/assets/css/studio-forms.css">
    <link rel="stylesheet" href="/assets/css/studio-clinical-form.css">
    <link rel="stylesheet" href="/assets/css/studio-clinical-record.css">
    <link rel="stylesheet" href="/assets/css/app-drawer.css">
    <link rel="stylesheet" href="/assets/css/studio-patient-workspace.css">
</head>
<body class="studio">
<main class="patient-workspace" data-patient-return-base="patient/101" style="padding: 24px">
<?php if ($pageType === 'money_trail'):
    $summary = ['provider_remaining' => 123.45];
    if ($moneyVariant === 'empty') {
        $summary['provider_currency_summary'] = \App\Services\ProviderCurrencySummary::summarize([]);
    } elseif ($moneyVariant === 'mixed') {
        $summary['provider_currency_summary'] = \App\Services\ProviderCurrencySummary::summarize([
            ['finance_id' => 1, 'currency' => 'ILS', 'provider_balance_due' => 125, 'provider_share_amount' => 125, 'gross_amount' => 500],
            ['finance_id' => 2, 'currency' => 'USD', 'provider_balance_due' => 50, 'provider_share_amount' => 50, 'gross_amount' => 200],
        ]);
    } elseif ($moneyVariant === 'malformed') {
        $summary['provider_currency_summary'] = [];
    }
    echo view('components/studio_money_trail', ['money_trail' => ['summary' => $summary]]);
elseif ($pageType === 'workspace'): ?>
    <?= view('patient/partials/context_quick_drawers', [
        'patient' => $patient,
        'is_rtl' => $locale === 'ar',
        'attachments_enabled' => true,
        'patient_command_treatment_types' => ['filling' => 'Composite Filling', 'extraction' => 'Extraction'],
        'patient_command_clinic_providers' => [['id' => 1, 'first_name' => 'Dr. Sara', 'last_name' => 'Doctor']],
        'patient_command_inventory_items' => []
    ]) ?>
<?php elseif ($pageType === 'examination'): ?>
    <?= view('examination/show', [
        'examination' => $examination,
        'treatments' => [$treatment],
        'is_rtl' => $locale === 'ar',
        'money_trail' => ['summary' => ['provider_currency_summary' => \App\Services\ProviderCurrencySummary::summarize([])]],
        'v4Can' => static fn($m, $a) => has_permission($m, $a)
    ]) ?>
<?php elseif ($pageType === 'treatment_create'): ?>
    <div class="clinical-form-workspace">
        <form class="studio-form" id="treatment-form" method="post" action="/treatment/store">
            <?= view('treatment/partials/studio_form_fields', [
                'editing' => false,
                'patients' => [$patient],
                'treatment_types' => ['filling' => 'Composite Filling', 'root_canal' => 'Root Canal'],
                'clinic_providers' => [['id' => 1, 'first_name' => 'Dr. Sara', 'last_name' => 'Doctor']],
                'inventory_items' => []
            ]) ?>
        </form>
    </div>
<?php endif; ?>
</main>
<script src="/assets/js/app-drawer.js"></script>
<script src="/assets/js/studio-patient-workspace.js"></script>
<?= $GLOBALS['reviewScripts'] ?? '' ?>
</body>
</html>
