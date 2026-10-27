<?= $this->extend('layouts/studio') ?>
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
// A malformed saved document must not become an apparently healthy empty preview.
$historyPreviewRows = json_decode((string) ($selected_snapshot['snapshot_json'] ?? 'null'), true);
$historyPreviewRows = is_array($historyPreviewRows) && array_is_list($historyPreviewRows) ? $historyPreviewRows : null;
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


<?php helper('asset');foreach(['cavity'=>'Cavity','filling'=>'Filling','crown'=>'Crown','root_canal'=>'RootCanal','extracted'=>'Extracted','implant'=>'Implant','bridge'=>'Bridge','partial_denture'=>'PartialDenture','full_denture'=>'FullDenture'] as $condition=>$key){$label=lang('UI.patientCondition'.$key);if($label!=='UI.patientCondition'.$key)$conditionLabelMap[$condition]=$label;} ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-chart-history.css') ?>?v=<?= esc(asset_version('assets/css/studio-chart-history.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="chart-history"><header class="chart-history-heading"><div><h1><?= esc(lang('UI.patientChartHistoryPageTitle')) ?></h1><p><?= esc($fullName) ?> · <?= esc($patient['patient_id']??'') ?></p></div><nav><a class="button secondary" href="<?= workflow_back('patient/'.$patient['id']) ?>"><?= esc(lang('UI.backToPatient')) ?></a><a class="button primary" href="<?= base_url('odontogram/'.$patient['id']) ?>"><?= esc(lang('UI.patientOpenFullChart')) ?></a></nav></header><p class="chart-history-help"><?= esc(lang('UI.patientChartHistoryPageHelp')) ?></p>
<div class="chart-history-layout"><aside class="chart-history-list"><header><h2><?= esc(lang('UI.patientChartHistoryListTitle')) ?></h2><p><?= esc(lang('UI.patientChartHistoryListHelp')) ?></p></header><nav aria-label="<?= esc(lang('UI.patientChartHistoryListTitle'),'attr') ?>">
<?php if(!empty($odontogram_snapshots)): ?><?php foreach($odontogram_snapshots as $snapshot):$isSelected=!empty($selected_snapshot)&&(int)($selected_snapshot['id']??0)===(int)($snapshot['id']??0);$sourceLabel=$sourceLabelMap[$snapshot['source_type']??'']??lang('UI.patientSnapshotSourceUnknown'); ?>
<a href="<?= base_url('patient/'.$patient['id'].'/odontogram-versions/'.$snapshot['id']) ?>" <?= $isSelected?'aria-current="page"':'' ?>><div><strong><?= esc($sourceLabel) ?></strong><time><?= esc(!empty($snapshot['created_at'])?formatDateTime($snapshot['created_at']):'--') ?></time></div><?php if(!empty($snapshot['label'])): ?><h3><?= esc($snapshot['label']) ?></h3><?php endif; ?><p><?= esc(lang('UI.patientSnapshotTeethAffected',['count'=>(int)($snapshot['affected_count']??0)])) ?></p><?php if(!empty($snapshot['creator_name'])): ?><small><?= esc(lang('UI.patientSnapshotCapturedBy',['name'=>$snapshot['creator_name']])) ?></small><?php endif; ?></a>
<?php endforeach; ?><?php else: ?><p class="chart-history-empty"><?= esc(lang('UI.patientNoChartSnapshots')) ?></p><?php endif; ?></nav></aside>
<section class="chart-history-detail"><header><div><h2><?= esc(lang('UI.patientChartHistoryDetailTitle')) ?></h2><p><?= esc(lang('UI.patientChartHistoryDetailHelp')) ?></p></div><?php if(!empty($selected_snapshot['source_url'])): ?><a class="button secondary" href="<?= esc($selected_snapshot['source_url']) ?>"><?= esc(lang('UI.patientChartHistoryOpenSource')) ?></a><?php endif; ?></header>
<?php if(!empty($selected_snapshot)):$selectedSourceLabel=$sourceLabelMap[$selected_snapshot['source_type']??'']??lang('UI.patientSnapshotSourceUnknown'); ?>
<dl class="chart-history-context"><div><dt><?= esc(lang('UI.patientSnapshotReferenceLabel')) ?></dt><dd><?= esc($selected_snapshot['label']??('#'.(int)$selected_snapshot['id'])) ?></dd></div><div><dt><?= esc(lang('UI.patientStatus')) ?></dt><dd><?= esc($selectedSourceLabel) ?></dd></div><div><dt><?= esc(lang('UI.patientChartHistoryTitle')) ?></dt><dd><?= esc(lang('UI.patientSnapshotTeethAffected',['count'=>(int)($selected_snapshot['affected_count']??0)])) ?></dd></div><div><dt><?= esc(lang('UI.patientCreatedBy')) ?></dt><dd><?= esc($selected_snapshot['creator_name']??lang('UI.patientUnknownDoctor')) ?></dd></div></dl>
<section class="chart-history-preview"><header><div><h3><?= esc(lang('Studio.historyChartTitle')) ?></h3><p><?= esc(lang('UI.patientSnapshotCapturedOn',['date'=>!empty($selected_snapshot['created_at'])?formatDateTime($selected_snapshot['created_at']):'--'])) ?></p></div><a class="button secondary" href="<?= base_url('patient/'.$patient['id'].'/odontogram-versions/'.$selected_snapshot['id']) ?>"><?= esc(lang('UI.patientChartHistoryOpenSelected')) ?></a></header><p id="patientHistoryPreviewUnavailable" class="chart-history-warning" role="status"><?= esc(lang('Studio.historyPreviewWarning')) ?></p><div class="patient-history-preview-shell"><?php if(!empty($historySvg)): ?><div id="patientHistoryOdontogramPreview" hidden class="patient-history-odontogram-svg"><?= $historySvg ?></div><?php else: ?><p class="chart-history-empty"><?= esc(lang('UI.patientNoOdontogramPreview')) ?></p><?php endif; ?></div></section>
<section class="chart-history-affected"><h3><?= esc(lang('UI.patientSnapshotAffectedTeethTitle')) ?></h3><p><?= esc(lang('UI.patientSnapshotAffectedTeethHelp')) ?></p>
<?php if(!empty($selectedAffectedRows)): ?><table class="studio-grid chart-history-table" data-grid-widths="10,12,28,50" aria-label="<?= esc(lang('UI.patientSnapshotAffectedTeethTitle'),'attr') ?>"><thead><tr><th scope="col">#</th><th scope="col">FDI</th><th scope="col"><?= esc(lang('UI.patientSnapshotConditionLabel')) ?></th><th scope="col"><?= esc(lang('UI.patientNotesTitle')) ?></th></tr></thead><tbody>
<?php foreach($selectedAffectedRows as $affectedRow):$conditionType=(string)($affectedRow['condition_type']??'healthy');$conditionLabel=$conditionLabelMap[$conditionType]??ucwords(str_replace('_',' ',$conditionType));$conditionDescription=trim((string)($affectedRow['condition_description']??''));$statusText=trim((string)($affectedRow['treatment_status']??''));$detailText=$conditionDescription!==''?$conditionDescription:($statusText!==''?ucwords(str_replace('_',' ',$statusText)):'—'); ?><tr><td><?= esc((string)($affectedRow['tooth_number']??'--')) ?></td><td><?= esc((string)getFDINumber((int)($affectedRow['tooth_number']??0))) ?></td><td><?= esc($conditionLabel) ?></td><td><?= esc($detailText) ?></td></tr><?php endforeach; ?></tbody></table><?php else: ?><p class="chart-history-empty"><?= esc(lang('UI.patientSnapshotNoAffectedTeeth')) ?></p><?php endif; ?></section>
<?php else: ?><p class="chart-history-empty"><?= esc(lang('UI.patientChartHistoryNoSelection')) ?></p><?php endif; ?></section></div></div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    const patientHistorySnapshotData = <?= json_encode($historyPreviewRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('patientHistoryOdontogramPreview');
        const unavailable = document.getElementById('patientHistoryPreviewUnavailable');
        if (!container || !Array.isArray(patientHistorySnapshotData)) {
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

        // Validate the complete overlay before painting anything. Never guess a tooth.
        const reject = () => { container.hidden = true; };
        // Synchronous validation completes before the browser paints; SVG geometry
        // must participate in layout for getBBox to select the correct base shape.
        container.hidden = false;
        const overlays = [];
        const seen = new Set();
        for (const record of patientHistorySnapshotData) {
            const raw = record?.tooth_number;
            if (!['string', 'number'].includes(typeof raw) || !/^[1-9][0-9]*$/.test(String(raw))) return reject();
            const number = Number(raw);
            if (!Number.isSafeInteger(number) || number > 32 || seen.has(number)) return reject();
            seen.add(number);
            const condition = record.condition_type;
            if (typeof condition !== 'string' || (condition !== 'healthy' && !Object.hasOwn(conditionColors, condition))) return reject();
            // The canonical native ID contains a hyphen. A legacy exact ID is safe only
            // when it is the single matching element; both formats together are ambiguous.
            const matches = container.querySelectorAll(`[id="tooth-${number}"], [id="tooth${number}"]`);
            if (matches.length !== 1) return reject();
            const group = matches[0];
            const base = getLargestShape(group);
            if (!base) return reject();
            overlays.push({group, base, condition});
        }
        for (const {group, base, condition} of overlays) {
            group.querySelectorAll('.tooth-overlay, .tooth-outline, .tooth-hit').forEach(el => el.remove());
            if (condition !== 'healthy') {
                const overlay = base.cloneNode(true);
                overlay.removeAttribute('id');
                overlay.setAttribute('class', 'tooth-overlay');
                overlay.setAttribute('fill', conditionColors[condition]);
                overlay.setAttribute('fill-opacity', '0.38');
                overlay.setAttribute('stroke', 'none');
                group.appendChild(overlay);
            }
        }
        container.hidden = false;
        if (unavailable) unavailable.hidden = true;
    });
</script>
<?= $this->endSection() ?>
