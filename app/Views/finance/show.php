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
        'paid' => ['pill' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
        'partial' => ['pill' => 'bg-blue-100 text-blue-700', 'dot' => 'bg-blue-500', 'text' => 'text-blue-700'],
        'pending' => ['pill' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500', 'text' => 'text-amber-700'],
        'overdue' => ['pill' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500', 'text' => 'text-rose-700'],
        default => ['pill' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-400', 'text' => 'text-gray-700'],
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
?>
<style>
    .finance-show-shell {
        min-height: 100vh;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 24%),
            radial-gradient(circle at top left, rgba(16, 185, 129, 0.07), transparent 20%),
            linear-gradient(180deg, #f3f7fb 0%, #f8fbff 44%, #f6f8fc 100%);
    }

    .finance-show-card {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        border: 1px solid #dce6f2;
        border-radius: 16px;
        box-shadow: 0 14px 28px -20px rgba(15, 23, 42, 0.26), 0 10px 18px -18px rgba(59, 130, 246, 0.22);
    }

    .finance-show-card-hero {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border-color: #dbe7f5;
    }

    .finance-show-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 24px;
        border-bottom: 1px solid #e4edf7;
        background: linear-gradient(180deg, #f8fbff 0%, #f2f7fd 100%);
    }

    .finance-show-card-body {
        padding: 24px;
    }

    .finance-show-page-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
    }

    .finance-show-main {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .finance-show-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .finance-show-sidebar-stack {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .finance-show-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }

    .finance-show-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
    }

    .finance-show-hero-summary {
        border: 1px solid #dbe7f5;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f3f8ff 100%);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 18px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .finance-show-info-card {
        border: 1px solid #dce6f2;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 14px 16px;
    }

    .finance-show-hero .grid .finance-show-info-card:nth-child(4n + 1) {
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }

    .finance-show-hero .grid .finance-show-info-card:nth-child(4n + 2) {
        background: linear-gradient(180deg, #ffffff 0%, #f8fcfa 100%);
    }

    .finance-show-hero .grid .finance-show-info-card:nth-child(4n + 3) {
        background: linear-gradient(180deg, #ffffff 0%, #fbfaff 100%);
    }

    .finance-show-hero .grid .finance-show-info-card:nth-child(4n + 4) {
        background: linear-gradient(180deg, #ffffff 0%, #fffaf4 100%);
    }

    .finance-show-clamp {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .finance-show-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
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

    .finance-show-main > .grid:first-child .finance-show-kpi:nth-child(4n + 1) {
        background: linear-gradient(180deg, #ffffff 0%, #f3f8ff 100%);
    }

    .finance-show-main > .grid:first-child .finance-show-kpi:nth-child(4n + 2) {
        background: linear-gradient(180deg, #ffffff 0%, #f7fcf9 100%);
    }

    .finance-show-main > .grid:first-child .finance-show-kpi:nth-child(4n + 3) {
        background: linear-gradient(180deg, #ffffff 0%, #fffaf3 100%);
    }

    .finance-show-main > .grid:first-child .finance-show-kpi:nth-child(4n + 4) {
        background: linear-gradient(180deg, #ffffff 0%, #faf7ff 100%);
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

    .finance-show-mini-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .finance-show-mini-stat {
        border: 1px solid #dce6f2;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 12px 14px;
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

    .finance-show-sidebar-card {
        border: 1px solid #dce6f2;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 20px;
        box-shadow: 0 10px 22px -20px rgba(15, 23, 42, 0.18);
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

    @media (min-width: 1280px) {
        .finance-show-page-grid {
            grid-template-columns: minmax(0, 1.7fr) minmax(300px, 0.78fr);
            align-items: start;
        }

        .finance-show-sidebar {
            position: sticky;
            top: 88px;
        }

        .finance-show-hero {
            grid-template-columns: minmax(360px, 0.92fr) minmax(0, 1.18fr);
            align-items: stretch;
        }
    }

    @media (max-width: 767.98px) {
        .finance-show-card-header,
        .finance-show-card-body {
            padding: 18px;
        }

        .finance-show-hero-summary {
            padding: 18px;
        }

        .finance-show-table thead th,
        .finance-show-table tbody td {
            padding: 14px 16px;
        }

        .finance-show-mini-stat-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .finance-show-action-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>
<div class="finance-show-shell">
    <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <div class="mb-8 finance-show-card finance-show-card-hero p-6">
            <div class="finance-show-hero">
                <div class="space-y-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl <?= esc($typeTone['bg']) ?> text-white shadow-lg">
                            <i class="fas <?= esc($typeTone['icon']) ?> text-2xl"></i>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500"><?= esc($transactionTypeLabel) ?></p>
                                <h1 class="mt-1 text-3xl font-black text-slate-950"><?= $isExpense ? lang('Finance.expenseTransaction') : lang('Finance.financialTransaction') ?></h1>
                                <p class="mt-2 text-sm font-medium text-slate-600"><?= lang('Finance.transactionIdLabel') ?>: <span class="font-bold text-slate-900"><?= esc($finance['transaction_id']) ?></span></p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-bold <?= esc($statusTone['pill']) ?>">
                                    <span class="h-2 w-2 rounded-full <?= esc($statusTone['dot']) ?>"></span>
                                    <?= esc($paymentStatusLabel) ?>
                                </span>
                                <span class="inline-flex items-center rounded-full <?= esc($typeTone['soft']) ?> px-3 py-1 text-sm font-bold">
                                    <?= esc($isExpense ? $expenseCategoryLabel : $serviceTypeLabel) ?>
                                </span>
                                <?php if (!empty($finance['transaction_date'])): ?>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                                        <i class="fas fa-calendar-day text-slate-500"></i>
                                        <?= formatDate($finance['transaction_date']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="finance-show-toolbar">
                        <?php if (!empty($patient)): ?>
                        <a href="<?= base_url('patient/' . $patient['id']) ?>" class="finance-show-btn finance-show-btn-soft">
                            <i class="fas fa-user"></i><?= lang('Finance.backToPatient') ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('finance') ?>" class="finance-show-btn finance-show-btn-soft">
                            <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><?= lang('Finance.backToFinance') ?>
                        </a>
                        <a href="<?= base_url('finance/' . $finance['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="finance-show-btn finance-show-btn-primary">
                            <i class="fas fa-pen"></i><?= lang('Finance.editTransaction') ?>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:max-w-3xl">
                        <div class="finance-show-info-card">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><?= $relatedPartyTitle ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= esc($relatedPartyValue !== '' ? $relatedPartyValue : lang('Finance.unknown')) ?></p>
                            <p class="mt-1 text-xs text-slate-500"><?= esc($relatedPartyMeta) ?></p>
                        </div>
                        <div class="finance-show-info-card">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><?= lang('Finance.standardMethod') ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= lang('Finance.' . $finance['payment_method']) ?></p>
                            <p class="mt-1 text-xs text-slate-500">
                                <?php if (!empty($finance['liquidity_account'])): ?>
                                    <?= esc(($liquidity_account_options ?? [])[$finance['liquidity_account']] ?? $finance['liquidity_account']) ?>
                                <?php else: ?>
                                    <?= esc($baseCurrency) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="finance-show-info-card">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><?= lang('Finance.createdByLabel') ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?></p>
                            <p class="mt-1 text-xs text-slate-500">
                                <?= !empty($finance['due_date']) ? lang('Finance.dueDate') . ': ' . formatDate($finance['due_date']) : (!empty($finance['transaction_date']) ? formatDate($finance['transaction_date']) : lang('Finance.unknown')) ?>
                            </p>
                        </div>
                        <div class="finance-show-info-card">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><?= $isExpense ? lang('Finance.expenseDescription') : lang('Finance.serviceDescription') ?></p>
                            <p class="mt-2 text-sm font-semibold text-slate-700 finance-show-clamp">
                                <?= esc($descriptionPreview !== '' ? $descriptionPreview : lang('Finance.noDescriptionProvided')) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="finance-show-hero-summary">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500"><?= esc($settlementCardTitle) ?></p>
                            <p class="mt-2 text-4xl font-black text-slate-950"><?= number_format($totalAmount, 2) ?></p>
                            <p class="mt-1 text-sm font-semibold text-slate-500"><?= esc($baseCurrency) ?></p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-bold <?= esc($statusTone['pill']) ?>">
                            <span class="h-2 w-2 rounded-full <?= esc($statusTone['dot']) ?>"></span>
                            <?= esc($paymentStatusLabel) ?>
                        </span>
                    </div>
                    <?php if ($showSettlementSummary): ?>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="font-semibold text-slate-600"><?= lang('Finance.paidToDate') ?></span>
                                <span class="font-bold text-slate-900"><?= formatCurrency($paidSoFar) ?></span>
                            </div>
                            <div class="finance-show-progress">
                                <span style="width: <?= esc((string) $settlementProgress) ?>%;"></span>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="font-semibold text-slate-600"><?= lang('Finance.balanceDue') ?></span>
                                <span class="font-bold <?= $balanceDue > 0 ? 'text-rose-700' : 'text-emerald-700' ?>"><?= formatCurrency($balanceDue) ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500"><?= lang('Finance.transactionType') ?></p>
                            <p class="mt-2 text-lg font-black text-slate-950"><?= esc($transactionTypeLabel) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="finance-show-mini-stat-grid">
                        <div class="finance-show-mini-stat">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><?= $relatedPartyTitle ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= esc($relatedPartyValue !== '' ? $relatedPartyValue : lang('Finance.unknown')) ?></p>
                            <p class="mt-1 text-xs text-slate-500"><?= esc($relatedPartyMeta) ?></p>
                        </div>
                        <div class="finance-show-mini-stat">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500"><?= lang('Finance.createdByLabel') ?></p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?></p>
                            <p class="mt-1 text-xs text-slate-500"><?= !empty($finance['transaction_date']) ? formatDate($finance['transaction_date']) : lang('Finance.unknown') ?></p>
                        </div>
                    </div>

                    <div class="space-y-3 border-t border-slate-200 pt-4">
                        <?php if ($finance['payment_status'] != 'paid' && $showSettlementSummary && floatval($finance['balance_due'] ?? 0) > 0): ?>
                            <button type="button" onclick="document.getElementById('invoicePaymentForm')?.scrollIntoView({behavior:'smooth', block:'start'})" class="finance-show-btn finance-show-btn-success w-full">
                                <i class="fas fa-plus-circle"></i><?= esc($recordPaymentTitle) ?>
                            </button>
                        <?php endif; ?>
                        <div class="finance-show-action-grid">
                            <?php if ($finance['payment_status'] != 'paid'): ?>
                                <button onclick="markAsPaid(<?= $finance['id'] ?>)" class="finance-show-btn finance-show-btn-soft w-full">
                                    <i class="fas fa-check-circle text-emerald-600"></i><?= esc($markFullyPaidLabel) ?>
                                </button>
                            <?php endif; ?>
                            <?php if (($finance['transaction_type'] ?? '') === 'invoice' && floatval($finance['balance_due'] ?? 0) > 0): ?>
                                <button onclick="closeBalance(<?= (int) $finance['id'] ?>)" class="finance-show-btn finance-show-btn-soft w-full">
                                    <i class="fas fa-scale-balanced text-amber-600"></i><?= lang('Finance.closeBalanceCredit') ?>
                                </button>
                            <?php endif; ?>
                            <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="finance-show-btn finance-show-btn-soft w-full">
                                <i class="fas fa-file-invoice text-blue-600"></i><?= lang('Finance.exportInvoice') ?>
                            </a>
                            <?php if (has_permission('finance', 'delete')): ?>
                                <button onclick="deleteFinance(<?= $finance['id'] ?>)" class="finance-show-btn finance-show-btn-soft w-full text-rose-700 border-rose-200 hover:bg-rose-50 hover:border-rose-300">
                                    <i class="fas fa-trash-alt text-rose-600"></i><?= esc($deleteLedgerLabel) ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="finance-show-page-grid">
            <div class="finance-show-main">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <div class="finance-show-kpi">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="finance-show-kpi-label"><?= lang('Finance.totalAmount') ?></span>
                            <i class="fas fa-wallet text-slate-400"></i>
                        </div>
                        <div class="text-3xl font-black text-slate-950">
                            <?= number_format($totalAmount, 2) ?> <span class="text-sm font-semibold text-slate-500"><?= esc($baseCurrency) ?></span>
                        </div>
                    </div>

                    <?php if ($showSettlementSummary): ?>
                        <div class="finance-show-kpi">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="finance-show-kpi-label"><?= lang('Finance.paidToDate') ?></span>
                                <i class="fas fa-check-circle text-emerald-500"></i>
                            </div>
                            <div class="text-3xl font-black text-emerald-700">
                                <?= number_format($paidSoFar, 2) ?> <span class="text-sm font-semibold text-slate-500"><?= esc($baseCurrency) ?></span>
                            </div>
                        </div>

                        <div class="finance-show-kpi">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="finance-show-kpi-label"><?= lang('Finance.balanceDue') ?></span>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-bold <?= esc($statusTone['pill']) ?>">
                                    <span class="h-2 w-2 rounded-full <?= esc($statusTone['dot']) ?>"></span>
                                    <?= esc($paymentStatusLabel) ?>
                                </span>
                            </div>
                        <div class="text-3xl font-black <?= $balanceDue > 0 ? 'text-rose-700' : 'text-slate-950' ?>">
                            <?= number_format($balanceDue, 2) ?> <span class="text-sm font-semibold text-slate-500"><?= esc($baseCurrency) ?></span>
                        </div>
                        <p class="mt-3 text-sm text-slate-500"><?= $balanceDue > 0 ? lang('Finance.settlementOverviewHelp') : lang('Finance.paidToDate') ?></p>
                    </div>
                    <?php else: ?>
                        <div class="finance-show-kpi">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="finance-show-kpi-label"><?= lang('Finance.transactionType') ?></span>
                                <i class="fas fa-exchange-alt text-slate-400"></i>
                            </div>
                            <div class="text-xl font-black text-slate-950"><?= esc($transactionTypeLabel) ?></div>
                        </div>

                        <div class="finance-show-kpi">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="finance-show-kpi-label"><?= lang('Finance.paymentStatusUpper') ?></span>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-bold <?= esc($statusTone['pill']) ?>">
                                    <span class="h-2 w-2 rounded-full <?= esc($statusTone['dot']) ?>"></span>
                                    <?= esc($paymentStatusLabel) ?>
                                </span>
                            </div>
                        <div class="text-xl font-black <?= esc($statusTone['text']) ?>"><?= esc($paymentStatusLabel) ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="finance-show-kpi">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="finance-show-kpi-label"><?= lang('Finance.standardMethod') ?></span>
                            <i class="fas fa-credit-card text-slate-400"></i>
                        </div>
                        <div class="text-xl font-black text-slate-950"><?= lang('Finance.' . $finance['payment_method']) ?></div>
                        <p class="mt-3 text-sm text-slate-500">
                            <?php if (!empty($finance['liquidity_account'])): ?>
                                <?= esc(($liquidity_account_options ?? [])[$finance['liquidity_account']] ?? $finance['liquidity_account']) ?>
                            <?php else: ?>
                                <?= esc($baseCurrency) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <?php if ($showSettlementSummary): ?>
                <div class="finance-show-card px-6 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900"><?= esc($settlementCardTitle) ?></h2>
                            <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.settlementOverviewHelp') ?></p>
                        </div>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= lang('Finance.totalAmount') ?></p>
                                <p class="mt-1 font-bold text-gray-900"><?= formatCurrency($totalAmount) ?></p>
                            </div>
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= lang('Finance.paidToDate') ?></p>
                                <p class="mt-1 font-bold text-emerald-800"><?= formatCurrency($paidSoFar) ?></p>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-rose-700"><?= lang('Finance.balanceDue') ?></p>
                                <p class="mt-1 font-bold text-rose-800"><?= formatCurrency($balanceDue) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Financial Details & Breakdown -->
                <div class="finance-show-card overflow-hidden">
                    <div class="finance-show-card-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <i class="fas fa-list-ul"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= lang('Finance.detailsBreakdown') ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= esc($isExpense ? $expenseCategoryLabel : $serviceTypeLabel) ?>
                        </span>
                    </div>
                    <div class="finance-show-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Description -->
                            <div class="space-y-6">
                                <div>
                                    <label class="finance-show-field-label"><?= $isExpense ? lang('Finance.expenseDescription') : lang('Finance.serviceDescription') ?></label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= !empty($finance['description']) ? nl2br(htmlspecialchars($finance['description'])) : '<span class="text-gray-400 italic">' . esc(lang('Finance.noDescriptionProvided')) . '</span>' ?>
                                    </div>
                                </div>
                                <?php if ($isExpense && !empty($finance['counterparty_name'])): ?>
                                <div>
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
                                <div>
                                    <label class="finance-show-field-label"><?= lang('Finance.technicalDetails') ?></label>
                                    <div class="text-gray-800 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($finance['service_details'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <label class="finance-show-field-label"><?= lang('Finance.createdByLabel') ?></label>
                                    <div class="text-gray-800 leading-relaxed font-semibold">
                                        <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div class="finance-show-muted-box shadow-inner">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 font-medium"><?= lang('Finance.subtotal') ?></span>
                                        <span class="font-bold text-gray-900"><?= number_format($finance['amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php if ($isConverted): ?>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span class="font-medium"><?= lang('Finance.original') ?></span>
                                        <span class="font-semibold">
                                            <?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?>
                                            @ <?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['discount_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-rose-600">
                                        <span class="font-medium"><?= lang('Finance.discount') ?></span>
                                        <span class="font-bold">-<?= number_format($finance['discount_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($finance['tax_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-sm text-gray-600">
                                        <span class="font-medium"><?= lang('Finance.taxPercent') ?> (<?= number_format($taxPercent, 2) ?>%)</span>
                                        <span class="font-bold">+<?= number_format($finance['tax_amount'], 2) ?> <?= esc($baseCurrency) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-200 pt-4 mt-4 flex justify-between items-center">
                                        <span class="text-lg font-bold text-gray-800"><?= lang('Finance.total') ?></span>
                                        <span class="text-2xl font-bold text-gray-900">
                                            <?= number_format($totalAmount, 2) ?> <span class="text-sm font-normal text-gray-500"><?= esc($baseCurrency) ?></span>
                                        </span>
                                    </div>
                                    
                                    <?php if ($showSettlementSummary): ?>
                                        <div class="pt-4 mt-4 border-t border-gray-200 border-dashed space-y-3">
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-emerald-600 font-bold"><?= lang('Finance.paidToDate') ?></span>
                                                <span class="text-emerald-700 font-bold"><?= number_format($paidSoFar, 2) ?> <?= esc($baseCurrency) ?></span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm bg-rose-50 p-3 rounded-lg border border-rose-100 shadow-sm">
                                                <span class="text-rose-600 font-bold uppercase tracking-wider"><?= lang('Finance.balanceDue') ?></span>
                                                <span class="text-rose-700 text-xl font-bold"><?= number_format($balanceDue, 2) ?> <?= esc($baseCurrency) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($provider_payouts)): ?>
                <div class="finance-show-card overflow-hidden">
                    <div class="finance-show-card-header">
                        <div class="finance-show-section-title">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= lang('Finance.providerSplit') ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?= ($provider_payout_summary['provider_balance_due'] ?? 0) > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                            <?= ($provider_payout_summary['provider_balance_due'] ?? 0) > 0 ? esc(lang('Finance.partial')) : esc(lang('Finance.paid')) ?>
                        </span>
                    </div>
                    <div class="finance-show-card-body space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.grossCollected') ?></p>
                                <p class="mt-1 font-bold text-gray-900"><?= formatCurrency($provider_payout_summary['gross_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.totalProviderCompensation') ?></p>
                                <p class="mt-1 font-bold text-indigo-700"><?= formatCurrency($provider_payout_summary['provider_share_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.paidToProvider') ?></p>
                                <p class="mt-1 font-bold text-emerald-700"><?= formatCurrency($provider_payout_summary['provider_paid_amount'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= lang('Finance.balanceDue') ?></p>
                                <p class="mt-1 font-bold text-rose-700"><?= formatCurrency($provider_payout_summary['provider_balance_due'] ?? 0) ?></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <?php foreach ($provider_payouts as $providerPayout): ?>
                                <?php
                                    $providerDisplayName = trim(($providerPayout['provider_first_name'] ?? '') . ' ' . ($providerPayout['provider_last_name'] ?? ''));
                                    if ($providerDisplayName === '') {
                                        $providerDisplayName = $providerPayout['provider_name_snapshot'] ?? lang('Finance.guestProvider');
                                    }
                                    $providerStatusKey = $providerPayout['status'] ?? 'unpaid';
                                    $providerStatusLabel = lang('Finance.' . $providerStatusKey);
                                    if ($providerStatusLabel === 'Finance.' . $providerStatusKey) {
                                        $providerStatusLabel = ucfirst($providerStatusKey);
                                    }
                                    $providerStatusClass = $providerStatusKey === 'paid'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : ($providerStatusKey === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700');
                                ?>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-base font-bold text-slate-900"><?= esc($providerDisplayName) ?></h3>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold <?= $providerStatusClass ?>">
                                                    <?= esc($providerStatusLabel) ?>
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-500">
                                                <?= lang('Finance.splitRule') ?>:
                                                <?= esc(lang('Finance.' . ($providerPayout['split_mode'] ?? 'percentage'))) ?>
                                                <?= esc($providerPayout['split_value'] ?? 0) ?><?= ($providerPayout['split_mode'] ?? '') === 'percent' ? '%' : '' ?>
                                            </p>
                                            <?php if (!empty($providerPayout['notes'])): ?>
                                                <p class="mt-2 text-sm text-slate-600"><?= esc($providerPayout['notes']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:min-w-[34rem]">
                                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= lang('Finance.providerShare') ?></p>
                                                <p class="mt-1 text-sm font-bold text-indigo-700"><?= formatCurrency($providerPayout['provider_share_amount'] ?? 0) ?></p>
                                            </div>
                                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= lang('Finance.paidToProvider') ?></p>
                                                <p class="mt-1 text-sm font-bold text-emerald-700"><?= formatCurrency($providerPayout['provider_paid_amount'] ?? 0) ?></p>
                                            </div>
                                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= lang('Finance.balanceDue') ?></p>
                                                <p class="mt-1 text-sm font-bold text-rose-700"><?= formatCurrency($providerPayout['provider_balance_due'] ?? 0) ?></p>
                                            </div>
                                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= lang('Finance.clinicNet') ?></p>
                                                <p class="mt-1 text-sm font-bold text-sky-700"><?= formatCurrency($providerPayout['clinic_share_amount'] ?? 0) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (floatval($providerPayout['provider_balance_due'] ?? 0) > 0 && has_permission('finance', 'edit')): ?>
                                        <form class="provider-payout-payment-form grid grid-cols-1 md:grid-cols-5 gap-3 items-end"
                                              data-url="<?= esc(base_url('finance/payouts/' . (int) $providerPayout['id'] . '/payments')) ?>">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= lang('Finance.amount') ?></label>
                                                <input type="number" name="amount" step="0.01" min="0.01" max="<?= esc($providerPayout['provider_balance_due'] ?? 0) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= lang('Finance.method') ?></label>
                                                <select name="payment_method" class="provider-payout-method-input w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                    <option value="cash"><?= lang('Finance.cash') ?></option>
                                                    <option value="card"><?= lang('Finance.card') ?></option>
                                                    <option value="bank_transfer"><?= lang('Finance.bankTransfer') ?></option>
                                                    <option value="check"><?= lang('Finance.check') ?></option>
                                                    <option value="other"><?= lang('Finance.other') ?></option>
                                                </select>
                                            </div>
                                            <div class="provider-payout-liquidity-account-field hidden">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= lang('Finance.paidFromAccount') ?></label>
                                                <select name="liquidity_account" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                    <option value="bank"><?= esc(($check_outgoing_liquidity_options ?? [])['bank'] ?? 'Bank') ?></option>
                                                    <option value="undeposited_checks"><?= esc(($check_outgoing_liquidity_options ?? [])['undeposited_checks'] ?? 'Checks on Hand') ?></option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= lang('Finance.date') ?></label>
                                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                            </div>
                                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                                <i class="fas fa-plus"></i><?= lang('Finance.recordProviderPayout') ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($isInvoice || $isExpense): ?>
                <div class="finance-show-card overflow-hidden">
                    <div class="finance-show-card-header">
                        <div class="flex items-center gap-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?= lang('Finance.settlementHistoryTitle') ?></h3>
                                <p class="text-xs text-gray-500"><?= lang('Finance.historyOfSettlements') ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200">
                                <?= count($payments ?? []) ?> <?= lang('Finance.settlement') ?>
                            </span>
                            <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-file-pdf text-red-600"></i>
                                <?= lang('Finance.exportInvoice') ?>
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
                                            <span><?= lang('Finance.status') ?></span>
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
                                            <span><?= lang('Finance.settlement') ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-end text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <div class="flex items-center justify-end gap-2">
                                            <i class="fas fa-wallet text-green-600"></i>
                                            <span><?= lang('Finance.amountBase') ?></span>
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

                <?php if (has_permission('finance', 'edit')): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Record Payment Form -->
                    <div class="finance-show-card overflow-hidden">
                        <div class="finance-show-card-header">
                            <div class="finance-show-section-title">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <i class="fas fa-plus-circle text-sm"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= esc($recordPaymentTitle) ?></h2>
                            </div>
                        </div>
                        <div class="finance-show-card-body">
                            <form id="invoicePaymentForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                                    <?= $supportsPaymentExchangeFields ? lang('Finance.settlementCurrencyNotice') : lang('Finance.settlementBaseCurrencyNotice') ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                            <i class="fas fa-dollar-sign text-emerald-600"></i>
                                            <span><?= lang('Finance.amountRequired') ?></span>
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
                                            <select name="currency" data-settlement-currency class="finance-show-select appearance-none">
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
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                            <span><?= lang('Finance.methodRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <select name="payment_method" class="finance-show-select appearance-none">
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
                                            <select id="settlementLiquidityAccount" name="liquidity_account" class="finance-show-select appearance-none">
                                                <option value="bank"><?= esc(($check_outgoing_liquidity_options ?? [])['bank'] ?? 'Bank') ?></option>
                                                <option value="undeposited_checks"><?= esc(($check_outgoing_liquidity_options ?? [])['undeposited_checks'] ?? 'Checks on Hand') ?></option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
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
                                                  placeholder="<?= esc(lang('Finance.optionalNotesPlaceholder')) ?>"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="finance-show-btn finance-show-btn-success w-full px-8 py-4 text-sm">
                                    <i class="fas fa-check-double"></i><?= lang('Finance.postSettlement') ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($isInvoice): ?>
                    <!-- Record Refund Form -->
                    <div class="finance-show-card overflow-hidden">
                        <div class="finance-show-card-header">
                            <div class="finance-show-section-title">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                                <i class="fas fa-undo-alt text-sm"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= lang('Finance.recordRefund') ?></h2>
                            </div>
                        </div>
                        <div class="finance-show-card-body">
                            <form id="invoiceRefundForm" class="space-y-6">
                                <?= csrf_field() ?>
                                <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                                    <?= $supportsPaymentExchangeFields ? lang('Finance.settlementCurrencyNotice') : lang('Finance.settlementBaseCurrencyNotice') ?> <span class="font-semibold"><?= esc($baseCurrency) ?></span>.
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                            <i class="fas fa-dollar-sign text-rose-600"></i>
                                            <span><?= lang('Finance.amountRequired') ?></span>
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
                                            <select name="currency" data-settlement-currency class="finance-show-select appearance-none">
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
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                            <span><?= lang('Finance.methodRequired') ?></span>
                                        </label>
                                        <div class="relative">
                                            <select name="payment_method" class="finance-show-select appearance-none">
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
                                        <span><?= lang('Finance.reason') ?></span>
                                    </label>
                                    <div class="relative">
                                        <textarea name="notes" rows="2"
                                                  class="finance-show-textarea resize-none"
                                                  placeholder="<?= esc(lang('Finance.refundReasonPlaceholder')) ?>"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="finance-show-btn finance-show-btn-danger w-full px-8 py-4 text-sm">
                                    <i class="fas fa-reply-all"></i><?= lang('Finance.processReversal') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($finance['notes'])): ?>
                <!-- Additional Notes -->
                <div class="finance-show-card p-6">
                    <h2 class="mb-4 flex items-center gap-3 text-xl font-bold text-gray-800">
                        <i class="fas fa-sticky-note text-yellow-600"></i><?= lang('Finance.administrativeNotes') ?>
                    </h2>
                    <div class="text-gray-700 leading-relaxed bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                        <?= nl2br(htmlspecialchars($finance['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <aside class="finance-show-sidebar">
                <div class="finance-show-sidebar-stack">
                <?php if ($patient): ?>
                <div class="finance-show-sidebar-card">
                    <h2 class="mb-4 flex items-center gap-2 text-xl font-bold text-gray-800">
                        <i class="fas fa-user text-purple-600"></i>
                        <?= lang('Finance.patientInformation') ?>
                    </h2>
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-2xl font-bold text-white shadow-lg">
                            <?= esc(ui_initials($patient['first_name'] ?? '', $patient['last_name'] ?? '', 'P')) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-gray-800">
                                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?>
                            </div>
                            <div class="text-sm text-gray-500"><?= lang('Finance.idLabel') ?>: <?= $patient['patient_id'] ?></div>
                        </div>
                    </div>
                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="finance-show-btn finance-show-btn-soft w-full">
                        <?= lang('Finance.viewProfile') ?>
                    </a>
                </div>
                <?php endif; ?>

                <div class="finance-show-sidebar-card text-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-xl font-bold text-gray-800">
                        <i class="fas fa-info-circle text-gray-600"></i>
                        <?= lang('Finance.metadata') ?>
                    </h2>
                    <dl class="finance-show-meta-list">
                        <div class="finance-show-meta-row">
                            <dt><?= lang('Finance.standardMethod') ?></dt>
                            <dd><?= lang('Finance.' . $finance['payment_method']) ?></dd>
                        </div>
                        <?php if (!empty($finance['liquidity_account'])): ?>
                        <div class="finance-show-meta-row">
                            <dt><?= lang('Finance.liquidityAccount') ?></dt>
                            <dd><?= esc(($liquidity_account_options ?? [])[$finance['liquidity_account']] ?? $finance['liquidity_account']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <div class="finance-show-meta-row">
                            <dt><?= lang('Finance.currency') ?></dt>
                            <dd>
                                <?= esc($baseCurrency) ?>
                                <?php if ($isConverted): ?>
                                    <span class="text-xs text-gray-500"><?= lang('Finance.fromCurrency') ?> <?= esc($finance['currency']) ?></span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if ($isConverted): ?>
                        <div class="finance-show-meta-row">
                            <dt><?= lang('Finance.exchangeRate') ?></dt>
                            <dd><?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($check_attachment)): ?>
                        <div class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4">
                            <dt class="block text-sm font-medium text-gray-600"><?= lang('Finance.checkScanLabel') ?></dt>
                            <div class="flex flex-wrap items-center gap-3">
                                <a class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-download"></i><?= lang('Finance.download') ?>
                                </a>
                                <a class="inline-flex items-center gap-2 px-3 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-eye"></i><?= lang('Finance.view') ?>
                                </a>
                            </div>
                            <?php
                                $isImage = !empty($check_attachment['mime_type']) && strpos($check_attachment['mime_type'], 'image/') === 0;
                            ?>
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
                        <div class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4">
                            <dt class="block text-sm font-medium text-gray-600"><?= lang('Finance.attachmentLabel') ?></dt>
                            <div class="flex flex-wrap items-center gap-3">
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
                        <?php if (!empty($finance['transaction_date'])): ?>
                        <div class="finance-show-meta-row">
                            <dt><?= lang('Finance.transactionDate') ?></dt>
                            <dd class="text-blue-600"><?= formatDate($finance['transaction_date']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($finance['due_date'])): ?>
                        <div class="finance-show-meta-row">
                            <dt><?= lang('Finance.dueDate') ?></dt>
                            <dd class="text-rose-600"><?= formatDate($finance['due_date']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($finance['paid_date'])): ?>
                        <div class="finance-show-meta-row">
                            <dt><?= lang('Finance.settlementDate') ?></dt>
                            <dd class="text-emerald-600"><?= formatDate($finance['paid_date']) ?></dd>
                        </div>
                        <?php endif; ?>
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-[10px] text-gray-400"><?= lang('Finance.lastUpdated') ?>: <?= formatDateTime($finance['updated_at']) ?></p>
                        </div>
                    </dl>
                </div>

                <?php if ($examination): ?>
                <div class="finance-show-sidebar-card">
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
                    <a href="<?= base_url('examination/' . $examination['id']) ?>" class="finance-show-btn finance-show-btn-soft w-full">
                        <?= lang('Finance.viewClinicalRecord') ?>
                    </a>
                </div>
                <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
const financeBaseCurrency = '<?= esc($baseCurrency) ?>';

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
    if (!settlementField || !settlementMethod) {
        return;
    }
    settlementField.classList.toggle('hidden', settlementMethod.value !== 'check');
}

document.querySelector('#invoicePaymentForm select[name="payment_method"]')?.addEventListener('change', syncExpenseSettlementLiquidityField);
syncExpenseSettlementLiquidityField();

document.querySelectorAll('.provider-payout-payment-form').forEach((form) => {
    const methodInput = form.querySelector('.provider-payout-method-input');
    const liquidityField = form.querySelector('.provider-payout-liquidity-account-field');

    const syncProviderPayoutLiquidityField = () => {
        if (!methodInput || !liquidityField) {
            return;
        }
        liquidityField.classList.toggle('hidden', methodInput.value !== 'check');
    };

    methodInput?.addEventListener('change', syncProviderPayoutLiquidityField);
    syncProviderPayoutLiquidityField();

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(form);
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

