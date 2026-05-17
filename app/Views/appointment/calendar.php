<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$calendarWorkingHours = is_array($workingHours ?? null) ? $workingHours : [];

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
?>
<div class="container mx-auto px-6 py-8">
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= esc(lang('UI.appointmentCalendarTitle')) ?></h1>
            <p class="text-gray-600 text-lg"><?= esc(lang('UI.appointmentCalendarSubtitle')) ?></p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="<?= base_url('appointment') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-list mr-2"></i><?= esc(lang('UI.appointmentListView')) ?>
            </a>
            <a href="<?= base_url('appointment/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.appointmentNew')) ?>
            </a>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?= esc(lang('UI.appointmentCalendarView')) ?></h3>
                    <p class="text-sm text-gray-600"><?= esc(lang('UI.appointmentCalendarHint')) ?></p>
                </div>
                <div class="flex items-center space-x-2">
                    <div id="loading-indicator" class="hidden flex items-center space-x-2 text-blue-600">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        <span class="text-sm"><?= esc(lang('UI.appointmentLoadingAppointments')) ?></span>
                    </div>
                </div>
            </div>

            <div class="mb-6 rounded-2xl border border-white/40 bg-white/70 p-4 shadow-lg shadow-blue-500/5 backdrop-blur-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label for="calendarSearchFilter" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600"><?= esc(lang('UI.appointmentSearch')) ?></label>
                        <div class="relative">
                            <input
                                type="text"
                                id="calendarSearchFilter"
                                class="w-full rounded-xl border border-white/40 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-800 shadow-lg outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 <?= !empty($is_rtl) ? 'pr-10 pl-4' : 'pl-10 pr-4' ?>"
                                placeholder="<?= esc(lang('UI.appointmentSearchPatientHint')) ?>"
                                autocomplete="off"
                            >
                            <i class="fas fa-search pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400 <?= !empty($is_rtl) ? 'right-3' : 'left-3' ?>"></i>
                        </div>
                    </div>
                    <div>
                        <label for="calendarStatusFilter" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600"><?= esc(lang('UI.appointmentStatusLabel')) ?></label>
                        <select id="calendarStatusFilter" class="w-full rounded-xl border border-white/40 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-800 shadow-lg outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20">
                            <option value=""><?= esc(lang('UI.appointmentAllStatuses')) ?></option>
                            <option value="scheduled"><?= esc(lang('UI.appointmentStatusScheduled')) ?></option>
                            <option value="confirmed"><?= esc(lang('UI.appointmentStatusConfirmed')) ?></option>
                            <option value="completed"><?= esc(lang('UI.dashboardCompleted')) ?></option>
                            <option value="cancelled"><?= esc(lang('UI.appointmentStatusCancelled')) ?></option>
                            <option value="no_show"><?= esc(lang('UI.appointmentStatusNoShow')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="calendarTypeFilter" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-600"><?= esc(lang('UI.appointmentTypeBadge')) ?></label>
                        <select id="calendarTypeFilter" class="w-full rounded-xl border border-white/40 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-800 shadow-lg outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20">
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
                        <button type="button" id="calendarClearFilters" class="inline-flex w-full items-center justify-center rounded-xl border border-white/40 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-lg outline-none transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:ring-4 focus:ring-blue-500/20">
                            <i class="fas fa-eraser me-2"></i><?= esc(lang('UI.appointmentClearAction')) ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="calendar" class="appointment-calendar-shell w-full"></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-800 mb-2"><?= esc(lang('UI.appointmentStatusLegend')) ?></h3>
                <p class="text-sm text-gray-600"><?= esc(lang('UI.appointmentStatusLegendHint')) ?></p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span class="text-sm text-gray-600"><?= esc(lang('UI.appointmentStatusScheduled')) ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span class="text-sm text-gray-600"><?= esc(lang('UI.appointmentStatusConfirmed')) ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-gray-500 rounded"></div>
                    <span class="text-sm text-gray-600"><?= esc(lang('UI.dashboardCompleted')) ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-red-500 rounded"></div>
                    <span class="text-sm text-gray-600"><?= esc(lang('UI.appointmentStatusCancelled')) ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                    <span class="text-sm text-gray-600"><?= esc(lang('UI.appointmentStatusNoShow')) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>

<style>
    .appointment-calendar-shell {
        min-height: min(760px, calc(100vh - 260px));
    }

    .appointment-calendar-shell .fc {
        --fc-border-color: #e5e7eb;
        --fc-today-bg-color: rgba(59, 130, 246, 0.08);
        font-family: inherit;
    }

    .appointment-calendar-shell .fc-toolbar-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #111827;
    }

    .appointment-calendar-shell .fc-button {
        border-radius: 0.75rem;
        border-color: #d1d5db;
        background: #fff;
        color: #374151;
        font-weight: 700;
        text-transform: none;
        box-shadow: none;
    }

    .appointment-calendar-shell .fc-button:hover,
    .appointment-calendar-shell .fc-button-primary:not(:disabled).fc-button-active {
        border-color: #2563eb;
        background: #2563eb;
        color: #fff;
    }

    .appointment-calendar-shell .fc-timegrid-slot {
        height: 2.35rem;
    }

    .appointment-calendar-shell .fc-event {
        border-radius: 0.65rem;
        border: 0;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
        overflow: hidden;
    }

    .appointment-calendar-event {
        display: flex;
        flex-direction: column;
        gap: 0.12rem;
        padding: 0.35rem 0.45rem;
        line-height: 1.15;
        min-width: 0;
    }

    .appointment-calendar-event__time {
        font-size: 0.72rem;
        font-weight: 800;
        opacity: 0.95;
        direction: ltr;
        unicode-bidi: isolate;
    }

    .appointment-calendar-event__title {
        font-size: 0.78rem;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .appointment-calendar-event__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        font-size: 0.66rem;
        font-weight: 700;
        opacity: 0.9;
    }

    .appointment-calendar-event__pill {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        padding: 0.08rem 0.35rem;
    }

    @media (max-width: 640px) {
        .appointment-calendar-shell {
            min-height: calc(100vh - 220px);
        }

        .appointment-calendar-shell .fc-header-toolbar {
            align-items: stretch;
            flex-direction: column;
            gap: 0.75rem;
        }

        .appointment-calendar-shell .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const loadingIndicator = document.getElementById('loading-indicator');
    const searchFilter = document.getElementById('calendarSearchFilter');
    const statusFilter = document.getElementById('calendarStatusFilter');
    const typeFilter = document.getElementById('calendarTypeFilter');
    const clearFilters = document.getElementById('calendarClearFilters');
    const isRtl = <?= !empty($is_rtl) ? 'true' : 'false' ?>;
    const locale = <?= json_encode(service('request')->getLocale() === 'ar' ? 'ar' : 'en') ?>;
    const isCompactViewport = window.matchMedia('(max-width: 768px)').matches;
    const appointmentI18n = {
        loadFailed: '<?= esc(lang('UI.appointmentCalendarLoadFailed'), 'js') ?>',
        statusLabel: '<?= esc(lang('UI.appointmentStatusLabel'), 'js') ?>',
        doctorLabel: '<?= esc(lang('UI.appointmentDoctorLabel'), 'js') ?>',
        durationSuffix: '<?= esc(lang('UI.patientMinutes'), 'js') ?>'
    };
    
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
                        booked_by: event.booked_by || ''
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
            window.location.href = info.event.url;
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
            url.searchParams.set('return_to', 'appointment/calendar');

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
        height: 'auto',
        contentHeight: 'auto',
        expandRows: true,
        aspectRatio: 1.45,
        dayMaxEvents: 5,
        eventMinHeight: 42,
        moreLinkClick: 'popover',
        eventDisplay: 'block',
        nowIndicator: true,
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
    
    function getStatusColor(status) {
        const colors = {
            'scheduled': '#3b82f6',
            'confirmed': '#10b981',
            'completed': '#6b7280',
            'cancelled': '#ef4444',
            'no_show': '#f59e0b'
        };
        return colors[status] || '#6b7280';
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
