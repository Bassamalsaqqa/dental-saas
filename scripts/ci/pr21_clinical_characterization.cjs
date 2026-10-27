/* Read-only characterization of explicitly unresolved PR21 clinical defects.
 * Runs trusted repository functions against synthetic values; no browser, database or network.
 * Exit 0 means the documented defects were reproduced, NOT that clinical workflows are safe.
 */
'use strict';
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm'),cp=require('node:child_process');
const root=path.resolve(__dirname,'../..'),baselineRef='5785e4c71902e06b61c2bb08a021b34ad7a3be20',read=name=>cp.execFileSync('git',['show',baselineRef+':'+name],{cwd:root,encoding:'utf8',maxBuffer:8*1024*1024});
const history=read('app/Views/patient/odontogram_versions.php'),svg=read('app/Views/odontogram/odontogram.svg'),builder=read('public/assets/js/studio-contract-builder.js');
const legacyLookup=history.includes("container.querySelector('#tooth' + toothNumber)"),nativeIds=svg.includes('id="tooth-1"')&&svg.includes('id="tooth-32"');
const normalize=builder.slice(builder.indexOf('function normalizeItems()'),builder.indexOf('function syncItemsFromConditions()'));
const sync=builder.slice(builder.indexOf('function syncItemsFromConditions()'),builder.indexOf('function renderItems()'));
if(!normalize||!sync)throw Error('Characterization seam changed; review this test rather than assuming safety.');
const context={initialItems:[{procedure_code:'SYNTH-TOOTH',tooth_no:'11',description:'Synthetic only',qty:2,price:125},{procedure_code:'SYNTH-MANUAL',tooth_no:'',description:'Synthetic manual',qty:1,price:25}],odontogramData:[],conditionTypes:{},renderItems(){}};
vm.createContext(context);vm.runInContext(normalize+'\n'+sync+'\nnormalizeItems();',context,{timeout:1000});const before=context.items.map(x=>({...x}));vm.runInContext('syncItemsFromConditions();',context,{timeout:1000});const after=context.items.map(x=>({...x}));
const pricedItemDropped=before.some(x=>x.code==='SYNTH-TOOTH')&&!after.some(x=>x.code==='SYNTH-TOOTH'),manualRetained=after.some(x=>x.code==='SYNTH-MANUAL'&&x.price===25);
const result={disposition:'BEFORE-ONLY: pinned 5785e4c characterization reproduces original defects; run corrected-behavior regressions for current code',history:{legacyLookup,nativeIds,conditionOverlayTargetMissing:legacyLookup&&nativeIds},contract:{pricedItemDropped,manualRetained,beforeCount:before.length,afterCount:after.length},scope:'Read-only trusted-source synthetic characterization; no persistence claim. Actual isolated route evidence is documented separately.'};
console.log(JSON.stringify(result,null,2));if(!result.history.conditionOverlayTargetMissing||!pricedItemDropped||!manualRetained)process.exitCode=1;
