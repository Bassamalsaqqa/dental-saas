<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Finance.' . $key, $params);
$currentUri = service('request')->getUri();
$returnToContext = ltrim($currentUri->getPath(), '/');
$currentQuery = $currentUri->getQuery();
if ($currentQuery !== '') {
    $returnToContext .= '?' . $currentQuery;
}
$returnToContextEncoded = urlencode($returnToContext);
?>

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

@media (max-width: 767.98px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        width: 100% !important;
        margin: 0 !important;
        justify-content: space-between !important;
    }

    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_length label {
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        width: 100% !important;
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        flex-wrap: wrap !important;
        gap: 8px !important;
    }
}
</style>

<!-- Enhanced <?= lang('Finance.financeManagement') ?> with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-green-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-3 sm:px-4 py-4 sm:py-6">
        <!-- Simplified Page Header -->
    <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                        <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-center">
                        <div class="flex items-start gap-3 sm:gap-4 sm:items-center">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-dollar-sign text-xl"></i>
                                </div>
                        <div>
                            <h1 class="text-xl sm:text-3xl font-bold text-gray-900"><?= lang('Finance.financeManagement') ?></h1>
                            <p class="text-gray-600"><?= esc($tr('financeManagementShortHelp')) ?></p>
                            </div>
                            </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:justify-end">
                        <?php if (has_permission('finance', 'create')): ?>
                            <a href="<?= base_url('finance/income/create?return_to=' . $returnToContextEncoded) ?>" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-semibold leading-none rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-circle-plus mr-2"></i>
                                <?= esc($tr('addIncome')) ?>
                            </a>
                            <a href="<?= base_url('finance/expenses/create?return_to=' . urlencode('finance')) ?>" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2.5 bg-gradient-to-r from-rose-500 to-orange-500 text-white text-sm font-semibold leading-none rounded-lg hover:from-rose-600 hover:to-orange-600 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-receipt mr-2"></i>
                                <?= esc($tr('newExpense')) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($can_access_finance_reports)): ?>
                            <a href="<?= base_url('finance/reports') ?>" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold leading-none rounded-lg hover:bg-gray-200 transition-all duration-200">
                                <i class="fas fa-chart-bar mr-2"></i>
                                <?= esc($tr('reports')) ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('finance/transfers') ?>" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2.5 bg-emerald-100 text-emerald-700 text-sm font-semibold leading-none rounded-lg hover:bg-emerald-200 transition-all duration-200">
                            <i class="fas fa-right-left mr-2"></i>
                            <?= esc($tr('treasuryTransfers')) ?>
                        </a>
                        <a href="<?= base_url('finance/payouts') ?>" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2.5 bg-indigo-100 text-indigo-700 text-sm font-semibold leading-none rounded-lg hover:bg-indigo-200 transition-all duration-200">
                            <i class="fas fa-hand-holding-dollar mr-2"></i>
                            <?= esc($tr('providerPayoutsNav')) ?>
                        </a>
                        </div>
                    </div>
                                        </div>
                    </div>
                    
        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 sm:gap-6 items-stretch">
                <!-- <?= lang('Finance.grossCollections') ?> Card -->
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
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-green-700 font-bold text-xs"><?= lang('Finance.cashBasisShort') ?></span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.grossCollections') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-green-900" id="totalRevenue">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc($tr('grossCollectionsHelp')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- <?= lang('Finance.providerPaidCash') ?> Card -->
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
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <span class="text-yellow-700 font-bold text-sm"><?= lang('Finance.cashOutShort') ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.providerPaidCash') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-yellow-900" id="providerPaidCash">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-yellow-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.providerPaidCashHelp') ?></p>
                        </div>
                    </div>
                </div>

                <!-- <?= lang('Finance.netClinicCash') ?> Card -->
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
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span class="text-blue-700 font-bold text-sm"><?= lang('Finance.snapshotShort') ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.netClinicCash') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-blue-900" id="netClinicCash">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-blue-200 to-cyan-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.netClinicCashHelp') ?></p>
                        </div>
                    </div>
                </div>

                <!-- <?= lang('Finance.outstandingInvoices') ?> Card -->
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
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <span class="text-red-700 font-bold text-sm"><?= lang('Finance.currentShort') ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.openInvoices') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-red-900" id="outstandingInvoices">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-red-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-5/6 h-full bg-gradient-to-r from-red-500 to-pink-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium" id="outstandingInvoicesBalance"><?= esc($tr('openInvoicesHelp')) ?></p>
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
                            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Finance.financialTransactions') ?></h3>
                                </div>
                                <div class="flex items-center space-x-3">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                <?= esc($tr('exportPdf')) ?>
                            </button>
                            <button onclick="printTransactions()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                <?= esc($tr('print')) ?>
                            </button>
                            <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                <?= esc($tr('exportCsv')) ?>
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
                                            <span><?= lang('Finance.transactionId') ?></span>
                            </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-green-600"></i>
                                            <span><?= lang('Finance.patient') ?></span>
                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-tag text-green-600"></i>
                                            <span><?= lang('Finance.type') ?></span>
                    </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-green-600"></i>
                                            <span><?= lang('Finance.total') ?></span>
                                </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle text-green-600"></i>
                                            <span><?= lang('Finance.status') ?></span>
                            </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-green-600"></i>
                                            <span><?= lang('Finance.date') ?></span>
                            </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-green-600"></i>
                                            <span><?= lang('Finance.actions') ?></span>
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
                                                <span><?= lang('Finance.loadingTransactions') ?></span>
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

