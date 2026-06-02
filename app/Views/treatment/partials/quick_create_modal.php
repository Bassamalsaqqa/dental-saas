<?php
$modalReturnTo = (string) ($return_to ?? 'treatment');
$modalSelectedPatient = $selected_patient ?? (!empty($patients[0]) ? $patients[0] : null);
$modalSelectedExaminationId = $selected_examination_id ?? null;
$modalContextual = !empty($contextual);
$modalShouldOpen = old('source_modal') === 'treatment_quick_create';
$modalFullPageQuery = ['return_to' => $modalReturnTo];
if (!empty($modalSelectedExaminationId)) {
    $modalFullPageQuery['examination_id'] = $modalSelectedExaminationId;
}
if (!empty($modalSelectedPatient['id'])) {
    $modalFullPageQuery['patient_id'] = $modalSelectedPatient['id'];
}
$modalFullPageHref = base_url('treatment/create?' . http_build_query($modalFullPageQuery));
?>
<?php if (has_permission('treatments', 'create')): ?>
    <style>
    .treatment-create-modal {
        visibility: hidden;
        pointer-events: none;
        transition: visibility 0s linear 240ms;
    }
    .treatment-create-modal.is-open {
        visibility: visible;
        pointer-events: auto;
        transition-delay: 0s;
    }
    .treatment-create-modal__backdrop {
        opacity: 0;
        transition: opacity 220ms ease;
    }
    .treatment-create-modal.is-open .treatment-create-modal__backdrop {
        opacity: 1;
    }
    .treatment-create-modal__panel {
        display: grid !important;
        grid-template-rows: auto minmax(0, 1fr) auto;
        opacity: 0;
        overflow: clip;
        transform: scale(0.86);
        transform-origin: center center;
        height: min(92dvh, 52rem);
        transition:
            opacity 180ms ease,
            transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, opacity;
    }
    .treatment-create-modal.is-open .treatment-create-modal__panel {
        opacity: 1;
        transform: scale(1);
    }
    .treatment-create-modal .select2-container {
        width: 100% !important;
    }
    @media (prefers-reduced-motion: reduce) {
        .treatment-create-modal__backdrop,
        .treatment-create-modal__panel {
            transition: none;
        }
    }
    @media (max-width: 640px) {
        .treatment-create-modal__shell {
            align-items: stretch;
            padding: 0;
        }
        .treatment-create-modal__panel {
            width: 100vw;
            height: 100dvh;
            min-height: 100dvh;
            max-height: 100dvh;
            border-radius: 0;
        }
    }
    </style>

    <div id="newTreatmentModal" class="app-scale-modal treatment-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newTreatmentModalTitle" dir="<?= !empty($isRtl) ? 'rtl' : 'ltr' ?>">
        <div class="treatment-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-treatment-create-modal-close></div>
        <div class="treatment-create-modal__shell relative z-10 flex h-full items-center justify-center p-4" data-treatment-create-modal-close>
            <form id="newTreatmentQuickCreateForm" action="<?= base_url('treatment/store') ?>" method="POST" class="treatment-create-modal__panel flex w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="treatment_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-purple-600 text-white shadow-lg">
                            <i class="fas fa-tooth"></i>
                        </span>
                        <div>
                            <h2 id="newTreatmentModalTitle" class="text-lg font-bold text-slate-900"><?= esc(lang('Treatment.new_treatment')) ?></h2>
                            <a href="<?= esc($modalFullPageHref, 'attr') ?>" data-treatment-full-page-link data-default-href="<?= esc($modalFullPageHref, 'attr') ?>" class="mt-1 inline-flex text-xs font-semibold text-pink-700 hover:text-pink-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-treatment-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 overflow-y-auto bg-slate-50/70 p-5">
                    <?php if (session('error') && $modalShouldOpen): ?>
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                        <?= view('treatment/partials/form_fields', [
                            'contextual' => $modalContextual,
                            'selected_patient' => $modalSelectedPatient,
                            'patients' => $modalContextual ? [] : ($patients ?? []),
                            'treatment_types' => $treatment_types ?? [],
                            'inventory_items' => $inventory_items ?? [],
                            'clinic_providers' => $clinic_providers ?? [],
                            'validation' => $validation ?? null,
                            'selected_examination_id' => $modalSelectedExaminationId,
                            'id_prefix' => 'quick_treatment_charge',
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-treatment-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('Treatment.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-pink-500 to-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-pink-600 hover:to-purple-700 hover:shadow-xl">
                        <i class="fas fa-tooth me-2"></i>
                        <?= esc(lang('Treatment.create_treatment')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        if (window.__treatmentQuickCreateModalBound) {
            return;
        }
        window.__treatmentQuickCreateModalBound = true;

        let treatmentModalTrigger = null;
        let treatmentModalCloseTimer = null;

        function mountTreatmentModal() {
            const modal = document.getElementById('newTreatmentModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (modal && modal.dataset.closeControlsBound !== '1') {
                modal.dataset.closeControlsBound = '1';
                modal.querySelectorAll('button[data-treatment-create-modal-close], a[data-treatment-create-modal-close]').forEach(function (control) {
                    control.addEventListener('click', function (event) {
                        event.preventDefault();
                        closeTreatmentModal();
                    });
                });
            }
            return modal;
        }

        function initializeTreatmentModalContent(modal) {
            if (!modal) {
                return Promise.resolve();
            }
            const tasks = [];
            if (typeof window.initAppSelects === 'function') {
                tasks.push(Promise.resolve(window.initAppSelects(modal)));
            }
            if (typeof window.initializeClinicDateInputs === 'function') {
                window.initializeClinicDateInputs(modal);
            }
            if (typeof window.initContextualTreatmentForms === 'function') {
                window.initContextualTreatmentForms(modal);
            }
            return Promise.allSettled(tasks);
        }

        function getTreatmentPatientSelect(modal) {
            return modal ? modal.querySelector('select[name="patient_id"]') : null;
        }

        function updateTreatmentFullPageLink(modal, patientId) {
            const link = modal ? modal.querySelector('[data-treatment-full-page-link]') : null;
            if (!link) {
                return;
            }

            const defaultHref = link.dataset.defaultHref || link.getAttribute('href') || '<?= esc($modalFullPageHref, 'js') ?>';
            if (!patientId) {
                link.setAttribute('href', defaultHref);
                return;
            }

            try {
                const url = new URL(defaultHref, window.location.origin);
                url.searchParams.set('patient_id', patientId);
                if (!url.searchParams.get('return_to')) {
                    url.searchParams.set('return_to', '<?= esc($modalReturnTo, 'js') ?>');
                }
                link.setAttribute('href', url.toString());
            } catch (error) {
                link.setAttribute('href', '<?= base_url('treatment/create') ?>?patient_id=' + encodeURIComponent(patientId) + '&return_to=<?= rawurlencode($modalReturnTo) ?>');
            }
        }

        function triggerTreatmentFieldChange(field) {
            if (!field) {
                return;
            }
            field.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && window.jQuery(field).hasClass('select2-hidden-accessible')) {
                window.jQuery(field).trigger('change.select2');
            }
        }

        function clearDynamicTreatmentPatient(modal) {
            const patientSelect = getTreatmentPatientSelect(modal);
            if (patientSelect) {
                patientSelect.querySelectorAll('option[data-dynamic-patient-option="1"]').forEach(function (option) {
                    option.remove();
                });
                patientSelect.value = '';
                triggerTreatmentFieldChange(patientSelect);
            }
            updateTreatmentFullPageLink(modal, '');
        }

        function applyTreatmentTriggerContext(modal, trigger) {
            const patientSelect = getTreatmentPatientSelect(modal);
            const patientId = trigger && trigger.dataset ? (trigger.dataset.contextPatientId || '') : '';
            if (!patientSelect || !patientId) {
                updateTreatmentFullPageLink(modal, patientId);
                return;
            }

            const patientName = trigger.dataset.contextPatientName || '';
            const patientCode = trigger.dataset.contextPatientCode || '';
            const patientLabel = [patientName, patientCode].filter(Boolean).join(' - ') || ('Patient #' + patientId);
            let option = Array.from(patientSelect.options).find(function (candidate) {
                return candidate.value === patientId;
            });
            if (!option) {
                option = new Option(patientLabel, patientId, true, true);
                option.dataset.dynamicPatientOption = '1';
                patientSelect.add(option);
            }
            option.text = patientLabel;
            option.selected = true;
            patientSelect.value = patientId;
            triggerTreatmentFieldChange(patientSelect);
            updateTreatmentFullPageLink(modal, patientId);
        }

        function resetTreatmentModalForm(modal) {
            const form = modal ? modal.querySelector('form') : null;
            if (!form) {
                return;
            }
            form.reset();
            const usageContainer = form.querySelector('[data-usage-items-container]');
            if (usageContainer) {
                const rows = Array.from(usageContainer.querySelectorAll('.usage-item'));
                rows.slice(1).forEach(function (row) { row.remove(); });
                rows.slice(0, 1).forEach(function (row) {
                    row.querySelectorAll('select, input').forEach(function (field) {
                        field.value = '';
                        if (field.classList.contains('total-cost')) {
                            field.dataset.value = '0';
                        }
                        if (window.jQuery && field.tagName === 'SELECT' && window.jQuery(field).data('select2')) {
                            window.jQuery(field).val('').trigger('change');
                        }
                    });
                });
            }
            form.querySelectorAll('[data-usage-grand-total]').forEach(function (field) { field.textContent = '0.00'; });
            clearDynamicTreatmentPatient(modal);
            form.querySelectorAll('select, input[type="checkbox"], input[type="radio"]').forEach(triggerTreatmentFieldChange);
            if (typeof window.initContextualTreatmentForms === 'function') {
                window.initContextualTreatmentForms(modal);
            }
        }

        function openTreatmentModal(trigger) {
            const modal = mountTreatmentModal();
            if (!modal) {
                return;
            }
            treatmentModalTrigger = trigger || document.activeElement;
            if (treatmentModalCloseTimer) {
                clearTimeout(treatmentModalCloseTimer);
                treatmentModalCloseTimer = null;
            }
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('app-drawer-lock');
            document.body.classList.add('app-drawer-lock');
            requestAnimationFrame(function () {
                modal.classList.add('is-open');
            });
            applyTreatmentTriggerContext(modal, trigger);
            window.setTimeout(function () {
                initializeTreatmentModalContent(modal);
            }, 80);
            window.setTimeout(function () {
                const firstField = modal.querySelector('[name="patient_id"], [name="treatment_type"], input, select, textarea, button');
                if (firstField && typeof firstField.focus === 'function') {
                    firstField.focus({ preventScroll: true });
                }
            }, 120);
        }

        function closeTreatmentModal() {
            const modal = document.getElementById('newTreatmentModal');
            if (!modal || modal.classList.contains('hidden')) {
                return;
            }
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('app-drawer-lock');
            document.body.classList.remove('app-drawer-lock');
            treatmentModalCloseTimer = setTimeout(function () {
                modal.classList.add('hidden');
                resetTreatmentModalForm(modal);
                if (treatmentModalTrigger && typeof treatmentModalTrigger.focus === 'function') {
                    treatmentModalTrigger.focus();
                }
                treatmentModalTrigger = null;
            }, 240);
        }

        window.openTreatmentModal = openTreatmentModal;
        window.closeTreatmentModal = closeTreatmentModal;

        document.addEventListener('click', function (event) {
            const openTrigger = event.target.closest('[data-treatment-create-modal-open]');
            if (openTrigger) {
                event.preventDefault();
                openTreatmentModal(openTrigger);
                return;
            }

            const closeTrigger = event.target.closest('[data-treatment-create-modal-close]');
            if (closeTrigger) {
                event.preventDefault();
                closeTreatmentModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }
            const modal = document.getElementById('newTreatmentModal');
            if (modal && !modal.classList.contains('hidden')) {
                event.preventDefault();
                closeTreatmentModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (<?= $modalShouldOpen ? 'true' : 'false' ?>) {
                openTreatmentModal(document.getElementById('newTreatmentModalTrigger'));
            }
        });
    })();
    </script>
<?php endif; ?>
