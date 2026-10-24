<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<p class="px-4 py-2 text-sm text-amber-800"><?= esc(lang('Finance.providerCurrencyWarning')) ?></p>
<?php
    $isRtl = !empty($is_rtl);
    $viewedUser = $viewed_user ?? null;
    $viewedUserRoles = $viewed_user_roles ?? [];
    $formatAccountDateTime = static function ($value, string $fallback) {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return $fallback;
        }

        $formatted = formatDateTime($value);
        return $formatted !== '' ? $formatted : $fallback;
    };
    $userFullName = trim((string) (($viewedUser->first_name ?? '') . ' ' . ($viewedUser->last_name ?? '')));
    $userFullName = $userFullName !== '' ? $userFullName : lang('UserRole.user');
    $createdAtText = $formatAccountDateTime($viewedUser->created_on ?? ($viewedUser->created_at ?? null), lang('UserRole.na'));
    $lastLoginText = $formatAccountDateTime($viewedUser->last_login ?? null, lang('UserRole.never'));
    $lastIpText = trim((string) ($viewedUser->ip_address ?? ''));
    $lastIpText = $lastIpText !== '' ? $lastIpText : lang('UserRole.na');
    $membershipActive = ($membership_status ?? ($viewedUser->membership_status ?? 'active')) === 'active';
    $membershipStatusText = $membershipActive ? lang('UserRole.active') : lang('UserRole.inactive');
    $isGloballyDisabled = (int) ($viewedUser->active ?? 0) !== 1;
    $statusText = $membershipStatusText;
    $statusClass = $membershipActive
        ? 'patient-hero-status patient-hero-status-active'
        : 'patient-hero-status patient-hero-status-inactive';
    $contentStatusClass = $membershipActive
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
        : 'bg-slate-100 text-slate-700 ring-slate-200';
    $isSelf = !empty($is_self) || ((int) ($viewedUser->id ?? 0) === (int) ($current_user->id ?? 0));
    $providerSummary = $provider_finance_summary ?? [
        'payout_count' => 0,
        'gross_amount_total' => 0,
        'provider_share_total' => 0,
        'provider_paid_total' => 0,
        'provider_balance_total' => 0,
    ];
    $providerRows = $provider_payout_rows ?? [];
    $providerPaymentRows = $provider_payment_rows ?? [];
    $recentProviderPaymentRows = array_slice($providerPaymentRows, 0, 4);
    $assignableRoles = $assignable_roles ?? [];
    $departments = $departments ?? [];
    $currentRoleId = (int) ($current_role_id ?? 0);
    $canEditUsers = has_permission('users', 'edit');
    $canDeleteUsers = has_permission('users', 'delete');
    $canViewFinance = has_permission('finance', 'view');
    $canEditFinance = has_permission('finance', 'edit');
    $departmentKey = (string) ($viewedUser->department ?? '');
    $departmentText = $departmentKey !== ''
        ? (string) ($departments[$departmentKey] ?? $departmentKey)
        : lang('UserRole.na');
    $hireDateText = !empty($viewedUser->hire_date)
        ? formatDate((string) $viewedUser->hire_date)
        : lang('UserRole.na');
    $userGender = strtolower((string) ($viewedUser->gender ?? ''));
    $userHeroThemeClass = 'patient-hero-card-neutral';
    $userAvatarToneClass = 'patient-avatar-badge-neutral';
    $userAvatarIconClass = 'fas fa-user';

    if ($userGender === 'female') {
        $userHeroThemeClass = 'patient-hero-card-female';
        $userAvatarToneClass = 'patient-avatar-badge-female';
        $userAvatarIconClass = 'fas fa-user-nurse';
    } elseif ($userGender === 'male') {
        $userHeroThemeClass = 'patient-hero-card-male';
        $userAvatarToneClass = 'patient-avatar-badge-male';
        $userAvatarIconClass = 'fas fa-user-doctor';
    }

    $userRoleSummary = !empty($viewedUserRoles)
        ? implode(', ', array_map(static function ($role) {
            return (string) (is_array($role) ? ($role['name'] ?? '') : ($role->name ?? ''));
        }, $viewedUserRoles))
        : lang('UserRole.noRolesAssigned');

    $heroInfoCapsules = [
        ['label' => lang('UserRole.userIdColon'), 'display_label' => '', 'value' => '#' . (string) ($viewedUser->id ?? ''), 'icon' => 'fas fa-id-card', 'class' => 'patient-hero-chip-ltr'],
        ['label' => lang('UserRole.phone'), 'display_label' => '', 'value' => (string) ($viewedUser->phone ?? lang('UserRole.notProvided')), 'icon' => 'fas fa-phone', 'class' => 'patient-hero-chip-ltr'],
        ['label' => lang('UserRole.email'), 'display_label' => '', 'value' => (string) ($viewedUser->email ?? lang('UserRole.na')), 'icon' => 'fas fa-envelope', 'class' => 'patient-hero-chip-ltr'],
        ['label' => lang('UserRole.statusAndGroups'), 'display_label' => '', 'value' => $statusText, 'icon' => 'fas fa-toggle-on'],
        ['label' => lang('UserRole.createdColon'), 'display_label' => '', 'value' => $createdAtText, 'icon' => 'fas fa-calendar-plus'],
        ['label' => lang('UserRole.lastLoginColon'), 'display_label' => '', 'value' => $lastLoginText, 'icon' => 'fas fa-right-to-bracket'],
        ['label' => lang('UserRole.lastIpColon'), 'display_label' => '', 'value' => $lastIpText, 'icon' => 'fas fa-network-wired', 'class' => 'patient-hero-chip-ltr'],
        ['label' => lang('UserRole.statusAndGroups'), 'display_label' => '', 'value' => $userRoleSummary, 'icon' => 'fas fa-user-shield'],
    ];

    $patientAvatarFor = static function (?string $gender): array {
        $normalized = strtolower((string) ($gender ?? 'other'));
        return match ($normalized) {
            'male' => ['icon' => 'fas fa-person', 'color' => 'text-blue-700'],
            'female' => ['icon' => 'fas fa-person-dress', 'color' => 'text-rose-700'],
            default => ['icon' => 'fas fa-user', 'color' => 'text-slate-600'],
        };
    };

    $transactionMetaFor = static function (?string $type): array {
        $normalized = strtolower((string) ($type ?? ''));
        return match ($normalized) {
            'payment' => ['icon' => 'fas fa-money-check-dollar', 'color' => 'text-emerald-700'],
            'invoice' => ['icon' => 'fas fa-file-invoice-dollar', 'color' => 'text-blue-700'],
            'refund' => ['icon' => 'fas fa-rotate-left', 'color' => 'text-rose-700'],
            'adjustment' => ['icon' => 'fas fa-sliders', 'color' => 'text-amber-700'],
            'expense' => ['icon' => 'fas fa-receipt', 'color' => 'text-orange-700'],
            default => ['icon' => 'fas fa-tag', 'color' => 'text-slate-600'],
        };
    };
