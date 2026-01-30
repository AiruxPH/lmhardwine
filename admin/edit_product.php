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
    $stock_qty = $_POST['stock_qty'];
    $year = $_POST['year'];
    $description = $_POST['description'];

    // Seller Assignment Logic
    $seller_id = !empty($_POST['seller_id']) ? $_POST['seller_id'] : null;

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

            // Ensure uploads directory exists
            if (!is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0777, true);
            }

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
        $sql = "UPDATE products SET name=?, type=?, varietal=?, price=?, stock_qty=?, vintage_year=?, description=?, color_style=?, image_path=?, seller_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $type, $varietal, $price, $stock_qty, $year, $description, $color_style, $image_path, $seller_id, $id]);

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
    <link rel="stylesheet" href="../css/style.css?v=1.4">
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
            max-width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #333;
            margin-bottom: 1rem;
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
                <label>Name <span class="tooltip-icon"
                        data-tooltip="The commercial name of the wine bottle.">?</span></label>
                <input type="text" name="name" class="form-control"
                    value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <?php
            // Fetch Sellers for Dropdown
            $stmt_sellers = $pdo->query("SELECT u.id, sp.brand_name FROM users u JOIN seller_profiles sp ON u.id = sp.user_id WHERE u.role = 'seller' ORDER BY sp.brand_name");
            $sellers = $stmt_sellers->fetchAll();
            ?>
            <div class="form-group">
                <label>Assign to Seller (Optional) <span class="tooltip-icon"
                        data-tooltip="Assign this product to a specific seller's inventory. Leave blank for House/Admin product.">?</span></label>
                <select name="seller_id" class="form-control">
                    <option value="">-- Admin (No specific seller) --</option>
                    <?php foreach ($sellers as $seller): ?>
                        <option value="<?php echo $seller['id']; ?>" <?php echo ($product['seller_id'] == $seller['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($seller['brand_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Type <span class="tooltip-icon"
                            data-tooltip="General classification: Red, White, or Rose.">?</span></label>
                    <select name="type" class="form-control">
                        <option value="Red" <?php echo $product['type'] == 'Red' ? 'selected' : ''; ?>>Red</option>
                        <option value="White" <?php echo $product['type'] == 'White' ? 'selected' : ''; ?>>White</option>
                        <option value="Rose" <?php echo $product['type'] == 'Rose' ? 'selected' : ''; ?>>Rose</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Varietal <span class="tooltip-icon"
                            data-tooltip="The specific grape variety, e.g., Merlot, Chardonnay.">?</span></label>
                    <input type="text" name="varietal" class="form-control"
                        value="<?php echo htmlspecialchars($product['varietal']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Price (₱) <span class="tooltip-icon"
                            data-tooltip="Selling price per bottle in PHP.">?</span></label>
                    <input type="number" step="0.01" name="price" class="form-control"
                        value="<?php echo $product['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Stock Quantity <span class="tooltip-icon"
                            data-tooltip="Number of bottles currently available in inventory.">?</span></label>
                    <input type="number" name="stock_qty" class="form-control"
                        value="<?php echo $product['stock_qty']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Vintage Year <span class="tooltip-icon"
                            data-tooltip="The year the grapes were harvested.">?</span></label>
                    <input type="number" name="year" class="form-control"
                        value="<?php echo $product['vintage_year']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description <span class="tooltip-icon"
                        data-tooltip="Tasting notes, origin details, and pairing suggestions.">?</span></label>
                <textarea name="description" class="form-control"
                    rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Product Image <span class="tooltip-icon"
                        data-tooltip="Update the bottle photo. Leave empty to keep the current one.">?</span></label>
                <div class="file-upload-wrapper" style="text-align: center;">
                    <?php
                    $img_src = '';
                    if ($product['image_path']) {
                        $img = $product['image_path'];
                        $img_src = (strpos($img, 'uploads/') === 0) ? '../' . $img : '../uploads/' . $img;
                    } else {
                        $type = strtolower($product['type']);
                        if (strpos($type, 'red') !== false)
                            $img_src = '../assets/images/defaults/red_default.png';
                        elseif (strpos($type, 'white') !== false)
                            $img_src = '../assets/images/defaults/white_default.png';
                        elseif (strpos($type, 'rose') !== false)
                            $img_src = '../assets/images/defaults/rose_default.png';
                        else
                            $img_src = '../assets/images/defaults/red_default.png';
                    }
                    ?>
                    <img id="image-preview" src="<?php echo htmlspecialchars($img_src); ?>" class="current-img-preview"
                        style="display: inline-block; margin: 0 auto 1rem;">

                    <div id="upload-content">
                        <div class="file-upload-icon">📷</div>
                        <span id="file-label">Click or Drag to Change Image</span>
                    </div>
                    <input type="file" name="product_image" accept="image/*" onchange="previewImage(this)">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
        </form>
    </div>

    <script>
        function previewImage(input) {
            var preview = document.getElementById('image-preview');
            var label = document.getElementById('file-label');
            var uploadContent = document.getElementById('upload-content');

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                    if (uploadContent) uploadContent.style.display = 'none';
                }

                reader.readAsDataURL(input.files[0]);
                label.textContent = input.files[0].name;
            }
        }
    </script>
</body>

</html>