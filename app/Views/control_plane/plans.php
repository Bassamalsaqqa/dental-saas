<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cubes text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Plan Catalog</h1>
                            <p class="text-gray-600">Define pricing, limits, and entitlements</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Catalog</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Total -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-200 to-gray-300 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-slate-200/50 group-hover:shadow-slate-300/50 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl text-white shadow-xl" style="background: linear-gradient(to bottom right, #64748b, #4b5563);">
                                <i class="fas fa-layer-group text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Plans</p>
                            <p class="text-3xl font-black text-slate-900"><?= number_format($stats['total'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Active -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-200 to-green-300 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-200/50 group-hover:shadow-emerald-300/50 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl text-white shadow-xl" style="background: linear-gradient(to bottom right, #10b981, #16a34a);">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Active Plans</p>
                            <p class="text-3xl font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($stats['active'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>

                <!-- In View -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-200 to-indigo-300 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-200/50 group-hover:shadow-blue-300/50 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl text-white shadow-xl" style="background: linear-gradient(to bottom right, #3b82f6, #6366f1);">
                                <i class="fas fa-eye text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">In View</p>
                            <p class="text-3xl font-black text-blue-600" style="color: #2563eb !important;"><?= number_format($stats['filtered'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Table Section -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden mb-12">
            <!-- Header & Filters -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Catalog Plans</h3>
                    </div>
                    
                    <form method="get" class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Search plans..."
                                   class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 w-64">
                        </div>

                        <!-- Status Filter -->
                        <select name="status" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white" onchange="this.form.submit()">
                            <?php $currentStatus = $filters['status'] ?? ''; ?>
                            <option value="" <?= $currentStatus === '' ? 'selected' : '' ?>>All Status</option>
                            <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>

                        <!-- Duration Filter -->
                        <select name="duration" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white" onchange="this.form.submit()">
                            <?php $currentDuration = $filters['duration'] ?? ''; ?>
                            <option value="" <?= $currentDuration === '' ? 'selected' : '' ?>>All Durations</option>
                            <option value="monthly" <?= $currentDuration === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $currentDuration === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            <option value="lifetime" <?= $currentDuration === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                        </select>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 border-l border-gray-300 pl-3">
                            <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= base_url('controlplane/plans') ?>" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="Reset">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>

                        <!-- Create Button -->
                        <a href="<?= base_url('controlplane/plans/create') ?>" class="ml-2 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                            <i class="fas fa-plus"></i>
                            Create Plan
                        </a>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cube text-emerald-600"></i>
                                    <span>Plan Name</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-tag text-emerald-600"></i>
                                    <span>Pricing</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-sliders-h text-emerald-600"></i>
                                    <span>Limits</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-emerald-600"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end space-x-2">
                                    <i class="fas fa-cogs text-emerald-600"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($plans)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-cubes text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="font-medium">No plans found</p>
                                        <p class="text-xs mt-1">Create a new plan to populate the catalog.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($plans as $row): ?>
                                <?php
                                    $plan = $row['plan'] ?? [];
                                    $limits = $row['limits'] ?? [];
                                    $price = $limits['price'] ?? 0;
                                    $duration = ucfirst($limits['duration'] ?? 'monthly');
                                    $trial = (int) ($limits['trial_days'] ?? 0);
                                    $isActive = ($plan['status'] ?? 'inactive') === 'active';
                                    
                                    // Format limits
                                    $patients = $limits['patients_active_max'] ?? -1;
                                    $users = $limits['users_max'] ?? -1;
                                    $storage = $limits['storage_gb'] ?? -1;
                                    $limitText = [];
                                    if ($patients === -1) $limitText[] = 'Unlimited Pts'; else $limitText[] = "$patients Pts";
                                    if ($users === -1) $limitText[] = 'Unlimited Users'; else $limitText[] = "$users Users";
                                    if ($storage === -1) $limitText[] = 'Unlimited Storage'; else $limitText[] = "$storage GB";
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-xs">
                                                <?= strtoupper(substr($plan['name'] ?? 'PL', 0, 2)) ?>
                                            </div>
                                            <div>
                                                <span class="block text-sm font-medium text-slate-900"><?= esc($plan['name'] ?? 'Plan') ?></span>
                                                <span class="block text-xs text-slate-500 font-mono">ID: <?= esc($plan['id']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900">$<?= number_format((float) $price, 2) ?></div>
                                        <div class="text-xs text-slate-500">per <?= strtolower($duration) ?></div>
                                        <?php if ($trial > 0): ?>
                                            <div class="text-[10px] text-emerald-600 font-medium mt-0.5">+<?= $trial ?> days trial</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($limitText as $limit): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                    <?= esc($limit) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($isActive): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-check-circle mr-1.5"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                <i class="fas fa-pause-circle mr-1.5"></i> Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Edit -->
                                            <a href="<?= base_url('controlplane/plans/' . $plan['id'] . '/edit') ?>" 
                                               class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200"
                                               title="Edit Plan">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Toggle Status -->
                                            <form action="<?= base_url('controlplane/plans/' . $plan['id'] . '/toggle') ?>" method="post" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="p-2 <?= $isActive ? 'text-amber-600 hover:text-amber-700 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50' ?> rounded-md transition-colors duration-200"
                                                        title="<?= $isActive ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="fas <?= $isActive ? 'fa-pause' : 'fa-play' ?>"></i>
                                                </button>
                                            </form>

                                            <!-- Delete -->
                                            <form action="<?= base_url('controlplane/plans/' . $plan['id'] . '/delete') ?>" method="post" class="inline" onsubmit="return confirm('Are you sure you want to delete this plan? This action cannot be undone.');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="p-2 text-rose-600 hover:text-rose-700 rounded-md hover:bg-rose-50 transition-colors duration-200" title="Delete Plan">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>

<script>
    document.querySelectorAll('[data-plan-toggle]').forEach((toggle) => {
        toggle.addEventListener('change', (event) => {
            const form = event.target.closest('form');
            event.target.setAttribute('aria-checked', event.target.checked ? 'true' : 'false');
            if (form) {
                form.submit();
            }
        });
    });
</script>
<?= $this->endSection() ?>
