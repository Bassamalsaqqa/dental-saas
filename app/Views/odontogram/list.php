<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
<div class="space-y-8">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 mb-8 border border-blue-100">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-tooth text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-2">
                        Odontogram Management
                    </h1>
                    <p class="text-gray-600 text-lg flex items-center">
                        <i class="fas fa-chart-line mr-2 text-blue-500"></i>
                        Select a patient to view their dental chart
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="bg-white/70 px-4 py-2 rounded-lg border border-blue-200">
                    <span class="text-sm font-medium text-gray-600">Total Patients:</span>
                    <span class="ml-2 text-lg font-bold text-blue-600"><?= count($patients ?? []) ?></span>
                </div>
            </div>
        </div>
    </div>


    <!-- Enhanced Patients List with Server-Side Processing -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-users mr-3 text-green-600"></i>
                    All Patients
                </h3>
                <p class="text-sm text-gray-600">Click on a patient to view their odontogram</p>
            </div>


            <!-- Enhanced Compact Search and Filter Controls -->
            <div class="bg-gradient-to-r from-slate-50 to-blue-50 border border-slate-200 rounded-xl p-4 mb-6 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Left Section: Search and Controls -->
                    <div class="flex flex-col sm:flex-row gap-3 flex-1">
                        <!-- Search Input with Enhanced Design -->
                        <div class="relative flex-1 min-w-0">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400 text-sm"></i>
                            </div>
                            <input type="text" id="globalSearch" 
                                   placeholder="Search patients by name, phone, email, or ID..." 
                                   class="w-full pl-10 pr-4 h-10 border border-slate-300 rounded-lg text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm">
                        </div>
                        
                        <!-- Compact Controls Group -->
                        <div class="flex items-center gap-2">
                            <!-- Page Length Selector -->
                            <div class="flex items-center gap-2 bg-white px-3 h-10 border border-slate-300 rounded-lg shadow-sm">
                                <select id="pageLength" class="text-sm border-0 bg-transparent focus:outline-none text-slate-700 h-full">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            
                            <!-- Refresh Button -->
                            <button id="refreshTable" class="inline-flex items-center px-3 h-10 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 shadow-sm">
                                <i class="fas fa-sync-alt mr-1.5 text-slate-500"></i>
                                <span class="hidden sm:inline">Refresh</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Right Section: Action Button -->
                    <div class="flex-shrink-0">
                        <button onclick="openAddPatientModal()" 
                                class="inline-flex items-center px-4 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-plus mr-2"></i>
                            <span>Add Patient</span>
                        </button>
                    </div>
                </div>
                
                <!-- Enhanced Search Status -->
                <div id="searchStatus" class="hidden mt-3">
                    <div class="flex items-center gap-2 text-sm text-blue-600 bg-blue-50 px-3 py-2 rounded-lg border border-blue-200">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span id="searchStatusText">Searching...</span>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden text-center py-8">
                <div class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                    <i class="fas fa-spinner fa-spin mr-3 text-blue-600"></i>
                    <span class="text-blue-700 font-medium">Loading patients...</span>
                </div>
            </div>

            <!-- Data Table Container -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table id="patientsTable" class="min-w-full divide-y divide-gray-200">
                        <thead style="background: #f9fafb;">
                            <tr>
                                <th data-column="0" style="padding: 16px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; cursor: pointer; border-bottom: 2px solid #e5e7eb;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Patient</span>
                                        <span style="color: #9ca3af;">↕️</span>
                                    </div>
                                </th>
                                <th data-column="1" style="padding: 16px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; cursor: pointer; border-bottom: 2px solid #e5e7eb;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Contact Info</span>
                                        <span style="color: #9ca3af;">↕️</span>
                                    </div>
                                </th>
                                <th data-column="2" style="padding: 16px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; cursor: pointer; border-bottom: 2px solid #e5e7eb;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Patient ID</span>
                                        <span style="color: #9ca3af;">↕️</span>
                                    </div>
                                </th>
                                <th data-column="3" style="padding: 16px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; cursor: pointer; border-bottom: 2px solid #e5e7eb;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Age</span>
                                        <span style="color: #9ca3af;">↕️</span>
                                    </div>
                                </th>
                                <th data-column="4" style="padding: 16px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; cursor: pointer; border-bottom: 2px solid #e5e7eb;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Last Visit</span>
                                        <span style="color: #9ca3af;">↕️</span>
                                    </div>
                                </th>
                                <th style="padding: 16px; text-align: center; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; border-bottom: 2px solid #e5e7eb;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="patientsList">
                            <!-- Data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination and Info -->
            <div style="margin-top: 20px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between;">
                    <div style="font-size: 14px; color: #374151;">
                        Showing <span id="showingStart" style="font-weight: 600;">0</span> to <span id="showingEnd" style="font-weight: 600;">0</span> of <span id="totalRecords" style="font-weight: 600;">0</span> entries
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button id="prevPage" style="padding: 8px 12px; font-size: 14px; font-weight: 500; color: #374151; background: white; border: 2px solid #d1d5db; border-radius: 4px; cursor: pointer;">
                            ← Previous
                        </button>
                        <div id="paginationNumbers" style="display: flex; gap: 4px;">
                            <!-- Pagination numbers will be generated here -->
                        </div>
                        <button id="nextPage" style="padding: 8px 12px; font-size: 14px; font-weight: 500; color: #374151; background: white; border: 2px solid #d1d5db; border-radius: 4px; cursor: pointer;">
                            Next →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div id="addPatientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 text-white flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-plus text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Add New Patient</h3>
                        <p class="text-blue-100 text-sm">Enter patient information to create a new record</p>
                    </div>
                </div>
                <button onclick="closeAddPatientModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <form id="addPatientForm" class="space-y-6">
                <!-- Personal Information Section -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Personal Information
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" id="firstName" name="first_name" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="Enter first name">
                        </div>
                        
                        <!-- Last Name -->
                        <div>
                            <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" id="lastName" name="last_name" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="Enter last name">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Date of Birth -->
                        <div>
                            <label for="dateOfBirth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                            <input type="date" id="dateOfBirth" name="date_of_birth" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        </div>
                        
                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                            <select id="gender" name="gender" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-phone mr-2 text-green-600"></i>
                        Contact Information
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="Enter phone number">
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="Enter email address">
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea id="address" name="address" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
                                  placeholder="Enter full address"></textarea>
                    </div>
                </div>

                <!-- Emergency Contact Section -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                        Emergency Contact
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Emergency Contact Name -->
                        <div>
                            <label for="emergencyName" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name</label>
                            <input type="text" id="emergencyName" name="emergency_contact_name"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="Enter emergency contact name">
                        </div>
                        
                        <!-- Emergency Contact Phone -->
                        <div>
                            <label for="emergencyPhone" class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone</label>
                            <input type="tel" id="emergencyPhone" name="emergency_contact_phone"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="Enter emergency contact phone">
                        </div>
                    </div>
                </div>

                <!-- Medical Information Section -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-heartbeat mr-2 text-purple-600"></i>
                        Medical Information
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Blood Type -->
                        <div>
                            <label for="bloodType" class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                            <select id="bloodType" name="blood_type"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <option value="">Select Blood Type</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        
                        <!-- Allergies -->
                        <div>
                            <label for="allergies" class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                            <input type="text" id="allergies" name="allergies"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="Enter any known allergies">
                        </div>
                    </div>

                    <!-- Medical Notes -->
                    <div>
                        <label for="medicalNotes" class="block text-sm font-medium text-gray-700 mb-2">Medical Notes</label>
                        <textarea id="medicalNotes" name="notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
                                  placeholder="Enter any relevant medical information"></textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between flex-shrink-0">
            <button onclick="testAjaxConnection()" 
                    class="px-4 py-2 text-sm text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200">
                <i class="fas fa-wifi mr-1"></i>
                Test Connection
            </button>
            <div class="flex items-center space-x-3">
                <button onclick="closeAddPatientModal()" 
                        class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200">
                    Cancel
                </button>
                <button onclick="submitAddPatient()" 
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Add Patient
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Server-side data processing for odontogram table
class OdontogramTable {
    constructor() {
        this.currentPage = 1;
        this.pageLength = 25;
        this.searchValue = '';
        this.orderColumn = 0;
        this.orderDir = 'asc';
        this.totalRecords = 0;
        this.filteredRecords = 0;
        this.totalPages = 0;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadData();
    }
    
    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('globalSearch');
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            
            // Show search status
            this.showSearchStatus('Searching...');
            
