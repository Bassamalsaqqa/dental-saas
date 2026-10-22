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
            'charge_line_id' => 0,
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
        'charge_line_id' => $chargeId,
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
$moneyTrailForPatient = $money_trail ?? [];
$clinicalSourcesByFinanceId = [];
$providerPayoutsByFinanceId = [];
$sameDayClinicalByDate = [];
$inventoryUsageByTreatmentId = [];
$inventoryUsageByExaminationId = [];

$providerPayoutName = static function (array $row): string {
    $name = trim((string) (($row['provider_first_name'] ?? '') . ' ' . ($row['provider_last_name'] ?? '')));
    if ($name !== '') {
        return $name;
    }

    $snapshot = trim((string) ($row['provider_name_snapshot'] ?? ''));
    return $snapshot !== '' ? $snapshot : lang('UI.moneyTrailProvider');
};

$inventoryUsageSummary = static function (array $row): string {
    $items = json_decode((string) ($row['items_used'] ?? ''), true);
    if (!is_array($items) || $items === []) {
        return lang('UI.moneyTrailInventoryUsage');
    }

    $labels = [];
    foreach (array_slice($items, 0, 2) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = trim((string) ($item['item_name'] ?? ''));
        $qty = trim((string) ($item['quantity_used'] ?? ''));
        if ($name === '') {
            continue;
        }
        $labels[] = $qty !== '' ? $name . ' x' . $qty : $name;
    }

    if ($labels === []) {
        return lang('UI.moneyTrailInventoryUsage');
    }

    $remaining = count($items) - count($labels);
    return implode(', ', $labels) . ($remaining > 0 ? ' +' . $remaining : '');
};

foreach ($examinations as $examRow) {
    $examDate = !empty($examRow['examination_date']) ? substr((string) $examRow['examination_date'], 0, 10) : '';
    if ($examDate === '') {
        continue;
    }

    if (!isset($sameDayClinicalByDate[$examDate])) {
        $sameDayClinicalByDate[$examDate] = ['examinations' => [], 'treatments' => [], 'captured_amount' => 0.0];
    }

    $sameDayClinicalByDate[$examDate]['examinations'][] = [
        'label' => (string) ($examRow['examination_id'] ?? lang('UI.patientClinicalExamination')),
        'url' => workflow_context_url('examination/' . (int) ($examRow['id'] ?? 0), $patientContextReturnTo),
    ];
}

foreach ($treatments as $treatmentRow) {
    $treatmentDate = !empty($treatmentRow['start_date']) ? substr((string) $treatmentRow['start_date'], 0, 10) : '';
    if ($treatmentDate === '') {
        continue;
    }

    if (!isset($sameDayClinicalByDate[$treatmentDate])) {
        $sameDayClinicalByDate[$treatmentDate] = ['examinations' => [], 'treatments' => [], 'captured_amount' => 0.0];
    }

    $billingMeta = $treatmentBillingMeta($treatmentRow);
    $sameDayClinicalByDate[$treatmentDate]['treatments'][] = [
        'label' => (string) ($treatmentRow['treatment_name'] ?? lang('UI.patientTreatment')),
        'url' => workflow_context_url('treatment/' . (int) ($treatmentRow['id'] ?? 0), $patientContextReturnTo),
        'amount' => (float) ($billingMeta['amount_raw'] ?? 0),
    ];
    $sameDayClinicalByDate[$treatmentDate]['captured_amount'] += (float) ($billingMeta['amount_raw'] ?? 0);
}

foreach (($moneyTrailForPatient['inventory_usage_rows'] ?? []) as $usageRow) {
    $usageTreatmentId = (int) ($usageRow['treatment_id'] ?? 0);
    $usageExaminationId = (int) ($usageRow['examination_id'] ?? 0);

    if ($usageTreatmentId > 0) {
        $inventoryUsageByTreatmentId[$usageTreatmentId][] = $usageRow;
    }
    if ($usageExaminationId > 0) {
        $inventoryUsageByExaminationId[$usageExaminationId][] = $usageRow;
    }
}

foreach (($moneyTrailForPatient['charge_lines'] ?? []) as $chargeLineRow) {
    $sourceFinanceId = (int) ($chargeLineRow['finance_id'] ?? 0);
    if ($sourceFinanceId <= 0) {
        continue;
    }

    $sourceTreatmentId = (int) ($chargeLineRow['treatment_id'] ?? 0);
    $sourceExaminationId = (int) ($chargeLineRow['examination_id'] ?? 0);
    $sourceLabel = trim((string) ($chargeLineRow['treatment_name'] ?? ''));
    $sourceReference = trim((string) ($chargeLineRow['treatment_number'] ?? ''));
    $sourceIcon = 'fas fa-stethoscope';
    $sourceUrl = null;

    if ($sourceTreatmentId > 0) {
        $sourceIcon = 'fas fa-tooth';
        $sourceUrl = workflow_context_url('treatment/' . $sourceTreatmentId, $patientContextReturnTo);
        if ($sourceLabel === '') {
            $sourceLabel = $sourceReference !== '' ? $sourceReference : lang('UI.patientTreatment');
        }
    } elseif ($sourceExaminationId > 0) {
        $sourceUrl = workflow_context_url('examination/' . $sourceExaminationId, $patientContextReturnTo);
        $sourceReference = trim((string) ($chargeLineRow['examination_number'] ?? ''));
        $sourceLabel = $sourceReference !== '' ? $sourceReference : lang('UI.patientClinicalExamination');
    }

    $clinicalSourcesByFinanceId[$sourceFinanceId][] = [
        'label' => $sourceLabel !== '' ? $sourceLabel : lang('UI.patientClinicalWork'),
        'reference' => $sourceReference,
        'url' => $sourceUrl,
        'icon' => $sourceIcon,
        'amount' => isset($chargeLineRow['net_production_amount']) ? (float) $chargeLineRow['net_production_amount'] : null,
        'currency' => (string) ($chargeLineRow['currency'] ?? ''),
    ];
}

