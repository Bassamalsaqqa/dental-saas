<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<style>.impact-page{width:100%;max-width:1100px;margin:auto;padding:16px;box-sizing:border-box;overflow-wrap:anywhere}.impact-page *{min-width:0;box-sizing:border-box}.impact-page .overflow-x-auto{display:block;width:100%;overflow-x:auto}.impact-page table{min-width:950px}.impact-page td,.impact-page th{padding:12px}.impact-page textarea{width:100%;min-width:180px}.impact-page button{white-space:normal}.impact-page form{width:100%}</style>
<div class="impact-page max-w-6xl mx-auto px-4 py-8" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
    <!-- Top breadcrumb and title -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="<?= base_url('settings?tab=hours') ?>" class="text-sm font-medium text-orange-600 hover:text-orange-700 flex items-center gap-1">
                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                <span><?= esc(lang('UI.settingsOperations')) ?></span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2"><?= esc(lang('DoctorScheduleSettings.impactReport')) ?></h1>
            <p class="text-sm text-slate-600 mt-1"><?= esc(lang('DoctorScheduleSettings.impactReportHelp')) ?></p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(lang('DoctorScheduleSettings.providers')) ?>:</span>
            <span class="font-semibold text-slate-800"><?= esc($target_label) ?></span>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.totalFutureVisits')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1" dir="ltr"><?= esc((string) $report['total_future_visits']) ?></div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.beforeStatus')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold <?= $report['before_conflicts_count'] > 0 ? 'text-amber-600' : 'text-emerald-600' ?> mt-1" dir="ltr">
                <?= esc((string) $report['before_conflicts_count']) ?>
            </div>
            <span class="text-xs text-slate-500"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.afterStatus')) ?></div>
            <div class="text-2xl sm:text-3xl font-extrabold <?= $report['after_conflicts_count'] > 0 ? 'text-rose-600' : 'text-emerald-600' ?> mt-1" dir="ltr">
                <?= esc((string) $report['after_conflicts_count']) ?>
            </div>
            <span class="text-xs text-slate-500"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?></span>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= esc(lang('DoctorScheduleSettings.auditReference')) ?></div>
            <div class="text-xs font-mono text-slate-700 truncate mt-2" dir="ltr" title="<?= esc($report['state_digest']) ?>">
                <?= esc(substr($report['state_digest'], 0, 12)) ?>...
            </div>
            <span class="text-xs text-slate-400">SHA-256 Digest</span>
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
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900 flex items-start gap-4">
                <i class="fas fa-check-circle text-emerald-500 text-2xl mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-lg"><?= esc(lang('DoctorScheduleSettings.noConflicts')) ?></h3>
                    <p class="text-sm text-emerald-800 mt-1"><?= esc(lang('DoctorScheduleSettings.recheckReportClean')) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-900 flex items-start gap-4">
                <i class="fas fa-exclamation-triangle text-rose-500 text-2xl mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-lg"><?= esc(lang('DoctorScheduleSettings.unresolvedImpact')) ?></h3>
                    <p class="text-sm text-rose-800 mt-1"><?= esc(lang('DoctorScheduleSettings.impactReportHelp')) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Appointments List -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-lg"><?= esc(lang('DoctorScheduleSettings.affectedVisits')) ?> (<?= esc((string) count($report['items'])) ?>)</h2>
                <span class="text-xs text-slate-500"><?= esc(lang('DoctorScheduleSettings.recheckReportHelp')) ?></span>
            </div>

            <?php if (empty($report['items'])): ?>
                <div class="p-8 text-center text-slate-500">
                    <?= esc(lang('DoctorScheduleSettings.recheckReportClean')) ?>
                </div>
            <?php else: ?>
                <!-- Desktop Table View -->
                <div class="block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.window')) ?> #</th>
                                <th class="py-3.5 px-4"><?= esc(lang('DoctorScheduleSettings.date')) ?> / Time</th>
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
                                        <?php if ($item['before_outcome']['compliant']): ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                                <i class="fas fa-check text-[10px]"></i> <?= esc(lang('DoctorScheduleSettings.compliant')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800" title="<?= esc($item['before_outcome']['reason']) ?>">
                                                <i class="fas fa-exclamation text-[10px]"></i> <?= esc($item['before_outcome']['code']) ?>
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
                                                <i class="fas fa-times text-[10px]"></i> <?= esc($item['after_outcome']['code']) ?>
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
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="acknowledged" value="1" required class="mt-1 h-5 w-5 rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-semibold">
                        <?= esc(lang('DoctorScheduleSettings.acknowledgePrompt')) ?>
                    </span>
                </label>
            </div>

        <p><?= esc(lang('DoctorScheduleSettings.communicationGate')) ?></p>
        <button type="submit" name="refresh_preview" value="1" formaction="<?= esc(base_url('settings/preview-schedule-impact'), 'attr') ?>" formnovalidate><?= esc(lang('DoctorScheduleSettings.refreshReport')) ?></button>
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-200">
            <a href="<?= base_url('settings?tab=hours') ?>" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm transition-colors">
                <i class="fas <?= !empty($is_rtl) ? 'fa-arrow-right' : 'fa-arrow-left' ?> mr-1"></i> <?= esc(lang('UI.settingsOperations')) ?>
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold shadow-lg shadow-orange-500/20 hover:from-orange-600 hover:to-amber-700 transition-all text-sm flex items-center gap-2">
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
