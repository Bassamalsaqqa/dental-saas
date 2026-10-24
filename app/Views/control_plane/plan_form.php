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
<div class="v3-platform-workspace min-h-screen relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">


    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Enhanced Form Container with Glassmorphism -->
        <div class="group relative">

            <div class="relative cp-surface border cp-border cp-radius transition-all duration-500 overflow-hidden">

                <!-- Form Header -->
                <div class="cp-panel-padding border-b cp-border">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">

                                <div class="relative w-12 h-12 cp-radius flex items-center justify-center transition-all duration-300">
                                    <i class="fas fa-sliders-h text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300"><?= $mode === 'edit' ? 'Edit Plan' : 'Plan Configurator' ?></h3>
                                <p class="text-gray-600 font-medium"><?= esc(lang('V3Platform.defineEntitlementsLimitsAndPricingModels')) ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="px-4 py-2 cp-radius bg-slate-100 border cp-border text-slate-600 text-sm font-bold">
                                <?= esc(ucfirst($mode)) ?> <?= esc(lang('V3Platform.mode')) ?>
                            </span>
                            <?php if ($mode === 'edit'): ?>
                                <span class="px-4 py-2 cp-radius border <?= ($plan['status'] ?? 'active') === 'active' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-600' ?> text-sm font-bold">
                                    <?= esc(ucfirst($plan['status'] ?? 'active')) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="cp-panel-padding">
                    <form action="<?= $actionUrl ?>" method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                        <?= csrf_field() ?>

                        <!-- Left Column: Core & Limits -->
                        <div class="lg:col-span-2 space-y-8">

                            <!-- Core Details Section -->
                            <div class="group/section relative">

                                <div class="relative cp-surface border cp-border cp-radius cp-panel-padding">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tag text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V3Platform.coreDetails')) ?></h4>
                                    </div>

                                    <div class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div class="md:col-span-2 group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-signature cp-accent"></i>
                                                    <span><?= esc(lang('V3Platform.planName')) ?></span>
                                                </label>
                                                <div class="relative">
                                                    <input type="text" name="name" value="<?= esc($plan['name'] ?? '') ?>"
                                                           class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300"
                                                           placeholder="<?= esc(lang('V3Platform.eGProfessionalTier'), 'attr') ?>" required>
                                                </div>
                                            </div>

                                            <div class="group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-dollar-sign cp-accent"></i>
                                                    <span><?= esc(lang('V3Platform.price')) ?></span>
                                                </label>
                                                <div class="relative">
                                                    <input type="number" name="price" value="<?= esc($limits['price'] ?? 0) ?>" min="0" step="0.01"
                                                           class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 pl-10"
                                                           placeholder="0.00">
                                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                                        <span class="text-gray-500 font-bold">$</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-calendar-alt cp-accent"></i>
                                                    <span><?= esc(lang('V3Platform.billingDuration')) ?></span>
                                                </label>
                                                <div class="relative">
                                                    <select name="duration" class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 appearance-none">
                                                        <?php $duration = $limits['duration'] ?? 'monthly'; ?>
                                                        <option value="monthly" <?= $duration === 'monthly' ? 'selected' : '' ?>><?= esc(lang('V3Platform.monthly')) ?></option>
                                                        <option value="yearly" <?= $duration === 'yearly' ? 'selected' : '' ?>><?= esc(lang('V3Platform.yearly')) ?></option>
                                                        <option value="lifetime" <?= $duration === 'lifetime' ? 'selected' : '' ?>><?= esc(lang('V3Platform.lifetime')) ?></option>
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2 group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-align-left cp-accent"></i>
                                                    <span><?= esc(lang('V3Platform.description')) ?></span>
                                                </label>
                                                <textarea name="description" rows="3"
                                                          class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 resize-none"
                                                          placeholder="<?= esc(lang('V3Platform.publicDescriptionOfThisPlan'), 'attr') ?>"><?= esc($limits['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Limits & Quotas Section -->
                            <div class="group/section relative">

                                <div class="relative cp-surface border cp-border cp-radius cp-panel-padding">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chart-pie text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V3Platform.limitsQuotas')) ?></h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-users cp-accent"></i>
                                                <span><?= esc(lang('V3Platform.patientLimit')) ?></span>
                                            </label>
                                            <input type="number" name="patient_limit" value="<?= esc($limits['patients_active_max'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="<?= esc(lang('V3Platform.label1ForUnlimited'), 'attr') ?>">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-user-shield cp-accent"></i>
                                                <span><?= esc(lang('V3Platform.userSeats')) ?></span>
                                            </label>
                                            <input type="number" name="user_seats" value="<?= esc($limits['users_max'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="<?= esc(lang('V3Platform.label1ForUnlimited'), 'attr') ?>">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-hdd cp-accent"></i>
                                                <span><?= esc(lang('V3Platform.storageGb')) ?></span>
                                            </label>
                                            <input type="number" name="storage_gb" value="<?= esc($limits['storage_gb'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="<?= esc(lang('V3Platform.label1ForUnlimited'), 'attr') ?>">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-file-upload cp-accent"></i>
                                                <span><?= esc(lang('V3Platform.attachmentMaxSizeMb')) ?></span>
                                            </label>
                                            <input type="number" name="attachments_max_file_mb" value="<?= esc($limits['attachments_max_file_mb'] ?? 5) ?>" min="-1" step="1"
                                                   class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300"
                                                   placeholder="5">
                                            <p class="mt-2 text-xs text-slate-400"><?= esc(lang('V3Platform.use1ForUnlimited')) ?></p>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-hourglass-half cp-accent"></i>
                                                <span><?= esc(lang('V3Platform.trialPeriodDays')) ?></span>
                                            </label>
                                            <input type="number" name="trial_days" value="<?= esc($limits['trial_days'] ?? 0) ?>" min="0"
                                                   class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-file-export cp-accent"></i>
                                                <span><?= esc(lang('V3Platform.exportQuota')) ?></span>
                                            </label>
                                            <input type="number" name="export_quota" value="<?= esc($limits['exports'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-envelope cp-accent"></i>
                                                <span><?= esc(lang('V3Platform.emailQuota')) ?></span>
                                            </label>
                                            <input type="number" name="email_quota" value="<?= esc($limits['notifications_email'] ?? -1) ?>"
                                                   class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature Modules -->
                            <div class="group/section relative">

                                <div class="relative cp-surface border cp-border cp-radius cp-panel-padding">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-toggle-on text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V3Platform.featureModules')) ?></h4>
                                    </div>

                                    <div class="mb-5 cp-radius border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                        <p class="font-semibold"><?= esc(lang('V3Platform.planFlagModel')) ?></p>
                                        <p class="mt-1 text-amber-800"><?= esc(lang('V3Platform.onlyTheEntitlementsInTheEnforcedSectionBelowAreGuaranteedToBlockTenantAccessTodayModulePackagingFlagsRemainCatalogMetadataUntilTheirRuntimeGuardsAreImplemented')) ?></p>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500"><?= esc(lang('V3Platform.enforcedEntitlements')) ?></p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <label class="relative flex items-center p-4 cp-surface border-2 border-emerald-200 cp-radius cursor-pointer hover:border-emerald-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[finance_reports][enabled]" <?= (!empty($features['finance_reports']['enabled']) || (empty($features['finance_reports']) && !empty($features['reports']))) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-emerald-600 cp-border rounded focus:ring-emerald-500 transition-colors">
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-700 group-hover/toggle:text-emerald-700"><?= esc(lang('V3Platform.financeReports')) ?></div>
                                                <div class="text-xs text-gray-500"><?= esc(lang('V3Platform.canonicalFinanceReportsWorkspaceAndFinanceReportExports')) ?></div>
                                            </div>
                                        </label>
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[exports][enabled]" <?= !empty($features['exports']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 cp-border rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc(lang('V3Platform.dataExportTools')) ?></div>
                                        </label>
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[notifications][email][enabled]" <?= !empty($features['notifications']['email']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 cp-border rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc(lang('V3Platform.emailNotifications')) ?></div>
                                        </label>
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[notifications][whatsapp][enabled]" <?= !empty($features['notifications']['whatsapp']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 cp-border rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc(lang('V3Platform.whatsappNotifications')) ?></div>
                                        </label>
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[attachments][enabled]" <?= !empty($features['attachments']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 cp-border rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc(lang('V3Platform.patientAttachments')) ?></div>
                                        </label>
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[contracts][enabled]" <?= !empty($features['contracts']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 cp-border rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc(lang('V3Platform.treatmentContracts')) ?></div>
                                        </label>
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[contracts][signature][enabled]" <?= !empty($features['contracts']['signature']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 cp-border rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc(lang('V3Platform.contractESignatures')) ?></div>
                                        </label>
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-orange-400 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[kiosk][enabled]" <?= !empty($features['kiosk']['enabled']) ? 'checked' : '' ?>
                                                       class="w-5 h-5 text-orange-600 cp-border rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc(lang('V3Platform.kioskCheckIn')) ?></div>
                                        </label>
                                    </div>

                                    <div class="mt-8 mb-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500"><?= esc(lang('V3Platform.catalogRoadmapFlags')) ?></p>
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
                                        <label class="relative flex items-center p-4 cp-surface border-2 cp-border cp-radius cursor-pointer hover:border-slate-300 transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[<?= $key ?>]" <?= $checked ?>
                                                       class="w-5 h-5 text-slate-600 cp-border rounded focus:ring-slate-500 transition-colors">
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-700 group-hover/toggle:text-slate-700"><?= esc($label) ?></div>
                                                <div class="text-xs text-gray-500"><?= esc(lang('V3Platform.storedInThePlanCatalogNotYetUsedAsAStrictRuntimeGate')) ?></div>
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

                                <div class="relative cp-surface border cp-border cp-radius cp-panel-padding">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-save text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('V3Platform.publish')) ?></h4>
                                    </div>

                                    <div class="space-y-6">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-power-off text-slate-600"></i>
                                                <span><?= esc(lang('V3Platform.status')) ?></span>
                                            </label>
                                            <div class="relative">
                                                <select name="status" class="w-full px-4 py-3 cp-surface border-2 cp-border cp-radius focus:outline-none focus:ring-4 focus:ring-slate-500/20 focus:border-slate-500 transition-all duration-300 appearance-none">
                                                    <?php $status = $plan['status'] ?? 'active'; ?>
                                                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= esc(lang('V3Platform.active')) ?></option>
                                                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>><?= esc(lang('V3Platform.inactive')) ?></option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full group/btn relative inline-flex items-center justify-center px-8 py-4 text-white text-sm font-bold cp-radius transition-all duration-300">

                                            <i class="fas fa-save mr-2 relative z-10"></i>
                                            <span class="relative z-10"><?= $mode === 'edit' ? 'Update Plan' : 'Create Plan' ?></span>
                                        </button>

                                        <a href="<?= base_url('controlplane/plans') ?>" class="w-full group/btn relative inline-flex items-center justify-center px-8 py-4 border-2 cp-border text-gray-700 text-sm font-bold cp-radius hover:border-gray-400 transition-all duration-300">
                                            <?= esc(lang('V3Platform.cancel')) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Tips -->
                            <div class="cp-soft-surface border cp-border cp-radius p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <i class="fas fa-lightbulb cp-accent"></i>
                                    <h4 class="font-bold cp-accent text-sm"><?= esc(lang('V3Platform.configTips')) ?></h4>
                                </div>
                                <ul class="space-y-3 text-xs font-medium cp-accent">
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check mt-0.5 cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.use')) ?> <strong>-1</strong> <?= esc(lang('V3Platform.forUnlimitedQuotas')) ?></span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check mt-0.5 cp-accent"></i>
                                        <span><?= esc(lang('V3Platform.price')) ?> <strong>0.00</strong> <?= esc(lang('V3Platform.allowsFreeTrials')) ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
