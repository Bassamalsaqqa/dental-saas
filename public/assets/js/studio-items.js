/* Supplier selection/creation only. Item writes remain native, canonical form POSTs. */
(() => {
const select=document.getElementById('supplier_id'),text=document.getElementById('supplier');
if(select&&window.jQuery?.fn?.select2)jQuery(select).select2({width:'100%',dir:document.documentElement.dir,minimumResultsForSearch:0});
function syncSupplier(){if(select?.value&&text)text.value=select.selectedOptions[0]?.textContent?.trim()||text.value;}
select?.addEventListener('change',syncSupplier);if(select&&window.jQuery?.fn?.select2)jQuery(select).on('change',syncSupplier);
const form=document.getElementById('item-supplier-form'),dialog=document.getElementById('item-supplier-dialog');if(!form||!dialog)return;
form.querySelector('[data-item-supplier-cancel]')?.addEventListener('click',()=>dialog.close());
form.addEventListener('submit',async e=>{e.preventDefault();const button=form.querySelector('[type=submit]'),feedback=document.getElementById('item-supplier-feedback');button.disabled=true;feedback.hidden=true;
try{const data=new FormData(form);const token=document.querySelector('meta[name=csrf-token]')?.content,name=document.querySelector('meta[name=csrf-name]')?.content;if(name&&token)data.set(name,token);
 const response=await fetch(form.action,{method:'POST',credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'},body:data});const result=await response.json();
 if(typeof result.csrf_token==='string'){const meta=document.querySelector('meta[name=csrf-token]');if(meta)meta.content=result.csrf_token;document.querySelectorAll('input').forEach(input=>{if(input.name===name)input.value=result.csrf_token;});}
 if(!response.ok||!result.success||!result.supplier)throw Error(typeof result.message==='string'?result.message:form.dataset.failed);
 const id=Number(result.supplier.id);if(!Number.isSafeInteger(id)||id<=0)throw Error(form.dataset.failed);
 let option=Array.from(select.options).find(o=>o.value===String(id));if(!option){option=new Option(String(result.supplier.name),String(id));select.add(option);}select.value=String(id);syncSupplier();if(window.jQuery?.fn?.select2)jQuery(select).trigger('change');
 let notice=document.getElementById('item-supplier-success');if(!notice){notice=document.createElement('p');notice.id='item-supplier-success';notice.className='item-supplier-feedback';notice.setAttribute('role','status');select.closest('.form-field').append(notice);}notice.textContent=form.dataset.success;dialog.close();
}catch(error){feedback.textContent=error.message||form.dataset.failed;feedback.classList.add('is-error');feedback.hidden=false;}finally{button.disabled=false;}});
})();