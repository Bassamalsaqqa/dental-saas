<?php
$scheduleContext = $provider_settings ?? ['schema_ready' => false, 'members' => []];
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$inputClass = 'settings-window-input';
?>
<section class="form-section provider-schedules" aria-labelledby="providerScheduleHeading">
    <h4 id="providerScheduleHeading" class="settings-section-title"><?= esc(lang('DoctorScheduleSettings.providers')) ?></h4>
    <p class="settings-help"><?= esc(lang('DoctorScheduleSettings.providersHelp')) ?></p>
    <?php if (!$scheduleContext['schema_ready']): ?>
        <p class="settings-warning"><?= esc(lang('DoctorScheduleSettings.schemaMissing')) ?></p>
    <?php elseif (empty($scheduleContext['members'])): ?>
        <p class="settings-information"><?= esc(lang('DoctorScheduleSettings.noMembers')) ?></p>
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
            <details class="settings-provider" data-provider-settings-id="<?= $providerId ?>">
                <summary class="settings-provider-summary"><?= esc($member['name']) ?> <span class="settings-provider-meta" dir="ltr">#<?= $providerId ?> · <?= esc($member['role_slug']) ?></span></summary>
                <form action="<?= esc(base_url('settings/preview-schedule-impact'), 'attr') ?>" method="post" data-settings-form class="settings-provider-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="target_type" value="provider_profile">
                    <input type="hidden" name="provider_user_id" value="<?= $providerId ?>">
                    <div class="settings-provider-options">
                        <label class="settings-inline-check"><input type="checkbox" name="is_bookable" value="1" <?= (int) ($profile['is_bookable'] ?? 1) === 1 ? 'checked' : '' ?>><?= esc(lang('DoctorScheduleSettings.bookable')) ?></label>
                        <label class="settings-inline-check"><input type="checkbox" name="is_clinician" value="1" <?= (int) ($profile['is_clinician'] ?? 0) === 1 ? 'checked' : '' ?>><?= esc(lang('DoctorScheduleSettings.qualified')) ?></label>
                        <label class="settings-inline-check"><input type="checkbox" name="weekly_override" value="1" <?= is_array($weekly) ? 'checked' : '' ?>><?= esc(lang('DoctorScheduleSettings.weeklyOverride')) ?></label>
                    </div>
                    <p class="settings-help"><?= esc(lang('DoctorScheduleSettings.qualificationHelp')) ?></p>
                    <fieldset class="settings-window-group"><legend class="settings-section-title"><?= esc(lang('DoctorScheduleSettings.weeklyHours')) ?></legend>
                        <p class="settings-help"><?= esc(lang('DoctorScheduleSettings.windowsHelp')) ?></p>
                        <?php foreach ($days as $day): ?>
                            <?php $windows = is_array($weekly[$day] ?? null) ? $weekly[$day] : []; ?>
                            <div class="settings-window-row">
                                <span class="settings-window-day"><?= esc(lang('DoctorScheduleSettings.' . $day)) ?></span>
                                <?php for ($i = 0; $i < max(2, count($windows)); $i++): ?>
                                    <label class="settings-window-pair"><?= esc(lang('DoctorScheduleSettings.window')) ?> <?= $i + 1 ?>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.from'), 'attr') ?>" name="weekly[<?= $day ?>][<?= $i ?>][start]" value="<?= esc($windows[$i]['start'] ?? '', 'attr') ?>" class="<?= $inputClass ?>" dir="ltr">
                                        <span>–</span>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.until'), 'attr') ?>" name="weekly[<?= $day ?>][<?= $i ?>][end]" value="<?= esc($windows[$i]['end'] ?? '', 'attr') ?>" class="<?= $inputClass ?>" dir="ltr">
                                    </label>
                                <?php endfor; ?>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                    <fieldset class="settings-window-group"><legend class="settings-section-title"><?= esc(lang('DoctorScheduleSettings.exceptions')) ?></legend>
                        <p class="settings-help"><?= esc(lang('DoctorScheduleSettings.exceptionsHelp')) ?></p>
                        <?php foreach ($exceptionRows as $index => $exception): ?>
                            <?php $windows = is_array($exception['windows']) ? $exception['windows'] : []; ?>
                            <div class="settings-window-row">
                                <input type="date" aria-label="<?= esc(lang('DoctorScheduleSettings.date'), 'attr') ?>" name="exceptions[<?= $index ?>][date]" value="<?= esc($exception['date'], 'attr') ?>" class="<?= $inputClass ?>" dir="ltr">
                                <label class="settings-inline-check"><input type="checkbox" name="exceptions[<?= $index ?>][leave]" value="1" <?= $exception['date'] !== '' && !$windows ? 'checked' : '' ?>><?= esc(lang('DoctorScheduleSettings.leave')) ?></label>
                                <?php for ($i = 0; $i < max(2, count($windows)); $i++): ?>
                                    <label class="settings-window-pair"><?= esc(lang('DoctorScheduleSettings.window')) ?> <?= $i + 1 ?>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.from'), 'attr') ?>" name="exceptions[<?= $index ?>][windows][<?= $i ?>][start]" value="<?= esc($windows[$i]['start'] ?? '', 'attr') ?>" class="<?= $inputClass ?>" dir="ltr">
                                        <span>–</span>
                                        <input type="time" aria-label="<?= esc(lang('DoctorScheduleSettings.until'), 'attr') ?>" name="exceptions[<?= $index ?>][windows][<?= $i ?>][end]" value="<?= esc($windows[$i]['end'] ?? '', 'attr') ?>" class="<?= $inputClass ?>" dir="ltr">
                                    </label>
                                <?php endfor; ?>
                                <?php if ($exception['date'] !== ''): ?><label class="settings-inline-check settings-danger"><input type="checkbox" name="exceptions[<?= $index ?>][remove]" value="1"><?= esc(lang('DoctorScheduleSettings.removeException')) ?></label><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                    <?php if (has_permission('settings', 'edit')): ?><button class="button primary" type="submit"><?= esc(lang('DoctorScheduleSettings.previewProvider')) ?></button><?php endif; ?>
                </form>
            </details>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
