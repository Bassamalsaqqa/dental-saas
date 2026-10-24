<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="v3-platform-workspace min-h-screen relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">



    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-4">
            <div class="cp-surface border cp-border cp-radius p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 cp-radius flex items-center justify-center text-white">
                            <i class="fas fa-cubes text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold text-gray-900"><?= esc(lang('V3Platform.planCatalog')) ?></h1>
                            <p class="text-gray-600"><?= esc(lang('V3Platform.definePricingLimitsAndEntitlements')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full cp-soft-surface text-emerald-700 text-xs font-semibold"><?= esc(lang('V3Platform.globalMode')) ?></span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold"><?= esc(lang('V3Platform.catalog')) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                <!-- Total Plans -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius cp-primary-surface text-white transition-all duration-200">
                                    <i class="fas fa-layer-group text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 cp-soft-surface px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.allTime')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.totalPlans')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black cp-accent"><?= number_format($stats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 cp-soft-surface rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full cp-primary-surface rounded-full"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.catalogItems')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Active -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="text-emerald-700 font-bold text-xs"><?= esc(lang('V3Platform.active')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.activePlans')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($stats['active'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.currentlyAvailable')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- In View -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-eye text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.filtered2')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.inView')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black cp-accent" style="color: #2563eb !important;"><?= number_format($stats['filtered'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.filteredResults')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Table Section -->
        <div class="cp-surface cp-radius border cp-border overflow-hidden mb-12">
            <!-- Header & Filters -->
            <div class="px-6 py-4 border-b cp-border cp-surface">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('V3Platform.catalogPlans')) ?></h3>
                    </div>

                    <form method="get" class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="<?= esc(lang('V3Platform.searchPlans'), 'attr') ?>"
                                   class="pl-10 pr-4 py-2 rounded-lg border cp-border text-sm focus:ring-emerald-500 focus:border-emerald-500 w-64">
                        </div>

                        <!-- Status Filter -->
                        <select name="status" class="py-2 pl-3 pr-8 rounded-lg border cp-border text-sm focus:ring-emerald-500 focus:border-emerald-500 cp-surface" onchange="this.form.submit()">
                            <?php $currentStatus = $filters['status'] ?? ''; ?>
                            <option value="" <?= $currentStatus === '' ? 'selected' : '' ?>><?= esc(lang('V3Platform.allStatus')) ?></option>
                            <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>><?= esc(lang('V3Platform.active')) ?></option>
                            <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>><?= esc(lang('V3Platform.inactive')) ?></option>
                        </select>

                        <!-- Duration Filter -->
                        <select name="duration" class="py-2 pl-3 pr-8 rounded-lg border cp-border text-sm focus:ring-emerald-500 focus:border-emerald-500 cp-surface" onchange="this.form.submit()">
                            <?php $currentDuration = $filters['duration'] ?? ''; ?>
                            <option value="" <?= $currentDuration === '' ? 'selected' : '' ?>><?= esc(lang('V3Platform.allDurations')) ?></option>
                            <option value="monthly" <?= $currentDuration === 'monthly' ? 'selected' : '' ?>><?= esc(lang('V3Platform.monthly')) ?></option>
                            <option value="yearly" <?= $currentDuration === 'yearly' ? 'selected' : '' ?>><?= esc(lang('V3Platform.yearly')) ?></option>
                            <option value="lifetime" <?= $currentDuration === 'lifetime' ? 'selected' : '' ?>><?= esc(lang('V3Platform.lifetime')) ?></option>
                        </select>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 border-l cp-border pl-3">
                            <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="<?= esc(lang('V3Platform.filter'), 'attr') ?>">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= base_url('controlplane/plans') ?>" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="<?= esc(lang('V3Platform.reset'), 'attr') ?>">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>

                        <!-- Create Button -->
                        <a href="<?= base_url('controlplane/plans/create') ?>" class="ml-2 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors">
                            <i class="fas fa-plus"></i>
                            <?= esc(lang('V3Platform.createPlan')) ?>
                        </a>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="cp-surface">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cube text-emerald-600"></i>
                                    <span><?= esc(lang('V3Platform.planName')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-tag text-emerald-600"></i>
                                    <span><?= esc(lang('V3Platform.pricing')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-sliders-h text-emerald-600"></i>
                                    <span><?= esc(lang('V3Platform.limits')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-emerald-600"></i>
                                    <span><?= esc(lang('V3Platform.status')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end space-x-2">
                                    <i class="fas fa-cogs text-emerald-600"></i>
                                    <span><?= esc(lang('V3Platform.actions')) ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="cp-surface divide-y divide-gray-200">
                        <?php if (empty($plans)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-cubes text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="font-medium"><?= esc(lang('V3Platform.noPlansFound')) ?></p>
                                        <p class="text-xs mt-1"><?= esc(lang('V3Platform.createANewPlanToPopulateTheCatalog')) ?></p>
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
                                    $usageCount = (int) ($row['usage_count'] ?? 0);

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
                                            <div class="w-8 h-8 rounded-lg cp-soft-surface flex items-center justify-center text-emerald-600 font-bold text-xs">
                                                <?= esc(ui_badge_text($plan['name'] ?? '', 2, 'PL')) ?>
                                            </div>
                                            <div>
                                                <span class="block text-sm font-medium text-slate-900"><?= esc($plan['name'] ?? 'Plan') ?></span>
                                                <span class="block text-xs text-slate-500 font-mono"><?= esc(lang('V3Platform.id')) ?> <?= esc($plan['id']) ?></span>
                                                <span class="block text-[10px] text-slate-400"><?= esc(lang('V3Platform.linkedSubscriptions')) ?> <?= number_format($usageCount) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900">$<?= number_format((float) $price, 2) ?></div>
                                        <div class="text-xs text-slate-500"><?= esc(lang('V3Platform.per')) ?> <?= strtolower($duration) ?></div>
                                        <?php if ($trial > 0): ?>
                                            <div class="text-[10px] text-emerald-600 font-medium mt-0.5">+<?= $trial ?> days trial</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($limitText as $limit): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border cp-border">
                                                    <?= esc($limit) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" id="status-badge-<?= $plan['id'] ?>">
                                        <?php if ($isActive): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cp-soft-surface text-emerald-800">
                                                <i class="fas fa-check-circle mr-1.5"></i> <?= esc(lang('V3Platform.active')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                <i class="fas fa-pause-circle mr-1.5"></i> <?= esc(lang('V3Platform.inactive')) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Edit -->
                                            <a href="<?= base_url('controlplane/plans/' . $plan['id'] . '/edit') ?>"
                                               class="p-2 cp-accent hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200"
                                               title="<?= esc(lang('V3Platform.editPlan'), 'attr') ?>">
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
                                                    title="<?= esc(lang('V3Platform.deletePlan'), 'attr') ?>">
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
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
    <div class="w-full max-w-lg cp-radius border border-rose-200 cp-surface">
        <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4 bg-rose-50 rounded-t-3xl">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-rose-600 font-bold"><?= esc(lang('V3Platform.irreversibleAction')) ?></p>
                <h3 class="text-lg font-bold text-rose-900"><?= esc(lang('V3Platform.deletePlan')) ?></h3>
            </div>
            <button type="button" class="rounded-full p-2 text-rose-400 hover:text-slate-700 hover:bg-rose-100" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-6 space-y-4 text-sm text-slate-600">
            <p class="font-medium text-slate-800">
                <?= esc(lang('V3Platform.youAreAboutToPermanentlyDelete')) ?> <span id="deletePlanName" class="font-bold text-rose-600"><?= esc(lang('V3Platform.thisPlan')) ?></span>.
            </p>
            <div class="bg-rose-50 p-4 cp-radius text-rose-800 text-xs border border-rose-100">
                <ul class="list-disc list-inside space-y-1">
                    <li><?= esc(lang('V3Platform.thisAction')) ?> <strong><?= esc(lang('V3Platform.cannotBeUndone')) ?></strong>.</li>
                    <li>If subscriptions use this plan, deletion will be blocked (deactivate instead).</li>
                </ul>
            </div>
            <p><?= esc(lang('V3Platform.type')) ?> <span class="font-mono font-bold text-slate-900">DELETE</span> <?= esc(lang('V3Platform.toConfirm')) ?></p>
            <input type="text" id="deleteConfirmInput" class="w-full cp-radius border cp-border px-3 py-2 text-sm focus:border-rose-500 focus:ring-rose-500"
                   placeholder="DELETE" oninput="updateDeleteState()">
        </div>
        <form id="deleteForm" method="post" class="px-6 pb-6 flex items-center justify-end gap-3">
            <?= csrf_field() ?>
            <button type="button" class="px-4 py-2 cp-radius bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition" onclick="closeDeleteModal()">
                <?= esc(lang('V3Platform.cancel')) ?>
            </button>
            <button id="deleteConfirmButton" type="submit" class="px-6 py-2 cp-radius text-white text-sm font-bold cursor-not-allowed transition-all" disabled style="background-color: #e11d48 !important; color: #ffffff !important; opacity: 1 !important;">
                <span><?= esc(lang('V3Platform.permanentlyDelete')) ?></span>
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

function setButtonLoading(btn, label) {
    if (!btn) {
        return;
    }
    if (!btn._originalChildren) {
        btn._originalChildren = Array.from(btn.childNodes).map(node => node.cloneNode(true));
    }
    const icon = document.createElement('i');
    icon.className = 'fas fa-spinner fa-spin mr-2';
    btn.replaceChildren(icon, document.createTextNode(label));
}

function restoreButton(btn) {
    if (!btn || !btn._originalChildren) {
        return;
    }
    btn.replaceChildren(...btn._originalChildren.map(node => node.cloneNode(true)));
}

function buildStatusBadge(isActive) {
    const span = document.createElement('span');
    span.className = isActive
        ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800'
        : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600';

    const icon = document.createElement('i');
    icon.className = isActive ? 'fas fa-check-circle mr-1.5' : 'fas fa-pause-circle mr-1.5';
    span.appendChild(icon);
    span.appendChild(document.createTextNode(isActive ? ' Active' : ' Inactive'));
    return span;
}

function buildToggleIcon(isActive) {
    const icon = document.createElement('i');
    icon.className = isActive ? 'fas fa-pause' : 'fas fa-play';
    return icon;
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
                badgeEl.replaceChildren(buildStatusBadge(isActive));
            }

            if (btnEl) {
                btnEl.title = isActive ? 'Deactivate' : 'Activate';
                if (isActive) {
                    btnEl.className = "p-2 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded-md transition-colors duration-200";
                } else {
                    btnEl.className = "p-2 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-md transition-colors duration-200";
                }
                btnEl.replaceChildren(buildToggleIcon(isActive));
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
            btn.disabled = true;
            setButtonLoading(btn, 'Deleting...');

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
                restoreButton(btn);
                refreshCsrfToken();
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
