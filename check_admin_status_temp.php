<?php
include 'includes/db.php';

echo "Database Connection: ";
if ($pdo) {
    echo "OK\n";
} else {
    echo "FAILED\n";
    exit;
}

echo "Checking 'admins' table...\n";
try {
    $stmt = $pdo->query("SELECT count(*) FROM admins");
    $count = $stmt->fetchColumn();
    echo "Found 'admins' table. Admin count: $count\n";

    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM admins LIMIT 1");
        $admin = $stmt->fetch();
        echo "Sample Admin User: " . $admin['username'] . "\n";
        // Verify default password if possible (assuming we know it might be Admin123!)
        if (password_verify('Admin123!', $admin['password_hash'])) {
            echo "Note: Password 'Admin123!' is valid for this user.\n";
        } else {
            echo "Note: Password 'Admin123!' is NOT valid for this user.\n";
        }
    } else {
        echo "WARNING: No admin users found!\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    // Check if table missing
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        echo "Table 'admins' seems to be missing.\n";
    }
}
?>