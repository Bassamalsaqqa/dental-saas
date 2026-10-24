                <div id="backup-tab" class="settings-tab hidden">
                    <div class="v3-surface v3-radius border v3-border overflow-hidden">
                        <div class="p-4 v3-form-section-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center"><i class="fas fa-database v3-text-warning text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold v3-text-on-primary"><?= lang('TenantMisc.backupRestore') ?></h3><p class="v3-text-muted"><?= lang('TenantMisc.manageDataSafety') ?></p></div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="settings-action-panel mb-8 flex flex-col gap-5 rounded-[1.5rem] border v3-border v3-surface p-6 sm:flex-row sm:items-center">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center v3-radius v3-bg-forest v3-text-warning">
                                        <i class="fas fa-shield-alt text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black v3-text-ink"><?= lang('TenantMisc.backupManagedPlatformTitle') ?></h4>
                                        <p class="mt-2 text-sm font-semibold leading-relaxed v3-text-muted"><?= lang('TenantMisc.backupManagedPlatformDesc') ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 border-t v3-border pt-8">
                                <div class="mb-6 flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center v3-radius v3-text-on-primary v3-form-section-header">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black v3-text-ink">
                                            Export Retention Policy
                                        </h4>
                                        <p class="mt-1 text-sm font-semibold v3-text-muted">
                                            Control how long exported files are kept before automatic pruning.
                                        </p>
                                    </div>
                                </div>

                                <?php if (!session()->get('global_mode')): ?>
                                    <div class="v3-radius border v3-border-default v3-bg-waiting p-4 text-sm font-semibold v3-text-warning">
                                        <i class="fas fa-lock mr-2"></i>
                                        Export retention policy is managed by your platform administrator.
                                    </div>
                                <?php else: ?>
                                    <form action="<?= base_url('settings/updateRetention') ?>" method="POST" class="space-y-6">
                                        <?= csrf_field() ?>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <label class="cursor-pointer v3-radius border-2 p-4 text-center transition <?= ($settings['retention_mode'] ?? 'latest') === 'latest' ? 'v3-border-default v3-bg-alert v3-text-danger shadow-lg shadow-rose-500/10' : 'v3-border-default v3-bg-panel v3-text-ink v3-border-default v3-hover-danger' ?>">
                                                <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= ($settings['retention_mode'] ?? 'latest') === 'latest' ? 'checked' : '' ?>>
                                                <div class="font-black">Latest Only</div>
                                            </label>
                                            <label class="cursor-pointer v3-radius border-2 p-4 text-center transition <?= ($settings['retention_mode'] ?? '') === 'keep_last_n' ? 'v3-border-default v3-bg-alert v3-text-danger shadow-lg shadow-rose-500/10' : 'v3-border-default v3-bg-panel v3-text-ink v3-border-default v3-hover-danger' ?>">
                                                <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= ($settings['retention_mode'] ?? '') === 'keep_last_n' ? 'checked' : '' ?>>
                                                <div class="font-black">Keep Last N</div>
                                            </label>
                                            <label class="cursor-pointer v3-radius border-2 p-4 text-center transition <?= ($settings['retention_mode'] ?? '') === 'keep_x_days' ? 'v3-border-default v3-bg-alert v3-text-danger shadow-lg shadow-rose-500/10' : 'v3-border-default v3-bg-panel v3-text-ink v3-border-default v3-hover-danger' ?>">
                                                <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= ($settings['retention_mode'] ?? '') === 'keep_x_days' ? 'checked' : '' ?>>
                                                <div class="font-black">Time Based</div>
                                            </label>
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit" class="v3-radius px-8 py-3 font-extrabold v3-text-on-primary transition-all v3-form-section-header">
                                                Save Policy
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Communications Tab -->
