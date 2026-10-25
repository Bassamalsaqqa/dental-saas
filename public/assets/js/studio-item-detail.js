/* Existing stock endpoints, explicit dialogs; no inventory accounting in the browser. */
(() => {
const config=JSON.parse(document.getElementById('stock-config').textContent);let pendingMapping=null;
document.querySelectorAll('[data-stock-cancel]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog').close()));
document.querySelectorAll('[data-reorder-qty]').forEach(b=>b.addEventListener('click',()=>{const dialog=document.getElementById('stock-reorder');dialog.querySelector('[name=requested_qty]').value=String(Math.max(1,Number(b.dataset.reorderQty)||1));dialog.showModal();}));
document.querySelectorAll('[data-confirm-mapping]').forEach(form=>form.addEventListener('submit',e=>{e.preventDefault();pendingMapping=form;document.getElementById('stock-mapping-confirm').showModal();}));
document.querySelector('[data-mapping-remove]')?.addEventListener('click',e=>{if(!pendingMapping)return;e.currentTarget.disabled=true;pendingMapping.submit();});
document.querySelectorAll('[data-stock-action]').forEach(form=>form.addEventListener('submit',async e=>{
e.preventDefault();const action=form.dataset.stockAction,button=form.querySelector('[type=submit]'),error=form.querySelector('[data-stock-error]');button.disabled=true;error.hidden=true;
try{const data=new FormData(form),token=document.querySelector('meta[name=csrf-token]')?.content,name=document.querySelector('meta[name=csrf-name]')?.content;const headers={'X-Requested-With':'XMLHttpRequest'};if(token)headers['X-CSRF-TOKEN']=token;if(name&&token)data.set(name,token);let body;
if(action==='receive'||action==='adjust'){headers['Content-Type']='application/json';const values=Object.fromEntries(data);if(action==='receive'){values.quantity=Number(values.quantity);values.reason=values.reason||'Stock received';}else{values.adjustment=Number(values.adjustment);values.reason=values.reason||'Manual adjustment';}body=JSON.stringify(values);}else body=data;
const response=await fetch(form.action,{method:action==='delete'?'DELETE':'POST',credentials:'same-origin',headers,body});const result=await response.json();
if(typeof result.csrf_token==='string'){const meta=document.querySelector('meta[name=csrf-token]');if(meta)meta.content=result.csrf_token;document.querySelectorAll('input').forEach(input=>{if(input.name===name)input.value=result.csrf_token;});}
if(!response.ok||result.success!==true)throw Error(typeof result.message==='string'?result.message:typeof result.error==='string'?result.error:config.failed);
if(action==='delete'){location.assign(config.inventoryUrl);return;}if(action==='reorder'){const feedback=document.getElementById('stock-feedback');feedback.textContent=result.message||config.reorderDone;feedback.hidden=false;form.closest('dialog').close();feedback.scrollIntoView({block:'nearest'});return;}location.reload();
}catch(failure){error.textContent=failure.message||config.failed;error.hidden=false;}finally{button.disabled=false;}
}));
})();
