<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$calendarWorkingHours = is_array($workingHours ?? null) ? $workingHours : [];
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
$currentUri = service('request')->getUri();
$returnToContext = ltrim($currentUri->getPath(), '/');
$currentQuery = $currentUri->getQuery();
if ($currentQuery !== '') {
    $returnToContext .= '?' . $currentQuery;
}

$normalizeCalendarTime = static function ($value, string $fallback): string {
    $raw = trim((string) $value);
    if ($raw === '') {
        $raw = $fallback;
    }

    if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $raw) === 1) {
        [$hour, $minute, $second] = array_pad(explode(':', $raw), 3, '00');
        $hour = max(0, min(23, (int) $hour));
        $minute = max(0, min(59, (int) $minute));
        $second = max(0, min(59, (int) $second));
        return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
    }

    $timestamp = strtotime('1970-01-01 ' . $raw);
    if ($timestamp === false) {
        $timestamp = strtotime('1970-01-01 ' . $fallback);
    }

    return $timestamp === false ? $fallback . ':00' : date('H:i:s', $timestamp);
};

$calendarTimeToSeconds = static function (string $time): int {
    [$hour, $minute, $second] = array_map('intval', explode(':', $time));
    return ($hour * 3600) + ($minute * 60) + $second;
};

$calendarSecondsToDuration = static function (int $seconds): string {
    if ($seconds >= 86400) {
        return '24:00:00';
    }

    $seconds = max(0, min(86399, $seconds));
    return gmdate('H:i:s', $seconds);
};

$calendarWorkingStart = $normalizeCalendarTime($calendarWorkingHours['start'] ?? '09:00', '09:00');
$calendarWorkingEnd = $normalizeCalendarTime($calendarWorkingHours['end'] ?? '17:00', '17:00');
$calendarWorkingStartSeconds = $calendarTimeToSeconds($calendarWorkingStart);
$calendarWorkingEndSeconds = $calendarTimeToSeconds($calendarWorkingEnd);

if ($calendarWorkingEndSeconds <= $calendarWorkingStartSeconds) {
    $calendarWorkingStart = '09:00:00';
    $calendarWorkingEnd = '17:00:00';
    $calendarWorkingStartSeconds = 9 * 3600;
    $calendarWorkingEndSeconds = 17 * 3600;
}

$calendarSlotMin = $calendarSecondsToDuration($calendarWorkingStartSeconds - 3600);
$calendarSlotMax = $calendarSecondsToDuration($calendarWorkingEndSeconds + 3600);
if ($calendarTimeToSeconds($calendarSlotMax === '24:00:00' ? '23:59:59' : $calendarSlotMax) <= $calendarTimeToSeconds($calendarSlotMin)) {
    $calendarSlotMin = '08:00:00';
    $calendarSlotMax = '18:00:00';
}

