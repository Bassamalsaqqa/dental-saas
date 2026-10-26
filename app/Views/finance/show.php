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
                                            <button type="button" onclick="openFinanceModal('<?= esc($providerPayoutModalId) ?>', this)" class="button secondary">
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
<?php if($showFinanceForms && $showSettlementSummary && floatval($finance['balance_due']??0)>0): ?><button type="button" class="button primary" onclick="openFinanceModal('paymentModal',this)"><?= esc($ui['recordPayment']) ?></button><?php endif; ?>
<?php if($showFinanceForms && $isInvoice): ?><button type="button" class="button secondary" onclick="openFinanceModal('refundModal',this)"><?= esc($ui['recordRefund']) ?></button><?php endif; ?>
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
                                        <button type="button" onclick="voidPayment(<?= (int) $payment['id'] ?>)"  title="<?= esc(lang('Finance.voidPayment')) ?>">
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
    <div id="paymentModal" class="fr-modal hidden" data-finance-modal-root="paymentModal" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
        <div class="finance-inline-modal-backdrop" onclick="closeFinanceModal('paymentModal')"></div>
        <div  onclick="if (event.target === this) closeFinanceModal('paymentModal')">
            <div class="finance-inline-modal-panel" onclick="event.stopPropagation()">
                <div class="finance-inline-modal-header">
                    <div>
                        <span class="finance-inline-modal-kicker">

                            <span><?= esc($ui['paymentKicker']) ?></span>
                        </span>
                        <h4 id="paymentModalTitle" ><?= esc($ui['recordPayment']) ?></h4>
                        <p ><?= esc($ui['paymentNotice']) ?> <span ><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button"  onclick="closeFinanceModal('paymentModal')">
                        <?= esc(lang('Studio.close')) ?>
                    </button>
                </div>
                <form id="invoicePaymentForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label >

                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div >
                                <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.currency') ?></span>
                            </label>
                            <div >
                                <select name="currency" data-settlement-currency class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.currency')) ?>">
                                    <?php foreach (($currency_options ?? [$baseCurrency]) as $currencyOption): ?>
                                        <option value="<?= esc($currencyOption) ?>" <?= $currencyOption === $baseCurrency ? 'selected' : '' ?>><?= esc($currencyOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div >

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div >
                                <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                            </label>
                            <div >
                                <input type="text" data-settlement-base readonly
                                       class="finance-show-input"
                                       value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label >

                                <span><?= esc($ui['methodRequired']) ?></span>
                            </label>
                            <div >
                                <select name="payment_method" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
                                    <option value="cash"><?= lang('Finance.cash') ?></option>
                                    <option value="card"><?= lang('Finance.card') ?></option>
                                    <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                    <option value="check"><?= lang('Finance.check') ?></option>
                                    <option value="other"><?= lang('Finance.other') ?></option>
                                </select>
                                <div >

                                </div>
                            </div>
                        </div>
                        <?php if ($isExpense): ?>
                        <div class="hidden fr-field" id="settlementLiquidityAccountField">
                            <label >

                                <span><?= lang('Finance.paidFromAccount') ?></span>
                            </label>
                            <div >
                                <select id="settlementLiquidityAccount" name="liquidity_account" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                                        <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div >

                                </div>
                            </div>
                            <p ><?= lang('Finance.paidFromAccountHelp') ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div >
                                <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="fr-field">
                        <label >

                            <span><?= lang('Finance.notes') ?></span>
                        </label>
                        <div >
                            <textarea name="notes" rows="2"

                                      placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                    </div>
                    <div class="finance-inline-modal-actions">
                        <button type="button" onclick="closeFinanceModal('paymentModal')" >
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="button secondary">
                            <?= esc($ui['savePayment']) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($isInvoice): ?>
    <div id="refundModal" class="fr-modal hidden" data-finance-modal-root="refundModal" role="dialog" aria-modal="true" aria-labelledby="refundModalTitle">
        <div class="finance-inline-modal-backdrop" onclick="closeFinanceModal('refundModal')"></div>
        <div  onclick="if (event.target === this) closeFinanceModal('refundModal')">
            <div class="finance-inline-modal-panel" onclick="event.stopPropagation()">
                <div class="finance-inline-modal-header">
                    <div>
                        <span class="finance-inline-modal-kicker finance-inline-modal-kicker-danger">

                            <span><?= esc($ui['refundKicker']) ?></span>
                        </span>
                        <h4 id="refundModalTitle" ><?= esc($ui['recordRefund']) ?></h4>
                        <p ><?= esc($ui['refundNotice']) ?> <span ><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button"  onclick="closeFinanceModal('refundModal')">
                        <?= esc(lang('Studio.close')) ?>
                    </button>
                </div>
                <form id="invoiceRefundForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label >

                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div >
                                <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.currency') ?></span>
                            </label>
                            <div >
                                <select name="currency" data-settlement-currency class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.currency')) ?>">
                                    <?php foreach (($currency_options ?? [$baseCurrency]) as $currencyOption): ?>
                                        <option value="<?= esc($currencyOption) ?>" <?= $currencyOption === $baseCurrency ? 'selected' : '' ?>><?= esc($currencyOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div >

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div >
                                <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                            </label>
                            <div >
                                <input type="text" data-settlement-base readonly
                                       class="finance-show-input"
                                       value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label >

                                <span><?= esc($ui['methodRequired']) ?></span>
                            </label>
                            <div >
                                <select name="payment_method" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
                                    <option value="cash"><?= lang('Finance.cash') ?></option>
                                    <option value="card"><?= lang('Finance.card') ?></option>
                                    <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                    <option value="check"><?= lang('Finance.check') ?></option>
                                    <option value="other"><?= lang('Finance.other') ?></option>
                                </select>
                                <div >

                                </div>
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div >
                                <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="fr-field">
                        <label >

                            <span><?= esc($ui['refundNote']) ?></span>
                        </label>
                        <div >
                            <textarea name="notes" rows="2"

                                      placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                    </div>
                    <div class="finance-inline-modal-actions">
                        <button type="button" onclick="closeFinanceModal('refundModal')" >
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="button secondary">
                            <?= esc($ui['saveRefund']) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
        <div id="<?= esc($providerPayoutModalId) ?>" class="fr-modal hidden" data-finance-modal-root="<?= esc($providerPayoutModalId) ?>" role="dialog" aria-modal="true" aria-labelledby="<?= esc($providerPayoutModalId) ?>Title">
            <div class="finance-inline-modal-backdrop" onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')"></div>
            <div  onclick="if (event.target === this) closeFinanceModal('<?= esc($providerPayoutModalId) ?>')">
                <div class="finance-inline-modal-panel" onclick="event.stopPropagation()">
                    <div class="finance-inline-modal-header">
                        <div>
                            <span class="finance-inline-modal-kicker">

                                <span><?= esc($providerPayoutUi['kicker']) ?></span>
                            </span>
                            <h4 id="<?= esc($providerPayoutModalId) ?>Title" ><?= esc($providerPayoutUi['action']) ?></h4>
                            <p ><?= esc($providerPayoutUi['notice']) ?></p>
                        </div>
                        <button type="button"  onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')">
                            <?= esc(lang('Studio.close')) ?>
                        </button>
                    </div>
                    <form class="finance-inline-modal-form provider-payout-payment-form" data-url="<?= esc(base_url('finance/payouts/' . (int) $providerPayout['id'] . '/payments')) ?>" data-finance-modal-close="<?= esc($providerPayoutModalId) ?>">
                        <?= csrf_field() ?>
                        <div >
                            <div >
                                <div >
                                    <p ><?= esc($providerPayoutUi['kicker']) ?></p>
                                    <h5 ><?= esc($providerDisplayName) ?></h5>
                                    <p >
                                        <?= esc(lang('Finance.financeTransaction')) ?>:
                                        <span ><?= esc($finance['transaction_id'] ?? ('#' . ($finance['id'] ?? ''))) ?></span>
                                    </p>
                                </div>
                                <div class="fr-field-grid">
                                    <div >
                                        <p ><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                        <p  dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_share_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                    <div >
                                        <p ><?= esc($providerPayoutUi['paid']) ?></p>
                                        <p  dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_paid_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                    <div >
                                        <p ><?= esc($providerPayoutUi['left']) ?></p>
                                        <p  dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerBalanceDue, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                            <div class="fr-field">
                                <label >

                                    <span><?= esc($ui['amountRequired']) ?></span>
                                </label>
                                <input type="number" name="amount" step="0.01" min="0.01" max="<?= esc($providerPayoutAmountValue) ?>" value="<?= esc($providerPayoutAmountValue) ?>" class="finance-show-input" placeholder="0.00" required>
                            </div>
                            <div class="fr-field">
                                <label >

                                    <span><?= esc($ui['methodRequired']) ?></span>
                                </label>
                                <div >
                                    <select name="payment_method" class="provider-payout-method-input finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
                                        <option value="cash"><?= lang('Finance.cash') ?></option>
                                        <option value="card"><?= lang('Finance.card') ?></option>
                                        <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                        <option value="check"><?= lang('Finance.check') ?></option>
                                        <option value="other"><?= lang('Finance.other') ?></option>
                                    </select>
                                    <div >

                                    </div>
                                </div>
                            </div>
                            <div class="fr-field">
                                <label >

                                    <span><?= lang('Finance.dateRequired') ?></span>
                                </label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="finance-show-input">
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid">
                            <div class="provider-payout-liquidity-account-field hidden fr-field">
                                <label >

                                    <span><?= lang('Finance.paidFromAccount') ?></span>
                                </label>
                                <div >
                                    <select name="liquidity_account" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                        <option value="bank"><?= esc(($check_outgoing_liquidity_options ?? [])['bank'] ?? 'Bank') ?></option>
                                        <option value="undeposited_checks"><?= esc(($check_outgoing_liquidity_options ?? [])['undeposited_checks'] ?? 'Checks on Hand') ?></option>
                                    </select>
                                    <div >

                                    </div>
                                </div>
                                <p ><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= esc($providerPayoutUi['historyNotes']) ?></span>
                            </label>
                            <textarea name="notes" rows="2" class="finance-show-input" placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                        <div class="finance-inline-modal-actions">
                            <button type="button" onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')" >
                                <span><?= esc(lang('Finance.cancel')) ?></span>
                            </button>
                            <button type="submit" class="button secondary">
                                <?= esc($providerPayoutUi['save']) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

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

initializeClinicDateInputs();
</script>
<script>
const financeBaseCurrency = '<?= esc($baseCurrency) ?>';
let financeModalLastTrigger = null;

function mountFinanceModal(id) {
    const modal = document.getElementById(id);
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    return modal;
}

function getOpenFinanceModal() {
    return document.querySelector('[data-finance-modal-root]:not(.hidden)');
}

function getFinanceModalFocusable(modal) {
    if (!modal) {
        return [];
    }

    return Array.from(modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'))
        .filter((element) => !element.disabled && element.offsetParent !== null);
}

function openFinanceModal(id, trigger = null) {
    const modal = mountFinanceModal(id);
    if (!modal) {
        return;
    }
    financeModalLastTrigger = trigger || document.activeElement;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    const focusable = getFinanceModalFocusable(modal);
    const focusTarget = focusable.find((element) => ['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName)) || focusable[0];
    if (focusTarget) {
        window.setTimeout(() => focusTarget.focus(), 50);
    }
}

function closeFinanceModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
        return;
    }
    modal.classList.add('hidden');
    if (!document.querySelector('[data-finance-modal-root]:not(.hidden)')) {
        document.body.classList.remove('overflow-hidden');
    }
    if (financeModalLastTrigger && typeof financeModalLastTrigger.focus === 'function') {
        window.setTimeout(() => financeModalLastTrigger.focus(), 0);
    }
}

function closeOpenFinanceModals() {
    document.querySelectorAll('[data-finance-modal-root]').forEach((modal) => {
        if (!modal.classList.contains('hidden')) {
            closeFinanceModal(modal.id);
        }
    });
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeOpenFinanceModals();
        return;
    }

    if (event.key !== 'Tab') {
        return;
    }

    const modal = getOpenFinanceModal();
    if (!modal) {
        return;
    }

    const focusable = getFinanceModalFocusable(modal);
    if (!focusable.length) {
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

document.querySelectorAll('[data-finance-modal-root]').forEach((modal) => {
    modal.addEventListener('click', function (event) {
        if (event.target === modal || event.target.classList.contains('finance-inline-modal-backdrop')) {
            closeFinanceModal(modal.id);
        }
    });
});

function syncSettlementForm(form) {
    if (!form) {
        return;
    }

    const currencyInput = form.querySelector('[data-settlement-currency]');
    const rateInput = form.querySelector('[data-settlement-rate]');
    const amountInput = form.querySelector('input[name="amount_original"], input[name="amount"]');
    const baseOutput = form.querySelector('[data-settlement-base]');
    if (!amountInput || !baseOutput) {
        return;
    }

    const currency = currencyInput ? currencyInput.value : financeBaseCurrency;
    let rate = parseFloat(rateInput ? rateInput.value : '1');
    if (!Number.isFinite(rate) || rate <= 0) {
        rate = 1;
    }

    if (currency === financeBaseCurrency && rateInput) {
        rate = 1;
        rateInput.value = '1';
        rateInput.readOnly = true;
        rateInput.classList.add('bg-slate-50');
    } else if (rateInput) {
        rateInput.readOnly = false;
        rateInput.classList.remove('bg-slate-50');
    }

    const amount = parseFloat(amountInput.value || '0');
    const baseAmount = Number.isFinite(amount) ? (amount * rate) : 0;
    baseOutput.value = baseAmount.toFixed(2);
}

function initSettlementForm(form) {
    if (!form) {
        return;
    }

    const watchedInputs = form.querySelectorAll('input[name="amount_original"], input[name="amount"], [data-settlement-currency], [data-settlement-rate]');
    watchedInputs.forEach((input) => {
        input.addEventListener('input', function () { syncSettlementForm(form); });
        input.addEventListener('change', function () { syncSettlementForm(form); });
    });

    syncSettlementForm(form);
}

function markAsPaid(transactionId) {
    if (confirm('<?= lang('Finance.confirmMarkTransactionPaid') ?>')) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('finance') ?>/${transactionId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= lang('Finance.errorUpdatingTransaction') ?>');
        });
    }
}

function deleteFinance(id) {
    if (confirm('<?= lang('Finance.confirmDeleteTransaction') ?>')) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('finance') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                window.location.href = '<?= base_url('finance') ?>';
            } else {
                alert('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= lang('Finance.errorDeletingTransaction') ?>');
        });
    }
}

