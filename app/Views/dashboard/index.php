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

<style>
    .dashboard-workspace {
        font-family: var(--app-font-sans);
    }

    html[lang^="ar"] .dashboard-workspace {
        font-family: var(--app-font-ar);
    }

    .dashboard-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.65rem;
        background:
            radial-gradient(circle at 10% 10%, rgba(255, 255, 255, 0.22), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(125, 211, 252, 0.18), transparent 30%),
            linear-gradient(135deg, #0f3f90 0%, #1656c1 56%, #0f766e 100%);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, 0.72);
    }

    [dir="rtl"] .dashboard-hero {
        background:
            radial-gradient(circle at 90% 10%, rgba(255, 255, 255, 0.22), transparent 28%),
            radial-gradient(circle at 8% 12%, rgba(125, 211, 252, 0.18), transparent 30%),
            linear-gradient(225deg, #0f3f90 0%, #1656c1 56%, #0f766e 100%);
    }

    .dashboard-hero::after {
        content: "";
        position: absolute;
        inset-block-start: -52%;
        inset-inline-end: -8%;
        width: 22rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
    }

    .dashboard-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        min-height: 2.75rem;
        border-radius: 1rem;
        padding: 0.7rem 0.95rem;
        font-size: 0.82rem;
        font-weight: 800;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        text-align: center;
    }

    [dir="rtl"] .dashboard-action {
        flex-direction: row-reverse;
    }

    .dashboard-action:hover {
        transform: translateY(-1px);
    }

    .dashboard-action-soft {
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.12);
        color: #eff6ff;
        backdrop-filter: blur(12px);
    }

    .dashboard-action-primary {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        box-shadow: 0 18px 32px -24px rgba(5, 150, 105, 0.85);
    }

    .dashboard-action-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #ffffff;
        box-shadow: 0 18px 32px -24px rgba(217, 119, 6, 0.85);
    }

    .dashboard-stat-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        align-items: stretch;
    }

    .dashboard-routing {
        overflow: hidden;
        border: 1px solid rgba(191, 219, 254, 0.8);
        border-radius: 1.45rem;
        background:
            radial-gradient(circle at 0% 0%, rgba(37, 99, 235, 0.13), transparent 30%),
            linear-gradient(135deg, rgba(255, 255, 255, 0.99), rgba(241, 245, 249, 0.97));
        box-shadow: 0 24px 58px -42px rgba(15, 23, 42, 0.48);
    }

    .dashboard-routing-board {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(16.5rem, 1fr);
        gap: 0.85rem;
        overflow-x: auto;
        padding: 0 1rem 1rem;
        scroll-snap-type: inline proximity;
    }

    @media (min-width: 1536px) {
        .dashboard-routing-board {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            grid-auto-flow: row;
            grid-auto-columns: auto;
            overflow-x: visible;
        }
    }

    .dashboard-routing-column {
        min-height: 12rem;
        scroll-snap-align: start;
        border-radius: 1.1rem;
        padding: 0.75rem;
    }

    .dashboard-routing-column-blue { background: linear-gradient(180deg, #dbeafe, #eff6ff); }
    .dashboard-routing-column-amber { background: linear-gradient(180deg, #fef3c7, #fffbeb); }
    .dashboard-routing-column-indigo { background: linear-gradient(180deg, #e0e7ff, #eef2ff); }
    .dashboard-routing-column-emerald { background: linear-gradient(180deg, #d1fae5, #ecfdf5); }
    .dashboard-routing-column-rose { background: linear-gradient(180deg, #ffe4e6, #fff1f2); }

    .dashboard-routing-card {
        border: 1px solid rgba(255, 255, 255, 0.92);
        border-radius: 0.95rem;
        background: rgba(255, 255, 255, 0.94);
        padding: 0.75rem;
        box-shadow: 0 14px 28px -24px rgba(15, 23, 42, 0.68);
    }

    .dashboard-routing-primary {
        display: inline-flex;
        min-height: 2.35rem;
        width: 100%;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        background: #1d4ed8;
        padding: 0.5rem 0.7rem;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 900;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .dashboard-routing-primary:hover {
        background: #1e40af;
        transform: translateY(-1px);
    }

    @media (min-width: 640px) {
        .dashboard-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1180px) {
        .dashboard-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .dashboard-stat-card {
        position: relative;
        display: flex;
        height: 100%;
        overflow: hidden;
        min-height: 8.6rem;
        border-radius: 1.35rem;
        padding: 1.1rem;
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 18px 38px -30px rgba(15, 23, 42, 0.62);
    }

    .dashboard-stat-card::after {
        content: "";
        position: absolute;
        inset-block-start: -48%;
        inset-inline-end: -14%;
        width: 11rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
    }

    .dashboard-stat-card > * {
        position: relative;
        z-index: 1;
        width: 100%;
    }

    .dashboard-stat-card > .flex {
        min-height: 100%;
    }

    .dashboard-stat-blue {
        background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.24), transparent 34%), linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a);
    }

    .dashboard-stat-emerald {
        background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.24), transparent 34%), linear-gradient(135deg, #059669, #047857 56%, #064e3b);
    }

    .dashboard-stat-amber {
        background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.24), transparent 34%), linear-gradient(135deg, #d97706, #b45309 58%, #78350f);
    }

    .dashboard-stat-purple {
        background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.21), transparent 34%), linear-gradient(135deg, #7c3aed, #6d28d9 56%, #4c1d95);
    }

    .dashboard-stat-label {
        font-size: 0.84rem;
        font-weight: 900;
        letter-spacing: 0.055em;
        text-transform: uppercase;
        color: rgba(239, 246, 255, 0.86);
    }

    .dashboard-stat-value {
        margin-top: 0.5rem;
        font-size: 1.8rem;
        line-height: 1.1;
        font-weight: 800;
        color: #ffffff;
    }

    .dashboard-stat-meta {
        margin-top: 0.45rem;
        font-size: 0.8rem;
        line-height: 1.35;
        color: rgba(239, 246, 255, 0.84);
    }

    .dashboard-panel {
        position: relative;
        display: flex;
        height: 100%;
        flex-direction: column;
        overflow: hidden;
        border-radius: 1.35rem;
        border: 1px solid rgba(255, 255, 255, 0.76);
        background:
            radial-gradient(circle at 0% 0%, rgba(37, 99, 235, 0.1), transparent 30%),
            radial-gradient(circle at 100% 10%, rgba(16, 185, 129, 0.09), transparent 32%),
            linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        box-shadow: 0 24px 58px -42px rgba(15, 23, 42, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .dashboard-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem 0.75rem;
    }

    .dashboard-panel-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        color: #0f172a;
        font-size: 1.05rem;
        line-height: 1.25;
        font-weight: 800;
    }

    [dir="rtl"] .dashboard-panel-title {
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .dashboard-panel-icon {
        display: inline-flex;
        width: 2.25rem;
        height: 2.25rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 0.85rem;
        color: #ffffff;
        box-shadow: 0 16px 28px -22px rgba(15, 23, 42, 0.72);
    }

    .dashboard-view-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 999px;
        background: #ffffff;
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 800;
        color: #1d4ed8;
        box-shadow: inset 0 0 0 1px rgba(191, 219, 254, 0.9), 0 12px 22px -20px rgba(37, 99, 235, 0.45);
    }

    [dir="rtl"] .dashboard-view-link {
        flex-direction: row-reverse;
    }

    .dashboard-list {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        gap: 0.7rem;
        padding: 0 1.1rem 1.1rem;
    }

    .dashboard-row {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        border-radius: 1rem;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.82);
        padding: 0.82rem;
        color: #0f172a;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    [dir="rtl"] .dashboard-row {
        flex-direction: row-reverse;
    }

    .dashboard-row:hover {
        transform: translateY(-1px);
        border-color: rgba(147, 197, 253, 0.92);
        box-shadow: 0 18px 34px -28px rgba(37, 99, 235, 0.42);
    }

    .dashboard-row-icon {
        display: inline-flex;
        width: 2.55rem;
        height: 2.55rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 0.95rem;
        color: #ffffff;
        box-shadow: 0 16px 26px -22px rgba(15, 23, 42, 0.7);
    }

    .dashboard-empty {
        margin: 0 1.1rem 1.1rem;
        border-radius: 1.1rem;
        border: 1px dashed #cbd5e1;
        background: rgba(248, 250, 252, 0.84);
        padding: 2rem 1rem;
        text-align: center;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .dashboard-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.28rem 0.65rem;
        font-size: 0.7rem;
        font-weight: 800;
        box-shadow: inset 0 0 0 1px transparent;
        white-space: nowrap;
    }

    .dashboard-status-emerald {
        background: #dcfce7;
        color: #166534;
        box-shadow: inset 0 0 0 1px rgba(187, 247, 208, 0.9);
    }

    .dashboard-status-amber {
        background: #fef3c7;
        color: #92400e;
        box-shadow: inset 0 0 0 1px rgba(253, 230, 138, 0.92);
    }

    .dashboard-status-rose {
        background: #ffe4e6;
        color: #be123c;
        box-shadow: inset 0 0 0 1px rgba(254, 205, 211, 0.92);
    }

    .dashboard-status-slate {
        background: #f1f5f9;
        color: #475569;
        box-shadow: inset 0 0 0 1px rgba(203, 213, 225, 0.92);
    }

    .dashboard-mini-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.8rem;
        padding: 0 1.1rem 1.1rem;
    }

    @media (min-width: 640px) {
        .dashboard-mini-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .dashboard-mini-card {
        min-height: 6.4rem;
        border-radius: 1.05rem;
        padding: 0.95rem;
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 18px 34px -28px rgba(15, 23, 42, 0.55);
    }

    .dashboard-chart-wrap {
        flex: 1 1 auto;
        height: 17rem;
        padding: 0 1.1rem 1.1rem;
    }

    .dashboard-chart-card {
        height: 100%;
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.78);
        background: rgba(255, 255, 255, 0.78);
        padding: 0.8rem;
    }

    .dashboard-content-grid,
    .dashboard-bottom-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .dashboard-panel-stack {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    @media (min-width: 1280px) {
        .dashboard-content-grid,
        .dashboard-bottom-grid {
            grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
        }

        .dashboard-panel-stack {
            grid-template-rows: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="dashboard-workspace min-h-screen">
    <div class="container mx-auto px-3 py-4 sm:px-4 sm:py-6">
        <section class="dashboard-hero mb-5 p-4 sm:p-5 lg:p-6">
            <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/12 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-blue-100 backdrop-blur">
                        <i class="fas fa-heart-pulse text-emerald-200"></i>
                        <span><?= esc($todayLabel) ?></span>
                    </div>
                    <h1 class="mt-4 text-2xl font-black leading-tight text-white sm:text-3xl lg:text-4xl">
                        <?= esc(lang('UI.menuDashboard')) ?>
                    </h1>
                </div>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:min-w-[28rem]">
                    <a href="<?= base_url('patient/create') ?>" data-patient-create-modal-open class="dashboard-action dashboard-action-primary">
                        <i class="fas fa-user-plus"></i>
                        <span><?= esc(lang('UI.dashboardAddPatient')) ?></span>
                    </a>
                    <a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="dashboard-action dashboard-action-gold">
                        <i class="fas fa-calendar-plus"></i>
                        <span><?= esc(lang('UI.dashboardBookAppointment')) ?></span>
                    </a>
                    <a href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="dashboard-action dashboard-action-soft">
                        <i class="fas fa-stethoscope"></i>
                        <span><?= esc(lang('UI.dashboardNewExamination')) ?></span>
                    </a>
                    <a href="<?= base_url('finance/create') ?>" class="dashboard-action dashboard-action-soft">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span><?= esc(lang('UI.dashboardCreateInvoice')) ?></span>
                    </a>
                </div>
            </div>
        </section>

        <?php if ($canRouteAppointments): ?>
            <section class="dashboard-routing mb-5" aria-labelledby="today-routing-title">
                <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-800 text-white shadow-lg shadow-blue-500/20">
                            <i class="fas fa-route"></i>
                        </span>
                        <div>
                            <h2 id="today-routing-title" class="text-lg font-black text-slate-950 sm:text-xl"><?= esc(lang('UI.dashboardRoutingTitle')) ?></h2>
                            <p class="mt-0.5 text-sm font-semibold text-slate-600"><?= esc(lang('UI.dashboardRoutingSubtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-blue-100 px-3 py-1.5 text-xs font-black text-blue-800">
                            <?= esc(lang('UI.dashboardRoutingOpenCount', ['count' => (int) ($todayRouting['open_count'] ?? 0)])) ?>
                        </span>
                        <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-800">
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
                                <span class="flex items-center gap-2 text-sm font-black text-slate-900">
                                    <i class="fas <?= esc($groupConfig['icon'], 'attr') ?>"></i>
                                    <span><?= esc($groupConfig['label']) ?></span>
                                </span>
                                <span class="flex h-7 min-w-7 items-center justify-center rounded-full bg-white px-2 text-xs font-black text-slate-700 shadow-sm" dir="ltr"><?= count($groupRows) ?></span>
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
                                                <span class="block truncate text-sm font-black text-slate-950"><?= esc($patientName) ?></span>
                                                <span class="mt-1 block truncate text-xs font-bold text-slate-500">
                                                    <?= esc($formatHumanLabel($visit['appointment_type'] ?? '')) ?>
                                                    <?php if (!empty($visit['chair_name'])): ?>
                                                        <span aria-hidden="true"> · </span><?= esc($visit['chair_name']) ?>
                                                    <?php endif; ?>
                                                </span>
                                            </a>
                                            <span class="shrink-0 text-end">
                                                <span class="block text-sm font-black text-slate-950" dir="ltr"><?= esc(formatTime($visit['appointment_time'] ?? '')) ?></span>
                                                <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-black text-slate-700"><?= esc($routingStageLabels[$stage] ?? $formatHumanLabel($stage)) ?></span>
                                            </span>
                                        </div>

                                        <?php if (!empty($visit['is_late'])): ?>
                                            <p class="mt-2 text-xs font-black text-rose-700">
                                                <i class="fas fa-clock me-1"></i><?= esc(lang('UI.dashboardRoutingLate', ['minutes' => (int) ($visit['late_minutes'] ?? 0)])) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="mt-3 flex items-center gap-2">
                                            <?php if ($patientId > 0): ?>
                                                <a href="<?= esc(workflow_context_url('patient/' . $patientId, 'dashboard')) ?>" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 transition hover:bg-slate-200" title="<?= esc(lang('UI.patientProfile'), 'attr') ?>">
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
                                    <div class="rounded-xl border border-dashed border-white bg-white/55 px-3 py-5 text-center text-xs font-bold text-slate-500">
                                        <?= esc(lang('UI.dashboardRoutingEmpty')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="dashboard-stat-grid mb-5">
            <a href="<?= base_url('patient') ?>" class="dashboard-stat-card dashboard-stat-blue">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTotalPatients')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(number_format($totalPatients)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('UI.dashboardRecentPatients')) ?>: <?= esc(number_format($recentPatientCount)) ?></p>
                    </div>
                    <i class="fas fa-users text-3xl text-white/80"></i>
                </div>
            </a>

            <a href="<?= base_url('examination') ?>" class="dashboard-stat-card dashboard-stat-emerald">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTodayExaminations')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(number_format($todayExaminations)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('UI.dashboardRecentExaminations')) ?>: <?= esc(number_format($recentExamCount)) ?></p>
                    </div>
                    <i class="fas fa-stethoscope text-3xl text-white/80"></i>
                </div>
            </a>

            <a href="<?= base_url('appointment') ?>" class="dashboard-stat-card dashboard-stat-amber">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTodayAppointments')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(number_format($todayAppointments)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('UI.dashboardUpcomingAppointments')) ?>: <?= esc(number_format($upcomingAppointmentsCount)) ?></p>
                    </div>
                    <i class="fas fa-calendar-days text-3xl text-white/80"></i>
                </div>
            </a>

            <a href="<?= base_url('finance') ?>" class="dashboard-stat-card dashboard-stat-purple">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-stat-label"><?= esc(lang('UI.dashboardTotalRevenue')) ?></p>
                        <p class="dashboard-stat-value" dir="ltr"><?= esc(formatCurrencyAbbreviated($totalRevenue)) ?></p>
                        <p class="dashboard-stat-meta"><?= esc(lang('Finance.pendingPayments')) ?>: <?= esc(formatCurrencyAbbreviated($pendingPayments)) ?></p>
                    </div>
                    <i class="fas fa-chart-line text-3xl text-white/80"></i>
                </div>
            </a>
        </section>

        <div class="dashboard-content-grid">
            <section class="dashboard-panel-stack">
                <div class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div class="dashboard-panel-title">
                            <span class="dashboard-panel-icon bg-gradient-to-br from-amber-500 to-orange-600"><i class="fas fa-calendar-check"></i></span>
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
                                    <span class="dashboard-row-icon bg-gradient-to-br from-amber-500 to-orange-600"><i class="fas fa-calendar-day"></i></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-black text-slate-950"><?= esc($appointmentName) ?></span>
                                        <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc($formatHumanLabel($appointment['appointment_type'] ?? '')) ?></span>
                                    </span>
                                    <span class="shrink-0 text-end">
                                        <span class="block text-xs font-black text-slate-900" dir="ltr"><?= esc(formatDate($appointment['appointment_date'] ?? '')) ?></span>
                                        <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-black text-amber-800" dir="ltr"><?= esc(formatTime($appointment['appointment_time'] ?? '')) ?></span>
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
                            <span class="dashboard-panel-icon bg-gradient-to-br from-emerald-500 to-teal-700"><i class="fas fa-stethoscope"></i></span>
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
                                    <span class="dashboard-row-icon bg-gradient-to-br from-emerald-500 to-teal-700"><i class="fas fa-notes-medical"></i></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-black text-slate-950"><?= esc($examName) ?></span>
                                        <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc($formatHumanLabel($examination['examination_type'] ?? '')) ?></span>
                                    </span>
                                    <span class="shrink-0 text-end">
                                        <span class="block text-xs font-black text-slate-900" dir="ltr"><?= esc(formatDate($examination['examination_date'] ?? '')) ?></span>
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

            <aside class="dashboard-panel-stack">
                <?php if (has_permission('examinations', 'view')): ?>
                <div class="dashboard-panel overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-700 via-blue-700 to-cyan-700 px-5 py-4 text-white">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="dashboard-panel-icon bg-white/15 ring-1 ring-white/20"><i class="fas fa-file-signature"></i></span>
                                <div>
                                    <h2 class="text-base font-black"><?= esc(lang('Examination.documentation_queue')) ?></h2>
                                    <p class="mt-1 text-xs font-bold text-blue-100"><?= esc(lang('Examination.documentation_queue_help')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <a href="<?= base_url('examination?documentation=ready_to_sign') ?>" class="rounded-2xl bg-indigo-50 p-4 ring-1 ring-indigo-100 transition hover:bg-indigo-100">
                            <p class="text-xs font-black text-indigo-700"><?= esc(lang('Examination.documentation_ready_to_sign')) ?></p>
                            <p class="mt-2 text-2xl font-black text-indigo-950" dir="ltr"><?= $readyToSignCount ?></p>
                        </a>
                        <a href="<?= base_url('examination?documentation=overdue_draft') ?>" class="rounded-2xl bg-amber-50 p-4 ring-1 ring-amber-100 transition hover:bg-amber-100">
                            <p class="text-xs font-black text-amber-800"><?= esc(lang('Examination.documentation_overdue_draft')) ?></p>
                            <p class="mt-2 text-2xl font-black text-amber-950" dir="ltr"><?= $overdueDraftCount ?></p>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ((int) ($acceptedTreatmentQueue['count'] ?? 0) > 0): ?>
                    <div class="dashboard-panel overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-violet-800 px-5 py-4 text-white">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="dashboard-panel-icon bg-white/15 ring-1 ring-white/20"><i class="fas fa-calendar-plus"></i></span>
                                    <div>
                                        <h2 class="text-base font-black"><?= esc(lang('Treatment.unscheduled_queue')) ?></h2>
                                        <p class="mt-1 text-xs font-bold text-indigo-100"><?= esc(lang('Treatment.unscheduled_queue_help')) ?></p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-sm font-black ring-1 ring-white/20"><?= (int) ($acceptedTreatmentQueue['count'] ?? 0) ?></span>
                            </div>
                        </div>
                        <div class="dashboard-list">
                            <?php foreach (($acceptedTreatmentQueue['items'] ?? []) as $item): ?>
                                <?php $queuePatientName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? '')); ?>
                                <div class="dashboard-row">
                                    <span class="dashboard-row-icon bg-gradient-to-br from-indigo-600 to-violet-700"><i class="fas fa-tooth"></i></span>
                                    <a href="<?= esc(workflow_context_url('treatment/' . (int) $item['treatment_id'], 'dashboard')) ?>" class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-black text-slate-950"><?= esc($item['procedure_name'] ?? '') ?></span>
                                        <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc($queuePatientName) ?></span>
                                    </a>
                                    <?php if (has_permission('appointments', 'create')): ?>
                                        <a href="<?= base_url('appointment/create?' . http_build_query(['patient_id' => (int) $item['patient_id'], 'clinical_procedure_id' => (int) $item['procedure_id'], 'appointment_type' => 'treatment', 'return_to' => 'dashboard'])) ?>"
                                           data-appointment-create-modal-open data-patient-id="<?= (int) $item['patient_id'] ?>" data-clinical-procedure-id="<?= (int) $item['procedure_id'] ?>" data-appointment-type="treatment"
                                           class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white transition hover:bg-indigo-700" title="<?= esc(lang('Treatment.schedule_accepted_treatment'), 'attr') ?>">
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
                            <span class="dashboard-panel-icon bg-gradient-to-br from-violet-600 to-fuchsia-700"><i class="fas fa-sack-dollar"></i></span>
                            <div>
                                <h2><?= esc(lang('UI.dashboardRevenue')) ?></h2>
                                <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardRevenueTrends')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-mini-grid">
                        <div class="dashboard-mini-card bg-gradient-to-br from-violet-600 to-purple-800">
                            <p class="text-xs font-black uppercase tracking-wider text-violet-100"><?= esc(lang('UI.dashboardTotalRevenue')) ?></p>
                            <p class="mt-2 text-2xl font-black" dir="ltr"><?= esc(formatCurrency($totalRevenue)) ?></p>
                        </div>
                        <div class="dashboard-mini-card bg-gradient-to-br from-rose-600 to-orange-700">
                            <p class="text-xs font-black uppercase tracking-wider text-rose-100"><?= esc(lang('Finance.overdue')) ?></p>
                            <p class="mt-2 text-2xl font-black" dir="ltr"><?= esc(formatCurrency($overduePayments)) ?></p>
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
                            <span class="dashboard-panel-icon bg-gradient-to-br from-sky-600 to-blue-800"><i class="fas fa-tooth"></i></span>
                            <div>
                                <h2><?= esc(lang('UI.dashboardTreatmentDistribution')) ?></h2>
                                <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardTreatmentBreakdown')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-mini-grid">
                        <div class="dashboard-mini-card bg-gradient-to-br from-sky-600 to-blue-800">
                            <p class="text-xs font-black uppercase tracking-wider text-sky-100"><?= esc(lang('UI.dashboardTotalTreatments')) ?></p>
                            <p class="mt-2 text-2xl font-black" dir="ltr"><?= esc(number_format($activeTreatments + $completedTreatments)) ?></p>
                        </div>
                        <div class="dashboard-mini-card bg-gradient-to-br from-emerald-600 to-teal-800">
                            <p class="text-xs font-black uppercase tracking-wider text-emerald-100"><?= esc(lang('UI.dashboardCompleted')) ?></p>
                            <p class="mt-2 text-2xl font-black" dir="ltr"><?= esc(number_format($completedTreatments)) ?></p>
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
                        <span class="dashboard-panel-icon bg-gradient-to-br from-blue-600 to-indigo-800"><i class="fas fa-user-clock"></i></span>
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
                                <span class="dashboard-row-icon bg-gradient-to-br from-blue-600 to-indigo-800"><i class="fas fa-user"></i></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-950"><?= esc($patientName) ?></span>
                                    <span class="mt-0.5 block truncate text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardPatientId')) ?>: <span dir="ltr"><?= esc($patient['patient_id'] ?? '') ?></span></span>
                                </span>
                                <span class="shrink-0 text-end">
                                    <span class="block text-xs font-black text-slate-900" dir="ltr"><?= esc(formatDate($patient['created_at'] ?? '')) ?></span>
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
                        <span class="dashboard-panel-icon bg-gradient-to-br from-slate-700 to-slate-950"><i class="fas fa-bolt"></i></span>
                        <div>
                            <h2><?= esc(lang('UI.dashboardQuickActions')) ?></h2>
                            <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardQuickActionsDesc')) ?></p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
                    <a href="<?= base_url('patient/create') ?>" data-patient-create-modal-open class="dashboard-action bg-gradient-to-br from-blue-600 to-indigo-800 text-white shadow-lg shadow-blue-500/20">
                        <i class="fas fa-user-plus"></i>
                        <span><?= esc(lang('UI.dashboardAddPatient')) ?></span>
                    </a>
                    <a href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="dashboard-action bg-gradient-to-br from-emerald-600 to-teal-800 text-white shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-stethoscope"></i>
                        <span><?= esc(lang('UI.dashboardNewExamination')) ?></span>
                    </a>
                    <a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="dashboard-action bg-gradient-to-br from-amber-500 to-orange-700 text-white shadow-lg shadow-amber-500/20">
                        <i class="fas fa-calendar-plus"></i>
                        <span><?= esc(lang('UI.dashboardBookAppointment')) ?></span>
                    </a>
                    <a href="<?= base_url('finance/create') ?>" class="dashboard-action bg-gradient-to-br from-violet-600 to-purple-800 text-white shadow-lg shadow-violet-500/20">
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
