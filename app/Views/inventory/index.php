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

<!-- Enhanced Inventory Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-teal-50 to-blue-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-teal-400/20 to-blue-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-teal-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-blue-400/10 to-teal-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-box text-xl"></i>
                        </div>
                <div>
                            <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
                    <p class="text-gray-600">Manage your dental supplies and equipment inventory</p>
                </div>
                    </div>
                    <div class="flex items-center space-x-3">
                    <?php if (has_permission('inventory', 'create')): ?>
                            <a href="<?= base_url('inventory/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-500 to-blue-600 text-white text-sm font-semibold rounded-lg hover:from-teal-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Add New Item
                        </a>
                    <?php endif; ?>
                        <a href="<?= base_url('inventory/usage-history') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-history mr-2"></i>
                            Usage History
                    </a>
                        <a href="<?= base_url('inventory/low-stock') ?>" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition-all duration-200 shadow-lg hover:shadow-xl border border-red-500">
                            <i class="fas fa-exclamation-triangle mr-2 text-white"></i>
                            <span class="text-white font-bold">Low Stock Alert</span>
                    </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Success</h3>
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
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error</h3>
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
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error Loading Inventory</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= $error ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-teal-500 to-teal-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-boxes text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Total Items</p>
                                <p class="text-3xl font-bold text-gray-900 total-items-count"><?= $total_items ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">In Stock</p>
                                <p class="text-3xl font-bold text-gray-900 active-items-count"><?= $in_stock_items ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-exclamation-triangle text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Low Stock</p>
                                <p class="text-3xl font-bold text-gray-900 low-stock-count"><?= $low_stock_items ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-red-500 to-red-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-times-circle text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Out of Stock</p>
                                <p class="text-3xl font-bold text-gray-900 out-of-stock-count"><?= $out_of_stock_items ?? 0 ?></p>
                            </div>
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
                            <div class="w-2 h-2 bg-teal-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Inventory Items</h3>
                    </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-sm text-gray-600">
                                <span class="total-items-count">0</span> items
                    </div>
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                PDF
                        </button>
                            <button onclick="printInventory()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                Print
                        </button>
                            <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                CSV
                        </button>
                    </div>
                </div>
            </div>

                <!-- Enhanced Table with Advanced Styling -->
            <div class="overflow-x-auto">
                    <div class="dataTables-wrapper">
                        <table id="inventoryTable" class="w-full min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-hashtag text-teal-600"></i>
                                            <span>ID</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-box text-teal-600"></i>
                                            <span>Item</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-tags text-teal-600"></i>
                                            <span>Category</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-layer-group text-teal-600"></i>
                                            <span>Quantity</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-teal-600"></i>
                                            <span>Unit Price</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-truck text-teal-600"></i>
                                            <span>Supplier</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-teal-600"></i>
                                            <span>Expiry Date</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle text-teal-600"></i>
                                            <span>Status</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-teal-600"></i>
                                            <span>Actions</span>
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
                
                const category = categoryCell.querySelector('.bg-blue-100')?.textContent?.toLowerCase() || '';
                
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
                title.textContent = 'No items found';
                
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
                alert('Error: ' + data.message);
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
                showNotification('Item deleted successfully!', 'success');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
                // Restore button state
                deleteBtn.replaceChildren(...deleteBtn._originalChildren.map(node => node.cloneNode(true)));
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the item.', 'error');
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
        'bg-blue-500 text-white'
    }`;
    
    const container = document.createElement('div');
    container.className = 'flex items-center space-x-2';
    
    const icon = document.createElement('i');
    icon.className = `fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'}`;
    container.appendChild(icon);
    
    const msgSpan = document.createElement('span');
    msgSpan.textContent = message;
    container.appendChild(msgSpan);
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ml-2 text-white hover:text-gray-200';
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
            console.error('Failed to load inventory statistics');
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
    
    console.log('Initializing DataTable for #inventoryTable');
    console.log('Table element:', $('#inventoryTable'));
    console.log('Table element length:', $('#inventoryTable').length);
    
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
                
                console.log('Received DataTables response:', json);
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
                    console.log('No inventory items found');
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
                
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
                
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
                const tbody = document.querySelector('#inventoryTable tbody');
                tbody.replaceChildren(); // Clear existing content
                
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 9;
                td.className = 'text-center py-8 text-gray-500';
                
                const container = document.createElement('div');
                container.className = 'flex flex-col items-center space-y-2';
                
                const icon = document.createElement('i');
                icon.className = 'fas fa-exclamation-triangle text-4xl text-yellow-500';
                
                const title = document.createElement('p');
                title.className = 'text-lg font-medium';
                title.textContent = 'Unable to load inventory data';
                
                const msg = document.createElement('p');
                msg.className = 'text-sm';
                msg.textContent = 'Please check your connection and try again.';
                
                const btn = document.createElement('button');
                btn.className = 'mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700';
                btn.onclick = () => location.reload();
                
                const btnIcon = document.createElement('i');
                btnIcon.className = 'fas fa-refresh mr-2';
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
                    
                    const container = document.createElement('div');
                    container.className = 'flex items-center space-x-3';
                    
                    const iconBox = document.createElement('div');
                    iconBox.className = 'w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-box text-blue-600 text-sm';
                    iconBox.appendChild(icon);
                    
                    const textBox = document.createElement('div');
                    
                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'text-sm font-medium text-gray-900';
                    nameDiv.textContent = cellData;
                    
                    const idDiv = document.createElement('div');
                    idDiv.className = 'text-sm text-gray-500';
                    idDiv.textContent = 'ID: ' + rowData[0];
                    
                    textBox.appendChild(nameDiv);
                    textBox.appendChild(idDiv);
                    
                    container.appendChild(iconBox);
                    container.appendChild(textBox);
                    
                    td.appendChild(container);
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
                    span.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
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
                        span.className = 'text-gray-400';
                        span.textContent = 'N/A';
                        td.appendChild(span);
                        return;
                    }
                    
                    const container = document.createElement('div');
                    container.className = 'flex items-center space-x-2';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-calendar text-orange-600 text-xs';
                    
                    const dateText = document.createElement('span');
                    dateText.className = 'text-sm text-gray-900';
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
            emptyTable: "No inventory items found",
            zeroRecords: "No matching inventory items found",
            lengthMenu: "_MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ inventory items",
            infoEmpty: "No inventory items available",
            infoFiltered: "(filtered from _MAX_ total inventory items)",
            search: "",
            searchPlaceholder: "Search inventory items...",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        responsive: true,
        initComplete: function(settings, json) {
            console.log('DataTable initialization complete:', json);
            console.log('Settings:', settings);
            console.log('JSON response:', json);
            console.log('Table rows:', $('#inventoryTable tbody tr').length);
            
            // Hide all loading indicators
            hideLoadingIndicators();
        },
        drawCallback: function(settings) {
            console.log('DataTable draw callback triggered');
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
            console.error('Failed to load inventory statistics');
        }
    });
}

// Export Functions
function exportToPDF() {
    console.log('Export PDF clicked');
    generatePDF();
}

function printInventory() {
    console.log('Print clicked');
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
        doc.text(`Total Items: ${inventoryTable.page.info().recordsTotal}`, 20, 45);
        
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
        alert('Error generating PDF. Please try again.');
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
    totalStrong.textContent = 'Total Items: ';
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
    console.log('Export CSV clicked');
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
        alert('Error exporting CSV. Please try again.');
    }
}

// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    // loadInventoryStats is already called in $(document).ready
});
</script>
<?= $this->endSection() ?>
