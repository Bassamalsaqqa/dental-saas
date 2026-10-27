/* Corrected historical preview behavior. Synthetic DOM, no database or stored snapshots. */
'use strict';
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const root=path.resolve(__dirname,'../..'),source=fs.readFileSync(path.join(root,'app/Views/patient/odontogram_versions.php'),'utf8');
const script=source.slice(source.indexOf("    document.addEventListener('DOMContentLoaded'"),source.lastIndexOf('</script>'));
function preview(rows,ids){const overlays=[],base={classList:{contains:()=>false},getBBox:()=>({width:10,height:20}),cloneNode:()=>({removeAttribute(){},setAttribute(k,v){this[k]=v}})};const groups=ids.map(id=>({id,querySelectorAll:selector=>selector.startsWith('path')?[base]:[],appendChild:overlay=>overlays.push({id,...overlay})}));const container={hidden:true,querySelectorAll:selector=>groups.filter(g=>selector.includes('[id="'+g.id+'"]'))},warning={hidden:false};const context={patientHistorySnapshotData:rows,document:{getElementById:id=>id==='patientHistoryOdontogramPreview'?container:warning,addEventListener:(_,callback)=>callback()}};vm.runInNewContext(script,context,{timeout:1000});return{available:!container.hidden,warning:!warning.hidden,overlays}}
let result=preview([1,16,17,32].map(tooth_number=>({tooth_number,condition_type:'cavity'})),['tooth-1','tooth-16','tooth-17','tooth-32']);assert(result.available);assert.equal(result.overlays.length,4);assert(result.overlays.every(x=>x.fill==='#facc15'&&x.opacity==='1'));
for(const [rows,ids] of [
 [[{tooth_number:0,condition_type:'cavity'}],['tooth-1']],[[{condition_type:'cavity'}],['tooth-1']],[[{tooth_number:33,condition_type:'cavity'}],['tooth-33']],[[{tooth_number:'tooth-1',condition_type:'cavity'}],['tooth-1']],
 [[{tooth_number:1,condition_type:'cavity'},{tooth_number:1,condition_type:'crown'}],['tooth-1']],[[{tooth_number:1,condition_type:'unsupported'}],['tooth-1']],[[{tooth_number:1,condition_type:'cavity'}],[]],[[{tooth_number:1,condition_type:'cavity'}],['tooth-1','tooth-1']],[[{tooth_number:1,condition_type:'cavity'}],['tooth-1','tooth1']]
]){result=preview(rows,ids);assert(!result.available);assert(result.warning);assert.equal(result.overlays.length,0)}
assert(preview([{tooth_number:1,condition_type:'crown'}],['tooth1']).available);assert(preview([{tooth_number:1,condition_type:'healthy'}],['tooth-1']).available);
console.log('PASS: exact unique mapping, preserved colors, extremes, supported legacy identity and fail-closed malformed/ambiguous history.');
