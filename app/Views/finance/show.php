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
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Amount</div>
                        </div>
                    </div>
                    
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group">
                        <div class="text-center">
                            <div class="text-xl font-bold text-gray-900 mb-1 flex items-center justify-center">
                                <i class="fas fa-exchange-alt mr-2 text-blue-500/50 group-hover:rotate-180 transition-transform duration-500"></i>
                                <?= ucfirst(str_replace('_', ' ', $finance['transaction_type'])) ?>
                            </div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaction Type</div>
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
                                    <?= ucfirst($finance['payment_status']) ?>
                                </span>
                            </div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment Status</div>
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
                            <h2 class="text-xl font-bold text-gray-800">Details & Breakdown</h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= $isExpense ? ucfirst(str_replace('_', ' ', (string)($finance['expense_category'] ?? 'miscellaneous'))) : ucfirst(str_replace('_', ' ', $finance['service_type'])) ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Description -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1"><?= $isExpense ? 'Expense Description' : 'Service Description' ?></label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= !empty($finance['description']) ? nl2br(htmlspecialchars($finance['description'])) : '<span class="text-gray-400 italic">No description provided</span>' ?>
                                    </div>
                                </div>
                                <?php if ($isExpense && !empty($finance['counterparty_name'])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Payee / Vendor</label>
                                    <div class="text-gray-800 leading-relaxed font-semibold">
                                        <?= esc($finance['counterparty_name']) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($finance['service_details'])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Technical Details</label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($finance['service_details'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                                    <div class="text-gray-800 leading-relaxed font-semibold">
                                        <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : 'Unknown' ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 shadow-inner">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 font-medium">Subtotal</span>
                                        <span class="font-bold text-gray-900"><?= number_format($finance['amount'], 2) ?></span>
                                    </div>
                                    <?php if ($isConverted): ?>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span class="font-medium">Original</span>
                                        <span class="font-semibold">
                                            <?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?>
                                            @ <?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['discount_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-rose-600">
                                        <span class="font-medium">Discount</span>
                                        <span class="font-bold">-<?= number_format($finance['discount_amount'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['tax_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-gray-600">
                                        <span class="font-medium">Tax (<?= number_format($taxPercent, 2) ?>%)</span>
                                        <span class="font-bold">+<?= number_format($finance['tax_amount'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-200 pt-4 mt-4 flex justify-between items-center">
                                        <span class="text-lg font-bold text-gray-800">Total</span>
                                        <span class="text-2xl font-bold text-gray-900">
                                            <?= number_format($totalAmount, 2) ?> <span class="text-sm font-normal text-gray-500"><?= esc($baseCurrency) ?></span>
                                        </span>
                                    </div>
                                    
                                    <?php if ($finance['transaction_type'] === 'invoice'): ?>
                                        <div class="pt-4 mt-4 border-t border-gray-200 border-dashed space-y-3">
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-emerald-600 font-bold">Paid to Date</span>
                                                <span class="text-emerald-700 font-bold"><?= number_format($finance['amount_paid'] ?? 0, 2) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm bg-rose-50 p-3 rounded-lg border border-rose-100 shadow-sm">
                                                <span class="text-rose-600 font-bold uppercase tracking-wider">Balance Due</span>
                                                <span class="text-rose-700 text-xl font-bold"><?= number_format($finance['balance_due'] ?? $totalAmount, 2) ?></span>
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
                            <h2 class="text-xl font-bold text-gray-800">Provider Split</h2>
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
                                $providerDisplayName = $provider_payout['provider_name_snapshot'] ?? 'Guest Provider';
                            }
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Provider</p>
                                <p class="mt-1 font-bold text-gray-900"><?= esc($providerDisplayName) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Gross</p>
                                <p class="mt-1 font-bold text-gray-900"><?= formatCurrency($provider_payout['gross_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Provider Share</p>
                                <p class="mt-1 font-bold text-indigo-700"><?= formatCurrency($provider_payout['provider_share_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Clinic Net</p>
                                <p class="mt-1 font-bold text-emerald-700"><?= formatCurrency($provider_payout['clinic_share_amount'] ?? 0) ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-emerald-700 font-semibold">Paid to Provider</p>
                                <p class="mt-1 font-bold text-emerald-800"><?= formatCurrency($provider_payout['provider_paid_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-rose-700 font-semibold">Balance Due</p>
                                <p class="mt-1 font-bold text-rose-800"><?= formatCurrency($provider_payout['provider_balance_due'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Split Rule</p>
                                <p class="mt-1 font-bold text-gray-900">
                                    <?= ucfirst($provider_payout['split_mode'] ?? 'percent') ?>:
                                    <?= esc($provider_payout['split_value'] ?? 0) ?><?= ($provider_payout['split_mode'] ?? '') === 'percent' ? '%' : '' ?>
                                </p>
                            </div>
                        </div>

                        <?php if (floatval($provider_payout['provider_balance_due'] ?? 0) > 0 && has_permission('finance', 'edit')): ?>
                        <form id="providerPayoutPaymentForm" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Amount</label>
                                <input type="number" name="amount" step="0.01" min="0.01" max="<?= esc($provider_payout['provider_balance_due'] ?? 0) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Method</label>
                                <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Date</label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-plus mr-2"></i>Record Payout
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
                            <h3 class="text-xl font-bold text-gray-800">Payments Ledger</h3>
                            <p class="text-xs text-gray-500">History of Settlements</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Recorded By</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Notes</th>
                                    <?php if (has_permission('finance', 'edit')): ?>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
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
                                                <?= ucfirst($type) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?php $paymentCreator = trim(($payment['creator_first_name'] ?? '') . ' ' . ($payment['creator_last_name'] ?? '')); ?>
                                            <?= $paymentCreator !== '' ? 'Dr. ' . esc($paymentCreator) : 'Unknown' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right <?= $amountClass ?>">
                                            <?= $prefix . number_format($payment['amount'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs italic">
                                            <?= $payment['notes'] ? esc($payment['notes']) : '<span class="text-slate-300">—</span>' ?>
                                        </td>
                                        <?php if (has_permission('finance', 'edit')): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if ($type === 'payment'): ?>
                                                <button type="button" onclick="voidPayment(<?= (int) $payment['id'] ?>)" class="text-gray-400 hover:text-rose-600 transition-colors" title="Void Payment">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <?php $emptyColspan = has_permission('finance', 'edit') ? 7 : 6; ?>
                                        <td colspan="<?= $emptyColspan ?>" class="px-6 py-12 text-center text-sm text-gray-400 font-medium">
                                            No payment history found for this invoice.
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
                            <h2 class="text-xl font-bold text-gray-800">Record Payment</h2>
                        </div>
                        <div class="p-6">
                            <form id="invoicePaymentForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign text-emerald-600"></i>
                                        <span>Amount *</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 font-bold">$</span>
                                        </div>
                                        <input type="number" name="amount" step="0.01" min="0" 
                                               class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" 
                                               placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                            <span>Method *</span>
                                        </label>
                                        <div class="relative">
                                            <select name="payment_method" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                <option value="cash">Cash</option>
                                                <option value="card">Card</option>
                                                <option value="bank_transfer">Transfer</option>
                                                <option value="check">Check</option>
                                                <option value="other">Other</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-calendar-alt text-rose-600"></i>
                                            <span>Date *</span>
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
                                        <span>Notes</span>
                                    </label>
                                    <div class="relative">
                                        <textarea name="notes" rows="2" 
                                                  class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                  placeholder="Optional notes..."></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                                    <i class="fas fa-check-double mr-2"></i> Post Settlement
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
                            <h2 class="text-xl font-bold text-gray-800">Record Refund</h2>
                        </div>
                        <div class="p-6">
                            <form id="invoiceRefundForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign text-rose-600"></i>
                                        <span>Amount *</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 font-bold">$</span>
                                        </div>
                                        <input type="number" name="amount" step="0.01" min="0" 
                                               class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl" 
                                               placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                            <span>Method *</span>
                                        </label>
                                        <div class="relative">
                                            <select name="payment_method" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                <option value="cash">Cash</option>
                                                <option value="card">Card</option>
                                                <option value="bank_transfer">Transfer</option>
                                                <option value="check">Check</option>
                                                <option value="other">Other</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-calendar-alt text-amber-600"></i>
                                            <span>Date *</span>
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
                                        <span>Reason</span>
                                    </label>
                                    <div class="relative">
                                        <textarea name="notes" rows="2" 
                                                  class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                  placeholder="Justification..."></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-rose-500 to-pink-600 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-rose-500/25 hover:shadow-rose-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                                    <i class="fas fa-reply-all mr-2"></i> Process Reversal
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
                        <i class="fas fa-sticky-note mr-3 text-yellow-600"></i> Administrative Notes
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
                        Quick Actions
                    </h2>
                    <div class="space-y-3">
                        <?php if ($finance['payment_status'] != 'paid'): ?>
                            <button onclick="markAsPaid(<?= $finance['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-green-500">
                                <i class="fas fa-check-circle mr-2 text-white"></i> Mark as Paid
                            </button>
                        <?php endif; ?>
                        <?php if (($finance['transaction_type'] ?? '') === 'invoice' && floatval($finance['balance_due'] ?? 0) > 0): ?>
                            <button onclick="closeBalance(<?= (int) $finance['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-amber-600 text-white text-sm font-bold rounded-xl hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-amber-500">
                                <i class="fas fa-scale-balanced mr-2 text-white"></i> Close Balance (Credit)
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-blue-500">
                            <i class="fas fa-file-invoice mr-2 text-white"></i> Export Invoice
                        </a>

                        <?php if (has_permission('finance', 'delete')): ?>
                        <button onclick="deleteFinance(<?= $finance['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-red-500">
                            <i class="fas fa-trash-alt mr-2 text-white"></i> Delete Ledger
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Patient Card -->
                <?php if ($patient): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 group hover:shadow-2xl transition-all duration-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-purple-600 mr-2"></i>
                        Patient Information
                    </h2>
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-xl group-hover:scale-110 transition-transform mb-4">
                            <?= esc(ui_initials($patient['first_name'] ?? '', $patient['last_name'] ?? '', 'P')) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-gray-800">
                                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?>
                            </div>
                            <div class="text-sm text-gray-500">ID: <?= $patient['patient_id'] ?></div>
                        </div>
                    </div>
                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="block w-full text-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-bold rounded-xl transition-all duration-300">
                        View Profile
                    </a>
                </div>
                <?php endif; ?>

                <!-- Metadata Card -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 text-sm group hover:shadow-2xl transition-all duration-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                        Metadata
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Standard Method</label>
                            <p class="text-gray-800 font-semibold"><?= ucfirst($finance['payment_method']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Currency</label>
                            <p class="text-gray-800 font-semibold">
                                <?= esc($baseCurrency) ?>
                                <?php if ($isConverted): ?>
                                    <span class="text-xs text-gray-500">from <?= esc($finance['currency']) ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($isConverted): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Exchange Rate</label>
                            <p class="text-gray-800 font-semibold"><?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($check_attachment)): ?>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Check Scan</label>
                            <div class="flex flex-wrap items-center gap-3">
                                <a class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-download mr-2"></i>Download
                                </a>
                                <a class="inline-flex items-center px-3 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-eye mr-2"></i>View
                                </a>
                            </div>
                            <?php
                                $isImage = !empty($check_attachment['mime_type']) && strpos($check_attachment['mime_type'], 'image/') === 0;
                            ?>
                            <?php if ($isImage): ?>
                                <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2">
                                    <img src="<?= base_url('file/download/' . $check_attachment['id']) ?>" alt="Check Scan Preview" class="max-h-56 w-auto rounded-lg">
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">Preview available for images only. Use View to open the file.</p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($finance['due_date'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Due Date</label>
                            <p class="text-rose-600 font-bold"><?= formatDate($finance['due_date']) ?></p>
                        </div>
                        <?php endif; ?>
                         <?php if (!empty($finance['paid_date'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Settlement Date</label>
                            <p class="text-emerald-600 font-bold"><?= formatDate($finance['paid_date']) ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-[10px] text-gray-400">Last Updated: <?= formatDateTime($finance['updated_at']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Related Examination -->
                <?php if ($examination): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 group hover:shadow-2xl transition-all duration-500">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-stethoscope text-emerald-600 mr-2"></i>
                        Clinical Link
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
                                Status: <?= ucfirst($examination['status']) ?>
                            </div>
                        </div>
                    </div>
                    <a href="<?= base_url('examination/' . $examination['id']) ?>" class="block w-full text-center px-4 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-sm font-bold rounded-xl transition-all duration-300">
                        View Clinical Record
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
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
    invoicePaymentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoicePaymentForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Processing...';

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
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the payment.');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
}

const invoiceRefundForm = document.getElementById('invoiceRefundForm');
if (invoiceRefundForm) {
    invoiceRefundForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoiceRefundForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Processing...';

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
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while recording the refund.');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
}

function voidPayment(paymentId) {
    if (!confirm('Void this payment? This will reverse the payment in the ledger.')) {
        return;
    }
    const reason = prompt('Reason for voiding this payment (optional):') || '';
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
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while voiding the payment.');
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
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while recording provider payout.');
        });
    });
}
</script>
<?= $this->endSection() ?>
