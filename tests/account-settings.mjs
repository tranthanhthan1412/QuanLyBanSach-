import assert from 'node:assert/strict';

// Uses the smoke runner's isolated database, HTTP server and session clients.
export async function accountSettings({ base, Client, sql }) {
    const client = new Client(base);
    for (const route of ['account', 'account-password']) {
        assert.equal((await client.request(`route=${route}`)).status, 302);
        assert.equal((await client.request(`route=${route}`, 'POST')).status, 303);
    }
    const email = 'settings@example.test';
    const originalPassword = 'SettingsOld123!';
    let page = await client.request('route=register');
    const registration = { name: 'Settings User', email, password: originalPassword, confirm: originalPassword, csrf_token: client.token(page.html) };
    assert.equal((await client.request('route=register', 'POST', registration)).status, 303);
    assert.equal((await client.request('route=login', 'POST', registration)).status, 303);
    const other = new Client(base);
    page = await other.request('route=register');
    const otherRegistration = { ...registration, email: 'settings-other@example.test', csrf_token: other.token(page.html) };
    assert.equal((await other.request('route=register', 'POST', otherRegistration)).status, 303);
    assert.equal((await other.request('route=login', 'POST', otherRegistration)).status, 303);
    const otherBefore = sql("echo json_encode($db->query(\"SELECT * FROM nguoidung WHERE email = 'settings-other@example.test'\")->fetch());");
    const otherId = JSON.parse(otherBefore).maND;
    const storedProfile = () => JSON.parse(sql("echo json_encode($db->query(\"SELECT tenND, SDT, diaChi, email, maVT, biKhoa FROM nguoidung WHERE email = 'settings@example.test'\")->fetch());"));
    const before = storedProfile();
    page = await client.request('route=account');
    const csrf = client.token(page.html);
    const profile = { csrf_token: csrf, name: 'Tên <script>alert(1)</script>', phone: '+84 901-234-567', address: '123 Đường Sách, Phường Sài Gòn' };
    assert.equal((await client.request('route=account', 'POST', { ...profile, csrf_token: '' })).status, 403);
    assert.deepEqual(storedProfile(), before);
    for (const invalid of [{ name: ' ' }, { name: 'a'.repeat(81) }, { phone: '123' }, { address: 'a'.repeat(256) }]) {
        page = await client.request('route=account', 'POST', { ...profile, ...invalid });
        assert.equal(page.status, 422);
        assert.deepEqual(storedProfile(), before);
    }
    page = await client.request('route=account', 'POST', {
        ...profile, id: String(otherId), maND: String(otherId), email: 'forged@example.test',
        role: 'Admin', maVT: '1', biKhoa: '1', matKhau: 'forged-password',
    });
    assert.equal(page.status, 303);
    assert.equal(page.headers.get('location'), '/index.php?route=account');
    assert.deepEqual(storedProfile(), { ...before, tenND: profile.name, SDT: '+84901234567', diaChi: profile.address });
    assert.equal(sql("echo json_encode($db->query(\"SELECT * FROM nguoidung WHERE email = 'settings-other@example.test'\")->fetch());"), otherBefore);
    page = await client.request('route=account');
    assert.match(page.html, /Đã cập nhật thông tin cá nhân/);
    assert.match(page.html, /Tên &lt;script&gt;alert\(1\)&lt;\/script&gt;/);
    assert.doesNotMatch(page.html, /Tên <script>/);
    assert.doesNotMatch((await client.request('route=account')).html, /Đã cập nhật thông tin cá nhân/);
    // Optional contact information can be cleared.
    assert.equal((await client.request('route=account', 'POST', { ...profile, name: 'Settings User', phone: '', address: '' })).status, 303);
    assert.equal(storedProfile().SDT, null);
    assert.equal(storedProfile().diaChi, null);

    const secondSession = new Client(base);
    page = await secondSession.request('route=login');
    assert.equal((await secondSession.request('route=login', 'POST', { ...registration, csrf_token: secondSession.token(page.html) })).status, 303);
    const secondToken = secondSession.token((await secondSession.request('route=account')).html);
    page = await client.request('route=account-password');
    const passwordForm = { csrf_token: client.token(page.html), current_password: originalPassword, password: 'SettingsNew456!', confirm: 'SettingsNew456!' };
    const oldHash = sql("echo $db->query(\"SELECT matKhau FROM nguoidung WHERE email = 'settings@example.test'\")->fetchColumn();");
    assert.equal((await client.request('route=account-password', 'POST', { ...passwordForm, csrf_token: '' })).status, 403);
    for (const invalid of [
        { current_password: 'incorrect' }, { current_password: '' }, { confirm: 'mismatch' },
        { password: 'short', confirm: 'short' },
        { password: 'á'.repeat(37), confirm: 'á'.repeat(37) },
        { password: 'a'.repeat(73), confirm: 'a'.repeat(73) },
        { password: 'abcd\0efgh', confirm: 'abcd\0efgh' },
        { password: originalPassword, confirm: originalPassword },
    ]) {
        page = await client.request('route=account-password', 'POST', { ...passwordForm, ...invalid });
        assert.equal(page.status, 422);
        assert.doesNotMatch(page.html, /value="(?:SettingsOld123!|SettingsNew456!|incorrect)"/);
        assert.equal(sql("echo $db->query(\"SELECT matKhau FROM nguoidung WHERE email = 'settings@example.test'\")->fetchColumn();"), oldHash);
    }
    const oldCookie = client.cookie;
    page = await client.request('route=account-password', 'POST', { ...passwordForm, maND: String(otherId) });
    assert.equal(page.status, 303);
    assert.equal(page.headers.get('location'), '/index.php?route=login');
    assert.notEqual(client.cookie, oldCookie);
    assert.equal((await client.request('route=account')).status, 302);
    // A previously authenticated browser cannot write with its old session/CSRF token.
    assert.equal((await secondSession.request('route=account', 'POST', { ...profile, csrf_token: secondToken })).status, 303);
    assert.equal((await secondSession.request('route=account')).status, 302);
    assert.equal((await other.request('route=account')).status, 200);
    assert.equal(sql("echo json_encode($db->query(\"SELECT * FROM nguoidung WHERE email = 'settings-other@example.test'\")->fetch());"), otherBefore);
    page = await client.request('route=login');
    assert.match(page.html, /Đổi mật khẩu thành công/);
    const loginCsrf = client.token(page.html);
    page = await client.request('route=login', 'POST', { ...registration, csrf_token: loginCsrf });
    assert.match(page.html, /Email hoặc mật khẩu không chính xác/);
    page = await client.request('route=login', 'POST', { ...registration, password: passwordForm.password, csrf_token: loginCsrf });
    assert.equal(page.status, 303);
    assert.equal((await client.request('route=account')).status, 200);
    assert.equal(sql("echo password_verify('SettingsNew456!', $db->query(\"SELECT matKhau FROM nguoidung WHERE email = 'settings@example.test'\")->fetchColumn()) ? 'hashed' : 'invalid';"), 'hashed');
    // Admins use the same self-service forms without losing their role.
    sql("$db->exec(\"UPDATE nguoidung SET maVT = (SELECT maVT FROM vaitro WHERE tenVT = 'Admin') WHERE email = 'settings@example.test'\");");
    page = await client.request('route=account');
    assert.match(page.html, /Quản trị viên/);
    assert.equal((await client.request('route=account', 'POST', { ...profile, name: 'Settings Admin', csrf_token: client.token(page.html) })).status, 303);
    assert.equal((await client.request('route=account-password')).status, 200);
    assert.equal((await client.request('route=admin')).status, 200);
    console.log('PASS: Account settings, validation, ownership, CSRF, password change and session revocation.');
}
