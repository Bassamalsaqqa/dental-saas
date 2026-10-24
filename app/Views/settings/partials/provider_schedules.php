<?php
$scheduleContext = $provider_settings ?? ['schema_ready' => false, 'members' => []];
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$inputClass = 'min-w-0 max-w-full rounded-lg border v3-border-default px-3 py-2';
?>
<section class="mt-8 space-y-4" aria-labelledby="providerScheduleHeading">
    <h4 id="providerScheduleHeading" class="text-xl font-bold v3-text-ink"><?= esc(lang('DoctorScheduleSettings.providers')) ?></h4>
    <p class="text-sm v3-text-muted"><?= esc(lang('DoctorScheduleSettings.providersHelp')) ?></p>
    <?php if (!$scheduleContext['schema_ready']): ?>
        <p class="v3-radius v3-bg-waiting p-4 v3-text-warning"><?= esc(lang('DoctorScheduleSettings.schemaMissing')) ?></p>
    <?php elseif (empty($scheduleContext['members'])): ?>
        <p class="v3-radius v3-surface p-4"><?= esc(lang('DoctorScheduleSettings.noMembers')) ?></p>
    <?php else: ?>
        <?php foreach ($scheduleContext['members'] as $member): ?>
            <?php
            $profile = $member['profile'];
            $weekly = json_decode((string) ($profile['weekly_hours_json'] ?? 'null'), true);
            $exceptions = json_decode((string) ($profile['exceptions_json'] ?? '{}'), true);
            $exceptions = is_array($exceptions) ? $exceptions : [];
            $exceptionRows = [];
            foreach ($exceptions as $date => $windows) $exceptionRows[] = ['date' => $date, 'windows' => $windows];
            $exceptionRows[] = ['date' => '', 'windows' => []];
            $exceptionRows[] = ['date' => '', 'windows' => []];
            $providerId = (int) $member['user_id'];
            ?>
            <details class="v3-radius border v3-border v3-surface p-4" data-provider-settings-id="<?= $providerId ?>">
                <summary class="cursor-pointer font-semibold"><?= esc($member['name']) ?> <span class="text-sm font-normal v3-text-muted" dir="ltr">#<?= $providerId ?> · <?= esc($member['role_slug']) ?></span></summary>
                <form action="<?= esc(base_url('settings/preview-schedule-impact'), 'attr') ?>" method="post" class="mt-5 space-y-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="target_type" value="provider_profile">
                    <input type="hidden" name="provider_user_id" value="<?= $providerId ?>">
                    <div class="flex flex-wrap gap-5">
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_bookable" value="1" <?= (int) ($profile['is_bookable'] ?? 1) === 1 ? 'checked' : '' ?> class="v3-checkbox"><?= esc(lang('DoctorScheduleSettings.bookable')) ?></label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_clinician" value="1" <?= (int) ($profile['is_clinician'] ?? 0) === 1 ? 'checked' : '' ?> class="v3-checkbox"><?= esc(lang('DoctorScheduleSettings.qualified')) ?></label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="weekly_override" value="1" <?= is_array($weekly) ? 'checked' : '' ?> class="v3-checkbox"><?= esc(lang('DoctorScheduleSettings.weeklyOverride')) ?></label>
                    </div>
                    <p class="text-sm v3-text-muted"><?= esc(lang('DoctorScheduleSettings.qualificationHelp')) ?></p>
                    <fieldset class="space-y-3"><legend class="mb-3 font-semibold"><?= esc(lang('DoctorScheduleSettings.weeklyHours')) ?></legend>
                        <p class="text-sm v3-text-muted"><?= esc(lang('DoctorScheduleSettings.windowsHelp')) ?></p>
                        <?php foreach ($days as $day): ?>
                            <?php $windows = is_array($weekly[$day] ?? null) ? $weekly[$day] : []; ?>
                            <div class="flex flex-wrap items-center gap-3 rounded-lg v3-surface p-3">
                                <span class="w-28 font-medium"><?= esc(lang('DoctorScheduleSettings.' . $day)) ?></span>
                                <?php for ($i = 0; $i < max(2, count($windows)); $i++): ?>
                                    <label class="flex flex-wrap items-center gap-2 text-sm"><?= esc(lang('DoctorScheduleSettings.window')) ?> <?= $i + 1 ?>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.from'), 'attr') ?>" name="weekly[<?= $day ?>][<?= $i ?>][start]" value="<?= esc($windows[$i]['start'] ?? '', 'attr') ?>" class="<?= $inputClass ?> v3-input" dir="ltr">
                                        <span>–</span>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.until'), 'attr') ?>" name="weekly[<?= $day ?>][<?= $i ?>][end]" value="<?= esc($windows[$i]['end'] ?? '', 'attr') ?>" class="<?= $inputClass ?> v3-input" dir="ltr">
                                    </label>
                                <?php endfor; ?>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                    <fieldset class="space-y-3"><legend class="mb-3 font-semibold"><?= esc(lang('DoctorScheduleSettings.exceptions')) ?></legend>
                        <p class="text-sm v3-text-muted"><?= esc(lang('DoctorScheduleSettings.exceptionsHelp')) ?></p>
                        <?php foreach ($exceptionRows as $index => $exception): ?>
                            <?php $windows = is_array($exception['windows']) ? $exception['windows'] : []; ?>
                            <div class="flex flex-wrap items-center gap-3 rounded-lg v3-surface p-3">
                                <input type="date" aria-label="<?= esc(lang('DoctorScheduleSettings.date'), 'attr') ?>" name="exceptions[<?= $index ?>][date]" value="<?= esc($exception['date'], 'attr') ?>" class="<?= $inputClass ?> v3-input" dir="ltr">
                                <label class="flex items-center gap-2"><input type="checkbox" name="exceptions[<?= $index ?>][leave]" value="1" <?= $exception['date'] !== '' && !$windows ? 'checked' : '' ?> class="v3-checkbox"><?= esc(lang('DoctorScheduleSettings.leave')) ?></label>
                                <?php for ($i = 0; $i < max(2, count($windows)); $i++): ?>
                                    <label class="flex flex-wrap items-center gap-2 text-sm"><?= esc(lang('DoctorScheduleSettings.window')) ?> <?= $i + 1 ?>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.from'), 'attr') ?>" name="exceptions[<?= $index ?>][windows][<?= $i ?>][start]" value="<?= esc($windows[$i]['start'] ?? '', 'attr') ?>" class="<?= $inputClass ?> v3-input" dir="ltr">
                                        <span>–</span>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.until'), 'attr') ?>" name="exceptions[<?= $index ?>][windows][<?= $i ?>][end]" value="<?= esc($windows[$i]['end'] ?? '', 'attr') ?>" class="<?= $inputClass ?> v3-input" dir="ltr">
                                    </label>
                                <?php endfor; ?>
                                <?php if ($exception['date'] !== ''): ?><label class="flex items-center gap-2 v3-text-danger"><input type="checkbox" name="exceptions[<?= $index ?>][remove]" value="1" class="v3-checkbox"><?= esc(lang('DoctorScheduleSettings.removeException')) ?></label><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                    <?php if (has_permission('settings', 'edit')): ?><button class="v3-radius v3-primary-surface px-5 py-3 font-semibold v3-text-on-primary v3-btn v3-btn--primary" type="submit"><?= esc(lang('DoctorScheduleSettings.previewProvider')) ?></button><?php endif; ?>
                </form>
            </details>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
