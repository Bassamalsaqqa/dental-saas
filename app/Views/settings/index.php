<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 56px !important;
        border: 2px solid #e5e7eb !important;
        border-radius: 12px !important;
        padding: 0 16px !important;
        background-color: rgba(249, 250, 251, 0.5) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 52px !important;
        padding-left: 0 !important;
        color: #374151 !important;
        font-weight: 500 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 52px !important;
        right: 16px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
    }
    .settings-tab.hidden { display: none !important; }
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
    .ui-toggle:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    [dir="rtl"] #settings-breadcrumb {
        direction: rtl;
    }
    [dir="rtl"] #settings-breadcrumb .fa-chevron-right::before {
        content: "\f053";
    }
    [dir="rtl"] .settings-menu-btn {
        text-align: right;
    }
    [dir="rtl"] .settings-menu-btn i {
        margin-right: 0 !important;
        margin-left: 0.75rem !important;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-cog text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?= lang('UI.systemSettings') ?></h1>
                    <p class="text-gray-600"><?= lang('UI.settingsConfigureSystem') ?></p>
                </div>
            </div>
        </div>

        <nav class="mb-4 flex items-center space-x-1 text-sm text-gray-500" id="settings-breadcrumb">
            <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors"><?= lang('UI.home') ?></a>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium"><?= lang('UI.menuSettings') ?></span>
            <i class="fas fa-chevron-right text-xs text-gray-300" id="breadcrumb-sep-2" style="display:none"></i>
            <span id="breadcrumb-tab" class="text-gray-900 font-semibold"></span>
            <i class="fas fa-chevron-right text-xs text-gray-300" id="breadcrumb-sep-3" style="display:none"></i>
            <span id="breadcrumb-subtab" class="text-gray-900 font-semibold"></span>
        </nav>

        <?php $settingsErrors = session('errors'); ?>
        <?php if (session('error') || (!empty($settingsErrors) && is_array($settingsErrors))): ?>
            <div id="settings-validation-summary" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-rose-600">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-bold">Please review the highlighted settings and try again.</p>
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
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Sidebar -->
            <div class="lg:w-80 flex-shrink-0">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 p-6 sticky top-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6"><?= lang('UI.settingsMenu') ?></h3>
                    <nav class="space-y-2">
                        <button onclick="showSettingsTab('clinic')" id="tab-clinic" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-building w-5 h-5 mr-3"></i> <?= lang('UI.settingsClinicProfile') ?>
                        </button>
                        <button onclick="showSettingsTab('subscription')" id="tab-subscription" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-layer-group w-5 h-5 mr-3"></i> <?= lang('UI.settingsSubscription') ?>
                        </button>
                        <button onclick="showSettingsTab('system')" id="tab-system" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-cog w-5 h-5 mr-3"></i> <?= lang('UI.settingsSystemTab') ?>
                        </button>
                        <button onclick="showSettingsTab('hours')" id="tab-hours" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-clock w-5 h-5 mr-3"></i> <?= lang('UI.settingsOperations') ?>
                        </button>
                        <button onclick="showSettingsTab('kiosk')" id="tab-kiosk" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-tablet-alt w-5 h-5 mr-3"></i> <?= lang('UI.settingsKiosk') ?>
                        </button>
                        <button onclick="showSettingsTab('backup')" id="tab-backup" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-database w-5 h-5 mr-3"></i> <?= lang('UI.settingsBackupRestore') ?>
                        </button>
                        
                        <div class="pt-4 pb-2 border-t border-gray-100 mt-4">
                            <h4 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2"><?= lang('UI.settingsCommunications') ?></h4>
                            <button onclick="showSettingsTab('communications')" id="tab-communications" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                                <i class="fas fa-satellite-dish w-5 h-5 mr-3"></i> <?= lang('UI.settingsCommunications') ?>
                            </button>
                        </div>

                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
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

<div id="confirm-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <div class="flex items-start space-x-4 mb-6">
            <div id="confirm-modal-icon" class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-xl font-bold text-gray-900 mb-1"><?= lang('UI.confirmAreYouSure') ?></h3>
                <p id="confirm-modal-message" class="text-gray-500 text-sm"><?= lang('UI.confirmCannotUndo') ?></p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeConfirmModal()" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all">
                <?= lang('UI.cancel') ?>
            </button>
            <button id="confirm-modal-confirm-btn" type="button" class="px-6 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-all" onclick="confirmModalProceed()">
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
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
    }
}

