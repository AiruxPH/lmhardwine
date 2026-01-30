<?php
include 'includes/db.php';

try {
    // 1. Add role column if it doesn't exist
    $pdo->exec("ALTER TABLE admins ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'admin' AFTER password_hash");
    echo "Column 'role' added or already exists.<br>";

    // 2. Set all existing admins to super_admin initially to avoid lockout
    $pdo->exec("UPDATE admins SET role = 'super_admin'");
    echo "Existing admins promoted to 'super_admin'.<br>";

    echo "Migration completed successfully.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>