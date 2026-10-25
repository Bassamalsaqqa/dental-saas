<?php include APPPATH . 'Views/layouts/partials/view_permissions.php'; ?>
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
<div class="v4-calendar-workspace">
<header class="v4-page-heading"><div><h1><?= esc(lang('V4.scheduleTitle')) ?></h1><p><?= esc(lang('V4.scheduleHelp')) ?></p></div><div class="v4-page-actions"><a href="<?= base_url('appointment') ?>" class="v4-button"><i class="fas fa-list" aria-hidden="true"></i><?= esc(lang('UI.appointmentListView')) ?></a><?php if($v4Can('appointments','create')): ?><a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="v4-button v4-button--primary"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('UI.appointmentNew')) ?></a><?php endif; ?></div></header>
<section class="v4-calendar-surface">
<details class="v4-calendar-filters" open><summary><i class="fas fa-filter" aria-hidden="true"></i><?= esc(lang('V4.filterVisits')) ?></summary><div class="v4-calendar-filter-fields">
<label for="calendarSearchFilter"><span><?= esc(lang('UI.appointmentSearch')) ?></span><input id="calendarSearchFilter" type="search" autocomplete="off" placeholder="<?= esc(lang('UI.appointmentSearchPatientHint'),'attr') ?>"></label>
<label for="calendarStatusFilter"><span><?= esc(lang('UI.appointmentStatusLabel')) ?></span><select id="calendarStatusFilter">
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
                        </select></label>
<label for="calendarTypeFilter"><span><?= esc(lang('UI.appointmentTypeBadge')) ?></span><select id="calendarTypeFilter">
                            <option value=""><?= esc(lang('UI.appointmentViewAll')) ?></option>
                            <option value="Consultation"><?= esc(lang('UI.appointmentTypeConsultation')) ?></option>
                            <option value="Treatment"><?= esc(lang('UI.appointmentTypeTreatment')) ?></option>
                            <option value="Follow Up"><?= esc(lang('UI.appointmentTypeFollowUp')) ?></option>
                            <option value="Emergency"><?= esc(lang('UI.appointmentTypeEmergency')) ?></option>
                            <option value="Cleaning"><?= esc(lang('UI.appointmentTypeCleaning')) ?></option>
                            <option value="Checkup"><?= esc(lang('UI.appointmentTypeCheckup')) ?></option>
                        </select></label>
<button type="button" id="calendarClearFilters" class="v4-button v4-button--quiet"><i class="fas fa-undo" aria-hidden="true"></i><?= esc(lang('UI.appointmentClearAction')) ?></button>
</div></details>
<div id="loading-indicator" class="v4-calendar-loading hidden" role="status"><?= esc(lang('UI.appointmentLoadingAppointments')) ?></div>
<div id="calendar" class="v4-calendar-grid"></div>
<footer class="v4-calendar-footer"><span><?= esc(lang('UI.appointmentDoctorLabel')) ?>: <?= esc($dynamicMode?lang('V4.canonicalDoctors'):lang('V4.legacyResources')) ?></span><details><summary><?= esc(lang('V4.visitSummary')) ?></summary><div><span><?= esc(lang('UI.appointmentToday')) ?> <b><?= $calendarTodayCount ?></b></span><span><?= esc(lang('UI.appointmentActive')) ?> <b><?= $calendarUpcomingCount ?></b></span><span><?= esc(lang('UI.dashboardCompleted')) ?> <b><?= $calendarCompletedCount ?></b></span><span><?= esc(lang('UI.appointmentStatusCancelled')) ?> <b><?= $calendarCancelledCount ?></b></span></div></details></footer>
</section>
<div id="appointmentCalendarDetailsModal" class="v4-visit-inspector hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="appointmentCalendarDetailsTitle">
<div class="v4-visit-inspector-backdrop" data-calendar-details-close></div><div class="v4-visit-inspector-position" data-calendar-details-close><section class="v4-visit-inspector-panel" onclick="event.stopPropagation()">
<header><div><h2 id="appointmentCalendarDetailsTitle"><?= esc(lang('UI.appointmentDetails')) ?></h2><p id="appointmentCalendarDetailsSubtitle"></p></div><button type="button" class="v4-icon-button" data-calendar-details-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
<div class="v4-visit-inspector-content"><section class="v4-visit-slot"><p id="appointmentCalendarDetailsDate"></p><h3 id="appointmentCalendarDetailsTime" dir="ltr"></h3><span id="appointmentCalendarDetailsStatus" class="v4-badge"></span></section>
<dl><div><dt><?= esc(lang('UI.patientName')) ?></dt><dd id="appointmentCalendarDetailsPatient"></dd></div><div><dt><?= esc(lang('UI.appointmentTypeBadge')) ?></dt><dd id="appointmentCalendarDetailsType"></dd></div><div><dt><?= esc(lang('UI.appointmentDoctorLabel')) ?></dt><dd id="appointmentCalendarDetailsDoctor"></dd></div><div><dt><?= esc(lang('UI.appointmentDurationLabel')) ?></dt><dd id="appointmentCalendarDetailsDuration"></dd></div><div><dt><?= esc(lang('UI.appointmentBookedByLabel')) ?></dt><dd id="appointmentCalendarDetailsBookedBy"></dd></div></dl></div>
<footer><a id="appointmentCalendarDetailsPatientLink" href="#" class="v4-button"><?= esc(lang('UI.patientView')) ?></a><?php if($v4Can('appointments','edit')): ?><a id="appointmentCalendarDetailsEditLink" href="#" class="v4-button"><?= esc(lang('UI.appointmentEdit')) ?></a><?php endif; ?><a id="appointmentCalendarDetailsOpenLink" href="#" class="v4-button v4-button--primary"><?= esc(lang('UI.openFullPage')) ?></a></footer>
</section></div></div>
    <!-- Native HTML/CSS Doctor Dayboard Partial -->
    <?php if ($dynamicMode): ?>
        <?= view('appointment/partials/doctor_dayboard', [
            'appointments' => $dayboard_appointments ?? $appointments ?? [],
            'selected_date' => $selected_date ?? date('Y-m-d'),
            'booking_context' => $bookingContext ?? null,
        ]) ?>
    <?php endif; ?>

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

