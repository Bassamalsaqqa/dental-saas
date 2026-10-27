/* Corrected-behavior regression: exit zero means saved plans survive chart refresh. */
'use strict';
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../../public/assets/js/studio-contract-builder.js'),'utf8');
const functions=source.slice(source.indexOf('function normalizeItems()'),source.indexOf('function renderItems()'));
const saved=[{id:1,procedure_code:'MANUAL',tooth_no:null,description:'Manual',qty:1,price:'25.00'},{id:2,procedure_code:'SAVED',tooth_no:'11',description:'Persisted priced plan',qty:2,price:'125.00'},{id:3,procedure_code:'SAME',tooth_no:'11',description:'Second plan',qty:1,price:'35.00'}];
const context={initialItems:saved,odontogramData:[],conditionTypes:{cavity:'Cavity'},renderItems(){}};vm.createContext(context);vm.runInContext(functions+'\nnormalizeItems();syncItemsFromConditions();',context);
assert.equal(context.items.length,3);assert(context.items.every(x=>x.source==='persisted'));assert.equal(context.items[1].id,2);assert.equal(context.items[1].price,125);
context.odontogramData=[{tooth_number:11,condition_type:'cavity'},{tooth_number:32,condition_type:'cavity'}];vm.runInContext('syncItemsFromConditions();',context);assert.equal(context.items.length,4);assert.equal(context.items.filter(x=>x.tooth==='11').length,2);
context.odontogramData=[];vm.runInContext('syncItemsFromConditions();',context);assert.equal(context.items.length,3);assert.equal(context.items[1].description,'Persisted priced plan');
context.odontogramData=[{tooth_number:32,condition_type:'cavity'}];vm.runInContext('syncItemsFromConditions();',context);context.items[3].source='manual';context.items[3].price=77;context.odontogramData=[];vm.runInContext('syncItemsFromConditions();',context);assert.equal(context.items[3].price,77);
console.log('PASS: saved IDs, multiple same-tooth plans, chart changes and edited suggestions remain explicit plan records.');
