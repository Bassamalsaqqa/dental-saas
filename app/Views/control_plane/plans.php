<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cubes text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Plan Catalog</h1>
                            <p class="text-gray-600">Define pricing, limits, and entitlements</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Catalog</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Total Plans -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-indigo-500/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-indigo-500 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-indigo-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-layer-group text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-indigo-100 px-3 py-1.5 rounded-full border border-indigo-200">
                                <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                                <span class="text-indigo-700 font-bold text-xs">All Time</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Plans</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-indigo-900"><?= number_format($stats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-indigo-100 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-indigo-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Catalog Items</p>
                        </div>
                    </div>
                </div>

                <!-- Active -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-green-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span class="text-emerald-700 font-bold text-xs">Active</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Active Plans</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($stats['active'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-emerald-200 to-green-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Currently Available</p>
                        </div>
                    </div>
                </div>

                <!-- In View -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-eye text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-indigo-100 px-3 py-1.5 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <span class="text-blue-700 font-bold text-xs">Filtered</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">In View</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-blue-600" style="color: #2563eb !important;"><?= number_format($stats['filtered'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-blue-200 to-indigo-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Filtered Results</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Table Section -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden mb-12">
            <!-- Header & Filters -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Catalog Plans</h3>
                    </div>
                    
                    <form method="get" class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Search plans..."
                                   class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 w-64">
                        </div>

                        <!-- Status Filter -->
                        <select name="status" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white" onchange="this.form.submit()">
                            <?php $currentStatus = $filters['status'] ?? ''; ?>
                            <option value="" <?= $currentStatus === '' ? 'selected' : '' ?>>All Status</option>
                            <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>

                        <!-- Duration Filter -->
                        <select name="duration" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white" onchange="this.form.submit()">
                            <?php $currentDuration = $filters['duration'] ?? ''; ?>
                            <option value="" <?= $currentDuration === '' ? 'selected' : '' ?>>All Durations</option>
                            <option value="monthly" <?= $currentDuration === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $currentDuration === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            <option value="lifetime" <?= $currentDuration === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                        </select>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 border-l border-gray-300 pl-3">
                            <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= base_url('controlplane/plans') ?>" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="Reset">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>

                        <!-- Create Button -->
                        <a href="<?= base_url('controlplane/plans/create') ?>" class="ml-2 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                            <i class="fas fa-plus"></i>
                            Create Plan
                        </a>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cube text-emerald-600"></i>
                                    <span>Plan Name</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-tag text-emerald-600"></i>
                                    <span>Pricing</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-sliders-h text-emerald-600"></i>
                                    <span>Limits</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-emerald-600"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end space-x-2">
                                    <i class="fas fa-cogs text-emerald-600"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($plans)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-cubes text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="font-medium">No plans found</p>
                                        <p class="text-xs mt-1">Create a new plan to populate the catalog.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($plans as $row): ?>
                                <?php
                                    $plan = $row['plan'] ?? [];
                                    $limits = $row['limits'] ?? [];
                                    $price = $limits['price'] ?? 0;
                                    $duration = ucfirst($limits['duration'] ?? 'monthly');
                                    $trial = (int) ($limits['trial_days'] ?? 0);
                                    $isActive = ($plan['status'] ?? 'inactive') === 'active';
                                    
                                    // Format limits
                                    $patients = $limits['patients_active_max'] ?? -1;
                                    $users = $limits['users_max'] ?? -1;
                                    $storage = $limits['storage_gb'] ?? -1;
                                    $limitText = [];
                                    if ($patients === -1) $limitText[] = 'Unlimited Pts'; else $limitText[] = "$patients Pts";
                                    if ($users === -1) $limitText[] = 'Unlimited Users'; else $limitText[] = "$users Users";
                                    if ($storage === -1) $limitText[] = 'Unlimited Storage'; else $limitText[] = "$storage GB";
                                ?>
                                <tr id="plan-row-<?= $plan['id'] ?>" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-xs">
                                                <?= strtoupper(substr($plan['name'] ?? 'PL', 0, 2)) ?>
                                            </div>
                                            <div>
                                                <span class="block text-sm font-medium text-slate-900"><?= esc($plan['name'] ?? 'Plan') ?></span>
                                                <span class="block text-xs text-slate-500 font-mono">ID: <?= esc($plan['id']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900">$<?= number_format((float) $price, 2) ?></div>
                                        <div class="text-xs text-slate-500">per <?= strtolower($duration) ?></div>
                                        <?php if ($trial > 0): ?>
                                            <div class="text-[10px] text-emerald-600 font-medium mt-0.5">+<?= $trial ?> days trial</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($limitText as $limit): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                    <?= esc($limit) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" id="status-badge-<?= $plan['id'] ?>">
                                        <?php if ($isActive): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-check-circle mr-1.5"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                <i class="fas fa-pause-circle mr-1.5"></i> Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Edit -->
                                            <a href="<?= base_url('controlplane/plans/' . $plan['id'] . '/edit') ?>" 
                                               class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200"
                                               title="Edit Plan">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Toggle Status -->
                                            <button type="button" 
                                                    id="toggle-btn-<?= $plan['id'] ?>"
                                                    onclick="togglePlanStatus(<?= $plan['id'] ?>)"
                                                    class="p-2 <?= $isActive ? 'text-amber-600 hover:text-amber-700 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50' ?> rounded-md transition-colors duration-200"
                                                    title="<?= $isActive ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas <?= $isActive ? 'fa-pause' : 'fa-play' ?>"></i>
                                            </button>

                                            <!-- Delete -->
                                            <button type="button" 
                                                    onclick="openDeleteModal(<?= $plan['id'] ?>, '<?= esc($plan['name']) ?>')"
                                                    class="p-2 text-rose-600 hover:text-rose-700 rounded-md hover:bg-rose-50 transition-colors duration-200" 
                                                    title="Delete Plan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-3xl border border-rose-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4 bg-rose-50 rounded-t-3xl">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-rose-600 font-bold">Irreversible Action</p>
                <h3 class="text-lg font-bold text-rose-900">Delete Plan</h3>
            </div>
            <button type="button" class="rounded-full p-2 text-rose-400 hover:text-slate-700 hover:bg-rose-100" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-6 space-y-4 text-sm text-slate-600">
            <p class="font-medium text-slate-800">
                You are about to permanently delete <span id="deletePlanName" class="font-bold text-rose-600">this plan</span>.
            </p>
            <div class="bg-rose-50 p-4 rounded-xl text-rose-800 text-xs border border-rose-100">
                <ul class="list-disc list-inside space-y-1">
                    <li>This action <strong>cannot be undone</strong>.</li>
                    <li>If subscriptions use this plan, deletion will be blocked (deactivate instead).</li>
                </ul>
            </div>
            <p>Type <span class="font-mono font-bold text-slate-900">DELETE</span> to confirm.</p>
            <input type="text" id="deleteConfirmInput" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-rose-500 focus:ring-rose-500"
                   placeholder="DELETE" oninput="updateDeleteState()">
        </div>
        <form id="deleteForm" method="post" class="px-6 pb-6 flex items-center justify-end gap-3">
            <?= csrf_field() ?>
            <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition" onclick="closeDeleteModal()">
                Cancel
            </button>
            <button id="deleteConfirmButton" type="submit" class="px-6 py-2 rounded-xl text-white text-sm font-bold cursor-not-allowed shadow-lg transition-all" disabled style="background-color: #e11d48 !important; color: #ffffff !important; opacity: 1 !important;">
                <span>Permanently Delete</span>
            </button>
        </form>
    </div>
</div>

<script>
const baseUrl = '<?= base_url() ?>';
const csrfMetaName = document.querySelector('meta[name="csrf-name"]');
const csrfMetaToken = document.querySelector('meta[name="csrf-token"]');
const csrfCookieName = '<?= esc(config('Security')->cookieName) ?>';
let deletePlanId = '';
let deletePlanName = '';

function getCookieValue(name) {
    if (!name) return '';
    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
        const [key, ...valueParts] = cookie.trim().split('=');
        if (key === name) return decodeURIComponent(valueParts.join('='));
    }
    return '';
}

function getCsrfToken() {
    return csrfMetaToken ? csrfMetaToken.getAttribute('content') : '';
}

function getCsrfName() {
    return csrfMetaName ? csrfMetaName.getAttribute('content') : '<?= csrf_token() ?>';
}

function refreshCsrfToken() {
    const cookieToken = getCookieValue(csrfCookieName);
    if (cookieToken && csrfMetaToken) {
        csrfMetaToken.setAttribute('content', cookieToken);
    }
}

// Toggle Plan Status AJAX
function togglePlanStatus(planId) {
    // No confirmation requested for toggle
    const formData = new FormData();
    const csrfName = getCsrfName();
    const csrfToken = getCsrfToken();
    formData.append(csrfName, csrfToken);

    fetch(`${baseUrl}controlplane/plans/${planId}/toggle`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const isActive = data.new_status === 'active';
            const badgeEl = document.getElementById(`status-badge-${planId}`);
            const btnEl = document.getElementById(`toggle-btn-${planId}`);

            if (badgeEl) {
                if (isActive) {
                    badgeEl.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"><i class="fas fa-check-circle mr-1.5"></i> Active</span>`;
                } else {
                    badgeEl.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><i class="fas fa-pause-circle mr-1.5"></i> Inactive</span>`;
                }
            }

            if (btnEl) {
                btnEl.title = isActive ? 'Deactivate' : 'Activate';
                if (isActive) {
                    btnEl.className = "p-2 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded-md transition-colors duration-200";
                    btnEl.innerHTML = `<i class="fas fa-pause"></i>`;
                } else {
                    btnEl.className = "p-2 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-md transition-colors duration-200";
                    btnEl.innerHTML = `<i class="fas fa-play"></i>`;
                }
            }
        } else {
            alert(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred.');
    })
    .finally(() => {
        refreshCsrfToken(); 
    });
}

// Delete Modal Functions
function openDeleteModal(id, name) {
    const modal = document.getElementById('deleteModal');
    const nameEl = document.getElementById('deletePlanName');
    const inputEl = document.getElementById('deleteConfirmInput');
    
    deletePlanId = id;
    deletePlanName = name;
    
    if (nameEl) nameEl.textContent = name;
    if (inputEl) inputEl.value = '';
    
    updateDeleteState();
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function updateDeleteState() {
    const inputEl = document.getElementById('deleteConfirmInput');
    const buttonEl = document.getElementById('deleteConfirmButton');
    if (!inputEl || !buttonEl) return;
    
    const matches = inputEl.value === 'DELETE';
    buttonEl.disabled = !matches;
    if (matches) {
        buttonEl.classList.remove('opacity-50', 'cursor-not-allowed');
        buttonEl.classList.add('hover:bg-rose-700');
    } else {
        buttonEl.classList.add('opacity-50', 'cursor-not-allowed');
        buttonEl.classList.remove('hover:bg-rose-700');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const deleteForm = document.getElementById('deleteForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('deleteConfirmButton');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';

            const formData = new FormData();
            const csrfName = getCsrfName();
            const csrfToken = getCsrfToken();
            formData.append(csrfName, csrfToken);

            fetch(`${baseUrl}controlplane/plans/${deletePlanId}/delete`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    // Remove row from DOM
                    const row = document.getElementById(`plan-row-${deletePlanId}`);
                    if (row) row.remove();
                } else {
                    alert(data.message || 'Failed to delete plan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                refreshCsrfToken();
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
