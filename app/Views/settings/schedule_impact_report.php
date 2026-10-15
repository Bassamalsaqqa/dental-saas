<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<style>.impact-recheck{width:100%;max-width:1100px;margin:auto;padding:16px;box-sizing:border-box;overflow-wrap:anywhere}.impact-recheck *{min-width:0;box-sizing:border-box}.impact-recheck .overflow-x-auto{width:100%;overflow-x:auto}.impact-recheck table{min-width:850px}.impact-recheck td,.impact-recheck th{padding:12px}</style>
<div class="impact-recheck max-w-6xl mx-auto px-4 py-8" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
    <!-- Top breadcrumb and title -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="<?= base_url('settings?tab=hours') ?>" class="text-sm font-medium text-orange-600 hover:text-orange-700 flex items-center gap-1">
                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                <span><?= esc(lang('UI.settingsOperations')) ?></span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2"><?= esc(lang('DoctorScheduleSettings.recheckReport')) ?></h1>
            <p class="text-sm text-slate-600 mt-1"><?= esc(lang('DoctorScheduleSettings.recheckReportHelp')) ?></p>
        </div>
        <div class="inline-flex items-center gap-2">
            <a href="<?= base_url('settings?tab=hours') ?>" class="px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-medium text-sm transition-colors shadow-sm">
                <i class="fas fa-cog mr-1"></i> <?= esc(lang('UI.settingsOperations')) ?>
            </a>
        </div>
    </div>

    <?php $unresolvedIds=array_diff($report['affected_appointment_ids'],array_keys($retained_exceptions ?? [])); ?>
    <?php if ($unresolvedIds): ?><p class="rounded-xl border border-amber-300 bg-amber-50 p-4 font-semibold" data-unresolved-impact><?= esc(lang('DoctorScheduleSettings.unresolvedVisit')) ?> (<?= count($unresolvedIds) ?>)</p><?php endif; ?>
    <!-- Latest Audit Card if Available -->
    <?php if (!empty($latest_audit)): ?>
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 mb-8 text-blue-950 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-800"><?= esc(lang('DoctorScheduleSettings.lastAuditTitle')) ?></span>
                <span class="text-xs text-blue-700" dir="ltr"><?= esc((string) ($latest_audit['applied_at'] ?? '')) ?></span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="block text-xs text-blue-600"><?= esc(lang('DoctorScheduleSettings.providers')) ?>:</span>
                    <span class="font-semibold"><?= esc(ucfirst((string) ($latest_audit['target_type'] ?? ''))) ?><?= !empty($latest_audit['target_id']) ? ' #' . esc((string) $latest_audit['target_id']) : '' ?></span>
                </div>
                <div>
                    <span class="block text-xs text-blue-600"><?= esc(lang('DoctorScheduleSettings.retainedExceptionsCount')) ?>:</span>
                    <span class="font-bold text-base" dir="ltr"><?= esc((string) ($latest_audit['affected_count'] ?? 0)) ?></span>
                </div>
                <div>
                    <span class="block text-xs text-blue-600"><?= esc(lang('DoctorScheduleSettings.auditReference')) ?>:</span>
                    <span class="font-mono text-xs truncate block" dir="ltr" title="<?= esc((string) ($latest_audit['state_digest'] ?? '')) ?>">
                        <?= esc(substr((string) ($latest_audit['state_digest'] ?? ''), 0, 16)) ?>...
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-blue-600"><?= esc(lang('ActivityLog.user')) ?>:</span>
                    <span class="font-medium" dir="ltr">Actor #<?= esc((string) ($latest_audit['actor_user_id'] ?? '')) ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($latest_audit)): ?><p><?= esc(lang('DoctorScheduleSettings.auditUnavailable')) ?></p><?php endif; ?>
    <p><?= esc(lang('DoctorScheduleSettings.retainedExceptionsCount')) ?>: <?= count($retained_exceptions ?? []) ?></p>
    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.totalFutureVisits')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1" dir="ltr"><?= esc((string) $report['total_future_visits']) ?></div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold <?= $report['after_conflicts_count'] > 0 ? 'text-amber-600' : 'text-emerald-600' ?> mt-1" dir="ltr">
                <?= esc((string) $report['after_conflicts_count']) ?>
            </div>
            <span class="text-xs text-slate-500"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.compliant')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1" dir="ltr">
                <?= esc((string) ($report['total_future_visits'] - $report['after_conflicts_count'])) ?>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-4 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-lg"><?= esc(lang('DoctorScheduleSettings.recheckReport')) ?> (<?= esc((string) count($report['items'])) ?>)</h2>
            <span class="text-xs text-slate-500" dir="ltr"><?= esc(date('Y-m-d H:i:s')) ?></span>
        </div>

        <?php if (empty($report['items'])): ?>
            <div class="p-8 text-center text-slate-500">
                <?= esc(lang('DoctorScheduleSettings.recheckReportClean')) ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.window')) ?> #</th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.date')) ?> / Time</th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.providers')) ?></th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.afterStatus')) ?></th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.retainReason')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($report['items'] as $item): ?>
                            <?php
                            $isConflict = !$item['after_outcome']['compliant'];
                            $rowBg = $isConflict ? 'bg-amber-50/30' : '';
                            ?>
                            <tr class="<?= $rowBg ?>">
                                <td class="py-3 px-4 font-mono font-semibold text-slate-800 whitespace-nowrap">
                                    <span dir="ltr">#<?= esc((string) $item['appointment_id']) ?></span>
                                    <?php if ($item['status'] === 'completed'): ?>
                                        <span class="block text-xs font-normal text-slate-500"><?= esc(lang('DoctorScheduleSettings.completedPreserved')) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-800" dir="ltr"><?= esc($item['appointment_date']) ?></div>
                                    <div class="text-xs text-slate-500" dir="ltr"><?= esc($item['appointment_time']) ?> – <?= esc($item['end_time']) ?> (<?= esc((string) $item['duration']) ?>m)</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-800"><?= esc($item['provider_name'] ?? $item['chair_name'] ?? '—') ?></div>
                                    <?php if ($item['provider_user_id'] !== null): ?>
                                        <span class="text-xs font-mono text-slate-500" dir="ltr">#<?= esc((string) $item['provider_user_id']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($item['after_outcome']['compliant']): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                            <i class="fas fa-check text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.compliant')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800" title="<?= esc($item['after_outcome']['reason']) ?>">
                                            <i class="fas fa-exclamation text-[10px]"></i> <?= esc($item['after_outcome']['code']) ?>
                                        </span>
                                        <span class="block text-xs text-amber-700 mt-1"><?= esc($item['after_outcome']['reason']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($isConflict && isset($retained_exceptions[(int) $item['appointment_id']])): ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-700">
                                            <i class="fas fa-shield-alt text-amber-600"></i> <?= esc(lang('DoctorScheduleSettings.retainException')) ?>
                                        </span><p><?= esc($retained_exceptions[(int) $item['appointment_id']]['reason']) ?></p>
                                        <?php if (!empty($item['edit_url'])): ?>
                                            <div class="mt-1">
                                                <a href="<?= esc($item['edit_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 hover:text-blue-800 underline">
                                                    <?= esc(lang('DoctorScheduleSettings.rescheduleLink')) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if (has_permission('settings', 'edit')): ?>
<form method="post" action="<?= esc(base_url('settings/preview-schedule-impact'), 'attr') ?>" class="impact-recheck">
<?= csrf_field() ?><input type="hidden" name="target_type" value="clinic_hours"><input type="hidden" name="refresh_preview" value="1">
<input type="hidden" name="proposed_dto_json" value="<?= esc(json_encode($current_dto, JSON_THROW_ON_ERROR), 'attr') ?>">
<button type="submit"><?= esc(lang('DoctorScheduleSettings.reviewCurrent')) ?></button></form>
<?php endif; ?>
<?= $this->endSection() ?>
