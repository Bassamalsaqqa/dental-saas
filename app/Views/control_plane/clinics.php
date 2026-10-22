<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php $disableDataTables = false; ?>

<style>
/* Keep existing styles */
.dataTables_wrapper { position: relative !important; }
/* ... (Keep existing DataTables overrides) ... */
#clinicsTable tbody tr td { border-bottom: 1px solid rgba(226, 232, 240, 0.6); }
/* Reset DataTables Button Styles */
button.dt-button {
    background-image: none !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    text-shadow: none !important;
    margin-inline-end: 0.5rem !important;
}
button.dt-button:hover, button.dt-button:active {
    background-image: none !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
}
/* Manual Tailwind Fallbacks */
.bg-rose-50 { background-color: #fff1f2 !important; }
.text-rose-600 { color: #e11d48 !important; }
.bg-slate-100 { background-color: #f1f5f9 !important; }
.text-slate-600 { color: #475569 !important; }
.bg-emerald-50 { background-color: #ecfdf5 !important; }
.text-emerald-600 { color: #059669 !important; }
/* Hide default buttons */
.dt-buttons { display: none !important; }
body.cp-clinics-page {
    background: #ffffff !important;
}
body.cp-clinics-page main {
    background: #ffffff !important;
}
body.cp-clinics-page main > .pointer-events-none.absolute.inset-0 {
    display: none !important;
}
body.cp-clinics-page .cp-content {
    background: #ffffff !important;
}
.cp-clinics {
    background: #ffffff;
}
.cp-clinics .cp-clinics-title {
    color: rgba(255, 255, 255, 0.96) !important;
}
#clinicsTable.dataTable {
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0;
}
#clinicsTable.dataTable thead th {
    padding: 0.85rem 1rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
#clinicsTable.dataTable tbody td {
    padding: 0.7rem 1rem !important;
    vertical-align: middle;
}
#clinicsTable.dataTable tbody tr:hover {
    background: #f8fafc;
}
#clinicsTable_wrapper .dataTables_info {
    padding: 0.85rem 1rem 1rem !important;
    color: #64748b !important;
    font-weight: 600;
}
#clinicsTable_wrapper .dataTables_paginate {
    padding: 0.65rem 1rem 1rem !important;
}
#clinicsTable_wrapper .dataTables_paginate .paginate_button {
    border-radius: 0.75rem !important;
    border: 1px solid #e2e8f0 !important;
    margin-inline-start: 0.25rem !important;
    padding: 0.4rem 0.75rem !important;
    font-weight: 700;
}
</style>
<script>
document.body.classList.add('cp-clinics-page');
</script>

