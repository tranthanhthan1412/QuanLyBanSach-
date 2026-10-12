// Real browser regression test, using CDP and the installed Chrome/Edge (no npm dependencies).
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { once } from 'node:events';
import { existsSync, mkdtempSync, rmSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

export async function browserCheckout(base, cookie) {
    const executable = process.env.BROWSER_BIN || [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    ].find(existsSync);
    assert.ok(executable, 'Set BROWSER_BIN to a Chromium browser to run the browser regression test.');
    const root = dirname(fileURLToPath(import.meta.url));
    const profile = mkdtempSync(join(root, '.tmp-browser-'));
    const browser = spawn(executable, ['--headless=new', '--disable-gpu', '--no-first-run',
        '--no-default-browser-check', '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank'],
    { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
    let socket;
    try {
        const endpoint = await new Promise((resolve, reject) => {
            const timer = setTimeout(() => reject(new Error('Browser startup timed out')), 15000);
            let output = '';
            browser.once('error', error => { clearTimeout(timer); reject(error); });
            browser.once('exit', code => { clearTimeout(timer); reject(new Error(`Browser exited: ${code}`)); });
            browser.stderr.on('data', chunk => {
                output += chunk;
                const match = output.match(/DevTools listening on (ws:\/\/\S+)/);
                if (match) { clearTimeout(timer); resolve(match[1]); }
            });
        });
        const target = (await (await fetch(`http://${new URL(endpoint).host}/json/list`)).json()).find(item => item.type === 'page');
        socket = new WebSocket(target.webSocketDebuggerUrl);
        await Promise.race([once(socket, 'open'), new Promise((_, reject) => {
            const timer = setTimeout(() => reject(new Error('Browser socket timed out')), 10000);
            socket.addEventListener('open', () => clearTimeout(timer), { once: true });
        })]);
        let id = 0;
        const pending = new Map();
        const errors = [];
        const moduleRequests = [];
        function call(method, params = {}) {
            return new Promise((resolve, reject) => {
                const requestId = ++id;
                const timer = setTimeout(() => { pending.delete(requestId); reject(new Error(`CDP timeout: ${method}`)); }, 10000);
                pending.set(requestId, {
                    resolve(value) { clearTimeout(timer); resolve(value); },
                    reject(error) { clearTimeout(timer); reject(error); },
                });
                socket.send(JSON.stringify({ id: requestId, method, params }));
            });
        }
        socket.addEventListener('message', event => {
            const message = JSON.parse(event.data);
            if (message.id) {
                const waiter = pending.get(message.id);
                pending.delete(message.id);
                if (message.error) waiter?.reject(new Error(message.error.message));
                else waiter?.resolve(message.result);
            }
            if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails.text);
            if (message.method === 'Network.requestWillBeSent' && /\/js\/.*\.js/.test(message.params.request.url)) {
                moduleRequests.push(message.params.request.url);
            }
        });
        async function evaluate(expression) {
            const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
            assert.equal(result.exceptionDetails, undefined, JSON.stringify(result.exceptionDetails));
            return result.result.value;
        }
        async function waitFor(expression) {
            const deadline = Date.now() + 15000;
            while (Date.now() < deadline) {
                if (await evaluate(expression)) return;
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            throw new Error(`Browser wait timed out: ${expression}; exceptions: ${errors.join(', ')}`);
        }
        await call('Runtime.enable');
        await call('Network.enable');
        await call('Page.enable');
        await call('Network.setCookie', { name: 'PHPSESSID', value: cookie.split('=')[1], url: base });
        await call('Page.navigate', { url: `${base}/index.php?route=products` });
        await waitFor('document.querySelector("[data-add-cart]") !== null');
        await evaluate('localStorage.setItem("bookstore_cart_v1", JSON.stringify([{id:1,qty:1}]))');
        await call('Page.navigate', { url: `${base}/index.php?route=checkout` });
        await waitFor('document.querySelector("#place-order") !== null');
        await evaluate(`Object.entries({recipient:'Khách kiểm thử trình duyệt',phone:'0901234567',province:'TP Hồ Chí Minh',ward:'Phường Sài Gòn',address:'123 Đường Sách'}).forEach(([id,value]) => document.getElementById(id).value = value)`);
        // Simulate the stale-script/native form POST from the reported screenshot.
        await evaluate('document.getElementById("checkout-form").submit()');
        await waitFor('document.querySelector("[role=alert]")?.textContent.includes("Chưa gửi được giỏ hàng") && document.querySelector("#place-order") !== null');
        assert.equal(await evaluate('document.getElementById("recipient").value'), 'Khách kiểm thử trình duyệt');
        await evaluate('document.getElementById("place-order").click()');
        await waitFor('location.search.includes("route=order-success") && document.querySelector("#success-root h1")?.textContent.includes("Đặt hàng thành công")');
        assert.equal(await evaluate('localStorage.getItem("bookstore_cart_v1")'), '[]');
        assert.equal(await evaluate('document.querySelector("#success-root").textContent.includes("đã được gửi đến")'), true);
        for (const module of ['app.js', 'demo.js', 'cart-store.js']) {
            assert.ok(moduleRequests.some(url => url.includes(`/${module}?v=`)), `Expected versioned ${module}`);
        }
        assert.deepEqual(errors, []);
        console.log('PASS: Browser native-form recovery, preserved inputs, JS checkout, cart clearing, versioned modules and email confirmation.');
        await call('Browser.close');
    } finally {
        socket?.close();
        if (browser.exitCode === null && browser.signalCode === null) {
            const stopped = once(browser, 'exit');
            browser.kill();
            await stopped;
        }
        // Delete only this invocation's generated browser profile under tests/.
        assert.equal(dirname(resolve(profile)), resolve(root));
        assert.ok(profile.startsWith(join(root, '.tmp-browser-')));
        rmSync(profile, { recursive: true, force: true, maxRetries: 10, retryDelay: 100 });
    }
}
