// Only product IDs and quantities are persisted. Replace this adapter with an
// authenticated backend cart when the project enters its backend phase.
export const CART_KEY = 'bookstore_cart_v1';
export const RECEIPT_KEY = 'bookstore_demo_order_v1';

export function normalizeItems(value, products) {
    if (!Array.isArray(value)) return [];
    const known = new Map(products.map(product => [product.id, product]));
    const merged = new Map();
    for (const item of value.slice(0, 200)) {
        if (!item || !Number.isInteger(item.id) || !known.has(item.id)) continue;
        if (!Number.isInteger(item.qty) || item.qty < 1) continue;
        const maximum = known.get(item.id).stock;
        merged.set(item.id, Math.min(maximum, (merged.get(item.id) || 0) + item.qty));
    }
    return [...merged].map(([id, qty]) => ({ id, qty }));
}

export function calculateTotals(items, products, shippingFee, freeShippingFrom) {
    const known = new Map(products.map(product => [product.id, product]));
    const normalized = normalizeItems(items, products);
    const subtotal = normalized.reduce((sum, item) => sum + known.get(item.id).price * item.qty, 0);
    const count = normalized.reduce((sum, item) => sum + item.qty, 0);
    const shipping = subtotal === 0 || subtotal >= freeShippingFrom ? 0 : shippingFee;
    return { subtotal, shipping, total: subtotal + shipping, count };
}

export function createCart(products, storage, onWarning = () => {}) {
    const known = new Map(products.map(product => [product.id, product]));
    let items = [];
    const listeners = new Set();
    function load() {
        try {
            const raw = storage.getItem(CART_KEY);
            items = normalizeItems(raw ? JSON.parse(raw) : [], products);
        } catch {
            items = [];
            onWarning('Không đọc được giỏ đã lưu. Giỏ hàng được khởi tạo lại.');
        }
    }
    function persist() {
        try { storage.setItem(CART_KEY, JSON.stringify(items)); }
        catch { onWarning('Trình duyệt không cho lưu giỏ hàng. Thay đổi chỉ được giữ trên trang hiện tại.'); }
        for (const listener of listeners) listener();
    }
    load();
    return {
        getItems: () => items.map(item => ({ ...item })),
        subscribe(listener) { listeners.add(listener); return () => listeners.delete(listener); },
        add(id, quantity = 1) {
            if (!known.has(id)) return { ok: false, message: 'Sản phẩm không tồn tại trong dữ liệu mẫu.' };
            const qty = Math.min(known.get(id).stock, Math.max(1, Math.trunc(Number(quantity)) || 1));
            const line = items.find(item => item.id === id);
            const current = line?.qty || 0;
            if (current + qty > known.get(id).stock) {
                return { ok: false, message: `Tối đa ${known.get(id).stock} cuốn cho mỗi đầu sách trong demo.` };
            }
            if (line) line.qty += qty; else items.push({ id, qty });
            persist();
            return { ok: true, message: `Đã thêm ${qty} cuốn “${known.get(id).title}” vào giỏ.` };
        },
        update(id, quantity) {
            const line = items.find(item => item.id === id);
            if (!line) return;
            line.qty = Math.min(known.get(id).stock, Math.max(1, Math.trunc(Number(quantity)) || 1));
            persist();
        },
        remove(id) { items = items.filter(item => item.id !== id); persist(); },
        clear() { items = []; persist(); },
        reload() { load(); for (const listener of listeners) listener(); },
    };
}
