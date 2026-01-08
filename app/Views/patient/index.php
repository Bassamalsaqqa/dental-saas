<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables Styling -->
<style>
/* DataTables Controls Styling - More specific selectors */
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
    color: #374151 !important;
    margin-right: 12px !important;
}

.dataTables_length select {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_length select:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}

.dataTables_length select:hover {
    border-color: #9ca3af !important;
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
    color: #374151 !important;
    margin-right: 12px !important;
}

.dataTables_filter input {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_filter input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}

.dataTables_filter input:hover {
    border-color: #9ca3af !important;
}

.dataTables_info {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
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
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #f3f4f6 !important;
    border-color: #9ca3af !important;
    color: #1f2937 !important;
}

.dataTables_paginate .paginate_button.current {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: white !important;
}

.dataTables_paginate .paginate_button.disabled {
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
}

.dataTables_processing {
    background-color: rgba(255, 255, 255, 0.9) !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    padding: 20px !important;
    text-align: center !important;
}

/* Ensure proper spacing and layout */
.dataTables_wrapper .dataTables_length {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-right: auto !important;
    order: 1 !important;
}

.dataTables_wrapper .dataTables_filter {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-left: auto !important;
    order: 2 !important;
}

.dataTables_wrapper .dataTables_info {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-right: auto !important;
    order: 1 !important;
}

.dataTables_wrapper .dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-left: auto !important;
    order: 2 !important;
}

/* Ensure the top container uses flexbox */
.dataTables_wrapper > .row:first-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

/* Ensure the bottom container uses flexbox */
.dataTables_wrapper > .row:last-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
</style>

<!-- Enhanced Patient Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Simplified Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-users text-xl"></i>
                            </div>
                            <div>
                            <h1 class="text-3xl font-bold text-gray-900">Patient Management</h1>
                            <p class="text-gray-600">Manage patient records and information</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <?php if (has_permission('patients', 'create')): ?>
                            <a href="<?= base_url('patient/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                New Patient
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                <!-- Total Patients Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-teal-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                                <span class="text-emerald-700 font-bold text-xs">+8%</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Patients</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-900" id="totalPatients">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-emerald-200 to-teal-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">vs last month</p>
                        </div>
                    </div>
            </div>

                <!-- Active Patients Card -->
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
                                <i class="fas fa-shield-alt text-green-600 text-sm"></i>
                                <span class="text-green-700 font-bold text-sm">Active</span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Active Patients</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-green-900" id="activePatients">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Currently active</p>
                        </div>
                        </div>
                    </div>

                <!-- New Patients Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-user-plus text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-cyan-100 px-4 py-2 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-star text-blue-600 text-sm"></i>
                                <span class="text-blue-700 font-bold text-sm">New</span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">New Patients</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-blue-900" id="newPatients">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-blue-200 to-cyan-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">This month</p>
                        </div>
                                </div>
                            </div>
                            
                <!-- This Month Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-pink-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-calendar-day text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-purple-100 to-pink-100 px-4 py-2 rounded-full border border-purple-200">
                                <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-calendar text-purple-600 text-sm"></i>
                                <span class="text-purple-700 font-bold text-sm">This Month</span>
                            </div>
                        </div>
                        <div class="space-y-3 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">This Month</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-purple-900" id="monthlyPatients">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-purple-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-5/6 h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= date('M Y') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Standardized Patients Table -->
        <div class="mb-8">
            <!-- Table View -->
            <div id="tableView" class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Patient Registry</h3>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Export Buttons -->
                            <button onclick="exportToPDF()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                PDF
                            </button>
                            <button onclick="printPatients()" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
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

                    <!-- Enhanced Table with Advanced Styling -->
                    <div class="overflow-x-auto">
                        <table id="patientsTable" class="w-full min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-id-card text-emerald-600"></i>
                                            <span>Patient ID</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user text-emerald-600"></i>
                                            <span>Patient Name</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-phone text-emerald-600"></i>
                                            <span>Phone</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-envelope text-emerald-600"></i>
                                            <span>Email</span>
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
                                            <i class="fas fa-calendar text-emerald-600"></i>
                                            <span>Created</span>
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
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Data will be loaded via DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
// Global variables
let patientsTable;

