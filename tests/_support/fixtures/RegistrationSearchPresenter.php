<?php
// CLI-only, DB-free registration/search integration fixture. Never uses real patient records.
if (PHP_SAPI !== 'cli') exit(2);
define('APPPATH', dirname(__DIR__, 3).'/app/');
$locale = ($argv[1] ?? 'en') === 'ar' ? 'ar' : 'en';
$mode = $argv[2] ?? 'page';
$canView = ($argv[3] ?? 'allowed') === 'allowed';
$canCreate = ($argv[4] ?? 'allowed') === 'allowed';

$translations = [];
foreach (['Studio', 'UI', 'Inventory', 'Finance', 'Treatment', 'Examination'] as $group) {
    $file = APPPATH . 'Language/' . $locale . '/' . $group . '.php';
    if (is_file($file)) {
        $translations[$group] = require $file;
    }
}

function lang($key, $args = []) {
    [$group, $name] = explode('.', $key, 2);
    $text = $GLOBALS['translations'][$group][$name] ?? $key;
    foreach ($args as $k => $v) {
        $text = str_replace('{' . $k . '}', (string)$v, $text);
    }
    return $text;
}

function esc($value, $context = null) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function old($key, $fallback = '') {
    return $fallback;
}

function has_permission($module, $action) {
    if ($module === 'patients' && $action === 'view') {
        return $GLOBALS['canView'];
    }
    if ($module === 'patients' && $action === 'create') {
        return $GLOBALS['canCreate'];
    }
    return true;
}

function settings() {
    return new class {
        public function get($key, $default = null) {
            return ['currency' => 'ILS', 'timezone' => 'Asia/Hebron'][$key] ?? $default;
        }
        public function getCurrencySymbol() { return '₪'; }
    };
}

function base_url($path = '') {
    return 'http://127.0.0.1:57526/' . ltrim($path, '/');
}

function workflow_context_url($path, $return = null) {
    return base_url($path);
}

function workflow_cancel($fallback, $return = null) {
    return base_url($fallback);
}

function csrf_field() {
    return '<input type="hidden" name="csrf_test_name" value="synthetic">';
}

function csrf_token() {
    return 'csrf_test_name';
}

function csrf_hash() {
    return 'synthetic';
}

function csrf_header() {
    return 'X-CSRF-TOKEN';
}

function asset_version($path) {
    return 'synthetic';
}

function helper($name) {}

function config($name) {
    return (object) ['enabled' => false, 'CSPEnabled' => false];
}

function service($name) {
    return new class {
        public function getLocale() { return $GLOBALS['locale']; }
        public function getUri() {
            return new class {
                public function getPath() { return 'patient/create'; }
                public function getSegments() { return ['patient', 'create']; }
            };
        }
        public function getSegments() { return ['patient', 'create']; }
        public function getPath() { return 'patient/create'; }
    };
}

function current_url($returnObject = false) {
    return new class {
        public function __toString() { return 'http://127.0.0.1:57526/patient/create'; }
    };
}

function session($key = null) {
    $sessData = [
        'user_id' => 1,
        'email' => 'operator@denta.local',
        'active_clinic_id' => 1,
        'active_clinic_role_id' => 1,
        'global_mode' => false,
        'is_impersonating' => false,
        'error' => null,
        'success' => null,
        'errors' => []
    ];
    if ($key !== null) {
        return $sessData[$key] ?? null;
    }
    return new class($sessData) {
        private array $d;
        public function __construct(array $d) { $this->d = $d; }
        public function get($k = null) { return $k ? ($this->d[$k] ?? null) : $this->d; }
        public function getFlashdata($k = null) { return null; }
    };
}

function workflow_back($path, $return = null) {
    return base_url($path);
}

function formatCurrency($amount) {
    return 'ILS ' . number_format((float)$amount, 2);
}

function formatDate($date) {
    return (string)$date;
}

function formatDateTime($date) {
    return (string)$date;
}

$studioIcon = static fn($name) => '';

class RegistrationFixtureView {
    private array $data = [];
    private array $sections = [];
    private string $currentSection = '';
    private ?string $layout = null;

    public function getData(): array { return $this->data; }
    public function setVar($name, $value = null) { $this->data[$name] = $value; return $this; }
    public function extend($layout) { $this->layout = $layout; return ''; }
    public function section($name) { $this->currentSection = $name; ob_start(); return ''; }
    public function endSection() { $this->sections[$this->currentSection] = ob_get_clean(); return ''; }
    public function renderSection($name) { return $this->sections[$name] ?? ''; }

    public function render($path, $data = []) {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        ob_start();
        include APPPATH . 'Views/' . $path . '.php';
        $content = ob_get_clean();
        if ($this->layout) {
            $layout = $this->layout;
            $this->layout = null;
            ob_start();
            include APPPATH . 'Views/' . $layout . '.php';
            return ob_get_clean();
        }
        return $content;
    }
}

function view($path, $data = []) {
    return (new RegistrationFixtureView())->render($path, $data);
}

