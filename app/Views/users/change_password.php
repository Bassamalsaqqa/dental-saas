<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<div class="min-h-screen relative overflow-hidden">


    <div class="v3-operational-body">

                <div class="relative">

            <div class="v3-flow-section">
                <!-- Form Header -->
                <div class="v3-flow-command">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">

                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-key text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="v3-section-title transition-colors"><?= lang('UserRole.changePassword') ?></h3>
                                <p class="font-medium"><?= lang('UserRole.updatePasswordFor') ?> <?= ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold">

                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.backToUsers') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="">

                    <form action="<?= base_url('users/' . $user->id . '/update-password') ?>" method="POST" class="v3-flow-form ">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <!-- User Information -->
                        <div class="relative">

                            <div class="relative border">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= lang('UserRole.userInformation') ?></h4>
                                </div>

                                <div class="flex items-center space-x-6">
                                    <div class="w-16 h-16 flex items-center justify-center font-bold text-xl">
                                        <?= esc(ui_initials($user->first_name ?? '', $user->last_name ?? '', 'U')) ?>
                                    </div>
                                    <div>
                                        <h5 class="text-lg font-bold">
                                            <?= ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') ?>
                                        </h5>
                                        <p class=""><?= $user->email ?></p>
                                        <p class="text-sm">@<?= $user->username ?? $user->email ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Change -->
                        <div class="relative">

                            <div class="relative border">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= lang('UserRole.passwordChange') ?></h4>
                                </div>

                                <div class="">
                                    <div class="relative">
                                        <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                            <i class="fas fa-lock text-red-600"></i>
                                            <span><?= lang('UserRole.currentPasswordRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="current_password" name="current_password"
                                                   class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('current_password')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                   placeholder="<?= lang('UserRole.enterCurrentPassword') ?>" required>
                                            <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                <i class="fas fa-lock"></i>
                                            </div>
                                        </div>
                                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('current_password')): ?>
                                            <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span><?= session()->getFlashdata('validation')->getError('current_password') ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2">
                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                <i class="fas fa-lock text-red-600"></i>
                                                <span><?= lang('UserRole.newPasswordRequired') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="password" id="new_password" name="new_password"
                                                       class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('new_password')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       placeholder="<?= lang('UserRole.enterNewPassword') ?>" required>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('new_password')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('new_password') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="relative">
                                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                <i class="fas fa-lock text-red-600"></i>
                                                <span><?= lang('UserRole.confirmNewPasswordRequired') ?></span>
                                            </label>
                                            <div class="relative">
                                                <input type="password" id="confirm_password" name="confirm_password"
                                                       class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('confirm_password')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       placeholder="<?= lang('UserRole.confirmNewPassword') ?>" required>
                                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                    <i class="fas fa-lock"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('confirm_password')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('confirm_password') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="relative">

                            <div class="relative border">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-info-circle text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= lang('UserRole.passwordRequirements') ?></h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2">
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm"><?= lang('UserRole.passwordRuleMinLength') ?></span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm"><?= lang('UserRole.passwordRuleUppercase') ?></span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm"><?= lang('UserRole.passwordRuleLowercase') ?></span>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm"><?= lang('UserRole.passwordRuleNumber') ?></span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm"><?= lang('UserRole.passwordRuleSpecialChar') ?></span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm"><?= lang('UserRole.passwordRuleDifferent') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t">
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="relative inline-flex items-center justify-center px-8 py-3 border-2 text-sm font-bold">

                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.cancel') ?></span>
                            </a>
                            <button type="submit" class="relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold">

                                <i class="fas fa-key mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('UserRole.changePassword') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');

    // Password confirmation validation
    function validatePassword() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity(<?= json_encode(lang('UserRole.passwordsDoNotMatch')) ?>);
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }

    newPassword.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);

    // Form submission
    form.addEventListener('submit', function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            alert(<?= json_encode(lang('UserRole.newPasswordsDoNotMatch')) ?>);
            confirmPassword.focus();
            return false;
        }

        if (newPassword.value.length < 8) {
            e.preventDefault();
            alert(<?= json_encode(lang('UserRole.newPasswordMinLengthAlert')) ?>);
            newPassword.focus();
            return false;
        }
    });
});
</script>
</div>
<?= $this->endSection() ?>
