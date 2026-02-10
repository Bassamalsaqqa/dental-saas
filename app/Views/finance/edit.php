<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Finance Edit with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Finance Form with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 overflow-hidden">
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
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-green-900 transition-colors duration-300">Edit Financial Transaction</h3>
                                <p class="text-gray-600 font-medium">Update transaction details for <?= $finance['transaction_id'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('finance') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Finance</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= base_url('finance/' . $finance['id'] . '/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-8" data-finance-calc data-base-currency="<?= esc($baseCurrency ?? $base_currency ?? 'ILS') ?>">
                        <?= csrf_field() ?>
                        <!-- Patient and Transaction Type -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                    <span>Patient *</span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
                                            data-searchable-select 
                                            data-minimum-input-length="0"
                                            data-limit="5"
                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                            data-placeholder="Search patients by name, phone, or ID..."
                                            required>
                                        <option value="">Select a patient</option>
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
                                    <span>Transaction Type *</span>
                                </label>
                                <div class="relative">
                                    <select name="transaction_type" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Select type</option>
                                        <option value="payment" <?= (old('transaction_type', $finance['transaction_type']) == 'payment') ? 'selected' : '' ?>>Payment</option>
                                        <option value="invoice" <?= (old('transaction_type', $finance['transaction_type']) == 'invoice') ? 'selected' : '' ?>>Invoice</option>
                                        <option value="refund" <?= (old('transaction_type', $finance['transaction_type']) == 'refund') ? 'selected' : '' ?>>Refund</option>
                                        <option value="adjustment" <?= (old('transaction_type', $finance['transaction_type']) == 'adjustment') ? 'selected' : '' ?>>Adjustment</option>
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

                        <!-- Examination Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-file-medical text-indigo-600"></i>
                                <span>Examination</span>
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
                                        data-placeholder="Search examinations by ID, type, or patient...">
                                    <option value="">Select examination (optional)</option>
                                    <?php if (!empty($examinations)): ?>
                                        <?php foreach ($examinations as $examination): ?>
                                            <option value="<?= $examination['id'] ?>" selected>
                                                #<?= $examination['id'] ?> - <?= $examination['examination_type'] ?> (<?= $examination['created_at'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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

                        <!-- Amount, Currency & Payment Method -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span>Amount (Original) *</span>
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
                                    <span>Currency *</span>
                                </label>
                                <div class="relative">
                                    <select name="currency" id="currencySelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" required>
                                        <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= ($selectedCurrency === $option) ? 'selected' : '' ?>>
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
                                    <span>Exchange Rate *</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="exchange_rate" id="exchangeRate" value="<?= esc($exchangeRate) ?>" step="0.0001" min="0" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" placeholder="1.0000">
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Rate to convert into clinic currency (<?= esc($baseCurrency) ?>).</p>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calculator text-emerald-600"></i>
                                    <span>Converted Amount (<?= esc($baseCurrency) ?>)</span>
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
                                    <span>Payment Method</span>
                                </label>
                                <div class="relative">
                                    <select name="payment_method" id="paymentMethod" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl">
                                        <option value="">Select method</option>
                                        <option value="cash" <?= (old('payment_method', $finance['payment_method']) == 'cash') ? 'selected' : '' ?>>Cash</option>
                                        <option value="card" <?= (old('payment_method', $finance['payment_method']) == 'card') ? 'selected' : '' ?>>Card</option>
                                        <option value="bank_transfer" <?= (old('payment_method', $finance['payment_method']) == 'bank_transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                                        <option value="check" <?= (old('payment_method', $finance['payment_method']) == 'check') ? 'selected' : '' ?>>Check</option>
                                        <option value="other" <?= (old('payment_method', $finance['payment_method']) == 'other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-file-upload text-rose-600"></i>
                                    <span>Check Scan (optional)</span>
                                </label>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    <label class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-bold rounded-xl shadow-lg hover:bg-rose-700 cursor-pointer transition-all w-fit">
                                        <i class="fas fa-upload mr-2"></i>Upload Check Scan
                                        <input type="file" name="check_scan" id="checkScanInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                    </label>
                                    <span class="text-xs text-gray-500">PDF, JPG, PNG (max per plan)</span>
                                    <span id="checkScanName" class="text-xs text-gray-400"></span>
                                </div>
                                <?php if (!empty($check_attachment)): ?>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Existing: <a class="text-blue-600 hover:text-blue-800" href="<?= base_url('file/download/' . $check_attachment['id']) ?>">Download</a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Status and Payment Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-amber-600"></i>
                                    <span>Payment Status *</span>
                                </label>
                                <div class="relative">
                                    <select name="payment_status" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="pending" <?= (old('payment_status', $finance['payment_status']) == 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="paid" <?= (old('payment_status', $finance['payment_status']) == 'paid') ? 'selected' : '' ?>>Paid</option>
                                        <option value="partial" <?= (old('payment_status', $finance['payment_status']) == 'partial') ? 'selected' : '' ?>>Partial</option>
                                        <option value="overdue" <?= (old('payment_status', $finance['payment_status']) == 'overdue') ? 'selected' : '' ?>>Overdue</option>
                                        <option value="cancelled" <?= (old('payment_status', $finance['payment_status']) == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-rose-600"></i>
                                    <span>Paid Date</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="paid_date" value="<?= old('paid_date', $finance['paid_date']) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-calendar-day text-orange-600"></i>
                                <span>Due Date</span>
                            </label>
                            <div class="relative">
                                <input type="date" name="due_date" value="<?= old('due_date', $finance['due_date']) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Service Type -->
                        <div class="grid grid-cols-1 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tools text-purple-600"></i>
                                    <span>Service Type *</span>
                                </label>
                                <div class="relative">
                                    <select name="service_type" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Select service type</option>
                                        <option value="consultation" <?= (old('service_type', $finance['service_type']) == 'consultation') ? 'selected' : '' ?>>Consultation</option>
                                        <option value="treatment" <?= (old('service_type', $finance['service_type']) == 'treatment') ? 'selected' : '' ?>>Treatment</option>
                                        <option value="medication" <?= (old('service_type', $finance['service_type']) == 'medication') ? 'selected' : '' ?>>Medication</option>
                                        <option value="procedure" <?= (old('service_type', $finance['service_type']) == 'procedure') ? 'selected' : '' ?>>Procedure</option>
                                        <option value="other" <?= (old('service_type', $finance['service_type']) == 'other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

        <!-- Service Details -->
        <div class="group/field relative">
            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                <i class="fas fa-notes-medical text-emerald-600"></i>
                <span>Service Details</span>
            </label>
            <div class="relative">
                <textarea name="service_details" rows="3" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Details about the service provided..."><?= old('service_details', $finance['service_details']) ?></textarea>
                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
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
                    <span>Discount Amount</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-bold"><?= getCurrencySymbol() ?></span>
                    </div>
                    <input type="number" name="discount_amount" id="discountAmount" value="<?= old('discount_amount', $finance['discount_amount'] ?? 0) ?>" step="0.01" min="0" class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-percentage text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="group/field relative">
                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                    <i class="fas fa-percent text-indigo-600"></i>
                    <span>Tax Percentage (%)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-bold">%</span>
                    </div>
                    <input type="number" name="tax_percentage" id="taxPercentage" value="<?= old('tax_percentage', $taxPercent) ?>" step="0.01" min="0" max="100" class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00">
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
                <span>Total (<?= esc($baseCurrency) ?>)</span>
            </label>
            <div class="relative">
                <input type="text" id="totalAmountLive" readonly class="w-full px-4 py-3 bg-emerald-50 border-2 border-emerald-200 rounded-xl shadow-lg text-emerald-700 font-bold" value="0.00">
            </div>
            <p class="text-xs text-gray-500 mt-2">Total = Converted Amount - Discount + Tax</p>
        </div>

        <!-- Live Total -->
        <div class="group/field relative">
            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                <i class="fas fa-receipt text-emerald-600"></i>
                <span>Total (<?= esc($baseCurrency) ?>)</span>
            </label>
            <div class="relative">
                <input type="text" id="totalAmountLive" readonly class="w-full px-4 py-3 bg-emerald-50 border-2 border-emerald-200 rounded-xl shadow-lg text-emerald-700 font-bold" value="0.00">
            </div>
            <p class="text-xs text-gray-500 mt-2">Total = Converted Amount - Discount + Tax</p>
        </div>

        <!-- Description -->
        <div class="group/field relative">
            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                <i class="fas fa-align-left text-purple-600"></i>
                <span>Description</span>
                            </label>
                            <div class="relative">
                                <textarea name="description" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Transaction description or notes..."><?= old('description', $finance['description']) ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-cyan-600"></i>
                                <span>Notes</span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Additional notes..."><?= old('notes', $finance['notes']) ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('finance') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-green-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-save mr-2 relative z-10"></i>
                                <span class="relative z-10">Update Transaction</span>
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

