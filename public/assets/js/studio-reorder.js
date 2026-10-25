/* Local list filters and confirmation; all writes use existing native forms. */
(() => {
    const buttons=[...document.querySelectorAll('[data-reorder-filter]')];
    const rows=[...document.querySelectorAll('[data-reorder-status]')];
    buttons.forEach(button=>button.addEventListener('click',()=>{
        buttons.forEach(b=>b.setAttribute('aria-pressed',String(b===button)));
        rows.forEach(row=>row.hidden=button.dataset.reorderFilter!=='all'&&row.dataset.reorderStatus!==button.dataset.reorderFilter);
        const empty=document.querySelector('.reorder-filter-empty');
        if(empty)empty.hidden=rows.some(row=>!row.hidden);
    }));
    const dialog=document.getElementById('reorder-confirm-dialog');if(!dialog)return;
    let pending=null,trigger=null,confirmed=null;
    document.querySelectorAll('[data-reorder-remove]').forEach(form=>form.addEventListener('submit',e=>{
        if(confirmed===form){confirmed=null;return;}
        e.preventDefault();pending=form;trigger=e.submitter||form.querySelector('button');
        dialog.querySelector('.reorder-confirm-message').textContent=form.dataset.confirm||'';
        dialog.showModal();
    }));
    dialog.querySelector('[data-reorder-stay]').addEventListener('click',()=>dialog.close());
    dialog.querySelector('[data-reorder-confirm]').addEventListener('click',()=>{
        if(!pending)return;const form=pending;confirmed=form;dialog.close();form.requestSubmit();
    });
    dialog.addEventListener('close',()=>{pending=null;trigger?.focus();});
})();