

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
                            Export HTML
            </button>
                        <button onclick="exportReport('excel')" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-file-excel mr-2"></i>
                            Export Excel (CSV)
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Period Mode</label>
                        <select id="dateMode" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90 backdrop-blur-sm">
                            <option value="relative" <?= (($filters['date_mode'] ?? 'relative') === 'relative') ? 'selected' : '' ?>>Relative</option>
                            <option value="custom" <?= (($filters['date_mode'] ?? '') === 'custom') ? 'selected' : '' ?>>Custom Range</option>
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
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Compare</label>
                    <select id="compareMode" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90 backdrop-blur-sm">
                        <option value="none" <?= (($filters['compare_mode'] ?? 'none') === 'none') ? 'selected' : '' ?>>No comparison</option>
                        <option value="previous_period" <?= (($filters['compare_mode'] ?? '') === 'previous_period') ? 'selected' : '' ?>>Previous period</option>
                    </select>
                </div>
                <div id="customDateControls" class="flex-[1.6] hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Custom Range</label>
                    <div class="grid grid-cols-2 gap-3">
                        <input id="customFrom" type="text" data-clinic-date="1" value="<?= esc($filters['custom_from'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90 backdrop-blur-sm">
                        <input id="customTo" type="text" data-clinic-date="1" value="<?= esc($filters['custom_to'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90 backdrop-blur-sm">
                    </div>
                </div>
                <div class="lg:pt-6">
                        <button onclick="generateReport()" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Generate Report
                    </button>
                </div>
            </div>
            <div class="mt-4 flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 font-semibold">
                        <i class="fas fa-calendar-range mr-2"></i>
                        <?= esc($filters['period_label'] ?? (($date_range ?? '30') . ' days')) ?>
                    </span>
                    <?php if (($filters['compare_mode'] ?? 'none') === 'previous_period' && !empty($filters['compare_start_date'])): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold">
                        <i class="fas fa-code-compare mr-2"></i>
                        vs <?= esc(($filters['compare_start_date'] ?? '') . ' to ' . ($filters['compare_end_date'] ?? '')) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap items-center gap-2 lg:ml-auto">
                    <select id="reportPresetSelect" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white/90 min-w-[220px]">
                        <option value="">Saved presets...</option>
                    </select>
                    <button type="button" id="loadPresetBtn" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold border border-gray-200 bg-white text-gray-700 hover:border-blue-300">
                        <i class="fas fa-folder-open mr-2"></i>Load
                    </button>
                    <button type="button" id="savePresetBtn" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100">
                        <i class="fas fa-floppy-disk mr-2"></i>Save Preset
                    </button>
                    <button type="button" id="deletePresetBtn" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                        <i class="fas fa-trash mr-2"></i>Delete
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
        <?php if (!empty($reports['comparison']['summary'] ?? [])): ?>
        <?php $cmp = $reports['comparison']['summary']; ?>
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Previous Period Comparison</h3>
                    <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full"><?= esc($reports['comparison']['label'] ?? 'Previous period') ?></span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php
                    $comparisonCards = [
                        ['label' => 'New Patients', 'current' => (float)($reports['summary']['new_patients'] ?? 0), 'previous' => (float)($cmp['new_patients'] ?? 0), 'currency' => false],
                        ['label' => 'Examinations', 'current' => (float)($reports['summary']['examinations_this_period'] ?? 0), 'previous' => (float)($cmp['examinations_this_period'] ?? 0), 'currency' => false],
                        ['label' => 'Appointments', 'current' => (float)($reports['summary']['appointments_this_period'] ?? 0), 'previous' => (float)($cmp['appointments_this_period'] ?? 0), 'currency' => false],
                        ['label' => 'Revenue', 'current' => (float)($reports['summary']['revenue_this_period'] ?? 0), 'previous' => (float)($cmp['revenue_this_period'] ?? 0), 'currency' => true],
                    ];
                    foreach ($comparisonCards as $card):
                        $delta = $card['current'] - $card['previous'];
                        $up = $delta >= 0;
                    ?>
                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($card['label']) ?></p>
                        <p class="mt-1 text-lg font-bold text-gray-900"><?= $card['currency'] ? formatCurrency($card['current']) : number_format($card['current']) ?></p>
                        <p class="mt-1 text-xs <?= $up ? 'text-emerald-600' : 'text-rose-600' ?> font-semibold">
                            <?= $up ? '▲' : '▼' ?> <?= $card['currency'] ? formatCurrency(abs($delta)) : number_format(abs($delta)) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Other Report Types -->
    <?php if (($report_type ?? 'overview') !== 'overview'): ?>
        <div class="mb-8">
                <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?= ucfirst($report_type ?? 'Report') ?> Report</h3>
                        <p class="text-sm text-gray-600">Detailed analysis for <?= esc($filters['period_label'] ?? (($date_range ?? '30') . ' days')) ?></p>
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
                            <?php $summary = $reports['summary'] ?? []; ?>
                            <div class="bg-gradient-to-r from-slate-50 to-blue-50 border border-blue-100 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-500 text-white flex items-center justify-center shrink-0">
                                        <i class="fas fa-circle-info"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">Finance report basis</p>
                                        <p class="text-sm text-slate-600">This report mixes cash collections and provider payout metrics. Cards are labeled as <span class="font-semibold">Cash</span> or <span class="font-semibold">Accrual</span> to avoid ambiguity.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Financial Statistics -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div class="bg-gradient-to-r from-green-50/80 to-emerald-50/80 rounded-lg p-4 border border-green-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Gross Collections</p>
                                            <p class="text-xl font-bold text-green-900"><?= formatCurrency($summary['gross_collections'] ?? 0) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg p-4 border border-blue-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Provider Share (Accrual)</p>
                                            <p class="text-xl font-bold text-blue-900"><?= formatCurrency($summary['provider_share_accrual'] ?? 0) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-yellow-50/80 to-amber-50/80 rounded-lg p-4 border border-yellow-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-money-bill-transfer"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Provider Paid (Cash)</p>
                                            <p class="text-xl font-bold text-yellow-900"><?= formatCurrency($summary['provider_paid_cash'] ?? 0) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-50/80 to-indigo-50/80 rounded-lg p-4 border border-purple-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Net Clinic (Cash)</p>
                                            <p class="text-xl font-bold text-purple-900"><?= formatCurrency($summary['net_clinic_revenue_cash'] ?? 0) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-rose-50/80 to-orange-50/80 rounded-lg p-4 border border-rose-100/50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-orange-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Expenses (Period)</p>
                                            <p class="text-xl font-bold text-rose-900"><?= formatCurrency($summary['total_expenses'] ?? 0) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Provider Payable Outstanding</p>
                                    <p class="mt-1 text-lg font-bold text-rose-700"><?= formatCurrency($summary['provider_payable_outstanding'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Outstanding Invoices (Amount)</p>
                                    <p class="mt-1 text-lg font-bold text-amber-700"><?= formatCurrency($summary['pending_invoice_amount'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Transactions (Period)</p>
                                    <p class="mt-1 text-lg font-bold text-slate-800"><?= (int) ($summary['total_transactions'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Operating Result (Cash)</p>
                                    <p class="mt-1 text-lg font-bold <?= ((float)($summary['operating_result_cash'] ?? 0)) >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>"><?= formatCurrency($summary['operating_result_cash'] ?? 0) ?></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Refund Count</p>
                                    <p class="mt-1 text-lg font-bold text-rose-700"><?= (int) ($summary['refund_count'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Refund Total</p>
                                    <p class="mt-1 text-lg font-bold text-rose-700"><?= formatCurrency($summary['refund_total'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Discounts (Period)</p>
                                    <p class="mt-1 text-lg font-bold text-sky-700"><?= formatCurrency($summary['discount_total'] ?? 0) ?></p>
                                </div>
                            </div>
                            <?php if (!empty($reports['comparison']['summary'] ?? [])): ?>
                            <?php $fcmp = $reports['comparison']['summary']; ?>
                            <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800">Previous Period Comparison</h4>
                                    <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full"><?= esc($reports['comparison']['label'] ?? 'Previous period') ?></span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                    <?php
                                    $financeCompareCards = [
                                        ['label'=>'Gross Collections','current'=>(float)($summary['gross_collections']??0),'previous'=>(float)($fcmp['gross_collections']??0),'count'=>false],
                                        ['label'=>'Provider Share','current'=>(float)($summary['provider_share_accrual']??0),'previous'=>(float)($fcmp['provider_share_accrual']??0),'count'=>false],
                                        ['label'=>'Provider Paid Cash','current'=>(float)($summary['provider_paid_cash']??0),'previous'=>(float)($fcmp['provider_paid_cash']??0),'count'=>false],
                                        ['label'=>'Expenses','current'=>(float)($summary['total_expenses']??0),'previous'=>(float)($fcmp['total_expenses']??0),'count'=>false],
                                        ['label'=>'Pending Invoices','current'=>(float)($summary['pending_invoice_amount']??0),'previous'=>(float)($fcmp['pending_invoice_amount']??0),'count'=>false],
                                        ['label'=>'Transactions','current'=>(float)($summary['total_transactions']??0),'previous'=>(float)($fcmp['total_transactions']??0),'count'=>true],
                                    ];
                                    foreach ($financeCompareCards as $card):
                                        $delta = $card['current'] - $card['previous'];
                                        $up = $delta >= 0;
                                    ?>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($card['label']) ?></p>
                                        <p class="mt-1 font-bold text-gray-900"><?= $card['count'] ? number_format($card['current']) : formatCurrency($card['current']) ?></p>
                                        <p class="mt-1 text-xs <?= $up ? 'text-emerald-600' : 'text-rose-600' ?> font-semibold"><?= $up ? '▲' : '▼' ?> <?= $card['count'] ? number_format(abs($delta)) : formatCurrency(abs($delta)) ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
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

                            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-800">Payment Method Mix</h4>
                                        <p class="text-sm text-gray-500">Click a slice to drill into matching transactions.</p>
                                    </div>
                                    <div class="relative h-64">
                                        <canvas id="financePaymentMethodsChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-800">A/R Aging Distribution</h4>
                                        <p class="text-sm text-gray-500">Click a bucket to inspect invoices in that aging range.</p>
                                    </div>
                                    <div class="relative h-64">
                                        <canvas id="financeAgingChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-800">Provider Payout Status Mix</h4>
                                        <p class="text-sm text-gray-500">Click a status to drill into provider payout rows.</p>
                                    </div>
                                    <div class="relative h-64">
                                        <canvas id="financeProviderStatusChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm xl:col-span-3">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-800">Transaction Type Mix</h4>
                                        <p class="text-sm text-gray-500">Click a bar to drill into transactions by type.</p>
                                    </div>
                                    <div class="relative h-64">
                                        <canvas id="financeTransactionTypeChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($reports['ar_aging_summary'])): ?>
                            <?php $ar = $reports['ar_aging_summary']; ?>
                            <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800">Accounts Receivable Aging</h4>
                                        <p class="text-sm text-gray-500">Outstanding invoice balances bucketed by days past due (through <?= esc($filters['end_date'] ?? date('Y-m-d')) ?>)</p>
                                    </div>
                                    <div class="text-sm font-semibold text-rose-700">Total: <?= formatCurrency($ar['total_balance_due'] ?? 0) ?></div>
                                </div>
                                <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
                                    <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-500">Current</p>
                                        <p class="text-lg font-bold text-slate-900"><?= formatCurrency($ar['current'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-3">
                                        <p class="text-xs uppercase tracking-wide text-amber-700">1-30</p>
                                        <p class="text-lg font-bold text-amber-900"><?= formatCurrency($ar['days_1_30'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-lg bg-orange-50 border border-orange-200 p-3">
                                        <p class="text-xs uppercase tracking-wide text-orange-700">31-60</p>
                                        <p class="text-lg font-bold text-orange-900"><?= formatCurrency($ar['days_31_60'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-lg bg-rose-50 border border-rose-200 p-3">
                                        <p class="text-xs uppercase tracking-wide text-rose-700">61-90</p>
                                        <p class="text-lg font-bold text-rose-900"><?= formatCurrency($ar['days_61_90'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-lg bg-red-50 border border-red-200 p-3">
                                        <p class="text-xs uppercase tracking-wide text-red-700">91+</p>
                                        <p class="text-lg font-bold text-red-900"><?= formatCurrency($ar['days_91_plus'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-3">
                                        <p class="text-xs uppercase tracking-wide text-indigo-700">Invoices</p>
                                        <p class="text-lg font-bold text-indigo-900"><?= (int) ($ar['invoice_count'] ?? 0) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($reports['ar_aging_detail'])): ?>
                            <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800">A/R Aging Detail</h4>
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Top <?= count($reports['ar_aging_detail']) ?> open invoices</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-gray-500 border-b">
                                                <th class="py-2 pr-3">Invoice</th>
                                                <th class="py-2 pr-3">Patient</th>
                                                <th class="py-2 pr-3">Due Date</th>
                                                <th class="py-2 pr-3">Days</th>
                                                <th class="py-2 pr-3">Bucket</th>
                                                <th class="py-2 pr-3 text-right">Balance Due</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <?php foreach ($reports['ar_aging_detail'] as $row): ?>
                                            <tr>
                                                <td class="py-2 pr-3 font-medium text-gray-900"><?= esc($row['transaction_id'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                                                <td class="py-2 pr-3 text-gray-700">
                                                    <?= esc(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?>
                                                    <?php if (!empty($row['patient_number'])): ?>
                                                        <span class="text-xs text-gray-400">(<?= esc($row['patient_number']) ?>)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-2 pr-3 text-gray-700"><?= esc($row['due_date'] ?? '-') ?></td>
                                                <td class="py-2 pr-3 text-gray-700"><?= (int) ($row['days_past_due'] ?? 0) ?></td>
                                                <td class="py-2 pr-3">
                                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700"><?= esc(strtoupper(str_replace('_', ' ', $row['aging_bucket'] ?? 'current'))) ?></span>
                                                </td>
                                                <td class="py-2 pr-3 text-right font-semibold text-rose-700"><?= formatCurrency($row['balance_due'] ?? 0) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <?php if (!empty($reports['provider_payout_status'])): ?>
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Provider Payout Status</h4>
                                    <div class="space-y-3">
                                        <?php foreach ($reports['provider_payout_status'] as $row): ?>
                                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 bg-gray-50/60">
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= esc(ucfirst($row['status'] ?? 'unknown')) ?></p>
                                                <p class="text-xs text-gray-500"><?= (int) ($row['payout_count'] ?? 0) ?> payouts</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-blue-700"><?= formatCurrency($row['provider_share_total'] ?? 0) ?></p>
                                                <p class="text-xs text-rose-600">Due: <?= formatCurrency($row['balance_due_total'] ?? 0) ?></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($reports['provider_payout_breakdown'])): ?>
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Top Providers by Share</h4>
                                    <div class="space-y-3">
                                        <?php foreach ($reports['provider_payout_breakdown'] as $row): ?>
                                        <?php
                                            $share = (float) ($row['provider_share_total'] ?? 0);
                                            $paid = (float) ($row['provider_paid_total'] ?? 0);
                                            $pct = $share > 0 ? min(100, round(($paid / $share) * 100)) : 0;
                                        ?>
                                        <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-semibold text-gray-800"><?= esc($row['provider_label'] ?? 'Unknown') ?></p>
                                                    <p class="text-xs text-gray-500"><?= (int) ($row['payout_count'] ?? 0) ?> payouts - Gross <?= formatCurrency($row['gross_amount_total'] ?? 0) ?></p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-bold text-blue-700"><?= formatCurrency($share) ?></p>
                                                    <p class="text-xs text-rose-600">Due <?= formatCurrency($row['provider_balance_total'] ?? 0) ?></p>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                                                    <div class="h-full bg-gradient-to-r from-blue-500 to-emerald-500" style="width: <?= (int) $pct ?>%"></div>
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">Paid <?= formatCurrency($paid) ?> (<?= (int) $pct ?>%)</div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
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

        <?php if (!empty($detail_tables ?? [])): ?>
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Rows Behind This Report</h3>
                        <p class="text-sm text-gray-600">Audit the exact rows included in the currently selected report and period.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (($detail_tables ?? []) as $idx => $table): ?>
                        <button
                            type="button"
                            class="report-detail-tab inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold border transition-all <?= $idx === 0 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300' ?>"
                            data-target="<?= esc($table['id']) ?>"
                        >
                            <?= esc($table['title']) ?>
                            <span class="ml-2 text-xs <?= $idx === 0 ? 'text-blue-100' : 'text-gray-400' ?>"><?= (int) ($table['row_count'] ?? 0) ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="reportDrillBadge" class="hidden mb-4">
                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 text-sm font-semibold">
                        <i class="fas fa-filter"></i>
                        <span id="reportDrillBadgeText">Drillthrough active</span>
                        <button type="button" id="clearDrillthroughBtn" class="inline-flex items-center px-2 py-1 rounded bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-50 text-xs font-semibold">
                            Clear
                        </button>
                    </div>
                </div>

                <?php foreach (($detail_tables ?? []) as $idx => $table): ?>
                <div class="report-detail-panel <?= $idx === 0 ? '' : 'hidden' ?>" data-panel="<?= esc($table['id']) ?>">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <h4 class="text-lg font-semibold text-gray-800"><?= esc($table['title']) ?></h4>
                        <div class="flex items-center gap-2">
                            <select class="report-detail-per-page px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="25">25 rows</option>
                                <option value="50">50 rows</option>
                                <option value="100">100 rows</option>
                            </select>
                            <div class="relative">
                            <input type="text" class="report-detail-search w-full md:w-72 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search rows in this table...">
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                        <table class="min-w-full text-sm report-detail-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <?php foreach (($table['columns'] ?? []) as $column): ?>
                                    <th data-key="<?= esc($column['key'] ?? '') ?>" class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap"><?= esc($column['label'] ?? $column['key']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($table['rows'])): ?>
                                <tr>
                                    <td colspan="<?= count($table['columns'] ?? []) ?>" class="px-4 py-8 text-center text-gray-500">No rows available for this period.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($table['rows'] as $row): ?>
                                <tr class="report-detail-row hover:bg-blue-50/30 transition-colors">
                                    <?php foreach (($table['columns'] ?? []) as $column): ?>
                                    <?php $key = $column['key'] ?? ''; $value = $row[$key] ?? ''; ?>
                                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                        <?php if (is_numeric($value) && preg_match('/amount|balance|total|share|paid/i', (string) $key)): ?>
                                            <span class="font-semibold"><?= formatCurrency($value) ?></span>
                                        <?php else: ?>
                                            <?= esc((string) $value) ?>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="text-sm text-gray-500 report-detail-pagination-info">Showing <?= (int) min(($table['row_count'] ?? 0), 25) ?> of <?= (int) ($table['row_count'] ?? 0) ?> rows</div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="report-detail-prev px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:border-blue-300 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                            <span class="report-detail-page-label text-sm text-gray-600 min-w-[110px] text-center">Page 1</span>
                            <button type="button" class="report-detail-next px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:border-blue-300 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        </div>
</div>

<script>
const reportWorkspace = {
    detailApiUrl: '<?= base_url('reports/detail-data') ?>',
    filters: <?= json_encode($filters ?? [], JSON_UNESCAPED_SLASHES) ?>,
    detailTableMeta: <?= json_encode(array_map(static function ($t) {
        return [
            'id' => $t['id'] ?? '',
            'title' => $t['title'] ?? '',
            'columns' => $t['columns'] ?? [],
            'row_count' => (int) ($t['row_count'] ?? 0),
        ];
    }, $detail_tables ?? []), JSON_UNESCAPED_SLASHES) ?>,
};

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function syncDateModeControls() {
    const mode = document.getElementById('dateMode')?.value || 'relative';
    const customWrap = document.getElementById('customDateControls');
    const dateRange = document.getElementById('dateRange');

    if (customWrap) customWrap.classList.toggle('hidden', mode !== 'custom');
    if (dateRange) {
        dateRange.disabled = (mode === 'custom');
        dateRange.classList.toggle('opacity-60', mode === 'custom');
    }
}

function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    const dateMode = document.getElementById('dateMode').value;
    const compareMode = document.getElementById('compareMode')?.value || 'none';
    const customFromRaw = document.getElementById('customFrom')?.value || '';
    const customToRaw = document.getElementById('customTo')?.value || '';
    const customFrom = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customFromRaw) || '') : customFromRaw;
    const customTo = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customToRaw) || '') : customToRaw;
    
    const url = new URL(window.location);
    url.searchParams.set('report_type', reportType);
    url.searchParams.set('date_range', dateRange);
    url.searchParams.set('date_mode', dateMode);
    url.searchParams.set('compare_mode', compareMode);
    if (customFrom) url.searchParams.set('custom_from', customFrom); else url.searchParams.delete('custom_from');
    if (customTo) url.searchParams.set('custom_to', customTo); else url.searchParams.delete('custom_to');
    
    window.location.href = url.toString();
}

function exportReport(format) {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    const dateMode = document.getElementById('dateMode').value;
    const compareMode = document.getElementById('compareMode')?.value || 'none';
    const customFromRaw = document.getElementById('customFrom')?.value || '';
    const customToRaw = document.getElementById('customTo')?.value || '';
    const customFrom = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customFromRaw) || '') : customFromRaw;
    const customTo = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customToRaw) || '') : customToRaw;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url("reports/export") ?>';
    form.target = '_blank';
    form.style.display = 'none';

    const fields = {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        format: format,
        report_type: reportType,
        date_range: dateRange,
        date_mode: dateMode,
        compare_mode: compareMode,
        custom_from: customFrom,
        custom_to: customTo,
    };

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    form.remove();
}

document.getElementById('dateMode')?.addEventListener('change', syncDateModeControls);
syncDateModeControls();

const reportDrillState = { active: false, tableId: null, query: '', label: '' };
const reportDetailState = {};

function setActiveDetailTab(target) {
    document.querySelectorAll('.report-detail-tab').forEach((b) => {
        const active = b.dataset.target === target;
        b.classList.toggle('bg-blue-600', active);
        b.classList.toggle('text-white', active);
        b.classList.toggle('border-blue-600', active);
        b.classList.toggle('bg-white', !active);
        b.classList.toggle('text-gray-700', !active);
        b.classList.toggle('border-gray-200', !active);
        const countSpan = b.querySelector('span');
        if (countSpan) {
            countSpan.classList.toggle('text-blue-100', active);
            countSpan.classList.toggle('text-gray-400', !active);
        }
    });
    document.querySelectorAll('.report-detail-panel').forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.panel !== target);
    });
}

const REPORT_PRESETS_KEY = 'dental_saas_reports_presets_v1';

function getCurrentFilterState() {
    return {
        report_type: document.getElementById('reportType')?.value || 'overview',
        date_range: document.getElementById('dateRange')?.value || '30',
        date_mode: document.getElementById('dateMode')?.value || 'relative',
        compare_mode: document.getElementById('compareMode')?.value || 'none',
        custom_from: (() => {
            const raw = document.getElementById('customFrom')?.value || '';
            return window.parseClinicDateToIso ? (window.parseClinicDateToIso(raw) || '') : raw;
        })(),
        custom_to: (() => {
            const raw = document.getElementById('customTo')?.value || '';
            return window.parseClinicDateToIso ? (window.parseClinicDateToIso(raw) || '') : raw;
        })(),
    };
}

function readReportPresets() {
    try {
        const raw = localStorage.getItem(REPORT_PRESETS_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function writeReportPresets(presets) {
    localStorage.setItem(REPORT_PRESETS_KEY, JSON.stringify(presets));
}

function renderPresetOptions() {
    const select = document.getElementById('reportPresetSelect');
    if (!select) return;
    const presets = readReportPresets();
    const currentValue = select.value;
    select.innerHTML = '<option value="">Saved presets...</option>' + presets.map((p, idx) =>
        `<option value="${idx}">${escapeHtml(p.name || `Preset ${idx + 1}`)}</option>`
    ).join('');
    if (currentValue && select.querySelector(`option[value="${currentValue}"]`)) {
        select.value = currentValue;
    }
}

function applyPresetToForm(preset) {
    if (!preset || !preset.filters) return;
    const f = preset.filters;
    if (document.getElementById('reportType')) document.getElementById('reportType').value = f.report_type || 'overview';
    if (document.getElementById('dateRange')) document.getElementById('dateRange').value = f.date_range || '30';
    if (document.getElementById('dateMode')) document.getElementById('dateMode').value = f.date_mode || 'relative';
    if (document.getElementById('compareMode')) document.getElementById('compareMode').value = f.compare_mode || 'none';
    if (document.getElementById('customFrom')) document.getElementById('customFrom').value = f.custom_from || '';
    if (document.getElementById('customTo')) document.getElementById('customTo').value = f.custom_to || '';
    syncDateModeControls();
}

document.getElementById('savePresetBtn')?.addEventListener('click', () => {
    const name = window.prompt('Preset name');
    if (!name) return;
    const presets = readReportPresets();
    presets.push({ name: name.trim(), filters: getCurrentFilterState(), created_at: new Date().toISOString() });
    writeReportPresets(presets);
    renderPresetOptions();
});

document.getElementById('loadPresetBtn')?.addEventListener('click', () => {
    const idx = parseInt(document.getElementById('reportPresetSelect')?.value || '', 10);
    if (Number.isNaN(idx)) return;
    const preset = readReportPresets()[idx];
    if (!preset) return;
    applyPresetToForm(preset);
});

document.getElementById('deletePresetBtn')?.addEventListener('click', () => {
    const select = document.getElementById('reportPresetSelect');
    const idx = parseInt(select?.value || '', 10);
    if (Number.isNaN(idx)) return;
    const presets = readReportPresets();
    if (!presets[idx]) return;
    presets.splice(idx, 1);
    writeReportPresets(presets);
    renderPresetOptions();
});

renderPresetOptions();

function getPanelSearchQuery(panel) {
    const search = panel.querySelector('.report-detail-search');
    return (search?.value || '').trim().toLowerCase();
}

function getPanelPerPage(panel) {
    const select = panel.querySelector('.report-detail-per-page');
    return Math.max(10, Math.min(200, parseInt(select?.value || '25', 10) || 25));
}

function applyPanelRowFilters(panel) {
    const panelId = panel.dataset.panel;
    const searchQ = getPanelSearchQuery(panel);
    const drillQ = reportDrillState.active && reportDrillState.tableId === panelId
        ? (reportDrillState.query || '').toLowerCase()
        : '';

    panel.querySelectorAll('.report-detail-row').forEach((row) => {
        const text = row.innerText.toLowerCase();
        const searchMatch = !searchQ || text.includes(searchQ);
        const drillMatch = !drillQ || text.includes(drillQ);
        row.classList.toggle('hidden', !(searchMatch && drillMatch));
    });
}

function applyAllPanelFilters() {
    document.querySelectorAll('.report-detail-panel').forEach(applyPanelRowFilters);
}

function updateDrillBadge() {
    const badge = document.getElementById('reportDrillBadge');
    const text = document.getElementById('reportDrillBadgeText');
    if (!badge || !text) return;
    if (!reportDrillState.active) {
        badge.classList.add('hidden');
        return;
    }
    text.textContent = `Drillthrough: ${reportDrillState.label}`;
    badge.classList.remove('hidden');
}

function applyDrillthrough(tableId, query, label) {
    reportDrillState.active = true;
    reportDrillState.tableId = tableId;
    reportDrillState.query = query;
    reportDrillState.label = label;
    setActiveDetailTab(tableId);
    updateDrillBadge();
    applyAllPanelFilters();
    loadDetailPanel(tableId, 1);
    const section = document.getElementById('reportDrillBadge');
    section?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearDrillthrough() {
    reportDrillState.active = false;
    reportDrillState.tableId = null;
    reportDrillState.query = '';
    reportDrillState.label = '';
    updateDrillBadge();
    applyAllPanelFilters();
    if (document.querySelector('.report-detail-panel:not(.hidden)')) {
        const activePanel = document.querySelector('.report-detail-panel:not(.hidden)');
        loadDetailPanel(activePanel.dataset.panel, 1);
    }
}

document.getElementById('clearDrillthroughBtn')?.addEventListener('click', clearDrillthrough);

document.querySelectorAll('.report-detail-tab').forEach((btn) => {
    btn.addEventListener('click', () => {
        const target = btn.dataset.target;
        setActiveDetailTab(target);
        loadDetailPanel(target, reportDetailState[target]?.page || 1);
    });
});

document.querySelectorAll('.report-detail-panel').forEach((panel) => {
    const search = panel.querySelector('.report-detail-search');
    const perPage = panel.querySelector('.report-detail-per-page');
    const prevBtn = panel.querySelector('.report-detail-prev');
    const nextBtn = panel.querySelector('.report-detail-next');
    const panelId = panel.dataset.panel;
    reportDetailState[panelId] = { page: 1, sortBy: '', sortDir: 'asc' };
    if (search) {
        search.addEventListener('input', () => {
            reportDetailState[panelId].page = 1;
            loadDetailPanel(panelId, 1);
        });
    }
    if (perPage) {
        perPage.addEventListener('change', () => {
            reportDetailState[panelId].page = 1;
            loadDetailPanel(panelId, 1);
        });
    }
    prevBtn?.addEventListener('click', () => {
        const currentPage = reportDetailState[panelId]?.page || 1;
        if (currentPage > 1) loadDetailPanel(panelId, currentPage - 1);
    });
    nextBtn?.addEventListener('click', () => {
        const currentPage = reportDetailState[panelId]?.page || 1;
        loadDetailPanel(panelId, currentPage + 1);
    });
    panel.querySelectorAll('thead th[data-key]').forEach((th) => {
        th.classList.add('cursor-pointer', 'select-none', 'hover:text-blue-700');
        th.addEventListener('click', () => {
            const key = th.dataset.key || '';
            if (!key) return;
            const state = reportDetailState[panelId] || { page: 1, sortBy: '', sortDir: 'asc' };
            if (state.sortBy === key) {
                state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sortBy = key;
                state.sortDir = 'asc';
            }
            state.page = 1;
            reportDetailState[panelId] = state;
            loadDetailPanel(panelId, 1);
        });
    });
});

function renderDetailRows(panel, payload) {
    const tbody = panel.querySelector('tbody');
    const info = panel.querySelector('.report-detail-pagination-info');
    const pageLabel = panel.querySelector('.report-detail-page-label');
    const prevBtn = panel.querySelector('.report-detail-prev');
    const nextBtn = panel.querySelector('.report-detail-next');
    if (!tbody || !payload?.table) return;

    const columns = payload.table.columns || [];
    const rows = payload.rows || [];
    const pagination = payload.pagination || {};
    const sorting = payload.sorting || {};
    const panelId = panel.dataset.panel;

    const state = reportDetailState[panelId] || { page: 1, sortBy: '', sortDir: 'asc' };
    state.sortBy = sorting.sort_by || state.sortBy || '';
    state.sortDir = sorting.sort_dir || state.sortDir || 'asc';
    reportDetailState[panelId] = state;

    panel.querySelectorAll('thead th[data-key]').forEach((th) => {
        const key = th.dataset.key || '';
        const active = key && key === state.sortBy;
        th.classList.toggle('text-blue-700', active);
        th.classList.toggle('font-bold', active);
        const baseLabel = th.dataset.baseLabel || th.textContent.replace(/\s[▲▼]$/, '');
        th.dataset.baseLabel = baseLabel;
        th.textContent = active ? `${baseLabel} ${state.sortDir === 'desc' ? '▼' : '▲'}` : baseLabel;
    });

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${columns.length}" class="px-4 py-8 text-center text-gray-500">No rows match the current filters.</td></tr>`;
    } else {
        tbody.innerHTML = rows.map((row) => {
            const cells = columns.map((col) => {
                const key = col.key || '';
                const raw = row[key] ?? '';
                const isMoney = /amount|balance|total|share|paid/i.test(key) && !Number.isNaN(parseFloat(raw));
                const rendered = isMoney ? formatCurrency(raw) : escapeHtml(raw);
                return `<td class="px-4 py-3 text-gray-700 whitespace-nowrap">${isMoney ? `<span class="font-semibold">${rendered}</span>` : rendered}</td>`;
            }).join('');
            return `<tr class="report-detail-row hover:bg-blue-50/30 transition-colors">${cells}</tr>`;
        }).join('');
    }

    const from = pagination.from ?? 0;
    const to = pagination.to ?? 0;
    const total = pagination.total_rows ?? rows.length;
    const page = pagination.page ?? 1;
    const lastPage = pagination.last_page ?? 1;
    info.textContent = `Showing ${from}-${to} of ${total} rows`;
    pageLabel.textContent = `Page ${page} of ${lastPage}`;
    prevBtn.disabled = page <= 1;
    nextBtn.disabled = page >= lastPage;
}

async function loadDetailPanel(panelId, page = 1) {
    const panel = document.querySelector(`.report-detail-panel[data-panel="${panelId}"]`);
    if (!panel) return;

    const search = panel.querySelector('.report-detail-search')?.value || '';
    const perPage = getPanelPerPage(panel);
    const params = new URLSearchParams();
    params.set('report_type', reportWorkspace.filters.report_type || 'overview');
    params.set('date_mode', reportWorkspace.filters.date_mode || 'relative');
    params.set('date_range', reportWorkspace.filters.date_range || '30');
    params.set('compare_mode', reportWorkspace.filters.compare_mode || 'none');
    if (reportWorkspace.filters.custom_from) params.set('custom_from', reportWorkspace.filters.custom_from);
    if (reportWorkspace.filters.custom_to) params.set('custom_to', reportWorkspace.filters.custom_to);
    params.set('table_id', panelId);
    params.set('page', String(page));
    params.set('per_page', String(perPage));
    const sortState = reportDetailState[panelId] || {};
    if (sortState.sortBy) {
        params.set('sort_by', sortState.sortBy);
        params.set('sort_dir', sortState.sortDir || 'asc');
    }
    if (search) params.set('q', search);
    if (reportDrillState.active) {
        params.set('drill_table', reportDrillState.tableId || '');
        params.set('drill_query', reportDrillState.query || '');
    }

    panel.classList.add('opacity-70');
    try {
        const res = await fetch(`${reportWorkspace.detailApiUrl}?${params.toString()}`, { credentials: 'same-origin' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const payload = await res.json();
        renderDetailRows(panel, payload);
        reportDetailState[panelId] = { ...(reportDetailState[panelId] || {}), page: payload.pagination?.page || page };
    } catch (err) {
        const tbody = panel.querySelector('tbody');
        if (tbody) {
            const colspan = panel.querySelectorAll('thead th').length || 1;
            tbody.innerHTML = `<tr><td colspan="${colspan}" class="px-4 py-8 text-center text-rose-600">Failed to load rows. Please retry.</td></tr>`;
        }
    } finally {
        panel.classList.remove('opacity-70');
    }
}

const initialDetailPanel = document.querySelector('.report-detail-panel:not(.hidden)');
if (initialDetailPanel) {
    loadDetailPanel(initialDetailPanel.dataset.panel, 1);
}

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart')?.getContext('2d');
if (revenueCtx) {
    // Get real revenue data from PHP
    const revenueData = <?= json_encode($reports['charts']['monthly_revenue'] ?? []) ?>;
    
    // Extract labels and data
    const labels = revenueData.map(item => item.month || '');
    const data = revenueData.map(item => parseFloat(item.total_amount ?? item.amount) || 0);

    if (labels.length === 0 || data.every(val => val === 0)) {
        labels.length = 0;
        data.length = 0;
    }
    
    const revenueChart = new Chart(revenueCtx, {
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
            },
            onClick: function(event, elements) {
                if (!elements || !elements.length) return;
                const idx = elements[0].index;
                const month = labels[idx];
                if (!month) return;
                applyDrillthrough('overview_finance_transactions', month.toLowerCase(), `Revenue chart -> ${month}`);
            }
        }
    });
}

// Examination Chart
const examinationCtx = document.getElementById('examinationChart')?.getContext('2d');
if (examinationCtx) {
    // Get real examination data from PHP
    const examinationData = <?= json_encode($reports['charts']['examination_types'] ?? []) ?>;
    
    // Extract labels and data
    const labels = examinationData.map(item => item.examination_type || 'Unknown');
    const data = examinationData.map(item => parseInt(item.count) || 0);

    if (labels.length === 0 || data.every(val => val === 0)) {
        labels.length = 0;
        data.length = 0;
    }
    
    // Define colors for different examination types
    const colors = [
        '#0284c7', '#059669', '#d97706', '#dc2626', '#7c3aed',
        '#f59e0b', '#ef4444', '#6b7280', '#10b981', '#8b5cf6'
    ];
    
    const examinationChart = new Chart(examinationCtx, {
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
            },
            onClick: function(event, elements) {
                if (!elements || !elements.length) return;
                const idx = elements[0].index;
                const type = labels[idx];
                if (!type) return;
                applyDrillthrough('overview_examinations', type.toLowerCase(), `Examination type -> ${type}`);
            }
        }
    });
}

// Appointment Chart
const appointmentCtx = document.getElementById('appointmentChart')?.getContext('2d');
if (appointmentCtx) {
    // Get real appointment data from PHP
    const appointmentData = <?= json_encode($reports['charts']['appointment_status'] ?? []) ?>;
    
    // Extract labels and data
    const labels = appointmentData.map(item => item.status || 'Unknown');
    const data = appointmentData.map(item => parseInt(item.count) || 0);

    if (labels.length === 0 || data.every(val => val === 0)) {
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
    
    const appointmentChart = new Chart(appointmentCtx, {
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
            },
            onClick: function(event, elements) {
                if (!elements || !elements.length) return;
                const idx = elements[0].index;
                const status = labels[idx];
                if (!status) return;
                applyDrillthrough('overview_appointments', status.toLowerCase(), `Appointment status -> ${status}`);
            }
        }
    });
}

// Finance: Payment Method Mix
const financePaymentMethodsCtx = document.getElementById('financePaymentMethodsChart')?.getContext('2d');
if (financePaymentMethodsCtx) {
    const rows = <?= json_encode($reports['payment_methods'] ?? []) ?>;
    const labels = rows.map(r => (r.payment_method || 'unknown').toString());
    const data = rows.map(r => parseFloat(r.total_amount || 0));
    const palette = ['#0284c7', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#64748b', '#ec4899'];

    new Chart(financePaymentMethodsCtx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{ data, backgroundColor: palette.slice(0, Math.max(labels.length, 1)) }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            onClick: function(event, elements) {
                if (!elements?.length) return;
                const method = labels[elements[0].index];
                if (!method) return;
                applyDrillthrough('transactions', method.toLowerCase(), `Payment method -> ${method}`);
            }
        }
    });
}

// Finance: A/R Aging Distribution
const financeAgingCtx = document.getElementById('financeAgingChart')?.getContext('2d');
if (financeAgingCtx) {
    const ar = <?= json_encode($reports['ar_aging_summary'] ?? []) ?>;
    const agingBuckets = [
        { key: 'current', label: 'Current', color: '#94a3b8' },
        { key: 'days_1_30', label: '1-30', color: '#fbbf24' },
        { key: 'days_31_60', label: '31-60', color: '#fb923c' },
        { key: 'days_61_90', label: '61-90', color: '#fb7185' },
        { key: 'days_91_plus', label: '91+', color: '#ef4444' },
    ];
    const labels = agingBuckets.map(b => b.label);
    const data = agingBuckets.map(b => parseFloat(ar[b.key] || 0));

    new Chart(financeAgingCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Balance Due',
                data,
                backgroundColor: agingBuckets.map(b => b.color),
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => formatCurrency(v) }
                }
            },
            onClick: function(event, elements) {
                if (!elements?.length) return;
                const bucket = agingBuckets[elements[0].index];
                if (!bucket) return;
                applyDrillthrough('ar_aging_detail', bucket.key.toLowerCase(), `A/R aging -> ${bucket.label}`);
            }
        }
    });
}

// Finance: Provider Payout Status Mix
const financeProviderStatusCtx = document.getElementById('financeProviderStatusChart')?.getContext('2d');
if (financeProviderStatusCtx) {
    const rows = <?= json_encode($reports['provider_payout_status'] ?? []) ?>;
    const labels = rows.map(r => (r.status || 'unknown').toString());
    const data = rows.map(r => parseFloat(r.provider_share_total || 0));
    const statusColorMap = {
        unpaid: '#ef4444',
        partial: '#f59e0b',
        paid: '#10b981',
        void: '#64748b',
    };
    const colors = labels.map(l => statusColorMap[l.toLowerCase()] || '#0284c7');

new Chart(financeProviderStatusCtx, {
        type: 'pie',
        data: { labels, datasets: [{ data, backgroundColor: colors }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            onClick: function(event, elements) {
                if (!elements?.length) return;
                const status = labels[elements[0].index];
                if (!status) return;
                applyDrillthrough('provider_payout_rows', status.toLowerCase(), `Provider payout status -> ${status}`);
            }
        }
    });
}

// Finance: Transaction Type Mix
const financeTxnTypeCtx = document.getElementById('financeTransactionTypeChart')?.getContext('2d');
if (financeTxnTypeCtx) {
    const rows = <?= json_encode($reports['transaction_type_mix'] ?? []) ?>;
    const labels = rows.map(r => (r.transaction_type || 'unknown').toString());
    const data = rows.map(r => parseFloat(r.total_amount || 0));
    const colors = labels.map((type) => ({
        payment: '#10b981',
        invoice: '#0284c7',
        refund: '#ef4444',
        adjustment: '#f59e0b',
    }[(type || '').toLowerCase()] || '#64748b'));

    new Chart(financeTxnTypeCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Total Amount',
                data,
                backgroundColor: colors,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => formatCurrency(v) }
                }
            },
            onClick: function(event, elements) {
                if (!elements?.length) return;
                const type = labels[elements[0].index];
                if (!type) return;
                applyDrillthrough('transactions', type.toLowerCase(), `Transaction type -> ${type}`);
            }
        }
    });
}

// Utility function for currency formatting
function formatCurrency(value) {
    if (typeof value !== 'number') {
        value = parseFloat(value) || 0;
    }

    const currencySymbol = '<?= settings()->getCurrencySymbol() ?>';
    const currencyPosition = '<?= settings()->get('currency_position', 'before') ?>';
    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(value);

    return currencyPosition === 'after'
        ? `${formatted} ${currencySymbol}`
        : `${currencySymbol}${formatted}`;
}
</script>
