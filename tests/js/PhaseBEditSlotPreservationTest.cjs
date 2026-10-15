const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
let checks=0;
for(const locale of ['en','ar']) {
 const html=fs.readFileSync(`tmp/phase-b-rendered-edit-${locale}.html`,'utf8');
 const script=[...html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)].map(m=>m[1]).find(s=>s.includes('function appendCurrentTimeIfMissing'));
 assert.ok(script);checks++;
 const declaration=name=>script.match(new RegExp(`const ${name} = ([^;]+);`))[0];
 const start=script.indexOf('    function appendCurrentTimeIfMissing');
 const end=script.indexOf('    let currentSlotRequestId',start);
 const source=['currentTime','originalDate','originalDuration','originalDoctor'].map(declaration).join('\n')+'\n'+script.slice(start,end)+'\nglobalThis.append=appendCurrentTimeIfMissing;globalThis.original={date:originalDate,duration:originalDuration,doctor:originalDoctor};';
 const providerSelect=html.match(/<select[^>]*id="providerSelect"[^>]*>([\s\S]*?)<\/select>/i);
 assert.ok(providerSelect && /disabled/.test(providerSelect[0]));checks++;
 const renderedProvider=providerSelect[1].match(/<option[^>]*value="([^"]*)"/)[1];
 const context={window:{},document:{createElement:()=>({})},appointmentI18n:{current:'Current'},dateInput:{value:''},durationSelect:{value:''},doctorSelect:{value:''},timeSelect:{value:'',options:[],appendChild(option){this.options.push(option);}}};
 vm.runInNewContext(source,context);
 for(const changed of ['none','date','duration','doctor']) {
  context.dateInput.value=changed==='date'?'2026-11-10':context.original.date;
  context.durationSelect.value=changed==='duration'?'60':String(context.original.duration);
  context.doctorSelect.value=changed==='doctor'?'9999999':renderedProvider;
  context.timeSelect.value='';context.timeSelect.options=[];
  context.append([]);
  assert.equal(context.timeSelect.options.length,changed==='none'?1:0);checks++;
  assert.equal(Boolean(context.timeSelect.value),changed==='none');checks++;
 }
}
console.log(`Phase B rendered edit slot preservation: ${checks} assertions passed (EN/AR).`);
