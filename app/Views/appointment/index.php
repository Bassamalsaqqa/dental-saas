<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$currentUri = service('request')->getUri();
$returnToContext = ltrim($currentUri->getPath(), '/');
$currentQuery = $currentUri->getQuery();
if ($currentQuery !== '') {
    $returnToContext .= '?' . $currentQuery;
}
$validation = $validation ?? session('validation');
if (!$validation instanceof \CodeIgniter\Validation\Validation) {
    $validation = null;
}
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
$appointmentRows = array_values($appointments ?? []);
$statusCounts = [
    'scheduled' => 0,
    'confirmed' => 0,
    'arrived' => 0,
    'ready' => 0,
    'in_chair' => 0,
    'checkout' => 0,
    'completed' => 0,
    'cancelled' => 0,
    'no_show' => 0,
];
$nowTimestamp = time();
$nextAppointment = null;
foreach ($appointmentRows as $row) {
    $rowStatus = (string) ($row['status'] ?? 'scheduled');
    if (array_key_exists($rowStatus, $statusCounts)) {
        $statusCounts[$rowStatus]++;
    }

    $appointmentTimestamp = strtotime((string) ($row['appointment_date'] ?? '') . ' ' . (string) ($row['appointment_time'] ?? ''));
    if ($appointmentTimestamp !== false && $appointmentTimestamp >= $nowTimestamp) {
        if ($nextAppointment === null || $appointmentTimestamp < $nextAppointment['timestamp']) {
            $nextAppointment = ['timestamp' => $appointmentTimestamp, 'appointment' => $row];
        }
    }
}
$viewAllMode = isset($view_mode) && $view_mode === 'all';
$activeCount = $statusCounts['scheduled'] + $statusCounts['confirmed'] + $statusCounts['arrived']
    + $statusCounts['ready'] + $statusCounts['in_chair'] + $statusCounts['checkout'];
$doneCount = $statusCounts['completed'];
$riskCount = $statusCounts['cancelled'] + $statusCounts['no_show'];
$nextAppointmentLabel = lang('UI.dashboardNoUpcomingAppointments');
if ($nextAppointment) {
    $nextRow = $nextAppointment['appointment'];
    $nextAppointmentLabel = trim((string) (($nextRow['first_name'] ?? '') . ' ' . ($nextRow['last_name'] ?? '')));
}
$bookingContext = $booking_context ?? null;
if ($bookingContext === null && class_exists(\App\Services\ProviderDirectoryService::class)) {
    $activeClinicId = (int) (session('active_clinic_id') ?? 0);
    if ($activeClinicId > 0) {
        $providerService = new \App\Services\ProviderDirectoryService();
        $isDynamic = false;
        try {
            $isDynamic = $providerService->isEnabled($activeClinicId);
            $bookingContext = $providerService->getBookingContext($activeClinicId);
        } catch (\Throwable $e) {
            if ($isDynamic || (strpos($e->getMessage(), 'DYNAMIC_SCHEDULING') !== false)) {
                $bookingContext = [
                    'enabled' => true,
                    'schema_ready' => false,
                    'providers' => [],
                    'chair_options' => [],
                    'error' => $e->getMessage(),
                ];
            } else {
                $bookingContext = null;
            }
        }
    }
}
$dynamicMode = !empty($bookingContext['enabled']);
?>
<style>
    .appointment-page {
        font-family: var(--app-font-sans);
    }

    [dir="rtl"] .appointment-page {
        font-family: var(--app-font-ar);
    }

    .appointment-panel {
        border-radius: 1.25rem;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 42px -34px rgba(15, 23, 42, 0.42);
    }
</style>

