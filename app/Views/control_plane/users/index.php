<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php $disableDataTables = false; ?>

<style>
/* Keep existing styles */
.dataTables_wrapper { position: relative !important; }
#usersTable tbody tr td { border-bottom: 1px solid rgba(226, 232, 240, 0.6); }
/* Reset DataTables Button Styles */
button.dt-button {
    background-image: none !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow:none;
    text-shadow: none !important;
    margin-right: 0.5rem !important;
}
button.dt-button:hover, button.dt-button:active {
    background-image: none !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow:none;
}
/* Hide default buttons */
.dt-buttons { display: none !important; }
</style>

<!-- Enhanced User Management -->
<div class="v3-platform-workspace min-h-screen relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">


    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-4">
            <div class="cp-surface border cp-border cp-radius p-6">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 cp-radius flex items-center justify-center v3-text-on-primary">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold v3-text-ink"><?= esc(lang('V3Platform.userRegistry')) ?></h1>
                            <p class="v3-text-muted"><?= esc(lang('V3Platform.managePlatformUsersAndAccess')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                <!-- Total Users -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius v3-text-on-primary transition-all duration-200">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.allTime')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.totalUsers')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black cp-accent"><?= number_format($stats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs v3-text-muted font-medium"><?= esc(lang('V3Platform.platformUsers')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius v3-text-on-primary transition-all duration-200">
                                    <i class="fas fa-user-check text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border v3-border-default">
                                <div class="w-2 h-2 v3-bg-primary rounded-full"></div>
                                <span class="v3-text-primary font-bold text-xs"><?= esc(lang('V3Platform.active')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.activeUsers')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black v3-text-primary"><?= number_format($stats['active'] ?? 0) ?></p>
                                <div class="w-16 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs v3-text-muted font-medium"><?= esc(lang('V3Platform.currentlyOnline')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- New Users -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius v3-text-on-primary transition-all duration-200">
                                    <i class="fas fa-user-plus text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.thisMonth')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.newSignups')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black cp-accent"><?= number_format($stats['new'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs v3-text-muted font-medium"><?= esc(lang('V3Platform.monthlyGrowth')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="mb-4">
            <div class="cp-surface cp-radius border cp-border overflow-hidden">
                <div class="px-6 py-4 border-b cp-border cp-surface">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                            <h3 class="text-lg font-semibold v3-text-ink"><?= esc(lang('V3Platform.userRegistry')) ?></h3>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Search -->
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform /2 v3-text-muted"></i>
                                <input type="text" id="userSearch" placeholder="<?= esc(lang('V3Platform.searchUsers'), 'attr') ?>"
                                       class="pl-10 pr-4 py-2 rounded-lg border cp-border text-sm v3-focus-control v3-focus-control w-64 v3-input">
                            </div>

                            <!-- Status Filter -->
                            <select id="userStatus" class="py-2 pl-3 pr-8 rounded-lg border cp-border text-sm v3-focus-control v3-focus-control cp-surface v3-select">
                                <option value=""><?= esc(lang('V3Platform.allStatus')) ?></option>
                                <option value="active"><?= esc(lang('V3Platform.active')) ?></option>
                                <option value="inactive"><?= esc(lang('V3Platform.inactive')) ?></option>
                            </select>

                            <!-- Export Actions -->
                            <div class="flex items-center space-x-2 border-l cp-border pl-3">
                                <button onclick="exportToPDF()" class="p-2 v3-text-danger v3-hover-danger rounded-lg transition-colors v3-btn v3-btn--danger" title="<?= esc(lang('V3Platform.exportPdf'), 'attr') ?>">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button onclick="exportToCSV()" class="p-2 v3-text-primary v3-hover-primary rounded-lg transition-colors v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.exportCsv'), 'attr') ?>">
                                    <i class="fas fa-file-csv"></i>
                                </button>
                                <button onclick="printTable()" class="p-2 v3-text-muted v3-hover-soft rounded-lg transition-colors v3-btn v3-btn--secondary" title="<?= esc(lang('V3Platform.printTable'), 'attr') ?>">
                                    <i class="fas fa-print"></i>
                                </button>
                                <a href="<?= base_url('controlplane/users') ?>" class="p-2 v3-text-muted v3-hover-soft rounded-lg transition-colors" title="<?= esc(lang('V3Platform.resetFilters'), 'attr') ?>">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="usersTable" class="min-w-full divide-y v3-border-default">
                        <thead class="cp-surface">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.user')) ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.primaryRole')) ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.clinics')) ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.status')) ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.joined')) ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('V3Platform.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="cp-surface divide-y v3-border-default"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<p class="mb-4 text-sm v3-text-muted" role="note"><?= esc(lang('IonAuth.delete_history_protected')) ?></p>

<script>
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
                                <div class="w-10 h-10 rounded-full v3-bg-chair flex items-center justify-center text-sm font-bold v3-text-chair v3-elevation-none">
                                    ${initials}
                                </div>
                                <div>
                                    <div class="text-sm font-medium v3-text-ink">${data}</div>
                                    <div class="text-xs v3-text-muted">${row.email}</div>
                                </div>
                            </div>
                        `;
                    return `
                        <a href="${baseUrl}controlplane/users/${row.id}" class="block rounded-md v3-hover-primary transition-colors duration-200 cursor-pointer">
                            ${identityHtml}
                        </a>
                    `;
                }
            },
            {
                data: 'role',
                render: function(data) {
                    let color = 'v3-bg-soft v3-text-ink';
                    if (data === 'Super Admin') color = 'v3-bg-chair v3-text-chair';
                    if (data === 'Clinic Staff') color = 'v3-bg-scheduled v3-text-primary';
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${color}">${data}</span>`;
                }
            },
            {
                data: 'clinic_names',
                render: function(data, type, row) {
                    const names = Array.isArray(data) ? data : [];
                    if (names.length === 0) {
                        return `<span class="text-xs v3-text-muted">No clinic</span>`;
                    }
                    const preview = names.slice(0, 2);
                    const extra = names.length - preview.length;
                    const badges = preview.map(name => `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold v3-bg-scheduled v3-text-primary border v3-border-default">${name}</span>`).join(' ');
                    const extraBadge = extra > 0 ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold v3-bg-soft v3-text-muted border v3-border-default">+${extra}</span>` : '';
                    return `<div class="flex flex-wrap gap-1">${badges}${extraBadge}</div>`;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const isActive = data === 'active';
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${isActive ? 'v3-bg-paid v3-text-primary' : 'v3-bg-alert v3-text-danger'}">
                        <i class="fas fa-circle text-[8px] mr-1 ${isActive ? 'v3-text-primary' : 'v3-text-danger'}"></i>
                        ${isActive ? 'Active' : 'Inactive'}
                    </span>`;
                }
            },
            {
                data: 'created_on',
                render: function(data) {
                    return `<span class="text-sm v3-text-muted">${data}</span>`;
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
                           class="p-2 v3-text-primary v3-hover-text-primary rounded-md v3-hover-primary transition-colors duration-200" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>`;

                    // Toggle Status (Suspend/Activate)
                    if (isActive) {
                        actions += `
                            <button onclick="toggleUserStatus('${data}', 'deactivate')" class="p-2 v3-text-warning v3-hover-text-warning rounded-md v3-hover-primary transition-colors duration-200 v3-btn v3-btn--secondary" title="Deactivate">
                                <i class="fas fa-pause"></i>
                            </button>`;
                    } else {
                        actions += `
                            <button onclick="toggleUserStatus('${data}', 'activate')" class="p-2 v3-text-primary v3-hover-text-primary rounded-md v3-hover-primary transition-colors duration-200 v3-btn v3-btn--secondary" title="Activate">
                                <i class="fas fa-play"></i>
                            </button>`;
                    }

                    actions += `<button type="button" disabled class="p-2 v3-text-muted cursor-not-allowed v3-btn v3-btn--secondary" title="<?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?>"><i class="fas fa-lock" aria-hidden="true"></i><span class="sr-only"><?= esc(lang('IonAuth.delete_disabled_label'), 'js') ?></span></button>`;
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
