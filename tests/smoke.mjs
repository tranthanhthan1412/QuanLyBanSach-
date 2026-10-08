// Integration checks use a uniquely named temporary database, never the app database.
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { once } from 'node:events';
import net from 'node:net';
import { fileURLToPath } from 'node:url';
import { mkdtempSync, readdirSync, unlinkSync, rmdirSync } from 'node:fs';
import { join } from 'node:path';

const cwd = fileURLToPath(new URL('../', import.meta.url));
const php = process.env.PHP_BIN || (process.platform === 'win32' ? 'C:\\xampp\\php\\php.exe' : 'php');
const database = `bookstore_test_${process.pid}_${Date.now()}`;
const env = { ...process.env, DB_NAME: database };
// The parent tests/ directory is protected by .htaccess; remove this directory after each run.
const sessions = mkdtempSync(join(cwd, 'tests', '.tmp-'));
let server;
let serverLog = '';
let checks = 0;
function run(args, input) {
    const result = spawnSync(php, args, { cwd, env, input, encoding: 'utf8', windowsHide: true });
    assert.equal(result.status, 0, result.stderr || result.stdout || String(result.error));
    assert.doesNotMatch(result.stdout + result.stderr, /Warning:|Fatal error:|Deprecated:/);
    return result.stdout;
}
function sql(body) {
    return run([], `<?php require 'model/database.php'; $db = (new Database())->getConnection(); ${body}`);
}
class Client {
    cookie = '';
    constructor(base) { this.base = base; }
    async request(route, method = 'GET', values = {}) {
        const response = await fetch(`${this.base}/index.php?${route}`, {
            method, redirect: 'manual',
            headers: { Cookie: this.cookie, ...(method === 'POST' ? { 'Content-Type': 'application/x-www-form-urlencoded' } : {}) },
            body: method === 'POST' ? new URLSearchParams(values) : undefined,
        });
        const cookie = response.headers.getSetCookie().find(value => value.startsWith('PHPSESSID='));
        if (cookie) this.cookie = cookie.split(';')[0];
        const html = await response.text();
        assert.doesNotMatch(html, /Warning:|Fatal error:|Deprecated:|Uncaught /);
        checks++;
        return { status: response.status, html, headers: response.headers };
    }
    token(html) {
        const token = html.match(/name="csrf_token" value="([a-f0-9]{64})"/)?.[1];
        assert.ok(token, 'Expected CSRF token');
        return token;
    }
}

