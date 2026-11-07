<?php
// CLI-only synthetic presenter for Whole-App Design Proof.
// Renders the REAL Studio shell (layouts/studio) and REAL canonical patient intake workflow (patient/create).
// Uses canonical Studio component classes (.button.primary, .button.secondary, .icon-button, .form-notice--error).
// Pure synthetic data; no database, credentials, or production writes.
if (PHP_SAPI !== 'cli') exit(2);

define('APPPATH', dirname(__DIR__, 3) . '/app/');
$locale = ($argv[1] ?? 'en') === 'ar' ? 'ar' : 'en';
$direction = $argv[2] ?? 'direction-3'; // direction-1 (Baseline), direction-2 (Card Surfaces), direction-3 (Operator Clarity)
$state = $argv[3] ?? 'default'; // default, validation-error, intake-dialog

$translations = [];
foreach (['Studio', 'UI', 'PatientHistory', 'Finance', 'Inventory', 'ActivityLog'] as $group) {
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
    if ($GLOBALS['state'] === 'validation-error') {
        if ($key === 'first_name') return '';
        if ($key === 'last_name') return 'Doe';
        if ($key === 'phone') return '123'; // invalid short phone
        if ($key === 'gender') return 'male';
    }
    return $fallback;
}

function has_permission($module, $action) {
    return true;
}

