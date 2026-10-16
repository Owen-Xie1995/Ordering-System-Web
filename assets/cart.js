export class Cart {
  constructor(menu) {
    this.menu = new Map();
    this.quantities = new Map();
    for (const item of menu) {
      if (!Number.isInteger(item.id) || this.menu.has(item.id)) throw new Error('商品 ID 必須唯一');
      if (!Number.isInteger(item.price) || item.price < 0) throw new Error('商品價格無效');
      this.menu.set(item.id, item);
    }
  }

  add(id) {
    if (!this.menu.has(id)) throw new Error('找不到商品');
    this.quantities.set(id, (this.quantities.get(id) || 0) + 1);
  }

  changeQty(id, delta) {
    if (!Number.isInteger(delta)) throw new Error('數量需為整數');
    if (!this.quantities.has(id)) return;
    const next = this.quantities.get(id) + delta;
    if (next <= 0) this.quantities.delete(id);
    else this.quantities.set(id, next);
  }

  clear() {
    this.quantities.clear();
  }

  entries() {
    return [...this.quantities].map(([id, quantity]) => ({ item: this.menu.get(id), quantity }));
  }

  total() {
    return this.entries().reduce((sum, { item, quantity }) => sum + item.price * quantity, 0);
  }
}
