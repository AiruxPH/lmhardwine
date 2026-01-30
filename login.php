<?php
session_start();
include 'includes/db.php';

$error = '';

// 1. Check if Admin is logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/index.php');
    exit;
}

// 2. Check if User (Seller/Customer) is logged in
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
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // 1. Check Admins Table First (Secure Hash - Legacy/Standard)
        // Added is_deleted = 0 check
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_deleted = 0");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // Note: keeping password_verify for admins as strictly requested by user rules (admins have separate database/logic)
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'] ?? 'admin'; // Fetch role
            $_SESSION['user_type'] = 'admin'; // Unified identifier
            $_SESSION['username'] = $admin['username'];

            header('Location: admin/index.php');
            exit();
        }

        // 2. Check Users Table (Sellers/Customers) - Plain Text as requested
        // Plain Text Passwords are for demonstration purposes only.
        // Added is_deleted = 0 check
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_deleted = 0");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user) {
            // Direct comparison for plain text password
            if ($password === $user['password_hash']) {
                $_SESSION['user_logged_in'] = true; // Distinguish from admin
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role']; // 'customer' or 'seller'
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                if ($user['role'] == 'seller') {
                    // Redirect seller to dashboard
                    header('Location: seller/index.php');
                } else {
                    // Redirect customer
                    header('Location: index.php');
                }
                exit();
            }
        }

        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LM Hard Wine</title>
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
            max-width: 400px;
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

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="glass-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-family: 'Playfair Display', serif;">Welcome Back</h1>
                <p style="color: var(--color-text-muted);">Login to access your account.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label>Username or Email</label>
                    <input type="text" name="username" class="form-control" required autocomplete="off"
                        placeholder="Enter your username or email">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required autocomplete="new-password"
                        placeholder="Enter your password">
                    <div style="text-align: right; margin-top: 5px;">
                        <a href="forgot_password.php"
                            style="color: #888; font-size: 0.8rem; text-decoration: none;">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; border: none; padding: 12px; font-size: 1rem;">Login</button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: var(--color-text-muted); font-size: 0.9rem;">
                    Don't have an account? <a href="register.php" style="color: var(--color-accent);">Register here</a>
                </p>
                <a href="index.php" style="display: block; margin-top: 1rem; color: #666; font-size: 0.8rem;">← Return
                    to Store</a>
            </div>
        </div>
    </div>
</body>

</html>