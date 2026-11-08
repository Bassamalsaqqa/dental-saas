// DB-free denial tests of the real router/runner, with exclusively owned scratch files.
'use strict';
const path = require('node:path');
const fs = require('node:fs');
const net = require('node:net');
const http = require('node:http');
const crypto = require('node:crypto');
const { spawn, execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const php = process.env.PHP_BINARY || 'php';
const router = path.join(root, 'tests/_support/test_http_server.php');
const fixture = path.join(root, 'tests/_support/PatientHttpNavigationFixture.php');
const results = [];
const assert = (ok, message) => { if (!ok) throw new Error(message); };
const record = name => { results.push({ name, passed: true }); console.log('[PASS] ' + name); };

function exclusive(file, content) { fs.writeFileSync(file, content, { flag: 'wx', mode: 0o600 }); }
function free(port) {
    return new Promise((resolve, reject) => {
        const socket = net.createServer(); socket.once('error', reject);
        socket.listen(port, '127.0.0.1', () => socket.close(resolve));
    });
}
async function stop(child) {
    if (!child || child.exitCode !== null || child.signalCode !== null) return;
    await new Promise((resolve, reject) => {
        const limit = setTimeout(() => reject(new Error('Owned negative server did not exit.')), 4000);
        child.once('exit', () => { clearTimeout(limit); resolve(); });
        child.kill();
    });
}
async function server(port, token) {
    await free(port);
    const child = spawn(php, ['-S', '127.0.0.1:' + port, '-t', 'public', router], {
        cwd: root, stdio: ['ignore', 'ignore', 'pipe'], windowsHide: true,
        env: { ...process.env, DENTA_TEST_RUN_TOKEN: token, DENTA_TEST_RUN_ID: crypto.randomBytes(8).toString('hex'), DENTA_VERIFIED_TEST_CONNECTION: 'invalid-json' }
    });
    try {
        await new Promise((resolve, reject) => {
            let output = '';
            const timer = setTimeout(() => reject(new Error('Negative server startup timeout.')), 5000);
            const fail = error => { clearTimeout(timer); reject(error); };
            child.once('error', fail);
            child.once('exit', code => fail(new Error('Negative server exited: ' + code)));
            child.stderr.on('data', data => {
                output = (output + data.toString()).slice(-4096);
                if (output.includes('127.0.0.1:' + port) && output.includes('started')) { clearTimeout(timer); resolve(); }
            });
        });
        return child;
    } catch (error) { await stop(child); throw error; }
}
function request(port, headers = {}) {
    return new Promise((resolve, reject) => {
        const req = http.get('http://127.0.0.1:' + port + '/patient/create', { headers }, response => {
            let body = ''; response.on('data', data => { body += data; });
            response.on('end', () => resolve({ status: response.statusCode, body }));
        });
        req.once('error', reject);
        req.setTimeout(2000, () => req.destroy(new Error('Negative HTTP request timeout.')));
    });
}

async function main() {
    const token = crypto.randomBytes(16).toString('hex');
    let child;
    try {
        child = await server(57527, token);
        assert((await request(57527)).status === 403, 'Missing token did not reject.');
        record('ServerRefusesMissingRunToken');
        assert((await request(57527, { 'X-Test-Run-Token': 'forged' })).status === 403, 'Forged token did not reject.');
        record('ServerRefusesForgedRunToken');
        assert((await request(57527, { Host: 'attacker.example', 'X-Test-Run-Token': token })).status === 400, 'Bad host did not reject.');
        record('ServerRefusesUnexpectedHostHeader');
    } finally { await stop(child); }
    try {
        child = await server(57528, token);
        const wrong = await request(57528, { 'X-Test-Run-Token': token });
        assert(wrong.status === 400 && wrong.body.includes('fixed port 57527'), 'Valid-token wrong-port request did not reject before app boot.');
        record('ServerRefusesWrongPortWithValidToken');
    } finally { await stop(child); }

    const id = crypto.randomBytes(16).toString('hex');
    const parent = path.join(root, 'writable/tmp');
    const directory = path.join(parent, 'pnav_negative_' + id);
    fs.mkdirSync(directory, { mode: 0o700 }); // Exclusive: collision is an error, never reuse an existing directory.
    const owned = [];
    try {
        assert(fs.realpathSync(directory) === path.resolve(directory) && !fs.lstatSync(directory).isSymbolicLink(), 'Scratch path redirected.');
        exclusive(path.join(directory, '.owner'), id); owned.push('.owner');
        const sentinel = path.join(directory, 'sentinel.json');
        const original = Buffer.from('pre-existing owned artifact ' + id);
        exclusive(sentinel, original); owned.push('sentinel.json');
        let collision = false;
        try { exclusive(sentinel, 'must not overwrite'); } catch (error) { collision = error.code === 'EEXIST'; }
        assert(collision && fs.readFileSync(sentinel).equals(original), 'Exclusive collision changed existing bytes.');
        record('ExistingNegativeFilePreservedOnCollision');
        const receipt = path.join(directory, 'receipt.json');
        exclusive(receipt, JSON.stringify({ run_id: 'forged-run' })); owned.push('receipt.json');
        let refused = false;
        try { execFileSync(php, [fixture, 'teardown', id, receipt], { cwd: root, encoding: 'utf8', timeout: 5000, stdio: 'pipe' }); }
        catch (error) { refused = error.status === 1 && String(error.stdout).includes('Receipt run_id mismatch'); }
        assert(refused, 'Forged receipt did not fail for its ownership mismatch.');
        record('FixtureRefusesMismatchedReceipt');
    } finally {
        assert(fs.realpathSync(directory) === path.join(parent, 'pnav_negative_' + id)
            && !fs.lstatSync(directory).isSymbolicLink() && fs.readFileSync(path.join(directory, '.owner'), 'utf8') === id, 'Scratch ownership differs; preserving evidence.');
        assert(fs.readdirSync(directory).every(name => owned.includes(name)), 'Unknown scratch files; preserving evidence.');
        for (const name of owned.reverse()) {
            const file = path.join(directory, name);
            assert(!fs.lstatSync(file).isSymbolicLink(), 'Scratch file redirected.');
            fs.unlinkSync(file);
        }
        fs.rmdirSync(directory); // Only the checked, empty, exclusively created directory.
    }

    const blocker = net.createServer();
    await new Promise((resolve, reject) => { blocker.once('error', reject); blocker.listen(57527, '127.0.0.1', resolve); });
    try {
        let refused = false;
        try { execFileSync(process.execPath, [path.join(__dirname, 'run-patient-navigation-http.cjs')], { cwd: root, timeout: 10000, stdio: 'pipe' }); }
        catch (error) { refused = error.status === 1 && String(error.stderr).includes('Port 57527 is already in use'); }
        assert(refused, 'Actual runner did not refuse occupied port.');
        record('RunnerRefusesOccupiedPort');
    } finally { await new Promise(resolve => blocker.close(resolve)); }
    console.log(JSON.stringify({ success: true, total: results.length, results }));
}
main().catch(error => { console.error(error.stack || error.message); process.exitCode = 1; });
