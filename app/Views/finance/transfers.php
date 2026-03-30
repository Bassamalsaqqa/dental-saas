<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $formatMoney = static fn($amount) => formatCurrency((float) $amount); ?>
<div class="space-y-6">
    <section class="rounded-xl border border-emerald-200 bg-emerald-50/30 shadow-sm overflow-hidden">
        <div class="border-b border-emerald-100 bg-white/80 px-6 py-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="fas fa-right-left text-sm"></i>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900"><?= lang('Finance.treasuryTransfers') ?></h1>
                    <p class="text-xs text-gray-500"><?= lang('Finance.treasuryTransfersHelp') ?></p>
                </div>
            </div>
            <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-2 rtl-back-icon"></i>
                <?= lang('Finance.backToReports') ?>
            </a>
        </div>
        <div class="grid grid-cols-1 gap-4 border-b border-emerald-100 px-6 py-4 md:grid-cols-3">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= lang('Finance.recentTransfers') ?></p>
                <p class="mt-2 text-2xl font-black text-emerald-950"><?= number_format(count($transfers ?? [])) ?></p>
                <p class="mt-2 text-xs text-emerald-700/80"><?= lang('Finance.treasuryTransfersHelp') ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-700"><?= lang('Finance.total') ?></p>
                <p class="mt-2 text-2xl font-black text-slate-950"><?= $formatMoney(array_sum(array_map(static fn($row) => (float) ($row['amount'] ?? 0), $transfers ?? []))) ?></p>
                <p class="mt-2 text-xs text-slate-600"><?= lang('Finance.periodMovement') ?></p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700"><?= lang('Finance.transferType') ?></p>
                <p class="mt-2 text-sm font-semibold text-amber-950"><?= esc(lang('Finance.cashDepositToBank')) ?></p>
                <p class="text-sm font-semibold text-amber-950"><?= esc(lang('Finance.bankWithdrawalToSafe')) ?></p>
                <p class="text-sm font-semibold text-amber-950"><?= esc(lang('Finance.checkDepositToBank')) ?></p>
            </div>
        </div>
        <div class="border-b border-emerald-100 px-6 py-4">
            <div class="rounded-xl border border-emerald-200 bg-white/90 p-4">
                <div class="mb-3 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <i class="fas fa-circle-info text-sm"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900"><?= lang('Finance.treasuryOperatorGuide') ?></h2>
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600"><?= lang('Finance.cashDepositToBank') ?></p>
                        <p class="mt-2 text-sm text-gray-600"><?= lang('Finance.cashDepositToBankHelp') ?></p>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50/40 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600"><?= lang('Finance.bankWithdrawalToSafe') ?></p>
                        <p class="mt-2 text-sm text-gray-600"><?= lang('Finance.bankWithdrawalToSafeHelp') ?></p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600"><?= lang('Finance.checkDepositToBank') ?></p>
                        <p class="mt-2 text-sm text-gray-600"><?= lang('Finance.checkDepositToBankHelp') ?></p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600"><?= lang('Finance.internalTransfer') ?></p>
                        <p class="mt-2 text-sm text-gray-600"><?= lang('Finance.internalTransferHelp') ?></p>
                    </div>
                </div>
            </div>
        </div>
        <form method="POST" action="<?= base_url('finance/transfers/store') ?>" class="grid grid-cols-1 gap-4 bg-white/80 px-6 py-5 md:grid-cols-2 xl:grid-cols-5">
            <?= csrf_field() ?>
            <div class="space-y-1">
                <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.fromAccount') ?></label>
                <select name="from_account" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                    <option value=""><?= lang('Finance.selectAccount') ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= old('from_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.toAccount') ?></label>
                <select name="to_account" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                    <option value=""><?= lang('Finance.selectAccount') ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= old('to_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.transferType') ?></label>
                <select name="transfer_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                    <option value="cash_deposit_to_bank" <?= old('transfer_type') === 'cash_deposit_to_bank' ? 'selected' : '' ?>><?= lang('Finance.cashDepositToBank') ?></option>
                    <option value="bank_withdrawal_to_safe" <?= old('transfer_type') === 'bank_withdrawal_to_safe' ? 'selected' : '' ?>><?= lang('Finance.bankWithdrawalToSafe') ?></option>
                    <option value="check_deposit_to_bank" <?= old('transfer_type') === 'check_deposit_to_bank' ? 'selected' : '' ?>><?= lang('Finance.checkDepositToBank') ?></option>
                    <option value="internal_transfer" <?= old('transfer_type') === 'internal_transfer' ? 'selected' : '' ?>><?= lang('Finance.internalTransfer') ?></option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.amount') ?></label>
                <input type="number" step="0.01" min="0.01" name="amount" value="<?= esc(old('amount')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.date') ?></label>
                <input type="text" name="transfer_date" data-clinic-date="1" value="<?= esc(old('transfer_date', date('Y-m-d'))) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
            </div>
            <div class="space-y-1 md:col-span-2 xl:col-span-4">
                <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.notes') ?></label>
                <input type="text" name="notes" value="<?= esc(old('notes')) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <?= lang('Finance.recordTransfer') ?>
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-xl border border-slate-200 bg-slate-50/40 shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900"><?= lang('Finance.recentTransfers') ?></h2>
                    <p class="mt-1 text-sm text-gray-500"><?= lang('Finance.treasuryMovementHelp') ?></p>
                </div>
                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700"><?= number_format(count($transfers ?? [])) ?> <?= lang('Finance.eventsLabel') ?></span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100/80">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-6 py-4"><?= lang('Finance.date') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.transferType') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.fromAccount') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.toAccount') ?></th>
                        <th class="px-6 py-4 text-right"><?= lang('Finance.amount') ?></th>
                        <th class="px-6 py-4"><?= lang('Finance.notes') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/95">
                    <?php if (!empty($transfers)): ?>
                        <?php foreach ($transfers as $transfer): ?>
                            <tr class="transition-colors duration-150 hover:bg-slate-50">
                                <td class="px-6 py-4 text-gray-700"><?= esc($transfer['transfer_date'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-gray-700"><?= esc(lang('Finance.' . ($transfer['transfer_type'] ?? 'internalTransfer'))) ?></td>
                                <td class="px-6 py-4 text-gray-700"><?= esc($liquidity_account_options[$transfer['from_account'] ?? ''] ?? ($transfer['from_account'] ?? '-')) ?></td>
                                <td class="px-6 py-4 text-gray-700"><?= esc($liquidity_account_options[$transfer['to_account'] ?? ''] ?? ($transfer['to_account'] ?? '-')) ?></td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900"><?= formatCurrency((float) ($transfer['amount'] ?? 0)) ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= esc($transfer['notes'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500"><?= lang('Finance.noTransfersRecorded') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
