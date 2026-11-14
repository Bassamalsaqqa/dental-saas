<?php
// CLI-only, DB-free dashboard Today table integration fixture.
if (PHP_SAPI !== 'cli') exit(2);
define('APPPATH', dirname(__DIR__, 3).'/app/');
$locale = ($argv[1] ?? 'en') === 'ar' ? 'ar' : 'en';
$canEdit = ($argv[2] ?? 'allowed') === 'allowed';
$scenario = $argv[3] ?? 'all_stages';

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

function has_permission($module, $action) {
    if ($module === 'appointments' && $action === 'edit') {
        return $GLOBALS['canEdit'];
    }
    return true;
}

function settings() {
    return new class {
        public function get($key, $default = null) {
            return ['currency' => 'ILS', 'timezone' => 'Asia/Hebron'][$key] ?? $default;
        }
        public function getCurrencySymbol() { return '₪'; }
        public function getClinicInfo() {
            return ['name' => 'Synthetic Clinic A', 'id' => 1];
        }
    };
}

function base_url($path = '') {
    return 'http://127.0.0.1:57526/' . ltrim($path, '/');
}

function workflow_context_url($path, $return = null) {
    return base_url($path . ($return ? '?return_to=' . $return : ''));
}

function uri_string() {
    return 'dashboard';
}

function current_url($returnObject = false) {
    return new class {
        public function __toString() { return 'http://127.0.0.1:57526/dashboard'; }
    };
}

function formatDate($date, $format = 'd M Y') {
    return $date ? date($format, strtotime($date)) : '-';
}

function formatTime($time, $format = 'H:i') {
    return $time ? date($format, strtotime($time)) : '-';
}

function csrf_token() {
    return 'csrf_test_name';
}

function csrf_hash() {
    return 'synthetic_csrf_hash_12345';
}

function csrf_field() {
    return '<input type="hidden" name="csrf_test_name" value="synthetic_csrf_hash_12345">';
}

function helper($name) {
    return true;
}

function session($key = null) {
    $data = [
        'user_id' => 1,
        'active_clinic_id' => 1,
        'global_mode' => 0,
        'is_impersonating' => 0,
    ];
    if ($key === null) {
        return new class($data) {
            public function __construct(private array $data) {}
            public function get($k = null) {
                return $k === null ? $this->data : ($this->data[$k] ?? null);
            }
        };
    }
    return $data[$key] ?? null;
}

function old($key = null, $default = null) {
    return $default;
}

function getAppointmentDuration($type = '') {
    return 30;
}

function asset_version($path) {
    return '1.0.0';
}

$studioIcon = static fn($name) => '<svg data-icon="' . esc($name) . '"></svg>';

$visits = [];
if ($scenario === 'all_stages') {
    $visits = [
        [
            'id' => 101,
            'patient_id' => 501,
            'first_name' => 'Scheduled',
            'last_name' => 'Patient',
            'status' => 'scheduled',
            'appointment_time' => '09:00:00',
            'appointment_date' => date('Y-m-d'),
            'duration' => 30,
            'chair_name' => 'Chair 1',
            'appointment_type' => 'consultation',
        ],
        [
            'id' => 102,
            'patient_id' => 502,
            'first_name' => 'Confirmed',
            'last_name' => 'Patient',
            'status' => 'confirmed',
            'appointment_time' => '10:00:00',
            'appointment_date' => date('Y-m-d'),
            'duration' => 45,
            'chair_name' => 'Chair 2',
            'appointment_type' => 'treatment',
        ],
        [
            'id' => 103,
            'patient_id' => 503,
            'first_name' => 'Arrived',
            'last_name' => 'Patient',
            'status' => 'arrived',
            'appointment_time' => '11:00:00',
            'appointment_date' => date('Y-m-d'),
            'duration' => 30,
            'chair_name' => 'Chair 1',
            'appointment_type' => 'cleaning',
        ],
        [
            'id' => 104,
            'patient_id' => 504,
            'first_name' => 'InChair',
            'last_name' => 'Patient',
            'status' => 'in_chair',
            'appointment_time' => '12:00:00',
            'appointment_date' => date('Y-m-d'),
            'duration' => 60,
            'chair_name' => 'Chair 1',
            'appointment_type' => 'treatment',
        ],
        [
            'id' => 105,
            'patient_id' => 505,
            'first_name' => 'Completed',
            'last_name' => 'Patient',
            'status' => 'completed',
            'appointment_time' => '13:00:00',
            'appointment_date' => date('Y-m-d'),
            'duration' => 30,
            'chair_name' => 'Chair 2',
            'appointment_type' => 'checkup',
        ],
    ];
} elseif ($scenario === 'empty') {
    $visits = [];
}

class DashboardFixtureView {
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
        $mainContent = $this->renderSection('content');
        return ($mainContent !== '' ? $mainContent : $content) . "\n" . ($this->renderSection('scripts'));
    }
}

function view($path, $data = []) {
    return (new DashboardFixtureView())->render($path, $data);
}

$viewData = [
    'locale' => $locale,
    'today_routing' => [
        'groups' => [
            $visits,
        ],
        'completed_count' => 1,
    ],
    'upcoming_appointments' => [],
    'can_route_appointments' => true,
    'stats' => [
        'today_appointments' => count($visits),
    ],
    'clinical_documentation_queue' => [],
    'appointment_recovery_queue' => [],
    'accepted_treatment_queue' => [],
    'is_rtl' => $locale === 'ar',
    'studioIcon' => $studioIcon,
    'v4Can' => static function (string $module, string $action) use ($canEdit): bool {
        if ($module === 'appointments' && $action === 'edit') {
            return $canEdit;
        }
        return true;
    },
    'v4PermissionContext' => '1:1:0:0',
];

echo (new DashboardFixtureView())->render('dashboard/index', $viewData);
