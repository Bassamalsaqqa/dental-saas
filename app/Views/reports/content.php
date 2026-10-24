<div class="v3-operational-view">

<?php
$tm = static fn(string $key, array $params = []) => lang('TenantMisc.' . $key, $params);
$tf = static fn(string $key, array $params = []) => lang('Finance.' . $key, $params);
$activeReportType = $report_type ?? 'overview';
$request = service('request');
$workspaceBaseQuery = $request->getGet();
$workspaceBaseQuery = is_array($workspaceBaseQuery) ? $workspaceBaseQuery : [];
$workspaceCards = [
    ['type' => 'overview', 'icon' => 'fa-chart-pie', 'title' => $tm('reportOverview'), 'desc' => $tm('reportOverviewDesc')],
    ['type' => 'patients', 'icon' => 'fa-users', 'title' => $tm('reportPatients'), 'desc' => $tm('reportPatientsDesc')],
    ['type' => 'examinations', 'icon' => 'fa-stethoscope', 'title' => $tm('reportExaminations'), 'desc' => $tm('reportExaminationsDesc')],
    ['type' => 'appointments', 'icon' => 'fa-calendar-check', 'title' => $tm('reportAppointments'), 'desc' => $tm('reportAppointmentsDesc')],
    ['type' => 'treatments', 'icon' => 'fa-tooth', 'title' => $tm('reportTreatments'), 'desc' => $tm('reportTreatmentsDesc')],
];
$currentMeta = [
    'overview' => ['headline' => $tm('reportOverviewHeadline'), 'summary' => $tm('reportOverviewSummary')],
    'patients' => ['headline' => $tm('reportPatientsHeadline'), 'summary' => $tm('reportPatientsSummary')],
    'examinations' => ['headline' => $tm('reportExaminationsHeadline'), 'summary' => $tm('reportExaminationsSummary')],
    'appointments' => ['headline' => $tm('reportAppointmentsHeadline'), 'summary' => $tm('reportAppointmentsSummary')],
    'treatments' => ['headline' => $tm('reportTreatmentsHeadline'), 'summary' => $tm('reportTreatmentsSummary')],
][$activeReportType] ?? ['headline' => $tm('reportsAnalyticsTitle'), 'summary' => $tm('reportsAnalyticsDesc')];
?>