function submitSystemSettings(btn) {
    const form = document.getElementById('system-settings-form');
    if (!form) {
        return;
    }
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    setButtonLoading(btn, true);
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
    const isLegacyCommunicationTab = communicationTabs.includes(tabName);
    let communicationSubTab = null;
    if (isLegacyCommunicationTab) {
        communicationSubTab = (tabName === 'notifications' || tabName === 'alerts') ? 'alerts' : tabName;
        tabName = 'communications';
    }

    if (!document.getElementById(tabName + '-tab')) {
        tabName = 'clinic';
    }

    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.add('hidden');
    });

    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
        btn.classList.add('text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
    });

    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.remove('hidden');
    }

    const activeButton = document.getElementById('tab-' + tabName);
    if (activeButton) {
        activeButton.classList.remove('text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
        activeButton.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
    }

    const tabNames = {
        clinic: settingsI18n.tabClinic,
        subscription: settingsI18n.tabSubscription,
        system: settingsI18n.tabSystem,
        hours: settingsI18n.tabHours,
        kiosk: settingsI18n.tabKiosk,
        backup: settingsI18n.tabBackup,
        communications: settingsI18n.tabCommunications,
    };
    const breadcrumbTab = document.getElementById('breadcrumb-tab');
    const breadcrumbSubTab = document.getElementById('breadcrumb-subtab');
    const sep2 = document.getElementById('breadcrumb-sep-2');
    const sep3 = document.getElementById('breadcrumb-sep-3');
    if (breadcrumbTab) {
        breadcrumbTab.textContent = tabNames[tabName] || '';
    }
    if (sep2) {
        sep2.style.display = tabNames[tabName] ? '' : 'none';
    }
    if (tabName !== 'communications') {
        if (breadcrumbSubTab) breadcrumbSubTab.textContent = '';
        if (sep3) sep3.style.display = 'none';
    }

    if (tabName === 'communications') {
        const params = new URLSearchParams(window.location.search);
        const requestedSubtab = communicationSubTab || params.get('subtab') || 'channels';
        showSubTab(requestedSubtab, { updateUrl: false });
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    if (tabName === 'communications') {
        url.searchParams.set('tab', 'communications');
        url.searchParams.set('subtab', communicationSubTab || url.searchParams.get('subtab') || 'channels');
    } else {
        url.searchParams.set('tab', tabName);
        url.searchParams.delete('subtab');
    }
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

    const subNames = {
        channels: settingsI18n.subChannels,
        email: settingsI18n.subEmail,
        sms: settingsI18n.subSms,
        whatsapp: settingsI18n.subWhatsapp,
        alerts: settingsI18n.subAlerts,
    };
    const breadcrumbSubTab = document.getElementById('breadcrumb-subtab');
    const sep3 = document.getElementById('breadcrumb-sep-3');
    if (breadcrumbSubTab) {
        breadcrumbSubTab.textContent = subNames[normalizedName] || '';
    }
    if (sep3) {
        sep3.style.display = subNames[normalizedName] ? '' : 'none';
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', 'communications');
    url.searchParams.set('subtab', normalizedName);
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

function saveWhatsAppConfig(event) {
    const btn = event?.currentTarget || null;
    setButtonLoading(btn, true);
    const messageTemplates = {
        appointment_reminder: document.getElementById('tmpl_appointment_reminder').value,
        appointment_confirmation: document.getElementById('tmpl_appointment_confirmation').value,
        follow_up: document.getElementById('tmpl_follow_up').value,
        no_show_follow_up: document.getElementById('tmpl_no_show_follow_up').value,
        payment_reminder: document.getElementById('tmpl_payment_reminder').value
    };
    const config = { phone_number_id: document.getElementById('wa_phone_number_id').value, message_templates: messageTemplates };
    fetch('<?= base_url('settings/updateChannelConfig') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken }, body: `channel_type=whatsapp&config_json=${encodeURIComponent(JSON.stringify(config))}&action=save_config` })
    .then(res => res.json()).then(data => { if(data.success) window.showToast?.('success', settingsI18n.toastSaved, data.message || ''); else window.showToast?.('error', settingsI18n.toastError, data.message || settingsI18n.toastSomethingWrong); })
    .finally(() => setButtonLoading(btn, false));
}

function resetTemplatesToDefault() {
    const defaults = <?= json_encode([
        'appointment_reminder' => json_decode('"\u062a\u0630\u0643\u064a\u0631 \u0645\u0646 \u0627\u0644\u0639\u064a\u0627\u062f\u0629: \u0644\u062f\u064a\u0643\u0645 \u0645\u0648\u0639\u062f \u0628\u062a\u0627\u0631\u064a\u062e {{appointment_date}} \u0627\u0644\u0633\u0627\u0639\u0629 {{appointment_time}}. \u0644\u0644\u0645\u062a\u0627\u0628\u0639\u0629 \u064a\u0631\u062c\u0649 \u0627\u0644\u0631\u062f \u0639\u0644\u0649 \u0647\u0630\u0647 \u0627\u0644\u0631\u0633\u0627\u0644\u0629."'),
        'appointment_confirmation' => json_decode('"\u062a\u0645 \u062a\u0623\u0643\u064a\u062f \u0645\u0648\u0639\u062f\u0643\u0645 \u0628\u062a\u0627\u0631\u064a\u062e {{appointment_date}} \u0627\u0644\u0633\u0627\u0639\u0629 {{appointment_time}}. \u0646\u062a\u0637\u0644\u0639 \u0644\u062e\u062f\u0645\u062a\u0643\u0645."'),
        'follow_up' => json_decode('"\u0646\u0623\u0645\u0644 \u0623\u0646\u0643\u0645 \u0628\u062e\u064a\u0631. \u0647\u0630\u0647 \u0645\u062a\u0627\u0628\u0639\u0629 \u0645\u0646 \u0627\u0644\u0639\u064a\u0627\u062f\u0629 \u0628\u062e\u0635\u0648\u0635 \u0632\u064a\u0627\u0631\u062a\u0643\u0645 \u0627\u0644\u0623\u062e\u064a\u0631\u0629. \u0644\u0623\u064a \u0627\u0633\u062a\u0641\u0633\u0627\u0631 \u064a\u0631\u062c\u0649 \u0627\u0644\u0631\u062f \u0639\u0644\u0649 \u0647\u0630\u0647 \u0627\u0644\u0631\u0633\u0627\u0644\u0629."'),
        'no_show_follow_up' => json_decode('"\u0644\u0627\u062d\u0638\u0646\u0627 \u0623\u0646\u0643\u0645 \u0641\u0627\u062a\u0643\u0645 \u0645\u0648\u0639\u062f \u0627\u0644\u064a\u0648\u0645. \u0625\u0630\u0627 \u0631\u063a\u0628\u062a\u0645 \u0628\u0625\u0639\u0627\u062f\u0629 \u0627\u0644\u062c\u062f\u0648\u0644\u0629 \u064a\u0631\u062c\u0649 \u0627\u0644\u0631\u062f \u0639\u0644\u0649 \u0647\u0630\u0647 \u0627\u0644\u0631\u0633\u0627\u0644\u0629 \u0623\u0648 \u0627\u0644\u062a\u0648\u0627\u0635\u0644 \u0645\u0639\u0646\u0627 \u0644\u062d\u062c\u0632 \u0645\u0648\u0639\u062f \u062c\u062f\u064a\u062f."'),
        'payment_reminder' => json_decode('"\u062a\u0630\u0643\u064a\u0631 \u0645\u0646 \u0627\u0644\u0639\u064a\u0627\u062f\u0629: \u064a\u0648\u062c\u062f \u0631\u0635\u064a\u062f \u0645\u0633\u062a\u062d\u0642 \u0628\u0642\u064a\u0645\u0629 {{amount}}. \u0644\u0644\u062a\u0641\u0627\u0635\u064a\u0644 \u064a\u0631\u062c\u0649 \u0627\u0644\u062a\u0648\u0627\u0635\u0644 \u0645\u0639\u0646\u0627 \u0639\u0628\u0631 \u0647\u0630\u0647 \u0627\u0644\u0645\u062d\u0627\u062f\u062b\u0629."'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    Object.entries(defaults).forEach(([k, v]) => {
        const el = document.getElementById('tmpl_' + k);
        if (el) {
            el.value = v;
        }
    });
    window.showToast?.('success', settingsI18n.toastUpdated, settingsI18n.toastTemplatesDefaultLoaded);
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

function downloadBackup(filename) { window.location.href = `<?= base_url('settings/download-backup/') ?>${filename}`; }
function restoreBackup(filename) {
    openConfirmModal({
        title: settingsI18n.confirmRestoreBackupTitle,
        message: settingsI18n.confirmRestoreBackupMessage,
        confirmText: settingsI18n.confirmRestore,
        onConfirm: () => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('settings/restore') ?>';
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = csrfHeader;
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'backup_file';
            input.value = filename;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        },
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
    if (requestedTab === 'communications' || ['channels', 'email', 'sms', 'whatsapp', 'notifications', 'alerts'].includes(initialTab)) {
        initialTab = 'communications';
    }
    showSettingsTab(initialTab, { updateUrl: false });
    if (initialTab === 'communications') {
        showSubTab(requestedSubTab || (fallbackTab === 'notifications' ? 'alerts' : fallbackTab) || 'channels', { updateUrl: false });
    }
    restoreSavedScrollPosition();
});

window.addEventListener('popstate', function() {
    const params = new URLSearchParams(window.location.search);
    const requestedTab = params.get('tab');
    const requestedSubTab = params.get('subtab');
    let targetTab = requestedTab || 'clinic';
    if (requestedTab === 'communications') {
        targetTab = 'communications';
    }
    showSettingsTab(targetTab, { updateUrl: false });
    if (targetTab === 'communications') {
        showSubTab(requestedSubTab || 'channels', { updateUrl: false });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?= $this->endSection() ?>

