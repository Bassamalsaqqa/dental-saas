<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view min-h-screen">
    <div class="px-6 py-8">
        <!-- Enhanced Profile Header -->
        <div class="mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="relative group">

                        <div class="relative w-16 h-16 v3-radius flex items-center justify-center group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-user v3-text-on-primary text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="v3-title-scale font-black v3-text-ink mb-2"><?= lang('TenantMisc.profileManagement') ?></h1>
                        <p class="v3-text-muted font-medium"><?= lang('TenantMisc.profileManagementDesc') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Profile Information Card -->
            <div class="lg:col-span-2">
                <div class="v3-surface v3-radius border v3-border p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-black v3-text-ink flex items-center">
                            <i class="fas fa-user-edit w-6 h-6 mr-3 v3-accent-text"></i>
                            <?= lang('TenantMisc.personalInformation') ?>
                        </h2>
                    </div>


                    <!-- Profile Form -->
                    <form action="<?= base_url('profile/update') ?>" method="POST" class="space-y-6">
                        <?= csrf_field() ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="group">
                                <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center">
                                    <i class="fas fa-user w-4 h-4 mr-2 v3-accent-text"></i>
                                    <?= lang('TenantMisc.firstName') ?>
                                </label>
                                <input type="text" name="first_name" value="<?= old('first_name', $user->first_name ?? '') ?>"
                                       class="w-full px-4 py-4 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 v3-border-default v3-input"
                                       required>
                                <?php if (session('validation') && session('validation')->hasError('first_name')): ?>
                                    <p class="v3-text-danger text-sm mt-2"><?= session('validation')->getError('first_name') ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="group">
                                <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center">
                                    <i class="fas fa-user w-4 h-4 mr-2 v3-accent-text"></i>
                                    <?= lang('TenantMisc.lastName') ?>
                                </label>
                                <input type="text" name="last_name" value="<?= old('last_name', $user->last_name ?? '') ?>"
                                       class="w-full px-4 py-4 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 v3-border-default v3-input"
                                       required>
                                <?php if (session('validation') && session('validation')->hasError('last_name')): ?>
                                    <p class="v3-text-danger text-sm mt-2"><?= session('validation')->getError('last_name') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center">
                                <i class="fas fa-envelope w-4 h-4 mr-2 v3-text-primary"></i>
                                <?= lang('TenantMisc.emailAddress') ?>
                            </label>
                            <input type="email" name="email" value="<?= old('email', $user->email ?? '') ?>"
                                   class="w-full px-4 py-4 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 v3-border-default v3-input"
                                   required>
                            <?php if (session('validation') && session('validation')->hasError('email')): ?>
                                <p class="v3-text-danger text-sm mt-2"><?= session('validation')->getError('email') ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center">
                                <i class="fas fa-phone w-4 h-4 mr-2 v3-accent-text"></i>
                                <?= lang('TenantMisc.phoneNumber') ?>
                            </label>
                            <input type="tel" name="phone" value="<?= old('phone', $user->phone ?? '') ?>"
                                   class="w-full px-4 py-4 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 v3-border-default v3-input">
                            <?php if (session('validation') && session('validation')->hasError('phone')): ?>
                                <p class="v3-text-danger text-sm mt-2"><?= session('validation')->getError('phone') ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" class="group relative px-8 py-4 v3-text-on-primary font-bold v3-radius transition-all duration-300 v3-btn v3-btn--primary">

                                <span class="relative z-10 flex items-center">
                                    <i class="fas fa-save mr-2"></i>
                                    <?= lang('TenantMisc.updateProfile') ?>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- User Permissions Card -->
                <div class="v3-surface v3-radius border v3-border p-6">
                    <h3 class="text-xl font-black v3-text-ink mb-6 flex items-center">
                        <i class="fas fa-shield-alt w-5 h-5 mr-3 v3-text-primary"></i>
                        <?= lang('TenantMisc.userPermissions') ?>
                    </h3>

                    <div class="space-y-4">
                        <?php if (!empty($user_groups)): ?>
                            <?php foreach ($user_groups as $group): ?>
                                <div class="flex items-center justify-between p-3 v3-radius border v3-border-default">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-tag v3-text-primary mr-3"></i>
                                        <span class="font-semibold v3-text-ink"><?= esc(ucfirst($group->name)) ?></span>
                                    </div>
                                    <div class="w-2 h-2 v3-bg-primary rounded-full"></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle v3-text-muted text-2xl mb-2"></i>
                                <p class="v3-text-muted"><?= lang('TenantMisc.noSpecificPermissions') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="v3-surface v3-radius border v3-border p-6">
                    <h3 class="text-xl font-black v3-text-ink mb-6 flex items-center">
                        <i class="fas fa-key w-5 h-5 mr-3 v3-text-warning"></i>
                        <?= lang('TenantMisc.changePassword') ?>
                    </h3>

                    <form action="<?= base_url('profile/change-password') ?>" method="POST" class="space-y-4">
                        <?= csrf_field() ?>

                        <div class="group">
                            <label class="block text-sm font-bold v3-text-ink mb-2"><?= lang('TenantMisc.currentPassword') ?></label>
                            <input type="password" name="current_password"
                                   class="w-full px-3 py-3 v3-surface border-2 v3-border rounded-lg focus:outline-none focus:ring-2 v3-focus-control v3-focus-control transition-all duration-300 v3-input"
                                   required>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold v3-text-ink mb-2"><?= lang('TenantMisc.newPassword') ?></label>
                            <input type="password" name="new_password"
                                   class="w-full px-3 py-3 v3-surface border-2 v3-border rounded-lg focus:outline-none focus:ring-2 v3-focus-control v3-focus-control transition-all duration-300 v3-input"
                                   required>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold v3-text-ink mb-2"><?= lang('TenantMisc.confirmPassword') ?></label>
                            <input type="password" name="confirm_password"
                                   class="w-full px-3 py-3 v3-surface border-2 v3-border rounded-lg focus:outline-none focus:ring-2 v3-focus-control v3-focus-control transition-all duration-300 v3-input"
                                   required>
                        </div>

                        <button type="submit" class="w-full group relative px-4 py-3 v3-text-on-primary font-bold rounded-lg transition-all duration-300 v3-btn v3-btn--primary">

                            <span class="relative z-10 flex items-center justify-center">
                                <i class="fas fa-key mr-2"></i>
                                <?= lang('TenantMisc.changePassword') ?>
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Account Info Card -->
                <div class="v3-surface v3-radius border v3-border p-6">
                    <h3 class="text-xl font-black v3-text-ink mb-6 flex items-center">
                        <i class="fas fa-info-circle w-5 h-5 mr-3 v3-accent-text"></i>
                        <?= lang('TenantMisc.accountInformation') ?>
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2">
                            <span class="v3-text-muted font-medium"><?= lang('TenantMisc.userIdLabel') ?></span>
                            <span class="v3-text-ink font-semibold">#<?= $user->id ?></span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="v3-text-muted font-medium"><?= lang('TenantMisc.statusLabel') ?></span>
                            <span class="px-2 py-1 v3-bg-paid v3-text-primary text-xs font-semibold rounded-full">
                                <?= $user->active ? lang('TenantMisc.statusActive') : lang('TenantMisc.statusInactive') ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="v3-text-muted font-medium"><?= lang('TenantMisc.lastLoginLabel') ?></span>
                            <span class="v3-text-ink font-semibold">
                                <?= $user->last_login ? date('M j, Y g:i A', $user->last_login) : lang('TenantMisc.never') ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="v3-text-muted font-medium"><?= lang('TenantMisc.createdLabel') ?></span>
                            <span class="v3-text-ink font-semibold">
                                <?= formatDate($user->created_on) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
