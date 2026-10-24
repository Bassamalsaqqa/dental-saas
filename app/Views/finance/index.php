<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Finance.' . $key, $params);
$currentUri = service('request')->getUri();
$returnToContext = ltrim($currentUri->getPath(), '/');
$currentQuery = $currentUri->getQuery();
if ($currentQuery !== '') {
    $returnToContext .= '?' . $currentQuery;
}
$returnToContextEncoded = urlencode($returnToContext);
$isRtl = (bool) (session('is_rtl') ?? (service('request')->getLocale() === 'ar'));
$sourceModal = (string) old('source_modal', '');
$shouldOpenIncomeModal = $sourceModal === 'finance_income_quick_create';
$shouldOpenExpenseModal = $sourceModal === 'finance_expense_quick_create';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables Styling -->


<div>
    <div class="finance-page-wrap mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <header class="v3-command-header"><div><h1><?= lang('Finance.financeManagement') ?></h1><p><?= esc($tr('cashBasisShort')) ?></p></div><div class="v3-command-actions"><?php if (has_permission('finance', 'create')): ?>
                            <a href="<?= base_url('finance/income/create') ?>" id="addIncomeModalTrigger" data-finance-create-modal-open="financeIncomeModal" data-finance-transaction-type="payment" class="inline-flex w-full items-center justify-center gap-2 v3-radius v3-bg-panel px-4 py-2.5 text-sm v3-text-primary transition v3-hover-primary sm:w-auto v3-btn v3-btn--secondary">
                                <i class="fas fa-hand-holding-dollar"></i>
                                <?= esc($tr('recordPayment')) ?>
                            </a>
                            <a href="<?= base_url('finance/income/create') ?>" data-finance-create-modal-open="financeIncomeModal" data-finance-transaction-type="invoice" class="inline-flex w-full items-center justify-center gap-2 v3-radius v3-bg-primary px-4 py-2.5 text-sm transition v3-hover-primary sm:w-auto v3-btn v3-btn--secondary">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <?= esc($tr('createInvoice')) ?>
                            </a>
                            <a href="<?= base_url('finance/expenses/create') ?>" id="newExpenseModalTrigger" data-finance-create-modal-open="financeExpenseModal" class="inline-flex w-full items-center justify-center gap-2 v3-radius v3-bg-danger px-4 py-2.5 text-sm transition v3-hover-danger sm:w-auto v3-btn v3-btn--danger">
                                <i class="fas fa-receipt"></i>
                                <?= esc($tr('newExpense')) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($can_access_finance_reports)): ?>
                            <a href="<?= base_url('finance/reports') ?>" class="finance-hero-chip inline-flex w-full items-center justify-center gap-2 v3-radius px-4 py-2.5 text-sm transition v3-hover-soft sm:w-auto v3-btn v3-btn--secondary">
                                <i class="fas fa-chart-bar"></i>
                                <?= esc($tr('reports')) ?>
                            </a>
                        <?php endif; ?></div></header>



        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <section class="v3-metric-strip v3-finance-metrics">
