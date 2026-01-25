const Cart = {
    items: [],
    storageKey: 'lm_cart_guest', // Default

    init() {
        // 1. Determine Storage Key
        if (typeof CART_USER_KEY !== 'undefined') {
            if (CART_USER_KEY === 'guest') {
                this.storageKey = 'lm_cart_guest';
            } else {
                this.storageKey = 'lm_cart_' + CART_USER_KEY;
                // MIGRATION CHECK: If we are a user, check if there's a guest cart to migrate
                const guestCart = localStorage.getItem('lm_cart_guest');
                if (guestCart) {
                    try {
                        const guestItems = JSON.parse(guestCart);
                        if (Array.isArray(guestItems) && guestItems.length > 0) {
                            // Merge logic
                            const currentCart = localStorage.getItem(this.storageKey);
                            let userItems = currentCart ? JSON.parse(currentCart) : [];
                            
                            guestItems.forEach(gItem => {
                                const existing = userItems.find(uItem => uItem.id === gItem.id);
                                if (existing) {
                                    existing.qty = parseInt(existing.qty) + parseInt(gItem.qty);
                                } else {
                                    userItems.push(gItem);
                                }
                            });
                            
                            // Save to user cart
                            localStorage.setItem(this.storageKey, JSON.stringify(userItems));
                            // Clear guest cart
                            localStorage.removeItem('lm_cart_guest');
                            console.log('Cart migrated from guest to user.');
                        }
                    } catch (e) {
                        console.error('Error migrating cart:', e);
                    }
                }
            }
        }

        // 2. Admin Check
        if (typeof IS_ADMIN !== 'undefined' && IS_ADMIN === true) {
            console.log('Admin mode: Cart disabled');
            return; // Stop initialization
        }

        // 3. Load Items
        const stored = localStorage.getItem(this.storageKey);
        if (stored) {
            this.items = JSON.parse(stored);
        }
        this.updateUI();

        // Event Listeners (Use optional chaining or checks in case elements are hidden)
        const toggleBtn = document.getElementById('cart-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.togglePanel();
            });
        }

        const closeBtn = document.getElementById('close-cart');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.togglePanel();
            });
        }
    },

    add(id, name, price, type, qty = 1) {
        if (typeof IS_ADMIN !== 'undefined' && IS_ADMIN === true) return;

        qty = parseInt(qty);
        if (qty < 1) qty = 1;

        const existing = this.items.find(item => item.id === id);
        if (existing) {
            existing.qty = parseInt(existing.qty) + qty;
        } else {
            this.items.push({ id, name, price, type, qty: qty });
        }
        this.save();
        this.updateUI();
        this.openPanel();
    },

    remove(id) {
        this.items = this.items.filter(item => item.id !== id);
        this.save();
        this.updateUI();
    },

    save() {
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
        if(countEl) countEl.innerText = totalQty;

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
                    <span>$${item.price} x ${item.qty}</span>
                    <button onclick="Cart.remove(${item.id})" style="background: none; border: none; color: #720e1e; cursor: pointer;">&times;</button>
                </div>
            `;
            container.appendChild(el);
        });

        // Update Total
        const totalEl = document.getElementById('cart-total');
        if (totalEl) totalEl.innerText = '$' + totalPrice.toFixed(2);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Cart.init();
});
