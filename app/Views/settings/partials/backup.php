                <div id="backup-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-database text-amber-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('TenantMisc.backupRestore') ?></h3><p class="text-slate-300"><?= lang('TenantMisc.manageDataSafety') ?></p></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="flex justify-between items-center mb-8 bg-gray-50 p-6 rounded-2xl border-2 border-dashed border-gray-200">
                                <div><h4 class="text-lg font-bold text-gray-900"><?= lang('UI.settingsManualBackup') ?></h4><p class="text-sm text-gray-500"><?= lang('UI.settingsManualBackupDesc') ?></p></div>
                                <form action="<?= base_url('settings/create-backup') ?>" method="POST"><?= csrf_field() ?><button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-xl shadow-lg hover:bg-purple-700 transition-all"><i class="fas fa-download mr-2"></i><?= lang('UI.settingsCreateBackup') ?></button></form>
                            </div>
                            <div class="space-y-4">
                                <?php if (!empty($backups)): foreach ($backups as $backup): ?>
                                <div class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-xl shadow-sm">
                                    <div><h5 class="font-bold text-gray-800"><?= $backup['name'] ?></h5><p class="text-xs text-gray-500"><?= $backup['size'] ?> â€¢ <?= $backup['created_at'] ?></p></div>
                                    <div class="flex space-x-2"><button onclick="downloadBackup('<?= $backup['name'] ?>')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-download"></i></button><button onclick="restoreBackup('<?= $backup['name'] ?>')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg"><i class="fas fa-undo"></i></button></div>
                                </div>
                                <?php endforeach; else: ?>
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                                        <i class="fas fa-database text-gray-300 text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-800 mb-1"><?= lang('UI.settingsNoBackupsYet') ?></h4>
                                    <p class="text-sm text-gray-500 mb-6 max-w-xs">
                                        Create your first backup to protect your clinic's data.
                                        Backups can be downloaded or used to restore the system.
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-8 pt-8 border-t border-gray-100">
                                <h4 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                                    <i class="fas fa-history text-red-500 mr-2"></i>
                                    Export Retention Policy
                                </h4>
                                <p class="text-sm text-gray-500 mb-6">
                                    Control how long exported files are kept before automatic pruning.
                                </p>

                                <?php if (!session()->get('global_mode')): ?>
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-700 text-sm">
                                    <i class="fas fa-lock mr-2"></i>
                                    Export retention policy is managed by your platform administrator.
                                </div>
                                <?php else: ?>
                                <form action="<?= base_url('settings/updateRetention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="p-4 border-2 rounded-xl cursor-pointer text-center <?= ($settings['retention_mode']??'latest')==='latest'?'border-red-500 bg-red-50':'border-gray-200' ?>">
                                            <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= ($settings['retention_mode']??'latest')==='latest'?'checked':'' ?>>
                                            <div class="font-bold">Latest Only</div>
                                        </label>
                                        <label class="p-4 border-2 rounded-xl cursor-pointer text-center <?= ($settings['retention_mode']??'')==='keep_last_n'?'border-red-500 bg-red-50':'border-gray-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= ($settings['retention_mode']??'')==='keep_last_n'?'checked':'' ?>>
                                            <div class="font-bold">Keep Last N</div>
                                        </label>
                                        <label class="p-4 border-2 rounded-xl cursor-pointer text-center <?= ($settings['retention_mode']??'')==='keep_x_days'?'border-red-500 bg-red-50':'border-gray-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= ($settings['retention_mode']??'')==='keep_x_days'?'checked':'' ?>>
                                            <div class="font-bold">Time Based</div>
                                        </label>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl shadow-lg transition-all">
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
