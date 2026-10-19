<?php
/** Run directly with PHP; no application bootstrap, database or network is used. */
declare(strict_types=1);

function uri_string(): string
{
    return $GLOBALS['fixtureUri'];
}

function view(string $name, array $data = []): string
{
    if ($name !== 'appointment/partials/quick_create_modal') {
        return '';
    }
    return '<div id="newAppointmentModal" dir="' . (!empty($data['isRtl']) ? 'rtl' : 'ltr') . '"></div>';
}

function session(): object
{
    return new class {
        public function getFlashdata(string $key): mixed { return null; }
    };
}

function service(string $name): object
{
    if ($name !== 'settings') {
        throw new RuntimeException('Unexpected service: ' . $name);
    }
    return new class {
        public function getBookingChairNames(): array { return ['Doctor 1']; }
    };
}

function old(string $key, mixed $default = null): mixed
{
    return $default;
}

$component = $argv[1] ?? dirname(__DIR__, 2) . '/app/Views/components/global_quick_create_modals.php';
$cases = [
    'appointment/calendar' => 0,
    'appointments/calendar' => 0,
    '/appointment/calendar/' => 0,
    'appointment' => 0,
    'appointments' => 0,
    'appointment/create' => 0,
    'appointments/create' => 0,
    'examination/42' => 0,
    'examinations/42' => 0,
    'dashboard' => 1,
    'patient/42' => 1,
    '' => 1,
];
$checked = 0;
foreach (['en' => false, 'ar' => true] as $locale => $isRtl) {
    foreach ($cases as $uri => $expected) {
        $GLOBALS['fixtureUri'] = $uri;
        $is_rtl = $isRtl;
        ob_start();
        include $component;
        $output = ob_get_clean();
        $actual = substr_count($output, 'id="newAppointmentModal"');
        if ($actual !== $expected) {
            throw new RuntimeException("{$locale} {$uri}: expected {$expected} global appointment modal, got {$actual}");
        }
        if ($expected && !str_contains($output, 'dir="' . ($isRtl ? 'rtl' : 'ltr') . '"')) {
            throw new RuntimeException("{$locale} {$uri}: modal direction was not preserved");
        }
        ++$checked;
    }
}
echo "PASS: {$checked} rendered modal-route cases, EN/AR; no database or network.\n";