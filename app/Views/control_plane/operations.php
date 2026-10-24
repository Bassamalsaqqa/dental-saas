<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$healthChecks = is_array($healthChecks ?? null) ? $healthChecks : [];
$planAudits = is_array($planAudits ?? null) ? $planAudits : [];
$clinicDirectory = is_array($clinicDirectory ?? null) ? $clinicDirectory : [];
$healthyCount = count(array_filter($healthChecks, static fn ($check): bool => !empty($check['status'])));
$attentionCount = max(0, count($healthChecks) - $healthyCount);
$environmentLabel = (string) ($environment ?? ENVIRONMENT);
$phpVersionLabel = (string) ($phpVersion ?? PHP_VERSION);
?>

<style>
    .ops-page-shell {
        background: rgba(248, 250, 252, 0.82);
    }

    .ops-hero-card {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        border-radius: 26px;
        color: #fff;
        background:
            radial-gradient(circle at 12% 18%, rgba(255,255,255,0.18), transparent 30%),
            radial-gradient(circle at 88% 20%, rgba(125,211,252,0.20), transparent 26%),
            linear-gradient(135deg, #0f3f90 0%, #1656c1 58%, #0f766e 100%);
        border: 1px solid rgba(203,213,225,0.55);
        box-shadow: 0 28px 70px -44px rgba(15,23,42,0.75);
    }

    .ops-hero-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
        background:
            radial-gradient(circle at 4% 84%, rgba(255,255,255,0.10), transparent 28%),
            linear-gradient(120deg, rgba(255,255,255,0.04), rgba(255,255,255,0));
    }

    [dir="rtl"] .ops-hero-card {
        background:
            radial-gradient(circle at 88% 18%, rgba(255,255,255,0.18), transparent 30%),
            radial-gradient(circle at 12% 20%, rgba(125,211,252,0.20), transparent 26%),
            linear-gradient(225deg, #0f3f90 0%, #1656c1 58%, #0f766e 100%);
    }

    .ops-hero-card > * {
        position: relative;
        z-index: 1;
    }

    .ops-hero-avatar {
        display: inline-flex;
        width: 4.35rem;
        height: 4.35rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 1.55rem;
        background: linear-gradient(145deg, rgba(255,255,255,0.24), rgba(153,246,228,0.18));
        border: 1px solid rgba(255,255,255,0.22);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.15), 0 22px 40px -24px rgba(15,23,42,0.72);
        color: #fff;
        font-size: 1.9rem;
    }

    .ops-hero-chip {
        display: inline-flex;
        min-height: 2.42rem;
        width: 100%;
        align-items: center;
        gap: 0.48rem;
        overflow: hidden;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.16);
        background: rgba(255,255,255,0.12);
        padding: 0.46rem 0.72rem;
        color: #eff6ff;
        backdrop-filter: blur(12px);
    }

    .ops-hero-chip-icon {
        display: inline-flex;
        width: 1.65rem;
        height: 1.65rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 999px;
        background: rgba(255,255,255,0.12);
        font-size: 0.72rem;
    }

    .ops-hero-chip-text {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 0.08rem;
    }

    .ops-hero-chip-label {
        color: rgba(219,234,254,0.86);
        font-size: 0.66rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .ops-hero-chip-value {
        overflow: hidden;
        color: #fff;
        font-size: 0.9rem;
        font-weight: 900;
        line-height: 1.15;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ops-hero-side-card {
        border-radius: 1.3rem;
        border: 1px solid rgba(255,255,255,0.24);
        background:
            radial-gradient(circle at 18% 0%, rgba(255,255,255,0.22), transparent 38%),
            linear-gradient(145deg, rgba(255,255,255,0.20), rgba(8,47,73,0.18));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 20px 42px -30px rgba(15,23,42,0.72);
        backdrop-filter: blur(12px);
    }

    .ops-health-metric {
        border-radius: 1rem;
        border: 1px solid rgba(255,255,255,0.22);
        padding: 0.9rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 14px 26px -24px rgba(15,23,42,0.78);
    }

    .ops-health-metric-ok {
        background:
            radial-gradient(circle at 0% 0%, rgba(255,255,255,0.24), transparent 42%),
            linear-gradient(145deg, rgba(16,185,129,0.42), rgba(15,118,110,0.20));
    }

    .ops-health-metric-warn {
        background:
            radial-gradient(circle at 0% 0%, rgba(255,255,255,0.24), transparent 42%),
            linear-gradient(145deg, rgba(245,158,11,0.42), rgba(217,119,6,0.20));
    }

    .ops-health-metric-value {
        color: #fff;
        font-size: 1.75rem;
        font-weight: 950;
        line-height: 1;
        text-shadow: 0 1px 10px rgba(15,23,42,0.22);
    }

    .ops-health-metric-label {
        margin-top: 0.42rem;
        color: rgba(239,246,255,0.92);
        font-size: 0.72rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .ops-workspace-section {
        position: relative;
        overflow: hidden;
        border-radius: 1.42rem;
        border: 1px solid rgba(255,255,255,0.76);
        background:
            radial-gradient(circle at 0% 0%, rgba(37,99,235,0.11), transparent 28%),
            radial-gradient(circle at 100% 18%, rgba(124,58,237,0.12), transparent 30%),
            linear-gradient(135deg, rgba(248,250,252,0.98), rgba(255,255,255,0.96));
        box-shadow: 0 24px 58px -42px rgba(15,23,42,0.42), 0 1px 0 rgba(255,255,255,0.92) inset;
    }

    .ops-workspace-section::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 9% 0%, rgba(37,99,235,0.14), transparent 29%),
            radial-gradient(circle at 95% 10%, rgba(5,150,105,0.12), transparent 30%);
    }

    .ops-workspace-section > * {
        position: relative;
        z-index: 1;
    }

    .ops-tab-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 999px;
        padding: 0.62rem 0.9rem;
        border: 1px solid rgba(226,232,240,0.86);
        background: rgba(255,255,255,0.86);
        color: #475569;
        font-size: 0.82rem;
        font-weight: 900;
        white-space: nowrap;
        transition: all 0.18s ease;
        box-shadow: 0 12px 30px -24px rgba(15,23,42,0.34);
    }

    .ops-tab-button:hover {
        color: #0f172a;
        transform: translateY(-1px);
    }

    .ops-tab-button.is-active {
        color: #fff;
        border-color: rgba(255,255,255,0.55);
        background: linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a);
        box-shadow: 0 18px 34px -28px rgba(37,99,235,0.72);
    }

    .ops-section-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.08rem;
        border: 1px solid rgba(255,255,255,0.82);
        background:
            radial-gradient(circle at 8% 0%, rgba(37,99,235,0.15), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(239,246,255,0.92) 46%, rgba(255,255,255,0.98));
        box-shadow: 0 20px 48px -38px rgba(15,23,42,0.48), 0 1px 0 rgba(255,255,255,0.92) inset;
    }

    .ops-section-card-health {
        background:
            radial-gradient(circle at 8% 0%, rgba(5,150,105,0.18), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(209,250,229,0.92) 46%, rgba(255,255,255,0.98));
    }

    .ops-section-card-tools {
        background:
            radial-gradient(circle at 8% 0%, rgba(79,70,229,0.17), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(238,242,255,0.92) 46%, rgba(255,255,255,0.98));
    }

    .ops-section-card-maintenance {
        background:
            radial-gradient(circle at 8% 0%, rgba(217,119,6,0.18), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(254,243,199,0.92) 46%, rgba(255,255,255,0.98));
    }

    .ops-section-header {
        border-bottom: 1px solid rgba(203,213,225,0.72);
        background: linear-gradient(90deg, rgba(255,255,255,0.72), rgba(219,234,254,0.88));
    }

    .ops-section-title-dot {
        display: inline-flex;
        width: 2.25rem;
        height: 2.25rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, #2563eb, #1e40af);
        color: #fff;
        box-shadow: 0 16px 26px -20px rgba(15,23,42,0.78);
    }

    .ops-link-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        width: 100%;
        border-radius: 1rem;
        border: 1px solid rgba(226,232,240,0.88);
        background: rgba(255,255,255,0.86);
        padding: 0.82rem 0.9rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
        transition: all 0.18s ease;
    }

    .ops-link-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 32px -30px rgba(15,23,42,0.42), inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .ops-link-icon {
        display: inline-flex;
        width: 2.5rem;
        height: 2.5rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 0.9rem;
        color: #fff;
        box-shadow: 0 14px 24px -18px rgba(15,23,42,0.62);
    }

    .ops-audit-table th {
        padding: 0.9rem 1rem;
        color: rgba(239,246,255,0.92);
        background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
        border-bottom: 1px solid rgba(148,163,184,0.36);
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-align: start;
        text-transform: uppercase;
    }

    .ops-audit-table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(226,232,240,0.86);
        background: rgba(255,255,255,0.96);
        vertical-align: top;
    }

    .ops-audit-table tbody tr:nth-child(even) td {
        background: rgba(248,250,252,0.98);
    }
