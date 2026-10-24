<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<?php
    $clinicDisplayName = trim((string) ($settings['clinic_name'] ?? ''));
    $clinicDisplayName = $clinicDisplayName !== '' ? $clinicDisplayName : lang('UI.settingsClinicProfile');
    $planDisplayName = (string) ($subscription_plan['name'] ?? lang('TenantMisc.unassigned'));
    $subscriptionStatus = (string) ($subscription['status'] ?? lang('TenantMisc.none'));
    $timezoneDisplay = (string) ($settings['timezone'] ?? 'Asia/Jerusalem');
    $languageDisplay = (string) (($settings['ui_language'] ?? 'en') === 'ar' ? 'AR' : 'EN');
?>


<div>
    <div class="denta-settings-workspace settings-page-wrap mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <header class="v3-command-header"><div class="v3-command-title"><h1><?= lang('UI.systemSettings') ?></h1><p><?= lang('UI.settingsConfigureSystem') ?></p></div></header><section class="v3-settings-context"><div class="v3-settings-summary-strip">
                    <div class="settings-hero-chip v3-corner px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] v3-accent-text"><?= lang('UI.settingsClinicProfile') ?></p>
                        <p class="mt-1 truncate text-sm font-black text-white"><?= esc($clinicDisplayName) ?></p>
                    </div>
                    <div class="settings-hero-chip v3-corner px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] v3-accent-text"><?= lang('UI.settingsSubscription') ?></p>
                        <p class="mt-1 truncate text-sm font-black text-white"><?= esc($planDisplayName) ?> <span class="font-bold v3-accent-text">/ <?= esc($subscriptionStatus) ?></span></p>
                    </div>
                    <div class="settings-hero-chip v3-corner px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] v3-accent-text"><?= lang('UI.settingsSystemTab') ?></p>
                        <p class="mt-1 truncate text-sm font-black text-white"><span dir="ltr"><?= esc($timezoneDisplay) ?></span> <span class="font-bold v3-accent-text">/ <?= esc($languageDisplay) ?></span></p>
                    </div>
                </div></section>

        <nav class="settings-shell-breadcrumb mb-5 flex items-center space-x-1 v3-corner px-4 py-3 text-sm text-slate-500" id="settings-breadcrumb">
            <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors"><?= lang('UI.home') ?></a>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium"><?= lang('UI.menuSettings') ?></span>
            <i class="fas fa-chevron-right text-xs text-gray-300" id="breadcrumb-sep-2" style="display:none"></i>
            <span id="breadcrumb-tab" class="text-gray-900 font-semibold"></span>
        </nav>

        <?php $settingsErrors = session('errors'); ?>
        <?php if (session('error') || (!empty($settingsErrors) && is_array($settingsErrors))): ?>
            <div id="settings-validation-summary" class="mb-6 v3-corner border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-rose-600">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-bold"><?= service('request')->getLocale() === 'ar' ? 'يرجى مراجعة الإعدادات المظللة ثم المحاولة مرة أخرى.' : 'Please review the highlighted settings and try again.' ?></p>
                        <?php if (session('error')): ?>
                            <p class="text-sm"><?= esc(session('error')) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($settingsErrors) && is_array($settingsErrors)): ?>
                            <ul class="list-disc space-y-1 ps-5 text-sm">
                                <?php foreach ($settingsErrors as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Settings Layout with Sidebar -->
        <div class="v3-settings-grid">
            <!-- Left Sidebar -->
            <div class="v3-settings-navigation">
                <div class="settings-shell-card v3-corner p-4">
                    <div class="mb-3 px-2">
                        <p class="settings-nav-kicker text-[11px] font-black uppercase tracking-[0.16em]"><?= lang('UI.settingsMenu') ?></p>
                    </div>
                    <nav class="space-y-3">
                        <div class="settings-nav-group space-y-2">
                        <button onclick="showSettingsTab('clinic')" id="tab-clinic" class="settings-menu-btn">
                            <i class="fas fa-building w-5 h-5"></i> <span><?= lang('UI.settingsClinicProfile') ?></span>
                        </button>
                        <button onclick="showSettingsTab('subscription')" id="tab-subscription" class="settings-menu-btn">
                            <i class="fas fa-layer-group w-5 h-5"></i> <span><?= lang('UI.settingsSubscription') ?></span>
                        </button>
                        <button onclick="showSettingsTab('system')" id="tab-system" class="settings-menu-btn">
                            <i class="fas fa-cog w-5 h-5"></i> <span><?= lang('UI.settingsSystemTab') ?></span>
                        </button>
                        <button onclick="showSettingsTab('hours')" id="tab-hours" class="settings-menu-btn">
                            <i class="fas fa-clock w-5 h-5"></i> <span><?= lang('UI.settingsOperations') ?></span>
                        </button>
                        <button onclick="showSettingsTab('kiosk')" id="tab-kiosk" class="settings-menu-btn">
                            <i class="fas fa-tablet-alt w-5 h-5"></i> <span><?= lang('UI.settingsKiosk') ?></span>
                        </button>
                        <button onclick="showSettingsTab('backup')" id="tab-backup" class="settings-menu-btn">
                            <i class="fas fa-database w-5 h-5"></i> <span><?= lang('UI.settingsBackupRestore') ?></span>
                        </button>
                        </div>

                        <div class="settings-nav-group">
                            <div class="mb-3 px-2">
                                <p class="settings-nav-kicker text-[11px] font-black uppercase tracking-[0.16em]"><?= lang('UI.settingsCommunications') ?></p>
                            </div>
                            <div class="space-y-2">
                            <button onclick="showSettingsTab('channels')" id="tab-channels" class="settings-menu-btn">
                                <i class="fas fa-th-large w-5 h-5"></i> <span><?= lang('UI.settingsChannels') ?></span>
                            </button>
                            <button onclick="showSettingsTab('email')" id="tab-email" class="settings-menu-btn">
                                <i class="fas fa-envelope w-5 h-5"></i> <span><?= lang('UI.settingsEmail') ?></span>
                            </button>
                            <button onclick="showSettingsTab('sms')" id="tab-sms" class="settings-menu-btn">
                                <i class="fas fa-sms w-5 h-5"></i> <span><?= lang('UI.settingsSms') ?></span>
                            </button>
                            <button onclick="showSettingsTab('whatsapp')" id="tab-whatsapp" class="settings-menu-btn">
                                <i class="fab fa-whatsapp w-5 h-5"></i> <span><?= lang('UI.settingsWhatsApp') ?></span>
                            </button>
                            <button onclick="showSettingsTab('alerts')" id="tab-alerts" class="settings-menu-btn">
                                <i class="fas fa-user-cog w-5 h-5"></i> <span><?= lang('UI.settingsMyAlerts') ?></span>
                            </button>
                            </div>
                        </div>

                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div id="settings-tabs" class="min-w-0">
                <!-- Clinic Information Tab -->
                <?= $this->include('settings/partials/clinic') ?>
                <?= $this->include('settings/partials/subscription') ?>
                <?= $this->include('settings/partials/system') ?>
                <?= $this->include('settings/partials/hours') ?>
                <?= $this->include('settings/partials/kiosk') ?>
                <?= $this->include('settings/partials/backup') ?>
                <?= $this->include('settings/partials/communications') ?>

            </div> <!-- End Main Content Area -->
        </div>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="v3-surface v3-corner max-w-md w-full p-8">
        <div class="flex items-start space-x-4 mb-6">
            <div id="confirm-modal-icon" class="w-12 h-12 bg-red-100 v3-radius flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-xl font-bold text-gray-900 mb-1"><?= lang('UI.confirmAreYouSure') ?></h3>
                <p id="confirm-modal-message" class="text-gray-500 text-sm"><?= lang('UI.confirmCannotUndo') ?></p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeConfirmModal()" class="px-6 py-2.5 v3-surface border v3-border text-gray-700 font-semibold v3-radius hover:bg-gray-50 transition-all">
                <?= lang('UI.cancel') ?>
            </button>
            <button id="confirm-modal-confirm-btn" type="button" class="px-6 py-2.5 bg-red-600 text-white font-semibold v3-radius hover:bg-red-700 transition-all" onclick="confirmModalProceed()">
                <?= lang('UI.confirm') ?>
            </button>
        </div>
    </div>
</div>

<script>
const csrfHeader = '<?= config('Security')->headerName ?>';
const csrfToken = '<?= csrf_hash() ?>';
let confirmModalAction = null;
const settingsI18n = {
    saving: '<?= esc(lang('UI.saving'), 'js') ?>',
    fieldRequired: '<?= esc(lang('UI.validationFieldRequired'), 'js') ?>',
    validationEmail: '<?= esc(lang('UI.validationEmail'), 'js') ?>',
    validationUrl: '<?= esc(lang('UI.validationUrl'), 'js') ?>',
    tabClinic: '<?= esc(lang('UI.settingsClinicProfile'), 'js') ?>',
    tabSubscription: '<?= esc(lang('UI.settingsSubscription'), 'js') ?>',
    tabSystem: '<?= esc(lang('UI.settingsSystemTab'), 'js') ?>',
    tabHours: '<?= esc(lang('UI.settingsOperations'), 'js') ?>',
    tabKiosk: '<?= esc(lang('UI.settingsKiosk'), 'js') ?>',
    tabBackup: '<?= esc(lang('UI.settingsBackupRestore'), 'js') ?>',
    tabCommunications: '<?= esc(lang('UI.settingsCommunications'), 'js') ?>',
    tabChannels: '<?= esc(lang('UI.settingsChannels'), 'js') ?>',
    tabEmail: '<?= esc(lang('UI.settingsEmail'), 'js') ?>',
    tabSms: '<?= esc(lang('UI.settingsSms'), 'js') ?>',
    tabWhatsapp: '<?= esc(lang('UI.settingsWhatsApp'), 'js') ?>',
    tabAlerts: '<?= esc(lang('UI.settingsMyAlerts'), 'js') ?>',
    subChannels: '<?= esc(lang('UI.settingsChannels'), 'js') ?>',
    subEmail: '<?= esc(lang('UI.settingsEmail'), 'js') ?>',
    subSms: '<?= esc(lang('UI.settingsSms'), 'js') ?>',
    subWhatsapp: '<?= esc(lang('UI.settingsWhatsApp'), 'js') ?>',
    subAlerts: '<?= esc(lang('UI.settingsMyAlerts'), 'js') ?>',
    confirmAreYouSure: '<?= esc(lang('UI.confirmAreYouSure'), 'js') ?>',
    confirmText: '<?= esc(lang('UI.confirm'), 'js') ?>',
    toastSaved: '<?= esc(lang('UI.toastSaved'), 'js') ?>',
    toastError: '<?= esc(lang('UI.toastError'), 'js') ?>',
    toastSomethingWrong: '<?= esc(lang('UI.toastSomethingWrong'), 'js') ?>',
    toastKioskLinkNotAvailable: '<?= esc(lang('UI.toastKioskLinkNotAvailable'), 'js') ?>',
    toastKioskLinkCopied: '<?= esc(lang('UI.toastKioskLinkCopied'), 'js') ?>',
    toastTestSent: '<?= esc(lang('UI.toastTestSent'), 'js') ?>',
    toastTestFailed: '<?= esc(lang('UI.toastTestFailed'), 'js') ?>',
    toastUpdated: '<?= esc(lang('UI.toastUpdated'), 'js') ?>',
    pinMismatch: '<?= esc(lang('UI.validationPinMismatch'), 'js') ?>',
    toastOperationFailedRetry: '<?= esc(lang('TenantMisc.toastOperationFailedRetry'), 'js') ?>',
    toastChannelUpdated: '<?= esc(lang('TenantMisc.toastChannelStatusUpdated'), 'js') ?>',
    toastSmtpUpdated: '<?= esc(lang('TenantMisc.toastSmtpUpdated'), 'js') ?>',
    toastSmsUpdated: '<?= esc(lang('TenantMisc.toastSmsUpdated'), 'js') ?>',
    toastCopyKioskLinkFailed: '<?= esc(lang('TenantMisc.toastCopyKioskLinkFailed'), 'js') ?>',
    toastTemplatesDefaultLoaded: '<?= esc(lang('TenantMisc.toastTemplatesDefaultLoaded'), 'js') ?>',
    confirmRestoreBackupTitle: '<?= esc(lang('TenantMisc.confirmRestoreBackupTitle'), 'js') ?>',
    confirmRestoreBackupMessage: '<?= esc(lang('TenantMisc.confirmRestoreBackupMessage'), 'js') ?>',
    confirmRestore: '<?= esc(lang('TenantMisc.restore'), 'js') ?>',
    validationPortRange: '<?= esc(lang('TenantMisc.validationPortRange'), 'js') ?>',
    validationMinChars: '<?= esc(lang('TenantMisc.validationMinChars'), 'js') ?>',
    validationMaxChars: '<?= esc(lang('TenantMisc.validationMaxChars'), 'js') ?>',
    actionCannotBeUndone: '<?= esc(lang('TenantMisc.actionCannotBeUndone'), 'js') ?>',
    toastKioskStatusFailed: '<?= esc(lang('TenantMisc.toastKioskStatusFailed'), 'js') ?>',
};

const settingsScrollStorageKey = 'tenant-settings-scroll-position';

function setButtonLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
        // Prevent "stuck on Saving..." when browser validation blocks submit.
        if (btn.form && !btn.form.checkValidity()) {
            btn.form.reportValidity();
            return;
        }
        const existingIcon = btn.querySelector('i');
        btn.dataset.originalText = btn.textContent.trim();
        btn.dataset.originalIconClass = existingIcon ? existingIcon.className : '';
        btn.replaceChildren();

        const spinner = document.createElement('i');
        spinner.className = 'fas fa-spinner fa-spin mr-2';
        btn.appendChild(spinner);
        btn.appendChild(document.createTextNode(settingsI18n.saving));
        btn.disabled = true;
        btn.dataset.loadingState = '1';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    } else {
        const originalText = btn.dataset.originalText || btn.textContent.trim();
        const originalIconClass = btn.dataset.originalIconClass || '';

        btn.replaceChildren();
        if (originalIconClass) {
            const icon = document.createElement('i');
            icon.className = originalIconClass;
            btn.appendChild(icon);
            btn.appendChild(document.createTextNode(' '));
        }
        btn.appendChild(document.createTextNode(originalText));
        btn.disabled = false;
        delete btn.dataset.loadingState;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
    }
}

