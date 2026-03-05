<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
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
</style>

<!-- Enhanced Treatment Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-pink-50 to-purple-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-pink-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-purple-400/20 to-pink-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-pink-400/10 to-purple-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-procedures text-xl"></i>
                                </div>
                                <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= lang('Treatment.treatment_management') ?></h1>
                            <p class="text-gray-600"><?= lang('Treatment.treatment_management_subtitle') ?></p>
                                </div>
                            </div>
                    <div class="flex items-center space-x-3">
                        <?php if (has_permission('treatments', 'create')): ?>
                            <a href="<?= base_url('treatment/create?return_to=' . $returnToContextEncoded) ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-sm font-semibold rounded-lg hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                <?= lang('Treatment.new_treatment') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                <!-- Total Treatments Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-pink-500/20 to-purple-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-pink-500/10 group-hover:shadow-pink-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-pink-500 to-purple-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-procedures text-xl"></i>
                            </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-pink-100 to-purple-100 px-3 py-1.5 rounded-full border border-pink-200">
                                <div class="w-2 h-2 bg-pink-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-pink-600 text-xs"></i>
                                <span class="text-pink-700 font-bold text-xs">+15%</span>
                        </div>
                    </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Treatment.total_treatments') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-pink-900" id="totalTreatments">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-pink-200 to-purple-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-pink-500 to-purple-500 rounded-full animate-pulse"></div>
                </div>
            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Treatment.vs_last_month') ?></p>
                            </div>
                            </div>
                        </div>

                <!-- Active Treatments Card -->
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
                                <span class="text-green-700 font-bold text-sm"><?= lang('Treatment.status_values.active') ?></span>
            </div>
                            </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Treatment.active_treatments') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-green-900" id="activeTreatments">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Treatment.currently_active') ?></p>
                    </div>
                </div>
            </div>

                <!-- Completed Treatments Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-check-double text-xl"></i>
                            </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-cyan-100 px-4 py-2 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-trophy text-blue-600 text-sm"></i>
                                <span class="text-blue-700 font-bold text-sm"><?= lang('Treatment.status_values.completed') ?></span>
                        </div>
                    </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Treatment.completed_treatments') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-blue-900" id="completedTreatments">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-blue-200 to-cyan-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Treatment.successfully_completed') ?></p>
                </div>
            </div>
        </div>

                <!-- This Month Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-pink-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-calendar-day text-xl"></i>
                            </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-purple-100 to-pink-100 px-4 py-2 rounded-full border border-purple-200">
                                <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-calendar text-purple-600 text-sm"></i>
                                <span class="text-purple-700 font-bold text-sm"><?= lang('Treatment.this_month') ?></span>
                        </div>
                    </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Treatment.this_month') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-purple-900" id="monthlyTreatments">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-purple-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-5/6 h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse"></div>
                        </div>
                        </div>
                            <p class="text-xs text-gray-500 font-medium"><?= formatDate(date('Y-m-d')) ?></p>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
                
        <!-- Standardized Treatments Table -->
        <div class="mb-8">
            <!-- Table View -->
            <div id="tableView" class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-pink-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Treatment.treatment_registry') ?></h3>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                <?= lang('Treatment.pdf') ?>
                            </button>
                            <button onclick="printTreatments()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                <?= lang('Treatment.print') ?>
                            </button>
                            <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                <?= lang('Treatment.csv') ?>
                            </button>
                    </div>
                </div>
            </div>

                    <!-- Enhanced Table with Advanced Styling -->
                    <div class="overflow-x-auto">
                        <div class="dataTables-wrapper">
                            <table id="treatmentsTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-pink-600"></i>
                                    <span><?= lang('Treatment.patient') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                            <i class="fas fa-procedures text-pink-600"></i>
                                    <span><?= lang('Treatment.treatment') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                            <i class="fas fa-dollar-sign text-pink-600"></i>
                                    <span><?= lang('Treatment.cost') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle text-pink-600"></i>
                                    <span><?= lang('Treatment.status') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-pink-600"></i>
                                    <span><?= lang('Treatment.start_date') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-pink-600"></i>
                                    <span><?= lang('Treatment.actions') ?></span>
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
let treatmentsTable;

