<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
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

$currentProviderUserId = !empty($appointment['provider_user_id']) ? (int) $appointment['provider_user_id'] : null;
$storedChairName = trim((string) ($appointment['chair_name'] ?? ''));

$isCurrentProviderActive = false;
if ($currentProviderUserId !== null) {
    foreach ($providers as $p) {
        $pId = (int) ($p['user_id'] ?? $p['id'] ?? 0);
        if ($pId === $currentProviderUserId) {
            $isCurrentProviderActive = true;
            break;
        }
    }
}

$isLegacyOrInactive = ($currentProviderUserId === null) || (!$isCurrentProviderActive);
$selectedProviderId = old('provider_user_id', $currentProviderUserId !== null ? $currentProviderUserId : '');
if ($selectedProviderId !== '') {
    $selectedProviderId = (int) $selectedProviderId;
}
$selectedChair = old('chair_name', $appointment['chair_name'] ?? ($chairOptions[0] ?? ''));
?>
<style>
[dir="ltr"] input[data-clinic-date="1"].appointment-date-input {
    padding-left: 1rem !important;
    padding-right: 2.75rem !important;
}

[dir="rtl"] input[data-clinic-date="1"].appointment-date-input {
    padding-right: 2.75rem !important;
    padding-left: 1rem !important;
    text-align: right;
}
</style>
<div class="v3-record-flow">
    <header class="v3-flow-command">
        <div><h1><?= esc(lang('UI.appointmentEdit')) ?></h1><p><?= esc(lang('UI.appointmentUpdateFor', ['name' => $appointment['first_name'] . ' ' . $appointment['last_name']])) ?></p></div>
        <div class="v3-flow-navigation"><a href="<?= esc(workflow_back('appointment/' . $appointment['id'], $return_to ?? null)) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.appointmentBack')) ?></span>
                            </a></div>
    </header>
    <div class="v3-flow-body">
