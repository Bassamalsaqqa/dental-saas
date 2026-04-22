<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Finance.' . $key, $params);
$isRtl = in_array(service('request')->getLocale(), ['ar', 'he', 'fa', 'ur'], true);
$rows = $rows ?? [];
$summary = $summary ?? [];
$agingSummary = $aging_summary ?? [];
$liquidityOptions = $liquidity_account_options ?? [];
$returnTo = urlencode('finance/payables');
$formatMoney = static fn($amount) => formatCurrency((float) $amount);
$bucketCards = [
    'current' => ['label' => $tr('currentBucket'), 'class' => 'from-emerald-50 to-green-100 text-emerald-800 border-emerald-200'],
    'days_1_30' => ['label' => $tr('days1To30'), 'class' => 'from-amber-50 to-yellow-100 text-amber-800 border-amber-200'],
    'days_31_60' => ['label' => $tr('days31To60'), 'class' => 'from-orange-50 to-amber-100 text-orange-800 border-orange-200'],
    'days_61_90' => ['label' => $tr('days61To90'), 'class' => 'from-rose-50 to-red-100 text-rose-800 border-rose-200'],
    'days_91_plus' => ['label' => $tr('days91Plus'), 'class' => 'from-slate-100 to-gray-200 text-slate-800 border-slate-300'],
];
$statusClass = static function (string $status): string {
    return match ($status) {
        'paid' => 'bg-emerald-100 text-emerald-700',
        'partial' => 'bg-blue-100 text-blue-700',
        'overdue' => 'bg-rose-100 text-rose-700',
        'pending' => 'bg-amber-100 text-amber-700',
        default => 'bg-gray-100 text-gray-700',
    };
};
?>

