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
$usageHistory = is_array($usage_history ?? null) ? $usage_history : [];
$totalUsageCost = array_sum(array_column($usageHistory, 'total_cost'));
$thisMonth = date('Y-m');
$monthlyCount = 0;
foreach ($usageHistory as $usage) {
    if (strpos((string) ($usage['usage_date'] ?? ''), $thisMonth) === 0) {
        $monthlyCount++;
    }
}
$averageCost = count($usageHistory) > 0 ? $totalUsageCost / count($usageHistory) : 0;
?>
<div class="v3-operational-view w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <section class="overflow-hidden v3-radius border v3-border v3-bg-forest v3-text-on-primary">
        <div class="relative p-5 sm:p-6 lg:p-7">
            <div class="absolute inset-0 v3-bg-panel"></div>
            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,.76fr)] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center v3-radius v3-surface v3-text-on-primary border v3-border-default">
                        <i class="fas fa-history text-2xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.2em] v3-accent-text"><?= esc(lang('Inventory.inventoryUsage')) ?></p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight v3-text-on-primary sm:text-3xl"><?= esc(lang('Inventory.usageHistory')) ?></h1>
                        <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 v3-accent-text"><?= esc(lang('Inventory.usageHistorySubtitle')) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="v3-radius border v3-border v3-surface p-4">
                        <p class="text-xs font-black uppercase tracking-wide v3-accent-text"><?= esc(lang('Inventory.totalRecords')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= count($usageHistory) ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-bg-primary p-4">
                        <p class="text-xs font-black uppercase tracking-wide v3-text-primary"><?= esc(lang('Inventory.totalValue')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary" dir="ltr"><?= formatCurrency($totalUsageCost) ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-soft-surface p-4">
                        <p class="text-xs font-black uppercase tracking-wide v3-accent-text"><?= esc(lang('Inventory.thisMonth')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= $monthlyCount ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-bg-warning p-4">
                        <p class="text-xs font-black uppercase tracking-wide v3-text-warning"><?= esc(lang('Inventory.avgCost')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary" dir="ltr"><?= formatCurrency($averageCost) ?></p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-text-on-primary border v3-border-default transition v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-arrow-left rtl:rotate-180"></i>
                    <span><?= esc(lang('Inventory.backToInventory')) ?></span>
                </a>
                <a href="<?= base_url('inventory/usage') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-accent-text transition v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-plus"></i>
                    <span><?= esc(lang('Inventory.recordUsage')) ?></span>
                </a>
            </div>
        </div>
    </section>

    <section class="overflow-hidden v3-radius border v3-border v3-surface">
        <div class="flex flex-col gap-4 border-b v3-border p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center v3-radius v3-text-on-primary">
                    <i class="fas fa-clipboard-list"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black v3-text-ink"><?= esc(lang('Inventory.usageHistory')) ?></h2>
                    <p class="mt-1 text-sm font-semibold v3-text-muted"><span class="total-items-count">0</span> <?= esc(lang('Inventory.records')) ?></p>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <button type="button" onclick="exportUsageToPDF()" class="inline-flex items-center justify-center gap-2 v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-black v3-text-ink transition v3-border-default v3-hover-danger v3-hover-text-danger v3-btn v3-btn--secondary">
                    <i class="fas fa-file-pdf v3-text-danger"></i>
                    <span><?= esc(lang('Inventory.exportToPDF')) ?></span>
                </button>
                <button type="button" onclick="printUsageHistory()" class="inline-flex items-center justify-center gap-2 v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-black v3-text-ink transition v3-border-default v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-print v3-text-muted"></i>
                    <span><?= esc(lang('Inventory.exportToPrint')) ?></span>
                </button>
                <button type="button" onclick="exportUsageToCSV()" class="inline-flex items-center justify-center gap-2 v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-black v3-text-ink transition v3-border-default v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-file-csv v3-text-primary"></i>
                    <span><?= esc(lang('Inventory.exportToCSV')) ?></span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <div class="dataTables-wrapper">
                <table id="usageHistoryTable" class="w-full min-w-full">
                    <thead class="v3-surface">
                        <tr>
                                    <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-calendar v3-accent-text"></i>
                                            <span><?= esc(lang('Inventory.date')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user v3-accent-text"></i>
                                            <span><?= esc(lang('Inventory.patient')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-stethoscope v3-accent-text"></i>
                                            <span><?= esc(lang('Inventory.treatment')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-box v3-accent-text"></i>
                                            <span><?= esc(lang('Inventory.itemsUsed')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-dollar-sign v3-accent-text"></i>
                                            <span><?= esc(lang('Inventory.totalCost')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user-md v3-accent-text"></i>
                                            <span><?= esc(lang('Inventory.recordedBy')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-cogs v3-accent-text"></i>
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
</div>

<!-- Usage Details Modal -->
<div id="usageDetailsModal" class="fixed inset-0 z-[2147483500] hidden h-full w-full overflow-y-auto v3-backdrop p-4">
    <div class="relative mx-auto mt-14 w-full max-w-3xl overflow-hidden v3-radius border v3-border v3-surface">
        <div>
            <div class="flex items-center justify-between gap-4 border-b v3-border px-5 py-4">
                <h3 class="text-lg font-black v3-text-ink"><?= esc(lang('Inventory.viewDetails')) ?></h3>
                <button onclick="closeUsageDetails()" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--secondary">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="usageDetailsContent" class="p-5">
                <!-- Content will be loaded here -->
            </div>
        </div>
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
let usageHistoryTable;

// Client-side currency formatting function
function formatCurrency(amount) {
    if (typeof amount !== 'number') {
        amount = parseFloat(amount) || 0;
    }

    // Get currency symbol and position from server-side settings
    const currencySymbol = '<?= getCurrencySymbol() ?>';
    const currencyPosition = '<?= settings()->get('currency_position', 'before') ?>';

    const formattedAmount = amount.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // Apply currency position setting
    if (currencyPosition === 'after') {
        return formattedAmount + ' ' + currencySymbol;
    } else {
        return currencySymbol + formattedAmount;
    }
}

// Client-side date formatting function
function formatDate(dateString) {
    if (!dateString) return '';

    try {
        const date = new Date(dateString);
        const dateFormat = '<?= settings()->get('date_format', 'Y-m-d') ?>';

        // Convert PHP date format to JavaScript format
        let jsFormat = dateFormat
            .replace('Y', 'yyyy')
            .replace('m', 'MM')
            .replace('d', 'dd')
            .replace('y', 'yy');

        // Format the date according to user's preference
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        };

        // Apply specific formatting based on user's date format preference
        if (dateFormat === 'Y-m-d') {
            // YYYY-MM-DD format
            return date.toISOString().split('T')[0];
        } else if (dateFormat === 'm/d/Y') {
            // MM/DD/YYYY format
            return (date.getMonth() + 1).toString().padStart(2, '0') + '/' +
                   date.getDate().toString().padStart(2, '0') + '/' +
                   date.getFullYear();
        } else if (dateFormat === 'd/m/Y') {
            // DD/MM/YYYY format
            return date.getDate().toString().padStart(2, '0') + '/' +
                   (date.getMonth() + 1).toString().padStart(2, '0') + '/' +
                   date.getFullYear();
        } else {
            // Default to locale string
            return date.toLocaleDateString();
        }
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString; // Return original if formatting fails
    }
}

function viewUsageDetails(usageId) {
    const contentDiv = document.getElementById('usageDetailsContent');
    const modal = document.getElementById('usageDetailsModal');

    // Clear existing content
    contentDiv.textContent = '';

    // Fetch usage details via AJAX
    fetch(`<?= base_url() ?>inventory/usage-details/${usageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const usage = data.usage;
                let items = [];
                try {
                    items = JSON.parse(usage.items_used);
                } catch (e) {
                    console.error('Error parsing items_used:', e);
                }

                const container = document.createElement('div');
                container.className = 'space-y-4';

                // Header Grid
                const gridDiv = document.createElement('div');
                gridDiv.className = 'grid grid-cols-2 gap-4';

                // Date Section
                const dateDiv = document.createElement('div');
                const dateLabel = document.createElement('label');
                dateLabel.className = 'text-sm font-medium v3-text-muted';
                dateLabel.textContent = '<?= esc(lang('Inventory.usageDateRequired'), 'js') ?>';
                const dateP = document.createElement('p');
                dateP.className = 'text-lg font-semibold v3-text-ink';
                dateP.textContent = formatDate(usage.usage_date);
                dateDiv.appendChild(dateLabel);
                dateDiv.appendChild(dateP);

                // Cost Section
                const costDiv = document.createElement('div');
                const costLabel = document.createElement('label');
                costLabel.className = 'text-sm font-medium v3-text-muted';
                costLabel.textContent = '<?= esc(lang('Inventory.totalCost'), 'js') ?>';
                const costP = document.createElement('p');
                costP.className = 'text-lg font-semibold v3-text-primary';
                costP.textContent = formatCurrency(parseFloat(usage.total_cost));
                costDiv.appendChild(costLabel);
                costDiv.appendChild(costP);

                gridDiv.appendChild(dateDiv);
                gridDiv.appendChild(costDiv);
                container.appendChild(gridDiv);

                // Items List
                const itemsSection = document.createElement('div');
                const itemsLabel = document.createElement('label');
                itemsLabel.className = 'text-sm font-medium v3-text-muted';
                itemsLabel.textContent = '<?= esc(lang('Inventory.itemsUsed'), 'js') ?>';
                itemsSection.appendChild(itemsLabel);

                const itemsList = document.createElement('div');
                itemsList.className = 'mt-2 space-y-2';

                items.forEach(item => {
                    const itemRow = document.createElement('div');
                    itemRow.className = 'flex justify-between items-center py-2 border-b v3-border-default';

                    const leftCol = document.createElement('div');
                    const itemName = document.createElement('div');
                    itemName.className = 'font-medium v3-text-ink';
                    itemName.textContent = item.item_name;
                    const itemQty = document.createElement('div');
                    itemQty.className = 'text-sm v3-text-muted';
                    itemQty.textContent = `Quantity: ${item.quantity_used}`;
                    leftCol.appendChild(itemName);
                    leftCol.appendChild(itemQty);

                    const rightCol = document.createElement('div');
                    rightCol.className = 'text-right';
                    const unitCost = document.createElement('div');
                    unitCost.className = 'font-medium v3-text-ink';
                    unitCost.textContent = formatCurrency(parseFloat(item.unit_cost));
                    const totalLineCost = document.createElement('div');
                    totalLineCost.className = 'text-sm v3-text-muted';
                    totalLineCost.textContent = `Total: ${formatCurrency(parseFloat(item.total_cost))}`;
                    rightCol.appendChild(unitCost);
                    rightCol.appendChild(totalLineCost);

                    itemRow.appendChild(leftCol);
                    itemRow.appendChild(rightCol);
                    itemsList.appendChild(itemRow);
                });

                itemsSection.appendChild(itemsList);
                container.appendChild(itemsSection);

                // Notes (Optional)
                if (usage.notes) {
                    const notesDiv = document.createElement('div');
                    const notesLabel = document.createElement('label');
                    notesLabel.className = 'text-sm font-medium v3-text-muted';
                    notesLabel.textContent = '<?= esc(lang('Inventory.notes'), 'js') ?>';
                    const notesP = document.createElement('p');
                    notesP.className = 'v3-text-ink mt-1';
                    notesP.textContent = usage.notes;
                    notesDiv.appendChild(notesLabel);
                    notesDiv.appendChild(notesP);
                    container.appendChild(notesDiv);
                }

                // Footer (Recorded By)
                const footerDiv = document.createElement('div');
                footerDiv.className = 'text-sm v3-text-muted';
                footerDiv.textContent = `${'<?= esc(lang('Inventory.recordedBy'), 'js') ?>'}: ${usage.recorded_by_name} | ${formatDate(usage.created_at)}`;
                container.appendChild(footerDiv);

                if (usage.finance_entry_id) {
                    const financeRow = document.createElement('div');
                    financeRow.className = 'pt-2 border-t v3-border-default';

                    const financeLink = document.createElement('a');
                    financeLink.href = `<?= base_url() ?>finance/${usage.finance_entry_id}`;
                    financeLink.className = 'inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold v3-bg-alert v3-text-danger v3-hover-danger transition-colors';
                    const financeIcon = document.createElement('i');
                    financeIcon.className = 'fas fa-file-invoice-dollar mr-2';
                    const financeText = document.createElement('span');
                    financeText.textContent = '<?= esc(lang('Inventory.viewFinanceEntry'), 'js') ?>';
                    financeLink.appendChild(financeIcon);
                    financeLink.appendChild(financeText);

                    financeRow.appendChild(financeLink);
                    container.appendChild(financeRow);
                }

                contentDiv.appendChild(container);
            } else {
                renderError('Error loading usage details: ' + data.message);
            }
            modal.classList.remove('hidden');
        })
        .catch(error => {
            renderError('Error loading usage details: ' + error.message);
            modal.classList.remove('hidden');
        });
}

function renderError(message) {
    const contentDiv = document.getElementById('usageDetailsContent');
    contentDiv.textContent = '';

    const errorDiv = document.createElement('div');
    errorDiv.className = 'p-4 v3-bg-alert rounded-lg';

    const p = document.createElement('p');
    p.className = 'v3-text-danger';
    p.textContent = message;

    errorDiv.appendChild(p);
    contentDiv.appendChild(errorDiv);
}

function closeUsageDetails() {
    document.getElementById('usageDetailsModal').classList.add('hidden');
}

function printUsageRecord(usageId) {
    // This would generate a printable version of the usage record
    window.open('<?= base_url() ?>inventory/usage-print/' + usageId, '_blank');
}

// Close modal when clicking outside
document.getElementById('usageDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUsageDetails();
    }
});

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
        $('#usageHistoryTable').closest('.table-container').find('.loading').hide();
    }

    function showLoadingIndicators() {
        $('.dataTables_processing').show();
    }

    console.log('Initializing DataTable for #usageHistoryTable');
    console.log('Table element:', $('#usageHistoryTable'));
    console.log('Table element length:', $('#usageHistoryTable').length);

    usageHistoryTable = $('#usageHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        processingIndicator: true,
        ajax: {
            url: '<?= base_url('inventory/getUsageHistoryData') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            data: function(d) {
                d[window.csrfConfig.name] = window.getCsrfToken();
                return d;
            },
            dataSrc: function(json) {
                console.log('Received Usage History DataTables response:', json);

                // Refresh CSRF token if present
                if (json.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(json.csrf_token);
                }

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
                    console.log('No usage history records found');
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
                const tbody = document.querySelector('#usageHistoryTable tbody');
                tbody.replaceChildren();

                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 7;
                td.className = 'text-center py-8 v3-text-muted';

                const container = document.createElement('div');
                container.className = 'flex flex-col items-center space-y-2';

                const icon = document.createElement('i');
                icon.className = 'fas fa-exclamation-triangle text-4xl v3-text-warning';

                const title = document.createElement('p');
                title.className = 'text-lg font-medium';
                title.textContent = '<?= lang("Inventory.unableToLoadData") ?>';

                const msg = document.createElement('p');
                msg.className = 'text-sm';
                msg.textContent = '<?= lang("Inventory.checkConnection") ?>';

                const btn = document.createElement('button');
                btn.onclick = () => location.reload();
                btn.className = 'mt-4 px-4 py-2 v3-bg-primary v3-text-on-primary rounded-md v3-hover-primary';

                const btnIcon = document.createElement('i');
                btnIcon.className = 'fas fa-refresh mr-2';
                btn.appendChild(btnIcon);
                btn.appendChild(document.createTextNode('<?= lang("Inventory.retry") ?>'));

                container.appendChild(icon);
                container.appendChild(title);
                container.appendChild(msg);
                container.appendChild(btn);

                td.appendChild(container);
                tr.appendChild(td);
                tbody.appendChild(tr);

                // Hide loading indicators on error
                hideLoadingIndicators();
            }
        },
        columns: [
            { data: 0 }, // Date
            { data: 1 }, // Patient
            { data: 2 }, // Treatment
            { data: 3 }, // Items Used
            { data: 4 }, // Total Cost
            { data: 5 }, // Recorded By
            { data: 6, orderable: false } // Actions
        ],
        order: [[0, 'desc']], // Sort by date descending by default
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
            console.log('Usage History DataTable initialization complete:', json);
            console.log('Settings:', settings);
            console.log('JSON response:', json);
            console.log('Table rows:', $('#usageHistoryTable tbody tr').length);

            // Hide all loading indicators
            hideLoadingIndicators();
        },
        drawCallback: function(settings) {
            console.log('Usage History DataTable draw callback triggered');
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
function exportUsageToPDF() {
    console.log('Export Usage PDF clicked');
    generateUsagePDF();
}

function printUsageHistory() {
    console.log('Print Usage clicked');
    printUsageHistoryTable();
}

function exportUsageToCSV() {
    console.log('Export Usage CSV clicked');
    exportUsageHistoryToCSV();
}

function generateUsagePDF() {
    if (!usageHistoryTable) {
        alert('<?= lang('Inventory.tableNotReady') ?>');
        return;
    }

    try {
        // Get all data from the table
        const data = usageHistoryTable.data().toArray();

        // Create PDF content using jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');

        // Add title
        doc.setFontSize(20);
        doc.text('<?= lang('Inventory.usageHistoryReport') ?>', 105, 20, { align: 'center' });

        // Add generation date
        doc.setFontSize(12);
        doc.text(`<?= lang('Inventory.generatedOn') ?>: ${new Date().toLocaleDateString()}`, 20, 35);
        doc.text(`<?= lang('Inventory.totalRecordsCount') ?>: ${usageHistoryTable.page.info().recordsTotal}`, 20, 45);

        // Prepare table data
        const headers = ['<?= lang('Inventory.date') ?>', '<?= lang('Inventory.patient') ?>', '<?= lang('Inventory.treatment') ?>', '<?= lang('Inventory.itemsUsed') ?>', '<?= lang('Inventory.totalCost') ?>', '<?= lang('Inventory.recordedBy') ?>'];
        const tableData = data.map(row => {
            return row.slice(0, 6).map(cell => { // Exclude actions column
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
                0: { cellWidth: 25 }, // Date
                1: { cellWidth: 35 }, // Patient
                2: { cellWidth: 30 }, // Treatment
                3: { cellWidth: 40 }, // Items Used
                4: { cellWidth: 25 }, // Total Cost
                5: { cellWidth: 30 }, // Recorded By
            },
            margin: { left: 20, right: 20 },
        });

        // Save the PDF
        const fileName = `usage_history_report_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(fileName);

    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('<?= lang('Inventory.errorGeneratingPDF') ?>');
    }
}

function printUsageHistoryTable() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');

    if (!printWindow) {
        alert('Please allow popups for this website');
        return;
    }

    // Get table data
    const table = document.getElementById('usageHistoryTable');
    const tableClone = table.cloneNode(true);

    // Set up the print document
    const doc = printWindow.document;
    doc.open();
    doc.write('<!DOCTYPE html><html><head><title><?= lang("Inventory.usageHistoryReport") ?></title></head><body></body></html>');
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
    h1.textContent = '<?= lang("Inventory.usageHistoryReport") ?>';
    body.appendChild(h1);

    const pDate = doc.createElement('p');
    const dateStrong = doc.createElement('strong');
    dateStrong.textContent = '<?= lang("Inventory.generatedOn") ?>: ';
    pDate.appendChild(dateStrong);
    pDate.appendChild(doc.createTextNode(new Date().toLocaleDateString()));
    body.appendChild(pDate);

    const pTotal = doc.createElement('p');
    const totalStrong = doc.createElement('strong');
    totalStrong.textContent = '<?= lang("Inventory.totalRecordsCount") ?>: ';
    pTotal.appendChild(totalStrong);
    const totalText = usageHistoryTable ? usageHistoryTable.page.info().recordsTotal : 'N/A';
    pTotal.appendChild(doc.createTextNode(totalText));
    body.appendChild(pTotal);

    // Append cloned table
    body.appendChild(tableClone);

    // Wait for content to load (styles), then print
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 500);
}

function exportUsageHistoryToCSV() {
    if (!usageHistoryTable) {
        alert('<?= lang('Inventory.tableNotReady') ?>');
        return;
    }

    try {
        // Get all data from the table
        const data = usageHistoryTable.data().toArray();

        // Create CSV headers
        const headers = ['<?= lang('Inventory.date') ?>', '<?= lang('Inventory.patient') ?>', '<?= lang('Inventory.treatment') ?>', '<?= lang('Inventory.itemsUsed') ?>', '<?= lang('Inventory.totalCost') ?>', '<?= lang('Inventory.recordedBy') ?>'];

        // Create CSV content
        let csvContent = headers.join(',') + '\n';

        data.forEach(row => {
            // Clean the data for CSV (remove HTML tags, quotes, etc.)
            const cleanRow = row.slice(0, 6).map(cell => { // Exclude actions column
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
        link.setAttribute('download', `usage_history_${new Date().toISOString().split('T')[0]}.csv`);
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
