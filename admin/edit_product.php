<?php
include 'auth.php';
include '../includes/db.php';

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Fetch Product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product)
    die("Product not found.");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $varietal = $_POST['varietal'];
    $price = $_POST['price'];
    $year = $_POST['year'];
    $description = $_POST['description'];

    // Logic: Do not overwrite color_style if it exists, unless we want to regenerate it. 
    // For now, let's keep the original color style generator logic just in case type changes.
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
            $new_name = "wine_" . time() . "." . $ext;
            $destination = "../uploads/" . $new_name;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                $image_path = $new_name;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type.";
        }
    }

    if (!$error) {
        $sql = "UPDATE products SET name=?, type=?, varietal=?, price=?, vintage_year=?, description=?, color_style=?, image_path=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $type, $varietal, $price, $year, $description, $color_style, $image_path, $id]);

        $success = "Product updated!";
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 600px;
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
    <div class="admin-container">
        <header style="margin-bottom: 2rem; border-bottom: 1px solid #333; padding-bottom: 1rem;">
            <h1>Edit Wine</h1>
            <a href="products.php" style="color: var(--color-text-muted);">← Back to Products</a>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="glass-card">
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
                <label>Vintage Year</label>
                <input type="number" name="year" class="form-control" value="<?php echo $product['vintage_year']; ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control"
                    rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <?php if ($product['image_path']): ?>
                    <img src="../uploads/<?php echo $product['image_path']; ?>" class="current-img-preview">
                    <p style="font-size: 0.8rem; color: #888; margin-bottom: 0.5rem;">Current Image</p>
                <?php endif; ?>

                <div class="file-upload-wrapper">
                    <div class="file-upload-icon">📷</div>
                    <span id="file-label">Change Image (Optional)</span>
                    <input type="file" name="product_image" accept="image/*"
                        onchange="document.getElementById('file-label').textContent = this.files[0].name">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
        </form>
    </div>
</body>

</html>