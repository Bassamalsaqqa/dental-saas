<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$currencySymbol = $currency_symbol ?? settings()->getCurrencySymbol();
$currencyPosition = $currency_position ?? settings()->get('currency_position', 'before');
$isRtl = !empty($is_rtl);
$formatMoney = static function (float $amount) use ($currencySymbol, $currencyPosition): string {
    $formatted = number_format($amount, 2);
    return $currencyPosition === 'after' ? $formatted . ' ' . $currencySymbol : $currencySymbol . $formatted;
};
$summary = $summary ?? [];
$paymentRows = $payment_rows ?? [];
$recentPaymentRows = array_slice($paymentRows, 0, 5);
?>

<div class="min-h-screen bg-slate-50/80" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-900 via-indigo-900 to-blue-700 text-white shadow-xl">
                <div class="flex flex-col gap-6 p-6 sm:p-8 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                            <span><?= esc($tr('supplierStatement')) ?></span>
                            <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                            <span><?= esc($supplier['name'] ?? '') ?></span>
                        </div>
                        <div class="mt-5 flex items-start gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white/14 text-3xl text-white shadow-lg ring-1 ring-white/20">
                                <i class="fas fa-truck-field"></i>
                            </div>
                            <div class="min-w-0">
                                <h1 class="truncate text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($supplier['name'] ?? '') ?></h1>
                                <p class="mt-2 max-w-2xl text-sm text-blue-100/90"><?= esc($tr('supplierPayablesSubtitle')) ?></p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <?php if (!empty($supplier['contact_person'])): ?>
                                        <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/95">
                                            <i class="fas fa-user-tie <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc($supplier['contact_person']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($supplier['phone'])): ?>
                                        <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/95">
                                            <i class="fas fa-phone <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc($supplier['phone']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($supplier['email'])): ?>
                                        <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/95">
                                            <i class="fas fa-envelope <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc($supplier['email']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid w-full gap-2 sm:grid-cols-2 xl:w-[20rem]">
                        <a href="<?= base_url('finance/expenses/create?supplier_id=' . (int) ($supplier['id'] ?? 0)) ?>" class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                            <i class="fas fa-plus-circle <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.newExpense')) ?>
                        </a>
                        <a href="<?= base_url('providers') ?>" class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                            <i class="fas <?= $isRtl ? 'fa-arrow-right ml-2' : 'fa-arrow-left mr-2' ?>"></i><?= esc($tr('goToSupplierDirectory')) ?>
                        </a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><?= esc($tr('totalBills')) ?></p>
                    <p class="mt-3 text-3xl font-bold text-slate-900"><?= esc($formatMoney((float) ($summary['total_bills'] ?? 0))) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><?= esc($tr('totalPaid')) ?></p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700"><?= esc($formatMoney((float) ($summary['total_paid'] ?? 0))) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><?= esc($tr('payableBalance')) ?></p>
                    <p class="mt-3 text-3xl font-bold text-rose-700"><?= esc($formatMoney((float) ($summary['open_payables'] ?? 0))) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><?= esc($tr('linkedExpenses')) ?></p>
                    <p class="mt-3 text-3xl font-bold text-slate-900"><?= esc((string) ($summary['transaction_count'] ?? 0)) ?></p>
                    <p class="mt-2 text-xs text-slate-500"><?= esc($tr('lastPaymentDate')) ?>: <?= esc(!empty($summary['last_payment_date']) ? $summary['last_payment_date'] : lang('Finance.notAvailable')) ?></p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.75fr)]">
                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('Finance.paymentHistory')) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= esc(lang('Finance.recordExpensePayment')) ?></p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                <i class="fas fa-wallet <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc((string) ($summary['payment_count'] ?? 0)) ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($recentPaymentRows)): ?>
                        <div class="space-y-3 px-6 py-5">
                            <?php foreach ($recentPaymentRows as $row): ?>
                                <a href="<?= base_url('finance/' . (int) ($row['finance_id'] ?? 0)) ?>" class="flex items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-slate-50 via-emerald-50/60 to-white px-4 py-4 transition hover:from-slate-100 hover:via-emerald-50 hover:to-white">
                                    <div class="min-w-0 flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-slate-900"><?= esc((string) ($row['transaction_id'] ?? '-')) ?></p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                <?= esc(lang('Finance.' . ((string) ($row['payment_method'] ?? 'cash')))) ?>
                                                <span class="text-slate-300"><?= esc('•') ?></span>
                                                <?= esc(!empty($row['payment_date']) ? (string) $row['payment_date'] : lang('Finance.notAvailable')) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 text-end">
                                        <p class="text-sm font-bold text-emerald-700"><?= esc($formatMoney((float) ($row['amount'] ?? 0))) ?></p>
                                        <p class="mt-1 max-w-[16rem] truncate text-xs text-slate-500"><?= esc((string) (($row['notes'] ?? '') !== '' ? $row['notes'] : lang('Finance.optional'))) ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="px-6 py-12 text-center text-slate-500"><?= esc(lang('Finance.noPaymentsYet')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h2 class="text-lg font-bold text-slate-900"><?= esc($tr('supplierPayablesSubtitle')) ?></h2>
                        <p class="mt-1 text-sm text-slate-500"><?= esc($tr('linkedFinanceRecordsHelp')) ?></p>
                    </div>
                    <div class="space-y-3 px-6 py-5">
                        <div class="rounded-2xl bg-gradient-to-r from-slate-50 to-white px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><?= esc($tr('totalPaid')) ?></p>
                            <p class="mt-2 text-xl font-bold text-emerald-700"><?= esc($formatMoney((float) ($summary['total_paid'] ?? 0))) ?></p>
                        </div>
                        <div class="rounded-2xl bg-gradient-to-r from-rose-50 to-white px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><?= esc($tr('payableBalance')) ?></p>
                            <p class="mt-2 text-xl font-bold text-rose-700"><?= esc($formatMoney((float) ($summary['open_payables'] ?? 0))) ?></p>
                        </div>
                        <div class="rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><?= esc(lang('Finance.paymentHistory')) ?></p>
                            <p class="mt-2 text-xl font-bold text-slate-900"><?= esc((string) ($summary['payment_count'] ?? 0)) ?></p>
                            <p class="mt-1 text-xs text-slate-500"><?= esc($tr('lastPaymentDate')) ?>: <?= esc(!empty($summary['last_payment_date']) ? $summary['last_payment_date'] : lang('Finance.notAvailable')) ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-900"><?= esc($tr('supplierStatement')) ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?= esc($tr('linkedFinanceRecordsHelp')) ?></p>
                </div>

                <?php if (!empty($expenses)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc(lang('Finance.transaction')) ?></th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc(lang('Finance.date')) ?></th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc(lang('Inventory.category')) ?></th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc(lang('Finance.total')) ?></th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc($tr('totalPaid')) ?></th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc($tr('payableBalance')) ?></th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc(lang('Finance.status')) ?></th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-600"><?= esc(lang('Finance.actions')) ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <?php foreach ($expenses as $row): ?>
                                    <?php
                                    $status = (string) ($row['payment_status'] ?? 'pending');
                                    $statusClass = match ($status) {
                                        'paid' => 'bg-emerald-100 text-emerald-700',
                                        'partial' => 'bg-amber-100 text-amber-700',
                                        'overdue' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                    ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-6 py-4">
                                            <a href="<?= base_url('finance/' . (int) $row['id']) ?>" class="block rounded-md p-1 -m-1 transition-colors hover:bg-indigo-50/70">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex h-7 w-7 items-center justify-center flex-shrink-0">
                                                        <i class="fas fa-receipt text-xl text-orange-700"></i>
                                                    </span>
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900"><?= esc($row['transaction_id'] ?? '-') ?></div>
                                                        <div class="text-xs text-slate-500"><?= esc(lang('Finance.expenseTransaction')) ?></div>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?= esc((string) ($row['transaction_date'] ?? $row['paid_date'] ?? '')) ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?= esc(lang('Finance.expenseCategory.' . ($row['expense_category'] ?? 'miscellaneous'))) ?></td>
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900"><?= esc($formatMoney((float) ($row['amount'] ?? 0))) ?></td>
                                        <td class="px-6 py-4 text-sm font-semibold text-emerald-700"><?= esc($formatMoney((float) ($row['settled_total'] ?? 0))) ?></td>
                                        <td class="px-6 py-4 text-sm font-semibold text-rose-700"><?= esc($formatMoney((float) ($row['open_balance'] ?? 0))) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?= esc($statusClass) ?>"><?= esc(lang('Finance.' . ($status === 'pending' ? 'unpaid' : $status))) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-1">
                                                <a href="<?= base_url('finance/' . (int) $row['id']) ?>" class="p-2 text-blue-600 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-700 rounded-md" title="<?= esc(lang('Finance.viewTransaction')) ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (!empty($row['can_record_payment'])): ?>
                                                    <button type="button" onclick="openSupplierPaymentModal(<?= (int) $row['id'] ?>, <?= esc(number_format((float) ($row['open_balance'] ?? 0), 2, '.', '')) ?>)" class="p-2 text-emerald-600 transition-colors duration-200 hover:bg-emerald-50 hover:text-emerald-700 rounded-md" title="<?= esc(lang('Finance.recordPayment')) ?>">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>
                                                    <button type="button" onclick="markSupplierExpensePaid(<?= (int) $row['id'] ?>)" class="p-2 text-amber-600 transition-colors duration-200 hover:bg-amber-50 hover:text-amber-700 rounded-md" title="<?= esc(lang('Finance.markExpenseAsFullyPaid')) ?>">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="px-6 py-12 text-center text-slate-500"><?= esc($tr('noSupplierExpensesYet')) ?></div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<div id="supplierPaymentModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeSupplierPaymentModal()"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h4 class="text-base font-semibold text-gray-900"><?= esc(lang('Finance.recordExpensePayment')) ?></h4>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeSupplierPaymentModal()"><i class="fas fa-times"></i></button>
            </div>
            <form id="supplierPaymentForm" class="space-y-4 p-5">
                <input type="hidden" id="supplierPaymentFinanceId">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600"><?= esc(lang('Finance.amount')) ?></label>
                    <input type="number" step="0.01" min="0.01" id="supplierPaymentAmount" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" required>
                    <p id="supplierPaymentHelp" class="mt-1 text-xs text-gray-500"></p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600"><?= esc(lang('Finance.paymentMethod')) ?></label>
                    <select id="supplierPaymentMethod" data-force-select2 data-allow-clear="false" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="cash"><?= esc(lang('Finance.cash')) ?></option>
                        <option value="card"><?= esc(lang('Finance.card')) ?></option>
                        <option value="bank_transfer"><?= esc(lang('Finance.bankTransfer')) ?></option>
                        <option value="check"><?= esc(lang('Finance.check')) ?></option>
                        <option value="other"><?= esc(lang('Finance.other')) ?></option>
                    </select>
                </div>
                <div id="supplierLiquidityField" class="hidden">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600"><?= esc(lang('Finance.paidFromAccount')) ?></label>
                    <select id="supplierLiquidityAccount" data-force-select2 data-allow-clear="false" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="bank"><?= esc(lang('Finance.liquidityAccount.bank')) ?></option>
                        <option value="undeposited_checks"><?= esc(lang('Finance.liquidityAccount.undeposited_checks')) ?></option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600"><?= esc(lang('Finance.date')) ?></label>
                    <input type="date" id="supplierPaymentDate" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600"><?= esc(lang('Finance.notes')) ?></label>
                    <textarea id="supplierPaymentNotes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeSupplierPaymentModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"><?= esc(lang('Finance.cancel')) ?></button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"><?= esc(lang('Finance.recordPayment')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let supplierPaymentMaxAmount = 0;
const supplierCurrencySymbol = '<?= esc($currencySymbol) ?>';
const supplierCurrencyPosition = '<?= esc($currencyPosition) ?>';

function supplierFormatMoney(value) {
    const amount = Number(value || 0);
    const formatted = amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return supplierCurrencyPosition === 'after' ? `${formatted} ${supplierCurrencySymbol}` : `${supplierCurrencySymbol}${formatted}`;
}

function notifySupplier(type, message) {
    if (typeof window.showToast === 'function') {
        window.showToast(type, type === 'success' ? '<?= esc(lang('Inventory.success')) ?>' : '<?= esc(lang('Inventory.error')) ?>', message);
        return;
    }
    alert(message);
}

function closeSupplierPaymentModal() {
    document.getElementById('supplierPaymentModal').classList.add('hidden');
    document.getElementById('supplierPaymentForm').reset();
}

function openSupplierPaymentModal(id, maxAmount) {
    supplierPaymentMaxAmount = Number(maxAmount || 0);
    document.getElementById('supplierPaymentFinanceId').value = String(id);
    document.getElementById('supplierPaymentAmount').value = supplierPaymentMaxAmount > 0 ? supplierPaymentMaxAmount.toFixed(2) : '';
    document.getElementById('supplierPaymentDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('supplierPaymentHelp').textContent = '<?= esc(lang('Finance.maximumPayable')) ?>: ' + supplierFormatMoney(supplierPaymentMaxAmount);
    syncSupplierLiquidityField();
    document.getElementById('supplierPaymentModal').classList.remove('hidden');
}

function syncSupplierLiquidityField() {
    const method = document.getElementById('supplierPaymentMethod')?.value || '';
    const field = document.getElementById('supplierLiquidityField');
    if (!field) return;
    field.classList.toggle('hidden', method !== 'check');
}

async function markSupplierExpensePaid(financeId) {
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
    if (result.csrf_token) {
        window.refreshCsrfToken(result.csrf_token);
    }
    notifySupplier(result.success ? 'success' : 'error', result.message || 'Request failed.');
    if (result.success) {
        window.location.reload();
    }
}

document.getElementById('supplierPaymentMethod')?.addEventListener('change', syncSupplierLiquidityField);

document.getElementById('supplierPaymentForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const financeId = document.getElementById('supplierPaymentFinanceId').value;
    const amount = parseFloat(document.getElementById('supplierPaymentAmount').value || '0');
    if (!amount || amount <= 0 || amount > supplierPaymentMaxAmount + 0.0001) {
        notifySupplier('error', '<?= esc(lang('Finance.invalidPaymentAmount')) ?>');
        return;
    }

    const formData = new FormData();
    formData.append(window.csrfConfig.name, window.getCsrfToken());
    formData.append('amount', amount.toFixed(2));
    formData.append('payment_method', document.getElementById('supplierPaymentMethod').value);
    formData.append('liquidity_account', document.getElementById('supplierLiquidityAccount').value);
    formData.append('payment_date', document.getElementById('supplierPaymentDate').value);
    formData.append('notes', document.getElementById('supplierPaymentNotes').value);

    const response = await fetch(`<?= base_url('finance') ?>/${financeId}/payments`, {
        method: 'POST',
        headers: {
            [window.csrfConfig.header]: window.getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
    const result = await response.json();
    if (result.csrf_token) {
        window.refreshCsrfToken(result.csrf_token);
    }
    notifySupplier(result.success ? 'success' : 'error', result.message || 'Request failed.');
    if (result.success) {
        window.location.reload();
    }
});
</script>
<?= $this->endSection() ?>
