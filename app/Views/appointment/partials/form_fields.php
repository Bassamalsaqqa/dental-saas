<?php
$validation = session()->getFlashdata('validation');
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? null;
$fieldPrefix = !empty($field_id_prefix) ? $field_id_prefix : ($contextual ? 'contextual_' : 'field_');
$inputClass = 'v3-field-control';
$selectedType = old('appointment_type', $appointment_type_prefill ?? '');

$durationOptions = [30, 45, 60, 90, 120, 180];
$selectedDuration = (int) old('duration', (int) getAppointmentDuration());
if (!in_array($selectedDuration, $durationOptions, true) && $selectedDuration > 0) {
    $durationOptions[] = $selectedDuration;
    sort($durationOptions, SORT_NUMERIC);
}

$bookingContext = $booking_context ?? null;
if ($bookingContext === null && class_exists(\App\Services\ProviderDirectoryService::class)) {
    $activeClinicId = (int) (session('active_clinic_id') ?? 0);
    if ($activeClinicId > 0) {
        $providerService = new \App\Services\ProviderDirectoryService();
        $isDynamic = false;
        try {
            $isDynamic = $providerService->isEnabled($activeClinicId);
            $bookingContext = $providerService->getBookingContext($activeClinicId);
        } catch (\Throwable $e) {
            if ($isDynamic || (strpos($e->getMessage(), 'DYNAMIC_SCHEDULING') !== false)) {
                $bookingContext = [
                    'enabled' => true,
                    'schema_ready' => false,
                    'providers' => [],
                    'chair_options' => [],
                    'error' => $e->getMessage(),
                ];
            } else {
                $bookingContext = null;
            }
        }
    }
}

$dynamicMode = !empty($bookingContext['enabled']);
$schemaReady = !isset($bookingContext['schema_ready']) || !empty($bookingContext['schema_ready']);

