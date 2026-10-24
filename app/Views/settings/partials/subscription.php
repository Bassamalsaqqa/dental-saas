                <div id="subscription-tab" class="settings-tab hidden">
                    <div class="v3-surface v3-radius border v3-border overflow-hidden">
                        <div class="p-4 v3-form-section-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center"><i class="fas fa-layer-group text-emerald-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsSubscription') ?></h3><p class="text-slate-300"><?= lang('UI.settingsSubscriptionDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-4 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="v3-surface border v3-border v3-radius p-6">
                                    <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsCurrentPlan') ?></h4>
                                    <p class="text-2xl font-bold text-slate-900 mt-3"><?= esc($subscription_plan['name'] ?? lang('TenantMisc.unassigned')) ?></p>
                                    <p class="text-sm text-slate-500 mt-2"><?= lang('TenantMisc.statusLabel') ?> <?= esc($subscription['status'] ?? lang('TenantMisc.none')) ?></p>
                                </div>
                                <div class="v3-surface border v3-border v3-radius p-6">
                                    <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsLatestRequest') ?></h4>
                                    <?php if (!empty($latest_plan_request)): ?>
                                        <p class="text-sm text-slate-500 mt-2"><?= lang('TenantMisc.statusLabel') ?> <?= esc(ucfirst($latest_plan_request['status'] ?? lang('TenantMisc.pending'))) ?></p>
                                        <p class="text-xs text-slate-400"><?= lang('TenantMisc.requestedLabel') ?> <?= esc($latest_plan_request['created_at'] ?? '--') ?></p>
                                    <?php else: ?>
                                        <p class="text-sm text-slate-500 mt-2"><?= lang('UI.settingsNoPlanRequestsYet') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form action="<?= base_url('settings/plan-request') ?>" method="POST" class="v3-surface border v3-border v3-radius p-6 space-y-4">
                                <?= csrf_field() ?>
                                <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsRequestNewPlan') ?></h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div><label class="block text-xs font-semibold text-gray-700 mb-2"><?= lang('UI.settingsPlan') ?></label><select name="plan_id" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-emerald-500 transition-all" required><option value=""><?= lang('UI.settingsSelectPlan') ?></option><?php foreach (($plans ?? []) as $plan): ?><option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option><?php endforeach; ?></select></div>
                                    <div><label class="block text-xs font-semibold text-gray-700 mb-2"><?= lang('UI.settingsDuration') ?></label><select name="duration" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-emerald-500 transition-all"><option value="monthly"><?= lang('UI.settingsDurationMonthly') ?></option><option value="yearly"><?= lang('UI.settingsDurationYearly') ?></option><option value="lifetime"><?= lang('UI.settingsDurationLifetime') ?></option></select></div>
                                    <div><label class="block text-xs font-semibold text-gray-700 mb-2"><?= lang('UI.settingsReason') ?></label><input type="text" name="reason" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-emerald-500 transition-all" placeholder="<?= lang('UI.settingsOptionalReason') ?>"></div>
                                </div>
                                <div class="flex justify-end"><button type="submit" class="px-6 py-3 text-white font-semibold v3-radius transition-all v3-form-section-header"><i class="fas fa-paper-plane mr-2"></i><?= lang('UI.settingsSubmitRequest') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
