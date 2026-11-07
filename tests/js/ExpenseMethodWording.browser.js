// Browser test for expense payment method wording and dynamic settlement status guidance.
// No database, authentication or financial writes.
async(page)=>{
 const results=[],errors=[];
 const p=await page.context().newPage();
 p.on('pageerror',e=>errors.push(e.message));
 const assert=(ok,message)=>{if(!ok)throw Error(message);};
 try{
  for(const locale of ['en','ar'])for(const width of [1366,768,390,320]){
   await p.setViewportSize({width,height:844});
   const r=await p.goto(`http://127.0.0.1:57526/expense-${locale}.html`);
   assert(r.status()===200,`Expense presenter failed for ${locale}/${width}`);
   await p.locator('[data-studio-finance-entry]').waitFor();

   const overflow=await p.evaluate(()=>document.documentElement.scrollWidth-window.innerWidth);
   assert(overflow<=1,`Horizontal overflow ${overflow}px at ${locale}/${width}`);

   const labels=await p.locator('#finance-entry-labels').textContent().then(JSON.parse);
   assert(labels.pendingMethodHelp&&labels.paidMethodHelp&&labels.partialMethodHelp,'Missing method help labels');

   const methodHelp=p.locator('#expensePaymentMethodHelp');
   const methodSelect=p.locator('#paymentMethod');
   assert(await methodSelect.getAttribute('aria-describedby')==='expensePaymentMethodHelp','Payment method missing aria-describedby');
   assert(await methodSelect.getAttribute('required')!==null,'Unpaid record requirement was silently removed');
   await methodSelect.selectOption('bank_transfer');

   // 1. Initial default state is paid
   assert(await methodHelp.innerText()===labels.paidMethodHelp,`Initial help text mismatch at ${locale}/${width}`);

   // 2. Switch to pending (unpaid)
   await p.locator('#entry-payment-status').selectOption('pending');
   assert(await methodHelp.innerText()===labels.pendingMethodHelp,`Pending help text mismatch after switch at ${locale}/${width}`);
   assert(await p.locator('#expenseLiquidityAccountField').isHidden(),'Liquidity account should be hidden for pending');

   // 3. Switch to partial
   await p.locator('#entry-payment-status').selectOption('partial');
   assert(await methodHelp.innerText()===labels.partialMethodHelp,`Partial help text mismatch after switch at ${locale}/${width}`);
   assert(await p.locator('#expenseLiquidityAccountField').isVisible(),'Liquidity account should be visible for partial');

   // 4. Switch back to paid
   await p.locator('#entry-payment-status').selectOption('paid');
   assert(await methodHelp.innerText()===labels.paidMethodHelp,`Paid help text mismatch after switch back at ${locale}/${width}`);
   assert(await p.locator('#expenseLiquidityAccountField').isVisible(),'Liquidity account should be visible for paid');
   assert(await methodSelect.inputValue()==='bank_transfer','Status help overwrote the chosen payment method');

   // 5. Validation reopen / initial pending state (server-rendered without requiring JS change event)
   const rPending=await p.goto(`http://127.0.0.1:57526/expense-pending-${locale}.html`);
   assert(rPending.status()===200,`Expense pending presenter failed for ${locale}/${width}`);
   await p.locator('[data-studio-finance-entry]').waitFor();
   const initialPendingText=await p.locator('#expensePaymentMethodHelp').innerText();
   assert(initialPendingText===labels.pendingMethodHelp,`Initial pending server-rendered text mismatch at ${locale}/${width}`);
   const statusVal=await p.locator('#entry-payment-status').inputValue();
   assert(statusVal==='pending',`Expected pending status value at ${locale}/${width}`);

   results.push({
    locale,
    width,
    initialPaidHelp:true,
    dynamicPendingHelp:true,
    dynamicPartialHelp:true,
    dynamicBackToPaidHelp:true,
    serverRenderedPendingInitial:true,
    zeroOverflow:true
   });
  }
  const noJsContext=await page.context().browser().newContext({javaScriptEnabled:false});
  try{
   const noJs=await noJsContext.newPage();
   for(const locale of ['en','ar']){
    await noJs.goto(`http://127.0.0.1:57526/expense-pending-${locale}.html`);
    const labels=JSON.parse(await noJs.locator('#finance-entry-labels').textContent());
    assert(await noJs.locator('#expensePaymentMethodHelp').textContent()===labels.pendingMethodHelp,'No-JS initial unpaid wording is incorrect');
    assert(await noJs.locator('#entry-payment-status').inputValue()==='pending','No-JS pending re-entry was lost');
   }
  }finally{await noJsContext.close();}
  assert(errors.length===0,'Expense method wording page errors: '+errors.join('; '));
  return {groups:results.length,results,noJsLocaleCases:2,pageErrors:errors,scope:'Synthetic expense payment method wording and status switching; zero DB or financial mutations.'};
 }finally{await p.close({runBeforeUnload:false});}
}
