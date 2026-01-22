<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
    $plan = $plan ?? null;
    $limits = $limits ?? [];
    $features = $features ?? [];
    $mode = $mode ?? 'create';
    $actionUrl = $mode === 'edit' && !empty($plan['id'])
        ? base_url('controlplane/plans/' . $plan['id'])
        : base_url('controlplane/plans');
?>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Plan Configurator</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2"><?= $mode === 'edit' ? 'Edit plan' : 'Build your own plan' ?></h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Define pricing, duration, and entitlement caps for tenant clinics.
                </p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full <?= ($plan['status'] ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                <?= esc(ucfirst($plan['status'] ?? 'active')) ?>
            </span>
        </div>
    </div>

    <form action="<?= $actionUrl ?>" method="post" class="glass-card rounded-3xl p-6 space-y-8">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Plan Name</label>
                <input type="text" name="name" value="<?= esc($plan['name'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Enter plan name" required>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Price</label>
                <input type="number" name="price" value="<?= esc($limits['price'] ?? 0) ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Enter price" min="0" step="0.01">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Duration</label>
                <select name="duration" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <?php $duration = $limits['duration'] ?? 'monthly'; ?>
                    <option value="monthly" <?= $duration === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    <option value="yearly" <?= $duration === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                    <option value="lifetime" <?= $duration === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Trial Days</label>
                <input type="number" name="trial_days" value="<?= esc($limits['trial_days'] ?? 0) ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="0" min="0">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Patient License</label>
                <input type="number" name="patient_limit" value="<?= esc($limits['patients_active_max'] ?? -1) ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="-1 for unlimited">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">User Seats</label>
                <input type="number" name="user_seats" value="<?= esc($limits['users_max'] ?? -1) ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="-1 for unlimited">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Storage (GB)</label>
                <input type="number" name="storage_gb" value="<?= esc($limits['storage_gb'] ?? -1) ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="-1 for unlimited">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Status</label>
                <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <?php $status = $plan['status'] ?? 'active'; ?>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Export Quota (per month)</label>
                <input type="number" name="export_quota" value="<?= esc($limits['exports'] ?? -1) ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="-1 for unlimited">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Email Notification Quota (per month)</label>
                <input type="number" name="email_quota" value="<?= esc($limits['notifications_email'] ?? -1) ?>" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="-1 for unlimited">
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Feature Toggles</label>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-600">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[appointments]" <?= !empty($features['appointments']) ? 'checked' : '' ?>>
                    Appointments
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[finance]" <?= !empty($features['finance']) ? 'checked' : '' ?>>
                    Finance
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[inventory]" <?= !empty($features['inventory']) ? 'checked' : '' ?>>
                    Inventory
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[reports]" <?= !empty($features['reports']) ? 'checked' : '' ?>>
                    Reports
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[email_templates]" <?= !empty($features['email_templates']) ? 'checked' : '' ?>>
                    Email Templates
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[audit_exports]" <?= !empty($features['audit_exports']) ? 'checked' : '' ?>>
                    Audit Exports
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[chairside_notes]" <?= !empty($features['chairside_notes']) ? 'checked' : '' ?>>
                    Chairside Notes
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[claims_hub]" <?= !empty($features['claims_hub']) ? 'checked' : '' ?>>
                    Claims Hub
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[exports][enabled]" <?= !empty($features['exports']['enabled']) ? 'checked' : '' ?>>
                    Export tooling
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[notifications][email][enabled]" <?= !empty($features['notifications']['email']['enabled']) ? 'checked' : '' ?>>
                    Email notifications
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="features[notifications][sms][enabled]" <?= !empty($features['notifications']['sms']['enabled']) ? 'checked' : '' ?>>
                    SMS notifications
                </label>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Description</label>
            <textarea name="description" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" rows="4" placeholder="Plan description"><?= esc($limits['description'] ?? '') ?></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= base_url('controlplane/plans') ?>" class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600">
                Back
            </a>
            <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold">
                <?= $mode === 'edit' ? 'Update Plan' : 'Save Plan' ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
