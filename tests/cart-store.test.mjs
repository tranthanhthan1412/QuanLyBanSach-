import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createCart, normalizeItems, calculateTotals } from '../public/assets/js/cart-store.js';

const products = [
    { id: 1, title: 'Sách A', price: 100000, stock: 5 },
    { id: 2, title: 'Sách B', price: 50000, stock: 2 },
];
function memoryStorage() {
    const values = new Map();
    return { getItem: key => values.get(key) || null, setItem: (key, value) => values.set(key, value) };
}

test('Lọc hàng lạ và kẹp tồn kho', () => {
    assert.deepEqual(normalizeItems([{ id: 1, qty: 3 }, { id: 1, qty: 4 }, { id: 999, qty: 4 }], products), [{ id: 1, qty: 5 }]);
});

test('Thay dữ liệu từ server không cộng dồn sau nhiều lần hydrate', () => {
    const cart = createCart(products, memoryStorage());
    cart.replace([{ id: 1, qty: 2 }]);
    cart.replace([{ id: 1, qty: 2 }]);
    assert.deepEqual(cart.getItems(), [{ id: 1, qty: 2 }]);
    cart.add(2, 1);
    assert.deepEqual(cart.getItems(), [{ id: 1, qty: 2 }, { id: 2, qty: 1 }]);
});

test('Tổng tiền và phí giao không thay đổi', () => {
    const totals = calculateTotals([{ id: 1, qty: 1 }], products, 25000, 300000);
    assert.deepEqual(totals, { subtotal: 100000, shipping: 25000, total: 125000, count: 1 });
});
