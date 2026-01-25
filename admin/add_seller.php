<?php
include 'auth.php';
include '../includes/db.php';

$success = '';
$error = '';
$generated_password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $brand_name = trim($_POST['brand_name']);
    $brand_desc = trim($_POST['brand_description']);

    // Auto-generate Plain Text Password
    // Simple random string generator for school project
    $generated_password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 10);

    // Validate
    if (empty($username) || empty($email) || empty($brand_name)) {
        $error = "Username, Email, and Brand Name are required.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Create User (Seller)
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'seller')");
            $stmt->execute([$username, $email, $generated_password]);
            $user_id = $pdo->lastInsertId();

            // 2. Create Seller Profile
            $stmt_profile = $pdo->prepare("INSERT INTO seller_profiles (user_id, brand_name, brand_description) VALUES (?, ?, ?)");
            $stmt_profile->execute([$user_id, $brand_name, $brand_desc]);

            // Handle Logo Upload if present
            if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $filename = $_FILES['brand_logo']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $new_name = "brand_" . uniqid() . "." . $ext;
                    // Path relative to admin folder for moving file
                    $upload_path_admin = "../uploads/logos/";
                    // Path relative to root for database storage
                    $db_path = "uploads/logos/" . $new_name;

                    if (!is_dir($upload_path_admin)) {
                        mkdir($upload_path_admin, 0777, true);
                    }

                    if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $upload_path_admin . $new_name)) {
                        // Use lastInsertId for the profile ID safely
                        $stmt_img = $pdo->prepare("UPDATE seller_profiles SET brand_logo_path = ? WHERE id = ?");
                        $stmt_img->execute([$db_path, $pdo->lastInsertId()]);
                    }
                }
            }

            $pdo->commit();
            $success = "Seller created successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $generated_password = ''; // Clear if failed
            if ($e->getCode() == 23000) {
                $error = "Username or Email already exists.";
            } else {
                $error = "Error: " . $e->getMessage();
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
    <title>Add Seller - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }

        .password-display {
            background: #222;
            padding: 1rem;
            border: 1px solid #444;
            color: #4caf50;
            font-family: monospace;
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <header style="margin-bottom: 2rem; border-bottom: 1px solid #333; padding-bottom: 1rem;">
            <h1>Add New Seller</h1>
            <a href="index.php" style="color: var(--color-text-muted);">← Back to Dashboard</a>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>
                    <?php echo $success; ?>
                </strong>
            </div>
            <?php if ($generated_password): ?>
                <div
                    style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid var(--color-accent);">
                    <h3 style="margin-top: 0; color: #d4af37;">⚠️ Copy Password Now</h3>
                    <p>The password for this seller is:</p>
                    <div class="password-display">
                        <?php echo $generated_password; ?>
                    </div>
                    <p style="font-size: 0.9rem; color: #ccc;">Please send this password to the seller securely. It will not be
                        shown again.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="glass-card">
            <h3>Account Details</h3>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <h3 style="margin-top: 2rem;">Brand Information</h3>
            <div class="form-group">
                <label>Brand Name</label>
                <input type="text" name="brand_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Brand Description</label>
                <textarea name="brand_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Brand Logo</label>
                <input type="file" name="brand_logo" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Create Seller</button>
        </form>
    </div>
</body>

</html>