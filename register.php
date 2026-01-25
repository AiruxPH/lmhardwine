<?php
include 'includes/db.php';
session_start();

$error = '';
$success = '';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Plain text as requested
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match("/^[a-zA-Z\s'\-\.]+$/", $full_name)) {
        $error = "Invalid Full Name. Only letters, spaces, hyphens, apostrophes, and periods allowed.";
    } elseif (!preg_match("/^[a-zA-Z0-9_.]+$/", $username)) {
        $error = "Username can only contain letters, numbers, dots, and underscores.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = "Username must be between 3 and 20 characters.";
    } elseif (!empty($phone) && !preg_match("/^[0-9\-\+\s]+$/", $phone)) {
        $error = "Invalid phone number format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Create User
            // Note: Storing password in plain text as requested for school project.
            // Plain Text Passwords are for demonstration purposes only.
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$username, $email, $password]);
            $user_id = $pdo->lastInsertId();

            // 2. Create Customer Profile
            $stmt_profile = $pdo->prepare("INSERT INTO customer_profiles (user_id, full_name, phone_number) VALUES (?, ?, ?)");
            $stmt_profile->execute([$user_id, $full_name, $phone]);

            $pdo->commit();
            $success = "Registration successful! You can now <a href='login.php' style='color: var(--color-accent);'>Login</a>.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $error = "Username or Email already exists.";
            } else {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LM Hard Wine</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            /* Inherit global styles */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .auth-container {
            width: 100%;
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-text-muted);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 4px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-accent);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        /* Password Strength Meter */
        .strength-meter {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
            display: flex;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }

        .strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
            color: var(--color-text-muted);
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="glass-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-family: 'Playfair Display', serif;">Join the Club</h1>
                <p style="color: var(--color-text-muted);">Create your account to start ordering.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php else: ?>

                <form method="POST" autocomplete="off" id="registerForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required autocomplete="off"
                            placeholder="e.g. John Doe">
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required autocomplete="off"
                            pattern="[a-zA-Z0-9_.]+" title="Only letters, numbers, dots, and underscores allowed."
                            minlength="3" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" autocomplete="off" pattern="[0-9\-\+\s]*"
                            title="Only digits, spaces, and dashes allowed.">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="password" class="form-control" required
                            autocomplete="new-password">
                        <div class="strength-meter">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; border: none; padding: 12px; font-size: 1rem;">Register</button>
                </form>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: var(--color-text-muted); font-size: 0.9rem;">
                    Already have an account? <a href="login.php" style="color: var(--color-accent);">Login here</a>
                </p>
                <a href="index.php" style="display: block; margin-top: 1rem; color: #666; font-size: 0.8rem;">← Back to
                    Home</a>
            </div>
        </div>
    </div>

    <script>
        // Password Strength Meter
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function () {
            const val = this.value;
            let strength = 0;
            let status = '';
            let color = 'transparent';

            if (val.length > 0) {
                if (val.length >= 8) strength++;
                if (val.match(/[0-9]/)) strength++;
                if (val.match(/[a-z]/) && val.match(/[A-Z]/)) strength++;
                if (val.match(/[^a-zA-Z0-9]/)) strength++;
            }

            switch (strength) {
                case 0:
                    status = '';
                    break;
                case 1:
                    status = 'Weak';
                    color = '#f44336';
                    break;
                case 2:
                    status = 'Medium';
                    color = '#ff9800';
                    break;
                case 3:
                case 4:
                    status = 'Strong';
                    color = '#4caf50';
                    break;
            }

            strengthBar.style.width = (val.length > 0) ? (strength * 25) + '%' : '0';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = status;
            strengthText.style.color = color;
        });

        // Input Masking / Gatekeeping
        document.querySelector('input[name="full_name"]').addEventListener('input', function (e) {
            // Allow letters, spaces, hyphens, apostrophes, and periods
            this.value = this.value.replace(/[^a-zA-Z\s'\-\.]/g, '');
        });

        document.querySelector('input[name="username"]').addEventListener('input', function (e) {
            // Allow alphanumeric, dot, underscore
            this.value = this.value.replace(/[^a-zA-Z0-9_.]/g, '');
        });

        document.querySelector('input[name="phone"]').addEventListener('input', function (e) {
            // Allow digits, plus, dash, space
            this.value = this.value.replace(/[^0-9\+\-\s]/g, '');
        });
    </script>
</body>

</html>