/* Shared unsaved-form behavior; canonical form submission remains server-owned. */
(() => {
const form=document.querySelector('[data-unsaved-form]');if(!form)return;
const snapshot=()=>JSON.stringify(Array.from(form.elements).filter(e=>e.name&&e.type!=='hidden'&&e.type!=='submit'&&e.type!=='button').map(e=>[e.name,e.type==='checkbox'||e.type==='radio'?e.checked:e.value]));
let original=snapshot(),leaving=false,interacted=false;const dialog=document.getElementById('form-discard-dialog');let cancelTarget=null,cancelTrigger=null;
const dirty=()=>snapshot()!==original;
form.addEventListener('input',()=>{interacted=true;});
form.addEventListener('change',()=>{interacted=true;});
const acceptInitialValues=()=>{if(!interacted&&!leaving)original=snapshot();};
document.addEventListener('DOMContentLoaded',acceptInitialValues,{once:true});
form.addEventListener('studio:initial-values-ready',acceptInitialValues);
window.addEventListener('beforeunload',e=>{if(dirty()&&!leaving){e.preventDefault();e.returnValue='';}});
form.addEventListener('submit',e=>queueMicrotask(()=>{if(!e.defaultPrevented)leaving=true;}));
document.querySelectorAll('[data-form-cancel]').forEach(a=>a.addEventListener('click',e=>{if(!dirty()||e.ctrlKey||e.metaKey||e.shiftKey||e.altKey)return;e.preventDefault();cancelTarget=a.href;cancelTrigger=a;dialog.showModal();}));
dialog.querySelector('[data-discard-stay]')?.addEventListener('click',()=>dialog.close());
dialog.querySelector('[data-discard-confirm]')?.addEventListener('click',()=>{leaving=true;window.location.assign(cancelTarget);});
dialog.addEventListener('close',()=>cancelTrigger?.focus());
window.addEventListener('pageshow',()=>{leaving=false;});
})();
