<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<?php $isRtl = !empty($is_rtl); ?>

<style>
.finance-payouts-wrap {
    width: min(100%, 1500px);
}

.finance-payouts-hero {
    border-radius: 1.75rem;
    background:
        radial-gradient(circle at 10% 8%, rgba(255, 255, 255, 0.22), transparent 30%),
        radial-gradient(circle at 90% 0%, rgba(99, 102, 241, 0.35), transparent 34%),
        linear-gradient(135deg, #4338ca 0%, #1d4ed8 42%, #0f172a 100%);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
}

[dir="rtl"] .finance-payouts-hero {
    background:
        radial-gradient(circle at 90% 8%, rgba(255, 255, 255, 0.22), transparent 30%),
        radial-gradient(circle at 10% 0%, rgba(99, 102, 241, 0.35), transparent 34%),
        linear-gradient(225deg, #4338ca 0%, #1d4ed8 42%, #0f172a 100%);
}

.finance-payouts-card,
.finance-payouts-panel {
    position: relative;
    isolation: isolate;
    overflow: hidden;
}

.finance-payouts-card {
    border-radius: 1.5rem;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.finance-payouts-card::after {
    content: "";
    position: absolute;
    inset: auto -18% -46% auto;
    width: 72%;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.15);
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
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
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
    transition: border-color 160ms ease, box-shadow 160ms ease;
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
    <div class="finance-payouts-wrap mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="finance-payouts-hero overflow-hidden border border-white/20 p-5 text-white sm:p-6 lg:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg shadow-black/10 ring-1 ring-white/20">
                        <i class="fas fa-hand-holding-dollar text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white sm:text-3xl"><?= lang('Finance.providerPayouts') ?></h1>
                        <p class="mt-2 max-w-3xl text-sm font-semibold text-indigo-50/90"><?= lang('Finance.providerPayoutsHelp') ?></p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    <?php if (!empty($can_access_finance_reports)): ?>
                    <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-chart-bar <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.reports') ?>
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right ml-2' : 'fa-arrow-left mr-2' ?>"></i><?= lang('Finance.backToFinance') ?>
                    </a>
                </div>
            </div>
        </section>

        <section class="finance-payouts-panel border-amber-100 bg-gradient-to-br from-white via-amber-50/90 to-white px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                        <i class="fas fa-circle-info text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-950"><?= lang('Finance.providerPayouts') ?></p>
                        <p class="mt-1 text-sm font-semibold text-slate-600"><?= lang('Finance.providerPayoutsGuidance') ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="finance-payouts-card bg-gradient-to-br from-indigo-600 to-blue-900 p-5 text-white shadow-indigo-500/25">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-indigo-50/90"><?= lang('Finance.providerShareTotal') ?></p>
                        <p class="mt-3 text-3xl font-black"><?= formatCurrency($stats['total_provider_share'] ?? 0) ?></p>
                        <p class="mt-2 text-xs font-bold text-indigo-50/80"><?= lang('Finance.accruedProviderEntitlement') ?></p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20">
                        <i class="fas fa-percent text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="finance-payouts-card bg-gradient-to-br from-rose-600 to-pink-800 p-5 text-white shadow-rose-500/25">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-rose-50/90"><?= lang('Finance.outstandingPayable') ?></p>
                        <p class="mt-3 text-3xl font-black"><?= formatCurrency($stats['total_provider_balance'] ?? 0) ?></p>
                        <p class="mt-2 text-xs font-bold text-rose-50/80"><?= lang('Finance.pendingProviderPayouts') ?></p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20">
                        <i class="fas fa-triangle-exclamation text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="finance-payouts-card bg-gradient-to-br from-emerald-600 to-teal-800 p-5 text-white shadow-emerald-500/25">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-emerald-50/90"><?= lang('Finance.clinicShareTotal') ?></p>
                        <p class="mt-3 text-3xl font-black"><?= formatCurrency($stats['total_clinic_share'] ?? 0) ?></p>
                        <p class="mt-2 text-xs font-bold text-emerald-50/80"><?= lang('Finance.retainedByClinic') ?></p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20">
                        <i class="fas fa-building text-lg"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="finance-payouts-panel">
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-800 to-slate-950 px-6 py-5 text-white">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-2.5 w-2.5 rounded-full bg-indigo-400 shadow-lg shadow-indigo-300/40"></div>
                        <h3 class="text-lg font-black"><?= lang('Finance.payoutLedger') ?></h3>
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
                    <thead class="bg-slate-100/95 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-receipt text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.transaction') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-user text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.patient') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-user-doctor text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.provider') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-money-bill-trend-up text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.split') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-wallet text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.paidBalance') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-info-circle text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.status') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-calendar text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.date') ?></th>
                            <th class="px-6 py-4 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><i class="fas fa-cogs text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody id="providerPayoutsTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="recordPayoutModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" onclick="closePayoutModal()"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl border border-gray-200">
            <div class="flex items-center justify-between bg-gradient-to-r from-slate-900 to-indigo-900 px-5 py-4 text-white">
                <h4 class="text-base font-black"><?= lang('Finance.recordProviderPayout') ?></h4>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20" onclick="closePayoutModal()"><i class="fas fa-times"></i></button>
            </div>
            <form id="recordPayoutForm" class="p-5 space-y-4">
                <input type="hidden" id="payoutIdInput">
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.amount') ?></label>
                    <input type="number" step="0.01" min="0.01" id="payoutAmountInput" class="finance-payouts-input" required>
                    <p id="payoutAmountHelp" class="mt-1 text-xs text-gray-500"></p>
                </div>
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
                <div id="payoutLiquidityAccountField" class="hidden">
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.paidFromAccount') ?></label>
                    <select id="payoutLiquidityAccountInput" class="finance-payouts-input">
                        <option value="bank"><?= esc(lang('Finance.liquidityAccount.bank')) ?></option>
                        <option value="undeposited_checks"><?= esc(lang('Finance.liquidityAccount.undeposited_checks')) ?></option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500"><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.paid') ?> <?= lang('Finance.date') ?></label>
                    <input type="date" id="payoutDateInput" class="finance-payouts-input" required>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wide mb-1"><?= lang('Finance.notes') ?></label>
                    <textarea id="payoutNotesInput" rows="2" class="finance-payouts-input" placeholder="<?= esc(lang('Finance.optional')) ?>"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closePayoutModal()" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"><?= lang('Finance.cancel') ?></button>
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-blue-800 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-indigo-500/20 transition hover:from-indigo-700 hover:to-blue-900"><?= lang('Finance.recordPayment') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
let payoutsTable = null;
let payoutModalMaxAmount = 0;
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

function formatCurrency(value) {
    const amount = Number(value || 0);
    const normalized = Number.isFinite(amount) ? amount : 0;
    const formatted = normalized.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
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
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${config.cls}"><i class="${config.icon} mr-1"></i>${label}</span>`;
}

function closePayoutModal() {
    document.getElementById('recordPayoutModal').classList.add('hidden');
    document.getElementById('recordPayoutForm').reset();
}

function openPayoutModal(id, maxAmount) {
    payoutModalMaxAmount = Number(maxAmount || 0);
    document.getElementById('payoutIdInput').value = String(id);
    document.getElementById('payoutAmountInput').value = payoutModalMaxAmount > 0 ? payoutModalMaxAmount.toFixed(2) : '';
    document.getElementById('payoutDateInput').value = new Date().toISOString().split('T')[0];
    document.getElementById('payoutAmountHelp').textContent = `<?= lang('Finance.maximumPayable') ?>: ${formatCurrency(payoutModalMaxAmount)}`;
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
                        <a href="<?= base_url('finance') ?>/${row.finance_id}" class="block rounded-md hover:bg-indigo-50/70 transition-colors duration-200 p-1 -m-1">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                    <i class="${typeMeta.icon} ${typeMeta.iconColor} text-xl"></i>
                                </span>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">${data || '-'}</div>
                                    <div class="text-xs text-gray-500"><?= lang('Finance.financeTransaction') ?></div>
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
                        <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                            <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">${patient}</div>
                            <div class="text-xs text-gray-500"><?= esc(lang('Finance.patient')) ?></div>
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
                            <div class="w-10 h-10 ${isGuest ? 'bg-amber-100' : 'bg-indigo-100'} rounded-lg flex items-center justify-center">
                                <i class="${iconClass} text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data || '<?= lang('Finance.provider') ?>'}</div>
                                <div class="text-xs text-gray-500">${subtitle}</div>
                            </div>
                        </div>`;
                }
            },
            {
                data: 'provider_share_amount',
                render: function (value, type, row) {
                    return `
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm font-medium text-indigo-700">
                                <i class="fas fa-arrow-down text-rose-500 text-xs"></i>
                                <span>${formatCurrency(value)}</span>
                            </div>
                            <div class="text-xs text-gray-500"><?= lang('Finance.of') ?> ${formatCurrency(row.gross_amount || 0)}</div>
                        </div>`;
                }
            },
            {
                data: 'provider_paid_amount',
                render: function (value, type, row) {
                    return `
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm font-medium text-emerald-700">
                                <i class="fas fa-arrow-up text-emerald-500 text-xs"></i>
                                <span>${formatCurrency(value)}</span>
                            </div>
                            <div class="text-xs ${Number(row.provider_balance_due || 0) > 0 ? 'text-rose-600' : 'text-emerald-600'}">
                                <?= lang('Finance.balance') ?>: ${formatCurrency(row.provider_balance_due || 0)}
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
                            <i class="fas fa-calendar text-indigo-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${value || '-'}</span>
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
                    actions += `<a href="<?= base_url('finance') ?>/${row.finance_id}" class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="<?= lang('Finance.viewTransaction') ?>"><i class="fas fa-eye"></i></a>`;
                    if (canSettle) {
                        actions += `<button type="button" onclick="openPayoutModal(${id}, ${Number(row.provider_balance_due || 0)})" class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="<?= lang('Finance.recordPaymentFromLedger') ?>"><i class="fas fa-money-bill-wave"></i></button>`;
                        actions += `<button type="button" onclick="markPayoutPaid(${id})" class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="<?= lang('Finance.markFullyPaid') ?>"><i class="fas fa-check-double"></i></button>`;
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
            $(row).addClass('hover:bg-indigo-50/30 transition-colors duration-150');
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
            notify('error', '<?= lang('Finance.invalidAmount') ?>', `<?= lang('Finance.amountValidationMessage') ?> ${formatCurrency(payoutModalMaxAmount)}.`);
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
