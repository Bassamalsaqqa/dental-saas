<?php
declare(strict_types=1);

// Standalone CLI test: pure unit test for ProviderAllocationRoundtripPolicy (no DB/framework bootstrap).
require_once __DIR__ . '/../../app/Services/ProviderAllocationRoundtripPolicy.php';
use App\Services\ProviderAllocationRoundtripPolicy as Policy;

if (!function_exists('lang')) {
    function lang(string $key): string { return $key; }
}

function assertUnchanged(array $expected, array $actual, string $context): void {
    if (count($expected) !== count($actual)) {
        throw new \RuntimeException("{$context}: array length mutated");
    }
    foreach ($expected as $k => $v) {
        if (!array_key_exists($k, $actual)) {
            throw new \RuntimeException("{$context}: key {$k} missing");
        }
        $av = $actual[$k];
        if (is_float($v) && is_nan($v)) {
            if (!is_float($av) || !is_nan($av)) throw new \RuntimeException("{$context}: NAN changed");
            continue;
        }
        if ($v !== $av) throw new \RuntimeException("{$context}: key {$k} mutated ({$v} !== {$av})");
    }
}

$base = [
    'finance_id' => 10, 'patient_id' => 20, 'provider_user_id' => 30,
    'provider_type' => 'user', 'provider_name_snapshot' => 'Dr. Alice',
    'split_mode' => 'percent', 'split_value' => '30.00', 'gross_amount' => '100.00',
    'provider_share_amount' => '30.00', 'clinic_share_amount' => '70.00',
    'currency' => 'USD', 'notes' => 'Baseline note', 'provider_paid_amount' => '0.00',
];
$baseGuest = array_merge($base, [
    'provider_user_id' => null, 'provider_type' => 'guest',
    'provider_name_snapshot' => 'Dr. Guest', 'notes' => 'Guest note',
]);