$(document).ready(function() {
    // Initialize DataTable with server-side processing
    treatmentsTable = $('#treatmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('treatment/get-treatments') ?>',
            type: 'GET',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.search_term = $('#searchInput').val();
            }
        },
        columns: [
            {
                data: 'patient_name',
                name: 'patient_name',
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    const identityHtml = `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-pink-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500">${row.patient_id || '<?= esc(lang('Treatment.no_id'), 'js') ?>'}</div>
                            </div>
                        </div>
                    `;
                    if (permissions.treatments.view) {
                        return `
                            <a href="<?= base_url('treatment') ?>/${row.id}?return_to=${returnTo}" class="block rounded-md hover:bg-pink-50/60 transition-colors duration-200 cursor-pointer">
                                ${identityHtml}
                            </a>
                        `;
                    }
                    return identityHtml;
                }
            },
            {
                data: 'treatment_name',
                name: 'treatment_name',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-procedures text-pink-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500"><?= esc(lang('Treatment.treatment'), 'js') ?></div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'cost',
                name: 'cost',
                render: function(data, type, row) {
                    return data ? `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-dollar-sign text-pink-600 text-xs"></i>
                            <span class="text-sm text-gray-900"><?= settings()->getCurrencySymbol() ?>${parseFloat(data).toFixed(2)}</span>
                        </div>
                    ` : '<span class="text-gray-400 text-sm"><?= esc(lang('Treatment.no_cost'), 'js') ?></span>';
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'bg-green-100 text-green-800',
                        'completed': 'bg-blue-100 text-blue-800',
                        'cancelled': 'bg-gray-100 text-gray-800',
                        'on_hold': 'bg-yellow-100 text-yellow-800'
                    };
                    const statusIcons = {
                        'active': 'fas fa-play-circle',
                        'completed': 'fas fa-check-circle',
                        'cancelled': 'fas fa-times-circle',
                        'on_hold': 'fas fa-pause-circle'
                    };
                    const colorClass = statusColors[data] || 'bg-gray-100 text-gray-800';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    const localizedStatus = {
                        active: '<?= esc(lang('Treatment.status_values.active'), 'js') ?>',
                        completed: '<?= esc(lang('Treatment.status_values.completed'), 'js') ?>',
                        cancelled: '<?= esc(lang('Treatment.status_values.cancelled'), 'js') ?>',
                        on_hold: '<?= esc(lang('Treatment.status_values.on_hold'), 'js') ?>'
                    };
                    
                    return `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            <i class="${iconClass} mr-1"></i>
                            ${localizedStatus[data] || (data.charAt(0).toUpperCase() + data.slice(1))}
                        </span>
                    `;
                }
            },
            {
                data: 'start_date',
                name: 'start_date',
                render: function(data, type, row) {
                    const date = new Date(data);
                    return `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-calendar text-pink-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${row.start_date_formatted || date.toLocaleDateString()}</span>
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
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    let actions = '<div class="flex items-center space-x-2">';
                    
                    // View button
                    if (permissions.treatments.view) {
                        actions += `
                            <a href="<?= base_url('treatment') ?>/${data}?return_to=${returnTo}" 
                               class="p-2 text-pink-600 hover:text-pink-700 rounded-md hover:bg-pink-50 transition-colors duration-200" title="<?= esc(lang('Treatment.view_details'), 'attr') ?>">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }
                    
                    // Edit button
                    if (permissions.treatments.edit) {
                        actions += `
                            <a href="<?= base_url('treatment') ?>/${data}/edit?return_to=${returnTo}" 
                               class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="<?= esc(lang('Treatment.edit_treatment'), 'attr') ?>">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }
                    
                    // Delete button
                    if (permissions.treatments.delete) {
                        actions += `
                            <button onclick="deleteTreatment(${data})" 
                                    class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200" title="<?= esc(lang('Treatment.delete_treatment'), 'attr') ?>">
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
                text: '<i class="fas fa-file-pdf mr-2"></i><?= esc(lang('Treatment.pdf'), 'js') ?>',
                className: 'btn-pdf',
                title: '<?= esc(lang('Treatment.treatments_report'), 'js') ?>',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Treatment, Date, Status, Cost, Actions
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i><?= esc(lang('Treatment.print'), 'js') ?>',
                className: 'btn-print',
                title: '<?= esc(lang('Treatment.treatments_report'), 'js') ?>',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Treatment, Date, Status, Cost, Actions
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i><?= esc(lang('Treatment.csv'), 'js') ?>',
                className: 'btn-csv',
                title: '<?= esc(lang('Treatment.treatments_report'), 'js') ?>',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Treatment, Date, Status, Cost, Actions
                }
            }
        ],
        language: {
            processing: "<?= esc(lang('Treatment.loading_treatments'), 'js') ?>",
            lengthMenu: "_MENU_",
            zeroRecords: "<?= esc(lang('Treatment.no_treatments_found'), 'js') ?>",
            info: "<?= esc(lang('Treatment.datatable_info'), 'js') ?>",
            infoEmpty: "<?= esc(lang('Treatment.no_treatments_available'), 'js') ?>",
            infoFiltered: "<?= esc(lang('Treatment.datatable_info_filtered'), 'js') ?>",
            search: "",
            searchPlaceholder: "<?= esc(lang('Treatment.search_treatments_placeholder'), 'js') ?>",
            paginate: {
                first: "<?= esc(lang('Treatment.first'), 'js') ?>",
                last: "<?= esc(lang('Treatment.last'), 'js') ?>",
                next: "<?= esc(lang('Treatment.next'), 'js') ?>",
                previous: "<?= esc(lang('Treatment.previous'), 'js') ?>"
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
        $('.dataTables_length select').addClass('focus:ring-pink-500 focus:border-pink-500');
        
        // Style search input
        $('.dataTables_filter input').addClass('focus:ring-pink-500 focus:border-pink-500');
        
        // Style info text
        $('.dataTables_info').addClass('text-gray-600');
        
        // Style pagination buttons
        $('.dataTables_paginate .paginate_button').each(function() {
            if ($(this).hasClass('current')) {
                $(this).addClass('bg-pink-600 border-pink-600 text-white');
            } else {
                $(this).addClass('hover:bg-pink-50 hover:border-pink-300 hover:text-pink-700');
            }
        });
        
        // Style processing indicator
        $('.dataTables_processing').addClass('border-pink-200 text-pink-700');
        
        // Style labels
        $('.dataTables_length label, .dataTables_filter label').addClass('text-gray-700 font-semibold');
        
        // Style icons
        $('.dataTables_length i, .dataTables_filter i').addClass('text-pink-600');
    }

    // Load statistics
    loadTreatmentStatistics();

    // Search functionality
    $('#searchInput').on('keyup', function() {
        treatmentsTable.search(this.value).draw();
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        treatmentsTable.draw();
    });
});
    
// Load treatment statistics
function loadTreatmentStatistics() {
    $.ajax({
        url: '<?= base_url('treatment/stats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalTreatments').text(data.total || 0);
            $('#activeTreatments').text(data.active || 0);
            $('#completedTreatments').text(data.completed || 0);
                $('#monthlyTreatments').text(data.monthly_treatments || 0);
        },
        error: function() {
            $('#totalTreatments').text('0');
            $('#activeTreatments').text('0');
            $('#completedTreatments').text('0');
            $('#monthlyTreatments').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (treatmentsTable) {
        treatmentsTable.button('.btn-pdf').trigger();
    }
}

function printTreatments() {
    if (treatmentsTable) {
        treatmentsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (treatmentsTable) {
        treatmentsTable.button('.btn-csv').trigger();
    }
}

// Delete treatment function
function deleteTreatment(treatmentId) {
    if (confirm('<?= esc(lang('Treatment.confirm_delete_treatment'), 'js') ?>')) {
        // Create form for DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('treatment') ?>/' + treatmentId;
        
        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Permissions object (you'll need to populate this from your backend)
var permissions = {
    treatments: {
        view: true,
        edit: true,
        delete: true,
        create: true
    }
};
</script>

<?= $this->endSection() ?>
