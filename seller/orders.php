<?php
include 'auth.php';
include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Fetch Orders containing Seller's Products
// We need to act like a marketplace: One order might have items from multiple sellers.
// We only show the portion relevant to THIS seller.
$stmt = $pdo->prepare("
    SELECT 
        o.id as order_id, 
        o.order_date, 
        o.status,
        COALESCE(u.username, o.customer_name) as customer_name,
        SUM(oi.price_at_purchase * oi.quantity) as seller_total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN users u ON o.user_id = u.id
    WHERE p.seller_id = ? AND o.is_deleted = 0
    GROUP BY o.id, o.order_date, o.status, customer_name
    ORDER BY o.order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<style>
    body {
        padding-top: 80px;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .page-header {
        margin-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 1rem;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        color: var(--color-text-main);
    }

    .orders-table th,
    .orders-table td {
        text-align: left;
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .orders-table th {
        color: var(--color-text-muted);
        text-transform: uppercase;
        font-size: 0.8rem;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-pending {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-completed {
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .status-shipped {
        background: rgba(33, 150, 243, 0.2);
        color: #2196f3;
    }

    .status-cancelled {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }
</style>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header">
            <h1 style="font-family: 'Playfair Display', serif;">Customer Orders</h1>
            <p style="color: var(--color-text-muted);">View and manage orders for your products.</p>
        </div>

        <div class="glass-card">
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <p style="color: var(--color-text-muted);">No orders found for your products yet.</p>
                </div>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Your Revenue</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>#
                                    <?php echo htmlspecialchars($o['order_id']); ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($o['order_date'])); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($o['customer_name'] ?? 'Guest'); ?>
                                </td>
                                <td class="text-accent">₱
                                    <?php echo number_format($o['seller_total'], 2); ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'status-' . strtolower($o['status']);
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($o['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_order.php?id=<?php echo $o['order_id']; ?>" class="btn btn-primary"
                                        style="padding: 5px 15px; font-size: 0.8rem;">View Items</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>