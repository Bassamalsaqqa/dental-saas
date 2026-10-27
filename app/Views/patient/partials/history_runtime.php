<script>
    const patientShowI18n = {
        noHistoryRecords: '<?= esc(lang('UI.patientNoHistoryRecords'), 'js') ?>',
        timelineOn: '<?= esc(lang('UI.patientTimelineOn'), 'js') ?>',
        saving: '<?= esc(lang('UI.patientSaving'), 'js') ?>',
        toothUpdatedSuccess: '<?= esc(lang('UI.patientToothUpdatedSuccess'), 'js') ?>',
        toothUpdateError: '<?= esc(lang('UI.patientToothUpdateError'), 'js') ?>',
        toothUpdateGenericError: '<?= esc(lang('UI.patientToothUpdateGenericError'), 'js') ?>',
        unknown: '<?= esc(lang('UI.patientUnknown'), 'js') ?>'
    };
    const patientMiniOdontogramData = <?= json_encode(array_values($odontogram ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function initPatientMiniOdontogramPreview() {
        const wrapper = document.querySelector('.patient-odontogram-mini-svg');
        if (!wrapper) {
            return;
        }

        const svg = wrapper.querySelector('svg');
        if (!svg) {
            return;
        }

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

        const parseToothNumber = (element) => {
            const rawId = element?.id || '';
            const match = rawId.match(/tooth-(\d+)/);
            return match ? parseInt(match[1], 10) : null;
        };

        const getLargestShape = (group) => {
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
        };

        const ensurePreviewOverlay = (group) => {
            group.querySelectorAll('.tooth-overlay, .tooth-outline, .tooth-hit').forEach(el => el.remove());
            const base = getLargestShape(group);
            if (!base) {
                return null;
            }

            const overlay = base.cloneNode(true);
            overlay.removeAttribute('id');
            overlay.setAttribute('class', 'tooth-overlay');
            overlay.setAttribute('fill', 'transparent');
            overlay.setAttribute('opacity', '0');
            overlay.style.pointerEvents = 'none';
            group.appendChild(overlay);
            return overlay;
        };

        const overlays = new Map();
        Array.from(svg.querySelectorAll('[id^="tooth-"]'))
            .filter(element => (element.tagName || '').toLowerCase().endsWith('g'))
            .forEach(group => {
                const toothNumber = parseToothNumber(group);
                if (!toothNumber) {
                    return;
                }
                const overlay = ensurePreviewOverlay(group);
                if (overlay) {
                    overlays.set(toothNumber, overlay);
                }
            });

        patientMiniOdontogramData.forEach(record => {
            const toothNumber = parseInt(record.tooth_number, 10);
            const overlay = overlays.get(toothNumber);
            if (!overlay) {
                return;
            }

            const condition = record.condition_type || 'healthy';
            if (!condition || condition === 'healthy') {
                overlay.setAttribute('fill', 'transparent');
                overlay.setAttribute('opacity', '0');
                return;
            }

            overlay.setAttribute('fill', conditionColors[condition] || '#6b7280');
            overlay.setAttribute('opacity', '0.28');
        });
    }

    // Global variables for filtering
    const patientProfileUrl = new URL(window.location.href);
    const requestedPatientTab = patientProfileUrl.searchParams.get('tab');
    let currentTab = 'details';
    let allTimelineData = [];
    let filteredTimelineData = [];

    // Initialize timeline data
    document.addEventListener('DOMContentLoaded', function() {
        initPatientMiniOdontogramPreview();
        // Store all timeline data for filtering
        allTimelineData = [
            <?php foreach ($timeline as $item): ?>
            {
                type: <?= json_encode((string) ($item['type'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                date: <?= json_encode((string) ($item['date'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                title: <?= json_encode((string) ($item['title'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                description: <?= json_encode((string) ($item['description'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                status: <?= json_encode((string) ($item['status'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                status_label: <?= json_encode((string) ($item['status_label'] ?? ucfirst((string) ($item['status'] ?? ''))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                id: <?= json_encode((string) ($item['id'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                doctor: <?= json_encode((string) ($item['doctor'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                icon: <?= json_encode((string) ($item['icon'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                color: <?= json_encode((string) ($item['color'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                url: <?= json_encode((string) ($item['url'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                meta: <?= json_encode($item['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
            },
            <?php endforeach; ?>
        ];
        filteredTimelineData = [...allTimelineData];
        
        // Add event listeners
        document.getElementById('historySearch').addEventListener('input', filterHistory);
        document.getElementById('historySearch').addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); applyHistoryFilters(); } });
        document.getElementById('startDate').addEventListener('change', filterHistory);
        document.getElementById('endDate').addEventListener('change', filterHistory);
        const patientTabs = Array.from(document.querySelectorAll('.tab-button'));
        const patientPanels = Array.from(document.querySelectorAll('.tab-content'));

        patientPanels.forEach((panel) => {
            if (!panel.hasAttribute('role')) {
                panel.setAttribute('role', 'tabpanel');
            }
            const tabName = panel.id.replace('-content', '');
            panel.setAttribute('aria-labelledby', `${tabName}-tab`);
        });

        patientTabs.forEach((tab, index) => {
            tab.addEventListener('keydown', (event) => {
                if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                    return;
                }

                event.preventDefault();
                let nextIndex = index;
                if (event.key === 'ArrowRight') {
                    nextIndex = (index + 1) % patientTabs.length;
                } else if (event.key === 'ArrowLeft') {
                    nextIndex = (index - 1 + patientTabs.length) % patientTabs.length;
                } else if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = patientTabs.length - 1;
                }

                patientTabs[nextIndex]?.focus();
            });
        });

        const validTabs = patientTabs
            .map(tab => tab.id.replace('-tab', ''))
            .filter(Boolean);

        let initialTab = 'details';
        if (requestedPatientTab && validTabs.includes(requestedPatientTab)) {
            initialTab = requestedPatientTab;
        }

        const shouldOpenAttachments = <?= json_encode((bool) session('attachments_flash')) ?>;
        if (shouldOpenAttachments && validTabs.includes('attachments')) {
            initialTab = 'attachments';
        }

        const dropzone = document.getElementById('attachmentDropzone');
        const fileInput = document.getElementById('attachmentInput');
        const fileList = document.getElementById('attachmentFileList');

        if (dropzone && fileInput && fileList) {
            const renderFiles = (files) => {
                fileList.replaceChildren();
                if (!files || files.length === 0) {
                    return;
                }
                Array.from(files).forEach(file => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between rounded-md bg-white px-3 py-2 border border-gray-200';
                    const name = document.createElement('div');
                    name.className = 'text-sm text-gray-700';
                    name.textContent = file.name;
                    const size = document.createElement('div');
                    size.className = 'text-xs text-gray-500';
                    size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
                    row.appendChild(name);
                    row.appendChild(size);
                    fileList.appendChild(row);
                });
            };

            const setHover = (active) => {
                dropzone.classList.toggle('ring-2', active);
                dropzone.classList.toggle('ring-blue-400', active);
                dropzone.classList.toggle('bg-blue-50', active);
            };

            ['dragenter', 'dragover'].forEach(evt => {
                dropzone.addEventListener(evt, (event) => {
                    event.preventDefault();
                    setHover(true);
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, (event) => {
                    event.preventDefault();
                    setHover(false);
                });
            });

            dropzone.addEventListener('drop', (event) => {
                const files = event.dataTransfer?.files;
                if (!files || files.length === 0) {
                    return;
                }
                const dataTransfer = new DataTransfer();
                Array.from(files).forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
                renderFiles(fileInput.files);
            });

            dropzone.addEventListener('click', () => {
                fileInput.click();
            });

            dropzone.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    fileInput.click();
                }
            });

            fileInput.addEventListener('change', () => {
                renderFiles(fileInput.files);
            });
        }

        const shouldOpenContracts = <?= json_encode((bool) session('contracts_flash')) ?>;
        if (shouldOpenContracts && validTabs.includes('contracts')) {
            initialTab = 'contracts';
        }

        showTab(initialTab);
    });

    function showTab(tabName, event = null) {
        if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
        }
        const currentX = window.scrollX;
        const currentY = window.scrollY;
        currentTab = tabName;
        
        // Hide all tab contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => {
            content.classList.add('hidden');
            content.hidden = true;
        });
        
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab-button');
        tabs.forEach(tab => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
            tab.setAttribute('tabindex', '-1');
        });
        
        // Show selected tab content
        const selectedContent = document.getElementById(tabName + '-content');
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
            selectedContent.hidden = false;
        }
        
        // Add active class to selected tab
        const selectedTab = document.getElementById(tabName + '-tab');
        if (selectedTab) {
            selectedTab.classList.add('active');
            selectedTab.setAttribute('aria-selected', 'true');
            selectedTab.setAttribute('tabindex', '0');
        }

        const nextUrl = new URL(window.location.href);
        if (nextUrl.searchParams.get('tab') !== tabName) { nextUrl.searchParams.delete('history_before'); }
        if (tabName === 'details') {
            nextUrl.searchParams.delete('tab');
        } else {
            nextUrl.searchParams.set('tab', tabName);
        }
        window.history.replaceState({}, '', nextUrl.toString());

        const historyControls = document.getElementById('historyControls');
        if (historyControls) {
            historyControls.classList.toggle('hidden', tabName === 'details');
        }
        
        // Bounded tabs were filtered across their finite candidate batch on the server.
        if (isBoundedHistoryTab(tabName)) {
            return false;
        }
        if (tabName === 'timeline') {
            filterTimeline();
        } else if (tabName !== 'details') {
            filterTable(tabName);
        }

        window.requestAnimationFrame(() => {
            window.scrollTo(currentX, currentY);
        });

        return false;
    }

    function isBoundedHistoryTab(tab) {
        return ['timeline', 'examinations', 'appointments', 'treatments'].includes(tab);
    }

    function applyHistoryFilters() {
        if (!isBoundedHistoryTab(currentTab)) { filterHistory(); return; }
        const url = new URL(window.location.href);
        url.searchParams.set('tab', currentTab);
        url.searchParams.delete('history_before');
        const filters = {history_q: document.getElementById('historySearch').value, history_start: document.getElementById('startDate').value, history_end: document.getElementById('endDate').value};
        for (const [key, raw] of Object.entries(filters)) {
            const value = key === 'history_q' ? raw.trim() : (window.parseClinicDateToIso ? window.parseClinicDateToIso(raw) : raw);
            if (value) { url.searchParams.set(key, value); } else { url.searchParams.delete(key); }
        }
        window.location.assign(url.toString());
    }

    function filterHistory() {
        if (isBoundedHistoryTab(currentTab)) { return; }
        if (currentTab === 'timeline') {
            filterTimeline();
        } else if (currentTab !== 'details') {
            filterTable(currentTab);
        }
    }

    function filterTimeline() {
        const searchTerm = document.getElementById('historySearch').value.toLowerCase();
        const startDateRaw = document.getElementById('startDate').value;
        const endDateRaw = document.getElementById('endDate').value;
        const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startDateRaw) : startDateRaw;
        const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endDateRaw) : endDateRaw;
        
        filteredTimelineData = allTimelineData.filter(item => {
            const metaText = Array.isArray(item.meta)
                ? item.meta.map(metaItem => metaItem && metaItem.text ? String(metaItem.text).toLowerCase() : '').join(' ')
                : '';
            const matchesSearch = !searchTerm || 
                item.title.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm) ||
                item.status.toLowerCase().includes(searchTerm) ||
                (item.doctor && item.doctor.toLowerCase().includes(searchTerm)) ||
                metaText.includes(searchTerm);
                
            const itemDate = new Date(item.date);
            const matchesDateRange = (!startDate || itemDate >= new Date(startDate)) &&
                                    (!endDate || itemDate <= new Date(endDate));
            
            return matchesSearch && matchesDateRange;
        });
        
        renderTimeline();
    }

    function renderTimeline() {
        const timelineContainer = document.querySelector('#timeline-content ul');
        
        // Clear existing content safely
        timelineContainer.textContent = '';
        
        if (filteredTimelineData.length === 0) {
            const li = document.createElement('li');
            const div = document.createElement('div');
            div.className = 'text-center py-8';
            
            const icon = document.createElement('i');
            icon.className = 'fas fa-search text-gray-400 text-4xl mb-4';
            
            const p = document.createElement('p');
            p.className = 'text-gray-500';
            p.textContent = patientShowI18n.noHistoryRecords;
            
            div.appendChild(icon);
            div.appendChild(p);
            li.appendChild(div);
            timelineContainer.appendChild(li);
            return;
        }
        
        filteredTimelineData.forEach((item, index) => {
            const timelineItem = document.createElement('li');
            timelineItem.className = 'patient-timeline-node';
            
            const divRelative = document.createElement('div');
            divRelative.className = 'relative';
            
            if (index !== filteredTimelineData.length - 1) {
                const spanLine = document.createElement('span');
                spanLine.className = 'patient-timeline-rail';
                spanLine.setAttribute('aria-hidden', 'true');
                divRelative.appendChild(spanLine);
            }
            
            const link = document.createElement(item.url ? 'a' : 'div');
            if (item.url) {
                link.href = item.url;
            }
            link.className = 'patient-timeline-link';

            const divFlex = document.createElement('div');
            divFlex.className = 'patient-timeline-shell';
            
            // Icon
            const divIconOuter = document.createElement('div');
            const spanIconBg = document.createElement('span');
            spanIconBg.className = `patient-timeline-icon bg-${item.color}-500`;
            const icon = document.createElement('i');
            icon.className = `${item.icon} text-sm`;
            spanIconBg.appendChild(icon);
            divIconOuter.appendChild(spanIconBg);
            
            // Content
            const divContent = document.createElement('div');
            divContent.className = 'patient-timeline-main';
            
            const titleBlock = document.createElement('div');
            titleBlock.className = 'patient-timeline-title-block';
            const titleDiv = document.createElement('div');
            titleDiv.className = 'patient-timeline-title';
            titleDiv.textContent = item.title;
            const dateDiv = document.createElement('div');
            dateDiv.className = 'patient-timeline-date';
            const itemIso = String(item.date || '').split(' ')[0];
            const itemDisplay = window.formatClinicDateFromIso ? window.formatClinicDateFromIso(itemIso) : itemIso;
            const dateIcon = document.createElement('i');
            dateIcon.className = 'fas fa-calendar-day';
            const dateText = document.createElement('span');
            dateText.textContent = `${patientShowI18n.timelineOn} ${itemDisplay}`;
            dateDiv.appendChild(dateIcon);
            dateDiv.appendChild(dateText);
            titleBlock.appendChild(titleDiv);
            titleBlock.appendChild(dateDiv);
            
            const timelineMeta = Array.isArray(item.meta) ? [...item.meta] : [];
            if (item.doctor) {
                timelineMeta.push({
                    icon: 'fas fa-user-md',
                    text: `Dr. ${item.doctor}`
                });
            }

            const summaryWrap = document.createElement('div');
            summaryWrap.className = 'patient-timeline-summary';
            const pDesc = document.createElement('p');
            pDesc.className = 'patient-timeline-description';
            pDesc.textContent = item.description;
            summaryWrap.appendChild(pDesc);

            const timelineFacts = timelineMeta.filter((metaItem) => metaItem && metaItem.text);
            const timelineFactsPreview = timelineFacts.slice(0, 3);
            if (timelineFactsPreview.length > 0) {
                const metaWrap = document.createElement('div');
                metaWrap.className = 'patient-timeline-facts';
                timelineFactsPreview.forEach((metaItem) => {
                    const fact = document.createElement('span');
                    fact.className = 'patient-timeline-fact';
                    if (metaItem.icon) {
                        const metaIcon = document.createElement('i');
                        metaIcon.className = metaItem.icon;
                        fact.appendChild(metaIcon);
                    }
                    const metaText = document.createElement('span');
                    metaText.textContent = metaItem.text;
                    fact.appendChild(metaText);
                    metaWrap.appendChild(fact);
                });
                if (timelineFacts.length > timelineFactsPreview.length) {
                    const more = document.createElement('span');
                    more.className = 'patient-timeline-fact-more';
                    more.textContent = `+${timelineFacts.length - timelineFactsPreview.length}`;
                    metaWrap.appendChild(more);
                }
                summaryWrap.appendChild(metaWrap);
            }
            
            const divStatus = document.createElement('div');
            divStatus.className = 'patient-timeline-status';
            const spanStatus = document.createElement('span');
            spanStatus.className = `patient-timeline-status-label bg-${item.color}-100 text-${item.color}-800`;
            spanStatus.textContent = item.status_label || (item.status.charAt(0).toUpperCase() + item.status.slice(1));
            divStatus.appendChild(spanStatus);
            
            divContent.appendChild(titleBlock);
            divContent.appendChild(summaryWrap);
            divContent.appendChild(divStatus);
            
            divFlex.appendChild(divIconOuter);
            divFlex.appendChild(divContent);
            link.appendChild(divFlex);
            divRelative.appendChild(link);
            timelineItem.appendChild(divRelative);
            
            timelineContainer.appendChild(timelineItem);
        });
    }

    function filterTable(tabName) {
        const searchTerm = document.getElementById('historySearch').value.toLowerCase();
        const startDateRaw = document.getElementById('startDate').value;
        const endDateRaw = document.getElementById('endDate').value;
        const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startDateRaw) : startDateRaw;
        const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endDateRaw) : endDateRaw;
        
        const tableBody = document.querySelector(`#${tabName}-content table tbody`);
        const cardList = document.querySelector(`#${tabName}-content [data-patient-record-list]`);
        const rowContainer = tableBody || cardList;
        if (!rowContainer) return;
        
        const rows = tableBody ? rowContainer.querySelectorAll('tr') : rowContainer.querySelectorAll('.patient-record-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const cells = tableBody ? row.querySelectorAll('td') : row.querySelectorAll('[data-filter-cell]');
            if (cells.length === 0 && !row.textContent.trim()) return;
            
            let matchesSearch = true;
            let matchesDateRange = true;
            
            // Check search term
            if (searchTerm) {
                const searchableNodes = cells.length > 0 ? Array.from(cells) : [row];
                matchesSearch = searchableNodes.some(cell => 
                    cell.textContent.toLowerCase().includes(searchTerm)
                );
            }
            
            // Check date range (prefer ISO data-date when available)
            if (startDate || endDate) {
                const dateCell = cells[0];
                const rawDate = row.dataset.date || (dateCell ? dateCell.getAttribute('data-date') : '') || (dateCell ? dateCell.textContent.trim() : '');
                const cellDate = rawDate ? new Date(rawDate) : null;
                
                if (cellDate && !isNaN(cellDate.getTime())) {
                    if (startDate && cellDate < new Date(startDate + 'T00:00:00')) {
                        matchesDateRange = false;
                    }
                    if (endDate && cellDate > new Date(endDate + 'T23:59:59')) {
                        matchesDateRange = false;
                    }
                }
            }
            
            if (matchesSearch && matchesDateRange) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide "no results" message
        const noResultsDiv = document.querySelector(`#${tabName}-content .patient-filter-empty`) || document.querySelector(`#${tabName}-content .text-center`);
        if (noResultsDiv && visibleCount === 0) {
            noResultsDiv.style.display = 'block';
        } else if (noResultsDiv) {
            noResultsDiv.style.display = 'none';
        }
    }

    function clearFilters() {
        document.getElementById('historySearch').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        if (isBoundedHistoryTab(currentTab)) { applyHistoryFilters(); return; }
        
        if (currentTab === 'timeline') {
            filteredTimelineData = [...allTimelineData];
            renderTimeline();
        } else if (currentTab !== 'details') {
            filterTable(currentTab);
        }
    }

    document.addEventListener('click', function(event) {
        const chargeLineTrigger = event.target.closest('[data-patient-charge-line-open]');
        if (chargeLineTrigger) {
            event.preventDefault();
            event.stopPropagation();

            const chargeLineId = chargeLineTrigger.getAttribute('data-charge-line-id');
            const drawer = document.getElementById('patientChargeDrawer');
            const form = document.getElementById('patientChargeDrawerForm');
            if (drawer && form && chargeLineId) {
                form.querySelectorAll('[data-clinical-charge-line]').forEach((checkbox) => {
                    checkbox.checked = checkbox.value === chargeLineId;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                });

                if (typeof window.initFinanceCalculators === 'function') {
                    window.initFinanceCalculators(form);
                }
                if (window.AppDrawer && typeof window.AppDrawer.open === 'function') {
                    window.AppDrawer.open('patientChargeDrawer', chargeLineTrigger);
                }
            }

            return;
        }

        const row = event.target.closest('.patient-record-row[data-href]');
        if (!row) {
            return;
        }

        if (event.target.closest('a, button, form, input, select, textarea, label')) {
            return;
        }

        window.location.href = row.dataset.href;
    });

    function getSortableCellValue(row, columnIndex) {
        if (columnIndex === 0 && row.dataset.date) {
            const dateValue = Date.parse(row.dataset.date);
            if (!Number.isNaN(dateValue)) {
                return { type: 'number', value: dateValue };
            }
        }

        const cell = row.children[columnIndex];
        if (!cell) {
            return { type: 'text', value: '' };
        }

        const rawText = cell.textContent.replace(/\s+/g, ' ').trim();
        const currencyText = rawText.replace(/[^0-9.\-]/g, '');
        if (currencyText !== '' && /^-?\d+(\.\d+)?$/.test(currencyText)) {
            return { type: 'number', value: parseFloat(currencyText) };
        }

        const parsedDate = Date.parse(rawText);
        if (!Number.isNaN(parsedDate) && /\d/.test(rawText)) {
            return { type: 'number', value: parsedDate };
        }

        return { type: 'text', value: rawText.toLowerCase() };
    }

    function sortPatientRecordTable(table, columnIndex, direction) {
        const tbody = table.querySelector('tbody');
        if (!tbody) {
            return;
        }

        const rows = Array.from(tbody.querySelectorAll('tr'));
        const dataRows = rows.filter(row => row.children.length > 1);
        if (dataRows.length <= 1) {
            return;
        }

        dataRows.sort((a, b) => {
            const aValue = getSortableCellValue(a, columnIndex);
            const bValue = getSortableCellValue(b, columnIndex);

            let result = 0;
            if (aValue.type === 'number' && bValue.type === 'number') {
                result = aValue.value - bValue.value;
            } else {
                result = String(aValue.value).localeCompare(String(bValue.value), document.documentElement.lang || 'en');
            }

            return direction === 'desc' ? -result : result;
        });

        dataRows.forEach(row => tbody.appendChild(row));
    }

    function initializePatientRecordTables() {
        const headerIconMap = {
            attachments: ['fas fa-calendar-days', 'fas fa-file-lines', 'fas fa-folder-open', 'fas fa-note-sticky', 'fas fa-ellipsis'],
            contracts: ['fas fa-calendar-days', 'fas fa-file-signature', 'fas fa-stethoscope', 'fas fa-sack-dollar', 'fas fa-ellipsis'],
            examinations: ['fas fa-calendar-days', 'fas fa-stethoscope', 'fas fa-comment-medical', 'fas fa-circle-check', 'fas fa-ellipsis'],
            appointments: ['fas fa-calendar-days', 'fas fa-notes-medical', 'fas fa-hourglass-half', 'fas fa-circle-check', 'fas fa-ellipsis'],
            treatments: ['fas fa-briefcase-medical', 'fas fa-tooth', 'fas fa-sack-dollar', 'fas fa-circle-check', 'fas fa-ellipsis'],
            prescriptions: ['fas fa-pills', 'fas fa-prescription-bottle-medical', 'fas fa-repeat', 'fas fa-calendar-days', 'fas fa-circle-check', 'fas fa-ellipsis'],
            finances: ['fas fa-calendar-days', 'fas fa-receipt', 'fas fa-credit-card', 'fas fa-sack-dollar', 'fas fa-circle-check', 'fas fa-ellipsis']
        };

        document.querySelectorAll('.patient-record-table').forEach((table) => {
            const headers = Array.from(table.querySelectorAll('thead th'));
            if (headers.length === 0) {
                return;
            }

            const recordType = table.dataset.recordType || '';
            const headerIcons = headerIconMap[recordType] || [];

            headers.forEach((th, index) => {
                if (index === headers.length - 1 || th.dataset.sortReady === '1') {
                    return;
                }

                const label = th.textContent.trim();
                th.dataset.sortReady = '1';
                th.textContent = '';

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'patient-table-sort';
                button.dataset.direction = 'none';
                const iconClass = headerIcons[index] || 'fas fa-table-columns';
                const labelWrap = document.createElement('span');
                labelWrap.className = 'patient-table-sort-label';
                const headerIcon = document.createElement('i');
                headerIcon.className = iconClass;
                const labelText = document.createElement('span');
                labelText.textContent = label;
                const sortIcon = document.createElement('i');
                sortIcon.className = 'fas fa-sort';
                labelWrap.append(headerIcon, labelText);
                button.append(labelWrap, sortIcon);

                button.addEventListener('click', () => {
                    const currentDirection = button.dataset.direction === 'asc' ? 'asc' : (button.dataset.direction === 'desc' ? 'desc' : 'none');
                    const nextDirection = currentDirection === 'asc' ? 'desc' : 'asc';

                    headers.forEach((otherHeader) => {
                        const otherButton = otherHeader.querySelector('.patient-table-sort');
                        if (!otherButton) {
                            return;
                        }
                        otherButton.dataset.direction = 'none';
                        otherButton.classList.remove('is-active');
                        const icon = otherButton.lastElementChild;
                        if (icon) {
                            icon.className = 'fas fa-sort';
                        }
                    });

                    button.dataset.direction = nextDirection;
                    button.classList.add('is-active');
                    const icon = button.lastElementChild;
                    if (icon) {
                        icon.className = nextDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
                    }

                    sortPatientRecordTable(table, index, nextDirection);
                });

                th.appendChild(button);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initializePatientRecordTables);





</script>
