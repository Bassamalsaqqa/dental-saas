<?php helper('asset'); ?>
<?= $this->extend('layouts/studio') ?>
<?php
// Function to convert Universal numbering to FDI numbering
function getFDINumber($universalNumber) {
    $fdiMap = [
        // Upper Right (1-8) -> FDI 11-18
        1 => '11', 2 => '12', 3 => '13', 4 => '14', 5 => '15', 6 => '16', 7 => '17', 8 => '18',
        // Upper Left (9-16) -> FDI 21-28
        9 => '21', 10 => '22', 11 => '23', 12 => '24', 13 => '25', 14 => '26', 15 => '27', 16 => '28',
        // Lower Left (17-24) -> FDI 31-38
        17 => '31', 18 => '32', 19 => '33', 20 => '34', 21 => '35', 22 => '36', 23 => '37', 24 => '38',
        // Lower Right (25-32) -> FDI 41-48
        25 => '41', 26 => '42', 27 => '43', 28 => '44', 29 => '45', 30 => '46', 31 => '47', 32 => '48'
    ];

    return $fdiMap[$universalNumber] ?? $universalNumber;
}

// Function to get condition styling classes
function getConditionClass($condition) {
    switch($condition) {
        case 'healthy':
            return 'bg-green-100 text-green-800';
        case 'cavity':
            return 'bg-yellow-100 text-yellow-800';
        case 'filling':
            return 'bg-blue-100 text-blue-800';
        case 'crown':
            return 'bg-purple-100 text-purple-800';
        case 'root_canal':
            return 'bg-pink-100 text-pink-800';
        case 'extracted':
            return 'bg-red-100 text-red-800';
        case 'implant':
            return 'bg-indigo-100 text-indigo-800';
        case 'bridge':
            return 'bg-teal-100 text-teal-800';
        case 'partial_denture':
            return 'bg-orange-100 text-orange-800';
        case 'full_denture':
            return 'bg-amber-100 text-amber-800';
        case 'other':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-green-100 text-green-800';
    }
}

// Function to get status styling classes
function getStatusClass($status) {
    switch($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'in_progress':
            return 'bg-orange-100 text-orange-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'needs_attention':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-green-100 text-green-800';
    }
}

// Function to get status display text
function getStatusText($status) {
    switch($status) {
        case "pending":
            return "Pending";
        case "in_progress":
            return "In Progress";
        case "completed":
            return "Completed";
        case "needs_attention":
            return "Needs Attention";
        default:
            return "Completed";
    }
}
?>

<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-chart.css') ?>?v=<?= esc(asset_version('assets/css/studio-chart.css')) ?>"><?php // Original native SVG/outline styles remain unchanged. ?><style>
#odontogramSvgWrapper svg {
    width: 100%;
    height: auto;
}

#odontogramSvgWrapper text {
    pointer-events: none;
    font-family: inherit;
}

#odontogramSvgWrapper .tooth-hit {
    outline: none;
}

#odontogramSvgWrapper .tooth-outline {
    stroke: #3b82f6;
    opacity: 0;
}

#odontogramSvgWrapper .tooth-overlay {
    pointer-events: none;
    mix-blend-mode: multiply;
}

#odontogramSvgWrapper .tooth-group {
    cursor: pointer;
}

#odontogramSvgWrapper .tooth-group.is-hover .tooth-outline {
    opacity: 0.9;
}

#odontogramSvgWrapper .tooth-group.is-selected .tooth-outline {
    opacity: 1;
    stroke: #f97316;
    stroke-width: 2;
}

#odontogramSvgWrapper .tooth-group.is-batch-selected .tooth-outline {
    opacity: 1;
    stroke: #f59e0b;
    stroke-width: 2;
}

/* Animation classes for tooth updates */
.updating {
    animation: pulse 1s infinite;
    opacity: 0.7;
}

.success {
    animation: bounce 0.6s ease-in-out;
    background: linear-gradient(135deg, #10b981, #34d399) !important;
    border-color: #059669 !important;
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0,0,0);
    }
    40%, 43% {
        transform: translate3d(0, -8px, 0);
    }
    70% {
        transform: translate3d(0, -4px, 0);
    }
    90% {
        transform: translate3d(0, -2px, 0);
    }
}
</style><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$isExaminationChart = !empty($examination_id);
$chartTitle = $isExaminationChart ? lang('Examination.visit_chart') : lang('Examination.patient_chart');
$chartHelp = $isExaminationChart ? lang('Examination.visit_chart_help') : lang('Examination.patient_chart_help');
$chartReturnTo = $return_to ?? ($isExaminationChart ? 'examination/' . (int) $examination_id : 'patient/' . (int) $patient['id']);
$treatmentCreateQuery = [
    'patient_id' => (int) $patient['id'],
    'return_to' => $chartReturnTo,
];
if ($isExaminationChart) {
    $treatmentCreateQuery['examination_id'] = (int) $examination_id;
}
$treatmentCreateUrl = base_url('treatment/create?' . http_build_query($treatmentCreateQuery, '', '&', PHP_QUERY_RFC3986));
$contractCreateUrl = $isExaminationChart
    ? workflow_context_url(
        'contracts/create/' . (int) $patient['id'] . '?examination_id=' . (int) $examination_id,
        $chartReturnTo
    )
    : workflow_context_url('contracts/create/' . (int) $patient['id'], $chartReturnTo);
?>
<div class="chart-workspace">
<header class="chart-heading"><div><h1><?= esc($chartTitle) ?></h1><p><?= esc($chartHelp) ?> <?php if($isExaminationChart): ?><bdi><?= esc($examination['examination_id']??('EX-'.$examination_id)) ?></bdi><?php endif; ?></p></div><a class="button secondary" href="<?= esc(workflow_back('patient/'.$patient['id'],$return_to??null),'attr') ?>"><?= esc(lang('Studio.back')) ?></a></header>
<section class="chart-context"><div><span><?= esc(lang('UI.currentPatient')) ?></span><strong><?= esc(trim($patient['first_name'].' '.$patient['last_name'])) ?></strong><bdi><?= esc($patient['patient_id']) ?></bdi></div><nav aria-label="<?= esc(lang('Studio.chartActions'),'attr') ?>"><?php if(has_permission('treatments','create')): ?><a class="button secondary" href="<?= esc($treatmentCreateUrl,'attr') ?>"><?= esc(lang('Examination.add_treatment')) ?></a><?php endif; ?><?php if(has_permission('patients','edit')): ?><a class="button secondary" href="<?= esc($contractCreateUrl,'attr') ?>"><?= esc(lang('Examination.create_treatment_plan')) ?></a><?php endif; ?><button class="button secondary" type="button" onclick="printOdontogram()"><?= esc(lang('Studio.printPage')) ?></button></nav></section>
<div class="chart-layout"><section class="chart-canvas-panel"><header><div><h2><?= esc(lang('Studio.chartInteractive')) ?></h2><p><?= esc(lang('Studio.chartInstruction')) ?></p></div><div class="chart-toolbar"><button type="button" id="legendToggleBtn"><span id="legendToggleIcon" hidden></span><span id="legendToggleText"><?= esc(lang('Studio.chartShowLegend')) ?></span></button><button type="button" id="batchModeToggleBtn" onclick="toggleBatchMode()"><span id="batchModeToggleText"><?= esc(lang('Studio.chartBatchSelect')) ?></span></button></div></header>
<div id="batchTopControls" class="hidden chart-batch-tools"><span id="batchTopCount">0 <?= esc(lang('Studio.chartSelected')) ?></span><button type="button" id="batchApplyBtn" onclick="openBatchModal()" disabled><?= esc(lang('Studio.chartApply')) ?></button><button type="button" id="batchClearBtn" onclick="clearBatchSelection()"><?= esc(lang('Studio.stockClearSearch')) ?></button><button type="button" id="batchExitBtn" onclick="toggleBatchMode(false)"><?= esc(lang('Studio.chartExitBatch')) ?></button></div>
<div id="legendContent" class="hidden chart-legend"><?php foreach(['healthy','cavity','filling','crown','root_canal','extracted','implant','bridge','partial_denture','full_denture','other'] as $condition): ?><div><span class="chart-condition-swatch chart-swatch-<?= esc($condition,'attr') ?>" aria-hidden="true"></span><span><?= esc(lang('UI.patientCondition'.str_replace(' ','',ucwords(str_replace('_',' ',$condition))))) ?></span></div><?php endforeach; ?></div>
<div class="chart-svg-scroll" role="region" tabindex="0" aria-label="<?= esc(lang('Studio.chartInteractive'),'attr') ?>"><div id="odontogramSvgWrapper">                            <?php
                            $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
                            $odontogramSvg = '';
                            if (is_file($svgPath)) {
                                $odontogramSvg = preg_replace('/<\?xml.*?\?>/i', '', file_get_contents($svgPath), 1);
                            }
                            ?>
                            <?= $odontogramSvg ?></div></div>
