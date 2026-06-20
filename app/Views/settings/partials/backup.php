                <div id="backup-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-database text-amber-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('TenantMisc.backupRestore') ?></h3><p class="text-slate-300"><?= lang('TenantMisc.manageDataSafety') ?></p></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="settings-action-panel mb-8 flex flex-col gap-5 rounded-[1.5rem] p-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white shadow-lg shadow-violet-500/25">
                                        <i class="fas fa-download"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black text-slate-950"><?= lang('UI.settingsManualBackup') ?></h4>
                                        <p class="mt-1 max-w-2xl text-sm font-semibold text-slate-600"><?= lang('UI.settingsManualBackupDesc') ?></p>
                                    </div>
                                </div>
                                <form action="<?= base_url('settings/create-backup') ?>" method="POST" class="shrink-0">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-6 py-3 font-extrabold text-white shadow-lg shadow-violet-500/25 transition-all hover:-translate-y-0.5 hover:shadow-xl"><i class="fas fa-download mr-2"></i><?= lang('UI.settingsCreateBackup') ?></button>
                                </form>
                            </div>

                            <div class="space-y-4">
                                <?php if (!empty($backups)): ?>
                                    <?php foreach ($backups as $backup): ?>
                                        <?php $backupName = (string) ($backup['name'] ?? ''); ?>
                                        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                                            <div class="min-w-0">
                                                <h5 class="truncate font-black text-slate-900"><?= esc($backupName) ?></h5>
                                                <p class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($backup['size'] ?? '')) ?> &bull; <?= esc((string) ($backup['created_at'] ?? '')) ?></p>
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="button" onclick="downloadBackup(<?= json_encode($backupName) ?>)" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100"><i class="fas fa-download"></i></button>
                                                <button type="button" onclick="restoreBackup(<?= json_encode($backupName) ?>)" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 transition hover:bg-rose-100"><i class="fas fa-undo"></i></button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="settings-empty-state flex flex-col items-center justify-center rounded-[1.5rem] px-6 py-14 text-center">
                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-800 to-blue-700 text-white shadow-lg shadow-blue-500/20">
                                            <i class="fas fa-database text-2xl"></i>
                                        </div>
                                        <h4 class="mb-1 text-lg font-black text-slate-950"><?= lang('UI.settingsNoBackupsYet') ?></h4>
                                        <p class="mb-6 max-w-md text-sm font-semibold text-slate-600">
                                            Create your first backup to protect your clinic's data.
                                            Backups can be downloaded or used to restore the system.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-8 border-t border-slate-200 pt-8">
                                <div class="mb-6 flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-600 to-orange-500 text-white shadow-lg shadow-rose-500/20">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black text-slate-950">
                                            Export Retention Policy
                                        </h4>
                                        <p class="mt-1 text-sm font-semibold text-slate-600">
                                            Control how long exported files are kept before automatic pruning.
                                        </p>
                                    </div>
                                </div>

                                <?php if (!session()->get('global_mode')): ?>
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                                        <i class="fas fa-lock mr-2"></i>
                                        Export retention policy is managed by your platform administrator.
                                    </div>
                                <?php else: ?>
                                    <form action="<?= base_url('settings/updateRetention') ?>" method="POST" class="space-y-6">
                                        <?= csrf_field() ?>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <label class="cursor-pointer rounded-2xl border-2 p-4 text-center transition <?= ($settings['retention_mode'] ?? 'latest') === 'latest' ? 'border-rose-500 bg-rose-50 text-rose-900 shadow-lg shadow-rose-500/10' : 'border-slate-200 bg-white text-slate-700 hover:border-rose-200 hover:bg-rose-50/50' ?>">
                                                <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= ($settings['retention_mode'] ?? 'latest') === 'latest' ? 'checked' : '' ?>>
                                                <div class="font-black">Latest Only</div>
                                            </label>
                                            <label class="cursor-pointer rounded-2xl border-2 p-4 text-center transition <?= ($settings['retention_mode'] ?? '') === 'keep_last_n' ? 'border-rose-500 bg-rose-50 text-rose-900 shadow-lg shadow-rose-500/10' : 'border-slate-200 bg-white text-slate-700 hover:border-rose-200 hover:bg-rose-50/50' ?>">
                                                <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= ($settings['retention_mode'] ?? '') === 'keep_last_n' ? 'checked' : '' ?>>
                                                <div class="font-black">Keep Last N</div>
                                            </label>
                                            <label class="cursor-pointer rounded-2xl border-2 p-4 text-center transition <?= ($settings['retention_mode'] ?? '') === 'keep_x_days' ? 'border-rose-500 bg-rose-50 text-rose-900 shadow-lg shadow-rose-500/10' : 'border-slate-200 bg-white text-slate-700 hover:border-rose-200 hover:bg-rose-50/50' ?>">
                                                <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= ($settings['retention_mode'] ?? '') === 'keep_x_days' ? 'checked' : '' ?>>
                                                <div class="font-black">Time Based</div>
                                            </label>
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit" class="rounded-xl bg-gradient-to-r from-rose-600 to-orange-500 px-8 py-3 font-extrabold text-white shadow-lg shadow-rose-500/20 transition-all hover:-translate-y-0.5">
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
