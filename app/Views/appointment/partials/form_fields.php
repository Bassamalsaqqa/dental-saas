<?php
$validation = session()->getFlashdata('validation');
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? null;
$inputClass = 'w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15';
$selectedType = old('appointment_type', $appointment_type_prefill ?? '');
$selectedChair = old('chair_name', $chair_options[0] ?? '');
$durationOptions = [30, 45, 60, 90, 120, 180];
$selectedDuration = (int) old('duration', (int) getAppointmentDuration());
if (!in_array($selectedDuration, $durationOptions, true) && $selectedDuration > 0) {
    $durationOptions[] = $selectedDuration;
    sort($durationOptions, SORT_NUMERIC);
}
?>
<div data-contextual-appointment-form data-slots-url="<?= esc(base_url('appointment/available-time-slots')) ?>">
    <?php if ($contextual && !empty($selectedPatient['id'])): ?>
        <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
        <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
    <?php else: ?>
        <div>
            <label for="patientSelect" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.appointmentPatientRequired')) ?></label>
            <select name="patient_id" id="patientSelect" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc(lang('UI.appointmentSearchPatientPlaceholder')) ?>" required>
                <option value=""><?= esc(lang('UI.appointmentSelectPatient')) ?></option>
                <?php if (!empty($selectedPatient)): ?><option value="<?= (int) $selectedPatient['id'] ?>" selected><?= esc($selectedPatient['first_name'] . ' ' . $selectedPatient['last_name']) ?> (<?= esc($selectedPatient['phone'] ?? $selectedPatient['patient_id'] ?? 'N/A') ?>)</option><?php endif; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.appointmentTypeRequired')) ?></label>
            <select name="appointment_type" class="<?= esc($inputClass) ?>" data-force-select2 data-allow-clear="false" required>
                <option value=""><?= esc(lang('UI.appointmentSelectType')) ?></option>
                <?php foreach (['consultation', 'treatment', 'follow_up', 'emergency', 'cleaning', 'checkup'] as $type): ?>
                    <option value="<?= esc($type) ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= esc(lang('UI.appointmentType' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type))))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.appointmentDoctorLabel')) ?></label>
            <select name="chair_name" class="<?= esc($inputClass) ?>" data-appointment-chair data-force-select2 data-allow-clear="false" required>
                <option value=""><?= esc(lang('UI.appointmentSelectDoctor')) ?></option>
                <?php foreach (($chair_options ?? []) as $chairOption): ?>
                    <option value="<?= esc($chairOption) ?>" <?= $selectedChair === $chairOption ? 'selected' : '' ?>><?= esc($chairOption) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.appointmentDateRequired')) ?></label>
            <input type="text" name="appointment_date" data-appointment-date data-clinic-date="1" value="<?= esc(old('appointment_date', $requested_date ?? date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?> appointment-date-input" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.appointmentDurationRequired')) ?></label>
            <select name="duration" class="<?= esc($inputClass) ?>" data-appointment-duration data-force-select2 data-allow-clear="false" required>
                <?php foreach ($durationOptions as $duration): ?>
                    <option value="<?= (int) $duration ?>" <?= $selectedDuration === (int) $duration ? 'selected' : '' ?>><?= (int) $duration ?> <?= esc(lang('UI.patientMinutes')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.appointmentTimeRequired')) ?></label>
            <select name="appointment_time" class="<?= esc($inputClass) ?>" data-appointment-time data-force-select2 data-allow-clear="false" required>
                <option value=""><?= esc(lang('UI.appointmentSelectPrerequisites')) ?></option>
            </select>
        </div>
    </div>
    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.appointmentAdditionalNotes')) ?></label>
        <textarea name="notes" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('UI.appointmentNotesPlaceholder')) ?>"><?= old('notes') ?></textarea>
    </div>
</div>
