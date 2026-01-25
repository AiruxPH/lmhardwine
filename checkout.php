<?php
include 'includes/header.php';
include 'includes/db.php';

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

// Pre-fill Logic
$pre_name = '';
$pre_email = '';
$pre_address = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT u.email, cp.full_name, cp.default_shipping_address FROM users u LEFT JOIN customer_profiles cp ON u.id = cp.user_id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $uData = $stmt->fetch();
    if ($uData) {
        $pre_name = $uData['full_name'] ?? '';
        $pre_email = $uData['email'] ?? '';
        $pre_address = $uData['default_shipping_address'] ?? '';
    }
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
                            value="<?php echo htmlspecialchars($pre_name); ?>"
                            style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;">
                    </div>

                    <div>
                        <label style="color: var(--color-text-muted);">Email Address</label>
                        <input type="email" name="customer_email" required
                            value="<?php echo htmlspecialchars($pre_email); ?>"
                            style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;">
                    </div>

                    <div style="position: relative;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <label style="color: var(--color-text-muted);">Shipping Address</label>
                            <button type="button" onclick="detectLocation()" id="detect-btn"
                                style="background: none; border: none; color: var(--color-accent); font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                <span id="detect-icon">📍</span> <span id="detect-text">Use My Current Location</span>
                            </button>
                        </div>
                        <textarea id="customer_address" name="customer_address" required rows="3"
                            style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;"><?php echo htmlspecialchars($pre_address); ?></textarea>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="save_default_address" id="save_default"
                                    style="cursor: pointer;">
                                <label for="save_default" style="font-size: 0.85rem; color: #aaa; cursor: pointer;">Save as
                                    my
                                    default shipping address</label>
                            </div>
                        <?php endif; ?>
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
    async function detectLocation() {
        const btn = document.getElementById('detect-btn');
        const text = document.getElementById('detect-text');
        const icon = document.getElementById('detect-icon');
        const addressField = document.getElementById('customer_address');

        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your browser");
            return;
        }

        btn.disabled = true;
        text.innerText = "Locating...";
        icon.classList.add('loading-pulse');

        navigator.geolocation.getCurrentPosition(async (position) => {
            const { latitude, longitude } = position.coords;
            try {
                // Using Nominatim (OpenStreetMap) for reverse geocoding
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`);
                const data = await response.json();

                if (data.display_name) {
                    addressField.value = data.display_name;
                } else {
                    addressField.value = `Lat: ${latitude}, Lon: ${longitude}`;
                }
            } catch (error) {
                console.error("Geocoding failed:", error);
                addressField.value = `Lat: ${latitude}, Lon: ${longitude}`;
            } finally {
                btn.disabled = false;
                text.innerText = "Use My Current Location";
                icon.classList.remove('loading-pulse');
            }
        }, (error) => {
            console.error("Geolocation error:", error);
            alert("Unable to retrieve your location. Please check your permissions.");
            btn.disabled = false;
            text.innerText = "Use My Current Location";
            icon.classList.remove('loading-pulse');
        });
    }

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
        }

        // Dump Cart to Hidden Input (Only needed for Guest)
        if (cartItems) {
            document.getElementById('cart-data').value = cartItems;
        }
    });
</script>

<style>
    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.3;
        }

        100% {
            opacity: 1;
        }
    }

    .loading-pulse {
        animation: pulse 1s infinite ease-in-out;
    }
</style>

<?php include 'includes/footer.php'; ?>