<?php helper('asset'); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/v4-calendar.css') ?>?v=<?= esc(asset_version('assets/css/v4-calendar.css')) ?>">


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
    if(isCompactViewport) document.querySelector('.v4-calendar-filters')?.removeAttribute('open');
    let calendarInertState = [];
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
        initialView: isCompactViewport ? 'listWeek' : 'timeGridWeek',
        locale: locale,
        direction: isRtl ? 'rtl' : 'ltr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isCompactViewport ? 'listWeek,timeGridDay' : 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
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
            if(!<?= $v4Can('appointments','create') ? 'true' : 'false' ?>) return;
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
            const tooltip = `${event.title}\n${appointmentI18n.statusLabel}: ${formatStatus(event.extendedProps.status)}${doctorLine}\n<?= esc(lang('UI.patientView'), 'js') ?>`;
            info.el.setAttribute('title', tooltip);
            info.el.setAttribute('tabindex','0');
            info.el.setAttribute('role','button');
            info.el.setAttribute('aria-label',tooltip);
            info.el.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();openAppointmentDetailsModal(event,info.el);}});
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
                if(window.jQuery && jQuery(control).data('select2')) jQuery(control).trigger('change.select2');
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
        if(event.key==='Tab' && detailsModal && !detailsModal.classList.contains('hidden')){
            const items=[...detailsModal.querySelectorAll('button:not([disabled]),a[href],input,select')].filter(el=>el.getClientRects().length);
            const first=items[0],last=items.at(-1);
            if(event.shiftKey&&document.activeElement===first){event.preventDefault();last?.focus();}
            else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first?.focus();}
        }
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
            'scheduled': '#546C60',
            'confirmed': '#0F6B63',
            'completed': '#696958',
            'cancelled': '#935247',
            'no_show': '#9C681E',
            'arrived': '#956E28',
            'ready': '#477564',
            'in_chair': '#255B50',
            'checkout': '#815F3D'
        };
        return colors[status] || '#696958';
    }

    function getStatusClasses(status) {
        const classes = {
            scheduled: 'v4-badge--neutral',
            confirmed: 'v4-badge--success',
            completed: 'v4-badge--success',
            cancelled: 'v4-badge--neutral',
            no_show: 'v4-badge--attention',
            arrived: 'v4-badge--attention',
            ready: 'v4-badge--success',
            in_chair: 'v4-badge--success',
            checkout: 'v4-badge--attention'
        };
        return classes[status] || 'v4-badge--neutral';
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
            statusEl.className = `v4-badge ${getStatusClasses(props.status)}`;
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

        if(detailsModal.parentElement!==document.body) document.body.appendChild(detailsModal);
        calendarInertState = [...document.querySelectorAll('.v4-app,.v4-mobile-dock')].map(el=>[el,el.inert]);
        calendarInertState.forEach(([el])=>el.inert=true);
        detailsModal.classList.remove('hidden');
        detailsModal.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('app-drawer-lock');
        document.body.classList.add('app-drawer-lock');
        requestAnimationFrame(function() {
            detailsModal.classList.add('is-open');
            detailsModal.querySelector('button[data-calendar-details-close]')?.focus();
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
            calendarInertState.forEach(([el,wasInert])=>el.inert=wasInert);
            calendarInertState=[];
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

        [duration, status ? formatStatus(status) : '', doctor].filter(Boolean).forEach(function(value) {
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
