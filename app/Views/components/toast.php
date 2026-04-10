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
        transform: translateY(-8px);
        opacity: 0;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .toast.show {
        transform: translateY(0);
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

    html[dir="rtl"] .toast,
    body[dir="rtl"] .toast {
        text-align: right;
    }

    html[dir="rtl"] .toast-container,
    body[dir="rtl"] .toast-container {
        inset-inline-end: auto;
        inset-inline-start: 1rem;
    }

    @media (max-width: 640px) {
        html[dir="rtl"] .toast-container,
        body[dir="rtl"] .toast-container {
            inset-inline-start: 0.75rem;
        }
    }
</style>

<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="false"></div>

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
    };

    function defaultTitleForType(type) {
        return toastI18n[type] || toastI18n.info;
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
        titleDiv.textContent = title || defaultTitleForType(normalizedType);

        const messageDiv = document.createElement('div');
        messageDiv.className = 'toast-message';
        messageDiv.textContent = message || '';

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
