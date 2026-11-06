// Isolated finance/patient presenter server. No database, authentication or successful application writes.
'use strict';
const http=require('node:http'),fs=require('node:fs'),path=require('node:path'),{execFileSync}=require('node:child_process');
const root=path.resolve(__dirname,'../..'),publicRoot=path.join(root,'public');
const pages=Object.fromEntries(['en','ar'].map(locale=>['/payouts-'+locale+'.html',execFileSync('php',[path.join(root,'tests/_support/fixtures/FinanceDrawerPresenter.php'),locale],{windowsHide:true,encoding:'utf8'})]));
for(const locale of ['en','ar'])pages['/payables-'+locale+'.html']=execFileSync('php',[path.join(root,'tests/_support/fixtures/FinanceDrawerPresenter.php'),locale,'payables'],{windowsHide:true,encoding:'utf8'});
for(const locale of ['en','ar']){
 pages['/expense-'+locale+'.html']=execFileSync('php',[path.join(root,'tests/_support/fixtures/FinanceDrawerPresenter.php'),locale,'expense'],{windowsHide:true,encoding:'utf8'});
 pages['/expense-pending-'+locale+'.html']=execFileSync('php',[path.join(root,'tests/_support/fixtures/FinanceDrawerPresenter.php'),locale,'expense-pending'],{windowsHide:true,encoding:'utf8'});
}
for(const locale of ['en','ar'])pages['/booking-'+locale+'.html']=execFileSync('php',[path.join(root,'tests/_support/fixtures/BookingPresenter.php'),locale,'settings'],{windowsHide:true,encoding:'utf8'});
const syntheticPayouts = [
    {
        id: 1,
        finance_id: 101,
        transaction_id: 'INV-2026-0001',
        transaction_type: 'invoice',
        patient_name: 'John Doe',
        patient_gender: 'male',
        provider_name: 'Dr. Sara Ahmad',
        provider_type: 'user',
        currency: 'ILS',
        gross_amount: 1000.0,
        provider_share_amount: 400.0,
        clinic_share_amount: 600.0,
        provider_paid_amount: 0.0,
        provider_balance_due: 400.0,
        status: 'unpaid',
        created_at: '2026-11-01 10:00:00',
        created_at_formatted: '2026-11-01 10:00',
        description: 'Root canal treatment'
    },
    {
        id: 2,
        finance_id: 102,
        transaction_id: 'INV-2026-0002',
        transaction_type: 'invoice',
        patient_name: 'Jane Roe',
        patient_gender: 'female',
        provider_name: 'Outside Specialist',
        provider_type: 'guest',
        currency: 'ILS',
        gross_amount: 500.0,
        provider_share_amount: 250.0,
        clinic_share_amount: 250.0,
        provider_paid_amount: 100.0,
        provider_balance_due: 150.0,
        status: 'partial',
        created_at: '2026-11-02 11:30:00',
        created_at_formatted: '2026-11-02 11:30',
        description: 'Orthodontic adjustment'
    },
    {
        id: 3,
        finance_id: 103,
        transaction_id: 'INV-2026-0003',
        transaction_type: 'invoice',
        patient_name: 'Ali Hassan',
        patient_gender: 'male',
        provider_name: 'Dr. Sara Ahmad',
        provider_type: 'user',
        currency: 'ILS',
        gross_amount: 800.0,
        provider_share_amount: 320.0,
        clinic_share_amount: 480.0,
        provider_paid_amount: 320.0,
        provider_balance_due: 0.0,
        status: 'paid',
        created_at: '2026-11-03 09:15:00',
        created_at_formatted: '2026-11-03 09:15',
        description: 'Implant surgery'
    },
    {
        id: 4,
        finance_id: 104,
        transaction_id: 'INV-2026-0004',
        transaction_type: 'invoice',
        patient_name: 'Farah N.',
        patient_gender: 'female',
        provider_name: 'Dr. Guest Specialist',
        provider_type: 'user',
        currency: 'UNKNOWN',
        gross_amount: 600.0,
        provider_share_amount: 300.0,
        clinic_share_amount: 300.0,
        provider_paid_amount: 0.0,
        provider_balance_due: 300.0,
        status: 'unpaid',
        created_at: '2026-11-04 14:00:00',
        created_at_formatted: '2026-11-04 14:00',
        description: 'Crown placement'
    },
    {
        id: 5,
        finance_id: 105,
        transaction_id: 'INV-2026-0005',
        transaction_type: 'invoice',
        patient_name: 'Void Case',
        patient_gender: 'male',
        provider_name: 'Dr. Sara Ahmad',
        provider_type: 'user',
        currency: 'ILS',
        gross_amount: 400.0,
        provider_share_amount: 200.0,
        clinic_share_amount: 200.0,
        provider_paid_amount: 0.0,
        provider_balance_due: 0.0,
        status: 'void',
        created_at: '2026-11-05 08:00:00',
        created_at_formatted: '2026-11-05 08:00',
        description: 'Cancelled procedure'
    }
];


