<?php
include '../includes/db.php';
session_start();
header('Content-Type: application/json');

// Check if user is logged in (Seller/Admin)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$name = $_GET['name'] ?? '';

if (empty($name)) {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    // Check for exact name match (case-insensitive usually by default in MySQL, but we can be explicit if needed)
    $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ? AND is_deleted = 0 LIMIT 1");
    $stmt->execute([trim($name)]);

    if ($stmt->fetch()) {
        echo json_encode(['exists' => true, 'message' => 'This product name is already in use.']);
    } else {
        echo json_encode(['exists' => false]);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>