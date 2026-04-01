<?= $this->extend('layouts/main_auth') ?>

<?php
// Function to convert Universal numbering to FDI numbering
function getFDINumber($universalNumber) {
    $fdiMap = [
        // Upper Right (1-8) -> FDI 11-18
        1 => '11', 2 => '12', 3 => '13', 4 => '14', 5 => '15', 6 => '16', 7 => '17', 8 => '18',
        // Upper Left (9-16) -> FDI 21-28  
        9 => '21', 10 => '22', 11 => '23', 12 => '24', 13 => '25', 14 => '26', 15 => '27', 16 => '28',
        // Lower Left (17-24) -> FDI 31-38
        17 => '31', 18 => '32', 19 => '33', 20 => '34', 21 => '35', 22 => '36', 23 => '37', 24 => '38',
        // Lower Right (25-32) -> FDI 41-48
        25 => '41', 26 => '42', 27 => '43', 28 => '44', 29 => '45', 30 => '46', 31 => '47', 32 => '48'
    ];
    
    return $fdiMap[$universalNumber] ?? $universalNumber;
}

$isRtl = !empty($is_rtl);
$fullName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
$patientGenderMap = [
    'male' => lang('UI.patientMale'),
    'female' => lang('UI.patientFemale'),
    'other' => lang('UI.patientOther'),
];
$patientGenderLabel = $patientGenderMap[strtolower((string) ($patient['gender'] ?? 'other'))] ?? lang('UI.patientOther');
$patientStatusKeyMap = [
    'active' => 'patientStatusActive',
    'inactive' => 'patientStatusInactive',
    'pending' => 'patientStatusPending',
];
$patientStatusValue = strtolower((string) ($patient['status'] ?? 'pending'));
$patientStatusLabel = lang('UI.' . ($patientStatusKeyMap[$patientStatusValue] ?? 'patientStatusPending'));
$patientStatusToneMap = [
    'active' => 'patient-hero-status patient-hero-status-active',
    'inactive' => 'patient-hero-status patient-hero-status-inactive',
    'pending' => 'patient-hero-status patient-hero-status-pending',
];
$patientStatusTone = $patientStatusToneMap[$patientStatusValue] ?? $patientStatusToneMap['pending'];

$creatorName = trim(($patient['creator_first_name'] ?? '') . ' ' . ($patient['creator_last_name'] ?? ''));
$memberSince = !empty($patient['created_at']) ? formatDate($patient['created_at']) : lang('UI.patientNotProvided');
$patientPhone = !empty($patient['phone']) ? $patient['phone'] : lang('UI.patientNoPhone');
$patientEmail = !empty($patient['email']) ? $patient['email'] : lang('UI.patientNoEmail');
$patientCity = !empty($patient['city']) ? $patient['city'] : lang('UI.patientNotProvided');
$patientWhatsApp = $patient_whatsapp ?? null;
$patientWhatsAppOptout = !empty($patient_whatsapp_optout);
$patientWhatsAppNumber = !empty($patientWhatsApp['wa_number']) ? (string) $patientWhatsApp['wa_number'] : '';
$patientWhatsAppChipLabel = lang('UI.patientWhatsAppNotSet');
$patientWhatsAppChipClass = 'patient-meta-chip-whatsapp-idle';
$patientWhatsAppChipValue = $patientWhatsAppNumber !== '' ? $patientWhatsAppNumber : lang('UI.patientWhatsAppNotSet');

if ($patientWhatsAppOptout) {
    $patientWhatsAppChipLabel = lang('UI.patientWhatsAppOptedOut');
    $patientWhatsAppChipClass = 'patient-meta-chip-whatsapp-blocked';
    $patientWhatsAppChipValue = 'STOP';
} elseif ($patientWhatsAppNumber !== '') {
    $patientWhatsAppChipLabel = lang('UI.patientWhatsAppReady');
    $patientWhatsAppChipClass = 'patient-meta-chip-whatsapp-ready';
    $patientWhatsAppChipValue = 'OK';
} else {
    $patientWhatsAppChipValue = lang('UI.patientAddShort');
}

$patientGenderInitialMap = [
    'male' => 'M',
    'female' => 'F',
    'other' => lang('UI.patientOther'),
];

$patientAgeLabel = lang('UI.patientNotProvided');
if (!empty($patient['date_of_birth'])) {
    try {
        $dob = new DateTime($patient['date_of_birth']);
        $patientAgeLabel = $dob->diff(new DateTime())->y . ' ' . lang('UI.patientYears');
    } catch (Throwable $e) {
        $patientAgeLabel = lang('UI.patientNotProvided');
    }
}

$medicalAlerts = [
    [
        'enabled' => !empty($patient['alert_has_allergies'] ?? 0),
        'icon' => 'fas fa-allergies',
        'icon_class' => 'text-red-600',
        'label' => lang('UI.patientAllergies'),
        'details' => $patient['alert_allergies_details'] ?? null,
    ],
    [
        'enabled' => !empty($patient['alert_taking_medications'] ?? 0),
        'icon' => 'fas fa-pills',
        'icon_class' => 'text-blue-600',
        'label' => lang('UI.patientTakingMedications'),
        'details' => $patient['alert_medications_details'] ?? null,
    ],
    [
        'enabled' => !empty($patient['alert_blood_thinners'] ?? 0),
        'icon' => 'fas fa-tint',
        'icon_class' => 'text-rose-600',
        'label' => lang('UI.patientTakingBloodThinners'),
        'details' => $patient['alert_blood_thinners_details'] ?? null,
    ],
    [
        'enabled' => !empty($patient['alert_diabetes'] ?? 0),
        'icon' => 'fas fa-syringe',
        'icon_class' => 'text-amber-600',
        'label' => lang('UI.patientDiabetes'),
        'details' => $patient['alert_diabetes_details'] ?? null,
    ],
    [
        'enabled' => !empty($patient['alert_high_blood_pressure'] ?? 0),
        'icon' => 'fas fa-heartbeat',
        'icon_class' => 'text-pink-600',
        'label' => lang('UI.patientHighBloodPressure'),
        'details' => $patient['alert_blood_pressure_details'] ?? null,
    ],
    [
        'enabled' => !empty($patient['alert_heart_condition'] ?? 0),
        'icon' => 'fas fa-heart',
        'icon_class' => 'text-red-700',
        'label' => lang('UI.patientHeartCondition'),
        'details' => $patient['alert_heart_condition_details'] ?? null,
    ],
    [
        'enabled' => !empty($patient['alert_pregnant'] ?? 0),
        'icon' => 'fas fa-baby',
        'icon_class' => 'text-fuchsia-600',
        'label' => lang('UI.patientPregnant'),
        'details' => $patient['alert_pregnancy_details'] ?? null,
    ],
    [
        'enabled' => !empty($patient['alert_anesthesia_problem'] ?? 0),
        'icon' => 'fas fa-mask-face',
        'icon_class' => 'text-indigo-600',
        'label' => lang('UI.patientAnesthesiaProblem'),
        'details' => $patient['alert_anesthesia_problem_details'] ?? null,
    ],
];
$activeMedicalAlerts = array_values(array_filter($medicalAlerts, static fn ($row) => !empty($row['enabled'])));
$hasStructuredMedicalAlerts = !empty($activeMedicalAlerts);
$medicalAlertCount = count($activeMedicalAlerts);

$lastVisitLabel = lang('UI.patientNoVisitsYet');
$lastVisitSubLabel = lang('UI.patientClinicalHistoryState');
$latestTimestamp = null;
$latestTimestampLabel = null;

$updateLatest = static function (?string $dateValue, string $label) use (&$latestTimestamp, &$latestTimestampLabel): void {
    if (!$dateValue) {
        return;
    }

    $timestamp = strtotime($dateValue);
    if ($timestamp === false) {
        return;
    }

    if ($latestTimestamp === null || $timestamp > $latestTimestamp) {
        $latestTimestamp = $timestamp;
        $latestTimestampLabel = $label;
    }
};

foreach ($appointments as $appointmentItem) {
    $updateLatest($appointmentItem['appointment_date'] ?? null, $appointmentItem['appointment_type'] ?? lang('UI.patientAppointments'));
}
foreach ($examinations as $examinationItem) {
    $updateLatest($examinationItem['examination_date'] ?? null, $examinationItem['examination_type'] ?? lang('UI.patientExaminations'));
}
foreach ($treatments as $treatmentItem) {
    $updateLatest($treatmentItem['start_date'] ?? null, $treatmentItem['treatment_name'] ?? lang('UI.patientTreatments'));
}
if ($latestTimestamp !== null) {
    $lastVisitLabel = formatDate(date('Y-m-d', $latestTimestamp));
    $lastVisitSubLabel = $latestTimestampLabel ?: lang('UI.patientClinicalHistoryState');
}
$lastVisitChipValue = $latestTimestamp !== null ? $lastVisitLabel : lang('UI.patientNoneShort');

$nextAppointmentLabel = lang('UI.patientNoUpcomingAppointment');
$nextAppointmentSubLabel = lang('UI.patientAppointmentScheduleState');
$nextAppointmentTimestamp = null;
$todayCutoff = strtotime(date('Y-m-d'));
foreach ($appointments as $appointmentItem) {
    $status = strtolower((string) ($appointmentItem['status'] ?? ''));
    if (in_array($status, ['cancelled', 'completed'], true)) {
        continue;
    }
    $dateValue = $appointmentItem['appointment_date'] ?? null;
    if (!$dateValue) {
        continue;
    }
    $timestamp = strtotime($dateValue . ' ' . ($appointmentItem['appointment_time'] ?? '00:00:00'));
    if ($timestamp === false || $timestamp < $todayCutoff) {
        continue;
    }
    if ($nextAppointmentTimestamp === null || $timestamp < $nextAppointmentTimestamp) {
        $nextAppointmentTimestamp = $timestamp;
        $nextAppointmentLabel = formatDate(date('Y-m-d', $timestamp));
        $timeValue = !empty($appointmentItem['appointment_time']) ? formatTime($appointmentItem['appointment_time']) : null;
        $typeValue = $appointmentItem['appointment_type'] ?? lang('UI.patientAppointments');
        $nextAppointmentSubLabel = trim($typeValue . ($timeValue ? ' · ' . $timeValue : ''));
    }
}

$nextAppointmentChipValue = $nextAppointmentTimestamp !== null ? $nextAppointmentLabel : lang('UI.patientNoneShort');

$activeTreatmentsCount = 0;
foreach ($treatments as $treatmentItem) {
    $status = strtolower((string) ($treatmentItem['status'] ?? ''));
    if (!in_array($status, ['completed', 'cancelled'], true)) {
        $activeTreatmentsCount++;
    }
}

$totalBilled = 0.0;
$totalPaid = 0.0;
$outstandingBalance = 0.0;
$openInvoicesCount = 0;
foreach ($finances as $financeItem) {
    if (($financeItem['transaction_type'] ?? 'invoice') !== 'invoice') {
        continue;
    }

    $totalAmount = (float) ($financeItem['total_amount'] ?? (($financeItem['amount'] ?? 0) - ($financeItem['discount_amount'] ?? 0) + ($financeItem['tax_amount'] ?? 0)));
    $balanceDue = isset($financeItem['balance_due']) ? max(0, (float) $financeItem['balance_due']) : 0.0;
    $paidAmount = max(0, $totalAmount - $balanceDue);

    $totalBilled += $totalAmount;
    $totalPaid += $paidAmount;
    $outstandingBalance += $balanceDue;

    if ($balanceDue > 0.009) {
        $openInvoicesCount++;
    }
}