$rawWorkingDays = $calendarWorkingHours['days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
if (is_string($rawWorkingDays)) {
    $decodedDays = json_decode($rawWorkingDays, true);
    $rawWorkingDays = is_array($decodedDays) ? $decodedDays : preg_split('/\s*,\s*/', $rawWorkingDays);
}
if (!is_array($rawWorkingDays) || $rawWorkingDays === []) {
    $rawWorkingDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
}

$calendarDayMap = [
    'sunday' => 0,
    'monday' => 1,
    'tuesday' => 2,
    'wednesday' => 3,
    'thursday' => 4,
    'friday' => 5,
    'saturday' => 6,
];
$calendarBusinessDays = array_values(array_unique(array_map(
    static fn($day) => $calendarDayMap[strtolower(trim((string) $day))] ?? 1,
    $rawWorkingDays
)));
$calendarStats = is_array($stats ?? null) ? $stats : [];
$calendarTodayCount = (int) ($calendarStats['today_appointments'] ?? 0);
$calendarUpcomingCount = (int) ($calendarStats['upcoming_appointments'] ?? 0);
$calendarCompletedCount = (int) ($calendarStats['completed_appointments'] ?? 0);
$calendarCancelledCount = (int) ($calendarStats['cancelled_appointments'] ?? 0);
$calendarWorkHoursLabel = substr($calendarWorkingStart, 0, 5) . ' - ' . substr($calendarWorkingEnd, 0, 5);
$calendarStatusLegend = [
    ['status' => 'scheduled', 'class' => 'bg-blue-500', 'label' => lang('UI.appointmentStatusScheduled')],
    ['status' => 'confirmed', 'class' => 'bg-green-500', 'label' => lang('UI.appointmentStatusConfirmed')],
    ['status' => 'completed', 'class' => 'bg-gray-500', 'label' => lang('UI.dashboardCompleted')],
    ['status' => 'cancelled', 'class' => 'bg-red-500', 'label' => lang('UI.appointmentStatusCancelled')],
    ['status' => 'no_show', 'class' => 'bg-yellow-500', 'label' => lang('UI.appointmentStatusNoShow')],
    ['status' => 'arrived', 'class' => 'bg-cyan-500', 'label' => lang('UI.appointmentStageArrived')],
    ['status' => 'ready', 'class' => 'bg-teal-500', 'label' => lang('UI.appointmentStageReady')],
    ['status' => 'in_chair', 'class' => 'bg-indigo-500', 'label' => lang('UI.appointmentStageInChair')],
    ['status' => 'checkout', 'class' => 'bg-violet-500', 'label' => lang('UI.appointmentStageCheckout')],
];
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
<div class="appointment-calendar-page">
<div class="appointment-calendar-wrap w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <header class="v3-command-header"><div><h1><?= esc(lang('UI.appointmentCalendarTitle')) ?></h1><p><span dir="ltr"><?= esc($calendarWorkHoursLabel) ?></span> / <?= count($calendarBusinessDays) ?> <?= esc(lang('UI.settingsHoursWorkingDays')) ?></p></div><div class="v3-command-actions"><a href="<?= base_url('appointment') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm ring-1 transition hover:bg-white/20">
                    <i class="fas fa-list"></i>
                    <span><?= esc(lang('UI.appointmentListView')) ?></span>
                </a>
                <?php if (has_permission('appointments', 'create')): ?>
                    <a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm v3-accent-text transition hover:bg-blue-50">
                        <i class="fas fa-plus"></i>
                        <span><?= esc(lang('UI.appointmentNew')) ?></span>
                    </a>
                <?php endif; ?></div></header>
<section class="v3-calendar-summary">
 <span><?= esc(lang('UI.appointmentTodaySchedule')) ?> <strong><?= $calendarTodayCount ?></strong></span>
 <span><?= esc(lang('UI.appointmentActive')) ?> <strong><?= $calendarUpcomingCount ?></strong></span>
 <span><?= esc(lang('UI.dashboardCompleted')) ?> <strong><?= $calendarCompletedCount ?></strong></span>
 <span><?= esc(lang('UI.appointmentStatusCancelled')) ?> <strong><?= $calendarCancelledCount ?></strong></span>
</section>

    <section class="appointment-calendar-panel overflow-hidden">
        <div class="border-b v3-border v3-warm-surface p-4 sm:p-5 lg:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-radius v3-warm-surface">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <div>
                            <h3 class="text-xl text-slate-950"><?= esc(lang('UI.appointmentCalendarView')) ?></h3>
                            <p class="mt-1 text-sm font-semibold text-slate-600"><?= esc(lang('UI.appointmentCalendarHint')) ?></p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <?php foreach ($calendarStatusLegend as $legendItem): ?>
                            <span class="inline-flex items-center gap-2 rounded-full v3-surface px-3 py-1.5 text-xs text-slate-700 ring-1 ring-slate-200/80">
                                <span class="h-2.5 w-2.5 rounded-full <?= esc($legendItem['class']) ?>"></span>
                                <?= esc($legendItem['label']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="loading-indicator" class="hidden flex items-center gap-2 rounded-full v3-soft-surface px-3 py-2 text-sm font-bold v3-accent-text ring-1 ring-blue-100">
                    <div class="h-4 w-4 animate-spin rounded-full border-b-2 v3-border"></div>
                    <span><?= esc(lang('UI.appointmentLoadingAppointments')) ?></span>
                </div>
            </div>

            <div class="mt-5 v3-radius border v3-border v3-surface p-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <label for="calendarSearchFilter" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600"><?= esc(lang('UI.appointmentSearch')) ?></label>
                        <div class="relative">
                            <input
                                type="text"
                                id="calendarSearchFilter"
                                class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 <?= $isRtl ? 'pr-10 pl-4' : 'pl-10 pr-4' ?>"
                                placeholder="<?= esc(lang('UI.appointmentSearchPatientHint')) ?>"
                                autocomplete="off"
                            >
                            <i class="fas fa-search pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400 <?= $isRtl ? 'right-3' : 'left-3' ?>"></i>
                        </div>
                    </div>
                    <div>
                        <label for="calendarStatusFilter" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600"><?= esc(lang('UI.appointmentStatusLabel')) ?></label>
                        <select id="calendarStatusFilter" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15">
                            <option value=""><?= esc(lang('UI.appointmentAllStatuses')) ?></option>
                            <option value="scheduled"><?= esc(lang('UI.appointmentStatusScheduled')) ?></option>
                            <option value="confirmed"><?= esc(lang('UI.appointmentStatusConfirmed')) ?></option>
                            <option value="completed"><?= esc(lang('UI.dashboardCompleted')) ?></option>
                            <option value="cancelled"><?= esc(lang('UI.appointmentStatusCancelled')) ?></option>
                            <option value="no_show"><?= esc(lang('UI.appointmentStatusNoShow')) ?></option>
                            <option value="arrived"><?= esc(lang('UI.appointmentStageArrived')) ?></option>
                            <option value="ready"><?= esc(lang('UI.appointmentStageReady')) ?></option>
                            <option value="in_chair"><?= esc(lang('UI.appointmentStageInChair')) ?></option>
                            <option value="checkout"><?= esc(lang('UI.appointmentStageCheckout')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="calendarTypeFilter" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600"><?= esc(lang('UI.appointmentTypeBadge')) ?></label>
                        <select id="calendarTypeFilter" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15">
                            <option value=""><?= esc(lang('UI.appointmentViewAll')) ?></option>
                            <option value="Consultation"><?= esc(lang('UI.appointmentTypeConsultation')) ?></option>
                            <option value="Treatment"><?= esc(lang('UI.appointmentTypeTreatment')) ?></option>
                            <option value="Follow Up"><?= esc(lang('UI.appointmentTypeFollowUp')) ?></option>
                            <option value="Emergency"><?= esc(lang('UI.appointmentTypeEmergency')) ?></option>
                            <option value="Cleaning"><?= esc(lang('UI.appointmentTypeCleaning')) ?></option>
                            <option value="Checkup"><?= esc(lang('UI.appointmentTypeCheckup')) ?></option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" id="calendarClearFilters" class="inline-flex w-full items-center justify-center v3-radius border-2 v3-border bg-slate-100 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:ring-4 focus:ring-blue-500/15">
                            <i class="fas fa-eraser me-2"></i><?= esc(lang('UI.appointmentClearAction')) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="v3-surface p-3 sm:p-4 lg:p-5">
            <div id="calendar" class="appointment-calendar-shell w-full"></div>
        </div>
    </section>



    <!-- Native HTML/CSS Doctor Dayboard Partial -->
    <?php if ($dynamicMode): ?>
        <?= view('appointment/partials/doctor_dayboard', [
            'appointments' => $dayboard_appointments ?? $appointments ?? [],
            'selected_date' => $selected_date ?? date('Y-m-d'),
            'booking_context' => $bookingContext ?? null,
        ]) ?>
    <?php endif; ?>

</div>

    <div id="appointmentCalendarDetailsModal" class="appointment-calendar-details-modal v3-context-drawer fixed inset-0 z-[2147483500] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="appointmentCalendarDetailsTitle">
        <div class="appointment-calendar-details-modal__backdrop v3-context-drawer__backdrop fixed inset-0 bg-slate-950/45" data-calendar-details-close></div>
        <div class="appointment-calendar-details-modal__shell v3-context-drawer__shell" data-calendar-details-close>
            <div class="appointment-calendar-details-modal__panel v3-context-drawer__panel" onclick="event.stopPropagation()">
                <div class="flex items-start justify-between gap-4 border-b v3-border v3-surface px-5 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center v3-radius v3-warm-surface">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="min-w-0">
                            <h2 id="appointmentCalendarDetailsTitle" class="truncate text-lg font-bold text-slate-900"><?= esc(lang('UI.appointmentDetails')) ?></h2>
                            <p id="appointmentCalendarDetailsSubtitle" class="mt-1 truncate text-xs font-semibold text-slate-500"></p>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-calendar-details-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <div class="min-h-0 space-y-4 overflow-y-auto v3-surface p-5">
                    <div class="v3-radius border v3-border v3-surface p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p id="appointmentCalendarDetailsTime" class="text-xl text-slate-900" dir="ltr"></p>
                                <p id="appointmentCalendarDetailsDate" class="mt-1 text-sm font-semibold text-slate-500" dir="ltr"></p>
                            </div>
                            <span id="appointmentCalendarDetailsStatus" class="inline-flex rounded-full px-3 py-1 text-xs uppercase tracking-wide"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="v3-radius border v3-border v3-surface p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.appointmentPatientInfo')) ?></p>
                            <p id="appointmentCalendarDetailsPatient" class="mt-2 text-sm font-bold text-slate-900"></p>
                        </div>
                        <div class="v3-radius border v3-border v3-surface p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.appointmentTypeLabel')) ?></p>
                            <p id="appointmentCalendarDetailsType" class="mt-2 text-sm font-bold text-slate-900"></p>
                        </div>
                        <div class="v3-radius border v3-border v3-surface p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.appointmentDoctorLabel')) ?></p>
                            <p id="appointmentCalendarDetailsDoctor" class="mt-2 text-sm font-bold text-slate-900"></p>
                        </div>
                        <div class="v3-radius border v3-border v3-surface p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.appointmentDurationLabel')) ?></p>
                            <p id="appointmentCalendarDetailsDuration" class="mt-2 text-sm font-bold text-slate-900"></p>
                        </div>
                    </div>
                    <div class="v3-radius border v3-border v3-surface p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.appointmentBookedByLabel')) ?></p>
                        <p id="appointmentCalendarDetailsBookedBy" class="mt-2 text-sm font-bold text-slate-900"></p>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t v3-border v3-surface px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" data-calendar-details-close class="inline-flex items-center justify-center v3-radius border v3-border px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('UI.closeDrawer')) ?>
                    </button>
                    <a id="appointmentCalendarDetailsPatientLink" href="#" class="inline-flex items-center justify-center v3-radius border v3-border v3-soft-surface px-5 py-2.5 text-sm font-bold v3-accent-text transition hover:bg-blue-100">
                        <i class="fas fa-user me-2"></i><?= esc(lang('UI.patientView')) ?>
                    </a>
                    <a id="appointmentCalendarDetailsEditLink" href="#" class="inline-flex items-center justify-center v3-radius border v3-border v3-soft-surface px-5 py-2.5 text-sm font-bold v3-accent-text transition hover:bg-purple-100">
                        <i class="fas fa-edit me-2"></i><?= esc(lang('UI.appointmentEdit')) ?>
                    </a>
                    <a id="appointmentCalendarDetailsOpenLink" href="#" class="inline-flex items-center justify-center v3-radius v3-warm-surface px-5 py-2.5 text-sm font-bold transition">
                        <i class="fas fa-external-link-alt me-2"></i><?= esc(lang('UI.openFullPage')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

<?= view('appointment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => $returnToContext,
    'contextual' => false,
    'selected_patient' => null,
    'chair_options' => $chair_options ?? [],
    'booking_context' => $bookingContext ?? null,
    'requested_date' => old('appointment_date', date('Y-m-d')),
    'appointment_type_prefill' => old('appointment_type', ''),
]) ?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const loadingIndicator = document.getElementById('loading-indicator');
    const searchFilter = document.getElementById('calendarSearchFilter');
    const statusFilter = document.getElementById('calendarStatusFilter');
    const typeFilter = document.getElementById('calendarTypeFilter');
    const clearFilters = document.getElementById('calendarClearFilters');
    const detailsModal = document.getElementById('appointmentCalendarDetailsModal');
    const isRtl = <?= !empty($is_rtl) ? 'true' : 'false' ?>;
    const locale = <?= json_encode(service('request')->getLocale() === 'ar' ? 'ar' : 'en') ?>;
    const isCompactViewport = window.matchMedia('(max-width: 768px)').matches;
    const getCalendarHeight = function() {
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 720;
        const compact = window.matchMedia('(max-width: 768px)').matches;
        const offset = compact ? 210 : 260;
        const minHeight = compact ? 560 : 520;
        const maxHeight = compact ? 680 : 700;
        return Math.max(minHeight, Math.min(maxHeight, viewportHeight - offset));
    };
    const appointmentI18n = {
        loadFailed: '<?= esc(lang('UI.appointmentCalendarLoadFailed'), 'js') ?>',
        statusLabel: '<?= esc(lang('UI.appointmentStatusLabel'), 'js') ?>',
        doctorLabel: '<?= esc(lang('UI.appointmentDoctorLabel'), 'js') ?>',
        durationSuffix: '<?= esc(lang('UI.patientMinutes'), 'js') ?>',
        unknown: '<?= esc(lang('UI.appointmentUnknownCreator'), 'js') ?>',
        statuses: {
            scheduled: '<?= esc(lang('UI.appointmentStatusScheduled'), 'js') ?>',
            confirmed: '<?= esc(lang('UI.appointmentStatusConfirmed'), 'js') ?>',
            completed: '<?= esc(lang('UI.dashboardCompleted'), 'js') ?>',
            cancelled: '<?= esc(lang('UI.appointmentStatusCancelled'), 'js') ?>',
            no_show: '<?= esc(lang('UI.appointmentStatusNoShow'), 'js') ?>',
            arrived: '<?= esc(lang('UI.appointmentStageArrived'), 'js') ?>',
            ready: '<?= esc(lang('UI.appointmentStageReady'), 'js') ?>',
            in_chair: '<?= esc(lang('UI.appointmentStageInChair'), 'js') ?>',
            checkout: '<?= esc(lang('UI.appointmentStageCheckout'), 'js') ?>'
        },
        types: {
            consultation: '<?= esc(lang('UI.appointmentTypeConsultation'), 'js') ?>',
            treatment: '<?= esc(lang('UI.appointmentTypeTreatment'), 'js') ?>',
            follow_up: '<?= esc(lang('UI.appointmentTypeFollowUp'), 'js') ?>',
            emergency: '<?= esc(lang('UI.appointmentTypeEmergency'), 'js') ?>',
            cleaning: '<?= esc(lang('UI.appointmentTypeCleaning'), 'js') ?>',
            checkup: '<?= esc(lang('UI.appointmentTypeCheckup'), 'js') ?>'
        }
    };
    let calendarDetailsTrigger = null;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: isCompactViewport ? 'timeGridDay' : 'timeGridWeek',
        locale: locale,
        direction: isRtl ? 'rtl' : 'ltr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(info, successCallback, failureCallback) {
            // Show loading indicator
            if (loadingIndicator) {
                loadingIndicator.classList.remove('hidden');
            }

            const startDate = info.startStr;
            const endDate = info.endStr;

            fetch(`<?= base_url('appointment/calendar-events') ?>?start=${startDate}&end=${endDate}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Refresh CSRF token if present
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                // Handle both array and object responses
                let eventsData = [];
                if (Array.isArray(data)) {
                    eventsData = data;
                } else if (typeof data === 'object' && data !== null) {
                    // Filter out csrf_token and keep only event objects
                    eventsData = Object.values(data).filter(item =>
                        typeof item === 'object' && item !== null && item.hasOwnProperty('id')
                    );
                }

                const events = eventsData.filter(eventMatchesCalendarFilters).map(event => ({
                    id: event.id,
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    backgroundColor: getStatusColor(event.status),
                    borderColor: getStatusColor(event.status),
                    textColor: '#ffffff',
                    url: `<?= base_url('appointment') ?>/${event.id}`,
                    extendedProps: {
                        status: event.status,
                        patient_id: event.patient_id,
                        appointment_type: event.appointment_type || '',
                        duration: event.duration || '',
                        doctor_name: event.doctor_name || '',
                        booked_by: event.booked_by || '',
                        title: event.title || ''
                    }
                }));

                successCallback(events);
            })
            .catch(error => {
                console.error('Error loading calendar events:', error);

                // Show error message
                showError(appointmentI18n.loadFailed);

                failureCallback(error);
            })
            .finally(() => {
                // Hide loading indicator
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
            });
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            openAppointmentDetailsModal(info.event, info.el);
        },
        dateClick: function(info) {
            const rawDate = String(info.dateStr || '');
            const parts = rawDate.split('T');
            const appointmentDate = (parts[0] || rawDate).slice(0, 10);
            const appointmentTime = parts[1] ? parts[1].slice(0, 5) : '';
            const url = new URL(`<?= base_url('appointment/create') ?>`, window.location.origin);
            if (appointmentDate) {
                url.searchParams.set('date', appointmentDate);
                url.searchParams.set('appointment_date', appointmentDate);
            }
            if (appointmentTime) {
                url.searchParams.set('appointment_time', appointmentTime);
            }
            url.searchParams.set('return_to', <?= json_encode($returnToContext) ?>);

            const trigger = document.createElement('a');
            trigger.href = url.toString();
            trigger.setAttribute('data-appointment-create-modal-open', '');
            trigger.dataset.appointmentDate = appointmentDate;
            if (appointmentTime) {
                trigger.dataset.appointmentTime = appointmentTime;
            }
            document.body.appendChild(trigger);
            trigger.click();
            trigger.remove();
        },
        eventContent: function(info) {
            return { domNodes: [buildAppointmentEventNode(info)] };
        },
        eventDidMount: function(info) {
            // Add tooltip with appointment details
            const event = info.event;
            const doctorLine = event.extendedProps.doctor_name ? `\n${appointmentI18n.doctorLabel}: ${event.extendedProps.doctor_name}` : '';
            const tooltip = `${event.title}\n${appointmentI18n.statusLabel}: ${event.extendedProps.status}${doctorLine}\n<?= esc(lang('UI.patientView'), 'js') ?>`;
            info.el.setAttribute('title', tooltip);
        },
        height: getCalendarHeight(),
        contentHeight: getCalendarHeight() - (isCompactViewport ? 110 : 118),
        expandRows: false,
        aspectRatio: 1.45,
        dayMaxEvents: 5,
        eventMinHeight: 22,
        moreLinkClick: 'popover',
        eventDisplay: 'block',
        nowIndicator: true,
        stickyHeaderDates: true,
        stickyFooterScrollbar: true,
        scrollTimeReset: false,
        businessHours: {
            daysOfWeek: <?= json_encode($calendarBusinessDays) ?>,
            startTime: <?= json_encode($calendarWorkingStart) ?>,
            endTime: <?= json_encode($calendarWorkingEnd) ?>
        },
        views: {
            timeGridWeek: {
                dayMaxEventRows: false
            },
            timeGridDay: {
                dayMaxEventRows: false
            }
        },
        slotDuration: '00:15:00',
        slotLabelInterval: '00:30:00',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        scrollTime: <?= json_encode($calendarWorkingStart) ?>,
        slotMinTime: <?= json_encode($calendarSlotMin) ?>,
        slotMaxTime: <?= json_encode($calendarSlotMax) ?>
    });

    calendar.render();

    let calendarResizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(calendarResizeTimer);
        calendarResizeTimer = setTimeout(function() {
            const nextHeight = getCalendarHeight();
            calendar.setOption('height', nextHeight);
            calendar.setOption('contentHeight', nextHeight - (window.matchMedia('(max-width: 768px)').matches ? 110 : 118));
        }, 120);
    });

    [searchFilter, statusFilter, typeFilter].filter(Boolean).forEach(function(control) {
        const eventName = control.tagName === 'INPUT' ? 'input' : 'change';
        control.addEventListener(eventName, debounce(function() {
            calendar.refetchEvents();
        }, 180));
    });

    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            [searchFilter, statusFilter, typeFilter].filter(Boolean).forEach(function(control) {
                control.value = '';
                control.dispatchEvent(new Event('change', { bubbles: true }));
                control.dispatchEvent(new Event('input', { bubbles: true }));
            });
            calendar.refetchEvents();
        });
    }

    document.addEventListener('click', function(event) {
        const closeTrigger = event.target.closest('[data-calendar-details-close]');
        if (closeTrigger) {
            event.preventDefault();
            closeAppointmentDetailsModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key !== 'Escape') {
            return;
        }
        if (detailsModal && !detailsModal.classList.contains('hidden')) {
            event.preventDefault();
            closeAppointmentDetailsModal();
        }
    });

    function getStatusColor(status) {
        const colors = {
            'scheduled': '#3b82f6',
            'confirmed': '#10b981',
            'completed': '#6b7280',
            'cancelled': '#ef4444',
            'no_show': '#f59e0b',
            'arrived': '#06b6d4',
            'ready': '#14b8a6',
            'in_chair': '#6366f1',
            'checkout': '#8b5cf6'
        };
        return colors[status] || '#6b7280';
    }

    function getStatusClasses(status) {
        const classes = {
            scheduled: 'bg-blue-100 text-blue-800',
            confirmed: 'bg-green-100 text-green-800',
            completed: 'bg-emerald-100 text-emerald-800',
            cancelled: 'bg-red-100 text-red-800',
            no_show: 'bg-amber-100 text-amber-800',
            arrived: 'bg-cyan-100 text-cyan-800',
            ready: 'bg-teal-100 text-teal-800',
            in_chair: 'bg-indigo-100 text-indigo-800',
            checkout: 'bg-violet-100 text-violet-800'
        };
        return classes[status] || 'bg-slate-100 text-slate-800';
    }

    function formatStatus(status) {
        const key = String(status || '').toLowerCase();
        if (appointmentI18n.statuses[key]) {
            return appointmentI18n.statuses[key];
        }
        return key.replace(/_/g, ' ').replace(/\b\w/g, function(letter) {
            return letter.toUpperCase();
        });
    }

    function formatAppointmentType(type) {
        const normalized = String(type || '').toLowerCase().replace(/\s+/g, '_');
        return appointmentI18n.types[normalized] || type || appointmentI18n.unknown;
    }

    function formatModalDate(dateValue) {
        if (!dateValue) return '';
        try {
            return new Intl.DateTimeFormat(locale === 'ar' ? 'ar' : 'en', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }).format(dateValue);
        } catch (error) {
            return String(dateValue);
        }
    }

    function formatModalTime(dateValue) {
        if (!dateValue) return '';
        try {
            return new Intl.DateTimeFormat('en', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).format(dateValue);
        } catch (error) {
            return String(dateValue);
        }
    }

    function setDetailsText(id, value) {
        const target = document.getElementById(id);
        if (target) {
            target.textContent = value || appointmentI18n.unknown;
        }
    }

    function openAppointmentDetailsModal(event, trigger) {
        if (!detailsModal || !event) return;
        calendarDetailsTrigger = trigger || document.activeElement;

        const props = event.extendedProps || {};
        const rawAppointmentUrl = event.url || `<?= base_url('appointment') ?>/${event.id}`;
        const appointmentUrl = window.WorkflowNavigationContext?.withCurrentContext ? window.WorkflowNavigationContext.withCurrentContext(rawAppointmentUrl) : rawAppointmentUrl;
        const rawPatientUrl = props.patient_id ? `<?= base_url('patient') ?>/${props.patient_id}` : '#';
        const patientUrl = props.patient_id && window.WorkflowNavigationContext?.withCurrentContext ? window.WorkflowNavigationContext.withCurrentContext(rawPatientUrl) : rawPatientUrl;
        const editUrlBase = `<?= base_url('appointment') ?>/${event.id}/edit`;
        const editUrl = window.WorkflowNavigationContext?.withCurrentContext ? window.WorkflowNavigationContext.withCurrentContext(editUrlBase) : editUrlBase;
        const start = event.start;
        const end = event.end;
        const timeRange = [formatModalTime(start), formatModalTime(end)].filter(Boolean).join(' - ');
        const titleParts = String(props.title || event.title || '').split(' - ');
        const patientName = titleParts[0] || event.title || appointmentI18n.unknown;

        setDetailsText('appointmentCalendarDetailsSubtitle', `#${event.id}`);
        setDetailsText('appointmentCalendarDetailsTime', timeRange);
        setDetailsText('appointmentCalendarDetailsDate', formatModalDate(start));
        setDetailsText('appointmentCalendarDetailsPatient', patientName);
        setDetailsText('appointmentCalendarDetailsType', formatAppointmentType(props.appointment_type || ''));
        setDetailsText('appointmentCalendarDetailsDoctor', props.doctor_name || '');
        setDetailsText('appointmentCalendarDetailsDuration', props.duration ? `${props.duration} ${appointmentI18n.durationSuffix}` : '');
        setDetailsText('appointmentCalendarDetailsBookedBy', props.booked_by || '');

        const statusEl = document.getElementById('appointmentCalendarDetailsStatus');
        if (statusEl) {
            statusEl.className = `inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ${getStatusClasses(props.status)}`;
            statusEl.textContent = formatStatus(props.status);
        }

        const openLink = document.getElementById('appointmentCalendarDetailsOpenLink');
        const editLink = document.getElementById('appointmentCalendarDetailsEditLink');
        const patientLink = document.getElementById('appointmentCalendarDetailsPatientLink');
        if (openLink) openLink.href = appointmentUrl;
        if (editLink) editLink.href = editUrl;
        if (patientLink) {
            patientLink.href = patientUrl;
            patientLink.classList.toggle('hidden', !props.patient_id);
        }

        detailsModal.classList.remove('hidden');
        detailsModal.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('app-drawer-lock');
        document.body.classList.add('app-drawer-lock');
        requestAnimationFrame(function() {
            detailsModal.classList.add('is-open');
        });
    }

    function closeAppointmentDetailsModal() {
        if (!detailsModal || detailsModal.classList.contains('hidden')) return;
        detailsModal.classList.remove('is-open');
        detailsModal.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('app-drawer-lock');
        document.body.classList.remove('app-drawer-lock');
        window.setTimeout(function() {
            detailsModal.classList.add('hidden');
            if (calendarDetailsTrigger && typeof calendarDetailsTrigger.focus === 'function') {
                calendarDetailsTrigger.focus();
            }
            calendarDetailsTrigger = null;
        }, 220);
    }

    function eventMatchesCalendarFilters(event) {
        const selectedStatus = statusFilter ? statusFilter.value : '';
        const selectedType = typeFilter ? typeFilter.value : '';
        const searchTerm = searchFilter ? searchFilter.value.trim().toLowerCase() : '';
        const eventStatus = String(event.status || '');
        const eventType = String(event.appointment_type || '');

        if (selectedStatus && eventStatus !== selectedStatus) {
            return false;
        }

        if (selectedType && eventType !== selectedType) {
            return false;
        }

        if (searchTerm) {
            const haystack = [
                event.title,
                event.appointment_type,
                event.status,
                event.doctor_name,
                event.booked_by
            ].filter(Boolean).join(' ').toLowerCase();

            if (!haystack.includes(searchTerm)) {
                return false;
            }
        }

        return true;
    }

    function debounce(callback, wait) {
        let timeoutId;
        return function() {
            window.clearTimeout(timeoutId);
            timeoutId = window.setTimeout(callback, wait);
        };
    }

    function buildAppointmentEventNode(info) {
        const event = info.event;
        const wrapper = document.createElement('div');
        wrapper.className = 'appointment-calendar-event';
        wrapper.dir = isRtl ? 'rtl' : 'ltr';

        const time = document.createElement('div');
        time.className = 'appointment-calendar-event__time';
        time.textContent = info.timeText || formatEventTime(event.start);
        wrapper.appendChild(time);

        const title = document.createElement('div');
        title.className = 'appointment-calendar-event__title';
        title.textContent = event.title || '';
        wrapper.appendChild(title);

        const meta = document.createElement('div');
        meta.className = 'appointment-calendar-event__meta';

        const duration = event.extendedProps.duration ? `${event.extendedProps.duration} ${appointmentI18n.durationSuffix}` : '';
        const status = event.extendedProps.status || '';
        const doctor = event.extendedProps.doctor_name || '';

        [duration, status, doctor].filter(Boolean).forEach(function(value) {
            const pill = document.createElement('span');
            pill.className = 'appointment-calendar-event__pill';
            pill.textContent = value;
            meta.appendChild(pill);
        });

        if (meta.childNodes.length > 0) {
            wrapper.appendChild(meta);
        }

        return wrapper;
    }

    function formatEventTime(date) {
        if (!(date instanceof Date)) {
            return '';
        }

        return date.toLocaleTimeString(locale, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    function showError(message) {
        // Create error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';

        const flexContainer = document.createElement('div');
        flexContainer.className = 'flex items-center space-x-2';

        const icon = document.createElement('i');
        icon.className = 'fas fa-exclamation-triangle';

        const text = document.createElement('span');
        text.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.className = 'ml-2 text-white hover:text-gray-200';
        closeBtn.onclick = function() {
            errorDiv.remove();
        };
        const closeIcon = document.createElement('i');
        closeIcon.className = 'fas fa-times';
        closeBtn.appendChild(closeIcon);

        flexContainer.appendChild(icon);
        flexContainer.appendChild(text);
        flexContainer.appendChild(closeBtn);

        errorDiv.appendChild(flexContainer);
        document.body.appendChild(errorDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
});
</script>
</div>
</div>
<?= $this->endSection() ?>
