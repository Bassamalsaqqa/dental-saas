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
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('controlplane/invoices/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
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
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-50 to-indigo-50 px-3 py-1.5 rounded-full border border-blue-100">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <span class="text-blue-700 font-bold text-xs">Pending</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Outstanding</p>
                            <p class="text-3xl font-black text-slate-900"><?= number_format($stats['outstanding'] ?? 0, 2) ?></p>
                            <p class="text-xs text-slate-500">Balance to collect</p>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-50 to-teal-50 px-3 py-1.5 rounded-full border border-emerald-100">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                                <span class="text-emerald-700 font-bold text-xs">Income</span>
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
                    <div class="absolute inset-0 bg-gradient-to-br from-red-600/20 to-rose-700/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-rose-500/10 group-hover:shadow-rose-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col border-rose-200/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <i class="fas fa-exclamation-triangle text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-red-100 to-rose-100 px-3 py-1.5 rounded-full border border-red-200">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                <span class="text-red-700 font-bold text-xs">Overdue</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Overdue</p>
                            <p class="text-3xl font-black text-red-900"><?= (int) ($stats['overdue'] ?? 0) ?></p>
                            <p class="text-xs text-red-600 font-medium">Action required</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Table & Filters Card -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/30 overflow-hidden">
            <!-- Table Header Bar -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                        <h3 class="text-lg font-bold text-gray-900 uppercase tracking-tight">Invoice Registry</h3>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="exportInvoicePDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200 shadow-sm" title="PDF Export">
                            <i class="fas fa-file-pdf mr-2 text-rose-600"></i>
                            PDF
                        </button>
                        <button type="button" onclick="exportInvoicePrint()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200 shadow-sm" title="Print List">
                            <i class="fas fa-print mr-2 text-gray-600"></i>
                            Print
                        </button>
                        <button type="button" onclick="exportInvoiceCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200 shadow-sm" title="CSV Export">
                            <i class="fas fa-file-csv mr-2 text-emerald-600"></i>
                            CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters Bar -->
            <div class="px-6 py-4 bg-white/50 border-b border-gray-100">
                <form method="get" action="<?= base_url('controlplane/invoices') ?>" class="flex flex-wrap items-end gap-4">
                    <!-- Status Filter -->
                    <div class="flex-1 min-w-[160px]">
                        <label class="flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1.5 ml-1">
                            <i class="fas fa-filter text-indigo-500/50"></i>
                            <span>Status</span>
                        </label>
                        <div class="relative">
                            <select name="status" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <?php $statusValue = $filters['status'] ?? ''; ?>
                                <option value="">All Statuses</option>
                                <?php foreach (['draft', 'sent', 'paid', 'overdue', 'void', 'cancelled'] as $statusOption): ?>
                                    <option value="<?= esc($statusOption) ?>" <?= $statusValue === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Clinic Filter -->
                    <div class="flex-1 min-w-[200px]">
                        <label class="flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1.5 ml-1">
                            <i class="fas fa-hospital text-indigo-500/50"></i>
                            <span>Clinic</span>
                        </label>
                        <div class="relative">
                            <select name="clinic_id" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="">All Clinics</option>
                                <?php $clinicValue = (int) ($filters['clinic_id'] ?? 0); ?>
                                <?php foreach ($clinics as $clinic): ?>
                                    <option value="<?= esc($clinic['id']) ?>" <?= $clinicValue === (int) $clinic['id'] ? 'selected' : '' ?>><?= esc($clinic['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="flex-[2] min-w-[300px]">
                        <label class="flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1.5 ml-1">
                            <i class="far fa-calendar-alt text-indigo-500/50"></i>
                            <span>Period</span>
                        </label>
                        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 p-1 rounded-xl shadow-inner focus-within:ring-4 focus-within:ring-indigo-500/10 focus-within:border-indigo-500 transition-all">
                            <div class="relative flex-1">
                                <input type="date" name="from" value="<?= esc($filters['from'] ?? '') ?>" class="w-full px-3 py-1.5 bg-transparent border-none text-sm focus:ring-0">
                            </div>
                            <span class="text-gray-300 font-bold text-xs uppercase">to</span>
                            <div class="relative flex-1">
                                <input type="date" name="to" value="<?= esc($filters['to'] ?? '') ?>" class="w-full px-3 py-1.5 bg-transparent border-none text-sm focus:ring-0">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all hover:scale-105 active:scale-95">
                            <i class="fas fa-search mr-2"></i> Filter
                        </button>
                        <a href="<?= base_url('controlplane/invoices') ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all active:scale-95">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="overflow-x-auto">
                <?php if (empty($invoices)): ?>
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100">
                            <i class="fas fa-file-invoice text-gray-300 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No results found</h3>
                        <p class="text-gray-500 text-sm max-w-xs mx-auto">We couldn't find any invoices matching your current filters.</p>
                    </div>
                <?php else: ?>
                    <table id="invoicesTable" class="w-full text-sm text-slate-600">
                        <thead class="bg-gray-50/50 backdrop-blur-sm">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hashtag text-indigo-500/50"></i>
                                        <span>Invoice #</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hospital text-indigo-500/50"></i>
                                        <span>Clinic</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-indigo-500/50"></i>
                                        <span>Issue Date</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-check text-indigo-500/50"></i>
                                        <span>Due Date</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign text-indigo-500/50"></i>
                                        <span>Amount</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-info-circle text-indigo-500/50"></i>
                                        <span>Status</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <span>Actions</span>
                                </th>
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
                                <tr class="hover:bg-indigo-50/30 transition-colors group/row">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600">
                                        <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" class="hover:underline flex items-center gap-2">
                                            <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 group-hover/row:bg-indigo-600 group-hover/row:text-white transition-colors">
                                                <i class="fas fa-file-invoice text-xs"></i>
                                            </div>
                                            <?= esc($invoice['invoice_number'] ?? '?') ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">
                                        <?= esc($invoice['clinic_name'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?= esc($invoice['issue_date'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?= esc($invoice['due_date'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 font-black text-gray-900">
                                        <?= esc($invoice['currency'] ?? 'USD') ?> <?= number_format($invoice['total_amount'] ?? 0, 2) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider <?= $badgeClass ?>">
                                            <?= ucfirst($statusKey) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" 
                                               class="group/action relative w-9 h-9 flex items-center justify-center text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-xl transition-all duration-300 hover:scale-110" 
                                               title="View Details">
                                                <i class="fas fa-eye text-sm transition-transform group-hover/action:scale-110"></i>
                                            </a>
                                            <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/email') ?>" method="post" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" 
                                                        class="group/action relative w-9 h-9 flex items-center justify-center text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-xl transition-all duration-300 hover:scale-110" 
                                                        title="Email Invoice">
                                                    <i class="fas fa-envelope text-sm transition-transform group-hover/action:scale-110"></i>
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
<style>
    #invoicesTable_wrapper .dt-buttons {
        display: none;
    }
</style>

<script>
    let invoicesTableInstance;

    function exportInvoicePDF() {
        if (invoicesTableInstance) {
            invoicesTableInstance.button('.btn-pdf').trigger();
        }
    }

    function exportInvoiceCSV() {
        if (invoicesTableInstance) {
            invoicesTableInstance.button('.btn-csv').trigger();
        }
    }

    function exportInvoicePrint() {
        if (invoicesTableInstance) {
            invoicesTableInstance.button('.btn-print').trigger();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tableEl = document.getElementById('invoicesTable');
        if (!tableEl || typeof DataTable === 'undefined') {
            return;
        }

        invoicesTableInstance = $(tableEl).DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdf',
                    className: 'btn-pdf',
                    title: 'Control Plane Invoices',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'csv',
                    className: 'btn-csv',
                    title: 'Control Plane Invoices'
                },
                {
                    extend: 'print',
                    className: 'btn-print',
                    title: 'Control Plane Invoices'
                }
            ],
            paging: false,
            searching: false,
            info: false,
            responsive: true,
            order: [[2, 'desc']],
            language: {
                zeroRecords: 'No invoices available for export.'
            }
        });
    });
</script>
<?= $this->endSection() ?>
