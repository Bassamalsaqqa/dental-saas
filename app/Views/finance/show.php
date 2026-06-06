<?= $this->extend('layouts/main_auth') ?>

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
<style>
    .finance-show-shell {
        min-height: 100vh;
        background:
            radial-gradient(920px 520px at 6% -10%, rgba(16, 185, 129, 0.10), transparent 58%),
            radial-gradient(780px 480px at 92% 0%, rgba(37, 99, 235, 0.10), transparent 54%),
            #f8fafc;
    }

    /* === Patient hero card pattern (shared from users/show.php) === */
    .patient-hero-card {
        position: relative;
        --hero-angle: 135deg;
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        --hero-orb-primary: rgba(255,255,255,0.18);
        --hero-orb-secondary: rgba(125,211,252,0.2);
        background: linear-gradient(var(--hero-angle), var(--hero-start) 0%, var(--hero-mid) 58%, var(--hero-end) 100%);
        box-shadow: 0 28px 70px -44px rgba(15,23,42,0.75);
    }
    .patient-hero-card-neutral { --hero-start: #334155; --hero-mid: #2563eb; --hero-end: #0f766e; --hero-orb-primary: rgba(255,255,255,0.16); --hero-orb-secondary: rgba(153,246,228,0.2); }
    [dir="rtl"] .patient-hero-card { --hero-angle: 225deg; }
    .patient-hero-overlay {
        position: absolute; inset: 0;
        background: radial-gradient(circle at 12% 18%, var(--hero-orb-primary), transparent 30%), radial-gradient(circle at 88% 20%, var(--hero-orb-secondary), transparent 26%), linear-gradient(120deg, rgba(255,255,255,0.04), rgba(255,255,255,0));
    }
    [dir="rtl"] .patient-hero-overlay {
        background: radial-gradient(circle at 88% 18%, var(--hero-orb-primary), transparent 30%), radial-gradient(circle at 12% 20%, var(--hero-orb-secondary), transparent 26%), linear-gradient(240deg, rgba(255,255,255,0.04), rgba(255,255,255,0));
    }
    .patient-avatar-badge {
        display: inline-flex; width: 5.5rem; height: 5.5rem; align-items: center; justify-content: center;
        border-radius: 1.75rem; background: linear-gradient(145deg, rgba(255,255,255,0.2), rgba(191,219,254,0.18));
        border: 1px solid rgba(255,255,255,0.22); color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.15), 0 22px 40px -24px rgba(15,23,42,0.72);
    }
    .patient-avatar-badge i { font-size: 2.55rem; line-height: 1; display: inline-block; }
    .patient-avatar-badge-neutral { background: linear-gradient(145deg, rgba(255,255,255,0.24), rgba(94,234,212,0.16)); }
    .patient-hero-chip-grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 0.85rem; }
    @media (min-width: 640px) { .patient-hero-chip-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1100px) { .patient-hero-chip-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .patient-hero-chip {
        display: inline-flex; align-items: center; gap: 0.55rem; min-height: 2.65rem; width: 100%;
        border-radius: 9999px; border: 1px solid rgba(255,255,255,0.16); background: rgba(255,255,255,0.12);
        padding: 0.52rem 0.82rem; color: #eff6ff; backdrop-filter: blur(12px); text-align: start; overflow: hidden;
    }
    .patient-hero-chip-icon { display: inline-flex; align-items: center; justify-content: center; width: 1.8rem; height: 1.8rem; border-radius: 9999px; background: rgba(255,255,255,0.12); flex-shrink: 0; font-size: 0.78rem; }
    .patient-hero-chip-text { display: flex; align-items: baseline; gap: 0.35rem; min-width: 0; }
    .patient-hero-chip-label { display: inline; flex-shrink: 0; font-size: 0.68rem; font-weight: 600; line-height: 1.2; color: rgba(219,234,254,0.84); }
    .patient-hero-chip-value { display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.76rem; font-weight: 500; line-height: 1.25; color: #fff; }
    .patient-hero-status { color: #eff6ff; backdrop-filter: blur(12px); }
    .patient-header-action {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.58rem; min-height: 2.65rem;
        border-radius: 0.95rem; padding: 0.58rem 0.78rem; font-size: 0.76rem; font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease; text-align: center;
    }
    [dir="rtl"] .patient-header-action, [dir="rtl"] .patient-hero-tool-button { flex-direction: row-reverse; }
    .patient-header-action:hover, .patient-hero-tool-button:hover { transform: translateY(-1px); }
    .patient-header-action-soft { border: 1px solid rgba(255,255,255,0.16); background: rgba(255,255,255,0.12); color: #eff6ff; }
    .patient-header-action-emerald { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 12px 22px -20px rgba(5,150,105,0.85); }
    .patient-header-action-danger { background: linear-gradient(135deg, #f43f5e, #e11d48); color: #fff; box-shadow: 0 12px 22px -20px rgba(225,29,72,0.88); }
    .patient-hero-tool-button {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.65rem; min-height: 2.4rem;
        border-radius: 1rem; padding: 0.48rem 0.72rem; font-size: 0.72rem; font-weight: 700; color: rgba(239,246,255,0.92);
        backdrop-filter: blur(12px); border: 1px dashed rgba(255,255,255,0.2); background: rgba(255,255,255,0.09);
        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease; text-align: center;
    }

    .finance-show-card {
        --finance-card-accent: #2563eb;
        --finance-card-soft: rgba(239,246,255,0.9);
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 7% 0%, color-mix(in srgb, var(--finance-card-accent) 14%, transparent), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,0.98), var(--finance-card-soft) 46%, rgba(255,255,255,0.98));
        border: 1px solid rgba(255,255,255,0.82);
        border-radius: 1.2rem;
        box-shadow: 0 22px 50px -40px rgba(15,23,42,0.48), 0 1px 0 rgba(255,255,255,0.92) inset;
        transition: box-shadow 0.2s;
    }
    .finance-show-card:hover { box-shadow: 0 26px 56px -40px rgba(15,23,42,0.54), 0 1px 0 rgba(255,255,255,0.92) inset; }
    .finance-show-card > * { position: relative; z-index: 1; }
    .finance-detail-card { --finance-card-accent: #2563eb; --finance-card-soft: rgba(219,234,254,0.92); }
    .finance-clinical-card { --finance-card-accent: #059669; --finance-card-soft: rgba(209,250,229,0.9); }
    .finance-provider-card { --finance-card-accent: #4f46e5; --finance-card-soft: rgba(238,242,255,0.92); }
    .finance-notes-card { --finance-card-accent: #d97706; --finance-card-soft: rgba(254,243,199,0.92); }
    .finance-history-card { --finance-card-accent: #0f766e; --finance-card-soft: rgba(204,251,241,0.9); }

    .finance-show-card-hero {
        background: #fff;
        border-color: #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 12px 32px -12px rgba(0,0,0,0.08);
    }

    .finance-show-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid rgba(203,213,225,0.72);
        background: linear-gradient(90deg, rgba(255,255,255,0.78), color-mix(in srgb, var(--finance-card-accent) 11%, white));
    }

    .finance-show-card-body {
        padding: 22px;
    }

    .finance-show-page-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
    }

    .finance-show-page-header {
        display: flex;
        flex-direction: column;
        gap: 18px;
        margin-bottom: 24px;
    }

    .finance-show-page-header-top {
        display: grid;
        gap: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 20px 24px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
    }

    .finance-show-page-heading {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .finance-show-page-title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }

    .finance-show-page-id {
        font-size: 2.15rem;
        line-height: 1.1;
        font-weight: 800;
        color: #0f172a;
    }

    .finance-show-page-submeta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .finance-show-page-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        border: 1px solid #d9e5f2;
        background: rgba(255, 255, 255, 0.88);
        padding: 8px 12px;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
    }

    .finance-show-page-meta-chip strong {
        color: #0f172a;
        font-weight: 700;
    }

    .finance-show-page-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
    }

    .finance-show-page-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 18px;
        border-top: 1px solid #e4edf7;
    }

    .finance-show-page-action-cluster {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }

    .finance-show-page-header-top-rtl {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .finance-show-page-header-top-rtl .finance-show-page-heading {
        text-align: right;
        align-items: stretch;
        width: 100%;
        max-width: 100%;
    }

    .finance-show-page-header-top-rtl .finance-show-page-meta,
    .finance-show-page-header-top-rtl .finance-show-page-submeta,
    [dir="rtl"] .finance-show-section-title {
        flex-direction: row-reverse;
    }

    .finance-show-page-header-top-rtl .finance-show-page-title-row,
    .finance-show-page-header-top-rtl .finance-show-page-submeta {
        width: 100%;
        justify-content: flex-end;
    }

    .finance-show-page-header-top-rtl .finance-show-page-id {
        width: 100%;
        text-align: right;
    }

    .finance-show-page-header-top-rtl .finance-show-page-actions {
        justify-content: space-between;
        width: 100%;
    }

    .finance-show-page-header-top-rtl .finance-show-page-title-row,
    .finance-show-page-header-top-rtl .finance-show-page-action-cluster {
        flex-direction: row-reverse;
    }

    .finance-show-page-header-top-rtl .finance-show-page-submeta {
        justify-content: flex-end;
    }

    .finance-show-page-header-top-rtl .finance-show-page-actions .finance-show-btn,
    [dir="rtl"] .finance-show-page-actions .finance-show-btn {
        flex-direction: row-reverse;
    }

    [dir="rtl"] .finance-show-card-header {
        flex-direction: row-reverse;
    }

    [dir="rtl"] .finance-show-card-header .inline-flex,
    [dir="rtl"] .finance-show-sidebar-card .inline-flex {
        flex-direction: row-reverse;
    }

    .finance-show-summary-card {
        --finance-card-accent: #047857;
        --finance-card-soft: rgba(220,252,231,0.9);
        padding: 20px;
    }

    .finance-show-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .finance-show-summary-stat {
        position: relative;
        overflow: hidden;
        min-height: 7rem;
        padding: 16px;
        border-radius: 18px;
        background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a);
        border: 0;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -28px rgba(15,23,42,0.55);
    }
    .finance-show-summary-stat:nth-child(2) {
        background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #059669, #047857 55%, #064e3b);
    }
    .finance-show-summary-stat:nth-child(3) {
        background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #d97706, #b45309 58%, #92400e);
    }
    .finance-show-summary-stat::after {
        content: "";
        position: absolute;
        inset-block-start: -44%;
        inset-inline-end: -10%;
        width: 9.5rem;
        aspect-ratio: 1;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
    }

    .finance-show-summary-label {
        position: relative;
        z-index: 1;
        font-size: 0.72rem;
        font-weight: 900;
        color: rgba(239,246,255,0.88);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
    }

    .finance-show-summary-value {
        position: relative;
        z-index: 1;
        font-size: 1.45rem;
        line-height: 1.2;
        font-weight: 900;
        color: #fff;
    }

    .finance-show-main {
        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0;
    }

    .finance-show-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0;
    }

    .finance-show-sidebar-stack {
        display: grid;
        gap: 18px;
        align-content: start;
    }

    .finance-show-full-span {
        grid-column: 1 / -1;
    }

    .finance-show-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    [dir="rtl"] .finance-show-section-title {
        justify-content: flex-start;
    }

    .finance-show-kpi {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #dce6f2;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 10px 22px -20px rgba(15, 23, 42, 0.2);
        min-height: 144px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .finance-show-kpi-label {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .finance-show-progress {
        position: relative;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
        height: 10px;
    }

    .finance-show-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0f766e 0%, #10b981 100%);
    }

    .finance-show-input,
    .finance-show-select,
    .finance-show-textarea {
        width: 100%;
        border-radius: 12px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #0f172a;
        padding: 12px 14px;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .finance-show-input:focus,
    .finance-show-select:focus,
    .finance-show-textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .finance-show-input[readonly] {
        background: #f8fafc;
        color: #475569;
    }

    .finance-show-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .finance-show-btn-modal {
        position: relative;
        padding-inline-start: 14px;
        padding-inline-end: 18px;
    }

    .finance-show-btn-modal::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
        opacity: 0.9;
        box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.18);
        flex-shrink: 0;
    }

    .finance-show-btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        box-shadow: 0 12px 20px -14px rgba(37, 99, 235, 0.6);
    }

    .finance-show-btn-primary:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    }

    .finance-show-btn-soft {
        background: linear-gradient(180deg, #ffffff 0%, #f5f9ff 100%);
        color: #334155;
        border: 1px solid #d8e2ef;
    }

    .finance-show-btn-soft:hover {
        background: linear-gradient(180deg, #ffffff 0%, #edf5ff 100%);
        border-color: #b7c8dd;
    }

    .finance-show-btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        box-shadow: 0 12px 20px -14px rgba(5, 150, 105, 0.55);
    }

    .finance-show-btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .finance-show-btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #ffffff;
    }

    .finance-show-btn-danger:hover {
        background: #b91c1c;
    }

    .finance-show-btn-warning {
        background: #d97706;
        color: #ffffff;
    }

    .finance-show-btn-warning:hover {
        background: #b45309;
    }

    .finance-show-table thead th {
        padding: 16px 24px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #4b5563;
        background: #f9fafb;
        text-align: start;
        white-space: nowrap;
    }

    .finance-show-table tbody td {
        padding: 16px 24px;
        vertical-align: top;
        white-space: nowrap;
    }

    .finance-show-field-label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
    }

    .finance-show-muted-box {
        border: 1px solid #dbe7f4;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
        padding: 16px;
    }

    .finance-show-meta-list {
        display: grid;
        gap: 14px;
    }

    .finance-show-meta-compact {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 16px;
    }

    .finance-show-meta-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.84);
        padding: 12px 14px;
    }

    .finance-show-meta-card dt {
        margin-bottom: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }

    .finance-show-meta-card dd {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }

    .finance-show-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .finance-show-meta-row dt {
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
    }

    .finance-show-meta-row dd {
        margin: 0;
        text-align: end;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    [dir="rtl"] .finance-show-card-body,
    [dir="rtl"] .finance-show-sidebar-card,
    [dir="rtl"] .finance-show-meta-list,
    [dir="rtl"] .finance-show-muted-box {
        text-align: right;
    }

    [dir="rtl"] .finance-show-context-head,
    [dir="rtl"] .finance-show-attachment-actions {
        flex-direction: row-reverse;
    }

    [dir="rtl"] .finance-show-meta-row {
        flex-direction: row-reverse;
    }

    [dir="rtl"] .finance-show-meta-card {
        text-align: right;
    }

    .finance-show-sidebar-card {
        border: 1px solid #dce6f2;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 18px;
        box-shadow: 0 10px 22px -20px rgba(15, 23, 42, 0.18);
    }

    .finance-show-context-card {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .finance-show-context-head {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .finance-show-context-avatar {
        display: flex;
        height: 64px;
        width: 64px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        color: #ffffff;
        font-size: 1.125rem;
        font-weight: 800;
        box-shadow: 0 16px 26px -22px rgba(37, 99, 235, 0.75);
    }

    .finance-show-context-actions {
        display: grid;
        gap: 10px;
    }

    .finance-show-attachment-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .finance-show-context-divider {
        border-top: 1px solid #e2e8f0;
        padding-top: 16px;
    }

    .finance-show-sidebar-stack > .finance-show-sidebar-card:nth-child(3n + 1) {
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
    }

    .finance-show-sidebar-stack > .finance-show-sidebar-card:nth-child(3n + 2) {
        background: linear-gradient(180deg, #ffffff 0%, #fbfbff 100%);
    }

    .finance-show-sidebar-stack > .finance-show-sidebar-card:nth-child(3n + 3) {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdf8 100%);
    }

    .finance-show-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .finance-inline-modal-backdrop {
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
    }

    .finance-inline-modal-panel {
        width: 100%;
        border-radius: 24px;
        border: 1px solid #dce6f2;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 28px 60px -36px rgba(15, 23, 42, 0.55);
        overflow: hidden;
    }

    .finance-inline-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        border-bottom: 1px solid #e2e8f0;
        padding: 22px 24px 18px;
        background: linear-gradient(180deg, rgba(248, 251, 255, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%);
    }

    [dir="rtl"] .finance-inline-modal-header {
        position: relative;
        flex-direction: row-reverse;
        text-align: right;
        align-items: flex-start;
        padding-inline-start: 72px;
    }

    [dir="rtl"] .finance-inline-modal-header > :first-child {
        flex: 1 1 auto;
    }

    [dir="rtl"] .finance-inline-modal-header > :last-child {
        flex: 0 0 auto;
        position: absolute;
        left: 24px;
        right: auto;
        top: 22px;
    }

    .finance-inline-modal-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        border-radius: 999px;
        background: #eef6ff;
        color: #1d4ed8;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    [dir="rtl"] .finance-inline-modal-kicker {
        flex-direction: row-reverse;
        justify-content: flex-start;
    }

    .finance-inline-modal-kicker.finance-inline-modal-kicker-danger {
        background: #fff1f2;
        color: #be123c;
    }

    .finance-inline-modal-form {
        padding: 24px;
    }

    [dir="rtl"] .finance-inline-modal-form {
        text-align: right;
    }

    .finance-inline-modal-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 20px;
    }

    .finance-inline-modal-grid + .finance-inline-modal-grid,
    .finance-inline-modal-form > .group\/field {
        margin-top: 20px;
    }

    .finance-inline-modal-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid #e2e8f0;
    }

    [dir="rtl"] .finance-inline-modal-actions {
        flex-direction: row-reverse;
        justify-content: flex-start;
    }

    [dir="rtl"] .finance-inline-modal-form label.flex,
    [dir="rtl"] .finance-inline-modal-form .inline-flex {
        flex-direction: row-reverse;
    }

    .finance-modal-select + .select2-container .select2-selection--single {
        min-height: 3rem;
        border: 2px solid #e5e7eb !important;
        border-radius: 0.9rem !important;
        background: rgba(255, 255, 255, 0.92) !important;
        box-shadow: 0 10px 18px -16px rgba(15, 23, 42, 0.45) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .finance-modal-select + .select2-container.select2-container--focus .select2-selection--single,
    .finance-modal-select + .select2-container.select2-container--open .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18) !important;
    }

    .finance-modal-select + .select2-container .select2-selection__rendered {
        padding: 0.72rem 2.75rem 0.72rem 1rem !important;
        color: #0f172a !important;
        font-size: 0.875rem !important;
        line-height: 1.4rem !important;
    }

    [dir="rtl"] .finance-modal-select + .select2-container .select2-selection__rendered {
        padding: 0.72rem 1rem 0.72rem 2.75rem !important;
        text-align: right;
    }

    .finance-modal-select + .select2-container .select2-selection__arrow {
        top: 0.55rem !important;
        right: 0.65rem !important;
    }

    [dir="rtl"] .finance-modal-select + .select2-container .select2-selection__arrow {
        left: 0.65rem !important;
        right: auto !important;
    }

    .finance-show-content-tile,
    .finance-breakdown-panel,
    .finance-clinical-snapshot,
    .finance-provider-payout-card,
    .finance-sidebar-inner-card,
    .finance-notes-body {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(203,213,225,0.78);
        background: rgba(255,255,255,0.9);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.92), 0 14px 30px -27px rgba(15,23,42,0.35);
    }

    .finance-show-content-tile {
        min-height: 7rem;
        padding: 1.12rem;
        border-inline-start: 5px solid #2563eb;
        background: radial-gradient(circle at 96% 0%, rgba(37,99,235,0.16), transparent 36%), linear-gradient(135deg, rgba(239,246,255,0.98), rgba(255,255,255,0.94));
    }

    .finance-show-content-tile:nth-child(2n) {
        border-inline-start-color: #059669;
        background: radial-gradient(circle at 96% 0%, rgba(5,150,105,0.16), transparent 36%), linear-gradient(135deg, rgba(236,253,245,0.98), rgba(255,255,255,0.94));
    }

    .finance-show-content-tile .text-gray-800,
    .finance-show-content-tile .text-gray-700 {
        color: #1e293b !important;
        font-size: 0.96rem;
        font-weight: 800;
        line-height: 1.7;
    }

    .finance-show-content-tile .text-gray-400 {
        color: #64748b !important;
        font-weight: 700;
    }

    .finance-breakdown-panel {
        border: 0 !important;
        background: radial-gradient(circle at 8% 0%, rgba(255,255,255,0.12), transparent 34%), linear-gradient(135deg, #0f172a, #1e293b 58%, #334155) !important;
        color: #fff;
    }

    .finance-breakdown-panel .divide-y > div {
        border-color: rgba(255,255,255,0.1) !important;
    }

    .finance-breakdown-panel span,
    .finance-breakdown-panel .text-slate-500,
    .finance-breakdown-panel .text-slate-700,
    .finance-breakdown-panel .text-slate-800,
    .finance-breakdown-panel .text-slate-900 {
        color: rgba(255,255,255,0.9) !important;
    }

    .finance-breakdown-panel .bg-slate-50\/80 {
        background: rgba(255,255,255,0.11) !important;
    }

    .finance-breakdown-panel .text-rose-600,
    .finance-breakdown-panel .text-rose-700 { color: #fecdd3 !important; }
    .finance-breakdown-panel .text-emerald-700 { color: #bbf7d0 !important; }

    .finance-clinical-snapshot {
        border: 0 !important;
        background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 35%), linear-gradient(135deg, #059669, #047857 58%, #064e3b) !important;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 36px -28px rgba(15,23,42,0.62);
    }

    .finance-clinical-snapshot .text-emerald-700,
    .finance-clinical-snapshot .text-slate-600,
    .finance-clinical-snapshot .text-slate-500 {
        color: rgba(255,255,255,0.88) !important;
    }

    .finance-trace-stat {
        border: 1px solid rgba(255,255,255,0.2) !important;
        background: rgba(255,255,255,0.15) !important;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
    }

    .finance-trace-stat p,
    .finance-trace-stat .text-slate-500,
    .finance-trace-stat .text-slate-900,
    .finance-trace-stat .text-emerald-700,
    .finance-trace-stat .text-blue-700,
    .finance-trace-stat .text-indigo-700,
    .finance-trace-stat .text-purple-700,
    .finance-trace-stat .text-rose-700 {
        color: #fff !important;
    }

    .finance-provider-stat {
        border: 0 !important;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 16px 30px -26px rgba(15,23,42,0.52);
    }

    .finance-provider-stat:nth-child(1) { background: linear-gradient(135deg, #2563eb, #1d4ed8) !important; }
    .finance-provider-stat:nth-child(2) { background: linear-gradient(135deg, #4f46e5, #4338ca) !important; }
    .finance-provider-stat:nth-child(3) { background: linear-gradient(135deg, #059669, #047857) !important; }
    .finance-provider-stat:nth-child(4) { background: linear-gradient(135deg, #e11d48, #be123c) !important; }
    .finance-provider-stat:nth-child(5) { background: linear-gradient(135deg, #0284c7, #0369a1) !important; }
    .finance-provider-stat p { color: #fff !important; }

    .finance-provider-payout-card {
        border: 1px solid rgba(199,210,254,0.76) !important;
        background: radial-gradient(circle at 6% 0%, rgba(79,70,229,0.16), transparent 33%), linear-gradient(135deg, rgba(238,242,255,0.98), rgba(255,255,255,0.96)) !important;
    }

    .finance-provider-mini-stat {
        border: 0 !important;
        color: #fff !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 14px 28px -24px rgba(15,23,42,0.48);
    }

    .finance-provider-mini-stat:nth-child(1) { background: linear-gradient(135deg, #4f46e5, #3730a3) !important; }
    .finance-provider-mini-stat:nth-child(2) { background: linear-gradient(135deg, #059669, #047857) !important; }
    .finance-provider-mini-stat:nth-child(3) { background: linear-gradient(135deg, #e11d48, #be123c) !important; }
    .finance-provider-mini-stat p { color: #fff !important; }

    .finance-provider-history-panel,
    .finance-attachment-item {
        border: 1px solid rgba(203,213,225,0.78) !important;
        background: rgba(255,255,255,0.86) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.92), 0 14px 30px -27px rgba(15,23,42,0.35);
    }

    .finance-provider-cta {
        border: 0 !important;
        background: radial-gradient(circle at 8% 0%, rgba(255,255,255,0.24), transparent 35%), linear-gradient(135deg, #059669, #047857 58%, #064e3b) !important;
        color: #fff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 16px 30px -26px rgba(15,23,42,0.52);
    }

    .finance-provider-cta p { color: #fff !important; }

    .finance-table-panel {
        border: 1px solid rgba(203,213,225,0.82) !important;
        border-radius: 1rem !important;
        box-shadow: 0 16px 36px -30px rgba(15,23,42,0.44);
    }

    .finance-show-table thead th {
        font-weight: 900 !important;
        color: #334155 !important;
    }

    .finance-show-table tbody td {
        font-weight: 650;
        color: #334155;
    }

    .finance-sidebar-inner-card {
        padding: 1rem;
        border-inline-start: 5px solid #4f46e5;
        background: radial-gradient(circle at 96% 0%, rgba(79,70,229,0.15), transparent 34%), linear-gradient(135deg, rgba(238,242,255,0.98), rgba(255,255,255,0.95));
    }

    .finance-sidebar-inner-card--attachments {
        border-inline-start-color: #0f766e;
        background: radial-gradient(circle at 96% 0%, rgba(15,118,110,0.16), transparent 34%), linear-gradient(135deg, rgba(240,253,250,0.98), rgba(255,255,255,0.95));
    }

    .finance-sidebar-inner-card--clinical {
        border-inline-start-color: #059669;
        background: radial-gradient(circle at 96% 0%, rgba(5,150,105,0.16), transparent 34%), linear-gradient(135deg, rgba(236,253,245,0.98), rgba(255,255,255,0.95));
    }

    .finance-notes-body {
        border-inline-start: 5px solid #d97706 !important;
        background: radial-gradient(circle at 96% 0%, rgba(217,119,6,0.16), transparent 36%), linear-gradient(135deg, rgba(255,251,235,0.98), rgba(255,255,255,0.94)) !important;
        color: #1e293b !important;
        font-size: 0.96rem;
        font-weight: 800;
        line-height: 1.75;
    }

    .finance-history-card .finance-show-card-header {
        background: radial-gradient(circle at 8% 0%, rgba(255,255,255,0.2), transparent 34%), linear-gradient(135deg, #0f766e, #047857) !important;
        color: #fff;
    }

    .finance-history-card .finance-show-card-header h3,
    .finance-history-card .finance-show-card-header p,
    .finance-history-card .finance-show-card-header .text-gray-500 {
        color: #fff !important;
    }

    .finance-history-card .finance-show-card-header .bg-white {
        background: rgba(255,255,255,0.16) !important;
        border-color: rgba(255,255,255,0.18) !important;
        color: #fff !important;
    }

    @media (min-width: 1280px) {
        .finance-show-page-grid {
            grid-template-columns: minmax(0, 1.95fr) minmax(300px, 0.8fr);
            align-items: start;
        }

        .finance-show-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px;
        }

        .finance-show-summary-stat::after {
            content: "";
            position: absolute;
            inset-block-start: -44%;
            inset-inline-end: -10%;
            width: 9.5rem;
            aspect-ratio: 1;
            border-radius: 999px;
            background: rgba(255,255,255,0.16);
        }

        .finance-show-summary-stat:last-child::after {
            display: block;
        }

        .finance-show-main--with-forms {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.75fr);
            gap: 24px;
            align-items: start;
        }

        .finance-show-primary-block {
            grid-column: 1;
        }

        .finance-show-forms-block {
            grid-column: 2;
            grid-row: 1 / span 3;
            align-self: start;
        }

        .finance-show-sidebar-stack {
            grid-template-columns: minmax(0, 1fr);
        }

        [dir="rtl"] .finance-show-page-grid {
            direction: rtl;
        }

        [dir="ltr"] .finance-show-page-grid {
            direction: ltr;
        }

        .finance-show-page-header-top {
            padding: 28px 32px;
        }

        .finance-show-page-actions {
            flex-wrap: nowrap;
        }

        .finance-show-page-action-cluster {
            flex-wrap: nowrap;
        }

    }

    @media (max-width: 767.98px) {
        .finance-show-card-header,
        .finance-show-card-body {
            padding: 18px;
        }

        .finance-show-table thead th,
        .finance-show-table tbody td {
            padding: 14px 16px;
        }

        .finance-show-action-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .finance-show-summary-card {
            padding: 20px;
        }

        .finance-show-summary-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .finance-show-page-header-top {
            padding: 20px;
        }

        .finance-show-page-actions {
            padding-top: 16px;
        }

        .finance-show-page-id {
            font-size: 1.85rem;
        }

        .finance-show-context-head {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .finance-inline-modal-header,
        .finance-inline-modal-form {
            padding-inline: 18px;
        }

        .finance-show-meta-compact {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (min-width: 768px) {
        .finance-inline-modal-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .finance-inline-modal-grid.finance-inline-modal-grid-3 {
            grid-template-columns: 0.9fr 1.1fr 1.2fr;
        }
    }
</style>
<div class="finance-show-shell px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">
        <!-- Hero Header (clone of /patient/149 pattern) -->
        <section class="patient-hero-card patient-hero-card-neutral overflow-hidden rounded-[28px] border border-slate-200 mb-8">
            <div class="patient-hero-overlay"></div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="flex flex-col gap-5">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                            <span><?= $isRtl ? 'المالية' : 'Finance' ?></span>
                            <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                            <span><?= esc($transactionTypeLabel) ?></span>
                        </div>
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                            <div class="patient-avatar-badge patient-avatar-badge-neutral">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc('#' . $finance['transaction_id']) ?></h1>
                                        <span class="patient-hero-status inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold" style="background:<?= esc($statusTone['hero_bg'] ?? 'rgba(255,255,255,0.12)') ?>;border-color:<?= esc($statusTone['hero_border'] ?? 'rgba(255,255,255,0.2)') ?>;color:#eff6ff;">
                                            <?= esc($paymentStatusLabel) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="patient-hero-chip-grid">
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
                    <div class="w-full space-y-2 xl:w-[21rem]">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <a href="<?= esc(workflow_back('finance', $return_to ?? null)) ?>" class="patient-header-action patient-header-action-soft">
                                <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                                <span><?= esc($ui['back']) ?></span>
                            </a>
                            <a href="<?= workflow_context_url('finance/' . $finance['id'] . '/edit', $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft">
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
                            <button type="button" onclick="openFinanceModal('paymentModal', this)" class="patient-header-action patient-header-action-emerald">
                                <i class="fas fa-plus-circle"></i>
                                <span><?= esc($ui['recordPayment']) ?></span>
                            </button>
                            <?php endif; ?>
                            <?php if ($showRefund): ?>
                            <button type="button" onclick="openFinanceModal('refundModal', this)" class="patient-header-action patient-header-action-danger">
                                <i class="fas fa-rotate-left"></i>
                                <span><?= esc($ui['recordRefund']) ?></span>
                            </button>
                            <?php endif; ?>
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
                <div class="finance-show-summary-stat">
                    <p class="finance-show-summary-label"><?= lang('Finance.totalAmount') ?></p>
                    <p class="finance-show-summary-value"><?= number_format($totalAmount, 2) ?> <?= esc($baseCurrency) ?></p>
                </div>
                <div class="finance-show-summary-stat">
                    <p class="finance-show-summary-label"><?= lang('Finance.paidToDate') ?></p>
                    <p class="finance-show-summary-value"><?= number_format($paidSoFar, 2) ?> <?= esc($baseCurrency) ?></p>
                </div>
                <div class="finance-show-summary-stat">
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
                    <div class="finance-show-card-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <i class="fas fa-list-ul"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= esc($ui['details']) ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= esc($isExpense ? $expenseCategoryLabel : $serviceTypeLabel) ?>
                        </span>
                    </div>
                    <div class="finance-show-card-body">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="finance-show-content-tile">
                                    <label class="finance-show-field-label"><?= $isExpense ? lang('Finance.expenseDescription') : lang('Finance.serviceDescription') ?></label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= !empty($finance['description']) ? nl2br(htmlspecialchars($finance['description'])) : '<span class="text-gray-400 italic">' . esc(lang('Finance.noDescriptionProvided')) . '</span>' ?>
                                    </div>
                                </div>
                                <div class="finance-show-content-tile">
                                    <label class="finance-show-field-label"><?= lang('Finance.createdByLabel') ?></label>
                                    <div class="text-gray-800 leading-relaxed font-semibold">
                                        <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?>
                                    </div>
                                </div>
                                <?php if ($isExpense && !empty($finance['counterparty_name'])): ?>
                                <div class="finance-show-content-tile">
                                    <label class="finance-show-field-label"><?= lang('Finance.payeeVendor') ?></label>
                                    <div class="text-gray-800 leading-relaxed font-semibold">
                                        <?php if (!empty($supplier['id'])): ?>
                                            <a href="<?= base_url('inventory/suppliers/' . (int) $supplier['id']) ?>" class="inline-flex items-center gap-2 text-blue-700 hover:text-blue-800 hover:underline">
                                                <i class="fas fa-truck-field text-blue-500"></i>
                                                <span><?= esc($finance['counterparty_name']) ?></span>
                                            </a>
                                        <?php else: ?>
                                            <?= esc($finance['counterparty_name']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($finance['service_details'])): ?>
                                <div class="finance-show-content-tile">
                                    <label class="finance-show-field-label"><?= lang('Finance.technicalDetails') ?></label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($finance['service_details'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="finance-breakdown-panel overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="divide-y divide-slate-100 text-sm">
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium text-slate-500"><?= lang('Finance.subtotal') ?></span>
                                        <span class="font-semibold text-slate-800"><?= number_format($finance['amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php if ($isConverted): ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium text-slate-500"><?= lang('Finance.original') ?></span>
                                        <span class="font-semibold text-slate-700 text-end">
                                            <?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?>
                                            @ <?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['discount_amount'] > 0): ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium text-slate-500"><?= lang('Finance.discount') ?></span>
                                        <span class="font-semibold text-rose-600">-<?= number_format($finance['discount_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['tax_amount'] > 0): ?>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="font-medium text-slate-500"><?= lang('Finance.taxPercent') ?> (<?= number_format($taxPercent, 2) ?>%)</span>
                                        <span class="font-semibold text-slate-800">+<?= number_format($finance['tax_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-between gap-4 bg-slate-50/80 px-5 py-5">
                                        <span class="text-base font-bold text-slate-800"><?= lang('Finance.total') ?></span>
                                        <span class="text-2xl font-bold text-slate-900"><?= number_format($totalAmount, 2) ?> <span class="text-base font-medium text-slate-500"><?= esc($baseCurrency) ?></span></span>
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
                    <div class="finance-show-card-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <i class="fas fa-tooth"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800"><?= esc(lang('Finance.clinicalWorkBilled')) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= esc(lang('Finance.clinicalWorkBilledHelp')) ?></p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-700" dir="ltr">
                            <?= esc(formatCurrency($billedClinicalTotal)) ?>
                        </span>
                    </div>
                    <div class="finance-show-card-body space-y-5">
                        <div class="finance-clinical-snapshot rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700"><?= esc(lang('Finance.clinicalTraceSnapshot')) ?></p>
                                    <p class="mt-1 text-sm text-slate-600"><?= esc(lang('Finance.clinicalTraceSnapshotHelp')) ?></p>
                                </div>
                                <?php if (!empty($patient['id'])): ?>
                                    <a href="<?= esc(workflow_context_url('patient/' . (int) $patient['id'], $return_to ?? null)) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm font-bold text-emerald-700 shadow-sm hover:bg-emerald-50">
                                        <i class="fas <?= $isRtl ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i>
                                        <span><?= esc(lang('Finance.openPatientProfile')) ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                                <div class="finance-trace-stat rounded-xl border border-white/70 bg-white/85 p-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc(lang('Finance.clinicalTracePatient')) ?></p>
                                    <p class="mt-1 truncate text-sm font-bold text-slate-900">
                                        <?= esc($relatedPartyValue !== '' ? $relatedPartyValue : lang('Finance.unknown')) ?>
                                    </p>
                                    <p class="mt-1 truncate text-xs text-slate-500"><?= esc($relatedPartyMeta) ?></p>
                                </div>
                                <div class="finance-trace-stat rounded-xl border border-white/70 bg-white/85 p-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc(lang('Finance.clinicalTraceLines')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold text-emerald-700"><?= esc((string) count($billedClinicalChargeLines)) ?></p>
                                </div>
                                <div class="finance-trace-stat rounded-xl border border-white/70 bg-white/85 p-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc(lang('Finance.clinicalTraceTreatments')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold text-blue-700"><?= esc((string) $billedClinicalTreatmentCount) ?></p>
                                </div>
                                <div class="finance-trace-stat rounded-xl border border-white/70 bg-white/85 p-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc(lang('Finance.clinicalTraceExaminations')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold text-indigo-700"><?= esc((string) $billedClinicalExaminationCount) ?></p>
                                </div>
                                <div class="finance-trace-stat rounded-xl border border-white/70 bg-white/85 p-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc(lang('Finance.clinicalTraceProviders')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold text-purple-700"><?= esc((string) $billedClinicalProviderCount) ?></p>
                                </div>
                                <div class="finance-trace-stat rounded-xl border border-white/70 bg-white/85 p-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc(lang('UI.moneyTrailInventoryCost')) ?></p>
                                    <p class="mt-1 text-lg font-extrabold <?= $billedInventoryUsageTotal > 0.009 ? 'text-rose-700' : 'text-slate-500' ?>" dir="ltr"><?= esc(formatCurrency($billedInventoryUsageTotal)) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="finance-table-panel overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
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
                                                    <div class="font-semibold text-slate-900"><?= esc($line['procedure_name'] ?? lang('Finance.clinicalChargeLine')) ?></div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5">
                                                            <?= esc(lang('Finance.clinicalChargeLine')) ?> #<?= esc((string) ($line['id'] ?? '')) ?>
                                                        </span>
                                                        <?php if ($sourceType !== ''): ?>
                                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-blue-700">
                                                                <?= esc(lang('Finance.clinicalSource')) ?>: <?= esc($sourceType) ?><?= $sourceId > 0 ? ' #' . esc((string) $sourceId) : '' ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($line['tooth_numbers'])): ?>
                                                        <div class="mt-1 text-xs text-slate-500"><?= esc(lang('Finance.tooth')) ?>: <span dir="ltr"><?= esc($line['tooth_numbers']) ?></span></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <?php if ($treatmentId > 0): ?>
                                                        <a href="<?= esc(workflow_context_url('treatment/' . $treatmentId, $return_to ?? null)) ?>" class="font-semibold text-emerald-700 hover:underline">
                                                            <?= esc($treatmentLabel !== '' ? $treatmentLabel : ('#' . $treatmentId)) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-slate-400"><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <?php if ($examinationId > 0): ?>
                                                        <a href="<?= esc(workflow_context_url('examination/' . $examinationId, $return_to ?? null)) ?>" class="font-semibold text-blue-700 hover:underline">
                                                            <?= esc($examinationLabel !== '' ? $examinationLabel : ('#' . $examinationId)) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-slate-400"><?= esc(lang('Finance.notAvailable')) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 text-slate-700"><?= esc($providerDisplay) ?></td>
                                                <td class="px-4 py-3 text-slate-700"><?= !empty($line['service_date']) ? esc(formatDate($line['service_date'])) : esc(lang('Finance.notAvailable')) ?></td>
                                                <td class="px-4 py-3">
                                                    <?php if ($lineInventoryCost > 0.009): ?>
                                                        <div class="font-bold text-rose-700" dir="ltr"><?= esc(formatCurrency($lineInventoryCost)) ?></div>
                                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                                            <?php foreach (array_slice($lineInventoryRows, 0, 2) as $usageRow): ?>
                                                                <?php $usageFinanceId = (int) ($usageRow['finance_entry_id'] ?? 0); ?>
                                                                <?php if ($usageFinanceId > 0): ?>
                                                                    <a href="<?= esc(workflow_context_url('finance/' . $usageFinanceId, $return_to ?? null)) ?>" class="inline-flex items-center gap-1 rounded-full border border-rose-100 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 hover:bg-rose-100">
                                                                        <i class="fas fa-boxes-stacked"></i>
                                                                        <span><?= esc($summarizeInventoryUsage($usageRow)) ?></span>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="inline-flex items-center gap-1 rounded-full border border-rose-100 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">
                                                                        <i class="fas fa-boxes-stacked"></i>
                                                                        <span><?= esc($summarizeInventoryUsage($usageRow)) ?></span>
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-slate-400"><?= esc(lang('Finance.notAvailable')) ?></span>
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
                    <div class="finance-show-card-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= esc($providerPayoutUi['sectionTitle']) ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?= ($provider_payout_summary['provider_balance_due'] ?? 0) > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                            <?= ($provider_payout_summary['provider_balance_due'] ?? 0) > 0 ? esc($providerPayoutUi['sectionStatusOpen']) : esc($providerPayoutUi['sectionStatusClear']) ?>
                        </span>
                    </div>
                    <div class="finance-show-card-body space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                            <div class="finance-provider-stat rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($providerPayoutUi['collected']) ?></p>
                                <p class="mt-1 font-bold text-gray-900"><?= formatCurrency($provider_payout_summary['gross_amount'] ?? 0) ?></p>
                            </div>
                            <div class="finance-provider-stat rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                <p class="mt-1 font-bold text-indigo-700"><?= formatCurrency($provider_payout_summary['provider_share_amount'] ?? 0) ?></p>
                            </div>
                            <div class="finance-provider-stat rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($providerPayoutUi['paid']) ?></p>
                                <p class="mt-1 font-bold text-emerald-700"><?= formatCurrency($provider_payout_summary['provider_paid_amount'] ?? 0) ?></p>
                            </div>
                            <div class="finance-provider-stat rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($providerPayoutUi['left']) ?></p>
                                <p class="mt-1 font-bold text-rose-700"><?= formatCurrency($provider_payout_summary['provider_balance_due'] ?? 0) ?></p>
                            </div>
                            <div class="finance-provider-stat rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($providerPayoutUi['clinicKeeps']) ?></p>
                                <p class="mt-1 font-bold text-sky-700"><?= formatCurrency($provider_payout_summary['clinic_share_amount'] ?? 0) ?></p>
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
                                <div class="finance-provider-payout-card rounded-2xl border border-slate-200 bg-slate-50/80 p-5 space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-base font-bold text-slate-900"><?= esc($providerDisplayName) ?></h3>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                                                    <?= esc($providerTypeLabel) ?>
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold <?= $providerStatusClass ?>">
                                                    <?= esc($providerStatusLabel) ?>
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-500">
                                                <?= esc($providerPayoutUi['rule']) ?>:
                                                <?= esc(lang('Finance.' . ($providerPayout['split_mode'] ?? 'percentage'))) ?>
                                                <?= esc($providerPayout['split_value'] ?? 0) ?><?= ($providerPayout['split_mode'] ?? '') === 'percent' ? '%' : '' ?>
                                            </p>
                                            <?php if (!empty($providerPayout['notes'])): ?>
                                                <p class="mt-2 text-sm text-slate-600"><?= esc($providerPayout['notes']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($providerTypeKey === 'user' && $providerUserId > 0): ?>
                                                <a href="<?= esc(workflow_context_url('users/' . $providerUserId, $return_to ?? null)) ?>" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-indigo-700 hover:text-indigo-900">
                                                    <i class="fas fa-user-doctor"></i>
                                                    <span><?= esc(lang('Finance.openProviderProfile')) ?></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:min-w-[28rem]">
                                            <div class="finance-provider-mini-stat rounded-xl border border-slate-200 bg-white p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                                <p class="mt-1 text-sm font-bold text-indigo-700"><?= formatCurrency($providerPayout['provider_share_amount'] ?? 0) ?></p>
                                            </div>
                                            <div class="finance-provider-mini-stat rounded-xl border border-slate-200 bg-white p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc($providerPayoutUi['paid']) ?></p>
                                                <p class="mt-1 text-sm font-bold text-emerald-700"><?= formatCurrency($providerPayout['provider_paid_amount'] ?? 0) ?></p>
                                            </div>
                                            <div class="finance-provider-mini-stat rounded-xl border border-slate-200 bg-white p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc($providerPayoutUi['left']) ?></p>
                                                <p class="mt-1 text-sm font-bold text-rose-700"><?= formatCurrency($providerPayout['provider_balance_due'] ?? 0) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="finance-provider-history-panel rounded-2xl border border-slate-200 bg-white overflow-hidden">
                                        <div class="flex items-center justify-between gap-3 px-4 py-3 bg-slate-50">
                                            <div>
                                                <h4 class="text-sm font-semibold text-slate-900"><?= esc($providerPayoutUi['history']) ?></h4>
                                                <p class="mt-1 text-xs text-slate-500"><?= esc($providerPayoutUi['historyHelp']) ?></p>
                                            </div>
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                <?= count($providerPayoutPayments) ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($providerPayoutPayments)): ?>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full text-sm">
                                                    <thead class="bg-white">
                                                        <tr class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyDate']) ?></th>
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyMethod']) ?></th>
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyAmount']) ?></th>
                                                            <th class="px-4 py-3 <?= $isRtl ? 'text-right' : 'text-left' ?>"><?= esc($providerPayoutUi['historyNotes']) ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100">
                                                        <?php foreach ($providerPayoutPayments as $paymentRow): ?>
                                                            <tr class="bg-white">
                                                                <td class="px-4 py-3 text-slate-700"><?= !empty($paymentRow['paid_date']) ? esc(formatDate($paymentRow['paid_date'])) : '-' ?></td>
                                                                <td class="px-4 py-3 text-slate-700"><?= esc(lang('Finance.' . ($paymentRow['payment_method'] ?? 'cash'))) ?></td>
                                                                <td class="px-4 py-3 font-semibold text-slate-900"><?= formatCurrency($paymentRow['amount'] ?? 0) ?></td>
                                                                <td class="px-4 py-3 text-slate-600"><?= !empty($paymentRow['notes']) ? esc($paymentRow['notes']) : '-' ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="px-4 py-4 text-sm text-slate-500"><?= esc($providerPayoutUi['historyEmpty']) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (floatval($providerPayout['provider_balance_due'] ?? 0) > 0 && has_permission('finance', 'edit')): ?>
                                        <?php $providerPayoutModalId = 'providerPayoutModal' . (int) $providerPayout['id']; ?>
                                        <div class="finance-provider-cta flex flex-col gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="<?= $isRtl ? 'text-right' : 'text-left' ?>">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= esc($providerPayoutUi['balanceReady']) ?></p>
                                                <p class="mt-1 text-sm font-bold text-slate-900"><?= formatCurrency($providerPayout['provider_balance_due'] ?? 0) ?></p>
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
                <div class="finance-show-card finance-notes-card finance-show-primary-block p-6">
                    <h2 class="mb-4 flex items-center gap-3 text-xl font-bold text-gray-800">
                        <i class="fas fa-sticky-note text-yellow-600"></i><?= lang('Finance.administrativeNotes') ?>
                    </h2>
                    <div class="finance-notes-body text-gray-700 leading-relaxed bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                        <?= nl2br(htmlspecialchars($finance['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <aside class="finance-show-sidebar">
                <div class="finance-show-sidebar-stack">
                    <?php if ($patient): ?>
                    <div class="finance-show-sidebar-card finance-sidebar-inner-card finance-show-context-card">
                        <h2 class="mb-4 flex items-center gap-2 text-xl font-bold text-gray-800">
                            <i class="fas fa-user text-purple-600"></i>
                            <?= esc($ui['patient']) ?>
                        </h2>
                        <div class="finance-show-context-head">
                            <div class="finance-show-context-avatar">
                                <?= esc(ui_initials($patient['first_name'] ?? '', $patient['last_name'] ?? '', 'P')) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-bold text-gray-800">
                                    <?= esc(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?>
                                </div>
                                <div class="mt-1 text-sm text-gray-500"><?= lang('Finance.idLabel') ?>: <?= esc($patient['patient_id']) ?></div>
                            </div>
                        </div>
                        <div class="finance-show-context-actions">
                            <a href="<?= workflow_context_url('patient/' . $patient['id'], $return_to ?? null) ?>" class="finance-show-btn finance-show-btn-soft w-full">
                                <?= esc($ui['openProfile']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($check_attachment) || !empty($finance_attachment)): ?>
                    <div class="finance-show-sidebar-card finance-sidebar-inner-card finance-sidebar-inner-card--attachments text-sm">
                        <h2 class="mb-4 flex items-center gap-2 text-xl font-bold text-gray-800">
                            <i class="fas fa-paperclip text-slate-600"></i>
                            <?= $isRtl ? 'المرفقات' : 'Attachments' ?>
                        </h2>
                        <dl class="finance-show-meta-list">
                            <?php if (!empty($check_attachment)): ?>
                            <div class="finance-attachment-item space-y-2 rounded-2xl border border-slate-200 bg-white p-4">
                                <dt class="block text-sm font-medium text-gray-600"><?= lang('Finance.checkScanLabel') ?></dt>
                                <div class="finance-show-attachment-actions">
                                    <a class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-download"></i><?= lang('Finance.download') ?>
                                    </a>
                                    <a class="inline-flex items-center gap-2 px-3 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-eye"></i><?= lang('Finance.view') ?>
                                    </a>
                                </div>
                                <?php $isImage = !empty($check_attachment['mime_type']) && strpos($check_attachment['mime_type'], 'image/') === 0; ?>
                                <?php if ($isImage): ?>
                                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2">
                                        <img src="<?= base_url('file/download/' . $check_attachment['id']) ?>" alt="<?= esc(lang('Finance.checkScanPreview')) ?>" class="max-h-56 w-auto rounded-lg">
                                    </div>
                                <?php else: ?>
                                    <p class="text-xs text-gray-500"><?= lang('Finance.imagePreviewOnly') ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($finance_attachment)): ?>
                            <div class="finance-attachment-item space-y-2 rounded-2xl border border-slate-200 bg-white p-4">
                                <dt class="block text-sm font-medium text-gray-600"><?= lang('Finance.attachmentLabel') ?></dt>
                                <div class="finance-show-attachment-actions">
                                    <a class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors" href="<?= base_url('file/download/' . $finance_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-download"></i><?= lang('Finance.download') ?>
                                    </a>
                                    <a class="inline-flex items-center gap-2 px-3 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors" href="<?= base_url('file/download/' . $finance_attachment['id']) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-eye"></i><?= lang('Finance.view') ?>
                                    </a>
                                    <span class="text-xs text-gray-500"><?= esc($finance_attachment['original_name'] ?? '') ?></span>
                                </div>
                                <?php $isFinanceAttachmentImage = !empty($finance_attachment['mime_type']) && strpos($finance_attachment['mime_type'], 'image/') === 0; ?>
                                <?php if ($isFinanceAttachmentImage): ?>
                                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2">
                                        <img src="<?= base_url('file/download/' . $finance_attachment['id']) ?>" alt="<?= esc(lang('Finance.attachmentPreview')) ?>" class="max-h-56 w-auto rounded-lg">
                                    </div>
                                <?php else: ?>
                                    <p class="text-xs text-gray-500"><?= lang('Finance.imagePreviewOnly') ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <?php endif; ?>

                    <?php if ($examination): ?>
                    <div class="finance-show-sidebar-card finance-sidebar-inner-card finance-sidebar-inner-card--clinical">
                        <h2 class="mb-4 flex items-center gap-2 text-xl font-bold text-gray-800">
                            <i class="fas fa-stethoscope text-emerald-600"></i>
                            <?= lang('Finance.clinicalLink') ?>
                        </h2>
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 shadow-inner">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-800">
                                    <?= formatDate($examination['examination_date']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
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
            <div class="finance-show-card-header">
                <div class="flex items-center gap-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= esc($ui['history']) ?></h3>
                        <p class="text-xs text-gray-500"><?= esc($ui['historyHelp']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200">
                        <?= count($payments ?? []) ?> <?= lang('Finance.settlement') ?>
                    </span>
                    <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-file-pdf text-red-600"></i>
                        <?= esc($ui['exportPdf']) ?>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="dataTables-wrapper">
                <table class="finance-show-table min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-green-600"></i>
                                    <span><?= lang('Finance.date') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-dot text-green-600"></i>
                                    <span><?= esc($ui['type']) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-credit-card text-green-600"></i>
                                    <span><?= lang('Finance.method') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-doctor text-green-600"></i>
                                    <span><?= lang('Finance.recordedBy') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-coins text-green-600"></i>
                                    <span><?= esc($ui['enteredAmount']) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-end text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end gap-2">
                                    <i class="fas fa-wallet text-green-600"></i>
                                    <span><?= esc($ui['baseAmount']) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-note-sticky text-green-600"></i>
                                    <span><?= lang('Finance.notes') ?></span>
                                </div>
                            </th>
                            <?php if (has_permission('finance', 'edit')): ?>
                            <th class="px-6 py-4 text-end text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end gap-2">
                                    <i class="fas fa-cogs text-green-600"></i>
                                    <span><?= lang('Finance.actions') ?></span>
                                </div>
                            </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
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
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    <?= formatDate($payment['payment_date']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $typeClass ?>">
                                        <?= lang('Finance.' . $type) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= lang('Finance.' . $payment['payment_method']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php $paymentCreator = trim(($payment['creator_first_name'] ?? '') . ' ' . ($payment['creator_last_name'] ?? '')); ?>
                                    <?= $paymentCreator !== '' ? 'Dr. ' . esc($paymentCreator) : lang('Finance.unknown') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php
                                        $paymentCurrency = $payment['currency'] ?? $baseCurrency;
                                        $paymentExchangeRate = floatval($payment['exchange_rate'] ?? 1);
                                        $paymentOriginalAmount = isset($payment['amount_original']) ? floatval($payment['amount_original']) : floatval($payment['amount']);
                                    ?>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-800">
                                            <?= $prefix . number_format($paymentOriginalAmount, 2) ?> <?= esc($paymentCurrency) ?>
                                        </span>
                                        <?php if ($paymentCurrency !== $baseCurrency || abs($paymentExchangeRate - 1) > 0.0001): ?>
                                            <span class="text-xs text-gray-400">
                                                <?= lang('Finance.rateShort') ?> <?= rtrim(rtrim(number_format($paymentExchangeRate, 6), '0'), '.') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-end <?= $amountClass ?>">
                                    <?= $prefix . formatCurrency($payment['amount']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs italic">
                                    <?php if (!empty($payment['notes'])): ?>
                                        <?= esc($payment['notes']) ?>
                                    <?php else: ?>
                                        <span class="text-slate-300">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (has_permission('finance', 'edit')): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    <?php if ($type === 'payment'): ?>
                                        <button type="button" onclick="voidPayment(<?= (int) $payment['id'] ?>)" class="text-gray-400 hover:text-rose-600 transition-colors" title="<?= esc(lang('Finance.voidPayment')) ?>">
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
                                <td colspan="<?= $emptyColspan ?>" class="px-6 py-12 text-center text-sm text-gray-400 font-medium">
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
                        <h4 id="paymentModalTitle" class="text-base font-semibold text-slate-900"><?= esc($ui['recordPayment']) ?></h4>
                        <p class="mt-1 text-sm text-slate-500"><?= esc($ui['paymentNotice']) ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeFinanceModal('paymentModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="invoicePaymentForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-emerald-600"></i>
                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
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
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-chart-line text-indigo-600"></i>
                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="group/field relative md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-scale-balanced text-slate-600"></i>
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
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-credit-card text-blue-600"></i>
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
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <?php if ($isExpense): ?>
                        <div class="group/field relative hidden" id="settlementLiquidityAccountField">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-vault text-indigo-600"></i>
                                <span><?= lang('Finance.paidFromAccount') ?></span>
                            </label>
                            <div class="relative">
                                <select id="settlementLiquidityAccount" name="liquidity_account" class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                                        <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.paidFromAccountHelp') ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-rose-600"></i>
                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div class="relative">
                                <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="group/field relative">
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-purple-600"></i>
                            <span><?= lang('Finance.notes') ?></span>
                        </label>
                        <div class="relative">
                            <textarea name="notes" rows="2"
                                      class="finance-show-textarea resize-none"
                                      placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                    </div>
                    <div class="finance-inline-modal-actions">
                        <button type="button" onclick="closeFinanceModal('paymentModal')" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
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
                        <h4 id="refundModalTitle" class="text-base font-semibold text-slate-900"><?= esc($ui['recordRefund']) ?></h4>
                        <p class="mt-1 text-sm text-slate-500"><?= esc($ui['refundNotice']) ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeFinanceModal('refundModal')">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <form id="invoiceRefundForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-rose-600"></i>
                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
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
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-chart-line text-indigo-600"></i>
                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="group/field relative md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-scale-balanced text-slate-600"></i>
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
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-credit-card text-blue-600"></i>
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
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-amber-600"></i>
                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div class="relative">
                                <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="group/field relative">
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
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
                        <button type="button" onclick="closeFinanceModal('refundModal')" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
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
        <?php if (floatval($providerPayout['provider_balance_due'] ?? 0) <= 0): ?>
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
                            <h4 id="<?= esc($providerPayoutModalId) ?>Title" class="text-base font-semibold text-slate-900"><?= esc($providerPayoutUi['action']) ?></h4>
                            <p class="mt-1 text-sm text-slate-500"><?= esc($providerPayoutUi['notice']) ?></p>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <form class="finance-inline-modal-form provider-payout-payment-form" data-url="<?= esc(base_url('finance/payouts/' . (int) $providerPayout['id'] . '/payments')) ?>" data-finance-modal-close="<?= esc($providerPayoutModalId) ?>">
                        <?= csrf_field() ?>
                        <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-emerald-50 p-4 shadow-sm">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-indigo-700"><?= esc($providerPayoutUi['kicker']) ?></p>
                                    <h5 class="mt-1 truncate text-base font-bold text-slate-900"><?= esc($providerDisplayName) ?></h5>
                                    <p class="mt-1 text-xs text-slate-500">
                                        <?= esc(lang('Finance.financeTransaction')) ?>:
                                        <span class="font-semibold text-slate-700"><?= esc($finance['transaction_id'] ?? ('#' . ($finance['id'] ?? ''))) ?></span>
                                    </p>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center md:min-w-[22rem]">
                                    <div class="rounded-xl border border-white/80 bg-white/80 px-3 py-2 shadow-sm">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500"><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                        <p class="mt-1 text-sm font-bold text-indigo-700" dir="ltr"><?= formatCurrency($providerPayout['provider_share_amount'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-xl border border-white/80 bg-white/80 px-3 py-2 shadow-sm">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500"><?= esc($providerPayoutUi['paid']) ?></p>
                                        <p class="mt-1 text-sm font-bold text-emerald-700" dir="ltr"><?= formatCurrency($providerPayout['provider_paid_amount'] ?? 0) ?></p>
                                    </div>
                                    <div class="rounded-xl border border-white/80 bg-white/80 px-3 py-2 shadow-sm">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500"><?= esc($providerPayoutUi['left']) ?></p>
                                        <p class="mt-1 text-sm font-bold text-rose-700" dir="ltr"><?= formatCurrency($providerBalanceDue) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                            <div class="group/field relative">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
                                    <i class="fas fa-dollar-sign text-emerald-600"></i>
                                    <span><?= esc($ui['amountRequired']) ?></span>
                                </label>
                                <input type="number" name="amount" step="0.01" min="0.01" max="<?= esc($providerPayoutAmountValue) ?>" value="<?= esc($providerPayoutAmountValue) ?>" class="finance-show-input" placeholder="0.00" required>
                            </div>
                            <div class="group/field relative">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
                                    <i class="fas fa-credit-card text-blue-600"></i>
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
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="group/field relative">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
                                    <i class="fas fa-calendar-alt text-rose-600"></i>
                                    <span><?= lang('Finance.dateRequired') ?></span>
                                </label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="finance-show-input">
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid">
                            <div class="provider-payout-liquidity-account-field group/field relative hidden">
                                <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
                                    <i class="fas fa-vault text-indigo-600"></i>
                                    <span><?= lang('Finance.paidFromAccount') ?></span>
                                </label>
                                <div class="relative">
                                    <select name="liquidity_account" class="finance-show-select finance-modal-select appearance-none" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                        <option value="bank"><?= esc(($check_outgoing_liquidity_options ?? [])['bank'] ?? 'Bank') ?></option>
                                        <option value="undeposited_checks"><?= esc(($check_outgoing_liquidity_options ?? [])['undeposited_checks'] ?? 'Checks on Hand') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
                            </div>
                        </div>
                        <div class="group/field relative">
                            <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
                                <i class="fas fa-note-sticky text-amber-600"></i>
                                <span><?= esc($providerPayoutUi['historyNotes']) ?></span>
                            </label>
                            <textarea name="notes" rows="2" class="finance-show-input min-h-[5rem]" placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                        <div class="finance-inline-modal-actions">
                            <button type="button" onclick="closeFinanceModal('<?= esc($providerPayoutModalId) ?>')" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
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
<?= $this->endSection() ?>