<div class="appointment-page min-h-screen">
    <div class="w-full">
        <div class="w-full px-3 pt-3 sm:px-4 lg:px-5">
            <div class="overflow-hidden rounded-2xl border border-blue-900/10 bg-slate-950 shadow-xl shadow-blue-900/16">
                <div class="relative p-4 sm:p-5">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_12%,rgba(59,130,246,.5),transparent_30%),radial-gradient(circle_at_84%_10%,rgba(16,185,129,.34),transparent_25%),linear-gradient(135deg,#0f3f90_0%,#1656c1_54%,#0f7cc1_100%)]"></div>
                    <div class="relative grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(440px,.9fr)] xl:items-center">
                            <div class="flex min-w-0 items-start gap-4">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg ring-1 ring-white/20 backdrop-blur">
                                    <i class="fas fa-calendar-check text-2xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-200"><?= $viewAllMode ? lang('UI.appointmentAllAppointments') : lang('UI.appointmentTodaySchedule') ?></p>
                                    <h1 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl"><?= lang('UI.appointmentManagement') ?></h1>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                <div class="rounded-2xl bg-white/15 p-3 ring-1 ring-white/15 backdrop-blur">
                                    <p class="text-[11px] font-black uppercase tracking-wide text-blue-100"><?= lang('UI.appointmentTotal') ?></p>
                                    <p class="mt-1 text-2xl font-black text-white"><?= count($appointmentRows) ?></p>
                                </div>
                                <div class="rounded-2xl bg-emerald-400/20 p-3 ring-1 ring-emerald-200/25 backdrop-blur">
                                    <p class="text-[11px] font-black uppercase tracking-wide text-emerald-100"><?= lang('UI.appointmentActive') ?></p>
                                    <p class="mt-1 text-2xl font-black text-white"><?= $activeCount ?></p>
                                </div>
                                <div class="rounded-2xl bg-cyan-400/20 p-3 ring-1 ring-cyan-200/20 backdrop-blur">
                                    <p class="text-[11px] font-black uppercase tracking-wide text-cyan-100"><?= lang('UI.patientTreatmentStatusCompleted') ?></p>
                                    <p class="mt-1 text-2xl font-black text-white"><?= $doneCount ?></p>
                                </div>
                                <div class="rounded-2xl bg-rose-400/20 p-3 ring-1 ring-rose-200/25 backdrop-blur">
                                    <p class="text-[11px] font-black uppercase tracking-wide text-rose-100"><?= lang('UI.appointmentStatusCancelled') ?></p>
                                    <p class="mt-1 text-2xl font-black text-white"><?= $riskCount ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 xl:min-w-[31rem]">
                            <div class="rounded-2xl bg-white/14 p-3 ring-1 ring-white/20 backdrop-blur">
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-100"><?= lang('UI.patientNextAppointment') ?></p>
                                <p class="mt-1 truncate text-lg font-black text-white"><?= esc($nextAppointmentLabel) ?></p>
                                <?php if ($nextAppointment): ?>
                                    <?php $nextRow = $nextAppointment['appointment']; ?>
                                    <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-black">
                                        <span class="rounded-full bg-white/16 px-2.5 py-1 text-white ring-1 ring-white/18" dir="ltr"><?= esc(formatDate($nextRow['appointment_date'] ?? '')) ?></span>
                                        <span class="rounded-full bg-white/16 px-2.5 py-1 text-white ring-1 ring-white/18" dir="ltr"><?= esc(formatTime($nextRow['appointment_time'] ?? '')) ?></span>
                                        <span class="rounded-full bg-white/16 px-2.5 py-1 text-white ring-1 ring-white/18"><?= esc($nextRow['chair_name'] ?? lang('UI.appointmentChairDefault')) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <?php if (has_permission('appointments', 'create')): ?>
                                    <a href="<?= base_url('appointment/create') ?>" id="newAppointmentModalTrigger" data-appointment-create-modal-open class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-blue-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-blue-50">
                                        <i class="fas fa-plus"></i>
                                        <span><?= lang('UI.appointmentNew') ?></span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= base_url('appointment/calendar') ?>" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/20">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= lang('UI.appointmentCalendarView') ?></span>
                                </a>
                                <?php if ($viewAllMode): ?>
                                    <a href="<?= base_url('appointment') ?>" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/20">
                                        <i class="fas fa-calendar-day"></i>
                                        <span><?= lang('UI.appointmentDailyView') ?></span>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url('appointment') ?>?view=all" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/20">
                                        <i class="fas fa-list"></i>
                                        <span><?= lang('UI.appointmentViewAll') ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div class="w-full px-3 sm:px-4 lg:px-5">
            <!-- Success Message -->
            <?php if (session()->getFlashdata('success')): ?> 
                <noscript><div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm" role="status" aria-live="polite">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800"><?= lang('UI.appointmentSuccessTitle') ?></h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p><?= session()->getFlashdata('success') ?></p>
                            </div>
                        </div>
                    </div>
                </div></noscript>
            <?php endif; ?>

            <!-- Error Flash Message -->
            <?php if (session()->getFlashdata('error')): ?>
                <noscript><div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm" role="alert" aria-live="assertive">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800"><?= lang('UI.appointmentErrorTitle') ?></h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p><?= session()->getFlashdata('error') ?></p>
                            </div>
                        </div>
                    </div>
                </div></noscript>
            <?php endif; ?>
        </div>

        <div class="w-full px-3 py-4 sm:px-4 lg:px-5">
        <!-- Enhanced Filtering Section with Advanced UI -->
        <?php if (!isset($view_mode) || $view_mode !== 'all'): ?>
        <div class="mb-4">
            <div class="appointment-panel">
                    <div class="p-5 pb-3 sm:p-6 sm:pb-4">
                        <div class="space-y-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
                                <h3 class="text-2xl font-black text-gray-900">
                                    <?= lang('UI.appointmentAdvancedFilters') ?>
                                </h3>
                            </div>
                            <p class="text-gray-600 font-medium text-sm"><?= lang('UI.appointmentFilterSubtitle') ?></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 pt-0 md:grid-cols-2 xl:grid-cols-4 sm:p-6 sm:pt-0">
                        <!-- Date Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider"><?= lang('UI.appointmentSelectDate') ?></label>
                            <div class="relative">
                                    <input type="text" id="dateFilter" data-clinic-date="1" value="<?= $selected_date ?>" class="relative w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm hover:border-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition-all duration-300 font-medium">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider"><?= lang('UI.appointmentFilterByStatus') ?></label>
                            <div class="relative">
                                <select id="statusFilter" class="relative w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm hover:border-slate-300 focus:outline-none focus:ring-4 focus:ring-emerald-500/15 focus:border-emerald-500 transition-all duration-300 font-medium">
                                    <option value=""><?= lang('UI.appointmentAllStatuses') ?></option>
                                    <option value="scheduled" <?= ($selected_status ?? '') === 'scheduled' ? 'selected' : '' ?>><?= lang('UI.appointmentStatusScheduled') ?></option>
                                    <option value="confirmed" <?= ($selected_status ?? '') === 'confirmed' ? 'selected' : '' ?>><?= lang('UI.appointmentStatusConfirmed') ?></option>
                                    <option value="arrived" <?= ($selected_status ?? '') === 'arrived' ? 'selected' : '' ?>><?= lang('UI.appointmentStageArrived') ?></option>
                                    <option value="ready" <?= ($selected_status ?? '') === 'ready' ? 'selected' : '' ?>><?= lang('UI.appointmentStageReady') ?></option>
                                    <option value="in_chair" <?= ($selected_status ?? '') === 'in_chair' ? 'selected' : '' ?>><?= lang('UI.appointmentStageInChair') ?></option>
                                    <option value="checkout" <?= ($selected_status ?? '') === 'checkout' ? 'selected' : '' ?>><?= lang('UI.appointmentStageCheckout') ?></option>
                                    <option value="completed" <?= ($selected_status ?? '') === 'completed' ? 'selected' : '' ?>><?= lang('UI.patientTreatmentStatusCompleted') ?></option>
                                    <option value="cancelled" <?= ($selected_status ?? '') === 'cancelled' ? 'selected' : '' ?>><?= lang('UI.appointmentStatusCancelled') ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Search Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider"><?= lang('UI.appointmentSearchPatient') ?></label>
                            <div class="relative">
                                <input type="text" id="searchFilter" value="<?= htmlspecialchars($search_term ?? '') ?>" placeholder="<?= esc(lang('UI.appointmentSearchPatientHint')) ?>" class="relative w-full <?= !empty($is_rtl) ? 'pl-4 pr-11' : 'pl-4 pr-11' ?> py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm hover:border-slate-300 focus:outline-none focus:ring-4 focus:ring-amber-500/15 focus:border-amber-500 transition-all duration-300 font-medium">
                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider"><?= lang('UI.appointmentActions') ?></label>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button type="button" onclick="filterAppointments()" class="group/btn relative inline-flex flex-1 items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-filter mr-2 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10"><?= lang('UI.appointmentFilterAction') ?></span>
                                </button>
                                <button type="button" onclick="clearFilters()" class="group/btn relative inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white text-sm font-bold rounded-xl hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-gray-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-gray-500/20 to-slate-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-times mr-2 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10"><?= lang('UI.appointmentClearAction') ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search Section for View All Mode -->
        <?php if (isset($view_mode) && $view_mode === 'all'): ?>
        <div class="mb-4">
            <div class="appointment-panel">
                    <div class="p-5 pb-3 sm:p-6 sm:pb-4">
                        <div class="space-y-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full animate-pulse"></div>
                                <h3 class="text-2xl font-black text-gray-900"><?= lang('UI.appointmentSearchAppointments') ?></h3>
                            </div>
                            <p class="text-gray-600 font-medium text-sm"><?= lang('UI.appointmentSearchAppointmentsSubtitle') ?></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 pt-0 md:grid-cols-2 xl:grid-cols-3 sm:p-6 sm:pt-0">
                        <!-- Search Input -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider"><?= lang('UI.appointmentSearch') ?></label>
                            <div class="relative">
                                <input type="text" id="searchInput" placeholder="<?= esc(lang('UI.appointmentSearchInputPlaceholder')) ?>" 
                                       value="<?= $search_term ?>" 
                                       class="relative w-full <?= !empty($is_rtl) ? 'pl-4 pr-11' : 'pl-4 pr-11' ?> py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm hover:border-slate-300 focus:outline-none focus:ring-4 focus:ring-emerald-500/15 focus:border-emerald-500 transition-all duration-300 font-medium">
                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                    <i class="fas fa-search text-emerald-500" id="searchIcon"></i>
                                    <div class="hidden animate-spin rounded-full h-4 w-4 border-b-2 border-emerald-500" id="searchSpinner"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider"><?= lang('UI.appointmentStatusLabel') ?></label>
                            <div class="relative">
                                <select id="statusFilter" class="relative w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm hover:border-slate-300 focus:outline-none focus:ring-4 focus:ring-emerald-500/15 focus:border-emerald-500 transition-all duration-300 font-medium">
                                    <option value=""><?= lang('UI.appointmentAllStatuses') ?></option>
                                    <option value="scheduled" <?= $selected_status === 'scheduled' ? 'selected' : '' ?>><?= lang('UI.appointmentStatusScheduled') ?></option>
                                    <option value="confirmed" <?= $selected_status === 'confirmed' ? 'selected' : '' ?>><?= lang('UI.appointmentStatusConfirmed') ?></option>
                                    <option value="arrived" <?= $selected_status === 'arrived' ? 'selected' : '' ?>><?= lang('UI.appointmentStageArrived') ?></option>
                                    <option value="ready" <?= $selected_status === 'ready' ? 'selected' : '' ?>><?= lang('UI.appointmentStageReady') ?></option>
                                    <option value="in_chair" <?= $selected_status === 'in_chair' ? 'selected' : '' ?>><?= lang('UI.appointmentStageInChair') ?></option>
                                    <option value="checkout" <?= $selected_status === 'checkout' ? 'selected' : '' ?>><?= lang('UI.appointmentStageCheckout') ?></option>
                                    <option value="completed" <?= $selected_status === 'completed' ? 'selected' : '' ?>><?= lang('UI.patientTreatmentStatusCompleted') ?></option>
                                    <option value="cancelled" <?= $selected_status === 'cancelled' ? 'selected' : '' ?>><?= lang('UI.appointmentStatusCancelled') ?></option>
                                    <option value="no_show" <?= $selected_status === 'no_show' ? 'selected' : '' ?>><?= lang('UI.appointmentStatusNoShow') ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Search Actions -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider"><?= lang('UI.appointmentActions') ?></label>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button type="button" onclick="clearSearch()" class="group/btn relative inline-flex w-full items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white text-sm font-bold rounded-xl hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-gray-500/25 sm:w-auto">
                                    <div class="absolute inset-0 bg-gradient-to-r from-gray-500/20 to-slate-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-times mr-2 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10"><?= lang('UI.appointmentClearAction') ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($dynamicMode && !$viewAllMode): ?>
            <div class="mb-6">
                <?= view('appointment/partials/doctor_dayboard', [
                    'appointments' => $dayboard_appointments ?? $appointments ?? [],
                    'selected_date' => $selected_date ?? date('Y-m-d'),
                    'booking_context' => $bookingContext ?? null,
                ]) ?>
            </div>
        <?php endif; ?>

        <!-- Appointment schedule list -->
        <div class="mb-6">
            <div class="relative">
                <div class="appointment-panel overflow-hidden">
                    <div class="flex flex-col gap-4 p-6 pb-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-800 to-blue-900 text-white shadow-lg shadow-blue-900/20">
                                    <i class="fas fa-list-check"></i>
                                </span>
                                <h3 class="text-2xl font-black text-gray-900">
                                    <?php if (isset($view_mode) && $view_mode === 'all'): ?>
                                        <?= lang('UI.appointmentAllAppointments') ?>
                                    <?php else: ?>
                                        <?= lang('UI.appointmentForDate', ['date' => formatDate($selected_date)]) ?>
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <p class="text-gray-600 font-medium text-sm">
                                <?php if (isset($view_mode) && $view_mode === 'all'): ?>
                                    <?= lang('UI.appointmentAllAppointmentsSubtitle') ?>
                                <?php else: ?>
                                    <?= lang('UI.appointmentDailyAppointmentsSubtitle') ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2">
                                <div class="h-2 w-2 rounded-full bg-blue-600"></div>
                                <span class="text-blue-700 font-bold text-sm"><?= lang('UI.appointmentCountLabel', ['count' => count($appointments ?? [])]) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="appointment-records-list space-y-3 p-4 pt-0 sm:p-6 sm:pt-0">
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $index => $appointment): ?>
                                <?php
                                $appointmentGender = strtolower((string) ($appointment['gender'] ?? 'other'));
                                $appointmentAvatarIcon = 'fas fa-user';
                                $appointmentAvatarColor = 'text-slate-600';
                                if ($appointmentGender === 'male') {
                                    $appointmentAvatarIcon = 'fas fa-person';
                                    $appointmentAvatarColor = 'text-blue-700';
                                } elseif ($appointmentGender === 'female') {
                                    $appointmentAvatarIcon = 'fas fa-person-dress';
                                    $appointmentAvatarColor = 'text-rose-700';
                                }
                                ?>
                                <div class="appointment-record-card group/item relative flex flex-col gap-4 rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-slate-50/80 to-blue-50/45 p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 md:p-5 xl:flex-row xl:items-center xl:justify-between <?= has_permission('appointments', 'view') ? 'cursor-pointer' : '' ?>"<?= has_permission('appointments', 'view') ? ' data-href="' . esc(workflow_context_url('appointment/' . $appointment['id'])) . '"' : '' ?>>
                                    <!-- Patient Information -->
                                    <div class="flex items-start gap-4 md:gap-6 xl:flex-1">
                                        <span class="inline-flex items-center justify-center w-9 h-9 flex-shrink-0">
                                            <i class="<?= esc($appointmentAvatarIcon) ?> <?= esc($appointmentAvatarColor) ?> text-3xl"></i>
                                        </span>
                                        <div class="space-y-2">
                                            <h4 class="text-lg md:text-xl font-black text-gray-900 group-hover/item:text-blue-900 transition-colors duration-300">
                                                <?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?>
                                            </h4>
                                            <div class="flex flex-col gap-2 text-sm text-gray-600 font-medium">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-id-card text-slate-400"></i>
                                                    <span><?= esc($appointment['patient_number'] ?? lang('Treatment.no_id')) ?></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-phone text-blue-500"></i>
                                                    <span><?= $appointment['phone'] ?></span>
                                                </div>
                                                <div class="flex items-center gap-2 break-all">
                                                    <i class="fas fa-envelope text-emerald-500"></i>
                                                    <span><?= $appointment['email'] ?></span>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                    
                                    <!-- Time Information -->
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:flex xl:items-center xl:gap-6">
                                    <div class="text-center space-y-2">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative bg-gradient-to-r from-amber-100 to-orange-100 px-4 py-3 rounded-xl border border-amber-200 shadow-sm">
                                                <p class="text-2xl font-black text-amber-900">
                                                    <?= formatTime($appointment['appointment_time']) ?>
                                                </p>
                                                <p class="text-sm text-amber-700 font-bold">
                                                    <?= $appointment['duration'] ?> <?= lang('UI.appointmentMinutes') ?>
                                                </p>
                                                <?php if (isset($view_mode) && $view_mode === 'all'): ?>
                                                    <p class="text-xs text-amber-600 font-semibold mt-1">
                                                        <?= formatDate($appointment['appointment_date']) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Appointment Type -->
                                    <div class="text-center space-y-2">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative bg-gradient-to-r from-emerald-100 to-cyan-100 px-4 py-3 rounded-xl border border-emerald-200 shadow-sm">
                                                <p class="text-lg font-bold text-emerald-900">
                                                    <?= ucfirst(str_replace('_', ' ', $appointment['appointment_type'])) ?>
                                                </p>
                                                <p class="text-xs text-emerald-700 font-medium">
                                                    <?= esc($appointment['chair_name'] ?? lang('UI.appointmentChairDefault')) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Badge -->
                                    <div class="text-center sm:col-span-2 xl:col-span-1">
                                        <?php
                                        $statusConfig = [
                                            'scheduled' => ['bg' => 'from-blue-500 to-blue-600', 'text' => 'text-blue-900', 'label' => lang('UI.appointmentStatusScheduled')],
                                            'confirmed' => ['bg' => 'from-green-500 to-green-600', 'text' => 'text-green-900', 'label' => lang('UI.appointmentStatusConfirmed')],
                                            'arrived' => ['bg' => 'from-cyan-500 to-cyan-600', 'text' => 'text-cyan-900', 'label' => lang('UI.appointmentStageArrived')],
                                            'ready' => ['bg' => 'from-teal-500 to-teal-600', 'text' => 'text-teal-900', 'label' => lang('UI.appointmentStageReady')],
                                            'in_chair' => ['bg' => 'from-indigo-500 to-indigo-600', 'text' => 'text-indigo-900', 'label' => lang('UI.appointmentStageInChair')],
                                            'checkout' => ['bg' => 'from-violet-500 to-violet-600', 'text' => 'text-violet-900', 'label' => lang('UI.appointmentStageCheckout')],
                                            'completed' => ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-gray-900', 'label' => lang('UI.patientTreatmentStatusCompleted')],
                                            'cancelled' => ['bg' => 'from-red-500 to-red-600', 'text' => 'text-red-900', 'label' => lang('UI.appointmentStatusCancelled')],
                                            'no_show' => ['bg' => 'from-orange-500 to-orange-600', 'text' => 'text-orange-900', 'label' => lang('UI.appointmentStatusNoShow')]
                                        ];
                                        $status = $statusConfig[$appointment['status']] ?? $statusConfig['scheduled'];
                                        ?>
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-r <?= $status['bg'] ?>/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative bg-gradient-to-r <?= $status['bg'] ?> text-white px-6 py-3 rounded-xl shadow-lg font-bold text-sm uppercase tracking-wider group-hover/item:scale-105 transition-transform duration-300">
                                                <?= $status['label'] ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex flex-wrap justify-start gap-2 sm:col-span-2 xl:col-span-1 xl:justify-end">
                                        <?php if (has_permission('appointments', 'view')): ?>
                                            <a href="<?= base_url('appointment/' . $appointment['id'] . '') ?>" class="group/action relative inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl hover:from-gray-200 hover:to-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-gray-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-600/10 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-eye text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('appointments', 'edit')): ?>
                                            <a href="<?= base_url('appointment/' . $appointment['id'] . '/edit') ?>" class="group/action relative inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-blue-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-edit text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($appointment['status'] === 'scheduled' && has_permission('appointments', 'edit')): ?>
                                            <button type="button" onclick="confirmAppointment(<?= $appointment['id'] ?>)" class="group/action relative inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-green-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-check text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (in_array($appointment['status'], ['scheduled', 'confirmed', 'arrived', 'ready'], true) && has_permission('appointments', 'edit')): ?>
                                            <button type="button" onclick="cancelAppointment(<?= $appointment['id'] ?>)" class="group/action relative inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-4 focus:ring-red-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-red-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-red-500/20 to-pink-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-times text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (has_permission('appointments', 'delete')): ?>
                                            <button type="button" onclick="deleteAppointment(<?= $appointment['id'] ?>)" class="group/action relative inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-gray-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/20 to-slate-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-trash text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-16">
                                <div class="relative w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-calendar text-gray-400 text-4xl"></i>
                                </div>
                                <h3 class="text-2xl font-black text-gray-900 mb-3"><?= lang('UI.appointmentNoAppointmentsFound') ?></h3>
                                <p class="text-gray-500 text-lg font-medium mb-6"><?= lang('UI.appointmentNoAppointmentsForDate') ?></p>
                                <a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-2xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-2xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-plus mr-3 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10"><?= lang('UI.appointmentSchedule') ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Load More Button for View All Mode -->
                        <?php if (isset($view_mode) && $view_mode === 'all' && isset($pagination['has_more']) && $pagination['has_more']): ?>
                            <div class="text-center py-6">
                                <button onclick="loadMoreAppointments()" id="loadMoreBtn" class="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-lg font-bold rounded-2xl hover:from-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                                    <i class="fas fa-chevron-down mr-3 text-xl relative z-10 group-hover:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10"><?= lang('UI.appointmentLoadMore') ?></span>
                                </button>
                                <div id="loadMoreSpinner" class="hidden text-center py-4">
                                    <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-emerald-600 mr-3"></div>
                                        <span class="text-emerald-700 font-bold"><?= lang('UI.appointmentLoadingMore') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('appointment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => $returnToContext,
    'contextual' => false,
    'selected_patient' => $quick_create_patient ?? null,
    'chair_options' => $chair_options ?? [],
    'requested_date' => old('appointment_date', $selected_date ?? date('Y-m-d')),
    'appointment_type_prefill' => old('appointment_type', ''),
]) ?>

