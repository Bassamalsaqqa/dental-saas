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

function loadInlinePatientMiniOdontogramSvg(): string
{
    $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
    if (!is_file($svgPath)) {
        return '';
    }

    $svgContent = preg_replace('/<\?xml.*?\?>/i', '', (string) file_get_contents($svgPath), 1);
    return $svgContent === '' ? '' : $svgContent;
}

$isRtl = !empty($is_rtl);
$patientContextReturnTo = 'patient/' . (int) ($patient['id'] ?? 0);
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
        $nextAppointmentSubLabel = trim($typeValue . ($timeValue ? ' - ' . $timeValue : ''));
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

$financeTypeLabel = static function (?string $type): string {
    $type = trim((string) $type);
    if ($type === '') {
        return lang('UI.patientUnknown');
    }

    $localized = lang('Finance.' . $type);
    if ($localized !== 'Finance.' . $type) {
        return $localized;
    }

    return ucfirst(str_replace('_', ' ', $type));
};

$financeMethodLabel = static function (?string $method): string {
    $method = trim((string) $method);
    if ($method === '') {
        return lang('UI.patientNa');
    }

    $keyMap = [
        'bank_transfer' => 'bankTransfer',
    ];
    $localized = lang('Finance.' . ($keyMap[$method] ?? $method));
    if ($localized !== 'Finance.' . ($keyMap[$method] ?? $method)) {
        return $localized;
    }

    return ucfirst(str_replace('_', ' ', $method));
};

$financeStatusLabel = static function (?string $status): string {
    $status = trim((string) $status);
    if ($status === '') {
        return lang('UI.patientNa');
    }

    $localized = lang('Finance.' . $status);
    if ($localized !== 'Finance.' . $status) {
        return $localized;
    }

    return ucfirst(str_replace('_', ' ', $status));
};

$treatmentBillingMeta = static function (array $treatmentRow) use ($financeStatusLabel): array {
    $chargeId = (int) ($treatmentRow['clinical_charge_line_id'] ?? 0);
    $status = strtolower((string) ($treatmentRow['clinical_charge_status'] ?? ''));
    $amount = isset($treatmentRow['clinical_charge_amount']) ? (float) $treatmentRow['clinical_charge_amount'] : null;
    $currency = (string) ($treatmentRow['clinical_charge_currency'] ?? '');
    $financeId = (int) ($treatmentRow['clinical_charge_finance_id'] ?? 0);
    $invoiceNumber = trim((string) ($treatmentRow['clinical_charge_invoice_number'] ?? ''));
    $invoiceStatus = strtolower((string) ($treatmentRow['clinical_charge_invoice_status'] ?? ''));
    $invoicePaid = isset($treatmentRow['clinical_charge_invoice_paid']) ? max(0, (float) $treatmentRow['clinical_charge_invoice_paid']) : 0.0;
    $invoiceBalance = isset($treatmentRow['clinical_charge_invoice_balance']) ? max(0, (float) $treatmentRow['clinical_charge_invoice_balance']) : 0.0;

    if ($chargeId <= 0 || $amount === null) {
        return [
            'amount_text' => lang('UI.patientNotBilledYet'),
            'amount_raw' => 0.0,
            'status_text' => lang('UI.patientNoChargeCaptured'),
            'status_class' => 'bg-slate-100 text-slate-600',
            'finance_id' => 0,
            'invoice_number' => '',
            'invoice_status' => '',
            'invoice_status_text' => '',
            'invoice_status_class' => 'bg-slate-100 text-slate-600',
            'paid_amount' => 0.0,
            'paid_text' => '',
            'balance_amount' => 0.0,
            'balance_text' => '',
            'is_billed' => false,
            'is_paid' => false,
            'has_balance' => false,
        ];
    }

    $isBilled = $status === 'invoiced' && $financeId > 0;
    $isPaid = $isBilled && $invoiceBalance <= 0.009 && in_array($invoiceStatus, ['paid', 'partial', 'pending', 'overdue'], true);
    $hasBalance = $isBilled && $invoiceBalance > 0.009;
    $invoiceStatusClass = match ($invoiceStatus) {
        'paid' => 'bg-emerald-100 text-emerald-700',
        'partial' => 'bg-blue-100 text-blue-700',
        'overdue' => 'bg-rose-100 text-rose-700',
        'pending' => 'bg-amber-100 text-amber-700',
        default => 'bg-slate-100 text-slate-600',
    };

    return [
        'amount_text' => trim(($currency !== '' ? $currency . ' ' : '') . number_format($amount, 2)),
        'amount_raw' => $amount,
        'status_text' => $isBilled ? lang('UI.patientBilled') : lang('UI.patientReadyToBill'),
        'status_class' => $isBilled ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700',
        'finance_id' => $financeId,
        'invoice_number' => $invoiceNumber,
        'invoice_status' => $invoiceStatus,
        'invoice_status_text' => $invoiceStatus !== '' ? $financeStatusLabel($invoiceStatus) : '',
        'invoice_status_class' => $invoiceStatusClass,
        'paid_amount' => $invoicePaid,
        'paid_text' => formatCurrency($invoicePaid),
        'balance_amount' => $invoiceBalance,
        'balance_text' => formatCurrency($invoiceBalance),
        'is_billed' => $isBilled,
        'is_paid' => $isPaid,
        'has_balance' => $hasBalance,
    ];
};

$openClinicalChargeCount = 0;
$openClinicalChargeTotal = 0.0;
$paidBilledTreatmentCount = 0;
$unpaidBilledTreatmentCount = 0;
foreach ($treatments as $treatmentItem) {
    $billingMeta = $treatmentBillingMeta($treatmentItem);
    if (!$billingMeta['is_billed'] && ($billingMeta['amount_raw'] ?? 0) > 0) {
        $openClinicalChargeCount++;
        $openClinicalChargeTotal += (float) $billingMeta['amount_raw'];
    }
    if ($billingMeta['is_billed']) {
        if ($billingMeta['has_balance']) {
            $unpaidBilledTreatmentCount++;
        } else {
            $paidBilledTreatmentCount++;
        }
    }
}

$totalCharges = 0.0;
$totalCollected = 0.0;
$totalRefunded = 0.0;
$outstandingBalance = 0.0;
$openInvoicesCount = 0;
$financialLedgerRows = [];
$financeLookup = [];

foreach ($finances as $financeItem) {
    $financeId = (int) ($financeItem['id'] ?? 0);
    if ($financeId > 0) {
        $financeLookup[$financeId] = $financeItem;
    }

    $transactionType = strtolower((string) ($financeItem['transaction_type'] ?? 'invoice'));
    $paymentStatus = strtolower((string) ($financeItem['payment_status'] ?? ''));
    $totalAmount = (float) ($financeItem['total_amount'] ?? (($financeItem['amount'] ?? 0) - ($financeItem['discount_amount'] ?? 0) + ($financeItem['tax_amount'] ?? 0)));
    $balanceDue = isset($financeItem['balance_due']) ? max(0, (float) $financeItem['balance_due']) : 0.0;
    $documentDate = $financeItem['transaction_date'] ?? $financeItem['paid_date'] ?? $financeItem['created_at'] ?? null;
    $descriptionParts = [];

    if (!empty($financeItem['description'])) {
        $descriptionParts[] = trim((string) $financeItem['description']);
    }
    if (!empty($financeItem['service_type'])) {
        $descriptionParts[] = $financeTypeLabel((string) $financeItem['service_type']);
    } elseif (!empty($financeItem['payment_method'])) {
        $descriptionParts[] = $financeMethodLabel((string) $financeItem['payment_method']);
    }

    if ($transactionType === 'invoice' && $paymentStatus !== 'cancelled') {
        $totalCharges += $totalAmount;
        $outstandingBalance += $balanceDue;
        if ($balanceDue > 0.009) {
            $openInvoicesCount++;
        }
    } elseif (in_array($transactionType, ['payment', 'adjustment'], true)) {
        $totalCollected += $totalAmount;
    } elseif ($transactionType === 'refund') {
        $totalRefunded += $totalAmount;
    }

    $signedAmount = $totalAmount;
    if (in_array($transactionType, ['refund', 'expense'], true)) {
        $signedAmount *= -1;
    }

    $timestamp = $documentDate ? strtotime((string) $documentDate) : false;
    $financialLedgerRows[] = [
        'row_kind' => 'finance',
        'row_id' => 'finance-' . $financeId,
        'finance_id' => $financeId,
        'document_date' => $documentDate,
        'document_date_iso' => !empty($documentDate) ? substr((string) $documentDate, 0, 10) : '',
        'timestamp' => $timestamp ?: 0,
        'record_label' => $financeTypeLabel($transactionType),
        'record_note' => implode(' - ', array_filter($descriptionParts, static fn(string $value): bool => $value !== '')),
        'reference' => (string) ($financeItem['transaction_id'] ?? ('#' . $financeId)),
        'amount' => $signedAmount,
        'method_label' => $financeMethodLabel($financeItem['payment_method'] ?? null),
        'status_label' => $financeStatusLabel($financeItem['payment_status'] ?? null),
        'status_key' => $paymentStatus !== '' ? $paymentStatus : 'recorded',
        'action_url' => workflow_context_url('finance/' . $financeId, $patientContextReturnTo),
    ];
}

foreach (($finance_settlements ?? []) as $settlement) {
    $financeId = (int) ($settlement['finance_id'] ?? 0);
    $settlementType = strtolower((string) ($settlement['type'] ?? 'payment'));
    $amount = max(0, (float) ($settlement['amount'] ?? 0));
    $parentFinance = $financeLookup[$financeId] ?? null;
    $reference = (string) ($parentFinance['transaction_id'] ?? ('#' . $financeId));
    $signedAmount = in_array($settlementType, ['refund', 'void'], true) ? -$amount : $amount;

    if (in_array($settlementType, ['payment', 'adjustment'], true)) {
        $totalCollected += $amount;
    } elseif (in_array($settlementType, ['refund', 'void'], true)) {
        $totalRefunded += $amount;
    }

    $timestamp = !empty($settlement['payment_date']) ? strtotime((string) $settlement['payment_date']) : false;
    $recordedBy = trim(((string) ($settlement['creator_first_name'] ?? '')) . ' ' . ((string) ($settlement['creator_last_name'] ?? '')));
    $settlementNotes = trim((string) ($settlement['notes'] ?? ''));
    $recordNote = $settlementNotes !== '' ? $settlementNotes : $reference;
    if ($recordedBy !== '') {
        $recordNote .= ' - ' . $recordedBy;
    }

    $financialLedgerRows[] = [
        'row_kind' => 'settlement',
        'row_id' => 'settlement-' . (int) ($settlement['id'] ?? 0),
        'finance_id' => $financeId,
        'document_date' => $settlement['payment_date'] ?? null,
        'document_date_iso' => !empty($settlement['payment_date']) ? substr((string) $settlement['payment_date'], 0, 10) : '',
        'timestamp' => $timestamp ?: 0,
        'record_label' => $financeTypeLabel($settlementType),
        'record_note' => $recordNote,
        'reference' => $reference,
        'amount' => $signedAmount,
        'method_label' => $financeMethodLabel($settlement['payment_method'] ?? null),
        'status_label' => $financeTypeLabel($settlementType),
        'status_key' => 'recorded',
        'action_url' => workflow_context_url('finance/' . $financeId, $patientContextReturnTo),
    ];
}

usort($financialLedgerRows, static function (array $a, array $b): int {
    $timeCompare = (int) ($b['timestamp'] ?? 0) <=> (int) ($a['timestamp'] ?? 0);
    if ($timeCompare !== 0) {
        return $timeCompare;
    }

    return strcmp((string) ($b['row_id'] ?? ''), (string) ($a['row_id'] ?? ''));
});

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
    ['label' => lang('UI.patientPhone'), 'display_label' => '', 'value' => $patientPhoneChipValue, 'icon' => 'fas fa-phone-alt', 'class' => 'patient-hero-chip-ltr'],
    ['label' => lang('UI.patientGender'), 'display_label' => '', 'value' => $patientGenderChipValue, 'icon' => 'fas fa-user-tag', 'class' => ''],
    ['label' => lang('UI.patientCity'), 'display_label' => '', 'value' => $patientCityChipValue, 'icon' => 'fas fa-location-dot', 'class' => ''],
];

$patientHeaderSnapshotCards = [
    [
        'label' => lang('UI.patientMedicalAlertsSummary'),
        'value' => $medicalAlertCount > 0 ? (string) $medicalAlertCount : lang('UI.patientNoneShort'),
        'detail' => $medicalAlertCount > 0 ? lang('UI.patientProfileAlertSummary', ['count' => $medicalAlertCount]) : lang('UI.patientStatusActive'),
        'icon' => 'fas fa-triangle-exclamation',
        'class' => $medicalAlertCount > 0 ? 'patient-header-snapshot-card-alert' : 'patient-header-snapshot-card-calm',
    ],
    [
        'label' => lang('UI.patientNextAppointment'),
        'value' => $nextAppointmentChipValue,
        'detail' => $nextAppointmentSubLabel,
        'icon' => 'fas fa-calendar-check',
        'class' => 'patient-header-snapshot-card-blue',
    ],
    [
        'label' => lang('UI.patientOutstandingBalanceShort'),
        'value' => $outstandingBalanceChipValue,
        'detail' => $outstandingBalanceSubLabel,
        'icon' => 'fas fa-wallet',
        'class' => $outstandingBalance > 0.009 ? 'patient-header-snapshot-card-due' : 'patient-header-snapshot-card-calm',
    ],
];

$patientOverviewFacts = [
    [
        'label' => lang('UI.patientPatientId'),
        'value' => (string) ($patient['patient_id'] ?? lang('UI.patientNotProvided')),
        'accent' => 'patient-fact-accent-blue',
    ],
    [
        'label' => lang('UI.patientFullName'),
        'value' => $fullName !== '' ? $fullName : lang('UI.patientNotProvided'),
        'accent' => 'patient-fact-accent-slate',
    ],
    [
        'label' => lang('UI.patientStatus'),
        'value' => $patientStatusLabel,
        'accent' => 'patient-fact-accent-emerald',
    ],
    [
        'label' => lang('UI.patientGender'),
        'value' => $patientGenderLabel,
        'accent' => 'patient-fact-accent-violet',
    ],
    [
        'label' => lang('UI.patientAge'),
        'value' => $patientAgeLabel,
        'accent' => 'patient-fact-accent-amber',
    ],
    [
        'label' => lang('UI.patientDateOfBirth'),
        'value' => !empty($patient['date_of_birth']) ? formatDate($patient['date_of_birth']) : lang('UI.patientNotProvided'),
        'accent' => 'patient-fact-accent-slate',
    ],
];

$patientContactFacts = [
    [
        'label' => lang('UI.patientPhone'),
        'value' => $patientPhone,
        'accent' => 'patient-fact-accent-blue',
    ],
    [
        'label' => lang('UI.patientEmail'),
        'value' => $patientEmail,
        'accent' => 'patient-fact-accent-slate',
    ],
    [
        'label' => lang('UI.patientCity'),
        'value' => $patientCity,
        'accent' => 'patient-fact-accent-violet',
        'wide' => true,
    ],
];