function settings() {
    return new class {
        public function get($key, $default = null) {
            return ['currency' => 'ILS', 'timezone' => 'Asia/Hebron', 'date_format' => 'Y-m-d'][$key] ?? $default;
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
    return 'synthetic-proof';
}

function helper($name) {}

function config($name) {
    return (object) ['enabled' => false, 'CSPEnabled' => false];
}

function service($name) {
    if ($name === 'uri') {
        return new class {
            public function getSegments() { return ['patient', 'create']; }
            public function getPath() { return 'patient/create'; }
        };
    }
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

$mockSessionData = [
    'user_id' => 1,
    'email' => 'operator@denta.local',
    'active_clinic_id' => 1,
    'active_clinic_role_id' => 1,
    'global_mode' => false,
    'is_impersonating' => false,
    'error' => $state === 'validation-error' ? ($locale === 'ar' ? 'يرجى مراجعة وتصحيح الحقول المحددة أدناه قبل المتابعة.' : 'Please correct the highlighted fields before saving.') : null,
    'success' => null,
    'errors' => $state === 'validation-error' ? [
        'first_name' => $locale === 'ar' ? 'الاسم الأول مطلوب ولا يمكن تركه فارغاً.' : 'First name is required and cannot be empty.',
        'phone' => $locale === 'ar' ? 'رقم الهاتف المحمول يجب أن يكون رقماً صحيحاً من 9 إلى 15 خانة.' : 'Mobile phone number must be a valid number between 9 and 15 digits.'
    ] : []
];

function session($key = null) {
    global $mockSessionData;
    if ($key !== null) {
        return $mockSessionData[$key] ?? null;
    }
    return new class($mockSessionData) {
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

class ProofFixtureView {
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
    return (new ProofFixtureView())->render($path, $data);
}

// Scoped proof styles matching evaluated directions, deriving purely from canonical tokens and classes.
// Strictly scoped to [data-proof-direction] on the form container; shell toolbar, navigation, and other controls remain completely unaffected.
$directionStyles = '';
if ($direction === 'direction-2') {
    // Direction 2: Refined Surface & Elevation (Cards on canvas, canonical font sizes and controls)
    $directionStyles = <<<CSS
<style id="proof-direction-styles">
/* Direction 2: Refined Card Surface & Semantic Contrast (Scoped to Proof Form) */
form[data-proof-direction="direction-2"] .registration-basics {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
form[data-proof-direction="direction-2"] .registration-basics > header {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 20px;
}
form[data-proof-direction="direction-2"] .form-actions {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px 24px;
    margin-top: 16px;
}
</style>
CSS;
} elseif ($direction === 'direction-3') {
    // Direction 3: Operator High-Clarity (Recommended for 65yo Non-technical Operator)
    // Prominent 48px touch targets, readable 16px labels (NO shrinking!), high-contrast focus rings, crisp white card grouping.
    // Scoped strictly to form[data-proof-direction="direction-3"] so shell and other controls remain completely unaffected.
    $directionStyles = <<<CSS
<style id="proof-direction-styles">
/* Direction 3: Operator High-Clarity (Accessibility & Cognitive Ease) */
/* Scoped exclusively to the form consumer; shell buttons/toolbar remain untouched */
form[data-proof-direction="direction-3"] .registration-basics {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 28px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}
form[data-proof-direction="direction-3"] .registration-basics > header {
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
form[data-proof-direction="direction-3"] .registration-basics > header h2 {
    font-size: 20px;
    font-weight: 700;
    color: #0c251f;
    margin: 0;
}
form[data-proof-direction="direction-3"] .form-field label {
    font-size: 16px;
    font-weight: 600;
    color: #0c251f;
    margin-bottom: 8px;
    display: block;
}
form[data-proof-direction="direction-3"] .form-field input,
form[data-proof-direction="direction-3"] .form-field select,
form[data-proof-direction="direction-3"] .form-field textarea {
    min-height: 48px;
    font-size: 16px;
    color: #0c251f;
    border: 1.5px solid #94a3b8;
    border-radius: 6px;
    padding: 10px 14px;
    background: #ffffff;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
form[data-proof-direction="direction-3"] .form-field input:focus,
form[data-proof-direction="direction-3"] .form-field select:focus,
form[data-proof-direction="direction-3"] .form-field textarea:focus {
    outline: none;
    border-color: #0d9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.25);
}
form[data-proof-direction="direction-3"] .form-field input[aria-invalid="true"],
form[data-proof-direction="direction-3"] .form-field select[aria-invalid="true"] {
    border-color: #ef4444;
    background-color: #fffafa;
}
form[data-proof-direction="direction-3"] .form-field .field-error {
    font-size: 14px;
    font-weight: 600;
    color: #b91c1c;
    margin-top: 6px;
}
/* Form action buttons scoped specifically to proof form actions */
form[data-proof-direction="direction-3"] .form-actions .button.primary {
    min-height: 48px;
    padding: 0 24px;
    font-size: 16px;
    font-weight: 700;
    background-color: #102e27;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
form[data-proof-direction="direction-3"] .form-actions .button.primary:hover {
    background-color: #153f36;
}
form[data-proof-direction="direction-3"] .form-actions .button.primary:focus-visible {
    outline: 2px solid #0d9488;
    outline-offset: 2px;
}
form[data-proof-direction="direction-3"] .form-actions .button.secondary,
form[data-proof-direction="direction-3"] .registration-basics .button.secondary {
    min-height: 48px;
    padding: 0 20px;
    font-size: 16px;
    font-weight: 600;
    background-color: #e9f1ed;
    color: #102e27;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}
form[data-proof-direction="direction-3"] .form-actions .button.secondary:hover,
form[data-proof-direction="direction-3"] .registration-basics .button.secondary:hover {
    background-color: #dce9e2;
    border-color: #94a3b8;
}
form[data-proof-direction="direction-3"] .form-actions .button.secondary:focus-visible,
form[data-proof-direction="direction-3"] .registration-basics .button.secondary:focus-visible {
    outline: 2px solid #0d9488;
    outline-offset: 2px;
}
form[data-proof-direction="direction-3"] .form-actions {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 20px 28px;
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
form[data-proof-direction="direction-3"] .form-actions > div {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
/* Responsive bounding & text wrapping at 320px */
@media (max-width: 480px) {
    form[data-proof-direction="direction-3"] .registration-basics {
        padding: 16px;
    }
    form[data-proof-direction="direction-3"] .form-actions {
        padding: 16px;
        flex-direction: column-reverse;
        align-items: stretch;
    }
    form[data-proof-direction="direction-3"] .form-actions > div {
        flex-direction: column;
    }
    form[data-proof-direction="direction-3"] .form-actions .button {
        width: 100%;
        text-align: center;
    }
}
</style>
CSS;
}

if ($state === 'intake-dialog') {
    // Render the modal intake dialog variant using real shared markup
    $content = view('patient/partials/studio_intake_dialog', [
        'studioCanCreatePatient' => true,
        'intakeReturn' => 'patients',
        'studioIcon' => static fn($n) => '<svg class="si" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>'
    ]);
    echo "<!doctype html><html lang=\"{$locale}\" dir=\"" . ($locale === 'ar' ? 'rtl' : 'ltr') . "\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>Quick Intake Dialog Proof</title>";
    foreach (['studio', 'studio-theme', 'studio-workspace', 'studio-forms', 'studio-controls'] as $css) {
        echo "<link rel=\"stylesheet\" href=\"/assets/css/{$css}.css\">";
    }
    echo "{$directionStyles}</head><body class=\"studio\"><main style=\"padding:24px;\"><button type=\"button\" class=\"button primary\" data-patient-create-open>Open Intake Dialog</button>{$content}</main><script src=\"/assets/js/studio-forms.js\"></script><script src=\"/assets/js/studio-create-dialog.js\"></script></body></html>";
    exit(0);
}

// Render canonical patient/create extending layouts/studio
$html = view('patient/create', [
    'locale' => $locale,
    'title' => lang('UI.patientCreateTitle'),
    'clinic' => ['name' => 'Synthetic Clinic A', 'id' => 1],
    'user' => (object) ['first_name' => 'Mary', 'last_name' => 'Operator', 'email' => 'reception@denta.local'],
    'validation' => null,
    'studioFormErrors' => $mockSessionData['errors'],
    'is_rtl' => $locale === 'ar',
    'return_to' => 'patient'
]);

// Scope direction styles strictly to the form element
$html = str_replace('<form ', '<form data-proof-direction="' . esc($direction) . '" ', $html);

// Inject direction styles before </head>
if ($directionStyles) {
    $html = str_replace('</head>', $directionStyles . "\n</head>", $html);
}

echo $html;
