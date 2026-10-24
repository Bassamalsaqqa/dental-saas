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

<div class="v3-platform-workspace min-h-screen bg-[radial-gradient(circle_at_8%_0%,rgba(59,130,246,.12),transparent_30%),radial-gradient(circle_at_92%_8%,rgba(245,158,11,.10),transparent_28%),linear-gradient(135deg,#eef5ff_0%,#f8fafc_48%,#fff7ed_100%)]">
    <div class="w-full space-y-5 px-3 py-3 sm:px-4 lg:px-5">
        <section class="overflow-hidden cp-radius border cp-border bg-slate-950">
            <div class="relative p-4 sm:p-5">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_14%_16%,rgba(59,130,246,.52),transparent_30%),radial-gradient(circle_at_86%_12%,rgba(245,158,11,.35),transparent_25%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_54%,#92400e_100%)]"></div>
                <div class="relative grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center cp-radius cp-surface text-white ring-1 ring-white/20">
                            <i class="fas fa-history text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-amber-200"><?= esc(lang('V3Platform.auditWorkspace')) ?></p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl"><?= esc(lang('V3Platform.controlPlaneActivityLog')) ?></h1>
                            <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 cp-accent"><?= esc(lang('V3Platform.reviewOperatorActionsRouteAccessIdentityContextAndExportableAuditEvidenceAcrossTheControlPlane')) ?></p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                <span class="rounded-full bg-emerald-400/20 px-3 py-1.5 text-emerald-50 ring-1 ring-emerald-200/25">
                                    <i class="fas fa-lock me-1"></i><?= esc(lang('V3Platform.globalMode')) ?>
                                </span>
                                <span class="rounded-full cp-surface px-3 py-1.5 text-white ring-1 ring-white/18"><?= esc(lang('V3Platform.latest')) ?> <?= esc($latestLabel) ?></span>
                                <span class="rounded-full cp-surface px-3 py-1.5 text-white ring-1 ring-white/18"><?= number_format(count($eventTypes)) ?> <?= esc(lang('V3Platform.eventTypes')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid min-w-full grid-cols-2 gap-3 sm:min-w-[30rem] lg:grid-cols-4">
                        <div class="cp-radius cp-surface p-3 ring-1 ring-white/15">
                            <p class="text-[11px] font-bold cp-accent"><?= esc(lang('V3Platform.loadedRows')) ?></p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= number_format($initialCount) ?></p>
                        </div>
                        <div class="cp-radius bg-amber-400/20 p-3 ring-1 ring-amber-200/25">
                            <p class="text-[11px] font-bold text-amber-100"><?= esc(lang('V3Platform.eventTypes2')) ?></p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= number_format(count($eventTypes)) ?></p>
                        </div>
                        <button type="button" onclick="refreshActivities()" class="cp-radius cp-surface p-3 text-start ring-1 ring-white/15 transition hover:bg-white/22">
                            <p class="text-[11px] font-bold cp-accent"><?= esc(lang('V3Platform.action')) ?></p>
                            <p class="mt-1 text-sm font-bold text-white"><i class="fas fa-sync-alt me-2"></i><?= esc(lang('V3Platform.refresh')) ?></p>
                        </button>
                        <button type="button" onclick="exportActivities()" class="cp-radius bg-emerald-400/20 p-3 text-start ring-1 ring-emerald-200/25 transition hover:bg-emerald-400/28">
                            <p class="text-[11px] font-bold text-emerald-100"><?= esc(lang('V3Platform.export')) ?></p>
                            <p class="mt-1 text-sm font-bold text-white"><i class="fas fa-download me-2"></i>CSV</p>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="cp-radius border cp-border cp-surface p-4 sm:p-5">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.eventType')) ?></label>
                        <select id="eventTypeFilter" class="w-full cp-radius border cp-border cp-surface px-3 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            <option value=""><?= esc(lang('V3Platform.allEvents')) ?></option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?= esc($type) ?>"><?= esc(ucwords(str_replace('_', ' ', $type))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.user')) ?></label>
                        <input type="text" id="userFilter" placeholder="<?= esc(lang('V3Platform.searchByUserNameOrEmail'), 'attr') ?>" class="w-full cp-radius border cp-border cp-surface px-3 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="applyFilters()" class="inline-flex items-center justify-center gap-2 cp-radius px-5 py-3 text-sm font-bold text-white transition">
                        <i class="fas fa-filter"></i><?= esc(lang('V3Platform.filter')) ?>
                    </button>
                    <button type="button" onclick="clearFilters()" class="inline-flex items-center justify-center gap-2 cp-radius border cp-border cp-surface px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-white hover:text-slate-950">
                        <i class="fas fa-xmark"></i><?= esc(lang('V3Platform.clear')) ?>
                    </button>
                </div>
            </div>
        </section>

        <section class="overflow-hidden cp-radius border cp-border cp-surface">
            <div class="bg-[radial-gradient(circle_at_12%_20%,rgba(96,165,250,.42),transparent_28%),radial-gradient(circle_at_86%_18%,rgba(245,158,11,.28),transparent_24%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_55%,#92400e_100%)] px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center cp-radius cp-surface text-white ring-1 ring-white/20">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-white"><?= esc(lang('V3Platform.recentActivities')) ?></h3>
                            <p class="text-xs font-semibold cp-accent"><span id="total-activities">0</span> total · <span id="filtered-activities">0</span> <?= esc(lang('V3Platform.filtered')) ?></p>
                        </div>
                    </div>
                    <div class="rounded-full cp-surface px-3 py-1.5 text-xs font-bold text-white ring-1 ring-white/18">
                        <i class="fas fa-shield-halved me-1"></i><?= esc(lang('V3Platform.auditEvidence')) ?>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="activitiesTable" class="w-full min-w-[980px]">
                    <thead class="cp-surface">
                        <tr>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.activity')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.user')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.event')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.description')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.time')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-[0.14em] text-slate-500"><?= esc(lang('V3Platform.ipAddress')) ?></th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody" class="divide-y divide-slate-100 cp-surface"></tbody>
                </table>
            </div>

            <div id="loadingState" class="hidden cp-panel-padding text-center">
                <div class="inline-flex items-center gap-3 cp-radius cp-soft-surface px-5 py-3 text-sm font-bold cp-accent">
                    <div class="h-6 w-6 animate-spin rounded-full border-b-2 cp-border"></div>
                    <span><?= esc(lang('V3Platform.loadingActivities')) ?></span>
                </div>
            </div>

            <div id="emptyState" class="hidden cp-panel-padding text-center">
                <div class="mb-4 text-slate-300">
                    <i class="fas fa-history cp-title"></i>
                </div>
                <h3 class="mb-2 text-lg font-bold text-slate-900"><?= esc(lang('V3Platform.noActivitiesFound')) ?></h3>
                <p class="text-sm font-medium text-slate-500"><?= esc(lang('V3Platform.noActivitiesMatchYourCurrentFilters')) ?></p>
            </div>

            <div id="paginationContainer" class="hidden border-t cp-border cp-surface px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <button id="prevPage" class="cp-radius border cp-border cp-surface px-3 py-2 text-sm font-bold text-slate-600 transition hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="fas fa-chevron-left me-1 rtl:rotate-180"></i><?= esc(lang('V3Platform.previous')) ?>
                        </button>
                        <span id="pageInfo" class="cp-radius cp-surface px-3 py-2 text-sm font-bold text-slate-700 ring-1 ring-slate-200"></span>
                        <button id="nextPage" class="cp-radius border cp-border cp-surface px-3 py-2 text-sm font-bold text-slate-600 transition hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-50">
                            <?= esc(lang('V3Platform.next')) ?><i class="fas fa-chevron-right ms-1 rtl:rotate-180"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-bold text-slate-600"><?= esc(lang('V3Platform.show')) ?></label>
                        <select id="pageSize" class="cp-radius border cp-border cp-surface px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
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
        tr.className = 'transition-colors duration-200 hover:bg-blue-50/45';

        const tdActivity = document.createElement('td');
        tdActivity.className = 'whitespace-nowrap px-5 py-4';

        const divFlex = document.createElement('div');
        divFlex.className = 'flex items-center gap-3';

        const divIcon = document.createElement('div');
        divIcon.className = `flex h-10 w-10 items-center justify-center rounded-2xl shadow-sm ${getActivityIconBgClass(activity.color)}`;
        const icon = document.createElement('i');
        icon.className = `${activity.icon} ${getActivityIconClass(activity.color)} text-sm`;
        divIcon.appendChild(icon);

        const divText = document.createElement('div');
        divText.className = 'min-w-0';
        const divTitle = document.createElement('div');
        divTitle.className = 'text-sm font-bold text-slate-950';
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
        divUserName.className = 'text-sm font-bold text-slate-900';
        divUserName.textContent = activity.user_name || 'System';
        const divUserEmail = document.createElement('div');
        divUserEmail.className = 'text-xs font-medium text-slate-500';
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
        divDesc.className = 'max-w-md truncate text-sm font-semibold text-slate-700';
        divDesc.textContent = activity.description || '-';
        tdDesc.appendChild(divDesc);

        const tdTime = document.createElement('td');
        tdTime.className = 'whitespace-nowrap px-5 py-4';
        const divTimeFormatted = document.createElement('div');
        divTimeFormatted.className = 'text-sm font-bold text-slate-900';
        divTimeFormatted.textContent = activity.formatted_time || '';
        const divTimeFull = document.createElement('div');
        divTimeFull.className = 'text-xs font-medium text-slate-500';
        divTimeFull.textContent = activity.created_at ? new Date(activity.created_at).toLocaleString() : '';
        tdTime.appendChild(divTimeFormatted);
        tdTime.appendChild(divTimeFull);

        const tdIp = document.createElement('td');
        tdIp.className = 'whitespace-nowrap px-5 py-4';
        const divIp = document.createElement('div');
        divIp.className = 'inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 ring-1 ring-slate-200';
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
        blue: 'bg-blue-100',
        green: 'bg-emerald-100',
        yellow: 'bg-amber-100',
        purple: 'bg-violet-100',
        red: 'bg-rose-100',
        gray: 'bg-slate-100',
        teal: 'bg-teal-100'
    };
    return colorMap[color] || 'bg-slate-100';
}

function getActivityIconClass(color) {
    const colorMap = {
        blue: 'text-blue-700',
        green: 'text-emerald-700',
        yellow: 'text-amber-700',
        purple: 'text-violet-700',
        red: 'text-rose-700',
        gray: 'text-slate-700',
        teal: 'text-teal-700'
    };
    return colorMap[color] || 'text-slate-700';
}

function getBadgeClassFromColor(color) {
    const colorMap = {
        blue: 'bg-blue-100 text-blue-800',
        green: 'bg-emerald-100 text-emerald-800',
        yellow: 'bg-amber-100 text-amber-800',
        purple: 'bg-violet-100 text-violet-800',
        red: 'bg-rose-100 text-rose-800',
        gray: 'bg-slate-100 text-slate-800',
        teal: 'bg-teal-100 text-teal-800'
    };
    return colorMap[color] || 'bg-slate-100 text-slate-800';
}
</script>
<?= $this->endSection() ?>