<div class="report-page-shell min-h-screen">
<div class="px-3 py-4 sm:px-4 sm:py-6">
    <div class="mx-auto max-w-7xl">
    <div class="mb-6 sm:mb-8">
            <div class="report-hero sm:p-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center border">
                            <i class="fas fa-chart-pie text-xl"></i>
                        </div>
                        <div class="max-w-2xl">
                            <p class="text-xs uppercase tracking-[0.2em]"><?= esc($tm('reportsAnalyticsTitle')) ?></p>
                            <h1 class="mt-1 v3-section-title tracking-tight v3-title"><?= esc($currentMeta['headline']) ?></h1>
                        </div>
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:min-w-[28rem]">
                        <span class="inline-flex w-full items-center justify-center gap-2 px-4 py-2 text-xs font-bold border">
                            <i class="fas fa-calendar-days"></i>
                            <span><?= esc($filters['period_label'] ?? (($date_range ?? '30') . ' ' . $tm('days'))) ?></span>
                        </span>
                        <div class="grid grid-cols-3 gap-3">
                            <button onclick="exportReport('pdf')" class="report-table-export-button v3-btn v3-btn--secondary">
                                <i class="fas fa-file-pdf v3-text-danger"></i>
                                <?= esc($tf('exportPdf')) ?>
                            </button>
                            <button onclick="printReport()" class="report-table-export-button v3-btn v3-btn--secondary">
                                <i class="fas fa-print"></i>
                                <?= esc($tf('print')) ?>
                            </button>
                            <button onclick="exportReport('excel')" class="report-table-export-button v3-btn v3-btn--secondary">
                                <i class="fas fa-file-csv v3-text-primary"></i>
                                <?= esc($tf('exportCsv')) ?>
                            </button>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <div class="mb-6 sm:mb-8">
        <div class="report-card p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <?php foreach ($workspaceCards as $card): ?>
                    <?php $isActiveCard = $activeReportType === $card['type']; ?>
                    <?php $tabQuery = array_merge($workspaceBaseQuery, ['report_type' => $card['type']]); ?>
                    <a
                        class="report-workspace-card flex items-center gap-3 border p-3 text-start <?= $isActiveCard ? 'v3-border-default v3-bg-primary v3-text-on-primary v3-elevation-none shadow-blue-500/20' : 'v3-border-default v3-bg-panel v3-text-ink v3-border-default v3-hover-primary hover:v3-elevation-none' ?>"
                        href="<?= esc(current_url() . '?' . http_build_query($tabQuery)) ?>"
                        aria-current="<?= $isActiveCard ? 'page' : 'false' ?>"
                    >
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center <?= $isActiveCard ? 'v3-bg-panel v3-text-on-primary' : 'v3-bg-scheduled v3-text-primary v3-hover-soft' ?>">
                            <i class="fas <?= esc($card['icon']) ?>"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold"><?= esc($card['title']) ?></span>
                            <span class="report-workspace-desc mt-1 block text-xs leading-5 <?= $isActiveCard ? 'v3-text-primary' : 'v3-text-muted' ?>"><?= esc($card['desc']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

        <!-- Report Controls -->
    <div class="mb-6 sm:mb-8">
            <div class="report-card sm:p-6">
            <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-base font-bold"><?= esc($tm('reportControlsTitle')) ?></h2>
                <span class="inline-flex w-fit items-center gap-2 px-3 py-1 text-xs font-bold">
                    <i class="fas fa-sliders"></i><?= esc($filters['period_label'] ?? (($date_range ?? '30') . ' ' . $tm('days'))) ?>
                </span>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:flex xl:flex-row xl:flex-nowrap xl:items-end xl:gap-3">
                <div class="xl:flex-1 xl:min-w-0">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= lang('TenantMisc.quickRange') ?></label>
                    <select id="dateRange" data-force-select2 data-allow-clear="false" class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
                        <option value="7" <?= ($date_range ?? '30') === '7' ? 'selected' : '' ?>><?= esc(lang('TenantMisc.last7Days')) ?></option>
                        <option value="30" <?= ($date_range ?? '30') === '30' ? 'selected' : '' ?>><?= esc(lang('TenantMisc.last30Days')) ?></option>
                        <option value="90" <?= ($date_range ?? '30') === '90' ? 'selected' : '' ?>><?= esc(lang('TenantMisc.last90Days')) ?></option>
                        <option value="365" <?= ($date_range ?? '30') === '365' ? 'selected' : '' ?>><?= esc(lang('TenantMisc.lastYear')) ?></option>
                    </select>
                </div>
                <div class="xl:flex-1 xl:min-w-0">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= lang('TenantMisc.from') ?></label>
                    <input id="customFrom" type="text" data-clinic-date="1" value="<?= esc(($filters['custom_from'] ?? '') ?: ($filters['start_date'] ?? '')) ?>" class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-input">
                </div>
                <div class="xl:flex-1 xl:min-w-0">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= lang('TenantMisc.to') ?></label>
                    <input id="customTo" type="text" data-clinic-date="1" value="<?= esc(($filters['custom_to'] ?? '') ?: ($filters['end_date'] ?? '')) ?>" class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-input">
                </div>
                <div class="xl:flex-1 xl:min-w-0">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= lang('TenantMisc.compareMode') ?></label>
                    <select id="compareMode" data-force-select2 data-allow-clear="false" class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
                        <option value="none" <?= ($filters['compare_mode'] ?? 'none') === 'none' ? 'selected' : '' ?>><?= esc(lang('TenantMisc.noComparison')) ?></option>
                        <option value="previous_period" <?= ($filters['compare_mode'] ?? '') === 'previous_period' ? 'selected' : '' ?>><?= esc(lang('TenantMisc.previousPeriodComparison')) ?></option>
                    </select>
                </div>
                <div id="patientFilterWrap" class="xl:flex-1 xl:min-w-0">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= lang('TenantMisc.patientOptional') ?></label>
                    <select id="patientFilter" data-searchable-select data-minimum-input-length="0" data-limit="10" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc($tm('searchPatientsPlaceholder')) ?>" class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
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
                                <?= esc($patientLabel . ($patientNumber !== '' ? ' (' . $patientNumber . ')' : '') . (!empty($patient['phone']) ? ' - ' . $patient['phone'] : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="examinationTypeWrap" class="<?= $activeReportType === 'examinations' ? 'xl:flex-1 xl:min-w-0' : 'hidden' ?>">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= $tm('examinationType') ?></label>
                    <select id="examinationTypeFilter" data-force-select2 class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
                        <option value=""><?= esc($tm('anyExaminationType')) ?></option>
                        <?php foreach (($reports['examination_types'] ?? []) as $type): ?>
                            <?php $typeValue = (string) ($type['examination_type'] ?? ''); ?>
                            <?php if ($typeValue === '') { continue; } ?>
                            <option value="<?= esc($typeValue) ?>" <?= ($filters['examination_type'] ?? '') === $typeValue ? 'selected' : '' ?>><?= esc($typeValue) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="appointmentStatusWrap" class="<?= $activeReportType === 'appointments' ? 'xl:flex-1 xl:min-w-0' : 'hidden' ?>">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= $tm('appointmentStatus') ?></label>
                    <select id="appointmentStatusFilter" data-force-select2 class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
                        <option value=""><?= $tm('allStatuses') ?></option>
                        <option value="scheduled" <?= ($filters['appointment_status'] ?? '') === 'scheduled' ? 'selected' : '' ?>><?= $tm('scheduled') ?></option>
                        <option value="confirmed" <?= ($filters['appointment_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>><?= $tm('confirmed') ?></option>
                        <option value="completed" <?= ($filters['appointment_status'] ?? '') === 'completed' ? 'selected' : '' ?>><?= $tm('completed') ?></option>
                        <option value="cancelled" <?= ($filters['appointment_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= $tm('cancelled') ?></option>
                        <option value="no_show" <?= ($filters['appointment_status'] ?? '') === 'no_show' ? 'selected' : '' ?>><?= $tm('noShow') ?></option>
                        <option value="pending" <?= ($filters['appointment_status'] ?? '') === 'pending' ? 'selected' : '' ?>><?= $tm('pending') ?></option>
                    </select>
                </div>
                <div id="appointmentTypeWrap" class="<?= $activeReportType === 'appointments' ? 'xl:flex-1 xl:min-w-0' : 'hidden' ?>">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= $tm('appointmentType') ?></label>
                    <select id="appointmentTypeFilter" data-force-select2 class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
                        <option value=""><?= esc($tm('anyAppointmentType')) ?></option>
                        <?php foreach (($reports['appointment_types'] ?? []) as $type): ?>
                            <?php $typeValue = (string) ($type['appointment_type'] ?? ''); ?>
                            <?php if ($typeValue === '') { continue; } ?>
                            <option value="<?= esc($typeValue) ?>" <?= ($filters['appointment_type'] ?? '') === $typeValue ? 'selected' : '' ?>><?= esc($typeValue) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="treatmentStatusWrap" class="<?= $activeReportType === 'treatments' ? 'xl:flex-1 xl:min-w-0' : 'hidden' ?>">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= $tm('treatmentStatus') ?></label>
                    <select id="treatmentStatusFilter" data-force-select2 class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
                        <option value=""><?= $tm('allStatuses') ?></option>
                        <option value="planned" <?= ($filters['treatment_status'] ?? '') === 'planned' ? 'selected' : '' ?>><?= $tm('planned') ?></option>
                        <option value="in_progress" <?= ($filters['treatment_status'] ?? '') === 'in_progress' ? 'selected' : '' ?>><?= $tm('inProgress') ?></option>
                        <option value="completed" <?= ($filters['treatment_status'] ?? '') === 'completed' ? 'selected' : '' ?>><?= $tm('completed') ?></option>
                        <option value="on_hold" <?= ($filters['treatment_status'] ?? '') === 'on_hold' ? 'selected' : '' ?>><?= $tm('onHold') ?></option>
                        <option value="cancelled" <?= ($filters['treatment_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= $tm('cancelled') ?></option>
                    </select>
                </div>
                <div id="treatmentTypeWrap" class="<?= $activeReportType === 'treatments' ? 'xl:flex-1 xl:min-w-0' : 'hidden' ?>">
                    <label class="block text-xs font-semibold uppercase tracking-wide mb-2"><?= $tm('treatmentType') ?></label>
                    <select id="treatmentTypeFilter" data-force-select2 class="w-full px-4 py-3 border text-sm focus:outline-none focus:ring-2 v3-focus-control v3-focus-control v3-select">
                        <option value=""><?= esc($tm('anyTreatmentType')) ?></option>
                        <?php foreach (($reports['treatment_types'] ?? []) as $type): ?>
                            <?php $typeValue = (string) ($type['treatment_type'] ?? ''); ?>
                            <?php if ($typeValue === '') { continue; } ?>
                            <option value="<?= esc($typeValue) ?>" <?= ($filters['treatment_type'] ?? '') === $typeValue ? 'selected' : '' ?>><?= esc($typeValue) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end xl:flex-none xl:justify-end">
                    <button onclick="generateReport()" class="inline-flex h-[46px] min-w-[6.5rem] items-center justify-center px-4 py-2.5 text-sm font-semibold transition v3-btn v3-btn--secondary">
                        <?= esc(lang('TenantMisc.applyFilters')) ?>
                    </button>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <?php if (!empty($filters['patient_id'])): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1 v3-bg-paid v3-text-primary border v3-border-default font-semibold">
                    <i class="fas fa-user"></i><?= lang('TenantMisc.patientScoped') ?>
                </span>
                <?php endif; ?>
                <?php if (($filters['compare_mode'] ?? 'none') === 'previous_period'): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1 border font-semibold">
                    <i class="fas fa-arrow-right-arrow-left"></i><?= lang('TenantMisc.previousPeriodComparison') ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Overview Report -->
    <?php if (($report_type ?? 'overview') === 'overview'): ?>
        <div class="mb-8">
            <div class="">
                <!-- Summary Stats -->
                    <div class="report-card sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-2"><?= lang('TenantMisc.summaryStatistics') ?></h3>
                    </div>
                <div class="report-kpi-grid grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="flex justify-between items-center p-4">
                        <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 v3-bg-primary flex items-center justify-center">
                                        <i class="fas fa-users"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold"><?= lang('TenantMisc.totalPatients') ?></p>
                                <p class="text-xs"><?= lang('TenantMisc.allTime') ?></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <p class="v3-section-title font-bold"><?= $reports['summary']['total_patients'] ?? 0 ?></p>
                                    <p class="text-xs v3-text-primary font-semibold">+<?= $reports['summary']['new_patients'] ?? 0 ?> <?= lang('TenantMisc.new') ?></p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4">
                        <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 v3-bg-primary flex items-center justify-center">
                                        <i class="fas fa-stethoscope"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold"><?= lang('TenantMisc.examinations') ?></p>
                                <p class="text-xs"><?= lang('TenantMisc.thisPeriod') ?></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <p class="v3-section-title font-bold"><?= $reports['summary']['examinations_this_period'] ?? 0 ?></p>
                                    <p class="text-xs font-semibold"><?= $reports['summary']['total_examinations'] ?? 0 ?> <?= $tm('totalLower') ?></p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4">
                        <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 v3-bg-warning flex items-center justify-center">
                                        <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold"><?= lang('TenantMisc.appointments') ?></p>
                                <p class="text-xs"><?= lang('TenantMisc.thisPeriod') ?></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <p class="v3-section-title font-bold"><?= $reports['summary']['appointments_this_period'] ?? 0 ?></p>
                                    <p class="text-xs font-semibold"><?= $reports['summary']['total_appointments'] ?? 0 ?> <?= $tm('totalLower') ?></p>
                        </div>
                    </div>

                            <div class="flex justify-between items-center p-4">
                        <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 v3-bg-danger flex items-center justify-center">
                                        <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div>
                                        <p class="text-sm font-semibold"><?= lang('TenantMisc.revenue') ?></p>
                                <p class="text-xs"><?= lang('TenantMisc.thisPeriod') ?></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <p class="v3-section-title font-bold"><?= formatCurrency($reports['summary']['revenue_this_period'] ?? 0) ?></p>
                                    <p class="text-xs font-semibold"><?= formatCurrency($reports['summary']['total_revenue'] ?? 0) ?> <?= $tm('totalLower') ?></p>
                    </div>
                </div>
                </div>
            </div>

                    <!-- Monthly Revenue Chart -->
                    <div class="report-card sm:p-6">
                <div class="mb-6">
                    <h3 class="text-xl font-bold mb-2"><?= $tm('monthlyRevenueTitle') ?></h3>
                </div>
                <div class="relative h-64">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
            </div>
        </div>

        <!-- Additional Charts -->
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                    <div class="report-card sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-2"><?= $tm('examinationTypesTitle') ?></h3>
                    </div>
                    <div class="relative h-64">
                        <canvas id="examinationChart" width="400" height="200"></canvas>
                    </div>
                </div>

                    <div class="report-card sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-2"><?= $tm('appointmentStatusTitle') ?></h3>
                    </div>
                    <div class="relative h-64">
                        <canvas id="appointmentChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-8">
            <div class="overflow-hidden border v3-border-default sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl">
                        <h3 class="text-xl"><?= $tm('primaryFinanceWorkspaceTitle') ?></h3>
                    </div>
                    <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold v3-text-primary transition-colors v3-hover-primary v3-btn v3-btn--secondary">
                        <i class="fas"></i><?= $tm('openFinanceReports') ?>
                    </a>
                </div>
            </div>
        </div>
        <?php if (!empty($reports['comparison']['summary'] ?? [])): ?>
        <?php $cmp = $reports['comparison']['summary']; ?>
        <div class="mb-8">
            <div class="border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold"><?= $tm('previousPeriodComparison') ?></h3>
                    <span class="text-xs font-semibold border px-3 py-1"><?= esc($reports['comparison']['label'] ?? $tm('previousPeriodLabel')) ?></span>
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
                    <div class="rounded-lg border p-4">
                        <p class="text-xs uppercase tracking-wide font-semibold"><?= esc($card['label']) ?></p>
                        <p class="mt-1 text-lg font-bold"><?= $card['currency'] ? formatCurrency($card['current']) : number_format($card['current']) ?></p>
                        <p class="mt-1 text-xs <?= $up ? 'v3-text-primary' : 'v3-text-danger' ?> font-semibold">
                            <i class="fas <?= $up ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                            <?= $card['currency'] ? formatCurrency(abs($delta)) : number_format(abs($delta)) ?>
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
        <?php
            $reportTypeKeyMap = [
                'overview' => 'reportOverview',
                'patients' => 'reportPatients',
                'examinations' => 'reportExaminations',
                'appointments' => 'reportAppointments',
                'treatments' => 'reportTreatments',
            ];
            $reportTitle = isset($reportTypeKeyMap[$report_type ?? ''])
                ? $tm($reportTypeKeyMap[$report_type])
                : ucfirst((string) ($report_type ?? $tm('reportTitleSuffix')));
            $reportPeriodLabel = $filters['period_label'] ?? (($date_range ?? '30') . ' ' . $tm('days'));
            $patientScopeLabel = !empty($filters['patient_id']) ? $tm('patientScoped') : $tm('allPatients');
        ?>
        <div class="mb-8">
            <div class="report-card sm:p-6">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em]"><?= esc($reportPeriodLabel) ?></p>
                        <h3 class="mt-2 v3-section-title font-bold"><?= esc($reportTitle) ?></h3>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold">
                        <i class="fas fa-crosshairs"></i>
                        <span><?= esc($patientScopeLabel) ?></span>
                    </div>
                </div>
            </div>

            <?php if ($report_type === 'patients'): ?>
                <div class="report-kpi-grid grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-sm font-semibold v3-text-primary"><?= $tm('totalPatients') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['patients'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold v3-text-primary"><?= $tm('new') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= (int) ($reports['new_vs_returning']['new'] ?? 0) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= $tm('returning') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= (int) ($reports['new_vs_returning']['returning'] ?? 0) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold v3-text-warning"><?= $tm('patientsWithBalance') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= (int) ($reports['financial_snapshot']['patients_with_balance'] ?? 0) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-[1.1fr,0.9fr]">
                    <div class="report-card sm:p-6">
                        <div class="mb-5">
                            <h4 class="text-lg font-bold"><?= esc($tm('patientMixTitle')) ?></h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border px-4 py-3">
                                <span class="text-sm font-medium"><?= $tm('newVsReturningTitle') ?></span>
                                <span class="text-sm font-semibold"><?= (int) ($reports['new_vs_returning']['new'] ?? 0) ?> / <?= (int) ($reports['new_vs_returning']['returning'] ?? 0) ?></span>
                            </div>
                            <?php if (!empty($reports['gender_distribution'])): ?>
                                <?php foreach (array_slice($reports['gender_distribution'], 0, 5) as $gender): ?>
                                    <div class="flex items-center justify-between border px-4 py-3">
                                        <span class="text-sm"><?= esc(ucfirst((string) ($gender['gender'] ?? $tm('unknown')))) ?></span>
                                        <span class="text-sm font-semibold"><?= (int) ($gender['count'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="border border-dashed px-4 py-6 text-sm"><?= esc($tm('noBreakdownAvailable')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="overflow-hidden border v3-border-default sm:p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h4 class="text-lg"><?= $tm('patientBalanceTitle') ?></h4>
                            </div>
                            <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold v3-text-primary transition-colors v3-hover-primary v3-btn v3-btn--secondary">
                                <i class="fas"></i><?= $tm('openFinanceReports') ?>
                            </a>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="border px-4 py-4">
                                <p class="text-xs uppercase tracking-wide v3-text-primary"><?= $tm('balanceDue') ?></p>
                                <p class="mt-2 v3-section-title"><?= formatCurrency($reports['financial_snapshot']['balance_due_total'] ?? 0) ?></p>
                            </div>
                            <div class="border px-4 py-4">
                                <p class="text-xs uppercase tracking-wide v3-text-primary"><?= $tm('outstandingInvoices') ?></p>
                                <p class="mt-2 v3-section-title"><?= (int) ($reports['financial_snapshot']['open_invoices'] ?? 0) ?></p>
                            </div>
                            <div class="border px-4 py-4">
                                <p class="text-xs uppercase tracking-wide v3-text-primary"><?= $tm('patientsWithBalance') ?></p>
                                <p class="mt-2 v3-section-title"><?= (int) ($reports['financial_snapshot']['patients_with_balance'] ?? 0) ?></p>
                            </div>
                            <div class="border px-4 py-4">
                                <p class="text-xs uppercase tracking-wide v3-text-primary"><?= $tm('overdueBalance') ?></p>
                                <p class="mt-2 v3-section-title"><?= formatCurrency($reports['financial_snapshot']['overdue_balance_total'] ?? 0) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($reports['top_balance_patients'])): ?>
                <div class="report-table-card">
                    <div class="report-table-head">
                        <div class="report-table-toolbar">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 v3-bg-primary"></div>
                                <div>
                                    <h4 class="text-lg font-semibold"><?= esc($tm('topBalancePatientsTitle')) ?></h4>
                                    <p class="text-sm"><?= esc($tm('topBalancePatientsDesc')) ?></p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <button onclick="exportReport('pdf')" class="report-table-export-button v3-btn v3-btn--secondary">
                                    <i class="fas fa-file-pdf v3-text-danger"></i>
                                    <?= esc($tf('exportPdf')) ?>
                                </button>
                                <button onclick="printReport()" class="report-table-export-button v3-btn v3-btn--secondary">
                                    <i class="fas fa-print"></i>
                                    <?= esc($tf('print')) ?>
                                </button>
                                <button onclick="exportReport('excel')" class="report-table-export-button v3-btn v3-btn--secondary">
                                    <i class="fas fa-file-csv v3-text-primary"></i>
                                    <?= esc($tf('exportCsv')) ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="report-table-scroll">
                        <div class="dataTables-wrapper">
                        <table id="topBalancePatientsTable" class="w-full min-w-full text-sm">
                            <thead class="">
                                <tr>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user v3-text-primary"></i>
                                            <span><?= $tm('patient') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-file-invoice v3-text-primary"></i>
                                            <span><?= $tm('outstandingInvoices') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-clock v3-text-primary"></i>
                                            <span><?= $tm('daysPastDue') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-dollar-sign v3-text-primary"></i>
                                            <span><?= $tm('balanceDue') ?></span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y v3-border-default">
                                <?php foreach (($reports['top_balance_patients'] ?? []) as $row): ?>
                                <tr class="cursor-pointer transition-colors v3-hover-primary" data-href="<?= esc((string) ($row['row_href'] ?? ''), 'attr') ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium truncate"><?= esc((string) ($row['full_name'] ?? '')) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?= (int) ($row['open_invoices'] ?? 0) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-medium <?= ((int) ($row['max_days_past_due'] ?? 0)) > 0 ? 'v3-bg-alert v3-text-danger' : 'v3-bg-paid v3-text-primary' ?>">
                                            <i class="fas fa-clock"></i>
                                            <?= (int) ($row['max_days_past_due'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-arrow-up v3-text-primary text-xs"></i>
                                            <span class="text-sm font-medium v3-text-primary"><?= formatCurrency($row['balance_due'] ?? 0) ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <?php elseif ($report_type === 'examinations'): ?>
                <div class="report-kpi-grid grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-sm font-semibold v3-text-primary"><?= $tm('examinations') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['examinations'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold v3-text-primary"><?= $tm('type') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['examination_types'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= $tm('monthsTracked') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['monthly_examinations'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= $tm('patientScope') ?></p>
                        <p class="mt-3 v3-section-title font-bold"><?= esc($patientScopeLabel) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2">
                    <div class="report-card sm:p-6">
                        <div class="mb-5">
                            <h4 class="text-lg font-bold"><?= esc($tm('clinicalMixTitle')) ?></h4>
                        </div>
                        <div class="space-y-3">
                            <?php if (!empty($reports['examination_types'])): ?>
                                <?php foreach (array_slice($reports['examination_types'], 0, 6) as $type): ?>
                                    <div class="flex items-center justify-between border px-4 py-3">
                                        <span class="text-sm"><?= esc(ucfirst((string) ($type['examination_type'] ?? $tm('unknown')))) ?></span>
                                        <span class="text-sm font-semibold"><?= (int) ($type['count'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="border border-dashed px-4 py-6 text-sm"><?= esc($tm('noBreakdownAvailable')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="report-card sm:p-6">
                        <div class="mb-5">
                            <h4 class="text-lg font-bold"><?= esc($tm('activityTrendTitle')) ?></h4>
                        </div>
                        <div class="space-y-3">
                            <?php if (!empty($reports['monthly_examinations'])): ?>
                                <?php foreach (array_slice($reports['monthly_examinations'], -6) as $row): ?>
                                    <div class="flex items-center justify-between border px-4 py-3">
                                        <span class="text-sm"><?= esc((string) ($row['month'] ?? $tm('unknown'))) ?></span>
                                        <span class="text-sm font-semibold"><?= (int) ($row['count'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="border border-dashed px-4 py-6 text-sm"><?= esc($tm('noBreakdownAvailable')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type === 'appointments'): ?>
                <div class="report-kpi-grid grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-sm font-semibold v3-text-warning"><?= $tm('appointments') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['appointments'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold v3-text-primary"><?= $tm('statuses') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['status_distribution'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= $tm('type') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['appointment_types'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= $tm('patientScope') ?></p>
                        <p class="mt-3 v3-section-title font-bold"><?= esc($patientScopeLabel) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2">
                    <div class="report-card sm:p-6">
                        <div class="mb-5">
                            <h4 class="text-lg font-bold"><?= esc($tm('statusBreakdownTitle')) ?></h4>
                        </div>
                        <div class="space-y-3">
                            <?php if (!empty($reports['status_distribution'])): ?>
                                <?php foreach (array_slice($reports['status_distribution'], 0, 6) as $status): ?>
                                    <div class="flex items-center justify-between border px-4 py-3">
                                        <span class="text-sm"><?= esc(ucfirst((string) ($status['status'] ?? $tm('unknown')))) ?></span>
                                        <span class="text-sm font-semibold"><?= (int) ($status['count'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="border border-dashed px-4 py-6 text-sm"><?= esc($tm('noBreakdownAvailable')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="report-card sm:p-6">
                        <div class="mb-5">
                            <h4 class="text-lg font-bold"><?= esc($tm('typeBreakdownTitle')) ?></h4>
                        </div>
                        <div class="space-y-3">
                            <?php if (!empty($reports['appointment_types'])): ?>
                                <?php foreach (array_slice($reports['appointment_types'], 0, 6) as $type): ?>
                                    <div class="flex items-center justify-between border px-4 py-3">
                                        <span class="text-sm"><?= esc(ucfirst((string) ($type['appointment_type'] ?? $tm('unknown')))) ?></span>
                                        <span class="text-sm font-semibold"><?= (int) ($type['count'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="border border-dashed px-4 py-6 text-sm"><?= esc($tm('noBreakdownAvailable')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type === 'treatments'): ?>
                <div class="report-kpi-grid grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-sm font-semibold v3-text-danger"><?= $tm('reportTreatments') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['treatments'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold v3-text-primary"><?= $tm('type') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['treatment_types'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= $tm('statuses') ?></p>
                        <p class="mt-3 v3-title font-bold"><?= count($reports['status_distribution'] ?? []) ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= $tm('patientScope') ?></p>
                        <p class="mt-3 v3-section-title font-bold"><?= esc($patientScopeLabel) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2">
                    <div class="report-card sm:p-6">
                        <div class="mb-5">
                            <h4 class="text-lg font-bold"><?= esc($tm('statusBreakdownTitle')) ?></h4>
                        </div>
                        <div class="space-y-3">
                            <?php if (!empty($reports['status_distribution'])): ?>
                                <?php foreach (array_slice($reports['status_distribution'], 0, 6) as $status): ?>
                                    <div class="flex items-center justify-between border px-4 py-3">
                                        <span class="text-sm"><?= esc(ucfirst((string) ($status['status'] ?? $tm('unknown')))) ?></span>
                                        <span class="text-sm font-semibold"><?= (int) ($status['count'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="border border-dashed px-4 py-6 text-sm"><?= esc($tm('noBreakdownAvailable')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="report-card sm:p-6">
                        <div class="mb-5">
                            <h4 class="text-lg font-bold"><?= esc($tm('typeBreakdownTitle')) ?></h4>
                        </div>
                        <div class="space-y-3">
                            <?php if (!empty($reports['treatment_types'])): ?>
                                <?php foreach (array_slice($reports['treatment_types'], 0, 6) as $type): ?>
                                    <div class="flex items-center justify-between border px-4 py-3">
                                        <span class="text-sm"><?= esc(ucfirst((string) ($type['treatment_type'] ?? $tm('unknown')))) ?></span>
                                        <span class="text-sm font-semibold"><?= (int) ($type['count'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="border border-dashed px-4 py-6 text-sm"><?= esc($tm('noBreakdownAvailable')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="border px-6 py-10 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center v3-bg-scheduled v3-text-primary">
                        <i class="fas fa-chart-bar text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold"><?= $tm('reportData') ?></h3>
                    <p class="mt-2 text-sm"><?= $tm('reportUnderDevelopment') ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

        <?php if (!empty($detail_tables ?? []) && $activeReportType !== 'finance'): ?>
        <details class="mb-8 overflow-hidden border">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center border">
                        <i class="fas fa-table"></i>
                    </span>
                    <div>
                        <h3 class="text-lg"><?= lang('TenantMisc.rowsBehindReport') ?></h3>
                    </div>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center border">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </summary>
            <div id="reportDetailTables" class="report-table-card rounded-none border-0">
                <div class="report-table-head">
                <div class="report-table-toolbar">
                    <div class="text-sm font-bold"><?= esc($filters['period_label'] ?? (($date_range ?? '30') . ' ' . $tm('days'))) ?></div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <button onclick="exportReport('pdf')" class="report-table-export-button v3-btn v3-btn--secondary">
                            <i class="fas fa-file-pdf v3-text-danger"></i>
                            <?= esc($tf('exportPdf')) ?>
                        </button>
                        <button onclick="printReport()" class="report-table-export-button v3-btn v3-btn--secondary">
                            <i class="fas fa-print"></i>
                            <?= esc($tf('print')) ?>
                        </button>
                        <button onclick="exportReport('excel')" class="report-table-export-button v3-btn v3-btn--secondary">
                            <i class="fas fa-file-csv v3-text-primary"></i>
                            <?= esc($tf('exportCsv')) ?>
                        </button>
                    </div>
                </div>
                    <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
                            <?php foreach (($detail_tables ?? []) as $idx => $table): ?>
                            <button
                                type="button"
                                class="report-detail-tab flex items-center justify-between gap-3 rounded-lg border px-3 py-2 text-sm font-semibold <?= $idx === 0 ? 'v3-border-default v3-bg-primary v3-text-on-primary v3-elevation-none shadow-green-500/15' : 'v3-border-default v3-bg-panel v3-text-ink v3-border-default v3-hover-primary' ?> v3-btn v3-btn--secondary"
                                data-target="<?= esc($table['id']) ?>"
                                data-active="<?= $idx === 0 ? 'true' : 'false' ?>"
                            >
                                <span class="truncate"><?= esc($table['title']) ?></span>
                                <span class="px-2 py-0.5 text-xs <?= $idx === 0 ? 'v3-bg-panel v3-text-primary' : 'v3-bg-soft v3-text-muted' ?>"><?= (int) ($table['row_count'] ?? 0) ?></span>
                            </button>
                            <?php endforeach; ?>
                    </div>
                </div>
                <div class="">
                    <div id="reportDrillBadge" class="hidden mb-4">
                        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-semibold">
                            <i class="fas fa-filter"></i>
                            <span id="reportDrillBadgeText"><?= lang('TenantMisc.drillthroughActive') ?></span>
                            <button type="button" id="clearDrillthroughBtn" class="inline-flex items-center px-2 py-1 rounded border text-xs font-semibold v3-btn v3-btn--secondary">
                                <?= lang('TenantMisc.clear') ?>
                            </button>
                        </div>
                    </div>

                    <?php foreach (($detail_tables ?? []) as $idx => $table): ?>
                    <div class="report-detail-panel <?= $idx === 0 ? '' : 'hidden' ?>" data-panel="<?= esc($table['id']) ?>">
                    <div class="report-table-head rounded-none">
                        <div class="report-table-toolbar">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 v3-bg-primary"></div>
                                <h4 class="text-lg font-semibold"><?= esc($table['title']) ?></h4>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <label class="report-table-control-group">
                                    <span><?= lang('TenantMisc.showing') ?></span>
                                    <select class="report-detail-per-page report-table-control v3-select">
                                        <option value="25">25 <?= lang('TenantMisc.rows') ?></option>
                                        <option value="50">50 <?= lang('TenantMisc.rows') ?></option>
                                        <option value="100">100 <?= lang('TenantMisc.rows') ?></option>
                                    </select>
                                </label>
                                <label class="report-table-control-group">
                                    <span><?= esc($tf('search')) ?></span>
                                    <input type="text" class="report-detail-search report-table-control w-full md:w-72 v3-input" placeholder="<?= lang('TenantMisc.searchRowsPlaceholder') ?>">
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="report-table-scroll">
                        <div class="dataTables-wrapper">
                        <table class="w-full min-w-full text-sm report-detail-table">
                            <thead class="">
                                <tr>
                                    <?php foreach (($table['columns'] ?? []) as $column): ?>
                                    <th data-key="<?= esc($column['key'] ?? '') ?>" class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-table v3-text-primary"></i>
                                            <span><?= esc($column['label'] ?? $column['key']) ?></span>
                                        </div>
                                    </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y v3-border-default">
                                <?php if (empty($table['rows'])): ?>
                                <tr>
                                    <td colspan="<?= count($table['columns'] ?? []) ?>" class="px-6 py-8 text-center"><?= lang('TenantMisc.noRowsForPeriod') ?></td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($table['rows'] as $row): ?>
                                <tr class="report-detail-row <?= !empty($row['row_href'] ?? '') ? 'cursor-pointer v3-hover-primary' : 'v3-hover-primary' ?> transition-colors"<?= !empty($row['row_href'] ?? '') ? ' data-href="' . esc((string) $row['row_href'], 'attr') . '"' : '' ?>>
                                    <?php foreach (($table['columns'] ?? []) as $column): ?>
                                    <?php $key = $column['key'] ?? ''; $value = $row[$key] ?? ''; ?>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (is_numeric($value) && preg_match('/amount|balance|total|share|paid/i', (string) $key)): ?>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-arrow-up v3-text-primary text-xs"></i>
                                                <span class="text-sm font-medium v3-text-primary"><?= formatCurrency($value) ?></span>
                                            </div>
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
                    </div>
                    <div class="px-6 py-4 border-t flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="text-sm report-detail-pagination-info"><?= lang('TenantMisc.showing') ?> <?= (int) min(($table['row_count'] ?? 0), 25) ?> <?= lang('TenantMisc.of') ?> <?= (int) ($table['row_count'] ?? 0) ?> <?= lang('TenantMisc.rows') ?></div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="report-detail-prev px-3 py-2 text-sm font-semibold rounded-lg border v3-border-default disabled:opacity-50 disabled:cursor-not-allowed v3-btn v3-btn--secondary"><?= lang('TenantMisc.previous') ?></button>
                            <span class="report-detail-page-label text-sm min-w-[110px] text-center"><?= lang('TenantMisc.page') ?> 1</span>
                            <button type="button" class="report-detail-next px-3 py-2 text-sm font-semibold rounded-lg border v3-border-default disabled:opacity-50 disabled:cursor-not-allowed v3-btn v3-btn--secondary"><?= lang('TenantMisc.next') ?></button>
                        </div>
                    </div>
                </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>
        <?php endif; ?>
        </div>
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
    const reportType = '<?= esc($activeReportType, 'js') ?>';
    const examinationTypeWrap = document.getElementById('examinationTypeWrap');
    const appointmentStatusWrap = document.getElementById('appointmentStatusWrap');
    const appointmentTypeWrap = document.getElementById('appointmentTypeWrap');
    const treatmentStatusWrap = document.getElementById('treatmentStatusWrap');
    const treatmentTypeWrap = document.getElementById('treatmentTypeWrap');
    const examinationTypeFilter = document.getElementById('examinationTypeFilter');
    const appointmentStatusFilter = document.getElementById('appointmentStatusFilter');
    const appointmentTypeFilter = document.getElementById('appointmentTypeFilter');
    const treatmentStatusFilter = document.getElementById('treatmentStatusFilter');
    const treatmentTypeFilter = document.getElementById('treatmentTypeFilter');

    const isExaminations = reportType === 'examinations';
    const isAppointments = reportType === 'appointments';
    const isTreatments = reportType === 'treatments';

    if (examinationTypeWrap) examinationTypeWrap.classList.toggle('hidden', !isExaminations);
    if (appointmentStatusWrap) appointmentStatusWrap.classList.toggle('hidden', !isAppointments);
    if (appointmentTypeWrap) appointmentTypeWrap.classList.toggle('hidden', !isAppointments);
    if (treatmentStatusWrap) treatmentStatusWrap.classList.toggle('hidden', !isTreatments);
    if (treatmentTypeWrap) treatmentTypeWrap.classList.toggle('hidden', !isTreatments);

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
        examination_type: '',
        appointment_status: '',
        appointment_type: '',
        treatment_status: '',
        treatment_type: '',
    };
    if (reportType === 'examinations') {
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
    const reportType = '<?= esc($activeReportType, 'js') ?>';
    const dateRange = document.getElementById('dateRange').value;
    const specific = collectSpecificFilters(reportType);
    const customFromRaw = document.getElementById('customFrom')?.value || '';
    const customToRaw = document.getElementById('customTo')?.value || '';
    const compareMode = document.getElementById('compareMode')?.value || 'none';
    const customFrom = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customFromRaw) || '') : customFromRaw;
    const customTo = window.parseClinicDateToIso ? (window.parseClinicDateToIso(customToRaw) || '') : customToRaw;

    const url = new URL(window.location);
    url.searchParams.set('report_type', reportType);
    url.searchParams.set('date_range', dateRange);
    url.searchParams.set('date_mode', 'custom');
    url.searchParams.set('compare_mode', compareMode);
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
    const reportType = '<?= esc($activeReportType, 'js') ?>';
    const dateRange = document.getElementById('dateRange').value;
    const specific = collectSpecificFilters(reportType);
    const customFromRaw = document.getElementById('customFrom')?.value || '';
    const customToRaw = document.getElementById('customTo')?.value || '';
    const compareMode = document.getElementById('compareMode')?.value || 'none';
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
        compare_mode: compareMode,
        custom_from: customFrom,
        custom_to: customTo,
        patient_id: specific.patient_id,
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

function printReport() {
    window.print();
}

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
        b.dataset.active = active ? 'true' : 'false';
        b.classList.toggle('v3-bg-primary', active);
        b.classList.toggle('v3-text-on-primary', active);
        b.classList.toggle('v3-border-default', active);
        b.classList.toggle('v3-elevation-none', active);
        b.classList.toggle('shadow-green-500/15', active);
        b.classList.toggle('v3-bg-panel', !active);
        b.classList.toggle('v3-text-ink', !active);
        b.classList.toggle('v3-border-default', !active);
        b.classList.toggle('v3-border-default', !active);
        b.classList.toggle('v3-hover-primary', !active);
        const countSpan = b.querySelectorAll('span')[1];
        if (countSpan) {
            countSpan.classList.toggle('v3-bg-panel', active);
            countSpan.classList.toggle('v3-text-primary', active);
            countSpan.classList.toggle('v3-bg-soft', !active);
            countSpan.classList.toggle('v3-text-muted', !active);
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
        th.classList.add('cursor-pointer', 'select-none', 'v3-hover-text-primary');
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
        window.location.href = window.workflowContextUrl ? window.workflowContextUrl(href) : href;
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
        th.classList.toggle('v3-text-primary', active);
        th.classList.toggle('font-bold', active);
        const label = th.querySelector('span') || th;
        const baseLabel = th.dataset.baseLabel || label.textContent.replace(/\s[\^v]$/, '');
        th.dataset.baseLabel = baseLabel;
        label.textContent = active ? `${baseLabel} ${state.sortDir === 'desc' ? 'v' : '^'}` : baseLabel;
    });

    if (rows.length === 0) {
        const emptyRow = document.createElement('tr');
        const emptyCell = document.createElement('td');
        emptyCell.colSpan = columns.length;
        emptyCell.className = 'px-6 py-8 text-center v3-text-muted';
        emptyCell.textContent = reportI18n.noRowsMatch;
        emptyRow.appendChild(emptyCell);
        tbody.replaceChildren(emptyRow);
    } else {
        const fragment = document.createDocumentFragment();
        rows.forEach((row) => {
            const tr = document.createElement('tr');
            const rowHref = row.row_href || '';
            tr.className = rowHref
                ? 'report-detail-row cursor-pointer v3-hover-primary transition-colors'
                : 'report-detail-row v3-hover-primary transition-colors';
            if (rowHref) {
                tr.dataset.href = rowHref;
            }

            columns.forEach((col) => {
                const key = col.key || '';
                const raw = row[key] ?? '';
                const isMoney = /amount|balance|total|share|paid/i.test(key) && !Number.isNaN(parseFloat(raw));
                const td = document.createElement('td');
                td.className = 'px-6 py-4 v3-text-ink whitespace-nowrap';

                if (isMoney) {
                    const wrapper = document.createElement('div');
                    const icon = document.createElement('i');
                    const span = document.createElement('span');
                    wrapper.className = 'flex items-center gap-2';
                    icon.className = 'fas fa-arrow-up v3-text-primary text-xs';
                    span.className = 'text-sm font-medium v3-text-primary';
                    span.textContent = formatCurrency(raw);
                    wrapper.appendChild(icon);
                    wrapper.appendChild(span);
                    td.appendChild(wrapper);
                } else {
                    td.textContent = String(raw ?? '');
                }

                tr.appendChild(td);
            });

            fragment.appendChild(tr);
        });
        tbody.replaceChildren(fragment);
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
            const errorRow = document.createElement('tr');
            const errorCell = document.createElement('td');
            errorCell.colSpan = colspan;
            errorCell.className = 'px-4 py-8 text-center v3-text-danger';
            errorCell.textContent = reportI18n.failedLoadRows;
            errorRow.appendChild(errorCell);
            tbody.replaceChildren(errorRow);
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




</div>