<div class="min-h-screen bg-slate-50" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="container mx-auto px-3 py-5 sm:px-4 sm:py-6">
        <div class="mb-5 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-100 via-orange-100 to-slate-100 p-5 shadow-xl shadow-amber-900/10 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-600 text-white shadow-lg shadow-amber-700/20">
                        <i class="fas fa-file-invoice-dollar text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-amber-800"><?= esc($tr('accountsPayableAging')) ?></p>
                        <h1 class="text-2xl font-black text-slate-950 sm:text-3xl"><?= esc($tr('supplierPayables')) ?></h1>
                        <p class="mt-1 max-w-3xl text-sm font-medium text-slate-700"><?= esc($tr('supplierPayablesHelp')) ?></p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    <?php if (has_permission('finance', 'create')): ?>
                        <a href="<?= base_url('finance/expenses/create?return_to=' . $returnTo) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:from-amber-600 hover:to-orange-700">
                            <i class="fas fa-plus"></i>
                            <?= esc($tr('newSupplierExpense')) ?>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($can_access_finance_reports)): ?>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/80 px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-white">
                            <i class="fas fa-chart-simple"></i>
                            <?= esc($tr('reports')) ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-slate-800">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                        <?= esc($tr('financeManagement')) ?>
                    </a>
                </div>
            </div>
        </div>

        <form method="get" action="<?= base_url('finance/payables') ?>" class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(180px,240px)_auto] sm:items-end">
                <div>
                    <label for="asOfDate" class="mb-2 block text-sm font-bold text-slate-700"><?= esc($tr('asOfDate')) ?></label>
                    <input id="asOfDate" type="date" name="as_of" value="<?= esc($as_of_date ?? date('Y-m-d')) ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800">
                    <i class="fas fa-filter"></i>
                    <?= esc($tr('filter')) ?>
                </button>
            </div>
        </form>

        <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-amber-200 bg-amber-100 p-5 shadow-md shadow-amber-900/10">
                <p class="text-xs font-bold uppercase tracking-wide text-amber-800"><?= esc($tr('openBalance')) ?></p>
                <p class="mt-2 text-2xl font-black text-slate-950"><?= $formatMoney($summary['open_balance'] ?? 0) ?></p>
            </div>
            <div class="rounded-2xl border border-sky-200 bg-sky-100 p-5 shadow-md shadow-sky-900/10">
                <p class="text-xs font-bold uppercase tracking-wide text-sky-800"><?= esc($tr('openBills')) ?></p>
                <p class="mt-2 text-2xl font-black text-slate-950"><?= number_format((int) ($summary['open_count'] ?? 0)) ?></p>
            </div>
            <div class="rounded-2xl border border-rose-200 bg-rose-100 p-5 shadow-md shadow-rose-900/10">
                <p class="text-xs font-bold uppercase tracking-wide text-rose-800"><?= esc($tr('overdueBills')) ?></p>
                <p class="mt-2 text-2xl font-black text-slate-950"><?= number_format((int) ($summary['overdue_count'] ?? 0)) ?></p>
            </div>
            <div class="rounded-2xl border border-slate-300 bg-slate-200 p-5 shadow-md shadow-slate-900/10">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-700"><?= esc($tr('overdueBalance')) ?></p>
                <p class="mt-2 text-2xl font-black text-slate-950"><?= $formatMoney($summary['overdue_balance'] ?? 0) ?></p>
            </div>
        </div>

        <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-5">
            <?php foreach ($bucketCards as $bucket => $card): ?>
                <div class="rounded-2xl border bg-gradient-to-br p-4 shadow-sm <?= esc($card['class']) ?>">
                    <p class="text-xs font-black uppercase tracking-wide"><?= esc($card['label']) ?></p>
                    <p class="mt-2 text-lg font-black"><?= $formatMoney($agingSummary[$bucket] ?? 0) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
            <div class="flex flex-col gap-2 bg-slate-900 px-4 py-4 text-white sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black"><?= esc($tr('apAgingDetail')) ?></h2>
                    <p class="text-sm text-slate-300"><?= esc(lang('Finance.accountsPayableAgingHelp', ['date' => $as_of_date ?? date('Y-m-d')])) ?></p>
                </div>
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white">
                    <i class="fas fa-layer-group"></i>
                    <?= esc(lang('Finance.openPayablesTop', ['count' => count($rows)])) ?>
                </span>
            </div>

            <?php if (empty($rows)): ?>
                <div class="p-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                        <i class="fas fa-check text-2xl"></i>
                    </div>
                    <h3 class="mt-4 text-xl font-black text-slate-950"><?= esc($tr('noSupplierPayables')) ?></h3>
                    <p class="mt-2 text-sm font-medium text-slate-600"><?= esc($tr('noSupplierPayablesHelp')) ?></p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-start"><?= esc($tr('supplierOrVendor')) ?></th>
                                <th class="px-4 py-3 text-start"><?= esc($tr('transaction')) ?></th>
                                <th class="px-4 py-3 text-start"><?= esc($tr('expenses')) ?></th>
                                <th class="px-4 py-3 text-start"><?= esc($tr('dueDate')) ?></th>
                                <th class="px-4 py-3 text-end"><?= esc($tr('total')) ?></th>
                                <th class="px-4 py-3 text-end"><?= esc($tr('balanceDue')) ?></th>
                                <th class="px-4 py-3 text-start"><?= esc($tr('payableAge')) ?></th>
                                <th class="px-4 py-3 text-start"><?= esc($tr('status')) ?></th>
                                <th class="px-4 py-3 text-end"><?= esc($tr('actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach ($rows as $row): ?>
                                <?php
                                $financeId = (int) ($row['id'] ?? 0);
                                $supplierName = trim((string) ($row['supplier_name'] ?? ''));
                                $vendorName = $supplierName !== '' ? $supplierName : trim((string) ($row['counterparty_name'] ?? ''));
                                $vendorName = $vendorName !== '' ? $vendorName : $tr('unknown');
                                $status = (string) ($row['payment_status'] ?? 'pending');
                                $balance = (float) ($row['balance_due'] ?? 0);
                                $dueDate = !empty($row['due_date']) ? (string) $row['due_date'] : (string) ($row['transaction_date'] ?? '');
                                $age = (int) ($row['days_past_due'] ?? 0);
                                ?>
                                <tr class="group cursor-pointer transition hover:bg-amber-50/60" data-row-url="<?= base_url('finance/' . $financeId . '?return_to=' . $returnTo) ?>">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                                <i class="fas fa-building text-xs"></i>
                                            </span>
                                            <span class="max-w-[12rem] truncate font-bold text-slate-900" title="<?= esc($vendorName) ?>"><?= esc($vendorName) ?></span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-700"><?= esc($row['transaction_id'] ?? ('#' . $financeId)) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600"><?= esc($row['expense_category'] ?? '-') ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700"><?= $dueDate !== '' ? esc(formatDate($dueDate)) : '-' ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-end font-semibold text-slate-700"><?= $formatMoney($row['total_amount'] ?? 0) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-end font-black text-rose-700"><?= $formatMoney($balance) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-black <?= $age > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                            <?= $age > 0 ? esc($age . ' ' . $tr('daysPastDue')) : esc($tr('currentBucket')) ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-black <?= esc($statusClass($status)) ?>">
                                            <?= esc($tr($status)) ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a data-row-action href="<?= base_url('finance/' . $financeId . '?return_to=' . $returnTo) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition hover:bg-slate-200" title="<?= esc($tr('viewTransaction')) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!empty($row['supplier_id'])): ?>
                                                <a data-row-action href="<?= base_url('inventory/suppliers/' . (int) $row['supplier_id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-700 transition hover:bg-amber-200" title="<?= esc($tr('supplierDirectory')) ?>">
                                                    <i class="fas fa-address-book"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (has_permission('finance', 'edit')): ?>
                                                <button data-row-action type="button" onclick="openPayablePaymentModal(<?= $financeId ?>, <?= esc(json_encode($balance)) ?>)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 transition hover:bg-emerald-200" title="<?= esc($tr('recordPayablePayment')) ?>">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </button>
                                                <button data-row-action type="button" onclick="markPayablePaid(<?= $financeId ?>)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700 transition hover:bg-blue-200" title="<?= esc($tr('markPayableAsPaid')) ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="payablePaymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="payablePaymentTitle" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/55 backdrop-blur-sm" data-payable-modal-close></div>
    <div class="relative flex min-h-screen items-center justify-center p-4" data-payable-modal-close>
        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl" data-payable-modal-panel>
            <div class="flex items-start justify-between gap-4 rounded-t-2xl bg-slate-900 px-5 py-4 text-white">
                <div>
                    <h3 id="payablePaymentTitle" class="text-lg font-black"><?= esc($tr('recordPayablePayment')) ?></h3>
                    <p id="payablePaymentHelp" class="mt-1 text-sm text-slate-300"></p>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white/20" onclick="closePayablePaymentModal()" aria-label="<?= esc($tr('cancel')) ?>">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <form id="payablePaymentForm" class="space-y-4 p-5">
                <input type="hidden" id="payablePaymentFinanceId">
                <div>
                    <label for="payablePaymentAmount" class="mb-2 block text-sm font-bold text-slate-700">
                        <?= esc($tr('paymentAmount')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                    </label>
                    <input id="payablePaymentAmount" type="number" step="0.01" min="0.01" required class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="payablePaymentMethod" class="mb-2 block text-sm font-bold text-slate-700">
                            <?= esc($tr('paymentMethodRequired')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                        </label>
                        <select id="payablePaymentMethod" data-force-select2 required class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                            <option value="cash"><?= esc($tr('cash')) ?></option>
                            <option value="card"><?= esc($tr('card')) ?></option>
                            <option value="bank_transfer"><?= esc($tr('bankTransfer')) ?></option>
                            <option value="check"><?= esc($tr('check')) ?></option>
                            <option value="other"><?= esc($tr('other')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="payablePaymentDate" class="mb-2 block text-sm font-bold text-slate-700">
                            <?= esc($tr('settlementDate')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                        </label>
                        <input id="payablePaymentDate" type="date" required class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                    </div>
                </div>
                <div id="payableLiquidityField" class="hidden">
                    <label for="payableLiquidityAccount" class="mb-2 block text-sm font-bold text-slate-700"><?= esc($tr('paidFromAccount')) ?></label>
                    <select id="payableLiquidityAccount" data-force-select2 class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                        <option value="bank"><?= esc($liquidityOptions['bank'] ?? $tr('liquidityAccount.bank')) ?></option>
                        <option value="undeposited_checks"><?= esc($liquidityOptions['undeposited_checks'] ?? $tr('liquidityAccount.undeposited_checks')) ?></option>
                    </select>
                </div>
                <div>
                    <label for="payablePaymentNotes" class="mb-2 block text-sm font-bold text-slate-700"><?= esc($tr('notes')) ?></label>
                    <textarea id="payablePaymentNotes" rows="3" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15" placeholder="<?= esc($tr('optionalNotesPlaceholder')) ?>"></textarea>
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closePayablePaymentModal()" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200"><?= esc($tr('cancel')) ?></button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-3 text-sm font-bold text-white transition hover:from-amber-600 hover:to-orange-700">
                        <i class="fas fa-save"></i>
                        <?= esc($tr('postSettlement')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let payablePaymentMaxAmount = 0;
const payableCurrencySymbol = <?= json_encode($currency_symbol ?? '') ?>;
const payableCurrencyPosition = <?= json_encode($currency_position ?? 'before') ?>;

function payableFormatMoney(value) {
    const amount = Number(value || 0);
    const formatted = amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return payableCurrencyPosition === 'after' ? `${formatted} ${payableCurrencySymbol}` : `${payableCurrencySymbol}${formatted}`;
}

function notifyPayable(type, message) {
    if (typeof window.showToast === 'function') {
        window.showToast(type, type === 'success' ? '<?= esc($tr('success')) ?>' : '<?= esc($tr('error')) ?>', message);
        return;
    }
    alert(message);
}

function syncPayableLiquidityField() {
    const method = document.getElementById('payablePaymentMethod')?.value || '';
    document.getElementById('payableLiquidityField')?.classList.toggle('hidden', method !== 'check');
}

function closePayablePaymentModal() {
    document.getElementById('payablePaymentModal')?.classList.add('hidden');
    document.getElementById('payablePaymentForm')?.reset();
}

function openPayablePaymentModal(id, maxAmount) {
    payablePaymentMaxAmount = Number(maxAmount || 0);
    document.getElementById('payablePaymentFinanceId').value = String(id);
    document.getElementById('payablePaymentAmount').value = payablePaymentMaxAmount > 0 ? payablePaymentMaxAmount.toFixed(2) : '';
    document.getElementById('payablePaymentDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('payablePaymentHelp').textContent = '<?= esc($tr('maximumPayable')) ?>: ' + payableFormatMoney(payablePaymentMaxAmount);
    syncPayableLiquidityField();
    document.getElementById('payablePaymentModal')?.classList.remove('hidden');
}

async function markPayablePaid(financeId) {
    const formData = new FormData();
    formData.append(window.csrfConfig.name, window.getCsrfToken());

    const response = await fetch(`<?= base_url('finance') ?>/${financeId}/mark-paid`, {
        method: 'POST',
        headers: {
            [window.csrfConfig.header]: window.getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
    const result = await response.json();
    if (result.csrf_token && window.refreshCsrfToken) {
        window.refreshCsrfToken(result.csrf_token);
    }
    notifyPayable(result.success ? 'success' : 'error', result.message || 'Request failed.');
    if (result.success) {
        window.location.reload();
    }
}

document.querySelectorAll('[data-row-url]').forEach((row) => {
    row.addEventListener('click', (event) => {
        if (event.target.closest('[data-row-action]')) {
            return;
        }
        window.location.href = row.dataset.rowUrl;
    });
});

document.querySelectorAll('[data-payable-modal-close]').forEach((element) => {
    element.addEventListener('click', (event) => {
        if (event.target.closest('[data-payable-modal-panel]')) {
            return;
        }
        closePayablePaymentModal();
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !document.getElementById('payablePaymentModal')?.classList.contains('hidden')) {
        closePayablePaymentModal();
    }
});

document.getElementById('payablePaymentMethod')?.addEventListener('change', syncPayableLiquidityField);

document.getElementById('payablePaymentForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const financeId = document.getElementById('payablePaymentFinanceId').value;
    const amount = parseFloat(document.getElementById('payablePaymentAmount').value || '0');
    if (!amount || amount <= 0 || amount > payablePaymentMaxAmount + 0.0001) {
        notifyPayable('error', '<?= esc($tr('invalidPaymentAmount')) ?>');
        return;
    }

    const formData = new FormData();
    formData.append(window.csrfConfig.name, window.getCsrfToken());
    formData.append('amount', amount.toFixed(2));
    formData.append('payment_method', document.getElementById('payablePaymentMethod').value);
    formData.append('liquidity_account', document.getElementById('payableLiquidityAccount').value);
    formData.append('payment_date', document.getElementById('payablePaymentDate').value);
    formData.append('notes', document.getElementById('payablePaymentNotes').value);

    const response = await fetch(`<?= base_url('finance') ?>/${financeId}/payments`, {
        method: 'POST',
        headers: {
            [window.csrfConfig.header]: window.getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
    const result = await response.json();
    if (result.csrf_token && window.refreshCsrfToken) {
        window.refreshCsrfToken(result.csrf_token);
    }
    notifyPayable(result.success ? 'success' : 'error', result.message || 'Request failed.');
    if (result.success) {
        window.location.reload();
    }
});
</script>

<?= $this->endSection() ?>
