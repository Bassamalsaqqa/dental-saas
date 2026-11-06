async(page)=>{
 const results=[],errors=[];const p=await page.context().newPage();
 p.on('pageerror',e=>errors.push(e.message));
 const assert=(ok,message)=>{if(!ok)throw Error(message);};
 try{
  for(const locale of ['en','ar'])for(const width of [1366,768,390,320]){
   await p.setViewportSize({width,height:844});
   const r=await p.goto(`http://127.0.0.1:57526/payables-${locale}.html`);
   assert(r.status()===200,'Payables presenter failed');
   await p.locator('#money-rows tr[data-review-key]').nth(2).waitFor();
   const config=await p.locator('#money-workspace-config').textContent().then(JSON.parse);
   const label=config.expenseCategories.salaries;
   const salary=p.locator('#money-rows tr').filter({hasText:'SAL-SYN'});
   assert(await salary.locator('td').nth(2).innerText()===label,'Table displays raw salary category');
   const legacy=p.locator('#money-rows tr').filter({hasText:'OTHER-SYN'}).locator('td').nth(2);
   assert(await legacy.innerText()==='legacy <b>category</b>','Unknown category fallback lost');
   assert(await legacy.locator('b').count()===0,'Category text became HTML');
   const search=p.locator('#money-search');
   for(const query of [label,'salaries']){
    await search.fill(query);
    await p.waitForFunction(()=>document.querySelectorAll('#money-rows tr[data-review-key]').length===1);
    assert(await p.locator('#money-rows').innerText().then(t=>t.includes('SAL-SYN')&&!t.includes('RENT-SYN')),'Localized/raw search selected wrong payable');
   }
   await search.fill('no synthetic match');
   await p.waitForFunction(()=>document.querySelectorAll('#money-rows tr[data-review-key]').length===0);
   await search.fill('');await p.locator('#money-rows tr[data-review-key]').nth(2).waitFor();
   results.push({locale,width,localizedCategory:true,localizedAndRawSearch:true,unknownCategoryEscaped:true,clearRestoresRows:true});
  }
  assert(errors.length===0,'Payables page error: '+errors.join('; '));
  return {groups:results.length,results,pageErrors:errors,scope:'Synthetic payables display/search, no settlement context or payment write.'};
 }finally{await p.close({runBeforeUnload:false});}
}