$patientChartFacts = [
    [
        'label' => lang('UI.patientCreatedBy'),
        'value' => $creatorName !== '' ? $creatorName : lang('UI.patientUnknownDoctor'),
        'accent' => 'patient-fact-accent-slate',
    ],
    [
        'label' => lang('UI.patientMemberSince'),
        'value' => $memberSince,
        'accent' => 'patient-fact-accent-blue',
    ],
    [
        'label' => lang('UI.patientLastVisit'),
        'value' => $lastVisitLabel,
        'accent' => 'patient-fact-accent-emerald',
    ],
    [
        'label' => lang('UI.patientNextAppointment'),
        'value' => $nextAppointmentLabel,
        'accent' => 'patient-fact-accent-amber',
    ],
];

$miniOdontogramInlineSvg = loadInlinePatientMiniOdontogramSvg();
$latestSnapshot = !empty($odontogram_snapshots) ? $odontogram_snapshots[0] : null;
$snapshotSourceLabelMap = [
    'examination' => lang('UI.patientSnapshotSourceExamination'),
    'contract' => lang('UI.patientSnapshotSourceContract'),
    'manual' => lang('UI.patientSnapshotSourceManual'),
];
$snapshotPreviewRows = [];
foreach (($odontogram_snapshots ?? []) as $snapshotRow) {
    $snapshotPreviewRows[] = [
        'id' => (int) ($snapshotRow['id'] ?? 0),
        'label' => $snapshotSourceLabelMap[$snapshotRow['source_type'] ?? ''] ?? lang('UI.patientSnapshotSourceUnknown'),
        'created_at' => $snapshotRow['created_at'] ?? null,
        'count' => (int) ($snapshotRow['affected_count'] ?? 0),
        'raw_label' => $snapshotRow['label'] ?? null,
        'source_url' => $snapshotRow['source_url'] ?? null,
    ];
}

$latestImagingAttachment = $latest_imaging_attachment ?? null;
$latestImagingPreviewSrc = $latest_imaging_preview_src ?? null;
$latestImagingIsPreviewable = !empty($latestImagingAttachment['mime_type']) && strpos((string) $latestImagingAttachment['mime_type'], 'image/') === 0;
$latestImagingViewUrl = !empty($latestImagingAttachment['id']) ? base_url('file/inline/' . $latestImagingAttachment['id']) : null;
$latestImagingOpenUrl = !empty($latestImagingAttachment['id']) ? base_url('file/download/' . $latestImagingAttachment['id']) : null;
?>

