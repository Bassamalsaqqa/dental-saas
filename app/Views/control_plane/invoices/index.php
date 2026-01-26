<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php $filters = $filters ?? []; ?>
<div class="space-y-6">
    <div class="glass-card rounded-3xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Outstanding</p>
                <p class="text-2xl font-semibold text-slate-900"><?= number_format($stats['outstanding'] ?? 0, 2) ?></p>
                <p class="text-xs text-slate-500">Balance yet to be collected</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-emerald-50/80 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-500">Revenue this month</p>
                <p class="text-2xl font-semibold text-emerald-900"><?= number_format($stats['revenue_month'] ?? 0, 2) ?></p>
                <p class="text-xs text-slate-500">Paid invoices with issue dates this month</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-rose-50/80 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-rose-500">Overdue</p>
                <p class="text-2xl font-semibold text-rose-700"><?= (int) ($stats['overdue'] ?? 0) ?></p>
                <p class="text-xs text-slate-500">Invoices beyond their due date</p>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6">
        <form method="get" action="<?= base_url('controlplane/invoices') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-500">Status</label>
                <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <?php $filters = $filters ?? []; ?>
                    <?php $statusValue = $filters['status'] ?? ''; ?>
                    <option value="">All statuses</option>
                    <?php foreach (['draft', 'sent', 'paid', 'overdue', 'void', 'cancelled'] as $statusOption): ?>
                        <option value="<?= esc($statusOption) ?>" <?= $statusValue === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Clinic</label>
                <select name="clinic_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="">All clinics</option>
                    <?php $clinicValue = (int) ($filters['clinic_id'] ?? 0); ?>
                    <?php foreach ($clinics as $clinic): ?>
                        <option value="<?= esc($clinic['id']) ?>" <?= $clinicValue === (int) $clinic['id'] ? 'selected' : '' ?>><?= esc($clinic['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">From</label>
                <input type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">To</label>
                <div class="flex gap-2">
                    <input type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <button type="submit" class="ml-1 rounded-xl bg-indigo-600 px-4 py-2 text-white text-xs font-semibold">Filter</button>
                </div>
            </div>
            <div class="md:col-span-4 flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-white text-xs font-semibold">Apply filters</button>
                <a href="<?= base_url('controlplane/invoices') ?>" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300">Reset</a>
            </div>
        </form>
    </div>

    <div class="glass-card rounded-3xl p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-indigo-500 font-semibold">Registry</p>
                <h2 class="text-2xl font-semibold text-slate-900">Control Plane invoices</h2>
            </div>
            <a href="<?= base_url('controlplane/invoices/create') ?>" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-emerald-500/20">
                <i class="fas fa-plus"></i>
                New invoice
            </a>
        </div>
        <div class="mt-4 overflow-x-auto">
            <?php if (empty($invoices)): ?>
                <p class="text-sm text-slate-500">No invoices recorded yet. Create your first invoice to start billing clinics.</p>
            <?php else: ?>
                <table class="w-full text-sm text-slate-600">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-[0.3em] text-slate-400">
                            <th class="pb-2">Invoice #</th>
                            <th class="pb-2">Clinic</th>
                            <th class="pb-2">Issue</th>
                            <th class="pb-2">Due</th>
                            <th class="pb-2">Amount</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2">Tax %</th>
                            <th class="pb-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <?php
                                $statusClasses = [
                                    'draft' => 'bg-slate-100 text-slate-600',
                                    'sent' => 'bg-indigo-100 text-indigo-700',
                                    'paid' => 'bg-emerald-100 text-emerald-700',
                                    'overdue' => 'bg-rose-100 text-rose-700',
                                    'void' => 'bg-slate-200 text-slate-600',
                                    'cancelled' => 'bg-rose-50 text-rose-600',
                                ];
                                $statusLabel = ucfirst($invoice['status'] ?? 'draft');
                                $badgeClass = $statusClasses[$invoice['status'] ?? 'draft'] ?? 'bg-slate-100 text-slate-600';
                            ?>
                            <tr class="border-b border-slate-100">
                                <td class="py-3">
                                    <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" class="text-indigo-600 font-semibold hover:underline"><?= esc($invoice['invoice_number'] ?? '?') ?></a>
                                    <p class="text-xs text-slate-400 mt-1"><?= esc($invoice['issue_date'] ?? '--') ?></p>
                                </td>
                                <td class="py-3"><?= esc($invoice['clinic_name'] ?? '--') ?></td>
                                <td class="py-3"><?= esc($invoice['issue_date'] ?? '--') ?></td>
                                <td class="py-3"><?= esc($invoice['due_date'] ?? '--') ?></td>
                                <td class="py-3 font-semibold text-slate-900"><?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($invoice['total_amount'] ?? 0, 2) ?></td>
                                <td class="py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= $badgeClass ?>"><?= esc($statusLabel) ?></span>
                                </td>
                                <td class="py-3"><?= number_format($invoice['tax_percentage'] ?? 0, 2) ?>%</td>
                                <td class="py-3 space-y-2">
                                    <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" class="text-indigo-600 text-xs font-semibold hover:underline">View</a>
                                    <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/email') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-slate-700 text-xs font-semibold">Email</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
