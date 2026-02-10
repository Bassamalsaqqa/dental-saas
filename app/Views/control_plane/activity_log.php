<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-history text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Activity Log</h1>
                            <p class="text-gray-600">Comprehensive audit trail of Control Plane actions</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="refreshActivities()" class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                        <button onclick="exportActivities()" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all shadow-sm">
                            <i class="fas fa-download mr-2"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/30 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Event Type</label>
                        <select id="eventTypeFilter" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Events</option>
                            <?php foreach ($filters['event_types'] as $type): ?>
                                <option value="<?= esc($type) ?>"><?= esc(ucwords(str_replace('_', ' ', $type))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">User</label>
                        <input type="text" id="userFilter" placeholder="Search by user name or email..." class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button onclick="applyFilters()" class="w-full px-6 py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-md hover:shadow-lg">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/30 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Audit Trail</h3>
                </div>
                <div class="text-xs text-gray-500 font-medium">
                    <span id="total-activities">0</span> records found
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table id="activitiesTable" class="w-full">
                    <thead class="bg-gray-50/50 backdrop-blur-sm">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Route / Details</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Source</th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody" class="divide-y divide-gray-100 bg-transparent">
                        <!-- Activities will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="hidden p-12 text-center">
                <div class="inline-flex items-center justify-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                    <span class="text-gray-600 font-medium">Loading audit data...</span>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No records found</h3>
                <p class="text-gray-500 text-sm">Try adjusting your filters.</p>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="hidden px-6 py-4 border-t border-gray-100 bg-white/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button id="prevPage" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </button>
                        <span id="pageInfo" class="text-xs font-bold text-gray-500 uppercase tracking-wide"></span>
                        <button id="nextPage" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-xs font-medium text-gray-500">Rows per page:</label>
                        <select id="pageSize" class="px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <!-- Detail Panel -->
        <div id="activityDetailOverlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4 py-6">
            <div id="activityDetailPanel" class="w-full max-w-lg bg-white/95 border border-white/40 rounded-3xl shadow-2xl p-6 transition duration-200 transform scale-95">
                <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.5em] text-gray-400 font-semibold">Activity Detail</p>
                    <h3 class="text-lg font-bold text-gray-800" id="detailTitle">Select a log entry</h3>
                </div>
                <button type="button" onclick="hideActivityDetail()" class="text-gray-500 hover:text-gray-800 text-sm font-semibold flex items-center gap-1">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
            <div class="grid gap-3 md:grid-cols-2 text-sm text-gray-600">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Event / Route</p>
                    <p id="detailEventRoute" class="font-medium text-gray-800"></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Performed by</p>
                    <p id="detailUser" class="font-medium text-gray-800"></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Method</p>
                    <p id="detailMethod" class="font-medium text-gray-800"></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">IP</p>
                    <p id="detailIp" class="font-medium text-gray-800"></p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Metadata</p>
                <pre id="detailMetadata" class="bg-slate-900 text-white text-xs p-3 rounded-lg max-h-48 overflow-auto">Select a row to view metadata.</pre>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let pageSize = 50;
let totalActivities = 0;
const activityDetailOverlay = document.getElementById('activityDetailOverlay');
const activityDetailPanel = document.getElementById('activityDetailPanel');
const detailTitle = document.getElementById('detailTitle');
const detailUser = document.getElementById('detailUser');
const detailEventRoute = document.getElementById('detailEventRoute');
const detailMethod = document.getElementById('detailMethod');
const detailIp = document.getElementById('detailIp');
const detailMetadata = document.getElementById('detailMetadata');

