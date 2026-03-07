                            <div id="comms-panel-whatsapp" class="comms-panel hidden space-y-6">
                                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-emerald-800 text-sm">WhatsApp Live Operations</h4>
                                        <p class="text-xs text-emerald-600 mt-0.5">Real-time delivery stats, journal, and opt-out monitoring</p>
                                    </div>
                                    <a href="<?= base_url('controlplane/whatsapp') ?>" class="px-5 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:from-emerald-600 hover:to-emerald-700 transition-all">
                                        <i class="fas fa-external-link-alt mr-1.5"></i>Open Dashboard
                                    </a>
                                </div>

                                <div class="pt-2 border-t border-slate-100">
                                    <h4 class="text-sm font-bold text-slate-800 mb-3">API Credentials</h4>
                                    <div class="space-y-4">
                                        <?php foreach (($wa_secrets ?? []) as $s): ?>
                                            <div class="p-4 border border-slate-200 rounded-2xl bg-white">
                                                <div class="flex items-center justify-between mb-3">
                                                    <code class="text-xs font-black text-blue-600"><?= esc($s['secret_key']) ?></code>
                                                    <span class="text-[10px] font-black <?= !empty($s['missing']) ? 'text-rose-600' : 'text-emerald-600' ?>"><?= !empty($s['missing']) ? 'MISSING' : 'READY' ?></span>
                                                </div>
                                                <form action="<?= base_url('controlplane/whatsapp/rotate-secret') ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3" data-settings-form="true" data-saving-label="Rotating key...">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="secret_key" value="<?= esc($s['secret_key']) ?>">
                                                    <div class="relative md:col-span-1">
                                                        <input type="password" name="new_value" required class="w-full px-4 py-3 pr-10 bg-white border border-slate-200 rounded-xl text-sm font-mono text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all" placeholder="Paste new secret value">
                                                        <button type="button" onclick="toggleSecretVis(this)" class="absolute right-3 top-2.5 text-slate-400"><i class="fas fa-eye text-sm"></i></button>
                                                    </div>
                                                    <input type="text" name="rotation_note" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 font-medium focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all md:col-span-1" placeholder="Rotation note">
                                                    <button type="submit" class="px-5 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:from-blue-600 hover:to-indigo-700 transition-all md:col-span-1">Rotate Key</button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-slate-100">
                                    <h4 class="text-sm font-bold text-slate-800 mb-3">Tenant Rate Limits</h4>
                                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                                        <table class="w-full text-sm">
                                            <thead class="bg-slate-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Clinic</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Daily</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Monthly</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Per Hour</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                            <?php foreach (($wa_policies ?? []) as $pol): ?>
                                                <tr>
                                                    <td class="px-4 py-3"><?= esc($pol['clinic_name']) ?></td>
                                                    <td class="px-4 py-3"><input type="number" form="policy-form-<?= (int) $pol['clinic_id'] ?>" name="daily_limit" value="<?= (int) $pol['daily_limit'] ?>" class="w-20 px-2 py-1 border border-slate-300 rounded text-xs"></td>
                                                    <td class="px-4 py-3"><input type="number" form="policy-form-<?= (int) $pol['clinic_id'] ?>" name="monthly_limit" value="<?= (int) $pol['monthly_limit'] ?>" class="w-24 px-2 py-1 border border-slate-300 rounded text-xs"></td>
                                                    <td class="px-4 py-3"><input type="number" form="policy-form-<?= (int) $pol['clinic_id'] ?>" name="per_hour_limit" value="<?= (int) $pol['per_hour_limit'] ?>" class="w-20 px-2 py-1 border border-slate-300 rounded text-xs"></td>
                                                    <td class="px-4 py-3">
                                                        <form id="policy-form-<?= (int) $pol['clinic_id'] ?>" action="<?= base_url('controlplane/whatsapp/policy') ?>" method="post" data-settings-form="true" data-saving-label="Saving policy...">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="clinic_id" value="<?= (int) $pol['clinic_id'] ?>">
                                                            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg text-xs font-semibold shadow-sm hover:from-blue-600 hover:to-indigo-700 transition-all">Save</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
