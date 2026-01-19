<!DOCTYPE html>
<?php
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
                    <li><a href="index.php#about">Our Story</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="header-actions" style="display: flex; align-items: center; gap: 1.5rem;">
                <button id="mobile-menu-toggle" class="mobile-toggle" aria-label="Toggle navigation">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <a href="#" id="cart-toggle"
                    style="position: relative; display: flex; align-items: center; color: white;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span id="cart-count" class="cart-badge">0</span>
                </a>
                <a href="products.php" class="btn btn-primary" style="padding: 8px 20px; font-size: 0.8rem;">Shop
                    Now</a>
            </div>
        </div>
    </header>

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
            <button class="btn btn-primary" style="width: 100%; text-align: center; border: none;">Proceed to
                Checkout</button>
        </div>
    </div>

    <script src="js/cart.js"></script>