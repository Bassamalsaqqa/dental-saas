<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<style>
    .control-settings-shell {
        background:
            radial-gradient(circle at 8% 0%, rgba(59, 130, 246, 0.12), transparent 30%),
            radial-gradient(circle at 92% 8%, rgba(16, 185, 129, 0.10), transparent 28%),
            linear-gradient(135deg, #eef5ff 0%, #f8fafc 48%, #ecfdf5 100%);
    }
    .control-settings-shell-card {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(226, 232, 240, 0.84);
        box-shadow: 0 18px 42px -32px rgba(15, 23, 42, 0.34);
        backdrop-filter: blur(18px);
    }
    .control-settings-shell-breadcrumb {
        background: rgba(255, 255, 255, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 10px 30px -24px rgba(15, 23, 42, 0.3);
        backdrop-filter: blur(18px);
    }
    .control-settings-menu-btn {
        display: flex;
        align-items: center;
        width: 100%;
        gap: 0.8rem;
        min-height: 5.25rem;
        padding: 1rem;
        border-radius: 1.15rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        transition: all 0.2s ease;
        text-align: left;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 14px 32px -28px rgba(15, 23, 42, 0.32);
        backdrop-filter: blur(14px);
    }
    .control-settings-menu-btn:hover {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(239, 246, 255, 0.92));
        color: #0f172a;
        border-color: rgba(59, 130, 246, 0.28);
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -30px rgba(59, 130, 246, 0.4);
    }
    .control-settings-menu-btn i {
        color: #475569;
        transition: color 0.2s ease;
    }
    .control-settings-menu-btn.is-active {
        background: linear-gradient(135deg, #0f172a, #1d4ed8 54%, #0f766e 100%);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 22px 40px -24px rgba(37, 99, 235, 0.5);
    }
    .control-settings-menu-btn.is-active i {
        color: #fff;
    }
    .control-settings-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.88);
        border-radius: 1.5rem;
        box-shadow: 0 18px 44px -34px rgba(15, 23, 42, 0.36);
        backdrop-filter: blur(18px);
        overflow: hidden;
    }
    .control-settings-header {
        background:
            radial-gradient(circle at 12% 18%, rgba(96, 165, 250, 0.42), transparent 28%),
            radial-gradient(circle at 86% 18%, rgba(45, 212, 191, 0.28), transparent 24%),
            linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #0f766e 100%);
        padding: 1.35rem 1.5rem;
    }
    .control-settings-header h3,
    .control-settings-header h4 {
        color: #fff !important;
    }
    .control-settings-header p {
        color: #cbd5e1 !important;
    }
    .control-settings-header .backdrop-blur-sm {
        -webkit-backdrop-filter: blur(4px) !important;
        backdrop-filter: blur(4px) !important;
    }
    .control-settings-header .w-12.h-12 {
        background: rgba(255, 255, 255, 0.2) !important;
        border-radius: 0.75rem !important;
        box-shadow: none !important;
    }
    .control-settings-field {
        width: 100%;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(203, 213, 225, 0.95);
        border-radius: 0.95rem;
        color: #334155;
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.45);
    }
    .control-settings-field:focus {
        outline: none;
        border-color: #3b82f6;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.10);
    }
    .control-settings-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.95rem;
        background: linear-gradient(135deg, #1d4ed8, #2563eb 48%, #14b8a6 100%);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 18px 38px -22px rgba(37, 99, 235, 0.5);
        transition: all 0.2s ease;
    }
    .control-settings-submit:hover {
        filter: brightness(0.98);
        transform: translateY(-1px);
    }
    .control-settings-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.95rem;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(203, 213, 225, 0.9);
        color: #475569;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 12px 26px -24px rgba(15, 23, 42, 0.35);
        backdrop-filter: blur(14px);
    }
    .control-settings-secondary:hover {
        background: rgba(255, 255, 255, 0.95);
        border-color: rgba(59, 130, 246, 0.28);
        color: #0f172a;
    }
    .control-settings-card > .p-8 {
        padding: 1.35rem !important;
    }
    .control-settings-surface {
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 1.2rem;
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.94), rgba(239, 246, 255, 0.72));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.5);
    }
    .control-settings-metric {
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 1.25rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(239, 246, 255, 0.74));
        box-shadow: 0 18px 40px -32px rgba(15, 23, 42, 0.22);
    }
    .ui-toggle {
        appearance: none;
        width: 44px;
        height: 24px;
        border-radius: 9999px;
        background: #e5e7eb;
        position: relative;
        cursor: pointer;
        transition: background 0.2s ease;
        flex-shrink: 0;
    }
    .ui-toggle::before {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        top: 3px;
        left: 3px;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .ui-toggle:checked {
        background: #3b82f6;
    }
    .ui-toggle:checked::before {
        transform: translateX(20px);
    }
    [dir="rtl"] .ui-toggle::before {
        left: auto;
        right: 3px;
    }
    [dir="rtl"] .ui-toggle:checked::before {
        transform: translateX(-20px);
    }
    .ui-toggle:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    [dir="rtl"] #control-settings-breadcrumb {
        direction: rtl;
    }
    [dir="rtl"] #control-settings-breadcrumb .fa-chevron-right::before {
        content: "\f053";
    }
    [dir="rtl"] .control-settings-menu-btn {
        text-align: right;
    }
    [dir="rtl"] .control-settings-menu-btn i {
        margin-right: 0 !important;
        margin-left: 0.75rem !important;
    }
    @media (max-width: 1279px) {
        #control-settings-breadcrumb {
            flex-wrap: wrap;
            row-gap: 0.35rem;
        }
        .control-settings-sidebar-nav {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
            scroll-snap-type: x proximity;
        }
        .control-settings-menu-btn {
            width: auto;
            min-width: 13rem;
            flex: 0 0 auto;
            scroll-snap-align: start;
            white-space: nowrap;
        }
    }
