<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Activity Log Page -->
<div class="min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                <div class="space-y-2">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">Activity Log</h1>
                    <p class="text-gray-600 text-base lg:text-lg">Track all system activities and user actions</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <button onclick="refreshActivities()" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                    <button onclick="exportActivities()" class="inline-flex items-center justify-center px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:space-x-4 space-y-4 lg:space-y-0">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Entity Type</label>
                        <select id="entityTypeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Types</option>
                            <?php foreach ($filters['entity_types'] as $type): ?>
                                <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <select id="actionFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Actions</option>
                            <?php foreach ($filters['actions'] as $action): ?>
                                <option value="<?= $action ?>"><?= ucfirst($action) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                        <input type="text" id="userFilter" placeholder="Search by user name..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button onclick="applyFilters()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-teal-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-600">
                            <span id="total-activities">0</span> activities
                        </div>
                        <div class="text-sm text-gray-600">
                            <span id="filtered-activities">0</span> filtered
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table id="activitiesTable" class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Activities will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="hidden p-8 text-center">
                <div class="inline-flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-600">Loading activities...</span>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-history text-4xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No activities found</h3>
                <p class="text-gray-500">No activities match your current filters.</p>
            </div>

            <!-- Pagination -->
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
        user_id: user
    });
    
    fetch(`<?= base_url('activity-log/api') ?>?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActivities(data.activities);
                totalActivities = data.total;
                updatePagination();
                updateCounts();
            } else {
                showError(data.message || 'Failed to load activities');
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
    
    // Clear existing content safely
    tbody.textContent = '';
    
    activities.forEach(activity => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 transition-colors duration-200 cursor-pointer';
        tr.onclick = () => navigateToEntity(activity.entity_type, activity.entity_id || 'null');

        // Activity Cell
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
        divAction.className = 'text-xs text-gray-500';
        divAction.textContent = `${activity.action} • ${activity.entity_type}`;
        
        divText.appendChild(divTitle);
        divText.appendChild(divAction);
        divFlex.appendChild(divIcon);
        divFlex.appendChild(divText);
        tdActivity.appendChild(divFlex);

        // User Cell
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

        // Entity Cell
        const tdEntity = document.createElement('td');
        tdEntity.className = 'px-6 py-4 whitespace-nowrap';
        const spanBadge = document.createElement('span');
        spanBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getEntityBadgeClass(activity.entity_type)}`;
        spanBadge.textContent = activity.entity_type;
        tdEntity.appendChild(spanBadge);

        // Description Cell
        const tdDesc = document.createElement('td');
        tdDesc.className = 'px-6 py-4';
        const divDesc = document.createElement('div');
        divDesc.className = 'text-sm text-gray-900 max-w-xs truncate';
        divDesc.textContent = activity.description;
        tdDesc.appendChild(divDesc);

        // Time Cell
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

        // IP Cell
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
    // You can implement a toast notification here
    console.error(message);
}

function updatePagination() {
    const maxPage = Math.ceil(totalActivities / pageSize);
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');
    
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= maxPage;
    
    pageInfo.textContent = `Page ${currentPage} of ${maxPage}`;
    
    if (totalActivities > 0) {
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
    currentPage = 1;
    loadActivities();
}

function refreshActivities() {
    loadActivities();
}

function exportActivities() {
    // Implement export functionality
    alert('Export functionality will be implemented');
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
</script>
<?= $this->endSection() ?>
