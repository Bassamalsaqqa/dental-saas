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
                                    $selectedAppointmentDuration = (int) ($settings['appointment_duration'] ?? 30);
                                    if (!in_array($selectedAppointmentDuration, $appointmentDurationOptions, true) && $selectedAppointmentDuration > 0) {
                                        $appointmentDurationOptions[] = $selectedAppointmentDuration;
                                        sort($appointmentDurationOptions, SORT_NUMERIC);
                                    }
                                ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Default Appointment Duration</label>
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
                                        <p class="text-xs text-gray-500 mt-1">The default length preselected for new appointments.</p>
                                    </div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label><input type="time" name="working_hours_start" value="<?= $settings['working_hours_start'] ?? '09:00' ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1">The earliest time a new appointment can be booked.</p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label><input type="time" name="working_hours_end" value="<?= $settings['working_hours_end'] ?? '17:00' ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1">The latest time a new appointment can be booked.</p></div>
                                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-gray-700 mb-2">Number of Chairs</label><input type="number" name="booking_chair_count" value="<?= $settings['booking_chair_count'] ?? 1 ?>" min="1" max="3" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1">Number of simultaneous appointment slots available (1-3).</p></div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" onclick="setButtonLoading(this, true)" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
