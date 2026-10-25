<script>
    const notificationsApiUrl = '<?= base_url('api/notifications') ?>';
    const notificationsListUrl = '<?= base_url('notifications') ?>';
    const notificationsMarkReadUrl = '<?= base_url('notifications/mark-read') ?>';
    const notificationsUi = {
        isRtl: document.documentElement.dir === 'rtl',
        empty: <?= json_encode(lang('UI.notificationEmpty')) ?>,
        emptyDesc: <?= json_encode(lang('UI.notificationEmptyDesc')) ?>,
        loadFailed: <?= json_encode(lang('UI.notificationLoadFailed')) ?>,
        retry: <?= json_encode(lang('UI.notificationRetry')) ?>,
        by: <?= json_encode(lang('UI.notificationByUser')) ?>,
        unreadCount: <?= json_encode(lang('UI.notificationUnreadCount')) ?>,
        allCaughtUp: <?= json_encode(lang('UI.notificationAllCaughtUp')) ?>,
        justNow: <?= json_encode(lang('UI.notificationJustNow')) ?>,
        minuteAgo: <?= json_encode(lang('UI.notificationMinuteAgo')) ?>,
        minutesAgo: <?= json_encode(lang('UI.notificationMinutesAgo')) ?>,
        hourAgo: <?= json_encode(lang('UI.notificationHourAgo')) ?>,
        hoursAgo: <?= json_encode(lang('UI.notificationHoursAgo')) ?>,
        dayAgo: <?= json_encode(lang('UI.notificationDayAgo')) ?>,
        daysAgo: <?= json_encode(lang('UI.notificationDaysAgo')) ?>,
    };
    const notificationsState = {
        items: [],
        count: 0,
        loaded: false,
        loading: false,
    };

    function setNotificationTriggerExpanded(expanded) {
        const trigger = document.getElementById('notifications-trigger');
        if (trigger) {
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function positionNotificationsDropdown() {
        const dropdown = document.getElementById('notifications-dropdown');
        const trigger = document.getElementById('notifications-trigger');
        if (!dropdown || !trigger) {
            return;
        }

        const viewportPadding = 8;
        const triggerRect = trigger.getBoundingClientRect();
        const preferredWidth = Math.min(420, window.innerWidth - (viewportPadding * 2));
        const availableHeight = Math.max(240, window.innerHeight - triggerRect.bottom - 16);
        const dropdownHeight = Math.min(520, availableHeight);

        dropdown.style.width = `${preferredWidth}px`;
        dropdown.style.maxWidth = `calc(100vw - ${viewportPadding * 2}px)`;
        dropdown.style.height = `${dropdownHeight}px`;
        dropdown.style.maxHeight = `${dropdownHeight}px`;
        dropdown.style.top = `${Math.max(triggerRect.bottom + 8, viewportPadding)}px`;

        const isRtl = document.documentElement.dir === 'rtl';
        const unclampedLeft = isRtl
            ? triggerRect.left
            : triggerRect.right - preferredWidth;
        const left = Math.min(
            Math.max(unclampedLeft, viewportPadding),
            window.innerWidth - preferredWidth - viewportPadding
        );

        dropdown.style.left = `${left}px`;
        dropdown.style.right = 'auto';
    }

    function setUserMenuExpanded(expanded) {
        const trigger = document.getElementById('user-menu-trigger');
        if (trigger) {
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function closeHeaderMenus(exceptId = null) {
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        const userMenu = document.getElementById('userMenu');
        const clinicMenu = document.getElementById('clinicSwitcherMenu');

        if (notificationsDropdown && exceptId !== 'notifications-dropdown') {
            notificationsDropdown.classList.add('hidden');
            notificationsDropdown.classList.remove('flex');
            setNotificationTriggerExpanded(false);
        }

        if (userMenu && exceptId !== 'userMenu') {
            userMenu.classList.add('hidden');
            setUserMenuExpanded(false);
        }

        if (clinicMenu && exceptId !== 'clinicSwitcherMenu') {
            clinicMenu.classList.add('hidden');
        }
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        const willShow = dropdown.classList.contains('hidden');
        closeHeaderMenus(willShow ? 'notifications-dropdown' : null);

        dropdown.classList.toggle('hidden', !willShow);
        dropdown.classList.toggle('flex', willShow);
        setNotificationTriggerExpanded(willShow);

        if (willShow) {
            positionNotificationsDropdown();
            loadNotifications({ showList: true });
        }
    }

    function toggleUserProfile() {
        toggleUserMenu();
    }

    function loadNotifications({ showList = false } = {}) {
        if (notificationsState.loading) {
            return Promise.resolve();
        }

        notificationsState.loading = true;

        return fetch(notificationsApiUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.text().then((text) => {
                    const normalized = text.replace(/^\uFEFF/, '').trim();
                    return normalized ? JSON.parse(normalized) : {};
                });
            })
            .then((data) => {
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (!data.success) {
                    throw new Error(data.message || notificationsUi.loadFailed);
                }

                notificationsState.items = Array.isArray(data.notifications) ? data.notifications : [];
                notificationsState.count = Number(data.count || 0);
                notificationsState.loaded = true;
                updateNotificationCount(notificationsState.count);

                if (showList || !document.getElementById('notifications-dropdown').classList.contains('hidden')) {
                    displayNotifications(notificationsState.items);
                } else {
                    renderNotificationSummary();
                }
            })
            .catch((error) => {
                console.error('Error loading notifications:', error);
                notificationsState.loaded = false;
                updateNotificationCount(0);

                if (showList || !document.getElementById('notifications-dropdown').classList.contains('hidden')) {
                    displayNotificationError();
                } else {
                    renderNotificationSummary();
                }
            })
            .finally(() => {
                notificationsState.loading = false;
            });
    }

    function updateNotificationCount(count) {
        const countElement = document.getElementById('notification-count');
        const unreadCount = Number(count || 0);

        if (unreadCount > 0) {
            countElement.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            countElement.classList.remove('hidden');
        } else {
            countElement.classList.add('hidden');
            countElement.textContent = '0';
        }

        renderNotificationSummary();
    }

    function renderNotificationSummary() {
        const summary = document.getElementById('notifications-summary');
        if (!summary) {
            return;
        }

        if (!notificationsState.loaded) {
            summary.textContent = notificationsUi.loadFailed;
            return;
        }

        summary.textContent = notificationsState.count > 0
            ? notificationsUi.unreadCount.replace('{count}', notificationsState.count)
            : notificationsUi.allCaughtUp;
    }

    function displayNotifications(notifications) {
        const listElement = document.getElementById('notifications-list');
        listElement.textContent = '';

        if (!notifications.length) {
            const emptyState = document.createElement('div');
            emptyState.className = 'v4-notification-empty';

            const iconWrap = document.createElement('div');
            iconWrap.className = 'v4-notification-empty-icon';

            const icon = document.createElement('i');
            icon.className = 'fas fa-bell-slash text-lg';
            iconWrap.appendChild(icon);

            const title = document.createElement('p');
            title.className = 'v4-notification-empty-title';
            title.textContent = notificationsUi.empty;

            const description = document.createElement('p');
            description.className = 'v4-notification-empty-help';
            description.textContent = notificationsUi.emptyDesc;

            emptyState.appendChild(iconWrap);
            emptyState.appendChild(title);
            emptyState.appendChild(description);
            listElement.appendChild(emptyState);
            return;
        }

        const fragment = document.createDocumentFragment();

        notifications.forEach((notification) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'v4-notification-item';
            item.setAttribute('role', 'listitem');
            item.onclick = async function () {
                if (!notification.is_read) {
                    await markNotificationAsRead(notification.id, { silent: true });
                }

                navigateToNotificationTarget(notification);
            };

            const row = document.createElement('div');
            row.className = 'v4-notification-row';

            const iconWrap = document.createElement('div');
            iconWrap.className = `v4-notification-symbol ${getNotificationIconBgClass(notification.color)}`;

            const icon = document.createElement('i');
            icon.className = `${notification.icon} ${getNotificationIconClass(notification.color)} text-sm`;
            iconWrap.appendChild(icon);

            const content = document.createElement('div');
            content.className = 'v4-notification-copy';

            const header = document.createElement('div');
            header.className = 'v4-notification-heading';

            const textWrap = document.createElement('div');
            textWrap.className = 'v4-notification-copy';

            const title = document.createElement('p');
            title.className = 'v4-notification-title';
            title.textContent = notification.title || '';

            const message = document.createElement('p');
            message.className = 'v4-notification-message';
            message.textContent = notification.message || '';

            textWrap.appendChild(title);
            textWrap.appendChild(message);
            header.appendChild(textWrap);

            if (!notification.is_read) {
                const unread = document.createElement('span');
                unread.className = 'v4-notification-unread';
                unread.setAttribute('aria-hidden', 'true');
                header.appendChild(unread);
            }

            const meta = document.createElement('div');
            meta.className = 'v4-notification-meta';

            const time = document.createElement('span');
            time.textContent = formatTime(notification.created_at);
            meta.appendChild(time);

            if (notification.user_name) {
                const user = document.createElement('span');
                user.textContent = notificationsUi.by.replace('{name}', notification.user_name);
                meta.appendChild(user);
            }

            if (notification.type_label) {
                const badge = document.createElement('span');
                badge.className = 'v4-badge';
                badge.textContent = notification.type_label;
                meta.appendChild(badge);
            }

            content.appendChild(header);
            content.appendChild(meta);

            row.appendChild(iconWrap);
            row.appendChild(content);
            item.appendChild(row);
            fragment.appendChild(item);
        });

        listElement.appendChild(fragment);

        const listScroller = document.getElementById('notifications-list');
        if (listScroller) {
            listScroller.scrollTop = 0;
        }
    }

    function displayNotificationError() {
        const listElement = document.getElementById('notifications-list');
        listElement.textContent = '';

        const state = document.createElement('div');
        state.className = 'v4-notification-empty';

        const iconWrap = document.createElement('div');
        iconWrap.className = 'v4-notification-empty-icon v4-critical';

        const icon = document.createElement('i');
        icon.className = 'fas fa-bell-slash text-lg';
        iconWrap.appendChild(icon);

        const title = document.createElement('p');
        title.className = 'v4-notification-empty-title';
        title.textContent = notificationsUi.loadFailed;

        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'v4-button';
        retry.textContent = notificationsUi.retry;
        retry.onclick = function () {
            loadNotifications({ showList: true });
        };

        state.appendChild(iconWrap);
        state.appendChild(title);
        state.appendChild(retry);
        listElement.appendChild(state);
    }

    function getNotificationIconBgClass(color) {
        const colorMap = {
            blue: 'bg-blue-50',
            green: 'bg-emerald-50',
            yellow: 'bg-amber-50',
            purple: 'bg-violet-50',
            red: 'bg-rose-50',
            gray: 'bg-slate-100',
        };

        return colorMap[color] || 'bg-slate-100';
    }

    function getNotificationIconClass(color) {
        const colorMap = {
            blue: 'text-blue-600',
            green: 'text-emerald-600',
            yellow: 'text-amber-600',
            purple: 'text-violet-600',
            red: 'text-rose-600',
            gray: 'text-slate-600',
        };

        return colorMap[color] || 'text-slate-600';
    }

    function formatTime(dateString) {
        if (!dateString) {
            return notificationsUi.justNow;
        }

        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));

        if (diffInMinutes < 1) {
            return notificationsUi.justNow;
        }

        if (diffInMinutes < 60) {
            return diffInMinutes === 1
                ? notificationsUi.minuteAgo
                : notificationsUi.minutesAgo.replace('{count}', diffInMinutes);
        }

        if (diffInMinutes < 1440) {
            const hours = Math.floor(diffInMinutes / 60);
            return hours === 1
                ? notificationsUi.hourAgo
                : notificationsUi.hoursAgo.replace('{count}', hours);
        }

        const days = Math.floor(diffInMinutes / 1440);
        return days === 1
            ? notificationsUi.dayAgo
            : notificationsUi.daysAgo.replace('{count}', days);
    }

    function markNotificationAsRead(id, { silent = false } = {}) {
        if (!id) {
            return Promise.resolve();
        }

        return fetch(`${notificationsMarkReadUrl}/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof csrfToken !== 'undefined' ? csrfToken : ''
            },
            body: JSON.stringify({
                [csrfTokenName]: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (!data.success) {
                    throw new Error(data.message || 'mark-read-failed');
                }

                notificationsState.items = notificationsState.items.map((item) => {
                    if (Number(item.id) === Number(id)) {
                        return { ...item, is_read: true };
                    }

                    return item;
                });
                notificationsState.count = notificationsState.items.filter((item) => !item.is_read).length;
                updateNotificationCount(notificationsState.count);

                if (!silent) {
                    displayNotifications(notificationsState.items);
                }
            })
            .catch((error) => {
                console.error('Error marking notification as read:', error);
            });
    }

    function markAllAsRead() {
        return fetch(notificationsMarkReadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof csrfToken !== 'undefined' ? csrfToken : ''
            },
            body: JSON.stringify({
                [csrfTokenName]: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (!data.success) {
                    throw new Error(data.message || 'mark-all-read-failed');
                }

                notificationsState.items = notificationsState.items.map((item) => ({
                    ...item,
                    is_read: true
                }));
                notificationsState.count = 0;
                updateNotificationCount(0);

                if (!document.getElementById('notifications-dropdown').classList.contains('hidden')) {
                    displayNotifications(notificationsState.items);
                }
            })
            .catch((error) => {
                console.error('Error marking all notifications as read:', error);
            });
    }

    window.workflowContextUrl = function(target, returnTo) {
        if (!target || target === '#') {
            return target;
        }

        try {
            const url = new URL(target, window.location.origin);
            if (url.origin !== window.location.origin || url.searchParams.has('return_to')) {
                return url.toString();
            }

            const context = returnTo || <?= json_encode(workflow_current_target() ?? '') ?>;
            if (!context) {
                return url.toString();
            }

            const basePrefix = <?= json_encode(parse_url(base_url(), PHP_URL_PATH) ? trim((string) parse_url(base_url(), PHP_URL_PATH), '/') . '/' : '') ?>;
            let currentPath = (window.location.pathname + window.location.search).replace(/^\/+/, '');
            if (basePrefix && currentPath.startsWith(basePrefix)) {
                currentPath = currentPath.slice(basePrefix.length);
            }
            if (currentPath === context.replace(/^\/+/, '')) {
                return url.toString();
            }

            url.searchParams.set('return_to', context);
            return url.toString();
        } catch (error) {
            return target;
        }
    };

    function navigateToNotificationTarget(notification) {
        const url = notification && notification.entity_url ? notification.entity_url : notificationsListUrl;
        window.location.href = window.workflowContextUrl(url);
    }

    const clinicDateFormat = '<?= esc(settings()->get('date_format', 'd/m/Y')) ?>';
    const clinicTimeFormat = '<?= esc(settings()->get('time_format', '12')) ?>';

    function clinicDateFormatLabel(format) {
        return format.replace('Y', 'YYYY').replace('m', 'MM').replace('d', 'DD');
    }

    function formatClinicDateFromIso(isoDate) {
        if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) {
            return isoDate || '';
        }

        const [year, month, day] = isoDate.split('-');
        if (clinicDateFormat === 'm/d/Y') {
            return `${month}/${day}/${year}`;
        }
        if (clinicDateFormat === 'd/m/Y') {
            return `${day}/${month}/${year}`;
        }
        return isoDate;
    }

    function parseClinicDateToIso(rawValue) {
        if (!rawValue) {
            return '';
        }

        const value = rawValue.trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }

        const isValidCalendarDate = (year, month, day) => {
            const y = Number(year);
            const m = Number(month);
            const d = Number(day);
            if (!Number.isInteger(y) || !Number.isInteger(m) || !Number.isInteger(d)) {
                return false;
            }
            const probe = new Date(Date.UTC(y, m - 1, d));
            return probe.getUTCFullYear() === y && (probe.getUTCMonth() + 1) === m && probe.getUTCDate() === d;
        };

        if (clinicDateFormat === 'm/d/Y') {
            const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (!match) return null;
            const month = match[1].padStart(2, '0');
            const day = match[2].padStart(2, '0');
            const year = match[3];
            if (!isValidCalendarDate(year, month, day)) return null;
            return `${year}-${month}-${day}`;
        }

        if (clinicDateFormat === 'd/m/Y') {
            const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (!match) return null;
            const day = match[1].padStart(2, '0');
            const month = match[2].padStart(2, '0');
            const year = match[3];
            if (!isValidCalendarDate(year, month, day)) return null;
            return `${year}-${month}-${day}`;
        }

        return null;
    }

    function formatClinicTimeFrom24(rawTime) {
        if (!rawTime) return '';
        const match = String(rawTime).trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (!match) return rawTime;

        const hour24 = Number(match[1]);
        const minute = match[2];
        if (!Number.isInteger(hour24) || hour24 < 0 || hour24 > 23) return rawTime;

        if (clinicTimeFormat === '24') {
            return `${String(hour24).padStart(2, '0')}:${minute}`;
        }

        const period = hour24 >= 12 ? 'PM' : 'AM';
        const hour12 = hour24 % 12 || 12;
        return `${hour12}:${minute} ${period}`;
    }

    function parseClinicTimeTo24(rawValue) {
        if (!rawValue) return '';
        const value = String(rawValue).trim();

        let match = value.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (match) {
            const hour = Number(match[1]);
            const minute = Number(match[2]);
            if (Number.isInteger(hour) && Number.isInteger(minute) && hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59) {
                return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            }
        }

        match = value.match(/^(\d{1,2}):(\d{2})\s*([AP]M)$/i);
        if (match) {
            let hour = Number(match[1]);
            const minute = Number(match[2]);
            const period = match[3].toUpperCase();
            if (!Number.isInteger(hour) || hour < 1 || hour > 12 || !Number.isInteger(minute) || minute < 0 || minute > 59) {
                return null;
            }
            if (period === 'AM') {
                hour = hour === 12 ? 0 : hour;
            } else {
                hour = hour === 12 ? 12 : hour + 12;
            }
            return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
        }

        return null;
    }

    function initializeClinicDateInputs(root) {
        const scope = root || document;
        const fields = scope.querySelectorAll('input[data-clinic-date="1"]');
        const formatHint = clinicDateFormatLabel(clinicDateFormat);
        const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

        fields.forEach((field) => {
            if (field.dataset.clinicDateInputBound === '1') {
                return;
            }
            field.dataset.clinicDateInputBound = '1';
            const current = field.value ? field.value.trim() : '';
            if (current && /^\d{4}-\d{2}-\d{2}$/.test(current)) {
                field.value = formatClinicDateFromIso(current);
            }

            const syncDateFieldChrome = () => {
                const nativePickerActive = field.type === 'date';
                if (nativePickerActive) {
                    if (isRtl) {
                        field.style.paddingLeft = '2.75rem';
                        field.style.paddingRight = '2.75rem';
                    } else {
                        field.style.paddingLeft = '1rem';
                        field.style.paddingRight = '2.75rem';
                    }
                } else {
                    field.style.removeProperty('padding-left');
                    field.style.removeProperty('padding-right');
                }
            };

            field.type = 'text';
            field.setAttribute('placeholder', formatHint);
            field.setAttribute('autocomplete', 'off');
            field.inputMode = 'numeric';
            field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);
            syncDateFieldChrome();

            const switchToPickerMode = () => {
                const raw = (field.value || '').trim();
                const iso = parseClinicDateToIso(raw);
                field.setCustomValidity('');
                field.type = 'date';
                if (iso) {
                    field.value = iso;
                } else if (raw === '') {
                    field.value = '';
                }
                syncDateFieldChrome();
                if (typeof field.showPicker === 'function') {
                    try {
                        field.showPicker();
                    } catch (e) {
                        // no-op: browser may block showPicker unless user-initiated
                    }
                }
            };

            const switchToDisplayMode = () => {
                if (field.type === 'date') {
                    const iso = (field.value || '').trim();
                    field.type = 'text';
                    field.value = iso ? formatClinicDateFromIso(iso) : '';
                }
                field.setCustomValidity('');
                syncDateFieldChrome();
            };

            // Keep text mode by default so users can type dates manually with numpad.
            // Picker remains available via click, Alt+ArrowDown, or double-click.
            field.addEventListener('click', () => {
                if (field.type !== 'date') {
                    switchToPickerMode();
                }
            });
            field.addEventListener('keydown', (event) => {
                if (event.altKey && event.key === 'ArrowDown') {
                    event.preventDefault();
                    switchToPickerMode();
                }
            });
            field.addEventListener('dblclick', () => {
                if (field.type !== 'date') {
                    switchToPickerMode();
                }
            });
            field.addEventListener('input', () => {
                field.setCustomValidity('');
            });
            field.addEventListener('change', () => {
                field.setCustomValidity('');
            });
            field.addEventListener('blur', switchToDisplayMode);
        });

        scope.querySelectorAll('form').forEach((form) => {
            if (form.dataset.clinicDateBound === '1') {
                return;
            }
            form.dataset.clinicDateBound = '1';
            form.addEventListener('submit', function (event) {
                const dateInputs = form.querySelectorAll('input[data-clinic-date="1"]');
                for (const input of dateInputs) {
                    input.setCustomValidity('');
                    const raw = (input.value || '').trim();
                    if (raw === '') {
                        continue;
                    }

                    const iso = parseClinicDateToIso(raw);
                    if (!iso) {
                        input.setCustomValidity(`Use date format ${formatHint}`);
                        input.reportValidity();
                        event.preventDefault();
                        return;
                    }

                    input.setCustomValidity('');
                    input.value = iso;
                }
            }, true);
        });
    }

    function initializeClinicTimeInputs(root) {
        const scope = root || document;
        const fields = scope.querySelectorAll('input[data-clinic-time="1"]');
        if (!fields.length) {
            return;
        }

        const formatHint = clinicTimeFormat === '24' ? 'HH:MM' : 'h:mm AM/PM';

        fields.forEach((field) => {
            if (field.dataset.clinicTimeInputBound === '1') {
                return;
            }
            field.dataset.clinicTimeInputBound = '1';
            if (clinicTimeFormat === '24') {
                field.type = 'time';
                field.setAttribute('title', 'Use 24-hour format (HH:MM)');
                return;
            }

            const current = (field.value || '').trim();
            if (current) {
                field.value = formatClinicTimeFrom24(current);
            }

            field.type = 'text';
            field.setAttribute('placeholder', formatHint);
            field.setAttribute('autocomplete', 'off');
            field.inputMode = 'numeric';
            field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);

            const switchToPickerMode = () => {
                const raw = (field.value || '').trim();
                const time24 = parseClinicTimeTo24(raw);
                field.type = 'time';
                field.value = time24 || '';
                if (typeof field.showPicker === 'function') {
                    try {
                        field.showPicker();
                    } catch (e) {
                        // browser may block without direct interaction
                    }
                }
            };

            const switchToDisplayMode = () => {
                if (field.type === 'time') {
                    const time24 = (field.value || '').trim();
                    field.type = 'text';
                    field.value = time24 ? formatClinicTimeFrom24(time24) : '';
                }
            };

            field.addEventListener('click', () => {
                if (field.type !== 'time') switchToPickerMode();
            });
            field.addEventListener('dblclick', () => {
                if (field.type !== 'time') switchToPickerMode();
            });
            field.addEventListener('keydown', (event) => {
                if (event.altKey && event.key === 'ArrowDown') {
                    event.preventDefault();
                    switchToPickerMode();
                }
            });
            field.addEventListener('blur', switchToDisplayMode);
        });

        scope.querySelectorAll('form').forEach((form) => {
            if (form.dataset.clinicTimeBound === '1') {
                return;
            }
            form.dataset.clinicTimeBound = '1';
            form.addEventListener('submit', function (event) {
                const timeInputs = form.querySelectorAll('input[data-clinic-time="1"]');
                for (const input of timeInputs) {
                    const raw = (input.value || '').trim();
                    if (raw === '') {
                        input.setCustomValidity('');
                        continue;
                    }

                    const time24 = parseClinicTimeTo24(raw);
                    if (!time24) {
                        input.setCustomValidity(`Use time format ${formatHint}`);
                        input.reportValidity();
                        event.preventDefault();
                        return;
                    }

                    input.setCustomValidity('');
                    input.value = time24;
                }
            }, true);
        });
    }

    window.formatClinicDateFromIso = formatClinicDateFromIso;
    window.parseClinicDateToIso = parseClinicDateToIso;
    window.formatClinicTimeFrom24 = formatClinicTimeFrom24;
    window.parseClinicTimeTo24 = parseClinicTimeTo24;
    window.initializeClinicDateInputs = initializeClinicDateInputs;
    window.initializeClinicTimeInputs = initializeClinicTimeInputs;

    let isSyncingRequiredFieldMarkers = false;

    function runRequiredFieldMarkerSync() {
        if (isSyncingRequiredFieldMarkers) {
            return;
        }

        isSyncingRequiredFieldMarkers = true;
        try {
            syncRequiredFieldMarkers();
        } finally {
            isSyncingRequiredFieldMarkers = false;
        }
    }

    function isRequiredMarkerMutation(mutation) {
        if (mutation.type === 'attributes') {
            return false;
        }

        const nodes = [
            ...Array.from(mutation.addedNodes || []),
            ...Array.from(mutation.removedNodes || []),
        ];

        return nodes.length > 0 && nodes.every((node) => {
            return node.nodeType === Node.ELEMENT_NODE
                && node.classList
                && node.classList.contains('required-indicator');
        });
    }

    function getToggleCheckedTransform() {
        return document.documentElement.dir === 'rtl' ? 'translateX(-20px)' : 'translateX(20px)';
    }

    function applyToggleDotState(dot, checked) {
        if (!dot) {
            return;
        }

        dot.style.transform = checked ? getToggleCheckedTransform() : 'translateX(0)';
    }

    window.applyToggleDotState = applyToggleDotState;

    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeClinicDateInputs();
        initializeClinicTimeInputs();
        runRequiredFieldMarkerSync();
        loadNotifications();

        // Handle URL search parameters
        handleUrlSearchParams();

        // Hidden tabs do not poll; returning to the clinic workspace refreshes immediately.
        setInterval(() => {
            if (!document.hidden) { loadNotifications(); }
        }, 30000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) { loadNotifications(); }
        });

        const requiredObserver = new MutationObserver((mutations) => {
            if (isSyncingRequiredFieldMarkers) {
                return;
            }

            const hasRelevantMutation = mutations.some((mutation) => {
                if (mutation.type === 'attributes') {
                    return true;
                }

                return !isRequiredMarkerMutation(mutation);
            });

            if (hasRelevantMutation) {
                runRequiredFieldMarkerSync();
            }
        });

        requiredObserver.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['required']
        });
    });

    // Handle URL search parameters
    function handleUrlSearchParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');

        if (searchParam) {
            console.log('URL search parameter found:', searchParam);
            // Pre-populate the global search input
            const globalSearchInput = document.getElementById('globalSearch');
            if (globalSearchInput) {
                globalSearchInput.value = searchParam;

                // If we're on patients page, also perform the search
                if (window.location.pathname.includes('/patients')) {
                    setTimeout(() => {
                        performGlobalSearch(searchParam);
                    }, 1000); // Wait a bit for DataTables to initialize
                }
            }
        }
    }

    // Load notification count from API
    function loadNotificationCount() {
        return loadNotifications();
    }

    // Global search functionality
    function handleGlobalSearch(event) {
        if (event.key === 'Enter') {
            const query = event.target.value.trim();
            if (query.length > 2) {
                performGlobalSearch(query);
            }
        }
    }

    function performGlobalSearch(query) {
        // Try to search on current page first, if it's the patients page
        if (window.location.pathname.includes('/patients')) {
            // If we're on the patients page, try to use DataTables search
            try {
                if (typeof patientsTable !== 'undefined') {
                    patientsTable.search(query).draw();
                    return;
                }
            } catch (e) {
                console.log('DataTables not available or search failed');
            }
        }

        // Fallback: redirect to patients page with search parameter
        window.location.href = `<?= base_url('patients') ?>?search=${encodeURIComponent(query)}`;
    }

    function normalizeRequiredLabelText(label) {
        const walker = document.createTreeWalker(label, NodeFilter.SHOW_TEXT);
        const replacements = [];

        while (walker.nextNode()) {
            const node = walker.currentNode;
            const original = node.nodeValue || '';
            const updated = original.replace(/\s*(Required|Ù…Ø·Ù„ÙˆØ¨)\s*$/u, '');
            if (updated !== original) {
                replacements.push([node, updated]);
            }
        }

        replacements.forEach(([node, value]) => {
            node.nodeValue = value;
        });
    }

    function stripExistingRequiredMarkers(label) {
        label.querySelectorAll('.required-indicator').forEach((marker) => marker.remove());

        label.querySelectorAll('span, strong, b, em, small').forEach((node) => {
            if (node.classList.contains('required-indicator')) {
                return;
            }

            if (node.children.length === 0 && node.textContent.trim() === '*') {
                node.remove();
            }
        });

        Array.from(label.childNodes).forEach((node) => {
            if (node.nodeType !== Node.TEXT_NODE) {
                return;
            }

            const updated = (node.nodeValue || '').replace(/\s*\*+\s*$/u, '');
            if (updated !== node.nodeValue) {
                node.nodeValue = updated;
            }
        });
    }

    function getLabelControl(label) {
        if (!label) {
            return null;
        }

        if (label.htmlFor) {
            return document.getElementById(label.htmlFor);
        }

        return label.querySelector('input, select, textarea');
    }

    function syncRequiredFieldMarkers() {
        document.querySelectorAll('form label').forEach((label) => {
            const control = getLabelControl(label);
            if (!control || control.type === 'hidden') {
                return;
            }

            normalizeRequiredLabelText(label);
            stripExistingRequiredMarkers(label);

            if (!control.required) {
                return;
            }

            const marker = document.createElement('span');
            marker.className = 'required-indicator notranslate';
            marker.setAttribute('aria-hidden', 'true');
            marker.textContent = '*';
            label.appendChild(marker);
        });
    }


    // Toggle User Menu Dropdown
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        if (!menu) {
            return;
        }

        const willShow = menu.classList.contains('hidden');
        closeHeaderMenus(willShow ? 'userMenu' : null);
        menu.classList.toggle('hidden', !willShow);
        setUserMenuExpanded(willShow);
    }

    // Toggle Clinic Switcher Dropdown
    function toggleClinicSwitcher() {
        const menu = document.getElementById('clinicSwitcherMenu');
        if (menu) {
            const willShow = menu.classList.contains('hidden');
            closeHeaderMenus(willShow ? 'clinicSwitcherMenu' : null);
            menu.classList.toggle('hidden', !willShow);
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#notifications-dropdown') && !e.target.closest('#notifications-trigger')) {
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            if (notificationsDropdown && !notificationsDropdown.classList.contains('hidden')) {
                notificationsDropdown.classList.add('hidden');
                notificationsDropdown.classList.remove('flex');
                setNotificationTriggerExpanded(false);
            }
        }

        // Handle User Menu
        if (!e.target.closest('#userMenu') && !e.target.closest('#user-menu-trigger')) {
            const userMenu = document.getElementById('userMenu');
            if (userMenu && !userMenu.classList.contains('hidden')) {
                userMenu.classList.add('hidden');
                setUserMenuExpanded(false);
            }
        }

        // Handle Clinic Switcher
        if (!e.target.closest('#clinic-switcher')) {
            const clinicMenu = document.getElementById('clinicSwitcherMenu');
            if (clinicMenu && !clinicMenu.classList.contains('hidden')) {
                clinicMenu.classList.add('hidden');
            }
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+B to toggle sidebar
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            toggleSidebar();
        }

        if (e.key === 'Escape') {
            const openMenu=['notifications-dropdown','userMenu','clinicSwitcherMenu'].find(id=>!document.getElementById(id)?.classList.contains('hidden'));
            closeHeaderMenus();
            const trigger={ 'notifications-dropdown':'notifications-trigger',userMenu:'user-menu-trigger',clinicSwitcherMenu:'v4-clinic-trigger' }[openMenu];
            if(trigger) document.getElementById(trigger)?.focus();
        }
    });

    window.addEventListener('resize', function() {
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        if (notificationsDropdown && !notificationsDropdown.classList.contains('hidden')) {
            positionNotificationsDropdown();
        }
    });

</script>
