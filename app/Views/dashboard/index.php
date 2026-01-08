<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Dashboard with Advanced Design -->
<div class="min-h-screen">
    <div class="container mx-auto px-4 py-6"> 
        <!-- Enhanced Page Header with Glassmorphism -->
       

        <!-- Enhanced Statistics Cards with Advanced Animations -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                <!-- Total Patients Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-purple-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-green-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                                <span class="text-emerald-700 font-bold text-xs">+12%</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Patients</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-blue-900">
                                    <?= $stats['total_patients'] ?? 0 ?>
                                </p>
                                <div class="w-12 h-1 bg-gradient-to-r from-blue-200 to-purple-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">vs last month</p>
                        </div>
                    </div>
                </div>

                <!-- Today's Examinations Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-cyan-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-stethoscope text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-cyan-100 px-4 py-2 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-calendar text-blue-600 text-sm"></i>
                                <span class="text-blue-700 font-bold text-sm">Today</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Today's Examinations</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-emerald-900">
                                    <?= $stats['today_examinations'] ?? 0 ?>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-emerald-200 to-cyan-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= formatDate(date('Y-m-d')) ?></p>
                </div>
            </div>
        </div>

        <!-- Today's Appointments Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-orange-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-6">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-calendar-alt text-2xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-4 py-2 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-clock text-amber-600 text-sm"></i>
                                <span class="text-amber-700 font-bold text-sm">Scheduled</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Today's Appointments</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-amber-900">
                                    <?= $stats['today_appointments'] ?? 0 ?>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Appointments</p>
                    </div>
                    </div>
                </div>

                <!-- Total Revenue Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-pink-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-6">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-chart-line text-2xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-green-100 px-4 py-2 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-emerald-600 text-sm"></i>
                                <span class="text-emerald-700 font-bold text-sm">+8.2%</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Revenue</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-4xl font-black text-purple-900">
                                    <?= formatCurrencyAbbreviated($stats['total_revenue'] ?? 0) ?>
                                </p>
                                <div class="w-16 h-1 bg-gradient-to-r from-purple-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-5/6 h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">vs last month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Charts Section with Advanced Styling -->
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                <!-- Revenue Chart -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex items-center justify-between p-6 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900">
                                        Monthly Revenue
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm">Revenue trends over the past 6 months</p>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-50 to-purple-50 px-3 py-2 rounded-xl border border-blue-200">
                                <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full"></div>
                                <span class="text-blue-700 font-bold text-xs">Revenue</span>
                            </div>
                        </div>
                        <div class="relative h-64 p-6 pt-0 flex-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-purple-50/50 rounded-2xl"></div>
                            <canvas id="revenueChart" width="400" height="200" class="relative z-10"></canvas>
                    </div>
                    </div>
                </div>

                <!-- Treatment Types Chart -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex items-center justify-between p-6 pb-6">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-2xl font-black text-gray-900">
                                        Treatment Distribution
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium">Breakdown of treatment types</p>
                            </div>
                            <div class="text-right bg-gradient-to-r from-emerald-50 to-cyan-50 px-6 py-4 rounded-2xl border border-emerald-200">
                                <p class="text-3xl font-black text-emerald-900">100%</p>
                                <p class="text-xs text-emerald-600 font-semibold uppercase tracking-wider">Total Treatments</p>
                            </div>
                        </div>
                        <div class="relative h-80 p-6 pt-0 flex-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 to-cyan-50/50 rounded-2xl"></div>
                            <canvas id="treatmentChart" width="400" height="200" class="relative z-10"></canvas>
                </div>
            </div>
        </div>
        </div>
    </div>

        <!-- Enhanced Activity Feeds with Sophisticated Visual Hierarchy -->
        <div class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
                <!-- Recent Patients -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex justify-between items-center p-4 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-lg font-black text-gray-900">
                                        Recent Patients
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-xs">Latest patient registrations</p>
                            </div>
                            <a href="<?= base_url('patient') ?>" class="group/link relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 text-blue-700 hover:text-blue-800 text-sm font-semibold rounded-2xl border border-blue-200 hover:border-blue-300 transition-all duration-200 hover:scale-105">
                                <span class="relative z-10">View All</span>
                                <i class="fas fa-arrow-right ml-2 text-xs relative z-10 group-hover/link:translate-x-1 transition-transform duration-300"></i>
                            </a>
            </div>
                        <div class="space-y-1 p-4 pt-0 flex-1">
                            <?php if (!empty($recent_patients)): ?>
                                <?php foreach ($recent_patients as $index => $patient): ?>
                                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="group/item relative flex items-center space-x-3 p-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-indigo-50/50 transition-all duration-200 border border-transparent hover:border-blue-200/50 hover:shadow-lg hover:shadow-blue-500/10 cursor-pointer">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover/item:scale-110 group-hover/item:rotate-3 transition-all duration-200">
                                                <i class="fas fa-user text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-1">
                                            <p class="text-sm font-bold text-gray-900 truncate group-hover/item:text-blue-900 transition-colors duration-300">
                                                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?>
                                            </p>
                                            <p class="text-xs text-gray-500 font-medium">ID: <?= $patient['patient_id'] ?></p>
                                        </div>
                                        <div class="text-right space-y-1">
                                            <span class="inline-block text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full font-semibold">
                                                <?= formatDate($patient['created_at']) ?>
                                            </span>
                                            <div class="flex items-center justify-end space-x-1">
                                                <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                                                <span class="text-xs text-green-600 font-semibold">Active</span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-16">
                                    <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-105 transition-transform duration-300">
                                        <i class="fas fa-users text-gray-400 text-3xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-base font-medium">No recent patients</p>
            </div>
                            <?php endif; ?>
        </div>
        </div>
    </div>

                <!-- Upcoming Appointments -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex justify-between items-center p-6 pb-6">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-2xl font-black text-gray-900">
                                        Upcoming Appointments
                                    </h3>
                </div>
                                <p class="text-gray-600 font-medium">Today's scheduled appointments</p>
                            </div>
                            <a href="<?= base_url('appointment') ?>" class="group/link relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 text-amber-700 hover:text-amber-800 text-sm font-semibold rounded-2xl border border-amber-200 hover:border-amber-300 transition-all duration-200 hover:scale-105">
                                <span class="relative z-10">View All</span>
                                <i class="fas fa-arrow-right ml-2 text-xs relative z-10 group-hover/link:translate-x-1 transition-transform duration-300"></i>
                            </a>
                        </div>
                        <div class="space-y-1 p-6 pt-0 flex-1">
                            <?php if (!empty($upcoming_appointments)): ?>
                                <?php foreach ($upcoming_appointments as $appointment): ?>
                                    <a href="<?= base_url('appointment/' . $appointment['id']) ?>" class="group/item relative flex items-center space-x-4 p-5 rounded-2xl hover:bg-gradient-to-r hover:from-amber-50/50 hover:to-orange-50/50 transition-all duration-200 border border-transparent hover:border-amber-200/50 hover:shadow-lg hover:shadow-amber-500/10 cursor-pointer">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg group-hover/item:scale-110 group-hover/item:rotate-3 transition-all duration-200">
                                                <i class="fas fa-calendar text-white text-xl"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-1">
                                            <p class="text-base font-bold text-gray-900 truncate group-hover/item:text-amber-900 transition-colors duration-300">
                                                <?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?>
                                            </p>
                                            <p class="text-sm text-gray-500 font-medium"><?= ucfirst(str_replace('_', ' ', $appointment['appointment_type'])) ?></p>
                                        </div>
                                        <div class="text-right space-y-1">
                                            <p class="text-sm font-bold text-gray-900"><?= formatDate($appointment['appointment_date']) ?></p>
                                            <p class="text-xs text-amber-600 font-semibold bg-amber-100 px-2 py-1 rounded-full"><?= formatTime($appointment['appointment_time']) ?></p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-16">
                                    <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-105 transition-transform duration-300">
                                        <i class="fas fa-calendar text-gray-400 text-3xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-base font-medium">No upcoming appointments</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Examinations -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex justify-between items-center p-6 pb-6">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-2xl font-black text-gray-900">
                                        Recent Examinations
                                    </h3>
                            </div>
                                <p class="text-gray-600 font-medium">Latest patient examinations</p>
                            </div>
                            <a href="<?= base_url('examination') ?>" class="group/link relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-50 to-cyan-50 hover:from-emerald-100 hover:to-cyan-100 text-emerald-700 hover:text-emerald-800 text-sm font-semibold rounded-2xl border border-emerald-200 hover:border-emerald-300 transition-all duration-200 hover:scale-105">
                                <span class="relative z-10">View All</span>
                                <i class="fas fa-arrow-right ml-2 text-xs relative z-10 group-hover/link:translate-x-1 transition-transform duration-300"></i>
                            </a>
                        </div>
                        <div class="space-y-1 p-6 pt-0 flex-1">
                            <?php if (!empty($recent_examinations)): ?>
                                <?php foreach ($recent_examinations as $examination): ?>
                                    <a href="<?= base_url('examination/' . $examination['id']) ?>" class="group/item relative flex items-center space-x-4 p-5 rounded-2xl hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-cyan-50/50 transition-all duration-200 border border-transparent hover:border-emerald-200/50 hover:shadow-lg hover:shadow-emerald-500/10 cursor-pointer">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg group-hover/item:scale-110 group-hover/item:rotate-3 transition-all duration-200">
                                                <i class="fas fa-stethoscope text-white text-xl"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-1">
                                            <p class="text-base font-bold text-gray-900 truncate group-hover/item:text-emerald-900 transition-colors duration-300">
                                                <?= $examination['first_name'] . ' ' . $examination['last_name'] ?>
                                            </p>
                                            <p class="text-sm text-gray-500 font-medium"><?= ucfirst(str_replace('_', ' ', $examination['examination_type'])) ?></p>
                                        </div>
                                        <div class="text-right space-y-1">
                                            <span class="inline-block text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-semibold">
                                                <?= formatDate($examination['examination_date']) ?>
                                            </span>
                                            <div class="flex items-center justify-end space-x-1">
                                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                                <span class="text-xs text-green-600 font-semibold">Completed</span>
                                            </div>
                                        </div>
                                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                                <div class="text-center py-16">
                                    <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-105 transition-transform duration-300">
                                        <i class="fas fa-stethoscope text-gray-400 text-3xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-base font-medium">No recent examinations</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Quick Actions with Advanced Hover Animations -->
        <div class="mb-6">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200">
                    <div class="p-4 pb-3">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
                                <h3 class="text-lg font-black text-gray-900">
                                    Quick Actions
                                </h3>
                            </div>
                            <p class="text-gray-600 font-medium text-xs">Common tasks and shortcuts for efficient workflow</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 pt-0">
                        <!-- Add Patient Action -->
                        <a href="<?= base_url('patient/create') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-blue-200 hover:border-blue-400 hover:bg-gradient-to-br hover:from-blue-50/80 hover:to-indigo-50/80 transition-all duration-200 hover:shadow-2xl hover:shadow-blue-500/20 hover:scale-105 hover:-translate-y-2">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-600/10 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-200 opacity-0 group-hover/action:opacity-100"></div>
                            <div class="relative z-10 w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-3 shadow-2xl shadow-blue-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-200">
                                <i class="fas fa-user-plus text-lg text-white"></i>
                            </div>
                            <div class="relative z-10 text-center space-y-1">
                                <span class="text-sm font-bold text-gray-900 group-hover/action:text-blue-900 transition-colors duration-300">Add Patient</span>
                                <span class="text-xs text-gray-500 font-medium">Register new patient</span>
                            </div>
                        </a>

                        <!-- New Examination Action -->
                        <a href="<?= base_url('examination/create') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-emerald-200 hover:border-emerald-400 hover:bg-gradient-to-br hover:from-emerald-50/80 hover:to-cyan-50/80 transition-all duration-200 hover:shadow-2xl hover:shadow-emerald-500/20 hover:scale-105 hover:-translate-y-2">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-200 opacity-0 group-hover/action:opacity-100"></div>
                            <div class="relative z-10 w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-xl flex items-center justify-center mb-3 shadow-2xl shadow-emerald-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-200">
                                <i class="fas fa-stethoscope text-lg text-white"></i>
                            </div>
                            <div class="relative z-10 text-center space-y-1">
                                <span class="text-sm font-bold text-gray-900 group-hover/action:text-emerald-900 transition-colors duration-300">New Examination</span>
                                <span class="text-xs text-gray-500 font-medium">Start examination</span>
                            </div>
                        </a>

                        <!-- Book Appointment Action -->
                        <a href="<?= base_url('appointment/create') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-amber-200 hover:border-amber-400 hover:bg-gradient-to-br hover:from-amber-50/80 hover:to-orange-50/80 transition-all duration-200 hover:shadow-2xl hover:shadow-amber-500/20 hover:scale-105 hover:-translate-y-2">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-200 opacity-0 group-hover/action:opacity-100"></div>
                            <div class="relative z-10 w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center mb-3 shadow-2xl shadow-amber-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-200">
                                <i class="fas fa-calendar-plus text-lg text-white"></i>
                            </div>
                            <div class="relative z-10 text-center space-y-1">
                                <span class="text-sm font-bold text-gray-900 group-hover/action:text-amber-900 transition-colors duration-300">Book Appointment</span>
                                <span class="text-xs text-gray-500 font-medium">Schedule visit</span>
                            </div>
                        </a>

                        <!-- Create Invoice Action -->
                        <a href="<?= base_url('finance/create') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-purple-200 hover:border-purple-400 hover:bg-gradient-to-br hover:from-purple-50/80 hover:to-pink-50/80 transition-all duration-200 hover:shadow-2xl hover:shadow-purple-500/20 hover:scale-105 hover:-translate-y-2">
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-600/10 rounded-xl blur-xl group-hover/action:blur-2xl transition-all duration-200 opacity-0 group-hover/action:opacity-100"></div>
                            <div class="relative z-10 w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mb-3 shadow-2xl shadow-purple-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-200">
                                <i class="fas fa-receipt text-lg text-white"></i>
                            </div>
                            <div class="relative z-10 text-center space-y-1">
                                <span class="text-sm font-bold text-gray-900 group-hover/action:text-purple-900 transition-colors duration-300">Create Invoice</span>
                                <span class="text-xs text-gray-500 font-medium">Generate billing</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
