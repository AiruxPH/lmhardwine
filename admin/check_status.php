<?php
include '../includes/db.php';

$status = [];

// 1. Check Database Connection
if ($pdo) {
    $status[] = "<span style='color:green'>✅ Database Connected</span>";
} else {
    $status[] = "<span style='color:red'>❌ Database Connection Failed</span>";
}

// 2. Check Admin Table & User
try {
    $stmt = $pdo->query("SELECT count(*) FROM admins");
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $status[] = "<span style='color:green'>✅ Admin User Found (Count: $count)</span>";
        // Check if we can verify the default password hash
        $stmt = $pdo->query("SELECT * FROM admins LIMIT 1");
        $admin = $stmt->fetch();
        if (password_verify('Admin123!', $admin['password_hash'])) {
             $status[] = "<span style='color:green'>✅ Default password 'Admin123!' is valid for user '{$admin['username']}'</span>";
        } else {
             $status[] = "<span style='color:orange'>ℹ️ Admin found, but 'Admin123!' is not their password.</span>";
        }
    } else {
        $status[] = "<span style='color:red'>❌ No Admin User Found! Login will fail.</span>";
    }
} catch (Exception $e) {
    $status[] = "<span style='color:red'>❌ Error checking admins: " . $e->getMessage() . "</span>";
}

// 3. Check Stock Column
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock_qty'");
    if ($stmt->fetch()) {
        $status[] = "<span style='color:green'>✅ 'stock_qty' column exists in products table</span>";
    } else {
        $status[] = "<span style='color:red'>❌ 'stock_qty' column MISSING in products table!</span>";
    }
} catch (Exception $e) {
    $status[] = "<span style='color:red'>❌ Error checking products: " . $e->getMessage() . "</span>";
}

?>
<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; padding: 2rem; background: #222; color: #eee;">
    <h1>System Status Check</h1>
    <div style="background: #333; padding: 1rem; border-radius: 8px;">
        <?php foreach($status as $s): ?>
            <p><?php echo $s; ?></p>
        <?php endforeach; ?>
    </div>
    <br>
    <a href="login.php" style="color: #4CAF50">Go to Login</a>
</body>
</html>
