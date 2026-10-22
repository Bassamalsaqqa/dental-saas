<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">User Profile</h1>
                <p class="text-gray-600 text-lg"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: esc($userProfile['email']) ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('controlplane/users') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Success</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p><?= session()->getFlashdata('success') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- User Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-8 text-white">
                    <div class="text-center">
                        <?php 
                            $initials = ui_initials($userProfile['first_name'] ?? '', $userProfile['last_name'] ?? '', 'U');
                        ?>
                        <div class="w-24 h-24 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm mx-auto mb-4 shadow-inner">
                            <span class="text-white text-4xl font-bold"><?= $initials ?></span>
                        </div>
                        <h2 class="text-2xl font-bold"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: 'No Name' ?></h2>
                        <p class="text-indigo-100 text-lg">ID: <?= esc($userProfile['id']) ?></p>
                        
                        <div class="mt-4">
                            <?php
                                $isActive = ($userProfile['active'] == 1);
                                $badgeClass = $isActive ? 'bg-green-400 text-green-900' : 'bg-rose-400 text-rose-900';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?= $badgeClass ?> shadow-sm">
                                <?= $isActive ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-blue-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500">Email Address</p>
                                <p class="text-sm font-semibold text-gray-900 truncate"><?= esc($userProfile['email']) ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar text-purple-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500">Member Since</p>
                                <p class="text-sm font-semibold text-gray-900"><?= date('M j, Y', $userProfile['created_on']) ?></p>
                            </div>
                        </div>

                        <?php if(!empty($userProfile['phone'])): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-emerald-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500">Phone</p>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($userProfile['phone']) ?></p>
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
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-hospital text-indigo-600 mr-2"></i>
                        Clinic Memberships
                    </h3>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                        <?= count($memberships) ?> Clinics
                    </span>
                </div>
                
                <div class="p-6">
                    <?php if (empty($memberships)): ?>
                        <div class="text-center py-6 text-gray-400">
                            <p>No clinic memberships found.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3">Clinic Name</th>
                                        <th class="px-4 py-3">Joined</th>
                                        <th class="px-4 py-3">Clinic Role</th>
                                        <th class="px-4 py-3"><?= esc(lang('UI.clinicAdminTitle')) ?></th>
                                        <th class="px-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($memberships as $membership): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 font-medium text-indigo-600">
                                                <a href="<?= base_url('controlplane/clinics/' . $membership['clinic_id']) ?>"><?= esc($membership['clinic_name']) ?></a>
                                            </td>
                                            <td class="px-4 py-3"><?= date('M j, Y', strtotime($membership['created_at'])) ?></td>
                                            <td class="px-4 py-3">
                                                <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/memberships/' . $membership['id'] . '/update-role') ?>" method="post" class="flex items-center gap-2">
                                                    <?= csrf_field() ?>
                                                    <select name="role_id" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                        <?php foreach (($rolesByClinic[(int)$membership['clinic_id']] ?? []) as $role): ?>
                                                            <option value="<?= (int) $role['id'] ?>" <?= ((int) $membership['role_id'] === (int) $role['id']) ? 'selected' : '' ?>>
                                                                <?= esc($role['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold hover:bg-indigo-200 transition-colors">
                                                        Update
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?php if (!empty($membership['is_designated_admin'])): ?>
                                                    <?php if (!empty($membership['admin_is_healthy'])): ?>
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                            <i class="fas fa-shield-alt me-1"></i><?= esc(lang('UI.primaryAdmin')) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <div class="space-y-1">
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                                <i class="fas fa-exclamation-triangle me-1"></i><?= esc($membership['admin_info']['state_label'] ?? 'Degraded') ?>
                                                            </span>
                                                            <?php if ((int)($userProfile['active'] ?? 0) === 1): ?>
                                                                <button type="button"
                                                                        class="block px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200 hover:bg-indigo-100 transition-colors"
                                                                        onclick="openRestoreModal(<?= (int)$membership['clinic_id'] ?>, '<?= esc($membership['clinic_name'], 'js') ?>', <?= (int)$userProfile['id'] ?>, <?= (int)($membership['admin_info']['admin_user']['id'] ?? $userProfile['id']) ?>, this)">
                                                                    <i class="fas fa-wrench me-1"></i><?= esc(lang('UI.restoreAuthority')) ?>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php elseif (!empty($membership['is_fallback_candidate'])): ?>
                                                    <div class="space-y-1">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                            <i class="fas fa-clock me-1"></i><?= esc(lang('UI.legacyAdminCandidate')) ?>
                                                        </span>
                                                        <?php if ((int)($userProfile['active'] ?? 0) === 1): ?>
                                                            <button type="button"
                                                                    class="block px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200 hover:bg-indigo-100 transition-colors"
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
                                                            <span class="text-xs text-gray-400 italic block"><?= esc(lang('UI.accountInactive')) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?php if ((int)($userProfile['active'] ?? 0) !== 1): ?>
                                                        <span class="text-xs text-gray-400 italic"><?= esc(lang('UI.accountInactive')) ?></span>
                                                    <?php else: ?>
                                                        <button type="button"
                                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200 hover:bg-indigo-100 transition-colors"
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
                                                    <button type="submit" class="px-3 py-2 rounded-lg bg-rose-100 text-rose-700 text-xs font-bold hover:bg-rose-200 transition-colors">
                                                        Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6 border-t border-gray-100 pt-6">
                        <h4 class="text-sm font-bold text-gray-700 mb-3">Add To Another Clinic</h4>
                        <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/memberships/add') ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Clinic</label>
                                <select id="addMembershipClinic" name="clinic_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white" required>
                                    <?php if (!empty($assignableClinics)): ?>
                                        <option value="">Select clinic</option>
                                        <?php foreach ($assignableClinics as $clinic): ?>
                                            <option value="<?= (int) $clinic['id'] ?>"><?= esc($clinic['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">No additional clinics available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Role</label>
                                <select id="addMembershipRole" name="role_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white" required disabled>
                                    <option value="">Select role</option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" <?= empty($assignableClinics) ? 'disabled' : '' ?>>
                                    Add Membership
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Global Roles -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-shield-alt text-purple-600 mr-2"></i>
                        Global Roles
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($globalRoles)): ?>
                        <p class="text-gray-400 text-sm">No global roles assigned.</p>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($globalRoles as $role): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200">
                                    <?= esc($role['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 rounded-xl shadow-lg border border-red-200 p-6">
                <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Danger Zone
                </h3>
                
                <div class="flex flex-wrap gap-4">
                    <!-- Toggle Status -->
                    <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/toggle-status') ?>" method="post">
                        <?= csrf_field() ?>
                        <?php if ($userProfile['active'] == 1): ?>
                            <button class="px-4 py-2 bg-yellow-500 text-white text-sm font-bold rounded-lg hover:bg-yellow-600 shadow-md transition-colors">
                                Deactivate User
                            </button>
                        <?php else: ?>
                            <button class="px-4 py-2 bg-green-500 text-white text-sm font-bold rounded-lg hover:bg-green-600 shadow-md transition-colors">
                                Activate User
                            </button>
                        <?php endif; ?>
                    </form>

                    <!-- Reset Password Trigger -->
                    <button type="button" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow-md transition-colors" onclick="openPasswordResetModal()">
                        Reset Password
                    </button>

                    <button type="button" disabled class="px-4 py-2 bg-slate-100 text-slate-500 text-sm rounded-lg cursor-not-allowed"><?= esc(lang('IonAuth.delete_disabled_label')) ?></button>
                    <p class="w-full text-sm text-slate-600" role="note"><?= esc(lang('IonAuth.delete_history_protected')) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordResetModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all scale-100">
        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-indigo-800">Reset User Password</h3>
            <button onclick="closePasswordResetModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">
                Enter a new password for <strong><?= esc($userProfile['email']) ?></strong>.
            </p>
            <form action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/reset-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">New Password</label>
                    <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required minlength="8" placeholder="Minimum 8 characters">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200" onclick="closePasswordResetModal()">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Designate Administrator Modal -->
<div id="designateAdminModal" role="dialog" aria-modal="true" aria-labelledby="designateModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm px-4" onclick="handleBackdropClick(event, 'designateAdminModal', closeDesignateModal)">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all scale-100" onclick="event.stopPropagation()">
        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                <i class="fas fa-user-shield text-indigo-600"></i>
                <span id="designateModalTitle"><?= esc(lang('UI.clinicAdminModalAssignTitle')) ?></span>
            </h3>
            <button type="button" onclick="closeDesignateModal()" class="text-gray-400 hover:text-gray-600" aria-label="<?= esc(lang('UI.cancel')) ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <!-- Explainer banner (Requirement 108) -->
            <div class="mb-4 rounded-xl bg-blue-50 border border-blue-200 p-3.5 text-xs text-blue-800 flex items-start gap-2.5">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                <span><?= esc(lang('UI.clinicAdminOneAdminExplainer')) ?></span>
            </div>

            <div class="mb-4 bg-gray-50 rounded-xl p-3 border border-gray-200 text-sm space-y-1">
                <div><span class="text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('UI.clinicLabel')) ?></span> <strong id="designateClinicName" class="text-gray-900"></strong></div>
                <div><span class="text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('UI.candidateLabel')) ?></span> <strong class="text-gray-900"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: esc($userProfile['email']) ?></strong> (<?= esc($userProfile['email']) ?>)</div>
            </div>

            <form id="designateAdminForm" action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/admin/designate') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="clinic_id" id="designateClinicId" value="">
                <input type="hidden" name="expected_admin_id" id="designateExpectedAdminId" value="">

                <!-- Replacement Section (shown only when clinic has another admin) -->
                <div id="replacementOptionsSection" class="mb-4 hidden">
                    <div class="mb-3 rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                        <span class="font-bold"><?= esc(lang('UI.currentAdministratorLabel')) ?></span> <span id="designateCurrentAdminInfo"></span>
                    </div>

                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2"><?= esc(lang('UI.clinicAdminPreviousActionLabel')) ?></label>
                    <div class="space-y-2 border border-gray-200 rounded-xl p-3 bg-gray-50 text-sm">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="previous_admin_action" value="demote_to_staff" checked class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-semibold text-gray-900"><?= esc(lang('UI.clinicAdminDemotePrevious')) ?></span>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="previous_admin_action" value="keep_role" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-semibold text-gray-900"><?= esc(lang('UI.clinicAdminKeepRolePrevious')) ?></span>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="previous_admin_action" value="deactivate_membership" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-semibold text-gray-900"><?= esc(lang('UI.clinicAdminDeactivateMembershipPrevious')) ?></span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1"><?= esc(lang('UI.clinicAdminReason')) ?></label>
                    <input type="text" name="reason" id="designateReasonInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="<?= esc(lang('UI.clinicAdminReasonPlaceholder')) ?>">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors" onclick="closeDesignateModal()"><?= esc(lang('UI.cancel')) ?></button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow">
                        <span id="designateSubmitLabel"><?= esc(lang('UI.clinicAdminConfirmAssign')) ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restore Administrator Modal -->
<div id="restoreAdminModal" role="dialog" aria-modal="true" aria-labelledby="restoreModalTitle" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm px-4" onclick="handleBackdropClick(event, 'restoreAdminModal', closeRestoreModal)">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all scale-100" onclick="event.stopPropagation()">
        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                <i class="fas fa-wrench text-indigo-600"></i>
                <span id="restoreModalTitle"><?= esc(lang('UI.clinicAdminModalRestoreTitle')) ?></span>
            </h3>
            <button type="button" onclick="closeRestoreModal()" class="text-gray-400 hover:text-gray-600" aria-label="<?= esc(lang('UI.cancel')) ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">
                <?= esc(lang('UI.clinicAdminRestoreHelp')) ?>
            </p>
            <div class="mb-4 bg-gray-50 rounded-xl p-3 border border-gray-200 text-sm space-y-1">
                <div><span class="text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('UI.clinicLabel')) ?></span> <strong id="restoreClinicName" class="text-gray-900"></strong></div>
                <div><span class="text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('UI.clinicAdminTitle')) ?>:</span> <strong class="text-gray-900"><?= esc(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: esc($userProfile['email']) ?></strong></div>
            </div>

            <form id="restoreAdminForm" action="<?= base_url('controlplane/users/' . $userProfile['id'] . '/admin/restore') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="clinic_id" id="restoreClinicId" value="">
                <input type="hidden" name="expected_designated_admin_id" id="restoreExpectedDesignatedId" value="">

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1"><?= esc(lang('UI.clinicAdminReason')) ?></label>
                    <input type="text" name="reason" id="restoreReasonInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="<?= esc(lang('UI.clinicAdminRestoreReasonPlaceholder')) ?>">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors" onclick="closeRestoreModal()"><?= esc(lang('UI.cancel')) ?></button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow">
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
