<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$isRtl = !empty($is_rtl);
$modalReturnTo = (string) ($return_to ?? 'examination');
$modalSelectedPatient = $selected_patient ?? null;
$modalContextual = !empty($contextual);
$shouldOpenExaminationCreateModal = old('source_modal') === 'examination_quick_create';
$modalFullPageQuery = ['return_to' => $modalReturnTo];
if (!empty($modalSelectedPatient['id'])) {
    $modalFullPageQuery['patient_id'] = (int) $modalSelectedPatient['id'];
}
?>
<?php if (has_permission('examinations', 'create')): ?>
    <style>
    .examination-create-modal {
        visibility: hidden;
        pointer-events: none;
        transition: visibility 0s linear 240ms;
    }
    .examination-create-modal.is-open {
        visibility: visible;
        pointer-events: auto;
        transition-delay: 0s;
    }
    .examination-create-modal__backdrop {
        opacity: 0;
        transition: opacity 220ms ease;
    }
    .examination-create-modal.is-open .examination-create-modal__backdrop {
        opacity: 1;
    }
    .examination-create-modal__panel {
        display: grid !important;
        grid-template-rows: auto minmax(0, 1fr) auto;
        opacity: 0;
        overflow: clip;
        transform: scale(0.86);
        transform-origin: center center;
        height: min(92dvh, 52rem);
        transition: opacity 180ms ease, transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, opacity;
    }
    .examination-create-modal.is-open .examination-create-modal__panel {
        opacity: 1;
        transform: scale(1);
    }
    .examination-create-modal .select2-container {
        width: 100% !important;
    }
    .examination-create-modal [data-health-update-item] {
        align-self: start;
    }
    @media (prefers-reduced-motion: reduce) {
        .examination-create-modal__backdrop,
        .examination-create-modal__panel {
            transition: none;
        }
    }
    @media (max-width: 640px) {
        .examination-create-modal__shell {
            align-items: stretch;
            padding: 0;
        }
        .examination-create-modal__panel {
            width: 100vw;
            height: 100dvh;
            min-height: 100dvh;
            max-height: 100dvh;
            border-radius: 0;
        }
    }
    </style>

    <div id="newExaminationModal" class="app-scale-modal examination-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newExaminationModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="examination-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-examination-create-modal-close></div>
        <div class="examination-create-modal__shell relative z-10 flex min-h-screen items-center justify-center p-4" data-examination-create-modal-close>
            <form id="newExaminationQuickCreateForm" action="<?= base_url('examination/store') ?>" method="POST" class="examination-create-modal__panel flex w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-examination-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="examination_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 text-white shadow-lg">
                            <i class="fas fa-stethoscope"></i>
                        </span>
                        <div>
                            <h2 id="newExaminationModalTitle" class="text-lg font-bold text-slate-900"><?= esc($tr('new_examination')) ?></h2>
                            <a href="<?= esc(base_url('examination/create?' . http_build_query($modalFullPageQuery)), 'attr') ?>" class="mt-1 inline-flex text-xs font-semibold text-orange-700 hover:text-orange-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-examination-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-5">
                    <?php if (session('error') && $shouldOpenExaminationCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                        <?= view('examination/partials/form_fields', [
                            'contextual' => $modalContextual,
                            'selected_patient' => $modalSelectedPatient,
                            'validation' => session()->getFlashdata('validation'),
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'examinationQuick',
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-examination-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc($tr('cancel')) ?>
                    </button>
                    <button type="submit" name="after_save" value="odontogram" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 px-5 py-2.5 text-sm font-bold text-cyan-800 transition hover:bg-cyan-100">
                        <i class="fas fa-tooth me-2"></i>
                        <?= esc($tr('create_and_open_chart')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-orange-600 hover:to-amber-700 hover:shadow-xl">
                        <i class="fas fa-stethoscope me-2"></i>
                        <?= esc($tr('create_examination')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        if (window.__examinationQuickCreateModalBound) {
            return;
        }
        window.__examinationQuickCreateModalBound = true;

        let newExaminationModalTrigger = null;
        let newExaminationModalCloseTimer = null;

        function mountNewExaminationModal() {
            const modal = document.getElementById('newExaminationModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (modal && modal.dataset.closeControlsBound !== '1') {
                modal.dataset.closeControlsBound = '1';
                modal.querySelectorAll('button[data-examination-create-modal-close], a[data-examination-create-modal-close]').forEach(function (control) {
                    control.addEventListener('click', function (event) {
                        event.preventDefault();
                        closeNewExaminationModal();
                    });
                });
            }
            return modal;
        }

        function getNewExaminationPatientSelect(modal) {
            return modal?.querySelector('select[name="patient_id"]') || null;
        }

        function clearDynamicExaminationPatient(modal) {
            const patientSelect = getNewExaminationPatientSelect(modal);
            if (!patientSelect) {
                return;
            }
            patientSelect.querySelectorAll('option[data-dynamic-patient-option="1"]').forEach(function (option) {
                option.remove();
            });
            patientSelect.value = '';
            if (window.jQuery) {
                window.jQuery(patientSelect).val('').trigger('change');
            } else {
                patientSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
            modal.querySelectorAll('[data-contextual-exam]').forEach(function (wrap) {
                wrap.dataset.patientGender = '';
                delete wrap.dataset.examBound;
            });
        }

        function applyNewExaminationTriggerContext(modal, trigger) {
            if (!modal) {
                return;
            }
            const patientId = trigger?.dataset?.contextPatientId || '';
            const patientSelect = getNewExaminationPatientSelect(modal);
            const fullPageLink = modal.querySelector('a[href*="examination/create"]');
            if (fullPageLink && !fullPageLink.dataset.defaultHref) {
                fullPageLink.dataset.defaultHref = fullPageLink.getAttribute('href') || '';
            }
            if (!patientId || !patientSelect) {
                clearDynamicExaminationPatient(modal);
                if (fullPageLink?.dataset.defaultHref) {
                    fullPageLink.setAttribute('href', fullPageLink.dataset.defaultHref);
                }
                return;
            }
            const patientName = trigger.dataset.contextPatientName || '';
            const patientCode = trigger.dataset.contextPatientCode || '';
            const patientGender = (trigger.dataset.contextPatientGender || '').toLowerCase();
            const label = patientName && patientCode ? `${patientName} (${patientCode})` : (patientName || patientCode || `#${patientId}`);
            patientSelect.querySelectorAll('option[data-dynamic-patient-option="1"]').forEach(function (option) {
                option.remove();
            });
            let option = Array.from(patientSelect.options).find(function (candidate) {
                return String(candidate.value) === String(patientId);
            });
            if (!option) {
                option = new Option(label, patientId, true, true);
                option.dataset.dynamicPatientOption = '1';
                patientSelect.appendChild(option);
            }
            option.selected = true;
            option.dataset.gender = patientGender;
            patientSelect.value = patientId;
            modal.querySelectorAll('[data-contextual-exam]').forEach(function (wrap) {
                wrap.dataset.patientGender = patientGender;
                delete wrap.dataset.examBound;
            });
            if (window.jQuery) {
                window.jQuery(patientSelect).val(patientId).trigger('change');
            } else {
                patientSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (fullPageLink?.dataset.defaultHref) {
                try {
                    const href = new URL(fullPageLink.dataset.defaultHref, window.location.origin);
                    href.searchParams.set('patient_id', patientId);
                    href.searchParams.set('return_to', 'patients');
                    fullPageLink.setAttribute('href', href.toString());
                } catch (error) {
                    fullPageLink.setAttribute('href', `<?= base_url('examination/create') ?>?patient_id=${encodeURIComponent(patientId)}&return_to=patients`);
                }
            }
        }
        function initializeNewExaminationModalContent(modal) {
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
            if (typeof window.initContextualExaminationForms === 'function') {
                window.initContextualExaminationForms(modal);
            }
            return Promise.allSettled(tasks);
        }

        function openNewExaminationModal(trigger) {
            const modal = mountNewExaminationModal();
            if (!modal) {
                return;
            }
            newExaminationModalTrigger = trigger || document.activeElement;
            applyNewExaminationTriggerContext(modal, trigger);
            if (newExaminationModalCloseTimer) {
                clearTimeout(newExaminationModalCloseTimer);
                newExaminationModalCloseTimer = null;
            }
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('app-drawer-lock');
            document.body.classList.add('app-drawer-lock');
            requestAnimationFrame(function () {
                modal.classList.add('is-open');
            });
            window.setTimeout(function () {
                initializeNewExaminationModalContent(modal);
                const firstField = modal.querySelector('[name="patient_id"], input, select, textarea, button');
                if (firstField && typeof firstField.focus === 'function') {
                    firstField.focus({ preventScroll: true });
                }
            }, 80);
        }

        function closeNewExaminationModal() {
            const modal = document.getElementById('newExaminationModal');
            if (!modal || modal.classList.contains('hidden')) {
                return;
            }
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('app-drawer-lock');
            document.body.classList.remove('app-drawer-lock');
            newExaminationModalCloseTimer = setTimeout(function () {
                const form = modal.querySelector('[data-examination-create-form]');
                if (form) {
                    form.reset();
                    form.querySelectorAll('select, input[type="checkbox"]').forEach(function (field) {
                        field.dispatchEvent(new Event('change', { bubbles: true }));
                        if (window.jQuery && field.tagName === 'SELECT') {
                            window.jQuery(field).trigger('change');
                        }
                    });
                    clearDynamicExaminationPatient(modal);
                    if (typeof window.initContextualExaminationForms === 'function') {
                        window.initContextualExaminationForms(form);
                    }
                }
                modal.classList.add('hidden');
                if (newExaminationModalTrigger && typeof newExaminationModalTrigger.focus === 'function') {
                    newExaminationModalTrigger.focus();
                }
                newExaminationModalTrigger = null;
            }, 240);
        }

        window.openNewExaminationModal = openNewExaminationModal;
        window.closeNewExaminationModal = closeNewExaminationModal;

        // capture-phase examination modal close: form panels stop bubbling clicks.
        document.addEventListener('click', function (event) {
            const closeTrigger = event.target.closest('[data-examination-create-modal-close]');
            const isExplicitCloseControl = closeTrigger && closeTrigger.matches('button, a');
            const isBackdropOrShellClick = closeTrigger && event.target === closeTrigger;
            if (isExplicitCloseControl || isBackdropOrShellClick) {
                event.preventDefault();
                event.stopPropagation();
                closeNewExaminationModal();
            }
        }, true);

        document.addEventListener('click', function (event) {
            const openTrigger = event.target.closest('[data-examination-create-modal-open]');
            if (openTrigger) {
                event.preventDefault();
                openNewExaminationModal(openTrigger);
                return;
            }

            const closeTrigger = event.target.closest('[data-examination-create-modal-close]');
            if (closeTrigger) {
                event.preventDefault();
                closeNewExaminationModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }
            const modal = document.getElementById('newExaminationModal');
            if (modal && !modal.classList.contains('hidden')) {
                event.preventDefault();
                closeNewExaminationModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (<?= $shouldOpenExaminationCreateModal ? 'true' : 'false' ?>) {
                openNewExaminationModal(document.querySelector('[data-examination-create-modal-open]'));
            }
        });
    })();
    </script>
<?php endif; ?>
