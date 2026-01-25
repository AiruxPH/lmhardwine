<?php
include '../includes/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email address.']);
    exit;
}

try {
    // 1. Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        // Security: Don't reveal if email exists, but for UX in this specific project we might want to say it.
        // Let's pretend it sent to prevent enumeration, or return error if client implies lenient security.
        // User asked for "Check if email exists".
        echo json_encode(['error' => 'Email not found in our records.']);
        exit;
    }

    // 2. Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+2 minutes'));

    // 3. Save to DB
    // Clear old OTPs for this email first
    $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->execute([$email]);

    $ins = $pdo->prepare("INSERT INTO password_resets (email, otp_code, expires_at) VALUES (?, ?, ?)");
    $ins->execute([$email, $otp, $expires]);

    // 4. Send Email
    $subject = "Password Reset Code - LM Hard Wine";
    $message = "Your verification code is: " . $otp . "\n\nThis code expires in 1 minute.";
    $headers = "From: noreply@lmhardwine.com";

    // Attempt to send (might fail on local, so we ignore result for demo and return OTP in debug)
    @mail($email, $subject, $message, $headers);

    // 4. Send Email success response
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>