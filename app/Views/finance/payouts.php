<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<p class="px-4 py-2 text-sm text-amber-800"><?= esc(lang('Finance.providerCurrencyWarning')) ?></p>
<?php $isRtl = !empty($is_rtl); ?>

<style>
.finance-payouts-wrap {
    width: 100%;
    font-family: var(--app-font-sans);
}

[dir="rtl"] .finance-payouts-wrap {
    font-family: var(--app-font-ar);
}

.finance-payouts-hero {
    border-radius: 1.75rem;
    background:
        radial-gradient(circle at 12% 12%, rgba(255, 255, 255, 0.2), transparent 29%),
        radial-gradient(circle at 82% 6%, rgba(45, 212, 191, 0.28), transparent 32%),
        linear-gradient(135deg, #0f766e 0%, #075985 48%, #0f172a 100%);
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.22);
}

[dir="rtl"] .finance-payouts-hero {
    background:
        radial-gradient(circle at 88% 12%, rgba(255, 255, 255, 0.2), transparent 29%),
        radial-gradient(circle at 18% 6%, rgba(45, 212, 191, 0.28), transparent 32%),
        linear-gradient(225deg, #0f766e 0%, #075985 48%, #0f172a 100%);
}

.finance-payouts-card,
.finance-payouts-panel {
    position: relative;
    isolation: isolate;
    overflow: hidden;
}

.finance-payouts-card {
    border-radius: 1.5rem;
    box-shadow: 0 20px 52px rgba(15, 23, 42, 0.13);
}

.finance-payouts-card::after {
    content: "";
    position: absolute;
    inset: auto -18% -46% auto;
    width: 72%;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    z-index: -1;
}

[dir="rtl"] .finance-payouts-card::after {
    inset-inline-start: -18%;
    inset-inline-end: auto;
}

.finance-payouts-panel {
    border-radius: 1.5rem;
    border: 1px solid rgba(226, 232, 240, 0.92);
    background: rgba(255, 255, 255, 0.97);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.09);
}

.finance-payouts-input {
    border-radius: 0.9rem;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #0f172a;
    font-size: 0.875rem;
    font-weight: 700;
    outline: none;
    padding: 0.7rem 0.85rem;
    width: 100%;
    font-family: inherit;
    transition: border-color 160ms ease, box-shadow 160ms ease;
}

.finance-payouts-wrap .dataTables_wrapper,
.finance-payouts-wrap .dataTables_wrapper *,
.finance-payouts-wrap table,
.finance-payouts-wrap button,
.finance-payouts-wrap input,
.finance-payouts-wrap select,
.finance-payouts-wrap textarea {
    font-family: inherit !important;
}

.finance-payouts-table-shell .dataTables_wrapper .dataTables_info,
.finance-payouts-table-shell .dataTables_wrapper .dataTables_paginate {
    padding-block: 0.35rem;
}

.finance-payouts-input:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.14);
}

.dataTables_wrapper {
    position: relative !important;
}
.dataTables_info {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
}
.dataTables_paginate .paginate_button {
    padding: 8px 12px !important;
    margin: 0 2px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
}
.dataTables_paginate .paginate_button:hover {
    background-color: #f3f4f6 !important;
    border-color: #9ca3af !important;
    color: #1f2937 !important;
}
.dataTables_paginate .paginate_button.current {
    background-color: #4f46e5 !important;
    border-color: #4f46e5 !important;
    color: white !important;
}
.dataTables_paginate .paginate_button.disabled {
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
}
[dir="rtl"] .dataTables_wrapper .dataTables_filter {
    margin-left: 0 !important;
    margin-right: auto !important;
}
[dir="rtl"] .dataTables_wrapper .dataTables_length {
    margin-right: 0 !important;
    margin-left: auto !important;
}
[dir="rtl"] .dataTables_wrapper .dataTables_info {
    margin-right: 0 !important;
    margin-left: auto !important;
}
[dir="rtl"] .dataTables_wrapper .dataTables_paginate {
    margin-left: 0 !important;
    margin-right: auto !important;
}
[dir="rtl"] .dataTables_wrapper table th,
[dir="rtl"] .dataTables_wrapper table td {
    text-align: right !important;
}
[dir="rtl"] .dataTables_wrapper .dataTables_filter input {
    margin-right: 0 !important;
}
</style>

