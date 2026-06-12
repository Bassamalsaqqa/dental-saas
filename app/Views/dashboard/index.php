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

$formatHumanLabel = static function (?string $value): string {
    $value = trim((string) $value);
    return $value !== '' ? ucfirst(str_replace('_', ' ', $value)) : '-';
};
?>

<style>
    .dashboard-workspace {
        font-family: var(--app-font-sans);
        background:
            radial-gradient(900px 520px at 4% -10%, rgba(37, 99, 235, 0.11), transparent 60%),
            radial-gradient(720px 430px at 94% 0%, rgba(16, 185, 129, 0.1), transparent 58%),
            #f8fafc;
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
                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-relaxed text-blue-100/90">
                        <?= esc($clinicName) ?> - <?= esc(lang('UI.dashboardQuickActionsDesc')) ?>
                    </p>
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

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
            <section class="xl:col-span-7 space-y-5">
                <div class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div class="dashboard-panel-title">
                            <span class="dashboard-panel-icon bg-gradient-to-br from-amber-500 to-orange-600"><i class="fas fa-calendar-check"></i></span>
                            <div>
                                <h2><?= esc(lang('UI.dashboardUpcomingAppointments')) ?></h2>
                                <p class="mt-0.5 text-xs font-bold text-slate-500"><?= esc(lang('UI.dashboardTodayScheduledAppointments')) ?></p>
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

            <aside class="xl:col-span-5 space-y-5">
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

        <section class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-12">
            <div class="dashboard-panel xl:col-span-7">
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

            <div class="dashboard-panel xl:col-span-5">
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
