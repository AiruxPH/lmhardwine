const Cart = {
    items: [],

    init() {
        const stored = localStorage.getItem('lm_cart');
        if (stored) {
            this.items = JSON.parse(stored);
        }
        this.updateUI();

        // Event Listeners
        document.getElementById('cart-toggle').addEventListener('click', (e) => {
            e.preventDefault();
            this.togglePanel();
        });

        document.getElementById('close-cart').addEventListener('click', (e) => {
            e.preventDefault();
            this.togglePanel();
        });
    },

    add(id, name, price, type) {
        const existing = this.items.find(item => item.id === id);
        if (existing) {
            existing.qty++;
        } else {
            this.items.push({ id, name, price, type, qty: 1 });
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
        localStorage.setItem('lm_cart', JSON.stringify(this.items));
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
        const totalQty = this.items.reduce((sum, item) => sum + item.qty, 0);
        document.getElementById('cart-count').innerText = totalQty;

        // Render Items
        const container = document.getElementById('cart-items');
        container.innerHTML = '';

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
        document.getElementById('cart-total').innerText = '$' + totalPrice.toFixed(2);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Cart.init();
});
