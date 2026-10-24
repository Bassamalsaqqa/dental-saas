<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<div class="v3-operational-view">
    <div class="v3-operational-body">
        <div class="relative">
            <div class="relative overflow-hidden">
                <div class="border-b">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="relative">

                                <div class="relative flex h-12 w-12 items-center justify-center">
                                    <i class="fas fa-receipt text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="v3-section-title transition-colors"><?= lang('Finance.newExpense') ?></h3>
                                <p class="font-medium"><?= lang('Finance.newExpenseHelp') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="<?= base_url('finance/create') ?>" class="relative inline-flex items-center px-6 py-3 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-plus-circle relative z-10 mr-2"></i>
                                <span class="relative z-10"><?= lang('Finance.addIncome') ?></span>
                            </a>
                            <a href="<?= esc(workflow_back('finance', $return_to ?? null)) ?>" class="relative inline-flex items-center border-2 px-6 py-3 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-arrow-left rtl-back-icon relative z-10 mr-2"></i>
                                <span class="relative z-10"><?= lang('Finance.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="">
                    <form action="<?= base_url('finance/expenses/store') ?>" method="POST" enctype="multipart/form-data" class="v3-flow-form " data-finance-calc data-base-currency="<?= esc($base_currency ?? 'ILS') ?>">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-truck-field mr-2"></i><?= esc(lang('Finance.linkedSupplierOptional')) ?>
                                </label>
                                <div class="relative">
                                    <select id="expenseSupplierSelect" name="supplier_id" data-force-select2 class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-select">
                                        <option value=""><?= esc(lang('Finance.selectSupplierOptional')) ?></option>
                                        <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                                            <option value="<?= (int) $supplier['id'] ?>" <?= (string) old('supplier_id', $selected_supplier_id ?? '') === (string) $supplier['id'] ? 'selected' : '' ?>><?= esc($supplier['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs"><?= esc(lang('Finance.linkedSupplierHelp')) ?></p>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-building mr-2"></i><?= lang('Finance.payeeVendorRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <input type="text" id="expenseCounterpartyInput" name="counterparty_name" value="<?= esc(old('counterparty_name')) ?>" maxlength="150" class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-input" placeholder="<?= esc(lang('Finance.payeeVendorPlaceholder')) ?>" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-tags mr-2"></i><?= lang('Finance.expenseCategoryRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="expense_category" data-force-select2 class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-select" required>
                                        <option value=""><?= lang('Finance.selectCategory') ?></option>
                                        <?php foreach (($expense_category_options ?? []) as $key => $label): ?>
                                            <option value="<?= esc($key) ?>" <?= old('expense_category') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-dollar-sign v3-text-primary mr-2"></i><?= lang('Finance.amountOriginalRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <input type="number" id="amountOriginal" name="amount_original" value="<?= esc(old('amount_original')) ?>" step="0.01" min="0.01" class="w-full border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input" placeholder="0.00" required>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-coins mr-2"></i><?= lang('Finance.currencyRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select id="currencySelect" name="currency" data-force-select2 class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-select" required>
                                        <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= old('currency', $base_currency ?? 'ILS') === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative" id="exchangeRateField">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-exchange-alt mr-2"></i><?= lang('Finance.exchangeRateRequired') ?>
                                </label>
                                <input type="number" id="exchangeRate" name="exchange_rate" value="<?= esc(old('exchange_rate', 1)) ?>" step="0.0001" min="0" class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-input" placeholder="1.0000">
                                <p class="mt-2 text-xs"><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($base_currency ?? 'ILS') ?>).</p>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-calculator v3-text-primary mr-2"></i><?= lang('Finance.convertedAmount') ?> (<?= esc($base_currency ?? 'ILS') ?>)
                                </label>
                                <input type="text" id="amountConverted" readonly class="w-full border-2 px-4 py-3 v3-input" value="0.00">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3">
                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-percent v3-text-primary mr-2"></i><?= lang('Finance.discount') ?>
                                </label>
                                <input type="number" id="discountAmount" name="discount_amount" value="<?= esc(old('discount_amount', 0)) ?>" step="0.01" min="0" class="w-full border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-file-invoice-dollar v3-text-warning mr-2"></i><?= lang('Finance.taxPercent') ?>
                                </label>
                                <input type="number" id="taxPercentage" name="tax_percentage" value="<?= esc(old('tax_percentage', 0)) ?>" step="0.01" min="0" max="100" class="w-full border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-receipt v3-text-danger mr-2"></i><?= lang('Finance.total') ?> (<?= esc($base_currency ?? 'ILS') ?>)
                                </label>
                                <input type="text" id="totalAmountLive" readonly class="w-full border-2 v3-border-default v3-bg-alert px-4 py-3 font-bold v3-text-danger v3-input" value="0.00">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3">
                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-credit-card mr-2"></i><?= lang('Finance.paymentMethodRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select id="paymentMethod" name="payment_method" data-force-select2 class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-select" required>
                                        <option value="cash" <?= old('payment_method', 'cash') === 'cash' ? 'selected' : '' ?>><?= lang('Finance.cash') ?></option>
                                        <option value="card" <?= old('payment_method') === 'card' ? 'selected' : '' ?>><?= lang('Finance.card') ?></option>
                                        <option value="bank_transfer" <?= old('payment_method') === 'bank_transfer' ? 'selected' : '' ?>><?= lang('Finance.bankTransfer') ?></option>
                                        <option value="check" <?= old('payment_method') === 'check' ? 'selected' : '' ?>><?= lang('Finance.check') ?></option>
                                        <option value="insurance" <?= old('payment_method') === 'insurance' ? 'selected' : '' ?>><?= lang('Finance.insurance') ?></option>
                                        <option value="other" <?= old('payment_method') === 'other' ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="relative" id="expenseLiquidityAccountField">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-vault mr-2"></i><?= lang('Finance.paidFromAccount') ?>
                                </label>
                                <div class="relative">
                                    <select id="expenseLiquidityAccount" name="liquidity_account" data-force-select2 class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-select">
                                        <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                                            <option value="<?= esc($key) ?>" <?= old('liquidity_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="mt-2 text-xs"><?= lang('Finance.paidFromAccountHelp') ?></p>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-calendar-check mr-2"></i><?= lang('Finance.transactionDate') ?>
                                </label>
                                <input type="text" name="transaction_date" data-clinic-date="1" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="w-full border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-input">
                                <p class="mt-2 text-xs"><?= lang('Finance.transactionDateHelp') ?></p>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-calendar v3-text-danger mr-2"></i><?= lang('Finance.paidDate') ?>
                                </label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(old('paid_date', date('Y-m-d'))) ?>" class="w-full border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3">
                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-hourglass-half v3-text-warning mr-2"></i><?= lang('Finance.paymentStatusRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="payment_status" data-force-select2 class="w-full border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-select" required>
                                        <option value="paid" <?= old('payment_status', 'paid') === 'paid' ? 'selected' : '' ?>><?= lang('Finance.paid') ?></option>
                                        <option value="partial" <?= old('payment_status') === 'partial' ? 'selected' : '' ?>><?= lang('Finance.partial') ?></option>
                                        <option value="pending" <?= old('payment_status') === 'pending' ? 'selected' : '' ?>><?= lang('Finance.unpaid') ?></option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-wallet v3-text-primary mr-2"></i><?= lang('Finance.amountPaidNow') ?>
                                </label>
                                <input type="number" name="amount_paid" value="<?= esc(old('amount_paid', 0)) ?>" step="0.01" min="0" class="w-full border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                                <p class="mt-2 text-xs"><?= lang('Finance.amountPaidNowHelp') ?></p>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-calendar-day v3-text-warning mr-2"></i><?= lang('Finance.dueDate') ?>
                                </label>
                                <input type="text" name="due_date" data-clinic-date="1" value="<?= esc(old('due_date')) ?>" class="w-full border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                            </div>
                        </div>

                        <div class="relative">
                            <label class="mb-3 block text-sm font-bold">
                                <i class="fas fa-paperclip v3-text-danger mr-2"></i><?= lang('Finance.attachmentOptional') ?>
                            </label>
                            <div class="border v3-border-default v3-bg-alert">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-medium"><?= lang('Finance.expenseAttachmentHelp') ?></p>
                                        <p class="mt-1 text-xs"><?= lang('Finance.attachmentFormatsHelp') ?></p>
                                    </div>
                                    <div class="flex flex-col gap-2 sm:items-end">
                                        <label class="inline-flex w-fit cursor-pointer items-center v3-bg-danger px-4 py-2 text-sm font-bold v3-hover-danger">
                                            <i class="fas fa-upload mr-2"></i><?= lang('Finance.uploadAttachment') ?>
                                            <input type="file" name="attachment" id="expenseAttachmentInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden v3-input">
                                        </label>
                                        <span id="expenseAttachmentName" class="text-xs"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-align-left mr-2"></i><?= lang('Finance.description') ?>
                                </label>
                                <textarea name="description" rows="4" class="w-full resize-none border-2 px-4 py-3 focus:outline-none focus:ring-4 v3-textarea" placeholder="<?= esc(lang('Finance.expenseDescriptionPlaceholder')) ?>"><?= esc(old('description')) ?></textarea>
                            </div>

                            <div class="relative">
                                <label class="mb-3 block text-sm font-bold">
                                    <i class="fas fa-file-lines mr-2"></i><?= lang('Finance.detailsReference') ?>
                                </label>
                                <textarea name="service_details" rows="4" class="w-full resize-none border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-textarea" placeholder="<?= esc(lang('Finance.detailsReferencePlaceholder')) ?>"><?= esc(old('service_details')) ?></textarea>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="mb-3 block text-sm font-bold">
                                <i class="fas fa-sticky-note mr-2"></i><?= lang('Finance.notes') ?>
                            </label>
                            <textarea name="notes" rows="4" class="w-full resize-none border-2 px-4 py-3 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-textarea"><?= esc(old('notes')) ?></textarea>
                        </div>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="border v3-border-default v3-bg-alert px-4 py-3 text-sm font-medium v3-text-danger"><?= esc(session()->getFlashdata('error')) ?></div>
                        <?php endif; ?>

                        <div class="flex flex-col justify-end gap-4 border-t pt-6 sm:flex-row">
                            <a href="<?= esc(workflow_cancel('finance', $return_to ?? null)) ?>" class="relative inline-flex items-center justify-center border-2 px-8 py-3 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-times relative z-10 mr-2"></i>
                                <span class="relative z-10"><?= lang('Finance.cancel') ?></span>
                            </a>
                            <button type="submit" class="relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold v3-btn v3-btn--primary">

                                <i class="fas fa-save relative z-10 mr-2"></i>
                                <span class="relative z-10"><?= lang('Finance.recordExpense') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<script>
(() => {
    const input = document.getElementById('expenseAttachmentInput');
    const label = document.getElementById('expenseAttachmentName');
    const paymentMethod = document.getElementById('paymentMethod');
    const paymentStatus = document.querySelector('select[name="payment_status"]');
    const amountPaidInput = document.querySelector('input[name="amount_paid"]');
    const accountField = document.getElementById('expenseLiquidityAccountField');
    const accountSelect = document.getElementById('expenseLiquidityAccount');
    const supplierSelect = document.getElementById('expenseSupplierSelect');
    const counterpartyInput = document.getElementById('expenseCounterpartyInput');
    if (!input || !label) {
        return;
    }
    input.addEventListener('change', () => {
        label.textContent = input.files?.length ? input.files[0].name : '';
    });

    const syncExpenseLiquidityField = () => {
        if (!paymentMethod || !paymentStatus || !accountField || !accountSelect) {
            return;
        }

        const shouldShow = paymentStatus.value !== 'pending';
        accountField.classList.toggle('hidden', !shouldShow);
        accountSelect.disabled = !shouldShow;
        if (shouldShow && !accountSelect.value) {
            accountSelect.value = paymentMethod.value === 'cash' ? 'safe_cash' : 'bank';
        }
        if (!shouldShow) {
            accountSelect.value = '';
        }
    };

    paymentMethod?.addEventListener('change', syncExpenseLiquidityField);
    paymentStatus?.addEventListener('change', syncExpenseLiquidityField);
    amountPaidInput?.addEventListener('input', syncExpenseLiquidityField);
    if (window.jQuery) {
        window.jQuery(paymentMethod).on('select2:select select2:clear change', syncExpenseLiquidityField);
        window.jQuery(paymentStatus).on('select2:select select2:clear change', syncExpenseLiquidityField);
    }
    syncExpenseLiquidityField();

    let lastSupplierName = '';
    const syncSupplierCounterparty = () => {
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
    syncSupplierCounterparty();
})();
</script>
</div>
<?= $this->endSection() ?>

