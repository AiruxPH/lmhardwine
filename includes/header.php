<!DOCTYPE html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LM Hard Wine | Premium Selection</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="css/style.css?v=1.1">
</head>

<body>
    <header class="main-header">
        <div class="container nav-container">
            <a href="index.php" class="logo">LM <span>HARD</span> WINE</a>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Collection</a></li>
                    <li><a href="about.php">Our Story</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions" style="display: flex; align-items: center; gap: 1.5rem;">
                <!-- Auth Links -->
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <div style="color: white; font-size: 0.9rem;">
                        <span style="color: var(--color-text-muted);">Hello,</span>
                        <?php
                        $profileLink = ($_SESSION['role'] === 'seller') ? 'seller_profile.php' : 'profile.php';
                        ?>
                        <a href="<?php echo $profileLink; ?>"
                            style="color: var(--color-accent); font-weight: bold;"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    </div>
                    <a href="logout.php" style="color: white; font-size: 0.9rem; text-decoration: underline;">Logout</a>
                <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <a href="admin/index.php" style="color: var(--color-accent); font-weight: bold;">Admin Panel</a>
                    <a href="logout.php" style="color: white; font-size: 0.9rem;">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="color: white; font-size: 0.9rem;">Login / Register</a>
                <?php endif; ?>

                <button id="mobile-menu-toggle" class="mobile-toggle" aria-label="Toggle navigation">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>

                <!-- Cart Icon (Hidden for Admins and Sellers) -->
                <?php
                $hideCart = false;
                if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
                    $hideCart = true;
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller')
                    $hideCart = true;

                if (!$hideCart):
                    ?>
                    <a href="#" id="cart-toggle"
                        style="position: relative; display: flex; align-items: center; color: white;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span id="cart-count" class="cart-badge">0</span>
                    </a>
                <?php endif; ?>

                <a href="products.php" class="btn btn-primary" style="padding: 8px 20px; font-size: 0.8rem;">Shop
                    Now</a>
            </div>
        </div>
    </header>

    <!-- Pass Session Info to JS -->
    <script>
        <?php
        $cartKey = 'guest';
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            $cartKey = 'user_' . $_SESSION['user_id'];
        }
        $isAdmin = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ? 'true' : 'false';
        ?>
        const CART_USER_KEY = "<?php echo $cartKey; ?>";
        const IS_ADMIN = <?php echo $isAdmin; ?>;
    </script>

    <!-- Cart Sidebar -->
    <div id="cart-overlay" class="cart-overlay"></div>
    <div id="cart-panel" class="cart-panel">
        <div class="cart-header">
            <h3>Your Cellar</h3>
            <button id="close-cart"
                style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="cart-items" class="cart-items">
            <!-- Items rendered via JS -->
            <p style="text-align: center; color: #666; margin-top: 2rem;">Your cellar is empty.</p>
        </div>
        <div class="cart-footer">
            <div
                style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 1.2rem; font-weight: 700;">
                <span>Total</span>
                <span id="cart-total">$0.00</span>
            </div>
            <a href="checkout.php" class="btn btn-primary"
                style="width: 100%; text-align: center; border: none; display: block; text-decoration: none;">Proceed to
                Checkout</a>
        </div>
    </div>

    <script src="js/cart.js"></script>