<div class="cp-clinics">
    <div class="w-full space-y-3 px-2 py-3 sm:px-4 lg:px-5">
        <section class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
            <div class="bg-gradient-to-r from-slate-950 via-emerald-800 to-teal-700 px-5 py-4 text-white sm:px-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg ring-1 ring-white/20">
                            <i class="fas fa-hospital-user text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="cp-clinics-title text-2xl font-black tracking-tight sm:text-3xl">Clinic Registry</h1>
                                <span class="rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-black uppercase tracking-wide text-emerald-50 ring-1 ring-emerald-200/25">Global Mode</span>
                            </div>
                            <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-emerald-50/90">Manage tenant clinics, lifecycle status, plan assignment, admin access, and operator actions.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 sm:min-w-[24rem]">
                        <div class="rounded-2xl bg-white/15 px-3 py-2 ring-1 ring-white/20">
                            <p class="text-[11px] font-bold text-emerald-100">Total</p>
                            <p class="text-2xl font-black text-white"><?= number_format($stats['total'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-white/15 px-3 py-2 ring-1 ring-white/20">
                            <p class="text-[11px] font-bold text-emerald-100">Active</p>
                            <p class="text-2xl font-black text-white"><?= number_format($stats['active'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-white/15 px-3 py-2 ring-1 ring-white/20">
                            <p class="text-[11px] font-bold text-emerald-100">View</p>
                            <p class="text-2xl font-black text-white"><?= number_format($stats['filtered'] ?? 0) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-white px-4 text-sm font-black text-emerald-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-emerald-50">
                            <i class="fas fa-plus"></i>
                            <span>Onboard Clinic</span>
                        </a>
                        <a href="<?= base_url('controlplane/plans') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-white/15 px-4 text-sm font-black text-white ring-1 ring-white/20 transition hover:bg-white/20">
                            <i class="fas fa-layer-group"></i>
                            <span>Plans</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-b border-slate-200 bg-slate-50/80 p-3 sm:p-4">
                <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(18rem,1fr)_12rem_14rem_auto]">
                        <div class="relative">
                            <i class="fas fa-search pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400 ltr:left-3 rtl:right-3"></i>
                            <input id="clinicSearch" type="search" value="<?= esc($filters['q'] ?? '', 'attr') ?>" placeholder="Search clinics or admin email" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 ltr:pl-10 rtl:pr-10">
                        </div>
                        <select id="clinicStatus" class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 shadow-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                            <option value="">All statuses</option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <select id="clinicPlan" class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 shadow-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                            <option value="0">All plans</option>
                            <?php foreach (($plans ?? []) as $plan): ?>
                                <option value="<?= (int) ($plan['id'] ?? 0) ?>" <?= (int) ($filters['plan_id'] ?? 0) === (int) ($plan['id'] ?? 0) ? 'selected' : '' ?>><?= esc($plan['name'] ?? 'Plan') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="flex gap-2">
                            <button id="resetClinicFilters" type="button" class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-100 px-4 text-sm font-black text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-200">
                                <i class="fas fa-times me-2"></i>Clear
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-start gap-2 xl:justify-end">
                        <button onclick="exportToPDF()" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-white px-4 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-rose-50 hover:text-rose-700">
                            <i class="fas fa-file-pdf text-rose-600"></i>PDF
                        </button>
                        <button onclick="printTable()" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-white px-4 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                            <i class="fas fa-print text-slate-600"></i>Print
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-white px-4 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fas fa-file-csv text-emerald-600"></i>CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="clinicsTable" class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-start text-xs font-black uppercase tracking-wide text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-hospital text-emerald-600"></i>
                                    <span>Clinic Name</span>
                                </div>
                            </th>
                            <th class="text-start text-xs font-black uppercase tracking-wide text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-emerald-600"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="text-start text-xs font-black uppercase tracking-wide text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-layer-group text-emerald-600"></i>
                                    <span>Plan</span>
                                </div>
                            </th>
                            <th class="text-start text-xs font-black uppercase tracking-wide text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-shield text-emerald-600"></i>
                                    <span>Admin Email</span>
                                </div>
                            </th>
                            <th class="text-start text-xs font-black uppercase tracking-wide text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-dollar-sign text-emerald-600"></i>
                                    <span>MRR</span>
                                </div>
                            </th>
                            <th class="text-end text-xs font-black uppercase tracking-wide text-slate-600">
                                <div class="flex items-center justify-end gap-2">
                                    <i class="fas fa-cogs text-emerald-600"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="terminateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4">
    <div class="w-full max-w-lg rounded-3xl border border-rose-100 bg-white/95 shadow-2xl backdrop-blur-xl">
        <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-rose-400 font-semibold">Danger Zone</p>
                <h3 class="text-lg font-semibold text-slate-900">Terminate clinic</h3>
            </div>
            <button type="button" class="rounded-full p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100" onclick="closeTerminateModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-4 space-y-4 text-sm text-slate-600">
            <p>
                This will immediately disable access and cancel subscriptions for
                <span id="terminateClinicName" class="font-semibold text-slate-900">this clinic</span>.
                Type the clinic name to confirm.
            </p>
            <input type="text" id="terminateConfirmInput" class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm"
                   placeholder="Type clinic name to confirm" oninput="updateTerminateState()">
        </div>
        <form id="terminateForm" method="post" class="px-6 pb-6 flex items-center justify-end gap-3">
            <?= csrf_field() ?>
            <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition" onclick="closeTerminateModal()">
                Cancel
            </button>
            <button id="terminateConfirmButton" type="submit" class="px-6 py-2 rounded-xl text-white text-sm font-bold cursor-not-allowed shadow-lg transition-all" disabled style="background-color: #e11d48 !important; color: #ffffff !important; opacity: 1 !important;">
                <span>Confirm Termination</span>
            </button>
        </form>
    </div>
</div>

<p class="mb-4 text-sm text-slate-600" role="note"><?= esc(lang('IonAuth.clinic_delete_history_protected')) ?></p>

<script>
let clinicsTable;
const baseUrl = '<?= base_url() ?>';
const csrfMetaName = document.querySelector('meta[name="csrf-name"]');
const csrfMetaToken = document.querySelector('meta[name="csrf-token"]');
const csrfMetaHeader = document.querySelector('meta[name="csrf-header"]');
const csrfCookieName = '<?= esc(config('Security')->cookieName) ?>';
let terminateClinicName = '';

function getCookieValue(name) {
    if (!name) {
        return '';
    }

    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
        const [key, ...valueParts] = cookie.trim().split('=');
        if (key === name) {
            return decodeURIComponent(valueParts.join('='));
        }
    }

    return '';
}

function getCsrfName() {
    return csrfMetaName ? csrfMetaName.getAttribute('content') : '<?= csrf_token() ?>';
}

function getCsrfToken() {
    return csrfMetaToken ? csrfMetaToken.getAttribute('content') : '';
}

function getCsrfHeaderName() {
    return csrfMetaHeader ? csrfMetaHeader.getAttribute('content') : 'X-CSRF-TOKEN';
}

function refreshCsrfToken() {
    const cookieToken = getCookieValue(csrfCookieName);
    if (cookieToken && csrfMetaToken) {
        csrfMetaToken.setAttribute('content', cookieToken);
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) {
        return '';
    }
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getAvatarInitials(name) {
    if (!name) {
        return 'CL';
    }
    const words = String(name).trim().split(/\s+/).filter(Boolean);
    const getFirstChar = (value) => Array.from(String(value || '').trim())[0] || '';
    const getFirstChars = (value, count = 2) => Array.from(String(value || '').trim()).slice(0, count).join('');

    if (words.length === 1) {
        return getFirstChars(words[0], 2).toLocaleUpperCase() || 'CL';
    }
    return (getFirstChar(words[0]) + getFirstChar(words[1])).toLocaleUpperCase() || 'CL';
}

function getAvatarColor(seed) {
    const palette = ['#c7d2fe', '#fbcfe8', '#bbf7d0', '#bfdbfe', '#fde68a', '#fed7aa'];
    let hash = 0;
    const value = seed || 'CL';
    for (let i = 0; i < value.length; i++) {
        hash = (hash * 31 + value.charCodeAt(i)) % palette.length;
    }
    return palette[Math.abs(hash)];
}

function renderCsrfField() {
    const name = getCsrfName();
    const token = getCsrfToken();
    if (!name || !token) {
        return '';
    }
    return `<input type="hidden" name="${name}" value="${token}">`;
}

function formatCurrency(value) {
    const numericValue = Number(value) || 0;
    if (window.Intl && Intl.NumberFormat) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(numericValue);
    }
    return `$${numericValue.toFixed(2)}`;
}

function openTerminateModal(clinicId, clinicNameEncoded) {
    const modal = document.getElementById('terminateModal');
    const nameEl = document.getElementById('terminateClinicName');
    const inputEl = document.getElementById('terminateConfirmInput');
    const formEl = document.getElementById('terminateForm');
    const decodedName = decodeURIComponent(clinicNameEncoded || 'Clinic');

    terminateClinicName = decodedName;
    if (nameEl) {
        nameEl.textContent = decodedName;
    }
    if (formEl) {
        formEl.action = `${baseUrl}controlplane/clinics/${clinicId}/terminate`;
    }
    if (inputEl) {
        inputEl.value = '';
    }
    updateTerminateState();
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeTerminateModal() {
    const modal = document.getElementById('terminateModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function updateTerminateState() {
    const inputEl = document.getElementById('terminateConfirmInput');
    const buttonEl = document.getElementById('terminateConfirmButton');
    if (!inputEl || !buttonEl) {
        return;
    }
    const matches = inputEl.value.trim().toLowerCase() === terminateClinicName.trim().toLowerCase();
    buttonEl.disabled = !matches;
    buttonEl.classList.toggle('opacity-50', !matches);
    buttonEl.classList.toggle('cursor-not-allowed', !matches);
}

$(document).ready(function() {
    clinicsTable = $('#clinicsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: {
            details: false
        },
        searching: false,
        ajax: {
            url: '<?= base_url('controlplane/clinics') ?>',
            type: 'POST',
            dataType: 'json',
            beforeSend: function(xhr) {
                const csrfToken = getCsrfToken();
                const headerName = getCsrfHeaderName();
                if (csrfToken) {
                    xhr.setRequestHeader(headerName, csrfToken);
                }
            },
            data: function(d) {
                d.status = $('#clinicStatus').val();
                d.plan_id = $('#clinicPlan').val();
                d.q = $('#clinicSearch').val();
                const csrfName = getCsrfName();
                const csrfToken = getCsrfToken();
                if (csrfName && csrfToken) {
                    d[csrfName] = csrfToken;
                }
                return d;
            },
            complete: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token && csrfMetaToken) {
                    csrfMetaToken.setAttribute('content', xhr.responseJSON.csrf_token);
                }
            },
            error: function(xhr, error, thrown) {
                console.error('Clinics DataTables error:', error, thrown);
                console.error('Clinics DataTables response:', xhr.responseText);
                if (xhr.responseJSON && xhr.responseJSON.csrf_token && csrfMetaToken) {
                    csrfMetaToken.setAttribute('content', xhr.responseJSON.csrf_token);
                }
                alert('Error loading clinic data. Please refresh and try again.');
            }
        },
        columns: [
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    const nameRaw = data || 'Clinic';
                    const name = escapeHtml(nameRaw);
                    const slug = escapeHtml(row.slug || 'no-slug');
                    const id = escapeHtml(row.id || '');
                    const initials = escapeHtml(getAvatarInitials(nameRaw));
                    const avatarColor = getAvatarColor(initials || (Array.from(nameRaw)[0] || 'C'));
                    const detailUrl = `${baseUrl}controlplane/clinics/${id}`;
                    return `
                        <a href="${detailUrl}" class="block rounded-2xl px-2 py-1.5 transition-colors duration-200 hover:bg-emerald-50/70">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-black text-slate-900"
                                     style="background: ${avatarColor}; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-black text-slate-950">${name}</div>
                                    <div class="truncate text-xs font-bold text-slate-500">ID ${id} - ${slug}</div>
                                </div>
                            </div>
                        </a>
                    `;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const status = (data || 'inactive').toLowerCase();
                    const statusColors = {
                        'active': 'bg-green-100 text-green-800',
                        'suspended': 'bg-yellow-100 text-yellow-800',
                        'inactive': 'bg-gray-100 text-gray-800'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'suspended': 'fas fa-pause-circle',
                        'inactive': 'fas fa-times-circle'
                    };
                    
                    const colorClass = statusColors[status] || 'bg-gray-100 text-gray-800';
                    const iconClass = statusIcons[status] || 'fas fa-question-circle';
                    const label = status.charAt(0).toUpperCase() + status.slice(1);

                    return `
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-black ${colorClass}">
                            <i class="${iconClass} me-1"></i>
                            ${label}
                        </span>
                    `;
                }
            },
            {
                data: 'plan_name',
                name: 'plan_name',
                render: function(data) {
                    return `<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-black text-indigo-700 ring-1 ring-indigo-100">${escapeHtml(data || 'Unassigned')}</span>`;
                }
            },
            {
                data: 'admin_email',
                name: 'admin_email',
                render: function(data, type, row) {
                    const email = escapeHtml(data || 'None');
                    if (email === 'None' || email === 'Unknown' || row.admin_state === 'unassigned') {
                        return `<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">${escapeHtml(row.admin_state_label || 'Unassigned')}</span>`;
                    }
                    if (row.admin_is_fallback) {
                        return `
                            <div>
                                <div class="text-sm font-semibold text-slate-700">${email}</div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 mt-0.5">
                                    <i class="fas fa-clock text-[9px] me-1"></i><?= esc(lang('UI.legacyAdminCandidate'), 'js') ?>
                                </span>
                            </div>
                        `;
                    }
                    if (row.admin_is_healthy) {
                        return `<span class="text-sm font-semibold text-slate-700">${email}</span>`;
                    }
                    let badgeClass = 'bg-amber-100 text-amber-800';
                    if (row.admin_state === 'disabled_user') {
                        badgeClass = 'bg-rose-100 text-rose-800';
                    } else if (row.admin_state === 'inactive_membership') {
                        badgeClass = 'bg-orange-100 text-orange-800';
                    }
                    return `
                        <div>
                            <div class="text-sm font-semibold text-slate-700">${email}</div>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold ${badgeClass} mt-0.5">
                                ${escapeHtml(row.admin_state_label || 'Degraded')}
                            </span>
                        </div>
                    `;
                }
            },
            {
                data: 'mrr',
                name: 'mrr',
                render: function(data) {
                    return `<span class="text-sm font-black text-slate-900">${formatCurrency(data)}</span>`;
                }
            },
            {
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const id = escapeHtml(data || '');
                    const status = (row.status || 'inactive').toLowerCase();
                    const encodedName = encodeURIComponent(row.name || 'Clinic');
                    let actions = '<div class="flex items-center justify-end gap-2">';

                    // View Details
                    actions += `
                        <a href="${baseUrl}controlplane/clinics/${id}"
                           class="rounded-xl p-2 text-emerald-600 transition-colors duration-200 hover:bg-emerald-50 hover:text-emerald-700"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>`;

                    // Suspend / Reactivate
                    if (status === 'active') {
                        actions += `
                            <button onclick="toggleClinicStatus('${id}', 'suspend')" class="rounded-xl p-2 text-amber-600 transition-colors duration-200 hover:bg-amber-50 hover:text-amber-700" title="Suspend">
                                <i class="fas fa-pause"></i>
                            </button>`;
                    } else {
                        actions += `
                            <button onclick="toggleClinicStatus('${id}', 'reactivate')" class="rounded-xl p-2 text-green-600 transition-colors duration-200 hover:bg-green-50 hover:text-green-700" title="Reactivate">
                                <i class="fas fa-play"></i>
                            </button>`;
                    }

                    // Login As (Impersonate)
                    actions += `
                        <form action="${baseUrl}controlplane/impersonate" method="post" class="inline">
                            ${renderCsrfField()}
                            <input type="hidden" name="clinic_id" value="${id}">
                            <button type="submit" class="rounded-xl p-2 text-indigo-600 transition-colors duration-200 hover:bg-indigo-50 hover:text-indigo-700" title="Login As">
                                <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </form>`;

                    // Terminate
                    actions += `
                        <button type="button" class="rounded-xl p-2 text-rose-600 transition-colors duration-200 hover:bg-rose-50 hover:text-rose-700"
                                onclick="openTerminateModal('${id}', '${encodedName}')" title="Terminate">
                            <i class="fas fa-ban"></i>
                        </button>`;

                    actions += `<button type="button" disabled class="p-2 text-slate-400 cursor-not-allowed" title="<?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?>"><i class="fas fa-lock" aria-hidden="true"></i><span class="sr-only"><?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?></span></button>`;
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        columnDefs: [
            { targets: 0, responsivePriority: 3 },
            { targets: 2, responsivePriority: 2 },
            { targets: 5, responsivePriority: 1, className: 'dt-actions' }
        ],
        // Layout: Length+Filters (Top Left), Empty (Top Right), Table, Info+Paginate (Bottom)
        dom: 'Bfrtip',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'asc']],
        buttons: [
            {
                extend: 'pdf',
                className: 'btn-pdf',
                title: 'Clinics Report',
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                className: 'btn-print',
                title: 'Clinics Report'
            },
            {
                extend: 'csv',
                className: 'btn-csv',
                title: 'Clinics Report'
            }
        ],
        autoWidth: false,
        language: {
            processing: 'Loading clinics...',
            lengthMenu: '_MENU_',
            zeroRecords: `<div class="py-12 text-center text-slate-400 space-y-3"><p class="text-sm font-semibold">No clinics found</p></div>`,
            info: 'Showing _START_ to _END_ of _TOTAL_ clinics',
            infoEmpty: 'No clinics available',
            infoFiltered: '(filtered from _MAX_ total clinics)',
            search: '',
            searchPlaceholder: 'Search clinics...',
            paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
        },
        drawCallback: function() { refreshCsrfToken(); },
    });

    let clinicFilterTimer;
    $('#clinicSearch').on('input', function() {
        window.clearTimeout(clinicFilterTimer);
        clinicFilterTimer = window.setTimeout(function() {
            clinicsTable.draw();
        }, 250);
    });

    $('#clinicStatus, #clinicPlan').on('change', function() {
        clinicsTable.draw();
    });

    $('#resetClinicFilters').on('click', function() {
        $('#clinicSearch').val('');
        $('#clinicStatus').val('');
        $('#clinicPlan').val('0');
        clinicsTable.draw();
    });

    const termModal = document.getElementById('terminateModal');
    if (termModal) termModal.addEventListener('click', function(event) { if (event.target === termModal) closeTerminateModal(); });
});

