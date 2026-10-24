/**
 * Select2 Initialization Script
 * Replaces custom searchable select with Select2 for better UX and functionality
 */

function getMetaContent(name) {
    const el = document.querySelector(`meta[name="${name}"]`);
    return el ? el.getAttribute('content') : '';
}

const uiSearchAuth = {
    tokenUrl: getMetaContent('api-session-token-url'),
    clinicId: getMetaContent('active-clinic-id'),
    token: null,
    expiresAt: null,
    tokenPromise: null
};

function isSameOriginUrl(url) {
    if (typeof url !== 'string' || !url) return false;
    if (!/^[a-z][a-z0-9+.-]*:/i.test(url) && !url.startsWith('//')) {
        return true;
    }
    if (typeof window !== 'undefined' && window.location && window.location.origin) {
        try {
            const parsed = new URL(url, window.location.origin);
            return parsed.origin === window.location.origin;
        } catch (_e) {
            return false;
        }
    }
    return false;
}

function isV1SearchUrl(url) {
    return typeof url === 'string' && /(?:^|\/)api\/v1\/search\//.test(url);
}

function resolveFirstPartySearchUrl(url) {
    if (typeof url !== 'string' || !url) {
        return url;
    }
    if (!isSameOriginUrl(url)) {
        return url;
    }

    const pattern = /^(.*\/)?api\/v1\/search\/(patients|users|examinations|treatments|inventory|medications|treatment-types|departments|roles)([?#].*)?$/;
    const match = url.match(pattern);
    if (match) {
        const prefix = match[1] || '';
        const endpoint = match[2];
        const rest = match[3] || '';
        return `${prefix}api/search/${endpoint}${rest}`;
    }

    return url;
}

function isTokenValid() {
    if (!uiSearchAuth.token || !uiSearchAuth.expiresAt) {
        return false;
    }
    const exp = Date.parse(uiSearchAuth.expiresAt);
    if (isNaN(exp)) {
        return true;
    }
    return (Date.now() + 30000) < exp;
}

function fetchUiSearchToken() {
    if (!uiSearchAuth.tokenUrl) {
        return Promise.resolve(null);
    }
    if (isTokenValid()) {
        return Promise.resolve(uiSearchAuth.token);
    }
    if (uiSearchAuth.tokenPromise) {
        return uiSearchAuth.tokenPromise;
    }

    uiSearchAuth.tokenPromise = fetch(uiSearchAuth.tokenUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-store',
            'Pragma': 'no-cache'
        }
    })
        .then(response => response.ok ? response.json() : null)
        .then(data => {
            if (data && data.token) {
                uiSearchAuth.token = data.token;
                uiSearchAuth.expiresAt = data.expires_at || null;
                return uiSearchAuth.token;
            }
            return null;
        })
        .catch(() => null)
        .finally(() => {
            uiSearchAuth.tokenPromise = null;
        });

    return uiSearchAuth.tokenPromise;
}

function invalidateUiSearchToken() {
    uiSearchAuth.token = null;
    uiSearchAuth.expiresAt = null;
}

function hideRedundantSelectChevron(select) {
    if (!select || !select.parentElement) {
        return;
    }

    const overlays = select.parentElement.querySelectorAll(':scope > .pointer-events-none i.fas.fa-chevron-down, :scope > .absolute.pointer-events-none i.fas.fa-chevron-down');
    overlays.forEach(icon => {
        const container = icon.closest('.pointer-events-none');
        if (container) {
            container.style.display = 'none';
        }
    });
}

function getAppSelect2DropdownParent(select) {
    if (!select || !window.jQuery) {
        return undefined;
    }

    const drawer = select.closest('.app-drawer, .app-scale-modal');
    if (!drawer) {
        return window.jQuery(document.body);
    }

    const explicitParent = select.closest('[data-select2-dropdown-parent]');
    const host = explicitParent || drawer;
    host.classList.add('app-select2-dropdown-parent');
    host.setAttribute('dir', document.documentElement.getAttribute('dir') === 'rtl' ? 'rtl' : 'ltr');
    return window.jQuery(host);
}

window.getAppSelect2DropdownParent = getAppSelect2DropdownParent;
function applyV1SearchHeaders(headers) {
    const finalHeaders = headers || {};
    if (uiSearchAuth.token) {
        finalHeaders['Authorization'] = 'Bearer ' + uiSearchAuth.token;
    }
    if (uiSearchAuth.clinicId) {
        finalHeaders['X-Clinic-Id'] = uiSearchAuth.clinicId;
    }
    finalHeaders['Cache-Control'] = 'no-store';
    finalHeaders['Pragma'] = 'no-cache';
    return finalHeaders;
}

