<?= $this->extend('layouts/main_control_plane') ?><?= $this->section('content') ?><header class="v4-page-heading"><div><h1><?= esc(lang('V4.planCatalog')) ?></h1><p><?= esc(lang('V4.planCatalogHelp')) ?></p></div><a class="v4-button v4-button--primary" href="<?= base_url('controlplane/plans/create') ?>"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('V4.createPlan')) ?></a></header><form method="get" class="v4-form v4-platform-filter"><input type="search" name="q" value="<?= esc($filters['q']??'','attr') ?>" placeholder="<?= esc(lang('V4.searchPlans'),'attr') ?>" aria-label="<?= esc(lang('V4.searchPlans'),'attr') ?>"><select name="status" aria-label="<?= esc(lang('V4.tenantStatus')) ?>" onchange="this.form.submit()" data-no-select2><?php foreach([''=>'V4.allStatuses','active'=>'V4.active','inactive'=>'V4.inactive'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($filters['status']??'')===$value?'selected':'' ?>><?= esc(lang($label)) ?></option><?php endforeach; ?></select><select name="duration" aria-label="<?= esc(lang('V4.duration')) ?>" onchange="this.form.submit()" data-no-select2><?php foreach([''=>'allDurations','monthly'=>'monthly','yearly'=>'yearly','lifetime'=>'lifetime'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($filters['duration']??'')===$value?'selected':'' ?>><?= esc(lang('V4.'.$label)) ?></option><?php endforeach; ?></select><button class="v4-button" type="submit"><?= esc(lang('V4.filter')) ?></button><a class="v4-button" href="<?= base_url('controlplane/plans') ?>"><?= esc(lang('V4.clearFilters')) ?></a></form><details class="v4-clinical-summary"><summary><?= esc(lang('V4.recordSummary')) ?></summary><dl><?php foreach(['total'=>'V4.totalPlans','active'=>'V4.activePlans','filtered'=>'V4.filteredPlans'] as $key=>$label): ?><div><dt><?= esc(lang($label)) ?></dt><dd><?= number_format($stats[$key]??0) ?></dd></div><?php endforeach; ?></dl></details><div class="v4-plan-catalog"><?php if(empty($plans)): ?><div class="v4-platform-empty"><?= esc(lang('V4.noPlans')) ?></div><?php endif; ?><?php foreach($plans as $row): ?><?php $plan=$row['plan']??[];$limits=$row['limits']??[];$price=$limits['price']??0;$duration=strtolower($limits['duration']??'monthly');$trial=(int)($limits['trial_days']??0);$isActive=($plan['status']??'inactive')==='active';$usageCount=(int)($row['usage_count']??0);$durationLabel=lang('V4.'.$duration);if($durationLabel==='V4.'.$duration)$durationLabel=ucfirst($duration); ?><article id="plan-row-<?= (int)$plan['id'] ?>" class="v4-plan-card"><header><div><h2><?= esc($plan['name']??'') ?></h2><p><?= esc($plan['slug']??'') ?></p></div><div id="status-badge-<?= (int)$plan['id'] ?>"><span class="v4-badge"><?= esc(lang($isActive?'V4.active':'V4.inactive')) ?></span></div></header><div class="v4-plan-price"><strong>$<?= number_format((float)$price,2) ?></strong><span><?= esc($durationLabel) ?></span></div><dl><?php foreach(['patients_active_max'=>'V4.patientCapacity','users_max'=>'V4.userCapacity','storage_gb'=>'V4.storageCapacity'] as $key=>$label): ?><?php $value=$limits[$key]??-1; ?><div><dt><?= esc(lang($label)) ?></dt><dd><?= $value===-1?esc(lang('V4.unlimited')):esc((string)$value) ?><?= $key==='storage_gb'&&$value!==-1?' GB':'' ?></dd></div><?php endforeach; ?><div><dt><?= esc(lang('V4.clinicsUsingPlan')) ?></dt><dd><?= $usageCount ?></dd></div><?php if($trial>0): ?><div><dt><?= esc(lang('V4.trialDays')) ?></dt><dd><?= $trial ?></dd></div><?php endif; ?></dl><footer>                                            <!-- Edit -->
                                            <a href="<?= base_url('controlplane/plans/' . $plan['id'] . '/edit') ?>"
                                               class="v4-icon-button"
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
                                                    onclick="openDeleteModal(<?= (int)$plan['id'] ?>, <?= esc(json_encode((string)$plan['name'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT),'attr') ?>)"
                                                    class="v4-icon-button v4-critical"
                                                    title="Delete Plan">
                                                <i class="fas fa-trash"></i>
                                            </button>
</footer></article><?php endforeach; ?></div><div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
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
        ? 'v4-badge v4-badge--success'
        : 'v4-badge';

    const icon = document.createElement('i');
    icon.className = isActive ? 'fas fa-check-circle mr-1.5' : 'fas fa-pause-circle mr-1.5';
    span.appendChild(icon);
    span.appendChild(document.createTextNode(isActive ? '<?= esc(lang('V4.active'),'js') ?>' : '<?= esc(lang('V4.inactive'),'js') ?>'));
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
