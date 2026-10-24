<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('ActivityLog.' . $key, $params);
$isRtl = !empty($is_rtl);
$initialActivities = $activities ?? [];
$initialActivityCount = count($initialActivities);
$initialEntityCount = count($filters['entity_types'] ?? []);
$initialActionCount = count($filters['actions'] ?? []);
?>
<style>
.activity-log-shell {
    font-family: inherit;
}
.activity-hero-card {
    position: relative;
    overflow: hidden;
    border-radius: 1.75rem;
    border: 1px solid rgba(255,255,255,0.56);
    background:
        radial-gradient(circle at 9% 4%, rgba(255,255,255,0.24), transparent 29%),
        radial-gradient(circle at 88% 14%, rgba(129,140,248,0.36), transparent 28%),
        linear-gradient(135deg, #334155 0%, #2563eb 54%, #0f766e 100%);
    box-shadow: 0 30px 72px -46px rgba(15,23,42,0.78);
}
[dir="rtl"] .activity-hero-card {
    background:
        radial-gradient(circle at 91% 4%, rgba(255,255,255,0.24), transparent 29%),
        radial-gradient(circle at 12% 14%, rgba(129,140,248,0.36), transparent 28%),
        linear-gradient(225deg, #334155 0%, #2563eb 54%, #0f766e 100%);
}
.activity-hero-card::after {
    content: "";
    position: absolute;
    inset: auto -8rem -10rem auto;
    width: 24rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.13);
}
.activity-hero-content {
    position: relative;
    z-index: 1;
}
.activity-hero-avatar {
    display: inline-flex;
    width: 5rem;
    height: 5rem;
    align-items: center;
    justify-content: center;
    border-radius: 1.5rem;
    border: 1px solid rgba(255,255,255,0.22);
    background: linear-gradient(145deg, rgba(255,255,255,0.24), rgba(125,211,252,0.16));
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 22px 42px -28px rgba(15,23,42,0.8);
}
.activity-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 2.7rem;
    border-radius: 0.95rem;
    padding: 0.72rem 1rem;
    font-size: 0.875rem;
    font-weight: 850;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}
