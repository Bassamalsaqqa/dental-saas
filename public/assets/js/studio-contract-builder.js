const contractConfig = JSON.parse(document.getElementById('contract-builder-config').textContent);
const contractBuildUi = contractConfig.value0;

const svgNS = 'http://www.w3.org/2000/svg';
const conditionTypes = contractConfig.value1;
const conditionColors = {
    healthy: '#10b981',
    cavity: '#f59e0b',
    filling: '#3b82f6',
    crown: '#8b5cf6',
    root_canal: '#ec4899',
    extracted: '#ef4444',
    implant: '#6366f1',
    bridge: '#14b8a6',
    partial_denture: '#f97316',
    full_denture: '#f59e0b',
    other: '#6b7280'
};
const conditionBadgeClasses = {
    healthy: 'bg-green-100 text-green-800',
    cavity: 'bg-yellow-100 text-yellow-800',
    filling: 'bg-blue-100 text-blue-800',
    crown: 'bg-purple-100 text-purple-800',
    root_canal: 'bg-pink-100 text-pink-800',
    extracted: 'bg-red-100 text-red-800',
    implant: 'bg-indigo-100 text-indigo-800',
    bridge: 'bg-teal-100 text-teal-800',
    partial_denture: 'bg-orange-100 text-orange-800',
    full_denture: 'bg-amber-100 text-amber-800',
    other: 'bg-gray-100 text-gray-800'
};
const statusBadgeClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-orange-100 text-orange-800',
    completed: 'bg-green-100 text-green-800',
    needs_attention: 'bg-red-100 text-red-800'
};
const statusLabels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    completed: 'Completed',
    needs_attention: 'Needs Attention'
};
const initialOdontogram = contractConfig.value2;
const initialItems = contractConfig.value3;

const toothGroups = new Map();
const toothOverlays = new Map();
const toothOutlines = new Map();
const toothHits = new Map();
let labelsVisible = false;
let activeTooth = null;
let batchModeEnabled = false;
const batchSelectedTeeth = new Set();
let odontogramData = [];
let items = [];
let tooltipEl = null;
let manualTotalOverride = false;
let totalOverrideInitialized = false;

function getFdiNumber(universalNumber) {
    const fdiMap = {
        1: '11', 2: '12', 3: '13', 4: '14', 5: '15', 6: '16', 7: '17', 8: '18',
        9: '21', 10: '22', 11: '23', 12: '24', 13: '25', 14: '26', 15: '27', 16: '28',
        17: '31', 18: '32', 19: '33', 20: '34', 21: '35', 22: '36', 23: '37', 24: '38',
        25: '41', 26: '42', 27: '43', 28: '44', 29: '45', 30: '46', 31: '47', 32: '48'
    };
    return fdiMap[universalNumber] || String(universalNumber);
}

function parseToothNumber(element) {
    if (!element) return null;
    const dataTooth = element.dataset?.tooth;
    if (dataTooth) return parseInt(dataTooth, 10);
    const id = element.getAttribute('id') || '';
    const match = id.match(/^tooth-(\d+)$/);
    return match ? parseInt(match[1], 10) : null;
}

function getLargestShape(group) {
    const shapes = Array.from(group.querySelectorAll('path, polygon, rect, circle, ellipse'))
        .filter(el => !el.classList.contains('tooth-hit') && !el.classList.contains('tooth-overlay') && !el.classList.contains('tooth-outline'));

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
        return !!(fill && fill !== 'none' && fill !== 'transparent');
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
        if (!area || area <= 0) return;

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

        overlay = base.cloneNode(true);
        overlay.removeAttribute('id');
        overlay.setAttribute('class', 'tooth-overlay');
        overlay.setAttribute('fill', 'transparent');
        overlay.setAttribute('opacity', '0');
        overlay.style.pointerEvents = 'none';
        group.appendChild(overlay);

        outline = base.cloneNode(true);
        outline.removeAttribute('id');
        outline.setAttribute('class', 'tooth-outline');
        outline.setAttribute('fill', 'none');
        outline.setAttribute('stroke', '#3b82f6');
        outline.setAttribute('stroke-width', '1.5');
        outline.setAttribute('opacity', '0');
        outline.style.pointerEvents = 'none';
        group.appendChild(outline);

        hit = base.cloneNode(true);
        hit.removeAttribute('id');
        hit.setAttribute('class', 'tooth-hit');
        hit.setAttribute('fill', 'transparent');
        hit.setAttribute('opacity', '0');
        hit.style.pointerEvents = 'fill';
        group.appendChild(hit);
    }

    if (hit) {
        hit.style.cursor = 'pointer';
        hit.setAttribute('tabindex', '0');
        hit.setAttribute('role', 'button');
        hit.setAttribute('aria-label', `Tooth ${toothNumber} (FDI ${getFdiNumber(toothNumber)})`);
    }

    return { overlay, outline, hit };
}