http.createServer((req,res)=>{
 const url=new URL(req.url,'http://127.0.0.1:57526');
 res.setHeader('Cache-Control','no-store');
 const json=body=>{res.setHeader('Content-Type','application/json');res.end(JSON.stringify(body));};
 if(req.method==='POST'&&url.pathname==='/finance/payouts/data'){req.resume();json({draw:1,recordsTotal:syntheticPayouts.length,recordsFiltered:syntheticPayouts.length,data:syntheticPayouts,csrf_token:'synthetic'});return;}
 if(req.method!=='GET'){res.writeHead(405);res.end('Presenter server never writes financial records.');return;}
 const registration=url.pathname.match(/^\/registration-search\/(en|ar)\/(page|dialog|edit|inventory)\.html$/);
 if(registration){
  const permission=url.searchParams.get('permission')==='denied'?'denied':'allowed';
  try{
   const html=execFileSync('php',[path.join(root,'tests/_support/fixtures/RegistrationSearchPresenter.php'),registration[1],registration[2],permission],{windowsHide:true,encoding:'utf8'});
   res.setHeader('Content-Type','text/html;charset=utf-8');res.end(html);
  }catch(error){console.error('Registration presenter failed:',error.message);res.writeHead(500);res.end('Synthetic presenter failed');}
  return;
 }
 const patientPage=url.pathname.match(/^\/patient-workflow\/(en|ar)\/(workspace|examination|treatment_create|money_trail)\.html$/);
 if(patientPage){
  const permissions=url.searchParams.get('permissions')||'all';
  const money=url.searchParams.get('money')||'missing';
  if(!['all','none','treatment-only'].includes(permissions)||!['missing','malformed','truncated','empty','mixed'].includes(money)){res.writeHead(400);res.end();return;}
  try{
   const html=execFileSync('php',[path.join(root,'tests/_support/fixtures/PatientWorkflowPresenter.php'),patientPage[1],patientPage[2],permissions,money],{windowsHide:true,encoding:'utf8'});
   res.setHeader('Content-Type','text/html;charset=utf-8');res.end(html);
  }catch(error){console.error('Patient presenter render failed:',error.message);res.writeHead(500);res.end('Synthetic presenter failed.');}
  return;
 }
 if(url.pathname==='/finance/settlement-context'){
  const row=syntheticPayouts.find(row=>row.id===Number(url.searchParams.get('targets')));
  if(!row){res.writeHead(404);res.end();return;}
  json({success:true,context:{request_key:'synthetic-presenter-only',settlement_revision:'synthetic-revision',currency:row.currency,transaction_id:row.transaction_id,facts:{total:row.provider_share_amount.toFixed(2),paid:row.provider_paid_amount.toFixed(2),due:row.provider_balance_due.toFixed(2)}}});return;
 }
 if(pages[url.pathname]){res.setHeader('Content-Type','text/html;charset=utf-8');res.end(pages[url.pathname]);return;}
 if(url.pathname.startsWith('/assets/')){
  const file=path.resolve(publicRoot,'.'+url.pathname);
  if(!file.startsWith(publicRoot+path.sep)){res.writeHead(403);res.end();return;}
  fs.readFile(file,(error,data)=>{if(error){res.writeHead(404);res.end();return;}res.setHeader('Content-Type',({'.css':'text/css','.js':'text/javascript','.woff2':'font/woff2'})[path.extname(file)]||'application/octet-stream');res.end(data);});return;
 }
 res.writeHead(404);res.end();
}).listen(57526,'127.0.0.1',()=>console.log('Synthetic workflow presenter on 127.0.0.1:57526; no DB or application writes.'));
