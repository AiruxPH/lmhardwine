<?php
include 'auth.php';
include '../includes/db.php';

// Restrict this page to Super Admins ONLY
restrictToSuperAdmin();

$message = '';
$error = '';

// Handle Add Admin
if (isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password_hash, $role]);
            $message = "Admin account created successfully!";
        } catch (PDOException $e) {
            $error = "Failed to create account: " . $e->getMessage();
        }
    }
}

// Handle Delete Admin
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id == $_SESSION['admin_id']) {
        $error = "You cannot delete your own account.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Admin account deleted successfully.";
        } catch (PDOException $e) {
            $error = "Failed to delete account: " . $e->getMessage();
        }
    }
}

// Fetch all admins
$stmt = $pdo->query("SELECT id, username, role FROM admins ORDER BY id ASC");
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Admin Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 80px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 4px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .admin-table th {
            color: var(--color-accent);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-super {
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
        }

        .badge-admin {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 1px solid #f44336;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <header style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <p
                    style="color: var(--color-accent); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; margin-bottom: 5px; font-weight: 600;">
                    System Access Control</p>
                <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Manage <span
                        class="text-accent">Administrators</span></h1>
            </div>
            <a href="index.php" style="color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem;">← Back
                to Dashboard</a>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-grid">
            <!-- Add Admin Form -->
            <div class="glass-card" style="padding: 1.5rem;">
                <h2 style="font-size: 1.2rem; margin-bottom: 1.5rem;">Add New Account</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="Pick a username">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required
                            placeholder="Minimum 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="admin">Regular Admin</option>
                            <option value="super_admin">Super Admin (Full Control)</option>
                        </select>
                    </div>
                    <button type="submit" name="add_admin" class="btn btn-primary" style="width: 100%;">Create
                        Account</button>
                </form>
            </div>

            <!-- Admins List -->
            <div class="glass-card" style="padding: 1.5rem;">
                <h2 style="font-size: 1.2rem; margin-bottom: 1.5rem;">Active Administrators</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td style="font-weight: 600;">
                                    <?php echo htmlspecialchars($admin['username']); ?>
                                </td>
                                <td>
                                    <span
                                        class="badge <?php echo $admin['role'] === 'super_admin' ? 'badge-super' : 'badge-admin'; ?>">
                                        <?php echo str_replace('_', ' ', $admin['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                        <a href="?delete=<?php echo $admin['id']; ?>"
                                            onclick="return confirm('Are you sure you want to remove this administrator?')"
                                            style="color: #f44336; text-decoration: none; font-size: 0.8rem;">Delete</a>
                                    <?php else: ?>
                                        <span style="color: #666; font-size: 0.8rem;">Its You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>