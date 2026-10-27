(function () {
    const openStack = [];
    const triggerMap = new Map();

    function getDrawer(idOrElement) {
        if (!idOrElement) return null;
        return typeof idOrElement === 'string' ? document.getElementById(idOrElement) : idOrElement;
    }

    function getFocusable(root) {
        return Array.from(root.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
            .filter((element) => element.offsetParent !== null || element === document.activeElement);
    }

    function setBodyLock() {
        document.documentElement.classList.toggle('app-drawer-lock', openStack.length > 0);
        document.body.classList.toggle('app-drawer-lock', openStack.length > 0);
    }

    function ensureDrawerPortal(drawer) {
        if (!drawer || drawer.parentElement === document.body) return;
        document.body.appendChild(drawer);
    }

    function syncDrawerDirection(drawer) {
        const direction = document.documentElement.getAttribute('dir') || document.body.getAttribute('dir') || 'ltr';
        const normalized = direction === 'rtl' ? 'rtl' : 'ltr';
        drawer.setAttribute('dir', normalized);
        drawer.classList.toggle('app-drawer--rtl', normalized === 'rtl');
        drawer.classList.toggle('app-drawer--ltr', normalized !== 'rtl');
    }

    function openDrawer(id, trigger) {
        const drawer = getDrawer(id);
        if (!drawer || drawer.classList.contains('is-open')) return;

        ensureDrawerPortal(drawer);
        syncDrawerDirection(drawer);
        triggerMap.set(drawer.id, trigger || document.activeElement);
        drawer.setAttribute('aria-hidden', 'false');
        drawer.classList.remove('is-settled');
        drawer.classList.remove('hidden');
        requestAnimationFrame(() => { if (openStack.includes(drawer.id)) drawer.classList.add('is-open'); });
        openStack.push(drawer.id);
        setBodyLock();

        window.setTimeout(() => {
            if (!openStack.includes(drawer.id)) return;
            if (typeof window.initAppSelects === 'function') {
                window.initAppSelects(drawer);
            }
            if (typeof window.initializeClinicDateInputs === 'function') {
                window.initializeClinicDateInputs(drawer);
            }
            if (typeof window.initializeClinicTimeInputs === 'function') {
                window.initializeClinicTimeInputs(drawer);
            }
            if (typeof window.initContextualAppointmentForms === 'function') {
                window.initContextualAppointmentForms(drawer);
            }
            if (typeof window.initContextualTreatmentForms === 'function') {
                window.initContextualTreatmentForms(drawer);
            }
            if (typeof window.initContextualExaminationForms === 'function') {
                window.initContextualExaminationForms(drawer);
            }
            if (typeof window.initFinanceCalculators === 'function') {
                window.initFinanceCalculators(drawer);
            }

            const focusable = getFocusable(drawer);
            (focusable.find((el) => ['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) || focusable[0])?.focus();
        }, 80);

        window.setTimeout(() => {
            if (drawer.classList.contains('is-open')) {
                drawer.classList.add('is-settled');
            }
        }, 240);
    }

    function closeDrawer(id) {
        const drawer = getDrawer(id);
        if (!drawer || !openStack.includes(drawer.id)) return;

        drawer.classList.remove('is-open');
        drawer.classList.remove('is-settled');
        openStack.splice(openStack.lastIndexOf(drawer.id), 1);
        setBodyLock();

        window.setTimeout(() => {
            if (openStack.includes(drawer.id)) return;
            drawer.setAttribute('aria-hidden', 'true');
            drawer.classList.add('hidden');
            const trigger = triggerMap.get(drawer.id);
            triggerMap.delete(drawer.id);
            const restoreTarget = trigger?.getClientRects().length ? trigger : trigger?.closest('details')?.querySelector('summary');
            restoreTarget?.focus();
        }, 230);
    }

    function closeTopDrawer() {
        const id = openStack[openStack.length - 1];
        if (id) closeDrawer(id);
    }

    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-app-drawer-open]');
        if (opener) {
            event.preventDefault();
            openDrawer(opener.getAttribute('data-app-drawer-open'), opener);
            return;
        }

        const closer = event.target.closest('[data-app-drawer-close]');
        if (closer) {
            event.preventDefault();
            closeDrawer(closer.getAttribute('data-app-drawer-close'));
            return;
        }

        const shell = event.target.closest('.app-drawer__shell');
        if (shell && !event.target.closest('.app-drawer__panel')) {
            event.preventDefault();
            closeDrawer(shell.closest('.app-drawer')?.id);
        }
    });

    document.addEventListener('keydown', function (event) {
        const id = openStack[openStack.length - 1];
        if (!id) return;
        const drawer = getDrawer(id);
        if (!drawer) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeTopDrawer();
            return;
        }

        if (event.key !== 'Tab') return;
        const focusable = getFocusable(drawer);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    window.AppDrawer = {
        open: openDrawer,
        close: closeDrawer,
        closeTop: closeTopDrawer
    };
})();
