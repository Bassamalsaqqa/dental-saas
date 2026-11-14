/* Explicit presentation bindings, same existing financial handlers and payloads. */
(()=>{'use strict';
// A payment deep link opens the same authorized control as a manual click.
document.addEventListener('DOMContentLoaded',()=>{
 if(location.hash!=='#record-payment')return;
 const trigger=document.querySelector('[data-finance-open="paymentModal"]');
 const modal=document.getElementById('paymentModal');
 if(trigger&&!trigger.disabled&&trigger.getAttribute('aria-disabled')!=='true'&&modal?.matches('[data-finance-modal-root]')&&typeof window.openFinanceModal==='function'){
  history.replaceState(history.state,'',location.pathname+location.search);
  window.openFinanceModal('paymentModal',trigger);
 }
});
document.addEventListener('click',event=>{const open=event.target.closest('[data-finance-open]');if(open){event.preventDefault();const id=open.dataset.financeOpen;if(document.getElementById(id)?.matches('[data-finance-modal-root]'))window.openFinanceModal?.(id,open);return;}const close=event.target.closest('[data-finance-close]');if(close){event.preventDefault();window.closeFinanceModal?.(close.dataset.financeClose);return;}const self=event.target.closest('[data-finance-close-self]');if(self&&event.target===self)window.closeFinanceModal?.(self.dataset.financeCloseSelf);const payment=event.target.closest('[data-finance-void-payment]');if(payment){const id=Number(payment.dataset.financeVoidPayment);if(Number.isSafeInteger(id)&&id>0){event.preventDefault();window.voidPayment?.(id);}}});})();
