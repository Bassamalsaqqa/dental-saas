<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="v3-platform-workspace min-h-screen relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">


    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-4">
            <div class="cp-surface border cp-border cp-radius p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 cp-radius flex items-center justify-center text-white">
                            <i class="fas fa-file-invoice text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold text-gray-900"><?= esc($invoice['invoice_number'] ?? 'Invoice') ?></h1>
                            <p class="text-gray-600"><?= esc(lang('V3Platform.issuedTo')) ?> <?= esc($clinic['name'] ?? 'Unknown Clinic') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="<?= base_url('controlplane/invoices') ?>" class="inline-flex items-center gap-2 px-4 py-2 cp-surface border cp-border cp-radius text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                            <i class="fas fa-arrow-left"></i> <?= esc(lang('V3Platform.back')) ?>
                        </a>
                        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 cp-primary-surface text-white cp-radius text-sm font-semibold hover:bg-indigo-700 transition-all">
                            <i class="fas fa-print"></i> <?= esc(lang('V3Platform.print')) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Invoice Document -->
            <div class="lg:col-span-2 space-y-6">
                <div class="cp-surface cp-radius overflow-hidden border cp-border relative">
                    <!-- Status Stamp -->
                    <?php if (($invoice['status'] ?? '') === 'paid'): ?>
                        <div class="absolute top-8 right-8 pointer-events-none opacity-20 transform rotate-12 border-4 border-emerald-600 text-emerald-600 cp-title font-black uppercase px-6 py-2 cp-radius">
                            <?= esc(lang('V3Platform.paid')) ?>
                        </div>
                    <?php elseif (($invoice['status'] ?? '') === 'overdue'): ?>
                        <div class="absolute top-8 right-8 pointer-events-none opacity-20 transform rotate-12 border-4 border-rose-600 text-rose-600 cp-title font-black uppercase px-6 py-2 cp-radius">
                            <?= esc(lang('V3Platform.overdue2')) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Document Header -->
                    <div class="px-8 py-8 border-b cp-border cp-surface">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] cp-accent font-bold mb-2"><?= esc(lang('V3Platform.invoiceFrom')) ?></p>
                                <h2 class="text-xl font-bold text-gray-900"><?= esc(lang('V3Platform.controlPlane')) ?></h2>
                                <p class="text-sm text-gray-500 mt-1"><?= esc(lang('V3Platform.saasPlatformBilling')) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400 font-bold mb-2"><?= esc(lang('V3Platform.invoiceTo')) ?></p>
                                <h2 class="text-xl font-bold text-gray-900"><?= esc($clinic['name'] ?? '--') ?></h2>
                                <p class="text-sm text-gray-500 mt-1"><?= esc(lang('V3Platform.id')) ?> <?= esc($clinic['id'] ?? '--') ?></p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-8 pt-6 border-t cp-border">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider"><?= esc(lang('V3Platform.issueDate')) ?></p>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($invoice['issue_date'] ?? '--') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider"><?= esc(lang('V3Platform.dueDate')) ?></p>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($invoice['due_date'] ?? '--') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider"><?= esc(lang('V3Platform.amountDue')) ?></p>
                                <p class="text-xl font-bold cp-accent"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($invoice['total_amount'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="px-8 py-8">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase cp-surface font-bold">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg"><?= esc(lang('V3Platform.description')) ?></th>
                                    <th class="px-4 py-3 text-center"><?= esc(lang('V3Platform.qty')) ?></th>
                                    <th class="px-4 py-3 text-right"><?= esc(lang('V3Platform.price')) ?></th>
                                    <th class="px-4 py-3 text-right rounded-r-lg"><?= esc(lang('V3Platform.total')) ?></th>
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
                                    <span><?= esc(lang('V3Platform.subtotal')) ?></span>
                                    <span class="font-mono"><?= number_format($invoice['subtotal'] ?? 0, 2) ?></span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Tax (<?= number_format($invoice['tax_percentage'] ?? 0, 2) ?>%)</span>
                                    <span class="font-mono"><?= number_format($invoice['tax_amount'] ?? 0, 2) ?></span>
                                </div>
                                <div class="flex justify-between text-lg font-bold cp-accent pt-3 border-t cp-border">
                                    <span><?= esc(lang('V3Platform.total')) ?></span>
                                    <span class="font-mono"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($invoice['total_amount'] ?? 0, 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Notes -->
                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="px-8 py-6 cp-surface border-t cp-border">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.notes')) ?></p>
                            <p class="text-sm text-gray-600"><?= esc($invoice['notes']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-6 print:hidden">
                <!-- Status Card -->
                <div class="cp-surface border cp-border cp-radius p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4"><?= esc(lang('V3Platform.status')) ?></h3>
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
                    <div class="flex items-center justify-between p-3 cp-radius <?= $statusBadge ?> bg-opacity-50 border border-current border-opacity-10">
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
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 cp-surface border cp-border cp-radius text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                                <i class="fas fa-envelope text-gray-400"></i> <?= esc(lang('V3Platform.emailInvoice')) ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Record Payment -->
                <?php if (($invoice['status'] ?? '') !== 'paid'): ?>
                    <div class="cp-surface border cp-border cp-radius p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 cp-soft-surface rounded-lg flex items-center justify-center text-emerald-600">
                                <i class="fas fa-money-bill-wave text-sm"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide"><?= esc(lang('V3Platform.recordPayment')) ?></h3>
                        </div>
                        <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/mark-paid') ?>" method="post" class="space-y-4">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5"><?= esc(lang('V3Platform.amount')) ?></label>
                                <input type="number" name="payment_amount" value="<?= esc($invoice['total_amount'] ?? 0) ?>" step="0.01" class="w-full px-3 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5"><?= esc(lang('V3Platform.method')) ?></label>
                                <select name="payment_method" class="w-full px-3 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-2 focus:ring-emerald-500">
                                    <option value="bank_transfer"><?= esc(lang('V3Platform.bankTransfer')) ?></option>
                                    <option value="card"><?= esc(lang('V3Platform.creditCard')) ?></option>
                                    <option value="cash"><?= esc(lang('V3Platform.cash')) ?></option>
                                    <option value="check"><?= esc(lang('V3Platform.check')) ?></option>
                                    <option value="other"><?= esc(lang('V3Platform.other')) ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5"><?= esc(lang('V3Platform.reference')) ?></label>
                                <input type="text" name="transaction_reference" placeholder="<?= esc(lang('V3Platform.txnIdCheck'), 'attr') ?>" class="w-full px-3 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5"><?= esc(lang('V3Platform.notes')) ?></label>
                                <textarea name="payment_notes" rows="2" class="w-full px-3 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 cp-primary-surface text-white font-bold cp-radius hover:bg-emerald-700 transition-colors">
                                <?= esc(lang('V3Platform.recordPayment')) ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Payment History -->
                <div class="cp-surface border cp-border cp-radius p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4"><?= esc(lang('V3Platform.history')) ?></h3>
                    <?php if (empty($payments)): ?>
                        <p class="text-xs text-slate-500 text-center py-4"><?= esc(lang('V3Platform.noPaymentsRecorded')) ?></p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($payments as $payment): ?>
                                <div class="flex items-center justify-between p-3 cp-radius cp-surface border cp-border">
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
                    <div class="bg-yellow-50/80 border border-yellow-200 cp-radius p-6">
                        <h3 class="text-xs font-bold text-yellow-700 uppercase tracking-wide mb-2"><?= esc(lang('V3Platform.internalNotes')) ?></h3>
                        <p class="text-sm text-yellow-800"><?= esc($invoice['private_notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>