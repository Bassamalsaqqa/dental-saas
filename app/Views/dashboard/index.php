<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$todayLabel = formatDate(date('Y-m-d'));
$totalPatients = (int) ($stats['total_patients'] ?? 0);
$todayExaminations = (int) ($stats['today_examinations'] ?? 0);
$todayAppointments = (int) ($stats['today_appointments'] ?? 0);
$upcomingAppointmentsCount = (int) ($stats['upcoming_appointments'] ?? count($upcoming_appointments ?? []));
$totalRevenue = (float) ($stats['total_revenue'] ?? 0);
$pendingPayments = (float) ($stats['pending_payments'] ?? 0);
$overduePayments = (float) ($stats['overdue_payments'] ?? 0);
$activeTreatments = (int) ($stats['active_treatments'] ?? 0);
$completedTreatments = (int) ($stats['completed_treatments'] ?? 0);
$recentPatientCount = count($recent_patients ?? []);
$recentExamCount = count($recent_examinations ?? []);
$clinicName = trim((string) (session('active_clinic_name') ?? session('clinic_name') ?? ''));
$clinicName = $clinicName !== '' ? $clinicName : lang('UI.menuDashboard');
$todayRouting = is_array($today_routing ?? null) ? $today_routing : ['groups' => [], 'counts' => [], 'completed_count' => 0, 'open_count' => 0];
$routingGroups = is_array($todayRouting['groups'] ?? null) ? $todayRouting['groups'] : [];
$canRouteAppointments = !empty($can_route_appointments);
$canEditAppointments = has_permission('appointments', 'edit');
$documentationQueue = is_array($clinical_documentation_queue ?? null) ? $clinical_documentation_queue : [];
$readyToSignCount = (int) ($documentationQueue['ready_to_sign_count'] ?? 0);
$overdueDraftCount = (int) ($documentationQueue['overdue_draft_count'] ?? 0);
$acceptedTreatmentQueue = is_array($accepted_treatment_queue ?? null) ? $accepted_treatment_queue : ['count' => 0, 'items' => []];
$routingStageLabels = [
    'scheduled' => lang('UI.appointmentStageScheduled'),
    'confirmed' => lang('UI.appointmentStageConfirmed'),
    'arrived' => lang('UI.appointmentStageArrived'),
    'ready' => lang('UI.appointmentStageReady'),
    'in_chair' => lang('UI.appointmentStageInChair'),
    'checkout' => lang('UI.appointmentStageCheckout'),
    'completed' => lang('UI.appointmentStageCompleted'),
    'cancelled' => lang('UI.appointmentStageCancelled'),
    'no_show' => lang('UI.appointmentStageNoShow'),
];
$routingGroupConfig = [
    'expected' => ['label' => lang('UI.dashboardRoutingExpected'), 'icon' => 'fa-calendar-check', 'tone' => 'blue'],
    'waiting' => ['label' => lang('UI.dashboardRoutingWaiting'), 'icon' => 'fa-user-clock', 'tone' => 'amber'],
    'chairside' => ['label' => lang('UI.dashboardRoutingChairside'), 'icon' => 'fa-tooth', 'tone' => 'indigo'],
    'checkout' => ['label' => lang('UI.dashboardRoutingCheckout'), 'icon' => 'fa-receipt', 'tone' => 'emerald'],
    'exceptions' => ['label' => lang('UI.dashboardRoutingExceptions'), 'icon' => 'fa-triangle-exclamation', 'tone' => 'rose'],
];

$formatHumanLabel = static function (?string $value): string {
    $value = trim((string) $value);
    return $value !== '' ? ucfirst(str_replace('_', ' ', $value)) : '-';
};
?>



<div class="dashboard-workspace min-h-screen">
    <div class="container mx-auto px-3 py-4 sm:px-4 sm:py-6">
        <header class="v3-command-header"><div><h1><?= esc(lang('UI.menuDashboard')) ?></h1><p><?= esc($todayLabel) ?> <span aria-hidden="true"> / </span> <?= esc($clinicName) ?></p></div><div class="v3-command-actions"><a href="<?= base_url('patient/create') ?>" data-patient-create-modal-open class="v3-action">
                        <i class="fas fa-user-plus"></i>
                        <span><?= esc(lang('UI.dashboardAddPatient')) ?></span>
                    </a>
