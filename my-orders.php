<?php
include 'includes/db.php';
include 'includes/header.php';

// Gatekeeping: Redirect if not logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Gatekeeping: Redirect sellers to their portal
if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller') {
    header('Location: seller/orders.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch User's Orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND is_deleted = 0 ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding-top: 100px; padding-bottom: var(--spacing-xl);">
    <div class="container">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 0.5rem;">My <span
                    style="color: var(--color-accent);">Orders</span></h1>
            <p style="color: var(--color-text-muted);">Track and view details of your previous collections.</p>
        </div>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 5rem 2rem;">
                    <span style="font-size: 4rem; opacity: 0.1; display: block; margin-bottom: 1rem;">🍷</span>
                    <p style="color: var(--color-text-muted);">You haven't placed any orders yet.</p>
                    <a href="products.php" class="btn btn-primary" style="margin-top: 1.5rem;">Explore Collection</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <th
                                    style="text-align: left; padding: 1.5rem; color: var(--color-text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                                    Order ID</th>
                                <th
                                    style="text-align: left; padding: 1.5rem; color: var(--color-text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                                    Date</th>
                                <th
                                    style="text-align: left; padding: 1.5rem; color: var(--color-text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                                    Total Amount</th>
                                <th
                                    style="text-align: left; padding: 1.5rem; color: var(--color-text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                                    Status</th>
                                <th
                                    style="text-align: right; padding: 1.5rem; color: var(--color-text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); transition: background 0.3s;"
                                    onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                                    onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem; font-weight: 600; color: #fff;">#
                                        <?php echo $o['id']; ?>
                                    </td>
                                    <td style="padding: 1.5rem; color: #888;">
                                        <?php echo date('M d, Y', strtotime($o['order_date'])); ?>
                                    </td>
                                    <td
                                        style="padding: 1.5rem; color: var(--color-accent); font-weight: bold; font-family: 'Playfair Display', serif; font-size: 1.1rem;">
                                        ₱
                                        <?php echo number_format($o['total_amount'], 2); ?>
                                    </td>
                                    <td style="padding: 1.5rem;">
                                        <?php
                                        $status = strtolower($o['status']);
                                        $bg = 'rgba(255,193,7,0.1)';
                                        $color = '#ffc107';
                                        if ($status === 'completed') {
                                            $bg = 'rgba(76,175,80,0.1)';
                                            $color = '#4caf50';
                                        } elseif ($status === 'shipped') {
                                            $bg = 'rgba(33,150,243,0.1)';
                                            $color = '#2196f3';
                                        } elseif ($status === 'cancelled') {
                                            $bg = 'rgba(244,67,54,0.1)';
                                            $color = '#f44336';
                                        }
                                        ?>
                                        <span
                                            style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <?php echo htmlspecialchars($o['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1.5rem; text-align: right;">
                                        <a href="view-order-details.php?id=<?php echo $o['id']; ?>" class="btn"
                                            style="padding: 6px 15px; font-size: 0.85rem; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">View
                                            Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>