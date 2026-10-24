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
                            <i class="fas fa-file-invoice-dollar text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold text-gray-900"><?= esc(lang('V3Platform.invoices')) ?></h1>
                            <p class="text-gray-600"><?= esc(lang('V3Platform.manageBillingAndCollections')) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('controlplane/invoices/create') ?>" class="inline-flex items-center px-4 py-2 text-white text-sm font-semibold rounded-lg transition-all duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            <?= esc(lang('V3Platform.newInvoice')) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                <!-- Outstanding -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 cp-radius text-white transition-all duration-300">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.pending')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.outstanding')) ?></p>
                            <p class="cp-title font-black text-slate-900"><?= number_format($stats['outstanding'] ?? 0, 2) ?></p>
                            <p class="text-xs text-slate-500"><?= esc(lang('V3Platform.balanceToCollect')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 cp-radius text-white transition-all duration-300">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border border-emerald-100">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                                <span class="text-emerald-700 font-bold text-xs"><?= esc(lang('V3Platform.income')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.revenueMonth')) ?></p>
                            <p class="cp-title font-black text-emerald-900"><?= number_format($stats['revenue_month'] ?? 0, 2) ?></p>
                            <p class="text-xs text-emerald-600 font-medium"><?= esc(lang('V3Platform.collectedThisMonth')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Overdue -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col border-rose-200/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 cp-radius text-white transition-all duration-300">
                                <i class="fas fa-exclamation-triangle text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border border-red-200">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <span class="text-red-700 font-bold text-xs"><?= esc(lang('V3Platform.overdue')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.overdue')) ?></p>
                            <p class="cp-title font-black text-red-900"><?= (int) ($stats['overdue'] ?? 0) ?></p>
                            <p class="text-xs text-red-600 font-medium"><?= esc(lang('V3Platform.actionRequired')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Table & Filters Card -->
        <div class="cp-surface cp-radius border cp-border overflow-hidden">
            <!-- Table Header Bar -->
            <div class="px-6 py-4 border-b cp-border cp-surface">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                        <h3 class="text-lg font-bold text-gray-900 uppercase tracking-tight"><?= esc(lang('V3Platform.invoiceRegistry')) ?></h3>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="exportInvoicePDF()" class="inline-flex items-center px-3 py-2 cp-surface border cp-border rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200" title="<?= esc(lang('V3Platform.pdfExport'), 'attr') ?>">
                            <i class="fas fa-file-pdf mr-2 text-rose-600"></i>
                            PDF
                        </button>
                        <button type="button" onclick="exportInvoicePrint()" class="inline-flex items-center px-3 py-2 cp-surface border cp-border rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200" title="<?= esc(lang('V3Platform.printList'), 'attr') ?>">
                            <i class="fas fa-print mr-2 text-gray-600"></i>
                            <?= esc(lang('V3Platform.print')) ?>
                        </button>
                        <button type="button" onclick="exportInvoiceCSV()" class="inline-flex items-center px-3 py-2 cp-surface border cp-border rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200" title="<?= esc(lang('V3Platform.csvExport'), 'attr') ?>">
                            <i class="fas fa-file-csv mr-2 text-emerald-600"></i>
                            CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters Bar -->
            <div class="px-6 py-4 cp-surface border-b cp-border">
                <form method="get" action="<?= base_url('controlplane/invoices') ?>" class="flex flex-wrap items-end gap-4">
                    <!-- Status Filter -->
                    <div class="flex-1 min-w-[160px]">
                        <label class="flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1.5 ml-1">
                            <i class="fas fa-filter cp-accent"></i>
                            <span><?= esc(lang('V3Platform.status')) ?></span>
                        </label>
                        <div class="relative">
                            <select name="status" class="w-full px-4 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <?php $statusValue = $filters['status'] ?? ''; ?>
                                <option value=""><?= esc(lang('V3Platform.allStatuses2')) ?></option>
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
                            <i class="fas fa-hospital cp-accent"></i>
                            <span><?= esc(lang('V3Platform.clinic')) ?></span>
                        </label>
                        <div class="relative">
                            <select name="clinic_id" class="w-full px-4 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value=""><?= esc(lang('V3Platform.allClinics')) ?></option>
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
                            <i class="far fa-calendar-alt cp-accent"></i>
                            <span><?= esc(lang('V3Platform.period')) ?></span>
                        </label>
                        <div class="flex items-center gap-2 cp-surface border cp-border p-1 cp-radius focus-within:ring-4 focus-within:ring-indigo-500/10 focus-within:border-indigo-500 transition-all">
                            <div class="relative flex-1">
                                <input type="text" data-clinic-date="1" name="from" value="<?= esc($filters['from'] ?? '') ?>" class="w-full px-3 py-1.5 bg-transparent border-none text-sm focus:ring-0">
                            </div>
                            <span class="text-gray-300 font-bold text-xs uppercase"><?= esc(lang('V3Platform.to')) ?></span>
                            <div class="relative flex-1">
                                <input type="text" data-clinic-date="1" name="to" value="<?= esc($filters['to'] ?? '') ?>" class="w-full px-3 py-1.5 bg-transparent border-none text-sm focus:ring-0">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 cp-primary-surface text-white font-bold cp-radius hover:bg-indigo-700 transition-all active:scale-95">
                            <i class="fas fa-search mr-2"></i> <?= esc(lang('V3Platform.filter')) ?>
                        </button>
                        <a href="<?= base_url('controlplane/invoices') ?>" class="inline-flex items-center justify-center px-5 py-2.5 cp-surface border cp-border text-gray-600 font-bold cp-radius hover:bg-gray-50 transition-all active:scale-95">
                            <i class="fas fa-undo mr-2"></i> <?= esc(lang('V3Platform.reset')) ?>
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <?php if (empty($invoices)): ?>
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 cp-surface rounded-full flex items-center justify-center mx-auto mb-6 border cp-border">
                            <i class="fas fa-file-invoice text-gray-300 cp-title"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= esc(lang('V3Platform.noResultsFound')) ?></h3>
                        <p class="text-gray-500 text-sm max-w-xs mx-auto"><?= esc(lang('V3Platform.weCouldnTFindAnyInvoicesMatchingYourCurrentFilters')) ?></p>
                    </div>
                <?php else: ?>
                    <table id="invoicesTable" class="w-full text-sm text-slate-600">
                        <thead class="cp-surface">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hashtag cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.invoice')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hospital cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.clinic')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.issueDate')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-check cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.dueDate')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.amount')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-info-circle cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.status')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <span><?= esc(lang('V3Platform.actions')) ?></span>
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
                                    <td class="px-6 py-4 font-mono font-bold cp-accent">
                                        <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" class="hover:underline flex items-center gap-2">
                                            <div class="w-8 h-8 cp-soft-surface rounded-lg flex items-center justify-center cp-accent group-hover/row:bg-indigo-600 group-hover/row:text-white transition-colors">
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
                                               class="group/action relative w-9 h-9 flex items-center justify-center cp-accent hover:text-indigo-800 hover:bg-indigo-50 cp-radius transition-all duration-300"
                                               title="<?= esc(lang('V3Platform.viewDetails'), 'attr') ?>">
                                                <i class="fas fa-eye text-sm transition-transform group-hover/action:scale-110"></i>
                                            </a>
                                            <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/email') ?>" method="post" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                        class="group/action relative w-9 h-9 flex items-center justify-center cp-accent hover:text-blue-800 hover:bg-blue-50 cp-radius transition-all duration-300"
                                                        title="<?= esc(lang('V3Platform.emailInvoice'), 'attr') ?>">
                                                    <i class="fas fa-envelope text-sm transition-transform group-hover/action:scale-110"></i>
                                                </button>
                                            </form>
                                            <?php $canDelete = in_array($statusKey, ['draft', 'void', 'cancelled'], true); ?>
                                            <button type="button"
                                                    class="group/action relative w-9 h-9 flex items-center justify-center cp-radius transition-all duration-300 <?= $canDelete ? 'text-rose-600 hover:text-rose-800 hover:bg-rose-50' : 'text-gray-300 cursor-not-allowed' ?>"
                                                    title="<?= $canDelete ? 'Delete Invoice' : 'Cannot delete paid or sent invoice' ?>"
                                                    data-id="<?= esc($invoice['id']) ?>"
                                                    data-number="<?= esc($invoice['invoice_number'] ?? '') ?>"
                                                    data-clinic="<?= esc($invoice['clinic_name'] ?? '') ?>"
                                                    <?= $canDelete ? 'onclick="openInvoiceDeleteModal(this)"' : '' ?>>
                                                <i class="fas fa-trash text-sm transition-transform group-hover/action:scale-110"></i>
                                            </button>
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

<!-- Delete Invoice Modal -->
<div id="invoiceDeleteModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="cp-surface cp-radius w-full max-w-lg mx-4 border border-rose-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-rose-100">
            <div>
                <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold"><?= esc(lang('V3Platform.deleteInvoice')) ?></p>
                <h3 class="text-lg font-bold text-rose-900"><?= esc(lang('V3Platform.confirmDeletion')) ?></h3>
            </div>
            <button type="button" class="rounded-full p-2 text-rose-400 hover:text-rose-700 hover:bg-rose-50" onclick="closeInvoiceDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="bg-rose-50 border border-rose-100 cp-radius p-4 text-sm text-rose-700">
                <?= esc(lang('V3Platform.thisWillPermanentlyDeleteTheInvoiceThisActionCannotBeUndone')) ?>
            </div>
            <div class="cp-surface border cp-border cp-radius p-4">
                <p class="text-xs text-slate-500 uppercase tracking-widest"><?= esc(lang('V3Platform.invoice2')) ?></p>
                <p id="invoiceDeleteLabel" class="text-sm font-semibold text-slate-900 mt-1"></p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2"><?= esc(lang('V3Platform.typeDeleteToConfirm')) ?></label>
                <input type="text" id="invoiceDeleteConfirm" class="w-full px-4 py-3 border border-rose-200 cp-radius focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="DELETE" oninput="toggleInvoiceDeleteButton()">
            </div>
        </div>
        <div class="px-6 py-4 border-t cp-border flex items-center justify-end gap-3">
            <button type="button" class="px-4 py-2 cp-radius bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition" onclick="closeInvoiceDeleteModal()"><?= esc(lang('V3Platform.cancel')) ?></button>
            <form id="invoiceDeleteForm" method="post" action="">
                <?= csrf_field() ?>
                <button type="submit" id="invoiceDeleteSubmit" class="px-4 py-2 cp-radius bg-rose-600 text-white text-sm font-semibold opacity-50 cursor-not-allowed" disabled><?= esc(lang('V3Platform.deleteInvoice')) ?></button>
            </form>
        </div>
    </div>
</div>

<script>
    function openInvoiceDeleteModal(button) {
        const modal = document.getElementById('invoiceDeleteModal');
        const label = document.getElementById('invoiceDeleteLabel');
        const form = document.getElementById('invoiceDeleteForm');
        const input = document.getElementById('invoiceDeleteConfirm');
        if (!modal || !label || !form || !input) return;
        const id = button?.dataset?.id;
        const number = button?.dataset?.number;
        const clinicName = button?.dataset?.clinic;
        label.textContent = `#${number || id} ? ${clinicName || 'Clinic'}`;
        form.action = `<?= base_url('controlplane/invoices') ?>/${id}/delete`;
        input.value = '';
        toggleInvoiceDeleteButton();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => input.focus(), 50);
    }

    function closeInvoiceDeleteModal() {
        const modal = document.getElementById('invoiceDeleteModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleInvoiceDeleteButton() {
        const input = document.getElementById('invoiceDeleteConfirm');
        const button = document.getElementById('invoiceDeleteSubmit');
        if (!input || !button) return;
        if (input.value.trim().toUpperCase() === 'DELETE') {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    document.getElementById('invoiceDeleteModal')?.addEventListener('click', function (event) {
        if (event.target === this) {
            closeInvoiceDeleteModal();
        }
    });
</script>
<?= $this->endSection() ?>