$providers = is_array($bookingContext['providers'] ?? null) ? $bookingContext['providers'] : ($clinic_providers ?? ($providers ?? []));
$chairOptions = is_array($bookingContext['chair_options'] ?? null) ? $bookingContext['chair_options'] : ($chair_options ?? []);
$selectedChair = old('chair_name', $chairOptions[0] ?? '');
$selectedProviderId = old('provider_user_id', $provider_id_prefill ?? ($provider_user_id ?? ''));
if ($selectedProviderId !== '') {
    $selectedProviderId = (int) $selectedProviderId;
}
?>
<div class="v3-flow-fields" data-contextual-appointment-form data-slots-url="<?= esc(base_url('appointment/available-time-slots')) ?>">
    <?php if ($contextual && !empty($selectedPatient['id'])): ?>
        <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
        <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
    <?php else: ?>
        <div>
            <label for="<?= esc($fieldPrefix) ?>patientSelect" class="mb-2 block text-sm font-semibold"><?= esc(lang('UI.appointmentPatientRequired')) ?></label>
            <select name="patient_id" id="<?= esc($fieldPrefix) ?>patientSelect" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc(lang('UI.appointmentSearchPatientPlaceholder')) ?>" required>
                <option value=""><?= esc(lang('UI.appointmentSelectPatient')) ?></option>
                <?php if (!empty($selectedPatient)): ?><option value="<?= (int) $selectedPatient['id'] ?>" selected><?= esc($selectedPatient['first_name'] . ' ' . $selectedPatient['last_name']) ?> (<?= esc($selectedPatient['phone'] ?? $selectedPatient['patient_id'] ?? 'N/A') ?>)</option><?php endif; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label for="<?= esc($fieldPrefix) ?>appointmentType" class="mb-2 block text-sm font-semibold"><?= esc(lang('UI.appointmentTypeRequired')) ?></label>
            <select name="appointment_type" id="<?= esc($fieldPrefix) ?>appointmentType" class="<?= esc($inputClass) ?>" data-force-select2 data-allow-clear="false" required>
                <option value=""><?= esc(lang('UI.appointmentSelectType')) ?></option>
                <?php foreach (['consultation', 'treatment', 'follow_up', 'emergency', 'cleaning', 'checkup'] as $type): ?>
                    <option value="<?= esc($type) ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= esc(lang('UI.appointmentType' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type))))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($dynamicMode): ?>
            <div>
                <label for="<?= esc($fieldPrefix) ?>providerSelect" class="mb-2 block text-sm font-semibold"><?= esc(lang('DoctorScheduling.doctorRequired')) ?> *</label>
                <?php if (!$schemaReady): ?>
                    <div class="mb-2 border border-amber-300 bg-amber-50 p-2.5 text-xs font-semibold text-amber-900">
                        <i class="fas fa-triangle-exclamation me-1 text-amber-600"></i>
                        <?= esc(lang('DoctorScheduling.schemaNotReady')) ?>
                    </div>
                    <select dir="ltr" style="text-align: start;" name="provider_user_id" id="<?= esc($fieldPrefix) ?>providerSelect" class="<?= esc($inputClass) ?> cursor-not-allowed" data-appointment-provider disabled required>
                        <option value=""><?= esc(lang('DoctorScheduling.schemaNotReadyTitle')) ?></option>
                    </select>
                <?php elseif (empty($providers)): ?>
                    <div class="mb-2 border border-amber-200 bg-amber-50 p-2.5 text-xs font-semibold text-amber-800">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        <?= esc(lang('DoctorScheduling.noProvidersBookingDisabled')) ?>
                    </div>
                    <select dir="ltr" style="text-align: start;" name="provider_user_id" id="<?= esc($fieldPrefix) ?>providerSelect" class="<?= esc($inputClass) ?> cursor-not-allowed" data-appointment-provider disabled required>
                        <option value=""><?= esc(lang('DoctorScheduling.noEligibleProviders')) ?></option>
                    </select>
                <?php else: ?>
                    <select dir="ltr" style="text-align: start;" name="provider_user_id" id="<?= esc($fieldPrefix) ?>providerSelect" class="<?= esc($inputClass) ?>" data-appointment-provider data-force-select2 data-allow-clear="false" required>
                        <option value=""><?= esc(lang('DoctorScheduling.selectDoctorPlaceholder')) ?></option>
                        <?php foreach ($providers as $provider): ?>
                            <?php
                            $pId = (int) ($provider['user_id'] ?? $provider['id'] ?? 0);
                            $pName = (string) ($provider['name'] ?? trim(($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? '')));
                            $pLabel = \App\Libraries\ProviderDisplayLabel::forOption($pId, $pName);
                            ?>
                            <option value="<?= $pId ?>" <?= ($selectedProviderId === $pId) ? 'selected' : '' ?>><?= esc($pLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div>
                <label for="<?= esc($fieldPrefix) ?>chairSelect" class="mb-2 block text-sm font-semibold"><?= esc(lang('UI.appointmentDoctorLabel')) ?></label>
                <select name="chair_name" id="<?= esc($fieldPrefix) ?>chairSelect" class="<?= esc($inputClass) ?>" data-appointment-chair data-force-select2 data-allow-clear="false" required>
                    <option value=""><?= esc(lang('UI.appointmentSelectDoctor')) ?></option>
                    <?php foreach (($chairOptions ?? []) as $chairOption): ?>
                        <option value="<?= esc($chairOption) ?>" <?= $selectedChair === $chairOption ? 'selected' : '' ?>><?= esc($chairOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div>
            <label for="<?= esc($fieldPrefix) ?>appointmentDate" class="mb-2 block text-sm font-semibold"><?= esc(lang('UI.appointmentDateRequired')) ?></label>
            <input type="text" name="appointment_date" id="<?= esc($fieldPrefix) ?>appointmentDate" data-appointment-date data-clinic-date="1" value="<?= esc(old('appointment_date', $requested_date ?? date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?> appointment-date-input" required>
        </div>
        <div>
            <label for="<?= esc($fieldPrefix) ?>durationSelect" class="mb-2 block text-sm font-semibold"><?= esc(lang('UI.appointmentDurationRequired')) ?></label>
            <select name="duration" id="<?= esc($fieldPrefix) ?>durationSelect" class="<?= esc($inputClass) ?>" data-appointment-duration data-force-select2 data-allow-clear="false" required>
                <?php foreach ($durationOptions as $duration): ?>
                    <option value="<?= (int) $duration ?>" <?= $selectedDuration === (int) $duration ? 'selected' : '' ?>><?= (int) $duration ?> <?= esc(lang('UI.patientMinutes')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="<?= esc($fieldPrefix) ?>appointmentTime" class="mb-2 block text-sm font-semibold"><?= esc(lang('UI.appointmentTimeRequired')) ?></label>
            <select name="appointment_time" id="<?= esc($fieldPrefix) ?>appointmentTime" class="<?= esc($inputClass) ?>" data-appointment-time data-force-select2 data-allow-clear="false" required>
                <option value=""><?= esc(lang('UI.appointmentSelectPrerequisites')) ?></option>
            </select>
        </div>
    </div>
    <div class="mt-5">
        <label for="<?= esc($fieldPrefix) ?>notes" class="mb-2 block text-sm font-semibold"><?= esc(lang('UI.appointmentAdditionalNotes')) ?></label>
        <textarea name="notes" id="<?= esc($fieldPrefix) ?>notes" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('UI.appointmentNotesPlaceholder')) ?>"><?= esc(old('notes')) ?></textarea>
    </div>
</div>