$(document).ajaxError(function(_event, jqXHR, settings) {
    if (!settings || !isV1SearchUrl(settings.url)) {
        return;
    }
    if (jqXHR.status !== 401 || settings._uiSearchRetried) {
        return;
    }

    invalidateUiSearchToken();
    fetchUiSearchToken().then(token => {
        if (!token) {
            return;
        }
        const retrySettings = $.extend(true, {}, settings);
        retrySettings._uiSearchRetried = true;
        retrySettings.headers = applyV1SearchHeaders(retrySettings.headers || {});
        $.ajax(retrySettings);
    });
});

document.addEventListener('DOMContentLoaded', async function() {
    console.log('Select2: Initializing...');
    const pathName = window.location.pathname || '';
    const isCreateEditPage = /\/(create|edit)(\/|$)/.test(pathName);
    const isSettingsPage = /\/settings(\/|$|\?)/.test(pathName);
    const isRtl = document.documentElement.getAttribute('dir') === 'rtl';
    const select2I18n = {
        placeholder: isRtl ? '\u0627\u062e\u062a\u0631 \u062e\u064a\u0627\u0631\u064b\u0627...' : 'Select an option...',
        noResults: isRtl ? '\u0644\u0627 \u062a\u0648\u062c\u062f \u0646\u062a\u0627\u0626\u062c' : 'No results found',
        searching: isRtl ? '\u062c\u0627\u0631\u064d \u0627\u0644\u0628\u062d\u062b...' : 'Searching...',
        inputTooShort: function(minimumInputLength) {
            return isRtl
                ? `\u0627\u0644\u0631\u062c\u0627\u0621 \u0625\u062f\u062e\u0627\u0644 ${minimumInputLength} \u0623\u062d\u0631\u0641 \u0623\u0648 \u0623\u0643\u062b\u0631`
                : `Please enter ${minimumInputLength} or more characters`;
        }
    };
    // Initialize Select2 for elements with data-searchable-select attribute
    const searchableSelects = Array.from(document.querySelectorAll('[data-searchable-select]'))
        .filter(select => !select.closest('.app-drawer, .app-scale-modal'));
    console.log('Select2: Found', searchableSelects.length, 'searchable select elements');

    let needsBearer = false;
    searchableSelects.forEach(select => {
        const searchUrl = resolveFirstPartySearchUrl(select.dataset.searchUrl || '');
        if (searchUrl.indexOf('/api/v1/search/') !== -1) {
            needsBearer = true;
        }
    });

    if (needsBearer) {
        const token = await fetchUiSearchToken();
        if (!token) {
            console.warn('Select2: UI search token unavailable; v1 search may 401.');
        }
    }
    
    searchableSelects.forEach((select, index) => {
        try {
            const searchUrl = resolveFirstPartySearchUrl(select.dataset.searchUrl);
            const searchParam = select.dataset.searchParam || 'q';
            const valueField = select.dataset.valueField || 'id';
            const textField = select.dataset.textField || 'text';
            const placeholder = select.dataset.placeholder || 'Search and select...';
            const allowClear = select.dataset.allowClear
                ? select.dataset.allowClear !== 'false'
                : !select.required;
            const minimumInputLength = Number.isNaN(parseInt(select.dataset.minimumInputLength, 10))
                ? 1
                : parseInt(select.dataset.minimumInputLength, 10);
            const delay = parseInt(select.dataset.delay) || 300;
            const limit = parseInt(select.dataset.limit) || null;
            const extraParamsRaw = select.dataset.extraParams || '';
            let extraParams = null;
            if (extraParamsRaw) {
                try {
                    extraParams = JSON.parse(extraParamsRaw);
                } catch (e) {
                    extraParams = null;
                }
            }
            const statusParam = select.dataset.status || '';
            const dependsOnId = select.dataset.dependsOn || '';
            const dependsParam = select.dataset.dependsParam || 'parent_id';
            const requireDependency = select.dataset.requireDependency !== 'false';
            const getDependencyValue = () => {
                if (!dependsOnId) {
                    return '';
                }
                const depEl = document.getElementById(dependsOnId);
                if (!depEl) {
                    return '';
                }
                if (window.jQuery) {
                    const jqVal = $(depEl).val();
                    if (Array.isArray(jqVal)) {
                        if (jqVal.length) {
                            return jqVal[0];
                        }
                    } else if (jqVal) {
                        return jqVal;
                    }
                    if ($(depEl).data('select2')) {
                        const data = $(depEl).select2('data');
                        if (Array.isArray(data) && data.length) {
                            return data[0].id || data[0].value || '';
                        }
                    }
                }
                return depEl.value || '';
            };
            
            console.log(`Select2: Initializing element ${index + 1}`, {
                id: select.id || 'unnamed',
                searchUrl: searchUrl,
                placeholder: placeholder,
                allowClear: allowClear
            });
            
            // Configure Select2 options
            const select2Options = {
                placeholder: placeholder,
                allowClear: allowClear,
                minimumInputLength: minimumInputLength,
                minimumResultsForSearch: 0,
                delay: delay,
                width: '100%',
                theme: 'default',
                language: {
                    noResults: function() {
                        return select2I18n.noResults;
                    },
                    searching: function() {
                        return select2I18n.searching;
                    },
                    inputTooShort: function() {
                        return select2I18n.inputTooShort(minimumInputLength);
                    }
                },
                dir: select.name === 'provider_user_id' ? 'ltr' : (isRtl ? 'rtl' : 'ltr'),
                dropdownParent: getAppSelect2DropdownParent(select)
            };
            
            // Add AJAX configuration if search URL is provided
            if (searchUrl) {
                select2Options.ajax = {
                    url: searchUrl,
                    dataType: 'json',
                    delay: delay,
                    data: function (params) {
                        const payload = {
                            [searchParam]: params.term,
                            page: params.page || 1
                        };
                        if (dependsOnId) {
                            const depValue = getDependencyValue();
                            if (depValue) {
                                payload[dependsParam] = depValue;
                            } else if (requireDependency) {
                                payload.__skip = 1;
                            }
                        }
                        if (statusParam) {
                            payload.status = statusParam;
                        }
                        if (extraParams && typeof extraParams === 'object') {
                            Object.keys(extraParams).forEach(key => {
                                if (typeof payload[key] === 'undefined') {
                                    payload[key] = extraParams[key];
                                }
                            });
                        }
                        if (limit) {
                            payload.limit = limit;
                        }
                        return payload;
                    },
                    transport: function (params, success, failure) {
                        if (params && params.data && params.data.__skip) {
                            success({ results: [], total: 0, pagination: { more: false } });
                            return { abort: function () {} };
                        }
                        const request = $.ajax(params);
                        request.then(success).fail(failure);
                        return request;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        
                        // Handle different response formats
                        let results = [];
                        if (data.results) {
                            results = data.results;
                        } else if (Array.isArray(data)) {
                            results = data;
                        } else if (data.data) {
                            results = data.data;
                        }
                        
                        return {
                            results: results.map(item => ({
                                id: item[valueField],
                                text: item[textField]
                            })),
                            pagination: {
                                more: (params.page * (limit || 10)) < (data.total || results.length)
                            }
                        };
                    },
                    beforeSend: function (xhr) {
                        if (isV1SearchUrl(searchUrl)) {
                            const headers = applyV1SearchHeaders();
                            Object.keys(headers).forEach(key => xhr.setRequestHeader(key, headers[key]));
                        }
                    },
                    cache: !isV1SearchUrl(searchUrl)
                };
            }
            
            // Initialize Select2
            $(select).select2(select2Options);
            hideRedundantSelectChevron(select);

            if (searchUrl && minimumInputLength === 0) {
                $(select).on('select2:open', function() {
                    const instance = $(this).data('select2');
                    if (instance) {
                        instance.trigger('query', { term: '', page: 1 });
                    }
                });
            }

            if (dependsOnId) {
                const depEl = document.getElementById(dependsOnId);
                if (depEl) {
                    const syncDependencyState = () => {
                        if (!requireDependency) {
                            return;
                        }
                        const hasValue = !!getDependencyValue();
                        $(select).prop('disabled', !hasValue).trigger('change.select2');
                        if (!hasValue) {
                            $(select).val(null).trigger('change');
                        }
                    };
                    syncDependencyState();
                    depEl.addEventListener('change', syncDependencyState);
                    if (window.jQuery) {
                        $(depEl).on('select2:select select2:clear', syncDependencyState);
                    }
                }
            }
            
            // Add custom styling classes
            $(select).on('select2:open', function() {
                $('.select2-container--default .select2-search--dropdown .select2-search__field').addClass('form-input');
            });
            
            // Handle form validation styling
            $(select).on('change', function() {
                const $select2 = $(this).next('.select2-container');
                if ($(this).hasClass('is-invalid')) {
                    $select2.addClass('is-invalid');
                } else {
                    $select2.removeClass('is-invalid');
                }
            });
            
            console.log(`Select2: Successfully initialized element ${index + 1}`);
        } catch (error) {
            console.error(`Select2: Error initializing element ${index + 1}:`, error);
        }
    });
    
    // Initialize Select2 for regular select elements that should be searchable
    const regularSelects = Array.from(document.querySelectorAll('select:not([data-searchable-select]):not([data-no-select2])'))
        .filter(select => !select.closest('.app-drawer, .app-scale-modal'));
    console.log('Select2: Found', regularSelects.length, 'regular select elements');
    
    regularSelects.forEach((select, index) => {
        try {
            const forceSelect2 = select.hasAttribute('data-force-select2');
            const optionCount = select.options.length;
            const isInsideForm = !!select.closest('form');
            const isCompactUtilitySelect =
                !!select.closest('.dataTables_wrapper') ||
                select.id === 'pageSize' ||
                select.id === 'pageLength' ||
                select.classList.contains('report-detail-per-page');
            const shouldUseSelect2 = !isCompactUtilitySelect && (forceSelect2 || isCreateEditPage || isSettingsPage || isInsideForm || select.classList.contains('form-select'));

            // Skip simple selects outside create/edit pages unless explicitly forced
            if (!shouldUseSelect2 && optionCount <= 5) {
                return;
            }
            
            console.log(`Select2: Initializing regular select ${index + 1}`, {
                id: select.id || 'unnamed',
                options: optionCount,
                forced: forceSelect2,
                createEditPage: isCreateEditPage
            });
            
            const regularAllowClear = select.dataset.allowClear
                ? select.dataset.allowClear !== 'false'
                : !select.required;

            $(select).select2({
                placeholder: select2I18n.placeholder,
                dir: select.name === 'provider_user_id' ? 'ltr' : (isRtl ? 'rtl' : 'ltr'),
                allowClear: regularAllowClear,
                width: '100%',
                theme: 'default',
                dropdownParent: getAppSelect2DropdownParent(select),
                minimumResultsForSearch: optionCount > 5 ? 0 : Infinity,
                language: {
                    noResults: function() {
                        return select2I18n.noResults;
                    }
                }
            });
            hideRedundantSelectChevron(select);
            
            console.log(`Select2: Successfully initialized regular select ${index + 1}`);
        } catch (error) {
            console.error(`Select2: Error initializing regular select ${index + 1}:`, error);
        }
    });
    
    console.log('Select2: Initialization complete');
});