<a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="v3-action v3-action-primary">
                        <i class="fas fa-calendar-plus"></i>
                        <span><?= esc(lang('UI.dashboardBookAppointment')) ?></span>
                    </a>
<a href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="v3-action">
                        <i class="fas fa-stethoscope"></i>
                        <span><?= esc(lang('UI.dashboardNewExamination')) ?></span>
                    </a>
<a href="<?= base_url('finance/create') ?>" class="v3-action">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span><?= esc(lang('UI.dashboardCreateInvoice')) ?></span>
                    </a></div></header>

        <section class="dashboard-stat-grid v3-dashboard-metrics">
            <a href="<?= base_url('patient') ?>" class="dashboard-stat-card dashboard-stat-blue">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTotalPatients')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(number_format($totalPatients)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('UI.dashboardRecentPatients')) ?>: <?= esc(number_format($recentPatientCount)) ?></p>
                    </div>
                    <i class="fas fa-users v3-title-scale"></i>
                </div>
            </a>

            <a href="<?= base_url('examination') ?>" class="dashboard-stat-card dashboard-stat-emerald">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTodayExaminations')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(number_format($todayExaminations)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('UI.dashboardRecentExaminations')) ?>: <?= esc(number_format($recentExamCount)) ?></p>
                    </div>
                    <i class="fas fa-stethoscope v3-title-scale"></i>
                </div>
            </a>

            <a href="<?= base_url('appointment') ?>" class="dashboard-stat-card dashboard-stat-amber">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTodayAppointments')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(number_format($todayAppointments)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('UI.dashboardUpcomingAppointments')) ?>: <?= esc(number_format($upcomingAppointmentsCount)) ?></p>
                    </div>
                    <i class="fas fa-calendar-days v3-title-scale"></i>
                </div>
            </a>

            <a href="<?= base_url('finance') ?>" class="dashboard-stat-card dashboard-stat-purple">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTotalRevenue')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(formatCurrencyAbbreviated($totalRevenue)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('Finance.pendingPayments')) ?>: <?= esc(formatCurrencyAbbreviated($pendingPayments)) ?></p>
                    </div>
                    <i class="fas fa-chart-line v3-title-scale"></i>
                </div>
            </a>
        </section>





        <div class="dashboard-content-grid v3-command-center-grid">
            <section class="dashboard-panel-stack v3-command-center-live">
