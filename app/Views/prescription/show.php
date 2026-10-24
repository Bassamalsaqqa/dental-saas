<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$tr = static fn(string $key, array $params = []) => lang('Prescription.' . $key, $params);
$doctorName = trim(($prescription['creator_first_name'] ?? '') . ' ' . ($prescription['creator_last_name'] ?? ''));
$patientName = trim(($prescription['first_name'] ?? '') . ' ' . ($prescription['last_name'] ?? ''));
$prescriptionReference = $prescription['prescription_id'] ?? ('RX-' . str_pad((string) ($prescription['id'] ?? '0'), 6, '0', STR_PAD_LEFT));

$medicines = json_decode((string) ($prescription['medication_name'] ?? ''), true);
if (!is_array($medicines)) { $medicines = []; }
if (isset($medicines['name']) || isset($medicines['dosage']) || isset($medicines['frequency']) || isset($medicines['duration'])) { $medicines = [$medicines]; }
$medicineCount = count($medicines);

$statusKey = strtolower((string) ($prescription['status'] ?? 'pending'));
$statusLabel = $tr('status_' . $statusKey);
if ($statusLabel === 'Prescription.status_' . $statusKey) { $statusLabel = ucfirst($statusKey); }
$statusTone = match ($statusKey) {
    'active' => ['bg' => 'rgba(16,185,129,0.22)', 'border' => 'rgba(167,243,208,0.4)'],
    'expired' => ['bg' => 'rgba(251,191,36,0.22)', 'border' => 'rgba(253,230,138,0.4)'],
    'cancelled' => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
    'pending' => ['bg' => 'rgba(59,130,246,0.22)', 'border' => 'rgba(147,197,253,0.4)'],
    default => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
};
?>



