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

<style>
.rx-card {
    --rx-accent: #2563eb;
    --rx-soft: rgba(239,246,255,0.94);
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 7% 0%, color-mix(in srgb, var(--rx-accent) 15%, transparent), transparent 30%),
        linear-gradient(135deg, rgba(255,255,255,0.99), var(--rx-soft) 48%, rgba(255,255,255,0.98));
    border: 1px solid rgba(255,255,255,0.82);
    border-radius: 1.2rem;
    box-shadow: 0 22px 50px -40px rgba(15,23,42,0.48), inset 0 1px 0 rgba(255,255,255,0.9);
    padding: 1.25rem;
}
.rx-card-header {
    display: flex; align-items: center; gap: 0.75rem;
    margin: -1.25rem -1.25rem 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(203,213,225,0.62);
    background: radial-gradient(circle at 8% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, var(--rx-accent), color-mix(in srgb, var(--rx-accent) 82%, #0f172a));
    font-weight: 900; font-size: 0.98rem; color: #fff;
}
.rx-card-header i,
.rx-card-header span,
.rx-card-header h3,
.rx-card-header p {
    color: #fff !important;
}

.rx-card-header > i {
    display: inline-flex;
    width: 2.65rem;
    height: 2.65rem;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
    background: rgba(255,255,255,0.16);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 14px 26px -24px rgba(15,23,42,0.52);
}

.rx-card-medication { --rx-accent: #0891b2; --rx-soft: rgba(207,250,254,0.92); padding: 0; }
.rx-card-metadata { --rx-accent: #4f46e5; --rx-soft: rgba(238,242,255,0.94); }
.rx-card-instructions { --rx-accent: #0f766e; --rx-soft: rgba(204,251,241,0.92); }
.rx-card-notice { --rx-accent: #d97706; --rx-soft: rgba(254,243,199,0.94); }

.rx-card-medication .rx-card-header {
    margin: 0;
    padding: 1.15rem 1.35rem;
    border-bottom: 0;
    background: radial-gradient(circle at 8% 0%, rgba(255,255,255,0.24), transparent 34%), linear-gradient(135deg, #0891b2, #0e7490 58%, #164e63);
}

.rx-section-subtitle {
    margin-top: 0.25rem;
    color: rgba(255,255,255,0.84);
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1.35;
}

.rx-medication-table-wrap {
    padding: 1rem;
    background: linear-gradient(135deg, rgba(236,254,255,0.64), rgba(255,255,255,0.88));
}

.rx-medication-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    border: 1px solid rgba(14,116,144,0.14);
    border-radius: 1rem;
    background: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 14px 30px -28px rgba(15,23,42,0.36);
}

.rx-medication-table thead {
    background: linear-gradient(135deg, #ecfeff, #f8fafc);
}

.rx-medication-table th {
    padding: 0.95rem 1.15rem;
    color: #334155;
    font-size: 0.73rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-align: start;
    white-space: nowrap;
}

.rx-medication-table td {
    padding: 1rem 1.15rem;
    color: #334155;
    font-size: 0.9rem;
    font-weight: 700;
    vertical-align: top;
}

.rx-medication-table tbody tr {
    background: rgba(255,255,255,0.86);
    transition: background 0.2s ease;
}

.rx-medication-table tbody tr:hover {
    background: rgba(236,254,255,0.75);
}

.rx-medicine-name {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 0;
    color: #0f172a;
    font-weight: 900;
}

.rx-medicine-icon {
    display: inline-flex;
    width: 2.2rem;
    height: 2.2rem;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    border-radius: 0.85rem;
    background: linear-gradient(135deg, #0891b2, #0e7490);
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 16px 28px -24px rgba(14,116,144,0.8);
}

.rx-info-list {
    display: grid;
    gap: 0.75rem;
}

.rx-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-height: 3.15rem;
    border-radius: 1rem;
    border: 1px solid rgba(255,255,255,0.72);
    background: rgba(255,255,255,0.76);
    padding: 0.8rem 0.9rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.86), 0 12px 24px -24px rgba(15,23,42,0.38);
}

.rx-info-label {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 800;
}

.rx-info-value {
    color: #0f172a;
    font-size: 0.88rem;
    font-weight: 900;
    text-align: end;
}

.rx-instructions-body {
    min-height: 8rem;
    border-radius: 1rem;
    border: 1px solid rgba(13,148,136,0.16);
    background: rgba(255,255,255,0.76);
    padding: 1rem;
    color: #1e293b;
    font-size: 0.95rem;
    font-weight: 750;
    line-height: 1.75;
}

.rx-notice-body {
    display: flex;
    gap: 0.85rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #d97706, #b45309 58%, #92400e);
    padding: 1rem;
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -28px rgba(146,64,14,0.7);
}

[dir="rtl"] .rx-notice-body { flex-direction: row-reverse; }

.rx-notice-body i,
.rx-notice-body p,
.rx-notice-body h4 {
    color: #fff !important;
}

.rx-empty-state {
    padding: 3rem 1.5rem;
    text-align: center;
    color: #64748b;
    font-size: 0.92rem;
    font-weight: 800;
}

[dir="rtl"] .rx-medication-table th,
[dir="rtl"] .rx-medication-table td {
    text-align: right;
}

[dir="rtl"] .rx-medicine-name,
[dir="rtl"] .rx-card-header {
    flex-direction: row-reverse;
}

.patient-hero-card {
    position: relative;
    --hero-angle: 135deg; --hero-start: #0f3f90; --hero-mid: #1656c1; --hero-end: #0f7cc1;
    --hero-orb-primary: rgba(255,255,255,0.18); --hero-orb-secondary: rgba(125,211,252,0.2);
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
[dir="rtl"] .patient-hero-chip { flex-direction: row-reverse; }
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
.patient-header-action-danger { background: linear-gradient(135deg, #f43f5e, #e11d48); color: #fff; box-shadow: 0 12px 22px -20px rgba(225,29,72,0.88); }
.patient-hero-tool-button {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.65rem; min-height: 2.4rem;
    border-radius: 1rem; padding: 0.48rem 0.72rem; font-size: 0.72rem; font-weight: 700; color: rgba(239,246,255,0.92);
    backdrop-filter: blur(12px); border: 1px dashed rgba(255,255,255,0.2); background: rgba(255,255,255,0.09);
    transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease; text-align: center;
}
</style>

<div class="min-h-screen bg-slate-50">
<div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">

    <!-- Hero Header -->
    <section class="patient-hero-card patient-hero-card-neutral overflow-hidden rounded-[28px] border border-slate-200 mb-8">
        <div class="patient-hero-overlay"></div>
        <div class="relative z-10 p-6 sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                        <span><?= $isRtl ? 'الوصفات' : 'Prescriptions' ?></span>
                        <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                        <span><?= esc($prescriptionReference) ?></span>
                    </div>
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                        <div class="patient-avatar-badge patient-avatar-badge-neutral">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($patientName) ?></h1>
                                    <span class="patient-hero-status inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold" style="background:<?= esc($statusTone['bg']) ?>;border-color:<?= esc($statusTone['border']) ?>;color:#eff6ff;"><?= esc($statusLabel) ?></span>
                                </div>
                            </div>
                            <div class="patient-hero-chip-grid">
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
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full space-y-2 xl:w-[21rem]">
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
                        <button type="button" onclick="deletePrescription(<?= $prescription['id'] ?>)" class="patient-header-action patient-header-action-danger">
                            <i class="fas fa-trash"></i><span><?= esc($tr('delete_prescription')) ?></span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

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
                                    <div class="flex items-center gap-2"><i class="fas fa-capsules text-cyan-700"></i><span><?= esc($tr('medicine_name')) ?></span></div>
                                </th>
                                <th>
                                    <div class="flex items-center gap-2"><i class="fas fa-weight-scale text-cyan-700"></i><span><?= esc($tr('dosage')) ?></span></div>
                                </th>
                                <th>
                                    <div class="flex items-center gap-2"><i class="fas fa-clock text-cyan-700"></i><span><?= esc($tr('frequency')) ?></span></div>
                                </th>
                                <th>
                                    <div class="flex items-center gap-2"><i class="fas fa-calendar-range text-cyan-700"></i><span><?= esc($tr('duration')) ?></span></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
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
                        <a href="<?= workflow_context_url('patient/' . $prescription['patient_id'], $return_to ?? null) ?>" class="rx-info-value text-blue-700 hover:text-blue-900 hover:underline"><?= esc($patientName) ?></a>
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
                    <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white/15">
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
