/* Register in context using the existing authenticated endpoint. No automatic retries after uncertain writes. */
(() => {
 'use strict';
 const dialog=document.querySelector('[data-studio-create-dialog]');if(!dialog)return;
 const form=dialog.querySelector('form'),feedback=dialog.querySelector('[data-create-feedback]'),discard=dialog.querySelector('[data-create-discard]'),success=dialog.querySelector('[data-create-success]');
 const patient=dialog.dataset.createKind==='patient';
 const focusFirst=()=>form.querySelector('input:not([type=hidden]):not([disabled]),select:not([disabled]),textarea:not([disabled])')?.focus();
 const fields=()=>Array.from(form.elements).filter(e=>e.name&&!['hidden','submit','button'].includes(e.type));
 const snapshot=()=>JSON.stringify(fields().map(e=>[e.name,e.type==='checkbox'||e.type==='radio'?e.checked:e.value]));
 let original=snapshot(),busy=false,uncertain=false,opener=null,lastFocused=null;
 const controls=()=>form.querySelectorAll('button[type=submit]');
 function clearFeedback(){feedback.hidden=true;feedback.querySelector('p').textContent='';feedback.querySelector('ul').replaceChildren();fields().forEach(e=>e.removeAttribute('aria-invalid'));}
 function reset(){form.reset();form.querySelector('#optionalEnabled')?.setAttribute('aria-expanded','false');window.initPatientCreateForms?.(form);form.__syncPatientCreateForm?.();clearFeedback();uncertain=false;controls().forEach(b=>b.disabled=false);form.hidden=false;success.hidden=true;discard.hidden=true;original=snapshot();}
 function askClose(){if(busy)return;if(form.hidden||snapshot()===original){dialog.close();return;}lastFocused=document.activeElement;discard.hidden=false;discard.querySelector('[data-intake-keep]').focus();}
 document.addEventListener('click',event=>{const link=event.target.closest('[data-patient-create-open],[data-booking-create-open]');if(!link||event.button!==0||event.ctrlKey||event.metaKey||event.altKey||event.shiftKey)return;if(patient?!link.matches('[data-patient-create-open]'):!link.matches('[data-booking-create-open]'))return;event.preventDefault();opener=link;const parent=link.closest('dialog');if(parent?.open)parent.close();reset();dialog.showModal();focusFirst();dialog.dispatchEvent(new Event('studio:create-open'));});
 dialog.addEventListener('cancel',event=>{event.preventDefault();askClose();});
 dialog.addEventListener('studio:request-close',event=>{event.preventDefault();askClose();});
 dialog.querySelectorAll('[data-intake-close]').forEach(button=>button.addEventListener('click',askClose));
 dialog.querySelector('[data-intake-keep]').addEventListener('click',()=>{discard.hidden=true;lastFocused?.focus();});
 dialog.querySelector('[data-intake-discard]').addEventListener('click',()=>{reset();dialog.close();});
 dialog.querySelector('[data-intake-another]').addEventListener('click',()=>{reset();focusFirst();dialog.dispatchEvent(new Event('studio:create-open'));});
 dialog.addEventListener('close',()=>opener?.focus());
 window.addEventListener('beforeunload',event=>{if(dialog.open&&(busy||(!form.hidden&&snapshot()!==original))){event.preventDefault();event.returnValue='';}});
 form.addEventListener('submit',async event=>{
  if(event.defaultPrevented)return;event.preventDefault();if(busy||uncertain)return;
  if(!form.reportValidity())return;
  const data=new FormData(form);if(event.submitter?.name)data.set(event.submitter.name,event.submitter.value);
  const name=document.querySelector('meta[name=csrf-name]')?.content,token=document.querySelector('meta[name=csrf-token]')?.content;if(name&&token)data.set(name,token);
  busy=true;clearFeedback();dialog.setAttribute('aria-busy','true');controls().forEach(b=>b.disabled=true);
  const savedName=patient?[data.get('first_name'),data.get('last_name')].filter(Boolean).join(' '):[data.get('appointment_date'),data.get('appointment_time')].filter(Boolean).join(' ');
  try{
   const response=await fetch(form.action,{method:'POST',body:data,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest',Accept:'application/json'},signal:AbortSignal.timeout(20000)});
   if(!response.headers.get('content-type')?.includes('application/json'))throw new Error('unconfirmed');
   const payload=await response.json();if(!response.ok&&payload.success===true)throw new Error('unconfirmed');
   if(typeof payload.csrf_token==='string'&&name){document.querySelectorAll('input[type=hidden]').forEach(input=>{if(input.name===name)input.value=payload.csrf_token;});const meta=document.querySelector('meta[name=csrf-token]');if(meta)meta.content=payload.csrf_token;}
   if([401,403].includes(response.status)){feedback.querySelector('p').textContent=dialog.dataset.denied;feedback.hidden=false;feedback.focus();return;}
   if(payload.success!==true){
    const errors=payload.errors;
    if(errors&&typeof errors==='object'&&!Array.isArray(errors)){
     feedback.querySelector('p').textContent=dialog.dataset.failed;
     for(const[key,message]of Object.entries(errors)){if(typeof message!=='string')continue;const li=document.createElement('li'),field=form.elements.namedItem(key),node=field instanceof Element?field:field?.[0];if(node?.id){const link=document.createElement('a');link.href='#'+node.id;link.textContent=message;link.addEventListener('click',e=>{e.preventDefault();form.querySelector('#optionalEnabled')?.setAttribute('aria-expanded','true');form.__syncPatientCreateForm?.();node.focus();});li.append(link);node.setAttribute('aria-invalid','true');}else li.textContent=message;feedback.querySelector('ul').append(li);}
     feedback.hidden=false;feedback.focus();return;
    }
    if(response.status===422&&typeof payload.message==='string'){feedback.querySelector('p').textContent=payload.message;feedback.hidden=false;feedback.focus();return;}
    throw new Error('unconfirmed');
   }
   const id=Number(patient?payload.patient_id:payload.id);if(!Number.isSafeInteger(id)||id<=0)throw new Error('unconfirmed');
   dialog.querySelector('[data-intake-saved-name]').textContent=savedName+' \u00b7 #'+id;
   const record=new URL(dialog.dataset.recordBase.replace(/\/$/,'')+'/'+id,location.origin);record.searchParams.set('return_to',dialog.dataset.returnContext||'patients');dialog.querySelector('[data-intake-record]').href=record.href;
   const book=dialog.querySelector('[data-intake-book]');if(book){const url=new URL(dialog.dataset.bookBase,location.origin);url.searchParams.set('patient_id',String(id));url.searchParams.set('return_to','patient/'+id);book.href=url.href;}
   form.hidden=true;success.hidden=false;success.focus();original=snapshot();document.dispatchEvent(new CustomEvent(patient?'studio:patient-created':'studio:appointment-created',{detail:{id}}));
  }catch(_){uncertain=true;feedback.querySelector('p').textContent=dialog.dataset.uncertain;feedback.hidden=false;feedback.focus();}
  finally{busy=false;dialog.removeAttribute('aria-busy');controls().forEach(b=>b.disabled=uncertain);}
 });
})();
