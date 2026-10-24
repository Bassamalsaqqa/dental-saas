                <div id="tab-governance" class="settings-content hidden">
                    <div class="space-y-8">
                        <div id="section-governance-retention" data-section-title="<?= esc(lang('V3Platform.exportRetentionPolicy'), 'attr') ?>" class="control-settings-card">
                            <div class="control-settings-header">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 cp-surface cp-radius flex items-center justify-center">
                                        <i class="fas fa-file-shield v3-text-on-primary text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold v3-text-on-primary"><?= esc(lang('V3Platform.exportRetentionPolicy')) ?></h3>
                                        <p class="v3-text-muted text-sm"><?= esc(lang('V3Platform.globalPolicyForExportArtifactsAcrossAllClinics')) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="cp-panel-padding space-y-6">
                                <form action="<?= base_url('controlplane/settings/retention') ?>" method="POST" class="space-y-6" data-settings-form="true" data-saving-label="Saving retention policy...">
                                    <?= csrf_field() ?>
                                    <?php $retentionMode = $settings['retention_mode'] ?? 'latest'; ?>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="relative flex flex-col p-4 border-2 cp-radius cursor-pointer v3-hover-soft transition-all <?= $retentionMode === 'latest' ? 'v3-border-default v3-bg-panel' : 'v3-border-default' ?>">
                                            <input type="radio" name="retention_mode" value="latest" class="sr-only v3-radio" <?= $retentionMode === 'latest' ? 'checked' : '' ?>>
                                            <span class="font-semibold v3-text-ink"><?= esc(lang('V3Platform.keepLatestOnly')) ?></span>
                                            <span class="text-xs v3-text-muted mt-1"><?= esc(lang('V3Platform.supersedePreviousExportsForEachPurpose')) ?></span>
                                        </label>
                                        <label class="relative flex flex-col p-4 border-2 cp-radius cursor-pointer v3-hover-soft transition-all <?= $retentionMode === 'keep_last_n' ? 'v3-border-default v3-bg-panel' : 'v3-border-default' ?>">
                                            <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only v3-radio" <?= $retentionMode === 'keep_last_n' ? 'checked' : '' ?>>
                                            <span class="font-semibold v3-text-ink"><?= esc(lang('V3Platform.keepLastN')) ?></span>
                                            <span class="text-xs v3-text-muted mt-1"><?= esc(lang('V3Platform.retainAFixedNumberOfRecentExports')) ?></span>
                                        </label>
                                        <label class="relative flex flex-col p-4 border-2 cp-radius cursor-pointer v3-hover-soft transition-all <?= $retentionMode === 'keep_x_days' ? 'v3-border-default v3-bg-panel' : 'v3-border-default' ?>">
                                            <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only v3-radio" <?= $retentionMode === 'keep_x_days' ? 'checked' : '' ?>>
                                            <span class="font-semibold v3-text-ink"><?= esc(lang('V3Platform.keepForXDays')) ?></span>
                                            <span class="text-xs v3-text-muted mt-1"><?= esc(lang('V3Platform.deleteExportsOlderThanTheTimeWindow')) ?></span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div id="retention-last-n" class="<?= $retentionMode === 'keep_last_n' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.numberOfExportsToKeep')) ?></label>
                                            <input type="number" name="retention_last_n" value="<?= esc((int) ($settings['retention_last_n'] ?? 5)) ?>" min="1" max="100" class="control-settings-field v3-input">
                                            <p class="text-xs v3-text-muted mt-1"><?= esc(lang('V3Platform.howManyExportFilesToKeepPerClinicPerExportType')) ?></p>
                                        </div>
                                        <div id="retention-days" class="<?= $retentionMode === 'keep_x_days' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.numberOfDaysToKeep')) ?></label>
                                            <input type="number" name="retention_days" value="<?= esc((int) ($settings['retention_days'] ?? 30)) ?>" min="1" max="3650" class="control-settings-field v3-input">
                                            <p class="text-xs v3-text-muted mt-1"><?= esc(lang('V3Platform.exportsOlderThanThisNumberOfDaysAreEligibleForPruning')) ?></p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t cp-border">
                                        <button type="button" id="prune-exports-btn" class="control-settings-secondary v3-btn v3-btn--secondary">
                                            <i class="fas fa-broom mr-2"></i><?= esc(lang('V3Platform.pruneExportArtifacts')) ?>
                                        </button>
                                        <button type="submit" class="control-settings-submit v3-btn v3-btn--primary">
                                            <i class="fas fa-save mr-2"></i><?= esc(lang('V3Platform.saveRetentionPolicy')) ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="section-governance-audit" data-section-title="<?= esc(lang('V3Platform.auditRetention'), 'attr') ?>" class="control-settings-card">
                            <div class="control-settings-header">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 cp-surface cp-radius flex items-center justify-center">
                                        <i class="fas fa-user-shield cp-accent text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold v3-text-on-primary"><?= esc(lang('V3Platform.controlPlaneAuditRetention')) ?></h3>
                                        <p class="v3-text-muted text-sm"><?= esc(lang('V3Platform.retentionPolicyForOperatorActivityLogs')) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="cp-panel-padding">
                                <form action="<?= base_url('controlplane/settings/audit-retention') ?>" method="POST" class="space-y-6" data-settings-form="true" data-saving-label="Saving audit policy...">
                                    <?= csrf_field() ?>
                                    <div>
                                        <label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.auditLogRetentionWindowDays')) ?></label>
                                        <input type="number" name="control_plane_audit_retention_days" value="<?= esc((int) ($settings['control_plane_audit_retention_days'] ?? 365)) ?>" min="30" max="3650" class="control-settings-field v3-input">
                                        <p class="text-xs v3-text-muted mt-1"><?= esc(lang('V3Platform.auditLogEntriesOlderThanThisWindowArePrunedWhenYouRunPruneAuditLogsMinimum30DaysRecommended')) ?></p>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t cp-border">
                                        <button type="button" id="prune-audits-btn" class="control-settings-secondary v3-btn v3-btn--secondary">
                                            <i class="fas fa-broom mr-2"></i><?= esc(lang('V3Platform.pruneAuditLogs')) ?>
                                        </button>
                                        <button type="submit" class="control-settings-submit v3-btn v3-btn--primary">
                                            <i class="fas fa-save mr-2"></i><?= esc(lang('V3Platform.saveAuditPolicy')) ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="section-governance-features" data-section-title="<?= esc(lang('V3Platform.featureGovernance'), 'attr') ?>" class="control-settings-card cp-panel-padding">
                            <div class="flex items-start justify-between flex-wrap gap-3">
                                <div>
                                    <h4 class="text-lg font-bold v3-text-ink"><?= esc(lang('V3Platform.featureGovernance')) ?></h4>
                                    <p class="text-sm v3-text-muted mt-2 max-w-2xl"><?= esc(lang('V3Platform.featureAvailabilityIsManagedThroughPlansToAvoidDuplicationUsePlanSettingsToEnableOrDisableTenantFacingCapabilities')) ?></p>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <a href="<?= base_url('controlplane/plans') ?>" class="control-settings-submit">
                                        <i class="fas fa-layer-group mr-2"></i><?= esc(lang('V3Platform.managePlans')) ?>
                                    </a>
                                    <a href="<?= base_url('controlplane/settings?tab=email-templates') ?>" class="control-settings-secondary">
                                        <i class="fas fa-envelope mr-2"></i><?= esc(lang('V3Platform.emailTemplates')) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

