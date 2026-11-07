async(page)=>{
 const results=[],errors=[];
 const p=await page.context().newPage();p.on('pageerror',e=>errors.push(e.message));
 p.on('dialog',dialog=>dialog.type()==='beforeunload'?dialog.accept():dialog.dismiss());
 const assert=(ok,message)=>{if(!ok)throw Error(message);};
 try{
  for(const locale of ['en','ar'])for(const width of [1366,768,390,320])for(const mode of ['page','dialog','fullshell']){
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
   await p.waitForFunction(()=>document.activeElement===document.querySelector('#patient-search'),{},{timeout:2000});
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
   const fit=await form.evaluate(form=>{const r=form.getBoundingClientRect();return {fields:[...form.querySelectorAll('.registration-basics,.registration-fields>.form-field')].every(n=>{const b=n.getBoundingClientRect();return b.left>=r.left-1&&b.right<=r.right+1&&n.scrollWidth<=n.clientWidth+1;}),paired:Math.abs(form.querySelector('[name=first_name]').getBoundingClientRect().top-form.querySelector('[name=last_name]').getBoundingClientRect().top)<1};});
   assert(fit.fields,`Registration fields clipped ${locale}/${width}/${mode}`);
   if(width<=390)assert(fit.paired,'Accepted paired name layout changed');
   assert(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'Document overflow');
   results.push({locale,width,mode,fieldFit:true,existingSearch:true,draftPreserved:true,focusRestored:true,noRegistrationWrite:true});
   await p.unroute('**/patient/store');await p.unroute('**/api/search/patients?*');
  }

  // 1. Search states: empty, loading, error, denied, late response
  for(const locale of ['en','ar']){
   await p.setViewportSize({width:1366,height:844});
   await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/page.html`);
   const trigger=p.locator('[data-dialog="search-dialog"]');
   const search=p.locator('#search-dialog');
   const searchInput=p.locator('#patient-search');
   const searchStatus=p.locator('[data-search-status]');
   const searchResults=p.locator('#search-results');

   // A. Empty results
   const expectedEmpty=await searchInput.getAttribute('data-empty');
   await p.route('**/api/search/patients?*',async route=>{
    return route.fulfill({contentType:'application/json',body:JSON.stringify({results:[]})});
   });
   await trigger.click();await search.waitFor({state:'visible'});
   await searchInput.fill('NoMatchPatient');
   await p.waitForFunction((exp)=>document.querySelector('#search-results p')?.textContent?.includes(exp),expectedEmpty);
   const emptyText=await searchResults.locator('p').textContent();
   assert(emptyText===expectedEmpty,'Empty results text mismatch: '+emptyText);
   await p.unroute('**/api/search/patients?*');

   // B. Error response (500)
   const expectedError=await searchInput.getAttribute('data-error');
   await p.route('**/api/search/patients?*',route=>route.fulfill({status:500,body:'Server Error'}));
   await searchInput.fill('CauseErrorQuery');
   await p.waitForFunction((exp)=>document.querySelector('[data-search-status]')?.textContent?.includes(exp),expectedError);
   const errorStatus=await searchStatus.textContent();
   assert(errorStatus.includes(expectedError),'Error status mismatch: '+errorStatus);
   await p.unroute('**/api/search/patients?*');

   // C. Denied response (403)
   const expectedDenied=await searchStatus.getAttribute('data-denied');
   await p.route('**/api/search/patients?*',route=>route.fulfill({status:403,contentType:'application/json',body:JSON.stringify({error:'Forbidden'})}));
   await searchInput.fill('DeniedPatient');
   await p.waitForFunction((exp)=>document.querySelector('[data-search-status]')?.textContent?.includes(exp),expectedDenied);
   const deniedStatus=await searchStatus.textContent();
   assert(deniedStatus===expectedDenied,'Denied status mismatch: '+deniedStatus);
   await p.unroute('**/api/search/patients?*');

   // D. Late response after search dialog closed
   let startLate,releaseLate;
   const started=new Promise(resolve=>startLate=resolve),held=new Promise(resolve=>releaseLate=resolve);
   await p.route('**/api/search/patients?*',async route=>{
    startLate();await held;
    await route.fulfill({contentType:'application/json',body:JSON.stringify({results:[{id:999,text:'Late Patient'}]})}).catch(()=>{});
   });
   await searchInput.fill('LateQuery');await started;
   assert(await searchResults.getAttribute('aria-busy')==='true','Search loading state missing');
   const beforeLate=await searchResults.textContent();
   await p.keyboard.press('Escape');await search.waitFor({state:'hidden'});
   releaseLate();await p.unrouteAll({behavior:'wait'});
   assert(await searchResults.getAttribute('aria-busy')!=='true','Search dialog remained aria-busy after late response');
   assert(await searchResults.textContent()===beforeLate,'Closed search was overwritten by late results');
   await p.unroute('**/api/search/patients?*');
  }

  // 2. Unsaved forms and discard handling: Full-Page and Dialog
  for(const locale of ['en','ar']){
   // Full-page cancel / keep / discard
   await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/page.html`);
   const form=p.locator('[data-patient-create-form]');
   await form.locator('[name=first_name]').fill('Unsaved First');
   const cancelBtn=p.locator('[data-form-cancel]');
   await cancelBtn.click();
   const discardDialog=p.locator('#form-discard-dialog');
   assert(await discardDialog.evaluate(d=>d.open),'Full-page discard dialog did not open');
   await discardDialog.locator('[data-discard-stay]').click();
   assert(!(await discardDialog.evaluate(d=>d.open)),'Full-page discard dialog remained open after stay');
   assert(await cancelBtn.evaluate(b=>b===document.activeElement),'Focus did not return to cancel button');
   assert(await form.locator('[name=first_name]').inputValue()==='Unsaved First','Draft lost after stay');
   await p.route('**/patients',route=>route.fulfill({contentType:'text/html',body:'<html><title>Synthetic registry</title><body>Registry</body></html>'}));
   await cancelBtn.click();await discardDialog.locator('[data-discard-confirm]').click();
   await p.waitForURL('**/patients');assert(await p.title()==='Synthetic registry','Discard did not return to registry');
   await p.unroute('**/patients');

   // Dialog intake cancel / keep / discard
   await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/dialog.html`);
   await p.locator('[data-patient-create-open]').click();
   const intakeDialog=p.locator('#patient-intake-dialog');
   assert(await intakeDialog.evaluate(d=>d.open),'Intake dialog did not open');
   const intakeForm=p.locator('#patient-intake-form');
   await intakeForm.locator('[name=first_name]').fill('Draft In Dialog');
   await intakeDialog.locator('header [data-intake-close]').click();
   const discardSection=intakeDialog.locator('[data-create-discard]');
   assert(!(await discardSection.evaluate(s=>s.hidden)),'Intake discard prompt hidden on dirty close');
   assert(await intakeDialog.locator('[data-intake-keep]').evaluate(b=>b===document.activeElement),'Keep button not focused');
   await intakeDialog.locator('[data-intake-keep]').click();
   assert(await discardSection.evaluate(s=>s.hidden),'Intake discard prompt remained visible after keep');
   assert(await intakeDialog.evaluate(d=>d.open),'Intake dialog closed after keep');
   assert(await intakeForm.locator('[name=first_name]').inputValue()==='Draft In Dialog','Draft lost after keep');
   await intakeDialog.locator('header [data-intake-close]').click();
   await intakeDialog.locator('[data-intake-discard]').click();
   assert(!(await intakeDialog.evaluate(d=>d.open)),'Intake dialog did not close on discard');
  }

  // 3. Validation errors: preserves fields and focuses error summary with field links
  for(const locale of ['en','ar']){
   await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/dialog.html`);
   await p.locator('[data-patient-create-open]').click();
   const intakeDialog=p.locator('#patient-intake-dialog');
   const intakeForm=p.locator('#patient-intake-form');
   await intakeForm.locator('[name=first_name]').fill('Al');
   await intakeForm.locator('[name=last_name]').fill('Tester');
   await intakeForm.locator('[name=phone]').fill('0599111222');
   await intakeForm.locator('[name=date_of_birth]').fill('1990-01-01');
   await intakeForm.locator('[name=gender]').selectOption('male');

   await p.route('**/patient/store',route=>route.fulfill({
    status:422,
    contentType:'application/json',
    body:JSON.stringify({
     success:false,
     errors:{first_name:'First name must be at least 3 characters',phone:'Phone number format is invalid'}
    })
   }));

   await intakeForm.locator('button[type=submit]').click();
   const feedback=intakeDialog.locator('[data-create-feedback]');
   await p.waitForFunction(()=>!document.querySelector('[data-create-feedback]').hidden);
   assert(await feedback.evaluate(f=>f===document.activeElement),'Error feedback did not receive focus');
   assert(await intakeForm.locator('[name=first_name]').inputValue()==='Al','Field value lost on validation error');
   assert(await intakeForm.locator('[name=phone]').inputValue()==='0599111222','Phone value lost on validation error');
   assert(await intakeForm.locator('#first_name').getAttribute('aria-invalid')==='true','aria-invalid not set on invalid field');

   const firstNameLink=feedback.locator('a[href="#first_name"]');
   assert(await firstNameLink.isVisible(),'Field error link missing');
   await firstNameLink.click();
   assert(await intakeForm.locator('#first_name').evaluate(f=>f===document.activeElement),'Clicking error link did not focus input');
   await p.unroute('**/patient/store');
  }

  // 4. Uncertain submission protection: no automatic retries, fields locked, reconciliation path exposed
  for(const locale of ['en','ar']){
   await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/dialog.html`);
   await p.locator('[data-patient-create-open]').click();
   const intakeDialog=p.locator('#patient-intake-dialog');
   const intakeForm=p.locator('#patient-intake-form');
   await intakeForm.locator('[name=first_name]').fill('Uncertain');
   await intakeForm.locator('[name=last_name]').fill('Patient');
   await intakeForm.locator('[name=phone]').fill('0599888777');
   await intakeForm.locator('[name=date_of_birth]').fill('1985-05-15');
   await intakeForm.locator('[name=gender]').selectOption('female');

   let releaseWrite,startWrite,writes=0;
   const heldWrite=new Promise(resolve=>releaseWrite=resolve),writeStarted=new Promise(resolve=>startWrite=resolve);
   await p.route('**/patient/store',async route=>{writes++;startWrite();await heldWrite;await route.fulfill({status:500,contentType:'text/html',body:'Synthetic unavailable response'});});

   await intakeForm.locator('button[type=submit]').click();await writeStarted;
   assert(await intakeForm.locator('button[type=submit]').isDisabled(),'Pending write allows another submit');
   await p.keyboard.press('Escape');assert(await intakeDialog.evaluate(d=>d.open),'Pending write can be dismissed');
   releaseWrite();
   const feedback=intakeDialog.locator('[data-create-feedback]');
   await p.waitForFunction(()=>!document.querySelector('[data-create-feedback]').hidden);

   const reconcileLink=feedback.locator('[data-create-reconcile]');
   assert(!(await reconcileLink.evaluate(l=>l.hidden)),'Reconcile link hidden after uncertain submission');
   assert(await reconcileLink.evaluate(l=>l===document.activeElement),'Reconcile link did not receive focus');
   const reconcileHref=await reconcileLink.getAttribute('href');
   assert(reconcileHref&&reconcileHref.includes('/patients'),'Reconcile link does not navigate to patient registry');

   assert(await intakeForm.locator('button[type=submit]').isDisabled(),'Submit button not disabled during uncertain state');
   assert(await intakeForm.locator('[name=first_name]').isDisabled(),'Form fields not locked during uncertain state');

   await intakeDialog.locator('header [data-intake-close]').click();
   assert(await intakeDialog.evaluate(d=>d.open),'Dialog closed during uncertain state');
   assert(await reconcileLink.evaluate(l=>l===document.activeElement),'Close attempt did not refocus reconciliation link');

   await intakeForm.dispatchEvent('submit');
   assert(writes===1,'Uncertain submission triggered a duplicate POST');
   await p.unroute('**/patient/store');
  }

  // 5. Permission denied & edit mode
  for(const locale of ['en','ar'])for(const mode of ['page','dialog','edit']){
   await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/${mode}.html?permission=denied`);
   assert(await p.locator('[data-dialog="search-dialog"]').count()===0,'Search action exposed without patient-view permission');
   if(mode==='edit'){
    await p.goto(`http://127.0.0.1:57526/registration-search/${locale}/edit.html`);
    assert(await p.locator('[data-dialog="search-dialog"]').count()===0,'Registration hint leaked into patient editing');
   }
  }

  assert(errors.length===0,'JavaScript errors: '+errors.join('; '));
  return {groups:results.length,results,permissionAndEditingCases:6,searchStatesTested:5,unsavedCases:4,validationCases:2,pendingAndUncertainCases:2,pageErrors:errors,scope:'Synthetic registration forms and existing search; no DB or live submission.'};
 }finally{await p.close({runBeforeUnload:false});}
}
