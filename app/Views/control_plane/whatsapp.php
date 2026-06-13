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
        'sent', 'delivered', 'success', 'read' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'failed', 'error' => 'bg-rose-100 text-rose-800 ring-rose-200',
        'queued', 'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
};

$tabLabels = [
    'overview' => ['icon' => 'fa-gauge-high', 'label' => 'Overview'],
    'send' => ['icon' => 'fa-paper-plane', 'label' => 'Test send'],
    'activity' => ['icon' => 'fa-wave-square', 'label' => 'Activity'],
    'webhooks' => ['icon' => 'fa-code-branch', 'label' => 'Webhooks'],
    'optouts' => ['icon' => 'fa-user-slash', 'label' => 'Opt-outs'],
    'reference' => ['icon' => 'fa-book-open', 'label' => 'Reference'],
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
        color: #475569;
        background: rgba(255, 255, 255, .82);
        border: 1px solid rgba(226, 232, 240, .96);
        box-shadow: 0 12px 30px -24px rgba(15, 23, 42, .34);
        transition: all .18s ease;
        white-space: nowrap;
    }
    .wa-tab:hover {
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 18px 34px -26px rgba(15, 23, 42, .36);
    }
    .wa-tab.is-active {
        color: #fff;
        background: linear-gradient(135deg, #1559c7 0%, #0f766e 100%);
        border-color: rgba(255, 255, 255, .55);
        box-shadow: 0 18px 36px -18px rgba(15, 89, 199, .48);
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
        background:
            radial-gradient(circle at 10% 14%, rgba(255, 255, 255, .20), transparent 30%),
            radial-gradient(circle at 88% 18%, rgba(125, 211, 252, .20), transparent 28%),
            linear-gradient(135deg, #0f3f90 0%, #1656c1 56%, #0f766e 100%);
        border: 1px solid rgba(255, 255, 255, .16);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, .75);
    }
    .wa-panel {
        background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        box-shadow: 0 14px 34px -30px rgba(15, 23, 42, .32);
    }
    .wa-panel-emerald {
        background:
            radial-gradient(circle at 0% 0%, rgba(16, 185, 129, .30), transparent 34%),
            linear-gradient(135deg, #d1fae5 0%, #ffffff 56%, #ecfdf5 100%);
    }
    .wa-panel-sky {
        background:
            radial-gradient(circle at 100% 0%, rgba(37, 99, 235, .28), transparent 34%),
            linear-gradient(135deg, #dbeafe 0%, #ffffff 56%, #eff6ff 100%);
    }
    .wa-panel-amber {
        background:
            radial-gradient(circle at 0% 0%, rgba(245, 158, 11, .30), transparent 34%),
            linear-gradient(135deg, #fef3c7 0%, #ffffff 56%, #fffbeb 100%);
    }
    .wa-field {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid #cbd5e1;
        background: #fff;
        padding: .8rem 1rem;
        font-size: .875rem;
        font-weight: 600;
        color: #0f172a;
        outline: none;
        transition: all .18s ease;
    }
    .wa-field:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
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
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .08em;
        background: #f0f9ff;
        border-bottom: 1px solid #e2e8f0;
        text-align: start;
    }
    .wa-mini-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
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
        color: #fff;
        background:
            radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.18), transparent 30%),
            radial-gradient(circle at 88% 20%, rgba(125, 211, 252, 0.20), transparent 26%),
            linear-gradient(135deg, #0f3f90 0%, #1656c1 58%, #0f766e 100%);
        border: 1px solid rgba(203, 213, 225, 0.55);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, 0.75);
    }

    .wa-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 4% 84%, rgba(255,255,255,0.10), transparent 28%),
            linear-gradient(120deg, rgba(255,255,255,0.04), rgba(255,255,255,0));
        z-index: 0;
    }

    [dir="rtl"] .wa-card {
        background:
            radial-gradient(circle at 88% 18%, rgba(255, 255, 255, 0.18), transparent 30%),
            radial-gradient(circle at 12% 20%, rgba(125, 211, 252, 0.20), transparent 26%),
            linear-gradient(225deg, #0f3f90 0%, #1656c1 58%, #0f766e 100%);
    }

    .wa-card > * {
        position: relative;
        z-index: 1;
    }

    .wa-hero-avatar {
        width: 4.8rem !important;
        height: 4.8rem !important;
        border-radius: 1.75rem !important;
        background: linear-gradient(145deg, rgba(255,255,255,0.24), rgba(153,246,228,0.18)) !important;
        border: 1px solid rgba(255,255,255,0.22);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.15), 0 22px 40px -24px rgba(15,23,42,0.72) !important;
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
        backdrop-filter: blur(12px);
        box-shadow: none !important;
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
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 18px 34px -28px rgba(15,23,42,0.55) !important;
    }

    .wa-hero-action:hover {
        background: rgba(255,255,255,0.20) !important;
    }

    .wa-hero-side-card {
        border-radius: 1.4rem;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.16);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 18px 34px -28px rgba(15,23,42,0.55);
        backdrop-filter: blur(12px);
    }

    .wa-workspace-section {
        position: relative;
        overflow: hidden;
        border-radius: 1.6rem;
        border: 1px solid rgba(255,255,255,0.76);
        background:
            radial-gradient(circle at 0% 0%, rgba(37,99,235,0.11), transparent 28%),
            radial-gradient(circle at 100% 18%, rgba(124,58,237,0.12), transparent 30%),
            linear-gradient(135deg, rgba(248,250,252,0.98), rgba(255,255,255,0.96));
        box-shadow: 0 24px 58px -42px rgba(15,23,42,0.42), 0 1px 0 rgba(255,255,255,0.92) inset;
    }

    .wa-workspace-section::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 9% 0%, rgba(37,99,235,0.14), transparent 29%),
            radial-gradient(circle at 95% 10%, rgba(5,150,105,0.12), transparent 30%);
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
        background: linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a);
        box-shadow: 0 18px 34px -28px rgba(37,99,235,0.72);
    }

    .wa-panel {
        position: relative;
        overflow: hidden;
        border-radius: 1.2rem !important;
        border-color: rgba(255,255,255,0.82);
        background:
            radial-gradient(circle at 8% 0%, rgba(37,99,235,0.15), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(239,246,255,0.92) 46%, rgba(255,255,255,0.98));
        box-shadow: 0 20px 48px -38px rgba(15,23,42,0.48), 0 1px 0 rgba(255,255,255,0.92) inset;
    }

    .wa-panel-emerald {
        background:
            radial-gradient(circle at 8% 0%, rgba(5,150,105,0.18), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(209,250,229,0.92) 46%, rgba(255,255,255,0.98));
    }

    .wa-panel-sky {
        background:
            radial-gradient(circle at 8% 0%, rgba(37,99,235,0.18), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(219,234,254,0.92) 46%, rgba(255,255,255,0.98));
    }

    .wa-panel-amber {
        background:
            radial-gradient(circle at 8% 0%, rgba(217,119,6,0.20), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(254,243,199,0.92) 46%, rgba(255,255,255,0.98));
    }

    .wa-snapshot-card {
        position: relative;
        overflow: hidden;
        min-height: 7.2rem;
        border-radius: 1.2rem;
        color: #fff !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -28px rgba(15,23,42,0.55);
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
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a);
    }

    .wa-snapshot-card:nth-child(2) {
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
            linear-gradient(135deg, #7c3aed, #6d28d9 56%, #4c1d95);
    }

    .wa-snapshot-card:nth-child(3) {
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #047857, #0f766e 56%, #064e3b);
    }

    .wa-snapshot-card:nth-child(4) {
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
            linear-gradient(135deg, #d97706, #b45309 58%, #92400e);
    }

    .wa-snapshot-card p {
        color: rgba(239,246,255,0.88) !important;
    }

    .wa-snapshot-card p:nth-child(2) {
        color: #fff !important;
    }
</style>

<div class="wa-page-shell min-h-screen">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="wa-card overflow-hidden rounded-[2rem]">
            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="flex flex-col justify-between gap-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex items-start gap-4">
                            <span class="wa-hero-avatar flex shrink-0 items-center justify-center text-white">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-blue-100">Control Plane</p>
                                <h1 class="mt-1 text-3xl font-black tracking-tight text-white sm:text-4xl">WhatsApp Operations</h1>
                                <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-blue-50/90">Monitor tenant messaging health, send safe tests, and inspect delivery activity.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-circle-check"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label">Delivery</span>
                                <span class="wa-hero-chip-value"><?= esc($formatCount($stats['attempts_24h_sentish'] ?? 0)) ?> successful</span>
                            </span>
                        </div>
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-triangle-exclamation"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label">Failures</span>
                                <span class="wa-hero-chip-value"><?= esc($formatPercent($failureRate)) ?> rate</span>
                            </span>
                        </div>
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-list-check"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label">Queue</span>
                                <span class="wa-hero-chip-value"><?= esc($formatCount($stats['attempts_24h_total'] ?? 0)) ?> attempts</span>
                            </span>
                        </div>
                        <div class="wa-hero-chip">
                            <span class="wa-hero-chip-icon"><i class="fas fa-code-branch"></i></span>
                            <span class="wa-hero-chip-text">
                                <span class="wa-hero-chip-label">Webhooks</span>
                                <span class="wa-hero-chip-value"><?= esc($formatCount($stats['events_24h'] ?? 0)) ?> events</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="wa-hero-side-card p-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.20em] text-emerald-100">Snapshot</p>
                            <h2 class="mt-1 text-xl font-black">Needs attention</h2>
                        </div>
                        <span class="rounded-xl bg-white/16 p-2.5 ring-1 ring-white/20">
                            <i class="fas fa-shield-halved"></i>
                        </span>
                    </div>
                    <div class="mt-4 rounded-2xl bg-white/13 p-4 ring-1 ring-white/16">
                        <p class="text-4xl font-black leading-none"><?= esc($formatCount(count($needsAttention))) ?></p>
                        <p class="mt-2 text-sm font-bold text-blue-50/85">items requiring operator review</p>
                    </div>
                    <div class="mt-3">
                        <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="wa-hero-action inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold transition hover:-translate-y-0.5">
                            <i class="fas fa-sliders"></i>
                            <span>Message settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <?php if (session('success')): ?>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-sm">
                <i class="fas fa-check-circle me-2"></i><?= esc(session('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800 shadow-sm">
                <i class="fas fa-triangle-exclamation me-2"></i><?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session('errors')): ?>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800 shadow-sm">
                <ul class="list-disc space-y-1 ps-5">
                    <?php foreach ((array) session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="wa-workspace-section space-y-5 p-4 sm:p-5">
        <nav class="overflow-x-auto rounded-[1.35rem] bg-white/80 p-2 shadow-sm ring-1 ring-white/80 backdrop-blur">
            <div class="flex min-w-max gap-2">
                <?php foreach ($tabLabels as $tabKey => $tabMeta): ?>
                    <button
                        type="button"
                        id="tab-<?= esc($tabKey, 'attr') ?>"
                        class="wa-tab <?= $initialTab === $tabKey ? 'is-active' : '' ?>"
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
                            ['label' => '24h attempts', 'value' => $stats['attempts_24h_total'] ?? 0, 'icon' => 'fa-paper-plane', 'class' => 'wa-panel-emerald', 'accent' => 'text-emerald-700'],
                            ['label' => 'Sent / delivered', 'value' => $stats['attempts_24h_sentish'] ?? 0, 'icon' => 'fa-circle-check', 'class' => 'wa-panel-sky', 'accent' => 'text-sky-700'],
                            ['label' => 'Failed attempts', 'value' => $stats['attempts_24h_failed'] ?? 0, 'icon' => 'fa-circle-xmark', 'class' => 'wa-panel-amber', 'accent' => 'text-rose-700'],
                            ['label' => 'Read receipts', 'value' => $stats['attempts_24h_read'] ?? 0, 'icon' => 'fa-eye', 'class' => 'wa-panel-sky', 'accent' => 'text-cyan-700'],
                            ['label' => 'Webhook events', 'value' => $stats['events_24h'] ?? 0, 'icon' => 'fa-code-branch', 'class' => 'wa-panel-amber', 'accent' => 'text-amber-700'],
                            ['label' => 'Opt-outs', 'value' => $stats['optouts_24h'] ?? 0, 'icon' => 'fa-user-slash', 'class' => 'wa-panel-emerald', 'accent' => 'text-emerald-700'],
                        ];
                        ?>
                        <?php foreach ($kpis as $kpi): ?>
                            <article class="wa-snapshot-card rounded-[1.45rem] p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500"><?= esc($kpi['label']) ?></p>
                                        <p class="mt-3 text-3xl font-black text-slate-950"><?= esc($formatCount($kpi['value'])) ?></p>
                                    </div>
                                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/80 <?= esc($kpi['accent'], 'attr') ?> shadow-sm ring-1 ring-white">
                                        <i class="fas <?= esc($kpi['icon'], 'attr') ?>"></i>
                                    </span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="wa-panel rounded-[1.6rem] overflow-hidden">
                        <div class="flex flex-col gap-3 border-b border-slate-200 bg-white/85 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-black text-slate-950">Recent message stream</h2>
                                <p class="text-sm font-semibold text-slate-500">Latest outbound and webhook activity across tenants.</p>
                            </div>
                            <a href="#activity" onclick="showWATab('activity')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-800">
                                <i class="fas fa-wave-square"></i>
                                <span>Open activity</span>
                            </a>
                        </div>
                        <?php if (empty($recentStream)): ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-inbox text-3xl text-slate-300"></i>
                                <p class="mt-3 text-sm font-bold text-slate-500">No recent WhatsApp activity yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-slate-100">
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
                                            <span class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl <?= $type === 'attempt' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' ?>">
                                                <i class="fas <?= $type === 'attempt' ? 'fa-paper-plane' : 'fa-code-branch' ?>"></i>
                                            </span>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="font-black text-slate-950"><?= esc($streamTitle) ?></p>
                                                    <span class="wa-chip <?= esc($statusClass($item['effective_status'] ?? null), 'attr') ?> ring-1"><?= esc($streamStatus) ?></span>
                                                </div>
                                                <p class="mt-1 text-sm font-semibold text-slate-600"><?= esc($streamSummary !== '' ? $streamSummary : '-') ?></p>
                                            </div>
                                        </div>
                                        <p class="text-xs font-bold text-slate-400 sm:text-end"><?= esc($item['created_label'] ?? $formatDate($item['created_at'] ?? null)) ?></p>
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
                                <h2 class="text-lg font-black text-slate-950">Needs attention</h2>
                                <p class="text-sm font-semibold text-slate-500">Failures, stalled queue, or integration warnings.</p>
                            </div>
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-amber-700 shadow-sm">
                                <i class="fas fa-bell"></i>
                            </span>
                        </div>
                        <div class="mt-4 space-y-3">
                            <?php if (empty($needsAttention)): ?>
                                <div class="rounded-2xl bg-white/82 p-4 text-sm font-bold text-emerald-700 ring-1 ring-emerald-100">
                                    <i class="fas fa-check-circle me-2"></i>No operational alerts right now.
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
                                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-amber-100">
                                        <p class="font-black text-slate-950"><?= esc($warningTitle) ?></p>
                                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.08em] text-amber-700"><?= esc($warningMeta !== '' ? $warningMeta : 'WhatsApp send attempt') ?></p>
                                        <p class="mt-2 text-sm font-semibold text-slate-600"><?= esc($warningMessage !== '' ? $warningMessage : 'Review the latest provider response for this message.') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="wa-panel rounded-[1.6rem] p-5">
                        <h2 class="text-lg font-black text-slate-950">Delivery mix</h2>
                        <div class="mt-4 space-y-3">
                            <?php if (empty($statusBreakdown)): ?>
                                <p class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-500">No status breakdown available yet.</p>
                            <?php else: ?>
                                <?php foreach ($statusBreakdown as $label => $count): ?>
                                    <?php
                                    $label = (string) $label;
                                    $count = (int) $count;
                                    ?>
                                    <div>
                                        <div class="flex items-center justify-between text-sm font-black">
                                            <span class="text-slate-700"><?= esc(ucfirst($label)) ?></span>
                                            <span class="text-slate-950"><?= esc($formatCount($count)) ?></span>
                                        </div>
                                        <div class="mt-2 h-2 rounded-full bg-slate-100">
                                            <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-sky-500" style="width: <?= esc((string) min(100, max(4, $count)), 'attr') ?>%"></div>
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
                    <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">Safe test send</p>
                            <h2 class="mt-1 text-2xl font-black text-slate-950">Send a controlled WhatsApp test</h2>
                            <p class="mt-2 text-sm font-semibold text-slate-600">Use this for tenant setup checks and template verification. Production workflow messages still belong to tenant operations.</p>
                        </div>
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <i class="fas fa-paper-plane"></i>
                        </span>
                    </div>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Clinic</span>
                            <select name="clinic_id" class="wa-field" required>
                                <option value="">Select clinic</option>
                                <?php foreach ($testSendClinics as $clinic): ?>
                                    <?php $clinicId = (string) ($clinic['id'] ?? ''); ?>
                                    <option value="<?= esc($clinicId, 'attr') ?>" <?= (string) old('clinic_id') === $clinicId ? 'selected' : '' ?>>
                                        <?= esc($clinic['clinic_name'] ?? ('Clinic #' . $clinicId)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Recipient number</span>
                            <input type="text" name="target_number" value="<?= esc((string) old('target_number'), 'attr') ?>" class="wa-field" placeholder="+970..." required>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Send mode</span>
                            <select id="wa-test-send-mode" name="send_mode" class="wa-field">
                                <?php $sendMode = (string) old('send_mode', 'text'); ?>
                                <option value="text" <?= $sendMode === 'text' ? 'selected' : '' ?>>Free text</option>
                                <option value="template" <?= $sendMode === 'template' ? 'selected' : '' ?>>Template</option>
                            </select>
                        </label>

                        <label id="wa-template-source" class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Template source</span>
                            <?php $templateSource = (string) old('template_source', 'explicit'); ?>
                            <select name="template_source" class="wa-field">
                                <option value="explicit" <?= $templateSource === 'explicit' ? 'selected' : '' ?>>Explicit template</option>
                                <option value="fallback" <?= $templateSource === 'fallback' ? 'selected' : '' ?>>Clinic fallback template</option>
                                <option value="trigger_template" <?= $templateSource === 'trigger_template' ? 'selected' : '' ?>>Trigger catalog</option>
                            </select>
                        </label>
                    </div>

                    <div id="wa-test-text-fields" class="mt-5">
                        <label class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Message</span>
                            <textarea name="message" rows="5" class="wa-field" placeholder="Write a short operational test message."><?= esc((string) old('message')) ?></textarea>
                        </label>
                    </div>

                    <div id="wa-test-template-fields" class="mt-5 space-y-5">
                        <div class="grid gap-5 lg:grid-cols-2">
                            <label id="wa-template-name-wrap" class="block">
                                <span class="mb-2 block text-sm font-black text-slate-800">Template name</span>
                                <input type="text" name="template_name" value="<?= esc((string) old('template_name'), 'attr') ?>" class="wa-field" placeholder="appointment_reminder">
                            </label>

                            <label id="wa-template-language-wrap" class="block">
                                <span class="mb-2 block text-sm font-black text-slate-800">Template language</span>
                                <input type="text" name="template_language" value="<?= esc((string) old('template_language', 'en_US'), 'attr') ?>" class="wa-field" placeholder="en_US">
                            </label>
                        </div>

                        <label id="wa-trigger-template-wrap" class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Trigger template</span>
                            <select id="wa-trigger-key" name="trigger_key" class="wa-field">
                                <option value="">Select trigger</option>
                                <?php foreach ($triggerCatalog as $key => $trigger): ?>
                                    <option value="<?= esc((string) $key, 'attr') ?>" <?= (string) old('trigger_key') === (string) $key ? 'selected' : '' ?>>
                                        <?= esc($trigger['label'] ?? $key) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <div id="wa-trigger-summary" class="hidden rounded-2xl bg-sky-50 p-4 ring-1 ring-sky-100">
                            <p id="wa-trigger-summary-title" class="font-black text-slate-950"></p>
                            <p id="wa-trigger-summary-description" class="mt-1 text-sm font-semibold text-slate-600"></p>
                            <div id="wa-trigger-summary-vars" class="mt-3 flex flex-wrap gap-2"></div>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Template components JSON</span>
                            <textarea name="template_components_json" rows="5" class="wa-field wa-code" placeholder='[{"type":"body","parameters":[...]}]'><?= esc((string) old('template_components_json')) ?></textarea>
                        </label>

                        <div class="grid gap-5 lg:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-black text-slate-800">Header text</span>
                                <input type="text" name="template_header_text" value="<?= esc((string) old('template_header_text'), 'attr') ?>" class="wa-field">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-black text-slate-800">Body params</span>
                                <input type="text" name="template_body_params" value="<?= esc((string) old('template_body_params'), 'attr') ?>" class="wa-field" placeholder="Comma separated values">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-black text-slate-800">Button type</span>
                                <input type="text" name="template_button_type" value="<?= esc((string) old('template_button_type'), 'attr') ?>" class="wa-field" placeholder="url">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-black text-slate-800">Button index</span>
                                <input type="text" name="template_button_index" value="<?= esc((string) old('template_button_index'), 'attr') ?>" class="wa-field" placeholder="0">
                            </label>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-black text-slate-800">Button value</span>
                            <input type="text" name="template_button_value" value="<?= esc((string) old('template_button_value'), 'attr') ?>" class="wa-field">
                        </label>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <a href="<?= base_url('controlplane/whatsapp') ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">Reset</a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:from-emerald-700 hover:to-sky-700">
                            <i class="fas fa-paper-plane"></i>
                            <span>Send test message</span>
                        </button>
                    </div>
                </form>

                <aside class="space-y-5">
                    <div class="wa-panel wa-panel-emerald rounded-[1.6rem] p-5">
                        <h3 class="text-lg font-black text-slate-950">Operator rules</h3>
                        <div class="mt-4 space-y-3 text-sm font-semibold text-slate-700">
                            <p><i class="fas fa-check-circle me-2 text-emerald-600"></i>Use test sends for setup and health checks only.</p>
                            <p><i class="fas fa-check-circle me-2 text-emerald-600"></i>Templates should be managed from settings.</p>
                            <p><i class="fas fa-check-circle me-2 text-emerald-600"></i>Opt-outs and provider failures stay visible in this workspace.</p>
                        </div>
                    </div>
                    <div class="wa-panel rounded-[1.6rem] p-5">
                        <h3 class="text-lg font-black text-slate-950">Template settings</h3>
                        <p class="mt-2 text-sm font-semibold text-slate-600">Secret rotation, policy controls, and trigger templates live in Control Plane settings.</p>
                        <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-slate-800">
                            <i class="fas fa-sliders"></i>
                            <span>Open settings</span>
                        </a>
                    </div>
                </aside>
            </div>
        </section>

        <section id="pane-activity" class="wa-pane <?= $initialTab === 'activity' ? '' : 'hidden' ?>">
            <div class="wa-panel overflow-hidden rounded-[1.7rem]">
                <div class="border-b border-slate-200 bg-white/85 p-5">
                    <h2 class="text-2xl font-black text-slate-950">Recent send attempts</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Trace outbound test and workflow sends without leaving the control plane.</p>
                </div>
                <?php if (empty($recentAttempts)): ?>
                    <div class="p-10 text-center">
                        <i class="fas fa-paper-plane text-4xl text-slate-300"></i>
                        <p class="mt-3 text-sm font-bold text-slate-500">No send attempts recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="wa-mini-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Tenant</th>
                                    <th>Recipient</th>
                                    <th>Content</th>
                                    <th>Delivery</th>
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
                                        <td><span class="wa-chip <?= esc($statusClass($attemptStatus), 'attr') ?> ring-1"><?= esc($attemptStatusMeta['label'] ?? $attemptStatus) ?></span></td>
                                        <td>
                                            <p class="font-black text-slate-950"><?= esc($attempt['clinic_name'] ?? 'Unknown clinic') ?></p>
                                            <p class="mt-1 text-xs font-bold text-slate-400">Attempt #<?= esc((string) ($attempt['attempt_id'] ?? '-')) ?></p>
                                        </td>
                                        <td>
                                            <p class="font-bold text-slate-800"><?= esc($attemptRecipient['label'] ?? '-') ?></p>
                                            <p class="wa-code mt-1 font-bold text-slate-500"><?= esc($attemptRecipient['secondary'] ?? '') ?></p>
                                        </td>
                                        <td>
                                            <p class="font-black text-slate-950"><?= esc($attemptContent['title'] ?? '-') ?></p>
                                            <p class="mt-1 text-sm font-semibold text-slate-600"><?= esc($attemptContent['subtitle'] ?? '') ?></p>
                                            <p class="mt-2 max-w-sm text-sm font-semibold text-slate-700"><?= esc($attemptContent['preview'] ?? '-') ?></p>
                                            <?php
                                            $attemptFailure = is_array($attempt['failure'] ?? null) ? $attempt['failure'] : [];
                                            $attemptFailureText = (string) ($attemptFailure['detail'] ?? $attemptFailure['title'] ?? '');
                                            ?>
                                            <?php if ($attemptFailureText !== ''): ?>
                                                <p class="mt-2 rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 ring-1 ring-rose-100"><?= esc($attemptFailureText) ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <p class="text-sm font-black text-slate-950"><?= esc($attempt['created_label'] ?? $formatDate($attempt['created_at'] ?? null)) ?></p>
                                            <p class="mt-1 text-xs font-bold text-slate-400"><?= esc($attemptDelivery['label'] ?? ('Notification #' . (string) ($attempt['notification_id'] ?? '-'))) ?></p>
                                            <?php if (!empty($attempt['provider_message_id'])): ?>
                                                <p class="wa-code mt-1 text-xs font-bold text-slate-400"><?= esc($attempt['provider_message_id']) ?></p>
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
                    <div class="wa-panel rounded-[1.7rem] p-10 text-center lg:col-span-2">
                        <i class="fas fa-code-branch text-4xl text-slate-300"></i>
                        <p class="mt-3 text-sm font-bold text-slate-500">No webhook events recorded yet.</p>
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
                                    <span class="wa-chip bg-sky-100 text-sky-800 ring-1 ring-sky-200"><?= esc($event['event_type'] ?? 'webhook') ?></span>
                                    <h3 class="mt-3 text-lg font-black text-slate-950"><?= esc($eventTitle) ?></h3>
                                    <?php if ($eventDetail !== ''): ?>
                                        <p class="mt-1 text-sm font-semibold text-slate-600"><?= esc($eventDetail) ?></p>
                                    <?php endif; ?>
                                    <?php if ($eventSubject !== '' || $eventCode !== ''): ?>
                                        <p class="mt-2 text-xs font-bold text-slate-500">
                                            <?php if ($eventSubject !== ''): ?><span class="wa-code"><?= esc($eventSubject) ?></span><?php endif; ?>
                                            <?php if ($eventSubject !== '' && $eventCode !== ''): ?><span class="mx-1">·</span><?php endif; ?>
                                            <?php if ($eventCode !== ''): ?><span><?= esc('Code ' . $eventCode) ?></span><?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="mt-1 text-xs font-bold text-slate-400"><?= esc($event['processed_label'] ?? $formatDate($event['processed_at'] ?? null)) ?></p>
                                </div>
                                <button type="button" onclick="copyToClipboard(<?= json_encode((string) $payload) ?>)" class="rounded-xl bg-white px-3 py-2 text-xs font-extrabold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
                                    <i class="fas fa-copy me-1"></i>Copy
                                </button>
                            </div>
                            <pre class="mt-4 max-h-72 overflow-auto rounded-2xl bg-slate-50 p-4 text-xs font-semibold text-slate-700 ring-1 ring-slate-200"><?= esc((string) $payload) ?></pre>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="pane-optouts" class="wa-pane <?= $initialTab === 'optouts' ? '' : 'hidden' ?>">
            <div class="wa-panel overflow-hidden rounded-[1.7rem]">
                <div class="border-b border-slate-200 bg-white/85 p-5">
                    <h2 class="text-2xl font-black text-slate-950">Recent opt-outs</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Patients who should not receive WhatsApp workflow messages.</p>
                </div>
                <?php if (empty($recentOptouts)): ?>
                    <div class="p-10 text-center">
                        <i class="fas fa-user-slash text-4xl text-slate-300"></i>
                        <p class="mt-3 text-sm font-bold text-slate-500">No opt-outs recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="wa-mini-table">
                            <thead>
                                <tr>
                                    <th>Clinic</th>
                                    <th>Phone</th>
                                    <th>Reason</th>
                                    <th>Recorded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOptouts as $optout): ?>
                                    <tr>
                                        <td class="font-black text-slate-950"><?= esc($optout['clinic_name'] ?? 'Unknown clinic') ?></td>
                                        <td class="wa-code font-bold text-slate-700"><?= esc($optout['wa_number'] ?? '-') ?></td>
                                        <td class="text-sm font-semibold text-slate-600"><?= esc(trim((string) ($optout['bsuid'] ?? '') . ' ' . (string) ($optout['parent_bsuid'] ?? '')) ?: '-') ?></td>
                                        <td class="text-sm font-bold text-slate-500"><?= esc($optout['created_label'] ?? $formatDate($optout['created_at'] ?? null)) ?></td>
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
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700"><i class="fas fa-route"></i></span>
                    <h3 class="mt-4 text-lg font-black text-slate-950">Workflow ownership</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Tenant events create workflow messages. This control-plane page observes, tests, and governs them without becoming a tenant messaging workspace.</p>
                </div>
                <div class="wa-panel wa-panel-sky rounded-[1.7rem] p-5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700"><i class="fas fa-lock"></i></span>
                    <h3 class="mt-4 text-lg font-black text-slate-950">Safety model</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Secrets, policy toggles, templates, and emergency pause controls remain centralized in settings with existing guardrails.</p>
                </div>
                <div class="wa-panel wa-panel-amber rounded-[1.7rem] p-5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700"><i class="fas fa-list-check"></i></span>
                    <h3 class="mt-4 text-lg font-black text-slate-950">Operator checklist</h3>
                    <div class="mt-3 space-y-2 text-sm font-semibold text-slate-700">
                        <?php if (empty($operatorGuidance)): ?>
                            <p>Monitor failures, review opt-outs, and keep templates aligned with clinic workflows.</p>
                        <?php else: ?>
                            <?php foreach ($operatorGuidance as $item): ?>
                                <?php $guidanceLine = $guidanceText($item); ?>
                                <?php if ($guidanceLine !== ''): ?>
                                    <p><i class="fas fa-check me-2 text-emerald-600"></i><?= esc($guidanceLine) ?></p>
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
        toast.className = 'copy-toast fixed bottom-5 right-5 z-[9999] rounded-2xl bg-white px-4 py-3 text-sm font-extrabold text-slate-800 shadow-2xl ring-1 ring-slate-200';
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
                badge.className = 'wa-chip bg-white text-sky-800 ring-1 ring-sky-200';
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
