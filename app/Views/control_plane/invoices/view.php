<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <?php $lineItems = json_decode($invoice['items_json'] ?? '[]', true) ?: []; ?>
    <?php $statusClasses = [
        'draft' => 'bg-slate-100 text-slate-600',
        'sent' => 'bg-indigo-100 text-indigo-700',
        'paid' => 'bg-emerald-100 text-emerald-700',
        'overdue' => 'bg-rose-100 text-rose-700',
        'void' => 'bg-slate-200 text-slate-600',
        'cancelled' => 'bg-rose-50 text-rose-600',
    ];
    $statusKey = $invoice['status'] ?? 'draft';
    $statusBadge = $statusClasses[$statusKey] ?? 'bg-slate-100 text-slate-600'; ?>

    <div class="glass-card rounded-3xl p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-indigo-500 font-semibold">Invoice</p>
                <h1 class="text-3xl font-bold text-slate-900"><?= esc($invoice['invoice_number'] ?? '?') ?></h1>
                <p class="text-sm text-slate-500">Issued for <?= esc($clinic['name'] ?? '?') ?></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center rounded-full px-4 py-2 text-xs font-semibold <?= $statusBadge ?>"><?= ucfirst($statusKey) ?></span>
                <a href="<?= base_url('controlplane/invoices') ?>" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300">Back</a>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 text-sm text-slate-600">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Issued</p>
                <p class="font-semibold text-slate-900"><?= esc($invoice['issue_date'] ?? '--') ?></p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Due</p>
                <p class="font-semibold text-slate-900"><?= esc($invoice['due_date'] ?? '--') ?></p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Currency</p>
                <p class="font-semibold text-slate-900"><?= esc($invoice['currency'] ?? 'USD') ?></p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Subtotal</p>
                <p class="text-2xl font-semibold text-slate-900"><?= number_format($invoice['subtotal'] ?? 0, 2) ?></p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-indigo-50/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Tax (<?= number_format($invoice['tax_percentage'] ?? 0, 2) ?>%)</p>
                <p class="text-2xl font-semibold text-indigo-700"><?= number_format($invoice['tax_amount'] ?? 0, 2) ?></p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-emerald-50/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-500">Total</p>
                <p class="text-2xl font-semibold text-emerald-900"><?= number_format($invoice['total_amount'] ?? 0, 2) ?></p>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">Line items</h3>
            <span class="text-xs text-slate-500">Tax <?= number_format($invoice['tax_percentage'] ?? 0, 2) ?>%</span>
        </div>
        <div class="overflow-x-auto">
            <?php if (empty($lineItems)): ?>
                <p class="text-sm text-slate-500">No line items provided.</p>
            <?php else: ?>
                <table class="w-full text-sm text-slate-600">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-[0.3em] text-slate-400">
                            <th class="pb-2">Description</th>
                            <th class="pb-2">Quantity</th>
                            <th class="pb-2">Unit price</th>
                            <th class="pb-2">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lineItems as $item): ?>
                            <tr class="border-b border-slate-100">
                                <td class="py-3"><?= esc($item['description'] ?? '--') ?></td>
                                <td class="py-3"><?= esc($item['quantity'] ?? 0) ?></td>
                                <td class="py-3"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                                <td class="py-3 font-semibold text-slate-900"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format((($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass-card rounded-3xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Payment history</h3>
                <span class="text-xs text-slate-500">Clocked payments</span>
            </div>
            <?php if (empty($payments)): ?>
                <p class="text-sm text-slate-500">No payments logged yet.</p>
            <?php else: ?>
                <table class="w-full text-sm text-slate-600">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-[0.3em] text-slate-400">
                            <th class="pb-2">Amount</th>
                            <th class="pb-2">Method</th>
                            <th class="pb-2">Reference</th>
                            <th class="pb-2">Paid at</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr class="border-b border-slate-100">
                                <td class="py-3 font-semibold text-slate-900"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($payment['amount'] ?? 0, 2) ?></td>
                                <td class="py-3"><?= esc($payment['payment_method'] ?? 'manual') ?></td>
                                <td class="py-3"><?= esc($payment['transaction_reference'] ?? '?') ?></td>
                                <td class="py-3"><?= esc($payment['paid_at'] ?? '--') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="space-y-4">
            <?php if (($invoice['status'] ?? '') !== 'paid'): ?>
                <div class="glass-card rounded-3xl p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-slate-900">Mark as paid</h3>
                    <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/mark-paid') ?>" method="post" class="space-y-3">
                        <?= csrf_field() ?>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Amount</label>
                            <input type="number" name="payment_amount" value="<?= esc($invoice['total_amount'] ?? 0) ?>" step="0.01" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Payment method</label>
                            <input type="text" name="payment_method" value="manual" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Transaction reference</label>
                            <input type="text" name="transaction_reference" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Optional reference">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Notes</label>
                            <textarea name="payment_notes" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Optional internal note"></textarea>
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white">Record payment</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="glass-card rounded-3xl p-6">
                    <p class="text-sm text-emerald-600">Invoice already marked as paid on <?= esc($invoice['paid_at'] ?? '--') ?>.</p>
                </div>
            <?php endif; ?>
            <div class="glass-card rounded-3xl p-6 space-y-3">
                <h3 class="text-lg font-semibold text-slate-900">Invoice Actions</h3>
                <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/email') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700">Email invoice</button>
                </form>
            </div>
        </div>
    </div>

    <?php if (!empty($invoice['notes'])): ?>
        <div class="glass-card rounded-3xl p-6">
            <h3 class="text-xs uppercase tracking-[0.3em] text-slate-400">Public notes</h3>
            <p class="text-sm text-slate-600"><?= esc($invoice['notes']) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($invoice['private_notes'])): ?>
        <div class="glass-card rounded-3xl p-6">
            <h3 class="text-xs uppercase tracking-[0.3em] text-slate-400">Internal notes</h3>
            <p class="text-sm text-slate-600"><?= esc($invoice['private_notes']) ?></p>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
