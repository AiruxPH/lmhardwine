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
            max-width: 1300px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 80px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.2rem;
        }

        .stat-label {
            color: var(--color-text-muted);
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .management-center {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .action-card {
            padding: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .action-card:hover {
            background: rgba(114, 14, 30, 0.1);
            border-color: var(--color-accent);
            transform: translateX(5px);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            font-size: 1.2rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            color: var(--color-text-main);
        }

        .orders-table th,
        .orders-table td {
            text-align: left;
            padding: 1.2rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .orders-table th {
            color: #666;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-Pending { background: rgba(212, 175, 55, 0.1); color: #d4af37; }
        .status-Delivered { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
        .status-Processing { background: rgba(33, 150, 243, 0.1); color: #2196f3; }
        .status-Shipped { background: rgba(156, 39, 176, 0.1); color: #9c27b0; }
        .status-Canceled { background: rgba(244, 67, 54, 0.1); color: #f44336; }

        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <header style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <p style="color: var(--color-accent); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; margin-bottom: 5px; font-weight: 600;">Executive Control</p>
                <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Admin <span class="text-accent">Portal</span></h1>
            </div>
            <a href="../index.php" style="color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem;">ΓåÉ Exit to Frontend</a>
        </header>

        <div class="stats-grid">
            <div class="glass-card stat-card" style="border-top: 3px solid var(--color-accent);">
                <div class="stat-value">Γé▒<?php echo number_format($total_sales, 2); ?></div>
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
            <!-- Main Content: Recent Orders -->
            <div class="glass-card" style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1rem;">
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
                            <tr><td colspan="5" class="text-center" style="padding: 3rem; color: #555;">No recent data found.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($orders, 0, 10) as $order): ?>
                                <tr>
                                    <td style="font-weight: 600;">#<?php echo $order['id']; ?></td>
                                    <td>
                                        <div style="color: #fff;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        <div style="font-size: 0.75rem; color: #555;"><?php echo date('M d, H:i', strtotime($order['order_date'])); ?></div>
                                    </td>
                                    <td style="color: var(--color-accent); font-weight: 600;">Γé▒<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm" style="background: transparent; border: 1px solid #444;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sidebar: Management Center -->
            <div class="management-column">
                <h3 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-bottom: 1.5rem; padding-left: 0.5rem;">Management Center</h3>
                
                <div class="management-center">
                    <a href="users.php" class="action-card glass-card">
                        <div class="action-icon">≡ƒæÑ</div>
                        <div>
                            <div style="font-weight: 600;">User Directory</div>
                            <div style="font-size: 0.75rem; color: #666;">Manage access & profiles</div>
                        </div>
                    </a>

                    <a href="products.php" class="action-card glass-card">
                        <div class="action-icon">≡ƒì╖</div>
                        <div>
                            <div style="font-weight: 600;">Cellar Inventory</div>
                            <div style="font-size: 0.75rem; color: #666;">Global product controls</div>
                        </div>
                    </a>

                    <a href="add_product.php" class="action-card glass-card">
                        <div class="action-icon">Γ₧ò</div>
                        <div>
                            <div style="font-weight: 600;">New Selection</div>
                            <div style="font-size: 0.75rem; color: #666;">Add bottles to catalog</div>
                        </div>
                    </a>

                    <a href="add_seller.php" class="action-card glass-card">
                        <div class="action-icon">≡ƒÅó</div>
                        <div>
                            <div style="font-weight: 600;">Partner Onboarding</div>
                            <div style="font-size: 0.75rem; color: #666;">Register new merchants</div>
                        </div>
                    </a>

                    <a href="messages.php" class="action-card glass-card">
                        <div class="action-icon">Γ£ë∩╕Å</div>
                        <div>
                            <div style="font-weight: 600;">Communications</div>
                            <div style="font-size: 0.75rem; color: #666;">Customer inquiries</div>
                        </div>
                    </a>

                    <a href="database.php" class="action-card glass-card">
                        <div class="action-icon">≡ƒùä∩╕Å</div>
                        <div>
                            <div style="font-weight: 600;">Database Explorer</div>
                            <div style="font-size: 0.75rem; color: #666;">View & search raw tables</div>
                        </div>
                    </a>

                    <?php if (isSuperAdmin()): ?>
                    <a href="manage_admins.php" class="action-card glass-card">
                        <div class="action-icon">≡ƒöæ</div>
                        <div>
                            <div style="font-weight: 600;">Access Control</div>
                            <div style="font-size: 0.75rem; color: #666;">Manage administrators</div>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
