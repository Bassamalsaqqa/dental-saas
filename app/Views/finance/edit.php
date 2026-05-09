<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<style>
.toggle-track {
    transition: background-color 0.2s ease;
}

.toggle-dot {
    transition: transform 0.2s ease;
}

.finance-leading-adornment {
    position: absolute;
    inset-block: 0;
    display: flex;
    align-items: center;
    pointer-events: none;
}

[dir="ltr"] .finance-leading-adornment {
    left: 0;
    right: auto;
    padding-left: 0.75rem;
}

[dir="rtl"] .finance-leading-adornment {
    right: 0;
    left: auto;
    padding-right: 0.75rem;
}

[dir="ltr"] .finance-leading-adornment-wide {
    padding-left: 1rem;
}

[dir="rtl"] .finance-leading-adornment-wide {
    padding-right: 1rem;
}

[dir="ltr"] .finance-leading-input {
    padding-left: 2.5rem !important;
    padding-right: 1rem !important;
}

[dir="rtl"] .finance-leading-input {
    padding-right: 2.5rem !important;
    padding-left: 1rem !important;
}

[dir="rtl"] input[data-clinic-date="1"].finance-leading-input {
    padding-right: 3.25rem !important;
}
</style>
<?php
    $leadingFieldPadding = 'finance-leading-input';
    $leadingAdornment = 'finance-leading-adornment';
    $leadingAdornmentWide = 'finance-leading-adornment finance-leading-adornment-wide';
