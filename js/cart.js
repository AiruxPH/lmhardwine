const Cart = {
    items: [],
    mode: 'guest', // 'guest' or 'user'
    storageKey: 'lm_cart_guest',

    async init() {
        // 1. Admin Perms Check
        if (typeof IS_ADMIN !== 'undefined' && IS_ADMIN === true) {
            console.log('Admin mode: Cart disabled');
            return;
        }

        // 2. Determine Mode
        if (typeof CART_USER_KEY !== 'undefined' && CART_USER_KEY !== 'guest') {
            this.mode = 'user';
        }

        // 3. Logic Branching
        if (this.mode === 'user') {
            await this.handleUserInit();
        } else {
            this.handleGuestInit();
        }

        // 4. Event Listeners
        const toggleBtn = document.getElementById('cart-toggle');
        if (toggleBtn) toggleBtn.addEventListener('click', (e) => { e.preventDefault(); this.togglePanel(); });

        const closeBtn = document.getElementById('close-cart');
        if (closeBtn) closeBtn.addEventListener('click', (e) => { e.preventDefault(); this.togglePanel(); });
    },

    handleGuestInit() {
        const stored = localStorage.getItem(this.storageKey);
        if (stored) this.items = JSON.parse(stored);
        this.updateUI();
    },

    async handleUserInit() {
        // Migration Check: Do we have a guest cart locally?
        const guestCart = localStorage.getItem('lm_cart_guest');
        if (guestCart) {
            try {
                const guestItems = JSON.parse(guestCart);
                if (Array.isArray(guestItems) && guestItems.length > 0) {
                    // Send to API to merge
                    console.log('Migrating guest cart to DB...');
                    await fetch('api/cart.php?action=migrate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ items: guestItems })
                    });
                    // Clear local guest cart
                    localStorage.removeItem('lm_cart_guest');
                }
            } catch (e) {
                console.error('Migration failed', e);
            }
        }

        // Fetch User Data from DB
        await this.syncFromDB();
    },

    async syncFromDB() {
        try {
            const res = await fetch('api/cart.php');
            if (res.ok) {
                this.items = await res.json();
                this.updateUI();
            }
        } catch (e) {
            console.error('Failed to sync cart', e);
        }
    },

    async add(id, name, price, type, qty = 1) {
        if (typeof IS_ADMIN !== 'undefined' && IS_ADMIN === true) return;

        qty = parseInt(qty);
        if (qty < 1) qty = 1;

        if (this.mode === 'user') {
            // API Call
            try {
                const res = await fetch('api/cart.php?action=add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, qty })
                });

                if (res.ok) {
                    await this.syncFromDB();
                    this.openPanel();
                } else {
                    const data = await res.json();
                    alert(data.error || 'Failed to add to cart. Stock might be limited.');
                }
            } catch (e) {
                console.error('Cart add failed', e);
            }
        } else {
            // LocalStorage Logic
            const existing = this.items.find(item => item.id === id);
            if (existing) {
                existing.qty = parseInt(existing.qty) + qty;
            } else {
                this.items.push({ id, name, price, type, qty: qty });
            }
            this.saveLocal();
            this.updateUI();
            this.openPanel();
        }
    },

    async remove(id) {
        if (this.mode === 'user') {
            // API Call
            const res = await fetch('api/cart.php?action=remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            if (res.ok) await this.syncFromDB();
        } else {
            this.items = this.items.filter(item => item.id !== id);
            this.saveLocal();
            this.updateUI();
        }
    },

    saveLocal() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.items));
    },

    togglePanel() {
        document.getElementById('cart-panel').classList.toggle('open');
        document.getElementById('cart-overlay').classList.toggle('open');
    },

    openPanel() {
        document.getElementById('cart-panel').classList.add('open');
        document.getElementById('cart-overlay').classList.add('open');
    },

    updateUI() {
        // Update Badge
        const totalQty = this.items.reduce((sum, item) => sum + parseInt(item.qty), 0);
        const countEl = document.getElementById('cart-count');
        if (countEl) countEl.innerText = totalQty;

        // Render Items
        const container = document.getElementById('cart-items');
        if (!container) return;

        container.innerHTML = '';

        if (this.items.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #666; margin-top: 2rem;">Your cellar is empty.</p>';
        }

        let totalPrice = 0;

        this.items.forEach(item => {
            totalPrice += item.price * item.qty;

            const el = document.createElement('div');
            el.className = 'cart-item';
            el.innerHTML = `
                <div style="flex: 1;">
                    <h4 style="margin-bottom: 0.25rem;">${item.name}</h4>
                    <span style="font-size: 0.8rem; color: #888;">${item.type}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span>₱${item.price} x ${item.qty}</span>
                    <button onclick="Cart.remove(${item.id})" style="background: none; border: none; color: #720e1e; cursor: pointer;">&times;</button>
                </div>
            `;
            container.appendChild(el);
        });

        // Update Total
        const totalEl = document.getElementById('cart-total');
        if (totalEl) totalEl.innerText = '₱' + totalPrice.toFixed(2);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Cart.init();
});
