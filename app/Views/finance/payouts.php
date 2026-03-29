<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
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
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: white !important;
}
.dataTables_paginate .paginate_button.disabled {
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
}
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-green-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6 space-y-6">
        <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-hand-holding-dollar text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?= lang('Finance.providerPayouts') ?></h1>
                        <p class="text-gray-600"><?= lang('Finance.providerPayoutsHelp') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                        <i class="fas fa-chart-bar mr-2"></i><?= lang('Finance.reports') ?>
                    </a>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-4 py-2 bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-lg hover:bg-indigo-200 transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2"></i><?= lang('Finance.backToFinance') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-blue-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <i class="fas fa-percent text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 bg-gradient-to-r from-indigo-100 to-blue-100 px-3 py-1.5 rounded-full border border-indigo-200">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                            <span class="text-indigo-700 font-bold text-xs"><?= lang('Finance.split') ?></span>
                        </div>
                    </div>
                    <div class="space-y-2 flex-1 flex flex-col justify-end">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.providerShareTotal') ?></p>
                        <p class="text-3xl font-black text-indigo-900"><?= formatCurrency($stats['total_provider_share'] ?? 0) ?></p>
                        <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.accruedProviderEntitlement') ?></p>
                    </div>
                </div>
            </div>

            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-500/20 to-orange-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-rose-500/10 group-hover:shadow-rose-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-rose-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-rose-500 to-orange-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <i class="fas fa-triangle-exclamation text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 bg-gradient-to-r from-rose-100 to-orange-100 px-3 py-1.5 rounded-full border border-rose-200">
                            <div class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></div>
                            <span class="text-rose-700 font-bold text-xs"><?= lang('Finance.due') ?></span>
                        </div>
                    </div>
                    <div class="space-y-2 flex-1 flex flex-col justify-end">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.outstandingPayable') ?></p>
                        <p class="text-3xl font-black text-rose-900"><?= formatCurrency($stats['total_provider_balance'] ?? 0) ?></p>
                        <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.pendingProviderPayouts') ?></p>
                    </div>
                </div>
            </div>

            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <i class="fas fa-building text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-green-100 px-3 py-1.5 rounded-full border border-emerald-200">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-emerald-700 font-bold text-xs"><?= lang('Finance.net') ?></span>
                        </div>
                    </div>
                    <div class="space-y-2 flex-1 flex flex-col justify-end">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.clinicShareTotal') ?></p>
                        <p class="text-3xl font-black text-emerald-900"><?= formatCurrency($stats['total_clinic_share'] ?? 0) ?></p>
                        <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.retainedByClinic') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Finance.payoutLedger') ?></h3>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400">
                            <option value=""><?= lang('Finance.allStatuses') ?></option>
                            <option value="unpaid"><?= lang('Finance.unpaid') ?></option>
                            <option value="partial"><?= lang('Finance.partial') ?></option>
                            <option value="paid"><?= lang('Finance.paid') ?></option>
                            <option value="void"><?= lang('Finance.void') ?></option>
                        </select>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 text-sm"><i class="fas fa-search"></i></span>
                            <input id="payoutSearch" type="text" class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-72 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400" placeholder="<?= esc(lang('Finance.searchProviderPatientTxn')) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="providerPayoutsTable" class="w-full min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-receipt text-indigo-600 mr-2"></i><?= lang('Finance.transaction') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-user text-indigo-600 mr-2"></i><?= lang('Finance.patient') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-user-doctor text-indigo-600 mr-2"></i><?= lang('Finance.provider') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-money-bill-trend-up text-indigo-600 mr-2"></i><?= lang('Finance.split') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-wallet text-indigo-600 mr-2"></i><?= lang('Finance.paidBalance') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-info-circle text-indigo-600 mr-2"></i><?= lang('Finance.status') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-calendar text-indigo-600 mr-2"></i><?= lang('Finance.date') ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><i class="fas fa-cogs text-indigo-600 mr-2"></i><?= lang('Finance.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody id="providerPayoutsTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="recordPayoutModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closePayoutModal()"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h4 class="text-base font-semibold text-gray-900"><?= lang('Finance.recordProviderPayout') ?></h4>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closePayoutModal()"><i class="fas fa-times"></i></button>
            </div>
            <form id="recordPayoutForm" class="p-5 space-y-4">
                <input type="hidden" id="payoutIdInput">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"><?= lang('Finance.amount') ?></label>
                    <input type="number" step="0.01" min="0.01" id="payoutAmountInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400" required>
                    <p id="payoutAmountHelp" class="mt-1 text-xs text-gray-500"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"><?= lang('Finance.paymentMethod') ?></label>
                    <select id="payoutMethodInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400">
                        <option value="cash"><?= lang('Finance.cash') ?></option>
                        <option value="card"><?= lang('Finance.card') ?></option>
                        <option value="bank_transfer"><?= lang('Finance.bankTransfer') ?></option>
                        <option value="check"><?= lang('Finance.check') ?></option>
                        <option value="other"><?= lang('Finance.other') ?></option>
                    </select>
                </div>
                <div id="payoutLiquidityAccountField" class="hidden">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"><?= lang('Finance.paidFromAccount') ?></label>
                    <select id="payoutLiquidityAccountInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400">
                        <option value="bank"><?= esc(lang('Finance.liquidityAccount.bank')) ?></option>
                        <option value="undeposited_checks"><?= esc(lang('Finance.liquidityAccount.undeposited_checks')) ?></option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500"><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"><?= lang('Finance.paid') ?> <?= lang('Finance.date') ?></label>
                    <input type="date" id="payoutDateInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"><?= lang('Finance.notes') ?></label>
                    <textarea id="payoutNotesInput" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400" placeholder="<?= esc(lang('Finance.optional')) ?>"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closePayoutModal()" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"><?= lang('Finance.cancel') ?></button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"><?= lang('Finance.recordPayment') ?></button>
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
                    return `
                        <a href="<?= base_url('finance') ?>/${row.finance_id}" class="block rounded-md hover:bg-indigo-50/70 transition-colors duration-200 p-1 -m-1">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-receipt text-indigo-600 text-sm"></i>
                                </div>
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
                render: function (data) {
                    const patient = data && data.trim() !== '' ? data : '<?= esc(lang('Finance.notAvailable')) ?>';
                    return `<div class="text-sm font-medium text-gray-900">${patient}</div>`;
                }
            },
            {
                data: 'provider_name',
                render: function (data, type, row) {
                    const isGuest = (row.provider_type || '') === 'guest';
                    const iconClass = isGuest ? 'fas fa-user-tie text-amber-600' : 'fas fa-user-doctor text-indigo-600';
                    const subtitle = isGuest ? '<?= esc(lang('Finance.guestProvider')) ?>' : '<?= esc(lang('Finance.clinicUser')) ?>';
                    return `
                        <div class="flex items-center space-x-3">
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
                            <div class="flex items-center space-x-2 text-sm font-medium text-indigo-700">
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
                            <div class="flex items-center space-x-2 text-sm font-medium text-emerald-700">
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
                        <div class="flex items-center space-x-2">
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
                    let actions = '<div class="flex items-center space-x-2">';
                    actions += `<a href="<?= base_url('finance') ?>/${row.finance_id}" class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="<?= lang('Finance.viewTransaction') ?>"><i class="fas fa-eye"></i></a>`;
                    if (canSettle) {
                        actions += `<button type="button" onclick="openPayoutModal(${id}, ${Number(row.provider_balance_due || 0)})" class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="<?= lang('Finance.recordPayment') ?>"><i class="fas fa-money-bill-wave"></i></button>`;
                        actions += `<button type="button" onclick="markPayoutPaid(${id})" class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="<?= lang('Finance.markFullyPaid') ?>"><i class="fas fa-check-double"></i></button>`;
                    }
                    if (!canSettle) {
                        actions += '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-500"><?= lang('Finance.noActions') ?></span>';
                    }
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        dom: 'rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
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
