<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);

$typeLabels = ['initial' => $tr('type_initial'), 'periodic' => $tr('type_periodic'), 'emergency' => $tr('type_emergency'), 'follow_up' => $tr('type_follow_up')];
$statusLabels = ['pending' => $tr('status_pending'), 'in_progress' => $tr('status_in_progress'), 'completed' => $tr('status_completed'), 'cancelled' => $tr('status_cancelled')];
$typeKey = strtolower((string)($examination['examination_type'] ?? 'follow_up'));
$statusKey = strtolower((string)($examination['status'] ?? 'pending'));
$typeLabel = $typeLabels[$typeKey] ?? $tr('type_follow_up');
$statusLabel = $statusLabels[$statusKey] ?? $tr('status_pending');
$doctorName = trim(($examination['doctor_first_name'] ?? '') . ' ' . ($examination['doctor_last_name'] ?? ''));
$patientName = trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? ''));
$patientGender = strtolower((string)($examination['gender'] ?? 'other'));
$treatmentCount = is_array($treatments ?? null) ? count($treatments) : 0;
$odontogramConditionCount = (int)($odontogram_stats['total_conditions'] ?? (is_array($odontogram ?? null) ? count($odontogram) : 0));

$statusTone = match ($statusKey) {
    'completed'  => ['bg' => 'rgba(16,185,129,0.22)', 'border' => 'rgba(167,243,208,0.4)'],
    'in_progress' => ['bg' => 'rgba(59,130,246,0.22)', 'border' => 'rgba(147,197,253,0.4)'],
    'cancelled'   => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
    'pending'     => ['bg' => 'rgba(251,191,36,0.22)', 'border' => 'rgba(253,230,138,0.4)'],
    default       => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
};

$heroTheme  = 'patient-hero-card-neutral'; $avatarTone = 'patient-avatar-badge-neutral';
if ($patientGender === 'female') { $heroTheme = 'patient-hero-card-female'; $avatarTone = 'patient-avatar-badge-female'; }
elseif ($patientGender === 'male') { $heroTheme = 'patient-hero-card-male'; $avatarTone = 'patient-avatar-badge-male'; }

$healthFlags = array_values(array_filter([
    ['enabled' => !empty($examination['health_update_medical_changes'] ?? 0),    'label' => $tr('health_medical_changes'),     'icon' => 'fa-notes-medical',       'color' => 'emerald'],
    ['enabled' => !empty($examination['health_update_medication_changes'] ?? 0), 'label' => $tr('health_medication_changes'),   'icon' => 'fa-pills',               'color' => 'blue'],
    ['enabled' => !empty($examination['health_update_allergies_reactions'] ?? 0),'label' => $tr('health_allergies_reactions'),  'icon' => 'fa-allergies',           'color' => 'rose'],
    ['enabled' => !empty($examination['health_update_pregnancy'] ?? 0),          'label' => $tr('health_pregnancy'),            'icon' => 'fa-baby',                'color' => 'fuchsia'],
    ['enabled' => !empty($examination['health_update_current_symptoms'] ?? 0),   'label' => $tr('health_current_symptoms'),     'icon' => 'fa-triangle-exclamation','color' => 'amber'],
], fn($r) => !empty($r['enabled'])));
?>

