<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-zinc-100 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-gray-600/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-zinc-600/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-700 to-gray-800 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Global Configuration</h1>
                            <p class="text-gray-600">Control plane policies and system-wide defaults</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Live Settings</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-72 flex-shrink-0">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 p-4 sticky top-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 px-2">Settings Menu</h3>
                    <nav class="space-y-1">
                        <button onclick="showTab('platform')" id="nav-platform" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 bg-gradient-to-r from-slate-700 to-gray-800 text-white shadow-md">
                            <i class="fas fa-id-badge w-5 h-5 mr-3 text-slate-300"></i>
                            Platform Profile
                        </button>
                        <button onclick="showTab('governance')" id="nav-governance" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                            <i class="fas fa-shield-alt w-5 h-5 mr-3 text-slate-400"></i>
                            Governance
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
                <div id="tab-platform" class="settings-content">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-gray-800 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-id-badge text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Platform Profile</h3>
                                    <p class="text-slate-300 text-sm">Support identity used across control plane surfaces</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="<?= base_url('controlplane/settings/platform') ?>" method="POST" class="space-y-6">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Platform Name</label>
                                        <input type="text" name="platform_name" value="<?= esc($settings['platform_name'] ?? '') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Email</label>
                                        <input type="email" name="platform_support_email" value="<?= esc($settings['platform_support_email'] ?? '') ?>" placeholder="support@yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Phone</label>
                                        <input type="text" name="platform_support_phone" value="<?= esc($settings['platform_support_phone'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Primary)</label>
                                        <input type="text" name="platform_support_whatsapp_primary" value="<?= esc($settings['platform_support_whatsapp_primary'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Secondary)</label>
                                        <input type="text" name="platform_support_whatsapp_secondary" value="<?= esc($settings['platform_support_whatsapp_secondary'] ?? '') ?>" placeholder="+972 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Website</label>
                                        <input type="url" name="platform_support_website" value="<?= esc($settings['platform_support_website'] ?? '') ?>" placeholder="https://yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:from-slate-800 hover:to-gray-900 transition-all">
                                        <i class="fas fa-save mr-2"></i>Save Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="tab-governance" class="settings-content hidden">
                    <div class="space-y-8">
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-file-shield text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Export Retention Policy</h3>
                                        <p class="text-slate-300 text-sm">Global policy for export artifacts across all clinics</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8 space-y-6">
                                <form action="<?= base_url('controlplane/settings/retention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <?php $retentionMode = $settings['retention_mode'] ?? 'latest'; ?>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition-all <?= $retentionMode === 'latest' ? 'border-slate-700 bg-slate-50' : 'border-slate-200' ?>">
                                            <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= $retentionMode === 'latest' ? 'checked' : '' ?>>
                                            <span class="font-semibold text-slate-800">Keep Latest Only</span>
                                            <span class="text-xs text-slate-500 mt-1">Supersede previous exports for each purpose.</span>
                                        </label>
                                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition-all <?= $retentionMode === 'keep_last_n' ? 'border-slate-700 bg-slate-50' : 'border-slate-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= $retentionMode === 'keep_last_n' ? 'checked' : '' ?>>
                                            <span class="font-semibold text-slate-800">Keep Last N</span>
                                            <span class="text-xs text-slate-500 mt-1">Retain a fixed number of recent exports.</span>
                                        </label>
                                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition-all <?= $retentionMode === 'keep_x_days' ? 'border-slate-700 bg-slate-50' : 'border-slate-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= $retentionMode === 'keep_x_days' ? 'checked' : '' ?>>
                                            <span class="font-semibold text-slate-800">Keep for X Days</span>
                                            <span class="text-xs text-slate-500 mt-1">Delete exports older than the time window.</span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div id="retention-last-n" class="<?= $retentionMode === 'keep_last_n' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of exports to keep</label>
                                            <input type="number" name="retention_last_n" value="<?= esc((int) ($settings['retention_last_n'] ?? 5)) ?>" min="1" max="100" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        </div>
                                        <div id="retention-days" class="<?= $retentionMode === 'keep_x_days' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of days to keep</label>
                                            <input type="number" name="retention_days" value="<?= esc((int) ($settings['retention_days'] ?? 30)) ?>" min="1" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-exports-btn" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                                            <i class="fas fa-broom mr-2"></i>Prune Export Artifacts
                                        </button>
                                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:from-slate-800 hover:to-gray-900 transition-all">
                                            <i class="fas fa-save mr-2"></i>Save Retention Policy
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-user-shield text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Control Plane Audit Retention</h3>
                                        <p class="text-indigo-100 text-sm">Retention policy for operator activity logs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <form action="<?= base_url('controlplane/settings/audit-retention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Audit log retention window (days)</label>
                                        <input type="number" name="control_plane_audit_retention_days" value="<?= esc((int) ($settings['control_plane_audit_retention_days'] ?? 365)) ?>" min="30" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60 transition-all">
                                        <p class="text-xs text-slate-500 mt-2">Retention applies to control plane audit events only. Tenant audit policies remain unchanged.</p>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-audits-btn" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                                            <i class="fas fa-broom mr-2"></i>Prune Audit Logs
                                        </button>
                                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-semibold rounded-xl shadow-lg hover:from-indigo-700 hover:to-violet-700 transition-all">
                                            <i class="fas fa-save mr-2"></i>Save Audit Policy
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-sliders-h text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Tenant Defaults</h3>
                                        <p class="text-emerald-100 text-sm">Applied only when a new clinic is onboarded</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <form action="<?= base_url('controlplane/settings/tenant-defaults') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Default Timezone</label>
                                            <input type="text" name="default_timezone" value="<?= esc($settings['default_timezone'] ?? '') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Default Appointment Duration (minutes)</label>
                                            <input type="number" name="default_appointment_duration" value="<?= esc((int) ($settings['default_appointment_duration'] ?? 30)) ?>" min="5" max="480" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Default Date Format</label>
                                            <select name="default_date_format" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                                <?php $dateFormat = $settings['default_date_format'] ?? 'Y-m-d'; ?>
                                                <option value="Y-m-d" <?= $dateFormat === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                                <option value="m/d/Y" <?= $dateFormat === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                                <option value="d/m/Y" <?= $dateFormat === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Default Time Format</label>
                                            <select name="default_time_format" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                                <?php $timeFormat = $settings['default_time_format'] ?? '12'; ?>
                                                <option value="12" <?= $timeFormat === '12' ? 'selected' : '' ?>>12-hour</option>
                                                <option value="24" <?= $timeFormat === '24' ? 'selected' : '' ?>>24-hour</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Default Currency</label>
                                            <input type="text" name="default_currency" value="<?= esc($settings['default_currency'] ?? '') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Currency Position</label>
                                            <select name="default_currency_position" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                                <?php $currencyPosition = $settings['default_currency_position'] ?? 'before'; ?>
                                                <option value="before" <?= $currencyPosition === 'before' ? 'selected' : '' ?>>Before amount</option>
                                                <option value="after" <?= $currencyPosition === 'after' ? 'selected' : '' ?>>After amount</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Default Working Hours Start</label>
                                            <input type="time" name="default_working_hours_start" value="<?= esc($settings['default_working_hours_start'] ?? '09:00') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Default Working Hours End</label>
                                            <input type="time" name="default_working_hours_end" value="<?= esc($settings['default_working_hours_end'] ?? '17:00') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
                                        </div>
                                    </div>

                                    <?php $workingDefaults = $settings['default_working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']; ?>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Default Working Days</label>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <?php
                                                $dayLabels = [
                                                    'monday' => 'Monday',
                                                    'tuesday' => 'Tuesday',
                                                    'wednesday' => 'Wednesday',
                                                    'thursday' => 'Thursday',
                                                    'friday' => 'Friday',
                                                    'saturday' => 'Saturday',
                                                    'sunday' => 'Sunday',
                                                ];
                                            ?>
                                            <?php foreach ($dayLabels as $dayKey => $dayLabel): ?>
                                                <label class="flex items-center space-x-2 p-3 border border-slate-200 rounded-xl bg-slate-50">
                                                    <input type="checkbox" name="default_working_days[]" value="<?= esc($dayKey) ?>" class="h-4 w-4 text-emerald-600 border-slate-300 rounded" <?= in_array($dayKey, $workingDefaults, true) ? 'checked' : '' ?>>
                                                    <span class="text-sm font-medium text-slate-700"><?= esc($dayLabel) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:from-emerald-600 hover:to-teal-700 transition-all">
                                            <i class="fas fa-save mr-2"></i>Save Tenant Defaults
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
                            <div class="flex items-start justify-between flex-wrap gap-6">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">Feature Governance</h4>
                                    <p class="text-sm text-slate-600 mt-2 max-w-2xl">Feature availability is managed through Plans to avoid duplication. Use plan settings to enable or disable tenant-facing capabilities.</p>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <a href="<?= base_url('controlplane/plans') ?>" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition-all">
                                        <i class="fas fa-layer-group mr-2"></i>Manage Plans
                                    </a>
                                    <a href="<?= base_url('controlplane/email-templates') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-all">
                                        <i class="fas fa-envelope mr-2"></i>Email Templates
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));

    const target = document.getElementById('tab-' + tabName);
    if (target) {
        target.classList.remove('hidden');
    }

    const navButtons = document.querySelectorAll('[id^="nav-"]');
    navButtons.forEach(btn => {
        btn.className = 'w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900';
        const icon = btn.querySelector('i');
        if (icon) icon.className = icon.className.replace('text-slate-300', 'text-slate-400');
    });

    const activeBtn = document.getElementById('nav-' + tabName);
    let activeClass = 'bg-gradient-to-r from-slate-700 to-gray-800 text-white shadow-md';
    if (tabName === 'governance') activeClass = 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md';

    if (activeBtn) {
        activeBtn.className = `w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 ${activeClass}`;
        const activeIcon = activeBtn.querySelector('i');
        if (activeIcon) {
            activeIcon.classList.remove('text-slate-400');
            activeIcon.classList.add('text-white/80');
        }
    }
}

function toastMessage(type, message) {
    if (window.showToast) {
        window.showToast(type === 'success' ? 'success' : 'error', type === 'success' ? 'Success' : 'Error', message);
        return;
    }
    alert(message);
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
            if (!confirm('Prune all expired/superseded export artifacts? This cannot be undone.')) return;

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
        });
    }

    const pruneAuditsBtn = document.getElementById('prune-audits-btn');
    if (pruneAuditsBtn) {
        pruneAuditsBtn.addEventListener('click', () => {
            if (!confirm('Prune control plane audit logs beyond the retention window?')) return;

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
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const urlTab = new URLSearchParams(window.location.search).get('tab');
    if (urlTab && document.getElementById(`tab-${urlTab}`)) {
        showTab(urlTab);
    } else {
        showTab('platform');
    }

    initRetentionControls();
    bindPruneActions();
});
</script>
<?= $this->endSection() ?>
