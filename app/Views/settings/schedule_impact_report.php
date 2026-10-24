<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<div class="denta-settings-workspace impact-recheck max-w-6xl mx-auto px-4 py-8" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
    <!-- Top breadcrumb and title -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="<?= base_url('settings?tab=hours') ?>" class="text-sm font-medium v3-text-warning v3-hover-text-warning flex items-center gap-1">
                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                <span><?= esc(lang('DoctorScheduleSettings.backToHours')) ?></span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold v3-text-ink mt-2"><?= esc(lang('DoctorScheduleSettings.recheckReport')) ?></h1>
            <p class="text-sm v3-text-muted mt-1"><?= esc(lang('DoctorScheduleSettings.recheckReportHelp')) ?></p>
        </div>
        <div class="inline-flex items-center gap-2">
            <a href="<?= base_url('settings?tab=hours') ?>" class="px-4 py-2 v3-radius border v3-border v3-surface v3-text-ink v3-hover-soft font-medium text-sm transition-colors">
                <i class="fas fa-cog mr-1"></i> <?= esc(lang('DoctorScheduleSettings.backToHours')) ?>
            </a>
        </div>
    </div>

    <?php $unresolvedIds=array_diff($report['affected_appointment_ids'],array_keys($retained_exceptions ?? [])); ?>
    <?php if ($unresolvedIds): ?><p class="v3-radius border v3-border-default v3-bg-waiting p-4 font-semibold" data-unresolved-impact><?= esc(lang('DoctorScheduleSettings.unresolvedVisit')) ?> (<?= count($unresolvedIds) ?>)</p><?php endif; ?>
    <!-- Latest Audit Card if Available -->
    <?php if (!empty($latest_audit)): ?>
        <div class="v3-corner border v3-border v3-soft-surface p-5 mb-8 v3-accent-text">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider v3-accent-text"><?= esc(lang('DoctorScheduleSettings.lastAuditTitle')) ?></span>
                <span class="text-xs v3-accent-text" dir="ltr"><?= esc((string) ($latest_audit['applied_at'] ?? '')) ?></span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="block text-xs v3-accent-text"><?= esc(lang('DoctorScheduleSettings.changedSetting')) ?>:</span>
                    <span class="font-semibold"><?= esc(lang('DoctorScheduleSettings.' . (($latest_audit['target_type'] ?? '') === 'provider_profile' ? 'doctorAvailability' : 'clinicHours'))) ?><?= !empty($latest_audit['target_id']) ? ' #' . esc((string) $latest_audit['target_id']) : '' ?></span>
                </div>
                <div>
                    <span class="block text-xs v3-accent-text"><?= esc(lang('DoctorScheduleSettings.retainedExceptionsCount')) ?>:</span>
                    <span class="font-bold text-base" dir="ltr"><?= esc((string) ($latest_audit['affected_count'] ?? 0)) ?></span>
                </div>
                <div>
                    <span class="block text-xs v3-accent-text"><?= esc(lang('DoctorScheduleSettings.auditReference')) ?>:</span>
                    <span class="font-mono text-xs truncate block" dir="ltr" title="<?= esc((string) ($latest_audit['state_digest'] ?? '')) ?>">
                        <?= esc(substr((string) ($latest_audit['state_digest'] ?? ''), 0, 16)) ?>...
                    </span>
                </div>
                <div>
                    <span class="block text-xs v3-accent-text"><?= esc(lang('ActivityLog.user')) ?>:</span>
                    <span class="font-medium" dir="ltr"><?= esc(lang('DoctorScheduleSettings.actor')) ?> #<?= esc((string) ($latest_audit['actor_user_id'] ?? '')) ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($latest_audit)): ?><p><?= esc(lang('DoctorScheduleSettings.auditUnavailable')) ?></p><?php endif; ?>
    <p><?= esc(lang('DoctorScheduleSettings.retainedExceptionsCount')) ?>: <?= count($retained_exceptions ?? []) ?></p>
    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
        <div class="v3-corner border v3-border v3-surface p-4">
            <div class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.totalFutureVisits')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold v3-text-ink mt-1" dir="ltr"><?= esc((string) $report['total_future_visits']) ?></div>
        </div>
        <div class="v3-corner border v3-border v3-surface p-4">
            <div class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold <?= $report['after_conflicts_count'] > 0 ? 'v3-text-warning' : 'v3-text-primary' ?> mt-1" dir="ltr">
                <?= esc((string) $report['after_conflicts_count']) ?>
            </div>
            <span class="text-xs v3-text-muted"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></span>
        </div>
        <div class="v3-corner border v3-border v3-surface p-4">
            <div class="text-xs font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.compliant')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold v3-text-primary mt-1" dir="ltr">
                <?= esc((string) ($report['total_future_visits'] - $report['after_conflicts_count'])) ?>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="v3-corner border v3-border v3-surface overflow-hidden">
        <div class="border-b v3-border v3-surface px-6 py-4 flex items-center justify-between">
            <h2 class="font-bold v3-text-ink text-lg"><?= esc(lang('DoctorScheduleSettings.reviewedVisits')) ?> (<?= esc((string) count($report['items'])) ?>)</h2>
            <span class="text-xs v3-text-muted" dir="ltr"><?= esc(date('Y-m-d H:i:s')) ?></span>
        </div>

        <?php if (empty($report['items'])): ?>
            <div class="p-8 text-center v3-text-muted">
                <?= esc(lang('DoctorScheduleSettings.recheckReportClean')) ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-start border-collapse text-sm">
                    <thead>
                        <tr class="border-b v3-border v3-surface text-xs font-semibold v3-text-muted uppercase tracking-wider">
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.visit')) ?> #</th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.date')) ?> / <?= esc(lang('DoctorScheduleSettings.time')) ?></th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.providers')) ?></th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.beforeStatus')) ?></th>
                            <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.retainReason')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y v3-border-default">
                        <?php foreach ($report['items'] as $item): ?>
                            <?php
                            $isConflict = !$item['after_outcome']['compliant'];
                            $rowBg = $isConflict ? 'v3-bg-waiting' : '';
                            ?>
                            <tr class="<?= $rowBg ?>">
                                <td class="py-3 px-4 font-mono font-semibold v3-text-ink whitespace-nowrap">
                                    <span dir="ltr">#<?= esc((string) $item['appointment_id']) ?></span>
                                        <?php if (($item['temporal_state'] ?? '') === 'in_progress'): ?><span class="block text-xs v3-text-warning" data-temporal-state="in_progress"><?= esc(lang('DoctorScheduleSettings.inProgressVisit')) ?></span><?php endif; ?>
                                    <?php if ($item['status'] === 'completed'): ?>
                                        <span class="block text-xs font-normal v3-text-muted"><?= esc(lang('DoctorScheduleSettings.completedPreserved')) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-medium v3-text-ink" dir="ltr"><?= esc($item['appointment_date']) ?></div>
                                    <div class="text-xs v3-text-muted" dir="ltr"><?= esc($item['appointment_time']) ?> – <?= esc($item['end_time']) ?> (<?= esc((string) $item['duration']) ?> <?= esc(lang('DoctorScheduleSettings.minutes')) ?>)</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium v3-text-ink"><?= esc($item['provider_name'] ?? $item['chair_name'] ?? '—') ?></div>
                                    <?php if ($item['provider_user_id'] !== null): ?>
                                        <span class="text-xs font-mono v3-text-muted" dir="ltr">#<?= esc((string) $item['provider_user_id']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($item['after_outcome']['compliant']): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full v3-bg-paid px-2.5 py-0.5 text-xs font-semibold v3-text-primary">
                                            <i class="fas fa-check text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.compliant')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 rounded-full v3-bg-waiting px-2.5 py-0.5 text-xs font-semibold v3-text-warning" title="<?= esc($item['after_outcome']['reason']) ?>">
                                            <i class="fas fa-exclamation text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.needsReview')) ?>
                                        </span>
                                        <span class="block text-xs v3-text-warning mt-1"><?= esc($item['after_outcome']['reason']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($isConflict && isset($retained_exceptions[(int) $item['appointment_id']])): ?>
                                        <span class="inline-flex items-center gap-1 text-xs v3-text-ink">
                                            <i class="fas fa-shield-alt v3-text-warning"></i> <?= esc(lang('DoctorScheduleSettings.retainException')) ?>
                                        </span><p><?= esc($retained_exceptions[(int) $item['appointment_id']]['reason']) ?></p>
                                        <?php if (!empty($item['edit_url'])): ?>
                                            <div class="mt-1">
                                                <a href="<?= esc($item['edit_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer" class="text-xs v3-accent-text v3-hover-text-primary underline">
                                                    <?= esc(lang('DoctorScheduleSettings.rescheduleLink')) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-xs v3-text-muted">—</span>
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
<button class="v3-radius v3-soft-surface px-5 py-3 font-semibold v3-text-on-primary v3-btn v3-btn--primary" type="submit"><?= esc(lang('DoctorScheduleSettings.reviewCurrent')) ?></button></form>
<?php endif; ?>
<?= $this->endSection() ?>