</style>
<div class="v3-platform-workspace control-settings-shell min-h-screen">
    <div class="w-full space-y-5 px-3 py-3 sm:px-4 lg:px-5">
        <section class="overflow-hidden cp-radius border cp-border bg-slate-950">
            <div class="relative p-4 sm:p-5">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_14%_16%,rgba(59,130,246,.54),transparent_30%),radial-gradient(circle_at_86%_12%,rgba(16,185,129,.32),transparent_25%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_54%,#0f766e_100%)]"></div>
                <div class="relative grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center cp-radius cp-surface text-white ring-1 ring-white/20">
                            <i class="fas fa-cog text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-emerald-200"><?= esc(lang('V3Platform.globalConfiguration')) ?></p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl"><?= esc(lang('V3Platform.controlPlaneSettings')) ?></h1>
                            <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 cp-accent"><?= esc(lang('V3Platform.managePlatformIdentityClinicOnboardingDefaultsRetentionGovernanceAndSharedCommunicationTemplatesFromOneControlledWorkspace')) ?></p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                <span class="rounded-full bg-emerald-400/20 px-3 py-1.5 text-emerald-50 ring-1 ring-emerald-200/25">
                                    <i class="fas fa-lock me-1"></i><?= esc(lang('V3Platform.globalMode')) ?>
                                </span>
                                <span class="rounded-full cp-surface px-3 py-1.5 text-white ring-1 ring-white/18"><?= esc($settings['platform_name'] ?? 'Platform') ?></span>
                                <span class="rounded-full cp-surface px-3 py-1.5 text-white ring-1 ring-white/18"><?= esc($settings['default_timezone'] ?? 'Asia/Jerusalem') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid min-w-full grid-cols-2 gap-3 sm:min-w-[30rem] lg:grid-cols-4">
                        <div class="cp-radius cp-surface p-3 ring-1 ring-white/15">
                            <p class="text-[11px] font-bold cp-accent"><?= esc(lang('V3Platform.currency')) ?></p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= esc($settings['default_currency'] ?? 'ILS') ?></p>
                        </div>
                        <div class="cp-radius bg-emerald-400/20 p-3 ring-1 ring-emerald-200/25">
                            <p class="text-[11px] font-bold text-emerald-100"><?= esc(lang('V3Platform.slotLength')) ?></p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= (int) ($settings['default_appointment_duration'] ?? 30) ?>m</p>
                        </div>
                        <div class="cp-radius bg-violet-400/20 p-3 ring-1 ring-violet-200/20">
                            <p class="text-[11px] font-bold cp-accent"><?= esc(lang('V3Platform.emailTemplates2')) ?></p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= number_format($templateStats['total'] ?? 0) ?></p>
                        </div>
                        <div class="cp-radius bg-amber-400/20 p-3 ring-1 ring-amber-200/25">
                            <p class="text-[11px] font-bold text-amber-100"><?= esc(lang('V3Platform.exportRetention')) ?></p>
                            <p class="mt-1 text-2xl font-bold text-white"><?= esc(ucwords(str_replace('_', ' ', (string) ($settings['retention_mode'] ?? 'latest')))) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <nav class="control-settings-shell-breadcrumb mb-5 flex items-center gap-1 cp-radius px-4 py-3 text-sm text-slate-500" id="control-settings-breadcrumb">
            <span class="text-gray-700 font-medium"><?= esc(lang('V3Platform.controlPlane')) ?></span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium"><?= esc(lang('V3Platform.settings')) ?></span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span id="control-breadcrumb-tab" class="text-gray-900 font-semibold"></span>
        </nav>
        <?php $controlSettingsErrors = session('errors'); ?>
        <?php if (session('error') || (!empty($controlSettingsErrors) && is_array($controlSettingsErrors))): ?>
            <div id="control-settings-feedback" class="mb-6 cp-radius border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-rose-600">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-bold"><?= esc(lang('V3Platform.pleaseReviewTheHighlightedSettingsAndTryAgain')) ?></p>
                        <?php if (session('error')): ?>
                            <p class="text-sm"><?= esc(session('error')) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($controlSettingsErrors) && is_array($controlSettingsErrors)): ?>
                            <ul class="list-disc space-y-1 ps-5 text-sm">
                                <?php foreach ($controlSettingsErrors as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <section class="control-settings-shell-card rounded-[1.75rem] p-3 sm:p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3 px-1 sm:px-2">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500"><?= esc(lang('V3Platform.settingsAreas')) ?></p>
                    <p class="mt-1 text-sm font-semibold text-slate-600"><?= esc(lang('V3Platform.chooseTheOperatingSurfaceYouNeedToReviewOrUpdate')) ?></p>
                </div>
                <span class="rounded-full cp-soft-surface px-3 py-1.5 text-xs font-bold cp-accent ring-1 ring-blue-100"><?= esc(lang('V3Platform.changesAreSavedPerSection')) ?></span>
            </div>
            <nav class="grid gap-3 control-settings-sidebar-nav sm:grid-cols-2 xl:grid-cols-6">
                <button onclick="showTab('platform')" id="nav-platform" class="control-settings-menu-btn is-active">
                    <i class="fas fa-id-badge w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.platformProfile')) ?></span>
                </button>
                <button onclick="showTab('system-preferences')" id="nav-system-preferences" class="control-settings-menu-btn">
                    <i class="fas fa-clinic-medical w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.systemPreferences')) ?></span>
                </button>
                <button onclick="showTab('working-hours')" id="nav-working-hours" class="control-settings-menu-btn">
                    <i class="fas fa-clock w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.workingHours')) ?></span>
                </button>
                <button onclick="showTab('governance')" id="nav-governance" class="control-settings-menu-btn">
                    <i class="fas fa-shield-alt w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.governance')) ?></span>
                </button>
                <button onclick="showTab('email-templates')" id="nav-email-templates" class="control-settings-menu-btn">
                    <i class="fas fa-envelope-open-text w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.emailTemplates')) ?></span>
                </button>
                <button onclick="showTab('whatsapp-templates')" id="nav-whatsapp-templates" class="control-settings-menu-btn">
                    <i class="fab fa-whatsapp w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.whatsappTemplates')) ?></span>
                </button>
            </nav>
        </section>

        <section class="min-w-0">
            <?= $this->include('control_plane/settings_partials/platform') ?>
            <?= $this->include('control_plane/settings_partials/clinic_defaults') ?>
            <?= $this->include('control_plane/settings_partials/governance') ?>
            <?= $this->include('control_plane/settings_partials/communications') ?>
        </section>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="cp-surface cp-radius max-w-md w-full cp-panel-padding">
        <div class="mb-6 flex items-start gap-4">
            <div class="w-12 h-12 bg-red-100 cp-radius flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-xl font-bold text-gray-900 mb-1"><?= esc(lang('V3Platform.areYouSure')) ?></h3>
                <p id="confirm-modal-message" class="text-gray-500 text-sm"><?= esc(lang('V3Platform.thisActionCannotBeUndone')) ?></p>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <button onclick="closeConfirmModal()"
                    class="px-6 py-2.5 cp-surface border cp-border text-gray-700 font-semibold cp-radius hover:bg-gray-50 transition-all">
                <?= esc(lang('V3Platform.cancel')) ?>
            </button>
            <button id="confirm-modal-action"
                    class="px-6 py-2.5 text-white font-semibold cp-radius transition-all">
                <?= esc(lang('V3Platform.confirm')) ?>
            </button>
        </div>
    </div>
</div>

<script>
function showTab(tabName, options = {}) {
    const tabNames = {
        platform: 'Platform Profile',
        'system-preferences': 'System Preferences',
        'working-hours': 'Working Hours',
        governance: 'Governance',
        'email-templates': 'Email Templates',
        'whatsapp-templates': 'WhatsApp Templates',
    };

    if (!document.getElementById('tab-' + tabName)) {
        tabName = 'platform';
    }

    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));

    const target = document.getElementById('tab-' + tabName);
    if (target) {
        target.classList.remove('hidden');
    }

    if (tabName === 'whatsapp-templates' && typeof showWhatsAppSettingsTab === 'function') {
        const requestedPane = new URLSearchParams(window.location.search).get('wa_tab');
        showWhatsAppSettingsTab(['overview', 'templates', 'rollout', 'credentials'].includes(requestedPane) ? requestedPane : 'templates');
    }

    const navButtons = document.querySelectorAll('[id^="nav-"]');
    navButtons.forEach(btn => btn.classList.remove('is-active'));

    const activeBtn = document.getElementById('nav-' + tabName);
    if (activeBtn) {
        activeBtn.classList.add('is-active');
    }

    const breadcrumbTab = document.getElementById('control-breadcrumb-tab');
    if (breadcrumbTab) {
        breadcrumbTab.textContent = tabNames[tabName] || '';
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    url.searchParams.delete('subtab');
    history.pushState(null, '', url.toString());
}

