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
const pendingText=form.dataset.pendingText;
const nativePending=form.matches('[data-patient-create-form]')&&form.method.toLowerCase()==='post'&&Boolean(pendingText);
let activeSubmission=null,submissionEvent=null;
const restoreAttribute=(element,name,value)=>{if(value===null)element.removeAttribute(name);else element.setAttribute(name,value);};
const restorePendingState=()=>{
    if(!activeSubmission)return;
    const {buttons,busy}=activeSubmission;
    if(Array.isArray(buttons)){
        buttons.forEach(({button,nodes,disabled})=>{
            button.replaceChildren(...nodes);
            restoreAttribute(button,'aria-disabled',disabled);
        });
    }
    restoreAttribute(form,'aria-busy',busy);
    activeSubmission=null;
};
form.addEventListener('submit',e=>{
    if(nativePending&&(activeSubmission||submissionEvent)){
        e.preventDefault();
        e.stopImmediatePropagation();
        return;
    }
    if(nativePending)submissionEvent=e;
    // Later field/form/document handlers may still cancel this event.
    queueMicrotask(()=>{
        if(submissionEvent===e)submissionEvent=null;
        if(e.defaultPrevented)return;
        leaving=true;
        if(!nativePending)return;
        const buttons=Array.from(form.querySelectorAll('button[type="submit"]'));
        const submitter=e.submitter||buttons[0]||null;
        activeSubmission={
            buttons:buttons.map(btn=>({
                button:btn,
                nodes:[...btn.childNodes],
                disabled:btn.getAttribute('aria-disabled')
            })),
            busy:form.getAttribute('aria-busy')
        };
        form.setAttribute('aria-busy','true');
        buttons.forEach(btn=>{
            btn.setAttribute('aria-disabled','true');
            // Keep name/value enabled so the clicked action remains a successful control.
            if(btn===submitter&&!btn.querySelector('input,select,textarea')){
                btn.replaceChildren(document.createTextNode(pendingText));
            }
        });
    });
});
form.addEventListener('click',e=>{
    const btn=e.target.closest('button[type="submit"]');
    if(nativePending&&btn&&(activeSubmission||submissionEvent)){
        e.preventDefault();
        e.stopImmediatePropagation();
    }
},true);
form.addEventListener('keydown',e=>{
    if((e.key==='Enter'||e.key===' ')&&nativePending&&(activeSubmission||submissionEvent)){
        const btn=e.target.closest('button[type="submit"]');
        if(btn){
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }
},true);
document.querySelectorAll('[data-form-cancel]').forEach(a=>a.addEventListener('click',e=>{if(!dirty()||e.ctrlKey||e.metaKey||e.shiftKey||e.altKey)return;e.preventDefault();cancelTarget=a.href;cancelTrigger=a;dialog.showModal();}));
dialog.querySelector('[data-discard-stay]')?.addEventListener('click',()=>dialog.close());
dialog.querySelector('[data-discard-confirm]')?.addEventListener('click',()=>{leaving=true;window.location.assign(cancelTarget);});
dialog.addEventListener('close',()=>cancelTrigger?.focus());
window.addEventListener('pageshow',()=>{
    leaving=false;
    submissionEvent=null;
    restorePendingState();
});
})();
