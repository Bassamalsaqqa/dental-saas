<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$tr = static fn(string $key, array $params = []) => lang('Treatment.' . $key, $params);
$doctorName = trim(($treatment['doctor_first_name'] ?? '') . ' ' . ($treatment['doctor_last_name'] ?? ''));
$patientName = trim(($treatment['first_name'] ?? '') . ' ' . ($treatment['last_name'] ?? ''));
$statusRaw = $treatment['status'] ?? 'active';
$statusLabel = lang('Treatment.status_values.' . $statusRaw);
if ($statusLabel === 'Treatment.status_values.' . $statusRaw) { $statusLabel = ucfirst(str_replace('_', ' ', $statusRaw)); }
$typeLabel = ucfirst(str_replace('_', ' ', $treatment['treatment_type'] ?? ''));
$statusTone = match ($statusRaw) {
    'active' => ['bg' => 'rgba(59,130,246,0.22)', 'border' => 'rgba(147,197,253,0.4)'],
    'completed' => ['bg' => 'rgba(16,185,129,0.22)', 'border' => 'rgba(167,243,208,0.4)'],
    'on_hold' => ['bg' => 'rgba(251,191,36,0.22)', 'border' => 'rgba(253,230,138,0.4)'],
    'cancelled' => ['bg' => 'rgba(244,63,94,0.22)', 'border' => 'rgba(254,205,211,0.4)'],
    default => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
};
?>

<style>
.treatment-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
    padding: 1.25rem 1.5rem;
}
.treatment-card-header {
    display: flex; align-items: center; gap: 0.625rem;
    margin-bottom: 1rem; padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 700; font-size: 0.9375rem; color: #1e293b;
}
.treatment-card-header i { opacity: 0.7; }

