<!DOCTYPE html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Portal | LM Hard Wine</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style.css?v=1.4">
    <style>
        .seller-nav-active {
            color: var(--color-accent) !important;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="container nav-container">
            <a href="index.php" class="logo">SELLER <span>PORTAL</span></a>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="products.php">My Products</a></li>
                    <li><a href="orders.php">Orders</a></li>
                </ul>
            </nav>
            <div class="header-actions" style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="color: white; font-size: 0.9rem;">
                    <span style="color: var(--color-text-muted);">Seller:</span>
                    <a href="profile.php" style="color: var(--color-accent); font-weight: bold;">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                </div>
                <a href="../logout.php" style="color: white; font-size: 0.9rem; text-decoration: underline;">Logout</a>

                <button id="mobile-menu-toggle" class="mobile-toggle" aria-label="Toggle navigation">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>
        </div>
    </header>