function toastMessage(type, message) {
    if (window.showToast) {
        window.showToast(type === 'success' ? 'success' : 'error', type === 'success' ? 'Success' : 'Error', message);
        return;
    }

    let container = document.getElementById('control-settings-toast');
    if (!container) {
        container = document.createElement('div');
        container.id = 'control-settings-toast';
        container.className = 'fixed top-5 z-[100] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-2xl transition-all';
        container.style.insetInlineEnd = '1.25rem';
        document.body.appendChild(container);
    }

    container.className = `fixed top-5 z-[100] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-2xl transition-all ${
        type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'
    }`;
    container.style.insetInlineEnd = '1.25rem';
    container.textContent = message;
    clearTimeout(container._hideTimer);
    container._hideTimer = setTimeout(() => {
        container.remove();
    }, 3500);
}

function setButtonLoading(button, loading, label = 'Saving...') {
    if (!button) return;

    if (loading) {
        button.dataset.originalText = button.textContent || '';
        button.textContent = label;
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        return;
    }

    button.textContent = button.dataset.originalText || button.textContent;
    button.disabled = false;
    button.classList.remove('opacity-75', 'cursor-not-allowed');
}

function bindManagedForms() {
    document.querySelectorAll('form[data-settings-form="true"]').forEach((form) => {
        const trackedFields = form.querySelectorAll('input, select, textarea');

        trackedFields.forEach((field) => {
            const syncFieldState = () => {
                field.classList.remove('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-100');
                if (!field.checkValidity()) {
                    return;
                }

                document.getElementById('control-settings-feedback')?.remove();
            };

            field.addEventListener('input', syncFieldState);
            field.addEventListener('change', syncFieldState);
        });

        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                trackedFields.forEach((field) => {
                    if (typeof field.checkValidity === 'function' && !field.checkValidity()) {
                        field.classList.add('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-100');
                    } else {
                        field.classList.remove('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-100');
                    }
                });
                form.reportValidity();
                return;
            }

            const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
            if (!submitButton) {
                return;
            }

            setButtonLoading(submitButton, true, form.dataset.savingLabel || 'Saving...');
        });
    });
}

