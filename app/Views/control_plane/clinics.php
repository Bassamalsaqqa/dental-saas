<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php $disableDataTables = false; ?>

<div class="cp-clinics">
    <!-- Command Header / Platform Stamp -->
    <div class="cp-command-bar">
        <div class="cp-command-bar-info">
            <div class="cp-command-bar-icon">
                <i class="fas fa-hospital-user"></i>
            </div>
            <div>
                <h1 class="cp-command-bar-title">
                    <span><?= esc(lang('V3Platform.tenantClinicRegistry')) ?></span>
                    <span class="cp-badge cp-badge-teal">
                        <i class="fas fa-globe text-[10px]"></i> <?= esc(lang('V3Platform.globalMode')) ?>
                    </span>
                </h1>
                <p class="cp-command-bar-sub"><?= esc(lang('V3Platform.manageTenantClinicsLifecycleStatusPlanAssignmentsAdministratorAccessAndOperatorActions')) ?></p>
            </div>
        </div>
        <div class="cp-command-bar-actions">
            <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="cp-btn cp-btn-primary">
                <i class="fas fa-plus"></i>
                <span><?= esc(lang('V3Platform.onboardClinic')) ?></span>
            </a>
            <a href="<?= base_url('controlplane/plans') ?>" class="cp-btn cp-btn-light">
                <i class="fas fa-layer-group"></i>
                <span><?= esc(lang('V3Platform.managePlans')) ?></span>
            </a>
        </div>
    </div>

    <!-- Quick Registry KPI Strip -->
    <div class="cp-kpi-grid cols-3 mb-3.5">
        <div class="cp-kpi mint">
            <div class="cp-kpi-label">
                <i class="fas fa-hospital"></i>
                <span><?= esc(lang('V3Platform.totalClinics')) ?></span>
            </div>
            <div class="cp-kpi-value"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="cp-kpi-foot"><?= esc(lang('V3Platform.allRegisteredTenantClinics')) ?></div>
        </div>
        <div class="cp-kpi emerald">
            <div class="cp-kpi-label">
                <i class="fas fa-check-circle"></i>
                <span><?= esc(lang('V3Platform.activeClinics')) ?></span>
            </div>
            <div class="cp-kpi-value"><?= number_format($stats['active'] ?? 0) ?></div>
            <div class="cp-kpi-foot"><?= esc(lang('V3Platform.currentlyOperatingNormally')) ?></div>
        </div>
        <div class="cp-kpi softteal">
            <div class="cp-kpi-label">
                <i class="fas fa-filter"></i>
                <span><?= esc(lang('V3Platform.filteredResults')) ?></span>
            </div>
            <div class="cp-kpi-value"><?= number_format($stats['filtered'] ?? 0) ?></div>
            <div class="cp-kpi-foot"><?= esc(lang('V3Platform.matchingCurrentViewCriteria')) ?></div>
        </div>
    </div>

    <!-- Tenant Zebra Registry Table -->
    <div class="cp-table-wrap">
        <!-- Table Filter Tools Bar -->
        <div class="cp-table-tools">
            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                <div class="relative min-w-[220px] flex-1 max-w-sm">
                    <i class="fas fa-search absolute left-2.5 top-1/2 /2 text-[var(--denta-muted)] text-xs"></i>
                    <input id="clinicSearch" type="search" value="<?= esc($filters['q'] ?? '', 'attr') ?>" placeholder="<?= esc(lang('V3Platform.searchClinicsOrAdminEmail'), 'attr') ?>"
                           class="cp-input w-full pl-8 text-xs v3-input">
                </div>
                <select id="clinicStatus" class="cp-select text-xs v3-select">
                    <option value=""><?= esc(lang('V3Platform.allStatuses')) ?></option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>><?= esc(lang('V3Platform.active')) ?></option>
                    <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>><?= esc(lang('V3Platform.suspended')) ?></option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= esc(lang('V3Platform.inactive')) ?></option>
                </select>
                <select id="clinicPlan" class="cp-select text-xs v3-select">
                    <option value="0"><?= esc(lang('V3Platform.allPlans')) ?></option>
                    <?php foreach (($plans ?? []) as $plan): ?>
                        <option value="<?= (int) ($plan['id'] ?? 0) ?>" <?= (int) ($filters['plan_id'] ?? 0) === (int) ($plan['id'] ?? 0) ? 'selected' : '' ?>><?= esc($plan['name'] ?? 'Plan') ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="resetClinicFilters" type="button" class="cp-btn cp-btn-light cp-btn-sm v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.clearFilters'), 'attr') ?>">
                    <i class="fas fa-times"></i>
                    <span><?= esc(lang('V3Platform.clear')) ?></span>
                </button>
            </div>
            <div class="flex items-center gap-1.5">
                <button onclick="exportToPDF()" class="cp-btn cp-btn-light cp-btn-sm v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.exportPdf'), 'attr') ?>">
                    <i class="fas fa-file-pdf text-[#9E2A2B]"></i>
                    <span>PDF</span>
                </button>
                <button onclick="printTable()" class="cp-btn cp-btn-light cp-btn-sm v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.printRegistry'), 'attr') ?>">
                    <i class="fas fa-print"></i>
                    <span><?= esc(lang('V3Platform.print')) ?></span>
                </button>
                <button onclick="exportToCSV()" class="cp-btn cp-btn-light cp-btn-sm v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.exportCsv'), 'attr') ?>">
                    <i class="fas fa-file-csv text-[var(--denta-emerald-pine)]"></i>
                    <span>CSV</span>
                </button>
            </div>
        </div>

        <!-- DataTable Container -->
        <div class="overflow-x-auto">
            <table id="clinicsTable" class="cp-table dataTable">
                <thead>
                    <tr>
                        <th>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-hospital text-[var(--denta-emerald-pine)]"></i>
                                <span><?= esc(lang('V3Platform.clinicName')) ?></span>
                            </div>
                        </th>
                        <th>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-info-circle text-[var(--denta-emerald-pine)]"></i>
                                <span><?= esc(lang('V3Platform.status')) ?></span>
                            </div>
                        </th>
                        <th>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-layer-group text-[var(--denta-emerald-pine)]"></i>
                                <span><?= esc(lang('V3Platform.plan')) ?></span>
                            </div>
                        </th>
                        <th>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-user-shield text-[var(--denta-emerald-pine)]"></i>
                                <span><?= esc(lang('V3Platform.adminEmail')) ?></span>
                            </div>
                        </th>
                        <th>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-dollar-sign text-[var(--denta-emerald-pine)]"></i>
                                <span><?= esc(lang('V3Platform.mrr')) ?></span>
                            </div>
                        </th>
                        <th class="text-end">
                            <div class="flex items-center justify-end gap-1.5">
                                <i class="fas fa-cogs text-[var(--denta-emerald-pine)]"></i>
                                <span><?= esc(lang('V3Platform.actions')) ?></span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <p class="mt-2 text-xs text-[var(--denta-muted)] font-medium" role="note"><?= esc(lang('IonAuth.clinic_delete_history_protected')) ?></p>
