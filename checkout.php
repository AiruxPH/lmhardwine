<?php include 'includes/header.php'; ?>

<main style="padding-top: 100px; padding-bottom: var(--spacing-xl);">
    <div class="container" style="max-width: 600px;">
        <h1 class="text-center" style="margin-bottom: 2rem;">Checkout</h1>

        <div class="glass-card">
            <form action="includes/place_order.php" method="POST" id="checkout-form">

                <h3 style="margin-bottom: 1.5rem; color: var(--color-accent);">Shipping Details</h3>

                <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
                    <div>
                        <label style="color: var(--color-text-muted);">Full Name</label>
                        <input type="text" name="customer_name" required
                            style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;">
                    </div>

                    <div>
                        <label style="color: var(--color-text-muted);">Email Address</label>
                        <input type="email" name="customer_email" required
                            style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;">
                    </div>

                    <div>
                        <label style="color: var(--color-text-muted);">Shipping Address</label>
                        <textarea name="customer_address" required rows="3"
                            style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;"></textarea>
                    </div>
                </div>

                <!-- Hidden Input for Cart Data -->
                <input type="hidden" name="cart_data" id="cart-data">

                <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; border: none; font-size: 1.1rem;">Place Order</button>
                    <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #666;">Payment will be
                        collected upon delivery.</p>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.getElementById('checkout-form').addEventListener('submit', function (e) {
        // Validation: Check if cart is empty
        const cartItems = localStorage.getItem('lm_cart');
        if (!cartItems || JSON.parse(cartItems).length === 0) {
            e.preventDefault();
            alert('Your cart is empty!');
            return;
        }

        // Dump Cart to Hidden Input
        document.getElementById('cart-data').value = cartItems;
    });
</script>

<?php include 'includes/footer.php'; ?>