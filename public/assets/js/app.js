const config = JSON.parse(document.getElementById('app-data').textContent);
// PHP provides content-versioned URLs for the full module graph, not just app.js.
const [{ createCart, calculateTotals, normalizeItems, CART_KEY }, { initDemoForms, showFormResult }] = await Promise.all([
    import(config.modules.cart),
    import(config.modules.forms),
]);
const products = new Map(config.products.map(product => [product.id, product]));
const currency = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });
const money = value => currency.format(value);
const escape = value => String(value).replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character]));
const totalsFor = items => calculateTotals(items, config.products, config.shippingFee, config.freeShippingFrom);
const checkIcon = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>';
const cartIcon = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 3h3l3 13h11l3-10H6"/><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/></svg>';
let toastTimer;
function notify(message) {
    const region = document.getElementById('toast-region');
    clearTimeout(toastTimer);
    region.innerHTML = `<div class="toast">${checkIcon}<span>${escape(message)}</span></div>`;
    toastTimer = setTimeout(() => region.replaceChildren(), 5000);
}

// Khách chưa đăng nhập tiếp tục dùng localStorage; giỏ tài khoản chỉ sống trên server.
const storage = {
    getItem: key => window.localStorage.getItem(key),
    setItem: (key, value) => window.localStorage.setItem(key, value),
    removeItem: key => window.localStorage.removeItem(key),
};
const warnCart = message => setTimeout(() => notify(message), 0);
const syncEndpoint = new URL('index.php?route=cart-sync', window.location.href).href;
const transferKey = 'bookstore_cart_guest_transfer_v1';