?>
<!-- Enhanced Finance Edit with Advanced Design -->
<div class="min-h-screen bg-slate-50">
    <!-- Animated Background Elements -->
    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Finance Form with Glassmorphism -->
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-edit text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-green-900 transition-colors duration-300"><?= lang('Finance.editFinancialTransaction') ?></h3>
                                <p class="text-gray-600 font-medium"><?= lang('Finance.updateTransactionDetailsFor') ?> <?= $finance['transaction_id'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= esc(workflow_back('finance/' . $finance['id'], $return_to ?? null)) ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= base_url('finance/' . $finance['id'] . '/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-8" data-finance-calc data-provider-split data-base-currency="<?= esc($baseCurrency ?? $base_currency ?? 'ILS') ?>">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient and Transaction Type -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                    <span><?= lang('Finance.patientRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
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
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('patient_id') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-exchange-alt text-purple-600"></i>
                                    <span><?= lang('Finance.transactionTypeRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <select data-force-select2 data-allow-clear="false" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" required disabled>
                                        <option value=""><?= lang('Finance.selectType') ?></option>
                                        <option value="payment" <?= (old('transaction_type', $finance['transaction_type']) == 'payment') ? 'selected' : '' ?>><?= lang('Finance.payment') ?></option>
                                        <option value="invoice" <?= (old('transaction_type', $finance['transaction_type']) == 'invoice') ? 'selected' : '' ?>><?= lang('Finance.invoice') ?></option>
                                        <option value="refund" <?= (old('transaction_type', $finance['transaction_type']) == 'refund') ? 'selected' : '' ?>><?= lang('Finance.refund') ?></option>
                                        <option value="adjustment" <?= (old('transaction_type', $finance['transaction_type']) == 'adjustment') ? 'selected' : '' ?>><?= lang('Finance.adjustment') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('transaction_type')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('transaction_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <input type="hidden" name="transaction_type" value="<?= esc(old('transaction_type', $finance['transaction_type'])) ?>">

                        <!-- Examination Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-file-medical text-indigo-600"></i>
                                <span><?= lang('Finance.examination') ?></span>
                            </label>
                            <div class="relative">
                                <select name="examination_id" id="examinationSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl"
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
                                    <?php if (!empty($examinations)): ?>
                                        <?php foreach ($examinations as $examination): ?>
                                            <option value="<?= $examination['id'] ?>" selected>
                                                #<?= $examination['id'] ?> - <?= $examination['examination_type'] ?> (<?= $examination['created_at'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <?php
                            $baseCurrency = $base_currency ?? 'ILS';
                            $amountOriginal = old('amount_original', $finance['amount_original'] ?? $finance['amount']);
                            $exchangeRate = old('exchange_rate', $finance['exchange_rate'] ?? 1);
                            $selectedCurrency = old('currency', $finance['currency'] ?? $baseCurrency);
                        ?>

                        <!-- Amount, Currency & <?= lang('Finance.paymentMethod') ?> -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span><?= lang('Finance.amountOriginalRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="amount_original" id="amountOriginal" value="<?= esc($amountOriginal) ?>" step="0.01" min="0" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00" required>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('amount_original')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('amount_original') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-coins text-indigo-600"></i>
                                    <span><?= lang('Finance.currencyRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <select name="currency" id="currencySelect" data-force-select2 data-allow-clear="false" class="w-full <?= $leadingFieldPadding ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" required>
                                        <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= ($selectedCurrency === $option) ? 'selected' : '' ?>>
                                                <?= esc($option) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative" id="exchangeRateField">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-exchange-alt text-blue-600"></i>
                                    <span><?= lang('Finance.exchangeRateRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="exchange_rate" id="exchangeRate" value="<?= esc($exchangeRate) ?>" step="0.0001" min="0" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" placeholder="1.0000">
                                </div>
                                <p class="text-xs text-gray-500 mt-2"><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($baseCurrency) ?>).</p>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calculator text-emerald-600"></i>
                                    <span><?= lang('Finance.convertedAmount') ?> (<?= esc($baseCurrency) ?>)</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="amountConverted" readonly class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl shadow-lg text-gray-700" value="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-credit-card text-indigo-600"></i>
                                    <span><?= lang('Finance.paymentMethod') ?></span>
                                </label>
                                <div class="relative">
                                    <select name="payment_method" id="paymentMethod" data-force-select2 data-allow-clear="false" class="w-full <?= $leadingFieldPadding ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl">
                                        <option value=""><?= lang('Finance.selectMethod') ?></option>
                                        <option value="cash" <?= (old('payment_method', $finance['payment_method']) == 'cash') ? 'selected' : '' ?>><?= lang('Finance.cash') ?></option>
                                        <option value="card" <?= (old('payment_method', $finance['payment_method']) == 'card') ? 'selected' : '' ?>><?= lang('Finance.card') ?></option>
                                        <option value="bank_transfer" <?= (old('payment_method', $finance['payment_method']) == 'bank_transfer') ? 'selected' : '' ?>><?= lang('Finance.bankTransfer') ?></option>
                                        <option value="check" <?= (old('payment_method', $finance['payment_method']) == 'check') ? 'selected' : '' ?>><?= lang('Finance.check') ?></option>
                                        <option value="other" <?= (old('payment_method', $finance['payment_method']) == 'other') ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group/field relative" id="checkScanField">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-file-upload text-rose-600"></i>
                                    <span><?= lang('Finance.checkScanOptional') ?></span>
                                </label>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    <label class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-bold rounded-xl shadow-lg hover:bg-rose-700 cursor-pointer transition-all w-fit">
                                        <i class="fas fa-upload mr-2"></i><?= lang('Finance.uploadCheckScan') ?>
                                        <input type="file" name="check_scan" id="checkScanInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                    </label>
                                    <span class="text-xs text-gray-500"><?= lang('Finance.checkScanFormatsHelp') ?></span>
                                    <span id="checkScanName" class="text-xs text-gray-400"></span>
                                </div>
                                <?php if (!empty($check_attachment)): ?>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <?= lang('Finance.existing') ?>: <a class="text-blue-600 hover:text-blue-800" href="<?= base_url('file/download/' . $check_attachment['id']) ?>"><?= lang('Finance.download') ?></a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-check text-blue-600"></i>
                                    <span><?= lang('Finance.transactionDate') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="transaction_date" data-clinic-date="1" value="<?= old('transaction_date', $finance['transaction_date'] ?? $finance['paid_date'] ?? date('Y-m-d')) ?>" class="w-full <?= $leadingFieldPadding ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="<?= $leadingAdornment ?>">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2"><?= lang('Finance.transactionDateHelp') ?></p>
                            </div>
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-rose-600"></i>
                                    <span><?= lang('Finance.paidDate') ?></span>
                                </label>
                                <div class="relative">
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= old('paid_date', $finance['paid_date']) ?>" class="w-full <?= $leadingFieldPadding ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="<?= $leadingAdornment ?>">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-day text-orange-600"></i>
                                    <span><?= lang('Finance.dueDate') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="due_date" data-clinic-date="1" value="<?= old('due_date', $finance['due_date']) ?>" class="w-full <?= $leadingFieldPadding ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="<?= $leadingAdornment ?>">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Type -->
                        <div class="grid grid-cols-1 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tools text-purple-600"></i>
                                    <span><?= lang('Finance.serviceTypeRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <select name="service_type" data-force-select2 data-allow-clear="false" class="w-full <?= $leadingFieldPadding ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value=""><?= lang('Finance.selectServiceType') ?></option>
                                        <option value="consultation" <?= (old('service_type', $finance['service_type']) == 'consultation') ? 'selected' : '' ?>><?= lang('Finance.consultation') ?></option>
                                        <option value="treatment" <?= (old('service_type', $finance['service_type']) == 'treatment') ? 'selected' : '' ?>><?= lang('Finance.treatment') ?></option>
                                        <option value="medication" <?= (old('service_type', $finance['service_type']) == 'medication') ? 'selected' : '' ?>><?= lang('Finance.medication') ?></option>
                                        <option value="procedure" <?= (old('service_type', $finance['service_type']) == 'procedure') ? 'selected' : '' ?>><?= lang('Finance.procedure') ?></option>
                                        <option value="other" <?= (old('service_type', $finance['service_type']) == 'other') ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

        <!-- <?= lang('Finance.serviceDetails') ?> -->
        <div class="group/field relative">
            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                <i class="fas fa-notes-medical text-emerald-600"></i>
                <span><?= lang('Finance.serviceDetails') ?></span>
            </label>
            <div class="relative">
                <textarea name="service_details" rows="3" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= esc(lang('Finance.serviceDetailsPlaceholder')) ?>"><?= old('service_details', $finance['service_details']) ?></textarea>
                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-gray-400 text-xs">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        </div>

        <?php
            $taxPercent = $finance['tax_percentage'] ?? 0;
            $taxableBase = max(0, floatval($finance['amount']) - floatval($finance['discount_amount'] ?? 0));
            if (!$taxPercent && $taxableBase > 0 && !empty($finance['tax_amount'])) {
                $taxPercent = round((floatval($finance['tax_amount']) / $taxableBase) * 100, 2);
            }
        ?>

        <!-- Discount and Tax Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="group/field relative">
                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                    <i class="fas fa-percentage text-blue-600"></i>
                    <span><?= lang('Finance.discountAmount') ?></span>
                </label>
                <div class="relative">
                    <div class="<?= $leadingAdornmentWide ?>">
                        <span class="text-gray-500 font-bold"><?= getCurrencySymbol() ?></span>
                    </div>
                    <input type="number" name="discount_amount" id="discountAmount" value="<?= old('discount_amount', $finance['discount_amount'] ?? 0) ?>" step="0.01" min="0" class="w-full <?= !empty($is_rtl) ? 'pr-10 pl-10' : 'pl-10 pr-10' ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00">
                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                        <i class="fas fa-percentage text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="group/field relative">
                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                    <i class="fas fa-percent text-indigo-600"></i>
                    <span><?= lang('Finance.taxPercentage') ?></span>
                </label>
                <div class="relative">
                    <div class="<?= $leadingAdornmentWide ?>">
                        <span class="text-gray-500 font-bold">%</span>
                    </div>
                    <input type="number" name="tax_percentage" id="taxPercentage" value="<?= old('tax_percentage', $taxPercent) ?>" step="0.01" min="0" max="100" class="w-full <?= !empty($is_rtl) ? 'pr-10 pl-10' : 'pl-10 pr-10' ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00">
                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                        <i class="fas fa-percent text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Total -->
        <div class="group/field relative">
            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                <i class="fas fa-receipt text-emerald-600"></i>
                <span><?= lang('Finance.total') ?> (<?= esc($baseCurrency) ?>)</span>
            </label>
            <div class="relative">
                <input type="text" id="totalAmountLive" readonly class="w-full px-4 py-3 bg-emerald-50 border-2 border-emerald-200 rounded-xl shadow-lg text-emerald-700 font-bold" value="0.00">
            </div>
            <p class="text-xs text-gray-500 mt-2"><?= lang('Finance.totalFormulaHelp') ?></p>
        </div>

        <?= view('finance/partials/provider_split_section', [
            'clinic_providers' => $clinic_providers ?? [],
            'provider_payouts' => $provider_payouts ?? [],
            'is_rtl' => $is_rtl ?? false,
        ]) ?>

        <!-- Description -->
        <div class="group/field relative">
            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                <i class="fas fa-align-left text-purple-600"></i>
                <span><?= lang('Finance.description') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="description" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= esc(lang('Finance.transactionDescriptionPlaceholder')) ?>"><?= old('description', $finance['description']) ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-cyan-600"></i>
                                <span><?= lang('Finance.notes') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= esc(lang('Finance.additionalNotesPlaceholder')) ?>"><?= old('notes', $finance['notes']) ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= esc(workflow_cancel('finance/' . $finance['id'], $return_to ?? ('finance/' . $finance['id']))) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.cancel') ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-green-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-save mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.updateTransaction') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<?= $this->endSection() ?>



