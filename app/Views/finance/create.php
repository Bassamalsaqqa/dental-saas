<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<style>
.toggle-track {
    transition: background-color 0.2s ease;
}

.toggle-dot {
    transition: transform 0.2s ease;
}
</style>
<!-- Enhanced Finance Creation with Advanced Design -->
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
                                    <i class="fas fa-dollar-sign text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-green-900 transition-colors duration-300"><?= lang('Finance.newFinancialTransaction') ?></h3>
                                <p class="text-gray-600 font-medium"><?= lang('Finance.createFinancialTransactionHelp') ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <?php
                                $showBackToPatient = !empty($patients) && (empty($return_to) || preg_match('#^patient/\\d+(\\?|$)#', (string) $return_to));
                            ?>
                            <?php if ($showBackToPatient): ?>
                                <a href="<?= base_url('patient/' . $patients[0]['id']) ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-blue-600 text-white text-sm font-bold rounded-xl hover:from-indigo-600 hover:to-blue-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-indigo-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 to-blue-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                    <i class="fas fa-user mr-2 relative z-10"></i>
                                    <span class="relative z-10"><?= lang('Finance.backToPatient') ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('finance') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= base_url('finance/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-8" data-finance-calc data-provider-split data-base-currency="<?= esc($base_currency ?? 'ILS') ?>">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient and Transaction Type -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                    <span><?= lang('Finance.patientRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
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
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <span><?= lang('Finance.transactionTypeRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="transaction_type" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value=""><?= lang('Finance.selectType') ?></option>
                                        <option value="payment" <?= (old('transaction_type') == 'payment') ? 'selected' : '' ?>><?= lang('Finance.payment') ?></option>
                                        <option value="invoice" <?= (old('transaction_type') == 'invoice') ? 'selected' : '' ?>><?= lang('Finance.invoice') ?></option>
                                        <option value="refund" <?= (old('transaction_type') == 'refund') ? 'selected' : '' ?>><?= lang('Finance.refund') ?></option>
                                        <option value="adjustment" <?= (old('transaction_type') == 'adjustment') ? 'selected' : '' ?>><?= lang('Finance.adjustment') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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

                        <!-- Examination -->
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
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Amount, Currency & <?= lang('Finance.paymentMethod') ?> -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span><?= lang('Finance.amountOriginalRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="amount_original" id="amountOriginal" value="<?= old('amount_original') ?>" step="0.01" min="0" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00" required>
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
                                    <span><?= lang('Finance.currencyRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="currency" id="currencySelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" required>
                                        <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= (old('currency', $base_currency ?? 'ILS') === $option) ? 'selected' : '' ?>>
                                                <?= esc($option) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <input type="number" name="exchange_rate" id="exchangeRate" value="<?= old('exchange_rate', 1) ?>" step="0.0001" min="0" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" placeholder="1.0000">
                                </div>
                                <p class="text-xs text-gray-500 mt-2"><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($base_currency ?? 'ILS') ?>).</p>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calculator text-emerald-600"></i>
                                    <span><?= lang('Finance.convertedAmount') ?> (<?= esc($base_currency ?? 'ILS') ?>)</span>
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
                                    <span><?= lang('Finance.paymentMethod') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                                </label>
                                <div class="relative">
                                    <select name="payment_method" id="paymentMethod" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value=""><?= lang('Finance.selectMethod') ?></option>
                                        <option value="cash" <?= (old('payment_method') == 'cash') ? 'selected' : '' ?>><?= lang('Finance.cash') ?></option>
                                        <option value="card" <?= (old('payment_method') == 'card') ? 'selected' : '' ?>><?= lang('Finance.card') ?></option>
                                        <option value="bank_transfer" <?= (old('payment_method') == 'bank_transfer') ? 'selected' : '' ?>><?= lang('Finance.bankTransfer') ?></option>
                                        <option value="check" <?= (old('payment_method') == 'check') ? 'selected' : '' ?>><?= lang('Finance.check') ?></option>
                                        <option value="other" <?= (old('payment_method') == 'other') ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-rose-600"></i>
                                    <span><?= lang('Finance.paymentDate') ?></span>
                                </label>
                                <div class="relative">
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= old('paid_date', date('Y-m-d')) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <input type="text" name="due_date" data-clinic-date="1" value="<?= old('due_date') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Type -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-stethoscope text-teal-600"></i>
                                <span><?= lang('Finance.serviceTypeRequired') ?></span><span class="required-indicator notranslate" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <select name="service_type" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl" required>
                                    <option value=""><?= lang('Finance.selectServiceType') ?></option>
                                    <option value="consultation" <?= (old('service_type') == 'consultation') ? 'selected' : '' ?>><?= lang('Finance.consultation') ?></option>
                                    <option value="treatment" <?= (old('service_type') == 'treatment') ? 'selected' : '' ?>><?= lang('Finance.treatment') ?></option>
                                    <option value="medication" <?= (old('service_type') == 'medication') ? 'selected' : '' ?>><?= lang('Finance.medication') ?></option>
                                    <option value="procedure" <?= (old('service_type') == 'procedure') ? 'selected' : '' ?>><?= lang('Finance.procedure') ?></option>
                                    <option value="other" <?= (old('service_type') == 'other') ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('service_type')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('service_type') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- <?= lang('Finance.serviceDetails') ?> -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-notes-medical text-emerald-600"></i>
                                <span><?= lang('Finance.serviceDetails') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="service_details" rows="3" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= esc(lang('Finance.serviceDetailsPlaceholder')) ?>"><?= old('service_details') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>



                        <!-- Discount and Tax Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-percentage text-blue-600"></i>
                                    <span><?= lang('Finance.discountAmount') ?></span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold"><?= getCurrencySymbol() ?></span>
                                    </div>
                                    <input type="number" name="discount_amount" id="discountAmount" value="<?= old('discount_amount', 0) ?>" step="0.01" min="0" class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold">%</span>
                                    </div>
                                    <input type="number" name="tax_percentage" id="taxPercentage" value="<?= old('tax_percentage', 0) ?>" step="0.01" min="0" max="100" class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-percent text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Total -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-receipt text-emerald-600"></i>
                                <span><?= lang('Finance.total') ?> (<?= esc($base_currency ?? 'ILS') ?>)</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="totalAmountLive" readonly class="w-full px-4 py-3 bg-emerald-50 border-2 border-emerald-200 rounded-xl shadow-lg text-emerald-700 font-bold" value="0.00">
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><?= lang('Finance.totalFormulaHelp') ?></p>
                        </div>

                        <!-- <?= lang('Finance.providerSplit') ?> -->
                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-6 space-y-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-base font-bold text-indigo-900 flex items-center gap-2">
                                        <i class="fas fa-hand-holding-dollar text-indigo-600"></i>
                                        <?= lang('Finance.providerSplit') ?>
                                    </h4>
                                    <p class="text-xs text-indigo-700/80"><?= lang('Finance.providerSplitHelp') ?></p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="providerSplitEnabled" name="provider_split_enabled" value="1" class="sr-only" <?= old('provider_split_enabled') ? 'checked' : '' ?>>
                                    <div id="providerSplitTrack" class="toggle-track w-11 h-6 bg-gray-200 rounded-full relative">
                                        <div id="providerSplitDot" class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-semibold text-indigo-900"><?= lang('Finance.enable') ?></span>
                                </label>
                            </div>

                            <div id="providerSplitFields" class="<?= old('provider_split_enabled') ? 'space-y-5' : 'hidden space-y-5' ?>">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.providerType') ?></label>
                                        <select id="providerType" name="provider_type" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                                            <option value="user" <?= old('provider_type', 'user') === 'user' ? 'selected' : '' ?>><?= lang('Finance.clinicUser') ?></option>
                                            <option value="guest" <?= old('provider_type') === 'guest' ? 'selected' : '' ?>><?= lang('Finance.guestProvider') ?></option>
                                        </select>
                                    </div>
                                    <div id="providerUserWrap">
                                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.providerUser') ?></label>
                                        <select id="providerUserId" name="provider_user_id" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                                            <option value=""><?= lang('Finance.selectUser') ?></option>
                                            <?php foreach (($clinic_providers ?? []) as $provider): ?>
                                                <option value="<?= (int) $provider['id'] ?>" <?= (string) old('provider_user_id') === (string) $provider['id'] ? 'selected' : '' ?>>
                                                    <?= esc(trim(($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? ''))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div id="providerGuestWrap">
                                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.guestProviderName') ?></label>
                                        <input type="text" id="providerGuestName" name="provider_name_snapshot" value="<?= esc(old('provider_name_snapshot')) ?>" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="<?= esc(lang('Finance.guestProviderPlaceholder')) ?>">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.splitMode') ?></label>
                                        <select id="providerSplitMode" name="provider_split_mode" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                                            <option value="percent" <?= old('provider_split_mode', 'percent') === 'percent' ? 'selected' : '' ?>><?= lang('Finance.percentage') ?></option>
                                            <option value="fixed" <?= old('provider_split_mode') === 'fixed' ? 'selected' : '' ?>><?= lang('Finance.fixedAmount') ?></option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.splitValue') ?></label>
                                        <input type="number" id="providerSplitValue" name="provider_split_value" value="<?= esc(old('provider_split_value', '70')) ?>" step="0.01" min="0" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="rounded-xl bg-white border border-indigo-100 p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= lang('Finance.grossCollected') ?></p>
                                        <p class="mt-1 text-lg font-bold text-gray-900" id="splitGrossPreview">0.00</p>
                                    </div>
                                    <div class="rounded-xl bg-white border border-indigo-100 p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= lang('Finance.providerShare') ?></p>
                                        <p class="mt-1 text-lg font-bold text-indigo-700" id="splitProviderPreview">0.00</p>
                                    </div>
                                    <div class="rounded-xl bg-white border border-indigo-100 p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= lang('Finance.clinicNet') ?></p>
                                        <p class="mt-1 text-lg font-bold text-emerald-700" id="splitClinicPreview">0.00</p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-indigo-200 bg-white p-4 space-y-4">
                                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-800">
                                        <input type="checkbox" id="providerMarkPaidNow" name="provider_mark_paid_now" value="1" class="h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500" <?= old('provider_mark_paid_now') ? 'checked' : '' ?>>
                                        <?= lang('Finance.markProviderPaidNow') ?>
                                    </label>

                                    <div id="providerPaidNowFields" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Finance.paidAmount') ?></label>
                                            <input type="number" id="providerPaidAmount" name="provider_paid_amount" value="<?= esc(old('provider_paid_amount')) ?>" step="0.01" min="0" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Finance.method') ?></label>
                                            <select id="providerPaidMethod" name="provider_paid_method" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl">
                                                <option value="cash" <?= old('provider_paid_method', 'cash') === 'cash' ? 'selected' : '' ?>><?= lang('Finance.cash') ?></option>
                                                <option value="card" <?= old('provider_paid_method') === 'card' ? 'selected' : '' ?>><?= lang('Finance.card') ?></option>
                                                <option value="bank_transfer" <?= old('provider_paid_method') === 'bank_transfer' ? 'selected' : '' ?>><?= lang('Finance.bankTransfer') ?></option>
                                                <option value="check" <?= old('provider_paid_method') === 'check' ? 'selected' : '' ?>><?= lang('Finance.check') ?></option>
                                                <option value="other" <?= old('provider_paid_method') === 'other' ? 'selected' : '' ?>><?= lang('Finance.other') ?></option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Finance.paidDate') ?></label>
                                            <input type="text" id="providerPaidDate" name="provider_paid_date" data-clinic-date="1" value="<?= esc(old('provider_paid_date', date('Y-m-d'))) ?>" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-align-left text-purple-600"></i>
                                <span><?= lang('Finance.description') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="description" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= esc(lang('Finance.transactionDescriptionPlaceholder')) ?>"><?= old('description') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
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
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= esc(lang('Finance.additionalNotesPlaceholder')) ?>"><?= old('notes') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('finance') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Finance.cancel') ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-green-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
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

<script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const splitToggle = document.getElementById('providerSplitEnabled');
    const splitFields = document.getElementById('providerSplitFields');
    const splitTrack = document.getElementById('providerSplitTrack');
    const splitDot = document.getElementById('providerSplitDot');
    const providerType = document.getElementById('providerType');
    const providerUserWrap = document.getElementById('providerUserWrap');
    const providerGuestWrap = document.getElementById('providerGuestWrap');
    const providerUserId = document.getElementById('providerUserId');
    const providerGuestName = document.getElementById('providerGuestName');
    const splitMode = document.getElementById('providerSplitMode');
    const splitValue = document.getElementById('providerSplitValue');
    const grossInput = document.getElementById('totalAmountLive');
    const grossPreview = document.getElementById('splitGrossPreview');
    const providerPreview = document.getElementById('splitProviderPreview');
    const clinicPreview = document.getElementById('splitClinicPreview');
    const markPaidNow = document.getElementById('providerMarkPaidNow');
    const paidNowFields = document.getElementById('providerPaidNowFields');
    const providerPaidAmount = document.getElementById('providerPaidAmount');
    const providerPaidMethod = document.getElementById('providerPaidMethod');
    const providerPaidDate = document.getElementById('providerPaidDate');

    const parseNum = (val) => {
        const n = parseFloat(val || '0');
        return Number.isFinite(n) ? n : 0;
    };

    const setVisible = (el, visible) => {
        if (!el) return;
        if (visible) {
            el.classList.remove('hidden');
            el.style.display = '';
        } else {
            el.classList.add('hidden');
            el.style.display = 'none';
        }
    };

    function computeShares() {
        const gross = parseNum(grossInput?.value);
        const mode = splitMode?.value || 'percent';
        const raw = parseNum(splitValue?.value);
        let providerShare = 0;

        if (mode === 'fixed') {
            providerShare = Math.max(0, Math.min(raw, gross));
        } else {
            const pct = Math.max(0, Math.min(raw, 100));
            providerShare = gross * (pct / 100);
        }

        providerShare = Math.max(0, Math.min(providerShare, gross));
        return {
            gross,
            providerShare,
            clinicShare: Math.max(0, gross - providerShare),
        };
    }

    function updateProviderTypeVisibility() {
        const type = providerType?.value || 'user';
        setVisible(providerUserWrap, type === 'user');
        setVisible(providerGuestWrap, type === 'guest');
        if (providerUserId) providerUserId.required = !!splitToggle?.checked && type === 'user';
        if (providerGuestName) providerGuestName.required = !!splitToggle?.checked && type === 'guest';
    }

    function updateSplitPreview() {
        const { gross, providerShare, clinicShare } = computeShares();
        if (grossPreview) grossPreview.textContent = gross.toFixed(2);
        if (providerPreview) providerPreview.textContent = providerShare.toFixed(2);
        if (clinicPreview) clinicPreview.textContent = clinicShare.toFixed(2);

        if (providerPaidAmount && markPaidNow?.checked && splitToggle?.checked) {
            providerPaidAmount.value = providerShare.toFixed(2);
        }
    }

    function updatePaidNowSection() {
        const active = !!markPaidNow?.checked && !!splitToggle?.checked;
        setVisible(paidNowFields, active);
        if (providerPaidAmount) providerPaidAmount.required = active;
        if (providerPaidMethod) providerPaidMethod.required = active;
        if (providerPaidDate) providerPaidDate.required = active;
    }

    function syncSplitToggleState() {
        if (!splitToggle || !splitFields) {
            return;
        }

        if (splitToggle.checked) {
            splitFields.classList.remove('hidden');
            splitFields.style.display = '';
            splitTrack?.classList.remove('bg-gray-200');
            splitTrack?.classList.add('bg-blue-500');
            if (splitDot) {
                splitDot.style.transform = 'translateX(20px)';
            }
        } else {
            splitFields.classList.add('hidden');
            splitFields.style.display = 'none';
            splitTrack?.classList.remove('bg-blue-500');
            splitTrack?.classList.add('bg-gray-200');
            if (splitDot) {
                splitDot.style.transform = 'translateX(0)';
            }
        }

        if (splitMode) splitMode.required = splitToggle.checked;
        if (splitValue) splitValue.required = splitToggle.checked;
        updateProviderTypeVisibility();
        updatePaidNowSection();
        updateSplitPreview();
    }

    if (splitToggle) {
        splitToggle.addEventListener('change', syncSplitToggleState);
    }

    providerType?.addEventListener('change', updateProviderTypeVisibility);
    splitMode?.addEventListener('change', updateSplitPreview);
    splitValue?.addEventListener('input', updateSplitPreview);
    splitValue?.addEventListener('change', updateSplitPreview);
    markPaidNow?.addEventListener('change', () => {
        updatePaidNowSection();
        updateSplitPreview();
    });

    const recalcTriggers = [
        document.getElementById('amountOriginal'),
        document.getElementById('exchangeRate'),
        document.getElementById('currencySelect'),
        document.getElementById('discountAmount'),
        document.getElementById('taxPercentage'),
        grossInput,
    ];
    recalcTriggers.forEach((el) => {
        if (!el) return;
        ['input', 'change', 'keyup', 'blur'].forEach((evt) => {
            el.addEventListener(evt, () => setTimeout(updateSplitPreview, 0));
        });
    });

    document.querySelector('form[data-finance-calc]')?.addEventListener('finance:totalsUpdated', updateSplitPreview);
    syncSplitToggleState();
});
</script>
<?= $this->endSection() ?>


