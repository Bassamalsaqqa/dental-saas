/* Native finance form interaction. Canonical calculator owns monetary previews; server owns persistence. */
(()=>{'use strict';
window.initStudioFinanceEntry=(root=document)=>{
  const form=root.querySelector('[data-studio-finance-entry]');
  if(!form||form.dataset.entryBound)return;
  form.dataset.entryBound='true';
  const L=JSON.parse(root.querySelector('#finance-entry-labels').textContent),
    method=form.elements.payment_method,
    status=form.querySelector('select[name=payment_status]'),
    paid=form.elements.amount_paid,
    account=form.querySelector('#expenseLiquidityAccount'),
    accountField=form.querySelector('#expenseLiquidityAccountField'),
    supplier=form.elements.supplier_id,
    payee=form.elements.counterparty_name,
    category=form.elements.expense_category,
    payeeLabel=form.querySelector('#expenseCounterpartyLabel'),
    payeeHelp=form.querySelector('#expensePayeeHelp'),
    supplierDetails=form.querySelector('.finance-optional-supplier'),
    totalLive=form.querySelector('[data-finance-total]'),
    submitBtn=form.querySelector('button[data-entry-submit]'),
    heading=document.querySelector('.finance-entry-workspace .money-heading h1'),
    methodHelp=form.querySelector('[data-expense-method-help]');

  function isPaidLocked(){
    return !paid||paid.readOnly||paid.disabled||form.hasAttribute('data-settlement-edit');
  }

  function syncCategory(){
    if(!category)return;
    const isSal=category.value==='salaries';
    if(payeeLabel)payeeLabel.textContent=isSal?(L.staffPayee||payeeLabel.dataset.labelSalary||payeeLabel.textContent):(L.vendorPayee||payeeLabel.dataset.labelPayee||payeeLabel.textContent);
    if(payee)payee.placeholder=isSal?(L.staffPayeePlaceholder||payee.dataset.placeholderSalary||''):(L.vendorPayeePlaceholder||payee.dataset.placeholderPayee||'');
    if(payeeHelp)payeeHelp.textContent=isSal?(L.staffPayeeHelp||payeeHelp.dataset.helpSalary||''):'';
    if(supplierDetails&&isSal&&!supplier?.value)supplierDetails.removeAttribute('open');
    if(submitBtn&&!form.hasAttribute('data-settlement-edit')){
      submitBtn.textContent=isSal?(L.salarySubmit||submitBtn.textContent):(L.expenseSubmit||submitBtn.textContent);
    }
    if(heading&&!form.hasAttribute('data-settlement-edit')){
      heading.textContent=isSal?(L.salaryTitle||heading.textContent):(L.expenseTitle||heading.textContent);
    }
  }
  category?.addEventListener('change',syncCategory);
  syncCategory();

  function syncPaid(){
    if(isPaidLocked()||!status)return;
    if(status.value==='paid'&&totalLive){
      paid.value=totalLive.value;
    }else if(status.value==='pending'){
      paid.value='0.00';
    }
  }

  form.addEventListener('finance:totalsUpdated',()=>{
    if(!isPaidLocked()&&status?.value==='paid'&&totalLive){
      paid.value=totalLive.value;
    }
  });

  function liquidity(){
    if(!status||!account||!method)return;
    const visible=status.value!=='pending';
    accountField.hidden=!visible;
    account.disabled=!visible;
    if(visible&&!account.value)account.value=method.value==='cash'?'safe_cash':'bank';
    if(!visible)account.value='';
  }
  function syncStatusHelp(){
    if(!methodHelp||!status)return;
    const s=status.value;
    if(s==='pending'){
      methodHelp.textContent=L.pendingMethodHelp||methodHelp.dataset.helpPending||'';
    }else if(s==='partial'){
      methodHelp.textContent=L.partialMethodHelp||methodHelp.dataset.helpPartial||'';
    }else{
      methodHelp.textContent=L.paidMethodHelp||methodHelp.dataset.helpPaid||'';
    }
  }
  method?.addEventListener('change',liquidity);
  status?.addEventListener('change',()=>{liquidity();syncPaid();syncStatusHelp();});
  paid?.addEventListener('input',liquidity);
  liquidity();
  syncStatusHelp();

  let lastSupplier='';
  supplier?.addEventListener('change',()=>{
    const name=supplier.selectedOptions[0]?.textContent?.trim()||'',current=payee?payee.value.trim():'';
    if(supplier.value&&(!current||current===lastSupplier)&&payee)payee.value=name;
    lastSupplier=supplier.value?name:'';
  });

  const patient=form.elements.patient_id,examination=form.elements.examination_id;
  if(!patient||!examination)return;
  const query=form.querySelector('[data-exam-search]'),
    feedback=form.querySelector('[data-exam-feedback]'),
    prev=form.querySelector('[data-exam-prev]'),
    next=form.querySelector('[data-exam-next]');
  let page=1,pendingReq=null,version=0;
  async function load(){
    if(!patient.value){feedback.textContent=L.empty;return;}
    pendingReq?.abort();
    pendingReq=new AbortController();
    const id=++version;
    feedback.textContent=L.loading;
    try{
      const url=new URL(examination.dataset.url,location.origin);
      url.searchParams.set('patient_id',patient.value);
      url.searchParams.set('status','all');
      url.searchParams.set('q',query.value);
      url.searchParams.set('limit','5');
      url.searchParams.set('page',String(page));
      const response=await fetch(url,{credentials:'same-origin',signal:pendingReq.signal}),data=await response.json();
      if(!response.ok||!Array.isArray(data.results))throw Error();
      if(id!==version)return;
      const placeholder=examination.options[0].cloneNode(true),current=examination.value?examination.selectedOptions[0].cloneNode(true):null;
      examination.replaceChildren(placeholder);
      if(current)examination.append(current);
      for(const record of data.results){
        if(String(record.id)===current?.value)continue;
        examination.append(new Option(String(record.text),String(record.id)));
      }
      if(current)current.selected=true;
      feedback.textContent=data.results.length?'':L.empty;
      prev.hidden=page===1;
      next.hidden=!data.pagination?.more;
    }catch(e){
      if(e.name!=='AbortError'&&id===version)feedback.textContent=L.failed;
    }
  }
  form.querySelector('[data-exam-find]')?.addEventListener('click',()=>{page=1;load();});
  query?.addEventListener('keydown',event=>{if(event.key==='Enter'){event.preventDefault();page=1;load();}});
  prev?.addEventListener('click',()=>{page=Math.max(1,page-1);load();});
  next?.addEventListener('click',()=>{page++;load();});
  patient?.addEventListener('change',()=>{
    pendingReq?.abort();
    version++;
    page=1;
    examination.replaceChildren(examination.options[0].cloneNode(true));
    if(query)query.value='';
    if(feedback)feedback.textContent='';
    if(prev)prev.hidden=true;
    if(next)next.hidden=true;
  });
};
window.initStudioFinanceEntry();
})();
