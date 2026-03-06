                        </div>

                        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-clock text-emerald-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-white">Working Hours</h3>
                                        <p class="text-slate-300 text-sm">Set default operating hours and working days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label>
                                        <input type="time" name="default_working_hours_start" data-clinic-time="1" value="<?= esc($settings['default_working_hours_start'] ?? '09:00') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Default calendar open/close times applied to new clinics.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label>
                                        <input type="time" name="default_working_hours_end" data-clinic-time="1" value="<?= esc($settings['default_working_hours_end'] ?? '17:00') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Default calendar open/close times applied to new clinics.</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Working Days</label>
                                        <p class="text-xs text-slate-400 mt-1 mb-3">Days the clinic is open by default. Clinics can override their own schedule.</p>
                                        <?php
                                            $workingDefaults = $settings['default_working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            $dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                        ?>
                                        <div class="grid grid-cols-7 gap-2">
                                            <?php foreach ($days as $index => $day): ?>
                                                <label class="flex flex-col items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 <?= in_array($day, $workingDefaults, true) ? 'border-orange-500 bg-orange-50' : '' ?>">
                                                    <input type="checkbox" name="default_working_days[]" value="<?= esc($day) ?>" <?= in_array($day, $workingDefaults, true) ? 'checked' : '' ?> class="sr-only">
                                                    <span class="text-sm font-medium text-gray-700"><?= esc($dayLabels[$index]) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Appointment Duration (minutes)</label>
                                        <input type="number" name="default_appointment_duration" value="<?= esc((int) ($settings['default_appointment_duration'] ?? 30)) ?>" min="15" max="480" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Initial slot length for the clinic calendar in minutes. Clinics can override after onboarding.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Max Chairs</label>
                                        <input type="number" name="default_booking_chair_max" value="<?= esc((int) ($settings['default_booking_chair_max'] ?? 3)) ?>" min="1" max="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Platform-level cap on concurrent appointment slots per time slot. Cannot exceed 3.</p>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold rounded-xl hover:from-orange-600 hover:to-amber-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-save mr-2"></i>Save Clinic Defaults
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                                            <p class="text-xs text-slate-400 mt-1">How many export files to keep per clinic per export type.</p>
                                        </div>
                                        <div id="retention-days" class="<?= $retentionMode === 'keep_x_days' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of days to keep</label>
                                            <input type="number" name="retention_days" value="<?= esc((int) ($settings['retention_days'] ?? 30)) ?>" min="1" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                            <p class="text-xs text-slate-400 mt-1">Exports older than this number of days are eligible for pruning.</p>
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
                            <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-user-shield text-violet-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Control Plane Audit Retention</h3>
                                        <p class="text-slate-300 text-sm">Retention policy for operator activity logs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <form action="<?= base_url('controlplane/settings/audit-retention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Audit log retention window (days)</label>
                                        <input type="number" name="control_plane_audit_retention_days" value="<?= esc((int) ($settings['control_plane_audit_retention_days'] ?? 365)) ?>" min="30" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Audit log entries older than this window are pruned when you run 'Prune Audit Logs'. Minimum 30 days recommended.</p>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-audits-btn" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                                            <i class="fas fa-broom mr-2"></i>Prune Audit Logs
                                        </button>
                                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:from-slate-800 hover:to-gray-900 transition-all">
                                            <i class="fas fa-save mr-2"></i>Save Audit Policy
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
                                    <a href="<?= base_url('controlplane/settings?tab=communications&subtab=email-templates') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-all">
                                        <i class="fas fa-envelope mr-2"></i>Email Templates
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

