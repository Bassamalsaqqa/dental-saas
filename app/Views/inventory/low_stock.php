<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- jsPDF Library for PDF Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<!-- Custom DataTables Styling -->
<style>
/* DataTables Controls Styling - More specific selectors */
.dataTables_wrapper {
    position: relative !important;
}

.dataTables_length {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    order: 1 !important;
}

.dataTables_length label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-right: 12px !important;
}

.dataTables_length select {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_length select:focus {
    border-color: var(--denta-emerald-pine) !important;
    box-shadow:none;
}

.dataTables_length select:hover {
    border-color: #9ca3af !important;
}

.dataTables_filter {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-left: auto !important;
    order: 2 !important;
}

.dataTables_filter label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-right: 12px !important;
}

.dataTables_filter input {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_filter input:focus {
    border-color: var(--denta-emerald-pine) !important;
    box-shadow:none;
}

.dataTables_filter input:hover {
    border-color: #9ca3af !important;
}

.dataTables_info {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
    display: flex !important;
    align-items: center !important;
}

.dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.dataTables_paginate .paginate_button {
    padding: 8px 12px !important;
    margin: 0 2px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #f9fafb !important;
    border-color: #9ca3af !important;
}

.dataTables_paginate .paginate_button.current {
    background-color: var(--denta-emerald-pine) !important;
    border-color: var(--denta-emerald-pine) !important;
    color: white !important;
}

.dataTables_paginate .paginate_button.disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
}

/* Ensure proper ordering */
.dataTables_wrapper .dataTables_length {
    display: flex !important;
    align-items: center !important;
    order: 1 !important;
}

.dataTables_wrapper .dataTables_filter {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-left: auto !important;
    order: 2 !important;
}

.dataTables_wrapper .dataTables_info {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    order: 1 !important;
}

.dataTables_wrapper .dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-left: auto !important;
    order: 2 !important;
}

/* Ensure the top container uses flexbox */
.dataTables_wrapper > .row:first-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

/* Ensure the bottom container uses flexbox */
.dataTables_wrapper > .row:last-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

/* Additional styling to ensure proper control positioning */
.dataTables_wrapper .dataTables_length {
    margin-right: auto !important;
}

.dataTables_wrapper .dataTables_filter {
    margin-left: auto !important;
    margin-right: 0 !important;
}

/* Ensure loading indicators can be properly hidden */
.dataTables_processing {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    background: rgba(255, 255, 255, 0.9) !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    padding: 10px 20px !important;
    z-index: 1000 !important;
}

.loading-indicator,
.spinner,
.loading-dots {
    display: none;
}

.loading-indicator.show,
.spinner.show,
.loading-dots.show {
    display: block;
}
</style>

<?php
$lowStockItems = is_array($items ?? null) ? $items : [];
$criticalCount = 0;

foreach ($lowStockItems as $item) {
    if ((float) ($item['quantity'] ?? 0) <= 0) {
        $criticalCount++;
    }
}

$lowCount = max(0, count($lowStockItems) - $criticalCount);
$openReorderCount = (int) ($open_reorder_count ?? 0);
?>

