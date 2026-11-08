// Retained runner for real HTTP browser navigation acceptance.
// Boots CodeIgniter on isolated port 57527 with 127.0.0.1:3307 disposable DB.
// Enforces structured try/finally lifecycle, bounded subprocesses, verified teardown, and secret isolation.
'use strict';
const path = require('node:path');
const fs = require('node:fs');
const os = require('node:os');
const net = require('node:net');
const http = require('node:http');
const crypto = require('node:crypto');
const { spawn, execFileSync } = require('node:child_process');

const root = path.resolve(__dirname, '../..');
const host = '127.0.0.1';
const port = 57527;
const phpBinary = process.env.PHP_BINARY || 'php';

const candidates = [
    process.env.PLAYWRIGHT_PATH,
    'playwright',
    path.join(process.env.APPDATA || path.join(os.homedir(), 'AppData/Roaming'), 'npm/node_modules/@playwright/mcp/node_modules/playwright')
].filter(Boolean);

function loadPlaywright() {
    for (const candidate of candidates) {
        try { return require(candidate); } catch (e) {
            if (e.code !== 'MODULE_NOT_FOUND') throw e;
        }
    }
    throw new Error('Playwright unavailable. Set PLAYWRIGHT_PATH.');
}

function checkPortFree(targetPort) {
    return new Promise((resolve, reject) => {
        const probe = net.createServer();
        probe.once('error', err => reject(new Error(`Port ${targetPort} is already in use: ${err.message}`)));
        probe.listen(targetPort, host, () => probe.close(resolve));
    });
}

function bounded(promise, ms, message) {
    let timer;
    return Promise.race([promise, new Promise((_, reject) => { timer = setTimeout(() => reject(new Error(message)), ms); })])
        .finally(() => clearTimeout(timer));
}

function waitForHttpReady(url, token, runId, child, maxMs = 12000) {
    return new Promise((resolve, reject) => {
        let done = false, request, retryTimer;
        const finish = error => {
            if (done) return;
            done = true; clearTimeout(deadline); clearTimeout(retryTimer);
            child.removeListener('exit', exited); child.removeListener('error', failed);
            if (request) request.destroy();
            error ? reject(error) : resolve();
        };
        const exited = code => finish(new Error('Owned HTTP server exited before readiness: ' + code));
        const failed = error => finish(error);
        const deadline = setTimeout(() => finish(new Error('HTTP readiness deadline exceeded.')), maxMs);
        child.once('exit', exited); child.once('error', failed);
        const check = () => {
            if (done) return;
            if (child.exitCode !== null || child.signalCode !== null) return exited(child.exitCode);
            let retried = false;
            const retry = () => {
                if (done || retried) return;
                retried = true; retryTimer = setTimeout(check, 150);
            };
            request = http.get(url, { headers: { 'X-Test-Run-Token': token } }, response => {
                response.resume();
                if (response.statusCode === 200 && response.headers['x-test-run-id'] === runId) finish();
                else retry();
            });
            request.once('error', retry);
            request.setTimeout(800, () => request.destroy(new Error('Readiness request stalled.')));
        };
        check();
    });
}

async function stopChild(child) {
    if (!child || child.exitCode !== null || child.signalCode !== null) return;
    await new Promise((resolve, reject) => {
        const limit = setTimeout(() => reject(new Error('Owned server termination deadline exceeded.')), 6000);
        const force = setTimeout(() => {
            try { child.kill('SIGKILL'); } catch (_) {}
        }, 3000);
        child.once('exit', () => {
            clearTimeout(force); clearTimeout(limit);
            resolve();
        });
        try { child.kill(); } catch (_) {
            clearTimeout(force); clearTimeout(limit);
            reject(new Error('Owned server termination failed.'));
        }
    });
}

