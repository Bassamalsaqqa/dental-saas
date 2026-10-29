(()=>{'use strict';const csrf=()=>({header:document.querySelector('meta[name="csrf-header"]').content,token:document.querySelector('meta[name="csrf-token"]')}),errorText=document.getElementById('management-confirm')?.dataset.error||'Request failed';async function submit(form,url,body){const button=form.querySelector('[type=submit]'),error=form.querySelector('[data-staff-error]');if(button.disabled)return;button.disabled=true;error.hidden=true;try{const security=csrf();const response=await fetch(url,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest',[security.header]:security.token.content},body});const data=await response.json();if(data.csrf_token)security.token.content=data.csrf_token;if(!response.ok||!data.success)throw Error(data.message||errorText);location.reload();}catch(e){error.textContent=e instanceof SyntaxError||e.message==='Failed to fetch'?errorText:e.message;error.hidden=false;button.disabled=false;}}document.querySelector('[data-staff-role]')?.addEventListener('submit',event=>{event.preventDefault();submit(event.currentTarget,event.currentTarget.action,new FormData(event.currentTarget));});const dialog=document.getElementById('staff-payout-dialog');if(!dialog)return;
const form=dialog.querySelector('form'),amount=form.elements.amount,method=form.elements.payment_method,liquidity=form.elements.liquidity_account;let payoutId='';
const sync=()=>{const check=method.value==='check';document.getElementById('payoutLiquidityAccountField').hidden=!check;liquidity.disabled=!check;};
document.querySelectorAll('[data-staff-payout]').forEach(button=>button.addEventListener('click',()=>{
 payoutId=button.dataset.id;form.dataset.settlementUrl=dialog.dataset.base.replace(/\/$/,'')+'/'+payoutId+'/payments';form.reset();amount.max=button.dataset.max;amount.value=button.dataset.max;
 document.getElementById('payoutAmountHelp').textContent=dialog.dataset.maxLabel+': '+button.dataset.max+' '+button.dataset.currency;
 form.elements.paid_date.value=dialog.dataset.today;
 const facts=JSON.parse(button.dataset.payoutContext);dialog.querySelector('[data-staff-transaction]').textContent=facts.transaction;dialog.querySelector('[data-staff-patient]').textContent=facts.patient;
 for(const key of ['share','paid','due'])dialog.querySelector('[data-staff-fact='+key+']').textContent=facts[key];
 form.querySelector('[data-finance-reconcile]').href=button.dataset.financeUrl;form.querySelector('.finance-task-feedback').hidden=true;
 form.querySelector('[type=submit]').disabled=false;sync();window.StudioFinanceTask?.open(dialog.id,button,true);
}));
method.addEventListener('change',sync);form.addEventListener('submit',event=>{
 event.preventDefault();if(!form.reportValidity())return;
 window.StudioFinanceTask?.submit(form,dialog.dataset.base.replace(/\/$/,'')+'/'+payoutId+'/payments',data=>data.set('amount',Number(amount.value).toFixed(2)));
});})();