<script>

const appointmentI18n = {
    confirmPrompt: <?= json_encode(lang('UI.appointmentConfirmPrompt')) ?>,
    completePrompt: <?= json_encode(lang('UI.appointmentCompletePrompt')) ?>,
    cancelPrompt: <?= json_encode(lang('UI.appointmentCancelPrompt')) ?>,
    deletePrompt: <?= json_encode(lang('UI.appointmentDeletePrompt')) ?>,
    confirmSuccess: <?= json_encode(lang('UI.appointmentConfirmSuccess')) ?>,
    completeSuccess: <?= json_encode(lang('UI.appointmentCompleteSuccess')) ?>,
    cancelSuccess: <?= json_encode(lang('UI.appointmentCancelSuccess')) ?>,
    deleteSuccess: <?= json_encode(lang('UI.appointmentDeleteSuccess')) ?>,
    errorPrefix: <?= json_encode(lang('UI.appointmentErrorPrefix')) ?>,
    confirmError: <?= json_encode(lang('UI.appointmentConfirmError')) ?>,
    completeError: <?= json_encode(lang('UI.appointmentCompleteError')) ?>,
    cancelError: <?= json_encode(lang('UI.appointmentCancelError')) ?>,
    deleteError: <?= json_encode(lang('UI.appointmentDeleteError')) ?>,
    loadMoreError: <?= json_encode(lang('UI.appointmentLoadMoreError')) ?>,
    loadError: <?= json_encode(lang('UI.appointmentLoadError')) ?>,
    statusScheduled: <?= json_encode(lang('UI.appointmentStatusScheduled')) ?>,
    statusConfirmed: <?= json_encode(lang('UI.appointmentStatusConfirmed')) ?>,
    statusArrived: <?= json_encode(lang('UI.appointmentStageArrived')) ?>,
    statusReady: <?= json_encode(lang('UI.appointmentStageReady')) ?>,
    statusInChair: <?= json_encode(lang('UI.appointmentStageInChair')) ?>,
    statusCheckout: <?= json_encode(lang('UI.appointmentStageCheckout')) ?>,
    statusCompleted: <?= json_encode(lang('UI.patientTreatmentStatusCompleted')) ?>,
    statusCancelled: <?= json_encode(lang('UI.appointmentStatusCancelled')) ?>,
    statusNoShow: <?= json_encode(lang('UI.appointmentStatusNoShow')) ?>,
    minutes: <?= json_encode(lang('UI.appointmentMinutes')) ?>,
    chairDefault: <?= json_encode(lang('UI.appointmentChairDefault')) ?>,
    typeBadge: <?= json_encode(lang('UI.appointmentTypeBadge')) ?>,
    statusBadge: <?= json_encode(lang('UI.appointmentStatusLabel')) ?>,
    viewAction: <?= json_encode(lang('UI.appointmentViewAction')) ?>,
    editAction: <?= json_encode(lang('UI.appointmentEditAction')) ?>
};

