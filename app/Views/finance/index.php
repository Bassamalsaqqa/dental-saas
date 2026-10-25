<?php include APPPATH . 'Views/layouts/partials/view_permissions.php'; ?>
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

<?php helper('asset'); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/v4-operations.css') ?>?v=<?= esc(asset_version('assets/css/v4-operations.css')) ?>">
<div class="v4-finance-workspace">
<header class="v4-page-heading"><div><h1><?= esc($tr('financeManagement')) ?></h1><p><?= esc(lang('V4.financeHelp')) ?></p></div><div class="v4-page-actions"><?php if($v4Can('finance','create')): ?><a id="addIncomeModalTrigger" data-finance-create-modal-open="financeIncomeModal" data-finance-transaction-type="payment" class="v4-button v4-button--primary" href="<?= base_url('finance/income/create') ?>"><i class="fas fa-hand-holding-dollar" aria-hidden="true"></i><?= esc($tr('recordPayment')) ?></a><details class="v4-action-menu"><summary class="v4-button"><?= esc(lang('V4.moreActions')) ?></summary><div><a href="<?= base_url('finance/income/create') ?>" data-finance-create-modal-open="financeIncomeModal" data-finance-transaction-type="invoice"><?= esc($tr('createInvoice')) ?></a><a id="newExpenseModalTrigger" href="<?= base_url('finance/expenses/create') ?>" data-finance-create-modal-open="financeExpenseModal"><?= esc($tr('newExpense')) ?></a></div></details><?php endif; ?></div></header>
<nav class="v4-operation-nav" aria-label="<?= esc($tr('financeAreas')) ?>"><a href="<?= base_url('finance') ?>" aria-current="page"><?= esc($tr('financialTransactions')) ?></a><a href="<?= base_url('finance/payables') ?>"><?= esc($tr('supplierPayablesNav')) ?></a><a href="<?= base_url('finance/payouts') ?>"><?= esc($tr('providerPayoutsNav')) ?></a><a href="<?= base_url('finance/transfers') ?>"><?= esc($tr('treasuryTransfers')) ?></a><?php if(!empty($can_access_finance_reports)): ?><a href="<?= base_url('finance/reports') ?>"><?= esc($tr('reports')) ?></a><?php endif; ?></nav>
<section class="v4-finance-position" aria-label="<?= esc($tr('cashBasisShort')) ?>">
<div class="v4-finance-collections"><div><span><?= esc($tr('grossCollections')) ?></span><strong id="totalRevenue" dir="ltr" aria-live="polite">&mdash;</strong><p><?= esc($tr('cashBasisShort')) ?></p></div><i class="fas fa-money-bill-wave" aria-hidden="true"></i></div>
<dl><div><dt><?= esc($tr('providerPaidCash')) ?></dt><dd id="providerPaidCash" dir="ltr">&mdash;</dd></div><div><dt><?= esc($tr('netClinicCash')) ?></dt><dd id="netClinicCash" dir="ltr">&mdash;</dd></div><div><dt><?= esc($tr('openInvoices')) ?></dt><dd id="outstandingInvoices">&mdash;</dd><p id="outstandingInvoicesBalance"></p></div></dl>
</section>
<section id="tableView" class="v4-surface v4-ledger">
<header class="v4-ledger-header"><h2><?= esc($tr('financialTransactions')) ?></h2><details class="v4-action-menu"><summary class="v4-button v4-button--small"><i class="fas fa-download" aria-hidden="true"></i><?= esc(lang('V4.exportRecords')) ?></summary><div><button type="button" onclick="exportToPDF()">PDF</button><button type="button" onclick="printTransactions()"><?= esc($tr('print')) ?></button><button type="button" onclick="exportToCSV()">CSV</button></div></details></header>
<div class="v4-ledger-data"><table id="transactionsTable"><thead><tr><?php foreach(['transactionId','patient','type','total','status','date','actions'] as $label): ?><th><?= esc($tr($label)) ?></th><?php endforeach; ?></tr></thead><tbody id="transactionsTableBody"></tbody></table><div id="v4-finance-cards" class="v4-mobile-records" aria-live="polite"></div></div>
</section></div>
<?php if ($v4Can('finance', 'create')): ?>
    <div id="financeIncomeModal" class="finance-create-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="financeIncomeModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="finance-create-modal__backdrop" data-finance-create-modal-close></div>
        <div class="finance-create-modal__shell" data-finance-create-modal-close>
            <form id="financeIncomeQuickCreateForm" action="<?= base_url('finance/store') ?>" method="POST" enctype="multipart/form-data" class="finance-create-modal__panel v4-form" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="finance">
                <input type="hidden" name="source_modal" value="finance_income_quick_create">

                <header class="v4-modal-header"><div><h2 id="financeIncomeModalTitle"><?= esc($tr('addIncome')) ?></h2><a href="<?= workflow_context_url('finance/income/create',$returnToContext) ?>" class="v4-text-link"><?= esc(lang('UI.openFullPage')) ?></a></div><button type="button" class="v4-icon-button" data-finance-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
                <div class="v4-modal-body">
                    <?php if (session('error') && $shouldOpenIncomeModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= esc(session('error')) ?></div>
                    <?php endif; ?>
                    <section class="v4-form-section">
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

                <footer class="v4-modal-footer"><button type="button" data-finance-create-modal-close class="v4-button"><?= esc(lang('UI.cancel')) ?></button><button type="submit" class="v4-button v4-button--primary"><?= esc($tr('addIncome')) ?></button></footer>
            </form>
        </div>
    </div>

    <div id="financeExpenseModal" class="finance-create-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="financeExpenseModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="finance-create-modal__backdrop" data-finance-create-modal-close></div>
        <div class="finance-create-modal__shell" data-finance-create-modal-close>
            <form id="financeExpenseQuickCreateForm" action="<?= base_url('finance/expenses/store') ?>" method="POST" enctype="multipart/form-data" class="finance-create-modal__panel v4-form" data-select2-dropdown-parent data-finance-calc data-base-currency="<?= esc($base_currency ?? 'ILS') ?>" onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="finance">
                <input type="hidden" name="source_modal" value="finance_expense_quick_create">

                <header class="v4-modal-header"><div><h2 id="financeExpenseModalTitle"><?= esc($tr('newExpense')) ?></h2><a href="<?= workflow_context_url('finance/expenses/create',$returnToContext) ?>" class="v4-text-link"><?= esc(lang('UI.openFullPage')) ?></a></div><button type="button" class="v4-icon-button" data-finance-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
                <div class="v4-modal-body">
                    <?php if (session('error') && $shouldOpenExpenseModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= esc(session('error')) ?></div>
                    <?php endif; ?>
                    <section class="v4-form-section">
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

                <footer class="v4-modal-footer"><button type="button" data-finance-create-modal-close class="v4-button"><?= esc(lang('UI.cancel')) ?></button><button type="submit" class="v4-button v4-button--primary"><?= esc($tr('newExpense')) ?></button></footer>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- DataTables JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= esc(asset_version('assets/js/finance-calc.js')) ?>"></script>

<script>



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

const financeModalInitialStates=new WeakMap();
function financeModalState(modal){return JSON.stringify([...new FormData(modal.querySelector('form'))].filter(([key])=>key!==document.querySelector('meta[name="csrf-name"]')?.content));}
function openFinanceCreateModal(modalId,trigger,preserveValues){
    const modal=mountFinanceCreateModal(modalId);if(!modal)return;
    if(!preserveValues){resetFinanceCreateModal(modal);applyFinanceTransactionPreset(modal,trigger);}
    window.AppModal.open(modal,{trigger:trigger||document.activeElement,focusSelector:'.select2-selection,input:not([type="hidden"]):not([disabled]),button',onOpen:()=>{initializeFinanceCreateModalContent(modal);financeModalInitialStates.set(modal,financeModalState(modal));}});
}
function closeFinanceCreateModal(modalId){
    const modal=document.getElementById(modalId);if(!window.AppModal.isOpen(modal))return;
    const initial=financeModalInitialStates.get(modal);
    if(initial&&initial!==financeModalState(modal)&&!window.confirm(<?= json_encode(lang('V4.discardFinance'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>))return;
    window.AppModal.close(modal,{onReset:()=>resetFinanceCreateModal(modal)});
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
    if(event.target.closest?.('.select2-search')) return;
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
            'view' => (bool) $v4Can('finance', 'view'),
            'edit' => (bool) $v4Can('finance', 'edit'),
            'delete' => (bool) $v4Can('finance', 'delete'),
            'create' => (bool) $v4Can('finance', 'create'),
        ],
    ],
];
?>
<script id="finance-page-config" type="application/json"><?= json_encode($financeConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="<?= base_url('assets/js/finance-index.js') ?>?v=<?= esc(asset_version('assets/js/finance-index.js')) ?>"></script>

<?= $this->endSection() ?>