/* Hero card CSS (shared from patient pattern) */
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
.patient-header-action-emerald { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 12px 22px -20px rgba(5,150,105,0.85); }
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
                        <span><?= $isRtl ? 'العلاجات' : 'Treatments' ?></span>
                        <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                        <span><?= esc($treatment['treatment_id'] ?? '') ?></span>
                    </div>
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                        <div class="patient-avatar-badge patient-avatar-badge-neutral">
                            <i class="fas fa-tooth"></i>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($treatment['treatment_name'] ?? $tr('treatment_details')) ?></h1>
                                    <span class="patient-hero-status inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold" style="background:<?= esc($statusTone['bg']) ?>;border-color:<?= esc($statusTone['border']) ?>;color:#eff6ff;"><?= esc($statusLabel) ?></span>
                                </div>
                            </div>
                            <div class="patient-hero-chip-grid">
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
                <div class="w-full space-y-2 xl:w-[21rem]">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php if (!empty($treatment['patient_id'])): ?>
                        <a href="<?= base_url('patient/' . $treatment['patient_id']) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas fa-user"></i><span><?= esc($tr('back_to_patient')) ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="<?= esc(workflow_back('treatment', $return_to ?? null)) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back_to_treatments')) ?></span>
                        </a>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <a href="<?= base_url('treatment/' . $treatment['id'] . '/edit') ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas fa-pen"></i><span><?= esc($tr('edit_treatment')) ?></span>
                        </a>
                        <button type="button" onclick="printTreatment(<?= $treatment['id'] ?>)" class="patient-hero-tool-button">
                            <i class="fas fa-print"></i><span><?= esc($tr('print_treatment')) ?></span>
                        </button>
                    </div>
                    <?php if ($statusRaw === 'active'): ?>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <button type="button" onclick="completeTreatment(<?= $treatment['id'] ?>)" class="patient-header-action patient-header-action-emerald">
                            <i class="fas fa-check"></i><span><?= esc($tr('mark_as_completed')) ?></span>
                        </button>
                    </div>
                    <?php endif; ?>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <button type="button" onclick="deleteTreatment(<?= $treatment['id'] ?>)" class="patient-header-action patient-header-action-danger">
                            <i class="fas fa-trash"></i><span><?= esc($tr('delete_treatment')) ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Treatment Description -->
            <div class="treatment-card">
                <div class="treatment-card-header">
                    <i class="fas fa-align-left text-blue-600"></i><span><?= esc($tr('treatment_description')) ?></span>
                </div>
                <p class="text-sm text-gray-700 leading-relaxed"><?= !empty($treatment['treatment_description']) ? nl2br(htmlspecialchars($treatment['treatment_description'])) : '<span class="text-gray-400 italic">' . esc($tr('no_description_provided')) . '</span>' ?></p>
            </div>

            <!-- Treatment Notes -->
            <?php if (!empty($treatment['treatment_notes'])): ?>
            <div class="treatment-card">
                <div class="treatment-card-header">
                    <i class="fas fa-sticky-note text-amber-500"></i><span><?= esc($tr('treatment_notes')) ?></span>
                </div>
                <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($treatment['treatment_notes'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Inventory Usage -->
            <?php if (!empty($usage_records)): ?>
            <div class="treatment-card">
                <div class="treatment-card-header">
                    <i class="fas fa-boxes text-emerald-600"></i><span><?= esc($tr('inventory_usage')) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                                <th class="px-3 py-2.5"><?= esc($tr('date')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('items_used')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('cost')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('notes')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($usage_records as $usage):
                                $items = json_decode($usage['items_used'] ?? '[]', true);
                                $itemList = [];
                                if (is_array($items)) {
                                    foreach ($items as $item) { $itemList[] = ($item['item_name'] ?? 'Item') . ' ×' . ($item['quantity_used'] ?? 0); }
                                }
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2.5 text-gray-700"><?= !empty($usage['usage_date']) ? formatDate($usage['usage_date']) : '—' ?></td>
                                    <td class="px-3 py-2.5 text-gray-700"><?= !empty($itemList) ? esc(implode(', ', $itemList)) : '—' ?></td>
                                    <td class="px-3 py-2.5 font-semibold text-emerald-700"><?= formatCurrency((float) ($usage['total_cost'] ?? 0)) ?></td>
                                    <td class="px-3 py-2.5 text-gray-500"><?= !empty($usage['notes']) ? esc($usage['notes']) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">

            <!-- Patient Info -->
            <?php if (!empty($treatment['patient_id'])): ?>
            <div class="treatment-card">
                <div class="treatment-card-header">
                    <i class="fas fa-user text-purple-600"></i><span><?= esc($tr('patient_information')) ?></span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('patient_name')) ?></span><span class="font-medium text-gray-900"><?= esc($patientName) ?></span></div>
                    <?php if (!empty($treatment['phone'])): ?><div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('phone')) ?></span><span class="text-gray-900"><?= esc($treatment['phone']) ?></span></div><?php endif; ?>
                </div>
                <a href="<?= base_url('patient/' . $treatment['patient_id']) ?>" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800">
                    <i class="fas fa-external-link-alt"></i><?= $isRtl ? 'فتح ملف المريض' : 'Open patient profile' ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Timeline -->
            <div class="treatment-card">
                <div class="treatment-card-header">
                    <i class="fas fa-clock text-indigo-600"></i><span><?= esc($tr('treatment_timeline')) ?></span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('start_date')) ?></span><span class="text-gray-900"><?= !empty($treatment['start_date']) ? formatDate($treatment['start_date']) : '—' ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('completion_date')) ?></span><span class="text-gray-900"><?= !empty($treatment['completion_date']) ? formatDateTime($treatment['completion_date']) : esc($tr('not_completed')) ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('created_by')) ?></span><span class="text-gray-900"><?= $doctorName !== '' ? esc($doctorName) : '—' ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('last_updated')) ?></span><span class="text-gray-900 text-xs"><?= !empty($treatment['updated_at']) ? formatDateTime($treatment['updated_at']) : '—' ?></span></div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

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
<?= $this->endSection() ?>
