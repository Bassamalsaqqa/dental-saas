<?php helper('asset'); ?><?= $this->extend('layouts/main_control_plane') ?><?= $this->section('content') ?><header class="v4-page-heading"><div><h1><?= esc(lang('V4.platformUsers')) ?></h1><p><?= esc(lang('V4.platformUsersHelp')) ?></p></div></header><section class="v4-surface v4-ledger"><div class="v4-platform-filter v4-form"><input type="search" id="userSearch" placeholder="<?= esc(lang('V4.searchUsers'),'attr') ?>" aria-label="<?= esc(lang('V4.searchUsers'),'attr') ?>"><select id="userStatus" aria-label="<?= esc(lang('V4.tenantStatus')) ?>" data-no-select2><option value=""><?= esc(lang('V4.allStatuses')) ?></option><option value="active"><?= esc(lang('V4.active')) ?></option><option value="inactive"><?= esc(lang('V4.inactive')) ?></option></select><a href="<?= base_url('controlplane/users') ?>" class="v4-button"><?= esc(lang('V4.clearFilters')) ?></a><details class="v4-action-menu"><summary class="v4-button"><?= esc(lang('V4.exportRecords')) ?></summary><div><button type="button" onclick="exportToPDF()">PDF</button><button type="button" onclick="exportToCSV()">CSV</button><button type="button" onclick="printTable()"><?= esc(lang('Finance.print')) ?></button></div></details></div><div class="v4-ledger-data" data-v4-records="platform-users" data-v4-title-index="0" data-v4-copy-actions="true"><table id="usersTable"><thead><tr><?php foreach(['V4.user','V4.primaryRole','UI.cpClinics','V4.tenantStatus','V4.joined','V4.actions'] as $label): ?><th><?= esc(lang($label)) ?></th><?php endforeach; ?></tr></thead><tbody></tbody></table><div class="v4-mobile-records" data-v4-record-cards aria-live="polite"></div></div></section><script src="<?= base_url('assets/js/v4-record-cards.js') ?>?v=<?= esc(asset_version('assets/js/v4-record-cards.js')) ?>"></script><!-- Delete Modal -->
<p class="mb-4 text-sm text-slate-600" role="note"><?= esc(lang('IonAuth.delete_history_protected')) ?></p>

