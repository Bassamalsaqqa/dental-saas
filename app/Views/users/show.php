<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
    $isRtl = !empty($is_rtl);
    $viewedUser = $viewed_user ?? null;
    $viewedUserRoles = $viewed_user_roles ?? [];
    $userFullName = trim((string) (($viewedUser->first_name ?? '') . ' ' . ($viewedUser->last_name ?? '')));
    $userFullName = $userFullName !== '' ? $userFullName : lang('UserRole.user');
    $createdAtText = !empty($viewedUser->created_on) ? date('M j, Y g:i A', strtotime((string) $viewedUser->created_on)) : lang('UserRole.na');
    $lastLoginText = !empty($viewedUser->last_login) ? date('M j, Y g:i A', strtotime((string) $viewedUser->last_login)) : lang('UserRole.never');
    $statusText = (int) ($viewedUser->active ?? 0) === 1 ? lang('UserRole.active') : lang('UserRole.inactive');
    $statusClass = (int) ($viewedUser->active ?? 0) === 1
        ? 'patient-hero-status patient-hero-status-active'
        : 'patient-hero-status patient-hero-status-inactive';
    $contentStatusClass = (int) ($viewedUser->active ?? 0) === 1
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
        : 'bg-slate-100 text-slate-700 ring-slate-200';
    $providerSummary = $provider_finance_summary ?? [
        'payout_count' => 0,
        'gross_amount_total' => 0,
        'provider_share_total' => 0,
        'provider_paid_total' => 0,
        'provider_balance_total' => 0,
    ];
    $providerRows = $provider_payout_rows ?? [];
    $canEditUsers = has_permission('users', 'edit');
    $canDeleteUsers = has_permission('users', 'delete') && (int) ($viewedUser->id ?? 0) !== 1;
    $canViewFinance = has_permission('finance', 'view');
    $canEditFinance = has_permission('finance', 'edit');
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
        ['label' => lang('UserRole.statusAndGroups'), 'display_label' => '', 'value' => $userRoleSummary, 'icon' => 'fas fa-user-shield'],
        ['label' => lang('UserRole.lastLoginColon'), 'display_label' => '', 'value' => $lastLoginText, 'icon' => 'fas fa-clock'],
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

