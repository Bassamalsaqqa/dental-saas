<?php $dynamicScheduling=in_array((string)old('dynamic_doctor_scheduling_enabled',$settings['dynamic_doctor_scheduling_enabled']??'0'),['1','true'],true);
                                    $appointmentDurationOptions = [30, 45, 60, 90, 120, 180];
                                    $selectedAppointmentDuration = (int) old('appointment_duration', $settings['appointment_duration'] ?? 30);
                                    if (!in_array($selectedAppointmentDuration, $appointmentDurationOptions, true) && $selectedAppointmentDuration > 0) {
                                        $appointmentDurationOptions[] = $selectedAppointmentDuration;
                                        sort($appointmentDurationOptions, SORT_NUMERIC);
                                    }
                                    $maxChairCount = max(1, (int) ($max_booking_chair_count ?? 3));
                                    $selectedChairCount = min($maxChairCount, max(1, (int) old('booking_chair_count', $settings['booking_chair_count'] ?? 1)));
                                    $chairNames = old('booking_chair_names');
                                    if (!is_array($chairNames)) {
                                        $chairNames = $settings['booking_chair_names'] ?? [];
                                    }
                                    if (!is_array($chairNames)) {
                                        $chairNames = [];
                                    }
                                    $workingDays = old('working_days') ?? ($settings['working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
                                    $workingDays = is_array($workingDays) ? $workingDays : [];
                                    $dayKeys = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                    $dayLabels = [
                                        'monday' => lang('UI.settingsDayMonday'),
                                        'tuesday' => lang('UI.settingsDayTuesday'),
                                        'wednesday' => lang('UI.settingsDayWednesday'),
                                        'thursday' => lang('UI.settingsDayThursday'),
                                        'friday' => lang('UI.settingsDayFriday'),
                                        'saturday' => lang('UI.settingsDaySaturday'),
                                        'sunday' => lang('UI.settingsDaySunday'),
                                    ];
?>
<section class="form-section"><header class="settings-panel-heading"><div><h2><?= esc(lang('DoctorScheduleSettings.pageTitle')) ?></h2><p><?= esc(lang('DoctorScheduleSettings.workflowHelp')) ?></p></div><a class="button secondary" href="<?= base_url('settings/schedule-impact-recheck') ?>"><?= esc(lang('DoctorScheduleSettings.currentSchedule')) ?></a></header><form method="post" action="<?= base_url('settings/preview-schedule-impact') ?>" data-settings-form><?= csrf_field() ?><input type="hidden" name="target_type" value="clinic_hours"><input type="hidden" name="dynamic_doctor_scheduling_enabled" value="0"><label class="settings-switch settings-switch--important"><span><strong><?= esc(lang('DoctorScheduleSettings.enabled')) ?></strong><small><?= esc(lang('DoctorScheduleSettings.enabledHelp')) ?></small></span><input type="checkbox" role="switch" class="studio-switch-input" id="dynamicDoctorScheduling" name="dynamic_doctor_scheduling_enabled" value="1" <?= $dynamicScheduling?'checked':'' ?> <?= empty($provider_settings['schema_ready'])||!$settingsEdit?'disabled':'' ?>></label>
<?php if(!$dynamicScheduling): ?><p class="settings-information"><?= esc(lang('DoctorScheduleSettings.modeOffHelp')) ?></p><?php endif; ?><?php if(empty($provider_settings['schema_ready'])): ?><p class="settings-warning"><?= esc(lang('DoctorScheduleSettings.schemaMissing')) ?></p><?php endif; ?>
<div class="settings-hours-fields"><?php foreach(['working_hours_start'=>['UI.settingsHoursStartTime','09:00'],'working_hours_end'=>['UI.settingsHoursEndTime','17:00']] as $name=>[$label,$default]): ?><div class="form-field"><label for="setting-<?= $name ?>"><?= esc(lang($label)) ?></label><input type="time" name="<?= $name ?>" id="setting-<?= $name ?>" dir="ltr" value="<?= esc(old($name,$settings[$name]??$default),'attr') ?>" required></div><?php endforeach; ?><div class="form-field"><label for="setting-appointment_duration"><?= esc(lang('UI.settingsHoursAppointmentDuration')) ?></label><select name="appointment_duration" id="setting-appointment_duration" required><?php foreach($appointmentDurationOptions as $duration): ?><option value="<?= $duration ?>" <?= $selectedAppointmentDuration===$duration?'selected':'' ?>><?= $duration ?> <?= esc(lang('Studio.minutes')) ?></option><?php endforeach; ?></select></div></div>
<fieldset class="settings-days"><legend><?= esc(lang('UI.settingsHoursWorkingDays')) ?></legend><?php foreach($dayKeys as $day): ?><label><input type="checkbox" name="working_days[]" value="<?= $day ?>" <?= in_array($day,$workingDays,true)?'checked':'' ?>><span><?= esc($dayLabels[$day]) ?></span></label><?php endforeach; ?></fieldset>
<fieldset data-legacy-scheduling class="settings-resources" <?= $dynamicScheduling?'disabled hidden':'' ?>><legend><?= esc(lang('UI.settingsHoursChairNames')) ?></legend><div class="form-field settings-resource-count"><label for="bookingChairCount"><?= esc(lang('UI.settingsHoursBookingChairCount')) ?></label><input type="number" id="bookingChairCount" name="booking_chair_count" min="1" max="<?= $maxChairCount ?>" value="<?= $selectedChairCount ?>" required></div><div class="settings-resource-names" data-chair-names-section="1"><?php for($i=0;$i<$maxChairCount;$i++):$chairIndex=$i+1;$chairValue=trim((string)($chairNames[$i]??('Doctor '.$chairIndex))); ?><div data-chair-name-row="<?= $chairIndex ?>" class="form-field" <?= $chairIndex>$selectedChairCount?'hidden':'' ?>><label for="setting-chair-<?= $chairIndex ?>"><?= esc(lang('UI.settingsHoursChairNameLabel',['number'=>$chairIndex])) ?></label><input name="booking_chair_names[]" id="setting-chair-<?= $chairIndex ?>" value="<?= esc($chairValue,'attr') ?>" maxlength="100"></div><?php endfor; ?></div></fieldset><footer class="settings-save"><button type="submit" class="button primary" <?= $settingsEdit?'':'disabled' ?>><?= esc(lang('DoctorScheduleSettings.previewChanges')) ?></button></footer></form></section>
<?= view('settings/partials/provider_schedules',['provider_settings'=>$provider_settings??['schema_ready'=>false,'members'=>[]]]) ?>