async function requestCart(values = null) {
    const response = await fetch(syncEndpoint, {
        method: values ? 'POST' : 'GET',
        credentials: 'same-origin',
        cache: 'no-store',
        keepalive: !!values,
        headers: { Accept: 'application/json', ...(values ? { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' } : {}) },
        ...(values ? { body: new URLSearchParams(values) } : {}),
    });
    if (!response.headers.get('content-type')?.includes('application/json')) {
        throw new Error('Phiên đăng nhập hoặc kết nối đã thay đổi. Vui lòng tải lại trang.');
    }
    const result = await response.json();
    if (!response.ok || result.ok === false) {
        throw new Error(result.message || 'Không thể đồng bộ giỏ. Vui lòng thử lại.');
    }
    return result;
}

function createRemoteCart(saved, csrf, userId) {
    // Không bao giờ ghi giỏ của tài khoản vào khóa localStorage dùng chung.
    const memoryStorage = { getItem: () => JSON.stringify(saved), setItem: () => {} };
    const inner = createCart(config.products, memoryStorage, warnCart);
    let confirmed = saved;
    let pending = 0;
    let failure = null;
    let tail = Promise.resolve();

    function enqueue(operation) {
        if (pending === 0) failure = null;
        pending++;
        tail = tail.then(async () => {
            const result = await requestCart({ ...operation, csrf_token: csrf });
            confirmed = result.items;
        }).catch(error => {
            failure = error;
            warnCart('Chưa lưu được giỏ trên tài khoản: ' + error.message);
        }).finally(() => {
            pending--;
            if (pending === 0) inner.replace(confirmed); // Nếu lỗi: trở lại trạng thái server.
        });
    }

    return {
        getItems: inner.getItems,
        subscribe: inner.subscribe,
        add(id, qty = 1) {
            const result = inner.add(id, qty);
            if (result.ok) {
                enqueue({ action: 'set', id: String(id), qty: String(inner.getItems().find(item => item.id === id).qty) });
            }
            return result;
        },
        update(id, qty) {
            inner.update(id, qty);
            const line = inner.getItems().find(item => item.id === id);
            if (line) enqueue({ action: 'set', id: String(id), qty: String(line.qty) });
        },
        remove(id) { inner.remove(id); enqueue({ action: 'set', id: String(id), qty: '0' }); },
        clear() { inner.clear(); enqueue({ action: 'clear' }); },
        // Đơn hàng đã xoá giỏ MySQL trên server; tránh gửi yêu cầu xoá trễ lúc điều hướng.
        clearLocal() { confirmed = []; inner.replace([]); },
        async waitForSync() {
            await tail;
            if (failure) throw new Error('Giỏ hàng chưa đồng bộ xong. Vui lòng tải lại trang trước khi đặt hàng.');
        },
        async refresh() {
            await tail;
            if (pending) return;
            const result = await requestCart();
            if (!result.authenticated || result.userId !== userId) {
                window.location.reload(); // Ngăn hiển thị giỏ sai tài khoản sau đổi phiên.
                return;
            }
            if (pending) return; // Không đè lên thay đổi vừa bắt đầu khi GET đang chạy.
            failure = null;
            confirmed = result.items;
            inner.replace(result.items);
        },
    };
}

async function initializeCart() {
    let session;
    try {
        session = await requestCart();
    } catch (error) {
        warnCart('Không kiểm tra được giỏ trên máy chủ: ' + error.message);
        return { cart: createCart(config.products, storage, warnCart), remote: false };
    }
    if (!session.authenticated) {
        return { cart: createCart(config.products, storage, warnCart), remote: false };
    }

    let saved = session.items;
    let guest = [];
    try {
        guest = normalizeItems(JSON.parse(storage.getItem(CART_KEY) || '[]'), config.products);
    } catch {
        warnCart('Giỏ khách trên trình duyệt không đọc được.');
    }
    if (guest.length) {
        try {
            // Giữ mã chuyển giỏ khi request mất phản hồi; server sẽ chỉ cộng đúng một lần.
            const payload = JSON.stringify(guest);
            let transfer;
            try { transfer = JSON.parse(storage.getItem(transferKey) || 'null'); } catch { /* dữ liệu cũ */ }
            if (!transfer || transfer.payload !== payload || !/^[a-f0-9]{32}$/.test(transfer.id)) {
                const random = crypto.getRandomValues(new Uint8Array(16));
                transfer = { id: Array.from(random, n => n.toString(16).padStart(2, '0')).join(''), payload };
                storage.setItem(transferKey, JSON.stringify(transfer));
            }
            saved = (await requestCart({
                action: 'merge', transfer_id: transfer.id, items: payload, csrf_token: session.csrf,
            })).items;
            storage.removeItem(CART_KEY);
            storage.removeItem(transferKey);
        } catch (error) {
            warnCart('Chưa gộp được giỏ khách. Giỏ cũ vẫn được giữ trên trình duyệt để thử lại: ' + error.message);
        }
    }
    return { cart: createRemoteCart(saved, session.csrf, session.userId), remote: true };
}

const { cart, remote: hasRemoteCart } = await initializeCart();

const menu = document.getElementById('category-nav');
const menuToggle = document.getElementById('menu-toggle');
function closeMenu() {
    menu.classList.remove('is-open');
    menuToggle.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>';
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-label', 'Mở danh mục');
}
menuToggle.addEventListener('click', () => {
    const open = menuToggle.getAttribute('aria-expanded') !== 'true';
    menu.classList.toggle('is-open', open);
    menuToggle.innerHTML = open
        ? '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg>'
        : '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>';
    menuToggle.setAttribute('aria-expanded', String(open));
    menuToggle.setAttribute('aria-label', open ? 'Đóng danh mục' : 'Mở danh mục');
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && menu.classList.contains('is-open')) { closeMenu(); menuToggle.focus(); }
});
document.addEventListener('click', event => {
    if (menu.classList.contains('is-open') && !event.target.closest('.site-header')) closeMenu();
});
matchMedia('(min-width: 1024px)').addEventListener('change', closeMenu);
for (const select of document.querySelectorAll('[data-auto-submit]')) select.addEventListener('change', () => select.form.requestSubmit());

document.addEventListener('click', event => {
    const add = event.target.closest('[data-add-cart]');
    if (add) {
        const input = add.dataset.quantityInput ? document.getElementById(add.dataset.quantityInput) : null;
        const quantity = input ? Math.min(Number(input.max), Math.max(1, Math.trunc(Number(input.value)) || 1)) : 1;
        if (input) input.value = quantity;
        const result = cart.add(Number(add.dataset.addCart), quantity);
        notify(result.message);
    }
    const step = event.target.closest('[data-quantity-step]');
    if (step) {
        const input = document.getElementById('product-quantity');
        input.value = Math.min(Number(input.max), Math.max(1, (Number(input.value) || 1) + Number(step.dataset.quantityStep)));
        updateQuantityButtons();
    }
});
function updateQuantityButtons() {
    const input = document.getElementById('product-quantity');
    if (!input) return;
    input.value = Math.min(Number(input.max), Math.max(1, Math.trunc(Number(input.value)) || 1));
    document.querySelector('[data-quantity-step="-1"]').disabled = Number(input.value) <= 1;
    document.querySelector('[data-quantity-step="1"]').disabled = Number(input.value) >= Number(input.max);
}
document.getElementById('product-quantity')?.addEventListener('change', updateQuantityButtons);
updateQuantityButtons();

function emptyCart() {
    return `<div class="empty-state panel">${cartIcon}<h2>Giỏ hàng đang chờ một cuốn sách hay</h2><p>Bạn chưa thêm sách nào. Khám phá những câu chuyện và chọn cuốn sách yêu thích nhé.</p><a class="button" href="${escape(config.urls.products)}">Khám phá sách</a></div>`;
}
function summary(totals, checkout = false) {
    const remaining = config.freeShippingFrom - totals.subtotal;
    return `<h2>${checkout ? 'Tóm tắt đơn hàng' : 'Tóm tắt giỏ hàng'}</h2>
        ${checkout ? cart.getItems().map(item => {
            const product = products.get(item.id);
            return `<div class="checkout-line"><img src="${escape(product.image)}" alt="" width="45" height="60"><div>${escape(product.title)}<small>Số lượng: ${item.qty}</small></div><strong>${money(product.price * item.qty)}</strong></div>`;
        }).join('') : ''}
        <div class="summary-row"><span>Tạm tính (${totals.count} cuốn)</span><strong data-subtotal>${money(totals.subtotal)}</strong></div>
        <div class="summary-row"><span>Phí vận chuyển</span><span data-shipping>${totals.shipping ? money(totals.shipping) : 'Miễn phí'}</span></div>
        <p class="shipping-hint">${remaining > 0 ? `Thêm ${money(remaining)} để được miễn phí giao hàng.` : 'Đơn hàng đã được miễn phí giao hàng.'}</p>
        <div class="summary-row summary-total"><span>Tổng cộng</span><strong data-total>${money(totals.total)}</strong></div>
        ${checkout ? `<button type="submit" form="checkout-form" class="button full-width" id="place-order" ${submitting ? 'disabled' : ''}>${submitting ? 'Đang xử lý…' : 'Đặt hàng →'}</button>` : `<a class="button full-width" href="${escape(config.urls.checkout)}">Tiến hành đặt hàng →</a>`}
        <p class="summary-footnote">Thanh toán khi nhận hàng (COD). Đơn hàng chờ cửa hàng xác nhận.</p>`;
}
function renderCart() {
    const target = document.getElementById('cart-root');
    if (!target) return;
    const items = cart.getItems();
    if (!items.length) { target.innerHTML = emptyCart(); return; }
    const totals = totalsFor(items);
    target.innerHTML = `<div class="cart-grid"><section class="panel cart-items" aria-label="Sách trong giỏ"><div class="cart-table-head"><span>Sản phẩm</span><span>${totals.count} cuốn sách</span></div>${items.map(item => {
        const product = products.get(item.id);
        return `<article class="cart-item" data-cart-item="${item.id}">
            <a href="${escape(product.href)}" tabindex="-1" aria-hidden="true"><img src="${escape(product.image)}" alt="" width="80" height="105"></a>
            <div class="cart-item-info"><h2><a href="${escape(product.href)}">${escape(product.title)}</a></h2><p>${escape(product.author)}</p><strong>${money(product.price)}</strong>
                <div class="quantity-control"><button data-cart-step="-1" data-id="${item.id}" ${item.qty <= 1 ? 'disabled' : ''} aria-label="Giảm số lượng ${escape(product.title)}">−</button><input type="number" min="1" max="${product.stock}" value="${item.qty}" data-cart-quantity="${item.id}" aria-label="Số lượng ${escape(product.title)}" inputmode="numeric"><button data-cart-step="1" data-id="${item.id}" ${item.qty >= product.stock ? 'disabled' : ''} aria-label="Tăng số lượng ${escape(product.title)}">+</button></div>
            </div><div class="cart-item-end"><strong>${money(product.price * item.qty)}</strong><button class="icon-button remove-button" data-remove="${item.id}" aria-label="Xóa ${escape(product.title)}"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/></svg></button></div></article>`;
    }).join('')}</section><aside class="order-summary panel">${summary(totals)}</aside></div>`;
}
document.getElementById('cart-root')?.addEventListener('click', event => {
    const remove = event.target.closest('[data-remove]');
    if (remove) {
        cart.remove(Number(remove.dataset.remove));
        notify('Đã xóa sách khỏi giỏ hàng.');
        document.querySelector('[data-remove], #cart-root .button')?.focus();
        return;
    }
    const button = event.target.closest('[data-cart-step]');
    if (!button) return;
    const id = Number(button.dataset.id);
    const step = button.dataset.cartStep;
    const item = cart.getItems().find(item => item.id === id);
    if (item) {
        cart.update(id, item.qty + Number(step));
        const replacement = document.querySelector(`[data-id="${id}"][data-cart-step="${step}"]`);
        if (replacement && !replacement.disabled) replacement.focus();
        else document.querySelector(`[data-cart-quantity="${id}"]`)?.focus();
    }
});
document.getElementById('cart-root')?.addEventListener('change', event => {
    if (!event.target.matches('[data-cart-quantity]')) return;
    const id = Number(event.target.dataset.cartQuantity);
    cart.update(id, event.target.value);
    document.querySelector(`[data-cart-quantity="${id}"]`)?.focus();
});

function renderCheckout() {
    const content = document.getElementById('checkout-content');
    if (!content) return;
    const items = cart.getItems();
    const empty = document.getElementById('checkout-empty');
    content.hidden = !items.length;
    empty.hidden = !!items.length;
    if (!items.length) empty.innerHTML = emptyCart();
    else document.getElementById('checkout-summary').innerHTML = summary(totalsFor(items), true);
}

let submitting = false;
initDemoForms({
    notify,
    async checkout(form) {
        if (submitting) return;

        if (!cart.getItems().length) { renderCheckout(); notify('Giỏ hàng đang trống. Vui lòng chọn sách trước.'); return; }
        submitting = true;
        renderCheckout();
        form.setAttribute('aria-busy', 'true');
        try {
            // Chờ lưu giỏ MySQL xong trước khi gửi yêu cầu tạo đơn.
            if (cart.waitForSync) await cart.waitForSync();
            const items = cart.getItems();
            if (!items.length) throw new Error('Giỏ hàng đã thay đổi. Vui lòng kiểm tra lại trước khi đặt.');
            const data = new FormData(form);
            data.set('items', JSON.stringify(items));
            const response = await fetch(form.action, {
                method: 'POST', body: data, credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!response.headers.get('content-type')?.includes('application/json')) {
                throw new Error(response.redirected || response.status === 403
                    ? 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập và tải lại trang đặt hàng.'
                    : 'Chưa nhận được xác nhận. Bạn có thể bấm đặt hàng lại; cùng yêu cầu sẽ không tạo trùng đơn.');
            }
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'Không thể đặt hàng. Vui lòng thử lại.');
            if (cart.clearLocal) cart.clearLocal(); else cart.clear();
            window.location.assign(result.url);
        } catch (error) {
            showFormResult(form, error instanceof TypeError
                ? 'Mất kết nối. Hãy thử lại để kiểm tra đơn; cùng yêu cầu sẽ không tạo trùng đơn.' : error.message, true);
        } finally {
            submitting = false;
            form.removeAttribute('aria-busy');
            renderCheckout();
        }
    },
});

function render() {
    const count = totalsFor(cart.getItems()).count;
    for (const badge of document.querySelectorAll('[data-cart-badge]')) {
        badge.textContent = count;
        badge.hidden = count === 0;
        badge.closest('a').setAttribute('aria-label', `Giỏ hàng, ${count} cuốn sách`);
    }
    renderCart();
    renderCheckout();
}
cart.subscribe(render);
if (!hasRemoteCart) {
    window.addEventListener('storage', event => { if (event.key === CART_KEY || event.key === null) cart.reload(); });
}
// Đổi tab/thiết bị: luôn đọc dữ liệu mới nhất của tài khoản khi quay lại trang.
function resumeCart() {
    if (hasRemoteCart) cart.refresh().catch(error => warnCart('Không tải được giỏ mới nhất: ' + error.message));
    else cart.reload();
}
window.addEventListener('pageshow', event => { if (event.persisted) resumeCart(); });
window.addEventListener('focus', () => { if (hasRemoteCart) resumeCart(); });
render();
