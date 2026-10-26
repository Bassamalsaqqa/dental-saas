/* Presentation-only modal accessibility. Original settlement and provider handlers remain authoritative. */
(()=>{'use strict';
const modals=[...document.querySelectorAll('[data-finance-modal-root]')],previous=new Map();
for(const [index,form] of [...document.querySelectorAll('.finance-inline-modal-form')].entries()){
 for(const [fieldIndex,label] of [...form.querySelectorAll('label')].entries()){
  if(label.htmlFor)continue;
  const field=label.querySelector('input,select,textarea')||label.parentElement.querySelector('input:not([type=hidden]),select,textarea');
  if(!field)continue;
  field.id=field.id||'finance-task-'+index+'-field-'+fieldIndex;label.htmlFor=field.id;
 }
}
function isolate(){const open=modals.find(modal=>!modal.classList.contains('hidden'));
 if(open){for(const node of document.body.children){if(modals.includes(node)||['SCRIPT','STYLE','LINK'].includes(node.tagName))continue;if(!previous.has(node))previous.set(node,node.inert);node.inert=true;}}
 else {for(const [node,inert]of previous)node.inert=inert;previous.clear();}
}
for(const modal of modals)new MutationObserver(isolate).observe(modal,{attributes:true,attributeFilter:['class']});
})();
