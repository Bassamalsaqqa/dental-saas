async(page)=>{
 const results=[],errors=[];
 const p=await page.context().newPage();p.on('pageerror',e=>errors.push(e.message));
 p.on('dialog',dialog=>dialog.type()==='beforeunload'?dialog.accept():dialog.dismiss());
 const assert=(ok,message)=>{if(!ok)throw Error(message);};
 try{
  for(const locale of ['en','ar'])for(const width of [1366,768,390,320])for(const mode of ['page','dialog']){
   await p.setViewportSize({width,height:844});
   let writes=0;
   await p.route('**/patient/store',route=>{writes++;return route.abort();});
   await p.route('**/api/search/patients?*',route=>route.fulfill({contentType:'application/json',body:JSON.stringify({results:[{id:901,text:'Synthetic Existing Patient (0599000000)'}]})}));
   const response=await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/${mode}.html`);
   assert(response.status()===200,'Fixture render failed');
   if(mode==='dialog')await p.locator('[data-patient-create-open]').click();
   const form=p.locator('[data-patient-create-form]');
   await form.locator('[name=first_name]').fill('Draft name');
   const trigger=form.locator('[data-dialog="search-dialog"]');
   assert(await trigger.isVisible(),'Existing-patient action is not visible');
   assert(await trigger.getAttribute('type')==='button','Search can submit registration');
   await trigger.click();
   const search=p.locator('#search-dialog');await search.waitFor({state:'visible'});
   assert(await p.locator('#patient-search').evaluate(n=>n===document.activeElement),'Search focus missing');
   await p.locator('#patient-search').fill('Synthetic');
   await p.locator('#search-results a').waitFor();
   assert((await p.locator('#search-results a').getAttribute('href')).endsWith('/patient/901'),'Canonical patient link lost');
   await p.keyboard.press('Escape');await search.waitFor({state:'hidden'});
   assert(await form.locator('[name=first_name]').inputValue()==='Draft name','Search erased entered data');
   assert(await trigger.evaluate(n=>n===document.activeElement),'Focus did not return to registration');
   if(mode==='dialog')assert(await p.locator('#patient-intake-dialog').evaluate(n=>n.open),'Search closed intake dialog');
   assert(writes===0,'Search attempted patient creation');
   const bounds=await trigger.boundingBox();
   assert(bounds&&bounds.x>=0&&bounds.x+bounds.width<=width+1,`Search action does not fit ${locale}/${width}/${mode}`);
   results.push({locale,width,mode,existingSearch:true,draftPreserved:true,focusRestored:true,noRegistrationWrite:true});
   // Close without invoking intake discard: this synthetic page is simply replaced next iteration.
   await p.unroute('**/patient/store');await p.unroute('**/api/search/patients?*');
  }
  for(const locale of ['en','ar'])for(const mode of ['page','dialog','edit']){
   await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/${mode}.html?permission=denied`);
   assert(await p.locator('[data-dialog="search-dialog"]').count()===0,'Search action exposed without patient-view permission');
   if(mode==='edit'){
    await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/edit.html`);
    assert(await p.locator('[data-dialog="search-dialog"]').count()===0,'Registration hint leaked into patient editing');
   }
  }
  assert(errors.length===0,'JavaScript errors: '+errors.join('; '));
  return {groups:results.length,results,permissionAndEditingCases:6,pageErrors:errors,scope:'Synthetic registration forms and existing search; no DB or live submission.'};
 }finally{await p.close({runBeforeUnload:false});}
}
