<?php $title = 'Clinic Onboarding'; ?>
<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl space-y-8">
    <div class="cp-soft-card rounded-3xl p-6 md:p-8 cp-hero-card">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Onboarding Studio</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Create a clinic, owner, and plan in one flow.</h1>
                <p class="mt-3 text-sm text-slate-700 max-w-2xl">
                    This will provision the clinic record, assign the first admin, and activate a subscription in a
                    single audited transaction.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-white/70 rounded-2xl px-4 py-3 border border-white/80">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Step</p>
                    <p class="text-lg font-semibold">1 of 3</p>
                </div>
                <div class="bg-white/70 rounded-2xl px-4 py-3 border border-white/80">
                    <p class="text-xs uppercase tracking-widest text-slate-400">SLA</p>
                    <p class="text-lg font-semibold">Live today</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.3fr,0.7fr] gap-6">
        <div class="cp-soft-card rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-white/70">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">New Clinic</h2>
                        <p class="text-sm text-slate-600 mt-2">All fields are required. The subscription activates immediately.</p>
                    </div>
                    <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full">Ready</span>
                </div>
            </div>

            <form action="<?= base_url('controlplane/onboarding/clinic/create') ?>" method="POST" class="p-6 space-y-6">
                <?= csrf_field() ?>

                <div class="rounded-2xl border border-slate-200 p-5 bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Clinic Details</h3>
                        <span class="text-xs text-slate-400">Step 1</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Clinic Name</label>
                        <input type="text" name="clinic_name" value="<?= old('clinic_name') ?>" required
                               class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                               placeholder="e.g. Springfield Dental">
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-5 bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Initial Admin User</h3>
                        <span class="text-xs text-slate-400">Step 2</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Admin Name</label>
                            <input type="text" name="admin_name" value="<?= old('admin_name') ?>" required
                                   class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                                   placeholder="e.g. John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Admin Email</label>
                            <input type="email" name="admin_email" value="<?= old('admin_email') ?>" required
                                   class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                                   placeholder="admin@clinic.com">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                            <input type="password" name="admin_password" required minlength="8"
                                   class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                                   placeholder="Minimum 8 characters">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-5 bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Subscription</h3>
                        <span class="text-xs text-slate-400">Step 3</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Select Plan</label>
                            <select name="plan_id" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <option value="">-- Choose a Plan --</option>
                                <?php foreach ($plans as $plan): ?>
                                    <option value="<?= esc($plan['id']) ?>" <?= old('plan_id') == $plan['id'] ? 'selected' : '' ?>>
                                        <?= esc($plan['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Activation Date</label>
                            <input type="date" name="start_date" value="<?= old('start_date') ?>"
                                   class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
                    <p class="text-xs text-slate-500">This action is audited and cannot be undone.</p>
                    <div class="flex gap-2">
                        <a href="<?= base_url('controlplane/dashboard') ?>" class="px-4 py-2 text-slate-700 hover:bg-slate-100 rounded-lg">Cancel</a>
                        <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-semibold uppercase tracking-widest text-[11px] rounded-lg">
                            Create Clinic
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="cp-soft-card rounded-3xl p-6">
                <h3 class="text-sm font-semibold text-slate-800">Onboarding checklist</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> Clinic profile created</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> Admin role assigned</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> Subscription activated</li>
                    <li class="flex items-center gap-2"><i class="fas fa-circle text-slate-300"></i> Welcome email dispatched</li>
                </ul>
            </div>

            <div class="cp-soft-card rounded-3xl p-6">
                <h3 class="text-sm font-semibold text-slate-800">Default policy</h3>
                <p class="text-sm text-slate-600 mt-3">
                    The new clinic inherits global security defaults, billing cadence, and audit retention.
                    Override policies later from the clinic detail page.
                </p>
                <div class="mt-5 flex items-center gap-3">
                    <span class="text-xs uppercase tracking-widest text-slate-400">Mode</span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Production</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