<div dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="finance-payouts-wrap space-y-5 px-3 py-4 sm:px-5 lg:px-6">
        <section class="finance-payouts-hero overflow-hidden border border-white/20 p-5 text-white sm:p-6 lg:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl bg-white/15 text-white shadow-lg shadow-black/10 ring-1 ring-white/20">
                        <i class="fas fa-hand-holding-dollar text-xl"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-black uppercase tracking-wide text-cyan-50 ring-1 ring-white/20">
                            <i class="fas fa-user-doctor"></i>
                            <span><?= lang('Finance.providerPayouts') ?></span>
                        </div>
                        <h1 class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl"><?= lang('Finance.providerPayouts') ?></h1>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    <?php if (!empty($can_access_finance_reports)): ?>
                    <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center rounded-2xl bg-white/15 px-4 py-2.5 text-sm font-extrabold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-chart-bar <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.reports') ?>
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-2.5 text-sm font-extrabold text-teal-800 shadow-lg shadow-slate-950/20 transition hover:-translate-y-0.5">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right ml-2' : 'fa-arrow-left mr-2' ?>"></i><?= lang('Finance.backToFinance') ?>
                    </a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="finance-payouts-card bg-gradient-to-br from-blue-700 via-indigo-700 to-slate-950 p-5 text-white shadow-blue-900/25">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-indigo-50/90"><?= lang('Finance.providerShareTotal') ?></p>
                        <p class="mt-3 text-3xl font-black"><?= esc(\App\Services\ProviderCurrencySummary::format($stats, 'total_provider_share')) ?></p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20">
                        <i class="fas fa-percent text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="finance-payouts-card bg-gradient-to-br from-rose-700 via-red-700 to-slate-950 p-5 text-white shadow-rose-900/25">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-rose-50/90"><?= lang('Finance.outstandingPayable') ?></p>
                        <p class="mt-3 text-3xl font-black"><?= esc(\App\Services\ProviderCurrencySummary::format($stats, 'total_provider_balance')) ?></p>
                        <p class="mt-2 text-xs text-rose-50/90"><?= esc(lang('Finance.providerVoidBalanceHelp')) ?></p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20">
                        <i class="fas fa-triangle-exclamation text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="finance-payouts-card bg-gradient-to-br from-emerald-700 via-teal-700 to-slate-950 p-5 text-white shadow-emerald-900/25">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-emerald-50/90"><?= lang('Finance.clinicShareTotal') ?></p>
                        <p class="mt-3 text-3xl font-black"><?= esc(\App\Services\ProviderCurrencySummary::format($stats, 'total_clinic_share')) ?></p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20">
                        <i class="fas fa-building text-lg"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="finance-payouts-panel finance-payouts-table-shell">
            <div class="border-b border-slate-200/80 bg-gradient-to-r from-white via-teal-50/80 to-sky-50/80 px-5 py-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-700 text-white shadow-lg shadow-teal-700/20">
                            <i class="fas fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-black text-slate-950"><?= lang('Finance.payoutLedger') ?></h3>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <select id="statusFilter" class="finance-payouts-input sm:w-48">
                            <option value=""><?= lang('Finance.allStatuses') ?></option>
                            <option value="unpaid"><?= lang('Finance.unpaid') ?></option>
                            <option value="partial"><?= lang('Finance.partial') ?></option>
                            <option value="paid"><?= lang('Finance.paid') ?></option>
                            <option value="void"><?= lang('Finance.void') ?></option>
                        </select>
                        <div class="relative">
                            <span class="absolute <?= $isRtl ? 'right-3' : 'left-3' ?> top-3 text-slate-400 text-sm"><i class="fas fa-search"></i></span>
                            <input id="payoutSearch" type="text" class="finance-payouts-input <?= $isRtl ? 'pr-9 pl-3' : 'pl-9 pr-3' ?> sm:w-80" placeholder="<?= esc(lang('Finance.searchProviderPatientTxn')) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="providerPayoutsTable" class="w-full min-w-full" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                    <thead class="border-b border-slate-200 bg-slate-50/95">
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-receipt text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.transaction') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-user text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.patient') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-user-doctor text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.provider') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-money-bill-trend-up text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.split') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-wallet text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.paidBalance') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-info-circle text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.status') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-calendar text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.date') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-600 uppercase tracking-wider"><i class="fas fa-cogs text-teal-700 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody id="providerPayoutsTableBody" class="divide-y divide-slate-100 bg-white"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="recordPayoutModal" class="fixed inset-0 z-[2147483500] hidden">
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" onclick="closePayoutModal()"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-[1.65rem] bg-white shadow-2xl shadow-slate-950/25 border border-slate-200">
            <div class="flex items-center justify-between gap-4 bg-gradient-to-r from-teal-700 via-sky-800 to-slate-950 px-5 py-4 text-white">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </span>
                    <h4 class="text-base font-black"><?= lang('Finance.recordProviderPayout') ?></h4>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20" onclick="closePayoutModal()"><i class="fas fa-times"></i></button>
            </div>
            <form id="recordPayoutForm" class="p-5 space-y-4">
                <input type="hidden" id="payoutIdInput">
                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.amount') ?></label>
                    <input type="number" step="0.01" min="0.01" id="payoutAmountInput" class="finance-payouts-input" required>
                    <p id="payoutAmountHelp" class="mt-2 text-xs font-semibold text-slate-500"></p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.paymentMethod') ?></label>
                    <select id="payoutMethodInput" class="finance-payouts-input">
                        <option value="cash"><?= lang('Finance.cash') ?></option>
                        <option value="card"><?= lang('Finance.card') ?></option>
                        <option value="bank_transfer"><?= lang('Finance.bankTransfer') ?></option>
                        <option value="check"><?= lang('Finance.check') ?></option>
                        <option value="other"><?= lang('Finance.other') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.paid') ?> <?= lang('Finance.date') ?></label>
                    <input type="date" id="payoutDateInput" class="finance-payouts-input" required>
                </div>
                </div>
                <div id="payoutLiquidityAccountField" class="hidden">
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.paidFromAccount') ?></label>
                    <select id="payoutLiquidityAccountInput" class="finance-payouts-input">
                        <option value="bank"><?= esc(lang('Finance.liquidityAccount.bank')) ?></option>
                        <option value="undeposited_checks"><?= esc(lang('Finance.liquidityAccount.undeposited_checks')) ?></option>
                    </select>
                    <p class="mt-2 text-xs font-semibold text-slate-500"><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.notes') ?></label>
                    <textarea id="payoutNotesInput" rows="2" class="finance-payouts-input" placeholder="<?= esc(lang('Finance.optional')) ?>"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closePayoutModal()" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50"><?= lang('Finance.cancel') ?></button>
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-teal-700 to-sky-800 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-teal-700/20 transition hover:-translate-y-0.5"><?= lang('Finance.recordPayment') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let payoutsTable = null;
let payoutModalMaxAmount = 0;
let payoutModalCurrency = null;
const payoutCurrencySymbol = '<?= esc($currency_symbol ?? '$') ?>';
const payoutCurrencyPosition = '<?= esc($currency_position ?? 'before') ?>';
const payoutStatusLabels = {
    unpaid: '<?= esc(lang('Finance.unpaid')) ?>',
    partial: '<?= esc(lang('Finance.partial')) ?>',
    paid: '<?= esc(lang('Finance.paid')) ?>',
    void: '<?= esc(lang('Finance.void')) ?>',
    unknown: '<?= esc(lang('Finance.unknown')) ?>'
};

