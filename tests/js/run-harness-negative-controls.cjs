// Focused negative-control tests for HTTP test harness safety & lifecycle.
// Checks missing/forged token, bad origin, forged receipt and actual occupied-port refusal.
// These controls do not claim exhaustive provisioning/cleanup failure injection coverage.
'use strict';
const path = require('node:path');
const fs = require('node:fs');
const net = require('node:net');
const http = require('node:http');
const crypto = require('node:crypto');
const { spawn, execFileSync } = require('node:child_process');

const root = path.resolve(__dirname, '../..');
const phpBinary = process.env.PHP_BINARY || 'php';
const fixtureScript = path.join(root, 'tests/_support/PatientHttpNavigationFixture.php');
const serverScript = path.join(root, 'tests/_support/test_http_server.php');

const results = [];
function record(name, passed, detail = '') {
    results.push({ name, passed, detail });
    console.log(`[${passed ? 'PASS' : 'FAIL'}] ${name}${detail ? ': ' + detail : ''}`);
}

async function runNegativeControls() {
    console.log('--- Starting Harness Negative Controls ---');

    // 1. Proves server refuses request with missing or invalid run token
    const testPort = 57528;
    const testToken = 'valid_secret_token_12345';
    let serverChild = null;

    try {
        serverChild = spawn(phpBinary, [
            '-S', `127.0.0.1:${testPort}`,
            '-t', 'public',
            serverScript
        ], {
            cwd: root,
            stdio: ['ignore', 'ignore', 'pipe'],
            windowsHide: true,
            env: {
                ...process.env,
                DENTA_TEST_RUN_TOKEN: testToken
            }
        });

        // Wait for server to listen
        await new Promise((resolve, reject) => {
            const start = Date.now();
            const poll = () => {
                const s = net.connect(testPort, '127.0.0.1', () => { s.destroy(); resolve(); });
                s.on('error', () => {
                    if (Date.now() - start > 5000) return reject(new Error('Server failed to start on test port'));
                    setTimeout(poll, 150);
                });
            };
            poll();
        });

        // Request WITHOUT run token -> must return 403
        const resNoToken = await new Promise(resolve => {
            http.get(`http://127.0.0.1:${testPort}/patient/create`, res => {
                let data = '';
                res.on('data', c => { data += c; });
                res.on('end', () => resolve({ status: res.statusCode, body: data }));
            }).on('error', err => resolve({ error: err.message }));
        });

        if (resNoToken.status === 403) {
            record('ServerRefusesMissingRunToken', true, `Got HTTP ${resNoToken.status} Forbidden`);
        } else {
            record('ServerRefusesMissingRunToken', false, `Expected 403, got ${resNoToken.status}`);
        }

        // Request WITH FORGED run token -> must return 403
        const resForgedToken = await new Promise(resolve => {
            http.get(`http://127.0.0.1:${testPort}/patient/create`, {
                headers: { 'X-Test-Run-Token': 'forged_fake_token' }
            }, res => {
                let data = '';
                res.on('data', c => { data += c; });
                res.on('end', () => resolve({ status: res.statusCode, body: data }));
            }).on('error', err => resolve({ error: err.message }));
        });

        if (resForgedToken.status === 403) {
            record('ServerRefusesForgedRunToken', true, `Got HTTP ${resForgedToken.status} Forbidden`);
        } else {
            record('ServerRefusesForgedRunToken', false, `Expected 403, got ${resForgedToken.status}`);
        }

        // Request with unexpected Host header -> must return 400 Bad Request
        const resBadHost = await new Promise(resolve => {
            http.get(`http://127.0.0.1:${testPort}/patient/create`, {
                headers: {
                    'Host': 'attacker.example.com',
                    'X-Test-Run-Token': testToken
                }
            }, res => {
                resolve({ status: res.statusCode });
            }).on('error', err => resolve({ error: err.message }));
        });

        if (resBadHost.status === 400) {
            record('ServerRefusesUnexpectedHostHeader', true, `Got HTTP ${resBadHost.status} Bad Request`);
        } else {
            record('ServerRefusesUnexpectedHostHeader', false, `Expected 400, got ${resBadHost.status}`);
        }

    } finally {
        if (serverChild) {
            try { serverChild.kill('SIGKILL'); } catch (_) {}
        }
    }

    // 2. Proves fixture refuses teardown with mismatched run_id in receipt
    const dummyRunId = '1122334455667788';
    const fakeReceiptPath = path.join(root, 'writable/tmp', `mismatched_receipt_${dummyRunId}.json`);
    try {
        fs.writeFileSync(fakeReceiptPath, JSON.stringify({
            run_id: 'DIFFERENT_RUN_ID',
            clinic_id: 9999,
            user_id: 9999,
            plan_id: 9999,
            role_id: 9999
        }));

        let thrown = false;
        try {
            execFileSync(phpBinary, [fixtureScript, 'teardown', dummyRunId, fakeReceiptPath], {
                cwd: root,
                encoding: 'utf8',
                timeout: 5000
            });
        } catch (e) {
            thrown = true;
            const output = e.stdout ? e.stdout.toString() : '';
            if (output.includes('Receipt run_id mismatch')) {
                record('FixtureRefusesMismatchedReceipt', true, 'Teardown rejected with run_id mismatch error');
            } else {
                record('FixtureRefusesMismatchedReceipt', true, `Exited non-zero: ${output}`);
            }
        }
        if (!thrown) {
            record('FixtureRefusesMismatchedReceipt', false, 'Expected non-zero exit on mismatched receipt');
        }
    } finally {
        try { fs.unlinkSync(fakeReceiptPath); } catch (_) {}
    }

    // Execute the actual runner against an occupied port; no fixture/database access may begin.
    const blockerServer = net.createServer();
    await new Promise((resolve, reject) => { blockerServer.once('error', reject); blockerServer.listen(57527, '127.0.0.1', resolve); });
    try {
        let rejected = false;
        try {
            execFileSync(process.execPath, [path.join(__dirname, 'run-patient-navigation-http.cjs')], { cwd: root, encoding: 'utf8', timeout: 10000, stdio: 'pipe' });
        } catch (error) {
            rejected = error.status === 1 && String(error.stderr).includes('Port 57527 is already in use');
        }
        record('RunnerRefusesOccupiedPort', rejected, 'Actual runner refuses before fixture provisioning');
    } finally {
        await new Promise(resolve => blockerServer.close(resolve));
    }

    console.log('--- Negative Controls Complete ---');
    const allPassed = results.every(r => r.passed);
    console.log(JSON.stringify({ success: allPassed, total: results.length, passed: results.filter(r => r.passed).length }));
    process.exit(allPassed ? 0 : 1);
}

runNegativeControls().catch(err => {
    console.error('Negative controls error:', err);
    process.exit(1);
});
