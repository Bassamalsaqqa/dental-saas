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
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
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
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
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
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
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

<!-- Enhanced Usage History Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-purple-400/20 to-indigo-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-purple-400/20 to-blue-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-purple-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-history text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= lang('Inventory.usageHistory') ?></h1>
                            <p class="text-gray-600"><?= lang('Inventory.usageHistorySubtitle') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <?= lang('Inventory.backToInventory') ?>
                        </a>
                        <a href="<?= base_url('inventory/usage') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-plus mr-2"></i>
                            <?= lang('Inventory.recordUsage') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-list text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium"><?= lang('Inventory.totalRecords') ?></p>
                            <p class="text-2xl font-bold text-gray-900"><?= count($usage_history) ?></p>
                        </div>
                    </div>
                </div>

                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-green-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-dollar-sign text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium"><?= lang('Inventory.totalValue') ?></p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?= formatCurrency(array_sum(array_column($usage_history, 'total_cost'))) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-purple-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-calendar text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">This Month</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $thisMonth = date('Y-m');
                                $monthlyCount = 0;
                                foreach ($usage_history as $usage) {
                                    if (strpos($usage['usage_date'], $thisMonth) === 0) {
                                        $monthlyCount++;
                                    }
                                }
                                echo $monthlyCount;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-orange-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-chart-line text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium"><?= lang('Inventory.avgCost') ?></p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?= count($usage_history) > 0 ? formatCurrency(array_sum(array_column($usage_history, 'total_cost')) / count($usage_history)) : formatCurrency(0) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Table Container -->
        <div class="mb-8">
            <div class="backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-xl shadow-gray-500/10 overflow-hidden">
                <!-- Table Header with Export Controls -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Inventory.usageHistory') ?></h3>
                        </div>
                        <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-600">
                                <span class="total-items-count">0</span> <?= lang('Inventory.records') ?>
                            </div>
                            <!-- Export Buttons -->
                            <button onclick="exportUsageToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                <?= lang('Inventory.exportToPDF') ?>
                            </button>
                            <button onclick="printUsageHistory()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                <?= lang('Inventory.exportToPrint') ?>
                            </button>
                            <button onclick="exportUsageToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                <?= lang('Inventory.exportToCSV') ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Table with Advanced Styling -->
                <div class="overflow-x-auto">
                    <div class="dataTables-wrapper">
                        <table id="usageHistoryTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-purple-600"></i>
                                            <span><?= lang('Inventory.date') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-purple-600"></i>
                                            <span><?= lang('Inventory.patient') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-stethoscope text-purple-600"></i>
                                            <span><?= lang('Inventory.treatment') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-box text-purple-600"></i>
                                            <span><?= lang('Inventory.itemsUsed') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-purple-600"></i>
                                            <span><?= lang('Inventory.totalCost') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user-md text-purple-600"></i>
                                            <span><?= lang('Inventory.recordedBy') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-purple-600"></i>
                                            <span><?= lang('Inventory.actions') ?></span>
                                        </div>
                                    </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Data will be loaded via DataTables -->
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Usage Details Modal -->
<div id="usageDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Usage Details</h3>
                <button onclick="closeUsageDetails()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="usageDetailsContent">
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
    // Fetch usage details via AJAX
    fetch(`<?= base_url() ?>inventory/usage-details/${usageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const usage = data.usage;
                const items = JSON.parse(usage.items_used);
                
                let itemsHtml = '';
                items.forEach(item => {
                    itemsHtml += `
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <div>
                                <div class="font-medium text-gray-900">${item.item_name}</div>
                                <div class="text-sm text-gray-500">Quantity: ${item.quantity_used}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900">${formatCurrency(parseFloat(item.unit_cost))}</div>
                                <div class="text-sm text-gray-500">Total: ${formatCurrency(parseFloat(item.total_cost))}</div>
                            </div>
                        </div>
                    `;
                });
                
                document.getElementById('usageDetailsContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Usage Date</label>
                                <p class="text-lg font-semibold text-gray-900">${formatDate(usage.usage_date)}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Total Cost</label>
                                <p class="text-lg font-semibold text-green-600">${formatCurrency(parseFloat(usage.total_cost))}</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Items Used</label>
                            <div class="mt-2 space-y-2">
                                ${itemsHtml}
                            </div>
                        </div>
                        
                        ${usage.notes ? `
                            <div>
                                <label class="text-sm font-medium text-gray-500">Notes</label>
                                <p class="text-gray-900 mt-1">${usage.notes}</p>
                            </div>
                        ` : ''}
                        
                        <div class="text-sm text-gray-500">
                            Recorded by: ${usage.recorded_by_name} on ${formatDate(usage.created_at)}
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('usageDetailsContent').innerHTML = `
                    <div class="p-4 bg-red-50 rounded-lg">
                        <p class="text-red-600">Error loading usage details: ${data.message}</p>
                    </div>
                `;
            }
            document.getElementById('usageDetailsModal').classList.remove('hidden');
        })
        .catch(error => {
            document.getElementById('usageDetailsContent').innerHTML = `
                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-red-600">Error loading usage details: ${error.message}</p>
                </div>
            `;
            document.getElementById('usageDetailsModal').classList.remove('hidden');
        });
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
                    alert('Error: ' + json.error);
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
                $('#usageHistoryTable tbody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center space-y-2">
                                <i class="fas fa-exclamation-triangle text-4xl text-yellow-500"></i>
                                <p class="text-lg font-medium"><?= lang('Inventory.unableToLoadData') ?></p>
                                <p class="text-sm"><?= lang('Inventory.checkConnection') ?></p>
                                <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    <i class="fas fa-refresh mr-2"></i><?= lang('Inventory.retry') ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
                
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
        dom: '<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
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
    
    // Get table data
    const table = document.getElementById('usageHistoryTable');
    const tableClone = table.cloneNode(true);
    
    // Create print content
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title><?= lang('Inventory.usageHistoryReport') ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; text-align: center; margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .no-print { display: none; }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            <h1><?= lang('Inventory.usageHistoryReport') ?></h1>
            <p><strong><?= lang('Inventory.generatedOn') ?>:</strong> ${new Date().toLocaleDateString()}</p>
            <p><strong><?= lang('Inventory.totalRecordsCount') ?>:</strong> ${usageHistoryTable ? usageHistoryTable.page.info().recordsTotal : 'N/A'}</p>
            ${tableClone.outerHTML}
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Wait for content to load, then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
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