<div class="v3-operational-view w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <section class="overflow-hidden v3-radius border v3-border v3-bg-forest v3-text-on-primary">
        <div class="relative isolate">
            <div class="absolute inset-0 v3-bg-panel"></div>

            <div class="relative grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end lg:p-7">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full v3-surface px-3 py-1 text-xs font-extrabold uppercase tracking-wide v3-text-warning border v3-border-default">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span><?= esc(lang('Inventory.lowStock')) ?></span>
                    </div>
                    <h1 class="mt-4 text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl"><?= esc(lang('Inventory.lowStockItems')) ?></h1>
                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 v3-text-warning sm:text-base"><?= esc(lang('Inventory.lowStockSubtitle')) ?></p>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    <a href="<?= base_url('inventory/movements') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-extrabold v3-text-on-primary border v3-border-default transition v3-hover-soft v3-btn v3-btn--secondary">
                        <i class="fas fa-arrows-rotate"></i>
                        <span><?= esc(lang('Inventory.movements')) ?></span>
                    </a>
                    <a href="<?= base_url('inventory/reorder-list') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-extrabold v3-text-warning transition v3-btn v3-btn--secondary">
                        <i class="fas fa-cart-shopping"></i>
                        <span><?= esc(lang('Inventory.reorderList')) ?></span>
                        <span class="inline-flex min-w-6 items-center justify-center rounded-full v3-bg-warning px-2 py-0.5 text-xs font-black v3-text-on-primary"><?= $openReorderCount ?></span>
                    </a>
                    <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-backdrop px-4 py-2.5 text-sm font-extrabold v3-text-on-primary border v3-border-default transition v3-backdrop v3-btn v3-btn--secondary">
                        <i class="fas fa-arrow-left rtl-back-icon"></i>
                        <span><?= esc(lang('Inventory.backToInventory')) ?></span>
                    </a>
                </div>
            </div>

            <div class="relative grid gap-3 border-t v3-border v3-backdrop p-4 sm:grid-cols-2 lg:grid-cols-4 lg:p-5">
                <div class="v3-radius v3-surface p-4 border v3-border-default">
                    <p class="text-xs font-black uppercase tracking-wide v3-text-warning"><?= esc(lang('Inventory.lowStock')) ?></p>
                    <p class="mt-2 v3-title-scale font-black v3-text-on-primary"><?= count($lowStockItems) ?></p>
                </div>
                <div class="v3-radius v3-bg-danger p-4 border v3-border-default">
                    <p class="text-xs font-black uppercase tracking-wide v3-text-danger"><?= esc(lang('Inventory.critical')) ?> <?= esc(lang('Inventory.items')) ?></p>
                    <p class="mt-2 v3-title-scale font-black v3-text-on-primary"><?= $criticalCount ?></p>
                </div>
                <div class="v3-radius v3-bg-warning p-4 border v3-border-default">
                    <p class="text-xs font-black uppercase tracking-wide v3-text-warning"><?= esc(lang('Inventory.low')) ?> <?= esc(lang('Inventory.items')) ?></p>
                    <p class="mt-2 v3-title-scale font-black v3-text-on-primary"><?= $lowCount ?></p>
                </div>
                <div class="v3-radius v3-bg-primary p-4 border v3-border-default">
                    <p class="text-xs font-black uppercase tracking-wide v3-text-primary"><?= esc(lang('Inventory.reorderList')) ?></p>
                    <p class="mt-2 v3-title-scale font-black v3-text-on-primary"><?= $openReorderCount ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden v3-radius border v3-border v3-surface">
        <div class="flex flex-col gap-4 border-b v3-border px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center v3-radius v3-bg-warning v3-text-on-primary">
                    <i class="fas fa-boxes-stacked"></i>
                </span>
                <div>
                    <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('Inventory.lowStockItems')) ?></h2>
                    <p class="text-sm font-bold v3-text-muted"><span class="total-items-count">0</span> <?= esc(lang('Inventory.items')) ?></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="exportLowStockToPDF()" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-3.5 py-2 text-sm font-extrabold v3-text-ink border v3-border-default transition v3-hover-danger v3-hover-text-danger v3-btn v3-btn--secondary">
                    <i class="fas fa-file-pdf v3-text-danger"></i>
                    <span><?= esc(lang('Inventory.exportToPDF')) ?></span>
                </button>
                <button type="button" onclick="printLowStockTable()" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-3.5 py-2 text-sm font-extrabold v3-text-ink border v3-border-default transition v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-print v3-text-muted"></i>
                    <span><?= esc(lang('Inventory.exportToPrint')) ?></span>
                </button>
                <button type="button" onclick="exportLowStockToCSV()" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-3.5 py-2 text-sm font-extrabold v3-text-ink border v3-border-default transition v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-file-csv v3-text-primary"></i>
                    <span><?= esc(lang('Inventory.exportToCSV')) ?></span>
                </button>
            </div>
        </div>

        <div class="table-container overflow-x-auto">
            <div class="dataTables-wrapper">
                <table id="lowStockTable" class="w-full min-w-full">
                    <thead class="v3-surface">
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-hashtag v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.id')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-box v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.itemName')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tags v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.category')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-layer-group v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.quantity')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-triangle-exclamation v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.minimumQuantityRequired')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-shekel-sign v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.unitPrice')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-truck v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.supplier')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.expiryDate')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-sliders v3-text-warning"></i>
                                    <span><?= esc(lang('Inventory.actions')) ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y v3-border-default v3-surface">
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="grid gap-3 md:grid-cols-2">
        <a href="<?= base_url('inventory/create') ?>" class="group flex items-center justify-between gap-4 v3-radius p-4 v3-text-on-primary transition">
            <span class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center v3-radius v3-surface border v3-border-default"><i class="fas fa-plus"></i></span>
                <span class="text-sm font-black"><?= esc(lang('Inventory.addNewItem')) ?></span>
            </span>
            <i class="fas fa-arrow-right rtl-back-icon opacity-80 transition group-hover:translate-x-0.5"></i>
        </a>

        <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="group flex items-center justify-between gap-4 v3-radius p-4 v3-text-on-primary transition">
            <span class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center v3-radius v3-surface border v3-border-default"><i class="fas fa-list"></i></span>
                <span class="text-sm font-black"><?= esc(lang('Inventory.inventoryItems')) ?></span>
            </span>
            <i class="fas fa-arrow-right rtl-back-icon opacity-80 transition group-hover:translate-x-0.5"></i>
        </a>
    </div>