try {
    run(['database/setup.php']);
    const counts = JSON.parse(sql(`echo json_encode([
        'books' => $db->query('SELECT COUNT(*) FROM sach')->fetchColumn(),
        'categories' => $db->query('SELECT COUNT(*) FROM theloai')->fetchColumn(),
        'users' => $db->query('SELECT COUNT(*) FROM nguoidung')->fetchColumn()
    ]);`));
    assert.deepEqual(counts, { books: 24, categories: 8, users: 0 });
    // A custom role ID must work; setup must preserve stock and existing role IDs.
    sql("$db->exec(\"UPDATE vaitro SET maVT = 17 WHERE tenVT = 'User'\"); $db->exec('UPDATE sach SET tonKho = 37 WHERE maSach = 1');");
    run(['database/setup.php']);
    assert.equal(sql("echo $db->query('SELECT tonKho FROM sach WHERE maSach = 1')->fetchColumn();"), '37');
    assert.equal(sql("echo $db->query('SELECT COUNT(*) FROM sach')->fetchColumn();"), '24');

    const listener = net.createServer();
    listener.listen(0, '127.0.0.1');
    await once(listener, 'listening');
    const port = listener.address().port;
    await new Promise(resolve => listener.close(resolve));
    server = spawn(php, ['-d', 'display_errors=1', '-d', 'html_errors=0', '-d', 'error_reporting=32767', '-d', `session.save_path=${sessions}`, '-S', `127.0.0.1:${port}`, '-t', 'public'], { cwd, env, windowsHide: true });
    server.stdout.on('data', chunk => { serverLog += chunk; });
    server.stderr.on('data', chunk => { serverLog += chunk; });
    const base = `http://127.0.0.1:${port}`;
    for (let attempt = 0; attempt < 50; attempt++) {
        try { await fetch(`${base}/assets/images/favicon.svg`); break; }
        catch { await new Promise(resolve => setTimeout(resolve, 100)); }
    }
    const guest = new Client(base);
    for (const route of ['home', 'products', 'product&id=1', 'products&q=chien+binh', 'products&category=1', 'products&sort=price-desc&page=2', 'cart', 'checkout', 'order-success', 'login', 'register']) {
        assert.equal((await guest.request(`route=${route}`)).status, 200, route);
    }
    const catalog = await guest.request('route=products&category=1');
    assert.match(catalog.html, /value="1" selected/);
    const search = await guest.request('route=products&q=chien+binh');
    assert.match(search.html, /Chiến binh cầu vồng/);
    const home = await guest.request('route=home');
    assert.match(home.html, /Những câu chuyện chạm đến tâm hồn/);
    const data = JSON.parse(home.html.match(/<script id="app-data" type="application\/json">(.*?)<\/script>/s)[1]);
    assert.equal(typeof data.products[0].id, 'number');
    assert.equal(typeof data.products[0].price, 'number');
    assert.equal(typeof data.products[0].stock, 'number');
    assert.equal((await fetch(base + data.products[0].image)).status, 200);
    sql("$db->exec(\"INSERT INTO theloai (tenTL) VALUES ('Custom category')\"); $db->exec('UPDATE sach SET hinhAnh = NULL WHERE maSach = 1');");
    assert.equal((await guest.request('route=home')).status, 200);
    assert.match((await guest.request('route=product&id=1')).html, /book-placeholder.svg/);
    assert.equal((await guest.request('route=missing')).status, 404);
    assert.equal((await guest.request('route=product&id=999999')).status, 404);
    assert.equal((await guest.request('route=account')).status, 302);
    const adminGuest = await guest.request('route=admin');
    assert.equal(adminGuest.status, 302);
    assert.equal(adminGuest.headers.get('location'), '/index.php?route=login');
    assert.equal((await guest.request('route=logout')).status, 405);
    assert.equal((await guest.request('route=home', 'POST')).status, 405);
    assert.equal((await guest.request('route=checkout', 'POST')).status, 405);
    assert.equal((await guest.request('route=register', 'POST', { email: 'missing@example.test' })).status, 403);

    let page = await guest.request('route=register');
    const token = guest.token(page.html);
    const registration = { csrf_token: token, name: 'Người kiểm thử', email: 'smoke@example.test', password: 'TestPass123!', confirm: 'TestPass123!', role: 'Admin', role_id: '1', maVT: '1' };
    page = await guest.request('route=register', 'POST', { ...registration, confirm: 'mismatch' });
    assert.match(page.html, /Mật khẩu nhập lại không khớp/);
    page = await guest.request('route=register', 'POST', registration);
    assert.equal(page.status, 303);
    assert.equal(sql("echo $db->query(\"SELECT maVT FROM nguoidung WHERE email = 'smoke@example.test'\")->fetchColumn();"), '17');
    assert.equal(sql("echo password_verify('TestPass123!', $db->query(\"SELECT matKhau FROM nguoidung WHERE email = 'smoke@example.test'\")->fetchColumn()) ? 'hashed' : 'invalid';"), 'hashed');
    page = await guest.request('route=register', 'POST', registration);
    assert.match(page.html, /Email này đã được đăng ký/);
    page = await guest.request('route=login', 'POST', { csrf_token: token, email: registration.email, password: 'wrong-password' });
    assert.match(page.html, /Email hoặc mật khẩu không chính xác/);
    const beforeLogin = guest.cookie;
    page = await guest.request('route=login', 'POST', registration);
    assert.equal(page.status, 303);
    assert.notEqual(guest.cookie, beforeLogin);
    assert.equal(page.headers.get('location'), '/index.php?route=account');
    page = await guest.request('route=account');
    assert.match(page.html, /Người kiểm thử/);
    assert.match(page.html, /Khách hàng/);
    assert.doesNotMatch(page.html, /href="[^"]*route=admin"/);
    const forbidden = await guest.request('route=admin');
    assert.equal(forbidden.status, 403);
    assert.match(forbidden.html, /Bạn không có quyền truy cập/);
    assert.doesNotMatch(forbidden.html, /Tổng quan cửa hàng/);
    const loggedInToken = guest.token(page.html);
    assert.notEqual(loggedInToken, token);
    assert.match((await guest.request('route=orders')).html, /Bạn chưa có đơn hàng nào/);

    const orders = JSON.parse(sql(`
        $user = (int) $db->query("SELECT maND FROM nguoidung WHERE email = 'smoke@example.test'")->fetchColumn();
        $stmt = $db->prepare('INSERT INTO donhang (tenDH, tongSL, maND, ghiChu) VALUES (?, 2, ?, ?)');
        $stmt->execute(['Test order', $user, '<script>alert(1)</script>']);
        $own = (int) $db->lastInsertId();
        $db->prepare('INSERT INTO chitietdonhang (maDH, maSach, soLuong, tongTien) VALUES (?, 1, 2, 178000)')->execute([$own]);
        $db->exec("INSERT INTO nguoidung (tenND, email, matKhau, maVT) VALUES ('Other', 'other@example.test', 'unused', 17)");
        $other = (int) $db->lastInsertId();
        $stmt->execute(['Private order', $other, 'Private']);
        echo json_encode(['own' => $own, 'other' => (int) $db->lastInsertId()]);
    `));
    page = await guest.request(`route=order&id=${orders.own}`);
    assert.equal(page.status, 200);
    assert.match(page.html, /178\.000/);
    assert.match(page.html, /&lt;script&gt;alert\(1\)&lt;\/script&gt;/);
    assert.match(page.html, /Chờ xác nhận/);
    assert.equal((await guest.request(`route=order&id=${orders.other}`)).status, 404);
    assert.equal((await guest.request('route=order&id=999999')).status, 404);
    assert.equal((await guest.request('route=orders')).status, 200);
    assert.equal((await guest.request('route=logout', 'POST', { csrf_token: token })).status, 403);
    assert.equal((await guest.request('route=logout', 'POST', { csrf_token: loggedInToken })).status, 303);
    assert.equal((await guest.request('route=account')).status, 302);
    // Existing customer sessions immediately gain/lose access when the DB role changes.
    sql("$db->exec(\"UPDATE vaitro SET maVT = 29 WHERE tenVT = 'Admin'\");");
    run(['database/set-role.php', registration.email, 'Admin']);
    page = await guest.request('route=login');
    page = await guest.request('route=login', 'POST', { ...registration, csrf_token: guest.token(page.html) });
    assert.equal(page.status, 303);
    assert.equal(page.headers.get('location'), '/index.php?route=admin');
    page = await guest.request('route=admin');
    assert.equal(page.status, 200);
    assert.match(page.html, /Tổng quan cửa hàng/);
    assert.match(page.html, /Quản trị viên/);
    assert.match(page.html, /href="[^"]*route=admin"/);
    const adminCss = await fetch(`${base}/assets/css/admin.css`);
    assert.equal(adminCss.status, 200);
    assert.match(await adminCss.text(), /\.admin-sidebar/);
    assert.equal((await guest.request(`route=order&id=${orders.other}`)).status, 404);
    // The read-only dashboard must not accept POST requests.
    assert.equal((await guest.request('route=admin', 'POST', { csrf_token: guest.token(page.html) })).status, 405);

    // Product history must prevent destructive deletion while leaving the book intact.
    const adminProducts = await guest.request('route=admin-products');
    const bookVersion = sql("echo $db->query('SELECT phienBan FROM sach WHERE maSach = 1')->fetchColumn();");
    page = await guest.request('route=admin-product-delete', 'POST', {
        csrf_token: guest.token(adminProducts.html), id: '1', version: bookVersion,
    });
    assert.equal(page.status, 303);
    assert.equal(sql("echo $db->query('SELECT COUNT(*) FROM sach WHERE maSach = 1')->fetchColumn();"), '1');
    page = await guest.request('route=admin-products');
    assert.match(page.html, /Sách đã có trong đơn hàng, không thể xóa/);

    // Confirmation is atomic when a later line item has insufficient stock.
    const adminOrders = await guest.request('route=admin-orders');
    const edgeOrders = JSON.parse(sql(`
        $admin = (int) $db->query("SELECT maND FROM nguoidung WHERE email = 'smoke@example.test'")->fetchColumn();
        $db->exec('UPDATE sach SET tonKho = 37 WHERE maSach = 1');
        $db->exec('UPDATE sach SET tonKho = 0 WHERE maSach = 2');
        $stmt = $db->prepare('INSERT INTO donhang (tenDH, tongSL, maND) VALUES (?, 3, ?)');
        $stmt->execute(['Insufficient stock test', $admin]);
        $order = (int) $db->lastInsertId();
        $lines = $db->prepare('INSERT INTO chitietdonhang (maDH, maSach, soLuong, tongTien) VALUES (?, ?, ?, ?)');
        $lines->execute([$order, 1, 2, 178000]);
        $lines->execute([$order, 2, 1, 25000]);
        echo json_encode(['order' => $order]);
    `));
    page = await guest.request(`route=admin-order&id=${edgeOrders.order}`);
    const failedConfirmation = await guest.request(`route=admin-order&id=${edgeOrders.order}`, 'POST', {
        csrf_token: guest.token(page.html), expected: 'ChoXacNhan', status: 'DaXacNhan', note: '',
    });
    assert.equal(failedConfirmation.status, 303);
    const failedState = JSON.parse(sql(`
        $order = ${edgeOrders.order};
        echo json_encode([
            'status' => $db->query("SELECT trangThai FROM donhang WHERE maDH = $order")->fetchColumn(),
            'deducted' => $db->query("SELECT daTruKho FROM donhang WHERE maDH = $order")->fetchColumn(),
            'first' => $db->query('SELECT tonKho FROM sach WHERE maSach = 1')->fetchColumn(),
            'second' => $db->query('SELECT tonKho FROM sach WHERE maSach = 2')->fetchColumn()
        ]);
    `));
    assert.deepEqual(failedState, { status: 'ChoXacNhan', deducted: 0, first: 37, second: 0 });
    page = await guest.request(`route=admin-order&id=${edgeOrders.order}`);
    assert.match(page.html, /không đủ tồn kho hoặc đã ẩn/);

    // Repeated cancellation requests must never restore inventory twice.
    sql("$db->exec('UPDATE sach SET tonKho = 1 WHERE maSach = 2');");
    page = await guest.request(`route=admin-order&id=${edgeOrders.order}`);
    let mutation = await guest.request(`route=admin-order&id=${edgeOrders.order}`, 'POST', {
        csrf_token: guest.token(page.html), expected: 'ChoXacNhan', status: 'DaXacNhan', note: '',
    });
    assert.equal(mutation.status, 303);
    assert.deepEqual(JSON.parse(sql(`
        echo json_encode([
            'first' => $db->query('SELECT tonKho FROM sach WHERE maSach = 1')->fetchColumn(),
            'second' => $db->query('SELECT tonKho FROM sach WHERE maSach = 2')->fetchColumn(),
            'deducted' => $db->query('SELECT daTruKho FROM donhang WHERE maDH = ${edgeOrders.order}')->fetchColumn()
        ]);
    `)), { first: 35, second: 0, deducted: 1 });
    page = await guest.request(`route=admin-order&id=${edgeOrders.order}`);
    const cancellation = {
        csrf_token: guest.token(page.html), expected: 'DaXacNhan', status: 'DaHuy', note: 'Khách yêu cầu hủy',
    };
    mutation = await guest.request(`route=admin-order&id=${edgeOrders.order}`, 'POST', cancellation);
    assert.equal(mutation.status, 303);
    const restoredState = JSON.parse(sql(`
        echo json_encode([
            'status' => $db->query('SELECT trangThai FROM donhang WHERE maDH = ${edgeOrders.order}')->fetchColumn(),
            'deducted' => $db->query('SELECT daTruKho FROM donhang WHERE maDH = ${edgeOrders.order}')->fetchColumn(),
            'first' => $db->query('SELECT tonKho FROM sach WHERE maSach = 1')->fetchColumn(),
            'second' => $db->query('SELECT tonKho FROM sach WHERE maSach = 2')->fetchColumn()
        ]);
    `));
    assert.deepEqual(restoredState, { status: 'DaHuy', deducted: 0, first: 37, second: 1 });
    mutation = await guest.request(`route=admin-order&id=${edgeOrders.order}`, 'POST', cancellation);
    assert.equal(mutation.status, 303);
    assert.deepEqual(JSON.parse(sql(`
        echo json_encode([
            'status' => $db->query('SELECT trangThai FROM donhang WHERE maDH = ${edgeOrders.order}')->fetchColumn(),
            'deducted' => $db->query('SELECT daTruKho FROM donhang WHERE maDH = ${edgeOrders.order}')->fetchColumn(),
            'first' => $db->query('SELECT tonKho FROM sach WHERE maSach = 1')->fetchColumn(),
            'second' => $db->query('SELECT tonKho FROM sach WHERE maSach = 2')->fetchColumn()
        ]);
    `)), { status: 'DaHuy', deducted: 0, first: 37, second: 1 });
    page = await guest.request(`route=admin-order&id=${edgeOrders.order}`);
    assert.match(page.html, /Trạng thái đã thay đổi/);

    // A locked customer loses access even when their existing browser session remains open.
    const lockable = new Client(base);
    page = await lockable.request('route=register');
    const lockableRegistration = {
        ...registration, name: 'Tài khoản sẽ khóa', email: 'lockable@example.test',
        csrf_token: lockable.token(page.html),
    };
    assert.equal((await lockable.request('route=register', 'POST', lockableRegistration)).status, 303);
    page = await lockable.request('route=login');
    assert.equal((await lockable.request('route=login', 'POST', {
        ...lockableRegistration, csrf_token: lockable.token(page.html),
    })).status, 303);
    assert.equal((await lockable.request('route=account')).status, 200);
    const adminUsers = await guest.request('route=admin-users');
    const lockableId = sql("echo $db->query(\"SELECT maND FROM nguoidung WHERE email = 'lockable@example.test'\")->fetchColumn();");
    mutation = await guest.request('route=admin-users', 'POST', {
        csrf_token: guest.token(adminUsers.html), id: lockableId, role: 'User', blocked: '1',
    });
    assert.equal(mutation.status, 303);
    assert.equal(sql(`echo $db->query('SELECT biKhoa FROM nguoidung WHERE maND = ${lockableId}')->fetchColumn();`), '1');
    assert.equal((await lockable.request('route=account')).status, 302);
    assert.equal((await guest.request('route=admin')).status, 200);

    run(['database/set-role.php', registration.email, 'User']);
    assert.equal((await guest.request('route=admin')).status, 403);
    assert.doesNotMatch((await guest.request('route=account')).html, /href="[^"]*route=admin"/);
    run(['database/set-role.php', registration.email, 'Admin']);
    assert.equal((await guest.request('route=admin')).status, 200);
    // Unknown roles invalidate the existing session and cannot log in.
    sql("$db->exec(\"INSERT INTO vaitro (tenVT) VALUES ('Unsupported')\"); $db->exec(\"UPDATE nguoidung SET maVT = (SELECT maVT FROM vaitro WHERE tenVT = 'Unsupported') WHERE email = 'smoke@example.test'\");");
    assert.equal((await guest.request('route=admin')).status, 302);
    page = await guest.request('route=login');
    page = await guest.request('route=login', 'POST', { ...registration, csrf_token: guest.token(page.html) });
    assert.match(page.html, /Email hoặc mật khẩu không chính xác/);
    assert.equal((await guest.request('route=account')).status, 302);
    // Deleted accounts cannot keep using an old authenticated session.
    const removed = new Client(base);
    page = await removed.request('route=register');
    const removedRegistration = { ...registration, email: 'removed@example.test', csrf_token: removed.token(page.html) };
    assert.equal((await removed.request('route=register', 'POST', removedRegistration)).status, 303);
    assert.equal((await removed.request('route=login', 'POST', removedRegistration)).status, 303);
    sql("$db->exec(\"DELETE FROM nguoidung WHERE email = 'removed@example.test'\");");
    assert.equal((await removed.request('route=account')).status, 302);
    assert.doesNotMatch(serverLog, /PHP (Warning|Fatal error|Deprecated)/);
    const unavailable = run(['-d', `session.save_path=${sessions}`], `<?php
        putenv('DB_NAME=${database}_missing');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        register_shutdown_function(function () { echo 'HTTP_STATUS=' . http_response_code(); });
        require 'index.php';
    `);
    assert.match(unavailable, /HTTP_STATUS=503/);
    assert.doesNotMatch(unavailable, /SQLSTATE|PDOException|Stack trace/);
    console.log(`PASS: ${checks} HTTP checks; setup rerun, data preservation, catalog, auth, CSRF, roles, role revocation, order ownership.`);
} finally {
    if (server && server.exitCode === null) {
        const closed = once(server, 'exit');
        server.kill();
        await closed;
    }
    // Only remove the uniquely named database created by this test invocation.
    assert.match(database, /^bookstore_test_\d+_\d+$/);
    run([], `<?php require 'model/database.php'; $db = Database::connect(false); $db->exec('DROP DATABASE IF EXISTS \`${database}\`');`);
    for (const filename of readdirSync(sessions)) unlinkSync(join(sessions, filename));
    rmdirSync(sessions);
}
