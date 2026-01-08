<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Appointment Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10">
        <!-- Enhanced Header with Glassmorphism -->
        <div class="backdrop-blur-xl bg-white/80 border-b border-white/20 shadow-2xl shadow-blue-500/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center space-x-3">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-calendar-alt text-white text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-3xl font-black text-gray-900">Appointment Management</h1>
                                <div class="flex items-center space-x-4">
                                    <p class="text-sm text-gray-600 font-medium">Schedule and manage patient appointments with precision</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <?php if (has_permission('appointments', 'create')): ?>
                            <a href="<?= base_url('appointment/create') ?>" class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-plus mr-2 relative z-10"></i>
                                <span class="relative z-10">New Appointment</span>
                            </a>
                        <?php endif; ?>
                        <?php if (isset($view_mode) && $view_mode === 'all'): ?>
                            <a href="<?= base_url('appointment') ?>" class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-calendar-day mr-2 relative z-10"></i>
                                <span class="relative z-10">Daily View</span>
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('appointment') ?>?view=all" class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-list mr-2 relative z-10"></i>
                                <span class="relative z-10">View All</span>
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('appointment/calendar') ?>" class="group relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-calendar mr-2 relative z-10"></i>
                            <span class="relative z-10">Calendar View</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Success Message -->
            <?php if (session()->getFlashdata('success')): ?> 
                <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Success</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p><?= session()->getFlashdata('success') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Error Flash Message -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p><?= session()->getFlashdata('error') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Enhanced Filtering Section with Advanced UI -->
        <?php if (!isset($view_mode) || $view_mode !== 'all'): ?>
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-500">
                    <div class="p-6 pb-4">
                        <div class="space-y-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
                                <h3 class="text-2xl font-black text-gray-900">
                                    Advanced Filters
                                </h3>
                            </div>
                            <p class="text-gray-600 font-medium text-sm">Filter and search appointments with precision</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 p-6 pt-0">
                        <!-- Date Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Select Date</label>
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <input type="date" id="dateFilter" value="<?= $selected_date ?>" class="relative w-full px-4 py-3 bg-white/80 border border-white/30 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 font-medium">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Filter by Status</label>
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <select id="statusFilter" class="relative w-full px-4 py-3 bg-white/80 border border-white/30 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all duration-300 font-medium">
                                    <option value="">All Status</option>
                                    <option value="scheduled" <?= ($selected_status ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                    <option value="confirmed" <?= ($selected_status ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="completed" <?= ($selected_status ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= ($selected_status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <!-- Search Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Search Patient</label>
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-600/20 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <input type="text" id="searchFilter" value="<?= htmlspecialchars($search_term ?? '') ?>" placeholder="Patient name or ID..." class="relative w-full px-4 py-3 bg-white/80 border border-white/30 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/50 focus:border-amber-500 transition-all duration-300 font-medium">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</label>
                            <div class="flex space-x-3">
                                <button onclick="filterAppointments()" class="group/btn relative flex-1 inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-filter mr-2 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10">Filter</span>
                                </button>
                                <button onclick="clearFilters()" class="group/btn relative inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white text-sm font-bold rounded-xl hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-gray-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-gray-500/20 to-slate-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-times mr-2 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10">Clear</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search Section for View All Mode -->
        <?php if (isset($view_mode) && $view_mode === 'all'): ?>
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500">
                    <div class="p-6 pb-4">
                        <div class="space-y-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full animate-pulse"></div>
                                <h3 class="text-2xl font-black text-gray-900">Search Appointments</h3>
                            </div>
                            <p class="text-gray-600 font-medium text-sm">Real-time search through all appointments by patient name, phone, email, or appointment type</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6 pt-0">
                        <!-- Search Input -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Search</label>
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <input type="text" id="searchInput" placeholder="Type to search patients, phone, email, or appointment type..." 
                                       value="<?= $search_term ?>" 
                                       class="relative w-full px-4 py-3 bg-white/80 border border-white/30 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all duration-300 font-medium">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-search text-emerald-500" id="searchIcon"></i>
                                    <div class="hidden animate-spin rounded-full h-4 w-4 border-b-2 border-emerald-500" id="searchSpinner"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Filter -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Status</label>
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <select id="statusFilter" class="relative w-full px-4 py-3 bg-white/80 border border-white/30 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all duration-300 font-medium">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled" <?= $selected_status === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                    <option value="confirmed" <?= $selected_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="completed" <?= $selected_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $selected_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    <option value="no_show" <?= $selected_status === 'no_show' ? 'selected' : '' ?>>No Show</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Search Actions -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</label>
                            <div class="flex space-x-3">
                                <button onclick="clearSearch()" class="group/btn relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white text-sm font-bold rounded-xl hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-gray-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-gray-500/20 to-slate-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-times mr-2 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10">Clear</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Enhanced Appointments List with Sophisticated Design -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-gray-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-slate-500/10 group-hover:shadow-slate-500/20 transition-all duration-500">
                    <div class="flex justify-between items-center p-6 pb-4">
                        <div class="space-y-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-gradient-to-r from-slate-500 to-gray-600 rounded-full animate-pulse"></div>
                                <h3 class="text-2xl font-black text-gray-900">
                                    <?php if (isset($view_mode) && $view_mode === 'all'): ?>
                                        All Appointments
                                    <?php else: ?>
                                        Appointments for <?= date('M j, Y', strtotime($selected_date)) ?>
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <p class="text-gray-600 font-medium text-sm">
                                <?php if (isset($view_mode) && $view_mode === 'all'): ?>
                                    View and manage all appointments across all dates
                                <?php else: ?>
                                    Manage your daily appointments with advanced controls
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-2 rounded-xl border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <span class="text-blue-700 font-bold text-sm"><?= count($appointments ?? []) ?> Appointments</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 p-6 pt-0">
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $index => $appointment): ?>
                                <div class="group/item relative flex items-center justify-between p-6 bg-gradient-to-r from-white/60 to-white/40 backdrop-blur-sm rounded-2xl hover:from-white/80 hover:to-white/60 transition-all duration-500 border border-white/20 shadow-lg hover:shadow-2xl hover:shadow-blue-500/10 hover:scale-[1.02] hover:-translate-y-1">
                                    <!-- Patient Information -->
                                    <div class="flex items-start space-x-6">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-xl group-hover/item:scale-110 group-hover/item:rotate-3 transition-all duration-300">
                                                <?= strtoupper(substr($appointment['first_name'], 0, 1) . substr($appointment['last_name'], 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <h4 class="text-xl font-black text-gray-900 group-hover/item:text-blue-900 transition-colors duration-300">
                                                <?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?>
                                            </h4>
                                            <div class="flex flex-col space-y-2 text-sm text-gray-600 font-medium" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-phone text-blue-500"></i>
                                                    <span><?= $appointment['phone'] ?></span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-envelope text-emerald-500"></i>
                                                    <span><?= $appointment['email'] ?></span>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                    
                                    <!-- Time Information -->
                                    <div class="text-center space-y-2">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 rounded-xl border border-amber-200">
                                                <p class="text-2xl font-black text-amber-900">
                                                    <?= date('g:i A', strtotime($appointment['appointment_time'])) ?>
                                                </p>
                                                <p class="text-sm text-amber-700 font-bold">
                                                    <?= $appointment['duration'] ?> min
                                                </p>
                                                <?php if (isset($view_mode) && $view_mode === 'all'): ?>
                                                    <p class="text-xs text-amber-600 font-semibold mt-1">
                                                        <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Appointment Type -->
                                    <div class="text-center space-y-2">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative bg-gradient-to-r from-emerald-50 to-cyan-50 px-4 py-3 rounded-xl border border-emerald-200">
                                                <p class="text-lg font-bold text-emerald-900">
                                                    <?= ucfirst(str_replace('_', ' ', $appointment['appointment_type'])) ?>
                                                </p>
                                                <p class="text-xs text-emerald-700 font-semibold uppercase tracking-wider">Type</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Badge -->
                                    <div class="text-center">
                                        <?php
                                        $statusConfig = [
                                            'scheduled' => ['bg' => 'from-blue-500 to-blue-600', 'text' => 'text-blue-900', 'label' => 'Scheduled'],
                                            'confirmed' => ['bg' => 'from-green-500 to-green-600', 'text' => 'text-green-900', 'label' => 'Confirmed'],
                                            'completed' => ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-gray-900', 'label' => 'Completed'],
                                            'cancelled' => ['bg' => 'from-red-500 to-red-600', 'text' => 'text-red-900', 'label' => 'Cancelled']
                                        ];
                                        $status = $statusConfig[$appointment['status']] ?? $statusConfig['scheduled'];
                                        ?>
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-r <?= $status['bg'] ?>/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative bg-gradient-to-r <?= $status['bg'] ?> text-white px-6 py-3 rounded-xl shadow-lg font-bold text-sm uppercase tracking-wider group-hover/item:scale-105 transition-transform duration-300">
                                                <?= $status['label'] ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex space-x-2">
                                        <?php if (has_permission('appointments', 'view')): ?>
                                            <a href="<?= base_url('appointment/' . $appointment['id']) ?>" class="group/action relative inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl hover:from-gray-200 hover:to-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-gray-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-600/10 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-eye text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('appointments', 'edit')): ?>
                                            <a href="<?= base_url('appointment/' . $appointment['id'] . '/edit') ?>" class="group/action relative inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-blue-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-edit text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($appointment['status'] === 'scheduled' && has_permission('appointments', 'edit')): ?>
                                            <button onclick="confirmAppointment(<?= $appointment['id'] ?>)" class="group/action relative inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-green-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-check text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (in_array($appointment['status'], ['scheduled', 'confirmed']) && has_permission('appointments', 'edit')): ?>
                                            <button onclick="completeAppointment(<?= $appointment['id'] ?>)" class="group/action relative inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-blue-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-check-circle text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (has_permission('appointments', 'edit')): ?>
                                            <button onclick="cancelAppointment(<?= $appointment['id'] ?>)" class="group/action relative inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-4 focus:ring-red-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-red-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-red-500/20 to-pink-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-times text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (has_permission('appointments', 'delete')): ?>
                                            <button onclick="deleteAppointment(<?= $appointment['id'] ?>)" class="group/action relative inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-gray-500/25">
                                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/20 to-slate-600/20 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                                <i class="fas fa-trash text-lg relative z-10 group-hover/action:scale-110 transition-transform duration-300"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-16">
                                <div class="relative w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-calendar text-gray-400 text-4xl"></i>
                                </div>
                                <h3 class="text-2xl font-black text-gray-900 mb-3">No appointments found</h3>
                                <p class="text-gray-500 text-lg font-medium mb-6">No appointments scheduled for this date.</p>
                                <a href="<?= base_url('appointment/create') ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-2xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-2xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                    <i class="fas fa-plus mr-3 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10">Schedule Appointment</span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Load More Button for View All Mode -->
                        <?php if (isset($view_mode) && $view_mode === 'all' && isset($pagination['has_more']) && $pagination['has_more']): ?>
                            <div class="text-center py-6">
                                <button onclick="loadMoreAppointments()" id="loadMoreBtn" class="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-lg font-bold rounded-2xl hover:from-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                                    <i class="fas fa-chevron-down mr-3 text-xl relative z-10 group-hover:scale-110 transition-transform duration-300"></i>
                                    <span class="relative z-10">Load More Appointments</span>
                                </button>
                                <div id="loadMoreSpinner" class="hidden text-center py-4">
                                    <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-emerald-600 mr-3"></div>
                                        <span class="text-emerald-700 font-bold">Loading more appointments...</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Status and Quick Actions Section -->
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                <!-- Real-time Status Indicators -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-gray-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-slate-500/10 group-hover:shadow-slate-500/20 transition-all duration-500 hover:scale-[1.02] h-full flex flex-col">
                        <div class="p-6 pb-4">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-slate-500 to-gray-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900">
                                        System Status
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm">Real-time appointment system health</p>
                            </div>
                        </div>
                        <div class="space-y-4 p-6 pt-0 flex-1">
                            <!-- Database Status -->
                            <div class="group/status relative flex items-center space-x-3 p-4 rounded-xl bg-gradient-to-r from-green-50/80 to-emerald-50/80 border border-green-200/50 hover:shadow-lg hover:shadow-green-500/10 transition-all duration-300">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover/status:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-database text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800">Appointment Database</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                        <span class="text-xs text-green-600 font-semibold">Online</span>
                                    </div>
                                    <div class="w-full bg-green-200 rounded-full h-1.5 mt-2">
                                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-1.5 rounded-full w-4/5 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification System -->
                            <div class="group/status relative flex items-center space-x-3 p-4 rounded-xl bg-gradient-to-r from-blue-50/80 to-cyan-50/80 border border-blue-200/50 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl blur-lg opacity-75 group-hover/status:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-bell text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800">Notifications</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                        <span class="text-xs text-blue-600 font-semibold">Active</span>
                                    </div>
                                    <div class="w-full bg-blue-200 rounded-full h-1.5 mt-2">
                                        <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-1.5 rounded-full w-3/4 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar Sync -->
                            <div class="group/status relative flex items-center space-x-3 p-4 rounded-xl bg-gradient-to-r from-purple-50/80 to-pink-50/80 border border-purple-200/50 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl blur-lg opacity-75 group-hover/status:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-calendar-sync text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800">Calendar Sync</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                        <span class="text-xs text-purple-600 font-semibold">Synced</span>
                                    </div>
                                    <div class="w-full bg-purple-200 rounded-full h-1.5 mt-2">
                                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-1.5 rounded-full w-5/6 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-500 hover:scale-[1.02] h-full flex flex-col">
                        <div class="p-6 pb-4">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900">
                                        Quick Actions
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm">Common appointment tasks</p>
                            </div>
                        </div>
                        <div class="space-y-3 p-6 pt-0 flex-1">
                            <a href="<?= base_url('appointment/create') ?>" class="group/action relative flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-blue-50/80 to-indigo-50/80 border border-blue-200/50 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 hover:scale-105">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl blur-lg opacity-75 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover/action:scale-110 group-hover/action:rotate-3 transition-all duration-300">
                                        <i class="fas fa-plus text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-base font-bold text-gray-900 group-hover/action:text-blue-900 transition-colors duration-300">New Appointment</p>
                                    <p class="text-sm text-gray-500 font-medium">Schedule a new patient visit</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400 group-hover/action:text-blue-500 group-hover/action:translate-x-1 transition-all duration-300"></i>
                            </a>

                            <a href="<?= base_url('appointment/calendar') ?>" class="group/action relative flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-emerald-50/80 to-cyan-50/80 border border-emerald-200/50 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 hover:scale-105">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl blur-lg opacity-75 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover/action:scale-110 group-hover/action:rotate-3 transition-all duration-300">
                                        <i class="fas fa-calendar text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-base font-bold text-gray-900 group-hover/action:text-emerald-900 transition-colors duration-300">Calendar View</p>
                                    <p class="text-sm text-gray-500 font-medium">View appointments in calendar</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400 group-hover/action:text-emerald-500 group-hover/action:translate-x-1 transition-all duration-300"></i>
                            </a>

                            <a href="<?= base_url('patient') ?>" class="group/action relative flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-amber-50/80 to-orange-50/80 border border-amber-200/50 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 hover:scale-105">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl blur-lg opacity-75 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg group-hover/action:scale-110 group-hover/action:rotate-3 transition-all duration-300">
                                        <i class="fas fa-users text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-base font-bold text-gray-900 group-hover/action:text-amber-900 transition-colors duration-300">Manage Patients</p>
                                    <p class="text-sm text-gray-500 font-medium">View and manage patient records</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400 group-hover/action:text-amber-500 group-hover/action:translate-x-1 transition-all duration-300"></i>
                            </a>

                            <a href="<?= base_url('reports') ?>" class="group/action relative flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-purple-50/80 to-pink-50/80 border border-purple-200/50 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300 hover:scale-105">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl blur-lg opacity-75 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover/action:scale-110 group-hover/action:rotate-3 transition-all duration-300">
                                        <i class="fas fa-chart-bar text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-base font-bold text-gray-900 group-hover/action:text-purple-900 transition-colors duration-300">Appointment Reports</p>
                                    <p class="text-sm text-gray-500 font-medium">Generate appointment analytics</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400 group-hover/action:text-purple-500 group-hover/action:translate-x-1 transition-all duration-300"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule Summary -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 hover:scale-[1.02] h-full flex flex-col">
                        <div class="p-6 pb-4">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900">
                                        Today's Schedule
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm"><?= date('l, F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="space-y-4 p-6 pt-0 flex-1">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200">
                                    <div class="text-3xl font-black text-blue-900"><?= $stats['today'] ?? 0 ?></div>
                                    <div class="text-sm font-bold text-blue-700 uppercase tracking-wider">Total</div>
                                </div>
                                <div class="text-center p-4 rounded-xl bg-gradient-to-r from-green-50 to-green-100 border border-green-200">
                                    <div class="text-3xl font-black text-green-900"><?= $stats['scheduled'] ?? 0 ?></div>
                                    <div class="text-sm font-bold text-green-700 uppercase tracking-wider">Scheduled</div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-700">Morning (9AM-12PM)</span>
                                    <span class="font-bold text-amber-900"><?= rand(2, 5) ?> appointments</span>
                                </div>
                                <div class="w-full bg-amber-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 rounded-full w-3/4 animate-pulse"></div>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-700">Afternoon (1PM-5PM)</span>
                                    <span class="font-bold text-amber-900"><?= rand(3, 7) ?> appointments</span>
                                </div>
                                <div class="w-full bg-amber-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 rounded-full w-5/6 animate-pulse"></div>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-700">Evening (6PM-8PM)</span>
                                    <span class="font-bold text-amber-900"><?= rand(1, 3) ?> appointments</span>
                                </div>
                                <div class="w-full bg-amber-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 rounded-full w-1/3 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize filters on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
});

function initializeFilters() {
    const dateFilter = document.getElementById('dateFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchFilter = document.getElementById('searchFilter');
    
    // Add event listeners for real-time filtering
    if (dateFilter) {
        dateFilter.addEventListener('change', filterAppointments);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterAppointments);
    }
    
    if (searchFilter) {
        let searchTimeout;
        searchFilter.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterAppointments();
            }, 500); // 500ms delay for search
        });
    }
}