<article class="v3-metric v3-metric-tone-0"><span><?= lang('Finance.grossCollections') ?></span><strong id="totalRevenue"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong></article>
<article class="v3-metric v3-metric-tone-1"><span><?= lang('Finance.providerPaidCash') ?></span><strong id="providerPaidCash"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong></article>
<article class="v3-metric v3-metric-tone-2"><span><?= lang('Finance.netClinicCash') ?></span><strong id="netClinicCash"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong></article>
<article class="v3-metric v3-metric-tone-3"><span><?= lang('Finance.openInvoices') ?></span><strong id="outstandingInvoices"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong><p class="sr-only" id="outstandingInvoicesBalance"><?= esc($tr('openInvoicesHelp')) ?></p></article>
</section>


        <nav class="v3-finance-navigation" aria-label="<?= esc($tr('financeAreas')) ?>">
            <span class="shrink-0 text-xs uppercase tracking-wide v3-text-muted"><?= esc($tr('financeAreas')) ?></span>
            <div class="flex flex-wrap gap-2">
                <a href="<?= base_url('finance/payables') ?>" class="inline-flex items-center gap-2 v3-radius v3-bg-waiting px-3 py-2 text-sm font-bold v3-text-warning transition v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <?= esc($tr('supplierPayablesNav')) ?>
                </a>
                <a href="<?= base_url('finance/payouts') ?>" class="inline-flex items-center gap-2 v3-radius v3-bg-scheduled px-3 py-2 text-sm font-bold v3-text-primary transition v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-user-doctor"></i>
                    <?= esc($tr('providerPayoutsNav')) ?>
                </a>
                <a href="<?= base_url('finance/transfers') ?>" class="inline-flex items-center gap-2 v3-radius v3-bg-soft px-3 py-2 text-sm font-bold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-right-left"></i>
                    <?= esc($tr('treasuryTransfers')) ?>
                </a>
            </div>
        </nav>

        <!-- Standardized Finance Table -->
        <div class="mb-8">
            <!-- Table View -->
            <div id="tableView" class="finance-table-panel">
                <!-- Table Header -->
                <div class="finance-table-header v3-panel-toolbar">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center v3-radius v3-warm-surface">
                                <i class="fas fa-table-list"></i>
                            </div>
                            <div>
                                <h3 class="text-lg v3-text-ink"><?= lang('Finance.financialTransactions') ?></h3>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="finance-export-btn v3-btn v3-btn--secondary">
                                <i class="fas fa-file-pdf v3-text-danger"></i>
                                <?= esc($tr('exportPdf')) ?>
                            </button>
                            <button onclick="printTransactions()" class="finance-export-btn v3-btn v3-btn--secondary">
                                <i class="fas fa-print v3-text-muted"></i>
                                <?= esc($tr('print')) ?>
                            </button>
                            <button onclick="exportToCSV()" class="finance-export-btn v3-btn v3-btn--secondary">
                                <i class="fas fa-file-csv v3-text-primary"></i>
                                <?= esc($tr('exportCsv')) ?>
                            </button>
                        </div>
            </div>
        </div>

                    <!-- Enhanced Table with Advanced Styling -->
                    <div class="overflow-x-auto">
                        <div class="dataTables-wrapper">
                            <table id="transactionsTable" class="w-full min-w-full">
                            <thead class="v3-bg-panel">
                                <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-receipt v3-text-primary"></i>
                                            <span><?= lang('Finance.transactionId') ?></span>
                            </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user v3-text-primary"></i>
                                            <span><?= lang('Finance.patient') ?></span>
                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-tag v3-text-primary"></i>
                                            <span><?= lang('Finance.type') ?></span>
                    </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign v3-text-primary"></i>
                                            <span><?= lang('Finance.total') ?></span>
                                </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle v3-text-primary"></i>
                                            <span><?= lang('Finance.status') ?></span>
                            </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar v3-text-primary"></i>
                                            <span><?= lang('Finance.date') ?></span>
                            </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs v3-text-primary"></i>
                                            <span><?= lang('Finance.actions') ?></span>
                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTableBody" class="v3-bg-panel divide-y v3-border-default">
                                    <!-- Data will be loaded via DataTables -->
                                    <tr id="loadingRow">
                                        <td colspan="7" class="px-6 py-4 text-center v3-text-muted">
                                            <div class="flex items-center justify-center space-x-2">
                                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 v3-border-default"></div>
                                                <span><?= lang('Finance.loadingTransactions') ?></span>
                                </div>
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                            </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

