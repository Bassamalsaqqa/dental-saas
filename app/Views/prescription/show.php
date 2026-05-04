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
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
    padding: 1.25rem 1.5rem;
}
.rx-card-header {
    display: flex; align-items: center; gap: 0.625rem;
    margin-bottom: 1rem; padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 700; font-size: 0.9375rem; color: #1e293b;
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
                        <a href="<?= base_url('patient/' . $prescription['patient_id']) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas fa-user"></i><span><?= esc($tr('back_to_patient')) ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('prescription') ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back_to_prescriptions')) ?></span>
                        </a>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php if (has_permission('prescriptions', 'edit')): ?>
                        <a href="<?= base_url('prescription/' . $prescription['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="patient-header-action patient-header-action-soft">
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
            <div class="rx-card">
                <div class="rx-card-header">
                    <i class="fas fa-pills text-cyan-600"></i><span><?= esc($tr('medication_plan')) ?></span>
                </div>
                <?php if (!empty($medicines)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                                <th class="px-3 py-2.5"><?= esc($tr('medicine_name')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('dosage')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('frequency')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('duration')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($medicines as $m): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3 font-semibold text-gray-900"><?= esc($m['name'] ?? '—') ?></td>
                                    <td class="px-3 py-3 text-gray-700"><?= esc($m['dosage'] ?? '—') ?></td>
                                    <td class="px-3 py-3 text-gray-700"><?= esc($m['frequency'] ?? '—') ?></td>
                                    <td class="px-3 py-3 text-gray-700"><?= esc($m['duration'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-sm text-gray-400 text-center py-6"><?= esc($tr('no_medication_information_available')) ?></p>
                <?php endif; ?>
            </div>

            <!-- Instructions -->
            <div class="rx-card">
                <div class="rx-card-header">
                    <i class="fas fa-circle-info text-teal-600"></i><span><?= esc($tr('important_instructions')) ?></span>
                </div>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                        <?= nl2br(esc($prescription['instructions'] ?? $tr('no_instructions_provided'))) ?>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-red-800 mb-2">
                            <i class="fas fa-triangle-exclamation text-red-600"></i>
                            <span><?= esc($tr('emergency_notice')) ?></span>
                        </h3>
                        <p class="text-sm leading-relaxed text-red-900"><?= esc($tr('emergency_notice_body')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Patient + Metadata -->
        <div class="space-y-6">
            <?php if (!empty($prescription['patient_id'])): ?>
            <div class="rx-card">
                <div class="rx-card-header">
                    <i class="fas fa-user text-purple-600"></i><span><?= esc($tr('patient_profile')) ?></span>
                </div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-cyan-50 text-xl font-bold text-cyan-700 shadow-sm flex-shrink-0">
                        <?= esc(mb_substr($patientName ?: 'P', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900"><?= esc($patientName ?: $tr('na')) ?></p>
                        <p class="text-xs text-gray-500">ID: <?= esc($prescription['patient_id'] ?? '—') ?></p>
                    </div>
                </div>
                <div class="space-y-2 text-sm border-t border-gray-100 pt-4">
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('phone')) ?></span><span class="text-gray-900"><?= esc(trim((string)($prescription['phone']??''))?:$tr('na')) ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('email')) ?></span><span class="text-gray-900 truncate max-w-[60%]"><?= esc(trim((string)($prescription['email']??''))?:$tr('na')) ?></span></div>
                </div>
                <a href="<?= base_url('patient/' . $prescription['patient_id']) ?>" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800">
                    <i class="fas fa-external-link-alt"></i><?= $isRtl ? 'فتح ملف المريض' : 'Open patient profile' ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="rx-card">
                <div class="rx-card-header">
                    <i class="fas fa-clipboard-list text-indigo-600"></i><span><?= esc($tr('rx_metadata')) ?></span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('prescription_id')) ?></span><span class="font-mono text-gray-900"><?= esc($prescriptionReference) ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('status')) ?></span><span class="text-gray-900"><?= esc($statusLabel) ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('created_by')) ?></span><span class="text-gray-900"><?= esc($doctorName ?: $tr('unknown')) ?></span></div>
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
