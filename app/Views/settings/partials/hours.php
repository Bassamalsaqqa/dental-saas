                <div id="hours-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-clock text-orange-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsOperations') ?></h3><p class="text-slate-300"><?= lang('UI.settingsOperationsDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="<?= base_url('settings/updateWorkingHours') ?>" method="POST">
                                <?= csrf_field() ?>
                                <?php
                                    $appointmentDurationOptions = [30, 45, 60, 90, 120];
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
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursStartTime') ?></label><input type="time" name="working_hours_start" value="<?= old('working_hours_start', $settings['working_hours_start'] ?? '09:00') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursStartTimeHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursEndTime') ?></label><input type="time" name="working_hours_end" value="<?= old('working_hours_end', $settings['working_hours_end'] ?? '17:00') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursEndTimeHelp') ?></p></div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursWorkingDays') ?></label>
                                        <p class="text-xs text-gray-500 mt-1 mb-3"><?= lang('UI.settingsHoursWorkingDaysHelp') ?></p>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($dayKeys as $dayKey): ?>
                                                <label class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center border-2 rounded-xl cursor-pointer transition-all duration-200 text-xs sm:text-sm font-semibold <?= in_array($dayKey, $workingDays, true) ? 'border-orange-500 bg-orange-50 text-orange-800 shadow-sm' : 'border-gray-200 text-gray-700 hover:border-orange-300 hover:bg-orange-50/50' ?>">
                                                    <input type="checkbox" name="working_days[]" value="<?= esc($dayKey) ?>" <?= in_array($dayKey, $workingDays, true) ? 'checked' : '' ?> class="sr-only">
                                                    <span class="leading-none text-center"><?= esc($dayLabels[$dayKey]) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursAppointmentDuration') ?></label>
                                        <div class="relative">
                                            <select name="appointment_duration" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl appearance-none" required>
                                                <?php foreach ($appointmentDurationOptions as $durationOption): ?>
                                                    <option value="<?= $durationOption ?>" <?= $selectedAppointmentDuration === $durationOption ? 'selected' : '' ?>><?= $durationOption ?> <?= esc(lang('UI.patientMinutes')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursAppointmentDurationHelp') ?></p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursBookingChairCount') ?></label>
                                        <input type="number" id="bookingChairCount" name="booking_chair_count" value="<?= $selectedChairCount ?>" min="1" max="<?= esc($maxChairCount) ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required>
                                        <p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsHoursBookingChairCountHelp') ?></p>
                                    </div>
                                    <div class="md:col-span-2 rounded-2xl border border-orange-100 bg-orange-50/60 p-5">
                                        <div class="mb-4">
                                            <label class="block text-sm font-semibold text-gray-700 mb-1"><?= lang('UI.settingsHoursChairNames') ?></label>
                                            <p class="text-xs text-gray-500"><?= lang('UI.settingsHoursChairNamesHelp') ?></p>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-chair-names-section="1">
                                            <?php for ($i = 0; $i < $maxChairCount; $i++): ?>
                                                <?php
                                                    $chairIndex = $i + 1;
                                                    $chairValue = trim((string) ($chairNames[$i] ?? ('Chair ' . $chairIndex)));
                                                ?>
                                                <div data-chair-name-row="<?= $chairIndex ?>" class="<?= $chairIndex > $selectedChairCount ? 'hidden' : '' ?>">
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsHoursChairNameLabel', ['number' => $chairIndex]) ?></label>
                                                    <input type="text" name="booking_chair_names[]" value="<?= esc($chairValue) ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" placeholder="<?= esc(lang('UI.settingsHoursChairNamePlaceholder')) ?>" maxlength="100">
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
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
