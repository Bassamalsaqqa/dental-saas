<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php $disableDataTables = false; ?>

<style>
.dataTables_wrapper {
    position: relative !important;
}

.dataTables_length {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    order: 1 !important;
}

.dataTables_length label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    margin-right: 12px !important;
}

.dataTables_length select {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #cbd5f5 !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #334155 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_length select:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
}

.dataTables_filter {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-left: auto !important;
    order: 2 !important;
}

.dataTables_filter label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    margin-right: 12px !important;
}

.dataTables_filter input {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #cbd5f5 !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #334155 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_filter input:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
}

.dataTables_info {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #64748b !important;
    display: flex !important;
    align-items: center !important;
}

.dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.dataTables_paginate .paginate_button {
    padding: 8px 12px !important;
    margin: 0 2px !important;
    background-color: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    color: #334155 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #eef2ff !important;
    border-color: #c7d2fe !important;
    color: #4338ca !important;
}

.dataTables_paginate .paginate_button.current {
    background-color: #4f46e5 !important;
    border-color: #4f46e5 !important;
    color: white !important;
}

.dataTables_paginate .paginate_button.disabled {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
}

.dataTables_processing {
    background-color: rgba(255, 255, 255, 0.9) !important;
    border: 1px solid #c7d2fe !important;
    border-radius: 12px !important;
    color: #334155 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    padding: 20px !important;
    text-align: center !important;
}

.dataTables_wrapper > .row:first-child,
.dataTables_wrapper > .row:last-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

#clinicsTable {
    background: transparent !important;
}

#clinicsTable tbody tr {
    background: rgba(255, 255, 255, 0.72) !important;
    backdrop-filter: blur(12px);
}

#clinicsTable tbody tr td {
    border-bottom: 1px solid rgba(226, 232, 240, 0.6);
}
</style>