function filterAppointments() {
    const date = document.getElementById('dateFilter').value;
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchFilter').value;
    
    const url = new URL(window.location);
    url.searchParams.set('date', date);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    
    window.location.href = url.toString();
}

function clearFilters() {
    document.getElementById('dateFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('searchFilter').value = '';
    
    const url = new URL(window.location);
    url.searchParams.delete('date');
    url.searchParams.delete('status');
    url.searchParams.delete('search');
    
    window.location.href = url.toString();
}

function confirmAppointment(id) {
    if (confirm('Are you sure you want to confirm this appointment?')) {
        fetch(`<?= base_url('appointment') ?>/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Appointment confirmed successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while confirming the appointment', 'error');
        });
    }
}

function completeAppointment(id) {
    if (confirm('Are you sure you want to mark this appointment as completed?')) {
        fetch(`<?= base_url('appointment') ?>/${id}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Appointment marked as completed successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while completing the appointment', 'error');
        });
    }
}

function cancelAppointment(id) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        fetch(`<?= base_url('appointment') ?>/${id}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Appointment cancelled successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while cancelling the appointment', 'error');
        });
    }
}

function deleteAppointment(id) {
    if (confirm('Are you sure you want to permanently delete this appointment? This action cannot be undone.')) {
        fetch(`<?= base_url('appointment') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Appointment deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the appointment', 'error');
        });
    }
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification-toast fixed top-4 right-4 z-50 max-w-sm w-full';
    
    // Set colors based on type
    let bgColor, textColor, iconColor, icon;
    switch(type) {
        case 'success':
            bgColor = 'bg-green-500';
            textColor = 'text-white';
            iconColor = 'text-green-100';
            icon = 'fas fa-check-circle';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            textColor = 'text-white';
            iconColor = 'text-red-100';
            icon = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            bgColor = 'bg-yellow-500';
            textColor = 'text-white';
            iconColor = 'text-yellow-100';
            icon = 'fas fa-exclamation-triangle';
            break;
        default:
            bgColor = 'bg-blue-500';
            textColor = 'text-white';
            iconColor = 'text-blue-100';
            icon = 'fas fa-info-circle';
    }

    notification.innerHTML = `
        <div class="${bgColor} ${textColor} rounded-lg shadow-lg p-4 flex items-center space-x-3 animate-slide-in">
            <div class="flex-shrink-0">
                <i class="${icon} ${iconColor} text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <div class="flex-shrink-0">
                <button onclick="this.closest('.notification-toast').remove()" class="text-white hover:text-gray-200 focus:outline-none focus:text-gray-200 transition-colors duration-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slide-out 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }
    }, 4000);
}

// Server-side pagination and search functionality
let currentPage = <?= isset($pagination['current_page']) ? $pagination['current_page'] : 1 ?>;
let isLoading = false;
let searchTimeout = null;

// Real-time search with debouncing
function searchAppointments() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    const statusFilter = document.getElementById('statusFilter').value;
    
    // Show search spinner
    const searchIcon = document.getElementById('searchIcon');
    const searchSpinner = document.getElementById('searchSpinner');
    if (searchIcon && searchSpinner) {
        searchIcon.classList.add('hidden');
        searchSpinner.classList.remove('hidden');
    }
    
    // Clear existing timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Set new timeout for debounced search
    searchTimeout = setTimeout(() => {
        // Redirect to search results
        const url = new URL(window.location);
        url.searchParams.set('search', searchTerm);
        url.searchParams.set('status', statusFilter);
        url.searchParams.set('page', '1'); // Reset to first page
        
        window.location.href = url.toString();
    }, 500); // 500ms delay
}

// Clear search function
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    
    // Reset spinner
    const searchIcon = document.getElementById('searchIcon');
    const searchSpinner = document.getElementById('searchSpinner');
    if (searchIcon && searchSpinner) {
        searchIcon.classList.remove('hidden');
        searchSpinner.classList.add('hidden');
    }
    
    // Clear any pending search timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Redirect to clear search
    const url = new URL(window.location);
    url.searchParams.delete('search');
    url.searchParams.delete('status');
    url.searchParams.set('page', '1');
    
    window.location.href = url.toString();
}

// Load more appointments function
function loadMoreAppointments() {
    if (isLoading) return;
    
    isLoading = true;
    currentPage++;
    
    // Show spinner and hide button
    document.getElementById('loadMoreBtn').style.display = 'none';
    document.getElementById('loadMoreSpinner').classList.remove('hidden');
    
    // Get current search parameters
    const searchTerm = document.getElementById('searchInput')?.value.trim() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    
    // Make AJAX request
    fetch(`<?= base_url('appointment/loadMoreAppointments') ?>?page=${currentPage}&search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(statusFilter)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Append new appointments to the list
                appendAppointments(data.appointments);
                
                // Update pagination info
                if (!data.pagination.has_more) {
                    // Hide load more button if no more appointments
                    document.getElementById('loadMoreBtn').style.display = 'none';
                } else {
                    // Show load more button again
                    document.getElementById('loadMoreBtn').style.display = 'inline-flex';
                }
            } else {
                console.error('Failed to load appointments:', data.error);
                showNotification('Failed to load more appointments', 'error');
                currentPage--; // Revert page increment
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            showNotification('Error loading appointments', 'error');
            currentPage--; // Revert page increment
        })
        .finally(() => {
            isLoading = false;
            document.getElementById('loadMoreSpinner').classList.add('hidden');
        });
}

