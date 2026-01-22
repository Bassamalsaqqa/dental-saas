<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="cp-soft-card rounded-3xl p-6 md:p-8">
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
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Read-only KPIs</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="cp-soft-card rounded-3xl p-5 cp-hero-card">
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
        <div class="cp-soft-card rounded-3xl p-5">
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
        <div class="cp-soft-card rounded-3xl p-5">
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
        <div class="cp-soft-card rounded-3xl p-5">
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

    <div class="grid grid-cols-1 xl:grid-cols-[2fr,1fr] gap-6">
        <div class="cp-soft-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Activity trend</h3>
                    <p class="text-xs text-slate-500 mt-1">Control plane activity volume.</p>
                </div>
                <span class="text-xs uppercase tracking-widest text-slate-400">Last 14 days</span>
            </div>
            <?php
            $series = $activity_series ?? [];
            $maxTotal = 0;
            foreach ($series as $point) {
                if (($point['total'] ?? 0) > $maxTotal) {
                    $maxTotal = (int) $point['total'];
                }
            }
            $maxTotal = max(1, $maxTotal);
            ?>
            <div class="h-56 rounded-2xl bg-gradient-to-br from-emerald-50 via-white to-indigo-50 border border-slate-100 p-4 flex items-end gap-2">
                <?php foreach ($series as $point): ?>
                    <?php $height = 18 + (int) (($point['total'] ?? 0) / $maxTotal * 70); ?>
                    <div class="flex-1 bg-emerald-200/70 rounded-t-2xl" style="height: <?= $height ?>%"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="space-y-6">
            <div class="cp-soft-card rounded-3xl p-6">
                <h3 class="text-sm font-semibold text-slate-800">Quick actions</h3>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-4 text-emerald-700 font-semibold">
                        <i class="fas fa-plus"></i>
                        Onboard clinic
                    </a>
                    <a href="<?= base_url('controlplane/plans/create') ?>" class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-100 bg-white px-3 py-4 text-slate-700 font-semibold">
                        <i class="fas fa-layer-group"></i>
                        New plan
                    </a>
                </div>
            </div>
            <div class="cp-soft-card rounded-3xl p-6">
                <h3 class="text-sm font-semibold text-slate-800">System posture</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>Tenant isolation</span>
                        <span class="text-xs font-semibold text-emerald-700">Enforced</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Legacy API monitor</span>
                        <span class="text-xs font-semibold text-amber-700">Active</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Control plane mode</span>
                        <span class="text-xs font-semibold text-indigo-700">Global only</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="cp-soft-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800">Recent clinics</h3>
                <span class="text-xs text-slate-400">Last 3</span>
            </div>
            <div class="space-y-3 text-sm">
                <?php if (!empty($recent_clinics)): ?>
                    <?php foreach ($recent_clinics as $clinic): ?>
                        <?php $status = $clinic['status'] ?? 'inactive'; ?>
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-4 py-3">
                            <div>
                                <p class="font-semibold text-slate-800"><?= esc($clinic['name']) ?></p>
                                <p class="text-xs text-slate-400">Created <?= esc($clinic['created_at'] ?? 'unknown') ?></p>
                            </div>
                            <span class="text-xs font-semibold <?= $status === 'active' ? 'text-emerald-700' : 'text-slate-500' ?>">
                                <?= esc(ucfirst($status)) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rounded-2xl border border-slate-100 bg-white px-4 py-6 text-center text-slate-400">
                        No clinics yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="cp-soft-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800">Plan requests</h3>
                <span class="text-xs text-slate-400">Queue</span>
            </div>
            <div class="space-y-3 text-sm">
                <?php if (!empty($recent_plan_requests)): ?>
                    <?php foreach ($recent_plan_requests as $request): ?>
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-4 py-3">
                            <div>
                                <p class="font-semibold text-slate-800"><?= esc($request['clinic_name'] ?? 'Clinic') ?></p>
                                <p class="text-xs text-slate-400"><?= esc($request['summary'] ?? 'Plan change requested') ?></p>
                            </div>
                            <span class="text-xs font-semibold <?= ($request['status'] ?? 'pending') === 'approved' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                <?= esc(ucfirst($request['status'] ?? 'pending')) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rounded-2xl border border-slate-100 bg-white px-4 py-6 text-center text-slate-400">
                        No plan requests.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
