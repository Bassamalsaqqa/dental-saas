<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$waPolicy = is_array($wa_policy ?? null) ? $wa_policy : [];
$waPolicyToggles = is_array($wa_policy_toggles ?? null) ? $wa_policy_toggles : [];
$waTriggerCatalog = is_array($wa_trigger_catalog ?? null) ? $wa_trigger_catalog : [];
$waGlobalTemplates = is_array($wa_global_templates ?? null) ? $wa_global_templates : [];
$waGlobalTriggerTemplates = is_array($waGlobalTemplates['trigger_templates'] ?? null) ? $waGlobalTemplates['trigger_templates'] : [];
$waGlobalTriggerToggles = is_array($waGlobalTemplates['trigger_toggles'] ?? null) ? $waGlobalTemplates['trigger_toggles'] : [];
$clinicSettingsSnapshot = is_array($clinic_settings_snapshot ?? null) ? $clinic_settings_snapshot : [];
?>
<style>
    .wa-rollout-shell {
        border: 1px solid var(--denta-taupe-border);
        border-radius: 1rem;
        background:var(--denta-alabaster);
        box-shadow:none;
        overflow: hidden;
    }
    .wa-rollout-head {
        padding: 1.5rem;
        border-bottom: 1px solid var(--denta-taupe-border);
        background: var(--denta-alabaster);
    }
    .wa-rollout-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid var(--denta-taupe-border);
        border-radius: 1rem;
        background:var(--denta-alabaster);
    }
    .wa-rollout-row.is-disabled {
        background: var(--denta-alabaster);
        border-style: dashed;
    }
    .wa-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: .3rem .7rem;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