?>



<div class="user-page-shell min-h-screen" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="">
            <section class="v3-record-context <?= esc($userHeroThemeClass) ?> overflow-hidden border">

                <div class="v3-record-context-body">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em]">
                            <span><?= esc(lang('UserRole.userDetails')) ?></span>
                            <span class="h-1 w-1"></span>
                            <span><?= esc(lang('UserRole.createdColon')) ?> <?= esc($createdAtText) ?></span>
                        </div>
                        <div class="v3-record-identity">
                            <div class="v3-context-avatar <?= esc($userAvatarToneClass) ?>">
                                <i class="<?= esc($userAvatarIconClass) ?>"></i>
                            </div>
                            <div class="space-y-4 min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="truncate v3-title font-extrabold tracking-tight v3-title"><?= esc($userFullName) ?></h1>
                                    <span class="inline-flex items-center border px-3 py-1 text-xs font-semibold <?= esc($statusClass) ?>">
                                        <?= esc($statusText) ?>
                                    </span>
                                </div>
                                <div class="v3-context-facts">
                                    <?php foreach ($heroInfoCapsules as $capsule): ?>
                                        <?php
                                            $capsuleClass = trim('patient-hero-chip ' . ($capsule['class'] ?? ''));
                                            $capsuleValue = (string) ($capsule['value'] ?? lang('UserRole.notProvided'));
                                            $capsuleLabel = (string) ($capsule['label'] ?? '');
                                            $capsuleDisplayLabel = array_key_exists('display_label', $capsule) ? (string) $capsule['display_label'] : $capsuleLabel;
                                            $capsuleIcon = (string) ($capsule['icon'] ?? 'fas fa-circle');
                                        ?>
                                        <div class="<?= esc($capsuleClass) ?>" title="<?= esc($capsuleLabel . ': ' . $capsuleValue) ?>">
                                            <span class="patient-hero-chip-icon"><i class="<?= esc($capsuleIcon) ?>"></i></span>
                                            <span class="patient-hero-chip-text">
                                                <?php if ($capsuleDisplayLabel !== ''): ?><span class="patient-hero-chip-label"><?= esc($capsuleDisplayLabel) ?></span><?php endif; ?>
                                                <span class="patient-hero-chip-value"><?= esc($capsuleValue) ?></span>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="v3-record-context-actions">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <?php if ($canEditUsers): ?>
                                <a href="<?= workflow_context_url('users/' . (int) $viewedUser->id . '/edit') ?>" class="v3-context-action patient-header-action-primary">
                                    <i class="fas fa-pen"></i>
                                    <span><?= esc(lang('UserRole.editUser')) ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="v3-context-action patient-header-action-soft">
                                <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                                <span><?= esc(lang('UserRole.backToUsers')) ?></span>
                            </a>
                        </div>
                        <?php if ($canViewFinance): ?>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <a href="<?= base_url('finance/payouts') ?>" class="v3-context-action patient-header-action-emerald">
                                    <i class="fas fa-wallet"></i>
                                    <span><?= esc(lang('Finance.openPayoutLedger')) ?></span>
                                </a>
                                <a href="<?= base_url('finance') ?>" class="v3-context-action patient-header-action-gold">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span><?= esc(lang('Finance.finance')) ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="grid gap-2 <?= $isSelf ? 'sm:grid-cols-2' : 'sm:grid-cols-1' ?>">
                            <?php if ($canEditUsers): ?>
                                <?php if ($isSelf): ?>
                                    <a href="<?= workflow_context_url('users/' . (int) $viewedUser->id . '/change-password') ?>" class="v3-context-action">
                                        <i class="fas fa-key"></i>
                                        <span><?= esc(lang('UserRole.changePassword')) ?></span>
                                    </a>
                                <?php endif; ?>
                                <button type="button" onclick="toggleStatus(<?= (int) $viewedUser->id ?>)" <?= $isGloballyDisabled && !$membershipActive ? 'disabled aria-disabled="true"' : '' ?> class="user-inline-toggle <?= $membershipActive ? 'is-active' : '' ?>">
                                    <span class="user-inline-toggle-track" aria-hidden="true"></span>
                                    <span><?= esc(($membershipActive ? lang('UserRole.deactivate') : lang('UserRole.activate')) . ' ' . lang('UserRole.clinicAccess')) ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($canDeleteUsers): ?>
                            <div class="grid gap-2 sm:grid-cols-1">
                                <button type="button" onclick="deleteUser(<?= (int) $viewedUser->id ?>)" class="v3-context-action patient-header-action-danger">
                                    <i class="fas fa-trash"></i>
                                    <span><?= esc(lang('UserRole.removeClinicAccess')) ?></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            </section>

            <section class="user-snapshot-section p-4 sm:p-5">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="user-snapshot-heading">
                        <p class="user-snapshot-eyebrow"><?= esc(lang('UserRole.userDetails')) ?></p>
                        <h2 class="user-snapshot-title"><?= esc(lang('UserRole.statusAndGroups')) ?></h2>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 border px-3 py-2 text-sm font-bold">
                        <i class="fas fa-user-shield"></i>
                        <span><?= esc($userRoleSummary) ?></span>
                    </span>
                </div>
                <div class="user-snapshot-grid">
                    <div class="user-snapshot-card">
                        <p class="user-snapshot-label"><?= esc(lang('UserRole.statusAndGroups')) ?></p>
                        <p class="user-snapshot-value"><?= esc($statusText) ?></p>
                        <p class="user-snapshot-meta"><?= esc(lang('UserRole.lastLoginColon')) ?> <?= esc($lastLoginText) ?></p>
                    </div>
                    <div class="user-snapshot-card">
                        <p class="user-snapshot-label"><?= esc(lang('UserRole.roles')) ?></p>
                        <p class="user-snapshot-value"><?= esc((string) count($viewedUserRoles)) ?></p>
                        <p class="user-snapshot-meta"><?= esc($departmentText) ?></p>
                    </div>
                    <div class="user-snapshot-card">
                        <p class="user-snapshot-label"><?= esc(lang('Finance.providerShareTotal')) ?></p>
                        <p class="user-snapshot-value" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_share_total')) ?></p>
                        <p class="user-snapshot-meta"><?= esc(lang('Finance.paidToProvider')) ?>: <span dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_paid_total')) ?></span></p>
                    </div>
                    <div class="user-snapshot-card">
                        <p class="user-snapshot-label"><?= esc(lang('Finance.balance')) ?></p>
                        <p class="user-snapshot-value" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_balance_total')) ?></p>
                        <p class="user-snapshot-meta"><?= esc(lang('Finance.providerPayoutCount')) ?>: <?= esc((string) ($providerSummary['payout_count'] ?? 0)) ?></p>
                    </div>
                </div>
            </section>

            <p class="p-4 text-sm"><?= esc(lang('UserRole.identityManagedByOwner')) ?> <a href="<?= base_url('profile') ?>" class="underline"><?= esc(lang('UserRole.profileRedirectNotice')) ?></a></p>
            <?php if ($isGloballyDisabled): ?><p class="bg-rose-50 p-4 text-sm text-rose-900"><?= esc(lang('UserRole.globallyDisabledWarning')) ?></p><?php endif; ?>
            <div class="user-workspace-section">
            <div class="user-profile-grid">
                <section class="user-section-card user-section-card-roles">
                    <div class="user-section-header">
                        <h2 class="user-section-title">
                            <span class="user-section-title-dot"></span>
                            <span><?= esc(lang('UserRole.rolesAndPermissions')) ?></span>
                        </h2>
                        <p class="user-section-subtitle"><?= esc(lang('UserRole.manageUsersRolesPermissions')) ?></p>
                    </div>
                    <div class="user-section-body space-y-5">
                        <div class="user-info-grid">
                            <div class="user-info-tile">
                                <p class="user-info-label"><i class="fas fa-user-shield"></i><?= esc(lang('UserRole.roleAssignment')) ?></p>
                                <p class="user-info-value"><?= esc($userRoleSummary) ?></p>
                                <div class="mt-3 user-role-list">
                                    <?php if (!empty($viewedUserRoles)): ?>
                                        <?php foreach ($viewedUserRoles as $role): ?>
                                            <?php
                                                $roleName = is_array($role)
                                                    ? ($role['name'] ?? lang('UserRole.unknownRole'))
                                                    : ($role->name ?? lang('UserRole.unknownRole'));
                                            ?>
                                            <span class="user-role-pill"><?= esc(ucfirst((string) $roleName)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="user-summary-badge"><?= esc(lang('UserRole.noRolesAssigned')) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="user-info-tile">
                                <p class="user-info-label"><i class="fas fa-layer-group"></i><?= esc(lang('UserRole.roles')) ?></p>
                                <p class="user-info-value"><?= esc((string) count($viewedUserRoles)) ?></p>
                                <p class="user-info-help"><?= esc(lang('UserRole.availableRoles')) ?></p>
                            </div>
                            <div class="user-info-tile">
                                <p class="user-info-label"><i class="fas fa-building-user text-sky-500"></i><?= esc(lang('UserRole.department')) ?></p>
                                <p class="user-info-value"><?= esc($departmentText) ?></p>
                                <p class="user-info-help"><?= esc(lang('UserRole.userDetails')) ?></p>
                            </div>
                            <div class="user-info-tile">
                                <p class="user-info-label"><i class="fas fa-calendar-check text-emerald-500"></i><?= esc(lang('UserRole.hireDateRequired')) ?></p>
                                <p class="user-info-value"><?= esc($hireDateText) ?></p>
                                <p class="user-info-help"><?= esc(lang('UserRole.createdColon')) ?> <?= esc($createdAtText) ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="user-section-card user-section-card-actions">
                    <div class="user-section-header">
                        <h2 class="user-section-title">
                            <span class="user-section-title-dot"></span>
                            <span><?= esc(lang('UserRole.actions')) ?></span>
                        </h2>
                        <p class="user-section-subtitle"><?= esc(lang('UserRole.viewUserInformationPermissions')) ?></p>
                    </div>
                    <div class="user-section-body">
                        <div class="user-action-list">
                            <?php if ($canEditUsers): ?>
                                <a href="<?= workflow_context_url('users/' . (int) $viewedUser->id . '/edit') ?>" class="user-action-row">
                                    <span class="user-action-row-main">
                                        <span class="user-action-row-title"><i class="fas fa-pen text-sky-500"></i><?= esc(lang('UserRole.editUser')) ?></span>
                                        <span class="user-action-row-copy"><?= esc(lang('UserRole.updateUserInformationDescription')) ?></span>
                                    </span>
                                    <span class="user-action-row-icon"><i class="fas fa-arrow-right user-action-row-arrow"></i></span>
                                </a>
                                <button type="button" onclick="openInlineModal('roleModal')" class="user-action-row user-action-row-modal">
                                    <span class="user-action-row-main">
                                        <span class="user-action-row-title"><i class="fas fa-user-tag"></i><?= esc(lang('UserRole.roleAssignment')) ?></span>
                                        <span class="user-action-row-copy"><?= esc(lang('UserRole.chooseRole')) ?></span>
                                    </span>
                                    <span class="user-action-row-icon"><i class="fas fa-window-restore"></i></span>
                                </button>
                                <?php if ($isSelf): ?>
                                    <a href="<?= workflow_context_url('users/' . (int) $viewedUser->id . '/change-password') ?>" class="user-action-row">
                                        <span class="user-action-row-main">
                                            <span class="user-action-row-title"><i class="fas fa-key text-amber-500"></i><?= esc(lang('UserRole.accountSecurity')) ?></span>
                                            <span class="user-action-row-copy"><?= esc(lang('UserRole.changePassword')) ?></span>
                                        </span>
                                        <span class="user-action-row-icon"><i class="fas fa-arrow-right user-action-row-arrow"></i></span>
                                    </a>
                                <?php else: ?>
                                    <div class="user-action-row opacity-80 cursor-default">
                                        <span class="user-action-row-main">
                                            <span class="user-action-row-title"><i class="fas fa-shield-alt"></i><?= esc(lang('UserRole.accountSecurity')) ?></span>
                                            <span class="user-action-row-copy"><?= esc(lang('UserRole.cannotResetOtherUserPassword')) ?></span>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($canViewFinance): ?>
                                <a href="<?= base_url('finance/payouts') ?>" class="user-action-row">
                                    <span class="user-action-row-main">
                                        <span class="user-action-row-title"><i class="fas fa-wallet text-emerald-500"></i><?= esc(lang('Finance.openPayoutLedger')) ?></span>
                                        <span class="user-action-row-copy"><?= esc(lang('Finance.payoutLedger')) ?></span>
                                    </span>
                                    <span class="user-action-row-icon"><i class="fas fa-arrow-right user-action-row-arrow"></i></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <?php if ($canViewFinance): ?>
                <section class="user-section-card user-section-card-finance">
                    <div class="user-section-header">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="user-section-title">
                                    <span class="user-section-title-dot"></span>
                                    <span><?= esc(lang('Finance.clinicalProviderCompensation')) ?></span>
                                </h2>
                                <p class="user-section-subtitle"><?= esc(lang('Finance.clinicalProviderCompensationHelp')) ?></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="<?= base_url('finance/payouts') ?>" class="inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition">
                                    <i class="fas fa-wallet text-xs"></i>
                                    <span><?= esc(lang('Finance.openPayoutLedger')) ?></span>
                                </a>
                                <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition">
                                    <i class="fas text-xs"></i>
                                    <span><?= esc(lang('Finance.finance')) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="user-section-body">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-percent"></i><?= esc(lang('Finance.providerShareTotal')) ?></p>
                                <p class="user-info-value"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_share_total')) ?></p>
                            </div>
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-money-bill-wave text-emerald-500"></i><?= esc(lang('Finance.paidToProvider')) ?></p>
                                <p class="user-info-value"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_paid_total')) ?></p>
                            </div>
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-triangle-exclamation text-rose-500"></i><?= esc(lang('Finance.balance')) ?></p>
                                <p class="user-info-value"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_balance_total')) ?></p>
                            </div>
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-receipt"></i><?= esc(lang('Finance.providerPayoutCount')) ?></p>
                                <p class="user-info-value"><?= esc((string) ($providerSummary['payout_count'] ?? 0)) ?></p>
                            </div>
                        </div>
                        <div class="user-payment-summary-grid">
                            <div class="user-payment-summary-card">
                                <div class="user-payment-summary-head">
                                    <span class="user-payment-summary-title">
                                        <i class="fas fa-money-bill-wave text-emerald-500 text-xs"></i>
                                        <span><?= esc(lang('Finance.paymentHistory')) ?></span>
                                    </span>
                                    <span class="user-summary-badge">
                                        <i class="fas fa-list-check text-[11px]"></i>
                                        <span><?= esc((string) count($providerPaymentRows)) ?> <?= esc(lang('Finance.paymentsCount')) ?></span>
                                    </span>
                                </div>
                                <?php if (!empty($recentProviderPaymentRows)): ?>
                                    <div class="user-payment-summary-list">
                                        <?php foreach ($recentProviderPaymentRows as $row): ?>
                                            <?php $patientAvatar = $patientAvatarFor($row['patient_gender'] ?? null); ?>
                                            <a href="<?= esc(workflow_context_url('finance/' . (int) ($row['finance_id'] ?? 0), 'users/' . (int) ($viewedUser->id ?? 0)), 'attr') ?>" class="user-payment-summary-item">
                                                <span class="user-payment-summary-main">
                                                    <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center">
                                                        <i class="<?= esc($patientAvatar['icon']) ?> <?= esc($patientAvatar['color']) ?> text-base"></i>
                                                    </span>
                                                    <span class="user-payment-summary-copy">
                                                        <strong><?= esc((string) (((string) ($row['patient_name'] ?? '')) !== '' ? $row['patient_name'] : lang('Finance.notAvailable'))) ?></strong>
                                                        <span>
                                                            <?= esc((string) ($row['transaction_id'] ?? '-')) ?>
                                                            <span class=""><?= esc('•') ?></span>
                                                            <?= esc(lang('Finance.' . ((string) ($row['payment_method'] ?? 'cash')))) ?>
                                                        </span>
                                                    </span>
                                                </span>
                                                <span class="user-payment-summary-meta">
                                                    <span class="user-payment-summary-amount"><?= esc(\App\Services\ProviderCurrencySummary::native($row['amount'] ?? 0, $row['currency'] ?? null)) ?></span>
                                                    <span class="user-payment-summary-date"><?= !empty($row['paid_date']) ? esc(formatDate((string) $row['paid_date'])) : esc(lang('Finance.notAvailable')) ?></span>
                                                </span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="user-payment-summary-empty"><?= esc(lang('Finance.noPayoutsAvailable')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="user-payment-summary-card">
                                <div class="user-payment-summary-head">
                                    <span class="user-payment-summary-title">
                                        <i class="fas fa-chart-line text-xs"></i>
                                        <span><?= esc(lang('Finance.clinicalProviderCompensation')) ?></span>
                                    </span>
                                </div>
                                <div class="space-y-3">
                                    <div class="user-info-tile user-finance-tile">
                                        <p class="user-info-label"><i class="fas fa-money-bill-wave text-emerald-500"></i><?= esc(lang('Finance.paidToProvider')) ?></p>
                                        <p class="user-info-value"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_paid_total')) ?></p>
                                    </div>
                                    <div class="user-info-tile user-finance-tile">
                                        <p class="user-info-label"><i class="fas fa-triangle-exclamation text-rose-500"></i><?= esc(lang('Finance.balance')) ?></p>
                                        <p class="user-info-value"><?= esc(\App\Services\ProviderCurrencySummary::format($providerSummary, 'provider_balance_total')) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($canViewFinance): ?>
                <section class="user-section-card user-section-card-ledger">
                    <div class="user-section-header">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="user-section-title">
                                    <span class="user-section-title-dot"></span>
                                    <span><?= esc(lang('Finance.payoutLedger')) ?></span>
                                </h2>
                                <p class="user-section-subtitle"><?= esc(lang('Finance.clinicalProviderCompensationHelp')) ?></p>
                            </div>
                            <div class="user-summary-badge">
                                <i class="fas fa-receipt text-[11px]"></i>
                                <span><?= esc((string) ($providerSummary['payout_count'] ?? 0)) ?> <?= esc(lang('Finance.providerPayoutCount')) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($providerRows)): ?>
                        <div class="overflow-x-auto">
                            <table class="user-record-table min-w-full" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                <colgroup>
                                    <col style="width: 20%;">
                                    <col style="width: 16%;">
                                    <col style="width: 12%;">
                                    <col style="width: 28%;">
                                    <col style="width: 15%;">
                                    <col style="width: 9%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-receipt <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.transaction')) ?></th>
                                        <th><i class="fas fa-user <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.patient')) ?></th>
                                        <th><i class="fas fa-calendar <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.businessDate')) ?></th>
                                        <th><i class="fas fa-wallet <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.paidBalance')) ?></th>
                                        <th><i class="fas fa-money-check-dollar <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.lastPaymentDate')) ?></th>
                                        <th><i class="fas fa-cogs <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.actions')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($providerRows as $row): ?>
                                        <?php
                                            $rowStatus = strtolower((string) ($row['status'] ?? 'unknown'));
                                            $transactionMeta = $transactionMetaFor($row['transaction_type'] ?? null);
                                            $patientAvatar = $patientAvatarFor($row['patient_gender'] ?? null);
                                            $rowStatusClass = match ($rowStatus) {
                                                'paid' => 'user-ledger-status user-ledger-status-paid',
                                                'partial' => 'user-ledger-status user-ledger-status-partial',
                                                'void' => 'user-ledger-status user-ledger-status-void',
                                                default => 'user-ledger-status user-ledger-status-unpaid',
                                            };
                                            $canSettlePayout = $canEditFinance && !in_array($rowStatus, ['paid', 'void'], true);
                                        ?>
                                        <tr class="user-record-row" tabindex="0" data-transaction-url="<?= esc(workflow_context_url('finance/' . (int) ($row['finance_id'] ?? 0), 'users/' . (int) ($viewedUser->id ?? 0)), 'attr') ?>" title="<?= esc(lang('Finance.viewTransaction')) ?>">
                                            <td>
                                                <div class="user-record-cell">
                                                    <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg">
                                                        <i class="<?= esc($transactionMeta['icon']) ?> <?= esc($transactionMeta['color']) ?> text-sm"></i>
                                                    </span>
                                                    <div class="user-record-main">
                                                        <span class="user-record-title"><?= esc((string) ($row['transaction_id'] ?? '-')) ?></span>
                                                        <?php if ((int) ($row['clinical_line_count'] ?? 0) > 0): ?>
                                                            <span class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                                                                <span class="inline-flex items-center bg-emerald-50 px-2 py-0.5 text-emerald-700">
                                                                    <?= esc(lang('Finance.clinicalTraceLines')) ?>: <?= esc((string) ($row['clinical_line_count'] ?? 0)) ?>
                                                                </span>
                                                                <span class="inline-flex items-center px-2 py-0.5">
                                                                    <?= esc(lang('Finance.clinicalTraceTreatments')) ?>: <?= esc((string) ($row['treatment_count'] ?? 0)) ?>
                                                                </span>
                                                                <span class="inline-flex items-center px-2 py-0.5">
                                                                    <?= esc(lang('Finance.clinicalTraceExaminations')) ?>: <?= esc((string) ($row['examination_count'] ?? 0)) ?>
                                                                </span>
                                                            </span>
                                                            <?php if (trim((string) ($row['clinical_procedures'] ?? '')) !== ''): ?>
                                                                <span class="mt-1 block max-w-xs truncate text-xs font-medium" title="<?= esc((string) $row['clinical_procedures'], 'attr') ?>">
                                                                    <?= esc((string) $row['clinical_procedures']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-record-cell">
                                                    <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg">
                                                        <i class="<?= esc($patientAvatar['icon']) ?> <?= esc($patientAvatar['color']) ?> text-base"></i>
                                                    </span>
                                                    <div class="user-record-main">
                                                        <span class="user-record-title"><?= esc((string) (((string) ($row['patient_name'] ?? '')) !== '' ? $row['patient_name'] : lang('Finance.notAvailable'))) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap">
                                                <?= !empty($row['business_date']) ? esc(formatDate((string) $row['business_date'])) : '&mdash;' ?>
                                            </td>
                                            <td>
                                                <div class="inline-flex max-w-full items-center gap-1.5 whitespace-nowrap">
                                                    <span class="font-semibold"><?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_share_amount'] ?? 0, $row['currency'] ?? null)) ?></span>
                                                    <span class="text-emerald-700"><?= esc(lang('Finance.paid')) ?> <?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_paid_amount'] ?? 0, $row['currency'] ?? null)) ?></span>
                                                    <span class="text-rose-700"><?= esc(lang('Finance.balance')) ?> <?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_balance_due'] ?? 0, $row['currency'] ?? null)) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-record-main">
                                                    <span class="user-record-title"><?= !empty($row['last_paid_date']) ? esc(formatDate((string) $row['last_paid_date'])) : esc(lang('UserRole.never')) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-record-actions">
                                                    <span class="<?= esc($rowStatusClass) ?>"><?= esc(lang('Finance.' . $rowStatus)) ?></span>
                                                    <a href="<?= esc(workflow_context_url('finance/' . (int) ($row['finance_id'] ?? 0), 'users/' . (int) ($viewedUser->id ?? 0)), 'attr') ?>" class="p-2 rounded-md transition-colors duration-200" data-row-action="true" title="<?= esc(lang('Finance.viewTransaction')) ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($canSettlePayout): ?>
                                                        <button type="button" data-row-action="true" onclick="openPayoutModal(<?= (int) $row['id'] ?>, <?= esc(number_format((float) ($row['provider_balance_due'] ?? 0), 2, '.', '')) ?>, <?= esc(json_encode(\App\Services\ProviderCurrencySummary::currency($row['currency'] ?? null)), 'attr') ?>)" class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="<?= esc(lang('Finance.recordPaymentFromLedger')) ?>">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </button>
                                                        <button type="button" data-row-action="true" onclick="markPayoutPaid(<?= (int) $row['id'] ?>)" class="p-2 rounded-md transition-colors duration-200" title="<?= esc(lang('Finance.markFullyPaid')) ?>">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="px-6 py-16 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center">
                                <i class="fas fa-wallet text-xl"></i>
                            </div>
                            <h3 class="mt-4 text-lg font-bold"><?= esc(lang('Finance.noClinicalProviderPayouts')) ?></h3>
                            <p class="mx-auto mt-2 max-w-2xl text-sm"><?= esc(lang('Finance.noClinicalProviderPayoutsHelp')) ?></p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            </div>

<?php if ($canEditUsers): ?>
    <div id="roleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="user-inline-modal-backdrop fixed inset-0"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeInlineModal('roleModal')">
            <div class="w-full max-w-lg border" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between border-b px-6 py-4">
                    <div>
                        <h4 class="text-base font-semibold"><?= esc(lang('UserRole.roleAssignment')) ?></h4>
                        <p class="mt-1 text-sm"><?= esc(lang('UserRole.chooseRole')) ?></p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition" onclick="closeInlineModal('roleModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="inlineRoleForm" class="space-y-5">
                    <div>
                        <label for="inlineRoleSelect" class="mb-1 block text-sm font-semibold"><?= esc(lang('UserRole.role')) ?></label>
                        <select id="inlineRoleSelect" name="role_id" class="user-modal-select w-full px-4 py-3 border-2 focus:outline-none focus:ring-4" data-searchable-select data-minimum-input-length="0" data-placeholder="<?= esc(lang('UserRole.selectRole')) ?>" required>
                            <option value=""><?= esc(lang('UserRole.selectRole')) ?></option>
                            <?php foreach ($assignableRoles as $role): ?>
                                <option value="<?= (int) ($role['id'] ?? 0) ?>" <?= $currentRoleId === (int) ($role['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($role['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" onclick="closeInlineModal('roleModal')" class="inline-flex items-center gap-2 border px-4 py-2.5 text-sm font-semibold transition">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('UserRole.cancel')) ?></span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition">
                            <i class="fas fa-user-tag text-xs"></i>
                            <span><?= esc(lang('UserRole.assignRole')) ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php if ($canEditFinance): ?>
    <div id="recordPayoutModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closePayoutModal()">
            <div class="w-full max-w-md border" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between border-b px-5 py-4">
                    <h4 class="text-base font-semibold"><?= esc(lang('Finance.recordProviderPayout')) ?></h4>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition" onclick="closePayoutModal()">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="recordPayoutForm" class="space-y-4">
                    <input type="hidden" id="payoutIdInput">
                    <div>
                        <label for="payoutAmountInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider"><?= esc(lang('Finance.amount')) ?></label>
                        <input type="number" step="0.01" min="0.01" id="payoutAmountInput" class="w-full border px-3 py-2.5 text-sm focus:outline-none focus:ring-4" required>
                        <p id="payoutAmountHelp" class="mt-1 text-xs"></p>
                    </div>
                    <div>
                        <label for="payoutMethodInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider"><?= esc(lang('Finance.paymentMethod')) ?></label>
                        <select id="payoutMethodInput" class="user-modal-select w-full px-4 py-3 border-2 focus:outline-none focus:ring-4" data-searchable-select data-minimum-input-length="0" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>" data-allow-clear="false">
                            <option value="cash"><?= esc(lang('Finance.cash')) ?></option>
                            <option value="card"><?= esc(lang('Finance.card')) ?></option>
                            <option value="bank_transfer"><?= esc(lang('Finance.bankTransfer')) ?></option>
                            <option value="check"><?= esc(lang('Finance.check')) ?></option>
                            <option value="other"><?= esc(lang('Finance.other')) ?></option>
                        </select>
                    </div>
                    <div id="payoutLiquidityAccountField" class="hidden">
                        <label for="payoutLiquidityAccountInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider"><?= esc(lang('Finance.paidFromAccount')) ?></label>
                        <select id="payoutLiquidityAccountInput" class="user-modal-select w-full px-4 py-3 border-2 focus:outline-none focus:ring-4" data-searchable-select data-minimum-input-length="0" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>" data-allow-clear="false">
                            <option value="bank"><?= esc(lang('Finance.liquidityAccount.bank')) ?></option>
                            <option value="undeposited_checks"><?= esc(lang('Finance.liquidityAccount.undeposited_checks')) ?></option>
                        </select>
                        <p class="mt-1 text-xs"><?= esc(lang('Finance.checkOutgoingAccountHelp')) ?></p>
                    </div>
                    <div>
                        <label for="payoutDateInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider"><?= esc(lang('Finance.paidDate')) ?></label>
                        <input type="date" id="payoutDateInput" class="w-full border px-3 py-2.5 text-sm focus:outline-none focus:ring-4" required>
                    </div>
                    <div>
                        <label for="payoutNotesInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider"><?= esc(lang('Finance.notes')) ?></label>
                        <textarea id="payoutNotesInput" rows="2" class="w-full border px-3 py-2.5 text-sm focus:outline-none focus:ring-4" placeholder="<?= esc(lang('Finance.optional')) ?>"></textarea>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" onclick="closePayoutModal()" class="inline-flex items-center gap-2 border px-4 py-2.5 text-sm font-semibold transition">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition">
                            <i class="fas fa-money-bill-wave text-xs"></i>
                            <span><?= esc(lang('Finance.recordPaymentFromLedger')) ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function notify(type, title, message) {
    if (typeof window.showToast === 'function') {
        window.showToast(type, title, message);
        return;
    }
    alert(message);
}

function mountUserModal(id) {
    const modal = document.getElementById(id);
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    return modal;
}

function openInlineModal(id) {
    mountUserModal(id)?.classList.remove('hidden');
}

function closeInlineModal(id) {
    document.getElementById(id)?.classList.add('hidden');
}

function closeOpenUserModals() {
    ['roleModal'].forEach((id) => {
        const modal = document.getElementById(id);
        if (modal && !modal.classList.contains('hidden')) {
            closeInlineModal(id);
        }
    });

    const payoutModal = document.getElementById('recordPayoutModal');
    if (payoutModal && !payoutModal.classList.contains('hidden') && typeof closePayoutModal === 'function') {
        closePayoutModal();
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeOpenUserModals();
    }
});

document.querySelectorAll('.user-record-row[data-transaction-url]').forEach((row) => {
    const openTransaction = () => {
        const url = row.dataset.transactionUrl;
        if (url) {
            window.location.href = window.workflowContextUrl ? window.workflowContextUrl(url) : url;
        }
    };

    row.addEventListener('click', function (event) {
        if (event.target.closest('[data-row-action], a, button, input, select, textarea')) {
            return;
        }
        openTransaction();
    });

    row.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        event.preventDefault();
        openTransaction();
    });
});

function toggleStatus(id) {
    if (!confirm(<?= json_encode(lang('UserRole.confirmToggleUserStatus')) ?>)) {
        return;
    }

    fetch(`<?= base_url('users') ?>/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || <?= json_encode(lang('UserRole.errorUpdatingUserStatus')) ?>);
        }
        location.reload();
    })
    .catch(error => {
        notify('error', <?= json_encode(lang('UserRole.error')) ?>, error.message || <?= json_encode(lang('UserRole.errorUpdatingUserStatus')) ?>);
    });
}

function deleteUser(id) {
    if (!confirm(<?= json_encode(lang('UserRole.confirmRemoveClinicAccess')) ?>)) {
        return;
    }

    fetch(`<?= base_url('users') ?>/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || <?= json_encode(lang('UserRole.errorDeletingUser')) ?>);
        }
        window.location.href = <?= json_encode(workflow_back('users', $return_to ?? null)) ?>;
    })
    .catch(error => {
        notify('error', <?= json_encode(lang('UserRole.error')) ?>, error.message || <?= json_encode(lang('UserRole.errorDeletingUser')) ?>);
    });
}

