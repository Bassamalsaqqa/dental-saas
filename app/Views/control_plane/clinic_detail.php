<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
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
        <div class="glass-card rounded-3xl p-6">
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

        <div class="glass-card rounded-3xl p-6">
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

    <div class="glass-card rounded-3xl p-6 space-y-6">
        <h2 class="text-sm font-semibold text-slate-800">Lifecycle actions</h2>

        <?php
            $startValue = !empty($subscription['start_at']) ? date('Y-m-d\\TH:i', strtotime($subscription['start_at'])) : '';
            $endValue = !empty($subscription['end_at']) ? date('Y-m-d\\TH:i', strtotime($subscription['end_at'])) : '';
        ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-slate-600">
            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/start') ?>" method="post" class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-xs uppercase tracking-widest text-emerald-700 font-semibold">Start subscription</h3>
                <div>
                    <label class="text-xs text-slate-500">Plan</label>
                    <select name="plan_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                        <option value="">Select plan</option>
                        <?php foreach (($plans ?? []) as $plan): ?>
                            <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Duration</label>
                    <select name="duration" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
                <button class="w-full px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Start</button>
            </form>

            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/extend') ?>" method="post" class="rounded-2xl border border-blue-100 bg-blue-50/40 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-xs uppercase tracking-widest text-blue-700 font-semibold">Extend subscription</h3>
                <div>
                    <label class="text-xs text-slate-500">Duration</label>
                    <select name="duration" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
                <button class="w-full px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold">Extend</button>
            </form>

            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/update-dates') ?>" method="post" class="rounded-2xl border border-slate-100 bg-slate-50/40 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-xs uppercase tracking-widest text-slate-600 font-semibold">Adjust dates</h3>
                <div>
                    <label class="text-xs text-slate-500">Start</label>
                    <input type="datetime-local" name="start_at" value="<?= esc($startValue) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="text-xs text-slate-500">End</label>
                    <input type="datetime-local" name="end_at" value="<?= esc($endValue) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <button class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white text-xs font-semibold">Save Dates</button>
            </form>

            <div class="rounded-2xl border border-rose-100 bg-rose-50/40 p-4 space-y-3">
                <h3 class="text-xs uppercase tracking-widest text-rose-700 font-semibold">Terminate clinic</h3>
                <p class="text-xs text-slate-500">Disables clinic access and deactivates subscription.</p>
                <?php if (($clinic['status'] ?? 'inactive') === 'active'): ?>
                    <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="w-full px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold">Suspend Clinic</button>
                    </form>
                <?php else: ?>
                    <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="w-full px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Reactivate Clinic</button>
                    </form>
                <?php endif; ?>
                <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/cancel') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="w-full px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold">Cancel Subscription</button>
                </form>
                <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/terminate') ?>" method="post" class="mt-2">
                    <?= csrf_field() ?>
                    <button class="w-full px-3 py-2 rounded-lg bg-rose-600 text-white text-xs font-semibold">Terminate Clinic</button>
                </form>
            </div>
        </div>

        <p class="text-xs text-slate-500">All actions are audited and applied immediately.</p>
    </div>
</div>
<?= $this->endSection() ?>