</div>

<div id="reorderQtyModal" class="fixed inset-0 z-[2147483500] hidden">
    <div class="absolute inset-0 v3-backdrop" onclick="closeReorderQtyModal()"></div>
    <div class="relative z-10 flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden v3-radius border v3-border v3-surface">
            <div class="flex items-center justify-between gap-4 border-b v3-border px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center v3-radius v3-bg-warning v3-text-on-primary">
                        <i class="fas fa-cart-plus"></i>
                    </span>
                    <h4 class="text-base font-black v3-text-ink"><?= esc(lang('Inventory.reorderQtyPromptTitle')) ?></h4>
                </div>
                <button type="button" class="flex h-9 w-9 items-center justify-center v3-radius v3-surface v3-text-muted border v3-border-default transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--secondary" onclick="closeReorderQtyModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="reorderQtyForm" class="space-y-4 p-5">
                <input type="hidden" id="reorderInventoryId">
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc(lang('Inventory.reorderQtyLabel')) ?></label>
                    <input type="number" min="1" step="1" id="reorderQtyInput" class="w-full v3-radius border v3-border px-4 py-3 text-sm font-bold v3-text-ink v3-focus-control focus:ring-4 v3-focus-control v3-input" required>
                    <p class="mt-2 text-xs font-semibold v3-text-muted"><?= esc(lang('Inventory.reorderQtyHelp')) ?></p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeReorderQtyModal()" class="v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-extrabold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary"><?= esc(lang('Inventory.cancel')) ?></button>
                    <button type="submit" class="v3-radius px-4 py-2.5 text-sm font-extrabold v3-text-on-primary transition v3-btn v3-btn--primary"><?= esc(lang('Inventory.saveToReorderList')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let reorderQtyTargetId = null;

function openReorderQtyModal(id) {
    reorderQtyTargetId = id;
    document.getElementById('reorderInventoryId').value = String(id);
    document.getElementById('reorderQtyInput').value = '1';
    document.getElementById('reorderQtyModal').classList.remove('hidden');
    document.getElementById('reorderQtyInput').focus();
}

function closeReorderQtyModal() {
    document.getElementById('reorderQtyModal').classList.add('hidden');
    document.getElementById('reorderQtyForm').reset();
    reorderQtyTargetId = null;
}

function addToReorderList(id, qty) {
    if (typeof qty === 'undefined') {
        openReorderQtyModal(id);
        return;
    }
    if (!Number.isFinite(qty) || qty <= 0) {
        showToast('error', 'Error!', '<?= esc(lang('Inventory.enterValidQuantity'), 'js') ?>');
        return;
    }

    const formData = new FormData();
    formData.append('inventory_id', String(id));
    formData.append('requested_qty', String(qty));
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url('inventory/reorder-list/add') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.success) {
            closeReorderQtyModal();
            showToast('success', '<?= esc(lang('Inventory.reorderList'), 'js') ?>', data.message || '<?= esc(lang('Inventory.addedToReorderList'), 'js') ?>');
        } else {
            showToast('error', 'Error!', (data && data.message) ? data.message : '<?= esc(lang('Inventory.failedToAddToReorderList'), 'js') ?>');
        }
    })
    .catch(() => showToast('error', 'Error!', '<?= esc(lang('Inventory.failedToAddToReorderList'), 'js') ?>'));
}

