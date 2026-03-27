<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$baseCurrency = $base_currency ?? 'ILS';
$finance = $finance ?? [];
?>
<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto relative z-10 px-4 py-6">
        <div class="group relative">
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-2xl shadow-orange-500/10 transition-all duration-500 group-hover:shadow-orange-500/20">
                <div class="border-b border-white/20 p-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 blur-lg opacity-75 transition-opacity duration-300 group-hover:opacity-100"></div>
                                <div class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 text-white shadow-xl transition-all duration-300 group-hover:scale-110 group-hover:rotate-3">
                                    <i class="fas fa-pen-to-square text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 transition-colors duration-300 group-hover:text-orange-900"><?= lang('Finance.editExpense') ?></h3>
                                <p class="font-medium text-gray-600"><?= lang('Finance.transactionLabel') ?>: <?= esc($finance['transaction_id'] ?? '') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="<?= base_url('finance/' . (int) ($finance['id'] ?? 0)) ?>" class="group/btn relative inline-flex items-center rounded-xl border-2 border-gray-300 px-6 py-3 text-sm font-bold text-gray-700 transition-all duration-300 hover:scale-105 hover:border-gray-400 hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-gray-500/10 to-slate-500/10 blur opacity-0 transition-opacity duration-300 group-hover/btn:opacity-100"></div>
                                <i class="fas fa-arrow-left relative z-10 mr-2"></i>
                                <span class="relative z-10"><?= lang('Finance.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <form action="<?= base_url('finance/' . (int) ($finance['id'] ?? 0) . '/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-8" data-finance-calc data-base-currency="<?= esc($baseCurrency) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="transaction_type" value="expense">
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-building text-blue-600 mr-2"></i><?= lang('Finance.payeeVendorRequired') ?>
                                </label>
                                <input type="text" name="counterparty_name" value="<?= esc(old('counterparty_name', $finance['counterparty_name'] ?? '')) ?>" maxlength="150" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/20" required>
                            </div>

                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-tags text-purple-600 mr-2"></i><?= lang('Finance.expenseCategoryRequired') ?>
                                </label>
                                <div class="relative">
                                    <select name="expense_category" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-500/20" required>
                                        <option value=""><?= lang('Finance.selectCategory') ?></option>
                                        <?php foreach (($expense_category_options ?? []) as $key => $label): ?>
                                            <option value="<?= esc($key) ?>" <?= old('expense_category', $finance['expense_category'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-dollar-sign text-green-600 mr-2"></i><?= lang('Finance.amountOriginalRequired') ?>
                                </label>
                                <input type="number" id="amountOriginal" name="amount_original" value="<?= esc(old('amount_original', $finance['amount_original'] ?? $finance['amount'] ?? '')) ?>" step="0.01" min="0.01" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-500/20" required>
                            </div>

                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-coins text-indigo-600 mr-2"></i><?= lang('Finance.currencyRequired') ?>
                                </label>
                                <div class="relative">
                                    <select id="currencySelect" name="currency" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20" required>
                                        <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= old('currency', $finance['currency'] ?? $baseCurrency) === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div class="group/field relative" id="exchangeRateField">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-exchange-alt text-blue-600 mr-2"></i><?= lang('Finance.exchangeRateRequired') ?>
                                </label>
                                <input type="number" id="exchangeRate" name="exchange_rate" value="<?= esc(old('exchange_rate', $finance['exchange_rate'] ?? 1)) ?>" step="0.0001" min="0" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/20">
                                <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($baseCurrency) ?>).</p>
                            </div>

                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-calculator text-emerald-600 mr-2"></i><?= lang('Finance.convertedAmount') ?> (<?= esc($baseCurrency) ?>)
                                </label>
                                <input type="text" id="amountConverted" readonly class="w-full rounded-xl border-2 border-gray-200 bg-gray-100 px-4 py-3 text-gray-700 shadow-lg" value="0.00">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-percent text-sky-600 mr-2"></i><?= lang('Finance.discount') ?>
                                </label>
                                <input type="number" id="discountAmount" name="discount_amount" value="<?= esc(old('discount_amount', $finance['discount_amount'] ?? 0)) ?>" step="0.01" min="0" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20">
                            </div>

                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-file-invoice-dollar text-amber-600 mr-2"></i><?= lang('Finance.taxPercent') ?>
                                </label>
                                <input type="number" id="taxPercentage" name="tax_percentage" value="<?= esc(old('tax_percentage', $finance['tax_percentage'] ?? 0)) ?>" step="0.01" min="0" max="100" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/20">
                            </div>

                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-receipt text-rose-600 mr-2"></i><?= lang('Finance.total') ?> (<?= esc($baseCurrency) ?>)
                                </label>
                                <input type="text" id="totalAmountLive" readonly class="w-full rounded-xl border-2 border-rose-200 bg-rose-50 px-4 py-3 font-bold text-rose-700 shadow-lg" value="0.00">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-credit-card text-indigo-600 mr-2"></i><?= lang('Finance.paymentMethodRequired') ?>
                                </label>
                                <div class="relative">
                                    <select id="paymentMethod" name="payment_method" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20" required>
                                        <?php foreach (['cash'=>lang('Finance.cash'),'card'=>lang('Finance.card'),'bank_transfer'=>lang('Finance.bankTransfer'),'check'=>lang('Finance.check'),'insurance'=>lang('Finance.insurance'),'other'=>lang('Finance.other')] as $key => $label): ?>
                                            <option value="<?= esc($key) ?>" <?= old('payment_method', $finance['payment_method'] ?? 'cash') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-calendar text-rose-600 mr-2"></i><?= lang('Finance.expenseDate') ?>
                                </label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(old('paid_date', $finance['paid_date'] ?? date('Y-m-d'))) ?>" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-500/20">
                            </div>
                        </div>

                        <div class="group/field relative">
                            <label class="mb-3 block text-sm font-bold text-gray-700">
                                <i class="fas fa-paperclip text-rose-600 mr-2"></i><?= lang('Finance.attachmentOptional') ?>
                            </label>
                            <div class="rounded-2xl border border-rose-100 bg-rose-50/40 p-5 shadow-lg">
                                <div class="space-y-3">
                                    <p class="text-sm font-medium text-gray-700"><?= lang('Finance.expenseAttachmentHelp') ?></p>
                                    <p class="text-xs text-gray-500"><?= lang('Finance.attachmentFormatsHelp') ?></p>
                                    <?php if (!empty($expense_attachment)): ?>
                                        <div class="flex flex-wrap items-center gap-3 text-sm">
                                            <span class="font-semibold text-gray-700"><?= lang('Finance.existing') ?>:</span>
                                            <a class="text-blue-600 hover:text-blue-800" href="<?= base_url('file/download/' . $expense_attachment['id']) ?>" target="_blank" rel="noopener"><?= esc($expense_attachment['original_name'] ?? lang('Finance.download')) ?></a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <label class="inline-flex w-fit cursor-pointer items-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:bg-rose-700">
                                            <i class="fas fa-upload mr-2"></i><?= lang('Finance.uploadAttachment') ?>
                                            <input type="file" name="attachment" id="expenseAttachmentInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                        </label>
                                        <span id="expenseAttachmentName" class="text-xs text-gray-500"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-align-left text-purple-600 mr-2"></i><?= lang('Finance.description') ?>
                                </label>
                                <textarea name="description" rows="4" class="w-full resize-none rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-500/20"><?= esc(old('description', $finance['description'] ?? '')) ?></textarea>
                            </div>

                            <div class="group/field relative">
                                <label class="mb-3 block text-sm font-bold text-gray-700">
                                    <i class="fas fa-file-lines text-cyan-600 mr-2"></i><?= lang('Finance.detailsReference') ?>
                                </label>
                                <textarea name="service_details" rows="4" class="w-full resize-none rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-500/20"><?= esc(old('service_details', $finance['service_details'] ?? '')) ?></textarea>
                            </div>
                        </div>

                        <div class="group/field relative">
                            <label class="mb-3 block text-sm font-bold text-gray-700">
                                <i class="fas fa-sticky-note text-slate-600 mr-2"></i><?= lang('Finance.notes') ?>
                            </label>
                            <textarea name="notes" rows="4" class="w-full resize-none rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-500/20"><?= esc(old('notes', $finance['notes'] ?? '')) ?></textarea>
                        </div>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
                        <?php endif; ?>

                        <div class="flex flex-col justify-end gap-4 border-t border-white/20 pt-6 sm:flex-row">
                            <a href="<?= base_url('finance/' . (int) ($finance['id'] ?? 0)) ?>" class="group/btn relative inline-flex items-center justify-center rounded-xl border-2 border-gray-300 px-8 py-3 text-sm font-bold text-gray-700 transition-all duration-300 hover:scale-105 hover:border-gray-400 hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-gray-500/10 to-slate-500/10 blur opacity-0 transition-opacity duration-300 group-hover/btn:opacity-100"></div>
                                <i class="fas fa-times relative z-10 mr-2"></i>
                                <span class="relative z-10"><?= lang('Finance.cancel') ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-8 py-3 text-sm font-bold text-white transition-all duration-300 hover:scale-105 hover:from-orange-600 hover:to-rose-600 hover:shadow-2xl hover:shadow-orange-500/25">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-orange-500/20 to-rose-500/20 blur opacity-0 transition-opacity duration-300 group-hover/btn:opacity-100"></div>
                                <i class="fas fa-save relative z-10 mr-2"></i>
                                <span class="relative z-10"><?= lang('Finance.updateExpense') ?></span>
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
    if (!input || !label) {
        return;
    }
    input.addEventListener('change', () => {
        label.textContent = input.files?.length ? input.files[0].name : '';
    });
})();
</script>
<?= $this->endSection() ?>
