const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

class Element {
  constructor(value='') { this.value=value; this.dataset={}; this.options=[]; this.listeners={}; }
  replaceChildren() { this.options=[]; this.value=''; }
  appendChild(option) { this.options.push(option); }
  addEventListener(name,handler) { this.listeners[name]=handler; }
  dispatchEvent() {}
}
async function verify(dir,dynamic) {
  const date=new Element('2026-11-09'), duration=new Element('30'), resource=new Element(dynamic?'41':'Legacy room'), time=new Element();
  const controls={'[data-appointment-date]':date,'[data-appointment-duration]':duration,'[data-appointment-time]':time};
  controls[dynamic?'[data-appointment-provider]':'[data-appointment-chair]']=resource;
  const wrap={dataset:{slotsUrl:'https://example.test/denta/appointment/available-time-slots',excludeId:'12'},querySelector:s=>controls[s]||null};
  const requests=[];
  const document={documentElement:{dir},createElement:()=>new Element(),createTextNode:text=>({textContent:text}),addEventListener:()=>{}};
  const window={location:{origin:'https://example.test',href:'https://example.test/denta/patient/1'},setTimeout:fn=>fn()};
  const fetch=(url,options)=>new Promise((resolve,reject)=>requests.push({url,options,resolve,reject}));
  const context={window,document,fetch,URL,Event:class{},console};
  vm.runInNewContext(fs.readFileSync('public/assets/js/contextual-workspace.js','utf8'),context);
  window.initContextualAppointmentForms({querySelectorAll:()=>[wrap]});
  assert.equal(requests.length,1);
  let url=new URL(requests[0].url);
  assert.equal(url.pathname,'/denta/appointment/available-time-slots');
  assert.equal(url.searchParams.get(dynamic?'provider_user_id':'chair_name'),dynamic?'41':'Legacy room');
  assert.equal(url.searchParams.has(dynamic?'chair_name':'provider_user_id'),false);
  assert.equal(url.searchParams.get('exclude_id'),'12');
  assert.equal(JSON.stringify(requests[0].options.headers),JSON.stringify({'X-Requested-With':'XMLHttpRequest'}));
  resource.value=dynamic?'42':'Other room'; resource.listeners.change();
  assert.equal(requests.length,2);
  requests[1].resolve({ok:true,json:async()=>[{value:'11:00',display:'11:00'}]});
  await new Promise(setImmediate);
  assert.equal(time.options[1].value,'11:00');
  requests[0].resolve({ok:true,json:async()=>[{value:'10:00',display:'Stale'}]});
  await new Promise(setImmediate);
  assert.equal(time.options[1].value,'11:00');
  resource.value=''; resource.listeners.change();
  assert.equal(requests.length,2); assert.equal(time.options.length,1); assert.equal(time.value,'');
  resource.value=dynamic?'42':'Other room'; resource.listeners.change();
  requests[2].reject(new Error('Synthetic failure')); await new Promise(setImmediate);
  assert.equal(time.options.length,1); assert.equal(time.value,'');
  assert.equal(/Stale|DoctorScheduling\./.test(time.options[0].textContent),false);
}
(async()=>{for(const dir of ['ltr','rtl'])for(const dynamic of [true,false])await verify(dir,dynamic); console.log('Phase B slot transport: 60 assertions passed (EN/AR, dynamic/legacy, stale response and failure clearing).');})().catch(e=>{console.error(e);process.exitCode=1;});
