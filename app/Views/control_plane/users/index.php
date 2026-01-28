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
    box-shadow: none !important;
    text-shadow: none !important;
    margin-right: 0.5rem !important;
}
button.dt-button:hover, button.dt-button:active {
    background-image: none !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
}
/* Hide default buttons */
.dt-buttons { display: none !important; }
</style>

<!-- Enhanced User Management -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-indigo-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-indigo-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">User Registry</h1>
                            <p class="text-gray-600">Manage platform users and access</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Total Users -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-purple-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-xl">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Users</p>
                            <p class="text-3xl font-black text-indigo-900"><?= number_format($stats['total'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-xl">
                                <i class="fas fa-user-check text-xl"></i>
                            </div>
                            <span class="text-green-700 font-bold text-sm bg-green-100 px-3 py-1 rounded-full">Active</span>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Active Users</p>
                            <p class="text-3xl font-black text-green-900"><?= number_format($stats['active'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>

                <!-- New Users -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl">
                                <i class="fas fa-user-plus text-xl"></i>
                            </div>
                            <span class="text-blue-700 font-bold text-sm bg-blue-100 px-3 py-1 rounded-full">This Month</span>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">New Signups</p>
                            <p class="text-3xl font-black text-blue-900"><?= number_format($stats['new'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="mb-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">User Registry</h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                PDF
                            </button>
                            <button onclick="printTable()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-print mr-2 text-gray-600"></i>
                                Print
                            </button>
                            <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                CSV
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center space-x-2">
                        <select id="userStatus" class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <input type="text" id="userSearch" placeholder="Search users..." class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table id="usersTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Primary Role</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Clinics</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-3xl border border-rose-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4 bg-rose-50 rounded-t-3xl">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-rose-600 font-bold">Irreversible Action</p>
                <h3 class="text-lg font-bold text-rose-900">Delete User</h3>
            </div>
            <button type="button" class="rounded-full p-2 text-rose-400 hover:text-slate-700 hover:bg-rose-100" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-6 space-y-4 text-sm text-slate-600">
            <p class="font-medium text-slate-800">
                You are about to permanently delete <span id="deleteUserName" class="font-bold text-rose-600">this user</span>.
            </p>
            <div class="bg-rose-50 p-4 rounded-xl text-rose-800 text-xs border border-rose-100">
                <ul class="list-disc list-inside space-y-1">
                    <li>This action <strong>cannot be undone</strong>.</li>
                    <li>The user will be removed from all clinics.</li>
                    <li>They will no longer be able to log in.</li>
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
let usersTable;
const baseUrl = '<?= base_url() ?>';
let deleteUserName = '';

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

// Delete Modal Functions
function openDeleteModal(userId, userName) {
    const modal = document.getElementById('deleteModal');
    const nameEl = document.getElementById('deleteUserName');
    const inputEl = document.getElementById('deleteConfirmInput');
    const formEl = document.getElementById('deleteForm');
    
    deleteUserName = decodeURIComponent(userName);
    if (nameEl) nameEl.textContent = deleteUserName;
    if (formEl) formEl.action = `${baseUrl}controlplane/users/${userId}/delete`;
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

$(document).ready(function() {
    usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: { details: false },
        searching: false,
        dom: 'Bfrtip', // Enable Buttons
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
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-700 shadow-sm">
                                ${initials}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data}</div>
                                <div class="text-xs text-gray-500">${row.email}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'role',
                render: function(data) {
                    let color = 'bg-gray-100 text-gray-800';
                    if (data === 'Super Admin') color = 'bg-purple-100 text-purple-800';
                    if (data === 'Clinic Staff') color = 'bg-blue-100 text-blue-800';
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${color}">${data}</span>`;
                }
            },
            {
                data: 'clinic_count',
                render: function(data) {
                    return `<span class="text-sm text-gray-600 font-semibold">${data}</span>`;
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
                    return `<span class="text-sm text-gray-500">${data}</span>`;
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
                            <form action="${baseUrl}controlplane/users/${data}/toggle-status" method="post" class="inline">
                                ${renderCsrfField()}
                                <button type="submit" class="p-2 text-amber-600 hover:text-amber-700 rounded-md hover:bg-amber-50 transition-colors duration-200" title="Deactivate">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>`;
                    } else {
                        actions += `
                            <form action="${baseUrl}controlplane/users/${data}/toggle-status" method="post" class="inline">
                                ${renderCsrfField()}
                                <button type="submit" class="p-2 text-green-600 hover:text-green-700 rounded-md hover:bg-green-50 transition-colors duration-200" title="Activate">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>`;
                    }

                    // Delete
                    actions += `
                        <button type="button" class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200"
                                onclick="openDeleteModal('${data}', '${name}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>`;
                    
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
