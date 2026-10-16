import test from 'node:test';
import assert from 'node:assert/strict';
import { Cart } from '../assets/cart.js';
import { menuData } from '../assets/menu-data.js';

test('each drink keeps its own name, quantity, and price', () => {
  const cart = new Cart(menuData);
  const milkTea = menuData.find(item => item.name === '奶茶');
  const oolong = menuData.find(item => item.name === '烏龍茶');
  const pearlTea = menuData.find(item => item.name === '珍珠奶茶');
  cart.add(milkTea.id);
  cart.add(oolong.id);
  cart.add(oolong.id);
  cart.add(pearlTea.id);
  assert.equal(cart.total(), 45 + 35 * 2 + 60);
  assert.deepEqual(cart.entries().map(({item, quantity}) => [item.name, quantity]), [
    ['奶茶', 1], ['烏龍茶', 2], ['珍珠奶茶', 1]
  ]);
});

test('decreasing to zero removes an item; clear resets total', () => {
  const cart = new Cart(menuData);
  cart.add(1);
  cart.changeQty(1, -1);
  assert.deepEqual(cart.entries(), []);
  assert.equal(cart.total(), 0);
  cart.add(2);
  cart.clear();
  assert.equal(cart.total(), 0);
});

test('invalid IDs, duplicate IDs, and fractional quantities are rejected', () => {
  const cart = new Cart(menuData);
  assert.throws(() => cart.add(9999));
  assert.throws(() => new Cart([menuData[0], menuData[0]]));
  cart.add(1);
  assert.throws(() => cart.changeQty(1, 0.5));
});
