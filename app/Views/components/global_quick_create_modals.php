<?php
$currentUri = trim(uri_string(), '/');
$returnTo = $currentUri !== '' ? $currentUri : 'dashboard';
$isRtl = !empty($is_rtl);

$renderPatientCreate = !in_array($currentUri, ['patient', 'patients', 'patient/create', 'patients/create'], true)
    && !preg_match('#^patients?/\d+/edit$#', $currentUri);
$renderExaminationCreate = !in_array($currentUri, ['examination', 'examinations', 'examination/create', 'examinations/create'], true)
    && !preg_match('#^appointments?/\d+$#', $currentUri);
$renderAppointmentCreate = !in_array($currentUri, ['appointment', 'appointments', 'appointment/create', 'appointments/create'], true)
    && !preg_match('#^examinations?/\d+$#', $currentUri);
?>

<?php if ($renderPatientCreate): ?>
    <?= view('patient/partials/quick_create_modal', [
        'return_to' => $returnTo,
        'is_rtl' => $isRtl,
        'validation' => session()->getFlashdata('validation'),
    ]) ?>
<?php endif; ?>

<?php if ($renderAppointmentCreate): ?>
    <?= view('appointment/partials/quick_create_modal', [
        'return_to' => $returnTo,
        'isRtl' => $isRtl,
        'contextual' => false,
        'selected_patient' => null,
        'chair_options' => service('settings')->getBookingChairNames(),
        'requested_date' => old('appointment_date', date('Y-m-d')),
        'appointment_type_prefill' => old('appointment_type', ''),
    ]) ?>
<?php endif; ?>

<?php if ($renderExaminationCreate): ?>
    <?= view('examination/partials/quick_create_modal', [
        'return_to' => $returnTo,
        'is_rtl' => $isRtl,
    ]) ?>
<?php endif; ?>