</style>

<div class="v3-platform-workspace ops-page-shell min-h-screen">
    <div class="mx-auto max-w-7xl space-y-5 px-4 py-5 sm:px-5 lg:px-6">
        <section class="ops-hero-card">
            <div class="grid gap-4 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_19rem]">
                <div class="flex flex-col justify-between gap-3">
                    <div class="flex items-start gap-3.5">
                        <span class="ops-hero-avatar">
                            <i class="fas fa-cogs"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-[0.28em] cp-accent"><?= esc(lang('V3Platform.controlPlane')) ?></p>
                            <h1 class="mt-1 cp-title font-black tracking-tight text-white"><?= esc(lang('V3Platform.operationsConsole')) ?></h1>
                            <p class="mt-1.5 max-w-3xl text-sm font-semibold leading-5 cp-accent"><?= esc(lang('V3Platform.runtimeHealthOperationalShortcutsAndPlanEnforcementVisibilityForTheGlobalApp')) ?></p>
                        </div>
                    </div>

                    <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="ops-hero-chip">
                            <span class="ops-hero-chip-icon"><i class="fas fa-globe"></i></span>
                            <span class="ops-hero-chip-text">
                                <span class="ops-hero-chip-label"><?= esc(lang('V3Platform.mode')) ?></span>
                                <span class="ops-hero-chip-value"><?= esc(lang('V3Platform.globalMode2')) ?></span>
                            </span>
                        </div>
                        <div class="ops-hero-chip">
                            <span class="ops-hero-chip-icon"><i class="fas fa-server"></i></span>
                            <span class="ops-hero-chip-text">
                                <span class="ops-hero-chip-label"><?= esc(lang('V3Platform.environment')) ?></span>
                                <span class="ops-hero-chip-value"><?= esc($environmentLabel) ?></span>
                            </span>
                        </div>
                        <div class="ops-hero-chip">
                            <span class="ops-hero-chip-icon"><i class="fab fa-php"></i></span>
                            <span class="ops-hero-chip-text">
                                <span class="ops-hero-chip-label"><?= esc(lang('V3Platform.runtime')) ?></span>
                                <span class="ops-hero-chip-value">PHP <?= esc($phpVersionLabel) ?></span>
                            </span>
                        </div>
                        <div class="ops-hero-chip">
                            <span class="ops-hero-chip-icon"><i class="fas fa-shield-halved"></i></span>
                            <span class="ops-hero-chip-text">
                                <span class="ops-hero-chip-label"><?= esc(lang('V3Platform.guardrails')) ?></span>
                                <span class="ops-hero-chip-value"><?= esc((string) count($planAudits)) ?> <?= esc(lang('V3Platform.planEvents')) ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ops-hero-side-card p-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.20em] cp-accent"><?= esc(lang('V3Platform.healthReview')) ?></p>
                            <h2 class="mt-1 text-xl font-black text-white"><?= $attentionCount === 0 ? 'All clear' : 'Needs review' ?></h2>
                            <p class="mt-1 text-xs font-bold cp-accent"><?= esc(lang('V3Platform.currentRuntimeChecks')) ?></p>
                        </div>
                        <span class="cp-radius cp-surface p-2.5 text-white ring-1 ring-white/24">
                            <i class="fas <?= $attentionCount === 0 ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                        </span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2.5">
                        <div class="ops-health-metric ops-health-metric-ok">
                            <p class="ops-health-metric-value"><?= esc((string) $healthyCount) ?></p>
                            <p class="ops-health-metric-label"><?= esc(lang('V3Platform.checksPassing')) ?></p>
                        </div>
                        <div class="ops-health-metric ops-health-metric-warn">
                            <p class="ops-health-metric-value"><?= esc((string) $attentionCount) ?></p>
                            <p class="ops-health-metric-label"><?= esc(lang('V3Platform.needAttention')) ?></p>
                        </div>
                    </div>
                    <div class="mt-2.5 grid gap-2">
                        <a href="<?= base_url('controlplane/settings') ?>" class="inline-flex items-center justify-center gap-2 cp-radius border cp-border cp-surface px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-white/22">
                            <i class="fas fa-sliders"></i>
                            <span><?= esc(lang('V3Platform.globalSettings')) ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="ops-workspace-section space-y-4 p-3 sm:p-4">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <nav class="overflow-x-auto rounded-[1.35rem] cp-surface p-2 ring-1 ring-white/80">
                    <div class="flex min-w-max gap-2">
                        <button type="button" onclick="showOpsTab('health')" id="ops-btn-health" class="ops-tab-button">
                            <i class="fas fa-heartbeat"></i>
                            <span><?= esc(lang('V3Platform.systemHealth')) ?></span>
                        </button>
                        <button type="button" onclick="showOpsTab('audit')" id="ops-btn-audit" class="ops-tab-button">
                            <i class="fas fa-history"></i>
                            <span><?= esc(lang('V3Platform.planAuditTrail')) ?></span>
                        </button>
                    </div>
                </nav>

                <div class="grid gap-2 sm:grid-cols-2">
                    <a href="<?= base_url('controlplane/settings') ?>" class="ops-link-row">
                        <span class="flex items-center gap-3">
                            <span class="ops-link-icon"><i class="fas fa-sliders"></i></span>
                            <span>
                                <span class="block text-sm font-black text-slate-950"><?= esc(lang('V3Platform.globalSettings')) ?></span>
                                <span class="block text-xs font-bold text-slate-500"><?= esc(lang('V3Platform.platformConfiguration')) ?></span>
                            </span>
                        </span>
                    </a>
                    <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="ops-link-row">
                        <span class="flex items-center gap-3">
                            <span class="ops-link-icon"><i class="fab fa-whatsapp"></i></span>
                            <span>
                                <span class="block text-sm font-black text-slate-950"><?= esc(lang('V3Platform.whatsappPolicies')) ?></span>
                                <span class="block text-xs font-bold text-slate-500"><?= esc(lang('V3Platform.secretsAndTemplates')) ?></span>
                            </span>
                        </span>
                    </a>
                </div>
            </div>

            <div id="ops-tab-health">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(360px,.82fr)]">
                    <section class="ops-section-card ops-section-card-health">
                        <div class="ops-section-header px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="ops-section-title-dot"><i class="fas fa-heartbeat text-sm"></i></span>
                                <div>
                                    <h2 class="text-lg font-black text-slate-950"><?= esc(lang('V3Platform.systemHealth')) ?></h2>
                                    <p class="text-sm font-semibold text-slate-500"><?= esc(lang('V3Platform.runtimeChecksRequiredBeforeOperatorWork')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2">
                            <?php foreach ($healthChecks as $check): ?>
                                <?php $isOk = !empty($check['status']); ?>
                                <div class="cp-radius border <?= $isOk ? 'border-emerald-200 bg-emerald-50/80' : 'border-rose-200 bg-rose-50/80' ?> p-3.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-black text-slate-950"><?= esc($check['label'] ?? '-') ?></p>
                                            <p class="mt-1 text-xs font-bold <?= $isOk ? 'text-emerald-700' : 'text-rose-700' ?>"><?= esc($check['detail'] ?? '-') ?></p>
                                        </div>
                                        <span class="inline-flex h-9 w-9 items-center justify-center cp-radius <?= $isOk ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' ?>">
                                            <i class="fas <?= $isOk ? 'fa-check' : 'fa-triangle-exclamation' ?>"></i>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="ops-section-card ops-section-card-tools">
                        <div class="ops-section-header px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="ops-section-title-dot"><i class="fas fa-compass text-sm"></i></span>
                                <div>
                                    <h2 class="text-lg font-black text-slate-950"><?= esc(lang('V3Platform.operationalTools')) ?></h2>
                                    <p class="text-sm font-semibold text-slate-500"><?= esc(lang('V3Platform.highFrequencyControlPlaneActions')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2.5 p-4">
                            <?php
                            $toolLinks = [
                                ['href' => base_url('controlplane/onboarding/clinic/create'), 'icon' => 'fa-plus', 'tone' => 'from-indigo-600 to-blue-700', 'title' => 'Onboard new clinic', 'copy' => 'Create tenant and admin'],
                                ['href' => base_url('controlplane/clinics'), 'icon' => 'fa-hospital-user', 'tone' => 'from-emerald-600 to-teal-700', 'title' => 'Review clinics', 'copy' => 'Status checks and audits'],
                                ['href' => base_url('controlplane/plan-requests'), 'icon' => 'fa-inbox', 'tone' => 'from-amber-600 to-orange-700', 'title' => 'Process requests', 'copy' => 'Subscription changes'],
                                ['href' => base_url('controlplane/activity-log'), 'icon' => 'fa-history', 'tone' => 'from-blue-600 to-sky-700', 'title' => 'System activity', 'copy' => 'Audit logs and events'],
                                ['href' => base_url('rbac/sync'), 'icon' => 'fa-sync-alt', 'tone' => 'from-slate-700 to-slate-900', 'title' => 'Sync permissions', 'copy' => 'RBAC inventory'],
                            ];
                            ?>
                            <?php foreach ($toolLinks as $tool): ?>
                                <a href="<?= esc($tool['href'], 'attr') ?>" class="ops-link-row group">
                                    <span class="flex items-center gap-3">
                                        <span class="ops-link-icon <?= esc($tool['tone'], 'attr') ?>"><i class="fas <?= esc($tool['icon'], 'attr') ?>"></i></span>
                                        <span>
                                            <span class="block text-sm font-black text-slate-950"><?= esc($tool['title']) ?></span>
                                            <span class="block text-xs font-bold text-slate-500"><?= esc($tool['copy']) ?></span>
                                        </span>
                                    </span>
                                    <i class="fas fa-chevron-right text-xs text-slate-300 transition group-hover:text-blue-600"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <section class="ops-section-card ops-section-card-maintenance mt-4">
                    <div class="ops-section-header px-4 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="ops-section-title-dot"><i class="fas fa-tools text-sm"></i></span>
                            <div>
                                <h2 class="text-lg font-black text-slate-950"><?= esc(lang('V3Platform.maintenance')) ?></h2>
                                <p class="text-sm font-semibold text-slate-500"><?= esc(lang('V3Platform.safeguardedOperationsAndRbacChecks')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2 xl:grid-cols-4">
                        <?php
                        $maintenanceLinks = [
                            ['href' => base_url('rbac/status'), 'icon' => 'fa-heartbeat', 'tone' => 'from-emerald-600 to-teal-700', 'title' => 'RBAC Status'],
                            ['href' => base_url('rbac/init'), 'icon' => 'fa-seedling', 'tone' => 'from-indigo-600 to-violet-700', 'title' => 'Initialize RBAC'],
                            ['href' => base_url('rbac/setup'), 'icon' => 'fa-tools', 'tone' => 'from-amber-600 to-orange-700', 'title' => 'RBAC Setup'],
                            ['href' => base_url('controlplane/settings'), 'icon' => 'fa-sliders-h', 'tone' => 'from-slate-700 to-slate-900', 'title' => 'Global Settings'],
                        ];
                        ?>
                        <?php foreach ($maintenanceLinks as $link): ?>
                            <a href="<?= esc($link['href'], 'attr') ?>" class="ops-link-row group">
                                <span class="flex items-center gap-3">
                                    <span class="ops-link-icon <?= esc($link['tone'], 'attr') ?>"><i class="fas <?= esc($link['icon'], 'attr') ?>"></i></span>
                                    <span class="text-sm font-black text-slate-950"><?= esc($link['title']) ?></span>
                                </span>
                                <i class="fas fa-chevron-right text-xs text-slate-300 transition group-hover:text-blue-600"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <div id="ops-tab-audit" class="hidden">
                <section id="planEnforcementTableView" class="ops-section-card">
                    <div class="ops-section-header px-4 py-3.5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="ops-section-title-dot"><i class="fas fa-shield-halved text-sm"></i></span>
                                <div>
                                    <h2 class="text-lg font-black text-slate-950"><?= esc(lang('V3Platform.planEnforcementViolations')) ?></h2>
                                    <p class="text-sm font-semibold text-slate-500"><?= esc(lang('V3Platform.blockedActionsTriggeredByPlanGuardrails')) ?></p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <label class="relative block">
                                    <i class="fas fa-search text-slate-400 absolute left-3 top-3 text-sm"></i>
                                    <input id="planEnforcementSearch" type="text" placeholder="<?= esc(lang('V3Platform.searchClinicActionReason'), 'attr') ?>"
                                           class="w-full cp-radius border cp-border cp-surface py-2.5 pl-9 pr-3 text-sm font-semibold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15 sm:w-72">
                                </label>
                                <span class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-rose-100 px-3 py-2 text-xs font-black text-rose-700">
                                    <?= count($planAudits) ?> <?= esc(lang('V3Platform.events')) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="planEnforcementTable" class="ops-audit-table w-full min-w-full">
                            <thead>
                                <tr>
                                    <th><?= esc(lang('V3Platform.timestamp')) ?></th>
                                    <th><?= esc(lang('V3Platform.clinic')) ?></th>
                                    <th><?= esc(lang('V3Platform.actionBlocked')) ?></th>
                                    <th><?= esc(lang('V3Platform.reason')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($planAudits)): ?>
                                    <tr>
                                        <td colspan="4" class="!bg-white px-6 py-12 text-center text-slate-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="mb-4 flex h-16 w-16 items-center justify-center cp-radius border border-emerald-100 cp-soft-surface">
                                                    <i class="fas fa-shield-alt text-2xl text-emerald-500"></i>
                                                </div>
                                                <p class="text-sm font-black text-slate-950"><?= esc(lang('V3Platform.allSystemsCompliant')) ?></p>
                                                <p class="mt-1 text-xs font-bold text-slate-500"><?= esc(lang('V3Platform.noPlanEnforcementActionsTriggeredRecently')) ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($planAudits as $audit): ?>
                                        <?php $clinic = $clinicDirectory[$audit['clinic_id']] ?? null; ?>
                                        <tr class="plan-enforcement-row transition-colors hover:bg-blue-50/70">
                                            <td class="whitespace-nowrap font-mono text-xs font-bold text-slate-500">
                                                <?= esc($audit['created_at'] ?? '-') ?>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center cp-radius cp-soft-surface cp-accent">
                                                        <i class="fas fa-hospital text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-black text-slate-950">
                                                            <?= esc($clinic['name'] ?? ('Clinic #' . ($audit['clinic_id'] ?? '-'))) ?>
                                                        </p>
                                                        <p class="text-xs font-bold text-slate-500">
                                                            <?= $clinic ? esc($clinic['slug'] ?? '') : '' ?>
                                                            <?= $clinic ? (' - ID ' . esc((string) ($audit['clinic_id'] ?? ''))) : ('ID ' . esc((string) ($audit['clinic_id'] ?? ''))) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-700">
                                                    <?= esc($audit['action_key'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td class="text-sm font-bold text-rose-600">
                                                <?= esc($audit['reason_code'] ?? '-') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
    function showOpsTab(name, options = {}) {
        ['health', 'audit'].forEach((tab) => {
            document.getElementById('ops-tab-' + tab)?.classList.toggle('hidden', tab !== name);
            const btn = document.getElementById('ops-btn-' + tab);
            if (btn) {
                btn.classList.toggle('is-active', tab === name);
            }
        });
        if (options.updateUrl === false) {
            return;
        }
        const url = new URL(window.location.href);
        url.searchParams.set('tab', name);
        history.pushState(null, '', url.toString());
    }

    (function () {
        const opsTab = new URLSearchParams(window.location.search).get('tab') || 'health';
        showOpsTab(opsTab, { updateUrl: false });

        const searchInput = document.getElementById('planEnforcementSearch');
        const rows = document.querySelectorAll('.plan-enforcement-row');
        if (!searchInput || !rows.length) return;

        const filterRows = () => {
            const query = searchInput.value.trim().toLowerCase();
            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', filterRows);
    })();

    window.addEventListener('popstate', () => {
        const opsTab = new URLSearchParams(window.location.search).get('tab') || 'health';
        showOpsTab(opsTab, { updateUrl: false });
    });
</script>
<?= $this->endSection() ?>
