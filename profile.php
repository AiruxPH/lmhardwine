<?php
include 'includes/db.php';
session_start();

// Gatekeeping: Redirect Sellers
if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller') {
    header('Location: seller/profile.php');
    exit;
}


// Ensure user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Validation
    if (!preg_match("/^[a-zA-Z\s'\-\.]+$/", $full_name)) {
        $error = "Invalid Full Name. Only letters, spaces, hyphens, apostrophes, and periods allowed.";
    } elseif (!empty($phone) && !preg_match("/^[0-9\-\+\s]+$/", $phone)) {
        $error = "Invalid phone number format.";
    } else {
        try {
            // Update customer_profiles
            // Check if profile exists first (it should from registration, but good to be safe)
            $stmt_check = $pdo->prepare("SELECT id FROM customer_profiles WHERE user_id = ?");
            $stmt_check->execute([$user_id]);

            if ($stmt_check->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE customer_profiles SET full_name = ?, phone_number = ?, default_shipping_address = ? WHERE user_id = ?");
                $stmt->execute([$full_name, $phone, $address, $user_id]);
            } else {
                // Create if missing (edge case)
                $stmt = $pdo->prepare("INSERT INTO customer_profiles (user_id, full_name, phone_number, default_shipping_address) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $full_name, $phone, $address]);
            }

            $success = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}

// Fetch Current User Data
try {
    $stmt = $pdo->prepare("
        SELECT u.username, u.email, cp.full_name, cp.phone_number, cp.default_shipping_address 
        FROM users u 
        LEFT JOIN customer_profiles cp ON u.id = cp.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Should not happen if logged in
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error loading profile: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - LM Hard Wine</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            padding-top: 80px;
            /* Space for fixed header */
        }

        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-header {
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="profile-container">
        <div class="glass-card">
            <div class="profile-header">
                <h2>My Profile</h2>
                <p style="color: var(--color-text-muted);">Manage your account details and shipping address.</p>
            </div>

            <?php if (!empty($success)) { ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php } ?>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <form method="POST" autocomplete="off">
                <div class="info-grid">
                    <!-- Read-Only Information -->
                    <div>
                        <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: var(--color-accent);">Account Info
                        </h3>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                style="opacity: 0.7; cursor: not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                                style="opacity: 0.7; cursor: not-allowed;">
                        </div>
                    </div>

                    <!-- Editable Information -->
                    <div>
                        <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: var(--color-accent);">Personal
                            Details</h3>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label>Default Shipping Address</label>
                    <textarea name="address" class="form-control" rows="3"
                        placeholder="Enter your full shipping address..."><?php echo htmlspecialchars($user['default_shipping_address'] ?? ''); ?></textarea>
                    <p style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 5px;">
                        This address will be automatically used when you checkout.</p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
                    <button type="button" class="btn" onclick="openPasswordModal()"
                        style="background: rgba(255, 255, 255, 0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">Change
                        Password</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal"
        style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.8); backdrop-filter: blur(5px);">
        <div class="glass-card"
            style="margin: 10% auto; padding: 2rem; width: 90%; max-width: 400px; position: relative;">
            <h3 style="margin-bottom: 1.5rem; text-align: center;">Change Password</h3>

            <div id="pwd-msg"
                style="display: none; padding: 10px; margin-bottom: 10px; border-radius: 4px; text-align: center;">
            </div>

            <form id="passwordForm" onsubmit="submitPasswordChange(event)">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" id="current_password" class="form-control" required>
                    <div style="text-align: right; margin-top: 5px;">
                        <a href="forgot_password.php"
                            style="color: #bbb; font-size: 0.8rem; text-decoration: none;">Forgot Password?</a>
                    </div>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="new_password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="confirm_password" class="form-control" required minlength="6">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="closePasswordModal()" class="btn"
                        style="flex: 1; background: transparent; border: 1px solid #444; color: #ccc;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Input Gatekeeping
        document.querySelector('input[name="full_name"]').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^a-zA-Z\s'\-\.]/g, '');
        });

        document.querySelector('input[name="phone"]').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9\+\-\s]/g, '');
        });

        // Modal Logic
        const modal = document.getElementById('passwordModal');
        const msgBox = document.getElementById('pwd-msg');

        function openPasswordModal() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            msgBox.style.display = 'none';
        }

        function closePasswordModal() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('passwordForm').reset();
        }

        async function submitPasswordChange(e) {
            e.preventDefault();
            const current = document.getElementById('current_password').value;
            const newPwd = document.getElementById('new_password').value;
            const confirmPwd = document.getElementById('confirm_password').value;

            if (newPwd !== confirmPwd) {
                showMsg('Passwords do not match.', 'error');
                return;
            }

            try {
                const res = await fetch('api/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        current_password: current,
                        new_password: newPwd,
                        confirm_password: confirmPwd
                    })
                });

                const data = await res.json();

                if (data.success) {
                    showMsg('Password updated successfully!', 'success');
                    setTimeout(() => {
                        closePasswordModal();
                    }, 1500);
                } else {
                    showMsg(data.error || 'Failed to update password.', 'error');
                }
            } catch (err) {
                showMsg('An error occurred.', 'error');
            }
        }

        function showMsg(text, type) {
            msgBox.style.display = 'block';
            msgBox.innerText = text;
            if (type === 'success') {
                msgBox.style.background = 'rgba(76, 175, 80, 0.2)';
                msgBox.style.color = '#4caf50';
            } else {
                msgBox.style.background = 'rgba(244, 67, 54, 0.2)';
                msgBox.style.color = '#f44336';
            }
        }

        // Close on click outside
        window.onclick = function (event) {
            if (event.target == modal) {
                closePasswordModal();
            }
        }
    </script>
</body>

</html>