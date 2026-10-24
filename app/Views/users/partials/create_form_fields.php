<?php
$validation = $validation ?? session()->getFlashdata('validation');
$isRtl = !empty($is_rtl);
$fieldClass = 'v3-field-control';
$sectionClass = $sectionClass ?? 'v3-flow-section';
$sectionGapClass = $sectionGapClass ?? 'v3-flow-fields';
$iconSideClass = $isRtl ? 'left-0 pl-3' : 'right-0 pr-3';
$fieldError = static function (string $field) use ($validation): bool {
    return $validation && $validation->hasError($field);
};
$errorText = static function (string $field) use ($validation): string {
    return $validation ? (string) $validation->getError($field) : '';
};
?>

<div class="<?= esc($sectionGapClass) ?>">
    <div class="relative">

        <div class="<?= esc($sectionClass) ?>">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user text-sm"></i>
                </div>
                <h4 class="text-xl font-bold"><?= lang('UserRole.personalInformation') ?></h4>
            </div>

            <div class="">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="relative">
                        <label for="first_name" class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-user"></i>
                            <span><?= lang('UserRole.firstNameRequired') ?></span>
                        </label>
                        <div class="relative">
                            <input type="text" id="first_name" name="first_name"
                                   class="<?= esc($fieldClass) ?> <?= $fieldError('first_name') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                   value="<?= old('first_name') ?>" placeholder="<?= lang('UserRole.enterFirstName') ?>" required>
                            <div class="absolute inset-y-0 <?= esc($iconSideClass) ?> flex items-center pointer-events-none">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <?php if ($fieldError('first_name')): ?>
                            <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= esc($errorText('first_name')) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="relative">
                        <label for="last_name" class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-user"></i>
                            <span><?= lang('UserRole.lastNameRequired') ?></span>
                        </label>
                        <div class="relative">
                            <input type="text" id="last_name" name="last_name"
                                   class="<?= esc($fieldClass) ?> <?= $fieldError('last_name') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                   value="<?= old('last_name') ?>" placeholder="<?= lang('UserRole.enterLastName') ?>" required>
                            <div class="absolute inset-y-0 <?= esc($iconSideClass) ?> flex items-center pointer-events-none">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <?php if ($fieldError('last_name')): ?>
                            <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= esc($errorText('last_name')) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1">
                    <div class="relative">
                        <label for="email" class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-envelope"></i>
                            <span><?= lang('UserRole.emailRequired') ?></span>
                        </label>
                        <div class="relative">
                            <input type="email" id="email" name="email"
                                   data-user-email-check
                                   data-password-targets="#password,#password_confirm"
                                   data-existing-note-target="#users-email-exists-note"
                                   class="<?= esc($fieldClass) ?> <?= $fieldError('email') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                   value="<?= old('email') ?>" placeholder="<?= lang('UserRole.enterEmailAddress') ?>" required>
                            <div class="absolute inset-y-0 <?= esc($iconSideClass) ?> flex items-center pointer-events-none">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <?php if ($fieldError('email')): ?>
                            <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= esc($errorText('email')) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="relative">
                        <label for="phone" class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-phone text-emerald-600"></i>
                            <span><?= lang('UserRole.phone') ?></span>
                        </label>
                        <div class="relative">
                            <input type="tel" id="phone" name="phone"
                                   class="<?= esc($fieldClass) ?> focus:ring-emerald-500/20 focus:border-emerald-500 <?= $fieldError('phone') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                   value="<?= old('phone') ?>" placeholder="<?= lang('UserRole.enterPhoneNumber') ?>">
                            <div class="absolute inset-y-0 <?= esc($iconSideClass) ?> flex items-center pointer-events-none">
                                <i class="fas fa-phone"></i>
                            </div>
                        </div>
                        <?php if ($fieldError('phone')): ?>
                            <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= esc($errorText('phone')) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="relative">
                        <label for="hire_date" class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-calendar text-amber-600"></i>
                            <span><?= lang('UserRole.hireDateRequired') ?></span>
                        </label>
                        <div class="relative">
                            <input type="text" id="hire_date" name="hire_date" data-clinic-date="1"
                                   class="<?= esc($fieldClass) ?> focus:ring-amber-500/20 focus:border-amber-500 <?= $fieldError('hire_date') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                   value="<?= old('hire_date', date('Y-m-d')) ?>" required>
                            <div class="absolute inset-y-0 <?= esc($iconSideClass) ?> flex items-center pointer-events-none">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <?php if ($fieldError('hire_date')): ?>
                            <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= esc($errorText('hire_date')) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative">

        <div class="<?= esc($sectionClass) ?>">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-sm"></i>
                </div>
                <h4 class="text-xl font-bold"><?= lang('UserRole.accountSecurity') ?></h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="relative">
                    <label for="password" class="block text-sm font-bold mb-3 flex items-center gap-2">
                        <i class="fas fa-lock text-red-600"></i>
                        <span><?= lang('UserRole.passwordRequired') ?></span>
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               class="<?= esc($fieldClass) ?> focus:ring-red-500/20 focus:border-red-500 <?= $fieldError('password') ? 'border-red-500 ring-red-500/20' : '' ?>"
                               placeholder="<?= lang('UserRole.enterPassword') ?>" required>
                        <div class="absolute inset-y-0 <?= esc($iconSideClass) ?> flex items-center pointer-events-none">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <p id="users-email-exists-note" class="mt-2 text-xs text-amber-600 hidden flex items-center gap-2" role="status">
                        <i class="fas fa-info-circle"></i>
                        <span data-note-text></span>
                    </p>
                    <?php if ($fieldError('password')): ?>
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= esc($errorText('password')) ?></span>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="relative">
                    <label for="password_confirm" class="block text-sm font-bold mb-3 flex items-center gap-2">
                        <i class="fas fa-lock text-red-600"></i>
                        <span><?= lang('UserRole.confirmPasswordRequired') ?></span>
                    </label>
                    <div class="relative">
                        <input type="password" id="password_confirm" name="password_confirm"
                               class="<?= esc($fieldClass) ?> focus:ring-red-500/20 focus:border-red-500 <?= $fieldError('password_confirm') ? 'border-red-500 ring-red-500/20' : '' ?>"
                               placeholder="<?= lang('UserRole.confirmPassword') ?>" required>
                        <div class="absolute inset-y-0 <?= esc($iconSideClass) ?> flex items-center pointer-events-none">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <?php if ($fieldError('password_confirm')): ?>
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= esc($errorText('password_confirm')) ?></span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="relative">

        <div class="<?= esc($sectionClass) ?>">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-shield text-sm"></i>
                </div>
                <h4 class="text-xl font-bold"><?= lang('UserRole.roleAndPermissions') ?></h4>
            </div>

            <div class="">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="relative" data-select2-dropdown-parent>
                        <label for="role" class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-user-tag text-green-600"></i>
                            <span><?= lang('UserRole.roleRequired') ?></span>
                        </label>
                        <select id="role" name="role"
                                class="<?= esc($fieldClass) ?> focus:ring-green-500/20 focus:border-green-500 <?= $fieldError('role') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                data-searchable-select
                                data-search-url="<?= base_url('api/v1/search/roles') ?>"
                                data-placeholder="<?= lang('UserRole.searchRoles') ?>"
                                required>
                            <option value=""><?= lang('UserRole.selectRole') ?></option>
                            <?php foreach (($roles ?? []) as $key => $role): ?>
                                <?php
                                $roleValue = is_array($role) ? ($role['id'] ?? $key) : $key;
                                $roleLabel = is_array($role) ? ($role['name'] ?? $roleValue) : $role;
                                ?>
                                <option value="<?= esc($roleValue) ?>" <?= (old('role') == $roleValue) ? 'selected' : '' ?>>
                                    <?= esc($roleLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($fieldError('role')): ?>
                            <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= esc($errorText('role')) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="relative" data-select2-dropdown-parent>
                        <label for="department" class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-building text-green-600"></i>
                            <span><?= lang('UserRole.department') ?></span>
                        </label>
                        <select id="department" name="department"
                                class="<?= esc($fieldClass) ?> focus:ring-green-500/20 focus:border-green-500"
                                data-searchable-select
                                data-search-url="<?= base_url('api/v1/search/departments') ?>"
                                data-placeholder="<?= lang('UserRole.searchDepartments') ?>">
                            <option value=""><?= lang('UserRole.selectDepartment') ?></option>
                            <?php foreach (($departments ?? []) as $key => $department): ?>
                                <option value="<?= esc($key) ?>" <?= (old('department') == $key) ? 'selected' : '' ?>>
                                    <?= esc($department) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="relative" data-select2-dropdown-parent>
                    <label for="status" class="block text-sm font-bold mb-3 flex items-center gap-2">
                        <i class="fas fa-toggle-on text-green-600"></i>
                        <span><?= lang('UserRole.statusRequired') ?></span>
                    </label>
                    <select id="status" name="status"
                            class="<?= esc($fieldClass) ?> focus:ring-green-500/20 focus:border-green-500 <?= $fieldError('status') ? 'border-red-500 ring-red-500/20' : '' ?>"
                            data-force-select2
                            required>
                        <option value="active" <?= (old('status') == 'active') ? 'selected' : '' ?>><?= lang('UserRole.active') ?></option>
                        <option value="inactive" <?= (old('status') == 'inactive') ? 'selected' : '' ?>><?= lang('UserRole.inactive') ?></option>
                        <option value="suspended" <?= (old('status') == 'suspended') ? 'selected' : '' ?>><?= lang('UserRole.suspended') ?></option>
                    </select>
                    <?php if ($fieldError('status')): ?>
                        <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= esc($errorText('status')) ?></span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