document.getElementById('reorderQtyForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const qty = parseInt(document.getElementById('reorderQtyInput').value, 10);
    const id = reorderQtyTargetId || parseInt(document.getElementById('reorderInventoryId').value, 10);
    addToReorderList(id, qty);
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeReorderQtyModal();
    }
});

function adjustStock(id) {
    const adjustment = prompt('Enter adjustment amount (positive to add, negative to subtract):');
    if (adjustment !== null && !isNaN(adjustment)) {
        const adjustmentValue = parseInt(adjustment);
        const type = adjustmentValue >= 0 ? 'add' : 'subtract';
        const reason = prompt('Enter reason for adjustment (optional):') || 'Manual adjustment';

        fetch(`<?= base_url('inventory') ?>/${id}/adjust`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify({
                adjustment: Math.abs(adjustmentValue),
                type: type,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Success!', 'Stock adjusted successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', 'Error!', data.message || 'Failed to adjust stock.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error!', 'An error occurred while adjusting the stock.');
        });
    }
}
</script>

<style>
@media print {
    body {
        margin: 0;
        padding: 0;
        background: white !important;
    }

    .print\\:hidden {
        display: none !important;
    }

    .print\\:block {
        display: block !important;
    }

    .print\\:v3-bg-panel {
        background: white !important;
    }

    .print\\:v3-text-ink {
        color: black !important;
    }

    .print\\:border-0 {
        border: 0 !important;
    }

    .print\\:rounded-none {
        border-radius: 0 !important;
    }

    .print\\:shadow-none {
        box-shadow:none;
    }

    .print\\:p-0 {
        padding: 0 !important;
    }

    .print\\:max-w-none {
        max-width: none !important;
    }

    .print\\:border {
        border: 1px solid #d1d5db !important;
    }

    .print\\:border-t {
        border-top: 1px solid #d1d5db !important;
    }

    .print\\:v3-bg-panel {
        background: white !important;
    }

    .print\\:v3-text-muted {
        color: #4b5563 !important;
    }

    .print\\:v3-bg-soft {
        background: #f3f4f6 !important;
    }

    .print\\:v3-border-default {
        border-color: #d1d5db !important;
    }

    /* Hide navigation and other non-printable elements */
    nav, header, footer, .no-print {
        display: none !important;
    }
}
</style>

<style>
/* Ensure loading indicators can be properly hidden */
.dataTables_processing {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    background: rgba(255, 255, 255, 0.9) !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    padding: 10px 20px !important;
    z-index: 1000 !important;
}

.loading-indicator,
.spinner,
.loading-dots {
    display: none;
}

.loading-indicator.show,
.spinner.show,
.loading-dots.show {
    display: block;
}
</style>

<script>
// Global variables
let lowStockTable;

// Initialize DataTable with server-side processing
$(document).ready(function() {
    // Check if DataTables is loaded
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables is not loaded. Please check if the DataTables library is included.');
        return;
    }

    // CSRF token setup
    const csrf_name = '<?= csrf_token() ?>';
    const csrf_hash = '<?= csrf_hash() ?>';

    // Custom loading indicator management
    function hideLoadingIndicators() {
        $('.dataTables_processing').hide();
        $('.loading-indicator').hide();
        $('.spinner').hide();
        $('.loading-dots').hide();
        $('#lowStockTable').closest('.table-container').find('.loading').hide();
    }

    function showLoadingIndicators() {
        $('.dataTables_processing').show();
    }

    console.log('Initializing DataTable for #lowStockTable');
    console.log('Table element:', $('#lowStockTable'));
    console.log('Table element length:', $('#lowStockTable').length);

    lowStockTable = $('#lowStockTable').DataTable({
        processing: true,
        serverSide: true,
        processingIndicator: true,
        ajax: {
            url: '<?= base_url('inventory/getLowStockData') ?>',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function(d) {
                console.log('Sending Low Stock DataTables request:', d);
                console.log('CSRF name:', csrf_name);
                console.log('CSRF hash:', csrf_hash);
                d[csrf_name] = csrf_hash;
                console.log('Final request data:', d);
                return d;
            },
            dataSrc: function(json) {
                console.log('Received Low Stock DataTables response:', json);
                console.log('Response type:', typeof json);
                console.log('Response keys:', Object.keys(json));

                if (json.error) {
                    console.error('Server error:', json.error);
                    alert('<?= esc(lang('Inventory.error'), 'js') ?>: ' + json.error);
                    return [];
                }

                if (!json.data) {
                    console.error('No data property in response');
                    console.log('Full response:', json);
                    return [];
                }

                console.log('Data array length:', json.data.length);
                console.log('Sample data item:', json.data[0]);

                // Handle empty data case
                if (json.data.length === 0) {
                    console.log('No low stock items found');
                    if (json.message) {
                        console.log('Server message:', json.message);
                    }
                }

                // Hide loading indicators after data is received
                setTimeout(function() {
                    hideLoadingIndicators();
                }, 100);

                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax error:', {
                    xhr: xhr,
                    error: error,
                    thrown: thrown,
                    responseText: xhr.responseText,
                    status: xhr.status,
                    statusText: xhr.statusText
                });

                let errorMessage = 'Error loading data';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || error || thrown;
                }

                console.error('DataTables Error: ' + errorMessage);

                // Show a user-friendly message in the table
                const tbody = document.querySelector('#lowStockTable tbody');
                if (tbody) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.setAttribute('colspan', '9');
                    td.className = 'text-center py-8 v3-text-muted';

                    const flexDiv = document.createElement('div');
                    flexDiv.className = 'flex flex-col items-center space-y-2';

                    const icon = document.createElement('i');
                    icon.className = 'fas fa-exclamation-triangle text-4xl v3-text-warning';

                    const p1 = document.createElement('p');
                    p1.className = 'text-lg font-medium';
                    p1.textContent = '<?= esc(lang('Inventory.unableToLoadData'), 'js') ?>';

                    const p2 = document.createElement('p');
                    p2.className = 'text-sm';
                    p2.textContent = '<?= esc(lang('Inventory.checkConnection'), 'js') ?>';

                    const retryBtn = document.createElement('button');
                    retryBtn.className = 'mt-4 px-4 py-2 v3-bg-primary v3-text-on-primary rounded-md v3-hover-primary';
                    retryBtn.onclick = function() { location.reload(); };

                    const retryIcon = document.createElement('i');
                    retryIcon.className = 'fas fa-refresh mr-2';
                    retryBtn.appendChild(retryIcon);
                    retryBtn.appendChild(document.createTextNode('Retry'));

                    flexDiv.appendChild(icon);
                    flexDiv.appendChild(p1);
                    flexDiv.appendChild(p2);
                    flexDiv.appendChild(retryBtn);
                    td.appendChild(flexDiv);
                    tr.appendChild(td);

                    tbody.replaceChildren(tr);
                }

                // Hide loading indicators on error
                hideLoadingIndicators();
            }
        },
        columns: [
            { data: 0, visible: false }, // ID (hidden)
            { data: 1 }, // Item Name
            { data: 2 }, // Category
            { data: 3 }, // Current Stock
            { data: 4 }, // Minimum Required
            { data: 5 }, // Unit Price
            { data: 6 }, // Supplier
            { data: 7 }, // Expiry Date
            { data: 8, orderable: false } // Actions
        ],
        order: [[1, 'asc']], // Sort by item name by default
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: "<?= lang('Inventory.processing') ?>",
            emptyTable: "<?= lang('Inventory.emptyTable') ?>",
            zeroRecords: "<?= lang('Inventory.zeroRecords') ?>",
            lengthMenu: "<?= lang('Inventory.lengthMenu') ?>",
            info: "<?= lang('Inventory.info') ?>",
            infoEmpty: "<?= lang('Inventory.infoEmpty') ?>",
            infoFiltered: "<?= lang('Inventory.infoFiltered') ?>",
            search: "<?= lang('Inventory.search') ?>",
            searchPlaceholder: "<?= lang('Inventory.searchPlaceholder') ?>",
            paginate: {
                first: "<?= lang('Inventory.paginate.first') ?>",
                last: "<?= lang('Inventory.paginate.last') ?>",
                next: "<?= lang('Inventory.paginate.next') ?>",
                previous: "<?= lang('Inventory.paginate.previous') ?>"
            }
        },
        dom: '<"px-6 py-4 border-b v3-border-default v3-bg-panel"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t v3-border-default v3-bg-panel"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        responsive: true,
        initComplete: function(settings, json) {
            console.log('Low Stock DataTable initialization complete:', json);
            console.log('Settings:', settings);
            console.log('JSON response:', json);
            console.log('Table rows:', $('#lowStockTable tbody tr').length);

            // Hide all loading indicators
            hideLoadingIndicators();
        },
        drawCallback: function(settings) {
            console.log('Low Stock DataTable draw callback triggered');
            // Re-initialize any interactive elements after table redraw
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }

            // Ensure loading indicator is hidden after each draw
            hideLoadingIndicators();
        }
    });

    // Fallback timeout to ensure loading indicators are hidden
    setTimeout(function() {
        hideLoadingIndicators();
    }, 5000); // Hide after 5 seconds regardless
});