</section><aside class="chart-summary"><section><h2><?= esc(lang('Studio.chartRecordedState')) ?></h2><dl><?php foreach(['totalConditions'=>['total_teeth','chartRecordedEntries'],'healthyCount'=>['healthy_teeth','chartHealthy'],'cavitiesCount'=>['cavities','chartCavities'],'fillingsCount'=>['fillings','chartFillings'],'crownsCount'=>['crowns','chartCrowns'],'extractedCount'=>['extracted','chartExtracted'],'needsTreatmentCount'=>['needs_attention','chartNeedsAttention']] as $id=>[$key,$label]): ?><div><dt><?= esc(lang('Studio.'.$label)) ?></dt><dd id="<?= $id ?>"><?= $id==='totalConditions'?count($odontogram):esc($stats[$key]??0) ?></dd></div><?php endforeach; ?></dl></section><section><h2><?= esc(lang('Studio.chartRecordActions')) ?></h2><div class="chart-record-actions"><button type="button" onclick="exportData()"><?= esc(lang('Studio.chartExport')) ?></button><details><summary><?= esc(lang('Studio.chartBulkActions')) ?></summary><button type="button" onclick="resetAllTeeth()"><?= esc(lang('Studio.chartResetAll')) ?></button><button type="button" onclick="markAllHealthy()"><?= esc(lang('Studio.chartMarkHealthy')) ?></button></details></div></section></aside></div>
<section class="chart-condition-records"><header><h2><?= esc(lang('Studio.chartConditionRecords')) ?></h2><p><?= esc(lang('Studio.chartRecordedConditionsHelp')) ?></p></header><div class="chart-table-scroll" role="region" tabindex="0" aria-label="<?= esc(lang('Studio.chartConditionRecords'),'attr') ?>">                    <table class="studio-grid chart-condition-table" data-grid-widths="7,7,14,13,20,17,12,10">
                        <thead>
                            <tr >
                                <th ><?= esc(lang('Studio.chartTooth')) ?></th>
                                <th ><?= esc(lang('Studio.chartFdi')) ?></th>
                                <th ><?= esc(lang('Studio.chartPosition')) ?></th>
                                <th ><?= esc(lang('Studio.chartCondition')) ?></th>
                                <th ><?= esc(lang('Studio.chartDescription')) ?></th>
                                <th ><?= esc(lang('Studio.chartTreatmentNotes')) ?></th>
                                <th ><?= esc(lang('Studio.chartTreatmentStatus')) ?></th>
                                <th ><?= esc(lang('Studio.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody id="teethTableBody">
                            <?php
                            $teethWithConditions = [];
                            for ($i = 1; $i <= 32; $i++) {
                                $toothData = null;
                                foreach ($odontogram as $tooth) {
                                    if ($tooth['tooth_number'] == $i) {
                                        $toothData = $tooth;
                                        break;
                                    }
                                }

                                $condition = $toothData ? $toothData['condition_type'] : 'healthy';

                                // Only include teeth with conditions other than healthy
                                if ($condition !== 'healthy') {
                                    $teethWithConditions[] = [
                                        'tooth_number' => $i,
                                        'tooth_data' => $toothData,
                                        'condition' => $condition
                                    ];
                                }
                            }

                            if (empty($teethWithConditions)): ?>
                                <tr>
                                    <td colspan="8" >
                                        <div >

                                            <div ><?= esc(lang('Studio.chartNoRecordedConditions')) ?></div>
                                            <div ><?= esc(lang('Studio.chartNoConditionsHelp')) ?></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teethWithConditions as $toothInfo):
                                    $i = $toothInfo['tooth_number'];
                                    $toothData = $toothInfo['tooth_data'];
                                    $condition = $toothInfo['condition'];

                                    // Determine tooth position
                                    $position = '';
                                    if ($i >= 1 && $i <= 8) {
                                        $position = 'Upper Right';
                                    } elseif ($i >= 9 && $i <= 16) {
                                        $position = 'Upper Left';
                                    } elseif ($i >= 17 && $i <= 24) {
                                        $position = 'Lower Left';
                                    } elseif ($i >= 25 && $i <= 32) {
                                        $position = 'Lower Right';
                                    }

                                    $description = $toothData ? $toothData['condition_description'] : '';
                                    $treatment = $toothData ? $toothData['treatment_notes'] : '';
                                    $status = $toothData ? $toothData['treatment_status'] : 'completed';
                                ?>
                                <tr  data-tooth="<?= $i ?>">
                                    <td ><?= $i ?></td>
                                    <td ><?= getFDINumber($i) ?></td>
                                    <td ><?= esc(lang('Studio.chartPosition'.str_replace(' ','',$position))) ?></td>
                                    <td >
                                        <span >
                                            <?= esc(lang('UI.patientCondition'.str_replace(' ','',ucwords(str_replace('_',' ',$condition))))) ?>
                                        </span>
                                    </td>
                                    <td  title="<?= htmlspecialchars($description) ?>">
                                        <?= $description ? htmlspecialchars($description) : '-' ?>
                                    </td>
                                    <td  title="<?= htmlspecialchars($treatment) ?>">
                                        <?= $treatment ? htmlspecialchars($treatment) : '-' ?>
                                    </td>
                                    <td >
                                        <span >
                                            <?= getStatusText($status) ?>
                                        </span>
                                    </td>
                                    <td >
                                        <button onclick="openToothModal(<?= $i ?>)" >
                                            <?= esc(lang('Studio.editRecord')) ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div></section>
</div>
<div id="toothModal" class="chart-modal hidden" role="dialog" aria-modal="true" aria-labelledby="chart-tooth-title"><div class="chart-modal-panel" id="modalContent"><header><h2 id="chart-tooth-title"><?= esc(lang('Studio.chartTooth')) ?> <bdi id="modalToothNumber"></bdi></h2><button type="button" onclick="closeToothModal()"><?= esc(lang('Studio.close')) ?></button></header><div class="chart-modal-body"><form id="toothForm" >
                    <?= csrf_field() ?>
                    <input type="hidden" id="toothNumber" name="tooth_number">
                    <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                    <input type="hidden" name="examination_id" value="<?= esc($examination_id ?? '') ?>">

                    <div>
                        <label for="condition_type" ><?= esc(lang('Studio.chartCondition')) ?></label>
                        <select id="condition_type" name="condition_type"  required>
                            <option value=""><?= esc(lang('Studio.chartSelectCondition')) ?></option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?php $conditionLabel=lang('UI.patientCondition'.str_replace(' ','',ucwords(str_replace('_',' ',$key)))); ?><?= esc(str_starts_with($conditionLabel,'UI.')?$label:$conditionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="condition_description" ><?= esc(lang('Studio.chartDescription')) ?></label>
                        <textarea id="condition_description" name="condition_description" rows="2"  placeholder="<?= esc(lang('Studio.chartDescriptionHint'),'attr') ?>"></textarea>
                    </div>

                    <div>
                        <label for="treatment_notes" ><?= esc(lang('Examination.follow_up_note')) ?></label>
                        <textarea id="treatment_notes" name="treatment_notes" rows="2"  placeholder="<?= esc(lang('Examination.follow_up_note_placeholder')) ?>"></textarea>
                    </div>

                    <div>
                        <label for="treatment_status" ><?= esc(lang('Examination.tooth_attention_status')) ?></label>
                        <select id="treatment_status" name="treatment_status" >
                            <option value="pending"><?= esc(lang('Studio.chartPending')) ?></option>
                            <option value="in_progress"><?= esc(lang('Studio.chartInProgress')) ?></option>
                            <option value="completed"><?= esc(lang('Studio.chartCompleted')) ?></option>
                            <option value="needs_attention"><?= esc(lang('Studio.chartNeedsAttention')) ?></option>
                        </select>
                    </div>

                    <div >
                        <?= esc(lang('Examination.odontogram_record_boundary')) ?>
                    </div>

                    <div >
                        <div >
                            <button type="button" onclick="closeToothModal()" >
                                <?= esc(lang('Studio.cancel')) ?>
                            </button>
                            <button type="button" onclick="resetTooth()" >
                                <?= esc(lang('Studio.chartRemoveStatus')) ?>
                            </button>
                            <?php if (has_permission('treatments', 'create')): ?>
                                <a id="createTreatmentForToothLink" href="<?= esc($treatmentCreateUrl, 'attr') ?>" >
                                    <?= esc(lang('Examination.add_treatment')) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <button type="submit" >
                            <?= esc(lang('Studio.chartApply')) ?>
                        </button>
                    </div>
                </form></div></div></div>
<div id="batchModal" class="chart-modal hidden" role="dialog" aria-modal="true" aria-labelledby="chart-batch-title"><div class="chart-modal-panel" id="batchModalContent"><header><div><h2 id="chart-batch-title"><?= esc(lang('Studio.chartApplySelected')) ?></h2><p><?= esc(lang('Studio.chartSelected')) ?>: <bdi id="batchModalCount">0</bdi></p></div><button type="button" onclick="closeBatchModal()"><?= esc(lang('Studio.close')) ?></button></header><div class="chart-modal-body"><form id="batchForm" >
                    <div>
                        <label for="batch_condition_type" ><?= esc(lang('Studio.chartCondition')) ?></label>
                        <select id="batch_condition_type" name="condition_type"  required>
                            <option value=""><?= esc(lang('Studio.chartSelectCondition')) ?></option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?php $conditionLabel=lang('UI.patientCondition'.str_replace(' ','',ucwords(str_replace('_',' ',$key)))); ?><?= esc(str_starts_with($conditionLabel,'UI.')?$label:$conditionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="batch_condition_description" ><?= esc(lang('Studio.chartDescription')) ?></label>
                        <textarea id="batch_condition_description" name="condition_description" rows="2"  placeholder="<?= esc(lang('Studio.chartSharedDescriptionHint'),'attr') ?>"></textarea>
                    </div>
                    <div>
                        <label for="batch_treatment_notes" ><?= esc(lang('Studio.chartTreatmentNotes')) ?></label>
                        <textarea id="batch_treatment_notes" name="treatment_notes" rows="2"  placeholder="<?= esc(lang('Studio.chartSharedNotesHint'),'attr') ?>"></textarea>
                    </div>
                    <div>
                        <label for="batch_treatment_status" ><?= esc(lang('Studio.chartTreatmentStatus')) ?></label>
                        <select id="batch_treatment_status" name="treatment_status" >
                            <option value="pending"><?= esc(lang('Studio.chartPending')) ?></option>
                            <option value="in_progress"><?= esc(lang('Studio.chartInProgress')) ?></option>
                            <option value="completed"><?= esc(lang('Studio.chartCompleted')) ?></option>
                            <option value="needs_attention"><?= esc(lang('Studio.chartNeedsAttention')) ?></option>
                        </select>
                    </div>
                    <div >
                        <div >
                            <button type="button" onclick="closeBatchModal()" >
                                <?= esc(lang('Studio.cancel')) ?>
                            </button>
                            <button type="button" onclick="batchResetSelected()" >
                                <?= esc(lang('Studio.chartRemoveStatus')) ?>
                            </button>
                        </div>
                        <button type="submit" id="batchSubmitBtn" >
                            <?= esc(lang('Studio.chartApply')) ?>
                        </button>
                    </div>
                </form></div></div></div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>window.csrfConfig={name:document.querySelector('meta[name="csrf-name"]').content,header:document.querySelector('meta[name="csrf-header"]').content};window.csrfHash=document.querySelector('meta[name="csrf-token"]').content;window.getCsrfToken=()=>window.csrfHash;window.refreshCsrfToken=token=>{if(token){window.csrfHash=token;document.querySelector('meta[name="csrf-token"]').content=token;for(const field of document.querySelectorAll('input[type=hidden]'))if(field.name===window.csrfConfig.name)field.value=token;}return window.csrfHash;};</script>
<script>
const patientId = <?= $patient['id'] ?>;
const examinationId = <?= isset($examination_id) && $examination_id ? (int)$examination_id : 'null' ?>;
const odontogramData = <?= json_encode($odontogram) ?>;
const conditionTypes = <?= json_encode($condition_types) ?>;
const svgNS = 'http://www.w3.org/2000/svg';
const conditionColors = {
    cavity: '#fbbf24',
    filling: '#3b82f6',
    crown: '#a855f7',
    root_canal: '#ec4899',
    extracted: '#ef4444',
    implant: '#6366f1',
    bridge: '#0ea5e9',
    partial_denture: '#f97316',
    full_denture: '#f59e0b',
    other: '#6b7280'
};

const toothGroups = new Map();
const toothOverlays = new Map();
const toothOutlines = new Map();
const toothHits = new Map();
let activeTooth = null;
let tooltipEl = null;
let batchModeEnabled = false;
const batchSelectedTeeth = new Set();

document.addEventListener('DOMContentLoaded', () => {
    setupSvgOdontogram();
    initializeOdontogram();
    attachModalBehavior();
    attachFormHandler();
    attachBatchHandler();
    updateBatchUi();
});

function getFdiNumber(universalNumber) {
    const num = parseInt(universalNumber, 10);
    if (isNaN(num)) {
        return universalNumber;
    }

    if (num >= 1 && num <= 8) {
        return (10 + num).toString();
    }
    if (num >= 9 && num <= 16) {
        return (12 + num).toString();
    }
    if (num >= 17 && num <= 24) {
        return (14 + num).toString();
    }
    if (num >= 25 && num <= 32) {
        return (16 + num).toString();
    }

    return num.toString();
}

function setOutlineVisible(toothNumber, visible) {
    const outline = toothOutlines.get(toothNumber);
    if (!outline) {
        return;
    }
    outline.style.opacity = visible ? '0.9' : '0';
}

function isToothBatchSelected(toothNumber) {
    return batchSelectedTeeth.has(parseInt(toothNumber, 10));
}

function syncBatchToothVisual(toothNumber) {
    const tooth = parseInt(toothNumber, 10);
    const group = toothGroups.get(tooth);
    if (!group) {
        return;
    }
    const selected = isToothBatchSelected(tooth);
    group.classList.toggle('is-batch-selected', selected);
    if (selected) {
        setOutlineVisible(tooth, true);
    } else if (!group.classList.contains('is-hover') && !group.classList.contains('is-selected')) {
        setOutlineVisible(tooth, false);
    }
}

function updateBatchUi() {
    const toggleBtn = document.getElementById('batchModeToggleBtn');
    const toggleText = document.getElementById('batchModeToggleText');
    const topControls = document.getElementById('batchTopControls');
    const countEl = document.getElementById('batchTopCount');
    const applyBtn = document.getElementById('batchApplyBtn');
    const count = batchSelectedTeeth.size;

    if (toggleBtn) {
        toggleBtn.classList.toggle('bg-amber-500', batchModeEnabled);
        toggleBtn.classList.toggle('hover:bg-amber-600', batchModeEnabled);
        toggleBtn.classList.toggle('border-amber-300', batchModeEnabled);
        toggleBtn.classList.toggle('shadow-lg', batchModeEnabled);
        toggleBtn.classList.toggle('bg-white/20', !batchModeEnabled);
        toggleBtn.classList.toggle('hover:bg-white/30', !batchModeEnabled);
        toggleBtn.classList.toggle('border-white/30', !batchModeEnabled);
        toggleBtn.classList.toggle('shadow-none', !batchModeEnabled);
    }
    if (toggleText) {
        toggleText.textContent = batchModeEnabled ? 'Batch Mode On' : 'Batch Select';
    }
    if (topControls) {
        topControls.classList.toggle('hidden', !batchModeEnabled);
        topControls.classList.toggle('flex', batchModeEnabled);
    }
    if (countEl) {
        countEl.textContent = `${count} selected`;
    }
    if (applyBtn) {
        const enabled = count > 0;
        applyBtn.disabled = !enabled;
        applyBtn.classList.toggle('bg-gradient-to-r', enabled);
        applyBtn.classList.toggle('from-emerald-500', enabled);
        applyBtn.classList.toggle('to-green-600', enabled);
        applyBtn.classList.toggle('hover:from-emerald-600', enabled);
        applyBtn.classList.toggle('hover:to-green-700', enabled);
        applyBtn.classList.toggle('border-emerald-300/70', enabled);
        applyBtn.classList.toggle('shadow-lg', enabled);
        applyBtn.classList.toggle('text-white', enabled);

        applyBtn.classList.toggle('bg-white/20', !enabled);
        applyBtn.classList.toggle('hover:bg-white/20', !enabled);
        applyBtn.classList.toggle('border-white/30', !enabled);
        applyBtn.classList.toggle('shadow-none', !enabled);
        applyBtn.classList.toggle('text-white/70', !enabled);
    }
}

function clearBatchSelection() {
    Array.from(batchSelectedTeeth).forEach(tooth => {
        batchSelectedTeeth.delete(tooth);
        syncBatchToothVisual(tooth);
    });
    updateBatchUi();
}

function toggleBatchMode(forceState = null) {
    const shouldEnable = forceState === null ? !batchModeEnabled : !!forceState;
    if (shouldEnable === batchModeEnabled) {
        return;
    }

    batchModeEnabled = shouldEnable;
    if (batchModeEnabled) {
        closeToothModal();
    } else {
        clearBatchSelection();
        closeBatchModal();
    }
    updateBatchUi();
}

function toggleBatchTooth(toothNumber) {
    const tooth = parseInt(toothNumber, 10);
    if (!batchModeEnabled || !tooth) {
        return;
    }
    if (batchSelectedTeeth.has(tooth)) {
        batchSelectedTeeth.delete(tooth);
    } else {
        batchSelectedTeeth.add(tooth);
    }
    syncBatchToothVisual(tooth);
    updateBatchUi();
}

function createTooltip() {
    const el = document.createElement('div');
    el.id = 'odontogramTooltip';
    el.className = 'fixed z-50 pointer-events-none px-3 py-2 rounded-md text-xs font-medium shadow-lg bg-slate-900 text-white opacity-0 transition-opacity duration-150';
    document.body.appendChild(el);
    return el;
}

function showTooltip(event, toothNumber) {
    if (!tooltipEl) {
        tooltipEl = createTooltip();
    }

    const condition = getConditionForTooth(toothNumber);
    const conditionLabel = conditionTypes?.[condition] || 'Healthy';
    tooltipEl.textContent = `Tooth ${toothNumber} (FDI ${getFdiNumber(toothNumber)}) - ${conditionLabel}`;
    tooltipEl.style.left = `${event.clientX + 12}px`;
    tooltipEl.style.top = `${event.clientY + 12}px`;
    tooltipEl.style.opacity = '1';
}

function moveTooltip(event) {
    if (!tooltipEl || tooltipEl.style.opacity !== '1') {
        return;
    }
    tooltipEl.style.left = `${event.clientX + 12}px`;
    tooltipEl.style.top = `${event.clientY + 12}px`;
}

function hideTooltip() {
    if (!tooltipEl) {
        return;
    }
    tooltipEl.style.opacity = '0';
}

function parseToothNumber(element) {
    if (!element) {
        return null;
    }
    const dataTooth = element.dataset?.tooth;
    if (dataTooth) {
        return parseInt(dataTooth, 10);
    }
    const id = element.getAttribute('id') || '';
    const match = id.match(/^tooth-(\d+)$/);
    if (match) {
        return parseInt(match[1], 10);
    }
    return null;
}

function getLargestShape(group) {
    const shapes = Array.from(group.querySelectorAll('path, polygon, rect, circle, ellipse'))
        .filter(el =>
            !el.classList.contains('tooth-hit') &&
            !el.classList.contains('tooth-overlay') &&
            !el.classList.contains('tooth-outline')
        );

    const getOpacity = (el) => {
        const attr = el.getAttribute('opacity');
        if (attr !== null && attr !== '') {
            const value = Number(attr);
            return Number.isFinite(value) ? value : 1;
        }
        const styleValue = el.style && el.style.opacity ? Number(el.style.opacity) : NaN;
        return Number.isFinite(styleValue) ? styleValue : 1;
    };

    const inMultiplyGroup = (el) => {
        let node = el;
        while (node && node !== group) {
            if (node.getAttribute && (node.getAttribute('mix-blend-mode') || '').toLowerCase() === 'multiply') {
                return true;
            }
            node = node.parentNode;
        }
        return false;
    };

    const isVisibleFill = (el) => {
        const fill = (el.getAttribute('fill') || '').trim().toLowerCase();
        if (!fill || fill === 'none' || fill === 'transparent') {
            return false;
        }
        return true;
    };

    const filled = shapes.filter(isVisibleFill);
    const candidates = filled.length ? filled : shapes;
    let best = null;
    let bestScore = -1;

    candidates.forEach(shape => {
        let bbox;
        try {
            bbox = shape.getBBox();
        } catch (e) {
            return;
        }
        const area = bbox.width * bbox.height;
        if (!area || area <= 0) {
            return;
        }

        const opacity = Math.max(0, Math.min(1, getOpacity(shape)));
        const multiplyPenalty = inMultiplyGroup(shape) ? 0.15 : 1;
        const score = area * (0.25 + 0.75 * opacity) * multiplyPenalty;

        if (score > bestScore) {
            bestScore = score;
            best = shape;
        }
    });

    return best;
}

function ensureToothControls(group, toothNumber) {
    group.querySelectorAll('.tooth-overlay, .tooth-outline, .tooth-hit').forEach(el => el.remove());
    let overlay = null;
    let outline = null;
    let hit = null;
    const base = getLargestShape(group);
    if (base) {
        group.querySelectorAll('path, polygon, rect, circle, ellipse').forEach(shape => {
            if (!shape.classList.contains('tooth-hit') && !shape.classList.contains('tooth-overlay') && !shape.classList.contains('tooth-outline')) {
                shape.style.pointerEvents = 'none';
            }
        });

        if (!overlay) {
            overlay = base.cloneNode(true);
            overlay.removeAttribute('id');
            overlay.setAttribute('class', 'tooth-overlay');
            overlay.setAttribute('fill', 'transparent');
            overlay.setAttribute('opacity', '0');
            overlay.style.pointerEvents = 'none';
            group.appendChild(overlay);
        }
        if (!outline) {
            outline = base.cloneNode(true);
            outline.removeAttribute('id');
            outline.setAttribute('class', 'tooth-outline');
            outline.setAttribute('fill', 'none');
            outline.setAttribute('stroke', '#3b82f6');
            outline.setAttribute('stroke-width', '1.5');
            outline.setAttribute('opacity', '0');
            outline.style.pointerEvents = 'none';
            group.appendChild(outline);
        }
        if (!hit) {
            hit = base.cloneNode(true);
            hit.removeAttribute('id');
            hit.setAttribute('class', 'tooth-hit');
            hit.setAttribute('fill', 'transparent');
            hit.setAttribute('opacity', '0');
            hit.style.pointerEvents = 'fill';
            group.appendChild(hit);
        }
    }

    if (hit) {
        hit.style.cursor = 'pointer';
        hit.setAttribute('tabindex', '0');
        hit.setAttribute('role', 'button');
        hit.setAttribute('aria-label', `Tooth ${toothNumber} (FDI ${getFdiNumber(toothNumber)})`);
    }

    if (overlay) {
        overlay.style.pointerEvents = 'none';
    }
    if (outline) {
        outline.style.pointerEvents = 'none';
    }

    return { overlay, outline, hit };
}

function cacheToothGroups(svg) {
    toothGroups.clear();
    toothOverlays.clear();
    toothOutlines.clear();
    toothHits.clear();

    const groups = Array.from(svg.querySelectorAll('[id^=\"tooth-\"]'))
        .filter(element => (element.tagName || '').toLowerCase().endsWith('g'));
    groups.forEach(group => {
        const toothNumber = parseToothNumber(group);
        if (!toothNumber) {
            return;
        }

        group.classList.add('tooth-group');
        group.dataset.tooth = toothNumber;

        const { overlay, outline, hit } = ensureToothControls(group, toothNumber);

        if (overlay) {
            toothOverlays.set(toothNumber, overlay);
        }
        if (outline) {
            toothOutlines.set(toothNumber, outline);
        }
        if (hit) {
            toothHits.set(toothNumber, hit);
            hit.addEventListener('mouseenter', event => {
                group.classList.add('is-hover');
                setOutlineVisible(toothNumber, true);
                showTooltip(event, toothNumber);
            });
            hit.addEventListener('mousemove', moveTooltip);
            hit.addEventListener('mouseleave', () => {
                group.classList.remove('is-hover');
                if (!group.classList.contains('is-selected') && !group.classList.contains('is-batch-selected')) {
                    setOutlineVisible(toothNumber, false);
                }
                hideTooltip();
            });
            hit.addEventListener('focus', event => {
                group.classList.add('is-hover');
                setOutlineVisible(toothNumber, true);
                showTooltip(event, toothNumber);
            });
            hit.addEventListener('blur', () => {
                group.classList.remove('is-hover');
                if (!group.classList.contains('is-selected') && !group.classList.contains('is-batch-selected')) {
                    setOutlineVisible(toothNumber, false);
                }
                hideTooltip();
            });
            hit.addEventListener('click', () => {
                if (batchModeEnabled) {
                    toggleBatchTooth(toothNumber);
                } else {
                    openToothModal(toothNumber);
                }
            });
            hit.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    if (batchModeEnabled) {
                        toggleBatchTooth(toothNumber);
                    } else {
                        openToothModal(toothNumber);
                    }
                }
            });
        }

        toothGroups.set(toothNumber, group);
    });
}

