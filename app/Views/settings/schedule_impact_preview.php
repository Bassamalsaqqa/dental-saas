<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<div class="denta-settings-workspace impact-page max-w-6xl mx-auto px-4 py-8" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
    <!-- Top breadcrumb and title -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="<?= base_url('settings?tab=hours') ?>" class="text-sm font-medium text-orange-600 hover:text-orange-700 flex items-center gap-1">
                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                <span><?= esc(lang('DoctorScheduleSettings.backToHours')) ?></span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2"><?= esc(lang('DoctorScheduleSettings.impactReport')) ?></h1>
            <p class="text-sm text-slate-600 mt-1"><?= esc(lang('DoctorScheduleSettings.impactReportHelp')) ?></p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 v3-surface px-4 py-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('DoctorScheduleSettings.changedSetting')) ?>:</span>
            <span class="font-semibold text-slate-800"><?= $target_type === 'clinic_hours' ? esc(lang('DoctorScheduleSettings.clinicHours')) : esc($target_label) ?></span>
        </div>
    </div>

    <div class="mb-6 space-y-2 rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-950" role="status">
        <p class="font-semibold"><?= esc(lang('DoctorScheduleSettings.pendingPreview')) ?></p>
        <p class="text-sm"><?= esc(lang('DoctorScheduleSettings.previewHelp')) ?></p>
        <p class="text-sm"><?= esc(lang('DoctorScheduleSettings.previewExpiryHelp')) ?></p>
    </div>
    <?php $proposal = json_decode($proposed_dto_json, true, 512, JSON_THROW_ON_ERROR); ?>
    <section class="mb-6 rounded-xl border border-slate-200 v3-surface p-4 sm:p-6" aria-labelledby="proposed-settings-heading" data-proposed-settings>
        <h2 id="proposed-settings-heading" class="mb-4 text-lg font-bold text-slate-900"><?= esc(lang('DoctorScheduleSettings.proposedSettings')) ?></h2>
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
        <?php if ($target_type === 'clinic_hours'): ?>
            <div><dt class="font-semibold"><?= esc(lang('DoctorScheduleSettings.clinicHours')) ?></dt><dd dir="ltr"><?= esc($proposal['working_hours_start']) ?> – <?= esc($proposal['working_hours_end']) ?></dd></div>
            <div><dt class="font-semibold"><?= esc(lang('UI.settingsHoursWorkingDays')) ?></dt><dd><?= $proposal['working_days'] ? esc(implode(!empty($is_rtl) ? '، ' : ', ', array_map(static fn ($day) => lang('DoctorScheduleSettings.' . $day), $proposal['working_days']))) : esc(lang('DoctorScheduleSettings.noWorkingDays')) ?></dd></div>
            <div><dt class="font-semibold"><?= esc(lang('UI.settingsHoursAppointmentDuration')) ?></dt><dd><?= esc($proposal['appointment_duration']) ?> <?= esc(lang('DoctorScheduleSettings.minutes')) ?></dd></div>
            <div><dt class="font-semibold"><?= esc(lang('DoctorScheduleSettings.bookingMode')) ?></dt><dd><?= esc(lang('DoctorScheduleSettings.' . ($proposal['dynamic_doctor_scheduling_enabled'] === '1' ? 'doctorMode' : 'manualMode'))) ?></dd></div>
            <?php if ($proposal['dynamic_doctor_scheduling_enabled'] === '0'): ?>
            <div class="sm:col-span-2"><dt class="font-semibold"><?= esc(lang('DoctorScheduleSettings.legacyResources')) ?> (<?= esc($proposal['booking_chair_count']) ?>)</dt><dd><?= esc(implode(!empty($is_rtl) ? '، ' : ', ', $proposal['booking_chair_names'])) ?></dd></div>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach (['is_bookable' => 'bookable', 'is_clinician' => 'qualified'] as $field => $label): ?>
            <div><dt class="font-semibold"><?= esc(lang('DoctorScheduleSettings.' . $label)) ?></dt><dd><?= esc(lang('DoctorScheduleSettings.' . ($proposal[$field] ? 'yes' : 'no'))) ?></dd></div>
            <?php endforeach; ?>
            <div class="sm:col-span-2"><dt class="mb-2 font-semibold"><?= esc(lang('DoctorScheduleSettings.weeklyHours')) ?></dt><dd>
                <?php if ($proposal['weekly_hours'] === null): ?>
                    <?= esc(lang('DoctorScheduleSettings.clinicHoursApply')) ?>
                <?php else: ?>
                    <ul class="space-y-1">
                    <?php foreach ($proposal['weekly_hours'] as $day => $windows): ?>
                        <li><?= esc(lang('DoctorScheduleSettings.' . $day)) ?>:
                        <?php if (!$windows): ?><?= esc(lang('DoctorScheduleSettings.unavailableDay')) ?><?php endif; ?>
                        <?php foreach ($windows as $window): ?><span class="inline-block" dir="ltr"><?= esc($window['start']) ?> – <?= esc($window['end']) ?></span> <?php endforeach; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </dd></div>
            <div class="sm:col-span-2"><dt class="mb-2 font-semibold"><?= esc(lang('DoctorScheduleSettings.exceptions')) ?></dt><dd>
                <?php if (!$proposal['exceptions']): ?><?= esc(lang('DoctorScheduleSettings.noDateExceptions')) ?><?php endif; ?>
                <ul class="space-y-1">
                <?php foreach (($proposal['exceptions'] ?? []) as $date => $windows): ?>
                    <li><span dir="ltr"><?= esc($date) ?></span>:
                    <?php if (!$windows): ?><?= esc(lang('DoctorScheduleSettings.leave')) ?><?php endif; ?>
                    <?php foreach ($windows as $window): ?><span class="inline-block" dir="ltr"><?= esc($window['start']) ?> – <?= esc($window['end']) ?></span> <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </dd></div>
        <?php endif; ?>
        </dl>
    </section>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <div class="v3-corner border border-slate-200 v3-surface p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.totalFutureVisits')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1" dir="ltr"><?= esc((string) $report['total_future_visits']) ?></div>
        </div>
        <div class="v3-corner border border-slate-200 v3-surface p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.beforeStatus')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold <?= $report['before_conflicts_count'] > 0 ? 'text-amber-600' : 'text-emerald-600' ?> mt-1" dir="ltr">
                <?= esc((string) $report['before_conflicts_count']) ?>
            </div>
            <span class="text-xs text-slate-500"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></span>
        </div>
        <div class="v3-corner border border-slate-200 v3-surface p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.afterStatus')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold <?= $report['after_conflicts_count'] > 0 ? 'text-rose-600' : 'text-emerald-600' ?> mt-1" dir="ltr">
                <?= esc((string) $report['after_conflicts_count']) ?>
            </div>
            <span class="text-xs text-slate-500"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></span>
        </div>
        <div class="v3-corner border border-slate-200 v3-surface p-4">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.proofReference')) ?></div>
            <div class="text-xs font-mono text-slate-700 truncate mt-2" dir="ltr" title="<?= esc($report['state_digest']) ?>">
                <?= esc(substr($report['state_digest'], 0, 12)) ?>...
            </div>

        </div>
    </div>

    <!-- Main Form for Dispositions & Apply -->
    <form id="schedule-impact-apply" action="<?= base_url('settings/apply-schedule-impact') ?>" method="POST" class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="proof_token" value="<?= esc($proof_token, 'attr') ?>">
        <input type="hidden" name="target_type" value="<?= esc($target_type, 'attr') ?>">
        <?php if ($target_id !== null): ?>
            <input type="hidden" name="target_id" value="<?= esc((string) $target_id, 'attr') ?>">
        <?php endif; ?>
        <input type="hidden" name="proposed_dto_json" value="<?= esc($proposed_dto_json, 'attr') ?>">

        <!-- Status Notification Box -->
        <?php if ($report['after_conflicts_count'] === 0): ?>
            <div class="v3-corner border border-emerald-200 bg-emerald-50 p-5 text-emerald-900 flex items-start gap-4">
                <i class="fas fa-check-circle text-emerald-500 text-2xl mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-lg"><?= esc(lang('DoctorScheduleSettings.noConflicts')) ?></h3>
                    <p class="text-sm text-emerald-800 mt-1"><?= esc(lang('DoctorScheduleSettings.previewClean')) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="v3-corner border border-rose-200 bg-rose-50 p-5 text-rose-900 flex items-start gap-4">
                <i class="fas fa-exclamation-triangle text-rose-500 text-2xl mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-lg"><?= esc(lang('DoctorScheduleSettings.unresolvedImpact')) ?></h3>
                    <p class="text-sm text-rose-800 mt-1"><?= esc(lang('DoctorScheduleSettings.impactReportHelp')) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Appointments List -->
        <div class="v3-corner border border-slate-200 v3-surface overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-lg"><?= esc(lang('DoctorScheduleSettings.reviewedVisits')) ?> (<?= esc((string) count($report['items'])) ?>)</h2>
                <span class="text-xs text-slate-500"><?= esc(lang('DoctorScheduleSettings.previewExpiryHelp')) ?></span>
            </div>

            <?php if (empty($report['items'])): ?>
                <div class="p-8 text-center text-slate-500">
                    <?= esc(lang('DoctorScheduleSettings.previewClean')) ?>
                </div>
            <?php else: ?>
                <!-- Desktop Table View -->
                <div class="block overflow-x-auto">
                    <table class="w-full text-start border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.visit')) ?> #</th>
                                <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.date')) ?> / <?= esc(lang('DoctorScheduleSettings.time')) ?></th>
                                <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.providers')) ?></th>
                                <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.beforeStatus')) ?></th>
                                <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.afterStatus')) ?></th>
                                <th class="py-3.5 px-4 w-72"><?= esc(lang('DoctorScheduleSettings.retainReason')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($report['items'] as $item): ?>
                                <?php
                                $isConflict = !$item['after_outcome']['compliant'];
                                $rowBg = $isConflict ? 'bg-rose-50/30 hover:bg-rose-50/60' : 'hover:bg-slate-50/60';
                                ?>
                                <tr class="transition-colors <?= $rowBg ?>">
                                    <td class="py-3 px-4 font-mono font-semibold text-slate-800 whitespace-nowrap">
                                        <span dir="ltr">#<?= esc((string) $item['appointment_id']) ?></span>
                                        <?php if (($item['temporal_state'] ?? '') === 'in_progress'): ?><span class="block text-xs text-amber-800" data-temporal-state="in_progress"><?= esc(lang('DoctorScheduleSettings.inProgressVisit')) ?></span><?php endif; ?>
                                        <?php if ($item['status'] === 'completed'): ?>
                                            <span class="block text-xs font-normal text-slate-500"><?= esc(lang('DoctorScheduleSettings.completedPreserved')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-medium text-slate-800" dir="ltr"><?= esc($item['appointment_date']) ?></div>
                                        <div class="text-xs text-slate-500" dir="ltr"><?= esc($item['appointment_time']) ?> – <?= esc($item['end_time']) ?> (<?= esc((string) $item['duration']) ?> <?= esc(lang('DoctorScheduleSettings.minutes')) ?>)</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-800"><?= esc($item['provider_name'] ?? $item['chair_name'] ?? '—') ?></div>
                                        <?php if ($item['provider_user_id'] !== null): ?>
                                            <span class="text-xs font-mono text-slate-500" dir="ltr">#<?= esc((string) $item['provider_user_id']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($item['before_outcome']['compliant']): ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                                <i class="fas fa-check text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.compliant')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800" title="<?= esc($item['before_outcome']['reason']) ?>">
                                                <i class="fas fa-exclamation text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.needsReview')) ?>
                                            </span>
                                            <span class="block text-xs text-amber-700 mt-1 max-w-xs"><?= esc($item['before_outcome']['reason']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($item['after_outcome']['compliant']): ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                                <i class="fas fa-check text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.compliant')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800" title="<?= esc($item['after_outcome']['reason']) ?>">
                                                <i class="fas fa-times text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.needsReview')) ?>
                                            </span>
                                            <span class="block text-xs text-rose-700 mt-1 max-w-xs font-medium"><?= esc($item['after_outcome']['reason']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($isConflict): ?>
                                            <div class="space-y-2">
                                                <input type="hidden" name="dispositions[<?= esc((string) $item['appointment_id'], 'attr') ?>][action]" value="retain">
                                                <label class="block text-xs font-semibold text-slate-700">
                                                    <?= esc(lang('DoctorScheduleSettings.retainReason')) ?>:
                                                    <textarea name="dispositions[<?= esc((string) $item['appointment_id'], 'attr') ?>][reason]" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-500" placeholder="<?= esc(lang('DoctorScheduleSettings.reasonRequired'), 'attr') ?>" required minlength="3" maxlength="500"></textarea>
                                                </label>
                                                <?php if (!empty($item['edit_url'])): ?>
                                                    <a href="<?= esc($item['edit_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 underline">
                                                        <i class="fas fa-external-link-alt text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.rescheduleLink')) ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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

        <!-- Explicit operator acknowledgment -->
            <div class="v3-corner border border-amber-200 bg-amber-50 p-5 text-amber-950">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="acknowledged" value="1" required class="mt-1 h-5 w-5 rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-semibold">
                        <?= esc(lang('DoctorScheduleSettings.acknowledgePrompt')) ?>
                    </span>
                </label>
            </div>

        <p><?= esc(lang('DoctorScheduleSettings.communicationGate')) ?></p>
        <button class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700" type="submit" name="refresh_preview" value="1" formaction="<?= esc(base_url('settings/preview-schedule-impact'), 'attr') ?>" formnovalidate><?= esc(lang('DoctorScheduleSettings.refreshReport')) ?></button>
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-200">
            <a href="<?= base_url('settings?tab=hours') ?>" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm transition-colors">
                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right' : 'fa-arrow-left' ?> mr-1"></i> <?= esc(lang('DoctorScheduleSettings.backToHours')) ?>
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" class="v3-action v3-action-primary px-6 py-3 rounded-xl font-bold transition-all text-sm flex items-center gap-2">
                    <i class="fas fa-check-double"></i>
                    <span><?= esc(lang('DoctorScheduleSettings.applyChanges')) ?></span>
                </button>
            </div>
        </div>
    </form>
</div>
<script>
const impactForm = document.getElementById('schedule-impact-apply');
impactForm.addEventListener('submit', function () {
    const dispositions = {};
    this.querySelectorAll('textarea[name^="dispositions["]').forEach(field => {
        const id = field.name.match(/^dispositions\[([0-9]+)\]/)[1];
        dispositions[id] = {action: 'retain', reason: field.value};
    });
    const field = document.createElement('input'); field.type = 'hidden'; field.name = 'dispositions_json'; field.value = JSON.stringify(dispositions); this.append(field);
    this.querySelectorAll('[name^="dispositions["]').forEach(control => control.removeAttribute('name'));
});
</script>
<?= $this->endSection() ?>