<div class="denta-prescription-workspace v3-module-workspace">
<div class="v3-module-content">

    <!-- Hero Header -->
    <section class="v3-record-banner"><div class="v3-record-heading"><span class="v3-record-reference"><?= esc($prescriptionReference) ?></span><h1 class="v3-title-scale font-extrabold tracking-tight v3-text-on-primary sm:text-4xl"><?= esc($patientName) ?></h1><span class="patient-hero-status inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold" style="background:<?= esc($statusTone['bg']) ?>;border-color:<?= esc($statusTone['border']) ?>;color:#eff6ff;"><?= esc($statusLabel) ?></span><div class="v3-record-meta">
                                <div class="patient-hero-chip" title="<?= esc($tr('issued_on') . ': ' . (!empty($prescription['prescribed_date']) ? formatDate($prescription['prescribed_date']) : $tr('na'))) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-calendar-check"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-label"><?= esc($tr('issued_on')) ?></span>
                                        <span class="patient-hero-chip-value"><?= !empty($prescription['prescribed_date']) ? formatDate($prescription['prescribed_date']) : '—' ?></span>
                                    </span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('expires_on') . ': ' . (!empty($prescription['expiry_date']) ? formatDate($prescription['expiry_date']) : $tr('na'))) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-calendar-times"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-label"><?= esc($tr('expires_on')) ?></span>
                                        <span class="patient-hero-chip-value"><?= !empty($prescription['expiry_date']) ? formatDate($prescription['expiry_date']) : '—' ?></span>
                                    </span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('medicines') . ': ' . $tr('medicine_count_label', ['count' => $medicineCount])) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-pills"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-label"><?= esc($tr('medicines')) ?></span>
                                        <span class="patient-hero-chip-value"><?= esc($tr('medicine_count_label', ['count' => $medicineCount])) ?></span>
                                    </span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('created_by') . ': ' . ($doctorName ?: $tr('unknown'))) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-user-doctor"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-label"><?= esc($tr('created_by')) ?></span>
                                        <span class="patient-hero-chip-value"><?= esc($doctorName ?: $tr('unknown')) ?></span>
                                    </span>
                                </div>
                            </div></div><div class="v3-record-actions">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php if (!empty($prescription['patient_id'])): ?>
                        <a href="<?= workflow_context_url('patient/' . $prescription['patient_id'], $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas fa-user"></i><span><?= esc($tr('patient_profile')) ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="<?= esc(workflow_back('prescription', $return_to ?? null)) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back')) ?></span>
                        </a>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php if (has_permission('prescriptions', 'edit')): ?>
                        <a href="<?= workflow_context_url('prescription/' . $prescription['id'] . '/edit', $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas fa-pen"></i><span><?= esc($tr('edit_prescription')) ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if (has_permission('prescriptions', 'view')): ?>
                        <a href="<?= base_url('prescription/' . $prescription['id'] . '/print') ?>" target="_blank" class="patient-hero-tool-button">
                            <i class="fas fa-print"></i><span><?= esc($tr('print_prescription')) ?></span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php if (has_permission('prescriptions', 'delete')): ?>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <button type="button" onclick="deletePrescription(<?= $prescription['id'] ?>)" class="patient-header-action patient-header-action-danger v3-btn v3-btn--secondary">
                            <i class="fas fa-trash"></i><span><?= esc($tr('delete_prescription')) ?></span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div></section>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Left: Medications + Instructions -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Medication Plan -->
            <div class="rx-card rx-card-medication overflow-hidden">
                <div class="rx-card-header">
                    <i class="fas fa-capsules"></i>
                    <div>
                        <h3 class="text-base font-black"><?= esc($tr('medication_plan')) ?></h3>
                        <p class="rx-section-subtitle"><?= esc($tr('medication_plan_help')) ?></p>
                    </div>
                </div>
                <?php if (!empty($medicines)): ?>
                <div class="rx-medication-table-wrap overflow-x-auto">
                    <table class="rx-medication-table min-w-full">
                        <thead>
                            <tr>
                                <th>
                                    <div class="flex items-center gap-2"><i class="fas fa-capsules v3-accent-text"></i><span><?= esc($tr('medicine_name')) ?></span></div>
                                </th>
                                <th>
                                    <div class="flex items-center gap-2"><i class="fas fa-weight-scale v3-accent-text"></i><span><?= esc($tr('dosage')) ?></span></div>
                                </th>
                                <th>
                                    <div class="flex items-center gap-2"><i class="fas fa-clock v3-accent-text"></i><span><?= esc($tr('frequency')) ?></span></div>
                                </th>
                                <th>
                                    <div class="flex items-center gap-2"><i class="fas fa-calendar-days v3-accent-text"></i><span><?= esc($tr('duration')) ?></span></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y v3-border-default">
                            <?php foreach ($medicines as $m): ?>
                                <tr>
                                    <td>
                                        <div class="rx-medicine-name"><span class="rx-medicine-icon"><i class="fas fa-pills"></i></span><span><?= esc($m['name'] ?? '-') ?></span></div>
                                    </td>
                                    <td><?= esc($m['dosage'] ?? '-') ?></td>
                                    <td><?= esc($m['frequency'] ?? '-') ?></td>
                                    <td><?= esc($m['duration'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="rx-empty-state"><?= esc($tr('no_medication_information_available')) ?></p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right: Metadata + Instructions -->
        <div class="space-y-6">
            <div class="rx-card rx-card-metadata">
                <div class="rx-card-header">
                    <i class="fas fa-clipboard-list"></i>
                    <div>
                        <h3 class="text-base font-black"><?= esc($tr('rx_metadata')) ?></h3>
                        <p class="rx-section-subtitle"><?= esc($tr('prescription_summary_side_help')) ?></p>
                    </div>
                </div>
                <div class="rx-info-list">
                    <div class="rx-info-row"><span class="rx-info-label"><?= esc($tr('prescription_id')) ?></span><span class="rx-info-value font-mono"><?= esc($prescriptionReference) ?></span></div>
                    <div class="rx-info-row"><span class="rx-info-label"><?= esc($tr('status')) ?></span><span class="rx-info-value"><?= esc($statusLabel) ?></span></div>
                    <div class="rx-info-row"><span class="rx-info-label"><?= esc($tr('created_by')) ?></span><span class="rx-info-value"><?= esc($doctorName ?: $tr('unknown')) ?></span></div>
                    <div class="rx-info-row"><span class="rx-info-label"><?= esc($tr('patient')) ?></span>
                        <?php if (!empty($prescription['patient_id'])): ?>
                        <a href="<?= workflow_context_url('patient/' . $prescription['patient_id'], $return_to ?? null) ?>" class="rx-info-value v3-accent-text v3-hover-text-primary hover:underline"><?= esc($patientName) ?></a>
                        <?php else: ?>
                        <span class="rx-info-value">-</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="rx-card rx-card-instructions">
                <div class="rx-card-header">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        <h3 class="text-base font-black"><?= esc($tr('important_instructions')) ?></h3>
                        <p class="rx-section-subtitle"><?= esc($tr('instructions_help')) ?></p>
                    </div>
                </div>
                <div class="rx-instructions-body whitespace-pre-line">
                    <?= nl2br(esc($prescription['instructions'] ?? $tr('no_instructions_provided'))) ?>
                </div>
            </div>

            <div class="rx-card rx-card-notice">
                <div class="rx-notice-body">
                    <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center v3-radius v3-surface">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>
                    <div>
                        <h4 class="text-sm font-black"><?= esc($tr('emergency_notice')) ?></h4>
                        <p class="mt-1 text-sm font-semibold leading-relaxed opacity-95"><?= esc($tr('emergency_notice_body')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

<script>
function deletePrescription(id) {
    if (!confirm(<?= json_encode(lang('Prescription.delete_confirm_hard')) ?>)) return;
    fetch('<?= base_url() ?>prescription/'+id, {
        method:'DELETE',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest',[window.csrfConfig?.header||'X-CSRF-TOKEN']:window.getCsrfToken?.()||''},
    }).then(r=>r.json()).then(d=>{
        if(d.csrf_token&&window.refreshCsrfToken)window.refreshCsrfToken(d.csrf_token);
        if(d.success)window.location.href='<?= base_url('prescription') ?>';
        else alert(d.message||'Error');
    }).catch(()=>alert(<?= json_encode($tr('delete_error_occurred')) ?>));
}
</script>
<?= $this->endSection() ?>
