<?php
include 'auth.php';
include '../includes/db.php';

$user_id = $_GET['id'] ?? 0;

// 1. Fetch Basic User Info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

$role = $user['role'];
$profile = null;
$activity = [];

if ($role === 'customer') {
    // 2a. Fetch Customer Profile
    $stmt = $pdo->prepare("SELECT * FROM customer_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3a. Fetch Order Summary
    $stmt = $pdo->prepare("SELECT id, order_date, total_amount, status FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // 2b. Fetch Seller Profile
    $stmt = $pdo->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3b. Fetch Product Summary
    $stmt = $pdo->prepare("SELECT id, name, price, stock_qty FROM products WHERE seller_id = ? AND is_deleted = 0 LIMIT 5");
    $stmt->execute([$user_id]);
    $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4b. Fetch Recent Sales
    $stmt = $pdo->prepare("
        SELECT DISTINCT o.id, o.order_date, o.status 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        JOIN products p ON oi.product_id = p.id 
        WHERE p.seller_id = ? 
        ORDER BY o.order_date DESC LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile - #
        <?php echo $user_id; ?>
    </title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .info-card {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-label {
            color: var(--color-text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 4px;
            display: block;
        }

        .info-value {
            font-size: 1.1rem;
            color: #fff;
            margin-bottom: 1rem;
            display: block;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .activity-table th,
        .activity-table td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .activity-table th {
            color: #666;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .role-tag {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1 style="margin-bottom: 5px;">
                    <?php echo htmlspecialchars($user['username']); ?> <span class="role-tag">
                        <?php echo $role; ?>
                    </span>
                </h1>
                <p style="color: #666;">User ID: #
                    <?php echo $user_id; ?> • Joined
                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                </p>
            </div>
            <a href="users.php" class="btn" style="background: transparent; border: 1px solid #444;">Back to List</a>
        </div>

        <div class="profile-grid">
            <!-- Sidebar: Details -->
            <div class="profile-sidebar">
                <div class="glass-card info-card">
                    <h3
                        style="margin-bottom: 1.5rem; color: var(--color-accent); border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
                        Basic Info</h3>

                    <span class="info-label">Email Address</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </span>

                    <?php if ($role === 'customer'): ?>
                        <span class="info-label">Full Name</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($profile['full_name'] ?? 'Not set'); ?>
                        </span>

                        <span class="info-label">Phone</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($profile['phone_number'] ?? 'Not set'); ?>
                        </span>

                        <span class="info-label">Default Address</span>
                        <p style="font-size: 0.9rem; color: #ccc;">
                            <?php echo nl2br(htmlspecialchars($profile['default_shipping_address'] ?? 'No address saved.')); ?>
                        </p>
                    <?php else: ?>
                        <span class="info-label">Brand Name</span>
                        <span class="info-value" style="color: var(--color-accent);">
                            <?php echo htmlspecialchars($profile['brand_name'] ?? 'Not set'); ?>
                        </span>

                        <span class="info-label">Contact Email</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($profile['contact_email'] ?? 'Not set'); ?>
                        </span>

                        <span class="info-label">Description</span>
                        <p style="font-size: 0.9rem; color: #ccc;">
                            <?php echo nl2br(htmlspecialchars($profile['brand_description'] ?? 'No description.')); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Content Area: Activity -->
            <div class="profile-content">
                <?php if ($role === 'customer'): ?>
                    <div class="glass-card" style="padding: 1.5rem;">
                        <h3 style="margin-bottom: 1.5rem;">Recent Purchase History</h3>
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activity)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: #666; padding: 2rem;">No orders yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activity as $o): ?>
                                        <tr>
                                            <td>#
                                                <?php echo $o['id']; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($o['order_date'])); ?>
                                            </td>
                                            <td>₱
                                                <?php echo number_format($o['total_amount'], 2); ?>
                                            </td>
                                            <td><span
                                                    style="color: <?php echo $o['status'] == 'Pending' ? '#d4af37' : '#4caf50'; ?>">
                                                    <?php echo $o['status']; ?>
                                                </span></td>
                                            <td><a href="view_order.php?id=<?php echo $o['id']; ?>" class="text-accent">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="glass-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1.5rem;">Active Inventory</h3>
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Product #</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activity)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: #666; padding: 2rem;">No products
                                            listed.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activity as $p): ?>
                                        <tr>
                                            <td>#
                                                <?php echo $p['id']; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($p['name']); ?>
                                            </td>
                                            <td>₱
                                                <?php echo number_format($p['price'], 2); ?>
                                            </td>
                                            <td>
                                                <?php echo $p['stock_qty']; ?>
                                            </td>
                                            <td><a href="edit_product.php?id=<?php echo $p['id']; ?>" class="text-accent">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div style="margin-top: 1rem; text-align: right;">
                            <a href="products.php" class="btn btn-sm"
                                style="background: transparent; border: 1px solid #444;">View Full Inventory</a>
                        </div>
                    </div>

                    <div class="glass-card" style="padding: 1.5rem;">
                        <h3 style="margin-bottom: 1.5rem;">Recent Sales</h3>
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_sales)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #666; padding: 2rem;">No sales yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_sales as $s): ?>
                                        <tr>
                                            <td>#
                                                <?php echo $s['id']; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($s['order_date'])); ?>
                                            </td>
                                            <td>
                                                <?php echo $s['status']; ?>
                                            </td>
                                            <td><a href="view_order.php?id=<?php echo $s['id']; ?>" class="text-accent">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>