// Initialize filters on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
});

function initializeFilters() {
    const dateFilter = document.getElementById('dateFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchFilter = document.getElementById('searchFilter');
    
    // Add event listeners for real-time filtering
    if (dateFilter) {
        dateFilter.addEventListener('change', filterAppointments);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterAppointments);
    }
    
    if (searchFilter) {
        let searchTimeout;
        searchFilter.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterAppointments();
            }, 500); // 500ms delay for search
        });
    }
}

function filterAppointments() {
    const rawDate = document.getElementById('dateFilter').value;
    const date = window.parseClinicDateToIso ? window.parseClinicDateToIso(rawDate) : rawDate;
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchFilter').value;
    
    const url = new URL(window.location);
    if (date) {
        url.searchParams.set('date', date);
    } else {
        url.searchParams.delete('date');
    }
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    
    window.location.href = url.toString();
}

function clearFilters() {
    document.getElementById('dateFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('searchFilter').value = '';
    
    const url = new URL(window.location);
    url.searchParams.delete('date');
    url.searchParams.delete('status');
    url.searchParams.delete('search');
    
    window.location.href = url.toString();
}

function confirmAppointment(id) {
    if (confirm(appointmentI18n.confirmPrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.confirmSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.confirmError, 'error');
        });
    }
}

