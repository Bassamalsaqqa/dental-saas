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

<div class="v3-platform-workspace clinic-onboarding-shell min-h-screen">
    <div class="mx-auto max-w-7xl space-y-4 px-4 py-5 sm:px-5 lg:px-6">
        <section class="clinic-onboarding-hero p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="flex items-start gap-4">
                    <span class="clinic-hero-icon">
                        <i class="fas fa-hospital-user"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold uppercase tracking-[0.28em] cp-accent"><?= esc(lang('V3Platform.controlPlane')) ?></p>
                        <h1 class="mt-1 cp-title font-black tracking-tight v3-text-on-primary"><?= esc(lang('V3Platform.onboardClinic')) ?></h1>
                        <p class="mt-1.5 max-w-3xl text-sm font-semibold leading-5 cp-accent">
                            <?= esc(lang('V3Platform.createTheTenantClinicInitialAdministratorAndStartingSubscriptionPlanInOneControlledWorkflow')) ?>
                        </p>
                    </div>
                </div>
                <div class="grid gap-2.5 sm:grid-cols-3 xl:w-[28rem]">
                    <span class="clinic-hero-chip">
                        <i class="fas fa-building-shield cp-accent"></i>
                        <span class="min-w-0">
                            <span class="block text-[0.65rem] font-black uppercase tracking-wider cp-accent"><?= esc(lang('V3Platform.scope')) ?></span>
                            <span class="block truncate text-sm font-black"><?= esc(lang('V3Platform.tenantSetup')) ?></span>
                        </span>
                    </span>
                    <span class="clinic-hero-chip">
                        <i class="fas fa-user-shield cp-accent"></i>
                        <span class="min-w-0">
                            <span class="block text-[0.65rem] font-black uppercase tracking-wider cp-accent"><?= esc(lang('V3Platform.admin')) ?></span>
                            <span class="block truncate text-sm font-black"><?= esc(lang('V3Platform.initialOwner')) ?></span>
                        </span>
                    </span>
                    <span class="clinic-hero-chip">
                        <i class="fas fa-layer-group cp-accent"></i>
                        <span class="min-w-0">
                            <span class="block text-[0.65rem] font-black uppercase tracking-wider cp-accent"><?= esc(lang('V3Platform.plans')) ?></span>
                            <span class="block truncate text-sm font-black"><?= esc((string) count($plans)) ?> <?= esc(lang('V3Platform.active2')) ?></span>
                        </span>
                    </span>
                </div>
            </div>
        </section>

        <?php if (!empty($errors) || !empty($errorMessage)): ?>
            <div class="cp-radius border v3-border-default v3-bg-alert p-4 text-sm font-bold v3-text-danger">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center cp-radius v3-bg-danger v3-text-on-primary">
                        <i class="fas fa-triangle-exclamation text-sm"></i>
                    </span>
                    <div>
                        <p class="font-black v3-text-danger"><?= esc(lang('V3Platform.reviewTheOnboardingDetails')) ?></p>
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
                            <span class="clinic-section-icon cp-surface border v3-border-default">
                                <i class="fas fa-hospital-alt text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-black v3-text-on-primary"><?= esc(lang('V3Platform.clinicDetails')) ?></h2>
                                <p class="text-sm font-semibold cp-accent"><?= esc(lang('V3Platform.nameTheTenantExactlyAsOperatorsShouldSeeIt')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <label class="block">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black v3-text-ink">
                                <i class="fas fa-signature cp-accent"></i>
                                <span><?= esc(lang('V3Platform.clinicName')) ?></span>
                            </span>
                            <input type="text" name="clinic_name" value="<?= old('clinic_name') ?>"
                                   class="clinic-input v3-input"
                                   placeholder="<?= esc(lang('V3Platform.eGSpringfieldDental'), 'attr') ?>" required>
                        </label>
                    </div>
                </section>

                <section class="clinic-form-card clinic-form-card-admin">
                    <div class="clinic-card-header clinic-card-header-admin px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="clinic-section-icon cp-surface border v3-border-default">
                                <i class="fas fa-user-shield text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-black v3-text-on-primary"><?= esc(lang('V3Platform.initialAdminUser')) ?></h2>
                                <p class="text-sm font-semibold cp-accent"><?= esc(lang('V3Platform.createOrLinkTheFirstClinicAdministrator')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-4 p-4 sm:p-5 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black v3-text-ink">
                                <i class="fas fa-user cp-accent"></i>
                                <span><?= esc(lang('V3Platform.adminName')) ?></span>
                            </span>
                            <input type="text" name="admin_name" value="<?= old('admin_name') ?>"
                                   class="clinic-input v3-input"
                                   placeholder="<?= esc(lang('V3Platform.eGJohnDoe'), 'attr') ?>" required>
                        </label>

                        <label class="block">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black v3-text-ink">
                                <i class="fas fa-envelope cp-accent"></i>
                                <span><?= esc(lang('V3Platform.adminEmail')) ?></span>
                            </span>
                            <input type="email" name="admin_email" value="<?= old('admin_email') ?>"
                                   data-user-email-check
                                   data-password-targets="#admin_password"
                                   data-existing-note-target="#onboarding-email-exists-note"
                                   class="clinic-input v3-input"
                                   placeholder="admin@clinic.com" required>
                        </label>

                        <label class="block md:col-span-2">
                            <span class="mb-2 flex items-center gap-2 text-sm font-black v3-text-ink">
                                <i class="fas fa-key cp-accent"></i>
                                <span><?= esc(lang('V3Platform.password')) ?></span>
                            </span>
                            <input type="password" name="admin_password" id="admin_password"
                                   class="clinic-input v3-input"
                                   placeholder="<?= esc(lang('V3Platform.minimum8Characters'), 'attr') ?>" required minlength="8">
                            <span class="mt-2 flex items-center gap-2 text-xs font-bold v3-text-muted">
                                <i class="fas fa-info-circle"></i>
                                <span><?= esc(lang('V3Platform.shareThisSecurelyWithTheClinicOwner')) ?></span>
                            </span>
                        </label>

                        <p id="onboarding-email-exists-note" class="hidden cp-radius border v3-border-default v3-bg-waiting px-3 py-2 text-xs font-bold v3-text-warning md:col-span-2" role="status">
                            <i class="fas fa-info-circle me-1"></i>
                            <span data-note-text></span>
                        </p>
                    </div>
                </section>

                <section class="clinic-form-card clinic-form-card-plan">
                    <div class="clinic-card-header clinic-card-header-plan px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="clinic-section-icon cp-surface border v3-border-default">
                                <i class="fas fa-layer-group text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-black v3-text-on-primary"><?= esc(lang('V3Platform.subscriptionPlan')) ?></h2>
                                <p class="text-sm font-semibold v3-text-primary"><?= esc(lang('V3Platform.chooseTheStartingLimitsAndBillingPackage')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 p-4 sm:p-5">
                        <?php if (empty($plans)): ?>
                            <div class="cp-radius border v3-border-default v3-bg-waiting p-4 text-sm font-bold v3-text-warning">
                                <i class="fas fa-triangle-exclamation me-2"></i><?= esc(lang('V3Platform.noActivePlansAreAvailable')) ?>
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
                                           class="h-5 w-5 cp-border v3-text-primary v3-focus-control v3-radio"
                                           <?= $selectedPlan === (int) $plan['id'] ? 'checked' : '' ?> required>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <span class="text-lg font-black v3-text-ink"><?= esc($plan['name']) ?></span>
                                        <span class="inline-flex w-fit items-center rounded-full border v3-border-default cp-soft-surface px-3 py-1 text-xs font-black v3-text-primary">
                                            <?= $price !== null ? '$' . esc($price) : 'Custom' ?><?= $duration ? '/' . esc(substr($duration, 0, 2)) : '' ?>
                                        </span>
                                    </span>
                                    <span class="mt-3 flex flex-wrap gap-2 text-xs font-bold v3-text-muted">
                                        <span class="rounded-full border cp-border v3-bg-soft px-2.5 py-1">
                                            <i class="fas fa-users me-1 v3-text-muted"></i><?= esc($limits['patients_active_max'] ?? '0') ?> <?= esc(lang('V3Platform.pts')) ?>
                                        </span>
                                        <span class="rounded-full border cp-border v3-bg-soft px-2.5 py-1">
                                            <i class="fas fa-user-shield me-1 v3-text-muted"></i><?= esc($limits['users_max'] ?? '0') ?> <?= esc(lang('V3Platform.users')) ?>
                                        </span>
                                        <span class="rounded-full border cp-border v3-bg-soft px-2.5 py-1">
                                            <i class="fas fa-hdd me-1 v3-text-muted"></i><?= esc($limits['storage_gb'] ?? '0') ?> GB
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
                    <p class="text-xs font-black uppercase tracking-[0.22em] cp-accent"><?= esc(lang('V3Platform.readyToProvision')) ?></p>
                    <h2 class="mt-1 text-xl font-black v3-text-on-primary"><?= esc(lang('V3Platform.createClinicWorkspace')) ?></h2>
                    <p class="mt-2 text-sm font-semibold leading-5 cp-accent">
                        <?= esc(lang('V3Platform.thisActionCreatesTheClinicTenantLinksTheInitialAdminAndActivatesTheSelectedPlan')) ?>
                    </p>
                    <button type="submit" class="mt-4 inline-flex w-full items-center justify-center gap-2 cp-radius cp-surface px-5 py-3 text-sm font-black cp-accent transition v3-hover-primary focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--primary">
                        <i class="fas fa-magic"></i>
                        <span><?= esc(lang('V3Platform.createClinic')) ?></span>
                    </button>
                    <a href="<?= base_url('controlplane/dashboard') ?>" class="mt-2 inline-flex w-full items-center justify-center cp-radius border cp-border cp-surface px-5 py-2.5 text-sm font-extrabold v3-text-on-primary transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc(lang('V3Platform.cancel')) ?>
                    </a>
                </section>

                <section class="clinic-side-card p-5">
                    <div class="flex items-center gap-3">
                        <span class="clinic-section-icon">
                            <i class="fas fa-list-check text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-black v3-text-ink"><?= esc(lang('V3Platform.checklist')) ?></h3>
                            <p class="text-xs font-bold v3-text-muted"><?= esc(lang('V3Platform.whatThisFlowPrepares')) ?></p>
                        </div>
                    </div>
                    <ul class="mt-4 space-y-2.5 text-sm font-bold v3-text-muted">
                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle v3-text-primary"></i>
                            <span><?= esc(lang('V3Platform.clinicProfileCreation')) ?></span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle v3-text-primary"></i>
                            <span><?= esc(lang('V3Platform.adminAccountSetup')) ?></span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle v3-text-primary"></i>
                            <span><?= esc(lang('V3Platform.planActivation')) ?></span>
                        </li>
                        <li class="flex items-center gap-2.5 v3-text-muted">
                            <i class="far fa-circle"></i>
                            <span><?= esc(lang('V3Platform.welcomeEmailRemainsManual')) ?></span>
                        </li>
                    </ul>
                </section>

                <section class="clinic-side-card p-5">
                    <div class="flex items-center gap-3">
                        <span class="clinic-section-icon">
                            <i class="fas fa-shield-alt text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-black v3-text-ink"><?= esc(lang('V3Platform.defaultPolicy')) ?></h3>
                            <p class="text-xs font-bold v3-text-muted"><?= esc(lang('V3Platform.inheritedPlatformControls')) ?></p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm font-semibold leading-6 v3-text-muted">
                        <?= esc(lang('V3Platform.theClinicInheritsGlobalSecurityDefaultsBillingCadenceAndAuditRetentionPolicies')) ?>
                    </p>
                    <div class="mt-4 cp-radius border cp-border cp-soft-surface px-3 py-2 text-sm font-black cp-accent">
                        <i class="fas fa-circle-check me-2"></i><?= esc(lang('V3Platform.modeProduction')) ?>
                    </div>
                </section>
            </aside>
        </form>
    </div>
</div>
<?= view('components/email_check_script', ['emailCheckUrl' => base_url('controlplane/onboarding/check-email'), 'allowExistingLink' => true]) ?>
<?= $this->endSection() ?>