function initRetentionControls() {
    const retentionRadios = document.querySelectorAll('input[name="retention_mode"]');
    const lastN = document.getElementById('retention-last-n');
    const days = document.getElementById('retention-days');

    retentionRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (lastN) lastN.classList.add('hidden');
            if (days) days.classList.add('hidden');

            if (radio.value === 'keep_last_n' && lastN) {
                lastN.classList.remove('hidden');
            }
            if (radio.value === 'keep_x_days' && days) {
                days.classList.remove('hidden');
            }
        });
    });
}

function bindPruneActions() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.getAttribute('content') || 'X-CSRF-TOKEN';

    const pruneExportsBtn = document.getElementById('prune-exports-btn');
    if (pruneExportsBtn) {
        pruneExportsBtn.addEventListener('click', () => {
            openConfirmModal(
                'Prune Export Artifacts',
                'This will permanently delete all expired and superseded export files across all clinics. Only files outside the retention window are affected. This cannot be undone.',
                () => {
                    fetch('<?= base_url('controlplane/settings/retention/prune') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            [csrfHeader]: csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastMessage('success', data.message || 'Export artifacts pruned.');
                                return;
                            }
                            toastMessage('error', data.message || 'Pruning failed.');
                        })
                        .catch(() => toastMessage('error', 'Pruning failed.'));
                },
                'Prune Now'
            );
        });
    }

    const pruneAuditsBtn = document.getElementById('prune-audits-btn');
    if (pruneAuditsBtn) {
        pruneAuditsBtn.addEventListener('click', () => {
            openConfirmModal(
                'Prune Audit Log Entries',
                'This will permanently delete all control plane audit log entries older than the configured retention window. Deleted entries cannot be recovered.',
                () => {
                    fetch('<?= base_url('controlplane/settings/audit-retention/prune') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            [csrfHeader]: csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastMessage('success', data.message || 'Audit logs pruned.');
                                return;
                            }
                            toastMessage('error', data.message || 'Pruning failed.');
                        })
                        .catch(() => toastMessage('error', 'Pruning failed.'));
                },
                'Prune Now'
            );
        });
    }
}

