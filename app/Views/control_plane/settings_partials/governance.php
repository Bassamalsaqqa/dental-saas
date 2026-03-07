                <div id="tab-governance" class="settings-content hidden">
                    <div class="space-y-8">
                        <div id="section-governance-retention" data-section-title="Export Retention Policy" class="control-settings-card">
                            <div class="control-settings-header">
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
                                <form action="<?= base_url('controlplane/settings/retention') ?>" method="POST" class="space-y-6" data-settings-form="true" data-saving-label="Saving retention policy...">
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
                                            <input type="number" name="retention_last_n" value="<?= esc((int) ($settings['retention_last_n'] ?? 5)) ?>" min="1" max="100" class="control-settings-field">
                                            <p class="text-xs text-slate-400 mt-1">How many export files to keep per clinic per export type.</p>
                                        </div>
                                        <div id="retention-days" class="<?= $retentionMode === 'keep_x_days' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of days to keep</label>
                                            <input type="number" name="retention_days" value="<?= esc((int) ($settings['retention_days'] ?? 30)) ?>" min="1" max="3650" class="control-settings-field">
                                            <p class="text-xs text-slate-400 mt-1">Exports older than this number of days are eligible for pruning.</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-exports-btn" class="control-settings-secondary">
                                            <i class="fas fa-broom mr-2"></i>Prune Export Artifacts
                                        </button>
                                        <button type="submit" class="control-settings-submit">
                                            <i class="fas fa-save mr-2"></i>Save Retention Policy
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="section-governance-audit" data-section-title="Audit Retention" class="control-settings-card">
                            <div class="control-settings-header">
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
                                <form action="<?= base_url('controlplane/settings/audit-retention') ?>" method="POST" class="space-y-6" data-settings-form="true" data-saving-label="Saving audit policy...">
                                    <?= csrf_field() ?>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Audit log retention window (days)</label>
                                        <input type="number" name="control_plane_audit_retention_days" value="<?= esc((int) ($settings['control_plane_audit_retention_days'] ?? 365)) ?>" min="30" max="3650" class="control-settings-field">
                                        <p class="text-xs text-slate-400 mt-1">Audit log entries older than this window are pruned when you run 'Prune Audit Logs'. Minimum 30 days recommended.</p>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-audits-btn" class="control-settings-secondary">
                                            <i class="fas fa-broom mr-2"></i>Prune Audit Logs
                                        </button>
                                        <button type="submit" class="control-settings-submit">
                                            <i class="fas fa-save mr-2"></i>Save Audit Policy
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="section-governance-features" data-section-title="Feature Governance" class="control-settings-card p-8">
                            <div class="flex items-start justify-between flex-wrap gap-6">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">Feature Governance</h4>
                                    <p class="text-sm text-slate-600 mt-2 max-w-2xl">Feature availability is managed through Plans to avoid duplication. Use plan settings to enable or disable tenant-facing capabilities.</p>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <a href="<?= base_url('controlplane/plans') ?>" class="control-settings-submit">
                                        <i class="fas fa-layer-group mr-2"></i>Manage Plans
                                    </a>
                                    <a href="<?= base_url('controlplane/settings?tab=communications&subtab=email-templates') ?>" class="control-settings-secondary">
                                        <i class="fas fa-envelope mr-2"></i>Email Templates
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