</div>

<!-- Terminate Clinic Modal -->
<div id="terminateModal" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4 ">
    <div class="w-full max-w-lg cp-modal-box">
        <div class="cp-modal-head bg-[var(--denta-tint-alert)] border-b border-[#EABCB8]">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-[#8C2224] font-bold m-0"><?= esc(lang('V3Platform.dangerZone')) ?></p>
                <h3 class="text-sm font-bold text-[#8C2224] m-0"><?= esc(lang('V3Platform.terminateClinicTenant')) ?></h3>
            </div>
            <button type="button" class="p-1.5 v3-text-muted v3-hover-text-ink rounded v3-btn v3-btn--secondary" onclick="closeTerminateModal()">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <div class="cp-modal-body space-y-3 text-xs v3-text-ink">
            <p>
                <?= esc(lang('V3Platform.thisWillImmediatelyDisableAccessAndCancelSubscriptionsFor')) ?>
                <span id="terminateClinicName" class="font-bold v3-text-ink"><?= esc(lang('V3Platform.thisClinic')) ?></span>.
                Type the clinic name below to confirm termination.
            </p>
            <input type="text" id="terminateConfirmInput" class="cp-input w-full v3-input"
                   placeholder="<?= esc(lang('V3Platform.typeClinicNameToConfirm'), 'attr') ?>" oninput="updateTerminateState()">
        </div>
        <form id="terminateForm" method="post" class="cp-modal-foot">
            <?= csrf_field() ?>
            <button type="button" class="cp-btn cp-btn-light cp-btn-sm v3-btn v3-btn--secondary" onclick="closeTerminateModal()">
                <?= esc(lang('V3Platform.cancel')) ?>
            </button>
            <button id="terminateConfirmButton" type="submit" class="cp-btn cp-btn-danger cp-btn-sm v3-btn v3-btn--danger" disabled>
                <span><?= esc(lang('V3Platform.confirmTermination')) ?></span>
            </button>
        </form>
    </div>
</div>

<script>
document.body.classList.add('cp-clinics-page');
</script>

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
                        <a href="${detailUrl}" class="block v3-radius px-2 py-1.5 transition-colors duration-200 v3-hover-primary">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center v3-radius text-sm font-black v3-text-ink"
                                     style="background: ${avatarColor}; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-black v3-text-ink">${name}</div>
                                    <div class="truncate text-xs font-bold v3-text-muted">ID ${id} - ${slug}</div>
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
                        'active': 'v3-bg-paid v3-text-primary',
                        'suspended': 'v3-bg-waiting v3-text-warning',
                        'inactive': 'v3-bg-soft v3-text-ink'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'suspended': 'fas fa-pause-circle',
                        'inactive': 'fas fa-times-circle'
                    };

                    const colorClass = statusColors[status] || 'v3-bg-soft v3-text-ink';
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
                    return `<span class="inline-flex items-center rounded-full v3-bg-chair px-2.5 py-1 text-xs font-black v3-text-chair border v3-border-default">${escapeHtml(data || 'Unassigned')}</span>`;
                }
            },
            {
                data: 'admin_email',
                name: 'admin_email',
                render: function(data, type, row) {
                    const email = escapeHtml(data || 'None');
                    if (email === 'None' || email === 'Unknown' || row.admin_state === 'unassigned') {
                        return `<span class="inline-flex items-center rounded-full v3-bg-soft px-2.5 py-0.5 text-xs font-bold v3-text-muted">${escapeHtml(row.admin_state_label || 'Unassigned')}</span>`;
                    }
                    if (row.admin_is_fallback) {
                        return `
                            <div>
                                <div class="text-sm font-semibold v3-text-ink">${email}</div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold v3-bg-waiting v3-text-warning border v3-border-default mt-0.5">
                                    <i class="fas fa-clock text-[9px] me-1"></i><?= esc(lang('UI.legacyAdminCandidate'), 'js') ?>
                                </span>
                            </div>
                        `;
                    }
                    if (row.admin_is_healthy) {
                        return `<span class="text-sm font-semibold v3-text-ink">${email}</span>`;
                    }
                    let badgeClass = 'v3-bg-waiting v3-text-warning';
                    if (row.admin_state === 'disabled_user') {
                        badgeClass = 'v3-bg-alert v3-text-danger';
                    } else if (row.admin_state === 'inactive_membership') {
                        badgeClass = 'v3-bg-waiting v3-text-warning';
                    }
                    return `
                        <div>
                            <div class="text-sm font-semibold v3-text-ink">${email}</div>
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
                    return `<span class="text-sm font-black v3-text-ink">${formatCurrency(data)}</span>`;
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
                           class="v3-radius p-2 v3-text-primary transition-colors duration-200 v3-hover-primary v3-hover-text-primary"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>`;

                    // Suspend / Reactivate
                    if (status === 'active') {
                        actions += `
                            <button onclick="toggleClinicStatus('${id}', 'suspend')" class="v3-radius p-2 v3-text-warning transition-colors duration-200 v3-hover-primary v3-hover-text-warning v3-btn v3-btn--secondary" title="Suspend">
                                <i class="fas fa-pause"></i>
                            </button>`;
                    } else {
                        actions += `
                            <button onclick="toggleClinicStatus('${id}', 'reactivate')" class="v3-radius p-2 v3-text-primary transition-colors duration-200 v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary" title="Reactivate">
                                <i class="fas fa-play"></i>
                            </button>`;
                    }

                    // Login As (Impersonate)
                    actions += `
                        <form action="${baseUrl}controlplane/impersonate" method="post" class="inline">
                            ${renderCsrfField()}
                            <input type="hidden" name="clinic_id" value="${id}">
                            <button type="submit" class="v3-radius p-2 v3-text-chair transition-colors duration-200 v3-hover-primary v3-hover-text-chair v3-btn v3-btn--primary" title="Login As">
                                <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </form>`;

                    // Terminate
                    actions += `
                        <button type="button" class="v3-radius p-2 v3-text-danger transition-colors duration-200 v3-hover-danger v3-hover-text-danger v3-btn v3-btn--danger"
                                onclick="openTerminateModal('${id}', '${encodedName}')" title="Terminate">
                            <i class="fas fa-ban"></i>
                        </button>`;

                    actions += `<button type="button" disabled class="p-2 v3-text-muted cursor-not-allowed v3-btn v3-btn--secondary" title="<?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?>"><i class="fas fa-lock" aria-hidden="true"></i><span class="sr-only"><?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?></span></button>`;
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
            zeroRecords: `<div class="py-12 text-center v3-text-muted space-y-3"><p class="text-sm font-semibold">No clinics found</p></div>`,
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
        buttonEl.classList.add('v3-hover-danger');
    } else {
        buttonEl.classList.add('opacity-50', 'cursor-not-allowed');
        buttonEl.classList.remove('v3-hover-danger');
    }
}







</script>

<?= $this->endSection() ?>
