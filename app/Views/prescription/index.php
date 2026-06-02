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
$shouldOpenPrescriptionCreateModal = old('source_modal') === 'prescription_quick_create';
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
</style>
<style>
.prescription-create-modal {
    visibility: hidden;
    pointer-events: none;
    transition: visibility 0s linear 240ms;
}
.prescription-create-modal.is-open {
    visibility: visible;
    pointer-events: auto;
    transition-delay: 0s;
}
.prescription-create-modal__backdrop {
    opacity: 0;
    transition: opacity 220ms ease;
}
.prescription-create-modal.is-open .prescription-create-modal__backdrop {
    opacity: 1;
}
.prescription-create-modal__panel {
    opacity: 0;
    transform: scale(0.86);
    transform-origin: center center;
    transition:
        opacity 180ms ease,
        transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform, opacity;
}
.prescription-create-modal.is-open .prescription-create-modal__panel {
    opacity: 1;
    transform: scale(1);
}
.prescription-create-modal .select2-container {
    width: 100% !important;
}
@media (prefers-reduced-motion: reduce) {
    .prescription-create-modal__backdrop,
    .prescription-create-modal__panel {
        transition: none;
    }
}
@media (max-width: 640px) {
    .prescription-create-modal__shell {
        align-items: stretch;
        padding: 0;
    }
    .prescription-create-modal__panel {
        width: 100vw;
        min-height: 100dvh;
        max-height: 100dvh;
        border-radius: 0;
    }
}
</style>
<style>
[dir="ltr"] .prescription-leading-input {
    padding-left: 0.75rem !important;
    padding-right: 2.5rem !important;
}

[dir="rtl"] .prescription-leading-input {
    padding-right: 2.5rem !important;
    padding-left: 0.75rem !important;
    text-align: right;
}

[dir="ltr"] input[data-clinic-date="1"].prescription-date-input {
    padding-left: 1rem !important;
    padding-right: 2.75rem !important;
}

[dir="rtl"] input[data-clinic-date="1"].prescription-date-input {
    padding-right: 2.75rem !important;
    padding-left: 1rem !important;
    text-align: right;
}
</style>

<!-- Enhanced Prescription Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-teal-50 to-emerald-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-teal-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-teal-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-emerald-400/10 to-teal-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
                    </div>
                    
    <div class="container mx-auto relative z-10 px-3 sm:px-4 py-4 sm:py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-center">
                    <div class="flex items-start sm:items-center space-x-3 sm:space-x-4">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-prescription-bottle-alt text-lg sm:text-xl"></i>
                            </div>
                        <div>
                            <h1 class="text-xl sm:text-3xl font-bold text-gray-900"><?= lang('Prescription.management_title') ?></h1>
                            <p class="text-gray-600"><?= lang('Prescription.management_subtitle') ?></p>
                                </div>
                            </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-3">
                        <?php if (has_permission('prescriptions', 'create')): ?>
                            <a href="<?= base_url('prescription/create') ?>" id="newPrescriptionModalTrigger" data-prescription-create-modal-open class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-gradient-to-r from-teal-500 to-emerald-600 text-white text-sm font-semibold rounded-lg hover:from-teal-600 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                <?= lang('Prescription.new_prescription') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 items-stretch">
                <!-- Total Prescriptions Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-teal-500/10 group-hover:shadow-teal-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-prescription-bottle-alt text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-teal-100 to-emerald-100 px-3 py-1.5 rounded-full border border-teal-200">
                                <div class="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-teal-600 text-xs"></i>
                                <span class="text-teal-700 font-bold text-xs">+12%</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Prescription.total_prescriptions') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-teal-900" id="totalPrescriptions">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-teal-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-teal-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Prescription.vs_last_month') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Active Prescriptions Card -->
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
                                <span class="text-green-700 font-bold text-sm"><?= lang('Prescription.status_active') ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Prescription.active_prescriptions') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-green-900" id="activePrescriptions">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Prescription.currently_active') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Expired Prescriptions Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-orange-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-exclamation-triangle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-4 py-2 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-clock text-amber-600 text-sm"></i>
                                <span class="text-amber-700 font-bold text-sm"><?= lang('Prescription.status_expired') ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Prescription.expired_prescriptions') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-amber-900" id="expiredPrescriptions">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Prescription.need_renewal') ?></p>
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
                                <span class="text-purple-700 font-bold text-sm"><?= lang('Prescription.this_month') ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Prescription.this_month') ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-purple-900" id="monthlyPrescriptions">
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

        <!-- Standardized Prescriptions Table -->
        <div class="mb-8">
            <!-- Table View -->
            <div id="tableView" class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-teal-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Prescription.prescription_registry') ?></h3>
    </div>
                        <div class="flex items-center space-x-3">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                <?= lang('Prescription.export_pdf') ?>
                            </button>
                            <button onclick="printPrescriptions()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                <?= lang('Prescription.export_print') ?>
                            </button>
                            <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                <?= lang('Prescription.export_csv') ?>
                            </button>