// Custom CSS for Select2 to match application theme
const select2CustomCSS = `
<style>
/* Select2 Custom Styling */
.select2-container--default .select2-selection--single {
    height: 42px;
    border: 1px solid var(--denta-taupe-border);
    border-radius: 5px;
    background-color: var(--denta-ivory-input);
    position: relative;
    transition: all 0.2s ease-in-out;
}

.select2-container--default .select2-selection--single:hover {
    border-color: var(--denta-emerald-pine);
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--denta-emerald-pine);
    box-shadow:none;
    outline: none;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--denta-ink);
    line-height: 40px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-left: 12px;
    padding-right: 44px;
    padding-inline-start: 12px;
    padding-inline-end: 44px;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: var(--denta-muted);
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
    width: 28px;
    right: 8px;
    left: auto;
    inset-inline-start: auto;
    inset-inline-end: 8px;
}

html[dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__rendered {
    padding-left: 44px;
    padding-right: 12px;
    padding-inline-start: 12px;
    padding-inline-end: 44px;
    text-align: right;
}

html[dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__arrow {
    left: 8px;
    right: auto;
    inset-inline-start: auto;
    inset-inline-end: 8px;
}

.select2-container--default .select2-selection--single .select2-selection__clear {
    float: none;
    position: absolute;
    top: 50%;
    right: 34px;
    transform: translateY(-50%);
    margin: 0;
    line-height: 1;
}

html[dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__clear {
    right: auto;
    left: 34px;
}

html[dir="rtl"] .select2-container--default .select2-search--dropdown .select2-search__field,
html[dir="rtl"] .select2-container--default .select2-results__option,
html[dir="rtl"] .select2-container--default .select2-results__message {
    text-align: right;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: var(--denta-muted) transparent transparent transparent;
    border-style: solid;
    border-width: 5px 4px 0 4px;
    height: 0;
    left: 50%;
    margin-left: -4px;
    margin-top: -2px;
    position: absolute;
    top: 50%;
    width: 0;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent var(--denta-muted) transparent;
    border-width: 0 4px 5px 4px;
}

.select2-dropdown {
    border: 1px solid var(--denta-taupe-border);
    border-radius: 5px;
    box-shadow:none;
    background-color: var(--denta-ivory-input);
    z-index: 9999;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid var(--denta-taupe-border);
    border-radius: 0.375rem;
    padding: 8px 12px;
    font-size: 14px;
    margin: 4px;
    width: calc(100% - 8px);
}

.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: var(--denta-emerald-pine);
    box-shadow:none;
    outline: none;
}

.select2-container--default .select2-results__option {
    padding: 8px 12px;
    font-size: 14px;
    color: var(--denta-ink);
    transition: background-color 0.2s ease-in-out;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--denta-emerald-pine);
    color: var(--denta-ivory-input);
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: var(--denta-parchment);
    color: var(--denta-ink);
}

.select2-container--default .select2-results__option[aria-selected=true]:hover {
    background-color: var(--denta-taupe-border);
}

.select2-container--default .select2-results__option--loading {
    color: var(--denta-muted);
    font-style: italic;
}

.select2-container--default .select2-results__message {
    color: var(--denta-muted);
    font-style: italic;
    padding: 8px 12px;
}

/* Error state styling */
.select2-container--default.is-invalid .select2-selection--single {
    border-color: var(--denta-terracotta);
}

.select2-container--default.is-invalid.select2-container--focus .select2-selection--single {
    border-color: var(--denta-terracotta);
    box-shadow:none;
}

/* Loading state */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    position: relative;
}

.select2-container--default .select2-selection--single .select2-selection__rendered::after {
    content: '';
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    border: 2px solid var(--denta-taupe-border);
    border-top: 2px solid var(--denta-emerald-pine);
    border-radius: 50%;
    animation: select2-spin 1s linear infinite;
    display: none;
}

.select2-container--default.select2-container--loading .select2-selection--single .select2-selection__rendered::after {
    display: block;
}

@keyframes select2-spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .select2-container--default .select2-selection--single {
        height: 40px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 10px;
        padding-right: 42px;
        padding-inline-start: 10px;
        padding-inline-end: 42px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
        width: 28px;
        right: 6px;
        left: auto;
        inset-inline-start: auto;
        inset-inline-end: 6px;
    }

    html[dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 42px;
        padding-right: 10px;
        padding-inline-start: 10px;
        padding-inline-end: 42px;
    }

    html[dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__arrow {
        left: 6px;
        right: auto;
        inset-inline-start: auto;
        inset-inline-end: 6px;
    }
}

</style>
`;

