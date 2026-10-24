<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<?php
$isRtl = !empty($is_rtl);
$tr = static fn(string $key, array $params = []) => lang('Treatment.' . $key, $params);
$doctorName = trim(($treatment['doctor_first_name'] ?? '') . ' ' . ($treatment['doctor_last_name'] ?? ''));
$patientName = trim(($treatment['first_name'] ?? '') . ' ' . ($treatment['last_name'] ?? ''));
$statusRaw = $treatment['status'] ?? 'active';
$statusLabel = lang('Treatment.status_values.' . $statusRaw);
if ($statusLabel === 'Treatment.status_values.' . $statusRaw) { $statusLabel = ucfirst(str_replace('_', ' ', $statusRaw)); }
$typeLabel = ucfirst(str_replace('_', ' ', $treatment['treatment_type'] ?? ''));
$chargeLine = $charge_line ?? null;
$chargeLineId = (int) ($chargeLine['id'] ?? 0);
$chargeLineStatus = (string) ($chargeLine['status'] ?? '');
$chargeLineAmount = (float) ($chargeLine['net_production_amount'] ?? 0);
$chargeLineCurrency = (string) ($chargeLine['currency'] ?? 'ILS');
$chargeLineIsOpen = $chargeLineId > 0 && !in_array($chargeLineStatus, ['invoiced', 'voided'], true);
$treatmentReturnTarget = 'treatment/' . (int) ($treatment['id'] ?? 0);
$billThisTreatmentUrl = $chargeLineIsOpen && !empty($treatment['patient_id'])
    ? workflow_context_url('finance/create?patient_id=' . (int) $treatment['patient_id'] . '&transaction_type=invoice&clinical_charge_line_ids[]=' . $chargeLineId, $treatmentReturnTarget)
    : '';
$linkedInvoiceUrl = !empty($chargeLine['finance_id'])
    ? workflow_context_url('finance/' . (int) $chargeLine['finance_id'], $treatmentReturnTarget)
    : '';
$treatmentFinancePatient = !empty($treatment['patient_id']) ? [
    'id' => (int) $treatment['patient_id'],
    'first_name' => (string) ($treatment['first_name'] ?? ''),
    'last_name' => (string) ($treatment['last_name'] ?? ''),
    'patient_id' => (string) ($treatment['patient_number'] ?? $treatment['patient_id'] ?? ''),
    'phone' => (string) ($treatment['phone'] ?? ''),
] : null;
$invoiceableChargeLines = $chargeLineIsOpen && is_array($chargeLine) ? [$chargeLine] : [];
$invoiceableChargeLineIds = $chargeLineId > 0 ? [$chargeLineId] : [];
$moneyTrail = $money_trail ?? [];
$moneySummary = $moneyTrail['summary'] ?? [];
$billingStateLabel = $chargeLineId <= 0
    ? $tr('no_charge_captured')
    : ($chargeLineIsOpen ? $tr('not_billed_yet') : $tr('already_billed'));
$billingStateTone = $chargeLineId <= 0
    ? 'v3-border-default v3-bg-panel v3-text-ink'
    : ($chargeLineIsOpen ? 'v3-border-default v3-bg-waiting v3-text-warning' : 'v3-border-default v3-bg-paid v3-text-primary');