$(document).ready(function() {
    // Check if table exists before initializing DataTable
    if ($('#patientsTable').length === 0) {
        console.error('Table #patientsTable not found');
        return;
    }
    
    // Initialize DataTable with server-side processing
    patientsTable = $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?= base_url('patient/get-data') ?>',
            type: 'POST',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.search_term = $('#searchInput').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);
                
                // Check if it's an authentication error
                if (xhr.status === 401 || xhr.status === 403) {
                    alert('Session expired. Please refresh the page and log in again.');
                    window.location.reload();
                } else if (xhr.status === 0) {
                    alert('Network error. Please check your connection.');
                } else {
                    alert('Error loading patient data. Please check console for details.');
                }
            }
        },
        columns: [
            { 
                data: 'patient_id',
                name: 'patient_id',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-id-card text-emerald-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data || 'N/A'}</div>
                                <div class="text-xs text-gray-500">Patient ID</div>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-emerald-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data || 'N/A'}</div>
                                <div class="text-xs text-gray-500">Patient Name</div>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'phone',
                name: 'phone',
                render: function(data, type, row) {
                    return data ? `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-phone text-emerald-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${data}</span>
                        </div>
                    ` : '<span class="text-gray-400 text-sm">No phone</span>';
                }
            },
            { 
                data: 'email',
                name: 'email',
                render: function(data, type, row) {
                    return data ? `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-emerald-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${data}</span>
                        </div>
                    ` : '<span class="text-gray-400 text-sm">No email</span>';
                }
            },
            { 
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'bg-green-100 text-green-800',
                        'inactive': 'bg-gray-100 text-gray-800',
                        'pending': 'bg-yellow-100 text-yellow-800'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'inactive': 'fas fa-times-circle',
                        'pending': 'fas fa-clock'
                    };
                    const colorClass = statusColors[data] || 'bg-gray-100 text-gray-800';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    
                    return `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            <i class="${iconClass} mr-1"></i>
                            ${data ? data.charAt(0).toUpperCase() + data.slice(1) : 'Unknown'}
                        </span>
                    `;
                }
            },
            { 
                data: 'created_at',
                name: 'created_at',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-calendar text-emerald-600 text-xs"></i>
                            <span class="text-sm text-gray-900">${data || 'N/A'}</span>
                        </div>
                    `;
                }
            },
            { 
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = '<div class="flex items-center space-x-2">';
                    
                    // View button
                    if (permissions.patients.view) {
                        actions += `
                            <a href="<?= base_url('patient') ?>/${data}" 
                               class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="View Patient">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }
                    
                    // Edit button
                    if (permissions.patients.edit) {
                        actions += `
                            <a href="<?= base_url('patient') ?>/${data}/edit" 
                               class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="Edit Patient">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }
                    
                    // Odontogram button
                    actions += `
                        <a href="<?= base_url('odontogram') ?>/${data}" 
                           class="p-2 text-purple-600 hover:text-purple-700 rounded-md hover:bg-purple-50 transition-colors duration-200" title="Odontogram">
                            <i class="fas fa-tooth"></i>
                        </a>`;
                    
                    // Delete button
                    if (permissions.patients.delete) {
                        actions += `
                            <button onclick="deletePatient(${data})" 
                                    class="p-2 text-red-600 hover:text-red-700 rounded-md hover:bg-red-50 transition-colors duration-200" title="Delete Patient">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }
                    
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        dom: 'B<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                className: 'btn-pdf',
                title: 'Patients Report',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i>Print',
                className: 'btn-print',
                title: 'Patients Report',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i>CSV',
                className: 'btn-csv',
                title: 'Patients Report',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            }
        ],
        language: {
            processing: "Loading patients...",
            lengthMenu: "_MENU_",
            zeroRecords: "No patients found",
            info: "Showing _START_ to _END_ of _TOTAL_ patients",
            infoEmpty: "No patients available",
            infoFiltered: "(filtered from _MAX_ total patients)",
            search: "",
            searchPlaceholder: "Search patients...",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        drawCallback: function(settings) {
            // Apply custom styling after each draw
            applyDataTablesStyling();
        }
    });

    // Apply custom DataTables styling
    function applyDataTablesStyling() {
        // Style length selector
        $('.dataTables_length select').addClass('focus:ring-emerald-500 focus:border-emerald-500');
        
        // Style search input
        $('.dataTables_filter input').addClass('focus:ring-emerald-500 focus:border-emerald-500');
        
        // Style info text
        $('.dataTables_info').addClass('text-gray-600');
        
        // Style pagination buttons
        $('.dataTables_paginate .paginate_button').each(function() {
            if ($(this).hasClass('current')) {
                $(this).addClass('bg-emerald-600 border-emerald-600 text-white');
            } else {
                $(this).addClass('hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700');
            }
        });
        
        // Style processing indicator
        $('.dataTables_processing').addClass('border-emerald-200 text-emerald-700');
        
        // Style labels
        $('.dataTables_length label, .dataTables_filter label').addClass('text-gray-700 font-semibold');
        
        // Style icons
        $('.dataTables_length i, .dataTables_filter i').addClass('text-emerald-600');
    }

    // Load statistics
    loadPatientStatistics();

    // Search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        table.draw();
    });
    
    // Add a fallback if the table fails to load
    setTimeout(function() {
        if (table && table.data().count() === 0) {
            console.log('Table appears empty, checking for errors...');
            // You could add fallback logic here if needed
        }
    }, 5000);
});

// Load patient statistics
function loadPatientStatistics() {
    $.ajax({
        url: '<?= base_url('patient/get-statistics') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalPatients').text(data.total_patients || 0);
            $('#activePatients').text(data.active_patients || 0);
            $('#newPatients').text(data.new_patients || 0);
            $('#monthlyPatients').text(data.monthly_patients || 0);
        },
        error: function() {
            $('#totalPatients').text('0');
            $('#activePatients').text('0');
            $('#newPatients').text('0');
            $('#monthlyPatients').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (patientsTable) {
        patientsTable.button('.btn-pdf').trigger();
    }
}

function printPatients() {
    if (patientsTable) {
        patientsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (patientsTable) {
        patientsTable.button('.btn-csv').trigger();
    }
}

// Delete patient function
function deletePatient(patientId) {
    confirmDelete('<?= base_url('patient') ?>/' + patientId, 'Are you sure you want to delete this patient? This action cannot be undone.');
}

// Permissions object (you'll need to populate this from your backend)
var permissions = {
    patients: {
        view: true,
        edit: true,
        delete: true,
        create: true
    }
};
</script>

<?= $this->endSection() ?>
