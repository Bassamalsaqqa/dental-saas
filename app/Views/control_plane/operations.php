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
        color:var(--denta-ivory-input);
        background:var(--denta-alabaster);
        border: 1px solid rgba(203,213,225,0.55);
        box-shadow:none;
    }

    .ops-hero-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
        background:var(--denta-alabaster);
    }

    [dir="rtl"] .ops-hero-card {
        background:var(--denta-alabaster);
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
        background:var(--denta-alabaster);
        border: 1px solid rgba(255,255,255,0.22);
        box-shadow:none;
        color:var(--denta-ivory-input);
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
        backdrop-filter:none;
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
        color:var(--denta-ivory-input);
        font-size: 0.9rem;
        font-weight: 900;
        line-height: 1.15;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ops-hero-side-card {
        border-radius: 1.3rem;
        border: 1px solid rgba(255,255,255,0.24);
        background:var(--denta-alabaster);
        box-shadow:none;
        backdrop-filter:none;
    }

    .ops-health-metric {
        border-radius: 1rem;
        border: 1px solid rgba(255,255,255,0.22);
        padding: 0.9rem;
        box-shadow:none;
    }

    .ops-health-metric-ok {
        background:var(--denta-alabaster);
    }

    .ops-health-metric-warn {
        background:var(--denta-alabaster);
    }

    .ops-health-metric-value {
        color:var(--denta-ivory-input);
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
        background:var(--denta-alabaster);
        box-shadow:none;
    }

    .ops-workspace-section::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:var(--denta-alabaster);
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
        color: var(--denta-muted);
        font-size: 0.82rem;
        font-weight: 900;
        white-space: nowrap;
        transition: all 0.18s ease;
        box-shadow:none;
    }

    .ops-tab-button:hover {
        color: var(--denta-forest);
        transform: translateY(-1px);
    }

    .ops-tab-button.is-active {
        color:var(--denta-ivory-input);
        border-color: rgba(255,255,255,0.55);
        background:var(--denta-alabaster);
        box-shadow:none;
    }

    .ops-section-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.08rem;
        border: 1px solid rgba(255,255,255,0.82);
        background:var(--denta-alabaster);
        box-shadow:none;
    }

    .ops-section-card-health {
        background:var(--denta-alabaster);
    }

    .ops-section-card-tools {
        background:var(--denta-alabaster);
    }

    .ops-section-card-maintenance {
        background:var(--denta-alabaster);
    }

    .ops-section-header {
        border-bottom: 1px solid rgba(203,213,225,0.72);
        background:var(--denta-alabaster);
    }

    .ops-section-title-dot {
        display: inline-flex;
        width: 2.25rem;
        height: 2.25rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.85rem;
        background:var(--denta-alabaster);
        color:var(--denta-ivory-input);
        box-shadow:none;
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
        box-shadow:none;
        transition: all 0.18s ease;
    }

    .ops-link-row:hover {
        transform: translateY(-1px);
        box-shadow:none;
    }

    .ops-link-icon {
        display: inline-flex;
        width: 2.5rem;
        height: 2.5rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 0.9rem;
        color:var(--denta-ivory-input);
        box-shadow:none;
    }

    .ops-audit-table th {
        padding: 0.9rem 1rem;
        color: rgba(239,246,255,0.92);
        background:var(--denta-alabaster);
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
                            <h1 class="mt-1 cp-title font-black tracking-tight v3-text-on-primary"><?= esc(lang('V3Platform.operationsConsole')) ?></h1>
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

                <div class="ops-hero-side-card p-4 v3-text-on-primary">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.20em] cp-accent"><?= esc(lang('V3Platform.healthReview')) ?></p>
                            <h2 class="mt-1 text-xl font-black v3-text-on-primary"><?= $attentionCount === 0 ? 'All clear' : 'Needs review' ?></h2>
                            <p class="mt-1 text-xs font-bold cp-accent"><?= esc(lang('V3Platform.currentRuntimeChecks')) ?></p>
                        </div>
                        <span class="cp-radius cp-surface p-2.5 v3-text-on-primary border v3-border-default">
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
                        <a href="<?= base_url('controlplane/settings') ?>" class="inline-flex items-center justify-center gap-2 cp-radius border cp-border cp-surface px-4 py-2.5 text-sm font-extrabold v3-text-on-primary transition v3-hover-soft v3-btn v3-btn--secondary">
                            <i class="fas fa-sliders"></i>
                            <span><?= esc(lang('V3Platform.globalSettings')) ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="ops-workspace-section space-y-4 p-3 sm:p-4">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <nav class="overflow-x-auto rounded-[1.35rem] cp-surface p-2 border v3-border-default">
                    <div class="flex min-w-max gap-2">
                        <button type="button" onclick="showOpsTab('health')" id="ops-btn-health" class="ops-tab-button v3-btn v3-btn--secondary">
                            <i class="fas fa-heartbeat"></i>
                            <span><?= esc(lang('V3Platform.systemHealth')) ?></span>
                        </button>
                        <button type="button" onclick="showOpsTab('audit')" id="ops-btn-audit" class="ops-tab-button v3-btn v3-btn--secondary">
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
                                <span class="block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.globalSettings')) ?></span>
                                <span class="block text-xs font-bold v3-text-muted"><?= esc(lang('V3Platform.platformConfiguration')) ?></span>
                            </span>
                        </span>
                    </a>
                    <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="ops-link-row">
                        <span class="flex items-center gap-3">
                            <span class="ops-link-icon"><i class="fab fa-whatsapp"></i></span>
                            <span>
                                <span class="block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.whatsappPolicies')) ?></span>
                                <span class="block text-xs font-bold v3-text-muted"><?= esc(lang('V3Platform.secretsAndTemplates')) ?></span>
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
                                    <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.systemHealth')) ?></h2>
                                    <p class="text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.runtimeChecksRequiredBeforeOperatorWork')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2">
                            <?php foreach ($healthChecks as $check): ?>
                                <?php $isOk = !empty($check['status']); ?>
                                <div class="cp-radius border <?= $isOk ? 'v3-border-default v3-bg-paid' : 'v3-border-default v3-bg-alert' ?> p-3.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-black v3-text-ink"><?= esc($check['label'] ?? '-') ?></p>
                                            <p class="mt-1 text-xs font-bold <?= $isOk ? 'v3-text-primary' : 'v3-text-danger' ?>"><?= esc($check['detail'] ?? '-') ?></p>
                                        </div>
                                        <span class="inline-flex h-9 w-9 items-center justify-center cp-radius <?= $isOk ? 'v3-bg-primary v3-text-on-primary' : 'v3-bg-danger v3-text-on-primary' ?>">
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
                                    <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.operationalTools')) ?></h2>
                                    <p class="text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.highFrequencyControlPlaneActions')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2.5 p-4">
                            <?php
                            $toolLinks = [
                                ['href' => base_url('controlplane/onboarding/clinic/create'), 'icon' => 'fa-plus', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'Onboard new clinic', 'copy' => 'Create tenant and admin'],
                                ['href' => base_url('controlplane/clinics'), 'icon' => 'fa-hospital-user', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'Review clinics', 'copy' => 'Status checks and audits'],
                                ['href' => base_url('controlplane/plan-requests'), 'icon' => 'fa-inbox', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'Process requests', 'copy' => 'Subscription changes'],
                                ['href' => base_url('controlplane/activity-log'), 'icon' => 'fa-history', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'System activity', 'copy' => 'Audit logs and events'],
                                ['href' => base_url('rbac/sync'), 'icon' => 'fa-sync-alt', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'Sync permissions', 'copy' => 'RBAC inventory'],
                            ];
                            ?>
                            <?php foreach ($toolLinks as $tool): ?>
                                <a href="<?= esc($tool['href'], 'attr') ?>" class="ops-link-row group">
                                    <span class="flex items-center gap-3">
                                        <span class="ops-link-icon <?= esc($tool['tone'], 'attr') ?>"><i class="fas <?= esc($tool['icon'], 'attr') ?>"></i></span>
                                        <span>
                                            <span class="block text-sm font-black v3-text-ink"><?= esc($tool['title']) ?></span>
                                            <span class="block text-xs font-bold v3-text-muted"><?= esc($tool['copy']) ?></span>
                                        </span>
                                    </span>
                                    <i class="fas fa-chevron-right text-xs v3-text-muted transition v3-hover-text-primary"></i>
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
                                <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.maintenance')) ?></h2>
                                <p class="text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.safeguardedOperationsAndRbacChecks')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2 xl:grid-cols-4">
                        <?php
                        $maintenanceLinks = [
                            ['href' => base_url('rbac/status'), 'icon' => 'fa-heartbeat', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'RBAC Status'],
                            ['href' => base_url('rbac/init'), 'icon' => 'fa-seedling', 'tone' => 'v3-bg-panel to-violet-700', 'title' => 'Initialize RBAC'],
                            ['href' => base_url('rbac/setup'), 'icon' => 'fa-tools', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'RBAC Setup'],
                            ['href' => base_url('controlplane/settings'), 'icon' => 'fa-sliders-h', 'tone' => 'v3-bg-panel v3-bg-panel', 'title' => 'Global Settings'],
                        ];
                        ?>
                        <?php foreach ($maintenanceLinks as $link): ?>
                            <a href="<?= esc($link['href'], 'attr') ?>" class="ops-link-row group">
                                <span class="flex items-center gap-3">
                                    <span class="ops-link-icon <?= esc($link['tone'], 'attr') ?>"><i class="fas <?= esc($link['icon'], 'attr') ?>"></i></span>
                                    <span class="text-sm font-black v3-text-ink"><?= esc($link['title']) ?></span>
                                </span>
                                <i class="fas fa-chevron-right text-xs v3-text-muted transition v3-hover-text-primary"></i>
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
                                    <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.planEnforcementViolations')) ?></h2>
                                    <p class="text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.blockedActionsTriggeredByPlanGuardrails')) ?></p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <label class="relative block">
                                    <i class="fas fa-search v3-text-muted absolute left-3 top-3 text-sm"></i>
                                    <input id="planEnforcementSearch" type="text" placeholder="<?= esc(lang('V3Platform.searchClinicActionReason'), 'attr') ?>"
                                           class="w-full cp-radius border cp-border cp-surface py-2.5 pl-9 pr-3 text-sm font-semibold v3-text-ink v3-focus-control focus:outline-none focus:ring-4 v3-focus-control sm:w-72 v3-input">
                                </label>
                                <span class="inline-flex items-center justify-center rounded-full border v3-border-default v3-bg-alert px-3 py-2 text-xs font-black v3-text-danger">
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
                                        <td colspan="4" class="!v3-bg-panel px-6 py-12 text-center v3-text-muted">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="mb-4 flex h-16 w-16 items-center justify-center cp-radius border v3-border-default cp-soft-surface">
                                                    <i class="fas fa-shield-alt text-2xl v3-text-primary"></i>
                                                </div>
                                                <p class="text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.allSystemsCompliant')) ?></p>
                                                <p class="mt-1 text-xs font-bold v3-text-muted"><?= esc(lang('V3Platform.noPlanEnforcementActionsTriggeredRecently')) ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($planAudits as $audit): ?>
                                        <?php $clinic = $clinicDirectory[$audit['clinic_id']] ?? null; ?>
                                        <tr class="plan-enforcement-row transition-colors v3-hover-primary">
                                            <td class="whitespace-nowrap font-mono text-xs font-bold v3-text-muted">
                                                <?= esc($audit['created_at'] ?? '-') ?>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center cp-radius cp-soft-surface cp-accent">
                                                        <i class="fas fa-hospital text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-black v3-text-ink">
                                                            <?= esc($clinic['name'] ?? ('Clinic #' . ($audit['clinic_id'] ?? '-'))) ?>
                                                        </p>
                                                        <p class="text-xs font-bold v3-text-muted">
                                                            <?= $clinic ? esc($clinic['slug'] ?? '') : '' ?>
                                                            <?= $clinic ? (' - ID ' . esc((string) ($audit['clinic_id'] ?? ''))) : ('ID ' . esc((string) ($audit['clinic_id'] ?? ''))) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="inline-flex items-center rounded-full border v3-border-default v3-bg-waiting px-2.5 py-1 text-xs font-black v3-text-warning">
                                                    <?= esc($audit['action_key'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td class="text-sm font-bold v3-text-danger">
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
