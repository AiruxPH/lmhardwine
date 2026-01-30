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

    // Seller Assignment Logic
    $seller_id = !empty($_POST['seller_id']) ? $_POST['seller_id'] : null;

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
            $new_name = "wine_" . time() . "." . $ext;
            // Use absolute path for reliability
            $upload_dir = __DIR__ . '/../uploads/';

            // Ensure uploads directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $destination = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                $image_path = $new_name;
            } else {
                $error = "Failed to move uploaded file to: " . $destination;
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
<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <header style="margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">
        <h1>Add New Wine</h1>
        <a href="index.php" style="color: var(--color-text-muted);">← Back to Dashboard</a>
    </header>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="glass-card">
        <div class="form-group">
            <label>Name <span class="tooltip-icon"
                    data-tooltip="The commercial name of the wine bottle.">?</span></label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Reserve Cabernet"
                autocomplete="off">
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
                    <option value="<?php echo $seller['id']; ?>"><?php echo htmlspecialchars($seller['brand_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label>Type <span class="tooltip-icon"
                        data-tooltip="General classification: Red, White, or Rose.">?</span></label>
                <select name="type" class="form-control">
                    <option value="Red">Red</option>
                    <option value="White">White</option>
                    <option value="Rose">Rose</option>
                </select>
            </div>
            <div class="form-group">
                <label>Varietal <span class="tooltip-icon"
                        data-tooltip="The specific grape variety, e.g., Merlot, Chardonnay.">?</span></label>
                <input type="text" name="varietal" class="form-control" placeholder="e.g. Cabernet Sauvignon" required>
            </div>
            <div class="form-group">
                <label>Price (₱) <span class="tooltip-icon"
                        data-tooltip="Selling price per bottle in PHP.">?</span></label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Stock Quantity <span class="tooltip-icon"
                        data-tooltip="Number of bottles currently available in inventory.">?</span></label>
                <input type="number" name="stock_qty" class="form-control" value="10" min="0" required>
            </div>
            <div class="form-group">
                <label>Vintage Year <span class="tooltip-icon"
                        data-tooltip="The year the grapes were harvested.">?</span></label>
                <input type="number" name="year" class="form-control" value="2024" required>
            </div>
        </div>

        <div class="form-group">
            <label>Description <span class="tooltip-icon"
                    data-tooltip="Tasting notes, origin details, and pairing suggestions.">?</span></label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label>Product Image <span class="tooltip-icon"
                    data-tooltip="High-quality photo of the bottle.">?</span></label>
            <div class="file-upload-wrapper" style="text-align: center;">
                <img id="image-preview" src="#" alt="Preview"
                    style="display: none; max-width: 100%; max-height: 300px; margin-bottom: 1rem; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">

                <div id="upload-content">
                    <div class="file-upload-icon">📷</div>
                    <span id="file-label">Click or Drag Image Here</span>
                </div>
                <input type="file" name="product_image" accept="image/*" onchange="previewImage(this)">
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Add Product</button>
    </form>
</div>

<script>
    function previewImage(input) {
        var preview = document.getElementById('image-preview');
        var uploadContent = document.getElementById('upload-content');

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'inline-block';
                uploadContent.style.display = 'none'; // Hide text/icon
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>

</html>