<?php helper('asset'); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-finance-record.css') ?>?v=<?= esc(asset_version('assets/css/studio-finance-record.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
    $totalAmount = $finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']);
    $taxPercent = $finance['tax_percentage'] ?? 0;
    $taxableBase = max(0, floatval($finance['amount']) - floatval($finance['discount_amount'] ?? 0));
    if (!$taxPercent && $taxableBase > 0 && !empty($finance['tax_amount'])) {
        $taxPercent = round((floatval($finance['tax_amount']) / $taxableBase) * 100, 2);
    }
    $baseCurrency = $base_currency ?? ($finance['currency'] ?? 'ILS');
    $amountOriginal = $finance['amount_original'] ?? $finance['amount'];
    $exchangeRate = $finance['exchange_rate'] ?? 1;
    $isConverted = !empty($finance['currency']) && $finance['currency'] !== $baseCurrency;
    $creatorName = trim(($finance['creator_first_name'] ?? '') . ' ' . ($finance['creator_last_name'] ?? ''));
    $isExpense = (($finance['transaction_type'] ?? '') === 'expense');
    $isInvoice = (($finance['transaction_type'] ?? '') === 'invoice');
    $currencySymbol = settings()->getCurrencySymbol();
    $supportsPaymentExchangeFields = !empty($supports_payment_exchange_fields);
    $paymentStatusKey = $finance['payment_status'] ?? 'pending';
    $paymentStatusLabel = lang('Finance.' . $paymentStatusKey);
    if ($paymentStatusLabel === 'Finance.' . $paymentStatusKey) {
        $paymentStatusLabel = ucfirst($paymentStatusKey);
    }
    $transactionTypeKey = $finance['transaction_type'] ?? 'transaction';
    $transactionTypeLabel = lang('Finance.' . $transactionTypeKey);
    if ($transactionTypeLabel === 'Finance.' . $transactionTypeKey) {
        $transactionTypeLabel = ucfirst(str_replace('_', ' ', $transactionTypeKey));
    }
    $serviceTypeKey = $finance['service_type'] ?? 'other';
    $serviceTypeLabel = lang('Finance.' . $serviceTypeKey);
    if ($serviceTypeLabel === 'Finance.' . $serviceTypeKey) {
        $serviceTypeLabel = ucfirst(str_replace('_', ' ', $serviceTypeKey));
    }
    $expenseCategoryMap = [
        'rent' => ['en' => 'Rent', 'ar' => 'إيجار'],
        'utilities' => ['en' => 'Utilities', 'ar' => 'مرافق'],
        'supplies' => ['en' => 'Supplies', 'ar' => 'مستلزمات'],
        'marketing' => ['en' => 'Marketing', 'ar' => 'تسويق'],
        'miscellaneous' => ['en' => 'Miscellaneous', 'ar' => 'متفرقات'],
    ];
    $expenseCategoryKey = $finance['expense_category'] ?? 'miscellaneous';
    $expenseCategoryLabel = $expenseCategoryMap[$expenseCategoryKey][service('request')->getLocale()] ?? ucfirst(str_replace('_', ' ', $expenseCategoryKey));
    $paidSoFar = floatval($finance['amount_paid'] ?? 0);
    $balanceDue = floatval($finance['balance_due'] ?? max(0, $totalAmount - $paidSoFar));
    $showSettlementSummary = $isInvoice || $isExpense;
    $showFinanceForms = has_permission('finance', 'edit') && ($isInvoice || $isExpense);
    $showProviderPayoutForms = has_permission('finance', 'edit') && !empty($provider_payouts);
    $billedClinicalChargeLines = $billed_clinical_charge_lines ?? [];
    $billedInventoryUsageRows = $billed_inventory_usage_rows ?? [];
    $billedInventoryUsageByTreatmentId = [];
    $billedInventoryUsageTotal = 0.0;
    foreach ($billedInventoryUsageRows as $usageRow) {
        $usageTreatmentId = (int) ($usageRow['treatment_id'] ?? 0);
        if ($usageTreatmentId > 0) {
            $billedInventoryUsageByTreatmentId[$usageTreatmentId][] = $usageRow;
        }
        $billedInventoryUsageTotal += max(0, (float) ($usageRow['total_cost'] ?? 0));
    }
    $summarizeInventoryUsage = static function (array $row): string {
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
    $billedClinicalTotal = array_reduce($billedClinicalChargeLines, static fn(float $carry, array $row): float => $carry + (float) ($row['net_production_amount'] ?? 0), 0.0);
    $billedClinicalTreatmentIds = [];
    $billedClinicalExaminationIds = [];
    $billedClinicalProviderNames = [];
    foreach ($billedClinicalChargeLines as $clinicalLine) {
        $lineTreatmentId = (int) ($clinicalLine['treatment_id'] ?? 0);
        if ($lineTreatmentId > 0) {
            $billedClinicalTreatmentIds[$lineTreatmentId] = true;
        }

        $lineExaminationId = (int) ($clinicalLine['examination_id'] ?? 0);
        if ($lineExaminationId > 0) {
            $billedClinicalExaminationIds[$lineExaminationId] = true;
        }

        $lineProviderName = trim((string) (($clinicalLine['provider_first_name'] ?? '') . ' ' . ($clinicalLine['provider_last_name'] ?? '')));
        if ($lineProviderName === '') {
            $lineProviderName = trim((string) ($clinicalLine['provider_name_snapshot'] ?? ''));
        }
        if ($lineProviderName !== '') {
            $billedClinicalProviderNames[$lineProviderName] = true;
        }
    }
    $billedClinicalTreatmentCount = count($billedClinicalTreatmentIds);
    $billedClinicalExaminationCount = count($billedClinicalExaminationIds);
    $billedClinicalProviderCount = count($billedClinicalProviderNames);
    $settlementCardTitle = $isExpense ? lang('Finance.expenseSettlementOverview') : lang('Finance.invoiceSettlementOverview');
    $recordPaymentTitle = $isExpense ? lang('Finance.recordExpensePayment') : lang('Finance.recordInvoicePayment');
    $markFullyPaidLabel = $isExpense ? lang('Finance.markExpenseAsFullyPaid') : lang('Finance.markInvoiceAsFullyPaid');
    $deleteLedgerLabel = lang('Finance.deleteLedger');
    if ($deleteLedgerLabel === 'Finance.deleteLedger') {
        $deleteLedgerLabel = service('request')->getLocale() === 'ar' ? 'حذف السجل' : 'Delete Ledger';
    }
    $isRtl = in_array(service('request')->getLocale(), ['ar', 'he', 'fa', 'ur'], true);
    $settlementProgress = $totalAmount > 0 ? max(0, min(100, round(($paidSoFar / $totalAmount) * 100))) : 0;
    $statusTone = match ($paymentStatusKey) {
        'paid' => ['pill' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'hero_bg' => 'rgba(16,185,129,0.22)', 'hero_border' => 'rgba(167,243,208,0.4)'],
        'partial' => ['pill' => 'bg-blue-100 text-blue-700', 'dot' => 'bg-blue-500', 'text' => 'text-blue-700', 'hero_bg' => 'rgba(96,165,250,0.22)', 'hero_border' => 'rgba(191,219,254,0.4)'],
        'pending' => ['pill' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500', 'text' => 'text-amber-700', 'hero_bg' => 'rgba(251,191,36,0.22)', 'hero_border' => 'rgba(253,230,138,0.4)'],
        'overdue' => ['pill' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500', 'text' => 'text-rose-700', 'hero_bg' => 'rgba(244,63,94,0.22)', 'hero_border' => 'rgba(254,205,211,0.4)'],
        default => ['pill' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-400', 'text' => 'text-gray-700', 'hero_bg' => 'rgba(255,255,255,0.12)', 'hero_border' => 'rgba(255,255,255,0.2)'],
    };
    $typeTone = $isExpense
        ? ['icon' => 'fa-receipt', 'bg' => 'bg-amber-600', 'soft' => 'bg-amber-100 text-amber-800']
        : ['icon' => 'fa-file-invoice-dollar', 'bg' => 'bg-blue-600', 'soft' => 'bg-blue-100 text-blue-800'];
    $counterpartyName = trim((string) ($finance['counterparty_name'] ?? ''));
    $relatedPartyTitle = $isExpense ? lang('Finance.payeeVendor') : lang('Finance.patient');
    $relatedPartyValue = $isExpense ? $counterpartyName : trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
    $relatedPartyMeta = $isExpense
        ? ($counterpartyName !== '' ? $expenseCategoryLabel : lang('Finance.unknown'))
        : (!empty($patient['patient_id']) ? lang('Finance.idLabel') . ': ' . $patient['patient_id'] : lang('Finance.unknown'));
    $descriptionPreview = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($finance['description'] ?? ''))));
    $ui = $isRtl
        ? [
            'pageTitle' => $isInvoice ? 'الفاتورة' : ($isExpense ? 'المصروف' : 'المعاملة'),
            'recordPayment' => 'إضافة دفعة',
            'recordRefund' => 'إضافة استرداد',
            'back' => 'رجوع',
            'edit' => 'تعديل',
            'exportPdf' => 'تصدير PDF',
            'details' => 'التفاصيل',
            'patient' => 'المريض',
            'paymentInfo' => 'بيانات الدفع',
            'openProfile' => 'فتح الملف',
            'history' => 'سجل الحركة',
            'historyHelp' => 'كل الدفعات والاستردادات والتعديلات',
            'paymentKicker' => 'دفعة',
            'refundKicker' => 'استرداد',
            'paymentNotice' => 'أدخل بيانات الدفعة. يبقى الرصيد المتبقي محسوباً بعملة العيادة الأساسية.',
            'refundNotice' => 'أدخل بيانات الاسترداد. يبقى الرصيد المتبقي محسوباً بعملة العيادة الأساسية.',
            'savePayment' => 'حفظ الدفعة',
            'saveRefund' => 'حفظ الاسترداد',
            'type' => 'النوع',
            'enteredAmount' => 'المبلغ المسجل',
            'baseAmount' => 'المبلغ الأساسي',
            'method' => 'طريقة الدفع',
            'date' => 'التاريخ',
            'updated' => 'آخر تحديث',
            'amountRequired' => 'المبلغ *',
            'methodRequired' => 'طريقة الدفع *',
            'optionalNote' => 'ملاحظة اختيارية...',
            'refundNote' => 'ملاحظة الاسترداد',
        ]
        : [
            'pageTitle' => $isInvoice ? 'Invoice' : ($isExpense ? 'Expense' : 'Transaction'),
            'recordPayment' => 'Add Payment',
            'recordRefund' => 'Add Refund',
            'back' => 'Back',
            'edit' => 'Edit',
            'exportPdf' => 'Export PDF',
            'details' => 'Details',
            'patient' => 'Patient',
            'paymentInfo' => 'Payment Info',
            'openProfile' => 'Open Profile',
            'history' => 'Payment History',
            'historyHelp' => 'All payments, refunds, and adjustments',
            'paymentKicker' => 'Payment',
            'refundKicker' => 'Refund',
            'paymentNotice' => 'Enter the payment details. Remaining balance stays in the clinic currency.',
            'refundNotice' => 'Enter the refund details. Remaining balance stays in the clinic currency.',
            'savePayment' => 'Save Payment',
            'saveRefund' => 'Save Refund',
            'type' => 'Type',
            'enteredAmount' => 'Entered Amount',
            'baseAmount' => 'Base Amount',
            'method' => 'Payment Method',
            'date' => 'Date',
            'updated' => 'Updated',
            'amountRequired' => 'Amount *',
            'methodRequired' => 'Payment Method *',
            'optionalNote' => 'Optional note...',
            'refundNote' => 'Refund Note',
        ];
    $providerPayoutUi = $isRtl
        ? [
            'sectionTitle' => 'دفعات مقدم الخدمة',
            'sectionStatusOpen' => 'يوجد مبلغ متبق',
            'sectionStatusClear' => 'مكتمل',
            'collected' => 'المبلغ المحصل',
            'doctorShare' => 'حصة مقدم الخدمة',
            'paid' => 'تم دفعه',
            'left' => 'المتبقي',
            'clinicKeeps' => 'حصة العيادة',
            'rule' => 'طريقة الحساب',
            'history' => 'سجل الدفعات',
            'historyHelp' => 'آخر الدفعات المسجلة لمقدم الخدمة هذا',
            'historyEmpty' => 'لا توجد دفعات مسجلة بعد.',
            'historyDate' => 'التاريخ',
            'historyMethod' => 'الطريقة',
            'historyAmount' => 'المبلغ',
            'historyNotes' => 'ملاحظة',
            'action' => 'دفع لمقدم الخدمة',
            'kicker' => 'دفعة مقدم خدمة',
            'notice' => 'أدخل بيانات دفعة مقدم الخدمة. يتم تحديث الرصيد بعد الحفظ.',
            'save' => 'حفظ الدفعة',
            'balanceReady' => 'مبلغ جاهز للدفع',
        ]
        : [
            'sectionTitle' => 'Provider Pay',
            'sectionStatusOpen' => 'Amount left',
            'sectionStatusClear' => 'Done',
            'collected' => 'Collected',
            'doctorShare' => 'Provider Share',
            'paid' => 'Paid',
            'left' => 'Left to Pay',
            'clinicKeeps' => 'Clinic Share',
            'rule' => 'Rule',
            'history' => 'Payment History',
            'historyHelp' => 'Recent payments recorded for this provider',
            'historyEmpty' => 'No provider payments recorded yet.',
            'historyDate' => 'Date',
            'historyMethod' => 'Method',
            'historyAmount' => 'Amount',
            'historyNotes' => 'Note',
            'action' => 'Pay Provider',
            'kicker' => 'Provider Payment',
            'notice' => 'Enter the provider payment details. The balance updates after saving.',
            'save' => 'Save Payment',
            'balanceReady' => 'Amount ready to pay',
        ];
    $providerPayoutPaymentHistory = $provider_payout_payment_history ?? [];
?>
<div class="finance-record"><header class="fr-heading"><div><h1><?= esc($transactionTypeLabel) ?> <?= esc('#'.$finance['transaction_id']) ?></h1></div><a class="button secondary" href="<?= esc(workflow_back('finance',$return_to??null)) ?>"><?= esc($ui['back']) ?></a></header><dl class="fr-context"><div><dt><?= esc(lang('Finance.status')) ?></dt><dd><?= esc($paymentStatusLabel) ?></dd></div><div><dt><?= esc($ui['date']) ?></dt><dd><?= !empty($finance['transaction_date'])?formatDate($finance['transaction_date']):esc(lang('Finance.unknown')) ?></dd></div><div><dt><?= esc($ui['method']) ?></dt><dd><?= esc(lang('Finance.'.$finance['payment_method'])) ?></dd></div><div><dt><?= esc(lang('Finance.currency')) ?></dt><dd><?= esc($baseCurrency) ?></dd></div></dl><div class="fr-layout"><div class="fr-main">                <!-- Financial Details & Breakdown -->
                <div class="fr-panel">
                    <div class="fr-panel-heading">
                        <div >
                            <div >

                            </div>
                            <h2 ><?= esc($ui['details']) ?></h2>
                        </div>
                        <span >
                            <?= esc($isExpense ? $expenseCategoryLabel : $serviceTypeLabel) ?>
                        </span>
                    </div>
                    <div class="fr-panel-body">
                        <div >
                            <div class="fr-field-grid">
                                <div class="fr-fact">
                                    <label class="fr-label"><?= $isExpense ? lang('Finance.expenseDescription') : lang('Finance.serviceDescription') ?></label>
                                    <div >
                                        <?= !empty($finance['description']) ? nl2br(htmlspecialchars($finance['description'])) : '<span class="text-gray-400 italic">' . esc(lang('Finance.noDescriptionProvided')) . '</span>' ?>
                                    </div>
                                </div>
                                <div class="fr-fact">
                                    <label class="fr-label"><?= lang('Finance.createdByLabel') ?></label>
                                    <div >
                                        <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?>
                                    </div>
                                </div>
                                <?php if ($isExpense && !empty($finance['counterparty_name'])): ?>
                                <div class="fr-fact">
                                    <label class="fr-label"><?= lang('Finance.payeeVendor') ?></label>
                                    <div >
                                        <?php if (!empty($supplier['id'])): ?>
                                            <a href="<?= base_url('inventory/suppliers/' . (int) $supplier['id']) ?>" >

                                                <span><?= esc($finance['counterparty_name']) ?></span>
                                            </a>
                                        <?php else: ?>
                                            <?= esc($finance['counterparty_name']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($finance['service_details'])): ?>
                                <div class="fr-fact">
                                    <label class="fr-label"><?= lang('Finance.technicalDetails') ?></label>
                                    <div >
                                        <?= nl2br(htmlspecialchars($finance['service_details'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="fr-breakdown">
                                <div class="fr-lines">
                                    <div >
                                        <span ><?= lang('Finance.subtotal') ?></span>
                                        <span ><?= number_format($finance['amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php if ($isConverted): ?>
                                    <div >
                                        <span ><?= lang('Finance.original') ?></span>
                                        <span >
                                            <?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?>
                                            @ <?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['discount_amount'] > 0): ?>
                                    <div >
                                        <span ><?= lang('Finance.discount') ?></span>
                                        <span >-<?= number_format($finance['discount_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['tax_amount'] > 0): ?>
                                    <div >
                                        <span ><?= lang('Finance.taxPercent') ?> (<?= number_format($taxPercent, 2) ?>%)</span>
                                        <span >+<?= number_format($finance['tax_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div >
                                        <span ><?= lang('Finance.total') ?></span>
                                        <span ><?= number_format($totalAmount, 2) ?> <span ><?= esc($baseCurrency) ?></span></span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($billedClinicalChargeLines)): ?>
                <div class="fr-panel">
                    <div class="fr-panel-heading">
                        <div >
                            <div >

                            </div>
                            <div>
                                <h2 ><?= esc(lang('Finance.clinicalWorkBilled')) ?></h2>
                                <p ><?= esc(lang('Finance.clinicalWorkBilledHelp')) ?></p>
                            </div>
                        </div>
                        <span  dir="ltr">
                            <?= esc(formatCurrency($billedClinicalTotal)) ?>
                        </span>
                    </div>
                    <div class="fr-panel-body">
                        <div class="fr-clinical-context">
                            <div >
                                <div>
                                    <p ><?= esc(lang('Finance.clinicalTraceSnapshot')) ?></p>
                                    <p ><?= esc(lang('Finance.clinicalTraceSnapshotHelp')) ?></p>
                                </div>
                                <?php if (!empty($patient['id'])): ?>
                                    <a href="<?= esc(workflow_context_url('patient/' . (int) $patient['id'], $return_to ?? null)) ?>" class="button secondary">

                                        <span><?= esc(lang('Finance.openPatientProfile')) ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="fr-field-grid">
                                <div class="fr-trace-stat">
                                    <p ><?= esc(lang('Finance.clinicalTracePatient')) ?></p>
                                    <p >
                                        <?= esc($relatedPartyValue !== '' ? $relatedPartyValue : lang('Finance.unknown')) ?>
                                    </p>
                                    <p ><?= esc($relatedPartyMeta) ?></p>
                                </div>
                                <div class="fr-trace-stat">
                                    <p ><?= esc(lang('Finance.clinicalTraceLines')) ?></p>
                                    <p ><?= esc((string) count($billedClinicalChargeLines)) ?></p>
                                </div>
                                <div class="fr-trace-stat">
                                    <p ><?= esc(lang('Finance.clinicalTraceTreatments')) ?></p>
                                    <p ><?= esc((string) $billedClinicalTreatmentCount) ?></p>
                                </div>
                                <div class="fr-trace-stat">
                                    <p ><?= esc(lang('Finance.clinicalTraceExaminations')) ?></p>
                                    <p ><?= esc((string) $billedClinicalExaminationCount) ?></p>
                                </div>
                                <div class="fr-trace-stat">
                                    <p ><?= esc(lang('Finance.clinicalTraceProviders')) ?></p>
                                    <p ><?= esc((string) $billedClinicalProviderCount) ?></p>
                                </div>
                                <div class="fr-trace-stat">
                                    <p ><?= esc(lang('UI.moneyTrailInventoryCost')) ?></p>
                                    <p  dir="ltr"><?= esc(formatCurrency($billedInventoryUsageTotal)) ?></p>
                                </div>
                            </div>
                        </div>
                        <div >
                            <div >
                                <table class="studio-grid fr-table" data-grid-widths="13,18,13,13,16,15,12">
                                    <thead >
                                        <tr>
                                            <th ><?= esc(lang('Finance.procedure')) ?></th>
                                            <th ><?= esc(lang('Finance.linkedTreatment')) ?></th>
                                            <th ><?= esc(lang('Finance.linkedExamination')) ?></th>
                                            <th ><?= esc(lang('Finance.provider')) ?></th>
                                            <th ><?= esc(lang('Finance.serviceDate')) ?></th>
                                            <th ><?= esc(lang('UI.moneyTrailInventoryCost')) ?></th>
                                            <th ><?= esc(lang('Finance.amount')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="fr-lines">
                                        <?php foreach ($billedClinicalChargeLines as $line): ?>
                                            <?php
                                                $providerDisplay = trim((string) (($line['provider_first_name'] ?? '') . ' ' . ($line['provider_last_name'] ?? '')));
                                                if ($providerDisplay === '') {
                                                    $providerDisplay = trim((string) ($line['provider_name_snapshot'] ?? ''));
                                                }
                                                if ($providerDisplay === '') {
                                                    $providerDisplay = lang('Finance.unknown');
                                                }
                                                $treatmentId = (int) ($line['treatment_id'] ?? 0);
                                                $examinationId = (int) ($line['examination_id'] ?? 0);
                                                $treatmentLabel = trim((string) ($line['treatment_name'] ?? '')) ?: ((string) ($line['treatment_number'] ?? ''));
                                                $examinationLabel = trim((string) ($line['examination_number'] ?? ''));
                                                $sourceType = trim((string) ($line['source_entity_type'] ?? ''));
                                                $sourceId = (int) ($line['source_entity_id'] ?? 0);
                                                $lineInventoryRows = $treatmentId > 0 ? ($billedInventoryUsageByTreatmentId[$treatmentId] ?? []) : [];
                                                $lineInventoryCost = array_reduce($lineInventoryRows, static fn(float $carry, array $row): float => $carry + max(0, (float) ($row['total_cost'] ?? 0)), 0.0);
                                            ?>
                                            <tr >
                                                <td >
                                                    <div ><?= esc($line['procedure_name'] ?? lang('Finance.clinicalChargeLine')) ?></div>
                                                    <div >
                                                        <span >
                                                            <?= esc(lang('Finance.clinicalChargeLine')) ?> #<?= esc((string) ($line['id'] ?? '')) ?>
                                                        </span>
                                                        <?php if ($sourceType !== ''): ?>
                                                            <span >
                                                                <?= esc(lang('Finance.clinicalSource')) ?>: <?= esc($sourceType) ?><?= $sourceId > 0 ? ' #' . esc((string) $sourceId) : '' ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($line['tooth_numbers'])): ?>
                                                        <div ><?= esc(lang('Finance.tooth')) ?>: <span dir="ltr"><?= esc($line['tooth_numbers']) ?></span></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td >
                                                    <?php if ($treatmentId > 0): ?>
                                                        <a href="<?= esc(workflow_context_url('treatment/' . $treatmentId, $return_to ?? null)) ?>" >
                                                            <?= esc($treatmentLabel !== '' ? $treatmentLabel : ('#' . $treatmentId)) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span ><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td >
                                                    <?php if ($examinationId > 0): ?>
                                                        <a href="<?= esc(workflow_context_url('examination/' . $examinationId, $return_to ?? null)) ?>" >
                                                            <?= esc($examinationLabel !== '' ? $examinationLabel : ('#' . $examinationId)) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span ><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td ><?= esc($providerDisplay) ?></td>
                                                <td ><?= !empty($line['service_date']) ? esc(formatDate($line['service_date'])) : esc(lang('Finance.notAvailable')) ?></td>
                                                <td >
                                                    <?php if ($lineInventoryCost > 0.009): ?>
                                                        <div  dir="ltr"><?= esc(formatCurrency($lineInventoryCost)) ?></div>
                                                        <div >
                                                            <?php foreach (array_slice($lineInventoryRows, 0, 2) as $usageRow): ?>
                                                                <?php $usageFinanceId = (int) ($usageRow['finance_entry_id'] ?? 0); ?>
                                                                <?php if ($usageFinanceId > 0): ?>
                                                                    <a href="<?= esc(workflow_context_url('finance/' . $usageFinanceId, $return_to ?? null)) ?>" >

                                                                        <span><?= esc($summarizeInventoryUsage($usageRow)) ?></span>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span >

                                                                        <span><?= esc($summarizeInventoryUsage($usageRow)) ?></span>
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span ><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td  dir="ltr"><?= esc(formatCurrency($line['net_production_amount'] ?? 0)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($provider_payouts)): ?>
                <div class="fr-panel">
                    <div class="fr-panel-heading">
                        <div >
                            <div >

                            </div>
                            <h2 ><?= esc($providerPayoutUi['sectionTitle']) ?></h2>
                        </div>
                        <span >
                            <?= (bool) array_filter($provider_payout_summary['currency_buckets'] ?? [], static fn(array $b): bool => ($b['provider_balance_due'] ?? 0) > 0) ? esc($providerPayoutUi['sectionStatusOpen']) : esc($providerPayoutUi['sectionStatusClear']) ?>
                        </span>
                    </div>
                    <div class="fr-panel-body">
                        <p ><?= esc(lang('Finance.providerVoidBalanceHelp')) ?></p>
                        <div class="fr-field-grid">
                            <div class="fr-provider-stat">
                                <p ><?= esc($providerPayoutUi['collected']) ?></p>
                                <p ><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'gross_amount')) ?></p>
                            </div>
                            <div class="fr-provider-stat">
                                <p ><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                <p ><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'provider_share_amount')) ?></p>
                            </div>
                            <div class="fr-provider-stat">
                                <p ><?= esc($providerPayoutUi['paid']) ?></p>
                                <p ><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'provider_paid_amount')) ?></p>
                            </div>
                            <div class="fr-provider-stat">
                                <p ><?= esc($providerPayoutUi['left']) ?></p>
                                <p ><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'provider_balance_due')) ?></p>
                            </div>
                            <div class="fr-provider-stat">
                                <p ><?= esc($providerPayoutUi['clinicKeeps']) ?></p>
                                <p ><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'clinic_share_amount')) ?></p>
                            </div>
                        </div>

                        <div >
                            <?php foreach ($provider_payouts as $providerPayout): ?>
                                <?php
                                    $providerDisplayName = trim(($providerPayout['provider_first_name'] ?? '') . ' ' . ($providerPayout['provider_last_name'] ?? ''));
                                    if ($providerDisplayName === '') {
                                        $providerDisplayName = $providerPayout['provider_name_snapshot'] ?? lang('Finance.guestProvider');
                                    }
                                    $providerPayoutPayments = $providerPayoutPaymentHistory[(int) ($providerPayout['id'] ?? 0)] ?? [];
                                    $providerStatusKey = $providerPayout['status'] ?? 'unpaid';
                                    $providerStatusLabel = lang('Finance.' . $providerStatusKey);
                                    if ($providerStatusLabel === 'Finance.' . $providerStatusKey) {
                                        $providerStatusLabel = ucfirst($providerStatusKey);
                                    }
                                    $providerStatusClass = $providerStatusKey === 'paid'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : ($providerStatusKey === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700');
                                    $providerTypeKey = (string) ($providerPayout['provider_type'] ?? 'guest');
                                    $providerTypeLabel = $providerTypeKey === 'user' ? lang('Finance.clinicUser') : lang('Finance.guestProvider');
                                    $providerUserId = (int) ($providerPayout['provider_user_id'] ?? 0);
                                ?>
                                <div class="fr-provider-record">
                                    <div >
                                        <div>
                                            <div >
                                                <h3 ><?= esc($providerDisplayName) ?></h3>
                                                <span >
                                                    <?= esc($providerTypeLabel) ?>
                                                </span>
                                                <span >
                                                    <?= esc($providerStatusLabel) ?>
                                                </span>
                                            </div>
                                            <p >
                                                <?= esc($providerPayoutUi['rule']) ?>:
                                                <?= esc(lang('Finance.' . (($providerPayout['split_mode'] ?? '') === 'percent' ? 'percentage' : (($providerPayout['split_mode'] ?? '') === 'fixed' ? 'fixedAmount' : ($providerPayout['split_mode'] ?? 'percentage'))))) ?>
                                                <?= esc($providerPayout['split_value'] ?? 0) ?><?= ($providerPayout['split_mode'] ?? '') === 'percent' ? '%' : '' ?>
                                            </p>
                                            <?php if (!empty($providerPayout['notes'])): ?>
                                                <p ><?= esc($providerPayout['notes']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($providerTypeKey === 'user' && $providerUserId > 0): ?>
                                                <a href="<?= esc(workflow_context_url('users/' . $providerUserId, $return_to ?? null)) ?>" >

                                                    <span><?= esc(lang('Finance.openProviderProfile')) ?></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fr-field-grid">
                                            <div class="fr-provider-stat">
                                                <p ><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                                <p ><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_share_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                            <div class="fr-provider-stat">
                                                <p ><?= esc($providerPayoutUi['paid']) ?></p>
                                                <p ><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_paid_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                            <div class="fr-provider-stat">
                                                <p ><?= esc($providerPayoutUi['left']) ?></p>
                                                <p ><?= esc(\App\Services\ProviderCurrencySummary::native(($providerPayout['status'] ?? '') === 'void' ? 0 : ($providerPayout['provider_balance_due'] ?? 0), $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fr-provider-history">
                                        <div >
                                            <div>
                                                <h4 ><?= esc($providerPayoutUi['history']) ?></h4>
                                                <p ><?= esc($providerPayoutUi['historyHelp']) ?></p>
                                            </div>
                                            <span >
                                                <?= count($providerPayoutPayments) ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($providerPayoutPayments)): ?>
                                            <div >
                                                <table class="studio-grid fr-table" data-grid-widths="24,24,22,30">
                                                    <thead >
                                                        <tr >
                                                            <th ><?= esc($providerPayoutUi['historyDate']) ?></th>
                                                            <th ><?= esc($providerPayoutUi['historyMethod']) ?></th>
                                                            <th ><?= esc($providerPayoutUi['historyAmount']) ?></th>
                                                            <th ><?= esc($providerPayoutUi['historyNotes']) ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="fr-lines">
                                                        <?php foreach ($providerPayoutPayments as $paymentRow): ?>
                                                            <tr >
                                                                <td ><?= !empty($paymentRow['paid_date']) ? esc(formatDate($paymentRow['paid_date'])) : '-' ?></td>
                                                                <td ><?= esc(lang('Finance.' . ($paymentRow['payment_method'] ?? 'cash'))) ?></td>
                                                                <td ><?= esc(\App\Services\ProviderCurrencySummary::native($paymentRow['amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></td>
                                                                <td ><?= !empty($paymentRow['notes']) ? esc($paymentRow['notes']) : '-' ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div ><?= esc($providerPayoutUi['historyEmpty']) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (($providerPayout['status'] ?? '') !== 'void' && floatval($providerPayout['provider_balance_due'] ?? 0) > 0 && has_permission('finance', 'edit')): ?>
                                        <?php $providerPayoutModalId = 'providerPayoutModal' . (int) $providerPayout['id']; ?>
                                        <div class="fr-provider-cta">
                                            <div >
                                                <p ><?= esc($providerPayoutUi['balanceReady']) ?></p>
                                                <p ><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_balance_due'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                            <button type="button" data-finance-open="<?= esc($providerPayoutModalId) ?>" class="button secondary">
                                                <?php if ($isRtl): ?>
                                                    <span><?= esc($providerPayoutUi['action']) ?></span>
                                                <?php else: ?>
                                                    <span><?= esc($providerPayoutUi['action']) ?></span>
                                                <?php endif; ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($finance['notes'])): ?>
                <!-- Additional Notes -->
                <div class="fr-panel">
                    <h2 >
                        <?= lang('Finance.administrativeNotes') ?>
                    </h2>
                    <div class="fr-notes">
                        <?= nl2br(htmlspecialchars($finance['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
</div><aside class="fr-side"><section class="fr-panel fr-settlement"><h2><?= esc($settlementCardTitle) ?></h2><dl class="fr-position"><div><dt><?= esc(lang('Finance.totalAmount')) ?></dt><dd dir="ltr"><?= number_format($totalAmount,2) ?> <?= esc($baseCurrency) ?></dd></div><div><dt><?= esc(lang('Finance.paidToDate')) ?></dt><dd dir="ltr"><?= number_format($paidSoFar,2) ?> <?= esc($baseCurrency) ?></dd></div><div><dt><?= esc(lang('Finance.balanceDue')) ?></dt><dd dir="ltr"><?= number_format($balanceDue,2) ?> <?= esc($baseCurrency) ?></dd></div></dl><div class="fr-actions fr-actions--stack">
<?php if($showFinanceForms && $showSettlementSummary && floatval($finance['balance_due']??0)>0): ?><button type="button" class="button primary" data-finance-open="paymentModal"><?= esc($ui['recordPayment']) ?></button><?php endif; ?>
<?php if($showFinanceForms && $isInvoice): ?><button type="button" class="button secondary" data-finance-open="refundModal"><?= esc($ui['recordRefund']) ?></button><?php endif; ?>
<a class="button secondary" href="<?= workflow_context_url('finance/'.$finance['id'].'/edit',$return_to??null) ?>"><?= esc($ui['edit']) ?></a>
<?php if($isInvoice && config('InvoiceCorrections')->enabled===true): ?><a class="button secondary" href="<?= esc(site_url('finance/'.(int)$finance['id'].'/corrections'),'attr') ?>"><?= esc(lang('InvoiceCorrections.title')) ?></a><?php endif; ?>
<a class="button secondary" href="<?= base_url('finance/'.$finance['id'].'/invoice') ?>"><?= esc($ui['exportPdf']) ?></a></div></section>                    <?php if ($patient): ?>
                    <div class="fr-panel">
                        <h2 >

                            <?= esc($ui['patient']) ?>
                        </h2>
                        <div class="fr-person">
                            <div class="fr-avatar">
                                <?= esc(ui_initials($patient['first_name'] ?? '', $patient['last_name'] ?? '', 'P')) ?>
                            </div>
                            <div >
                                <div >
                                    <?= esc(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?>
                                </div>
                                <div ><?= lang('Finance.idLabel') ?>: <?= esc($patient['patient_id']) ?></div>
                            </div>
                        </div>
                        <div class="fr-actions">
                            <a href="<?= workflow_context_url('patient/' . $patient['id'], $return_to ?? null) ?>" class="button secondary">

                                <span><?= esc(lang('Finance.openPatientProfile')) ?></span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($check_attachment) || !empty($finance_attachment)): ?>
                    <div class="fr-panel">
                        <h2 >

                            <?= $isRtl ? 'المرفقات' : 'Attachments' ?>
                        </h2>
                        <dl class="fr-documents">
                            <?php if (!empty($check_attachment)): ?>
                            <div class="fr-document">
                                <dt ><?= lang('Finance.checkScanLabel') ?></dt>
                                <div class="fr-actions">
                                    <a  href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <?= lang('Finance.download') ?>
                                    </a>
                                    <a  href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <?= lang('Finance.view') ?>
                                    </a>
                                </div>
                                <?php $isImage = !empty($check_attachment['mime_type']) && strpos($check_attachment['mime_type'], 'image/') === 0; ?>
                                <?php if ($isImage): ?>
                                    <div >
                                        <img loading="lazy" src="<?= base_url('file/download/' . $check_attachment['id']) ?>" alt="<?= esc(lang('Finance.checkScanPreview')) ?>" >
                                    </div>
                                <?php else: ?>
                                    <p ><?= lang('Finance.imagePreviewOnly') ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($finance_attachment)): ?>
                            <div class="fr-document">
                                <dt ><?= lang('Finance.attachmentLabel') ?></dt>
                                <div class="fr-actions">
                                    <a  href="<?= base_url('file/download/' . $finance_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <?= lang('Finance.download') ?>
                                    </a>
                                    <a  href="<?= base_url('file/download/' . $finance_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <?= lang('Finance.view') ?>
                                    </a>
                                    <span ><?= esc($finance_attachment['original_name'] ?? '') ?></span>
                                </div>
                                <?php $isFinanceAttachmentImage = !empty($finance_attachment['mime_type']) && strpos($finance_attachment['mime_type'], 'image/') === 0; ?>
                                <?php if ($isFinanceAttachmentImage): ?>
                                    <div >
                                        <img loading="lazy" src="<?= base_url('file/download/' . $finance_attachment['id']) ?>" alt="<?= esc(lang('Finance.attachmentPreview')) ?>" >
                                    </div>
                                <?php else: ?>
                                    <p ><?= lang('Finance.imagePreviewOnly') ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <?php endif; ?>

                    <?php if ($examination): ?>
                    <div class="fr-panel">
                        <h2 >

                            <?= lang('Finance.clinicalLink') ?>
                        </h2>
                        <div >
                            <div >

                            </div>
                            <div >
                                <div >
                                    <?= formatDate($examination['examination_date']) ?>
                                </div>
                                <div >
                                    <?= lang('Finance.status') ?>: <?= ucfirst($examination['status']) ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?= workflow_context_url('examination/' . $examination['id'], $return_to ?? null) ?>" class="button secondary">
                            <?= lang('Finance.viewClinicalRecord') ?>
                        </a>
                    </div>
                    <?php endif; ?>
</aside></div>        <?php if ($isInvoice || $isExpense): ?>
        <div class="fr-panel">
            <div class="fr-panel-heading">
                <div >
                    <span ></span>
                    <div>
                        <h3 ><?= esc($ui['history']) ?></h3>
                        <p ><?= esc($ui['historyHelp']) ?></p>
                    </div>
                </div>
                <div >
                    <span >
                        <?= count($payments ?? []) ?> <?= lang('Finance.settlement') ?>
                    </span>
                    <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" >

                        <?= esc($ui['exportPdf']) ?>
                    </a>
                </div>
            </div>
            <div >
                <div >
                <table class="studio-grid fr-table" data-grid-widths="<?= has_permission('finance','edit')?'12,10,11,16,14,14,14,9':'14,12,12,18,15,15,14' ?>">
                    <thead >
                        <tr>
                            <th >
                                <div >

                                    <span><?= lang('Finance.date') ?></span>
                                </div>
                            </th>
                            <th >
                                <div >

                                    <span><?= esc($ui['type']) ?></span>
                                </div>
                            </th>
                            <th >
                                <div >

                                    <span><?= lang('Finance.method') ?></span>
                                </div>
                            </th>
                            <th >
                                <div >

                                    <span><?= lang('Finance.recordedBy') ?></span>
                                </div>
                            </th>
                            <th >
                                <div >

                                    <span><?= esc($ui['enteredAmount']) ?></span>
                                </div>
                            </th>
                            <th >
                                <div >

                                    <span><?= esc($ui['baseAmount']) ?></span>
                                </div>
                            </th>
                            <th >
                                <div >

                                    <span><?= lang('Finance.notes') ?></span>
                                </div>
                            </th>
                            <?php if (has_permission('finance', 'edit')): ?>
                            <th >
                                <div >

                                    <span><?= lang('Finance.actions') ?></span>
                                </div>
                            </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="fr-lines">
                        <?php if (!empty($payments)): ?>
                            <?php foreach ($payments as $payment): ?>
                            <?php
                                $type = $payment['type'] ?? 'payment';
                                $typeClass = 'bg-emerald-100 text-emerald-700';
                                $amountClass = 'text-emerald-700';
                                $prefix = '+';

                                if ($type === 'refund') {
                                    $typeClass = 'bg-rose-100 text-rose-700';
                                    $amountClass = 'text-rose-700';
                                    $prefix = '-';
                                } elseif ($type === 'void') {
                                    $typeClass = 'bg-slate-100 text-slate-500 line-through';
                                    $amountClass = 'text-slate-400 line-through';
                                    $prefix = '';
                                } elseif ($type === 'adjustment') {
                                    $typeClass = 'bg-amber-100 text-amber-700';
                                    $amountClass = 'text-amber-700';
                                    $prefix = '+';
                                }
                            ?>
                            <tr >
                                <td >
                                    <?= formatDate($payment['payment_date']) ?>
                                </td>
                                <td >
                                    <span >
                                        <?= lang('Finance.' . $type) ?>
                                    </span>
                                </td>
                                <td >
                                    <?= lang('Finance.' . $payment['payment_method']) ?>
                                </td>
                                <td >
                                    <?php $paymentCreator = trim(($payment['creator_first_name'] ?? '') . ' ' . ($payment['creator_last_name'] ?? '')); ?>
                                    <?= $paymentCreator !== '' ? 'Dr. ' . esc($paymentCreator) : lang('Finance.unknown') ?>
                                </td>
                                <td >
                                    <?php
                                        $paymentCurrency = $payment['currency'] ?? $baseCurrency;
                                        $paymentExchangeRate = floatval($payment['exchange_rate'] ?? 1);
                                        $paymentOriginalAmount = isset($payment['amount_original']) ? floatval($payment['amount_original']) : floatval($payment['amount']);
                                    ?>
                                    <div >
                                        <span >
                                            <?= $prefix . number_format($paymentOriginalAmount, 2) ?> <?= esc($paymentCurrency) ?>
                                        </span>
                                        <?php if ($paymentCurrency !== $baseCurrency || abs($paymentExchangeRate - 1) > 0.0001): ?>
                                            <span >
                                                <?= lang('Finance.rateShort') ?> <?= rtrim(rtrim(number_format($paymentExchangeRate, 6), '0'), '.') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td >
                                    <?= $prefix . formatCurrency($payment['amount']) ?>
                                </td>
                                <td >
                                    <?php if (!empty($payment['notes'])): ?>
                                        <?= esc($payment['notes']) ?>
                                    <?php else: ?>
                                        <span >&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (has_permission('finance', 'edit')): ?>
                                <td >
                                    <?php if ($type === 'payment'): ?>
                                        <button type="button" data-finance-void-payment="<?= (int) $payment['id'] ?>"  title="<?= esc(lang('Finance.voidPayment')) ?>">
<?= esc(lang('Finance.voidPayment')) ?></button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <?php $emptyColspan = has_permission('finance', 'edit') ? 8 : 7; ?>
                                <td colspan="<?= $emptyColspan ?>" >
                                    <?= lang('Finance.settlementHistoryEmpty') ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <?php endif; ?>
</div>
<?php if ($showFinanceForms): ?>
    <?php include APPPATH.'Views/finance/partials/modal_paymentModal.php'; ?>

    <?php if ($isInvoice): ?>
    <?php include APPPATH.'Views/finance/partials/modal_refundModal.php'; ?>
    <?php endif; ?>
<?php endif; ?>

<?php if ($showProviderPayoutForms): ?>
    <?php foreach ($provider_payouts as $providerPayout): ?>
        <?php if (($providerPayout['status'] ?? '') === 'void' || floatval($providerPayout['provider_balance_due'] ?? 0) <= 0): ?>
            <?php continue; ?>
        <?php endif; ?>
        <?php
            $providerPayoutModalId = 'providerPayoutModal' . (int) $providerPayout['id'];
            $providerDisplayName = trim(($providerPayout['provider_first_name'] ?? '') . ' ' . ($providerPayout['provider_last_name'] ?? ''));
            if ($providerDisplayName === '') {
                $providerDisplayName = $providerPayout['provider_name_snapshot'] ?? lang('Finance.guestProvider');
            }
            $providerBalanceDue = max(0, (float) ($providerPayout['provider_balance_due'] ?? 0));
            $providerPayoutAmountValue = number_format($providerBalanceDue, 2, '.', '');
        ?>
        <?php include APPPATH.'Views/finance/partials/modal_provider_payment.php'; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php include APPPATH.'Views/components/studio_csrf_runtime.php'; ?>
<?php include APPPATH.'Views/finance/partials/field_widgets.php'; ?>
<?php include APPPATH.'Views/finance/partials/record_runtime.php'; ?>
<script src="<?= base_url('assets/js/studio-finance-record.js') ?>?v=<?= esc(asset_version('assets/js/studio-finance-record.js')) ?>" defer></script>
<script src="<?= base_url('assets/js/studio-finance-controls.js') ?>?v=<?= esc(asset_version('assets/js/studio-finance-controls.js')) ?>" defer></script>
<?= $this->endSection() ?>