<?php if ($canRouteAppointments): ?>
            <section class="dashboard-routing mb-5" aria-labelledby="today-routing-title">
                <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center v3-radius v3-warm-surface">
                            <i class="fas fa-route"></i>
                        </span>
                        <div>
                            <h2 id="today-routing-title" class="text-lg text-slate-950 sm:text-xl"><?= esc(lang('UI.dashboardRoutingTitle')) ?></h2>
                            <p class="mt-0.5 text-sm font-semibold text-slate-600"><?= esc(lang('UI.dashboardRoutingSubtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full v3-soft-surface px-3 py-1.5 text-xs v3-accent-text">
                            <?= esc(lang('UI.dashboardRoutingOpenCount', ['count' => (int) ($todayRouting['open_count'] ?? 0)])) ?>
                        </span>
                        <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs text-emerald-800">
                            <?= esc(lang('UI.dashboardRoutingCompletedCount', ['count' => (int) ($todayRouting['completed_count'] ?? 0)])) ?>
                        </span>
                        <a href="<?= base_url('appointment/calendar') ?>" class="dashboard-view-link">
                            <span><?= esc(lang('UI.dashboardRoutingOpenCalendar')) ?></span>
                            <i class="fas <?= $isRtl ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i>
                        </a>
                    </div>
                </div>

                <div class="dashboard-routing-board">
                    <?php foreach ($routingGroupConfig as $groupKey => $groupConfig): ?>
                        <?php $groupRows = is_array($routingGroups[$groupKey] ?? null) ? $routingGroups[$groupKey] : []; ?>
                        <section class="dashboard-routing-column dashboard-routing-column-<?= esc($groupConfig['tone'], 'attr') ?>">
                            <header class="mb-3 flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2 text-sm text-slate-900">
                                    <i class="fas <?= esc($groupConfig['icon'], 'attr') ?>"></i>
                                    <span><?= esc($groupConfig['label']) ?></span>
                                </span>
                                <span class="flex h-7 min-w-7 items-center justify-center rounded-full v3-surface px-2 text-xs text-slate-700" dir="ltr"><?= count($groupRows) ?></span>
                            </header>

                            <div class="space-y-2">
                                <?php foreach ($groupRows as $visit): ?>
                                    <?php
                                    $visitId = (int) ($visit['id'] ?? 0);
                                    $patientId = (int) ($visit['patient_id'] ?? 0);
                                    $patientName = trim((string) (($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? '')));
                                    $patientName = $patientName !== '' ? $patientName : lang('UI.patientUnknown');
                                    $stage = (string) ($visit['status'] ?? 'scheduled');
                                    $nextStage = (string) ($visit['primary_next_stage'] ?? '');
                                    ?>
                                    <article class="dashboard-routing-card">
                                        <div class="flex items-start justify-between gap-3">
                                            <a href="<?= esc(workflow_context_url('appointment/' . $visitId, 'dashboard')) ?>" class="min-w-0 flex-1">
                                                <span class="block truncate text-sm text-slate-950"><?= esc($patientName) ?></span>
                                                <span class="mt-1 block truncate text-xs font-bold text-slate-500">
                                                    <?= esc($formatHumanLabel($visit['appointment_type'] ?? '')) ?>
                                                    <?php if (!empty($visit['chair_name'])): ?>
                                                        <span aria-hidden="true"> · </span><?= esc($visit['chair_name']) ?>
                                                    <?php endif; ?>
                                                </span>
                                            </a>
                                            <span class="shrink-0 text-end">
                                                <span class="block text-sm text-slate-950" dir="ltr"><?= esc(formatTime($visit['appointment_time'] ?? '')) ?></span>
                                                <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700"><?= esc($routingStageLabels[$stage] ?? $formatHumanLabel($stage)) ?></span>
                                            </span>
                                        </div>

                                        <?php if (!empty($visit['is_late'])): ?>
                                            <p class="mt-2 text-xs text-rose-700">
                                                <i class="fas fa-clock me-1"></i><?= esc(lang('UI.dashboardRoutingLate', ['minutes' => (int) ($visit['late_minutes'] ?? 0)])) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="mt-3 flex items-center gap-2">
                                            <?php if ($patientId > 0): ?>
                                                <a href="<?= esc(workflow_context_url('patient/' . $patientId, 'dashboard')) ?>" class="inline-flex h-9 w-9 shrink-0 items-center justify-center v3-radius bg-slate-100 text-slate-700 transition hover:bg-slate-200" title="<?= esc(lang('UI.patientProfile'), 'attr') ?>">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEditAppointments && $nextStage !== ''): ?>
                                                <form method="post" action="<?= base_url('appointment/' . $visitId . '/transition') ?>" class="min-w-0 flex-1">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="<?= esc($nextStage, 'attr') ?>">
                                                    <input type="hidden" name="return_to" value="dashboard">
                                                    <button type="submit" class="dashboard-routing-primary">
                                                        <?= esc(lang('UI.appointmentMoveToStage', ['stage' => $routingStageLabels[$nextStage] ?? $formatHumanLabel($nextStage)])) ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <a href="<?= esc(workflow_context_url('appointment/' . $visitId, 'dashboard')) ?>" class="dashboard-routing-primary">
                                                    <?= esc(lang('UI.appointmentViewAction')) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>

                                <?php if (!$groupRows): ?>
                                    <div class="v3-radius border border-dashed v3-border v3-surface px-3 py-5 text-center text-xs font-bold text-slate-500">
                                        <?= esc(lang('UI.dashboardRoutingEmpty')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
                <div class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div class="dashboard-panel-title">
                            <span class="dashboard-panel-icon v3-warm-surface"><i class="fas fa-calendar-check"></i></span>
                            <div>
                                <h2><?= esc(lang('UI.dashboardUpcomingAppointments')) ?></h2>
                                <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardFutureAppointmentsSubtitle')) ?></p>
                            </div>
                        </div>
                        <a href="<?= base_url('appointment') ?>" class="dashboard-view-link">
                            <span><?= esc(lang('UI.dashboardViewAll')) ?></span>
                            <i class="fas <?= $isRtl ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i>
                        </a>
                    </div>
                    <div class="dashboard-list">
                        <?php if (!empty($upcoming_appointments)): ?>
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <?php
                                    $appointmentName = trim((string) (($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? '')));
                                    $appointmentName = $appointmentName !== '' ? $appointmentName : lang('UI.patientUnknown');
                                ?>
                                <a href="<?= base_url('appointment/' . (int) ($appointment['id'] ?? 0)) ?>" class="dashboard-row">
                                    <span class="dashboard-row-icon v3-warm-surface"><i class="fas fa-calendar-day"></i></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm text-slate-950"><?= esc($appointmentName) ?></span>
                                        <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc($formatHumanLabel($appointment['appointment_type'] ?? '')) ?></span>
                                    </span>
                                    <span class="shrink-0 text-end">
                                        <span class="block text-xs text-slate-900" dir="ltr"><?= esc(formatDate($appointment['appointment_date'] ?? '')) ?></span>
                                        <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-800" dir="ltr"><?= esc(formatTime($appointment['appointment_time'] ?? '')) ?></span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-calendar-check mb-3 block text-2xl text-slate-400"></i>
                                <?= esc(lang('UI.dashboardNoUpcomingAppointments')) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div class="dashboard-panel-title">
                            <span class="dashboard-panel-icon v3-warm-surface"><i class="fas fa-stethoscope"></i></span>
                            <div>
                                <h2><?= esc(lang('UI.dashboardRecentExaminations')) ?></h2>
                                <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardLatestPatientExaminations')) ?></p>
                            </div>
                        </div>
                        <a href="<?= base_url('examination') ?>" class="dashboard-view-link">
                            <span><?= esc(lang('UI.dashboardViewAll')) ?></span>
                            <i class="fas <?= $isRtl ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i>
                        </a>
                    </div>
                    <div class="dashboard-list">
                        <?php if (!empty($recent_examinations)): ?>
                            <?php foreach ($recent_examinations as $examination): ?>
                                <?php
                                    $examName = trim((string) (($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? '')));
                                    $examName = $examName !== '' ? $examName : lang('UI.patientUnknown');
                                ?>
                                <a href="<?= base_url('examination/' . (int) ($examination['id'] ?? 0)) ?>" class="dashboard-row">
                                    <span class="dashboard-row-icon v3-warm-surface"><i class="fas fa-notes-medical"></i></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm text-slate-950"><?= esc($examName) ?></span>
                                        <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc($formatHumanLabel($examination['examination_type'] ?? '')) ?></span>
                                    </span>
                                    <span class="shrink-0 text-end">
                                        <span class="block text-xs text-slate-900" dir="ltr"><?= esc(formatDate($examination['examination_date'] ?? '')) ?></span>
                                        <span class="mt-1 dashboard-status dashboard-status-emerald"><?= esc(lang('UI.dashboardCompleted')) ?></span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-stethoscope mb-3 block text-2xl text-slate-400"></i>
                                <?= esc(lang('UI.dashboardNoRecentExaminations')) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <aside class="dashboard-panel-stack v3-command-center-actions">
                <?php if (has_permission('examinations', 'view')): ?>
                <div class="dashboard-panel overflow-hidden">
                    <div class="v3-warm-surface px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="dashboard-panel-icon v3-surface ring-1"><i class="fas fa-file-signature"></i></span>
                                <div>
                                    <h2 class="text-base"><?= esc(lang('Examination.documentation_queue')) ?></h2>
                                    <p class="mt-1 text-xs font-medium text-slate-600"><?= esc(lang('Examination.documentation_queue_help')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <a href="<?= base_url('examination?documentation=ready_to_sign') ?>" class="v3-radius v3-soft-surface p-4 ring-1 ring-indigo-100 transition hover:bg-indigo-100">
                            <p class="text-xs v3-accent-text"><?= esc(lang('Examination.documentation_ready_to_sign')) ?></p>
                            <p class="mt-2 text-2xl v3-accent-text" dir="ltr"><?= $readyToSignCount ?></p>
                        </a>
                        <a href="<?= base_url('examination?documentation=overdue_draft') ?>" class="v3-radius bg-amber-50 p-4 ring-1 ring-amber-100 transition hover:bg-amber-100">
                            <p class="text-xs text-amber-800"><?= esc(lang('Examination.documentation_overdue_draft')) ?></p>
                            <p class="mt-2 text-2xl text-amber-950" dir="ltr"><?= $overdueDraftCount ?></p>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($can_route_appointments)): ?>
                    <?= view('dashboard/partials/appointment_recovery', ['queue' => $appointment_recovery_queue ?? []]) ?>
                <?php endif; ?>
                <?php if ((int) ($acceptedTreatmentQueue['count'] ?? 0) > 0): ?>
                    <div class="dashboard-panel overflow-hidden">
                        <div class="v3-warm-surface px-5 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="dashboard-panel-icon v3-surface ring-1"><i class="fas fa-calendar-plus"></i></span>
                                    <div>
                                        <h2 class="text-base"><?= esc(lang('Treatment.unscheduled_queue')) ?></h2>
                                        <p class="mt-1 text-xs font-medium text-slate-600"><?= esc(lang('Treatment.unscheduled_queue_help')) ?></p>
                                    </div>
                                </div>
                                <span class="rounded-full v3-surface px-3 py-1 text-sm ring-1"><?= (int) ($acceptedTreatmentQueue['count'] ?? 0) ?></span>
                            </div>
                        </div>
                        <div class="dashboard-list">
                            <?php foreach (($acceptedTreatmentQueue['items'] ?? []) as $item): ?>
                                <?php $queuePatientName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? '')); ?>
                                <div class="dashboard-row">
                                    <span class="dashboard-row-icon v3-warm-surface"><i class="fas fa-tooth"></i></span>
                                    <a href="<?= esc(workflow_context_url('treatment/' . (int) $item['treatment_id'], 'dashboard')) ?>" class="min-w-0 flex-1">
                                        <span class="block truncate text-sm text-slate-950"><?= esc($item['procedure_name'] ?? '') ?></span>
                                        <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc($queuePatientName) ?></span>
                                    </a>
                                    <?php if (has_permission('appointments', 'create')): ?>
                                        <a href="<?= base_url('appointment/create?' . http_build_query(['patient_id' => (int) $item['patient_id'], 'clinical_procedure_id' => (int) $item['procedure_id'], 'appointment_type' => 'treatment', 'return_to' => 'dashboard'])) ?>"
                                           data-appointment-create-modal-open data-patient-id="<?= (int) $item['patient_id'] ?>" data-clinical-procedure-id="<?= (int) $item['procedure_id'] ?>" data-appointment-type="treatment"
                                           class="inline-flex h-9 w-9 shrink-0 items-center justify-center v3-radius v3-soft-surface transition hover:bg-indigo-700" title="<?= esc(lang('Treatment.schedule_accepted_treatment'), 'attr') ?>">
                                            <i class="fas fa-calendar-plus"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <a href="<?= base_url('treatment') ?>" class="dashboard-view-link m-4 mt-2"><span><?= esc(lang('UI.dashboardViewAll')) ?></span></a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div class="dashboard-panel-title">
                            <span class="dashboard-panel-icon v3-warm-surface"><i class="fas fa-sack-dollar"></i></span>
                            <div>
                                <h2><?= esc(lang('UI.dashboardRevenue')) ?></h2>
                                <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardRevenueTrends')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-mini-grid">
                        <div class="dashboard-mini-card v3-warm-surface">
                            <p class="text-xs uppercase tracking-wider v3-accent-text"><?= esc(lang('UI.dashboardTotalRevenue')) ?></p>
                            <p class="mt-2 text-2xl" dir="ltr"><?= esc(formatCurrency($totalRevenue)) ?></p>
                        </div>
                        <div class="dashboard-mini-card v3-warm-surface">
                            <p class="text-xs uppercase tracking-wider text-rose-800"><?= esc(lang('Finance.overdue')) ?></p>
                            <p class="mt-2 text-2xl" dir="ltr"><?= esc(formatCurrency($overduePayments)) ?></p>
                        </div>
                    </div>
                    <div class="dashboard-chart-wrap">
                        <div class="dashboard-chart-card">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div class="dashboard-panel-title">
                            <span class="dashboard-panel-icon v3-warm-surface"><i class="fas fa-tooth"></i></span>
                            <div>
                                <h2><?= esc(lang('UI.dashboardTreatmentDistribution')) ?></h2>
                                <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardTreatmentBreakdown')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-mini-grid">
                        <div class="dashboard-mini-card v3-warm-surface">
                            <p class="text-xs uppercase tracking-wider v3-accent-text"><?= esc(lang('UI.dashboardTotalTreatments')) ?></p>
                            <p class="mt-2 text-2xl" dir="ltr"><?= esc(number_format($activeTreatments + $completedTreatments)) ?></p>
                        </div>
                        <div class="dashboard-mini-card v3-warm-surface">
                            <p class="text-xs uppercase tracking-wider text-emerald-800"><?= esc(lang('UI.dashboardCompleted')) ?></p>
                            <p class="mt-2 text-2xl" dir="ltr"><?= esc(number_format($completedTreatments)) ?></p>
                        </div>
                    </div>
                    <div class="dashboard-chart-wrap">
                        <div class="dashboard-chart-card">
                            <canvas id="treatmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <section class="dashboard-bottom-grid mt-5">
            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div class="dashboard-panel-title">
                        <span class="dashboard-panel-icon v3-warm-surface"><i class="fas fa-user-clock"></i></span>
                        <div>
                            <h2><?= esc(lang('UI.dashboardRecentPatients')) ?></h2>
                            <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardLatestPatientRegistrations')) ?></p>
                        </div>
                    </div>
                    <a href="<?= base_url('patient') ?>" class="dashboard-view-link">
                        <span><?= esc(lang('UI.dashboardViewAll')) ?></span>
                        <i class="fas <?= $isRtl ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i>
                    </a>
                </div>
                <div class="dashboard-list">
                    <?php if (!empty($recent_patients)): ?>
                        <?php foreach ($recent_patients as $patient): ?>
                            <?php
                                $patientName = trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')));
                                $patientName = $patientName !== '' ? $patientName : lang('UI.patientUnknown');
                            ?>
                            <a href="<?= base_url('patient/' . (int) ($patient['id'] ?? 0)) ?>" class="dashboard-row">
                                <span class="dashboard-row-icon v3-warm-surface"><i class="fas fa-user"></i></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm text-slate-950"><?= esc($patientName) ?></span>
                                    <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardPatientId')) ?>: <span dir="ltr"><?= esc($patient['patient_id'] ?? '') ?></span></span>
                                </span>
                                <span class="shrink-0 text-end">
                                    <span class="block text-xs text-slate-900" dir="ltr"><?= esc(formatDate($patient['created_at'] ?? '')) ?></span>
                                    <span class="mt-1 dashboard-status dashboard-status-emerald"><?= esc(lang('UI.dashboardActive')) ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dashboard-empty">
                            <i class="fas fa-users mb-3 block text-2xl text-slate-400"></i>
                            <?= esc(lang('UI.dashboardNoRecentPatients')) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div class="dashboard-panel-title">
                        <span class="dashboard-panel-icon v3-warm-surface"><i class="fas fa-bolt"></i></span>
                        <div>
                            <h2><?= esc(lang('UI.dashboardQuickActions')) ?></h2>
                            <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardQuickActionsDesc')) ?></p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
                    <a href="<?= base_url('patient/create') ?>" data-patient-create-modal-open class="dashboard-action v3-warm-surface">
                        <i class="fas fa-user-plus"></i>
                        <span><?= esc(lang('UI.dashboardAddPatient')) ?></span>
                    </a>
                    <a href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="dashboard-action v3-warm-surface">
                        <i class="fas fa-stethoscope"></i>
                        <span><?= esc(lang('UI.dashboardNewExamination')) ?></span>
                    </a>
                    <a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="dashboard-action v3-warm-surface">
                        <i class="fas fa-calendar-plus"></i>
                        <span><?= esc(lang('UI.dashboardBookAppointment')) ?></span>
                    </a>
                    <a href="<?= base_url('finance/create') ?>" class="dashboard-action v3-warm-surface">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span><?= esc(lang('UI.dashboardCreateInvoice')) ?></span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const dashboardI18n = {
    revenue: '<?= esc(lang('UI.dashboardRevenue'), 'js') ?>',
    monthJan: '<?= esc(lang('UI.monthJan'), 'js') ?>',
    monthFeb: '<?= esc(lang('UI.monthFeb'), 'js') ?>',
    monthMar: '<?= esc(lang('UI.monthMar'), 'js') ?>',
    monthApr: '<?= esc(lang('UI.monthApr'), 'js') ?>',
    monthMay: '<?= esc(lang('UI.monthMay'), 'js') ?>',
    monthJun: '<?= esc(lang('UI.monthJun'), 'js') ?>',
    monthJul: '<?= esc(lang('UI.monthJul'), 'js') ?>',
    monthAug: '<?= esc(lang('UI.monthAug'), 'js') ?>',
    monthSep: '<?= esc(lang('UI.monthSep'), 'js') ?>',
    monthOct: '<?= esc(lang('UI.monthOct'), 'js') ?>',
    monthNov: '<?= esc(lang('UI.monthNov'), 'js') ?>',
    monthDec: '<?= esc(lang('UI.monthDec'), 'js') ?>',
    treatCleaning: '<?= esc(lang('UI.treatCleaning'), 'js') ?>',
    treatExtraction: '<?= esc(lang('UI.treatExtraction'), 'js') ?>',
    treatFilling: '<?= esc(lang('UI.treatFilling'), 'js') ?>',
    treatCrown: '<?= esc(lang('UI.treatCrown'), 'js') ?>',
    treatRootCanal: '<?= esc(lang('UI.treatRootCanal'), 'js') ?>',
    treatOrthodontic: '<?= esc(lang('UI.treatOrthodontic'), 'js') ?>',
    treatImplant: '<?= esc(lang('UI.treatImplant'), 'js') ?>',
    treatOther: '<?= esc(lang('UI.treatOther'), 'js') ?>'
};

