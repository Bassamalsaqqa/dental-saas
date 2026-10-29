/* Server-issued financial preconditions. No local money calculation or automatic mutation retry. */
(()=>{'use strict';const node=document.getElementById('studio-settlement-config');if(!node)return;const config=JSON.parse(node.textContent);
async function issue(operation,targets){const url=new URL(config.url,location.origin);url.searchParams.set('operation',operation);url.searchParams.set('targets',targets.join(','));const response=await fetch(url,{credentials:'same-origin',cache:'no-store',headers:{'X-Requested-With':'XMLHttpRequest'},signal:AbortSignal.timeout(15000)});if(response.redirected||!response.ok)throw Error(config.labels.settlementConflictHelp);const body=await response.json();if(!body.success||!body.context?.request_key||!body.context?.settlement_revision)throw Error(config.labels.settlementConflictHelp);return body.context;}
function fields(data,context){if(data instanceof FormData||data instanceof URLSearchParams){data.set('request_key',context.request_key);data.set('settlement_revision',context.settlement_revision);}else{data.request_key=context.request_key;data.settlement_revision=context.settlement_revision;}return data;}
function bind(form,context){for(const key of ['request_key','settlement_revision']){let input=form.elements.namedItem(key);if(!input){input=document.createElement('input');input.type='hidden';input.name=key;form.append(input);}input.value=context[key];}}
function present(form,context){
 const modal=form.closest('dialog')||form,facts=context.facts||{},unit=context.currency?' '+context.currency:'';
 let card=modal.querySelector('.finance-task-context');
 if(!card&&modal.tagName==='DIALOG'){
  card=document.createElement('section');card.className='finance-task-context';
  const identity=document.createElement('div');identity.className='finance-task-identity';const name=document.createElement('strong');name.textContent=context.transaction_id;identity.append(name);card.append(identity);
  const list=document.createElement('dl');
  for(const [key,label]of [['total',config.labels.total],['paid',config.labels.paid],[facts.refundable!==undefined?'refundable':'due',facts.refundable!==undefined?config.labels.refundable:config.labels.due]]){const row=document.createElement('div'),term=document.createElement('dt'),definition=document.createElement('dd'),value=document.createElement('bdi');term.textContent=label;value.dataset.settlementFact=key;definition.append(value);row.append(term,definition);list.append(row);}
  card.append(list);form.prepend(card);
 }
 if(card){card.dataset.currentSettlementFacts='';card.setAttribute('aria-live','polite');const help=card.querySelector('p');if(help)help.textContent=config.labels.settlementReady;}

 for(const [key,value]of Object.entries(facts)){
  for(const node of modal.querySelectorAll('[data-settlement-fact="'+key+'"],[data-supplier-fact="'+key+'"],[data-staff-fact="'+key+'"]'))node.textContent=value+unit;
 }
 for(const node of modal.querySelectorAll('[data-supplier-fact=amount],[data-staff-fact=share]'))node.textContent=facts.total+unit;
 for(const node of modal.querySelectorAll('[data-money-outstanding]'))node.textContent=facts.due+unit;
 const amount=form.elements.namedItem('amount');if(amount&&facts.due!==undefined){amount.max=facts.refundable??facts.due;}
 for(const node of modal.querySelectorAll('[data-payout-maximum],#supplierPaymentHelp,#payoutAmountHelp'))node.textContent=config.labels.due+': '+facts.due+unit;
}
function scope(url){const destination=new URL(url,location.href);if(destination.origin!==location.origin||destination.username||destination.password)return null;const path=destination.pathname,base=new URL(config.base,location.href).pathname.replace(/\/$/,'');if(!path.startsWith(base+'/'))return null;const rest=path.slice(base.length+1);let match;if((match=rest.match(/^payouts\/(\d+)\/(payments|mark-paid)$/)))return{operation:match[2]==='payments'?'provider_payment':'provider_mark_paid',targets:[match[1]]};if((match=rest.match(/^(\d+)\/(payments(?:\/refund|\/\d+\/void)?|mark-paid|close-balance|update)$/))){const suffix=match[2],operation=suffix==='payments'?'payment':suffix==='payments/refund'?'refund':suffix.endsWith('/void')?'void':suffix==='mark-paid'?'mark_paid':suffix==='close-balance'?'close_balance':'update';return{operation,targets:[match[1]]};}return null;}
async function forUrl(url){const target=scope(url);if(!target)throw Error(config.labels.settlementConflictHelp);return issue(target.operation,target.targets);}
// Full-page expense edits preserve native submission/validation and uploaded content.
for(const form of document.querySelectorAll('form[data-settlement-edit][action]')){const target=scope(form.action);if(target?.operation!=='update')continue;let ready=false;const submits=Array.from(form.elements).filter(e=>e.type==='submit'),disabled=submits.map(e=>e.disabled);submits.forEach(e=>e.disabled=true);form.addEventListener('submit',event=>{if(!ready)event.preventDefault();});issue(target.operation,target.targets).then(context=>{if(form.dataset.settlementParentVersion!==context.parent_version)throw Error(config.labels.settlementConflictHelp);bind(form,context);present(form,context);const facts=document.createElement('p');facts.className='form-notice';facts.setAttribute('role','status');facts.textContent=describe(context);form.prepend(facts);ready=true;submits.forEach((e,i)=>e.disabled=disabled[i]);}).catch(()=>{const box=document.createElement('p');box.className='form-notice form-notice--error';box.setAttribute('role','alert');box.textContent=config.labels.settlementConflictHelp;form.prepend(box);});}
function describe(context){const f=context.facts||{},l=config.labels;return l.current+' '+context.transaction_id+' | '+l.total+': '+(f.total||'-')+' | '+l.paid+': '+(f.paid||'-')+' | '+l.due+': '+(f.due||'-')+(f.refundable!==undefined?' ? '+l.refundable+': '+f.refundable:'');}
window.StudioSettlement={issue,fields,bind,scope,forUrl,describe,present,labels:config.labels};})();
