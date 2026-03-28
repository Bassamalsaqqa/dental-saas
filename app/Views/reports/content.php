
<?php
$tm = static fn(string $key, array $params = []) => lang('TenantMisc.' . $key, $params);
$activeReportType = $report_type ?? 'overview';
$financeTypeLabel = static function (?string $type) use ($tm): string {
    return match ((string) $type) {
        'payment' => $tm('payment'),
        'expense' => $tm('expense'),
        'refund' => $tm('refund'),
        'void' => $tm('void'),
        'provider_payout' => $tm('providerPayoutCash'),
        default => $type ? ucfirst(str_replace('_', ' ', (string) $type)) : $tm('unknown'),
    };
};
$financeMethodLabel = static function (?string $method) use ($tm): string {
    return match ((string) $method) {
        'cash' => $tm('cash'),
        'card' => $tm('card'),
        'bank_transfer' => $tm('bankTransfer'),
        'check' => $tm('check'),
        'insurance' => $tm('insurance'),
        'other' => $tm('other'),
        default => $method ? ucfirst(str_replace('_', ' ', (string) $method)) : $tm('unknown'),
    };
};
$agingBucketLabel = static function (?string $bucket) use ($tm): string {
    return match ((string) $bucket) {
        'current' => $tm('current'),
        'days_1_30' => $tm('days1to30'),
        'days_31_60' => $tm('days31to60'),
        'days_61_90' => $tm('days61to90'),
        'days_91_plus' => $tm('days91Plus'),
        default => $bucket ? strtoupper(str_replace('_', ' ', (string) $bucket)) : $tm('unknown'),
    };
};
?>
<div class="space-y-6">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-gradient-to-br from-blue-400/15 to-indigo-600/15 blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-gradient-to-br from-cyan-400/15 to-blue-600/15 blur-3xl animate-pulse delay-1000"></div>
    </div>
    <div class="mx-auto max-w-7xl space-y-6">
    <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-center">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-chart-bar text-xl"></i>
                        </div>
        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= lang('TenantMisc.reportsAnalyticsTitle') ?></h1>
                            <p class="text-gray-600"><?= lang('TenantMisc.reportsAnalyticsDesc') ?></p>
                        </div>
        </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button onclick="exportReport('pdf')" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-lg border border-gray-200 hover:bg-gray-50 transition-all duration-200 shadow-sm">
                            <i class="fas fa-file-pdf mr-2"></i>
                            <?= lang('TenantMisc.exportHtml') ?>
            </button>
                        <button onclick="exportReport('excel')" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-file-excel mr-2"></i>
                            <?= lang('TenantMisc.exportExcelCsv') ?>
            </button>
                    </div>
                </div>
        </div>
    </div>

        <!-- Simplified Report Filters -->
    <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
            <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.reportType') ?></label>
                    <select id="reportType" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                        <option value="overview" <?= ($report_type ?? 'overview') === 'overview' ? 'selected' : '' ?>><?= lang('TenantMisc.reportOverview') ?></option>
                        <option value="patients" <?= ($report_type ?? '') === 'patients' ? 'selected' : '' ?>><?= lang('TenantMisc.reportPatients') ?></option>
                        <option value="examinations" <?= ($report_type ?? '') === 'examinations' ? 'selected' : '' ?>><?= lang('TenantMisc.reportExaminations') ?></option>
                        <option value="appointments" <?= ($report_type ?? '') === 'appointments' ? 'selected' : '' ?>><?= lang('TenantMisc.reportAppointments') ?></option>
                        <option value="finance" <?= ($report_type ?? '') === 'finance' ? 'selected' : '' ?>><?= lang('TenantMisc.reportFinance') ?></option>
                        <option value="treatments" <?= ($report_type ?? '') === 'treatments' ? 'selected' : '' ?>><?= lang('TenantMisc.reportTreatments') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.quickRange') ?></label>
                    <select id="dateRange" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                        <option value="7" <?= ($date_range ?? '30') === '7' ? 'selected' : '' ?>><?= lang('TenantMisc.last7Days') ?></option>
                        <option value="30" <?= ($date_range ?? '30') === '30' ? 'selected' : '' ?>><?= lang('TenantMisc.last30Days') ?></option>
                        <option value="90" <?= ($date_range ?? '30') === '90' ? 'selected' : '' ?>><?= lang('TenantMisc.last90Days') ?></option>
                        <option value="365" <?= ($date_range ?? '30') === '365' ? 'selected' : '' ?>><?= lang('TenantMisc.lastYear') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.from') ?></label>
                    <input id="customFrom" type="text" data-clinic-date="1" value="<?= esc(($filters['custom_from'] ?? '') ?: ($filters['start_date'] ?? '')) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.to') ?></label>
                    <input id="customTo" type="text" data-clinic-date="1" value="<?= esc(($filters['custom_to'] ?? '') ?: ($filters['end_date'] ?? '')) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                </div>
                <div class="flex items-end">
                    <button onclick="generateReport()" class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-chart-bar mr-2"></i><?= lang('TenantMisc.generate') ?>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
                <div id="patientFilterWrap" class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.patientOptional') ?></label>
                    <select id="patientFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                        <option value=""><?= lang('TenantMisc.allPatients') ?></option>
                        <?php foreach (($report_patients ?? []) as $patient): ?>
                            <?php
                                $patientId = (int) ($patient['id'] ?? 0);
                                $patientLabel = trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')));
                                if ($patientLabel === '') {
                                    $patientLabel = lang('TenantMisc.patientNumberPrefix') . $patientId;
                                }
                                $patientNumber = trim((string) ($patient['patient_number'] ?? ''));
                            ?>
                            <option value="<?= $patientId ?>" <?= (int) ($filters['patient_id'] ?? 0) === $patientId ? 'selected' : '' ?>>
                                <?= esc($patientLabel . ($patientNumber !== '' ? ' (' . $patientNumber . ')' : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="transactionTypeWrap">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.transactionTypeOptional') ?></label>
                    <select id="transactionTypeFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                        <option value=""><?= lang('TenantMisc.allTypes') ?></option>
                        <option value="payment" <?= ($filters['transaction_type'] ?? '') === 'payment' ? 'selected' : '' ?>><?= lang('TenantMisc.payment') ?></option>
                        <option value="expense" <?= ($filters['transaction_type'] ?? '') === 'expense' ? 'selected' : '' ?>><?= lang('TenantMisc.expense') ?></option>
                        <option value="refund" <?= ($filters['transaction_type'] ?? '') === 'refund' ? 'selected' : '' ?>><?= lang('TenantMisc.refund') ?></option>
                        <option value="void" <?= ($filters['transaction_type'] ?? '') === 'void' ? 'selected' : '' ?>><?= $tm('void') ?></option>
                        <option value="provider_payout" <?= ($filters['transaction_type'] ?? '') === 'provider_payout' ? 'selected' : '' ?>><?= $tm('providerPayoutCash') ?></option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
                <div id="paymentMethodWrap" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= $tm('paymentMethodFinance') ?></label>
                    <select id="paymentMethodFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                        <option value=""><?= $tm('allMethods') ?></option>
                        <option value="cash" <?= ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>><?= $tm('cash') ?></option>
                        <option value="card" <?= ($filters['payment_method'] ?? '') === 'card' ? 'selected' : '' ?>><?= $tm('card') ?></option>
                        <option value="bank_transfer" <?= ($filters['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>><?= $tm('bankTransfer') ?></option>
                        <option value="check" <?= ($filters['payment_method'] ?? '') === 'check' ? 'selected' : '' ?>><?= $tm('check') ?></option>
                        <option value="insurance" <?= ($filters['payment_method'] ?? '') === 'insurance' ? 'selected' : '' ?>><?= $tm('insurance') ?></option>
                        <option value="other" <?= ($filters['payment_method'] ?? '') === 'other' ? 'selected' : '' ?>><?= $tm('other') ?></option>
                    </select>
                </div>
                <div id="examinationTypeWrap" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= $tm('examinationType') ?></label>
                    <input id="examinationTypeFilter" type="text" value="<?= esc($filters['examination_type'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90" placeholder="<?= esc($tm('anyExaminationType')) ?>">
                </div>
                <div id="appointmentStatusWrap" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= $tm('appointmentStatus') ?></label>
                    <select id="appointmentStatusFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                        <option value=""><?= $tm('allStatuses') ?></option>
                        <option value="scheduled" <?= ($filters['appointment_status'] ?? '') === 'scheduled' ? 'selected' : '' ?>><?= $tm('scheduled') ?></option>
                        <option value="confirmed" <?= ($filters['appointment_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>><?= $tm('confirmed') ?></option>
                        <option value="completed" <?= ($filters['appointment_status'] ?? '') === 'completed' ? 'selected' : '' ?>><?= $tm('completed') ?></option>
                        <option value="cancelled" <?= ($filters['appointment_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= $tm('cancelled') ?></option>
                        <option value="no_show" <?= ($filters['appointment_status'] ?? '') === 'no_show' ? 'selected' : '' ?>><?= $tm('noShow') ?></option>
                        <option value="pending" <?= ($filters['appointment_status'] ?? '') === 'pending' ? 'selected' : '' ?>><?= $tm('pending') ?></option>
                    </select>
                </div>
                <div id="appointmentTypeWrap" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= $tm('appointmentType') ?></label>
                    <input id="appointmentTypeFilter" type="text" value="<?= esc($filters['appointment_type'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90" placeholder="<?= esc($tm('anyAppointmentType')) ?>">
                </div>
                <div id="treatmentStatusWrap" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= $tm('treatmentStatus') ?></label>
                    <select id="treatmentStatusFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90">
                        <option value=""><?= $tm('allStatuses') ?></option>
                        <option value="planned" <?= ($filters['treatment_status'] ?? '') === 'planned' ? 'selected' : '' ?>><?= $tm('planned') ?></option>
                        <option value="in_progress" <?= ($filters['treatment_status'] ?? '') === 'in_progress' ? 'selected' : '' ?>><?= $tm('inProgress') ?></option>
                        <option value="completed" <?= ($filters['treatment_status'] ?? '') === 'completed' ? 'selected' : '' ?>><?= $tm('completed') ?></option>
                        <option value="on_hold" <?= ($filters['treatment_status'] ?? '') === 'on_hold' ? 'selected' : '' ?>><?= $tm('onHold') ?></option>
                        <option value="cancelled" <?= ($filters['treatment_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= $tm('cancelled') ?></option>
                    </select>
                </div>
                <div id="treatmentTypeWrap" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= $tm('treatmentType') ?></label>
                    <input id="treatmentTypeFilter" type="text" value="<?= esc($filters['treatment_type'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/90" placeholder="<?= esc($tm('anyTreatmentType')) ?>">
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 font-semibold">
                    <i class="fas fa-calendar-range mr-2"></i><?= esc($filters['period_label'] ?? (($date_range ?? '30') . ' ' . $tm('days'))) ?>
                </span>
                <?php if (!empty($filters['patient_id'])): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 font-semibold">
                    <i class="fas fa-user mr-2"></i><?= lang('TenantMisc.patientScoped') ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($filters['transaction_type'])): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold">
                    <i class="fas fa-filter mr-2"></i><?= lang('TenantMisc.typePrefix') ?> <?= esc($financeTypeLabel($filters['transaction_type'])) ?>
                </span>
                <?php endif; ?>
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
                        <h3 class="text-xl font-bold text-gray-800 mb-2"><?= lang('TenantMisc.summaryStatistics') ?></h3>
                        <p class="text-sm text-gray-600"><?= lang('TenantMisc.summaryStatisticsDesc') ?></p>
                    </div>
                <div class="space-y-4">
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 rounded-lg backdrop-blur-sm border border-blue-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-users"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900"><?= lang('TenantMisc.totalPatients') ?></p>
                                <p class="text-xs text-gray-500"><?= lang('TenantMisc.allTime') ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= $reports['summary']['total_patients'] ?? 0 ?></p>
                                    <p class="text-xs text-green-600 font-semibold">+<?= $reports['summary']['new_patients'] ?? 0 ?> <?= lang('TenantMisc.new') ?></p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50/80 to-emerald-50/80 rounded-lg backdrop-blur-sm border border-green-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-stethoscope"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900"><?= lang('TenantMisc.examinations') ?></p>
                                <p class="text-xs text-gray-500"><?= lang('TenantMisc.thisPeriod') ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= $reports['summary']['examinations_this_period'] ?? 0 ?></p>
                                    <p class="text-xs text-blue-600 font-semibold"><?= $reports['summary']['total_examinations'] ?? 0 ?> <?= $tm('totalLower') ?></p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-50/80 to-amber-50/80 rounded-lg backdrop-blur-sm border border-yellow-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900"><?= lang('TenantMisc.appointments') ?></p>
                                <p class="text-xs text-gray-500"><?= lang('TenantMisc.thisPeriod') ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= $reports['summary']['appointments_this_period'] ?? 0 ?></p>
                                    <p class="text-xs text-blue-600 font-semibold"><?= $reports['summary']['total_appointments'] ?? 0 ?> <?= $tm('totalLower') ?></p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-purple-50/80 to-indigo-50/80 rounded-lg backdrop-blur-sm border border-purple-100/50">
                        <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center text-white shadow-md">
                                        <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold text-gray-900"><?= lang('TenantMisc.revenue') ?></p>
                                <p class="text-xs text-gray-500"><?= lang('TenantMisc.thisPeriod') ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900"><?= formatCurrency($reports['summary']['revenue_this_period'] ?? 0) ?></p>
                                    <p class="text-xs text-blue-600 font-semibold"><?= formatCurrency($reports['summary']['total_revenue'] ?? 0) ?> <?= $tm('totalLower') ?></p>
                    </div>
                </div>
                </div>
            </div>

                    <!-- Monthly Revenue Chart -->
                    <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?= $tm('monthlyRevenueTitle') ?></h3>
                    <p class="text-sm text-gray-600"><?= $tm('monthlyRevenueDesc') ?></p>
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
                        <h3 class="text-xl font-bold text-gray-800 mb-2"><?= $tm('examinationTypesTitle') ?></h3>
                        <p class="text-sm text-gray-600"><?= $tm('examinationTypesDesc') ?></p>
                    </div>
                    <div class="relative h-64">
                        <canvas id="examinationChart" width="400" height="200"></canvas>
                    </div>
                </div>

                    <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2"><?= $tm('appointmentStatusTitle') ?></h3>
                        <p class="text-sm text-gray-600"><?= $tm('appointmentStatusDesc') ?></p>
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
                    <h3 class="text-lg font-bold text-gray-800"><?= $tm('previousPeriodComparison') ?></h3>
                    <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full"><?= esc($reports['comparison']['label'] ?? $tm('previousPeriodLabel')) ?></span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php
                    $comparisonCards = [
                        ['label' => $tm('newPatients'), 'current' => (float)($reports['summary']['new_patients'] ?? 0), 'previous' => (float)($cmp['new_patients'] ?? 0), 'currency' => false],
                        ['label' => $tm('examinations'), 'current' => (float)($reports['summary']['examinations_this_period'] ?? 0), 'previous' => (float)($cmp['examinations_this_period'] ?? 0), 'currency' => false],
                        ['label' => $tm('appointments'), 'current' => (float)($reports['summary']['appointments_this_period'] ?? 0), 'previous' => (float)($cmp['appointments_this_period'] ?? 0), 'currency' => false],
                        ['label' => $tm('revenue'), 'current' => (float)($reports['summary']['revenue_this_period'] ?? 0), 'previous' => (float)($cmp['revenue_this_period'] ?? 0), 'currency' => true],
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
                <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg group hover:shadow-xl transition-all duration-300 p-4">
                <div class="mb-4">
                    <?php
                        $reportTypeKeyMap = [
                            'overview' => 'reportOverview',
                            'patients' => 'reportPatients',
                            'examinations' => 'reportExaminations',
                            'appointments' => 'reportAppointments',
                            'finance' => 'reportFinance',
                            'treatments' => 'reportTreatments',
                        ];
                        $reportTitle = isset($reportTypeKeyMap[$report_type ?? ''])
                            ? $tm($reportTypeKeyMap[$report_type])
                            : ucfirst((string) ($report_type ?? $tm('reportTitleSuffix')));
                        $reportPeriodLabel = $filters['period_label'] ?? (($date_range ?? '30') . ' ' . $tm('days'));
                        $isArabicLocale = strpos(service('request')->getLocale(), 'ar') === 0;
                        $composedReportTitle = $isArabicLocale
                            ? ($tm('reportTitleSuffix') . ' ' . $reportTitle)
                            : ($reportTitle . ' ' . $tm('reportTitleSuffix'));
                    ?>
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?= esc($composedReportTitle) ?></h3>
                        <p class="text-sm text-gray-600"><?= esc($tm('reportDetailedAnalysis', ['period' => $reportPeriodLabel])) ?></p>
                    </div>
                    
                    <?php if ($report_type === 'patients'): ?>
                        <!-- Patients Report -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="bg-blue-50/80 rounded-lg p-3 border border-blue-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-users text-blue-600 text-sm"></i>
                                        <span class="text-xl font-bold text-blue-900"><?= count($reports['patients'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('totalPatients') ?></p>
                                </div>
                                <div class="bg-emerald-50/80 rounded-lg p-3 border border-emerald-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-user-plus text-emerald-600 text-sm"></i>
                                        <span class="text-xl font-bold text-emerald-900"><?= $reports['new_vs_returning']['new'] ?? 0 ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('new') ?></p>
                                </div>
                                <div class="bg-indigo-50/80 rounded-lg p-3 border border-indigo-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-user-check text-indigo-600 text-sm"></i>
                                        <span class="text-xl font-bold text-indigo-900"><?= $reports['new_vs_returning']['returning'] ?? 0 ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('returning') ?></p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/70">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-venus-mars text-slate-600 text-sm"></i>
                                        <span class="text-xl font-bold text-slate-800"><?= count($reports['gender_distribution'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('genderBuckets') ?></p>
                                </div>
                            </div>
                            <?php if (!empty($reports['gender_distribution'])): ?>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (array_slice($reports['gender_distribution'], 0, 5) as $gender): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-700">
                                        <?= esc(ucfirst((string) $gender['gender'])) ?>: <?= (int) $gender['count'] ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($report_type === 'examinations'): ?>
                        <!-- Examinations Report -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="bg-emerald-50/80 rounded-lg p-3 border border-emerald-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-stethoscope text-emerald-600 text-sm"></i>
                                        <span class="text-xl font-bold text-emerald-900"><?= count($reports['examinations'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('examinations') ?></p>
                                </div>
                                <div class="bg-blue-50/80 rounded-lg p-3 border border-blue-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-layer-group text-blue-600 text-sm"></i>
                                        <span class="text-xl font-bold text-blue-900"><?= count($reports['examination_types'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('type') ?></p>
                                </div>
                                <div class="bg-indigo-50/80 rounded-lg p-3 border border-indigo-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-calendar-check text-indigo-600 text-sm"></i>
                                        <span class="text-xl font-bold text-indigo-900"><?= count($reports['monthly_examinations'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('monthsTracked') ?></p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/70">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-user text-slate-600 text-sm"></i>
                                        <span class="text-xl font-bold text-slate-800"><?= !empty($filters['patient_id']) ? '1' : $tm('all') ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('patientScope') ?></p>
                                </div>
                            </div>
                            <?php if (!empty($reports['examination_types'])): ?>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (array_slice($reports['examination_types'], 0, 6) as $type): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-700">
                                        <?= esc(ucfirst((string) $type['examination_type'])) ?>: <?= (int) ($type['count'] ?? 0) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($report_type === 'appointments'): ?>
                        <!-- Appointments Report -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="bg-amber-50/80 rounded-lg p-3 border border-amber-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-calendar-check text-amber-600 text-sm"></i>
                                        <span class="text-xl font-bold text-amber-900"><?= count($reports['appointments'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('appointments') ?></p>
                                </div>
                                <div class="bg-blue-50/80 rounded-lg p-3 border border-blue-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-signal text-blue-600 text-sm"></i>
                                        <span class="text-xl font-bold text-blue-900"><?= count($reports['status_distribution'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('statuses') ?></p>
                                </div>
                                <div class="bg-indigo-50/80 rounded-lg p-3 border border-indigo-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-tags text-indigo-600 text-sm"></i>
                                        <span class="text-xl font-bold text-indigo-900"><?= count($reports['appointment_types'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('type') ?></p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/70">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-user text-slate-600 text-sm"></i>
                                        <span class="text-xl font-bold text-slate-800"><?= !empty($filters['patient_id']) ? '1' : $tm('all') ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('patientScope') ?></p>
                                </div>
                            </div>
                            <?php if (!empty($reports['status_distribution'])): ?>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (array_slice($reports['status_distribution'], 0, 6) as $status): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-700">
                                        <?= esc(ucfirst((string) $status['status'])) ?>: <?= (int) ($status['count'] ?? 0) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($report_type === 'finance'): ?>
                        <!-- Finance Report -->
                        <div class="space-y-6">
                            <?php
                                $summary = $reports['summary'] ?? [];
                                $financeTransactionTable = null;
                                foreach (($detail_tables ?? []) as $table) {
                                    if (($table['id'] ?? '') === 'financial_transactions') {
                                        $financeTransactionTable = $table;
                                        break;
                                    }
                                }
                            ?>
                            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-500 text-white flex items-center justify-center shrink-0">
                                        <i class="fas fa-circle-info"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800"><?= $tm('financeReportBasisTitle') ?></p>
                                        <p class="text-sm text-slate-600"><?= $tm('financeReportBasisDesc') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800"><?= $tm('ownerSummaryTitle') ?></h4>
                                    <p class="text-sm text-gray-500"><?= esc($tm('ownerSummaryHelp', ['date' => $summary['as_of_date'] ?? ($filters['end_date'] ?? date('Y-m-d'))])) ?></p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                                    <div class="rounded-lg p-4 border border-green-100 bg-green-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-arrow-down-wide-short"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('moneyIn') ?></p>
                                                <p class="text-xl font-bold text-green-900"><?= formatCurrency($summary['money_in'] ?? 0) ?></p>
                                                <p class="mt-1 text-xs text-slate-500"><?= $tm('moneyInHelp') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border border-rose-100 bg-rose-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-rose-600 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-arrow-up-wide-short"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('moneyOut') ?></p>
                                                <p class="text-xl font-bold text-rose-900"><?= formatCurrency($summary['money_out'] ?? 0) ?></p>
                                                <p class="mt-1 text-xs text-slate-500"><?= $tm('moneyOutHelp') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border border-amber-100 bg-amber-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('customersOweUs') ?></p>
                                                <p class="text-xl font-bold text-amber-900"><?= formatCurrency($summary['customers_owe_us'] ?? 0) ?></p>
                                                <p class="mt-1 text-xs text-slate-500"><?= esc($tm('customersOweUsHelp', ['date' => $summary['as_of_date'] ?? ($filters['end_date'] ?? date('Y-m-d'))])) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border border-indigo-100 bg-indigo-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-hand-holding-dollar"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('weOweOthers') ?></p>
                                                <p class="text-xl font-bold text-indigo-900"><?= formatCurrency($summary['we_owe_others'] ?? 0) ?></p>
                                                <p class="mt-1 text-xs text-slate-500"><?= esc($tm('weOweOthersHelp', ['date' => $summary['as_of_date'] ?? ($filters['end_date'] ?? date('Y-m-d'))])) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border border-slate-200 bg-slate-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-slate-900 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-scale-balanced"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('netPosition') ?></p>
                                                <p class="text-xl font-bold <?= ((float)($summary['net_position'] ?? 0)) >= 0 ? 'text-slate-900' : 'text-rose-700' ?>"><?= formatCurrency($summary['net_position'] ?? 0) ?></p>
                                                <p class="mt-1 text-xs text-slate-500"><?= $tm('netPositionHelp') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <details class="group rounded-xl border border-gray-200 bg-white shadow-lg">
                                <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-5">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800"><?= $tm('detailsBreakdownTitle') ?></h4>
                                        <p class="text-sm text-gray-500"><?= $tm('detailsBreakdownHelp') ?></p>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        <i class="fas fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
                                    </span>
                                </summary>
                                <div class="space-y-6 border-t border-gray-200 px-6 py-6">
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="rounded-lg p-4 border border-green-100 bg-green-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-wallet"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('grossCollections') ?></p>
                                                <p class="text-xl font-bold text-green-900"><?= formatCurrency($summary['gross_collections'] ?? 0) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border border-yellow-100 bg-yellow-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-money-bill-transfer"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('providerPaidCash') ?></p>
                                                <p class="text-xl font-bold text-yellow-900"><?= formatCurrency($summary['provider_paid_cash'] ?? 0) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border border-purple-100 bg-purple-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-chart-line"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('netClinicCash') ?></p>
                                                <p class="text-xl font-bold text-purple-900"><?= formatCurrency($summary['net_clinic_revenue_cash'] ?? 0) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border border-rose-100 bg-rose-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-rose-600 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= $tm('expensesPaid') ?></p>
                                                <p class="text-xl font-bold text-rose-900"><?= formatCurrency($summary['total_expenses'] ?? 0) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                                        <i class="fas fa-cash-register"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800"><?= $tm('safeCashMovementTitle') ?></h4>
                                        <p class="text-sm text-gray-500"><?= $tm('safeCashMovementHelp') ?></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                                        <p class="text-xs uppercase tracking-wide text-emerald-700 font-semibold"><?= $tm('safeCashIn') ?></p>
                                        <p class="mt-2 text-2xl font-bold text-emerald-950"><?= formatCurrency($summary['safe_cash_in'] ?? 0) ?></p>
                                        <p class="mt-1 text-xs text-slate-600"><?= $tm('safeCashInHelp') ?></p>
                                    </div>
                                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
                                        <p class="text-xs uppercase tracking-wide text-rose-700 font-semibold"><?= $tm('safeCashOut') ?></p>
                                        <p class="mt-2 text-2xl font-bold text-rose-950"><?= formatCurrency($summary['safe_cash_out'] ?? 0) ?></p>
                                        <p class="mt-1 text-xs text-slate-600"><?= $tm('safeCashOutHelp') ?></p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-wide text-slate-700 font-semibold"><?= $tm('safeCashNet') ?></p>
                                        <p class="mt-2 text-2xl font-bold <?= ((float)($summary['safe_cash_net'] ?? 0)) >= 0 ? 'text-slate-950' : 'text-rose-700' ?>"><?= formatCurrency($summary['safe_cash_net'] ?? 0) ?></p>
                                        <p class="mt-1 text-xs text-slate-600"><?= $tm('safeCashNetHelp') ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= $tm('outstandingInvoicesAmount') ?></p>
                                    <p class="mt-1 text-lg font-bold text-amber-700"><?= formatCurrency($summary['pending_invoice_amount'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= $tm('outstandingInvoices') ?></p>
                                    <p class="mt-1 text-lg font-bold text-indigo-700"><?= (int) ($summary['outstanding_invoices_count'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= $tm('transactionsPeriod') ?></p>
                                    <p class="mt-1 text-lg font-bold text-slate-800"><?= (int) ($summary['total_transactions'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= $tm('operatingResultCash') ?></p>
                                    <p class="mt-1 text-lg font-bold <?= ((float)($summary['operating_result_cash'] ?? 0)) >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>"><?= formatCurrency($summary['operating_result_cash'] ?? 0) ?></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= $tm('refundCount') ?></p>
                                    <p class="mt-1 text-lg font-bold text-rose-700"><?= (int) ($summary['refund_count'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= $tm('refundTotal') ?></p>
                                    <p class="mt-1 text-lg font-bold text-rose-700"><?= formatCurrency($summary['refund_total'] ?? 0) ?></p>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= $tm('reportBasis') ?></p>
                                    <p class="mt-1 text-lg font-bold text-sky-700"><?= $tm('cashBasis') ?></p>
                                </div>
                            </div>
                            <?php if (!empty($reports['comparison']['summary'] ?? [])): ?>
                            <?php $fcmp = $reports['comparison']['summary']; ?>
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800"><?= $tm('previousPeriodComparison') ?></h4>
                                    <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full"><?= esc($reports['comparison']['label'] ?? 'Previous period') ?></span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                    <?php
                                    $financeCompareCards = [
                                        ['label'=>$tm('moneyIn'),'current'=>(float)($summary['money_in']??0),'previous'=>(float)($fcmp['money_in']??0),'count'=>false],
                                        ['label'=>$tm('moneyOut'),'current'=>(float)($summary['money_out']??0),'previous'=>(float)($fcmp['money_out']??0),'count'=>false],
                                        ['label'=>$tm('customersOweUs'),'current'=>(float)($summary['customers_owe_us']??0),'previous'=>(float)($fcmp['customers_owe_us']??0),'count'=>false],
                                        ['label'=>$tm('weOweOthers'),'current'=>(float)($summary['we_owe_others']??0),'previous'=>(float)($fcmp['we_owe_others']??0),'count'=>false],
                                        ['label'=>$tm('netPosition'),'current'=>(float)($summary['net_position']??0),'previous'=>(float)($fcmp['net_position']??0),'count'=>false],
                                        ['label'=>$tm('transactionsPeriod'),'current'=>(float)($summary['total_transactions']??0),'previous'=>(float)($fcmp['total_transactions']??0),'count'=>true],
                                    ];
                                    foreach ($financeCompareCards as $card):
                                        $delta = $card['current'] - $card['previous'];
                                        $up = $delta >= 0;
                                    ?>
                                    <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($card['label']) ?></p>
                                        <p class="mt-1 font-bold text-gray-900"><?= $card['count'] ? number_format($card['current']) : formatCurrency($card['current']) ?></p>
                                        <p class="mt-1 text-xs <?= $up ? 'text-emerald-600' : 'text-rose-600' ?> font-semibold"><?= $up ? '+' : '-' ?> <?= $card['count'] ? number_format(abs($delta)) : formatCurrency(abs($delta)) ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Payment Methods -->
                            <?php if (!empty($reports['payment_methods'])): ?>
                            <div class="bg-gray-50/80 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4"><?= $tm('paymentMethods') ?></h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($reports['payment_methods'] as $method): ?>
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                        <span class="font-medium text-gray-700"><?= esc($financeMethodLabel($method['payment_method'] ?? '')) ?></span>
                                        <span class="text-lg font-bold text-green-600"><?= formatCurrency($method['total_amount']) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-800"><?= $tm('paymentMethodMix') ?></h4>
                                        <p class="text-sm text-gray-500"><?= $tm('paymentMethodMixDesc') ?></p>
                                    </div>
                                    <div class="relative h-64">
                                        <canvas id="financePaymentMethodsChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-800"><?= $tm('arAgingDistribution') ?></h4>
                                        <p class="text-sm text-gray-500"><?= $tm('arAgingDistributionDesc') ?></p>
                                    </div>
                                    <div class="relative h-64">
                                        <canvas id="financeAgingChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm xl:col-span-2">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-gray-800"><?= $tm('transactionTypeMix') ?></h4>
                                        <p class="text-sm text-gray-500"><?= $tm('transactionTypeMixDesc') ?></p>
                                    </div>
                                    <div class="relative h-64">
                                        <canvas id="financeTransactionTypeChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($reports['ar_aging_summary'])): ?>
                            <?php $ar = $reports['ar_aging_summary']; ?>
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800"><?= $tm('accountsReceivableAging') ?></h4>
                                        <p class="text-sm text-gray-500"><?= esc($tm('accountsReceivableAgingDesc', ['date' => ($filters['end_date'] ?? date('Y-m-d'))])) ?></p>
                                    </div>
                                    <div class="text-sm font-semibold text-rose-700"><?= $tm('totalAmount') ?>: <?= formatCurrency($ar['total_balance_due'] ?? 0) ?></div>
                                </div>
                                <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
                                    <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-500"><?= $tm('current') ?></p>
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
                                        <p class="text-xs uppercase tracking-wide text-indigo-700"><?= $tm('invoices') ?></p>
                                        <p class="text-lg font-bold text-indigo-900"><?= (int) ($ar['invoice_count'] ?? 0) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                                </div>
                            </details>

                            <?php if (!empty($reports['ar_aging_detail'])): ?>
                            <div class="bg-white/90 rounded-xl p-5 border border-gray-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800"><?= $tm('arAgingDetail') ?></h4>
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= esc($tm('openInvoicesTop', ['count' => count($reports['ar_aging_detail'])])) ?></span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-gray-500 border-b">
                                                <th class="py-2 pr-3"><?= $tm('invoice') ?></th>
                                                <th class="py-2 pr-3"><?= $tm('patient') ?></th>
                                                <th class="py-2 pr-3"><?= $tm('dueDate') ?></th>
                                                <th class="py-2 pr-3"><?= $tm('daysPastDue') ?></th>
                                                <th class="py-2 pr-3"><?= $tm('bucket') ?></th>
                                                <th class="py-2 pr-3 text-right"><?= $tm('balanceDue') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <?php foreach ($reports['ar_aging_detail'] as $row): ?>
                                            <tr class="cursor-pointer transition-colors hover:bg-amber-50/60" data-href="<?= esc(base_url('finance/' . (int) ($row['id'] ?? 0)), 'attr') ?>">
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
                                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700"><?= esc($agingBucketLabel($row['aging_bucket'] ?? 'current')) ?></span>
                                                </td>
                                                <td class="py-2 pr-3 text-right font-semibold text-rose-700"><?= formatCurrency($row['balance_due'] ?? 0) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($financeTransactionTable['rows'] ?? [])): ?>
                            <div class="rounded-xl border border-gray-200 bg-white shadow-lg">
                                <div class="border-b border-gray-200 px-6 py-5">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <h4 class="text-xl font-bold text-gray-900"><?= $tm('unifiedFinancialLedger') ?></h4>
                                            <p class="mt-1 text-sm text-gray-600"><?= $tm('unifiedLedgerHelp') ?></p>
                                        </div>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= (int) ($financeTransactionTable['row_count'] ?? count($financeTransactionTable['rows'] ?? [])) ?> <?= $tm('rows') ?></span>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                <?php foreach (($financeTransactionTable['columns'] ?? []) as $column): ?>
                                                <th class="px-6 py-4 whitespace-nowrap"><?= esc($column['label'] ?? ($column['key'] ?? '')) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            <?php foreach (($financeTransactionTable['rows'] ?? []) as $row): ?>
                                            <tr class="<?= !empty($row['row_href'] ?? '') ? 'cursor-pointer transition-colors duration-150 hover:bg-slate-50' : '' ?>"<?= !empty($row['row_href'] ?? '') ? ' data-href="' . esc((string) $row['row_href'], 'attr') . '"' : '' ?>>
                                                <?php foreach (($financeTransactionTable['columns'] ?? []) as $column): ?>
                                                <?php $key = $column['key'] ?? ''; $value = $row[$key] ?? ''; ?>
                                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                                    <?php if (is_numeric($value) && preg_match('/amount|balance|total|share|paid/i', (string) $key)): ?>
                                                        <span class="font-semibold"><?= formatCurrency($value) ?></span>
                                                    <?php else: ?>
                                                        <?= esc((string) $value) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <?php endforeach; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                        
                    <?php elseif ($report_type === 'treatments'): ?>
                        <!-- Treatments Report -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="bg-rose-50/80 rounded-lg p-3 border border-rose-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-tooth text-rose-600 text-sm"></i>
                                        <span class="text-xl font-bold text-rose-900"><?= count($reports['treatments'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('reportTreatments') ?></p>
                                </div>
                                <div class="bg-blue-50/80 rounded-lg p-3 border border-blue-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-tags text-blue-600 text-sm"></i>
                                        <span class="text-xl font-bold text-blue-900"><?= count($reports['treatment_types'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('type') ?></p>
                                </div>
                                <div class="bg-indigo-50/80 rounded-lg p-3 border border-indigo-100/60">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-wave-square text-indigo-600 text-sm"></i>
                                        <span class="text-xl font-bold text-indigo-900"><?= count($reports['status_distribution'] ?? []) ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('statuses') ?></p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/70">
                                    <div class="flex items-center justify-between">
                                        <i class="fas fa-user text-slate-600 text-sm"></i>
                                        <span class="text-xl font-bold text-slate-800"><?= !empty($filters['patient_id']) ? '1' : $tm('all') ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-700 uppercase tracking-wide"><?= $tm('patientScope') ?></p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (array_slice(($reports['status_distribution'] ?? []), 0, 4) as $status): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-700">
                                        <?= esc(ucfirst((string) $status['status'])) ?>: <?= (int) ($status['count'] ?? 0) ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php foreach (array_slice(($reports['treatment_types'] ?? []), 0, 4) as $type): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-700">
                                        <?= esc(ucfirst((string) $type['treatment_type'])) ?>: <?= (int) ($type['count'] ?? 0) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Default fallback -->
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-chart-bar text-blue-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= $tm('reportData') ?></h3>
                    <p class="text-gray-500"><?= $tm('reportUnderDevelopment') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($detail_tables ?? []) && $activeReportType !== 'finance'): ?>
        <div class="mb-8">
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800"><?= lang('TenantMisc.rowsBehindReport') ?></h3>
                        <p class="text-sm text-gray-600"><?= lang('TenantMisc.rowsBehindReportDesc') ?></p>
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
                        <span id="reportDrillBadgeText"><?= lang('TenantMisc.drillthroughActive') ?></span>
                        <button type="button" id="clearDrillthroughBtn" class="inline-flex items-center px-2 py-1 rounded bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-50 text-xs font-semibold">
                            <?= lang('TenantMisc.clear') ?>
                        </button>
                    </div>
                </div>

                <?php foreach (($detail_tables ?? []) as $idx => $table): ?>
                <div class="report-detail-panel <?= $idx === 0 ? '' : 'hidden' ?>" data-panel="<?= esc($table['id']) ?>">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <h4 class="text-lg font-semibold text-gray-800"><?= esc($table['title']) ?></h4>
                        <div class="flex items-center gap-2">
                            <select class="report-detail-per-page px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="25">25 <?= lang('TenantMisc.rows') ?></option>
                                <option value="50">50 <?= lang('TenantMisc.rows') ?></option>
                                <option value="100">100 <?= lang('TenantMisc.rows') ?></option>
                            </select>
                            <div class="relative">
                            <input type="text" class="report-detail-search w-full md:w-72 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="<?= lang('TenantMisc.searchRowsPlaceholder') ?>">
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
                                    <td colspan="<?= count($table['columns'] ?? []) ?>" class="px-4 py-8 text-center text-gray-500"><?= lang('TenantMisc.noRowsForPeriod') ?></td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($table['rows'] as $row): ?>
                                <tr class="report-detail-row <?= !empty($row['row_href'] ?? '') ? 'cursor-pointer hover:bg-blue-50/50' : 'hover:bg-blue-50/30' ?> transition-colors"<?= !empty($row['row_href'] ?? '') ? ' data-href="' . esc((string) $row['row_href'], 'attr') . '"' : '' ?>>
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
                        <div class="text-sm text-gray-500 report-detail-pagination-info"><?= lang('TenantMisc.showing') ?> <?= (int) min(($table['row_count'] ?? 0), 25) ?> <?= lang('TenantMisc.of') ?> <?= (int) ($table['row_count'] ?? 0) ?> <?= lang('TenantMisc.rows') ?></div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="report-detail-prev px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:border-blue-300 disabled:opacity-50 disabled:cursor-not-allowed"><?= lang('TenantMisc.previous') ?></button>
                            <span class="report-detail-page-label text-sm text-gray-600 min-w-[110px] text-center"><?= lang('TenantMisc.page') ?> 1</span>
                            <button type="button" class="report-detail-next px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:border-blue-300 disabled:opacity-50 disabled:cursor-not-allowed"><?= lang('TenantMisc.next') ?></button>
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

const reportI18n = {
    noRowsMatch: '<?= esc(lang('TenantMisc.noRowsMatchFilters'), 'js') ?>',
    showing: '<?= esc(lang('TenantMisc.showing'), 'js') ?>',
    of: '<?= esc(lang('TenantMisc.of'), 'js') ?>',
    rows: '<?= esc(lang('TenantMisc.rows'), 'js') ?>',
    page: '<?= esc(lang('TenantMisc.page'), 'js') ?>',
    failedLoadRows: '<?= esc(lang('TenantMisc.failedLoadRowsRetry'), 'js') ?>',
    revenue: '<?= esc(lang('TenantMisc.revenue'), 'js') ?>',
    unknown: '<?= esc(lang('TenantMisc.unknown'), 'js') ?>',
    appointments: '<?= esc(lang('TenantMisc.appointments'), 'js') ?>',
    balanceDue: '<?= esc(lang('TenantMisc.balanceDue'), 'js') ?>',
    totalAmount: '<?= esc(lang('TenantMisc.totalAmount'), 'js') ?>',
    drillthroughPrefix: '<?= esc(lang('TenantMisc.drillthroughPrefix'), 'js') ?>',
    revenueChartDrill: '<?= esc(lang('TenantMisc.revenueChartDrill'), 'js') ?>',
    examinationTypeDrill: '<?= esc(lang('TenantMisc.examinationTypeDrill'), 'js') ?>',
    appointmentStatusDrill: '<?= esc(lang('TenantMisc.appointmentStatusDrill'), 'js') ?>',
    paymentMethodDrill: '<?= esc(lang('TenantMisc.paymentMethodDrill'), 'js') ?>',
    arAgingDrill: '<?= esc(lang('TenantMisc.arAgingDrill'), 'js') ?>',
    transactionTypeDrill: '<?= esc(lang('TenantMisc.transactionTypeDrill'), 'js') ?>',
    current: '<?= esc(lang('TenantMisc.current'), 'js') ?>',
    days1to30: '<?= esc(lang('TenantMisc.days1to30'), 'js') ?>',
    days31to60: '<?= esc(lang('TenantMisc.days31to60'), 'js') ?>',
    days61to90: '<?= esc(lang('TenantMisc.days61to90'), 'js') ?>',
    days91Plus: '<?= esc(lang('TenantMisc.days91Plus'), 'js') ?>',
    transactionTypeLabels: <?= json_encode([
        'payment' => $tm('payment'),
        'expense' => $tm('expense'),
        'refund' => $tm('refund'),
        'void' => $tm('void'),
        'provider_payout' => $tm('providerPayoutCash'),
        'unknown' => $tm('unknown'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    paymentMethodLabels: <?= json_encode([
        'cash' => $tm('cash'),
        'card' => $tm('card'),
        'bank_transfer' => $tm('bankTransfer'),
        'check' => $tm('check'),
        'insurance' => $tm('insurance'),
        'other' => $tm('other'),
        'unknown' => $tm('unknown'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
};

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function syncReportFilterVisibility() {
    const reportType = document.getElementById('reportType')?.value || 'overview';
    const transactionTypeWrap = document.getElementById('transactionTypeWrap');
    const transactionTypeFilter = document.getElementById('transactionTypeFilter');
    const paymentMethodWrap = document.getElementById('paymentMethodWrap');
    const examinationTypeWrap = document.getElementById('examinationTypeWrap');
    const appointmentStatusWrap = document.getElementById('appointmentStatusWrap');
    const appointmentTypeWrap = document.getElementById('appointmentTypeWrap');
    const treatmentStatusWrap = document.getElementById('treatmentStatusWrap');
    const treatmentTypeWrap = document.getElementById('treatmentTypeWrap');
    const paymentMethodFilter = document.getElementById('paymentMethodFilter');
    const examinationTypeFilter = document.getElementById('examinationTypeFilter');
    const appointmentStatusFilter = document.getElementById('appointmentStatusFilter');
    const appointmentTypeFilter = document.getElementById('appointmentTypeFilter');
    const treatmentStatusFilter = document.getElementById('treatmentStatusFilter');
    const treatmentTypeFilter = document.getElementById('treatmentTypeFilter');

    const isFinance = reportType === 'finance';
    const isExaminations = reportType === 'examinations';
    const isAppointments = reportType === 'appointments';
    const isTreatments = reportType === 'treatments';

    if (transactionTypeWrap) {
        transactionTypeWrap.classList.toggle('opacity-50', !isFinance);
    }
    if (transactionTypeFilter) {
        transactionTypeFilter.disabled = !isFinance;
        if (!isFinance) {
            transactionTypeFilter.value = '';
        }
    }
    if (paymentMethodWrap) paymentMethodWrap.classList.toggle('hidden', !isFinance);
    if (examinationTypeWrap) examinationTypeWrap.classList.toggle('hidden', !isExaminations);
    if (appointmentStatusWrap) appointmentStatusWrap.classList.toggle('hidden', !isAppointments);
    if (appointmentTypeWrap) appointmentTypeWrap.classList.toggle('hidden', !isAppointments);
    if (treatmentStatusWrap) treatmentStatusWrap.classList.toggle('hidden', !isTreatments);
    if (treatmentTypeWrap) treatmentTypeWrap.classList.toggle('hidden', !isTreatments);

    if (!isFinance) {
        if (paymentMethodFilter) paymentMethodFilter.value = '';
    }
    if (!isExaminations && examinationTypeFilter) examinationTypeFilter.value = '';
    if (!isAppointments) {
        if (appointmentStatusFilter) appointmentStatusFilter.value = '';
        if (appointmentTypeFilter) appointmentTypeFilter.value = '';
    }
    if (!isTreatments) {
        if (treatmentStatusFilter) treatmentStatusFilter.value = '';
        if (treatmentTypeFilter) treatmentTypeFilter.value = '';
    }
}

function applyQuickDateRange(days, force = false) {
    const isCustomMode = (reportWorkspace.filters.date_mode || 'relative') === 'custom';
    const hasCustomRange = Boolean(reportWorkspace.filters.custom_from && reportWorkspace.filters.custom_to);
    if (!force && isCustomMode && hasCustomRange) {
        return;
    }

    const toIso = new Date().toISOString().slice(0, 10);
    const fromDate = new Date();
    fromDate.setDate(fromDate.getDate() - Number(days || 30));
    const fromIso = fromDate.toISOString().slice(0, 10);
    const fromDisplay = window.formatClinicDate ? window.formatClinicDate(fromIso) : fromIso;
    const toDisplay = window.formatClinicDate ? window.formatClinicDate(toIso) : toIso;
    const fromInput = document.getElementById('customFrom');
    const toInput = document.getElementById('customTo');
    if (fromInput) {
        fromInput.value = fromDisplay;
    }
    if (toInput) {
        toInput.value = toDisplay;
    }
}

function collectSpecificFilters(reportType) {
    const payload = {
        patient_id: document.getElementById('patientFilter')?.value || '',
        transaction_type: '',
        payment_method: '',
        examination_type: '',
        appointment_status: '',
        appointment_type: '',
        treatment_status: '',
        treatment_type: '',
    };
    if (reportType === 'finance') {
        payload.transaction_type = document.getElementById('transactionTypeFilter')?.value || '';
        payload.payment_method = document.getElementById('paymentMethodFilter')?.value || '';
    } else if (reportType === 'examinations') {
        payload.examination_type = document.getElementById('examinationTypeFilter')?.value || '';
    } else if (reportType === 'appointments') {
        payload.appointment_status = document.getElementById('appointmentStatusFilter')?.value || '';
        payload.appointment_type = document.getElementById('appointmentTypeFilter')?.value || '';
    } else if (reportType === 'treatments') {
        payload.treatment_status = document.getElementById('treatmentStatusFilter')?.value || '';
        payload.treatment_type = document.getElementById('treatmentTypeFilter')?.value || '';
    }
    return payload;
}

function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    const specific = collectSpecificFilters(reportType);
    const customFromRaw = document.getElementById('customFrom')?.value || '';
    const customToRaw = document.getElementById('customTo')?.value || '';
    const customFrom = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customFromRaw) || '') : customFromRaw;
    const customTo = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customToRaw) || '') : customToRaw;
    
    const url = new URL(window.location);
    url.searchParams.set('report_type', reportType);
    url.searchParams.set('date_range', dateRange);
    url.searchParams.set('date_mode', 'custom');
    url.searchParams.set('compare_mode', 'none');
    if (customFrom) url.searchParams.set('custom_from', customFrom); else url.searchParams.delete('custom_from');
    if (customTo) url.searchParams.set('custom_to', customTo); else url.searchParams.delete('custom_to');
    Object.entries(specific).forEach(([key, value]) => {
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
    });
    
    window.location.href = url.toString();
}

function exportReport(format) {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    const specific = collectSpecificFilters(reportType);
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
        date_mode: 'custom',
        compare_mode: 'none',
        custom_from: customFrom,
        custom_to: customTo,
        patient_id: specific.patient_id,
        transaction_type: specific.transaction_type,
        payment_method: specific.payment_method,
        examination_type: specific.examination_type,
        appointment_status: specific.appointment_status,
        appointment_type: specific.appointment_type,
        treatment_status: specific.treatment_status,
        treatment_type: specific.treatment_type,
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

document.getElementById('reportType')?.addEventListener('change', syncReportFilterVisibility);
document.getElementById('dateRange')?.addEventListener('change', (event) => {
    const fromInput = document.getElementById('customFrom');
    const toInput = document.getElementById('customTo');
    if (fromInput) fromInput.value = '';
    if (toInput) toInput.value = '';
    applyQuickDateRange(event.target.value, true);
});
syncReportFilterVisibility();
applyQuickDateRange(document.getElementById('dateRange')?.value || '30');

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
    text.textContent = `${reportI18n.drillthroughPrefix}: ${reportDrillState.label}`;
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

document.addEventListener('click', function(event) {
    const row = event.target.closest('tr[data-href]');
    if (!row || event.target.closest('a, button, input, select, textarea, label')) {
        return;
    }
    const href = row.getAttribute('data-href');
    if (href) {
        window.location.href = href;
    }
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
        tbody.innerHTML = `<tr><td colspan="${columns.length}" class="px-4 py-8 text-center text-gray-500">${reportI18n.noRowsMatch}</td></tr>`;
    } else {
        tbody.innerHTML = rows.map((row) => {
            const cells = columns.map((col) => {
                const key = col.key || '';
                const raw = row[key] ?? '';
                const isMoney = /amount|balance|total|share|paid/i.test(key) && !Number.isNaN(parseFloat(raw));
                const rendered = isMoney ? formatCurrency(raw) : escapeHtml(raw);
                return `<td class="px-4 py-3 text-gray-700 whitespace-nowrap">${isMoney ? `<span class="font-semibold">${rendered}</span>` : rendered}</td>`;
            }).join('');
            const rowHref = row.row_href || '';
            const rowClass = rowHref ? 'report-detail-row cursor-pointer hover:bg-blue-50/50 transition-colors' : 'report-detail-row hover:bg-blue-50/30 transition-colors';
            const hrefAttr = rowHref ? ` data-href="${escapeHtml(rowHref)}"` : '';
            return `<tr class="${rowClass}"${hrefAttr}>${cells}</tr>`;
        }).join('');
    }

    const from = pagination.from ?? 0;
    const to = pagination.to ?? 0;
    const total = pagination.total_rows ?? rows.length;
    const page = pagination.page ?? 1;
    const lastPage = pagination.last_page ?? 1;
    info.textContent = `${reportI18n.showing} ${from}-${to} ${reportI18n.of} ${total} ${reportI18n.rows}`;
    pageLabel.textContent = `${reportI18n.page} ${page} ${reportI18n.of} ${lastPage}`;
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
    if (reportWorkspace.filters.patient_id) params.set('patient_id', reportWorkspace.filters.patient_id);
    if (reportWorkspace.filters.transaction_type) params.set('transaction_type', reportWorkspace.filters.transaction_type);
    if (reportWorkspace.filters.payment_method) params.set('payment_method', reportWorkspace.filters.payment_method);
    if (reportWorkspace.filters.examination_type) params.set('examination_type', reportWorkspace.filters.examination_type);
    if (reportWorkspace.filters.appointment_status) params.set('appointment_status', reportWorkspace.filters.appointment_status);
    if (reportWorkspace.filters.appointment_type) params.set('appointment_type', reportWorkspace.filters.appointment_type);
    if (reportWorkspace.filters.treatment_status) params.set('treatment_status', reportWorkspace.filters.treatment_status);
    if (reportWorkspace.filters.treatment_type) params.set('treatment_type', reportWorkspace.filters.treatment_type);
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
            tbody.innerHTML = `<tr><td colspan="${colspan}" class="px-4 py-8 text-center text-rose-600">${reportI18n.failedLoadRows}</td></tr>`;
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
                label: reportI18n.revenue,
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
                applyDrillthrough('overview_finance_transactions', month.toLowerCase(), `${reportI18n.revenueChartDrill}: ${month}`);
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
    const labels = examinationData.map(item => item.examination_type || reportI18n.unknown);
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
                applyDrillthrough('overview_examinations', type.toLowerCase(), `${reportI18n.examinationTypeDrill}: ${type}`);
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
    const labels = appointmentData.map(item => item.status || reportI18n.unknown);
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
                label: reportI18n.appointments,
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
                applyDrillthrough('overview_appointments', status.toLowerCase(), `${reportI18n.appointmentStatusDrill}: ${status}`);
            }
        }
    });
}

// Finance: Payment Method Mix
const financePaymentMethodsCtx = document.getElementById('financePaymentMethodsChart')?.getContext('2d');
if (financePaymentMethodsCtx) {
    const rows = <?= json_encode($reports['payment_methods'] ?? []) ?>;
    const rawLabels = rows.map(r => (r.payment_method || 'unknown').toString().toLowerCase());
    const labels = rawLabels.map(key => reportI18n.paymentMethodLabels[key] || key);
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
                const method = rawLabels[elements[0].index];
                const methodLabel = labels[elements[0].index];
                if (!method) return;
                applyDrillthrough('transactions', method, `${reportI18n.paymentMethodDrill}: ${methodLabel}`);
            }
        }
    });
}

// Finance: A/R Aging Distribution
const financeAgingCtx = document.getElementById('financeAgingChart')?.getContext('2d');
if (financeAgingCtx) {
    const ar = <?= json_encode($reports['ar_aging_summary'] ?? []) ?>;
    const agingBuckets = [
        { key: 'current', label: reportI18n.current, color: '#94a3b8' },
        { key: 'days_1_30', label: reportI18n.days1to30, color: '#fbbf24' },
        { key: 'days_31_60', label: reportI18n.days31to60, color: '#fb923c' },
        { key: 'days_61_90', label: reportI18n.days61to90, color: '#fb7185' },
        { key: 'days_91_plus', label: reportI18n.days91Plus, color: '#ef4444' },
    ];
    const labels = agingBuckets.map(b => b.label);
    const data = agingBuckets.map(b => parseFloat(ar[b.key] || 0));

    new Chart(financeAgingCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: reportI18n.balanceDue,
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
                applyDrillthrough('ar_aging_detail', bucket.key.toLowerCase(), `${reportI18n.arAgingDrill}: ${bucket.label}`);
            }
        }
    });
}

// Finance: Transaction Type Mix
const financeTxnTypeCtx = document.getElementById('financeTransactionTypeChart')?.getContext('2d');
if (financeTxnTypeCtx) {
    const rows = <?= json_encode($reports['transaction_type_mix'] ?? []) ?>;
    const rawLabels = rows.map(r => (r.transaction_type || 'unknown').toString().toLowerCase());
    const labels = rawLabels.map(key => reportI18n.transactionTypeLabels[key] || key);
    const data = rows.map(r => parseFloat(r.total_amount || 0));
    const colors = labels.map((type) => ({
        payment: '#10b981',
        refund: '#ef4444',
        expense: '#f97316',
        void: '#64748b',
        provider_payout: '#eab308',
    }[(type || '').toLowerCase()] || '#64748b'));

    new Chart(financeTxnTypeCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: reportI18n.totalAmount,
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
                const type = rawLabels[elements[0].index];
                const typeLabel = labels[elements[0].index];
                if (!type) return;
                applyDrillthrough('transactions', type, `${reportI18n.transactionTypeDrill}: ${typeLabel}`);
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



