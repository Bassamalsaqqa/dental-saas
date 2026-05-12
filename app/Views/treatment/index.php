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
$shouldOpenTreatmentCreateModal = old('source_modal') === 'treatment_quick_create';
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
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
.treatment-create-modal {
    visibility: hidden;
    pointer-events: none;
    transition: visibility 0s linear 240ms;
}
.treatment-create-modal.is-open {
    visibility: visible;
    pointer-events: auto;
    transition-delay: 0s;
}
.treatment-create-modal__backdrop {
    opacity: 0;
    transition: opacity 220ms ease;
}
.treatment-create-modal.is-open .treatment-create-modal__backdrop {
    opacity: 1;
}
.treatment-create-modal__panel {
    display: grid !important;
    grid-template-rows: auto minmax(0, 1fr) auto;
    opacity: 0;
    overflow: clip;
    transform: scale(0.86);
    transform-origin: center center;
    height: min(92dvh, 52rem);
    transition:
        opacity 180ms ease,
        transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform, opacity;
}
.treatment-create-modal.is-open .treatment-create-modal__panel {
    opacity: 1;
    transform: scale(1);
}
.treatment-create-modal .select2-container {
    width: 100% !important;
}
@media (prefers-reduced-motion: reduce) {
    .treatment-create-modal__backdrop,
    .treatment-create-modal__panel {
        transition: none;
    }
}
@media (max-width: 640px) {
    .treatment-create-modal__shell {
        align-items: stretch;
        padding: 0;
    }
    .treatment-create-modal__panel {
        width: 100vw;
        height: 100dvh;
        min-height: 100dvh;
        max-height: 100dvh;
        border-radius: 0;
    }
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

    <div class="container mx-auto relative z-10 px-3 sm:px-4 py-4 sm:py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-center">
                    <div class="flex items-start sm:items-center space-x-3 sm:space-x-4">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-br from-pink-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-procedures text-lg sm:text-xl"></i>
                                </div>
                                <div>
                            <h1 class="text-xl sm:text-3xl font-bold text-gray-900"><?= lang('Treatment.treatment_management') ?></h1>
                            <p class="text-gray-600"><?= lang('Treatment.treatment_management_subtitle') ?></p>
                                </div>
                            </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-3">
                        <?php if (has_permission('treatments', 'create')): ?>
                            <a href="<?= base_url('treatment/create') ?>" id="newTreatmentModalTrigger" data-treatment-create-modal-open class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-sm font-semibold rounded-lg hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 items-stretch">
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

<?php if (has_permission('treatments', 'create')): ?>
    <div id="newTreatmentModal" class="app-scale-modal treatment-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newTreatmentModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="treatment-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-treatment-create-modal-close></div>
        <div class="treatment-create-modal__shell relative z-10 flex h-full items-center justify-center p-4" data-treatment-create-modal-close>
            <form id="newTreatmentQuickCreateForm" action="<?= base_url('treatment/store') ?>" method="POST" class="treatment-create-modal__panel flex w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="treatment">
                <input type="hidden" name="source_modal" value="treatment_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-purple-600 text-white shadow-lg">
                            <i class="fas fa-tooth"></i>
                        </span>
                        <div>
                            <h2 id="newTreatmentModalTitle" class="text-lg font-bold text-slate-900"><?= esc(lang('Treatment.new_treatment')) ?></h2>
                            <a href="<?= base_url('treatment/create?return_to=treatment') ?>" class="mt-1 inline-flex text-xs font-semibold text-pink-700 hover:text-pink-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-treatment-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 overflow-y-auto bg-slate-50/70 p-5">
                    <?php if (session('error') && $shouldOpenTreatmentCreateModal): ?>
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                        <?= view('treatment/partials/form_fields', [
                            'contextual' => false,
                            'patients' => [],
                            'treatment_types' => $treatment_types ?? [],
                            'inventory_items' => $inventory_items ?? [],
                            'validation' => $validation ?? null,
                            'selected_examination_id' => null,
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-treatment-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('Treatment.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-pink-500 to-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-pink-600 hover:to-purple-700 hover:shadow-xl">
                        <i class="fas fa-tooth me-2"></i>
                        <?= esc(lang('Treatment.create_treatment')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

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
let newTreatmentModalTrigger = null;
let newTreatmentModalCloseTimer = null;

function mountNewTreatmentModal() {
    const modal = document.getElementById('newTreatmentModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    if (modal && modal.dataset.closeControlsBound !== '1') {
        modal.dataset.closeControlsBound = '1';
        modal.querySelectorAll('button[data-treatment-create-modal-close], a[data-treatment-create-modal-close]').forEach(function (control) {
            control.addEventListener('click', function (event) {
                event.preventDefault();
                closeNewTreatmentModal();
            });
        });
    }
    return modal;
}

function initializeNewTreatmentModalContent(modal) {
    if (!modal) {
        return Promise.resolve();
    }
    const tasks = [];
    if (typeof window.initAppSelects === 'function') {
        tasks.push(Promise.resolve(window.initAppSelects(modal)));
    }
    if (typeof window.initializeClinicDateInputs === 'function') {
        window.initializeClinicDateInputs(modal);
    }
    if (typeof window.initContextualTreatmentForms === 'function') {
        window.initContextualTreatmentForms(modal);
    }
    return Promise.allSettled(tasks);
}

function openNewTreatmentModal(trigger) {
    const modal = mountNewTreatmentModal();
    if (!modal) {
        return;
    }
    newTreatmentModalTrigger = trigger || document.activeElement;
    if (newTreatmentModalCloseTimer) {
        clearTimeout(newTreatmentModalCloseTimer);
        newTreatmentModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function () {
        modal.classList.add('is-open');
    });
    window.setTimeout(function () {
        initializeNewTreatmentModalContent(modal);
    }, 80);
    window.setTimeout(function () {
        const firstField = modal.querySelector('[name="patient_id"]');
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus({ preventScroll: true });
        }
    }, 120);
}

function closeNewTreatmentModal() {
    const modal = document.getElementById('newTreatmentModal');
    if (!modal || modal.classList.contains('hidden')) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    newTreatmentModalCloseTimer = setTimeout(function () {
        modal.classList.add('hidden');
        if (newTreatmentModalTrigger && typeof newTreatmentModalTrigger.focus === 'function') {
            newTreatmentModalTrigger.focus();
        }
        newTreatmentModalTrigger = null;
    }, 240);
}

document.addEventListener('click', function (event) {
    const openTrigger = event.target.closest('[data-treatment-create-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openNewTreatmentModal(openTrigger);
        return;
    }

    const closeTrigger = event.target.closest('[data-treatment-create-modal-close]');
    if (closeTrigger) {
        event.preventDefault();
        closeNewTreatmentModal();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
        return;
    }
    const modal = document.getElementById('newTreatmentModal');
    if (modal && !modal.classList.contains('hidden')) {
        event.preventDefault();
        closeNewTreatmentModal();
    }
});

$(document).ready(function() {
    if (<?= $shouldOpenTreatmentCreateModal ? 'true' : 'false' ?>) {
        openNewTreatmentModal(document.getElementById('newTreatmentModalTrigger'));
    }

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
                    const gender = String(row.gender || 'other').toLowerCase();
                    const avatarMap = {
                        male: { icon: 'fas fa-person', color: 'text-blue-700' },
                        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
                        other: { icon: 'fas fa-user', color: 'text-slate-600' }
                    };
                    const avatar = avatarMap[gender] || avatarMap.other;
                    const patientNumber = row.patient_number || '<?= esc(lang('Treatment.no_id'), 'js') ?>';
                    return `
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">${data || '<?= esc(lang('Treatment.na'), 'js') ?>'}</div>
                                <div class="text-xs text-gray-500 truncate">${patientNumber}</div>
                            </div>
                        </div>
                    `;
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
                            <a href="<?= base_url('treatment') ?>/${data}" 
                               class="p-2 text-pink-600 hover:text-pink-700 rounded-md hover:bg-pink-50 transition-colors duration-200" title="<?= esc(lang('Treatment.view_details'), 'attr') ?>">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }
                    
                    // Edit button
                    if (permissions.treatments.edit) {
                        actions += `
                            <a href="<?= base_url('treatment') ?>/${data}/edit" 
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

        if (permissions.treatments.view) {
            $('#treatmentsTable tbody')
                .on('click', 'tr', function (event) {
                    const $target = $(event.target);
                    if ($target.closest('a, button').length) {
                        return;
                    }

                    const rowData = treatmentsTable.row(this).data();
                    if (!rowData || !rowData.id) {
                        return;
                    }

                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    window.location.href = `<?= base_url('treatment') ?>/${rowData.id}`;
                })
                .on('mouseenter', 'tr', function () {
                    $(this).addClass('cursor-pointer hover:bg-pink-50/50 transition-colors duration-150');
                });
        }
        
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
