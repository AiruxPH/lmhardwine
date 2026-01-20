<?php
include '../includes/db.php';

// Change this to whatever username/password you want
$new_user = 'admin';
$new_pass = 'Admin123!';

// This scrambles the password so it looks like gibberish in the database
$hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$new_user, $hashed_pass]);
    echo "Success! User '$new_user' created. You can now delete this file.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>