.activity-action:hover {
    transform: translateY(-1px);
}
.activity-action-blue {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    box-shadow: 0 18px 34px -24px rgba(37,99,235,0.82);
}
.activity-action-emerald {
    background: linear-gradient(135deg, #10b981, #047857);
    color: #fff;
    box-shadow: 0 18px 34px -24px rgba(16,185,129,0.82);
}
.activity-snapshot-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}
@media (min-width: 640px) {
    .activity-snapshot-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
.activity-snapshot-card {
    position: relative;
    overflow: hidden;
    border-radius: 1.25rem;
    border: 1px solid rgba(255,255,255,0.75);
    padding: 1rem;
    box-shadow: 0 18px 36px -30px rgba(15,23,42,0.42), inset 0 1px 0 rgba(255,255,255,0.78);
}
.activity-snapshot-card::after {
    content: "";
    position: absolute;
    inset: -3rem -2rem auto auto;
    width: 8rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.42);
}
.activity-snapshot-card > * {
    position: relative;
    z-index: 1;
}
.activity-snapshot-total { background: linear-gradient(135deg, #bfdbfe, #93c5fd); }
.activity-snapshot-types { background: linear-gradient(135deg, #ddd6fe, #c4b5fd); }
.activity-snapshot-actions { background: linear-gradient(135deg, #bbf7d0, #5eead4); }
.activity-snapshot-label {
    margin: 0;
    font-size: 0.7rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(15,23,42,0.62);
}
.activity-snapshot-value {
    margin-top: 0.3rem;
    font-size: 1.75rem;
    line-height: 1.1;
    font-weight: 950;
    color: #0f172a;
}
.activity-snapshot-meta {
    margin-top: 0.25rem;
    font-size: 0.78rem;
    font-weight: 750;
    color: rgba(15,23,42,0.66);
}
.activity-panel {
    overflow: hidden;
    border-radius: 1.45rem;
    border: 1px solid rgba(226,232,240,0.9);
    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
    box-shadow: 0 22px 48px -36px rgba(15,23,42,0.32), inset 0 1px 0 rgba(255,255,255,0.8);
}
.activity-filter-card {
    border-radius: 1.35rem;
    border: 1px solid rgba(255,255,255,0.76);
    background: linear-gradient(135deg, #eef2ff, #f0fdfa);
    box-shadow: 0 20px 42px -34px rgba(15,23,42,0.36), inset 0 1px 0 rgba(255,255,255,0.82);
}
.activity-filter-field {
    width: 100%;
    min-height: 2.75rem;
    border-radius: 0.9rem;
    border: 1px solid rgba(203,213,225,0.95);
    background: rgba(255,255,255,0.88);
    padding: 0.65rem 0.85rem;
    font-size: 0.9rem;
    font-weight: 650;
    color: #0f172a;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.76);
}
.activity-filter-field:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.14), inset 0 1px 0 rgba(255,255,255,0.76);
}
.activity-filter-label {
    display: block;
    margin-bottom: 0.45rem;
    font-size: 0.75rem;
    font-weight: 900;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #475569;
}
.activity-table-wrap {
    border-radius: 1.1rem;
    border: 1px solid rgba(226,232,240,0.84);
    background: rgba(255,255,255,0.82);
}
.activity-table-wrap table thead tr {
    background: rgba(248,250,252,0.94);
}
.activity-row {
    transition: background 0.16s ease;
}
.activity-row:hover {
    background: rgba(239,246,255,0.72);
}
.activity-pagination-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-height: 2.35rem;
    border-radius: 0.8rem;
    border: 1px solid rgba(203,213,225,0.9);
    background: #fff;
    padding: 0.45rem 0.8rem;
    font-size: 0.875rem;
    font-weight: 800;
    color: #475569;
}
.activity-pagination-button:hover:not(:disabled) {
    background: #f8fafc;
    color: #0f172a;
}
</style>
<!-- Activity Log Page -->
<div class="v3-operational-view activity-log-shell min-h-screen v3-surface">
    <div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">
        <section class="activity-hero-card mb-6">
            <div class="activity-hero-content p-6 sm:p-8">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                        <div class="activity-hero-avatar">
                            <i class="fas fa-history v3-title-scale"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] v3-accent-text">
                                <span><?= esc($tr('recentActivities')) ?></span>
                                <span class="h-1 w-1 rounded-full v3-soft-surface"></span>
                                <span><?= esc($tr('activities')) ?></span>
                            </div>
                            <h1 class="mt-2 v3-title-scale font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($tr('title')) ?></h1>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row xl:flex-col xl:w-[13rem]">
                        <button onclick="refreshActivities()" class="activity-action activity-action-blue">
                            <i class="fas fa-sync-alt"></i><?= esc($tr('refresh')) ?>
                        </button>
                        <button onclick="exportActivities()" class="activity-action activity-action-emerald">
                            <i class="fas fa-download"></i><?= esc($tr('export')) ?>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-6 rounded-[1.5rem] border v3-border p-4 sm:p-5">
            <div class="activity-snapshot-grid">
                <div class="activity-snapshot-card activity-snapshot-total">
                    <p class="activity-snapshot-label"><?= esc($tr('recentActivities')) ?></p>
                    <p class="activity-snapshot-value" id="activity-snapshot-total" dir="ltr"><?= esc((string) $initialActivityCount) ?></p>
                    <p class="activity-snapshot-meta"><?= esc($tr('activities')) ?></p>
                </div>
                <div class="activity-snapshot-card activity-snapshot-types">
                    <p class="activity-snapshot-label"><?= esc($tr('entityType')) ?></p>
                    <p class="activity-snapshot-value" dir="ltr"><?= esc((string) $initialEntityCount) ?></p>
                    <p class="activity-snapshot-meta"><?= esc($tr('allTypes')) ?></p>
                </div>
                <div class="activity-snapshot-card activity-snapshot-actions">
                    <p class="activity-snapshot-label"><?= esc($tr('action')) ?></p>
                    <p class="activity-snapshot-value" dir="ltr"><?= esc((string) $initialActionCount) ?></p>
                    <p class="activity-snapshot-meta"><?= esc($tr('allActions')) ?></p>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <div class="mb-6">
            <div class="activity-filter-card p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-4 lg:items-end">
                    <div class="flex-1">
                        <label class="activity-filter-label"><?= esc($tr('entityType')) ?></label>
                        <select id="entityTypeFilter" class="activity-filter-field">
                            <option value=""><?= esc($tr('allTypes')) ?></option>
                            <?php foreach ($filters['entity_types'] as $type): ?>
                                <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="activity-filter-label"><?= esc($tr('action')) ?></label>
                        <select id="actionFilter" class="activity-filter-field">
                            <option value=""><?= esc($tr('allActions')) ?></option>
                            <?php foreach ($filters['actions'] as $action): ?>
                                <option value="<?= $action ?>"><?= ucfirst($action) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="activity-filter-label"><?= esc($tr('user')) ?></label>
                        <input type="text" id="userFilter" placeholder="<?= esc($tr('searchByUserName')) ?>" class="activity-filter-field">
                    </div>
                    <div class="flex items-end">
                        <button onclick="applyFilters()" class="activity-action activity-action-blue w-full">
                            <i class="fas fa-filter"></i><?= esc($tr('filter')) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="activity-panel">
            <!-- Table Header -->
            <div class="border-b v3-border px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center v3-radius text-white">
                            <i class="fas fa-stream"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950"><?= esc($tr('recentActivities')) ?></h3>
                            <p class="text-xs font-semibold text-slate-500"><?= esc($tr('subtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="rounded-full v3-soft-surface px-3 py-1 text-sm font-bold v3-accent-text ring-1 ring-blue-100">
                            <span id="total-activities">0</span> <?= esc($tr('activities')) ?>
                        </div>
                        <div class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700 ring-1 ring-emerald-100">
                            <span id="filtered-activities">0</span> <?= esc($tr('filtered')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="activity-table-wrap m-4 overflow-x-auto sm:m-5">
                <table id="activitiesTable" class="w-full">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><?= esc($tr('activity')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><?= esc($tr('user')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><?= esc($tr('entity')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><?= esc($tr('description')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><?= esc($tr('time')) ?></th>
                            <th class="px-5 py-3 text-start text-xs font-black text-slate-500 uppercase tracking-wider"><?= esc($tr('ipAddress')) ?></th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody" class="divide-y divide-slate-100 v3-surface">
                        <!-- Activities will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="hidden p-4 text-center">
                <div class="inline-flex items-center gap-3 v3-radius v3-soft-surface px-4 py-3 font-bold v3-accent-text">
                    <div class="h-6 w-6 animate-spin rounded-full border-2 v3-border border-b-blue-700"></div>
                    <span><?= esc($tr('loadingActivities')) ?></span>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden p-4 text-center">
                <div class="mb-4 text-slate-400">
                    <i class="fas fa-history v3-title-scale"></i>
                </div>
                <h3 class="mb-2 text-lg font-black text-slate-900"><?= esc($tr('noActivitiesFound')) ?></h3>
                <p class="font-semibold text-slate-500"><?= esc($tr('noActivitiesMatchFilters')) ?></p>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="hidden border-t v3-border v3-surface px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <button id="prevPage" class="activity-pagination-button disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="fas fa-chevron-left mr-1"></i><?= esc($tr('previous')) ?>
                        </button>
                        <span id="pageInfo" class="rounded-full v3-surface px-3 py-1 text-sm font-bold text-slate-700 ring-1 ring-slate-200"></span>
                        <button id="nextPage" class="activity-pagination-button disabled:cursor-not-allowed disabled:opacity-50">
                            <?= esc($tr('next')) ?><i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-bold text-slate-700"><?= esc($tr('show')) ?></label>
                        <select id="pageSize" class="rounded-lg border v3-border v3-surface px-3 py-2 text-sm font-bold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let pageSize = 50;
let totalActivities = 0;
let filteredActivities = 0;

// Load activities on page load
document.addEventListener('DOMContentLoaded', function() {
    loadActivities();

    // Add event listeners
    document.getElementById('pageSize').addEventListener('change', function() {
        pageSize = parseInt(this.value);
        currentPage = 1;
        loadActivities();
    });

    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadActivities();
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        const maxPage = Math.ceil(totalActivities / pageSize);
        if (currentPage < maxPage) {
            currentPage++;
            loadActivities();
        }
    });
});

function loadActivities() {
    showLoading();

    const entityType = document.getElementById('entityTypeFilter').value;
    const action = document.getElementById('actionFilter').value;
    const user = document.getElementById('userFilter').value;

    const params = new URLSearchParams({
        limit: pageSize,
        offset: (currentPage - 1) * pageSize,
        entity_type: entityType,
        action: action,
        user: user
    });

    fetch(`<?= base_url('activity-log/api') ?>?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActivities(data.activities);
                totalActivities = data.total_all ?? 0;
                filteredActivities = data.total_filtered ?? 0;
                updatePagination();
                updateCounts();
            } else {
                showError(data.message || '<?= esc($tr('failedToLoadActivities'), 'js') ?>');
            }
        })
        .catch(error => {
            console.error('Error loading activities:', error);
            showError('<?= esc($tr('failedToLoadActivities'), 'js') ?>');
        })
        .finally(() => {
            hideLoading();
        });
}

function displayActivities(activities) {
    const tbody = document.getElementById('activitiesTableBody');

    if (activities.length === 0) {
        showEmpty();
        return;
    }

    hideEmpty();

    // Clear existing content safely
    tbody.textContent = '';

    activities.forEach(activity => {
        const tr = document.createElement('tr');
        tr.className = 'activity-row';

        // Activity Cell
        const tdActivity = document.createElement('td');
        tdActivity.className = 'px-5 py-4 whitespace-nowrap cursor-pointer';
        tdActivity.setAttribute('role', 'button');
        tdActivity.setAttribute('tabindex', '0');
        tdActivity.title = '<?= esc($tr('openActivityDetails'), 'js') ?>';
        tdActivity.addEventListener('click', () => navigateToEntity(activity.entity_type, activity.entity_id || 'null'));
        tdActivity.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                navigateToEntity(activity.entity_type, activity.entity_id || 'null');
            }
        });

        const divFlex = document.createElement('div');
        divFlex.className = 'flex items-center gap-3';

        const divIcon = document.createElement('div');
        divIcon.className = `flex h-9 w-9 items-center justify-center rounded-xl ${getActivityIconBgClass(activity.color)}`;
        const icon = document.createElement('i');
        icon.className = `${activity.icon} ${getActivityIconClass(activity.color)} text-sm`;
        divIcon.appendChild(icon);

        const divText = document.createElement('div');
        const divTitle = document.createElement('div');
        divTitle.className = 'text-sm font-black text-slate-900';
        divTitle.textContent = activity.title;
        const divAction = document.createElement('div');
        divAction.className = 'text-xs font-semibold text-slate-500';
        divAction.textContent = `${activity.action} • ${activity.entity_type}`;

        divText.appendChild(divTitle);
        divText.appendChild(divAction);
        divFlex.appendChild(divIcon);
        divFlex.appendChild(divText);
        tdActivity.appendChild(divFlex);

        // User Cell
        const tdUser = document.createElement('td');
        tdUser.className = 'px-5 py-4 whitespace-nowrap';
        const divUserName = document.createElement('div');
        divUserName.className = 'text-sm font-bold text-slate-900';
        divUserName.textContent = activity.user_name;
        const divUserEmail = document.createElement('div');
        divUserEmail.className = 'text-xs font-semibold text-slate-500';
        divUserEmail.textContent = activity.user_email;
        tdUser.appendChild(divUserName);
        tdUser.appendChild(divUserEmail);

        // Entity Cell
        const tdEntity = document.createElement('td');
        tdEntity.className = 'px-5 py-4 whitespace-nowrap';
        const spanBadge = document.createElement('span');
        spanBadge.className = `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-black ${getEntityBadgeClass(activity.entity_type)}`;
        spanBadge.textContent = activity.entity_type;
        tdEntity.appendChild(spanBadge);

        // Description Cell
        const tdDesc = document.createElement('td');
        tdDesc.className = 'px-5 py-4';
        const divDesc = document.createElement('div');
        divDesc.className = 'max-w-sm truncate text-sm font-semibold text-slate-800';
        divDesc.textContent = activity.description;
        tdDesc.appendChild(divDesc);

        // Time Cell
        const tdTime = document.createElement('td');
        tdTime.className = 'px-5 py-4 whitespace-nowrap';
        const divTimeFormatted = document.createElement('div');
        divTimeFormatted.className = 'text-sm font-bold text-slate-900';
        divTimeFormatted.textContent = activity.formatted_time;
        const divTimeFull = document.createElement('div');
        divTimeFull.className = 'text-xs font-semibold text-slate-500';
        divTimeFull.textContent = new Date(activity.created_at).toLocaleString();
        tdTime.appendChild(divTimeFormatted);
        tdTime.appendChild(divTimeFull);

        // IP Cell
        const tdIp = document.createElement('td');
        tdIp.className = 'px-5 py-4 whitespace-nowrap';
        const divIp = document.createElement('div');
        divIp.className = 'text-sm font-semibold text-slate-500';
        divIp.textContent = activity.ip_address || '<?= esc($tr('notAvailable'), 'js') ?>';
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
    // You can implement a toast notification here
    console.error(message);
}

function updatePagination() {
    const maxPage = Math.max(1, Math.ceil(filteredActivities / pageSize));
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');

    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= maxPage;

    pageInfo.textContent = `<?= esc($tr('pageInfo', ['current' => '{current}', 'max' => '{max}']), 'js') ?>`
        .replace('{current}', currentPage)
        .replace('{max}', maxPage);

    if (filteredActivities > 0) {
        document.getElementById('paginationContainer').classList.remove('hidden');
    } else {
        document.getElementById('paginationContainer').classList.add('hidden');
    }
}

function updateCounts() {
    document.getElementById('total-activities').textContent = totalActivities;
    document.getElementById('filtered-activities').textContent = filteredActivities;
    const snapshotTotal = document.getElementById('activity-snapshot-total');
    if (snapshotTotal) {
        snapshotTotal.textContent = filteredActivities || totalActivities || 0;
    }
}

function applyFilters() {
    currentPage = 1;
    loadActivities();
}

function refreshActivities() {
    loadActivities();
}

function exportActivities() {
    const entityType = document.getElementById('entityTypeFilter').value;
    const action = document.getElementById('actionFilter').value;
    const user = document.getElementById('userFilter').value;

    const params = new URLSearchParams({
        entity_type: entityType,
        action: action,
        user: user
    });

    window.location.href = `<?= base_url('activity-log/export') ?>?${params.toString()}`;
}

// Navigate to entity details page
function navigateToEntity(entityType, entityId) {
    if (!entityId || entityId === 'null') {
        console.log('No entity ID available for navigation');
        return;
    }

    const baseUrl = '<?= base_url() ?>';
    let url = '';

    switch (entityType) {
        case 'patient':
            url = `${baseUrl}patient/${entityId}`;
            break;
        case 'appointment':
            url = `${baseUrl}appointment/${entityId}`;
            break;
        case 'treatment':
            url = `${baseUrl}treatment/${entityId}`;
            break;
        case 'examination':
            url = `${baseUrl}examination/${entityId}`;
            break;
        case 'prescription':
            url = `${baseUrl}prescription/${entityId}`;
            break;
        case 'finance':
            url = `${baseUrl}finance/${entityId}`;
            break;
        case 'contract':
            url = `${baseUrl}contracts/${entityId}`;
            break;
        case 'inventory':
            url = `${baseUrl}inventory/${entityId}`;
            break;
        case 'user':
            url = `${baseUrl}user/${entityId}`;
            break;
        default:
            console.log(`Unknown entity type: ${entityType}`);
            return;
    }

    // Navigate to the entity details page
    window.location.href = url;
}

// Helper functions for styling
function getActivityIconBgClass(color) {
    const colorMap = {
        'blue': 'bg-blue-100',
        'green': 'bg-green-100',
        'yellow': 'bg-yellow-100',
        'purple': 'bg-purple-100',
        'red': 'bg-red-100',
        'gray': 'bg-gray-100',
        'teal': 'bg-teal-100',
        'indigo': 'bg-indigo-100'
    };
    return colorMap[color] || 'bg-gray-100';
}

function getActivityIconClass(color) {
    const colorMap = {
        'blue': 'text-blue-600',
        'green': 'text-green-600',
        'yellow': 'text-yellow-600',
        'purple': 'text-purple-600',
        'red': 'text-red-600',
        'gray': 'text-gray-600',
        'teal': 'text-teal-600',
        'indigo': 'text-indigo-600'
    };
    return colorMap[color] || 'text-gray-600';
}

function getEntityBadgeClass(entityType) {
    const colorMap = {
        'patient': 'bg-green-100 text-green-800',
        'appointment': 'bg-blue-100 text-blue-800',
        'examination': 'bg-purple-100 text-purple-800',
        'treatment': 'bg-orange-100 text-orange-800',
        'inventory': 'bg-yellow-100 text-yellow-800',
        'finance': 'bg-emerald-100 text-emerald-800',
        'prescription': 'bg-indigo-100 text-indigo-800',
        'user': 'bg-slate-100 text-slate-700',
        'contract': 'bg-violet-100 text-violet-800'
    };
    return colorMap[entityType] || 'bg-gray-100 text-gray-800';
}
</script>
<?= $this->endSection() ?>