<style>
    .patient-hero-card {
        position: relative;
        --hero-angle: 135deg;
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        --hero-orb-primary: rgba(255, 255, 255, 0.18);
        --hero-orb-secondary: rgba(125, 211, 252, 0.2);
        background: linear-gradient(var(--hero-angle), var(--hero-start) 0%, var(--hero-mid) 58%, var(--hero-end) 100%);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, 0.75);
    }

    .patient-hero-card-male {
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        --hero-orb-primary: rgba(255, 255, 255, 0.18);
        --hero-orb-secondary: rgba(125, 211, 252, 0.2);
    }

    .patient-hero-card-female {
        --hero-start: #8a245a;
        --hero-mid: #c03579;
        --hero-end: #7c3aed;
        --hero-orb-primary: rgba(255, 255, 255, 0.16);
        --hero-orb-secondary: rgba(251, 207, 232, 0.24);
    }

    .patient-hero-card-neutral {
        --hero-start: #334155;
        --hero-mid: #2563eb;
        --hero-end: #0f766e;
        --hero-orb-primary: rgba(255, 255, 255, 0.16);
        --hero-orb-secondary: rgba(153, 246, 228, 0.2);
    }

    [dir="rtl"] .patient-hero-card {
        --hero-angle: 225deg;
    }

    .patient-hero-overlay {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 18%, var(--hero-orb-primary), transparent 30%),
            radial-gradient(circle at 88% 20%, var(--hero-orb-secondary), transparent 26%),
            linear-gradient(120deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
    }

    [dir="rtl"] .patient-hero-overlay {
        background:
            radial-gradient(circle at 88% 18%, var(--hero-orb-primary), transparent 30%),
            radial-gradient(circle at 12% 20%, var(--hero-orb-secondary), transparent 26%),
            linear-gradient(240deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
    }

    .patient-avatar-badge {
        display: inline-flex;
        width: 5.5rem;
        height: 5.5rem;
        align-items: center;
        justify-content: center;
        border-radius: 1.75rem;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(191, 219, 254, 0.18));
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 22px 40px -24px rgba(15, 23, 42, 0.72);
    }

    .patient-avatar-badge i {
        font-size: 2.55rem;
        line-height: 1;
        display: inline-block;
    }

    .patient-avatar-badge-male {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(191, 219, 254, 0.18));
    }

    .patient-avatar-badge-female {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(244, 114, 182, 0.18));
    }

    .patient-avatar-badge-neutral {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(94, 234, 212, 0.16));
    }

    .patient-hero-chip-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.85rem;
    }

    @media (min-width: 640px) {
        .patient-hero-chip-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1100px) {
        .patient-hero-chip-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .patient-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 2.65rem;
        width: 100%;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        padding: 0.52rem 0.82rem;
        color: #eff6ff;
        backdrop-filter: blur(12px);
        text-align: start;
        overflow: hidden;
    }

    .patient-hero-chip-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.12);
        flex-shrink: 0;
        font-size: 0.78rem;
    }

    .patient-hero-chip-text {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        min-width: 0;
    }

    .patient-hero-chip-label {
        display: inline;
        flex-shrink: 0;
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.2;
        color: rgba(219, 234, 254, 0.84);
    }

    .patient-hero-chip-value {
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.76rem;
        font-weight: 500;
        line-height: 1.25;
        color: #ffffff;
    }

    .patient-hero-chip-ltr .patient-hero-chip-value {
        direction: ltr;
    }

    .patient-hero-status {
        color: #eff6ff;
        backdrop-filter: blur(12px);
    }

    .patient-hero-status-active {
        background: rgba(16, 185, 129, 0.18);
        border-color: rgba(167, 243, 208, 0.4);
    }

    .patient-hero-status-inactive {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .patient-header-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.58rem;
        min-height: 2.65rem;
        border-radius: 0.95rem;
        padding: 0.58rem 0.78rem;
        font-size: 0.76rem;
        font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        text-align: center;
    }

    .patient-header-action:hover,
    .patient-hero-clinical-button:hover,
    .patient-hero-tool-button:hover {
        transform: translateY(-1px);
    }

    .patient-header-action-soft {
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        color: #eff6ff;
    }

    .patient-header-action-primary {
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        color: #eff6ff;
        box-shadow: none;
    }

    .patient-header-action-emerald {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        box-shadow: 0 12px 22px -20px rgba(5, 150, 105, 0.85);
    }

    .patient-header-action-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #ffffff;
        box-shadow: 0 12px 22px -20px rgba(217, 119, 6, 0.85);
    }

    .patient-header-action-danger {
        background: linear-gradient(135deg, #f43f5e, #e11d48);
        color: #ffffff;
        box-shadow: 0 12px 22px -20px rgba(225, 29, 72, 0.88);
    }

    .patient-hero-clinical-button,
    .patient-hero-tool-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        min-height: 2.4rem;
        border-radius: 1rem;
        padding: 0.48rem 0.72rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #eff6ff;
        backdrop-filter: blur(12px);
        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        text-align: center;
    }

    .patient-hero-clinical-button {
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.14);
        box-shadow: 0 10px 24px -20px rgba(15, 23, 42, 0.45);
    }

    .patient-hero-tool-button {
        border: 1px dashed rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.09);
        color: rgba(239, 246, 255, 0.92);
    }

    .patient-hero-tool-button-danger {
        border-color: rgba(254, 205, 211, 0.4);
        background: rgba(244, 63, 94, 0.16);
        color: #fff1f2;
    }

    .user-role-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        background: #eef2ff;
        padding: 0.4rem 0.78rem;
        font-size: 0.76rem;
        font-weight: 600;
        color: #4338ca;
        box-shadow: inset 0 0 0 1px rgba(165, 180, 252, 0.55);
    }

    .user-summary-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 9999px;
        background: #f8fafc;
        padding: 0.4rem 0.78rem;
        font-size: 0.76rem;
        font-weight: 600;
        color: #475569;
        box-shadow: inset 0 0 0 1px rgba(226, 232, 240, 0.9);
    }

    .user-ledger-status {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 0.3rem 0.7rem;
        font-size: 0.76rem;
        font-weight: 600;
        box-shadow: inset 0 0 0 1px transparent;
    }

    .user-ledger-status-paid {
        background: #ecfdf3;
        color: #166534;
        box-shadow: inset 0 0 0 1px rgba(167, 243, 208, 0.9);
    }

    .user-ledger-status-partial {
        background: #fff7ed;
        color: #9a3412;
        box-shadow: inset 0 0 0 1px rgba(253, 186, 116, 0.8);
    }

    .user-ledger-status-void {
        background: #f8fafc;
        color: #475569;
        box-shadow: inset 0 0 0 1px rgba(203, 213, 225, 0.9);
    }

    .user-ledger-status-unpaid {
        background: #fff1f2;
        color: #be123c;
        box-shadow: inset 0 0 0 1px rgba(254, 205, 211, 0.95);
    }
