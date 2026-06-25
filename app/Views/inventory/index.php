<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
?>


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
    margin-inline-end: 12px !important;
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
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14) !important;
}

.dataTables_length select:hover {
    border-color: #9ca3af !important;
}

.dataTables_filter {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-inline-start: auto !important;
    order: 2 !important;
}

.dataTables_filter label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-inline-end: 12px !important;
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
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14) !important;
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
    background-color: #0d9488 !important;
    border-color: #0d9488 !important;
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
    margin-inline-start: auto !important;
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
    margin-inline-start: auto !important;
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
    margin-inline-end: auto !important;
}

.dataTables_wrapper .dataTables_filter {
    margin-inline-start: auto !important;
    margin-inline-end: 0 !important;
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

.inventory-page .dataTables_wrapper {
    border-radius: 0 0 1.5rem 1.5rem;
    overflow: hidden;
}

.inventory-page .dt-buttons {
    display: none;
}

.inventory-page .dataTables_length label,
.inventory-page .dataTables_filter label {
    color: #475569 !important;
    font-size: 0.8rem !important;
    font-weight: 800 !important;
}

.inventory-page .dataTables_filter input,
.inventory-page .dataTables_length select {
    border-radius: 0.85rem !important;
    border-color: #cbd5e1 !important;
    background: #fff !important;
    box-shadow: 0 10px 24px -22px rgba(15,23,42,0.5) !important;
}

.inventory-page .dataTables_filter input:focus,
.inventory-page .dataTables_length select:focus {
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 3px rgba(13,148,136,0.14) !important;
}

.inventory-page #inventoryTable thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
    color: #334155 !important;
    text-align: start !important;
    font-weight: 800 !important;
}

.inventory-page #inventoryTable tbody tr:hover {
    background: linear-gradient(90deg, rgba(240,253,250,0.96), rgba(236,253,245,0.62)) !important;
}

.inventory-page .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #0d9488, #2563eb) !important;
    border-color: transparent !important;
    color: #fff !important;
}

.inventory-page .dataTables_paginate .paginate_button:hover {
    background: #f0fdfa !important;
    border-color: #5eead4 !important;
    color: #0f766e !important;
}
</style>