function getPatientAvatar(gender) {
    const normalized = String(gender || 'other').toLowerCase();
    const map = {
        male: { icon: 'fas fa-person', color: 'text-blue-700' },
        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
        other: { icon: 'fas fa-user', color: 'text-slate-600' }
    };
    return map[normalized] || map.other;
}

function getTransactionTypeMeta(type) {
    const normalized = String(type || '').toLowerCase();
    const map = {
        payment: { icon: 'fas fa-money-check-dollar', iconColor: 'text-emerald-700' },
        invoice: { icon: 'fas fa-file-invoice-dollar', iconColor: 'text-blue-700' },
        refund: { icon: 'fas fa-rotate-left', iconColor: 'text-rose-700' },
        adjustment: { icon: 'fas fa-sliders', iconColor: 'text-amber-700' },
        expense: { icon: 'fas fa-receipt', iconColor: 'text-orange-700' }
    };
    return map[normalized] || { icon: 'fas fa-tag', iconColor: 'text-slate-600' };
}

function formatCurrency(value, currency = null) {
    const amount = Number(value || 0);
    const normalized = Number.isFinite(amount) ? amount : 0;
    const formatted = normalized.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    if (currency) return `${formatted} ${currency}`;
    return payoutCurrencyPosition === 'after' ? `${formatted} ${payoutCurrencySymbol}` : `${payoutCurrencySymbol}${formatted}`;
}

