<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<!-- Enhanced Reports & Analytics with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-indigo-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-blue-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Simplified Page Header -->
    <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-chart-bar text-xl"></i>
                        </div>
        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
                            <p class="text-gray-600">Comprehensive reports and analytics for your dental practice</p>
                        </div>
        </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="exportReport('pdf')" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Export PDF
            </button>
                        <button onclick="exportReport('excel')" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-file-excel mr-2"></i>
                            Export Excel
            </button>
                    </div>
                </div>
        </div>
    </div>

        <!-- Enhanced Report Filters -->
    <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
            <div class="flex flex-col lg:flex-row lg:items-center space-y-4 lg:space-y-0 lg:space-x-6">
                <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Report Type</label>
                        <select id="reportType" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90 backdrop-blur-sm">
                        <option value="overview" <?= ($report_type ?? 'overview') === 'overview' ? 'selected' : '' ?>>Overview</option>
                        <option value="patients" <?= ($report_type ?? '') === 'patients' ? 'selected' : '' ?>>Patients</option>
                        <option value="examinations" <?= ($report_type ?? '') === 'examinations' ? 'selected' : '' ?>>Examinations</option>
                        <option value="appointments" <?= ($report_type ?? '') === 'appointments' ? 'selected' : '' ?>>Appointments</option>
                        <option value="finance" <?= ($report_type ?? '') === 'finance' ? 'selected' : '' ?>>Finance</option>
                        <option value="treatments" <?= ($report_type ?? '') === 'treatments' ? 'selected' : '' ?>>Treatments</option>
                    </select>
                </div>
                <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                        <select id="dateRange" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90 backdrop-blur-sm">
                        <option value="7" <?= ($date_range ?? '30') === '7' ? 'selected' : '' ?>>Last 7 days</option>
                        <option value="30" <?= ($date_range ?? '30') === '30' ? 'selected' : '' ?>>Last 30 days</option>
                        <option value="90" <?= ($date_range ?? '30') === '90' ? 'selected' : '' ?>>Last 90 days</option>
                        <option value="365" <?= ($date_range ?? '30') === '365' ? 'selected' : '' ?>>Last year</option>
                    </select>
                </div>
                <div class="lg:pt-6">
                        <button onclick="generateReport()" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Report -->
    <?php if (($report_type ?? 'overview') === 'overview'): ?>
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Summary Stats -->
                    <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Summary Statistics</h3>
                        <p class="text-sm text-gray-600">Key metrics for the selected period</p>
                    </div>
                <div class="space-y-4">
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg backdrop-blur-sm border border-blue-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-users"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900">Total Patients</p>
                                <p class="text-xs text-gray-500">All time</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= $reports['summary']['total_patients'] ?? 0 ?></p>
                                    <p class="text-xs text-green-600 font-semibold">+<?= $reports['summary']['new_patients'] ?? 0 ?> new</p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50/80 to-emerald-50/80 rounded-lg backdrop-blur-sm border border-green-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-stethoscope"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900">Examinations</p>
                                <p class="text-xs text-gray-500">This period</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= $reports['summary']['examinations_this_period'] ?? 0 ?></p>
                                    <p class="text-xs text-blue-600 font-semibold"><?= $reports['summary']['total_examinations'] ?? 0 ?> total</p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-50/80 to-amber-50/80 rounded-lg backdrop-blur-sm border border-yellow-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900">Appointments</p>
                                <p class="text-xs text-gray-500">This period</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= $reports['summary']['appointments_this_period'] ?? 0 ?></p>
                                    <p class="text-xs text-blue-600 font-semibold"><?= $reports['summary']['total_appointments'] ?? 0 ?> total</p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-purple-50/80 to-indigo-50/80 rounded-lg backdrop-blur-sm border border-purple-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900">Revenue</p>
                                <p class="text-xs text-gray-500">This period</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= formatCurrency($reports['summary']['revenue_this_period'] ?? 0) ?></p>
                                    <p class="text-xs text-blue-600 font-semibold"><?= formatCurrency($reports['summary']['total_revenue'] ?? 0) ?> total</p>
                    </div>
                </div>
                </div>
            </div>

                    <!-- Monthly Revenue Chart -->
                    <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Monthly Revenue</h3>
                    <p class="text-sm text-gray-600">Revenue trends over the past 6 months</p>
                </div>
                <div class="relative h-64">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
            </div>
        </div>

        <!-- Additional Charts -->
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Examination Types</h3>
                        <p class="text-sm text-gray-600">Distribution of examination types</p>
                    </div>
                    <div class="relative h-64">
                        <canvas id="examinationChart" width="400" height="200"></canvas>
                    </div>
                </div>

                    <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Appointment Status</h3>
                        <p class="text-sm text-gray-600">Current appointment status distribution</p>
                    </div>
                    <div class="relative h-64">
                        <canvas id="appointmentChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Other Report Types -->
    <?php if (($report_type ?? 'overview') !== 'overview'): ?>
        <div class="mb-8">
                <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?= ucfirst($report_type ?? 'Report') ?> Report</h3>
                        <p class="text-sm text-gray-600">Detailed analysis for the selected period (<?= $date_range ?? '30' ?> days)</p>
                    </div>
                    
                    <?php if ($report_type === 'patients'): ?>
                        <!-- Patients Report -->
                        <div class="space-y-6">
                            <!-- Patient Statistics -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg p-4 border border-blue-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Total Patients</p>
                                            <p class="text-2xl font-bold text-blue-900"><?= count($reports['patients'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-green-50/80 to-emerald-50/80 rounded-lg p-4 border border-green-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">New Patients</p>
                                            <p class="text-2xl font-bold text-green-900"><?= $reports['new_vs_returning']['new'] ?? 0 ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-50/80 to-indigo-50/80 rounded-lg p-4 border border-purple-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Returning Patients</p>
                                            <p class="text-2xl font-bold text-purple-900"><?= $reports['new_vs_returning']['returning'] ?? 0 ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Gender Distribution -->
                            <?php if (!empty($reports['gender_distribution'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Gender Distribution</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($reports['gender_distribution'] as $gender): ?>
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                        <span class="font-medium text-gray-700"><?= ucfirst($gender['gender']) ?></span>
                                        <span class="text-lg font-bold text-blue-600"><?= $gender['count'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Age Groups -->
                            <?php if (!empty($reports['age_groups'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Age Groups</h4>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                    <?php foreach ($reports['age_groups'] as $ageGroup => $count): ?>
                                    <div class="text-center p-3 bg-white rounded-lg border">
                                        <p class="text-sm text-gray-600"><?= $ageGroup ?> years</p>
                                        <p class="text-xl font-bold text-blue-600"><?= $count ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($report_type === 'examinations'): ?>
                        <!-- Examinations Report -->
                        <div class="space-y-6">
                            <!-- Examination Statistics -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gradient-to-r from-green-50/80 to-emerald-50/80 rounded-lg p-4 border border-green-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-stethoscope"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Total Examinations</p>
                                            <p class="text-2xl font-bold text-green-900"><?= count($reports['examinations'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg p-4 border border-blue-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-chart-pie"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Examination Types</p>
                                            <p class="text-2xl font-bold text-blue-900"><?= count($reports['examination_types'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Examination Types -->
                            <?php if (!empty($reports['examination_types'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Examination Types</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($reports['examination_types'] as $type): ?>
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                        <span class="font-medium text-gray-700"><?= ucfirst($type['examination_type']) ?></span>
                                        <span class="text-lg font-bold text-green-600"><?= $type['count'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($report_type === 'appointments'): ?>
                        <!-- Appointments Report -->
                        <div class="space-y-6">
                            <!-- Appointment Statistics -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gradient-to-r from-yellow-50/80 to-amber-50/80 rounded-lg p-4 border border-yellow-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Total Appointments</p>
                                            <p class="text-2xl font-bold text-yellow-900"><?= count($reports['appointments'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg p-4 border border-blue-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-chart-pie"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Status Types</p>
                                            <p class="text-2xl font-bold text-blue-900"><?= count($reports['status_distribution'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Appointment Status -->
                            <?php if (!empty($reports['status_distribution'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Appointment Status</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($reports['status_distribution'] as $status): ?>
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                        <span class="font-medium text-gray-700"><?= ucfirst($status['status']) ?></span>
                                        <span class="text-lg font-bold text-yellow-600"><?= $status['count'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($report_type === 'finance'): ?>
                        <!-- Finance Report -->
                        <div class="space-y-6">
                            <!-- Financial Statistics -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="bg-gradient-to-r from-green-50/80 to-emerald-50/80 rounded-lg p-4 border border-green-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Total Revenue</p>
                                            <p class="text-xl font-bold text-green-900"><?= formatCurrency($reports['monthly_revenue'][0]['total_amount'] ?? 0) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg p-4 border border-blue-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Total Transactions</p>
                                            <p class="text-xl font-bold text-blue-900"><?= count($reports['transactions'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-yellow-50/80 to-amber-50/80 rounded-lg p-4 border border-yellow-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Pending Payments</p>
                                            <p class="text-xl font-bold text-yellow-900"><?= count($reports['outstanding_payments'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-50/80 to-indigo-50/80 rounded-lg p-4 border border-purple-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Payment Methods</p>
                                            <p class="text-xl font-bold text-purple-900"><?= count($reports['payment_methods'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Methods -->
                            <?php if (!empty($reports['payment_methods'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($reports['payment_methods'] as $method): ?>
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                        <span class="font-medium text-gray-700"><?= ucfirst($method['payment_method']) ?></span>
                                        <span class="text-lg font-bold text-green-600"><?= formatCurrency($method['total_amount']) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($report_type === 'treatments'): ?>
                        <!-- Treatments Report -->
                        <div class="space-y-6">
                            <!-- Treatment Statistics -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gradient-to-r from-pink-50/80 to-rose-50/80 rounded-lg p-4 border border-pink-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-tooth"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Total Treatments</p>
                                            <p class="text-2xl font-bold text-pink-900"><?= count($reports['treatments'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg p-4 border border-blue-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-chart-pie"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Treatment Types</p>
                                            <p class="text-2xl font-bold text-blue-900"><?= count($reports['treatment_types'] ?? []) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Treatment Types -->
                            <?php if (!empty($reports['treatment_types'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Treatment Types</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($reports['treatment_types'] as $type): ?>
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                        <span class="font-medium text-gray-700"><?= ucfirst($type['treatment_type']) ?></span>
                                        <span class="text-lg font-bold text-pink-600"><?= $type['count'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Treatment Status -->
                            <?php if (!empty($reports['status_distribution'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Treatment Status</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($reports['status_distribution'] as $status): ?>
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                        <span class="font-medium text-gray-700"><?= ucfirst($status['status']) ?></span>
                                        <span class="text-lg font-bold text-pink-600"><?= $status['count'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php else: ?>
                        <!-- Default fallback -->
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-chart-bar text-blue-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Report Data</h3>
                    <p class="text-gray-500">This report type is under development. Please check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        </div>
</div>

<script>
function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    
    const url = new URL(window.location);
    url.searchParams.set('report_type', reportType);
    url.searchParams.set('date_range', dateRange);
    
    window.location.href = url.toString();
}

function exportReport(format) {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    
    // Construct the URL properly
    const exportUrl = '<?= base_url("reports/export") ?>' + 
        '?format=' + encodeURIComponent(format) + 
        '&type=' + encodeURIComponent(reportType) + 
        '&date_range=' + encodeURIComponent(dateRange);
    
    window.open(exportUrl, '_blank');
}

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart')?.getContext('2d');
if (revenueCtx) {
    // Get real revenue data from PHP
    const revenueData = <?= json_encode($reports['charts']['monthly_revenue'] ?? []) ?>;
    
    // Debug: Log the data to console
    console.log('Revenue Chart Data:', revenueData);
    
    // Extract labels and data
    const labels = revenueData.map(item => item.month || '');
    const data = revenueData.map(item => parseFloat(item.amount) || 0);
    
    // Debug: Log processed data
    console.log('Revenue Chart Labels:', labels);
    console.log('Revenue Chart Data:', data);
    
    // Check if we have valid data
    if (labels.length === 0 || data.every(val => val === 0)) {
        console.log('No revenue data available, showing empty chart');
        labels.length = 0;
        data.length = 0;
    }
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: data,
                borderColor: '#0284c7',
                backgroundColor: 'rgba(2, 132, 199, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

// Examination Chart
const examinationCtx = document.getElementById('examinationChart')?.getContext('2d');
if (examinationCtx) {
    // Get real examination data from PHP
    const examinationData = <?= json_encode($reports['charts']['examination_types'] ?? []) ?>;
    
    // Debug: Log the data to console
    console.log('Examination Chart Data:', examinationData);
    
    // Extract labels and data
    const labels = examinationData.map(item => item.examination_type || 'Unknown');
    const data = examinationData.map(item => parseInt(item.count) || 0);
    
    // Debug: Log processed data
    console.log('Examination Chart Labels:', labels);
    console.log('Examination Chart Data:', data);
    
    // Check if we have valid data
    if (labels.length === 0 || data.every(val => val === 0)) {
        console.log('No examination data available, showing empty chart');
        labels.length = 0;
        data.length = 0;
    }
    
    // Define colors for different examination types
    const colors = [
        '#0284c7', '#059669', '#d97706', '#dc2626', '#7c3aed',
        '#f59e0b', '#ef4444', '#6b7280', '#10b981', '#8b5cf6'
    ];
    
    new Chart(examinationCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, labels.length)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Appointment Chart
const appointmentCtx = document.getElementById('appointmentChart')?.getContext('2d');
if (appointmentCtx) {
    // Get real appointment data from PHP
    const appointmentData = <?= json_encode($reports['charts']['appointment_status'] ?? []) ?>;
    
    // Debug: Log the data to console
    console.log('Appointment Chart Data:', appointmentData);
    
    // Extract labels and data
    const labels = appointmentData.map(item => item.status || 'Unknown');
    const data = appointmentData.map(item => parseInt(item.count) || 0);
    
    // Debug: Log processed data
    console.log('Appointment Chart Labels:', labels);
    console.log('Appointment Chart Data:', data);
    
    // Check if we have valid data
    if (labels.length === 0 || data.every(val => val === 0)) {
        console.log('No appointment data available, showing empty chart');
        labels.length = 0;
        data.length = 0;
    }
    
    // Define colors for different appointment statuses
    const statusColors = {
        'scheduled': '#0284c7',
        'completed': '#059669',
        'cancelled': '#dc2626',
        'no_show': '#6b7280',
        'pending': '#f59e0b',
        'confirmed': '#10b981'
    };
    
    const colors = labels.map(status => statusColors[status.toLowerCase()] || '#6b7280');
    
    new Chart(appointmentCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Appointments',
                data: data,
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Utility function for currency formatting
function formatCurrency(value) {
    if (typeof value !== 'number') {
        value = parseFloat(value) || 0;
    }
    
    // Format as currency with 2 decimal places
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}
</script>
<?= $this->endSection() ?>