$cases = [
    // 1. Numeric text equivalents (30, 30.0, 30.00, 30.000, +3e1)
    ['equiv split_value 30', $base, ['split_value' => '30'], null, true],
    ['equiv split_value 30.0', $base, ['split_value' => '30.0'], null, true],
    ['equiv split_value 30.00', $base, ['split_value' => '30.00'], null, true],
    ['equiv split_value 30.000', $base, ['split_value' => '30.000'], null, true],
    ['equiv split_value +3e1', $base, ['split_value' => '+3e1'], null, true],
    ['equiv raw 30', $base, [], '30', true],
    ['equiv raw 30.0', $base, [], '30.0', true],
    ['equiv raw 30.00', $base, [], '30.00', true],
    ['equiv raw 30.000', $base, [], '30.000', true],
    ['equiv raw +3e1', $base, [], '+3e1', true],

    // 2. Unsupported raw / extra precision reject BEFORE float cast
    ['reject raw 30.0000000000000000001', $base, [], '30.0000000000000000001', false],
    ['reject split_value 30.00001', $base, ['split_value' => '30.00001'], null, false],
    ['reject raw 30.00001', $base, [], '30.00001', false],
    ['reject scientific 3.0000000000001e1', $base, ['split_value' => '3.0000000000001e1'], null, false],
    ['reject raw scientific 3.0000000000001e1', $base, [], '3.0000000000001e1', false],

    // 3. Null / empty text & boundary whitespace equivalence
    ['equiv null to empty notes', array_merge($base, ['notes' => null]), ['notes' => ''], null, true],
    ['equiv empty to null notes', array_merge($base, ['notes' => '']), ['notes' => null], null, true],
    ['equiv null to empty provider_user_id', array_merge($baseGuest, ['provider_user_id' => null]), ['provider_user_id' => ''], null, true],
    ['equiv notes boundary whitespace', $base, ['notes' => "  Baseline note \t "], null, true],
    ['equiv guest name boundary whitespace', $baseGuest, ['provider_name_snapshot' => "  Dr. Guest  "], null, true],

    // 4. Interior whitespace & name case change reject
    ['reject interior whitespace in notes', $base, ['notes' => 'Baseline  note'], null, false],
    ['reject interior whitespace in guest name', $baseGuest, ['provider_name_snapshot' => 'Dr.  Guest'], null, false],
    ['reject case change in guest name', $baseGuest, ['provider_name_snapshot' => 'dr. guest'], null, false],
    ['reject case change in user name', $base, ['provider_name_snapshot' => 'dr. alice'], null, false],

    // 5. Test ALL field changes reject
    ['reject finance_id change', $base, ['finance_id' => 99], null, false],
    ['reject patient_id change', $base, ['patient_id' => 99], null, false],
    ['reject provider_user_id change', $base, ['provider_user_id' => 99], null, false],
    ['reject provider_type change', $base, ['provider_type' => 'guest'], null, false],
    ['reject name change', $base, ['provider_name_snapshot' => 'Dr. Bob'], null, false],
    ['reject split_mode change', $base, ['split_mode' => 'fixed'], null, false],
    ['reject split_value change', $base, ['split_value' => '35.00'], null, false],
    ['reject gross_amount change', $base, ['gross_amount' => '105.00'], null, false],
    ['reject provider_share_amount change', $base, ['provider_share_amount' => '35.00'], null, false],
    ['reject clinic_share_amount change', $base, ['clinic_share_amount' => '65.00'], null, false],
    ['reject currency change', $base, ['currency' => 'EUR'], null, false],
    ['reject notes change', $base, ['notes' => 'Changed note'], null, false],
    ['reject new inline payment 10.00', $base, ['provider_paid_amount' => '10.00'], null, false],
    ['reject new inline payment 0.01', $base, ['provider_paid_amount' => '0.01'], null, false],

    // 6. Stable user ID with historical display name proposed null is equivalent
    ['equiv stable user id with proposed null snapshot', $base, ['provider_name_snapshot' => null], null, true],
    ['reject actual user id change even if snapshot null', $base, ['provider_user_id' => 99, 'provider_name_snapshot' => null], null, false],

    // 7. Numeric fields NaN / INF / non-numeric / missing / negative / epsilon reject
    ['reject NaN in split_value', $base, ['split_value' => NAN], null, false],
    ['reject INF in gross_amount', $base, ['gross_amount' => INF], null, false],
    ['reject non-numeric share', $base, ['provider_share_amount' => 'non_numeric'], null, false],
    ['reject missing clinic share', $base, ['__unset' => 'clinic_share_amount'], null, false],
    ['reject negative amount', $base, ['split_value' => '-30.00'], null, false],
    ['reject epsilon difference 30.01', $base, ['split_value' => '30.01'], null, false],
    ['reject epsilon difference 30.001', $base, ['split_value' => '30.001'], null, false],
];

$passed = 0;
foreach ($cases as [$name, $old, $patch, $raw, $expectPass]) {
    $new = $old;
    if (isset($patch['__unset'])) {
        unset($new[$patch['__unset']]);
        unset($patch['__unset']);
    }
    $new = array_merge($new, $patch);
    $oldBefore = $old;
    $newBefore = $new;
    $threw = false;
    try {
        Policy::assertEquivalent($old, $new, $raw);
    } catch (\DomainException) {
        $threw = true;
    }

    if ($expectPass && $threw) {
        throw new \RuntimeException("Case '{$name}' failed: expected pass, threw DomainException");
    }
    if (!$expectPass && !$threw) {
        throw new \RuntimeException("Case '{$name}' failed: expected DomainException, but passed");
    }
    assertUnchanged($oldBefore, $old, "Case '{$name}' [old]");
    assertUnchanged($newBefore, $new, "Case '{$name}' [new]");
    $passed++;
}

echo json_encode([
    'status' => 'PASS',
    'total_cases' => count($cases),
    'passed' => $passed,
    'input_immutability' => 'VERIFIED',
], JSON_PRETTY_PRINT), PHP_EOL;
