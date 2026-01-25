<?php
include 'auth.php';
include '../includes/db.php';

// ... includes ...

// 1. Total Sales (Revenue)
$stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE is_deleted = 0");
$total_sales = $stmt->fetch()['total'] ?? 0;

// 2. Total Orders Count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE is_deleted = 0");
$total_orders = $stmt->fetch()['count'];

// 3. Pending Orders Count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending' AND is_deleted = 0");
$pending_orders = $stmt->fetch()['count'];

// 4. Total Users Count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

// 5. Fetch Recent Orders (Existing code)
$stmt = $pdo->query("SELECT * FROM orders WHERE is_deleted = 0 ORDER BY order_date DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LM Hard Wine</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            color: var(--color-text-main);
        }

        .orders-table th,
        .orders-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .orders-table th {
            background-color: rgba(114, 14, 30, 0.2);
            color: var(--color-accent);
        }

        .orders-table tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .btn-sm {
            padding: 5px 15px;
            font-size: 0.8rem;
        }

        /* Add to your existing styles */
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
    </style>
</head>

<body>
    <div class="admin-container">
        <header style="margin-bottom: 2rem; border-bottom: 1px solid #333; padding-bottom: 1rem;">
            <h1><span class="text-accent">Admin</span> Dashboard</h1>
            <a href="../index.php" style="color: var(--color-text-muted);">← Back to Store</a>
        </header>

        <div class="stats-grid">
            <div class="glass-card stat-card">
                <div class="stat-value text-accent">₱
                    <?php echo number_format($total_sales, 2); ?>
                </div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="glass-card stat-card">
                <div class="stat-value">
                    <?php echo $total_orders; ?>
                </div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="glass-card stat-card" style="border-left: 3px solid #d4af37;">
                <div class="stat-value" style="color: #d4af37;">
                    <?php echo $pending_orders; ?>
                </div>
                <div class="stat-label">Orders Pending</div>
            </div>

            <div class="glass-card stat-card" style="border-left: 3px solid #2196f3;">
                <div class="stat-value" style="color: #2196f3;">
                    <?php echo $total_users; ?>
                </div>
                <div class="stat-label">Registered Users</div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Recent Orders</h2>
            <div>
                <a href="messages.php" class="btn btn-sm"
                    style="margin-right: 10px; border-color: #4caf50; color: #4caf50;">View Messages</a>
                <a href="users.php" class="btn btn-sm"
                    style="margin-right: 10px; border-color: #2196f3; color: #2196f3;">Manage Users</a>
                <a href="add_seller.php" class="btn btn-sm"
                    style="margin-right: 10px; border-color: #d4af37; color: #d4af37;">Add New Seller</a>
                <a href="products.php" class="btn btn-sm"
                    style="margin-right: 10px; border-color: #888; color: #ccc;">Manage Products</a>
                <a href="add_product.php" class="btn btn-primary btn-sm">Add New Product</a>
            </div>
        </div>

        <div class="glass-card">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No orders found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#
                                    <?php echo htmlspecialchars($order['id']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                </td>
                                <td>₱
                                    <?php echo number_format($order['total_amount'], 2); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['order_date']); ?>
                                </td>
                                <td>
                                    <span style="color: <?php echo $order['status'] == 'Pending' ? '#d4af37' : '#4caf50'; ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>