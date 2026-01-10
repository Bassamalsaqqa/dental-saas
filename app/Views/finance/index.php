<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

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
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #f3f4f6 !important;
    border-color: #9ca3af !important;
    color: #1f2937 !important;
}

.dataTables_paginate .paginate_button.current {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: white !important;
}

.dataTables_paginate .paginate_button.disabled {
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
}

.dataTables_processing {
    background-color: rgba(255, 255, 255, 0.9) !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    padding: 20px !important;
    text-align: center !important;
}

/* Ensure proper spacing and layout */
.dataTables_wrapper .dataTables_length {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-right: auto !important;
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
    margin-right: auto !important;
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
</style>

<!-- Enhanced Finance Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-green-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Simplified Page Header -->
    <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                            <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-dollar-sign text-xl"></i>
                                </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Finance Management</h1>
                            <p class="text-gray-600">Manage financial transactions and billing</p>
                            </div>
                            </div>
                    <div class="flex items-center space-x-3">
                        <?php if (has_permission('finance', 'create')): ?>
                            <a href="<?= base_url('finance/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-semibold rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                New Transaction
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Reports
                        </a>
                        </div>
                    </div>
                                        </div>
                    </div>
                    
        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                <!-- Total Revenue Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-dollar-sign text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-green-100 to-emerald-100 px-3 py-1.5 rounded-full border border-green-200">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-green-600 text-xs"></i>
                                <span class="text-green-700 font-bold text-xs">+18%</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Revenue</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-green-900" id="totalRevenue">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">vs last month</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/20 to-orange-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/10 group-hover:shadow-yellow-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-yellow-100 to-orange-100 px-4 py-2 rounded-full border border-yellow-200">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-exclamation text-yellow-600 text-sm"></i>
                                <span class="text-yellow-700 font-bold text-sm">Pending</span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Pending Payments</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-yellow-900" id="pendingPayments">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-yellow-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Awaiting payment</p>
                        </div>
                    </div>
                </div>

                <!-- Monthly Income Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-calendar-day text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-cyan-100 px-4 py-2 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-calendar text-blue-600 text-sm"></i>
                                <span class="text-blue-700 font-bold text-sm">This Month</span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Monthly Income</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-blue-900" id="monthlyIncome">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-blue-200 to-cyan-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= formatDate(date('Y-m-d')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Invoices Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/20 to-pink-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-red-500/10 group-hover:shadow-red-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-red-500 to-pink-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-file-invoice text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-red-100 to-pink-100 px-4 py-2 rounded-full border border-red-200">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                <span class="text-red-700 font-bold text-sm">Overdue</span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Outstanding Invoices</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-red-900" id="outstandingInvoices">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-red-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-5/6 h-full bg-gradient-to-r from-red-500 to-pink-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Need attention</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Standardized Finance Table -->
        <div class="mb-8">
            <!-- Table View -->
            <div id="tableView" class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Financial Transactions</h3>
                                </div>
                                <div class="flex items-center space-x-3">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                PDF
                            </button>
                            <button onclick="printTransactions()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
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
                            <table id="transactionsTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-receipt text-green-600"></i>
                                            <span>Transaction ID</span>
                            </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-green-600"></i>
                                            <span>Patient</span>
                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-tag text-green-600"></i>
                                            <span>Type</span>
                    </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-green-600"></i>
                                            <span>Amount</span>
                                </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle text-green-600"></i>
                                            <span>Status</span>
                            </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-green-600"></i>
                                            <span>Date</span>
                            </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-green-600"></i>
                                            <span>Actions</span>
                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be loaded via DataTables -->
                                    <tr id="loadingRow">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            <div class="flex items-center justify-center space-x-2">
                                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-green-600"></div>
                                                <span>Loading transactions...</span>
                                </div>
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                            </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
// Global variables
let transactionsTable;

// Client-side currency formatting function
function formatCurrency(amount) {
    // Get currency symbol and position from server-side settings
    const currencySymbol = '<?= $currency_symbol ?>';
    const currencyPosition = '<?= $currency_position ?>';
    
    const formattedAmount = parseFloat(amount).toLocaleString('en-US', {
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

// Client-side abbreviated currency formatting function
function formatCurrencyAbbreviated(amount) {
    const currencySymbol = '<?= $currency_symbol ?>';
    const currencyPosition = '<?= $currency_position ?>';
    
    if (typeof amount !== 'number') {
        amount = parseFloat(amount) || 0;
    }
    
    let formattedAmount;
    if (amount >= 1000000) {
        formattedAmount = (amount / 1000000).toFixed(1) + 'M';
    } else if (amount >= 1000) {
        formattedAmount = (amount / 1000).toFixed(1) + 'k';
    } else {
        formattedAmount = Math.round(amount).toString();
    }
    
    if (currencyPosition === 'after') {
        return formattedAmount + ' ' + currencySymbol;
    } else {
        return currencySymbol + formattedAmount;
    }
}

$(document).ready(function() {
    // Initialize DataTable with server-side processing
    transactionsTable = $('#transactionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('finance/getFinancesData') ?>',
            type: 'POST',
            data: function(d) {
                // Add CSRF token to body
                d[window.csrfConfig.name] = window.getCsrfToken();
                // Add any additional filters here if needed
                return d;
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            complete: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
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
                
                // Refresh token on error too
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
                    errorMessage = xhr.responseText || 'Unknown error';
                }
                
                // Show error message
                const tbody = document.getElementById('transactionsTableBody');
                if (tbody) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.setAttribute('colspan', '6');
                    td.className = 'px-6 py-4 text-center text-red-500';
                    
                    const flexDiv = document.createElement('div');
                    flexDiv.className = 'flex items-center justify-center space-x-2';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-exclamation-triangle';
                    
                    const span = document.createElement('span');
                    span.textContent = errorMessage;
                    
                    flexDiv.appendChild(icon);
                    flexDiv.appendChild(span);
                    td.appendChild(flexDiv);
                    tr.appendChild(td);
                    
                    tbody.replaceChildren(tr);
                }
            }
        },
        columns: [
            { 
                data: 'transaction_id',
                name: 'transaction_id',
                render: function(data, type, row) {
                    return `
        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500">Transaction ID</div>
                            </div>
        </div>
    `;
                }
            },
            { 
                data: 'patient_name',
                name: 'patient_name',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-green-600 text-sm"></i>
                        </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500">${row.patient_id || 'No ID'}</div>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'transaction_type',
                name: 'transaction_type',
                render: function(data, type, row) {
                    const typeColors = {
                        'payment': 'bg-green-100 text-green-800',
                        'invoice': 'bg-blue-100 text-blue-800',
                        'refund': 'bg-red-100 text-red-800',
                        'adjustment': 'bg-yellow-100 text-yellow-800'
                    };
                    const typeIcons = {
                        'payment': 'fas fa-credit-card',
                        'invoice': 'fas fa-file-invoice',
                        'refund': 'fas fa-undo',
                        'adjustment': 'fas fa-adjust'
                    };
                    const colorClass = typeColors[data] || 'bg-gray-100 text-gray-800';
                    const iconClass = typeIcons[data] || 'fas fa-tag';
                    
                    return `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            <i class="${iconClass} mr-1"></i>
                            ${data.charAt(0).toUpperCase() + data.slice(1)}
                        </span>
                    `;
                }
            },
            { 
                data: 'amount',
                name: 'amount',
                render: function(data, type, row) {
                    const isPositive = data >= 0;
                    const colorClass = isPositive ? 'text-green-600' : 'text-red-600';
                    const iconClass = isPositive ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
                    
                    return `
                        <div class="flex items-center space-x-2">
                            <i class="${iconClass} ${colorClass} text-xs"></i>
                            <span class="text-sm font-medium ${colorClass}">${row.amount_formatted || formatCurrency(Math.abs(data))}</span>
                        </div>
                    `;
                }
            },
            { 
                data: 'payment_status',
                name: 'payment_status',
                render: function(data, type, row) {
                    const statusColors = {
                        'paid': 'bg-green-100 text-green-800',
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'overdue': 'bg-red-100 text-red-800',
                        'cancelled': 'bg-gray-100 text-gray-800'
                    };
                    const statusIcons = {
                        'paid': 'fas fa-check-circle',
                        'pending': 'fas fa-clock',
                        'overdue': 'fas fa-exclamation-triangle',
                        'cancelled': 'fas fa-times-circle'
                    };
                    const colorClass = statusColors[data] || 'bg-gray-100 text-gray-800';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    
                    return `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            <i class="${iconClass} mr-1"></i>
                            ${data.charAt(0).toUpperCase() + data.slice(1)}
                    </span>
                    `;
                }
            },
            { 
                data: 'created_at',
                name: 'created_at',
                render: function(data, type, row) {
                    return `
                    <div class="flex items-center space-x-2">
                            <i class="fas fa-calendar text-green-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${row.created_at_formatted || data}</span>
                    </div>
    `;
                }
            },
            { 
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = '<div class="flex items-center space-x-2">';
                    
                    // View Invoice button
                    if (permissions.finance.view) {
                        actions += `
                            <a href="<?= base_url('finance') ?>/${data}/invoice" 
                               class="p-2 text-green-600 hover:text-green-700 rounded-md hover:bg-green-50 transition-colors duration-200" title="View Invoice">
                                <i class="fas fa-file-invoice"></i>
                            </a>`;
                    }
                    
                    // Edit button
                    if (permissions.finance.edit) {
                        actions += `
                            <a href="<?= base_url('finance') ?>/${data}/edit" 
                               class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="Edit Transaction">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }
                    
                    // Delete button
                    if (permissions.finance.delete) {
                        actions += `
                            <button onclick="deleteTransaction(${data})" 
                                    class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200" title="Delete Transaction">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }
                    
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        dom: 'B<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                className: 'btn-pdf',
                title: 'Financial Transactions Report',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Date, Patient, Type, Amount, Status, Actions
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i>Print',
                className: 'btn-print',
                title: 'Financial Transactions Report',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Date, Patient, Type, Amount, Status, Actions
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i>CSV',
                className: 'btn-csv',
                title: 'Financial Transactions Report',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Date, Patient, Type, Amount, Status, Actions
                }
            }
        ],
        language: {
            processing: "Loading transactions...",
            lengthMenu: "_MENU_",
            zeroRecords: "No transactions found",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            infoEmpty: "No transactions available",
            infoFiltered: "(filtered from _MAX_ total transactions)",
            search: "",
            searchPlaceholder: "Search transactions...",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        drawCallback: function(settings) {
            // Re-initialize tooltips if the function exists
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            // Apply custom styling after each draw
            applyDataTablesStyling();
        }
    });

    // Apply custom DataTables styling
    function applyDataTablesStyling() {
        // Style length selector
        $('.dataTables_length select').addClass('focus:ring-green-500 focus:border-green-500');
        
        // Style search input
        $('.dataTables_filter input').addClass('focus:ring-green-500 focus:border-green-500');
        
        // Style info text
        $('.dataTables_info').addClass('text-gray-600');
        
        // Style pagination buttons
        $('.dataTables_paginate .paginate_button').each(function() {
            if ($(this).hasClass('current')) {
                $(this).addClass('bg-green-600 border-green-600 text-white');
        } else {
                $(this).addClass('hover:bg-green-50 hover:border-green-300 hover:text-green-700');
            }
        });
        
        // Style processing indicator
        $('.dataTables_processing').addClass('border-green-200 text-green-700');
        
        // Style labels
        $('.dataTables_length label, .dataTables_filter label').addClass('text-gray-700 font-semibold');
        
        // Style icons
        $('.dataTables_length i, .dataTables_filter i').addClass('text-green-600');
    }

    // Load statistics
    loadFinanceStatistics();

    // Search functionality
    $('#searchInput').on('keyup', function() {
        transactionsTable.search(this.value).draw();
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        transactionsTable.draw();
        });
    });

// Load finance statistics
function loadFinanceStatistics() {
    $.ajax({
        url: '<?= base_url('finance/stats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalRevenue').text(data.total_revenue_formatted ? formatCurrencyAbbreviated(data.total_revenue) : formatCurrencyAbbreviated(0));
            $('#pendingPayments').text(data.pending_payments_formatted ? formatCurrencyAbbreviated(data.pending_payments) : formatCurrencyAbbreviated(0));
            $('#monthlyIncome').text(data.monthly_revenue_formatted ? formatCurrencyAbbreviated(data.monthly_revenue) : formatCurrencyAbbreviated(0));
            $('#outstandingInvoices').text(data.outstanding_invoices || 0);
        },
        error: function() {
            $('#totalRevenue').text(formatCurrencyAbbreviated(0));
            $('#pendingPayments').text(formatCurrencyAbbreviated(0));
            $('#monthlyIncome').text(formatCurrencyAbbreviated(0));
            $('#outstandingInvoices').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (transactionsTable) {
        transactionsTable.button('.btn-pdf').trigger();
    }
}

function printTransactions() {
    if (transactionsTable) {
        transactionsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (transactionsTable) {
        transactionsTable.button('.btn-csv').trigger();
    }
}

// Delete transaction function
function deleteTransaction(transactionId) {
    confirmDelete('<?= base_url('finance') ?>/' + transactionId, 'Are you sure you want to delete this transaction? This action cannot be undone.');
}

// Permissions object (you'll need to populate this from your backend)
var permissions = {
    finance: {
        view: true,
        edit: true,
        delete: true,
        create: true
    }
};
</script>

<?= $this->endSection() ?>