$outstandingBalanceLabel = $outstandingBalance > 0.009
    ? formatCurrency($outstandingBalance)
    : lang('UI.patientNoBalanceDue');
$outstandingBalanceSubLabel = $outstandingBalance > 0.009
    ? lang('UI.patientProfileInvoicesOpen', ['count' => $openInvoicesCount])
    : lang('UI.patientFinanceClearState');
$outstandingBalanceChipValue = $outstandingBalance > 0.009 ? formatCurrency($outstandingBalance) : lang('UI.patientNoneShort');
$clinicalSnapshotToneClass = $medicalAlertCount > 0 ? 'patient-rail-card-clinical-alert' : 'patient-rail-card-clinical-calm';
$financialSnapshotToneClass = $outstandingBalance > 0.009 ? 'patient-rail-card-financial-due' : 'patient-rail-card-financial-clear';
$clinicalSnapshotState = $medicalAlertCount > 0 ? lang('UI.patientMedicalAlertsSummary') . ': ' . $medicalAlertCount : lang('UI.patientStatusActive');
$financialSnapshotState = $outstandingBalance > 0.009 ? lang('UI.patientOutstandingBalanceShort') : lang('UI.patientFinanceClearState');
$patientGenderShort = $patientGenderInitialMap[strtolower((string) ($patient['gender'] ?? 'other'))] ?? lang('UI.patientOther');
$patientAgeShort = preg_match('/^\d+/', $patientAgeLabel) ? preg_replace('/\s+' . preg_quote(lang('UI.patientYears'), '/') . '$/u', 'y', $patientAgeLabel) : lang('UI.patientNoneShort');
$patientGenderChipValue = $patientGenderShort . ' - ' . $patientAgeShort;
$patientCityChipValue = $patientCity === lang('UI.patientNotProvided') ? lang('UI.patientNoneShort') : $patientCity;
$patientPhoneChipValue = in_array($patientPhone, [lang('UI.patientNoPhone'), lang('UI.patientNotProvided')], true) ? lang('UI.patientNoneShort') : preg_replace('/\s+/', '', $patientPhone);

$patientGenderValue = strtolower((string) ($patient['gender'] ?? 'other'));
$patientHeroThemeClass = 'patient-hero-card-neutral';
$patientAvatarToneClass = 'patient-avatar-badge-neutral';
$patientAvatarIconClass = 'fas fa-user';

if ($patientGenderValue === 'female') {
    $patientHeroThemeClass = 'patient-hero-card-female';
    $patientAvatarToneClass = 'patient-avatar-badge-female';
    $patientAvatarIconClass = 'fas fa-person-dress';
} elseif ($patientGenderValue === 'male') {
    $patientHeroThemeClass = 'patient-hero-card-male';
    $patientAvatarToneClass = 'patient-avatar-badge-male';
    $patientAvatarIconClass = 'fas fa-person';
}

$heroInfoCapsules = [
    ['label' => lang('UI.patientPatientIdShort'), 'display_label' => '', 'value' => $patient['patient_id'], 'icon' => 'fas fa-id-card', 'class' => 'patient-hero-chip-ltr'],
    ['label' => lang('UI.patientGender'), 'display_label' => '', 'value' => $patientAgeShort, 'icon' => 'fas fa-cake-candles', 'class' => ''],
    ['label' => lang('UI.patientPhone'), 'display_label' => '', 'value' => $patientPhoneChipValue, 'icon' => 'fas fa-phone-alt', 'class' => 'patient-hero-chip-ltr'],
    ['label' => lang('UI.patientCity'), 'value' => $patientCityChipValue, 'icon' => 'fas fa-map-marker-alt', 'class' => ''],
    ['label' => lang('UI.patientWhatsApp'), 'value' => $patientWhatsAppChipValue, 'icon' => 'fab fa-whatsapp', 'class' => trim('patient-hero-chip-action ' . $patientWhatsAppChipClass), 'is_button' => has_permission('patients', 'edit')],
    ['label' => lang('UI.patientOutstandingBalanceShort'), 'value' => $outstandingBalanceChipValue, 'icon' => 'fas fa-wallet', 'class' => ''],
    ['label' => lang('UI.patientNextAppointmentShort'), 'value' => $nextAppointmentChipValue, 'icon' => 'fas fa-calendar-check', 'class' => ''],
    ['label' => lang('UI.patientLastVisitShort'), 'value' => $lastVisitChipValue, 'icon' => 'fas fa-history', 'class' => ''],
];
?>