function cacheToothGroups(svg) {
    toothGroups.clear();
    toothOverlays.clear();
    toothOutlines.clear();
    toothHits.clear();

    const groups = Array.from(svg.querySelectorAll('[id^="tooth-"]'))
        .filter(element => (element.tagName || '').toLowerCase().endsWith('g'));

    groups.forEach(group => {
        const toothNumber = parseToothNumber(group);
        if (!toothNumber) return;

        group.classList.add('tooth-group');
        group.dataset.tooth = toothNumber;

        const { overlay, outline, hit } = ensureToothControls(group, toothNumber);
        if (overlay) toothOverlays.set(toothNumber, overlay);
        if (outline) toothOutlines.set(toothNumber, outline);
        if (hit) {
            toothHits.set(toothNumber, hit);
            hit.addEventListener('mouseenter', (event) => {
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
            hit.addEventListener('focus', (event) => {
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
            hit.addEventListener('keydown', (event) => {
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

function buildToothLabels(svg) {
    svg.querySelectorAll('.tooth-label-layer').forEach(el => el.remove());
    const labelLayer = document.createElementNS(svgNS, 'g');
    labelLayer.setAttribute('class', 'tooth-label-layer');
    labelLayer.setAttribute('pointer-events', 'none');

    toothGroups.forEach((group, toothNumber) => {
        let bbox;
        try {
            bbox = group.getBBox();
        } catch (e) {
            return;
        }
        const centerX = bbox.x + bbox.width / 2;
        const centerY = bbox.y + (toothNumber <= 16 ? bbox.height + 10 : -6);

        const universalLabel = document.createElementNS(svgNS, 'text');
        universalLabel.setAttribute('class', 'universal-number');
        universalLabel.setAttribute('x', centerX.toString());
        universalLabel.setAttribute('y', centerY.toString());
        universalLabel.textContent = String(toothNumber);
        labelLayer.appendChild(universalLabel);

        const fdiLabel = document.createElementNS(svgNS, 'text');
        fdiLabel.setAttribute('class', 'fdi-number');
        fdiLabel.setAttribute('x', centerX.toString());
        fdiLabel.setAttribute('y', centerY.toString());
        fdiLabel.textContent = getFdiNumber(toothNumber);
        fdiLabel.style.display = 'none';
        labelLayer.appendChild(fdiLabel);
    });

    svg.appendChild(labelLayer);
}

function setupSvgOdontogram() {
    const wrapper = document.getElementById('odontogramSvgWrapper');
    if (!wrapper) return;
    const svg = wrapper.querySelector('svg');
    if (!svg) return;

    svg.id = svg.id || 'odontogramSvg';
    svg.removeAttribute('width');
    svg.removeAttribute('height');
    svg.style.width = '100%';
    svg.style.height = 'auto';

    cacheToothGroups(svg);
    if (labelsVisible) {
        buildToothLabels(svg);
    }
}

function setOutlineVisible(toothNumber, visible) {
    const outline = toothOutlines.get(toothNumber);
    if (outline) {
        outline.setAttribute('opacity', visible ? '0.9' : '0');
    }
}

function isToothBatchSelected(toothNumber) {
    return batchSelectedTeeth.has(parseInt(toothNumber, 10));
}

function syncBatchToothVisual(toothNumber) {
    const tooth = parseInt(toothNumber, 10);
    const group = toothGroups.get(tooth);
    if (!group) return;

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
        toggleText.textContent = batchModeEnabled ? contractBuildUi.batchOn : contractBuildUi.batchSelect;
    }

    if (topControls) {
        topControls.classList.toggle('hidden', !batchModeEnabled);
        topControls.classList.toggle('flex', batchModeEnabled);
    }

    if (countEl) {
        countEl.textContent = contractBuildUi.selected.replace('{count}',String(count));
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
    if (shouldEnable === batchModeEnabled) return;

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
    if (!batchModeEnabled || !tooth) return;

    if (batchSelectedTeeth.has(tooth)) {
        batchSelectedTeeth.delete(tooth);
    } else {
        batchSelectedTeeth.add(tooth);
    }
    syncBatchToothVisual(tooth);
    updateBatchUi();
}

function getConditionForTooth(toothNumber) {
    const record = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
    return record ? record.condition_type : 'healthy';
}

function applyToothVisual(toothNumber) {
    const overlay = toothOverlays.get(parseInt(toothNumber, 10));
    if (!overlay) return;

    const condition = getConditionForTooth(toothNumber);
    if (!condition || condition === 'healthy') {
        overlay.setAttribute('opacity', '0');
        overlay.setAttribute('fill', 'transparent');
    } else {
        overlay.setAttribute('fill', conditionColors[condition] || '#6b7280');
        overlay.setAttribute('opacity', '0.28');
    }
}

function setElementDisplay(el, show) {
    el.style.display = show ? 'inline' : 'none';
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
    moveTooltip(event);
    tooltipEl.style.opacity = '1';
}

function moveTooltip(event) {
    if (!tooltipEl || tooltipEl.style.opacity !== '1') {
        return;
    }
    const x = event?.clientX ?? 0;
    const y = event?.clientY ?? 0;
    tooltipEl.style.left = `${x + 12}px`;
    tooltipEl.style.top = `${y + 12}px`;
}

function hideTooltip() {
    if (!tooltipEl) {
        return;
    }
    tooltipEl.style.opacity = '0';
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
    if (!activeTooth) return;
    const tooth = parseInt(activeTooth, 10);
    const current = toothGroups.get(tooth);
    current?.classList.remove('is-selected');
    current?.classList.remove('is-hover');
    if (!isToothBatchSelected(tooth)) {
        setOutlineVisible(tooth, false);
    }
    hideTooltip();
    activeTooth = null;
}

function openToothModal(toothNumber) {
    if (batchModeEnabled) return;

    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    if (!modal || !modalContent) return;

    const modalNumber = document.getElementById('modalToothNumber');
    const toothInput = document.getElementById('toothNumber');
    modalNumber.textContent = toothNumber;
    toothInput.value = toothNumber;

    const existing = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
    const conditionTypeEl = document.getElementById('condition_type');
    const descriptionEl = document.getElementById('condition_description');
    const notesEl = document.getElementById('treatment_notes');
    const statusEl = document.getElementById('treatment_status');

    if (existing) {
        conditionTypeEl.value = existing.condition_type || 'healthy';
        descriptionEl.value = existing.condition_description || '';
        notesEl.value = existing.treatment_notes || '';
        statusEl.value = existing.treatment_status || 'pending';
    } else {
        document.getElementById('toothForm').reset();
        conditionTypeEl.value = 'healthy';
        statusEl.value = 'pending';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);

    setSelectedTooth(toothNumber);
}

function closeToothModal() {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    if (!modal || !modalContent) return;

    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);

    clearSelectedTooth();
}

function openBatchModal() {
    if (!batchModeEnabled) return;
    const selected = batchSelectedTeeth.size;
    if (selected === 0) return;

    const modal = document.getElementById('batchModal');
    const content = document.getElementById('batchModalContent');
    const countEl = document.getElementById('batchModalCount');
    if (!modal || !content) return;

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
    if (!modal || !content) return;

    content.style.transform = 'scale(0.95)';
    content.style.opacity = '0';

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 250);
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
    });
}

function saveTooth() {
    const toothNumber = parseInt(document.getElementById('toothNumber').value, 10);
    if (!toothNumber) return;

    const condition = document.getElementById('condition_type').value || 'healthy';
    const description = document.getElementById('condition_description').value || '';
    const notes = document.getElementById('treatment_notes').value || '';
    const status = document.getElementById('treatment_status').value || 'pending';

    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === toothNumber);
    const updated = {
        tooth_number: toothNumber,
        condition_type: condition,
        condition_description: description,
        treatment_notes: notes,
        treatment_status: status
    };

    if (existingIndex !== -1) {
        odontogramData[existingIndex] = updated;
    } else {
        odontogramData.push(updated);
    }

    applyToothVisual(toothNumber);
    updateConditionsTable();
    syncItemsFromConditions();
    closeToothModal();
}

function resetTooth() {
    const toothNumber = parseInt(document.getElementById('toothNumber').value, 10);
    if (!toothNumber) return;

    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === toothNumber);
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
    updateConditionsTable();
    syncItemsFromConditions();
    closeToothModal();
}

function removeToothCondition() {
    resetTooth();
}

function saveBatchTooth(toothNumber, values) {
    const tooth = parseInt(toothNumber, 10);
    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === tooth);
    const updated = {
        tooth_number: tooth,
        condition_type: values.condition_type || 'healthy',
        condition_description: values.condition_description || '',
        treatment_notes: values.treatment_notes || '',
        treatment_status: values.treatment_status || 'pending'
    };

    if (existingIndex !== -1) {
        odontogramData[existingIndex] = updated;
    } else {
        odontogramData.push(updated);
    }

    applyToothVisual(tooth);
}

function batchResetSelected() {
    const selectedTeeth = Array.from(batchSelectedTeeth);
    if (!selectedTeeth.length) return;

    selectedTeeth.forEach(toothNumber => {
        saveBatchTooth(toothNumber, {
            condition_type: 'healthy',
            condition_description: '',
            treatment_notes: '',
            treatment_status: 'completed'
        });
    });

    updateConditionsTable();
    syncItemsFromConditions();
    clearBatchSelection();
    closeBatchModal();
}

function getToothPosition(toothNumber) {
    if (toothNumber >= 1 && toothNumber <= 8) return 'Upper Right';
    if (toothNumber >= 9 && toothNumber <= 16) return 'Upper Left';
    if (toothNumber >= 17 && toothNumber <= 24) return 'Lower Left';
    if (toothNumber >= 25 && toothNumber <= 32) return 'Lower Right';
    return '';
}

function updateConditionsTable() {
    const tableBody = document.getElementById('teethTableBody');
    const summaryEl = document.getElementById('conditionsSummary');
    if (!tableBody) return;

    const conditions = [];
    for (let i = 1; i <= 32; i += 1) {
        const record = odontogramData.find(t => parseInt(t.tooth_number, 10) === i);
        const condition = record ? record.condition_type : 'healthy';
        if (condition !== 'healthy') {
            conditions.push({
                tooth_number: i,
                record
            });
        }
    }

    tableBody.replaceChildren();

    if (summaryEl) {
        summaryEl.textContent = contractBuildUi.conditions.replace('{count}',String(conditions.length));
    }

    if (conditions.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.setAttribute('colspan', '8');
        cell.className = 'py-12 text-center text-gray-500';
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col items-center space-y-3';
        const icon = document.createElement('i');
        icon.className = 'fas fa-smile text-4xl text-green-400';
        const title = document.createElement('div');
        title.className = 'text-lg font-medium';
        title.textContent = contractBuildUi.healthy;
        const subtitle = document.createElement('div');
        subtitle.className = 'text-sm';
        subtitle.textContent = contractBuildUi.noConditions;
        wrapper.appendChild(icon);
        wrapper.appendChild(title);
        wrapper.appendChild(subtitle);
        cell.appendChild(wrapper);
        row.appendChild(cell);
        tableBody.appendChild(row);
        return;
    }

    conditions.forEach(item => {
        const toothNumber = item.tooth_number;
        const record = item.record || {};
        const condition = record.condition_type || 'healthy';
        const description = record.condition_description || '';
        const treatment = record.treatment_notes || '';
        const status = record.treatment_status || 'completed';

        const row = document.createElement('tr');
        row.className = 'border-b border-gray-100 hover:bg-gray-50/50 transition-colors duration-200';
        row.dataset.tooth = toothNumber;

        const toothCell = document.createElement('td');
        toothCell.className = 'py-2 px-3';
        const toothUniversal = document.createElement('span');
        toothUniversal.className = 'universal-number font-semibold text-blue-600';
        toothUniversal.textContent = String(toothNumber);
        const toothFdi = document.createElement('span');
        toothFdi.className = 'fdi-number hidden font-semibold text-blue-600';
        toothFdi.textContent = getFdiNumber(toothNumber);
        toothCell.appendChild(toothUniversal);
        toothCell.appendChild(toothFdi);

        const fdiCell = document.createElement('td');
        fdiCell.className = 'py-2 px-3';
        const fdiUniversal = document.createElement('span');
        fdiUniversal.className = 'universal-number text-gray-600';
        fdiUniversal.textContent = getFdiNumber(toothNumber);
        const fdiAlt = document.createElement('span');
        fdiAlt.className = 'fdi-number hidden text-gray-600';
        fdiAlt.textContent = String(toothNumber);
        fdiCell.appendChild(fdiUniversal);
        fdiCell.appendChild(fdiAlt);

        const positionCell = document.createElement('td');
        positionCell.className = 'py-3 px-4 text-gray-600';
        positionCell.textContent = getToothPosition(toothNumber);

        const conditionCell = document.createElement('td');
        conditionCell.className = 'py-2 px-3';
        const conditionBadge = document.createElement('span');
        conditionBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${conditionBadgeClasses[condition] || conditionBadgeClasses.other}`;
        conditionBadge.textContent = (conditionTypes?.[condition] || 'Healthy');
        conditionCell.appendChild(conditionBadge);

        const descriptionCell = document.createElement('td');
        descriptionCell.className = 'py-3 px-4 text-gray-600 max-w-xs truncate';
        descriptionCell.title = description;
        descriptionCell.textContent = description || '-';

        const treatmentCell = document.createElement('td');
        treatmentCell.className = 'py-3 px-4 text-gray-600 max-w-xs truncate';
        treatmentCell.title = treatment;
        treatmentCell.textContent = treatment || '-';

        const statusCell = document.createElement('td');
        statusCell.className = 'py-2 px-3';
        const statusBadge = document.createElement('span');
        statusBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusBadgeClasses[status] || statusBadgeClasses.completed}`;
        statusBadge.textContent = statusLabels[status] || 'Completed';
        statusCell.appendChild(statusBadge);

        const actionCell = document.createElement('td');
        actionCell.className = 'py-2 px-3 text-center';
        const editLink = document.createElement('a');
        editLink.href = '#';
        editLink.className = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors';
        editLink.title = 'Edit tooth ' + toothNumber;
        editLink.textContent = contractBuildUi.edit;
        editLink.addEventListener('click', (e) => { e.preventDefault(); openToothModal(toothNumber); });
        actionCell.appendChild(editLink);

        row.appendChild(toothCell);
        row.appendChild(fdiCell);
        row.appendChild(positionCell);
        row.appendChild(conditionCell);
        row.appendChild(descriptionCell);
        row.appendChild(treatmentCell);
        row.appendChild(statusCell);
        row.appendChild(actionCell);

        tableBody.appendChild(row);
    });

}

function normalizeOdontogram() {
    odontogramData = [];
    initialOdontogram.forEach(row => {
        if (!row || row.tooth_number == null) return;
        odontogramData.push({
            tooth_number: parseInt(row.tooth_number, 10),
            condition_type: row.condition_type || 'healthy',
            condition_description: row.condition_description || '',
            treatment_notes: row.treatment_notes || '',
            treatment_status: row.treatment_status || 'pending'
        });
    });
}

function normalizeItems() {
    items = [];
    initialItems.forEach(item => {
        items.push({
            code: item.procedure_code || '',
            tooth: item.tooth_no || '',
            description: item.description || '',
            qty: Number(item.qty || 1),
            price: Number(item.price || 0),
            source: item.tooth_no ? 'auto' : 'manual'
        });
    });
}

function syncItemsFromConditions() {
    const conditionTeeth = odontogramData.filter(row => row.condition_type && row.condition_type !== 'healthy');
    const existingAuto = new Map();
    items.forEach(item => {
        if (item.source === 'auto' && item.tooth) {
            existingAuto.set(String(item.tooth), item);
        }
    });

    conditionTeeth.forEach(row => {
        const key = String(row.tooth_number);
        if (!existingAuto.has(key)) {
            items.push({
                code: '',
                tooth: key,
                description: `${conditionTypes?.[row.condition_type] || 'Condition'} - Tooth ${key}`,
                qty: 1,
                price: 0,
                source: 'auto'
            });
        } else {
            const item = existingAuto.get(key);
            if (!item.description) {
                item.description = `${conditionTypes?.[row.condition_type] || 'Condition'} - Tooth ${key}`;
            }
        }
    });

    items = items.filter(item => {
        if (item.source !== 'auto') return true;
        if (!item.tooth) return false;
        const toothRow = conditionTeeth.find(row => String(row.tooth_number) === String(item.tooth));
        return !!toothRow;
    });

    renderItems();
}

function renderItems() {
    const itemsBody = document.getElementById('itemsBody');
    if (!itemsBody) return;

    itemsBody.replaceChildren();
    if (items.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
        cell.className = 'px-4 py-6 text-center text-gray-500';
        cell.textContent = contractBuildUi.noItems;
        row.appendChild(cell);
        itemsBody.appendChild(row);
        updateTotal();
        return;
    }

    items.forEach((item, index) => {
        const row = document.createElement('tr');

        row.appendChild(buildItemCell('code', item.code, index));
        row.appendChild(buildItemCell('tooth', item.tooth, index));
        row.appendChild(buildItemCell('description', item.description, index));
        row.appendChild(buildItemCell('qty', item.qty, index, 'number'));
        row.appendChild(buildItemCell('price', item.price, index, 'number'));

        const actionCell = document.createElement('td');
        actionCell.className = 'px-2 py-1.5 text-right';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-[11px] text-rose-500 hover:text-rose-700 font-medium';
        removeBtn.textContent = contractBuildUi.remove;
        removeBtn.addEventListener('click', () => {
            items.splice(index, 1);
            renderItems();
        });
        actionCell.appendChild(removeBtn);
        row.appendChild(actionCell);

        itemsBody.appendChild(row);
    });

    updateTotal();
}

function buildItemCell(field, value, index, type = 'text') {
    const cell = document.createElement('td');
    cell.className = 'px-2 py-1.5';
    const input = document.createElement('input');
    input.type = type;
    input.name = `items[${index}][${field}]`;
    input.value = value ?? '';
    input.className = 'w-full px-2 py-1.5 border border-gray-200 rounded text-xs bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-400 focus:border-blue-400';

    if (field === 'qty' || field === 'price') {
        input.step = '0.01'; input.min = '0';
    }
    input.addEventListener('input', () => {
        items[index][field] = (field === 'qty' || field === 'price') ? Number(input.value || 0) : input.value;
        updateTotal();
    });
    cell.appendChild(input);
    return cell;
}

function updateTotal() {
    const total = items.reduce((sum, item) => {
        const qty = Number(item.qty || 1);
        const price = Number(item.price || 0);
        return sum + (qty > 0 ? qty : 1) * price;
    }, 0);
    const totalInput = document.getElementById('contractTotalInput');
    if (!totalInput) {
        return;
    }

    if (!totalOverrideInitialized) {
        const existingValue = Number(totalInput.value);
        if (Number.isFinite(existingValue) && Math.abs(existingValue - total) > 0.009) {
            manualTotalOverride = true;
        }
        totalOverrideInitialized = true;
    }

    if (!manualTotalOverride) {
        totalInput.value = total.toFixed(2);
    }
}

function captureOdontogramSnapshot() {
    document.getElementById('odontogramSvg').value = '';
    document.getElementById('odontogramJson').value = JSON.stringify(odontogramData);
}

function initializeOdontogram() {
    normalizeOdontogram();
    normalizeItems();
    syncItemsFromConditions();

    toothGroups.forEach((_, toothNumber) => {
        applyToothVisual(toothNumber);
    });
    updateConditionsTable();
}

function attachFormHandlers() {
    document.getElementById('toothForm')?.addEventListener('submit', (event) => {
        event.preventDefault();
        saveTooth();
    });

    document.getElementById('addItemBtn')?.addEventListener('click', () => {
        items.push({
            code: '',
            tooth: '',
            description: '',
            qty: 1,
            price: 0,
            source: 'manual'
        });
        renderItems();
    });

    document.getElementById('contractForm')?.addEventListener('submit', () => {
        captureOdontogramSnapshot();
    });

    document.getElementById('batchForm')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const selectedTeeth = Array.from(batchSelectedTeeth);
        if (!selectedTeeth.length) return;

        const form = document.getElementById('batchForm');
        const values = {
            condition_type: form.condition_type.value,
            condition_description: form.condition_description.value,
            treatment_notes: form.treatment_notes.value,
            treatment_status: form.treatment_status.value
        };
        if (!values.condition_type) return;

        selectedTeeth.forEach(toothNumber => {
            saveBatchTooth(toothNumber, values);
        });

        updateConditionsTable();
        syncItemsFromConditions();
        clearBatchSelection();
        closeBatchModal();
    });

    const totalInput = document.getElementById('contractTotalInput');
    totalInput?.addEventListener('input', () => {
        manualTotalOverride = true;
    });

    const termsToggleBtn = document.getElementById('contractTermsToggleBtn');
    const termsContent = document.getElementById('contractTermsContent');
    const termsIcon = document.getElementById('contractTermsToggleIcon');
    const termsText = document.getElementById('contractTermsToggleText');
    const legendToggleBtn = document.getElementById('legendToggleBtn');
    const legendContent = document.getElementById('legendContent');
    const legendToggleIcon = document.getElementById('legendToggleIcon');
    const legendToggleText = document.getElementById('legendToggleText');

    termsToggleBtn?.addEventListener('click', () => {
        if (!termsContent) return;
        const willShow = termsContent.classList.contains('hidden');
        termsContent.classList.toggle('hidden', !willShow);
        if (termsIcon) {
            termsIcon.classList.toggle('fa-chevron-down', !willShow);
            termsIcon.classList.toggle('fa-chevron-up', willShow);
        }
        if (termsText) {
            termsText.textContent = willShow ? contractBuildUi.hide : contractBuildUi.show;
        }
    });

    legendToggleBtn?.addEventListener('click', () => {
        if (!legendContent) return;
        const willShow = legendContent.classList.contains('hidden');
        legendContent.classList.toggle('hidden', !willShow);
        if (legendToggleIcon) {
            legendToggleIcon.classList.toggle('fa-chevron-down', !willShow);
            legendToggleIcon.classList.toggle('fa-chevron-up', willShow);
        }
        if (legendToggleText) {
            legendToggleText.textContent = willShow ? contractBuildUi.hideLegend : contractBuildUi.showLegend;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.body?.classList.add('contract-full-width');
    document.querySelector('main')?.classList.add('contract-full-main');
    setupSvgOdontogram();
    initializeOdontogram();
    attachModalBehavior();
    attachFormHandlers();
    updateBatchUi();
});

// Explicit first-party controls; signed evidence and builder algorithms are unchanged.
const contractActions={'batch-toggle':()=>toggleBatchMode(),'batch-open':()=>openBatchModal(),'batch-clear':()=>clearBatchSelection(),'batch-exit':()=>toggleBatchMode(false),'tooth-close':()=>closeToothModal(),'tooth-remove':()=>removeToothCondition(),'batch-close':()=>closeBatchModal(),'batch-reset':()=>batchResetSelected()};
document.addEventListener('click',event=>{const button=event.target.closest('[data-contract-action]');if(!button||button.disabled)return;const action=contractActions[button.dataset.contractAction];if(action){event.preventDefault();action();}});
