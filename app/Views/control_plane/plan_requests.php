<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-inbox text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Plan Requests</h1>
                            <p class="text-gray-600">Review and manage subscription change requests</p>
                        </div>
                    </div>
                    <a href="<?= base_url('controlplane/plans') ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-layer-group"></i>
                        View Plans
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-stretch">
                <!-- Total -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-indigo-500/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-indigo-500 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-indigo-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-list text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-indigo-100 px-3 py-1.5 rounded-full border border-indigo-200">
                                <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                                <span class="text-indigo-700 font-bold text-xs">All Time</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Requests</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-indigo-900"><?= number_format($requestStats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-indigo-100 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-indigo-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">History</p>
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-orange-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <span class="text-amber-700 font-bold text-xs">Queued</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Pending</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-amber-600" style="color: #d97706 !important;"><?= number_format($requestStats['pending'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Awaiting Action</p>
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-green-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span class="text-emerald-700 font-bold text-xs">Finalized</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Approved</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($requestStats['approved'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-emerald-200 to-green-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Provisioned</p>
                        </div>
                    </div>
                </div>

                <!-- Rejected -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-red-500/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-red-500/10 group-hover:shadow-red-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-red-500 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-red-500 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-times text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-red-100 px-3 py-1.5 rounded-full border border-red-200">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                <span class="text-red-700 font-bold text-xs">Declined</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Rejected</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-red-900"><?= number_format($requestStats['rejected'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-red-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-red-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Closed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Unified Table Section -->
    <div class="mb-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
            <!-- Header & Filters -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Request Inbox</h3>
                    </div>
                    
                    <form method="get" class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="request_q" value="<?= esc($requestFilters['q'] ?? '') ?>" placeholder="Search requests..."
                                   class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 w-64">
                        </div>

                        <!-- Status Filter -->
                        <select name="request_status" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white" onchange="this.form.submit()">
                            <?php $currentReqStatus = $requestFilters['status'] ?? ''; ?>
                            <option value="" <?= $currentReqStatus === '' ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= $currentReqStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $currentReqStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $currentReqStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 border-l border-gray-300 pl-3">
                            <button type="submit" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= base_url('controlplane/plan-requests') ?>" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="Reset">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>

                        <!-- Queue Request Button -->
                        <button type="button" onclick="openQueueModal()" class="ml-2 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                            <i class="fas fa-plus"></i>
                            Queue Request
                        </button>
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
                                    <i class="fas fa-hospital text-blue-600"></i>
                                    <span>Clinic</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-blue-600"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-exchange-alt text-blue-600"></i>
                                    <span>Details</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-calendar text-blue-600"></i>
                                    <span>Requested</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end space-x-2">
                                    <i class="fas fa-cogs text-blue-600"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($planRequests)): ?>
                            <?php foreach ($planRequests as $request): ?>
                                <?php $status = $request['status'] ?? ''; ?>
                                <tr class="hover:bg-gray-50 transition-colors" data-request-row>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                                <?= esc(ui_badge_text($request['clinic_name'] ?? '', 2, 'CL')) ?>
                                            </div>
                                            <span class="text-sm font-medium text-slate-900"><?= esc($request['clinic_name'] ?? 'Clinic') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            $badgeColor = match($status) {
                                                'pending' => 'bg-amber-100 text-amber-800',
                                                'approved' => 'bg-emerald-100 text-emerald-800',
                                                'rejected' => 'bg-rose-100 text-rose-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            $icon = match($status) {
                                                'pending' => 'fas fa-clock',
                                                'approved' => 'fas fa-check-circle',
                                                'rejected' => 'fas fa-times-circle',
                                                default => 'fas fa-question-circle'
                                            };
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeColor ?>">
                                            <i class="<?= $icon ?> mr-1.5"></i>
                                            <?= esc(ucfirst($status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <span class="text-gray-500">From:</span> <?= esc($request['current_plan_name'] ?? 'None') ?>
                                            <i class="fas fa-arrow-right text-gray-400 mx-1 text-xs"></i>
                                            <span class="font-medium"><?= esc($request['requested_plan_name'] ?? 'None') ?></span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <?= esc(ucfirst($request['requested_duration'] ?? 'monthly')) ?> billing
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= esc($request['created_at'] ?? '-') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <?php if ($status === 'pending'): ?>
                                                <form action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/approve') ?>" method="post" data-approve-form class="inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="expected_requested_plan_id" value="<?= esc($request['requested_plan_id']) ?>">
                                                    <input type="hidden" name="expected_requested_duration" value="<?= esc($request['requested_duration'] ?? 'monthly') ?>">
                                                    <button type="button" data-approve-button class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="Approve Request">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" 
                                                        class="p-2 text-rose-600 hover:text-rose-700 rounded-md hover:bg-rose-50 transition-colors duration-200"
                                                        data-reject-action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/reject') ?>"
                                                        data-request-label="<?= esc($request['clinic_name'] ?? 'Clinic') ?>"
                                                        title="Reject Request">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="p-2 text-rose-600 hover:text-rose-700 rounded-md hover:bg-rose-50 transition-colors duration-200"
                                                        data-delete-action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/delete') ?>"
                                                        data-request-label="<?= esc($request['clinic_name'] ?? 'Clinic') ?>"
                                                        title="Delete Request">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-inbox text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="font-medium">No requests found</p>
                                        <p class="text-xs mt-1">New subscription change requests will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Queue Request Modal -->
<div id="queueModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Queue Manual Request</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeQueueModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/plan-requests') ?>" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Clinic</label>
                <select name="clinic_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    <option value="">Select clinic...</option>
                    <?php foreach (($requestClinics ?? []) as $clinic): ?>
                        <option value="<?= esc($clinic['id']) ?>"><?= esc($clinic['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Plan</label>
                <select name="requested_plan_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    <option value="">Select plan...</option>
                    <?php foreach (($requestPlans ?? []) as $plan): ?>
                        <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                <select name="requested_duration" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="lifetime">Lifetime</option>
                </select>
            </div>
            
            <div class="pt-4 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors" onclick="closeQueueModal()">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 shadow-md transition-colors">
                    Queue Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openQueueModal() {
    const modal = document.getElementById('queueModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeQueueModal() {
    const modal = document.getElementById('queueModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
</script>

<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
    <div class="glass-card rounded-2xl p-6 w-full max-w-md max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold">Reject Request</p>
                <h3 class="text-lg font-semibold text-slate-900 mt-1">Add a rejection reason</h3>
                <p class="text-xs text-slate-500 mt-1" id="reject-label">This request will be closed.</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-reject-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reject-form" method="post" class="mt-4 space-y-4 pb-10">
            <?= csrf_field() ?>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Reason</label>
                <textarea name="decision_reason" id="reject-reason" required rows="3"
                          class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                          placeholder="Optional context for the clinic."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 sticky bottom-0 left-0 right-0 bg-white/80 backdrop-blur border-t border-white/40 pt-3 -mx-6 -mb-6 px-6">
                <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold" data-reject-close>Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
    <div class="glass-card rounded-2xl p-6 w-full max-w-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold">Delete Request</p>
                <h3 class="text-lg font-semibold text-slate-900 mt-1">Remove this request</h3>
                <p class="text-xs text-slate-500 mt-1" id="delete-label">This request will be removed permanently.</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-delete-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="delete-form" method="post" class="mt-4 space-y-4">
            <?= csrf_field() ?>
            <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                This action cannot be undone.
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold" data-delete-close>Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    const rejectModal = document.getElementById('reject-modal');
    const rejectForm = document.getElementById('reject-form');
    const rejectReason = document.getElementById('reject-reason');
    const rejectLabel = document.getElementById('reject-label');
    let activeRejectRow = null;
    const deleteModal = document.getElementById('delete-modal');
    const deleteForm = document.getElementById('delete-form');
    const deleteLabel = document.getElementById('delete-label');
    let activeDeleteRow = null;

    document.querySelectorAll('[data-approve-form]').forEach((form) => {
        const button = form.querySelector('[data-approve-button]');
        if (!button) {
            return;
        }
        button.addEventListener('click', () => {
            const row = form.closest('[data-request-row]');
            if (row) {
                row.classList.add('opacity-40');
                row.classList.add('pointer-events-none');
            }
            form.submit();
        });
    });

    document.querySelectorAll('[data-reject-action]').forEach((button) => {
        button.addEventListener('click', () => {
            activeRejectRow = button.closest('[data-request-row]');
            const action = button.getAttribute('data-reject-action');
            if (action && rejectForm) {
                rejectForm.action = action;
            }
            if (rejectLabel) {
                rejectLabel.textContent = button.getAttribute('data-request-label') || 'This request will be closed.';
            }
            if (rejectReason) {
                rejectReason.value = '';
            }
            if (rejectModal) {
                rejectModal.classList.remove('hidden');
                rejectModal.classList.add('flex');
            }
            if (rejectReason) {
                rejectReason.focus();
            }
        });
    });

    document.querySelectorAll('[data-reject-close]').forEach((button) => {
        button.addEventListener('click', () => {
            if (rejectModal) {
                rejectModal.classList.add('hidden');
                rejectModal.classList.remove('flex');
            }
            activeRejectRow = null;
        });
    });

    if (rejectForm) {
        rejectForm.addEventListener('submit', () => {
            if (activeRejectRow) {
                activeRejectRow.classList.add('opacity-40');
                activeRejectRow.classList.add('pointer-events-none');
            }
        });
    }

    document.querySelectorAll('[data-delete-action]').forEach((button) => {
        button.addEventListener('click', () => {
            activeDeleteRow = button.closest('[data-request-row]');
            const action = button.getAttribute('data-delete-action');
            if (action && deleteForm) {
                deleteForm.action = action;
            }
            if (deleteLabel) {
                deleteLabel.textContent = button.getAttribute('data-request-label') || 'This request will be removed permanently.';
            }
            if (deleteModal) {
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
            }
        });
    });

    document.querySelectorAll('[data-delete-close]').forEach((button) => {
        button.addEventListener('click', () => {
            if (deleteModal) {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
            }
            activeDeleteRow = null;
        });
    });

    if (deleteForm) {
        deleteForm.addEventListener('submit', () => {
            if (activeDeleteRow) {
                activeDeleteRow.classList.add('opacity-40');
                activeDeleteRow.classList.add('pointer-events-none');
            }
        });
    }
</script>
<?= $this->endSection() ?>
