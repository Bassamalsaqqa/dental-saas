<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="cp-soft-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Clinic Profile</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2"><?= esc($clinic['name']) ?></h1>
                <p class="mt-3 text-sm text-slate-600">Clinic overview, subscription status, and lifecycle controls.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                Status: <?= esc(ucfirst($clinic['status'] ?? 'inactive')) ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.2fr,0.8fr] gap-6">
        <div class="cp-soft-card rounded-3xl p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Clinic details</h2>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Clinic ID</span>
                    <span class="font-semibold text-slate-800"><?= esc($clinic['id']) ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Slug</span>
                    <span><?= esc($clinic['slug'] ?? '--') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Created</span>
                    <span><?= esc($clinic['created_at'] ?? '--') ?></span>
                </div>
            </div>
        </div>

        <div class="cp-soft-card rounded-3xl p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Subscription</h2>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Plan</span>
                    <span class="font-semibold text-slate-800"><?= esc($plan_name ?? 'Unassigned') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Status</span>
                    <span><?= esc($subscription['status'] ?? 'none') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Start</span>
                    <span><?= esc($subscription['start_at'] ?? '--') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">End</span>
                    <span><?= esc($subscription['end_at'] ?? '--') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="cp-soft-card rounded-3xl p-6">
        <h2 class="text-sm font-semibold text-slate-800 mb-4">Lifecycle actions</h2>
        <div class="flex flex-wrap gap-3">
            <button class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-semibold cursor-not-allowed" disabled>
                Start Subscription
            </button>
            <button class="px-4 py-2 rounded-lg bg-blue-50 text-blue-700 text-sm font-semibold cursor-not-allowed" disabled>
                Upgrade Plan
            </button>
            <button class="px-4 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-semibold cursor-not-allowed" disabled>
                Suspend Clinic
            </button>
            <button class="px-4 py-2 rounded-lg bg-rose-50 text-rose-700 text-sm font-semibold cursor-not-allowed" disabled>
                Terminate Clinic
            </button>
        </div>
        <p class="text-xs text-slate-500 mt-3">Lifecycle actions will be wired after Phase 1 backend readiness.</p>
    </div>
</div>
<?= $this->endSection() ?>
