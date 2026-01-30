<?php
include 'includes/db.php';
include 'includes/header.php';

// Gatekeeping
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch Order Summary (Ensure it belongs to the user)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND is_deleted = 0");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='container' style='padding-top: 150px; text-align: center;'><h2>Order not found.</h2><a href='my-orders.php'>Back to Orders</a></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch Order Items
$stmt_items = $pdo->prepare("SELECT oi.*, p.image_path, p.type FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

// Fetch Status History
$stmt_hist = $pdo->prepare("SELECT * FROM order_history WHERE order_id = ? ORDER BY changed_at DESC");
$stmt_hist->execute([$order_id]);
$history = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding-top: 100px; padding-bottom: var(--spacing-xl);">
    <div class="container" style="max-width: 900px;">
        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <a href="my-orders.php"
                    style="color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem; display: block; margin-bottom: 10px;">←
                    Back to My Orders</a>
                <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin: 0;">Order <span
                        style="color: var(--color-accent);">#<?php echo $order['id']; ?></span></h1>
                <p style="color: var(--color-text-muted); margin: 5px 0 0 0;">Placed on
                    <?php echo date('F d, Y \a\t h:i A', strtotime($order['order_date'])); ?>
                </p>
            </div>
            <div style="text-align: right;">
                <?php
                $status = strtolower($order['status']);
                $color = '#ffc107';
                if ($status === 'completed')
                    $color = '#4caf50';
                elseif ($status === 'shipped')
                    $color = '#2196f3';
                else
                    $color = '#ffc107';
                ?>
                <span
                    style="color: <?php echo $color; ?>; font-weight: bold; text-transform: uppercase; font-size: 1rem; border: 1px solid <?php echo $color; ?>; padding: 5px 15px; border-radius: 30px;">
                    <?php echo htmlspecialchars($order['status']); ?>
                </span>
            </div>
        </div>

        <div class="order-details-grid">
            <!-- Items List -->
            <div class="glass-card" style="padding: 0;">
                <div style="padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <h3 style="margin: 0; font-size: 1.2rem;">Order Items</h3>
                </div>
                <div style="padding: 1rem;">
                    <?php foreach ($items as $item): ?>
                        <div
                            style="display: flex; gap: 1.5rem; padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.03); align-items: center;">
                            <div
                                style="width: 70px; height: 70px; background: rgba(255,255,255,0.05); border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                                <?php if ($item['image_path']): ?>
                                    <?php $img = $item['image_path'];
                                    $src = (strpos($img, 'uploads/') === 0) ? $img : 'uploads/' . $img; ?>
                                    <img src="<?php echo $src; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; opacity: 0.1; font-weight: bold;">
                                        <?php echo $item['type'] ?? '🍷'; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: 1rem; color: #fff;">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </h4>
                                <p style="margin: 3px 0 0 0; color: #666; font-size: 0.9rem;">Quantity:
                                    <?php echo $item['quantity']; ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <p style="margin: 0; font-weight: bold; color: var(--color-accent);">
                                    ₱<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></p>
                                <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: #555;">
                                    ₱<?php echo number_format($item['price_at_purchase'], 2); ?> each</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div
                    style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.01);">
                    <span style="font-size: 1.1rem; color: #888;">Order Total</span>
                    <span
                        style="font-size: 1.8rem; color: #fff; font-family: 'Playfair Display', serif; font-weight: bold;">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <!-- Shipping Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <div class="glass-card">
                    <h3
                        style="margin-bottom: 1.2rem; font-size: 1.1rem; color: var(--color-accent); text-transform: uppercase; letter-spacing: 1px;">
                        Shipping To</h3>
                    <p style="color: #fff; font-weight: 600; margin-bottom: 5px;">
                        <?php echo htmlspecialchars($order['customer_name']); ?>
                    </p>
                    <p style="color: #888; font-size: 0.9rem; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>
                    </p>
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
                        <p style="color: #666; font-size: 0.8rem; text-transform: uppercase;">Contact Email</p>
                        <p style="color: #ccc; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($order['customer_email']); ?>
                        </p>
                    </div>
                </div>

                <div class="glass-card"
                    style="background: linear-gradient(135deg, rgba(114, 14, 30, 0.1) 0%, rgba(20, 20, 20, 0.2) 100%); border-left: 2px solid var(--color-accent);">
                    <h4 style="margin: 0 0 10px 0; font-size: 0.9rem; color: var(--color-accent);">Payment Method</h4>
                    <p style="margin: 0; color: #fff; font-size: 0.95rem;">Cash on Delivery</p>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.8rem;">To be collected upon arrival.</p>
                </div>

                <!-- Order Timeline -->
                <div class="glass-card">
                    <h3
                        style="margin-bottom: 1.2rem; font-size: 1.1rem; color: var(--color-accent); text-transform: uppercase;">
                        Order Timeline</h3>
                    <div style="position: relative; padding-left: 20px;">
                        <div
                            style="position: absolute; left: 4px; top: 5px; bottom: 5px; width: 2px; background: rgba(255,255,255,0.05);">
                        </div>

                        <?php if (empty($history)): ?>
                            <div style="position: relative; margin-bottom: 1rem;">
                                <div
                                    style="position: absolute; left: -21px; top: 6px; width: 10px; height: 10px; border-radius: 50%; background: #666; border: 2px solid #1a1a1a;">
                                </div>
                                <p style="margin: 0; font-size: 0.9rem; color: #888;">Processing your order...</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                                <div style="position: relative; margin-bottom: 1.5rem;">
                                    <div
                                        style="position: absolute; left: -21px; top: 6px; width: 10px; height: 10px; border-radius: 50%; background: var(--color-accent); border: 2px solid #1a1a1a;">
                                    </div>
                                    <p
                                        style="margin: 0; font-weight: 600; color: #fff; font-size: 0.9rem; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($h['status_to']); ?>
                                    </p>
                                    <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: #555;">
                                        <?php echo date('M d, Y - h:i A', strtotime($h['changed_at'])); ?>
                                    </p>
                                    <?php if (!empty($h['notes'])): ?>
                                        <div
                                            style="margin-top: 5px; padding: 8px; background: rgba(255,255,255,0.02); border-radius: 4px; font-size: 0.85rem; color: #aaa; border: 1px solid rgba(255,255,255,0.03);">
                                            <?php echo nl2br(htmlspecialchars($h['notes'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <div style="position: relative;">
                            <div
                                style="position: absolute; left: -21px; top: 6px; width: 10px; height: 10px; border-radius: 50%; background: #444; border: 2px solid #1a1a1a;">
                            </div>
                            <p
                                style="margin: 0; font-weight: 600; color: #aaa; font-size: 0.9rem; text-transform: uppercase;">
                                Order Placed</p>
                            <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: #555;">
                                <?php echo date('M d, Y - h:i A', strtotime($order['order_date'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>