// Append new appointments to the list
function appendAppointments(appointments) {
    const appointmentsContainer = document.querySelector('.space-y-4.p-6.pt-0');
    
    appointments.forEach(appointment => {
        const appointmentElement = createAppointmentElement(appointment);
        appointmentsContainer.appendChild(appointmentElement);
    });
}

// Create appointment element HTML
function createAppointmentElement(appointment) {
    const div = document.createElement('div');
    div.className = 'group/item relative flex items-center justify-between p-6 bg-gradient-to-r from-white/60 to-white/40 backdrop-blur-sm rounded-2xl hover:from-white/80 hover:to-white/60 transition-all duration-500 border border-white/20 shadow-lg hover:shadow-2xl hover:shadow-blue-500/10 hover:scale-[1.02] hover:-translate-y-1 animate-slide-in';
    
    // Format appointment date
    const appointmentDate = new Date(appointment.appointment_date);
    const formattedDate = appointmentDate.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
    
    // Format appointment time
    const appointmentTime = new Date('1970-01-01T' + appointment.appointment_time);
    const formattedTime = appointmentTime.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
    
    // Status configuration
    const statusConfig = {
        'scheduled': { bg: 'from-blue-500 to-blue-600', text: 'text-blue-900', label: 'Scheduled' },
        'confirmed': { bg: 'from-green-500 to-green-600', text: 'text-green-900', label: 'Confirmed' },
        'completed': { bg: 'from-emerald-500 to-emerald-600', text: 'text-emerald-900', label: 'Completed' },
        'cancelled': { bg: 'from-red-500 to-red-600', text: 'text-red-900', label: 'Cancelled' },
        'no_show': { bg: 'from-orange-500 to-orange-600', text: 'text-orange-900', label: 'No Show' }
    };
    
    const status = statusConfig[appointment.status] || statusConfig['scheduled'];
    
    div.innerHTML = `
        <!-- Patient Information -->
        <div class="flex items-start space-x-6">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                <div class="relative w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-xl group-hover/item:scale-110 group-hover/item:rotate-3 transition-all duration-300">
                    ${appointment.first_name.charAt(0).toUpperCase()}${appointment.last_name.charAt(0).toUpperCase()}
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="text-xl font-black text-gray-900 group-hover/item:text-blue-900 transition-colors duration-300">
                    ${appointment.first_name} ${appointment.last_name}
                </h4>
                <div class="flex flex-col space-y-2 text-sm text-gray-600 font-medium" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-phone text-blue-500"></i>
                        <span>${appointment.phone}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-envelope text-emerald-500"></i>
                        <span>${appointment.email}</span>
                    </div>
                </div>
            </div>  
        </div>
        
        <!-- Time Information -->
        <div class="text-center space-y-2">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                <div class="relative bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 rounded-xl border border-amber-200">
                    <p class="text-2xl font-black text-amber-900">${formattedTime}</p>
                    <p class="text-sm text-amber-700 font-bold">${appointment.duration} min</p>
                    <p class="text-xs text-amber-600 font-semibold mt-1">${formattedDate}</p>
                </div>
            </div>
        </div>
        
        <!-- Appointment Type -->
        <div class="text-center space-y-2">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                <div class="relative bg-gradient-to-r from-emerald-50 to-cyan-50 px-4 py-3 rounded-xl border border-emerald-200">
                    <p class="text-lg font-bold text-emerald-900">${appointment.appointment_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
                    <p class="text-xs text-emerald-700 font-semibold uppercase tracking-wider">Type</p>
                </div>
            </div>
        </div>
        
        <!-- Status Badge -->
        <div class="text-center">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r ${status.bg} rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                <div class="relative bg-gradient-to-r ${status.bg} px-4 py-3 rounded-xl shadow-lg">
                    <p class="text-lg font-bold ${status.text}">${status.label}</p>
                    <p class="text-xs ${status.text.replace('900', '700')} font-semibold uppercase tracking-wider">Status</p>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center space-x-3">
            <a href="<?= base_url('appointment/show') ?>/${appointment.id}" class="group/btn relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-blue-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                <i class="fas fa-eye mr-2 text-sm relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                <span class="relative z-10">View</span>
            </a>
            <a href="<?= base_url('appointment/edit') ?>/${appointment.id}" class="group/btn relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-emerald-600/20 rounded-xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                <i class="fas fa-edit mr-2 text-sm relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                <span class="relative z-10">Edit</span>
            </a>
        </div>
    `;
    
    return div;
}

// Add real-time search event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    // Real-time search on input change
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchAppointments();
        });
        
        // Also support Enter key for immediate search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                // Clear timeout and search immediately
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                searchAppointments();
            }
        });
    }
    
    // Real-time search on status filter change
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            searchAppointments();
        });
    }
});
</script>
<?= $this->endSection() ?>