</style>

<div class="min-h-screen bg-slate-50/80" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <section class="patient-hero-card <?= esc($userHeroThemeClass) ?> overflow-hidden rounded-[28px] border border-slate-200 text-white">
                <div class="patient-hero-overlay"></div>
                <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                            <span><?= esc(lang('UserRole.userDetails')) ?></span>
                            <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                            <span><?= esc(lang('UserRole.createdColon')) ?> <?= esc($createdAtText) ?></span>
                        </div>
                        <div class="mt-5 flex flex-col gap-5 lg:flex-row lg:items-start">
                            <div class="patient-avatar-badge <?= esc($userAvatarToneClass) ?>">
                                <i class="<?= esc($userAvatarIconClass) ?>"></i>
                            </div>
                            <div class="space-y-4 min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="truncate text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($userFullName) ?></h1>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold <?= esc($statusClass) ?>">
                                        <?= esc($statusText) ?>
                                    </span>
                                </div>
                                <div class="patient-hero-chip-grid">
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

                    <div class="w-full space-y-2 xl:w-[21rem]">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <?php if ($canEditUsers): ?>
                                <a href="<?= base_url('users/' . $viewedUser->id . '/edit') ?>" class="patient-header-action patient-header-action-primary">
                                    <i class="fas fa-pen"></i>
                                    <span><?= esc(lang('UserRole.editUser')) ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= base_url('users') ?>" class="patient-header-action patient-header-action-soft">
                                <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                                <span><?= esc(lang('UserRole.backToUsers')) ?></span>
                            </a>
                        </div>
                        <?php if ($canViewFinance): ?>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <a href="<?= base_url('finance/payouts') ?>" class="patient-header-action patient-header-action-emerald">
                                    <i class="fas fa-wallet"></i>
                                    <span><?= esc(lang('Finance.openPayoutLedger')) ?></span>
                                </a>
                                <a href="<?= base_url('finance') ?>" class="patient-header-action patient-header-action-gold">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span><?= esc(lang('Finance.finance')) ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <?php if ($canEditUsers): ?>
                                <a href="<?= base_url('users/' . $viewedUser->id . '/change-password') ?>" class="patient-hero-clinical-button">
                                    <i class="fas fa-key"></i>
                                    <span><?= esc(lang('UserRole.changePassword')) ?></span>
                                </a>
                                <button type="button" onclick="toggleStatus(<?= (int) $viewedUser->id ?>)" class="patient-hero-clinical-button">
                                    <i class="fas fa-toggle-on"></i>
                                    <span><?= esc(((int) ($viewedUser->active ?? 0) === 1 ? lang('UserRole.deactivate') : lang('UserRole.activate')) . ' ' . lang('UserRole.user')) ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($canDeleteUsers): ?>
                            <div class="grid gap-2 sm:grid-cols-1">
                                <button type="button" onclick="deleteUser(<?= (int) $viewedUser->id ?>)" class="patient-header-action patient-header-action-danger">
                                    <i class="fas fa-trash"></i>
                                    <span><?= esc(lang('UserRole.deleteUser')) ?></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.9fr)]">
                <section class="rounded-[24px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('UserRole.userInformation')) ?></h2>
                        <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UserRole.viewUserInformationPermissions')) ?></p>
                    </div>
                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('UserRole.contactInformation')) ?></p>
                                <div class="mt-4 space-y-3 text-sm text-slate-700">
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="text-slate-500"><?= esc(lang('UserRole.phone')) ?></span>
                                        <span class="font-semibold text-slate-900"><?= esc((string) ($viewedUser->phone ?? lang('UserRole.notProvided'))) ?></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="text-slate-500"><?= esc(lang('UserRole.email')) ?></span>
                                        <span class="font-semibold text-slate-900"><?= esc((string) ($viewedUser->email ?? lang('UserRole.na'))) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('UserRole.accountInformation')) ?></p>
                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-medium text-slate-500"><?= esc(lang('UserRole.userIdColon')) ?></p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">#<?= esc((string) ($viewedUser->id ?? '')) ?></p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-medium text-slate-500"><?= esc(lang('UserRole.lastIpColon')) ?></p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= esc((string) ($viewedUser->ip_address ?? lang('UserRole.na'))) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('UserRole.statusAndGroups')) ?></p>
                                <div class="mt-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 <?= esc($contentStatusClass) ?>">
                                        <?= esc($statusText) ?>
                                    </span>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <?php if (!empty($viewedUserRoles)): ?>
                                        <?php foreach ($viewedUserRoles as $role): ?>
                                            <?php
                                                $roleName = is_array($role)
                                                    ? ($role['name'] ?? lang('UserRole.unknownRole'))
                                                    : ($role->name ?? lang('UserRole.unknownRole'));
                                            ?>
                                            <span class="user-role-pill">
                                                <?= esc(ucfirst((string) $roleName)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="user-summary-badge">
                                            <?= esc(lang('UserRole.noRolesAssigned')) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('UserRole.activity')) ?></p>
                                <div class="mt-4 space-y-3 text-sm text-slate-700">
                                    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <span class="text-slate-500"><?= esc(lang('UserRole.createdColon')) ?></span>
                                        <span class="font-semibold text-slate-900"><?= esc($createdAtText) ?></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <span class="text-slate-500"><?= esc(lang('UserRole.lastLoginColon')) ?></span>
                                        <span class="font-semibold text-slate-900"><?= esc($lastLoginText) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <?php if ($canViewFinance): ?>
                    <aside class="space-y-6">
                        <section class="rounded-[24px] border border-indigo-200 bg-gradient-to-br from-indigo-50 via-white to-sky-50 shadow-sm">
                            <div class="border-b border-indigo-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('Finance.clinicalProviderCompensation')) ?></h2>
                                <p class="mt-1 text-sm text-slate-600"><?= esc(lang('Finance.clinicalProviderCompensationHelp')) ?></p>
                            </div>
                            <div class="space-y-4 p-6">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-indigo-100 bg-white px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.providerShareTotal')) ?></p>
                                        <p class="mt-2 text-xl font-bold text-slate-900"><?= formatCurrency($providerSummary['provider_share_total'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-100 bg-white px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.paidToProvider')) ?></p>
                                        <p class="mt-2 text-xl font-bold text-emerald-700"><?= formatCurrency($providerSummary['provider_paid_total'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-2xl border border-rose-100 bg-white px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.balance')) ?></p>
                                        <p class="mt-2 text-xl font-bold text-rose-700"><?= formatCurrency($providerSummary['provider_balance_total'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.providerPayoutCount')) ?></p>
                                        <p class="mt-2 text-xl font-bold text-slate-900"><?= esc((string) ($providerSummary['payout_count'] ?? 0)) ?></p>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                            <i class="fas fa-circle-info text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold"><?= esc(lang('Finance.providerSplit')) ?></p>
                                            <p class="mt-1 text-amber-800"><?= esc(lang('Finance.supplierPayablesGuidance')) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= base_url('finance/payouts') ?>" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">
                                        <i class="fas fa-wallet text-xs"></i>
                                        <span><?= esc(lang('Finance.openPayoutLedger')) ?></span>
                                    </a>
                                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                        <span><?= esc(lang('Finance.finance')) ?></span>
                                    </a>
                                </div>
                            </div>
                        </section>
                    </aside>
                <?php endif; ?>
            </div>

            <?php if ($canViewFinance): ?>
                <section class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('Finance.payoutLedger')) ?></h2>
                            <p class="mt-1 text-sm text-slate-500"><?= esc(lang('Finance.clinicalProviderCompensationHelp')) ?></p>
                        </div>
                        <div class="user-summary-badge">
                            <i class="fas fa-receipt text-[11px]"></i>
                            <span><?= esc((string) ($providerSummary['payout_count'] ?? 0)) ?> <?= esc(lang('Finance.providerPayoutCount')) ?></span>
                        </div>
                    </div>

                    <?php if (!empty($providerRows)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-600"><?= esc(lang('Finance.transaction')) ?></th>
                                        <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-600"><?= esc(lang('Finance.patient')) ?></th>
                                        <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-600"><?= esc(lang('Finance.businessDate')) ?></th>
                                        <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-600"><?= esc(lang('Finance.paidBalance')) ?></th>
                                        <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-600"><?= esc(lang('Finance.lastPaymentDate')) ?></th>
                                        <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-600"><?= esc(lang('Finance.status')) ?></th>
                                        <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-600"><?= esc(lang('Finance.actions')) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
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
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="px-6 py-4 align-top">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                                        <i class="<?= esc($transactionMeta['icon']) ?> <?= esc($transactionMeta['color']) ?> text-xl"></i>
                                                    </span>
                                                    <div class="space-y-1 min-w-0">
                                                        <div class="text-sm font-semibold text-slate-900 truncate"><?= esc((string) ($row['transaction_id'] ?? '-')) ?></div>
                                                        <div class="text-xs text-slate-500">#<?= esc((string) ($row['id'] ?? '')) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                                        <i class="<?= esc($patientAvatar['icon']) ?> <?= esc($patientAvatar['color']) ?> text-2xl"></i>
                                                    </span>
                                                    <div class="space-y-1 min-w-0">
                                                        <div class="text-sm font-semibold text-slate-900 truncate"><?= esc((string) (((string) ($row['patient_name'] ?? '')) !== '' ? $row['patient_name'] : lang('Finance.notAvailable'))) ?></div>
                                                        <div class="text-xs text-slate-500"><?= esc((string) ($row['patient_number'] ?? '')) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top text-sm text-slate-700">
                                                <?= !empty($row['business_date']) ? esc(formatDate((string) $row['business_date'])) : '&mdash;' ?>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="space-y-1 text-sm">
                                                    <div class="font-semibold text-indigo-700"><?= formatCurrency($row['provider_share_amount'] ?? 0) ?></div>
                                                    <div class="text-emerald-700"><?= esc(lang('Finance.paid')) ?>: <?= formatCurrency($row['provider_paid_amount'] ?? 0) ?></div>
                                                    <div class="text-rose-700"><?= esc(lang('Finance.balance')) ?>: <?= formatCurrency($row['provider_balance_due'] ?? 0) ?></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="space-y-1 text-sm text-slate-700">
                                                    <div><?= !empty($row['last_paid_date']) ? esc(formatDate((string) $row['last_paid_date'])) : esc(lang('UserRole.never')) ?></div>
                                                    <div class="text-xs text-slate-500"><?= esc((string) ($row['payment_count'] ?? 0)) ?> <?= esc(lang('Finance.paymentsCount')) ?></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <span class="<?= esc($rowStatusClass) ?>">
                                                    <?= esc(lang('Finance.' . $rowStatus)) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="flex items-center gap-2">
                                                    <a href="<?= base_url('finance/' . (int) ($row['finance_id'] ?? 0)) ?>" class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="<?= esc(lang('Finance.viewTransaction')) ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($canSettlePayout): ?>
                                                        <button type="button" onclick="openPayoutModal(<?= (int) $row['id'] ?>, <?= esc(number_format((float) ($row['provider_balance_due'] ?? 0), 2, '.', '')) ?>)" class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="<?= esc(lang('Finance.recordPaymentFromLedger')) ?>">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </button>
                                                        <button type="button" onclick="markPayoutPaid(<?= (int) $row['id'] ?>)" class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="<?= esc(lang('Finance.markFullyPaid')) ?>">
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
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <i class="fas fa-wallet text-xl"></i>
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-slate-900"><?= esc(lang('Finance.noClinicalProviderPayouts')) ?></h3>
                            <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-500"><?= esc(lang('Finance.noClinicalProviderPayoutsHelp')) ?></p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($canEditFinance): ?>
    <div id="recordPayoutModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-950/45" onclick="closePayoutModal()"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h4 class="text-base font-semibold text-slate-900"><?= esc(lang('Finance.recordProviderPayout')) ?></h4>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closePayoutModal()">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="recordPayoutForm" class="space-y-4 p-5">
                    <input type="hidden" id="payoutIdInput">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.amount')) ?></label>
                        <input type="number" step="0.01" min="0.01" id="payoutAmountInput" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        <p id="payoutAmountHelp" class="mt-1 text-xs text-slate-500"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.paymentMethod')) ?></label>
                        <select id="payoutMethodInput" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            <option value="cash"><?= esc(lang('Finance.cash')) ?></option>
                            <option value="card"><?= esc(lang('Finance.card')) ?></option>
                            <option value="bank_transfer"><?= esc(lang('Finance.bankTransfer')) ?></option>
                            <option value="check"><?= esc(lang('Finance.check')) ?></option>
                            <option value="other"><?= esc(lang('Finance.other')) ?></option>
                        </select>
                    </div>
                    <div id="payoutLiquidityAccountField" class="hidden">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.paidFromAccount')) ?></label>
                        <select id="payoutLiquidityAccountInput" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            <option value="bank"><?= esc(lang('Finance.liquidityAccount.bank')) ?></option>
                            <option value="undeposited_checks"><?= esc(lang('Finance.liquidityAccount.undeposited_checks')) ?></option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500"><?= esc(lang('Finance.checkOutgoingAccountHelp')) ?></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.paidDate')) ?></label>
                        <input type="date" id="payoutDateInput" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.notes')) ?></label>
                        <textarea id="payoutNotesInput" rows="2" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" placeholder="<?= esc(lang('Finance.optional')) ?>"></textarea>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" onclick="closePayoutModal()" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
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
    if (!confirm(<?= json_encode(lang('UserRole.confirmDeleteUser')) ?>)) {
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
        window.location.href = '<?= base_url('users') ?>';
    })
    .catch(error => {
        notify('error', <?= json_encode(lang('UserRole.error')) ?>, error.message || <?= json_encode(lang('UserRole.errorDeletingUser')) ?>);
    });
}

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

function openPayoutModal(id, maxAmount) {
    payoutModalMaxAmount = Number(maxAmount || 0);
    document.getElementById('payoutIdInput').value = String(id);
    document.getElementById('payoutAmountInput').value = payoutModalMaxAmount > 0 ? payoutModalMaxAmount.toFixed(2) : '';
    document.getElementById('payoutDateInput').value = new Date().toISOString().split('T')[0];
    document.getElementById('payoutAmountHelp').textContent = `<?= esc(lang('Finance.maximumPayable')) ?>: ${formatCurrencyValue(payoutModalMaxAmount)}`;
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
        notify('error', <?= json_encode(lang('Finance.invalidAmount')) ?>, `<?= esc(lang('Finance.amountValidationMessage')) ?> ${formatCurrencyValue(payoutModalMaxAmount)}.`);
        return;
    }

    submitPayoutPayment(id, amount, method, liquidityAccount, paidDate, notes);
});

document.getElementById('payoutMethodInput')?.addEventListener('change', syncPayoutLiquidityField);
<?php endif; ?>
</script>
<?= $this->endSection() ?>