// Export Functions
function exportToPDF() {
    if (clinicsTable) clinicsTable.button('.btn-pdf').trigger();
}
function printTable() {
    if (clinicsTable) clinicsTable.button('.btn-print').trigger();
}
function exportToCSV() {
    if (clinicsTable) clinicsTable.button('.btn-csv').trigger();
}

// AJAX Status Toggle
function toggleClinicStatus(clinicId, action) {
    // No confirmation as requested
    const formData = new FormData();
    const csrfName = getCsrfName();
    const csrfToken = getCsrfToken();
    if (csrfName && csrfToken) {
        formData.append(csrfName, csrfToken);
    }

    fetch(`${baseUrl}controlplane/clinics/${clinicId}/${action}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (clinicsTable) clinicsTable.ajax.reload(null, false);
            // Optional: Success toast
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

// AJAX Form Submissions for Modals
document.addEventListener('DOMContentLoaded', function() {
    function setButtonLoading(btn, label) {
        if (!btn) {
            return;
        }
        if (!btn._originalChildren) {
            btn._originalChildren = Array.from(btn.childNodes).map(node => node.cloneNode(true));
        }
        const icon = document.createElement('i');
        icon.className = 'fas fa-spinner fa-spin me-2';
        btn.replaceChildren(icon, document.createTextNode(label));
    }

    function restoreButton(btn) {
        if (!btn || !btn._originalChildren) {
            return;
        }
        btn.replaceChildren(...btn._originalChildren.map(node => node.cloneNode(true)));
    }

    // Terminate AJAX
    const terminateForm = document.getElementById('terminateForm');
    if (terminateForm) {
        terminateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('terminateConfirmButton');
            btn.disabled = true;
            setButtonLoading(btn, 'Terminating...');

            const actionUrl = terminateForm.action;
            const formData = new FormData(terminateForm);
            
            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeTerminateModal();
                    if (clinicsTable) clinicsTable.ajax.reload(null, false);
                } else {
                    alert(data.message || 'Failed to terminate clinic');
                }
            })
            .catch(error => { console.error('Error:', error); alert('Error occurred'); })
            .finally(() => {
                btn.disabled = false;
                restoreButton(btn);
                refreshCsrfToken();
            });
        });
    }


});
</script>



<script>

// Terminate Modal Functions
function openTerminateModal(clinicId, clinicNameEncoded) {
    const modal = document.getElementById('terminateModal');
    const nameEl = document.getElementById('terminateClinicName');
    const inputEl = document.getElementById('terminateConfirmInput');
    const formEl = document.getElementById('terminateForm');
    const decodedName = decodeURIComponent(clinicNameEncoded || 'Clinic');

    terminateClinicName = decodedName;
    if (nameEl) nameEl.textContent = decodedName;
    if (formEl) formEl.action = `${baseUrl}controlplane/clinics/${clinicId}/terminate`;
    if (inputEl) inputEl.value = '';
    
    updateTerminateState();
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeTerminateModal() {
    const modal = document.getElementById('terminateModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function updateTerminateState() {
    const inputEl = document.getElementById('terminateConfirmInput');
    const buttonEl = document.getElementById('terminateConfirmButton');
    if (!inputEl || !buttonEl) return;
    
    const matches = inputEl.value.trim().toLowerCase() === terminateClinicName.trim().toLowerCase();
    buttonEl.disabled = !matches;
    if (matches) {
        buttonEl.classList.remove('opacity-50', 'cursor-not-allowed');
        buttonEl.classList.add('hover:bg-rose-700');
    } else {
        buttonEl.classList.add('opacity-50', 'cursor-not-allowed');
        buttonEl.classList.remove('hover:bg-rose-700');
    }
}







</script>

<?= $this->endSection() ?>
