// Real Chromium against actual PHP-rendered synthetic views; no remote requests or dependencies.
// Generate fixtures first with ScheduleImpactUiRenderingTest through the safe test runner.
const fs = require('node:fs');
const path = require('node:path');
const {spawn} = require('node:child_process');
const assert = require('node:assert/strict');
const {pathToFileURL} = require('node:url');
const root = process.cwd();
const binary = process.env.SCHEDULE_IMPACT_BROWSER_BINARY || process.env.PR2_BROWSER_BINARY || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
let assertions = 0;
const check = (condition, message) => { assertions++; assert.ok(condition, message); };

(async () => {
    for (const locale of ['en', 'ar']) for (const surface of ['preview', 'recheck']) {
        assert.ok(fs.existsSync(path.join(root, `tmp/schedule-impact-${surface === 'preview' ? 'mobile' : 'recheck'}-${locale}.html`)), 'Run the safe PHP rendering test first.');
    }
    const profile = fs.mkdtempSync(path.join(root, 'tmp/schedule-impact-browser-'));
    const browser = spawn(binary, ['--headless', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--disable-background-networking', '--remote-debugging-address=127.0.0.1', '--remote-debugging-port=0', '--host-resolver-rules=MAP * 127.0.0.1, EXCLUDE localhost', '--user-data-dir=' + profile, 'about:blank'], {stdio:'ignore', windowsHide:true});
    let socket;
    try {
        const active = path.join(profile, 'DevToolsActivePort');
        const deadline = Date.now() + 10000;
        while (!fs.existsSync(active)) {
            if (Date.now() > deadline) throw Error('Task browser did not start.');
            await new Promise(resolve => setTimeout(resolve, 50));
        }
        const port = Number(fs.readFileSync(active, 'utf8').split('\n')[0]);
        const page = await (await fetch(`http://127.0.0.1:${port}/json/new?about:blank`, {method:'PUT'})).json();
        socket = new WebSocket(page.webSocketDebuggerUrl);
        await new Promise((resolve, reject) => { socket.onopen=resolve; socket.onerror=reject; });
        let nextId=0;
        const pending = new Map();
        let onLoad;
        socket.onmessage = event => {
            const message=JSON.parse(event.data);
            if (message.id) {
                const item=pending.get(message.id);
                if (item) { pending.delete(message.id); clearTimeout(item.timer); message.error ? item.reject(Error(JSON.stringify(message.error))) : item.resolve(message.result); }
            } else if (message.method==='Page.loadEventFired' && onLoad) { onLoad(); onLoad=null; }
        };
        const call = (method, params={}) => new Promise((resolve,reject) => {
            const id=++nextId;
            const timer=setTimeout(() => { pending.delete(id); reject(Error('Browser timeout: '+method)); },20000);
            pending.set(id,{resolve,reject,timer}); socket.send(JSON.stringify({id,method,params}));
        });
        const evaluate = async expression => {
            const result=await call('Runtime.evaluate',{expression,awaitPromise:true,returnByValue:true});
            if (result.exceptionDetails) throw Error(JSON.stringify(result.exceptionDetails));
            return result.result.value;
        };
        await call('Page.enable');
        await call('Network.enable');
        await call('Network.setBlockedURLs',{urls:['http://*','https://*']});
        for (const locale of ['en','ar']) for (const width of [320,360,390]) for (const surface of ['preview','recheck']) {
            const label=`${surface}/${locale}/${width}`;
            await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:true});
            const loaded=new Promise(resolve => { onLoad=resolve; });
            await call('Page.navigate',{url:pathToFileURL(path.join(root,`tmp/schedule-impact-${surface === 'preview' ? 'mobile' : 'recheck'}-${locale}.html`)).href});
            await loaded;
            const result=await evaluate(`(async()=>{
                await document.fonts.ready; await new Promise(requestAnimationFrame);
                const metadata=JSON.parse(document.getElementById('impact-fixture-data').textContent);
                const text=document.body.innerText;
                const refs=[...document.querySelectorAll('span,bdi')].filter(e=>e.textContent.trim()==='#'+metadata.provider_id);
                const reference=refs.find(e=>e.getBoundingClientRect().width>0 && getComputedStyle(e).display!=='none');
                const scrolling=[...document.querySelectorAll('div')].some(e=>['auto','scroll'].includes(getComputedStyle(e).overflowX)&&e.scrollWidth>e.clientWidth+1);
                const form=document.getElementById('schedule-impact-apply');
                const reasons=form?[...form.querySelectorAll('textarea[name^="dispositions["]')]:[];
                const ids=reasons.map(e=>e.name.match(/^dispositions\\[([0-9]+)\\]\\[reason\\]$/)?.[1]);
                const ack=form?.querySelector('[name="acknowledged"]');
                return {viewport:innerWidth,pageWidth:document.documentElement.scrollWidth,dir:document.documentElement.dir,
                    metadata,providerLiteral:text.includes(metadata.hostile_provider),beforeLiteral:text.includes(metadata.before_reason),
                    afterLiteral:text.includes(metadata.after_reason),retainedLiteral:text.includes(metadata.retain_reason),
                    injection:!!document.querySelector('svg,img[src="impact-hostile"]')||typeof impactBad==='function',
                    refVisible:!!reference,scrolling,ids,expected:metadata.affected_ids.map(String),
                    ack:ack?{type:ack.type,checked:ack.checked,required:ack.required}:null,
                    reasonCount:reasons.length,traditionalCount:form?form.querySelectorAll('[name^="dispositions["]').length:0,
                    reviewAction:[...document.forms].some(f=>f.method==='post'&&f.querySelector('[name="refresh_preview"]'))};
            })()`);
            check(result.viewport===width,label+': viewport');
            check(result.pageWidth<=width+1,label+': whole page must not overflow; table may scroll internally');
            check(result.dir===(locale==='ar'?'rtl':'ltr'),label+': locale direction');
            check(result.providerLiteral,label+': hostile provider is literal text');
            check(result.afterLiteral,label+': after reason is visible literal text');
            check(!result.injection,label+': hostile values cannot create active DOM');
            check(result.refVisible,label+': stable doctor reference remains visible');
            check(result.scrolling,label+': wide report scrolls inside its own container');
            check(result.reviewAction,label+': explicit refresh/review POST exists');
            if(surface==='recheck') {
                check(result.retainedLiteral,label+': verified retained reason is visible text');
                check(result.reasonCount===0,label+': recheck does not contain duplicate disposition controls');
                continue;
            }
            check(result.beforeLiteral,label+': before reason is visible literal text');
            check(JSON.stringify(result.ids)===JSON.stringify(result.expected),label+': exactly one reason per affected visit and no controls for compliant visit');
            check(new Set(result.ids).size===result.ids.length,label+': unique appointment identities');
            check(result.traditionalCount>1000,label+': real rendered fixture exceeds default PHP max_input_vars');
            check(result.ack?.type==='checkbox'&&result.ack.required&&!result.ack.checked,label+': acknowledgment is explicit and initially unchecked');
            const submitted=await evaluate(`(()=>{
                const form=document.getElementById('schedule-impact-apply');
                const metadata=JSON.parse(document.getElementById('impact-fixture-data').textContent);
                form.querySelectorAll('textarea[name^="dispositions["]').forEach(field=>{
                    const id=field.name.match(/^dispositions\\[([0-9]+)\\]/)[1];
                    field.value='Retain \u0627\u0644\u0639\u0631\u0628\u064a\u0629 #'+id+' <script>literal</script>';
                });
                const ack=form.querySelector('[name="acknowledged"]');
                const blockedWithoutAck=!form.checkValidity(); ack.checked=true;
                const validWithAck=form.checkValidity(); let captured=null;
                form.addEventListener('submit',event=>{event.preventDefault();const data=new FormData(form);
                    const entries=[...data.entries()].map(([k,v])=>[k,String(v)]);
                    const json=data.get('dispositions_json'); const parsed=JSON.parse(json);
                    const expected=metadata.affected_ids.map(String);
                    captured={fieldCount:entries.length,jsonCount:data.getAll('dispositions_json').length,
                        allIds:JSON.stringify(Object.keys(parsed))===JSON.stringify(expected),
                        reasons:expected.every(id=>parsed[id].action==='retain'&&parsed[id].reason==='Retain \u0627\u0644\u0639\u0631\u0628\u064a\u0629 #'+id+' <script>literal</script>'),
                        ack:data.get('acknowledged'),oldNames:entries.some(([key])=>key.startsWith('dispositions[')),
                        target:data.get('target_type'),proof:data.get('proof_token'),proposal:JSON.parse(data.get('proposed_dto_json'))};
                });
                const apply=[...form.querySelectorAll('button[type="submit"]')].find(b=>b.name!=='refresh_preview');
                form.requestSubmit(apply);return {blockedWithoutAck,validWithAck,captured};
            })()`);
            check(submitted.blockedWithoutAck,label+': browser refuses implicit acknowledgment');
            check(submitted.validWithAck,label+': filled dispositions plus acknowledgment are submittable');
            check(!!submitted.captured,label+': real submit handler executed');
            check(submitted.captured.allIds&&submitted.captured.reasons,label+': JSON preserves every ID and exact Unicode reason beyond max_input_vars');
            check(submitted.captured.jsonCount===1&&!submitted.captured.oldNames,label+': only one compact disposition JSON field is sent');
            check(submitted.captured.fieldCount<30,label+': submit does not depend on PHP expanded field limit');
            check(submitted.captured.ack==='1'&&submitted.captured.target==='clinic_hours'&&submitted.captured.proof==='a'.repeat(64),label+': proof and explicit acknowledgment survive serialization');
            check(submitted.captured.proposal.dynamic_doctor_scheduling_enabled==='1',label+': exact frozen proposal survives serialization');
            if(width===360){const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(root,`tmp/schedule-impact-${locale}-360.png`),Buffer.from(shot.data,'base64'));}
        }
        await call('Browser.close').catch(()=>{});
        console.log(`Schedule impact real Chromium: ${assertions} assertions passed (actual EN/AR preview/recheck at 320/360/390px; 505 affected visits and compact submit JSON).`);
    } finally { socket?.close(); if(browser.exitCode===null)browser.kill(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
