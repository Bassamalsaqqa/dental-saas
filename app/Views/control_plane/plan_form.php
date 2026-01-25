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
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-sliders-h text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= $mode === 'edit' ? 'Edit Plan' : 'Plan Configurator' ?></h1>
                            <p class="text-gray-600">Define entitlements, limits, and pricing models.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                            <?= esc(ucfirst($mode)) ?> Mode
                        </span>
                        <?php if ($mode === 'edit'): ?>
                            <span class="px-3 py-1 rounded-full <?= ($plan['status'] ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?> text-xs font-semibold">
                                <?= esc(ucfirst($plan['status'] ?? 'active')) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?= $actionUrl ?>" method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <?= csrf_field() ?>
            
            <!-- Main Configuration -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Core Details -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Core Details</h3>
                        <i class="fas fa-tag text-gray-400"></i>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                            <input type="text" name="name" value="<?= esc($plan['name'] ?? '') ?>" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" 
                                   placeholder="e.g. Professional Tier" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="price" value="<?= esc($limits['price'] ?? 0) ?>" min="0" step="0.01"
                                       class="block w-full rounded-lg border-gray-300 pl-7 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" 
                                       placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Billing Duration</label>
                            <select name="duration" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                <?php $duration = $limits['duration'] ?? 'monthly'; ?>
                                <option value="monthly" <?= $duration === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                <option value="yearly" <?= $duration === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                <option value="lifetime" <?= $duration === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                                      placeholder="Public description of this plan..."><?= esc($limits['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Limits & Quotas -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Limits & Quotas</h3>
                        <i class="fas fa-chart-pie text-gray-400"></i>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Patient Limit</label>
                            <input type="number" name="patient_limit" value="<?= esc($limits['patients_active_max'] ?? -1) ?>" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" 
                                   placeholder="-1 for unlimited">
                            <p class="mt-1 text-xs text-gray-500">-1 for unlimited active patients.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User Seats</label>
                            <input type="number" name="user_seats" value="<?= esc($limits['users_max'] ?? -1) ?>" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" 
                                   placeholder="-1 for unlimited">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Storage (GB)</label>
                            <input type="number" name="storage_gb" value="<?= esc($limits['storage_gb'] ?? -1) ?>" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" 
                                   placeholder="-1 for unlimited">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trial Period (Days)</label>
                            <input type="number" name="trial_days" value="<?= esc($limits['trial_days'] ?? 0) ?>" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Export Quota / Mo</label>
                            <input type="number" name="export_quota" value="<?= esc($limits['exports'] ?? -1) ?>" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Quota / Mo</label>
                            <input type="number" name="email_quota" value="<?= esc($limits['notifications_email'] ?? -1) ?>" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Feature Toggles -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Feature Modules</h3>
                        <i class="fas fa-toggle-on text-gray-400"></i>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                                $featuresList = [
                                    'appointments' => 'Appointments',
                                    'finance' => 'Finance & Billing',
                                    'inventory' => 'Inventory Management',
                                    'reports' => 'Advanced Reports',
                                    'email_templates' => 'Email Templates',
                                    'audit_exports' => 'Audit Log Exports',
                                    'chairside_notes' => 'Chairside Notes',
                                    'claims_hub' => 'Insurance Claims',
                                ];
                                foreach ($featuresList as $key => $label):
                                    $checked = !empty($features[$key]) ? 'checked' : '';
                            ?>
                            <label class="relative flex items-start p-4 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="features[<?= $key ?>]" <?= $checked ?> 
                                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-900"><?= esc($label) ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                            
                            <!-- Nested Features -->
                            <label class="relative flex items-start p-4 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="features[exports][enabled]" <?= !empty($features['exports']['enabled']) ? 'checked' : '' ?> 
                                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-900">Data Export Tools</span>
                                </div>
                            </label>
                            <label class="relative flex items-start p-4 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="features[notifications][email][enabled]" <?= !empty($features['notifications']['email']['enabled']) ? 'checked' : '' ?> 
                                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-900">Email Notifications</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Actions & Info) -->
            <div class="space-y-6">
                <!-- Publish Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Publish</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                <?php $status = $plan['status'] ?? 'active'; ?>
                                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500">Inactive plans are hidden from the selection catalog.</p>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-100 flex flex-col gap-3">
                            <button type="submit" class="w-full flex justify-center items-center px-4 py-3 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition-colors shadow-lg">
                                <i class="fas fa-save mr-2"></i>
                                <?= $mode === 'edit' ? 'Update Plan' : 'Create Plan' ?>
                            </button>
                            <a href="<?= base_url('controlplane/plans') ?>" class="w-full flex justify-center items-center px-4 py-3 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="bg-blue-50 rounded-xl border border-blue-100 p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-lightbulb text-blue-500"></i>
                        <h4 class="font-bold text-blue-900 text-sm">Config Tips</h4>
                    </div>
                    <ul class="space-y-3 text-xs text-blue-800">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check mt-0.5 text-blue-400"></i>
                            <span>Use <strong>-1</strong> for unlimited quotas (patients, users, storage).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check mt-0.5 text-blue-400"></i>
                            <span>Price <strong>0</strong> enables free tier behavior.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check mt-0.5 text-blue-400"></i>
                            <span>Feature flags control visibility of sidebar menu items for the tenant.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
