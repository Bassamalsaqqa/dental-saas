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
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-file-invoice-dollar text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
                            <p class="text-gray-600">Manage billing and collections</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="<?= base_url('controlplane/invoices/create') ?>" class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-blue-500/25">
                            <i class="fas fa-plus mr-2"></i>
                            New Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Outstanding -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-purple-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-xl">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Outstanding</p>
                            <p class="text-3xl font-black text-slate-900"><?= number_format($stats['outstanding'] ?? 0, 2) ?></p>
                            <p class="text-xs text-slate-500">To be collected</p>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Revenue (Month)</p>
                            <p class="text-3xl font-black text-emerald-900"><?= number_format($stats['revenue_month'] ?? 0, 2) ?></p>
                            <p class="text-xs text-emerald-600 font-medium">Collected this month</p>
                        </div>
                    </div>
                </div>

                <!-- Overdue -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-500/20 to-red-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-rose-500/10 group-hover:shadow-rose-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-xl">
                                <i class="fas fa-exclamation-circle text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Overdue</p>
                            <p class="text-3xl font-black text-rose-900"><?= (int) ($stats['overdue'] ?? 0) ?></p>
                            <p class="text-xs text-rose-600 font-medium">Invoices past due</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/30 p-6">
                <form method="get" action="<?= base_url('controlplane/invoices') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <?php $statusValue = $filters['status'] ?? ''; ?>
                            <option value="">All statuses</option>
                            <?php foreach (['draft', 'sent', 'paid', 'overdue', 'void', 'cancelled'] as $statusOption): ?>
                                <option value="<?= esc($statusOption) ?>" <?= $statusValue === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Clinic</label>
                        <select name="clinic_id" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">All clinics</option>
                            <?php $clinicValue = (int) ($filters['clinic_id'] ?? 0); ?>
                            <?php foreach ($clinics as $clinic): ?>
                                <option value="<?= esc($clinic['id']) ?>" <?= $clinicValue === (int) $clinic['id'] ? 'selected' : '' ?>><?= esc($clinic['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Date Range</label>
                        <div class="flex gap-2">
                            <input type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                            <input type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-md">
                            Filter
                        </button>
                        <a href="<?= base_url('controlplane/invoices') ?>" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-colors">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                        <i class="fas fa-list text-sm"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Registry</h3>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <?php if (empty($invoices)): ?>
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-invoice text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">No invoices found</h3>
                        <p class="text-gray-500 text-sm">Adjust filters or create a new invoice.</p>
                    </div>
                <?php else: ?>
                    <table class="w-full text-sm text-slate-600">
                        <thead class="bg-gray-50/50 backdrop-blur-sm">
                            <tr class="text-left text-xs uppercase tracking-wider text-gray-500 font-bold">
                                <th class="px-6 py-3">Invoice #</th>
                                <th class="px-6 py-3">Clinic</th>
                                <th class="px-6 py-3">Issue Date</th>
                                <th class="px-6 py-3">Due Date</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-transparent">
                            <?php foreach ($invoices as $invoice): ?>
                                <?php
                                    $statusClasses = [
                                        'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        'sent' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'overdue' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        'void' => 'bg-gray-100 text-gray-500 border-gray-200',
                                        'cancelled' => 'bg-rose-50 text-rose-600 border-rose-100',
                                    ];
                                    $statusKey = $invoice['status'] ?? 'draft';
                                    $badgeClass = $statusClasses[$statusKey] ?? 'bg-slate-100 text-slate-600';
                                ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group/row">
                                    <td class="px-6 py-4 font-mono font-medium text-indigo-600">
                                        <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" class="hover:underline">
                                            <?= esc($invoice['invoice_number'] ?? '?') ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <?= esc($invoice['clinic_name'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?= esc($invoice['issue_date'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?= esc($invoice['due_date'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">
                                        <?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($invoice['total_amount'] ?? 0, 2) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $badgeClass ?>">
                                            <?= ucfirst($statusKey) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/email') ?>" method="post" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="Email Invoice">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>