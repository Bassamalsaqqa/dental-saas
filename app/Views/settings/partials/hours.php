                <div id="hours-tab" class="settings-tab hidden">
                    <div class="v3-surface v3-radius border v3-border overflow-hidden">
                        <div class="p-4 v3-form-section-header">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center"><i class="fas fa-clock text-orange-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= esc(lang('DoctorScheduleSettings.pageTitle')) ?></h3><p class="text-slate-300"><?= esc(lang('DoctorScheduleSettings.pageHelp')) ?></p></div>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="mb-4 text-sm text-slate-700"><?= esc(lang('DoctorScheduleSettings.workflowHelp')) ?></p>
                            <p class="mb-6"><a class="v3-accent-text underline" href="<?= esc(base_url('settings/schedule-impact-recheck'), 'attr') ?>"><?= esc(lang('DoctorScheduleSettings.currentSchedule')) ?></a></p>
                            <form action="<?= base_url('settings/preview-schedule-impact') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="target_type" value="clinic_hours">
                                <?php $dynamicScheduling = in_array((string) old('dynamic_doctor_scheduling_enabled', $settings['dynamic_doctor_scheduling_enabled'] ?? '0'), ['1', 'true'], true); ?>
                                <input type="hidden" name="dynamic_doctor_scheduling_enabled" value="0">
                                <label class="mb-6 flex items-start gap-3 v3-radius border v3-border v3-soft-surface p-4">
                                    <input type="checkbox" id="dynamicDoctorScheduling" name="dynamic_doctor_scheduling_enabled" value="1" <?= $dynamicScheduling ? 'checked' : '' ?> <?= empty($provider_settings['schema_ready']) ? 'disabled' : '' ?> class="mt-1">
                                    <span><strong><?= esc(lang('DoctorScheduleSettings.enabled')) ?></strong><span class="mt-1 block text-sm text-slate-600"><?= esc(lang('DoctorScheduleSettings.enabledHelp')) ?></span></span>
                                </label>
                                <?php if (!$dynamicScheduling): ?><p class="mb-4 text-sm text-slate-700"><?= esc(lang('DoctorScheduleSettings.modeOffHelp')) ?></p><?php endif; ?>
                                <?php if (empty($provider_settings['schema_ready'])): ?><p class="mb-4 text-sm text-amber-800"><?= esc(lang('DoctorScheduleSettings.schemaMissing')) ?></p><?php endif; ?>
                                <?php
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursStartTime') ?></label><input type="time" name="working_hours_start" value="<?= esc(old('working_hours_start', $settings['working_hours_start'] ?? '09:00'), 'attr') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursStartTimeHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursEndTime') ?></label><input type="time" name="working_hours_end" value="<?= esc(old('working_hours_end', $settings['working_hours_end'] ?? '17:00'), 'attr') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursEndTimeHelp') ?></p></div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursWorkingDays') ?></label>
                                        <p class="text-xs text-gray-500 mt-1 mb-3"><?= lang('UI.settingsHoursWorkingDaysHelp') ?></p>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($dayKeys as $dayKey): ?>
                                                <label class="flex min-h-12 items-center justify-center px-3 py-3 border-2 v3-radius cursor-pointer transition-all duration-200 text-sm font-semibold <?= in_array($dayKey, $workingDays, true) ? 'border-orange-500 bg-orange-50 text-orange-800 shadow-sm' : 'border-gray-200 text-gray-700 hover:border-orange-300 hover:bg-orange-50/50' ?>">
                                                    <input type="checkbox" name="working_days[]" value="<?= esc($dayKey) ?>" <?= in_array($dayKey, $workingDays, true) ? 'checked' : '' ?> class="sr-only">
                                                    <span class="leading-none text-center"><?= esc($dayLabels[$dayKey]) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursAppointmentDuration') ?></label>
                                        <div class="relative">
                                            <select name="appointment_duration" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 appearance-none" required>
                                                <?php foreach ($appointmentDurationOptions as $durationOption): ?>
                                                    <option value="<?= $durationOption ?>" <?= $selectedAppointmentDuration === $durationOption ? 'selected' : '' ?>><?= $durationOption ?> <?= esc(lang('UI.patientMinutes')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursAppointmentDurationHelp') ?></p>
                                    </div>
                                    <fieldset data-legacy-scheduling class="md:col-span-2" <?= $dynamicScheduling ? 'disabled hidden' : '' ?>>
                                        <legend class="mb-2 font-semibold"><?= esc(lang('DoctorScheduleSettings.legacyResources')) ?></legend>
                                        <p class="mb-4 text-sm text-slate-600"><?= esc(lang('DoctorScheduleSettings.legacyHelp')) ?></p>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursBookingChairCount') ?></label>
                                        <input type="number" id="bookingChairCount" name="booking_chair_count" value="<?= $selectedChairCount ?>" min="1" max="<?= esc($maxChairCount) ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-orange-500 transition-all" required>
                                        <p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursBookingChairCountHelp') ?></p>
                                    </fieldset>
                                    <fieldset data-legacy-scheduling class="md:col-span-2 v3-radius border border-orange-100 bg-orange-50/60 p-5" <?= $dynamicScheduling ? 'disabled hidden' : '' ?>>
                                        <div class="mb-4">
                                            <label class="block text-sm font-semibold text-gray-700 mb-1"><?= lang('UI.settingsHoursChairNames') ?></label>
                                            <p class="text-xs text-gray-500"><?= lang('UI.settingsHoursChairNamesHelp') ?></p>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-chair-names-section="1">
                                            <?php for ($i = 0; $i < $maxChairCount; $i++): ?>
                                                <?php
                                                    $chairIndex = $i + 1;
                                                    $chairValue = trim((string) ($chairNames[$i] ?? ('Doctor ' . $chairIndex)));
                                                ?>
                                                <div data-chair-name-row="<?= $chairIndex ?>" class="<?= $chairIndex > $selectedChairCount ? 'hidden' : '' ?>">
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursChairNameLabel', ['number' => $chairIndex]) ?></label>
                                                    <input type="text" name="booking_chair_names[]" value="<?= esc($chairValue) ?>" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-orange-500 transition-all" placeholder="<?= esc(lang('UI.settingsHoursChairNamePlaceholder')) ?>" maxlength="100">
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" class="px-8 py-3 text-white font-semibold v3-radius transition-all v3-form-section-header"><i class="fas fa-eye mx-2"></i><?= esc(lang('DoctorScheduleSettings.previewChanges')) ?></button></div>
                            </form>
                            <?= view('settings/partials/provider_schedules', ['provider_settings' => $provider_settings ?? ['schema_ready' => false, 'members' => []]]) ?>
                        </div>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const dynamicToggle = document.getElementById('dynamicDoctorScheduling');
                    const syncMode = () => document.querySelectorAll('[data-legacy-scheduling]').forEach((field) => {
                        field.disabled = Boolean(dynamicToggle?.checked);
                        field.hidden = Boolean(dynamicToggle?.checked);
                    });
                    dynamicToggle?.addEventListener('change', syncMode);
                    syncMode();
                    const chairCountInput = document.getElementById('bookingChairCount');
                    const chairRows = Array.from(document.querySelectorAll('[data-chair-name-row]'));
                    if (!chairCountInput || chairRows.length === 0) return;

                    const syncChairRows = () => {
                        const count = Math.max(1, parseInt(chairCountInput.value || '1', 10) || 1);
                        chairRows.forEach((row) => {
                            const rowIndex = parseInt(row.getAttribute('data-chair-name-row') || '0', 10);
                            row.classList.toggle('hidden', rowIndex > count);
                        });
                    };

                    chairCountInput.addEventListener('input', syncChairRows);
                    chairCountInput.addEventListener('change', syncChairRows);
                    syncChairRows();
                });
                </script>

