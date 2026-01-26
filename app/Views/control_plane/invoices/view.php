<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-file-invoice text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= esc($invoice['invoice_number'] ?? 'Invoice') ?></h1>
                            <p class="text-gray-600">Issued to <?= esc($clinic['name'] ?? 'Unknown Clinic') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="<?= base_url('controlplane/invoices') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-sm hover:shadow-md">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-all shadow-md">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Invoice Document -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100 relative">
                    <!-- Status Stamp -->
                    <?php if (($invoice['status'] ?? '') === 'paid'): ?>
                        <div class="absolute top-8 right-8 pointer-events-none opacity-20 transform rotate-12 border-4 border-emerald-600 text-emerald-600 text-4xl font-black uppercase px-6 py-2 rounded-xl">
                            PAID
                        </div>
                    <?php elseif (($invoice['status'] ?? '') === 'overdue'): ?>
                        <div class="absolute top-8 right-8 pointer-events-none opacity-20 transform rotate-12 border-4 border-rose-600 text-rose-600 text-4xl font-black uppercase px-6 py-2 rounded-xl">
                            OVERDUE
                        </div>
                    <?php endif; ?>

                    <!-- Document Header -->
                    <div class="px-8 py-8 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-indigo-500 font-bold mb-2">Invoice From</p>
                                <h2 class="text-xl font-bold text-gray-900">Control Plane</h2>
                                <p class="text-sm text-gray-500 mt-1">SaaS Platform Billing</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400 font-bold mb-2">Invoice To</p>
                                <h2 class="text-xl font-bold text-gray-900"><?= esc($clinic['name'] ?? '--') ?></h2>
                                <p class="text-sm text-gray-500 mt-1">ID: <?= esc($clinic['id'] ?? '--') ?></p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Issue Date</p>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($invoice['issue_date'] ?? '--') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Due Date</p>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($invoice['due_date'] ?? '--') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Amount Due</p>
                                <p class="text-xl font-bold text-indigo-600"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($invoice['total_amount'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="px-8 py-8">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 font-bold">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg">Description</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Price</th>
                                    <th class="px-4 py-3 text-right rounded-r-lg">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $lineItems = json_decode($invoice['items_json'] ?? '[]', true) ?: []; ?>
                                <?php foreach ($lineItems as $item): ?>
                                    <tr>
                                        <td class="px-4 py-4 font-medium text-gray-900"><?= esc($item['description'] ?? '--') ?></td>
                                        <td class="px-4 py-4 text-center text-gray-600"><?= esc($item['quantity'] ?? 0) ?></td>
                                        <td class="px-4 py-4 text-right text-gray-600 font-mono"><?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                                        <td class="px-4 py-4 text-right font-bold text-gray-900 font-mono"><?= number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="flex justify-end mt-8">
                            <div class="w-64 space-y-3">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span class="font-mono"><?= number_format($invoice['subtotal'] ?? 0, 2) ?></span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Tax (<?= number_format($invoice['tax_percentage'] ?? 0, 2) ?>%)</span>
                                    <span class="font-mono"><?= number_format($invoice['tax_amount'] ?? 0, 2) ?></span>
                                </div>
                                <div class="flex justify-between text-lg font-bold text-indigo-900 pt-3 border-t border-gray-200">
                                    <span>Total</span>
                                    <span class="font-mono"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($invoice['total_amount'] ?? 0, 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Notes -->
                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notes</p>
                            <p class="text-sm text-gray-600"><?= esc($invoice['notes']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-6 print:hidden">
                <!-- Status Card -->
                <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Status</h3>
                    <?php
                        $statusClasses = [
                            'draft' => 'bg-slate-100 text-slate-700',
                            'sent' => 'bg-indigo-100 text-indigo-700',
                            'paid' => 'bg-emerald-100 text-emerald-700',
                            'overdue' => 'bg-rose-100 text-rose-700',
                            'void' => 'bg-slate-200 text-slate-600',
                            'cancelled' => 'bg-rose-50 text-rose-600',
                        ];
                        $statusKey = $invoice['status'] ?? 'draft';
                        $statusBadge = $statusClasses[$statusKey] ?? 'bg-slate-100 text-slate-600';
                    ?>
                    <div class="flex items-center justify-between p-3 rounded-xl <?= $statusBadge ?> bg-opacity-50 border border-current border-opacity-10">
                        <span class="font-bold"><?= ucfirst($statusKey) ?></span>
                        <?php if ($statusKey === 'paid'): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php elseif ($statusKey === 'overdue'): ?>
                            <i class="fas fa-exclamation-triangle"></i>
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i>
                        <?php endif; ?>
                    </div>

                    <div class="mt-6 space-y-3">
                        <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/email') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-sm">
                                <i class="fas fa-envelope text-gray-400"></i> Email Invoice
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Record Payment -->
                <?php if (($invoice['status'] ?? '') !== 'paid'): ?>
                    <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600">
                                <i class="fas fa-money-bill-wave text-sm"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Record Payment</h3>
                        </div>
                        <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/mark-paid') ?>" method="post" class="space-y-4">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Amount</label>
                                <input type="number" name="payment_amount" value="<?= esc($invoice['total_amount'] ?? 0) ?>" step="0.01" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Method</label>
                                <select name="payment_method" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="card">Credit Card</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Reference</label>
                                <input type="text" name="transaction_reference" placeholder="Txn ID / Check #" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Notes</label>
                                <textarea name="payment_notes" rows="2" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-500/20">
                                Record Payment
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Payment History -->
                <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">History</h3>
                    <?php if (empty($payments)): ?>
                        <p class="text-xs text-slate-500 text-center py-4">No payments recorded.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($payments as $payment): ?>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($payment['amount'] ?? 0, 2) ?></p>
                                        <p class="text-xs text-gray-500"><?= esc($payment['payment_method'] ?? 'manual') ?> • <?= esc($payment['paid_at'] ?? '--') ?></p>
                                    </div>
                                    <span class="text-xs font-mono text-gray-400"><?= esc($payment['transaction_reference'] ?? '#') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Internal Notes -->
                <?php if (!empty($invoice['private_notes'])): ?>
                    <div class="bg-yellow-50/80 backdrop-blur-xl border border-yellow-200 rounded-2xl shadow-sm p-6">
                        <h3 class="text-xs font-bold text-yellow-700 uppercase tracking-wide mb-2">Internal Notes</h3>
                        <p class="text-sm text-yellow-800"><?= esc($invoice['private_notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>