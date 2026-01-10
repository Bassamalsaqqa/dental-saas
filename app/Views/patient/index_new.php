<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Patient Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="relative z-10">
        <!-- Enhanced Header with Glassmorphism -->
        <div class="backdrop-blur-xl bg-white/80 border-b border-white/20 shadow-2xl shadow-emerald-500/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center space-x-3">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-3xl font-black text-gray-900">Patient Management</h1>
                                <div class="flex items-center space-x-4">
                                    <p class="text-sm text-gray-600 font-medium">Advanced patient management with real-time search and sorting</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('patient/create') ?>" class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-cyan-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-plus mr-2 relative z-10"></i>
                            <span class="relative z-10">Add Patient</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Patient Table with Server-Side Processing -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <!-- Enhanced Patient Directory Header -->
            <div class="group relative mb-8 mt-8">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                        <i class="fas fa-list text-white text-lg"></i>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300">Patient Directory</h2>
                                    <div class="flex items-center space-x-4">
                                        <p class="text-sm text-gray-600 font-medium">Advanced patient management with real-time search and sorting</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-cyan-100 px-4 py-2 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-server text-blue-600 text-sm"></i>
                                <span class="text-blue-700 font-bold text-sm">Server-Side Processing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced DataTable with Server-Side Processing -->
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500">
                    <!-- DataTable Container -->
                    <div class="p-6">
                        <table id="patientsTable" class="w-full">
                            <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Patient ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Age</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Examinations</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Last Visit</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/50 divide-y divide-gray-200/50">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with server-side processing
    $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('patient') ?>',
            type: 'GET'
        },
        columns: [
            { 
                data: 'patient_id',
                name: 'patient_id',
                className: 'px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'
            },
            { 
                data: 'name',
                name: 'name',
                className: 'px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'
            },
            { 
                data: 'email',
                name: 'email',
                className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-500'
            },
            { 
                data: 'phone',
                name: 'phone',
                className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-500'
            },
            { 
                data: 'age',
                name: 'age',
                className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-500'
            },
            { 
                data: 'status',
                name: 'status',
                className: 'px-6 py-4 whitespace-nowrap',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'bg-gradient-to-r from-emerald-100 to-green-100 text-emerald-800 border-emerald-200',
                        'inactive': 'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-800 border-gray-200',
                        'pending': 'bg-gradient-to-r from-amber-100 to-orange-100 text-amber-800 border-amber-200'
                    };
                    const colorClass = statusColors[data.toLowerCase()] || statusColors['inactive'];
                    return `<span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold ${colorClass} border shadow-sm">
                        <div class="w-2 h-2 bg-current rounded-full mr-2 animate-pulse"></div>
                        ${data}
                    </span>`;
                }
            },
            { 
                data: 'examinations',
                name: 'examinations',
                className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-500'
            },
            { 
                data: 'last_visit',
                name: 'last_visit',
                className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-500'
            },
            { 
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'px-6 py-4 whitespace-nowrap text-sm font-medium',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-2">
                            <a href="<?= base_url('patient/') ?>${data}" class="group/action relative p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-all duration-300 hover:scale-110" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('patient/') ?>${data}/edit" class="group/action relative p-2 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-all duration-300 hover:scale-110" title="Edit Patient">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= base_url('odontogram/') ?>${data}" class="group/action relative p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-all duration-300 hover:scale-110" title="Odontogram">
                                <i class="fas fa-tooth"></i>
                            </a>
                            <a href="<?= base_url('examination/create?patient_id=') ?>${data}" class="group/action relative p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-all duration-300 hover:scale-110" title="New Examination">
                                <i class="fas fa-stethoscope"></i>
                            </a>
                            <button onclick="confirmDelete(${data})" class="group/action relative p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-all duration-300 hover:scale-110" title="Delete Patient">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[7, 'desc']], // Order by created_at desc by default
        dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 mb-6"<"flex items-center space-x-4"<"text-sm text-gray-700 font-medium">l><"text-sm text-gray-700 font-medium">f>>rt<"flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 mt-6"<"text-sm text-gray-700 font-medium">ip>>',
        language: {
            processing: '<div class="flex items-center justify-center p-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div><span class="ml-3 text-gray-600">Loading patients...</span></div>',
            emptyTable: '<div class="text-center py-16"><div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-users text-gray-400 text-3xl"></i></div><h3 class="text-xl font-bold text-gray-900 mb-2">No patients found</h3><p class="text-gray-500 font-medium">Get started by adding your first patient.</p></div>',
            zeroRecords: '<div class="text-center py-16"><div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-search text-gray-400 text-3xl"></i></div><h3 class="text-xl font-bold text-gray-900 mb-2">No matching records found</h3><p class="text-gray-500 font-medium">Try adjusting your search criteria.</p></div>'
        },
        responsive: true,
        scrollX: true,
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel mr-2"></i>Export Excel',
                className: 'bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-300'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>Export PDF',
                className: 'bg-gradient-to-r from-red-500 to-pink-600 text-white px-4 py-2 rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-300'
            }
        ]
    });
});

// Delete confirmation function
let patientToDelete = null;

function confirmDelete(patientId) {
    patientToDelete = patientId;
    
    // Create a beautiful confirmation modal safely
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    
    const card = document.createElement('div');
    card.className = 'bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-100';
    
    const content = document.createElement('div');
    content.className = 'p-6';
    
    // Header section
    const header = document.createElement('div');
    header.className = 'flex items-center space-x-4 mb-6';
    
    const iconContainer = document.createElement('div');
    iconContainer.className = 'w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center';
    const icon = document.createElement('i');
    icon.className = 'fas fa-exclamation-triangle text-white text-xl';
    iconContainer.appendChild(icon);
    
    const titleContainer = document.createElement('div');
    const title = document.createElement('h3');
    title.className = 'text-xl font-bold text-gray-900';
    title.textContent = 'Delete Patient';
    const subtitle = document.createElement('p');
    subtitle.className = 'text-gray-600';
    subtitle.textContent = 'This action cannot be undone.';
    titleContainer.appendChild(title);
    titleContainer.appendChild(subtitle);
    
    header.appendChild(iconContainer);
    header.appendChild(titleContainer);
    
    // Message
    const message = document.createElement('p');
    message.className = 'text-gray-700 mb-6';
    message.textContent = 'Are you sure you want to delete this patient? All associated data will be permanently removed.';
    
    // Actions
    const actions = document.createElement('div');
    actions.className = 'flex items-center justify-end space-x-3';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.onclick = closeDeleteModal;
    cancelBtn.className = 'px-4 py-2 text-gray-600 hover:text-gray-800 font-medium transition-colors duration-200';
    cancelBtn.textContent = 'Cancel';
    
    const deleteBtn = document.createElement('button');
    deleteBtn.onclick = deletePatient;
    deleteBtn.className = 'px-6 py-2 bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-300';
    deleteBtn.textContent = 'Delete Patient';
    
    actions.appendChild(cancelBtn);
    actions.appendChild(deleteBtn);
    
    // Assemble
    content.appendChild(header);
    content.appendChild(message);
    content.appendChild(actions);
    card.appendChild(content);
    modal.appendChild(card);
    
    document.body.appendChild(modal);
}

function closeDeleteModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (modal) {
        modal.remove();
    }
    patientToDelete = null;
}

function deletePatient() {
    if (patientToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('patient') ?>/' + patientToDelete;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?= $this->endSection() ?>
