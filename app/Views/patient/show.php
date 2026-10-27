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

<div class="patient-workspace patient-view">
    <div >
        <header class="patient-identity"><div class="patient-identity-name"><span class="patient-identity-avatar"><?= esc(ui_initials($patient['first_name']??'',$patient['last_name']??'','P')) ?></span><div><h1><?= esc($fullName) ?></h1><p><?= esc($patient['patient_id']??'') ?> <span><?= esc($patientStatusLabel) ?></span></p></div></div><div class="patient-identity-actions"><?php if(has_permission('patients','edit')): ?><button type="button" class="button secondary" data-app-drawer-open="patientProfileEditDrawer"><?= esc(lang('UI.patientEditPatient')) ?></button><?php endif; ?><a class="button secondary" href="<?= esc(workflow_back('patients',$return_to??null)) ?>"><?= esc(lang('UI.back')) ?></a></div></header>
<dl class="patient-identity-facts"><?php foreach($heroInfoCapsules as $capsule):$capsuleValue=(string)($capsule['value']??lang('UI.patientNotProvided'));$capsuleLabel=(string)($capsule['label']??''); ?><div><dt><?= esc($capsuleLabel) ?></dt><dd><?php if(!empty($capsule['is_button'])): ?><button type="button" onclick="openPatientWhatsAppIdentityModal('<?= esc($patientWhatsAppNumber,'js') ?>')"><?= esc($capsuleValue) ?></button><?php else: ?><?= esc($capsuleValue) ?><?php endif; ?></dd></div><?php endforeach; ?><?php foreach($patientHeaderSnapshotCards as $snapshotCard): ?><div><dt><?= esc($snapshotCard['label']) ?></dt><dd title="<?= esc($snapshotCard['detail'],'attr') ?>"><?= esc($snapshotCard['value']) ?></dd></div><?php endforeach; ?></dl>
<?php if (has_permission('patients', 'edit')): ?>
            <?php
            $patientEditDrawerId = 'patientProfileEditDrawer';
            $patientEditFormId = 'patientProfileEditDrawerForm';
            ob_start();
            ?>
            <form id="<?= esc($patientEditFormId) ?>" action="<?= base_url('patient/' . (int) $patient['id'] . '/update') ?>" method="post" class="patient-form-stack">
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
                    <button type="button" onclick="return showTab('details', event)" >

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
                        <div >
                            <nav  aria-label="<?= esc(lang('UI.patientProfileWorkspace')) ?>" role="tablist">
                                <button type="button" onclick="return showTab('details', event)" 
                                        id="details-tab" 
                                        role="tab"
                                        aria-selected="true"
                                        aria-controls="details-content"
                                        tabindex="0"
                                        class="tab-button active">
                                    <span ><?= esc(lang('UI.patientTabDetails')) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('timeline', event)"
                                        id="timeline-tab"
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="timeline-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabTimeline')) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('examinations', event)"
                                        id="examinations-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="examinations-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabExams')) ?></span> <span class="tab-count"><?= (int) ($historyCounts['examinations'] ?? count($examinations)) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('appointments', event)" 
                                        id="appointments-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="appointments-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabAppts')) ?></span> <span class="tab-count"><?= (int) ($historyCounts['appointments'] ?? count($appointments)) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('treatments', event)" 
                                        id="treatments-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="treatments-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTreatments')) ?></span> <span class="tab-count"><?= (int) ($historyCounts['treatments'] ?? count($treatments)) ?></span>
                                </button>
                                <button type="button" onclick="return showTab('prescriptions', event)" 
                                        id="prescriptions-tab" 
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="prescriptions-content"
                                        tabindex="-1"
                                        class="tab-button">
                                    <span ><?= esc(lang('UI.patientTabRx')) ?></span> <span class="tab-count"><?= count($prescriptions) ?></span>
                                </button>
                                <?php if (!empty($attachments_enabled)): ?>
                                    <button type="button" onclick="return showTab('attachments', event)" 
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
                                    <button type="button" onclick="return showTab('contracts', event)" 
                                            id="contracts-tab" 
                                            role="tab"
                                            aria-selected="false"
                                            aria-controls="contracts-content"
                                            tabindex="-1"
                                            class="tab-button">
                                        <span ><?= esc(lang('UI.patientTabContracts')) ?></span> <span class="tab-count"><?= count($contracts ?? []) ?></span>
                                    </button>
                                <?php endif; ?>
                                <button type="button" onclick="return showTab('finances', event)" 
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
                                    <button id="historySearchAll" type="button" onclick="applyHistoryFilters()" ><?= esc(lang('PatientHistory.search')) ?></button>
                                    <button type="button" onclick="clearFilters()"
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
                        <div id="details-content" class="tab-content" role="tabpanel" aria-labelledby="details-tab">
                            <div class="patient-details-layout">
                                <div class="patient-details-main">
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

                                                            <span><?= esc(lang('UI.patientOpenImagingWorkspace')) ?></span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-preview-frame">
                                                    <?php if (!empty($latestImagingAttachment) && $latestImagingIsPreviewable && !empty($latestImagingPreviewSrc)): ?>
                                                        <img loading="lazy" decoding="async" src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="patient-imaging-preview-image">
                                                    <?php elseif (!empty($latestImagingAttachment)): ?>
                                                        <div class="patient-preview-empty">

                                                            <p><?= esc(lang('UI.patientImagingPreviewUnavailable')) ?></p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="patient-preview-empty">

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
                                    </section><section class="patient-compact-card patient-compact-card-profile">
                                        <div class="patient-compact-header">
                                            <div>
                                                <h3 class="patient-compact-title"><?= esc(lang('UI.patientSummaryTitle')) ?></h3>
                                                <p class="patient-compact-copy"><?= esc(lang('UI.patientSummaryHelp')) ?></p>
                                            </div>
                                        </div>
                                        <div class="patient-facts-section patient-facts-section-primary">
                                            <div class="patient-facts-grid">
                                                <?php foreach ($patientOverviewFacts as $fact): ?>
                                                    <article class="patient-fact-card">
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
                                                        <article class="patient-fact-card">
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
                                                        <article class="patient-fact-card">
                                                            <span class="patient-fact-label"><?= esc($fact['label']) ?></span>
                                                            <p class="patient-fact-value"><?= esc($fact['value']) ?></p>
                                                        </article>
                                                    <?php endforeach; ?>
                                                </div>
                                            </section>
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
                                                <div >
                                                    <a href="<?= workflow_context_url('patient/' . $patient['id'] . '/odontogram-versions', $patientContextReturnTo) ?>" >

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
                                                        <div >
                                                            <?php if (!empty($snapshotItem['id'])): ?>
                                                                <a href="<?= workflow_context_url('patient/' . $patient['id'] . '/odontogram-versions/' . $snapshotItem['id'], $patientContextReturnTo) ?>" >

                                                                    <span><?= esc(lang('UI.patientChartHistoryOpenSelected')) ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($snapshotItem['source_url'])): ?>
                                                                <a href="<?= esc($snapshotItem['source_url']) ?>" >

                                                                    <span><?= esc(lang('UI.patientChartHistoryOpenSource')) ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </article>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="patient-preview-empty patient-preview-empty-compact">

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
                            <?= view('patient/partials/history_navigation', ['history_page' => $workspaceHistoryPages['timeline'] ?? null, 'history_navigation' => $historyNavigation['timeline'] ?? null]) ?>
                            <div class="patient-tab-ambient patient-timeline-workspace">
                                <div class="patient-timeline-workspace-header">
                                    <div class="patient-timeline-heading">
                                        <span class="patient-timeline-heading-icon">

                                        </span>
                                        <div >
                                            <h3><?= esc(lang('UI.patientTabTimeline')) ?></h3>
                                            <p><?= esc(lang('UI.patientHistory')) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <ul class="patient-timeline-list">
                                    <?php 
                                    $timeline = $workspaceHistoryPages['timeline']['items'] ?? \App\Services\PatientWorkspaceTimelinePresenter::build(get_defined_vars());

                                    foreach ($timeline as $index => $item): 
                                    ?>
                                    <li class="patient-timeline-node">
                                        <div >
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
                                                        <span class="patient-timeline-icon">

                                                        </span>
                                                    </div>
                                                    <div class="patient-timeline-main">
                                                        <div class="patient-timeline-title-block">
                                                            <div class="patient-timeline-title"><?= esc($item['title']) ?></div>
                                                            <div class="patient-timeline-date">

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
                                                                            <?php if (!empty($metaItem['icon'])): ?><?php endif; ?>
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
                                                            <span class="patient-timeline-status-label">
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
                                            <div class="patient-empty">

                                                <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoHistoryRecords')) ?></p>
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
                                    <div >
                                        <?= esc(session('success')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (session('error')): ?>
                                    <div >
                                        <?= esc(session('error')) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($limitBytes > 0 && $usagePercent >= 80): ?>
                                    <div >
                                        <strong><?= esc(lang('UI.patientStorageWarning')) ?></strong> <?= esc(lang('UI.patientStorageWarningText', ['percent' => $usagePercent])) ?>
                                    </div>
                                <?php endif; ?>

                                <section ><div ><div ></div><div ></div></div><div class="patient-fields">
                                    <section >
                                        <div >
                                            <h3 ><?= esc(lang('UI.patientImagingWorkspaceLatestTitle')) ?></h3>
                                            <p ><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                        </div>
                                        <div >
                                            <?php if (!empty($latestImagingAttachment)): ?>
                                                <div >
                                                    <div >
                                                        <?php if (!empty($latestImagingAttachment) && $latestImagingIsPreviewable && !empty($latestImagingPreviewSrc)): ?>
                                                            <img loading="lazy" decoding="async" src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" >
                                                        <?php else: ?>
                                                            <div class="patient-empty"><?= esc(lang('UI.patientImagingPreviewUnavailable')) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div >
                                                    <div>
                                                        <h4 ><?= esc($latestImagingAttachment['category'] ?? lang('UI.patientLatestImagingTitle')) ?></h4>
                                                        <p ><?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></p>
                                                        <p ><?= esc(!empty($latestImagingAttachment['created_at']) ? formatDateTime($latestImagingAttachment['created_at']) : '--') ?></p>
                                                    </div>
                                                    <div >
                                                        <?php if (!empty($latestImagingViewUrl) && $latestImagingIsPreviewable): ?>
                                                            <a href="<?= esc($latestImagingViewUrl) ?>" target="_blank" rel="noopener" >

                                                                <span><?= esc(lang('UI.patientOpenImaging')) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (!empty($latestImagingOpenUrl)): ?>
                                                            <a href="<?= esc($latestImagingOpenUrl) ?>" >

                                                                <span><?= esc(lang('UI.patientImagingWorkspaceOpenFile')) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="patient-empty">

                                                    <p ><?= esc(lang('UI.patientNoImagingPreview')) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </section>

                                    <aside>
                                        <section >
                                            <div >
                                                <h3 ><?= esc(lang('UI.patientUploadFiles')) ?></h3>
                                                <p ><?= esc(lang('UI.patientAttachmentsSubtitle', ['max' => ($maxFileMb === null ? lang('UI.patientUnlimited') : ((int) $maxFileMb . 'MB'))])) ?></p>
                                            </div>
                                            <div >
                                                <?php $selectedAttachmentCategory = old('category'); ?>
                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/upload') ?>" method="post" enctype="multipart/form-data" >
                                                    <?= csrf_field() ?>
                                                    <div>
                                                        <label ><?= esc(lang('UI.patientUploadFiles')) ?></label>
                                                        <input type="file" name="attachment[]" required multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip" >
                                                    </div>
                                                    <div>
                                                        <label ><?= esc(lang('UI.patientAttachmentCategory')) ?></label>
                                                        <select name="category" >
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
                                                        <label ><?= esc(lang('UI.patientNotes')) ?></label>
                                                        <textarea name="notes" rows="4" placeholder="<?= esc(lang('UI.patientAttachmentNotesPlaceholder')) ?>" ></textarea>
                                                    </div>
                                                    <div >
                                                        <button type="submit" >

                                                            <span><?= esc(lang('UI.patientUpload')) ?></span>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </section>
                                    </aside>
                                </div>

                                <div >
                                    <div >
                                        <h4 ><?= esc(lang('UI.patientAttachmentHistory')) ?></h4>
                                    </div>
                                    <div >
                                        <table class="patient-record-table studio-grid" data-record-type="attachments" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th ><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th ><?= esc(lang('UI.patientFile')) ?></th>
                                                    <th ><?= esc(lang('UI.patientCategory')) ?></th>
                                                    <th ><?= esc(lang('UI.patientNotes')) ?></th>
                                                    <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($attachments)): ?>
                                                    <?php foreach ($attachments as $attachment): ?>
                                                        <?php $attachmentIsPreviewable = strpos((string) ($attachment['mime_type'] ?? ''), 'image/') === 0; ?>
                                                        <tr class="patient-record-row" data-date="<?= !empty($attachment['created_at']) ? esc(date('Y-m-d', strtotime($attachment['created_at']))) : '' ?>" data-href="<?= esc($attachmentIsPreviewable ? base_url('file/inline/' . $attachment['id']) : base_url('file/download/' . $attachment['id'])) ?>">
                                                            <td >
                                                                <?= !empty($attachment['created_at']) ? formatDateTime($attachment['created_at']) : '--' ?>
                                                            </td>
                                                            <td>
                                                                <div class="patient-record-main">
                                                                    <div class="patient-record-title"><?= esc($attachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></div>
                                                                    <div class="patient-record-subtitle"><?= number_format(($attachment['file_size'] ?? 0) / 1024, 1) ?> KB</div>
                                                                </div>
                                                            </td>
                                                            <td >
                                                                <?= esc($attachment['category'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td >
                                                                <?= esc($attachment['notes'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td >
                                                                <div class="patient-table-actions">
                                                                <?php if ($attachmentIsPreviewable): ?>
                                                                <a href="<?= base_url('file/inline/' . $attachment['id']) ?>" target="_blank" rel="noopener"
                                                                   class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                                <?php endif; ?>
                                                                <a href="<?= base_url('file/download/' . $attachment['id']) ?>" 
                                                                   class="patient-table-action" title="<?= esc(lang('UI.patientDownload')) ?>"><?= esc(lang('UI.patientDownload')) ?></a>
                                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/' . $attachment['id'] . '/delete') ?>" method="post" >
                                                                    <?= csrf_field() ?>
                                                                    <button type="submit" class="patient-table-action patient-table-action-danger" title="<?= esc(lang('UI.patientDelete')) ?>" onclick="return confirm('<?= esc(lang('UI.patientDeleteAttachmentConfirm'), 'js') ?>');">

                                                                    </button>
                                                                </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="patient-empty"><?= esc(lang('UI.patientNoAttachmentsYet')) ?></td>
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
                                <section ><div ><div ></div><div ></div></div><div class="patient-form-stack">
                                <?php if (session('success')): ?>
                                    <div >
                                        <?= esc(session('success')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (session('error')): ?>
                                    <div >
                                        <?= esc(session('error')) ?>
                                    </div>
                                <?php endif; ?>

                                <div >
                                    <?php if (has_permission('patients', 'edit')): ?>
                                        <a href="<?= workflow_context_url('contracts/create/' . $patient['id'], $patientContextReturnTo) ?>"
                                           >
                                            <?= esc(lang('UI.patientCreateTreatmentPlan')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div >
                                    <div >
                                        <h4 ><?= esc(lang('UI.patientContractHistory')) ?></h4>
                                    </div>
                                    <div >
                                        <table class="patient-record-table studio-grid" data-record-type="contracts" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th ><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                    <th ><?= esc(lang('UI.patientExam')) ?></th>
                                                    <th ><?= esc(lang('UI.patientTotal')) ?></th>
                                                    <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($contracts)): ?>
                                                    <?php foreach ($contracts as $contract): ?>
                                                        <tr class="patient-record-row" data-date="<?= !empty($contract['created_at']) ? esc(date('Y-m-d', strtotime($contract['created_at']))) : '' ?>" data-href="<?= esc(workflow_context_url('contracts/' . $contract['id'], $patientContextReturnTo)) ?>">
                                                            <td >
                                                                <?= !empty($contract['created_at']) ? formatDateTime($contract['created_at']) : '--' ?>
                                                            </td>
                                                            <td>
                                                                <span >
                                                                    <?= esc(ucfirst($contract['status'] ?? 'draft')) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <?= !empty($contract['examination_id']) ? esc($contract['examination_id']) : '--' ?>
                                                            </td>
                                                            <td >
                                                                <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                                                            </td>
                                                    <td >
                                                        <div class="patient-table-actions">
                                                        <a href="<?= workflow_context_url('contracts/' . $contract['id'], $patientContextReturnTo) ?>" 
                                                           class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                        <?php if (($contract['status'] ?? 'draft') === 'draft'): ?>
                                                            <a href="<?= workflow_context_url('contracts/' . $contract['id'] . '/edit', $patientContextReturnTo) ?>" 
                                                               class="patient-table-action" title="<?= esc(lang('UI.patientEdit')) ?>"><?= esc(lang('UI.patientEdit')) ?></a>
                                                            <form action="<?= base_url('contracts/' . $contract['id'] . '/delete') ?>" method="post" >
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="patient-table-action patient-table-action-danger" title="<?= esc(lang('UI.patientDelete')) ?>" onclick="return confirm('<?= esc(lang('UI.patientDeleteContractConfirm'), 'js') ?>');">

                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <a href="<?= base_url('contracts/' . $contract['id'] . '/print') ?>" 
                                                           class="patient-table-action" title="<?= esc(lang('UI.patientPrint')) ?>"><?= esc(lang('UI.patientPrint')) ?></a>
                                                        </div>
                                                    </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="patient-empty"><?= esc(lang('UI.patientNoContractsYet')) ?></td>
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
                            <?= view('patient/partials/history_navigation', ['history_page' => $workspaceHistoryPages['examinations'] ?? null, 'history_navigation' => $historyNavigation['examinations'] ?? null]) ?>
                            <section class="patient-tab-ambient patient-exam-workspace">
                                <div class="patient-exam-header">
                                    <div class="patient-exam-heading">
                                        <span class="patient-exam-heading-icon"></span>
                                        <div >
                                            <h3><?= esc(lang('UI.patientExaminations')) ?></h3>
                                            <p><?= esc(lang('UI.patientExaminationsSubtitle')) ?></p>
                                        </div>
                                    </div>
                                    <div class="patient-exam-header-actions">
                                        <div class="patient-exam-count">
                                            <strong><?= (int) ($historyCounts['examinations'] ?? count($examinations)) ?></strong>
                                            <span><?= esc(lang('UI.patientTabExams')) ?></span>
                                        </div>
                                        <?php if (has_permission('examinations', 'create')): ?>
                                            <button type="button" data-app-drawer-open="patientExamDrawer" class="patient-exam-create-action">

                                                <span><?= esc(lang('UI.patientCreateExamination')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($examinations)): ?>
                                    <table class="patient-record-table studio-grid" data-record-type="examinations" data-grid-widths="14,15,14,28,17,12" aria-label="<?= esc(lang('UI.patientExaminations'),'attr') ?>"><thead><tr><?php foreach(['UI.patientDate','UI.patientType','UI.patientStatus','Examination.chief_complaint','Finance.provider','UI.patientActions'] as $key): ?><th scope="col"><?= esc(lang($key)) ?></th><?php endforeach; ?></tr></thead><tbody>
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
                                            <tr class="patient-record-row" data-href="<?= esc($examUrl) ?>" data-date="<?= esc($examDateIso,'attr') ?>"><td><?= esc(formatDate($examination['examination_date'])) ?></td><td><strong><?= esc($examTypeLabel) ?></strong><small><?= esc(lang('UI.patientExam')) ?></small></td><td><span class="patient-record-status"><?= esc($examStatusLabel) ?></span></td><td><?= esc($examComplaintPreview) ?></td><td><?= esc($examDoctorName) ?></td><td><a class="patient-table-action" href="<?= esc($examUrl) ?>"><?= esc(lang('UI.patientView')) ?></a></td></tr>
                                        <?php endforeach; ?>
</tbody></table>
                                    <div class="patient-filter-empty hidden">

                                        <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoExaminations')) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="patient-exam-empty">

                                        <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoExaminations')) ?></p>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>

                        <!-- Appointments Tab -->
                        <div id="appointments-content" class="tab-content hidden">
                            <?= view('patient/partials/history_navigation', ['history_page' => $workspaceHistoryPages['appointments'] ?? null, 'history_navigation' => $historyNavigation['appointments'] ?? null]) ?>
                            <section >
                                <div >
                                    <div ></div>
                                    <div ></div>
                                </div>
                                <div >
                                    <div >
                                        <div >
                                            <span >

                                            </span>
                                            <div >
                                                <div >
                                                    <h3 ><?= esc(lang('UI.patientAppointments')) ?></h3>
                                                    <span ><?= (int) ($historyCounts['appointments'] ?? count($appointments)) ?> <?= esc(lang('UI.patientTabAppts')) ?></span>
                                                </div>
                                                <p ><?= esc(lang('UI.patientAppointmentsSubtitle')) ?></p>
                                            </div>
                                        </div>
                                        <?php if (has_permission('appointments', 'create')): ?>
                                            <button type="button" data-app-drawer-open="patientAppointmentDrawer" class="patient-appointment-create-action">

                                                <span><?= esc(lang('UI.patientScheduleAppointment')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($appointments)): ?>
                                    <div >
                                        <div >
                                            <table class="patient-record-table studio-grid" data-record-type="appointments" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead >
                                                    <tr>
                                                        <th ><?= esc(lang('UI.patientDateTime')) ?></th>
                                                        <th ><?= esc(lang('UI.patientType')) ?></th>
                                                        <th ><?= esc(lang('UI.patientDuration')) ?></th>
                                                        <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody >
                                                    <?php foreach ($appointments as $apt): ?>
                                                        <?php
                                                        $appointmentDateKey = !empty($apt['appointment_date']) ? substr((string) $apt['appointment_date'], 0, 10) : '';
                                                        $appointmentClinical = $appointmentDateKey !== '' ? ($sameDayClinicalByDate[$appointmentDateKey] ?? null) : null;
                                                        $appointmentClinicalCount = !empty($appointmentClinical)
                                                            ? (int) ($appointmentClinical['examination_count'] ?? count($appointmentClinical['examinations'] ?? [])) + (int) ($appointmentClinical['treatment_count'] ?? count($appointmentClinical['treatments'] ?? []))
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
                                                        <tr class="patient-record-row" data-href="<?= esc($appointmentUrl) ?>" data-date="<?= esc($appointmentDateKey, 'attr') ?>">
                                                            <td >
                                                                <div ><?= esc(formatDate($apt['appointment_date'])) ?></div>
                                                                <div ><?= esc(lang('UI.patientAt')) ?> <?= esc(formatTime($apt['appointment_time'])) ?></div>
                                                            </td>
                                                            <td >
                                                                <div class="patient-record-title"><?= esc($appointmentTypeLabel) ?></div>
                                                                <div >

                                                                    <?= esc($appointmentChairLabel) ?>
                                                                </div>
                                                                <?php if ($appointmentClinicalCount > 0): ?>
                                                                    <div >
                                                                        <span >

                                                                            <span><?= esc(lang('UI.patientSameDayClinicalWork')) ?>: <?= esc((string) $appointmentClinicalCount) ?></span>
                                                                        </span>
                                                                        <?php foreach (array_slice(($appointmentClinical['examinations'] ?? []), 0, 1) as $clinicalItem): ?>
                                                                            <a href="<?= esc($clinicalItem['url']) ?>" >

                                                                                <span><?= esc($clinicalItem['label']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                        <?php foreach (array_slice(($appointmentClinical['treatments'] ?? []), 0, 1) as $clinicalItem): ?>
                                                                            <a href="<?= esc($clinicalItem['url']) ?>" >

                                                                                <span><?= esc($clinicalItem['label']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td >
                                                                <span >

                                                                    <?= esc($apt['duration'] ?? '30') ?> <?= esc(lang('UI.patientMinutes')) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <span >
                                                                    <?= esc($appointmentStatusLabel) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <a href="<?= esc($appointmentUrl) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div >
                                        <div class="patient-empty">

                                            <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoAppointments')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        <!-- Treatments Tab -->
                        <div id="treatments-content" class="tab-content hidden">
<?= view('patient/partials/history_navigation',['history_page'=>$workspaceHistoryPages['treatments']??null,'history_navigation'=>$historyNavigation['treatments']??null]) ?>
<section class="patient-tab-ambient patient-treatment-workspace"><header class="patient-section-header"><div><h3><?= esc(lang('UI.patientTreatments')) ?></h3><p><?= esc(lang('UI.patientBilling')) ?> / <?= esc(lang('UI.patientStatus')) ?> / <?= esc(lang('Finance.provider')) ?></p></div><?php if(has_permission('treatments','create')): ?><button type="button" data-app-drawer-open="patientTreatmentDrawer"><?= esc(lang('UI.patientCreateTreatmentPlan')) ?></button><?php endif; ?></header>
<dl class="patient-treatment-summary"><div><dt><?= esc(lang('UI.patientUnbilledWork')) ?></dt><dd><?= formatCurrency($openClinicalChargeTotal) ?></dd><small><?= esc($openClinicalChargeCount) ?> <?= esc(lang('UI.patientTreatments')) ?></small></div><div><dt><?= esc(lang('UI.patientBilledPaid')) ?></dt><dd><?= esc($paidBilledTreatmentCount) ?></dd></div><div><dt><?= esc(lang('UI.patientBilledWithBalance')) ?></dt><dd><?= esc($unpaidBilledTreatmentCount) ?></dd></div></dl>
<?php if (!empty($treatments)): ?>
                                    <table class="patient-record-table studio-grid" data-record-type="treatments" data-grid-widths="12,25,15,15,21,12" aria-label="<?= esc(lang('UI.patientTreatments'),'attr') ?>"><thead><tr><?php foreach(['UI.patientDate','UI.patientTreatments','Finance.provider','UI.patientBilling','Finance.invoice','UI.patientActions'] as $key): ?><th scope="col"><?= esc(lang($key)) ?></th><?php endforeach; ?></tr></thead><tbody>
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
                                            <tr class="patient-record-row" data-href="<?= esc($treatmentUrl) ?>" data-date="<?= esc(!empty($trt['start_date'])?substr($trt['start_date'],0,10):'','attr') ?>"><td><?= !empty($trt['start_date'])?esc(formatDate($trt['start_date'])):esc(lang('UI.patientNa')) ?></td><td><strong><?= esc($trt['treatment_name']??lang('UI.patientNa')) ?></strong><small><?= esc($treatmentTypeLabel) ?> · <?= esc(lang('UI.patientTooth')) ?> <?= esc($trt['tooth_number']??lang('UI.patientNa')) ?></small><?php if(!empty($trt['treatment_description'])): ?><small><?= esc($trt['treatment_description']) ?></small><?php endif; ?></td><td><?= esc($doctorName) ?></td><td><strong><?= esc($rowBilling['amount_text']) ?></strong><small><?= esc($rowBilling['status_text']) ?></small><span class="patient-record-status"><?= esc($treatmentStatusLabel) ?></span></td><td><?php if(!empty($rowBilling['finance_id'])): ?><a href="<?= esc(workflow_context_url('finance/'.(int)$rowBilling['finance_id'],$patientContextReturnTo)) ?>"><?= esc($rowBilling['invoice_number']!==''?$rowBilling['invoice_number']:lang('Finance.invoice')) ?></a><?php if($rowBilling['invoice_status_text']!==''): ?><small><?= esc($rowBilling['invoice_status_text']) ?></small><?php endif; ?><small><?= esc(lang('UI.patientPaidShort')) ?> <?= esc($rowBilling['paid_text']) ?></small><small><?= esc(lang('UI.patientBalanceShort')) ?> <?= esc($rowBilling['balance_text']) ?></small><?php else: ?><?= esc(lang('UI.patientNa')) ?><?php endif; ?></td><td><a class="patient-table-action" href="<?= esc($treatmentUrl) ?>"><?= esc(lang('UI.patientView')) ?></a><?php if($billTreatmentFromPatientUrl!==''): ?><button type="button" data-patient-charge-line-open data-charge-line-id="<?= (int)$rowBilling['charge_line_id'] ?>"><?= esc(lang('UI.patientCreateInvoiceFromWork')) ?></button><?php endif; ?></td></tr>
                                        <?php endforeach; ?>
</tbody></table>
                                <?php else: ?>
                                    <div >
                                        <div class="patient-empty">

                                            <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoTreatments')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        <!-- Prescriptions Tab -->
                        <div id="prescriptions-content" class="tab-content hidden">
                            <section >
                                <div >
                                    <div ></div>
                                    <div ></div>
                                </div>
                                <div >
                                    <div >
                                        <div >
                                            <span >

                                            </span>
                                            <div >
                                                <div >
                                                    <h3 ><?= esc(lang('UI.patientPrescriptions')) ?></h3>
                                                    <span ><?= count($prescriptions) ?> <?= esc(lang('UI.patientTabRx')) ?></span>
                                                </div>
                                                <p ><?= esc(lang('UI.patientPrescriptionsSubtitle')) ?></p>
                                            </div>
                                        </div>
                                        <?php if (has_permission('prescriptions', 'create')): ?>
                                            <a href="<?= workflow_context_url('prescription/create?patient_id=' . $patient['id'], $patientContextReturnTo) ?>"
                                               >

                                                <span><?= esc(lang('UI.patientCreatePrescription')) ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($prescriptions)): ?>
                                    <div >
                                        <div >
                                            <table class="patient-record-table studio-grid" data-record-type="prescriptions" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead >
                                                    <tr>
                                                        <th ><?= esc(lang('UI.patientMedication')) ?></th>
                                                        <th ><?= esc(lang('UI.patientDosage')) ?></th>
                                                        <th ><?= esc(lang('UI.patientFrequency')) ?></th>
                                                        <th ><?= esc(lang('UI.patientPrescribedDate')) ?></th>
                                                        <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody >
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
                                                        <tr class="patient-record-row" data-href="<?= esc($prescriptionUrl) ?>" data-date="<?= esc($prescribedDateIso, 'attr') ?>">
                                                            <td >
                                                                <div >
                                                                    <span >

                                                                    </span>
                                                                    <div >
                                                                        <div class="patient-record-title"><?= esc($medicationName) ?></div>
                                                                        <div ><?= esc(lang('UI.patientMedicationDetails')) ?></div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td ><?= esc($dosage) ?></td>
                                                            <td ><?= esc($frequency) ?></td>
                                                            <td >
                                                                <?= !empty($prescription['prescribed_date']) ? esc(formatDate($prescription['prescribed_date'])) : esc(lang('UI.patientNa')) ?>
                                                            </td>
                                                            <td >
                                                                <span >
                                                                    <?= esc($prescriptionStatusLabel) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <a href="<?= esc($prescriptionUrl) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div >
                                        <div class="patient-empty">

                                            <p ><?= esc(lang('UI.patientNoPrescriptions')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        <!-- Financials Tab -->
                        <div id="finances-content" class="tab-content hidden" role="tabpanel" aria-labelledby="finances-tab" hidden>
                            <section class="patient-finance-workspace"><div ><div ></div><div ></div></div><div class="patient-form-stack">
                                <div >
                                    <?php if (has_permission('finance', 'create')): ?>
                                        <button type="button" data-app-drawer-open="patientPaymentDrawer" >
                                            <?= esc(lang('UI.recordPayment')) ?>
                                        </button>
                                        <button type="button" data-app-drawer-open="patientChargeDrawer" >
                                            <?= esc(lang('UI.patientCreateInvoice')) ?>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <dl class="patient-statement <?= $outstandingBalance>0.009?'patient-statement--due':'' ?>"><?php foreach([[lang('UI.patientOutstandingBalance'),$outstandingBalance>0.009?formatCurrency($outstandingBalance):lang('UI.patientNoBalanceDue')],[lang('UI.patientTotalBilled'),formatCurrency($totalCharges)],[lang('UI.patientTotalPaid'),formatCurrency($totalCollected)],[lang('Finance.refunds'),formatCurrency($totalRefunded)]] as [$label,$value]): ?><div><dt><?= esc($label) ?></dt><dd><?= esc($value) ?></dd></div><?php endforeach; ?></dl>
<?php if (!empty($financialLedgerRows)): ?>
                                    <div >
                                        <div >
                                            <table class="patient-record-table studio-grid" data-record-type="finances" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead >
                                                    <tr>
                                                        <th ><?= esc(lang('UI.patientDate')) ?></th>
                                                        <th ><?= esc(lang('UI.patientType')) ?></th>
                                                        <th ><?= esc(lang('Finance.detailsReference')) ?></th>
                                                        <th ><?= esc(lang('UI.patientClinicalSource')) ?></th>
                                                        <th ><?= esc(lang('Finance.amount')) ?></th>
                                                        <th ><?= esc(lang('Finance.method')) ?></th>
                                                        <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th ><?= esc(lang('UI.patientActions')) ?></th>
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
                                                            <td >
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
                                                                    <div >
                                                                        <?php foreach ($ledgerRow['clinical_sources'] as $clinicalSource): ?>
                                                                            <?php $clinicalSourceLabel = trim((string) ($clinicalSource['label'] ?? '')); ?>
                                                                            <?php if ($clinicalSourceLabel === '') { continue; } ?>
                                                                            <?php if (!empty($clinicalSource['url'])): ?>
                                                                                <a href="<?= esc($clinicalSource['url']) ?>" >

                                                                                    <span><?= esc($clinicalSourceLabel) ?></span>
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <span >

                                                                                    <span><?= esc($clinicalSourceLabel) ?></span>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span ><?= esc(lang('UI.patientNoClinicalSource')) ?></span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($ledgerRow['provider_payouts'])): ?>
                                                                    <div >
                                                                        <?php foreach ($ledgerRow['provider_payouts'] as $providerPayout): ?>
                                                                            <a href="<?= esc($providerPayout['url']) ?>" >

                                                                                <span><?= esc(lang('UI.patientProviderCompensation')) ?>: <?= esc($providerPayout['provider_name']) ?></span>
                                                                                <span dir="ltr"><?= esc($providerPayout['share_text']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td >
                                                                <?= ($rowAmount < 0 ? '- ' : '') . formatCurrency(abs($rowAmount)) ?>
                                                            </td>
                                                            <td><?= esc($ledgerRow['method_label']) ?></td>
                                                            <td >
                                                                <span >
                                                                    <?= esc($ledgerRow['status_label']) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <div class="patient-table-actions">
                                                                    <a href="<?= esc($ledgerRow['action_url']) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                <div class="patient-empty">

                                    <p ><?= esc(lang('UI.patientNoFinancialRecords')) ?></p>
                                </div>
                                <?php endif; ?>
                                <details class="patient-finance-trace">
                                    <summary >
                                        <span ><?= esc(lang('UI.moneyTrailTitle')) ?></span>
                                        <span >
                                            <?= esc(lang('UI.moneyTrailLinkedRecords')) ?>

                                        </span>
                                    </summary>
                                    <div >
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
<script>
    window.csrfConfig = {
        name: document.querySelector('meta[name="csrf-name"]')?.getAttribute('content') || '<?= csrf_token() ?>',
        header: document.querySelector('meta[name="csrf-header"]')?.getAttribute('content') || '<?= csrf_header() ?>',
        cookieName: '<?= esc(config('Security')->cookieName) ?>'
    };

    // Initialize global hash
    window.csrfHash = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?= csrf_hash() ?>';

    // Global CSRF helper functions
    window.getCsrfToken = function() {
        return window.csrfHash;
    };

    window.refreshCsrfToken = function(serverToken) {
        let token = serverToken;
        if (!token) {
            const name = window.csrfConfig.cookieName;
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                token = parts.pop().split(';').shift();
            }
        }

        if (token) {
            window.csrfHash = token;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', token);
                // Also update any hidden CSRF inputs in forms
                document.querySelectorAll(`input[name="${window.csrfConfig.name}"]`).forEach(input => {
                    input.value = token;
                });
            }
            return token;
        }
        return window.csrfHash;
    };

</script>
<script>
    const clinicDateFormat = '<?= esc(settings()->get('date_format', 'd/m/Y')) ?>';
    const clinicTimeFormat = '<?= esc(settings()->get('time_format', '12')) ?>';

    function clinicDateFormatLabel(format) {
        return format.replace('Y', 'YYYY').replace('m', 'MM').replace('d', 'DD');
    }

    function formatClinicDateFromIso(isoDate) {
        if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) {
            return isoDate || '';
        }

        const [year, month, day] = isoDate.split('-');
        if (clinicDateFormat === 'm/d/Y') {
            return `${month}/${day}/${year}`;
        }
        if (clinicDateFormat === 'd/m/Y') {
            return `${day}/${month}/${year}`;
        }
        return isoDate;
    }

    function parseClinicDateToIso(rawValue) {
        if (!rawValue) {
            return '';
        }

        const value = rawValue.trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }

        const isValidCalendarDate = (year, month, day) => {
            const y = Number(year);
            const m = Number(month);
            const d = Number(day);
            if (!Number.isInteger(y) || !Number.isInteger(m) || !Number.isInteger(d)) {
                return false;
            }
            const probe = new Date(Date.UTC(y, m - 1, d));
            return probe.getUTCFullYear() === y && (probe.getUTCMonth() + 1) === m && probe.getUTCDate() === d;
        };

        if (clinicDateFormat === 'm/d/Y') {
            const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (!match) return null;
            const month = match[1].padStart(2, '0');
            const day = match[2].padStart(2, '0');
            const year = match[3];
            if (!isValidCalendarDate(year, month, day)) return null;
            return `${year}-${month}-${day}`;
        }

        if (clinicDateFormat === 'd/m/Y') {
            const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (!match) return null;
            const day = match[1].padStart(2, '0');
            const month = match[2].padStart(2, '0');
            const year = match[3];
            if (!isValidCalendarDate(year, month, day)) return null;
            return `${year}-${month}-${day}`;
        }

        return null;
    }

    function formatClinicTimeFrom24(rawTime) {
        if (!rawTime) return '';
        const match = String(rawTime).trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (!match) return rawTime;

        const hour24 = Number(match[1]);
        const minute = match[2];
        if (!Number.isInteger(hour24) || hour24 < 0 || hour24 > 23) return rawTime;

        if (clinicTimeFormat === '24') {
            return `${String(hour24).padStart(2, '0')}:${minute}`;
        }

        const period = hour24 >= 12 ? 'PM' : 'AM';
        const hour12 = hour24 % 12 || 12;
        return `${hour12}:${minute} ${period}`;
    }

    function parseClinicTimeTo24(rawValue) {
        if (!rawValue) return '';
        const value = String(rawValue).trim();

        let match = value.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (match) {
            const hour = Number(match[1]);
            const minute = Number(match[2]);
            if (Number.isInteger(hour) && Number.isInteger(minute) && hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59) {
                return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            }
        }

        match = value.match(/^(\d{1,2}):(\d{2})\s*([AP]M)$/i);
        if (match) {
            let hour = Number(match[1]);
            const minute = Number(match[2]);
            const period = match[3].toUpperCase();
            if (!Number.isInteger(hour) || hour < 1 || hour > 12 || !Number.isInteger(minute) || minute < 0 || minute > 59) {
                return null;
            }
            if (period === 'AM') {
                hour = hour === 12 ? 0 : hour;
            } else {
                hour = hour === 12 ? 12 : hour + 12;
            }
            return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
        }

        return null;
    }

    function initializeClinicDateInputs(root) {
        const scope = root || document;
        const fields = scope.querySelectorAll('input[data-clinic-date="1"]');
        const formatHint = clinicDateFormatLabel(clinicDateFormat);
        const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

        fields.forEach((field) => {
            if (field.dataset.clinicDateInputBound === '1') {
                return;
            }
            field.dataset.clinicDateInputBound = '1';
            const current = field.value ? field.value.trim() : '';
            if (current && /^\d{4}-\d{2}-\d{2}$/.test(current)) {
                field.value = formatClinicDateFromIso(current);
            }

            const syncDateFieldChrome = () => {
                const nativePickerActive = field.type === 'date';
                if (nativePickerActive) {
                    if (isRtl) {
                        field.style.paddingLeft = '2.75rem';
                        field.style.paddingRight = '2.75rem';
                    } else {
                        field.style.paddingLeft = '1rem';
                        field.style.paddingRight = '2.75rem';
                    }
                } else {
                    field.style.removeProperty('padding-left');
                    field.style.removeProperty('padding-right');
                }
            };

            field.type = 'text';
            field.setAttribute('placeholder', formatHint);
            field.setAttribute('autocomplete', 'off');
            field.inputMode = 'numeric';
            field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);
            syncDateFieldChrome();

            const switchToPickerMode = () => {
                const raw = (field.value || '').trim();
                const iso = parseClinicDateToIso(raw);
                field.setCustomValidity('');
                field.type = 'date';
                if (iso) {
                    field.value = iso;
                } else if (raw === '') {
                    field.value = '';
                }
                syncDateFieldChrome();
                if (typeof field.showPicker === 'function') {
                    try {
                        field.showPicker();
                    } catch (e) {
                        // no-op: browser may block showPicker unless user-initiated
                    }
                }
            };

            const switchToDisplayMode = () => {
                if (field.type === 'date') {
                    const iso = (field.value || '').trim();
                    field.type = 'text';
                    field.value = iso ? formatClinicDateFromIso(iso) : '';
                }
                field.setCustomValidity('');
                syncDateFieldChrome();
            };

            // Keep text mode by default so users can type dates manually with numpad.
            // Picker remains available via click, Alt+ArrowDown, or double-click.
            field.addEventListener('click', () => {
                if (field.type !== 'date') {
                    switchToPickerMode();
                }
            });
            field.addEventListener('keydown', (event) => {
                if (event.altKey && event.key === 'ArrowDown') {
                    event.preventDefault();
                    switchToPickerMode();
                }
            });
            field.addEventListener('dblclick', () => {
                if (field.type !== 'date') {
                    switchToPickerMode();
                }
            });
            field.addEventListener('input', () => {
                field.setCustomValidity('');
            });
            field.addEventListener('change', () => {
                field.setCustomValidity('');
            });
            field.addEventListener('blur', switchToDisplayMode);
        });

        scope.querySelectorAll('form').forEach((form) => {
            if (form.dataset.clinicDateBound === '1') {
                return;
            }
            form.dataset.clinicDateBound = '1';
            form.addEventListener('submit', function (event) {
                const dateInputs = form.querySelectorAll('input[data-clinic-date="1"]');
                for (const input of dateInputs) {
                    input.setCustomValidity('');
                    const raw = (input.value || '').trim();
                    if (raw === '') {
                        continue;
                    }

                    const iso = parseClinicDateToIso(raw);
                    if (!iso) {
                        input.setCustomValidity(`Use date format ${formatHint}`);
                        input.reportValidity();
                        event.preventDefault();
                        return;
                    }

                    input.setCustomValidity('');
                    input.value = iso;
                }
            }, true);
        });
    }

    function initializeClinicTimeInputs(root) {
        const scope = root || document;
        const fields = scope.querySelectorAll('input[data-clinic-time="1"]');
        if (!fields.length) {
            return;
        }

        const formatHint = clinicTimeFormat === '24' ? 'HH:MM' : 'h:mm AM/PM';

        fields.forEach((field) => {
            if (field.dataset.clinicTimeInputBound === '1') {
                return;
            }
            field.dataset.clinicTimeInputBound = '1';
            if (clinicTimeFormat === '24') {
                field.type = 'time';
                field.setAttribute('title', 'Use 24-hour format (HH:MM)');
                return;
            }

            const current = (field.value || '').trim();
            if (current) {
                field.value = formatClinicTimeFrom24(current);
            }

            field.type = 'text';
            field.setAttribute('placeholder', formatHint);
            field.setAttribute('autocomplete', 'off');
            field.inputMode = 'numeric';
            field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);

            const switchToPickerMode = () => {
                const raw = (field.value || '').trim();
                const time24 = parseClinicTimeTo24(raw);
                field.type = 'time';
                field.value = time24 || '';
                if (typeof field.showPicker === 'function') {
                    try {
                        field.showPicker();
                    } catch (e) {
                        // browser may block without direct interaction
                    }
                }
            };

            const switchToDisplayMode = () => {
                if (field.type === 'time') {
                    const time24 = (field.value || '').trim();
                    field.type = 'text';
                    field.value = time24 ? formatClinicTimeFrom24(time24) : '';
                }
            };

            field.addEventListener('click', () => {
                if (field.type !== 'time') switchToPickerMode();
            });
            field.addEventListener('dblclick', () => {
                if (field.type !== 'time') switchToPickerMode();
            });
            field.addEventListener('keydown', (event) => {
                if (event.altKey && event.key === 'ArrowDown') {
                    event.preventDefault();
                    switchToPickerMode();
                }
            });
            field.addEventListener('blur', switchToDisplayMode);
        });

        scope.querySelectorAll('form').forEach((form) => {
            if (form.dataset.clinicTimeBound === '1') {
                return;
            }
            form.dataset.clinicTimeBound = '1';
            form.addEventListener('submit', function (event) {
                const timeInputs = form.querySelectorAll('input[data-clinic-time="1"]');
                for (const input of timeInputs) {
                    const raw = (input.value || '').trim();
                    if (raw === '') {
                        input.setCustomValidity('');
                        continue;
                    }

                    const time24 = parseClinicTimeTo24(raw);
                    if (!time24) {
                        input.setCustomValidity(`Use time format ${formatHint}`);
                        input.reportValidity();
                        event.preventDefault();
                        return;
                    }

                    input.setCustomValidity('');
                    input.value = time24;
                }
            }, true);
        });
    }

    window.formatClinicDateFromIso = formatClinicDateFromIso;
    window.parseClinicDateToIso = parseClinicDateToIso;
    window.formatClinicTimeFrom24 = formatClinicTimeFrom24;
    window.parseClinicTimeTo24 = parseClinicTimeTo24;
    window.initializeClinicDateInputs = initializeClinicDateInputs;
    window.initializeClinicTimeInputs = initializeClinicTimeInputs;

    let isSyncingRequiredFieldMarkers = false;


initializeClinicDateInputs();initializeClinicTimeInputs();
</script>
<script src="<?= base_url('assets/js/contextual-workspace.js') ?>?v=<?= esc(asset_version('assets/js/contextual-workspace.js')) ?>"></script>
<script src="<?= base_url('assets/js/app-drawer.js') ?>?v=<?= esc(asset_version('assets/js/app-drawer.js')) ?>"></script>
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
    const patientMiniOdontogramData = <?= json_encode(array_values($odontogram ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

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
                type: <?= json_encode((string) ($item['type'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                date: <?= json_encode((string) ($item['date'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                title: <?= json_encode((string) ($item['title'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                description: <?= json_encode((string) ($item['description'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                status: <?= json_encode((string) ($item['status'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                status_label: <?= json_encode((string) ($item['status_label'] ?? ucfirst((string) ($item['status'] ?? ''))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                id: <?= json_encode((string) ($item['id'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                doctor: <?= json_encode((string) ($item['doctor'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                icon: <?= json_encode((string) ($item['icon'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                color: <?= json_encode((string) ($item['color'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                url: <?= json_encode((string) ($item['url'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                meta: <?= json_encode($item['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
            },
            <?php endforeach; ?>
        ];
        filteredTimelineData = [...allTimelineData];
        
        // Add event listeners
        document.getElementById('historySearch').addEventListener('input', filterHistory);
        document.getElementById('historySearch').addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); applyHistoryFilters(); } });
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
        if (nextUrl.searchParams.get('tab') !== tabName) { nextUrl.searchParams.delete('history_before'); }
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
        
        // Bounded tabs were filtered across their finite candidate batch on the server.
        if (isBoundedHistoryTab(tabName)) {
            return false;
        }
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

    function isBoundedHistoryTab(tab) {
        return ['timeline', 'examinations', 'appointments', 'treatments'].includes(tab);
    }

    function applyHistoryFilters() {
        if (!isBoundedHistoryTab(currentTab)) { filterHistory(); return; }
        const url = new URL(window.location.href);
        url.searchParams.set('tab', currentTab);
        url.searchParams.delete('history_before');
        const filters = {history_q: document.getElementById('historySearch').value, history_start: document.getElementById('startDate').value, history_end: document.getElementById('endDate').value};
        for (const [key, raw] of Object.entries(filters)) {
            const value = key === 'history_q' ? raw.trim() : (window.parseClinicDateToIso ? window.parseClinicDateToIso(raw) : raw);
            if (value) { url.searchParams.set(key, value); } else { url.searchParams.delete(key); }
        }
        window.location.assign(url.toString());
    }

    function filterHistory() {
        if (isBoundedHistoryTab(currentTab)) { return; }
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
        if (isBoundedHistoryTab(currentTab)) { applyHistoryFilters(); return; }
        
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
            condition_type: <?= json_encode((string) ($tooth['condition_type'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            condition_description: <?= json_encode((string) ($tooth['condition_description'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            treatment_notes: <?= json_encode((string) ($tooth['treatment_notes'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            treatment_status: <?= json_encode((string) ($tooth['treatment_status'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            treatment_date: <?= json_encode((string) ($tooth['treatment_date'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
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

<script src="<?= base_url('assets/js/studio-patient-workspace.js') ?>?v=<?= esc(asset_version('assets/js/studio-patient-workspace.js')) ?>" defer></script>
<?= $this->endSection() ?>