<style>
/* ===== Shared hero-card CSS (matches patient/finance/treatment pages) ===== */
.patient-hero-card {
    position: relative;
    --hero-angle: 135deg; --hero-start: #0f3f90; --hero-mid: #1656c1; --hero-end: #0f7cc1;
    --hero-orb-primary: rgba(255,255,255,0.18); --hero-orb-secondary: rgba(125,211,252,0.2);
    background: linear-gradient(var(--hero-angle), var(--hero-start) 0%, var(--hero-mid) 58%, var(--hero-end) 100%);
    box-shadow: 0 28px 70px -44px rgba(15,23,42,0.75);
}
.patient-hero-card-male { --hero-start: #0f3f90; --hero-mid: #1656c1; --hero-end: #0f7cc1; }
.patient-hero-card-female { --hero-start: #8a245a; --hero-mid: #c03579; --hero-end: #7c3aed; --hero-orb-primary: rgba(255,255,255,0.16); --hero-orb-secondary: rgba(251,207,232,0.24); }
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
.patient-avatar-badge-male { background: linear-gradient(145deg, rgba(255,255,255,0.24), rgba(191,219,254,0.18)); }
.patient-avatar-badge-female { background: linear-gradient(145deg, rgba(255,255,255,0.24), rgba(244,114,182,0.18)); }
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
.patient-hero-chip-label { flex-shrink: 0; font-size: 0.68rem; font-weight: 600; line-height: 1.2; color: rgba(219,234,254,0.84); }
.patient-hero-chip-value { display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.76rem; font-weight: 500; line-height: 1.25; color: #fff; }
.patient-hero-chip-ltr .patient-hero-chip-value { direction: ltr; }
.patient-header-action {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.58rem; min-height: 2.65rem;
    border-radius: 0.95rem; padding: 0.58rem 0.78rem; font-size: 0.76rem; font-weight: 700;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease; text-align: center;
}
[dir="rtl"] .patient-header-action { flex-direction: row-reverse; }
.patient-header-action:hover { transform: translateY(-1px); }
.patient-header-action-soft { border: 1px solid rgba(255,255,255,0.16); background: rgba(255,255,255,0.12); color: #eff6ff; }
.patient-header-action-emerald { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 12px 22px -20px rgba(5,150,105,0.85); }

/* ===== Examination-specific cards ===== */
.exam-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1.125rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 2px 6px rgba(0,0,0,0.02), 0 10px 24px -16px rgba(0,0,0,0.04);
    transition: box-shadow 0.25s ease, transform 0.25s ease;
    position: relative; overflow: hidden;
}
.exam-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    border-radius: 1.125rem 1.125rem 0 0;
}
[dir="rtl"] .exam-card::before { left: 0; right: auto; }
.exam-card:hover { box-shadow: 0 1px 2px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04), 0 16px 36px -20px rgba(0,0,0,0.08); transform: translateY(-1px); }

.exam-card-header {
    display: flex; align-items: center; gap: 0.625rem;
    padding: 1.125rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 700; font-size: 0.9375rem; color: #0f172a;
}
.exam-card-header .exam-icon-circle {
    display: inline-flex; height: 2rem; width: 2rem; align-items: center; justify-content: center;
    border-radius: 0.625rem; box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.exam-card-body { padding: 1.5rem; }

.exam-field-label {
    font-size: 0.6875rem; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;
}
.exam-field-value { font-size: 0.875rem; color: #334155; line-height: 1.7; }
.exam-field-value p { margin-bottom: 0.25rem; }

/* Card top accent stripes — one per card position */
.exam-card-clinical::before { background: linear-gradient(90deg, #0284c7, #38bdf8); }
.exam-card-findings::before { background: linear-gradient(90deg, #059669, #34d399); }
.exam-card-careplan::before { background: linear-gradient(90deg, #d97706, #fbbf24); }
.exam-card-workflow::before { background: linear-gradient(90deg, #d97706, #fbbf24); }
.exam-card-history::before { background: linear-gradient(90deg, #e11d48, #fb7185); }

/* workflow action buttons */
.exam-action-primary {
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    width: 100%; padding: 0.75rem 1rem; border-radius: 0.875rem;
    font-size: 0.8125rem; font-weight: 700; transition: all 0.2s ease;
}
.exam-action-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.exam-action-secondary {
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    width: 100%; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0;
    background: #fff; color: #475569; font-size: 0.8125rem; font-weight: 600;
    transition: all 0.2s ease;
}
.exam-action-secondary:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.04); }

/* health flag pill */
.health-flag {
    display: flex; align-items: center; gap: 0.625rem; padding: 0.625rem 0.875rem;
    border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #fff;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
    border-left-width: 3px;
}
.health-flag:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.health-flag-dot { width: 0.5rem; height: 0.5rem; border-radius: 9999px; flex-shrink: 0; }
.health-flag i { font-size: 0.875rem; width: 1.25rem; text-align: center; }
</style>

<div class="min-h-screen bg-slate-50">
<div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">

    <!-- ===== Hero Header ===== -->
    <section class="patient-hero-card <?= esc($heroTheme) ?> overflow-hidden rounded-[28px] border border-slate-200 mb-8">
        <div class="patient-hero-overlay"></div>
        <div class="relative z-10 p-6 sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                        <span><?= $isRtl ? 'الفحوصات' : 'Examinations' ?></span>
                        <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                        <span><?= esc($examination['examination_id'] ?? ('EX-' . $examination['id'])) ?></span>
                    </div>
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                        <div class="patient-avatar-badge <?= esc($avatarTone) ?>"><i class="fas fa-stethoscope"></i></div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($patientName ?: $tr('examination_details')) ?></h1>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold text-white" style="background:<?= esc($statusTone['bg']) ?>;border-color:<?= esc($statusTone['border']) ?>;"><?= esc($statusLabel) ?></span>
                                </div>
                            </div>
                            <div class="patient-hero-chip-grid">
                                <div class="patient-hero-chip patient-hero-chip-ltr" title="<?= esc($tr('patient_number') . ': ' . ($examination['patient_number'] ?? '')) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-id-card"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('patient_number')) ?></span><span class="patient-hero-chip-value"><?= esc($examination['patient_number'] ?? $tr('na')) ?></span></span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('detail_examination_type') . ': ' . $typeLabel) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-stethoscope"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('detail_examination_type')) ?></span><span class="patient-hero-chip-value"><?= esc($typeLabel) ?></span></span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('examination_date') . ': ' . (!empty($examination['examination_date']) ? formatDateTime($examination['examination_date']) : $tr('na'))) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-calendar-day"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('examination_date')) ?></span><span class="patient-hero-chip-value"><?= !empty($examination['examination_date']) ? formatDateTime($examination['examination_date']) : $tr('na') ?></span></span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('created_by') . ': ' . ($doctorName ?: $tr('unknown'))) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-user-doctor"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('created_by')) ?></span><span class="patient-hero-chip-value"><?= esc($doctorName ?: $tr('unknown')) ?></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full space-y-2 xl:w-[21rem]">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <a href="<?= base_url('examination/' . $examination['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-pen"></i><span><?= esc($tr('edit_examination')) ?></span></a>
                        <?php if (!empty($examination['patient_id'])): ?>
                        <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-user"></i><span><?= esc($tr('back_to_patient')) ?></span></a>
                        <?php endif; ?>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="patient-header-action patient-header-action-soft"><i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back_to_examinations')) ?></span></a>
                    </div>
                    <?php if ($statusKey !== 'completed'): ?>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <form method="post" action="<?= base_url('examination/' . $examination['id'] . '/complete') ?>">
                            <?= csrf_field() ?>
                            <?php if (!empty($return_to)): ?><input type="hidden" name="return_to" value="<?= esc($return_to) ?>"><?php endif; ?>
                            <button type="submit" class="patient-header-action patient-header-action-emerald w-full"><i class="fas fa-check-circle"></i><span><?= esc($tr('finalize_assessment')) ?></span></button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== Content ===== -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">

        <!-- LEFT COLUMN -->
        <div class="xl:col-span-8 space-y-6">

            <!-- Chief Complaint + HPI -->
            <div class="exam-card exam-card-clinical">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-clipboard-list text-sm"></i></span>
                    <span><?= esc($tr('clinical_summary')) ?></span>
                </div>
                <div class="exam-card-body">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <p class="exam-field-label"><?= esc($tr('chief_complaint')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['chief_complaint']) ? nl2br(esc($examination['chief_complaint'])) : '<span class="text-gray-400">' . esc($tr('na')) . '</span>' ?></div>
                        </div>
                        <div>
                            <p class="exam-field-label"><?= esc($tr('history_of_present_illness')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['history_of_present_illness']) ? nl2br(esc($examination['history_of_present_illness'])) : '<span class="text-gray-400">' . esc($tr('na')) . '</span>' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Findings + Diagnosis -->
            <div class="exam-card exam-card-findings">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#d1fae5;color:#059669;"><i class="fas fa-magnifying-glass text-sm"></i></span>
                    <span><?= esc($tr('clinical_findings')) ?> &amp; <?= esc($tr('diagnosis')) ?></span>
                </div>
                <div class="exam-card-body space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <p class="exam-field-label"><?= esc($tr('clinical_findings')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['clinical_findings']) ? nl2br(esc($examination['clinical_findings'])) : '<span class="text-gray-400">' . esc($tr('no_clinical_findings_recorded')) . '</span>' ?></div>
                        </div>
                        <div>
                            <p class="exam-field-label"><?= esc($tr('diagnosis')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['diagnosis']) ? nl2br(esc($examination['diagnosis'])) : '<span class="text-gray-400">' . esc($tr('no_diagnosis_recorded')) . '</span>' ?></div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?= esc($tr('dental_chart_odontogram')) ?></p>
                                <p class="text-xs text-gray-500 mt-0.5"><?= $odontogramConditionCount > 0 ? esc($tr('odontogram_conditions_recorded', ['count' => $odontogramConditionCount])) : esc($tr('no_odontogram_data_recorded')) ?></p>
                            </div>
                            <a href="<?= base_url('odontogram/' . $examination['patient_id'] . '?return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-xs font-semibold text-sky-700 border border-gray-200 hover:bg-sky-50 hover:border-sky-200 transition-all shadow-sm">
                                <i class="fas fa-tooth"></i><span><?= esc($odontogramConditionCount > 0 ? $tr('view_full_odontogram') : $tr('create_odontogram')) ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Care Plan -->
            <div class="exam-card exam-card-careplan">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clipboard-check text-sm"></i></span>
                    <span><?= esc($tr('care_plan_heading')) ?></span>
                </div>
                <div class="exam-card-body space-y-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Treatment Plan card -->
                        <div class="rounded-xl border p-4 transition-shadow hover:shadow-md" style="border-color:#cffafe;background:#f0fdfa;">
                            <p class="exam-field-label"><?= esc($tr('treatment_plan')) ?></p>
                            <?php if (!empty($latest_contract)): ?>
                                <p class="text-sm font-semibold text-gray-900 mt-1"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p>
                                <p class="text-xs text-gray-500 mt-0.5"><?= esc($tr('total')) ?> <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?></p>
                                <a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-cyan-700 hover:text-cyan-800 transition-colors"><i class="fas fa-arrow-up-right-from-square"></i><?= esc($tr('view')) ?></a>
                            <?php else: ?>
                                <p class="text-sm text-gray-500 mt-1"><?= esc($tr('treatment_plan_hint')) ?></p>
                                <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-cyan-700 hover:text-cyan-800 transition-colors"><i class="fas fa-plus"></i><?= esc($tr('add_treatment_plan')) ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <!-- Treatments card -->
                        <div class="rounded-xl border p-4 transition-shadow hover:shadow-md" style="border-color:#d1fae5;background:#ecfdf5;">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="exam-field-label"><?= esc($tr('related_treatments')) ?></p>
                                    <p class="text-sm text-gray-500 mt-1"><?= $treatmentCount > 0 ? esc($tr('treatments_recorded', ['count' => $treatmentCount])) : esc($tr('no_treatments_recorded')) ?></p>
                                </div>
                                <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-emerald-700 border border-emerald-200 hover:bg-emerald-50 transition-all shadow-sm"><i class="fas fa-plus text-[10px]"></i><?= esc($tr('add_treatment')) ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($treatments)): ?>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <?php foreach ($treatments as $t): ?>
                                <a href="<?= base_url('treatment/' . $t['id']) ?>" class="inline-flex rounded-lg bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all"><?= esc($t['treatment_name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Prognosis -->
                        <div>
                            <p class="exam-field-label"><?= esc($tr('prognosis')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['prognosis']) ? nl2br(esc($examination['prognosis'])) : '<span class="text-gray-400">' . esc($tr('no_prognosis_recorded')) . '</span>' ?></div>
                        </div>
                        <!-- Recommendations -->
                        <div>
                            <p class="exam-field-label"><?= esc($tr('recommendations')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['recommendations']) ? nl2br(esc($examination['recommendations'])) : '<span class="text-gray-400">' . esc($tr('no_recommendations_recorded')) . '</span>' ?></div>
                        </div>
                    </div>
                    <?php if (!empty($examination['examination_notes'])): ?>
                    <div class="pt-4 border-t border-gray-100">
                        <p class="exam-field-label"><?= esc($tr('examination_notes')) ?></p>
                        <div class="exam-field-value"><?= nl2br(esc($examination['examination_notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="xl:col-span-4 space-y-6">
            <!-- Quick Workflow -->
            <div class="exam-card exam-card-workflow">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#fef3c7;color:#d97706;"><i class="fas fa-bolt text-sm"></i></span>
                    <span><?= esc($tr('workflow_heading')) ?></span>
                </div>
                <div class="exam-card-body">
                    <?php if ($statusKey !== 'completed'): ?>
                    <form method="post" action="<?= base_url('examination/' . $examination['id'] . '/complete') ?>" class="mb-4">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?><input type="hidden" name="return_to" value="<?= esc($return_to) ?>"><?php endif; ?>
                        <button type="submit" class="exam-action-primary" style="background:#059669;color:#fff;"><?= esc($tr('finalize_assessment')) ?></button>
                    </form>
                    <?php endif; ?>
                    <?php if (has_permission('treatments', 'create')): ?>
                    <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="exam-action-secondary mb-3"><i class="fas fa-file-signature text-sm"></i><?= esc(!empty($latest_contract) ? $tr('add_new_plan') : $tr('add_treatment_plan')) ?></a>
                    <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="exam-action-secondary mb-3"><i class="fas fa-plus text-sm"></i><?= esc($tr('add_treatment')) ?></a>
                    <?php endif; ?>
                    <?php if (has_permission('appointments', 'create')): ?>
                    <a href="<?= base_url('appointment/create?patient_id=' . $examination['patient_id'] . '&appointment_type=follow_up&examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="exam-action-secondary"><i class="fas fa-calendar-plus text-sm"></i><?= esc($tr('schedule_follow_up')) ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Health Flags -->
            <div class="exam-card exam-card-history">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#ffe4e6;color:#e11d48;"><i class="fas fa-heart-pulse text-sm"></i></span>
                    <span><?= esc($tr('medical_dental_history')) ?></span>
                </div>
                <div class="exam-card-body flex flex-col gap-3">
                    <?php if (!empty($healthFlags)): ?>
                        <?php foreach ($healthFlags as $f): ?>
                        <div class="health-flag">
                            <span class="health-flag-dot" style="background:var(--tw-<?= $f['color'] ?>-500, #94a3b8);"></span>
                            <i class="fas <?= $f['icon'] ?> text-gray-500"></i>
                            <span class="text-sm font-semibold text-gray-800"><?= esc($f['label']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($examination['medical_history'])): ?>
                    <div class="pt-2">
                        <p class="exam-field-label"><?= esc($tr('medical_history')) ?></p>
                        <div class="exam-field-value text-sm"><?= nl2br(esc($examination['medical_history'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($examination['dental_history'])): ?>
                    <div class="pt-2">
                        <p class="exam-field-label"><?= esc($tr('dental_history')) ?></p>
                        <div class="exam-field-value text-sm"><?= nl2br(esc($examination['dental_history'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (empty($healthFlags) && empty($examination['medical_history']) && empty($examination['dental_history'])): ?>
                    <p class="text-sm text-gray-400 py-2"><?= esc($tr('no_medical_dental_history_recorded')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<?= $this->endSection() ?>
