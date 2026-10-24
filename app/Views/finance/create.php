<?php helper('asset'); ?>
<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">

<?php
    $leadingFieldPadding = 'finance-leading-input';
    $leadingAdornment = 'finance-leading-adornment';
    $leadingAdornmentWide = 'finance-leading-adornment finance-leading-adornment-wide';
?>
<div class="v3-operational-view">
        <div class="v3-operational-body">

                <div class="relative">
            <div class="relative overflow-hidden">
                <!-- Form Header -->
                <div class="v3-flow-command">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">

                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-dollar-sign text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="v3-section-title transition-colors"><?= lang('Finance.newFinancialTransaction') ?></h3>
                                <p class="font-medium"><?= lang('Finance.createFinancialTransactionHelp') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <a href="<?= esc(workflow_back('finance', $return_to ?? null)) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold">

                                <i class="fas fa-arrow-left rtl-back-icon mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="">
                    <form action="<?= base_url('finance/store') ?>" method="POST" enctype="multipart/form-data" class="v3-flow-form " data-finance-calc data-provider-split data-base-currency="<?= esc($base_currency ?? 'ILS') ?>">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient and Transaction Type -->
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user"></i>
                                    <span><?= lang('Finance.patientRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4"
                                            data-searchable-select
                                            data-minimum-input-length="0"
                                            data-limit="5"
                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                            data-placeholder="<?= esc(lang('Finance.searchPatientsPlaceholder')) ?>"
                                            required>
                                        <option value=""><?= lang('Finance.selectPatient') ?></option>
                                        <?php if (!empty($patients)): ?>
                                            <?php foreach ($patients as $patient): ?>
                                                <option value="<?= $patient['id'] ?>" selected>
                                                    <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['phone'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('patient_id') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span><?= lang('Finance.transactionTypeRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="transaction_type" data-force-select2 data-allow-clear="false" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4" required>
                                        <option value=""><?= lang('Finance.selectType') ?></option>
                                        <?php $selectedTransactionType = old('transaction_type', $transaction_type_preset ?? ''); ?>
                                        <option value="payment" <?= ($selectedTransactionType == 'payment') ? 'selected' : '' ?>><?= lang('Finance.payment') ?></option>
                                        <option value="invoice" <?= ($selectedTransactionType == 'invoice') ? 'selected' : '' ?>><?= lang('Finance.invoice') ?></option>
                                        <option value="refund" <?= ($selectedTransactionType == 'refund') ? 'selected' : '' ?>><?= lang('Finance.refund') ?></option>
                                        <option value="adjustment" <?= ($selectedTransactionType == 'adjustment') ? 'selected' : '' ?>><?= lang('Finance.adjustment') ?></option>
                                    </select>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('transaction_type')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('transaction_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?= view('finance/partials/clinical_charge_line_picker', [
                            'clinical_charge_lines' => $clinical_charge_lines ?? [],
                            'selected_clinical_charge_line_ids' => $selected_clinical_charge_line_ids ?? [],
                            'is_rtl' => $is_rtl ?? false,
                        ]) ?>

                        <!-- Examination -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-file-medical"></i>
                                <span><?= lang('Finance.examination') ?></span>
                            </label>
                            <div class="relative">
                                <select name="examination_id" id="examinationSelect" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4"
                                        data-searchable-select
                                        data-minimum-input-length="0"
                                        data-limit="5"
                                        data-depends-on="patientSelect"
                                        data-depends-param="patient_id"
                                        data-require-dependency="true"
                                        data-status="all"
                                        data-search-url="<?= base_url('api/v1/search/examinations') ?>"
                                        data-placeholder="<?= esc(lang('Finance.searchExaminationsPlaceholder')) ?>">
                                    <option value=""><?= lang('Finance.selectExaminationOptional') ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Amount, Currency & <?= lang('Finance.paymentMethod') ?> -->
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span><?= lang('Finance.amountOriginalRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="amount_original" id="amountOriginal" value="<?= old('amount_original') ?>" step="0.01" min="0" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500" placeholder="0.00" required>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('amount_original')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('amount_original') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-coins"></i>
                                    <span><?= lang('Finance.currencyRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="currency" id="currencySelect" data-force-select2 data-allow-clear="false" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4" required>
                                        <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= (old('currency', $base_currency ?? 'ILS') === $option) ? 'selected' : '' ?>>
                                                <?= esc($option) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative" id="exchangeRateField">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span><?= lang('Finance.exchangeRateRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="exchange_rate" id="exchangeRate" value="<?= old('exchange_rate', 1) ?>" step="0.0001" min="0" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4" placeholder="1.0000">
                                </div>
                                <p class="text-xs mt-2"><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($base_currency ?? 'ILS') ?>).</p>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calculator text-emerald-600"></i>
                                    <span><?= lang('Finance.convertedAmount') ?> (<?= esc($base_currency ?? 'ILS') ?>)</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="amountConverted" readonly class="w-full px-4 py-3 border-2" value="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-credit-card"></i>
                                    <span><?= lang('Finance.paymentMethod') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="payment_method" id="paymentMethod" data-force-select2 data-allow-clear="false" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4" required>
                                        <option value=""><?= lang('Finance.selectMethod') ?></option>
                                        <option value="cash" <?= (old('payment_method') == 'cash') ? 'selected' : '' ?>><?= lang('Finance.cash') ?></option>
                                        <option value="card" <?= (old('payment_method') == 'card') ? 'selected' : '' ?>><?= lang('Finance.card') ?></option>
                                        <option value="bank_transfer" <?= (old('payment_method') == 'bank_transfer') ? 'selected' : '' ?>><?= lang('Finance.bankTransfer') ?></option>
                                        <option value="check" <?= (old('payment_method') == 'check') ? 'selected' : '' ?>><?= lang('Finance.check') ?></option>
                                        <option value="other" <?= (old('payment_method') == 'other') ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="relative" id="checkScanField">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-file-upload text-rose-600"></i>
                                    <span><?= lang('Finance.checkScanOptional') ?></span>
                                </label>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    <label class="inline-flex items-center px-4 py-2 bg-rose-600 text-sm font-bold hover:bg-rose-700 cursor-pointer w-fit">
                                        <i class="fas fa-upload mr-2"></i><?= lang('Finance.uploadCheckScan') ?>
                                        <input type="file" name="check_scan" id="checkScanInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                    </label>
                                    <span class="text-xs"><?= lang('Finance.checkScanFormatsHelp') ?></span>
                                    <span id="checkScanName" class="text-xs"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-3">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-check"></i>
                                    <span><?= lang('Finance.transactionDate') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="transaction_date" data-clinic-date="1" value="<?= old('transaction_date', date('Y-m-d')) ?>" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4">
                                    <div class="<?= $leadingAdornment ?>">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                                <p class="text-xs mt-2"><?= lang('Finance.transactionDateHelp') ?></p>
                            </div>
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-rose-600"></i>
                                    <span><?= lang('Finance.paymentDate') ?></span>
                                </label>
                                <div class="relative">
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= old('paid_date', date('Y-m-d')) ?>" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500">
                                    <div class="<?= $leadingAdornment ?>">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-day text-orange-600"></i>
                                    <span><?= lang('Finance.dueDate') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="due_date" data-clinic-date="1" value="<?= old('due_date') ?>" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500">
                                    <div class="<?= $leadingAdornment ?>">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Type -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-stethoscope text-teal-600"></i>
                                <span><?= lang('Finance.serviceTypeRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <select name="service_type" data-force-select2 data-allow-clear="false" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500" required>
                                    <option value=""><?= lang('Finance.selectServiceType') ?></option>
                                    <option value="consultation" <?= (old('service_type') == 'consultation') ? 'selected' : '' ?>><?= lang('Finance.consultation') ?></option>
                                    <option value="treatment" <?= (old('service_type') == 'treatment') ? 'selected' : '' ?>><?= lang('Finance.treatment') ?></option>
                                    <option value="medication" <?= (old('service_type') == 'medication') ? 'selected' : '' ?>><?= lang('Finance.medication') ?></option>
                                    <option value="procedure" <?= (old('service_type') == 'procedure') ? 'selected' : '' ?>><?= lang('Finance.procedure') ?></option>
                                    <option value="other" <?= (old('service_type') == 'other') ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                </select>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('service_type')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('service_type') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- <?= lang('Finance.serviceDetails') ?> -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-notes-medical text-emerald-600"></i>
                                <span><?= lang('Finance.serviceDetails') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="service_details" rows="3" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none" placeholder="<?= esc(lang('Finance.serviceDetailsPlaceholder')) ?>"><?= old('service_details') ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>



                        <!-- Discount and Tax Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-percentage"></i>
                                    <span><?= lang('Finance.discountAmount') ?></span>
                                </label>
                                <div class="relative">
                                    <div class="<?= $leadingAdornmentWide ?>">
                                        <span class="font-bold"><?= getCurrencySymbol() ?></span>
                                    </div>
                                    <input type="number" name="discount_amount" id="discountAmount" value="<?= old('discount_amount', 0) ?>" step="0.01" min="0" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4" placeholder="0.00">
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-percent"></i>
                                    <span><?= lang('Finance.taxPercentage') ?></span>
                                </label>
                                <div class="relative">
                                    <div class="<?= $leadingAdornmentWide ?>">
                                        <span class="font-bold">%</span>
                                    </div>
                                    <input type="number" name="tax_percentage" id="taxPercentage" value="<?= old('tax_percentage', 0) ?>" step="0.01" min="0" max="100" class="w-full <?= $leadingFieldPadding ?> py-3 border-2 focus:outline-none focus:ring-4" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Live Total -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-receipt text-emerald-600"></i>
                                <span><?= lang('Finance.total') ?> (<?= esc($base_currency ?? 'ILS') ?>)</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="totalAmountLive" readonly class="w-full px-4 py-3 bg-emerald-50 border-2 border-emerald-200 text-emerald-700 font-bold" value="0.00">
                            </div>
                            <p class="text-xs mt-2"><?= lang('Finance.totalFormulaHelp') ?></p>
                        </div>

                        <?php if (!empty($can_initial_receipt)): ?>
                            <fieldset class="border p-4 space-y-3" data-initial-receipt-section>
                                <legend class="text-sm font-bold"><?= esc(lang('Finance.checkoutTitle')) ?></legend>
                                <input type="hidden" name="checkout_request_key" value="<?= esc(old('checkout_request_key', $checkout_request_key ?? '')) ?>">
                                <label class="flex items-center gap-3">
                                    <input type="checkbox" name="initial_receipt" value="1" data-initial-receipt <?= old('initial_receipt') === '1' ? 'checked' : '' ?>>
                                    <span><?= esc(lang('Finance.checkoutTitle')) ?></span>
                                </label>
                                <p class="text-sm"><?= esc(lang('Finance.checkoutHelp')) ?></p>
                                <label class="block text-sm font-bold" for="initialPaymentAmount"><?= esc(lang('Finance.checkoutAmount')) ?></label>
                                <input id="initialPaymentAmount" type="number" inputmode="decimal" name="initial_payment_amount" min="0.01" step="0.01" value="<?= esc(old('initial_payment_amount')) ?>" class="w-full px-4 py-3 border" data-initial-payment-amount>
                            </fieldset>
                        <?php endif; ?>

                        <?= view('finance/partials/provider_split_section', [
                            'clinic_providers' => $clinic_providers ?? [],
                            'provider_payouts' => [],
                            'is_rtl' => $is_rtl ?? false,
                        ]) ?>

                        <!-- Description -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-align-left"></i>
                                <span><?= lang('Finance.description') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="description" rows="4" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 resize-none" placeholder="<?= esc(lang('Finance.transactionDescriptionPlaceholder')) ?>"><?= old('description') ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note"></i>
                                <span><?= lang('Finance.notes') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 resize-none" placeholder="<?= esc(lang('Finance.additionalNotesPlaceholder')) ?>"><?= old('notes') ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t">
                            <a href="<?= esc(workflow_cancel('finance', $return_to ?? null)) ?>" class="relative inline-flex items-center justify-center px-8 py-3 border-2 text-sm font-bold">

                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.cancel') ?></span>
                            </a>
                            <button type="submit" class="relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold">

                                <i class="fas fa-dollar-sign mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.createTransaction') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= asset_version('assets/js/finance-calc.js') ?>"></script>
</div>
<?= $this->endSection() ?>



