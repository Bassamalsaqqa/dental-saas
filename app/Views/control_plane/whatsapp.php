<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$stats = $stats ?? [];
$failureRate = (float) ($stats['failure_rate_24h'] ?? 0);
$statusBreakdown = is_array($status_breakdown ?? null) ? $status_breakdown : [];
$recentStream = is_array($recent_stream ?? null) ? $recent_stream : [];
$needsAttention = is_array($needs_attention ?? null) ? $needs_attention : [];
$recentAttempts = is_array($recent_attempts ?? null) ? $recent_attempts : [];
$recentEvents = is_array($recent_events ?? null) ? $recent_events : [];
$recentOptouts = is_array($recent_optouts ?? null) ? $recent_optouts : [];
$operatorGuidance = is_array($operator_guidance ?? null) ? $operator_guidance : [];
$triggerCatalog = is_array($trigger_catalog ?? null) ? $trigger_catalog : [];
$testSendClinics = is_array($test_send_clinics ?? null) ? $test_send_clinics : [];

$initialTab = 'overview';
if (old('send_mode') !== null || old('target_number') !== null || old('clinic_id') !== null) {
    $initialTab = 'send';
} elseif (is_string(session('success')) && str_contains(session('success'), 'WhatsApp test send')) {
    $initialTab = 'activity';
}

$formatCount = static fn ($value): string => number_format((int) ($value ?? 0));
$formatPercent = static fn ($value): string => number_format((float) ($value ?? 0), 1) . '%';
$formatDate = static function ($value): string {
    if (empty($value)) {
        return '-';
    }

    try {
        return (new DateTime((string) $value))->format('Y-m-d H:i');
    } catch (Throwable) {
        return (string) $value;
    }
};

$statusClass = static function (?string $status): string {
    return match (strtolower((string) $status)) {
        'sent', 'delivered', 'success', 'read' => 'v3-bg-paid v3-text-primary v3-border-default',
        'failed', 'error' => 'v3-bg-alert v3-text-danger v3-border-default',
        'queued', 'pending' => 'v3-bg-waiting v3-text-warning v3-border-default',
        default => 'v3-bg-soft v3-text-ink v3-border-default',
    };
};

$tabLabels = [
    'overview' => ['icon' => 'fa-gauge-high', 'label' => lang('V3Platform.overview')],
    'send' => ['icon' => 'fa-paper-plane', 'label' => 'Test send'],
    'activity' => ['icon' => 'fa-wave-square', 'label' => lang('V3Platform.activity')],
    'webhooks' => ['icon' => 'fa-code-branch', 'label' => lang('V3Platform.webhooks')],
    'optouts' => ['icon' => 'fa-user-slash', 'label' => 'Opt-outs'],
    'reference' => ['icon' => 'fa-book-open', 'label' => lang('V3Platform.reference')],
];

$guidanceText = static function ($item): string {
    if (is_array($item)) {
        $title = trim((string) ($item['title'] ?? $item['label'] ?? $item['name'] ?? ''));
        $message = trim((string) ($item['message'] ?? $item['description'] ?? $item['body'] ?? ''));
        return trim($title . ($title !== '' && $message !== '' ? ': ' : '') . $message);
    }

    if (is_object($item)) {
        $vars = get_object_vars($item);
        $title = trim((string) ($vars['title'] ?? $vars['label'] ?? $vars['name'] ?? ''));
        $message = trim((string) ($vars['message'] ?? $vars['description'] ?? $vars['body'] ?? ''));
        return trim($title . ($title !== '' && $message !== '' ? ': ' : '') . $message);
    }

    return trim((string) $item);
};
?>

