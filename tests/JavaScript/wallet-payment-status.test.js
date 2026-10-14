const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const viewPath = path.join(
    __dirname,
    '../../resources/views/themes/betting/user/currency-wallet.blade.php'
);

function loadPollingScript() {
    const source = fs.readFileSync(viewPath, 'utf8');
    const match = source.match(
        /@if\(request\(\)->query\('reference'\)\)\s*<script>([\s\S]*?)<\/script>\s*@endif/
    );

    assert.ok(match, 'wallet reference polling script should exist');
    const script = match[1].replace(
        "@json(route('user.wallet.status', ['reference' => request()->query('reference')]))",
        JSON.stringify('/user/wallet/status/reference')
    );

    assert.ok(!script.includes('@json'), 'Blade route should be replaced for the test');
    return script;
}

async function runStatusScript(status, message) {
    const timers = new Map();
    let nextTimerId = 1;
    let requestCount = 0;
    let reloadCount = 0;
    const statusBox = {
        className: '',
        textContent: '',
        style: {}
    };
    const location = {
        href: 'https://example.test/user/wallet?reference=wallet-123&tab=history',
        reload() {
            reloadCount += 1;
        }
    };
    const window = {
        location,
        history: {
            replaceState(_state, _title, url) {
                location.href = String(url);
            }
        },
        setTimeout(callback, delay) {
            const id = nextTimerId++;
            timers.set(id, { callback, delay });
            return id;
        },
        clearTimeout(id) {
            timers.delete(id);
        }
    };

    vm.runInNewContext(loadPollingScript(), {
        URL,
        document: {
            title: 'Wallet',
            getElementById() {
                return statusBox;
            }
        },
        fetch() {
            requestCount += 1;
            return Promise.resolve({
                ok: true,
                json: async () => ({ status, message })
            });
        },
        window
    });

    await new Promise(resolve => setImmediate(resolve));
    return {
        timers,
        statusBox,
        location,
        get requestCount() {
            return requestCount;
        },
        get reloadCount() {
            return reloadCount;
        }
    };
}

test('failed payment stops polling and keeps the failure message visible', async () => {
    const result = await runStatusScript('failed', 'Transaction failed.');

    assert.equal(result.statusBox.textContent, 'Transaction failed.');
    assert.equal(result.statusBox.className, 'alert alert-danger');
    assert.equal(result.requestCount, 1);
    assert.equal(result.timers.size, 0);
    assert.equal(result.reloadCount, 0);
});

test('successful payment stops polling and removes the reference before one balance refresh', async () => {
    const result = await runStatusScript('succeeded', 'Deposit successful.');

    assert.equal(result.statusBox.textContent, 'Deposit successful.');
    assert.equal(result.statusBox.className, 'alert alert-success');
    assert.equal(result.requestCount, 1);
    assert.deepEqual(
        Array.from(result.timers.values(), timer => timer.delay),
        [1500]
    );

    const [reloadTimerId, reloadTimer] = result.timers.entries().next().value;
    result.timers.delete(reloadTimerId);
    reloadTimer.callback();

    assert.equal(new URL(result.location.href).searchParams.has('reference'), false);
    assert.equal(new URL(result.location.href).searchParams.get('tab'), 'history');
    assert.equal(result.reloadCount, 1);
});

test('pending payment keeps a single five-second status poll scheduled', async () => {
    const result = await runStatusScript('pending', 'Transaction is still pending.');

    assert.equal(result.statusBox.textContent, 'Transaction is still pending.');
    assert.deepEqual(
        Array.from(result.timers.values(), timer => timer.delay),
        [5000]
    );
    assert.equal(result.reloadCount, 0);
});
