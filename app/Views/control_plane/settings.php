<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<style>
    .control-settings-menu-btn {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
        transition: all 0.2s ease;
        text-align: left;
    }
    .control-settings-menu-btn:hover {
        background: rgba(59, 130, 246, 0.08);
        color: #0f172a;
    }
    .control-settings-menu-btn i {
        margin-right: 0.75rem;
        color: #94a3b8;
        transition: color 0.2s ease;
    }
    .control-settings-menu-btn.is-active {
        background: linear-gradient(135deg, #3b82f6, #4f46e5);
        color: #fff;
        box-shadow: 0 18px 38px rgba(59, 130, 246, 0.18);
    }
    .control-settings-menu-btn.is-active i {
        color: rgba(255, 255, 255, 0.82);
    }
    .control-settings-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.45);
        border-radius: 1rem;
        box-shadow: 0 18px 40px rgba(59, 130, 246, 0.10);
        overflow: hidden;
    }
    .control-settings-header {
        background: linear-gradient(90deg, #334155, #0f172a);
        padding: 2rem;
    }
    .control-settings-header h3,
    .control-settings-header h4 {
        color: #fff !important;
    }
    .control-settings-header p {
        color: rgba(226, 232, 240, 0.92) !important;
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
    .control-settings-subtab {
        padding: 0.625rem 1rem;
        border-bottom: 2px solid transparent;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        white-space: nowrap;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        transition: all 0.2s ease;
    }
    .control-settings-subtab:hover {
        color: #334155;
    }
    .control-settings-sections {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .control-settings-section-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(148, 163, 184, 0.18);
        color: #334155;
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        transition: all 0.2s ease;
    }
    .control-settings-section-link:hover {
        background: #fff;
        color: #1d4ed8;
        border-color: rgba(59, 130, 246, 0.24);
    }
    .control-settings-section-link i {
        color: #3b82f6;
        font-size: 0.75rem;
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
</style>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-gray-600/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-zinc-600/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-6 py-8">
        <div class="mb-8">
            <div class="bg-white/90 backdrop-blur-xl border border-white/40 rounded-2xl p-6 shadow-xl shadow-blue-500/10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Global Configuration</h1>
                            <p class="text-gray-600">Control plane policies and system-wide defaults</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Live Settings</span>
                    </div>
                </div>
            </div>
        </div>

        <nav class="mb-4 flex items-center space-x-1 text-sm text-gray-500" id="control-settings-breadcrumb">
            <span class="text-gray-700 font-medium">Control Plane</span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium">Settings</span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span id="control-breadcrumb-tab" class="text-gray-900 font-semibold"></span>
        </nav>
        <div id="control-settings-sections" class="control-settings-sections"></div>

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

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-72 flex-shrink-0">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/50 p-6 sticky top-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Settings Menu</h3>
                    <nav class="space-y-2">
                        <button onclick="showTab('platform')" id="nav-platform" class="control-settings-menu-btn is-active">
                            <i class="fas fa-id-badge w-5 h-5"></i>
                            Platform Profile
                        </button>
                        <button onclick="showTab('clinic-defaults')" id="nav-clinic-defaults" class="control-settings-menu-btn">
                            <i class="fas fa-clinic-medical w-5 h-5"></i>
                            Clinic Defaults
                        </button>
                        <button onclick="showTab('governance')" id="nav-governance" class="control-settings-menu-btn">
                            <i class="fas fa-shield-alt w-5 h-5"></i>
                            Governance
                        </button>
                        <div class="pt-4 pb-2 border-t border-slate-100 mt-4">
                            <h4 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Communications</h4>
                        </div>
                        <button onclick="showTab('communications')" id="nav-communications" class="control-settings-menu-btn">
                            <i class="fas fa-satellite-dish w-5 h-5"></i>
                            Communications
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
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
        'clinic-defaults': 'Clinic Defaults',
        governance: 'Governance',
        communications: 'Communications',
    };
    if (!document.getElementById('tab-' + tabName)) {
        tabName = 'platform';
    }

    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));

    const target = document.getElementById('tab-' + tabName);
    if (target) {
        target.classList.remove('hidden');
    }

    if (tabName === 'communications') {
        const subtab = new URLSearchParams(window.location.search).get('subtab') || 'email-templates';
        showCommsSubTab(subtab, { updateUrl: false });
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

    renderSectionLinks();

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    if (tabName !== 'communications') {
        url.searchParams.delete('subtab');
    }
    history.pushState(null, '', url.toString());
}

function showCommsSubTab(name, options = {}) {
    document.querySelectorAll('.comms-panel').forEach(panel => panel.classList.add('hidden'));
    document.querySelectorAll('.comms-subtab').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600', 'bg-blue-50');
        btn.classList.add('border-transparent', 'text-slate-500');
    });

    const panel = document.getElementById('comms-panel-' + name);
    if (panel) panel.classList.remove('hidden');
    const btn = document.getElementById('comms-subtab-' + name);
    if (btn) {
        btn.classList.add('border-blue-600', 'text-blue-600', 'bg-blue-50');
        btn.classList.remove('border-transparent', 'text-slate-500');
    }

    renderSectionLinks();

    if (options.updateUrl === false) {
        return;
    }
    const url = new URL(window.location.href);
    url.searchParams.set('tab', 'communications');
    url.searchParams.set('subtab', name);
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

function renderSectionLinks() {
    const container = document.getElementById('control-settings-sections');
    if (!container) return;

    const visibleTab = document.querySelector('.settings-content:not(.hidden)');
    if (!visibleTab) {
        container.innerHTML = '';
        return;
    }

    const sections = Array.from(visibleTab.querySelectorAll('[data-section-title]'))
        .filter((section) => {
            if (section.classList.contains('hidden')) {
                return false;
            }

            if (!section.id || !section.dataset.sectionTitle) {
                return false;
            }

            return section.offsetParent !== null;
        });

    container.innerHTML = sections.map((section) => {
        const title = section.dataset.sectionTitle || '';
        const id = section.id || '';
        return `<a href="#${id}" class="control-settings-section-link"><i class="fas fa-link"></i><span>${title}</span></a>`;
    }).join('');
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
    if (urlTab && document.getElementById(`tab-${urlTab}`)) {
        showTab(urlTab, { updateUrl: false });
    } else {
        showTab('platform', { updateUrl: false });
    }

    initRetentionControls();
    bindPruneActions();
    bindManagedForms();
    renderSectionLinks();

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
    const urlTab = new URLSearchParams(window.location.search).get('tab') || 'platform';
    showTab(urlTab, { updateUrl: false });
    renderSectionLinks();
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

