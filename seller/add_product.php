<?php
include 'auth.php';
include '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $varietal = $_POST['varietal'];
    $price = $_POST['price'];
    $stock_qty = $_POST['stock_qty'];
    $year = $_POST['year'];
    $description = $_POST['description'];

    // Automatically assign to current seller
    $seller_id = $_SESSION['user_id'];

    // Auto-generate color_style fallback
    $color_style = "linear-gradient(45deg, #333, #000)"; // default
    if ($type == 'Red') {
        $color_style = "linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent)";
    } elseif ($type == 'White') {
        $color_style = "linear-gradient(45deg, rgba(212, 175, 55, 0.1), transparent)";
    } elseif ($type == 'Rose') {
        $color_style = "linear-gradient(45deg, rgba(255, 105, 180, 0.1), transparent)";
    }

    // Image Upload Logic
    $image_path = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = "wine_" . uniqid() . "." . $ext;
            // Upload to main uploads directory (up one level from seller folder)
            $upload_dir = '../uploads/';

            // Ensure uploads directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $destination = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                $image_path = "uploads/" . $new_name; // Store relative path from root
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, WEBP allowed.";
        }
    }

    if (!$error) {
        try {
            $sql = "INSERT INTO products (seller_id, name, type, varietal, price, stock_qty, vintage_year, description, color_style, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$seller_id, $name, $type, $varietal, $price, $stock_qty, $year, $description, $color_style, $image_path]);
            $success = "Product added successfully!";
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Seller</title>
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
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="admin-container">
        <header style="margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">
            <h1>Add New Wine</h1>
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
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <option value="Red">Red</option>
                        <option value="White">White</option>
                        <option value="Rose">Rose</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Varietal</label>
                    <input type="text" name="varietal" class="form-control" placeholder="e.g. Cabernet Sauvignon"
                        required>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_qty" class="form-control" value="10" min="0" required>
                </div>
                <div class="form-group">
                    <label>Vintage Year</label>
                    <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required>
                </div>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <div class="file-upload-wrapper" style="text-align: center;">
                    <img id="image-preview" src="#" alt="Preview"
                        style="display: none; max-width: 100%; max-height: 200px; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #444;">
                    <div class="file-upload-icon">📷</div>
                    <span id="file-label">Click or Drag Image Here</span>
                    <input type="file" name="product_image" accept="image/*" onchange="previewImage(this)">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Add Product</button>
        </form>
    </div>
</body>
<script>
    function previewImage(input) {
        var preview = document.getElementById('image-preview');
        var label = document.getElementById('file-label');

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'inline-block';
            }

            reader.readAsDataURL(input.files[0]);
            label.textContent = input.files[0].name;
        }
    }
</script>

</html>