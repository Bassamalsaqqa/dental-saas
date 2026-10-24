<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="v3-platform-workspace px-6 py-8">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="cp-title font-bold v3-text-ink mb-3"><?= esc(lang('V3Platform.userProfile')) ?></h1>
                <p class="v3-text-muted text-lg"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: esc($userProfile['email']) ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('controlplane/users') ?>" class="inline-flex items-center px-4 py-2 v3-bg-canvas v3-text-on-primary text-sm font-medium rounded-lg v3-bg-forest focus:outline-none focus:ring-2 v3-focus-control focus:ring-offset-2 transition-all duration-200 v3-btn v3-btn--secondary">
                    <i class="fas fa-arrow-left mr-2"></i><?= esc(lang('V3Platform.backToUsers')) ?>
                </a>
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
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="cp-surface cp-radius border cp-border overflow-hidden">
                <!-- User Header -->
                <div class="cp-panel-padding v3-text-on-primary">
                    <div class="text-center">
                        <?php
                            $initials = ui_initials($userProfile['first_name'] ?? '', $userProfile['last_name'] ?? '', 'U');
                        ?>
                        <div class="w-24 h-24 cp-surface cp-radius flex items-center justify-center mx-auto mb-4">
                            <span class="v3-text-on-primary cp-title font-bold"><?= $initials ?></span>
                        </div>
                        <h2 class="text-2xl font-bold"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: 'No Name' ?></h2>
                        <p class="cp-accent text-lg"><?= esc(lang('V3Platform.id')) ?> <?= esc($userProfile['id']) ?></p>

                        <div class="mt-4">
                            <?php
                                $isActive = ($userProfile['active'] == 1);
                                $badgeClass = $isActive ? 'v3-bg-primary v3-text-primary' : 'v3-bg-danger v3-text-danger';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?= $badgeClass ?>">
                                <?= $isActive ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full cp-soft-surface flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope cp-accent text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium v3-text-muted"><?= esc(lang('V3Platform.emailAddress')) ?></p>
                                <p class="text-sm font-semibold v3-text-ink truncate"><?= esc($userProfile['email']) ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full cp-soft-surface flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar cp-accent text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium v3-text-muted"><?= esc(lang('V3Platform.memberSince')) ?></p>
                                <p class="text-sm font-semibold v3-text-ink"><?= date('M j, Y', $userProfile['created_on']) ?></p>
                            </div>
                        </div>

                        <?php if(!empty($userProfile['phone'])): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full cp-soft-surface flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone v3-text-primary text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium v3-text-muted"><?= esc(lang('V3Platform.phone')) ?></p>
                                <p class="text-sm font-semibold v3-text-ink"><?= esc($userProfile['phone']) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Clinic Memberships -->
            <div class="cp-surface cp-radius border cp-border overflow-hidden">
                <div class="px-6 py-4 border-b cp-border flex justify-between items-center cp-surface">
                    <h3 class="text-lg font-bold v3-text-ink flex items-center">
                        <i class="fas fa-hospital cp-accent mr-2"></i>
                        <?= esc(lang('V3Platform.clinicMemberships')) ?>
                    </h3>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full cp-soft-surface cp-accent">
                        <?= count($memberships) ?> <?= esc(lang('V3Platform.clinics')) ?>
                    </span>
                </div>

                <div class="p-6">
                    <?php if (empty($memberships)): ?>
                        <div class="text-center py-6 v3-text-muted">
                            <p><?= esc(lang('V3Platform.noClinicMembershipsFound')) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm v3-text-muted">
                                <thead class="cp-surface v3-text-muted font-medium border-b cp-border">
                                    <tr>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.clinicName')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.joined')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.clinicRole')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('UI.clinicAdminTitle')) ?></th>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.actions')) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y v3-border-default">
                                    <?php foreach ($memberships as $membership): ?>
                                        <tr class="v3-hover-soft transition-colors">
                                            <td class="px-4 py-3 font-medium cp-accent">
                                                <a href="<?= base_url('controlplane/clinics/' . $membership['clinic_id']) ?>"><?= esc($membership['clinic_name']) ?></a>
                                            </td>
                                            <td class="px-4 py-3"><?= date('M j, Y', strtotime($membership['created_at'])) ?></td>
                                            <td class="px-4 py-3">
                                                <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/memberships/' . $membership['id'] . '/update-role') ?>" method="post" class="flex items-center gap-2">
                                                    <?= csrf_field() ?>
                                                    <select name="role_id" class="px-3 py-1.5 border cp-border rounded-lg text-xs cp-surface focus:ring-2 v3-focus-control v3-focus-control v3-select">
                                                        <?php foreach (($rolesByClinic[(int)$membership['clinic_id']] ?? []) as $role): ?>
                                                            <option value="<?= (int) $role['id'] ?>" <?= ((int) $membership['role_id'] === (int) $role['id']) ? 'selected' : '' ?>>
                                                                <?= esc($role['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="px-3 py-2 rounded-lg cp-soft-surface cp-accent text-xs font-bold v3-hover-primary transition-colors v3-btn v3-btn--primary">
                                                        <?= esc(lang('V3Platform.update')) ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?php if (!empty($membership['is_designated_admin'])): ?>
                                                    <?php if (!empty($membership['admin_is_healthy'])): ?>
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold v3-bg-paid v3-text-primary">
                                                            <i class="fas fa-shield-alt me-1"></i><?= esc(lang('UI.primaryAdmin')) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <div class="space-y-1">
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold v3-bg-waiting v3-text-warning">
                                                                <i class="fas fa-exclamation-triangle me-1"></i><?= esc($membership['admin_info']['state_label'] ?? 'Degraded') ?>
                                                            </span>
                                                            <?php if ((int)($userProfile['active'] ?? 0) === 1): ?>
                                                                <button type="button"
                                                                        class="block px-2.5 py-1 rounded-lg cp-soft-surface cp-accent text-xs font-bold border cp-border v3-hover-primary transition-colors v3-btn v3-btn--secondary"
                                                                        onclick="openRestoreModal(<?= (int)$membership['clinic_id'] ?>, '<?= esc($membership['clinic_name'], 'js') ?>', <?= (int)$userProfile['id'] ?>, <?= (int)($membership['admin_info']['admin_user']['id'] ?? $userProfile['id']) ?>, this)">
                                                                    <i class="fas fa-wrench me-1"></i><?= esc(lang('UI.restoreAuthority')) ?>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php elseif (!empty($membership['is_fallback_candidate'])): ?>
                                                    <div class="space-y-1">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold v3-bg-waiting v3-text-warning border v3-border-default">
                                                            <i class="fas fa-clock me-1"></i><?= esc(lang('UI.legacyAdminCandidate')) ?>
                                                        </span>
                                                        <?php if ((int)($userProfile['active'] ?? 0) === 1): ?>
                                                            <button type="button"
                                                                    class="block px-2.5 py-1 rounded-lg cp-soft-surface cp-accent text-xs font-bold border cp-border v3-hover-primary transition-colors v3-btn v3-btn--secondary"
                                                                    onclick="openDesignateModal({
                                                                        clinicId: <?= (int)$membership['clinic_id'] ?>,
                                                                        clinicName: '<?= esc($membership['clinic_name'], 'js') ?>',
                                                                        hasAdmin: false,
                                                                        isFallback: true,
                                                                        currentAdminId: 0,
                                                                        currentAdminName: '',
                                                                        currentAdminEmail: ''
                                                                    }, this)">
                                                                <i class="fas fa-user-check me-1"></i><?= esc(lang('UI.confirmDesignation')) ?>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-xs v3-text-muted italic block"><?= esc(lang('UI.accountInactive')) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?php if ((int)($userProfile['active'] ?? 0) !== 1): ?>
                                                        <span class="text-xs v3-text-muted italic"><?= esc(lang('UI.accountInactive')) ?></span>
                                                    <?php else: ?>
                                                        <button type="button"
                                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg cp-soft-surface cp-accent text-xs font-bold border cp-border v3-hover-primary transition-colors v3-btn v3-btn--secondary"
                                                                onclick="openDesignateModal({
                                                                    clinicId: <?= (int)$membership['clinic_id'] ?>,
                                                                    clinicName: '<?= esc($membership['clinic_name'], 'js') ?>',
                                                                    hasAdmin: <?= !empty($membership['admin_info']['has_admin']) ? 'true' : 'false' ?>,
                                                                    isFallback: <?= !empty($membership['admin_info']['is_fallback']) ? 'true' : 'false' ?>,
                                                                    currentAdminId: <?= (int)($membership['admin_info']['admin_user']['id'] ?? 0) ?>,
                                                                    currentAdminName: '<?= esc($membership['admin_info']['admin_name'] ?? 'None', 'js') ?>',
                                                                    currentAdminEmail: '<?= esc($membership['admin_info']['admin_email'] ?? 'None', 'js') ?>'
                                                                }, this)">
                                                            <i class="fas fa-user-shield me-1"></i><?= esc(lang('UI.makeAdministrator')) ?>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3">
                                                <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/memberships/' . $membership['id'] . '/remove') ?>" method="post" onsubmit="return confirm('Remove this user from this clinic?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="px-3 py-2 rounded-lg v3-bg-alert v3-text-danger text-xs font-bold v3-hover-danger transition-colors v3-btn v3-btn--danger">
                                                        <?= esc(lang('V3Platform.remove')) ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6 border-t cp-border pt-6">
                        <h4 class="text-sm font-bold v3-text-ink mb-3"><?= esc(lang('V3Platform.addToAnotherClinic')) ?></h4>
                        <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/memberships/add') ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-xs font-semibold v3-text-muted mb-1"><?= esc(lang('V3Platform.clinic')) ?></label>
                                <select id="addMembershipClinic" name="clinic_id" class="w-full px-3 py-2 border cp-border rounded-lg text-sm cp-surface v3-select" required>
                                    <?php if (!empty($assignableClinics)): ?>
                                        <option value=""><?= esc(lang('V3Platform.selectClinic2')) ?></option>
                                        <?php foreach ($assignableClinics as $clinic): ?>
                                            <option value="<?= (int) $clinic['id'] ?>"><?= esc($clinic['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value=""><?= esc(lang('V3Platform.noAdditionalClinicsAvailable')) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold v3-text-muted mb-1"><?= esc(lang('V3Platform.role')) ?></label>
                                <select id="addMembershipRole" name="role_id" class="w-full px-3 py-2 border cp-border rounded-lg text-sm cp-surface v3-select" required disabled>
                                    <option value=""><?= esc(lang('V3Platform.selectRole')) ?></option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="w-full md:w-auto px-4 py-2 cp-primary-surface v3-text-on-primary text-sm font-bold rounded-lg v3-hover-primary transition-colors disabled:opacity-50 disabled:cursor-not-allowed v3-btn v3-btn--primary" <?= empty($assignableClinics) ? 'disabled' : '' ?>>
                                    <?= esc(lang('V3Platform.addMembership')) ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Global Roles -->
            <div class="cp-surface cp-radius border cp-border overflow-hidden">
                <div class="px-6 py-4 border-b cp-border cp-surface">
                    <h3 class="text-lg font-bold v3-text-ink flex items-center">
                        <i class="fas fa-shield-alt cp-accent mr-2"></i>
                        <?= esc(lang('V3Platform.globalRoles')) ?>
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($globalRoles)): ?>
                        <p class="v3-text-muted text-sm"><?= esc(lang('V3Platform.noGlobalRolesAssigned')) ?></p>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($globalRoles as $role): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider cp-soft-surface cp-accent border cp-border">
                                    <?= esc($role['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="v3-bg-alert cp-radius border v3-border-default p-6">
                <h3 class="text-lg font-bold v3-text-danger mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= esc(lang('V3Platform.dangerZone')) ?>
                </h3>

                <div class="flex flex-wrap gap-4">
                    <!-- Toggle Status -->
                    <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/toggle-status') ?>" method="post">
                        <?= csrf_field() ?>
                        <?php if ($userProfile['active'] == 1): ?>
                            <button class="px-4 py-2 v3-bg-warning v3-text-on-primary text-sm font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--secondary">
                                <?= esc(lang('V3Platform.deactivateUser')) ?>
                            </button>
                        <?php else: ?>
                            <button class="px-4 py-2 v3-bg-primary v3-text-on-primary text-sm font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--primary">
                                <?= esc(lang('V3Platform.activateUser')) ?>
                            </button>
                        <?php endif; ?>
                    </form>

                    <!-- Reset Password Trigger -->
                    <button type="button" class="px-4 py-2 cp-primary-surface v3-text-on-primary text-sm font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--secondary" onclick="openPasswordResetModal()">
                        <?= esc(lang('V3Platform.resetPassword')) ?>
                    </button>

                    <button type="button" disabled class="px-4 py-2 v3-bg-soft v3-text-muted text-sm rounded-lg cursor-not-allowed v3-btn v3-btn--secondary"><?= esc(lang('IonAuth.delete_disabled_label')) ?></button>
                    <p class="w-full text-sm v3-text-muted" role="note"><?= esc(lang('IonAuth.delete_history_protected')) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordResetModal" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 class="text-lg font-bold cp-accent"><?= esc(lang('V3Platform.resetUserPassword')) ?></h3>
            <button onclick="closePasswordResetModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="v3-text-muted mb-4">
                <?= esc(lang('V3Platform.enterANewPasswordFor')) ?> <strong><?= esc($userProfile['email']) ?></strong>.
            </p>
            <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/reset-password') ?>" method="post">
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

<!-- Designate Administrator Modal -->
<div id="designateAdminModal" role="dialog" aria-modal="true" aria-labelledby="designateModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4" onclick="handleBackdropClick(event, 'designateAdminModal', closeDesignateModal)">
    <div class="w-full max-w-lg cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100" onclick="event.stopPropagation()">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 class="text-lg font-bold cp-accent flex items-center gap-2">
                <i class="fas fa-user-shield cp-accent"></i>
                <span id="designateModalTitle"><?= esc(lang('UI.clinicAdminModalAssignTitle')) ?></span>
            </h3>
            <button type="button" onclick="closeDesignateModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary" aria-label="<?= esc(lang('UI.cancel')) ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <!-- Explainer banner (Requirement 108) -->
            <div class="mb-4 cp-radius cp-soft-surface border cp-border p-3.5 text-xs cp-accent flex items-start gap-2.5">
                <i class="fas fa-info-circle cp-accent mt-0.5 flex-shrink-0"></i>
                <span><?= esc(lang('UI.clinicAdminOneAdminExplainer')) ?></span>
            </div>

            <div class="mb-4 cp-surface cp-radius p-3 border cp-border text-sm space-y-1">
                <div><span class="text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('UI.clinicLabel')) ?></span> <strong id="designateClinicName" class="v3-text-ink"></strong></div>
                <div><span class="text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('UI.candidateLabel')) ?></span> <strong class="v3-text-ink"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: esc($userProfile['email']) ?></strong> (<?= esc($userProfile['email']) ?>)</div>
            </div>

            <form id="designateAdminForm" action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/admin/designate') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="clinic_id" id="designateClinicId" value="">
                <input type="hidden" name="expected_admin_id" id="designateExpectedAdminId" value="">

                <!-- Replacement Section (shown only when clinic has another admin) -->
                <div id="replacementOptionsSection" class="mb-4 hidden">
                    <div class="mb-3 cp-radius v3-bg-waiting border v3-border-default p-3 text-xs v3-text-warning">
                        <span class="font-bold"><?= esc(lang('UI.currentAdministratorLabel')) ?></span> <span id="designateCurrentAdminInfo"></span>
                    </div>

                    <label class="block text-xs font-bold v3-text-ink uppercase tracking-wide mb-2"><?= esc(lang('UI.clinicAdminPreviousActionLabel')) ?></label>
                    <div class="space-y-2 border cp-border cp-radius p-3 cp-surface text-sm">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="previous_admin_action" value="demote_to_staff" checked class="mt-0.5 cp-accent v3-focus-control v3-radio">
                            <div>
                                <span class="font-semibold v3-text-ink"><?= esc(lang('UI.clinicAdminDemotePrevious')) ?></span>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="previous_admin_action" value="keep_role" class="mt-0.5 cp-accent v3-focus-control v3-radio">
                            <div>
                                <span class="font-semibold v3-text-ink"><?= esc(lang('UI.clinicAdminKeepRolePrevious')) ?></span>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="previous_admin_action" value="deactivate_membership" class="mt-0.5 cp-accent v3-focus-control v3-radio">
                            <div>
                                <span class="font-semibold v3-text-ink"><?= esc(lang('UI.clinicAdminDeactivateMembershipPrevious')) ?></span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold v3-text-muted uppercase mb-1"><?= esc(lang('UI.clinicAdminReason')) ?></label>
                    <input type="text" name="reason" id="designateReasonInput" class="w-full px-4 py-2 border cp-border rounded-lg text-sm focus:ring-2 v3-focus-control v3-focus-control v3-input" placeholder="<?= esc(lang('UI.clinicAdminReasonPlaceholder')) ?>">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 v3-bg-soft v3-text-ink font-bold rounded-lg v3-hover-soft transition-colors v3-btn v3-btn--secondary" onclick="closeDesignateModal()"><?= esc(lang('UI.cancel')) ?></button>
                    <button type="submit" class="px-4 py-2 cp-primary-surface v3-text-on-primary font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--primary">
                        <span id="designateSubmitLabel"><?= esc(lang('UI.clinicAdminConfirmAssign')) ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restore Administrator Modal -->
<div id="restoreAdminModal" role="dialog" aria-modal="true" aria-labelledby="restoreModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center v3-backdrop px-4" onclick="handleBackdropClick(event, 'restoreAdminModal', closeRestoreModal)">
    <div class="w-full max-w-md cp-surface cp-radius border cp-border overflow-hidden transform transition-all scale-100" onclick="event.stopPropagation()">
        <div class="cp-soft-surface px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 class="text-lg font-bold cp-accent flex items-center gap-2">
                <i class="fas fa-wrench cp-accent"></i>
                <span id="restoreModalTitle"><?= esc(lang('UI.clinicAdminModalRestoreTitle')) ?></span>
            </h3>
            <button type="button" onclick="closeRestoreModal()" class="v3-text-muted v3-hover-text-muted v3-btn v3-btn--secondary" aria-label="<?= esc(lang('UI.cancel')) ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm v3-text-muted mb-4">
                <?= esc(lang('UI.clinicAdminRestoreHelp')) ?>
            </p>
            <div class="mb-4 cp-surface cp-radius p-3 border cp-border text-sm space-y-1">
                <div><span class="text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('UI.clinicLabel')) ?></span> <strong id="restoreClinicName" class="v3-text-ink"></strong></div>
                <div><span class="text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('UI.clinicAdminTitle')) ?>:</span> <strong class="v3-text-ink"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: esc($userProfile['email']) ?></strong></div>
            </div>

            <form id="restoreAdminForm" action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/admin/restore') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="clinic_id" id="restoreClinicId" value="">
                <input type="hidden" name="expected_designated_admin_id" id="restoreExpectedDesignatedId" value="">

                <div class="mb-5">
                    <label class="block text-xs font-bold v3-text-muted uppercase mb-1"><?= esc(lang('UI.clinicAdminReason')) ?></label>
                    <input type="text" name="reason" id="restoreReasonInput" class="w-full px-4 py-2 border cp-border rounded-lg text-sm focus:ring-2 v3-focus-control v3-focus-control v3-input" placeholder="<?= esc(lang('UI.clinicAdminRestoreReasonPlaceholder')) ?>">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 v3-bg-soft v3-text-ink font-bold rounded-lg v3-hover-soft transition-colors v3-btn v3-btn--secondary" onclick="closeRestoreModal()"><?= esc(lang('UI.cancel')) ?></button>
                    <button type="submit" class="px-4 py-2 cp-primary-surface v3-text-on-primary font-bold rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--primary">
                        <?= esc(lang('UI.clinicAdminConfirmRestore')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const rolesByClinic = <?= json_encode($rolesByClinic ?? [], JSON_UNESCAPED_UNICODE) ?>;

    function hydrateAddMembershipRoles(clinicId) {
        const roleSelect = document.getElementById('addMembershipRole');
        if (!roleSelect) return;

        roleSelect.replaceChildren();
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = 'Select role';
        roleSelect.appendChild(placeholderOption);
        if (!clinicId || !rolesByClinic[clinicId] || !rolesByClinic[clinicId].length) {
            roleSelect.disabled = true;
            return;
        }

        rolesByClinic[clinicId].forEach((role) => {
            const option = document.createElement('option');
            option.value = role.id;
            option.textContent = role.name;
            roleSelect.appendChild(option);
        });
        roleSelect.disabled = false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const clinicSelect = document.getElementById('addMembershipClinic');
        if (!clinicSelect) return;
        clinicSelect.addEventListener('change', function () {
            hydrateAddMembershipRoles(this.value);
        });
    });

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
            return;
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

    function handleBackdropClick(event, modalId, closeFn) {
        if (event.target && event.target.id === modalId) {
            closeFn();
        }
    }

    function openDesignateModal(opts, triggerElement) {
        const modal = document.getElementById('designateAdminModal');
        if (!modal) return;

        document.getElementById('designateClinicId').value = opts.clinicId;
        document.getElementById('designateClinicName').textContent = opts.clinicName;

        const replacementSection = document.getElementById('replacementOptionsSection');
        const modalTitle = document.getElementById('designateModalTitle');
        const submitLabel = document.getElementById('designateSubmitLabel');
        const expectedInput = document.getElementById('designateExpectedAdminId');

        if (opts.isFallback) {
            replacementSection.classList.add('hidden');
            expectedInput.value = '';
            modalTitle.textContent = '<?= esc(lang('UI.confirmDesignation'), 'js') ?>';
            submitLabel.textContent = '<?= esc(lang('UI.confirmDesignation'), 'js') ?>';
        } else if (opts.hasAdmin && opts.currentAdminId) {
            replacementSection.classList.remove('hidden');
            expectedInput.value = opts.currentAdminId;
            document.getElementById('designateCurrentAdminInfo').textContent = `${opts.currentAdminName} (${opts.currentAdminEmail})`;
            modalTitle.textContent = '<?= esc(lang('UI.clinicAdminModalReplaceTitle'), 'js') ?>';
            submitLabel.textContent = '<?= esc(lang('UI.clinicAdminConfirmReplace'), 'js') ?>';
        } else {
            replacementSection.classList.add('hidden');
            expectedInput.value = '';
            modalTitle.textContent = '<?= esc(lang('UI.clinicAdminModalAssignTitle'), 'js') ?>';
            submitLabel.textContent = '<?= esc(lang('UI.clinicAdminConfirmAssign'), 'js') ?>';
        }

        showDialog(modal, triggerElement, '#designateReasonInput');
    }

    function closeDesignateModal() {
        const modal = document.getElementById('designateAdminModal');
        if (!modal || modal.classList.contains('hidden')) return;

        document.getElementById('designateClinicId').value = '';
        document.getElementById('designateExpectedAdminId').value = '';
        document.getElementById('replacementOptionsSection').classList.add('hidden');

        hideDialog(modal);
    }

    function openRestoreModal(clinicId, clinicName, userId, expectedAdminId, triggerElement) {
        const modal = document.getElementById('restoreAdminModal');
        if (!modal) return;

        document.getElementById('restoreClinicId').value = clinicId;
        document.getElementById('restoreClinicName').textContent = clinicName;
        document.getElementById('restoreExpectedDesignatedId').value = expectedAdminId || userId;

        showDialog(modal, triggerElement, '#restoreReasonInput');
    }

    function closeRestoreModal() {
        const modal = document.getElementById('restoreAdminModal');
        if (!modal || modal.classList.contains('hidden')) return;

        document.getElementById('restoreClinicId').value = '';
        document.getElementById('restoreExpectedDesignatedId').value = '';

        hideDialog(modal);
    }

    function openPasswordResetModal(triggerElement) {
        const modal = document.getElementById('passwordResetModal');
        showDialog(modal, triggerElement, 'input[name="password"]');
    }

    function closePasswordResetModal() {
        const modal = document.getElementById('passwordResetModal');
        hideDialog(modal);
    }

    document.addEventListener('keydown', function(event) {
        if (!activeModal || activeModal.classList.contains('hidden')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            if (activeModal.id === 'designateAdminModal') closeDesignateModal();
            else if (activeModal.id === 'restoreAdminModal') closeRestoreModal();
            else if (activeModal.id === 'passwordResetModal') closePasswordResetModal();
            return;
        }

        if (event.key === 'Tab') {
            trapFocus(activeModal, event);
        }
    });

    ['designateAdminModal', 'restoreAdminModal', 'passwordResetModal'].forEach(function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    if (id === 'designateAdminModal') closeDesignateModal();
                    else if (id === 'restoreAdminModal') closeRestoreModal();
                    else if (id === 'passwordResetModal') closePasswordResetModal();
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
