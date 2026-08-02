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
$invoiceableChargeLines = $examination_invoiceable_charge_lines ?? [];
$invoiceableChargeLineIds = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $invoiceableChargeLines);
$invoiceableChargeLineIds = array_values(array_filter($invoiceableChargeLineIds, static fn(int $id): bool => $id > 0));
$invoiceableChargeTotal = array_reduce(
    $invoiceableChargeLines,
    static fn(float $carry, array $row): float => $carry + max(0, (float) ($row['net_production_amount'] ?? 0)),
    0.0
);
$odontogramConditionCount = (int)($odontogram_stats['total_conditions'] ?? (is_array($odontogram ?? null) ? count($odontogram) : 0));
$returnToContext = 'examination/' . (int) ($examination['id'] ?? 0);
$clinicalNoteState = is_array($clinical_note_state ?? null) ? $clinical_note_state : ['signed' => false, 'signature' => null, 'addenda' => []];
$clinicalNoteSigned = !empty($clinicalNoteState['signed']);
$clinicalNoteSignature = $clinicalNoteState['signature'] ?? null;
$clinicalNoteAddenda = $clinicalNoteState['addenda'] ?? [];

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
}
.exam-card:hover { box-shadow: 0 1px 2px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04), 0 16px 36px -20px rgba(0,0,0,0.08); transform: translateY(-1px); }

