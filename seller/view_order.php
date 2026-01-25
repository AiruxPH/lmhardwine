<?php
include 'auth.php';
include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// 1. Fetch Order Details (Basic Info)
// distinct check to ensure this seller actually has items in this order
$stmt = $pdo->prepare("
    SELECT o.*, u.username as customer_name, u.email as customer_email, cp.full_name, cp.phone_number
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN customer_profiles cp ON u.id = cp.user_id
    WHERE o.id = ? AND p.seller_id = ?
    LIMIT 1
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found or you do not have permission to view it.");
}

// 2. Fetch Order Items (Only for this seller)
$stmtItems = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_path
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ? AND p.seller_id = ?
");
$stmtItems->execute([$order_id, $user_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// Calculate Seller's Total for this order
$seller_total = 0;
foreach ($items as $item) {
    $seller_total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">

<style>
    body {
        padding-top: 80px;
    }

    .dashboard-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .page-header {
        margin-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-card {
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
    }

    .info-title {
        color: var(--color-text-muted);
        font-size: 0.9rem;
        text-transform: uppercase;
        margin-bottom: 1rem;
        letter-spacing: 1px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .items-table th,
    .items-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .items-table th {
        background: rgba(255, 255, 255, 0.05);
        color: var(--color-text-muted);
    }

    .product-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        vertical-align: middle;
        margin-right: 10px;
    }
</style>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header">
            <div>
                <h1 style="font-family: 'Playfair Display', serif;">Order #
                    <?php echo $order_id; ?>
                </h1>
                <p style="color: var(--color-text-muted);">
                    Placed on
                    <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                </p>
            </div>
            <a href="orders.php" class="btn btn-primary" style="background: transparent; border: 1px solid white;">Back
                to Orders</a>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-title">Customer Details</div>
                <div class="detail-row">
                    <span>Name:</span>
                    <strong>
                        <?php echo htmlspecialchars($order['full_name'] ?? $order['customer_name']); ?>
                    </strong>
                </div>
                <div class="detail-row">
                    <span>Email:</span>
                    <span>
                        <?php echo htmlspecialchars($order['customer_email']); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span>Phone:</span>
                    <span>
                        <?php echo htmlspecialchars($order['phone_number'] ?? 'N/A'); ?>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-title">Order Status</div>
                <div class="detail-row">
                    <span>Global Status:</span>
                    <strong style="color: var(--color-accent);">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </strong>
                </div>
                <div class="detail-row">
                    <span>Payment Method:</span>
                    <span>
                        <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span>Shipping Address:</span>
                    <span>
                        <?php echo htmlspecialchars($order['shipping_address']); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <h3 style="margin-bottom: 1rem;">Items to Fulfill</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php
                                $img = $item['image_path'];
                                // Fix path logic if needed
                                $display_path = strpos($img, 'uploads/') === 0 ? '../' . $img : '../uploads/' . $img;
                                ?>
                                <img src="<?php echo htmlspecialchars($display_path); ?>" class="product-thumb">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </td>
                            <td>₱
                                <?php echo number_format($item['price'], 2); ?>
                            </td>
                            <td>
                                <?php echo $item['quantity']; ?>
                            </td>
                            <td class="text-accent">₱
                                <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold; padding-top: 1.5rem;">Your Share
                            Total:</td>
                        <td
                            style="font-weight: bold; padding-top: 1.5rem; font-size: 1.2rem; color: var(--color-accent);">
                            ₱
                            <?php echo number_format($seller_total, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</body>

</html>