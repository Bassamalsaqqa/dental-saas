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
<div id="newAppointmentModal" class="appointment-create-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newAppointmentModalTitle" dir="<?= !empty($isRtl) ? 'rtl' : 'ltr' ?>">
        <div class="appointment-create-modal__backdrop" data-appointment-create-modal-close></div>
        <div class="appointment-create-modal__shell" data-appointment-create-modal-close>
            <form id="newAppointmentQuickCreateForm" action="<?= base_url('appointment/store') ?>" method="POST" class="appointment-create-modal__panel v4-form" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="appointment_quick_create">
                <?php if (!empty($modalClinicalProcedureId)): ?>
                    <input type="hidden" name="clinical_procedure_id" value="<?= (int) $modalClinicalProcedureId ?>">
                <?php endif; ?>
                <?php if (!empty($modalSelectedExaminationId)): ?>
                    <input type="hidden" name="examination_id" value="<?= (int) $modalSelectedExaminationId ?>">
                <?php endif; ?>

                <header class="v4-modal-header"><div><h2 id="newAppointmentModalTitle"><?= esc(lang('UI.appointmentNew')) ?></h2><a class="v4-text-link" href="<?= esc($modalFullPageHref,'attr') ?>"><?= esc(lang('UI.openFullPage')) ?></a></div><button type="button" class="v4-icon-button" data-appointment-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
                <div class="v4-modal-body">
                    <?php if (session('error') && $modalShouldOpen): ?>
                        <div class="v4-form-error">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="v4-booking-fields">
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

                <footer class="v4-modal-footer"><button type="button" data-appointment-create-modal-close class="v4-button"><?= esc(lang('UI.cancel')) ?></button><button type="submit" class="v4-button v4-button--primary"><i class="fas fa-calendar-check" aria-hidden="true"></i><?= esc(lang('UI.appointmentSchedule')) ?></button></footer>
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
                        event.stopPropagation();
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

        let initialBooking='';
        const bookingState=modal=>JSON.stringify([...new FormData(modal.querySelector('form'))].filter(([key])=>key!==document.querySelector('meta[name="csrf-name"]')?.content));
        function openAppointmentModal(trigger) {
            const modal=mountAppointmentModal(); if(!modal) return;
            applyAppointmentTriggerDefaults(modal,trigger);
            initialBooking=bookingState(modal);
            window.AppModal.open(modal,{trigger:trigger||document.activeElement,focusSelector:'.select2-selection, input:not([type="hidden"]):not([disabled]), button',openDelay:100,onOpen:()=>initializeAppointmentModalContent(modal)});
        }
        function closeAppointmentModal() {
            const modal=document.getElementById('newAppointmentModal');
            if(!window.AppModal.isOpen(modal)) return;
            if(initialBooking&&bookingState(modal)!==initialBooking&&!window.confirm(<?= json_encode(lang('V4.discardBooking'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)) return;
            window.AppModal.close(modal,{onReset:()=>resetAppointmentModalForm(modal)});
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
            if (event.target.closest?.('.select2-search')) return;
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