function closeBalance(transactionId) {
    if (!confirm('<?= lang('Finance.confirmCloseBalance') ?>')) {
        return;
    }

    const reason = prompt('<?= lang('Finance.reasonOptionalPrompt') ?>') || '';
    const formData = new FormData();
    formData.append('reason', reason);

    const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
    const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

    fetch(`<?= base_url('finance') ?>/${transactionId}/close-balance`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (data.success) {
            location.reload();
        } else {
            alert('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?= lang('Finance.errorClosingBalance') ?>');
    });
}

const invoicePaymentForm = document.getElementById('invoicePaymentForm');
if (invoicePaymentForm) {
    initSettlementForm(invoicePaymentForm);
    invoicePaymentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoicePaymentForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '<?= esc(lang('Finance.processing')) ?>';

        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                closeFinanceModal('paymentModal');
                location.reload();
            } else {
                alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Finance.errorAddingPayment')) ?>');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
}

const invoiceRefundForm = document.getElementById('invoiceRefundForm');
if (invoiceRefundForm) {
    initSettlementForm(invoiceRefundForm);
    invoiceRefundForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoiceRefundForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '<?= esc(lang('Finance.processing')) ?>';

        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/refund`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                closeFinanceModal('refundModal');
                location.reload();
            } else {
                alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Finance.errorRecordingRefund')) ?>');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
}

function voidPayment(paymentId) {
    if (!confirm('<?= esc(lang('Finance.confirmVoidPayment')) ?>')) {
        return;
    }
    const reason = prompt('<?= esc(lang('Finance.voidReasonPrompt')) ?>') || '';
    const formData = new FormData();
    formData.append('notes', reason);

    const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
    const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

    fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/${paymentId}/void`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (data.success) {
            location.reload();
        } else {
            alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?= esc(lang('Finance.errorVoidingPayment')) ?>');
    });
}