<style>
    .wa-pane.hidden { display: none !important; }
    .wa-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        border-radius: 9999px;
        padding: .75rem 1rem;
        font-size: .82rem;
        font-weight: 800;
        color: var(--denta-muted);
        background: rgba(255, 255, 255, .82);
        border: 1px solid rgba(226, 232, 240, .96);
        box-shadow:none;
        transition: all .18s ease;
        white-space: nowrap;
    }
    .wa-tab:hover {
        color: var(--denta-forest);
        transform: translateY(-1px);
        box-shadow:none;
    }
    .wa-tab.is-active {
        color:var(--denta-ivory-input);
        background:var(--denta-alabaster);
        border-color: rgba(255, 255, 255, .55);
        box-shadow:none;
    }
    .wa-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 9999px;
        padding: .35rem .75rem;
        font-size: .72rem;
        font-weight: 800;
        line-height: 1;
        border: 1px solid transparent;
    }
    .wa-dot {
        display: inline-block;
        width: .5rem;
        height: .5rem;
        border-radius: 9999px;
    }
    .wa-card {
        background:var(--denta-alabaster);
        border: 1px solid rgba(255, 255, 255, .16);
        box-shadow:none;
    }
    .wa-panel {
        background:var(--denta-alabaster);
        border: 1px solid var(--denta-taupe-border);
        box-shadow:none;
    }
    .wa-panel-emerald {
        background:var(--denta-alabaster);
    }
    .wa-panel-sky {
        background:var(--denta-alabaster);
    }
    .wa-panel-amber {
        background:var(--denta-alabaster);
    }
    .wa-field {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid var(--denta-taupe-border);
        background:var(--denta-alabaster);
        padding: .8rem 1rem;
        font-size: .875rem;
        font-weight: 600;
        color: var(--denta-forest);
        outline: none;
        transition: all .18s ease;
    }
    .wa-field:focus {
        border-color: #0ea5e9;
        box-shadow:none;
    }
    .wa-mini-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .wa-mini-table th {
        padding: .85rem 1rem;
        font-size: .68rem;
        font-weight: 800;
        color: var(--denta-muted);
        text-transform: uppercase;
        letter-spacing: .08em;
        background: #f0f9ff;
        border-bottom: 1px solid var(--denta-taupe-border);
        text-align: start;
    }
    .wa-mini-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--denta-parchment);
        vertical-align: top;
    }
    .wa-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .75rem;
        word-break: break-all;
    }
    .copy-toast {
        animation: wa-slide-up .25s ease-out;
    }
    @keyframes wa-slide-up {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Match the mature profile workspace style used on /users/{id}. */
    .wa-page-shell {
        background: rgba(248, 250, 252, 0.82);
    }

    .wa-card {
        position: relative;
        isolation: isolate;
        border-radius: 28px !important;
        overflow: hidden;
        color:var(--denta-ivory-input);
        background:var(--denta-alabaster);
        border: 1px solid rgba(203, 213, 225, 0.55);
        box-shadow:none;
    }

    .wa-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:var(--denta-alabaster);
        z-index: 0;
    }

    [dir="rtl"] .wa-card {
        background:var(--denta-alabaster);
    }

    .wa-card > * {
        position: relative;
        z-index: 1;
    }

    .wa-hero-avatar {
        width: 4.8rem !important;
        height: 4.8rem !important;
        border-radius: 1.75rem !important;
        background:var(--denta-alabaster);
        border: 1px solid rgba(255,255,255,0.22);
        box-shadow:none;
        font-size: 2.15rem !important;
    }

    .wa-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 2.65rem;
        width: 100%;
        border-radius: 999px !important;
        background: rgba(255,255,255,0.12) !important;
        border: 1px solid rgba(255,255,255,0.16) !important;
        color: #eff6ff !important;
        backdrop-filter:none;
        box-shadow:none;
        padding: 0.54rem 0.82rem !important;
        overflow: hidden;
    }

    .wa-hero-chip-icon {
        display: inline-flex;
        width: 1.8rem;
        height: 1.8rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 999px;
        background: rgba(255,255,255,0.12);
        font-size: 0.78rem;
    }

    .wa-hero-chip-text {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 0.08rem;
    }

    .wa-hero-chip-label {
        color: rgba(219,234,254,0.86) !important;
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .wa-hero-chip-value {
        color: #fff !important;
        font-size: 0.9rem;
        font-weight: 900;
        line-height: 1.15;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .wa-hero-action {
        background: rgba(255,255,255,0.14) !important;
        color: #fff !important;
        border: 1px solid rgba(255,255,255,0.18) !important;
        box-shadow:none;
    }

    .wa-hero-action:hover {
        background: rgba(255,255,255,0.20) !important;
    }

    .wa-hero-side-card {
        border-radius: 1.4rem;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.16);
        box-shadow:none;
        backdrop-filter:none;
    }

    .wa-workspace-section {
        position: relative;
        overflow: hidden;
        border-radius: 1.6rem;
        border: 1px solid rgba(255,255,255,0.76);
        background:var(--denta-alabaster);
        box-shadow:none;
    }

    .wa-workspace-section::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:var(--denta-alabaster);
    }

    .wa-workspace-section > * {
        position: relative;
        z-index: 1;
    }

    .wa-tab {
        background: rgba(255,255,255,0.86);
        border-color: rgba(226,232,240,0.86);
    }

    .wa-tab.is-active {
        background:var(--denta-alabaster);
        box-shadow:none;
    }

    .wa-panel {
        position: relative;
        overflow: hidden;
        border-radius: 1.2rem !important;
        border-color: rgba(255,255,255,0.82);
        background:var(--denta-alabaster);
        box-shadow:none;
    }

    .wa-panel-emerald {
        background:var(--denta-alabaster);
    }

    .wa-panel-sky {
        background:var(--denta-alabaster);
    }

    .wa-panel-amber {
        background:var(--denta-alabaster);
    }

    .wa-snapshot-card {
        position: relative;
        overflow: hidden;
        min-height: 7.2rem;
        border-radius: 1.2rem;
        color: #fff !important;
        box-shadow:none;
    }

    .wa-snapshot-card::after {
        content: "";
        position: absolute;
        inset-block-start: -42%;
        inset-inline-end: -10%;
        width: 9.5rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
    }

    .wa-snapshot-card > * {
        position: relative;
        z-index: 1;
    }

    .wa-snapshot-card:nth-child(1) {
        background:var(--denta-alabaster);
    }

    .wa-snapshot-card:nth-child(2) {
        background:var(--denta-alabaster);
    }

    .wa-snapshot-card:nth-child(3) {
        background:var(--denta-alabaster);
    }

    .wa-snapshot-card:nth-child(4) {
        background:var(--denta-alabaster);
    }

    .wa-snapshot-card p {
        color: rgba(239,246,255,0.88) !important;
    }

    .wa-snapshot-card p:nth-child(2) {
        color: #fff !important;
    }
</style>

