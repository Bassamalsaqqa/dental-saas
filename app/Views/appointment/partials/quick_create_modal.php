<?php
$modalReturnTo = (string) ($return_to ?? 'appointment');
$modalSelectedPatient = $selected_patient ?? null;
$modalSelectedExaminationId = $examination_id ?? null;
$modalClinicalProcedureId = $clinical_procedure_id ?? null;
$modalContextual = !empty($contextual);
$modalShouldOpen = old('source_modal') === 'appointment_quick_create';
$modalFullPageQuery = ['return_to' => $modalReturnTo];
if (!empty($modalSelectedPatient['id'])) {
    $modalFullPageQuery['patient_id'] = (int) $modalSelectedPatient['id'];
}
if (!empty($modalSelectedExaminationId)) {
    $modalFullPageQuery['examination_id'] = (int) $modalSelectedExaminationId;
}
if (!empty($modalClinicalProcedureId)) {
    $modalFullPageQuery['clinical_procedure_id'] = (int) $modalClinicalProcedureId;
}
if (!empty($appointment_type_prefill)) {
    $modalFullPageQuery['appointment_type'] = (string) $appointment_type_prefill;
}
if (!empty($provider_id_prefill)) {
    $modalFullPageQuery['provider_user_id'] = (int) $provider_id_prefill;
}
$modalFullPageHref = base_url('appointment/create?' . http_build_query($modalFullPageQuery));
?>
<?php if (has_permission('appointments', 'create')): ?>
    <style>
    .appointment-create-modal {
        visibility: hidden;
        pointer-events: none;
        transition: visibility 0s linear 240ms;
    }
    .appointment-create-modal.is-open {
        visibility: visible;
        pointer-events: auto;
        transition-delay: 0s;
    }
    .appointment-create-modal__backdrop {
        opacity: 0;
        transition: opacity 220ms ease;
    }
    .appointment-create-modal.is-open .appointment-create-modal__backdrop {
        opacity: 1;
    }
    .appointment-create-modal__panel {
        display: grid !important;
        grid-template-rows: auto minmax(0, 1fr) auto;
        opacity: 0;
        overflow: clip;
        transform: scale(0.86);
        transform-origin: center center;
        height: min(92dvh, 48rem);
        transition:
            opacity 180ms ease,
            transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, opacity;
    }
    .appointment-create-modal.is-open .appointment-create-modal__panel {
        opacity: 1;
        transform: scale(1);
    }
    .appointment-create-modal .select2-container {
        width: 100% !important;
    }
    @media (prefers-reduced-motion: reduce) {
        .appointment-create-modal__backdrop,
        .appointment-create-modal__panel {
            transition: none;
        }
    }
    @media (max-width: 640px) {
        .appointment-create-modal__shell {
            align-items: stretch;
            padding: 0;
        }
        .appointment-create-modal__panel {
            width: 100vw;
            height: 100dvh;
            min-height: 100dvh;
            max-height: 100dvh;
            border-radius: 0;
        }
    }
    </style>

    <div id="newAppointmentModal" class="app-scale-modal appointment-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newAppointmentModalTitle" dir="<?= !empty($isRtl) ? 'rtl' : 'ltr' ?>">
        <div class="appointment-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-appointment-create-modal-close></div>
        <div class="appointment-create-modal__shell relative z-10 flex h-full items-center justify-center p-4" data-appointment-create-modal-close>
            <form id="newAppointmentQuickCreateForm" action="<?= base_url('appointment/store') ?>" method="POST" class="appointment-create-modal__panel flex w-full max-w-4xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="appointment_quick_create">
                <?php if (!empty($modalClinicalProcedureId)): ?>
                    <input type="hidden" name="clinical_procedure_id" value="<?= (int) $modalClinicalProcedureId ?>">
                <?php endif; ?>
                <?php if (!empty($modalSelectedExaminationId)): ?>
                    <input type="hidden" name="examination_id" value="<?= (int) $modalSelectedExaminationId ?>">
                <?php endif; ?>

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div>
                            <h2 id="newAppointmentModalTitle" class="text-lg font-bold text-slate-900"><?= esc(lang('UI.appointmentNew')) ?></h2>
                            <a href="<?= esc($modalFullPageHref, 'attr') ?>" class="mt-1 inline-flex text-xs font-semibold text-blue-700 hover:text-blue-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-appointment-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-5">
                    <?php if (session('error') && $modalShouldOpen): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <?= view('appointment/partials/form_fields', [
                            'contextual' => $modalContextual,
                            'selected_patient' => $modalSelectedPatient,
                            'chair_options' => $chair_options ?? [],
                            'booking_context' => $booking_context ?? null,
                            'provider_id_prefill' => $provider_id_prefill ?? null,
                            'requested_date' => old('appointment_date', $requested_date ?? date('Y-m-d')),
                            'appointment_type_prefill' => old('appointment_type', $appointment_type_prefill ?? ''),
                            'field_id_prefix' => 'quick_modal_',
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-appointment-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('UI.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-blue-700 hover:to-purple-700 hover:shadow-xl">
                        <i class="fas fa-calendar-check me-2"></i>
                        <?= esc(lang('UI.appointmentSchedule')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        if (window.__appointmentQuickCreateModalBound) {
            return;
        }
        window.__appointmentQuickCreateModalBound = true;

        let appointmentModalTrigger = null;
        let appointmentModalCloseTimer = null;

        function mountAppointmentModal() {
            const modal = document.getElementById('newAppointmentModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (modal && modal.dataset.closeControlsBound !== '1') {
                modal.dataset.closeControlsBound = '1';
                modal.querySelectorAll('button[data-appointment-create-modal-close], a[data-appointment-create-modal-close]').forEach(function (control) {
                    control.addEventListener('click', function (event) {
                        event.preventDefault();
                        closeAppointmentModal();
                    });
                });
            }
            return modal;
        }

        function initializeAppointmentModalContent(modal) {
            if (!modal) {
                return;
            }
            if (typeof window.initAppSelects === 'function') {
                window.initAppSelects(modal);
            }
            if (typeof window.initializeClinicDateInputs === 'function') {
                window.initializeClinicDateInputs(modal);
            }
            if (typeof window.initContextualAppointmentForms === 'function') {
                window.initContextualAppointmentForms(modal);
            }
        }

        function dispatchFieldChange(field) {
            if (!field) {
                return;
            }
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.jQuery && window.jQuery(field).data('select2')) {
                window.jQuery(field).trigger('change.select2');
            }
        }

        function setFieldValue(form, selector, value) {
            if (!form || value === null || value === undefined || value === '') {
                return;
            }
            const field = form.querySelector(selector);
            if (!field) {
                return;
            }
            field.value = value;
            dispatchFieldChange(field);
        }

        function splitAppointmentDateTime(rawValue) {
            const raw = String(rawValue || '').trim();
            if (!raw) {
                return { date: '', time: '' };
            }
            const parts = raw.split('T');
            const date = (parts[0] || '').slice(0, 10);
            const time = parts[1] ? parts[1].slice(0, 5) : '';
            return { date: date, time: time };
        }

        function collectTriggerDefaults(trigger) {
            if (!trigger) {
                return {};
            }
            let params = new URLSearchParams();
            const href = trigger.getAttribute('href');
            if (href) {
                try {
                    params = new URL(href, window.location.origin).searchParams;
                } catch (error) {
                    params = new URLSearchParams();
                }
            }

            const dateTime = splitAppointmentDateTime(
                trigger.dataset.appointmentDateTime
                || params.get('appointment_datetime')
                || params.get('date')
            );

            return {
                date: trigger.dataset.appointmentDate || params.get('appointment_date') || dateTime.date,
                time: trigger.dataset.appointmentTime || params.get('appointment_time') || dateTime.time,
                duration: trigger.dataset.appointmentDuration || params.get('duration'),
                chair: trigger.dataset.appointmentChair || params.get('chair_name'),
                providerId: trigger.dataset.appointmentProvider || params.get('provider_user_id') || params.get('provider_id'),
                type: trigger.dataset.appointmentType || params.get('appointment_type'),
                patientId: trigger.dataset.patientId || params.get('patient_id'),
                clinicalProcedureId: trigger.dataset.clinicalProcedureId || params.get('clinical_procedure_id'),
            };
        }

        function applyAppointmentTriggerDefaults(modal, trigger) {
            const form = modal?.querySelector('#newAppointmentQuickCreateForm');
            const wrap = form?.querySelector('[data-contextual-appointment-form]');
            if (!form || !wrap || !trigger) {
                return;
            }

            const defaults = collectTriggerDefaults(trigger);
            setFieldValue(form, '[name="appointment_date"]', defaults.date);
            setFieldValue(form, '[name="duration"]', defaults.duration);
            setFieldValue(form, '[name="provider_user_id"]', defaults.providerId);
            setFieldValue(form, '[name="chair_name"]', defaults.chair);
            setFieldValue(form, '[name="appointment_type"]', defaults.type);
            setFieldValue(form, '[name="patient_id"]', defaults.patientId);
            setFieldValue(form, '[name="clinical_procedure_id"]', defaults.clinicalProcedureId);

            if (defaults.time) {
                wrap.dataset.prefillAppointmentTime = defaults.time;
            } else {
                delete wrap.dataset.prefillAppointmentTime;
            }

            const fullPageLink = modal.querySelector('a[href*="appointment/create"]');
            if (fullPageLink) {
                fullPageLink.dataset.baseHref = fullPageLink.dataset.baseHref || fullPageLink.href;
                const fullPageUrl = new URL(fullPageLink.dataset.baseHref, window.location.origin);
                if (defaults.date) {
                    fullPageUrl.searchParams.set('date', defaults.date);
                }
                if (defaults.time) {
                    fullPageUrl.searchParams.set('appointment_time', defaults.time);
                }
                if (defaults.duration) {
                    fullPageUrl.searchParams.set('duration', defaults.duration);
                }
                if (defaults.providerId) {
                    fullPageUrl.searchParams.set('provider_user_id', defaults.providerId);
                }
                if (defaults.chair) {
                    fullPageUrl.searchParams.set('chair_name', defaults.chair);
                }
                if (defaults.type) {
                    fullPageUrl.searchParams.set('appointment_type', defaults.type);
                }
                if (defaults.patientId) {
                    fullPageUrl.searchParams.set('patient_id', defaults.patientId);
                }
                if (defaults.clinicalProcedureId) {
                    fullPageUrl.searchParams.set('clinical_procedure_id', defaults.clinicalProcedureId);
                }
                fullPageLink.href = fullPageUrl.toString();
            }
        }

        function resetAppointmentModalForm(modal) {
            const form = modal ? modal.querySelector('#newAppointmentQuickCreateForm') : null;
            if (!form) {
                return;
            }

            form.reset();
            form.querySelectorAll('[data-contextual-appointment-form]').forEach(function (wrap) {
                delete wrap.dataset.prefillAppointmentTime;
            });
            form.querySelectorAll('select, input, textarea').forEach(function (field) {
                dispatchFieldChange(field);
            });
            const fullPageLink = modal.querySelector('a[href*="appointment/create"]');
            if (fullPageLink && fullPageLink.dataset.baseHref) {
                fullPageLink.href = fullPageLink.dataset.baseHref;
            }
            if (typeof window.initContextualAppointmentForms === 'function') {
                window.initContextualAppointmentForms(modal);
            }
        }

        function openAppointmentModal(trigger) {
            const modal = mountAppointmentModal();
            if (!modal) {
                return;
            }
            appointmentModalTrigger = trigger || document.activeElement;
            if (appointmentModalCloseTimer) {
                clearTimeout(appointmentModalCloseTimer);
                appointmentModalCloseTimer = null;
            }
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            applyAppointmentTriggerDefaults(modal, trigger);
            document.documentElement.classList.add('app-drawer-lock');
            document.body.classList.add('app-drawer-lock');
            requestAnimationFrame(function () {
                modal.classList.add('is-open');
            });
            window.setTimeout(function () {
                initializeAppointmentModalContent(modal);
                const firstField = modal.querySelector('#patientSelect, select[name="patient_id"], input, select, textarea, button');
                if (firstField && typeof firstField.focus === 'function') {
                    firstField.focus({ preventScroll: true });
                }
            }, 100);
        }

        function closeAppointmentModal() {
            const modal = document.getElementById('newAppointmentModal');
            if (!modal || modal.classList.contains('hidden')) {
                return;
            }
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('app-drawer-lock');
            document.body.classList.remove('app-drawer-lock');
            appointmentModalCloseTimer = setTimeout(function () {
                modal.classList.add('hidden');
                resetAppointmentModalForm(modal);
                if (appointmentModalTrigger && typeof appointmentModalTrigger.focus === 'function') {
                    appointmentModalTrigger.focus();
                }
                appointmentModalTrigger = null;
            }, 240);
        }

        window.openAppointmentModal = openAppointmentModal;
        window.closeAppointmentModal = closeAppointmentModal;

        document.addEventListener('click', function (event) {
            const openTrigger = event.target.closest('[data-appointment-create-modal-open]');
            if (openTrigger) {
                event.preventDefault();
                openAppointmentModal(openTrigger);
                return;
            }

            const closeTrigger = event.target.closest('[data-appointment-create-modal-close]');
            if (closeTrigger) {
                event.preventDefault();
                closeAppointmentModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }
            const modal = document.getElementById('newAppointmentModal');
            if (modal && !modal.classList.contains('hidden')) {
                event.preventDefault();
                closeAppointmentModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (<?= $modalShouldOpen ? 'true' : 'false' ?>) {
                openAppointmentModal(document.getElementById('newAppointmentModalTrigger'));
            }
        });
    })();
    </script>
<?php endif; ?>
