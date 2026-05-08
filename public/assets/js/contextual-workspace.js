(function () {
    function setOptions(select, options) {
        if (!select) return;
        select.replaceChildren();
        options.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.value || '';
            option.textContent = item.label || '';
            if (item.disabled) option.disabled = true;
            select.appendChild(option);
        });
        if (window.jQuery && window.jQuery(select).data('select2')) {
            window.jQuery(select).trigger('change.select2');
        }
    }

    window.initContextualAppointmentForms = function initContextualAppointmentForms(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-contextual-appointment-form]').forEach((wrap) => {
            if (wrap.dataset.appointmentBound === '1') return;
            wrap.dataset.appointmentBound = '1';

            const dateInput = wrap.querySelector('[data-appointment-date]');
            const timeSelect = wrap.querySelector('[data-appointment-time]');
            const durationSelect = wrap.querySelector('[data-appointment-duration]');
            const chairSelect = wrap.querySelector('[data-appointment-chair]');
            const slotsUrl = wrap.dataset.slotsUrl || '';
            const labels = {
                validDateFirst: document.documentElement.dir === 'rtl' ? 'أدخل تاريخًا صحيحًا أولًا' : 'Enter a valid date first',
                loading: document.documentElement.dir === 'rtl' ? 'جارٍ تحميل الأوقات...' : 'Loading times...',
                selectTime: document.documentElement.dir === 'rtl' ? 'اختر الوقت' : 'Select time',
                none: document.documentElement.dir === 'rtl' ? 'لا توجد أوقات متاحة' : 'No available times',
                prerequisites: document.documentElement.dir === 'rtl' ? 'اختر الطبيب والتاريخ والمدة أولًا' : 'Select doctor, date, and duration first',
            };

            if (document.documentElement.dir === 'rtl') {
                labels.validDateFirst = '\u0623\u062f\u062e\u0644 \u062a\u0627\u0631\u064a\u062e\u0627 \u0635\u062d\u064a\u062d\u0627 \u0623\u0648\u0644\u0627';
                labels.loading = '\u062c\u0627\u0631\u064a \u062a\u062d\u0645\u064a\u0644 \u0627\u0644\u0623\u0648\u0642\u0627\u062a...';
                labels.selectTime = '\u0627\u062e\u062a\u0631 \u0627\u0644\u0648\u0642\u062a';
                labels.none = '\u0644\u0627 \u062a\u0648\u062c\u062f \u0623\u0648\u0642\u0627\u062a \u0645\u062a\u0627\u062d\u0629';
                labels.prerequisites = '\u0627\u062e\u062a\u0631 \u0627\u0644\u0637\u0628\u064a\u0628 \u0648\u0627\u0644\u062a\u0627\u0631\u064a\u062e \u0648\u0627\u0644\u0645\u062f\u0629 \u0623\u0648\u0644\u0627';
            }

            function normalizeDate(raw) {
                return window.parseClinicDateToIso ? (window.parseClinicDateToIso(raw) || raw) : raw;
            }

            function loadSlots() {
                const date = normalizeDate((dateInput?.value || '').trim());
                const duration = durationSelect?.value || '';
                const chairName = chairSelect?.value || '';
                if (!date || !duration || !chairName) {
                    setOptions(timeSelect, [{ value: '', label: labels.prerequisites }]);
                    return;
                }
                if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                    setOptions(timeSelect, [{ value: '', label: labels.validDateFirst }]);
                    return;
                }
                setOptions(timeSelect, [{ value: '', label: labels.loading, disabled: true }]);
                fetch(`${slotsUrl}?date=${encodeURIComponent(date)}&duration=${encodeURIComponent(duration)}&chair_name=${encodeURIComponent(chairName)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then((response) => response.json())
                    .then((payload) => {
                        const rawSlots = Array.isArray(payload) ? payload : (Array.isArray(payload?.slots) ? payload.slots : []);
                        if (!rawSlots.length) {
                            setOptions(timeSelect, [{ value: '', label: labels.none }]);
                            return;
                        }
                        setOptions(timeSelect, [{ value: '', label: labels.selectTime }].concat(rawSlots.map((slot) => ({
                            value: slot.value || slot.time || slot,
                            label: slot.display || slot.label || slot.time || slot.value || slot,
                        }))));
                    })
                    .catch(() => setOptions(timeSelect, [{ value: '', label: labels.none }]));
            }

            [dateInput, durationSelect, chairSelect].forEach((element) => {
                element?.addEventListener('change', loadSlots);
                element?.addEventListener('input', loadSlots);
                if (window.jQuery) {
                    window.jQuery(element).on('select2:select select2:clear change', loadSlots);
                }
            });
            window.setTimeout(loadSlots, 120);
        });
    };

    window.initContextualTreatmentForms = function initContextualTreatmentForms(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-contextual-treatment-form]').forEach((wrap) => {
            if (wrap.dataset.treatmentBound === '1') return;
            wrap.dataset.treatmentBound = '1';
            const toggle = wrap.querySelector('[data-usage-toggle]');
            const section = wrap.querySelector('[data-usage-section]');
            const container = wrap.querySelector('[data-usage-items-container]');
            const addButton = wrap.querySelector('[data-add-usage-item]');
            const totalEl = wrap.querySelector('[data-usage-grand-total]');
            let rowCount = container ? container.querySelectorAll('.usage-item').length : 1;

            const formatCurrency = (amount) => {
                const value = Number.isFinite(amount) ? amount : 0;
                return value.toFixed(2);
            };
            const calculateTotal = () => {
                let total = 0;
                container?.querySelectorAll('.total-cost').forEach((field) => {
                    total += parseFloat(field.dataset.value || '0') || 0;
                });
                if (totalEl) totalEl.textContent = formatCurrency(total);
            };
            const bindRow = (row) => {
                if (row.dataset.usageRowBound === '1') return;
                row.dataset.usageRowBound = '1';
                const select = row.querySelector('.item-select');
                const qty = row.querySelector('.quantity-input');
                const unit = row.querySelector('.unit-cost');
                const total = row.querySelector('.total-cost');
                const remove = row.querySelector('[data-remove-usage-item]');
                const update = () => {
                    const option = select?.options?.[select.selectedIndex];
                    const price = parseFloat(option?.getAttribute('data-price') || '0') || 0;
                    const quantity = parseFloat(qty?.value || '0') || 0;
                    const rowTotal = price * quantity;
                    if (unit) unit.value = price ? formatCurrency(price) : '';
                    if (total) {
                        total.value = rowTotal ? formatCurrency(rowTotal) : '';
                        total.dataset.value = String(rowTotal || 0);
                    }
                    calculateTotal();
                };
                select?.addEventListener('change', update);
                qty?.addEventListener('input', update);
                remove?.addEventListener('click', () => {
                    const rows = container?.querySelectorAll('.usage-item') || [];
                    if (rows.length <= 1) {
                        row.querySelectorAll('select, input').forEach((field) => {
                            field.value = '';
                            if (field.classList.contains('total-cost')) field.dataset.value = '0';
                        });
                    } else {
                        row.remove();
                    }
                    calculateTotal();
                });
            };

            toggle?.addEventListener('change', () => section?.classList.toggle('hidden', !toggle.checked));
            container?.querySelectorAll('.usage-item').forEach(bindRow);
            addButton?.addEventListener('click', () => {
                const baseRow = container?.querySelector('.usage-item');
                if (!baseRow || !container) return;
                const newRow = baseRow.cloneNode(true);
                newRow.dataset.usageRowBound = '';
                newRow.querySelectorAll('.select2-container').forEach((element) => element.remove());
                newRow.querySelectorAll('select, input').forEach((field) => {
                    if (field.matches('select')) {
                        field.classList.remove('select2-hidden-accessible');
                        field.removeAttribute('data-select2-id');
                        field.removeAttribute('aria-hidden');
                        field.removeAttribute('tabindex');
                    }
                    if (field.name) field.name = field.name.replace(/\[\d+\]/, '[' + rowCount + ']');
                    field.value = '';
                    if (field.classList.contains('total-cost')) field.dataset.value = '0';
                });
                container.appendChild(newRow);
                rowCount++;
                if (typeof window.initAppSelects === 'function') window.initAppSelects(newRow);
                bindRow(newRow);
            });
        });
    };

    window.initContextualExaminationForms = function initContextualExaminationForms(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-contextual-exam]').forEach((wrap) => {
            if (wrap.dataset.examBound === '1') return;
            wrap.dataset.examBound = '1';
            const form = wrap.closest('form') || scope;
            const patientSelect = form.querySelector('[name="patient_id"]');
            const syncPregnancy = () => {
                const selectedGender = (wrap.dataset.patientGender || patientSelect?.selectedOptions?.[0]?.dataset?.gender || '').toLowerCase();
                wrap.querySelectorAll('[data-pregnancy-only="1"]').forEach((item) => {
                    const shouldDisable = !!selectedGender && selectedGender !== 'female';
                    item.classList.toggle('hidden', shouldDisable);
                    item.querySelectorAll('input, select, textarea').forEach((field) => {
                        if (typeof field.dataset.pregnancyOriginalDisabled === 'undefined') {
                            field.dataset.pregnancyOriginalDisabled = field.disabled ? '1' : '0';
                        }
                        if (shouldDisable) {
                            if (field.type === 'checkbox' || field.type === 'radio') {
                                field.checked = false;
                            }
                            field.disabled = true;
                        } else {
                            field.disabled = field.dataset.pregnancyOriginalDisabled === '1';
                        }
                    });
                });
            };
            patientSelect?.addEventListener('change', syncPregnancy);
            syncPregnancy();
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.initContextualAppointmentForms(document);
        window.initContextualTreatmentForms(document);
        window.initContextualExaminationForms(document);
    });
})();