fetch('<?= base_url('dashboard/chart-data') ?>')
    .then(response => {
        console.log('Chart data response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Chart data received:', data);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const revenueData = new Array(12).fill(0);
        
        // Map the monthly revenue data
        if (data.monthly_revenue && Array.isArray(data.monthly_revenue)) {
            console.log('Processing monthly revenue data:', data.monthly_revenue);
            data.monthly_revenue.forEach(item => {
                if (item.month >= 1 && item.month <= 12) {
                    revenueData[item.month - 1] = parseFloat(item.total_amount) || 0;
                }
            });
        } else {
            console.log('No monthly revenue data found or not an array');
        }
        
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
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
    })
    .catch(error => {
        console.error('Error fetching chart data:', error);
        console.log('Chart data fetch failed, showing empty chart');
        // Fallback to empty chart
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
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
    });

// Treatment Chart
const treatmentCtx = document.getElementById('treatmentChart').getContext('2d');
fetch('<?= base_url('dashboard/chart-data') ?>')
    .then(response => {
        console.log('Treatment chart data response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Treatment chart data received:', data);
        const treatmentChart = new Chart(treatmentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cleaning', 'Extraction', 'Filling', 'Crown', 'Root Canal', 'Orthodontic', 'Implant', 'Other'],
                datasets: [{
                    data: data.treatment_types ? data.treatment_types.map(item => item.count || 0) : [0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: [
                        '#0284c7',
                        '#059669',
                        '#d97706',
                        '#dc2626',
                        '#7c3aed',
                        '#f59e0b',
                        '#ef4444',
                        '#6b7280'
                    ]
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
    })
    .catch(error => {
        console.error('Error fetching treatment data:', error);
        // Fallback to empty chart
        const treatmentChart = new Chart(treatmentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cleaning', 'Extraction', 'Filling', 'Crown', 'Root Canal', 'Orthodontic', 'Implant', 'Other'],
                datasets: [{
                    data: [0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: [
                        '#0284c7',
                        '#059669',
                        '#d97706',
                        '#dc2626',
                        '#7c3aed',
                        '#f59e0b',
                        '#ef4444',
                        '#6b7280'
                    ]
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
    })
    .catch(error => {
        console.error('Error fetching treatment chart data:', error);
        console.log('Treatment chart data fetch failed, showing empty chart');
        // Fallback to empty chart
        const treatmentChart = new Chart(treatmentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cleaning', 'Extraction', 'Filling', 'Crown', 'Root Canal', 'Orthodontic', 'Implant', 'Other'],
                datasets: [{
                    data: [0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: [
                        '#0284c7',
                        '#059669',
                        '#d97706',
                        '#dc2626',
                        '#7c3aed',
                        '#f59e0b',
                        '#ef4444',
                        '#6b7280'
                    ]
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
    });

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
