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

<!-- Enhanced Low Stock Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-red-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-orange-400/20 to-red-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-red-400/20 to-orange-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-orange-400/10 to-red-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= lang('Inventory.lowStockItems') ?></h1>
                            <p class="text-gray-600">Items requiring immediate attention and restocking</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <?= lang('Inventory.backToInventory') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-orange-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium"><?= lang('Inventory.lowStock') ?></p>
                            <p class="text-2xl font-bold text-gray-900"><?= count($items) ?></p>
                        </div>
                    </div>
                </div>

                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-red-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-times-circle text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Critical Items</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $criticalCount = 0;
                                foreach ($items as $item) {
                                    if ($item['quantity'] == 0) {
                                        $criticalCount++;
                                    }
                                }
                                echo $criticalCount;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-yellow-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-clock text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Warning Items</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?= count($items) - $criticalCount ?>
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
                            <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Inventory.lowStockItems') ?></h3>
                        </div>
                        <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-600">
                                <span class="total-items-count">0</span> <?= lang('Inventory.items') ?>
                            </div>
                            <!-- Export Buttons -->
                            <button onclick="exportLowStockToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                <?= lang('Inventory.exportToPDF') ?>
                            </button>
                            <button onclick="printLowStockTable()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                <?= lang('Inventory.exportToPrint') ?>
                            </button>
                            <button onclick="exportLowStockToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                <?= lang('Inventory.exportToCSV') ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Table with Advanced Styling -->
                <div class="overflow-x-auto">
                    <div class="dataTables-wrapper">
                        <table id="lowStockTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-hashtag text-orange-600"></i>
                                            <span><?= lang('Inventory.id') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-box text-orange-600"></i>
                                            <span><?= lang('Inventory.itemName') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-tags text-orange-600"></i>
                                            <span><?= lang('Inventory.category') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-layer-group text-orange-600"></i>
                                            <span><?= lang('Inventory.quantity') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-exclamation-triangle text-orange-600"></i>
                                            <span>Min Required</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-orange-600"></i>
                                            <span><?= lang('Inventory.unitPrice') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-truck text-orange-600"></i>
                                            <span><?= lang('Inventory.supplier') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-orange-600"></i>
                                            <span><?= lang('Inventory.expiryDate') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-orange-600"></i>
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

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="<?= base_url('inventory/create') ?>" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-plus mr-2"></i>Add New Item
            </a>
            
            <a href="<?= base_url('inventory') ?>" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-list mr-2"></i>View All Items
            </a>
        </div>
    </div>
</div>

<script>
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
    
    .print\\:bg-white {
        background: white !important;
    }
    
    .print\\:text-black {
        color: black !important;
    }
    
    .print\\:border-0 {
        border: 0 !important;
    }
    
    .print\\:rounded-none {
        border-radius: 0 !important;
    }
    
    .print\\:shadow-none {
        box-shadow: none !important;
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
    
    .print\\:bg-white {
        background: white !important;
    }
    
    .print\\:text-gray-600 {
        color: #4b5563 !important;
    }
    
    .print\\:bg-gray-100 {
        background: #f3f4f6 !important;
    }
    
    .print\\:border-gray-300 {
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
                    td.className = 'text-center py-8 text-gray-500';
                    
                    const flexDiv = document.createElement('div');
                    flexDiv.className = 'flex flex-col items-center space-y-2';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-exclamation-triangle text-4xl text-yellow-500';
                    
                    const p1 = document.createElement('p');
                    p1.className = 'text-lg font-medium';
                    p1.textContent = 'Unable to load low stock data';
                    
                    const p2 = document.createElement('p');
                    p2.className = 'text-sm';
                    p2.textContent = 'Please check your connection and try again.';
                    
                    const retryBtn = document.createElement('button');
                    retryBtn.className = 'mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700';
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
        dom: '<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
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
            $('[data-toggle="tooltip"]').tooltip();
            
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
