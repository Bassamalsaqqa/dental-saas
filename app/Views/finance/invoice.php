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
    $paymentStatusLabel = lang('Finance.' . ($finance['payment_status'] ?? 'pending'));
    if ($paymentStatusLabel === 'Finance.' . ($finance['payment_status'] ?? 'pending')) {
        $paymentStatusLabel = ucfirst((string) ($finance['payment_status'] ?? 'pending'));
    }
    $serviceTypeLabel = lang('Finance.' . ($finance['service_type'] ?? 'other'));
    if ($serviceTypeLabel === 'Finance.' . ($finance['service_type'] ?? 'other')) {
        $serviceTypeLabel = ucfirst(str_replace('_', ' ', (string) ($finance['service_type'] ?? 'other')));
    }
    $paymentMethodLabel = lang('Finance.' . ($finance['payment_method'] ?? 'cash'));
    if ($paymentMethodLabel === 'Finance.' . ($finance['payment_method'] ?? 'cash')) {
        $paymentMethodLabel = ucfirst(str_replace('_', ' ', (string) ($finance['payment_method'] ?? 'cash')));
    }
?>
<!-- Professional Invoice Document -->
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Print Button -->
        <div class="mb-6 flex justify-end">
            <button onclick="window.print()" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-print mr-2"></i>
                <?= lang('Finance.printInvoice') ?>
            </button>
        </div>

        <!-- Invoice Document -->
        <div class="bg-white shadow-lg border border-gray-200">
            <!-- Professional Header -->
            <div class="border-b-4 border-blue-600">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-start">
                        <!-- Company Info -->
                        <div>
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-3 overflow-hidden">
                                    <?php if (!empty($clinic['logo_path'])): ?>
                                        <?php 
                                        $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0) 
                                            ? $clinic['logo_path'] 
                                            : base_url(ltrim($clinic['logo_path'], '/'));
                                        ?>
                                        <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" class="w-full h-full object-contain bg-white">
                                    <?php else: ?>
                                        <i class="fas fa-tooth text-white text-xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900"><?= esc($clinic['name']) ?></h1>
                                    <p class="text-gray-600 text-sm"><?= esc($clinic['tagline']) ?></p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p><?= esc($clinic['address']) ?></p>
                                <p><?= lang('Finance.phone') ?>: <?= esc($clinic['phone']) ?> | <?= lang('Finance.email') ?>: <?= esc($clinic['email']) ?></p>
                                <?php if ($clinic['website']): ?>
                                <p><?= lang('Finance.website') ?>: <?= esc($clinic['website']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Invoice Details -->
                        <div class="text-right">
                            <h2 class="text-3xl font-bold text-blue-600 mb-2"><?= lang('Finance.invoiceUpper') ?></h2>
                            <div class="bg-gray-50 p-3 rounded-lg border">
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-700"><?= lang('Finance.invoiceNumberLabel') ?>:</span>
                                        <span class="text-gray-900"><?= $finance['transaction_id'] ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-700"><?= lang('Finance.date') ?>:</span>
                                        <span class="text-gray-900"><?= formatDate($finance['created_at']) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-700"><?= lang('Finance.dueDate') ?>:</span>
                                        <span class="text-gray-900"><?= $finance['due_date'] ? formatDate($finance['due_date']) : lang('Finance.notAvailable') ?></span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 border-t">
                                        <span class="font-semibold text-gray-700"><?= lang('Finance.status') ?>:</span>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            <?= $finance['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                                                ($finance['payment_status'] == 'partial' ? 'bg-blue-100 text-blue-800' :
                                                ($finance['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($finance['payment_status'] == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))) ?>">
                                            <?= esc($paymentStatusLabel) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Content -->
            <div class="px-8 py-6">
                <!-- Billing Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Bill To -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b-2 border-gray-300 pb-2"><?= lang('Finance.billTo') ?></h3>
                        <div class="space-y-2">
                            <p class="text-lg font-semibold text-gray-900"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
                            <p class="text-sm text-gray-600"><?= lang('Finance.patientId') ?>: <?= $patient['patient_id'] ?></p>
                            <?php if ($patient['phone']): ?>
                            <p class="text-sm text-gray-600"><?= lang('Finance.phone') ?>: <?= $patient['phone'] ?></p>
                            <?php endif; ?>
                            <?php if ($patient['email']): ?>
                            <p class="text-sm text-gray-600"><?= lang('Finance.email') ?>: <?= $patient['email'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Transaction Details -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b-2 border-gray-300 pb-2"><?= lang('Finance.transactionDetails') ?></h3>
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.serviceTypeLabel') ?>:</span> <?= esc($serviceTypeLabel) ?></p>
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.paymentMethodLabel') ?>:</span> <?= esc($paymentMethodLabel) ?></p>
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.createdByLabel') ?>:</span> <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : lang('Finance.unknown') ?></p>
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.currency') ?>:</span> <?= esc($baseCurrency) ?></p>
                            <?php if ($isConverted): ?>
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.original') ?>:</span> <?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?></p>
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.exchangeRate') ?>:</span> <?= rtrim(rtrim(number_format($exchangeRate, 6), '0'), '.') ?></p>
                            <?php endif; ?>
                            <?php if (!empty($check_attachment)): ?>
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.checkScanLabel') ?>:</span> <a class="text-blue-600" href="<?= base_url('file/download/' . $check_attachment['id']) ?>" target="_blank" rel="noopener"><?= lang('Finance.download') ?></a></p>
                            <?php endif; ?>
                            <?php if ($examination): ?>
                            <p class="text-sm text-gray-600"><span class="font-semibold"><?= lang('Finance.relatedExaminationLabel') ?>:</span> <?= $examination['examination_id'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Service Details Table -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b-2 border-gray-300 pb-2"><?= lang('Finance.serviceDetailsUpper') ?></h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold text-gray-700"><?= lang('Finance.description') ?></th>
                                    <th class="border border-gray-300 px-4 py-3 text-center text-sm font-bold text-gray-700"><?= lang('Finance.serviceTypeLabel') ?></th>
                                    <th class="border border-gray-300 px-4 py-3 text-right text-sm font-bold text-gray-700"><?= lang('Finance.amount') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                        <?= $finance['description'] ?: lang('Finance.defaultDentalServiceDescription') ?>
                                        <?php if ($finance['service_details']): ?>
                                        <br><span class="text-xs text-gray-500 mt-1 block"><?= $finance['service_details'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3 text-center text-sm text-gray-700">
                                        <?= esc($serviceTypeLabel) ?>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                        <?= formatCurrency($finance['amount']) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Examination Details (if available) -->
                <?php if ($examination): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-stethoscope mr-2 text-blue-600"></i>
                        <?= lang('Finance.relatedExamination') ?>
                    </h3>
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <p><strong class="text-gray-700"><?= lang('Finance.examinationId') ?>:</strong> <?= $examination['id'] ?></p>
                                <p><strong class="text-gray-700"><?= lang('Finance.date') ?>:</strong> <?= formatDate($examination['created_at']) ?></p>
                            </div>
                            <div>
                                <p><strong class="text-gray-700"><?= lang('Finance.status') ?>:</strong> 
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?= esc(ucfirst((string) ($examination['status'] ?? ''))) ?>
                                    </span>
                                </p>
                                <p><strong class="text-gray-700"><?= lang('Finance.notes') ?>:</strong> <?= $examination['examination_notes'] ?: lang('Finance.notAvailable') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Summary -->
                <div class="mb-8">
                    <div class="flex justify-end">
                        <div class="w-80">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b-2 border-gray-300 pb-2"><?= lang('Finance.paymentSummary') ?></h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700"><?= lang('Finance.serviceAmountLabel') ?>:</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= formatCurrency($finance['amount']) ?></span>
                                </div>
                                <?php if ($isConverted): ?>
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700"><?= lang('Finance.originalAmount') ?>:</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= number_format($amountOriginal, 2) ?> <?= esc($finance['currency']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($finance['discount_amount'] > 0): ?>
                                <div class="flex justify-between items-center py-2 px-3 bg-green-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700"><?= lang('Finance.discountLabel') ?>:</span>
                                    <span class="text-sm font-semibold text-green-600">-<?= formatCurrency($finance['discount_amount']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($finance['tax_amount'] > 0): ?>
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700"><?= lang('Finance.taxLabel') ?> (<?= number_format($taxPercent, 2) ?>%):</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= formatCurrency($finance['tax_amount']) ?></span>
                                </div>
                                <?php endif; ?>

                                <?php if ($finance['transaction_type'] === 'invoice'): ?>
                                <div class="flex justify-between items-center py-2 px-3 bg-emerald-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700"><?= lang('Finance.amountPaidLabel') ?>:</span>
                                    <span class="text-sm font-semibold text-emerald-700"><?= formatCurrency($finance['amount_paid'] ?? 0) ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 px-3 bg-red-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700"><?= lang('Finance.balanceDueLabel') ?>:</span>
                                    <span class="text-sm font-semibold text-red-700"><?= formatCurrency($finance['balance_due'] ?? $totalAmount) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="border-t-2 border-gray-400 pt-3">
                                    <div class="flex justify-between items-center py-3 px-3 bg-blue-50 rounded-lg">
                                        <span class="text-lg font-bold text-gray-900"><?= lang('Finance.totalAmountUpper') ?>:</span>
                                        <span class="text-xl font-bold text-blue-600"><?= formatCurrency($totalAmount) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($payments)): ?>
                <!-- Payment History -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b-2 border-gray-300 pb-2"><?= lang('Finance.paymentHistory') ?></h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold text-gray-700"><?= lang('Finance.date') ?></th>
                                    <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold text-gray-700"><?= lang('Finance.type') ?></th>
                                    <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold text-gray-700"><?= lang('Finance.method') ?></th>
                                    <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold text-gray-700"><?= lang('Finance.recordedBy') ?></th>
                                    <th class="border border-gray-300 px-4 py-3 text-right text-sm font-bold text-gray-700"><?= lang('Finance.amount') ?></th>
                                    <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold text-gray-700"><?= lang('Finance.notes') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <?php $type = $payment['type'] ?? 'payment'; ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700"><?= formatDate($payment['payment_date']) ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700"><?= esc(lang('Finance.' . $type)) ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700"><?= esc(lang('Finance.' . $payment['payment_method'])) ?></td>
                                    <?php $paymentCreator = trim(($payment['creator_first_name'] ?? '') . ' ' . ($payment['creator_last_name'] ?? '')); ?>
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700"><?= $paymentCreator !== '' ? 'Dr. ' . esc($paymentCreator) : lang('Finance.unknown') ?></td>
                                    <?php
                                        $prefix = '+';
                                        if ($type === 'refund' || $type === 'void') {
                                            $prefix = '-';
                                        }
                                    ?>
                                    <td class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold text-gray-900"><?= $prefix . formatCurrency($payment['amount']) ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-gray-600"><?= $payment['notes'] ? nl2br(htmlspecialchars($payment['notes'])) : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Information -->
                <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h4 class="text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.paymentMethodUpper') ?></h4>
                        <p class="text-sm text-gray-600"><?= esc($paymentMethodLabel) ?></p>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h4 class="text-sm font-bold text-gray-700 mb-2"><?= strtoupper(lang('Finance.currency')) ?></h4>
                        <p class="text-sm text-gray-600"><?= esc($baseCurrency) ?></p>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h4 class="text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.paymentStatusUpper') ?></h4>
                        <p class="text-sm text-gray-600"><?= esc($paymentStatusLabel) ?></p>
                    </div>
                </div>

                <!-- Notes -->
                <?php if ($finance['notes']): ?>
                <div class="mb-8">
                    <h4 class="text-sm font-bold text-gray-700 mb-3"><?= strtoupper(lang('Finance.notes')) ?></h4>
                    <div class="bg-yellow-50 p-4 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-gray-700"><?= $finance['notes'] ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Professional Footer -->
                <div class="border-t-2 border-gray-400 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-sm font-bold text-gray-700 mb-3"><?= lang('Finance.paymentInstructions') ?></h4>
                            <div class="text-xs text-gray-600 space-y-1">
                                <p><?= lang('Finance.invoicePaymentInstruction1') ?></p>
                                <p><?= lang('Finance.invoicePaymentInstruction2') ?></p>
                                <p><?= lang('Finance.invoicePaymentInstruction3') ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <h4 class="text-sm font-bold text-gray-700 mb-3"><?= lang('Finance.thankYouUpper') ?></h4>
                            <p class="text-xs text-gray-600 mb-1"><?= lang('Finance.invoiceThankYouBody') ?></p>
                            <p class="text-xs text-gray-500"><?= lang('Finance.invoiceGeneratedOn') ?> <?= formatDateTime(date('Y-m-d H:i:s')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles - Professional Document Feel -->
<style>
@media print {
    /* Page setup - Clean invoice only */
    @page {
        size: A4;
        margin: 0.5in;
    }
    
    /* Hide outer containers and UI elements */
    .min-h-screen > .max-w-4xl > div:first-child,
    aside, #sidebar, nav, .sidebar, button, .print-button, .no-print,
    /* Hide header elements */
    header, .header, .navbar, .nav-header,
    /* Hide notification containers */
    .notification, .notifications, .alert, .alert-container,
    /* Hide debug elements */
    .debug, .debugbar, .ci-debug, .debug-icon, .debug-toolbar,
    /* Hide top header invoice number */
    .invoice-header, .invoice-number-header, .top-invoice,
    /* Hide any CodeIgniter debug elements */
    [class*="debug"], [id*="debug"], [class*="ci-"], [id*="ci-"],
    /* Hide any notification elements */
    [class*="notification"], [id*="notification"], [class*="alert"], [id*="alert"] {
        display: none !important;
    }
    
    /* Show the invoice container */
    .bg-white {
        display: block !important;
        background: white !important;
        box-shadow: none !important;
        border: none !important;
        page-break-inside: auto !important;
        overflow: visible !important;
    }
    
    /* Ensure main containers are visible */
    .min-h-screen {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }
    
    .max-w-4xl {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }
    
    /* Reset body for clean print */
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        font-family: 'Times New Roman', 'Georgia', serif !important;
        font-size: 11pt !important;
        line-height: 1.4 !important;
        color: #000 !important;
    }
    
    /* Hide any fixed or absolute positioned elements at top */
    .fixed, .absolute, .sticky {
        position: static !important;
    }
    
    /* Hide any elements with high z-index (usually overlays) */
    [style*="z-index"] {
        display: none !important;
    }
    
    /* Hide any elements that might be floating at top */
    .top-0, .top-4, .top-8, .top-12, .top-16 {
        display: none !important;
    }
    
    /* Header section */
    .border-b-4 {
        border-bottom: 2px solid #000 !important;
    }
    
    .px-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .py-4 {
        padding-top: 12pt !important;
        padding-bottom: 12pt !important;
    }
    
    .py-6 {
        padding-top: 18pt !important;
        padding-bottom: 18pt !important;
    }
    
    /* Professional Typography */
    .text-3xl {
        font-size: 24pt !important;
        line-height: 1.2 !important;
        font-weight: bold !important;
    }
    
    .text-lg {
        font-size: 14pt !important;
        line-height: 1.3 !important;
        font-weight: bold !important;
    }
    
    .text-sm {
        font-size: 10pt !important;
        line-height: 1.3 !important;
    }
    
    .text-xs {
        font-size: 9pt !important;
        line-height: 1.2 !important;
    }
    
    /* Font weights */
    .font-bold {
        font-weight: bold !important;
    }
    
    .font-semibold {
        font-weight: bold !important;
    }
    
    .font-medium {
        font-weight: bold !important;
    }
    
    /* Professional Document Colors */
    .text-gray-900 {
        color: #000 !important;
    }
    
    .text-gray-700 {
        color: #000 !important;
    }
    
    .text-gray-600 {
        color: #333 !important;
    }
    
    .text-gray-500 {
        color: #666 !important;
    }
    
    .text-blue-600 {
        color: #000 !important;
    }
    
    .text-green-600 {
        color: #000 !important;
    }
    
    .text-green-800 {
        color: #000 !important;
    }
    
    .text-yellow-800 {
        color: #000 !important;
    }
    
    .text-red-800 {
        color: #000 !important;
    }
    
    /* Background colors - Clean Document Look */
    .bg-gray-50 {
        background-color: #f8f8f8 !important;
    }
    
    .bg-gray-100 {
        background-color: #f0f0f0 !important;
    }
    
    .bg-green-50 {
        background-color: #f0f8f0 !important;
    }
    
    .bg-yellow-50 {
        background-color: #fff8f0 !important;
    }
    
    .bg-red-50 {
        background-color: #fff0f0 !important;
    }
    
    .bg-blue-50 {
        background-color: #f0f0f8 !important;
    }
    
    /* Professional Borders */
    .border {
        border: 1px solid #000 !important;
    }
    
    .border-gray-200 {
        border-color: #ccc !important;
    }
    
    .border-gray-300 {
        border-color: #999 !important;
    }
    
    .border-gray-400 {
        border-color: #666 !important;
    }
    
    .border-blue-600 {
        border-color: #000 !important;
    }
    
    .border-yellow-200 {
        border-color: #ccc !important;
    }
    
    .border-b-2 {
        border-bottom: 1px solid #000 !important;
    }
    
    .border-t-2 {
        border-top: 1px solid #000 !important;
    }
    
    /* Rounded corners - Remove for document feel */
    .rounded-lg {
        border-radius: 0 !important;
    }
    
    .rounded-full {
        border-radius: 0 !important;
    }
    
    /* Professional Document Spacing - Balanced for readability */
    .mb-2 {
        margin-bottom: 6pt !important;
    }
    
    .mb-3 {
        margin-bottom: 9pt !important;
    }
    
    .mb-4 {
        margin-bottom: 12pt !important;
    }
    
    .mb-6 {
        margin-bottom: 18pt !important;
    }
    
    .mb-8 {
        margin-bottom: 24pt !important;
    }
    
    .mb-10 {
        margin-bottom: 30pt !important;
    }
    
    .mt-1 {
        margin-top: 3pt !important;
    }
    
    .mt-2 {
        margin-top: 6pt !important;
    }
    
    .mr-3 {
        margin-right: 9pt !important;
    }
    
    /* Professional Document Padding - Balanced for readability */
    .p-3 {
        padding: 9pt !important;
    }
    
    .p-4 {
        padding: 12pt !important;
    }
    
    .p-6 {
        padding: 18pt !important;
    }
    
    .px-3 {
        padding-left: 9pt !important;
        padding-right: 9pt !important;
    }
    
    .px-4 {
        padding-left: 12pt !important;
        padding-right: 12pt !important;
    }
    
    .px-6 {
        padding-left: 18pt !important;
        padding-right: 18pt !important;
    }
    
    .py-2 {
        padding-top: 6pt !important;
        padding-bottom: 6pt !important;
    }
    
    .py-3 {
        padding-top: 9pt !important;
        padding-bottom: 9pt !important;
    }
    
    .py-4 {
        padding-top: 12pt !important;
        padding-bottom: 12pt !important;
    }
    
    .pb-2 {
        padding-bottom: 6pt !important;
    }
    
    .pb-3 {
        padding-bottom: 9pt !important;
    }
    
    .pt-2 {
        padding-top: 6pt !important;
    }
    
    .pt-3 {
        padding-top: 9pt !important;
    }
    
    .pt-6 {
        padding-top: 18pt !important;
    }
    
    /* Professional Table Styling */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        page-break-inside: auto !important;
    }
    
    /* Page break controls for sections */
    .border-b-4 {
        page-break-after: avoid !important;
    }
    
    /* Allow breaks between major sections */
    .mb-8, .mb-10 {
        page-break-after: auto !important;
    }
    
    /* Prevent orphaned headers */
    h1, h2, h3, h4 {
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
    }
    
    /* Allow breaks in content areas */
    .px-6 {
        page-break-inside: auto !important;
    }
    
    th, td {
        border: 1px solid #000 !important;
        padding: 8pt 12pt !important;
        text-align: left !important;
    }
    
    th {
        background-color: #f0f0f0 !important;
        font-weight: bold !important;
        font-size: 10pt !important;
    }
    
    td {
        font-size: 10pt !important;
    }
    
    /* Status badges - Document style */
    .px-3 {
        padding-left: 6pt !important;
        padding-right: 6pt !important;
    }
    
    .py-1 {
        padding-top: 2pt !important;
        padding-bottom: 2pt !important;
    }
    
    /* Ensure colors print correctly */
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    /* Hide overflow */
    .overflow-x-auto {
        overflow: visible !important;
    }
    
    /* Block display */
    .block {
        display: block !important;
    }
    
    /* Professional document spacing - Balanced for readability */
    .space-y-1 > * + * {
        margin-top: 3pt !important;
    }
    
    .space-y-2 > * + * {
        margin-top: 6pt !important;
    }
    
    .space-y-3 > * + * {
        margin-top: 9pt !important;
    }
    
    /* Width utilities for document */
    .w-12 {
        width: 36pt !important;
    }
    
    .w-80 {
        width: 240pt !important;
    }
    
    .w-96 {
        width: 288pt !important;
    }
    
    .h-12 {
        height: 36pt !important;
    }
    
    /* Grid gaps for document - Balanced for readability */
    .gap-3 {
        gap: 9pt !important;
    }
    
    .gap-6 {
        gap: 18pt !important;
    }
    
    .gap-8 {
        gap: 24pt !important;
    }
    
    /* Professional document layout */
    .grid-cols-1 {
        grid-template-columns: 1fr !important;
    }
    
    .grid-cols-2 {
        grid-template-columns: 1fr 1fr !important;
    }
    
    .grid-cols-3 {
        grid-template-columns: 1fr 1fr 1fr !important;
    }
    
    /* Text alignment */
    .text-left {
        text-align: left !important;
    }
    
    .text-right {
        text-align: right !important;
    }
    
    .text-center {
        text-align: center !important;
    }
    
    /* Flexbox for document */
    .flex {
        display: flex !important;
    }
    
    .justify-between {
        justify-content: space-between !important;
    }
    
    .justify-end {
        justify-content: flex-end !important;
    }
    
    .items-center {
        align-items: center !important;
    }
    
    .items-start {
        align-items: flex-start !important;
    }
}
</style>

<?= $this->endSection() ?>