<?php
$financeConfig = [
    'urls' => [
        'data' => base_url('finance/getFinancesData'),
        'base' => base_url('finance'),
        'stats' => base_url('finance/stats'),
    ],
    'i18n' => [
        'transactionId' => lang('Finance.transactionId'),
        'financialTransactionsReport' => lang('Finance.financialTransactions') . ' ' . lang('Finance.report'),
        'loadingTransactions' => lang('Finance.loadingTransactions'),
        'confirmDeleteTransaction' => lang('Finance.confirmDeleteTransaction'),
        'noId' => '-',
        'expensePayee' => lang('Finance.counterparty'),
        'transactionTypes' => [
            'payment' => lang('Finance.payment'),
            'invoice' => lang('Finance.invoice'),
            'refund' => lang('Finance.refund'),
            'adjustment' => lang('Finance.adjustment'),
            'expense' => lang('Finance.expenseTransaction'),
        ],
        'paymentStatuses' => [
            'paid' => lang('Finance.paid'),
            'pending' => lang('Finance.pending'),
            'partial' => lang('Finance.partial'),
            'overdue' => lang('Finance.overdue'),
            'cancelled' => lang('Finance.cancelled'),
        ],
        'paymentMethods' => [
            'cash' => lang('Finance.cash'),
            'card' => lang('Finance.card'),
            'bank_transfer' => lang('Finance.bankTransfer'),
            'check' => lang('Finance.check'),
            'insurance' => lang('Finance.insurance'),
            'other' => lang('Finance.other'),
        ],
        'actions' => [
            'viewTransaction' => lang('Finance.viewTransaction'),
            'viewInvoice' => lang('Finance.invoice'),
            'addPayment' => lang('Finance.postSettlement'),
            'editTransaction' => lang('Finance.editTransaction'),
            'deleteTransaction' => lang('Finance.delete'),
        ],
        'dataTable' => [
            'processing' => lang('Finance.loadingTransactions'),
            'search' => lang('Finance.search'),
            'lengthMenu' => lang('Finance.show') . ' _MENU_',
            'info' => lang('Finance.showing') . ' _START_ ' . lang('Finance.to') . ' _END_ ' . lang('Finance.of') . ' _TOTAL_ ' . lang('Finance.financialTransactions'),
            'infoEmpty' => lang('Finance.showing') . ' 0 ' . lang('Finance.to') . ' 0 ' . lang('Finance.of') . ' 0 ' . lang('Finance.financialTransactions'),
            'zeroRecords' => lang('Finance.noDataAvailable'),
            'emptyTable' => lang('Finance.noDataAvailable'),
            'paginate' => [
                'previous' => lang('Finance.previous'),
                'next' => lang('Finance.next'),
            ],
        ],
    ],
    'currency' => [
        'symbol' => (string) $currency_symbol,
        'position' => (string) $currency_position,
    ],
    'permissions' => [
        'finance' => [
            'view' => (bool) has_permission('finance', 'view'),
            'edit' => (bool) has_permission('finance', 'edit'),
            'delete' => (bool) has_permission('finance', 'delete'),
            'create' => (bool) has_permission('finance', 'create'),
        ],
    ],
];
?>
<script id="finance-page-config" type="application/json"><?= json_encode($financeConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="<?= base_url('assets/js/finance-index.js') ?>?v=<?= time() ?>"></script>

<?= $this->endSection() ?>
