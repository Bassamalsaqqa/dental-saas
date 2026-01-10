<!-- Toast Notification Styles -->
<style>
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        pointer-events: none;
    }
    
    .toast {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 16px 20px;
        margin-bottom: 12px;
        min-width: 300px;
        max-width: 400px;
        pointer-events: auto;
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .toast.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .toast.success {
        border-left-color: #10b981;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
    }
    
    .toast.error {
        border-left-color: #ef4444;
        background: linear-gradient(135deg, #fef2f2 0%, #fef7f7 100%);
    }
    
    .toast.warning {
        border-left-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    
    .toast.info {
        border-left-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }
    
    .toast-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }
    
    .toast.success .toast-icon {
        background: #10b981;
        color: white;
    }
    
    .toast.error .toast-icon {
        background: #ef4444;
        color: white;
    }
    
    .toast.warning .toast-icon {
        background: #f59e0b;
        color: white;
    }
    
    .toast.info .toast-icon {
        background: #3b82f6;
        color: white;
    }
    
    .toast-content {
        flex: 1;
    }
    
    .toast-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
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
        font-size: 13px;
        color: #6b7280;
    }
    
    .toast-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: color 0.2s;
    }
    
    .toast-close:hover {
        color: #6b7280;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Toast Notification JavaScript -->
<script>
(function() {
    /**
     * Toast notification system (Hardened)
     */
    window.showToast = function(type, title, message, duration = 5000) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        // Get appropriate icon
        let iconChar = 'ℹ';
        switch(type) {
            case 'success': iconChar = '✓'; break;
            case 'error':   iconChar = '✕'; break;
            case 'warning': iconChar = '⚠'; break;
            case 'info':    iconChar = 'ℹ'; break;
        }
        
        // Icon element
        const iconDiv = document.createElement('div');
        iconDiv.className = 'toast-icon';
        iconDiv.textContent = iconChar;
        
        // Content element
        const contentDiv = document.createElement('div');
        contentDiv.className = 'toast-content';
        
        const titleDiv = document.createElement('div');
        titleDiv.className = 'toast-title';
        titleDiv.textContent = title;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'toast-message';
        messageDiv.textContent = message;
        
        contentDiv.appendChild(titleDiv);
        contentDiv.appendChild(messageDiv);
        
        // Close button
        const closeBtn = document.createElement('button');
        closeBtn.className = 'toast-close';
        closeBtn.onclick = function() { window.closeToast(this); };
        
        const closeIcon = document.createElement('i');
        closeIcon.className = 'fas fa-times';
        closeBtn.appendChild(closeIcon);
        
        // Assemble toast
        toast.appendChild(iconDiv);
        toast.appendChild(contentDiv);
        toast.appendChild(closeBtn);
        
        // Add to container
        container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Auto remove after duration
        setTimeout(() => {
            if (toast.parentNode) {
                window.closeToast(closeBtn);
            }
        }, duration);
    };
    
    window.closeToast = function(closeButton) {
        const toast = closeButton.closest('.toast');
        if (!toast) return;
        
        toast.classList.remove('show');
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    };
    
    // Show flash messages as toasts
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (session()->getFlashdata('success')): ?>
            window.showToast('success', 'Success!', '<?= addslashes(session()->getFlashdata('success')) ?>');
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            window.showToast('error', 'Error!', '<?= addslashes(session()->getFlashdata('error')) ?>');
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('warning')): ?>
            window.showToast('warning', 'Warning!', '<?= addslashes(session()->getFlashdata('warning')) ?>');
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('info')): ?>
            window.showToast('info', 'Info!', '<?= addslashes(session()->getFlashdata('info')) ?>');
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('validation')): ?>
            const validation = <?= json_encode(session()->getFlashdata('validation')) ?>;
            if (validation && validation.errors) {
                const errors = Object.values(validation.errors).join(', ');
                window.showToast('error', 'Validation Error', errors);
            }
        <?php endif; ?>
    });
    
    // Form submission with loading state
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    // Cache original children once if not already cached
                    if (!submitBtn.hasOwnProperty('_originalChildren')) {
                        submitBtn._originalChildren = Array.from(submitBtn.childNodes).map(node => node.cloneNode(true));
                    }

                    // Add loading state
                    const loadingIcon = document.createElement('i');
                    loadingIcon.className = 'fas fa-spinner fa-spin mr-2';
                    submitBtn.replaceChildren(loadingIcon, document.createTextNode('Processing...'));
                    submitBtn.disabled = true;
                    
                    // Re-enable after 5 seconds (in case of network issues)
                    setTimeout(() => {
                        submitBtn.replaceChildren(...submitBtn._originalChildren.map(node => node.cloneNode(true)));
                        submitBtn.disabled = false;
                    }, 5000);
                }
            });
        });
    });
    
    // Delete confirmation with toast
    window.confirmDelete = function(url, message = 'Are you sure you want to delete this item?') {
        if (confirm(message)) {
            // Show loading toast
            window.showToast('info', 'Processing...', 'Deleting item, please wait...', 3000);
            
            // Create form for DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_test_name';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }
            
            // Add method override for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    };
})();
</script>