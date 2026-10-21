<?php

namespace App\Services;

/** Exact two-decimal accounting arithmetic; no rounding or exponent input. */
final class InvoiceCorrectionMoney
{
    public const MAX_CENTS = 999999999999;

    public static function cents(mixed $value): int
    {
        if (is_float($value)) {
            if (!is_finite($value) || abs($value) > self::MAX_CENTS / 100 || round($value, 2) !== $value) {
                throw new \DomainException('Money must be a finite two-decimal amount.');
            }
            $value = number_format($value, 2, '.', '');
        }
        if (!is_string($value) && !is_int($value)) {
            throw new \DomainException('Money must be a decimal amount.');
        }
        $text = (string) $value;
        if (!preg_match('/^(-?)([0-9]{1,10})(?:\.([0-9]{1,2})0*)?$/D', $text, $m)) {
            throw new \DomainException('Money must have at most two decimals.');
        }
        $minor = ((int) $m[2]) * 100 + (int) str_pad($m[3] ?? '', 2, '0');
        if ($minor > self::MAX_CENTS) {
            throw new \DomainException('Money exceeds the supported accounting range.');
        }
        return ($m[1] === '-' ? -1 : 1) * $minor;
    }

    public static function nonNegative(mixed $value): int
    {
        $cents = self::cents($value);
        if ($cents < 0) {
            throw new \DomainException('Money cannot be negative.');
        }
        return $cents;
    }

    public static function decimal(int $cents): string
    {
        if ($cents > self::MAX_CENTS || $cents < -self::MAX_CENTS) {
            throw new \DomainException('Money exceeds the supported accounting range.');
        }
        $abs = abs($cents);
        return ($cents < 0 ? '-' : '') . intdiv($abs, 100) . '.' . str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function sum(int ...$amounts): int
    {
        $sum = 0;
        foreach ($amounts as $amount) {
            if ($amount > self::MAX_CENTS || $amount < -self::MAX_CENTS
                || $sum > self::MAX_CENTS - max(0, $amount)
                || $sum < -self::MAX_CENTS - min(0, $amount)) {
                throw new \DomainException('Accounting sum exceeds the supported range.');
            }
            $sum += $amount;
        }
        return $sum;
    }
}
