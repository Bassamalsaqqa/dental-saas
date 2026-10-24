(function () {
    function setOptions(select, options) {
        if (!select) return;
        select.disabled = false;
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

    function normalizeAppointmentTime(value) {
        const raw = String(value || '').trim();
        const match = raw.match(/^(\d{1,2}):(\d{2})/);
        if (!match) return raw;
        return `${match[1].padStart(2, '0')}:${match[2]}`;
    }

    function applyPendingAppointmentTime(wrap, timeSelect) {
        const pendingTime = normalizeAppointmentTime(wrap?.dataset?.prefillAppointmentTime || '');
        if (!pendingTime || !timeSelect) return;

        const matchingOption = Array.from(timeSelect.options || []).find((option) => {
            return normalizeAppointmentTime(option.value) === pendingTime;
        });
        if (!matchingOption) return;

        timeSelect.value = matchingOption.value;
        delete wrap.dataset.prefillAppointmentTime;
        if (window.jQuery && window.jQuery(timeSelect).data('select2')) {
            window.jQuery(timeSelect).trigger('change.select2');
        } else {
            timeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function normalizeSlotsPayload(payload) {
        if (payload && payload.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(payload.csrf_token);
        }

        if (Array.isArray(payload)) {
            return payload;
        }

        if (payload && Array.isArray(payload.slots)) {
            return payload.slots;
        }

        if (payload && typeof payload === 'object') {
            return Object.values(payload).filter((item) => {
                if (!item || typeof item !== 'object') {
                    return false;
                }

                const hasValue = Object.prototype.hasOwnProperty.call(item, 'value');
                const hasDisplay = Object.prototype.hasOwnProperty.call(item, 'display');
                const hasLabel = Object.prototype.hasOwnProperty.call(item, 'label');
                const hasTime = Object.prototype.hasOwnProperty.call(item, 'time');

                return (hasValue && (hasDisplay || hasLabel)) || (hasTime && hasDisplay);
            });
        }

        return [];
    }

    window.initContextualAppointmentForms = function initContextualAppointmentForms(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-contextual-appointment-form]').forEach((wrap) => {
            if (wrap.dataset.appointmentBound === '1') {
                if (typeof wrap.__loadAppointmentSlots === 'function') {
                    wrap.__loadAppointmentSlots();
                }
                return;
            }
            wrap.dataset.appointmentBound = '1';

            const dateInput = wrap.querySelector('[data-appointment-date]');
            const timeSelect = wrap.querySelector('[data-appointment-time]');
            const durationSelect = wrap.querySelector('[data-appointment-duration]');
            const chairSelect = wrap.querySelector('[data-appointment-chair]');
            const providerSelect = wrap.querySelector('[data-appointment-provider]');
            const isDynamic = Boolean(providerSelect);
            const slotsUrl = wrap.dataset.slotsUrl || '';
            const labels = {
                validDateFirst: document.documentElement.dir === 'rtl' ? 'أدخل تاريخًا صحيحًا أولًا' : 'Enter a valid date first',
                loading: document.documentElement.dir === 'rtl' ? 'جارٍ تحميل الأوقات...' : 'Loading times...',
                selectTime: document.documentElement.dir === 'rtl' ? 'اختر الوقت' : 'Select time',
                none: document.documentElement.dir === 'rtl' ? 'لا توجد أوقات متاحة' : 'No available times',
                prerequisites: document.documentElement.dir === 'rtl' ? 'اختر الطبيب والتاريخ والمدة أولًا' : 'Select doctor, date, and duration first',
                failed: document.documentElement.dir === 'rtl' ? 'فشل تحميل الأوقات المتاحة' : 'Failed to load available times',
            };

            if (document.documentElement.dir === 'rtl') {
                labels.validDateFirst='\u0623\u062f\u062e\u0644 \u062a\u0627\u0631\u064a\u062e\u0627 \u0635\u062d\u064a\u062d\u0627 \u0623\u0648\u0644\u0627';
                labels.loading='\u062c\u0627\u0631\u064a \u062a\u062d\u0645\u064a\u0644 \u0627\u0644\u0623\u0648\u0642\u0627\u062a...';
                labels.selectTime='\u0627\u062e\u062a\u0631 \u0627\u0644\u0648\u0642\u062a';
                labels.none='\u0644\u0627 \u062a\u0648\u062c\u062f \u0623\u0648\u0642\u0627\u062a \u0645\u062a\u0627\u062d\u0629';
                labels.prerequisites='\u0627\u062e\u062a\u0631 \u0627\u0644\u0637\u0628\u064a\u0628 \u0648\u0627\u0644\u062a\u0627\u0631\u064a\u062e \u0648\u0627\u0644\u0645\u062f\u0629 \u0623\u0648\u0644\u0627';
                labels.failed='\u062a\u0639\u0630\u0631 \u062a\u062d\u0645\u064a\u0644 \u0627\u0644\u0623\u0648\u0642\u0627\u062a';
            }

            function clearSlotError() {
                const existing = wrap.querySelector('.slot-load-error');
                if (existing) {
                    existing.remove();
                }
            }

            function showSlotError(msg) {
                clearSlotError();
                const errDiv = document.createElement('div');
                errDiv.className = 'slot-load-error v3-text-danger text-xs mt-1.5 flex items-center gap-1';
                const icon = document.createElement('i');
                icon.className = 'fas fa-exclamation-circle';
                errDiv.appendChild(icon);
                errDiv.appendChild(document.createTextNode(msg));
                timeSelect?.parentNode?.appendChild(errDiv);
            }

            function normalizeDate(raw) {
                return window.parseClinicDateToIso ? (window.parseClinicDateToIso(raw) || raw) : raw;
            }

            let currentSlotRequestId = 0;

            function loadSlots() {
                const requestId = ++currentSlotRequestId;
                const date = normalizeDate((dateInput?.value || '').trim());
                const duration = durationSelect?.value || '';
                const providerUserId = providerSelect ? (providerSelect.value || '').trim() : '';
                const chairName = chairSelect ? (chairSelect.value || '').trim() : '';

                clearSlotError();

                if (isDynamic) {
                    if (!date || !duration || !providerUserId) {
                        setOptions(timeSelect, [{ value: '', label: labels.prerequisites }]);
                        applyPendingAppointmentTime(wrap, timeSelect);
                        return;
                    }
                } else {
                    if (!date || !duration || !chairName) {
                        setOptions(timeSelect, [{ value: '', label: labels.prerequisites }]);
                        applyPendingAppointmentTime(wrap, timeSelect);
                        return;
                    }
                }

                if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                    setOptions(timeSelect, [{ value: '', label: labels.validDateFirst }]);
                    applyPendingAppointmentTime(wrap, timeSelect);
                    return;
                }

                setOptions(timeSelect, [{ value: '', label: labels.loading, disabled: true }]);
                timeSelect.disabled = true;

                let targetUrl;
                try {
                    targetUrl = new URL(slotsUrl, window.location.origin);
                } catch (e) {
                    targetUrl = new URL(slotsUrl, window.location.href);
                }

                targetUrl.searchParams.set('date', date);
                targetUrl.searchParams.set('duration', duration);
                if (wrap.dataset.excludeId) {
                    targetUrl.searchParams.set('exclude_id', wrap.dataset.excludeId);
                }

                if (isDynamic) {
                    targetUrl.searchParams.set('provider_user_id', providerUserId);
                    targetUrl.searchParams.delete('chair_name');
                } else {
                    targetUrl.searchParams.set('chair_name', chairName);
                    targetUrl.searchParams.delete('provider_user_id');
                }

                fetch(targetUrl.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then((payload) => {
                        if (requestId !== currentSlotRequestId) {
                            return;
                        }
                        timeSelect.disabled = false;
                        const rawSlots = normalizeSlotsPayload(payload);
                        if (!rawSlots.length) {
                            const errorMsg = (payload && typeof payload.error === 'string') ? payload.error : labels.none;
                            setOptions(timeSelect, [{ value: '', label: errorMsg }]);
                            applyPendingAppointmentTime(wrap, timeSelect);
                            return;
                        }
                        setOptions(timeSelect, [{ value: '', label: labels.selectTime }].concat(rawSlots.map((slot) => ({
                            value: slot.value || slot.time || slot,
                            label: slot.display || slot.label || slot.time || slot.value || slot,
                        }))));
                        applyPendingAppointmentTime(wrap, timeSelect);
                    })
                    .catch(() => {
                        if (requestId !== currentSlotRequestId) {
                            return;
                        }
                        timeSelect.disabled = false;
                        setOptions(timeSelect, [{ value: '', label: labels.none }]);
                        showSlotError(labels.failed);
                        applyPendingAppointmentTime(wrap, timeSelect);
                    });
            }

            wrap.__loadAppointmentSlots = loadSlots;

            [dateInput, durationSelect, chairSelect, providerSelect].filter(Boolean).forEach((element) => {
                element.addEventListener('change', loadSlots);
                element.addEventListener('input', loadSlots);
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
            if (wrap.dataset.treatmentBound === '1') {
                if (typeof wrap.__syncTreatmentForm === 'function') {
                    wrap.__syncTreatmentForm();
                }
                return;
            }
            wrap.dataset.treatmentBound = '1';
            const toggle = wrap.querySelector('[data-usage-toggle]');
            const section = wrap.querySelector('[data-usage-section]');
            const container = wrap.querySelector('[data-usage-items-container]');
            const addButton = wrap.querySelector('[data-add-usage-item]');
            const totalEl = wrap.querySelector('[data-usage-grand-total]');
            let rowCount = container ? container.querySelectorAll('.usage-item').length : 1;
            const chargeToggle = wrap.querySelector('[data-charge-toggle]');
            const chargeBody = wrap.querySelector('[data-charge-body]');
            const providerType = wrap.querySelector('[data-charge-provider-type]');
            const providerUserWrap = wrap.querySelector('[data-provider-user-wrap]');
            const providerGuestWrap = wrap.querySelector('[data-provider-guest-wrap]');

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

            const syncChargeToggle = () => {
                if (!chargeToggle || !chargeBody) return;
                chargeBody.classList.toggle('hidden', !chargeToggle.checked);
            };

            const syncProviderFields = () => {
                const selected = providerType?.value || 'none';
                providerUserWrap?.classList.toggle('hidden', selected !== 'user');
                providerGuestWrap?.classList.toggle('hidden', selected !== 'guest');
                providerUserWrap?.querySelectorAll('select, input, textarea').forEach((field) => {
                    field.disabled = selected !== 'user';
                    if (selected !== 'user') {
                        field.value = '';
                        if (window.jQuery && field.tagName === 'SELECT') {
                            window.jQuery(field).val('').trigger('change');
                        }
                    }
                });
                providerGuestWrap?.querySelectorAll('select, input, textarea').forEach((field) => {
                    field.disabled = selected !== 'guest';
                    if (selected !== 'guest') field.value = '';
                });
            };

            chargeToggle?.addEventListener('change', syncChargeToggle);
            providerType?.addEventListener('change', syncProviderFields);
            if (window.jQuery && providerType) {
                window.jQuery(providerType).on('select2:select select2:clear change', syncProviderFields);
            }
            wrap.__syncTreatmentForm = function () {
                section?.classList.toggle('hidden', !toggle?.checked);
                calculateTotal();
                syncChargeToggle();
                syncProviderFields();
            };
            wrap.__syncTreatmentForm();
        });
    };

    window.initContextualExaminationForms = function initContextualExaminationForms(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-contextual-exam]').forEach((wrap) => {
            if (wrap.dataset.examBound === '1') return;
            wrap.dataset.examBound = '1';
            const form = wrap.closest('form') || scope;
            const patientSelect = form.querySelector('[name="patient_id"]');

            const syncToggleVisual = (toggle) => {
                if (!toggle) return;
                const track = document.getElementById(toggle.dataset.trackId || '');
                const dot = document.getElementById(toggle.dataset.dotId || '');
                if (track) {
                    track.classList.toggle('v3-bg-primary', toggle.checked);
                    track.classList.toggle('v3-bg-sand', !toggle.checked);
                }
                if (typeof window.applyToggleDotState === 'function') {
                    window.applyToggleDotState(dot, toggle.checked);
                } else if (dot) {
                    dot.style.transform = toggle.checked
                        ? (document.documentElement.dir === 'rtl' ? 'translateX(-20px)' : 'translateX(20px)')
                        : 'translateX(0)';
                }
            };

            const syncHealthDetails = () => {
                const toggles = Array.from(wrap.querySelectorAll('.health-update-toggle')).filter((toggle) => !toggle.disabled);
                const anyChecked = toggles.some((toggle) => toggle.checked);
                const detailsId = toggles.find((toggle) => toggle.dataset.detailsId)?.dataset.detailsId;
                const detailsWrap = detailsId ? document.getElementById(detailsId) : wrap.querySelector('[id$="healthUpdateDetailsWrap"]');
                const detailsField = detailsWrap?.querySelector('textarea, input, select');
                if (detailsField) {
                    detailsField.disabled = !anyChecked;
                    if (!anyChecked) {
                        detailsField.value = '';
                    }
                }
            };

            wrap.querySelectorAll('[data-exam-optional-toggle]').forEach((toggle) => {
                const syncOptional = () => {
                    const section = document.getElementById(toggle.dataset.sectionId || '');
                    section?.classList.toggle('hidden', !toggle.checked);
                    syncToggleVisual(toggle);
                    if (!toggle.checked && section) {
                        section.querySelectorAll('input[type="checkbox"]').forEach((field) => {
                            field.checked = false;
                            syncToggleVisual(field);
                        });
                        section.querySelectorAll('textarea, input:not([type="hidden"]):not([type="checkbox"]), select').forEach((field) => {
                            field.value = '';
                            if (window.jQuery && field.tagName === 'SELECT') {
                                window.jQuery(field).trigger('change');
                            }
                        });
                    }
                    syncHealthDetails();
                };
                toggle.addEventListener('change', syncOptional);
                syncOptional();
            });

            wrap.querySelectorAll('.health-update-toggle').forEach((toggle) => {
                toggle.addEventListener('change', () => {
                    syncToggleVisual(toggle);
                    syncHealthDetails();
                });
                syncToggleVisual(toggle);
            });

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
                    item.querySelectorAll('.health-update-toggle').forEach(syncToggleVisual);
                });
                syncHealthDetails();
            };
            patientSelect?.addEventListener('change', syncPregnancy);
            syncPregnancy();
            syncHealthDetails();
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.initContextualAppointmentForms(document);
        window.initContextualTreatmentForms(document);
        window.initContextualExaminationForms(document);
    });
})();
