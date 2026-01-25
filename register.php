<?php
include 'includes/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Plain text as requested
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    // Basic Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Create User
            // Note: Storing password in plain text as requested for school project
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

                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
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
</body>

</html>