function injectConditionFilters(svg) {
    let defs = svg.querySelector('defs');
    if (!defs) {
        defs = document.createElementNS(svgNS, 'defs');
        svg.prepend(defs);
    }

    Object.entries(conditionFilterColors).forEach(([condition, color]) => {
        const filterId = `cond-${condition}`;
        if (svg.getElementById(filterId)) {
            return;
        }

        const filter = document.createElementNS(svgNS, 'filter');
        filter.setAttribute('id', filterId);
        filter.setAttribute('filterUnits', 'objectBoundingBox');

        const flood = document.createElementNS(svgNS, 'feFlood');
        flood.setAttribute('flood-color', color);
        flood.setAttribute('flood-opacity', '0.72');
        flood.setAttribute('result', 'flood');

        const composite = document.createElementNS(svgNS, 'feComposite');
        composite.setAttribute('in', 'flood');
        composite.setAttribute('in2', 'SourceAlpha');
        composite.setAttribute('operator', 'in');
        composite.setAttribute('result', 'mask');

        const merge = document.createElementNS(svgNS, 'feMerge');
        const mergeNodeFlood = document.createElementNS(svgNS, 'feMergeNode');
        mergeNodeFlood.setAttribute('in', 'mask');
        const mergeNodeGraphic = document.createElementNS(svgNS, 'feMergeNode');
        mergeNodeGraphic.setAttribute('in', 'SourceGraphic');
        merge.appendChild(mergeNodeFlood);
        merge.appendChild(mergeNodeGraphic);

        filter.appendChild(flood);
        filter.appendChild(composite);
        filter.appendChild(merge);
        defs.appendChild(filter);
    });
}