async function main() {
    const playwright = loadPlaywright();
    await checkPortFree(port);

    const runId = crypto.randomBytes(8).toString('hex');
    const runToken = crypto.randomBytes(16).toString('hex');
    const tmpDir = path.join(root, 'writable/tmp');
    if (!fs.existsSync(tmpDir)) {
        fs.mkdirSync(tmpDir, { recursive: true });
    }
    const receiptPath = path.join(tmpDir, `test_fixture_${runId}.json`);
    const fixtureScript = path.join(root, 'tests/_support/PatientHttpNavigationFixture.php');

    const keys = ['hostname', 'port', 'database', 'username', 'password', 'DBDriver', 'DBPrefix'];
    let verifiedConn = process.env.DENTA_VERIFIED_TEST_CONNECTION;
    if (!verifiedConn) {
        if (!keys.every(key => process.env['database.tests.' + key] !== undefined)) {
            throw new Error('Explicit approved test connection required; no credential defaults.');
        }
        verifiedConn = JSON.stringify(Object.fromEntries(keys.map(key => {
            const value = process.env['database.tests.' + key];
            return [key, value === 'empty' ? '' : value];
        })));
    }

    const artifactDir = path.join(tmpDir, 'patient_http_' + runId);
    const runtimeDir = path.join(artifactDir, 'runtime');
    const screenshotDir = path.join(artifactDir, 'screenshots');
    const procEnv = {
        ...process.env,
        DENTA_VERIFIED_TEST_CONNECTION: verifiedConn,
        DENTA_TEST_RUN_TOKEN: runToken,
        DENTA_TEST_RUN_ID: runId,
        DENTA_TEST_WRITEPATH: runtimeDir
    };

    let serverChild = null;
    let browser = null;
    let testResult = null;
    let executionError = null;
    let teardownVerified = false;
    let teardownError = null;

    try {
        fs.mkdirSync(artifactDir, { mode: 0o700 });
        fs.writeFileSync(path.join(artifactDir, '.owner'), runId, { flag: 'wx', mode: 0o600 });
        fs.mkdirSync(runtimeDir, { mode: 0o700 });
        fs.mkdirSync(screenshotDir, { mode: 0o700 });
        for (const name of ['logs', 'cache', 'session', 'uploads']) fs.mkdirSync(path.join(runtimeDir, name), { mode: 0o700 });
        // 1. Transactional Provisioning of Run-Scoped Fixtures
        const provOutput = execFileSync(phpBinary, [fixtureScript, 'provision', runId, receiptPath], {
            cwd: root,
            encoding: 'utf8',
            timeout: 15000,
            env: procEnv
        });
        const fixtureData = JSON.parse(provOutput.trim());
        if (!fixtureData.success) {
            throw new Error(`Synthetic fixture provisioning failed: ${fixtureData.error || 'unknown'}`);
        }

        const clinicId = fixtureData.clinic_id;
        const userId = fixtureData.user_id;

        const queryDbCount = async () => {
            const out = execFileSync(phpBinary, [fixtureScript, 'count', runId, receiptPath], {
                cwd: root,
                encoding: 'utf8',
                timeout: 10000,
                env: procEnv
            });
            const res = JSON.parse(out.trim());
            if (!res.success) throw new Error(res.error || 'Count failed');
            return res.patient_count;
        };

        // 2. Start PHP built-in web server with isolated test router & run token
        serverChild = spawn(phpBinary, [
            '-S', `${host}:${port}`,
            '-t', 'public',
            path.join(root, 'tests/_support/test_http_server.php')
        ], {
            cwd: root,
            stdio: ['ignore', 'ignore', 'pipe'],
            windowsHide: true,
            env: procEnv
        });

        serverChild.stderr.on('data', data => {
            const line = data.toString().trim();
            if (line.includes('Preflight') || line.includes('Fatal') || line.includes('Error')) {
                console.error('[SERVER LOG]', line);
            }
        });

        await waitForHttpReady(`http://${host}:${port}/auth/login`, runToken, runId, serverChild, 12000);

        // 3. Launch browser and run real navigation suite
        browser = await playwright.chromium.launch({ channel: 'chrome', headless: true, timeout: 15000 });
        const context = await browser.newContext({
            extraHTTPHeaders: {
                'X-Test-Run-Token': runToken
            }
        });
        context.setDefaultTimeout(8000);
        context.setDefaultNavigationTimeout(8000);

        const page = await context.newPage();
        const testSuite = require(path.join(__dirname, 'PatientRealHttpNavigation.browser.js'));

        testResult = await bounded(testSuite(page, {
            baseUrl: `http://${host}:${port}`,
            clinicId,
            userId,
            email: fixtureData.email,
            password: fixtureData.password,
            screenshotDir,
            queryDbCount
        }), 60000, 'Actual navigation suite exceeded 60 seconds.');
        if (!testResult?.success || testResult.errors?.length || testResult.resultsCount !== testResult.results?.length || testResult.resultsCount < 6) throw new Error('Navigation suite did not return complete PASS evidence.');

    } catch (err) {
        executionError = err;
    } finally {
        // Structured Teardown Lifecycle
        if (browser) {
            try { await bounded(browser.close(), 8000, 'Owned browser close deadline exceeded.'); } catch (error) { teardownError = error; }
        }
        if (serverChild) {
            try { await stopChild(serverChild); } catch (error) { teardownError = error; }
        }

        // 4. Complete and Verified Teardown
        try {
            if (fs.existsSync(receiptPath)) {
                const tdOutput = execFileSync(phpBinary, [fixtureScript, 'teardown', runId, receiptPath], {
                    cwd: root,
                    encoding: 'utf8',
                    timeout: 15000,
                    env: procEnv
                });
                const tdRes = JSON.parse(tdOutput.trim());
                if (tdRes.success && tdRes.cleaned) {
                    teardownVerified = true;
                } else {
                    teardownError = new Error(`Teardown failed: ${tdRes.error || 'unknown'}`);
                }
            } else {
                teardownError = new Error('Provisioning receipt missing; cleanup cannot be certified.');
            }
        } catch (err) {
            teardownError = err;
        }
    }

    if (executionError) {
        console.error('TEST EXECUTION FAILURE:', executionError.stack || executionError.message);
        if (teardownError) console.error('TEARDOWN ALSO FAILED:', teardownError.stack || teardownError.message);
        process.exit(1);
    }

    if (!teardownVerified || teardownError) {
        console.error('CLEANUP INTEGRITY FAILURE: Fixtures were not cleanly retired!', teardownError?.stack || teardownError?.message);
        process.exit(1);
    }

    const resolvedRuntime = fs.realpathSync(runtimeDir);
    if (resolvedRuntime !== path.resolve(runtimeDir) || fs.lstatSync(runtimeDir).isSymbolicLink()
        || fs.readFileSync(path.join(artifactDir, '.owner'), 'utf8') !== runId
        || path.resolve(runtimeDir) !== path.join(root, 'writable/tmp', 'patient_http_' + runId, 'runtime')) {
        throw new Error('Runtime cleanup confinement check failed.');
    }
    fs.rmSync(resolvedRuntime, { recursive: true }); // Checked absolute owned target; screenshots/evidence remain separate.
    fs.writeFileSync(path.join(artifactDir, 'pass.json'), JSON.stringify({ runId, target: 'democa_dental_saas_test@127.0.0.1:3307', fixtureCleanup: true, ...testResult }, null, 2), { flag: 'wx', mode: 0o600 });
    // Publish PASS only after verified cleanup
    console.log(JSON.stringify(testResult, null, 2));
    process.exit(0);
}

main().catch(err => {
    console.error('RUNNER UNHANDLED ERROR:', err.stack || err.message);
    process.exit(1);
});
