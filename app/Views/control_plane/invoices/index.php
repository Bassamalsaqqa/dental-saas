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
                        <div class="w-12 h-12 cp-radius flex items-center justify-center v3-text-on-primary">
                            <i class="fas fa-file-invoice-dollar text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold v3-text-ink"><?= esc(lang('V3Platform.invoices')) ?></h1>
                            <p class="v3-text-muted"><?= esc(lang('V3Platform.manageBillingAndCollections')) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('controlplane/invoices/create') ?>" class="inline-flex items-center px-4 py-2 v3-text-on-primary text-sm font-semibold rounded-lg transition-all duration-200 v3-btn v3-btn--secondary">
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
                            <div class="relative p-3 cp-radius v3-text-on-primary transition-all duration-300">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.pending')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.outstanding')) ?></p>
                            <p class="cp-title font-black v3-text-ink"><?= number_format($stats['outstanding'] ?? 0, 2) ?></p>
                            <p class="text-xs v3-text-muted"><?= esc(lang('V3Platform.balanceToCollect')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 cp-radius v3-text-on-primary transition-all duration-300">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border v3-border-default">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <i class="fas fa-arrow-up v3-text-primary text-xs"></i>
                                <span class="v3-text-primary font-bold text-xs"><?= esc(lang('V3Platform.income')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.revenueMonth')) ?></p>
                            <p class="cp-title font-black v3-text-primary"><?= number_format($stats['revenue_month'] ?? 0, 2) ?></p>
                            <p class="text-xs v3-text-primary font-medium"><?= esc(lang('V3Platform.collectedThisMonth')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Overdue -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col v3-border-default">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 cp-radius v3-text-on-primary transition-all duration-300">
                                <i class="fas fa-exclamation-triangle text-xl"></i>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border v3-border-default">
                                <div class="w-2 h-2 v3-bg-danger rounded-full"></div>
                                <span class="v3-text-danger font-bold text-xs"><?= esc(lang('V3Platform.overdue')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.overdue')) ?></p>
                            <p class="cp-title font-black v3-text-danger"><?= (int) ($stats['overdue'] ?? 0) ?></p>
                            <p class="text-xs v3-text-danger font-medium"><?= esc(lang('V3Platform.actionRequired')) ?></p>
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
                        <h3 class="text-lg font-bold v3-text-ink uppercase tracking-tight"><?= esc(lang('V3Platform.invoiceRegistry')) ?></h3>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="exportInvoicePDF()" class="inline-flex items-center px-3 py-2 cp-surface border cp-border rounded-lg text-sm font-medium v3-text-ink v3-hover-soft transition-colors duration-200 v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.pdfExport'), 'attr') ?>">
                            <i class="fas fa-file-pdf mr-2 v3-text-danger"></i>
                            PDF
                        </button>
                        <button type="button" onclick="exportInvoicePrint()" class="inline-flex items-center px-3 py-2 cp-surface border cp-border rounded-lg text-sm font-medium v3-text-ink v3-hover-soft transition-colors duration-200 v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.printList'), 'attr') ?>">
                            <i class="fas fa-print mr-2 v3-text-muted"></i>
                            <?= esc(lang('V3Platform.print')) ?>
                        </button>
                        <button type="button" onclick="exportInvoiceCSV()" class="inline-flex items-center px-3 py-2 cp-surface border cp-border rounded-lg text-sm font-medium v3-text-ink v3-hover-soft transition-colors duration-200 v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.csvExport'), 'attr') ?>">
                            <i class="fas fa-file-csv mr-2 v3-text-primary"></i>
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
                        <label class="flex items-center gap-2 text-xs font-black v3-text-muted uppercase tracking-[0.2em] mb-1.5 ml-1">
                            <i class="fas fa-filter cp-accent"></i>
                            <span><?= esc(lang('V3Platform.status')) ?></span>
                        </label>
                        <div class="relative">
                            <select name="status" class="w-full px-4 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-4 v3-focus-control v3-focus-control transition-all appearance-none cursor-pointer v3-select">
                                <?php $statusValue = $filters['status'] ?? ''; ?>
                                <option value=""><?= esc(lang('V3Platform.allStatuses2')) ?></option>
                                <?php foreach (['draft', 'sent', 'paid', 'overdue', 'void', 'cancelled'] as $statusOption): ?>
                                    <option value="<?= esc($statusOption) ?>" <?= $statusValue === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 /2 v3-text-muted pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Clinic Filter -->
                    <div class="flex-1 min-w-[200px]">
                        <label class="flex items-center gap-2 text-xs font-black v3-text-muted uppercase tracking-[0.2em] mb-1.5 ml-1">
                            <i class="fas fa-hospital cp-accent"></i>
                            <span><?= esc(lang('V3Platform.clinic')) ?></span>
                        </label>
                        <div class="relative">
                            <select name="clinic_id" class="w-full px-4 py-2 cp-surface border cp-border cp-radius text-sm focus:ring-4 v3-focus-control v3-focus-control transition-all appearance-none cursor-pointer v3-select">
                                <option value=""><?= esc(lang('V3Platform.allClinics')) ?></option>
                                <?php $clinicValue = (int) ($filters['clinic_id'] ?? 0); ?>
                                <?php foreach ($clinics as $clinic): ?>
                                    <option value="<?= esc($clinic['id']) ?>" <?= $clinicValue === (int) $clinic['id'] ? 'selected' : '' ?>><?= esc($clinic['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 /2 v3-text-muted pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="flex-[2] min-w-[300px]">
                        <label class="flex items-center gap-2 text-xs font-black v3-text-muted uppercase tracking-[0.2em] mb-1.5 ml-1">
                            <i class="far fa-calendar-alt cp-accent"></i>
                            <span><?= esc(lang('V3Platform.period')) ?></span>
                        </label>
                        <div class="flex items-center gap-2 cp-surface border cp-border p-1 cp-radius focus-within:ring-4 focus-within:v3-border-default focus-within:v3-border-default transition-all">
                            <div class="relative flex-1">
                                <input type="text" data-clinic-date="1" name="from" value="<?= esc($filters['from'] ?? '') ?>" class="w-full px-3 py-1.5 bg-transparent border-none text-sm focus:ring-0 v3-input">
                            </div>
                            <span class="v3-text-muted font-bold text-xs uppercase"><?= esc(lang('V3Platform.to')) ?></span>
                            <div class="relative flex-1">
                                <input type="text" data-clinic-date="1" name="to" value="<?= esc($filters['to'] ?? '') ?>" class="w-full px-3 py-1.5 bg-transparent border-none text-sm focus:ring-0 v3-input">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 cp-primary-surface v3-text-on-primary font-bold cp-radius v3-hover-primary transition-all active:scale-95 v3-btn v3-btn--primary">
                            <i class="fas fa-search mr-2"></i> <?= esc(lang('V3Platform.filter')) ?>
                        </button>
                        <a href="<?= base_url('controlplane/invoices') ?>" class="inline-flex items-center justify-center px-5 py-2.5 cp-surface border cp-border v3-text-muted font-bold cp-radius v3-hover-soft transition-all active:scale-95 v3-btn v3-btn--secondary">
                            <i class="fas fa-undo mr-2"></i> <?= esc(lang('V3Platform.reset')) ?>
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <?php if (empty($invoices)): ?>
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 cp-surface rounded-full flex items-center justify-center mx-auto mb-6 border cp-border">
                            <i class="fas fa-file-invoice v3-text-muted cp-title"></i>
                        </div>
                        <h3 class="text-xl font-bold v3-text-ink mb-2"><?= esc(lang('V3Platform.noResultsFound')) ?></h3>
                        <p class="v3-text-muted text-sm max-w-xs mx-auto"><?= esc(lang('V3Platform.weCouldnTFindAnyInvoicesMatchingYourCurrentFilters')) ?></p>
                    </div>
                <?php else: ?>
                    <table id="invoicesTable" class="w-full text-sm v3-text-muted">
                        <thead class="cp-surface">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold v3-text-muted uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hashtag cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.invoice')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold v3-text-muted uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hospital cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.clinic')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold v3-text-muted uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.issueDate')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold v3-text-muted uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-check cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.dueDate')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold v3-text-muted uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.amount')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold v3-text-muted uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-info-circle cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.status')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold v3-text-muted uppercase tracking-wider">
                                    <span><?= esc(lang('V3Platform.actions')) ?></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y v3-border-default bg-transparent">
                            <?php foreach ($invoices as $invoice): ?>
                                <?php
                                    $statusClasses = [
                                        'draft' => 'v3-bg-soft v3-text-muted v3-border-default',
                                        'sent' => 'v3-bg-chair v3-text-chair v3-border-default',
                                        'paid' => 'v3-bg-paid v3-text-primary v3-border-default',
                                        'overdue' => 'v3-bg-alert v3-text-danger v3-border-default',
                                        'void' => 'v3-bg-soft v3-text-muted v3-border-default',
                                        'cancelled' => 'v3-bg-alert v3-text-danger v3-border-default',
                                    ];
                                    $statusKey = $invoice['status'] ?? 'draft';
                                    $badgeClass = $statusClasses[$statusKey] ?? 'v3-bg-soft v3-text-muted';
                                ?>
                                <tr class="v3-hover-primary transition-colors group/row">
                                    <td class="px-6 py-4 font-mono font-bold cp-accent">
                                        <a href="<?= base_url('controlplane/invoices/' . $invoice['id']) ?>" class="hover:underline flex items-center gap-2">
                                            <div class="w-8 h-8 cp-soft-surface rounded-lg flex items-center justify-center cp-accent group-hover/row:v3-bg-primary group-hover/row:v3-text-on-primary transition-colors">
                                                <i class="fas fa-file-invoice text-xs"></i>
                                            </div>
                                            <?= esc($invoice['invoice_number'] ?? '?') ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 font-bold v3-text-ink">
                                        <?= esc($invoice['clinic_name'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 v3-text-muted">
                                        <?= esc($invoice['issue_date'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 v3-text-muted">
                                        <?= esc($invoice['due_date'] ?? '--') ?>
                                    </td>
                                    <td class="px-6 py-4 font-black v3-text-ink">
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
                                               class="group/action relative w-9 h-9 flex items-center justify-center cp-accent v3-hover-text-chair v3-hover-primary cp-radius transition-all duration-300"
                                               title="<?= esc(lang('V3Platform.viewDetails'), 'attr') ?>">
                                                <i class="fas fa-eye text-sm transition-transform group-hover/action:scale-110"></i>
                                            </a>
                                            <form action="<?= base_url('controlplane/invoices/' . $invoice['id'] . '/email') ?>" method="post" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                        class="group/action relative w-9 h-9 flex items-center justify-center cp-accent v3-hover-text-primary v3-hover-primary cp-radius transition-all duration-300 v3-btn v3-btn--primary"
                                                        title="<?= esc(lang('V3Platform.emailInvoice'), 'attr') ?>">
                                                    <i class="fas fa-envelope text-sm transition-transform group-hover/action:scale-110"></i>
                                                </button>
                                            </form>
                                            <?php $canDelete = in_array($statusKey, ['draft', 'void', 'cancelled'], true); ?>
                                            <button type="button"
                                                    class="group/action relative w-9 h-9 flex items-center justify-center cp-radius transition-all duration-300 <?= $canDelete ? 'v3-text-danger v3-hover-text-danger v3-hover-danger' : 'v3-text-muted cursor-not-allowed' ?> v3-btn v3-btn--secondary"
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
<div id="invoiceDeleteModal" class="fixed inset-0 v3-backdrop hidden items-center justify-center z-50">
    <div class="cp-surface cp-radius w-full max-w-lg mx-4 border v3-border-default">
        <div class="flex items-center justify-between px-6 py-4 border-b v3-border-default">
            <div>
                <p class="text-xs uppercase tracking-widest v3-text-danger font-semibold"><?= esc(lang('V3Platform.deleteInvoice')) ?></p>
                <h3 class="text-lg font-bold v3-text-danger"><?= esc(lang('V3Platform.confirmDeletion')) ?></h3>
            </div>
            <button type="button" class="rounded-full p-2 v3-text-danger v3-hover-text-danger v3-hover-danger v3-btn v3-btn--danger" onclick="closeInvoiceDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="v3-bg-alert border v3-border-default cp-radius p-4 text-sm v3-text-danger">
                <?= esc(lang('V3Platform.thisWillPermanentlyDeleteTheInvoiceThisActionCannotBeUndone')) ?>
            </div>
            <div class="cp-surface border cp-border cp-radius p-4">
                <p class="text-xs v3-text-muted uppercase tracking-widest"><?= esc(lang('V3Platform.invoice2')) ?></p>
                <p id="invoiceDeleteLabel" class="text-sm font-semibold v3-text-ink mt-1"></p>
            </div>
            <div>
                <label class="block text-xs font-semibold v3-text-muted uppercase tracking-widest mb-2"><?= esc(lang('V3Platform.typeDeleteToConfirm')) ?></label>
                <input type="text" id="invoiceDeleteConfirm" class="w-full px-4 py-3 border v3-border-default cp-radius focus:ring-2 v3-focus-control v3-focus-control v3-input" placeholder="DELETE" oninput="toggleInvoiceDeleteButton()">
            </div>
        </div>
        <div class="px-6 py-4 border-t cp-border flex items-center justify-end gap-3">
            <button type="button" class="px-4 py-2 cp-radius v3-bg-soft v3-text-muted text-sm font-semibold v3-hover-soft transition v3-btn v3-btn--secondary" onclick="closeInvoiceDeleteModal()"><?= esc(lang('V3Platform.cancel')) ?></button>
            <form id="invoiceDeleteForm" method="post" action="">
                <?= csrf_field() ?>
                <button type="submit" id="invoiceDeleteSubmit" class="px-4 py-2 cp-radius v3-bg-danger v3-text-on-primary text-sm font-semibold opacity-50 cursor-not-allowed v3-btn v3-btn--danger" disabled><?= esc(lang('V3Platform.deleteInvoice')) ?></button>
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