<?php if (has_permission('finance', 'create')): ?>
    <div id="financeIncomeModal" class="finance-create-modal v3-context-drawer fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="financeIncomeModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="finance-create-modal__backdrop v3-context-drawer__backdrop fixed inset-0 v3-backdrop" data-finance-create-modal-close></div>
        <div class="finance-create-modal__shell v3-context-drawer__shell relative z-10 flex h-full items-center justify-center p-4" data-finance-create-modal-close>
            <form id="financeIncomeQuickCreateForm" action="<?= base_url('finance/store') ?>" method="POST" enctype="multipart/form-data" class="finance-create-modal__panel v3-context-drawer__panel " data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="finance">
                <input type="hidden" name="source_modal" value="finance_income_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b v3-border-default v3-bg-panel px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-radius v3-warm-surface">
                            <i class="fas fa-circle-plus"></i>
                        </span>
                        <div>
                            <h2 id="financeIncomeModalTitle" class="text-lg font-bold v3-text-ink"><?= esc($tr('addIncome')) ?></h2>
                            <a href="<?= workflow_context_url('finance/income/create', $returnToContext) ?>" class="mt-1 inline-flex text-xs font-semibold v3-text-primary v3-hover-text-primary">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--icon" data-finance-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto v3-bg-panel p-5">
                    <?php if (session('error') && $shouldOpenIncomeModal): ?>
                        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger"><?= esc(session('error')) ?></div>
                    <?php endif; ?>
                    <section class="v3-radius border v3-border-default v3-bg-panel p-5 md:p-6">
                        <?= view('finance/partials/form_fields', [
                            'contextual' => false,
                            'patients' => $patients ?? [],
                            'clinic_providers' => $clinic_providers ?? [],
                            'base_currency' => $base_currency ?? 'ILS',
                            'currency_options' => $currency_options ?? [],
                            'liquidity_account_options' => $liquidity_account_options ?? [],
                            'check_outgoing_liquidity_options' => $check_outgoing_liquidity_options ?? [],
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'financeIncomeQuick',
                            'show_liquidity_account' => false,
                            'advanced_details_style' => 'flat',
                        ]) ?>
                    </section>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t v3-border-default v3-bg-panel px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" data-finance-create-modal-close class="inline-flex items-center justify-center v3-radius border v3-border-default px-5 py-2.5 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc(lang('UI.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center v3-radius v3-warm-surface px-5 py-2.5 text-sm font-bold transition v3-btn v3-btn--primary">
                        <i class="fas fa-save me-2"></i>
                        <?= esc($tr('addIncome')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="financeExpenseModal" class="finance-create-modal v3-context-drawer fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="financeExpenseModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="finance-create-modal__backdrop v3-context-drawer__backdrop fixed inset-0 v3-backdrop" data-finance-create-modal-close></div>
        <div class="finance-create-modal__shell v3-context-drawer__shell relative z-10 flex h-full items-center justify-center p-4" data-finance-create-modal-close>
            <form id="financeExpenseQuickCreateForm" action="<?= base_url('finance/expenses/store') ?>" method="POST" enctype="multipart/form-data" class="finance-create-modal__panel v3-context-drawer__panel " data-select2-dropdown-parent data-finance-calc data-base-currency="<?= esc($base_currency ?? 'ILS') ?>" onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="finance">
                <input type="hidden" name="source_modal" value="finance_expense_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b v3-border-default v3-bg-panel px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-radius v3-warm-surface">
                            <i class="fas fa-receipt"></i>
                        </span>
                        <div>
                            <h2 id="financeExpenseModalTitle" class="text-lg font-bold v3-text-ink"><?= esc($tr('newExpense')) ?></h2>
                            <a href="<?= workflow_context_url('finance/expenses/create', $returnToContext) ?>" class="mt-1 inline-flex text-xs font-semibold v3-text-danger v3-hover-text-danger">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--icon" data-finance-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto v3-bg-panel p-5">
                    <?php if (session('error') && $shouldOpenExpenseModal): ?>
                        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger"><?= esc(session('error')) ?></div>
                    <?php endif; ?>
                    <section class="v3-radius border v3-border-default v3-bg-panel p-5 md:p-6">
                        <?= view('finance/partials/expense_form_fields', [
                            'base_currency' => $base_currency ?? 'ILS',
                            'currency_options' => $currency_options ?? [],
                            'liquidity_account_options' => $liquidity_account_options ?? [],
                            'expense_category_options' => $expense_category_options ?? [],
                            'supplier_directory' => $supplier_directory ?? [],
                            'selected_supplier_id' => $selected_supplier_id ?? 0,
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'financeExpenseQuick',
                        ]) ?>
                    </section>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t v3-border-default v3-bg-panel px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" data-finance-create-modal-close class="inline-flex items-center justify-center v3-radius border v3-border-default px-5 py-2.5 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc(lang('UI.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center v3-radius v3-warm-surface px-5 py-2.5 text-sm font-bold transition v3-btn v3-btn--primary">
                        <i class="fas fa-save me-2"></i>
                        <?= esc($tr('recordExpense')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>

<script>
let financeCreateModalTrigger = null;
let financeCreateModalCloseTimer = null;

function mountFinanceCreateModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    if (modal && modal.dataset.closeControlsBound !== '1') {
        modal.dataset.closeControlsBound = '1';
        modal.querySelectorAll('button[data-finance-create-modal-close], a[data-finance-create-modal-close]').forEach(function (control) {
            control.addEventListener('click', function (event) {
                event.preventDefault();
                closeFinanceCreateModal(modal.id);
            });
        });
    }
    return modal;
}

function initializeFinanceExpenseModal(modal) {
    modal.querySelectorAll('form').forEach(function (form) {
        if (form.dataset.expenseQuickBound === '1') {
            return;
        }
        form.dataset.expenseQuickBound = '1';

        const attachmentInput = form.querySelector('[data-expense-attachment-input]');
        const attachmentName = form.querySelector('[data-expense-attachment-name]');
        attachmentInput?.addEventListener('change', function () {
            if (attachmentName) {
                attachmentName.textContent = attachmentInput.files?.length ? attachmentInput.files[0].name : '';
            }
        });

        const paymentMethod = form.querySelector('[name="payment_method"]');
        const paymentStatus = form.querySelector('[data-expense-payment-status]');
        const amountPaidInput = form.querySelector('[data-expense-amount-paid]');
        const accountField = form.querySelector('[data-expense-liquidity-account-field]');
        const accountSelect = form.querySelector('[data-expense-liquidity-account]');
        const defaultAccountForMethod = function (method) {
            return method === 'cash' ? 'safe_cash' : 'bank';
        };
        const syncExpenseLiquidityField = function () {
            if (!paymentMethod || !paymentStatus || !accountField || !accountSelect) {
                return;
            }
            const shouldShow = paymentStatus.value !== 'pending';
            accountField.classList.toggle('hidden', !shouldShow);
            accountSelect.disabled = !shouldShow;
            if (shouldShow && !accountSelect.value) {
                const defaultAccount = defaultAccountForMethod(paymentMethod.value);
                accountSelect.value = defaultAccount;
                if (window.jQuery) {
                    window.jQuery(accountSelect).val(defaultAccount).trigger('change');
                }
            }
            if (!shouldShow) {
                accountSelect.value = '';
                if (window.jQuery) {
                    window.jQuery(accountSelect).val('').trigger('change');
                }
            }
        };
        paymentMethod?.addEventListener('change', syncExpenseLiquidityField);
        paymentStatus?.addEventListener('change', syncExpenseLiquidityField);
        amountPaidInput?.addEventListener('input', syncExpenseLiquidityField);
        if (window.jQuery && paymentMethod) {
            window.jQuery(paymentMethod).on('select2:select select2:clear change', syncExpenseLiquidityField);
        }
        if (window.jQuery && paymentStatus) {
            window.jQuery(paymentStatus).on('select2:select select2:clear change', syncExpenseLiquidityField);
        }
        syncExpenseLiquidityField();

        const supplierSelect = form.querySelector('[data-expense-supplier-select]');
        const counterpartyInput = form.querySelector('[data-expense-counterparty-input]');
        let lastSupplierName = '';
        const syncSupplierCounterparty = function () {
            if (!supplierSelect || !counterpartyInput) {
                return;
            }
            const selected = supplierSelect.options[supplierSelect.selectedIndex];
            if (!selected) {
                return;
            }
            const supplierName = selected.value ? selected.text.trim() : '';
            const currentCounterparty = counterpartyInput.value.trim();
            if (supplierName && (currentCounterparty === '' || currentCounterparty === lastSupplierName)) {
                counterpartyInput.value = supplierName;
            }
            lastSupplierName = supplierName;
        };
        supplierSelect?.addEventListener('change', syncSupplierCounterparty);
        if (window.jQuery && supplierSelect) {
            window.jQuery(supplierSelect).on('select2:select select2:clear change', syncSupplierCounterparty);
        }
        syncSupplierCounterparty();
    });
}

function initializeFinanceCreateModalContent(modal) {
    if (!modal) {
        return Promise.resolve();
    }
    const tasks = [];
    if (typeof window.initAppSelects === 'function') {
        tasks.push(Promise.resolve(window.initAppSelects(modal)));
    }
    if (typeof window.initializeClinicDateInputs === 'function') {
        window.initializeClinicDateInputs(modal);
    }
    if (typeof window.initFinanceCalculators === 'function') {
        window.initFinanceCalculators(modal);
    }
    initializeFinanceExpenseModal(modal);
    return Promise.allSettled(tasks);
}

function resetFinanceCreateModal(modal) {
    if (!modal) {
        return;
    }
    const form = modal.querySelector('form');
    if (!form) {
        return;
    }

    form.reset();
    form.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.value = '';
    });
    form.querySelectorAll('[data-check-scan-name], [data-expense-attachment-name]').forEach(function (label) {
        label.textContent = '';
    });
    form.querySelectorAll('[data-finance-optional]').forEach(function (section) {
        section.classList.add('hidden');
    });
    form.querySelectorAll('[data-finance-optional-toggle]').forEach(function (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
    });
    form.querySelectorAll('[data-finance-optional-chevron]').forEach(function (chevron) {
        chevron.classList.remove('rotate-180');
    });
    form.querySelectorAll('select').forEach(function (select) {
        select.dispatchEvent(new Event('change', { bubbles: true }));
        if (window.jQuery) {
            window.jQuery(select).trigger('change');
        }
    });
    form.querySelectorAll('input, textarea').forEach(function (field) {
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    });
    if (typeof window.initFinanceCalculators === 'function') {
        window.initFinanceCalculators(modal);
    }
}

function applyFinanceTransactionPreset(modal, trigger) {
    const transactionType = trigger?.getAttribute('data-finance-transaction-type') || '';
    if (!transactionType) {
        return;
    }
    const transactionSelect = modal.querySelector('select[name="transaction_type"]');
    if (!transactionSelect) {
        return;
    }
    transactionSelect.value = transactionType;
    transactionSelect.dispatchEvent(new Event('change', { bubbles: true }));
    if (window.jQuery) {
        window.jQuery(transactionSelect).val(transactionType).trigger('change');
    }
}

function openFinanceCreateModal(modalId, trigger, preserveValues) {
    const modal = mountFinanceCreateModal(modalId);
    if (!modal) {
        return;
    }
    if (!preserveValues) {
        resetFinanceCreateModal(modal);
        applyFinanceTransactionPreset(modal, trigger);
    }
    financeCreateModalTrigger = trigger || document.activeElement;
    if (financeCreateModalCloseTimer) {
        clearTimeout(financeCreateModalCloseTimer);
        financeCreateModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function () {
        modal.classList.add('is-open');
    });
    window.setTimeout(function () {
        initializeFinanceCreateModalContent(modal);
        const firstField = modal.querySelector('select, input:not([type="hidden"]), textarea, button');
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus({ preventScroll: true });
        }
    }, 80);
}

function closeFinanceCreateModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal || modal.classList.contains('hidden')) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    financeCreateModalCloseTimer = setTimeout(function () {
        resetFinanceCreateModal(modal);
        modal.classList.add('hidden');
        if (financeCreateModalTrigger && typeof financeCreateModalTrigger.focus === 'function') {
            financeCreateModalTrigger.focus();
        }
        financeCreateModalTrigger = null;
    }, 240);
}

