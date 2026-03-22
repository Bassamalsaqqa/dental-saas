<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$waPolicy = is_array($wa_policy ?? null) ? $wa_policy : [];
$waPolicyToggles = is_array($wa_policy_toggles ?? null) ? $wa_policy_toggles : [];
$waTriggerCatalog = is_array($wa_trigger_catalog ?? null) ? $wa_trigger_catalog : [];
$waGlobalTemplates = is_array($wa_global_templates ?? null) ? $wa_global_templates : [];
$waGlobalTriggerTemplates = is_array($waGlobalTemplates['trigger_templates'] ?? null) ? $waGlobalTemplates['trigger_templates'] : [];
$waGlobalTriggerToggles = is_array($waGlobalTemplates['trigger_toggles'] ?? null) ? $waGlobalTemplates['trigger_toggles'] : [];
$clinicSettingsSnapshot = is_array($clinic_settings_snapshot ?? null) ? $clinic_settings_snapshot : [];
?>
<style>
    .ui-toggle {
        appearance: none;
        width: 3.5rem;
        height: 2rem;
        border-radius: 9999px;
        background: #dbeafe;
        position: relative;
        cursor: pointer;
        transition: all .2s ease;
        border: 1px solid rgba(59,130,246,.18);
    }
    .ui-toggle::before {
        content: '';
        position: absolute;
        top: .2rem;
        left: .2rem;
        width: 1.4rem;
        height: 1.4rem;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15,23,42,.16);
        transition: all .2s ease;
    }
    .ui-toggle:checked {
        background: linear-gradient(135deg,#10b981,#059669);
        border-color: transparent;
    }
    .ui-toggle:checked::before {
        transform: translateX(1.45rem);
    }
    .ui-toggle:disabled {
        opacity: .55;
        cursor: not-allowed;
    }
</style>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Clinic Overview</h1>
                <p class="text-gray-600 text-lg"><?= esc($clinic['name']) ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('controlplane/clinics') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Clinics
                </a>
                <form action="<?= base_url('controlplane/impersonate') ?>" method="post" class="inline-block">
                    <?= csrf_field() ?>
                    <input type="hidden" name="clinic_id" value="<?= esc($clinic['id']) ?>">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login As
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Success</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p><?= session()->getFlashdata('success') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Clinic Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Clinic Header -->
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-8 text-white">
                    <div class="text-center">
                        <div class="w-24 h-24 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm mx-auto mb-4 shadow-inner">
                            <i class="fas fa-hospital text-white text-4xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold"><?= esc($clinic['name']) ?></h2>
                        <p class="text-emerald-100 text-lg">ID: <?= esc($clinic['id']) ?></p>
                        
                        <div class="mt-4">
                            <?php
                                $status = $clinic['status'] ?? 'inactive';
                                $statusColors = [
                                    'active' => 'bg-green-400 text-green-900',
                                    'suspended' => 'bg-yellow-400 text-yellow-900',
                                    'inactive' => 'bg-gray-400 text-gray-900'
                                ];
                                $badgeClass = $statusColors[$status] ?? 'bg-gray-400 text-gray-900';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?= $badgeClass ?> shadow-sm">
                                <?= ucfirst($status) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Clinic Info -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-globe text-blue-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500">Slug</p>
                                <p class="text-sm font-semibold text-gray-900 truncate"><?= esc($clinic['slug'] ?? '--') ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar text-purple-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500">Created</p>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($clinic['created_at'] ?? '--') ?></p>
                            </div>
                        </div>

                        <!-- Admin Info -->
                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Primary Admin</h4>
                                <button onclick="openPasswordResetModal()" class="text-xs font-semibold text-indigo-200 hover:text-white transition-colors">
                                    Reset Password
                                </button>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user-shield text-orange-600 text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate"><?= esc($clinic['admin_name'] ?? 'Unknown') ?></p>
                                    <p class="text-xs text-emerald-100 truncate"><?= esc($clinic['admin_email'] ?? 'Unknown') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clinic Details -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Subscription Information -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-layer-group text-indigo-600 mr-2"></i>
                        Subscription
                    </h3>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                        <?= esc($subscription['status'] ?? 'None') ?>
                    </span>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Current Plan</label>
                            <p class="text-xl font-bold text-gray-900"><?= esc($plan_name ?? 'Unassigned') ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Billing Cycle</label>
                            <div class="flex space-x-4 text-sm">
                                <div>
                                    <span class="text-gray-400">Start:</span>
                                    <span class="font-medium text-gray-900"><?= !empty($subscription['start_at']) ? date('M j, Y', strtotime($subscription['start_at'])) : '--' ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-400">End:</span>
                                    <span class="font-medium text-gray-900"><?= !empty($subscription['end_at']) ? date('M j, Y', strtotime($subscription['end_at'])) : '--' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lifecycle Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-700 mb-4">Subscription Actions</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Start/Change Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/start') ?>" method="post" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <?= csrf_field() ?>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Change Plan</label>
                                <div class="flex gap-2">
                                    <select name="plan_id" class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                        <option value="">Select plan...</option>
                                        <?php foreach (($plans ?? []) as $plan): ?>
                                            <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-colors">
                                        Start
                                    </button>
                                </div>
                            </form>

                            <!-- Extend Subscription -->
                            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/extend') ?>" method="post" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <?= csrf_field() ?>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Extend Duration</label>
                                <div class="flex gap-2">
                                    <select name="duration" class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="monthly">1 Month</option>
                                        <option value="yearly">1 Year</option>
                                    </select>
                                    <button class="px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors">
                                        Extend
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Snapshot -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-green-600 mr-2"></i>
                        Billing Snapshot
                    </h3>
                    <a href="<?= base_url('controlplane/invoices') ?>" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View All Invoices</a>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-red-50 p-4 rounded-lg border border-red-100 text-center">
                            <p class="text-xs font-bold text-red-500 uppercase tracking-wider">Outstanding</p>
                            <p class="text-2xl font-bold text-red-700 mt-1">$<?= number_format($invoiceStats['outstanding'] ?? 0, 2) ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100 text-center">
                            <p class="text-xs font-bold text-green-500 uppercase tracking-wider">Revenue (Mo)</p>
                            <p class="text-2xl font-bold text-green-700 mt-1">$<?= number_format($invoiceStats['revenue_month'] ?? 0, 2) ?></p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-100 text-center">
                            <p class="text-xs font-bold text-orange-500 uppercase tracking-wider">Overdue</p>
                            <p class="text-2xl font-bold text-orange-700 mt-1"><?= (int)($invoiceStats['overdue'] ?? 0) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($invoices)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3">Invoice #</th>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3">Amount</th>
                                        <th class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach (array_slice($invoices, 0, 5) as $inv): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 font-medium text-indigo-600">
                                                <a href="<?= base_url('controlplane/invoices/' . $inv['id']) ?>"><?= esc($inv['invoice_number']) ?></a>
                                            </td>
                                            <td class="px-4 py-3"><?= esc($inv['issue_date']) ?></td>
                                            <td class="px-4 py-3 font-semibold text-gray-900">$<?= number_format($inv['total_amount'], 2) ?></td>
                                            <td class="px-4 py-3">
                                                <?php
                                                    $iStatus = $inv['status'] ?? 'draft';
                                                    $iClass = match($iStatus) {
                                                        'paid' => 'bg-green-100 text-green-700',
                                                        'overdue' => 'bg-red-100 text-red-700',
                                                        'sent' => 'bg-blue-100 text-blue-700',
                                                        default => 'bg-gray-100 text-gray-600'
                                                    };
                                                ?>
                                                <span class="px-2 py-1 rounded-full text-xs font-bold <?= $iClass ?>"><?= ucfirst($iStatus) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-gray-400">
                            <p>No invoices found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fab fa-whatsapp text-emerald-600 mr-2"></i>
                        WhatsApp Rollout
                    </h3>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full <?= !empty($wa_feature_enabled) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= !empty($wa_feature_enabled) ? 'Plan Enabled' : 'Plan Disabled' ?>
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Clinic Name In Messages</p>
                            <p class="mt-2 text-sm font-bold text-gray-900"><?= esc(($clinicSettingsSnapshot['clinic_name'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_name'] : ($clinic['name'] ?? 'Not set')) ?></p>
                        </div>
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Clinic Phone In Messages</p>
                            <p class="mt-2 text-sm font-bold text-gray-900"><?= esc(($clinicSettingsSnapshot['clinic_phone'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_phone'] : 'Not set') ?></p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Clinic Address In Messages</p>
                            <p class="mt-2 text-sm font-bold text-gray-900"><?= esc(($clinicSettingsSnapshot['clinic_address'] ?? '') !== '' ? $clinicSettingsSnapshot['clinic_address'] : 'Not set') ?></p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4">
                        <p class="text-sm font-semibold text-gray-900">Template ownership</p>
                        <p class="mt-1 text-sm text-gray-600">The 5 Arabic WhatsApp templates are edited centrally in control plane settings. This clinic page only controls whether each template-trigger is allowed to run for this clinic, plus rate limits and emergency pause.</p>
                    </div>

                    <form action="<?= base_url('controlplane/whatsapp/policy') ?>" method="post" class="space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="clinic_id" value="<?= (int) $clinic['id'] ?>">

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Daily Limit</label>
                                <input type="number" name="daily_limit" value="<?= (int) ($waPolicy['daily_limit'] ?? 50) ?>" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Monthly Limit</label>
                                <input type="number" name="monthly_limit" value="<?= (int) ($waPolicy['monthly_limit'] ?? 1000) ?>" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Per Hour Limit</label>
                                <input type="number" name="per_hour_limit" value="<?= (int) ($waPolicy['per_hour_limit'] ?? 20) ?>" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Reminder Lead (Minutes)</label>
                                <input type="number" name="reminder_lead_time_minutes" value="<?= (int) ($waPolicy['reminder_lead_time_minutes'] ?? 1440) ?>" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                            <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                                <?php
                                $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1;
                                $clinicEnabled = (int) ($waPolicyToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1;
                                $triggerConfig = is_array($waGlobalTriggerTemplates[$triggerKey] ?? null) ? $waGlobalTriggerTemplates[$triggerKey] : [];
                                $displayTemplateName = trim((string) ($triggerConfig['name'] ?? '')) !== '' ? (string) $triggerConfig['name'] : (string) ($triggerMeta['suggested_template_name'] ?? ($triggerKey . '_ar_v1'));
                                ?>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-bold text-gray-900"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                            <p class="mt-1 text-xs text-gray-500"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                            <p class="mt-3 text-xs font-semibold text-gray-700">Template: <span class="font-mono"><?= esc($displayTemplateName) ?></span></p>
                                            <?php if (!$platformEnabled): ?>
                                                <p class="mt-2 text-[11px] font-bold uppercase tracking-wider text-rose-600">Disabled globally in control plane settings</p>
                                            <?php endif; ?>
                                        </div>
                                        <input type="checkbox" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1" class="ui-toggle" <?= $clinicEnabled ? 'checked' : '' ?> <?= !$platformEnabled || empty($wa_feature_enabled) ? 'disabled' : '' ?>>
                                    </div>
                                    <?php if ((!$platformEnabled || empty($wa_feature_enabled)) && $clinicEnabled): ?>
                                        <input type="hidden" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-[auto,1fr,auto] gap-4 items-end pt-2 border-t border-gray-100">
                            <label class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                <input type="checkbox" name="suspended" value="1" class="ui-toggle" <?= !empty($waPolicy['suspended']) ? 'checked' : '' ?>>
                                <span class="text-sm font-bold text-gray-800">Emergency Pause Clinic</span>
                            </label>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pause Reason</label>
                                <input type="text" name="suspended_reason" value="<?= esc($waPolicy['suspended_reason'] ?? '') ?>" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Optional reason for audit trail">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 shadow-md transition-colors">
                                    Save WhatsApp Policy
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 rounded-xl shadow-lg border border-red-200 p-6">
                <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Danger Zone
                </h3>
                
                <div class="flex flex-wrap gap-4">
                    <?php if (($clinic['status'] ?? 'inactive') === 'active'): ?>
                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="px-4 py-2 bg-yellow-500 text-white text-sm font-bold rounded-lg hover:bg-yellow-600 shadow-md transition-colors">
                                Suspend Clinic
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="px-4 py-2 bg-green-500 text-white text-sm font-bold rounded-lg hover:bg-green-600 shadow-md transition-colors">
                                Reactivate Clinic
                            </button>
                        </form>
                    <?php endif; ?>

                    <button type="button" class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 shadow-md transition-colors" onclick="openTerminateModal()">
                        Terminate Clinic
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terminate Modal -->
<div id="terminateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all scale-100">
        <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-red-800">Confirm Termination</h3>
            <button onclick="closeTerminateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">
                This will disable access and cancel the subscription for <strong class="text-gray-900"><?= esc($clinic['name']) ?></strong>. 
                <br>Please type the clinic name below to confirm.
            </p>
            <input type="text" id="terminateConfirmInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4" placeholder="<?= esc($clinic['name']) ?>" oninput="checkTerminateInput()">
            
            <form id="terminateForm" action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/terminate') ?>" method="post">
                <?= csrf_field() ?>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200" onclick="closeTerminateModal()">Cancel</button>
                    <button type="submit" id="terminateBtn" class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 opacity-50 cursor-not-allowed" disabled>Terminate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordResetModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all scale-100">
        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-indigo-800">Reset Admin Password</h3>
            <button onclick="closePasswordResetModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">
                Enter a new password for <strong><?= esc($clinic['admin_email'] ?? 'the admin') ?></strong>.
            </p>
            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reset-admin-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">New Password</label>
                    <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required minlength="8" placeholder="Minimum 8 characters">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200" onclick="closePasswordResetModal()">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const clinicName = "<?= esc($clinic['name']) ?>";
    
    function openTerminateModal() {
        document.getElementById('terminateModal').classList.remove('hidden');
        document.getElementById('terminateModal').classList.add('flex');
    }
    
    function closeTerminateModal() {
        document.getElementById('terminateModal').classList.add('hidden');
        document.getElementById('terminateModal').classList.remove('flex');
        document.getElementById('terminateConfirmInput').value = '';
        checkTerminateInput();
    }
    
    function checkTerminateInput() {
        const input = document.getElementById('terminateConfirmInput').value;
        const btn = document.getElementById('terminateBtn');
        
        if (input === clinicName) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    function openPasswordResetModal() {
        document.getElementById('passwordResetModal').classList.remove('hidden');
        document.getElementById('passwordResetModal').classList.add('flex');
    }

    function closePasswordResetModal() {
        document.getElementById('passwordResetModal').classList.add('hidden');
        document.getElementById('passwordResetModal').classList.remove('flex');
    }
</script>
<?= $this->endSection() ?>