<script>
const escapePlatformText=value=>String(value??'').replace(/[&<>"']/g, char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
let usersTable;
const baseUrl = '<?= base_url() ?>';

// ... (CSRF Helpers kept as is) ...
const csrfMetaName = document.querySelector('meta[name="csrf-name"]');
const csrfMetaToken = document.querySelector('meta[name="csrf-token"]');
const csrfCookieName = '<?= esc(config('Security')->cookieName) ?>';

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

function renderCsrfField() {
    return `<input type="hidden" name="${getCsrfName()}" value="${getCsrfToken()}">`;
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

// Toggle User Status AJAX
function toggleUserStatus(userId, action) {
    // Direct action without confirmation
    const formData = new FormData();
    const csrfName = getCsrfName();
    const csrfToken = getCsrfToken();
    formData.append(csrfName, csrfToken);

    fetch(`${baseUrl}controlplane/users/${userId}/toggle-status`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh table without resetting pagination
            if (usersTable) {
                usersTable.ajax.reload(null, false);
            }
            // Optional: Show toast instead of alert for smoother UX
            // alert(data.message);
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

$(document).ready(function() {
    usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: { details: false },
        searching: false,
        dom: 'Blfrtip', // Enable Buttons and Length Menu
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], // Define length options
        lengthChange: true, // Allow user to change page length
        buttons: [
            { extend: 'pdf', className: 'btn-pdf', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'print', className: 'btn-print', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'csv', className: 'btn-csv', exportOptions: { columns: [0,1,2,3,4] } }
        ],
        ajax: {
            url: '<?= base_url('controlplane/users') ?>',
            type: 'POST',
            dataType: 'json',
            beforeSend: function(xhr) {
                const token = getCsrfToken();
                if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
            },
            data: function(d) {
                d.status = $('#userStatus').val();
                d.q = $('#userSearch').val();
                d['<?= csrf_token() ?>'] = getCsrfToken();
                return d;
            },
            complete: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token && csrfMetaToken) {
                    csrfMetaToken.setAttribute('content', xhr.responseJSON.csrf_token);
                }
            }
        },
        columns: [
            {
                data: 'name',
                render: function(data, type, row) {
                    const initials = row.avatar || data.substring(0,2).toUpperCase();
                    const identityHtml = `
                            <div class="flex items-center space-x-3">
                                <div class="v4-tenant-avatar">
                                    ${escapePlatformText(initials)}
                                </div>
                                <div>
                                    <div class="v4-tenant-name">${escapePlatformText(data)}</div>
                                    <div class="v4-tenant-reference">${escapePlatformText(row.email)}</div>
                                </div>
                            </div>
                        `;
                    return `
                        <a href="${baseUrl}controlplane/users/${row.id}" class="block rounded-md hover:bg-indigo-50/70 transition-colors duration-200 cursor-pointer">
                            ${identityHtml}
                        </a>
                    `;
                }
            },
            {
                data: 'role',
                render: function(data) {
                    const color = 'v4-badge';
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${color}">${escapePlatformText(data)}</span>`;
                }
            },
            {
                data: 'clinic_names',
                render: function(data, type, row) {
                    const names = Array.isArray(data) ? data : [];
                    if (names.length === 0) {
                        return `<span class="text-xs text-gray-400">No clinic</span>`;
                    }
                    const preview = names.slice(0, 2);
                    const extra = names.length - preview.length;
                    const badges = preview.map(name => `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">${escapePlatformText(name)}</span>`).join(' ');
                    const extraBadge = extra > 0 ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">+${extra}</span>` : '';
                    return `<div class="flex flex-wrap gap-1">${badges}${extraBadge}</div>`;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const isActive = data === 'active';
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        <i class="fas fa-circle text-[8px] mr-1 ${isActive ? 'text-green-500' : 'text-red-500'}"></i>
                        ${isActive ? 'Active' : 'Inactive'}
                    </span>`;
                }
            },
            {
                data: 'created_on',
                render: function(data) {
                    return `<span class="text-sm text-gray-500">${escapePlatformText(data)}</span>`;
                }
            },
            {
                data: 'id',
                orderable: false,
                className: 'text-right',
                render: function(data, type, row) {
                    const isActive = row.status === 'active';
                    const name = encodeURIComponent(row.name);
                    let actions = '<div class="flex items-center justify-end space-x-2">';

                    // View
                    actions += `
                        <a href="${baseUrl}controlplane/users/${data}"
                           class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>`;

                    // Toggle Status (Suspend/Activate)
                    if (isActive) {
                        actions += `
                            <button onclick="toggleUserStatus('${data}', 'deactivate')" class="p-2 text-amber-600 hover:text-amber-700 rounded-md hover:bg-amber-50 transition-colors duration-200" title="Deactivate">
                                <i class="fas fa-pause"></i>
                            </button>`;
                    } else {
                        actions += `
                            <button onclick="toggleUserStatus('${data}', 'activate')" class="p-2 text-green-600 hover:text-green-700 rounded-md hover:bg-green-50 transition-colors duration-200" title="Activate">
                                <i class="fas fa-play"></i>
                            </button>`;
                    }

                    actions += `<button type="button" disabled class="p-2 text-slate-400 cursor-not-allowed" title="<?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?>"><i class="fas fa-lock" aria-hidden="true"></i><span class="sr-only"><?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?></span></button>`;
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        drawCallback: function() { refreshCsrfToken(); }
    });

    $('#userStatus, #userSearch').on('change keyup', function() {
        usersTable.draw();
    });

    // Export Functions
    window.exportToPDF = function() { usersTable.button('.btn-pdf').trigger(); };
    window.printTable = function() { usersTable.button('.btn-print').trigger(); };
    window.exportToCSV = function() { usersTable.button('.btn-csv').trigger(); };
});
</script>
<?= $this->endSection() ?>
