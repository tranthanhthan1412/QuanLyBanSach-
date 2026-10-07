import test from 'node:test';
import assert from 'node:assert/strict';
import { normalizeItems, calculateTotals, createCart } from '../public/assets/js/cart-store.js';

const products = [{ id: 1, stock: 3, price: 89000 }, { id: 2, stock: 0, price: 50000 }, { id: 3, stock: 50, price: 10000 }];

test('stored carts remove unknown and sold-out books, merge duplicates, and clamp stock', () => {
    assert.deepEqual(normalizeItems([
        { id: 1, qty: 2 }, { id: 1, qty: 5 }, { id: 2, qty: 1 },
        { id: 999, qty: 1 }, { id: 3, qty: -1 }, { id: '3', qty: 1 },
    ], products), [{ id: 1, qty: 3 }]);
});

test('totals use stock limits and the free shipping threshold', () => {
    assert.deepEqual(calculateTotals([{ id: 1, qty: 2 }], products, 25000, 300000),
        { subtotal: 178000, shipping: 25000, total: 203000, count: 2 });
    assert.equal(calculateTotals([{ id: 3, qty: 30 }], products, 25000, 300000).shipping, 0);
    assert.equal(calculateTotals([], products, 25000, 300000).total, 0);
});

test('cart supports quantities above 20 and refuses sold-out books', () => {
    const storage = new Map();
    const cart = createCart(products, { getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value) });
    assert.equal(cart.add(2).ok, false);
    assert.equal(cart.add(3, 25).ok, true);
    cart.update(3, 80);
    assert.deepEqual(cart.getItems(), [{ id: 3, qty: 50 }]);
    cart.clear();
    cart.reload();
    assert.deepEqual(cart.getItems(), []);
});