function resetSettingsLoadingButtons() {
    document.querySelectorAll('button[data-loading-state="1"], button[data-original-text]').forEach((btn) => {
        setButtonLoading(btn, false);
    });
}

function submitSystemSettings() {
    const form = document.getElementById('system-settings-form');
    if (!form) {
        return;
    }
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.submit();
    }
}

function getFieldErrorElement(input) {
    if (!input || !input.parentNode) return null;
    let errorEl = input.parentNode.querySelector(`.field-error[data-for="${input.id || input.name}"]`);
    if (!errorEl) {
        errorEl = document.createElement('p');
        errorEl.className = 'field-error text-xs text-red-600 mt-1 hidden';
        errorEl.dataset.for = input.id || input.name || '';
        input.parentNode.insertBefore(errorEl, input.nextSibling);
    }
    return errorEl;
}

function setFieldError(input, message) {
    if (!input) return false;
    const errorEl = getFieldErrorElement(input);
    if (message) {
        input.classList.add('border-red-400');
        input.classList.remove('border-gray-200', 'border-green-400');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
        return false;
    }

    input.classList.remove('border-red-400');
    if ((input.value || '').trim()) {
        input.classList.add('border-green-400');
        input.classList.remove('border-gray-200');
    } else {
        input.classList.remove('border-green-400');
        input.classList.add('border-gray-200');
    }
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
    }
    return true;
}

