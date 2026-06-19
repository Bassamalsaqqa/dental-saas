<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$plans = is_array($plans ?? null) ? $plans : [];
$selectedPlan = (int) (old('plan_id') ?? 0);
$errors = session('errors') ?? [];
$errorMessage = session('error');
?>

<style>
    .clinic-onboarding-shell {
        background:
            radial-gradient(circle at 4% 0%, rgba(37,99,235,0.13), transparent 28%),
            radial-gradient(circle at 96% 8%, rgba(16,185,129,0.13), transparent 30%),
            linear-gradient(135deg, rgba(248,250,252,0.99), rgba(239,246,255,0.94));
    }

    .clinic-onboarding-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.45rem;
        color: #fff;
        background:
            radial-gradient(circle at 12% 12%, rgba(255,255,255,0.20), transparent 31%),
            radial-gradient(circle at 88% 20%, rgba(94,234,212,0.22), transparent 28%),
            linear-gradient(135deg, #0f3f90 0%, #1d4ed8 54%, #0f766e 100%);
        border: 1px solid rgba(203,213,225,0.58);
        box-shadow: 0 26px 64px -44px rgba(15,23,42,0.78);
    }

    [dir="rtl"] .clinic-onboarding-hero {
        background:
            radial-gradient(circle at 88% 12%, rgba(255,255,255,0.20), transparent 31%),
            radial-gradient(circle at 12% 20%, rgba(94,234,212,0.22), transparent 28%),
            linear-gradient(225deg, #0f3f90 0%, #1d4ed8 54%, #0f766e 100%);
    }

    .clinic-onboarding-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(120deg, rgba(255,255,255,0.08), transparent 46%);
    }

    .clinic-onboarding-hero > * {
        position: relative;
        z-index: 1;
    }

    .clinic-hero-icon {
        display: inline-flex;
        width: 4.15rem;
        height: 4.15rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 1.42rem;
        color: #fff;
        font-size: 1.78rem;
        background: linear-gradient(145deg, rgba(255,255,255,0.25), rgba(153,246,228,0.18));
        border: 1px solid rgba(255,255,255,0.25);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 20px 40px -28px rgba(15,23,42,0.78);
    }

    .clinic-hero-chip {
        display: inline-flex;
        min-height: 2.42rem;
        align-items: center;
        gap: 0.55rem;
        border-radius: 999px;
        padding: 0.48rem 0.76rem;
        color: #fff;
        background: rgba(255,255,255,0.13);
        border: 1px solid rgba(255,255,255,0.18);
        backdrop-filter: blur(12px);
    }

    .clinic-form-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.12rem;
        border: 1px solid rgba(255,255,255,0.84);
        background:
            radial-gradient(circle at 0% 0%, rgba(37,99,235,0.10), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,0.99), rgba(239,246,255,0.94));
        box-shadow: 0 20px 48px -40px rgba(15,23,42,0.52), 0 1px 0 rgba(255,255,255,0.94) inset;
    }

    .clinic-form-card-admin {
        background:
            radial-gradient(circle at 0% 0%, rgba(124,58,237,0.13), transparent 31%),
            linear-gradient(135deg, rgba(255,255,255,0.99), rgba(245,243,255,0.93));
    }

    .clinic-form-card-plan {
        background:
            radial-gradient(circle at 0% 0%, rgba(5,150,105,0.14), transparent 31%),
            linear-gradient(135deg, rgba(255,255,255,0.99), rgba(209,250,229,0.88));
    }

    .clinic-card-header {
        color: #fff;
        border-bottom: 1px solid rgba(255,255,255,0.20);
        background:
            radial-gradient(circle at 8% 0%, rgba(255,255,255,0.18), transparent 36%),
            linear-gradient(135deg, #1d4ed8, #0f766e);
    }

    .clinic-card-header-admin {
        background:
            radial-gradient(circle at 8% 0%, rgba(255,255,255,0.18), transparent 36%),
            linear-gradient(135deg, #6d28d9, #be185d);
    }

    .clinic-card-header-plan {
        background:
            radial-gradient(circle at 8% 0%, rgba(255,255,255,0.20), transparent 36%),
            linear-gradient(135deg, #047857, #0f766e);
    }

    .clinic-section-icon {
        display: inline-flex;
        width: 2.35rem;
        height: 2.35rem;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 0.9rem;
        color: #fff;
        box-shadow: 0 16px 28px -22px rgba(15,23,42,0.75);
    }

    .clinic-input {
        width: 100%;
        border-radius: 0.95rem;
        border: 1px solid rgba(203,213,225,0.95);
        background: rgba(255,255,255,0.95);
        padding: 0.82rem 0.95rem;
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 700;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
        transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease;
    }

    .clinic-input:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 4px rgba(37,99,235,0.14), inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .clinic-plan-option {
        position: relative;
        display: flex;
        gap: 1rem;
        border-radius: 1rem;
        border: 1px solid rgba(148,163,184,0.38);
        background:
            radial-gradient(circle at 0% 0%, rgba(16,185,129,0.10), transparent 35%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(240,253,244,0.92));
        padding: 0.95rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.92);
        transition: all 0.16s ease;
    }

    .clinic-plan-option:hover {
        border-color: rgba(16,185,129,0.62);
        transform: translateY(-1px);
        box-shadow: 0 16px 34px -30px rgba(15,23,42,0.42), inset 0 1px 0 rgba(255,255,255,0.92);
    }

    .clinic-side-card {
        border-radius: 1.12rem;
        border: 1px solid rgba(255,255,255,0.84);
        background:
            radial-gradient(circle at 0% 0%, rgba(37,99,235,0.11), transparent 33%),
            linear-gradient(135deg, rgba(255,255,255,0.99), rgba(248,250,252,0.96));
        box-shadow: 0 18px 42px -38px rgba(15,23,42,0.48), 0 1px 0 rgba(255,255,255,0.94) inset;
    }

    .clinic-submit-card {
        border-radius: 1.12rem;
        color: #fff;
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
            linear-gradient(135deg, #1d4ed8, #0f766e);
        box-shadow: 0 22px 44px -34px rgba(15,23,42,0.72);
    }
</style>

<div class="clinic-onboarding-shell min-h-screen">
    <div class="mx-auto max-w-7xl space-y-4 px-4 py-5 sm:px-5 lg:px-6">
        <section class="clinic-onboarding-hero p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="flex items-start gap-4">
                    <span class="clinic-hero-icon">
                        <i class="fas fa-hospital-user"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-blue-100">Control Plane</p>
                        <h1 class="mt-1 text-3xl font-black tracking-tight text-white">Onboard Clinic</h1>
                        <p class="mt-1.5 max-w-3xl text-sm font-semibold leading-5 text-blue-50/90">
                            Create the tenant clinic, initial administrator, and starting subscription plan in one controlled workflow.
                        </p>
                    </div>
                </div>
                <div class="grid gap-2.5 sm:grid-cols-3 xl:w-[28rem]">
                    <span class="clinic-hero-chip">
                        <i class="fas fa-building-shield text-blue-100"></i>
                        <span class="min-w-0">
                            <span class="block text-[0.65rem] font-black uppercase tracking-wider text-blue-100">Scope</span>
                            <span class="block truncate text-sm font-black">Tenant setup</span>
                        </span>
                    </span>
                    <span class="clinic-hero-chip">
                        <i class="fas fa-user-shield text-blue-100"></i>
                        <span class="min-w-0">
                            <span class="block text-[0.65rem] font-black uppercase tracking-wider text-blue-100">Admin</span>
                            <span class="block truncate text-sm font-black">Initial owner</span>
                        </span>
                    </span>
                    <span class="clinic-hero-chip">
                        <i class="fas fa-layer-group text-blue-100"></i>
                        <span class="min-w-0">
                            <span class="block text-[0.65rem] font-black uppercase tracking-wider text-blue-100">Plans</span>
                            <span class="block truncate text-sm font-black"><?= esc((string) count($plans)) ?> active</span>
                        </span>
                    </span>
                </div>
            </div>
        </section>

        <?php if (!empty($errors) || !empty($errorMessage)): ?>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-600 text-white">
                        <i class="fas fa-triangle-exclamation text-sm"></i>
                    </span>
                    <div>
                        <p class="font-black text-rose-800">Review the onboarding details</p>
                        <?php if (!empty($errorMessage)): ?>
                            <p class="mt-1"><?= esc($errorMessage) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($errors)): ?>
                            <ul class="mt-2 list-disc space-y-1 ps-5">
                                <?php foreach ($errors as $message): ?>
                                    <li><?= esc((string) $message) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('controlplane/onboarding/clinic/create') ?>" method="POST" class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <?= csrf_field() ?>

            <div class="space-y-4">
                <section class="clinic-form-card">
                    <div class="clinic-card-header px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="clinic-section-icon bg-white/18 ring-1 ring-white/22">
                                <i class="fas fa-hospital-alt text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-black text-white">Clinic Details</h2>
                                <p class="text-sm font-semibold text-blue-50/88">Name the tenant exactly as operators should see it.</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <label class="block">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black text-slate-700">
                                <i class="fas fa-signature text-blue-600"></i>
                                <span>Clinic Name</span>
                            </span>
                            <input type="text" name="clinic_name" value="<?= old('clinic_name') ?>"
                                   class="clinic-input"
                                   placeholder="e.g. Springfield Dental" required>
                        </label>
                    </div>
                </section>

                <section class="clinic-form-card clinic-form-card-admin">
                    <div class="clinic-card-header clinic-card-header-admin px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="clinic-section-icon bg-white/18 ring-1 ring-white/22">
                                <i class="fas fa-user-shield text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-black text-white">Initial Admin User</h2>
                                <p class="text-sm font-semibold text-violet-50/88">Create or link the first clinic administrator.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-4 p-4 sm:p-5 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black text-slate-700">
                                <i class="fas fa-user text-violet-600"></i>
                                <span>Admin Name</span>
                            </span>
                            <input type="text" name="admin_name" value="<?= old('admin_name') ?>"
                                   class="clinic-input"
                                   placeholder="e.g. John Doe" required>
                        </label>

                        <label class="block">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black text-slate-700">
                                <i class="fas fa-envelope text-violet-600"></i>
                                <span>Admin Email</span>
                            </span>
                            <input type="email" name="admin_email" value="<?= old('admin_email') ?>"
                                   data-user-email-check
                                   data-password-targets="#admin_password"
                                   data-existing-note-target="#onboarding-email-exists-note"
                                   class="clinic-input"
                                   placeholder="admin@clinic.com" required>
                        </label>

                        <label class="block md:col-span-2">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black text-slate-700">
                                <i class="fas fa-key text-violet-600"></i>
                                <span>Password</span>
                            </span>
                            <input type="password" name="admin_password" id="admin_password"
                                   class="clinic-input"
                                   placeholder="Minimum 8 characters" required minlength="8">
                            <span class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-500">
                                <i class="fas fa-info-circle"></i>
                                <span>Share this securely with the clinic owner.</span>
                            </span>
                        </label>

                        <p id="onboarding-email-exists-note" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 md:col-span-2" role="status">
                            <i class="fas fa-info-circle me-1"></i>
                            <span data-note-text></span>
                        </p>
                    </div>
                </section>

                <section class="clinic-form-card clinic-form-card-plan">
                    <div class="clinic-card-header clinic-card-header-plan px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="clinic-section-icon bg-white/18 ring-1 ring-white/22">
                                <i class="fas fa-layer-group text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-black text-white">Subscription Plan</h2>
                                <p class="text-sm font-semibold text-emerald-50/88">Choose the starting limits and billing package.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 p-4 sm:p-5">
                        <?php if (empty($plans)): ?>
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800">
                                <i class="fas fa-triangle-exclamation me-2"></i>No active plans are available.
                            </div>
                        <?php endif; ?>

                        <?php foreach ($plans as $plan): ?>
                            <?php
                                $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
                                $price = $limits['price'] ?? null;
                                $duration = $limits['duration'] ?? null;
                            ?>
                            <label class="clinic-plan-option cursor-pointer">
                                <span class="mt-1 flex h-6 w-6 items-center justify-center">
                                    <input type="radio" name="plan_id" value="<?= esc($plan['id']) ?>"
                                           class="h-5 w-5 border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                           <?= $selectedPlan === (int) $plan['id'] ? 'checked' : '' ?> required>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <span class="text-lg font-black text-slate-950"><?= esc($plan['name']) ?></span>
                                        <span class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">
                                            <?= $price !== null ? '$' . esc($price) : 'Custom' ?><?= $duration ? '/' . esc(substr($duration, 0, 2)) : '' ?>
                                        </span>
                                    </span>
                                    <span class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-600">
                                        <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1">
                                            <i class="fas fa-users me-1 text-slate-400"></i><?= esc($limits['patients_active_max'] ?? '0') ?> Pts
                                        </span>
                                        <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1">
                                            <i class="fas fa-user-shield me-1 text-slate-400"></i><?= esc($limits['users_max'] ?? '0') ?> Users
                                        </span>
                                        <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1">
                                            <i class="fas fa-hdd me-1 text-slate-400"></i><?= esc($limits['storage_gb'] ?? '0') ?> GB
                                        </span>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="clinic-submit-card p-4 sm:p-5">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-100">Ready to provision</p>
                    <h2 class="mt-1 text-xl font-black text-white">Create clinic workspace</h2>
                    <p class="mt-2 text-sm font-semibold leading-5 text-blue-50/88">
                        This action creates the clinic tenant, links the initial admin, and activates the selected plan.
                    </p>
                    <button type="submit" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-blue-800 shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                        <i class="fas fa-magic"></i>
                        <span>Create Clinic</span>
                    </button>
                    <a href="<?= base_url('controlplane/dashboard') ?>" class="mt-2 inline-flex w-full items-center justify-center rounded-2xl border border-white/24 bg-white/14 px-5 py-2.5 text-sm font-extrabold text-white transition hover:bg-white/20">
                        Cancel
                    </a>
                </section>

                <section class="clinic-side-card p-5">
                    <div class="flex items-center gap-3">
                        <span class="clinic-section-icon bg-gradient-to-br from-emerald-600 to-teal-700">
                            <i class="fas fa-list-check text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-slate-950">Checklist</h3>
                            <p class="text-xs font-bold text-slate-500">What this flow prepares.</p>
                        </div>
                    </div>
                    <ul class="mt-4 space-y-2.5 text-sm font-bold text-slate-600">
                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle text-emerald-600"></i>
                            <span>Clinic profile creation</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle text-emerald-600"></i>
                            <span>Admin account setup</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle text-emerald-600"></i>
                            <span>Plan activation</span>
                        </li>
                        <li class="flex items-center gap-2.5 text-slate-400">
                            <i class="far fa-circle"></i>
                            <span>Welcome email remains manual</span>
                        </li>
                    </ul>
                </section>

                <section class="clinic-side-card p-5">
                    <div class="flex items-center gap-3">
                        <span class="clinic-section-icon bg-gradient-to-br from-blue-600 to-indigo-700">
                            <i class="fas fa-shield-alt text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-slate-950">Default Policy</h3>
                            <p class="text-xs font-bold text-slate-500">Inherited platform controls.</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm font-semibold leading-6 text-slate-600">
                        The clinic inherits global security defaults, billing cadence, and audit retention policies.
                    </p>
                    <div class="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-black text-blue-800">
                        <i class="fas fa-circle-check me-2"></i>Mode: Production
                    </div>
                </section>
            </aside>
        </form>
    </div>
</div>
<?= view('components/email_check_script', ['emailCheckUrl' => base_url('users/check-email')]) ?>
<?= $this->endSection() ?>
