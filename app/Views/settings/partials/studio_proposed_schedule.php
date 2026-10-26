<section class="form-section" data-proposed-settings><header><h2><?= esc(lang('DoctorScheduleSettings.proposedSettings')) ?></h2></header><dl class="settings-summary">
        <?php if ($target_type === 'clinic_hours'): ?>
            <div><dt><?= esc(lang('DoctorScheduleSettings.clinicHours')) ?></dt><dd dir="ltr"><?= esc($proposal['working_hours_start']) ?> – <?= esc($proposal['working_hours_end']) ?></dd></div>
            <div><dt><?= esc(lang('UI.settingsHoursWorkingDays')) ?></dt><dd><?= $proposal['working_days'] ? esc(implode(!empty($is_rtl) ? '، ' : ', ', array_map(static fn ($day) => lang('DoctorScheduleSettings.' . $day), $proposal['working_days']))) : esc(lang('DoctorScheduleSettings.noWorkingDays')) ?></dd></div>
            <div><dt><?= esc(lang('UI.settingsHoursAppointmentDuration')) ?></dt><dd><?= esc($proposal['appointment_duration']) ?> <?= esc(lang('DoctorScheduleSettings.minutes')) ?></dd></div>
            <div><dt><?= esc(lang('DoctorScheduleSettings.bookingMode')) ?></dt><dd><?= esc(lang('DoctorScheduleSettings.' . ($proposal['dynamic_doctor_scheduling_enabled'] === '1' ? 'doctorMode' : 'manualMode'))) ?></dd></div>
            <?php if ($proposal['dynamic_doctor_scheduling_enabled'] === '0'): ?>
            <div><dt><?= esc(lang('DoctorScheduleSettings.legacyResources')) ?> (<?= esc($proposal['booking_chair_count']) ?>)</dt><dd><?= esc(implode(!empty($is_rtl) ? '، ' : ', ', $proposal['booking_chair_names'])) ?></dd></div>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach (['is_bookable' => 'bookable', 'is_clinician' => 'qualified'] as $field => $label): ?>
            <div><dt><?= esc(lang('DoctorScheduleSettings.' . $label)) ?></dt><dd><?= esc(lang('DoctorScheduleSettings.' . ($proposal[$field] ? 'yes' : 'no'))) ?></dd></div>
            <?php endforeach; ?>
            <div><dt><?= esc(lang('DoctorScheduleSettings.weeklyHours')) ?></dt><dd>
                <?php if ($proposal['weekly_hours'] === null): ?>
                    <?= esc(lang('DoctorScheduleSettings.clinicHoursApply')) ?>
                <?php else: ?>
                    <ul>
                    <?php foreach ($proposal['weekly_hours'] as $day => $windows): ?>
                        <li><?= esc(lang('DoctorScheduleSettings.' . $day)) ?>:
                        <?php if (!$windows): ?><?= esc(lang('DoctorScheduleSettings.unavailableDay')) ?><?php endif; ?>
                        <?php foreach ($windows as $window): ?><span dir="ltr"><?= esc($window['start']) ?> – <?= esc($window['end']) ?></span> <?php endforeach; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </dd></div>
            <div><dt><?= esc(lang('DoctorScheduleSettings.exceptions')) ?></dt><dd>
                <?php if (!$proposal['exceptions']): ?><?= esc(lang('DoctorScheduleSettings.noDateExceptions')) ?><?php endif; ?>
                <ul>
                <?php foreach (($proposal['exceptions'] ?? []) as $date => $windows): ?>
                    <li><span dir="ltr"><?= esc($date) ?></span>:
                    <?php if (!$windows): ?><?= esc(lang('DoctorScheduleSettings.leave')) ?><?php endif; ?>
                    <?php foreach ($windows as $window): ?><span dir="ltr"><?= esc($window['start']) ?> – <?= esc($window['end']) ?></span> <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </dd></div>
        <?php endif; ?>

</dl></section>
