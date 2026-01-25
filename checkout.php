<?php
include 'includes/header.php';

// Gatekeeping: Block Admins
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/index.php');
    exit;
}

// Gatekeeping: Block Sellers
if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller') {
    header('Location: seller/index.php');
    exit;
}
?>

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
        const isGuest = (typeof CART_USER_KEY !== 'undefined' && CART_USER_KEY === 'guest');

        let cartItems = null;

        if (isGuest) {
            // Guest Mode: Check LocalStorage
            const stored = localStorage.getItem('lm_cart_guest');
            if (!stored || JSON.parse(stored).length === 0) {
                e.preventDefault();
                alert('Your cart is empty!');
                return;
            }
            cartItems = stored;
        } else {
            // User Mode: We assume backend validation, but we can check UI count as backup
            // Actually, backend 'place_order.php' will fetch from DB.
            // We do NOT need to populate hidden input, but we can for legacy fallback or leave empty.
        }

        // Dump Cart to Hidden Input (Only needed for Guest)
        if (cartItems) {
            document.getElementById('cart-data').value = cartItems;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>