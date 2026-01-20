<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];

    // Hardcoded password
    if ($password === 'Admin123!') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LM Hard Wine</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
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
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 4px;
        }

        .error-msg {
            color: #ff4d4d;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="glass-card login-card">
        <h2 class="text-center" style="margin-bottom: 2rem;">Admin Login</h2>

        <?php if ($error): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <div class="text-center" style="margin-top: 1rem;">
            <a href="../index.php" style="font-size: 0.9rem; color: var(--color-text-muted);">Return to Store</a>
        </div>
    </div>
</body>

</html>