document.addEventListener('DOMContentLoaded', function() {
    loadActivities();
    
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
    
    const eventType = document.getElementById('eventTypeFilter').value;
    const user = document.getElementById('userFilter').value;
    
    const params = new URLSearchParams({
        limit: pageSize,
        offset: (currentPage - 1) * pageSize,
        event_type: eventType,
        user: user
    });
    
    fetch(`<?= base_url('controlplane/activity-log/api') ?>?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActivities(data.activities);
                totalActivities = data.total;
                updatePagination();
                document.getElementById('total-activities').textContent = totalActivities;
            } else {
                console.error('API Error:', data);
            }
        })
        .catch(error => {
            console.error('Error loading activities:', error);
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
        tr.className = 'hover:bg-indigo-50/30 transition-colors duration-200 group/row';

        // Event Cell
        const tdEvent = document.createElement('td');
        tdEvent.className = 'px-6 py-4 whitespace-nowrap';
        const divFlex = document.createElement('div');
        divFlex.className = 'flex items-center space-x-3';
        
        const divIcon = document.createElement('div');
        divIcon.className = `w-8 h-8 ${getActivityIconBgClass(activity.color)} rounded-lg flex items-center justify-center shadow-sm`;
        const icon = document.createElement('i');
        icon.className = `${activity.icon} ${getActivityIconClass(activity.color)} text-sm`;
        divIcon.appendChild(icon);
        
        const divText = document.createElement('div');
        const divTitle = document.createElement('div');
        divTitle.className = 'text-sm font-bold text-gray-900';
        divTitle.textContent = activity.title;
        const divAction = document.createElement('div');
        divAction.className = 'text-xs text-gray-500 font-mono';
        divAction.textContent = activity.action;
        
        divText.appendChild(divTitle);
        divText.appendChild(divAction);
        divFlex.appendChild(divIcon);
        divFlex.appendChild(divText);
        tdEvent.appendChild(divFlex);

        // User Cell
        const tdUser = document.createElement('td');
        tdUser.className = 'px-6 py-4 whitespace-nowrap';
        const divUserName = document.createElement('div');
        divUserName.className = 'text-sm font-medium text-gray-900';
        divUserName.textContent = activity.user_name;
        const divUserEmail = document.createElement('div');
        divUserEmail.className = 'text-xs text-gray-500';
        divUserEmail.textContent = activity.user_email;
        tdUser.appendChild(divUserName);
        tdUser.appendChild(divUserEmail);

        // Route/Details Cell
        const tdDesc = document.createElement('td');
        tdDesc.className = 'px-6 py-4';
        const divDesc = document.createElement('div');
        divDesc.className = 'text-sm text-gray-700 font-mono bg-slate-50 px-2 py-1 rounded border border-slate-100 inline-block';
        divDesc.textContent = activity.description;
        tdDesc.appendChild(divDesc);

        // Time Cell
        const tdTime = document.createElement('td');
        tdTime.className = 'px-6 py-4 whitespace-nowrap';
        const divTimeFormatted = document.createElement('div');
        divTimeFormatted.className = 'text-sm font-medium text-gray-900';
        divTimeFormatted.textContent = activity.formatted_time;
        const divTimeFull = document.createElement('div');
        divTimeFull.className = 'text-xs text-gray-500';
        divTimeFull.textContent = new Date(activity.created_at).toLocaleString();
        tdTime.appendChild(divTimeFormatted);
        tdTime.appendChild(divTimeFull);

        // IP Cell
        const tdIp = document.createElement('td');
        tdIp.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono';
        tdIp.textContent = activity.ip_address || '-';

        tr.appendChild(tdEvent);
        tr.appendChild(tdUser);
        tr.appendChild(tdDesc);
        tr.appendChild(tdTime);
        tr.appendChild(tdIp);
        
        tr.addEventListener('click', () => showActivityDetail(activity));
        tbody.appendChild(tr);
    });
}

function showActivityDetail(activity) {
    if (!activityDetailPanel) return;
    detailTitle.textContent = activity.title || 'Activity Detail';
    detailUser.textContent = `${activity.user_name} (${activity.user_email || 'unknown'})`;
    detailEventRoute.textContent = `${activity.description || activity.route || 'N/A'}`;
    detailMethod.textContent = activity.action || '-';
    detailIp.textContent = activity.ip_address || 'Not recorded';
    detailMetadata.textContent = activity.metadata ? JSON.stringify(activity.metadata, null, 2) : 'No metadata logged.';
    if (activityDetailOverlay) {
        activityDetailOverlay.classList.remove('hidden');
    }
    activityDetailPanel.classList.remove('scale-95');
    activityDetailPanel.classList.add('scale-100');
}

function hideActivityDetail() {
    if (activityDetailOverlay) {
        activityDetailOverlay.classList.add('hidden');
    }
    activityDetailPanel.classList.remove('scale-100');
    activityDetailPanel.classList.add('scale-95');
}

if (activityDetailOverlay) {
    activityDetailOverlay.addEventListener('click', (event) => {
        if (event.target === activityDetailOverlay) {
            hideActivityDetail();
        }
    });
}

function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('activitiesTable').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('paginationContainer').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('activitiesTable').classList.remove('hidden');
}

function showEmpty() {
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('activitiesTable').classList.add('hidden');
    document.getElementById('paginationContainer').classList.add('hidden');
}

function hideEmpty() {
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('paginationContainer').classList.remove('hidden');
}

function applyFilters() {
    currentPage = 1;
    loadActivities();
}

function refreshActivities() {
    loadActivities();
}

function exportActivities() {
    const eventType = document.getElementById('eventTypeFilter').value;
    const user = document.getElementById('userFilter').value;

    const params = new URLSearchParams({
        event_type: eventType,
        user: user
    });

    window.location.href = `<?= base_url('controlplane/activity-log/export') ?>?${params.toString()}`;
}

function updatePagination() {
    const maxPage = Math.ceil(totalActivities / pageSize);
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');
    
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= maxPage;
    
    pageInfo.textContent = `Page ${currentPage} of ${maxPage || 1}`;
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
</script>
<?= $this->endSection() ?>
