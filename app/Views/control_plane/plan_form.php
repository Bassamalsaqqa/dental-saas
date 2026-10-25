<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
    $plan = $plan ?? null;
    $limits = $limits ?? [];
    $features = $features ?? [];
    $mode = $mode ?? 'create';
    $actionUrl = $mode === 'edit' && !empty($plan['id'])
        ? base_url('controlplane/plans/' . $plan['id'])
        : base_url('controlplane/plans');
?>
<div class="v4-task-page"><header class="v4-page-heading"><div><h1><?= esc(lang($mode==='edit'?'V4.editPlan':'V4.createPlan')) ?></h1><p><?= esc(lang('V4.planCatalogHelp')) ?></p></div><a class="v4-button" href="<?= base_url('controlplane/plans') ?>"><?= esc(lang('V4.back')) ?></a></header><form action="<?= $actionUrl ?>" method="post" class="v4-form v4-task-form v4-plan-config">
                        <?= csrf_field() ?>

                        <!-- Left Column: Core & Limits -->
                        <div class="space-y-6">

                            <!-- Core Details Section -->
                            <div class="group/section relative">

                                <div class="v4-form-section">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tag text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V4.planCore')) ?></h4>
                                    </div>

                                    <div class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="md:col-span-2 group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-signature v4-text-action"></i>
                                                    <span><?= esc(lang('V4.planName')) ?></span>
                                                </label>
                                                <div class="relative">
                                                    <input type="text" name="name" value="<?= esc($plan['name'] ?? '') ?>"
                                                           class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300"
                                                           placeholder="e.g. Professional Tier" required>
                                                </div>
                                            </div>

                                            <div class="group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-dollar-sign v4-text-action"></i>
                                                    <span><?= esc(lang('V4.price')) ?></span>
                                                </label>
                                                <div class="relative">
                                                    <input type="number" name="price" value="<?= esc($limits['price'] ?? 0) ?>" min="0" step="0.01"
                                                           class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 pl-10"
                                                           placeholder="0.00">
                                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                                        <span class="text-gray-500 font-bold">$</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-calendar-alt v4-text-action"></i>
                                                    <span><?= esc(lang('V4.billingDuration')) ?></span>
                                                </label>
                                                <div class="relative">
                                                    <select name="duration" class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 appearance-none">
                                                        <?php $duration = $limits['duration'] ?? 'monthly'; ?>
                                                        <option value="monthly" <?= $duration === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                        <option value="yearly" <?= $duration === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                                        <option value="lifetime" <?= $duration === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2 group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-align-left v4-text-action"></i>
                                                    <span><?= esc(lang('V4.description')) ?></span>
                                                </label>
                                                <textarea name="description" rows="3"
                                                          class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 resize-none"
                                                          placeholder="Public description of this plan..."><?= esc($limits['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Limits & Quotas Section -->
                            <div class="group/section relative">

                                <div class="v4-form-section">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chart-pie text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V4.limitsQuotas')) ?></h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-users v4-text-action"></i>
                                                <span><?= esc(lang('V4.patientLimit')) ?></span>
                                            </label>
                                            <input type="number" name="patient_limit" value="<?= esc($limits['patients_active_max'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="-1 for unlimited">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-user-shield v4-text-action"></i>
                                                <span>User Seats</span>
                                            </label>
                                            <input type="number" name="user_seats" value="<?= esc($limits['users_max'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="-1 for unlimited">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-hdd v4-text-action"></i>
                                                <span><?= esc(lang('V4.storageGb')) ?></span>
                                            </label>
                                            <input type="number" name="storage_gb" value="<?= esc($limits['storage_gb'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="-1 for unlimited">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-file-upload v4-text-action"></i>
                                                <span>Attachment Max Size (MB)</span>
                                            </label>
                                            <input type="number" name="attachments_max_file_mb" value="<?= esc($limits['attachments_max_file_mb'] ?? 5) ?>" min="-1" step="1"
                                                   class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="5">
                                            <p class="mt-2 text-xs text-slate-400">Use -1 for unlimited.</p>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-hourglass-half v4-text-action"></i>
                                                <span><?= esc(lang('V4.trialDays')) ?></span>
                                            </label>
                                            <input type="number" name="trial_days" value="<?= esc($limits['trial_days'] ?? 0) ?>" min="0"
                                                   class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-file-export v4-text-action"></i>
                                                <span>Export Quota</span>
                                            </label>
                                            <input type="number" name="export_quota" value="<?= esc($limits['exports'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-envelope v4-text-action"></i>
                                                <span>Email Quota</span>
                                            </label>
                                            <input type="number" name="email_quota" value="<?= esc($limits['notifications_email'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature Modules -->
                            <div class="group/section relative">

                                <div class="v4-form-section">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-toggle-on text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V4.featureModules')) ?></h4>
                                    </div>

                                    <div class="mb-5 v4-radius border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                        <p class="font-semibold">Plan flag model</p>
                                        <p class="mt-1 text-amber-800">Only the entitlements in the enforced section below are guaranteed to block tenant access today. Module packaging flags remain catalog metadata until their runtime guards are implemented.</p>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Enforced Entitlements</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-emerald-200 v4-radius cursor-pointer hover:border-emerald-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[finance_reports][enabled]" <?= (!empty($features['finance_reports']['enabled']) || (empty($features['finance_reports']) && !empty($features['reports']))) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 transition-colors">
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-700">Finance Reports</div>
                                                <div class="text-xs text-gray-500">Canonical `/finance/reports` workspace and finance report exports.</div>
                                            </div>
                                        </label>
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-gray-200 v4-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[exports][enabled]" <?= !empty($features['exports']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700">Data Export Tools</div>
                                        </label>
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-gray-200 v4-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[notifications][email][enabled]" <?= !empty($features['notifications']['email']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700">Email Notifications</div>
                                        </label>
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-gray-200 v4-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[notifications][whatsapp][enabled]" <?= !empty($features['notifications']['whatsapp']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700">WhatsApp Notifications</div>
                                        </label>
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-gray-200 v4-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[attachments][enabled]" <?= !empty($features['attachments']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700">Patient Attachments</div>
                                        </label>
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-gray-200 v4-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[contracts][enabled]" <?= !empty($features['contracts']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700">Treatment Contracts</div>
                                        </label>
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-gray-200 v4-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[contracts][signature][enabled]" <?= !empty($features['contracts']['signature']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700">Contract E-Signatures</div>
                                        </label>
                                        <label class="relative flex items-center p-4 v4-surface-fill border-2 border-gray-200 v4-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[kiosk][enabled]" <?= !empty($features['kiosk']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700">Kiosk Check-In</div>
                                        </label>
                                    </div>

                                    <div class="mt-8 mb-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Catalog / Roadmap Flags</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php
                                            $featuresList = [
                                                'appointments' => 'Appointments Module',
                                                'finance' => 'Finance Module',
                                                'inventory' => 'Inventory Module',
                                                'reports' => 'General Reports Package',
                                                'email_templates' => 'Email Templates',
                                                'audit_exports' => 'Audit Log Exports',
                                                'chairside_notes' => 'Chairside Notes',
                                                'claims_hub' => 'Insurance Claims',
                                            ];
                                            foreach ($featuresList as $key => $label):
                                                $checked = !empty($features[$key]) ? 'checked' : '';
                                        ?>
                                        <label class="relative flex items-center p-4 bg-slate-50/90 border-2 border-slate-200 v4-radius cursor-pointer hover:border-slate-300 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[<?= $key ?>]" <?= $checked ?>
                                                       class="w-5 h-5 text-slate-600 border-gray-300 rounded focus:ring-slate-500 transition-colors">
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-700"><?= esc($label) ?></div>
                                                <div class="text-xs text-gray-500">Stored in the plan catalog; not yet used as a strict runtime gate.</div>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar (Publish & Tips) -->
                        <div class="space-y-8">
                            <div class="group/section relative">

                                <div class="v4-form-section">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-save text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V4.publish')) ?></h4>
                                    </div>

                                    <div class="space-y-6">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-power-off text-slate-600"></i>
                                                <span>Status</span>
                                            </label>
                                            <div class="relative">
                                                <select name="status" class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-slate-500/20 focus:border-slate-500 transition-all duration-300 appearance-none">
                                                    <?php $status = $plan['status'] ?? 'active'; ?>
                                                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full group/btn relative inline-flex items-center justify-center px-8 py-4 text-white text-sm font-bold v4-radius transition-all duration-300 hover:scale-105">

                                            <i class="fas fa-save mr-2 relative z-10"></i>
                                            <span class="relative z-10"><?= $mode === 'edit' ? 'Update Plan' : 'Create Plan' ?></span>
                                        </button>

                                        <a href="<?= base_url('controlplane/plans') ?>" class="w-full group/btn relative inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 text-gray-700 text-sm font-bold v4-radius hover:bg-gradient-to-r hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Tips -->
                            <div class="bg-blue-50/80 border border-blue-100 v4-radius p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <i class="fas fa-lightbulb text-blue-500"></i>
                                    <h4 class="font-bold text-blue-900 text-sm"><?= esc(lang('V4.configTips')) ?></h4>
                                </div>
                                <ul class="space-y-3 text-xs font-medium text-blue-800">
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check mt-0.5 text-blue-400"></i>
                                        <span>Use <strong>-1</strong> for unlimited quotas.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check mt-0.5 text-blue-400"></i>
                                        <span>Price <strong>0.00</strong> allows free trials.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form></div><?= $this->endSection() ?>