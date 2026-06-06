<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
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
    $providerPaymentRows = $provider_payment_rows ?? [];
    $recentProviderPaymentRows = array_slice($providerPaymentRows, 0, 4);
    $assignableRoles = $assignable_roles ?? [];
    $departments = $departments ?? [];
    $currentRoleId = (int) ($current_role_id ?? 0);
    $canEditUsers = has_permission('users', 'edit');
    $canDeleteUsers = has_permission('users', 'delete') && (int) ($viewedUser->id ?? 0) !== 1;
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

    [dir="rtl"] .patient-hero-chip {
        flex-direction: row-reverse;
    }

    [dir="rtl"] .patient-hero-chip-ltr {
        flex-direction: row;
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

    [dir="rtl"] .patient-header-action,
    [dir="rtl"] .patient-hero-clinical-button,
    [dir="rtl"] .patient-hero-tool-button {
        flex-direction: row-reverse;
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

    .user-inline-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
        min-height: 2.65rem;
        border-radius: 0.95rem;
        padding: 0.58rem 0.78rem;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        color: #eff6ff;
        font-size: 0.76rem;
        font-weight: 700;
    }

    [dir="rtl"] .user-inline-toggle {
        flex-direction: row-reverse;
    }

    .user-inline-toggle-track {
        position: relative;
        width: 2.75rem;
        height: 1.55rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.18);
        transition: background 0.2s ease;
        flex-shrink: 0;
    }

    .user-inline-toggle-track::after {
        content: "";
        position: absolute;
        top: 0.16rem;
        left: 0.18rem;
        width: 1.22rem;
        height: 1.22rem;
        border-radius: 9999px;
        background: #ffffff;
        box-shadow: 0 8px 16px -12px rgba(15, 23, 42, 0.75);
        transition: transform 0.2s ease;
    }

    [dir="rtl"] .user-inline-toggle-track::after {
        left: auto;
        right: 0.18rem;
    }

    .user-inline-toggle.is-active .user-inline-toggle-track {
        background: rgba(16, 185, 129, 0.45);
    }

    .user-inline-toggle.is-active .user-inline-toggle-track::after {
        transform: translateX(1.16rem);
    }

    [dir="rtl"] .user-inline-toggle.is-active .user-inline-toggle-track::after {
        transform: translateX(-1.16rem);
    }

    .user-inline-modal-backdrop {
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
    }

    .user-section-card {
        --user-card-bg: #f1f5ff;
        --user-card-accent: #4f46e5;
        --user-card-accent-soft: #eef2ff;
        --user-card-accent-ring: rgba(79, 70, 229, 0.16);
        --user-card-shadow: rgba(79, 70, 229, 0.18);
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.72);
        background-color: var(--user-card-bg);
        background: var(--user-card-bg);
        box-shadow:
            0 24px 50px -34px rgba(15, 23, 42, 0.42),
            0 14px 32px -26px var(--user-card-shadow),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .user-section-card-roles {
        --user-card-bg: #e0e7ff;
        --user-card-accent: #4f46e5;
        --user-card-accent-soft: #eef2ff;
        --user-card-accent-ring: rgba(79, 70, 229, 0.16);
        --user-card-shadow: rgba(79, 70, 229, 0.22);
    }

    .user-section-card-actions {
        --user-card-bg: #bae6fd;
        --user-card-accent: #0284c7;
        --user-card-accent-soft: #e0f2fe;
        --user-card-accent-ring: rgba(2, 132, 199, 0.16);
        --user-card-shadow: rgba(14, 165, 233, 0.22);
    }

    .user-section-card-finance {
        --user-card-bg: #dcfce7;
        --user-card-accent: #059669;
        --user-card-accent-soft: #dcfce7;
        --user-card-accent-ring: rgba(5, 150, 105, 0.16);
        --user-card-shadow: rgba(16, 185, 129, 0.22);
    }

    .user-section-card-ledger {
        --user-card-bg: #dbe4ee;
        --user-card-accent: #475569;
        --user-card-accent-soft: #e2e8f0;
        --user-card-accent-ring: rgba(71, 85, 105, 0.16);
        --user-card-shadow: rgba(51, 65, 85, 0.22);
    }

    .user-profile-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.5rem;
    }

    @media (min-width: 1280px) {
        .user-profile-grid {
            grid-template-columns: minmax(0, 7fr) minmax(20rem, 5fr);
        }
    }

    .user-section-header {
        padding: 1rem 1.5rem;
    }

    .user-section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #111827;
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.4;
    }

    [dir="rtl"] .user-section-title {
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .user-section-title-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: var(--user-card-accent);
        box-shadow: 0 0 0 4px var(--user-card-accent-ring), 0 8px 18px -10px var(--user-card-accent);
        flex-shrink: 0;
    }

    .user-section-subtitle {
        margin-top: 0.35rem;
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.55;
    }

    .user-section-body {
        padding: 1.25rem 1.5rem;
    }

    .user-info-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    @media (min-width: 640px) {
        .user-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .user-info-tile {
        position: relative;
        min-height: 5rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.7);
        background: rgba(255, 255, 255, 0.68);
        padding: 1rem;
        box-shadow:
            0 14px 28px -26px rgba(15, 23, 42, 0.32),
            inset 0 1px 0 rgba(255, 255, 255, 0.72);
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .user-info-tile:hover {
        border-color: rgba(255, 255, 255, 0.9);
        box-shadow:
            0 18px 34px -28px rgba(15, 23, 42, 0.38),
            inset 0 1px 0 rgba(255, 255, 255, 0.86);
        transform: translateY(-1px);
    }

    .user-finance-tile {
        border: none;
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.96), rgba(255, 255, 255, 0.98));
        box-shadow: 0 16px 30px -28px rgba(37, 99, 235, 0.32);
    }

    .user-finance-tile:hover {
        border: none;
        box-shadow: 0 20px 34px -28px rgba(37, 99, 235, 0.36);
    }

    .user-payment-summary-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(16rem, 0.8fr);
        gap: 1rem;
        margin-top: 1rem;
    }

    .user-payment-summary-card {
        border-radius: 1rem;
        border: 1px solid rgba(226, 232, 240, 0.95);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.98));
        padding: 1rem 1.1rem;
        box-shadow: 0 20px 38px -34px rgba(15, 23, 42, 0.35);
    }

    .user-payment-summary-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    [dir="rtl"] .user-payment-summary-head,
    [dir="rtl"] .user-payment-summary-title,
    [dir="rtl"] .user-payment-summary-item,
    [dir="rtl"] .user-payment-summary-main {
        flex-direction: row-reverse;
    }

    .user-payment-summary-title {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
    }

    .user-payment-summary-list {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .user-payment-summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        border-radius: 0.9rem;
        border: none;
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.92), rgba(255, 255, 255, 0.98));
        padding: 0.8rem 0.9rem;
        transition: box-shadow 0.18s ease, transform 0.18s ease, background 0.18s ease;
    }

    .user-payment-summary-item:hover {
        box-shadow: 0 16px 30px -28px rgba(79, 70, 229, 0.4);
        transform: translateY(-1px);
    }

    .user-payment-summary-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .user-payment-summary-copy {
        min-width: 0;
    }

    .user-payment-summary-copy strong,
    .user-payment-summary-copy span {
        display: block;
    }

    .user-payment-summary-copy strong {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
    }

    .user-payment-summary-copy span {
        margin-top: 0.18rem;
        font-size: 0.75rem;
        color: #64748b;
    }

    .user-payment-summary-meta {
        flex-shrink: 0;
        text-align: end;
    }

    [dir="rtl"] .user-payment-summary-meta {
        text-align: start;
    }

    .user-payment-summary-amount {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #166534;
    }

    .user-payment-summary-date {
        display: block;
        margin-top: 0.18rem;
        font-size: 0.74rem;
        color: #64748b;
    }

    .user-payment-summary-empty {
        display: flex;
        min-height: 100%;
        align-items: center;
        justify-content: center;
        border-radius: 0.9rem;
        border: 1px dashed rgba(203, 213, 225, 0.95);
        background: rgba(248, 250, 252, 0.92);
        padding: 1.35rem 1rem;
        text-align: center;
        font-size: 0.8rem;
        color: #64748b;
    }

    @media (max-width: 1023px) {
        .user-payment-summary-grid {
            grid-template-columns: 1fr;
        }
    }

    .user-info-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        line-height: 1rem;
        text-transform: uppercase;
    }

    [dir="rtl"] .user-info-label {
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .user-info-value {
        margin-top: 0.45rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .user-info-help {
        margin-top: 0.25rem;
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .user-role-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    [dir="rtl"] .user-role-list {
        justify-content: flex-end;
    }

    .user-action-list {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    .user-action-row {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.74);
        background: rgba(255, 255, 255, 0.68);
        padding: 0.9rem 1rem;
        box-shadow:
            0 14px 28px -26px rgba(15, 23, 42, 0.32),
            inset 0 1px 0 rgba(255, 255, 255, 0.72);
        text-align: start;
        transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    [dir="rtl"] .user-action-row {
        direction: rtl;
        flex-direction: row;
    }

    .user-action-row:hover {
        border-color: rgba(255, 255, 255, 0.92);
        background: rgba(255, 255, 255, 0.82);
        box-shadow:
            0 18px 34px -28px rgba(15, 23, 42, 0.38),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
        transform: translateY(-1px);
    }

    .user-action-row-main {
        min-width: 0;
        flex: 1 1 auto;
        text-align: start;
    }

    [dir="rtl"] .user-action-row-main {
        text-align: right;
    }

    .user-action-row-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1.35;
    }

    [dir="rtl"] .user-action-row-title {
        direction: rtl;
        flex-direction: row;
        justify-content: flex-start;
    }

    [dir="rtl"] .user-action-row-arrow {
        transform: rotate(180deg);
    }

    .user-action-row-modal {
        background: rgba(255, 255, 255, 0.86);
    }

    .user-action-row-modal:hover {
        background: rgba(239, 246, 255, 0.94);
    }

    .user-action-row-copy {
        margin-top: 0.25rem;
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .user-action-row-icon {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.65rem;
        background: var(--user-card-accent-soft);
        color: var(--user-card-accent);
        box-shadow: inset 0 0 0 1px var(--user-card-accent-ring);
        flex-shrink: 0;
    }

    .user-action-row-modal .user-action-row-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .user-modal-select + .select2-container .select2-selection--single {
        min-height: 3rem;
        border: 2px solid #e5e7eb !important;
        border-radius: 0.75rem !important;
        background: rgba(255, 255, 255, 0.8) !important;
        box-shadow: 0 10px 18px -16px rgba(15, 23, 42, 0.45) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .user-modal-select + .select2-container .select2-selection--single:focus,
    .user-modal-select + .select2-container.select2-container--focus .select2-selection--single,
    .user-modal-select + .select2-container.select2-container--open .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18) !important;
    }

    .user-modal-select + .select2-container .select2-selection__rendered {
        padding: 0.72rem 2.75rem 0.72rem 1rem !important;
        color: #0f172a !important;
        font-size: 0.875rem !important;
        line-height: 1.4rem !important;
    }

    [dir="rtl"] .user-modal-select + .select2-container .select2-selection__rendered {
        padding: 0.72rem 1rem 0.72rem 2.75rem !important;
        text-align: right;
    }

    .user-modal-select + .select2-container .select2-selection__arrow {
        top: 0.55rem !important;
        right: 0.65rem !important;
    }

    [dir="rtl"] .user-modal-select + .select2-container .select2-selection__arrow {
        left: 0.65rem !important;
        right: auto !important;
    }

    .user-record-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
        direction: inherit;
    }

    .user-record-table thead th {
        padding: 0.78rem 0.75rem;
        font-size: 0.7rem;
        line-height: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 700;
        color: #64748b;
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        border-bottom: 1px solid #e2e8f0;
        text-align: start !important;
        white-space: nowrap;
    }

    .user-record-table tbody td {
        padding: 0.65rem 0.75rem;
        font-size: 0.86rem;
        line-height: 1.3rem;
        color: #0f172a;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        background: #ffffff;
        text-align: start;
        white-space: nowrap;
    }

    .user-record-table tbody tr:last-child td {
        border-bottom: 0;
    }

    [dir="rtl"] .user-record-table th,
    [dir="rtl"] .user-record-table td {
        text-align: right !important;
    }

    .user-record-cell {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 100%;
        direction: inherit;
    }

    .user-record-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.375rem;
        direction: inherit;
    }

    [dir="rtl"] .user-record-actions {
        justify-content: flex-start;
    }

    .user-record-row {
        cursor: pointer;
        transition: background-color 0.18s ease;
    }

    .user-record-row:hover td {
        background: #f8fbff;
    }

    .user-record-row:focus-visible td {
        background: #eef2ff;
        outline: 2px solid #6366f1;
        outline-offset: -2px;
    }

    .user-record-main {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        min-width: 0;
        white-space: nowrap;
    }

    [dir="rtl"] .user-record-main {
        direction: rtl;
        flex-direction: row-reverse;
    }

    .user-record-title {
        color: #0f172a;
        font-weight: 600;
        min-width: 0;
    }

    .user-record-subtitle {
        color: #64748b;
        font-size: 0.78rem;
        line-height: 1rem;
        min-width: 0;
    }

    /* User detail visual contract: mirrors the treatment entity-detail hierarchy. */
    .user-page-shell {
        background:
            radial-gradient(900px 520px at 8% -12%, rgba(37, 99, 235, 0.09), transparent 60%),
            radial-gradient(760px 460px at 92% 0%, rgba(124, 58, 237, 0.08), transparent 55%),
            #f8fafc;
    }

    .user-snapshot-section,
    .user-workspace-section {
        position: relative;
        overflow: hidden;
        border-radius: 1.6rem;
        border: 1px solid rgba(255, 255, 255, 0.76);
        background:
            radial-gradient(circle at 0% 0%, rgba(37, 99, 235, 0.11), transparent 28%),
            radial-gradient(circle at 100% 18%, rgba(124, 58, 237, 0.12), transparent 30%),
            linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.96));
        box-shadow: 0 24px 58px -42px rgba(15, 23, 42, 0.42), 0 1px 0 rgba(255, 255, 255, 0.92) inset;
    }

    .user-snapshot-section::before,
    .user-workspace-section::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 9% 0%, rgba(37, 99, 235, 0.14), transparent 29%),
            radial-gradient(circle at 95% 10%, rgba(5, 150, 105, 0.12), transparent 30%);
    }

    .user-snapshot-section > *,
    .user-workspace-section > * {
        position: relative;
        z-index: 1;
    }

    .user-snapshot-heading {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .user-snapshot-eyebrow {
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .user-snapshot-title {
        color: #0f172a;
        font-size: 1.18rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .user-snapshot-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.9rem;
    }

    @media (min-width: 640px) {
        .user-snapshot-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1180px) {
        .user-snapshot-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .user-snapshot-card {
        position: relative;
        overflow: hidden;
        min-height: 7.2rem;
        border-radius: 1.2rem;
        padding: 1rem;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -28px rgba(15,23,42,0.55);
    }

    .user-snapshot-card::after {
        content: "";
        position: absolute;
        inset-block-start: -42%;
        inset-inline-end: -10%;
        width: 9.5rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
    }

    .user-snapshot-card > * {
        position: relative;
        z-index: 1;
    }

    .user-snapshot-card:nth-child(1) {
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a);
    }

    .user-snapshot-card:nth-child(2) {
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
            linear-gradient(135deg, #7c3aed, #6d28d9 56%, #4c1d95);
    }

    .user-snapshot-card:nth-child(3) {
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #047857, #0f766e 56%, #064e3b);
    }

    .user-snapshot-card:nth-child(4) {
        background:
            radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
            linear-gradient(135deg, #d97706, #b45309 58%, #92400e);
    }

    .user-snapshot-label {
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(239,246,255,0.88);
    }

    .user-snapshot-value {
        margin-top: 0.5rem;
        font-size: 1.1rem;
        line-height: 1.25;
        font-weight: 900;
        color: #fff;
    }

    .user-snapshot-meta {
        margin-top: 0.35rem;
        font-size: 0.76rem;
        line-height: 1.25;
        color: rgba(239,246,255,0.84);
    }

    .user-workspace-section {
        padding: 1rem;
    }

    @media (min-width: 640px) {
        .user-workspace-section {
            padding: 1.25rem;
        }
    }

    .user-section-card {
        border-radius: 1.2rem;
        border-color: rgba(255,255,255,0.82);
        background:
            radial-gradient(circle at 8% 0%, color-mix(in srgb, var(--user-card-accent) 15%, transparent), transparent 31%),
            radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.98), var(--user-card-accent-soft) 46%, rgba(255,255,255,0.98));
        box-shadow: 0 20px 48px -38px rgba(15,23,42,0.48), 0 1px 0 rgba(255,255,255,0.92) inset;
    }

    .user-section-card-roles {
        --user-card-bg: #eef2ff;
        --user-card-accent: #4f46e5;
        --user-card-accent-soft: rgba(238, 242, 255, 0.92);
    }

    .user-section-card-actions {
        --user-card-bg: #e0f2fe;
        --user-card-accent: #0284c7;
        --user-card-accent-soft: rgba(224, 242, 254, 0.92);
    }

    .user-section-card-finance {
        --user-card-bg: #dcfce7;
        --user-card-accent: #047857;
        --user-card-accent-soft: rgba(209, 250, 229, 0.92);
    }

    .user-section-card-ledger {
        --user-card-bg: #e2e8f0;
        --user-card-accent: #334155;
        --user-card-accent-soft: rgba(226, 232, 240, 0.92);
    }

    .user-section-header {
        border-bottom: 1px solid rgba(203, 213, 225, 0.72);
        background: linear-gradient(90deg, rgba(255,255,255,0.72), color-mix(in srgb, var(--user-card-accent) 12%, white));
    }

    .user-section-title-dot {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, var(--user-card-accent), color-mix(in srgb, var(--user-card-accent) 78%, #0f172a));
        box-shadow: 0 16px 26px -20px rgba(15,23,42,0.78);
    }

    .user-section-title-dot::before {
        content: "";
        display: block;
        width: 0.62rem;
        height: 0.62rem;
        margin: 0.82rem auto;
        border-radius: 999px;
        background: rgba(255,255,255,0.9);
    }

    .user-info-tile,
    .user-payment-summary-card,
    .user-payment-summary-item {
        border-color: rgba(203, 213, 225, 0.82);
        background: rgba(255,255,255,0.82);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .user-action-row-modal:first-child {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        box-shadow: 0 18px 34px -28px rgba(37,99,235,0.72);
    }

    .user-action-row-modal:first-child .user-action-row-title,
    .user-action-row-modal:first-child .user-action-row-copy,
    .user-action-row-modal:first-child .user-action-row-icon,
    .user-action-row-modal:first-child i {
        color: #fff !important;
    }

    /* Lower user detail sections: provider finance and ledger should carry stronger hierarchy. */
    .user-section-card-finance .user-section-header {
        background:
            radial-gradient(circle at 8% 0%, rgba(255,255,255,0.42), transparent 30%),
            linear-gradient(135deg, rgba(236,253,245,0.98), rgba(219,234,254,0.98));
    }

    .user-section-card-finance .user-section-header a:first-child {
        border-color: rgba(5,150,105,0.28) !important;
        background: linear-gradient(135deg, #059669, #0f766e) !important;
        color: #fff !important;
        box-shadow: 0 16px 30px -24px rgba(5,150,105,0.7);
    }

    .user-section-card-finance .user-section-header a:first-child i {
        color: #fff !important;
    }

    .user-section-card-finance .user-section-header a:last-child {
        border-color: rgba(37,99,235,0.24) !important;
        background: rgba(255,255,255,0.86) !important;
        color: #1d4ed8 !important;
    }

    .user-section-card-finance .user-section-body > .grid:first-child {
        gap: 1rem;
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile {
        min-height: 7.35rem;
        overflow: hidden;
        border: 0;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 20px 36px -30px rgba(15,23,42,0.66);
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile::after {
        content: "";
        position: absolute;
        inset-block-start: -42%;
        inset-inline-end: -12%;
        width: 9rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile:nth-child(1) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #4f46e5, #4338ca 55%, #312e81) !important;
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile:nth-child(2) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #059669, #047857 55%, #064e3b) !important;
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile:nth-child(3) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #e11d48, #be123c 55%, #881337) !important;
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile:nth-child(4) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #334155, #1e293b 55%, #0f172a) !important;
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile .user-info-label,
    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile .user-info-label i {
        color: rgba(239,246,255,0.9) !important;
    }

    .user-section-card-finance .user-section-body > .grid:first-child > .user-finance-tile .user-info-value {
        color: #fff !important;
        font-size: 1.22rem;
        font-weight: 900;
    }

    .user-payment-summary-grid {
        gap: 1.15rem;
        align-items: stretch;
    }

    .user-payment-summary-card {
        position: relative;
        overflow: hidden;
        border-color: rgba(255,255,255,0.86) !important;
        background:
            radial-gradient(circle at 6% 0%, rgba(37,99,235,0.12), transparent 31%),
            radial-gradient(circle at 100% 0%, rgba(5,150,105,0.12), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(239,246,255,0.94)) !important;
        box-shadow: 0 22px 46px -36px rgba(15,23,42,0.48), inset 0 1px 0 rgba(255,255,255,0.9) !important;
    }

    .user-payment-summary-card:nth-child(2) {
        background:
            radial-gradient(circle at 6% 0%, rgba(5,150,105,0.14), transparent 31%),
            radial-gradient(circle at 100% 0%, rgba(79,70,229,0.12), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(236,253,245,0.94)) !important;
    }

    .user-payment-summary-title {
        font-size: 0.95rem;
        font-weight: 900;
    }

    .user-payment-summary-title i {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #059669, #047857);
        color: #fff !important;
        box-shadow: 0 14px 24px -18px rgba(5,150,105,0.72);
    }

    .user-payment-summary-card:nth-child(2) .user-payment-summary-title i {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        box-shadow: 0 14px 24px -18px rgba(79,70,229,0.72);
    }

    .user-payment-summary-item {
        border: 1px solid rgba(226,232,240,0.88) !important;
        border-inline-start: 4px solid #059669 !important;
        background: rgba(255,255,255,0.86) !important;
        box-shadow: 0 16px 32px -30px rgba(15,23,42,0.42), inset 0 1px 0 rgba(255,255,255,0.9) !important;
    }

    .user-payment-summary-amount {
        color: #047857;
        font-size: 0.9rem;
        font-weight: 900;
    }

    .user-section-card-ledger .user-section-header {
        background:
            radial-gradient(circle at 8% 0%, rgba(255,255,255,0.26), transparent 28%),
            linear-gradient(135deg, #1e293b, #334155 56%, #0f172a) !important;
    }

    .user-section-card-ledger .user-section-title,
    .user-section-card-ledger .user-section-subtitle {
        color: #fff !important;
    }

    .user-section-card-ledger .user-section-subtitle {
        color: rgba(226,232,240,0.86) !important;
    }

    .user-section-card-ledger .user-section-title-dot {
        background: linear-gradient(135deg, #60a5fa, #22c55e) !important;
        box-shadow: 0 16px 26px -20px rgba(96,165,250,0.88);
    }

    .user-section-card-ledger .user-summary-badge {
        background: rgba(255,255,255,0.14);
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.16);
    }

    .user-section-card-ledger .overflow-x-auto {
        margin: 1.1rem;
        overflow: hidden;
        border-radius: 1.1rem;
        border: 1px solid rgba(203,213,225,0.74);
        box-shadow: 0 18px 36px -32px rgba(15,23,42,0.5);
    }

    .user-section-card-ledger .user-record-table thead th {
        color: rgba(239,246,255,0.92) !important;
        background: linear-gradient(180deg, #1e293b 0%, #334155 100%) !important;
        border-bottom-color: rgba(148,163,184,0.36) !important;
    }

    .user-section-card-ledger .user-record-table thead th i {
        color: #93c5fd !important;
    }

    .user-section-card-ledger .user-record-table tbody td {
        background: rgba(255,255,255,0.96);
    }

    .user-section-card-ledger .user-record-table tbody tr:nth-child(even) td {
        background: rgba(248,250,252,0.98);
    }

    .user-section-card-ledger .user-record-row:hover td {
        background: #eef6ff !important;
    }

    .user-section-card-ledger .user-record-actions a,
    .user-section-card-ledger .user-record-actions button {
        border-radius: 0.75rem;
        background: rgba(255,255,255,0.74);
        box-shadow: inset 0 0 0 1px rgba(226,232,240,0.88);
    }

    /* Consistency pass: remove old pale-card spirit from roles and provider pay panels. */
    .user-section-card-roles .user-info-tile {
        overflow: hidden;
        border: 0 !important;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -30px rgba(15,23,42,0.58) !important;
    }

    .user-section-card-roles .user-info-tile::after {
        content: "";
        position: absolute;
        inset-block-start: -48%;
        inset-inline-end: -12%;
        width: 8.5rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
    }

    .user-section-card-roles .user-info-tile > * {
        position: relative;
        z-index: 1;
    }

    .user-section-card-roles .user-info-tile:nth-child(1) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #4f46e5, #4338ca 55%, #312e81) !important;
    }

    .user-section-card-roles .user-info-tile:nth-child(2) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #2563eb, #1d4ed8 55%, #1e3a8a) !important;
    }

    .user-section-card-roles .user-info-tile:nth-child(3) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #0284c7, #0369a1 55%, #0c4a6e) !important;
    }

    .user-section-card-roles .user-info-tile:nth-child(4) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #059669, #047857 55%, #064e3b) !important;
    }

    .user-section-card-roles .user-info-label,
    .user-section-card-roles .user-info-label i,
    .user-section-card-roles .user-info-help {
        color: rgba(239,246,255,0.88) !important;
    }

    .user-section-card-roles .user-info-value {
        color: #fff !important;
        font-weight: 900;
    }

    .user-section-card-roles .user-role-pill,
    .user-section-card-roles .user-summary-badge {
        background: rgba(255,255,255,0.16) !important;
        color: #fff !important;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
    }

    .user-payment-summary-card .user-info-tile.user-finance-tile {
        overflow: hidden;
        min-height: 6.2rem;
        border: 0 !important;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -30px rgba(15,23,42,0.58) !important;
    }

    .user-payment-summary-card .user-info-tile.user-finance-tile::after {
        content: "";
        position: absolute;
        inset-block-start: -48%;
        inset-inline-end: -12%;
        width: 8rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
    }

    .user-payment-summary-card .user-info-tile.user-finance-tile:nth-child(1) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #059669, #047857 55%, #064e3b) !important;
    }

    .user-payment-summary-card .user-info-tile.user-finance-tile:nth-child(2) {
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%),
            linear-gradient(135deg, #e11d48, #be123c 55%, #881337) !important;
    }

    .user-payment-summary-card .user-info-tile.user-finance-tile > * {
        position: relative;
        z-index: 1;
    }

    .user-payment-summary-card .user-info-tile.user-finance-tile .user-info-label,
    .user-payment-summary-card .user-info-tile.user-finance-tile .user-info-label i {
        color: rgba(239,246,255,0.88) !important;
    }

    .user-payment-summary-card .user-info-tile.user-finance-tile .user-info-value {
        color: #fff !important;
        font-weight: 900;
    }

    .user-payment-summary-card:first-child {
        border-block-start: 5px solid #059669 !important;
    }

    .user-payment-summary-card:nth-child(2) {
        border-block-start: 5px solid #4f46e5 !important;
    }

    .user-payment-summary-empty {
        border: 0 !important;
        background:
            radial-gradient(circle at 10% 0%, rgba(255,255,255,0.20), transparent 34%),
            linear-gradient(135deg, #64748b, #334155 58%, #0f172a) !important;
        color: rgba(255,255,255,0.9) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 16px 30px -28px rgba(15,23,42,0.58);
    }
</style>

<div class="user-page-shell min-h-screen bg-slate-50/80" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
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
                                <button type="button" onclick="openInlineModal('editUserModal')" class="patient-header-action patient-header-action-primary">
                                    <i class="fas fa-pen"></i>
                                    <span><?= esc(lang('UserRole.editUser')) ?></span>
                                </button>
                            <?php endif; ?>
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft">
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
                                <button type="button" onclick="openInlineModal('passwordModal')" class="patient-hero-clinical-button">
                                    <i class="fas fa-key"></i>
                                    <span><?= esc(lang('UserRole.changePassword')) ?></span>
                                </button>
                                <button type="button" onclick="toggleStatus(<?= (int) $viewedUser->id ?>)" class="user-inline-toggle <?= (int) ($viewedUser->active ?? 0) === 1 ? 'is-active' : '' ?>">
                                    <span class="user-inline-toggle-track" aria-hidden="true"></span>
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

            <section class="user-snapshot-section p-4 sm:p-5">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="user-snapshot-heading">
                        <p class="user-snapshot-eyebrow"><?= esc(lang('UserRole.userDetails')) ?></p>
                        <h2 class="user-snapshot-title"><?= esc(lang('UserRole.statusAndGroups')) ?></h2>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-xl border border-white/80 bg-white/80 px-3 py-2 text-sm font-bold text-slate-700 shadow-sm">
                        <i class="fas fa-user-shield text-indigo-600"></i>
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
                        <p class="user-snapshot-value" dir="ltr"><?= formatCurrency($providerSummary['provider_share_total'] ?? 0) ?></p>
                        <p class="user-snapshot-meta"><?= esc(lang('Finance.paidToProvider')) ?>: <span dir="ltr"><?= formatCurrency($providerSummary['provider_paid_total'] ?? 0) ?></span></p>
                    </div>
                    <div class="user-snapshot-card">
                        <p class="user-snapshot-label"><?= esc(lang('Finance.balance')) ?></p>
                        <p class="user-snapshot-value" dir="ltr"><?= formatCurrency($providerSummary['provider_balance_total'] ?? 0) ?></p>
                        <p class="user-snapshot-meta"><?= esc(lang('Finance.providerPayoutCount')) ?>: <?= esc((string) ($providerSummary['payout_count'] ?? 0)) ?></p>
                    </div>
                </div>
            </section>

            <div class="user-workspace-section space-y-6">
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
                                <p class="user-info-label"><i class="fas fa-user-shield text-indigo-500"></i><?= esc(lang('UserRole.roleAssignment')) ?></p>
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
                                <p class="user-info-label"><i class="fas fa-layer-group text-indigo-500"></i><?= esc(lang('UserRole.roles')) ?></p>
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
                                <button type="button" onclick="openInlineModal('editUserModal')" class="user-action-row user-action-row-modal">
                                    <span class="user-action-row-main">
                                        <span class="user-action-row-title"><i class="fas fa-pen text-sky-500"></i><?= esc(lang('UserRole.editUser')) ?></span>
                                        <span class="user-action-row-copy"><?= esc(lang('UserRole.updateUserInformationDescription')) ?></span>
                                    </span>
                                    <span class="user-action-row-icon"><i class="fas fa-window-restore"></i></span>
                                </button>
                                <button type="button" onclick="openInlineModal('roleModal')" class="user-action-row user-action-row-modal">
                                    <span class="user-action-row-main">
                                        <span class="user-action-row-title"><i class="fas fa-user-tag text-indigo-500"></i><?= esc(lang('UserRole.roleAssignment')) ?></span>
                                        <span class="user-action-row-copy"><?= esc(lang('UserRole.chooseRole')) ?></span>
                                    </span>
                                    <span class="user-action-row-icon"><i class="fas fa-window-restore"></i></span>
                                </button>
                                <button type="button" onclick="openInlineModal('passwordModal')" class="user-action-row user-action-row-modal">
                                    <span class="user-action-row-main">
                                        <span class="user-action-row-title"><i class="fas fa-key text-amber-500"></i><?= esc(lang('UserRole.accountSecurity')) ?></span>
                                        <span class="user-action-row-copy"><?= esc(lang('UserRole.changePassword')) ?></span>
                                    </span>
                                    <span class="user-action-row-icon"><i class="fas fa-window-restore"></i></span>
                                </button>
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
                                <a href="<?= base_url('finance/payouts') ?>" class="inline-flex items-center justify-center gap-2 rounded-lg border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">
                                    <i class="fas fa-wallet text-xs"></i>
                                    <span><?= esc(lang('Finance.openPayoutLedger')) ?></span>
                                </a>
                                <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                    <span><?= esc(lang('Finance.finance')) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="user-section-body">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-percent text-indigo-500"></i><?= esc(lang('Finance.providerShareTotal')) ?></p>
                                <p class="user-info-value"><?= formatCurrency($providerSummary['provider_share_total'] ?? 0) ?></p>
                            </div>
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-money-bill-wave text-emerald-500"></i><?= esc(lang('Finance.paidToProvider')) ?></p>
                                <p class="user-info-value"><?= formatCurrency($providerSummary['provider_paid_total'] ?? 0) ?></p>
                            </div>
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-triangle-exclamation text-rose-500"></i><?= esc(lang('Finance.balance')) ?></p>
                                <p class="user-info-value"><?= formatCurrency($providerSummary['provider_balance_total'] ?? 0) ?></p>
                            </div>
                            <div class="user-info-tile user-finance-tile">
                                <p class="user-info-label"><i class="fas fa-receipt text-slate-500"></i><?= esc(lang('Finance.providerPayoutCount')) ?></p>
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
                                                    <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-slate-50">
                                                        <i class="<?= esc($patientAvatar['icon']) ?> <?= esc($patientAvatar['color']) ?> text-base"></i>
                                                    </span>
                                                    <span class="user-payment-summary-copy">
                                                        <strong><?= esc((string) (((string) ($row['patient_name'] ?? '')) !== '' ? $row['patient_name'] : lang('Finance.notAvailable'))) ?></strong>
                                                        <span>
                                                            <?= esc((string) ($row['transaction_id'] ?? '-')) ?>
                                                            <span class="text-slate-300"><?= esc('•') ?></span>
                                                            <?= esc(lang('Finance.' . ((string) ($row['payment_method'] ?? 'cash')))) ?>
                                                        </span>
                                                    </span>
                                                </span>
                                                <span class="user-payment-summary-meta">
                                                    <span class="user-payment-summary-amount"><?= formatCurrency($row['amount'] ?? 0) ?></span>
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
                                        <i class="fas fa-chart-line text-indigo-500 text-xs"></i>
                                        <span><?= esc(lang('Finance.clinicalProviderCompensation')) ?></span>
                                    </span>
                                </div>
                                <div class="space-y-3">
                                    <div class="user-info-tile user-finance-tile">
                                        <p class="user-info-label"><i class="fas fa-money-bill-wave text-emerald-500"></i><?= esc(lang('Finance.paidToProvider')) ?></p>
                                        <p class="user-info-value"><?= formatCurrency($providerSummary['provider_paid_total'] ?? 0) ?></p>
                                    </div>
                                    <div class="user-info-tile user-finance-tile">
                                        <p class="user-info-label"><i class="fas fa-triangle-exclamation text-rose-500"></i><?= esc(lang('Finance.balance')) ?></p>
                                        <p class="user-info-value"><?= formatCurrency($providerSummary['provider_balance_total'] ?? 0) ?></p>
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
                                        <th><i class="fas fa-receipt text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.transaction')) ?></th>
                                        <th><i class="fas fa-user text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.patient')) ?></th>
                                        <th><i class="fas fa-calendar text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.businessDate')) ?></th>
                                        <th><i class="fas fa-wallet text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.paidBalance')) ?></th>
                                        <th><i class="fas fa-money-check-dollar text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.lastPaymentDate')) ?></th>
                                        <th><i class="fas fa-cogs text-indigo-600 <?= $isRtl ? 'ml-2' : 'mr-2' ?>"></i><?= esc(lang('Finance.actions')) ?></th>
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
                                                    <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-slate-50">
                                                        <i class="<?= esc($transactionMeta['icon']) ?> <?= esc($transactionMeta['color']) ?> text-sm"></i>
                                                    </span>
                                                    <div class="user-record-main">
                                                        <span class="user-record-title"><?= esc((string) ($row['transaction_id'] ?? '-')) ?></span>
                                                        <?php if ((int) ($row['clinical_line_count'] ?? 0) > 0): ?>
                                                            <span class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700">
                                                                    <?= esc(lang('Finance.clinicalTraceLines')) ?>: <?= esc((string) ($row['clinical_line_count'] ?? 0)) ?>
                                                                </span>
                                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-blue-700">
                                                                    <?= esc(lang('Finance.clinicalTraceTreatments')) ?>: <?= esc((string) ($row['treatment_count'] ?? 0)) ?>
                                                                </span>
                                                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700">
                                                                    <?= esc(lang('Finance.clinicalTraceExaminations')) ?>: <?= esc((string) ($row['examination_count'] ?? 0)) ?>
                                                                </span>
                                                            </span>
                                                            <?php if (trim((string) ($row['clinical_procedures'] ?? '')) !== ''): ?>
                                                                <span class="mt-1 block max-w-xs truncate text-xs font-medium text-slate-500" title="<?= esc((string) $row['clinical_procedures'], 'attr') ?>">
                                                                    <?= esc((string) $row['clinical_procedures']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-record-cell">
                                                    <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-slate-50">
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
                                                    <span class="font-semibold text-indigo-700"><?= formatCurrency($row['provider_share_amount'] ?? 0) ?></span>
                                                    <span class="text-emerald-700"><?= esc(lang('Finance.paid')) ?> <?= formatCurrency($row['provider_paid_amount'] ?? 0) ?></span>
                                                    <span class="text-rose-700"><?= esc(lang('Finance.balance')) ?> <?= formatCurrency($row['provider_balance_due'] ?? 0) ?></span>
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
                                                    <a href="<?= esc(workflow_context_url('finance/' . (int) ($row['finance_id'] ?? 0), 'users/' . (int) ($viewedUser->id ?? 0)), 'attr') ?>" class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" data-row-action="true" title="<?= esc(lang('Finance.viewTransaction')) ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($canSettlePayout): ?>
                                                        <button type="button" data-row-action="true" onclick="openPayoutModal(<?= (int) $row['id'] ?>, <?= esc(number_format((float) ($row['provider_balance_due'] ?? 0), 2, '.', '')) ?>)" class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="<?= esc(lang('Finance.recordPaymentFromLedger')) ?>">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </button>
                                                        <button type="button" data-row-action="true" onclick="markPayoutPaid(<?= (int) $row['id'] ?>)" class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="<?= esc(lang('Finance.markFullyPaid')) ?>">
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

<?php if ($canEditUsers): ?>
    <div id="editUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="user-inline-modal-backdrop fixed inset-0"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeInlineModal('editUserModal')">
            <div class="w-full max-w-3xl rounded-3xl border border-slate-200 bg-white shadow-2xl" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900"><?= esc(lang('UserRole.editUser')) ?></h4>
                        <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UserRole.updateUserInformationDescription')) ?></p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeInlineModal('editUserModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="inlineEditUserForm" class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="inlineFirstNameInput" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.firstName')) ?></label>
                            <input type="text" id="inlineFirstNameInput" name="first_name" value="<?= esc((string) ($viewedUser->first_name ?? '')) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        </div>
                        <div>
                            <label for="inlineLastNameInput" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.lastName')) ?></label>
                            <input type="text" id="inlineLastNameInput" name="last_name" value="<?= esc((string) ($viewedUser->last_name ?? '')) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="inlineEmailInput" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.email')) ?></label>
                            <input type="email" id="inlineEmailInput" name="email" value="<?= esc((string) ($viewedUser->email ?? '')) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        </div>
                        <div>
                            <label for="inlinePhoneInput" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.phone')) ?></label>
                            <input type="text" id="inlinePhoneInput" name="phone" value="<?= esc((string) ($viewedUser->phone ?? '')) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="inlineDepartmentSelect" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.department')) ?></label>
                            <select id="inlineDepartmentSelect" name="department" class="user-modal-select w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" data-searchable-select data-minimum-input-length="0" data-placeholder="<?= esc(lang('UserRole.selectDepartment')) ?>" data-allow-clear="true">
                                <option value=""><?= esc(lang('UserRole.selectDepartment')) ?></option>
                                <?php foreach ($departments as $departmentKey => $departmentName): ?>
                                    <option value="<?= esc($departmentKey) ?>" <?= (($viewedUser->department ?? '') === $departmentKey) ? 'selected' : '' ?>><?= esc($departmentName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="inlineHireDateInput" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(preg_replace('/\s+(Required|مطلوب)$/u', '', (string) lang('UserRole.hireDateRequired'))) ?></label>
                            <input type="text" id="inlineHireDateInput" name="hire_date" data-clinic-date="1" value="<?= esc((string) ($viewedUser->hire_date ?? '')) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" onclick="closeInlineModal('editUserModal')" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('UserRole.cancel')) ?></span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            <i class="fas fa-save text-xs"></i>
                            <span><?= esc(lang('UserRole.updateUser')) ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="roleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="user-inline-modal-backdrop fixed inset-0"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeInlineModal('roleModal')">
            <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white shadow-2xl" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900"><?= esc(lang('UserRole.roleAssignment')) ?></h4>
                        <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UserRole.chooseRole')) ?></p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeInlineModal('roleModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="inlineRoleForm" class="space-y-5 p-6">
                    <div>
                        <label for="inlineRoleSelect" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.role')) ?></label>
                        <select id="inlineRoleSelect" name="role_id" class="user-modal-select w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" data-searchable-select data-minimum-input-length="0" data-placeholder="<?= esc(lang('UserRole.selectRole')) ?>" required>
                            <option value=""><?= esc(lang('UserRole.selectRole')) ?></option>
                            <?php foreach ($assignableRoles as $role): ?>
                                <option value="<?= (int) ($role['id'] ?? 0) ?>" <?= $currentRoleId === (int) ($role['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($role['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" onclick="closeInlineModal('roleModal')" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('UserRole.cancel')) ?></span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            <i class="fas fa-user-tag text-xs"></i>
                            <span><?= esc(lang('UserRole.assignRole')) ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="passwordModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="user-inline-modal-backdrop fixed inset-0"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeInlineModal('passwordModal')">
            <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white shadow-2xl" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900"><?= esc(lang('UserRole.changePassword')) ?></h4>
                        <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UserRole.passwordRequirements')) ?></p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeInlineModal('passwordModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="inlinePasswordForm" class="space-y-5 p-6">
                    <div>
                        <label for="inlineNewPasswordInput" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.newPassword')) ?></label>
                        <input type="password" id="inlineNewPasswordInput" name="new_password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                    </div>
                    <div>
                        <label for="inlineConfirmPasswordInput" class="mb-1 block text-sm font-semibold text-slate-700"><?= esc(lang('UserRole.confirmNewPassword')) ?></label>
                        <input type="password" id="inlineConfirmPasswordInput" name="confirm_password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" onclick="closeInlineModal('passwordModal')" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('UserRole.cancel')) ?></span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            <i class="fas fa-key text-xs"></i>
                            <span><?= esc(lang('UserRole.changePassword')) ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($canEditFinance): ?>
    <div id="recordPayoutModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-950/45"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closePayoutModal()">
            <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-2xl" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h4 class="text-base font-semibold text-slate-900"><?= esc(lang('Finance.recordProviderPayout')) ?></h4>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closePayoutModal()">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="recordPayoutForm" class="space-y-4 p-5">
                    <input type="hidden" id="payoutIdInput">
                    <div>
                        <label for="payoutAmountInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.amount')) ?></label>
                        <input type="number" step="0.01" min="0.01" id="payoutAmountInput" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        <p id="payoutAmountHelp" class="mt-1 text-xs text-slate-500"></p>
                    </div>
                    <div>
                        <label for="payoutMethodInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.paymentMethod')) ?></label>
                        <select id="payoutMethodInput" class="user-modal-select w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" data-searchable-select data-minimum-input-length="0" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>" data-allow-clear="false">
                            <option value="cash"><?= esc(lang('Finance.cash')) ?></option>
                            <option value="card"><?= esc(lang('Finance.card')) ?></option>
                            <option value="bank_transfer"><?= esc(lang('Finance.bankTransfer')) ?></option>
                            <option value="check"><?= esc(lang('Finance.check')) ?></option>
                            <option value="other"><?= esc(lang('Finance.other')) ?></option>
                        </select>
                    </div>
                    <div id="payoutLiquidityAccountField" class="hidden">
                        <label for="payoutLiquidityAccountInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.paidFromAccount')) ?></label>
                        <select id="payoutLiquidityAccountInput" class="user-modal-select w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" data-searchable-select data-minimum-input-length="0" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>" data-allow-clear="false">
                            <option value="bank"><?= esc(lang('Finance.liquidityAccount.bank')) ?></option>
                            <option value="undeposited_checks"><?= esc(lang('Finance.liquidityAccount.undeposited_checks')) ?></option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500"><?= esc(lang('Finance.checkOutgoingAccountHelp')) ?></p>
                    </div>
                    <div>
                        <label for="payoutDateInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.paidDate')) ?></label>
                        <input type="date" id="payoutDateInput" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                    </div>
                    <div>
                        <label for="payoutNotesInput" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('Finance.notes')) ?></label>
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
    ['editUserModal', 'roleModal', 'passwordModal'].forEach((id) => {
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
    'inlineEditUserForm',
    `<?= base_url('users/' . (int) $viewedUser->id . '/inline-update') ?>`,
    <?= json_encode('User updated successfully') ?>,
    () => {
        closeInlineModal('editUserModal');
        location.reload();
    }
);

submitInlineUserForm(
    'inlineRoleForm',
    `<?= base_url('users/' . (int) $viewedUser->id . '/inline-update-role') ?>`,
    <?= json_encode('Role updated successfully') ?>,
    () => {
        closeInlineModal('roleModal');
        location.reload();
    }
);

submitInlineUserForm(
    'inlinePasswordForm',
    `<?= base_url('users/' . (int) $viewedUser->id . '/inline-reset-password') ?>`,
    <?= json_encode('Password updated successfully') ?>,
    () => {
        closeInlineModal('passwordModal');
        document.getElementById('inlinePasswordForm')?.reset();
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

function openPayoutModal(id, maxAmount) {
    mountUserModal('recordPayoutModal');
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
