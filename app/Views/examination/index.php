<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
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
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #f3f4f6 !important;
    color: #1f2937 !important;
    border-color: #9ca3af !important;
}

.dataTables_paginate .paginate_button.current {
    background-color: #3b82f6 !important;
    color: white !important;
    border-color: #3b82f6 !important;
}

.dataTables_paginate .paginate_button.disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
}

.dataTables_paginate .paginate_button.disabled:hover {
    background-color: white !important;
    color: #9ca3af !important;
}

.dataTables_processing {
    background-color: #3b82f6 !important;
    color: white !important;
    border-radius: 8px !important;
    border: none !important;
}

/* Table styling to match original design */
#examinationsTable {
    width: 100% !important;
    min-width: 100% !important;
}

#examinationsTable thead th {
    padding: 16px 24px !important;
    text-align: left !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
}

#examinationsTable tbody td {
    padding: 24px !important;
    white-space: nowrap !important;
}

#examinationsTable tbody tr {
    transition: all 0.3s ease !important;
}

#examinationsTable tbody tr:hover {
    background-color: #f9fafb !important;
}

/* Override DataTables default styles */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    float: none !important;
    text-align: left !important;
}

.dataTables_wrapper .dataTables_length {
    display: flex !important;
    align-items: center !important;
    margin-bottom: 0 !important;
    order: 1 !important;
}

