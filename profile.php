<?php
include 'includes/db.php';
session_start();

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

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

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
                                value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label>Default Shipping Address</label>
                    <textarea name="address" class="form-control" rows="3"
                        placeholder="Enter your full shipping address..."><?php echo htmlspecialchars($user['default_shipping_address']); ?></textarea>
                    <p style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 5px;">
                        This address will be automatically used when you checkout.</p>
                </div>

                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Input Gatekeeping
        document.querySelector('input[name="full_name"]').addEventListener('input', function (e) {
            // Allow letters, spaces, hyphens, apostrophes, and periods
            this.value = this.value.replace(/[^a-zA-Z\s'\-\.]/g, '');
        });

        document.querySelector('input[name="phone"]').addEventListener('input', function (e) {
            // Allow digits, plus, dash, space
            this.value = this.value.replace(/[^0-9\+\-\s]/g, '');
        });
    </script>

</body>

</html>