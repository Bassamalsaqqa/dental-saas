<?php
$baseCurrency = (string) ($base_currency ?? 'ILS');
$idPrefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($id_prefix ?? ''));
$fieldId = static function (string $id) use ($idPrefix): string {
    return $idPrefix !== '' ? $idPrefix . '_' . $id : $id;
};
$inputClass = 'w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm transition focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-500/15';
$selectClass = $inputClass . ' finance-leading-input';
$showOptionalDetails = old('supplier_id') !== null
    || trim((string) old('description', '')) !== ''
    || trim((string) old('service_details', '')) !== ''
    || trim((string) old('notes', '')) !== '';
?>
<div class="space-y-5">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Finance.linkedSupplierOptional')) ?></label>
            <select id="<?= esc($fieldId('expenseSupplierSelect')) ?>" name="supplier_id" data-expense-supplier-select data-force-select2 class="<?= esc($selectClass) ?>">
                <option value=""><?= esc(lang('Finance.selectSupplierOptional')) ?></option>
                <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                    <option value="<?= (int) $supplier['id'] ?>" <?= (string) old('supplier_id', $selected_supplier_id ?? '') === (string) $supplier['id'] ? 'selected' : '' ?>><?= esc($supplier['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.payeeVendorRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span></label>
            <input type="text" id="<?= esc($fieldId('expenseCounterpartyInput')) ?>" name="counterparty_name" data-expense-counterparty-input value="<?= esc(old('counterparty_name')) ?>" maxlength="150" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Finance.payeeVendorPlaceholder')) ?>" required>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.expenseCategoryRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span></label>
            <select name="expense_category" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <option value=""><?= lang('Finance.selectCategory') ?></option>
                <?php foreach (($expense_category_options ?? []) as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= old('expense_category') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.amountOriginalRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span></label>
            <input type="number" id="<?= esc($fieldId('amountOriginal')) ?>" name="amount_original" value="<?= esc(old('amount_original')) ?>" step="0.01" min="0.01" class="<?= esc($inputClass) ?>" placeholder="0.00" required>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.currencyRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span></label>
            <select id="<?= esc($fieldId('currencySelect')) ?>" name="currency" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <?php foreach (($currency_options ?? [$baseCurrency]) as $option): ?>
                    <option value="<?= esc($option) ?>" <?= old('currency', $baseCurrency) === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="<?= esc($fieldId('exchangeRateField')) ?>" data-exchange-rate-field>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.exchangeRateRequired') ?></label>
            <input type="number" id="<?= esc($fieldId('exchangeRate')) ?>" name="exchange_rate" value="<?= esc(old('exchange_rate', 1)) ?>" step="0.0001" min="0" class="<?= esc($inputClass) ?>" placeholder="1.0000">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div data-finance-converted-field>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.convertedAmount') ?> (<?= esc($baseCurrency) ?>)</label>
            <input type="text" id="<?= esc($fieldId('amountConverted')) ?>" data-finance-converted readonly class="<?= esc($inputClass) ?> bg-slate-100" value="0.00">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.discount') ?></label>
            <input type="number" id="<?= esc($fieldId('discountAmount')) ?>" name="discount_amount" value="<?= esc(old('discount_amount', 0)) ?>" step="0.01" min="0" class="<?= esc($inputClass) ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.taxPercent') ?></label>
            <input type="number" id="<?= esc($fieldId('taxPercentage')) ?>" name="tax_percentage" value="<?= esc(old('tax_percentage', 0)) ?>" step="0.01" min="0" max="100" class="<?= esc($inputClass) ?>">
        </div>
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.total') ?> (<?= esc($baseCurrency) ?>)</label>
        <input type="text" id="<?= esc($fieldId('totalAmountLive')) ?>" data-finance-total readonly class="<?= esc($inputClass) ?> border-rose-200 bg-rose-50 font-bold text-rose-700" value="0.00">
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.paymentMethodRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span></label>
            <select id="<?= esc($fieldId('paymentMethod')) ?>" name="payment_method" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <?php foreach (['cash', 'card', 'bank_transfer', 'check', 'insurance', 'other'] as $method): ?>
                    <option value="<?= esc($method) ?>" <?= old('payment_method', 'cash') === $method ? 'selected' : '' ?>><?= lang('Finance.' . ($method === 'bank_transfer' ? 'bankTransfer' : $method)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="<?= esc($fieldId('expenseLiquidityAccountField')) ?>" data-expense-liquidity-account-field>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.paidFromAccount') ?></label>
            <select id="<?= esc($fieldId('expenseLiquidityAccount')) ?>" name="liquidity_account" data-expense-liquidity-account data-force-select2 class="<?= esc($selectClass) ?>">
                <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= old('liquidity_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.paymentStatusRequired') ?><span class="required-indicator notranslate" aria-hidden="true">*</span></label>
            <select name="payment_status" data-expense-payment-status data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <option value="paid" <?= old('payment_status', 'paid') === 'paid' ? 'selected' : '' ?>><?= lang('Finance.paid') ?></option>
                <option value="partial" <?= old('payment_status') === 'partial' ? 'selected' : '' ?>><?= lang('Finance.partial') ?></option>
                <option value="pending" <?= old('payment_status') === 'pending' ? 'selected' : '' ?>><?= lang('Finance.unpaid') ?></option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.transactionDate') ?></label>
            <input type="text" name="transaction_date" data-clinic-date="1" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>">
        </div>
        <div data-finance-paid-date-field>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.paidDate') ?></label>
            <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(old('paid_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>">
        </div>
        <div data-finance-due-date-field>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.dueDate') ?></label>
            <input type="text" name="due_date" data-clinic-date="1" value="<?= esc(old('due_date')) ?>" class="<?= esc($inputClass) ?>">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.amountPaidNow') ?></label>
            <input type="number" name="amount_paid" data-expense-amount-paid value="<?= esc(old('amount_paid', 0)) ?>" step="0.01" min="0" class="<?= esc($inputClass) ?>">
        </div>
        <div class="<?= $showOptionalDetails ? '' : 'hidden' ?>" data-finance-optional>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.attachmentOptional') ?></label>
            <label class="inline-flex w-full cursor-pointer items-center justify-center v3-radius border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                <i class="fas fa-upload me-2"></i><?= lang('Finance.uploadAttachment') ?>
                <input type="file" name="attachment" id="<?= esc($fieldId('expenseAttachmentInput')) ?>" data-expense-attachment-input accept=".pdf,.jpg,.jpeg,.png" class="hidden">
            </label>
            <span id="<?= esc($fieldId('expenseAttachmentName')) ?>" data-expense-attachment-name class="mt-2 block text-xs text-slate-500"></span>
        </div>
    </div>

    <button type="button" data-finance-optional-toggle aria-expanded="<?= $showOptionalDetails ? 'true' : 'false' ?>" class="flex w-full items-center justify-between gap-3 v3-radius border v3-border v3-surface px-4 py-3 text-start text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
        <span class="inline-flex items-center gap-2">
            <i class="fas fa-sliders text-slate-500"></i>
            <?= esc(lang('Finance.optionalDetails')) ?>
        </span>
        <i class="fas fa-chevron-down text-xs transition-transform" data-finance-optional-chevron></i>
    </button>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 <?= $showOptionalDetails ? '' : 'hidden' ?>" data-finance-optional>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.description') ?></label>
            <textarea name="description" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('Finance.expenseDescriptionPlaceholder')) ?>"><?= esc(old('description')) ?></textarea>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.detailsReference') ?></label>
            <textarea name="service_details" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('Finance.detailsReferencePlaceholder')) ?>"><?= esc(old('service_details')) ?></textarea>
        </div>
    </div>

    <div class="<?= $showOptionalDetails ? '' : 'hidden' ?>" data-finance-optional>
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.notes') ?></label>
        <textarea name="notes" rows="3" class="<?= esc($inputClass) ?> resize-y"><?= esc(old('notes')) ?></textarea>
    </div>
</div>