function notify(type, title, message) {
    if (typeof window.showToast === 'function') {
        window.showToast(type, title, message);
        return;
    }
    alert(message);
}

function statusBadge(status) {
    const normalized = (status || '').toLowerCase();
    const map = {
        unpaid: { cls: 'bg-rose-100 text-rose-700', icon: 'fas fa-triangle-exclamation' },
        partial: { cls: 'bg-amber-100 text-amber-700', icon: 'fas fa-clock' },
        paid: { cls: 'bg-emerald-100 text-emerald-700', icon: 'fas fa-check-circle' },
        void: { cls: 'bg-gray-100 text-gray-700', icon: 'fas fa-ban' }
    };
    const config = map[normalized] || { cls: 'bg-gray-100 text-gray-700', icon: 'fas fa-circle-question' };
    const label = payoutStatusLabels[normalized] || payoutStatusLabels.unknown;
    return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black ${config.cls}"><i class="${config.icon} me-1"></i>${label}</span>`;
}

function closePayoutModal() {
    document.getElementById('recordPayoutModal').classList.add('hidden');
    document.getElementById('recordPayoutForm').reset();
}

function openPayoutModal(id, maxAmount, currency) {
    payoutModalCurrency = currency;
    payoutModalMaxAmount = Number(maxAmount || 0);
    document.getElementById('recordPayoutForm').reset();
    document.getElementById('payoutIdInput').value = String(id);
    document.getElementById('payoutAmountInput').value = payoutModalMaxAmount > 0 ? payoutModalMaxAmount.toFixed(2) : '';
    document.getElementById('payoutDateInput').value = new Date().toISOString().split('T')[0];
    document.getElementById('payoutAmountHelp').textContent = `<?= lang('Finance.maximumPayable') ?>: ${formatCurrency(payoutModalMaxAmount, payoutModalCurrency)}`;
    syncPayoutLiquidityField();
    document.getElementById('recordPayoutModal').classList.remove('hidden');
}

function syncPayoutLiquidityField() {
    const method = document.getElementById('payoutMethodInput')?.value || '';
    const field = document.getElementById('payoutLiquidityAccountField');
    if (!field) return;
    field.classList.toggle('hidden', method !== 'check');
}

