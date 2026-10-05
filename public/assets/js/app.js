import { createCart, calculateTotals, normalizeItems, CART_KEY, RECEIPT_KEY } from './cart-store.js';
import { initDemoForms, showFormResult } from './demo.js';

const config = JSON.parse(document.getElementById('app-data').textContent);
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

// Lazy wrappers also catch browsers that throw when the storage property is read.
const storage = { getItem: key => window.localStorage.getItem(key), setItem: (key, value) => window.localStorage.setItem(key, value) };
const cart = createCart(config.products, storage, message => setTimeout(() => notify(message), 0));

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
        const quantity = input ? Math.min(20, Math.max(1, Math.trunc(Number(input.value)) || 1)) : 1;
        if (input) input.value = quantity;
        const result = cart.add(Number(add.dataset.addCart), quantity);
        notify(result.message);
    }
    const step = event.target.closest('[data-quantity-step]');
    if (step) {
        const input = document.getElementById('product-quantity');
        input.value = Math.min(20, Math.max(1, (Number(input.value) || 1) + Number(step.dataset.quantityStep)));
        updateQuantityButtons();
    }
});
function updateQuantityButtons() {
    const input = document.getElementById('product-quantity');
    if (!input) return;
    input.value = Math.min(20, Math.max(1, Math.trunc(Number(input.value)) || 1));
    document.querySelector('[data-quantity-step="-1"]').disabled = Number(input.value) <= 1;
    document.querySelector('[data-quantity-step="1"]').disabled = Number(input.value) >= 20;
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
        ${checkout ? '<button type="submit" form="checkout-form" class="button full-width" id="place-order">Đặt hàng mô phỏng →</button>' : `<a class="button full-width" href="${escape(config.urls.checkout)}">Tiến hành đặt hàng →</a>`}
        <p class="summary-footnote">${checkout ? 'Không phát sinh đơn hàng hoặc thanh toán thật.' : 'Giỏ hàng demo · Thanh toán khi nhận hàng (COD)'}</p>`;
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
    checkout(form) {
        if (submitting) return;
        const items = cart.getItems();
        if (!items.length) { renderCheckout(); notify('Giỏ hàng đang trống. Vui lòng chọn sách trước.'); return; }
        // Store no form values. The receipt always uses an explicitly fictional recipient.
        const receipt = { version: 1, items, createdAt: new Date().toISOString() };
        try { sessionStorage.setItem(RECEIPT_KEY, JSON.stringify(receipt)); }
        catch { showFormResult(form, 'Trình duyệt không cho lưu đơn mô phỏng trong phiên này. Giỏ hàng vẫn được giữ nguyên.', true); return; }
        submitting = true;
        document.getElementById('place-order').disabled = true;
        form.reset();
        cart.clear();
        window.location.assign(config.urls.success);
    },
});

function renderSuccess() {
    const target = document.getElementById('success-details');
    if (!target) return;
    let receipt = null;
    try { receipt = JSON.parse(sessionStorage.getItem(RECEIPT_KEY) || 'null'); } catch { /* Invalid session data is not a completed order. */ }
    const items = normalizeItems(receipt?.items, config.products);
    const date = new Date(receipt?.createdAt ?? '');
    if (receipt?.version !== 1 || !items.length || Number.isNaN(date.getTime())) {
        document.querySelector('#success-root h1').textContent = 'Chưa có đơn hàng mô phỏng';
        document.querySelector('#success-root > p').textContent = 'Hãy chọn sách và hoàn tất form đặt hàng thử để xem xác nhận tại đây.';
        document.querySelector('.success-icon').innerHTML = cartIcon;
        return;
    }
    const totals = totalsFor(items);
    target.innerHTML = `<div class="success-facts"><div class="summary-row"><span>Mã tham chiếu minh họa</span><strong>DEMO-PREVIEW</strong></div><div class="summary-row"><span>Thời gian thử</span><span>${escape(date.toLocaleString('vi-VN', { timeZone: 'Asia/Ho_Chi_Minh' }))}</span></div><div class="summary-row"><span>Người nhận</span><span>Khách hàng mẫu</span></div><div class="summary-row"><span>Thanh toán</span><span>COD · Mô phỏng</span></div><div class="summary-row"><span>Số lượng</span><span>${totals.count} cuốn</span></div><div class="summary-row"><span>Tổng cộng</span><strong>${money(totals.total)}</strong></div><p class="small muted">Thông tin bạn nhập đã được xóa khỏi form, không được lưu. Danh sách đơn trong tài khoản là dữ liệu mẫu độc lập.</p></div>`;
}

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
window.addEventListener('storage', event => { if (event.key === CART_KEY || event.key === null) cart.reload(); });
// A page restored from browser history must reread the cart, including after checkout.
window.addEventListener('pageshow', event => { if (event.persisted) cart.reload(); });
render();
renderSuccess();