function setupSvgOdontogram() {
    const wrapper = document.getElementById('odontogramSvgWrapper');
    if (!wrapper) {
        console.warn('Odontogram SVG wrapper not found');
        return;
    }

    const svg = wrapper.querySelector('svg');
    if (!svg) {
        console.warn('Inline odontogram SVG is missing');
        return;
    }

    svg.id = svg.id || 'odontogramSvg';
    svg.removeAttribute('width');
    svg.removeAttribute('height');
    svg.style.width = '100%';
    svg.style.height = 'auto';

    cacheToothGroups(svg);
}

function initializeOdontogram() {
    toothGroups.forEach((_, toothNumber) => {
        applyToothVisual(toothNumber);
    });

    updateStats();
}

function getConditionForTooth(toothNumber) {
    const record = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
    return record ? record.condition_type : 'healthy';
}

function applyToothVisual(toothNumber) {
    const overlay = toothOverlays.get(parseInt(toothNumber, 10));
    if (!overlay) {
        return;
    }

    const condition = getConditionForTooth(toothNumber);
    if (!condition || condition === 'healthy') {
        overlay.setAttribute('opacity', '0');
        overlay.setAttribute('fill', 'transparent');
    } else {
        overlay.setAttribute('fill', conditionColors[condition] || '#6b7280');
        overlay.setAttribute('opacity', '0.28');
    }
}

