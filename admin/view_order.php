<?php
include 'auth.php';
include '../includes/db.php';

if (!isset($_GET['id'])) {
    die("Order ID not specified.");
}

$order_id = $_GET['id'];

// Fetch Order Info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

// Fetch Order Items
$stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        // Refresh
        header("Location: view_order.php?id=$order_id");
        exit;
    }

    if (isset($_POST['delete_order'])) {
        // 1. Hide the Order
        $stmt = $pdo->prepare("UPDATE orders SET is_deleted = 1 WHERE id = ?");
        $stmt->execute([$order_id]);

        // 2. Hide the Items belonging to that Order (Optional but clean)
        $stmtItems = $pdo->prepare("UPDATE order_items SET is_deleted = 1 WHERE order_id = ?");
        $stmtItems->execute([$order_id]);

        header("Location: index.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #
        <?php echo htmlspecialchars($order_id); ?> - Admin
    </title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .details-card h3 {
            color: var(--color-accent);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .info-row {
            margin-bottom: 0.5rem;
            display: flex;
        }

        .info-label {
            width: 100px;
            color: var(--color-text-muted);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .items-table th,
        .items-table td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .items-table th {
            color: var(--color-text-muted);
            font-weight: normal;
        }

        .total-row td {
            border-top: 2px solid var(--color-accent);
            font-weight: bold;
            font-size: 1.2rem;
            padding-top: 1rem;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="order-header">
            <h1>Order #
                <?php echo htmlspecialchars($order['id']); ?>
            </h1>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>

        <div class="details-grid">
            <div class="details-card glass-card">
                <h3>Customer Details</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span>
                        <?php echo htmlspecialchars($order['customer_name']); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span>
                        <?php echo htmlspecialchars($order['customer_email']); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span>
                        <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>
                    </span>
                </div>
            </div>

            <div class="details-card glass-card">
                <h3>Order Status</h3>
                <form method="POST" style="margin-top: 1rem;">
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <?php
                            $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Canceled'];
                            foreach ($statuses as $s) {
                                $selected = ($order['status'] == $s) ? 'selected' : '';
                                echo "<option value='$s' $selected>$s</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">Update
                        Status</button>
                </form>

                <form method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this order? This cannot be undone.');"
                    style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                    <button type="submit" name="delete_order" class="btn"
                        style="width: 100%; border-color: #f44336; color: #f44336;">Delete Order</button>
                </form>
            </div>
        </div>

        <div class="glass-card">
            <h3>Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </td>
                            <td>₱
                                <?php echo number_format($item['price_at_purchase'], 2); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($item['quantity']); ?>
                            </td>
                            <td>₱
                                <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;">Grand Total:</td>
                        <td>₱
                            <?php echo number_format($order['total_amount'], 2); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>