<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
    $initialActivities = is_array($activities ?? null) ? $activities : [];
    $initialCount = count($initialActivities);
    $eventTypes = is_array($filters['event_types'] ?? null) ? $filters['event_types'] : [];
    $latestActivity = $initialActivities[0] ?? null;
    $latestAt = $latestActivity['created_at'] ?? null;
    $latestLabel = $latestAt ? date('M j, Y g:i A', strtotime((string) $latestAt)) : 'No events yet';
?>

<div class="v3-platform-workspace min-h-screen v3-bg-panel">
    <div class="w-full space-y-5 px-3 py-3 sm:px-4 lg:px-5">
        <section class="overflow-hidden cp-radius border cp-border v3-bg-forest">
            <div class="relative p-4 sm:p-5">
                <div class="absolute inset-0 v3-bg-panel"></div>
                <div class="relative grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center cp-radius cp-surface v3-text-on-primary border v3-border-default">
                            <i class="fas fa-history text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold v3-text-warning"><?= esc(lang('V3Platform.auditWorkspace')) ?></p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight v3-text-on-primary sm:text-3xl"><?= esc(lang('V3Platform.controlPlaneActivityLog')) ?></h1>
                            <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 cp-accent"><?= esc(lang('V3Platform.reviewOperatorActionsRouteAccessIdentityContextAndExportableAuditEvidenceAcrossTheControlPlane')) ?></p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                <span class="rounded-full v3-bg-primary px-3 py-1.5 v3-text-primary border v3-border-default">
                                    <i class="fas fa-lock me-1"></i><?= esc(lang('V3Platform.globalMode')) ?>
                                </span>
                                <span class="rounded-full cp-surface px-3 py-1.5 v3-text-on-primary border v3-border-default"><?= esc(lang('V3Platform.latest')) ?> <?= esc($latestLabel) ?></span>
                                <span class="rounded-full cp-surface px-3 py-1.5 v3-text-on-primary border v3-border-default"><?= number_format(count($eventTypes)) ?> <?= esc(lang('V3Platform.eventTypes')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid min-w-full grid-cols-2 gap-3 sm:min-w-[30rem] lg:grid-cols-4">
                        <div class="cp-radius cp-surface p-3 border v3-border-default">
                            <p class="text-xs font-bold cp-accent"><?= esc(lang('V3Platform.loadedRows')) ?></p>
                            <p class="mt-1 text-2xl font-bold v3-text-on-primary"><?= number_format($initialCount) ?></p>
                        </div>
                        <div class="cp-radius v3-bg-warning p-3 border v3-border-default">
                            <p class="text-xs font-bold v3-text-warning"><?= esc(lang('V3Platform.eventTypes2')) ?></p>
                            <p class="mt-1 text-2xl font-bold v3-text-on-primary"><?= number_format(count($eventTypes)) ?></p>
                        </div>
                        <button type="button" onclick="refreshActivities()" class="cp-radius cp-surface p-3 text-start border v3-border-default transition v3-hover-soft v3-btn v3-btn--secondary">
                            <p class="text-xs font-bold cp-accent"><?= esc(lang('V3Platform.action')) ?></p>
                            <p class="mt-1 text-sm font-bold v3-text-on-primary"><i class="fas fa-sync-alt me-2"></i><?= esc(lang('V3Platform.refresh')) ?></p>
                        </button>
                        <button type="button" onclick="exportActivities()" class="cp-radius v3-bg-primary p-3 text-start border v3-border-default transition v3-hover-primary v3-btn v3-btn--primary">
                            <p class="text-xs font-bold v3-text-primary"><?= esc(lang('V3Platform.export')) ?></p>
                            <p class="mt-1 text-sm font-bold v3-text-on-primary"><i class="fas fa-download me-2"></i>CSV</p>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="cp-radius border cp-border cp-surface p-4 sm:p-5">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.eventType')) ?></label>
                        <select id="eventTypeFilter" class="w-full cp-radius border cp-border cp-surface px-3 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-focus-control focus:ring-4 v3-focus-control v3-select">
                            <option value=""><?= esc(lang('V3Platform.allEvents')) ?></option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?= esc($type) ?>"><?= esc(ucwords(str_replace('_', ' ', $type))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.user')) ?></label>
                        <input type="text" id="userFilter" placeholder="<?= esc(lang('V3Platform.searchByUserNameOrEmail'), 'attr') ?>" class="w-full cp-radius border cp-border cp-surface px-3 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-focus-control focus:ring-4 v3-focus-control v3-input">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="applyFilters()" class="inline-flex items-center justify-center gap-2 cp-radius px-5 py-3 text-sm font-bold v3-text-on-primary transition v3-btn v3-btn--secondary">
                        <i class="fas fa-filter"></i><?= esc(lang('V3Platform.filter')) ?>
                    </button>
                    <button type="button" onclick="clearFilters()" class="inline-flex items-center justify-center gap-2 cp-radius border cp-border cp-surface px-5 py-3 text-sm font-bold v3-text-ink transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--secondary">
                        <i class="fas fa-xmark"></i><?= esc(lang('V3Platform.clear')) ?>
                    </button>
                </div>
            </div>
        </section>

        <section class="overflow-hidden cp-radius border cp-border cp-surface">
            <div class="v3-bg-panel px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center cp-radius cp-surface v3-text-on-primary border v3-border-default">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide v3-text-on-primary"><?= esc(lang('V3Platform.recentActivities')) ?></h3>
                            <p class="text-xs font-semibold cp-accent"><span id="total-activities">0</span> total · <span id="filtered-activities">0</span> <?= esc(lang('V3Platform.filtered')) ?></p>
                        </div>
                    </div>
                    <div class="rounded-full cp-surface px-3 py-1.5 text-xs font-bold v3-text-on-primary border v3-border-default">
                        <i class="fas fa-shield-halved me-1"></i><?= esc(lang('V3Platform.auditEvidence')) ?>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="activitiesTable" class="w-full min-w-[980px]">
                    <thead class="cp-surface">
                        <tr>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.activity')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.user')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.event')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.description')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.time')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.ipAddress')) ?></th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody" class="divide-y v3-border-default cp-surface"></tbody>
                </table>
            </div>

            <div id="loadingState" class="hidden cp-panel-padding text-center">
                <div class="inline-flex items-center gap-3 cp-radius cp-soft-surface px-5 py-3 text-sm font-bold cp-accent">
                    <div class="h-6 w-6 animate-spin rounded-full border-b-2 cp-border"></div>
                    <span><?= esc(lang('V3Platform.loadingActivities')) ?></span>
                </div>
            </div>

            <div id="emptyState" class="hidden cp-panel-padding text-center">
                <div class="mb-4 v3-text-muted">
                    <i class="fas fa-history cp-title"></i>
                </div>
                <h3 class="mb-2 text-lg font-bold v3-text-ink"><?= esc(lang('V3Platform.noActivitiesFound')) ?></h3>
                <p class="text-sm font-medium v3-text-muted"><?= esc(lang('V3Platform.noActivitiesMatchYourCurrentFilters')) ?></p>
            </div>

            <div id="paginationContainer" class="hidden border-t cp-border cp-surface px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <button id="prevPage" class="cp-radius border cp-border cp-surface px-3 py-2 text-sm font-bold v3-text-muted transition v3-hover-text-ink disabled:cursor-not-allowed disabled:opacity-50 v3-btn v3-btn--secondary">
                            <i class="fas fa-chevron-left me-1 rtl:rotate-180"></i><?= esc(lang('V3Platform.previous')) ?>
                        </button>
                        <span id="pageInfo" class="cp-radius cp-surface px-3 py-2 text-sm font-bold v3-text-ink border v3-border-default"></span>
                        <button id="nextPage" class="cp-radius border cp-border cp-surface px-3 py-2 text-sm font-bold v3-text-muted transition v3-hover-text-ink disabled:cursor-not-allowed disabled:opacity-50 v3-btn v3-btn--secondary">
                            <?= esc(lang('V3Platform.next')) ?><i class="fas fa-chevron-right ms-1 rtl:rotate-180"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-bold v3-text-muted"><?= esc(lang('V3Platform.show')) ?></label>
                        <select id="pageSize" class="cp-radius border cp-border cp-surface px-3 py-2 text-sm font-bold v3-text-ink outline-none v3-focus-control focus:ring-4 v3-focus-control v3-select">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