<?php if ($canEditUsers): ?>
function submitInlineUserForm(formId, url, successMessage, onSuccess) {
    const form = document.getElementById(formId);
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(form);

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (!data.success) {
                throw new Error(data.message || <?= json_encode(lang('UserRole.error')) ?>);
            }
            notify('success', <?= json_encode(lang('UserRole.success')) ?>, data.message || successMessage);
            if (typeof onSuccess === 'function') {
                onSuccess();
            } else {
                location.reload();
            }
        })
        .catch(error => {
            notify('error', <?= json_encode(lang('UserRole.error')) ?>, error.message || <?= json_encode(lang('UserRole.error')) ?>);
        });
    });
}


submitInlineUserForm(
    'inlineRoleForm',
    `<?= base_url('users/' . (int) $viewedUser->id . '/inline-update-role') ?>`,
    <?= json_encode('Role updated successfully') ?>,
    () => {
        closeInlineModal('roleModal');
        location.reload();
    }
);

<?php endif; ?>

<?php if ($canEditFinance): ?>
let payoutModalMaxAmount = 0;

function formatCurrencyValue(value) {
    const amount = Number(value || 0);
    const normalized = Number.isFinite(amount) ? amount : 0;
    return normalized.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function closePayoutModal() {
    document.getElementById('recordPayoutModal').classList.add('hidden');
    document.getElementById('recordPayoutForm').reset();
}

let payoutModalCurrency = "UNKNOWN";
function openPayoutModal(id, maxAmount, currency) {
    payoutModalCurrency = currency || "UNKNOWN";
    mountUserModal('recordPayoutModal');
    payoutModalMaxAmount = Number(maxAmount || 0);
    document.getElementById('payoutIdInput').value = String(id);
    document.getElementById('payoutAmountInput').value = payoutModalMaxAmount > 0 ? payoutModalMaxAmount.toFixed(2) : '';
    document.getElementById('payoutDateInput').value = new Date().toISOString().split('T')[0];
    document.getElementById('payoutAmountHelp').textContent = `<?= esc(lang('Finance.maximumPayable')) ?>: ${formatCurrencyValue(payoutModalMaxAmount)} ${payoutModalCurrency}`;
    syncPayoutLiquidityField();
    document.getElementById('recordPayoutModal').classList.remove('hidden');
}

function syncPayoutLiquidityField() {
    const method = document.getElementById('payoutMethodInput')?.value || '';
    const field = document.getElementById('payoutLiquidityAccountField');
    if (!field) {
        return;
    }
    field.classList.toggle('hidden', method !== 'check');
}

function submitPayoutPayment(id, amount, method, liquidityAccount, paidDate, notes) {
    const formData = new FormData();
    formData.append('amount', Number(amount).toFixed(2));
    formData.append('payment_method', method);
    if (method === 'check') {
        formData.append('liquidity_account', liquidityAccount);
    }
    formData.append('paid_date', paidDate);
    formData.append('notes', notes);

    fetch(`<?= base_url('finance/payouts') ?>/${id}/payments`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (!data.success) {
            throw new Error(data.message || <?= json_encode(lang('Finance.failedToRecordPayoutPayment')) ?>);
        }
        closePayoutModal();
        notify('success', <?= json_encode(lang('Finance.paymentRecorded')) ?>, data.message || <?= json_encode(lang('Finance.providerPayoutPaymentRecorded')) ?>);
        location.reload();
    })
    .catch(error => {
        notify('error', <?= json_encode(lang('Finance.paymentFailed')) ?>, error.message || <?= json_encode(lang('Finance.unableToRecordPayment')) ?>);
    });
}

