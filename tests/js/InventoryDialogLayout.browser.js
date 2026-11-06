async(page)=>{
 const p=await page.context().newPage(),results=[],errors=[];
 p.on('pageerror',e=>errors.push(e.message));
 const assert=(ok,message)=>{if(!ok)throw Error(message);};
 try{
  for(const locale of ['en','ar'])for(const width of [1366,768,390,320]){
   await p.setViewportSize({width,height:844});
   const r=await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/inventory.html`);
   assert(r.status()===200,'Inventory presenter failed');
   let writes=0;await p.route('**/inventory/**',route=>{if(route.request().method()!=='GET')writes++;return route.abort();});
   for(const action of ['receive','adjust','reorder','mapping','delete']){
    const trigger=p.locator(`[data-dialog="stock-${action}"]`);
    await trigger.click();const dialog=p.locator(`#stock-${action}`);await dialog.waitFor({state:'visible'});
    const fit=await dialog.evaluate(d=>{const r=d.getBoundingClientRect();return {left:r.left,right:r.right,width:r.width,scroll:d.scrollWidth,client:d.clientWidth};});
    assert(fit.left>=-1&&fit.right<=width+1&&fit.scroll<=fit.client+1,`Stock ${action} clips at ${locale}/${width}`);
    assert(fit.width<=Math.min(680,width-28)+1,'Dialog exceeds intended bounds');
    await p.keyboard.press('Escape');await dialog.waitFor({state:'hidden'});
    assert(await trigger.evaluate(n=>n===document.activeElement),'Stock dialog focus did not return');
   }
   assert(writes===0,'Layout checks attempted a stock mutation');await p.unroute('**/inventory/**');
   results.push({locale,width,dialogs:5,viewportFit:true,focusRestored:true,noStockWrites:true});
  }
  assert(errors.length===0,'Inventory JavaScript error: '+errors.join('; '));
  return {groups:results.length,results,pageErrors:errors,scope:'Real inventory detail view, synthetic item, dialog layout/close only; no stock writer or route authorization certification.'};
 }finally{await p.close({runBeforeUnload:false});}
}
