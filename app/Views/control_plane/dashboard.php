<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
    $storageBytes = (int) ($stats['storage_total_bytes'] ?? 0);
    $storageGb = $storageBytes > 0 ? ($storageBytes / 1024 / 1024 / 1024) : 0;
    $lastEventAt = $stats['last_event_at'] ?? null;
    $lastEventLabel = $lastEventAt ? date('M j, Y g:i A', strtotime($lastEventAt)) : 'No events yet';
    $activeClinicRatio = ((int) ($stats['total_clinics'] ?? 0)) > 0
        ? round(((int) ($stats['active_clinics'] ?? 0) / (int) ($stats['total_clinics'] ?? 1)) * 100)
        : 0;

    $kpis = [
        [
            'label' => 'Total clinics',
            'value' => number_format($stats['total_clinics'] ?? 0),
            'detail' => 'Active: ' . number_format($stats['active_clinics'] ?? 0) . ' (' . $activeClinicRatio . '%)',
            'icon' => 'fas fa-hospital-user',
            'tone' => 'from-blue-700 to-cyan-600',
            'fill' => 'from-blue-700/16 to-cyan-500/10',
            'text' => 'text-blue-950',
        ],
        [
            'label' => 'Active subscriptions',
            'value' => number_format($stats['active_subscriptions'] ?? 0),
            'detail' => 'Renewal watch: ' . number_format($stats['expiring_subscriptions'] ?? 0),
            'icon' => 'fas fa-receipt',
            'tone' => 'from-emerald-700 to-teal-600',
            'fill' => 'from-emerald-700/16 to-teal-500/10',
            'text' => 'text-emerald-950',
        ],
        [
            'label' => 'Estimated MRR',
            'value' => '$' . number_format((float) ($stats['mrr'] ?? 0), 2),
            'detail' => 'Normalized active plan revenue',
            'icon' => 'fas fa-chart-line',
            'tone' => 'from-violet-700 to-fuchsia-600',
            'fill' => 'from-violet-700/16 to-fuchsia-500/10',
            'text' => 'text-violet-950',
        ],
        [
            'label' => 'Pending requests',
            'value' => number_format($stats['pending_requests'] ?? 0),
            'detail' => 'Plan changes awaiting review',
            'icon' => 'fas fa-inbox',
            'tone' => 'from-amber-600 to-orange-600',
            'fill' => 'from-amber-600/18 to-orange-500/10',
            'text' => 'text-amber-950',
        ],
        [
            'label' => 'Active plans',
            'value' => number_format($stats['active_plans'] ?? 0),
            'detail' => 'Published service catalog',
            'icon' => 'fas fa-layer-group',
            'tone' => 'from-indigo-700 to-blue-600',
            'fill' => 'from-indigo-700/16 to-blue-500/10',
            'text' => 'text-indigo-950',
        ],
        [
            'label' => 'Active users',
            'value' => number_format($stats['active_users'] ?? 0),
            'detail' => 'Enabled accounts across platform',
            'icon' => 'fas fa-user-shield',
            'tone' => 'from-slate-800 to-slate-600',
            'fill' => 'from-slate-700/14 to-slate-500/8',
            'text' => 'text-slate-950',
        ],
        [
            'label' => 'Storage used',
            'value' => number_format($storageGb, 2) . ' GB',
            'detail' => 'Attachments and generated files',
            'icon' => 'fas fa-hdd',
            'tone' => 'from-teal-700 to-emerald-600',
            'fill' => 'from-teal-700/16 to-emerald-500/10',
            'text' => 'text-teal-950',
        ],
        [
            'label' => 'Audit events',
            'value' => number_format($stats['events_7d'] ?? 0),
            'detail' => 'Last 7 days · ' . number_format($stats['events_30d'] ?? 0) . ' in 30 days',
            'icon' => 'fas fa-wave-square',
            'tone' => 'from-rose-700 to-red-600',
            'fill' => 'from-rose-700/14 to-red-500/8',
            'text' => 'text-rose-950',
        ],
    ];

    $quickActions = [
        ['label' => 'Onboard clinic', 'href' => base_url('controlplane/onboarding/clinic/create'), 'icon' => 'fas fa-plus', 'tone' => 'from-blue-700 to-cyan-600'],
        ['label' => 'Create plan', 'href' => base_url('controlplane/plans/create'), 'icon' => 'fas fa-layer-group', 'tone' => 'from-violet-700 to-fuchsia-600'],
        ['label' => 'Review requests', 'href' => base_url('controlplane/plan-requests'), 'icon' => 'fas fa-inbox', 'tone' => 'from-amber-600 to-orange-600'],
        ['label' => 'Operations', 'href' => base_url('controlplane/operations'), 'icon' => 'fas fa-cogs', 'tone' => 'from-slate-800 to-slate-600'],
    ];