function markPayoutPaid(id) {
    if (!confirm(<?= json_encode(lang('Finance.confirmMarkProviderPayoutFullyPaid')) ?>)) {
        return;
    }

    const formData = new FormData();
    formData.append('payment_method', 'cash');

    fetch(`<?= base_url('finance/payouts') ?>/${id}/mark-paid`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (!data.success) {
            throw new Error(data.message || <?= json_encode(lang('Finance.failedToMarkPayoutPaid')) ?>);
        }
        notify('success', <?= json_encode(lang('Finance.payoutUpdated')) ?>, data.message || <?= json_encode(lang('Finance.providerPayoutMarkedPaid')) ?>);
        location.reload();
    })
    .catch(error => {
        notify('error', <?= json_encode(lang('Finance.updateFailed')) ?>, error.message || <?= json_encode(lang('Finance.unableToMarkPayoutPaid')) ?>);
    });
}

document.getElementById('recordPayoutForm')?.addEventListener('submit', function (event) {
    event.preventDefault();

    const id = parseInt(document.getElementById('payoutIdInput').value, 10);
    const amount = parseFloat(document.getElementById('payoutAmountInput').value);
    const method = document.getElementById('payoutMethodInput').value;
    const liquidityAccount = document.getElementById('payoutLiquidityAccountInput').value;
    const paidDate = document.getElementById('payoutDateInput').value;
    const notes = document.getElementById('payoutNotesInput').value || '';

    if (!Number.isFinite(amount) || amount <= 0 || amount > payoutModalMaxAmount) {
        notify('error', <?= json_encode(lang('Finance.invalidAmount')) ?>, `<?= esc(lang('Finance.amountValidationMessage')) ?> ${formatCurrencyValue(payoutModalMaxAmount)} ${payoutModalCurrency}.`);
        return;
    }

    submitPayoutPayment(id, amount, method, liquidityAccount, paidDate, notes);
});

document.getElementById('payoutMethodInput')?.addEventListener('change', syncPayoutLiquidityField);
<?php endif; ?>
</script>
</div>
<?= $this->endSection() ?>
