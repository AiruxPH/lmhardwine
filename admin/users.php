<?php
include 'auth.php';
include '../includes/db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action_id = (int) $_GET['id'];
    $act = $_GET['action'];

    if ($act === 'archive') {
        $stmt = $pdo->prepare("UPDATE users SET is_deleted = 1 WHERE id = ?");
        $stmt->execute([$action_id]);
    } elseif ($act === 'restore') {
        $stmt = $pdo->prepare("UPDATE users SET is_deleted = 0 WHERE id = ?");
        $stmt->execute([$action_id]);
    }
    // Refresh to clear params
    header("Location: users.php");
    exit;
}

// Base Query - Include is_deleted and deletion_requested
$sql = "SELECT u.*, cp.full_name, sp.brand_name 
        FROM users u 
        LEFT JOIN customer_profiles cp ON u.id = cp.user_id 
        LEFT JOIN seller_profiles sp ON u.id = sp.user_id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR cp.full_name LIKE ? OR sp.brand_name LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if ($role_filter) {
    $sql .= " AND u.role = ?";
    $params[] = $role_filter;
}

$sql .= " ORDER BY u.deletion_requested DESC, u.is_deleted ASC, u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .filter-input {
            background: #1a1a1a;
            border: 1px solid #444;
            color: white;
            padding: 10px;
            border-radius: 4px;
            flex: 1;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th,
        .user-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-table th {
            background: rgba(114, 14, 30, 0.2);
            color: var(--color-accent);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .role-customer {
            background: rgba(33, 150, 243, 0.15);
            color: #2196f3;
        }

        .role-seller {
            background: rgba(156, 39, 176, 0.15);
            color: #e91e63;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="page-header">
            <h1>User <span class="text-accent">Directory</span></h1>
            <a href="index.php" style="color: var(--color-text-muted);">← Back to Dashboard</a>
        </div>

        <form class="filters">
            <input type="text" name="search" class="filter-input" placeholder="Search by name, email, or brand..."
                value="<?php echo htmlspecialchars($search); ?>">
            <select name="role" class="filter-input" style="max-width: 200px;">
                <option value="">All Roles</option>
                <option value="customer" <?php echo $role_filter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                <option value="seller" <?php echo $role_filter === 'seller' ? 'selected' : ''; ?>>Seller</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $role_filter): ?>
                <a href="users.php" class="btn" style="background: transparent; border: 1px solid #444;">Clear</a>
            <?php endif; ?>
        </form>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Profile/Brand</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem; color: #666;">No users found
                                    matching
                                    your criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>#
                                        <?php echo $u['id']; ?>
                                    </td>
                                    <td>
                                        <div
                                            style="font-weight: bold; <?php echo $u['is_deleted'] ? 'color: #777; text-decoration: line-through;' : ''; ?>">
                                            <?php echo htmlspecialchars($u['username']); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #666;">
                                            <?php echo htmlspecialchars($u['full_name'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?php echo $u['role']; ?>">
                                            <?php echo $u['role']; ?>
                                        </span>
                                        <?php if ($u['is_deleted']): ?>
                                            <span
                                                style="font-size: 0.7rem; color: #f44336; margin-left: 5px; font-weight: bold;">[ARCHIVED]</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['role'] === 'seller'): ?>
                                            <span style="color: var(--color-accent); font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($u['brand_name'] ?? 'N/A'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #888; font-size: 0.8rem;">Personal Account</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #888;">
                                        <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                                    </td>
                                    <td>
                                        <a href="view_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm"
                                            style="margin-right: 5px;">View</a>

                                        <?php if ($u['is_deleted']): ?>
                                            <a href="?action=restore&id=<?php echo $u['id']; ?>" class="btn btn-sm"
                                                style="background: rgba(76, 175, 80, 0.1); color: #4caf50; border: 1px solid rgba(76, 175, 80, 0.3);">
                                                Restore
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=archive&id=<?php echo $u['id']; ?>" class="btn btn-sm"
                                                onclick="return confirm('Are you sure you want to archive this user? They will not be able to login.');"
                                                style="background: rgba(244, 67, 54, 0.1); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                                                Archive
                                            </a>
                                        <?php endif; ?>
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