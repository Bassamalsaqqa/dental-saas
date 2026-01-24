<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Dashboard</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Control plane pulse</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Snapshot of clinics, subscriptions, and platform health.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Read-only</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="glass-card rounded-3xl p-5 bg-gradient-to-br from-blue-50 via-white to-emerald-50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-500">Total Clinics</p>
                    <h3 class="text-3xl font-semibold text-slate-900 mt-2"><?= number_format($stats['total_clinics'] ?? 0) ?></h3>
                    <p class="text-xs text-slate-500 mt-2">Active: <?= number_format($stats['active_clinics'] ?? 0) ?></p>
                </div>
                <span class="w-12 h-12 rounded-2xl bg-white/70 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-hospital-user text-lg"></i>
                </span>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-500">Active Subscriptions</p>
                    <h3 class="text-3xl font-semibold text-slate-900 mt-2"><?= number_format($stats['active_subscriptions'] ?? 0) ?></h3>
                    <p class="text-xs text-slate-500 mt-2">Active renewals</p>
                    <p class="text-[11px] text-slate-400">Pending requests: <?= number_format($stats['pending_requests'] ?? 0) ?></p>
                </div>
                <span class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-receipt text-lg"></i>
                </span>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-500">Active Plans</p>
                    <h3 class="text-3xl font-semibold text-slate-900 mt-2"><?= number_format($stats['active_plans'] ?? 0) ?></h3>
                    <p class="text-xs text-slate-500 mt-2">Plans available</p>
                </div>
                <span class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-layer-group text-lg"></i>
                </span>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-500">Estimated MRR</p>
                    <h3 class="text-3xl font-semibold text-slate-900 mt-2">$<?= number_format((float) ($stats['mrr'] ?? 0), 2) ?></h3>
                    <p class="text-xs text-slate-500 mt-2">Expiring in 30d: <?= number_format($stats['expiring_subscriptions'] ?? 0) ?></p>
                </div>
                <span class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-chart-line text-lg"></i>
                </span>
            </div>
        </div>
    </div>

    <!-- Modular Charts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- MRR Chart (Wide) -->
        <div class="glass-card rounded-3xl p-6 md:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">MRR growth</h3>
                    <p class="text-xs text-slate-500 mt-1">Trend of monthly recurring revenue.</p>
                </div>
                <span class="text-xs uppercase tracking-widest text-slate-400">Last 6 months</span>
            </div>
            <div class="h-64">
                <canvas id="mrrGrowthChart"></canvas>
            </div>
        </div>

        <!-- Status Chart (Square) -->
        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Clinic status</h3>
                    <p class="text-xs text-slate-500 mt-1">Active vs suspended.</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="clinicStatusChart"></canvas>
            </div>
        </div>

        <!-- Activity Chart (Wide Row) -->
        <div class="glass-card rounded-3xl p-6 md:col-span-2 xl:col-span-3">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Activity volume</h3>
                    <p class="text-xs text-slate-500 mt-1">Control plane events (daily total).</p>
                </div>
                <span class="text-xs uppercase tracking-widest text-slate-400">Last 14 days</span>
            </div>
            <div class="h-64">
                <canvas id="activityVolumeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Operational Widgets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Quick Actions -->
        <div class="glass-card rounded-3xl p-6 xl:col-span-1">
            <h3 class="text-sm font-semibold text-slate-800">Quick actions</h3>
            <div class="mt-4 space-y-3 text-sm">
                <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="flex items-center gap-3 p-3 rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700 font-semibold hover:bg-emerald-100 transition">
                    <div class="w-8 h-8 rounded-lg bg-emerald-200 flex items-center justify-center text-emerald-800"><i class="fas fa-plus"></i></div>
                    Onboard clinic
                </a>
                <a href="<?= base_url('controlplane/plans/create') ?>" class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 bg-white text-slate-700 font-semibold hover:bg-slate-50 transition">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600"><i class="fas fa-layer-group"></i></div>
                    New plan
                </a>
            </div>
        </div>

        <!-- System Posture -->
        <div class="glass-card rounded-3xl p-6 xl:col-span-1">
            <h3 class="text-sm font-semibold text-slate-800">System posture</h3>
            <div class="mt-4 space-y-4 text-sm text-slate-600">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <span>Tenant isolation</span>
                    <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Enforced</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <span>Legacy API</span>
                    <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Monitor</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Global mode</span>
                    <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">Active</span>
                </div>
            </div>
        </div>

        <!-- Recent Clinics (Span 2) -->
        <div class="glass-card rounded-3xl p-6 md:col-span-2 xl:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800">Recent clinics</h3>
                <a href="<?= base_url('controlplane/clinics') ?>" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="space-y-3 text-sm">
                <?php if (!empty($recent_clinics)): ?>
                    <?php foreach ($recent_clinics as $clinic): ?>
                        <?php $status = $clinic['status'] ?? 'inactive'; ?>
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white/50 px-4 py-3 hover:bg-white transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700">
                                    <?= substr($clinic['name'], 0, 2) ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800"><?= esc($clinic['name']) ?></p>
                                    <p class="text-[10px] text-slate-400"><?= esc(substr($clinic['created_at'], 0, 10)) ?></p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider <?= $status === 'active' ? 'text-emerald-600' : 'text-slate-400' ?>">
                                <?= esc($status) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-slate-400 py-4">No clinics yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dashboardChartData = <?= json_encode($chart_data ?? []) ?>;

        const mrrCtx = document.getElementById('mrrGrowthChart');
        if (mrrCtx && Array.isArray(dashboardChartData.mrr_series)) {
            const mrrLabels = dashboardChartData.mrr_series.map(point => point.label);
            const mrrTotals = dashboardChartData.mrr_series.map(point => point.value);
            const mrrGradient = mrrCtx.getContext('2d').createLinearGradient(0, 0, 0, 220);
            mrrGradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
            mrrGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');

            new Chart(mrrCtx, {
                type: 'line',
                data: {
                    labels: mrrLabels,
                    datasets: [{
                        label: 'MRR ($)',
                        data: mrrTotals,
                        borderColor: '#10b981',
                        backgroundColor: mrrGradient,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#ffffff',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569' },
                        },
                        y: {
                            grid: {
                                borderDash: [4, 4],
                                color: 'rgba(100, 116, 139, 0.15)',
                            },
                            ticks: {
                                callback: value => typeof value === 'number' ? '$' + value.toLocaleString() : value,
                                color: '#475569',
                            },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: context => '$' + Number(context.parsed.y ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2 }),
                            },
                        },
                    },
                },
            });
        }

        const statusCtx = document.getElementById('clinicStatusChart');
        if (statusCtx) {
            const statusLabels = ['Active', 'Suspended', 'Trial'];
            const statusValues = statusLabels.map(label => dashboardChartData.status_breakdown?.[label.toLowerCase()] || 0);

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: ['#10b981', '#f97316', '#6366f1'],
                        borderColor: 'rgba(255,255,255,0.8)',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#475569', boxWidth: 10, boxHeight: 10 },
                        },
                    },
                },
            });
        }

        const activityCtx = document.getElementById('activityVolumeChart');
        if (activityCtx && Array.isArray(dashboardChartData.activity_series)) {
            const activityLabels = dashboardChartData.activity_series.map(point => point.day);
            const activityTotals = dashboardChartData.activity_series.map(point => point.total);

            new Chart(activityCtx, {
                type: 'bar',
                data: {
                    labels: activityLabels,
                    datasets: [{
                        label: 'Events',
                        data: activityTotals,
                        backgroundColor: '#2563eb',
                        borderRadius: 6,
                        maxBarThickness: 24,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569' },
                        },
                        y: {
                            grid: {
                                borderDash: [4, 4],
                                color: 'rgba(148, 163, 184, 0.35)',
                            },
                            ticks: { color: '#475569' },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                    },
                },
            });
        }
    </script>
<?= $this->endSection() ?>