function getToothNode(toothNumber) {
    if (!toothNumber) {
        return null;
    }

    return toothGroups.get(parseInt(toothNumber, 10));
}

function updateStats() {
    const stats = {
        total_conditions: odontogramData.length,
        healthy_count: odontogramData.filter(t => t.condition_type === 'healthy').length,
        needs_treatment_count: odontogramData.filter(t => ['cavity', 'extracted'].includes(t.condition_type)).length,
        cavities: odontogramData.filter(t => t.condition_type === 'cavity').length,
        fillings: odontogramData.filter(t => t.condition_type === 'filling').length,
        crowns: odontogramData.filter(t => t.condition_type === 'crown').length,
        extracted: odontogramData.filter(t => t.condition_type === 'extracted').length
    };

    const totalEl = document.getElementById('totalConditions');
    if (totalEl) totalEl.textContent = stats.total_conditions;

    const healthyEl = document.getElementById('healthyCount');
    if (healthyEl) healthyEl.textContent = stats.healthy_count;

    const needsTreatmentEl = document.getElementById('needsTreatmentCount');
    if (needsTreatmentEl) needsTreatmentEl.textContent = stats.needs_treatment_count;

    const cavitiesEl = document.getElementById('cavitiesCount');
    if (cavitiesEl) cavitiesEl.textContent = stats.cavities;

    const fillingsEl = document.getElementById('fillingsCount');
    if (fillingsEl) fillingsEl.textContent = stats.fillings;

    const crownsEl = document.getElementById('crownsCount');
    if (crownsEl) crownsEl.textContent = stats.crowns;

    const extractedEl = document.getElementById('extractedCount');
    if (extractedEl) extractedEl.textContent = stats.extracted;
}

