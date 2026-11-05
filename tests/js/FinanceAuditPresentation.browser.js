async (page) => {
  const results=[];
  const labels={en:{settings:'Booking settings',heading:'Booking column and time'},ar:{settings:'إعدادات الحجز',heading:'عمود الحجز والموعد'}};
  for(const locale of ['en','ar'])for(const width of [320,390]){
    const p=await page.context().newPage();
    try{
      await p.setViewportSize({width,height:844});
      await p.goto('http://127.0.0.1:57526/payouts-'+locale+'.html');
      await p.locator('.grid-scroll').waitFor();
      const summaries=await p.locator('.money-partner-summary dd').evaluateAll(items=>items.map(item=>{
        const pairs=Array.from(item.querySelectorAll('.money-native-amount'));
        const [a,b]=pairs.map(node=>node.getBoundingClientRect());
        const sameLine=Math.abs(a.top-b.top)<1;
        const gap=sameLine?Math.max(b.left-a.right,a.left-b.right):Math.max(b.top-a.bottom,a.top-b.bottom);
        return {separated:item.textContent.includes(' / '),gap,intact:pairs.every(node=>node.scrollWidth<=node.clientWidth+1&&getComputedStyle(node).whiteSpace==='nowrap')};
      }));
      if(summaries.length!==4||summaries.some(item=>!item.separated||!item.intact||item.gap<3.5))throw Error(JSON.stringify({locale,width,summaries}));
      if(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Payout summary overflows viewport');
      await p.goto('http://127.0.0.1:57526/booking-'+locale+'.html');
      if(!await p.getByRole('heading',{name:labels[locale].heading,exact:true}).isVisible()||!await p.getByRole('link',{name:labels[locale].settings,exact:true}).isVisible())throw Error('Booking translation is missing');
      if(await p.locator('html').getAttribute('dir')!==(locale==='ar'?'rtl':'ltr')||await p.locator('[name=chair_name]').inputValue()!=='')throw Error('Booking direction/explicit choice changed');
      if(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1||/\?{2,}/.test(document.body.textContent)))throw Error('Booking text is corrupt or overflows');
      results.push({locale,width,mixedCurrencySeparated:true,currencyPairsIntact:true,bookingSettingsLocalized:true,rtlCorrect:true,noViewportOverflow:true});
    }finally{await p.close({runBeforeUnload:false});}
  }
  return results;
}
