<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<style>
    .control-settings-menu-btn {
        display: flex;
        align-items: center;
        width: 100%;
        gap: 0.75rem;
        padding: 0.85rem 0.95rem;
        border-radius: 0.9rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        transition: all 0.2s ease;
        text-align: left;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .control-settings-menu-btn:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .control-settings-menu-btn i {
        color: #64748b;
        transition: color 0.2s ease;
        margin-top: 0.15rem;
    }
    .control-settings-menu-btn.is-active {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
        box-shadow: none;
    }
    .control-settings-menu-btn.is-active i {
        color: #2563eb;
    }
    .control-settings-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .control-settings-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
    }
    .control-settings-header h3,
    .control-settings-header h4 {
        color: #0f172a !important;
    }
    .control-settings-header p {
        color: #64748b !important;
    }
    .control-settings-field {
        width: 100%;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        color: #334155;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .control-settings-field:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.10);
    }
    .control-settings-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #3b82f6, #4f46e5);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 14px 32px rgba(59, 130, 246, 0.20);
        transition: all 0.2s ease;
    }
    .control-settings-submit:hover {
        filter: brightness(0.97);
    }
    .control-settings-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .control-settings-secondary:hover {
        background: #f8fafc;
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
        margin-left: 0 !important;
    }
</style>
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-8">
        <div class="mb-5">
            <div class="flex items-center space-x-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                    <i class="fas fa-sliders-h text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-slate-950">Settings</h1>
                    <p class="mt-1 text-sm text-slate-600">Platform-wide defaults, governance, and shared communication settings.</p>
                </div>
            </div>
        </div>

        <nav class="mb-5 flex items-center space-x-1 text-sm text-gray-500" id="control-settings-breadcrumb">
            <span class="text-gray-700 font-medium">Control Plane</span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium">Settings</span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span id="control-breadcrumb-tab" class="text-gray-900 font-semibold"></span>
        </nav>
        <?php $controlSettingsErrors = session('errors'); ?>
        <?php if (session('error') || (!empty($controlSettingsErrors) && is_array($controlSettingsErrors))): ?>
            <div id="control-settings-feedback" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-rose-600">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-bold">Please review the highlighted settings and try again.</p>
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

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[220px,minmax(0,1fr)] items-start">
            <div class="xl:sticky xl:top-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 px-2">
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Control Settings</p>
                    </div>
                    <nav class="space-y-2">
                        <button onclick="showTab('platform')" id="nav-platform" class="control-settings-menu-btn is-active">
                            <i class="fas fa-id-badge w-5 h-5"></i>
                            <span>Platform Profile</span>
                        </button>
                        <button onclick="showTab('system-preferences')" id="nav-system-preferences" class="control-settings-menu-btn">
                            <i class="fas fa-clinic-medical w-5 h-5"></i>
                            <span>System Preferences</span>
                        </button>
                        <button onclick="showTab('working-hours')" id="nav-working-hours" class="control-settings-menu-btn">
                            <i class="fas fa-clock w-5 h-5"></i>
                            <span>Working Hours</span>
                        </button>
                        <button onclick="showTab('governance')" id="nav-governance" class="control-settings-menu-btn">
                            <i class="fas fa-shield-alt w-5 h-5"></i>
                            <span>Governance</span>
                        </button>
                        <button onclick="showTab('email-templates')" id="nav-email-templates" class="control-settings-menu-btn">
                            <i class="fas fa-envelope-open-text w-5 h-5"></i>
                            <span>Email Templates</span>
                        </button>
                        <button onclick="showTab('whatsapp-templates')" id="nav-whatsapp-templates" class="control-settings-menu-btn">
                            <i class="fab fa-whatsapp w-5 h-5"></i>
                            <span>WhatsApp Templates</span>
                        </button>
                    </nav>
                </div>
            </div>

            <div class="min-w-0">
                <?= $this->include('control_plane/settings_partials/platform') ?>
                <?= $this->include('control_plane/settings_partials/clinic_defaults') ?>
                <?= $this->include('control_plane/settings_partials/governance') ?>
                <?= $this->include('control_plane/settings_partials/communications') ?>
            </div>
        </div>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <div class="flex items-start space-x-4 mb-6">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-xl font-bold text-gray-900 mb-1">Are you sure?</h3>
                <p id="confirm-modal-message" class="text-gray-500 text-sm">This action cannot be undone.</p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeConfirmModal()"
                    class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all">
                Cancel
            </button>
            <button id="confirm-modal-action"
                    class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl shadow-lg transition-all">
                Confirm
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
        container.className = 'fixed top-5 right-5 z-[100] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-2xl transition-all';
        document.body.appendChild(container);
    }

    container.className = `fixed top-5 right-5 z-[100] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-2xl transition-all ${
        type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'
    }`;
    container.textContent = message;
    clearTimeout(container._hideTimer);
    container._hideTimer = setTimeout(() => {
        container.remove();
    }, 3500);
}

function setButtonLoading(button, loading, label = 'Saving...') {
    if (!button) return;

    if (loading) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${label}`;
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        return;
    }

    button.innerHTML = button.dataset.originalText || button.innerHTML;
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

