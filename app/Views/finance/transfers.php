<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $formatMoney = static fn($amount) => formatCurrency((float) $amount); ?>
<?php
$transferCount = count($transfers ?? []);
$transferTotal = array_sum(array_map(static fn($row) => (float) ($row['amount'] ?? 0), $transfers ?? []));
?>
<style>
    .finance-transfers-wrap {
        width: min(100%, 1500px);
    }

    .finance-transfers-hero {
        border-radius: 1.75rem;
        background:
            radial-gradient(circle at 10% 8%, rgba(255, 255, 255, 0.22), transparent 30%),
            radial-gradient(circle at 88% 0%, rgba(14, 165, 233, 0.28), transparent 34%),
            linear-gradient(135deg, #047857 0%, #0f766e 42%, #0f172a 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    }

    [dir="rtl"] .finance-transfers-hero {
        background:
            radial-gradient(circle at 90% 8%, rgba(255, 255, 255, 0.22), transparent 30%),
            radial-gradient(circle at 12% 0%, rgba(14, 165, 233, 0.28), transparent 34%),
            linear-gradient(225deg, #047857 0%, #0f766e 42%, #0f172a 100%);
    }

    .finance-transfers-card {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        border-radius: 1.5rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .finance-transfers-card::after {
        content: "";
        position: absolute;
        inset: auto -18% -46% auto;
        width: 72%;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        z-index: -1;
    }

    [dir="rtl"] .finance-transfers-card::after {
        inset-inline-start: -18%;
        inset-inline-end: auto;
    }

    .finance-transfers-panel {
        border-radius: 1.5rem;
        border: 1px solid rgba(226, 232, 240, 0.92);
        background: rgba(255, 255, 255, 0.97);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .finance-transfers-input {
        border-radius: 0.9rem;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #0f172a;
        font-size: 0.875rem;
        font-weight: 700;
        outline: none;
        padding: 0.7rem 0.85rem;
        width: 100%;
        transition: border-color 160ms ease, box-shadow 160ms ease;
    }

    .finance-transfers-input:focus {
        border-color: #059669;
        box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.15);
    }
</style>

<div>
    <div class="finance-transfers-wrap mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="finance-transfers-hero overflow-hidden border border-white/20 p-5 text-white sm:p-6 lg:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg shadow-black/10 ring-1 ring-white/20">
                        <i class="fas fa-right-left text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight"><?= lang('Finance.treasuryTransfers') ?></h1>
                    </div>
                </div>
                <?php if (!empty($can_access_finance_reports)): ?>
                    <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-arrow-left rtl-back-icon"></i>
                        <?= lang('Finance.backToReports') ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="finance-transfers-card bg-gradient-to-br from-emerald-600 to-teal-800 p-5 text-white shadow-emerald-500/25">
                <p class="text-xs font-black uppercase tracking-wide text-emerald-50/90"><?= lang('Finance.recentTransfers') ?></p>
                <p class="mt-3 text-3xl font-black"><?= number_format($transferCount) ?></p>
            </div>
            <div class="finance-transfers-card bg-gradient-to-br from-slate-700 to-slate-950 p-5 text-white shadow-slate-500/25">
                <p class="text-xs font-black uppercase tracking-wide text-slate-100/90"><?= lang('Finance.total') ?></p>
                <p class="mt-3 text-3xl font-black"><?= $formatMoney($transferTotal) ?></p>
            </div>
        </section>

        <section class="finance-transfers-panel">
            <div class="border-b border-slate-200 bg-slate-50/90 px-6 py-5">
                <h2 class="text-lg font-black text-slate-950"><?= lang('Finance.recordTransfer') ?></h2>
            </div>
            <form method="POST" action="<?= base_url('finance/transfers/store') ?>" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-5">
            <?= csrf_field() ?>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wide text-slate-600"><?= lang('Finance.fromAccount') ?></label>
                <select name="from_account" class="finance-transfers-input" required>
                    <option value=""><?= lang('Finance.selectAccount') ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= old('from_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wide text-slate-600"><?= lang('Finance.toAccount') ?></label>
                <select name="to_account" class="finance-transfers-input" required>
                    <option value=""><?= lang('Finance.selectAccount') ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= old('to_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wide text-slate-600"><?= lang('Finance.transferType') ?></label>
                <select name="transfer_type" class="finance-transfers-input" required>
                    <option value="cash_deposit_to_bank" <?= old('transfer_type') === 'cash_deposit_to_bank' ? 'selected' : '' ?>><?= lang('Finance.cashDepositToBank') ?></option>
                    <option value="bank_withdrawal_to_safe" <?= old('transfer_type') === 'bank_withdrawal_to_safe' ? 'selected' : '' ?>><?= lang('Finance.bankWithdrawalToSafe') ?></option>
                    <option value="check_deposit_to_bank" <?= old('transfer_type') === 'check_deposit_to_bank' ? 'selected' : '' ?>><?= lang('Finance.checkDepositToBank') ?></option>
                    <option value="internal_transfer" <?= old('transfer_type') === 'internal_transfer' ? 'selected' : '' ?>><?= lang('Finance.internalTransfer') ?></option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wide text-slate-600"><?= lang('Finance.amount') ?></label>
                <input type="number" step="0.01" min="0.01" name="amount" value="<?= esc(old('amount')) ?>" class="finance-transfers-input" required>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wide text-slate-600"><?= lang('Finance.date') ?></label>
                <input type="text" name="transfer_date" data-clinic-date="1" value="<?= esc(old('transfer_date', date('Y-m-d'))) ?>" class="finance-transfers-input" required>
            </div>
            <div class="space-y-1 md:col-span-2 xl:col-span-4">
                <label class="block text-xs font-black uppercase tracking-wide text-slate-600"><?= lang('Finance.notes') ?></label>
                <input type="text" name="notes" value="<?= esc(old('notes')) ?>" class="finance-transfers-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:from-emerald-700 hover:to-teal-800">
                    <?= lang('Finance.recordTransfer') ?>
                </button>
            </div>
        </form>
        </section>

        <section class="finance-transfers-panel">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-800 to-slate-950 px-6 py-5 text-white">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-black"><?= lang('Finance.recentTransfers') ?></h2>
                </div>
                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white ring-1 ring-white/20"><?= number_format($transferCount) ?> <?= lang('Finance.eventsLabel') ?></span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100/95">
                    <tr class="text-left text-xs font-black uppercase tracking-wide text-slate-500">
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
                                <td class="px-6 py-4 font-bold text-slate-700"><?= esc($transfer['transfer_date'] ?? '-') ?></td>
                                <td class="px-6 py-4 font-bold text-slate-900"><?= esc(lang('Finance.' . ($transfer['transfer_type'] ?? 'internalTransfer'))) ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= esc($liquidity_account_options[$transfer['from_account'] ?? ''] ?? ($transfer['from_account'] ?? '-')) ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= esc($liquidity_account_options[$transfer['to_account'] ?? ''] ?? ($transfer['to_account'] ?? '-')) ?></td>
                                <td class="px-6 py-4 text-right font-black text-emerald-700"><?= formatCurrency((float) ($transfer['amount'] ?? 0)) ?></td>
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
</div>
<?= $this->endSection() ?>
