(function () {
    if (window.__workflowNavigationContextBound) {
        return;
    }
    window.__workflowNavigationContextBound = true;

    const workflowPattern = /\/(create|edit)(\?|\/|$)/i;
    const ignoredSchemes = /^(mailto:|tel:|javascript:|#)/i;

    function currentTarget() {
        const path = window.location.pathname.replace(/^\/+/, '');
        const base = window.appBaseUrl || document.querySelector('base[href]')?.getAttribute('href') || '';
        let normalized = path;
        try {
            const basePath = new URL(base || window.location.origin, window.location.origin).pathname.replace(/^\/+|\/+$/g, '');
            if (basePath && normalized.startsWith(basePath + '/')) {
                normalized = normalized.slice(basePath.length + 1);
            } else if (basePath && normalized === basePath) {
                normalized = '';
            }
        } catch (error) {}

        return normalized + window.location.search;
    }

    function parseInternalUrl(href) {
        if (!href || ignoredSchemes.test(href)) {
            return null;
        }
        try {
            const url = new URL(href, window.location.origin);
            return url.origin === window.location.origin ? url : null;
        } catch (error) {
            return null;
        }
    }

    function withCurrentContext(href) {
        const url = parseInternalUrl(href);
        if (!url || url.searchParams.has('return_to')) {
            return href;
        }
        const target = currentTarget();
        const linkTarget = url.pathname.replace(/^\/+/, '') + url.search;
        if (!target || linkTarget === target) {
            return href;
        }
        url.searchParams.set('return_to', target);
        return url.toString();
    }

    function shouldEnhance(anchor) {
        if (!anchor || anchor.dataset.noWorkflowContext === '1') {
            return false;
        }
        const url = parseInternalUrl(anchor.getAttribute('href') || '');
        if (!url || url.searchParams.has('return_to')) {
            return false;
        }
        return anchor.dataset.workflowContext === '1' || workflowPattern.test(url.pathname);
    }

    function enhanceAnchor(anchor) {
        if (!shouldEnhance(anchor)) {
            return;
        }
        anchor.setAttribute('href', withCurrentContext(anchor.getAttribute('href')));
    }

    function enhanceAll(root) {
        const scope = root || document;
        scope.querySelectorAll?.('a[href]').forEach(enhanceAnchor);
    }

    document.addEventListener('click', function (event) {
        const anchor = event.target.closest('a[href]');
        if (anchor) {
            enhanceAnchor(anchor);
        }
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        enhanceAll(document);
    });

    window.WorkflowNavigationContext = {
        enhance: enhanceAnchor,
        enhanceAll: enhanceAll,
        currentTarget: currentTarget,
        withCurrentContext: withCurrentContext,
    };
})();