<div class="inventory-page min-h-screen bg-slate-50" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="mb-5 overflow-hidden rounded-[1.55rem] bg-gradient-to-br from-teal-700 via-blue-700 to-slate-950 text-white shadow-2xl shadow-teal-900/18">
            <div class="space-y-5 p-5 sm:p-6">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/16 text-white shadow-lg ring-1 ring-white/20">
                        <i class="fas fa-box text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-teal-100"><?= esc($tr('inventoryItems')) ?></p>
                        <h1 class="mt-1 text-2xl font-black leading-tight text-white sm:text-3xl"><?= esc($tr('inventoryManagement')) ?></h1>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <?php if (has_permission('inventory', 'create')): ?>
                        <a href="<?= base_url('inventory/create') ?>" data-inventory-create-modal-open class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-black text-teal-800 shadow-xl shadow-slate-950/18 transition hover:-translate-y-0.5 hover:bg-teal-50 focus:outline-none focus:ring-4 focus:ring-white/25">
                            <i class="fas fa-plus"></i>
                            <?= esc($tr('addNewItem')) ?>
                        </a>
                        <a href="<?= base_url('inventory/templates') ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/12 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/18">
                            <i class="fas fa-layer-group"></i>
                            <?= esc($tr('starterTemplates')) ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('providers') ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/12 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/18">
                        <i class="fas fa-handshake"></i>
                        <?= esc($tr('suppliers')) ?>
                    </a>
                    <a href="<?= base_url('inventory/reorder-list') ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-sm font-black text-amber-950 shadow-lg shadow-amber-950/18 transition hover:-translate-y-0.5 hover:bg-amber-300">
                        <i class="fas fa-cart-shopping"></i>
                        <?= esc($tr('reorderList')) ?>
                        <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-950 px-1 text-xs font-black text-white"><?= (int) ($open_reorder_count ?? 0) ?></span>
                    </a>
                    <a href="<?= base_url('inventory/movements') ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/12 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/18">
                        <i class="fas fa-arrows-rotate"></i>
                        <?= esc($tr('movements')) ?>
                    </a>
                    <a href="<?= base_url('inventory/usage-history') ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/12 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/18">
                        <i class="fas fa-history"></i>
                        <?= esc($tr('usageHistory')) ?>
                    </a>
                    <a href="<?= base_url('inventory/low-stock') ?>" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-red-950/18 transition hover:-translate-y-0.5 hover:bg-red-600">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= esc($tr('lowStockAlert')) ?>
                    </a>
                </div>
            </div>
        </section>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="text-sm font-medium text-green-800"><?= esc($tr('success')) ?></h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p><?= session()->getFlashdata('success') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="text-sm font-medium text-red-800"><?= esc($tr('error')) ?></h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= session()->getFlashdata('error') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="text-sm font-medium text-red-800"><?= esc($tr('errorLoadingInventory')) ?></h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= $error ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl bg-gradient-to-br from-teal-700 to-blue-800 p-4 text-white shadow-xl shadow-teal-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-teal-100"><?= esc($tr('totalItems')) ?></p>
                        <p class="mt-2 text-3xl font-black total-items-count"><?= $total_items ?? 0 ?></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-boxes"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-emerald-600 to-green-800 p-4 text-white shadow-xl shadow-emerald-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-emerald-100"><?= esc($tr('inStock')) ?></p>
                        <p class="mt-2 text-3xl font-black active-items-count"><?= $in_stock_items ?? 0 ?></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-check-circle"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-amber-600 to-orange-800 p-4 text-white shadow-xl shadow-amber-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-amber-100"><?= esc($tr('lowStock')) ?></p>
                        <p class="mt-2 text-3xl font-black low-stock-count"><?= $low_stock_items ?? 0 ?></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-exclamation-triangle"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-red-600 to-rose-800 p-4 text-white shadow-xl shadow-red-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-red-100"><?= esc($tr('outOfStock')) ?></p>
                        <p class="mt-2 text-3xl font-black out-of-stock-count"><?= $out_of_stock_items ?? 0 ?></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-times-circle"></i></span>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-[1.55rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/7">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-teal-50/80 px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-100 text-teal-700">
                            <i class="fas fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-black text-slate-950"><?= esc($tr('inventoryItems')) ?></h3>
                            <p class="mt-1 text-xs font-bold text-slate-500"><span class="total-items-count"><?= (int) ($total_items ?? 0) ?></span> <?= esc($tr('items')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-red-50 hover:text-red-700">
                            <i class="fas fa-file-pdf text-red-600"></i>
                            <?= esc($tr('exportToPDF')) ?>
                        </button>
                        <button onclick="printInventory()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-950">
                            <i class="fas fa-print text-slate-600"></i>
                            <?= esc($tr('exportToPrint')) ?>
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fas fa-file-csv text-emerald-600"></i>
                            <?= esc($tr('exportToCSV')) ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="inventoryTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-hashtag text-teal-700"></i>
                                    <span><?= esc($tr('id')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-box text-teal-700"></i>
                                    <span><?= esc($tr('item')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tags text-teal-700"></i>
                                    <span><?= esc($tr('category')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-layer-group text-teal-700"></i>
                                    <span><?= esc($tr('quantity')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-dollar-sign text-teal-700"></i>
                                    <span><?= esc($tr('unitPrice')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-truck text-teal-700"></i>
                                    <span><?= esc($tr('supplier')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-teal-700"></i>
                                    <span><?= esc($tr('expiryDate')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-teal-700"></i>
                                    <span><?= esc($tr('status')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs text-teal-700"></i>
                                    <span><?= esc($tr('actions')) ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

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
let inventoryTable;

// Inventory Filter Management Class
class InventoryFilterManager {
    constructor() {
        this.searchValue = '';
        this.categoryFilter = '';
        this.statusFilter = '';
        this.allItems = [];
        this.filteredItems = [];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadAllItems();
    }
    
    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('inventorySearchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchValue = e.target.value.toLowerCase();
                    this.applyFilters();
                }, 300);
            });
        }
        
        // Category filter
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.categoryFilter = e.target.value;
                this.applyFilters();
            });
        }
        
        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.statusFilter = e.target.value;
                this.applyFilters();
            });
        }
    }
    
    loadAllItems() {
        // Get all inventory rows from the table
        const itemRows = document.querySelectorAll('tbody tr');
        this.allItems = Array.from(itemRows).map(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 7) {
                const itemCell = cells[0];
                const categoryCell = cells[1];
                const statusCell = cells[4];
                
                const itemName = itemCell.querySelector('.text-sm.font-medium')?.textContent?.toLowerCase() || '';
                const description = itemCell.querySelector('.text-sm.text-gray-500')?.textContent?.toLowerCase() || '';
                
                const category = categoryCell.querySelector('.bg-slate-100')?.textContent?.toLowerCase() || '';
                
                // Determine status from CSS classes
                let status = 'in_stock';
                if (statusCell.querySelector('.bg-red-100')) {
                    status = 'out_of_stock';
                } else if (statusCell.querySelector('.bg-yellow-100')) {
                    status = 'low_stock';
                }
                
                return {
                    element: row,
                    name: itemName,
                    description: description,
                    category: category,
                    status: status,
                    searchText: `${itemName} ${description}`.toLowerCase()
                };
            }
            return null;
        }).filter(item => item !== null);
        
        this.filteredItems = [...this.allItems];
    }
    
    applyFilters() {
        this.filteredItems = this.allItems.filter(item => {
            // Search filter
            if (this.searchValue && !item.searchText.includes(this.searchValue)) {
                return false;
            }
            
            // Category filter
            if (this.categoryFilter && item.category !== this.categoryFilter) {
                return false;
            }
            
            // Status filter
            if (this.statusFilter && item.status !== this.statusFilter) {
                return false;
            }
            
            return true;
        });
        
        this.updateTableDisplay();
    }
    
    updateTableDisplay() {
        // Hide all rows first
        this.allItems.forEach(item => {
            item.element.style.display = 'none';
        });
        
        // Show filtered rows
        this.filteredItems.forEach(item => {
            item.element.style.display = '';
        });
        
        // Update counter
        const filteredCountElement = document.getElementById('filteredCount');
        const totalCountElement = document.getElementById('totalCount');
        if (filteredCountElement) {
            filteredCountElement.textContent = this.filteredItems.length;
        }
        if (totalCountElement) {
            totalCountElement.textContent = this.allItems.length;
        }
        
        // Show/hide "No items found" message
        const tbody = document.querySelector('tbody');
        const noItemsRow = tbody.querySelector('tr[data-no-items]');
        
        if (this.filteredItems.length === 0) {
            if (!noItemsRow) {
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-no-items', 'true');
                
                const cell = document.createElement('td');
                cell.colSpan = 7;
                cell.className = 'px-6 py-12 text-center';
                
                const container = document.createElement('div');
                container.className = 'flex flex-col items-center';
                
                const iconDiv = document.createElement('div');
                iconDiv.className = 'w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4';
                const icon = document.createElement('i');
                icon.className = 'fas fa-search text-gray-400 text-2xl';
                iconDiv.appendChild(icon);
                
                const title = document.createElement('h3');
                title.className = 'text-lg font-medium text-gray-900 mb-2';
            title.textContent = '<?= esc($tr('noInventoryItemsFound'), 'js') ?>';
                
                const message = document.createElement('p');
                message.className = 'text-gray-500 mb-4';
                message.textContent = 'Try adjusting your search criteria.';
                
                container.appendChild(iconDiv);
                container.appendChild(title);
                container.appendChild(message);
                cell.appendChild(container);
                newRow.appendChild(cell);
                
                tbody.appendChild(newRow);
            } else {
                noItemsRow.style.display = '';
            }
        } else {
            if (noItemsRow) {
                noItemsRow.style.display = 'none';
            }
        }
    }
}


