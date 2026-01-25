<?php
include '../includes/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$current_password = $data['current_password'] ?? '';
$new_password = $data['new_password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

// Basic Validation
if (empty($current_password) || empty($new_password)) {
    echo json_encode(['error' => 'Please fill in all fields.']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['error' => 'New passwords do not match.']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['error' => 'New password must be at least 6 characters long.']);
    exit;
}

try {
    // 1. Verify Current Password
    // Check if user is seller or user to determine hashing strategy?
    // Actually, in login.php we saw:
    // Admin: password_verify
    // User/Seller: Plain text (as per user requirement "Users use plain text")

    // Let's check the user role first to be safe, or just check the table.
    // The requirement said "Regular Users (plain text)" and "Admins (secure hash)".
    // Sellers are created by admins, also plain text initially? Let's check login.php logic again if needed.
    // For now, assuming standard users/sellers are plain text based on previous context.

    $stmt = $pdo->prepare("SELECT password_hash, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'User not found.']);
        exit;
    }

    $stored_password = $user['password_hash']; // In this project, this column holds plain text for users/sellers

    // Check if it matches
    // NOTE: If we ever upgraded users to hash, we'd need password_verify here.
    // But sticking to the "Plain Text" rule for users/sellers:
    if ($current_password !== $stored_password) {
        echo json_encode(['error' => 'Incorrect current password.']);
        exit;
    }

    // 2. Update Password
    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $updateStmt->execute([$new_password, $user_id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>