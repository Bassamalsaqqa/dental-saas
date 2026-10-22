<?php

namespace App\Services;

/** One presenter shared by the legacy workspace and bounded history candidates. */
final class PatientWorkspaceTimelinePresenter
{
    public static function build(array $context): array
    {
        extract($context, EXTR_SKIP);
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
                $sameDayExamCount = (int) ($sameDayClinical['examination_count'] ?? count($sameDayClinical['examinations'] ?? []));
                $sameDayTreatmentCount = (int) ($sameDayClinical['treatment_count'] ?? count($sameDayClinical['treatments'] ?? []));
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
        usort($timeline, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        return $timeline;
    }
    /**
     * Filter one finite candidate batch using the same labels/meta as the old client.
     * An empty batch with a continuation means search is unfinished, never "no records".
     * The caller passes canonical clinic date strings; range endpoints retain midnight semantics.
     */
    public static function page(array $context, array $keys, ?array $next, string $query = '', ?string $start = null, ?string $end = null, int $limit = 25, bool $clinicalTable = false): array
    {
        if (count($keys) > 100 || $limit < 1 || $limit > 100 || mb_strlen($query) > 200) {
            throw new \InvalidArgumentException('Invalid workspace presenter page.');
        }
        foreach ([$start,$end] as $date) {
            if ($date !== null && !ClinicSchedulingSettings::validDate($date)) {
                throw new \InvalidArgumentException('Invalid workspace date filter.');
            }
        }
        $ids = [];
        $clinicalSearch = [];
        if ($clinicalTable) {
            foreach (['examination' => 'examinations','appointment' => 'appointments','treatment' => 'treatments'] as $type => $name) {
                foreach ($context[$name] ?? [] as $row) {
                    $texts = [];
                    foreach ($row as $value) {
                        if (is_scalar($value)) {
                            $texts[] = (string)$value;
                        }
                    }$clinicalSearch[$type.':'.(int)($row['id'] ?? 0)] = implode(' ', $texts);
                }
            }
        }

        foreach ($keys as $key) {
            if (!isset($key['event_type'],$key['id'],$key['event_at'],$key['source_rank'],$key['source_order']) || !in_array($key['event_type'], PatientWorkspaceHistoryService::TYPES, true) || !is_int($key['id']) || $key['id'] <= 0) {
                throw new \InvalidArgumentException('Invalid workspace candidate.');
            }$ids[$key['event_type']][$key['id']] = true;
        }
        // Never expand all financial/context rows into a second unbounded timeline copy.
        foreach (['examination' => 'examinations','appointment' => 'appointments','treatment' => 'treatments','prescription' => 'prescriptions','contract' => 'contracts','imaging' => 'attachments','finance' => 'finances'] as $type => $name) {
            $context[$name] = array_values(array_filter($context[$name] ?? [], static fn (array$row): bool => isset($ids[$type][(int)($row['id'] ?? 0)])));
        }
        foreach (['finance_payment' => 'payment_rows','provider_payout' => 'provider_payouts','inventory_usage' => 'inventory_usage_rows'] as $type => $name) {
            $context['moneyTrailForPatient'][$name] = array_values(array_filter($context['moneyTrailForPatient'][$name] ?? [], static fn (array$row): bool => isset($ids[$type][(int)($row['id'] ?? 0)])));
        }
        $presented = [];
        foreach (self::build($context) as $item) {
            $presented[$item['type'].':'.(int)$item['id']] = $item;
        }
        $needle = mb_strtolower($query, 'UTF-8');
        $items = [];
        $matchedKeys = [];
        $cursor = null;
        $scanned = 0;
        $total = count($keys);
        foreach ($keys as $index => $key) {
            ++$scanned;
            $cursor = ['event_at' => $key['event_at'],'source_rank' => $key['source_rank'],'source_order' => $key['source_order'],'id' => $key['id']];
            $item = $presented[$key['event_type'].':'.$key['id']] ?? null;
            if ($item === null) {
                continue;
            }
            if ($needle !== '') {
                $texts = [(string)($item['title'] ?? ''),(string)($item['description'] ?? ''),(string)($item['status'] ?? ''),(string)($item['doctor'] ?? '')];
                $meta = [];
                foreach ($item['meta'] ?? [] as $value) {
                    $meta[] = (string)($value['text'] ?? '');
                }$texts[] = implode(' ', $meta);
                if ($clinicalTable) {
                    $texts[] = $clinicalSearch[$key['event_type'].':'.$key['id']] ?? '';
                }
                $match = false;
                foreach ($texts as $text) {
                    if (str_contains(mb_strtolower($text, 'UTF-8'), $needle)) {
                        $match = true;
                        break;
                    }
                }if (!$match) {
                    continue;
                }
            }
            $timestamp = strtotime((string)($item['date'] ?? ''));
            if (!($clinicalTable && $timestamp === false) && (($start !== null && ($timestamp === false || $timestamp < strtotime($start))) || ($end !== null && ($timestamp === false || $timestamp > strtotime($end))))) {
                continue;
            }
            $items[] = $item;
            $matchedKeys[] = $key;
            if (count($items) === $limit) {
                return ['items' => $items,'matched_keys' => $matchedKeys,'next' => ($index < $total - 1 || $next !== null) ? $cursor : null,'scanned_count' => $scanned];
            }
        }
        return ['items' => $items,'matched_keys' => $matchedKeys,'next' => $next !== null ? $cursor : null,'scanned_count' => $scanned];
    }

}