function syncExpenseSettlementLiquidityField() {
    const settlementMethod = document.querySelector('#invoicePaymentForm select[name="payment_method"]');
    const settlementField = document.getElementById('settlementLiquidityAccountField');
    const settlementInput = document.getElementById('settlementLiquidityAccount');
    if (!settlementField || !settlementMethod) {
        return;
    }

    settlementField.classList.remove('hidden');
    if (settlementInput) {
        settlementInput.disabled = false;
        if (!settlementInput.value) {
            const defaultAccount = settlementMethod.value === 'cash' ? 'safe_cash' : 'bank';
            settlementInput.value = defaultAccount;
            if (window.jQuery) {
                window.jQuery(settlementInput).val(defaultAccount).trigger('change');
            }
        }
    }
}

document.querySelector('#invoicePaymentForm select[name="payment_method"]')?.addEventListener('change', syncExpenseSettlementLiquidityField);
syncExpenseSettlementLiquidityField();

document.querySelectorAll('.provider-payout-payment-form').forEach((form) => {
    const methodInput = form.querySelector('.provider-payout-method-input');
    const liquidityField = form.querySelector('.provider-payout-liquidity-account-field');
    const liquidityInput = form.querySelector('select[name="liquidity_account"]');
    const modalToClose = form.dataset.financeModalClose;

    const syncProviderPayoutLiquidityField = () => {
        if (!methodInput || !liquidityField) {
            return;
        }
        const isCheck = methodInput.value === 'check';
        liquidityField.classList.toggle('hidden', !isCheck);
        if (liquidityInput) {
            liquidityInput.disabled = !isCheck;
            if (!isCheck) {
                liquidityInput.value = 'bank';
                if (window.jQuery) {
                    window.jQuery(liquidityInput).val('bank').trigger('change');
                }
            }
        }
    };

    methodInput?.addEventListener('change', syncProviderPayoutLiquidityField);
    syncProviderPayoutLiquidityField();

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(form);
        if (methodInput?.value !== 'check') {
            formData.delete('liquidity_account');
        }
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        const url = form.dataset.url;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                if (modalToClose) {
                    closeFinanceModal(modalToClose);
                }
                location.reload();
            } else {
                alert('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Finance.errorRecordingProviderPayout')) ?>');
        });
    });
});
</script>
<script src="<?= base_url('assets/js/studio-finance-record.js') ?>?v=<?= esc(asset_version('assets/js/studio-finance-record.js')) ?>" defer></script>
<?= $this->endSection() ?>
