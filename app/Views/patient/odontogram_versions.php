<?= $this->extend('layouts/main_auth') ?>

<?php
function getFDINumber($universalNumber) {
    $fdiMap = [
        1 => '11', 2 => '12', 3 => '13', 4 => '14', 5 => '15', 6 => '16', 7 => '17', 8 => '18',
        9 => '21', 10 => '22', 11 => '23', 12 => '24', 13 => '25', 14 => '26', 15 => '27', 16 => '28',
        17 => '31', 18 => '32', 19 => '33', 20 => '34', 21 => '35', 22 => '36', 23 => '37', 24 => '38',
        25 => '41', 26 => '42', 27 => '43', 28 => '44', 29 => '45', 30 => '46', 31 => '47', 32 => '48'
    ];

    return $fdiMap[$universalNumber] ?? $universalNumber;
}

function loadInlinePatientHistoryOdontogramSvg(): string
{
    $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
    if (!is_file($svgPath)) {
        return '';
    }

    $svgContent = preg_replace('/<\?xml.*?\?>/i', '', (string) file_get_contents($svgPath), 1);
    return $svgContent === '' ? '' : $svgContent;
}

$isRtl = !empty($is_rtl);
$fullName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
$patientHeroThemeClass = 'patient-hero-card-neutral';
$patientAvatarIconClass = 'fas fa-user';
$patientGenderValue = strtolower((string) ($patient['gender'] ?? ''));
if ($patientGenderValue === 'female') {
    $patientHeroThemeClass = 'patient-hero-card-female';
    $patientAvatarIconClass = 'fas fa-person-dress';
} elseif ($patientGenderValue === 'male') {
    $patientHeroThemeClass = 'patient-hero-card-male';
    $patientAvatarIconClass = 'fas fa-person';
}
$selectedSnapshotRows = array_values($selected_snapshot['snapshot_rows'] ?? []);
$selectedAffectedRows = array_values($selected_snapshot['affected_rows'] ?? []);
$historySvg = loadInlinePatientHistoryOdontogramSvg();
$sourceLabelMap = [
    'examination' => lang('UI.patientSnapshotSourceExamination'),
    'contract' => lang('UI.patientSnapshotSourceContract'),
    'manual' => lang('UI.patientSnapshotSourceManual'),
];
$conditionLabelMap = [
    'healthy' => lang('UI.patientHealthy'),
    'cavity' => 'Cavity',
    'filling' => 'Filling',
    'crown' => 'Crown',
    'root_canal' => 'Root Canal',
    'extracted' => 'Extracted',
    'implant' => 'Implant',
    'bridge' => 'Bridge',
    'partial_denture' => 'Partial Denture',
    'full_denture' => 'Full Denture',
];
?>