</style>
<div class="v3-platform-workspace px-6 py-8">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="cp-title font-bold v3-text-ink mb-3"><?= esc(lang('V3Platform.clinicOverview')) ?></h1>
                <p class="v3-text-muted text-lg"><?= esc($clinic['name']) ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('controlplane/clinics') ?>" class="inline-flex items-center px-4 py-2 v3-bg-canvas v3-text-on-primary text-sm font-medium rounded-lg v3-bg-forest focus:outline-none focus:ring-2 v3-focus-control focus:ring-offset-2 transition-all duration-200 v3-btn v3-btn--secondary">
                    <i class="fas fa-arrow-left mr-2"></i><?= esc(lang('V3Platform.backToClinics')) ?>
                </a>
                <form action="<?= base_url('controlplane/impersonate') ?>" method="post" class="inline-block">
                    <?= csrf_field() ?>
                    <input type="hidden" name="clinic_id" value="<?= esc($clinic['id']) ?>">
                    <button type="submit" class="inline-flex items-center px-4 py-2 v3-text-on-primary text-sm font-medium rounded-lg focus:outline-none focus:ring-2 v3-focus-control focus:ring-offset-2 transition-all duration-200 v3-btn v3-btn--primary">
                        <i class="fas fa-sign-in-alt mr-2"></i><?= esc(lang('V3Platform.loginAs')) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 v3-bg-paid border v3-border-default cp-radius p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle v3-text-primary text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium v3-text-primary"><?= esc(lang('V3Platform.success')) ?></h3>
                    <div class="mt-2 text-sm v3-text-primary">
                        <p><?= session()->getFlashdata('success') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 v3-bg-alert border v3-border-default cp-radius p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle v3-text-danger text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium v3-text-danger"><?= esc(lang('V3Platform.error')) ?></h3>
                    <div class="mt-2 text-sm v3-text-danger">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <!-- Clinic Profile Card -->
        <div class="lg:col-span-1">
            <div class="cp-surface cp-radius border cp-border overflow-hidden">
                <!-- Clinic Header -->
                <div class="cp-panel-padding v3-text-on-primary">
                    <div class="text-center">
                        <div class="w-24 h-24 cp-surface cp-radius flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-hospital v3-text-on-primary cp-title"></i>
                        </div>
                        <h2 class="text-2xl font-bold"><?= esc($clinic['name']) ?></h2>
                        <p class="v3-text-primary text-lg"><?= esc(lang('V3Platform.id')) ?> <?= esc($clinic['id']) ?></p>

                        <div class="mt-4">
                            <?php
                                $status = $clinic['status'] ?? 'inactive';
                                $statusColors = [
                                    'active' => 'v3-bg-primary v3-text-primary',
                                    'suspended' => 'v3-bg-warning v3-text-warning',
                                    'inactive' => 'v3-bg-canvas v3-text-ink'
                                ];
                                $badgeClass = $statusColors[$status] ?? 'v3-bg-canvas v3-text-ink';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?= $badgeClass ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Clinic Info -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full cp-soft-surface flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-globe cp-accent text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium v3-text-muted"><?= esc(lang('V3Platform.slug')) ?></p>
                                <p class="text-sm font-semibold v3-text-ink truncate"><?= esc($clinic['slug'] ?? '--') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full cp-soft-surface flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar cp-accent text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium v3-text-muted"><?= esc(lang('V3Platform.created')) ?></p>
                                <p class="text-sm font-semibold v3-text-ink"><?= esc($clinic['created_at'] ?? '--') ?></p>
                            </div>
                        </div>

                        <!-- Clinic Administrator Card -->
                        <div class="pt-4 border-t cp-border">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-bold v3-text-muted uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-user-shield cp-accent"></i>
                                    <span><?= esc(lang('UI.clinicAdminTitle')) ?></span>
                                </h4>
                                <?php
                                $statusClass = 'v3-bg-soft v3-text-ink v3-border-default';
                                $statusIcon = 'fa-question-circle';
                                $statusText = lang('UI.clinicAdminUnassigned');
                                if (!empty($admin_info['is_fallback'])) {
                                    $statusClass = 'v3-bg-waiting v3-text-warning v3-border-default';
                                    $statusIcon = 'fa-clock';
                                    $statusText = lang('UI.legacyAdminCandidate');
                                } elseif (($admin_info['status'] ?? '') === 'healthy') {
                                    $statusClass = 'v3-bg-paid v3-text-primary v3-border-default';
                                    $statusIcon = 'fa-check-circle';
                                    $statusText = lang('UI.clinicAdminHealthy');
                                } elseif (($admin_info['status'] ?? '') === 'disabled_user') {
                                    $statusClass = 'v3-bg-alert v3-text-danger v3-border-default';
                                    $statusIcon = 'fa-user-slash';
                                    $statusText = lang('UI.clinicAdminDisabledUser');
                                } elseif (($admin_info['status'] ?? '') === 'inactive_membership') {
                                    $statusClass = 'v3-bg-waiting v3-text-warning v3-border-default';
                                    $statusIcon = 'fa-id-badge';
                                    $statusText = lang('UI.clinicAdminInactiveMembership');
                                } elseif (($admin_info['status'] ?? '') === 'missing_authority') {
                                    $statusClass = 'v3-bg-waiting v3-text-warning v3-border-default';
                                    $statusIcon = 'fa-key';
                                    $statusText = lang('UI.clinicAdminMissingAuthority');
                                }
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border <?= $statusClass ?>">
                                    <i class="fas <?= $statusIcon ?> me-1 text-[10px]"></i>
                                    <?= esc($statusText) ?>
                                </span>
                            </div>

                            <?php if (!empty($admin_info['issues'])): ?>
                                <div class="mb-3 p-2.5 rounded-lg v3-bg-waiting border v3-border-default v3-text-warning text-xs">
                                    <div class="font-semibold flex items-center gap-1 mb-1">
                                        <i class="fas fa-exclamation-triangle v3-text-warning"></i>
                                        <span><?= esc($statusText) ?></span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 v3-text-warning text-[11px]">
                                        <?php if (!empty($admin_info['issue_codes'])): ?>
                                            <?php foreach ($admin_info['issue_codes'] as $issueCode): ?>
                                                <li><?= esc(lang('UI.' . $issueCode)) ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <?php foreach ($admin_info['issues'] as $issue): ?>
                                                <li><?= esc($issue) ?></li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($admin_info['admin_user'])): ?>
                                <?php $u = $admin_info['admin_user']; ?>
                                <div class="space-y-2 cp-surface p-3 cp-radius border cp-border">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold v3-text-ink truncate">
                                                <?= esc(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ($u['username'] ?? 'User #' . $u['id'])) ?>
                                            </p>
                                            <p class="text-xs v3-text-muted truncate flex items-center gap-1">
                                                <i class="fas fa-envelope v3-text-muted text-[10px]"></i>
                                                <span><?= esc($u['email'] ?? '--') ?></span>
                                            </p>
                                            <?php if (!empty($u['phone'])): ?>
                                                <p class="text-xs v3-text-muted truncate flex items-center gap-1 mt-0.5">
                                                    <i class="fas fa-phone v3-text-muted text-[10px]"></i>
                                                    <span><?= esc($u['phone']) ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Status Badges Grid -->
                                    <div class="pt-2 border-t cp-border grid grid-cols-3 gap-1.5 text-center">
                                        <div class="p-1 rounded cp-surface border cp-border">
                                            <div class="text-[10px] v3-text-muted font-medium"><?= esc(lang('UI.clinicAdminAccountActive')) ?></div>
                                            <div class="mt-0.5 text-xs font-bold <?= ($admin_info['account_active'] ?? false) ? 'v3-text-primary' : 'v3-text-danger' ?>">
                                                <i class="fas <?= ($admin_info['account_active'] ?? false) ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                                <span><?= ($admin_info['account_active'] ?? false) ? esc(lang('UI.statusActive')) : esc(lang('UI.statusDisabled')) ?></span>
                                            </div>
                                        </div>
                                        <div class="p-1 rounded cp-surface border cp-border">
                                            <div class="text-[10px] v3-text-muted font-medium"><?= esc(lang('UI.clinicAdminMembershipActive')) ?></div>
                                            <div class="mt-0.5 text-xs font-bold <?= ($admin_info['membership_active'] ?? false) ? 'v3-text-primary' : 'v3-text-danger' ?>">
                                                <i class="fas <?= ($admin_info['membership_active'] ?? false) ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                                <span><?= ($admin_info['membership_active'] ?? false) ? esc(lang('UI.statusActive')) : esc(lang('UI.statusInactive')) ?></span>
                                            </div>
                                        </div>
                                        <div class="p-1 rounded cp-surface border cp-border">
                                            <div class="text-[10px] v3-text-muted font-medium"><?= esc(lang('UI.clinicAdminAuthorityActive')) ?></div>
                                            <div class="mt-0.5 text-xs font-bold <?= ($admin_info['has_admin_authority'] ?? false) ? 'v3-text-primary' : 'v3-text-warning' ?>">
                                                <i class="fas <?= ($admin_info['has_admin_authority'] ?? false) ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                                                <span><?= ($admin_info['has_admin_authority'] ?? false) ? esc(lang('UI.statusManager')) : esc(lang('UI.statusMissing')) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metadata footer -->
                                    <div class="pt-1.5 flex items-center justify-between text-[11px] v3-text-muted">
                                        <span>
                                            <?= esc(lang('UI.clinicAdminAssignedSince')) ?>:
                                            <strong><?= !empty($admin_info['assigned_since']) ? date('M j, Y', strtotime($admin_info['assigned_since'])) : '--' ?></strong>
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium <?= ($admin_info['designation_source'] ?? '') === 'designated' ? 'v3-bg-chair v3-text-chair' : 'v3-bg-waiting v3-text-warning border v3-border-default' ?>">
                                            <?= ($admin_info['designation_source'] ?? '') === 'designated' ? esc(lang('UI.clinicAdminSourceDesignated')) : esc(lang('UI.legacyAdminCandidate')) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <?php if (!empty($admin_info['is_fallback'])): ?>
                                        <button type="button" onclick="openAssignAdminModal(this)" class="flex-1 px-2.5 py-1.5 cp-primary-surface v3-hover-primary v3-text-on-primary text-xs font-bold rounded-lg transition-colors flex items-center justify-center gap-1 v3-btn v3-btn--secondary">
                                            <i class="fas fa-check-circle text-[10px]"></i>
                                            <span><?= esc(lang('UI.confirmDesignation')) ?></span>
                                        </button>
                                    <?php else: ?>
                                        <?php if (!($admin_info['is_healthy'] ?? false)): ?>
                                            <button type="button" onclick="openRestoreAdminModal(this)" class="flex-1 px-2.5 py-1.5 cp-primary-surface v3-hover-primary v3-text-on-primary text-xs font-bold rounded-lg transition-colors flex items-center justify-center gap-1 v3-btn v3-btn--secondary">
                                                <i class="fas fa-wrench text-[10px]"></i>
                                                <span><?= esc(lang('UI.clinicAdminRestoreBtn')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <button type="button" onclick="openReplaceAdminModal(this)" class="flex-1 px-2.5 py-1.5 cp-soft-surface v3-hover-primary cp-accent text-xs font-bold rounded-lg border cp-border transition-colors flex items-center justify-center gap-1 v3-btn v3-btn--secondary">
                                        <i class="fas fa-exchange-alt text-[10px]"></i>
                                        <span><?= esc(lang('UI.clinicAdminReplaceBtn')) ?></span>
                                    </button>

                                    <button type="button" onclick="openPasswordResetModal(this)" class="px-2.5 py-1.5 cp-surface v3-hover-soft v3-text-ink text-xs font-semibold rounded-lg border cp-border transition-colors flex items-center justify-center gap-1 v3-btn v3-btn--secondary" title="<?= esc(lang('UI.clinicAdminResetPassword')) ?>">
                                        <i class="fas fa-key text-[10px]"></i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <!-- Unassigned Empty State -->
                                <div class="text-center py-4 cp-surface cp-radius border border-dashed cp-border">
                                    <div class="w-10 h-10 mx-auto rounded-full v3-bg-waiting flex items-center justify-center v3-text-warning mb-2">
                                        <i class="fas fa-user-slash text-base"></i>
                                    </div>
                                    <p class="text-xs font-semibold v3-text-ink mb-1"><?= esc(lang('UI.clinicAdminWarningUnassigned')) ?></p>
                                    <button type="button" onclick="openAssignAdminModal(this)" class="mt-2 px-3 py-1.5 cp-primary-surface v3-hover-primary v3-text-on-primary text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1 v3-btn v3-btn--secondary">
                                        <i class="fas fa-user-plus text-[10px]"></i>
                                        <span><?= esc(lang('UI.clinicAdminAssignBtn')) ?></span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clinic Details -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Subscription Information -->
            <div class="cp-surface cp-radius border cp-border overflow-hidden">
                <div class="px-6 py-4 border-b cp-border flex justify-between items-center cp-surface">
                    <h3 class="text-lg font-bold v3-text-ink flex items-center">
                        <i class="fas fa-layer-group cp-accent mr-2"></i>
                        <?= esc(lang('V3Platform.subscription')) ?>
                    </h3>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full cp-soft-surface cp-accent">
                        <?= esc($subscription['status'] ?? 'None') ?>
                    </span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium v3-text-muted uppercase tracking-wider mb-1"><?= esc(lang('V3Platform.currentPlan')) ?></label>
                            <p class="text-xl font-bold v3-text-ink"><?= esc($plan_name ?? 'Unassigned') ?></p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium v3-text-muted uppercase tracking-wider mb-1"><?= esc(lang('V3Platform.billingCycle')) ?></label>
                            <div class="flex space-x-4 text-sm">
                                <div>
                                    <span class="v3-text-muted"><?= esc(lang('V3Platform.start')) ?></span>
                                    <span class="font-medium v3-text-ink"><?= \App\Models\ClinicSubscriptionModel::formatDisplay($subscription['start_at'] ?? null) ?></span>
                                </div>
                                <div>
                                    <span class="v3-text-muted"><?= esc(lang('V3Platform.end')) ?></span>
                                    <span class="font-medium v3-text-ink"><?= \App\Models\ClinicSubscriptionModel::formatDisplay($subscription['end_at'] ?? null) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lifecycle Actions -->
                    <div class="mt-6 pt-6 border-t cp-border">
                        <h4 class="text-sm font-bold v3-text-ink mb-4"><?= esc(lang('V3Platform.subscriptionActions')) ?></h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Start/Change Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/start') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border">
                                <?= csrf_field() ?>
                                <label class="block text-xs font-medium v3-text-muted mb-2"><?= esc(lang('V3Platform.changePlan')) ?></label>
                                <div class="flex gap-2">
                                    <select name="plan_id" class="flex-1 rounded-lg cp-border text-sm v3-focus-control v3-focus-control v3-select" required>
                                        <option value=""><?= esc(lang('V3Platform.selectPlan')) ?></option>
                                        <?php foreach (($plans ?? []) as $plan): ?>
                                            <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="px-3 py-2 cp-primary-surface v3-text-on-primary text-xs font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--secondary">
                                        <?= esc(lang('V3Platform.start2')) ?>
                                    </button>
                                </div>
                            </form>

                            <!-- Extend Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/extend') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border">
                                <?= csrf_field() ?>
                                <input type="hidden" name="expected_end_at" value="<?= ($subscription && $subscription['end_at'] !== null) ? esc($subscription['end_at']) : '__NULL__' ?>">
                                <input type="hidden" name="expected_updated_at" value="<?= ($subscription && $subscription['updated_at'] !== null) ? esc($subscription['updated_at']) : '__NULL__' ?>">
                                <label class="block text-xs font-medium v3-text-muted mb-2"><?= esc(lang('V3Platform.extendDuration')) ?></label>
                                <div class="flex gap-2">
                                    <select name="duration" class="flex-1 rounded-lg cp-border text-sm v3-focus-control v3-focus-control v3-select">
                                        <option value="monthly"><?= esc(lang('V3Platform.label1Month')) ?></option>
                                        <option value="yearly"><?= esc(lang('V3Platform.label1Year')) ?></option>
                                    </select>
                                    <button class="px-3 py-2 cp-primary-surface v3-text-on-primary text-xs font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--secondary">
                                        <?= esc(lang('V3Platform.extend')) ?>
                                    </button>
                                </div>
                            </form>

                            <!-- Cancel Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/cancel') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border" onsubmit="return confirm('Are you sure you want to cancel this subscription?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="expected_updated_at" value="<?= ($subscription && $subscription['updated_at'] !== null) ? esc($subscription['updated_at']) : '__NULL__' ?>">
                                <input type="hidden" name="canceled_reason" value="manual_cancel">
                                <label class="block text-xs font-medium v3-text-muted mb-2"><?= esc(lang('V3Platform.cancelSubscription')) ?></label>
                                <button type="submit" class="w-full px-3 py-2 v3-bg-danger v3-text-on-primary text-xs font-bold rounded-lg v3-hover-danger transition-colors v3-btn v3-btn--danger" <?= (!$subscription || !in_array($subscription['status'] ?? '', ['active', 'paused'], true)) ? 'disabled' : '' ?>>
                                    <?= esc(lang('V3Platform.cancelActiveSubscription')) ?>
                                </button>
                            </form>

                            <!-- Update Subscription Dates -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/update-dates') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border">
                                <?= csrf_field() ?>
                                <input type="hidden" name="expected_start_at" value="<?= ($subscription && $subscription['start_at'] !== null) ? esc($subscription['start_at']) : '__NULL__' ?>">
                                <input type="hidden" name="expected_end_at" value="<?= ($subscription && $subscription['end_at'] !== null) ? esc($subscription['end_at']) : '__NULL__' ?>">
                                <input type="hidden" name="expected_updated_at" value="<?= ($subscription && $subscription['updated_at'] !== null) ? esc($subscription['updated_at']) : '__NULL__' ?>">
                                <label class="block text-xs font-medium v3-text-muted mb-2"><?= esc(lang('V3Platform.updateDatesUtc')) ?></label>
                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <div>
                                        <label class="block text-[10px] v3-text-muted mb-1"><?= esc(lang('V3Platform.startDate')) ?></label>
                                        <input type="text" name="start_at" value="<?= esc($subscription['start_at'] ?? '') ?>" placeholder="YYYY-MM-DD HH:MM:SS" class="w-full rounded-lg cp-border text-xs v3-focus-control v3-focus-control v3-input">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] v3-text-muted mb-1"><?= esc(lang('V3Platform.endDate')) ?></label>
                                        <input type="text" name="end_at" value="<?= esc($subscription['end_at'] ?? '') ?>" placeholder="YYYY-MM-DD HH:MM:SS" class="w-full rounded-lg cp-border text-xs v3-focus-control v3-focus-control v3-input">
                                    </div>
                                </div>
                                <button type="submit" class="w-full px-3 py-2 v3-bg-warning v3-text-on-primary text-xs font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--primary" <?= (!$subscription || !in_array($subscription['status'] ?? '', ['active', 'paused'], true)) ? 'disabled' : '' ?>>
                                    <?= esc(lang('V3Platform.updateDates')) ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Snapshot -->
            <div class="cp-surface cp-radius border cp-border overflow-hidden">
                <div class="px-6 py-4 border-b cp-border flex justify-between items-center cp-surface">
                    <h3 class="text-lg font-bold v3-text-ink flex items-center">
                        <i class="fas fa-file-invoice-dollar v3-text-primary mr-2"></i>
                        <?= esc(lang('V3Platform.billingSnapshot')) ?>
                    </h3>
                    <a href="<?= base_url('controlplane/invoices') ?>" class="text-sm cp-accent v3-hover-text-chair font-medium"><?= esc(lang('V3Platform.viewAllInvoices')) ?></a>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="v3-bg-alert p-4 rounded-lg border v3-border-default text-center">
                            <p class="text-xs font-bold v3-text-danger uppercase tracking-wider"><?= esc(lang('V3Platform.outstanding')) ?></p>
                            <p class="text-2xl font-bold v3-text-danger mt-1"><?= !empty($invoice_data_available) ? '$' . number_format($invoiceStats['outstanding'] ?? 0, 2) : '--' ?></p>
                        </div>
                        <div class="v3-bg-paid p-4 rounded-lg border v3-border-default text-center">
                            <p class="text-xs font-bold v3-text-primary uppercase tracking-wider"><?= esc(lang('V3Platform.revenueMo')) ?></p>
                            <p class="text-2xl font-bold v3-text-primary mt-1"><?= !empty($invoice_data_available) ? '$' . number_format($invoiceStats['revenue_month'] ?? 0, 2) : '--' ?></p>
                        </div>
                        <div class="v3-bg-waiting p-4 rounded-lg border v3-border-default text-center">
                            <p class="text-xs font-bold v3-text-warning uppercase tracking-wider"><?= esc(lang('V3Platform.overdue')) ?></p>
                            <p class="text-2xl font-bold v3-text-warning mt-1"><?= !empty($invoice_data_available) ? (int)($invoiceStats['overdue'] ?? 0) : '--' ?></p>
                        </div>
                    </div>

                    <?php if (!($invoice_data_available ?? true)): ?>
                        <div class="rounded-lg v3-bg-waiting border v3-border-default p-4 v3-text-warning text-sm flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle v3-text-warning"></i>
                            <span><?= esc(lang('UI.billingDataUnavailable')) ?></span>
                        </div>
                    <?php elseif (!empty($invoices)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm v3-text-muted">
                                <thead class="cp-surface v3-text-muted font-medium border-b cp-border">
                                    <tr>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.invoice')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.date')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.amount')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.status')) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y v3-border-default">
                                    <?php foreach (array_slice($invoices, 0, 5) as $inv): ?>
                                        <tr class="v3-hover-soft transition-colors">
                                            <td class="px-4 py-3 font-medium cp-accent">
                                                <a href="<?= base_url('controlplane/invoices/' . $inv['id']) ?>"><?= esc($inv['invoice_number']) ?></a>
                                            </td>
                                            <td class="px-4 py-3"><?= esc($inv['issue_date']) ?></td>
                                            <td class="px-4 py-3 font-semibold v3-text-ink">$<?= number_format($inv['total_amount'], 2) ?></td>
                                            <td class="px-4 py-3">
                                                <?php
                                                    $iStatus = $inv['status'] ?? 'draft';
                                                    $iClass = match($iStatus) {
                                                        'paid' => 'v3-bg-paid v3-text-primary',
                                                        'overdue' => 'v3-bg-alert v3-text-danger',
                                                        'sent' => 'v3-bg-scheduled v3-text-primary',
                                                        default => 'v3-bg-soft v3-text-muted'
                                                    };
                                                ?>
                                                <span class="px-2 py-1 rounded-full text-xs font-bold <?= $iClass ?>"><?= ucfirst($iStatus) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 v3-text-muted">
                            <p><?= esc(lang('V3Platform.noInvoicesFound')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wa-rollout-shell">
                <div class="wa-rollout-head">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 items-center justify-center cp-radius cp-soft-surface v3-text-primary">
                                <i class="fab fa-whatsapp text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold v3-text-ink"><?= esc(lang('V3Platform.whatsappRollout')) ?></h3>
                                <p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.clinicLevelDeliveryPolicyForTheSharedSenderAndArabicTemplates')) ?></p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="wa-pill <?= !empty($wa_feature_enabled) ? 'v3-bg-paid v3-text-primary' : 'v3-bg-alert v3-text-danger' ?>">
                                <?= !empty($wa_feature_enabled) ? 'Plan Enabled' : 'Plan Disabled' ?>
                            </span>
                            <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="inline-flex items-center cp-radius border cp-border cp-surface px-4 py-2 text-sm font-bold v3-text-ink v3-hover-soft v3-btn v3-btn--secondary">
                                <?= esc(lang('V3Platform.openSharedTemplates')) ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="cp-radius border v3-border-default cp-soft-surface p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-primary"><?= esc(lang('V3Platform.clinicName')) ?></p>
                            <p class="mt-2 text-sm font-bold v3-text-ink"><?= esc(($clinicSettingsSnapshot['clinic_name'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_name'] : ($clinic['name'] ?? 'Not set')) ?></p>
                        </div>
                        <div class="cp-radius border cp-border cp-soft-surface p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] cp-accent"><?= esc(lang('V3Platform.clinicPhone')) ?></p>
                            <p class="mt-2 text-sm font-bold v3-text-ink"><?= esc(($clinicSettingsSnapshot['clinic_phone'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_phone'] : 'Not set') ?></p>
                        </div>
                        <div class="cp-radius border v3-border-default v3-bg-waiting p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-warning"><?= esc(lang('V3Platform.clinicAddress')) ?></p>
                            <p class="mt-2 text-sm font-bold v3-text-ink"><?= esc(($clinicSettingsSnapshot['clinic_address'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_address'] : 'Not set') ?></p>
                        </div>
                    </div>

                    <div class="cp-radius border cp-border cp-surface p-4">
                        <p class="text-sm font-bold v3-text-ink"><?= esc(lang('V3Platform.howThisClinicInheritsWhatsapp')) ?></p>
                        <p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.templatesAreManagedOnceInControlPlaneThisPageOnlyDecidesWhichOfTheFiveWorkflowsAreActiveForThisClinicAndWhatDeliveryLimitsApply')) ?></p>
                    </div>

                    <form action="<?= base_url('controlplane/whatsapp/policy') ?>" method="post" class="space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="clinic_id" value="<?= (int) $clinic['id'] ?>">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider v3-text-muted"><?= esc(lang('V3Platform.dailyLimit')) ?></label>
                                <input type="number" name="daily_limit" value="<?= (int) ($waPolicy['daily_limit'] ?? 50) ?>" class="w-full rounded-lg cp-border text-sm v3-focus-control v3-focus-control v3-input">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider v3-text-muted"><?= esc(lang('V3Platform.monthlyLimit')) ?></label>
                                <input type="number" name="monthly_limit" value="<?= (int) ($waPolicy['monthly_limit'] ?? 1000) ?>" class="w-full rounded-lg cp-border text-sm v3-focus-control v3-focus-control v3-input">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider v3-text-muted"><?= esc(lang('V3Platform.perHourLimit')) ?></label>
                                <input type="number" name="per_hour_limit" value="<?= (int) ($waPolicy['per_hour_limit'] ?? 20) ?>" class="w-full rounded-lg cp-border text-sm v3-focus-control v3-focus-control v3-input">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider v3-text-muted"><?= esc(lang('V3Platform.reminderLeadMinutes')) ?></label>
                                <input type="number" name="reminder_lead_time_minutes" value="<?= (int) ($waPolicy['reminder_lead_time_minutes'] ?? 1440) ?>" class="w-full rounded-lg cp-border text-sm v3-focus-control v3-focus-control v3-input">
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <h4 class="text-base font-black v3-text-ink"><?= esc(lang('V3Platform.templateTriggerToggles')) ?></h4>
                                <p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.eachWorkflowIsControlledIndividuallyADisabledPlanOrGlobalSwitchWillLockTheClinicSwitch')) ?></p>
                            </div>
                            <div class="space-y-3">
                                <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                                    <?php
                                    $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1;
                                    $clinicEnabled = (int) ($waPolicyToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1;
                                    $triggerConfig = is_array($waGlobalTriggerTemplates[$triggerKey] ?? null) ? $waGlobalTriggerTemplates[$triggerKey] : [];
                                    $displayTemplateName = trim((string) ($triggerConfig['name'] ?? '')) !== '' ? (string) $triggerConfig['name'] : (string) ($triggerMeta['suggested_template_name'] ?? ($triggerKey . '_ar_v1'));
                                    ?>
                                    <div class="wa-rollout-row <?= (!$platformEnabled || empty($wa_feature_enabled)) ? 'is-disabled' : '' ?>">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-bold v3-text-ink"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                                <span class="wa-pill v3-bg-soft v3-text-ink"><?= esc($displayTemplateName) ?></span>
                                                <?php if (!$platformEnabled): ?>
                                                    <span class="wa-pill v3-bg-alert v3-text-danger"><?= esc(lang('V3Platform.globalOff')) ?></span>
                                                <?php endif; ?>
                                                <?php if (empty($wa_feature_enabled)): ?>
                                                    <span class="wa-pill v3-bg-waiting v3-text-warning"><?= esc(lang('V3Platform.planOff')) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mt-1 text-sm v3-text-muted"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-bold uppercase tracking-[0.12em] <?= $clinicEnabled ? 'v3-text-primary' : 'v3-text-muted' ?>">
                                                <?= $clinicEnabled ? 'On' : 'Off' ?>
                                            </span>
                                            <input type="checkbox" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1" class="ui-toggle v3-switch" <?= $clinicEnabled ? 'checked' : '' ?> <?= !$platformEnabled || empty($wa_feature_enabled) ? 'disabled' : '' ?>>
                                        </div>
                                    </div>
                                    <?php if ((!$platformEnabled || empty($wa_feature_enabled)) && $clinicEnabled): ?>
                                        <input type="hidden" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 border-t cp-border pt-6 lg:grid-cols-[auto,1fr,auto] lg:items-end">
                            <label class="inline-flex items-center gap-3 cp-radius border cp-border cp-surface px-4 py-3">
                                <input type="checkbox" name="suspended" value="1" class="ui-toggle v3-switch" <?= !empty($waPolicy['suspended']) ? 'checked' : '' ?>>
                                <span>
                                    <span class="block text-sm font-bold v3-text-ink"><?= esc(lang('V3Platform.emergencyPause')) ?></span>
                                    <span class="mt-1 block text-xs v3-text-muted"><?= esc(lang('V3Platform.immediatelyStopAutomatedSendsForThisClinic')) ?></span>
                                </span>
                            </label>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider v3-text-muted"><?= esc(lang('V3Platform.pauseReason')) ?></label>
                                <input type="text" name="suspended_reason" value="<?= esc($waPolicy['suspended_reason'] ?? '') ?>" class="w-full rounded-lg cp-border text-sm v3-focus-control v3-focus-control v3-input" placeholder="<?= esc(lang('V3Platform.optionalReasonForAuditTrail'), 'attr') ?>">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="cp-radius cp-primary-surface px-4 py-2 text-sm font-bold v3-text-on-primary transition-colors v3-hover-primary v3-btn v3-btn--primary">
                                    <?= esc(lang('V3Platform.saveWhatsappPolicy')) ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="v3-bg-alert cp-radius border v3-border-default p-6">
                <h3 class="text-lg font-bold v3-text-danger mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= esc(lang('V3Platform.dangerZone')) ?>
                </h3>

                <div class="flex flex-wrap gap-4">
                        <p class="mb-3 text-sm v3-text-muted" role="note"><?= esc(lang('IonAuth.clinic_delete_history_protected')) ?></p>
                    <?php if (($clinic['status'] ?? 'inactive') === 'active'): ?>
                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="px-4 py-2 v3-bg-warning v3-text-on-primary text-sm font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--secondary">
                                <?= esc(lang('V3Platform.suspendClinic')) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="px-4 py-2 v3-bg-primary v3-text-on-primary text-sm font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--primary">
                                <?= esc(lang('V3Platform.reactivateClinic')) ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <button type="button" class="px-4 py-2 v3-bg-danger v3-text-on-primary text-sm font-bold rounded-lg v3-hover-danger transition-colors v3-btn v3-btn--danger" onclick="openTerminateModal()">
                        <?= esc(lang('V3Platform.terminateClinic')) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terminate Modal -->
<div id="terminateModal" role="dialog" aria-modal="true" aria-labelledby="terminateModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="v3-bg-alert px-6 py-4 border-b v3-border-default flex items-center justify-between">
            <h3 id="terminateModalTitle" class="text-lg font-bold v3-text-danger"><?= esc(lang('V3Platform.confirmTermination')) ?></h3>
            <button onclick="closeTerminateModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="v3-text-muted mb-4">
                <?= esc(lang('V3Platform.thisWillDisableAccessAndCancelTheSubscriptionFor')) ?> <strong class="v3-text-ink"><?= esc($clinic['name']) ?></strong>.
                <br><?= esc(lang('V3Platform.pleaseTypeTheClinicNameBelowToConfirm')) ?>
            </p>
            <input type="text" id="terminateConfirmInput" class="w-full px-4 py-2 border cp-border rounded-lg focus:ring-2 v3-focus-control v3-focus-control mb-4 v3-input" placeholder="<?= esc($clinic['name']) ?>" oninput="checkTerminateInput()">

            <form id="terminateForm" action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/terminate') ?>" method="post">
                <?= csrf_field() ?>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 v3-bg-soft v3-text-ink font-bold rounded-lg v3-hover-soft v3-btn v3-btn--secondary" onclick="closeTerminateModal()"><?= esc(lang('V3Platform.cancel')) ?></button>
                    <button type="submit" id="terminateBtn" class="px-4 py-2 v3-bg-danger v3-text-on-primary font-bold rounded-lg v3-hover-danger opacity-50 cursor-not-allowed v3-btn v3-btn--danger" disabled><?= esc(lang('V3Platform.terminate')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordResetModal" role="dialog" aria-modal="true" aria-labelledby="passwordResetModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 id="passwordResetModalTitle" class="text-lg font-bold cp-accent"><?= esc(lang('V3Platform.resetAdminPassword')) ?></h3>
            <button onclick="closePasswordResetModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="v3-text-muted mb-4">
                <?= esc(lang('V3Platform.enterANewPasswordFor')) ?> <strong><?= esc($clinic['admin_email'] ?? 'the admin') ?></strong>.
            </p>
            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reset-admin-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-bold v3-text-muted uppercase mb-1"><?= esc(lang('V3Platform.newPassword')) ?></label>
                    <input type="password" name="password" class="w-full px-4 py-2 border cp-border rounded-lg focus:ring-2 v3-focus-control v3-focus-control v3-input" required minlength="8" placeholder="<?= esc(lang('V3Platform.minimum8Characters'), 'attr') ?>">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 v3-bg-soft v3-text-ink font-bold rounded-lg v3-hover-soft v3-btn v3-btn--secondary" onclick="closePasswordResetModal()"><?= esc(lang('V3Platform.cancel')) ?></button>
                    <button type="submit" class="px-4 py-2 cp-primary-surface v3-text-on-primary font-bold rounded-lg v3-hover-primary v3-btn v3-btn--primary"><?= esc(lang('V3Platform.resetPassword')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Administrator Modal -->
<div id="assignAdminModal" role="dialog" aria-modal="true" aria-labelledby="assignAdminModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 id="assignAdminModalTitle" class="text-lg font-bold cp-accent flex items-center gap-2">
                <i class="fas fa-user-plus cp-accent"></i>
                <span><?= esc(!empty($admin_info['is_fallback']) ? lang('UI.confirmDesignation') : lang('UI.clinicAdminModalAssignTitle')) ?></span>
            </h3>
            <button onclick="closeAssignAdminModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/admin/assign') ?>" method="post" class="p-6">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-xs font-bold v3-text-ink uppercase mb-1"><?= esc(lang('UI.clinicAdminSelectUser')) ?></label>
                <?php if (!empty($eligible_admin_users)): ?>
                    <select name="user_id" class="w-full px-3 py-2 border cp-border rounded-lg text-sm focus:ring-2 v3-focus-control v3-focus-control v3-select" required>
                        <option value=""><?= esc(lang('UI.clinicAdminSelectUserPlaceholder')) ?></option>
                        <?php foreach ($eligible_admin_users as $eu): ?>
                            <option value="<?= (int) $eu['id'] ?>" <?= (!empty($admin_info['is_fallback']) && (int) ($admin_info['admin_user']['id'] ?? 0) === (int) $eu['id']) ? 'selected' : '' ?>>
                                <?= esc(!empty($eu['name']) ? $eu['name'] : (!empty($eu['email']) ? $eu['email'] : 'User #' . $eu['id'])) ?>
                                (<?= esc($eu['email'] ?? '') ?>)
                                [<?= esc($eu['clinic_role_title'] ?? 'Staff') ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <div class="p-3 v3-bg-alert border v3-border-default v3-text-danger text-xs rounded-lg">
                        <?= esc(lang('UI.clinicAdminNoEligibleUsers')) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex justify-end space-x-3 rtl:space-x-reverse">
                <button type="button" class="px-4 py-2 v3-bg-soft v3-text-ink font-bold rounded-lg v3-hover-soft text-sm v3-btn v3-btn--secondary" onclick="closeAssignAdminModal()"><?= esc(lang('UI.cancel')) ?></button>
                <button type="submit" class="px-4 py-2 cp-primary-surface v3-text-on-primary font-bold rounded-lg v3-hover-primary text-sm v3-btn v3-btn--primary" <?= empty($eligible_admin_users) ? 'disabled' : '' ?>>
                    <?= esc(!empty($admin_info['is_fallback']) ? lang('UI.confirmDesignation') : lang('UI.clinicAdminConfirmAssign')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Replace Administrator Modal -->
<div id="replaceAdminModal" role="dialog" aria-modal="true" aria-labelledby="replaceAdminModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 id="replaceAdminModalTitle" class="text-lg font-bold cp-accent flex items-center gap-2">
                <i class="fas fa-exchange-alt cp-accent"></i>
                <span><?= esc(lang('UI.clinicAdminModalReplaceTitle')) ?></span>
            </h3>
            <button onclick="closeReplaceAdminModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/admin/replace') ?>" method="post" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="expected_admin_id" value="<?= (int) ($admin_info['admin_user']['id'] ?? 0) ?>">
            <div class="mb-4">
                <label class="block text-xs font-bold v3-text-ink uppercase mb-1"><?= esc(lang('UI.clinicAdminSelectUser')) ?></label>
                <?php
                $currentAdminId = (int) ($admin_info['admin_user']['id'] ?? 0);
                $replacementCandidates = array_filter($eligible_admin_users ?? [], function($eu) use ($currentAdminId) {
                    return (int) $eu['id'] !== $currentAdminId;
                });
                ?>
                <?php if (!empty($replacementCandidates)): ?>
                    <select name="user_id" class="w-full px-3 py-2 border cp-border rounded-lg text-sm focus:ring-2 v3-focus-control v3-focus-control v3-select" required>
                        <option value=""><?= esc(lang('UI.clinicAdminSelectUserPlaceholder')) ?></option>
                        <?php foreach ($replacementCandidates as $eu): ?>
                            <option value="<?= (int) $eu['id'] ?>">
                                <?= esc(!empty($eu['name']) ? $eu['name'] : (!empty($eu['email']) ? $eu['email'] : 'User #' . $eu['id'])) ?>
                                (<?= esc($eu['email'] ?? '') ?>)
                                [<?= esc($eu['clinic_role_title'] ?? 'Staff') ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <div class="p-3 v3-bg-waiting border v3-border-default v3-text-warning text-xs rounded-lg">
                        <?= esc(lang('UI.clinicAdminNoEligibleUsers')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-5 space-y-2">
                <label class="block text-xs font-bold v3-text-ink uppercase mb-1"><?= esc(lang('UI.clinicAdminPreviousActionLabel')) ?></label>
                <div class="space-y-2 border cp-border rounded-lg p-3 cp-surface">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="previous_admin_action" value="demote_to_staff" checked class="mt-0.5 w-4 h-4 cp-accent cp-border v3-focus-control v3-radio">
                        <span class="text-xs v3-text-ink"><?= esc(lang('UI.clinicAdminDemotePrevious')) ?></span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="previous_admin_action" value="keep_role" class="mt-0.5 w-4 h-4 cp-accent cp-border v3-focus-control v3-radio">
                        <span class="text-xs v3-text-ink"><?= esc(lang('UI.clinicAdminKeepRolePrevious')) ?></span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="previous_admin_action" value="deactivate_membership" class="mt-0.5 w-4 h-4 cp-accent cp-border v3-focus-control v3-radio">
                        <span class="text-xs v3-text-ink"><?= esc(lang('UI.clinicAdminDeactivateMembershipPrevious')) ?></span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 rtl:space-x-reverse">
                <button type="button" class="px-4 py-2 v3-bg-soft v3-text-ink font-bold rounded-lg v3-hover-soft text-sm v3-btn v3-btn--secondary" onclick="closeReplaceAdminModal()"><?= esc(lang('UI.cancel')) ?></button>
                <button type="submit" class="px-4 py-2 cp-primary-surface v3-text-on-primary font-bold rounded-lg v3-hover-primary text-sm v3-btn v3-btn--primary" <?= empty($replacementCandidates) ? 'disabled' : '' ?>>
                    <?= esc(lang('UI.clinicAdminConfirmReplace')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Restore Administrator Modal -->
<div id="restoreAdminModal" role="dialog" aria-modal="true" aria-labelledby="restoreAdminModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b v3-border-default flex items-center justify-between">
            <h3 id="restoreAdminModalTitle" class="text-lg font-bold v3-text-primary flex items-center gap-2">
                <i class="fas fa-wrench v3-text-primary"></i>
                <span><?= esc(lang('UI.clinicAdminModalRestoreTitle')) ?></span>
            </h3>
            <button onclick="closeRestoreAdminModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/admin/restore') ?>" method="post" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= (int) ($admin_info['admin_user']['id'] ?? 0) ?>">
            <input type="hidden" name="expected_designated_admin_id" value="<?= (int) ($admin_info['admin_user']['id'] ?? 0) ?>">

            <p class="text-sm v3-text-muted mb-4">
                <?= esc(lang('UI.clinicAdminRestoreHelp')) ?>
            </p>

            <?php if (!($admin_info['account_active'] ?? true)): ?>
                <div class="mb-4 p-3 v3-bg-alert border v3-border-default rounded-lg v3-text-danger text-xs">
                    <div class="font-bold flex items-center gap-1 mb-1">
                        <i class="fas fa-exclamation-circle v3-text-danger"></i>
                        <span><?= esc(lang('UI.clinicAdminAccountDeactivatedTitle')) ?></span>
                    </div>
                    <p class="mb-2"><?= esc(lang('UI.clinicAdminAccountDeactivatedHelp')) ?></p>
                    <a href="<?= base_url('controlplane/users') ?>" class="inline-flex items-center gap-1 font-bold v3-text-danger v3-hover-text-danger underline text-xs">
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                        <span><?= esc(lang('UI.clinicAdminGoToUsers')) ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($admin_info['admin_user'])): ?>
                <div class="p-3 cp-surface rounded-lg border cp-border text-xs mb-4">
                    <strong><?= esc(!empty($admin_info['admin_name']) ? $admin_info['admin_name'] : (!empty($admin_info['admin_user']['email']) ? $admin_info['admin_user']['email'] : 'Administrator')) ?></strong>
                    <div class="v3-text-muted"><?= esc($admin_info['admin_user']['email'] ?? '') ?></div>
                </div>
            <?php endif; ?>

            <div class="flex justify-end space-x-3 rtl:space-x-reverse">
                <button type="button" class="px-4 py-2 v3-bg-soft v3-text-ink font-bold rounded-lg v3-hover-soft text-sm v3-btn v3-btn--secondary" onclick="closeRestoreAdminModal()"><?= esc(lang('UI.cancel')) ?></button>
                <button type="submit" <?= !($admin_info['account_active'] ?? true) ? 'disabled title="' . esc(lang('UI.clinicAdminReactivateAccountFirst')) . '" class="px-4 py-2 v3-bg-sand v3-text-muted font-bold rounded-lg cursor-not-allowed text-sm"' : 'class="px-4 py-2 v3-bg-primary v3-text-on-primary font-bold rounded-lg v3-hover-primary text-sm"' ?> class="v3-btn v3-btn--primary">
                    <?= esc(lang('UI.clinicAdminConfirmRestore')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const clinicName = "<?= esc($clinic['name']) ?>";

    let activeModal = null;
    let lastFocusedTrigger = null;
    let focusTimer = null;

    const focusableSelector = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function getFocusableElements(container) {
        return Array.from(container.querySelectorAll(focusableSelector)).filter(function (el) {
            return el.offsetWidth > 0 || el.offsetHeight > 0 || el === document.activeElement;
        });
    }

    function trapFocus(modal, event) {
        if (event.key !== 'Tab') return;
        const focusables = getFocusableElements(modal);
        if (focusables.length === 0) {
            event.preventDefault();
            return;
        }
        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey) {
            if (document.activeElement === first || !modal.contains(document.activeElement)) {
                event.preventDefault();
                last.focus();
            }
        } else {
            if (document.activeElement === last || !modal.contains(document.activeElement)) {
                event.preventDefault();
                first.focus();
            }
        }
    }

    function showDialog(modal, triggerElement, initialSelector) {
        if (!modal) return;

        if (activeModal && activeModal !== modal) {
            activeModal.classList.add('hidden');
            activeModal.classList.remove('flex');
        }

        activeModal = modal;
        lastFocusedTrigger = triggerElement || document.activeElement;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (focusTimer) clearTimeout(focusTimer);
        focusTimer = setTimeout(function () {
            if (modal && !modal.classList.contains('hidden') && activeModal === modal) {
                const target = initialSelector ? modal.querySelector(initialSelector) : null;
                const focusables = getFocusableElements(modal);
                const toFocus = target || focusables[0];
                if (toFocus && typeof toFocus.focus === 'function') {
                    toFocus.focus();
                }
            }
        }, 50);
    }

    function hideDialog(modal) {
        if (!modal || modal.classList.contains('hidden')) {
            return; // Already hidden: do not consume trigger
        }

        if (focusTimer) {
            clearTimeout(focusTimer);
            focusTimer = null;
        }

        const form = modal.querySelector('form');
        if (form) form.reset();

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (activeModal === modal) {
            activeModal = null;
            if (lastFocusedTrigger && typeof lastFocusedTrigger.focus === 'function') {
                const el = lastFocusedTrigger;
                lastFocusedTrigger = null;
                el.focus();
            } else {
                lastFocusedTrigger = null;
            }
        }
    }

    function openTerminateModal(triggerElement) {
        const m = document.getElementById('terminateModal');
        showDialog(m, triggerElement, '#terminateConfirmInput');
    }

    function closeTerminateModal() {
        const m = document.getElementById('terminateModal');
        hideDialog(m);
        const input = document.getElementById('terminateConfirmInput');
        if (input) input.value = '';
        checkTerminateInput();
    }

    function checkTerminateInput() {
        const input = document.getElementById('terminateConfirmInput').value;
        const btn = document.getElementById('terminateBtn');

        if (input === clinicName) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    function openPasswordResetModal(triggerElement) {
        const m = document.getElementById('passwordResetModal');
        showDialog(m, triggerElement, 'input[name="password"]');
    }

    function closePasswordResetModal() {
        const m = document.getElementById('passwordResetModal');
        hideDialog(m);
    }

    function openAssignAdminModal(triggerElement) {
        const m = document.getElementById('assignAdminModal');
        showDialog(m, triggerElement, 'select, input, button[type="submit"]');
    }

    function closeAssignAdminModal() {
        const m = document.getElementById('assignAdminModal');
        hideDialog(m);
    }

    function openReplaceAdminModal(triggerElement) {
        const m = document.getElementById('replaceAdminModal');
        showDialog(m, triggerElement, 'select, input, button[type="submit"]');
    }

    function closeReplaceAdminModal() {
        const m = document.getElementById('replaceAdminModal');
        hideDialog(m);
    }

    function openRestoreAdminModal(triggerElement) {
        const m = document.getElementById('restoreAdminModal');
        showDialog(m, triggerElement, 'button[type="submit"]');
    }

    function closeRestoreAdminModal() {
        const m = document.getElementById('restoreAdminModal');
        hideDialog(m);
    }

    // Keyboard and Backdrop handlers for accessible dialogs
    document.addEventListener('keydown', function (e) {
        if (!activeModal || activeModal.classList.contains('hidden')) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            if (activeModal.id === 'terminateModal') closeTerminateModal();
            else if (activeModal.id === 'passwordResetModal') closePasswordResetModal();
            else if (activeModal.id === 'assignAdminModal') closeAssignAdminModal();
            else if (activeModal.id === 'replaceAdminModal') closeReplaceAdminModal();
            else if (activeModal.id === 'restoreAdminModal') closeRestoreAdminModal();
            return;
        }

        if (e.key === 'Tab') {
            trapFocus(activeModal, e);
        }
    });

    ['terminateModal', 'passwordResetModal', 'assignAdminModal', 'replaceAdminModal', 'restoreAdminModal'].forEach(function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    if (id === 'terminateModal') closeTerminateModal();
                    else if (id === 'passwordResetModal') closePasswordResetModal();
                    else if (id === 'assignAdminModal') closeAssignAdminModal();
                    else if (id === 'replaceAdminModal') closeReplaceAdminModal();
                    else if (id === 'restoreAdminModal') closeRestoreAdminModal();
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
