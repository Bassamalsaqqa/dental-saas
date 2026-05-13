<?php

namespace App\Services;

class FinanceTreasuryService
{
    public const ACCOUNTS = ['safe_cash', 'bank', 'undeposited_checks'];

    public function isValidLiquidityAccount(?string $account): bool
    {
        return in_array((string) $account, self::ACCOUNTS, true);
    }

    public function getLiquidityAccountOptions(): array
    {
        return [
            'safe_cash' => 'safe_cash',
            'bank' => 'bank',
            'undeposited_checks' => 'undeposited_checks',
        ];
    }

    public function defaultLiquidityAccount(string $paymentMethod, string $direction, ?string $transactionType = null): ?string
    {
        $paymentMethod = strtolower(trim($paymentMethod));
        $direction = strtolower(trim($direction));
        $transactionType = strtolower(trim((string) $transactionType));

        if ($paymentMethod === 'cash') {
            return 'safe_cash';
        }

        if (in_array($paymentMethod, ['card', 'bank_transfer'], true)) {
            return 'bank';
        }

        if ($paymentMethod === 'check') {
            if ($direction === 'in') {
                return 'undeposited_checks';
            }

            if (in_array($transactionType, ['refund', 'provider_payout'], true)) {
                return 'bank';
            }

            return 'bank';
        }

        if (in_array($paymentMethod, ['insurance', 'other'], true)) {
            return 'bank';
        }

        return null;
    }

    public function isCompatibleLiquidityAccount(string $paymentMethod, string $direction, string $account, ?string $transactionType = null): bool
    {
        $paymentMethod = strtolower(trim($paymentMethod));
        $direction = strtolower(trim($direction));
        $account = strtolower(trim($account));
        $transactionType = strtolower(trim((string) $transactionType));

        if (!$this->isValidLiquidityAccount($account)) {
            return false;
        }

        if ($direction === 'out' && $transactionType === 'expense') {
            return true;
        }

        return match ($paymentMethod) {
            'cash' => $account === 'safe_cash',
            'card', 'bank_transfer', 'insurance', 'other' => $account === 'bank',
            'check' => $direction === 'in'
                ? $account === 'undeposited_checks'
                : in_array($account, ['bank', 'undeposited_checks'], true),
            default => false,
        };
    }

    public function normalizeLiquidityAccount(string $paymentMethod, string $direction, ?string $requestedAccount = null, ?string $transactionType = null): ?string
    {
        $requestedAccount = strtolower(trim((string) $requestedAccount));
        if ($requestedAccount !== '' && $this->isCompatibleLiquidityAccount($paymentMethod, $direction, $requestedAccount, $transactionType)) {
            return $requestedAccount;
        }

        return $this->defaultLiquidityAccount($paymentMethod, $direction, $transactionType);
    }

    public function summarizeMovement(array $rows): array
    {
        $summary = [];
        foreach (self::ACCOUNTS as $account) {
            $summary[$account] = ['in' => 0.0, 'out' => 0.0, 'net' => 0.0];
        }

        foreach ($rows as $row) {
            $account = (string) ($row['liquidity_account'] ?? '');
            if (!$this->isValidLiquidityAccount($account)) {
                continue;
            }

            $signed = (float) ($row['signed_amount_base'] ?? 0);
            if ($signed >= 0) {
                $summary[$account]['in'] += $signed;
            } else {
                $summary[$account]['out'] += abs($signed);
            }
        }

        $externalIn = 0.0;
        $externalOut = 0.0;
        foreach (self::ACCOUNTS as $account) {
            $summary[$account]['in'] = round($summary[$account]['in'], 2);
            $summary[$account]['out'] = round($summary[$account]['out'], 2);
            $summary[$account]['net'] = round($summary[$account]['in'] - $summary[$account]['out'], 2);
        }

        foreach ($rows as $row) {
            if (($row['event_source'] ?? '') === 'liquidity_transfer') {
                continue;
            }
            $account = (string) ($row['liquidity_account'] ?? '');
            if (!$this->isValidLiquidityAccount($account)) {
                continue;
            }
            $signed = (float) ($row['signed_amount_base'] ?? 0);
            if ($signed >= 0) {
                $externalIn += $signed;
            } else {
                $externalOut += abs($signed);
            }
        }

        $summary['totals'] = [
            'in' => round($externalIn, 2),
            'out' => round($externalOut, 2),
            'net' => round($externalIn - $externalOut, 2),
        ];

        return $summary;
    }

    public function summarizeClosingBalances(array $rows): array
    {
        $balances = [
            'safe_cash' => 0.0,
            'bank' => 0.0,
            'undeposited_checks' => 0.0,
        ];

        foreach ($rows as $row) {
            $account = (string) ($row['liquidity_account'] ?? '');
            if (!$this->isValidLiquidityAccount($account)) {
                continue;
            }

            $balances[$account] += (float) ($row['signed_amount_base'] ?? 0);
        }

        foreach (self::ACCOUNTS as $account) {
            $balances[$account] = round($balances[$account], 2);
        }
        $balances['total_liquid_funds'] = round($balances['safe_cash'] + $balances['bank'] + $balances['undeposited_checks'], 2);

        return $balances;
    }
}