<div class="v3-platform-workspace wa-page-shell min-h-screen">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="wa-card overflow-hidden rounded-[2rem]">
            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="flex flex-col justify-between gap-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex items-start gap-4">
                            <span class="wa-hero-avatar flex shrink-0 items-center justify-center v3-text-on-primary">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.28em] cp-accent"><?= esc(lang('V3Platform.controlPlane')) ?></p>
                                <h1 class="mt-1 cp-title font-black tracking-tight v3-text-on-primary sm:text-4xl"><?= esc(lang('V3Platform.whatsappOperations')) ?></h1>
                                <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 cp-accent"><?= esc(lang('V3Platform.monitorTenantMessagingHealthSendSafeTestsAndInspectDeliveryActivity')) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-circle-check"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label"><?= esc(lang('V3Platform.delivery')) ?></span>
                                <span class="wa-hero-chip-value"><?= esc($formatCount($stats['attempts_24h_sentish'] ?? 0)) ?> <?= esc(lang('V3Platform.successful')) ?></span>
                            </span>
                        </div>
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-triangle-exclamation"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label"><?= esc(lang('V3Platform.failures')) ?></span>
                                <span class="wa-hero-chip-value"><?= esc($formatPercent($failureRate)) ?> <?= esc(lang('V3Platform.rate')) ?></span>
                            </span>
                        </div>
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-list-check"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label"><?= esc(lang('V3Platform.queue')) ?></span>
                                <span class="wa-hero-chip-value"><?= esc($formatCount($stats['attempts_24h_total'] ?? 0)) ?> <?= esc(lang('V3Platform.attempts')) ?></span>
                            </span>
                        </div>
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-code-branch"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label"><?= esc(lang('V3Platform.webhooks')) ?></span>
                                <span class="wa-hero-chip-value"><?= esc($formatCount($stats['events_24h'] ?? 0)) ?> <?= esc(lang('V3Platform.events')) ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="wa-hero-side-card p-4 v3-text-on-primary">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.20em] v3-text-primary"><?= esc(lang('V3Platform.snapshot')) ?></p>
                            <h2 class="mt-1 text-xl font-black"><?= esc(lang('V3Platform.needsAttention')) ?></h2>
                        </div>
                        <span class="cp-radius cp-surface p-2.5 border v3-border-default">
                            <i class="fas fa-shield-halved"></i>
                        </span>
                    </div>
                    <div class="mt-4 cp-radius cp-surface p-4 border v3-border-default">
                        <p class="cp-title font-black leading-none"><?= esc($formatCount(count($needsAttention))) ?></p>
                        <p class="mt-2 text-sm font-bold cp-accent"><?= esc(lang('V3Platform.itemsRequiringOperatorReview')) ?></p>
                    </div>
                    <div class="mt-3">
                        <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="wa-hero-action inline-flex w-full items-center justify-center gap-2 cp-radius px-4 py-3 text-sm font-extrabold transition v3-btn v3-btn--secondary">
                            <i class="fas fa-sliders"></i>
                            <span><?= esc(lang('V3Platform.messageSettings')) ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <?php if (session('success')): ?>
            <div class="cp-radius border v3-border-default cp-soft-surface px-4 py-3 text-sm font-bold v3-text-primary">
                <i class="fas fa-check-circle me-2"></i><?= esc(session('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="cp-radius border v3-border-default v3-bg-alert px-4 py-3 text-sm font-bold v3-text-danger">
                <i class="fas fa-triangle-exclamation me-2"></i><?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session('errors')): ?>
            <div class="cp-radius border v3-border-default v3-bg-alert px-4 py-3 text-sm font-bold v3-text-danger">
                <ul class="list-disc space-y-1 ps-5">
                    <?php foreach ((array) session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="wa-workspace-section space-y-5 p-4 sm:p-5">
        <nav class="overflow-x-auto rounded-[1.35rem] cp-surface p-2 border v3-border-default">
            <div class="flex min-w-max gap-2">
                <?php foreach ($tabLabels as $tabKey => $tabMeta): ?>
                    <button
                        type="button"
                        id="tab-<?= esc($tabKey, 'attr') ?>"
                        class="wa-tab <?= $initialTab === $tabKey ? 'is-active' : '' ?> v3-btn v3-btn--secondary"
                        onclick="showWATab('<?= esc($tabKey, 'attr') ?>')"
                    >
                        <i class="fas <?= esc($tabMeta['icon'], 'attr') ?>"></i>
                        <span><?= esc($tabMeta['label']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </nav>

        <section id="pane-overview" class="wa-pane <?= $initialTab === 'overview' ? '' : 'hidden' ?>">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(360px,.75fr)]">
                <div class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <?php
                        $kpis = [
                            ['label' => '24h attempts', 'value' => $stats['attempts_24h_total'] ?? 0, 'icon' => 'fa-paper-plane', 'class' => 'wa-panel-emerald', 'accent' => 'v3-text-primary'],
                            ['label' => 'Sent / delivered', 'value' => $stats['attempts_24h_sentish'] ?? 0, 'icon' => 'fa-circle-check', 'class' => 'wa-panel-sky', 'accent' => 'v3-text-primary'],
                            ['label' => 'Failed attempts', 'value' => $stats['attempts_24h_failed'] ?? 0, 'icon' => 'fa-circle-xmark', 'class' => 'wa-panel-amber', 'accent' => 'v3-text-danger'],
                            ['label' => 'Read receipts', 'value' => $stats['attempts_24h_read'] ?? 0, 'icon' => 'fa-eye', 'class' => 'wa-panel-sky', 'accent' => 'v3-text-primary'],
                            ['label' => 'Webhook events', 'value' => $stats['events_24h'] ?? 0, 'icon' => 'fa-code-branch', 'class' => 'wa-panel-amber', 'accent' => 'v3-text-warning'],
                            ['label' => 'Opt-outs', 'value' => $stats['optouts_24h'] ?? 0, 'icon' => 'fa-user-slash', 'class' => 'wa-panel-emerald', 'accent' => 'v3-text-primary'],
                        ];
                        ?>
                        <?php foreach ($kpis as $kpi): ?>
                            <article class="wa-snapshot-card rounded-[1.45rem] p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] v3-text-muted"><?= esc($kpi['label']) ?></p>
                                        <p class="mt-3 cp-title font-black v3-text-ink"><?= esc($formatCount($kpi['value'])) ?></p>
                                    </div>
                                    <span class="flex h-11 w-11 items-center justify-center cp-radius cp-surface <?= esc($kpi['accent'], 'attr') ?> border v3-border-default">
                                        <i class="fas <?= esc($kpi['icon'], 'attr') ?>"></i>
                                    </span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="wa-panel rounded-[1.6rem] overflow-hidden">
                        <div class="flex flex-col gap-3 border-b cp-border cp-surface p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.recentMessageStream')) ?></h2>
                                <p class="text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.latestOutboundAndWebhookActivityAcrossTenants')) ?></p>
                            </div>
                            <a href="#activity" onclick="showWATab('activity')" class="inline-flex items-center justify-center gap-2 cp-radius v3-bg-forest px-4 py-2.5 text-sm font-extrabold v3-text-on-primary transition v3-bg-forest v3-btn v3-btn--secondary">
                                <i class="fas fa-wave-square"></i>
                                <span><?= esc(lang('V3Platform.openActivity')) ?></span>
                            </a>
                        </div>
                        <?php if (empty($recentStream)): ?>
                            <div class="cp-panel-padding text-center">
                                <i class="fas fa-inbox cp-title v3-text-muted"></i>
                                <p class="mt-3 text-sm font-bold v3-text-muted"><?= esc(lang('V3Platform.noRecentWhatsappActivityYet')) ?></p>
                            </div>
                        <?php else: ?>
                            <div class="divide-y v3-border-default">
                                <?php foreach (array_slice($recentStream, 0, 8) as $item): ?>
                                    <?php
                                    $type = 'attempt';
                                    $content = is_array($item['content'] ?? null) ? $item['content'] : [];
                                    $recipient = is_array($item['recipient'] ?? null) ? $item['recipient'] : [];
                                    $statusMeta = is_array($item['status_meta'] ?? null) ? $item['status_meta'] : [];
                                    $streamTitle = (string) ($content['title'] ?? 'WhatsApp send attempt');
                                    $streamSummary = trim((string) ($recipient['label'] ?? '') . ' · ' . (string) ($content['preview'] ?? ''));
                                    $streamStatus = (string) ($statusMeta['label'] ?? $item['effective_status'] ?? 'pending');
                                    ?>
                                    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center cp-radius <?= $type === 'attempt' ? 'v3-bg-paid v3-text-primary' : 'v3-bg-scheduled v3-text-primary' ?>">
                                                <i class="fas <?= $type === 'attempt' ? 'fa-paper-plane' : 'fa-code-branch' ?>"></i>
                                            </span>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="font-black v3-text-ink"><?= esc($streamTitle) ?></p>
                                                    <span class="wa-chip <?= esc($statusClass($item['effective_status'] ?? null), 'attr') ?> border"><?= esc($streamStatus) ?></span>
                                                </div>
                                                <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc($streamSummary !== '' ? $streamSummary : '-') ?></p>
                                            </div>
                                        </div>
                                        <p class="text-xs font-bold v3-text-muted sm:text-end"><?= esc($item['created_label'] ?? $formatDate($item['created_at'] ?? null)) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="wa-panel wa-panel-amber rounded-[1.6rem] p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.needsAttention')) ?></h2>
                                <p class="text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.failuresStalledQueueOrIntegrationWarnings')) ?></p>
                            </div>
                            <span class="flex h-11 w-11 items-center justify-center cp-radius cp-surface v3-text-warning">
                                <i class="fas fa-bell"></i>
                            </span>
                        </div>
                        <div class="mt-4 space-y-3">
                            <?php if (empty($needsAttention)): ?>
                                <div class="cp-radius cp-surface p-4 text-sm font-bold v3-text-primary border v3-border-default">
                                    <i class="fas fa-check-circle me-2"></i><?= esc(lang('V3Platform.noOperationalAlertsRightNow')) ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($needsAttention as $warning): ?>
                                    <?php
                                    $warningContent = is_array($warning['content'] ?? null) ? $warning['content'] : [];
                                    $warningRecipient = is_array($warning['recipient'] ?? null) ? $warning['recipient'] : [];
                                    $warningFailure = is_array($warning['failure'] ?? null) ? $warning['failure'] : [];
                                    $warningTitle = (string) ($warningContent['title'] ?? 'Message needs attention');
                                    $warningMessage = (string) ($warningFailure['detail'] ?? $warningFailure['title'] ?? $warning['error_message'] ?? $warning['failure_reason'] ?? '');
                                    $warningMeta = trim((string) ($warning['clinic_name'] ?? '') . ' · ' . (string) ($warningRecipient['label'] ?? ''));
                                    ?>
                                    <div class="cp-radius cp-surface p-4 border v3-border-default">
                                        <p class="font-black v3-text-ink"><?= esc($warningTitle) ?></p>
                                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.08em] v3-text-warning"><?= esc($warningMeta !== '' ? $warningMeta : 'WhatsApp send attempt') ?></p>
                                        <p class="mt-2 text-sm font-semibold v3-text-muted"><?= esc($warningMessage !== '' ? $warningMessage : 'Review the latest provider response for this message.') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="wa-panel rounded-[1.6rem] p-5">
                        <h2 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.deliveryMix')) ?></h2>
                        <div class="mt-4 space-y-3">
                            <?php if (empty($statusBreakdown)): ?>
                                <p class="cp-radius cp-surface p-4 text-sm font-bold v3-text-muted"><?= esc(lang('V3Platform.noStatusBreakdownAvailableYet')) ?></p>
                            <?php else: ?>
                                <?php foreach ($statusBreakdown as $label => $count): ?>
                                    <?php
                                    $label = (string) $label;
                                    $count = (int) $count;
                                    ?>
                                    <div>
                                        <div class="flex items-center justify-between text-sm font-black">
                                            <span class="v3-text-ink"><?= esc(ucfirst($label)) ?></span>
                                            <span class="v3-text-ink"><?= esc($formatCount($count)) ?></span>
                                        </div>
                                        <div class="mt-2 h-2 rounded-full v3-bg-soft">
                                            <div class="h-2 rounded-full" style="width: <?= esc((string) min(100, max(4, $count)), 'attr') ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section id="pane-send" class="wa-pane <?= $initialTab === 'send' ? '' : 'hidden' ?>">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
                <form method="post" action="<?= base_url('controlplane/whatsapp/send-test') ?>" class="wa-panel rounded-[1.7rem] p-5 lg:p-6">
                    <?= csrf_field() ?>
                    <div class="flex flex-col gap-4 border-b cp-border pb-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] v3-text-primary"><?= esc(lang('V3Platform.safeTestSend')) ?></p>
                            <h2 class="mt-1 text-2xl font-black v3-text-ink"><?= esc(lang('V3Platform.sendAControlledWhatsappTest')) ?></h2>
                            <p class="mt-2 text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.useThisForTenantSetupChecksAndTemplateVerificationProductionWorkflowMessagesStillBelongToTenantOperations')) ?></p>
                        </div>
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center cp-radius cp-soft-surface v3-text-primary">
                            <i class="fas fa-paper-plane"></i>
                        </span>
                    </div>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.clinic')) ?></span>
                            <select name="clinic_id" class="wa-field v3-select" required>
                                <option value=""><?= esc(lang('V3Platform.selectClinic2')) ?></option>
                                <?php foreach ($testSendClinics as $clinic): ?>
                                    <?php $clinicId = (string) ($clinic['id'] ?? ''); ?>
                                    <option value="<?= esc($clinicId, 'attr') ?>" <?= (string) old('clinic_id') === $clinicId ? 'selected' : '' ?>>
                                        <?= esc($clinic['clinic_name'] ?? ('Clinic #' . $clinicId)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.recipientNumber')) ?></span>
                            <input type="text" name="target_number" value="<?= esc((string) old('target_number'), 'attr') ?>" class="wa-field v3-input" placeholder="+970..." required>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.sendMode')) ?></span>
                            <select id="wa-test-send-mode" name="send_mode" class="wa-field v3-select">
                                <?php $sendMode = (string) old('send_mode', 'text'); ?>
                                <option value="text" <?= $sendMode === 'text' ? 'selected' : '' ?>><?= esc(lang('V3Platform.freeText')) ?></option>
                                <option value="template" <?= $sendMode === 'template' ? 'selected' : '' ?>><?= esc(lang('V3Platform.template')) ?></option>
                            </select>
                        </label>

                        <label id="wa-template-source" class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.templateSource')) ?></span>
                            <?php $templateSource = (string) old('template_source', 'explicit'); ?>
                            <select name="template_source" class="wa-field v3-select">
                                <option value="explicit" <?= $templateSource === 'explicit' ? 'selected' : '' ?>><?= esc(lang('V3Platform.explicitTemplate')) ?></option>
                                <option value="fallback" <?= $templateSource === 'fallback' ? 'selected' : '' ?>><?= esc(lang('V3Platform.clinicFallbackTemplate')) ?></option>
                                <option value="trigger_template" <?= $templateSource === 'trigger_template' ? 'selected' : '' ?>><?= esc(lang('V3Platform.triggerCatalog')) ?></option>
                            </select>
                        </label>
                    </div>

                    <div id="wa-test-text-fields" class="mt-5">
                        <label class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.message')) ?></span>
                            <textarea name="message" rows="5" class="wa-field v3-textarea" placeholder="<?= esc(lang('V3Platform.writeAShortOperationalTestMessage'), 'attr') ?>"><?= esc((string) old('message')) ?></textarea>
                        </label>
                    </div>

                    <div id="wa-test-template-fields" class="mt-5 space-y-5">
                        <div class="grid gap-5 lg:grid-cols-2">
                            <label id="wa-template-name-wrap" class="block">
                                <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.templateName2')) ?></span>
                                <input type="text" name="template_name" value="<?= esc((string) old('template_name'), 'attr') ?>" class="wa-field v3-input" placeholder="appointment_reminder">
                            </label>

                            <label id="wa-template-language-wrap" class="block">
                                <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.templateLanguage')) ?></span>
                                <input type="text" name="template_language" value="<?= esc((string) old('template_language', 'en_US'), 'attr') ?>" class="wa-field v3-input" placeholder="en_US">
                            </label>
                        </div>

                        <label id="wa-trigger-template-wrap" class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.triggerTemplate')) ?></span>
                            <select id="wa-trigger-key" name="trigger_key" class="wa-field v3-select">
                                <option value=""><?= esc(lang('V3Platform.selectTrigger')) ?></option>
                                <?php foreach ($triggerCatalog as $key => $trigger): ?>
                                    <option value="<?= esc((string) $key, 'attr') ?>" <?= (string) old('trigger_key') === (string) $key ? 'selected' : '' ?>>
                                        <?= esc($trigger['label'] ?? $key) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <div id="wa-trigger-summary" class="hidden cp-radius cp-soft-surface p-4 border v3-border-default">
                            <p id="wa-trigger-summary-title" class="font-black v3-text-ink"></p>
                            <p id="wa-trigger-summary-description" class="mt-1 text-sm font-semibold v3-text-muted"></p>
                            <div id="wa-trigger-summary-vars" class="mt-3 flex flex-wrap gap-2"></div>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.templateComponentsJson')) ?></span>
                            <textarea name="template_components_json" rows="5" class="wa-field wa-code v3-textarea" placeholder='[{"type":"body","parameters":[...]}]'><?= esc((string) old('template_components_json')) ?></textarea>
                        </label>

                        <div class="grid gap-5 lg:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.headerText')) ?></span>
                                <input type="text" name="template_header_text" value="<?= esc((string) old('template_header_text'), 'attr') ?>" class="wa-field v3-input">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.bodyParams')) ?></span>
                                <input type="text" name="template_body_params" value="<?= esc((string) old('template_body_params'), 'attr') ?>" class="wa-field v3-input" placeholder="<?= esc(lang('V3Platform.commaSeparatedValues'), 'attr') ?>">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.buttonType')) ?></span>
                                <input type="text" name="template_button_type" value="<?= esc((string) old('template_button_type'), 'attr') ?>" class="wa-field v3-input" placeholder="url">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.buttonIndex')) ?></span>
                                <input type="text" name="template_button_index" value="<?= esc((string) old('template_button_index'), 'attr') ?>" class="wa-field v3-input" placeholder="0">
                            </label>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.buttonValue')) ?></span>
                            <input type="text" name="template_button_value" value="<?= esc((string) old('template_button_value'), 'attr') ?>" class="wa-field v3-input">
                        </label>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 border-t cp-border pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <a href="<?= base_url('controlplane/whatsapp') ?>" class="inline-flex items-center justify-center cp-radius border cp-border cp-surface px-5 py-3 text-sm font-extrabold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary"><?= esc(lang('V3Platform.reset')) ?></a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 cp-radius px-5 py-3 text-sm font-extrabold v3-text-on-primary transition v3-btn v3-btn--primary">
                            <i class="fas fa-paper-plane"></i>
                            <span><?= esc(lang('V3Platform.sendTestMessage')) ?></span>
                        </button>
                    </div>
                </form>

                <aside class="space-y-5">
                    <div class="wa-panel wa-panel-emerald rounded-[1.6rem] p-5">
                        <h3 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.operatorRules')) ?></h3>
                        <div class="mt-4 space-y-3 text-sm font-semibold v3-text-ink">
                            <p><i class="fas fa-check-circle me-2 v3-text-primary"></i><?= esc(lang('V3Platform.useTestSendsForSetupAndHealthChecksOnly')) ?></p>
                            <p><i class="fas fa-check-circle me-2 v3-text-primary"></i><?= esc(lang('V3Platform.templatesShouldBeManagedFromSettings')) ?></p>
                            <p><i class="fas fa-check-circle me-2 v3-text-primary"></i><?= esc(lang('V3Platform.optOutsAndProviderFailuresStayVisibleInThisWorkspace')) ?></p>
                        </div>
                    </div>
                    <div class="wa-panel rounded-[1.6rem] p-5">
                        <h3 class="text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.templateSettings')) ?></h3>
                        <p class="mt-2 text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.secretRotationPolicyControlsAndTriggerTemplatesLiveInControlPlaneSettings')) ?></p>
                        <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="mt-4 inline-flex w-full items-center justify-center gap-2 cp-radius v3-bg-forest px-4 py-3 text-sm font-extrabold v3-text-on-primary transition v3-bg-forest v3-btn v3-btn--secondary">
                            <i class="fas fa-sliders"></i>
                            <span><?= esc(lang('V3Platform.openSettings')) ?></span>
                        </a>
                    </div>
                </aside>
            </div>
        </section>

        <section id="pane-activity" class="wa-pane <?= $initialTab === 'activity' ? '' : 'hidden' ?>">
            <div class="wa-panel overflow-hidden rounded-[1.7rem]">
                <div class="border-b cp-border cp-surface p-5">
                    <h2 class="text-2xl font-black v3-text-ink"><?= esc(lang('V3Platform.recentSendAttempts')) ?></h2>
                    <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.traceOutboundTestAndWorkflowSendsWithoutLeavingTheControlPlane')) ?></p>
                </div>
                <?php if (empty($recentAttempts)): ?>
                    <div class="cp-panel-padding text-center">
                        <i class="fas fa-paper-plane cp-title v3-text-muted"></i>
                        <p class="mt-3 text-sm font-bold v3-text-muted"><?= esc(lang('V3Platform.noSendAttemptsRecordedYet')) ?></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="wa-mini-table">
                            <thead>
                                <tr>
                                    <th><?= esc(lang('V3Platform.status')) ?></th>
                                    <th><?= esc(lang('V3Platform.tenant')) ?></th>
                                    <th><?= esc(lang('V3Platform.recipient')) ?></th>
                                    <th><?= esc(lang('V3Platform.content')) ?></th>
                                    <th><?= esc(lang('V3Platform.delivery')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAttempts as $attempt): ?>
                                    <?php
                                    $attemptContent = is_array($attempt['content'] ?? null) ? $attempt['content'] : [];
                                    $attemptRecipient = is_array($attempt['recipient'] ?? null) ? $attempt['recipient'] : [];
                                    $attemptDelivery = is_array($attempt['delivery_meta'] ?? null) ? $attempt['delivery_meta'] : [];
                                    $attemptStatusMeta = is_array($attempt['status_meta'] ?? null) ? $attempt['status_meta'] : [];
                                    $attemptStatus = (string) ($attempt['effective_status'] ?? 'pending');
                                    ?>
                                    <tr>
                                        <td><span class="wa-chip <?= esc($statusClass($attemptStatus), 'attr') ?> border"><?= esc($attemptStatusMeta['label'] ?? $attemptStatus) ?></span></td>
                                        <td>
                                            <p class="font-black v3-text-ink"><?= esc($attempt['clinic_name'] ?? 'Unknown clinic') ?></p>
                                            <p class="mt-1 text-xs font-bold v3-text-muted"><?= esc(lang('V3Platform.attempt')) ?><?= esc((string) ($attempt['attempt_id'] ?? '-')) ?></p>
                                        </td>
                                        <td>
                                            <p class="font-bold v3-text-ink"><?= esc($attemptRecipient['label'] ?? '-') ?></p>
                                            <p class="wa-code mt-1 font-bold v3-text-muted"><?= esc($attemptRecipient['secondary'] ?? '') ?></p>
                                        </td>
                                        <td>
                                            <p class="font-black v3-text-ink"><?= esc($attemptContent['title'] ?? '-') ?></p>
                                            <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc($attemptContent['subtitle'] ?? '') ?></p>
                                            <p class="mt-2 max-w-sm text-sm font-semibold v3-text-ink"><?= esc($attemptContent['preview'] ?? '-') ?></p>
                                            <?php
                                            $attemptFailure = is_array($attempt['failure'] ?? null) ? $attempt['failure'] : [];
                                            $attemptFailureText = (string) ($attemptFailure['detail'] ?? $attemptFailure['title'] ?? '');
                                            ?>
                                            <?php if ($attemptFailureText !== ''): ?>
                                                <p class="mt-2 cp-radius v3-bg-alert px-3 py-2 text-xs font-bold v3-text-danger border v3-border-default"><?= esc($attemptFailureText) ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <p class="text-sm font-black v3-text-ink"><?= esc($attempt['created_label'] ?? $formatDate($attempt['created_at'] ?? null)) ?></p>
                                            <p class="mt-1 text-xs font-bold v3-text-muted"><?= esc($attemptDelivery['label'] ?? ('Notification #' . (string) ($attempt['notification_id'] ?? '-'))) ?></p>
                                            <?php if (!empty($attempt['provider_message_id'])): ?>
                                                <p class="wa-code mt-1 text-xs font-bold v3-text-muted"><?= esc($attempt['provider_message_id']) ?></p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="pane-webhooks" class="wa-pane <?= $initialTab === 'webhooks' ? '' : 'hidden' ?>">
            <div class="grid gap-5 lg:grid-cols-2">
                <?php if (empty($recentEvents)): ?>
                    <div class="wa-panel rounded-[1.7rem] cp-panel-padding text-center lg:col-span-2">
                        <i class="fas fa-code-branch cp-title v3-text-muted"></i>
                        <p class="mt-3 text-sm font-bold v3-text-muted"><?= esc(lang('V3Platform.noWebhookEventsRecordedYet')) ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentEvents as $event): ?>
                        <?php
                        $payload = (string) ($event['payload_pretty'] ?? $event['payload_json'] ?? '{}');
                        $eventSummary = is_array($event['summary'] ?? null) ? $event['summary'] : [];
                        $eventTitle = (string) ($eventSummary['title'] ?? $event['event_type'] ?? 'Webhook event');
                        $eventDetail = (string) ($eventSummary['detail'] ?? '');
                        $eventSubject = (string) ($eventSummary['subject'] ?? '');
                        $eventCode = (string) ($eventSummary['code'] ?? '');
                        ?>
                        <article class="wa-panel rounded-[1.7rem] p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="wa-chip cp-soft-surface cp-accent border v3-border-default"><?= esc($event['event_type'] ?? 'webhook') ?></span>
                                    <h3 class="mt-3 text-lg font-black v3-text-ink"><?= esc($eventTitle) ?></h3>
                                    <?php if ($eventDetail !== ''): ?>
                                        <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc($eventDetail) ?></p>
                                    <?php endif; ?>
                                    <?php if ($eventSubject !== '' || $eventCode !== ''): ?>
                                        <p class="mt-2 text-xs font-bold v3-text-muted">
                                            <?php if ($eventSubject !== ''): ?><span class="wa-code"><?= esc($eventSubject) ?></span><?php endif; ?>
                                            <?php if ($eventSubject !== '' && $eventCode !== ''): ?><span class="mx-1">·</span><?php endif; ?>
                                            <?php if ($eventCode !== ''): ?><span><?= esc('Code ' . $eventCode) ?></span><?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="mt-1 text-xs font-bold v3-text-muted"><?= esc($event['processed_label'] ?? $formatDate($event['processed_at'] ?? null)) ?></p>
                                </div>
                                <button type="button" onclick="copyToClipboard(<?= json_encode((string) $payload) ?>)" class="cp-radius cp-surface px-3 py-2 text-xs font-extrabold v3-text-ink border v3-border-default v3-hover-soft v3-btn v3-btn--secondary">
                                    <i class="fas fa-copy me-1"></i><?= esc(lang('V3Platform.copy')) ?>
                                </button>
                            </div>
                            <pre class="mt-4 max-h-72 overflow-auto cp-radius cp-surface p-4 text-xs font-semibold v3-text-ink border v3-border-default"><?= esc((string) $payload) ?></pre>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="pane-optouts" class="wa-pane <?= $initialTab === 'optouts' ? '' : 'hidden' ?>">
            <div class="wa-panel overflow-hidden rounded-[1.7rem]">
                <div class="border-b cp-border cp-surface p-5">
                    <h2 class="text-2xl font-black v3-text-ink"><?= esc(lang('V3Platform.recentOptOuts')) ?></h2>
                    <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.patientsWhoShouldNotReceiveWhatsappWorkflowMessages')) ?></p>
                </div>
                <?php if (empty($recentOptouts)): ?>
                    <div class="cp-panel-padding text-center">
                        <i class="fas fa-user-slash cp-title v3-text-muted"></i>
                        <p class="mt-3 text-sm font-bold v3-text-muted"><?= esc(lang('V3Platform.noOptOutsRecordedYet')) ?></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="wa-mini-table">
                            <thead>
                                <tr>
                                    <th><?= esc(lang('V3Platform.clinic')) ?></th>
                                    <th><?= esc(lang('V3Platform.phone')) ?></th>
                                    <th><?= esc(lang('V3Platform.reason')) ?></th>
                                    <th><?= esc(lang('V3Platform.recorded')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOptouts as $optout): ?>
                                    <tr>
                                        <td class="font-black v3-text-ink"><?= esc($optout['clinic_name'] ?? 'Unknown clinic') ?></td>
                                        <td class="wa-code font-bold v3-text-ink"><?= esc($optout['wa_number'] ?? '-') ?></td>
                                        <td class="text-sm font-semibold v3-text-muted"><?= esc(trim((string) ($optout['bsuid'] ?? '') . ' ' . (string) ($optout['parent_bsuid'] ?? '')) ?: '-') ?></td>
                                        <td class="text-sm font-bold v3-text-muted"><?= esc($optout['created_label'] ?? $formatDate($optout['created_at'] ?? null)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="pane-reference" class="wa-pane <?= $initialTab === 'reference' ? '' : 'hidden' ?>">
            <div class="grid gap-5 lg:grid-cols-3">
                <div class="wa-panel wa-panel-emerald rounded-[1.7rem] p-5">
                    <span class="flex h-12 w-12 items-center justify-center cp-radius cp-soft-surface v3-text-primary"><i class="fas fa-route"></i></span>
                    <h3 class="mt-4 text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.workflowOwnership')) ?></h3>
                    <p class="mt-2 text-sm font-semibold leading-6 v3-text-muted"><?= esc(lang('V3Platform.tenantEventsCreateWorkflowMessagesThisControlPlanePageObservesTestsAndGovernsThemWithoutBecomingATenantMessagingWorkspace')) ?></p>
                </div>
                <div class="wa-panel wa-panel-sky rounded-[1.7rem] p-5">
                    <span class="flex h-12 w-12 items-center justify-center cp-radius cp-soft-surface cp-accent"><i class="fas fa-lock"></i></span>
                    <h3 class="mt-4 text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.safetyModel')) ?></h3>
                    <p class="mt-2 text-sm font-semibold leading-6 v3-text-muted"><?= esc(lang('V3Platform.secretsPolicyTogglesTemplatesAndEmergencyPauseControlsRemainCentralizedInSettingsWithExistingGuardrails')) ?></p>
                </div>
                <div class="wa-panel wa-panel-amber rounded-[1.7rem] p-5">
                    <span class="flex h-12 w-12 items-center justify-center cp-radius v3-bg-waiting v3-text-warning"><i class="fas fa-list-check"></i></span>
                    <h3 class="mt-4 text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.operatorChecklist')) ?></h3>
                    <div class="mt-3 space-y-2 text-sm font-semibold v3-text-ink">
                        <?php if (empty($operatorGuidance)): ?>
                            <p><?= esc(lang('V3Platform.monitorFailuresReviewOptOutsAndKeepTemplatesAlignedWithClinicWorkflows')) ?></p>
                        <?php else: ?>
                            <?php foreach ($operatorGuidance as $item): ?>
                                <?php $guidanceLine = $guidanceText($item); ?>
                                <?php if ($guidanceLine !== ''): ?>
                                    <p><i class="fas fa-check me-2 v3-text-primary"></i><?= esc($guidanceLine) ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
</div>

<script>
    const waTriggerCatalog = <?= json_encode($triggerCatalog, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    function showWATab(tabName) {
        document.querySelectorAll('.wa-pane').forEach((pane) => pane.classList.add('hidden'));
        document.querySelectorAll('.wa-tab').forEach((tab) => tab.classList.remove('is-active'));

        const pane = document.getElementById(`pane-${tabName}`);
        const tab = document.getElementById(`tab-${tabName}`);

        if (pane) {
            pane.classList.remove('hidden');
        }

        if (tab) {
            tab.classList.add('is-active');
        }

        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url.toString());
        }
    }

    function copyToClipboard(text) {
        if (!navigator.clipboard) {
            showToast('Copy is unavailable in this browser');
            return;
        }

        navigator.clipboard.writeText(text)
            .then(() => showToast('Copied raw payload'))
            .catch(() => showToast('Copy failed'));
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'copy-toast fixed bottom-5 right-5 z-[9999] v3-radius v3-bg-panel px-4 py-3 text-sm font-extrabold v3-text-ink v3-elevation-none border v3-border-default';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2200);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const url = new URL(window.location.href);
        const requestedTab = url.searchParams.get('tab');
        if (requestedTab && document.getElementById(`pane-${requestedTab}`)) {
            showWATab(requestedTab);
        }

        const sendMode = document.getElementById('wa-test-send-mode');
        const textFields = document.getElementById('wa-test-text-fields');
        const templateFields = document.getElementById('wa-test-template-fields');
        const templateSourceWrap = document.getElementById('wa-template-source');
        const templateSource = templateSourceWrap ? templateSourceWrap.querySelector('select') : null;
        const manualNameWrap = document.getElementById('wa-template-name-wrap');
        const manualLanguageWrap = document.getElementById('wa-template-language-wrap');
        const triggerWrap = document.getElementById('wa-trigger-template-wrap');
        const triggerSelect = document.getElementById('wa-trigger-key');
        const triggerSummary = document.getElementById('wa-trigger-summary');
        const triggerSummaryTitle = document.getElementById('wa-trigger-summary-title');
        const triggerSummaryDescription = document.getElementById('wa-trigger-summary-description');
        const triggerSummaryVars = document.getElementById('wa-trigger-summary-vars');

        const syncSendMode = () => {
            const mode = sendMode ? sendMode.value : 'text';
            const isTemplate = mode === 'template';
            if (textFields) textFields.classList.toggle('hidden', isTemplate);
            if (templateFields) templateFields.classList.toggle('hidden', !isTemplate);
            if (templateSourceWrap) templateSourceWrap.classList.toggle('hidden', !isTemplate);
            syncTemplateSource();
        };

        const syncTriggerSummary = () => {
            if (!triggerSelect || !triggerSummary || !triggerSummaryTitle || !triggerSummaryDescription || !triggerSummaryVars) {
                return;
            }

            const trigger = waTriggerCatalog[triggerSelect.value] || null;
            triggerSummary.classList.toggle('hidden', !trigger);
            triggerSummaryVars.replaceChildren();

            if (!trigger) {
                triggerSummaryTitle.textContent = '';
                triggerSummaryDescription.textContent = '';
                return;
            }

            triggerSummaryTitle.textContent = trigger.label || triggerSelect.value;
            triggerSummaryDescription.textContent = trigger.description || '';

            const variables = Array.isArray(trigger.required_variables) ? trigger.required_variables : [];
            variables.forEach((variable) => {
                const badge = document.createElement('span');
                badge.className = 'wa-chip v3-bg-panel v3-text-primary border v3-border-default';
                badge.textContent = variable;
                triggerSummaryVars.appendChild(badge);
            });
        };

        function syncTemplateSource() {
            const source = templateSource ? templateSource.value : 'explicit';
            const useTrigger = source === 'trigger_template';
            if (manualNameWrap) manualNameWrap.classList.toggle('hidden', useTrigger);
            if (manualLanguageWrap) manualLanguageWrap.classList.toggle('hidden', useTrigger);
            if (triggerWrap) triggerWrap.classList.toggle('hidden', !useTrigger);
            syncTriggerSummary();
        }

        if (sendMode) sendMode.addEventListener('change', syncSendMode);
        if (templateSource) templateSource.addEventListener('change', syncTemplateSource);
        if (triggerSelect) triggerSelect.addEventListener('change', syncTriggerSummary);
        syncSendMode();
    });
</script>

<?= $this->endSection() ?>
