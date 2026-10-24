<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<style>
    .control-settings-shell {
        background:var(--denta-alabaster);
    }
    .control-settings-shell-card {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(226, 232, 240, 0.84);
        box-shadow:none;
        backdrop-filter:none;
    }
    .control-settings-shell-breadcrumb {
        background: rgba(255, 255, 255, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow:none;
        backdrop-filter:none;
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
        color: var(--denta-ink);
        transition: all 0.2s ease;
        text-align: left;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: rgba(255, 255, 255, 0.88);
        box-shadow:none;
        backdrop-filter:none;
    }
    .control-settings-menu-btn:hover {
        background:var(--denta-alabaster);
        color: var(--denta-forest);
        border-color: rgba(59, 130, 246, 0.28);
        transform: translateY(-2px);
        box-shadow:none;
    }
    .control-settings-menu-btn i {
        color: var(--denta-muted);
        transition: color 0.2s ease;
    }
    .control-settings-menu-btn.is-active {
        background:var(--denta-alabaster);
        color:var(--denta-ivory-input);
        border-color: transparent;
        box-shadow:none;
    }
    .control-settings-menu-btn.is-active i {
        color:var(--denta-ivory-input);
    }
    .control-settings-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.88);
        border-radius: 1.5rem;
        box-shadow:none;
        backdrop-filter:none;
        overflow: hidden;
    }
    .control-settings-header {
        background:var(--denta-alabaster);
        padding: 1.35rem 1.5rem;
    }
    .control-settings-header h3,
    .control-settings-header h4 {
        color: #fff !important;
    }
    .control-settings-header p {
        color: var(--denta-taupe-border) !important;
    }
    .control-settings-header . {
        backdrop-filter:none;
        backdrop-filter:none;
    }
    .control-settings-header .w-12.h-12 {
        background: rgba(255, 255, 255, 0.2) !important;
        border-radius: 0.75rem !important;
        box-shadow:none;
    }
    .control-settings-field {
        width: 100%;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(203, 213, 225, 0.95);
        border-radius: 0.95rem;
        color: var(--denta-ink);
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow:none;
    }
    .control-settings-field:focus {
        outline: none;
        border-color: var(--denta-emerald-pine);
        background: rgba(255, 255, 255, 0.96);
        box-shadow:none;
    }
    .control-settings-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.95rem;
        background:var(--denta-alabaster);
        color:var(--denta-ivory-input);
        font-weight: 600;
        box-shadow:none;
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
        color: var(--denta-muted);
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow:none;
        backdrop-filter:none;
    }
    .control-settings-secondary:hover {
        background: rgba(255, 255, 255, 0.95);
        border-color: rgba(59, 130, 246, 0.28);
        color: var(--denta-forest);
    }
    .control-settings-card > .p-8 {
        padding: 1.35rem !important;
    }
    .control-settings-surface {
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 1.2rem;
        background:var(--denta-alabaster);
        box-shadow:none;
    }
    .control-settings-metric {
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 1.25rem;
        background:var(--denta-alabaster);
        box-shadow:none;
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
        <section class="overflow-hidden cp-radius border cp-border v3-bg-forest">
            <div class="relative p-4 sm:p-5">
                <div class="absolute inset-0 v3-bg-panel"></div>
                <div class="relative grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center cp-radius cp-surface v3-text-on-primary border v3-border-default">
                            <i class="fas fa-cog text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold v3-text-primary"><?= esc(lang('V3Platform.globalConfiguration')) ?></p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight v3-text-on-primary sm:text-3xl"><?= esc(lang('V3Platform.controlPlaneSettings')) ?></h1>
                            <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 cp-accent"><?= esc(lang('V3Platform.managePlatformIdentityClinicOnboardingDefaultsRetentionGovernanceAndSharedCommunicationTemplatesFromOneControlledWorkspace')) ?></p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                <span class="rounded-full v3-bg-primary px-3 py-1.5 v3-text-primary border v3-border-default">
                                    <i class="fas fa-lock me-1"></i><?= esc(lang('V3Platform.globalMode')) ?>
                                </span>
                                <span class="rounded-full cp-surface px-3 py-1.5 v3-text-on-primary border v3-border-default"><?= esc($settings['platform_name'] ?? 'Platform') ?></span>
                                <span class="rounded-full cp-surface px-3 py-1.5 v3-text-on-primary border v3-border-default"><?= esc($settings['default_timezone'] ?? 'Asia/Jerusalem') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid min-w-full grid-cols-2 gap-3 sm:min-w-[30rem] lg:grid-cols-4">
                        <div class="cp-radius cp-surface p-3 border v3-border-default">
                            <p class="text-[11px] font-bold cp-accent"><?= esc(lang('V3Platform.currency')) ?></p>
                            <p class="mt-1 text-2xl font-bold v3-text-on-primary"><?= esc($settings['default_currency'] ?? 'ILS') ?></p>
                        </div>
                        <div class="cp-radius v3-bg-primary p-3 border v3-border-default">
                            <p class="text-[11px] font-bold v3-text-primary"><?= esc(lang('V3Platform.slotLength')) ?></p>
                            <p class="mt-1 text-2xl font-bold v3-text-on-primary"><?= (int) ($settings['default_appointment_duration'] ?? 30) ?>m</p>
                        </div>
                        <div class="cp-radius v3-bg-primary p-3 border v3-border-default">
                            <p class="text-[11px] font-bold cp-accent"><?= esc(lang('V3Platform.emailTemplates2')) ?></p>
                            <p class="mt-1 text-2xl font-bold v3-text-on-primary"><?= number_format($templateStats['total'] ?? 0) ?></p>
                        </div>
                        <div class="cp-radius v3-bg-warning p-3 border v3-border-default">
                            <p class="text-[11px] font-bold v3-text-warning"><?= esc(lang('V3Platform.exportRetention')) ?></p>
                            <p class="mt-1 text-2xl font-bold v3-text-on-primary"><?= esc(ucwords(str_replace('_', ' ', (string) ($settings['retention_mode'] ?? 'latest')))) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <nav class="control-settings-shell-breadcrumb mb-5 flex items-center gap-1 cp-radius px-4 py-3 text-sm v3-text-muted" id="control-settings-breadcrumb">
            <span class="v3-text-ink font-medium"><?= esc(lang('V3Platform.controlPlane')) ?></span>
            <i class="fas fa-chevron-right text-xs v3-text-muted"></i>
            <span class="v3-text-ink font-medium"><?= esc(lang('V3Platform.settings')) ?></span>
            <i class="fas fa-chevron-right text-xs v3-text-muted"></i>
            <span id="control-breadcrumb-tab" class="v3-text-ink font-semibold"></span>
        </nav>
        <?php $controlSettingsErrors = session('errors'); ?>
        <?php if (session('error') || (!empty($controlSettingsErrors) && is_array($controlSettingsErrors))): ?>
            <div id="control-settings-feedback" class="mb-6 cp-radius border v3-border-default v3-bg-alert px-5 py-4 v3-text-danger">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 v3-text-danger">
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

        <section class="control-settings-shell-card v3-radius p-3 sm:p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3 px-1 sm:px-2">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.settingsAreas')) ?></p>
                    <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc(lang('V3Platform.chooseTheOperatingSurfaceYouNeedToReviewOrUpdate')) ?></p>
                </div>
                <span class="rounded-full cp-soft-surface px-3 py-1.5 text-xs font-bold cp-accent border v3-border-default"><?= esc(lang('V3Platform.changesAreSavedPerSection')) ?></span>
            </div>
            <nav class="grid gap-3 control-settings-sidebar-nav sm:grid-cols-2 xl:grid-cols-6">
                <button onclick="showTab('platform')" id="nav-platform" class="control-settings-menu-btn is-active v3-btn v3-btn--secondary">
                    <i class="fas fa-id-badge w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.platformProfile')) ?></span>
                </button>
                <button onclick="showTab('system-preferences')" id="nav-system-preferences" class="control-settings-menu-btn v3-btn v3-btn--secondary">
                    <i class="fas fa-clinic-medical w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.systemPreferences')) ?></span>
                </button>
                <button onclick="showTab('working-hours')" id="nav-working-hours" class="control-settings-menu-btn v3-btn v3-btn--secondary">
                    <i class="fas fa-clock w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.workingHours')) ?></span>
                </button>
                <button onclick="showTab('governance')" id="nav-governance" class="control-settings-menu-btn v3-btn v3-btn--secondary">
                    <i class="fas fa-shield-alt w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.governance')) ?></span>
                </button>
                <button onclick="showTab('email-templates')" id="nav-email-templates" class="control-settings-menu-btn v3-btn v3-btn--secondary">
                    <i class="fas fa-envelope-open-text w-5 h-5"></i>
                    <span><?= esc(lang('V3Platform.emailTemplates')) ?></span>
                </button>
                <button onclick="showTab('whatsapp-templates')" id="nav-whatsapp-templates" class="control-settings-menu-btn v3-btn v3-btn--secondary">
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

<div id="confirm-modal" class="fixed inset-0 v3-backdrop z-50 hidden items-center justify-center p-4">
    <div class="cp-surface cp-radius max-w-md w-full cp-panel-padding">
        <div class="mb-6 flex items-start gap-4">
            <div class="w-12 h-12 v3-bg-alert cp-radius flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle v3-text-danger text-xl"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-xl font-bold v3-text-ink mb-1"><?= esc(lang('V3Platform.areYouSure')) ?></h3>
                <p id="confirm-modal-message" class="v3-text-muted text-sm"><?= esc(lang('V3Platform.thisActionCannotBeUndone')) ?></p>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <button onclick="closeConfirmModal()"
                    class="px-6 py-2.5 cp-surface border cp-border v3-text-ink font-semibold cp-radius v3-hover-soft transition-all v3-btn v3-btn--secondary">
                <?= esc(lang('V3Platform.cancel')) ?>
            </button>
            <button id="confirm-modal-action"
                    class="px-6 py-2.5 v3-text-on-primary font-semibold cp-radius transition-all v3-btn v3-btn--secondary">
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
        container.className = 'fixed top-5 z-[100] max-w-sm v3-radius px-4 py-3 text-sm font-semibold v3-elevation-none transition-all';
        container.style.insetInlineEnd = '1.25rem';
        document.body.appendChild(container);
    }

    container.className = `fixed top-5 z-[100] max-w-sm v3-radius px-4 py-3 text-sm font-semibold v3-elevation-none transition-all ${
        type === 'success' ? 'v3-bg-primary v3-text-on-primary' : 'v3-bg-danger v3-text-on-primary'
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
                field.classList.remove('v3-border-default', 'v3-focus-control', 'v3-focus-control');
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
                        field.classList.add('v3-border-default', 'v3-focus-control', 'v3-focus-control');
                    } else {
                        field.classList.remove('v3-border-default', 'v3-focus-control', 'v3-focus-control');
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
                label.classList.add('v3-border-default', 'v3-bg-scheduled');
                label.classList.remove('v3-border-default');
            } else {
                label.classList.remove('v3-border-default', 'v3-bg-scheduled');
                label.classList.add('v3-border-default');
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

