<?php
namespace App\Services;

/** Exact no-write equivalence for protected void allocations. No rounding or FX conversion. */
final class ProviderAllocationRoundtripPolicy
{
    public static function assertEquivalent(array $old, array $new, mixed $rawSplitValue = null): void
    {
        $reject = static function (): never { throw new \DomainException(lang('Finance.voidProviderAllocationProtected')); };
        foreach (['finance_id', 'patient_id', 'provider_user_id'] as $field) {
            $nullable = $field === 'provider_user_id';
            $a = self::identity($old[$field] ?? null, $nullable);
            $b = self::identity($new[$field] ?? null, $nullable);
            if ($a === null || $b === null || $a !== $b) $reject();
        }
        foreach (['provider_type', 'split_mode', 'currency'] as $field) {
            if (!is_string($old[$field] ?? null) || $old[$field] !== ($new[$field] ?? null)) $reject();
        }
        if (!in_array($old['provider_type'], ['user', 'guest'], true)
            || !in_array($old['split_mode'], ['percent', 'fixed'], true)
            || !preg_match('/^[A-Z]{3}$/D', $old['currency'])) $reject();
        // These fields are trimmed by the existing form normalizer. Comparing that
        // domain representation does not authorize rewriting their stored bytes.
        foreach (['provider_name_snapshot', 'notes'] as $field) {
            // A user provider's authoritative identity is its stable user ID; the
            // normalizer intentionally supplies no mutable display-name snapshot.
            if ($field === 'provider_name_snapshot' && $old['provider_type'] === 'user'
                && ($new[$field] ?? null) === null) continue;
            if (!is_scalar($old[$field] ?? '') || !is_scalar($new[$field] ?? '')
                || trim((string)($old[$field] ?? '')) !== trim((string)($new[$field] ?? ''))) $reject();
        }
        if ($old['provider_type'] === 'guest' && trim((string)($old['provider_name_snapshot'] ?? '')) === '') $reject();
        if (self::decimal($new['provider_paid_amount'] ?? 0) !== '0.00') $reject();
        foreach (['split_value', 'gross_amount', 'provider_share_amount', 'clinic_share_amount'] as $field) {
            $a = self::decimal($old[$field] ?? null);
            $b = self::decimal($new[$field] ?? null);
            if ($a === null || $b === null || $a !== $b) $reject();
        }
        // Inspect the original token before float conversion can erase unsupported
        // precision (e.g. 30.0000000000000000001 becoming the float 30).
        if ($rawSplitValue !== null && self::decimal($rawSplitValue) !== self::decimal($old['split_value'] ?? null)) $reject();
    }

    private static function identity(mixed $value, bool $nullable): ?string
    {
        if ($nullable && ($value === null || $value === '')) return '0';
        if (!is_int($value) && !is_string($value)) return null;
        if (!preg_match('/^[0-9]+$/D', (string)$value)) return null;
        $id = ltrim((string)$value, '0') ?: '0';
        return !$nullable && $id === '0' ? null : $id;
    }

    /** Canonical exact cents; trailing zeros/exponents are representations, not rounding. */
    private static function decimal(mixed $value): ?string
    {
        if (is_float($value)) {
            if (!is_finite($value)) return null;
            $value = json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
        } elseif (is_int($value)) {
            $value = (string)$value;
        }
        if (!is_string($value) || strlen($value) > 100) return null;
        $value = trim($value);
        if (!preg_match('/^([+-]?)([0-9]*)(?:\.([0-9]*))?(?:[eE]([+-]?[0-9]+))?$/D', $value, $m)
            || (($m[2] ?? '') === '' && ($m[3] ?? '') === '')) return null;
        $fraction = $m[3] ?? '';
        $exponent = $m[4] ?? '0';
        if (strlen(ltrim($exponent, '+-0')) > 2 || abs((int)$exponent) > 30) return null;
        $digits = ltrim(($m[2] ?? '').$fraction, '0') ?: '0';
        if ($digits === '0') return '0.00';
        if (($m[1] ?? '') === '-') return null;
        $shift = (int)$exponent - strlen($fraction);
        if ($shift >= 0) {
            $integer = $digits.str_repeat('0', $shift);
            $fraction = '';
        } else {
            $digits = str_pad($digits, 1-$shift, '0', STR_PAD_LEFT);
            $integer = substr($digits, 0, strlen($digits)+$shift);
            $fraction = rtrim(substr($digits, $shift), '0');
        }
        if (strlen($fraction) > 2 || strlen($integer) > 30) return null;
        return (ltrim($integer, '0') ?: '0').'.'.str_pad($fraction, 2, '0');
    }
}