let currentPage = 1;
let pageSize = 50;
let totalActivities = 0;
let filteredActivities = 0;

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementById('eventTypeFilter').value = urlParams.get('event_type') || '';
    document.getElementById('userFilter').value = urlParams.get('user') || '';
    loadActivities(1);

    document.getElementById('pageSize').addEventListener('change', function() {
        pageSize = parseInt(this.value);
        currentPage = 1;
        loadActivities();
    });

    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadActivities(currentPage);
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        const maxPage = Math.ceil(filteredActivities / pageSize);
        if (currentPage < maxPage) {
            currentPage++;
            loadActivities(currentPage);
        }
    });

    window.addEventListener('popstate', () => {
        const p = new URLSearchParams(window.location.search);
        document.getElementById('eventTypeFilter').value = p.get('event_type') || '';
        document.getElementById('userFilter').value = p.get('user') || '';
        loadActivities(1);
    });
});

function loadActivities(page = currentPage) {
    currentPage = page;
    showLoading();

    const eventType = document.getElementById('eventTypeFilter').value;
    const user = document.getElementById('userFilter').value;

    const params = new URLSearchParams({
        limit: pageSize,
        offset: (currentPage - 1) * pageSize
    });
    if (eventType) params.set('event_type', eventType);
    if (user) params.set('user', user);

    fetch(`<?= base_url('controlplane/activity-log/api') ?>?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActivities(data.activities || []);
                totalActivities = data.total ?? 0;
                filteredActivities = data.total ?? 0;
                updatePagination();
                updateCounts();
            } else {
                showError('Failed to load activities');
            }
        })
        .catch(error => {
            console.error('Error loading activities:', error);
            showError('Failed to load activities');
        })
        .finally(() => {
            hideLoading();
        });
}

function displayActivities(activities) {
    const tbody = document.getElementById('activitiesTableBody');

    if (activities.length === 0) {
        tbody.textContent = '';
        showEmpty();
        return;
    }

    hideEmpty();
    tbody.textContent = '';

    activities.forEach(activity => {
        const tr = document.createElement('tr');
        tr.className = 'transition-colors duration-200 v3-hover-primary';

        const tdActivity = document.createElement('td');
        tdActivity.className = 'whitespace-nowrap px-5 py-4';

        const divFlex = document.createElement('div');
        divFlex.className = 'flex items-center gap-3';

        const divIcon = document.createElement('div');
        divIcon.className = `flex h-10 w-10 items-center justify-center v3-radius v3-elevation-none ${getActivityIconBgClass(activity.color)}`;
        const icon = document.createElement('i');
        icon.className = `${activity.icon} ${getActivityIconClass(activity.color)} text-sm`;
        divIcon.appendChild(icon);

        const divText = document.createElement('div');
        divText.className = 'min-w-0';
        const divTitle = document.createElement('div');
        divTitle.className = 'text-sm font-bold v3-text-ink';
        divTitle.textContent = activity.title || 'Activity';
        const divAction = document.createElement('div');
        divAction.className = `mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ${getBadgeClassFromColor(activity.color)}`;
        divAction.textContent = activity.action || 'INFO';

        divText.appendChild(divTitle);
        divText.appendChild(divAction);
        divFlex.appendChild(divIcon);
        divFlex.appendChild(divText);
        tdActivity.appendChild(divFlex);

        const tdUser = document.createElement('td');
        tdUser.className = 'whitespace-nowrap px-5 py-4';
        const divUserName = document.createElement('div');
        divUserName.className = 'text-sm font-bold v3-text-ink';
        divUserName.textContent = activity.user_name || 'System';
        const divUserEmail = document.createElement('div');
        divUserEmail.className = 'text-xs font-medium v3-text-muted';
        divUserEmail.textContent = activity.user_email || 'system@internal';
        tdUser.appendChild(divUserName);
        tdUser.appendChild(divUserEmail);

        const tdEntity = document.createElement('td');
        tdEntity.className = 'whitespace-nowrap px-5 py-4';
        const spanBadge = document.createElement('span');
        spanBadge.className = `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ${getBadgeClassFromColor(activity.color)}`;
        spanBadge.textContent = activity.entity_type || 'event';
        tdEntity.appendChild(spanBadge);

        const tdDesc = document.createElement('td');
        tdDesc.className = 'px-5 py-4';
        const divDesc = document.createElement('div');
        divDesc.className = 'max-w-md truncate text-sm font-semibold v3-text-ink';
        divDesc.textContent = activity.description || '-';
        tdDesc.appendChild(divDesc);

        const tdTime = document.createElement('td');
        tdTime.className = 'whitespace-nowrap px-5 py-4';
        const divTimeFormatted = document.createElement('div');
        divTimeFormatted.className = 'text-sm font-bold v3-text-ink';
        divTimeFormatted.textContent = activity.formatted_time || '';
        const divTimeFull = document.createElement('div');
        divTimeFull.className = 'text-xs font-medium v3-text-muted';
        divTimeFull.textContent = activity.created_at ? new Date(activity.created_at).toLocaleString() : '';
        tdTime.appendChild(divTimeFormatted);
        tdTime.appendChild(divTimeFull);

        const tdIp = document.createElement('td');
        tdIp.className = 'whitespace-nowrap px-5 py-4';
        const divIp = document.createElement('div');
        divIp.className = 'inline-flex rounded-full v3-bg-soft px-2.5 py-1 text-xs font-bold v3-text-ink border v3-border-default';
        divIp.textContent = activity.ip_address || 'N/A';
        tdIp.appendChild(divIp);

        tr.appendChild(tdActivity);
        tr.appendChild(tdUser);
        tr.appendChild(tdEntity);
        tr.appendChild(tdDesc);
        tr.appendChild(tdTime);
        tr.appendChild(tdIp);

        tbody.appendChild(tr);
    });
}

function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('activitiesTable').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('activitiesTable').classList.remove('hidden');
}

function showEmpty() {
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('activitiesTable').classList.add('hidden');
}

function hideEmpty() {
    document.getElementById('emptyState').classList.add('hidden');
}

function showError(message) {
    console.error(message);
}

function updatePagination() {
    const maxPage = Math.max(1, Math.ceil(filteredActivities / pageSize));
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');

    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= maxPage;

    pageInfo.textContent = `Page ${currentPage} of ${maxPage}`;

    if (filteredActivities > 0) {
        document.getElementById('paginationContainer').classList.remove('hidden');
    } else {
        document.getElementById('paginationContainer').classList.add('hidden');
    }
}

function updateCounts() {
    document.getElementById('total-activities').textContent = totalActivities;
    document.getElementById('filtered-activities').textContent = filteredActivities;
}

function applyFilters() {
    const eventType = document.getElementById('eventTypeFilter').value;
    const user = document.getElementById('userFilter').value;
    const params = new URLSearchParams();
    if (eventType) params.set('event_type', eventType);
    if (user) params.set('user', user);
    const targetUrl = params.toString() ? `?${params.toString()}` : window.location.pathname;
    history.pushState(null, '', targetUrl);
    currentPage = 1;
    loadActivities(1);
}

function clearFilters() {
    document.getElementById('eventTypeFilter').value = '';
    document.getElementById('userFilter').value = '';
    history.pushState(null, '', window.location.pathname);
    currentPage = 1;
    loadActivities(1);
}

function refreshActivities() {
    loadActivities(currentPage);
}

function exportActivities() {
    const eventType = document.getElementById('eventTypeFilter').value;
    const user = document.getElementById('userFilter').value;
    const params = new URLSearchParams();
    if (eventType) params.set('event_type', eventType);
    if (user) params.set('user', user);
    window.location.href = '<?= base_url('controlplane/activity-log/export') ?>' + (params.toString() ? '?' + params.toString() : '');
}

function getActivityIconBgClass(color) {
    const colorMap = {
        blue: 'v3-bg-scheduled',
        green: 'v3-bg-paid',
        yellow: 'v3-bg-waiting',
        purple: 'v3-bg-chair',
        red: 'v3-bg-alert',
        gray: 'v3-bg-soft',
        teal: 'v3-bg-scheduled'
    };
    return colorMap[color] || 'v3-bg-soft';
}

function getActivityIconClass(color) {
    const colorMap = {
        blue: 'v3-text-primary',
        green: 'v3-text-primary',
        yellow: 'v3-text-warning',
        purple: 'v3-text-chair',
        red: 'v3-text-danger',
        gray: 'v3-text-ink',
        teal: 'v3-text-primary'
    };
    return colorMap[color] || 'v3-text-ink';
}

function getBadgeClassFromColor(color) {
    const colorMap = {
        blue: 'v3-bg-scheduled v3-text-primary',
        green: 'v3-bg-paid v3-text-primary',
        yellow: 'v3-bg-waiting v3-text-warning',
        purple: 'v3-bg-chair v3-text-chair',
        red: 'v3-bg-alert v3-text-danger',
        gray: 'v3-bg-soft v3-text-ink',
        teal: 'v3-bg-scheduled v3-text-primary'
    };
    return colorMap[color] || 'v3-bg-soft v3-text-ink';
}
</script>
<?= $this->endSection() ?>
