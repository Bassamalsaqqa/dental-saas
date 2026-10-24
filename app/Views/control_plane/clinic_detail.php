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
    .ui-toggle {
        appearance: none;
        width: 3.5rem;
        height: 2rem;
        border-radius: 9999px;
        background: #dbeafe;
        position: relative;
        cursor: pointer;
        transition: all .2s ease;
        border: 1px solid rgba(59,130,246,.18);
    }
    .ui-toggle::before {
        content: '';
        position: absolute;
        top: .2rem;
        left: .2rem;
        width: 1.4rem;
        height: 1.4rem;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15,23,42,.16);
        transition: all .2s ease;
    }
    .ui-toggle:checked {
        background: linear-gradient(135deg,#10b981,#059669);
        border-color: transparent;
    }
    .ui-toggle:checked::before {
        transform: translateX(1.45rem);
    }
    [dir="rtl"] .ui-toggle::before {
        left: auto;
        right: .2rem;
    }
    [dir="rtl"] .ui-toggle:checked::before {
        transform: translateX(-1.45rem);
    }
    .ui-toggle:disabled {
        opacity: .55;
        cursor: not-allowed;
    }
    .wa-rollout-shell {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .wa-rollout-head {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .wa-rollout-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
    }
    .wa-rollout-row.is-disabled {
        background: #f8fafc;
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
                <h1 class="cp-title font-bold text-gray-800 mb-3"><?= esc(lang('V3Platform.clinicOverview')) ?></h1>
                <p class="text-gray-600 text-lg"><?= esc($clinic['name']) ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('controlplane/clinics') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i><?= esc(lang('V3Platform.backToClinics')) ?>
                </a>
                <form action="<?= base_url('controlplane/impersonate') ?>" method="post" class="inline-block">
                    <?= csrf_field() ?>
                    <input type="hidden" name="clinic_id" value="<?= esc($clinic['id']) ?>">
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i><?= esc(lang('V3Platform.loginAs')) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 cp-radius p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800"><?= esc(lang('V3Platform.success')) ?></h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p><?= session()->getFlashdata('success') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 cp-radius p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800"><?= esc(lang('V3Platform.error')) ?></h3>
                    <div class="mt-2 text-sm text-red-700">
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
                <div class="cp-panel-padding text-white">
                    <div class="text-center">
                        <div class="w-24 h-24 cp-surface cp-radius flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-hospital text-white cp-title"></i>
                        </div>
                        <h2 class="text-2xl font-bold"><?= esc($clinic['name']) ?></h2>
                        <p class="text-emerald-100 text-lg"><?= esc(lang('V3Platform.id')) ?> <?= esc($clinic['id']) ?></p>

                        <div class="mt-4">
                            <?php
                                $status = $clinic['status'] ?? 'inactive';
                                $statusColors = [
                                    'active' => 'bg-green-400 text-green-900',
                                    'suspended' => 'bg-yellow-400 text-yellow-900',
                                    'inactive' => 'bg-gray-400 text-gray-900'
                                ];
                                $badgeClass = $statusColors[$status] ?? 'bg-gray-400 text-gray-900';
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
                                <p class="text-sm font-medium text-gray-500"><?= esc(lang('V3Platform.slug')) ?></p>
                                <p class="text-sm font-semibold text-gray-900 truncate"><?= esc($clinic['slug'] ?? '--') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full cp-soft-surface flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar cp-accent text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500"><?= esc(lang('V3Platform.created')) ?></p>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($clinic['created_at'] ?? '--') ?></p>
                            </div>
                        </div>

                        <!-- Clinic Administrator Card -->
                        <div class="pt-4 border-t cp-border">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-user-shield cp-accent"></i>
                                    <span><?= esc(lang('UI.clinicAdminTitle')) ?></span>
                                </h4>
                                <?php
                                $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';
                                $statusIcon = 'fa-question-circle';
                                $statusText = lang('UI.clinicAdminUnassigned');
                                if (!empty($admin_info['is_fallback'])) {
                                    $statusClass = 'bg-amber-50 text-amber-800 border-amber-200';
                                    $statusIcon = 'fa-clock';
                                    $statusText = lang('UI.legacyAdminCandidate');
                                } elseif (($admin_info['status'] ?? '') === 'healthy') {
                                    $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    $statusIcon = 'fa-check-circle';
                                    $statusText = lang('UI.clinicAdminHealthy');
                                } elseif (($admin_info['status'] ?? '') === 'disabled_user') {
                                    $statusClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                    $statusIcon = 'fa-user-slash';
                                    $statusText = lang('UI.clinicAdminDisabledUser');
                                } elseif (($admin_info['status'] ?? '') === 'inactive_membership') {
                                    $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $statusIcon = 'fa-id-badge';
                                    $statusText = lang('UI.clinicAdminInactiveMembership');
                                } elseif (($admin_info['status'] ?? '') === 'missing_authority') {
                                    $statusClass = 'bg-orange-50 text-orange-700 border-orange-200';
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
                                <div class="mb-3 p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                                    <div class="font-semibold flex items-center gap-1 mb-1">
                                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                                        <span><?= esc($statusText) ?></span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-amber-700 text-[11px]">
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
                                            <p class="text-sm font-bold text-gray-900 truncate">
                                                <?= esc(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ($u['username'] ?? 'User #' . $u['id'])) ?>
                                            </p>
                                            <p class="text-xs text-gray-600 truncate flex items-center gap-1">
                                                <i class="fas fa-envelope text-gray-400 text-[10px]"></i>
                                                <span><?= esc($u['email'] ?? '--') ?></span>
                                            </p>
                                            <?php if (!empty($u['phone'])): ?>
                                                <p class="text-xs text-gray-500 truncate flex items-center gap-1 mt-0.5">
                                                    <i class="fas fa-phone text-gray-400 text-[10px]"></i>
                                                    <span><?= esc($u['phone']) ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Status Badges Grid -->
                                    <div class="pt-2 border-t cp-border grid grid-cols-3 gap-1.5 text-center">
                                        <div class="p-1 rounded cp-surface border cp-border">
                                            <div class="text-[10px] text-gray-500 font-medium"><?= esc(lang('UI.clinicAdminAccountActive')) ?></div>
                                            <div class="mt-0.5 text-xs font-bold <?= ($admin_info['account_active'] ?? false) ? 'text-emerald-600' : 'text-rose-600' ?>">
                                                <i class="fas <?= ($admin_info['account_active'] ?? false) ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                                <span><?= ($admin_info['account_active'] ?? false) ? esc(lang('UI.statusActive')) : esc(lang('UI.statusDisabled')) ?></span>
                                            </div>
                                        </div>
                                        <div class="p-1 rounded cp-surface border cp-border">
                                            <div class="text-[10px] text-gray-500 font-medium"><?= esc(lang('UI.clinicAdminMembershipActive')) ?></div>
                                            <div class="mt-0.5 text-xs font-bold <?= ($admin_info['membership_active'] ?? false) ? 'text-emerald-600' : 'text-rose-600' ?>">
                                                <i class="fas <?= ($admin_info['membership_active'] ?? false) ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                                <span><?= ($admin_info['membership_active'] ?? false) ? esc(lang('UI.statusActive')) : esc(lang('UI.statusInactive')) ?></span>
                                            </div>
                                        </div>
                                        <div class="p-1 rounded cp-surface border cp-border">
                                            <div class="text-[10px] text-gray-500 font-medium"><?= esc(lang('UI.clinicAdminAuthorityActive')) ?></div>
                                            <div class="mt-0.5 text-xs font-bold <?= ($admin_info['has_admin_authority'] ?? false) ? 'text-emerald-600' : 'text-amber-600' ?>">
                                                <i class="fas <?= ($admin_info['has_admin_authority'] ?? false) ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                                                <span><?= ($admin_info['has_admin_authority'] ?? false) ? esc(lang('UI.statusManager')) : esc(lang('UI.statusMissing')) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metadata footer -->
                                    <div class="pt-1.5 flex items-center justify-between text-[11px] text-gray-500">
                                        <span>
                                            <?= esc(lang('UI.clinicAdminAssignedSince')) ?>:
                                            <strong><?= !empty($admin_info['assigned_since']) ? date('M j, Y', strtotime($admin_info['assigned_since'])) : '--' ?></strong>
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium <?= ($admin_info['designation_source'] ?? '') === 'designated' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-800 border border-amber-200' ?>">
                                            <?= ($admin_info['designation_source'] ?? '') === 'designated' ? esc(lang('UI.clinicAdminSourceDesignated')) : esc(lang('UI.legacyAdminCandidate')) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <?php if (!empty($admin_info['is_fallback'])): ?>
                                        <button type="button" onclick="openAssignAdminModal(this)" class="flex-1 px-2.5 py-1.5 cp-primary-surface hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center justify-center gap-1">
                                            <i class="fas fa-check-circle text-[10px]"></i>
                                            <span><?= esc(lang('UI.confirmDesignation')) ?></span>
                                        </button>
                                    <?php else: ?>
                                        <?php if (!($admin_info['is_healthy'] ?? false)): ?>
                                            <button type="button" onclick="openRestoreAdminModal(this)" class="flex-1 px-2.5 py-1.5 cp-primary-surface hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center justify-center gap-1">
                                                <i class="fas fa-wrench text-[10px]"></i>
                                                <span><?= esc(lang('UI.clinicAdminRestoreBtn')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <button type="button" onclick="openReplaceAdminModal(this)" class="flex-1 px-2.5 py-1.5 cp-soft-surface hover:bg-indigo-100 cp-accent text-xs font-bold rounded-lg border cp-border transition-colors flex items-center justify-center gap-1">
                                        <i class="fas fa-exchange-alt text-[10px]"></i>
                                        <span><?= esc(lang('UI.clinicAdminReplaceBtn')) ?></span>
                                    </button>

                                    <button type="button" onclick="openPasswordResetModal(this)" class="px-2.5 py-1.5 cp-surface hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg border cp-border transition-colors flex items-center justify-center gap-1" title="<?= esc(lang('UI.clinicAdminResetPassword')) ?>">
                                        <i class="fas fa-key text-[10px]"></i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <!-- Unassigned Empty State -->
                                <div class="text-center py-4 cp-surface cp-radius border border-dashed cp-border">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-amber-50 flex items-center justify-center text-amber-600 mb-2">
                                        <i class="fas fa-user-slash text-base"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-800 mb-1"><?= esc(lang('UI.clinicAdminWarningUnassigned')) ?></p>
                                    <button type="button" onclick="openAssignAdminModal(this)" class="mt-2 px-3 py-1.5 cp-primary-surface hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1">
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
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
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
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1"><?= esc(lang('V3Platform.currentPlan')) ?></label>
                            <p class="text-xl font-bold text-gray-900"><?= esc($plan_name ?? 'Unassigned') ?></p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1"><?= esc(lang('V3Platform.billingCycle')) ?></label>
                            <div class="flex space-x-4 text-sm">
                                <div>
                                    <span class="text-gray-400"><?= esc(lang('V3Platform.start')) ?></span>
                                    <span class="font-medium text-gray-900"><?= \App\Models\ClinicSubscriptionModel::formatDisplay($subscription['start_at'] ?? null) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-400"><?= esc(lang('V3Platform.end')) ?></span>
                                    <span class="font-medium text-gray-900"><?= \App\Models\ClinicSubscriptionModel::formatDisplay($subscription['end_at'] ?? null) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lifecycle Actions -->
                    <div class="mt-6 pt-6 border-t cp-border">
                        <h4 class="text-sm font-bold text-gray-700 mb-4"><?= esc(lang('V3Platform.subscriptionActions')) ?></h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Start/Change Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/start') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border">
                                <?= csrf_field() ?>
                                <label class="block text-xs font-medium text-gray-600 mb-2"><?= esc(lang('V3Platform.changePlan')) ?></label>
                                <div class="flex gap-2">
                                    <select name="plan_id" class="flex-1 rounded-lg cp-border text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                        <option value=""><?= esc(lang('V3Platform.selectPlan')) ?></option>
                                        <?php foreach (($plans ?? []) as $plan): ?>
                                            <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="px-3 py-2 cp-primary-surface text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-colors">
                                        <?= esc(lang('V3Platform.start2')) ?>
                                    </button>
                                </div>
                            </form>

                            <!-- Extend Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/extend') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border">
                                <?= csrf_field() ?>
                                <input type="hidden" name="expected_end_at" value="<?= ($subscription && $subscription['end_at'] !== null) ? esc($subscription['end_at']) : '__NULL__' ?>">
                                <input type="hidden" name="expected_updated_at" value="<?= ($subscription && $subscription['updated_at'] !== null) ? esc($subscription['updated_at']) : '__NULL__' ?>">
                                <label class="block text-xs font-medium text-gray-600 mb-2"><?= esc(lang('V3Platform.extendDuration')) ?></label>
                                <div class="flex gap-2">
                                    <select name="duration" class="flex-1 rounded-lg cp-border text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="monthly"><?= esc(lang('V3Platform.label1Month')) ?></option>
                                        <option value="yearly"><?= esc(lang('V3Platform.label1Year')) ?></option>
                                    </select>
                                    <button class="px-3 py-2 cp-primary-surface text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors">
                                        <?= esc(lang('V3Platform.extend')) ?>
                                    </button>
                                </div>
                            </form>

                            <!-- Cancel Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/cancel') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border" onsubmit="return confirm('Are you sure you want to cancel this subscription?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="expected_updated_at" value="<?= ($subscription && $subscription['updated_at'] !== null) ? esc($subscription['updated_at']) : '__NULL__' ?>">
                                <input type="hidden" name="canceled_reason" value="manual_cancel">
                                <label class="block text-xs font-medium text-gray-600 mb-2"><?= esc(lang('V3Platform.cancelSubscription')) ?></label>
                                <button type="submit" class="w-full px-3 py-2 bg-rose-600 text-white text-xs font-bold rounded-lg hover:bg-rose-700 transition-colors" <?= (!$subscription || !in_array($subscription['status'] ?? '', ['active', 'paused'], true)) ? 'disabled' : '' ?>>
                                    <?= esc(lang('V3Platform.cancelActiveSubscription')) ?>
                                </button>
                            </form>

                            <!-- Update Subscription Dates -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/update-dates') ?>" method="post" class="cp-surface p-4 rounded-lg border cp-border">
                                <?= csrf_field() ?>
                                <input type="hidden" name="expected_start_at" value="<?= ($subscription && $subscription['start_at'] !== null) ? esc($subscription['start_at']) : '__NULL__' ?>">
                                <input type="hidden" name="expected_end_at" value="<?= ($subscription && $subscription['end_at'] !== null) ? esc($subscription['end_at']) : '__NULL__' ?>">
                                <input type="hidden" name="expected_updated_at" value="<?= ($subscription && $subscription['updated_at'] !== null) ? esc($subscription['updated_at']) : '__NULL__' ?>">
                                <label class="block text-xs font-medium text-gray-600 mb-2"><?= esc(lang('V3Platform.updateDatesUtc')) ?></label>
                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1"><?= esc(lang('V3Platform.startDate')) ?></label>
                                        <input type="text" name="start_at" value="<?= esc($subscription['start_at'] ?? '') ?>" placeholder="YYYY-MM-DD HH:MM:SS" class="w-full rounded-lg cp-border text-xs focus:ring-amber-500 focus:border-amber-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1"><?= esc(lang('V3Platform.endDate')) ?></label>
                                        <input type="text" name="end_at" value="<?= esc($subscription['end_at'] ?? '') ?>" placeholder="YYYY-MM-DD HH:MM:SS" class="w-full rounded-lg cp-border text-xs focus:ring-amber-500 focus:border-amber-500">
                                    </div>
                                </div>
                                <button type="submit" class="w-full px-3 py-2 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 transition-colors" <?= (!$subscription || !in_array($subscription['status'] ?? '', ['active', 'paused'], true)) ? 'disabled' : '' ?>>
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
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-green-600 mr-2"></i>
                        <?= esc(lang('V3Platform.billingSnapshot')) ?>
                    </h3>
                    <a href="<?= base_url('controlplane/invoices') ?>" class="text-sm cp-accent hover:text-indigo-800 font-medium"><?= esc(lang('V3Platform.viewAllInvoices')) ?></a>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-red-50 p-4 rounded-lg border border-red-100 text-center">
                            <p class="text-xs font-bold text-red-500 uppercase tracking-wider"><?= esc(lang('V3Platform.outstanding')) ?></p>
                            <p class="text-2xl font-bold text-red-700 mt-1"><?= !empty($invoice_data_available) ? '$' . number_format($invoiceStats['outstanding'] ?? 0, 2) : '--' ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100 text-center">
                            <p class="text-xs font-bold text-green-500 uppercase tracking-wider"><?= esc(lang('V3Platform.revenueMo')) ?></p>
                            <p class="text-2xl font-bold text-green-700 mt-1"><?= !empty($invoice_data_available) ? '$' . number_format($invoiceStats['revenue_month'] ?? 0, 2) : '--' ?></p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-100 text-center">
                            <p class="text-xs font-bold text-orange-500 uppercase tracking-wider"><?= esc(lang('V3Platform.overdue')) ?></p>
                            <p class="text-2xl font-bold text-orange-700 mt-1"><?= !empty($invoice_data_available) ? (int)($invoiceStats['overdue'] ?? 0) : '--' ?></p>
                        </div>
                    </div>

                    <?php if (!($invoice_data_available ?? true)): ?>
                        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-amber-800 text-sm flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-amber-500"></i>
                            <span><?= esc(lang('UI.billingDataUnavailable')) ?></span>
                        </div>
                    <?php elseif (!empty($invoices)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-600">
                                <thead class="cp-surface text-gray-500 font-medium border-b cp-border">
                                    <tr>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.invoice')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.date')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.amount')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.status')) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach (array_slice($invoices, 0, 5) as $inv): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 font-medium cp-accent">
                                                <a href="<?= base_url('controlplane/invoices/' . $inv['id']) ?>"><?= esc($inv['invoice_number']) ?></a>
                                            </td>
                                            <td class="px-4 py-3"><?= esc($inv['issue_date']) ?></td>
                                            <td class="px-4 py-3 font-semibold text-gray-900">$<?= number_format($inv['total_amount'], 2) ?></td>
                                            <td class="px-4 py-3">
                                                <?php
                                                    $iStatus = $inv['status'] ?? 'draft';
                                                    $iClass = match($iStatus) {
                                                        'paid' => 'bg-green-100 text-green-700',
                                                        'overdue' => 'bg-red-100 text-red-700',
                                                        'sent' => 'bg-blue-100 text-blue-700',
                                                        default => 'bg-gray-100 text-gray-600'
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
                        <div class="text-center py-6 text-gray-400">
                            <p><?= esc(lang('V3Platform.noInvoicesFound')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wa-rollout-shell">
                <div class="wa-rollout-head">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 items-center justify-center cp-radius cp-soft-surface text-emerald-700">
                                <i class="fab fa-whatsapp text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-950"><?= esc(lang('V3Platform.whatsappRollout')) ?></h3>
                                <p class="mt-1 text-sm text-slate-600"><?= esc(lang('V3Platform.clinicLevelDeliveryPolicyForTheSharedSenderAndArabicTemplates')) ?></p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="wa-pill <?= !empty($wa_feature_enabled) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                <?= !empty($wa_feature_enabled) ? 'Plan Enabled' : 'Plan Disabled' ?>
                            </span>
                            <a href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>" class="inline-flex items-center cp-radius border cp-border cp-surface px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-100">
                                <?= esc(lang('V3Platform.openSharedTemplates')) ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="cp-radius border border-emerald-100 cp-soft-surface p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600"><?= esc(lang('V3Platform.clinicName')) ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= esc(($clinicSettingsSnapshot['clinic_name'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_name'] : ($clinic['name'] ?? 'Not set')) ?></p>
                        </div>
                        <div class="cp-radius border cp-border cp-soft-surface p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] cp-accent"><?= esc(lang('V3Platform.clinicPhone')) ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= esc(($clinicSettingsSnapshot['clinic_phone'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_phone'] : 'Not set') ?></p>
                        </div>
                        <div class="cp-radius border border-amber-100 bg-amber-50 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-amber-600"><?= esc(lang('V3Platform.clinicAddress')) ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= esc(($clinicSettingsSnapshot['clinic_address'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_address'] : 'Not set') ?></p>
                        </div>
                    </div>

                    <div class="cp-radius border cp-border cp-surface p-4">
                        <p class="text-sm font-bold text-slate-900"><?= esc(lang('V3Platform.howThisClinicInheritsWhatsapp')) ?></p>
                        <p class="mt-1 text-sm text-slate-600"><?= esc(lang('V3Platform.templatesAreManagedOnceInControlPlaneThisPageOnlyDecidesWhichOfTheFiveWorkflowsAreActiveForThisClinicAndWhatDeliveryLimitsApply')) ?></p>
                    </div>

                    <form action="<?= base_url('controlplane/whatsapp/policy') ?>" method="post" class="space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="clinic_id" value="<?= (int) $clinic['id'] ?>">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500"><?= esc(lang('V3Platform.dailyLimit')) ?></label>
                                <input type="number" name="daily_limit" value="<?= (int) ($waPolicy['daily_limit'] ?? 50) ?>" class="w-full rounded-lg cp-border text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500"><?= esc(lang('V3Platform.monthlyLimit')) ?></label>
                                <input type="number" name="monthly_limit" value="<?= (int) ($waPolicy['monthly_limit'] ?? 1000) ?>" class="w-full rounded-lg cp-border text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500"><?= esc(lang('V3Platform.perHourLimit')) ?></label>
                                <input type="number" name="per_hour_limit" value="<?= (int) ($waPolicy['per_hour_limit'] ?? 20) ?>" class="w-full rounded-lg cp-border text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500"><?= esc(lang('V3Platform.reminderLeadMinutes')) ?></label>
                                <input type="number" name="reminder_lead_time_minutes" value="<?= (int) ($waPolicy['reminder_lead_time_minutes'] ?? 1440) ?>" class="w-full rounded-lg cp-border text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <h4 class="text-base font-black text-slate-950"><?= esc(lang('V3Platform.templateTriggerToggles')) ?></h4>
                                <p class="mt-1 text-sm text-slate-600"><?= esc(lang('V3Platform.eachWorkflowIsControlledIndividuallyADisabledPlanOrGlobalSwitchWillLockTheClinicSwitch')) ?></p>
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
                                                <p class="font-bold text-slate-900"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                                <span class="wa-pill bg-slate-100 text-slate-700"><?= esc($displayTemplateName) ?></span>
                                                <?php if (!$platformEnabled): ?>
                                                    <span class="wa-pill bg-rose-100 text-rose-700"><?= esc(lang('V3Platform.globalOff')) ?></span>
                                                <?php endif; ?>
                                                <?php if (empty($wa_feature_enabled)): ?>
                                                    <span class="wa-pill bg-amber-100 text-amber-700"><?= esc(lang('V3Platform.planOff')) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mt-1 text-sm text-slate-600"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-bold uppercase tracking-[0.12em] <?= $clinicEnabled ? 'text-emerald-600' : 'text-slate-500' ?>">
                                                <?= $clinicEnabled ? 'On' : 'Off' ?>
                                            </span>
                                            <input type="checkbox" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1" class="ui-toggle" <?= $clinicEnabled ? 'checked' : '' ?> <?= !$platformEnabled || empty($wa_feature_enabled) ? 'disabled' : '' ?>>
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
                                <input type="checkbox" name="suspended" value="1" class="ui-toggle" <?= !empty($waPolicy['suspended']) ? 'checked' : '' ?>>
                                <span>
                                    <span class="block text-sm font-bold text-slate-900"><?= esc(lang('V3Platform.emergencyPause')) ?></span>
                                    <span class="mt-1 block text-xs text-slate-500"><?= esc(lang('V3Platform.immediatelyStopAutomatedSendsForThisClinic')) ?></span>
                                </span>
                            </label>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500"><?= esc(lang('V3Platform.pauseReason')) ?></label>
                                <input type="text" name="suspended_reason" value="<?= esc($waPolicy['suspended_reason'] ?? '') ?>" class="w-full rounded-lg cp-border text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="<?= esc(lang('V3Platform.optionalReasonForAuditTrail'), 'attr') ?>">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="cp-radius cp-primary-surface px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-emerald-700">
                                    <?= esc(lang('V3Platform.saveWhatsappPolicy')) ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 cp-radius border border-red-200 p-6">
                <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= esc(lang('V3Platform.dangerZone')) ?>
                </h3>

                <div class="flex flex-wrap gap-4">
                        <p class="mb-3 text-sm text-slate-600" role="note"><?= esc(lang('IonAuth.clinic_delete_history_protected')) ?></p>
                    <?php if (($clinic['status'] ?? 'inactive') === 'active'): ?>
                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="px-4 py-2 bg-yellow-500 text-white text-sm font-bold rounded-lg hover:bg-yellow-600 transition-colors">
                                <?= esc(lang('V3Platform.suspendClinic')) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="px-4 py-2 bg-green-500 text-white text-sm font-bold rounded-lg hover:bg-green-600 transition-colors">
                                <?= esc(lang('V3Platform.reactivateClinic')) ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <button type="button" class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition-colors" onclick="openTerminateModal()">
                        <?= esc(lang('V3Platform.terminateClinic')) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terminate Modal -->
<div id="terminateModal" role="dialog" aria-modal="true" aria-labelledby="terminateModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center justify-between">
            <h3 id="terminateModalTitle" class="text-lg font-bold text-red-800"><?= esc(lang('V3Platform.confirmTermination')) ?></h3>
            <button onclick="closeTerminateModal()" class="text-gray-400 hover:text-gray-600" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">
                <?= esc(lang('V3Platform.thisWillDisableAccessAndCancelTheSubscriptionFor')) ?> <strong class="text-gray-900"><?= esc($clinic['name']) ?></strong>.
                <br><?= esc(lang('V3Platform.pleaseTypeTheClinicNameBelowToConfirm')) ?>
            </p>
            <input type="text" id="terminateConfirmInput" class="w-full px-4 py-2 border cp-border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4" placeholder="<?= esc($clinic['name']) ?>" oninput="checkTerminateInput()">

            <form id="terminateForm" action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/terminate') ?>" method="post">
                <?= csrf_field() ?>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200" onclick="closeTerminateModal()"><?= esc(lang('V3Platform.cancel')) ?></button>
                    <button type="submit" id="terminateBtn" class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 opacity-50 cursor-not-allowed" disabled><?= esc(lang('V3Platform.terminate')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordResetModal" role="dialog" aria-modal="true" aria-labelledby="passwordResetModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 id="passwordResetModalTitle" class="text-lg font-bold cp-accent"><?= esc(lang('V3Platform.resetAdminPassword')) ?></h3>
            <button onclick="closePasswordResetModal()" class="text-gray-400 hover:text-gray-600" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">
                <?= esc(lang('V3Platform.enterANewPasswordFor')) ?> <strong><?= esc($clinic['admin_email'] ?? 'the admin') ?></strong>.
            </p>
            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reset-admin-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1"><?= esc(lang('V3Platform.newPassword')) ?></label>
                    <input type="password" name="password" class="w-full px-4 py-2 border cp-border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required minlength="8" placeholder="<?= esc(lang('V3Platform.minimum8Characters'), 'attr') ?>">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200" onclick="closePasswordResetModal()"><?= esc(lang('V3Platform.cancel')) ?></button>
                    <button type="submit" class="px-4 py-2 cp-primary-surface text-white font-bold rounded-lg hover:bg-indigo-700"><?= esc(lang('V3Platform.resetPassword')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Administrator Modal -->
<div id="assignAdminModal" role="dialog" aria-modal="true" aria-labelledby="assignAdminModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 id="assignAdminModalTitle" class="text-lg font-bold cp-accent flex items-center gap-2">
                <i class="fas fa-user-plus cp-accent"></i>
                <span><?= esc(!empty($admin_info['is_fallback']) ? lang('UI.confirmDesignation') : lang('UI.clinicAdminModalAssignTitle')) ?></span>
            </h3>
            <button onclick="closeAssignAdminModal()" class="text-gray-400 hover:text-gray-600" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/admin/assign') ?>" method="post" class="p-6">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1"><?= esc(lang('UI.clinicAdminSelectUser')) ?></label>
                <?php if (!empty($eligible_admin_users)): ?>
                    <select name="user_id" class="w-full px-3 py-2 border cp-border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
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
                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg">
                        <?= esc(lang('UI.clinicAdminNoEligibleUsers')) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex justify-end space-x-3 rtl:space-x-reverse">
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 text-sm" onclick="closeAssignAdminModal()"><?= esc(lang('UI.cancel')) ?></button>
                <button type="submit" class="px-4 py-2 cp-primary-surface text-white font-bold rounded-lg hover:bg-indigo-700 text-sm" <?= empty($eligible_admin_users) ? 'disabled' : '' ?>>
                    <?= esc(!empty($admin_info['is_fallback']) ? lang('UI.confirmDesignation') : lang('UI.clinicAdminConfirmAssign')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Replace Administrator Modal -->
<div id="replaceAdminModal" role="dialog" aria-modal="true" aria-labelledby="replaceAdminModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 id="replaceAdminModalTitle" class="text-lg font-bold cp-accent flex items-center gap-2">
                <i class="fas fa-exchange-alt cp-accent"></i>
                <span><?= esc(lang('UI.clinicAdminModalReplaceTitle')) ?></span>
            </h3>
            <button onclick="closeReplaceAdminModal()" class="text-gray-400 hover:text-gray-600" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/admin/replace') ?>" method="post" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="expected_admin_id" value="<?= (int) ($admin_info['admin_user']['id'] ?? 0) ?>">
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1"><?= esc(lang('UI.clinicAdminSelectUser')) ?></label>
                <?php
                $currentAdminId = (int) ($admin_info['admin_user']['id'] ?? 0);
                $replacementCandidates = array_filter($eligible_admin_users ?? [], function($eu) use ($currentAdminId) {
                    return (int) $eu['id'] !== $currentAdminId;
                });
                ?>
                <?php if (!empty($replacementCandidates)): ?>
                    <select name="user_id" class="w-full px-3 py-2 border cp-border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
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
                    <div class="p-3 bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-lg">
                        <?= esc(lang('UI.clinicAdminNoEligibleUsers')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-5 space-y-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1"><?= esc(lang('UI.clinicAdminPreviousActionLabel')) ?></label>
                <div class="space-y-2 border cp-border rounded-lg p-3 cp-surface">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="previous_admin_action" value="demote_to_staff" checked class="mt-0.5 w-4 h-4 cp-accent cp-border focus:ring-indigo-500">
                        <span class="text-xs text-gray-700"><?= esc(lang('UI.clinicAdminDemotePrevious')) ?></span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="previous_admin_action" value="keep_role" class="mt-0.5 w-4 h-4 cp-accent cp-border focus:ring-indigo-500">
                        <span class="text-xs text-gray-700"><?= esc(lang('UI.clinicAdminKeepRolePrevious')) ?></span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="previous_admin_action" value="deactivate_membership" class="mt-0.5 w-4 h-4 cp-accent cp-border focus:ring-indigo-500">
                        <span class="text-xs text-gray-700"><?= esc(lang('UI.clinicAdminDeactivateMembershipPrevious')) ?></span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 rtl:space-x-reverse">
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 text-sm" onclick="closeReplaceAdminModal()"><?= esc(lang('UI.cancel')) ?></button>
                <button type="submit" class="px-4 py-2 cp-primary-surface text-white font-bold rounded-lg hover:bg-indigo-700 text-sm" <?= empty($replacementCandidates) ? 'disabled' : '' ?>>
                    <?= esc(lang('UI.clinicAdminConfirmReplace')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Restore Administrator Modal -->
<div id="restoreAdminModal" role="dialog" aria-modal="true" aria-labelledby="restoreAdminModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b border-emerald-100 flex items-center justify-between">
            <h3 id="restoreAdminModalTitle" class="text-lg font-bold text-emerald-900 flex items-center gap-2">
                <i class="fas fa-wrench text-emerald-600"></i>
                <span><?= esc(lang('UI.clinicAdminModalRestoreTitle')) ?></span>
            </h3>
            <button onclick="closeRestoreAdminModal()" class="text-gray-400 hover:text-gray-600" aria-label="<?= esc(lang('V3Platform.closeDialog'), 'attr') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/admin/restore') ?>" method="post" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= (int) ($admin_info['admin_user']['id'] ?? 0) ?>">
            <input type="hidden" name="expected_designated_admin_id" value="<?= (int) ($admin_info['admin_user']['id'] ?? 0) ?>">

            <p class="text-sm text-gray-600 mb-4">
                <?= esc(lang('UI.clinicAdminRestoreHelp')) ?>
            </p>

            <?php if (!($admin_info['account_active'] ?? true)): ?>
                <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-xs">
                    <div class="font-bold flex items-center gap-1 mb-1">
                        <i class="fas fa-exclamation-circle text-rose-600"></i>
                        <span><?= esc(lang('UI.clinicAdminAccountDeactivatedTitle')) ?></span>
                    </div>
                    <p class="mb-2"><?= esc(lang('UI.clinicAdminAccountDeactivatedHelp')) ?></p>
                    <a href="<?= base_url('controlplane/users') ?>" class="inline-flex items-center gap-1 font-bold text-rose-700 hover:text-rose-900 underline text-xs">
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                        <span><?= esc(lang('UI.clinicAdminGoToUsers')) ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($admin_info['admin_user'])): ?>
                <div class="p-3 cp-surface rounded-lg border cp-border text-xs mb-4">
                    <strong><?= esc(!empty($admin_info['admin_name']) ? $admin_info['admin_name'] : (!empty($admin_info['admin_user']['email']) ? $admin_info['admin_user']['email'] : 'Administrator')) ?></strong>
                    <div class="text-gray-500"><?= esc($admin_info['admin_user']['email'] ?? '') ?></div>
                </div>
            <?php endif; ?>

            <div class="flex justify-end space-x-3 rtl:space-x-reverse">
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 text-sm" onclick="closeRestoreAdminModal()"><?= esc(lang('UI.cancel')) ?></button>
                <button type="submit" <?= !($admin_info['account_active'] ?? true) ? 'disabled title="' . esc(lang('UI.clinicAdminReactivateAccountFirst')) . '" class="px-4 py-2 bg-gray-300 text-gray-500 font-bold rounded-lg cursor-not-allowed text-sm"' : 'class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 text-sm"' ?>>
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
