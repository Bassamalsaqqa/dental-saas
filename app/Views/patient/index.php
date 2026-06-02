<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$validation = session('validation');
if (!$validation instanceof \CodeIgniter\Validation\Validation) {
    $validation = null;
}
$shouldOpenPatientCreateModal = old('source_modal') === 'patient_quick_create';
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
.patient-create-modal {
    visibility: hidden;
    pointer-events: none;
    transition: visibility 0s linear 240ms;
}
.patient-create-modal.is-open {
    visibility: visible;
    pointer-events: auto;
    transition-delay: 0s;
}
.patient-create-modal__backdrop {
    opacity: 0;
    transition: opacity 220ms ease;
}
.patient-create-modal.is-open .patient-create-modal__backdrop {
    opacity: 1;
}
.patient-create-modal__panel {
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
.patient-create-modal.is-open .patient-create-modal__panel {
    opacity: 1;
    transform: scale(1);
}
.patient-create-modal .select2-container {
    width: 100% !important;
}
.patient-create-modal [data-medical-alert-item] {
    align-self: start;
}
@media (prefers-reduced-motion: reduce) {
    .patient-create-modal__backdrop,
    .patient-create-modal__panel {
        transition: none;
    }
}
@media (max-width: 640px) {
    .patient-create-modal__shell {
        align-items: stretch;
        padding: 0;
    }
    .patient-create-modal__panel {
        width: 100vw;
        height: 100dvh;
        min-height: 100dvh;
        max-height: 100dvh;
        border-radius: 0;
    }
}
</style>

<!-- Enhanced Patient Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-3 sm:px-4 py-4 sm:py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-center">
                    <div class="flex items-start sm:items-center space-x-3 sm:space-x-4">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-users text-lg sm:text-xl"></i>
                            </div>
                            <div>
                            <h1 class="text-xl sm:text-3xl font-bold text-gray-900"><?= esc(lang('UI.patientManagementTitle')) ?></h1>
                            <p class="text-gray-600"><?= esc(lang('UI.patientManagementSubtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-3">
                        <?php if (has_permission('patients', 'create')): ?>
                            <a href="<?= base_url('patient/create') ?>" id="newPatientModalTrigger" data-patient-create-modal-open class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus me-2"></i>
                                <?= esc(lang('UI.patientNewPatient')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 items-stretch">
                <!-- Total Patients Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-teal-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                                <span class="text-emerald-700 font-bold text-xs">+8%</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('UI.patientTotalPatients')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-900" id="totalPatients">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-emerald-200 to-teal-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('UI.dashboardVsLastMonth')) ?></p>
                        </div>
                    </div>
            </div>

                <!-- Active Patients Card -->
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
                                <span class="text-green-700 font-bold text-sm"><?= esc(lang('UI.patientStatusActive')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('UI.patientActivePatients')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-green-900" id="activePatients">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('UI.patientCurrentlyActive')) ?></p>
                        </div>
                        </div>
                    </div>

                <!-- New Patients Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-user-plus text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-cyan-100 px-4 py-2 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-star text-blue-600 text-sm"></i>
                                <span class="text-blue-700 font-bold text-sm"><?= esc(lang('UI.patientNewPatients')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('UI.patientNewPatients')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-blue-900" id="newPatients">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-blue-200 to-cyan-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('UI.patientThisMonthShort')) ?></p>
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
                                <span class="text-purple-700 font-bold text-sm"><?= esc(lang('UI.patientThisMonth')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('UI.patientThisMonth')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-purple-900" id="monthlyPatients">
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

        <!-- Standardized Patients Table -->
        <div class="mb-8">
            <!-- Table View -->
            <div id="tableView" class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('UI.patientPatientRegistry')) ?></h3>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                PDF
                            </button>
                            <button onclick="printPatients()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                <?= esc(lang('UI.patientPrint')) ?>
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
                        <table id="patientsTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-id-card text-emerald-600"></i>
                                            <span><?= esc(lang('UI.patientPatientId')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-emerald-600"></i>
                                            <span><?= esc(lang('UI.patientName')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-phone text-emerald-600"></i>
                                            <span><?= esc(lang('UI.patientPhone')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-envelope text-emerald-600"></i>
                                            <span><?= esc(lang('UI.patientEmail')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-info-circle text-emerald-600"></i>
                                            <span><?= esc(lang('UI.patientStatus')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-emerald-600"></i>
                                            <span><?= esc(lang('UI.patientCreated')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-cogs text-emerald-600"></i>
                                            <span><?= esc(lang('UI.patientActions')) ?></span>
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

<?php if (has_permission('patients', 'create')): ?>
    <div id="newPatientModal" class="app-scale-modal patient-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPatientModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="patient-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-patient-create-modal-close></div>
        <div class="patient-create-modal__shell relative z-10 flex h-full items-center justify-center p-4" data-patient-create-modal-close>
            <form id="newPatientQuickCreateForm" action="<?= base_url('patient/store') ?>" method="POST" class="patient-create-modal__panel flex w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-patient-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="patients">
                <input type="hidden" name="source_modal" value="patient_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div>
                            <h2 id="newPatientModalTitle" class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientNewPatient')) ?></h2>
                            <a href="<?= base_url('patient/create?return_to=patients') ?>" class="mt-1 inline-flex text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-patient-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-5">
                    <?php if (session('error') && $shouldOpenPatientCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?= view('patient/partials/create_form_fields', [
                        'validation' => $validation,
                        'is_rtl' => $isRtl,
                        'sectionClass' => 'rounded-2xl border border-slate-200 bg-white p-5 md:p-6 shadow-sm',
                    ]) ?>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-patient-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('UI.cancel')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_only" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-5 py-2.5 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50 hover:shadow-md">
                        <i class="fas fa-user-plus me-2"></i>
                        <?= esc(lang('UI.settingsSave')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_and_book" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-emerald-700 hover:to-teal-700 hover:shadow-xl">
                        <i class="fas fa-calendar-plus me-2"></i>
                        <?= esc(lang('UI.patientSaveAndBookAppointment')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?= view('examination/partials/quick_create_modal', [
    'is_rtl' => $isRtl,
    'return_to' => 'patients',
    'contextual' => false,
    'selected_patient' => null,
]) ?>

<?= view('treatment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => 'patients',
    'contextual' => false,
    'selected_patient' => null,
    'patients' => [],
    'treatment_types' => $patient_index_treatment_types ?? [],
    'inventory_items' => $patient_index_inventory_items ?? [],
    'clinic_providers' => $patient_index_clinic_providers ?? [],
    'validation' => session()->getFlashdata('validation'),
]) ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
let newPatientModalTrigger = null;
let newPatientModalCloseTimer = null;

function mountNewPatientModal() {
    const modal = document.getElementById('newPatientModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    if (modal && modal.dataset.closeControlsBound !== '1') {
        modal.dataset.closeControlsBound = '1';
        modal.querySelectorAll('button[data-patient-create-modal-close], a[data-patient-create-modal-close]').forEach(function (control) {
            control.addEventListener('click', function (event) {
                event.preventDefault();
                closeNewPatientModal();
            });
        });
    }
    return modal;
}

function initializeNewPatientModalContent(modal) {
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
    if (typeof window.initPatientCreateForms === 'function') {
        window.initPatientCreateForms(modal);
    }
    return Promise.allSettled(tasks);
}

function resetNewPatientModalForm(modal) {
    const form = modal ? modal.querySelector('[data-patient-create-form]') : null;
    if (!form) {
        return;
    }

    form.reset();
    form.querySelectorAll('select').forEach(function(field) {
        if (window.jQuery && window.jQuery(field).data('select2')) {
            window.jQuery(field).val('').trigger('change');
        } else {
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function(field) {
        field.dispatchEvent(new Event('change', { bubbles: true }));
    });
    if (typeof form.__syncPatientCreateForm === 'function') {
        form.__syncPatientCreateForm();
    } else if (typeof window.initPatientCreateForms === 'function') {
        window.initPatientCreateForms(form);
    }
}

function openNewPatientModal(trigger) {
    const modal = mountNewPatientModal();
    if (!modal) {
        return;
    }
    newPatientModalTrigger = trigger || document.activeElement;
    if (newPatientModalCloseTimer) {
        clearTimeout(newPatientModalCloseTimer);
        newPatientModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function() {
        modal.classList.add('is-open');
    });
    const firstField = modal.querySelector('#first_name');
    if (firstField && typeof firstField.focus === 'function') {
        firstField.focus({ preventScroll: true });
    }
    window.setTimeout(function() {
        initializeNewPatientModalContent(modal);
    }, 80);
    window.setTimeout(function() {
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus();
        }
    }, 100);
}

function closeNewPatientModal() {
    const modal = document.getElementById('newPatientModal');
    if (!modal || modal.classList.contains('hidden')) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    newPatientModalCloseTimer = setTimeout(function() {
        modal.classList.add('hidden');
        resetNewPatientModalForm(modal);
        if (newPatientModalTrigger && typeof newPatientModalTrigger.focus === 'function') {
            newPatientModalTrigger.focus();
        }
        newPatientModalTrigger = null;
    }, 240);
}

document.addEventListener('click', function(event) {
    const openTrigger = event.target.closest('[data-patient-create-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openNewPatientModal(openTrigger);
        return;
    }

    const closeTrigger = event.target.closest('[data-patient-create-modal-close]');
    if (closeTrigger) {
        event.preventDefault();
        closeNewPatientModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key !== 'Escape') {
        return;
    }
    const modal = document.getElementById('newPatientModal');
    if (modal && !modal.classList.contains('hidden')) {
        event.preventDefault();
        closeNewPatientModal();
    }
});

document.addEventListener('click', function(event) {
    const examinationTrigger = event.target.closest('[data-examination-create-modal-open]');
    if (examinationTrigger && document.getElementById('newExaminationModal') && typeof window.openNewExaminationModal === 'function') {
        event.preventDefault();
        event.stopPropagation();
        window.openNewExaminationModal(examinationTrigger);
        return;
    }

    const treatmentTrigger = event.target.closest('[data-treatment-create-modal-open]');
    if (treatmentTrigger && document.getElementById('newTreatmentModal') && typeof window.openTreatmentModal === 'function') {
        event.preventDefault();
        event.stopPropagation();
        window.openTreatmentModal(treatmentTrigger);
    }
}, true);
document.addEventListener('DOMContentLoaded', function() {
    if (<?= $shouldOpenPatientCreateModal ? 'true' : 'false' ?>) {
        openNewPatientModal(document.getElementById('newPatientModalTrigger'));
    }
});

// Global variables
let patientsTable;
const csrfMetaName = document.querySelector('meta[name="csrf-name"]');
const csrfMetaToken = document.querySelector('meta[name="csrf-token"]');
const csrfCookieName = '<?= esc(config('Security')->cookieName) ?>';
const patientI18n = {
    sessionExpired: '<?= esc(lang('UI.patientSessionExpired'), 'js') ?>',
    networkError: '<?= esc(lang('UI.patientNetworkError'), 'js') ?>',
    loadingError: '<?= esc(lang('UI.patientLoadingError'), 'js') ?>',
    noPhone: '<?= esc(lang('UI.patientNoPhone'), 'js') ?>',
    noEmail: '<?= esc(lang('UI.patientNoEmail'), 'js') ?>',
    na: '<?= esc(lang('UI.patientNa'), 'js') ?>',
    unknown: '<?= esc(lang('UI.patientUnknown'), 'js') ?>',
    statusActive: '<?= esc(lang('UI.patientStatusActive'), 'js') ?>',
    statusInactive: '<?= esc(lang('UI.patientStatusInactive'), 'js') ?>',
    statusPending: '<?= esc(lang('UI.patientStatusPending'), 'js') ?>',
    patientId: '<?= esc(lang('UI.patientPatientId'), 'js') ?>',
    patientName: '<?= esc(lang('UI.patientName'), 'js') ?>',
    viewPatient: '<?= esc(lang('UI.patientViewPatient'), 'js') ?>',
    editPatient: '<?= esc(lang('UI.patientEditPatient'), 'js') ?>',
    odontogram: '<?= esc(lang('UI.patientOdontogram'), 'js') ?>',
    newExamination: '<?= esc(lang('UI.newExamination'), 'js') ?>',
    newTreatment: '<?= esc(lang('UI.patientNewTreatment'), 'js') ?>',
    deletePatient: '<?= esc(lang('UI.patientDeletePatient'), 'js') ?>',
    reportTitle: '<?= esc(lang('UI.patientReportTitle'), 'js') ?>',
    loading: '<?= esc(lang('UI.patientLoading'), 'js') ?>',
    noRecords: '<?= esc(lang('UI.patientNoRecords'), 'js') ?>',
    showing: '<?= esc(lang('UI.patientShowing'), 'js') ?>',
    infoEmpty: '<?= esc(lang('UI.patientInfoEmpty'), 'js') ?>',
    infoFiltered: '<?= esc(lang('UI.patientInfoFiltered'), 'js') ?>',
    searchPlaceholder: '<?= esc(lang('UI.patientSearchPlaceholder'), 'js') ?>',
    paginateFirst: '<?= esc(lang('UI.patientPaginateFirst'), 'js') ?>',
    paginateLast: '<?= esc(lang('UI.patientPaginateLast'), 'js') ?>',
    paginateNext: '<?= esc(lang('UI.patientPaginateNext'), 'js') ?>',
    paginatePrevious: '<?= esc(lang('UI.patientPaginatePrevious'), 'js') ?>',
    deleteConfirm: '<?= esc(lang('UI.patientDeleteConfirm'), 'js') ?>'
};

function escapeHtmlAttribute(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function getCookieValue(name) {
    if (!name) {
        return '';
    }

    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
        const [key, ...valueParts] = cookie.trim().split('=');
        if (key === name) {
            return decodeURIComponent(valueParts.join('='));
        }
    }

    return '';
}

function getCsrfName() {
    return csrfMetaName ? csrfMetaName.getAttribute('content') : '<?= csrf_token() ?>';
}

function getCsrfToken() {
    return csrfMetaToken ? csrfMetaToken.getAttribute('content') : '';
}

function refreshCsrfToken() {
    const cookieToken = getCookieValue(csrfCookieName);
    if (cookieToken && csrfMetaToken) {
        csrfMetaToken.setAttribute('content', cookieToken);
    }
}

$(document).ready(function() {
    // Check if table exists before initializing DataTable
    if ($('#patientsTable').length === 0) {
        console.error('Table #patientsTable not found');
        return;
    }
    
    // Initialize DataTable with server-side processing
    patientsTable = $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?= base_url('patient/get-data') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                const csrfToken = getCsrfToken();
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }
            },
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.search_term = $('#searchInput').val();
                const csrfName = getCsrfName();
                const csrfToken = getCsrfToken();
                if (csrfName && csrfToken) {
                    d[csrfName] = csrfToken;
                }
                return d;
            },
            complete: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token && csrfMetaToken) {
                    csrfMetaToken.setAttribute('content', xhr.responseJSON.csrf_token);
                }
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);
                
                if (xhr.responseJSON && xhr.responseJSON.csrf_token && csrfMetaToken) {
                    csrfMetaToken.setAttribute('content', xhr.responseJSON.csrf_token);
                }
                
                // Check if it's an authentication error
                if (xhr.status === 401 || xhr.status === 403) {
                    alert(patientI18n.sessionExpired);
                    window.location.reload();
                } else if (xhr.status === 0) {
                    alert(patientI18n.networkError);
                } else {
                    alert(patientI18n.loadingError);
                }
            }
        },
        columns: [
            { 
                data: 'patient_id',
                name: 'patient_id',
                render: function(data, type, row) {
                    return `
                        <div>
                            <div class="text-sm font-medium text-gray-900">${data || patientI18n.na}</div>
                            <div class="text-xs text-gray-500">${patientI18n.patientId}</div>
                        </div>
                    `;
                }
            },
            { 
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    const gender = String(row.gender || 'other').toLowerCase();
                    const avatarMap = {
                        male: {
                            icon: 'fas fa-person',
                            shell: 'bg-blue-50 border border-blue-100',
                            iconColor: 'text-blue-700'
                        },
                        female: {
                            icon: 'fas fa-person-dress',
                            shell: 'bg-rose-50 border border-rose-100',
                            iconColor: 'text-rose-700'
                        },
                        other: {
                            icon: 'fas fa-user',
                            shell: 'bg-slate-100 border border-slate-200',
                            iconColor: 'text-slate-600'
                        }
                    };
                    const avatar = avatarMap[gender] || avatarMap.other;
                    return `
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                <i class="${avatar.icon} ${avatar.iconColor} text-2xl"></i>
                            </span>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data || patientI18n.na}</div>
                                <div class="text-xs text-gray-500">${patientI18n.patientName}</div>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'phone',
                name: 'phone',
                render: function(data, type, row) {
                    return data ? `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-phone text-emerald-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${data}</span>
                        </div>
                    ` : `<span class="text-gray-400 text-sm">${patientI18n.noPhone}</span>`;
                }
            },
            { 
                data: 'email',
                name: 'email',
                render: function(data, type, row) {
                    return data ? `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-emerald-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${data}</span>
                        </div>
                    ` : `<span class="text-gray-400 text-sm">${patientI18n.noEmail}</span>`;
                }
            },
            { 
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'bg-green-100 text-green-800',
                        'inactive': 'bg-gray-100 text-gray-800',
                        'pending': 'bg-yellow-100 text-yellow-800'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'inactive': 'fas fa-times-circle',
                        'pending': 'fas fa-clock'
                    };
                    const statusLabels = {
                        'active': patientI18n.statusActive,
                        'inactive': patientI18n.statusInactive,
                        'pending': patientI18n.statusPending
                    };
                    const colorClass = statusColors[data] || 'bg-gray-100 text-gray-800';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    const label = statusLabels[data] || patientI18n.unknown;
                    
                    return `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            <i class="${iconClass} mr-1"></i>
                            ${label}
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
                            <i class="fas fa-calendar text-emerald-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${data || patientI18n.na}</span>
                        </div>
                    `;
                }
            },
            { 
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = '<div class="flex items-center space-x-2">';

                    // View button
                    if (permissions.patients.view) {
                        actions += `
                            <a href="<?= base_url('patient') ?>/${data}"
                               class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="${patientI18n.viewPatient}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.patients.edit) {
                        actions += `
                            <a href="<?= base_url('patient') ?>/${data}/edit"
                               class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="${patientI18n.editPatient}">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }

                    // Odontogram button
                    actions += `
                        <a href="<?= base_url('odontogram') ?>/${data}"
                           class="p-2 text-purple-600 hover:text-purple-700 rounded-md hover:bg-purple-50 transition-colors duration-200" title="${patientI18n.odontogram}">
                            <i class="fas fa-tooth"></i>
                        </a>`;

                    const patientContextLabel = row.name || row.patient_id || '';

                    // New examination button
                    if (permissions.examinations.create) {
                        actions += `
                            <a href="<?= base_url('examination/create?patient_id=') ?>${data}&return_to=patients"
                               data-examination-create-modal-open
                               data-context-patient-id="${data}"
                               data-context-patient-name="${escapeHtmlAttribute(patientContextLabel)}"
                               data-context-patient-code="${escapeHtmlAttribute(row.patient_id || '')}"
                               data-context-patient-gender="${escapeHtmlAttribute(row.gender || '')}"
                               class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="${patientI18n.newExamination}">
                                <i class="fas fa-stethoscope"></i>
                            </a>`;
                    }

                    // New treatment button
                    if (permissions.treatments.create) {
                        actions += `
                            <a href="<?= base_url('treatment/create?patient_id=') ?>${data}&return_to=patients"
                               data-treatment-create-modal-open
                               data-context-patient-id="${data}"
                               data-context-patient-name="${escapeHtmlAttribute(patientContextLabel)}"
                               data-context-patient-code="${escapeHtmlAttribute(row.patient_id || '')}"
                               class="p-2 text-cyan-600 hover:text-cyan-700 rounded-md hover:bg-cyan-50 transition-colors duration-200" title="${patientI18n.newTreatment}">
                                <i class="fas fa-briefcase-medical"></i>
                            </a>`;
                    }
                    
                    // Delete button
                    if (permissions.patients.delete) {
                        actions += `
                            <button onclick="deletePatient(${data})" 
                                    class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200" title="${patientI18n.deletePatient}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }
                    
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        createdRow: function(row, data) {
            if (!permissions.patients.view) {
                return;
            }

            const profileUrl = `<?= base_url('patient') ?>/${data.id}`;
            const contextualProfileUrl = window.workflowContextUrl ? window.workflowContextUrl(profileUrl) : profileUrl;

            $(row)
                .addClass('cursor-pointer hover:bg-emerald-50/50 transition-colors duration-150')
                .attr('data-href', contextualProfileUrl)
                .attr('tabindex', '0')
                .attr('role', 'link')
                .attr('aria-label', `${patientI18n.viewPatient}: ${data.name || data.patient_id || ''}`)
                .on('click', function(event) {
                    if ($(event.target).closest('a, button').length) {
                        return;
                    }
                    window.location.href = contextualProfileUrl;
                })
                .on('keydown', function(event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        if ($(event.target).closest('a, button').length) {
                            return;
                        }
                        event.preventDefault();
                        window.location.href = contextualProfileUrl;
                    }
                });

            $('a, button', row).on('click', function(event) {
                event.stopPropagation();
            });
        },
        dom: 'B<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                className: 'btn-pdf',
                title: patientI18n.reportTitle,
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i>Print',
                className: 'btn-print',
                title: patientI18n.reportTitle,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i>CSV',
                className: 'btn-csv',
                title: patientI18n.reportTitle,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            }
        ],
        language: {
            processing: patientI18n.loading,
            lengthMenu: "_MENU_",
            zeroRecords: patientI18n.noRecords,
            info: patientI18n.showing,
            infoEmpty: patientI18n.infoEmpty,
            infoFiltered: patientI18n.infoFiltered,
            search: "",
            searchPlaceholder: patientI18n.searchPlaceholder,
            paginate: {
                first: patientI18n.paginateFirst,
                last: patientI18n.paginateLast,
                next: patientI18n.paginateNext,
                previous: patientI18n.paginatePrevious
            }
        },
        drawCallback: function(settings) {
            console.log('Patients DataTable draw callback triggered');
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
        $('.dataTables_length select').addClass('focus:ring-emerald-500 focus:border-emerald-500');
        
        // Style search input
        $('.dataTables_filter input').addClass('focus:ring-emerald-500 focus:border-emerald-500');
        
        // Style info text
        $('.dataTables_info').addClass('text-gray-600');
        
        // Style pagination buttons
        $('.dataTables_paginate .paginate_button').each(function() {
            if ($(this).hasClass('current')) {
                $(this).addClass('bg-emerald-600 border-emerald-600 text-white');
            } else {
                $(this).addClass('hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700');
            }
        });
        
        // Style processing indicator
        $('.dataTables_processing').addClass('border-emerald-200 text-emerald-700');
        
        // Style labels
        $('.dataTables_length label, .dataTables_filter label').addClass('text-gray-700 font-semibold');
        
        // Style icons
        $('.dataTables_length i, .dataTables_filter i').addClass('text-emerald-600');
    }

    // Load statistics
    loadPatientStatistics();

    // Search functionality
    $('#searchInput').on('keyup', function() {
        patientsTable.search(this.value).draw();
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        patientsTable.draw();
    });
    
    // Add a fallback if the table fails to load
    setTimeout(function() {
        if (patientsTable && patientsTable.data().count() === 0) {
            console.log('Table appears empty, checking for errors...');
            // You could add fallback logic here if needed
        }
    }, 5000);
});

// Load patient statistics
function loadPatientStatistics() {
    $.ajax({
        url: '<?= base_url('patient/get-statistics') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalPatients').text(data.total_patients || 0);
            $('#activePatients').text(data.active_patients || 0);
            $('#newPatients').text(data.new_patients || 0);
            $('#monthlyPatients').text(data.monthly_patients || 0);
        },
        error: function() {
            $('#totalPatients').text('0');
            $('#activePatients').text('0');
            $('#newPatients').text('0');
            $('#monthlyPatients').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (patientsTable) {
        patientsTable.button('.btn-pdf').trigger();
    }
}

function printPatients() {
    if (patientsTable) {
        patientsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (patientsTable) {
        patientsTable.button('.btn-csv').trigger();
    }
}

// Delete patient function
function deletePatient(patientId) {
    confirmDelete('<?= base_url('patient') ?>/' + patientId, patientI18n.deleteConfirm);
}

// Permissions mirrored from backend so visible row actions have matching modal support
var permissions = {
    patients: {
        view: <?= has_permission('patients', 'view') ? 'true' : 'false' ?>,
        edit: <?= has_permission('patients', 'edit') ? 'true' : 'false' ?>,
        delete: <?= has_permission('patients', 'delete') ? 'true' : 'false' ?>,
        create: <?= has_permission('patients', 'create') ? 'true' : 'false' ?>
    },
    examinations: {
        create: <?= has_permission('examinations', 'create') ? 'true' : 'false' ?>
    },
    treatments: {
        create: <?= has_permission('treatments', 'create') ? 'true' : 'false' ?>
    }
};
</script>

<?= view('patient/partials/create_form_script') ?>

<?= $this->endSection() ?>
