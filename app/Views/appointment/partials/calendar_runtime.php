<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const loadingIndicator = document.getElementById('loading-indicator');
    const searchFilter = document.getElementById('calendarSearchFilter');
    const statusFilter = document.getElementById('calendarStatusFilter');
    const typeFilter = document.getElementById('calendarTypeFilter');
    const clearFilters = document.getElementById('calendarClearFilters');
    const detailsModal = document.getElementById('appointmentCalendarDetailsModal');
    const isRtl = <?= $isRtl ? 'true' : 'false' ?>;
    const locale = <?= json_encode(service('request')->getLocale() === 'ar' ? 'ar' : 'en') ?>;
    const isCompactViewport = calendarEl.getBoundingClientRect().width < 680;
    document.getElementById('calendar-filter-panel').open = !isCompactViewport;
    document.getElementById('calendar-context-panel').open = !isCompactViewport;
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
    let loadedRange = '';
    let loadedEvents = null;
    let eventRequest = 0, eventAbort = null;
    
    if (!window.FullCalendar) { showError(appointmentI18n.loadFailed); return; }
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialDate: <?= json_encode($selected_date ?? date('Y-m-d')) ?>,
        initialView: isCompactViewport ? 'listDay' : 'timeGridWeek',
        locale: locale,
        direction: isRtl ? 'rtl' : 'ltr',
        buttonIcons: false,
        buttonText: { prev: '<?= esc(lang('Studio.previous'),'js') ?>', next: '<?= esc(lang('Studio.next'),'js') ?>', listDay: '<?= esc(lang('Studio.calendarAgenda'),'js') ?>' },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isCompactViewport ? 'listDay,timeGridDay' : 'dayGridMonth,timeGridWeek,timeGridDay,listDay'
        },
        events: function(info, successCallback, failureCallback) {
            document.getElementById('calendar-error').hidden = true;
            calendarEl.setAttribute('aria-busy','true');
            // Show loading indicator
            if (loadingIndicator) {
                loadingIndicator.classList.remove('hidden');
            }
            
            eventAbort?.abort();
            const request = ++eventRequest;
            eventAbort = new AbortController();
            const controller = eventAbort, requestTimeout = setTimeout(() => controller.abort(), 12000);
            const startDate = info.startStr;
            const endDate = info.endStr;
            
            const range = startDate + '/' + endDate;
            const eventUrl = new URL(<?= json_encode(base_url('appointment/calendar-events')) ?>);
            eventUrl.search = new URLSearchParams({start:startDate,end:endDate});
            const fetchEvents = loadedRange === range && loadedEvents !== null ? Promise.resolve(loadedEvents) : fetch(eventUrl, {
                method: 'GET',
                signal: controller.signal,
                credentials: 'same-origin',
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
            ;
            fetchEvents.then(data => {
                if (request !== eventRequest) { successCallback([]); return; }
                if (data && data.error) throw new Error(String(data.error));
                loadedRange = range;
                loadedEvents = data;
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
                    textColor: getComputedStyle(document.documentElement).getPropertyValue('--color-on-frame').trim(),
                    url: `<?= base_url('appointment') ?>/${event.id}`,
                    extendedProps: {
                        status: event.status,
                        patient_id: event.patient_id,
                        appointment_type: event.appointment_type || '',
                        duration: event.duration || '',
                        doctor_name: Number(event.provider_user_id) > 0 ? '#' + Number(event.provider_user_id) + (event.doctor_name ? ' - ' + event.doctor_name : '') : (event.doctor_name || ''),
                        booked_by: event.booked_by || '',
                        title: event.title || ''
                    }
                }));
                
                successCallback(events);
            })
            .catch(error => {
                if (request !== eventRequest) { failureCallback(error); return; }
                console.error('Error loading calendar events:', error);
                
                // Show error message
                showError(appointmentI18n.loadFailed);
                
                failureCallback(error);
            })
            .finally(() => {
                clearTimeout(requestTimeout);
                if (request !== eventRequest) return;
                calendarEl.setAttribute('aria-busy','false');
                // Hide loading indicator
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
            });
        },
        eventClick: function(info) {
            if (info.jsEvent.ctrlKey || info.jsEvent.metaKey || info.jsEvent.shiftKey || info.jsEvent.altKey) return;
            info.jsEvent.preventDefault();
            openAppointmentDetailsModal(info.event, info.el);
        },
        dateClick: function(info) {
            <?php if (!has_permission('appointments','create')): ?>return;<?php endif; ?>
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

            window.location.assign(url.toString());
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
        },
        height: isCompactViewport ? 'auto' : getCalendarHeight(),
        contentHeight: isCompactViewport ? 'auto' : getCalendarHeight() - 118,
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
    
    document.querySelector('.calendar-filters')?.addEventListener('submit', event => event.preventDefault());
    calendar.render();

    let calendarResizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(calendarResizeTimer);
        calendarResizeTimer = setTimeout(function() {
            const nextHeight = getCalendarHeight();
            const compact = calendarEl.getBoundingClientRect().width < 680;
            calendar.setOption('height', compact ? 'auto' : nextHeight);
            calendar.setOption('contentHeight', compact ? 'auto' : nextHeight - 118);
            if (compact && calendar.view.type !== 'listDay') calendar.changeView('listDay');
        }, 120);
    });

    [searchFilter, statusFilter, typeFilter].filter(Boolean).forEach(function(control) {
        const eventName = control.tagName === 'INPUT' ? 'input' : 'change';
        control.addEventListener(eventName, debounce(function() {
            calendar.refetchEvents();
        }, 180));
    });

    if (clearFilters) clearFilters.addEventListener('click', function() {
        [searchFilter, statusFilter, typeFilter].filter(Boolean).forEach(control => { control.value = ''; });
        calendar.refetchEvents();
    });
    function refreshCalendar() { loadedRange = ''; loadedEvents = null; calendar.refetchEvents(); }
    document.addEventListener('studio:appointment-created',refreshCalendar);
    document.getElementById('calendar-retry')?.addEventListener('click', refreshCalendar);
    document.getElementById('calendar-refresh')?.addEventListener('click', refreshCalendar);
    window.addEventListener('focus', refreshCalendar);
    document.querySelectorAll('[data-calendar-details-close]').forEach(button => button.addEventListener('click', () => detailsModal.close()));
    detailsModal?.addEventListener('close', function() {
        if (calendarDetailsTrigger?.isConnected) calendarDetailsTrigger.focus();
        calendarDetailsTrigger = null;
    });
    function getStatusColor(status) {
        const roles = {scheduled:'--color-action',confirmed:'--color-frame',completed:'--color-ink-muted',cancelled:'--color-danger',no_show:'--color-warning',arrived:'--color-info',ready:'--color-action',in_chair:'--color-frame-raised',checkout:'--color-frame-deep'};
        return getComputedStyle(document.documentElement).getPropertyValue(roles[status] || '--color-ink-muted').trim();
    }
    function getStatusClasses(status) {
        return 'calendar-status calendar-status--' + (['scheduled','confirmed','completed','cancelled','no_show','arrived','ready','in_chair','checkout'].includes(status) ? status : 'unknown');
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
            statusEl.className = getStatusClasses(props.status);
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

        detailsModal.showModal();
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
        const wrapper = document.createElement(info.view?.type.startsWith('list') ? 'a' : 'div');
        // FullCalendar v5 list events with a URL require an actual anchor for native click/keyboard handling.
        if (wrapper.tagName === 'A' && /^[1-9]\d*$/.test(String(event.id))) wrapper.href = event.url;
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

        [duration, formatStatus(status), doctor].filter(Boolean).forEach(function(value) {
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
        const notice = document.getElementById('calendar-error');
        notice.hidden = false;
        notice.querySelector('span').textContent = message;
    }
});
</script>
