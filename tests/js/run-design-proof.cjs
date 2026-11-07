// Runs only isolated synthetic presenters; no application or database boot.
'use strict';
const path = require('node:path');
const fs = require('node:fs');
const os = require('node:os');
const net = require('node:net');
const vm = require('node:vm');
const { spawn } = require('node:child_process');

const root = path.resolve(__dirname, '../..');
const host = '127.0.0.1', port = 57526;
const candidates = [process.env.PLAYWRIGHT_PATH, 'playwright',
    path.join(process.env.APPDATA || path.join(os.homedir(), 'AppData/Roaming'), 'npm/node_modules/@playwright/mcp/node_modules/playwright')].filter(Boolean);

function loadPlaywright() {
    for (const candidate of candidates) {
        try { return require(candidate); } catch (error) {
            if (error.code !== 'MODULE_NOT_FOUND') throw error;
        }
    }
    throw new Error('Playwright unavailable. Set PLAYWRIGHT_PATH to an existing Playwright module; no dependency installation is performed.');
}

function checkPort() {
    return new Promise((resolve, reject) => {
        const probe = net.createServer();
        probe.once('error', reject);
        probe.listen(port, host, () => probe.close(resolve));
    });
}

function waitForOwnedServer(child) {
    return new Promise((resolve, reject) => {
        let output = '';
        const finish = error => {
            clearTimeout(timer);
            child.removeListener('error', onError);
            child.removeListener('exit', onExit);
            child.stdout.removeListener('data', onData);
            child.stdout.resume();
            error ? reject(error) : resolve();
        };
        const onError = error => finish(error);
        const onExit = code => finish(new Error(`Owned presenter exited before readiness (${code}).`));
        const onData = data => {
            output = (output + data.toString()).slice(-4096);
            if (output.includes(`Synthetic workflow presenter on ${host}:${port}; no DB or application writes.`)) finish();
        };
        const timer = setTimeout(() => finish(new Error('Owned presenter startup exceeded 10 seconds.')), 10000);
        child.once('error', onError);
        child.once('exit', onExit);
        child.stdout.on('data', onData);
    });
}

async function stopOwnedServer(child) {
    if (!child || child.exitCode !== null || child.signalCode !== null) return;
    await new Promise((resolve, reject) => {
        const force = setTimeout(() => child.kill('SIGKILL'), 2000);
        const limit = setTimeout(() => reject(new Error('Owned presenter did not exit after termination.')), 4000);
        child.once('exit', () => { clearTimeout(force); clearTimeout(limit); resolve(); });
        child.kill();
    });
}

async function main() {
    const playwright = loadPlaywright();
    await checkPort(); // Refuse occupied ports; readiness comes from our child, not a borrowed HTTP response.
    let child, browser, deadline;
    try {
        child = spawn(process.execPath, [path.join(root, 'tests/js/finance-presenter-server.cjs')], {
            cwd: root, stdio: ['ignore', 'pipe', 'inherit'], windowsHide: true
        });
        await waitForOwnedServer(child);
        browser = await playwright.chromium.launch({ channel: 'chrome', headless: true, timeout: 15000 });
        const context = await browser.newContext();
        context.setDefaultTimeout(5000);
        context.setDefaultNavigationTimeout(5000);
        const page = await context.newPage();
        const test = vm.runInThisContext(fs.readFileSync(path.join(__dirname, 'DesignProofInteraction.browser.js'), 'utf8'), {
            filename: 'DesignProofInteraction.browser.js'
        });
        if (typeof test !== 'function') throw new Error('Browser fixture did not evaluate to a function.');
        const result = await Promise.race([test(page), new Promise((_, reject) => {
            deadline = setTimeout(() => reject(new Error('Browser suite exceeded 45 seconds.')), 45000);
        })]);
        if (!result.success || result.errors.length) throw new Error('Browser suite returned failures.');
        console.log(JSON.stringify({ success: true, groups: result.resultsCount, errors: result.errors }));
    } finally {
        clearTimeout(deadline);
        try { if (browser) await browser.close(); }
        finally { await stopOwnedServer(child); }
    }
}

main().catch(error => { console.error(error.stack || error.message); process.exitCode = 1; });
