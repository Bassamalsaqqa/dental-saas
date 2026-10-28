<?= $this->extend('layouts/studio') ?>
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
$workspaceSummary = $workspace_history['summary'];
$historyCounts = $workspaceSummary['counts'] ?? [];
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

foreach (array_filter([$workspaceSummary['latest']['appointments'] ?? null]) as $appointmentItem) {
    $updateLatest($appointmentItem['appointment_date'] ?? null, $appointmentItem['appointment_type'] ?? lang('UI.patientAppointments'));
}
foreach (array_filter([$workspaceSummary['latest']['examinations'] ?? null]) as $examinationItem) {
    $updateLatest($examinationItem['examination_date'] ?? null, $examinationItem['examination_type'] ?? lang('UI.patientExaminations'));
}
foreach (array_filter([$workspaceSummary['latest']['treatments'] ?? null]) as $treatmentItem) {
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
$todayCutoff = strtotime($workspaceSummary['clinic_today'] ?? date('Y-m-d'));
foreach (array_filter([$workspaceSummary['next_appointment']]) as $appointmentItem) {
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

$activeTreatmentsCount = (int) $workspaceSummary['active_treatments'];

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

$openClinicalChargeCount = (int) $workspaceSummary['clinical_billing']['open_count'];
$openClinicalChargeTotal = (float) $workspaceSummary['clinical_billing']['open_amount'];
$paidBilledTreatmentCount = (int) $workspaceSummary['clinical_billing']['paid_count'];
$unpaidBilledTreatmentCount = (int) $workspaceSummary['clinical_billing']['unpaid_count'];

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

if (isset($workspace_history)) {
    $sameDayClinicalByDate = $workspace_history['same_day'];
    foreach ($sameDayClinicalByDate as &$clinicalDay) {
        foreach (['examinations' => 'examination', 'treatments' => 'treatment'] as $plural => $routeName) {
            foreach ($clinicalDay[$plural] as &$clinicalLink) {
                $clinicalLink['url'] = workflow_context_url($routeName . '/' . $clinicalLink['id'], $patientContextReturnTo);
            }
            unset($clinicalLink);
        }
    }
    unset($clinicalDay);
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
        'value' => $medicalAlertCount > 0 ? (string) $medicalAlertCount : lang('Studio.medicalAssessmentUnconfirmed'),
        'detail' => $medicalAlertCount > 0 ? lang('UI.patientProfileAlertSummary', ['count' => $medicalAlertCount]) : lang('Studio.medicalAssessmentNotice'),
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

<?php
$workspaceHistoryPages = []; $historyNavigation = [];
if (isset($workspace_history)) {
    $historyFilter = $workspace_history['query'];
    $workspaceContext = get_defined_vars();
    foreach ($workspace_history['pages'] as $section => $candidatePage) {
        $context = array_replace($workspaceContext, $workspace_history['rows'][$section]);
        $workspaceHistoryPages[$section] = \App\Services\PatientWorkspaceTimelinePresenter::page(
            $context, $candidatePage['items'], $candidatePage['next'],
            $historyFilter['q'], $historyFilter['start'], $historyFilter['end'], 25, $section !== 'timeline'
        );
        $parameters = ['tab' => $section];
        foreach (['q' => 'history_q', 'start' => 'history_start', 'end' => 'history_end'] as $key => $parameter) {
            if ($historyFilter[$key] !== null && $historyFilter[$key] !== '') { $parameters[$parameter] = $historyFilter[$key]; }
        }
        $historyNavigation[$section] = ['latest' => base_url('patient/' . (int) $patient['id']) . '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986), 'next' => null];
        if ($workspaceHistoryPages[$section]['next'] !== null) {
            $parameters['history_before'] = rtrim(strtr(base64_encode(json_encode($workspaceHistoryPages[$section]['next'], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
            $historyNavigation[$section]['next'] = base_url('patient/' . (int) $patient['id']) . '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        }
    }
    foreach (['examinations', 'appointments', 'treatments'] as $section) {
        $byId = array_column($$section, null, 'id'); $visible = [];
        foreach ($workspaceHistoryPages[$section]['matched_keys'] as $key) { if (isset($byId[$key['id']])) { $visible[] = $byId[$key['id']]; } }
        $$section = $visible;
    }
}
?>

<?php helper('asset'); ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-context-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-context-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-patient-workspace.css') ?>?v=<?= esc(asset_version('assets/css/studio-patient-workspace.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="patient-workspace patient-view" data-patient-return-base="<?= esc('patient/'.(int)$patient['id'],'attr') ?>">
    <div >
        <header class="patient-identity"><div class="patient-identity-name"><span class="patient-identity-avatar"><?= esc(ui_initials($patient['first_name']??'',$patient['last_name']??'','P')) ?></span><div><h1><?= esc($fullName) ?></h1><p><?= esc($patient['patient_id']??'') ?> <span><?= esc($patientStatusLabel) ?></span></p></div></div><div class="patient-identity-actions"><?php if(has_permission('patients','edit')): ?><button type="button" class="button secondary" data-app-drawer-open="patientProfileEditDrawer"><?= esc(lang('UI.patientEditPatient')) ?></button><?php endif; ?><a class="button secondary" href="<?= esc(workflow_back('patients',$return_to??null)) ?>"><?= esc(lang('UI.back')) ?></a></div></header>
<p class="medical-assessment-note" role="note"><?= esc(lang('Studio.medicalAssessmentNotice')) ?></p>
<dl class="patient-identity-facts"><?php foreach($heroInfoCapsules as $capsule):$capsuleValue=(string)($capsule['value']??lang('UI.patientNotProvided'));$capsuleLabel=(string)($capsule['label']??''); ?><div><dt><?= esc($capsuleLabel) ?></dt><dd><?php if(!empty($capsule['is_button'])): ?><button type="button" onclick="openPatientWhatsAppIdentityModal('<?= esc($patientWhatsAppNumber,'js') ?>')"><?= esc($capsuleValue) ?></button><?php else: ?><?= esc($capsuleValue) ?><?php endif; ?></dd></div><?php endforeach; ?><?php foreach($patientHeaderSnapshotCards as $snapshotCard): ?><div><dt><?= esc($snapshotCard['label']) ?></dt><dd title="<?= esc($snapshotCard['detail'],'attr') ?>"><?= esc($snapshotCard['value']) ?></dd></div><?php endforeach; ?></dl>
<?php if (has_permission('patients', 'edit')): ?>
            <?php
            $patientEditDrawerId = 'patientProfileEditDrawer';
            $patientEditFormId = 'patientProfileEditDrawerForm';
            ob_start();
            ?>
            <form id="<?= esc($patientEditFormId) ?>" action="<?= base_url('patient/' . (int) $patient['id'] . '/update') ?>" method="post" class="patient-form-stack">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" data-patient-return value="<?= esc('patient/' . (int) $patient['id']) ?>">
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

                <section class="patient-edit-fields">
<div class="patient-fields">
                        <div>
                            <label for="profile_edit_first_name" ><?= esc(lang('UI.patientFirstName')) ?> *</label>
                            <input id="profile_edit_first_name" name="first_name" type="text" required value="<?= esc(old('first_name', $patient['first_name'] ?? '')) ?>" >
                        </div>
                        <div>
                            <label for="profile_edit_last_name" ><?= esc(lang('UI.patientLastName')) ?> *</label>
                            <input id="profile_edit_last_name" name="last_name" type="text" required value="<?= esc(old('last_name', $patient['last_name'] ?? '')) ?>" >
                        </div>
                        <div>
                            <label for="profile_edit_phone" ><?= esc(lang('UI.patientPhoneNumber')) ?> *</label>
                            <input id="profile_edit_phone" name="phone" type="tel" required minlength="9" maxlength="15" inputmode="numeric" pattern="\d{9,15}" value="<?= esc(old('phone', $patient['phone'] ?? '')) ?>"  dir="ltr">
                        </div>
                        <div>
                            <label for="profile_edit_email" ><?= esc(lang('UI.patientEmail')) ?></label>
                            <input id="profile_edit_email" name="email" type="email" value="<?= esc(old('email', $patient['email'] ?? '')) ?>" >
                        </div>
                        <div>
                            <label for="profile_edit_date_of_birth" ><?= esc(lang('UI.patientDateOfBirth')) ?> *</label>
                            <input id="profile_edit_date_of_birth" name="date_of_birth" type="text" data-clinic-date="1" required value="<?= esc(old('date_of_birth', $patient['date_of_birth'] ?? '')) ?>" >
                        </div>
                        <div data-select2-dropdown-parent>
                            <label for="profile_edit_gender" ><?= esc(lang('UI.patientGender')) ?> *</label>
                            <select id="profile_edit_gender" name="gender" data-force-select2 required >
                                <option value=""><?= esc(lang('UI.patientSelect')) ?></option>
                                <?php foreach (['male' => lang('UI.patientMale'), 'female' => lang('UI.patientFemale'), 'other' => lang('UI.patientOther')] as $genderValue => $genderLabel): ?>
                                    <option value="<?= esc($genderValue) ?>" <?= old('gender', $patient['gender'] ?? '') === $genderValue ? 'selected' : '' ?>><?= esc($genderLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <section >
                    <div class="patient-fields">
                        <div>
                            <label for="profile_edit_city" ><?= esc(lang('UI.patientCity')) ?></label>
                            <input id="profile_edit_city" name="city" type="text" value="<?= esc(old('city', $patient['city'] ?? '')) ?>" >
                        </div>
                        <div >
                            <label for="profile_edit_notes" ><?= esc(lang('UI.patientNotes')) ?></label>
                            <textarea id="profile_edit_notes" name="notes" rows="3" ><?= esc(old('notes', $patient['notes'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </section>
            </form>
            <?php
            $patientEditDrawerBody = (string) ob_get_clean();
            ob_start();
            ?>
            <div >
                <a href="<?= base_url('patient/' . (int) $patient['id'] . '/edit?' . http_build_query(['return_to' => 'patient/' . (int) $patient['id']])) ?>" >

                    <span><?= esc(lang('UI.openFullPage')) ?></span>
                </a>
                <div >
                    <button type="button"  data-app-drawer-close="<?= esc($patientEditDrawerId) ?>"><?= esc(lang('UI.cancel')) ?></button>
                    <button type="submit" form="<?= esc($patientEditFormId) ?>" ><?= esc(lang('UI.settingsSave')) ?></button>
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
            <section class="patient-alert-banner">
                <div >
                    <div >
                        <div >

                        </div>
                        <div >
                            <div>
                                <h2 ><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></h2>
                                <p ><?= esc(lang('UI.patientProfileAlertSummary', ['count' => $medicalAlertCount])) ?></p>
                            </div>
                            <div >
                                <?php foreach (array_slice($activeMedicalAlerts, 0, 4) as $medicalAlertRow): ?>
                                    <span >

                                        <span><?= esc($medicalAlertRow['label']) ?></span>
                                    </span>
                                <?php endforeach; ?>
                                <?php if ($medicalAlertCount > 4): ?>
                                    <span >+<?= $medicalAlertCount - 4 ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <button type="button" data-patient-tab="details" >

                        <span><?= esc(lang('UI.patientReviewMedicalDetails')) ?></span>
                    </button>
                </div>
            </section>
        <?php endif; ?>

        <!-- Main Content Layout -->
        <div >
            <?php
            $patientTabCount = 7 + (!empty($contracts_enabled) ? 1 : 0);
            ?>
            <!-- Patient History Tabs -->
            <div >
                <div class="patient-shell" data-hero-theme="<?= esc($patientHeroThemeClass) ?>">
                    <!-- Tab Navigation -->
                    <div class="patient-shell-nav">
                        <div class="patient-section-choice" role="group" aria-label="<?= esc(lang('UI.patientProfileWorkspace'),'attr') ?>"><button type="button" data-patient-group="care" aria-pressed="true"><?= esc(lang('Studio.patientCare')) ?></button><button type="button" data-patient-group="history" aria-pressed="false"><?= esc(lang('Studio.patientHistory')) ?></button></div>
                        <div >
                            <nav  aria-label="<?= esc(lang('UI.patientProfileWorkspace')) ?>" role="tablist">
                                <button type="button" data-patient-tab="details"
                                        id="details-tab" 
                                        role="tab"
                                        aria-selected="true"
                                        aria-controls="details-content"
                                        tabindex="0"
                                        class="tab-button active">
                                    <span ><?= esc(lang('UI.patientTabDetails')) ?></span>
                                </button>
                                <button type="button" data-patient-tab="timeline"
                                        id="timeline-tab"
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="timeline-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabTimeline')) ?></span>
                                </button>
                                <button type="button" data-patient-tab="examinations"
                                        id="examinations-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="examinations-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabExams')) ?></span> <span class="tab-count"><?= (int) ($historyCounts['examinations'] ?? count($examinations)) ?></span>
                                </button>
                                <button type="button" data-patient-tab="appointments"
                                        id="appointments-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="appointments-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabAppts')) ?></span> <span class="tab-count"><?= (int) ($historyCounts['appointments'] ?? count($appointments)) ?></span>
                                </button>
                                <button type="button" data-patient-tab="treatments"
                                        id="treatments-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="treatments-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTreatments')) ?></span> <span class="tab-count"><?= (int) ($historyCounts['treatments'] ?? count($treatments)) ?></span>
                                </button>
                                <button type="button" data-patient-tab="prescriptions"
                                        id="prescriptions-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="prescriptions-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabRx')) ?></span> <span class="tab-count"><?= count($prescriptions) ?></span>
                                </button>
                                <?php if (!empty($attachments_enabled)): ?>
                                    <button type="button" data-patient-tab="attachments"
                                            id="attachments-tab" 
                                            role="tab"
                                            aria-selected="false"
                                            aria-controls="attachments-content"
                                            tabindex="-1"
                                            class="tab-button">
                                        <span ><?= esc(lang('UI.patientTabAttachments')) ?></span> <span class="tab-count"><?= count($attachments ?? []) ?></span>
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($contracts_enabled)): ?>
                                    <button type="button" data-patient-tab="contracts"
                                            id="contracts-tab" 
                                            role="tab"
                                            aria-selected="false"
                                            aria-controls="contracts-content"
                                            tabindex="-1"
                                            class="tab-button">
                                        <span ><?= esc(lang('UI.patientTabContracts')) ?></span> <span class="tab-count"><?= count($contracts ?? []) ?></span>
                                    </button>
                                <?php endif; ?>
                                <button type="button" data-patient-tab="finances"
                                        id="finances-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="finances-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabFinancials')) ?></span> <span class="tab-count"><?= count($financialLedgerRows) ?></span>
                                </button>
                            </nav>

                            <!-- Search and Filter Controls -->
                            <div id="historyControls" class="patient-filter-strip hidden">
                                <div >
                                    <input type="text" 
                                           id="historySearch" maxlength="200" value="<?= esc($workspace_history['query']['q'] ?? '') ?>"
                                           placeholder="<?= esc(lang('UI.patientSearchHistory')) ?>" 
                                           >

                                </div>
                                <div >
                                    <input type="text" data-clinic-date="1"
                                           id="startDate" aria-label="<?= esc(lang('Studio.dateFrom'),'attr') ?>" value="<?= esc($workspace_history['query']['start'] ?? '') ?>"
                                           >
                                    <input type="text" data-clinic-date="1"
                                           id="endDate" aria-label="<?= esc(lang('Studio.dateTo'),'attr') ?>" value="<?= esc($workspace_history['query']['end'] ?? '') ?>"
                                           >
                                    <button id="historySearchAll" type="button" data-patient-history-apply ><?= esc(lang('PatientHistory.search')) ?></button>
                                    <button type="button" data-patient-history-clear
                                            >
                                        <span><?= esc(lang('UI.patientClear')) ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div >
                        <!-- Patient Details Tab -->
                        <?php include APPPATH.'Views/patient/partials/workspace_details.php'; ?>

                        <!-- Timeline Tab -->
                        <?php include APPPATH.'Views/patient/partials/workspace_timeline.php'; ?>

                        <?php if (!empty($attachments_enabled)): ?>
                            <!-- Attachments Tab -->
                            <?php include APPPATH.'Views/patient/partials/workspace_attachments.php'; ?></section>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($contracts_enabled)): ?>
                            <!-- Contracts Tab -->
                            <?php include APPPATH.'Views/patient/partials/workspace_contracts.php'; ?>
                        <?php endif; ?>

                        <!-- Examinations Tab -->
                        <?php include APPPATH.'Views/patient/partials/workspace_examinations.php'; ?>

                        <!-- Appointments Tab -->
                        <?php include APPPATH.'Views/patient/partials/workspace_appointments.php'; ?>
                        <!-- Treatments Tab -->
                        <?php include APPPATH.'Views/patient/partials/workspace_treatments.php'; ?>
                        <!-- Prescriptions Tab -->
                        <?php include APPPATH.'Views/patient/partials/workspace_prescriptions.php'; ?>
                        <!-- Financials Tab -->
                        <?php include APPPATH.'Views/patient/partials/workspace_finances.php'; ?>

                        <!-- Tooth Modal -->
                        <div id="toothModal" class="hidden">
                            <div >
                                <div  id="modalContent">
                                    <!-- Enhanced Modal Header -->
                                    <div >
                                        <div ></div>
                                        <div >
                                            <div >
                                                <div >
                                                    <div >

                                                    </div>
                                                    <div>
                                                        <h3 >
                                                            <?= esc(lang('UI.patientToothConditionHeadingPrefix')) ?>
                                                            <span id="modalToothNumber" ></span>
                                                            <?= esc(lang('UI.patientToothConditionHeadingSuffix')) ?>
                                                        </h3>
                                                        <p ><?= esc(lang('UI.patientToothConditionSubtitle')) ?></p>
                                                    </div>
                                                </div>
                                                <button onclick="closeToothModal()" >

                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Body -->
                                    <div >
                                        <form id="toothForm" onsubmit="updateTooth(event)">
                                            <input type="hidden" id="toothNumber" name="tooth_number">
                                            <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">

                                            <div >
                                                <!-- Condition Type -->
                                                <div>
                                                    <label for="condition_type" ><?= esc(lang('UI.patientConditionType')) ?></label>
                                                    <select id="condition_type" name="condition_type" >
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
                                                    <label for="condition_description" ><?= esc(lang('UI.patientConditionDescription')) ?></label>
                                                    <textarea id="condition_description" name="condition_description" rows="3"  placeholder="<?= esc(lang('UI.patientConditionDescriptionPlaceholder')) ?>"></textarea>
                                                </div>

                                                <!-- Treatment Notes -->
                                                <div>
                                                    <label for="treatment_notes" ><?= esc(lang('UI.patientTreatmentNotes')) ?></label>
                                                    <textarea id="treatment_notes" name="treatment_notes" rows="3"  placeholder="<?= esc(lang('UI.patientTreatmentNotesPlaceholder')) ?>"></textarea>
                                                </div>

                                                <!-- Treatment Status -->
                                                <div>
                                                    <label for="treatment_status" ><?= esc(lang('UI.patientTreatmentStatus')) ?></label>
                                                    <select id="treatment_status" name="treatment_status" >
                                                        <option value="pending"><?= esc(lang('UI.patientTreatmentStatusPending')) ?></option>
                                                        <option value="in_progress"><?= esc(lang('UI.patientTreatmentStatusInProgress')) ?></option>
                                                        <option value="completed"><?= esc(lang('UI.patientTreatmentStatusCompleted')) ?></option>
                                                        <option value="needs_attention"><?= esc(lang('UI.patientTreatmentStatusNeedsAttention')) ?></option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Modal Footer -->
                                            <div >
                                                <button type="button" onclick="resetTooth()" >
                                                    <?= esc(lang('UI.patientResetToHealthy')) ?>
                                                </button>
                                                <div >
                                                    <button type="button" onclick="closeToothModal()" >
                                                        <?= esc(lang('UI.cancel')) ?>
                                                    </button>
                                                    <button type="submit" >
                                                        <?= esc(lang('UI.settingsSaveChanges')) ?>
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

</style>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php include APPPATH.'Views/components/studio_csrf_runtime.php'; ?>
<?php include APPPATH.'Views/patient/partials/field_widgets.php'; ?>
<script src="<?= base_url('assets/js/contextual-workspace.js') ?>?v=<?= esc(asset_version('assets/js/contextual-workspace.js')) ?>"></script>
<script src="<?= base_url('assets/js/app-drawer.js') ?>?v=<?= esc(asset_version('assets/js/app-drawer.js')) ?>"></script>
<?php include APPPATH.'Views/patient/partials/history_runtime.php'; ?>

<?php include APPPATH.'Views/patient/partials/clinical_presentation_runtime.php'; ?>

<script src="<?= base_url('assets/js/studio-patient-workspace.js') ?>?v=<?= esc(asset_version('assets/js/studio-patient-workspace.js')) ?>" defer></script>
<script src="<?= base_url('assets/js/studio-patient-sections.js') ?>?v=<?= esc(asset_version('assets/js/studio-patient-sections.js')) ?>" defer></script>
<?= $this->endSection() ?>