function clearSettingsSummary() {
    document.getElementById('settings-validation-summary')?.remove();
}

function validateField(input, rules = {}) {
    if (!input) return true;
    const value = (input.value || '').trim();
    let error = '';

    if (rules.required && !value) {
        error = settingsI18n.fieldRequired;
    } else if (rules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        error = settingsI18n.validationEmail;
    } else if (rules.url && value && !/^https?:\/\/.+/i.test(value)) {
        error = settingsI18n.validationUrl;
    } else if (rules.port && value) {
        const port = parseInt(value, 10);
        if (Number.isNaN(port) || port < 1 || port > 65535) {
            error = settingsI18n.validationPortRange;
        }
    } else if (rules.minLength && value && value.length < rules.minLength) {
        error = `${settingsI18n.validationMinChars} ${rules.minLength}.`;
    } else if (rules.maxLength && value && value.length > rules.maxLength) {
        error = `${settingsI18n.validationMaxChars} ${rules.maxLength}.`;
    }

    return setFieldError(input, error);
}

function showSettingsTab(tabName, options = {}) {
    console.log('Switching to settings tab:', tabName);
    const communicationTabs = ['channels', 'email', 'sms', 'whatsapp', 'notifications', 'alerts'];
    const isCommunicationTab = communicationTabs.includes(tabName);
    const resolvedCommunicationTab = (tabName === 'notifications' || tabName === 'alerts') ? 'alerts' : tabName;
    const targetTabId = isCommunicationTab ? 'communications' : tabName;

    if (!document.getElementById(targetTabId + '-tab')) {
        tabName = 'clinic';
    }

    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.add('hidden');
    });

    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('is-active');
    });

    const targetTab = document.getElementById((communicationTabs.includes(tabName) ? 'communications' : tabName) + '-tab');
    if (targetTab) {
        targetTab.classList.remove('hidden');
    }

    const activeButton = document.getElementById('tab-' + tabName);
    if (activeButton) {
        activeButton.classList.add('is-active');
    }

    const tabNames = {
        clinic: settingsI18n.tabClinic,
        subscription: settingsI18n.tabSubscription,
        system: settingsI18n.tabSystem,
        hours: settingsI18n.tabHours,
        kiosk: settingsI18n.tabKiosk,
        backup: settingsI18n.tabBackup,
        communications: settingsI18n.tabCommunications,
        channels: settingsI18n.tabChannels,
        email: settingsI18n.tabEmail,
        sms: settingsI18n.tabSms,
        whatsapp: settingsI18n.tabWhatsapp,
        alerts: settingsI18n.tabAlerts,
    };
    const breadcrumbTab = document.getElementById('breadcrumb-tab');
    const sep2 = document.getElementById('breadcrumb-sep-2');
    if (breadcrumbTab) {
        breadcrumbTab.textContent = tabNames[tabName] || '';
    }
    if (sep2) {
        sep2.style.display = tabNames[tabName] ? '' : 'none';
    }

    if (communicationTabs.includes(tabName)) {
        showSubTab(resolvedCommunicationTab, { updateUrl: false });
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    url.searchParams.delete('subtab');
    history.pushState(null, '', url.toString());
}

