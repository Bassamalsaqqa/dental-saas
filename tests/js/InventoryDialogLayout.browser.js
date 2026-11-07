async(page)=>{
 const p=await page.context().newPage(),results=[],errors=[];
 p.on('pageerror',e=>errors.push(e.message));
 const assert=(ok,message)=>{if(!ok)throw Error(message);};
 try{
  for(const locale of ['en','ar'])for(const width of [1366,768,390,320]){
   await p.setViewportSize({width,height:844});
   const r=await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/inventory.html`);
   assert(r.status()===200,'Inventory presenter failed');
   assert(await p.locator('#application').count()===1,'Inventory shell is duplicated');
   let writes=0;await p.route('**/inventory/**',route=>{if(route.request().method()!=='GET')writes++;return route.abort();});
   for(const action of ['receive','adjust','reorder','mapping','delete']){
    const trigger=p.locator(`[data-dialog="stock-${action}"]`);
    await trigger.click();const dialog=p.locator(`#stock-${action}`);await dialog.waitFor({state:'visible'});
    const fit=await dialog.evaluate(d=>{const r=d.getBoundingClientRect();return {left:r.left,right:r.right,width:r.width,scroll:d.scrollWidth,client:d.clientWidth};});
    assert(fit.left>=-1&&fit.right<=width+1&&fit.scroll<=fit.client+1,`Stock ${action} clips at ${locale}/${width}`);
    assert(fit.width<=Math.min(680,width-28)+1,'Dialog exceeds intended bounds');

    // Confinement: Shift+Tab on first element wraps inside dialog
    await dialog.evaluate(d=>{
     const f=Array.from(d.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),summary,[tabindex="0"]')).filter(e=>e.getClientRects().length);
     f[0].focus();
    });
    await p.keyboard.press('Shift+Tab');
    assert(await dialog.evaluate(d=>d.contains(document.activeElement)),`Stock ${action} Shift+Tab escaped dialog at ${locale}/${width}`);

    // Confinement: Tab on last element wraps inside dialog
    await dialog.evaluate(d=>{
     const f=Array.from(d.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),summary,[tabindex="0"]')).filter(e=>e.getClientRects().length);
     f[f.length-1].focus();
    });
    await p.keyboard.press('Tab');
    assert(await dialog.evaluate(d=>d.contains(document.activeElement)),`Stock ${action} Tab escaped dialog at ${locale}/${width}`);

    // Client invalid-field announcement & protection
    if(action==='receive'){
     const qty=dialog.locator('[name="quantity"]');
     await qty.fill('0');
     await dialog.locator('button[type="submit"]').click();
     assert(await qty.getAttribute('aria-invalid')==='true',`Invalid field did not set aria-invalid on ${action}`);
     assert(writes===0,`Invalid submission initiated write on ${action}`);
     await qty.fill('5');
     assert(await qty.getAttribute('aria-invalid')===null,`Valid field did not clear aria-invalid on ${action}`);
    }

    await p.keyboard.press('Escape');await dialog.waitFor({state:'hidden'});
    assert(await trigger.evaluate(n=>n===document.activeElement),'Stock dialog focus did not return');
   }
   assert(writes===0,'Layout checks attempted a stock mutation');await p.unroute('**/inventory/**');
   results.push({locale,width,dialogs:5,viewportFit:true,confinement:true,focusRestored:true,noStockWrites:true});
  }
  assert(errors.length===0,'Inventory JavaScript error: '+errors.join('; '));
  return {groups:results.length,results,pageErrors:errors,scope:'Real inventory detail view, synthetic item, dialog layout, tab confinement, invalid-announcement & close; no stock writer or route authorization certification.'};
 }finally{await p.close({runBeforeUnload:false});}
}
