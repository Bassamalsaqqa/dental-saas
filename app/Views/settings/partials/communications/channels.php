                            <div id="sub-channels" class="sub-tab-panel">
                            <?php if ($is_global): ?>
                                <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr class="text-xs font-bold text-gray-500 uppercase border-b"><th class="pb-4"><?= lang('TenantMisc.clinic') ?></th><th class="pb-4"><?= lang('TenantMisc.email') ?></th><th class="pb-4"><?= lang('TenantMisc.sms') ?></th><th class="pb-4"><?= lang('TenantMisc.whatsapp') ?></th></tr></thead><tbody class="divide-y divide-gray-50">
                                    <?php foreach ($clinics as $clinic): ?>
                                    <tr><td class="py-4 font-bold"><?= esc($clinic['name']) ?></td>
                                        <?php foreach (['email', 'sms', 'whatsapp'] as $type):
                                            $ch = null; foreach ($clinic['channels'] as $c) if ($c['channel_type'] === $type) { $ch = $c; break; }
                                            $enabled = $ch && $ch['enabled_by_superadmin'] == 1; ?>
                                        <td class="py-4"><button onclick="toggleChannel(<?= $clinic['id'] ?>, '<?= $type ?>', <?= $enabled ? 0 : 1 ?>, this)" class="w-10 h-6 rounded-full transition-all <?= $enabled ? 'bg-green-500' : 'bg-gray-300' ?>"><div class="w-4 h-4 v3-surface rounded-full transition-all ml-1 <?= $enabled ? 'translate-x-4' : '' ?>"></div></button></td>
                                        <?php endforeach; ?>
                                    </tr><?php endforeach; ?>
                                </tbody></table></div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <?php foreach ($channels as $channel):
                                        $type = $channel['channel_type'];
                                        $locked = empty($channel['enabled_by_superadmin']); ?>
                                    <div class="p-6 v3-surface v3-radius border-2 v3-border text-center">
                                        <div class="w-12 h-12 mx-auto v3-surface v3-radius flex items-center justify-center v3-accent-text mb-4"><i class="fas fa-<?= $type==='whatsapp'?'fab fa-whatsapp':($type==='sms'?'sms':'envelope') ?> text-2xl"></i></div>
                                        <h4 class="font-bold text-gray-900 uppercase"><?= $type ?></h4>
                                        <div class="my-2"><span class="px-2 py-1 text-[10px] font-bold rounded-lg <?= $locked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>"><?= $locked ? lang('TenantMisc.disabledUpper') : lang('TenantMisc.activeUpper') ?></span></div>
                                        <button onclick="showSettingsTab('<?= $type ?>')" class="mt-4 px-4 py-2 v3-surface border v3-border text-xs font-bold rounded-lg hover:bg-gray-100 transition-all"><?= lang('TenantMisc.configureUpper') ?></button>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($channels)): ?>
                                    <div class="md:col-span-3 flex flex-col items-center justify-center py-16 text-center">
                                        <div class="w-16 h-16 bg-gray-100 v3-radius flex items-center justify-center mb-4">
                                            <i class="fas fa-satellite-dish text-gray-300 text-2xl"></i>
                                        </div>
                                        <h4 class="text-lg font-bold text-gray-800 mb-1"><?= lang('TenantMisc.noChannelsConfigured') ?></h4>
                                        <p class="text-sm text-gray-500 max-w-xs">
                                            <?= lang('TenantMisc.contactAdminEnableChannels') ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            </div>
