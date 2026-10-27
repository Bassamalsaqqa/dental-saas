/* Bounded server-side lists. Existing routes retain authorization and business authority. */
(() => {
'use strict';
const configNode=document.getElementById('directory-config');if(!configNode)return;
const c=JSON.parse(configNode.textContent),L=c.labels,inventory=c.type==='inventory';
const $=s=>document.querySelector(s),escape=v=>String(v??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
const plain=v=>String(v??'').replace(/<[^>]*>/g,'').replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&quot;/g,'"').replace(/&#0?39;/g,"'");
const idOf=row=>Number(inventory?row[0]:row.id),nameOf=row=>String(inventory?row[1]:row.name||row.patient_id||'');
const safeId=row=>Number.isSafeInteger(idOf(row))&&idOf(row)>0;
const recordUrl=row=>c.recordBase.replace(/\/$/,'')+'/'+idOf(row),context=url=>{const u=new URL(url,location.origin);u.searchParams.set('return_to',inventory?'inventory':'patients');return u.href;};
const csrf=()=>({name:$('meta[name=csrf-name]')?.content,header:$('meta[name=csrf-header]')?.content,token:$('meta[name=csrf-token]')?.content});
function updateToken(data){if(typeof data?.csrf_token!=='string')return;const m=$('meta[name=csrf-token]');if(m)m.content=data.csrf_token;const name=csrf().name;if(name)document.querySelectorAll('input').forEach(e=>{if(e.name===name)e.value=data.csrf_token;});}
function error(message){$('#directory-error').textContent=message;$('#directory-error').hidden=false;$('#directory-retry').hidden=false;}
let rows=[],selected=null,opener=null,requestVersion=0,abort=null,table,lastPage=null,stopLoading=()=>{};
const actions=$('#directory-actions'),remove=$('#directory-delete');
function link(label,url){const a=document.createElement('a');a.className='button secondary';a.textContent=label;a.href=context(url);return a;}
function addFact(label,value){const pair=document.createElement('div'),dt=document.createElement('dt'),dd=document.createElement('dd');dt.textContent=label;dd.textContent=String(value??'')||'—';pair.append(dt,dd);$('#directory-record-facts').append(pair);}
function openActions(row,trigger){if(!safeId(row))return;selected=row;opener=trigger;$('#directory-record-title').textContent=nameOf(row);const facts=$('#directory-record-facts');facts.replaceChildren();
 if(inventory){[[L.recordId,row[0]],[L.category,c.categories[row[2]]||row[2]],[L.quantity,quantityLabel(row[3])],[L.unitPrice,baseMoney(row[4])],[L.supplier,row[5]],[L.expiry,row[6]],[L.status,plain(row[7])]].forEach(x=>addFact(...x));}
 else{[[L.recordId,row.patient_id],[L.phone,row.phone],[L.email,row.email],[L.birth,row.date_of_birth],[L.gender,c.genders[row.gender]||row.gender],[L.exams,row.examinations]].forEach(x=>addFact(...x));}
 const nav=$('#directory-record-actions');nav.replaceChildren();if(c.can.view)nav.append(link(L.openRecord,recordUrl(row)));if(c.can.edit)nav.append(link(L.editRecord,recordUrl(row)+'/edit'));
 if(!inventory){if(c.can.book)nav.append(link(L.book,c.appBase+'appointment/create?patient_id='+idOf(row)));if(c.can.exam)nav.append(link(L.examination,c.appBase+'examination/create?patient_id='+idOf(row)));if(c.can.treatment)nav.append(link(L.treatment,c.appBase+'treatment/create?patient_id='+idOf(row)));if(c.can.view)nav.append(link(L.chart,c.appBase+'odontogram/'+idOf(row)));}
 if(c.can.delete){const b=document.createElement('button');b.className='button destructive';b.type='button';b.textContent=L.delete;b.addEventListener('click',()=>{$('#directory-delete-name').textContent=nameOf(row);$('#directory-delete-error').hidden=true;actions.close();remove.showModal();});nav.append(b);}actions.showModal();}
actions.addEventListener('close',()=>!remove.open&&opener?.isConnected&&opener.focus());remove.addEventListener('close',()=>opener?.isConnected&&opener.focus());
$('[data-delete-cancel]').addEventListener('click',()=>remove.close());
$('[data-delete-confirm]').addEventListener('click',async e=>{if(!selected||!safeId(selected))return;const b=e.currentTarget;b.disabled=true;const token=csrf();try{const response=await fetch(recordUrl(selected),{method:'DELETE',credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest',[token.header]:token.token}});const data=await response.json();updateToken(data);if(!response.ok||data.success!==true)throw Error(typeof data.error==='string'?data.error:typeof data.message==='string'?data.message:L.deleteFailed);remove.close();actions.close();table.ajax.reload(null,false);loadStats();}catch(err){$('#directory-delete-error').textContent=err.message||L.deleteFailed;$('#directory-delete-error').hidden=false;}finally{b.disabled=false;}});
function text(v){return escape(v===null||v===undefined||v===''?'\u2014':v);}
function primary(row){const label='<strong>'+text(nameOf(row))+'</strong><small>'+text(inventory?row[0]:row.patient_id)+'</small>';return safeId(row)&&c.can.view?'<a class="directory-record-link" href="'+escape(context(recordUrl(row)))+'">'+label+'</a>':label;}
function actionButton(row){return safeId(row)?'<button type="button" class="button secondary directory-action" data-record="'+idOf(row)+'">'+escape(L.actions)+'</button>':'';}
const patientColumns=[
 {data:null,render:(d,t,r)=>t==='display'?primary(r):nameOf(r)},
 {data:null,render:(d,t,r)=>t==='display'?'<span class="directory-phone" dir="ltr">'+text(r.phone)+'</span><small>'+text(r.email)+'</small>':[r.phone,r.email].filter(Boolean).join(' ')},
 {data:'status',render:(d,t)=>t==='display'?'<span class="directory-status '+(['active','inactive','archived','pending'].includes(d)?'is-'+d:'')+'">'+text(c.statuses[d]||d)+'</span>':c.statuses[d]||d},
 {data:null,render:(d,t,r)=>t==='display'?'<span>'+text(r.created_at)+'</span><small>'+escape(L.exams)+': '+text(r.examinations??0)+'</small>':r.created_at},
 {data:null,render:(d,t,r)=>t==='display'?actionButton(r):''}
];
function baseMoney(value){const match=/^\$(-?[\d,]+\.\d{2})$/.exec(String(value??''));return match&&c.money?(c.money.position==='after'?match[1]+' '+c.money.symbol:c.money.symbol+match[1]):value;}
function quantityLabel(value){return String(value??'').replace(/^(\d+)\s+(\w+)$/,(_,number,unit)=>number+' '+(c.units[unit]||unit));}
const inventoryColumns=[
 {data:null,render:(d,t,r)=>t==='display'?primary(r):r[1]},
 ...[2,3,4,5,6].map(i=>({data:i,render:(d,t)=>{const value=i===2?(c.categories[d]||d):i===4?baseMoney(d):i===3?String(d).replace(/^(\d+)\s+(\w+)$/,(_,n,u)=>n+' '+(c.units[u]||u)):d;return t==='display'?text(value):plain(value);}})),
 {data:7,render:(d,t)=>t==='display'?'<span class="directory-status">'+text(plain(d))+'</span>':plain(d)},
 {data:null,render:(d,t,r)=>t==='display'?actionButton(r):''}
];
function renderMobile(){const region=$('#directory-mobile-records');if(!region||inventory)return;const fragment=document.createDocumentFragment();for(const row of rows){const card=document.createElement('article'),title=document.createElement('h2'),code=document.createElement('p'),contact=document.createElement('div'),status=document.createElement('p'),nav=document.createElement('nav');card.className='directory-mobile-record';title.textContent=nameOf(row);code.textContent=String(row.patient_id||'');contact.className='directory-mobile-contact';for(const value of [row.phone,row.email]){const p=document.createElement('p');p.dir='auto';p.textContent=value||'\u2014';contact.append(p);}status.textContent=(c.statuses[row.status]||row.status||'')+' · '+L.exams+': '+String(row.examinations??0);if(c.can.view&&safeId(row))nav.append(link(L.openRecord,recordUrl(row)));const button=document.createElement('button');button.type='button';button.className='button secondary';button.textContent=L.actions;button.addEventListener('click',()=>openActions(row,button));nav.append(button);card.append(title,code,contact,status,nav);fragment.append(card);}region.replaceChildren(fragment);}
async function loadStats(){const summary=$('.directory-summary');summary.setAttribute('aria-busy','true');try{const r=await fetch(c.statsUrl,{signal:AbortSignal.timeout(12000),credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});if(!r.ok)throw Error();const d=await r.json();if(d.error)throw Error();document.querySelectorAll('[data-stat]').forEach(e=>{const value=Number(d[e.dataset.stat]);e.textContent=Number.isFinite(value)?String(value):'—';});}catch{document.querySelectorAll('[data-stat]').forEach(e=>{e.textContent='—';e.title=L.listFailed;});}finally{summary.setAttribute('aria-busy','false');}}
if(!window.jQuery?.fn?.DataTable){error(L.listFailed);$('#directory-loading').hidden=true;$('#directory-retry').addEventListener('click',()=>location.reload(),{once:true});return;}
jQuery.fn.dataTable.ext.errMode='none';
table=jQuery('#directory-table').DataTable({serverSide:true,processing:false,pageLength:10,searching:true,autoWidth:false,ordering:true,order:[],columnDefs:[{targets:'_all',orderable:false}],columns:inventory?inventoryColumns:patientColumns,dom:'rt<"directory-table-footer"ip>',language:{emptyTable:L.emptyList,zeroRecords:L.emptyList,info:L.listSummary,infoEmpty:L.listSummaryEmpty,infoFiltered:L.listFiltered,paginate:{previous:L.previous,next:L.next}},
 ajax:async(request,callback)=>{const version=++requestVersion;abort?.abort();stopLoading();abort=new AbortController();const controller=abort,expiry=setTimeout(()=>controller.abort(),12000);const stopTable=window.StudioLoading.begin(document.querySelector('#directory-table tbody'),{initial:!lastPage,columns:c.headings.length}),stopMobile=window.StudioLoading.begin($('#directory-mobile-records'),{initial:!lastPage,columns:2,rows:3});stopLoading=()=>{stopTable();stopMobile();};$('#directory-loading').textContent=L.loadingList+(lastPage?' '+c.previousResults:'');$('#directory-loading').hidden=false;$('#directory-loading').classList.remove('is-idle');$('#directory-panel')?.setAttribute('aria-busy','true');$('#directory-error').hidden=true;$('#directory-retry').hidden=true;const token=csrf(),body=new URLSearchParams();body.set('draw',request.draw);body.set('start',request.start);body.set('length',request.length);body.set('search[value]',request.search.value);body.set('order[0][column]',inventory?'1':'0');body.set('order[0][dir]',$('#directory-sort').value);if(token.name)body.set(token.name,token.token);
  try{const r=await fetch(c.url,{method:'POST',credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest',[token.header]:token.token},body,signal:abort.signal});if([401,403].includes(r.status)){lastPage=null;rows=[];throw Error('access');}if(!r.ok)throw Error();const d=await r.json();if(d.error||!Array.isArray(d.data))throw Error();if(version!==requestVersion)return;updateToken(d);rows=d.data;lastPage=d;stopLoading();callback({...d,draw:request.draw});}
  catch(err){if(version!==requestVersion)return;error(err.message==='access'?c.requestDenied:L.listFailed+(lastPage?' '+c.previousResults:''));stopLoading();callback(lastPage?{...lastPage,draw:request.draw}:{draw:request.draw,recordsTotal:0,recordsFiltered:0,data:[]});}
  finally{clearTimeout(expiry);if(version===requestVersion){stopLoading();$('#directory-panel')?.removeAttribute('data-initial-loading');$('#directory-loading').classList.add('is-idle');$('#directory-panel')?.setAttribute('aria-busy','false');}}}
});
jQuery('#directory-table').on('draw.dt',()=>{renderMobile();document.querySelectorAll('#directory-table tbody tr').forEach(tr=>{Array.from(tr.children).forEach((td,i)=>td.dataset.label=c.headings[i]||'');});});
$('#directory-table').addEventListener('click',e=>{const b=e.target.closest('[data-record]');if(!b)return;const row=rows.find(r=>idOf(r)===Number(b.dataset.record));if(row)openActions(row,b);});
// Keep text selectable; row navigation must not intercept inline controls or a drag selection.
function navigateRow(event){
 if(event.defaultPrevented||event.target.closest('a,button,input,select,textarea,summary')||String(window.getSelection()||'').trim())return;
 if(event.type==='click'&&event.button!==0||event.type==='auxclick'&&event.button!==1)return;
 const anchor=event.target.closest('tbody tr')?.querySelector('.directory-record-link');if(!anchor)return;
 if(event.type==='auxclick'||event.ctrlKey||event.metaKey||event.shiftKey){event.preventDefault();window.open(anchor.href,'_blank','noopener');}else location.assign(anchor.href);
}
$('#directory-table').addEventListener('click',navigateRow);
$('#directory-table').addEventListener('auxclick',navigateRow);
let timer;$('#directory-search').addEventListener('input',e=>{clearTimeout(timer);abort?.abort();requestVersion++;stopLoading();$('#directory-loading').textContent=L.loadingList+(lastPage?' '+c.previousResults:'');$('#directory-loading').hidden=false;$('#directory-loading').classList.remove('is-idle');timer=setTimeout(()=>table.search(e.target.value).draw(),300);});$('#directory-size').addEventListener('change',e=>table.page.len(Number(e.target.value)).draw());$('#directory-sort').addEventListener('change',()=>table.draw());$('#directory-retry').addEventListener('click',()=>{table.ajax.reload();loadStats();});loadStats();
function exportRows(){return rows.map(r=>inventory?[r[0],r[1],r[2],r[3],baseMoney(r[4]),r[5],r[6],plain(r[7])]:[r.patient_id,r.name,r.phone,r.email,c.statuses[r.status]||r.status,r.created_at]);}
function exportHeaders(){return inventory?[L.recordId,...c.headings.slice(0,-1)]:[L.recordId,L.openRecord,L.phone,L.email,L.status,L.registered];}
document.querySelectorAll('[data-export]').forEach(button=>button.addEventListener('click',()=>{
 if(button.dataset.export==='print'){window.print();return;}
 const data=exportRows();if(!data.length)return;
 try{const csv=v=>{let value=String(v??'');if(/^[=+@\-\t\r]/.test(value))value="'"+value;return '"'+value.replace(/"/g,'""')+'"';};
 const content='\uFEFF'+[exportHeaders(),...data].map(row=>row.map(csv).join(',')).join('\r\n');
 const url=URL.createObjectURL(new Blob([content],{type:'text/csv;charset=utf-8'})),a=document.createElement('a');a.href=url;a.download=c.type+'-current-page.csv';a.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
 }catch{error(L.exportFailed);}
}));
document.querySelectorAll('.directory-tools').forEach(d=>d.addEventListener('keydown',e=>{if(e.key==='Escape'){d.open=false;d.querySelector('summary').focus();}}));
})();