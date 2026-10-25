<script>
    // Global CSRF configuration
    window.appBaseUrl = '<?= rtrim(base_url(), '/') ?>/';
    window.csrfConfig = {
        name: document.querySelector('meta[name="csrf-name"]')?.getAttribute('content') || '<?= csrf_token() ?>',
        header: document.querySelector('meta[name="csrf-header"]')?.getAttribute('content') || '<?= csrf_header() ?>',
        cookieName: '<?= esc(config('Security')->cookieName) ?>'
    };

    // Initialize global hash
    window.csrfHash = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?= csrf_hash() ?>';

    // Global CSRF helper functions
    window.getCsrfToken = function() {
        return window.csrfHash;
    };

    window.refreshCsrfToken = function(serverToken) {
        let token = serverToken;
        if (!token) {
            const name = window.csrfConfig.cookieName;
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                token = parts.pop().split(';').shift();
            }
        }

        if (token) {
            window.csrfHash = token;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', token);
                // Also update any hidden CSRF inputs in forms
                document.querySelectorAll(`input[name="${window.csrfConfig.name}"]`).forEach(input => {
                    input.value = token;
                });
            }
            console.log('CSRF token refreshed successfully');
            return token;
        }
        return window.csrfHash;
    };

    function isSafeHttpMethod(method) {
        return ['GET', 'HEAD', 'OPTIONS', 'TRACE'].includes(method);
    }

    function appendCsrfToData(data, name, token, contentType) {
        if (!name || !token) {
            return data;
        }

        if (typeof FormData !== 'undefined' && data instanceof FormData) {
            if (!data.has(name)) {
                data.append(name, token);
            }
            return data;
        }

        if (typeof data === 'string') {
            const encodedName = encodeURIComponent(name);
            const hasToken = new RegExp(`(^|&)${encodedName}=`).test(data);
            if (hasToken) {
                return data;
            }
            if (contentType && contentType.indexOf('application/json') !== -1) {
                try {
                    const parsed = JSON.parse(data);
                    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                        parsed[name] = parsed[name] || token;
                        return JSON.stringify(parsed);
                    }
                } catch (e) {
                    return data;
                }
            }
            const separator = data.length ? '&' : '';
            return `${data}${separator}${encodedName}=${encodeURIComponent(token)}`;
        }

        if (data == null) {
            return { [name]: token };
        }

        if (typeof data === 'object') {
            if (typeof data[name] === 'undefined') {
                data[name] = token;
            }
            return data;
        }

        return data;
    }

    if (window.jQuery && $.ajaxPrefilter) {
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            const method = (options.type || options.method || 'GET').toUpperCase();
            if (isSafeHttpMethod(method)) {
                return;
            }

            const name = window.csrfConfig?.name;
            const token = window.getCsrfToken ? window.getCsrfToken() : null;
            if (!name || !token) {
                return;
            }

            const headerName = window.csrfConfig?.header || 'X-CSRF-TOKEN';
            const beforeSend = options.beforeSend;
            options.beforeSend = function(xhr, settings) {
                xhr.setRequestHeader(headerName, token);
                if (beforeSend) {
                    return beforeSend.call(this, xhr, settings);
                }
            };

            if (typeof options.data === 'function') {
                const originalData = options.data;
                options.data = function() {
                    const result = originalData.apply(this, arguments);
                    return appendCsrfToData(result, name, token, options.contentType);
                };
            } else {
                options.data = appendCsrfToData(options.data, name, token, options.contentType);
            }
        });

        $(document).ajaxComplete(function(event, xhr) {
            const headerName = window.csrfConfig?.header || 'X-CSRF-TOKEN';
            let newToken = null;
            try {
                newToken = xhr.getResponseHeader(headerName);
            } catch (e) {
                newToken = null;
            }

            if (!newToken) {
                const json = xhr.responseJSON;
                if (json) {
                    newToken = json.csrf_token || json.csrfToken || json.csrfHash || json.csrf_token_hash;
                } else {
                    const contentType = xhr.getResponseHeader('Content-Type') || '';
                    if (contentType.indexOf('application/json') !== -1 && typeof xhr.responseText === 'string') {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            newToken = parsed.csrf_token || parsed.csrfToken || parsed.csrfHash || parsed.csrf_token_hash;
                        } catch (e) {
                            newToken = null;
                        }
                    }
                }
            }

            if (newToken && window.refreshCsrfToken) {
                window.refreshCsrfToken(newToken);
            }
        });
    }


</script>