<?= $this->section('content') ?>
<div class="mx-auto w-full max-w-none px-2 py-6 sm:px-3 sm:py-8 xl:px-4 2xl:px-5">
    <div class="space-y-6">
        <section class="patient-hero-card <?= esc($patientHeroThemeClass) ?> rounded-[28px] border border-slate-200 text-white">
            <div class="flex flex-col gap-5 p-6 sm:p-8 xl:flex-row xl:items-center xl:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/85">
                        <span><?= esc(lang('UI.patientDetailsTitle')) ?></span>
                        <span class="h-1 w-1 rounded-full bg-blue-200/70"></span>
                        <span><?= esc(lang('UI.patientChartHistoryPageTitle')) ?></span>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($fullName) ?></h1>
                        <p class="max-w-3xl text-sm leading-7 text-blue-100/90"><?= esc(lang('UI.patientChartHistoryPageHelp')) ?></p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patient/' . $patient['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/12 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/18">
                        <i class="<?= esc($patientAvatarIconClass) ?>"></i>
                        <span><?= esc(lang('UI.backToPatient')) ?></span>
                    </a>
                    <a href="<?= base_url('odontogram/' . $patient['id'] . '?return_to=patient/' . $patient['id'] . '/odontogram-versions') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                        <i class="fas fa-tooth text-cyan-700"></i>
                        <span><?= esc(lang('UI.patientOpenFullChart')) ?></span>
                    </a>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(320px,0.9fr)_minmax(0,1.4fr)]">
            <aside class="rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.30)]">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-cyan-50/70 px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientChartHistoryListTitle')) ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientChartHistoryListHelp')) ?></p>
                </div>
                <div class="max-h-[calc(100vh-18rem)] space-y-3 overflow-y-auto p-4">
                    <?php if (!empty($odontogram_snapshots)): ?>
                        <?php foreach ($odontogram_snapshots as $snapshot): ?>
                            <?php
                            $isSelected = !empty($selected_snapshot) && (int) ($selected_snapshot['id'] ?? 0) === (int) ($snapshot['id'] ?? 0);
                            $sourceLabel = $sourceLabelMap[$snapshot['source_type'] ?? ''] ?? lang('UI.patientSnapshotSourceUnknown');
                            ?>
                            <a href="<?= base_url('patient/' . $patient['id'] . '/odontogram-versions/' . $snapshot['id']) ?>"
                               class="block rounded-2xl border px-4 py-4 transition <?= $isSelected ? 'border-cyan-300 bg-cyan-50 shadow-[0_18px_35px_-28px_rgba(8,145,178,0.35)]' : 'border-slate-200 bg-white hover:border-cyan-200 hover:bg-slate-50' ?>">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600"><?= esc($sourceLabel) ?></span>
                                    <span class="text-[11px] font-semibold text-slate-400"><?= esc(!empty($snapshot['created_at']) ? formatDateTime($snapshot['created_at']) : '--') ?></span>
                                </div>
                                <?php if (!empty($snapshot['label'])): ?>
                                    <p class="mt-3 text-sm font-semibold text-slate-900"><?= esc($snapshot['label']) ?></p>
                                <?php endif; ?>
                                <p class="mt-2 text-sm text-slate-600"><?= esc(lang('UI.patientSnapshotTeethAffected', ['count' => (int) ($snapshot['affected_count'] ?? 0)])) ?></p>
                                <?php if (!empty($snapshot['creator_name'])): ?>
                                    <p class="mt-1 text-xs text-slate-400"><?= esc(lang('UI.patientSnapshotCapturedBy', ['name' => $snapshot['creator_name']])) ?></p>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                            <i class="fas fa-clock-rotate-left text-2xl text-slate-300"></i>
                            <p class="mt-3 text-sm text-slate-500"><?= esc(lang('UI.patientNoChartSnapshots')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <section class="space-y-5">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.30)]">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50/70 px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientChartHistoryDetailTitle')) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientChartHistoryDetailHelp')) ?></p>
                            </div>
                            <?php if (!empty($selected_snapshot['source_url'])): ?>
                                <a href="<?= esc($selected_snapshot['source_url']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                    <span><?= esc(lang('UI.patientChartHistoryOpenSource')) ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($selected_snapshot)): ?>
                        <?php $selectedSourceLabel = $sourceLabelMap[$selected_snapshot['source_type'] ?? ''] ?? lang('UI.patientSnapshotSourceUnknown'); ?>
                        <div class="space-y-5 p-5">
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400"><?= esc(lang('UI.patientSnapshotReferenceLabel')) ?></p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900"><?= esc($selected_snapshot['label'] ?? ('#' . (int) $selected_snapshot['id'])) ?></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400"><?= esc(lang('UI.patientStatus')) ?></p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900"><?= esc($selectedSourceLabel) ?></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400"><?= esc(lang('UI.patientChartHistoryTitle')) ?></p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900"><?= esc(lang('UI.patientSnapshotTeethAffected', ['count' => (int) ($selected_snapshot['affected_count'] ?? 0)])) ?></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400"><?= esc(lang('UI.patientCreatedBy')) ?></p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900"><?= esc($selected_snapshot['creator_name'] ?? lang('UI.patientUnknownDoctor')) ?></p>
                                </div>
                            </div>

                            <div class="rounded-[28px] border border-slate-200 bg-gradient-to-b from-white to-slate-50 px-5 py-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.65)]">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900"><?= esc(lang('UI.patientCurrentOdontogramTitle')) ?></h3>
                                        <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientSnapshotCapturedOn', ['date' => !empty($selected_snapshot['created_at']) ? formatDateTime($selected_snapshot['created_at']) : '--'])) ?></p>
                                    </div>
                                    <a href="<?= base_url('patient/' . $patient['id'] . '/odontogram-versions/' . $selected_snapshot['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                                        <i class="fas fa-eye"></i>
                                        <span><?= esc(lang('UI.patientChartHistoryOpenSelected')) ?></span>
                                    </a>
                                </div>
                                <div class="patient-history-preview-shell mt-5">
                                    <?php if (!empty($historySvg)): ?>
                                        <div id="patientHistoryOdontogramPreview" class="patient-history-odontogram-svg"><?= $historySvg ?></div>
                                    <?php else: ?>
                                        <div class="patient-history-preview-empty">
                                            <i class="fas fa-tooth"></i>
                                            <p><?= esc(lang('UI.patientNoOdontogramPreview')) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-200 bg-white">
                                <div class="border-b border-slate-200 px-5 py-4">
                                    <h3 class="text-base font-bold text-slate-900"><?= esc(lang('UI.patientSnapshotAffectedTeethTitle')) ?></h3>
                                    <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientSnapshotAffectedTeethHelp')) ?></p>
                                </div>
                                <?php if (!empty($selectedAffectedRows)): ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="px-5 py-3 text-start font-semibold text-slate-500">#</th>
                                                    <th class="px-5 py-3 text-start font-semibold text-slate-500">FDI</th>
                                                    <th class="px-5 py-3 text-start font-semibold text-slate-500"><?= esc(lang('UI.patientSnapshotConditionLabel')) ?></th>
                                                    <th class="px-5 py-3 text-start font-semibold text-slate-500"><?= esc(lang('UI.patientNotesTitle')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                <?php foreach ($selectedAffectedRows as $affectedRow): ?>
                                                    <?php
                                                    $conditionType = (string) ($affectedRow['condition_type'] ?? 'healthy');
                                                    $conditionLabel = $conditionLabelMap[$conditionType] ?? ucwords(str_replace('_', ' ', $conditionType));
                                                    $conditionDescription = trim((string) ($affectedRow['condition_description'] ?? ''));
                                                    $statusText = trim((string) ($affectedRow['treatment_status'] ?? ''));
                                                    $detailText = $conditionDescription !== '' ? $conditionDescription : ($statusText !== '' ? ucwords(str_replace('_', ' ', $statusText)) : '—');
                                                    ?>
                                                    <tr>
                                                        <td class="px-5 py-3 font-semibold text-slate-900"><?= esc((string) ($affectedRow['tooth_number'] ?? '--')) ?></td>
                                                        <td class="px-5 py-3 text-slate-600"><?= esc((string) getFDINumber((int) ($affectedRow['tooth_number'] ?? 0))) ?></td>
                                                        <td class="px-5 py-3 text-slate-700"><?= esc($conditionLabel) ?></td>
                                                        <td class="px-5 py-3 text-slate-500"><?= esc($detailText) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="px-5 py-8 text-center text-sm text-slate-500">
                                        <?= esc(lang('UI.patientSnapshotNoAffectedTeeth')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="px-5 py-10 text-center">
                            <i class="fas fa-clock-rotate-left text-3xl text-slate-300"></i>
                            <p class="mt-3 text-sm text-slate-500"><?= esc(lang('UI.patientChartHistoryNoSelection')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
    .patient-hero-card {
        position: relative;
        --hero-angle: 135deg;
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        background: linear-gradient(var(--hero-angle), var(--hero-start) 0%, var(--hero-mid) 58%, var(--hero-end) 100%);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, 0.75);
    }

    .patient-hero-card-male {
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
    }

    .patient-hero-card-female {
        --hero-start: #8a245a;
        --hero-mid: #c03579;
        --hero-end: #7c3aed;
    }

    .patient-hero-card-neutral {
        --hero-start: #334155;
        --hero-mid: #2563eb;
        --hero-end: #0f766e;
    }

    .patient-history-preview-shell {
        border: 1px solid #dbe4ef;
        border-radius: 1.5rem;
        background: linear-gradient(180deg, #ffffff, #f8fbff);
        padding: 1.25rem;
        min-height: 340px;
    }

    .patient-history-odontogram-svg {
        width: 100%;
        overflow: hidden;
    }

    .patient-history-odontogram-svg svg {
        width: 100%;
        height: auto;
        display: block;
    }

    .patient-history-odontogram-svg .tooth-overlay {
        mix-blend-mode: multiply;
        pointer-events: none;
    }

    .patient-history-preview-empty {
        display: flex;
        min-height: 280px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        border: 1px dashed #cbd5e1;
        border-radius: 1rem;
        color: #64748b;
        background: #f8fafc;
    }
</style>

<script>
    const patientHistorySnapshotData = <?= json_encode($selectedSnapshotRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('patientHistoryOdontogramPreview');
        if (!container || !Array.isArray(patientHistorySnapshotData) || patientHistorySnapshotData.length === 0) {
            return;
        }

        const conditionColors = {
            cavity: '#facc15',
            filling: '#3b82f6',
            crown: '#8b5cf6',
            root_canal: '#ec4899',
            extracted: '#ef4444',
            implant: '#6366f1',
            bridge: '#14b8a6',
            partial_denture: '#f97316',
            full_denture: '#f59e0b',
            other: '#64748b'
        };

        const getLargestShape = (group) => {
            const shapes = Array.from(group.querySelectorAll('path, polygon, rect, ellipse, circle'))
                .filter(el =>
                    !el.classList.contains('tooth-overlay') &&
                    !el.classList.contains('tooth-outline') &&
                    !el.classList.contains('tooth-hit')
                );

            if (!shapes.length) {
                return null;
            }

            let best = null;
            let bestArea = -1;
            shapes.forEach(shape => {
                try {
                    const bbox = shape.getBBox();
                    const area = bbox.width * bbox.height;
                    if (area > bestArea) {
                        best = shape;
                        bestArea = area;
                    }
                } catch (error) {}
            });

            return best || shapes[0];
        };

        const applyToothCondition = (toothNumber, conditionType) => {
            const group = container.querySelector('#tooth' + toothNumber);
            if (!group) {
                return;
            }

            group.querySelectorAll('.tooth-overlay, .tooth-outline, .tooth-hit').forEach(el => el.remove());
            const base = getLargestShape(group);
            if (!base) {
                return;
            }

            if (conditionType !== 'healthy' && conditionColors[conditionType]) {
                const overlay = base.cloneNode(true);
                overlay.setAttribute('class', 'tooth-overlay');
                overlay.setAttribute('fill', conditionColors[conditionType]);
                overlay.setAttribute('fill-opacity', '0.38');
                overlay.setAttribute('stroke', 'none');
                group.appendChild(overlay);
            }
        };

        patientHistorySnapshotData.forEach(record => {
            if (!record || typeof record.tooth_number === 'undefined') {
                return;
            }
            applyToothCondition(record.tooth_number, record.condition_type || 'healthy');
        });
    });
</script>
<?= $this->endSection() ?>
