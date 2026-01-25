<?php
include '../includes/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$otp = $data['otp'] ?? '';

if (empty($email) || empty($otp)) {
    echo json_encode(['error' => 'Please enter the verification code.']);
    exit;
}

try {
    // 1. Verify OTP
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND otp_code = ?");
    $stmt->execute([$email, $otp]);
    $resetRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resetRecord) {
        echo json_encode(['error' => 'Invalid verification code.']);
        exit;
    }

    // 2. Check Expiry
    if (strtotime($resetRecord['expires_at']) < time()) {
        echo json_encode(['error' => 'Code has expired. Please request a new one.']);
        exit;
    }

    // 3. Extend Expiry
    // Give them 5 minutes to type the new password
    $newExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $upd = $pdo->prepare("UPDATE password_resets SET expires_at = ? WHERE id = ?");
    $upd->execute([$newExpiry, $resetRecord['id']]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
}
?>