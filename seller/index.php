<?php
include 'auth.php';
include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// 1. Get Brand Name
$stmt = $pdo->prepare("SELECT brand_name FROM seller_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$brand = $stmt->fetch();
$brand_name = $brand['brand_name'] ?? 'My Brand';

// 2. Total Products
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE seller_id = ? AND is_deleted = 0");
$stmt->execute([$user_id]);
$product_count = $stmt->fetch()['count'];

// 3. Orders (Placeholder logic until orders link properly)
// 3. Pending Orders Count (Orders containing seller's products that are Pending)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id) as count 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND o.status = 'Pending' AND o.is_deleted = 0
");
$stmt->execute([$user_id]);
$order_count = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<?php include 'includes/header.php'; ?>

<style>
    body {
        padding-top: 80px;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .welcome-banner {
        margin-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: end;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--color-text-main);
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .stat-label {
        color: var(--color-text-muted);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }

    .action-card {
        border-left: 3px solid var(--color-accent);
        transition: transform 0.2s;
    }

    .action-card:hover {
        transform: translateY(-2px);
    }
</style>

<div class="dashboard-container">
    <div class="welcome-banner">
        <div>
            <h1 style="font-family: 'Playfair Display', serif;">Welcome, <span class="text-accent">
                    <?php echo htmlspecialchars($brand_name); ?>
                </span></h1>
            <p style="color: var(--color-text-muted);">Here is what's happening with your store today.</p>
        </div>
        <div>
            <a href="add_product.php" class="btn btn-primary">+ Add New Product</a>
        </div>
    </div>

    <div class="stats-grid">
        <!-- Products -->
        <div class="glass-card stat-card">
            <div class="stat-value text-accent">
                <?php echo $product_count; ?>
            </div>
            <div class="stat-label">Active Products</div>
        </div>

        <!-- Orders (Placeholder) -->
        <div class="glass-card stat-card">
            <div class="stat-value">
                <?php echo $order_count; ?>
            </div>
            <div class="stat-label">Pending Orders</div>
        </div>

        <!-- Quick Actions -->
        <a href="profile.php" class="glass-card stat-card action-card" style="text-decoration: none;">
            <div class="stat-value" style="font-size: 1.5rem; color: white;">Edit Profile</div>
            <div class="stat-label">Manage Brand Info</div>
        </a>
    </div>

    <div class="glass-card">
        <h2 style="margin-bottom: 1rem;">Quick Start Guide</h2>
        <ul style="color: var(--color-text-muted); line-height: 1.6; padding-left: 1.2rem;">
            <li>Go to <strong>My Products</strong> to list your wines.</li>
            <li>Check <strong>Orders</strong> to fulfil new purchases.</li>
            <li>Update your <strong>Brand Profile</strong> with a custom logo and description.</li>
        </ul>
    </div>
</div>

</body>

</html>