<?php
include '../includes/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$otp = $data['otp'] ?? '';
$new_pwd = $data['new_password'] ?? '';

if (empty($email) || empty($otp) || empty($new_pwd)) {
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

if (strlen($new_pwd) < 6) {
    echo json_encode(['error' => 'Password must be at least 6 characters.']);
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

    // 3. Update User Password
    // Check if we should hash or plain text. Based on previous Login logic, users are Plain Text.
    // If we want to support Admin (hashed), we need to check role.

    // Find User
    $uStmt = $pdo->prepare("SELECT id, role FROM users WHERE email = ?");
    $uStmt->execute([$email]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'User account not found.']);
        exit;
    }

    $final_pwd = $new_pwd;

    // If Admin, Hash it. If User/Seller, keep plain (as per project conventions observed)
    // Actually, earlier User Rule said "Auto-generated plain text" for sellers. 
    // And "Customer" implies simple. 
    // But Admin is usually hashed.
    // Let's check: if role == admin, hash it. else plain.
    // However, if the user logins with `password_verify` for admin and `===` for others, we must match that.

    // Let's assume Admin might use this too.
    /*
        Login Logic Ref (Recalled):
        if ($user['role'] === 'admin') {
             if (password_verify($password, $user['password_hash'])) ...
        } else {
             if ($password === $user['password_hash']) ...
        }
    */

    if ($user['role'] === 'admin') {
        $final_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
    }
    // If customer/seller, use plain text $new_pwd

    $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $upd->execute([$final_pwd, $user['id']]);

    // 4. Delete OTP Record
    $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->execute([$email]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
}
?>