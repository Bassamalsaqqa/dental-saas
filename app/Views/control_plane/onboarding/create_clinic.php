<?php $title = 'ONBOARDING'; ?>
<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-xs font-black text-slate-500 uppercase tracking-[0.3em]">Control Plane — Onboarding</h1>
        <p class="mt-3 text-sm text-slate-700">Create a clinic, admin user, and subscription in a single transaction.</p>
    </div>
    <div class="bg-white border border-slate-200">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <h2 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">New Clinic</h2>
        </div>
        
        <form action="<?= base_url('controlplane/onboarding/clinic/create') ?>" method="POST" class="p-6 space-y-6">
            <?= csrf_field() ?>

            <!-- Clinic Details -->
            <div>
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Clinic Details</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Clinic Name</label>
                    <input type="text" name="clinic_name" value="<?= old('clinic_name') ?>" required
                           class="w-full rounded border-slate-300 focus:border-slate-500 focus:ring-slate-500 shadow-sm"
                           placeholder="e.g. Springfield Dental">
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Admin User -->
            <div>
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Initial Admin User</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Admin Name</label>
                        <input type="text" name="admin_name" value="<?= old('admin_name') ?>" required
                               class="w-full rounded border-slate-300 focus:border-slate-500 focus:ring-slate-500 shadow-sm"
                               placeholder="e.g. John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Admin Email</label>
                        <input type="email" name="admin_email" value="<?= old('admin_email') ?>" required
                               class="w-full rounded border-slate-300 focus:border-slate-500 focus:ring-slate-500 shadow-sm"
                               placeholder="admin@clinic.com">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" name="admin_password" required minlength="8"
                               class="w-full rounded border-slate-300 focus:border-slate-500 focus:ring-slate-500 shadow-sm"
                               placeholder="********">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Plan -->
            <div>
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Subscription</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Select Plan</label>
                    <select name="plan_id" required class="w-full rounded border-slate-300 focus:border-slate-500 focus:ring-slate-500 shadow-sm">
                        <option value="">-- Choose a Plan --</option>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?= esc($plan['id']) ?>" <?= old('plan_id') == $plan['id'] ? 'selected' : '' ?>>
                                <?= esc($plan['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="<?= base_url('settings') ?>" class="px-4 py-2 text-slate-700 hover:bg-slate-100 rounded">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold uppercase tracking-widest text-[11px]">
                    Create Clinic
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