function completeAppointment(id) {
    if (confirm(appointmentI18n.completePrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.completeSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.completeError, 'error');
        });
    }
}

function cancelAppointment(id) {
    if (confirm(appointmentI18n.cancelPrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.cancelSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.cancelError, 'error');
        });
    }
}

function deleteAppointment(id) {
    if (confirm(appointmentI18n.deletePrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.deleteSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.deleteError, 'error');
        });
    }
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification-toast fixed top-4 right-4 z-50 max-w-sm w-full';
    
    // Set colors based on type
    let bgColor, textColor, iconColor, icon;
    switch(type) {
        case 'success':
            bgColor = 'bg-green-500';
            textColor = 'text-white';
            iconColor = 'text-green-100';
            icon = 'fas fa-check-circle';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            textColor = 'text-white';
            iconColor = 'text-red-100';
            icon = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            bgColor = 'bg-yellow-500';
            textColor = 'text-white';
            iconColor = 'text-yellow-100';
            icon = 'fas fa-exclamation-triangle';
            break;
        default:
            bgColor = 'bg-blue-500';
            textColor = 'text-white';
            iconColor = 'text-blue-100';
            icon = 'fas fa-info-circle';
    }

    const innerContainer = document.createElement('div');
    innerContainer.className = `${bgColor} ${textColor} rounded-lg shadow-lg p-4 flex items-center space-x-3 animate-slide-in`;

    const iconDiv = document.createElement('div');
    iconDiv.className = 'flex-shrink-0';
    const iconElem = document.createElement('i');
    iconElem.className = `${icon} ${iconColor} text-xl`;
    iconDiv.appendChild(iconElem);

    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex-1';
    const messageP = document.createElement('p');
    messageP.className = 'text-sm font-medium';
    messageP.textContent = message;
    messageDiv.appendChild(messageP);

    const closeDiv = document.createElement('div');
    closeDiv.className = 'flex-shrink-0';
    const closeBtn = document.createElement('button');
    closeBtn.className = 'text-white hover:text-gray-200 focus:outline-none focus:text-gray-200 transition-colors duration-200';
    closeBtn.onclick = function() {
        this.closest('.notification-toast').remove();
    };
    const closeIcon = document.createElement('i');
    closeIcon.className = 'fas fa-times';
    closeBtn.appendChild(closeIcon);
    closeDiv.appendChild(closeBtn);

    innerContainer.appendChild(iconDiv);
    innerContainer.appendChild(messageDiv);
    innerContainer.appendChild(closeDiv);

    notification.appendChild(innerContainer);

    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slide-out 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }
    }, 4000);
}

