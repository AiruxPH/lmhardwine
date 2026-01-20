<?php
include 'auth.php';
include '../includes/db.php';

// Fetch all orders
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
    </style>
</head>

<body>
    <div class="admin-container">
        <header style="margin-bottom: 2rem; border-bottom: 1px solid #333; padding-bottom: 1rem;">
            <h1><span class="text-accent">Admin</span> Dashboard</h1>
            <a href="../index.php" style="color: var(--color-text-muted);">← Back to Store</a>
        </header>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Recent Orders</h2>
            <a href="add_product.php" class="btn btn-primary btn-sm">Add New Product</a>
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
                                <td>$
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