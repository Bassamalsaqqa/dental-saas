<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<p class="px-4 py-2 text-sm text-amber-800"><?= esc(lang('Finance.providerCurrencyWarning')) ?></p>
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

<div class="finance-show-shell px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">
        <!-- Hero Header (clone of /patient/149 pattern) -->
        <section class="v3-record-context patient-hero-card-neutral overflow-hidden border mb-4">

            <div class="v3-record-context-body">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between">
                    <div class="flex flex-col gap-5">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em]">
                            <span><?= $isRtl ? 'المالية' : 'Finance' ?></span>
                            <span class="h-1 w-1"></span>
                            <span><?= esc($transactionTypeLabel) ?></span>
                        </div>
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                            <div class="v3-context-avatar patient-avatar-badge-neutral">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h1 class="v3-title font-extrabold tracking-tight v3-title"><?= esc('#' . $finance['transaction_id']) ?></h1>
                                        <span class="patient-hero-status inline-flex items-center border px-3 py-1 text-xs font-semibold" style="background:<?= esc($statusTone['hero_bg'] ?? 'rgba(255,255,255,0.12)') ?>;border-color:<?= esc($statusTone['hero_border'] ?? 'rgba(255,255,255,0.2)') ?>;color:#eff6ff;">
                                            <?= esc($paymentStatusLabel) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="v3-context-facts">
                                    <div class="patient-hero-chip" title="<?= esc($ui['type'] . ': ' . $transactionTypeLabel) ?>">
                                        <span class="patient-hero-chip-icon"><i class="fas fa-tag"></i></span>
                                        <span class="patient-hero-chip-text">
                                            <span class="patient-hero-chip-label"><?= esc($ui['type']) ?></span>
                                            <span class="patient-hero-chip-value"><?= esc($transactionTypeLabel) ?></span>
                                        </span>
                                    </div>
                                    <div class="patient-hero-chip" title="<?= esc($ui['date'] . ': ' . (!empty($finance['transaction_date']) ? formatDate($finance['transaction_date']) : lang('Finance.unknown'))) ?>">
                                        <span class="patient-hero-chip-icon"><i class="fas fa-calendar"></i></span>
                                        <span class="patient-hero-chip-text">
                                            <span class="patient-hero-chip-label"><?= esc($ui['date']) ?></span>
                                            <span class="patient-hero-chip-value"><?= !empty($finance['transaction_date']) ? formatDate($finance['transaction_date']) : lang('Finance.unknown') ?></span>
                                        </span>
                                    </div>
                                    <div class="patient-hero-chip" title="<?= esc($ui['method'] . ': ' . lang('Finance.' . $finance['payment_method'])) ?>">
                                        <span class="patient-hero-chip-icon"><i class="fas fa-credit-card"></i></span>
                                        <span class="patient-hero-chip-text">
                                            <span class="patient-hero-chip-label"><?= esc($ui['method']) ?></span>
                                            <span class="patient-hero-chip-value"><?= esc(lang('Finance.' . $finance['payment_method'])) ?></span>
                                        </span>
                                    </div>
                                    <div class="patient-hero-chip" title="<?= esc(lang('Finance.currency') . ': ' . $baseCurrency) ?>">
                                        <span class="patient-hero-chip-icon"><i class="fas fa-coins"></i></span>
                                        <span class="patient-hero-chip-text">
                                            <span class="patient-hero-chip-label"><?= lang('Finance.currency') ?></span>
                                            <span class="patient-hero-chip-value"><?= esc($baseCurrency) ?></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="v3-record-context-actions">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <a href="<?= esc(workflow_back('finance', $return_to ?? null)) ?>" class="v3-context-action patient-header-action-soft">
                                <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                                <span><?= esc($ui['back']) ?></span>
                            </a>
                            <a href="<?= workflow_context_url('finance/' . $finance['id'] . '/edit', $return_to ?? null) ?>" class="v3-context-action patient-header-action-soft">
                                <i class="fas fa-pen"></i>
                                <span><?= esc($ui['edit']) ?></span>
                            </a>
                        </div>
                        <?php
                        $showPayment = $showFinanceForms && $showSettlementSummary && floatval($finance['balance_due'] ?? 0) > 0;
                        $showRefund = $showFinanceForms && $isInvoice;
                        if ($showPayment || $showRefund):
                            $finCols = ($showPayment && $showRefund) ? 'sm:grid-cols-2' : 'sm:grid-cols-1';
                        ?>
                        <div class="grid gap-2 <?= $finCols ?>">
                            <?php if ($showPayment): ?>
                            <button type="button" onclick="openFinanceModal('paymentModal', this)" class="v3-context-action patient-header-action-emerald">
                                <i class="fas fa-plus-circle"></i>
                                <span><?= esc($ui['recordPayment']) ?></span>
                            </button>
                            <?php endif; ?>
                            <?php if ($showRefund): ?>
                            <button type="button" onclick="openFinanceModal('refundModal', this)" class="v3-context-action patient-header-action-danger">
                                <i class="fas fa-rotate-left"></i>
                                <span><?= esc($ui['recordRefund']) ?></span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($isInvoice && config('InvoiceCorrections')->enabled === true): ?>
                        <div class="grid gap-2 sm:grid-cols-1">
                            <a href="<?= esc(site_url('finance/' . (int) $finance['id'] . '/corrections'), 'attr') ?>" class="patient-hero-tool-button">
                                <i class="fas fa-file-invoice" aria-hidden="true"></i><span><?= esc(lang('InvoiceCorrections.title')) ?></span>
                            </a>
                        </div>
                        <?php endif; ?>
                        <div class="grid gap-2 sm:grid-cols-1">
                            <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="patient-hero-tool-button">
                                <i class="fas fa-file-pdf"></i>
                                <span><?= esc($ui['exportPdf']) ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="finance-show-card finance-show-summary-card mb-8">
            <div class="finance-show-summary-grid">
                <div class="finance-show-summary-stat v3-finance-position">
                    <p class="finance-show-summary-label"><?= lang('Finance.totalAmount') ?></p>
                    <p class="finance-show-summary-value"><?= number_format($totalAmount, 2) ?> <?= esc($baseCurrency) ?></p>
                </div>
                <div class="finance-show-summary-stat v3-finance-position">
                    <p class="finance-show-summary-label"><?= lang('Finance.paidToDate') ?></p>
                    <p class="finance-show-summary-value"><?= number_format($paidSoFar, 2) ?> <?= esc($baseCurrency) ?></p>
                </div>
                <div class="finance-show-summary-stat v3-finance-position">
                    <p class="finance-show-summary-label"><?= lang('Finance.balanceDue') ?></p>
                    <p class="finance-show-summary-value"><?= number_format($balanceDue, 2) ?> <?= esc($baseCurrency) ?></p>
                </div>
            </div>
            <?php if ($showSettlementSummary): ?>
                <div class="finance-show-progress">
                    <span style="width: <?= esc((string) $settlementProgress) ?>%;"></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="finance-show-page-grid">
            <div class="finance-show-main">

                <!-- Financial Details & Breakdown -->
                <div class="finance-show-card finance-detail-card finance-show-primary-block overflow-hidden">
                    <div class="v3-finance-detail-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg">
                                <i class="fas fa-list-ul"></i>
                            </div>
                            <h2 class="text-xl font-bold"><?= esc($ui['details']) ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium">
                            <?= esc($isExpense ? $expenseCategoryLabel : $serviceTypeLabel) ?>
                        </span>
                    </div>
                    <div class="v3-finance-detail-body">
                        <div class="">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="v3-finance-detail-field">
                                    <label class="finance-show-field-label"><?= $isExpense ? lang('Finance.expenseDescription') : lang('Finance.serviceDescription') ?></label>
                                    <div class="leading-relaxed">
                                        <?= !empty($finance['description']) ? nl2br(htmlspecialchars($finance['description'])) : '<span class="italic">' . esc(lang('Finance.noDescriptionProvided')) . '</span>' ?>
                                    </div>
                                </div>
                                <div class="v3-finance-detail-field">
                                    <label class="finance-show-field-label"><?= lang('Finance.createdByLabel') ?></label>
                                    <div class="leading-relaxed font-semibold">
                                        <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?>
                                    </div>
                                </div>
                                <?php if ($isExpense && !empty($finance['counterparty_name'])): ?>
                                <div class="v3-finance-detail-field">
                                    <label class="finance-show-field-label"><?= lang('Finance.payeeVendor') ?></label>
                                    <div class="leading-relaxed font-semibold">
                                        <?php if (!empty($supplier['id'])): ?>
                                            <a href="<?= base_url('inventory/suppliers/' . (int) $supplier['id']) ?>" class="inline-flex items-center gap-2 hover:underline">
                                                <i class="fas fa-truck-field"></i>
                                                <span><?= esc($finance['counterparty_name']) ?></span>
                                            </a>
                                        <?php else: ?>
                                            <?= esc($finance['counterparty_name']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($finance['service_details'])): ?>
                                <div class="v3-finance-detail-field">
                                    <label class="finance-show-field-label"><?= lang('Finance.technicalDetails') ?></label>
                                    <div class="leading-relaxed">
                                        <?= nl2br(htmlspecialchars($finance['service_details'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="v3-finance-breakdown">
                                <div class="divide-y divide-slate-100 text-sm">
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium"><?= lang('Finance.subtotal') ?></span>
                                        <span class="font-semibold"><?= number_format($finance['amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php if ($isConverted): ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium"><?= lang('Finance.original') ?></span>
                                        <span class="font-semibold text-end">
                                            <?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?>
                                            @ <?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['discount_amount'] > 0): ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium"><?= lang('Finance.discount') ?></span>
                                        <span class="font-semibold text-rose-600">-<?= number_format($finance['discount_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['tax_amount'] > 0): ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium"><?= lang('Finance.taxPercent') ?> (<?= number_format($taxPercent, 2) ?>%)</span>
                                        <span class="font-semibold">+<?= number_format($finance['tax_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-5">
                                        <span class="text-base font-bold"><?= lang('Finance.total') ?></span>
                                        <span class="v3-section-title font-bold"><?= number_format($totalAmount, 2) ?> <span class="text-base font-medium"><?= esc($baseCurrency) ?></span></span>
                                    </div>
                                    <?php if ($showSettlementSummary): ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium text-emerald-700"><?= lang('Finance.paidToDate') ?></span>
                                        <span class="font-semibold text-emerald-700"><?= number_format($paidSoFar, 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium text-rose-700"><?= lang('Finance.balanceDue') ?></span>
                                        <span class="font-semibold text-rose-700"><?= number_format($balanceDue, 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($billedClinicalChargeLines)): ?>
                <div class="finance-show-card finance-clinical-card finance-show-primary-block overflow-hidden">
                    <div class="v3-finance-detail-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <i class="fas fa-tooth"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold"><?= esc(lang('Finance.clinicalWorkBilled')) ?></h2>
                                <p class="mt-1 text-sm"><?= esc(lang('Finance.clinicalWorkBilledHelp')) ?></p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 text-sm font-bold bg-emerald-100 text-emerald-700" dir="ltr">
                            <?= esc(formatCurrency($billedClinicalTotal)) ?>
                        </span>
                    </div>
                    <div class="finance-show-card-body space-y-5">
                        <div class="finance-clinical-snapshot border border-emerald-100 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700"><?= esc(lang('Finance.clinicalTraceSnapshot')) ?></p>
                                    <p class="mt-1 text-sm"><?= esc(lang('Finance.clinicalTraceSnapshotHelp')) ?></p>
                                </div>
                                <?php if (!empty($patient['id'])): ?>
                                    <a href="<?= esc(workflow_context_url('patient/' . (int) $patient['id'], $return_to ?? null)) ?>" class="finance-clinical-profile-action">
                                        <i class="fas <?= $isRtl ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i>
                                        <span><?= esc(lang('Finance.openPatientProfile')) ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                                <div class="finance-trace-stat border p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc(lang('Finance.clinicalTracePatient')) ?></p>
                                    <p class="mt-1 truncate text-sm font-bold">
                                        <?= esc($relatedPartyValue !== '' ? $relatedPartyValue : lang('Finance.unknown')) ?>
                                    </p>
                                    <p class="mt-1 truncate text-xs"><?= esc($relatedPartyMeta) ?></p>
                                </div>
                                <div class="finance-trace-stat border p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc(lang('Finance.clinicalTraceLines')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold text-emerald-700"><?= esc((string) count($billedClinicalChargeLines)) ?></p>
                                </div>
                                <div class="finance-trace-stat border p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc(lang('Finance.clinicalTraceTreatments')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold"><?= esc((string) $billedClinicalTreatmentCount) ?></p>
                                </div>
                                <div class="finance-trace-stat border p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc(lang('Finance.clinicalTraceExaminations')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold"><?= esc((string) $billedClinicalExaminationCount) ?></p>
                                </div>
                                <div class="finance-trace-stat border p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc(lang('Finance.clinicalTraceProviders')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold"><?= esc((string) $billedClinicalProviderCount) ?></p>
                                </div>
                                <div class="finance-trace-stat border p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc(lang('UI.moneyTrailInventoryCost')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold <?= $billedInventoryUsageTotal > 0.009 ? 'text-rose-700' : 'text-slate-500' ?>" dir="ltr"><?= esc(formatCurrency($billedInventoryUsageTotal)) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="finance-table-panel overflow-hidden border">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead class="text-xs font-bold uppercase tracking-wide">
                                        <tr>
                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc(lang('Finance.procedure')) ?></th>
                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc(lang('Finance.linkedTreatment')) ?></th>
                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc(lang('Finance.linkedExamination')) ?></th>
                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc(lang('Finance.provider')) ?></th>
                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc(lang('Finance.serviceDate')) ?></th>
                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc(lang('UI.moneyTrailInventoryCost')) ?></th>
                                            <th class="px-4 py-3 text-end"><?= esc(lang('Finance.amount')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
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
                                            <tr class="align-top">
                                                <td class="px-4 py-3">
                                                    <div class="font-semibold"><?= esc($line['procedure_name'] ?? lang('Finance.clinicalChargeLine')) ?></div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                                                        <span class="inline-flex items-center px-2 py-0.5">
                                                            <?= esc(lang('Finance.clinicalChargeLine')) ?> #<?= esc((string) ($line['id'] ?? '')) ?>
                                                        </span>
                                                        <?php if ($sourceType !== ''): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5">
                                                                <?= esc(lang('Finance.clinicalSource')) ?>: <?= esc($sourceType) ?><?= $sourceId > 0 ? ' #' . esc((string) $sourceId) : '' ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($line['tooth_numbers'])): ?>
                                                        <div class="mt-1 text-xs"><?= esc(lang('Finance.tooth')) ?>: <span dir="ltr"><?= esc($line['tooth_numbers']) ?></span></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <?php if ($treatmentId > 0): ?>
                                                        <a href="<?= esc(workflow_context_url('treatment/' . $treatmentId, $return_to ?? null)) ?>" class="font-semibold text-emerald-700 hover:underline">
                                                            <?= esc($treatmentLabel !== '' ? $treatmentLabel : ('#' . $treatmentId)) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class=""><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <?php if ($examinationId > 0): ?>
                                                        <a href="<?= esc(workflow_context_url('examination/' . $examinationId, $return_to ?? null)) ?>" class="font-semibold hover:underline">
                                                            <?= esc($examinationLabel !== '' ? $examinationLabel : ('#' . $examinationId)) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class=""><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3"><?= esc($providerDisplay) ?></td>
                                                <td class="px-4 py-3"><?= !empty($line['service_date']) ? esc(formatDate($line['service_date'])) : esc(lang('Finance.notAvailable')) ?></td>
                                                <td class="px-4 py-3">
                                                    <?php if ($lineInventoryCost > 0.009): ?>
                                                        <div class="font-bold text-rose-700" dir="ltr"><?= esc(formatCurrency($lineInventoryCost)) ?></div>
                                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                                            <?php foreach (array_slice($lineInventoryRows, 0, 2) as $usageRow): ?>
                                                                <?php $usageFinanceId = (int) ($usageRow['finance_entry_id'] ?? 0); ?>
                                                                <?php if ($usageFinanceId > 0): ?>
                                                                    <a href="<?= esc(workflow_context_url('finance/' . $usageFinanceId, $return_to ?? null)) ?>" class="inline-flex items-center gap-1 border border-rose-100 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 hover:bg-rose-100">
                                                                        <i class="fas fa-boxes-stacked"></i>
                                                                        <span><?= esc($summarizeInventoryUsage($usageRow)) ?></span>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="inline-flex items-center gap-1 border border-rose-100 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">
                                                                        <i class="fas fa-boxes-stacked"></i>
                                                                        <span><?= esc($summarizeInventoryUsage($usageRow)) ?></span>
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class=""><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 text-end font-bold text-emerald-700" dir="ltr"><?= esc(formatCurrency($line['net_production_amount'] ?? 0)) ?></td>
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
                <div class="finance-show-card finance-provider-card finance-show-primary-block overflow-hidden">
                    <div class="v3-finance-detail-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h2 class="text-xl font-bold"><?= esc($providerPayoutUi['sectionTitle']) ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?= (bool) array_filter($provider_payout_summary['currency_buckets'] ?? [], static fn(array $b): bool => ($b['provider_balance_due'] ?? 0) > 0) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                            <?= (bool) array_filter($provider_payout_summary['currency_buckets'] ?? [], static fn(array $b): bool => ($b['provider_balance_due'] ?? 0) > 0) ? esc($providerPayoutUi['sectionStatusOpen']) : esc($providerPayoutUi['sectionStatusClear']) ?>
                        </span>
                    </div>
                    <div class="finance-show-card-body space-y-5">
                        <p class="text-sm"><?= esc(lang('Finance.providerVoidBalanceHelp')) ?></p>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                            <div class="finance-provider-stat border p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold"><?= esc($providerPayoutUi['collected']) ?></p>
                                <p class="mt-1 font-bold"><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'gross_amount')) ?></p>
                            </div>
                            <div class="finance-provider-stat border p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold"><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                <p class="mt-1 font-bold"><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'provider_share_amount')) ?></p>
                            </div>
                            <div class="finance-provider-stat border p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold"><?= esc($providerPayoutUi['paid']) ?></p>
                                <p class="mt-1 font-bold text-emerald-700"><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'provider_paid_amount')) ?></p>
                            </div>
                            <div class="finance-provider-stat border p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold"><?= esc($providerPayoutUi['left']) ?></p>
                                <p class="mt-1 font-bold text-rose-700"><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'provider_balance_due')) ?></p>
                            </div>
                            <div class="finance-provider-stat border p-4">
                                <p class="text-xs uppercase tracking-wide font-semibold"><?= esc($providerPayoutUi['clinicKeeps']) ?></p>
                                <p class="mt-1 font-bold text-sky-700"><?= esc(\App\Services\ProviderCurrencySummary::format($provider_payout_summary, 'clinic_share_amount')) ?></p>
                            </div>
                        </div>

                        <div class="space-y-4">
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
                                <div class="finance-provider-payout-card border space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-base font-bold"><?= esc($providerDisplayName) ?></h3>
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold">
                                                    <?= esc($providerTypeLabel) ?>
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold <?= $providerStatusClass ?>">
                                                    <?= esc($providerStatusLabel) ?>
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs">
                                                <?= esc($providerPayoutUi['rule']) ?>:
                                                <?= esc(lang('Finance.' . ($providerPayout['split_mode'] ?? 'percentage'))) ?>
                                                <?= esc($providerPayout['split_value'] ?? 0) ?><?= ($providerPayout['split_mode'] ?? '') === 'percent' ? '%' : '' ?>
                                            </p>
                                            <?php if (!empty($providerPayout['notes'])): ?>
                                                <p class="mt-2 text-sm"><?= esc($providerPayout['notes']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($providerTypeKey === 'user' && $providerUserId > 0): ?>
                                                <a href="<?= esc(workflow_context_url('users/' . $providerUserId, $return_to ?? null)) ?>" class="mt-2 inline-flex items-center gap-1 text-xs font-bold">
                                                    <i class="fas fa-user-doctor"></i>
                                                    <span><?= esc(lang('Finance.openProviderProfile')) ?></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:min-w-[28rem]">
                                            <div class="finance-provider-mini-stat border p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                                <p class="mt-1 text-sm font-bold"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_share_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                            <div class="finance-provider-mini-stat border p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc($providerPayoutUi['paid']) ?></p>
                                                <p class="mt-1 text-sm font-bold text-emerald-700"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_paid_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                            <div class="finance-provider-mini-stat border p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide"><?= esc($providerPayoutUi['left']) ?></p>
                                                <p class="mt-1 text-sm font-bold text-rose-700"><?= esc(\App\Services\ProviderCurrencySummary::native(($providerPayout['status'] ?? '') === 'void' ? 0 : ($providerPayout['provider_balance_due'] ?? 0), $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="finance-provider-history-panel border overflow-hidden">
                                        <div class="flex items-center justify-between gap-3 px-4 py-3">
                                            <div>
                                                <h4 class="text-sm font-semibold"><?= esc($providerPayoutUi['history']) ?></h4>
                                                <p class="mt-1 text-xs"><?= esc($providerPayoutUi['historyHelp']) ?></p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold">
                                                <?= count($providerPayoutPayments) ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($providerPayoutPayments)): ?>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full text-sm">
                                                    <thead class="">
                                                        <tr class="text-xs font-semibold uppercase tracking-wide">
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyDate']) ?></th>
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyMethod']) ?></th>
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyAmount']) ?></th>
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyNotes']) ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100">
                                                        <?php foreach ($providerPayoutPayments as $paymentRow): ?>
                                                            <tr class="">
                                                                <td class="px-4 py-3"><?= !empty($paymentRow['paid_date']) ? esc(formatDate($paymentRow['paid_date'])) : '-' ?></td>
                                                                <td class="px-4 py-3"><?= esc(lang('Finance.' . ($paymentRow['payment_method'] ?? 'cash'))) ?></td>
                                                                <td class="px-4 py-3 font-semibold"><?= esc(\App\Services\ProviderCurrencySummary::native($paymentRow['amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></td>
                                                                <td class="px-4 py-3"><?= !empty($paymentRow['notes']) ? esc($paymentRow['notes']) : '-' ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="px-4 py-4 text-sm"><?= esc($providerPayoutUi['historyEmpty']) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (($providerPayout['status'] ?? '') !== 'void' && floatval($providerPayout['provider_balance_due'] ?? 0) > 0 && has_permission('finance', 'edit')): ?>
                                        <?php $providerPayoutModalId = 'providerPayoutModal' . (int) $providerPayout['id']; ?>
                                        <div class="finance-provider-cta flex flex-col gap-3 border border-emerald-200 bg-emerald-50/80 p-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="<?= $isRtl ? 'text-right' : 'text-left' ?>">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= esc($providerPayoutUi['balanceReady']) ?></p>
                                                <p class="mt-1 text-sm font-bold"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_balance_due'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                            </div>
                                            <button type="button" onclick="openFinanceModal('<?= esc($providerPayoutModalId) ?>', this)" class="finance-show-btn finance-show-btn-success finance-show-btn-modal">
                                                <?php if ($isRtl): ?>
                                                    <span><?= esc($providerPayoutUi['action']) ?></span><i class="fas fa-plus-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-plus-circle"></i><span><?= esc($providerPayoutUi['action']) ?></span>
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
                <div class="finance-show-card finance-notes-card finance-show-primary-block">
                    <h2 class="mb-4 flex items-center gap-3 text-xl font-bold">
                        <i class="fas fa-sticky-note text-yellow-600"></i><?= lang('Finance.administrativeNotes') ?>
                    </h2>
                    <div class="finance-notes-body leading-relaxed bg-yellow-50 p-4 border border-yellow-100">
                        <?= nl2br(htmlspecialchars($finance['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <aside class="finance-show-sidebar">
                <div class="finance-show-sidebar-stack">
                    <?php if ($patient): ?>
                    <div class="finance-show-sidebar-card finance-sidebar-inner-card finance-show-context-card">
                        <h2 class="mb-4 flex items-center gap-2 text-xl font-bold">
                            <i class="fas fa-user"></i>
                            <?= esc($ui['patient']) ?>
                        </h2>
                        <div class="finance-show-context-head">
                            <div class="finance-show-context-avatar">
                                <?= esc(ui_initials($patient['first_name'] ?? '', $patient['last_name'] ?? '', 'P')) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-bold">
                                    <?= esc(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?>
                                </div>
                                <div class="mt-1 text-sm"><?= lang('Finance.idLabel') ?>: <?= esc($patient['patient_id']) ?></div>
                            </div>
                        </div>
                        <div class="finance-show-context-actions">
                            <a href="<?= workflow_context_url('patient/' . $patient['id'], $return_to ?? null) ?>" class="finance-context-patient-action">
                                <i class="fas <?= $isRtl ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i>
                                <span><?= esc(lang('Finance.openPatientProfile')) ?></span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($check_attachment) || !empty($finance_attachment)): ?>
                    <div class="finance-show-sidebar-card finance-sidebar-inner-card finance-sidebar-inner-card--attachments text-sm">
                        <h2 class="mb-4 flex items-center gap-2 text-xl font-bold">
                            <i class="fas fa-paperclip"></i>
                            <?= $isRtl ? 'المرفقات' : 'Attachments' ?>
                        </h2>
                        <dl class="finance-show-meta-list">
                            <?php if (!empty($check_attachment)): ?>
                            <div class="finance-attachment-item space-y-2 border p-4">
                                <dt class="block text-sm font-medium"><?= lang('Finance.checkScanLabel') ?></dt>
                                <div class="finance-show-attachment-actions">
                                    <a class="inline-flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-lg transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-download"></i><?= lang('Finance.download') ?>
                                    </a>
                                    <a class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-eye"></i><?= lang('Finance.view') ?>
                                    </a>
                                </div>
                                <?php $isImage = !empty($check_attachment['mime_type']) && strpos($check_attachment['mime_type'], 'image/') === 0; ?>
                                <?php if ($isImage): ?>
                                    <div class="mt-3 border p-2">
                                        <img src="<?= base_url('file/download/' . $check_attachment['id']) ?>" alt="<?= esc(lang('Finance.checkScanPreview')) ?>" class="max-h-56 w-auto rounded-lg">
                                    </div>
                                <?php else: ?>
                                    <p class="text-xs"><?= lang('Finance.imagePreviewOnly') ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($finance_attachment)): ?>
                            <div class="finance-attachment-item space-y-2 border p-4">
                                <dt class="block text-sm font-medium"><?= lang('Finance.attachmentLabel') ?></dt>
                                <div class="finance-show-attachment-actions">
                                    <a class="inline-flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-lg transition-colors" href="<?= base_url('file/download/' . $finance_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-download"></i><?= lang('Finance.download') ?>
                                    </a>
                                    <a class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg transition-colors" href="<?= base_url('file/download/' . $finance_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-eye"></i><?= lang('Finance.view') ?>
                                    </a>
                                    <span class="text-xs"><?= esc($finance_attachment['original_name'] ?? '') ?></span>
                                </div>
                                <?php $isFinanceAttachmentImage = !empty($finance_attachment['mime_type']) && strpos($finance_attachment['mime_type'], 'image/') === 0; ?>
                                <?php if ($isFinanceAttachmentImage): ?>
                                    <div class="mt-3 border p-2">
                                        <img src="<?= base_url('file/download/' . $finance_attachment['id']) ?>" alt="<?= esc(lang('Finance.attachmentPreview')) ?>" class="max-h-56 w-auto rounded-lg">
                                    </div>
                                <?php else: ?>
                                    <p class="text-xs"><?= lang('Finance.imagePreviewOnly') ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <?php endif; ?>

                    <?php if ($examination): ?>
                    <div class="finance-show-sidebar-card finance-sidebar-inner-card finance-sidebar-inner-card--clinical">
                        <h2 class="mb-4 flex items-center gap-2 text-xl font-bold">
                            <i class="fas fa-stethoscope text-emerald-600"></i>
                            <?= lang('Finance.clinicalLink') ?>
                        </h2>
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center bg-emerald-100 text-emerald-600">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold">
                                    <?= formatDate($examination['examination_date']) ?>
                                </div>
                                <div class="text-xs">
                                    <?= lang('Finance.status') ?>: <?= ucfirst($examination['status']) ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?= workflow_context_url('examination/' . $examination['id'], $return_to ?? null) ?>" class="finance-show-btn finance-show-btn-soft w-full">
                            <?= lang('Finance.viewClinicalRecord') ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>

        <?php if ($isInvoice || $isExpense): ?>
        <div class="finance-show-card finance-history-card finance-show-full-span mt-6 overflow-hidden">
            <div class="v3-finance-detail-header">
                <div class="flex items-center gap-3">
                    <span class="h-2.5 w-2.5 bg-green-500"></span>
                    <div>
                        <h3 class="text-lg font-semibold"><?= esc($ui['history']) ?></h3>
                        <p class="text-xs"><?= esc($ui['historyHelp']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold border">
                        <?= count($payments ?? []) ?> <?= lang('Finance.settlement') ?>
                    </span>
                    <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-file-pdf text-red-600"></i>
                        <?= esc($ui['exportPdf']) ?>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="dataTables-wrapper">
                <table class="finance-show-table min-w-full">
                    <thead class="">
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-green-600"></i>
                                    <span><?= lang('Finance.date') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-dot text-green-600"></i>
                                    <span><?= esc($ui['type']) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-credit-card text-green-600"></i>
                                    <span><?= lang('Finance.method') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-doctor text-green-600"></i>
                                    <span><?= lang('Finance.recordedBy') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-coins text-green-600"></i>
                                    <span><?= esc($ui['enteredAmount']) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-end text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center justify-end gap-2">
                                    <i class="fas fa-wallet text-green-600"></i>
                                    <span><?= esc($ui['baseAmount']) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-note-sticky text-green-600"></i>
                                    <span><?= lang('Finance.notes') ?></span>
                                </div>
                            </th>
                            <?php if (has_permission('finance', 'edit')): ?>
                            <th class="px-6 py-4 text-end text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center justify-end gap-2">
                                    <i class="fas fa-cogs text-green-600"></i>
                                    <span><?= lang('Finance.actions') ?></span>
                                </div>
                            </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
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
                            <tr class="transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?= formatDate($payment['payment_date']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium <?= $typeClass ?>">
                                        <?= lang('Finance.' . $type) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?= lang('Finance.' . $payment['payment_method']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php $paymentCreator = trim(($payment['creator_first_name'] ?? '') . ' ' . ($payment['creator_last_name'] ?? '')); ?>
                                    <?= $paymentCreator !== '' ? 'Dr. ' . esc($paymentCreator) : lang('Finance.unknown') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php
                                        $paymentCurrency = $payment['currency'] ?? $baseCurrency;
                                        $paymentExchangeRate = floatval($payment['exchange_rate'] ?? 1);
                                        $paymentOriginalAmount = isset($payment['amount_original']) ? floatval($payment['amount_original']) : floatval($payment['amount']);
                                    ?>
                                    <div class="flex flex-col">
                                        <span class="font-semibold">
                                            <?= $prefix . number_format($paymentOriginalAmount, 2) ?> <?= esc($paymentCurrency) ?>
                                        </span>
                                        <?php if ($paymentCurrency !== $baseCurrency || abs($paymentExchangeRate - 1) > 0.0001): ?>
                                            <span class="text-xs">
                                                <?= lang('Finance.rateShort') ?> <?= rtrim(rtrim(number_format($paymentExchangeRate, 6), '0'), '.') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-end <?= $amountClass ?>">
                                    <?= $prefix . formatCurrency($payment['amount']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm truncate max-w-xs italic">
                                    <?php if (!empty($payment['notes'])): ?>
                                        <?= esc($payment['notes']) ?>
                                    <?php else: ?>
                                        <span class="">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (has_permission('finance', 'edit')): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    <?php if ($type === 'payment'): ?>
                                        <button type="button" onclick="voidPayment(<?= (int) $payment['id'] ?>)" class="hover:text-rose-600 transition-colors" title="<?= esc(lang('Finance.voidPayment')) ?>">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <?php $emptyColspan = has_permission('finance', 'edit') ? 8 : 7; ?>
                                <td colspan="<?= $emptyColspan ?>" class="px-6 py-12 text-center text-sm font-medium">
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
    <div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" data-finance-modal-root="paymentModal" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
        <div class="finance-inline-modal-backdrop fixed inset-0" onclick="closeFinanceModal('paymentModal')"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="if (event.target === this) closeFinanceModal('paymentModal')">
            <div class="finance-inline-modal-panel max-w-3xl" onclick="event.stopPropagation()">
                <div class="finance-inline-modal-header">
                    <div>
                        <span class="finance-inline-modal-kicker">
                            <i class="fas fa-circle-plus text-[11px]"></i>
                            <span><?= esc($ui['paymentKicker']) ?></span>
                        </span>
                        <h4 id="paymentModalTitle" class="text-base font-semibold"><?= esc($ui['recordPayment']) ?></h4>
                        <p class="mt-1 text-sm"><?= esc($ui['paymentNotice']) ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition" onclick="closeFinanceModal('paymentModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="invoicePaymentForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-emerald-600"></i>
                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-coins text-amber-500"></i>
                                <span><?= lang('Finance.currency') ?></span>
                            </label>
                            <div class="relative">
                                <select name="currency" data-settlement-currency class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.currency')) ?>">
                                    <?php foreach (($currency_options ?? [$baseCurrency]) as $currencyOption): ?>
                                        <option value="<?= esc($currencyOption) ?>" <?= $currencyOption === $baseCurrency ? 'selected' : '' ?>><?= esc($currencyOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-chart-line"></i>
                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="relative md:col-span-2">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-scale-balanced"></i>
                                <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                            </label>
                            <div class="relative">
                                <input type="text" data-settlement-base readonly
                                       class="finance-show-input font-semibold"
                                       value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid">
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-credit-card"></i>
                                <span><?= esc($ui['methodRequired']) ?></span>
                            </label>
                            <div class="relative">
                                <select name="payment_method" class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
                                    <option value="cash"><?= lang('Finance.cash') ?></option>
                                    <option value="card"><?= lang('Finance.card') ?></option>
                                    <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                    <option value="check"><?= lang('Finance.check') ?></option>
                                    <option value="other"><?= lang('Finance.other') ?></option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                        <?php if ($isExpense): ?>
                        <div class="relative hidden" id="settlementLiquidityAccountField">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-vault"></i>
                                <span><?= lang('Finance.paidFromAccount') ?></span>
                            </label>
                            <div class="relative">
                                <select id="settlementLiquidityAccount" name="liquidity_account" class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                                        <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-xs"><?= lang('Finance.paidFromAccountHelp') ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-rose-600"></i>
                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div class="relative">
                                <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-sticky-note"></i>
                            <span><?= lang('Finance.notes') ?></span>
                        </label>
                        <div class="relative">
                            <textarea name="notes" rows="2"
                                      class="finance-show-textarea resize-none"
                                      placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                    </div>
                    <div class="finance-inline-modal-actions">
                        <button type="button" onclick="closeFinanceModal('paymentModal')" class="inline-flex items-center gap-2 border px-4 py-2.5 text-sm font-semibold transition">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="finance-show-btn finance-show-btn-success px-6 py-3 text-sm">
                            <i class="fas fa-check-double"></i><?= esc($ui['savePayment']) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($isInvoice): ?>
    <div id="refundModal" class="fixed inset-0 z-50 hidden overflow-y-auto" data-finance-modal-root="refundModal" role="dialog" aria-modal="true" aria-labelledby="refundModalTitle">
        <div class="finance-inline-modal-backdrop fixed inset-0" onclick="closeFinanceModal('refundModal')"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="if (event.target === this) closeFinanceModal('refundModal')">
            <div class="finance-inline-modal-panel max-w-3xl" onclick="event.stopPropagation()">
                <div class="finance-inline-modal-header">
                    <div>
                        <span class="finance-inline-modal-kicker finance-inline-modal-kicker-danger">
                            <i class="fas fa-rotate-left text-[11px]"></i>
                            <span><?= esc($ui['refundKicker']) ?></span>
                        </span>
                        <h4 id="refundModalTitle" class="text-base font-semibold"><?= esc($ui['recordRefund']) ?></h4>
                        <p class="mt-1 text-sm"><?= esc($ui['refundNotice']) ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition" onclick="closeFinanceModal('refundModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="invoiceRefundForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-rose-600"></i>
                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-coins text-amber-500"></i>
                                <span><?= lang('Finance.currency') ?></span>
                            </label>
                            <div class="relative">
                                <select name="currency" data-settlement-currency class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.currency')) ?>">
                                    <?php foreach (($currency_options ?? [$baseCurrency]) as $currencyOption): ?>
                                        <option value="<?= esc($currencyOption) ?>" <?= $currencyOption === $baseCurrency ? 'selected' : '' ?>><?= esc($currencyOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-chart-line"></i>
                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="relative md:col-span-2">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-scale-balanced"></i>
                                <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                            </label>
                            <div class="relative">
                                <input type="text" data-settlement-base readonly
                                       class="finance-show-input font-semibold"
                                       value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid">
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-credit-card"></i>
                                <span><?= esc($ui['methodRequired']) ?></span>
                            </label>
                            <div class="relative">
                                <select name="payment_method" class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
                                    <option value="cash"><?= lang('Finance.cash') ?></option>
                                    <option value="card"><?= lang('Finance.card') ?></option>
                                    <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                    <option value="check"><?= lang('Finance.check') ?></option>
                                    <option value="other"><?= lang('Finance.other') ?></option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-amber-600"></i>
                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div class="relative">
                                <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-bold mb-3 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-rose-600"></i>
                            <span><?= esc($ui['refundNote']) ?></span>
                        </label>
                        <div class="relative">
                            <textarea name="notes" rows="2"
                                      class="finance-show-textarea resize-none"
                                      placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                    </div>
                    <div class="finance-inline-modal-actions">
                        <button type="button" onclick="closeFinanceModal('refundModal')" class="inline-flex items-center gap-2 border px-4 py-2.5 text-sm font-semibold transition">
                            <i class="fas fa-times text-xs"></i>
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="finance-show-btn finance-show-btn-danger px-6 py-3 text-sm">
                            <i class="fas fa-reply-all"></i><?= esc($ui['saveRefund']) ?>
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
        <div id="<?= esc($providerPayoutModalId) ?>" class="fixed inset-0 z-50 hidden overflow-y-auto" data-finance-modal-root="<?= esc($providerPayoutModalId) ?>" role="dialog" aria-modal="true" aria-labelledby="<?= esc($providerPayoutModalId) ?>Title">
            <div class="finance-inline-modal-backdrop fixed inset-0" onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')"></div>
            <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="if (event.target === this) closeFinanceModal('<?= esc($providerPayoutModalId) ?>')">
                <div class="finance-inline-modal-panel max-w-3xl" onclick="event.stopPropagation()">
                    <div class="finance-inline-modal-header">
                        <div>
                            <span class="finance-inline-modal-kicker">
                                <i class="fas fa-user-md text-[11px]"></i>
                                <span><?= esc($providerPayoutUi['kicker']) ?></span>
                            </span>
                            <h4 id="<?= esc($providerPayoutModalId) ?>Title" class="text-base font-semibold"><?= esc($providerPayoutUi['action']) ?></h4>
                            <p class="mt-1 text-sm"><?= esc($providerPayoutUi['notice']) ?></p>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition" onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <form class="finance-inline-modal-form provider-payout-payment-form" data-url="<?= esc(base_url('finance/payouts/' . (int) $providerPayout['id'] . '/payments')) ?>" data-finance-modal-close="<?= esc($providerPayoutModalId) ?>">
                        <?= csrf_field() ?>
                        <div class="border p-4">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide"><?= esc($providerPayoutUi['kicker']) ?></p>
                                    <h5 class="mt-1 truncate text-base font-bold"><?= esc($providerDisplayName) ?></h5>
                                    <p class="mt-1 text-xs">
                                        <?= esc(lang('Finance.financeTransaction')) ?>:
                                        <span class="font-semibold"><?= esc($finance['transaction_id'] ?? ('#' . ($finance['id'] ?? ''))) ?></span>
                                    </p>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center md:min-w-[22rem]">
                                    <div class="border px-3 py-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide"><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                        <p class="mt-1 text-sm font-bold" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_share_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                    <div class="border px-3 py-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide"><?= esc($providerPayoutUi['paid']) ?></p>
                                        <p class="mt-1 text-sm font-bold text-emerald-700" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_paid_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                    <div class="border px-3 py-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide"><?= esc($providerPayoutUi['left']) ?></p>
                                        <p class="mt-1 text-sm font-bold text-rose-700" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerBalanceDue, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                            <div class="relative">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold">
                                    <i class="fas fa-dollar-sign text-emerald-600"></i>
                                    <span><?= esc($ui['amountRequired']) ?></span>
                                </label>
                                <input type="number" name="amount" step="0.01" min="0.01" max="<?= esc($providerPayoutAmountValue) ?>" value="<?= esc($providerPayoutAmountValue) ?>" class="finance-show-input" placeholder="0.00" required>
                            </div>
                            <div class="relative">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold">
                                    <i class="fas fa-credit-card"></i>
                                    <span><?= esc($ui['methodRequired']) ?></span>
                                </label>
                                <div class="relative">
                                    <select name="payment_method" class="provider-payout-method-input finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
                                        <option value="cash"><?= lang('Finance.cash') ?></option>
                                        <option value="card"><?= lang('Finance.card') ?></option>
                                        <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                        <option value="check"><?= lang('Finance.check') ?></option>
                                        <option value="other"><?= lang('Finance.other') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="relative">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold">
                                    <i class="fas fa-calendar-alt text-rose-600"></i>
                                    <span><?= lang('Finance.dateRequired') ?></span>
                                </label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="finance-show-input">
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid">
                            <div class="provider-payout-liquidity-account-field relative hidden">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold">
                                    <i class="fas fa-vault"></i>
                                    <span><?= lang('Finance.paidFromAccount') ?></span>
                                </label>
                                <div class="relative">
                                    <select name="liquidity_account" class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                        <option value="bank"><?= esc(($check_outgoing_liquidity_options ?? [])['bank'] ?? 'Bank') ?></option>
                                        <option value="undeposited_checks"><?= esc(($check_outgoing_liquidity_options ?? [])['undeposited_checks'] ?? 'Checks on Hand') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs"><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
                            </div>
                        </div>
                        <div class="relative">
                            <label class="mb-3 flex items-center gap-2 text-sm font-bold">
                                <i class="fas fa-note-sticky text-amber-600"></i>
                                <span><?= esc($providerPayoutUi['historyNotes']) ?></span>
                            </label>
                            <textarea name="notes" rows="2" class="finance-show-input min-h-[5rem]" placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                        <div class="finance-inline-modal-actions">
                            <button type="button" onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')" class="inline-flex items-center gap-2 border px-4 py-2.5 text-sm font-semibold transition">
                                <i class="fas fa-times text-xs"></i>
                                <span><?= esc(lang('Finance.cancel')) ?></span>
                            </button>
                            <button type="submit" class="finance-show-btn finance-show-btn-success px-6 py-3 text-sm">
                                <i class="fas fa-check-double"></i><?= esc($providerPayoutUi['save']) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

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
</div>
<?= $this->endSection() ?>