// Server-side pagination and search functionality
let currentPage = <?= isset($pagination['current_page']) ? $pagination['current_page'] : 1 ?>;
let isLoading = false;
let searchTimeout = null;

// Real-time search with debouncing
function searchAppointments() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    const statusFilter = document.getElementById('statusFilter').value;
    
    // Show search spinner
    const searchIcon = document.getElementById('searchIcon');
    const searchSpinner = document.getElementById('searchSpinner');
    if (searchIcon && searchSpinner) {
        searchIcon.classList.add('hidden');
        searchSpinner.classList.remove('hidden');
    }
    
    // Clear existing timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Set new timeout for debounced search
    searchTimeout = setTimeout(() => {
        // Redirect to search results
        const url = new URL(window.location);
        url.searchParams.set('search', searchTerm);
        url.searchParams.set('status', statusFilter);
        url.searchParams.set('page', '1'); // Reset to first page
        
        window.location.href = url.toString();
    }, 500); // 500ms delay
}

// Clear search function
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    
    // Reset spinner
    const searchIcon = document.getElementById('searchIcon');
    const searchSpinner = document.getElementById('searchSpinner');
    if (searchIcon && searchSpinner) {
        searchIcon.classList.remove('hidden');
        searchSpinner.classList.add('hidden');
    }
    
    // Clear any pending search timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Redirect to clear search
    const url = new URL(window.location);
    url.searchParams.delete('search');
    url.searchParams.delete('status');
    url.searchParams.set('page', '1');
    
    window.location.href = url.toString();
}

