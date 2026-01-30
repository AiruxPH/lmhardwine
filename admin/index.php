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
<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
    <header style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <p
                style="color: var(--color-accent); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; margin-bottom: 5px; font-weight: 600;">
                Executive Control</p>
            <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Admin <span
                    class="text-accent">Portal</span></h1>
        </div>
        <a href="../index.php" style="color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem;">←
            Exit to Frontend</a>
    </header>

    <div class="stats-grid">
        <div class="glass-card stat-card" style="border-top: 3px solid var(--color-accent);">
            <div class="stat-value">₱<?php echo number_format($total_sales, 2); ?></div>
            <div class="stat-label">Gross Revenue</div>
        </div>

        <div class="glass-card stat-card" style="border-top: 3px solid #fff;">
            <div class="stat-value"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Volume</div>
        </div>

        <div class="glass-card stat-card" style="border-top: 3px solid #d4af37;">
            <div class="stat-value" style="color: #d4af37;"><?php echo $pending_orders; ?></div>
            <div class="stat-label">Action Required</div>
        </div>

        <div class="glass-card stat-card" style="border-top: 3px solid #2196f3;">
            <div class="stat-value" style="color: #2196f3;"><?php echo $total_users; ?></div>
            <div class="stat-label">Active Users</div>
        </div>
    </div>

    <div class="dashboard-grid">

        <!-- Management Section -->
        <div style="width: 100%;">
            <h3
                style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-bottom: 1.5rem; padding-left: 0.5rem;">
                Management Center</h3>

            <div class="management-center">
                <a href="users.php" class="action-card glass-card">
                    <div class="action-icon">👥</div>
                    <div>
                        <div style="font-weight: 600;">User Directory</div>
                        <div style="font-size: 0.75rem; color: #666;">Manage access & profiles</div>
                    </div>
                </a>

                <a href="products.php" class="action-card glass-card">
                    <div class="action-icon">🍷</div>
                    <div>
                        <div style="font-weight: 600;">Cellar Inventory</div>
                        <div style="font-size: 0.75rem; color: #666;">Global product controls</div>
                    </div>
                </a>

                <a href="add_product.php" class="action-card glass-card">
                    <div class="action-icon">➕</div>
                    <div>
                        <div style="font-weight: 600;">New Selection</div>
                        <div style="font-size: 0.75rem; color: #666;">Add bottles to catalog</div>
                    </div>
                </a>

                <a href="add_seller.php" class="action-card glass-card">
                    <div class="action-icon">🏢</div>
                    <div>
                        <div style="font-weight: 600;">Partner Onboarding</div>
                        <div style="font-size: 0.75rem; color: #666;">Register new merchants</div>
                    </div>
                </a>

                <a href="messages.php" class="action-card glass-card">
                    <div class="action-icon">✉️</div>
                    <div>
                        <div style="font-weight: 600;">Communications</div>
                        <div style="font-size: 0.75rem; color: #666;">Customer inquiries</div>
                    </div>
                </a>

                <a href="database.php" class="action-card glass-card">
                    <div class="action-icon">🗄️</div>
                    <div>
                        <div style="font-weight: 600;">Database Explorer</div>
                        <div style="font-size: 0.75rem; color: #666;">View & search raw tables</div>
                    </div>
                </a>

                <?php if (isSuperAdmin()): ?>
                    <a href="manage_admins.php" class="action-card glass-card">
                        <div class="action-icon">🔑</div>
                        <div>
                            <div style="font-weight: 600;">Access Control</div>
                            <div style="font-size: 0.75rem; color: #666;">Manage administrators</div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="glass-card" style="padding: 1.5rem;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1rem;">
                <h2 style="font-size: 1.25rem;">Recent Order Activity</h2>
                <span style="font-size: 0.8rem; color: #666;">Showing lastest transactions</span>
            </div>

            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Revenue</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 3rem; color: #555;">No recent data
                                found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_slice($orders, 0, 10) as $order): ?>
                            <tr>
                                <td style="font-weight: 600;">#<?php echo $order['id']; ?></td>
                                <td>
                                    <div style="color: #fff;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <div style="font-size: 0.75rem; color: #555;">
                                        <?php echo date('M d, H:i', strtotime($order['order_date'])); ?>
                                    </div>
                                </td>
                                <td style="color: var(--color-accent); font-weight: 600;">
                                    ₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm"
                                        style="background: transparent; border: 1px solid #444;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>

</html>