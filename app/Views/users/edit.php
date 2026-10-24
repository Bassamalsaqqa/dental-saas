<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<div class="v3-operational-view">
        <div class="v3-operational-body">

                <div class="relative">
            <div class="relative overflow-hidden">
                <!-- Form Header -->
                <div class="v3-flow-command">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                            <div class="relative">

                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-user-edit text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="v3-section-title transition-colors"><?= lang('UserRole.editUser') ?></h3>
                                <p class="font-medium"><?= lang('UserRole.updateUserInformationDescription') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right ml-2' : 'fa-arrow-left mr-2' ?> relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.backToUsers') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="">

                    <!-- Flash messages -->
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="mb-6 border v3-border-default v3-bg-alert p-4 text-sm font-semibold v3-text-danger">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-circle v3-text-danger"></i>
                                <span><?= esc(session()->getFlashdata('error')) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="mb-6 border v3-border-default v3-bg-paid p-4 text-sm font-semibold v3-text-primary">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle v3-text-primary"></i>
                                <span><?= esc(session()->getFlashdata('success')) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Identity Ownership Callout -->
                    <div class="mb-8 border">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                                <i class="fas fa-shield-alt text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-base font-bold"><?= lang('UserRole.accountInformation') ?></h4>
                                <p class="mt-1 text-sm font-semibold"><?= esc(lang('UserRole.identityManagedByOwner')) ?></p>
                                <?php if (!empty($is_self)): ?>
                                    <div class="mt-3">
                                        <a href="<?= base_url('profile') ?>" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition v3-btn v3-btn--secondary">
                                            <i class="fas fa-user-circle"></i>
                                            <span><?= esc(lang('UserRole.profileRedirectNotice')) ?></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form action="<?= base_url('users/' . (int) $user->id . '/update') ?>" method="POST" class="v3-flow-form ">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <!-- Readonly Personal Information (Display only, no input names to prevent identity tampering) -->
                        <div class="relative">

                            <div class="relative border">
                                <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?> mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= lang('UserRole.personalInformation') ?></h4>
                                    <span class="px-3 py-1 text-xs font-bold border"><?= lang('UserRole.readOnly') ?></span>
                                </div>

                                <div class="">
                                    <div class="grid grid-cols-1 md:grid-cols-2">
                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user"></i>
                                                <span><?= lang('UserRole.firstName') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_first_name"
                                                       class="w-full px-4 py-3 border-2 font-semibold cursor-not-allowed v3-input"
                                                       value="<?= esc($user->first_name ?? '') ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user"></i>
                                                <span><?= lang('UserRole.lastName') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_last_name"
                                                       class="w-full px-4 py-3 border-2 font-semibold cursor-not-allowed v3-input"
                                                       value="<?= esc($user->last_name ?? '') ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1">
                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-envelope"></i>
                                                <span><?= lang('UserRole.email') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="email" id="display_email"
                                                       class="w-full px-4 py-3 border-2 font-semibold cursor-not-allowed v3-input"
                                                       value="<?= esc($user->email ?? '') ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2">
                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-phone v3-text-primary"></i>
                                                <span><?= lang('UserRole.phone') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="tel" id="display_phone"
                                                       class="w-full px-4 py-3 border-2 font-semibold cursor-not-allowed v3-input"
                                                       value="<?= esc($user->phone ?? lang('UserRole.notProvided')) ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-calendar v3-text-warning"></i>
                                                <span><?= lang('UserRole.hireDateRequired') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_hire_date"
                                                       class="w-full px-4 py-3 border-2 font-semibold cursor-not-allowed v3-input"
                                                       value="<?= esc(!empty($user->hire_date) ? formatDate((string) $user->hire_date) : lang('UserRole.na')) ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Security (Direct to Profile / Recovery; no tenant password reset fields) -->
                        <div class="relative">

                            <div class="relative border">
                                <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?> mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= lang('UserRole.accountSecurity') ?></h4>
                                </div>

                                <div class="border">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-bold"><?= lang('UserRole.password') ?></p>
                                            <p class="mt-1 text-xs">
                                                <?= !empty($is_self) ? esc(lang('UserRole.profileRedirectNotice')) : esc(lang('UserRole.cannotResetOtherUserPassword')) ?>
                                            </p>
                                        </div>
                                        <?php if (!empty($is_self)): ?>
                                            <a href="<?= base_url('users/' . (int) $user->id . '/change-password') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold transition v3-btn v3-btn--secondary">
                                                <i class="fas fa-key"></i>
                                                <span><?= lang('UserRole.changePassword') ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clinic Membership & Role Assignment (Authorized Tenant Plane Mutation) -->
                        <div class="relative">

                            <div class="relative border">
                                <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?> mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user-shield text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= lang('UserRole.roleAndPermissions') ?></h4>
                                </div>

                                <div class="">
                                    <?php
                                    $currentRoleId = '';
                                    if (!empty($user->roles) && is_array($user->roles)) {
                                        $currentRoleId = $user->roles[0]['role_id'] ?? '';
                                    } elseif (!empty($user->current_role_id)) {
                                        $currentRoleId = $user->current_role_id;
                                    }
                                    $selectedRole = old('role', $currentRoleId);
                                    ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2">
                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user-tag v3-text-primary"></i>
                                                <span><?= lang('UserRole.roleRequired') ?></span>
                                            </label>
                                            <div class="relative">
                                                <select id="role" name="role"
                                                        class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 v3-focus-control v3-focus-control <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('role')) ? 'v3-border-default v3-border-default' : '' ?> v3-select"
                                                        data-searchable-select
                                                        data-search-url="<?= base_url('api/v1/search/roles') ?>"
                                                        data-placeholder="<?= lang('UserRole.searchRoles') ?>"
                                                        required>
                                                    <option value=""><?= lang('UserRole.selectRole') ?></option>
                                                    <?php foreach ($roles as $key => $role): ?>
                                                        <option value="<?= (int) $key ?>" <?= ((string) $selectedRole === (string) $key) ? 'selected' : '' ?>>
                                                            <?= esc($role) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-chevron-down"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('role')): ?>
                                                <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('role') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-building v3-text-primary"></i>
                                                <span><?= lang('UserRole.department') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_department"
                                                       class="w-full px-4 py-3 border-2 font-semibold cursor-not-allowed v3-input"
                                                       value="<?= esc(!empty($user->department) && isset($departments[$user->department]) ? $departments[$user->department] : ($user->department ?? lang('UserRole.na'))) ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Membership Status Display (Separate action; no status input on role edit submit) -->
                                    <div class="grid grid-cols-1 md:grid-cols-2">
                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-toggle-on v3-text-primary"></i>
                                                <span><?= lang('UserRole.membershipStatus') ?></span>
                                            </label>
                                            <div class="flex items-center justify-between p-3.5 border-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs <?= ($membership_status ?? 'active') === 'active' ? 'v3-bg-paid v3-text-primary' : 'v3-bg-sand v3-text-ink' ?>">
                                                        <span class="h-1.5 w-1.5 <?= ($membership_status ?? 'active') === 'active' ? 'v3-bg-primary' : 'v3-bg-canvas' ?>"></span>
                                                        <?= esc(($membership_status ?? 'active') === 'active' ? lang('UserRole.active') : lang('UserRole.inactive')) ?>
                                                    </span>
                                                    <span class="text-xs font-semibold"><?= lang('UserRole.clinicAccess') ?></span>
                                                </div>
                                                <span class="text-xs font-medium"><?= lang('UserRole.readOnly') ?></span>
                                            </div>
                                        </div>

                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user-circle"></i>
                                                <span><?= lang('UserRole.globalAccountStatus') ?></span>
                                            </label>
                                            <div class="flex items-center justify-between p-3.5 border-2">
                                                <div class="flex items-center gap-2">
                                                    <?php if (!empty($is_globally_disabled)): ?>
                                                        <span class="inline-flex items-center gap-1.5 v3-bg-alert px-3 py-1 text-xs v3-text-danger border v3-border-default">
                                                            <i class="fas fa-ban text-xs"></i>
                                                            <?= esc(lang('UserRole.globallyDisabled')) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs border">
                                                            <i class="fas fa-check text-xs"></i>
                                                            <?= esc(lang('UserRole.globalAccountActive')) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-xs font-medium"><?= lang('UserRole.readOnly') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t">
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="relative inline-flex items-center justify-center px-8 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-times <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?> relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.cancel') ?></span>
                            </a>
                            <button type="submit" class="relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold v3-btn v3-btn--primary">

                                <i class="fas fa-save <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?> relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.updateUser') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Null-safe initialization for form controls
    const roleSelect = document.getElementById('role');
    if (roleSelect && window.jQuery && typeof jQuery.fn.select2 !== 'undefined') {
        jQuery(roleSelect).select2();
    }
});
</script>
</div>
<?= $this->endSection() ?>
