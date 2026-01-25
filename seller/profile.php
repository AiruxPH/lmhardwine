<?php
include 'auth.php';
include '../includes/db.php';
// session_start() handled by auth.php

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand_name = trim($_POST['brand_name']);
    $brand_description = trim($_POST['brand_description']);
    $contact_email = trim($_POST['contact_email']);
    
    // File Upload Handling
    $logo_path = null;
    if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] == 0) {
        // Upload to main uploads folder (up one level)
        $upload_dir = '../uploads/logos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['brand_logo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('brand_') . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $destination)) {
                // Store path relative to root for consistency
                $logo_path = 'uploads/logos/' . $new_filename;
            } else {
                $error = "Failed to upload logo.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and WEBP allowed.";
        }
    }

    if (empty($error)) {
        try {
            if ($logo_path) {
                // Update with logo
                $stmt = $pdo->prepare("UPDATE seller_profiles SET brand_name = ?, brand_description = ?, contact_email = ?, brand_logo_path = ? WHERE user_id = ?");
                $stmt->execute([$brand_name, $brand_description, $contact_email, $logo_path, $user_id]);
            } else {
                // Update without changing logo
                $stmt = $pdo->prepare("UPDATE seller_profiles SET brand_name = ?, brand_description = ?, contact_email = ? WHERE user_id = ?");
                $stmt->execute([$brand_name, $brand_description, $contact_email, $user_id]);
            }
            $success = "Brand profile updated successfully!";
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}

// Fetch Current Data
try {
    $stmt = $pdo->prepare("
        SELECT u.username, sp.brand_name, sp.brand_description, sp.contact_email, sp.brand_logo_path 
        FROM users u 
        JOIN seller_profiles sp ON u.id = sp.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        die("Seller profile not found. Please contact admin.");
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
    <title>Seller Profile - LM Hard Wine</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            padding-top: 80px;
        }

        .profile-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
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

        .logo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--color-accent);
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="profile-container">
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">
                <div>
                    <h2>Brand Profile</h2>
                    <p style="color: var(--color-text-muted);">Manage your brand identity.</p>
                </div>
                <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
            </div>

            <?php if (!empty($success)) { ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php } ?>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">
                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    
                    <!-- Logo Section -->
                    <div style="flex: 0 0 200px; text-align: center;">
                        <?php if (!empty($seller['brand_logo_path'])): ?>
                            <img src="<?php echo '../' . htmlspecialchars($seller['brand_logo_path']); ?>" alt="Logo" class="logo-preview">
                        <?php else: ?>
                            <div class="logo-preview" style="display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05);">No Logo</div>
                        <?php endif; ?>
                        
                        <label class="btn btn-sm" style="cursor: pointer; display: inline-block; width: 100%;">
                            Change Logo
                            <input type="file" name="brand_logo" style="display: none;" accept="image/*">
                        </label>
                    </div>

                    <!-- Details Section -->
                    <div style="flex: 1; min-width: 300px;">
                        <div class="form-group">
                            <label>Username (Read-only)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($seller['username']); ?>" disabled style="opacity: 0.7;">
                        </div>

                        <div class="form-group">
                            <label>Brand Name</label>
                            <input type="text" name="brand_name" class="form-control" value="<?php echo htmlspecialchars($seller['brand_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($seller['contact_email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Brand Description</label>
                            <textarea name="brand_description" class="form-control" rows="5"><?php echo htmlspecialchars($seller['brand_description'] ?? ''); ?></textarea>
                        </div>

                        <div style="text-align: right; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</body>
</html>