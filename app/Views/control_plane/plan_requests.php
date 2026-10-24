<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="v3-platform-workspace min-h-screen relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">



    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <div class="mb-4">
            <div class="cp-surface border cp-border cp-radius p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 cp-radius flex items-center justify-center text-white">
                            <i class="fas fa-inbox text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold text-gray-900"><?= esc(lang('V3Platform.planRequests')) ?></h1>
                            <p class="text-gray-600"><?= esc(lang('V3Platform.reviewAndManageSubscriptionChangeRequests')) ?></p>
                        </div>
                    </div>
                    <a href="<?= base_url('controlplane/plans') ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-lg transition-all duration-200">
                        <i class="fas fa-layer-group"></i>
                        <?= esc(lang('V3Platform.viewPlans')) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-stretch">
                <!-- Total -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius cp-primary-surface text-white transition-all duration-200">
                                    <i class="fas fa-list text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 cp-soft-surface px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.allTime')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.totalRequests')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black cp-accent"><?= number_format($requestStats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 cp-soft-surface rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full cp-primary-surface rounded-full"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.history')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                <span class="text-amber-700 font-bold text-xs"><?= esc(lang('V3Platform.queued')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.pending')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black text-amber-600" style="color: #d97706 !important;"><?= number_format($requestStats['pending'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.awaitingAction')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="text-emerald-700 font-bold text-xs"><?= esc(lang('V3Platform.finalized')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.approved')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($requestStats['approved'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.provisioned')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Rejected -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius bg-red-500 text-white transition-all duration-200">
                                    <i class="fas fa-times text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-red-100 px-3 py-1.5 rounded-full border border-red-200">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <span class="text-red-700 font-bold text-xs"><?= esc(lang('V3Platform.declined')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.rejected')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black text-red-900"><?= number_format($requestStats['rejected'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-red-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-red-500 rounded-full"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.closed')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Unified Table Section -->
    <div class="mb-4">
        <div class="cp-surface cp-radius border cp-border overflow-hidden">
            <!-- Header & Filters -->
            <div class="px-6 py-4 border-b cp-border cp-surface">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('V3Platform.requestInbox')) ?></h3>
                    </div>

                    <form method="get" class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="request_q" value="<?= esc($requestFilters['q'] ?? '') ?>" placeholder="<?= esc(lang('V3Platform.searchRequests'), 'attr') ?>"
                                   class="pl-10 pr-4 py-2 rounded-lg border cp-border text-sm focus:ring-blue-500 focus:border-blue-500 w-64">
                        </div>

                        <!-- Status Filter -->
                        <select name="request_status" class="py-2 pl-3 pr-8 rounded-lg border cp-border text-sm focus:ring-blue-500 focus:border-blue-500 cp-surface" onchange="this.form.submit()">
                            <?php $currentReqStatus = $requestFilters['status'] ?? ''; ?>
                            <option value="" <?= $currentReqStatus === '' ? 'selected' : '' ?>><?= esc(lang('V3Platform.allStatus')) ?></option>
                            <option value="pending" <?= $currentReqStatus === 'pending' ? 'selected' : '' ?>><?= esc(lang('V3Platform.pending')) ?></option>
                            <option value="approved" <?= $currentReqStatus === 'approved' ? 'selected' : '' ?>><?= esc(lang('V3Platform.approved')) ?></option>
                            <option value="rejected" <?= $currentReqStatus === 'rejected' ? 'selected' : '' ?>><?= esc(lang('V3Platform.rejected')) ?></option>
                        </select>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 border-l cp-border pl-3">
                            <button type="submit" class="p-2 cp-accent hover:bg-blue-50 rounded-lg transition-colors" title="<?= esc(lang('V3Platform.filter'), 'attr') ?>">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= base_url('controlplane/plan-requests') ?>" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="<?= esc(lang('V3Platform.reset'), 'attr') ?>">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>

                        <!-- Queue Request Button -->
                        <button type="button" onclick="openQueueModal()" class="ml-2 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors">
                            <i class="fas fa-plus"></i>
                            <?= esc(lang('V3Platform.queueRequest')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="cp-surface">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-hospital cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.clinic')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.status')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-exchange-alt cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.details')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-calendar cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.requested')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end space-x-2">
                                    <i class="fas fa-cogs cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.actions')) ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="cp-surface divide-y divide-gray-200">
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
                                            <span class="text-gray-500"><?= esc(lang('V3Platform.from')) ?></span> <?= esc($request['current_plan_name'] ?? 'None') ?>
                                            <i class="fas fa-arrow-right text-gray-400 mx-1 text-xs"></i>
                                            <span class="font-medium"><?= esc($request['requested_plan_name'] ?? 'None') ?></span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <?= esc(ucfirst($request['requested_duration'] ?? 'monthly')) ?> <?= esc(lang('V3Platform.billing')) ?>
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
                                                    <button type="button" data-approve-button class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="<?= esc(lang('V3Platform.approveRequest'), 'attr') ?>">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button"
                                                        class="p-2 text-rose-600 hover:text-rose-700 rounded-md hover:bg-rose-50 transition-colors duration-200"
                                                        data-reject-action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/reject') ?>"
                                                        data-request-label="<?= esc($request['clinic_name'] ?? 'Clinic') ?>"
                                                        title="<?= esc(lang('V3Platform.rejectRequest'), 'attr') ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="p-2 text-rose-600 hover:text-rose-700 rounded-md hover:bg-rose-50 transition-colors duration-200"
                                                        data-delete-action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/delete') ?>"
                                                        data-request-label="<?= esc($request['clinic_name'] ?? 'Clinic') ?>"
                                                        title="<?= esc(lang('V3Platform.deleteRequest'), 'attr') ?>">
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
                                        <p class="font-medium"><?= esc(lang('V3Platform.noRequestsFound')) ?></p>
                                        <p class="text-xs mt-1"><?= esc(lang('V3Platform.newSubscriptionChangeRequestsWillAppearHere')) ?></p>
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
<div id="queueModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4">
    <div class="w-full max-w-lg cp-radius cp-surface border cp-border">
        <div class="px-6 py-4 border-b cp-border flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900"><?= esc(lang('V3Platform.queueManualRequest')) ?></h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeQueueModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?= base_url('controlplane/plan-requests') ?>" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('V3Platform.clinic')) ?></label>
                <select name="clinic_id" class="w-full rounded-lg cp-border focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    <option value=""><?= esc(lang('V3Platform.selectClinic')) ?></option>
                    <?php foreach (($requestClinics ?? []) as $clinic): ?>
                        <option value="<?= esc($clinic['id']) ?>"><?= esc($clinic['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('V3Platform.targetPlan')) ?></label>
                <select name="requested_plan_id" class="w-full rounded-lg cp-border focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    <option value=""><?= esc(lang('V3Platform.selectPlan')) ?></option>
                    <?php foreach (($requestPlans ?? []) as $plan): ?>
                        <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('V3Platform.duration')) ?></label>
                <select name="requested_duration" class="w-full rounded-lg cp-border focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="monthly"><?= esc(lang('V3Platform.monthly')) ?></option>
                    <option value="yearly"><?= esc(lang('V3Platform.yearly')) ?></option>
                    <option value="lifetime"><?= esc(lang('V3Platform.lifetime')) ?></option>
                </select>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors" onclick="closeQueueModal()">
                    <?= esc(lang('V3Platform.cancel')) ?>
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg cp-primary-surface text-white text-sm font-bold hover:bg-blue-700 transition-colors">
                    <?= esc(lang('V3Platform.queueRequest')) ?>
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

<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40">
    <div class="glass-card cp-radius p-6 w-full max-w-md max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold"><?= esc(lang('V3Platform.rejectRequest')) ?></p>
                <h3 class="text-lg font-semibold text-slate-900 mt-1"><?= esc(lang('V3Platform.addARejectionReason')) ?></h3>
                <p class="text-xs text-slate-500 mt-1" id="reject-label"><?= esc(lang('V3Platform.thisRequestWillBeClosed')) ?></p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-reject-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reject-form" method="post" class="mt-4 space-y-4 pb-10">
            <?= csrf_field() ?>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold"><?= esc(lang('V3Platform.reason')) ?></label>
                <textarea name="decision_reason" id="reject-reason" required rows="3"
                          class="mt-2 w-full cp-radius border cp-border px-3 py-2 text-sm"
                          placeholder="<?= esc(lang('V3Platform.optionalContextForTheClinic'), 'attr') ?>"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 sticky bottom-0 left-0 right-0 cp-surface border-t cp-border pt-3 -mx-6 -mb-6 px-6">
                <button type="button" class="px-4 py-2 cp-radius bg-slate-100 text-slate-600 text-sm font-semibold" data-reject-close><?= esc(lang('V3Platform.cancel')) ?></button>
                <button type="submit" class="px-4 py-2 cp-radius bg-rose-600 text-white text-sm font-semibold"><?= esc(lang('V3Platform.confirmReject')) ?></button>
            </div>
        </form>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40">
    <div class="glass-card cp-radius p-6 w-full max-w-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold"><?= esc(lang('V3Platform.deleteRequest')) ?></p>
                <h3 class="text-lg font-semibold text-slate-900 mt-1"><?= esc(lang('V3Platform.removeThisRequest')) ?></h3>
                <p class="text-xs text-slate-500 mt-1" id="delete-label"><?= esc(lang('V3Platform.thisRequestWillBeRemovedPermanently')) ?></p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-delete-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="delete-form" method="post" class="mt-4 space-y-4">
            <?= csrf_field() ?>
            <div class="cp-radius border border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                <?= esc(lang('V3Platform.thisActionCannotBeUndone')) ?>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" class="px-4 py-2 cp-radius bg-slate-100 text-slate-600 text-sm font-semibold" data-delete-close><?= esc(lang('V3Platform.cancel')) ?></button>
                <button type="submit" class="px-4 py-2 cp-radius bg-rose-600 text-white text-sm font-semibold"><?= esc(lang('V3Platform.delete')) ?></button>
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