$invoiceBalance = (float) ($moneySummary['balance_due'] ?? 0);
$providerRemaining = (float) ($moneySummary['provider_remaining'] ?? 0);
$inventoryCost = (float) ($moneySummary['inventory_cost'] ?? 0);
$procedureLifecycle = $procedure_lifecycle ?? null;
$procedureStatus = strtolower((string) ($procedureLifecycle['status'] ?? ''));
$procedureStatusLabel = $procedureStatus !== '' ? $tr('procedure_status_' . $procedureStatus) : '';
if ($procedureStatus !== '' && $procedureStatusLabel === 'Treatment.procedure_status_' . $procedureStatus) {
    $procedureStatusLabel = ucfirst(str_replace('_', ' ', $procedureStatus));
}
$procedureStatusClass = match ($procedureStatus) {
    'completed' => 'v3-bg-paid v3-text-primary v3-border-default',
    'in_progress' => 'v3-bg-scheduled v3-text-primary v3-border-default',
    'accepted', 'scheduled' => 'v3-bg-scheduled v3-text-primary v3-border-default',
    'rejected', 'cancelled' => 'v3-bg-alert v3-text-danger v3-border-default',
    'referred' => 'v3-bg-chair v3-text-chair v3-border-default',
    default => 'v3-bg-waiting v3-text-warning v3-border-default',
};
$statusTone = match ($statusRaw) {
    'active' => ['bg' => 'rgba(59,130,246,0.22)', 'border' => 'rgba(147,197,253,0.4)'],
    'completed' => ['bg' => 'rgba(16,185,129,0.22)', 'border' => 'rgba(167,243,208,0.4)'],
    'on_hold' => ['bg' => 'rgba(251,191,36,0.22)', 'border' => 'rgba(253,230,138,0.4)'],
    'cancelled' => ['bg' => 'rgba(244,63,94,0.22)', 'border' => 'rgba(254,205,211,0.4)'],
    default => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
};
?>