document.addEventListener('click', function (event) {
    const openTrigger = event.target.closest('[data-finance-create-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openFinanceCreateModal(openTrigger.getAttribute('data-finance-create-modal-open'), openTrigger);
        return;
    }

    const closeTrigger = event.target.closest('[data-finance-create-modal-close]');
    if (closeTrigger) {
        event.preventDefault();
        const modal = closeTrigger.closest('.finance-create-modal');
        if (modal) {
            closeFinanceCreateModal(modal.id);
        }
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
        return;
    }
    document.querySelectorAll('.finance-create-modal:not(.hidden)').forEach(function (modal) {
        event.preventDefault();
        closeFinanceCreateModal(modal.id);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    if (<?= $shouldOpenIncomeModal ? 'true' : 'false' ?>) {
        openFinanceCreateModal('financeIncomeModal', document.getElementById('addIncomeModalTrigger'), true);
    } else if (<?= $shouldOpenExpenseModal ? 'true' : 'false' ?>) {
        openFinanceCreateModal('financeExpenseModal', document.getElementById('newExpenseModalTrigger'), true);
    }
});
</script>

<?php
$financeConfig = [
    'urls' => [
        'data' => base_url('finance/getFinancesData'),
        'base' => base_url('finance'),
        'stats' => base_url('finance/stats'),
    ],
    'i18n' => [
        'transactionId' => lang('Finance.transactionId'),
        'financialTransactionsReport' => lang('Finance.financialTransactions') . ' ' . lang('Finance.report'),
        'loadingTransactions' => lang('Finance.loadingTransactions'),
        'confirmDeleteTransaction' => lang('Finance.confirmDeleteTransaction'),
        'noId' => '-',
        'expensePayee' => lang('Finance.counterparty'),
        'transactionTypes' => [
            'payment' => lang('Finance.payment'),
            'invoice' => lang('Finance.invoice'),
            'refund' => lang('Finance.refund'),
            'adjustment' => lang('Finance.adjustment'),
            'expense' => lang('Finance.expenseTransaction'),
        ],
        'paymentStatuses' => [
            'paid' => lang('Finance.paid'),
            'pending' => lang('Finance.pending'),
            'partial' => lang('Finance.partial'),
            'overdue' => lang('Finance.overdue'),
            'cancelled' => lang('Finance.cancelled'),
        ],
        'paymentMethods' => [
            'cash' => lang('Finance.cash'),
            'card' => lang('Finance.card'),
            'bank_transfer' => lang('Finance.bankTransfer'),
            'check' => lang('Finance.check'),
            'insurance' => lang('Finance.insurance'),
            'other' => lang('Finance.other'),
        ],
        'actions' => [
            'viewTransaction' => lang('Finance.viewTransaction'),
            'viewInvoice' => lang('Finance.invoice'),
            'addPayment' => lang('Finance.postSettlement'),
            'editTransaction' => lang('Finance.editTransaction'),
            'deleteTransaction' => lang('Finance.delete'),
        ],
        'dataTable' => [
            'processing' => lang('Finance.loadingTransactions'),
            'search' => lang('Finance.search'),
            'lengthMenu' => lang('Finance.show') . ' _MENU_',
            'info' => lang('Finance.showing') . ' _START_ ' . lang('Finance.to') . ' _END_ ' . lang('Finance.of') . ' _TOTAL_ ' . lang('Finance.financialTransactions'),
            'infoEmpty' => lang('Finance.showing') . ' 0 ' . lang('Finance.to') . ' 0 ' . lang('Finance.of') . ' 0 ' . lang('Finance.financialTransactions'),
            'zeroRecords' => lang('Finance.noDataAvailable'),
            'emptyTable' => lang('Finance.noDataAvailable'),
            'paginate' => [
                'previous' => lang('Finance.previous'),
                'next' => lang('Finance.next'),
            ],
        ],
    ],
    'currency' => [
        'symbol' => (string) $currency_symbol,
        'position' => (string) $currency_position,
    ],
    'permissions' => [
        'finance' => [
            'view' => (bool) has_permission('finance', 'view'),
            'edit' => (bool) has_permission('finance', 'edit'),
            'delete' => (bool) has_permission('finance', 'delete'),
            'create' => (bool) has_permission('finance', 'create'),
        ],
    ],
];
?>
<script id="finance-page-config" type="application/json"><?= json_encode($financeConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script defer src="<?= base_url('assets/js/finance-index.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/finance-index.js') ?: '1' ?>"></script>

<?= $this->endSection() ?>
