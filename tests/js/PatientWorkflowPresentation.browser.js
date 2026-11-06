async (page) => {
  const results=[];
  const assert=(condition,message)=>{if(!condition)throw Error(message);};
  const p=await page.context().newPage();
  const errors=[];p.on('pageerror',error=>errors.push(error.message));
  const visit=async(locale,kind,query='')=>{
    const response=await p.goto(`http://127.0.0.1:57526/patient-workflow/${locale}/${kind}.html${query}`);
    assert(response.status()===200,`Presenter failed: ${locale}/${kind}`);
  };
  const fit=async()=>assert(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'Document overflows');
  try{
    for(const locale of ['en','ar'])for(const width of [1366,768,390,320]){
      await p.setViewportSize({width,height:900});
      await visit(locale,'workspace');await fit();
      assert(await p.locator('html').getAttribute('dir')===(locale==='ar'?'rtl':'ltr'),'Incorrect reading direction');
      const treatment=p.locator('[data-app-drawer-open="patientTreatmentDrawer"]');
      assert(await treatment.count()===1,'Treatment action must be unique');
      assert(await treatment.isVisible(),'Treatment action is not discoverable');
      assert(await treatment.evaluate(node=>!node.closest('[popover]')),'Treatment action remains in More actions');
      await treatment.click();
      const drawer=p.locator('#patientTreatmentDrawer');
      await drawer.waitFor({state:'visible'});
      assert(await drawer.getAttribute('aria-hidden')==='false','Treatment drawer did not open');
      const footer=drawer.locator('.app-drawer__footer');
      assert(await footer.locator('button[type=submit].button.primary').count()===1,'Primary submit styling missing');
      assert(await footer.locator('a.button.secondary').count()===1,'Full page action styling missing');
      assert(await footer.locator('button.button.secondary').count()===1,'Cancel action styling missing');
      const charge=drawer.locator('[data-charge-toggle]');
      const id=await charge.getAttribute('id');
      assert(!!id&&await drawer.locator(`label[for="${id}"]`).count()===1,'Charge toggle association missing');
      assert(await p.locator(`[id="${id}"]`).count()===1,'Duplicate charge toggle ID');
      assert(await charge.evaluate(node=>getComputedStyle(node.closest('label')).fontSize)==='17px','Charge toggle label is too small');
      // Clicking the label must operate the real checkbox, without submitting a record.
      const wasChecked=await charge.isChecked();
      await drawer.locator(`label[for="${id}"]`).click();
      assert(await charge.isChecked()!==wasChecked,'Charge label does not toggle its control');
      await drawer.locator(`label[for="${id}"]`).click();
      await footer.locator('button[data-app-drawer-close="patientTreatmentDrawer"]').click();
      await drawer.waitFor({state:'hidden'});
      assert(!await drawer.isVisible(),'Clean drawer cancellation failed');
      assert(await treatment.evaluate(node=>node===document.activeElement),'Drawer focus was not restored');
      await visit(locale,'examination');await fit();
      const next=p.locator('.clinical-workflow-actions a[href*="treatment/create"]');
      assert(await next.count()===1,'Examination next action missing');
      const url=new URL(await next.getAttribute('href'));
      assert(url.searchParams.get('examination_id')==='201','Examination context was lost');
      assert(!url.searchParams.has('patient_id'),'Patient context must remain examination-derived');
      await visit(locale,'treatment_create');await fit();
      const fullCharge=p.locator('[data-charge-toggle]');
      const fullId=await fullCharge.getAttribute('id');
      assert(!!fullId&&await p.locator(`label[for="${fullId}"]`).count()===1,'Full-page charge label missing');
      results.push({locale,width,taskDiscovery:true,drawerInteraction:true,linkedExamination:true,readableChargeToggle:true,noOverflow:true});
    }
    for(const locale of ['en','ar']){
      await visit(locale,'workspace','?permissions=none');
      assert(await p.locator('[data-app-drawer-open="patientTreatmentDrawer"]').count()===0,'Denied treatment action leaked');
      await visit(locale,'workspace','?permissions=treatment-only');
      assert(await p.locator('[data-app-drawer-open="patientTreatmentDrawer"]').isVisible(),'Treatment-only user lost the primary action');
      assert(await p.locator('[popovertarget="patient-more-actions-panel"]').count()===0,'Empty More actions menu rendered');
      await visit(locale,'examination','?permissions=none');
      assert(await p.locator('a[href*="treatment/create"]').count()===0,'Denied examination treatment link leaked');
      for(const variant of ['missing','malformed','empty','mixed']){
        await visit(locale,'money_trail','?money='+variant);
        const value=await p.locator('.clinical-money-counts > div').nth(4).locator('dd').innerText();
        if(variant==='missing'||variant==='malformed')assert(value!==''&&!/[0-9]/.test(value),'Missing provider units became invented money');
        if(variant==='empty')assert(value==='0.00','Canonical empty summary must remain zero');
        if(variant==='mixed')assert(value.includes('125.00 ILS / 50.00 USD'),'Native provider currency buckets were lost');
      }
      results.push({locale,permissionBoundaries:true,missingMoneyUnavailable:true,canonicalMoneyPreserved:true});
    }
    assert(errors.length===0,'Patient workflow JavaScript failed: '+errors.join('; '));
    return {groups:results.length,results,pageErrors:errors,scope:'Synthetic native views and drawer interaction; no DB, routing, saves or clinical mutation acceptance.'};
  }finally{await p.close({runBeforeUnload:false});}
}
