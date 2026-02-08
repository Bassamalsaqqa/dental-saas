<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $totalAmount = $finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']); ?>
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
                                    Financial Transaction
                                </h1>
                                <p class="text-gray-600">Transaction ID: <?= $finance['transaction_id'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-sm font-medium rounded-xl hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Finance
                        </a>
                        <a href="<?= base_url('finance/' . $finance['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-edit mr-2"></i>Edit Transaction
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
                                <?= number_format($finance['amount'], 2) ?> <span class="text-sm text-gray-500 font-medium"><?= $finance['currency'] ?></span>
                            </div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Base Amount</div>
                        </div>
                    </div>
                    
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group">
                        <div class="text-center">
                            <div class="text-xl font-bold text-gray-900 mb-1 flex items-center justify-center">
                                <i class="fas fa-exchange-alt mr-2 text-blue-500/50 group-hover:rotate-180 transition-transform duration-500"></i>
                                <?= ucfirst(str_replace('_', ' ', $finance['transaction_type'])) ?>
                            </div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Transaction Type</div>
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
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Payment Status</div>
                        </div>
                    </div>
                </div>

                <!-- Financial Details & Breakdown -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <i class="fas fa-list-ul"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Details & Breakdown</h3>
                        </div>
                        <span class="text-xs font-black px-3 py-1.5 bg-blue-500 text-white rounded-xl uppercase tracking-tighter shadow-lg">
                            <?= ucfirst(str_replace('_', ' ', $finance['service_type'])) ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-8">
                            <!-- Description -->
                            <div class="space-y-6">
                                <div class="group/info">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] block mb-3">Service Description</label>
                                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 text-gray-700 text-sm leading-relaxed group-hover/info:bg-white group-hover/info:shadow-inner transition-all duration-300">
                                        <?= !empty($finance['description']) ? nl2br(htmlspecialchars($finance['description'])) : '<span class="text-gray-400 italic">No description provided</span>' ?>
                                    </div>
                                </div>
                                <?php if (!empty($finance['service_details'])): ?>
                                <div class="group/info">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] block mb-3">Technical Details</label>
                                    <div class="p-5 bg-blue-50/30 rounded-2xl border border-blue-100/50 text-gray-700 text-sm leading-relaxed group-hover/info:bg-white group-hover/info:shadow-inner transition-all duration-300">
                                        <?= nl2br(htmlspecialchars($finance['service_details'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Calculations -->
                            <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 rounded-3xl p-8 border border-slate-200/60 shadow-inner">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500 font-medium uppercase tracking-widest">Subtotal</span>
                                        <span class="font-bold text-gray-900"><?= number_format($finance['amount'], 2) ?></span>
                                    </div>
                                    <?php if ($finance['discount_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-rose-600 bg-rose-50 px-3 py-2 rounded-lg">
                                        <span class="font-bold uppercase tracking-widest">Discount</span>
                                        <span class="font-black">-<?= number_format($finance['discount_amount'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['tax_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded-lg">
                                        <span class="font-bold uppercase tracking-widest">Tax (VAT)</span>
                                        <span class="font-black">+<?= number_format($finance['tax_amount'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="border-t-2 border-slate-200 pt-6 mt-6 flex justify-between items-end">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-1">Total Due</span>
                                            <span class="font-black text-gray-900 text-3xl leading-none tracking-tight">
                                                <?= number_format($totalAmount, 2) ?>
                                            </span>
                                        </div>
                                        <span class="text-sm font-black text-blue-600 bg-blue-100 px-3 py-1 rounded-lg uppercase"><?= $finance['currency'] ?></span>
                                    </div>
                                    
                                    <?php if ($finance['transaction_type'] === 'invoice'): ?>
                                        <div class="pt-6 mt-6 border-t border-slate-200 border-dashed space-y-3">
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-emerald-600 font-bold uppercase tracking-widest">Paid to Date</span>
                                                <span class="text-emerald-700 font-black"><?= number_format($finance['amount_paid'] ?? 0, 2) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm bg-rose-50 p-4 rounded-2xl border border-rose-100 shadow-sm">
                                                <span class="text-rose-600 font-black uppercase tracking-widest">Balance Due</span>
                                                <span class="text-rose-700 text-2xl font-black"><?= number_format($finance['balance_due'] ?? $totalAmount, 2) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($finance['transaction_type'] === 'invoice'): ?>
                <!-- Invoice Payments Ledger -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white flex items-center space-x-3">
                        <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Payments Ledger</h3>
                            <p class="text-xs text-gray-500">History of Settlements</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Method</th>
                                    <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Notes</th>
                                    <?php if (has_permission('finance', 'edit')): ?>
                                    <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <?php if (!empty($payments)): ?>
                                    <?php foreach ($payments as $payment): ?>
                                    <?php
                                        $type = $payment['type'] ?? 'payment';
                                        $typeClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                        $amountClass = 'text-emerald-700';
                                        $prefix = '+';
                                        
                                        if ($type === 'refund') {
                                            $typeClass = 'bg-rose-100 text-rose-700 border-rose-200';
                                            $amountClass = 'text-rose-700';
                                            $prefix = '-';
                                        } elseif ($type === 'void') {
                                            $typeClass = 'bg-slate-100 text-slate-500 border-slate-200 line-through opacity-50';
                                            $amountClass = 'text-slate-400 line-through opacity-50';
                                            $prefix = '';
                                        }
                                    ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-5 whitespace-nowrap text-sm font-bold text-slate-700">
                                            <?= date('M j, Y', strtotime($payment['payment_date'])) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black border uppercase tracking-wider <?= $typeClass ?>">
                                                <?= ucfirst($type) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-slate-600">
                                            <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-sm font-black text-right <?= $amountClass ?>">
                                            <?= $prefix . number_format($payment['amount'], 2) ?>
                                        </td>
                                        <td class="px-6 py-5 text-sm text-slate-500 truncate max-w-xs italic">
                                            <?= $payment['notes'] ? esc($payment['notes']) : '<span class="text-slate-300">—</span>' ?>
                                        </td>
                                        <?php if (has_permission('finance', 'edit')): ?>
                                        <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if ($type === 'payment'): ?>
                                                <button type="button" onclick="voidPayment(<?= (int) $payment['id'] ?>)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all" title="Void Payment">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400 font-medium">
                                            <i class="fas fa-inbox text-3xl mb-3 block opacity-20"></i>
                                            No payment history found for this invoice.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (has_permission('finance', 'edit')): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Record Payment Form -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group/card">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-transparent flex items-center space-x-4">
                            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 shadow-inner group-hover/card:scale-110 transition-transform">
                                <i class="fas fa-plus-circle text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Settlement</h4>
                                <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-tighter">Receive new payment</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <form id="invoicePaymentForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <div class="group/field">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Amount</label>
                                    <div class="relative group">
                                        <div class="absolute inset-0 bg-emerald-500/5 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="relative">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5">
                                                <span class="text-slate-400 font-black text-lg">$</span>
                                            </div>
                                            <input type="number" name="amount" step="0.01" min="0" 
                                                   class="w-full pl-10 pr-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-lg font-black text-slate-800 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all duration-300 shadow-inner" 
                                                   placeholder="0.00" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="group/field">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Method</label>
                                        <select name="payment_method" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all duration-300 shadow-inner appearance-none">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="bank_transfer">Wire</option>
                                            <option value="check">Check</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="group/field">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Date</label>
                                        <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" 
                                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 focus:bg-white transition-all duration-300 shadow-inner">
                                    </div>
                                </div>
                                <div class="group/field">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Memo</label>
                                    <textarea name="notes" rows="2" 
                                              class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 focus:bg-white transition-all duration-300 shadow-inner resize-none" 
                                              placeholder="Details..."></textarea>
                                </div>
                                <button type="submit" class="w-full relative flex justify-center items-center py-4 px-6 border-transparent rounded-2xl shadow-xl text-sm font-black text-white bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 transition-all transform hover:-translate-y-1 active:translate-y-0 group/btn">
                                    <i class="fas fa-check-double mr-3 group-hover/btn:animate-bounce"></i>
                                    Post
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Record Refund Form -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl overflow-hidden group/card">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-rose-50 to-transparent flex items-center space-x-4">
                            <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 shadow-inner group-hover/card:scale-110 transition-transform">
                                <i class="fas fa-undo-alt text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Reversal</h4>
                                <p class="text-[10px] text-rose-600 font-bold uppercase tracking-tighter">Process refund</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <form id="invoiceRefundForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <div class="group/field">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Amount</label>
                                    <div class="relative group">
                                        <div class="absolute inset-0 bg-rose-500/5 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="relative">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5">
                                                <span class="text-slate-400 font-black text-lg">$</span>
                                            </div>
                                            <input type="number" name="amount" step="0.01" min="0" 
                                                   class="w-full pl-10 pr-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-lg font-black text-slate-800 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 focus:bg-white transition-all duration-300 shadow-inner" 
                                                   placeholder="0.00" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="group/field">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Method</label>
                                        <select name="payment_method" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all duration-300 shadow-inner appearance-none">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="bank_transfer">Wire</option>
                                            <option value="check">Check</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="group/field">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Date</label>
                                        <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" 
                                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 focus:bg-white transition-all duration-300 shadow-inner">
                                    </div>
                                </div>
                                <div class="group/field">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Reason</label>
                                    <textarea name="notes" rows="2" 
                                              class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 focus:bg-white transition-all duration-300 shadow-inner resize-none" 
                                              placeholder="Justification..."></textarea>
                                </div>
                                <button type="submit" class="w-full flex justify-center items-center py-4 px-6 border-2 border-rose-200 rounded-2xl shadow-sm text-sm font-black text-rose-600 bg-white hover:bg-rose-50 transition-all transform hover:-translate-y-1 active:translate-y-0 group/btn">
                                    <i class="fas fa-reply-all mr-3 group-hover/btn:-rotate-45 transition-transform"></i>
                                    Reverse
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($finance['notes'])): ?>
                <!-- Additional Notes -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-10 group hover:shadow-2xl transition-all duration-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-400/5 rounded-full -translate-y-16 translate-x-16"></div>
                    <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center">
                        <i class="fas fa-sticky-note mr-3 text-yellow-500"></i> Administrative Notes
                    </h3>
                    <div class="text-base text-slate-600 bg-gradient-to-br from-yellow-50/50 to-orange-50/20 p-8 rounded-2xl border border-yellow-100 shadow-inner leading-loose">
                        <?= nl2br(htmlspecialchars($finance['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Sidebar (1/3) -->
            <div class="space-y-6">
                
                <!-- Action Card -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 group hover:shadow-2xl transition-all duration-500">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6 flex items-center">
                        <i class="fas fa-cogs mr-2"></i> Control Panel
                    </h3>
                    <div class="space-y-4">
                        <?php if ($finance['payment_status'] != 'paid'): ?>
                            <button onclick="markAsPaid(<?= $finance['id'] ?>)" class="w-full flex items-center justify-between px-5 py-4 bg-gradient-to-br from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white text-sm font-black rounded-2xl transition-all shadow-lg hover:shadow-emerald-500/20 transform hover:-translate-y-1 group/mark">
                                <span>Settle All</span>
                                <i class="fas fa-check-circle group-hover/mark:scale-125 transition-transform"></i>
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="w-full flex items-center justify-between px-5 py-4 bg-white border-2 border-slate-100 hover:border-blue-200 hover:bg-blue-50 text-slate-700 text-sm font-bold rounded-2xl transition-all group/print">
                            <span>Export Invoice</span>
                            <i class="fas fa-file-pdf text-slate-300 group-hover/print:text-blue-500 group-hover/print:scale-110 transition-all"></i>
                        </a>

                        <?php if (has_permission('finance', 'delete')): ?>
                        <div class="pt-4 border-t border-slate-100 mt-4">
                            <form action="<?= base_url('finance/' . $finance['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Are you sure? This will wipe the transaction from existence.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="w-full flex items-center justify-between px-5 py-3 text-rose-500 hover:bg-rose-50 text-[11px] font-black uppercase tracking-widest rounded-xl transition-all group/del">
                                    <span>Delete Ledger</span>
                                    <i class="fas fa-trash-alt opacity-30 group-hover/del:opacity-100 group-hover/del:scale-110 transition-all"></i>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Patient Card -->
                <?php if ($patient): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 hover:shadow-2xl transition-all duration-500 group/patient">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Subject</h3>
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-3xl flex items-center justify-center text-white font-black text-2xl shadow-xl group-hover/patient:rotate-6 transition-transform mb-4">
                            <?= substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="text-base font-black text-slate-800 truncate">
                                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?>
                            </div>
                            <div class="text-xs font-bold text-blue-500 mt-1 uppercase tracking-tighter">Patient Record #<?= $patient['patient_id'] ?></div>
                        </div>
                    </div>
                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="block w-full text-center px-4 py-3 bg-slate-50 hover:bg-blue-600 hover:text-white text-slate-600 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl border border-slate-100 transition-all duration-300">
                        Subject Profile
                    </a>
                </div>
                <?php endif; ?>

                <!-- Metadata Card -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 text-sm hover:shadow-2xl transition-all duration-500">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Registry Info</h3>
                    <div class="space-y-4">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Standard Method</span>
                            <span class="font-bold text-slate-800"><?= ucfirst($finance['payment_method']) ?></span>
                        </div>
                        <?php if (!empty($finance['due_date'])): ?>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Expiration / Due</span>
                            <span class="font-bold text-rose-600"><?= date('M j, Y', strtotime($finance['due_date'])) ?></span>
                        </div>
                        <?php endif; ?>
                         <?php if (!empty($finance['paid_date'])): ?>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Settlement Date</span>
                            <span class="font-bold text-emerald-600"><?= date('M j, Y', strtotime($finance['paid_date'])) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="pt-4 border-t border-slate-50 mt-2">
                            <div class="flex justify-between items-center opacity-40">
                                <span class="text-[8px] font-black uppercase tracking-widest italic">Rev.</span>
                                <span class="text-[8px] font-black tracking-tighter"><?= date('M j, g:i a', strtotime($finance['updated_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Examination -->
                <?php if ($examination): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6 hover:shadow-2xl transition-all duration-500 group/exam">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Clinical Link</h3>
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 shadow-inner group-hover/exam:bg-emerald-100 transition-colors">
                            <i class="fas fa-stethoscope text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-black text-slate-800">
                                <?= date('M j, Y', strtotime($examination['examination_date'])) ?>
                            </div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1">
                                Status: <?= ucfirst($examination['status']) ?>
                            </div>
                        </div>
                    </div>
                    <a href="<?= base_url('examination/' . $examination['id']) ?>" class="text-[10px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest flex items-center justify-center group/link transition-colors">
                        Clinical Record <i class="fas fa-chevron-right ml-2 text-[8px] group-hover/link:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function markAsPaid(transactionId) {
    if (confirm('Are you sure you want to mark this transaction as paid?')) {
        fetch(`<?= base_url('finance') ?>/${transactionId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
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
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the transaction.');
        });
    }
}

const invoicePaymentForm = document.getElementById('invoicePaymentForm');
if (invoicePaymentForm) {
    invoicePaymentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoicePaymentForm);
        
        // Disable button to prevent double submit
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
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
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the payment.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
}

const invoiceRefundForm = document.getElementById('invoiceRefundForm');
if (invoiceRefundForm) {
    invoiceRefundForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoiceRefundForm);
        
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/refund`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
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
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while recording the refund.');
            btn.disabled = false;
            btn.innerHTML = originalText;
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
    fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/${paymentId}/void`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
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
</script>
<?= $this->endSection() ?>