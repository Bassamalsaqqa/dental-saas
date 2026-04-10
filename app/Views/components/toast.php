<!-- Toast Notification Styles -->
<style>
    .toast-container {
        position: fixed;
        top: 1rem;
        inset-inline-end: 1rem;
        z-index: 9999;
        pointer-events: none;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: min(24rem, calc(100vw - 1.5rem));
        max-width: calc(100vw - 1.5rem);
    }

    @media (max-width: 640px) {
        .toast-container {
            top: 0.75rem;
            inset-inline-end: 0.75rem;
            width: calc(100vw - 1.5rem);
            max-width: calc(100vw - 1.5rem);
        }
    }

    .toast {
        --toast-enter-x: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        width: 100%;
        padding: 0.9rem 1rem;
        border: 1px solid #e2e8f0;
        border-inline-start: 4px solid;
        border-radius: 0.85rem;
        background: #ffffff;
        box-shadow: 0 18px 40px -28px rgba(15, 23, 42, 0.35);
        pointer-events: auto;
        transform: translate3d(var(--toast-enter-x), -8px, 0);
        opacity: 0;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .toast.show {
        transform: translate3d(0, 0, 0);
        opacity: 1;
    }

    .toast.success {
        border-inline-start-color: #10b981;
        background: #f0fdf4;
    }

    .toast.error {
        border-inline-start-color: #ef4444;
        background: #fef2f2;
    }

    .toast.warning {
        border-inline-start-color: #f59e0b;
        background: #fffbeb;
    }

    .toast.info {
        border-inline-start-color: #3b82f6;
        background: #eff6ff;
    }

    .toast-icon {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6875rem;
        font-weight: 700;
        flex-shrink: 0;
        letter-spacing: 0.02em;
    }

    .toast.success .toast-icon {
        background: #10b981;
        color: #ffffff;
    }

    .toast.error .toast-icon {
        background: #ef4444;
        color: #ffffff;
    }

    .toast.warning .toast-icon {
        background: #f59e0b;
        color: #ffffff;
    }

    .toast.info .toast-icon {
        background: #3b82f6;
        color: #ffffff;
    }

    .toast-content {
        flex: 1;
        min-width: 0;
    }

    .toast-title {
        margin-bottom: 0.2rem;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.25rem;
    }

    .toast.success .toast-title {
        color: #065f46;
    }

    .toast.error .toast-title {
        color: #991b1b;
    }

    .toast.warning .toast-title {
        color: #92400e;
    }

    .toast.info .toast-title {
        color: #1e40af;
    }

    .toast-message {
        font-size: 0.8125rem;
        line-height: 1.35rem;
        color: #475569;
        word-break: break-word;
    }

    .toast-close {
        background: none;
        border: 0;
        color: #94a3b8;
        cursor: pointer;
        padding: 0.15rem;
        border-radius: 0.35rem;
        transition: color 0.2s ease, background-color 0.2s ease;
        flex-shrink: 0;
    }

    .toast-close:hover {
        color: #475569;
        background: rgba(148, 163, 184, 0.12);
    }

    .toast-container[data-dir="rtl"] .toast {
        --toast-enter-x: -1rem;
        direction: rtl;
        flex-direction: row-reverse;
        text-align: right;
    }

    .toast-container[data-dir="rtl"] .toast-title,
    .toast-container[data-dir="rtl"] .toast-message {
        text-align: right;
    }

    .toast-container[data-dir="rtl"] {
        inset-inline-end: auto;
        inset-inline-start: 1rem;
    }

    @media (max-width: 640px) {
        .toast-container[data-dir="rtl"] {
            inset-inline-start: 0.75rem;
        }
    }
</style>

<div class="toast-container" id="toastContainer" data-dir="<?= service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr' ?>" aria-live="polite" aria-atomic="false"></div>

<script>
(function() {
    const toastI18n = {
        success: <?= json_encode(lang('UI.toastSuccessTitle')) ?>,
        error: <?= json_encode(lang('UI.toastErrorTitle')) ?>,
        warning: <?= json_encode(lang('UI.toastWarningTitle')) ?>,
        info: <?= json_encode(lang('UI.toastInfoTitle')) ?>,
        validation: <?= json_encode(lang('UI.toastValidationTitle')) ?>,
        close: <?= json_encode(lang('UI.toastClose')) ?>,
        processing: <?= json_encode(lang('UI.toastProcessingTitle')) ?>,
        deleting: <?= json_encode(lang('UI.toastProcessingDelete')) ?>,
        deleteConfirm: <?= json_encode(lang('UI.toastDeleteConfirm')) ?>,
        signedIn: <?= json_encode(lang('UI.toastSignedIn')) ?>,
        signedOut: <?= json_encode(lang('UI.toastSignedOut')) ?>,
        actionCreated: <?= json_encode(lang('UI.toastActionCreated')) ?>,
        actionUpdated: <?= json_encode(lang('UI.toastActionUpdated')) ?>,
        actionDeleted: <?= json_encode(lang('UI.toastActionDeleted')) ?>,
        actionCompleted: <?= json_encode(lang('UI.toastActionCompleted')) ?>,
        actionCancelled: <?= json_encode(lang('UI.toastActionCancelled')) ?>,
        actionScheduled: <?= json_encode(lang('UI.toastActionScheduled')) ?>,
        actionSigned: <?= json_encode(lang('UI.toastActionSigned')) ?>,
        actionStarted: <?= json_encode(lang('UI.toastActionStarted')) ?>,
        actionExtended: <?= json_encode(lang('UI.toastActionExtended')) ?>,
        actionSuspended: <?= json_encode(lang('UI.toastActionSuspended')) ?>,
        actionReactivated: <?= json_encode(lang('UI.toastActionReactivated')) ?>,
        actionTerminated: <?= json_encode(lang('UI.toastActionTerminated')) ?>,
        actionMarkedPaid: <?= json_encode(lang('UI.toastActionMarkedPaid')) ?>,
        failedCreate: <?= json_encode(lang('UI.toastActionFailedCreate')) ?>,
        failedUpdate: <?= json_encode(lang('UI.toastActionFailedUpdate')) ?>,
        failedDelete: <?= json_encode(lang('UI.toastActionFailedDelete')) ?>,
        failedComplete: <?= json_encode(lang('UI.toastActionFailedComplete')) ?>,
        failedCancel: <?= json_encode(lang('UI.toastActionFailedCancel')) ?>,
        failedConfirm: <?= json_encode(lang('UI.toastActionFailedConfirm')) ?>,
        failedLoad: <?= json_encode(lang('UI.toastActionFailedLoad')) ?>,
        failedExport: <?= json_encode(lang('UI.toastActionFailedExport')) ?>,
        failedAdd: <?= json_encode(lang('UI.toastActionFailedAdd')) ?>,
        failedRemove: <?= json_encode(lang('UI.toastActionFailedRemove')) ?>,
        failedGrant: <?= json_encode(lang('UI.toastActionFailedGrant')) ?>,
        failedRevoke: <?= json_encode(lang('UI.toastActionFailedRevoke')) ?>,
        clinicSelectRequired: <?= json_encode(lang('UI.toastClinicSelectRequired')) ?>,
        patientNotFoundInClinic: <?= json_encode(lang('UI.toastPatientNotFoundInClinic')) ?>,
        doctorUnavailable: <?= json_encode(lang('UI.toastDoctorUnavailable')) ?>,
        appointmentsWorkingDaysOnly: <?= json_encode(lang('UI.toastAppointmentsWorkingDaysOnly')) ?>,
        appointmentsWorkingHoursOnly: <?= json_encode(lang('UI.toastAppointmentsWorkingHoursOnly')) ?>,
        followUpScheduled: <?= json_encode(lang('UI.toastFollowUpScheduled')) ?>,
        entityAppointment: <?= json_encode(lang('UI.toastEntityAppointment')) ?>,
        entityPatient: <?= json_encode(lang('UI.toastEntityPatient')) ?>,
        entityExamination: <?= json_encode(lang('UI.toastEntityExamination')) ?>,
        entityTreatment: <?= json_encode(lang('UI.toastEntityTreatment')) ?>,
        entityFinance: <?= json_encode(lang('UI.toastEntityFinance')) ?>,
        entityInventory: <?= json_encode(lang('UI.toastEntityInventory')) ?>,
        entityUser: <?= json_encode(lang('UI.toastEntityUser')) ?>,
        entityClinic: <?= json_encode(lang('UI.toastEntityClinic')) ?>,
        entityContract: <?= json_encode(lang('UI.toastEntityContract')) ?>,
        entityInvoice: <?= json_encode(lang('UI.toastEntityInvoice')) ?>,
        entityRole: <?= json_encode(lang('UI.toastEntityRole')) ?>,
        entityPermission: <?= json_encode(lang('UI.toastEntityPermission')) ?>,
        entityPassword: <?= json_encode(lang('UI.toastEntityPassword')) ?>,
        entityActivityLogs: <?= json_encode(lang('UI.toastEntityActivityLogs')) ?>,
    };

    function defaultTitleForType(type) {
        return toastI18n[type] || toastI18n.info;
    }

    function formatToastTemplate(template, replacements = {}) {
        return Object.entries(replacements).reduce((result, [key, value]) => {
            return result.replace(`{${key}}`, value);
        }, template);
    }

    function toastEntityLabel(entity) {
        const normalized = String(entity || '').trim().toLowerCase();
        const labels = {
            appointment: toastI18n.entityAppointment,
            'follow-up appointment': toastI18n.entityAppointment,
            patient: toastI18n.entityPatient,
            examination: toastI18n.entityExamination,
            treatment: toastI18n.entityTreatment,
            'financial transaction': toastI18n.entityFinance,
            'finance record': toastI18n.entityFinance,
            item: toastI18n.entityInventory,
            'inventory item': toastI18n.entityInventory,
            user: toastI18n.entityUser,
            clinic: toastI18n.entityClinic,
            contract: toastI18n.entityContract,
            invoice: toastI18n.entityInvoice,
            role: toastI18n.entityRole,
            permission: toastI18n.entityPermission,
            password: toastI18n.entityPassword,
            'activity logs': toastI18n.entityActivityLogs,
            'activity log': toastI18n.entityActivityLogs,
        };

        return labels[normalized] || entity;
    }

    function normalizeToastText(message) {
        const raw = String(message || '').trim();
        if (!raw) {
            return '';
        }

        const exactMap = {
            'Welcome back! You have been logged in successfully.': toastI18n.signedIn,
            'You have been logged out successfully.': toastI18n.signedOut,
            'Please select a clinic.': toastI18n.clinicSelectRequired,
            'Please select a clinic. ': toastI18n.clinicSelectRequired,
            'Selected patient was not found in this clinic': toastI18n.patientNotFoundInClinic,
            'The selected doctor is not available for this time slot': toastI18n.doctorUnavailable,
            'Appointments cannot be scheduled on non-working days': toastI18n.appointmentsWorkingDaysOnly,
            'Follow-up appointment scheduled successfully': toastI18n.followUpScheduled,
            'Appointment created successfully': formatToastTemplate(toastI18n.actionCreated, { entity: toastI18n.entityAppointment }),
            'Appointment updated successfully': formatToastTemplate(toastI18n.actionUpdated, { entity: toastI18n.entityAppointment }),
            'Appointment deleted successfully': formatToastTemplate(toastI18n.actionDeleted, { entity: toastI18n.entityAppointment }),
            'Appointment marked as completed successfully!': formatToastTemplate(toastI18n.actionCompleted, { entity: toastI18n.entityAppointment }),
            'Patient added successfully': formatToastTemplate(toastI18n.actionCreated, { entity: toastI18n.entityPatient }),
            'Patient added successfully!': formatToastTemplate(toastI18n.actionCreated, { entity: toastI18n.entityPatient }),
            'Examination created successfully': formatToastTemplate(toastI18n.actionCreated, { entity: toastI18n.entityExamination }),
            'Examination updated successfully': formatToastTemplate(toastI18n.actionUpdated, { entity: toastI18n.entityExamination }),
        };

        if (exactMap[raw]) {
            return exactMap[raw];
        }

        const workingHoursMatch = raw.match(/^Appointments can only be scheduled during working hours \((.+)\)$/i);
        if (workingHoursMatch) {
            return formatToastTemplate(toastI18n.appointmentsWorkingHoursOnly, { hours: workingHoursMatch[1] });
        }

        const patterns = [
            { regex: /^(.+?) has been created successfully!?\.?$/i, template: toastI18n.actionCreated },
            { regex: /^(.+?) created successfully!?\.?$/i, template: toastI18n.actionCreated },
            { regex: /^(.+?) has been updated successfully!?\.?$/i, template: toastI18n.actionUpdated },
            { regex: /^(.+?) updated successfully!?\.?$/i, template: toastI18n.actionUpdated },
            { regex: /^(.+?) has been deleted successfully!?\.?$/i, template: toastI18n.actionDeleted },
            { regex: /^(.+?) deleted successfully!?\.?$/i, template: toastI18n.actionDeleted },
            { regex: /^(.+?) marked as paid\.?$/i, template: toastI18n.actionMarkedPaid },
            { regex: /^(.+?) marked as completed successfully!?\.?$/i, template: toastI18n.actionCompleted },
            { regex: /^(.+?) completed successfully!?\.?$/i, template: toastI18n.actionCompleted },
            { regex: /^(.+?) cancelled successfully!?\.?$/i, template: toastI18n.actionCancelled },
            { regex: /^(.+?) signed successfully!?\.?$/i, template: toastI18n.actionSigned },
            { regex: /^(.+?) suspended\.?$/i, template: toastI18n.actionSuspended },
            { regex: /^(.+?) reactivated\.?$/i, template: toastI18n.actionReactivated },
            { regex: /^(.+?) terminated\.?$/i, template: toastI18n.actionTerminated },
            { regex: /^(.+?) extended\.?$/i, template: toastI18n.actionExtended },
            { regex: /^(.+?) started\.?$/i, template: toastI18n.actionStarted },
        ];

        for (const pattern of patterns) {
            const match = raw.match(pattern.regex);
            if (match) {
                return formatToastTemplate(pattern.template, { entity: toastEntityLabel(match[1]) });
            }
        }

        const failures = [
            { regex: /^Failed to create (.+?)(?:\.|:|$)/i, template: toastI18n.failedCreate },
            { regex: /^Failed to update (.+?)(?:\.|:|$)/i, template: toastI18n.failedUpdate },
            { regex: /^Failed to delete (.+?)(?:\.|:|$)/i, template: toastI18n.failedDelete },
            { regex: /^Failed to complete (.+?)(?:\.|:|$)/i, template: toastI18n.failedComplete },
            { regex: /^Failed to cancel (.+?)(?:\.|:|$)/i, template: toastI18n.failedCancel },
            { regex: /^Failed to confirm (.+?)(?:\.|:|$)/i, template: toastI18n.failedConfirm },
            { regex: /^Failed to load (.+?)(?:\.|:|$)/i, template: toastI18n.failedLoad },
            { regex: /^Failed to export (.+?)(?:\.|:|$)/i, template: toastI18n.failedExport },
            { regex: /^Failed to add (.+?)(?:\.|:|$)/i, template: toastI18n.failedAdd },
            { regex: /^Failed to remove (.+?)(?:\.|:|$)/i, template: toastI18n.failedRemove },
            { regex: /^Failed to grant (.+?)(?:\.|:|$)/i, template: toastI18n.failedGrant },
            { regex: /^Failed to revoke (.+?)(?:\.|:|$)/i, template: toastI18n.failedRevoke },
        ];

        for (const failure of failures) {
            const match = raw.match(failure.regex);
            if (match) {
                return formatToastTemplate(failure.template, { entity: toastEntityLabel(match[1]) });
            }
        }

        return raw;
    }

    function iconForType(type) {
        switch (type) {
            case 'success':
                return 'OK';
            case 'error':
                return 'ER';
            case 'warning':
                return '!';
            default:
                return 'i';
        }
    }

    window.showToast = function(type, title, message, duration = 5000) {
        const container = document.getElementById('toastContainer');
        if (!container) {
            return;
        }

        const normalizedType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
        let resolvedTitle = title;
        let resolvedMessage = message;

        if ((resolvedMessage === undefined || resolvedMessage === null || resolvedMessage === '') && resolvedTitle) {
            resolvedMessage = resolvedTitle;
            resolvedTitle = '';
        }

        const normalizedMessage = normalizeToastText(resolvedMessage);
        const toast = document.createElement('div');
        toast.className = `toast ${normalizedType}`;
        toast.setAttribute('role', normalizedType === 'error' || normalizedType === 'warning' ? 'alert' : 'status');
        toast.setAttribute('aria-live', normalizedType === 'error' || normalizedType === 'warning' ? 'assertive' : 'polite');
        toast.setAttribute('aria-atomic', 'true');

        const iconDiv = document.createElement('div');
        iconDiv.className = 'toast-icon';
        iconDiv.textContent = iconForType(normalizedType);

        const contentDiv = document.createElement('div');
        contentDiv.className = 'toast-content';

        const titleDiv = document.createElement('div');
        titleDiv.className = 'toast-title';
        titleDiv.textContent = resolvedTitle || defaultTitleForType(normalizedType);

        const messageDiv = document.createElement('div');
        messageDiv.className = 'toast-message';
        messageDiv.textContent = normalizedMessage;

        contentDiv.appendChild(titleDiv);
        contentDiv.appendChild(messageDiv);

        const closeBtn = document.createElement('button');
        closeBtn.className = 'toast-close';
        closeBtn.type = 'button';
        closeBtn.setAttribute('aria-label', toastI18n.close);
        closeBtn.addEventListener('click', function() {
            window.closeToast(closeBtn);
        });

        const closeIcon = document.createElement('i');
        closeIcon.className = 'fas fa-times';
        closeIcon.setAttribute('aria-hidden', 'true');
        closeBtn.appendChild(closeIcon);

        toast.appendChild(iconDiv);
        toast.appendChild(contentDiv);
        toast.appendChild(closeBtn);
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        if (duration > 0) {
            setTimeout(() => {
                if (toast.parentNode) {
                    window.closeToast(closeBtn);
                }
            }, duration);
        }
    };

    window.closeToast = function(closeButton) {
        const toast = closeButton.closest('.toast');
        if (!toast) {
            return;
        }

        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 200);
    };

    document.addEventListener('DOMContentLoaded', function() {
        <?php if (session()->getFlashdata('success')): ?>
            window.showToast('success', toastI18n.success, '<?= addslashes(session()->getFlashdata('success')) ?>');
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            window.showToast('error', toastI18n.error, '<?= addslashes(session()->getFlashdata('error')) ?>');
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            window.showToast('warning', toastI18n.warning, '<?= addslashes(session()->getFlashdata('warning')) ?>');
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            window.showToast('info', toastI18n.info, '<?= addslashes(session()->getFlashdata('info')) ?>');
        <?php endif; ?>

        <?php if (session()->getFlashdata('validation')): ?>
            const validation = <?= json_encode(session()->getFlashdata('validation')) ?>;
            if (validation && validation.errors) {
                const errors = Object.values(validation.errors).join(', ');
                window.showToast('error', toastI18n.validation, errors, 7000);
            }
        <?php endif; ?>
    });

    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');

        forms.forEach((form) => {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (!submitBtn) {
                    return;
                }

                if (!submitBtn.hasOwnProperty('_originalChildren')) {
                    submitBtn._originalChildren = Array.from(submitBtn.childNodes).map((node) => node.cloneNode(true));
                }

                const loadingIcon = document.createElement('i');
                loadingIcon.className = 'fas fa-spinner fa-spin';
                loadingIcon.setAttribute('aria-hidden', 'true');
                if (document.documentElement.dir === 'rtl') {
                    loadingIcon.classList.add('ml-2');
                } else {
                    loadingIcon.classList.add('mr-2');
                }

                submitBtn.replaceChildren(loadingIcon, document.createTextNode(toastI18n.processing + '...'));
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.replaceChildren(...submitBtn._originalChildren.map((node) => node.cloneNode(true)));
                    submitBtn.disabled = false;
                }, 5000);
            });
        });
    });

    window.confirmDelete = function(url, message = toastI18n.deleteConfirm) {
        if (confirm(message)) {
            window.showToast('info', toastI18n.processing, toastI18n.deleting, 3000);

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrfName = document.querySelector('meta[name="csrf-name"]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfName && csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = csrfName.getAttribute('content');
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    };

    window.alert = function(message) {
        window.showToast('info', toastI18n.info, String(message || ''), 7000);
    };
})();
</script>
