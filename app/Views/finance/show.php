<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
    $totalAmount = $finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']);
    $taxPercent = $finance['tax_percentage'] ?? 0;
    $taxableBase = max(0, floatval($finance['amount']) - floatval($finance['discount_amount'] ?? 0));
    if (!$taxPercent && $taxableBase > 0 && !empty($finance['tax_amount'])) {
        $taxPercent = round((floatval($finance['tax_amount']) / $taxableBase) * 100, 2);
    }
    $baseCurrency = $base_currency ?? ($finance['currency'] ?? 'ILS');
    $amountOriginal = $finance['amount_original'] ?? $finance['amount'];
    $exchangeRate = $finance['exchange_rate'] ?? 1;
    $isConverted = !empty($finance['currency']) && $finance['currency'] !== $baseCurrency;
    $creatorName = trim(($finance['creator_first_name'] ?? '') . ' ' . ($finance['creator_last_name'] ?? ''));
    $isExpense = (($finance['transaction_type'] ?? '') === 'expense');
    $currencySymbol = settings()->getCurrencySymbol();
    $supportsPaymentExchangeFields = !empty($supports_payment_exchange_fields);
    $paymentStatusKey = $finance['payment_status'] ?? 'pending';
    $paymentStatusLabel = lang('Finance.' . $paymentStatusKey);
    if ($paymentStatusLabel === 'Finance.' . $paymentStatusKey) {
        $paymentStatusLabel = ucfirst($paymentStatusKey);
    }
    $transactionTypeKey = $finance['transaction_type'] ?? 'transaction';
    $transactionTypeLabel = lang('Finance.' . $transactionTypeKey);
    if ($transactionTypeLabel === 'Finance.' . $transactionTypeKey) {
        $transactionTypeLabel = ucfirst(str_replace('_', ' ', $transactionTypeKey));
    }
    $serviceTypeKey = $finance['service_type'] ?? 'other';
    $serviceTypeLabel = lang('Finance.' . $serviceTypeKey);
    if ($serviceTypeLabel === 'Finance.' . $serviceTypeKey) {
        $serviceTypeLabel = ucfirst(str_replace('_', ' ', $serviceTypeKey));
    }
    $expenseCategoryMap = [
        'rent' => ['en' => 'Rent', 'ar' => 'إيجار'],
        'utilities' => ['en' => 'Utilities', 'ar' => 'مرافق'],
        'supplies' => ['en' => 'Supplies', 'ar' => 'مستلزمات'],
        'marketing' => ['en' => 'Marketing', 'ar' => 'تسويق'],
        'miscellaneous' => ['en' => 'Miscellaneous', 'ar' => 'متفرقات'],
    ];
    $expenseCategoryKey = $finance['expense_category'] ?? 'miscellaneous';
    $expenseCategoryLabel = $expenseCategoryMap[$expenseCategoryKey][service('request')->getLocale()] ?? ucfirst(str_replace('_', ' ', $expenseCategoryKey));
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/70 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl shadow-lg">
                                <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                                    <?= $isExpense ? lang('Finance.expenseTransaction') : lang('Finance.financialTransaction') ?>
                                </h1>
                                <p class="text-gray-600"><?= lang('Finance.transactionIdLabel') ?>: <?= $finance['transaction_id'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <?php if (!empty($patient)): ?>
                        <a href="<?= base_url('patient/' . $patient['id']) ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-blue-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-user mr-2"></i><?= lang('Finance.backToPatient') ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('finance') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-sm font-medium rounded-xl hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-arrow-left mr-2"></i><?= lang('Finance.backToFinance') ?>
                        </a>
                        <a href="<?= base_url('finance/' . $finance['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-edit mr-2"></i><?= lang('Finance.editTransaction') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Main Content (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Transaction Overview Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 mb-1 group-hover:scale-110 transition-transform duration-300">
                                <?= number_format($totalAmount, 2) ?> <span class="text-sm text-gray-500 font-medium"><?= esc($baseCurrency) ?></span>
                            </div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= lang('Finance.totalAmount') ?></div>
                        </div>
                    </div>
                    
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group">
                        <div class="text-center">
                            <div class="text-xl font-bold text-gray-900 mb-1 flex items-center justify-center">
                                <i class="fas fa-exchange-alt mr-2 text-blue-500/50 group-hover:rotate-180 transition-transform duration-500"></i>
                                <?= esc($transactionTypeLabel) ?>
                            </div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= lang('Finance.transactionType') ?></div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group">
                        <div class="text-center">
                            <div class="mb-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold shadow-sm
                                    <?= $finance['payment_status'] == 'paid' ? 'bg-emerald-100 text-emerald-700' : 
                                        ($finance['payment_status'] == 'partial' ? 'bg-blue-100 text-blue-700' :
                                        ($finance['payment_status'] == 'pending' ? 'bg-amber-100 text-amber-700' : 
                                        ($finance['payment_status'] == 'overdue' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700'))) ?>">
                                    <i class="fas fa-circle text-[8px] mr-2 opacity-50"></i>
                                    <?= esc($paymentStatusLabel) ?>
                                </span>
                            </div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= lang('Finance.paymentStatusUpper') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Financial Details & Breakdown -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <i class="fas fa-list-ul"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= lang('Finance.detailsBreakdown') ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= esc($isExpense ? $expenseCategoryLabel : $serviceTypeLabel) ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Description -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1"><?= $isExpense ? lang('Finance.expenseDescription') : lang('Finance.serviceDescription') ?></label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= !empty($finance['description']) ? nl2br(htmlspecialchars($finance['description'])) : '<span class="text-gray-400 italic">' . esc(lang('Finance.noDescriptionProvided')) . '</span>' ?>
                                    </div>
                                </div>
                                <?php if ($isExpense && !empty($finance['counterparty_name'])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.payeeVendor') ?></label>
                                    <div class="text-gray-800 leading-relaxed font-semibold">
                                        <?= esc($finance['counterparty_name']) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($finance['service_details'])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.technicalDetails') ?></label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($finance['service_details'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.createdByLabel') ?></label>
                                    <div class="text-gray-800 leading-relaxed font-semibold">
                                        <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 shadow-inner">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 font-medium"><?= lang('Finance.subtotal') ?></span>
                                        <span class="font-bold text-gray-900"><?= number_format($finance['amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php if ($isConverted): ?>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span class="font-medium"><?= lang('Finance.original') ?></span>
                                        <span class="font-semibold">
                                            <?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?>
                                            @ <?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['discount_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-rose-600">
                                        <span class="font-medium"><?= lang('Finance.discount') ?></span>
                                        <span class="font-bold">-<?= number_format($finance['discount_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['tax_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-gray-600">
                                        <span class="font-medium"><?= lang('Finance.taxPercent') ?> (<?= number_format($taxPercent, 2) ?>%)</span>
                                        <span class="font-bold">+<?= number_format($finance['tax_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-200 pt-4 mt-4 flex justify-between items-center">
                                        <span class="text-lg font-bold text-gray-800"><?= lang('Finance.total') ?></span>
                                        <span class="text-2xl font-bold text-gray-900">
                                            <?= number_format($totalAmount, 2) ?> <span class="text-sm font-normal text-gray-500"><?= esc($baseCurrency) ?></span>
                                        </span>
                                    </div>
                                    
                                    <?php if ($finance['transaction_type'] === 'invoice'): ?>
                                        <div class="pt-4 mt-4 border-t border-gray-200 border-dashed space-y-3">
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-emerald-600 font-bold"><?= lang('Finance.paidToDate') ?></span>
                                                <span class="text-emerald-700 font-bold"><?= number_format($finance['amount_paid'] ?? 0, 2) ?> <?= esc($baseCurrency) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm bg-rose-50 p-3 rounded-lg border border-rose-100 shadow-sm">
                                                <span class="text-rose-600 font-bold uppercase tracking-wider"><?= lang('Finance.balanceDue') ?></span>
                                                <span class="text-rose-700 text-xl font-bold"><?= number_format($finance['balance_due'] ?? $totalAmount, 2) ?> <?= esc($baseCurrency) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($provider_payout)): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= lang('Finance.providerSplit') ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?= ($provider_payout['status'] ?? 'unpaid') === 'paid' ? 'bg-emerald-100 text-emerald-700' : (($provider_payout['status'] ?? 'unpaid') === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') ?>">
                            <?= ucfirst($provider_payout['status'] ?? 'unpaid') ?>
                        </span>
                    </div>
                    <div class="p-6 space-y-5">
                        <?php
                            $providerDisplayName = trim(($provider_payout['provider_first_name'] ?? '') . ' ' . ($provider_payout['provider_last_name'] ?? ''));
                            if ($providerDisplayName === '') {
                                $providerDisplayName = $provider_payout['provider_name_snapshot'] ?? lang('Finance.guestProvider');
                            }
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.provider') ?></p>
                                <p class="mt-1 font-bold text-gray-900"><?= esc($providerDisplayName) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.grossCollected') ?></p>
                                <p class="mt-1 font-bold text-gray-900"><?= formatCurrency($provider_payout['gross_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.providerShare') ?></p>
                                <p class="mt-1 font-bold text-indigo-700"><?= formatCurrency($provider_payout['provider_share_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.clinicNet') ?></p>
                                <p class="mt-1 font-bold text-emerald-700"><?= formatCurrency($provider_payout['clinic_share_amount'] ?? 0) ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-emerald-700 font-semibold"><?= lang('Finance.paidToProvider') ?></p>
                                <p class="mt-1 font-bold text-emerald-800"><?= formatCurrency($provider_payout['provider_paid_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-rose-700 font-semibold"><?= lang('Finance.balanceDue') ?></p>
                                <p class="mt-1 font-bold text-rose-800"><?= formatCurrency($provider_payout['provider_balance_due'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.splitRule') ?></p>
                                <p class="mt-1 font-bold text-gray-900">
                                    <?= lang('Finance.' . ($provider_payout['split_mode'] ?? 'percentage')) ?>:
                                    <?= esc($provider_payout['split_value'] ?? 0) ?><?= ($provider_payout['split_mode'] ?? '') === 'percent' ? '%' : '' ?>
                                </p>
                            </div>
                        </div>

                        <?php if (floatval($provider_payout['provider_balance_due'] ?? 0) > 0 && has_permission('finance', 'edit')): ?>
                        <form id="providerPayoutPaymentForm" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= lang('Finance.amount') ?></label>
                                <input type="number" name="amount" step="0.01" min="0.01" max="<?= esc($provider_payout['provider_balance_due'] ?? 0) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= lang('Finance.method') ?></label>
                                <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="cash"><?= lang('Finance.cash') ?></option>
                                    <option value="card"><?= lang('Finance.card') ?></option>
                                    <option value="bank_transfer"><?= lang('Finance.bankTransfer') ?></option>
                                    <option value="check"><?= lang('Finance.check') ?></option>
                                    <option value="other"><?= lang('Finance.other') ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= lang('Finance.date') ?></label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-plus mr-2"></i><?= lang('Finance.recordProviderPayout') ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($finance['transaction_type'] === 'invoice'): ?>
                <!-- Invoice Payments Ledger -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="px-6 py-4 border-b border-gray-100 bg-white flex items-center space-x-3">
                        <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800"><?= lang('Finance.paymentsLedger') ?></h3>
                            <p class="text-xs text-gray-500"><?= lang('Finance.historyOfSettlements') ?></p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.date') ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.status') ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.method') ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.recordedBy') ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.settlement') ?></th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.amountBase') ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.notes') ?></th>
                                    <?php if (has_permission('finance', 'edit')): ?>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.actions') ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <?php if (!empty($payments)): ?>
                                    <?php foreach ($payments as $payment): ?>
                                    <?php
                                        $type = $payment['type'] ?? 'payment';
                                        $typeClass = 'bg-emerald-100 text-emerald-700';
                                        $amountClass = 'text-emerald-700';
                                        $prefix = '+';
                                        
                                        if ($type === 'refund') {
                                            $typeClass = 'bg-rose-100 text-rose-700';
                                            $amountClass = 'text-rose-700';
                                            $prefix = '-';
                                        } elseif ($type === 'void') {
                                            $typeClass = 'bg-slate-100 text-slate-500 line-through';
                                            $amountClass = 'text-slate-400 line-through';
                                            $prefix = '';
                                        } elseif ($type === 'adjustment') {
                                            $typeClass = 'bg-amber-100 text-amber-700';
                                            $amountClass = 'text-amber-700';
                                            $prefix = '+';
                                        }
                                    ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                            <?= formatDate($payment['payment_date']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $typeClass ?>">
                                                <?= lang('Finance.' . $type) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?= lang('Finance.' . $payment['payment_method']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?php $paymentCreator = trim(($payment['creator_first_name'] ?? '') . ' ' . ($payment['creator_last_name'] ?? '')); ?>
                                            <?= $paymentCreator !== '' ? 'Dr. ' . esc($paymentCreator) : lang('Finance.unknown') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?php
                                                $paymentCurrency = $payment['currency'] ?? $baseCurrency;
                                                $paymentExchangeRate = floatval($payment['exchange_rate'] ?? 1);
                                                $paymentOriginalAmount = isset($payment['amount_original']) ? floatval($payment['amount_original']) : floatval($payment['amount']);
                                            ?>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-800">
                                                    <?= $prefix . number_format($paymentOriginalAmount, 2) ?> <?= esc($paymentCurrency) ?>
                                                </span>
                                                <?php if ($paymentCurrency !== $baseCurrency || abs($paymentExchangeRate - 1) > 0.0001): ?>
                                                    <span class="text-xs text-gray-400">
                                                        <?= lang('Finance.rateShort') ?> <?= rtrim(rtrim(number_format($paymentExchangeRate, 6), '0'), '.') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right <?= $amountClass ?>">
                                            <?= $prefix . formatCurrency($payment['amount']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs italic">
                                            <?php if (!empty($payment['notes'])): ?>
                                                <?= esc($payment['notes']) ?>
                                            <?php else: ?>
                                                <span class="text-slate-300">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (has_permission('finance', 'edit')): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if ($type === 'payment'): ?>
                                                <button type="button" onclick="voidPayment(<?= (int) $payment['id'] ?>)" class="text-gray-400 hover:text-rose-600 transition-colors" title="<?= esc(lang('Finance.voidPayment')) ?>">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <?php $emptyColspan = has_permission('finance', 'edit') ? 8 : 7; ?>
                                        <td colspan="<?= $emptyColspan ?>" class="px-6 py-12 text-center text-sm text-gray-400 font-medium">
                                            <?= lang('Finance.noPaymentHistory') ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (has_permission('finance', 'edit')): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Record Payment Form -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-500">
                        <div class="px-6 py-4 border-b border-gray-100 bg-white flex items-center space-x-3">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600">
                                <i class="fas fa-plus-circle text-sm"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= lang('Finance.recordPayment') ?></h2>
                        </div>
                        <div class="p-6">
                            <form id="invoicePaymentForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                                    <?= $supportsPaymentExchangeFields ? lang('Finance.settlementCurrencyNotice') : lang('Finance.settlementBaseCurrencyNotice') ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-emerald-600"></i>
                                            <span><?= lang('Finance.amountRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl"
                                                   placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-coins text-amber-500"></i>
                                            <span><?= lang('Finance.currency') ?></span>
                                        </label>
                                        <div class="relative">
                                            <select name="currency" data-settlement-currency class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                <?php foreach (($currency_options ?? [$baseCurrency]) as $currencyOption): ?>
                                                    <option value="<?= esc($currencyOption) ?>" <?= $currencyOption === $baseCurrency ? 'selected' : '' ?>><?= esc($currencyOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-chart-line text-indigo-600"></i>
                                            <span><?= lang('Finance.exchangeRate') ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl">
                                        </div>
                                    </div>
                                    <div class="group/field relative md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-scale-balanced text-slate-600"></i>
                                            <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" data-settlement-base readonly
                                                   class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl shadow-inner text-slate-700 font-semibold"
                                                   value="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                            <span><?= lang('Finance.methodRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <select name="payment_method" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                <option value="cash"><?= lang('Finance.cash') ?></option>
                                                <option value="card"><?= lang('Finance.card') ?></option>
                                                <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                                <option value="check"><?= lang('Finance.check') ?></option>
                                                <option value="other"><?= lang('Finance.other') ?></option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-calendar-alt text-rose-600"></i>
                                            <span><?= lang('Finance.dateRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl">
                                        </div>
                                    </div>
                                </div>
                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-sticky-note text-purple-600"></i>
                                        <span><?= lang('Finance.notes') ?></span>
                                    </label>
                                    <div class="relative">
                                        <textarea name="notes" rows="2" 
                                                  class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                  placeholder="<?= esc(lang('Finance.optionalNotesPlaceholder')) ?>"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                                    <i class="fas fa-check-double mr-2"></i><?= lang('Finance.postSettlement') ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Record Refund Form -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-500">
                        <div class="px-6 py-4 border-b border-gray-100 bg-white flex items-center space-x-3">
                            <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center text-rose-600">
                                <i class="fas fa-undo-alt text-sm"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= lang('Finance.recordRefund') ?></h2>
                        </div>
                        <div class="p-6">
                            <form id="invoiceRefundForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                                    <?= $supportsPaymentExchangeFields ? lang('Finance.settlementCurrencyNotice') : lang('Finance.settlementBaseCurrencyNotice') ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-rose-600"></i>
                                            <span><?= lang('Finance.amountRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl"
                                                   placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-coins text-amber-500"></i>
                                            <span><?= lang('Finance.currency') ?></span>
                                        </label>
                                        <div class="relative">
                                            <select name="currency" data-settlement-currency class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                <?php foreach (($currency_options ?? [$baseCurrency]) as $currencyOption): ?>
                                                    <option value="<?= esc($currencyOption) ?>" <?= $currencyOption === $baseCurrency ? 'selected' : '' ?>><?= esc($currencyOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-chart-line text-indigo-600"></i>
                                            <span><?= lang('Finance.exchangeRate') ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl">
                                        </div>
                                    </div>
                                    <div class="group/field relative md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-scale-balanced text-slate-600"></i>
                                            <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" data-settlement-base readonly
                                                   class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl shadow-inner text-slate-700 font-semibold"
                                                   value="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                            <span><?= lang('Finance.methodRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <select name="payment_method" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                <option value="cash"><?= lang('Finance.cash') ?></option>
                                                <option value="card"><?= lang('Finance.card') ?></option>
                                                <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                                <option value="check"><?= lang('Finance.check') ?></option>
                                                <option value="other"><?= lang('Finance.other') ?></option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-calendar-alt text-amber-600"></i>
                                            <span><?= lang('Finance.dateRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl">
                                        </div>
                                    </div>
                                </div>
                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-sticky-note text-rose-600"></i>
                                        <span><?= lang('Finance.reason') ?></span>
                                    </label>
                                    <div class="relative">
                                        <textarea name="notes" rows="2" 
                                                  class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                  placeholder="<?= esc(lang('Finance.refundReasonPlaceholder')) ?>"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-rose-500 to-pink-600 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-rose-500/25 hover:shadow-rose-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                                    <i class="fas fa-reply-all mr-2"></i><?= lang('Finance.processReversal') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($finance['notes'])): ?>
                <!-- Additional Notes -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 group hover:shadow-2xl transition-all duration-500 relative overflow-hidden">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sticky-note mr-3 text-yellow-600"></i><?= lang('Finance.administrativeNotes') ?>
                    </h2>
                    <div class="text-gray-700 leading-relaxed bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                        <?= nl2br(htmlspecialchars($finance['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Sidebar (1/3) -->
            <div class="space-y-6">
                
                <!-- Action Card -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 group hover:shadow-2xl transition-all duration-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bolt text-orange-600 mr-2"></i>
                        <?= lang('Finance.quickActions') ?>
                    </h2>
                    <div class="space-y-3">
                        <?php if ($finance['payment_status'] != 'paid'): ?>
                            <button onclick="markAsPaid(<?= $finance['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-green-500">
                                <i class="fas fa-check-circle mr-2 text-white"></i><?= lang('Finance.markAsPaid') ?>
                            </button>
                        <?php endif; ?>
                        <?php if (($finance['transaction_type'] ?? '') === 'invoice' && floatval($finance['balance_due'] ?? 0) > 0): ?>
                            <button onclick="closeBalance(<?= (int) $finance['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-amber-600 text-white text-sm font-bold rounded-xl hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-amber-500">
                                <i class="fas fa-scale-balanced mr-2 text-white"></i><?= lang('Finance.closeBalanceCredit') ?>
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-blue-500">
                            <i class="fas fa-file-invoice mr-2 text-white"></i><?= lang('Finance.exportInvoice') ?>
                        </a>

                        <?php if (has_permission('finance', 'delete')): ?>
                        <button onclick="deleteFinance(<?= $finance['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-red-500">
                            <i class="fas fa-trash-alt mr-2 text-white"></i><?= lang('Finance.deleteLedger') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Patient Card -->
                <?php if ($patient): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 group hover:shadow-2xl transition-all duration-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-purple-600 mr-2"></i>
                        <?= lang('Finance.patientInformation') ?>
                    </h2>
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-xl group-hover:scale-110 transition-transform mb-4">
                            <?= esc(ui_initials($patient['first_name'] ?? '', $patient['last_name'] ?? '', 'P')) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-gray-800">
                                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?>
                            </div>
                            <div class="text-sm text-gray-500"><?= lang('Finance.idLabel') ?>: <?= $patient['patient_id'] ?></div>
                        </div>
                    </div>
                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="block w-full text-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-bold rounded-xl transition-all duration-300">
                        <?= lang('Finance.viewProfile') ?>
                    </a>
                </div>
                <?php endif; ?>

                <!-- Metadata Card -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 text-sm group hover:shadow-2xl transition-all duration-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                        <?= lang('Finance.metadata') ?>
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.standardMethod') ?></label>
                            <p class="text-gray-800 font-semibold"><?= lang('Finance.' . $finance['payment_method']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.currency') ?></label>
                            <p class="text-gray-800 font-semibold">
                                <?= esc($baseCurrency) ?>
                                <?php if ($isConverted): ?>
                                    <span class="text-xs text-gray-500"><?= lang('Finance.fromCurrency') ?> <?= esc($finance['currency']) ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($isConverted): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.exchangeRate') ?></label>
                            <p class="text-gray-800 font-semibold"><?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($check_attachment)): ?>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600"><?= lang('Finance.checkScanLabel') ?></label>
                            <div class="flex flex-wrap items-center gap-3">
                                <a class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-download mr-2"></i><?= lang('Finance.download') ?>
                                </a>
                                <a class="inline-flex items-center px-3 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-eye mr-2"></i><?= lang('Finance.view') ?>
                                </a>
                            </div>
                            <?php
                                $isImage = !empty($check_attachment['mime_type']) && strpos($check_attachment['mime_type'], 'image/') === 0;
                            ?>
                            <?php if ($isImage): ?>
                                <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2">
                                    <img src="<?= base_url('file/download/' . $check_attachment['id']) ?>" alt="<?= esc(lang('Finance.checkScanPreview')) ?>" class="max-h-56 w-auto rounded-lg">
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-gray-500"><?= lang('Finance.imagePreviewOnly') ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($finance['due_date'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.dueDate') ?></label>
                            <p class="text-rose-600 font-bold"><?= formatDate($finance['due_date']) ?></p>
                        </div>
                        <?php endif; ?>
                         <?php if (!empty($finance['paid_date'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Finance.settlementDate') ?></label>
                            <p class="text-emerald-600 font-bold"><?= formatDate($finance['paid_date']) ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-[10px] text-gray-400"><?= lang('Finance.lastUpdated') ?>: <?= formatDateTime($finance['updated_at']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Related Examination -->
                <?php if ($examination): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 group hover:shadow-2xl transition-all duration-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-stethoscope text-emerald-600 mr-2"></i>
                        <?= lang('Finance.clinicalLink') ?>
                    </h2>
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 shadow-inner group-hover:bg-emerald-100 transition-colors">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-800">
                                <?= formatDate($examination['examination_date']) ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= lang('Finance.status') ?>: <?= ucfirst($examination['status']) ?>
                            </div>
                        </div>
                    </div>
                    <a href="<?= base_url('examination/' . $examination['id']) ?>" class="block w-full text-center px-4 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-sm font-bold rounded-xl transition-all duration-300">
                        <?= lang('Finance.viewClinicalRecord') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const financeBaseCurrency = '<?= esc($baseCurrency) ?>';

function syncSettlementForm(form) {
    if (!form) {
        return;
    }

    const currencyInput = form.querySelector('[data-settlement-currency]');
    const rateInput = form.querySelector('[data-settlement-rate]');
    const amountInput = form.querySelector('input[name="amount_original"], input[name="amount"]');
    const baseOutput = form.querySelector('[data-settlement-base]');
    if (!amountInput || !baseOutput) {
        return;
    }

    const currency = currencyInput ? currencyInput.value : financeBaseCurrency;
    let rate = parseFloat(rateInput ? rateInput.value : '1');
    if (!Number.isFinite(rate) || rate <= 0) {
        rate = 1;
    }

    if (currency === financeBaseCurrency && rateInput) {
        rate = 1;
        rateInput.value = '1';
        rateInput.readOnly = true;
        rateInput.classList.add('bg-slate-50');
    } else if (rateInput) {
        rateInput.readOnly = false;
        rateInput.classList.remove('bg-slate-50');
    }

    const amount = parseFloat(amountInput.value || '0');
    const baseAmount = Number.isFinite(amount) ? (amount * rate) : 0;
    baseOutput.value = baseAmount.toFixed(2);
}

function initSettlementForm(form) {
    if (!form) {
        return;
    }

    const watchedInputs = form.querySelectorAll('input[name="amount_original"], input[name="amount"], [data-settlement-currency], [data-settlement-rate]');
    watchedInputs.forEach((input) => {
        input.addEventListener('input', function () { syncSettlementForm(form); });
        input.addEventListener('change', function () { syncSettlementForm(form); });
    });

    syncSettlementForm(form);
}

function markAsPaid(transactionId) {
    if (confirm('<?= lang('Finance.confirmMarkTransactionPaid') ?>')) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('finance') ?>/${transactionId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= lang('Finance.errorUpdatingTransaction') ?>');
        });
    }
}

function deleteFinance(id) {
    if (confirm('<?= lang('Finance.confirmDeleteTransaction') ?>')) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('finance') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                window.location.href = '<?= base_url('finance') ?>';
            } else {
                alert('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= lang('Finance.errorDeletingTransaction') ?>');
        });
    }
}

function closeBalance(transactionId) {
    if (!confirm('<?= lang('Finance.confirmCloseBalance') ?>')) {
        return;
    }

    const reason = prompt('<?= lang('Finance.reasonOptionalPrompt') ?>') || '';
    const formData = new FormData();
    formData.append('reason', reason);

    const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
    const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

    fetch(`<?= base_url('finance') ?>/${transactionId}/close-balance`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (data.success) {
            location.reload();
        } else {
            alert('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?= lang('Finance.errorClosingBalance') ?>');
    });
}

const invoicePaymentForm = document.getElementById('invoicePaymentForm');
if (invoicePaymentForm) {
    initSettlementForm(invoicePaymentForm);
    invoicePaymentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoicePaymentForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '<?= esc(lang('Finance.processing')) ?>';

        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Finance.errorAddingPayment')) ?>');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
}

const invoiceRefundForm = document.getElementById('invoiceRefundForm');
if (invoiceRefundForm) {
    initSettlementForm(invoiceRefundForm);
    invoiceRefundForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoiceRefundForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '<?= esc(lang('Finance.processing')) ?>';

        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/refund`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Finance.errorRecordingRefund')) ?>');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
}

function voidPayment(paymentId) {
    if (!confirm('<?= esc(lang('Finance.confirmVoidPayment')) ?>')) {
        return;
    }
    const reason = prompt('<?= esc(lang('Finance.voidReasonPrompt')) ?>') || '';
    const formData = new FormData();
    formData.append('notes', reason);

    const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
    const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

    fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/${paymentId}/void`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (data.success) {
            location.reload();
        } else {
            alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?= esc(lang('Finance.errorVoidingPayment')) ?>');
    });
}

const providerPayoutPaymentForm = document.getElementById('providerPayoutPaymentForm');
if (providerPayoutPaymentForm) {
    providerPayoutPaymentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(providerPayoutPaymentForm);
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

        fetch(`<?= base_url('finance/payouts/' . (int)($provider_payout['id'] ?? 0) . '/payments') ?>`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Finance.errorRecordingProviderPayout')) ?>');
        });
    });
}
</script>
<?= $this->endSection() ?>