function setSelectedTooth(toothNumber) {
    if (activeTooth) {
        const previous = toothGroups.get(parseInt(activeTooth, 10));
        previous?.classList.remove('is-selected');
    }

    activeTooth = toothNumber;
    const current = toothGroups.get(parseInt(toothNumber, 10));
    current?.classList.add('is-selected');
}

function clearSelectedTooth() {
    if (!activeTooth) {
        return;
    }
    const tooth = parseInt(activeTooth, 10);
    const current = toothGroups.get(tooth);
    if (current) {
        current.classList.remove('is-selected');
        current.classList.remove('is-hover');
    }
    if (!isToothBatchSelected(tooth)) {
        setOutlineVisible(tooth, false);
    }
    hideTooltip();
    activeTooth = null;
}

function openToothModal(toothNumber) {
    if (batchModeEnabled) {
        showNotification('Exit batch mode to edit a single tooth.', 'warning');
        return;
    }

    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    const conditionTypeEl = document.getElementById('condition_type');
    const descriptionEl = document.getElementById('condition_description');
    const notesEl = document.getElementById('treatment_notes');
    const statusEl = document.getElementById('treatment_status');
    const treatmentLink = document.getElementById('createTreatmentForToothLink');

    if (!modal || !modalContent) {
        return;
    }

    const modalNumber = document.getElementById('modalToothNumber');
    const toothInput = document.getElementById('toothNumber');
    modalNumber.textContent = toothNumber;
    toothInput.value = toothNumber;
    if (treatmentLink) {
        const treatmentUrl = new URL(treatmentLink.getAttribute('href'), window.location.origin);
        treatmentUrl.searchParams.set('tooth_number', String(toothNumber));
        treatmentLink.setAttribute('href', treatmentUrl.toString());
    }

    const existingTooth = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));

    if (existingTooth) {
        conditionTypeEl.value = existingTooth.condition_type || 'healthy';
        descriptionEl.value = existingTooth.condition_description || '';
        notesEl.value = existingTooth.treatment_notes || '';
        statusEl.value = existingTooth.treatment_status || 'pending';
    } else {
        document.getElementById('toothForm').reset();
        conditionTypeEl.value = 'healthy';
        statusEl.value = 'pending';
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);

    setSelectedTooth(toothNumber);
}

function closeToothModal() {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');

    if (!modal || !modalContent) {
        return;
    }

    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);

    clearSelectedTooth();
}

function openBatchModal() {
    if (!batchModeEnabled) {
        showNotification('Enable batch mode first.', 'warning');
        return;
    }
    const selected = batchSelectedTeeth.size;
    if (selected === 0) {
        showNotification('Select at least one tooth first.', 'warning');
        return;
    }

    const modal = document.getElementById('batchModal');
    const content = document.getElementById('batchModalContent');
    const countEl = document.getElementById('batchModalCount');
    if (!modal || !content) {
        return;
    }
    if (countEl) {
        countEl.textContent = String(selected);
    }
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.style.transform = 'scale(1)';
        content.style.opacity = '1';
    }, 10);
}

function closeBatchModal() {
    const modal = document.getElementById('batchModal');
    const content = document.getElementById('batchModalContent');
    if (!modal || !content) {
        return;
    }
    content.style.transform = 'scale(0.95)';
    content.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 250);
}

function upsertToothData(toothNumber, values) {
    const tooth = parseInt(toothNumber, 10);
    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === tooth);
    const updated = {
        tooth_number: tooth,
        condition_type: values.condition_type,
        condition_description: values.condition_description || '',
        treatment_notes: values.treatment_notes || '',
        treatment_status: values.treatment_status || 'pending',
        treatment_date: new Date().toISOString().split('T')[0],
        created_by: <?= (int) ($user->id ?? 0) ?>,
        examination_id: examinationId || null
    };
    if (existingIndex !== -1) {
        odontogramData[existingIndex] = updated;
    } else {
        odontogramData.push(updated);
    }
}

