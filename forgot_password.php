<?php
session_start();

// Check if Admin is logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/index.php');
    exit;
}

// Check if User (Seller/Customer) is logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller') {
        header('Location: seller/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | LM Hard Wine</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #0a0a0a;
            color: #fff;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .reset-container {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 0.5rem;
            color: #d4af37;
        }

        p {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .form-group {
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #d4af37;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4af37 0%, #b4932a 100%);
            color: #000;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-primary:disabled {
            background: #444;
            color: #888;
            cursor: not-allowed;
            transform: none;
        }

        .btn-link {
            background: none;
            border: none;
            color: #d4af37;
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.9rem;
            padding: 0;
        }

        .btn-link:disabled {
            color: #666;
            cursor: default;
            text-decoration: none;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: none;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }

        .step-hidden {
            display: none;
        }
    </style>
</head>

<body>

    <div class="reset-container">
        <!-- HEADER -->
        <h2 id="page-title">Reset Password</h2>
        <p id="page-desc">Enter your email address to receive a verification code.</p>

        <!-- ALERTS -->
        <div id="alert-box" class="alert"></div>

        <!-- STEP 1: EMAIL -->
        <form id="emailForm" onsubmit="sendOTP(event)">
            <div class="form-group">
                <input type="email" id="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <button type="submit" id="btn-send" class="btn-primary">Send Verification Code</button>
            <div style="margin-top: 1.5rem;">
                <a href="login.php" style="color: #888; text-decoration: none; font-size: 0.9rem;">Back to Login</a>
            </div>
        </form>

        <!-- STEP 2: VERIFY OTP -->
        <form id="otpForm" class="step-hidden" onsubmit="validateOTP(event)">
            <p style="color: #999; margin-bottom: 1.5rem;">Please enter the 6-digit code sent to your email.</p>
            <div class="form-group">
                <input type="text" id="otp_code" class="form-control" placeholder="000000" maxlength="6" required
                    style="letter-spacing: 8px; text-align: center; font-size: 1.5rem; font-weight: bold;">
            </div>

            <button type="submit" id="btn-verify" class="btn-primary">Verify Code</button>

            <div style="margin-top: 1.5rem;">
                <button type="button" id="btn-resend" class="btn-link" onclick="resendOTP()">Resend Code</button>
                <span id="timer" style="color: #666; margin-left: 5px;"></span>
            </div>
            <div style="margin-top: 1rem;">
                <button type="button" class="btn-link" onclick="location.reload()"
                    style="color: #888; font-size: 0.8rem;">Change Email</button>
            </div>
        </form>

        <!-- STEP 3: RESET PASSWORD -->
        <form id="resetForm" class="step-hidden" onsubmit="verifyAndReset(event)">
            <p style="color: #4caf50; margin-bottom: 1.5rem;">Code verified! Set your new password.</p>
            <div class="form-group">
                <input type="password" id="new_password" class="form-control" placeholder="New Password" required
                    minlength="6">
            </div>

            <div class="form-group">
                <input type="password" id="confirm_password" class="form-control" placeholder="Confirm Password"
                    required minlength="6">
            </div>

            <button type="submit" id="btn-reset" class="btn-primary">Reset Password</button>
        </form>
    </div>

    <script>
        const emailForm = document.getElementById('emailForm');
        const otpForm = document.getElementById('otpForm');
        const resetForm = document.getElementById('resetForm');
        const alertBox = document.getElementById('alert-box');
        const btnSend = document.getElementById('btn-send');
        const btnVerify = document.getElementById('btn-verify');
        const btnResend = document.getElementById('btn-resend');
        const timerSpan = document.getElementById('timer');

        let userEmail = '';
        let countdown;

        function showAlert(msg, type) {
            alertBox.innerText = msg;
            alertBox.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-error');
            alertBox.style.display = 'block';
        }

        async function sendOTP(e) {
            e.preventDefault();
            userEmail = document.getElementById('email').value.trim();
            btnSend.disabled = true;
            btnSend.innerText = 'Sending...';
            alertBox.style.display = 'none';

            try {
                const res = await fetch('api/send_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: userEmail })
                });
                const data = await res.json();

                if (data.success) {
                    showAlert('Code sent to ' + userEmail, 'success');
                    // Switch to Step 2
                    emailForm.classList.add('step-hidden');
                    otpForm.classList.remove('step-hidden');
                    document.title = "Verify Code | LM Hard Wine";
                    document.getElementById('page-title').innerText = "Verification";
                    document.getElementById('page-desc').style.display = 'none';
                    startTimer(120); // 2 Minutes
                } else {
                    showAlert(data.error || 'Failed to send OTP.', 'error');
                }
            } catch (err) {
                showAlert('Network error occurred.', 'error');
            } finally {
                btnSend.disabled = false;
                btnSend.innerText = 'Send Verification Code';
            }
        }

        async function validateOTP(e) {
            e.preventDefault();
            const otp = document.getElementById('otp_code').value.trim();
            btnVerify.disabled = true;
            btnVerify.innerText = 'Verifying...';

            try {
                const res = await fetch('api/validate_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: userEmail, otp: otp })
                });
                const data = await res.json();

                if (data.success) {
                    // Switch to Step 3
                    otpForm.classList.add('step-hidden');
                    resetForm.classList.remove('step-hidden');
                    document.getElementById('page-title').innerText = "Create Password";
                    alertBox.style.display = 'none';
                    clearInterval(countdown);
                } else {
                    showAlert(data.error || 'Invalid code.', 'error');
                    btnVerify.disabled = false;
                    btnVerify.innerText = 'Verify Code';
                }
            } catch (err) {
                showAlert('An error occurred.', 'error');
                btnVerify.disabled = false;
                btnVerify.innerText = 'Verify Code';
            }
        }

        async function verifyAndReset(e) {
            e.preventDefault();
            const otp = document.getElementById('otp_code').value.trim();
            const newPwd = document.getElementById('new_password').value;
            const confirmPwd = document.getElementById('confirm_password').value;
            const btnReset = document.getElementById('btn-reset');

            if (newPwd !== confirmPwd) {
                showAlert('Passwords do not match.', 'error');
                return;
            }

            btnReset.disabled = true;
            btnReset.innerText = 'Resetting...';

            try {
                const res = await fetch('api/verify_reset.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: userEmail,
                        otp: otp,
                        new_password: newPwd
                    })
                });
                const data = await res.json();

                if (data.success) {
                    showAlert('Password reset successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showAlert(data.error || 'Session expired. Please try again.', 'error');
                    btnReset.disabled = false;
                    btnReset.innerText = 'Reset Password';
                }
            } catch (err) {
                showAlert('An error occurred.', 'error');
                btnReset.disabled = false;
                btnReset.innerText = 'Reset Password';
            }
        }

        function resendOTP() {
            if (btnResend.disabled) return;
            btnResend.disabled = true;
            btnResend.innerText = 'Sending...';

            fetch('api/send_otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: userEmail })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert('New code sent!', 'success');
                        startTimer(120); // 2 Minutes
                    } else {
                        showAlert(data.error || 'Failed to resend.', 'error');
                        btnResend.disabled = false;
                        btnResend.innerText = 'Resend Code';
                    }
                })
                .catch(() => {
                    showAlert('Network error.', 'error');
                    btnResend.disabled = false;
                    btnResend.innerText = 'Resend Code';
                });
        }

        function startTimer(seconds) {
            clearInterval(countdown);
            btnResend.disabled = true;
            let left = seconds;
            updateTimerText(left);

            countdown = setInterval(() => {
                left--;
                updateTimerText(left);
                if (left <= 0) {
                    clearInterval(countdown);
                    btnResend.disabled = false;
                    btnResend.innerText = 'Resend Code';
                    timerSpan.innerText = '';
                }
            }, 1000);
        }

        function updateTimerText(sec) {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            btnResend.innerText = `Resend in ${m}:${s < 10 ? '0' : ''}${s}`;
        }
    </script>
</body>

</html>