<?= $this->section('content') ?>
<div class="patient-view mx-auto w-full max-w-none px-2 py-6 sm:px-3 sm:py-8 xl:px-4 2xl:px-5">
    <div class="space-y-8">
        <section class="patient-hero-card <?= esc($patientHeroThemeClass) ?> overflow-hidden rounded-[28px] border border-slate-200">
            <div class="patient-hero-overlay"></div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
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
                                <div class="space-y-2">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($fullName) ?></h1>
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold <?= esc($patientStatusTone) ?>">
                                                <?= esc($patientStatusLabel) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <aside class="flex w-full flex-wrap items-start justify-start gap-2 xl:w-auto xl:justify-end">
                            <?php if (has_permission('patients', 'edit')): ?>
                                <button type="button" data-app-drawer-open="patientProfileEditDrawer" class="patient-header-action patient-header-action-soft">
                                    <i class="fas fa-pen"></i>
                                    <span><?= esc(lang('UI.patientEditPatient')) ?></span>
                                </button>
                            <?php endif; ?>
                            <a href="<?= esc(workflow_back('patients', $return_to ?? null)) ?>" class="patient-header-action patient-header-action-soft">
                                <i class="fas fa-arrow-left"></i>
                                <span><?= esc(lang('UI.back')) ?></span>
                            </a>
                        </aside>
                    </div>
                    <div class="patient-header-pill-grid">
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
                        <?php foreach ($patientHeaderSnapshotCards as $snapshotCard): ?>
                            <div class="patient-header-snapshot-pill <?= esc($snapshotCard['class']) ?>" title="<?= esc($snapshotCard['label'] . ': ' . $snapshotCard['detail']) ?>">
                                <span class="patient-header-snapshot-icon"><i class="<?= esc($snapshotCard['icon']) ?>"></i></span>
                                <span class="min-w-0">
                                    <span class="block truncate text-xs font-black"><?= esc($snapshotCard['value']) ?></span>
                                    <span class="block max-w-[8.5rem] truncate text-[0.64rem] font-bold opacity-80"><?= esc($snapshotCard['label']) ?></span>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <?php if (has_permission('patients', 'edit')): ?>
            <?php
            $patientEditDrawerId = 'patientProfileEditDrawer';
            $patientEditFormId = 'patientProfileEditDrawerForm';
            ob_start();
            ?>
            <form id="<?= esc($patientEditFormId) ?>" action="<?= base_url('patient/' . (int) $patient['id'] . '/update') ?>" method="post" class="space-y-5">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc('patient/' . (int) $patient['id']) ?>">
                <?php foreach ([
                    'alert_has_allergies',
                    'alert_allergies_details',
                    'alert_taking_medications',
                    'alert_medications_details',
                    'alert_blood_thinners',
                    'alert_blood_thinners_details',
                    'alert_diabetes',
                    'alert_diabetes_details',
                    'alert_high_blood_pressure',
                    'alert_blood_pressure_details',
                    'alert_heart_condition',
                    'alert_heart_condition_details',
                    'alert_pregnant',
                    'alert_pregnancy_details',
                    'alert_anesthesia_problem',
                    'alert_anesthesia_problem_details',
                ] as $preservedPatientField): ?>
                    <input type="hidden" name="<?= esc($preservedPatientField) ?>" value="<?= esc((string) ($patient[$preservedPatientField] ?? '')) ?>">
                <?php endforeach; ?>

                <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-700"><i class="fas fa-user-pen"></i></span>
                        <div>
                            <h3 class="text-base font-black text-slate-950"><?= esc(lang('UI.patientEditPatient')) ?></h3>
                            <p class="text-sm font-semibold text-slate-500"><?= esc($fullName) ?></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="profile_edit_first_name" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientFirstName')) ?> *</label>
                            <input id="profile_edit_first_name" name="first_name" type="text" required value="<?= esc(old('first_name', $patient['first_name'] ?? '')) ?>" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-500/15">
                        </div>
                        <div>
                            <label for="profile_edit_last_name" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientLastName')) ?> *</label>
                            <input id="profile_edit_last_name" name="last_name" type="text" required value="<?= esc(old('last_name', $patient['last_name'] ?? '')) ?>" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-500/15">
                        </div>
                        <div>
                            <label for="profile_edit_phone" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientPhoneNumber')) ?> *</label>
                            <input id="profile_edit_phone" name="phone" type="tel" required minlength="9" maxlength="15" inputmode="numeric" pattern="\d{9,15}" value="<?= esc(old('phone', $patient['phone'] ?? '')) ?>" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" dir="ltr">
                        </div>
                        <div>
                            <label for="profile_edit_email" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientEmail')) ?></label>
                            <input id="profile_edit_email" name="email" type="email" value="<?= esc(old('email', $patient['email'] ?? '')) ?>" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-purple-500 focus:ring-4 focus:ring-purple-500/15">
                        </div>
                        <div>
                            <label for="profile_edit_date_of_birth" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientDateOfBirth')) ?> *</label>
                            <input id="profile_edit_date_of_birth" name="date_of_birth" type="text" data-clinic-date="1" required value="<?= esc(old('date_of_birth', $patient['date_of_birth'] ?? '')) ?>" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15">
                        </div>
                        <div data-select2-dropdown-parent>
                            <label for="profile_edit_gender" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientGender')) ?> *</label>
                            <select id="profile_edit_gender" name="gender" data-force-select2 required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-500/15">
                                <option value=""><?= esc(lang('UI.patientSelect')) ?></option>
                                <?php foreach (['male' => lang('UI.patientMale'), 'female' => lang('UI.patientFemale'), 'other' => lang('UI.patientOther')] as $genderValue => $genderLabel): ?>
                                    <option value="<?= esc($genderValue) ?>" <?= old('gender', $patient['gender'] ?? '') === $genderValue ? 'selected' : '' ?>><?= esc($genderLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="profile_edit_city" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientCity')) ?></label>
                            <input id="profile_edit_city" name="city" type="text" value="<?= esc(old('city', $patient['city'] ?? '')) ?>" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-500/15">
                        </div>
                        <div class="md:col-span-2">
                            <label for="profile_edit_notes" class="mb-2 block text-sm font-bold text-slate-700"><?= esc(lang('UI.patientNotes')) ?></label>
                            <textarea id="profile_edit_notes" name="notes" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-500/15"><?= esc(old('notes', $patient['notes'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </section>
            </form>
            <?php
            $patientEditDrawerBody = (string) ob_get_clean();
            ob_start();
            ?>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="<?= base_url('patient/' . (int) $patient['id'] . '/edit?' . http_build_query(['return_to' => 'patient/' . (int) $patient['id']])) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-sky-700 hover:bg-sky-50">
                    <i class="fas fa-up-right-from-square"></i>
                    <span><?= esc(lang('UI.openFullPage')) ?></span>
                </a>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" data-app-drawer-close="<?= esc($patientEditDrawerId) ?>"><?= esc(lang('UI.cancel')) ?></button>
                    <button type="submit" form="<?= esc($patientEditFormId) ?>" class="inline-flex items-center justify-center rounded-xl bg-sky-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-sky-800"><?= esc(lang('UI.settingsSave')) ?></button>
                </div>
            </div>
            <?php
            $patientEditDrawerFooter = (string) ob_get_clean();
            ?>
            <?= view('components/app_drawer', [
                'id' => $patientEditDrawerId,
                'title' => lang('UI.patientEditPatient'),
                'description' => $fullName,
                'icon' => 'fas fa-user-pen',
                'tone' => 'bg-sky-700',
                'size' => 'form',
                'body' => $patientEditDrawerBody,
                'footer' => $patientEditDrawerFooter,
            ]) ?>
        <?php endif; ?>

        <?= view('patient/partials/context_quick_drawers', [
            'patient' => $patient,
            'is_rtl' => $isRtl,
            'attachments_enabled' => $attachments_enabled ?? false,
            'attachment_category_options' => $attachment_category_options ?? [],
            'patient_command_treatment_types' => $patient_command_treatment_types ?? [],
            'patient_command_chair_options' => $patient_command_chair_options ?? [],
            'patient_command_base_currency' => $patient_command_base_currency ?? null,
            'patient_command_currency_options' => $patient_command_currency_options ?? [],
            'patient_command_inventory_items' => $patient_command_inventory_items ?? [],
            'patient_command_clinic_providers' => $patient_command_clinic_providers ?? [],
            'patient_command_liquidity_account_options' => $patient_command_liquidity_account_options ?? [],
            'attachments_max_file_mb' => $attachments_max_file_mb ?? null,
        ]) ?>

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

        <!-- Main Content Layout -->
        <div class="w-full">
            <?php
            $patientTabCount = 7 + (!empty($contracts_enabled) ? 1 : 0);
            ?>
            <!-- Patient History Tabs -->
            <div class="w-full">
                <div class="patient-shell" data-hero-theme="<?= esc($patientHeroThemeClass) ?>">
                    <!-- Tab Navigation -->
                    <div class="patient-shell-nav">
                        <div class="flex flex-col gap-4">
                            <nav class="flex w-full gap-1.5 overflow-x-auto pb-1 justify-center" aria-label="<?= esc(lang('UI.patientProfileWorkspace')) ?>" role="tablist">
                                <button type="button" onclick="return showTab('details', event)" 
                                        id="details-tab" 
                                        role="tab"
                                        aria-selected="true"
                                        aria-controls="details-content"
                                        tabindex="0"
                                        class="tab-button active inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                    <i class="fas fa-user shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabDetails')) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('timeline', event)"
                                        id="timeline-tab"
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="timeline-content"
                                        tabindex="-1"
                                        class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                    <i class="fas fa-history shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabTimeline')) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('examinations', event)"
                                        id="examinations-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="examinations-content"
                                        tabindex="-1"
                                        class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                    <i class="fas fa-stethoscope shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabExams')) ?></span> <span class="tab-count shrink-0"><?= count($examinations) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('appointments', event)" 
                                        id="appointments-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="appointments-content"
                                        tabindex="-1"
                                        class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                    <i class="fas fa-calendar shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabAppts')) ?></span> <span class="tab-count shrink-0"><?= count($appointments) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('treatments', event)" 
                                        id="treatments-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="treatments-content"
                                        tabindex="-1"
                                        class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                    <i class="fas fa-tooth shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTreatments')) ?></span> <span class="tab-count shrink-0"><?= count($treatments) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('prescriptions', event)" 
                                        id="prescriptions-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="prescriptions-content"
                                        tabindex="-1"
                                        class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                    <i class="fas fa-prescription shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabRx')) ?></span> <span class="tab-count shrink-0"><?= count($prescriptions) ?></span>
                                </button>
                                <?php if (!empty($attachments_enabled)): ?>
                                    <button type="button" onclick="return showTab('attachments', event)" 
                                            id="attachments-tab" 
                                            role="tab"
                                            aria-selected="false"
                                            aria-controls="attachments-content"
                                            tabindex="-1"
                                            class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                        <i class="fas fa-paperclip shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabAttachments')) ?></span> <span class="tab-count shrink-0"><?= count($attachments ?? []) ?></span>
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($contracts_enabled)): ?>
                                    <button type="button" onclick="return showTab('contracts', event)" 
                                            id="contracts-tab" 
                                            role="tab"
                                            aria-selected="false"
                                            aria-controls="contracts-content"
                                            tabindex="-1"
                                            class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                        <i class="fas fa-file-signature shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabContracts')) ?></span> <span class="tab-count shrink-0"><?= count($contracts ?? []) ?></span>
                                    </button>
                                <?php endif; ?>
                                <button type="button" onclick="return showTab('finances', event)" 
                                        id="finances-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="finances-content"
                                        tabindex="-1"
                                        class="tab-button inline-flex min-w-0 items-center justify-center gap-2 whitespace-nowrap px-3 py-2.5 text-sm font-medium">
                                    <i class="fas fa-file-invoice-dollar shrink-0"></i><span class="truncate"><?= esc(lang('UI.patientTabFinancials')) ?></span> <span class="tab-count shrink-0"><?= count($financialLedgerRows) ?></span>
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
                        <div id="details-content" class="tab-content" role="tabpanel" aria-labelledby="details-tab">
                            <div class="patient-details-layout">
                                <div class="patient-details-main">
                                    <section class="patient-compact-card patient-compact-card-profile">
                                        <div class="patient-compact-header">
                                            <div>
                                                <h3 class="patient-compact-title"><?= esc(lang('UI.patientSummaryTitle')) ?></h3>
                                                <p class="patient-compact-copy"><?= esc(lang('UI.patientSummaryHelp')) ?></p>
                                            </div>
                                        </div>
                                        <div class="patient-facts-section patient-facts-section-primary">
                                            <div class="patient-facts-grid">
                                                <?php foreach ($patientOverviewFacts as $fact): ?>
                                                    <article class="patient-fact-card <?= esc($fact['accent']) ?>">
                                                        <span class="patient-fact-label"><?= esc($fact['label']) ?></span>
                                                        <p class="patient-fact-value"><?= esc($fact['value']) ?></p>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="patient-facts-split patient-facts-split-secondary">
                                            <section class="patient-facts-section">
                                                <div class="patient-facts-heading">
                                                    <h4><?= esc(lang('UI.patientContactSection')) ?></h4>
                                                    <p><?= esc(lang('UI.patientContactSectionHelp')) ?></p>
                                                </div>
                                                <div class="patient-facts-grid patient-facts-grid-compact">
                                                    <?php foreach ($patientContactFacts as $fact): ?>
                                                        <article class="patient-fact-card <?= esc($fact['accent']) ?><?= !empty($fact['wide']) ? ' patient-fact-card-wide' : '' ?>">
                                                            <span class="patient-fact-label"><?= esc($fact['label']) ?></span>
                                                            <p class="patient-fact-value"><?= esc($fact['value']) ?></p>
                                                        </article>
                                                    <?php endforeach; ?>
                                                </div>
                                            </section>
                                            <section class="patient-facts-section">
                                                <div class="patient-facts-heading">
                                                    <h4><?= esc(lang('UI.patientChartSection')) ?></h4>
                                                    <p><?= esc(lang('UI.patientChartSectionHelp')) ?></p>
                                                </div>
                                                <div class="patient-facts-grid patient-facts-grid-compact">
                                                    <?php foreach ($patientChartFacts as $fact): ?>
                                                        <article class="patient-fact-card <?= esc($fact['accent']) ?>">
                                                            <span class="patient-fact-label"><?= esc($fact['label']) ?></span>
                                                            <p class="patient-fact-value"><?= esc($fact['value']) ?></p>
                                                        </article>
                                                    <?php endforeach; ?>
                                                </div>
                                            </section>
                                        </div>
                                    </section>
                                    <section class="patient-compact-card patient-compact-card-clinical">
                                        <div class="patient-compact-header">
                                            <div>
                                                <h3 class="patient-compact-title"><?= esc(lang('UI.patientClinicalVisualsTitle')) ?></h3>
                                                <p class="patient-compact-copy"><?= esc(lang('UI.patientClinicalVisualsHelp')) ?></p>
                                            </div>
                                        </div>
                                        <div class="patient-clinical-visuals-grid">
                                            <article class="patient-preview-card">
                                                <div class="patient-preview-card-header">
                                                    <div>
                                                        <h4><?= esc(lang('UI.patientCurrentOdontogramTitle')) ?></h4>
                                                        <p><?= esc(lang('UI.patientCurrentOdontogramHelp')) ?></p>
                                                    </div>
                                                    <a href="<?= workflow_context_url('odontogram/' . $patient['id'], $patientContextReturnTo) ?>" class="patient-preview-action">
                                                        <i class="fas fa-tooth"></i>
                                                        <span><?= esc(lang('UI.patientOpenFullChart')) ?></span>
                                                    </a>
                                                </div>
                                                <div class="patient-preview-frame">
                                                    <?php if (!empty($odontogram) && $miniOdontogramInlineSvg !== ''): ?>
                                                        <div class="patient-odontogram-mini-svg">
                                                            <?= $miniOdontogramInlineSvg ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="patient-preview-empty">
                                                            <i class="fas fa-tooth"></i>
                                                            <p><?= esc(lang('UI.patientNoOdontogramPreview')) ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-preview-metrics">
                                                    <span class="patient-preview-metric"><strong><?= (int) ($odontogram_stats['healthy_count'] ?? 0) ?></strong> <?= esc(lang('UI.patientHealthy')) ?></span>
                                                    <span class="patient-preview-metric"><strong><?= (int) ($odontogram_stats['treated_count'] ?? 0) ?></strong> <?= esc(lang('UI.patientTreated')) ?></span>
                                                    <span class="patient-preview-metric"><strong><?= (int) ($odontogram_stats['needs_treatment_count'] ?? 0) ?></strong> <?= esc(lang('UI.patientNeedsTreatment')) ?></span>
                                                </div>
                                            </article>
                                            <article class="patient-preview-card">
                                                <div class="patient-preview-card-header">
                                                    <div>
                                                        <h4><?= esc(lang('UI.patientLatestImagingTitle')) ?></h4>
                                                        <p><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                                    </div>
                                                    <?php if (!empty($attachments_enabled)): ?>
                                                        <button type="button" onclick="return showTab('attachments', event)" class="patient-preview-action">
                                                            <i class="fas fa-image"></i>
                                                            <span><?= esc(lang('UI.patientOpenImagingWorkspace')) ?></span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-preview-frame">
                                                    <?php if (!empty($latestImagingAttachment) && $latestImagingIsPreviewable && !empty($latestImagingPreviewSrc)): ?>
                                                        <img src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="patient-imaging-preview-image">
                                                    <?php elseif (!empty($latestImagingAttachment) && $latestImagingIsPreviewable): ?>
                                                        <img src="<?= esc($latestImagingViewUrl) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="patient-imaging-preview-image">
                                                    <?php elseif (!empty($latestImagingAttachment)): ?>
                                                        <div class="patient-preview-empty">
                                                            <i class="fas fa-file-medical"></i>
                                                            <p><?= esc(lang('UI.patientImagingPreviewUnavailable')) ?></p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="patient-preview-empty">
                                                            <i class="fas fa-image"></i>
                                                            <p><?= esc(lang('UI.patientNoImagingPreview')) ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-preview-meta">
                                                    <?php if (!empty($latestImagingAttachment)): ?>
                                                        <p class="patient-preview-meta-title"><?= esc($latestImagingAttachment['category'] ?? lang('UI.patientLatestImagingTitle')) ?></p>
                                                        <p class="patient-preview-meta-copy"><?= esc($latestImagingAttachment['original_name'] ?? '') ?></p>
                                                        <p class="patient-preview-meta-copy"><?= !empty($latestImagingAttachment['created_at']) ? formatDateTime($latestImagingAttachment['created_at']) : '--' ?></p>
                                                    <?php else: ?>
                                                        <p class="patient-preview-meta-copy"><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </article>
                                        </div>
                                    </section>
                                </div>
                                <aside class="patient-details-sidebar">
                                    <?php if ($hasStructuredMedicalAlerts || $patient['medical_history'] || $patient['allergies'] || $patient['notes']): ?>
                                        <section class="patient-compact-card patient-compact-card-alerts">
                                            <div class="patient-compact-header">
                                                <div>
                                                    <h3 class="patient-compact-title"><?= esc(lang('UI.patientMedicalInformation')) ?></h3>
                                                    <p class="patient-compact-copy"><?= esc(lang('UI.patientMedicalSectionHelp')) ?></p>
                                                </div>
                                                <?php if ($medicalAlertCount > 0): ?>
                                                    <span class="patient-alert-count"><?= (int) $medicalAlertCount ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="patient-medical-body">
                                                <?php if ($hasStructuredMedicalAlerts): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></h4>
                                                        </div>
                                                        <div class="patient-alert-grid">
                                                            <?php foreach ($medicalAlerts as $medicalAlertRow): ?>
                                                                <?php if (empty($medicalAlertRow['enabled'])) continue; ?>
                                                                <article class="patient-alert-tile">
                                                                    <div class="patient-alert-topline">
                                                                        <span class="patient-alert-title">
                                                                            <i class="<?= esc($medicalAlertRow['icon']) ?> <?= esc($medicalAlertRow['icon_class'] ?? 'text-amber-700') ?>"></i>
                                                                            <span><?= esc($medicalAlertRow['label']) ?></span>
                                                                        </span>
                                                                        <span class="patient-alert-flag"><?= esc(lang('UI.patientYes')) ?></span>
                                                                    </div>
                                                                    <?php if (!empty($medicalAlertRow['details'])): ?>
                                                                        <p class="patient-alert-copy"><?= nl2br(esc($medicalAlertRow['details'])) ?></p>
                                                                    <?php endif; ?>
                                                                </article>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                                <?php if ($patient['medical_history']): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc($hasStructuredMedicalAlerts ? lang('UI.patientLegacyMedicalHistory') : lang('UI.patientMedicalHistory')) ?></h4>
                                                        </div>
                                                        <div class="patient-note-panel">
                                                            <p class="patient-note-copy"><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                                <?php if ($patient['allergies']): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc($hasStructuredMedicalAlerts ? lang('UI.patientLegacyAllergies') : lang('UI.patientAllergies')) ?></h4>
                                                        </div>
                                                        <div class="patient-note-panel">
                                                            <p class="patient-note-copy"><?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                                <?php if ($patient['notes']): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc(lang('UI.patientNotesTitle')) ?></h4>
                                                            <p><?= esc(lang('UI.patientNotesSectionHelp')) ?></p>
                                                        </div>
                                                        <div class="patient-note-panel patient-note-panel-soft">
                                                            <p class="patient-note-copy"><?= nl2br(htmlspecialchars($patient['notes'])) ?></p>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                            </div>
                                        </section>
                                    <?php endif; ?>
                                    <section class="patient-compact-card patient-compact-card-history">
                                        <div class="patient-compact-header">
                                            <div>
                                                <h3 class="patient-compact-title"><?= esc(lang('UI.patientChartHistoryTitle')) ?></h3>
                                                <p class="patient-compact-copy"><?= esc(lang('UI.patientChartHistoryHelp')) ?></p>
                                            </div>
                                            <?php if ($latestSnapshot): ?>
                                                <div class="patient-history-summary">
                                                    <span class="patient-history-summary-count"><?= count($snapshotPreviewRows) ?></span>
                                                    <span class="patient-history-summary-copy"><?= esc(lang('UI.patientSnapshotCapturedOn', ['date' => !empty($latestSnapshot['created_at']) ? formatDateTime($latestSnapshot['created_at']) : '--'])) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="patient-snapshot-list">
                                            <?php if (!empty($snapshotPreviewRows)): ?>
                                                <div class="flex justify-end">
                                                    <a href="<?= workflow_context_url('patient/' . $patient['id'] . '/odontogram-versions', $patientContextReturnTo) ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                                                        <i class="fas fa-clock-rotate-left"></i>
                                                        <span><?= esc(lang('UI.patientChartHistoryOpenAll')) ?></span>
                                                    </a>
                                                </div>
                                                <?php foreach ($snapshotPreviewRows as $snapshotItem): ?>
                                                    <article class="patient-snapshot-item">
                                                        <div class="patient-snapshot-item-topline">
                                                            <span class="patient-snapshot-badge"><?= esc($snapshotItem['label']) ?></span>
                                                            <span class="patient-snapshot-date"><?= esc(lang('UI.patientSnapshotCapturedOn', ['date' => !empty($snapshotItem['created_at']) ? formatDateTime($snapshotItem['created_at']) : '--'])) ?></span>
                                                        </div>
                                                        <?php if (!empty($snapshotItem['raw_label'])): ?>
                                                            <p class="patient-snapshot-title"><?= esc($snapshotItem['raw_label']) ?></p>
                                                        <?php endif; ?>
                                                        <p class="patient-snapshot-copy"><?= esc(lang('UI.patientSnapshotTeethAffected', ['count' => (int) $snapshotItem['count']])) ?></p>
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <?php if (!empty($snapshotItem['id'])): ?>
                                                                <a href="<?= workflow_context_url('patient/' . $patient['id'] . '/odontogram-versions/' . $snapshotItem['id'], $patientContextReturnTo) ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                                                                    <i class="fas fa-eye"></i>
                                                                    <span><?= esc(lang('UI.patientChartHistoryOpenSelected')) ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($snapshotItem['source_url'])): ?>
                                                                <a href="<?= esc($snapshotItem['source_url']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100">
                                                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                                                    <span><?= esc(lang('UI.patientChartHistoryOpenSource')) ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </article>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="patient-preview-empty patient-preview-empty-compact">
                                                    <i class="fas fa-clock-rotate-left"></i>
                                                    <p><?= esc(lang('UI.patientNoChartSnapshots')) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </section>
                                </aside>
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
                                        $examStatus = ucfirst((string) ($exam['status'] ?? lang('UI.patientUnknown')));
                                        $examMeta = [];
                                        $examMeta[] = [
                                            'icon' => 'fas fa-stethoscope',
                                            'text' => ucfirst((string) ($exam['examination_type'] ?? lang('UI.patientGeneral'))),
                                        ];
                                        if (!empty($exam['chief_complaint'])) {
                                            $examComplaintPreview = function_exists('mb_strimwidth')
                                                ? mb_strimwidth((string) $exam['chief_complaint'], 0, 70, '...')
                                                : (strlen((string) $exam['chief_complaint']) > 67 ? substr((string) $exam['chief_complaint'], 0, 67) . '...' : (string) $exam['chief_complaint']);
                                            $examMeta[] = [
                                                'icon' => 'fas fa-comment-medical',
                                                'text' => $examComplaintPreview,
                                            ];
                                        }
                                        $timeline[] = [
                                            'type' => 'examination',
                                            'date' => $exam['examination_date'],
                                            'title' => lang('UI.patientTimelineExamTitle', ['type' => ($exam['examination_type'] ?? lang('UI.patientGeneral'))]),
                                            'description' => $exam['chief_complaint'] ?? lang('UI.patientNoComplaintRecorded'),
                                            'status' => $exam['status'],
                                            'status_label' => $examStatus,
                                            'id' => $exam['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-stethoscope',
                                            'color' => 'blue',
                                            'url' => workflow_context_url('examination/' . $exam['id'], $patientContextReturnTo),
                                            'meta' => $examMeta,
                                        ];
                                    }
                                    
                                    // Add appointments
                                    foreach ($appointments as $appt) {
                                        $doctorName = trim(($appt['doctor_first_name'] ?? '') . ' ' . ($appt['doctor_last_name'] ?? ''));
                                        $apptStatus = ucfirst((string) ($appt['status'] ?? lang('UI.patientUnknown')));
                                        $apptMeta = [];
                                        if (!empty($appt['appointment_time'])) {
                                            $apptMeta[] = [
                                                'icon' => 'fas fa-clock',
                                                'text' => formatTime($appt['appointment_time']),
                                            ];
                                        }
                                        if (!empty($appt['duration'])) {
                                            $apptMeta[] = [
                                                'icon' => 'fas fa-hourglass-half',
                                                'text' => (int) $appt['duration'] . ' ' . lang('UI.patientMinutes'),
                                            ];
                                        }
                                        if (!empty($appt['chair_name'])) {
                                            $apptMeta[] = [
                                                'icon' => 'fas fa-door-open',
                                                'text' => $appt['chair_name'],
                                            ];
                                        }
                                        $timeline[] = [
                                            'type' => 'appointment',
                                            'date' => $appt['appointment_date'],
                                            'title' => lang('UI.patientTimelineAppointmentTitle', ['type' => ($appt['appointment_type'] ?? lang('UI.patientGeneral'))]),
                                            'description' => $appt['notes'] ?? lang('UI.patientNoNotes'),
                                            'status' => $appt['status'],
                                            'status_label' => $apptStatus,
                                            'id' => $appt['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-calendar',
                                            'color' => 'green',
                                            'url' => workflow_context_url('appointment/' . $appt['id'], $patientContextReturnTo),
                                            'meta' => $apptMeta,
                                        ];
                                    }
                                    
                                    // Add treatments
                                    foreach ($treatments as $treatment) {
                                        $doctorName = trim(($treatment['doctor_first_name'] ?? '') . ' ' . ($treatment['doctor_last_name'] ?? ''));
                                        $treatmentStatus = ucfirst((string) ($treatment['status'] ?? lang('UI.patientUnknown')));
                                        $treatmentMeta = [];
                                        if (!empty($treatment['tooth_number'])) {
                                            $treatmentMeta[] = [
                                                'icon' => 'fas fa-tooth',
                                                'text' => (string) $treatment['tooth_number'],
                                            ];
                                        }
                                        $treatmentBilling = $treatmentBillingMeta($treatment);
                                        if ($treatmentBilling['amount_text'] !== lang('UI.patientNotBilledYet')) {
                                            $treatmentMeta[] = [
                                                'icon' => 'fas fa-dollar-sign',
                                                'text' => $treatmentBilling['amount_text'],
                                            ];
                                        }
                                        $treatmentMeta[] = [
                                            'icon' => $treatmentBilling['is_billed'] ? 'fas fa-file-invoice-dollar' : 'fas fa-receipt',
                                            'text' => $treatmentBilling['status_text'],
                                        ];
                                        if ($treatmentBilling['is_billed'] && $treatmentBilling['invoice_number'] !== '') {
                                            $treatmentMeta[] = [
                                                'icon' => 'fas fa-hashtag',
                                                'text' => $treatmentBilling['invoice_number'],
                                            ];
                                        }
                                        if ($treatmentBilling['is_billed']) {
                                            $treatmentMeta[] = [
                                                'icon' => 'fas fa-hand-holding-dollar',
                                                'text' => lang('UI.patientPaidShort') . ': ' . $treatmentBilling['paid_text'],
                                            ];
                                            $treatmentMeta[] = [
                                                'icon' => 'fas fa-scale-balanced',
                                                'text' => lang('UI.patientBalanceShort') . ': ' . $treatmentBilling['balance_text'],
                                            ];
                                        }
                                        $timeline[] = [
                                            'type' => 'treatment',
                                            'date' => $treatment['start_date'],
                                            'title' => lang('UI.patientTimelineTreatmentTitle', ['name' => ($treatment['treatment_name'] ?? lang('UI.patientGeneral'))]),
                                            'description' => $treatment['treatment_description'] ?? lang('UI.patientNoDescription'),
                                            'status' => $treatment['status'],
                                            'status_label' => $treatmentStatus,
                                            'id' => $treatment['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-tooth',
                                            'color' => 'purple',
                                            'url' => workflow_context_url('treatment/' . $treatment['id'], $patientContextReturnTo),
                                            'meta' => $treatmentMeta,
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
                                        $prescriptionStatus = ucfirst((string) ($prescription['status'] ?? lang('UI.patientUnknown')));
                                        $prescriptionPreviewName = function_exists('mb_strimwidth')
                                            ? mb_strimwidth((string) $medicationName, 0, 70, '...')
                                            : (strlen((string) $medicationName) > 67 ? substr((string) $medicationName, 0, 67) . '...' : (string) $medicationName);
                                        $prescriptionMeta = [];
                                        $prescriptionMeta[] = [
                                            'icon' => 'fas fa-pills',
                                            'text' => $prescriptionPreviewName,
                                        ];
                                        if (!empty($prescription['prescribed_date'])) {
                                            $prescriptionMeta[] = [
                                                'icon' => 'fas fa-calendar-day',
                                                'text' => formatDate($prescription['prescribed_date']),
                                            ];
                                        }
                                        
                                        $timeline[] = [
                                            'type' => 'prescription',
                                            'date' => $prescription['prescribed_date'],
                                            'title' => lang('UI.patientTimelinePrescriptionTitle', ['name' => $medicationName]),
                                            'description' => $prescription['instructions'] ?? lang('UI.patientNoInstructions'),
                                            'status' => $prescription['status'],
                                            'status_label' => $prescriptionStatus,
                                            'id' => $prescription['id'],
                                            'icon' => 'fas fa-prescription',
                                            'color' => 'orange',
                                            'url' => workflow_context_url('prescription/' . $prescription['id'], $patientContextReturnTo),
                                            'meta' => $prescriptionMeta,
                                        ];
                                    }
                                    
                                    // Add treatment plans / contracts
                                    foreach (($contracts ?? []) as $contract) {
                                        $contractStatus = ucfirst((string) ($contract['status'] ?? lang('UI.patientUnknown')));
                                        $contractReference = (string) ($contract['id'] ?? '');
                                        $contractMeta = [];
                                        if (isset($contract['total_cost'])) {
                                            $contractMeta[] = [
                                                'icon' => 'fas fa-file-signature',
                                                'text' => formatCurrency((float) $contract['total_cost']),
                                            ];
                                        }
                                        if (!empty($contract['currency'])) {
                                            $contractMeta[] = [
                                                'icon' => 'fas fa-coins',
                                                'text' => (string) $contract['currency'],
                                            ];
                                        }
                                        $timeline[] = [
                                            'type' => 'contract',
                                            'date' => $contract['created_at'] ?? null,
                                            'title' => lang('UI.patientTimelineContractTitle', ['reference' => ($contractReference !== '' ? $contractReference : lang('UI.patientUnknown'))]),
                                            'description' => $contract['notes'] ?? lang('UI.patientTreatmentPlanTimelineDescription'),
                                            'status' => $contract['status'] ?? null,
                                            'status_label' => $contractStatus,
                                            'id' => $contract['id'],
                                            'icon' => 'fas fa-file-signature',
                                            'color' => 'amber',
                                            'url' => workflow_context_url('contracts/' . $contract['id'], $patientContextReturnTo),
                                            'meta' => $contractMeta,
                                        ];
                                    }

                                    $timelineImagingCategories = $attachment_category_options['Dental Imaging'] ?? [];
                                    foreach (($attachments ?? []) as $attachment) {
                                        $attachmentCategory = (string) ($attachment['category'] ?? '');
                                        if (!in_array($attachmentCategory, $timelineImagingCategories, true)) {
                                            continue;
                                        }

                                        $timeline[] = [
                                            'type' => 'imaging',
                                            'date' => $attachment['created_at'] ?? null,
                                            'title' => lang('UI.patientTimelineImagingTitle', ['name' => ($attachmentCategory !== '' ? $attachmentCategory : lang('UI.patientLatestImagingTitle'))]),
                                            'description' => $attachment['notes'] ?? ($attachment['original_name'] ?? lang('UI.patientAttachmentFallback')),
                                            'status' => null,
                                            'status_label' => lang('UI.patientRecorded'),
                                            'id' => $attachment['id'],
                                            'icon' => 'fas fa-x-ray',
                                            'color' => 'slate',
                                            'url' => base_url('patient/' . $patient['id'] . '?tab=attachments'),
                                            'meta' => [
                                                [
                                                    'icon' => 'fas fa-file-image',
                                                    'text' => $attachment['original_name'] ?? lang('UI.patientAttachmentFallback'),
                                                ],
                                            ],
                                        ];
                                    }

                                    // Add financial transactions
                                    foreach ($finances as $finance) {
                                        $financeStatus = ucfirst((string) ($finance['payment_status'] ?? lang('UI.patientUnknown')));
                                        $financeAmount = $finance['total_amount'] ?? (($finance['amount'] ?? 0) - ($finance['discount_amount'] ?? 0) + ($finance['tax_amount'] ?? 0));
                                        $financeMeta = [];
                                        $financeMeta[] = [
                                            'icon' => 'fas fa-dollar-sign',
                                            'text' => formatCurrency((float) $financeAmount),
                                        ];
                                        if (!empty($finance['payment_method'])) {
                                            $financeMeta[] = [
                                                'icon' => 'fas fa-wallet',
                                                'text' => ucfirst(str_replace('_', ' ', (string) $finance['payment_method'])),
                                            ];
                                        }
                                        $timeline[] = [
                                            'type' => 'finance',
                                            'date' => $finance['created_at'],
                                            'title' => lang('UI.patientTimelinePaymentTitle', ['type' => ($finance['transaction_type'] ?? lang('UI.patientUnknown'))]),
                                            'description' => $finance['description'] ?? lang('UI.patientNoDescription'),
                                            'status' => $finance['payment_status'],
                                            'status_label' => $financeStatus,
                                            'id' => $finance['id'],
                                            'icon' => 'fas fa-dollar-sign',
                                            'color' => 'green',
                                            'url' => workflow_context_url('finance/' . $finance['id'], $patientContextReturnTo),
                                            'meta' => $financeMeta,
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

                                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.95fr)] mb-6">
                                    <section class="rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.30)]">
                                        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50/70 px-5 py-4">
                                            <h3 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientImagingWorkspaceLatestTitle')) ?></h3>
                                            <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                        </div>
                                        <div class="space-y-4 p-5">
                                            <?php if (!empty($latestImagingAttachment)): ?>
                                                <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-950">
                                                    <div class="aspect-[16/9] flex items-center justify-center">
                                                        <?php if (!empty($latestImagingAttachment) && $latestImagingIsPreviewable && !empty($latestImagingPreviewSrc)): ?>
                                                            <img src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="h-full w-full object-contain">
                                                        <?php elseif (!empty($latestImagingAttachment) && $latestImagingIsPreviewable): ?>
                                                            <img src="<?= esc($latestImagingViewUrl) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="h-full w-full object-contain">
                                                        <?php else: ?>
                                                            <div class="px-6 text-center text-sm text-slate-300"><?= esc(lang('UI.patientImagingPreviewUnavailable')) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                    <div>
                                                        <h4 class="text-base font-bold text-slate-900"><?= esc($latestImagingAttachment['category'] ?? lang('UI.patientLatestImagingTitle')) ?></h4>
                                                        <p class="mt-1 text-sm text-slate-500"><?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></p>
                                                        <p class="mt-1 text-xs text-slate-400"><?= esc(!empty($latestImagingAttachment['created_at']) ? formatDateTime($latestImagingAttachment['created_at']) : '--') ?></p>
                                                    </div>
                                                    <div class="flex flex-wrap gap-2">
                                                        <?php if (!empty($latestImagingViewUrl) && $latestImagingIsPreviewable): ?>
                                                            <a href="<?= esc($latestImagingViewUrl) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                                                                <i class="fas fa-eye"></i>
                                                                <span><?= esc(lang('UI.patientOpenImaging')) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (!empty($latestImagingOpenUrl)): ?>
                                                            <a href="<?= esc($latestImagingOpenUrl) ?>" class="inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                                                                <i class="fas fa-download"></i>
                                                                <span><?= esc(lang('UI.patientImagingWorkspaceOpenFile')) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-5 py-16 text-center">
                                                    <i class="fas fa-x-ray text-3xl text-slate-300"></i>
                                                    <p class="mt-4 text-sm text-slate-500"><?= esc(lang('UI.patientNoImagingPreview')) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </section>

                                    <aside>
                                        <section class="rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.30)]">
                                            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-cyan-50/70 px-5 py-4">
                                                <h3 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientUploadFiles')) ?></h3>
                                                <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientAttachmentsSubtitle', ['max' => ($maxFileMb === null ? lang('UI.patientUnlimited') : ((int) $maxFileMb . 'MB'))])) ?></p>
                                            </div>
                                            <div class="p-5">
                                                <?php $selectedAttachmentCategory = old('category'); ?>
                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/upload') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                                                    <?= csrf_field() ?>
                                                    <div>
                                                        <label class="mb-2 block text-sm font-medium text-slate-700"><?= esc(lang('UI.patientUploadFiles')) ?></label>
                                                        <input type="file" name="attachment[]" required multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip" class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 file:me-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                                                    </div>
                                                    <div>
                                                        <label class="mb-2 block text-sm font-medium text-slate-700"><?= esc(lang('UI.patientAttachmentCategory')) ?></label>
                                                        <select name="category" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200">
                                                            <option value=""><?= esc(lang('UI.patientAttachmentCategoryPlaceholder')) ?></option>
                                                            <?php foreach (($attachment_category_options ?? []) as $groupLabel => $groupOptions): ?>
                                                                <optgroup label="<?= esc($groupLabel) ?>">
                                                                    <?php foreach ($groupOptions as $optionLabel): ?>
                                                                        <option value="<?= esc($optionLabel) ?>" <?= $selectedAttachmentCategory === $optionLabel ? 'selected' : '' ?>><?= esc($optionLabel) ?></option>
                                                                    <?php endforeach; ?>
                                                                </optgroup>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="mb-2 block text-sm font-medium text-slate-700"><?= esc(lang('UI.patientNotes')) ?></label>
                                                        <textarea name="notes" rows="4" placeholder="<?= esc(lang('UI.patientAttachmentNotesPlaceholder')) ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"></textarea>
                                                    </div>
                                                    <div class="flex items-center justify-end">
                                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                                            <i class="fas fa-upload"></i>
                                                            <span><?= esc(lang('UI.patientUpload')) ?></span>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </section>
                                    </aside>
                                </div>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-800"><?= esc(lang('UI.patientAttachmentHistory')) ?></h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="patient-record-table" data-record-type="attachments" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientFile')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientCategory')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientNotes')) ?></th>
                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($attachments)): ?>
                                                    <?php foreach ($attachments as $attachment): ?>
                                                        <?php $attachmentIsPreviewable = strpos((string) ($attachment['mime_type'] ?? ''), 'image/') === 0; ?>
                                                        <tr class="patient-record-row" data-date="<?= !empty($attachment['created_at']) ? esc(date('Y-m-d', strtotime($attachment['created_at']))) : '' ?>" data-href="<?= esc($attachmentIsPreviewable ? base_url('file/inline/' . $attachment['id']) : base_url('file/download/' . $attachment['id'])) ?>">
                                                            <td class="whitespace-nowrap">
                                                                <?= !empty($attachment['created_at']) ? formatDateTime($attachment['created_at']) : '--' ?>
                                                            </td>
                                                            <td>
                                                                <div class="patient-record-main">
                                                                    <div class="patient-record-title"><?= esc($attachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></div>
                                                                    <div class="patient-record-subtitle"><?= number_format(($attachment['file_size'] ?? 0) / 1024, 1) ?> KB</div>
                                                                </div>
                                                            </td>
                                                            <td class="text-slate-600">
                                                                <?= esc($attachment['category'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td class="text-slate-600">
                                                                <?= esc($attachment['notes'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td class="text-right">
                                                                <div class="patient-table-actions">
                                                                <?php if ($attachmentIsPreviewable): ?>
                                                                <a href="<?= base_url('file/inline/' . $attachment['id']) ?>" target="_blank" rel="noopener"
                                                                   class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                                <a href="<?= base_url('file/download/' . $attachment['id']) ?>" 
                                                                   class="patient-table-action" title="<?= esc(lang('UI.patientDownload')) ?>">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/' . $attachment['id'] . '/delete') ?>" method="post" class="inline">
                                                                    <?= csrf_field() ?>
                                                                    <button type="submit" class="patient-table-action patient-table-action-danger" title="<?= esc(lang('UI.patientDelete')) ?>" onclick="return confirm('<?= esc(lang('UI.patientDeleteAttachmentConfirm'), 'js') ?>');">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                                </div>
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

                                <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-4">
                                    <div class="px-4 py-2.5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientContractsTitle')) ?></h3>
                                            <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientContractsSubtitle')) ?></p>
                                        </div>
                                        <div class="flex flex-col items-start gap-2 md:items-end">
                                            <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientContractsHint')) ?></p>
                                            <?php if (has_permission('patients', 'edit')): ?>
                                                <a href="<?= workflow_context_url('contracts/create/' . $patient['id'], $patientContextReturnTo) ?>"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                                    <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientCreateTreatmentPlan')) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-800"><?= esc(lang('UI.patientContractHistory')) ?></h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="patient-record-table" data-record-type="contracts" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientExam')) ?></th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientTotal')) ?></th>
                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($contracts)): ?>
                                                    <?php foreach ($contracts as $contract): ?>
                                                        <tr class="patient-record-row" data-date="<?= !empty($contract['created_at']) ? esc(date('Y-m-d', strtotime($contract['created_at']))) : '' ?>" data-href="<?= esc(workflow_context_url('contracts/' . $contract['id'], $patientContextReturnTo)) ?>">
                                                            <td class="whitespace-nowrap text-slate-600">
                                                                <?= !empty($contract['created_at']) ? formatDateTime($contract['created_at']) : '--' ?>
                                                            </td>
                                                            <td>
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= ($contract['status'] ?? 'draft') === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                                                    <?= esc(ucfirst($contract['status'] ?? 'draft')) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-slate-700">
                                                                <?= !empty($contract['examination_id']) ? esc($contract['examination_id']) : '--' ?>
                                                            </td>
                                                            <td class="text-slate-700">
                                                                <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                                                            </td>
                                                    <td class="text-right">
                                                        <div class="patient-table-actions">
                                                        <a href="<?= workflow_context_url('contracts/' . $contract['id'], $patientContextReturnTo) ?>" 
                                                           class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if (($contract['status'] ?? 'draft') === 'draft'): ?>
                                                            <a href="<?= workflow_context_url('contracts/' . $contract['id'] . '/edit', $patientContextReturnTo) ?>" 
                                                               class="patient-table-action" title="<?= esc(lang('UI.patientEdit')) ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="<?= base_url('contracts/' . $contract['id'] . '/delete') ?>" method="post" class="inline">
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="patient-table-action patient-table-action-danger" title="<?= esc(lang('UI.patientDelete')) ?>" onclick="return confirm('<?= esc(lang('UI.patientDeleteContractConfirm'), 'js') ?>');">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <a href="<?= base_url('contracts/' . $contract['id'] . '/print') ?>" 
                                                           class="patient-table-action" title="<?= esc(lang('UI.patientPrint')) ?>">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        </div>
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
                            <div class="bg-white rounded-xl border border-gray-200 shadow-lg mb-4">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('UI.patientExaminations')) ?></h3>
                                        </div>
                                        <span class="text-xs text-gray-400"><?= count($examinations) ?> <?= esc(lang('UI.patientExams')) ?></span>
                                    </div>
                                </div>
                                <?php if (!empty($examinations)): ?>
                                    <div class="overflow-x-auto">
                                        <table class="w-full min-w-full" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                        <div class="flex items-center space-x-2"><i class="fas fa-calendar text-blue-600"></i><span><?= esc(lang('UI.patientDate')) ?></span></div>
                                                    </th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                        <div class="flex items-center space-x-2"><i class="fas fa-stethoscope text-blue-600"></i><span><?= esc(lang('UI.patientType')) ?></span></div>
                                                    </th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                        <div class="flex items-center space-x-2"><i class="fas fa-align-left text-blue-600"></i><span><?= esc(lang('UI.patientChiefComplaint')) ?></span></div>
                                                    </th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                        <div class="flex items-center space-x-2"><i class="fas fa-circle-info text-blue-600"></i><span><?= esc(lang('UI.patientStatus')) ?></span></div>
                                                    </th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                        <div class="flex items-center space-x-2"><i class="fas fa-cogs text-blue-600"></i></div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <?php foreach ($examinations as $examination): ?>
                                                    <tr class="patient-record-row hover:bg-gray-50 transition-colors cursor-pointer" data-href="<?= esc(workflow_context_url('examination/' . $examination['id'], $patientContextReturnTo)) ?>">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                            <?= formatDate($examination['examination_date']) ?>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <div class="text-sm font-medium text-gray-900"><?= ucfirst($examination['examination_type'] ?? lang('UI.patientGeneral')) ?></div>
                                                            <div class="text-xs text-gray-500"><?= esc(lang('UI.patientExam')) ?></div>
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                                            <?= $examination['chief_complaint'] ? esc(substr($examination['chief_complaint'], 0, 60) . (strlen($examination['chief_complaint']) > 60 ? '...' : '')) : '<span class="text-gray-400">' . esc(lang('UI.patientNoComplaint')) . '</span>' ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $examination['status'] == 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                                                <?= ucfirst($examination['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <a href="<?= workflow_context_url('examination/' . $examination['id'], $patientContextReturnTo) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors" title="<?= esc(lang('UI.patientView')) ?>">
                                                                <i class="fas fa-eye text-xs"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-10">
                                        <i class="fas fa-stethoscope text-gray-300 text-4xl mb-3"></i>
                                        <p class="text-sm text-gray-400"><?= esc(lang('UI.patientNoExaminations')) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Appointments Tab -->
                        <div id="appointments-content" class="tab-content hidden">
                            <div class="bg-white rounded-xl border border-gray-200 shadow-lg mb-4">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('UI.patientAppointments')) ?></h3>
                                        </div>
                                        <span class="text-xs text-gray-400"><?= count($appointments) ?> <?= esc(lang('UI.patientAppts')) ?></span>
                                    </div>
                                </div>
                                <?php if (!empty($appointments)): ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-calendar text-purple-600"></i><span><?= esc(lang('UI.patientDateTime')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-tag text-purple-600"></i><span><?= esc(lang('UI.patientType')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-clock text-purple-600"></i><span><?= esc(lang('UI.patientDuration')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-circle-info text-purple-600"></i><span><?= esc(lang('UI.patientStatus')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-cogs text-purple-600"></i></div></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <?php foreach ($appointments as $apt): ?>
                                                    <tr class="patient-record-row hover:bg-gray-50 transition-colors cursor-pointer" data-href="<?= esc(workflow_context_url('appointment/' . $apt['id'], $patientContextReturnTo)) ?>">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= formatDate($apt['appointment_date']) ?> <?= esc(lang('UI.patientAt')) ?> <?= formatTime($apt['appointment_time']) ?></td>
                                                        <td class="px-6 py-4"><div class="text-sm font-medium text-gray-900"><?= ucfirst($apt['appointment_type'] ?? lang('UI.patientGeneral')) ?></div><div class="text-xs text-gray-500"><?= esc($apt['chair_name'] ?? lang('UI.appointmentChairDefault')) ?></div></td>
                                                        <td class="px-6 py-4 text-sm text-gray-700"><?= $apt['duration'] ?? lang('UI.patientNa') ?> <?= esc(lang('UI.patientMinutes')) ?></td>
                                                        <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ['scheduled'=>'bg-blue-100 text-blue-700','confirmed'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-gray-100 text-gray-600','cancelled'=>'bg-rose-100 text-rose-700','no_show'=>'bg-amber-100 text-amber-700'][$apt['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= ucfirst($apt['status']) ?></span></td>
                                                        <td class="px-6 py-4"><a href="<?= workflow_context_url('appointment/' . $apt['id'], $patientContextReturnTo) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-purple-600 hover:bg-purple-50 hover:text-purple-700 transition-colors"><i class="fas fa-eye text-xs"></i></a></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-10"><i class="fas fa-calendar text-gray-300 text-4xl mb-3"></i><p class="text-sm text-gray-400"><?= esc(lang('UI.patientNoAppointments')) ?></p></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Treatments Tab -->
                        <div id="treatments-content" class="tab-content hidden">
                            <div class="bg-white rounded-xl border border-gray-200 shadow-lg mb-4">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-pink-500 rounded-full"></div>
                                            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('UI.patientTreatments')) ?></h3>
                                        </div>
                                        <span class="text-xs text-gray-400"><?= count($treatments) ?> <?= esc(lang('UI.patientTreatments')) ?></span>
                                    </div>
                                </div>
                                <div class="px-6 py-3 border-b border-gray-100 bg-white">
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2">
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700"><?= esc(lang('UI.patientUnbilledWork')) ?></p>
                                            <p class="mt-1 text-sm font-bold text-amber-900"><?= esc($openClinicalChargeCount) ?> &middot; <span dir="ltr"><?= formatCurrency($openClinicalChargeTotal) ?></span></p>
                                        </div>
                                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2">
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700"><?= esc(lang('UI.patientBilledPaid')) ?></p>
                                            <p class="mt-1 text-sm font-bold text-emerald-900"><?= esc($paidBilledTreatmentCount) ?></p>
                                        </div>
                                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2">
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700"><?= esc(lang('UI.patientBilledWithBalance')) ?></p>
                                            <p class="mt-1 text-sm font-bold text-blue-900"><?= esc($unpaidBilledTreatmentCount) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($treatments)): ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-tooth text-pink-600"></i><span><?= esc(lang('UI.patientTreatment')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-tag text-pink-600"></i><span><?= esc(lang('UI.patientTooth')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-file-invoice-dollar text-pink-600"></i><span><?= esc(lang('UI.patientBilling')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-circle-info text-pink-600"></i><span><?= esc(lang('UI.patientStatus')) ?></span></div></th>
                                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><div class="flex items-center gap-2"><i class="fas fa-cogs text-pink-600"></i></div></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <?php foreach ($treatments as $trt): ?>
                                                    <?php $rowBilling = $treatmentBillingMeta($trt); ?>
                                                    <tr class="patient-record-row hover:bg-gray-50 transition-colors cursor-pointer" data-href="<?= esc(workflow_context_url('treatment/' . $trt['id'], $patientContextReturnTo)) ?>">
                                                        <td class="px-6 py-4"><div class="text-sm font-medium text-gray-900"><?= esc($trt['treatment_name'] ?? lang('UI.patientNa')) ?></div><div class="text-xs text-gray-500"><?= ucfirst($trt['treatment_type'] ?? lang('UI.patientGeneral')) ?></div></td>
                                                        <td class="px-6 py-4 text-sm text-gray-700"><?= $trt['tooth_number'] ?? '—' ?></td>
                                                        <td class="px-6 py-4 text-sm">
                                                            <div class="font-semibold text-gray-900" dir="ltr"><?= esc($rowBilling['amount_text']) ?></div>
                                                            <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold <?= esc($rowBilling['status_class']) ?>">
                                                                <?= esc($rowBilling['status_text']) ?>
                                                            </span>
                                                            <?php if (!empty($rowBilling['finance_id'])): ?>
                                                                <a href="<?= esc(workflow_context_url('finance/' . (int) $rowBilling['finance_id'], $patientContextReturnTo)) ?>" class="mt-1 block text-xs font-semibold text-emerald-700 hover:underline">
                                                                    <?= esc($rowBilling['invoice_number'] !== '' ? $rowBilling['invoice_number'] : lang('Finance.invoice')) ?>
                                                                </a>
                                                                <div class="mt-2 grid gap-1 text-xs text-slate-600">
                                                                    <?php if ($rowBilling['invoice_status_text'] !== ''): ?>
                                                                        <span class="inline-flex w-fit items-center rounded-full px-2 py-0.5 font-semibold <?= esc($rowBilling['invoice_status_class']) ?>">
                                                                            <?= esc($rowBilling['invoice_status_text']) ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                    <span><?= esc(lang('UI.patientPaidShort')) ?>: <strong dir="ltr"><?= esc($rowBilling['paid_text']) ?></strong></span>
                                                                    <span class="<?= $rowBilling['has_balance'] ? 'text-amber-700' : 'text-emerald-700' ?>"><?= esc(lang('UI.patientBalanceShort')) ?>: <strong dir="ltr"><?= esc($rowBilling['balance_text']) ?></strong></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ['active'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-rose-100 text-rose-700','on_hold'=>'bg-amber-100 text-amber-700'][$trt['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= ucfirst($trt['status']) ?></span></td>
                                                        <td class="px-6 py-4"><a href="<?= workflow_context_url('treatment/' . $trt['id'], $patientContextReturnTo) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-pink-600 hover:bg-pink-50 hover:text-pink-700 transition-colors"><i class="fas fa-eye text-xs"></i></a></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-10"><i class="fas fa-tooth text-gray-300 text-4xl mb-3"></i><p class="text-sm text-gray-400"><?= esc(lang('UI.patientNoTreatments')) ?></p></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Prescriptions Tab -->
                        <div id="prescriptions-content" class="tab-content hidden">
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-4">
                                <div class="px-4 py-2.5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientPrescriptions')) ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('UI.patientPrescriptionsSubtitle')) ?></p>
                                    </div>
                                    <?php if (has_permission('prescriptions', 'create')): ?>
                                        <a href="<?= workflow_context_url('prescription/create?patient_id=' . $patient['id'], $patientContextReturnTo) ?>"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientCreatePrescription')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($prescriptions)): ?>
                                <div class="overflow-x-auto">
                                      <table class="patient-record-table" data-record-type="prescriptions" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                          <thead>
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientMedication')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDosage')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientFrequency')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientPrescribedDate')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                            </tr>
                                        </thead>
                                          <tbody>
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
                                                  <tr class="patient-record-row" data-href="<?= esc(workflow_context_url('prescription/' . $prescription['id'], $patientContextReturnTo)) ?>">
                                                      <td>
                                                          <div class="flex items-center space-x-2">
                                                              <i class="fas fa-pills text-teal-600 text-xs"></i>
                                                              <div class="patient-record-main">
                                                                  <div class="patient-record-title"><?= htmlspecialchars($medicationName) ?></div>
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <?= htmlspecialchars($dosage) ?>
                                                      </td>
                                                      <td>
                                                          <?= htmlspecialchars($frequency) ?>
                                                      </td>
                                                      <td class="whitespace-nowrap">
                                                          <?= formatDate($prescription['prescribed_date']) ?>
                                                      </td>
                                                      <td class="whitespace-nowrap">
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
                                                      <td class="text-right">
                                                          <div class="patient-table-actions">
                                                          <a href="<?= workflow_context_url('prescription/' . $prescription['id'], $patientContextReturnTo) ?>" 
                                                             class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><i class="fas fa-eye"></i></a>
                                                          </div>
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

                        <!-- Financials Tab -->
                        <div id="finances-content" class="tab-content hidden" role="tabpanel" aria-labelledby="finances-tab" hidden>
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.patientTotalBilled')) ?></p>
                                        <p class="mt-2 text-2xl font-bold text-slate-900"><?= formatCurrency($totalCharges) ?></p>
                                    </div>
                                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= esc(lang('UI.patientTotalPaid')) ?></p>
                                        <p class="mt-2 text-2xl font-bold text-emerald-900"><?= formatCurrency($totalCollected) ?></p>
                                    </div>
                                    <div class="rounded-xl border <?= $outstandingBalance > 0.009 ? 'border-amber-200 bg-amber-50' : 'border-sky-200 bg-sky-50' ?> p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-wide <?= $outstandingBalance > 0.009 ? 'text-amber-700' : 'text-sky-700' ?>"><?= esc(lang('UI.patientOutstandingBalance')) ?></p>
                                        <p class="mt-2 text-2xl font-bold <?= $outstandingBalance > 0.009 ? 'text-amber-900' : 'text-sky-900' ?>"><?= $outstandingBalance > 0.009 ? formatCurrency($outstandingBalance) : esc(lang('UI.patientNoBalanceDue')) ?></p>
                                    </div>
                                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700"><?= esc(lang('Finance.refunds')) ?></p>
                                        <p class="mt-2 text-2xl font-bold text-rose-900"><?= formatCurrency($totalRefunded) ?></p>
                                    </div>
                                </div>
                                <?= view('components/money_trail_panel', [
                                    'money_trail' => $money_trail ?? [],
                                    'is_rtl' => $isRtl,
                                    'return_to' => $patientContextReturnTo,
                                ]) ?>
                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-4 py-2.5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('UI.patientFinancialRecords')) ?></h3>
                                        <p class="text-xs text-gray-500 mt-1"><?= esc($isRtl ? 'يعرض هذا السجل المالي الفواتير والتحصيلات والاستردادات والتسويات والرصيد الحالي للمريض في مكان واحد.' : lang('UI.patientFinancialRecordsSubtitle')) ?></p>
                                    </div>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center px-4 py-2 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors duration-200">
                                                <i class="fas fa-chart-line mr-2"></i><?= esc(lang('TenantMisc.openFinanceReports')) ?>
                                            </a>
                                            <?php if (has_permission('finance', 'create')): ?>
                                                <a href="<?= workflow_context_url('finance/create?patient_id=' . $patient['id'], $patientContextReturnTo) ?>"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                                    <i class="fas fa-plus mr-2"></i><?= esc(lang('UI.patientCreateInvoice')) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($financialLedgerRows)): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                                        <div class="overflow-x-auto">
                                            <table class="patient-record-table" data-record-type="finances" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead>
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientType')) ?></th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Finance.detailsReference')) ?></th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Finance.amount')) ?></th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Finance.method')) ?></th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($financialLedgerRows as $ledgerRow): ?>
                                                        <?php
                                                        $statusColors = [
                                                            'paid' => 'bg-green-100 text-green-800',
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'partial' => 'bg-blue-100 text-blue-800',
                                                            'overdue' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                                            'recorded' => 'bg-slate-100 text-slate-700',
                                                        ];
                                                        $rowAmount = (float) ($ledgerRow['amount'] ?? 0);
                                                        $amountClass = $rowAmount < 0 ? 'text-rose-700' : ($rowAmount > 0 ? 'text-emerald-700' : 'text-slate-700');
                                                        $colorClass = $statusColors[$ledgerRow['status_key']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <tr class="patient-record-row" data-href="<?= esc($ledgerRow['action_url']) ?>" data-date="<?= esc($ledgerRow['document_date_iso']) ?>">
                                                            <td class="whitespace-nowrap">
                                                                <?= !empty($ledgerRow['document_date']) ? formatDate($ledgerRow['document_date']) : esc(lang('UI.patientNa')) ?>
                                                            </td>
                                                            <td>
                                                                <div class="patient-record-main">
                                                                    <div class="patient-record-title"><?= esc($ledgerRow['record_label']) ?></div>
                                                                    <div class="patient-record-subtitle"><?= esc($ledgerRow['record_note'] !== '' ? $ledgerRow['record_note'] : lang('UI.patientNa')) ?></div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="patient-record-main">
                                                                    <div class="patient-record-title"><?= esc($ledgerRow['reference']) ?></div>
                                                                </div>
                                                            </td>
                                                            <td class="font-semibold <?= $amountClass ?>">
                                                                <?= ($rowAmount < 0 ? '- ' : '') . formatCurrency(abs($rowAmount)) ?>
                                                            </td>
                                                            <td><?= esc($ledgerRow['method_label']) ?></td>
                                                            <td class="whitespace-nowrap">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                                    <?= esc($ledgerRow['status_label']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-right">
                                                                <div class="patient-table-actions">
                                                                    <a href="<?= esc($ledgerRow['action_url']) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><i class="fas fa-eye"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-file-invoice text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500"><?= esc(lang('UI.patientNoFinancialRecords')) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
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
        padding: 0.48rem 0.74rem;
        font-size: 0.76rem;
        font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        text-align: center;
    }

    .patient-header-action:hover {
        transform: translateY(-1px);
    }

    .patient-header-pill-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .patient-header-pill-grid .patient-hero-chip,
    .patient-header-snapshot-pill {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        min-width: 0;
        width: 100%;
        max-width: none;
        min-height: 3.05rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        padding: 0.38rem 0.68rem 0.38rem 0.42rem;
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .patient-header-snapshot-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.95rem;
        height: 1.95rem;
        flex-shrink: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
    }

    .patient-header-pill-grid .patient-hero-chip-value,
    .patient-header-pill-grid .patient-hero-chip-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (min-width: 768px) {
        .patient-header-pill-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        .patient-header-pill-grid {
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
    }

    .patient-header-snapshot-card-calm {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.28), rgba(13, 148, 136, 0.18));
    }

    .patient-header-snapshot-card-alert {
        background: linear-gradient(135deg, rgba(244, 63, 94, 0.34), rgba(190, 18, 60, 0.2));
    }

    .patient-header-snapshot-card-blue {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.30), rgba(37, 99, 235, 0.18));
    }

    .patient-header-snapshot-card-due {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.34), rgba(217, 119, 6, 0.2));
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
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03), 0 20px 48px -24px rgba(0,0,0,0.12);
        overflow: hidden;
    }

    /* Gender-themed shell — concentrated tints from hero */
    .patient-shell[data-hero-theme="patient-hero-card-male"] {
        background: linear-gradient(180deg, #bfd5f7 0%, #dae6f9 30%, #eef3fb 100%);
        border-color: #87abdd;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 12px rgba(15,63,144,0.10), 0 20px 48px -24px rgba(15,63,144,0.28);
    }

    .patient-shell[data-hero-theme="patient-hero-card-female"] {
        background: linear-gradient(180deg, #f0c4de 0%, #f6d8ea 30%, #fbeef5 100%);
        border-color: #e09ec3;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 12px rgba(138,36,90,0.10), 0 20px 48px -24px rgba(138,36,90,0.28);
    }

    .patient-shell[data-hero-theme="patient-hero-card-neutral"] {
        background: linear-gradient(180deg, #b8ddd6 0%, #d0eae5 30%, #eaf5f2 100%);
        border-color: #88c9be;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 12px rgba(15,118,110,0.10), 0 20px 48px -24px rgba(15,118,110,0.28);
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
        letter-spacing: -0.012em;
        color: #172554;
    }

    .patient-panel-copy,
    .patient-shell-copy {
        margin-top: 0.35rem;
        font-size: 0.79rem;
        line-height: 1.45;
        color: #475569;
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
        border: 1px solid rgba(148, 163, 184, 0.28);
        background: rgba(255, 255, 255, 0.96);
        padding: 0.28rem 0.6rem;
        font-size: 0.68rem;
        font-weight: 600;
        color: #1e293b;
        box-shadow: 0 8px 18px -18px rgba(15, 23, 42, 0.28);
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
        position: sticky;
        top: 0;
        z-index: 30;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.625rem 1rem 0.75rem;
        background: #f1f5f9;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    /* Tab nav — themed background matching shell */
    .patient-shell[data-hero-theme="patient-hero-card-male"] .patient-shell-nav {
        background: #e2ecf9;
        border-bottom-color: #c5d5ed;
    }
    .patient-shell[data-hero-theme="patient-hero-card-female"] .patient-shell-nav {
        background: #f3dfeb;
        border-bottom-color: #e6c4d6;
    }
    .patient-shell[data-hero-theme="patient-hero-card-neutral"] .patient-shell-nav {
        background: #d4eae5;
        border-bottom-color: #bcd9d3;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        overflow: hidden;
        min-height: 2.4rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .tab-button.active,
    .tab-button.active:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
        box-shadow: 0 1px 3px rgba(37,99,235,0.12);
    }

    /* Gender-themed active tab — male (blue) */
    .patient-shell[data-hero-theme="patient-hero-card-male"] .tab-button.active {
        border-color: #1d4ed8;
        background: #dbeafe;
        color: #1e40af;
        box-shadow: 0 1px 3px rgba(29,78,216,0.2);
    }

    /* Gender-themed active tab — female (rose) */
    .patient-shell[data-hero-theme="patient-hero-card-female"] .tab-button.active {
        border-color: #be185d;
        background: #fce7f3;
        color: #9d174d;
        box-shadow: 0 1px 3px rgba(190,24,93,0.2);
    }

    /* Gender-themed active tab — neutral (teal) */
    .patient-shell[data-hero-theme="patient-hero-card-neutral"] .tab-button.active {
        border-color: #0d9488;
        background: #ccfbf1;
        color: #115e59;
        box-shadow: 0 1px 3px rgba(13,148,136,0.2);
    }

    .tab-button:hover:not(.active) {
        color: #334155;
        border-color: #cbd5e1;
        background: #f8fafc;
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

    .patient-shell[data-hero-theme="patient-hero-card-male"] .tab-button.active .tab-count { background: rgba(29, 78, 216, 0.14); }
    .patient-shell[data-hero-theme="patient-hero-card-female"] .tab-button.active .tab-count { background: rgba(190, 24, 93, 0.14); }
    .patient-shell[data-hero-theme="patient-hero-card-neutral"] .tab-button.active .tab-count { background: rgba(13, 148, 136, 0.14); }

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

    .patient-record-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        direction: inherit;
    }

    .patient-record-table thead th {
        padding: 0.85rem 1.25rem;
        font-size: 0.72rem;
        line-height: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 700;
        color: #64748b;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
        text-align: start !important;
        white-space: nowrap;
    }

    .patient-record-table tbody td {
        padding: 1rem 1.25rem;
        font-size: 0.9rem;
        line-height: 1.35rem;
        color: #0f172a;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        background: #ffffff;
        text-align: start;
    }

    .patient-record-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .patient-record-row {
        transition: background-color 0.18s ease;
    }

    .patient-record-row:nth-child(even) td {
        background: rgba(248,250,252,0.6);
    }

    .patient-record-row[data-href] {
        cursor: pointer;
    }

    .patient-record-row[data-href]:hover td {
        background: #eff6ff;
    }

    .patient-record-row[data-href]:hover .patient-record-title {
        color: #1d4ed8;
    }

    /* Table card backgrounds — neutral tint on header, clean body */
    .patient-shell .tab-content > .bg-white.rounded-xl {
        background: linear-gradient(180deg, #fafbfc 0%, #fff 25%) !important;
    }
    .patient-shell .tab-content thead th {
        background: #f8f9fa;
        color: #64748b;
        border-bottom-color: #e2e8f0;
    }
    .patient-shell .tab-content thead th i {
        color: #94a3b8;
    }

    .patient-record-table th {
        font-size: 0.6875rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.625rem 0.75rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .patient-record-table td {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .patient-table-sort {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.65rem;
        border: 0;
        background: transparent;
        padding: 0;
        font: inherit;
        color: inherit;
        cursor: pointer;
        direction: inherit;
        text-align: start;
    }

    .patient-table-sort-label {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-width: 0;
    }

    .patient-table-sort-label i:first-child {
        color: #94a3b8;
        font-size: 0.78rem;
        flex-shrink: 0;
    }

    .patient-table-sort-label span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .patient-table-sort > i:last-child {
        color: #cbd5e1;
        transition: color 0.18s ease;
        flex-shrink: 0;
        margin-inline-start: auto;
    }

    .patient-table-sort:hover > i:last-child,
    .patient-table-sort.is-active > i:last-child {
        color: #2563eb;
    }

    .patient-record-table th:last-child,
    .patient-record-table td:last-child {
        text-align: end;
    }

    .patient-record-main {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }

    .patient-record-title {
        font-weight: 600;
        color: #0f172a;
    }

    .patient-record-subtitle {
        font-size: 0.78rem;
        line-height: 1rem;
        color: #64748b;
    }

    .patient-table-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.45rem;
    }

    .patient-table-action {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.7rem;
        border: 1px solid transparent;
        background: linear-gradient(180deg, #f8fafc, #edf5ff);
        color: #475569;
        transition: all 0.18s ease;
    }

    .patient-table-action:hover {
        background: #eff6ff;
        color: #2563eb;
        border-color: #dbeafe;
    }

    .patient-table-action-danger:hover {
        background: #fff1f2;
        color: #dc2626;
        border-color: #fecdd3;
    }

    .patient-timeline-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .patient-timeline-node {
        position: relative;
        padding-bottom: 1.15rem;
    }

    .patient-timeline-link {
        display: block;
        border: 1px solid #dbe4ef;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 18px 36px -32px rgba(15, 23, 42, 0.28);
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .patient-timeline-link:hover {
        border-color: #cbdcf9;
        background: #ffffff;
        box-shadow: 0 20px 36px -30px rgba(37, 99, 235, 0.22);
        transform: translateY(-1px);
    }

    .patient-timeline-shell {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.05rem;
    }

    .patient-timeline-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 12px 24px -20px rgba(15, 23, 42, 0.5);
    }

    .patient-timeline-main {
        min-width: 0;
        flex: 1;
        display: grid;
        gap: 0.7rem;
        align-items: center;
    }

    .patient-timeline-title {
        font-size: 0.95rem;
        line-height: 1.35rem;
        font-weight: 700;
        color: #0f172a;
    }

    .patient-timeline-date {
        margin-top: 0.15rem;
        font-size: 0.78rem;
        color: #64748b;
    }

    .patient-timeline-status {
        flex-shrink: 0;
    }

    .patient-timeline-title-block {
        min-width: 0;
    }

    .patient-timeline-description {
        font-size: 0.84rem;
        line-height: 1.45rem;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .patient-timeline-summary {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        overflow: hidden;
    }

    .patient-timeline-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        min-width: 0;
        justify-content: flex-start;
    }

    .patient-timeline-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.38rem 0.62rem;
        border-radius: 9999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: 0.74rem;
        line-height: 1rem;
        color: #475569;
    }

    @media (min-width: 1024px) {
        .patient-timeline-main {
            grid-template-columns: minmax(15rem, 1fr) minmax(18rem, 1.45fr) auto;
            align-items: center;
            column-gap: 1.1rem;
        }

        .patient-timeline-status {
            justify-self: end;
        }
    }

    html[dir="rtl"] .patient-table-sort-label,
    body[dir="rtl"] .patient-table-sort-label,
    .patient-view[dir="rtl"] .patient-table-sort-label {
        flex-direction: row-reverse;
    }

    html[dir="rtl"] .patient-table-actions,
    body[dir="rtl"] .patient-table-actions,
    .patient-view[dir="rtl"] .patient-table-actions {
        justify-content: flex-start;
    }

    html[dir="rtl"] .patient-record-table th:last-child,
    html[dir="rtl"] .patient-record-table td:last-child,
    body[dir="rtl"] .patient-record-table th:last-child,
    body[dir="rtl"] .patient-record-table td:last-child,
    .patient-view[dir="rtl"] .patient-record-table th:last-child,
    .patient-view[dir="rtl"] .patient-record-table td:last-child {
        text-align: start;
    }

    html[dir="rtl"] .patient-timeline-meta,
    body[dir="rtl"] .patient-timeline-meta,
    .patient-view[dir="rtl"] .patient-timeline-meta {
        justify-content: flex-end;
    }

    html[dir="rtl"] .patient-timeline-status,
    body[dir="rtl"] .patient-timeline-status,
    .patient-view[dir="rtl"] .patient-timeline-status {
        justify-self: start;
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm,
    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm.group {
        border-color: #dbe4ef;
        box-shadow: 0 16px 32px -28px rgba(15, 23, 42, 0.28);
    }

    .patient-section-card {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #ffffff;
        box-shadow: 0 12px 26px -24px rgba(15, 23, 42, 0.2);
        overflow: hidden;
    }

    .patient-details-layout {
        display: grid;
        gap: 0.9rem;
    }

    @media (min-width: 1200px) {
        .patient-details-layout {
            grid-template-columns: minmax(0, 1.85fr) minmax(300px, 0.9fr);
            align-items: start;
        }
    }

    .patient-details-main,
    .patient-details-sidebar {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        align-self: start;
        gap: 0.9rem;
        min-width: 0;
    }

    .patient-details-main > .patient-compact-card,
    .patient-details-sidebar > .patient-compact-card {
        height: auto;
    }

    .patient-compact-card {
        position: relative;
        border: 1px solid #dbe4ef;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
        box-shadow: 0 16px 32px -28px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    .patient-compact-card-profile {
        background:
            linear-gradient(180deg, rgba(239, 246, 255, 0.9), rgba(255, 255, 255, 0) 16%),
            linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .patient-compact-card-clinical {
        background:
            linear-gradient(180deg, rgba(240, 249, 255, 0.95), rgba(255, 255, 255, 0) 16%),
            linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .patient-compact-card-alerts {
        background:
            linear-gradient(180deg, rgba(255, 251, 235, 0.95), rgba(255, 255, 255, 0) 18%),
            linear-gradient(180deg, #ffffff 0%, #fffdf9 100%);
    }

    .patient-compact-card-history {
        background:
            linear-gradient(180deg, rgba(236, 254, 255, 0.9), rgba(255, 255, 255, 0) 18%),
            linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .patient-compact-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 1rem 1.05rem 0.85rem;
        border-bottom: 1px solid #e5edf6;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.88));
    }

    .patient-compact-title {
        font-size: 0.98rem;
        line-height: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }

    .patient-compact-copy {
        margin-top: 0.28rem;
        font-size: 0.76rem;
        line-height: 1.22rem;
        color: #64748b;
    }

    .patient-facts-split {
        display: grid;
        gap: 1rem;
        padding: 1.05rem 1.05rem 1.05rem;
    }

    .patient-facts-section {
        padding: 0.85rem 0;
    }

    .patient-facts-section + .patient-facts-section {
        border-top: 1px solid #e2e8f0;
    }

    .patient-facts-section-primary {
        padding: 0.85rem 1.05rem 1rem;
    }

    .patient-facts-section-primary .patient-facts-grid {
        gap: 0.78rem;
    }

    .patient-facts-split-secondary {
        border-top: 1px solid rgba(226, 232, 240, 0.85);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78), rgba(255, 255, 255, 0));
        padding-bottom: 0.25rem;
    }

    @media (min-width: 900px) {
        .patient-facts-split {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .patient-facts-split .patient-facts-section {
            padding: 0.85rem 0;
        }

        .patient-facts-split .patient-facts-section + .patient-facts-section {
            border-top: 0;
            border-inline-start: 1px solid #e2e8f0;
            padding-inline-start: 1rem;
        }

    }

    .patient-facts-heading {
        margin-bottom: 0.75rem;
    }

    .patient-facts-heading h4 {
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: #475569;
    }

    .patient-facts-heading p {
        margin-top: 0.22rem;
        font-size: 0.74rem;
        line-height: 1.1rem;
        color: #64748b;
    }

    .patient-facts-grid {
        display: grid;
        gap: 0.65rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        align-items: stretch;
    }

    @media (min-width: 760px) {
        .patient-facts-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1100px) {
        .patient-facts-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .patient-facts-grid-compact {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    .patient-facts-grid-compact .patient-fact-card {
        min-height: 4.5rem;
    }

    @media (min-width: 760px) {
        .patient-facts-grid-compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .patient-fact-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        min-width: 0;
        min-height: 5.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        background: #fff;
        padding: 0.8rem 0.92rem 0.84rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 8px -4px rgba(0,0,0,0.06);
        transition: box-shadow 0.2s, transform 0.2s;
        overflow: hidden;
    }
    .patient-fact-card:hover {
        box-shadow: 0 1px 2px rgba(0,0,0,0.06), 0 8px 16px -6px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }

    .patient-facts-section-primary .patient-fact-card {
        min-height: 6.1rem;
        padding: 0.9rem 1rem 0.95rem;
    }

    .patient-fact-card-wide {
        grid-column: 1 / -1;
    }

    /* Accent backgrounds on fact cards */
    .patient-fact-accent-blue { background: #eff6ff; border-color: #bfdbfe; }
    .patient-fact-accent-slate { background: #f1f5f9; border-color: #cbd5e1; }
    .patient-fact-accent-emerald { background: #ecfdf5; border-color: #a7f3d0; }
    .patient-fact-accent-violet { background: #f5f3ff; border-color: #c4b5fd; }
    .patient-fact-accent-amber { background: #fffbeb; border-color: #fde68a; }

    .patient-fact-card::before {
        content: none;
    }
        border-radius: 9999px;
        background: #cbd5e1;
    }

    .patient-fact-label {
        display: block;
        padding-inline-start: 0.8rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #64748b;
    }

    .patient-fact-value {
        margin-top: 0.38rem;
        font-size: 0.92rem;
        line-height: 1.34rem;
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .patient-alert-count {
        display: inline-flex;
        min-width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: #fff7ed;
        color: #c2410c;
        font-size: 0.78rem;
        font-weight: 700;
        border: 1px solid #fed7aa;
        flex-shrink: 0;
    }

    .patient-alert-topline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .patient-alert-title {
        display: inline-flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #92400e;
    }

    .patient-alert-title i {
        margin-top: 0.1rem;
        width: 1rem;
        text-align: center;
        flex-shrink: 0;
    }

    .patient-alert-flag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: #ffedd5;
        color: #9a3412;
        padding: 0.15rem 0.45rem;
        font-size: 0.68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .patient-alert-copy {
        margin-top: 0.45rem;
        font-size: 0.78rem;
        line-height: 1.3rem;
        color: #92400e;
        white-space: pre-line;
    }

    .patient-medical-body {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.05rem 1.05rem 1rem;
    }

    .patient-clinical-visuals-grid {
        display: grid;
        gap: 1rem;
        padding: 1.05rem 1.05rem 0.3rem;
    }

    @media (min-width: 1100px) {
        .patient-clinical-visuals-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .patient-preview-card {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        min-width: 0;
        border: 1px solid #dbe5f0;
        border-radius: 0.95rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(247, 250, 255, 0.97));
        padding: 1rem;
        box-shadow: 0 14px 24px -24px rgba(15, 23, 42, 0.16);
    }

    .patient-preview-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .patient-preview-card-header h4 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
    }

    .patient-preview-card-header p {
        margin-top: 0.22rem;
        font-size: 0.76rem;
        line-height: 1.45;
        color: #64748b;
    }

    .patient-preview-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 9999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 0.48rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .patient-preview-frame {
        position: relative;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        border-radius: 0.9rem;
        border: 1px solid #dde7f2;
        background: linear-gradient(180deg, #f8fafc, #f1f5f9);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
    }

    .patient-preview-empty {
        display: flex;
        height: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        padding: 1rem;
        text-align: center;
        color: #64748b;
    }

    .patient-preview-empty i {
        font-size: 1.3rem;
        color: #94a3b8;
    }

    .patient-preview-empty p {
        font-size: 0.8rem;
        line-height: 1.45;
    }

    .patient-preview-empty-compact {
        min-height: 8rem;
    }

    .patient-odontogram-mini-svg {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 0.7rem 0.9rem;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .patient-mini-odontogram-svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .patient-odontogram-mini-svg svg {
        width: 100%;
        height: auto;
        display: block;
    }

    .patient-odontogram-mini-svg text {
        pointer-events: none;
        font-family: inherit;
    }

    .patient-odontogram-mini-svg .tooth-overlay {
        mix-blend-mode: multiply;
        pointer-events: none;
    }

    .patient-preview-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .patient-preview-metric {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 9999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: 0.4rem 0.7rem;
        font-size: 0.74rem;
    }

    .patient-preview-meta {
        display: flex;
        flex-direction: column;
        gap: 0.18rem;
    }

    .patient-preview-meta-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
    }

    .patient-preview-meta-copy {
        font-size: 0.76rem;
        line-height: 1.4;
        color: #64748b;
    }

    .patient-imaging-preview-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        background: #0f172a;
    }

    .patient-snapshot-list {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        padding: 1.05rem 1.05rem 0.3rem;
    }

    .patient-snapshot-item {
        border: 1px solid #dce6f0;
        border-radius: 0.9rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(236, 254, 255, 0.94));
        padding: 0.92rem 0.95rem;
        box-shadow: 0 12px 22px -24px rgba(15, 23, 42, 0.14);
    }

    .patient-snapshot-item-topline {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .patient-snapshot-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        background: #e0f2fe;
        color: #0369a1;
        padding: 0.28rem 0.55rem;
        font-size: 0.68rem;
        font-weight: 800;
    }

    .patient-snapshot-date {
        font-size: 0.7rem;
        color: #64748b;
    }

    .patient-snapshot-title {
        margin-top: 0.55rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
    }

    .patient-snapshot-copy {
        margin-top: 0.22rem;
        font-size: 0.76rem;
        color: #64748b;
        line-height: 1.45;
    }

    .patient-history-summary {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.16rem;
        text-align: end;
    }

    .patient-history-summary-count {
        display: inline-flex;
        min-width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 800;
        border: 1px solid #bfdbfe;
        align-self: flex-end;
    }

    .patient-history-summary-copy {
        font-size: 0.68rem;
        line-height: 1rem;
        color: #64748b;
        max-width: 10rem;
    }

    .patient-medical-section {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .patient-medical-section + .patient-medical-section {
        border-top: 1px solid #e2e8f0;
    }

    .patient-note-panel {
        border: 1px solid #dfe7f1;
        border-radius: 0.85rem;
        background: #ffffff;
        padding: 0.8rem 0.92rem;
        box-shadow: 0 10px 18px -24px rgba(15, 23, 42, 0.12);
    }

    .patient-note-panel-soft {
        background: linear-gradient(180deg, #f8fafc, #f1f5f9);
    }

    .patient-note-label {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        color: #64748b;
    }

    .patient-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem 0.9rem;
        border-bottom: 1px solid #e8eef5;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.94), rgba(255, 255, 255, 0.8));
    }

    .patient-section-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .patient-section-icon {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #2563eb;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        flex-shrink: 0;
    }

    .patient-section-title {
        font-size: 1rem;
        line-height: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }

    .patient-section-subtitle {
        margin-top: 0.15rem;
        font-size: 0.78rem;
        line-height: 1rem;
        color: #64748b;
    }

    .patient-section-body {
        padding: 1rem 1.25rem 1.15rem;
    }

    .patient-data-grid {
        display: grid;
        gap: 0.875rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    @media (min-width: 768px) {
        .patient-data-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .patient-data-item {
        padding: 0.85rem 0.95rem;
        border: 1px solid #edf2f7;
        border-radius: 0.9rem;
        background: #ffffff;
    }

    .patient-data-item-wide {
        grid-column: 1 / -1;
    }

    .patient-data-label {
        display: block;
        font-size: 0.75rem;
        line-height: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .patient-data-value {
        font-size: 0.95rem;
        line-height: 1.35rem;
        color: #0f172a;
    }

    .patient-note-copy {
        font-size: 0.94rem;
        line-height: 1.65;
        color: #1f2937;
        white-space: pre-line;
    }

    .patient-alert-grid {
        display: grid;
        gap: 0.8rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    @media (min-width: 768px) {
        .patient-alert-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .patient-alert-tile {
        border: 1px solid #f7d58b;
        background: linear-gradient(180deg, #fff8e6 0%, #fff4d8 100%);
        border-radius: 0.9rem;
        padding: 0.9rem 1rem;
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
    const patientMiniOdontogramData = <?= json_encode(array_values($odontogram ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function initPatientMiniOdontogramPreview() {
        const wrapper = document.querySelector('.patient-odontogram-mini-svg');
        if (!wrapper) {
            return;
        }

        const svg = wrapper.querySelector('svg');
        if (!svg) {
            return;
        }

        const conditionColors = {
            cavity: '#fbbf24',
            filling: '#3b82f6',
            crown: '#a855f7',
            root_canal: '#ec4899',
            extracted: '#ef4444',
            implant: '#6366f1',
            bridge: '#0ea5e9',
            partial_denture: '#f97316',
            full_denture: '#f59e0b',
            other: '#6b7280'
        };

        const parseToothNumber = (element) => {
            const rawId = element?.id || '';
            const match = rawId.match(/tooth-(\d+)/);
            return match ? parseInt(match[1], 10) : null;
        };

        const getLargestShape = (group) => {
            const shapes = Array.from(group.querySelectorAll('path, polygon, rect, circle, ellipse'))
                .filter(el =>
                    !el.classList.contains('tooth-hit') &&
                    !el.classList.contains('tooth-overlay') &&
                    !el.classList.contains('tooth-outline')
                );

            const getOpacity = (el) => {
                const attr = el.getAttribute('opacity');
                if (attr !== null && attr !== '') {
                    const value = Number(attr);
                    return Number.isFinite(value) ? value : 1;
                }
                const styleValue = el.style && el.style.opacity ? Number(el.style.opacity) : NaN;
                return Number.isFinite(styleValue) ? styleValue : 1;
            };

            const inMultiplyGroup = (el) => {
                let node = el;
                while (node && node !== group) {
                    if (node.getAttribute && (node.getAttribute('mix-blend-mode') || '').toLowerCase() === 'multiply') {
                        return true;
                    }
                    node = node.parentNode;
                }
                return false;
            };

            const isVisibleFill = (el) => {
                const fill = (el.getAttribute('fill') || '').trim().toLowerCase();
                if (!fill || fill === 'none' || fill === 'transparent') {
                    return false;
                }
                return true;
            };

            const filled = shapes.filter(isVisibleFill);
            const candidates = filled.length ? filled : shapes;
            let best = null;
            let bestScore = -1;

            candidates.forEach(shape => {
                let bbox;
                try {
                    bbox = shape.getBBox();
                } catch (e) {
                    return;
                }
                const area = bbox.width * bbox.height;
                if (!area || area <= 0) {
                    return;
                }

                const opacity = Math.max(0, Math.min(1, getOpacity(shape)));
                const multiplyPenalty = inMultiplyGroup(shape) ? 0.15 : 1;
                const score = area * (0.25 + 0.75 * opacity) * multiplyPenalty;

                if (score > bestScore) {
                    bestScore = score;
                    best = shape;
                }
            });

            return best;
        };

        const ensurePreviewOverlay = (group) => {
            group.querySelectorAll('.tooth-overlay, .tooth-outline, .tooth-hit').forEach(el => el.remove());
            const base = getLargestShape(group);
            if (!base) {
                return null;
            }

            const overlay = base.cloneNode(true);
            overlay.removeAttribute('id');
            overlay.setAttribute('class', 'tooth-overlay');
            overlay.setAttribute('fill', 'transparent');
            overlay.setAttribute('opacity', '0');
            overlay.style.pointerEvents = 'none';
            group.appendChild(overlay);
            return overlay;
        };

        const overlays = new Map();
        Array.from(svg.querySelectorAll('[id^="tooth-"]'))
            .filter(element => (element.tagName || '').toLowerCase().endsWith('g'))
            .forEach(group => {
                const toothNumber = parseToothNumber(group);
                if (!toothNumber) {
                    return;
                }
                const overlay = ensurePreviewOverlay(group);
                if (overlay) {
                    overlays.set(toothNumber, overlay);
                }
            });

        patientMiniOdontogramData.forEach(record => {
            const toothNumber = parseInt(record.tooth_number, 10);
            const overlay = overlays.get(toothNumber);
            if (!overlay) {
                return;
            }

            const condition = record.condition_type || 'healthy';
            if (!condition || condition === 'healthy') {
                overlay.setAttribute('fill', 'transparent');
                overlay.setAttribute('opacity', '0');
                return;
            }

            overlay.setAttribute('fill', conditionColors[condition] || '#6b7280');
            overlay.setAttribute('opacity', '0.28');
        });
    }

    // Global variables for filtering
    const patientProfileUrl = new URL(window.location.href);
    const requestedPatientTab = patientProfileUrl.searchParams.get('tab');
    let currentTab = 'details';
    let allTimelineData = [];
    let filteredTimelineData = [];

    // Initialize timeline data
    document.addEventListener('DOMContentLoaded', function() {
        initPatientMiniOdontogramPreview();
        // Store all timeline data for filtering
        allTimelineData = [
            <?php foreach ($timeline as $item): ?>
            {
                type: <?= json_encode((string) ($item['type'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                date: <?= json_encode((string) ($item['date'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                title: <?= json_encode((string) ($item['title'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                description: <?= json_encode((string) ($item['description'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                status: <?= json_encode((string) ($item['status'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                status_label: <?= json_encode((string) ($item['status_label'] ?? ucfirst((string) ($item['status'] ?? ''))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                id: <?= json_encode((string) ($item['id'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                doctor: <?= json_encode((string) ($item['doctor'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                icon: <?= json_encode((string) ($item['icon'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                color: <?= json_encode((string) ($item['color'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                url: <?= json_encode((string) ($item['url'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                meta: <?= json_encode($item['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
            },
            <?php endforeach; ?>
        ];
        filteredTimelineData = [...allTimelineData];
        
        // Add event listeners
        document.getElementById('historySearch').addEventListener('input', filterHistory);
        document.getElementById('startDate').addEventListener('change', filterHistory);
        document.getElementById('endDate').addEventListener('change', filterHistory);
        const patientTabs = Array.from(document.querySelectorAll('.tab-button'));
        const patientPanels = Array.from(document.querySelectorAll('.tab-content'));

        patientPanels.forEach((panel) => {
            if (!panel.hasAttribute('role')) {
                panel.setAttribute('role', 'tabpanel');
            }
            const tabName = panel.id.replace('-content', '');
            panel.setAttribute('aria-labelledby', `${tabName}-tab`);
        });

        patientTabs.forEach((tab, index) => {
            tab.addEventListener('keydown', (event) => {
                if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                    return;
                }

                event.preventDefault();
                let nextIndex = index;
                if (event.key === 'ArrowRight') {
                    nextIndex = (index + 1) % patientTabs.length;
                } else if (event.key === 'ArrowLeft') {
                    nextIndex = (index - 1 + patientTabs.length) % patientTabs.length;
                } else if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = patientTabs.length - 1;
                }

                patientTabs[nextIndex]?.focus();
            });
        });

        const validTabs = patientTabs
            .map(tab => tab.id.replace('-tab', ''))
            .filter(Boolean);

        let initialTab = 'details';
        if (requestedPatientTab && validTabs.includes(requestedPatientTab)) {
            initialTab = requestedPatientTab;
        }

        const shouldOpenAttachments = <?= json_encode((bool) session('attachments_flash')) ?>;
        if (shouldOpenAttachments && validTabs.includes('attachments')) {
            initialTab = 'attachments';
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
        if (shouldOpenContracts && validTabs.includes('contracts')) {
            initialTab = 'contracts';
        }

        showTab(initialTab);
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
        contents.forEach(content => {
            content.classList.add('hidden');
            content.hidden = true;
        });
        
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab-button');
        tabs.forEach(tab => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
            tab.setAttribute('tabindex', '-1');
        });
        
        // Show selected tab content
        const selectedContent = document.getElementById(tabName + '-content');
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
            selectedContent.hidden = false;
        }
        
        // Add active class to selected tab
        const selectedTab = document.getElementById(tabName + '-tab');
        if (selectedTab) {
            selectedTab.classList.add('active');
            selectedTab.setAttribute('aria-selected', 'true');
            selectedTab.setAttribute('tabindex', '0');
        }

        const nextUrl = new URL(window.location.href);
        if (tabName === 'details') {
            nextUrl.searchParams.delete('tab');
        } else {
            nextUrl.searchParams.set('tab', tabName);
        }
        window.history.replaceState({}, '', nextUrl.toString());

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
            timelineItem.className = 'patient-timeline-node';
            
            const divRelative = document.createElement('div');
            divRelative.className = 'relative';
            
            if (index !== filteredTimelineData.length - 1) {
                const spanLine = document.createElement('span');
                spanLine.className = 'absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200';
                spanLine.setAttribute('aria-hidden', 'true');
                divRelative.appendChild(spanLine);
            }
            
            const link = document.createElement(item.url ? 'a' : 'div');
            if (item.url) {
                link.href = item.url;
            }
            link.className = 'patient-timeline-link';

            const divFlex = document.createElement('div');
            divFlex.className = 'patient-timeline-shell';
            
            // Icon
            const divIconOuter = document.createElement('div');
            const spanIconBg = document.createElement('span');
            spanIconBg.className = `patient-timeline-icon bg-${item.color}-500`;
            const icon = document.createElement('i');
            icon.className = `${item.icon} text-sm`;
            spanIconBg.appendChild(icon);
            divIconOuter.appendChild(spanIconBg);
            
            // Content
            const divContent = document.createElement('div');
            divContent.className = 'patient-timeline-main';
            
            const titleBlock = document.createElement('div');
            titleBlock.className = 'patient-timeline-title-block';
            const titleDiv = document.createElement('div');
            titleDiv.className = 'patient-timeline-title';
            titleDiv.textContent = item.title;
            const dateDiv = document.createElement('div');
            dateDiv.className = 'patient-timeline-date';
            const itemIso = String(item.date || '').split(' ')[0];
            const itemDisplay = window.formatClinicDateFromIso ? window.formatClinicDateFromIso(itemIso) : itemIso;
            dateDiv.textContent = `${patientShowI18n.timelineOn} ${itemDisplay}`;
            titleBlock.appendChild(titleDiv);
            titleBlock.appendChild(dateDiv);
            
            const timelineMeta = Array.isArray(item.meta) ? [...item.meta] : [];
            if (item.doctor) {
                timelineMeta.push({
                    icon: 'fas fa-user-md',
                    text: `Dr. ${item.doctor}`
                });
            }

            const summaryWrap = document.createElement('div');
            summaryWrap.className = 'patient-timeline-summary';
            const pDesc = document.createElement('p');
            pDesc.className = 'patient-timeline-description';
            pDesc.textContent = item.description;
            summaryWrap.appendChild(pDesc);

            if (timelineMeta.length > 0) {
                const metaWrap = document.createElement('div');
                metaWrap.className = 'patient-timeline-meta';
                timelineMeta.forEach((metaItem) => {
                    if (!metaItem || !metaItem.text) {
                        return;
                    }
                    const chip = document.createElement('span');
                    chip.className = 'patient-timeline-meta-chip';
                    if (metaItem.icon) {
                        const metaIcon = document.createElement('i');
                        metaIcon.className = metaItem.icon;
                        chip.appendChild(metaIcon);
                    }
                    const metaText = document.createElement('span');
                    metaText.textContent = metaItem.text;
                    chip.appendChild(metaText);
                    metaWrap.appendChild(chip);
                });
                if (metaWrap.childNodes.length > 0) {
                    summaryWrap.appendChild(metaWrap);
                }
            }
            
            const divStatus = document.createElement('div');
            divStatus.className = 'patient-timeline-status';
            const spanStatus = document.createElement('span');
            spanStatus.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${item.color}-100 text-${item.color}-800`;
            spanStatus.textContent = item.status_label || (item.status.charAt(0).toUpperCase() + item.status.slice(1));
            divStatus.appendChild(spanStatus);
            
            divContent.appendChild(titleBlock);
            divContent.appendChild(summaryWrap);
            divContent.appendChild(divStatus);
            
            divFlex.appendChild(divIconOuter);
            divFlex.appendChild(divContent);
            link.appendChild(divFlex);
            divRelative.appendChild(link);
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
                    if (startDate && cellDate < new Date(startDate + 'T00:00:00')) {
                        matchesDateRange = false;
                    }
                    if (endDate && cellDate > new Date(endDate + 'T23:59:59')) {
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

    document.addEventListener('click', function(event) {
        const row = event.target.closest('.patient-record-row[data-href]');
        if (!row) {
            return;
        }

        if (event.target.closest('a, button, form, input, select, textarea, label')) {
            return;
        }

        window.location.href = row.dataset.href;
    });

    function getSortableCellValue(row, columnIndex) {
        if (columnIndex === 0 && row.dataset.date) {
            const dateValue = Date.parse(row.dataset.date);
            if (!Number.isNaN(dateValue)) {
                return { type: 'number', value: dateValue };
            }
        }

        const cell = row.children[columnIndex];
        if (!cell) {
            return { type: 'text', value: '' };
        }

        const rawText = cell.textContent.replace(/\s+/g, ' ').trim();
        const currencyText = rawText.replace(/[^0-9.\-]/g, '');
        if (currencyText !== '' && /^-?\d+(\.\d+)?$/.test(currencyText)) {
            return { type: 'number', value: parseFloat(currencyText) };
        }

        const parsedDate = Date.parse(rawText);
        if (!Number.isNaN(parsedDate) && /\d/.test(rawText)) {
            return { type: 'number', value: parsedDate };
        }

        return { type: 'text', value: rawText.toLowerCase() };
    }

    function sortPatientRecordTable(table, columnIndex, direction) {
        const tbody = table.querySelector('tbody');
        if (!tbody) {
            return;
        }

        const rows = Array.from(tbody.querySelectorAll('tr'));
        const dataRows = rows.filter(row => row.children.length > 1);
        if (dataRows.length <= 1) {
            return;
        }

        dataRows.sort((a, b) => {
            const aValue = getSortableCellValue(a, columnIndex);
            const bValue = getSortableCellValue(b, columnIndex);

            let result = 0;
            if (aValue.type === 'number' && bValue.type === 'number') {
                result = aValue.value - bValue.value;
            } else {
                result = String(aValue.value).localeCompare(String(bValue.value), document.documentElement.lang || 'en');
            }

            return direction === 'desc' ? -result : result;
        });

        dataRows.forEach(row => tbody.appendChild(row));
    }

    function initializePatientRecordTables() {
        const headerIconMap = {
            attachments: ['fas fa-calendar-days', 'fas fa-file-lines', 'fas fa-folder-open', 'fas fa-note-sticky', 'fas fa-ellipsis'],
            contracts: ['fas fa-calendar-days', 'fas fa-file-signature', 'fas fa-stethoscope', 'fas fa-sack-dollar', 'fas fa-ellipsis'],
            examinations: ['fas fa-calendar-days', 'fas fa-stethoscope', 'fas fa-comment-medical', 'fas fa-circle-check', 'fas fa-ellipsis'],
            appointments: ['fas fa-calendar-days', 'fas fa-notes-medical', 'fas fa-hourglass-half', 'fas fa-circle-check', 'fas fa-ellipsis'],
            treatments: ['fas fa-briefcase-medical', 'fas fa-tooth', 'fas fa-sack-dollar', 'fas fa-circle-check', 'fas fa-ellipsis'],
            prescriptions: ['fas fa-pills', 'fas fa-prescription-bottle-medical', 'fas fa-repeat', 'fas fa-calendar-days', 'fas fa-circle-check', 'fas fa-ellipsis'],
            finances: ['fas fa-calendar-days', 'fas fa-receipt', 'fas fa-credit-card', 'fas fa-sack-dollar', 'fas fa-circle-check', 'fas fa-ellipsis']
        };

        document.querySelectorAll('.patient-record-table').forEach((table) => {
            const headers = Array.from(table.querySelectorAll('thead th'));
            if (headers.length === 0) {
                return;
            }

            const recordType = table.dataset.recordType || '';
            const headerIcons = headerIconMap[recordType] || [];

            headers.forEach((th, index) => {
                if (index === headers.length - 1 || th.dataset.sortReady === '1') {
                    return;
                }

                const label = th.textContent.trim();
                th.dataset.sortReady = '1';
                th.textContent = '';

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'patient-table-sort';
                button.dataset.direction = 'none';
                const iconClass = headerIcons[index] || 'fas fa-table-columns';
                button.innerHTML = `<span class="patient-table-sort-label"><i class="${iconClass}"></i><span>${label}</span></span><i class="fas fa-sort"></i>`;

                button.addEventListener('click', () => {
                    const currentDirection = button.dataset.direction === 'asc' ? 'asc' : (button.dataset.direction === 'desc' ? 'desc' : 'none');
                    const nextDirection = currentDirection === 'asc' ? 'desc' : 'asc';

                    headers.forEach((otherHeader) => {
                        const otherButton = otherHeader.querySelector('.patient-table-sort');
                        if (!otherButton) {
                            return;
                        }
                        otherButton.dataset.direction = 'none';
                        otherButton.classList.remove('is-active');
                        const icon = otherButton.lastElementChild;
                        if (icon) {
                            icon.className = 'fas fa-sort';
                        }
                    });

                    button.dataset.direction = nextDirection;
                    button.classList.add('is-active');
                    const icon = button.lastElementChild;
                    if (icon) {
                        icon.className = nextDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
                    }

                    sortPatientRecordTable(table, index, nextDirection);
                });

                th.appendChild(button);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initializePatientRecordTables);





</script>

<script>
    // Odontogram functionality
    let currentNumberingSystem = 'universal';
    let odontogramData = [
        <?php foreach ($odontogram as $tooth): ?>
        {
            tooth_number: <?= $tooth['tooth_number'] ?>,
            condition_type: <?= json_encode((string) ($tooth['condition_type'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            condition_description: <?= json_encode((string) ($tooth['condition_description'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            treatment_notes: <?= json_encode((string) ($tooth['treatment_notes'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            treatment_status: <?= json_encode((string) ($tooth['treatment_status'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            treatment_date: <?= json_encode((string) ($tooth['treatment_date'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
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