// Inject custom CSS
document.head.insertAdjacentHTML('beforeend', select2CustomCSS);

window.initAppSelects = async function initAppSelects(root) {
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
        return;
    }

    const scope = root || document;
    const isRtl = document.documentElement.getAttribute('dir') === 'rtl';
    const i18n = {
        placeholder: isRtl ? 'اختر خيارًا...' : 'Select an option...',
        noResults: isRtl ? 'لا توجد نتائج' : 'No results found',
        searching: isRtl ? 'جارٍ البحث...' : 'Searching...',
    };

    if (isRtl) {
        i18n.placeholder = '\u0627\u062e\u062a\u0631 \u062e\u064a\u0627\u0631\u0627...';
        i18n.noResults = '\u0644\u0627 \u062a\u0648\u062c\u062f \u0646\u062a\u0627\u0626\u062c';
        i18n.searching = '\u062c\u0627\u0631\u064a \u0627\u0644\u0628\u062d\u062b...';
    }

    const drawerSearchSelects = Array.from(scope.querySelectorAll('select[data-searchable-select]'));
    const needsBearer = drawerSearchSelects.some(select => isV1SearchUrl(resolveFirstPartySearchUrl(select.dataset.searchUrl || '')));

    if (needsBearer) {
        const token = await fetchUiSearchToken();
        if (!token) {
            console.warn('Select2: UI search token unavailable before drawer select initialization; v1 search may 401.');
        }
    }

    const dropdownParentFor = (select) => getAppSelect2DropdownParent(select);

    const initializeSelect = (select, searchable) => {
        const $select = window.jQuery(select);
        if ($select.data('select2')) {
            return;
        }

        const dropdownParent = dropdownParentFor(select);
        const optionCount = select.options ? select.options.length : 0;
        const allowClear = select.dataset.allowClear
            ? select.dataset.allowClear !== 'false'
            : !select.required;

        const options = {
            placeholder: select.dataset.placeholder || i18n.placeholder,
            allowClear: allowClear,
            width: '100%',
            theme: 'default',
            dir: select.name === 'provider_user_id' ? 'ltr' : (isRtl ? 'rtl' : 'ltr'),
            dropdownParent: dropdownParent,
            minimumResultsForSearch: searchable || optionCount > 5 ? 0 : Infinity,
            language: {
                noResults: function () { return i18n.noResults; },
                searching: function () { return i18n.searching; }
            }
        };

        if (searchable && select.dataset.searchUrl) {
            const searchUrl = resolveFirstPartySearchUrl(select.dataset.searchUrl);
            const limit = parseInt(select.dataset.limit || '10', 10);
            const searchParam = select.dataset.searchParam || 'q';
            const valueField = select.dataset.valueField || 'id';
            const textField = select.dataset.textField || 'text';
            const minimumInputLength = Number.isNaN(parseInt(select.dataset.minimumInputLength, 10))
                ? 1
                : parseInt(select.dataset.minimumInputLength, 10);
            const dependsOnId = select.dataset.dependsOn || '';
            const dependsParam = select.dataset.dependsParam || 'parent_id';
            const requireDependency = select.dataset.requireDependency !== 'false';
            const statusParam = select.dataset.status || '';
            let extraParams = null;
            if (select.dataset.extraParams) {
                try {
                    extraParams = JSON.parse(select.dataset.extraParams);
                } catch (_error) {
                    extraParams = null;
                }
            }

            const getDependencyValue = () => {
                if (!dependsOnId) return '';
                const depEl = scope.querySelector('#' + CSS.escape(dependsOnId)) || document.getElementById(dependsOnId);
                if (!depEl) return '';
                const jqVal = window.jQuery(depEl).val();
                return Array.isArray(jqVal) ? (jqVal[0] || '') : (jqVal || depEl.value || '');
            };

            options.minimumInputLength = minimumInputLength;
            options.ajax = {
                url: searchUrl,
                dataType: 'json',
                delay: parseInt(select.dataset.delay || '300', 10),
                data: function (params) {
                    const payload = {
                        [searchParam]: params.term,
                        page: params.page || 1
                    };
                    if (dependsOnId) {
                        const depValue = getDependencyValue();
                        if (depValue) {
                            payload[dependsParam] = depValue;
                        } else if (requireDependency) {
                            payload.__skip = 1;
                        }
                    }
                    if (statusParam) payload.status = statusParam;
                    if (extraParams && typeof extraParams === 'object') {
                        Object.keys(extraParams).forEach(key => {
                            if (typeof payload[key] === 'undefined') {
                                payload[key] = extraParams[key];
                            }
                        });
                    }
                    if (limit) payload.limit = limit;
                    return payload;
                },
                transport: function (params, success, failure) {
                    if (params && params.data && params.data.__skip) {
                        success({ results: [], total: 0, pagination: { more: false } });
                        return { abort: function () {} };
                    }
                    const request = window.jQuery.ajax(params);
                    request.then(success).fail(failure);
                    return request;
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    const results = data.results || (Array.isArray(data) ? data : (data.data || []));
                    return {
                        results: results.map(item => ({
                            id: item[valueField],
                            text: item[textField]
                        })),
                        pagination: {
                            more: (params.page * (limit || 10)) < (data.total || results.length)
                        }
                    };
                },
                beforeSend: function (xhr) {
                    if (isV1SearchUrl(searchUrl)) {
                        const headers = applyV1SearchHeaders();
                        Object.keys(headers).forEach(key => xhr.setRequestHeader(key, headers[key]));
                    }
                },
                cache: !isV1SearchUrl(searchUrl)
            };
        }

        $select.select2(options);
        hideRedundantSelectChevron(select);
    };

    drawerSearchSelects.forEach(select => initializeSelect(select, true));
    scope.querySelectorAll('select[data-force-select2], .app-drawer select:not([data-no-select2]), .app-scale-modal select:not([data-no-select2])').forEach(select => initializeSelect(select, false));
};

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        resolveFirstPartySearchUrl,
        isV1SearchUrl,
        isSameOriginUrl
    };
}