function adjustStock(id) {
    const newQuantity = prompt('Enter new quantity:');
    if (newQuantity !== null && !isNaN(newQuantity)) {
        fetch(`/inventory/${id}/adjust`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            },
            body: JSON.stringify({ 
                quantity: parseInt(newQuantity),
                [window.csrfConfig.name]: window.getCsrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc($tr('error'), 'js') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the item.');
        });
    }
}

function deleteItem(id) {
    if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
        // Show loading state
        const deleteBtn = event.target.closest('button');
        
        // Cache original children once if not already cached
        if (!deleteBtn.hasOwnProperty('_originalChildren')) {
            deleteBtn._originalChildren = Array.from(deleteBtn.childNodes).map(node => node.cloneNode(true));
        }
        
        const loadingIcon = document.createElement('i');
        loadingIcon.className = 'fas fa-spinner fa-spin';
        deleteBtn.replaceChildren(loadingIcon);
        deleteBtn.disabled = true;
        
        fetch(`<?= base_url('inventory') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                // Show success message
                showNotification('<?= esc($tr('itemDeletedSuccessfully'), 'js') ?>', 'success');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('<?= esc($tr('error'), 'js') ?>: ' + data.message, 'error');
                // Restore button state
                deleteBtn.replaceChildren(...deleteBtn._originalChildren.map(node => node.cloneNode(true)));
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('<?= esc($tr('errorDeletingItem'), 'js') ?>', 'error');
            // Restore button state
            deleteBtn.replaceChildren(...deleteBtn._originalChildren.map(node => node.cloneNode(true)));
            deleteBtn.disabled = false;
        });
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-teal-600 text-white'
    }`;
    
    const container = document.createElement('div');
    container.className = 'flex items-center gap-2';
    
    const icon = document.createElement('i');
    icon.className = `fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'}`;
    container.appendChild(icon);
    
    const msgSpan = document.createElement('span');
    msgSpan.textContent = message;
    container.appendChild(msgSpan);
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ms-2 text-white hover:text-gray-200';
    closeBtn.onclick = function() { this.closest('.fixed').remove(); };
    
    const closeIcon = document.createElement('i');
    closeIcon.className = 'fas fa-times';
    closeBtn.appendChild(closeIcon);
    container.appendChild(closeBtn);
    
    notification.appendChild(container);
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Load inventory statistics
function loadInventoryStats() {
    $.ajax({
        url: '<?= base_url('inventory/getInventoryStats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Update the stats cards if they exist
            if (data.total_items !== undefined) {
                $('.total-items-count').text(data.total_items);
            }
            if (data.active_items !== undefined) {
                $('.active-items-count').text(data.active_items);
            }
            if (data.low_stock_items !== undefined) {
                $('.low-stock-count').text(data.low_stock_items);
            }
            if (data.out_of_stock_items !== undefined) {
                $('.out-of-stock-count').text(data.out_of_stock_items);
            }
        },
        error: function() {
            console.error('<?= esc($tr('failedToLoadInventoryStatistics'), 'js') ?>');
        }
    });
}

// Initialize DataTable with server-side processing
$(document).ready(function() {
    // Check if DataTables is loaded
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables is not loaded. Please check if the DataTables library is included.');
        return;
    }
    
    // Load statistics
    loadInventoryStats();
    
    // Custom loading indicator management
    function hideLoadingIndicators() {
        $('.dataTables_processing').hide();
        $('.loading-indicator').hide();
        $('.spinner').hide();
        $('.loading-dots').hide();
        $('#inventoryTable').closest('.table-container').find('.loading').hide();
    }
    
    function showLoadingIndicators() {
        $('.dataTables_processing').show();
    }
    
    inventoryTable = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        processingIndicator: true,
        ajax: {
            url: '<?= base_url('inventory/getInventoryData') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            data: function(d) {
                d[window.csrfConfig.name] = window.getCsrfToken();
                return d;
            },
            dataSrc: function(json) {
                // Check if new CSRF token is returned
                if (json.csrf_token) {
                    window.refreshCsrfToken(json.csrf_token);
                }
                
                if (json.error) {
                    console.error('Server error:', json.error);
                        alert('<?= esc($tr('error'), 'js') ?>: ' + json.error);
                    return [];
                }
                
                if (!json.data) {
                    console.error('No data property in response');
                    return [];
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
                
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
                
            let errorMessage = '<?= esc($tr('errorLoadingData'), 'js') ?>';
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
                const tbody = document.querySelector('#inventoryTable tbody');
                tbody.replaceChildren(); // Clear existing content
                
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 9;
                td.className = 'py-8 text-center text-slate-500';
                
                const container = document.createElement('div');
                container.className = 'flex flex-col items-center gap-2';
                
                const icon = document.createElement('i');
                icon.className = 'fas fa-exclamation-triangle text-4xl text-yellow-500';
                
                const title = document.createElement('p');
                title.className = 'text-lg font-medium';
                title.textContent = 'Unable to load inventory data';
                
                const msg = document.createElement('p');
                msg.className = 'text-sm';
                msg.textContent = 'Please check your connection and try again.';
                
                const btn = document.createElement('button');
                btn.className = 'mt-4 rounded-xl bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800';
                btn.onclick = () => location.reload();
                
                const btnIcon = document.createElement('i');
                btnIcon.className = 'fas fa-refresh me-2';
                btn.appendChild(btnIcon);
                btn.appendChild(document.createTextNode('Retry'));
                
                container.appendChild(icon);
                container.appendChild(title);
                container.appendChild(msg);
                container.appendChild(btn);
                
                td.appendChild(container);
                tr.appendChild(td);
                tbody.appendChild(tr);
            }
        },
        columns: [
            { 
                data: 0, // ID (hidden)
                visible: false
            },
            { 
                data: 1, // Item Name
                render: function(data, type, row) {
                    return data; // Return text for sorting/filtering
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    td.replaceChildren(); // Clear content
                    
                    const itemId = rowData[0];
                    const link = document.createElement('a');
                    link.href = '<?= base_url('inventory') ?>/' + itemId;
                    link.className = 'group block cursor-pointer rounded-xl transition-colors duration-200 hover:bg-teal-50/70';

                    const container = document.createElement('div');
                    container.className = 'flex items-center gap-3';
                    
                    const iconBox = document.createElement('div');
                    iconBox.className = 'flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-box text-sm';
                    iconBox.appendChild(icon);
                    
                    const textBox = document.createElement('div');
                    
                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'text-sm font-extrabold text-slate-950 transition-colors duration-200 group-hover:text-teal-800';
                    nameDiv.textContent = cellData;
                    
                    const idDiv = document.createElement('div');
                    idDiv.className = 'text-xs font-bold text-slate-500';
                    idDiv.textContent = 'ID: ' + rowData[0];
                    
                    textBox.appendChild(nameDiv);
                    textBox.appendChild(idDiv);
                    
                    container.appendChild(iconBox);
                    container.appendChild(textBox);
                    link.appendChild(container);
                    td.appendChild(link);
                }
            },
            { 
                data: 2, // Category
                render: function(data, type, row) {
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    td.replaceChildren();
                    
                    const span = document.createElement('span');
                    span.className = 'inline-flex items-center rounded-xl border border-slate-200 bg-gradient-to-r from-slate-100 to-gray-100 px-3 py-1.5 text-xs font-extrabold text-slate-800';
                    span.textContent = cellData;
                    
                    td.appendChild(span);
                }
            },
            { 
                data: 3 // Quantity
            },
            { 
                data: 4 // Unit Price
            },
            { 
                data: 5 // Supplier
            },
            { 
                data: 6, // Expiry Date
                render: function(data, type, row) {
                    return data || 'N/A';
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    td.replaceChildren();
                    
                    if (!cellData || cellData === 'N/A') {
                        const span = document.createElement('span');
                        span.className = 'text-sm font-bold text-slate-400';
                        span.textContent = 'N/A';
                        td.appendChild(span);
                        return;
                    }
                    
                    const container = document.createElement('div');
                    container.className = 'flex items-center gap-2';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-calendar text-orange-600 text-xs';
                    
                    const dateText = document.createElement('span');
                    dateText.className = 'text-sm font-bold text-slate-900';
                    dateText.textContent = cellData;
                    
                    container.appendChild(icon);
                    container.appendChild(dateText);
                    td.appendChild(container);
                }
            },
            { 
                data: 7 // Status
            },
            { 
                data: 8, // Actions
                orderable: false,
                searchable: false
            }
        ],
        order: [[1, 'asc']], // Sort by item name by default
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: "Loading inventory data...",
                emptyTable: "<?= esc($tr('noInventoryItemsFound'), 'js') ?>",
                zeroRecords: "<?= esc($tr('noMatchingInventoryItemsFound'), 'js') ?>",
            lengthMenu: "_MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ inventory items",
                infoEmpty: "<?= esc($tr('noInventoryItemsAvailable'), 'js') ?>",
            infoFiltered: "(filtered from _MAX_ total inventory items)",
            search: "",
                searchPlaceholder: "<?= esc($tr('searchInventoryItems'), 'js') ?>",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"border-b border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center gap-4"l><"flex items-center gap-4"f>>rt<"border-t border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center"i><"flex items-center"p>>>',
        responsive: true,
        initComplete: function(settings, json) {
            // Hide all loading indicators
            hideLoadingIndicators();
        },
        drawCallback: function(settings) {
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

// Load inventory statistics
function loadInventoryStats() {
    $.ajax({
        url: '<?= base_url('inventory/getInventoryStats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Update the stats cards if they exist
            if (data.total_items !== undefined) {
                $('.total-items-count').text(data.total_items);
            }
            if (data.active_items !== undefined) {
                $('.active-items-count').text(data.active_items);
            }
            if (data.low_stock_items !== undefined) {
                $('.low-stock-count').text(data.low_stock_items);
            }
            if (data.out_of_stock_items !== undefined) {
                $('.out-of-stock-count').text(data.out_of_stock_items);
            }
        },
        error: function() {
            console.error('<?= esc($tr('failedToLoadInventoryStatistics'), 'js') ?>');
        }
    });
}

// Export Functions
function exportToPDF() {
    generatePDF();
}

function printInventory() {
    printInventoryTable();
}

function generatePDF() {
    if (!inventoryTable) {
        alert('Table not ready. Please wait for the page to load completely.');
        return;
    }
    
    try {
        // Get all data from the table
        const data = inventoryTable.data().toArray();
        
        // Create PDF content using jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');
        
        // Add title
        doc.setFontSize(20);
        doc.text('Inventory Report', 105, 20, { align: 'center' });
        
        // Add generation date
        doc.setFontSize(12);
        doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 20, 35);
        doc.text(`<?= esc($tr('totalItems'), 'js') ?>: ${inventoryTable.page.info().recordsTotal}`, 20, 45);
        
        // Prepare table data
        const headers = ['ID', 'Item Name', 'Category', 'Quantity', 'Unit Price', 'Supplier', 'Expiry Date', 'Status'];
        const tableData = data.map(row => {
            return row.map(cell => {
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
                0: { cellWidth: 15 }, // ID
                1: { cellWidth: 35 }, // Item Name
                2: { cellWidth: 25 }, // Category
                3: { cellWidth: 20 }, // Quantity
                4: { cellWidth: 25 }, // Unit Price
                5: { cellWidth: 30 }, // Supplier
                6: { cellWidth: 25 }, // Expiry Date
                7: { cellWidth: 20 }, // Status
            },
            margin: { left: 20, right: 20 },
        });
        
        // Save the PDF
        const fileName = `inventory_report_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(fileName);
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('<?= esc($tr('errorGeneratingPDF'), 'js') ?>');
    }
}

function printInventoryTable() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    if (!printWindow) {
        alert('Please allow popups for this website');
        return;
    }

    // Get table data
    const table = document.getElementById('inventoryTable');
    const tableClone = table.cloneNode(true);
    
    // Set up the print document
    const doc = printWindow.document;
    doc.open();
    
    // Create HTML structure safely
    doc.write('<!DOCTYPE html><html><head><title>Inventory Report</title></head><body></body></html>');
    doc.close();
    
    const head = doc.head;
    const body = doc.body;
    
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
    
    // Add content
    const h1 = doc.createElement('h1');
    h1.textContent = 'Inventory Report';
    body.appendChild(h1);
    
    const pDate = doc.createElement('p');
    const dateStrong = doc.createElement('strong');
    dateStrong.textContent = 'Generated on: ';
    pDate.appendChild(dateStrong);
    pDate.appendChild(doc.createTextNode(new Date().toLocaleDateString()));
    body.appendChild(pDate);
    
    const pTotal = doc.createElement('p');
    const totalStrong = doc.createElement('strong');
    totalStrong.textContent = '<?= esc($tr('totalItems'), 'js') ?>: ';
    pTotal.appendChild(totalStrong);
    const totalText = inventoryTable ? inventoryTable.page.info().recordsTotal : 'N/A';
    pTotal.appendChild(doc.createTextNode(totalText));
    body.appendChild(pTotal);
    
    // Append cloned table
    body.appendChild(tableClone);
    
    // Wait for content to load (styles), then print
    // setTimeout is robust for ensuring rendering before print dialog
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 500);
}

function exportToCSV() {
    exportInventoryToCSV();
}

function exportInventoryToCSV() {
    if (!inventoryTable) {
        alert('Table not ready. Please wait for the page to load completely.');
        return;
    }
    
    try {
        // Get all data from the table
        const data = inventoryTable.data().toArray();
        
        // Create CSV headers
        const headers = ['ID', 'Item Name', 'Category', 'Quantity', 'Unit Price', 'Supplier', 'Expiry Date', 'Status'];
        
        // Create CSV content
        let csvContent = headers.join(',') + '\n';
        
        data.forEach(row => {
            // Clean the data for CSV (remove HTML tags, quotes, etc.)
            const cleanRow = row.map(cell => {
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
        link.setAttribute('download', `inventory_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
    } catch (error) {
        console.error('Error exporting CSV:', error);
        alert('<?= esc($tr('errorExportingCSV'), 'js') ?>');
    }
}

// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    // loadInventoryStats is already called in $(document).ready
});
</script>

<?= view('inventory/partials/quick_create_modal', [
    'return_to' => workflow_current_target() ?? 'inventory',
    'is_rtl' => !empty($is_rtl),
    'supplier_directory' => $supplier_directory ?? [],
    'currencies' => $currencies ?? ['ILS'],
]) ?>
<?= $this->endSection() ?>
