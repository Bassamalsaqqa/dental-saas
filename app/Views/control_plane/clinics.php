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
    margin-right: 0.5rem !important;
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
</style>

<!-- Enhanced Clinic Management with Advanced Design -->
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
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-hospital-user text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Clinic Registry</h1>
                            <p class="text-gray-600">Manage clinics, subscriptions, and lifecycle</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-plus mr-2"></i>
                            Onboard Clinic
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Total Clinics Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-hospital text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Clinics</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-900"><?= number_format($stats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-emerald-200 to-teal-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Clinics Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-green-100 to-emerald-100 px-4 py-2 rounded-full border border-green-200">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-green-700 font-bold text-sm">Active</span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Active Clinics</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-green-900"><?= number_format($stats['active'] ?? 0) ?></p>
                                <div class="w-16 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan Coverage Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-layer-group text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">In View</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-blue-900"><?= number_format($stats['filtered'] ?? 0) ?></p>
                                <div class="w-16 h-1 bg-gradient-to-r from-blue-200 to-cyan-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Filtered results</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Table Section -->
    <div class="mb-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Clinic Registry</h3>
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
            </div>
            
            <div class="overflow-x-auto">
                <table id="clinicsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-hospital text-emerald-600"></i>
                                    <span>Clinic Name</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-emerald-600"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-layer-group text-emerald-600"></i>
                                    <span>Plan</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-emerald-600"></i>
                                    <span>MRR</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cogs text-emerald-600"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>
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

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-3xl border border-rose-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4 bg-rose-50 rounded-t-3xl">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-rose-600 font-bold">Irreversible Action</p>
                <h3 class="text-lg font-bold text-rose-900">Delete Clinic Database</h3>
            </div>
            <button type="button" class="rounded-full p-2 text-rose-400 hover:text-slate-700 hover:bg-rose-100" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-6 space-y-4 text-sm text-slate-600">
            <p class="font-medium text-slate-800">
                You are about to permanently delete <span id="deleteClinicName" class="font-bold text-rose-600">this clinic</span>.
            </p>
            <div class="bg-rose-50 p-4 rounded-xl text-rose-800 text-xs border border-rose-100">
                <ul class="list-disc list-inside space-y-1">
                    <li>This action <strong>cannot be undone</strong>.</li>
                    <li>All patient records, appointments, and financial data will be wiped.</li>
                    <li>Subscriptions will be cancelled immediately.</li>
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
    const words = name.trim().split(/\s+/).filter(Boolean);
    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    }
    return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
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
                    const avatarColor = getAvatarColor(initials || name.charAt(0));
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-slate-900"
                                 style="background: ${avatarColor}; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);">
                                ${initials}
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
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            <i class="${iconClass} mr-1"></i>
                            ${label}
                        </span>
                    `;
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
                    let actions = '<div class="flex items-center justify-end space-x-2">';

                    // View Details
                    actions += `
                        <a href="${baseUrl}controlplane/clinics/${id}"
                           class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200"
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>`;

                    // Suspend / Reactivate
                    if (status === 'active') {
                        actions += `
                            <form action="${baseUrl}controlplane/clinics/${id}/suspend" method="post" class="inline">
                                ${renderCsrfField()}
                                <button type="submit" class="p-2 text-amber-600 hover:text-amber-700 rounded-md hover:bg-amber-50 transition-colors duration-200" title="Suspend">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>`;
                    } else {
                        actions += `
                            <form action="${baseUrl}controlplane/clinics/${id}/reactivate" method="post" class="inline">
                                ${renderCsrfField()}
                                <button type="submit" class="p-2 text-green-600 hover:text-green-700 rounded-md hover:bg-green-50 transition-colors duration-200" title="Reactivate">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>`;
                    }

                    // Login As (Impersonate)
                    actions += `
                        <form action="${baseUrl}controlplane/impersonate" method="post" class="inline">
                            ${renderCsrfField()}
                            <input type="hidden" name="clinic_id" value="${id}">
                            <button type="submit" class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="Login As">
                                <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </form>`;

                    // Terminate
                    actions += `
                        <button type="button" class="p-2 text-rose-600 hover:text-rose-700 rounded-md hover:bg-rose-50 transition-colors duration-200"
                                onclick="openTerminateModal('${id}', '${encodedName}')" title="Terminate">
                            <i class="fas fa-ban"></i>
                        </button>`;

                    // Delete
                    actions += `
                        <button type="button" class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200"
                                onclick="openDeleteModal('${id}', '${encodedName}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>`;

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        columnDefs: [
            { targets: 0, responsivePriority: 3 },
            { targets: 2, responsivePriority: 2 },
            { targets: 4, responsivePriority: 1, className: 'dt-actions' }
        ],
        // Layout: Length+Filters (Top Left), Empty (Top Right), Table, Info+Paginate (Bottom)
        dom: '<"px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row items-center justify-between gap-4"<"flex flex-wrap items-center gap-4"l<"#customFilters">>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
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
        initComplete: function() {
            const filterHtml = `
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="clinicSearch" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Search clinics..." 
                               class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 w-64">
                    </div>
                    <select id="clinicStatus" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        <option value="" <?= ($filters['status'] ?? '') === '' ? 'selected' : '' ?>>All Status</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                    <select id="clinicPlan" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white" style="max-width: 150px;">
                        <option value="0" <?= (int)($filters['plan_id'] ?? 0) === 0 ? 'selected' : '' ?>>All Plans</option>
                        <?php foreach (($plans ?? []) as $plan): ?>
                            <option value="<?= esc($plan['id']) ?>" <?= (int)($filters['plan_id'] ?? 0) === (int)$plan['id'] ? 'selected' : '' ?>>
                                <?= esc($plan['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="flex items-center space-x-2 border-l border-gray-300 pl-3">
                        <button id="applyClinicFilters" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Apply Filters">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button id="resetClinicFilters" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>`;
            $('#customFilters').html(filterHtml);

            // Re-bind events since elements are dynamic
            $('#applyClinicFilters').on('click', function() { clinicsTable.draw(); });
            $('#resetClinicFilters').on('click', function() {
                $('#clinicSearch').val('');
                $('#clinicStatus').val('');
                $('#clinicPlan').val('0');
                clinicsTable.draw();
            });
        }
    });

    // Event binding moved to initComplete
    // $('#applyClinicFilters').on('click', function() { clinicsTable.draw(); });
    // $('#resetClinicFilters').on('click', function() { ... });

    const termModal = document.getElementById('terminateModal');
    if (termModal) termModal.addEventListener('click', function(event) { if (event.target === termModal) closeTerminateModal(); });
    const delModal = document.getElementById('deleteModal');
    if (delModal) delModal.addEventListener('click', function(event) { if (event.target === delModal) closeDeleteModal(); });
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
</script>



<script>
let deleteClinicName = '';

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

// Delete Modal Functions
function openDeleteModal(clinicId, clinicNameEncoded) {
    const modal = document.getElementById('deleteModal');
    const nameEl = document.getElementById('deleteClinicName');
    const inputEl = document.getElementById('deleteConfirmInput');
    const formEl = document.getElementById('deleteForm');
    const decodedName = decodeURIComponent(clinicNameEncoded || 'Clinic');

    deleteClinicName = decodedName;
    if (nameEl) nameEl.textContent = decodedName;
    if (formEl) formEl.action = `${baseUrl}controlplane/clinics/${clinicId}/delete`;
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
</script>

<?= $this->endSection() ?>