<form action="<?= base_url('appointment/' . $appointment['id'] . '/update') ?>" method="POST" class="v3-flow-form v3-flow-form--booking">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient Selection -->
<h2 class="v3-flow-step"><span>1</span><?= esc(lang('UI.appointmentPatientRequired')) ?></h2>
                        <div class="v3-flow-section v3-booking-identity">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user"></i>
                                    <span><?= esc(lang('UI.appointmentPatientRequired')) ?></span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 v3-select"
                                            data-searchable-select
                                            data-minimum-input-length="0"
                                            data-limit="5"
                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                            data-placeholder="<?= esc(lang('UI.appointmentSearchPatientPlaceholder')) ?>"
                                            required>
                                        <option value=""><?= esc(lang('UI.appointmentSelectPatient')) ?></option>
                                        <?php if (!empty($patients)): ?>
                                            <?php foreach ($patients as $patient): ?>
                                                <option value="<?= (int)$patient['id'] ?>" selected>
                                                    <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= esc($patient['phone'] ?? '') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                                    <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('patient_id') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?= esc(lang('UI.appointmentTypeRequired')) ?></span>
                                </label>
                                <div class="relative">
                                    <select name="appointment_type" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 v3-select" data-force-select2 required>
                                        <option value=""><?= esc(lang('UI.appointmentSelectType')) ?></option>
                                        <option value="consultation" <?= (old('appointment_type', $appointment['appointment_type']) == 'consultation') ? 'selected' : '' ?>><?= esc(lang('UI.appointmentTypeConsultation')) ?></option>
                                        <option value="treatment" <?= (old('appointment_type', $appointment['appointment_type']) == 'treatment') ? 'selected' : '' ?>><?= esc(lang('UI.appointmentTypeTreatment')) ?></option>
                                        <option value="follow_up" <?= (old('appointment_type', $appointment['appointment_type']) == 'follow_up') ? 'selected' : '' ?>><?= esc(lang('UI.appointmentTypeFollowUp')) ?></option>
                                        <option value="emergency" <?= (old('appointment_type', $appointment['appointment_type']) == 'emergency') ? 'selected' : '' ?>><?= esc(lang('UI.appointmentTypeEmergency')) ?></option>
                                        <option value="cleaning" <?= (old('appointment_type', $appointment['appointment_type']) == 'cleaning') ? 'selected' : '' ?>><?= esc(lang('UI.appointmentTypeCleaning')) ?></option>
                                        <option value="checkup" <?= (old('appointment_type', $appointment['appointment_type']) == 'checkup') ? 'selected' : '' ?>><?= esc(lang('UI.appointmentTypeCheckup')) ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('appointment_type')): ?>
                                    <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('appointment_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <?php if ($dynamicMode): ?>
                                <div class="relative">
                                    <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                        <i class="fas fa-user-md"></i>
                                        <span><?= esc(lang('DoctorScheduling.doctorRequired')) ?> *</span>
                                    </label>
                                    <?php if ($isLegacyOrInactive): ?>
                                        <div class="mb-3 border v3-border-default v3-bg-waiting p-3 v3-text-warning">
                                            <div class="flex items-start gap-2.5">
                                                <i class="fas fa-triangle-exclamation mt-0.5 v3-text-warning"></i>
                                                <div>
                                                    <p class="font-bold text-xs uppercase tracking-wide v3-text-warning"><?= esc(lang('DoctorScheduling.legacyProviderNotice')) ?></p>
                                                    <p class="mt-0.5 text-xs v3-text-warning">
                                                        <?php if ($currentProviderUserId !== null && !$isCurrentProviderActive): ?>
                                                            <?= esc(lang('DoctorScheduling.legacyProviderInactiveNotice', [$storedChairName !== '' ? $storedChairName : ('Doctor #' . $currentProviderUserId)])) ?>
                                                        <?php elseif ($storedChairName !== ''): ?>
                                                            <?= esc(lang('DoctorScheduling.legacyProviderReassignmentNotice', [$storedChairName])) ?>
                                                        <?php else: ?>
                                                            <?= esc(lang('DoctorScheduling.legacyProviderUnassignedNotice')) ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="relative">
                                        <?php if (!$schemaReady): ?>
                                            <div class="mb-3 border v3-border-default v3-bg-waiting p-4 v3-text-warning">
                                                <div class="flex items-center gap-3">
                                                    <i class="fas fa-triangle-exclamation v3-text-warning text-base"></i>
                                                    <div>
                                                        <h4 class="font-bold text-xs uppercase tracking-wide v3-text-warning"><?= esc(lang('DoctorScheduling.schemaNotReadyTitle')) ?></h4>
                                                        <p class="mt-0.5 text-xs v3-text-warning"><?= esc(lang('DoctorScheduling.schemaNotReady')) ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <select dir="ltr" style="text-align: start;" name="provider_user_id" id="providerSelect" class="w-full px-4 py-3 border-2 cursor-not-allowed v3-select" data-appointment-provider disabled required>
                                                <option value="<?= (int)($currentProviderUserId ?? 0) ?: '' ?>" selected><?= esc(($currentProviderUserId === null ? \App\Libraries\ProviderDisplayLabel::forUnassignedOption($storedChairName) : \App\Libraries\ProviderDisplayLabel::forInactiveOption($currentProviderUserId,$storedChairName))) ?> - <?= esc(lang('DoctorScheduling.schemaNotReadyTitle')) ?></option>
                                            </select>
                                        <?php elseif (empty($providers)): ?>
                                            <div class="mb-2 border v3-border-default v3-bg-waiting p-3 text-xs font-semibold v3-text-warning">
                                                <i class="fas fa-triangle-exclamation me-1"></i>
                                                <?= esc(lang('DoctorScheduling.noProvidersBookingDisabled')) ?>
                                            </div>
                                            <select dir="ltr" style="text-align: start;" name="provider_user_id" id="providerSelect" class="w-full px-4 py-3 border-2 cursor-not-allowed v3-select" data-appointment-provider disabled required>
                                                <option value="<?= (int)($currentProviderUserId ?? 0) ?: '' ?>" selected><?= esc(($currentProviderUserId === null ? \App\Libraries\ProviderDisplayLabel::forUnassignedOption($storedChairName) : \App\Libraries\ProviderDisplayLabel::forInactiveOption($currentProviderUserId,$storedChairName))) ?> - <?= esc(lang('DoctorScheduling.noEligibleProviders')) ?></option>
                                            </select>
                                        <?php else: ?>
                                            <select dir="ltr" style="text-align: start;" name="provider_user_id" id="providerSelect" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 v3-select" data-appointment-provider data-force-select2 <?= ($currentProviderUserId === null) ? '' : 'required' ?>>
                                                <option value=""><?= esc(lang('DoctorScheduling.selectDoctorPlaceholder')) ?></option>
                                                <?php if ($currentProviderUserId !== null && !$isCurrentProviderActive): ?>
                                                    <?php $inactiveLabel = \App\Libraries\ProviderDisplayLabel::forInactiveOption($currentProviderUserId, $storedChairName !== '' ? $storedChairName : null); ?>
                                                    <option value="<?= $currentProviderUserId ?>" <?= ($selectedProviderId === $currentProviderUserId) ? 'selected' : '' ?>>
                                                        <?= esc($inactiveLabel) ?>
                                                    </option>
                                                <?php endif; ?>
                                                <?php if ($currentProviderUserId === null && $storedChairName !== ''): ?>
                                                    <?php $unassignedLabel = \App\Libraries\ProviderDisplayLabel::forUnassignedOption($storedChairName); ?>
                                                    <option value="" <?= ($selectedProviderId === '') ? 'selected' : '' ?>>
                                                        <?= esc($unassignedLabel) ?>
                                                    </option>
                                                <?php endif; ?>
                                                <?php foreach ($providers as $provider): ?>
                                                    <?php
                                                    $pId = (int) ($provider['user_id'] ?? $provider['id'] ?? 0);
                                                    $pName = (string) ($provider['name'] ?? trim(($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? '')));
                                                    $pLabel = \App\Libraries\ProviderDisplayLabel::forOption($pId, $pName);
                                                    ?>
                                                    <option value="<?= $pId ?>" <?= ($selectedProviderId === $pId) ? 'selected' : '' ?>><?= esc($pLabel) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('provider_user_id')): ?>
                                        <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('provider_user_id') ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="relative">
                                    <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                        <i class="fas fa-door-open"></i>
                                        <span><?= esc(lang('UI.appointmentDoctorLabel')) ?> *</span>
                                    </label>
                                    <div class="relative">
                                        <select name="chair_name" id="chairName" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 v3-select" data-appointment-chair data-force-select2 required>
                                            <option value=""><?= esc(lang('UI.appointmentSelectDoctor')) ?></option>
                                            <?php if (!empty($selectedChair) && !in_array($selectedChair, ($chairOptions ?? []), true)): ?>
                                                <option value="<?= esc($selectedChair) ?>" selected><?= esc($selectedChair) ?></option>
                                            <?php endif; ?>
                                            <?php foreach (($chairOptions ?? []) as $chairOption): ?>
                                                <option value="<?= esc($chairOption) ?>" <?= ($selectedChair === $chairOption) ? 'selected' : '' ?>><?= esc($chairOption) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('chair_name')): ?>
                                        <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('chair_name') ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Date, Time & Duration -->
