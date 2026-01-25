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
    $seller_total += $item['price_at_purchase'] * $item['quantity'];
}

// 3. Fetch Status History
$stmt_hist = $pdo->prepare("SELECT * FROM order_history WHERE order_id = ? ORDER BY changed_at DESC");
$stmt_hist->execute([$order_id]);
$history = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

// 4. Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $notes = trim($_POST['status_notes']);
    $old_status = $order['status'];

    // State Machine
    $allowed = [
        'Pending' => ['Processing', 'Canceled'],
        'Processing' => ['Shipped', 'Canceled'],
        'Shipped' => ['Delivered']
    ];

    $is_valid = (isset($allowed[$old_status]) && in_array($new_status, $allowed[$old_status]));

    if ($new_status === $old_status) {
        $error = "Status is already $new_status.";
    } elseif (!$is_valid) {
        $error = "Invalid status transition.";
    } else {
        $pdo->beginTransaction();
        try {
            // Update Order Status
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);

            // Log to History (Seller Action)
            $log_notes = "[Seller Note] " . $notes;
            $stmt_log = $pdo->prepare("INSERT INTO order_history (order_id, status_from, status_to, notes) VALUES (?, ?, ?, ?)");
            $stmt_log->execute([$order_id, $old_status, $new_status, $log_notes]);

            // Stock Reversion if Canceled (Only for this seller's items)
            if ($new_status === 'Canceled') {
                foreach ($items as $item) {
                    $stmt_stock = $pdo->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?");
                    $stmt_stock->execute([$item['quantity'], $item['product_id']]);
                }
            }

            $pdo->commit();
            header("Location: view_order.php?id=$order_id&success=1");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Update failed: " . $e->getMessage();
        }
    }
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
                    <span>Shipping Address:</span>
                    <span>
                        <?php echo htmlspecialchars($order['customer_address']); ?>
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
                <div class="info-title">Order Status & Fulfillment</div>
                <?php
                $current_status = $order['status'];
                $is_final = ($current_status === 'Delivered' || $current_status === 'Canceled');

                $allowed_map = [
                    'Pending' => ['Processing', 'Canceled'],
                    'Processing' => ['Shipped', 'Canceled'],
                    'Shipped' => ['Delivered'],
                    'Delivered' => [],
                    'Canceled' => []
                ];
                $next_steps = $allowed_map[$current_status] ?? [];
                ?>

                <div class="detail-row">
                    <span>Global Status:</span>
                    <strong
                        style="color: var(--color-accent);"><?php echo htmlspecialchars($current_status); ?></strong>
                </div>

                <?php if ($is_final): ?>
                    <p style="margin-top: 1rem; font-size: 0.85rem; color: #666; text-align: center;">Order is in a final
                        state.</p>
                <?php else: ?>
                    <form method="POST" style="margin-top: 1.5rem;">
                        <?php if (isset($error)): ?>
                            <div style="color: #f44336; font-size: 0.8rem; margin-bottom: 0.5rem;"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div style="margin-bottom: 0.8rem;">
                            <label style="font-size: 0.75rem; color: #888; display: block; margin-bottom: 4px;">Transition
                                To:</label>
                            <select name="status" id="status-select-seller" required onchange="updateNoteTemplatesSeller()"
                                style="width: 100%; background: #1a1a1a; border: 1px solid #444; color: #fff; padding: 8px; border-radius: 4px;">
                                <option value="" disabled selected>Select status...</option>
                                <?php foreach ($next_steps as $s): ?>
                                    <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="quick-note-container-seller" style="margin-bottom: 1rem; display: none;">
                            <label
                                style="font-size: 0.75rem; color: var(--color-accent); display: block; margin-bottom: 4px;">Quick
                                Templates:</label>
                            <div id="note-templates-seller" style="display: flex; flex-wrap: wrap; gap: 5px;">
                                <!-- Populated by JS -->
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="font-size: 0.75rem; color: #888; display: block; margin-bottom: 4px;">Audit Note
                                (Public):</label>
                            <textarea name="status_notes" id="status-notes-seller" required rows="2"
                                placeholder="e.g. Packing complete..."
                                style="width: 100%; background: #1a1a1a; border: 1px solid #444; color: #fff; padding: 8px; border-radius: 4px; font-size: 0.85rem;"></textarea>
                        </div>
                        <script>
                            const sellerTemplates = {
                                'Processing': [
                                    "Item confirmed and being packed.",
                                    "Preparing for courier pickup.",
                                    "Processing in our local cellar."
                                ],
                                'Shipped': [
                                    "Handed to courier. Tracking: ",
                                    "En route to sorting facility.",
                                    "Dispatched for delivery."
                                ],
                                'Canceled': [
                                    "Out of stock at seller location.",
                                    "Issue with item quality, order canceled.",
                                    "Canceled as per seller request."
                                ]
                            };

                            function updateNoteTemplatesSeller() {
                                const status = document.getElementById('status-select-seller').value;
                                const container = document.getElementById('quick-note-container-seller');
                                const templatesDiv = document.getElementById('note-templates-seller');
                                const textarea = document.getElementById('status-notes-seller');

                                templatesDiv.innerHTML = '';
                                if (sellerTemplates[status]) {
                                    container.style.display = 'block';
                                    sellerTemplates[status].forEach(t => {
                                        const btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.innerText = t;
                                        btn.style.cssText = 'font-size: 0.7rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #ccc; padding: 4px 8px; border-radius: 4px; cursor: pointer;';
                                        btn.onclick = () => {
                                            textarea.value = t;
                                            textarea.focus();
                                        };
                                        templatesDiv.appendChild(btn);
                                    });
                                } else {
                                    container.style.display = 'none';
                                }
                            }
                        </script>
                        <button type="submit" name="update_status" class="btn btn-primary"
                            style="width: 100%; font-size: 0.9rem; padding: 10px;">Update Status</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div class="glass-card" style="margin-bottom: 0;">
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
                                    $img = $item['image_path'] ?? '';
                                    $display_path = (strpos($img, 'uploads/') === 0) ? '../' . $img : '../uploads/' . $img;
                                    ?>
                                    <img src="<?php echo htmlspecialchars($display_path); ?>" class="product-thumb">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </td>
                                <td>₱<?php echo number_format($item['price_at_purchase'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="text-accent">
                                    ₱<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: bold; padding-top: 1.5rem;">Your
                                Share Total:</td>
                            <td
                                style="font-weight: bold; padding-top: 1.5rem; font-size: 1.2rem; color: var(--color-accent);">
                                ₱<?php echo number_format($seller_total, 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="glass-card">
                <h3 style="margin-bottom: 1rem;">Order Timeline</h3>
                <div style="position: relative; padding-left: 20px; border-left: 2px solid rgba(255,255,255,0.05);">
                    <?php foreach ($history as $h): ?>
                        <div style="position: relative; margin-bottom: 1.5rem;">
                            <div
                                style="position: absolute; left: -26px; top: 5px; width: 10px; height: 10px; border-radius: 50%; background: var(--color-accent); border: 2px solid #1a1a1a;">
                            </div>
                            <p
                                style="margin: 0; font-size: 0.85rem; font-weight: bold; color: #fff; text-transform: uppercase;">
                                <?php echo htmlspecialchars($h['status_to']); ?>
                            </p>
                            <p style="margin: 2px 0 5px 0; font-size: 0.7rem; color: #666;">
                                <?php echo date('M d, Y - h:i A', strtotime($h['changed_at'])); ?>
                            </p>
                            <?php if (!empty($h['notes'])): ?>
                                <div
                                    style="font-size: 0.8rem; color: #aaa; background: rgba(255,255,255,0.02); padding: 8px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.03);">
                                    <?php echo nl2br(htmlspecialchars($h['notes'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div style="position: relative;">
                        <div
                            style="position: absolute; left: -26px; top: 5px; width: 10px; height: 10px; border-radius: 50%; background: #444; border: 2px solid #1a1a1a;">
                        </div>
                        <p style="margin: 0; font-size: 0.85rem; color: #888; font-weight: bold;">ORDER PLACED</p>
                        <p style="margin: 2px 0 0 0; font-size: 0.7rem; color: #555;">
                            <?php echo date('M d, Y - h:i A', strtotime($order['order_date'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>