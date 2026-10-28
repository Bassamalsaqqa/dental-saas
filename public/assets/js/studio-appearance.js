/* Native form is authoritative; preview never publishes or writes patient data. */
(() => {
    'use strict';
    const form = document.getElementById('appearance-form');
    const configNode = document.getElementById('appearance-config');
    if (!form || !configNode) return;
    const config = JSON.parse(configNode.textContent);
    const style = document.getElementById('theme-preview-style');
    const originalCss = style.textContent;
    const status = document.getElementById('theme-status');
    const preview = document.getElementById('theme-preview');
    const contrastErrors = document.getElementById('theme-contrast-errors');
    const confirm = document.getElementById('theme-confirm');
    let busy = false;
    let confirmed = false;
    let submitter = null;
    let activeForm = form;
    const trackedForms = [form, document.getElementById('appearance-catalog-form')].filter(Boolean);
    const snapshot = form => JSON.stringify(Array.from(form.elements).filter(e => e.name && !['hidden','submit','button'].includes(e.type)).map(e => [e.name,e.type==='checkbox'||e.type==='radio'?e.checked:e.value]));
    const originals = new Map(trackedForms.map(f => [f,snapshot(f)]));
    let leaving = false;
    window.addEventListener('beforeunload', event => {
        if (!leaving && trackedForms.some(f => snapshot(f)!==originals.get(f))) { event.preventDefault(); event.returnValue=''; }
    });
    const selectedPreset = () => form.querySelector('[name=preset]:checked')?.value;
    function setColors(colors) {
        form.querySelectorAll('[data-theme-color]').forEach(input => { input.value = colors[input.dataset.themeColor]; });
        form.querySelectorAll('[data-theme-hex]').forEach(input => { input.value = colors[input.dataset.themeHex]; });
    }
    form.addEventListener('change', event => {
        if (event.target.matches('[name=preset]')) setColors(config.presets[selectedPreset()].colors);
        if (event.target.matches('[data-theme-color]')) form.querySelector(`[data-theme-hex="${event.target.dataset.themeColor}"]`).value = event.target.value;
    });
    form.addEventListener('input', event => {
        if (event.target.matches('[data-theme-hex]') && /^#[0-9a-f]{6}$/i.test(event.target.value)) form.querySelector(`[data-theme-color="${event.target.dataset.themeHex}"]`).value = event.target.value;
    });
    form.addEventListener('click', event => {
        const reset = event.target.closest('[data-theme-reset]');
        if (!reset) return;
        const role = reset.dataset.themeReset;
        const value = config.presets[selectedPreset()].colors[role];
        form.querySelector(`[data-theme-hex="${role}"]`).value = value;
        form.querySelector(`[data-theme-color="${role}"]`).value = value;
    });
    form.addEventListener('reset', event => {
        event.preventDefault();
        style.textContent = originalCss; status.textContent = config.labels.unchanged;
        contrastErrors.replaceChildren();contrastErrors.hidden=true;
        form.querySelectorAll('[data-theme-hex]').forEach(input=>input.removeAttribute('aria-invalid'));
        setColors(config.initial.colors);
        form.querySelectorAll('[name=preset]').forEach(input => {input.checked = input.value === config.initial.preset;});
        form.elements.namedItem('density').value = config.initial.density;
        form.elements.namedItem('text_size').value = config.initial.text_size;
        const reason = form.elements.namedItem('reason'); if (reason) reason.value = '';
    });
    preview.addEventListener('click', async () => {
        if (busy) return;
        if (Array.from(form.querySelectorAll('[data-theme-hex]')).some(input => !input.reportValidity())) return;
        contrastErrors.replaceChildren(); contrastErrors.hidden = true;
        form.querySelectorAll('[data-theme-hex]').forEach(input=>input.removeAttribute('aria-invalid'));
        busy = true;
        preview.disabled = true;
        const data = new FormData(form);
        data.set('intent', 'preview');
        try {
            const response = await fetch(form.action, {method:'POST',body:data,headers:{Accept:'application/json'},credentials:'same-origin'});
            if (!response.headers.get('content-type')?.includes('application/json')) throw new Error('Session or request failed');
            const payload = await response.json();
            if (payload.csrf) {
                const csrf = form.elements.namedItem(payload.csrf.name);
                if (csrf) csrf.value = payload.csrf.hash;
                document.querySelectorAll('input[type=hidden]').forEach(input => {if (input.name === payload.csrf.name) input.value = payload.csrf.hash;});
                const meta = document.querySelector('meta[name=csrf-token]'); if (meta) meta.content = payload.csrf.hash;
            }
            if (!response.ok && Array.isArray(payload.contrast)) {
                const roles = new Set();
                for (const problem of payload.contrast) {
                    if (typeof problem.message!=='string' || !Array.isArray(problem.roles)) continue;
                    const li=document.createElement('li');li.textContent=problem.message;contrastErrors.append(li);
                    problem.roles.forEach(role=>roles.add(role));
                }
                form.querySelectorAll('[data-theme-hex]').forEach(input=>{if(roles.has(input.dataset.themeHex))input.setAttribute('aria-invalid','true');});
                contrastErrors.hidden=contrastErrors.children.length===0;
            }
            if (!response.ok || !payload.css || !payload.css.startsWith(':root{')) throw new Error('Invalid preview');
            // Server creates the whitelisted declaration map; scope strictly to this synthetic UI sample.
            style.textContent = payload.css.replace(':root{', '#theme-sample{');
            status.textContent = config.labels.ready;
        } catch (_) { status.textContent = config.labels.invalid; }
        finally { busy = false; preview.disabled = false; }
    });
    function reviewSubmit(event) {
        if (confirmed) return;
        event.preventDefault();
        if (busy) return;
        activeForm = event.target;
        submitter = event.submitter;
        if (!submitter) return;
        const reason = activeForm.elements.namedItem('reason');
        if (!reason || !reason.reportValidity()) return;
        confirm.querySelector('h2').textContent = submitter.textContent.trim();
        confirm.querySelector('.appearance-confirm-body>p').textContent = activeForm.id === 'appearance-catalog-form' ? config.labels.catalogHelp : config.labels.publishHelp;
        confirm.showModal();
    }
    form.addEventListener('submit', reviewSubmit);
    document.getElementById('appearance-catalog-form')?.addEventListener('submit', reviewSubmit);
    confirm.addEventListener('close', () => { confirmed = false; submitter?.focus(); });
    document.getElementById('theme-confirm-cancel').addEventListener('click', () => confirm.close());
    document.getElementById('theme-confirm-go').addEventListener('click', () => {
        if (!submitter) return;
        const old = form.querySelector('[name=revert_version]');
        if (old) old.remove();
        if (submitter.dataset.revertVersion) {
            const input = document.createElement('input'); input.type='hidden';input.name='revert_version';input.value=submitter.dataset.revertVersion;form.appendChild(input);
        }
        confirmed = true;
        leaving = true;
        activeForm.requestSubmit(submitter);
        document.getElementById('theme-confirm-go').disabled = true;
    });
})();
