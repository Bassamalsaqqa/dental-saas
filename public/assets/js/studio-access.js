/* Progressive password control. Native names, values, validation and submit contracts are unchanged. */
(()=>{'use strict';const config=document.getElementById('studio-password-labels');if(!config)return;
const ns='http://www.w3.org/2000/svg';let sequence=0;
function enhance(root){[...(root.matches?.('input[type=password]')?[root]:[]),...root.querySelectorAll('input[type=password]')].forEach(field=>{
 if(field.closest('.studio-password'))return;
 if(!field.id)field.id='studio-password-'+(++sequence);
 field.spellcheck=false;field.autocapitalize='off';
 const label=Array.from(field.labels||[]).map(e=>e.textContent.trim()).join(' '),wrap=document.createElement('div'),button=document.createElement('button'),svg=document.createElementNS(ns,'svg'),path=document.createElementNS(ns,'path'),slash=document.createElementNS(ns,'path'),caption=document.createElement('span');
 wrap.className='studio-password';button.type='button';button.className='studio-password-toggle';button.setAttribute('aria-controls',field.id);
 svg.setAttribute('viewBox','0 0 24 24');svg.setAttribute('fill','none');svg.setAttribute('stroke','currentColor');svg.setAttribute('stroke-width','1.7');svg.setAttribute('aria-hidden','true');path.setAttribute('d','M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Zm10-3a3 3 0 1 0 0 6 3 3 0 0 0 0-6');slash.setAttribute('d','m4 3 16 18');svg.append(path,slash);button.append(svg,caption);caption.className='studio-password-caption';
 function state(){const shown=field.type==='text',text=shown?config.dataset.hide:config.dataset.show;caption.textContent=shown?config.dataset.hideShort:config.dataset.showShort;button.disabled=field.disabled;button.title=text;button.setAttribute('aria-label',text+(label?' — '+label:''));slash.style.display=shown?'':'none';}
 field.before(wrap);wrap.append(field,button);state();
 button.addEventListener('click',()=>{const start=field.selectionStart,end=field.selectionEnd;field.type=field.type==='password'?'text':'password';state();field.focus({preventScroll:true});if(start!==null)field.setSelectionRange(start,end);});
 field.form?.addEventListener('submit',()=>{field.type='password';state();},true);
 });}

// Native navigation forms: pending feedback after validation/other handlers; never disable successful form fields.
const submissions=new Map();
document.addEventListener('submit',event=>{const form=event.target;if(form.method.toLowerCase()!=='post'||form.closest('dialog'))return;if(submissions.has(form)){event.preventDefault();event.stopImmediatePropagation();return;}setTimeout(()=>{if(event.defaultPrevented||submissions.has(form))return;const button=event.submitter;if(!button||button.querySelector('input'))return;submissions.set(form,{button,nodes:[...button.childNodes],label:button.getAttribute('aria-label')});form.setAttribute('aria-busy','true');button.setAttribute('aria-disabled','true');button.textContent=config.dataset.pending;},0);},true);
window.addEventListener('pageshow',()=>{submissions.forEach(({button,nodes,label},form)=>{button.replaceChildren(...nodes);button.removeAttribute('aria-disabled');if(label!==null)button.setAttribute('aria-label',label);form.removeAttribute('aria-busy');});submissions.clear();});
 enhance(document);new MutationObserver(records=>{for(const record of records)for(const node of record.addedNodes)if(node.nodeType===1&&!node.closest('.studio-password'))enhance(node);}).observe(document.body,{childList:true,subtree:true});
})();