function openConfirmModal(title, message, callback, label = 'Confirm') {
    const modal = document.getElementById('confirm-modal');
    const titleEl = document.getElementById('confirm-modal-title');
    const messageEl = document.getElementById('confirm-modal-message');
    const actionBtn = document.getElementById('confirm-modal-action');
    if (!modal || !titleEl || !messageEl || !actionBtn) return;

    titleEl.textContent = title;
    messageEl.textContent = message;
    actionBtn.textContent = label;
    actionBtn.onclick = () => {
        closeConfirmModal();
        callback();
    };
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const urlTab = new URLSearchParams(window.location.search).get('tab');
    const legacySubtab = new URLSearchParams(window.location.search).get('subtab');
    const legacyTabMap = { 'clinic-defaults': 'system-preferences' };
    const normalizedTab = urlTab === 'communications' ? (legacySubtab || 'email-templates') : (legacyTabMap[urlTab] || urlTab);
    if (normalizedTab && document.getElementById(`tab-${normalizedTab}`)) {
        showTab(normalizedTab, { updateUrl: false });
    } else {
        showTab('platform', { updateUrl: false });
    }

    initRetentionControls();
    bindPruneActions();
    bindManagedForms();

    document.querySelectorAll('input[name="default_working_days[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const label = checkbox.closest('label');
            if (!label) return;
            if (checkbox.checked) {
                label.classList.add('border-blue-500', 'bg-blue-50');
                label.classList.remove('border-slate-200');
            } else {
                label.classList.remove('border-blue-500', 'bg-blue-50');
                label.classList.add('border-slate-200');
            }
        });
    });

    document.getElementById('confirm-modal')?.addEventListener('click', (event) => {
        if (event.target === event.currentTarget) {
            closeConfirmModal();
        }
    });
});

window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search);
    const requestedTab = params.get('tab') || 'platform';
    const legacyTabMap = { 'clinic-defaults': 'system-preferences' };
    const normalizedTab = requestedTab === 'communications' ? (params.get('subtab') || 'email-templates') : (legacyTabMap[requestedTab] || requestedTab);
    showTab(normalizedTab, { updateUrl: false });
});

function toggleSecretVis(btn) {
    const input = btn.closest('.relative')?.querySelector('input[name="new_value"]');
    const icon = btn.querySelector('i');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash text-sm';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye text-sm';
    }
}
</script>
<?= $this->endSection() ?>