$(document).ready(function () {
    payoutsTable = $('#providerPayoutsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('finance/payouts/data') ?>',
            type: 'POST',
            data: function (d) {
                d[window.csrfConfig.name] = window.getCsrfToken();
                d.status = $('#statusFilter').val();
                return d;
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            complete: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
            }
        },
        columns: [
            {
                data: 'transaction_id',
                render: function (data, type, row) {
                    const typeMeta = getTransactionTypeMeta(row.transaction_type);
                    return `
                        <a href="<?= base_url('finance') ?>/${row.finance_id}" class="block rounded-2xl p-2 transition-colors duration-200 hover:bg-teal-50/80">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-slate-100">
                                    <i class="${typeMeta.icon} ${typeMeta.iconColor} text-lg"></i>
                                </span>
                                <div>
                                    <div class="text-sm font-black text-slate-950">${data || '-'}</div>
                                    <div class="text-xs font-semibold text-slate-500"><?= lang('Finance.financeTransaction') ?></div>
                                </div>
                            </div>
                        </a>`;
                }
            },
            {
                data: 'patient_name',
                render: function (data, type, row) {
                    const avatar = getPatientAvatar(row.patient_gender);
                    const patient = data && data.trim() !== '' ? data : '<?= esc(lang('Finance.notAvailable')) ?>';
                    return `<div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-2xl bg-slate-100">
                            <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-black text-slate-950 truncate">${patient}</div>
                            <div class="text-xs font-semibold text-slate-500"><?= esc(lang('Finance.patient')) ?></div>
                        </div>
                    </div>`;
                }
            },
            {
                data: 'provider_name',
                render: function (data, type, row) {
                    const isGuest = (row.provider_type || '') === 'guest';
                    const iconClass = isGuest ? 'fas fa-user-tie text-amber-600' : 'fas fa-user-doctor text-indigo-600';
                    const subtitle = isGuest ? '<?= esc(lang('Finance.guestProvider')) ?>' : '<?= esc(lang('Finance.clinicUser')) ?>';
                    return `
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 ${isGuest ? 'bg-amber-100' : 'bg-teal-100'} rounded-2xl flex items-center justify-center">
                                <i class="${iconClass} text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-black text-slate-950">${data || '<?= lang('Finance.provider') ?>'}</div>
                                <div class="text-xs font-semibold text-slate-500">${subtitle}</div>
                            </div>
                        </div>`;
                }
            },
            {
                data: 'provider_share_amount',
                render: function (value, type, row) {
                    return `
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm font-black text-blue-800">
                                <i class="fas fa-arrow-down text-rose-500 text-xs"></i>
                                <span>${formatCurrency(value, row.currency)}</span>
                            </div>
                            <div class="text-xs font-semibold text-slate-500"><?= lang('Finance.of') ?> ${formatCurrency(row.gross_amount || 0, row.currency)}</div>
                        </div>`;
                }
            },
            {
                data: 'provider_paid_amount',
                render: function (value, type, row) {
                    return `
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm font-black text-emerald-700">
                                <i class="fas fa-arrow-up text-emerald-500 text-xs"></i>
                                <span>${formatCurrency(value, row.currency)}</span>
                            </div>
                            <div class="text-xs font-bold ${Number(row.provider_balance_due || 0) > 0 ? 'text-rose-600' : 'text-emerald-600'}">
                                <?= lang('Finance.balance') ?>: ${formatCurrency(row.provider_balance_due || 0, row.currency)}
                            </div>
                        </div>`;
                }
            },
            {
                data: 'status',
                render: function (value) {
                    return statusBadge(value);
                }
            },
            {
                data: 'created_at_formatted',
                render: function (value) {
                    return `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar text-teal-700 text-xs"></i>
                            <span class="text-sm font-bold text-slate-700">${value || '-'}</span>
                        </div>`;
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (id, type, row) {
                    const canSettle = row.status !== 'paid' && row.status !== 'void';
                    let actions = '<div class="flex items-center gap-2">';
                    actions += `<a href="<?= base_url('finance') ?>/${row.finance_id}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100" title="<?= lang('Finance.viewTransaction') ?>"><i class="fas fa-eye"></i></a>`;
                    if (canSettle) {
                        actions += `<button type="button" onclick="openPayoutModal(${id}, ${Number(row.provider_balance_due || 0)}, '${row.currency}')" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-100" title="<?= lang('Finance.recordPaymentFromLedger') ?>"><i class="fas fa-money-bill-wave"></i></button>`;
                        actions += `<button type="button" onclick="markPayoutPaid(${id})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-teal-700 ring-1 ring-teal-100 transition hover:bg-teal-100" title="<?= lang('Finance.markFullyPaid') ?>"><i class="fas fa-check-double"></i></button>`;
                    }
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        dom: 'rt<"px-6 py-4 border-t border-slate-100 bg-slate-50/80"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        language: {
            processing: '<?= lang('Finance.loadingPayouts') ?>',
            zeroRecords: '<?= lang('Finance.noProviderPayoutsFound') ?>',
            info: '<?= lang('Finance.showingPayoutsInfo') ?>',
            infoEmpty: '<?= lang('Finance.noPayoutsAvailable') ?>',
            infoFiltered: '<?= lang('Finance.filteredPayoutsInfo') ?>',
            paginate: {
                first: '<?= lang('Finance.first') ?>',
                last: '<?= lang('Finance.last') ?>',
                next: '<?= lang('Finance.next') ?>',
                previous: '<?= lang('Finance.previous') ?>'
            }
        },
        createdRow: function (row) {
            $(row).addClass('hover:bg-teal-50/35 transition-colors duration-150');
        }
    });

    $('#payoutSearch').on('keyup', function () {
        payoutsTable.search(this.value).draw();
    });

    $('#statusFilter').on('change', function () {
        payoutsTable.draw();
    });

    $('#recordPayoutForm').on('submit', function (event) {
        event.preventDefault();
        const id = parseInt(document.getElementById('payoutIdInput').value, 10);
        const amount = parseFloat(document.getElementById('payoutAmountInput').value);
        const method = document.getElementById('payoutMethodInput').value;
        const liquidityAccount = document.getElementById('payoutLiquidityAccountInput').value;
        const paidDate = document.getElementById('payoutDateInput').value;
        const notes = document.getElementById('payoutNotesInput').value || '';

        if (!Number.isFinite(amount) || amount <= 0 || amount > payoutModalMaxAmount) {
            notify('error', '<?= lang('Finance.invalidAmount') ?>', `<?= lang('Finance.amountValidationMessage') ?> ${formatCurrency(payoutModalMaxAmount, payoutModalCurrency)}.`);
            return;
        }
        submitPayoutPayment(id, amount, method, liquidityAccount, paidDate, notes);
    });

    $('#payoutMethodInput').on('change', syncPayoutLiquidityField);
});

function submitPayoutPayment(id, amount, method, liquidityAccount, paidDate, notes) {
    const formData = new FormData();
    formData.append('amount', Number(amount).toFixed(2));
    formData.append('payment_method', method);
    if (method === 'check') {
        formData.append('liquidity_account', liquidityAccount);
    }
    formData.append('paid_date', paidDate);
    formData.append('notes', notes);

    fetch(`<?= base_url('finance/payouts') ?>/${id}/payments`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (!data.success) {
            throw new Error(data.message || '<?= lang('Finance.failedToRecordPayoutPayment') ?>');
        }
        closePayoutModal();
        payoutsTable.ajax.reload(null, false);
        notify('success', '<?= lang('Finance.paymentRecorded') ?>', data.message || '<?= lang('Finance.providerPayoutPaymentRecorded') ?>');
    })
    .catch(err => {
        notify('error', '<?= lang('Finance.paymentFailed') ?>', err.message || '<?= lang('Finance.unableToRecordPayment') ?>');
    });
}

function markPayoutPaid(id) {
    if (!confirm('<?= lang('Finance.confirmMarkProviderPayoutFullyPaid') ?>')) {
        return;
    }
    const formData = new FormData();
    formData.append('payment_method', 'cash');

    fetch(`<?= base_url('finance/payouts') ?>/${id}/mark-paid`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (!data.success) {
            throw new Error(data.message || '<?= lang('Finance.failedToMarkPayoutPaid') ?>');
        }
        payoutsTable.ajax.reload(null, false);
        notify('success', '<?= lang('Finance.payoutUpdated') ?>', data.message || '<?= lang('Finance.providerPayoutMarkedPaid') ?>');
    })
    .catch(err => {
        notify('error', '<?= lang('Finance.updateFailed') ?>', err.message || '<?= lang('Finance.unableToMarkPayoutPaid') ?>');
    });
}
</script>
<?= $this->endSection() ?>
