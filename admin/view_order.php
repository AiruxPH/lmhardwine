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

// Fetch Status History
$stmt_hist = $pdo->prepare("SELECT * FROM order_history WHERE order_id = ? ORDER BY changed_at DESC");
$stmt_hist->execute([$order_id]);
$history = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $notes = trim($_POST['status_notes']);
        $old_status = $order['status'];

        // Define Allowed Transitions (State Machine)
        $allowed = [
            'Pending' => ['Processing', 'Canceled'],
            'Processing' => ['Shipped', 'Canceled'],
            'Shipped' => ['Delivered']
        ];

        $is_valid = false;
        if (isset($allowed[$old_status]) && in_array($new_status, $allowed[$old_status])) {
            $is_valid = true;
        }

        if ($new_status === $old_status) {
            $error = "Status is already $new_status.";
        } elseif (!$is_valid) {
            $error = "Invalid status transition from $old_status to $new_status.";
        } else {
            $pdo->beginTransaction();
            try {
                // 1. Update Order Status
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);

                // 2. Log to History
                $stmt_log = $pdo->prepare("INSERT INTO order_history (order_id, status_from, status_to, notes) VALUES (?, ?, ?, ?)");
                $stmt_log->execute([$order_id, $old_status, $new_status, $notes]);

                // 3. Stock Reversion if Canceled
                if ($new_status === 'Canceled') {
                    $stmt_items_list = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $stmt_items_list->execute([$order_id]);
                    $items_to_revert = $stmt_items_list->fetchAll();

                    foreach ($items_to_revert as $item) {
                        $stmt_stock = $pdo->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?");
                        $stmt_stock->execute([$item['quantity'], $item['product_id']]);
                    }
                }

                $pdo->commit();
                header("Location: view_order.php?id=$order_id&success=1");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to update: " . $e->getMessage();
            }
        }
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

        /* Timeline Styles */
        .timeline {
            margin-top: 2rem;
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background: rgba(255, 255, 255, 0.1);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--color-accent);
            border: 3px solid #1a1a1a;
        }

        .timeline-date {
            font-size: 0.8rem;
            color: var(--color-text-muted);
            margin-bottom: 4px;
        }

        .timeline-content {
            background: rgba(255, 255, 255, 0.03);
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .timeline-status {
            font-weight: bold;
            color: #fff;
            margin-bottom: 5px;
            display: block;
        }

        .timeline-notes {
            font-size: 0.9rem;
            color: #ccc;
            font-style: italic;
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
                <h3>Update Order State</h3>
                <?php
                $current_status = $order['status'];
                $is_final = ($current_status === 'Delivered' || $current_status === 'Canceled');

                // Define Allowed Transitions for UI
                $allowed_map = [
                    'Pending' => ['Processing', 'Canceled'],
                    'Processing' => ['Shipped', 'Canceled'],
                    'Shipped' => ['Delivered'],
                    'Delivered' => [],
                    'Canceled' => []
                ];
                $next_steps = $allowed_map[$current_status] ?? [];
                ?>

                <?php if ($is_final): ?>
                    <div
                        style="padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 8px; text-align: center; margin-top: 1rem;">
                        <span style="display: block; font-size: 1.5rem; margin-bottom: 5px;">🛑</span>
                        <p style="color: var(--color-text-muted); font-size: 0.9rem;">This order is in a <strong>final
                                state</strong> (<?php echo $current_status; ?>) and cannot be modified further.</p>
                    </div>
                <?php else: ?>
                    <form method="POST" style="margin-top: 1rem;">
                        <?php if (isset($error)): ?>
                            <div style="color: #f44336; font-size: 0.85rem; margin-bottom: 1rem;"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="font-size: 0.8rem; color: #888; display: block; margin-bottom: 5px;">Move Status
                                To:</label>
                            <select name="status" id="status-select" class="form-control" required
                                onchange="updateNoteTemplates()">
                                <option value="" disabled selected>Select next status...</option>
                                <?php foreach ($next_steps as $s): ?>
                                    <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="quick-note-container" style="margin-bottom: 1rem; display: none;">
                            <label
                                style="font-size: 0.75rem; color: var(--color-accent); display: block; margin-bottom: 5px;">Quick
                                Templates:</label>
                            <div id="note-templates" style="display: flex; flex-wrap: wrap; gap: 5px;">
                                <!-- Populated by JS -->
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="font-size: 0.8rem; color: #888; display: block; margin-bottom: 5px;">Notes / Paper
                                Trail (visible to customer):</label>
                            <textarea name="status_notes" id="status-notes" class="form-control" rows="2"
                                placeholder="e.g. Tracking number: 12345..." required></textarea>
                        </div>
                        <script>
                            const templates = {
                                'Processing': [
                                    "Order confirmed. Processing and packing now.",
                                    "Item is at the quality check station.",
                                    "Preparing for shipment."
                                ],
                                'Shipped': [
                                    "Handed over to the courier.",
                                    "In transit. Tracking: ",
                                    "Dispatched from our warehouse."
                                ],
                                'Delivered': [
                                    "Package successfully received by customer.",
                                    "Delivered to the specified address.",
                                    "Order completed."
                                ],
                                'Canceled': [
                                    "Canceled due to lack of stock.",
                                    "Canceled at customer's request.",
                                    "Unable to fulfill order."
                                ]
                            };

                            function updateNoteTemplates() {
                                const status = document.getElementById('status-select').value;
                                const container = document.getElementById('quick-note-container');
                                const templatesDiv = document.getElementById('note-templates');
                                const textarea = document.getElementById('status-notes');

                                templatesDiv.innerHTML = '';
                                if (templates[status]) {
                                    container.style.display = 'block';
                                    templates[status].forEach(t => {
                                        const btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.innerText = t;
                                        btn.style.cssText = 'font-size: 0.7rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #ccc; padding: 4px 8px; border-radius: 4px; cursor: pointer; transition: 0.2s;';
                                        btn.onmouseover = () => btn.style.background = 'rgba(255,255,255,0.1)';
                                        btn.onmouseout = () => btn.style.background = 'rgba(255,255,255,0.05)';
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
                        <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">Commit
                            Status Change</button>
                        <p style="font-size: 0.75rem; color: #666; margin-top: 10px; text-align: center;">Only valid
                            transitions are shown. Logs are public.</p>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
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
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td>₱<?php echo number_format($item['price_at_purchase'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>₱<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right;">Grand Total:</td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="glass-card">
                <h3>Order Timeline</h3>
                <div class="timeline">
                    <?php if (empty($history)): ?>
                        <p style="color: #555; font-size: 0.9rem;">No history recorded yet.</p>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('M d, Y - h:i A', strtotime($h['changed_at'])); ?>
                                </div>
                                <div class="timeline-content">
                                    <span class="timeline-status"><?php echo htmlspecialchars($h['status_to']); ?></span>
                                    <?php if (!empty($h['notes'])): ?>
                                        <div class="timeline-notes"><?php echo nl2br(htmlspecialchars($h['notes'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="timeline-item" style="opacity: 0.6;">
                        <div class="timeline-date">
                            <?php echo date('M d, Y - h:i A', strtotime($order['order_date'])); ?>
                        </div>
                        <div class="timeline-content">
                            <span class="timeline-status">Order Placed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>