async function submitToothUpdate(toothNumber, values) {
    const form = document.getElementById('toothForm');
    const payload = form ? new FormData(form) : new FormData();
    payload.set('patient_id', String(patientId));
    payload.set('examination_id', examinationId ? String(examinationId) : '');
    payload.set('tooth_number', String(toothNumber));
    payload.set('condition_type', values.condition_type);
    payload.set('condition_description', values.condition_description || '');
    payload.set('treatment_notes', values.treatment_notes || '');
    payload.set('treatment_status', values.treatment_status || 'pending');

    const response = await fetch('<?= base_url('odontogram/update-tooth') ?>', {
        method: 'POST',
        body: payload
    });
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    if (data.csrf_token) {
        window.refreshCsrfToken(data.csrf_token);
    }
    if (!data.success) {
        throw new Error(data.message || 'Failed to update tooth condition');
    }
}

function attachModalBehavior() {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    const batchModal = document.getElementById('batchModal');
    const batchModalContent = document.getElementById('batchModalContent');
    if (modal) {
        modal.addEventListener('click', () => {
            closeToothModal();
        });
    }
    if (modalContent) {
        modalContent.addEventListener('click', event => {
            event.stopPropagation();
        });
    }
    if (batchModal) {
        batchModal.addEventListener('click', () => {
            closeBatchModal();
        });
    }
    if (batchModalContent) {
        batchModalContent.addEventListener('click', event => {
            event.stopPropagation();
        });
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeToothModal();
            closeBatchModal();
        }

        const modalOpen = modal && !modal.classList.contains('hidden');
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's' && modalOpen) {
            event.preventDefault();
            document.getElementById('toothForm').dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });
}

function attachFormHandler() {
    const form = document.getElementById('toothForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');

        if (!submitButton) {
            return;
        }

        if (!submitButton._originalChildren) {
            submitButton._originalChildren = Array.from(submitButton.childNodes).map(node => node.cloneNode(true));
        }

        submitButton.disabled = true;
        submitButton.classList.add('opacity-75');

        const spinnerIcon = document.createElement('i');
        spinnerIcon.className = 'fas fa-spinner fa-spin mr-2';
        submitButton.replaceChildren(spinnerIcon, document.createTextNode('Saving...'));

        const toothNumber = parseInt(formData.get('tooth_number'), 10);
        const toothElement = getToothNode(toothNumber);
        if (toothElement) {
            toothElement.classList.add('updating');
        }

        fetch('<?= base_url('odontogram/update-tooth') ?>', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.csrf_token) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (data.success) {
                    const condition = formData.get('condition_type');
                    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === toothNumber);
                    const updatedToothData = {
                        tooth_number: toothNumber,
                        condition_type: condition,
                        condition_description: formData.get('condition_description'),
                        treatment_notes: formData.get('treatment_notes'),
                        treatment_status: formData.get('treatment_status'),
                        treatment_date: new Date().toISOString().split('T')[0],
                        created_by: <?= (int) ($user->id ?? 0) ?>,
                        examination_id: examinationId || null
                    };

                    if (existingIndex !== -1) {
                        odontogramData[existingIndex] = updatedToothData;
                    } else {
                        odontogramData.push(updatedToothData);
                    }

                    if (toothElement) {
                        toothElement.classList.remove('updating');
                        toothElement.classList.add('success');
                        setTimeout(() => {
                            toothElement.classList.remove('success');
                        }, 600);
                    }

                    applyToothVisual(toothNumber);

                    showNotification('Tooth condition updated successfully!', 'success');
                    closeToothModal();
                    updateStats();
                } else {
                    throw new Error(data.message || 'Failed to update tooth condition');
                }
            })
            .catch(error => {
                if (toothElement) {
                    toothElement.classList.remove('updating');
                }
                showNotification('Error: ' + error.message, 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.replaceChildren(...submitButton._originalChildren.map(node => node.cloneNode(true)));
                submitButton.classList.remove('opacity-75');
            });
    });

    const legendToggleBtn = document.getElementById('legendToggleBtn');
    const legendContent = document.getElementById('legendContent');
    const legendToggleIcon = document.getElementById('legendToggleIcon');
    const legendToggleText = document.getElementById('legendToggleText');

    legendToggleBtn?.addEventListener('click', () => {
        if (!legendContent) {
            return;
        }
        const willShow = legendContent.classList.contains('hidden');
        legendContent.classList.toggle('hidden', !willShow);
        if (legendToggleIcon) {
            legendToggleIcon.classList.toggle('fa-chevron-down', !willShow);
            legendToggleIcon.classList.toggle('fa-chevron-up', willShow);
        }
        if (legendToggleText) {
            legendToggleText.textContent = willShow ? 'Hide Legend' : 'Show Legend';
        }
    });
}

function attachBatchHandler() {
    const form = document.getElementById('batchForm');
    const submitBtn = document.getElementById('batchSubmitBtn');
    if (!form || !submitBtn) {
        return;
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const selectedTeeth = Array.from(batchSelectedTeeth);
        if (!selectedTeeth.length) {
            showNotification('Select at least one tooth first.', 'warning');
            return;
        }

        const values = {
            condition_type: form.condition_type.value,
            condition_description: form.condition_description.value,
            treatment_notes: form.treatment_notes.value,
            treatment_status: form.treatment_status.value
        };
        if (!values.condition_type) {
            showNotification('Condition type is required.', 'warning');
            return;
        }

        submitBtn.disabled = true;
        const originalNodes = Array.from(submitBtn.childNodes).map((node) => node.cloneNode(true));
        submitBtn.replaceChildren();
        const spinnerIcon = document.createElement('i');
        spinnerIcon.className = 'fas fa-spinner fa-spin mr-2';
        submitBtn.appendChild(spinnerIcon);
        submitBtn.appendChild(document.createTextNode('Applying...'));

        let successCount = 0;
        const failedTeeth = [];

        for (const toothNumber of selectedTeeth) {
            try {
                await submitToothUpdate(toothNumber, values);
                upsertToothData(toothNumber, values);
                applyToothVisual(toothNumber);
                successCount++;
            } catch (error) {
                failedTeeth.push(toothNumber);
            }
        }

        updateStats();
        clearBatchSelection();
        closeBatchModal();

        if (successCount > 0) {
            showNotification(`Updated ${successCount} teeth successfully.`, 'success');
        }
        if (failedTeeth.length > 0) {
            showNotification(`Failed on teeth: ${failedTeeth.join(', ')}`, 'warning');
        }

        submitBtn.disabled = false;
        submitBtn.replaceChildren(...originalNodes);
    });
}

