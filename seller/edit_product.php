<?php
include 'auth.php';
include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Fetch Product (Secure: Ensure it belongs to this seller)
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$id, $user_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // If not found, check if it exists generally to give a specific error, or just die
    die("Product not found or access denied.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $varietal = $_POST['varietal'];
    $price = $_POST['price'];
    $stock_qty = $_POST['stock_qty'];
    $year = $_POST['year'];
    $description = $_POST['description'];

    // Logic: Do not overwrite color_style if it exists, unless we want to regenerate it. 
    $color_style = $product['color_style'];
    if ($type != $product['type']) {
        if ($type == 'Red')
            $color_style = "linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent)";
        elseif ($type == 'White')
            $color_style = "linear-gradient(45deg, rgba(212, 175, 55, 0.1), transparent)";
        elseif ($type == 'Rose')
            $color_style = "linear-gradient(45deg, rgba(255, 105, 180, 0.1), transparent)";
    }

    $image_path = $product['image_path'];

    // Handle Image Update
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = "wine_" . uniqid() . "." . $ext;
            $upload_dir = '../uploads/';

            // Ensure uploads directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $destination = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                $image_path = "uploads/" . $new_name; // Relative path from root
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type.";
        }
    }

    if (!$error) {
        // Secure Update
        $sql = "UPDATE products SET name=?, type=?, varietal=?, price=?, stock_qty=?, vintage_year=?, description=?, color_style=?, image_path=? WHERE id=? AND seller_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $type, $varietal, $price, $stock_qty, $year, $description, $color_style, $image_path, $id, $user_id]);

        $success = "Product updated!";
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$id, $user_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Seller</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            padding-top: 80px;
        }

        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .alert {
            padding: 10px;
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

        .current-img-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #333;
            margin-bottom: 0.5rem;
            display: block;
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="admin-container">
        <header style="margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">
            <h1>Edit Wine</h1>
            <a href="products.php" style="color: var(--color-text-muted);">← Back to My Products</a>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="glass-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <option value="Red" <?php echo $product['type'] == 'Red' ? 'selected' : ''; ?>>Red</option>
                        <option value="White" <?php echo $product['type'] == 'White' ? 'selected' : ''; ?>>White</option>
                        <option value="Rose" <?php echo $product['type'] == 'Rose' ? 'selected' : ''; ?>>Rose</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Varietal</label>
                    <input type="text" name="varietal" class="form-control"
                        value="<?php echo htmlspecialchars($product['varietal']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" class="form-control"
                        value="<?php echo $product['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_qty" class="form-control"
                        value="<?php echo $product['stock_qty']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Vintage Year</label>
                    <input type="number" name="year" class="form-control"
                        value="<?php echo $product['vintage_year']; ?>" required>
                </div>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label>Description</label>
                <textarea name="description" class="form-control"
                    rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <?php if ($product['image_path']): ?>
                    <!-- Note: Image path logic handling needed if absolute vs relative -->
                    <!-- Attempting to show correct relative path. Assuming stored as 'uploads/file.jpg' or just 'file.jpg' -->
                    <?php
                    $display_path = strpos($product['image_path'], 'uploads/') === 0 ? '../' . $product['image_path'] : '../uploads/' . $product['image_path'];
                    ?>
                    <img src="<?php echo $display_path; ?>" class="current-img-preview">
                    <p style="font-size: 0.8rem; color: #888; margin-bottom: 0.5rem;">Current Image</p>
                <?php endif; ?>

                <div class="file-upload-wrapper">
                    <div class="file-upload-icon">📷</div>
                    <span id="file-label">Change Image (Optional)</span>
                    <input type="file" name="product_image" accept="image/*"
                        onchange="document.getElementById('file-label').textContent = this.files[0].name">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Save Changes</button>
        </form>
    </div>
</body>

</html>