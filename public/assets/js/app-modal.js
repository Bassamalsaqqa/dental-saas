(function () {
    if (window.AppModal) {
        return;
    }

    const activeModals = new Set();
    const timers = new WeakMap();
    const triggers = new WeakMap();

    function setScrollLock() {
        const hasOpen = activeModals.size > 0;
        document.documentElement.classList.toggle('app-drawer-lock', hasOpen);
        document.body.classList.toggle('app-drawer-lock', hasOpen);
    }

    function getModal(idOrElement) {
        if (!idOrElement) {
            return null;
        }
        if (idOrElement instanceof Element) {
            return idOrElement;
        }
        return document.getElementById(String(idOrElement));
    }

    function isOpen(modal) {
        return !!modal && !modal.classList.contains('hidden');
    }

    function mount(modal) {
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        return modal;
    }

    function focusFirst(modal, selector) {
        const target = modal.querySelector(selector || 'input:not([type="hidden"]), select, textarea, button, a[href]');
        if (target && typeof target.focus === 'function') {
            target.focus({ preventScroll: true });
        }
    }

    function open(idOrElement, options) {
        const opts = options || {};
        const modal = mount(getModal(idOrElement));
        if (!modal) {
            return null;
        }

        const closeTimer = timers.get(modal);
        if (closeTimer) {
            clearTimeout(closeTimer);
            timers.delete(modal);
        }

        triggers.set(modal, opts.trigger || document.activeElement || null);
        if (typeof opts.beforeOpen === 'function') {
            opts.beforeOpen(modal);
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        activeModals.add(modal);
        setScrollLock();

        requestAnimationFrame(function () {
            modal.classList.add('is-open');
        });

        window.setTimeout(function () {
            if (typeof opts.onOpen === 'function') {
                opts.onOpen(modal);
            }
            focusFirst(modal, opts.focusSelector);
        }, opts.openDelay || 80);

        return modal;
    }

    function close(idOrElement, options) {
        const opts = options || {};
        const modal = getModal(idOrElement);
        if (!isOpen(modal)) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        activeModals.delete(modal);
        setScrollLock();

        const timer = window.setTimeout(function () {
            if (typeof opts.onReset === 'function') {
                opts.onReset(modal);
            }
            modal.classList.add('hidden');
            const trigger = triggers.get(modal);
            if (trigger && typeof trigger.focus === 'function') {
                trigger.focus();
            }
            triggers.delete(modal);
            timers.delete(modal);
        }, opts.closeDelay || 240);
        timers.set(modal, timer);
    }

    function bind(options) {
        const opts = options || {};
        const id = opts.id;
        const modal = getModal(id);
        if (!modal || modal.dataset.appModalBound === '1') {
            return modal;
        }
        modal.dataset.appModalBound = '1';

        document.addEventListener('click', function (event) {
            const openTrigger = opts.openSelector ? event.target.closest(opts.openSelector) : null;
            if (openTrigger) {
                event.preventDefault();
                open(id, Object.assign({}, opts, { trigger: openTrigger }));
                return;
            }

            const closeTrigger = opts.closeSelector ? event.target.closest(opts.closeSelector) : null;
            if (!closeTrigger) {
                return;
            }

            const isExplicitCloseControl = closeTrigger.matches('button, a');
            const isBackdropOrShellClick = event.target === closeTrigger;
            if (!isExplicitCloseControl && !isBackdropOrShellClick) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            close(id, opts);
        }, true);

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape' || !isOpen(modal)) {
                return;
            }
            event.preventDefault();
            close(id, opts);
        });

        return modal;
    }

    window.AppModal = {
        bind: bind,
        open: open,
        close: close,
        isOpen: isOpen,
    };
})();