<?= $this->section('content') ?>
<div class="container patient-view mx-auto px-4 py-6 sm:px-6 sm:py-8">
    <div class="space-y-8">
        <section class="patient-hero-card <?= esc($patientHeroThemeClass) ?> overflow-hidden rounded-[28px] border border-slate-200">
            <div class="patient-hero-overlay"></div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="flex flex-col gap-5">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                            <span><?= esc(lang('UI.patientDetailsTitle')) ?></span>
                            <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                            <span><?= esc(lang('UI.patientMemberSince')) ?> <?= esc($memberSince) ?></span>
                        </div>
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                            <div class="patient-avatar-badge <?= esc($patientAvatarToneClass) ?>">
                                <i class="<?= esc($patientAvatarIconClass) ?>"></i>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($fullName) ?></h1>
                                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold <?= esc($patientStatusTone) ?>">
                                            <?= esc($patientStatusLabel) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="patient-hero-chip-grid">
                                    <?php foreach ($heroInfoCapsules as $capsule): ?>
                                        <?php
                                        $capsuleClass = trim('patient-hero-chip ' . ($capsule['class'] ?? ''));
                                        $capsuleValue = (string) ($capsule['value'] ?? lang('UI.patientNotProvided'));
                                        $capsuleLabel = (string) ($capsule['label'] ?? '');
                                        $capsuleDisplayLabel = array_key_exists('display_label', $capsule) ? (string) $capsule['display_label'] : $capsuleLabel;
                                        $capsuleIcon = (string) ($capsule['icon'] ?? 'fas fa-circle');
                                        $isButton = !empty($capsule['is_button']);
                                        ?>
                                        <?php if ($isButton): ?>
                                            <button type="button" class="<?= esc($capsuleClass) ?>" title="<?= esc($capsuleLabel . ': ' . $capsuleValue) ?>" onclick="openPatientWhatsAppIdentityModal('<?= esc($patientWhatsAppNumber, 'js') ?>')">
                                                <span class="patient-hero-chip-icon"><i class="<?= esc($capsuleIcon) ?>"></i></span>
                                                <span class="patient-hero-chip-text">
                                                    <?php if ($capsuleDisplayLabel !== ''): ?><span class="patient-hero-chip-label"><?= esc($capsuleDisplayLabel) ?></span><?php endif; ?>
                                                    <span class="patient-hero-chip-value"><?= esc($capsuleValue) ?></span>
                                                </span>
                                            </button>
                                        <?php else: ?>
                                            <div class="<?= esc($capsuleClass) ?>" title="<?= esc($capsuleLabel . ': ' . $capsuleValue) ?>">
                                                <span class="patient-hero-chip-icon"><i class="<?= esc($capsuleIcon) ?>"></i></span>
                                                <span class="patient-hero-chip-text">
                                                    <?php if ($capsuleDisplayLabel !== ''): ?><span class="patient-hero-chip-label"><?= esc($capsuleDisplayLabel) ?></span><?php endif; ?>
                                                    <span class="patient-hero-chip-value"><?= esc($capsuleValue) ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full space-y-2 xl:w-[21rem]">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <?php if (has_permission('patients', 'edit')): ?>
                                <a href="<?= base_url('patient/' . $patient['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="patient-header-action patient-header-action-primary">
                                    <i class="fas fa-pen"></i>
                                    <span><?= esc(lang('UI.patientEditPatient')) ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patients') ?>" class="patient-header-action patient-header-action-soft">
                                <i class="fas fa-arrow-left"></i>
                                <span><?= esc(lang('UI.patientBackToPatients')) ?></span>
                            </a>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <?php if (has_permission('appointments', 'create')): ?>
                                <a href="<?= base_url('appointment/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>" class="patient-header-action patient-header-action-emerald">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span><?= esc(lang('UI.patientBookAppointment')) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if (has_permission('finance', 'create')): ?>
                                <a href="<?= base_url('finance/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>" class="patient-header-action patient-header-action-gold">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span><?= esc(lang('UI.patientCreateInvoice')) ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <?php if (has_permission('examinations', 'create')): ?>
                                <a href="<?= base_url('examination/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>" class="patient-hero-clinical-button">
                                    <i class="fas fa-stethoscope"></i>
                                    <span><?= esc(lang('UI.patientCreateExamination')) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('treatment/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>" class="patient-hero-clinical-button">
                                    <i class="fas fa-tooth"></i>
                                    <span><?= esc(lang('UI.patientCreateTreatment')) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if (has_permission('prescriptions', 'create')): ?>
                                <a href="<?= base_url('prescription/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>" class="patient-hero-clinical-button">
                                    <i class="fas fa-prescription-bottle-alt"></i>
                                    <span><?= esc(lang('UI.patientCreatePrescription')) ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <a href="<?= base_url('odontogram/' . $patient['id']) ?>" class="patient-hero-tool-button">
                                <i class="fas fa-tooth"></i>
                                <span><?= esc(lang('UI.patientOdontogram')) ?></span>
                            </a>
                            <?php if (!empty($attachments_enabled)): ?>
                                <button type="button" onclick="return showTab('attachments', event)" class="patient-hero-tool-button">
                                    <i class="fas fa-paperclip"></i>
                                    <span><?= esc(lang('UI.patientUploadFiles')) ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?= view('patient/partials/whatsapp_identity', [
            'patient' => $patient,
            'patient_whatsapp' => $patient_whatsapp ?? null,
            'patient_whatsapp_optout' => $patient_whatsapp_optout ?? null,
            'embed_only' => true,
        ]) ?>

        <?php if ($medicalAlertCount > 0): ?>
            <section class="patient-alert-banner rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-rose-700"><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></h2>
                                <p class="mt-1 text-sm text-rose-900"><?= esc(lang('UI.patientProfileAlertSummary', ['count' => $medicalAlertCount])) ?></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (array_slice($activeMedicalAlerts, 0, 4) as $medicalAlertRow): ?>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-3 py-1 text-xs font-semibold text-rose-700">
                                        <i class="<?= esc($medicalAlertRow['icon']) ?> <?= esc($medicalAlertRow['icon_class']) ?>"></i>
                                        <span><?= esc($medicalAlertRow['label']) ?></span>
                                    </span>
                                <?php endforeach; ?>
                                <?php if ($medicalAlertCount > 4): ?>
                                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-white px-3 py-1 text-xs font-semibold text-rose-700">+<?= $medicalAlertCount - 4 ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="return showTab('details', event)" class="inline-flex items-center gap-2 self-start rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                        <i class="fas fa-arrow-down"></i>
                        <span><?= esc(lang('UI.patientReviewMedicalDetails')) ?></span>
                    </button>
                </div>
            </section>
        <?php endif; ?>

        <section class="grid gap-4 md:grid-cols-2">
            <div class="patient-rail-card patient-rail-card-compact <?= esc($clinicalSnapshotToneClass) ?>">
                <div class="patient-panel-header pb-3">
                    <div class="flex items-start gap-3">
                        <div class="patient-rail-card-icon patient-rail-card-icon-clinical">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div>
                            <h2 class="patient-panel-title"><?= esc(lang('UI.patientClinicalSnapshot')) ?></h2>
                            <p class="patient-panel-copy"><?= esc(lang('UI.patientClinicalSnapshotHelp')) ?></p>
                            <span class="patient-rail-card-state"><?= esc($clinicalSnapshotState) ?></span>
                        </div>
                    </div>
                </div>
                <div class="space-y-2.5">
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientLastVisit')) ?></span><strong><?= esc($lastVisitLabel) ?></strong></div>
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientNextAppointment')) ?></span><strong><?= esc($nextAppointmentLabel) ?></strong></div>
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientActiveTreatmentsSummary')) ?></span><strong><?= $activeTreatmentsCount ?></strong></div>
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientMedicalAlertsSummary')) ?></span><strong><?= $medicalAlertCount ?></strong></div>
                </div>
            </div>
            <div class="patient-rail-card patient-rail-card-compact <?= esc($financialSnapshotToneClass) ?>">
                <div class="patient-panel-header pb-3">
                    <div class="flex items-start gap-3">
                        <div class="patient-rail-card-icon patient-rail-card-icon-financial">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <h2 class="patient-panel-title"><?= esc(lang('UI.patientFinancialSnapshot')) ?></h2>
                            <p class="patient-panel-copy"><?= esc(lang('UI.patientFinancialSnapshotHelp')) ?></p>
                            <span class="patient-rail-card-state"><?= esc($financialSnapshotState) ?></span>
                        </div>
                    </div>
                </div>
                <div class="space-y-2.5">
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientOutstandingBalance')) ?></span><strong><?= formatCurrency($outstandingBalance) ?></strong></div>
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientTotalBilled')) ?></span><strong><?= formatCurrency($totalBilled) ?></strong></div>
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientTotalPaid')) ?></span><strong><?= formatCurrency($totalPaid) ?></strong></div>
                    <div class="patient-detail-row"><span><?= esc(lang('UI.patientInvoices')) ?></span><strong><?= count($finances) ?></strong></div>
                </div>
            </div>
        </section>

        <!-- Main Content Layout -->
        <div class="w-full">
            <!-- Patient History Tabs -->
            <div class="w-full">
                <div class="patient-shell">
                    <div class="patient-shell-header">
                        <div>
                            <p class="patient-shell-kicker"><?= esc(lang('UI.patientHistory')) ?></p>
                            <h2 class="patient-shell-title"><?= esc(lang('UI.patientProfileWorkspace')) ?></h2>
                            <p class="patient-shell-copy"><?= esc(lang('UI.patientProfileWorkspaceHelp')) ?></p>
                        </div>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <div class="patient-shell-nav">
                        <div class="flex flex-col gap-4">
                            <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                                <button type="button" onclick="return showTab('details', event)" 
                                        id="details-tab" 
                                        class="tab-button active whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                    <i class="fas fa-user mr-2"></i><?= esc(lang('UI.patientTabDetails')) ?>
                                </button>
                                <button type="button" onclick="return showTab('timeline', event)" 
                                        id="timeline-tab" 
                                        class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                    <i class="fas fa-history mr-2"></i><?= esc(lang('UI.patientTabTimeline')) ?>
                                </button>
                                <button type="button" onclick="return showTab('examinations', event)" 
                                        id="examinations-tab" 
                                        class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                    <i class="fas fa-stethoscope mr-2"></i><?= esc(lang('UI.patientTabExams')) ?> <span class="tab-count"><?= count($examinations) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('appointments', event)" 
                                        id="appointments-tab" 
                                        class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                    <i class="fas fa-calendar mr-2"></i><?= esc(lang('UI.patientTabAppts')) ?> <span class="tab-count"><?= count($appointments) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('treatments', event)" 
                                        id="treatments-tab" 
                                        class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                    <i class="fas fa-tooth mr-2"></i><?= esc(lang('UI.patientTreatments')) ?> <span class="tab-count"><?= count($treatments) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('prescriptions', event)" 
                                        id="prescriptions-tab" 
                                        class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                    <i class="fas fa-prescription mr-2"></i><?= esc(lang('UI.patientTabRx')) ?> <span class="tab-count"><?= count($prescriptions) ?></span>
                                </button>
                                <?php if (!empty($attachments_enabled)): ?>
                                    <button type="button" onclick="return showTab('attachments', event)" 
                                            id="attachments-tab" 
                                            class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                        <i class="fas fa-paperclip mr-2"></i><?= esc(lang('UI.patientTabAttachments')) ?> <span class="tab-count"><?= count($attachments ?? []) ?></span>
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($contracts_enabled)): ?>
                                    <button type="button" onclick="return showTab('contracts', event)" 
                                            id="contracts-tab" 
                                            class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                        <i class="fas fa-file-signature mr-2"></i><?= esc(lang('UI.patientTabContracts')) ?> <span class="tab-count"><?= count($contracts ?? []) ?></span>
                                    </button>
                                <?php endif; ?>
                                <button type="button" onclick="return showTab('finances', event)" 
                                        id="finances-tab" 
                                        class="tab-button whitespace-nowrap py-2.5 px-4 font-medium text-sm">
                                    <i class="fas fa-file-invoice mr-2"></i><?= esc(lang('UI.patientTabInvoice')) ?> <span class="tab-count"><?= count($finances) ?></span>
                                </button>
                            </nav>
                            
                            <!-- Search and Filter Controls -->
                            <div id="historyControls" class="patient-filter-strip hidden">
                                <div class="relative w-full lg:w-auto lg:min-w-[18rem]">
                                    <input type="text" 
                                           id="historySearch" 
                                           placeholder="<?= esc(lang('UI.patientSearchHistory')) ?>" 
                                           class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                                </div>
                                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                                    <input type="text" data-clinic-date="1"
                                           id="startDate" 
                                           class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <input type="text" data-clinic-date="1"
                                           id="endDate" 
                                           class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <button onclick="clearFilters()" 
                                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                        <i class="fas fa-undo"></i><span><?= esc(lang('UI.patientClear')) ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="px-6 py-6">
                        <!-- Patient Details Tab -->
                        <div id="details-content" class="tab-content">
                            <div class="space-y-6">
                                <!-- Personal Details -->
                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientPersonalInformation')) ?></h3>
                                    </div>
                                    <div class="px-6 py-4">
                                        <?php $creatorName = trim(($patient['creator_first_name'] ?? '') . ' ' . ($patient['creator_last_name'] ?? '')); ?>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientPatientId')) ?></label>
                                                <p class="text-sm text-gray-900 font-semibold"><?= $patient['patient_id'] ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientFullName')) ?></label>
                                                <p class="text-sm text-gray-900"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientDateOfBirth')) ?></label>
                                                <p class="text-sm text-gray-900"><?= formatDate($patient['date_of_birth']) ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientAge')) ?></label>
                                                <p class="text-sm text-gray-900">
                                                    <?php
                                                    $dob = new DateTime($patient['date_of_birth']);
                                                    $now = new DateTime();
                                                    $age = $now->diff($dob)->y;
                                                    echo $age . ' ' . lang('UI.patientYears');
                                                    ?>
                                                </p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientGender')) ?></label>
                                                <p class="text-sm text-gray-900"><?= ucfirst($patient['gender']) ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientStatus')) ?></label>
                                                <span class="status-badge <?= $patient['status'] == 'active' ? 'status-active' : 
                                                    ($patient['status'] == 'inactive' ? 'status-inactive' : 'status-pending') ?>">
                                                    <?= ucfirst($patient['status']) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientCreatedBy')) ?></label>
                                                <p class="text-sm text-gray-900"><?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : esc(lang('UI.patientUnknownDoctor')) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientContactDetails')) ?></h3>
                                    </div>
                                    <div class="px-6 py-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientPhone')) ?></label>
                                                <p class="text-sm text-gray-900"><?= $patient['phone'] ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientEmail')) ?></label>
                                                <p class="text-sm text-gray-900"><?= $patient['email'] ?: esc(lang('UI.patientNotProvided')) ?></p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc(lang('UI.patientCity')) ?></label>
                                                <p class="text-sm text-gray-900"><?= !empty($patient['city']) ? esc($patient['city']) : esc(lang('UI.patientNotProvided')) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Medical Information -->
                                <?php if ($hasStructuredMedicalAlerts || $patient['medical_history'] || $patient['allergies']): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                        <div class="px-6 py-4 border-b border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientMedicalInformation')) ?></h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            <div class="space-y-4">
                                                <?php if ($hasStructuredMedicalAlerts): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2"><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></label>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                            <?php foreach ($medicalAlerts as $medicalAlertRow): ?>
                                                                <?php if (empty($medicalAlertRow['enabled'])) continue; ?>
                                                                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                                    <div class="flex items-start justify-between gap-2">
                                                                        <span class="text-sm font-semibold text-amber-900 inline-flex items-start gap-2">
                                                                            <i class="<?= esc($medicalAlertRow['icon']) ?> <?= esc($medicalAlertRow['icon_class'] ?? 'text-amber-700') ?> mt-0.5 w-4 text-center"></i>
                                                                            <span><?= esc($medicalAlertRow['label']) ?></span>
                                                                        </span>
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900"><?= esc(lang('UI.patientYes')) ?></span>
                                                                    </div>
                                                                    <?php if (!empty($medicalAlertRow['details'])): ?>
                                                                        <p class="text-xs text-amber-800 mt-1"><?= nl2br(esc($medicalAlertRow['details'])) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($patient['medical_history']): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc($hasStructuredMedicalAlerts ? lang('UI.patientLegacyMedicalHistory') : lang('UI.patientMedicalHistory')) ?></label>
                                                        <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($patient['allergies']): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc($hasStructuredMedicalAlerts ? lang('UI.patientLegacyAllergies') : lang('UI.patientAllergies')) ?></label>
                                                        <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Notes -->
                                <?php if ($patient['notes']): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                        <div class="px-6 py-4 border-b border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientNotesTitle')) ?></h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($patient['notes'])) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Timeline Tab -->
                        <div id="timeline-content" class="tab-content hidden">
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <?php 
                                    // Combine all records into a timeline
                                    $timeline = [];
                                    
                                    // Add examinations
                                    foreach ($examinations as $exam) {
                                        $doctorName = trim(($exam['doctor_first_name'] ?? '') . ' ' . ($exam['doctor_last_name'] ?? ''));
                                        $timeline[] = [
                                            'type' => 'examination',
                                            'date' => $exam['examination_date'],
                                            'title' => lang('UI.patientTimelineExamTitle', ['type' => ($exam['examination_type'] ?? lang('UI.patientGeneral'))]),
                                            'description' => $exam['chief_complaint'] ?? lang('UI.patientNoComplaintRecorded'),
                                            'status' => $exam['status'],
                                            'id' => $exam['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-stethoscope',
                                            'color' => 'blue'
                                        ];
                                    }
                                    
                                    // Add appointments
                                    foreach ($appointments as $appt) {
                                        $doctorName = trim(($appt['doctor_first_name'] ?? '') . ' ' . ($appt['doctor_last_name'] ?? ''));
                                        $timeline[] = [
                                            'type' => 'appointment',
                                            'date' => $appt['appointment_date'],
                                            'title' => lang('UI.patientTimelineAppointmentTitle', ['type' => ($appt['appointment_type'] ?? lang('UI.patientGeneral'))]),
                                            'description' => $appt['notes'] ?? lang('UI.patientNoNotes'),
                                            'status' => $appt['status'],
                                            'id' => $appt['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-calendar',
                                            'color' => 'green'
                                        ];
                                    }
                                    
                                    // Add treatments
                                    foreach ($treatments as $treatment) {
                                        $doctorName = trim(($treatment['doctor_first_name'] ?? '') . ' ' . ($treatment['doctor_last_name'] ?? ''));
                                        $timeline[] = [
                                            'type' => 'treatment',
                                            'date' => $treatment['start_date'],
                                            'title' => lang('UI.patientTimelineTreatmentTitle', ['name' => ($treatment['treatment_name'] ?? lang('UI.patientGeneral'))]),
                                            'description' => $treatment['treatment_description'] ?? lang('UI.patientNoDescription'),
                                            'status' => $treatment['status'],
                                            'id' => $treatment['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-tooth',
                                            'color' => 'purple'
                                        ];
                                    }
                                    
                                    // Add prescriptions
                                    foreach ($prescriptions as $prescription) {
                                        // Parse medication JSON data
                                        $medicationName = lang('UI.patientUnknown');
                                        try {
                                            $medicines = json_decode($prescription['medication_name'], true);
                                            if (is_array($medicines) && !empty($medicines)) {
                                                $medicineNames = [];
                                                foreach ($medicines as $medicine) {
                                                    if (isset($medicine['name'])) {
                                                        $medicineNames[] = $medicine['name'];
                                                    }
                                                }
                                                $medicationName = implode(', ', $medicineNames);
                                            } else {
                                                $medicationName = $prescription['medication_name'] ?? lang('UI.patientUnknown');
                                            }
                                        } catch (Exception $e) {
                                            $medicationName = $prescription['medication_name'] ?? lang('UI.patientUnknown');
                                        }
                                        
                                        $timeline[] = [
                                            'type' => 'prescription',
                                            'date' => $prescription['prescribed_date'],
                                            'title' => lang('UI.patientTimelinePrescriptionTitle', ['name' => $medicationName]),
                                            'description' => $prescription['instructions'] ?? lang('UI.patientNoInstructions'),
                                            'status' => $prescription['status'],
                                            'id' => $prescription['id'],
                                            'icon' => 'fas fa-prescription',
                                            'color' => 'orange'
                                        ];
                                    }
                                    
                                    // Add financial transactions
                                    foreach ($finances as $finance) {
                                        $timeline[] = [
                                            'type' => 'finance',
                                            'date' => $finance['created_at'],
                                            'title' => lang('UI.patientTimelinePaymentTitle', ['type' => ($finance['transaction_type'] ?? lang('UI.patientUnknown'))]),
                                            'description' => $finance['description'] ?? lang('UI.patientNoDescription'),
                                            'status' => $finance['payment_status'],
                                            'id' => $finance['id'],
                                            'icon' => 'fas fa-dollar-sign',
                                            'color' => 'green'
                                        ];
                                    }
                                    
                                    // Sort by date
                                    usort($timeline, function($a, $b) {
                                        return strtotime($b['date']) - strtotime($a['date']);
                                    });
                                    
                                    foreach ($timeline as $index => $item): 
                                    ?>
                                    <li>
                                        <div class="relative pb-8">
                                            <?php if ($index !== count($timeline) - 1): ?>
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-<?= $item['color'] ?>-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="<?= $item['icon'] ?> text-white text-xs"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            <span class="font-medium text-gray-900"><?= $item['title'] ?></span>
                                                            <span class="text-gray-500"><?= esc(lang('UI.patientTimelineOn')) ?> <?= formatDate($item['date']) ?></span>
                                                        </p>
                                                        <p class="text-sm text-gray-500"><?= $item['description'] ?></p>
                                                        <?php if (!empty($item['doctor'])): ?>
                                                            <p class="text-xs text-gray-400 mt-1"><?= esc(lang('UI.patientCreatedBy')) ?>: <?= esc($item['doctor']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $item['color'] ?>-100 text-<?= $item['color'] ?>-800">
                                                            <?= ucfirst($item['status']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($timeline)): ?>
                                        <li>
                                            <div class="text-center py-8">
                                                <i class="fas fa-history text-gray-400 text-4xl mb-4"></i>
                                                <p class="text-gray-500"><?= esc(lang('UI.patientNoHistoryRecords')) ?></p>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <?php if (!empty($attachments_enabled)): ?>
                            <!-- Attachments Tab -->
                            <div id="attachments-content" class="tab-content hidden">
                                <?php
                                    $usageBytes = (int) ($attachments_storage_usage_bytes ?? 0);
                                    $limitGb = (int) ($attachments_storage_limit_gb ?? 0);
                                    $limitBytes = $limitGb > 0 ? $limitGb * 1024 * 1024 * 1024 : 0;
                                    $usagePercent = $limitBytes > 0 ? round(($usageBytes / $limitBytes) * 100) : 0;
                                    $maxFileMb = $attachments_max_file_mb ?? 5;
                                ?>

                                <?php if (session('success')): ?>
                                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 text-sm">
                                        <?= esc(session('success')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (session('error')): ?>
                                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
                                        <?= esc(session('error')) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($limitBytes > 0 && $usagePercent >= 80): ?>
                                    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800 text-sm">
                                        <strong><?= esc(lang('UI.patientStorageWarning')) ?></strong> <?= esc(lang('UI.patientStorageWarningText', ['percent' => $usagePercent])) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientAttachmentsTitle')) ?></h3>
                                            <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientAttachmentsSubtitle', ['max' => ($maxFileMb === null ? lang('UI.patientUnlimited') : ((int) $maxFileMb . 'MB'))])) ?></p>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4">
                                        <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/upload') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                                            <?= csrf_field() ?>
                                            <div class="space-y-4">
                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('UI.patientUploadFiles')) ?></label>
                                                        <input type="file" name="attachment[]" id="attachmentInput" required multiple
                                                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip"
                                                               class="hidden">
                                                        <div id="attachmentDropzone" tabindex="0" role="button"
                                                             class="rounded-lg border border-gray-200 bg-white px-4 py-3 transition-all duration-200 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400/30">
                                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                                <div class="min-w-0">
                                                                    <p class="text-sm text-gray-700"><?= esc(lang('UI.patientDragDropFiles')) ?></p>
                                                                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('UI.patientAllowedFileTypes')) ?></p>
                                                                </div>
                                                                <label for="attachmentInput" class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100">
                                                                    <i class="fas fa-folder-open"></i>
                                                                    <span><?= esc(lang('UI.patientChooseFiles')) ?></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="attachmentFileList" class="mt-3 space-y-2 text-sm text-gray-600"></div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('UI.patientAttachmentCategory')) ?></label>
                                                        <?php $selectedAttachmentCategory = old('category'); ?>
                                                        <select
                                                            name="category"
                                                            id="attachmentCategory"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                            data-searchable-select
                                                            data-minimum-input-length="0"
                                                            data-placeholder="<?= esc(lang('UI.patientAttachmentCategoryPlaceholder')) ?>"
                                                            data-allow-clear="true"
                                                        >
                                                            <option value=""><?= esc(lang('UI.patientAttachmentCategoryPlaceholder')) ?></option>
                                                            <?php foreach (($attachment_category_options ?? []) as $groupLabel => $groupOptions): ?>
                                                                <optgroup label="<?= esc($groupLabel) ?>">
                                                                    <?php foreach ($groupOptions as $optionLabel): ?>
                                                                        <option value="<?= esc($optionLabel) ?>" <?= $selectedAttachmentCategory === $optionLabel ? 'selected' : '' ?>>
                                                                            <?= esc($optionLabel) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </optgroup>
                                                            <?php endforeach; ?>
                                                            <?php if (!empty($selectedAttachmentCategory)): ?>
                                                                <?php
                                                                $knownCategory = false;
                                                                foreach (($attachment_category_options ?? []) as $groupOptions) {
                                                                    if (in_array($selectedAttachmentCategory, $groupOptions, true)) {
                                                                        $knownCategory = true;
                                                                        break;
                                                                    }
                                                                }
                                                                ?>
                                                                <?php if (!$knownCategory): ?>
                                                                    <option value="<?= esc($selectedAttachmentCategory) ?>" selected><?= esc($selectedAttachmentCategory) ?></option>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('UI.patientNotes')) ?></label>
                                                    <textarea name="notes" rows="4" placeholder="<?= esc(lang('UI.patientAttachmentNotesPlaceholder')) ?>"
                                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end">
                                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                                    <i class="fas fa-upload mr-2"></i><?= esc(lang('UI.patientUpload')) ?>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-800"><?= esc(lang('UI.patientAttachmentHistory')) ?></h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientFile')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientCategory')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientNotes')) ?></th>
                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php if (!empty($attachments)): ?>
                                                    <?php foreach ($attachments as $attachment): ?>
                                                        <tr data-date="<?= !empty($attachment['created_at']) ? esc(date('Y-m-d', strtotime($attachment['created_at']))) : '' ?>">
                                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                                <?= !empty($attachment['created_at']) ? formatDateTime($attachment['created_at']) : '--' ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                                <div class="font-medium"><?= esc($attachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></div>
                                                                <div class="text-xs text-gray-500"><?= number_format(($attachment['file_size'] ?? 0) / 1024, 1) ?> KB</div>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                                <?= esc($attachment['category'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                                <?= esc($attachment['notes'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-right text-sm">
                                                                <a href="<?= base_url('file/download/' . $attachment['id']) ?>" 
                                                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 mr-3">
                                                                    <i class="fas fa-download mr-1"></i><?= esc(lang('UI.patientDownload')) ?>
                                                                </a>
                                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/' . $attachment['id'] . '/delete') ?>" method="post" class="inline">
                                                                    <?= csrf_field() ?>
                                                                    <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-800" onclick="return confirm('<?= esc(lang('UI.patientDeleteAttachmentConfirm'), 'js') ?>');">
                                                                        <i class="fas fa-trash mr-1"></i><?= esc(lang('UI.patientDelete')) ?>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500"><?= esc(lang('UI.patientNoAttachmentsYet')) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($contracts_enabled)): ?>
                            <!-- Contracts Tab -->
                            <div id="contracts-content" class="tab-content hidden">
                                <?php if (session('success')): ?>
                                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 text-sm">
                                        <?= esc(session('success')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (session('error')): ?>
                                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
                                        <?= esc(session('error')) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientContractsTitle')) ?></h3>
                                            <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientContractsSubtitle')) ?></p>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientContractsHint')) ?></p>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-800"><?= esc(lang('UI.patientContractHistory')) ?></h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientExam')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientTotal')) ?></th>
                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php if (!empty($contracts)): ?>
                                                    <?php foreach ($contracts as $contract): ?>
                                                        <tr data-date="<?= !empty($contract['created_at']) ? esc(date('Y-m-d', strtotime($contract['created_at']))) : '' ?>">
                                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                                <?= !empty($contract['created_at']) ? formatDateTime($contract['created_at']) : '--' ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= ($contract['status'] ?? 'draft') === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                                                    <?= esc(ucfirst($contract['status'] ?? 'draft')) ?>
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                                <?= !empty($contract['examination_id']) ? esc($contract['examination_id']) : '--' ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                                <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                                                            </td>
                                                    <td class="px-6 py-4 text-right text-sm">
                                                        <a href="<?= base_url('contracts/' . $contract['id']) ?>" 
                                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 mr-3">
                                                            <i class="fas fa-eye mr-1"></i><?= esc(lang('UI.patientView')) ?>
                                                        </a>
                                                        <?php if (($contract['status'] ?? 'draft') === 'draft'): ?>
                                                            <a href="<?= base_url('contracts/' . $contract['id'] . '/edit') ?>" 
                                                               class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mr-3">
                                                                <i class="fas fa-edit mr-1"></i><?= esc(lang('UI.patientEdit')) ?>
                                                            </a>
                                                            <form action="<?= base_url('contracts/' . $contract['id'] . '/delete') ?>" method="post" class="inline">
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-800 mr-3" onclick="return confirm('<?= esc(lang('UI.patientDeleteContractConfirm'), 'js') ?>');">
                                                                    <i class="fas fa-trash mr-1"></i><?= esc(lang('UI.patientDelete')) ?>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <a href="<?= base_url('contracts/' . $contract['id'] . '/print') ?>" 
                                                           class="inline-flex items-center text-gray-600 hover:text-gray-800">
                                                            <i class="fas fa-print mr-1"></i><?= esc(lang('UI.patientPrint')) ?>
                                                        </a>
                                                    </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500"><?= esc(lang('UI.patientNoContractsYet')) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Examinations Tab -->
                        <div id="examinations-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientExaminations')) ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientExaminationsSubtitle')) ?></p>
                                    </div>
                                    <?php if (has_permission('examinations', 'create')): ?>
                                        <a href="<?= base_url('examination/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientCreateExamination')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($examinations)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientType')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientChiefComplaint')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($examinations as $examination): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($examination['examination_date']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($examination['examination_type'] ?? lang('UI.patientGeneral')) ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <?= $examination['chief_complaint'] ? substr($examination['chief_complaint'], 0, 50) . '...' : lang('UI.patientNoComplaint') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $examination['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                            <?= ucfirst($examination['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('examination/' . $examination['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900"><?= esc(lang('UI.patientView')) ?></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-stethoscope text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500"><?= esc(lang('UI.patientNoExaminations')) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Appointments Tab -->
                        <div id="appointments-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientAppointments')) ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientAppointmentsSubtitle')) ?></p>
                                    </div>
                                    <?php if (has_permission('appointments', 'create')): ?>
                                        <a href="<?= base_url('appointment/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientScheduleAppointment')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($appointments)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDateTime')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientType')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDuration')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($appointments as $appointment): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($appointment['appointment_date']) ?> <?= esc(lang('UI.patientAt')) ?> <?= formatTime($appointment['appointment_time']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($appointment['appointment_type'] ?? lang('UI.patientGeneral')) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= $appointment['duration'] ?? lang('UI.patientNa') ?> <?= esc(lang('UI.patientMinutes')) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'scheduled' => 'bg-blue-100 text-blue-800',
                                                            'confirmed' => 'bg-green-100 text-green-800',
                                                            'completed' => 'bg-gray-100 text-gray-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                            'no_show' => 'bg-yellow-100 text-yellow-800'
                                                        ];
                                                        $colorClass = $statusColors[$appointment['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($appointment['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('appointment/' . $appointment['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900"><?= esc(lang('UI.patientView')) ?></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-calendar text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500"><?= esc(lang('UI.patientNoAppointments')) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Treatments Tab -->
                        <div id="treatments-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientTreatments')) ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientTreatmentsSubtitle')) ?></p>
                                    </div>
                                    <?php if (has_permission('treatments', 'create')): ?>
                                        <a href="<?= base_url('treatment/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientAddTreatment')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($treatments)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientTreatment')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientType')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientTooth')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientCost')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($treatments as $treatment): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= $treatment['treatment_name'] ?? lang('UI.patientNa') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($treatment['treatment_type'] ?? lang('UI.patientGeneral')) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= $treatment['tooth_number'] ?? lang('UI.patientNa') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        $<?= number_format($treatment['cost'] ?? 0, 2) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'active' => 'bg-blue-100 text-blue-800',
                                                            'completed' => 'bg-green-100 text-green-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                            'on_hold' => 'bg-yellow-100 text-yellow-800'
                                                        ];
                                                        $colorClass = $statusColors[$treatment['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($treatment['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('treatment/' . $treatment['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900"><?= esc(lang('UI.patientView')) ?></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-tooth text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500"><?= esc(lang('UI.patientNoTreatments')) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Prescriptions Tab -->
                        <div id="prescriptions-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientPrescriptions')) ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientPrescriptionsSubtitle')) ?></p>
                                    </div>
                                    <?php if (has_permission('prescriptions', 'create')): ?>
                                        <a href="<?= base_url('prescription/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientCreatePrescription')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($prescriptions)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientMedication')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDosage')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientFrequency')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientPrescribedDate')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($prescriptions as $prescription): ?>
                                                <?php
                                                // Parse medication JSON data
                                                $medicines = [];
                                                $medicationName = lang('UI.patientNa');
                                                $dosage = lang('UI.patientNa');
                                                $frequency = lang('UI.patientNa');
                                                
                                                try {
                                                    $medicinesData = json_decode($prescription['medication_name'], true);
                                                    if (is_array($medicinesData) && !empty($medicinesData)) {
                                                        $medicines = $medicinesData;
                                                        $medicineNames = [];
                                                        $dosages = [];
                                                        $frequencies = [];
                                                        
                                                        foreach ($medicinesData as $medicine) {
                                                            if (isset($medicine['name'])) {
                                                                $medicineNames[] = $medicine['name'];
                                                            }
                                                            if (isset($medicine['dosage'])) {
                                                                $dosages[] = $medicine['dosage'];
                                                            }
                                                            if (isset($medicine['frequency'])) {
                                                                $frequencies[] = $medicine['frequency'];
                                                            }
                                                        }
                                                        
                                                        $medicationName = implode(', ', $medicineNames);
                                                        $dosage = implode(', ', $dosages);
                                                        $frequency = implode(', ', $frequencies);
                                                    } else {
                                                        $medicationName = $prescription['medication_name'] ?? lang('UI.patientNa');
                                                        $dosage = $prescription['dosage'] ?? lang('UI.patientNa');
                                                        $frequency = $prescription['frequency'] ?? lang('UI.patientNa');
                                                    }
                                                } catch (Exception $e) {
                                                    $medicationName = $prescription['medication_name'] ?? lang('UI.patientNa');
                                                    $dosage = $prescription['dosage'] ?? lang('UI.patientNa');
                                                    $frequency = $prescription['frequency'] ?? lang('UI.patientNa');
                                                }
                                                ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <div class="flex items-center space-x-2">
                                                            <i class="fas fa-pills text-teal-600 text-xs"></i>
                                                            <span><?= htmlspecialchars($medicationName) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <?= htmlspecialchars($dosage) ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <?= htmlspecialchars($frequency) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($prescription['prescribed_date']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'active' => 'bg-green-100 text-green-800',
                                                            'expired' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                                            'pending' => 'bg-yellow-100 text-yellow-800'
                                                        ];
                                                        $colorClass = $statusColors[$prescription['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($prescription['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('prescription/' . $prescription['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900"><?= esc(lang('UI.patientView')) ?></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-prescription text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500"><?= esc(lang('UI.patientNoPrescriptions')) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Invoice Tab -->
                        <div id="finances-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientInvoices')) ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientInvoicesSubtitle')) ?></p>
                                    </div>
                                    <?php if (has_permission('finance', 'create')): ?>
                                        <a href="<?= base_url('finance/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientCreateInvoice')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($finances)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientType')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientPaymentMethod')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientTotal')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($finances as $finance): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($finance['created_at']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($finance['transaction_type'] ?? lang('UI.patientUnknown')) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($finance['payment_method'] ?? lang('UI.patientNa')) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?php
                                                            $totalAmount = $finance['total_amount'] ?? ($finance['amount'] - ($finance['discount_amount'] ?? 0) + ($finance['tax_amount'] ?? 0));
                                                        ?>
                                                        <?= formatCurrency($totalAmount) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'paid' => 'bg-green-100 text-green-800',
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'partial' => 'bg-blue-100 text-blue-800',
                                                            'overdue' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-800'
                                                        ];
                                                        $colorClass = $statusColors[$finance['payment_status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($finance['payment_status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('finance/' . $finance['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900"><?= esc(lang('UI.patientView')) ?></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-file-invoice text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500"><?= esc(lang('UI.patientNoInvoices')) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tooth Modal -->
                        <div id="toothModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 transition-opacity duration-300">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
                                    <!-- Enhanced Modal Header -->
                                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 rounded-t-2xl relative overflow-hidden">
                                        <div class="absolute inset-0 bg-black/10"></div>
                                        <div class="relative z-10">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                                        <i class="fas fa-tooth text-white text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-xl font-bold">
                                                            <?= esc(lang('UI.patientToothConditionHeadingPrefix')) ?>
                                                            <span id="modalToothNumber" class="text-yellow-300"></span>
                                                            <?= esc(lang('UI.patientToothConditionHeadingSuffix')) ?>
                                                        </h3>
                                                        <p class="text-blue-100 text-sm"><?= esc(lang('UI.patientToothConditionSubtitle')) ?></p>
                                                    </div>
                                                </div>
                                                <button onclick="closeToothModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-times text-white"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Body -->
                                    <div class="p-6">
                                        <form id="toothForm" onsubmit="updateTooth(event)">
                                            <input type="hidden" id="toothNumber" name="tooth_number">
                                            <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                            
                                            <div class="space-y-6">
                                                <!-- Condition Type -->
                                                <div>
                                                    <label for="condition_type" class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('UI.patientConditionType')) ?></label>
                                                    <select id="condition_type" name="condition_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="healthy"><?= esc(lang('UI.patientConditionHealthy')) ?></option>
                                                        <option value="cavity"><?= esc(lang('UI.patientConditionCavity')) ?></option>
                                                        <option value="filling"><?= esc(lang('UI.patientConditionFilling')) ?></option>
                                                        <option value="crown"><?= esc(lang('UI.patientConditionCrown')) ?></option>
                                                        <option value="root_canal"><?= esc(lang('UI.patientConditionRootCanal')) ?></option>
                                                        <option value="extracted"><?= esc(lang('UI.patientConditionExtracted')) ?></option>
                                                        <option value="implant"><?= esc(lang('UI.patientConditionImplant')) ?></option>
                                                        <option value="bridge"><?= esc(lang('UI.patientConditionBridge')) ?></option>
                                                        <option value="partial_denture"><?= esc(lang('UI.patientConditionPartialDenture')) ?></option>
                                                        <option value="full_denture"><?= esc(lang('UI.patientConditionFullDenture')) ?></option>
                                                        <option value="other"><?= esc(lang('UI.patientConditionOther')) ?></option>
                                                    </select>
                                                </div>

                                                <!-- Condition Description -->
                                                <div>
                                                    <label for="condition_description" class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('UI.patientConditionDescription')) ?></label>
                                                    <textarea id="condition_description" name="condition_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?= esc(lang('UI.patientConditionDescriptionPlaceholder')) ?>"></textarea>
                                                </div>

                                                <!-- Treatment Notes -->
                                                <div>
                                                    <label for="treatment_notes" class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('UI.patientTreatmentNotes')) ?></label>
                                                    <textarea id="treatment_notes" name="treatment_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?= esc(lang('UI.patientTreatmentNotesPlaceholder')) ?>"></textarea>
                                                </div>

                                                <!-- Treatment Status -->
                                                <div>
                                                    <label for="treatment_status" class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('UI.patientTreatmentStatus')) ?></label>
                                                    <select id="treatment_status" name="treatment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="pending"><?= esc(lang('UI.patientTreatmentStatusPending')) ?></option>
                                                        <option value="in_progress"><?= esc(lang('UI.patientTreatmentStatusInProgress')) ?></option>
                                                        <option value="completed"><?= esc(lang('UI.patientTreatmentStatusCompleted')) ?></option>
                                                        <option value="needs_attention"><?= esc(lang('UI.patientTreatmentStatusNeedsAttention')) ?></option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Modal Footer -->
                                            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                                                <button type="button" onclick="resetTooth()" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors duration-200">
                                                    <i class="fas fa-undo mr-2"></i><?= esc(lang('UI.patientResetToHealthy')) ?>
                                                </button>
                                                <div class="flex space-x-3">
                                                    <button type="button" onclick="closeToothModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                                        <?= esc(lang('UI.cancel')) ?>
                                                    </button>
                                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                                        <i class="fas fa-save mr-2"></i><?= esc(lang('UI.settingsSaveChanges')) ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>                    </div>
                </div> 
           </div>


    </div>
</div>


<style>
    .patient-view {
        position: relative;
    }

    .patient-view::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(900px 520px at 8% -12%, rgba(37, 99, 235, 0.08), transparent 60%),
                    radial-gradient(700px 420px at 92% 0%, rgba(14, 165, 233, 0.08), transparent 55%);
        pointer-events: none;
        z-index: 0;
    }

    .patient-view > [class*="space-y-"] {
        position: relative;
        z-index: 1;
    }

    .patient-hero-card {
        position: relative;
        --hero-angle: 135deg;
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        --hero-orb-primary: rgba(255, 255, 255, 0.18);
        --hero-orb-secondary: rgba(125, 211, 252, 0.2);
        background: linear-gradient(var(--hero-angle), var(--hero-start) 0%, var(--hero-mid) 58%, var(--hero-end) 100%);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, 0.75);
    }

    .patient-hero-card-male {
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        --hero-orb-primary: rgba(255, 255, 255, 0.18);
        --hero-orb-secondary: rgba(125, 211, 252, 0.2);
    }

    .patient-hero-card-female {
        --hero-start: #8a245a;
        --hero-mid: #c03579;
        --hero-end: #7c3aed;
        --hero-orb-primary: rgba(255, 255, 255, 0.16);
        --hero-orb-secondary: rgba(251, 207, 232, 0.24);
    }

    .patient-hero-card-neutral {
        --hero-start: #334155;
        --hero-mid: #2563eb;
        --hero-end: #0f766e;
        --hero-orb-primary: rgba(255, 255, 255, 0.16);
        --hero-orb-secondary: rgba(153, 246, 228, 0.2);
    }

    [dir="rtl"] .patient-hero-card {
        --hero-angle: 225deg;
    }

    .patient-hero-overlay {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 18%, var(--hero-orb-primary), transparent 30%),
            radial-gradient(circle at 88% 20%, var(--hero-orb-secondary), transparent 26%),
            linear-gradient(120deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
    }

    [dir="rtl"] .patient-hero-overlay {
        background:
            radial-gradient(circle at 88% 18%, var(--hero-orb-primary), transparent 30%),
            radial-gradient(circle at 12% 20%, var(--hero-orb-secondary), transparent 26%),
            linear-gradient(240deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
    }

    .patient-avatar-badge {
        display: inline-flex;
        width: 5.5rem;
        height: 5.5rem;
        align-items: center;
        justify-content: center;
        border-radius: 1.75rem;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(191, 219, 254, 0.18));
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 22px 40px -24px rgba(15, 23, 42, 0.72);
    }

    .patient-avatar-badge i {
        font-size: 2.55rem;
        line-height: 1;
        display: inline-block;
    }

    .patient-avatar-badge-male {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(191, 219, 254, 0.18));
    }

    .patient-avatar-badge-female {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(244, 114, 182, 0.18));
    }

    .patient-avatar-badge-neutral {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(94, 234, 212, 0.16));
    }

    .patient-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 3.25rem;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.12);
        padding: 0.8rem 1.1rem;
        backdrop-filter: blur(12px);
        font-size: 0.94rem;
        font-weight: 700;
    }

    .patient-hero-chip-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.85rem;
    }

    @media (min-width: 640px) {
        .patient-hero-chip-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1100px) {
        .patient-hero-chip-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .patient-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 2.65rem;
        width: 100%;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        padding: 0.52rem 0.82rem;
        color: #eff6ff;
        backdrop-filter: blur(12px);
        text-align: start;
        overflow: hidden;
    }

    .patient-hero-chip-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.12);
        flex-shrink: 0;
        font-size: 0.78rem;
    }

    .patient-hero-chip-text {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        min-width: 0;
    }

    .patient-hero-chip-label {
        display: inline;
        flex-shrink: 0;
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.2;
        color: rgba(219, 234, 254, 0.84);
    }

    .patient-hero-chip-value {
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.76rem;
        font-weight: 500;
        line-height: 1.25;
        color: #ffffff;
    }

    .patient-hero-chip-ltr .patient-hero-chip-value {
        direction: ltr;
    }

    .patient-hero-chip-action {
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .patient-hero-chip-action:hover {
        transform: translateY(-1px);
    }

    .patient-meta-chip-action {
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .patient-meta-chip-action:hover {
        transform: translateY(-1px);
        background: rgba(255, 255, 255, 0.16);
        border-color: rgba(255, 255, 255, 0.26);
    }

    .patient-meta-chip-whatsapp-ready {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.26), rgba(22, 163, 74, 0.22));
        border-color: rgba(187, 247, 208, 0.42);
        color: #dcfce7;
    }

    .patient-meta-chip-whatsapp-ready .patient-hero-chip-icon,
    .patient-meta-chip-whatsapp-ready .patient-hero-chip-value,
    .patient-meta-chip-whatsapp-ready .patient-hero-chip-label {
        color: #86efac;
    }

    .patient-meta-chip-whatsapp-blocked {
        background: rgba(244, 63, 94, 0.16);
        border-color: rgba(254, 205, 211, 0.35);
    }

    .patient-meta-chip-whatsapp-idle {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.16);
    }

    .patient-hero-status {
        color: #eff6ff;
        backdrop-filter: blur(12px);
    }

    .patient-hero-status-active {
        background: rgba(16, 185, 129, 0.18);
        border-color: rgba(167, 243, 208, 0.4);
    }

    .patient-hero-status-inactive {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .patient-hero-status-pending {
        background: rgba(245, 158, 11, 0.18);
        border-color: rgba(253, 230, 138, 0.38);
    }

    .patient-hero-clinical-button,
    .patient-hero-tool-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        min-height: 2.4rem;
        border-radius: 1rem;
        padding: 0.48rem 0.72rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #eff6ff;
        backdrop-filter: blur(12px);
        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        text-align: center;
    }

    .patient-hero-clinical-button {
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.14);
        box-shadow: 0 10px 24px -20px rgba(15, 23, 42, 0.45);
    }

    .patient-hero-tool-button {
        border: 1px dashed rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.09);
        color: rgba(239, 246, 255, 0.92);
    }

    .patient-hero-clinical-button:hover,
    .patient-hero-tool-button:hover {
        transform: translateY(-1px);
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.28);
    }

    .patient-header-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.58rem;
        min-height: 2.65rem;
        border-radius: 0.95rem;
        padding: 0.58rem 0.78rem;
        font-size: 0.76rem;
        font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        text-align: center;
    }

    .patient-header-action:hover {
        transform: translateY(-1px);
    }

    .patient-header-action-soft {
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        color: #eff6ff;
    }

    .patient-header-action-primary {
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        color: #eff6ff;
        box-shadow: none;
    }

    .patient-header-action-emerald {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        box-shadow: 0 12px 22px -20px rgba(5, 150, 105, 0.85);
    }

    .patient-header-action-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #ffffff;
        box-shadow: 0 12px 22px -20px rgba(217, 119, 6, 0.85);
    }

    .patient-kpi-card,
    .patient-panel,
    .patient-rail-card,
    .patient-summary-card,
    .patient-shell {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid #d8e1ee;
        box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.35);
    }

    .patient-kpi-card {
        border-radius: 1.35rem;
        padding: 1.25rem;
        position: relative;
        overflow: hidden;
    }

    .patient-kpi-card::after {
        content: "";
        position: absolute;
        inset-inline-end: -1.4rem;
        top: -1.4rem;
        width: 5rem;
        height: 5rem;
        border-radius: 9999px;
        opacity: 0.35;
    }

    .patient-kpi-card-indigo::after { background: #bfdbfe; }
    .patient-kpi-card-emerald::after { background: #bbf7d0; }
    .patient-kpi-card-amber::after { background: #fde68a; }
    .patient-kpi-card-rose::after { background: #fecdd3; }

    .patient-kpi-label {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: #64748b;
    }

    .patient-kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.2;
        color: #0f172a;
    }

    .patient-kpi-subvalue {
        margin-top: 0.35rem;
        font-size: 0.82rem;
        color: #64748b;
    }

    .patient-kpi-icon {
        display: inline-flex;
        height: 2.75rem;
        width: 2.75rem;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        font-size: 1rem;
    }

    .patient-panel,
    .patient-rail-card,
    .patient-summary-card,
    .patient-shell {
        border-radius: 1.5rem;
    }

    .patient-panel,
    .patient-rail-card {
        padding: 1.35rem;
    }

    .patient-rail-card-compact {
        padding: 1rem 1rem 0.95rem;
        position: relative;
        overflow: hidden;
    }

    .patient-rail-card-compact::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        opacity: 0.9;
    }

    .patient-rail-card-clinical-calm::before {
        background: linear-gradient(180deg, rgba(236, 253, 245, 0.92), rgba(255, 255, 255, 0));
    }

    .patient-rail-card-clinical-alert::before {
        background: linear-gradient(180deg, rgba(255, 241, 242, 0.95), rgba(255, 255, 255, 0));
    }

    .patient-rail-card-financial-clear::before {
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.95), rgba(255, 255, 255, 0));
    }

    .patient-rail-card-financial-due::before {
        background: linear-gradient(180deg, rgba(255, 247, 237, 0.95), rgba(255, 255, 255, 0));
    }

    .patient-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1rem;
    }

    .patient-panel-title,
    .patient-shell-title {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: #0f172a;
    }

    .patient-panel-copy,
    .patient-shell-copy {
        margin-top: 0.35rem;
        font-size: 0.82rem;
        line-height: 1.5;
        color: #64748b;
    }

    .patient-rail-card-compact .patient-panel-copy {
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .patient-rail-card-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 10px 22px -18px rgba(15, 23, 42, 0.35);
    }

    .patient-rail-card-icon-clinical {
        background: linear-gradient(135deg, #10b981, #0f766e);
        color: #ecfdf5;
    }

    .patient-rail-card-icon-financial {
        background: linear-gradient(135deg, #f59e0b, #ea580c);
        color: #fff7ed;
    }

    .patient-rail-card-state {
        display: inline-flex;
        margin-top: 0.55rem;
        align-items: center;
        border-radius: 9999px;
        border: 1px solid #dbe7f5;
        background: rgba(255, 255, 255, 0.8);
        padding: 0.28rem 0.6rem;
        font-size: 0.68rem;
        font-weight: 600;
        color: #475569;
    }

    .patient-action-card {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        border-radius: 1.1rem;
        border: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        padding: 1rem;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .patient-action-card:hover {
        transform: translateY(-1px);
        border-color: #bfdbfe;
        box-shadow: 0 16px 30px -26px rgba(37, 99, 235, 0.45);
    }

    .patient-action-card h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    .patient-action-card p {
        margin-top: 0.25rem;
        font-size: 0.78rem;
        line-height: 1.45;
        color: #64748b;
    }

    .patient-action-icon {
        display: inline-flex;
        height: 2.7rem;
        width: 2.7rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 0.95rem;
    }

    .patient-detail-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid #e5edf6;
        padding: 0.72rem 0.85rem;
        font-size: 0.84rem;
        color: #475569;
    }

    .patient-detail-row strong {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        background: #f8fafc;
        padding: 0.28rem 0.58rem;
        color: #0f172a;
        font-weight: 600;
        text-align: end;
    }

    .patient-summary-card {
        padding: 1.1rem 1.15rem;
    }

    .patient-summary-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #64748b;
    }

    .patient-summary-value {
        margin-top: 0.45rem;
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
    }

    .patient-summary-icon {
        display: inline-flex;
        height: 2.7rem;
        width: 2.7rem;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
    }

    .patient-summary-icon-blue { background: #dbeafe; color: #1d4ed8; }
    .patient-summary-icon-emerald { background: #d1fae5; color: #047857; }
    .patient-summary-icon-violet { background: #ede9fe; color: #6d28d9; }
    .patient-summary-icon-amber { background: #fef3c7; color: #b45309; }
    .patient-summary-icon-rose { background: #ffe4e6; color: #be123c; }
    .patient-summary-icon-slate { background: #e2e8f0; color: #334155; }

    .patient-shell-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid #e5edf6;
    }

    .patient-shell-kicker {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: #2563eb;
    }

    .patient-shell-nav {
        border-bottom: 1px solid #e5edf6;
        padding: 1rem 1.5rem 1.25rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.88), rgba(241, 245, 249, 0.72));
    }

    .patient-filter-strip {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
    }

    @media (min-width: 1024px) {
        .patient-filter-strip {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .tab-button {
        border: 1px solid #dbe4f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #475569;
        border-radius: 0.9rem;
        transition: all 0.2s ease;
    }

    .tab-button.active,
    .tab-button.active:hover {
        border-color: #1d4ed8;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #1d4ed8;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45), 0 12px 24px -20px rgba(29, 78, 216, 0.55);
    }

    .tab-button:hover:not(.active) {
        color: #0f172a;
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .tab-count {
        display: inline-flex;
        min-width: 1.4rem;
        height: 1.4rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: rgba(148, 163, 184, 0.12);
        padding: 0 0.4rem;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .tab-button.active .tab-count {
        background: rgba(37, 99, 235, 0.12);
    }

    .tab-content {
        display: block;
    }

    .tab-content.hidden {
        display: none;
    }

    .patient-view table thead th {
        font-size: 0.72rem;
        letter-spacing: 0.12em;
        color: #64748b;
    }

    .patient-view table tbody tr {
        transition: background-color 0.18s ease;
    }

    .patient-view table tbody tr:hover {
        background-color: #f8fbff;
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm,
    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm.group {
        border-color: #dbe4ef;
        box-shadow: 0 16px 32px -28px rgba(15, 23, 42, 0.28);
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm > .px-6.py-4.border-b.border-gray-200 {
        border-bottom-color: #e5edf6;
    }

    .patient-view .tab-content label.text-sm {
        color: #475569;
        font-weight: 600;
    }

    @media (max-width: 767px) {
        .patient-avatar-badge {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1.35rem;
            font-size: 1.55rem;
        }
    }

</style>

<script>
    const patientShowI18n = {
        noHistoryRecords: '<?= esc(lang('UI.patientNoHistoryRecords'), 'js') ?>',
        timelineOn: '<?= esc(lang('UI.patientTimelineOn'), 'js') ?>',
        saving: '<?= esc(lang('UI.patientSaving'), 'js') ?>',
        toothUpdatedSuccess: '<?= esc(lang('UI.patientToothUpdatedSuccess'), 'js') ?>',
        toothUpdateError: '<?= esc(lang('UI.patientToothUpdateError'), 'js') ?>',
        toothUpdateGenericError: '<?= esc(lang('UI.patientToothUpdateGenericError'), 'js') ?>',
        unknown: '<?= esc(lang('UI.patientUnknown'), 'js') ?>'
    };

    // Global variables for filtering
    let currentTab = 'details';
    let allTimelineData = [];
    let filteredTimelineData = [];

    // Initialize timeline data
    document.addEventListener('DOMContentLoaded', function() {
        // Store all timeline data for filtering
        allTimelineData = [
            <?php foreach ($timeline as $item): ?>
            {
                type: '<?= $item['type'] ?>',
                date: '<?= $item['date'] ?>',
                title: '<?= addslashes($item['title']) ?>',
                description: '<?= addslashes($item['description']) ?>',
                status: '<?= $item['status'] ?>',
                id: '<?= $item['id'] ?>',
                doctor: '<?= addslashes((string) ($item['doctor'] ?? '')) ?>',
                icon: '<?= $item['icon'] ?>',
                color: '<?= $item['color'] ?>'
            },
            <?php endforeach; ?>
        ];
        filteredTimelineData = [...allTimelineData];
        
        // Set default date range (last 6 months)
        const endDate = new Date();
        const startDate = new Date();
        startDate.setMonth(startDate.getMonth() - 6);
        
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        
        // Add event listeners
        document.getElementById('historySearch').addEventListener('input', filterHistory);
        document.getElementById('startDate').addEventListener('change', filterHistory);
        document.getElementById('endDate').addEventListener('change', filterHistory);

        const shouldOpenAttachments = <?= json_encode((bool) session('attachments_flash')) ?>;
        if (shouldOpenAttachments) {
            const attachmentsTab = document.getElementById('attachments-tab');
            const attachmentsContent = document.getElementById('attachments-content');
            if (attachmentsTab && attachmentsContent) {
                showTab('attachments');
            }
        }

        const dropzone = document.getElementById('attachmentDropzone');
        const fileInput = document.getElementById('attachmentInput');
        const fileList = document.getElementById('attachmentFileList');

        if (dropzone && fileInput && fileList) {
            const renderFiles = (files) => {
                fileList.replaceChildren();
                if (!files || files.length === 0) {
                    return;
                }
                Array.from(files).forEach(file => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between rounded-md bg-white px-3 py-2 border border-gray-200';
                    const name = document.createElement('div');
                    name.className = 'text-sm text-gray-700';
                    name.textContent = file.name;
                    const size = document.createElement('div');
                    size.className = 'text-xs text-gray-500';
                    size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
                    row.appendChild(name);
                    row.appendChild(size);
                    fileList.appendChild(row);
                });
            };

            const setHover = (active) => {
                dropzone.classList.toggle('ring-2', active);
                dropzone.classList.toggle('ring-blue-400', active);
                dropzone.classList.toggle('bg-blue-50', active);
            };

            ['dragenter', 'dragover'].forEach(evt => {
                dropzone.addEventListener(evt, (event) => {
                    event.preventDefault();
                    setHover(true);
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, (event) => {
                    event.preventDefault();
                    setHover(false);
                });
            });

            dropzone.addEventListener('drop', (event) => {
                const files = event.dataTransfer?.files;
                if (!files || files.length === 0) {
                    return;
                }
                const dataTransfer = new DataTransfer();
                Array.from(files).forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
                renderFiles(fileInput.files);
            });

            dropzone.addEventListener('click', () => {
                fileInput.click();
            });

            dropzone.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    fileInput.click();
                }
            });

            fileInput.addEventListener('change', () => {
                renderFiles(fileInput.files);
            });
        }

        const shouldOpenContracts = <?= json_encode((bool) session('contracts_flash')) ?>;
        if (shouldOpenContracts) {
            const contractsTab = document.getElementById('contracts-tab');
            const contractsContent = document.getElementById('contracts-content');
            if (contractsTab && contractsContent) {
                showTab('contracts');
            }
        }
    });

    function showTab(tabName, event = null) {
        if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
        }
        const currentX = window.scrollX;
        const currentY = window.scrollY;
        currentTab = tabName;
        
        // Hide all tab contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.add('hidden'));
        
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab-button');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Show selected tab content
        document.getElementById(tabName + '-content').classList.remove('hidden');
        
        // Add active class to selected tab
        document.getElementById(tabName + '-tab').classList.add('active');

        const historyControls = document.getElementById('historyControls');
        if (historyControls) {
            historyControls.classList.toggle('hidden', tabName === 'details');
        }
        
        // Apply filters to the current tab
        if (tabName === 'timeline') {
            filterTimeline();
        } else if (tabName !== 'details') {
            filterTable(tabName);
        }

        window.requestAnimationFrame(() => {
            window.scrollTo(currentX, currentY);
        });

        return false;
    }

    function filterHistory() {
        if (currentTab === 'timeline') {
            filterTimeline();
        } else if (currentTab !== 'details') {
            filterTable(currentTab);
        }
    }

    function filterTimeline() {
        const searchTerm = document.getElementById('historySearch').value.toLowerCase();
        const startDateRaw = document.getElementById('startDate').value;
        const endDateRaw = document.getElementById('endDate').value;
        const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startDateRaw) : startDateRaw;
        const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endDateRaw) : endDateRaw;
        
        filteredTimelineData = allTimelineData.filter(item => {
            const matchesSearch = !searchTerm || 
                item.title.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm) ||
                item.status.toLowerCase().includes(searchTerm) ||
                (item.doctor && item.doctor.toLowerCase().includes(searchTerm));
                
            const itemDate = new Date(item.date);
            const matchesDateRange = (!startDate || itemDate >= new Date(startDate)) &&
                                    (!endDate || itemDate <= new Date(endDate));
            
            return matchesSearch && matchesDateRange;
        });
        
        renderTimeline();
    }

    function renderTimeline() {
        const timelineContainer = document.querySelector('#timeline-content ul');
        
        // Clear existing content safely
        timelineContainer.textContent = '';
        
        if (filteredTimelineData.length === 0) {
            const li = document.createElement('li');
            const div = document.createElement('div');
            div.className = 'text-center py-8';
            
            const icon = document.createElement('i');
            icon.className = 'fas fa-search text-gray-400 text-4xl mb-4';
            
            const p = document.createElement('p');
            p.className = 'text-gray-500';
            p.textContent = patientShowI18n.noHistoryRecords;
            
            div.appendChild(icon);
            div.appendChild(p);
            li.appendChild(div);
            timelineContainer.appendChild(li);
            return;
        }
        
        filteredTimelineData.forEach((item, index) => {
            const timelineItem = document.createElement('li');
            
            const divRelative = document.createElement('div');
            divRelative.className = 'relative pb-8';
            
            if (index !== filteredTimelineData.length - 1) {
                const spanLine = document.createElement('span');
                spanLine.className = 'absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200';
                spanLine.setAttribute('aria-hidden', 'true');
                divRelative.appendChild(spanLine);
            }
            
            const divFlex = document.createElement('div');
            divFlex.className = 'relative flex space-x-3';
            
            // Icon
            const divIconOuter = document.createElement('div');
            const spanIconBg = document.createElement('span');
            spanIconBg.className = `h-8 w-8 rounded-full bg-${item.color}-500 flex items-center justify-center ring-8 ring-white`;
            const icon = document.createElement('i');
            icon.className = `${item.icon} text-white text-xs`;
            spanIconBg.appendChild(icon);
            divIconOuter.appendChild(spanIconBg);
            
            // Content
            const divContent = document.createElement('div');
            divContent.className = 'min-w-0 flex-1 pt-1.5 flex justify-between space-x-4';
            
            const divText = document.createElement('div');
            const pTitle = document.createElement('p');
            pTitle.className = 'text-sm text-gray-500';
            const spanTitle = document.createElement('span');
            spanTitle.className = 'font-medium text-gray-900';
            spanTitle.textContent = item.title;
            const spanDate = document.createElement('span');
            spanDate.className = 'text-gray-500';
            const itemIso = String(item.date || '').split(' ')[0];
            const itemDisplay = window.formatClinicDateFromIso ? window.formatClinicDateFromIso(itemIso) : itemIso;
            spanDate.textContent = ` ${patientShowI18n.timelineOn} ${itemDisplay}`;
            pTitle.appendChild(spanTitle);
            pTitle.appendChild(spanDate);
            
            const pDesc = document.createElement('p');
            pDesc.className = 'text-sm text-gray-500';
            pDesc.textContent = item.description;

            divText.appendChild(pTitle);
            divText.appendChild(pDesc);
            
            if (item.doctor) {
                const pDoctor = document.createElement('p');
                pDoctor.className = 'text-xs text-gray-400 mt-1';
                pDoctor.textContent = `Dr. ${item.doctor}`;
                divText.appendChild(pDoctor);
            }
            
            const divStatus = document.createElement('div');
            divStatus.className = 'text-right text-sm whitespace-nowrap text-gray-500';
            const spanStatus = document.createElement('span');
            spanStatus.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${item.color}-100 text-${item.color}-800`;
            spanStatus.textContent = item.status.charAt(0).toUpperCase() + item.status.slice(1);
            divStatus.appendChild(spanStatus);
            
            divContent.appendChild(divText);
            divContent.appendChild(divStatus);
            
            divFlex.appendChild(divIconOuter);
            divFlex.appendChild(divContent);
            divRelative.appendChild(divFlex);
            timelineItem.appendChild(divRelative);
            
            timelineContainer.appendChild(timelineItem);
        });
    }

    function filterTable(tabName) {
        const searchTerm = document.getElementById('historySearch').value.toLowerCase();
        const startDateRaw = document.getElementById('startDate').value;
        const endDateRaw = document.getElementById('endDate').value;
        const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startDateRaw) : startDateRaw;
        const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endDateRaw) : endDateRaw;
        
        const table = document.querySelector(`#${tabName}-content table tbody`);
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length === 0) return;
            
            let matchesSearch = true;
            let matchesDateRange = true;
            
            // Check search term
            if (searchTerm) {
                matchesSearch = Array.from(cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchTerm)
                );
            }
            
            // Check date range (prefer ISO data-date when available)
            if (startDate || endDate) {
                const dateCell = cells[0];
                const rawDate = row.dataset.date || (dateCell ? dateCell.getAttribute('data-date') : '') || (dateCell ? dateCell.textContent.trim() : '');
                const cellDate = rawDate ? new Date(rawDate) : null;
                
                if (cellDate && !isNaN(cellDate.getTime())) {
                    if (startDate && cellDate < new Date(startDate)) {
                        matchesDateRange = false;
                    }
                    if (endDate && cellDate > new Date(endDate)) {
                        matchesDateRange = false;
                    }
                }
            }
            
            if (matchesSearch && matchesDateRange) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide "no results" message
        const noResultsDiv = document.querySelector(`#${tabName}-content .text-center`);
        if (noResultsDiv && visibleCount === 0) {
            noResultsDiv.style.display = 'block';
        } else if (noResultsDiv) {
            noResultsDiv.style.display = 'none';
        }
    }

    function clearFilters() {
        document.getElementById('historySearch').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        
        if (currentTab === 'timeline') {
            filteredTimelineData = [...allTimelineData];
            renderTimeline();
        } else if (currentTab !== 'details') {
            filterTable(currentTab);
        }
    }





</script>

<script>
    // Odontogram functionality
    let currentNumberingSystem = 'universal';
    let odontogramData = [
        <?php foreach ($odontogram as $tooth): ?>
        {
            tooth_number: <?= $tooth['tooth_number'] ?>,
            condition_type: '<?= $tooth['condition_type'] ?>',
            condition_description: '<?= addslashes($tooth['condition_description'] ?? '') ?>',
            treatment_notes: '<?= addslashes($tooth['treatment_notes'] ?? '') ?>',
            treatment_status: '<?= $tooth['treatment_status'] ?>',
            treatment_date: '<?= $tooth['treatment_date'] ?? '' ?>'
        },
        <?php endforeach; ?>
    ];

    function switchNumberingSystem(system) {
        currentNumberingSystem = system;
        
        // Update button states
        const universalBtn = document.getElementById('universalBtn');
        const fdiBtn = document.getElementById('fdiBtn');
        
        if (system === 'universal') {
            universalBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            universalBtn.classList.remove('text-gray-600');
            fdiBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            fdiBtn.classList.add('text-gray-600');
        } else {
            fdiBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            fdiBtn.classList.remove('text-gray-600');
            universalBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            universalBtn.classList.add('text-gray-600');
        }
        
        // Toggle number display
        document.querySelectorAll('.universal-number').forEach(el => {
            el.style.display = system === 'universal' ? 'block' : 'none';
        });
        document.querySelectorAll('.fdi-number').forEach(el => {
            el.style.display = system === 'fdi' ? 'block' : 'none';
        });
        document.querySelectorAll('.universal-tooltip').forEach(el => {
            el.style.display = system === 'universal' ? 'block' : 'none';
        });
        document.querySelectorAll('.fdi-tooltip').forEach(el => {
            el.style.display = system === 'fdi' ? 'block' : 'none';
        });
    }

    function openToothModal(toothNumber) {
        const modal = document.getElementById('toothModal');
        const modalContent = document.getElementById('modalContent');
        
        document.getElementById('modalToothNumber').textContent = toothNumber;
        document.getElementById('toothNumber').value = toothNumber;
        
        // Load existing data for this tooth
        const existingTooth = odontogramData.find(t => parseInt(t.tooth_number) === parseInt(toothNumber));
        
        if (existingTooth) {
            document.getElementById('condition_type').value = existingTooth.condition_type || 'healthy';
            document.getElementById('condition_description').value = existingTooth.condition_description || '';
            document.getElementById('treatment_notes').value = existingTooth.treatment_notes || '';
            document.getElementById('treatment_status').value = existingTooth.treatment_status || 'completed';
        } else {
            document.getElementById('condition_type').value = 'healthy';
            document.getElementById('condition_description').value = '';
            document.getElementById('treatment_notes').value = '';
            document.getElementById('treatment_status').value = 'completed';
        }
        
        // Show modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 10);
    }

    function closeToothModal() {
        const modal = document.getElementById('toothModal');
        const modalContent = document.getElementById('modalContent');
        
        // Animate out
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function updateTooth(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const toothNumber = formData.get('tooth_number');
        
        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        
        // Cache original children once if not already cached
        if (!submitBtn.hasOwnProperty('_originalChildren')) {
            submitBtn._originalChildren = Array.from(submitBtn.childNodes).map(node => node.cloneNode(true));
        }
        
        const loadingIcon = document.createElement('i');
        loadingIcon.className = 'fas fa-spinner fa-spin mr-2';
        submitBtn.replaceChildren(loadingIcon, document.createTextNode(patientShowI18n.saving));
        submitBtn.disabled = true;
        
        fetch('<?= base_url('odontogram/update-tooth') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Refresh CSRF token if present
            if (data.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(data.csrf_token);
            }

            if (data.success) {
                // ... update local data logic ...
                const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number) === parseInt(toothNumber));
                if (existingIndex >= 0) {
                    odontogramData[existingIndex] = {
                        tooth_number: parseInt(toothNumber),
                        condition_type: formData.get('condition_type'),
                        condition_description: formData.get('condition_description'),
                        treatment_notes: formData.get('treatment_notes'),
                        treatment_status: formData.get('treatment_status'),
                        treatment_date: new Date().toISOString().split('T')[0]
                    };
                } else {
                    odontogramData.push({
                        tooth_number: parseInt(toothNumber),
                        condition_type: formData.get('condition_type'),
                        condition_description: formData.get('condition_description'),
                        treatment_notes: formData.get('treatment_notes'),
                        treatment_status: formData.get('treatment_status'),
                        treatment_date: new Date().toISOString().split('T')[0]
                    });
                }
                
                updateToothAppearance(toothNumber, formData.get('condition_type'));
                showNotification(patientShowI18n.toothUpdatedSuccess, 'success');
                closeToothModal();
            } else {
                const errorText = patientShowI18n.toothUpdateError.replace('{message}', data.message || patientShowI18n.unknown);
                showNotification(errorText, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(patientShowI18n.toothUpdateGenericError, 'error');
        })
        .finally(() => {
            // Restore original button state
            submitBtn.replaceChildren(...submitBtn._originalChildren.map(node => node.cloneNode(true)));
            submitBtn.disabled = false;
        });
    }

    // ... resetTooth function ...

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        
        const flexDiv = document.createElement('div');
        flexDiv.className = 'flex items-center space-x-2';
        
        const icon = document.createElement('i');
        icon.className = `fas ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-times' : 'fa-info'}`;
        
        const span = document.createElement('span');
        span.textContent = message;
        
        flexDiv.appendChild(icon);
        flexDiv.appendChild(span);
        notification.appendChild(flexDiv);
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(full)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Close modal when clicking outside
    document.getElementById('toothModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeToothModal();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeToothModal();
        }
    });

    const initAttachmentCategorySelect = function () {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
            return;
        }

        const $attachmentCategory = $('#attachmentCategory');
        if (!$attachmentCategory.length || $attachmentCategory.data('select2')) {
            return;
        }

        $attachmentCategory.select2({
            placeholder: <?= json_encode(lang('UI.patientAttachmentCategoryPlaceholder')) ?>,
            allowClear: true,
            width: '100%',
            theme: 'default',
            minimumInputLength: 0,
            minimumResultsForSearch: 0,
            language: {
                noResults: function() {
                    return 'No results found';
                },
                searching: function() {
                    return 'Searching...';
                }
            }
        });
    };

    initAttachmentCategorySelect();
</script>

<?= $this->endSection() ?>


