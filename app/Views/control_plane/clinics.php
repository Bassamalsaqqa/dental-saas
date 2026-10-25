<?php helper('asset'); ?>
<?= $this->extend('layouts/main_control_plane') ?><?= $this->section('content') ?>
<header class="v4-page-heading"><div><h1><?= esc(lang('V4.tenantDirectory')) ?></h1><p><?= esc(lang('V4.tenantDirectoryHelp')) ?></p></div><div class="v4-page-actions"><a class="v4-button" href="<?= base_url('controlplane/plans') ?>"><?= esc(lang('UI.cpPlans')) ?></a><a class="v4-button v4-button--primary" href="<?= base_url('controlplane/onboarding/clinic/create') ?>"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('V4.onboarding')) ?></a></div></header>
<section class="v4-surface v4-ledger v4-tenant-directory"><div class="v4-tenant-filters v4-form"><label><span class="sr-only"><?= esc(lang('V4.searchTenants')) ?></span><input type="search" id="clinicSearch" value="<?= esc($filters['q']??'','attr') ?>" placeholder="<?= esc(lang('V4.searchTenants'),'attr') ?>"></label><select id="clinicStatus" aria-label="<?= esc(lang('V4.tenantStatus')) ?>" data-no-select2><?php foreach([''=>'V4.allStatuses','active'=>'V4.active','suspended'=>'V4.suspended','inactive'=>'V4.inactive'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($filters['status']??'')===$value?'selected':'' ?>><?= esc(lang($label)) ?></option><?php endforeach; ?></select><select id="clinicPlan" aria-label="<?= esc(lang('UI.cpPlans')) ?>" data-no-select2><option value="0"><?= esc(lang('V4.allPlans')) ?></option><?php foreach(($plans??[]) as $plan): ?><option value="<?= (int)($plan['id']??0) ?>" <?= (int)($filters['plan_id']??0)===(int)($plan['id']??0)?'selected':'' ?>><?= esc($plan['name']??'') ?></option><?php endforeach; ?></select><button type="button" id="resetClinicFilters" class="v4-button"><?= esc(lang('V4.clearFilters')) ?></button></div><header class="v4-ledger-header"><h2><?= esc(lang('UI.cpClinics')) ?> <span class="v4-directory-count"><?= number_format($stats['total']??0) ?></span></h2><details class="v4-action-menu"><summary class="v4-button v4-button--small"><?= esc(lang('V4.exportRecords')) ?></summary><div><button type="button" onclick="exportToPDF()">PDF</button><button type="button" onclick="printTable()"><?= esc(lang('Finance.print')) ?></button><button type="button" onclick="exportToCSV()">CSV</button></div></details></header><div class="v4-ledger-data" data-v4-records="tenants" data-v4-title-index="0" data-v4-copy-actions="true"><table id="clinicsTable"><thead><tr><?php foreach(['V4.clinic','V4.tenantStatus','V4.plan','V4.adminEmail','V4.estimatedMrr','V4.actions'] as $label): ?><th><?= esc(lang($label)) ?></th><?php endforeach; ?></tr></thead><tbody></tbody></table><div class="v4-mobile-records" data-v4-record-cards aria-live="polite"></div></div></section><script src="<?= base_url('assets/js/v4-record-cards.js') ?>?v=<?= esc(asset_version('assets/js/v4-record-cards.js')) ?>"></script>
<div id="terminateModal" class="v4-cp-confirm hidden">
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
                        <a href="${detailUrl}" class="v4-tenant-link">
                            <div class="flex items-center gap-3">
                                <div class="v4-tenant-avatar">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <div class="v4-tenant-name">${name}</div>
                                    <div class="v4-tenant-reference">ID ${id} - ${slug}</div>
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
                    return `<span class="v4-badge">${escapeHtml(data || 'Unassigned')}</span>`;
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
                    return `<span class="v4-money">${formatCurrency(data)}</span>`;
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
                           class="v4-icon-button"
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
                            <button type="submit" class="v4-icon-button" title="Login As">
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