            searchTimeout = setTimeout(() => {
                this.searchValue = e.target.value;
                this.currentPage = 1;
                this.loadData();
            }, 500);
        });
        
        // Page length change
        document.getElementById('pageLength').addEventListener('change', (e) => {
            this.pageLength = parseInt(e.target.value);
            this.currentPage = 1;
            this.loadData();
        });
        
        // Refresh button
        document.getElementById('refreshTable').addEventListener('click', () => {
            this.loadData();
        });
        
        // Pagination buttons
        document.getElementById('prevPage').addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadData();
            }
        });
        
        document.getElementById('nextPage').addEventListener('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadData();
            }
        });
        
        // Column sorting
        document.querySelectorAll('th[data-column]').forEach(th => {
            th.addEventListener('click', (e) => {
                const column = parseInt(e.currentTarget.dataset.column);
                if (this.orderColumn === column) {
                    this.orderDir = this.orderDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.orderColumn = column;
                    this.orderDir = 'asc';
                }
                this.updateSortIcons();
                this.loadData();
            });
        });
    }
    
    async loadData() {
        this.showLoading(true);
        this.hideSearchStatus();
        
        try {
            const formData = new FormData();
            formData.append('draw', 1);
            formData.append('start', (this.currentPage - 1) * this.pageLength);
            formData.append('length', this.pageLength);
            formData.append('search[value]', this.searchValue);
            formData.append('order[0][column]', this.orderColumn);
            formData.append('order[0][dir]', this.orderDir);
            
            // Add CSRF token
            formData.append(window.csrfConfig.name, window.getCsrfToken());
            
            const response = await fetch('<?= base_url('odontogram/get-patients-data') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    [window.csrfConfig.header]: window.getCsrfToken()
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            console.log('Response data:', data);

            // Update token from response if available
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            this.totalRecords = data.recordsTotal;
            this.filteredRecords = data.recordsFiltered;
            this.totalPages = Math.ceil(this.filteredRecords / this.pageLength);
            
            this.renderTable(data.data);
            this.updatePagination();
            this.updateInfo();
            
            // Show search results status
            if (this.searchValue) {
                this.showSearchStatus(`Found ${this.filteredRecords} results for "${this.searchValue}"`);
            }
            
        } catch (error) {
            console.error('Error loading data:', error);
            console.error('Error details:', error.message);
            this.showError('Failed to load patients data: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }
    
    renderTable(data) {
        const tbody = document.getElementById('patientsList');
        
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <i class="fas fa-users text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">No patients found</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">No patients match your search criteria.</p>
                        <button onclick="odontogramTable.clearSearch()" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-times mr-2"></i>Clear Search
                        </button>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = data.map(patient => `
            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 cursor-pointer group hover:shadow-sm" onclick="viewOdontogram(${patient.id})">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg group-hover:scale-110 transition-transform duration-200">
                            ${patient.name.split(' ').map(n => n[0]).join('').toUpperCase()}
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                ${patient.name}
                            </div>
                            <div class="text-sm text-gray-500">
                                Added: ${patient.created_at}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="space-y-1">
                        <div class="flex items-center text-sm text-gray-900">
                            <i class="fas fa-phone w-4 mr-2 text-blue-500"></i>
                            ${patient.contact.phone}
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-envelope w-4 mr-2 text-green-500"></i>
                            ${patient.contact.email}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 group-hover:bg-blue-200 transition-colors duration-200">
                        ${patient.patient_id}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${patient.age}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${patient.last_visit}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                     <div class="flex flex-col space-y-3">
                         <button onclick="event.stopPropagation(); viewOdontogram(${patient.id})" class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:via-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-offset-2 transition-all duration-500 transform hover:scale-110 hover:shadow-2xl shadow-lg w-full overflow-hidden">
                             <!-- Animated background -->
                             <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-20 group-hover:animate-shimmer"></div>
                             
                             <!-- Button content -->
                             <div class="flex items-center relative z-10">
                                 <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3 group-hover:bg-opacity-30 transition-all duration-300">
                                     <i class="fas fa-tooth text-lg group-hover:animate-bounce"></i>
                                 </div>
                                 <div class="text-left">
                                     <div class="text-sm font-bold">View Chart</div>
                                     <div class="text-xs opacity-90 group-hover:opacity-100">Dental Records</div>
                                 </div>
                             </div>
                             
                             <!-- Hover effect overlay -->
                             <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl opacity-0 group-hover:opacity-10 transition-all duration-500"></div>
                             
                             <!-- Shine effect -->
                             <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-30 group-hover:animate-shine"></div>
                         </button>
                         
                         <!-- Enhanced status indicator -->
                         <div class="flex items-center justify-center space-x-2 text-xs">
                             <div class="flex items-center space-x-1">
                                 <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                 <span class="text-gray-600 font-medium">Active Patient</span>
                             </div>
                             <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                             <div class="flex items-center space-x-1">
                                 <i class="fas fa-chart-line text-blue-500"></i>
                                 <span class="text-gray-500">Dental Chart</span>
                             </div>
                         </div>
                     </div>
                </td>
            </tr>
        `).join('');
    }
    
    updatePagination() {
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const paginationNumbers = document.getElementById('paginationNumbers');
        
        // Update button states
        prevBtn.disabled = this.currentPage === 1;
        nextBtn.disabled = this.currentPage === this.totalPages;
        
        // Generate pagination numbers
        let paginationHTML = '';
        const maxVisiblePages = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === this.currentPage;
            paginationHTML += `
                <button onclick="odontogramTable.goToPage(${i})" 
                        style="padding: 8px 12px; font-size: 14px; font-weight: 500; border-radius: 4px; cursor: pointer; border: 2px solid #d1d5db; ${
                            isActive 
                                ? 'background: #3b82f6; color: white; border-color: #3b82f6;' 
                                : 'background: white; color: #374151;'
                        }">
                    ${i}
                </button>
            `;
        }
        
        paginationNumbers.innerHTML = paginationHTML;
    }
    
    updateInfo() {
        const start = this.filteredRecords === 0 ? 0 : (this.currentPage - 1) * this.pageLength + 1;
        const end = Math.min(this.currentPage * this.pageLength, this.filteredRecords);
        
        document.getElementById('showingStart').textContent = start;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalRecords').textContent = this.filteredRecords;
    }
    
    updateSortIcons() {
        document.querySelectorAll('th span[style*="color: #9ca3af"]').forEach(icon => {
            icon.textContent = '↕️';
            icon.style.color = '#9ca3af';
        });
        
        const currentTh = document.querySelector(`th[data-column="${this.orderColumn}"] span[style*="color: #9ca3af"]`);
        if (currentTh) {
            currentTh.textContent = this.orderDir === 'asc' ? '↑' : '↓';
            currentTh.style.color = '#3b82f6';
        }
    }
    
    goToPage(page) {
        this.currentPage = page;
        this.loadData();
    }
    
    clearSearch() {
        document.getElementById('globalSearch').value = '';
        this.searchValue = '';
        this.currentPage = 1;
        this.hideSearchStatus();
        this.loadData();
    }
    
    showSearchStatus(message) {
        const searchStatus = document.getElementById('searchStatus');
        const searchStatusText = document.getElementById('searchStatusText');
        searchStatusText.textContent = message;
        searchStatus.classList.remove('hidden');
        
        // Add a subtle animation
        searchStatus.style.opacity = '0';
        searchStatus.style.transform = 'translateY(-10px)';
        requestAnimationFrame(() => {
            searchStatus.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            searchStatus.style.opacity = '1';
            searchStatus.style.transform = 'translateY(0)';
        });
    }
    
    hideSearchStatus() {
        const searchStatus = document.getElementById('searchStatus');
        searchStatus.style.transition = 'all 0.2s ease-out';
        searchStatus.style.opacity = '0';
        searchStatus.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            searchStatus.classList.add('hidden');
            searchStatus.style.transition = '';
            searchStatus.style.opacity = '';
            searchStatus.style.transform = '';
        }, 200);
    }
    
    showLoading(show) {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const tableContainer = document.querySelector('.overflow-x-auto');
        
        if (show) {
            loadingIndicator.classList.remove('hidden');
            tableContainer.style.opacity = '0.5';
        } else {
            loadingIndicator.classList.add('hidden');
            tableContainer.style.opacity = '1';
        }
    }
    
    showError(message) {
        const tbody = document.getElementById('patientsList');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-16 text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-red-100 to-red-200 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-exclamation-triangle text-red-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Error Loading Data</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">${message}</p>
                    <button onclick="odontogramTable.loadData()" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <i class="fas fa-redo mr-2"></i>Try Again
                    </button>
                </td>
            </tr>
        `;
    }
}

// Initialize the table when DOM is loaded
let odontogramTable;
document.addEventListener('DOMContentLoaded', function() {
    // Check if required elements exist
    const searchInput = document.getElementById('globalSearch');
    const pageLengthSelect = document.getElementById('pageLength');
    const refreshButton = document.getElementById('refreshTable');
    
    if (searchInput && pageLengthSelect && refreshButton) {
        odontogramTable = new OdontogramTable();
    }
});

function viewOdontogram(patientId) {
    // Add loading state to the button
    const button = event.target.closest('button');
    if (button) {
        button.classList.add('loading');
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Loading...</span>';
        
        // Add success state briefly before navigation
        setTimeout(() => {
            button.classList.remove('loading');
            button.classList.add('success');
            button.innerHTML = '<i class="fas fa-check mr-2"></i><span>Opening...</span>';
            
            // Navigate after success animation
            setTimeout(() => {
                window.location.href = '<?= base_url('odontogram/') ?>' + patientId;
            }, 300);
        }, 500);
    } else {
        // Fallback if button not found
        window.location.href = '<?= base_url('odontogram/') ?>' + patientId;
    }
}

// Modal Functions
function openAddPatientModal() {
    const modal = document.getElementById('addPatientModal');
    const modalContent = document.getElementById('modalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('firstName').focus();
    }, 300);
}

function closeAddPatientModal() {
    const modal = document.getElementById('addPatientModal');
    const modalContent = document.getElementById('modalContent');
    
    // Animate out
    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        // Reset form
        document.getElementById('addPatientForm').reset();
    }, 300);
}

function submitAddPatient() {
    const form = document.getElementById('addPatientForm');
    const formData = new FormData(form);
    const submitButton = event.target;
    
    // Validate required fields
    const requiredFields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'phone'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    // Add loading state
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Patient...';
    submitButton.disabled = true;
    
    // Submit form data
    console.log('Submitting to URL:', '<?= base_url('patient/store') ?>');
    console.log('Form data:', Object.fromEntries(formData));
    
    fetch('<?= base_url('patient/store') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // If not JSON, get the text to see what we're getting
            return response.text().then(text => {
                console.error('Non-JSON response received:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            showNotification('Patient added successfully!', 'success');
            closeAddPatientModal();
            
            // Refresh the table
            if (typeof odontogramTable !== 'undefined') {
                odontogramTable.loadData();
            }
        } else {
            // Handle validation errors
            if (data.errors) {
                let errorMessage = 'Validation errors:\n';
                Object.keys(data.errors).forEach(field => {
                    errorMessage += `• ${data.errors[field]}\n`;
                });
                showNotification(errorMessage, 'error');
            } else {
                showNotification(data.message || 'Failed to add patient', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while adding the patient: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' :
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(full)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('addPatientModal');
    const modalContent = document.getElementById('modalContent');
    
    if (event.target === modal) {
        closeAddPatientModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('addPatientModal');
        if (!modal.classList.contains('hidden')) {
            closeAddPatientModal();
        }
    }
});

// Test AJAX connection
function testAjaxConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Testing...';
    button.disabled = true;
    
    fetch('<?= base_url('patient/test-ajax') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Test response status:', response.status);
        console.log('Test response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Test - Non-JSON response received:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Test response data:', data);
        showNotification('AJAX connection test successful!', 'success');
    })
    .catch(error => {
        console.error('Test error:', error);
        showNotification('AJAX connection test failed: ' + error.message, 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Smooth transitions for search results */
#patientsList > tr {
    transition: all 0.3s ease-in-out;
}

/* Enhanced hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}


/* Table specific styles */
table {
    border-collapse: separate;
    border-spacing: 0;
}

th {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Ensure action buttons are always visible */
td:last-child {
    min-width: 100px;
    max-width: 120px;
}

/* Make sure buttons don't get cut off */
button {
    white-space: nowrap;
}

/* Enhanced button animations */
.group:hover .group-hover\:animate-pulse {
    animation: pulse 1s infinite;
}

/* Button glow effect */
button:hover {
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
}

/* Smooth icon rotation on hover */
button:hover i {
    transform: rotate(5deg);
    transition: transform 0.3s ease;
}

/* Button press effect */
button:active {
    transform: scale(0.98);
    transition: transform 0.1s ease;
}

/* Loading state for button */
button.loading {
    pointer-events: none;
    opacity: 0.7;
}

button.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Success state animation */
button.success {
    background: linear-gradient(135deg, #10b981, #34d399) !important;
    animation: successPulse 0.6s ease-in-out;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Enhanced Search and Filter Controls */
#globalSearch {
    min-width: 250px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

#globalSearch:focus {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
    border-color: #3b82f6;
}

#globalSearch:hover {
    border-color: #94a3b8;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

#pageLength {
    min-width: 60px;
    transition: all 0.2s ease;
}

#pageLength:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

/* Enhanced Search Status */
#searchStatus {
    animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Search input icon animation */
#globalSearch:focus + .fas.fa-search {
    color: #3b82f6;
    transform: scale(1.1);
}

/* Enhanced button hover effects */
#refreshTable:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

#refreshTable:active {
    transform: translateY(0);
}

/* Compact controls styling */
.flex.items-center.gap-2 > div {
    transition: all 0.2s ease;
}

.flex.items-center.gap-2 > div:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Enhanced search section animations */
.bg-gradient-to-r.from-slate-50.to-blue-50 {
    transition: all 0.3s ease;
}

.bg-gradient-to-r.from-slate-50.to-blue-50:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Search input focus ring enhancement */
#globalSearch:focus {
    ring-width: 2px;
    ring-color: #3b82f6;
    ring-opacity: 0.2;
}

/* Icon transitions */
.fas.fa-search {
    transition: all 0.2s ease;
}

#globalSearch:focus ~ .fas.fa-search {
    color: #3b82f6;
    transform: scale(1.1);
}

/* Button loading state enhancement */
#refreshTable.loading {
    opacity: 0.7;
    pointer-events: none;
}

#refreshTable.loading i {
    animation: spin 1s linear infinite;
}

/* Enhanced focus states */
button:focus,
select:focus,
input:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

/* Smooth transitions for all interactive elements */
button,
select,
input,
a {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Modal Styles */
#addPatientModal {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

#modalContent {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Modal form enhancements */
#addPatientForm input:focus,
#addPatientForm select:focus,
#addPatientForm textarea:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Form validation styles */
#addPatientForm input.border-red-500,
#addPatientForm select.border-red-500,
#addPatientForm textarea.border-red-500 {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Modal section headers */
#addPatientForm h4 {
    position: relative;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

#addPatientForm h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 2rem;
    height: 2px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    border-radius: 1px;
}

/* Modal scrollbar styling */
#addPatientModal .overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

#addPatientModal .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

#addPatientModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

#addPatientModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Ensure modal body can scroll properly */
#addPatientModal .flex-1 {
    min-height: 0; /* Important for flex scrolling */
}

/* Modal content height constraints */
#modalContent {
    max-height: 90vh;
    min-height: 400px;
}

/* Ensure form content doesn't overflow */
#addPatientForm {
    min-height: 100%;
}

/* Enhanced Chart Button Animations */
@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

@keyframes shine {
    0% {
        transform: translateX(-100%) skewX(-15deg);
    }
    100% {
        transform: translateX(200%) skewX(-15deg);
    }
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0,0,0);
    }
    40%, 43% {
        transform: translate3d(0, -8px, 0);
    }
    70% {
        transform: translate3d(0, -4px, 0);
    }
    90% {
        transform: translate3d(0, -2px, 0);
    }
}

.animate-shimmer {
    animation: shimmer 1.5s ease-in-out infinite;
}

.animate-shine {
    animation: shine 0.8s ease-in-out;
}

.animate-bounce {
    animation: bounce 1s ease-in-out;
}

/* Enhanced button hover effects */
.group:hover .group-hover\\:animate-shimmer {
    animation: shimmer 1.5s ease-in-out infinite;
}

.group:hover .group-hover\\:animate-shine {
    animation: shine 0.8s ease-in-out;
}

.group:hover .group-hover\\:animate-bounce {
    animation: bounce 1s ease-in-out;
}

/* Button glow effect on hover */
.group:hover {
    box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.4), 0 10px 20px -5px rgba(59, 130, 246, 0.2);
}

/* Enhanced focus states */
.group:focus {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3), 0 20px 40px -10px rgba(59, 130, 246, 0.4);
}

/* Status indicator enhancements */
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

/* Ensure consistent height for all search section elements */
#globalSearch,
#pageLength,
#refreshTable,
.flex-shrink-0 a {
    height: 2.5rem; /* 40px - consistent height */
    box-sizing: border-box;
}

/* Page length selector container height */
.flex.items-center.gap-2 > div:first-child {
    height: 2.5rem; /* Match other elements */
}

/* Ensure search controls are always visible */
.bg-gradient-to-r {
    position: relative;
    z-index: 10;
}

/* Table header improvements */
th[data-column] {
    user-select: none;
}

th[data-column]:hover {
    background-color: #f8fafc;
}

/* Loading states */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Enhanced Responsive improvements */
@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
    
    .bg-gradient-to-r {
        padding: 0.75rem;
    }
    
    .text-4xl {
        font-size: 2rem;
    }
    
    /* Enhanced mobile search section */
    .bg-gradient-to-r.from-slate-50.to-blue-50 {
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .flex.flex-col.lg\\:flex-row {
        gap: 0.75rem;
    }
    
    .flex.flex-col.sm\\:flex-row {
        gap: 0.5rem;
    }
    
    /* Mobile search input */
    #globalSearch {
        min-width: 200px;
        font-size: 16px; /* Prevent zoom on iOS */
        height: 2.5rem; /* Consistent height on mobile */
        padding: 0.75rem 0.75rem 0.75rem 2.5rem;
    }
    
    /* Mobile controls */
    .flex.items-center.gap-2 {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .flex.items-center.gap-2 > div {
        min-width: auto;
    }
    
    /* Mobile page length selector */
    .flex.items-center.gap-2 > div:first-child {
        padding: 0.5rem;
        font-size: 0.75rem;
        height: 2.5rem; /* Match search input height */
    }
    
    /* Mobile refresh button */
    #refreshTable {
        padding: 0.5rem;
        font-size: 0.75rem;
        height: 2.5rem; /* Match search input height */
    }
    
    /* Mobile add patient button */
    .flex-shrink-0 a {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        height: 2.5rem; /* Match search input height */
    }
    
    /* Mobile chart button enhancements */
    .group {
        padding: 0.75rem 1rem;
    }
    
    .group .w-8.h-8 {
        width: 1.5rem;
        height: 1.5rem;
    }
    
    .group .text-sm {
        font-size: 0.75rem;
    }
    
    .group .text-xs {
        font-size: 0.625rem;
    }
    
    /* Make table more mobile-friendly */
    .min-w-full {
        min-width: 700px;
    }
    
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .py-4 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    
    /* Ensure action buttons are visible on mobile */
    td:last-child {
        min-width: 80px;
        max-width: 100px;
    }
    
    /* Compact button for mobile */
    button {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
}

/* Extra small screens */
@media (max-width: 480px) {
    .bg-gradient-to-r.from-slate-50.to-blue-50 {
        padding: 0.5rem;
    }
    
    .flex.flex-col.lg\\:flex-row {
        gap: 0.5rem;
    }
    
    #globalSearch {
        min-width: 150px;
        padding: 0.625rem 0.625rem 0.625rem 2.25rem;
        font-size: 14px;
        height: 2.25rem; /* Slightly smaller for extra small screens */
    }
    
    .flex.items-center.gap-2 {
        flex-direction: column;
        align-items: stretch;
        gap: 0.375rem;
    }
    
    .flex.items-center.gap-2 > div {
        width: 100%;
        justify-content: center;
    }
    
    .flex-shrink-0 {
        width: 100%;
    }
    
    .flex-shrink-0 a {
        width: 100%;
        justify-content: center;
    }
    
    /* Modal mobile improvements */
    #addPatientModal {
        padding: 0.5rem;
    }
    
    #modalContent {
        max-height: 95vh;
        min-height: 300px;
    }
    
    #addPatientModal .flex-1 {
        -webkit-overflow-scrolling: touch;
    }
}

/* Ensure search controls are always visible */
@media (max-width: 640px) {
    .bg-gradient-to-r {
        padding: 0.75rem;
    }
    
    .flex-col.lg\\:flex-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .flex-col.lg\\:flex-row > div {
        width: 100%;
    }
}
</style>
</div>
</div>
<?= $this->endSection() ?>