.dataTables_wrapper .dataTables_filter {
    display: flex !important;
    align-items: center !important;
    margin-bottom: 0 !important;
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
</style>
<!-- Enhanced Examination Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-amber-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-orange-400/20 to-amber-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-orange-400/20 to-red-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-yellow-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-3 sm:px-4 py-4 sm:py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-center">
                    <div class="flex items-start sm:items-center space-x-3 sm:space-x-4">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-stethoscope text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-3xl font-bold text-gray-900"><?= esc($tr('examination_management')) ?></h1>
                            <p class="text-gray-600"><?= esc($tr('examination_management_subtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-3">
                        <?php if (has_permission('examinations', 'create')): ?>
                            <a href="<?= base_url('examination/create') ?>" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-600 text-white text-sm font-semibold rounded-lg hover:from-orange-600 hover:to-amber-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                <?= esc($tr('new_examination')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 items-stretch">
                <!-- Total Examinations Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/20 to-amber-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-orange-500/10 group-hover:shadow-orange-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-stethoscope text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-orange-100 to-amber-100 px-3 py-1.5 rounded-full border border-orange-200">
                                <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-orange-600 text-xs"></i>
                                <span class="text-orange-700 font-bold text-xs">+12%</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc($tr('card_total_examinations')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-orange-900" id="totalExaminations">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-orange-200 to-amber-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc($tr('card_vs_last_month')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Completed Today Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-green-100 to-emerald-100 px-4 py-2 rounded-full border border-green-200">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-shield-alt text-green-600 text-sm"></i>
                                <span class="text-green-700 font-bold text-sm"><?= esc($tr('card_completed')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= esc($tr('card_completed_today')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-green-900" id="completedToday">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc($tr('card_today_completed')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pending Reviews Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-orange-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-4 py-2 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-hourglass-half text-amber-600 text-sm"></i>
                                <span class="text-amber-700 font-bold text-sm"><?= esc($tr('card_pending')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= esc($tr('card_pending_reviews')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-amber-900" id="pendingReviews">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc($tr('card_awaiting_review')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Cases Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/20 to-pink-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-red-500/10 group-hover:shadow-red-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-red-500 to-pink-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-exclamation-triangle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-red-100 to-pink-100 px-4 py-2 rounded-full border border-red-200">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-bolt text-red-600 text-sm"></i>
                                <span class="text-red-700 font-bold text-sm"><?= esc($tr('card_emergency')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= esc($tr('card_emergency_cases')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-red-900" id="emergencyCases">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-red-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-5/6 h-full bg-gradient-to-r from-red-500 to-pink-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc($tr('card_urgent_attention')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Standardized Examinations Table -->
        <div class="mb-8">
            <!-- Table View -->
            <div id="tableView" class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= esc($tr('examinations_registry')) ?></h3>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                <?= esc($tr('pdf')) ?>
                            </button>
                            <button onclick="printExaminations()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                <?= esc($tr('print')) ?>
                            </button>
                            <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                <?= esc($tr('csv')) ?>
                            </button>
                        </div>
                    </div>
                </div>
            
                    <!-- Enhanced Table with Advanced Styling -->
                    <div class="overflow-x-auto">
                        <div class="dataTables-wrapper">
                            <table id="examinationsTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-stethoscope text-orange-600"></i>
                                            <span><?= esc($tr('column_examination_id')) ?></span>
                                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-orange-600"></i>
                                            <span><?= esc($tr('column_patient')) ?></span>
                                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar-alt text-orange-600"></i>
                                            <span><?= esc($tr('column_date_time')) ?></span>
                                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-tag text-orange-600"></i>
                                            <span><?= esc($tr('column_type')) ?></span>
                                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle text-orange-600"></i>
                                            <span><?= esc($tr('column_status')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-orange-600"></i>
                                            <span><?= esc($tr('column_actions')) ?></span>
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

        <!-- Grid View -->
        <div id="gridView" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (!empty($examinations)): ?>
                    <?php foreach ($examinations as $examination): ?>
                        <div class="bg-white rounded-xl border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-300 examination-card" data-examination-id="<?= $examination['id'] ?>">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                                            <i class="fas fa-stethoscope text-white text-sm"></i>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $examination['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                            ($examination['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                            ($examination['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) ?>">
                                        <?= esc($tr('status_' . $examination['status'])) ?>
                                    </span>
                                </div>
                                
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1"><?= $examination['examination_id'] ?></h3>
                                    <p class="text-sm text-gray-600"><?= $examination['first_name'] . ' ' . $examination['last_name'] ?></p>
                                    <p class="text-xs text-gray-500"><?= $examination['patient_number'] ?></p>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        <?= formatDate($examination['examination_date']) ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-clock mr-2"></i>
                                        <?= formatTime($examination['examination_date']) ?>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $examination['examination_type'] == 'emergency' ? 'bg-red-100 text-red-800' : 
                                            ($examination['examination_type'] == 'initial' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($examination['examination_type'] == 'periodic' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) ?>">
                                        <?= esc($tr('type_' . $examination['examination_type'])) ?>
                                    </span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <?php if (has_permission('examinations', 'view')): ?>
                                            <a href="<?= base_url('examination/' . $examination['id']) ?>" 
                                               class="text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="<?= esc($tr('action_view_details')) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('examinations', 'edit')): ?>
                                            <a href="<?= base_url('examination/' . $examination['id'] . '/edit') ?>" 
                                               class="text-yellow-600 hover:text-yellow-700 p-2 rounded-lg hover:bg-yellow-50 transition-colors" title="<?= esc($tr('edit_examination')) ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('examinations', 'view')): ?>
                                            <a href="<?= base_url('odontogram/' . $examination['patient_id']) ?>" 
                                               class="text-purple-600 hover:text-purple-700 p-2 rounded-lg hover:bg-purple-50 transition-colors" title="<?= esc($tr('action_view_odontogram')) ?>">
                                                <i class="fas fa-tooth"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (($examination['status'] == 'pending' || $examination['status'] == 'in_progress') && has_permission('examinations', 'edit')): ?>
                                        <button onclick="completeExamination(<?= $examination['id'] ?>)" 
                                                class="text-green-600 hover:text-green-700 p-2 rounded-lg hover:bg-green-50 transition-colors" title="<?= esc($tr('action_complete_examination')) ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full flex flex-col items-center justify-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-stethoscope text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= esc($tr('no_examinations_found')) ?></h3>
                        <p class="text-gray-500 mb-4"><?= esc($tr('start_creating_new_examination')) ?></p>
                        <?php if (has_permission('examinations', 'create')): ?>
                            <a href="<?= base_url('examination/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                <i class="fas fa-plus mr-2"></i><?= esc($tr('new_examination')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Calendar View -->
        <div id="calendarView" class="hidden">
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900"><?= esc($tr('calendar_title')) ?></h3>
                        <div class="flex items-center space-x-4">
                            <button onclick="previousMonth()" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="currentMonth" class="text-lg font-semibold text-gray-900"><?= formatDate(date('Y-m-d')) ?></span>
                            <button onclick="nextMonth()" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div id="calendarGrid" class="grid grid-cols-7 gap-1">
                        <!-- Calendar will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Examination Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-lg">
        <div class="p-8">
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
            
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= esc($tr('complete_examination')) ?></h3>
                <p class="text-gray-600"><?= esc($tr('complete_examination_subtitle')) ?></p>
            </div>
            
            <form id="completeForm">
                <?= csrf_field() ?>
                <input type="hidden" id="examinationId" name="examination_id">
                
                <div class="mb-6">
                    <label for="examination_notes" class="block text-sm font-medium text-gray-700 mb-2"><?= esc($tr('final_notes')) ?></label>
                    <textarea id="examination_notes" name="examination_notes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="4" 
                              placeholder="<?= esc($tr('final_notes_placeholder')) ?>"></textarea>
                </div>
                
                <div class="flex space-x-4">
                    <button type="button" onclick="closeCompleteModal()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors flex-1">
                        <i class="fas fa-times mr-2"></i><?= esc($tr('cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors flex-1">
                        <i class="fas fa-check mr-2"></i><?= esc($tr('complete_examination')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
// Global variables
let currentView = 'table';
let currentMonth = new Date();
let examinationsTable;

// Permission data from PHP
const permissions = {
    examinations: {
        view: <?= has_permission('examinations', 'view') ? 'true' : 'false' ?>,
        create: <?= has_permission('examinations', 'create') ? 'true' : 'false' ?>,
        edit: <?= has_permission('examinations', 'edit') ? 'true' : 'false' ?>,
        delete: <?= has_permission('examinations', 'delete') ? 'true' : 'false' ?>
    }
};
const i18n = {
    datatableShow: '<?= esc($tr('datatable_show'), 'js') ?>',
    datatableSearch: '<?= esc($tr('datatable_search'), 'js') ?>',
    errorLoadingStatistics: '<?= esc($tr('error_loading_statistics'), 'js') ?>',
    errorLoadingData: '<?= esc($tr('error_loading_data'), 'js') ?>',
    datatableErrorPrefix: '<?= esc($tr('datatable_error_prefix'), 'js') ?>',
    actionViewDetails: '<?= esc($tr('action_view_details'), 'js') ?>',
    actionEditExamination: '<?= esc($tr('action_edit_examination'), 'js') ?>',
    actionViewOdontogram: '<?= esc($tr('action_view_odontogram'), 'js') ?>',
    actionCompleteExamination: '<?= esc($tr('action_complete_examination'), 'js') ?>',
    actionMoreActions: '<?= esc($tr('action_more_actions'), 'js') ?>',
    actionPrintReport: '<?= esc($tr('action_print_report'), 'js') ?>',
    actionDuplicate: '<?= esc($tr('action_duplicate'), 'js') ?>',
    actionDelete: '<?= esc($tr('action_delete'), 'js') ?>',
    deleteExaminationConfirm: '<?= esc($tr('delete_examination_confirm'), 'js') ?>',
    errorPrefix: '<?= esc($tr('error_prefix'), 'js') ?>',
    datatableLoadingExaminations: '<?= esc($tr('datatable_loading_examinations'), 'js') ?>',
    datatableNoExaminationsFound: '<?= esc($tr('datatable_no_examinations_found'), 'js') ?>',
    datatableInfo: '<?= esc($tr('datatable_info'), 'js') ?>',
    datatableInfoEmpty: '<?= esc($tr('datatable_info_empty'), 'js') ?>',
    datatableInfoFiltered: '<?= esc($tr('datatable_info_filtered'), 'js') ?>',
    datatableSearchPlaceholder: '<?= esc($tr('datatable_search_placeholder'), 'js') ?>',
    first: '<?= esc($tr('first'), 'js') ?>',
    last: '<?= esc($tr('last'), 'js') ?>',
    next: '<?= esc($tr('next'), 'js') ?>',
    previous: '<?= esc($tr('previous'), 'js') ?>',
    examinationsReport: '<?= esc($tr('examinations_report'), 'js') ?>',
    examinationIdLabel: '<?= esc($tr('examination_id_label'), 'js') ?>',
    typeInitial: '<?= esc($tr('type_initial'), 'js') ?>',
    typePeriodic: '<?= esc($tr('type_periodic'), 'js') ?>',
    typeEmergency: '<?= esc($tr('type_emergency'), 'js') ?>',
    typeFollowUp: '<?= esc($tr('type_follow_up'), 'js') ?>',
    statusPending: '<?= esc($tr('status_pending'), 'js') ?>',
    statusInProgress: '<?= esc($tr('status_in_progress'), 'js') ?>',
    statusCompleted: '<?= esc($tr('status_completed'), 'js') ?>',
    statusCancelled: '<?= esc($tr('status_cancelled'), 'js') ?>',
    calendarDays: [
        '<?= esc($tr('calendar_day_sun'), 'js') ?>',
        '<?= esc($tr('calendar_day_mon'), 'js') ?>',
        '<?= esc($tr('calendar_day_tue'), 'js') ?>',
        '<?= esc($tr('calendar_day_wed'), 'js') ?>',
        '<?= esc($tr('calendar_day_thu'), 'js') ?>',
        '<?= esc($tr('calendar_day_fri'), 'js') ?>',
        '<?= esc($tr('calendar_day_sat'), 'js') ?>'
    ],
    locale: '<?= esc(strtolower(service('request')->getLocale()), 'js') ?>',
};

function getPatientAvatar(gender) {
    const avatarMap = {
        male: { icon: 'fas fa-person', color: 'text-blue-700' },
        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
        other: { icon: 'fas fa-user', color: 'text-slate-600' }
    };

    return avatarMap[String(gender || 'other').toLowerCase()] || avatarMap.other;
}

// Function to apply DataTables styling
function applyDataTablesStyling() {
    console.log('Applying DataTables styling...');
    
    // Style the length selector with clean design
    $('.dataTables_length select').removeClass().addClass('px-3 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500');
    
    // Style the search input with clean design
    $('.dataTables_filter input').removeClass().addClass('px-3 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500');
    
    // Style the info text
    $('.dataTables_info').removeClass().addClass('text-sm text-gray-600 flex items-center');
    
    // Style pagination buttons with clean design
    $('.dataTables_paginate .paginate_button').removeClass().addClass('px-3 py-2 mx-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500');
    $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-500 text-white border-blue-500');
    $('.dataTables_paginate .paginate_button.disabled').addClass('opacity-50 cursor-not-allowed hover:bg-white hover:text-gray-700');
    
    // Style the processing indicator
    $('.dataTables_processing').removeClass().addClass('bg-blue-500 text-white rounded-md border-0');
    
    // Add custom wrapper styling
    $('.dataTables_wrapper').addClass('relative');
    $('.dataTables_length').addClass('flex items-center space-x-3 order-1');
    $('.dataTables_filter').addClass('flex items-center space-x-3 ml-auto order-2');
    $('.dataTables_info').addClass('flex items-center order-1');
    $('.dataTables_paginate').addClass('flex items-center space-x-2 ml-auto order-2');
    
    // Force the containers to use flexbox
    $('.dataTables_wrapper > .row:first-child').addClass('flex items-center justify-between');
    $('.dataTables_wrapper > .row:last-child').addClass('flex items-center justify-between');
    
    // Add clean labels with icons
    if ($('.dataTables_length label').length === 0) {
        $('.dataTables_length').prepend(`<label class="text-sm text-gray-700 mr-3 flex items-center"><i class="fas fa-list-ul mr-2 text-gray-500"></i>${i18n.datatableShow}</label>`);
    }
    if ($('.dataTables_filter label').length === 0) {
        $('.dataTables_filter').prepend(`<label class="text-sm text-gray-700 mr-3 flex items-center"><i class="fas fa-search mr-2 text-gray-500"></i>${i18n.datatableSearch}</label>`);
    }
    
    // Add icons to pagination buttons using safe DOM construction
    const createIcon = (cls) => {
        const icon = document.createElement('i');
        icon.className = cls;
        return icon;
    };

    $('.dataTables_paginate .paginate_button.first').each(function() { this.replaceChildren(createIcon('fas fa-angle-double-left')); });
    $('.dataTables_paginate .paginate_button.previous').each(function() { this.replaceChildren(createIcon('fas fa-angle-left')); });
    $('.dataTables_paginate .paginate_button.next').each(function() { this.replaceChildren(createIcon('fas fa-angle-right')); });
    $('.dataTables_paginate .paginate_button.last').each(function() { this.replaceChildren(createIcon('fas fa-angle-double-right')); });
    
    console.log('DataTables styling applied');
}

// Load examination statistics
function loadExaminationStats() {
    fetch('<?= base_url('examination/getExaminationStats') ?>')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalExaminations').textContent = data.total_examinations || 0;
            document.getElementById('completedToday').textContent = data.today_examinations || 0;
            document.getElementById('pendingReviews').textContent = data.pending_examinations || 0;
            document.getElementById('emergencyCases').textContent = data.emergency_examinations || 0;
        })
        .catch(error => {
            console.error(i18n.errorLoadingStatistics, error);
            document.getElementById('totalExaminations').textContent = '0';
            document.getElementById('completedToday').textContent = '0';
            document.getElementById('pendingReviews').textContent = '0';
            document.getElementById('emergencyCases').textContent = '0';
        });
}

// Initialize DataTables
$(document).ready(function() {
    console.log('=== DataTable Initialization ===');
    
    // Load statistics
    loadExaminationStats();
    
    // Initialize DataTable with server-side processing
    examinationsTable = $('#examinationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('examination/getExaminationsData') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            data: function(d) {
                d[window.csrfConfig.name] = window.getCsrfToken();
                // Add any additional filters here if needed
                return d;
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
                
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
                
                let errorMessage = i18n.errorLoadingData;
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || error || thrown;
                }
                
                alert(i18n.datatableErrorPrefix + ' ' + errorMessage);
            }
        },
        columns: [
            { 
                data: 'examination_id',
                name: 'examination_id',
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    const identityHtml = `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-stethoscope text-orange-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500">${i18n.examinationIdLabel}</div>
                            </div>
                        </div>
                    `;
                    if (permissions.examinations.view) {
                        return `
                            <a href="<?= base_url('examination') ?>/${row.id}" class="block rounded-md hover:bg-orange-50/60 transition-colors duration-200 cursor-pointer">
                                ${identityHtml}
                            </a>
                        `;
                    }
                    return identityHtml;
                }
            },
            { 
                data: 'patient_name',
                name: 'patient_name',
                render: function(data, type, row) {
                    const avatar = getPatientAvatar(row.gender);
                    return `
                        <div class="flex items-center space-x-4">
                            <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                            </span>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500 font-medium">${row.patient_number}</div>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'examination_date',
                name: 'examination_date',
                render: function(data, type, row) {
                    return `
                        <div class="space-y-1">
                            <div class="text-sm font-bold text-gray-900">${row.examination_date_formatted || data}</div>
                            <div class="text-xs text-gray-500 font-medium">${row.examination_time_formatted || ''}</div>
                        </div>
                    `;
                }
            },
            { 
                data: 'examination_type',
                name: 'examination_type',
                render: function(data, type, row) {
                    const typeConfig = {
                        'emergency': { bg: 'bg-gradient-to-r from-red-100 to-pink-100', text: 'text-red-800', border: 'border-red-200', icon: 'exclamation-triangle' },
                        'initial': { bg: 'bg-gradient-to-r from-yellow-100 to-amber-100', text: 'text-yellow-800', border: 'border-yellow-200', icon: 'play-circle' },
                        'periodic': { bg: 'bg-gradient-to-r from-green-100 to-emerald-100', text: 'text-green-800', border: 'border-green-200', icon: 'calendar-check' },
                        'follow_up': { bg: 'bg-gradient-to-r from-gray-100 to-slate-100', text: 'text-gray-800', border: 'border-gray-200', icon: 'arrow-right' }
                    };
                    const typeLabelMap = {
                        emergency: i18n.typeEmergency,
                        initial: i18n.typeInitial,
                        periodic: i18n.typePeriodic,
                        follow_up: i18n.typeFollowUp,
                    };
                    const config = typeConfig[data] || typeConfig['follow_up'];
                    return `
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold ${config.bg} ${config.text} border ${config.border}">
                            <i class="fas fa-${config.icon} mr-2"></i>
                            ${typeLabelMap[data] || (typeLabelMap.follow_up)}
                        </span>
                    `;
                }
            },
            { 
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusConfig = {
                        'completed': { bg: 'bg-gradient-to-r from-green-100 to-emerald-100', text: 'text-green-800', border: 'border-green-200', icon: 'check-circle' },
                        'in_progress': { bg: 'bg-gradient-to-r from-blue-100 to-cyan-100', text: 'text-blue-800', border: 'border-blue-200', icon: 'clock' },
                        'pending': { bg: 'bg-gradient-to-r from-yellow-100 to-amber-100', text: 'text-yellow-800', border: 'border-yellow-200', icon: 'hourglass-half' },
                        'cancelled': { bg: 'bg-gradient-to-r from-red-100 to-pink-100', text: 'text-red-800', border: 'border-red-200', icon: 'times-circle' }
                    };
                    const statusLabelMap = {
                        pending: i18n.statusPending,
                        in_progress: i18n.statusInProgress,
                        completed: i18n.statusCompleted,
                        cancelled: i18n.statusCancelled,
                    };
                    const config = statusConfig[data] || statusConfig['pending'];
                    return `
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold ${config.bg} ${config.text} border ${config.border}">
                            <i class="fas fa-${config.icon} mr-2"></i>
                            ${statusLabelMap[data] || statusLabelMap.pending}
                        </span>
                    `;
                }
            },
            { 
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    let actions = '<div class="flex items-center space-x-2">';
                    
                    // View button
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}" 
                               class="p-2 text-orange-600 hover:text-orange-700 rounded-md hover:bg-orange-50 transition-colors duration-200" title="${i18n.actionViewDetails}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }
                    
                    // Edit button
                    if (permissions.examinations.edit) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}/edit" 
                               class="group/action relative p-2 text-blue-600 hover:text-blue-700 rounded-xl hover:bg-blue-50 transition-all duration-300 hover:scale-110" title="${i18n.actionEditExamination}">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 rounded-xl blur opacity-0 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-edit relative z-10"></i>
                            </a>`;
                    }
                    
                    // Odontogram button
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('odontogram') ?>/${row.patient_id}" 
                               class="group/action relative p-2 text-purple-600 hover:text-purple-700 rounded-xl hover:bg-purple-50 transition-all duration-300 hover:scale-110" title="${i18n.actionViewOdontogram}">
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-xl blur opacity-0 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-tooth relative z-10"></i>
                            </a>`;
                    }
                    
                    // Complete button
                    if ((row.status === 'pending' || row.status === 'in_progress') && permissions.examinations.edit) {
                        actions += `
                            <button onclick="completeExamination(${data})" 
                                    class="group/action relative p-2 text-green-600 hover:text-green-700 rounded-xl hover:bg-green-50 transition-all duration-300 hover:scale-110" title="${i18n.actionCompleteExamination}">
                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl blur opacity-0 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-check relative z-10"></i>
                            </button>`;
                    }
                    
                    // More actions dropdown
                    actions += `
                        <div class="relative">
                            <button onclick="toggleDropdown(${data})" class="p-2 text-gray-600 hover:text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-300 hover:scale-110" title="${i18n.actionMoreActions}">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="dropdown-${data}" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-200 z-10 hidden">
                                <div class="py-2">`;
                    
                    // Print button (always available if view permission)
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}/print" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
                                        <i class="fas fa-print mr-3 text-gray-500"></i>${i18n.actionPrintReport}
                                    </a>`;
                    }
                    
                    // Duplicate button (requires create permission)
                    if (permissions.examinations.create) {
                        actions += `
                                    <a href="<?= base_url('examination') ?>/${data}/duplicate" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                        <i class="fas fa-copy mr-3 text-blue-600"></i>${i18n.actionDuplicate}
                                    </a>`;
                    }
                    
                    // Delete button
                    if (permissions.examinations.delete) {
                        actions += `
                                    <button onclick="deleteExamination(${data})" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-200">
                                        <i class="fas fa-trash mr-3 text-red-600"></i>${i18n.actionDelete}
                                    </button>`;
                    }
                    
                    actions += `
                                </div>
                            </div>
                        </div>
                    </div>`;
                    
                    return actions;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: i18n.datatableLoadingExaminations,
            lengthMenu: "_MENU_",
            zeroRecords: i18n.datatableNoExaminationsFound,
            info: i18n.datatableInfo,
            infoEmpty: i18n.datatableInfoEmpty,
            infoFiltered: i18n.datatableInfoFiltered,
            search: "",
            searchPlaceholder: i18n.datatableSearchPlaceholder,
            paginate: {
                first: i18n.first,
                last: i18n.last,
                next: i18n.next,
                previous: i18n.previous
            }
        },
        dom: '<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i><?= esc($tr('pdf'), 'js') ?>',
                className: 'btn-pdf',
                title: i18n.examinationsReport,
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Adjust column indices as needed
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i><?= esc($tr('print'), 'js') ?>',
                className: 'btn-print',
                title: i18n.examinationsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Adjust column indices as needed
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i><?= esc($tr('csv'), 'js') ?>',
                className: 'btn-csv',
                title: i18n.examinationsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Adjust column indices as needed
                }
            }
        ],
        initComplete: function() {
            console.log('DataTables initComplete called');
            setTimeout(applyDataTablesStyling, 100);
        },
        drawCallback: function() {
            console.log('DataTables drawCallback called');
            setTimeout(applyDataTablesStyling, 50);
            if (permissions.examinations.view) {
                $('#examinationsTable tbody tr').addClass('cursor-pointer hover:bg-orange-50/40 transition-colors duration-150');
            }
        }
    });

    if (permissions.examinations.view) {
        $('#examinationsTable tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('a, button, input, select, textarea, [onclick]').length) {
                return;
            }

            const rowData = examinationsTable.row(this).data();
            if (!rowData || !rowData.id) {
                return;
            }

            const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
            window.location.href = `<?= base_url('examination') ?>/${rowData.id}`;
        });
    }
    
    // Set up mutation observer to watch for DataTables DOM changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Check if DataTables elements were added
                if (mutation.target.querySelector && 
                    (mutation.target.querySelector('.dataTables_length') || 
                     mutation.target.querySelector('.dataTables_filter') || 
                     mutation.target.querySelector('.dataTables_paginate'))) {
                    setTimeout(applyDataTablesStyling, 50);
                }
            }
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// View Toggle Functions
function toggleView(view) {
    // Hide all views
    document.getElementById('tableView').classList.add('hidden');
    document.getElementById('gridView').classList.add('hidden');
    document.getElementById('calendarView').classList.add('hidden');
    
    // Reset button styles
    document.getElementById('tableViewBtn').className = 'inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200';
    document.getElementById('gridViewBtn').className = 'inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200';
    document.getElementById('calendarViewBtn').className = 'inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200';
    
    // Show selected view and update button
    if (view === 'table') {
        document.getElementById('tableView').classList.remove('hidden');
        document.getElementById('tableViewBtn').className = 'inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md';
    } else if (view === 'grid') {
        document.getElementById('gridView').classList.remove('hidden');
        document.getElementById('gridViewBtn').className = 'inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md';
    } else if (view === 'calendar') {
        document.getElementById('calendarView').classList.remove('hidden');
        document.getElementById('calendarViewBtn').className = 'inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md';
        generateCalendar();
    }
    
    currentView = view;
}

// Calendar Functions
function generateCalendar() {
    const calendarGrid = document.getElementById('calendarGrid');
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    
    // Update month display
    document.getElementById('currentMonth').textContent = currentMonth.toLocaleDateString(i18n.locale, { month: 'long', year: 'numeric' });
    
    // Clear calendar
    calendarGrid.replaceChildren();
    
    // Add day headers
    i18n.calendarDays.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'p-2 text-center text-sm font-semibold text-gray-600 bg-gray-50';
        dayHeader.textContent = day;
        calendarGrid.appendChild(dayHeader);
    });
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    // Add empty cells for days before month starts
    for (let i = 0; i < startingDayOfWeek; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'h-20 p-2 border border-gray-200 bg-gray-50';
        calendarGrid.appendChild(emptyCell);
    }
    
    // Add days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'h-20 p-2 border border-gray-200 hover:bg-gray-50 cursor-pointer';
        
        const dayNum = document.createElement('div');
        dayNum.className = 'text-sm font-medium text-gray-900';
        dayNum.textContent = day;
        
        const dayContent = document.createElement('div');
        dayContent.className = 'mt-1 space-y-1';
        
        dayCell.appendChild(dayNum);
        dayCell.appendChild(dayContent);
        calendarGrid.appendChild(dayCell);
    }
}

function previousMonth() {
    currentMonth.setMonth(currentMonth.getMonth() - 1);
    generateCalendar();
}

function nextMonth() {
    currentMonth.setMonth(currentMonth.getMonth() + 1);
    generateCalendar();
}

// Export Functions
function exportToPDF() {
    if (examinationsTable) {
        examinationsTable.button('.btn-pdf').trigger();
    }
}

function printExaminations() {
    if (examinationsTable) {
        examinationsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (examinationsTable) {
        examinationsTable.button('.btn-csv').trigger();
    }
}

// Examination Actions
function completeExamination(examinationId) {
    document.getElementById('examinationId').value = examinationId;
    const modal = document.getElementById('completeModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function deleteExamination(examinationId) {
    if (confirm(i18n.deleteExaminationConfirm)) {
        fetch(`<?= base_url('examination') ?>/${examinationId}/delete`, {
            method: 'DELETE',
            headers: {
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            
            if (data.success) {
                examinationsTable.ajax.reload();
            } else {
                alert(i18n.errorPrefix + ' ' + data.message);
            }
        });
    }
}

function closeCompleteModal() {
    const modal = document.getElementById('completeModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.getElementById('completeForm').reset();
}

// Handle form submission
document.getElementById('completeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const examinationId = formData.get('examination_id');
    
    fetch(`<?= base_url('examination') ?>/${examinationId}/complete`, {
        method: 'POST',
        headers: {
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        
        if (data.success) {
            closeCompleteModal();
            examinationsTable.ajax.reload();
        } else {
            alert(i18n.errorPrefix + ' ' + data.message);
        }
    });
});

// Close modal when clicking outside
document.getElementById('completeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCompleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCompleteModal();
    }
});

// Dropdown toggle function
function toggleDropdown(examinationId) {
    const dropdown = document.getElementById(`dropdown-${examinationId}`);
    if (!dropdown) return;
    
    // Close all other dropdowns first
    document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
        if (dropdown.id !== `dropdown-${examinationId}`) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    // Check if click is outside any dropdown
    if (!e.target.closest('[id^="dropdown-"]') && !e.target.closest('button[onclick*="toggleDropdown"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});
</script>
<?= $this->endSection() ?>

