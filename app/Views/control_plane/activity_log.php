<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-zinc-100">
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-700 to-gray-800 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-history text-amber-400 text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Activity Log</h1>
                            <p class="text-gray-600">Track all control plane actions and system events</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="refreshActivities()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                        <button onclick="exportActivities()" class="px-5 py-2.5 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg transition-all">
                            <i class="fas fa-download mr-2"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:space-x-4 space-y-4 lg:space-y-0">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                        <select id="eventTypeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Events</option>
                            <?php foreach ($filters['event_types'] as $type): ?>
                                <option value="<?= esc($type) ?>"><?= esc(ucwords(str_replace('_', ' ', $type))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                        <input type="text" id="userFilter" placeholder="Search by user name or email..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-center self-end pt-6">
                        <button onclick="applyFilters()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-700 to-slate-900 px-6 py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-history text-amber-400 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wide">Recent Activities</h3>
                        <p class="text-xs text-slate-400"><span id="total-activities">0</span> total · <span id="filtered-activities">0</span> filtered</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="activitiesTable" class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>

            <div id="loadingState" class="hidden p-8 text-center">
                <div class="inline-flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-600">Loading activities...</span>
                </div>
            </div>

            <div id="emptyState" class="hidden p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-history text-4xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No activities found</h3>
                <p class="text-gray-500">No activities match your current filters.</p>
            </div>

            <div id="paginationContainer" class="hidden px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button id="prevPage" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i>Previous
                        </button>
                        <span id="pageInfo" class="text-sm text-gray-700"></span>
                        <button id="nextPage" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next<i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-700">Show:</label>
                        <select id="pageSize" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                        </select>
                    </div>
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
                displayActivities(data.activities);
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
        showEmpty();
        return;
    }

    hideEmpty();
    tbody.textContent = '';

    activities.forEach(activity => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 transition-colors duration-200';

        const tdActivity = document.createElement('td');
        tdActivity.className = 'px-6 py-4 whitespace-nowrap';

        const divFlex = document.createElement('div');
        divFlex.className = 'flex items-center space-x-3';

        const divIcon = document.createElement('div');
        divIcon.className = `w-8 h-8 ${getActivityIconBgClass(activity.color)} rounded-full flex items-center justify-center`;
        const icon = document.createElement('i');
        icon.className = `${activity.icon} ${getActivityIconClass(activity.color)} text-sm`;
        divIcon.appendChild(icon);

        const divText = document.createElement('div');
        const divTitle = document.createElement('div');
        divTitle.className = 'text-sm font-medium text-gray-900';
        divTitle.textContent = activity.title;
        const divAction = document.createElement('div');
        divAction.className = `text-xs font-medium px-2 py-0.5 rounded-full inline-flex items-center ${getBadgeClassFromColor(activity.color)}`;
        divAction.textContent = activity.action;

        divText.appendChild(divTitle);
        divText.appendChild(divAction);
        divFlex.appendChild(divIcon);
        divFlex.appendChild(divText);
        tdActivity.appendChild(divFlex);

        const tdUser = document.createElement('td');
        tdUser.className = 'px-6 py-4 whitespace-nowrap';
        const divUserName = document.createElement('div');
        divUserName.className = 'text-sm text-gray-900';
        divUserName.textContent = activity.user_name;
        const divUserEmail = document.createElement('div');
        divUserEmail.className = 'text-xs text-gray-500';
        divUserEmail.textContent = activity.user_email;
        tdUser.appendChild(divUserName);
        tdUser.appendChild(divUserEmail);

        const tdEntity = document.createElement('td');
        tdEntity.className = 'px-6 py-4 whitespace-nowrap';
        const spanBadge = document.createElement('span');
        spanBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBadgeClassFromColor(activity.color)}`;
        spanBadge.textContent = activity.entity_type;
        tdEntity.appendChild(spanBadge);

        const tdDesc = document.createElement('td');
        tdDesc.className = 'px-6 py-4';
        const divDesc = document.createElement('div');
        divDesc.className = 'text-sm text-gray-900 max-w-xs truncate';
        divDesc.textContent = activity.description;
        tdDesc.appendChild(divDesc);

        const tdTime = document.createElement('td');
        tdTime.className = 'px-6 py-4 whitespace-nowrap';
        const divTimeFormatted = document.createElement('div');
        divTimeFormatted.className = 'text-sm text-gray-900';
        divTimeFormatted.textContent = activity.formatted_time;
        const divTimeFull = document.createElement('div');
        divTimeFull.className = 'text-xs text-gray-500';
        divTimeFull.textContent = new Date(activity.created_at).toLocaleString();
        tdTime.appendChild(divTimeFormatted);
        tdTime.appendChild(divTimeFull);

        const tdIp = document.createElement('td');
        tdIp.className = 'px-6 py-4 whitespace-nowrap';
        const divIp = document.createElement('div');
        divIp.className = 'text-sm text-gray-500';
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
        'blue': 'bg-blue-100',
        'green': 'bg-green-100',
        'yellow': 'bg-yellow-100',
        'purple': 'bg-purple-100',
        'red': 'bg-red-100',
        'gray': 'bg-gray-100',
        'teal': 'bg-teal-100'
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
        'teal': 'text-teal-600'
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
        'finance': 'bg-emerald-100 text-emerald-800'
    };
    return colorMap[entityType] || 'bg-gray-100 text-gray-800';
}

function getBadgeClassFromColor(color) {
    const colorMap = {
        'blue': 'bg-blue-100 text-blue-800',
        'green': 'bg-green-100 text-green-800',
        'yellow': 'bg-yellow-100 text-yellow-800',
        'purple': 'bg-purple-100 text-purple-800',
        'red': 'bg-red-100 text-red-800',
        'gray': 'bg-gray-100 text-gray-800',
        'teal': 'bg-teal-100 text-teal-800'
    };
    return colorMap[color] || 'bg-gray-100 text-gray-800';
}
</script>
<?= $this->endSection() ?>