async function batchResetSelected() {
    const selectedTeeth = Array.from(batchSelectedTeeth);
    if (!selectedTeeth.length) {
        showNotification('Select at least one tooth first.', 'warning');
        return;
    }

    const resetValues = {
        condition_type: 'healthy',
        condition_description: '',
        treatment_notes: '',
        treatment_status: 'completed'
    };

    let successCount = 0;
    const failedTeeth = [];

    for (const toothNumber of selectedTeeth) {
        try {
            await submitToothUpdate(toothNumber, resetValues);
            upsertToothData(toothNumber, resetValues);
            applyToothVisual(toothNumber);
            successCount++;
        } catch (error) {
            failedTeeth.push(toothNumber);
        }
    }

    updateStats();
    clearBatchSelection();
    closeBatchModal();

    if (successCount > 0) {
        showNotification(`Removed status on ${successCount} teeth.`, 'success');
    }
    if (failedTeeth.length > 0) {
        showNotification(`Failed on teeth: ${failedTeeth.join(', ')}`, 'warning');
    }
}

function resetTooth() {
    const toothNumber = document.getElementById('toothNumber').value;
    if (!toothNumber) {
        return;
    }

    const toothElement = getToothNode(toothNumber);
    if (toothElement) {
        toothElement.classList.add('updating');
    }

    const headers = {
        'Content-Type': 'application/x-www-form-urlencoded'
    };

    if (window.csrfConfig?.header && typeof window.getCsrfToken === 'function') {
        headers[window.csrfConfig.header] = window.getCsrfToken();
    }

    fetch('<?= base_url('odontogram/reset-tooth') ?>', {
        method: 'POST',
        headers,
        body: `patient_id=${patientId}&tooth_number=${toothNumber}${examinationId ? `&examination_id=${examinationId}` : ''}`
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }

            if (data.success) {
                if (toothElement) {
                    toothElement.classList.remove('updating');
                    toothElement.classList.add('success');
                    setTimeout(() => {
                        toothElement.classList.remove('success');
                    }, 600);
                }

                const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
                const healthyEntry = {
                    tooth_number: toothNumber,
                    condition_type: 'healthy',
                    condition_description: '',
                    treatment_notes: '',
                    treatment_status: 'completed'
                };

                if (existingIndex !== -1) {
                    odontogramData[existingIndex] = healthyEntry;
                } else {
                    odontogramData.push(healthyEntry);
                }

                applyToothVisual(toothNumber);
                updateStats();
                showNotification('Tooth condition removed successfully!', 'success');
                closeToothModal();
            } else {
                throw new Error(data.message || 'Failed to reset tooth condition');
            }
        })
        .catch(error => {
            if (toothElement) {
                toothElement.classList.remove('updating');
            }
            showNotification('Error: ' + error.message, 'error');
        });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;

    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-white'
    };

    notification.className += ` ${colors[type] || colors.info}`;

    const container = document.createElement('div');
    container.className = 'flex items-center space-x-3';

    const icon = document.createElement('i');
    const iconClass = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
    icon.className = `fas fa-${iconClass}`;

    const msgSpan = document.createElement('span');
    msgSpan.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.className = 'ml-4 text-white hover:text-gray-200';
    closeBtn.onclick = function () {
        if (this.parentElement && this.parentElement.parentElement) {
            this.parentElement.parentElement.remove();
        }
    };

    const closeIcon = document.createElement('i');
    closeIcon.className = 'fas fa-times';
    closeBtn.appendChild(closeIcon);

    container.appendChild(icon);
    container.appendChild(msgSpan);
    container.appendChild(closeBtn);
    notification.appendChild(container);
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.remove('translate-x-full'), 10);

    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

function resetAllTeeth() {
    if (confirm('Are you sure you want to reset all teeth to healthy status? This action cannot be undone.')) {
        alert('Feature coming soon!');
    }
}

function markAllHealthy() {
    if (confirm('Are you sure you want to mark all teeth as healthy?')) {
        alert('Feature coming soon!');
    }
}

function printOdontogram() {
    window.open('<?= base_url('odontogram/' . $patient['id'] . '/print') ?>', '_blank');
}

function exportData() {
    const data = {
        patient: {
            id: patientId,
            name: '<?= $patient['first_name'] . ' ' . $patient['last_name'] ?>',
            patient_id: '<?= $patient['patient_id'] ?>'
        },
        odontogram: odontogramData,
        stats: <?= json_encode($stats) ?>
    };

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `odontogram_${patientId}_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>
<?php $chartConditionLabels=[];foreach(array_keys($condition_types) as $key){$chartConditionLabels[$key]=lang('UI.patientCondition'.str_replace(' ','',ucwords(str_replace('_',' ',$key))));} ?>
<script type="application/json" id="chart-condition-labels"><?= json_encode($chartConditionLabels,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="chart-presentation-labels"><?= json_encode(array_combine(['chartCondition', 'chartSelectCondition', 'chartDescription', 'chartDescriptionHint', 'chartSharedDescriptionHint', 'chartSharedNotesHint', 'chartTreatmentNotes', 'chartTreatmentStatus', 'chartPending', 'chartInProgress', 'chartCompleted', 'chartNeedsAttention', 'chartRemoveStatus', 'chartApply', 'chartTooth', 'chartFdi', 'chartPosition', 'chartNoRecordedConditions', 'chartNoConditionsHelp', 'chartPositionUpperRight', 'chartPositionUpperLeft', 'chartPositionLowerLeft', 'chartPositionLowerRight', 'chartActions', 'chartInteractive', 'chartInstruction', 'chartShowLegend', 'chartHideLegend', 'chartBatchSelect', 'chartBatchOn', 'chartSelected', 'chartExitBatch', 'chartRecordedState', 'chartTotalTeeth', 'chartHealthy', 'chartCavities', 'chartFillings', 'chartCrowns', 'chartExtracted', 'chartRecordActions', 'chartExport', 'chartBulkActions', 'chartResetAll', 'chartMarkHealthy', 'chartConditionRecords', 'chartRecordedConditionsHelp', 'chartApplySelected', 'chartSaving'], array_map(static fn($key)=>lang('Studio.'.$key), ['chartCondition', 'chartSelectCondition', 'chartDescription', 'chartDescriptionHint', 'chartSharedDescriptionHint', 'chartSharedNotesHint', 'chartTreatmentNotes', 'chartTreatmentStatus', 'chartPending', 'chartInProgress', 'chartCompleted', 'chartNeedsAttention', 'chartRemoveStatus', 'chartApply', 'chartTooth', 'chartFdi', 'chartPosition', 'chartNoRecordedConditions', 'chartNoConditionsHelp', 'chartPositionUpperRight', 'chartPositionUpperLeft', 'chartPositionLowerLeft', 'chartPositionLowerRight', 'chartActions', 'chartInteractive', 'chartInstruction', 'chartShowLegend', 'chartHideLegend', 'chartBatchSelect', 'chartBatchOn', 'chartSelected', 'chartExitBatch', 'chartRecordedState', 'chartTotalTeeth', 'chartHealthy', 'chartCavities', 'chartFillings', 'chartCrowns', 'chartExtracted', 'chartRecordActions', 'chartExport', 'chartBulkActions', 'chartResetAll', 'chartMarkHealthy', 'chartConditionRecords', 'chartRecordedConditionsHelp', 'chartApplySelected', 'chartSaving'])),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= base_url('assets/js/studio-chart.js') ?>?v=<?= esc(asset_version('assets/js/studio-chart.js')) ?>" defer></script>
<?= $this->endSection() ?>