foreach (($moneyTrailForPatient['provider_payouts'] ?? []) as $payoutRow) {
    $sourceFinanceId = (int) ($payoutRow['finance_id'] ?? 0);
    if ($sourceFinanceId <= 0) {
        continue;
    }

    $providerShare = max(0, (float) ($payoutRow['provider_share_amount'] ?? 0));
    $providerPaid = max(0, (float) ($payoutRow['provider_paid_amount'] ?? 0));
    $providerBalance = max(0, (float) ($payoutRow['provider_balance_due'] ?? 0));

    $providerPayoutsByFinanceId[$sourceFinanceId][] = [
        'provider_name' => $providerPayoutName($payoutRow),
        'status' => (string) ($payoutRow['status'] ?? ''),
        'share_amount' => $providerShare,
        'paid_amount' => $providerPaid,
        'balance_amount' => $providerBalance,
        'share_text' => formatCurrency($providerShare),
        'paid_text' => formatCurrency($providerPaid),
        'balance_text' => formatCurrency($providerBalance),
        'url' => workflow_context_url('finance/' . $sourceFinanceId, $patientContextReturnTo),
    ];
}

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
        'clinical_sources' => $clinicalSourcesByFinanceId[$financeId] ?? [],
        'provider_payouts' => $providerPayoutsByFinanceId[$financeId] ?? [],
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
        'clinical_sources' => $clinicalSourcesByFinanceId[$financeId] ?? [],
        'provider_payouts' => $providerPayoutsByFinanceId[$financeId] ?? [],
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
$latestImagingIsPreviewable = in_array(strtolower(trim((string) ($latestImagingAttachment['mime_type'] ?? ''))), ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'], true);
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
                                                        <img loading="lazy" decoding="async" src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="patient-imaging-preview-image">
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
                            <div class="patient-tab-ambient patient-timeline-workspace">
                                <div class="patient-timeline-workspace-header">
                                    <div class="patient-timeline-heading">
                                        <span class="patient-timeline-heading-icon">
                                            <i class="fas fa-clock-rotate-left"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <h3><?= esc(lang('UI.patientTabTimeline')) ?></h3>
                                            <p><?= esc(lang('UI.patientHistory')) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <ul class="patient-timeline-list">
                                    <?php 
                                    // Combine all records into a timeline
                                    $timeline = [];
                                    
                                    // Add examinations
                                    foreach ($examinations as $exam) {
                                        $doctorName = trim(($exam['doctor_first_name'] ?? '') . ' ' . ($exam['doctor_last_name'] ?? ''));
                                        $examStatus = ucfirst((string) ($exam['status'] ?? lang('UI.patientUnknown')));
                                        $examId = (int) ($exam['id'] ?? 0);
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
                                        $examInventoryCost = 0.0;
                                        foreach (($inventoryUsageByExaminationId[$examId] ?? []) as $usageRow) {
                                            $examInventoryCost += max(0, (float) ($usageRow['total_cost'] ?? 0));
                                        }
                                        if ($examInventoryCost > 0.009) {
                                            $examMeta[] = [
                                                'icon' => 'fas fa-boxes-stacked',
                                                'text' => lang('UI.moneyTrailInventoryCost') . ': ' . formatCurrency($examInventoryCost),
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
                                        $apptDateKey = !empty($appt['appointment_date']) ? substr((string) $appt['appointment_date'], 0, 10) : '';
                                        $sameDayClinical = $apptDateKey !== '' ? ($sameDayClinicalByDate[$apptDateKey] ?? null) : null;
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
                                        if (!empty($sameDayClinical)) {
                                            $sameDayExamCount = count($sameDayClinical['examinations'] ?? []);
                                            $sameDayTreatmentCount = count($sameDayClinical['treatments'] ?? []);
                                            $sameDayParts = [];
                                            if ($sameDayExamCount > 0) {
                                                $sameDayParts[] = $sameDayExamCount . ' ' . lang('UI.patientClinicalExamination');
                                            }
                                            if ($sameDayTreatmentCount > 0) {
                                                $sameDayParts[] = $sameDayTreatmentCount . ' ' . lang('UI.patientTreatment');
                                            }
                                            if ($sameDayParts !== []) {
                                                $apptMeta[] = [
                                                    'icon' => 'fas fa-link',
                                                    'text' => lang('UI.patientSameDayClinicalWork') . ': ' . implode(' / ', $sameDayParts),
                                                ];
                                            }
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
                                        $treatmentId = (int) ($treatment['id'] ?? 0);
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
                                        $treatmentInventoryCost = 0.0;
                                        foreach (($inventoryUsageByTreatmentId[$treatmentId] ?? []) as $usageRow) {
                                            $treatmentInventoryCost += max(0, (float) ($usageRow['total_cost'] ?? 0));
                                        }
                                        if ($treatmentInventoryCost > 0.009) {
                                            $treatmentMeta[] = [
                                                'icon' => 'fas fa-boxes-stacked',
                                                'text' => lang('UI.moneyTrailInventoryCost') . ': ' . formatCurrency($treatmentInventoryCost),
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
                                        foreach (($clinicalSourcesByFinanceId[(int) ($finance['id'] ?? 0)] ?? []) as $clinicalSource) {
                                            $financeMeta[] = [
                                                'icon' => $clinicalSource['icon'] ?? 'fas fa-link',
                                                'text' => lang('UI.patientClinicalSource') . ': ' . (string) ($clinicalSource['label'] ?? lang('UI.patientClinicalWork')),
                                            ];
                                        }
                                        foreach (($providerPayoutsByFinanceId[(int) ($finance['id'] ?? 0)] ?? []) as $providerPayout) {
                                            $financeMeta[] = [
                                                'icon' => 'fas fa-user-doctor',
                                                'text' => lang('UI.patientProviderCompensation') . ': ' . (string) ($providerPayout['provider_name'] ?? lang('UI.moneyTrailProvider')) . ' - ' . (string) ($providerPayout['share_text'] ?? ''),
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

                                    // Add invoice settlement rows recorded against patient invoices.
                                    foreach (($moneyTrailForPatient['payment_rows'] ?? []) as $paymentRow) {
                                        $paymentFinanceId = (int) ($paymentRow['finance_id'] ?? 0);
                                        if ($paymentFinanceId <= 0) {
                                            continue;
                                        }

                                        $paymentType = strtolower((string) ($paymentRow['type'] ?? 'payment'));
                                        $paymentAmount = max(0, (float) ($paymentRow['amount'] ?? 0));
                                        $paymentReference = trim((string) ($paymentRow['transaction_id'] ?? ('#' . $paymentFinanceId)));
                                        $paymentMeta = [
                                            [
                                                'icon' => 'fas fa-dollar-sign',
                                                'text' => formatCurrency($paymentAmount),
                                            ],
                                            [
                                                'icon' => 'fas fa-file-invoice-dollar',
                                                'text' => $paymentReference,
                                            ],
                                        ];
                                        if (!empty($paymentRow['payment_method'])) {
                                            $paymentMeta[] = [
                                                'icon' => 'fas fa-wallet',
                                                'text' => $financeMethodLabel((string) $paymentRow['payment_method']),
                                            ];
                                        }
                                        foreach (($clinicalSourcesByFinanceId[$paymentFinanceId] ?? []) as $clinicalSource) {
                                            $paymentMeta[] = [
                                                'icon' => $clinicalSource['icon'] ?? 'fas fa-link',
                                                'text' => lang('UI.patientClinicalSource') . ': ' . (string) ($clinicalSource['label'] ?? lang('UI.patientClinicalWork')),
                                            ];
                                        }

                                        $timeline[] = [
                                            'type' => 'finance_payment',
                                            'date' => $paymentRow['payment_date'] ?? $paymentRow['created_at'] ?? null,
                                            'title' => lang('UI.patientTimelinePaymentTitle', ['type' => $financeTypeLabel($paymentType)]),
                                            'description' => $paymentRow['notes'] ?? $paymentReference,
                                            'status' => $paymentType,
                                            'status_label' => $financeTypeLabel($paymentType),
                                            'id' => $paymentRow['id'] ?? null,
                                            'icon' => 'fas fa-hand-holding-dollar',
                                            'color' => in_array($paymentType, ['refund', 'void'], true) ? 'orange' : 'green',
                                            'url' => workflow_context_url('finance/' . $paymentFinanceId, $patientContextReturnTo),
                                            'meta' => $paymentMeta,
                                        ];
                                    }

                                    // Add provider-share rows so the patient timeline shows doctor compensation impact.
                                    foreach (($moneyTrailForPatient['provider_payouts'] ?? []) as $providerRow) {
                                        $providerFinanceId = (int) ($providerRow['finance_id'] ?? 0);
                                        if ($providerFinanceId <= 0) {
                                            continue;
                                        }

                                        $providerName = $providerPayoutName($providerRow);
                                        $providerShare = max(0, (float) ($providerRow['provider_share_amount'] ?? 0));
                                        $providerPaid = max(0, (float) ($providerRow['provider_paid_amount'] ?? 0));
                                        $providerBalance = max(0, (float) ($providerRow['provider_balance_due'] ?? 0));
                                        $providerMeta = [
                                            [
                                                'icon' => 'fas fa-user-doctor',
                                                'text' => $providerName,
                                            ],
                                            [
                                                'icon' => 'fas fa-scale-balanced',
                                                'text' => lang('UI.moneyTrailProviderShare') . ': ' . formatCurrency($providerShare),
                                            ],
                                            [
                                                'icon' => 'fas fa-hand-holding-dollar',
                                                'text' => lang('UI.moneyTrailPaid') . ': ' . formatCurrency($providerPaid),
                                            ],
                                            [
                                                'icon' => 'fas fa-wallet',
                                                'text' => lang('UI.moneyTrailProviderRemaining') . ': ' . formatCurrency($providerBalance),
                                            ],
                                        ];

                                        $timeline[] = [
                                            'type' => 'provider_payout',
                                            'date' => $providerRow['created_at'] ?? null,
                                            'title' => lang('Finance.providerPayout') . ' - ' . $providerName,
                                            'description' => trim((string) ($providerRow['notes'] ?? '')) !== '' ? (string) $providerRow['notes'] : (string) ($providerRow['transaction_id'] ?? lang('Finance.providerShare')),
                                            'status' => $providerRow['status'] ?? null,
                                            'status_label' => $financeStatusLabel($providerRow['status'] ?? null),
                                            'id' => $providerRow['id'] ?? null,
                                            'icon' => 'fas fa-user-doctor',
                                            'color' => $providerBalance > 0.009 ? 'purple' : 'green',
                                            'url' => workflow_context_url('finance/' . $providerFinanceId, $patientContextReturnTo),
                                            'meta' => $providerMeta,
                                        ];
                                    }

                                    // Add material usage rows linked to patient treatments.
                                    foreach (($moneyTrailForPatient['inventory_usage_rows'] ?? []) as $usageRow) {
                                        $usageTreatmentId = (int) ($usageRow['treatment_id'] ?? 0);
                                        $usageTreatmentLabel = trim((string) ($usageRow['treatment_name'] ?? $usageRow['treatment_number'] ?? ''));
                                        $usageCost = max(0, (float) ($usageRow['total_cost'] ?? 0));
                                        $usageMeta = [
                                            [
                                                'icon' => 'fas fa-boxes-stacked',
                                                'text' => $inventoryUsageSummary($usageRow),
                                            ],
                                            [
                                                'icon' => 'fas fa-dollar-sign',
                                                'text' => formatCurrency($usageCost),
                                            ],
                                        ];
                                        if ($usageTreatmentLabel !== '') {
                                            $usageMeta[] = [
                                                'icon' => 'fas fa-tooth',
                                                'text' => $usageTreatmentLabel,
                                            ];
                                        }

                                        $timeline[] = [
                                            'type' => 'inventory_usage',
                                            'date' => $usageRow['usage_date'] ?? $usageRow['created_at'] ?? null,
                                            'title' => lang('UI.moneyTrailInventoryUsage'),
                                            'description' => trim((string) ($usageRow['notes'] ?? '')) !== '' ? (string) $usageRow['notes'] : $inventoryUsageSummary($usageRow),
                                            'status' => 'recorded',
                                            'status_label' => lang('UI.patientRecorded'),
                                            'id' => $usageRow['id'] ?? null,
                                            'icon' => 'fas fa-boxes-stacked',
                                            'color' => 'amber',
                                            'url' => $usageTreatmentId > 0 ? workflow_context_url('treatment/' . $usageTreatmentId, $patientContextReturnTo) : '',
                                            'meta' => $usageMeta,
                                        ];
                                    }
                                    
                                    // Sort by date
                                    usort($timeline, function($a, $b) {
                                        return strtotime($b['date']) - strtotime($a['date']);
                                    });
                                    
                                    foreach ($timeline as $index => $item): 
                                    ?>
                                    <li class="patient-timeline-node">
                                        <div class="relative">
                                            <?php if ($index !== count($timeline) - 1): ?>
                                                <span class="patient-timeline-rail" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['url'])): ?>
                                                <a href="<?= esc($item['url']) ?>" class="patient-timeline-link">
                                            <?php else: ?>
                                                <div class="patient-timeline-link">
                                            <?php endif; ?>
                                                <div class="patient-timeline-shell">
                                                    <div>
                                                        <span class="patient-timeline-icon bg-<?= esc($item['color']) ?>-500">
                                                            <i class="<?= esc($item['icon']) ?> text-sm"></i>
                                                        </span>
                                                    </div>
                                                    <div class="patient-timeline-main">
                                                        <div class="patient-timeline-title-block">
                                                            <div class="patient-timeline-title"><?= esc($item['title']) ?></div>
                                                            <div class="patient-timeline-date">
                                                                <i class="fas fa-calendar-day"></i>
                                                                <span><?= esc(lang('UI.patientTimelineOn')) ?> <?= !empty($item['date']) ? formatDate($item['date']) : esc(lang('UI.patientNa')) ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="patient-timeline-summary">
                                                            <p class="patient-timeline-description"><?= esc($item['description']) ?></p>
                                                            <?php
                                                            $timelineMeta = $item['meta'] ?? [];
                                                            if (!empty($item['doctor'])) {
                                                                $timelineMeta[] = [
                                                                    'icon' => 'fas fa-user-md',
                                                                    'text' => 'Dr. ' . $item['doctor'],
                                                                ];
                                                            }
                                                            $timelineFacts = array_values(array_filter($timelineMeta, static fn($metaItem): bool => !empty($metaItem['text'])));
                                                            $timelineFactsPreview = array_slice($timelineFacts, 0, 3);
                                                            $timelineFactsMore = max(0, count($timelineFacts) - count($timelineFactsPreview));
                                                            ?>
                                                            <?php if (!empty($timelineFactsPreview)): ?>
                                                                <div class="patient-timeline-facts">
                                                                    <?php foreach ($timelineFactsPreview as $metaItem): ?>
                                                                        <span class="patient-timeline-fact">
                                                                            <?php if (!empty($metaItem['icon'])): ?><i class="<?= esc($metaItem['icon']) ?>"></i><?php endif; ?>
                                                                            <span><?= esc($metaItem['text']) ?></span>
                                                                        </span>
                                                                    <?php endforeach; ?>
                                                                    <?php if ($timelineFactsMore > 0): ?>
                                                                        <span class="patient-timeline-fact-more">+<?= (int) $timelineFactsMore ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="patient-timeline-status">
                                                            <span class="patient-timeline-status-label bg-<?= esc($item['color']) ?>-100 text-<?= esc($item['color']) ?>-800">
                                                                <?= esc($item['status_label'] ?? ucfirst((string) ($item['status'] ?? ''))) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php if (!empty($item['url'])): ?>
                                                </a>
                                            <?php else: ?>
                                                </div>
                                            <?php endif; ?>
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

                                <section class="relative overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-white via-cyan-50/65 to-blue-50/60 p-4 shadow-xl shadow-slate-200/60 sm:p-6"><div class="pointer-events-none absolute inset-0 opacity-80"><div class="absolute -top-24 end-10 h-52 w-52 rounded-full bg-cyan-400/25 blur-3xl"></div><div class="absolute bottom-0 start-6 h-48 w-48 rounded-full bg-blue-400/25 blur-3xl"></div></div><div class="relative grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.95fr)] mb-5">
                                    <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                        <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-cyan-50/80 to-blue-50/80 px-5 py-4">
                                            <h3 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientImagingWorkspaceLatestTitle')) ?></h3>
                                            <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                        </div>
                                        <div class="space-y-4 p-5">
                                            <?php if (!empty($latestImagingAttachment)): ?>
                                                <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-950">
                                                    <div class="aspect-[16/9] flex items-center justify-center">
                                                        <?php if (!empty($latestImagingAttachment) && $latestImagingIsPreviewable && !empty($latestImagingPreviewSrc)): ?>
                                                            <img loading="lazy" decoding="async" src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="h-full w-full object-contain">
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
                                        <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                            <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-cyan-50/80 to-blue-50/80 px-5 py-4">
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

                                <div class="overflow-hidden rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                    <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-indigo-50/80 to-cyan-50/80 px-5 py-4">
                                        <h4 class="text-base font-extrabold text-slate-950"><?= esc(lang('UI.patientAttachmentHistory')) ?></h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="patient-record-table" data-record-type="attachments" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientFile')) ?></th>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientCategory')) ?></th>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientNotes')) ?></th>
                                                    <th class="px-6 py-4 text-right text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
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
                                </div></section>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($contracts_enabled)): ?>
                            <!-- Contracts Tab -->
                            <div id="contracts-content" class="tab-content hidden">
                                <section class="relative overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-white via-indigo-50/65 to-cyan-50/60 p-4 shadow-xl shadow-slate-200/60 sm:p-6"><div class="pointer-events-none absolute inset-0 opacity-80"><div class="absolute -top-24 end-10 h-52 w-52 rounded-full bg-indigo-400/25 blur-3xl"></div><div class="absolute bottom-0 start-6 h-48 w-48 rounded-full bg-cyan-300/25 blur-3xl"></div></div><div class="relative space-y-5">
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

                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <?php if (has_permission('patients', 'edit')): ?>
                                        <a href="<?= workflow_context_url('contracts/create/' . $patient['id'], $patientContextReturnTo) ?>"
                                           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-indigo-500/20 transition hover:from-indigo-700 hover:to-cyan-800">
                                            <i class="fas fa-plus"></i><?= esc(lang('UI.patientCreateTreatmentPlan')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="overflow-hidden rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                    <div class="border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-indigo-50/80 to-cyan-50/80 px-5 py-4">
                                        <h4 class="text-base font-extrabold text-slate-950"><?= esc(lang('UI.patientContractHistory')) ?></h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="patient-record-table" data-record-type="contracts" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientExam')) ?></th>
                                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientTotal')) ?></th>
                                                    <th class="px-6 py-4 text-right text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
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
                                </div></section>
                            </div>
                        <?php endif; ?>

                        <!-- Examinations Tab -->
                        <div id="examinations-content" class="tab-content hidden">
                            <section class="patient-tab-ambient patient-exam-workspace">
                                <div class="patient-exam-header">
                                    <div class="patient-exam-heading">
                                        <span class="patient-exam-heading-icon"><i class="fas fa-stethoscope"></i></span>
                                        <div class="min-w-0">
                                            <h3><?= esc(lang('UI.patientExaminations')) ?></h3>
                                            <p><?= esc(lang('UI.patientExaminationsSubtitle')) ?></p>
                                        </div>
                                    </div>
                                    <div class="patient-exam-header-actions">
                                        <div class="patient-exam-count">
                                            <strong><?= count($examinations) ?></strong>
                                            <span><?= esc(lang('UI.patientTabExams')) ?></span>
                                        </div>
                                        <?php if (has_permission('examinations', 'create')): ?>
                                            <button type="button" data-app-drawer-open="patientExamDrawer" class="patient-exam-create-action">
                                                <i class="fas fa-plus"></i>
                                                <span><?= esc(lang('UI.patientCreateExamination')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($examinations)): ?>
                                    <div class="patient-exam-list" data-patient-record-list>
                                        <?php foreach ($examinations as $examination): ?>
                                            <?php
                                            $examStatusKey = strtolower((string) ($examination['status'] ?? ''));
                                            $examStatusLabel = match ($examStatusKey) {
                                                'completed' => lang('UI.patientTreatmentStatusCompleted'),
                                                'in_progress' => lang('UI.patientTreatmentStatusInProgress'),
                                                'pending' => lang('UI.patientStatusPending'),
                                                'active' => lang('UI.patientStatusActive'),
                                                'cancelled' => lang('UI.patientStatusInactive'),
                                                default => $examStatusKey !== '' ? ucfirst(str_replace('_', ' ', $examStatusKey)) : lang('UI.patientUnknown'),
                                            };
                                            $examStatusClass = $examStatusKey === 'completed' ? 'patient-exam-status-complete' : 'patient-exam-status-open';
                                            $examTypeRaw = trim((string) ($examination['examination_type'] ?? ''));
                                            $examTypeLabel = $examTypeRaw !== '' ? ucfirst(str_replace('_', ' ', $examTypeRaw)) : lang('UI.patientGeneral');
                                            $examComplaint = trim((string) ($examination['chief_complaint'] ?? ''));
                                            $examComplaintPreview = $examComplaint !== ''
                                                ? (function_exists('mb_strimwidth') ? mb_strimwidth($examComplaint, 0, 130, '...') : (strlen($examComplaint) > 127 ? substr($examComplaint, 0, 127) . '...' : $examComplaint))
                                                : lang('UI.patientNoComplaint');
                                            $examDoctorName = trim(((string) ($examination['doctor_first_name'] ?? '')) . ' ' . ((string) ($examination['doctor_last_name'] ?? '')));
                                            $examDateIso = !empty($examination['examination_date']) ? substr((string) $examination['examination_date'], 0, 10) : '';
                                            $examUrl = workflow_context_url('examination/' . $examination['id'], $patientContextReturnTo);
                                            ?>
                                            <article class="patient-exam-card patient-record-row" data-href="<?= esc($examUrl) ?>" data-date="<?= esc($examDateIso, 'attr') ?>">
                                                <div class="patient-exam-date-block" data-filter-cell>
                                                    <span class="patient-exam-date-icon"><i class="fas fa-calendar-day"></i></span>
                                                    <span class="patient-exam-date-text"><?= esc(formatDate($examination['examination_date'])) ?></span>
                                                </div>
                                                <div class="patient-exam-card-main">
                                                    <div class="patient-exam-card-title-row">
                                                        <div class="min-w-0" data-filter-cell>
                                                            <h4><?= esc($examTypeLabel) ?></h4>
                                                            <p><?= esc(lang('UI.patientExam')) ?></p>
                                                        </div>
                                                        <span class="patient-exam-status <?= esc($examStatusClass) ?>" data-filter-cell><?= esc($examStatusLabel) ?></span>
                                                    </div>
                                                    <div class="patient-exam-complaint" data-filter-cell>
                                                        <i class="fas fa-comment-medical"></i>
                                                        <span><?= esc($examComplaintPreview) ?></span>
                                                    </div>
                                                    <?php if ($examDoctorName !== ''): ?>
                                                        <div class="patient-exam-fact" data-filter-cell>
                                                            <i class="fas fa-user-md"></i>
                                                            <span><?= esc($examDoctorName) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <a href="<?= esc($examUrl) ?>" class="patient-exam-action" title="<?= esc(lang('UI.patientView')) ?>">
                                                    <i class="fas fa-arrow-right rtl:rotate-180"></i>
                                                </a>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="patient-filter-empty hidden text-center py-10">
                                        <i class="fas fa-search text-slate-300 text-4xl mb-3"></i>
                                        <p class="text-sm font-semibold text-slate-500"><?= esc(lang('UI.patientNoExaminations')) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="patient-exam-empty text-center py-10">
                                        <i class="fas fa-stethoscope text-slate-300 text-4xl mb-3"></i>
                                        <p class="text-sm font-semibold text-slate-500"><?= esc(lang('UI.patientNoExaminations')) ?></p>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>

                        <!-- Appointments Tab -->
                        <div id="appointments-content" class="tab-content hidden">
                            <section class="relative overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-white via-blue-50/65 to-emerald-50/60 shadow-xl shadow-slate-200/60">
                                <div class="pointer-events-none absolute inset-0 opacity-90">
                                    <div class="absolute -top-24 end-10 h-52 w-52 rounded-full bg-blue-400/25 blur-3xl"></div>
                                    <div class="absolute bottom-0 start-6 h-48 w-48 rounded-full bg-emerald-300/25 blur-3xl"></div>
                                </div>
                                <div class="relative border-b border-white/70 px-4 py-4 sm:px-6">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex items-start gap-3">
                                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg shadow-blue-500/25">
                                                <i class="fas fa-calendar-check"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-lg font-extrabold text-slate-950"><?= esc(lang('UI.patientAppointments')) ?></h3>
                                                    <span class="inline-flex items-center rounded-full bg-white/80 px-2.5 py-1 text-xs font-bold text-slate-600 shadow-sm ring-1 ring-slate-200/70"><?= count($appointments) ?> <?= esc(lang('UI.patientTabAppts')) ?></span>
                                                </div>
                                                <p class="mt-1 max-w-2xl text-sm font-medium text-slate-600"><?= esc(lang('UI.patientAppointmentsSubtitle')) ?></p>
                                            </div>
                                        </div>
                                        <?php if (has_permission('appointments', 'create')): ?>
                                            <button type="button" data-app-drawer-open="patientAppointmentDrawer" class="patient-appointment-create-action">
                                                <i class="fas fa-clock"></i>
                                                <span><?= esc(lang('UI.patientScheduleAppointment')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($appointments)): ?>
                                    <div class="relative px-4 py-5 sm:px-6">
                                        <div class="overflow-x-auto rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                            <table class="patient-record-table w-full min-w-full" data-record-type="appointments" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead class="bg-gradient-to-r from-slate-50 via-blue-50/70 to-emerald-50/70">
                                                    <tr>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientDateTime')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientType')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientDuration')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-200/70 bg-white/95">
                                                    <?php foreach ($appointments as $apt): ?>
                                                        <?php
                                                        $appointmentDateKey = !empty($apt['appointment_date']) ? substr((string) $apt['appointment_date'], 0, 10) : '';
                                                        $appointmentClinical = $appointmentDateKey !== '' ? ($sameDayClinicalByDate[$appointmentDateKey] ?? null) : null;
                                                        $appointmentClinicalCount = !empty($appointmentClinical)
                                                            ? count($appointmentClinical['examinations'] ?? []) + count($appointmentClinical['treatments'] ?? [])
                                                            : 0;
                                                        $appointmentStatusKey = strtolower((string) ($apt['status'] ?? ''));
                                                        $appointmentStatusLabel = match ($appointmentStatusKey) {
                                                            'scheduled' => lang('UI.appointmentStatusScheduled'),
                                                            'confirmed' => lang('UI.appointmentStatusConfirmed'),
                                                            'cancelled' => lang('UI.appointmentStatusCancelled'),
                                                            'no_show' => lang('UI.appointmentStatusNoShow'),
                                                            'completed' => lang('UI.patientTreatmentStatusCompleted'),
                                                            default => $appointmentStatusKey !== '' ? ucfirst(str_replace('_', ' ', $appointmentStatusKey)) : lang('UI.patientUnknown'),
                                                        };
                                                        $appointmentStatusClass = [
                                                            'scheduled' => 'bg-blue-100 text-blue-800 ring-blue-200/70',
                                                            'confirmed' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/70',
                                                            'completed' => 'bg-slate-100 text-slate-800 ring-slate-200/70',
                                                            'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200/70',
                                                            'no_show' => 'bg-amber-100 text-amber-800 ring-amber-200/70',
                                                        ][$appointmentStatusKey] ?? 'bg-slate-100 text-slate-700 ring-slate-200/70';
                                                        $appointmentTypeRaw = trim((string) ($apt['appointment_type'] ?? ''));
                                                        $appointmentTypeLabel = $appointmentTypeRaw !== '' ? ucfirst(str_replace('_', ' ', $appointmentTypeRaw)) : lang('UI.patientGeneral');
                                                        $appointmentChairLabel = trim((string) ($apt['chair_name'] ?? '')) !== '' ? (string) $apt['chair_name'] : lang('UI.appointmentChairDefault');
                                                        $appointmentUrl = workflow_context_url('appointment/' . $apt['id'], $patientContextReturnTo);
                                                        ?>
                                                        <tr class="patient-record-row transition-colors hover:bg-blue-50/70" data-href="<?= esc($appointmentUrl) ?>" data-date="<?= esc($appointmentDateKey, 'attr') ?>">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                                                <div class="font-extrabold text-slate-950"><?= esc(formatDate($apt['appointment_date'])) ?></div>
                                                                <div class="mt-1 text-xs font-bold text-blue-700"><?= esc(lang('UI.patientAt')) ?> <?= esc(formatTime($apt['appointment_time'])) ?></div>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <div class="patient-record-title text-sm font-extrabold text-slate-950"><?= esc($appointmentTypeLabel) ?></div>
                                                                <div class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-800 ring-1 ring-indigo-100">
                                                                    <i class="fas fa-chair text-[10px]"></i>
                                                                    <?= esc($appointmentChairLabel) ?>
                                                                </div>
                                                                <?php if ($appointmentClinicalCount > 0): ?>
                                                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-semibold">
                                                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2.5 py-1 text-sky-800 ring-1 ring-sky-100">
                                                                            <i class="fas fa-link text-sky-600"></i>
                                                                            <span><?= esc(lang('UI.patientSameDayClinicalWork')) ?>: <?= esc((string) $appointmentClinicalCount) ?></span>
                                                                        </span>
                                                                        <?php foreach (array_slice(($appointmentClinical['examinations'] ?? []), 0, 1) as $clinicalItem): ?>
                                                                            <a href="<?= esc($clinicalItem['url']) ?>" class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-blue-800 ring-1 ring-blue-100 hover:bg-blue-100">
                                                                                <i class="fas fa-stethoscope"></i>
                                                                                <span><?= esc($clinicalItem['label']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                        <?php foreach (array_slice(($appointmentClinical['treatments'] ?? []), 0, 1) as $clinicalItem): ?>
                                                                            <a href="<?= esc($clinicalItem['url']) ?>" class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-rose-800 ring-1 ring-rose-100 hover:bg-rose-100">
                                                                                <i class="fas fa-tooth"></i>
                                                                                <span><?= esc($clinicalItem['label']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 ring-1 ring-slate-200">
                                                                    <i class="fas fa-hourglass-half text-slate-500"></i>
                                                                    <?= esc($apt['duration'] ?? '30') ?> <?= esc(lang('UI.patientMinutes')) ?>
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 <?= esc($appointmentStatusClass) ?>">
                                                                    <?= esc($appointmentStatusLabel) ?>
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                                <a href="<?= esc($appointmentUrl) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="relative px-6 py-8">
                                        <div class="rounded-2xl border border-dashed border-blue-200 bg-white/70 px-6 py-12 text-center shadow-inner">
                                            <i class="fas fa-calendar-times mb-3 text-4xl text-blue-200"></i>
                                            <p class="text-sm font-semibold text-slate-500"><?= esc(lang('UI.patientNoAppointments')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        <!-- Treatments Tab -->
                        <div id="treatments-content" class="tab-content hidden">
                            <section class="relative overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-white via-rose-50/55 to-cyan-50/60 shadow-xl shadow-slate-200/60">
                                <div class="pointer-events-none absolute inset-0 opacity-80">
                                    <div class="absolute -top-24 end-10 h-52 w-52 rounded-full bg-pink-300/25 blur-3xl"></div>
                                    <div class="absolute bottom-0 start-6 h-48 w-48 rounded-full bg-cyan-300/25 blur-3xl"></div>
                                </div>
                                <div class="relative border-b border-white/70 px-4 py-4 sm:px-6">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex items-start gap-3">
                                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-600 to-rose-700 text-white shadow-lg shadow-pink-500/25">
                                                <i class="fas fa-tooth"></i>
                                            </span>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-lg font-extrabold text-slate-950"><?= esc(lang('UI.patientTreatments')) ?></h3>
                                                    <span class="inline-flex items-center rounded-full bg-white/80 px-2.5 py-1 text-xs font-bold text-slate-600 shadow-sm ring-1 ring-slate-200/70"><?= count($treatments) ?> <?= esc(lang('UI.patientTreatments')) ?></span>
                                                </div>
                                                <p class="mt-1 max-w-2xl text-sm font-medium text-slate-600"><?= esc(lang('UI.patientBilling')) ?> / <?= esc(lang('UI.patientStatus')) ?> / <?= esc(lang('Finance.provider')) ?></p>
                                            </div>
                                        </div>
                                        <?php if (has_permission('treatments', 'create')): ?>
                                            <button type="button" data-treatment-create-modal-open data-context-patient-id="<?= (int) $patient['id'] ?>" data-context-patient-name="<?= esc($fullName !== '' ? $fullName : ($patient['patient_id'] ?? ''), 'attr') ?>" data-context-patient-code="<?= esc((string) ($patient['patient_id'] ?? ''), 'attr') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-pink-600 to-rose-700 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-pink-500/25 transition hover:-translate-y-0.5 hover:from-pink-700 hover:to-rose-800 focus:outline-none focus:ring-4 focus:ring-pink-500/20">
                                                <i class="fas fa-plus"></i>
                                                <span><?= esc(lang('UI.patientCreateTreatmentPlan')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="relative px-4 py-4 sm:px-6">
                                    <div class="grid gap-3 md:grid-cols-3">
                                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white shadow-lg shadow-amber-500/20">
                                            <div class="absolute -end-8 -top-8 h-24 w-24 rounded-full bg-white/20 blur-2xl"></div>
                                            <div class="relative flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-bold uppercase tracking-wide text-amber-50/90"><?= esc(lang('UI.patientUnbilledWork')) ?></p>
                                                    <p class="mt-2 text-2xl font-black" dir="ltr"><?= formatCurrency($openClinicalChargeTotal) ?></p>
                                                    <p class="mt-1 text-xs font-semibold text-amber-50/90"><?= esc($openClinicalChargeCount) ?> <?= esc(lang('UI.patientTreatments')) ?></p>
                                                </div>
                                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20 text-white"><i class="fas fa-file-invoice-dollar"></i></span>
                                            </div>
                                        </div>
                                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-4 text-white shadow-lg shadow-emerald-500/20">
                                            <div class="absolute -end-8 -top-8 h-24 w-24 rounded-full bg-white/20 blur-2xl"></div>
                                            <div class="relative flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-50/90"><?= esc(lang('UI.patientBilledPaid')) ?></p>
                                                    <p class="mt-2 text-2xl font-black"><?= esc($paidBilledTreatmentCount) ?></p>
                                                    <p class="mt-1 text-xs font-semibold text-emerald-50/90"><?= esc(lang('UI.patientPaidShort')) ?></p>
                                                </div>
                                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20 text-white"><i class="fas fa-circle-check"></i></span>
                                            </div>
                                        </div>
                                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-4 text-white shadow-lg shadow-blue-500/20">
                                            <div class="absolute -end-8 -top-8 h-24 w-24 rounded-full bg-white/20 blur-2xl"></div>
                                            <div class="relative flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-bold uppercase tracking-wide text-blue-50/90"><?= esc(lang('UI.patientBilledWithBalance')) ?></p>
                                                    <p class="mt-2 text-2xl font-black"><?= esc($unpaidBilledTreatmentCount) ?></p>
                                                    <p class="mt-1 text-xs font-semibold text-blue-50/90"><?= esc(lang('UI.patientBalanceShort')) ?></p>
                                                </div>
                                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20 text-white"><i class="fas fa-scale-balanced"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($treatments)): ?>
                                    <div class="relative space-y-3 px-4 pb-5 sm:px-6">
                                        <?php foreach ($treatments as $trt): ?>
                                            <?php
                                            $rowBilling = $treatmentBillingMeta($trt);
                                            $treatmentId = (int) ($trt['id'] ?? 0);
                                            $treatmentUrl = workflow_context_url('treatment/' . $treatmentId, $patientContextReturnTo);
                                            $treatmentStatus = strtolower((string) ($trt['status'] ?? ''));
                                            $treatmentStatusLabel = match ($treatmentStatus) {
                                                'active' => lang('UI.patientStatusActive'),
                                                'completed' => lang('UI.patientTreatmentStatusCompleted'),
                                                'pending' => lang('UI.patientTreatmentStatusPending'),
                                                'in_progress' => lang('UI.patientTreatmentStatusInProgress'),
                                                'needs_attention' => lang('UI.patientTreatmentStatusNeedsAttention'),
                                                'on_hold' => lang('UI.patientStatusPending'),
                                                'cancelled' => lang('UI.patientStatusInactive'),
                                                default => $treatmentStatus !== '' ? ucfirst(str_replace('_', ' ', $treatmentStatus)) : lang('UI.patientNa'),
                                            };
                                            $statusClass = match ($treatmentStatus) {
                                                'active', 'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200/70',
                                                'completed' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/70',
                                                'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200/70',
                                                'on_hold', 'pending', 'needs_attention' => 'bg-amber-100 text-amber-800 ring-amber-200/70',
                                                default => 'bg-slate-100 text-slate-700 ring-slate-200/70',
                                            };
                                            $doctorName = trim((string) (($trt['doctor_first_name'] ?? '') . ' ' . ($trt['doctor_last_name'] ?? '')));
                                            if ($doctorName === '') {
                                                $doctorName = lang('UI.patientUnknownDoctor');
                                            }
                                            $treatmentType = trim((string) ($trt['treatment_type'] ?? ''));
                                            $treatmentTypeLabel = $treatmentType !== '' ? ucfirst(str_replace('_', ' ', $treatmentType)) : lang('UI.patientGeneral');
                                            $billTreatmentFromPatientUrl = '';
                                            if (
                                                has_permission('finance', 'create')
                                                && !$rowBilling['is_billed']
                                                && (int) ($rowBilling['charge_line_id'] ?? 0) > 0
                                                && (float) ($rowBilling['amount_raw'] ?? 0) > 0
                                            ) {
                                                $billTreatmentFromPatientUrl = workflow_context_url(
                                                    'finance/create?' . http_build_query([
                                                        'patient_id' => (int) $patient['id'],
                                                        'transaction_type' => 'invoice',
                                                        'clinical_charge_line_ids' => [(int) $rowBilling['charge_line_id']],
                                                    ], '', '&', PHP_QUERY_RFC3986),
                                                    'patient/' . (int) $patient['id'] . '?tab=treatments'
                                                );
                                            }
                                            ?>
                                            <article class="patient-record-row group relative overflow-hidden rounded-2xl border border-white/80 bg-white/90 p-4 shadow-md shadow-slate-200/70 ring-1 ring-slate-100 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-pink-100/70 sm:p-5" data-href="<?= esc($treatmentUrl) ?>">
                                                <div class="absolute inset-y-0 start-0 w-1.5 bg-gradient-to-b from-pink-500 via-rose-500 to-cyan-500"></div>
                                                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.9fr)_auto] xl:items-center">
                                                    <div class="min-w-0 ps-2">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <h4 class="truncate text-base font-extrabold text-slate-950"><?= esc($trt['treatment_name'] ?? lang('UI.patientNa')) ?></h4>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 <?= esc($statusClass) ?>"><?= esc($treatmentStatusLabel) ?></span>
                                                        </div>
                                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-pink-50 px-2.5 py-1 text-pink-800 ring-1 ring-pink-100"><i class="fas fa-tag text-[10px]"></i><?= esc($treatmentTypeLabel) ?></span>
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-slate-700 ring-1 ring-slate-100"><i class="fas fa-tooth text-[10px]"></i><?= esc(lang('UI.patientTooth')) ?>: <span dir="ltr"><?= esc($trt['tooth_number'] ?? '—') ?></span></span>
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-2.5 py-1 text-cyan-800 ring-1 ring-cyan-100"><i class="fas fa-user-doctor text-[10px]"></i><?= esc($doctorName) ?></span>
                                                            <?php if (!empty($trt['start_date'])): ?>
                                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-indigo-800 ring-1 ring-indigo-100"><i class="fas fa-calendar text-[10px]"></i><span dir="ltr"><?= esc(formatDate($trt['start_date'])) ?></span></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($trt['treatment_description'])): ?>
                                                            <p class="mt-3 text-sm font-medium leading-6 text-slate-600"><?= esc($trt['treatment_description']) ?></p>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-inner">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div>
                                                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.patientBilling')) ?></p>
                                                                <p class="mt-1 text-lg font-black text-slate-950" dir="ltr"><?= esc($rowBilling['amount_text']) ?></p>
                                                            </div>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold <?= esc($rowBilling['status_class']) ?>"><?= esc($rowBilling['status_text']) ?></span>
                                                        </div>
                                                        <?php if (!empty($rowBilling['finance_id'])): ?>
                                                            <div class="mt-3 grid gap-2 text-xs font-semibold text-slate-600 sm:grid-cols-2">
                                                                <a href="<?= esc(workflow_context_url('finance/' . (int) $rowBilling['finance_id'], $patientContextReturnTo)) ?>" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-2.5 py-2 text-emerald-800 ring-1 ring-emerald-100 hover:bg-emerald-100">
                                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                                    <span class="truncate"><?= esc($rowBilling['invoice_number'] !== '' ? $rowBilling['invoice_number'] : lang('Finance.invoice')) ?></span>
                                                                </a>
                                                                <?php if ($rowBilling['invoice_status_text'] !== ''): ?>
                                                                    <span class="inline-flex items-center justify-center rounded-xl px-2.5 py-2 font-bold <?= esc($rowBilling['invoice_status_class']) ?>"><?= esc($rowBilling['invoice_status_text']) ?></span>
                                                                <?php endif; ?>
                                                                <span><?= esc(lang('UI.patientPaidShort')) ?>: <strong dir="ltr"><?= esc($rowBilling['paid_text']) ?></strong></span>
                                                                <span class="<?= $rowBilling['has_balance'] ? 'text-amber-700' : 'text-emerald-700' ?>"><?= esc(lang('UI.patientBalanceShort')) ?>: <strong dir="ltr"><?= esc($rowBilling['balance_text']) ?></strong></span>
                                                            </div>
                                                        <?php elseif ($billTreatmentFromPatientUrl !== ''): ?>
                                                            <button type="button"
                                                                    data-patient-charge-line-open
                                                                    data-charge-line-id="<?= (int) $rowBilling['charge_line_id'] ?>"
                                                                    class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-3 py-2.5 text-xs font-extrabold text-white shadow-md shadow-amber-500/20 transition hover:from-amber-600 hover:to-orange-700">
                                                                <i class="fas fa-file-invoice-dollar"></i>
                                                                <?= esc(lang('UI.patientCreateInvoiceFromWork')) ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="flex shrink-0 items-center justify-end gap-2">
                                                        <a href="<?= esc($treatmentUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-pink-50 text-pink-700 ring-1 ring-pink-100 transition hover:bg-pink-100" title="<?= esc(lang('UI.patientView')) ?>">
                                                            <i class="fas fa-eye text-sm"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="relative px-6 pb-8">
                                        <div class="rounded-2xl border border-dashed border-pink-200 bg-white/70 px-6 py-12 text-center shadow-inner">
                                            <i class="fas fa-tooth mb-3 text-4xl text-pink-200"></i>
                                            <p class="text-sm font-semibold text-slate-500"><?= esc(lang('UI.patientNoTreatments')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        <!-- Prescriptions Tab -->
                        <div id="prescriptions-content" class="tab-content hidden">
                            <section class="relative overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-white via-teal-50/65 to-blue-50/60 shadow-xl shadow-slate-200/60">
                                <div class="pointer-events-none absolute inset-0 opacity-90">
                                    <div class="absolute -top-24 end-10 h-52 w-52 rounded-full bg-teal-400/25 blur-3xl"></div>
                                    <div class="absolute bottom-0 start-6 h-48 w-48 rounded-full bg-blue-400/25 blur-3xl"></div>
                                </div>
                                <div class="relative border-b border-white/70 px-4 py-4 sm:px-6">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex items-start gap-3">
                                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-600 to-blue-700 text-white shadow-lg shadow-teal-500/25">
                                                <i class="fas fa-prescription-bottle-medical"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-lg font-extrabold text-slate-950"><?= esc(lang('UI.patientPrescriptions')) ?></h3>
                                                    <span class="inline-flex items-center rounded-full bg-white/80 px-2.5 py-1 text-xs font-bold text-slate-600 shadow-sm ring-1 ring-slate-200/70"><?= count($prescriptions) ?> <?= esc(lang('UI.patientTabRx')) ?></span>
                                                </div>
                                                <p class="mt-1 max-w-2xl text-sm font-medium text-slate-600"><?= esc(lang('UI.patientPrescriptionsSubtitle')) ?></p>
                                            </div>
                                        </div>
                                        <?php if (has_permission('prescriptions', 'create')): ?>
                                            <a href="<?= workflow_context_url('prescription/create?patient_id=' . $patient['id'], $patientContextReturnTo) ?>"
                                               class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-teal-500/20 transition hover:-translate-y-0.5 hover:from-teal-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-teal-500/20">
                                                <i class="fas fa-plus"></i>
                                                <span><?= esc(lang('UI.patientCreatePrescription')) ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($prescriptions)): ?>
                                    <div class="relative px-4 py-5 sm:px-6">
                                        <div class="overflow-x-auto rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                            <table class="patient-record-table w-full min-w-full" data-record-type="prescriptions" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead class="bg-gradient-to-r from-slate-50 via-teal-50/70 to-blue-50/70">
                                                    <tr>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientMedication')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientDosage')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientFrequency')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientPrescribedDate')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-600"><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-200/70 bg-white/95">
                                                    <?php foreach ($prescriptions as $prescription): ?>
                                                        <?php
                                                        $medicationName = lang('UI.patientNa');
                                                        $dosage = lang('UI.patientNa');
                                                        $frequency = lang('UI.patientNa');

                                                        try {
                                                            $medicinesData = json_decode((string) ($prescription['medication_name'] ?? ''), true);
                                                            if (is_array($medicinesData) && !empty($medicinesData)) {
                                                                $medicineNames = [];
                                                                $dosages = [];
                                                                $frequencies = [];

                                                                foreach ($medicinesData as $medicine) {
                                                                    if (!empty($medicine['name'])) {
                                                                        $medicineNames[] = (string) $medicine['name'];
                                                                    }
                                                                    if (!empty($medicine['dosage'])) {
                                                                        $dosages[] = (string) $medicine['dosage'];
                                                                    }
                                                                    if (!empty($medicine['frequency'])) {
                                                                        $frequencies[] = (string) $medicine['frequency'];
                                                                    }
                                                                }

                                                                $medicationName = !empty($medicineNames) ? implode(', ', $medicineNames) : lang('UI.patientNa');
                                                                $dosage = !empty($dosages) ? implode(', ', $dosages) : lang('UI.patientNa');
                                                                $frequency = !empty($frequencies) ? implode(', ', $frequencies) : lang('UI.patientNa');
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

                                                        $prescriptionStatusKey = strtolower((string) ($prescription['status'] ?? ''));
                                                        $prescriptionStatusLabel = match ($prescriptionStatusKey) {
                                                            'active' => lang('UI.patientStatusActive'),
                                                            'pending' => lang('UI.patientStatusPending'),
                                                            'cancelled' => lang('UI.patientStatusInactive'),
                                                            default => $prescriptionStatusKey !== '' ? ucfirst(str_replace('_', ' ', $prescriptionStatusKey)) : lang('UI.patientUnknown'),
                                                        };
                                                        $prescriptionStatusClass = match ($prescriptionStatusKey) {
                                                            'active' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/70',
                                                            'pending' => 'bg-amber-100 text-amber-800 ring-amber-200/70',
                                                            'expired', 'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200/70',
                                                            default => 'bg-slate-100 text-slate-700 ring-slate-200/70',
                                                        };
                                                        $prescriptionUrl = workflow_context_url('prescription/' . $prescription['id'], $patientContextReturnTo);
                                                        $prescribedDateIso = !empty($prescription['prescribed_date']) ? substr((string) $prescription['prescribed_date'], 0, 10) : '';
                                                        ?>
                                                        <tr class="patient-record-row transition-colors hover:bg-teal-50/70" data-href="<?= esc($prescriptionUrl) ?>" data-date="<?= esc($prescribedDateIso, 'attr') ?>">
                                                            <td class="px-6 py-4">
                                                                <div class="flex items-center gap-3">
                                                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700 ring-1 ring-teal-100">
                                                                        <i class="fas fa-pills text-sm"></i>
                                                                    </span>
                                                                    <div class="min-w-0">
                                                                        <div class="patient-record-title truncate text-sm font-extrabold text-slate-950"><?= esc($medicationName) ?></div>
                                                                        <div class="mt-1 text-xs font-bold text-teal-700"><?= esc(lang('UI.patientMedicationDetails')) ?></div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm font-semibold text-slate-700"><?= esc($dosage) ?></td>
                                                            <td class="px-6 py-4 text-sm font-semibold text-slate-700"><?= esc($frequency) ?></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-700">
                                                                <?= !empty($prescription['prescribed_date']) ? esc(formatDate($prescription['prescribed_date'])) : esc(lang('UI.patientNa')) ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 <?= esc($prescriptionStatusClass) ?>">
                                                                    <?= esc($prescriptionStatusLabel) ?>
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                                <a href="<?= esc($prescriptionUrl) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="relative px-6 py-8">
                                        <div class="rounded-2xl border border-dashed border-teal-200 bg-white/70 px-6 py-12 text-center shadow-inner">
                                            <i class="fas fa-prescription-bottle-medical mb-3 text-4xl text-teal-200"></i>
                                            <p class="text-sm font-semibold text-slate-500"><?= esc(lang('UI.patientNoPrescriptions')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        <!-- Financials Tab -->
                        <div id="finances-content" class="tab-content hidden" role="tabpanel" aria-labelledby="finances-tab" hidden>
                            <section class="patient-finance-workspace relative overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-white via-emerald-50/65 to-amber-50/55 p-4 shadow-xl shadow-slate-200/60 sm:p-6"><div class="pointer-events-none absolute inset-0 opacity-80"><div class="absolute -top-24 end-10 h-52 w-52 rounded-full bg-emerald-400/20 blur-3xl"></div><div class="absolute bottom-0 start-6 h-48 w-48 rounded-full bg-amber-300/20 blur-3xl"></div></div><div class="relative space-y-5">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <?php if (has_permission('finance', 'create')): ?>
                                        <button type="button" data-app-drawer-open="patientPaymentDrawer" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white/90 px-4 py-2.5 text-xs font-extrabold text-emerald-800 shadow-sm transition hover:bg-emerald-50">
                                            <i class="fas fa-hand-holding-dollar"></i><?= esc(lang('UI.recordPayment')) ?>
                                        </button>
                                        <button type="button" data-app-drawer-open="patientChargeDrawer" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-emerald-500/20 transition hover:from-emerald-700 hover:to-teal-800">
                                            <i class="fas fa-plus"></i><?= esc(lang('UI.patientCreateInvoice')) ?>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="patient-finance-summary grid gap-3 md:grid-cols-4">
                                    <div class="patient-finance-balance-card relative overflow-hidden rounded-2xl <?= $outstandingBalance > 0.009 ? 'bg-gradient-to-br from-amber-500 to-orange-600 shadow-amber-500/25' : 'bg-gradient-to-br from-sky-600 to-blue-700 shadow-sky-500/25' ?> p-5 text-white shadow-lg">
                                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_0%,rgba(255,255,255,0.24),transparent_34%)]"></div>
                                        <div class="relative">
                                            <p class="text-xs font-bold uppercase tracking-wide text-white/90"><?= esc(lang('UI.patientOutstandingBalance')) ?></p>
                                            <p class="mt-2 text-3xl font-black text-white"><?= $outstandingBalance > 0.009 ? formatCurrency($outstandingBalance) : esc(lang('UI.patientNoBalanceDue')) ?></p>
                                        </div>
                                    </div>
                                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 to-slate-950 p-4 text-white shadow-lg shadow-slate-500/25">
                                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_0%,rgba(255,255,255,0.18),transparent_32%)]"></div>
                                        <div class="relative">
                                            <p class="text-xs font-bold uppercase tracking-wide text-slate-100/90"><?= esc(lang('UI.patientTotalBilled')) ?></p>
                                            <p class="mt-2 text-xl font-black text-white"><?= formatCurrency($totalCharges) ?></p>
                                        </div>
                                    </div>
                                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-800 p-4 text-white shadow-lg shadow-emerald-500/25">
                                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_0%,rgba(255,255,255,0.20),transparent_32%)]"></div>
                                        <div class="relative">
                                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-50/90"><?= esc(lang('UI.patientTotalPaid')) ?></p>
                                            <p class="mt-2 text-xl font-black text-white"><?= formatCurrency($totalCollected) ?></p>
                                        </div>
                                    </div>
                                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-600 to-pink-800 p-4 text-white shadow-lg shadow-rose-500/25">
                                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_0%,rgba(255,255,255,0.20),transparent_32%)]"></div>
                                        <div class="relative">
                                            <p class="text-xs font-bold uppercase tracking-wide text-rose-50/90"><?= esc(lang('Finance.refunds')) ?></p>
                                            <p class="mt-2 text-xl font-black text-white"><?= formatCurrency($totalRefunded) ?></p>
                                        </div>
                                    </div>

                                </div>
                                <?php if (!empty($financialLedgerRows)): ?>
                                    <div class="overflow-hidden rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                        <div class="overflow-x-auto">
                                            <table class="patient-record-table" data-record-type="finances" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead class="bg-gradient-to-r from-slate-50 via-emerald-50/70 to-amber-50/70">
                                                    <tr>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientDate')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientType')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('Finance.detailsReference')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientClinicalSource')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('Finance.amount')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('Finance.method')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-600 uppercase tracking-wider"><?= esc(lang('UI.patientActions')) ?></th>
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
                                                            <td>
                                                                <?php if (!empty($ledgerRow['clinical_sources'])): ?>
                                                                    <div class="flex flex-wrap gap-1.5">
                                                                        <?php foreach ($ledgerRow['clinical_sources'] as $clinicalSource): ?>
                                                                            <?php $clinicalSourceLabel = trim((string) ($clinicalSource['label'] ?? '')); ?>
                                                                            <?php if ($clinicalSourceLabel === '') { continue; } ?>
                                                                            <?php if (!empty($clinicalSource['url'])): ?>
                                                                                <a href="<?= esc($clinicalSource['url']) ?>" class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-800 hover:bg-sky-100">
                                                                                    <i class="<?= esc($clinicalSource['icon'] ?? 'fas fa-link') ?>"></i>
                                                                                    <span><?= esc($clinicalSourceLabel) ?></span>
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                                                                                    <i class="<?= esc($clinicalSource['icon'] ?? 'fas fa-link') ?>"></i>
                                                                                    <span><?= esc($clinicalSourceLabel) ?></span>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-xs text-slate-400"><?= esc(lang('UI.patientNoClinicalSource')) ?></span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($ledgerRow['provider_payouts'])): ?>
                                                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                                                        <?php foreach ($ledgerRow['provider_payouts'] as $providerPayout): ?>
                                                                            <a href="<?= esc($providerPayout['url']) ?>" class="inline-flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-800 hover:bg-violet-100">
                                                                                <i class="fas fa-user-doctor"></i>
                                                                                <span><?= esc(lang('UI.patientProviderCompensation')) ?>: <?= esc($providerPayout['provider_name']) ?></span>
                                                                                <span dir="ltr"><?= esc($providerPayout['share_text']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="font-semibold <?= $amountClass ?>">
                                                                <?= ($rowAmount < 0 ? '- ' : '') . formatCurrency(abs($rowAmount)) ?>
                                                            </td>
                                                            <td><?= esc($ledgerRow['method_label']) ?></td>
                                                            <td class="whitespace-nowrap">
                                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 <?= $colorClass ?>">
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
                                <div class="rounded-2xl border border-dashed border-emerald-200 bg-white/70 px-6 py-12 text-center shadow-inner">
                                    <i class="fas fa-file-invoice mb-3 text-4xl text-emerald-200"></i>
                                    <p class="text-sm font-semibold text-slate-500"><?= esc(lang('UI.patientNoFinancialRecords')) ?></p>
                                </div>
                                <?php endif; ?>
                                <details class="patient-finance-trace overflow-hidden rounded-2xl border border-white/80 bg-white/90 shadow-md shadow-slate-200/70 ring-1 ring-slate-100">
                                    <summary class="flex cursor-pointer items-center justify-between gap-3 bg-gradient-to-r from-slate-50 via-emerald-50/70 to-amber-50/70 px-5 py-4 text-sm font-extrabold text-slate-900">
                                        <span class="inline-flex items-center gap-2"><i class="fas fa-route text-emerald-700"></i><?= esc(lang('UI.moneyTrailTitle')) ?></span>
                                        <span class="inline-flex items-center gap-2 text-xs font-bold text-slate-500">
                                            <?= esc(lang('UI.moneyTrailLinkedRecords')) ?>
                                            <i class="fas fa-chevron-down text-slate-400"></i>
                                        </span>
                                    </summary>
                                    <div class="border-t border-slate-100 p-3 sm:p-4">
                                        <?= view('components/money_trail_panel', [
                                            'money_trail' => $money_trail ?? [],
                                            'is_rtl' => $isRtl,
                                            'return_to' => $patientContextReturnTo,
                                        ]) ?>
                                    </div>
                                </details>
                            </div></section>
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


<?php helper('asset'); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/patient-workspace.css') ?>?v=<?= esc(asset_version('assets/css/patient-workspace.css')) ?>">
<style>
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
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(191, 219, 254, 0.7);
        color: #1e3a8a;
        padding: 0.42rem 0.74rem;
        font-size: 0.74rem;
        font-weight: 700;
        box-shadow: 0 10px 18px -18px rgba(37, 99, 235, 0.38);
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
        border: 1px solid rgba(207, 250, 254, 0.82);
        border-radius: 1rem;
        background:
            radial-gradient(circle at 10% 0%, rgba(8, 145, 178, 0.14), transparent 34%),
            linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(236, 254, 255, 0.94));
        padding: 0.98rem 1rem;
        box-shadow: 0 18px 36px -32px rgba(8, 145, 178, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.82);
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
            const metaText = Array.isArray(item.meta)
                ? item.meta.map(metaItem => metaItem && metaItem.text ? String(metaItem.text).toLowerCase() : '').join(' ')
                : '';
            const matchesSearch = !searchTerm || 
                item.title.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm) ||
                item.status.toLowerCase().includes(searchTerm) ||
                (item.doctor && item.doctor.toLowerCase().includes(searchTerm)) ||
                metaText.includes(searchTerm);
                
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
                spanLine.className = 'patient-timeline-rail';
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
            const dateIcon = document.createElement('i');
            dateIcon.className = 'fas fa-calendar-day';
            const dateText = document.createElement('span');
            dateText.textContent = `${patientShowI18n.timelineOn} ${itemDisplay}`;
            dateDiv.appendChild(dateIcon);
            dateDiv.appendChild(dateText);
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

            const timelineFacts = timelineMeta.filter((metaItem) => metaItem && metaItem.text);
            const timelineFactsPreview = timelineFacts.slice(0, 3);
            if (timelineFactsPreview.length > 0) {
                const metaWrap = document.createElement('div');
                metaWrap.className = 'patient-timeline-facts';
                timelineFactsPreview.forEach((metaItem) => {
                    const fact = document.createElement('span');
                    fact.className = 'patient-timeline-fact';
                    if (metaItem.icon) {
                        const metaIcon = document.createElement('i');
                        metaIcon.className = metaItem.icon;
                        fact.appendChild(metaIcon);
                    }
                    const metaText = document.createElement('span');
                    metaText.textContent = metaItem.text;
                    fact.appendChild(metaText);
                    metaWrap.appendChild(fact);
                });
                if (timelineFacts.length > timelineFactsPreview.length) {
                    const more = document.createElement('span');
                    more.className = 'patient-timeline-fact-more';
                    more.textContent = `+${timelineFacts.length - timelineFactsPreview.length}`;
                    metaWrap.appendChild(more);
                }
                summaryWrap.appendChild(metaWrap);
            }
            
            const divStatus = document.createElement('div');
            divStatus.className = 'patient-timeline-status';
            const spanStatus = document.createElement('span');
            spanStatus.className = `patient-timeline-status-label bg-${item.color}-100 text-${item.color}-800`;
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
        
        const tableBody = document.querySelector(`#${tabName}-content table tbody`);
        const cardList = document.querySelector(`#${tabName}-content [data-patient-record-list]`);
        const rowContainer = tableBody || cardList;
        if (!rowContainer) return;
        
        const rows = tableBody ? rowContainer.querySelectorAll('tr') : rowContainer.querySelectorAll('.patient-record-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const cells = tableBody ? row.querySelectorAll('td') : row.querySelectorAll('[data-filter-cell]');
            if (cells.length === 0 && !row.textContent.trim()) return;
            
            let matchesSearch = true;
            let matchesDateRange = true;
            
            // Check search term
            if (searchTerm) {
                const searchableNodes = cells.length > 0 ? Array.from(cells) : [row];
                matchesSearch = searchableNodes.some(cell => 
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
        const noResultsDiv = document.querySelector(`#${tabName}-content .patient-filter-empty`) || document.querySelector(`#${tabName}-content .text-center`);
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
        const chargeLineTrigger = event.target.closest('[data-patient-charge-line-open]');
        if (chargeLineTrigger) {
            event.preventDefault();
            event.stopPropagation();

            const chargeLineId = chargeLineTrigger.getAttribute('data-charge-line-id');
            const drawer = document.getElementById('patientChargeDrawer');
            const form = document.getElementById('patientChargeDrawerForm');
            if (drawer && form && chargeLineId) {
                form.querySelectorAll('[data-clinical-charge-line]').forEach((checkbox) => {
                    checkbox.checked = checkbox.value === chargeLineId;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                });

                if (typeof window.initFinanceCalculators === 'function') {
                    window.initFinanceCalculators(form);
                }
                if (window.AppDrawer && typeof window.AppDrawer.open === 'function') {
                    window.AppDrawer.open('patientChargeDrawer', chargeLineTrigger);
                }
            }

            return;
        }

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
                const labelWrap = document.createElement('span');
                labelWrap.className = 'patient-table-sort-label';
                const headerIcon = document.createElement('i');
                headerIcon.className = iconClass;
                const labelText = document.createElement('span');
                labelText.textContent = label;
                const sortIcon = document.createElement('i');
                sortIcon.className = 'fas fa-sort';
                labelWrap.append(headerIcon, labelText);
                button.append(labelWrap, sortIcon);

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

