async (page) => {
  // Run against the isolated presenter server only; requests below never reach production.
  const results=[];
  for(const locale of ['en','ar']) for(const outcome of ['failure','late-success']) {
    const p=await page.context().newPage();
    let release; const gate=new Promise(resolve=>release=resolve); let requests=0;
    try {
      await p.setViewportSize({width:390,height:844});
      await p.route('**/finance/settlement-context?*',async route=>{
        if(++requests!==1) {await route.continue();return;}
        await gate;
        if(outcome==='failure') await route.fulfill({status:503,body:'Synthetic context rejection'});
        else await route.fulfill({contentType:'application/json',body:JSON.stringify({success:true,context:{request_key:'synthetic-late',settlement_revision:'old',transaction_id:'OLD',currency:'USD',facts:{total:'999.00',paid:'0.00',due:'999.00'}}})});
      });
      await p.goto('http://127.0.0.1:57526/payouts-'+locale+'.html');
      await p.locator('#money-rows tr[data-review-key]').first().locator('a,button').first().click();
      await p.waitForFunction(()=>document.querySelector('#money-payout-form').hasAttribute('aria-busy'));
      await p.keyboard.press('Escape');
      await p.locator('#money-record-drawer').waitFor({state:'hidden'});
      if(outcome==='failure') {release();await p.waitForTimeout(100);}
      await p.locator('#money-rows tr[data-review-key]').nth(1).locator('a,button').first().click();
      await p.waitForFunction(()=>document.querySelector('#money-record-drawer').open&&!document.querySelector('#money-payout-form').hasAttribute('aria-busy'));
      release();await p.waitForTimeout(100);
      const amount=p.locator('#money-payout-amount');
      if(!await amount.isEnabled()||await amount.inputValue()!=='150.00'||await amount.getAttribute('max')!=='150.00'||(await p.locator('[data-money-outstanding]').textContent())!=='150.00 ILS') throw Error(locale+' '+outcome+': closed context contaminated or blocked new task');
      results.push({locale,outcome,reopened:true,newContextPreserved:true});
    } finally {release();await p.close({runBeforeUnload:false});}
  }
  for(const locale of ['en','ar']) {
    const p=await page.context().newPage(); let release;const gate=new Promise(resolve=>release=resolve);let writes=0;
    try {
      await p.route('**/finance/payouts/1/payments',async route=>{writes++;await gate;await route.abort('failed');});
      await p.goto('http://127.0.0.1:57526/payouts-'+locale+'.html');
      await p.locator('#money-rows tr[data-review-key]').first().locator('a,button').first().click();
      await p.waitForFunction(()=>!document.querySelector('#money-payout-form').hasAttribute('aria-busy'));
      await p.locator('button[form="money-payout-form"][type="submit"]').click();
      await p.waitForFunction(()=>document.querySelector('#money-payout-form').hasAttribute('aria-busy'));
      await p.keyboard.press('Escape');
      if(!await p.locator('#money-record-drawer').evaluate(e=>e.open))throw Error('Pending payment could be dismissed');
      release();
      await p.waitForFunction(()=>!document.querySelector('#money-payout-form').hasAttribute('aria-busy'));
      await p.keyboard.press('Escape');
      if(!await p.locator('#money-record-drawer').evaluate(e=>e.open)||await p.locator('#money-payout-amount').isEnabled()||!await p.locator('[data-finance-reconcile]').isVisible()||writes!==1)throw Error('Uncertain payment lost its submission/history protection');
      results.push({locale,pendingProtected:true,uncertainProtected:true,noAutomaticRetry:true});
    } finally {release();await p.close({runBeforeUnload:false});}
  }
  for(const locale of ['en','ar']) for(const width of [1366,768,390,320]) {
    for(const index of [0,2,3,4]) {
      const p=await page.context().newPage();
      try {
        await p.setViewportSize({width,height:844});
        await p.goto('http://127.0.0.1:57526/payouts-'+locale+'.html');
        await p.locator('#money-rows tr[data-review-key]').nth(index).locator('a,button').first().click();
        await p.waitForFunction(()=>!document.querySelector('#money-payout-form').hasAttribute('aria-busy'));
        const state=await p.evaluate(()=>{
          const drawer=document.querySelector('#money-record-drawer'),warning=drawer.querySelector('[data-money-record-warning]');
          return {overflow:document.documentElement.scrollWidth>innerWidth+1,fits:drawer.getBoundingClientRect().width<=innerWidth+1,formHidden:drawer.querySelector('form').hidden,warningVisible:!!warning.getClientRects().length,warningOutsideDetails:!warning.closest('details'),history:!!drawer.querySelector('a[href$="#provider-payouts"]'),pairs:Array.from(document.querySelectorAll('.money-native-amount')).every(e=>e.scrollWidth<=e.clientWidth+1)};
        });
        if(state.overflow||!state.fits||!state.pairs||!state.history||!state.warningOutsideDetails||state.formHidden!==[2,4].includes(index)||state.warningVisible!==[3,4].includes(index))throw Error(JSON.stringify({locale,width,index,state}));
      } finally {await p.close({runBeforeUnload:false});}
    }
    results.push({locale,width,unpaidPaidUnknownVoid:true,mixedCurrencyPairsIntact:true,noViewportOverflow:true});
  }
  return results;
}