function showSubTab(name, options = {}) {
    const normalizedName = (name === 'notifications' || name === 'alerts') ? 'alerts' : name;
    document.querySelectorAll('.sub-tab-panel').forEach(panel => panel.classList.add('hidden'));
    document.querySelectorAll('.subtab-btn').forEach(btn => {
        btn.classList.remove('bg-blue-50', 'text-blue-600', 'border-b-2', 'border-blue-600');
        btn.classList.add('text-gray-500', 'hover:text-gray-700');
    });

    document.getElementById('sub-' + normalizedName)?.classList.remove('hidden');
    const activeBtn = document.getElementById('subtab-' + normalizedName);
    if (activeBtn) {
        activeBtn.classList.add('bg-blue-50', 'text-blue-600', 'border-b-2', 'border-blue-600');
        activeBtn.classList.remove('text-gray-500', 'hover:text-gray-700');
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', normalizedName);
    url.searchParams.delete('subtab');
    history.pushState(null, '', url.toString());
}

function toggleChannel(clinicId, type, enabled, buttonElement = null) {
    fetch('<?= base_url('settings/updateChannelStatus') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken },
        body: `clinic_id=${clinicId}&channel_type=${type}&enabled=${enabled}`
    }).then(res => res.json()).then(data => {
        if (!data.success) {
            window.showToast?.('error', settingsI18n.toastError, data.message || settingsI18n.toastOperationFailedRetry);
            return;
        }

        const isNowEnabled = enabled === 1;
        if (buttonElement) {
            buttonElement.className = `w-10 h-6 rounded-full transition-all ${isNowEnabled ? 'bg-green-500' : 'bg-gray-300'}`;
            const knob = buttonElement.querySelector('div');
            if (knob) {
                knob.className = `w-4 h-4 bg-white rounded-full transition-all ml-1 ${isNowEnabled ? 'translate-x-4' : ''}`;
            }
            buttonElement.setAttribute('onclick', `toggleChannel(${clinicId}, '${type}', ${isNowEnabled ? 0 : 1}, this)`);
        }
        window.showToast?.('success', settingsI18n.toastUpdated, settingsI18n.toastChannelUpdated);
    }).catch(() => {
        window.showToast?.('error', settingsI18n.toastError, settingsI18n.toastOperationFailedRetry);
    });
}