<div class="treatment-page-shell min-h-screen">
<div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">

    <!-- Hero Header -->
    <section class="v3-record-context patient-hero-card-neutral overflow-hidden border mb-4">

        <div class="v3-record-context-body">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em]">
                        <span><?= esc($tr('treatment_management')) ?></span>
                        <span class="h-1 w-1"></span>
                        <span><?= esc($treatment['treatment_id'] ?? '') ?></span>
                    </div>
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                        <div class="v3-context-avatar patient-avatar-badge-neutral">
                            <i class="fas fa-tooth"></i>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="v3-title font-extrabold tracking-tight v3-title"><?= esc($treatment['treatment_name'] ?? $tr('treatment_details')) ?></h1>
                                    <span class="patient-hero-status inline-flex items-center border px-3 py-1 text-xs font-semibold" style="background:<?= esc($statusTone['bg']) ?>;border-color:<?= esc($statusTone['border']) ?>;color:#eff6ff;"><?= esc($statusLabel) ?></span>
                                </div>
                            </div>
                            <div class="v3-context-facts">
                                <div class="patient-hero-chip" title="<?= esc($tr('treatment_type_required') . ': ' . $typeLabel) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-tag"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-label"><?= esc($tr('treatment_type_required')) ?></span>
                                        <span class="patient-hero-chip-value"><?= esc($typeLabel) ?></span>
                                    </span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('tooth_number') . ': ' . ($treatment['tooth_number'] ?? '—')) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-tooth"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-label"><?= esc($tr('tooth_number_short')) ?></span>
                                        <span class="patient-hero-chip-value"><?= esc($treatment['tooth_number'] ?? '—') ?></span>
                                    </span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('start_date') . ': ' . (!empty($treatment['start_date']) ? formatDate($treatment['start_date']) : '—')) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-calendar"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-label"><?= esc($tr('start')) ?></span>
                                        <span class="patient-hero-chip-value"><?= !empty($treatment['start_date']) ? formatDate($treatment['start_date']) : '—' ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="v3-record-context-actions">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php if (!empty($treatment['patient_id'])): ?>
                        <a href="<?= workflow_context_url('patient/' . $treatment['patient_id'], $return_to ?? null) ?>" class="v3-context-action patient-header-action-soft">
                            <i class="fas fa-user"></i><span><?= esc(lang('UI.patientProfile')) ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="<?= esc(workflow_back('treatment', $return_to ?? null)) ?>" class="v3-context-action patient-header-action-soft">
                            <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back')) ?></span>
                        </a>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <a href="<?= workflow_context_url('treatment/' . $treatment['id'] . '/edit', $return_to ?? null) ?>" class="v3-context-action patient-header-action-soft">
                            <i class="fas fa-pen"></i><span><?= esc($tr('edit_treatment')) ?></span>
                        </a>
                        <button type="button" onclick="printTreatment(<?= $treatment['id'] ?>)" class="patient-hero-tool-button v3-btn v3-btn--secondary">
                            <i class="fas fa-print"></i><span><?= esc($tr('print_treatment')) ?></span>
                        </button>
                    </div>
                    <?php if ($statusRaw === 'active'): ?>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <button type="button" onclick="completeTreatment(<?= $treatment['id'] ?>)" class="v3-context-action patient-header-action-emerald v3-btn v3-btn--secondary">
                            <i class="fas fa-check"></i><span><?= esc($tr('mark_as_completed')) ?></span>
                        </button>
                    </div>
                    <?php endif; ?>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <button type="button" onclick="deleteTreatment(<?= $treatment['id'] ?>)" class="v3-context-action patient-header-action-danger v3-btn v3-btn--secondary">
                            <i class="fas fa-trash"></i><span><?= esc($tr('delete_treatment')) ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="treatment-command-section mb-6 v3-radius border p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] v3-text-primary"><?= esc($tr('operational_snapshot')) ?></p>
                <h2 class="mt-1 text-lg font-extrabold"><?= esc($tr('clinical_finance_trace')) ?></h2>
            </div>
            <?php if ($linkedInvoiceUrl !== ''): ?>
                <a href="<?= esc($linkedInvoiceUrl) ?>" class="inline-flex w-fit items-center justify-center gap-2 border v3-border-default v3-bg-paid px-3 py-2 text-sm font-bold v3-text-primary v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span><?= esc($tr('open_linked_invoice')) ?></span>
                </a>
            <?php endif; ?>
        </div>
        <div class="treatment-snapshot-grid">
            <div class="treatment-snapshot-card">
                <p class="treatment-snapshot-label"><?= esc($tr('billing_state')) ?></p>
                <p class="treatment-snapshot-value"><?= esc($billingStateLabel) ?></p>
                <p class="treatment-snapshot-meta" dir="ltr">
                    <?= $chargeLineId > 0 ? esc($chargeLineCurrency . ' ' . number_format($chargeLineAmount, 2)) : esc($tr('not_available')) ?>
                </p>
            </div>
            <div class="treatment-snapshot-card">
                <p class="treatment-snapshot-label"><?= esc($tr('patient')) ?></p>
                <p class="treatment-snapshot-value truncate"><?= esc($patientName !== '' ? $patientName : $tr('not_available')) ?></p>
                <p class="treatment-snapshot-meta"><?= !empty($treatment['phone']) ? esc($treatment['phone']) : esc($tr('not_available')) ?></p>
            </div>
            <div class="treatment-snapshot-card">
                <p class="treatment-snapshot-label"><?= esc(lang('UI.moneyTrailBalanceDue')) ?></p>
                <p class="treatment-snapshot-value <?= $invoiceBalance > 0.009 ? 'v3-text-warning' : 'v3-text-primary' ?>" dir="ltr"><?= esc(formatCurrency($invoiceBalance)) ?></p>
                <p class="treatment-snapshot-meta"><?= esc(lang('UI.moneyTrailCharged')) ?>: <span dir="ltr"><?= esc(formatCurrency($moneySummary['charged'] ?? 0)) ?></span></p>
            </div>
            <div class="treatment-snapshot-card">
                <p class="treatment-snapshot-label"><?= esc(lang('UI.moneyTrailProviderRemaining')) ?></p>
                <p class="treatment-snapshot-value <?= $providerRemaining > 0.009 ? 'v3-text-chair' : 'v3-text-ink' ?>" dir="ltr"><?= esc(formatCurrency($providerRemaining)) ?></p>
                <p class="treatment-snapshot-meta"><?= esc(lang('UI.moneyTrailProviderShare')) ?>: <span dir="ltr"><?= esc(formatCurrency($moneySummary['provider_share'] ?? 0)) ?></span></p>
            </div>
            <div class="treatment-snapshot-card">
                <p class="treatment-snapshot-label"><?= esc(lang('UI.moneyTrailInventoryCost')) ?></p>
                <p class="treatment-snapshot-value <?= $inventoryCost > 0.009 ? 'v3-text-danger' : 'v3-text-ink' ?>" dir="ltr"><?= esc(formatCurrency($inventoryCost)) ?></p>
                <p class="treatment-snapshot-meta"><?= esc($tr('inventory_usage')) ?>: <?= esc((string) count($usage_records ?? [])) ?></p>
            </div>
        </div>
    </section>

    <!-- Content Grid -->
    <div class="treatment-workspace-section <?= !empty($usage_records) ? 'treatment-workspace-has-inventory' : 'treatment-workspace-no-inventory' ?>">

        <!-- Left Column -->
        <div class="treatment-clinical-grid <?= !empty($treatment['treatment_notes']) ? 'treatment-clinical-grid-has-notes' : 'treatment-clinical-grid-no-notes' ?>">
            <div class="treatment-section-heading treatment-section-heading--clinical flex items-center gap-3">
                <span class="treatment-section-icon"><i class="fas fa-notes-medical"></i></span>
                <div>
                    <p><?= esc($tr('procedure_overview')) ?></p>
                    <h2><?= esc($tr('treatment_overview')) ?></h2>
                </div>
            </div>

            <?php if ($procedureStatus !== ''): ?>
            <div class="treatment-card treatment-card--clinical">
                <div class="treatment-card-header">
                    <i class="fas fa-route"></i><span><?= esc($tr('procedure_lifecycle')) ?></span>
                </div>
                <div class="treatment-card-body">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border">
                        <div>
                            <p class="text-sm font-extrabold"><?= esc($tr('procedure_lifecycle')) ?></p>
                            <p class="mt-1 text-xs font-semibold"><?= esc($tr('procedure_lifecycle_help')) ?></p>
                        </div>
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-extrabold border <?= esc($procedureStatusClass) ?>">
                            <i class="fas fa-circle-check"></i>
                            <?= esc($procedureStatusLabel) ?>
                        </span>
                    </div>
                    <?php if (in_array($procedureStatus, ['proposed', 'presented'], true) && has_permission('treatments', 'edit')): ?>
                        <form action="<?= base_url('treatment/' . (int) $treatment['id'] . '/accept') ?>" method="POST" class="mt-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return_to" value="treatment/<?= (int) $treatment['id'] ?>">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-extrabold transition hover:-translate-y-0.5 v3-btn v3-btn--primary">
                                <i class="fas fa-circle-check"></i>
                                <span><?= esc($tr('accept_for_scheduling')) ?></span>
                            </button>
                        </form>
                    <?php elseif ($procedureStatus === 'accepted' && has_permission('appointments', 'create')): ?>
                        <a href="<?= base_url('appointment/create?' . http_build_query([
                            'patient_id' => (int) $treatment['patient_id'],
                            'clinical_procedure_id' => (int) $procedureLifecycle['id'],
                            'appointment_type' => 'treatment',
                            'return_to' => 'treatment/' . (int) $treatment['id'],
                        ])) ?>"
                           data-appointment-create-modal-open
                           data-patient-id="<?= (int) $treatment['patient_id'] ?>"
                           data-clinical-procedure-id="<?= (int) $procedureLifecycle['id'] ?>"
                           data-appointment-type="treatment"
                           class="mt-4 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-extrabold transition hover:-translate-y-0.5 v3-btn v3-btn--secondary">
                            <i class="fas fa-calendar-plus"></i>
                            <span><?= esc($tr('schedule_accepted_treatment')) ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Treatment Description -->
            <div class="treatment-card treatment-card--clinical">
                <div class="treatment-card-header">
                    <i class="fas fa-align-left"></i><span><?= esc($tr('treatment_description')) ?></span>
                </div>
                <div class="treatment-card-body">
                    <div class="treatment-detail-copy text-sm"><?= !empty($treatment['treatment_description']) ? nl2br(htmlspecialchars($treatment['treatment_description'])) : '<span class="italic">' . esc($tr('no_description_provided')) . '</span>' ?></div>
                </div>
            </div>

            <!-- Treatment Notes -->
            <?php if (!empty($treatment['treatment_notes'])): ?>
            <div class="treatment-card treatment-card--notes">
                <div class="treatment-card-header">
                    <i class="fas fa-sticky-note v3-text-warning"></i><span><?= esc($tr('treatment_notes')) ?></span>
                </div>
                <div class="treatment-card-body">
                    <div class="treatment-detail-copy text-sm"><?= nl2br(htmlspecialchars($treatment['treatment_notes'])) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Inventory Usage -->
            <?php if (!empty($usage_records)): ?>
            <div class="treatment-card treatment-card--inventory">
                <div class="treatment-card-header">
                    <i class="fas fa-boxes v3-text-primary"></i><span><?= esc($tr('inventory_usage')) ?></span>
                </div>
                <div class="treatment-card-body">
                <div class="treatment-card-panel overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase border-b">
                                <th class="px-3 py-2.5"><?= esc($tr('date')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('items_used')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('cost')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('notes')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y v3-border-default">
                            <?php foreach ($usage_records as $usage):
                                $items = json_decode($usage['items_used'] ?? '[]', true);
                                $itemList = [];
                                if (is_array($items)) {
                                    foreach ($items as $item) { $itemList[] = ($item['item_name'] ?? 'Item') . ' ×' . ($item['quantity_used'] ?? 0); }
                                }
                            ?>
                                <tr class="">
                                    <td class="px-3 py-2.5"><?= !empty($usage['usage_date']) ? formatDate($usage['usage_date']) : '—' ?></td>
                                    <td class="px-3 py-2.5"><?= !empty($itemList) ? esc(implode(', ', $itemList)) : '—' ?></td>
                                    <td class="px-3 py-2.5 font-semibold v3-text-primary"><?= formatCurrency((float) ($usage['total_cost'] ?? 0)) ?></td>
                                    <td class="px-3 py-2.5"><?= !empty($usage['notes']) ? esc($usage['notes']) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <aside class="treatment-context-grid <?= !empty($treatment['patient_id']) ? 'treatment-context-grid-has-patient' : 'treatment-context-grid-no-patient' ?>">
            <div class="treatment-section-heading treatment-section-heading--billing flex items-center gap-3">
                <span class="treatment-section-icon"><i class="fas fa-route"></i></span>
                <div>
                    <p><?= esc($tr('clinical_finance_trace')) ?></p>
                    <h2><?= esc($tr('patient_billing')) ?></h2>
                </div>
            </div>

            <!-- Patient Billing -->
            <div class="treatment-card treatment-card--billing">
                <div class="treatment-card-header">
                    <i class="fas fa-file-invoice-dollar v3-text-primary"></i><span><?= esc($tr('patient_billing')) ?></span>
                </div>
                <div class="treatment-card-body space-y-4 text-sm">
                    <?php if ($chargeLineId > 0): ?>
                        <div class="treatment-card-panel p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide v3-text-primary"><?= esc($tr('captured_charge')) ?></p>
                                    <p class="mt-1 truncate font-bold"><?= esc($chargeLine['procedure_name'] ?? $treatment['treatment_name'] ?? $tr('treatment')) ?></p>
                                    <?php if (!empty($chargeLine['tooth_numbers'])): ?>
                                        <p class="mt-1 text-xs"><?= esc($tr('tooth_number_short')) ?>: <span dir="ltr"><?= esc($chargeLine['tooth_numbers']) ?></span></p>
                                    <?php endif; ?>
                                </div>
                                <div class="shrink-0 text-end">
                                    <p class="v3-text-primary" dir="ltr"><?= esc($chargeLineCurrency) ?> <?= number_format($chargeLineAmount, 2) ?></p>
                                    <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-bold <?= $chargeLineIsOpen ? 'v3-bg-waiting v3-text-warning' : 'v3-bg-paid v3-text-primary' ?>">
                                        <?= esc($chargeLineIsOpen ? $tr('not_billed_yet') : $tr('already_billed')) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if ($billThisTreatmentUrl !== ''): ?>
                            <button type="button" data-app-drawer-open="treatmentInvoiceDrawer" class="treatment-card-action w-full v3-bg-primary v3-hover-primary v3-btn v3-btn--primary">
                                <i class="fas fa-file-invoice-dollar"></i><span><?= esc($tr('create_invoice_from_treatment')) ?></span>
                            </button>
                        <?php elseif ($linkedInvoiceUrl !== ''): ?>
                            <a href="<?= esc($linkedInvoiceUrl) ?>" class="treatment-card-action w-full border v3-border-default v3-text-primary v3-hover-primary">
                                <i class="fas fa-link"></i><span><?= esc($tr('open_linked_invoice')) ?></span>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="treatment-card-empty p-4">
                            <p class="font-semibold"><?= esc($tr('no_charge_captured')) ?></p>
                            <a href="<?= workflow_context_url('treatment/' . $treatment['id'] . '/edit', $return_to ?? null) ?>" class="mt-2 inline-flex items-center gap-2 text-xs font-bold v3-text-primary v3-hover-text-primary">
                                <i class="fas fa-pen"></i><span><?= esc($tr('edit_to_capture_charge')) ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="treatment-money-trail">
            <?= view('components/money_trail_panel', [
                'money_trail' => $money_trail ?? [],
                'is_rtl' => $isRtl,
                'return_to' => $return_to ?? null,
            ]) ?>
            </div>

            <!-- Timeline -->
            <div class="treatment-card treatment-card--timeline">
                <div class="treatment-card-header">
                    <i class="fas fa-clock"></i><span><?= esc($tr('treatment_timeline')) ?></span>
                </div>
                <div class="treatment-card-body space-y-3 text-sm">
                    <div class="flex justify-between"><span class=""><?= esc($tr('start_date')) ?></span><span class=""><?= !empty($treatment['start_date']) ? formatDate($treatment['start_date']) : '—' ?></span></div>
                    <div class="treatment-info-row"><span class="treatment-info-label"><?= esc($tr('completion_date')) ?></span><span class="treatment-info-value"><?= !empty($treatment['completion_date']) ? formatDateTime($treatment['completion_date']) : esc($tr('not_completed')) ?></span></div>
                    <div class="flex justify-between"><span class=""><?= esc($tr('created_by')) ?></span><span class=""><?= $doctorName !== '' ? esc($doctorName) : '—' ?></span></div>
                    <div class="flex justify-between"><span class=""><?= esc($tr('last_updated')) ?></span><span class="text-xs"><?= !empty($treatment['updated_at']) ? formatDateTime($treatment['updated_at']) : '—' ?></span></div>
                </div>
            </div>
        </aside>
    </div>

</div>
</div>

<?php if (has_permission('finance', 'create') && !empty($invoiceableChargeLineIds)): ?>
    <?php
    $treatmentInvoiceDrawerId = 'treatmentInvoiceDrawer';
    $treatmentInvoiceFormId = 'treatmentInvoiceDrawerForm';
    ob_start();
    ?>
    <form id="<?= esc($treatmentInvoiceFormId) ?>"
          method="post"
          action="<?= base_url('finance/store') ?>"
          enctype="multipart/form-data"
          data-finance-calc
          data-provider-split
          data-base-currency="<?= esc($treatment_finance_base_currency ?? settings()->get('currency', 'ILS')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc($treatmentReturnTarget) ?>">
        <?= view('finance/partials/form_fields', [
            'contextual' => true,
            'id_prefix' => $treatmentInvoiceDrawerId,
            'selected_patient' => $treatmentFinancePatient,
            'patients' => !empty($treatmentFinancePatient) ? [$treatmentFinancePatient] : [],
            'transaction_type_preset' => 'invoice',
            'lock_transaction_type' => true,
            'base_currency' => $treatment_finance_base_currency ?? settings()->get('currency', 'ILS'),
            'currency_options' => $treatment_finance_currency_options ?? ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'liquidity_account_options' => $treatment_finance_liquidity_options ?? [],
            'clinic_providers' => $clinic_providers ?? [],
            'clinical_charge_lines' => $invoiceableChargeLines,
            'selected_clinical_charge_line_ids' => $invoiceableChargeLineIds,
            'is_rtl' => $isRtl,
            'advanced_details_style' => 'flat',
        ]) ?>
    </form>
    <?php $treatmentInvoiceDrawerBody = (string) ob_get_clean(); ?>
    <?= view('components/app_drawer', [
        'id' => $treatmentInvoiceDrawerId,
        'title' => $tr('create_invoice_from_treatment'),
        'description' => '',
        'icon' => 'fas fa-file-invoice-dollar',
        'tone' => 'v3-bg-primary',
        'size' => 'wide',
        'body' => $treatmentInvoiceDrawerBody,
        'footer' => '<div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">'
            . '<button type="button" class="inline-flex items-center justify-center border px-4 py-2.5 text-sm font-bold" data-app-drawer-close="' . esc($treatmentInvoiceDrawerId) . '">' . esc(lang('UI.cancel')) . '</button>'
            . '<a href="' . esc($billThisTreatmentUrl) . '" class="inline-flex items-center justify-center border v3-border-default px-4 py-2.5 text-sm font-bold v3-text-primary v3-hover-primary">' . esc(lang('UI.openFullPage')) . '</a>'
            . '<button type="submit" form="' . esc($treatmentInvoiceFormId) . '" class="inline-flex items-center justify-center v3-bg-primary px-5 py-2.5 text-sm font-bold v3-hover-primary">' . esc(lang('Finance.createTransaction')) . '</button>'
            . '</div>',
    ]) ?>
    <script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<?php endif; ?>
<script>
function completeTreatment(id) {
    if (!confirm('<?= esc(lang('Treatment.confirm_mark_completed'), 'js') ?>')) return;
    fetch('<?= base_url('treatment') ?>/'+id+'/complete', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest',[window.csrfConfig?.header||'X-CSRF-TOKEN']:window.getCsrfToken?.()||''},
    }).then(r=>r.json()).then(d=>{
        if(d.csrf_token&&window.refreshCsrfToken)window.refreshCsrfToken(d.csrf_token);
        if(d.success)location.reload();else alert(d.message);
    }).catch(()=>alert('<?= esc(lang('Treatment.error_completing_treatment'), 'js') ?>'));
}
function printTreatment(id) { window.open('<?= base_url('treatment') ?>/'+id+'/print','_blank'); }
function deleteTreatment(id) {
    if (!confirm('<?= esc(lang('Treatment.confirm_delete_treatment'), 'js') ?>')) return;
    fetch('<?= base_url('treatment') ?>/'+id, {
        method:'DELETE', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest',[window.csrfConfig?.header||'X-CSRF-TOKEN']:window.getCsrfToken?.()||''},
    }).then(r=>r.json()).then(d=>{
        if(d.csrf_token&&window.refreshCsrfToken)window.refreshCsrfToken(d.csrf_token);
        if(d.success)window.location.href='<?= base_url('treatment') ?>';else alert(d.message);
    }).catch(()=>alert('<?= esc(lang('Treatment.error_deleting_treatment'), 'js') ?>'));
}
</script>
</div>
<?= $this->endSection() ?>