</div>
    </div>
</div>

                    <!-- Enhanced Table with Advanced Styling -->
                    <div class="overflow-x-auto">
                        <div class="dataTables-wrapper">
                            <table id="prescriptionsTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-prescription-bottle-alt text-teal-600"></i>
                                            <span><?= lang('Prescription.prescription_id') ?></span>
                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-teal-600"></i>
                                            <span><?= lang('Prescription.patient') ?></span>
                        </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-pills text-teal-600"></i>
                                            <span><?= lang('Prescription.medicines') ?></span>
                    </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar-alt text-teal-600"></i>
                                            <span><?= lang('Prescription.date_col') ?></span>
                            </div>
                                    </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle text-teal-600"></i>
                                            <span><?= lang('Prescription.status') ?></span>
                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-teal-600"></i>
                                            <span><?= lang('Prescription.actions') ?></span>
                            </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="prescriptionsTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be loaded via DataTables -->
                                    <tr id="loadingRow">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            <div class="flex items-center justify-center space-x-2">
                                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-teal-600"></div>
                                                <span><?= lang('Prescription.loading_prescriptions') ?></span>
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

<?php if (has_permission('prescriptions', 'create')): ?>
    <div id="newPrescriptionModal" class="app-scale-modal prescription-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPrescriptionModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="prescription-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-prescription-create-modal-close></div>
        <div class="prescription-create-modal__shell relative z-10 flex min-h-screen items-center justify-center p-4" data-prescription-create-modal-close>
            <form id="newPrescriptionQuickCreateForm" action="<?= base_url('prescription/store') ?>" method="POST" class="prescription-create-modal__panel flex max-h-[92dvh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-prescription-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
                <input type="hidden" name="source_modal" value="prescription_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </span>
                        <div>
                            <h2 id="newPrescriptionModalTitle" class="text-lg font-bold text-slate-900"><?= esc(lang('Prescription.new_prescription')) ?></h2>
                            <a href="<?= workflow_context_url('prescription/create', $returnToContext) ?>" class="mt-1 inline-flex text-xs font-semibold text-teal-700 hover:text-teal-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-prescription-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-5">
                    <?php if (session('error') && $shouldOpenPrescriptionCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session('errors') && $shouldOpenPrescriptionCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?php foreach ((array) session('errors') as $error): ?>
                                <div><?= esc($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                        <?= view('prescription/partials/form_fields', [
                            'selected_patient' => null,
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'prescriptionQuick',
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-prescription-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('Prescription.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-teal-600 hover:to-emerald-700 hover:shadow-xl">
                        <i class="fas fa-prescription-bottle-alt me-2"></i>
                        <?= esc(lang('Prescription.create_prescription')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?= view('prescription/partials/form_script') ?>

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
let prescriptionsTable;
let newPrescriptionModalTrigger = null;
let newPrescriptionModalCloseTimer = null;
const prescriptionI18n = {
    prescriptionId: <?= json_encode(lang('Prescription.prescription_id')) ?>,
    noPhone: <?= json_encode(lang('Prescription.no_phone')) ?>,
    medicineSingular: <?= json_encode(lang('Prescription.medicine_singular')) ?>,
    medicinePlural: <?= json_encode(lang('Prescription.medicine_plural')) ?>,
    oneMedicine: <?= json_encode(lang('Prescription.one_medicine')) ?>,
    noMedicines: <?= json_encode(lang('Prescription.no_medicines')) ?>,
    statusLabels: {
        active: <?= json_encode(lang('Prescription.status_active')) ?>,
        expired: <?= json_encode(lang('Prescription.status_expired')) ?>,
        cancelled: <?= json_encode(lang('Prescription.status_cancelled')) ?>,
        pending: <?= json_encode(lang('Prescription.status_pending')) ?>
    },
    viewDetails: <?= json_encode(lang('Prescription.view_details')) ?>,
    printPrescription: <?= json_encode(lang('Prescription.print_prescription')) ?>,
    editPrescription: <?= json_encode(lang('Prescription.edit_prescription')) ?>,
    deletePrescription: <?= json_encode(lang('Prescription.delete_prescription')) ?>,
    exportPdf: <?= json_encode(lang('Prescription.export_pdf')) ?>,
    exportPrint: <?= json_encode(lang('Prescription.export_print')) ?>,
    exportCsv: <?= json_encode(lang('Prescription.export_csv')) ?>,
    prescriptionsReport: <?= json_encode(lang('Prescription.prescriptions_report')) ?>,
    dtProcessing: <?= json_encode(lang('Prescription.dt_processing')) ?>,
    dtZeroRecords: <?= json_encode(lang('Prescription.dt_zero_records')) ?>,
    dtInfo: <?= json_encode(lang('Prescription.dt_info')) ?>,
    dtInfoEmpty: <?= json_encode(lang('Prescription.dt_info_empty')) ?>,
    dtInfoFiltered: <?= json_encode(lang('Prescription.dt_info_filtered')) ?>,
    dtSearchPlaceholder: <?= json_encode(lang('Prescription.dt_search_placeholder')) ?>,
    dtFirst: <?= json_encode(lang('Prescription.dt_first')) ?>,
    dtLast: <?= json_encode(lang('Prescription.dt_last')) ?>,
    dtNext: <?= json_encode(lang('Prescription.dt_next')) ?>,
    dtPrevious: <?= json_encode(lang('Prescription.dt_previous')) ?>,
    errorLoadingData: <?= json_encode(lang('Prescription.error_loading_data')) ?>,
    unknownError: <?= json_encode(lang('Prescription.unknown_error')) ?>,
    deleteConfirm: <?= json_encode(lang('Prescription.delete_confirm')) ?>
};

function getPatientAvatar(gender) {
    const avatarMap = {
        male: { icon: 'fas fa-person', color: 'text-blue-700' },
        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
        other: { icon: 'fas fa-user', color: 'text-slate-600' }
    };

    return avatarMap[String(gender || 'other').toLowerCase()] || avatarMap.other;
}

function mountNewPrescriptionModal() {
    const modal = document.getElementById('newPrescriptionModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    if (modal && modal.dataset.closeControlsBound !== '1') {
        modal.dataset.closeControlsBound = '1';
        modal.querySelectorAll('button[data-prescription-create-modal-close], a[data-prescription-create-modal-close]').forEach(function(control) {
            control.addEventListener('click', function(event) {
                event.preventDefault();
                closeNewPrescriptionModal();
            });
        });
    }
    return modal;
}

function initializeNewPrescriptionModalContent(modal) {
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
    if (typeof window.initPrescriptionCreateForms === 'function') {
        window.initPrescriptionCreateForms(modal);
    }
    return Promise.allSettled(tasks);
}

function openNewPrescriptionModal(trigger) {
    const modal = mountNewPrescriptionModal();
    if (!modal) {
        return;
    }
    newPrescriptionModalTrigger = trigger || document.activeElement;
    if (newPrescriptionModalCloseTimer) {
        clearTimeout(newPrescriptionModalCloseTimer);
        newPrescriptionModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function() {
        modal.classList.add('is-open');
    });
    window.setTimeout(function() {
        initializeNewPrescriptionModalContent(modal);
        const firstField = modal.querySelector('[name="patient_id"], input, select, textarea, button');
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus({ preventScroll: true });
        }
    }, 80);
}

function closeNewPrescriptionModal() {
    const modal = document.getElementById('newPrescriptionModal');
    if (!modal || modal.classList.contains('hidden')) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    newPrescriptionModalCloseTimer = setTimeout(function() {
        const form = modal.querySelector('[data-prescription-create-form]');
        if (form) {
            form.reset();
            if (typeof form.prescriptionReset === 'function') {
                form.prescriptionReset();
            }
            form.querySelectorAll('select').forEach(function(select) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
                if (window.jQuery) {
                    window.jQuery(select).trigger('change');
                }
            });
        }
        modal.classList.add('hidden');
        if (newPrescriptionModalTrigger && typeof newPrescriptionModalTrigger.focus === 'function') {
            newPrescriptionModalTrigger.focus();
        }
        newPrescriptionModalTrigger = null;
    }, 240);
}

document.addEventListener('click', function(event) {
    const openTrigger = event.target.closest('[data-prescription-create-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openNewPrescriptionModal(openTrigger);
        return;
    }

    const closeTrigger = event.target.closest('[data-prescription-create-modal-close]');
    if (closeTrigger) {
        event.preventDefault();
        closeNewPrescriptionModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key !== 'Escape') {
        return;
    }
    const modal = document.getElementById('newPrescriptionModal');
    if (modal && !modal.classList.contains('hidden')) {
        event.preventDefault();
        closeNewPrescriptionModal();
    }
});

$(document).ready(function() {
    if (<?= $shouldOpenPrescriptionCreateModal ? 'true' : 'false' ?>) {
        openNewPrescriptionModal(document.getElementById('newPrescriptionModalTrigger'));
    }

    // Initialize DataTable with server-side processing
    prescriptionsTable = $('#prescriptionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('prescription/get-prescriptions') ?>',
            type: 'POST',
            data: function(d) {
                // Add CSRF token to body
                d[window.csrfConfig.name] = window.getCsrfToken();
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
                
                let errorMessage = prescriptionI18n.errorLoadingData;
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || prescriptionI18n.unknownError;
                }
                
                // Show error message
                const tbody = document.getElementById('prescriptionsTableBody');
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
                data: 'prescription_id',
                name: 'prescription_id',
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    const identityHtml = `
                            <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-prescription-bottle-alt text-teal-600 text-sm"></i>
                                </div>
                                <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500">${prescriptionI18n.prescriptionId}</div>
                                </div>
                            </div>
                    `;
                    if (permissions.prescriptions.view) {
                        return `
                            <a href="<?= base_url('prescription') ?>/${row.id}" class="block rounded-md hover:bg-teal-50/60 transition-colors duration-200 cursor-pointer">
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
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                    <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                                </span>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">${data}</div>
                                    <div class="text-xs text-gray-500">${row.patient_number || row.phone || prescriptionI18n.noPhone}</div>
                                </div>
                            </div>
                    `;
                }
            },
            { 
                data: 'medication_name',
                name: 'medication_name',
                render: function(data, type, row) {
                    try {
                        const medicines = JSON.parse(data || '[]');
                        if (medicines.length > 0) {
                            return `
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-pills text-teal-600 text-xs"></i>
                                    <span class="text-sm text-gray-900">${medicines.length} ${medicines.length > 1 ? prescriptionI18n.medicinePlural : prescriptionI18n.medicineSingular}</span>
                                </div>
                            `;
                        }
                    } catch (e) {
                        // If not JSON, treat as single medicine
                        if (data) {
                            return `
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-pills text-teal-600 text-xs"></i>
                                    <span class="text-sm text-gray-900">${prescriptionI18n.oneMedicine}</span>
            </div>
        `;
                        }
                    }
                    return `<span class="text-gray-400 text-sm">${prescriptionI18n.noMedicines}</span>`;
                }
            },
            { 
                data: 'prescribed_date',
                name: 'prescribed_date',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-calendar-alt text-teal-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${row.prescribed_date_formatted || data}</span>
                        </div>
                    `;
                }
            },
            { 
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'bg-green-100 text-green-800',
                        'expired': 'bg-red-100 text-red-800',
                        'cancelled': 'bg-gray-100 text-gray-800',
                        'pending': 'bg-yellow-100 text-yellow-800'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'expired': 'fas fa-times-circle',
                        'cancelled': 'fas fa-ban',
                        'pending': 'fas fa-clock'
                    };
                    const colorClass = statusColors[data] || 'bg-gray-100 text-gray-800';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    
                    return `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            <i class="${iconClass} mr-1"></i>
                            ${prescriptionI18n.statusLabels[data] || prescriptionI18n.statusLabels.pending}
                        </span>
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
                    if (permissions.prescriptions.view) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}" 
                               class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="${prescriptionI18n.viewDetails}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Print button
                    if (permissions.prescriptions.print) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}/print" 
                               class="p-2 text-teal-600 hover:text-teal-700 rounded-md hover:bg-teal-50 transition-colors duration-200" title="${prescriptionI18n.printPrescription}">
                                <i class="fas fa-print"></i>
                            </a>`;
                    }
                    
                    // Edit button
                    if (permissions.prescriptions.edit) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}/edit" 
                               class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="${prescriptionI18n.editPrescription}">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }
                    
                    // Delete button
                    if (permissions.prescriptions.delete) {
                        actions += `
                            <button onclick="deletePrescription(${data})" 
                                    class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200" title="${prescriptionI18n.deletePrescription}">
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
                text: '<i class="fas fa-file-pdf mr-2"></i>' + prescriptionI18n.exportPdf,
                className: 'btn-pdf',
                title: prescriptionI18n.prescriptionsReport,
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Doctor, Date, Status, Notes, Actions
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i>' + prescriptionI18n.exportPrint,
                className: 'btn-print',
                title: prescriptionI18n.prescriptionsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Doctor, Date, Status, Notes, Actions
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i>' + prescriptionI18n.exportCsv,
                className: 'btn-csv',
                title: prescriptionI18n.prescriptionsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Doctor, Date, Status, Notes, Actions
                }
            }
        ],
        language: {
            processing: prescriptionI18n.dtProcessing,
            lengthMenu: "_MENU_",
            zeroRecords: prescriptionI18n.dtZeroRecords,
            info: prescriptionI18n.dtInfo,
            infoEmpty: prescriptionI18n.dtInfoEmpty,
            infoFiltered: prescriptionI18n.dtInfoFiltered,
            search: "",
            searchPlaceholder: prescriptionI18n.dtSearchPlaceholder,
            paginate: {
                first: prescriptionI18n.dtFirst,
                last: prescriptionI18n.dtLast,
                next: prescriptionI18n.dtNext,
                previous: prescriptionI18n.dtPrevious
            }
        },
        drawCallback: function(settings) {
            // Re-initialize tooltips if the function exists
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            // Apply custom styling after each draw
            applyDataTablesStyling();
            if (permissions.prescriptions.view) {
                $('#prescriptionsTable tbody tr').addClass('cursor-pointer hover:bg-teal-50/40 transition-colors duration-150');
            }
        }
    });

    if (permissions.prescriptions.view) {
        $('#prescriptionsTable tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('a, button, input, select, textarea, [onclick]').length) {
                return;
            }

            const rowData = prescriptionsTable.row(this).data();
            if (!rowData || !rowData.id) {
                return;
            }

            window.location.href = window.workflowContextUrl(`<?= base_url('prescription') ?>/${rowData.id}`);
        });
    }

    // Apply custom DataTables styling
    function applyDataTablesStyling() {
        // Style length selector
        $('.dataTables_length select').addClass('focus:ring-teal-500 focus:border-teal-500');
        
        // Style search input
        $('.dataTables_filter input').addClass('focus:ring-teal-500 focus:border-teal-500');
        
        // Style info text
        $('.dataTables_info').addClass('text-gray-600');
        
        // Style pagination buttons
        $('.dataTables_paginate .paginate_button').each(function() {
            if ($(this).hasClass('current')) {
                $(this).addClass('bg-teal-600 border-teal-600 text-white');
            } else {
                $(this).addClass('hover:bg-teal-50 hover:border-teal-300 hover:text-teal-700');
            }
        });
        
        // Style processing indicator
        $('.dataTables_processing').addClass('border-teal-200 text-teal-700');
        
        // Style labels
        $('.dataTables_length label, .dataTables_filter label').addClass('text-gray-700 font-semibold');
        
        // Style icons
        $('.dataTables_length i, .dataTables_filter i').addClass('text-teal-600');
    }

    // Load statistics
    loadPrescriptionStatistics();

    // Search functionality
    $('#searchInput').on('keyup', function() {
        prescriptionsTable.search(this.value).draw();
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        prescriptionsTable.draw();
    });
});

// Load prescription statistics
function loadPrescriptionStatistics() {
    $.ajax({
        url: '<?= base_url('prescription/stats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalPrescriptions').text(data.total || 0);
            $('#activePrescriptions').text(data.active || 0);
            $('#expiredPrescriptions').text(data.expired || 0);
            $('#monthlyPrescriptions').text(data.monthly || 0);
        },
        error: function() {
            $('#totalPrescriptions').text('0');
            $('#activePrescriptions').text('0');
            $('#expiredPrescriptions').text('0');
            $('#monthlyPrescriptions').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (prescriptionsTable) {
        prescriptionsTable.button('.btn-pdf').trigger();
    }
}

function printPrescriptions() {
    if (prescriptionsTable) {
        prescriptionsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (prescriptionsTable) {
        prescriptionsTable.button('.btn-csv').trigger();
    }
}

// Delete prescription function
function deletePrescription(prescriptionId) {
    confirmDelete('<?= base_url('prescription') ?>/' + prescriptionId, prescriptionI18n.deleteConfirm);
}

// Permissions object (you'll need to populate this from your backend)
var permissions = {
    prescriptions: {
        view: true,
        edit: true,
        delete: true,
        create: true,
        print: true
    }
};
</script>

<?= $this->endSection() ?>