if ($mode === 'inventory') {
    echo view('inventory/show', [
        'locale' => $locale,
        'clinic' => ['name' => 'Synthetic Clinic A', 'id' => 1],
        'user' => (object) ['first_name' => 'Staff', 'last_name' => 'Operator'],
        'item' => ['id' => 901, 'item_name' => 'Synthetic stock item', 'quantity' => 4, 'min_quantity' => 2, 'unit_price' => 10, 'unit' => 'pieces', 'category' => 'consumables', 'status' => 'active'],
        'supplier_links' => [], 'movements' => [], 'batches' => [], 'suppliers' => [],
    ]);
    exit(0);
}

if ($mode === 'fullshell') {
    // Renders the real full patient/create view extending layouts/studio
    echo view('patient/create', [
        'locale' => $locale,
        'title' => lang('UI.patientCreateTitle'),
        'clinic' => ['name' => 'Synthetic Clinic A', 'id' => 1],
        'user' => (object) ['first_name' => 'Staff', 'last_name' => 'Operator', 'email' => 'operator@denta.local'],
        'validation' => null,
        'studioFormErrors' => [],
        'is_rtl' => $locale === 'ar'
    ]);
    exit(0);
}

if ($mode === 'fullshell-edit') {
    // Renders the real full patient/edit view extending layouts/studio
    echo view('patient/edit', [
        'locale' => $locale,
        'title' => 'Edit Patient',
        'clinic' => ['name' => 'Synthetic Clinic A', 'id' => 1],
        'user' => (object) ['first_name' => 'Staff', 'last_name' => 'Operator', 'email' => 'operator@denta.local'],
        'patient' => [
            'id' => 901,
            'first_name' => 'Existing',
            'last_name' => 'Patient',
            'phone' => '0599111222',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male'
        ],
        'validation' => null,
        'studioFormErrors' => [],
        'is_rtl' => $locale === 'ar'
    ]);
    exit(0);
}
?>
<!doctype html><html lang="<?= $locale ?>" dir="<?= $locale==='ar'?'rtl':'ltr' ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registration search fixture</title>
<?php foreach(['studio','studio-theme','studio-workspace','studio-forms','studio-item-detail'] as $css): ?><link rel="stylesheet" href="/assets/css/<?= $css ?>.css"><?php endforeach; ?></head>
<body class="studio"><div class="application" id="application"><aside class="navigation" id="studio-nav"><div class="nav-groups"></div></aside><div class="workspace" id="studio-workspace"><main id="main" class="canvas">
<?php if($mode==='inventory'): ?>
<?= view('inventory/show',['item'=>['id'=>901,'item_name'=>'Synthetic stock item','quantity'=>4,'min_quantity'=>2,'unit_price'=>10,'unit'=>'pieces','category'=>'consumables','status'=>'active'],'supplier_links'=>[],'movements'=>[],'batches'=>[],'suppliers'=>[]]) ?>
<?php elseif($mode==='dialog'): ?>
<a href="/patient/create" data-patient-create-open>Register synthetic patient</a>
<?= view('patient/partials/studio_intake_dialog',['studioCanCreatePatient'=>$canCreate,'intakeReturn'=>'patients','studioIcon'=>$studioIcon]) ?>
<?php else: ?>
<form method="post" action="/patient/store" class="studio-form" data-patient-create-form data-unsaved-form>
<?= csrf_field() ?>
<?= view('patient/partials/studio_create_fields',['studioEditing'=>$mode==='edit']) ?>
<footer class="form-actions"><a class="button secondary" href="<?= base_url('patients') ?>" data-form-cancel><?= esc(lang('UI.cancel')) ?></a><button type="submit">Save fixture</button></footer></form>
<dialog id="form-discard-dialog" class="help-dialog" aria-labelledby="discard-title"><header><div><h2 id="discard-title"><?= esc(lang('Studio.discardTitle')) ?></h2><p><?= esc(lang('Studio.discardHelp')) ?></p></div><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>"></button></header><div class="discard-actions"><button class="button secondary" type="button" data-discard-stay><?= esc(lang('Studio.keepEditing')) ?></button><button class="button primary" type="button" data-discard-confirm><?= esc(lang('Studio.discardChanges')) ?></button></div></dialog>
<?= view('patient/partials/create_form_script') ?>
<script src="<?= base_url('assets/js/studio-forms.js') ?>" defer></script>
<?php endif; ?>
</main></div></div>
<?php if($canView): ?>
<dialog id="search-dialog" class="command-dialog" aria-labelledby="search-title"><header><h2 id="search-title"><?= esc(lang('Studio.search')) ?></h2><button type="button" data-close><?= esc(lang('Studio.close')) ?></button></header>
<input id="patient-search" type="search" aria-label="<?= esc(lang('Studio.searchHint')) ?>" data-url="/api/search/patients" data-record-base="/patient/" data-empty="<?= esc(lang('Studio.searchEmpty')) ?>" data-error="<?= esc(lang('Studio.searchFail')) ?>">
<p data-search-status role="status" data-loading="<?= esc(lang('Studio.refreshingList')) ?>" data-ready="<?= esc(lang('Studio.listReady')) ?>" data-denied="<?= esc(lang('Studio.requestDenied')) ?>" data-previous="<?= esc(lang('Studio.previousResults')) ?>"></p><div id="search-results"></div></dialog>
<?php endif; ?>
<script src="/assets/js/studio-loading.js"></script><script src="/assets/js/studio.js"></script>
</body></html>