?>

<div class="cp-dashboard min-h-screen bg-[radial-gradient(circle_at_8%_0%,rgba(59,130,246,.12),transparent_30%),radial-gradient(circle_at_92%_8%,rgba(16,185,129,.10),transparent_28%),linear-gradient(135deg,#eef5ff_0%,#f8fafc_48%,#ecfdf5_100%)]">
    <div class="w-full space-y-5 px-3 py-3 sm:px-4 lg:px-5">
        <section class="overflow-hidden rounded-2xl border border-slate-900/10 bg-slate-950 shadow-xl shadow-slate-900/16">
            <div class="relative p-4 sm:p-5">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_16%_12%,rgba(59,130,246,.54),transparent_30%),radial-gradient(circle_at_84%_10%,rgba(16,185,129,.32),transparent_25%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_52%,#0f766e_100%)]"></div>
                <div class="relative grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg ring-1 ring-white/20 backdrop-blur">
                            <i class="fas fa-tachometer-alt text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-emerald-200">Global operations</p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">Control Plane</h1>
                            <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-blue-50/90">Monitor clinics, subscriptions, revenue, platform activity, and operator queues from one admin workspace.</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                <span class="rounded-full bg-emerald-400/20 px-3 py-1.5 text-emerald-50 ring-1 ring-emerald-200/25">
                                    <i class="fas fa-globe me-1"></i>Global Mode
                                </span>
                                <span class="rounded-full bg-white/16 px-3 py-1.5 text-white ring-1 ring-white/18">Last event: <?= esc($lastEventLabel) ?></span>
                                <span class="rounded-full bg-white/16 px-3 py-1.5 text-white ring-1 ring-white/18">v2.0.0</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid min-w-full grid-cols-2 gap-3 sm:min-w-[28rem] lg:grid-cols-4">
                        <div class="rounded-2xl bg-white/15 p-3 ring-1 ring-white/15 backdrop-blur">
                            <p class="text-[11px] font-bold text-blue-100">Clinics</p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= number_format($stats['total_clinics'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-emerald-400/20 p-3 ring-1 ring-emerald-200/25 backdrop-blur">
                            <p class="text-[11px] font-bold text-emerald-100">Subscriptions</p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= number_format($stats['active_subscriptions'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-violet-400/20 p-3 ring-1 ring-violet-200/20 backdrop-blur">
                            <p class="text-[11px] font-bold text-violet-100">MRR</p>
                            <p class="mt-1 text-2xl font-bold text-white">$<?= number_format((float) ($stats['mrr'] ?? 0), 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-amber-400/20 p-3 ring-1 ring-amber-200/25 backdrop-blur">
                            <p class="text-[11px] font-bold text-amber-100">Queue</p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= number_format($stats['pending_requests'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($kpis as $kpi): ?>
                <article class="rounded-2xl border border-slate-200/80 bg-gradient-to-br <?= esc($kpi['fill']) ?> p-4 shadow-lg shadow-slate-900/5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-600"><?= esc($kpi['label']) ?></p>
                            <p class="mt-2 truncate text-2xl font-bold <?= esc($kpi['text']) ?>"><?= esc($kpi['value']) ?></p>
                            <p class="mt-1 text-xs font-medium text-slate-500"><?= esc($kpi['detail']) ?></p>
                        </div>
                        <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br <?= esc($kpi['tone']) ?> text-white shadow-lg">
                            <i class="<?= esc($kpi['icon']) ?>"></i>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,.85fr)]">
            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-blue-900/8 sm:p-5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Revenue and subscription trend</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Estimated MRR from active subscriptions, normalized monthly.</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100">
                        <i class="fas fa-chart-line me-2"></i>Revenue
                    </span>
                </div>
                <div class="h-72 rounded-2xl bg-gradient-to-br from-emerald-50 to-blue-50/70 p-3">
                    <canvas id="mrrGrowthChart"></canvas>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-blue-900/8 sm:p-5">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Audit activity</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Control-plane events over the last 14 days.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 ring-1 ring-blue-100"><?= number_format($stats['events_7d'] ?? 0) ?> / 7d</span>
                </div>
                <div class="h-72 rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50/70 p-3">
                    <canvas id="activityVolumeChart"></canvas>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-slate-900/6 sm:p-5">
                <h2 class="text-lg font-bold text-slate-950">Plan mix</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Active subscriptions by plan.</p>
                <div class="mt-4 h-64 rounded-2xl bg-gradient-to-br from-teal-50 to-emerald-50/70 p-3">
                    <canvas id="planDistributionChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-slate-900/6 sm:p-5">
                <h2 class="text-lg font-bold text-slate-950">Clinic growth</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">New clinics created over the last 6 months.</p>
                <div class="mt-4 h-64 rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50/70 p-3">
                    <canvas id="clinicGrowthChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-slate-900/6 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Operator shortcuts</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Common control-plane tasks.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Admin</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <?php foreach ($quickActions as $action): ?>
                        <a href="<?= esc($action['href'], 'attr') ?>" class="group flex min-h-28 flex-col justify-between rounded-2xl border border-slate-200 bg-slate-50/70 p-3 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white hover:shadow-lg">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br <?= esc($action['tone']) ?> text-white shadow-md">
                                <i class="<?= esc($action['icon']) ?>"></i>
                            </span>
                            <span class="text-sm font-bold text-slate-800 group-hover:text-blue-800"><?= esc($action['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,.9fr)]">
            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-slate-900/6 sm:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Recent clinics</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Newest tenants in the platform.</p>
                    </div>
                    <a href="<?= base_url('controlplane/clinics') ?>" class="inline-flex items-center rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100">
                        View all <i class="fas fa-arrow-right ms-2 rtl-back-icon"></i>
                    </a>
                </div>
                <div class="space-y-2">
                    <?php if (!empty($recent_clinics)): ?>
                        <?php foreach ($recent_clinics as $clinic): ?>
                            <?php $status = $clinic['status'] ?? 'inactive'; ?>
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-gradient-to-r from-white to-blue-50/60 p-3">
                                <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-700 to-cyan-600 text-xs font-bold text-white">
                                    <?= esc(ui_badge_text($clinic['name'] ?? '', 2, 'CL')) ?>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-950"><?= esc($clinic['name'] ?? 'Clinic') ?></p>
                                    <p class="text-xs font-medium text-slate-500">ID: <?= esc($clinic['id'] ?? '') ?></p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' ?>">
                                    <?= esc($status) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm font-medium text-slate-400">No recent clinics found.</div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-slate-900/6 sm:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Plan requests</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Latest plan change queue.</p>
                    </div>
                    <a href="<?= base_url('controlplane/plan-requests') ?>" class="inline-flex items-center rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 ring-1 ring-amber-100 hover:bg-amber-100">
                        Review <i class="fas fa-arrow-right ms-2 rtl-back-icon"></i>
                    </a>
                </div>
                <div class="space-y-2">
                    <?php if (!empty($recent_plan_requests)): ?>
                        <?php foreach ($recent_plan_requests as $request): ?>
                            <div class="flex items-center gap-3 rounded-2xl border border-amber-100 bg-gradient-to-r from-white to-amber-50/70 p-3">
                                <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-600 to-orange-600 text-white">
                                    <i class="fas fa-inbox"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-950"><?= esc($request['clinic_name'] ?? 'Clinic') ?></p>
                                    <p class="truncate text-xs font-medium text-slate-500"><?= esc($request['summary'] ?? 'Plan change') ?></p>
                                </div>
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">Pending</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm font-medium text-slate-400">No pending requests.</div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-xl shadow-slate-900/6 sm:p-5">
                <h2 class="text-lg font-bold text-slate-950">Platform pulse</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Storage and audit indicators.</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl bg-gradient-to-br from-emerald-700 to-teal-600 p-4 text-white shadow-lg shadow-emerald-700/20">
                        <p class="text-xs font-semibold text-emerald-100">Storage footprint</p>
                        <p class="mt-2 text-2xl font-bold"><?= number_format($storageGb, 2) ?> GB</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-100 p-3">
                            <p class="text-xs font-semibold text-slate-500">Events 7d</p>
                            <p class="mt-1 text-xl font-bold text-slate-950"><?= number_format($stats['events_7d'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-slate-100 p-3">
                            <p class="text-xs font-semibold text-slate-500">Events 30d</p>
                            <p class="mt-1 text-xl font-bold text-slate-950"><?= number_format($stats['events_30d'] ?? 0) ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-bold text-slate-500">Storage hotspots</p>
                        <div class="space-y-2">
                            <?php if (!empty($storage_top ?? [])): ?>
                                <?php foreach (array_slice($storage_top, 0, 3) as $row): ?>
                                    <?php
                                        $clinicName = $row['clinic_name'] ?? 'Clinic';
                                        $sizeBytes = (int) ($row['total_size'] ?? 0);
                                        $sizeGb = $sizeBytes > 0 ? ($sizeBytes / 1024 / 1024 / 1024) : 0;
                                    ?>
                                    <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2 ring-1 ring-emerald-100">
                                        <span class="truncate text-xs font-bold text-emerald-950"><?= esc($clinicName) ?></span>
                                        <span class="text-xs font-bold text-emerald-700"><?= number_format($sizeGb, 2) ?> GB</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="rounded-xl bg-slate-50 px-3 py-2 text-xs font-medium text-slate-400">No storage usage yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dashboardChartData = <?= json_encode($chart_data ?? []) ?>;

    const baseChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#64748b', font: { weight: 600 } },
            },
            y: {
                grid: { color: 'rgba(148, 163, 184, 0.28)' },
                ticks: { color: '#64748b', font: { weight: 600 } },
            },
        },
    };

    const mrrCtx = document.getElementById('mrrGrowthChart');
    if (mrrCtx && Array.isArray(dashboardChartData.mrr_series)) {
        const mrrLabels = dashboardChartData.mrr_series.map(point => point.label);
        const mrrTotals = dashboardChartData.mrr_series.map(point => point.value);
        const mrrGradient = mrrCtx.getContext('2d').createLinearGradient(0, 0, 0, 240);
        mrrGradient.addColorStop(0, 'rgba(16, 185, 129, 0.36)');
        mrrGradient.addColorStop(1, 'rgba(37, 99, 235, 0.04)');

        new Chart(mrrCtx, {
            type: 'line',
            data: {
                labels: mrrLabels,
                datasets: [{
                    label: 'MRR ($)',
                    data: mrrTotals,
                    borderColor: '#059669',
                    backgroundColor: mrrGradient,
                    pointBackgroundColor: '#047857',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: {
                ...baseChartOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: context => '$' + Number(context.parsed.y ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    ...baseChartOptions.scales,
                    y: {
                        ...baseChartOptions.scales.y,
                        ticks: {
                            color: '#64748b',
                            font: { weight: 600 },
                            callback: value => typeof value === 'number' ? '$' + value.toLocaleString() : value,
                        },
                    },
                },
            },
        });
    }

    const activityCtx = document.getElementById('activityVolumeChart');
    if (activityCtx && Array.isArray(dashboardChartData.activity_series)) {
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: dashboardChartData.activity_series.map(point => point.day),
                datasets: [{
                    label: 'Events',
                    data: dashboardChartData.activity_series.map(point => point.total),
                    backgroundColor: '#2563eb',
                    borderRadius: 8,
                    maxBarThickness: 26,
                }],
            },
            options: baseChartOptions,
        });
    }

    const distributionCtx = document.getElementById('planDistributionChart');
    if (distributionCtx && Array.isArray(dashboardChartData.plan_distribution)) {
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: dashboardChartData.plan_distribution.map(point => point.label),
                datasets: [{
                    data: dashboardChartData.plan_distribution.map(point => point.value),
                    backgroundColor: ['#059669', '#14b8a6', '#2563eb', '#f59e0b', '#dc2626', '#7c3aed'],
                    borderColor: 'rgba(255,255,255,0.88)',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#475569', boxWidth: 10, boxHeight: 10, font: { weight: 600 } },
                    },
                },
            },
        });
    }

    const growthCtx = document.getElementById('clinicGrowthChart');
    if (growthCtx && Array.isArray(dashboardChartData.clinic_growth)) {
        const growthGradient = growthCtx.getContext('2d').createLinearGradient(0, 0, 0, 220);
        growthGradient.addColorStop(0, 'rgba(37, 99, 235, 0.32)');
        growthGradient.addColorStop(1, 'rgba(124, 58, 237, 0.04)');

        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: dashboardChartData.clinic_growth.map(point => point.label),
                datasets: [{
                    label: 'New Clinics',
                    data: dashboardChartData.clinic_growth.map(point => point.value),
                    borderColor: '#2563eb',
                    backgroundColor: growthGradient,
                    pointBackgroundColor: '#1d4ed8',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: baseChartOptions,
        });
    }
</script>
<?= $this->endSection() ?>