// Load more appointments function
function loadMoreAppointments() {
    if (isLoading) return;
    
    isLoading = true;
    currentPage++;
    
    // Show spinner and hide button
    document.getElementById('loadMoreBtn').style.display = 'none';
    document.getElementById('loadMoreSpinner').classList.remove('hidden');
    
    // Get current search parameters
    const searchTerm = document.getElementById('searchInput')?.value.trim() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    
    // Make AJAX request
    fetch(`<?= base_url('appointment/loadMoreAppointments') ?>?page=${currentPage}&search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(statusFilter)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Append new appointments to the list
                appendAppointments(data.appointments);
                
                // Update pagination info
                if (!data.pagination.has_more) {
                    // Hide load more button if no more appointments
                    document.getElementById('loadMoreBtn').style.display = 'none';
                } else {
                    // Show load more button again
                    document.getElementById('loadMoreBtn').style.display = 'inline-flex';
                }
            } else {
                console.error('Failed to load appointments:', data.error);
                showNotification(appointmentI18n.loadMoreError, 'error');
                currentPage--; // Revert page increment
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            showNotification(appointmentI18n.loadError, 'error');
            currentPage--; // Revert page increment
        })
        .finally(() => {
            isLoading = false;
            document.getElementById('loadMoreSpinner').classList.add('hidden');
        });
}

// Append new appointments to the list
function appendAppointments(appointments) {
    const appointmentsContainer = document.querySelector('.appointment-records-list');
    
    appointments.forEach(appointment => {
        const appointmentElement = createAppointmentElement(appointment);
        appointmentsContainer.appendChild(appointmentElement);
    });
}

function getSafeInitials(firstName, lastName, fallback = 'P') {
    const firstChars = Array.from(String(firstName || '').trim());
    const lastChars = Array.from(String(lastName || '').trim());
    const initials = ((firstChars[0] || '') + (lastChars[0] || '')).trim();

    if (initials) {
        return initials.toLocaleUpperCase();
    }

    const fallbackChars = Array.from(String(firstName || '').trim());
    if (fallbackChars.length > 0) {
        return fallbackChars.slice(0, 2).join('').toLocaleUpperCase();
    }

    return fallback;
}

function getPatientAvatar(gender) {
    const avatarMap = {
        male: { icon: 'fas fa-person', color: 'text-blue-700' },
        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
        other: { icon: 'fas fa-user', color: 'text-slate-600' }
    };

    return avatarMap[String(gender || 'other').toLowerCase()] || avatarMap.other;
}

// Create appointment element HTML
function createAppointmentElement(appointment) {
    const div = document.createElement('div');
    div.className = 'appointment-record-card group/item relative flex items-center justify-between p-6 bg-gradient-to-r from-white/60 to-white/40 backdrop-blur-sm rounded-2xl hover:from-white/80 hover:to-white/60 transition-all duration-500 border border-white/20 shadow-lg hover:shadow-2xl hover:shadow-blue-500/10 hover:scale-[1.02] hover:-translate-y-1 animate-slide-in';
    if (<?= has_permission('appointments', 'view') ? 'true' : 'false' ?>) {
        const returnTo = encodeURIComponent('<?= esc($returnToContext, 'js') ?>');
        div.dataset.href = `<?= base_url('appointment') ?>/${appointment.id}`;
        div.classList.add('cursor-pointer');
    }
    
    // Format appointment date
    const appointmentDateIso = String(appointment.appointment_date || '').split(' ')[0];
    const formattedDate = window.formatClinicDateFromIso
        ? window.formatClinicDateFromIso(appointmentDateIso)
        : appointmentDateIso;
    
    // Format appointment time
    const clinicTimeFormat = '<?= esc(settings()->get('time_format', '12')) ?>';
    const appointmentTime = new Date('1970-01-01T' + appointment.appointment_time);
    const formattedTime = appointmentTime.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
        hour12: clinicTimeFormat !== '24'
    });
    
    // Status configuration
    const statusConfig = {
        'scheduled': { bg: 'from-blue-500 to-blue-600', text: 'text-blue-900', label: appointmentI18n.statusScheduled },
        'confirmed': { bg: 'from-green-500 to-green-600', text: 'text-green-900', label: appointmentI18n.statusConfirmed },
        'arrived': { bg: 'from-cyan-500 to-cyan-600', text: 'text-cyan-900', label: appointmentI18n.statusArrived },
        'ready': { bg: 'from-teal-500 to-teal-600', text: 'text-teal-900', label: appointmentI18n.statusReady },
        'in_chair': { bg: 'from-indigo-500 to-indigo-600', text: 'text-indigo-900', label: appointmentI18n.statusInChair },
        'checkout': { bg: 'from-violet-500 to-violet-600', text: 'text-violet-900', label: appointmentI18n.statusCheckout },
        'completed': { bg: 'from-emerald-500 to-emerald-600', text: 'text-emerald-900', label: appointmentI18n.statusCompleted },
        'cancelled': { bg: 'from-red-500 to-red-600', text: 'text-red-900', label: appointmentI18n.statusCancelled },
        'no_show': { bg: 'from-orange-500 to-orange-600', text: 'text-orange-900', label: appointmentI18n.statusNoShow }
    };
    
    const status = statusConfig[appointment.status] || statusConfig['scheduled'];
    const avatar = getPatientAvatar(appointment.gender);
    
    // 1. Patient Information
    const patientInfoDiv = document.createElement('div');
    patientInfoDiv.className = 'flex items-start space-x-6';
    
    const avatarContainer = document.createElement('span');
    avatarContainer.className = 'inline-flex items-center justify-center w-9 h-9 flex-shrink-0';
    const avatarIcon = document.createElement('i');
    avatarIcon.className = `${avatar.icon} ${avatar.color} text-3xl`;
    avatarContainer.appendChild(avatarIcon);
    patientInfoDiv.appendChild(avatarContainer);

    const detailsDiv = document.createElement('div');
    detailsDiv.className = 'space-y-2';
    const nameH4 = document.createElement('h4');
    nameH4.className = 'text-xl font-black text-gray-900 group-hover/item:text-blue-900 transition-colors duration-300';
    nameH4.textContent = appointment.first_name + ' ' + appointment.last_name;
    
    const contactDiv = document.createElement('div');
    contactDiv.className = 'flex flex-col space-y-2 text-sm text-gray-600 font-medium';
    contactDiv.style.display = 'flex';
    contactDiv.style.flexDirection = 'column';
    contactDiv.style.gap = '0.5rem';

    const idDiv = document.createElement('div');
    idDiv.className = 'flex items-center space-x-2';
    const idIcon = document.createElement('i');
    idIcon.className = 'fas fa-id-card text-slate-400';
    const idSpan = document.createElement('span');
    idSpan.textContent = appointment.patient_number || '<?= esc(lang('Treatment.no_id'), 'js') ?>';
    idDiv.appendChild(idIcon);
    idDiv.appendChild(idSpan);

    const phoneDiv = document.createElement('div');
    phoneDiv.className = 'flex items-center space-x-2';
    const phoneIcon = document.createElement('i');
    phoneIcon.className = 'fas fa-phone text-blue-500';
    const phoneSpan = document.createElement('span');
    phoneSpan.textContent = appointment.phone;
    phoneDiv.appendChild(phoneIcon);
    phoneDiv.appendChild(phoneSpan);

    const emailDiv = document.createElement('div');
    emailDiv.className = 'flex items-center space-x-2';
    const emailIcon = document.createElement('i');
    emailIcon.className = 'fas fa-envelope text-emerald-500';
    const emailSpan = document.createElement('span');
    emailSpan.textContent = appointment.email;
    emailDiv.appendChild(emailIcon);
    emailDiv.appendChild(emailSpan);

    contactDiv.appendChild(idDiv);
    contactDiv.appendChild(phoneDiv);
    contactDiv.appendChild(emailDiv);
    detailsDiv.appendChild(nameH4);
    detailsDiv.appendChild(contactDiv);
    patientInfoDiv.appendChild(detailsDiv);

    div.appendChild(patientInfoDiv);
    
    // 2. Time Information
    const timeDiv = document.createElement('div');
    timeDiv.className = 'text-center space-y-2';
    const timeRelative = document.createElement('div');
    timeRelative.className = 'relative';
    const timeBg = document.createElement('div');
    timeBg.className = 'absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300';
    const timeContent = document.createElement('div');
    timeContent.className = 'relative bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 rounded-xl border border-amber-200';
    
    const timeP = document.createElement('p');
    timeP.className = 'text-2xl font-black text-amber-900';
    timeP.textContent = formattedTime;
    
    const durationP = document.createElement('p');
    durationP.className = 'text-sm text-amber-700 font-bold';
    durationP.textContent = appointment.duration + ' ' + appointmentI18n.minutes;
    
    const dateP = document.createElement('p');
    dateP.className = 'text-xs text-amber-600 font-semibold mt-1';
    dateP.textContent = formattedDate;

    timeContent.appendChild(timeP);
    timeContent.appendChild(durationP);
    timeContent.appendChild(dateP);
    timeRelative.appendChild(timeBg);
    timeRelative.appendChild(timeContent);
    timeDiv.appendChild(timeRelative);
    div.appendChild(timeDiv);
    
    // 3. Appointment Type
    const typeDiv = document.createElement('div');
    typeDiv.className = 'text-center space-y-2';
    const typeRelative = document.createElement('div');
    typeRelative.className = 'relative';
    const typeBg = document.createElement('div');
    typeBg.className = 'absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300';
    const typeContent = document.createElement('div');
    typeContent.className = 'relative bg-gradient-to-r from-emerald-50 to-cyan-50 px-4 py-3 rounded-xl border border-emerald-200';
    
    const typeP = document.createElement('p');
    typeP.className = 'text-lg font-bold text-emerald-900';
    typeP.textContent = appointment.appointment_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

    const chairP = document.createElement('p');
    chairP.className = 'text-xs text-emerald-700 font-medium';
    chairP.textContent = appointment.chair_name || appointmentI18n.chairDefault;
    
    typeContent.appendChild(typeP);
    typeContent.appendChild(chairP);
    typeRelative.appendChild(typeBg);
    typeRelative.appendChild(typeContent);
    typeDiv.appendChild(typeRelative);
    div.appendChild(typeDiv);
    
    // 4. Status Badge
    const statusDiv = document.createElement('div');
    statusDiv.className = 'text-center';
    const statusRelative = document.createElement('div');
    statusRelative.className = 'relative';
    
    const statusBg = document.createElement('div');
    statusBg.className = `absolute inset-0 bg-gradient-to-r ${status.bg} rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300`;
    
    const statusContent = document.createElement('div');
    statusContent.className = `relative bg-gradient-to-r ${status.bg} px-4 py-3 rounded-xl shadow-lg`;
    
    const statusTextP = document.createElement('p');
    statusTextP.className = `text-lg font-bold ${status.text}`;
    statusTextP.textContent = status.label;
    
    const statusLabelP = document.createElement('p');
    const labelColorClass = status.text.replace('900', '700');
    statusLabelP.className = `text-xs ${labelColorClass} font-semibold uppercase tracking-wider`;
    statusLabelP.textContent = appointmentI18n.statusBadge;

    statusContent.appendChild(statusTextP);
    statusContent.appendChild(statusLabelP);
    statusRelative.appendChild(statusBg);
    statusRelative.appendChild(statusContent);
    statusDiv.appendChild(statusRelative);
    div.appendChild(statusDiv);
    
    // 5. Action Buttons
    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'flex items-center space-x-3';
    
    const returnTo = encodeURIComponent('<?= esc($returnToContext, 'js') ?>');
    const viewBtn = document.createElement('a');
    viewBtn.href = `<?= base_url('appointment') ?>/${appointment.id}`;
    viewBtn.className = 'group/btn relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25';
    
    const viewBg = document.createElement('div');
    viewBg.className = 'absolute inset-0 bg-gradient-to-r from-blue-500/20 to-blue-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100';
    
    const viewIcon = document.createElement('i');
    viewIcon.className = 'fas fa-eye mr-2 text-sm relative z-10 group-hover/btn:scale-110 transition-transform duration-300';
    
    const viewText = document.createElement('span');
    viewText.className = 'relative z-10';
    viewText.textContent = appointmentI18n.viewAction;
    
    viewBtn.appendChild(viewBg);
    viewBtn.appendChild(viewIcon);
    viewBtn.appendChild(viewText);
    
    const editBtn = document.createElement('a');
    editBtn.href = `<?= base_url('appointment') ?>/${appointment.id}/edit`;
    editBtn.className = 'group/btn relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25';
    
    const editBg = document.createElement('div');
    editBg.className = 'absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-emerald-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100';
    
    const editIcon = document.createElement('i');
    editIcon.className = 'fas fa-edit mr-2 text-sm relative z-10 group-hover/btn:scale-110 transition-transform duration-300';
    
    const editText = document.createElement('span');
    editText.className = 'relative z-10';
    editText.textContent = appointmentI18n.editAction;
    
    editBtn.appendChild(editBg);
    editBtn.appendChild(editIcon);
    editBtn.appendChild(editText);
    
    actionsDiv.appendChild(viewBtn);
    actionsDiv.appendChild(editBtn);
    
    div.appendChild(actionsDiv);
    
    return div;
}

document.addEventListener('click', function(e) {
    const card = e.target.closest('.appointment-record-card[data-href]');
    if (!card) {
        return;
    }

    if (e.target.closest('a, button, input, select, textarea, [onclick]')) {
        return;
    }

    window.location.href = window.workflowContextUrl(card.dataset.href);
});

// Add real-time search event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    // Real-time search on input change
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchAppointments();
        });
        
        // Also support Enter key for immediate search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                // Clear timeout and search immediately
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                searchAppointments();
            }
        });
    }
    
    // Real-time search on status filter change
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            searchAppointments();
        });
    }
});
</script>
<?= $this->endSection() ?>
