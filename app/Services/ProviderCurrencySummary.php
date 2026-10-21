<?php
namespace App\Services;

/** Native provider amounts have no recorded FX conversion. Never combine unlike units. */
final class ProviderCurrencySummary
{
    public static function currency($value): string
    {
        $value = strtoupper(trim((string) $value));
        return preg_match('/^[A-Z]{3}$/D', $value) ? $value : 'UNKNOWN';
    }

    public static function summarize(array $rows, array $cashRows = []): array
    {
        $keys = ['total_provider_share', 'total_provider_paid', 'total_provider_paid_cash',
            'total_provider_balance', 'total_clinic_share', 'total_gross_amount'];
        $buckets = [];
        $invoices = [];
        $ensure = static function (string $currency) use (&$buckets, $keys): void {
            if (!isset($buckets[$currency])) { $buckets[$currency] = array_fill_keys($keys, 0.0); }
        };
        foreach ($rows as $row) {
            $currency = self::currency($row['currency'] ?? null);
            $ensure($currency);
            $buckets[$currency]['total_provider_share'] += (float) ($row['provider_share_amount'] ?? 0);
            $buckets[$currency]['total_provider_paid'] += (float) ($row['provider_paid_amount'] ?? 0);
            if (($row['status'] ?? '') !== 'void') {
                $buckets[$currency]['total_provider_balance'] += (float) ($row['provider_balance_due'] ?? 0);
            }
            $invoice = (string) ($row['finance_id'] ?? ('row:' . ($row['id'] ?? 'unknown')));
            $invoices[$invoice][$currency][] = $row;
        }
        $inconsistent = [];
        foreach ($invoices as $id => $currencies) {
            if (count($currencies) !== 1) {
                $inconsistent[] = $id;
                foreach ($currencies as $currency => $_) {
                    $buckets[$currency]['total_gross_amount'] = null;
                    $buckets[$currency]['total_clinic_share'] = null;
                }
                continue;
            }
            foreach ($currencies as $currency => $allocationRows) {
                $grosses = array_map(static fn(array $r): float => (float) ($r['gross_amount'] ?? 0), $allocationRows);
                if (count(array_unique($grosses)) !== 1 || array_filter($allocationRows, static fn(array $r): bool => (int) ($r['gross_variant_count'] ?? 1) > 1)) {
                    $inconsistent[] = $id;
                    $buckets[$currency]['total_gross_amount'] = null;
                    $buckets[$currency]['total_clinic_share'] = null;
                    continue;
                }
                $gross = $grosses[0];
                $share = array_sum(array_column($allocationRows, 'provider_share_amount'));
                if ($buckets[$currency]['total_gross_amount'] !== null) { $buckets[$currency]['total_gross_amount'] += $gross; }
                if ($buckets[$currency]['total_clinic_share'] !== null) { $buckets[$currency]['total_clinic_share'] += $gross - $share; }
            }
        }
        foreach ($cashRows as $row) {
            $currency = self::currency($row['currency'] ?? null);
            $ensure($currency);
            $buckets[$currency]['total_provider_paid_cash'] += (float) ($row['amount'] ?? 0);
        }
        $aliases = ['gross_amount' => 'total_gross_amount', 'clinic_share_amount' => 'total_clinic_share', 'gross_amount_total' => 'total_gross_amount', 'provider_share_total' => 'total_provider_share',
            'provider_paid_total' => 'total_provider_paid', 'provider_balance_total' => 'total_provider_balance',
            'outstanding_balance' => 'total_provider_balance', 'provider_share_amount' => 'total_provider_share',
            'provider_paid_amount' => 'total_provider_paid', 'provider_balance_due' => 'total_provider_balance',
            'provider_share' => 'total_provider_share', 'provider_paid' => 'total_provider_paid', 'provider_remaining' => 'total_provider_balance'];
        foreach ($buckets as &$bucket) {
            foreach ($keys as $key) { if ($bucket[$key] !== null) { $bucket[$key] = round($bucket[$key], 2); } }
            foreach ($aliases as $alias => $key) { $bucket[$alias] = $bucket[$key]; }
        }
        unset($bucket);
        ksort($buckets);
        $singleKnown = count($buckets) === 1 && !isset($buckets['UNKNOWN']);
        $flat = $singleKnown ? reset($buckets) : array_fill_keys(array_merge($keys, array_keys($aliases)), $buckets ? null : 0.0);
        return $flat + ['payout_count' => array_sum(array_map(static fn(array $r): int => (int) ($r['allocation_count'] ?? 1), $rows)), 'currency' => $singleKnown ? array_key_first($buckets) : null,
            'currency_buckets' => $buckets, 'currency_unsupported' => isset($buckets['UNKNOWN']),
            'currency_mixed' => count($buckets) > 1, 'inconsistent_finance_ids' => array_values(array_unique($inconsistent))];
    }

    public static function baseCompatible(array $summary, string $base): bool
    {
        return !$summary['currency_buckets'] || (count($summary['currency_buckets']) === 1 && isset($summary['currency_buckets'][self::currency($base)]) && self::currency($base) !== 'UNKNOWN');
    }

    /** A base-currency summary cannot omit a retained native provider cash row. */
    public static function guardBaseSummary(array $summary, array $rows): array
    {
        $unknown = array_filter($rows, static fn(array $r): bool => ($r['event_source'] ?? '') === 'provider_payout_payment' && $r['amount_base'] === null);
        if (!$unknown) { return $summary; }
        $nullAmounts = static function (array $values) use (&$nullAmounts): array {
            foreach ($values as $key => &$value) {
                if (is_array($value)) { $value = $nullAmounts($value); }
                elseif (is_numeric($value) && in_array($key, ['in', 'out', 'net', 'balance', 'closing_balance', 'opening_balance', 'total', 'amount', 'safe_cash', 'bank', 'undeposited_checks', 'total_liquid_funds'], true)) { $value = null; }
            }
            return $values;
        };
        return $nullAmounts($summary);
    }

    public static function native($amount, $currency): string
    {
        if ($amount === null) { return lang('Finance.providerCurrencyUnavailable'); }
        $currency = self::currency($currency);
        return number_format((float) $amount, 2, '.', ',') . ' ' . ($currency === 'UNKNOWN' ? lang('Finance.providerCurrencyUnknown') : $currency);
    }

    public static function format(array $summary, string $key): string
    {
        $parts = [];
        foreach ($summary['currency_buckets'] ?? [] as $currency => $bucket) {
            $parts[] = self::native($bucket[$key] ?? null, $currency);
        }
        return $parts ? implode(' / ', $parts) : '0.00';
    }
}