<h2 class="v3-flow-step"><span>2</span><?= esc(lang('UI.appointmentDateRequired')) ?></h2>
                        <div class="v3-flow-section v3-booking-time">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= esc(lang('UI.appointmentDateRequired')) ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="appointment_date" id="appointmentDate" data-clinic-date="1" value="<?= esc(old('appointment_date', $appointment['appointment_date']), 'attr') ?>" class="appointment-date-input w-full py-3 border-2 focus:outline-none focus:ring-4 v3-input" required>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('appointment_date')): ?>
                                    <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('appointment_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-clock v3-text-warning"></i>
                                    <span><?= esc(lang('UI.appointmentTimeRequired')) ?></span>
                                </label>
                                <div class="relative">
                                    <select name="appointment_time" id="appointmentTime" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 v3-focus-control v3-focus-control v3-select" data-force-select2 required>
                                        <option value=""><?= esc(lang('UI.appointmentSelectTime')) ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('appointment_time')): ?>
                                    <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('appointment_time') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-hourglass-half v3-text-danger"></i>
                                    <span><?= esc(lang('UI.appointmentDurationRequired')) ?></span>
                                </label>
                                <div class="relative">
                                    <select name="duration" id="duration" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 v3-focus-control v3-focus-control v3-select" data-force-select2 required>
                                        <?php
                                            $defaultDuration = (int) getAppointmentDuration();
                                            $selectedDuration = (int) old('duration', $appointment['duration'] ?? $defaultDuration);
                                            $durationOptions = [30, 45, 60, 90, 120, 180];
                                            if (!in_array($selectedDuration, $durationOptions, true) && $selectedDuration > 0) {
                                                $durationOptions[] = $selectedDuration;
                                                sort($durationOptions, SORT_NUMERIC);
                                            }
                                        ?>
                                        <?php foreach ($durationOptions as $durationOption): ?>
                                            <option value="<?= $durationOption ?>" <?= $selectedDuration === $durationOption ? 'selected' : '' ?>><?= $durationOption ?> <?= esc(lang('UI.patientMinutes')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('duration')): ?>
                                    <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('duration') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="border p-4">
                            <p class="text-sm font-bold"><?= esc(lang('UI.appointmentVisitStage')) ?></p>
                            <p class="mt-1 text-sm font-semibold"><?= esc(lang('UI.appointmentVisitStageEditHint')) ?></p>
                        </div>

                        <!-- Notes Section -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note"></i>
                                <span><?= esc(lang('UI.appointmentAdditionalNotes')) ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 resize-none v3-textarea" placeholder="<?= esc(lang('UI.appointmentNotesPlaceholder')) ?>"><?= esc(old('notes', $appointment['notes'])) ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> v3-text-muted text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t">
                            <a href="<?= esc(workflow_cancel('appointment/' . $appointment['id'], $return_to ?? ('appointment/' . $appointment['id']))) ?>" class="relative inline-flex items-center justify-center px-8 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.cancel')) ?></span>
                            </a>
                            <button type="submit" class="v3-flow-submit relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold v3-btn v3-btn--primary">

                                <i class="fas fa-save mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.appointmentUpdate')) ?></span>
                            </button>
                        </div>
                    </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointmentDate');
    const timeSelect = document.getElementById('appointmentTime');
    const durationSelect = document.getElementById('duration');
    const doctorSelect = document.querySelector('[data-appointment-provider]') || document.getElementById('providerSelect') || document.querySelector('[data-appointment-chair]') || document.getElementById('chairName');
    const isDynamic = Boolean(doctorSelect && (doctorSelect.matches('[data-appointment-provider]') || doctorSelect.name === 'provider_user_id'));
    const currentTime = '<?= $appointment['appointment_time'] ?>';
    const appointmentId = '<?= (int)$appointment['id'] ?>';
    const slotEditContext = <?= json_encode($slot_edit_context ?? '') ?>;
    const apiBase = '<?= base_url('appointment/available-time-slots') ?>';
    const appointmentI18n = {
        current: '<?= esc(lang('UI.appointmentCurrentTime'), 'js') ?>',
        validDateFirst: '<?= esc(lang('UI.appointmentEnterValidDateFirst'), 'js') ?>',
        loadingSlots: '<?= esc(lang('UI.appointmentLoadingTimeSlots'), 'js') ?>',
        selectTime: '<?= esc(lang('UI.appointmentSelectTime'), 'js') ?>',
        noSlots: '<?= esc(lang('UI.appointmentNoAvailableSlots'), 'js') ?>',
        loadError: '<?= esc(lang('UI.appointmentErrorLoadingSlots'), 'js') ?>',
        selectPrerequisites: '<?= esc(lang('UI.appointmentSelectPrerequisites'), 'js') ?>',
        invalidFormat: '<?= esc(lang('UI.appointmentInvalidResponseFormat'), 'js') ?>'
    };

    function setTimeOptions(options, selectedValue = '') {
        timeSelect.replaceChildren();
        options.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.value ?? '';
            option.textContent = item.label ?? '';
            if (selectedValue && option.value === selectedValue) {
                option.selected = true;
            }
            timeSelect.appendChild(option);
        });
        if (window.jQuery) {
            window.jQuery(timeSelect).trigger('change.select2');
        }
    }

    function normalizeSlotsPayload(data) {
        if (data && data.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (Array.isArray(data)) {
            return { slots: data, error: null };
        }

        if (data && typeof data === 'object') {
            const directSlots = Array.isArray(data.slots) ? data.slots : [];
            if (directSlots.length > 0) {
                return { slots: directSlots, error: null };
            }

            const extractedSlots = Object.values(data).filter((item) =>
                item && typeof item === 'object' && Object.prototype.hasOwnProperty.call(item, 'value') && Object.prototype.hasOwnProperty.call(item, 'display')
            );

            return {
                slots: extractedSlots,
                error: typeof data.error === 'string' ? data.error : null,
            };
        }

        return { slots: [], error: appointmentI18n.invalidFormat };
    }

    const originalDate = <?= json_encode($appointment['appointment_date']) ?>;
    const originalDuration = <?= (int)$appointment['duration'] ?>;
    const originalDoctor = <?= json_encode((string)($dynamicMode ? ($appointment['provider_user_id'] ?? '') : $appointment['chair_name'])) ?>;
    function appendCurrentTimeIfMissing(slots) {
        const selectedDate = window.parseClinicDateToIso ? (window.parseClinicDateToIso(dateInput.value) || dateInput.value) : dateInput.value;
        if (selectedDate !== originalDate || Number(durationSelect.value) !== originalDuration || String(doctorSelect?.value || '') !== originalDoctor || !currentTime) {
            return;
        }
        const hasCurrent = slots.some((slot) => slot && slot.value === currentTime);
        if (!hasCurrent) {
            const currentOption = document.createElement('option');
            currentOption.value = currentTime;
            currentOption.textContent = `${currentTime} (${appointmentI18n.current})`;
            currentOption.selected = true;
            timeSelect.appendChild(currentOption);
        }
        timeSelect.value = currentTime;
        timeSelect.disabled = false;
        if (window.jQuery && window.jQuery(timeSelect).data('select2')) {
            window.jQuery(timeSelect).trigger('change.select2');
        }
    }

    let currentSlotRequestId = 0;

    function loadAvailableTimeSlots() {
        const requestId = ++currentSlotRequestId;
        const rawDate = (dateInput.value || '').trim();
        const date = window.parseClinicDateToIso ? (window.parseClinicDateToIso(rawDate) || rawDate) : rawDate;
        const duration = durationSelect.value;
        const doctorVal = (doctorSelect?.value || '').trim();

        if (date && duration && doctorVal) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                setTimeOptions([{ value: '', label: appointmentI18n.validDateFirst }], '');
                appendCurrentTimeIfMissing([]);
                return;
            }

            setTimeOptions([{ value: '', label: appointmentI18n.loadingSlots }], '');

            let targetUrl;
            try {
                targetUrl = new URL(apiBase, window.location.origin);
            } catch (e) {
                targetUrl = new URL(apiBase, window.location.href);
            }
            targetUrl.searchParams.set('date', date);
            targetUrl.searchParams.set('duration', duration);
            targetUrl.searchParams.set('exclude_id', appointmentId);
            targetUrl.searchParams.set('edit_context', slotEditContext);
            if (isDynamic) {
                targetUrl.searchParams.set('provider_user_id', doctorVal);
                targetUrl.searchParams.delete('chair_name');
            } else {
                targetUrl.searchParams.set('chair_name', doctorVal);
                targetUrl.searchParams.delete('provider_user_id');
            }

            fetch(targetUrl.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (requestId !== currentSlotRequestId) {
                        return;
                    }
                    const normalized = normalizeSlotsPayload(data);
                    if (normalized.slots.length > 0) {
                        const options = [{ value: '', label: appointmentI18n.selectTime }].concat(
                            normalized.slots.map((slot) => ({ value: slot.value, label: slot.display }))
                        );
                        setTimeOptions(options, currentTime);
                        appendCurrentTimeIfMissing(normalized.slots);
                        return;
                    }

                    if (normalized.error) {
                        setTimeOptions([{ value: '', label: normalized.error }], '');
                    } else {
                        setTimeOptions([{ value: '', label: appointmentI18n.noSlots }], '');
                    }
                    appendCurrentTimeIfMissing(normalized.slots);
                })
                .catch(error => {
                    if (requestId !== currentSlotRequestId) {
                        return;
                    }
                    console.error('Error loading time slots:', error);
                    setTimeOptions([{ value: '', label: appointmentI18n.loadError }], '');
                    appendCurrentTimeIfMissing([]);
                });
        } else {
            setTimeOptions([{ value: '', label: appointmentI18n.selectPrerequisites }], '');
            appendCurrentTimeIfMissing([]);
        }
    }

    function bindSlotRefresh(selectElement) {
        if (!selectElement) {
            return;
        }

        selectElement.addEventListener('change', loadAvailableTimeSlots);

        if (window.jQuery) {
            window.jQuery(selectElement).on('select2:select select2:clear change', loadAvailableTimeSlots);
        }
    }

    dateInput.addEventListener('change', loadAvailableTimeSlots);
    dateInput.addEventListener('input', loadAvailableTimeSlots);
    bindSlotRefresh(durationSelect);
    bindSlotRefresh(doctorSelect);

    // Load initial time slots (this will also set the current time)
    loadAvailableTimeSlots();
});
</script>
<?= $this->endSection() ?>



