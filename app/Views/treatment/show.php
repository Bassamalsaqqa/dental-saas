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
    ? 'border-slate-200 bg-slate-50 text-slate-700'
    : ($chargeLineIsOpen ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800');
$invoiceBalance = (float) ($moneySummary['balance_due'] ?? 0);
$providerRemaining = (float) ($moneySummary['provider_remaining'] ?? 0);
$inventoryCost = (float) ($moneySummary['inventory_cost'] ?? 0);
$statusTone = match ($statusRaw) {
    'active' => ['bg' => 'rgba(59,130,246,0.22)', 'border' => 'rgba(147,197,253,0.4)'],
    'completed' => ['bg' => 'rgba(16,185,129,0.22)', 'border' => 'rgba(167,243,208,0.4)'],
    'on_hold' => ['bg' => 'rgba(251,191,36,0.22)', 'border' => 'rgba(253,230,138,0.4)'],
    'cancelled' => ['bg' => 'rgba(244,63,94,0.22)', 'border' => 'rgba(254,205,211,0.4)'],
    default => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
};
?>

<style>
.treatment-page-shell {
    font-family: var(--app-font-sans, "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Roboto", "Noto Sans", sans-serif);
}
[dir="rtl"] .treatment-page-shell {
    font-family: var(--app-font-ar, "Noto Sans Arabic", "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Noto Sans", sans-serif);
}
.treatment-card {
    position: relative;
    border: 1px solid rgba(255,255,255,0.32);
    border-radius: 1.25rem;
    background:
        radial-gradient(circle at 92% 8%, rgba(255,255,255,0.24), transparent 30%),
        linear-gradient(135deg, rgba(30,64,175,0.9), rgba(14,116,144,0.82));
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 42px -30px rgba(15,23,42,0.58);
    overflow: hidden;
}
.treatment-card::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 8% 18%, rgba(255,255,255,0.16), transparent 32%),
        radial-gradient(circle at 88% 88%, rgba(125,211,252,0.18), transparent 34%),
        linear-gradient(120deg, rgba(255,255,255,0.06), rgba(255,255,255,0));
    opacity: 1;
}
.treatment-card > * {
    position: relative;
    z-index: 1;
}
.treatment-card--clinical {
    border-color: rgba(191,219,254,0.42);
    background:
        radial-gradient(circle at 88% 10%, rgba(255,255,255,0.22), transparent 32%),
        radial-gradient(circle at 12% 92%, rgba(34,211,238,0.22), transparent 34%),
        linear-gradient(135deg, rgba(30,64,175,0.9) 0%, rgba(14,116,144,0.84) 100%);
}
.treatment-card--notes {
    border-color: rgba(253,230,138,0.42);
    background:
        radial-gradient(circle at 88% 10%, rgba(255,255,255,0.2), transparent 32%),
        radial-gradient(circle at 12% 92%, rgba(251,191,36,0.24), transparent 34%),
        linear-gradient(135deg, rgba(180,83,9,0.88) 0%, rgba(15,118,110,0.78) 100%);
}
.treatment-card--inventory {
    border-color: rgba(167,243,208,0.42);
    background:
        radial-gradient(circle at 88% 10%, rgba(255,255,255,0.22), transparent 32%),
        radial-gradient(circle at 12% 92%, rgba(45,212,191,0.24), transparent 34%),
        linear-gradient(135deg, rgba(6,95,70,0.9) 0%, rgba(14,116,144,0.78) 100%);
}
.treatment-card--patient {
    border-color: rgba(196,181,253,0.44);
    background:
        radial-gradient(circle at 88% 10%, rgba(255,255,255,0.22), transparent 32%),
        radial-gradient(circle at 12% 92%, rgba(96,165,250,0.22), transparent 34%),
        linear-gradient(135deg, rgba(91,33,182,0.86) 0%, rgba(30,64,175,0.78) 100%);
}
.treatment-card--billing {
    border-color: rgba(94,234,212,0.44);
    background:
        radial-gradient(circle at 88% 10%, rgba(255,255,255,0.22), transparent 32%),
        radial-gradient(circle at 12% 92%, rgba(16,185,129,0.24), transparent 34%),
        linear-gradient(135deg, rgba(4,120,87,0.9) 0%, rgba(21,94,117,0.78) 100%);
}
.treatment-card--timeline {
    border-color: rgba(165,180,252,0.44);
    background:
        radial-gradient(circle at 88% 10%, rgba(255,255,255,0.22), transparent 32%),
        radial-gradient(circle at 12% 92%, rgba(129,140,248,0.24), transparent 34%),
        linear-gradient(135deg, rgba(49,46,129,0.88) 0%, rgba(14,116,144,0.76) 100%);
}
.treatment-workspace-section {
    border-radius: 1.6rem;
    border: 1px solid rgba(203,213,225,0.76);
    background:
        radial-gradient(circle at 0% 0%, rgba(59,130,246,0.08), transparent 28%),
        radial-gradient(circle at 100% 18%, rgba(16,185,129,0.1), transparent 30%),
        linear-gradient(135deg, rgba(248,250,252,0.98), rgba(255,255,255,0.96));
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 22px 56px -42px rgba(15,23,42,0.5);
    padding: 1rem;
}
@media (min-width: 640px) { .treatment-workspace-section { padding: 1.25rem; } }
.treatment-card-header {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,0.16);
    background:
        linear-gradient(90deg, rgba(255,255,255,0.14), rgba(255,255,255,0.05)),
        linear-gradient(180deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.02) 100%);
    font-weight: 800; font-size: 0.9375rem; color: #f8fafc;
}
.treatment-card-header i {
    display: inline-flex; align-items: center; justify-content: center;
    width: 2rem; height: 2rem; border-radius: 0.8rem;
    background: rgba(255,255,255,0.15);
    color: #fff;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18), 0 14px 22px -18px rgba(15,23,42,0.72);
}
.treatment-card-header i.text-blue-600,
.treatment-card-header i.text-amber-500,
.treatment-card-header i.text-emerald-600,
.treatment-card-header i.text-purple-600,
.treatment-card-header i.text-indigo-600 {
    color: #fff !important;
}
.treatment-card-body { padding: 1.25rem; }
.treatment-card-body + .treatment-card-body { border-top: 1px solid rgba(255,255,255,0.12); }
.treatment-card-body > p,
.treatment-card-body > .text-slate-500,
.treatment-card-body > .text-gray-500 {
    color: rgba(239,246,255,0.86) !important;
}
.treatment-card-panel .text-slate-500,
.treatment-card-panel .text-gray-500,
.treatment-card-panel .text-slate-600,
.treatment-card-panel .text-gray-600,
.treatment-card-empty .text-slate-500,
.treatment-card-empty .text-gray-500,
.treatment-card-empty .text-slate-600,
.treatment-card-empty .text-gray-600 {
    color: rgba(226,232,240,0.9) !important;
}
.treatment-card-panel .text-slate-900,
.treatment-card-panel .text-gray-900,
.treatment-card-panel .text-slate-800,
.treatment-card-panel .text-gray-800,
.treatment-card-panel .text-gray-700,
.treatment-card-empty .text-slate-900,
.treatment-card-empty .text-gray-900,
.treatment-card-empty .text-slate-800,
.treatment-card-empty .text-gray-800,
.treatment-card-empty .text-gray-700 {
    color: #fff !important;
}
.treatment-card-panel .text-emerald-700,
.treatment-card-empty .text-emerald-700 {
    color: #a7f3d0 !important;
}
.treatment-card-panel .text-amber-700,
.treatment-card-empty .text-amber-700 {
    color: #fde68a !important;
}
.treatment-card-panel .bg-emerald-100,
.treatment-card-empty .bg-emerald-100 {
    background: rgba(16,185,129,0.22) !important;
}
.treatment-card-panel .bg-amber-100,
.treatment-card-empty .bg-amber-100 {
    background: rgba(245,158,11,0.24) !important;
}
.treatment-card-panel .border-gray-100,
.treatment-card-panel .border-gray-50 {
    border-color: rgba(255,255,255,0.16) !important;
}
.treatment-card-panel tbody > :not([hidden]) ~ :not([hidden]) {
    border-color: rgba(255,255,255,0.12) !important;
}
.treatment-card-panel tbody tr:hover {
    background: rgba(255,255,255,0.08) !important;
}
.treatment-detail-copy {
    border-radius: 1rem;
    border: 1px solid rgba(255,255,255,0.26);
    background: rgba(255,255,255,0.18);
    padding: 1rem;
    color: #f8fafc;
    line-height: 1.75;
}
.treatment-detail-copy .text-gray-400 {
    color: rgba(226,232,240,0.82) !important;
}
.treatment-info-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    border-radius: 0.9rem;
    border: 1px solid rgba(255,255,255,0.26);
    background: rgba(255,255,255,0.18);
    padding: 0.8rem 0.9rem;
}
.treatment-info-label {
    color: rgba(219,234,254,0.9);
    font-size: 0.78rem;
    font-weight: 700;
}
.treatment-info-value {
    color: #fff;
    font-size: 0.86rem;
    font-weight: 800;
    text-align: end;
}
.treatment-card-panel {
    border: 1px solid rgba(255,255,255,0.26);
    background: rgba(255,255,255,0.18);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.62);
}
.treatment-card-empty {
    border: 1px dashed rgba(255,255,255,0.36);
    background: rgba(255,255,255,0.16);
    color: #f8fafc;
}
.treatment-card-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 0.9rem;
    padding: 0.7rem 0.9rem;
    font-size: 0.82rem;
    font-weight: 800;
    transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
}
.treatment-card-action:hover { transform: translateY(-1px); }
.treatment-card-action.border-blue-200,
.treatment-card-action.border-emerald-200 {
    border-color: rgba(255,255,255,0.34) !important;
    background: rgba(255,255,255,0.16) !important;
    color: #f8fafc !important;
}
.treatment-card-action.border-blue-200:hover,
.treatment-card-action.border-emerald-200:hover {
    background: rgba(255,255,255,0.24) !important;
    color: #fff !important;
}

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
.treatment-snapshot-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 0.9rem;
}
@media (min-width: 640px) { .treatment-snapshot-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 1280px) { .treatment-snapshot-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
.treatment-snapshot-card {
    min-height: 6rem;
    border-radius: 1rem;
    border: 1px solid rgba(255,255,255,0.44);
    background:
        radial-gradient(circle at 88% 10%, rgba(255,255,255,0.22), transparent 35%),
        linear-gradient(135deg, rgba(15,63,144,0.94), rgba(22,86,193,0.84) 54%, rgba(15,124,193,0.74));
    padding: 1rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 1px 3px rgba(15,23,42,0.04), 0 12px 28px -22px rgba(15,23,42,0.4);
}
.treatment-snapshot-card .text-amber-700,
.treatment-snapshot-card .text-emerald-700,
.treatment-snapshot-card .text-violet-700,
.treatment-snapshot-card .text-slate-700,
.treatment-snapshot-card .text-rose-700 {
    color: #fff !important;
}
.treatment-card--timeline .flex.justify-between {
    gap: 1rem;
    border-radius: 0.9rem;
    border: 1px solid rgba(255,255,255,0.26);
    background: rgba(255,255,255,0.18);
    padding: 0.8rem 0.9rem;
}
.treatment-card--timeline .flex.justify-between .text-gray-500 {
    color: rgba(219,234,254,0.9) !important;
    font-size: 0.78rem;
    font-weight: 700;
}
.treatment-card--timeline .flex.justify-between .text-gray-900 {
    color: #fff !important;
    font-size: 0.86rem;
    font-weight: 800;
    text-align: end;
}
.treatment-snapshot-label {
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(219,234,254,0.9);
}
.treatment-snapshot-value {
    margin-top: 0.45rem;
    font-size: 1rem;
    line-height: 1.25;
    font-weight: 800;
    color: #fff;
}
.treatment-snapshot-meta {
    margin-top: 0.35rem;
    font-size: 0.75rem;
    line-height: 1.25;
    color: rgba(239,246,255,0.82);
}

/* Treatment view refinement: align show cards with the patient workspace visual system. */
.treatment-page-shell {
    background:
        radial-gradient(900px 520px at 8% -12%, rgba(37, 99, 235, 0.08), transparent 60%),
        radial-gradient(700px 420px at 92% 0%, rgba(16, 185, 129, 0.08), transparent 55%),
        #f8fafc;
}
.treatment-workspace-section {
    border-color: rgba(255,255,255,0.76) !important;
    background:
        radial-gradient(circle at 0% 0%, rgba(37,99,235,0.10), transparent 28%),
        radial-gradient(circle at 100% 18%, rgba(16,185,129,0.12), transparent 30%),
        linear-gradient(135deg, rgba(248,250,252,0.98), rgba(255,255,255,0.96)) !important;
    box-shadow: 0 24px 58px -42px rgba(15,23,42,0.42), 0 1px 0 rgba(255,255,255,0.92) inset !important;
}
.treatment-card {
    --treatment-card-accent: #2563eb;
    --treatment-card-accent-soft: rgba(239,246,255,0.88);
    border-color: rgba(255,255,255,0.82) !important;
    background:
        radial-gradient(circle at 8% 0%, color-mix(in srgb, var(--treatment-card-accent) 15%, transparent), transparent 31%),
        radial-gradient(circle at 94% 6%, rgba(255,255,255,0.95), transparent 28%),
        linear-gradient(135deg, rgba(255,255,255,0.98), var(--treatment-card-accent-soft) 46%, rgba(255,255,255,0.98)) !important;
    box-shadow: 0 20px 48px -38px rgba(15,23,42,0.48), 0 1px 0 rgba(255,255,255,0.92) inset !important;
    color: #0f172a;
}
.treatment-card::after {
    opacity: 0.28;
    background: radial-gradient(circle at 92% 10%, color-mix(in srgb, var(--treatment-card-accent) 16%, transparent), transparent 30%);
}
.treatment-card--clinical { --treatment-card-accent: #2563eb; --treatment-card-accent-soft: rgba(239,246,255,0.88); }
.treatment-card--notes { --treatment-card-accent: #d97706; --treatment-card-accent-soft: rgba(255,251,235,0.9); }
.treatment-card--inventory { --treatment-card-accent: #059669; --treatment-card-accent-soft: rgba(236,253,245,0.9); }
.treatment-card--patient { --treatment-card-accent: #7c3aed; --treatment-card-accent-soft: rgba(245,243,255,0.9); }
.treatment-card--billing { --treatment-card-accent: #059669; --treatment-card-accent-soft: rgba(236,253,245,0.9); }
.treatment-card--timeline { --treatment-card-accent: #4f46e5; --treatment-card-accent-soft: rgba(238,242,255,0.9); }
.treatment-card-header {
    border-bottom-color: rgba(203,213,225,0.72) !important;
    background: linear-gradient(90deg, rgba(255,255,255,0.72), color-mix(in srgb, var(--treatment-card-accent) 12%, white)) !important;
    color: #0f172a !important;
}
.treatment-card-header i {
    background: linear-gradient(135deg, var(--treatment-card-accent), color-mix(in srgb, var(--treatment-card-accent) 78%, #0f172a)) !important;
    color: #fff !important;
    box-shadow: 0 14px 24px -18px rgba(15,23,42,0.72) !important;
}
.treatment-card-body + .treatment-card-body { border-top-color: rgba(203,213,225,0.72) !important; }
.treatment-card-body > p,
.treatment-card-body > .text-slate-500,
.treatment-card-body > .text-gray-500 { color: #475569 !important; }
.treatment-detail-copy,
.treatment-info-row,
.treatment-card-panel,
.treatment-card-empty,
.treatment-card--timeline .flex.justify-between {
    border-color: rgba(203,213,225,0.82) !important;
    background: rgba(255,255,255,0.82) !important;
    color: #334155 !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
}
.treatment-info-label,
.treatment-card--timeline .flex.justify-between .text-gray-500 { color: #64748b !important; }
.treatment-info-value,
.treatment-card--timeline .flex.justify-between .text-gray-900 { color: #0f172a !important; }
.treatment-card-panel .text-slate-500,
.treatment-card-panel .text-gray-500,
.treatment-card-panel .text-slate-600,
.treatment-card-panel .text-gray-600,
.treatment-card-empty .text-slate-500,
.treatment-card-empty .text-gray-500,
.treatment-card-empty .text-slate-600,
.treatment-card-empty .text-gray-600 { color: #64748b !important; }
.treatment-card-panel .text-slate-900,
.treatment-card-panel .text-gray-900,
.treatment-card-panel .text-slate-800,
.treatment-card-panel .text-gray-800,
.treatment-card-panel .text-gray-700,
.treatment-card-empty .text-slate-900,
.treatment-card-empty .text-gray-900,
.treatment-card-empty .text-slate-800,
.treatment-card-empty .text-gray-800,
.treatment-card-empty .text-gray-700 { color: #0f172a !important; }
.treatment-card-panel .text-emerald-700,
.treatment-card-empty .text-emerald-700 { color: #047857 !important; }
.treatment-card-panel .text-amber-700,
.treatment-card-empty .text-amber-700 { color: #b45309 !important; }
.treatment-card-panel tbody > :not([hidden]) ~ :not([hidden]) { border-color: rgba(226,232,240,0.9) !important; }
.treatment-card-panel tbody tr:hover { background: rgba(248,250,252,0.92) !important; }
.treatment-card-action.border-blue-200,
.treatment-card-action.border-emerald-200 {
    background: rgba(255,255,255,0.86) !important;
    color: color-mix(in srgb, var(--treatment-card-accent) 82%, #0f172a) !important;
    border-color: color-mix(in srgb, var(--treatment-card-accent) 24%, white) !important;
}
.treatment-card-action.border-blue-200:hover,
.treatment-card-action.border-emerald-200:hover { background: color-mix(in srgb, var(--treatment-card-accent) 10%, white) !important; }
.treatment-snapshot-card {
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 18px 34px -28px rgba(15,23,42,0.55);
}
.treatment-snapshot-card:nth-child(1) {
    background:
        radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%),
        linear-gradient(135deg, #047857, #0f766e 56%, #064e3b) !important;
}
.treatment-snapshot-card:nth-child(2) {
    background:
        radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
        linear-gradient(135deg, #2563eb, #1d4ed8 58%, #1e3a8a) !important;
}
.treatment-snapshot-card:nth-child(3) {
    background:
        radial-gradient(circle at 12% 0%, rgba(255,255,255,0.22), transparent 34%),
        linear-gradient(135deg, #d97706, #b45309 58%, #92400e) !important;
}
.treatment-snapshot-card:nth-child(4) {
    background:
        radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
        linear-gradient(135deg, #7c3aed, #6d28d9 56%, #4c1d95) !important;
}
.treatment-snapshot-card:nth-child(5) {
    background:
        radial-gradient(circle at 12% 0%, rgba(255,255,255,0.20), transparent 34%),
        linear-gradient(135deg, #e11d48, #be123c 56%, #881337) !important;
}
.treatment-snapshot-card .text-amber-700,
.treatment-snapshot-card .text-emerald-700,
.treatment-snapshot-card .text-violet-700,
.treatment-snapshot-card .text-slate-700,
.treatment-snapshot-card .text-rose-700 {
    color: #fff !important;
}

/* Treatment view structure pass: detail-first content and sticky workflow context. */
.treatment-command-section {
    position: relative;
    overflow: hidden;
}
.treatment-command-section::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 8% 0%, rgba(16,185,129,0.16), transparent 30%),
        radial-gradient(circle at 95% 10%, rgba(37,99,235,0.14), transparent 28%);
}
.treatment-command-section > * {
    position: relative;
    z-index: 1;
}
.treatment-command-section h2 {
    font-size: 1.25rem;
}
.treatment-section-heading {
    --section-start: #2563eb;
    --section-end: #1e40af;
    position: relative;
    overflow: hidden;
    border-radius: 1.4rem;
    padding: 1.1rem 1.25rem;
    color: #fff;
    background:
        radial-gradient(circle at 10% 0%, rgba(255,255,255,0.24), transparent 34%),
        linear-gradient(135deg, var(--section-start), var(--section-end));
    box-shadow: 0 22px 44px -32px color-mix(in srgb, var(--section-end) 78%, transparent);
}
.treatment-section-heading::after {
    content: "";
    position: absolute;
    inset-block-start: -48%;
    inset-inline-end: -5%;
    width: 13rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.16);
}
.treatment-section-heading > div {
    position: relative;
    z-index: 1;
}
.treatment-section-heading .treatment-section-icon {
    position: relative;
    z-index: 1;
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
.treatment-section-heading p {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(239,246,255,0.88);
}
.treatment-section-heading h2 {
    margin-top: 0.2rem;
    font-size: 1.05rem;
    line-height: 1.2;
    font-weight: 900;
    color: #fff;
}
.treatment-section-heading--clinical {
    --section-start: #2563eb;
    --section-end: #0f766e;
}
.treatment-section-heading--billing {
    --section-start: #047857;
    --section-end: #1d4ed8;
}
@media (min-width: 1024px) {
    .treatment-workspace-section > .grid:first-child {
        align-content: start;
    }
}
.treatment-workspace-section > aside {
    align-content: start;
}
.treatment-workspace-section > aside > .treatment-card,
.treatment-workspace-section > aside > div {
    width: 100%;
}
.treatment-card--clinical,
.treatment-card--notes,
.treatment-card--inventory {
    min-height: 0;
}
.treatment-card--clinical .treatment-card-body,
.treatment-card--notes .treatment-card-body {
    padding: 1.35rem;
}
.treatment-card--billing {
    --treatment-card-accent: #047857;
    --treatment-card-accent-soft: rgba(209,250,229,0.92);
}
.treatment-card--patient {
    --treatment-card-accent: #2563eb;
    --treatment-card-accent-soft: rgba(219,234,254,0.92);
}
.treatment-card--timeline {
    --treatment-card-accent: #4338ca;
    --treatment-card-accent-soft: rgba(224,231,255,0.92);
}
.treatment-card-header {
    min-height: 4.25rem;
}
.treatment-card--billing .treatment-card-header,
.treatment-card--patient .treatment-card-header,
.treatment-card--timeline .treatment-card-header {
    min-height: 3.8rem;
}
.treatment-card--billing .treatment-card-action.bg-emerald-600 {
    background: linear-gradient(135deg, #059669, #0f766e) !important;
    box-shadow: 0 18px 32px -24px rgba(5,150,105,0.78);
}

/* Treatment lower-half completion: match the mature user profile cards. */
.treatment-workspace-section {
    gap: 1.35rem;
    background:
        radial-gradient(circle at 0% 0%, rgba(37,99,235,0.14), transparent 28%),
        radial-gradient(circle at 100% 10%, rgba(16,185,129,0.14), transparent 30%),
        linear-gradient(135deg, rgba(248,250,252,0.98), rgba(255,255,255,0.96)) !important;
}
.treatment-card {
    --treatment-card-bg: #dbeafe;
    --treatment-card-accent: #2563eb;
    --treatment-card-shadow: rgba(37,99,235,0.22);
    border-color: rgba(255,255,255,0.72) !important;
    background: var(--treatment-card-bg) !important;
    box-shadow:
        0 24px 50px -34px rgba(15,23,42,0.42),
        0 14px 32px -26px var(--treatment-card-shadow),
        inset 0 1px 0 rgba(255,255,255,0.8) !important;
}
.treatment-card::after {
    background:
        radial-gradient(circle at 8% 0%, color-mix(in srgb, var(--treatment-card-accent) 18%, transparent), transparent 28%),
        radial-gradient(circle at 100% 0%, rgba(255,255,255,0.58), transparent 28%) !important;
    opacity: 1 !important;
}
.treatment-card--clinical { --treatment-card-bg: #bfdbfe; --treatment-card-accent: #2563eb; --treatment-card-shadow: rgba(37,99,235,0.24); }
.treatment-card--notes { --treatment-card-bg: #fde68a; --treatment-card-accent: #d97706; --treatment-card-shadow: rgba(217,119,6,0.24); }
.treatment-card--inventory { --treatment-card-bg: #bbf7d0; --treatment-card-accent: #059669; --treatment-card-shadow: rgba(5,150,105,0.24); }
.treatment-card--patient { --treatment-card-bg: #ddd6fe; --treatment-card-accent: #7c3aed; --treatment-card-shadow: rgba(124,58,237,0.24); }
.treatment-card--billing { --treatment-card-bg: #a7f3d0; --treatment-card-accent: #047857; --treatment-card-shadow: rgba(4,120,87,0.24); }
.treatment-card--timeline { --treatment-card-bg: #c7d2fe; --treatment-card-accent: #4338ca; --treatment-card-shadow: rgba(67,56,202,0.24); }
.treatment-card-header {
    min-height: 0 !important;
    padding: 1rem 1.45rem !important;
    border-bottom-color: rgba(255,255,255,0.58) !important;
    background: rgba(255,255,255,0.28) !important;
    color: #0f172a !important;
    font-size: 1.02rem !important;
    font-weight: 900 !important;
}
.treatment-card-header i {
    width: 2.35rem !important;
    height: 2.35rem !important;
    border-radius: 0.9rem !important;
    background: linear-gradient(135deg, var(--treatment-card-accent), color-mix(in srgb, var(--treatment-card-accent) 74%, #0f172a)) !important;
    color: #fff !important;
    box-shadow: 0 14px 24px -18px rgba(15,23,42,0.72) !important;
}
.treatment-card-body {
    padding: 1.35rem 1.45rem !important;
}
.treatment-detail-copy,
.treatment-info-row,
.treatment-card-panel,
.treatment-card-empty,
.treatment-card--timeline .flex.justify-between {
    border-color: rgba(255,255,255,0.72) !important;
    background: rgba(255,255,255,0.72) !important;
    color: #0f172a !important;
    box-shadow:
        0 14px 28px -26px rgba(15,23,42,0.34),
        inset 0 1px 0 rgba(255,255,255,0.78) !important;
}
.treatment-detail-copy {
    font-weight: 650;
    line-height: 1.75;
}
.treatment-info-label,
.treatment-card--timeline .flex.justify-between .text-gray-500 {
    color: #475569 !important;
    font-size: 0.76rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.04em;
}
.treatment-info-value,
.treatment-card--timeline .flex.justify-between .text-gray-900,
.treatment-card-panel .text-slate-900,
.treatment-card-panel .text-gray-900,
.treatment-card-panel .text-slate-800,
.treatment-card-panel .text-gray-800,
.treatment-card-panel .text-gray-700,
.treatment-card-empty .text-slate-900,
.treatment-card-empty .text-gray-900,
.treatment-card-empty .text-slate-800,
.treatment-card-empty .text-gray-800,
.treatment-card-empty .text-gray-700 {
    color: #0f172a !important;
    font-weight: 850;
}
.treatment-card-panel .text-slate-500,
.treatment-card-panel .text-gray-500,
.treatment-card-panel .text-slate-600,
.treatment-card-panel .text-gray-600,
.treatment-card-empty .text-slate-500,
.treatment-card-empty .text-gray-500,
.treatment-card-empty .text-slate-600,
.treatment-card-empty .text-gray-600 {
    color: #64748b !important;
}
.treatment-card-panel .text-emerald-700,
.treatment-card-empty .text-emerald-700 { color: #047857 !important; }
.treatment-card-panel .text-amber-700,
.treatment-card-empty .text-amber-700 { color: #b45309 !important; }
.treatment-card-panel .bg-emerald-100,
.treatment-card-empty .bg-emerald-100 { background: #dcfce7 !important; color: #047857 !important; }
.treatment-card-panel .bg-amber-100,
.treatment-card-empty .bg-amber-100 { background: #fef3c7 !important; color: #b45309 !important; }
.treatment-card-action.border-blue-200,
.treatment-card-action.border-emerald-200,
.treatment-card-action.bg-white {
    background: rgba(255,255,255,0.76) !important;
    color: var(--treatment-card-accent) !important;
    border-color: rgba(255,255,255,0.78) !important;
    box-shadow: 0 14px 26px -24px var(--treatment-card-shadow);
}
.treatment-card-action.border-blue-200:hover,
.treatment-card-action.border-emerald-200:hover,
.treatment-card-action.bg-white:hover {
    background: rgba(255,255,255,0.92) !important;
}
.treatment-money-trail > section {
    border-radius: 1.25rem !important;
    border-color: rgba(255,255,255,0.72) !important;
    background: #dcfce7 !important;
    box-shadow:
        0 24px 50px -34px rgba(15,23,42,0.42),
        0 14px 32px -26px rgba(16,185,129,0.24),
        inset 0 1px 0 rgba(255,255,255,0.8) !important;
}
.treatment-money-trail > section > div:first-child {
    border-bottom-color: rgba(255,255,255,0.64) !important;
    background: rgba(255,255,255,0.3) !important;
}
.treatment-money-trail > section > div:first-child p {
    display: none;
}
.treatment-money-trail .rounded-xl.bg-slate-50\/70,
.treatment-money-trail .rounded-xl.bg-indigo-50\/60,
.treatment-money-trail .rounded-xl.bg-rose-50\/60 {
    border-color: rgba(255,255,255,0.76) !important;
    background: rgba(255,255,255,0.72) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.78);
}

/* Professional wireframe: one content grid, cards sized by the work they show. */
.treatment-workspace-section {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.35rem !important;
    align-items: start;
}
.treatment-clinical-grid,
.treatment-context-grid {
    display: contents !important;
    position: static !important;
    top: auto !important;
}
.treatment-section-heading,
.treatment-card--inventory,
.treatment-money-trail {
    grid-column: 1 / -1;
}
@media (min-width: 1024px) {
    .treatment-workspace-section {
        grid-template-columns: repeat(12, minmax(0, 1fr));
    }
    .treatment-section-heading--clinical {
        order: 1;
    }
    .treatment-clinical-grid-has-notes > .treatment-card--clinical {
        order: 2;
        grid-column: span 6;
    }
    .treatment-clinical-grid-has-notes > .treatment-card--notes {
        order: 4;
        grid-column: span 6;
    }
    .treatment-clinical-grid-no-notes > .treatment-card--clinical {
        order: 2;
        grid-column: span 6;
    }
    .treatment-section-heading--billing {
        display: none !important;
    }
    .treatment-context-grid > .treatment-card--billing,
    .treatment-context-grid-no-patient > .treatment-card--billing {
        order: 3;
        grid-column: span 6;
    }
    .treatment-context-grid > .treatment-money-trail,
    .treatment-context-grid-no-patient > .treatment-money-trail {
        order: 7;
        grid-column: 1 / -1;
    }
    .treatment-clinical-grid > .treatment-card--inventory {
        order: 8;
        grid-column: 1 / -1;
    }
    .treatment-workspace-has-inventory .treatment-card--timeline {
        order: 5;
        grid-column: span 6;
    }
    .treatment-workspace-no-inventory .treatment-card--timeline {
        order: 5;
        grid-column: span 6;
    }
    .treatment-clinical-grid-no-notes ~ .treatment-context-grid .treatment-card--timeline {
        grid-column: span 4;
    }
    .treatment-clinical-grid-no-notes ~ .treatment-context-grid .treatment-money-trail {
        grid-column: span 8;
    }
}
@media (min-width: 1280px) {
    .treatment-clinical-grid-has-notes > .treatment-card--clinical {
        grid-column: span 6;
    }
    .treatment-context-grid > .treatment-card--billing,
    .treatment-context-grid-no-patient > .treatment-card--billing {
        grid-column: span 6;
    }
}
.treatment-card {
    align-self: stretch;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.treatment-card-body {
    flex: 1 1 auto;
}
.treatment-card--clinical .treatment-detail-copy {
    min-height: 6.5rem;
}
.treatment-card--notes .treatment-detail-copy {
    min-height: 6.5rem;
}
.treatment-card--inventory .treatment-card-body {
    padding: 1rem !important;
}
.treatment-money-trail {
    margin-top: 0.1rem;
}
</style>

<div class="treatment-page-shell min-h-screen bg-slate-50">
<div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">

    <!-- Hero Header -->
    <section class="patient-hero-card patient-hero-card-neutral overflow-hidden rounded-[28px] border border-slate-200 mb-8">
        <div class="patient-hero-overlay"></div>
        <div class="relative z-10 p-6 sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                        <span><?= esc($tr('treatment_management')) ?></span>
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
                        <a href="<?= workflow_context_url('patient/' . $treatment['patient_id'], $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas fa-user"></i><span><?= esc(lang('UI.patientProfile')) ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="<?= esc(workflow_back('treatment', $return_to ?? null)) ?>" class="patient-header-action patient-header-action-soft">
                            <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back')) ?></span>
                        </a>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <a href="<?= workflow_context_url('treatment/' . $treatment['id'] . '/edit', $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft">
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

    <section class="treatment-command-section mb-6 rounded-[1.6rem] border border-white/70 bg-gradient-to-br from-white via-emerald-50/70 to-blue-50/65 p-4 shadow-xl shadow-slate-200/60 sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700"><?= esc($tr('operational_snapshot')) ?></p>
                <h2 class="mt-1 text-lg font-extrabold text-slate-900"><?= esc($tr('clinical_finance_trace')) ?></h2>
            </div>
            <?php if ($linkedInvoiceUrl !== ''): ?>
                <a href="<?= esc($linkedInvoiceUrl) ?>" class="inline-flex w-fit items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-700 hover:bg-emerald-100">
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
                <p class="treatment-snapshot-value <?= $invoiceBalance > 0.009 ? 'text-amber-700' : 'text-emerald-700' ?>" dir="ltr"><?= esc(formatCurrency($invoiceBalance)) ?></p>
                <p class="treatment-snapshot-meta"><?= esc(lang('UI.moneyTrailCharged')) ?>: <span dir="ltr"><?= esc(formatCurrency($moneySummary['charged'] ?? 0)) ?></span></p>
            </div>
            <div class="treatment-snapshot-card">
                <p class="treatment-snapshot-label"><?= esc(lang('UI.moneyTrailProviderRemaining')) ?></p>
                <p class="treatment-snapshot-value <?= $providerRemaining > 0.009 ? 'text-violet-700' : 'text-slate-700' ?>" dir="ltr"><?= esc(formatCurrency($providerRemaining)) ?></p>
                <p class="treatment-snapshot-meta"><?= esc(lang('UI.moneyTrailProviderShare')) ?>: <span dir="ltr"><?= esc(formatCurrency($moneySummary['provider_share'] ?? 0)) ?></span></p>
            </div>
            <div class="treatment-snapshot-card">
                <p class="treatment-snapshot-label"><?= esc(lang('UI.moneyTrailInventoryCost')) ?></p>
                <p class="treatment-snapshot-value <?= $inventoryCost > 0.009 ? 'text-rose-700' : 'text-slate-700' ?>" dir="ltr"><?= esc(formatCurrency($inventoryCost)) ?></p>
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

            <!-- Treatment Description -->
            <div class="treatment-card treatment-card--clinical">
                <div class="treatment-card-header">
                    <i class="fas fa-align-left text-blue-600"></i><span><?= esc($tr('treatment_description')) ?></span>
                </div>
                <div class="treatment-card-body">
                    <div class="treatment-detail-copy text-sm"><?= !empty($treatment['treatment_description']) ? nl2br(htmlspecialchars($treatment['treatment_description'])) : '<span class="text-gray-400 italic">' . esc($tr('no_description_provided')) . '</span>' ?></div>
                </div>
            </div>

            <!-- Treatment Notes -->
            <?php if (!empty($treatment['treatment_notes'])): ?>
            <div class="treatment-card treatment-card--notes">
                <div class="treatment-card-header">
                    <i class="fas fa-sticky-note text-amber-500"></i><span><?= esc($tr('treatment_notes')) ?></span>
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
                    <i class="fas fa-boxes text-emerald-600"></i><span><?= esc($tr('inventory_usage')) ?></span>
                </div>
                <div class="treatment-card-body">
                <div class="treatment-card-panel overflow-hidden rounded-xl">
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
                    <i class="fas fa-file-invoice-dollar text-emerald-600"></i><span><?= esc($tr('patient_billing')) ?></span>
                </div>
                <div class="treatment-card-body space-y-4 text-sm">
                    <?php if ($chargeLineId > 0): ?>
                        <div class="treatment-card-panel rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700"><?= esc($tr('captured_charge')) ?></p>
                                    <p class="mt-1 truncate font-bold text-slate-900"><?= esc($chargeLine['procedure_name'] ?? $treatment['treatment_name'] ?? $tr('treatment')) ?></p>
                                    <?php if (!empty($chargeLine['tooth_numbers'])): ?>
                                        <p class="mt-1 text-xs text-slate-500"><?= esc($tr('tooth_number_short')) ?>: <span dir="ltr"><?= esc($chargeLine['tooth_numbers']) ?></span></p>
                                    <?php endif; ?>
                                </div>
                                <div class="shrink-0 text-end">
                                    <p class="font-black text-emerald-700" dir="ltr"><?= esc($chargeLineCurrency) ?> <?= number_format($chargeLineAmount, 2) ?></p>
                                    <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-bold <?= $chargeLineIsOpen ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                        <?= esc($chargeLineIsOpen ? $tr('not_billed_yet') : $tr('already_billed')) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if ($billThisTreatmentUrl !== ''): ?>
                            <button type="button" data-app-drawer-open="treatmentInvoiceDrawer" class="treatment-card-action w-full bg-emerald-600 text-white shadow-sm hover:bg-emerald-700">
                                <i class="fas fa-file-invoice-dollar"></i><span><?= esc($tr('create_invoice_from_treatment')) ?></span>
                            </button>
                        <?php elseif ($linkedInvoiceUrl !== ''): ?>
                            <a href="<?= esc($linkedInvoiceUrl) ?>" class="treatment-card-action w-full border border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50">
                                <i class="fas fa-link"></i><span><?= esc($tr('open_linked_invoice')) ?></span>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="treatment-card-empty rounded-2xl p-4">
                            <p class="font-semibold text-slate-800"><?= esc($tr('no_charge_captured')) ?></p>
                            <a href="<?= workflow_context_url('treatment/' . $treatment['id'] . '/edit', $return_to ?? null) ?>" class="mt-2 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
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
                    <i class="fas fa-clock text-indigo-600"></i><span><?= esc($tr('treatment_timeline')) ?></span>
                </div>
                <div class="treatment-card-body space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('start_date')) ?></span><span class="text-gray-900"><?= !empty($treatment['start_date']) ? formatDate($treatment['start_date']) : '—' ?></span></div>
                    <div class="treatment-info-row"><span class="treatment-info-label"><?= esc($tr('completion_date')) ?></span><span class="treatment-info-value"><?= !empty($treatment['completion_date']) ? formatDateTime($treatment['completion_date']) : esc($tr('not_completed')) ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('created_by')) ?></span><span class="text-gray-900"><?= $doctorName !== '' ? esc($doctorName) : '—' ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('last_updated')) ?></span><span class="text-gray-900 text-xs"><?= !empty($treatment['updated_at']) ? formatDateTime($treatment['updated_at']) : '—' ?></span></div>
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
        'description' => $tr('patient_billing_help'),
        'icon' => 'fas fa-file-invoice-dollar',
        'tone' => 'bg-emerald-600',
        'size' => 'wide',
        'body' => $treatmentInvoiceDrawerBody,
        'footer' => '<div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">'
            . '<button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" data-app-drawer-close="' . esc($treatmentInvoiceDrawerId) . '">' . esc(lang('UI.cancel')) . '</button>'
            . '<a href="' . esc($billThisTreatmentUrl) . '" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 px-4 py-2.5 text-sm font-bold text-emerald-800 hover:bg-emerald-50">' . esc(lang('UI.openFullPage')) . '</a>'
            . '<button type="submit" form="' . esc($treatmentInvoiceFormId) . '" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">' . esc(lang('Finance.createTransaction')) . '</button>'
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
<?= $this->endSection() ?>