function saveEmailConfig(event) {
    const btn = event?.currentTarget || null;
    setButtonLoading(btn, true);
    const config = { smtp_host: document.getElementById('smtp_host').value, smtp_port: document.getElementById('smtp_port').value, smtp_crypto: document.getElementById('smtp_crypto').value, smtp_user: document.getElementById('smtp_user').value };
    const pass = document.getElementById('smtp_pass').value; if (pass) config.smtp_pass = pass;
    fetch('<?= base_url('settings/updateChannelConfig') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken }, body: `channel_type=email&config_json=${encodeURIComponent(JSON.stringify(config))}&action=save_config` })
    .then(res => res.json()).then(data => { if(data.success) window.showToast?.('success', settingsI18n.toastSaved, settingsI18n.toastSmtpUpdated); else window.showToast?.('error', settingsI18n.toastError, data.message || settingsI18n.toastSomethingWrong); })
    .finally(() => setButtonLoading(btn, false));
}

function saveSmsConfig(event) {
    const btn = event?.currentTarget || null;
    setButtonLoading(btn, true);
    const config = {
        provider: document.getElementById('sms_provider')?.value || '',
        sender_id: document.getElementById('sms_sender_id')?.value || '',
        base_url: document.getElementById('sms_base_url')?.value || '',
    };
    const apiKey = document.getElementById('sms_api_key')?.value || '';
    if (apiKey) {
        config.api_key = apiKey;
    }

    fetch('<?= base_url('settings/updateChannelConfig') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken }, body: `channel_type=sms&config_json=${encodeURIComponent(JSON.stringify(config))}&action=save_config` })
    .then(res => res.json()).then(data => { if(data.success) window.showToast?.('success', settingsI18n.toastSaved, settingsI18n.toastSmsUpdated); else window.showToast?.('error', settingsI18n.toastError, data.message || settingsI18n.toastSomethingWrong); })
    .finally(() => setButtonLoading(btn, false));
}

function copyKioskLink() {
    const input = document.getElementById('kioskLinkInput');
    if (!input || !input.value) {
        window.showToast?.('error', settingsI18n.toastError, settingsI18n.toastKioskLinkNotAvailable);
        return;
    }

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(input.value)
            .then(() => window.showToast?.('success', settingsI18n.toastSaved, settingsI18n.toastKioskLinkCopied))
            .catch(() => window.showToast?.('error', settingsI18n.toastError, settingsI18n.toastCopyKioskLinkFailed));
        return;
    }

    input.focus();
    input.select();
    document.execCommand('copy');
    window.getSelection()?.removeAllRanges();
    window.showToast?.('success', settingsI18n.toastSaved, settingsI18n.toastKioskLinkCopied);
}

function testWhatsAppChannel() {
    fetch('<?= base_url('settings/testWhatsAppChannel') ?>', { method: 'POST', headers: { [csrfHeader]: csrfToken, 'X-Requested-With': 'XMLHttpRequest' } })
    .then(res => res.json()).then(data => {
        if (data.success) {
            window.showToast?.('success', settingsI18n.toastTestSent, data.message || '');
            return;
        }
        window.showToast?.('error', settingsI18n.toastTestFailed, data.message || settingsI18n.toastSomethingWrong);
    }).catch(() => {
        window.showToast?.('error', settingsI18n.toastTestFailed, settingsI18n.toastSomethingWrong);
    });
}


function openConfirmModal(options = {}) {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    const title = document.getElementById('confirm-modal-title');
    const message = document.getElementById('confirm-modal-message');
    const confirmBtn = document.getElementById('confirm-modal-confirm-btn');
    const iconWrap = document.getElementById('confirm-modal-icon');
    const icon = iconWrap?.querySelector('i');

    if (title) title.textContent = options.title || settingsI18n.confirmAreYouSure;
    if (message) message.textContent = options.message || settingsI18n.actionCannotBeUndone;
    if (confirmBtn) confirmBtn.textContent = options.confirmText || settingsI18n.confirmText;
    if (iconWrap) iconWrap.className = 'w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0';
    if (icon) icon.className = 'fas fa-exclamation-triangle text-red-600 text-xl';

    confirmModalAction = typeof options.onConfirm === 'function' ? options.onConfirm : null;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    confirmModalAction = null;
}

function confirmModalProceed() {
    const action = confirmModalAction;
    closeConfirmModal();
    if (typeof action === 'function') {
        action();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const restoreSavedScrollPosition = () => {
        try {
            const raw = sessionStorage.getItem(settingsScrollStorageKey);
            if (!raw) {
                return;
            }
            const saved = JSON.parse(raw);
            const currentTab = new URLSearchParams(window.location.search).get('tab') || 'clinic';
            if (saved?.path === window.location.pathname && saved?.tab === currentTab && Number.isFinite(saved?.scrollY)) {
                window.scrollTo({ top: saved.scrollY, behavior: 'auto' });
            }
            sessionStorage.removeItem(settingsScrollStorageKey);
        } catch (error) {
            sessionStorage.removeItem(settingsScrollStorageKey);
        }
    };

    const persistSettingsScrollPosition = () => {
        try {
            const params = new URLSearchParams(window.location.search);
            sessionStorage.setItem(settingsScrollStorageKey, JSON.stringify({
                path: window.location.pathname,
                tab: params.get('tab') || 'clinic',
                scrollY: window.scrollY || window.pageYOffset || 0,
            }));
        } catch (error) {
            // Ignore storage errors and continue submit flow.
        }
    };

    document.querySelectorAll('.settings-tab form').forEach((form) => {
        if (form.dataset.ajaxSubmit === 'true') {
            return;
        }

        form.addEventListener('submit', () => {
            persistSettingsScrollPosition();
        });
    });

    const confirmModal = document.getElementById('confirm-modal');
    if (confirmModal) {
        confirmModal.addEventListener('click', function(event) {
            if (event.target === confirmModal) {
                closeConfirmModal();
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeConfirmModal();
        }
    });

    document.querySelectorAll('input, select, textarea').forEach((field) => {
        field.addEventListener('input', () => {
            if (field.classList.contains('border-red-400')) {
                setFieldError(field, '');
            }
            clearSettingsSummary();
        });
        field.addEventListener('change', () => {
            if (field.classList.contains('border-red-400')) {
                setFieldError(field, '');
            }
            clearSettingsSummary();
        });
    });

    const kioskToggle = document.querySelector('.kiosk-toggle');
    if (kioskToggle) {
        const kioskForm = kioskToggle.closest('form');
        kioskToggle.addEventListener('change', function() {
            if (!kioskForm) {
                return;
            }

            const formData = new FormData(kioskForm);
            formData.set('kiosk_enabled', kioskToggle.checked ? '1' : '0');

            fetch(kioskForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(async (response) => {
                const data = await response.json().catch(() => null);
                if (response.ok) {
                    window.showToast?.('success', settingsI18n.toastUpdated, data?.message || '');
                    return;
                }

                kioskToggle.checked = !kioskToggle.checked;
                window.showToast?.('error', settingsI18n.toastError, data?.message || settingsI18n.toastKioskStatusFailed);
            }).catch(() => {
                kioskToggle.checked = !kioskToggle.checked;
                window.showToast?.('error', settingsI18n.toastError, settingsI18n.toastKioskStatusFailed);
            });
        });
    }

    // Keep native selects to preserve built-in required/validity UX for settings forms.
    const smsConfig = <?= json_encode($sms_config ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const smsProvider = document.getElementById('sms_provider');
    const smsSenderId = document.getElementById('sms_sender_id');
    const smsBaseUrl = document.getElementById('sms_base_url');
    const smsBaseUrlWrap = document.getElementById('sms_base_url_wrap');
    if (smsProvider && smsSenderId && smsBaseUrl && smsBaseUrlWrap) {
        if (smsConfig.provider) smsProvider.value = smsConfig.provider;
        if (smsConfig.sender_id) smsSenderId.value = smsConfig.sender_id;
        if (smsConfig.base_url) smsBaseUrl.value = smsConfig.base_url;

        const syncSmsBaseUrlVisibility = () => {
            const isCustom = smsProvider.value === 'custom';
            smsBaseUrlWrap.classList.toggle('hidden', !isCustom);
            smsBaseUrl.disabled = !isCustom;
        };
        syncSmsBaseUrlVisibility();
        smsProvider.addEventListener('change', syncSmsBaseUrlVisibility);
    }

    const clinicEmail = document.querySelector('input[name="clinic_email"]');
    clinicEmail?.addEventListener('blur', () => validateField(clinicEmail, { required: true, email: true }));

    const clinicWebsite = document.querySelector('input[name="clinic_website"]');
    clinicWebsite?.addEventListener('blur', () => validateField(clinicWebsite, { url: true }));

    const clinicName = document.querySelector('input[name="clinic_name"]');
    clinicName?.addEventListener('blur', () => validateField(clinicName, { required: true, minLength: 2 }));

    const smtpHost = document.getElementById('smtp_host');
    smtpHost?.addEventListener('blur', () => validateField(smtpHost, { required: true }));

    const smtpPort = document.getElementById('smtp_port');
    smtpPort?.addEventListener('blur', () => validateField(smtpPort, { required: true, port: true }));

    const smtpUser = document.getElementById('smtp_user');
    smtpUser?.addEventListener('blur', () => validateField(smtpUser, { email: true }));

    const kioskPin = document.querySelector('input[name="kiosk_pin"]');
    kioskPin?.addEventListener('blur', () => validateField(kioskPin, { minLength: 4, maxLength: 8 }));

    const kioskPinConfirm = document.querySelector('input[name="kiosk_pin_confirm"]');
    kioskPinConfirm?.addEventListener('blur', () => {
        const kioskPinValue = (kioskPin?.value || '').trim();
        const confirmValue = (kioskPinConfirm.value || '').trim();
        if (!confirmValue) {
            setFieldError(kioskPinConfirm, '');
            return;
        }
        if (kioskPinValue && confirmValue !== kioskPinValue) {
            setFieldError(kioskPinConfirm, settingsI18n.pinMismatch);
            return;
        }
        validateField(kioskPinConfirm, { minLength: 4, maxLength: 8 });
    });

    const params = new URLSearchParams(window.location.search);
    const requestedTab = params.get('tab');
    const requestedSubTab = params.get('subtab');
    const fallbackTab = '<?= $active_tab ?? 'clinic' ?>';
    let initialTab = requestedTab || fallbackTab;
    if (requestedTab === 'communications') {
        initialTab = requestedSubTab || 'channels';
    }
    resetSettingsLoadingButtons();
    showSettingsTab(initialTab, { updateUrl: false });
    restoreSavedScrollPosition();
});

window.addEventListener('popstate', function() {
    const params = new URLSearchParams(window.location.search);
    const requestedTab = params.get('tab');
    let targetTab = requestedTab || 'clinic';
    if (requestedTab === 'communications') {
        targetTab = params.get('subtab') || 'channels';
    }
    showSettingsTab(targetTab, { updateUrl: false });
});

window.addEventListener('pageshow', function() {
    resetSettingsLoadingButtons();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?= $this->endSection() ?>

