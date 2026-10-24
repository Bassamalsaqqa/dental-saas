<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<?php $formatMoney = static fn($amount) => formatCurrency((float) $amount); ?>
<?php
$transferCount = count($transfers ?? []);
$transferTotal = array_sum(array_map(static fn($row) => (float) ($row['amount'] ?? 0), $transfers ?? []));
?>


<div>
    <div class="finance-transfers-wrap mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <section class="finance-transfers-hero overflow-hidden border sm:p-6 lg:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center border">
                        <i class="fas fa-right-left text-lg"></i>
                    </div>
                    <div>
                        <h1 class="v3-section-title tracking-tight"><?= lang('Finance.treasuryTransfers') ?></h1>
                    </div>
                </div>
                <?php if (!empty($can_access_finance_reports)): ?>
                    <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold border transition v3-btn v3-btn--secondary">
                        <i class="fas fa-arrow-left rtl-back-icon"></i>
                        <?= lang('Finance.backToReports') ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="finance-transfers-card">
                <p class="text-xs uppercase tracking-wide v3-text-primary"><?= lang('Finance.recentTransfers') ?></p>
                <p class="mt-3 v3-title"><?= number_format($transferCount) ?></p>
            </div>
            <div class="finance-transfers-card">
                <p class="text-xs uppercase tracking-wide"><?= lang('Finance.total') ?></p>
                <p class="mt-3 v3-title"><?= $formatMoney($transferTotal) ?></p>
            </div>
        </section>

        <section class="finance-transfers-panel">
            <div class="border-b px-6 py-5">
                <h2 class="text-lg"><?= lang('Finance.recordTransfer') ?></h2>
            </div>
            <form method="POST" action="<?= base_url('finance/transfers/store') ?>" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <?= csrf_field() ?>
            <div class="space-y-1">
                <label class="block text-xs uppercase tracking-wide"><?= lang('Finance.fromAccount') ?></label>
                <select name="from_account" class="finance-transfers-input v3-select" required>
                    <option value=""><?= lang('Finance.selectAccount') ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= old('from_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs uppercase tracking-wide"><?= lang('Finance.toAccount') ?></label>
                <select name="to_account" class="finance-transfers-input v3-select" required>
                    <option value=""><?= lang('Finance.selectAccount') ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= old('to_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs uppercase tracking-wide"><?= lang('Finance.transferType') ?></label>
                <select name="transfer_type" class="finance-transfers-input v3-select" required>
                    <option value="cash_deposit_to_bank" <?= old('transfer_type') === 'cash_deposit_to_bank' ? 'selected' : '' ?>><?= lang('Finance.cashDepositToBank') ?></option>
                    <option value="bank_withdrawal_to_safe" <?= old('transfer_type') === 'bank_withdrawal_to_safe' ? 'selected' : '' ?>><?= lang('Finance.bankWithdrawalToSafe') ?></option>
                    <option value="check_deposit_to_bank" <?= old('transfer_type') === 'check_deposit_to_bank' ? 'selected' : '' ?>><?= lang('Finance.checkDepositToBank') ?></option>
                    <option value="internal_transfer" <?= old('transfer_type') === 'internal_transfer' ? 'selected' : '' ?>><?= lang('Finance.internalTransfer') ?></option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs uppercase tracking-wide"><?= lang('Finance.amount') ?></label>
                <input type="number" step="0.01" min="0.01" name="amount" value="<?= esc(old('amount')) ?>" class="finance-transfers-input v3-input" required>
            </div>
            <div class="space-y-1">
                <label class="block text-xs uppercase tracking-wide"><?= lang('Finance.date') ?></label>
                <input type="text" name="transfer_date" data-clinic-date="1" value="<?= esc(old('transfer_date', date('Y-m-d'))) ?>" class="finance-transfers-input v3-input" required>
            </div>
            <div class="space-y-1 md:col-span-2 xl:col-span-4">
                <label class="block text-xs uppercase tracking-wide"><?= lang('Finance.notes') ?></label>
                <input type="text" name="notes" value="<?= esc(old('notes')) ?>" class="finance-transfers-input v3-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center px-4 py-3 text-sm transition v3-btn v3-btn--primary">
                    <?= lang('Finance.recordTransfer') ?>
                </button>
            </div>
        </form>
        </section>

        <section class="finance-transfers-panel">
        <div class="border-b px-6 py-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg"><?= lang('Finance.recentTransfers') ?></h2>
                </div>
                <span class="px-3 py-1 text-xs font-bold border"><?= number_format($transferCount) ?> <?= lang('Finance.eventsLabel') ?></span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y v3-border-default text-sm">
                    <thead class="">
                    <tr class="text-left text-xs uppercase tracking-wide">
                        <th class="px-6 py-4"><?= lang('Finance.date') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.transferType') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.fromAccount') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.toAccount') ?></th>
                        <th class="px-6 py-4 text-right"><?= lang('Finance.amount') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.notes') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y v3-border-default">
                    <?php if (!empty($transfers)): ?>
                        <?php foreach ($transfers as $transfer): ?>
                            <tr class="transition-colors duration-150">
                                <td class="px-6 py-4 font-bold"><?= esc($transfer['transfer_date'] ?? '-') ?></td>
                                <td class="px-6 py-4 font-bold"><?= esc(lang('Finance.' . ($transfer['transfer_type'] ?? 'internalTransfer'))) ?></td>
                                <td class="px-6 py-4"><?= esc($liquidity_account_options[$transfer['from_account'] ?? ''] ?? ($transfer['from_account'] ?? '-')) ?></td>
                                <td class="px-6 py-4"><?= esc($liquidity_account_options[$transfer['to_account'] ?? ''] ?? ($transfer['to_account'] ?? '-')) ?></td>
                                <td class="px-6 py-4 text-right v3-text-primary"><?= formatCurrency((float) ($transfer['amount'] ?? 0)) ?></td>
                                <td class="px-6 py-4"><?= esc($transfer['notes'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-10 text-center"><?= lang('Finance.noTransfersRecorded') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </section>
    </div>
</div>
</div>
<?= $this->endSection() ?>