function dashboardCurrency(value) {
    const amount = Number(value || 0);
    return amount.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

function createEmptyDashboardChart(canvas, type, labels) {
    if (!window.Chart || !canvas) {
        return;
    }

    new Chart(canvas, {
        type,
        data: {
            labels,
            datasets: [{
                label: dashboardI18n.revenue,
                data: labels.map(() => 0),
                borderColor: '#2563eb',
                backgroundColor: type === 'line' ? 'rgba(37, 99, 235, 0.14)' : ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#f59e0b', '#ef4444', '#64748b'],
                tension: 0.38,
                fill: type === 'line'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: type !== 'line', position: 'bottom' } },
            scales: type === 'line' ? {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return dashboardCurrency(value); } }
                }
            } : {}
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const revenueCanvas = document.getElementById('revenueChart');
    const treatmentCanvas = document.getElementById('treatmentChart');
    const months = [dashboardI18n.monthJan, dashboardI18n.monthFeb, dashboardI18n.monthMar, dashboardI18n.monthApr, dashboardI18n.monthMay, dashboardI18n.monthJun, dashboardI18n.monthJul, dashboardI18n.monthAug, dashboardI18n.monthSep, dashboardI18n.monthOct, dashboardI18n.monthNov, dashboardI18n.monthDec];
    const treatmentLabels = [dashboardI18n.treatCleaning, dashboardI18n.treatExtraction, dashboardI18n.treatFilling, dashboardI18n.treatCrown, dashboardI18n.treatRootCanal, dashboardI18n.treatOrthodontic, dashboardI18n.treatImplant, dashboardI18n.treatOther];

    if (!window.Chart) {
        return;
    }

    fetch('<?= base_url('dashboard/chart-data') ?>')
        .then(response => response.ok ? response.json() : Promise.reject(new Error('chart-data')))
        .then(data => {
            const revenueData = new Array(12).fill(0);
            if (Array.isArray(data.monthly_revenue)) {
                data.monthly_revenue.forEach(item => {
                    const month = Number(item.month || 0);
                    if (month >= 1 && month <= 12) {
                        revenueData[month - 1] = Number(item.total_amount || 0);
                    }
                });
            }

            if (revenueCanvas) {
                new Chart(revenueCanvas, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: dashboardI18n.revenue,
                            data: revenueData,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.14)',
                            pointBackgroundColor: '#0f766e',
                            pointRadius: 3,
                            tension: 0.38,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(148, 163, 184, 0.18)' },
                                ticks: { callback: function(value) { return dashboardCurrency(value); } }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            if (treatmentCanvas) {
                new Chart(treatmentCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: treatmentLabels,
                        datasets: [{
                            data: Array.isArray(data.treatment_types) ? data.treatment_types.map(item => Number(item.count || 0)) : treatmentLabels.map(() => 0),
                            backgroundColor: ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#f59e0b', '#ef4444', '#64748b'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } }
                    }
                });
            }
        })
        .catch(() => {
            createEmptyDashboardChart(revenueCanvas, 'line', months);
            createEmptyDashboardChart(treatmentCanvas, 'doughnut', treatmentLabels);
        });
});
</script>
<?= $this->endSection() ?>
