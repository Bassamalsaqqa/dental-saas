<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced User Edit with Advanced Design -->
<div class="min-h-screen bg-slate-50">
    <!-- Animated Background Elements -->
    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced User Form with Glassmorphism -->
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-user-edit text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300"><?= lang('UserRole.editUser') ?></h3>
                                <p class="text-gray-600 font-medium"><?= lang('UserRole.updateUserInformationDescription') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right ml-2' : 'fa-arrow-left mr-2' ?> relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.backToUsers') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">

                    <!-- Flash messages -->
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                <span><?= session()->getFlashdata('error') ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <span><?= session()->getFlashdata('success') ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Identity Ownership Callout -->
                    <div class="mb-8 rounded-2xl border border-blue-200 bg-blue-50/70 p-5 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md">
                                <i class="fas fa-shield-alt text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-base font-bold text-blue-950"><?= lang('UserRole.accountInformation') ?></h4>
                                <p class="mt-1 text-sm font-semibold text-blue-800"><?= esc(lang('UserRole.identityManagedByOwner')) ?></p>
                                <?php if (!empty($is_self)): ?>
                                    <div class="mt-3">
                                        <a href="<?= base_url('profile') ?>" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-blue-700">
                                            <i class="fas fa-user-circle"></i>
                                            <span><?= esc(lang('UserRole.profileRedirectNotice')) ?></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form action="<?= base_url('users/' . (int) $user->id . '/update') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <!-- Readonly Personal Information (Display only, no input names to prevent identity tampering) -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?> mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800"><?= lang('UserRole.personalInformation') ?></h4>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200"><?= lang('UserRole.readOnly') ?></span>
                                </div>

                                <div class="space-y-8">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user text-blue-600"></i>
                                                <span><?= lang('UserRole.firstName') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_first_name"
                                                       class="w-full px-4 py-3 bg-slate-50 border-2 border-gray-200 rounded-xl shadow-sm text-slate-700 font-semibold cursor-not-allowed"
                                                       value="<?= esc($user->first_name ?? '') ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user text-blue-600"></i>
                                                <span><?= lang('UserRole.lastName') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_last_name"
                                                       class="w-full px-4 py-3 bg-slate-50 border-2 border-gray-200 rounded-xl shadow-sm text-slate-700 font-semibold cursor-not-allowed"
                                                       value="<?= esc($user->last_name ?? '') ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-envelope text-purple-600"></i>
                                                <span><?= lang('UserRole.email') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="email" id="display_email"
                                                       class="w-full px-4 py-3 bg-slate-50 border-2 border-gray-200 rounded-xl shadow-sm text-slate-700 font-semibold cursor-not-allowed"
                                                       value="<?= esc($user->email ?? '') ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-phone text-emerald-600"></i>
                                                <span><?= lang('UserRole.phone') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="tel" id="display_phone"
                                                       class="w-full px-4 py-3 bg-slate-50 border-2 border-gray-200 rounded-xl shadow-sm text-slate-700 font-semibold cursor-not-allowed"
                                                       value="<?= esc($user->phone ?? lang('UserRole.notProvided')) ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-calendar text-amber-600"></i>
                                                <span><?= lang('UserRole.hireDateRequired') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_hire_date"
                                                       class="w-full px-4 py-3 bg-slate-50 border-2 border-gray-200 rounded-xl shadow-sm text-slate-700 font-semibold cursor-not-allowed"
                                                       value="<?= esc(!empty($user->hire_date) ? formatDate((string) $user->hire_date) : lang('UserRole.na')) ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Security (Direct to Profile / Recovery; no tenant password reset fields) -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-pink-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?> mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800"><?= lang('UserRole.accountSecurity') ?></h4>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-5">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-bold text-slate-800"><?= lang('UserRole.password') ?></p>
                                            <p class="mt-1 text-xs text-slate-600">
                                                <?= !empty($is_self) ? esc(lang('UserRole.profileRedirectNotice')) : esc(lang('UserRole.cannotResetOtherUserPassword')) ?>
                                            </p>
                                        </div>
                                        <?php if (!empty($is_self)): ?>
                                            <a href="<?= base_url('users/' . (int) $user->id . '/change-password') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-900 transition">
                                                <i class="fas fa-key"></i>
                                                <span><?= lang('UserRole.changePassword') ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clinic Membership & Role Assignment (Authorized Tenant Plane Mutation) -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-teal-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?> mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user-shield text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800"><?= lang('UserRole.roleAndPermissions') ?></h4>
                                </div>

                                <div class="space-y-8">
                                    <?php
                                    $currentRoleId = '';
                                    if (!empty($user->roles) && is_array($user->roles)) {
                                        $currentRoleId = $user->roles[0]['role_id'] ?? '';
                                    } elseif (!empty($user->current_role_id)) {
                                        $currentRoleId = $user->current_role_id;
                                    }
                                    $selectedRole = old('role', $currentRoleId);
                                    ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user-tag text-green-600"></i>
                                                <span><?= lang('UserRole.roleRequired') ?></span>
                                            </label>
                                            <div class="relative">
                                                <select id="role" name="role"
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('role')) ? 'border-red-500 ring-red-500/20' : '' ?>"
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
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('role')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('role') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-building text-green-600"></i>
                                                <span><?= lang('UserRole.department') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="display_department"
                                                       class="w-full px-4 py-3 bg-slate-50 border-2 border-gray-200 rounded-xl shadow-sm text-slate-700 font-semibold cursor-not-allowed"
                                                       value="<?= esc(!empty($user->department) && isset($departments[$user->department]) ? $departments[$user->department] : ($user->department ?? lang('UserRole.na'))) ?>" readonly disabled>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Membership Status Display (Separate action; no status input on role edit submit) -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-toggle-on text-green-600"></i>
                                                <span><?= lang('UserRole.membershipStatus') ?></span>
                                            </label>
                                            <div class="flex items-center justify-between p-3.5 bg-slate-50 border-2 border-gray-200 rounded-xl">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black <?= ($membership_status ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' ?>">
                                                        <span class="h-1.5 w-1.5 rounded-full <?= ($membership_status ?? 'active') === 'active' ? 'bg-emerald-500' : 'bg-slate-500' ?>"></span>
                                                        <?= esc(($membership_status ?? 'active') === 'active' ? lang('UserRole.active') : lang('UserRole.inactive')) ?>
                                                    </span>
                                                    <span class="text-xs font-semibold text-slate-500"><?= lang('UserRole.clinicAccess') ?></span>
                                                </div>
                                                <span class="text-xs text-slate-400 font-medium"><?= lang('UserRole.readOnly') ?></span>
                                            </div>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2 <?= !empty($is_rtl) ? 'space-x-reverse' : '' ?>">
                                                <i class="fas fa-user-circle text-blue-600"></i>
                                                <span><?= lang('UserRole.globalAccountStatus') ?></span>
                                            </label>
                                            <div class="flex items-center justify-between p-3.5 bg-slate-50 border-2 border-gray-200 rounded-xl">
                                                <div class="flex items-center gap-2">
                                                    <?php if (!empty($is_globally_disabled)): ?>
                                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-xs font-black text-rose-800 ring-1 ring-rose-200">
                                                            <i class="fas fa-ban text-xs"></i>
                                                            <?= esc(lang('UserRole.globallyDisabled')) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800 ring-1 ring-blue-200">
                                                            <i class="fas fa-check text-xs"></i>
                                                            <?= esc(lang('UserRole.globalAccountActive')) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-xs text-slate-400 font-medium"><?= lang('UserRole.readOnly') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?> relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.cancel') ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
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
<?= $this->endSection() ?>