/* Card background tints — anchored to hero header blues */
.exam-card-clinical { background: linear-gradient(180deg, #eff6ff 0%, #fff 100%); border-color: #bfdbfe; }
.exam-card-findings { background: linear-gradient(180deg, #eef2ff 0%, #fff 100%); border-color: #c7d2fe; }
.exam-card-careplan { background: linear-gradient(180deg, #f0fdf4 0%, #fff 100%); border-color: #bbf7d0; }
.exam-card-workflow { background: linear-gradient(180deg, #ecfeff 0%, #fff 100%); border-color: #a5f3fc; }
.exam-card-history { background: linear-gradient(180deg, #fdf2f8 0%, #fff 100%); border-color: #fbcfe8; }

.exam-card-header {
    display: flex; align-items: center; gap: 0.625rem;
    padding: 1.05rem 1.35rem;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 900; font-size: 1rem; color: #0f172a;
}
.exam-card-header .exam-icon-circle {
    display: inline-flex; height: 2rem; width: 2rem; align-items: center; justify-content: center;
    border-radius: 0.625rem; box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.exam-card-body { padding: 1.35rem; }

.exam-field-label {
    font-size: 0.72rem; font-weight: 900; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.52rem;
}
.exam-field-value { font-size: 0.96rem; font-weight: 700; color: #1e293b; line-height: 1.75; }
.exam-field-value p { margin-bottom: 0.25rem; }

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
.exam-action-secondary-warning {
    border-color: #fde68a;
    background: #fffbeb;
    color: #92400e;
}
.exam-action-secondary-warning:hover {
    background: #fef3c7;
    border-color: #fcd34d;
}

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

/* Examination detail visual contract: match treatment/user entity-detail hierarchy. */
.exam-page-shell {
    background:
        radial-gradient(900px 520px at 8% -12%, rgba(37, 99, 235, 0.09), transparent 60%),
        radial-gradient(760px 460px at 92% 0%, rgba(16, 185, 129, 0.08), transparent 55%),
        #f8fafc;
}
.exam-snapshot-section,
.exam-workspace-section {
    position: relative;
    overflow: hidden;
    border-radius: 1.6rem;
    border: 1px solid rgba(255,255,255,0.76);
    background:
        radial-gradient(circle at 0% 0%, rgba(37,99,235,0.11), transparent 28%),
        radial-gradient(circle at 100% 18%, rgba(16,185,129,0.12), transparent 30%),
        linear-gradient(135deg, rgba(248,250,252,0.98), rgba(255,255,255,0.96));
    box-shadow: 0 24px 58px -42px rgba(15,23,42,0.42), 0 1px 0 rgba(255,255,255,0.92) inset;
}
.exam-snapshot-section::before,
.exam-workspace-section::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 9% 0%, rgba(37,99,235,0.14), transparent 29%),
        radial-gradient(circle at 95% 10%, rgba(5,150,105,0.12), transparent 30%);
}
.exam-snapshot-section > *,
.exam-workspace-section > * {
    position: relative;
    z-index: 1;
}
.exam-snapshot-eyebrow {
    color: #1d4ed8;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
.exam-snapshot-title {
    color: #0f172a;
    font-size: 1.18rem;
    font-weight: 900;
    line-height: 1.2;
}
.exam-snapshot-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 0.9rem;
}
@media (min-width: 640px) { .exam-snapshot-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 1180px) { .exam-snapshot-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.exam-snapshot-card {
    position: relative;
    overflow: hidden;
    min-height: 7rem;
    border-radius: 1.2rem;
    padding: 1.05rem;
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -28px rgba(15,23,42,0.55);
}
.exam-snapshot-card::after {
    content: "";
    position: absolute;
    inset-block-start: -42%;
    inset-inline-end: -10%;
    width: 9.5rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.16);
}
.exam-snapshot-card > * { position: relative; z-index: 1; }
.exam-snapshot-card:nth-child(1) {
    background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a);
}
.exam-snapshot-card:nth-child(2) {
    background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #059669, #047857 55%, #064e3b);
}
.exam-snapshot-card:nth-child(3) {
    background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%), linear-gradient(135deg, #7c3aed, #6d28d9 56%, #4c1d95);
}
.exam-snapshot-card:nth-child(4) {
    background: radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #d97706, #b45309 58%, #92400e);
}
.exam-snapshot-label {
    font-size: 0.7rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(239,246,255,0.88);
}
.exam-snapshot-value {
    margin-top: 0.5rem;
    font-size: 1.18rem;
    line-height: 1.25;
    font-weight: 900;
    color: #fff;
}
.exam-snapshot-meta {
    margin-top: 0.35rem;
    font-size: 0.8rem;
    line-height: 1.35;
    color: rgba(239,246,255,0.84);
}
.exam-workspace-section { padding: 1rem; }
@media (min-width: 640px) { .exam-workspace-section { padding: 1.25rem; } }
.exam-section-heading {
    --section-start: #2563eb;
    --section-end: #0f766e;
    position: relative;
    overflow: hidden;
    border-radius: 1.4rem;
    padding: 1.1rem 1.25rem;
    color: #fff;
    background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.24), transparent 34%), linear-gradient(135deg, var(--section-start), var(--section-end));
    box-shadow: 0 22px 44px -32px color-mix(in srgb, var(--section-end) 78%, transparent);
}
.exam-section-heading::after {
    content: "";
    position: absolute;
    inset-block-start: -48%;
    inset-inline-end: -5%;
    width: 13rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.16);
}
.exam-section-heading > * { position: relative; z-index: 1; }
.exam-section-heading-icon {
    display: inline-flex;
    width: 2.65rem;
    height: 2.65rem;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
    background: rgba(255,255,255,0.18);
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.24);
}
.exam-section-heading p {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(239,246,255,0.88);
}
.exam-section-heading h2 {
    margin-top: 0.2rem;
    font-size: 1.05rem;
    line-height: 1.2;
    font-weight: 900;
    color: #fff;
}
.exam-section-heading--context { --section-start: #047857; --section-end: #1d4ed8; }
.exam-card {
    --exam-card-accent: #2563eb;
    --exam-card-accent-soft: rgba(239,246,255,0.88);
    border-radius: 1.2rem;
    border-color: rgba(255,255,255,0.82) !important;
    background:
        radial-gradient(circle at 8% 0%, color-mix(in srgb, var(--exam-card-accent) 15%, transparent), transparent 31%),
        radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
        linear-gradient(135deg, rgba(255,255,255,0.98), var(--exam-card-accent-soft) 46%, rgba(255,255,255,0.98)) !important;
    box-shadow: 0 20px 48px -38px rgba(15,23,42,0.48), 0 1px 0 rgba(255,255,255,0.92) inset !important;
}
.exam-card-clinical { --exam-card-accent: #2563eb; --exam-card-accent-soft: rgba(219,234,254,0.92); }
.exam-card-findings { --exam-card-accent: #4f46e5; --exam-card-accent-soft: rgba(238,242,255,0.92); }
.exam-card-careplan { --exam-card-accent: #059669; --exam-card-accent-soft: rgba(209,250,229,0.92); }
.exam-card-workflow { --exam-card-accent: #d97706; --exam-card-accent-soft: rgba(254,243,199,0.92); }
.exam-card-history { --exam-card-accent: #e11d48; --exam-card-accent-soft: rgba(255,228,230,0.92); }
.exam-card-header {
    min-height: 4rem;
    border-bottom-color: rgba(203,213,225,0.72) !important;
    background: linear-gradient(90deg, rgba(255,255,255,0.72), color-mix(in srgb, var(--exam-card-accent) 12%, white)) !important;
}
.exam-icon-circle {
    background: linear-gradient(135deg, var(--exam-card-accent), color-mix(in srgb, var(--exam-card-accent) 78%, #0f172a)) !important;
    color: #fff !important;
    box-shadow: 0 14px 24px -18px rgba(15,23,42,0.72) !important;
}
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border {
    position: relative;
    overflow: hidden;
    border: 0 !important;
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -30px rgba(15,23,42,0.58) !important;
    padding: 1.05rem !important;
}
.exam-card-careplan .rounded-xl.border:nth-child(1) {
    background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #0284c7, #0369a1 55%, #0c4a6e) !important;
}
.exam-card-careplan .rounded-xl.border:nth-child(2) {
    background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.22), transparent 34%), linear-gradient(135deg, #059669, #047857 55%, #064e3b) !important;
}
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border .exam-field-label,
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border .text-gray-500,
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border .text-xs {
    color: rgba(239,246,255,0.88) !important;
}
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border .text-gray-900,
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border .text-gray-700,
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border .text-sm,
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border .exam-field-value {
    color: #fff !important;
    font-weight: 800 !important;
    line-height: 1.55;
}
.exam-card-careplan .exam-card-body > .grid > .rounded-xl.border a {
    background: rgba(255,255,255,0.15) !important;
    border-color: rgba(255,255,255,0.18) !important;
    color: #fff !important;
}
.exam-card-findings .rounded-xl.border {
    border-color: rgba(203,213,225,0.82) !important;
    background: rgba(255,255,255,0.82) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 14px 28px -26px rgba(15,23,42,0.32);
}
.exam-action-primary {
    background: linear-gradient(135deg, #059669, #047857) !important;
    color: #fff !important;
    box-shadow: 0 18px 34px -26px rgba(5,150,105,0.72);
}
.exam-action-secondary {
    border-color: rgba(203,213,225,0.82) !important;
    background: rgba(255,255,255,0.86) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
}
.exam-action-secondary-warning {
    background: linear-gradient(135deg, #d97706, #b45309) !important;
    color: #fff !important;
    border-color: rgba(217,119,6,0.25) !important;
    box-shadow: 0 18px 34px -26px rgba(217,119,6,0.72);
}
.health-flag {
    border: 0 !important;
    border-inline-start: 4px solid #e11d48 !important;
    background: rgba(255,255,255,0.86) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 14px 28px -26px rgba(15,23,42,0.32);
}
.exam-text-tile,
.exam-notes-panel,
.exam-history-text-block {
    position: relative;
    overflow: hidden;
    min-height: 7rem;
    border-radius: 1rem;
    border: 1px solid rgba(203,213,225,0.78);
    background: rgba(255,255,255,0.88);
    padding: 1.12rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.92), 0 14px 30px -27px rgba(15,23,42,0.35);
}
.exam-text-tile::after,
.exam-notes-panel::after,
.exam-history-text-block::after {
    content: "";
    position: absolute;
    inset-block-start: -3.8rem;
    inset-inline-end: -2.8rem;
    width: 8.5rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.48);
    pointer-events: none;
}
.exam-text-tile > *,
.exam-notes-panel > *,
.exam-history-text-block > * {
    position: relative;
    z-index: 1;
}
.exam-text-tile-blue {
    border-inline-start: 5px solid #2563eb;
    background: radial-gradient(circle at 96% 0%, rgba(37,99,235,0.16), transparent 36%), linear-gradient(135deg, rgba(239,246,255,0.98), rgba(255,255,255,0.94));
}
.exam-text-tile-indigo {
    border-inline-start: 5px solid #4f46e5;
    background: radial-gradient(circle at 96% 0%, rgba(79,70,229,0.16), transparent 36%), linear-gradient(135deg, rgba(238,242,255,0.98), rgba(255,255,255,0.94));
}
.exam-text-tile-violet {
    border-inline-start: 5px solid #7c3aed;
    background: radial-gradient(circle at 96% 0%, rgba(124,58,237,0.16), transparent 36%), linear-gradient(135deg, rgba(245,243,255,0.98), rgba(255,255,255,0.94));
}
.exam-text-tile-emerald {
    border-inline-start: 5px solid #059669;
    background: radial-gradient(circle at 96% 0%, rgba(5,150,105,0.18), transparent 36%), linear-gradient(135deg, rgba(236,253,245,0.98), rgba(255,255,255,0.94));
}
.exam-text-tile-amber {
    border-inline-start: 5px solid #d97706;
    background: radial-gradient(circle at 96% 0%, rgba(217,119,6,0.18), transparent 36%), linear-gradient(135deg, rgba(255,251,235,0.98), rgba(255,255,255,0.94));
}
.exam-text-tile-sky {
    border-inline-start: 5px solid #0284c7;
    background: radial-gradient(circle at 96% 0%, rgba(2,132,199,0.16), transparent 36%), linear-gradient(135deg, rgba(240,249,255,0.98), rgba(255,255,255,0.94));
}
.exam-odontogram-callout {
    position: relative;
    overflow: hidden;
    border: 0 !important;
    background: radial-gradient(circle at 10% 0%, rgba(255,255,255,0.24), transparent 35%), linear-gradient(135deg, #2563eb, #0f766e 62%, #064e3b) !important;
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 36px -28px rgba(15,23,42,0.62);
    padding: 1.08rem !important;
}
.exam-odontogram-callout::after {
    content: "";
    position: absolute;
    inset-block-start: -4rem;
    inset-inline-end: -2rem;
    width: 11rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.14);
}
.exam-odontogram-callout > * { position: relative; z-index: 1; }
.exam-odontogram-callout .text-gray-800,
.exam-odontogram-callout .text-gray-500,
.exam-odontogram-callout .text-sm,
.exam-odontogram-callout .text-xs {
    color: rgba(255,255,255,0.88) !important;
}
.exam-odontogram-callout .text-gray-800 {
    color: #fff !important;
}
.exam-odontogram-callout a {
    border-color: rgba(255,255,255,0.18) !important;
    background: rgba(255,255,255,0.16) !important;
    color: #fff !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.16);
}
.exam-workflow-stack {
    display: grid;
    gap: 0.7rem;
}
.exam-money-trail-shell {
    border-radius: 1.2rem;
    background: radial-gradient(circle at 10% 0%, rgba(5,150,105,0.12), transparent 32%), linear-gradient(135deg, rgba(236,253,245,0.94), rgba(255,255,255,0.98));
    box-shadow: 0 20px 48px -38px rgba(15,23,42,0.5), inset 0 1px 0 rgba(255,255,255,0.9);
}
.exam-money-trail-shell > * {
    border-radius: 1.2rem !important;
}
.exam-notes-panel {
    min-height: 5.6rem;
    border-inline-start: 5px solid #0f766e;
    background: radial-gradient(circle at 96% 0%, rgba(15,118,110,0.16), transparent 36%), linear-gradient(135deg, rgba(240,253,250,0.98), rgba(255,255,255,0.94));
}
.exam-history-text-block {
    min-height: 5.6rem;
    border-inline-start: 5px solid #e11d48;
    background: radial-gradient(circle at 96% 0%, rgba(225,29,72,0.14), transparent 36%), linear-gradient(135deg, rgba(255,241,242,0.98), rgba(255,255,255,0.94));
}
.exam-text-tile .text-gray-400,
.exam-notes-panel .text-gray-400,
.exam-history-text-block .text-gray-400 {
    color: #64748b !important;
    font-weight: 700;
}
.exam-card .text-gray-500 {
    color: #475569 !important;
}
.exam-card .text-xs {
    line-height: 1.45;
}
.exam-treatment-chip {
    font-weight: 800 !important;
    letter-spacing: 0.01em;
}
.exam-history-empty {
    border-radius: 1rem;
    border: 1px dashed rgba(225,29,72,0.25);
    background: rgba(255,241,242,0.72);
    padding: 1rem;
    color: #9f1239 !important;
    font-weight: 800;
}
</style>

<div class="exam-page-shell min-h-screen bg-slate-50">
<div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">

    <!-- ===== Hero Header ===== -->
    <section class="patient-hero-card <?= esc($heroTheme) ?> overflow-hidden rounded-[28px] border border-slate-200 mb-8">
        <div class="patient-hero-overlay"></div>
        <div class="relative z-10 p-6 sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                        <span><?= esc($tr('examination_management')) ?></span>
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
                        <?php if (!$clinicalNoteSigned): ?>
                        <a href="<?= workflow_context_url('examination/' . $examination['id'] . '/edit', $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-pen"></i><span><?= esc($tr('edit_examination')) ?></span></a>
                        <?php endif; ?>
                        <?php if (!empty($examination['patient_id'])): ?>
                        <a href="<?= workflow_context_url('patient/' . $examination['patient_id'], $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-user"></i><span><?= esc(lang('UI.patientProfile')) ?></span></a>
                        <?php endif; ?>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <a href="<?= esc(workflow_back('examination', $return_to ?? null)) ?>" class="patient-header-action patient-header-action-soft"><i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back')) ?></span></a>
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

    <section class="exam-snapshot-section mb-6 p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="exam-snapshot-eyebrow"><?= esc($tr('examination_details')) ?></p>
                <h2 class="exam-snapshot-title"><?= esc($tr('examination_summary_help')) ?></h2>
            </div>
            <?php if (!empty($invoiceableChargeLineIds)): ?>
                <button type="button" data-app-drawer-open="examinationInvoiceDrawer" class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-700 px-3.5 py-2.5 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span><?= esc($tr('create_invoice_from_work')) ?></span>
                </button>
            <?php endif; ?>
        </div>
        <div class="exam-snapshot-grid">
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('detail_status')) ?></p>
                <p class="exam-snapshot-value"><?= esc($statusLabel) ?></p>
                <p class="exam-snapshot-meta"><?= esc($tr('examination_date')) ?>: <?= !empty($examination['examination_date']) ? esc(formatDate($examination['examination_date'])) : esc($tr('na')) ?></p>
            </div>
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('related_treatments')) ?></p>
                <p class="exam-snapshot-value"><?= esc((string) $treatmentCount) ?></p>
                <p class="exam-snapshot-meta"><?= $treatmentCount > 0 ? esc($tr('treatments_recorded', ['count' => $treatmentCount])) : esc($tr('no_treatments_recorded')) ?></p>
            </div>
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('dental_chart_odontogram')) ?></p>
                <p class="exam-snapshot-value"><?= esc((string) $odontogramConditionCount) ?></p>
                <p class="exam-snapshot-meta"><?= $odontogramConditionCount > 0 ? esc($tr('odontogram_conditions_recorded', ['count' => $odontogramConditionCount])) : esc($tr('no_odontogram_data_recorded')) ?></p>
            </div>
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('create_invoice_from_work')) ?></p>
                <p class="exam-snapshot-value" dir="ltr"><?= esc(formatCurrency($invoiceableChargeTotal)) ?></p>
                <p class="exam-snapshot-meta"><?= esc($tr('open_full_finance_page')) ?></p>
            </div>
        </div>
    </section>

    <!-- ===== Content ===== -->
    <div class="exam-workspace-section grid grid-cols-1 gap-6 xl:grid-cols-12">

        <!-- LEFT COLUMN -->
        <div class="xl:col-span-8 space-y-6">
            <div class="exam-section-heading flex items-center gap-3">
                <span class="exam-section-heading-icon"><i class="fas fa-notes-medical"></i></span>
                <div>
                    <p><?= esc($tr('clinical_summary')) ?></p>
                    <h2><?= esc($tr('care_plan_help')) ?></h2>
                </div>
            </div>

            <!-- Chief Complaint + HPI -->
            <div class="exam-card exam-card-clinical">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-clipboard-list text-sm"></i></span>
                    <span><?= esc($tr('clinical_summary')) ?></span>
                </div>
                <div class="exam-card-body">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="exam-text-tile exam-text-tile-blue">
                            <p class="exam-field-label"><?= esc($tr('chief_complaint')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['chief_complaint']) ? nl2br(esc($examination['chief_complaint'])) : '<span class="text-gray-400">' . esc($tr('na')) . '</span>' ?></div>
                        </div>
                        <div class="exam-text-tile exam-text-tile-indigo">
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
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="exam-text-tile exam-text-tile-violet">
                            <p class="exam-field-label"><?= esc($tr('clinical_findings')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['clinical_findings']) ? nl2br(esc($examination['clinical_findings'])) : '<span class="text-gray-400">' . esc($tr('no_clinical_findings_recorded')) . '</span>' ?></div>
                        </div>
                        <div class="exam-text-tile exam-text-tile-emerald">
                            <p class="exam-field-label"><?= esc($tr('diagnosis')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['diagnosis']) ? nl2br(esc($examination['diagnosis'])) : '<span class="text-gray-400">' . esc($tr('no_diagnosis_recorded')) . '</span>' ?></div>
                        </div>
                    </div>
                    <div class="exam-odontogram-callout rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?= esc($tr('dental_chart_odontogram')) ?></p>
                                <p class="text-xs text-gray-500 mt-0.5"><?= $odontogramConditionCount > 0 ? esc($tr('odontogram_conditions_recorded', ['count' => $odontogramConditionCount])) : esc($tr('no_odontogram_data_recorded')) ?></p>
                            </div>
                            <a href="<?= workflow_context_url('odontogram/' . (int) $examination['patient_id'] . '?examination_id=' . (int) $examination['id'], $returnToContext) ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-xs font-semibold text-sky-700 border border-gray-200 hover:bg-sky-50 hover:border-sky-200 transition-all shadow-sm">
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
                                <p class="mt-2 text-base font-black leading-snug text-gray-900"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p>
                                <p class="mt-1 text-sm font-bold text-gray-500"><?= esc($tr('total')) ?> <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?></p>
                                <a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-cyan-700 hover:text-cyan-800 transition-colors"><i class="fas fa-arrow-up-right-from-square"></i><?= esc($tr('view')) ?></a>
                            <?php else: ?>
                                <p class="mt-2 text-sm font-bold leading-relaxed text-gray-500"><?= esc($tr('treatment_plan_hint')) ?></p>
                                <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '') ?>" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-cyan-700 hover:text-cyan-800 transition-colors"><i class="fas fa-plus"></i><?= esc($tr('add_treatment_plan')) ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <!-- Treatments card -->
                        <div class="rounded-xl border p-4 transition-shadow hover:shadow-md" style="border-color:#d1fae5;background:#ecfdf5;">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="exam-field-label"><?= esc($tr('related_treatments')) ?></p>
                                    <p class="mt-2 text-sm font-bold leading-relaxed text-gray-500"><?= $treatmentCount > 0 ? esc($tr('treatments_recorded', ['count' => $treatmentCount])) : esc($tr('no_treatments_recorded')) ?></p>
                                </div>
                                <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= workflow_context_url('treatment/create?examination_id=' . (int) $examination['id'], $returnToContext) ?>" data-treatment-create-modal-open class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-emerald-700 border border-emerald-200 hover:bg-emerald-50 transition-all shadow-sm"><i class="fas fa-plus text-[10px]"></i><?= esc($tr('add_treatment')) ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($treatments)): ?>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <?php foreach ($treatments as $t): ?>
                                <a href="<?= base_url('treatment/' . $t['id']) ?>" class="exam-treatment-chip inline-flex rounded-lg bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all"><?= esc($t['treatment_name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Prognosis -->
                        <div class="exam-text-tile exam-text-tile-amber">
                            <p class="exam-field-label"><?= esc($tr('prognosis')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['prognosis']) ? nl2br(esc($examination['prognosis'])) : '<span class="text-gray-400">' . esc($tr('no_prognosis_recorded')) . '</span>' ?></div>
                        </div>
                        <!-- Recommendations -->
                        <div class="exam-text-tile exam-text-tile-sky">
                            <p class="exam-field-label"><?= esc($tr('recommendations')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['recommendations']) ? nl2br(esc($examination['recommendations'])) : '<span class="text-gray-400">' . esc($tr('no_recommendations_recorded')) . '</span>' ?></div>
                        </div>
                    </div>
                    <?php if (!empty($examination['examination_notes'])): ?>
                    <div class="exam-notes-panel">
                        <p class="exam-field-label"><?= esc($tr('examination_notes')) ?></p>
                        <div class="exam-field-value"><?= nl2br(esc($examination['examination_notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?= view('examination/partials/clinical_note_governance', [
                'examination' => $examination,
                'status_key' => $statusKey,
                'clinical_note_signed' => $clinicalNoteSigned,
                'clinical_note_signature' => $clinicalNoteSignature,
                'clinical_note_addenda' => $clinicalNoteAddenda,
            ]) ?>
        </div>

        <!-- RIGHT COLUMN -->
        <aside class="xl:col-span-4 xl:sticky xl:top-6 space-y-6">
            <div class="exam-section-heading exam-section-heading--context flex items-center gap-3">
                <span class="exam-section-heading-icon"><i class="fas fa-route"></i></span>
                <div>
                    <p><?= esc($tr('workflow_heading')) ?></p>
                    <h2><?= esc($tr('workflow_help')) ?></h2>
                </div>
            </div>

            <!-- Quick Workflow -->
            <div class="exam-card exam-card-workflow">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#fef3c7;color:#d97706;"><i class="fas fa-bolt text-sm"></i></span>
                    <span><?= esc($tr('workflow_heading')) ?></span>
                </div>
                <div class="exam-card-body exam-workflow-stack">
                    <a href="<?= workflow_context_url('odontogram/' . (int) $examination['patient_id'] . '?examination_id=' . (int) $examination['id'], $returnToContext) ?>" class="exam-action-primary" style="background:#2563eb;color:#fff;">
                        <i class="fas fa-tooth text-sm"></i>
                        <?= esc($tr('open_visit_chart')) ?>
                    </a>
                    <?php if ($statusKey !== 'completed'): ?>
                    <form method="post" action="<?= base_url('examination/' . $examination['id'] . '/complete') ?>">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?><input type="hidden" name="return_to" value="<?= esc($return_to) ?>"><?php endif; ?>
                        <button type="submit" class="exam-action-primary" style="background:#059669;color:#fff;"><?= esc($tr('finalize_assessment')) ?></button>
                    </form>
                    <?php endif; ?>
                    <?php if (has_permission('treatments', 'create')): ?>
                    <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '') ?>" class="exam-action-secondary"><i class="fas fa-file-signature text-sm"></i><?= esc(!empty($latest_contract) ? $tr('add_new_plan') : $tr('add_treatment_plan')) ?></a>
                    <a href="<?= workflow_context_url('treatment/create?examination_id=' . (int) $examination['id'], $returnToContext) ?>" data-treatment-create-modal-open class="exam-action-secondary"><i class="fas fa-plus text-sm"></i><?= esc($tr('add_treatment')) ?></a>
                    <?php endif; ?>
                    <?php if (has_permission('finance', 'create') && !empty($invoiceableChargeLineIds)): ?>
                    <button type="button" data-app-drawer-open="examinationInvoiceDrawer" class="exam-action-secondary exam-action-secondary-warning">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                        <span><?= esc($tr('create_invoice_from_work')) ?></span>
                        <span class="ms-auto text-xs font-black" dir="ltr"><?= esc(formatCurrency($invoiceableChargeTotal)) ?></span>
                    </button>
                    <?php endif; ?>
                    <?php if (has_permission('appointments', 'create')): ?>
                    <a href="<?= workflow_context_url('appointment/create?patient_id=' . (int) $examination['patient_id'] . '&appointment_type=follow_up&examination_id=' . (int) $examination['id'], $returnToContext) ?>" data-appointment-create-modal-open class="exam-action-secondary"><i class="fas fa-calendar-plus text-sm"></i><?= esc($tr('schedule_follow_up')) ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="exam-money-trail-shell">
                <?= view('components/money_trail_panel', [
                    'money_trail' => $money_trail ?? [],
                    'is_rtl' => $isRtl,
                    'return_to' => $returnToContext ?? null,
                ]) ?>
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
                    <div class="exam-history-text-block">
                        <p class="exam-field-label"><?= esc($tr('medical_history')) ?></p>
                        <div class="exam-field-value text-sm"><?= nl2br(esc($examination['medical_history'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($examination['dental_history'])): ?>
                    <div class="exam-history-text-block">
                        <p class="exam-field-label"><?= esc($tr('dental_history')) ?></p>
                        <div class="exam-field-value text-sm"><?= nl2br(esc($examination['dental_history'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (empty($healthFlags) && empty($examination['medical_history']) && empty($examination['dental_history'])): ?>
                    <p class="exam-history-empty text-sm py-2"><?= esc($tr('no_medical_dental_history_recorded')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>

</div>
</div>
<?= view('treatment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => $returnToContext,
    'contextual' => true,
    'selected_patient' => $selected_treatment_patient ?? null,
    'patients' => [],
    'treatment_types' => $treatment_types ?? [],
    'inventory_items' => $inventory_items ?? [],
    'clinic_providers' => $clinic_providers ?? [],
    'validation' => session()->getFlashdata('validation'),
    'selected_examination_id' => $selected_examination_id ?? ($examination['id'] ?? null),
    'selected_appointment_id' => $examination['appointment_id'] ?? null,
]) ?>
<?php if (has_permission('finance', 'create') && !empty($invoiceableChargeLineIds)): ?>
    <?php
    $examinationInvoiceDrawerId = 'examinationInvoiceDrawer';
    $examinationInvoiceFormId = 'examinationInvoiceDrawerForm';
    ob_start();
    ?>
    <form id="<?= esc($examinationInvoiceFormId) ?>"
          method="post"
          action="<?= base_url('finance/store') ?>"
          enctype="multipart/form-data"
          data-finance-calc
          data-provider-split
          data-base-currency="<?= esc($examination_finance_base_currency ?? settings()->get('currency', 'ILS')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
        <?= view('finance/partials/form_fields', [
            'contextual' => true,
            'id_prefix' => $examinationInvoiceDrawerId,
            'selected_patient' => $selected_treatment_patient ?? null,
            'patients' => !empty($selected_treatment_patient) ? [$selected_treatment_patient] : [],
            'transaction_type_preset' => 'invoice',
            'lock_transaction_type' => true,
            'base_currency' => $examination_finance_base_currency ?? settings()->get('currency', 'ILS'),
            'currency_options' => $examination_finance_currency_options ?? ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'liquidity_account_options' => $examination_finance_liquidity_options ?? [],
            'clinic_providers' => $clinic_providers ?? [],
            'clinical_charge_lines' => $invoiceableChargeLines,
            'selected_clinical_charge_line_ids' => $invoiceableChargeLineIds,
            'is_rtl' => $isRtl,
        ]) ?>
    </form>
    <?php $examinationInvoiceDrawerBody = (string) ob_get_clean(); ?>
    <?= view('components/app_drawer', [
        'id' => $examinationInvoiceDrawerId,
        'title' => $tr('create_invoice_from_work'),
        'description' => $tr('create_invoice_from_work_help'),
        'icon' => 'fas fa-file-invoice-dollar',
        'tone' => 'bg-amber-600',
        'size' => 'wide',
        'body' => $examinationInvoiceDrawerBody,
        'footer' => '<div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">'
            . '<button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" data-app-drawer-close="' . esc($examinationInvoiceDrawerId) . '">' . esc(lang('UI.cancel')) . '</button>'
            . '<a href="' . esc(workflow_context_url('finance/create?' . http_build_query([
                'patient_id' => (int) ($examination['patient_id'] ?? 0),
                'transaction_type' => 'invoice',
                'clinical_charge_line_ids' => $invoiceableChargeLineIds,
            ], '', '&', PHP_QUERY_RFC3986), $returnToContext)) . '" class="inline-flex items-center justify-center rounded-xl border border-amber-200 px-4 py-2.5 text-sm font-bold text-amber-800 hover:bg-amber-50">' . esc($tr('open_full_finance_page')) . '</a>'
            . '<button type="submit" form="' . esc($examinationInvoiceFormId) . '" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-700">' . esc(lang('Finance.createTransaction')) . '</button>'
            . '</div>',
    ]) ?>
    <script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<?php endif; ?>
<?= view('appointment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => $returnToContext,
    'contextual' => true,
    'selected_patient' => $selected_appointment_patient ?? null,
    'chair_options' => $appointment_chair_options ?? [],
    'requested_date' => date('Y-m-d'),
    'appointment_type_prefill' => $appointment_type_prefill ?? 'follow_up',
    'examination_id' => $examination['id'] ?? null,
]) ?>
<?= $this->endSection() ?>