<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Clinics</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Manage clinics and subscriptions</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Provision new clinics, inspect status, and prepare lifecycle actions without leaving the control plane.
                </p>
            </div>
            <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow hover:bg-emerald-700">
                <i class="fas fa-plus"></i>
                Onboard Clinic
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Total Clinics</p>
            <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($stats['total'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Active Clinics</p>
            <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($stats['active'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Plan Coverage</p>
            <p class="text-sm text-slate-600 mt-2">Showing <?= number_format($stats['filtered'] ?? 0) ?> clinics in view.</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-5 flex flex-col lg:flex-row lg:items-end gap-4">
        <div class="flex-1">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Search</label>
            <input type="text" id="clinicSearch" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Clinic name or ID"
                   class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="w-full lg:w-56">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Status</label>
            <select id="clinicStatus" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <?php $currentStatus = $filters['status'] ?? ''; ?>
                <option value="" <?= $currentStatus === '' ? 'selected' : '' ?>>All statuses</option>
                <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= $currentStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </div>
        <div class="w-full lg:w-64">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Plan</label>
            <select id="clinicPlan" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <?php $currentPlan = (int) ($filters['plan_id'] ?? 0); ?>
                <option value="0" <?= $currentPlan === 0 ? 'selected' : '' ?>>All plans</option>
                <?php foreach (($plans ?? []) as $plan): ?>
                    <option value="<?= esc($plan['id']) ?>" <?= $currentPlan === (int) $plan['id'] ? 'selected' : '' ?>>
                        <?= esc($plan['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button id="applyClinicFilters" type="button" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">
                Apply Filters
            </button>
            <button id="resetClinicFilters" type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">
                Reset
            </button>
        </div>
    </div>

    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/40 bg-white/60">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                    <h3 class="text-lg font-semibold text-slate-900">Clinic Registry</h3>
                </div>
                <div class="text-xs text-slate-400">Powered by DataTables</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="clinicsTable" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">MRR</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100"></tbody>
            </table>
        </div>
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
        <form id="terminateForm" method="post" class="px-6 pb-6 flex items-center justify-between gap-3">
            <?= csrf_field() ?>
            <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold" onclick="closeTerminateModal()">
                Cancel
            </button>
            <button id="terminateConfirmButton" type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold opacity-60 cursor-not-allowed" disabled>
                Terminate Clinic
            </button>
        </form>
    </div>
</div>

<script>
let clinicsTable;
const baseUrl = '<?= base_url() ?>';
const csrfMetaName = document.querySelector('meta[name="csrf-name"]');
const csrfMetaToken = document.querySelector('meta[name="csrf-token"]');
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
    buttonEl.classList.toggle('opacity-60', !matches);
    buttonEl.classList.toggle('cursor-not-allowed', !matches);
}

$(document).ready(function() {
    clinicsTable = $('#clinicsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: false,
        ajax: {
            url: '<?= base_url('controlplane/clinics') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                const csrfToken = getCsrfToken();
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
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
                    const name = escapeHtml(data || 'Clinic');
                    const slug = escapeHtml(row.slug || 'no-slug');
                    const id = escapeHtml(row.id || '');
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hospital-user text-indigo-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-slate-900">${name}</div>
                                <div class="text-xs text-slate-500">ID ${id} · ${slug}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    const status = (data || 'inactive').toLowerCase();
                    const colorMap = {
                        active: 'bg-emerald-100 text-emerald-700',
                        suspended: 'bg-amber-100 text-amber-700',
                        inactive: 'bg-slate-100 text-slate-600'
                    };
                    const label = status.charAt(0).toUpperCase() + status.slice(1);
                    return `<span class="text-xs font-semibold px-2 py-1 rounded-full ${colorMap[status] || colorMap.inactive}">${label}</span>`;
                }
            },
            {
                data: 'plan_name',
                name: 'plan_name',
                render: function(data) {
                    return `<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">${escapeHtml(data || 'Unassigned')}</span>`;
                }
            },
            {
                data: 'mrr',
                name: 'mrr',
                render: function(data) {
                    return `<span class="text-sm font-semibold text-slate-800">${formatCurrency(data)}</span>`;
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
                    let actions = '<div class="flex flex-wrap items-center gap-2">';

                    actions += `
                        <a href="${baseUrl}controlplane/clinics/${id}"
                           class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">View Details</a>`;

                    if (status === 'active') {
                        actions += `
                            <form action="${baseUrl}controlplane/clinics/${id}/suspend" method="post">
                                ${renderCsrfField()}
                                <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-700">Suspend</button>
                            </form>`;
                    } else {
                        actions += `
                            <form action="${baseUrl}controlplane/clinics/${id}/reactivate" method="post">
                                ${renderCsrfField()}
                                <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700">Reactivate</button>
                            </form>`;
                    }

                    actions += `
                        <form action="${baseUrl}controlplane/impersonate" method="post">
                            ${renderCsrfField()}
                            <input type="hidden" name="clinic_id" value="${id}">
                            <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700">Login As</button>
                        </form>
                        <button type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700"
                                onclick="openTerminateModal('${id}', '${encodedName}')">Terminate</button>`;

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        dom: 'B<"px-6 py-4 border-b border-slate-200 bg-slate-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>>rt<"px-6 py-4 border-t border-slate-200 bg-slate-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'asc']],
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                className: 'btn-pdf',
                title: 'Clinics Report',
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i>Print',
                className: 'btn-print',
                title: 'Clinics Report'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i>CSV',
                className: 'btn-csv',
                title: 'Clinics Report'
            }
        ],
        language: {
            processing: 'Loading clinics...',
            lengthMenu: '_MENU_',
            zeroRecords: 'No clinics found',
            info: 'Showing _START_ to _END_ of _TOTAL_ clinics',
            infoEmpty: 'No clinics available',
            infoFiltered: '(filtered from _MAX_ total clinics)',
            search: '',
            searchPlaceholder: 'Search clinics...',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        drawCallback: function() {
            refreshCsrfToken();
        }
    });

    $('#applyClinicFilters').on('click', function() {
        clinicsTable.draw();
    });

    $('#resetClinicFilters').on('click', function() {
        $('#clinicSearch').val('');
        $('#clinicStatus').val('');
        $('#clinicPlan').val('0');
        clinicsTable.draw();
    });

    const modal = document.getElementById('terminateModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeTerminateModal();
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