// Export Functions
function exportLowStockToPDF() {
    console.log('Export Low Stock PDF clicked');
    generateLowStockPDF();
}

function printLowStockTable() {
    console.log('Print Low Stock clicked');
    printLowStockTableContent();
}

function exportLowStockToCSV() {
    console.log('Export Low Stock CSV clicked');
    exportLowStockToCSVFile();
}

function generateLowStockPDF() {
    if (!lowStockTable) {
        alert('<?= lang('Inventory.tableNotReady') ?>');
        return;
    }

    try {
        // Get all data from the table
        const data = lowStockTable.data().toArray();

        // Create PDF content using jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');

        // Add title
        doc.setFontSize(20);
        doc.text('<?= lang('Inventory.lowStockItems') ?> Report', 105, 20, { align: 'center' });

        // Add generation date
        doc.setFontSize(12);
        doc.text(`<?= lang('Inventory.generatedOn') ?>: ${new Date().toLocaleDateString()}`, 20, 35);
        doc.text(`<?= lang('Inventory.totalItemsCount') ?>: ${lowStockTable.page.info().recordsTotal}`, 20, 45);

        // Prepare table data
        const headers = ['<?= lang('Inventory.itemName') ?>', '<?= lang('Inventory.category') ?>', '<?= lang('Inventory.quantity') ?>', 'Min Required', '<?= lang('Inventory.unitPrice') ?>', '<?= lang('Inventory.supplier') ?>', '<?= lang('Inventory.expiryDate') ?>'];
        const tableData = data.map(row => {
            return row.slice(1, 8).map(cell => { // Skip ID column
                if (typeof cell === 'string') {
                    // Remove HTML tags and clean text
                    return cell.replace(/<[^>]*>/g, '').trim();
                }
                return cell || '';
            });
        });

        // Add table using autoTable
        doc.autoTable({
            head: [headers],
            body: tableData,
            startY: 55,
            styles: {
                fontSize: 8,
                cellPadding: 3,
            },
            headStyles: {
                fillColor: [245, 245, 245],
                textColor: [0, 0, 0],
                fontStyle: 'bold',
            },
            columnStyles: {
                0: { cellWidth: 35 }, // Item Name
                1: { cellWidth: 25 }, // Category
                2: { cellWidth: 20 }, // Quantity
                3: { cellWidth: 20 }, // Min Required
                4: { cellWidth: 25 }, // Unit Price
                5: { cellWidth: 30 }, // Supplier
                6: { cellWidth: 25 }, // Expiry Date
            },
            margin: { left: 20, right: 20 },
        });

        // Save the PDF
        const fileName = `low_stock_report_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(fileName);

    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('<?= lang('Inventory.errorGeneratingPDF') ?>');
    }
}

function printLowStockTableContent() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');

    // Get table data
    const table = document.getElementById('lowStockTable');
    const tableClone = table.cloneNode(true);

    // Set up print document
    const doc = printWindow.document;
    doc.open();

    // Create basic structure
    const html = doc.createElement('html');
    const head = doc.createElement('head');
    const title = doc.createElement('title');
    title.textContent = '<?= lang('Inventory.lowStockItems') ?> Report';
    head.appendChild(title);

    // Add styles
    const style = doc.createElement('style');
    style.textContent = `
        body { font-family: "Inter", "Noto Sans Arabic", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Noto Sans", sans-serif; margin: 20px; }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .no-print { display: none; }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    `;
    head.appendChild(style);

    const body = doc.createElement('body');

    const h1 = doc.createElement('h1');
    h1.textContent = '<?= lang('Inventory.lowStockItems') ?> Report';

    const p1 = doc.createElement('p');
    const strong1 = doc.createElement('strong');
    strong1.textContent = '<?= lang('Inventory.generatedOn') ?>:';
    p1.appendChild(strong1);
    p1.appendChild(document.createTextNode(` ${new Date().toLocaleDateString()}`));

    const p2 = doc.createElement('p');
    const strong2 = doc.createElement('strong');
    strong2.textContent = '<?= lang('Inventory.totalItemsCount') ?>:';
    p2.appendChild(strong2);
    p2.appendChild(document.createTextNode(` ${lowStockTable ? lowStockTable.page.info().recordsTotal : 'N/A'}`));

    body.appendChild(h1);
    body.appendChild(p1);
    body.appendChild(p2);
    body.appendChild(tableClone);

    html.appendChild(head);
    html.appendChild(body);

    // Write the built DOM to the new window
    doc.appendChild(html);
    doc.close();

    // Wait for content to load, then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}

function exportLowStockToCSVFile() {
    if (!lowStockTable) {
        alert('<?= lang('Inventory.tableNotReady') ?>');
        return;
    }

    try {
        // Get all data from the table
        const data = lowStockTable.data().toArray();

        // Create CSV headers
        const headers = ['<?= lang('Inventory.itemName') ?>', '<?= lang('Inventory.category') ?>', '<?= lang('Inventory.quantity') ?>', 'Min Required', '<?= lang('Inventory.unitPrice') ?>', '<?= lang('Inventory.supplier') ?>', '<?= lang('Inventory.expiryDate') ?>'];

        // Create CSV content
        let csvContent = headers.join(',') + '\n';

        data.forEach(row => {
            // Clean the data for CSV (remove HTML tags, quotes, etc.)
            const cleanRow = row.slice(1, 8).map(cell => { // Skip ID column
                if (typeof cell === 'string') {
                    // Remove HTML tags
                    const textContent = cell.replace(/<[^>]*>/g, '');
                    // Escape quotes and wrap in quotes if contains comma
                    return textContent.includes(',') ? `"${textContent.replace(/"/g, '""')}"` : textContent;
                }
                return cell || '';
            });
            csvContent += cleanRow.join(',') + '\n';
        });

        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `low_stock_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

    } catch (error) {
        console.error('Error exporting CSV:', error);
        alert('<?= lang('Inventory.errorExportingCSV') ?>');
    }
}
</script>

<?= $this->endSection() ?>
