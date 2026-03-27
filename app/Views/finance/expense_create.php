<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-orange-500 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900"><?= lang('Finance.newExpense') ?></h1>
                        <p class="text-gray-600"><?= lang('Finance.newExpenseHelp') ?></p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="<?= base_url('finance/create') ?>" class="inline-flex items-center px-4 py-2 rounded-xl border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-semibold">
                        <i class="fas fa-plus-circle mr-2"></i><?= lang('Finance.addIncome') ?>
                    </a>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i><?= lang('Finance.back') ?>
                    </a>
                </div>
            </div>

            <div class="p-8">
                <form action="<?= base_url('finance/expenses/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-8" data-finance-calc data-base-currency="<?= esc($base_currency ?? 'ILS') ?>">
                    <?= csrf_field() ?>
                    <?php if (!empty($return_to)): ?>
                        <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.payeeVendorRequired') ?></label>
                            <input type="text" name="counterparty_name" value="<?= esc(old('counterparty_name')) ?>" maxlength="150" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="<?= esc(lang('Finance.payeeVendorPlaceholder')) ?>" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.expenseCategoryRequired') ?></label>
                            <select name="expense_category" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" required>
                                <option value=""><?= lang('Finance.selectCategory') ?></option>
                                <?php foreach (($expense_category_options ?? []) as $key => $label): ?>
                                    <option value="<?= esc($key) ?>" <?= old('expense_category') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.amountOriginalRequired') ?></label>
                            <input type="number" id="amountOriginal" name="amount_original" value="<?= esc(old('amount_original')) ?>" step="0.01" min="0.01" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.currencyRequired') ?></label>
                            <select id="currencySelect" name="currency" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" required>
                                <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                    <option value="<?= esc($option) ?>" <?= old('currency', $base_currency ?? 'ILS') === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div id="exchangeRateField">
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.exchangeRateRequired') ?></label>
                            <input type="number" id="exchangeRate" name="exchange_rate" value="<?= esc(old('exchange_rate', 1)) ?>" step="0.0001" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl">
                            <p class="text-xs text-gray-500 mt-2"><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($base_currency ?? 'ILS') ?>).</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.convertedAmount') ?> (<?= esc($base_currency ?? 'ILS') ?>)</label>
                            <input type="text" id="amountConverted" readonly class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50" value="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.discount') ?></label>
                            <input type="number" id="discountAmount" name="discount_amount" value="<?= esc(old('discount_amount', 0)) ?>" step="0.01" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.taxPercent') ?></label>
                            <input type="number" id="taxPercentage" name="tax_percentage" value="<?= esc(old('tax_percentage', 0)) ?>" step="0.01" min="0" max="100" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.total') ?> (<?= esc($base_currency ?? 'ILS') ?>)</label>
                            <input type="text" id="totalAmountLive" readonly class="w-full px-4 py-3 border-2 border-rose-200 rounded-xl bg-rose-50 font-bold text-rose-700" value="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.paymentMethodRequired') ?></label>
                            <select id="paymentMethod" name="payment_method" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" required>
                                <option value="cash" <?= old('payment_method', 'cash') === 'cash' ? 'selected' : '' ?>><?= lang('Finance.cash') ?></option>
                                <option value="card" <?= old('payment_method') === 'card' ? 'selected' : '' ?>><?= lang('Finance.card') ?></option>
                                <option value="bank_transfer" <?= old('payment_method') === 'bank_transfer' ? 'selected' : '' ?>><?= lang('Finance.bankTransfer') ?></option>
                                <option value="check" <?= old('payment_method') === 'check' ? 'selected' : '' ?>><?= lang('Finance.check') ?></option>
                                <option value="insurance" <?= old('payment_method') === 'insurance' ? 'selected' : '' ?>><?= lang('Finance.insurance') ?></option>
                                <option value="other" <?= old('payment_method') === 'other' ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.expenseDate') ?></label>
                            <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(old('paid_date', date('Y-m-d'))) ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.description') ?></label>
                            <textarea name="description" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="<?= esc(lang('Finance.expenseDescriptionPlaceholder')) ?>"><?= esc(old('description')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.detailsReference') ?></label>
                            <textarea name="service_details" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="<?= esc(lang('Finance.detailsReferencePlaceholder')) ?>"><?= esc(old('service_details')) ?></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.notes') ?></label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl"><?= esc(old('notes')) ?></textarea>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.attachmentOptional') ?></label>
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm text-gray-600"><?= lang('Finance.expenseAttachmentHelp') ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= lang('Finance.attachmentFormatsHelp') ?></p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <label class="inline-flex cursor-pointer items-center rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                                    <i class="fas fa-paperclip mr-2"></i><?= lang('Finance.uploadAttachment') ?>
                                    <input type="file" name="attachment" id="expenseAttachmentInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                </label>
                                <span id="expenseAttachmentName" class="text-xs text-gray-500"></span>
                            </div>
                        </div>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm font-medium"><?= esc(session()->getFlashdata('error')) ?></div>
                    <?php endif; ?>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50"><?= lang('Finance.cancel') ?></a>
                        <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-gradient-to-r from-rose-500 to-orange-500 text-white font-semibold shadow-lg hover:from-rose-600 hover:to-orange-600">
                            <i class="fas fa-save mr-2"></i><?= lang('Finance.recordExpense') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<script>
(() => {
    const input = document.getElementById('expenseAttachmentInput');
    const label = document.getElementById('expenseAttachmentName');
    if (!input || !label) {
        return;
    }
    input.addEventListener('change', () => {
        label.textContent = input.files?.length ? input.files[0].name : '';
    });
})();
</script>
<?= $this->endSection() ?>
