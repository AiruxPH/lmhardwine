<?php
include 'auth.php';
include '../includes/db.php';

// Handle Soft Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product_id'])) {
    $id = $_POST['delete_product_id'];
    $stmt = $pdo->prepare("UPDATE products SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php");
    exit;
}

// Fetch Products with Seller Info
$stmt = $pdo->query("SELECT p.*, sp.brand_name FROM products p LEFT JOIN seller_profiles sp ON p.seller_id = sp.user_id WHERE p.is_deleted = 0 ORDER BY p.id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .product-table th,
        .product-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .product-table th {
            color: var(--color-accent);
            font-weight: normal;
        }

        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            background: #333;
        }

        /* Modal Styles */
        .stock-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .stock-high {
            background: rgba(76, 175, 80, 0.15);
            color: #66bb6a;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .stock-low {
            background: rgba(255, 152, 0, 0.15);
            color: #ffa726;
            border: 1px solid rgba(255, 152, 0, 0.2);
        }

        .stock-out {
            background: rgba(244, 67, 54, 0.15);
            color: #ef5350;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <header
            style="margin-bottom: 2rem; border-bottom: 1px solid #333; padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Manage Products</h1>
                <a href="index.php" style="color: var(--color-text-muted);">← Back to Dashboard</a>
            </div>
            <a href="add_product.php" class="btn btn-primary">Add New Product</a>
        </header>

        <div class="glass-card">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Seller</th>
                        <th>Type</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No products found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['image_path']): ?>
                                        <?php
                                        $img = $p['image_path'];
                                        $display_path = (strpos($img, 'uploads/') === 0) ? '../' . $img : '../uploads/' . $img;
                                        ?>
                                                    <img src="<?php echo htmlspecialchars($display_path); ?>"
                                        class="product-thumb">
                                    <?php else: ?>
                                        <div class="product-thumb" style="background: <?php echo $p['color_style']; ?>"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </td>
                                <td>
                                    <?php if ($p['brand_name']): ?>
                                        <span
                                            style="color: var(--color-accent); font-weight: bold;"><?php echo htmlspecialchars($p['brand_name']); ?></span>
                                    <?php else: ?>
                                        <span style="color: #888; font-style: italic;">Admin (House)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['type']); ?>
                                </td>
                                <td style="<?php echo ($p['stock_qty'] < 5) ? 'color: #ff9800; font-weight:bold;' : ''; ?>">
                                    <?php echo htmlspecialchars($p['stock_qty']); ?>
                                </td>
                                <td>₱
                                    <?php echo number_format($p['price'], 2); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['vintage_year']); ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm"
                                        style="background: rgba(255, 255, 255, 0.1); color: #ccc; margin-right: 8px; border: none; cursor: pointer;"
                                        onclick='openProductModal(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>View</button>

                                    <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm"
                                        style="border-color: var(--color-text-muted); color: white;">Edit</a>

                                    <form method="POST" style="display: inline-block; margin-left: 10px;"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="delete_product_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" class="btn btn-sm"
                                            style="border-color: #f44336; color: #f44336; background: none;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Product Preview Modal -->
    <div id="productModal"
        style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.85); backdrop-filter: blur(8px);">
        <div class="glass-panel"
            style="margin: 5% auto; width: 90%; max-width: 800px; padding: 0; position: relative;">
            <button onclick="closeProductModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 2rem; cursor: pointer; z-index: 10;">&times;</button>

            <div style="display: flex; flex-wrap: wrap;">
                <!-- Image Side -->
                <div
                    style="flex: 1 1 300px; height: 500px; background: #1a1a1a; position: relative; display: flex; align-items: center; justify-content: center;">
                    <img id="modal-img" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    <div id="modal-placeholder"
                        style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                        <span id="modal-type-ph"
                            style="font-size: 4rem; opacity: 0.1; font-weight: 700; text-transform: uppercase;">WINE</span>
                        <div id="modal-color-ph" style="position: absolute; inset: 0;"></div>
                    </div>
                </div>

                <!-- Details Side -->
                <div style="flex: 1 1 300px; padding: 2.5rem;">
                    <p id="modal-vintage"
                        style="color: var(--color-accent); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 0.5rem;">
                    </p>
                    <h2 id="modal-name"
                        style="font-size: 2.5rem; margin-bottom: 0.5rem;"></h2>
                    <p id="modal-varietal" style="font-size: 1.2rem; color: #aaa; margin-bottom: 1.5rem;"></p>

                    <div style="display: flex; gap: 2rem; margin-bottom: 2rem; align-items: center;">
                        <span id="modal-price" style="font-size: 2rem; font-weight: 700; color: #fff;"></span>
                        <span id="modal-stock" class="stock-badge"></span>
                    </div>

                    <p id="modal-desc"
                        style="color: #ccc; line-height: 1.6; margin-bottom: 2rem; max-height: 150px; overflow-y: auto;">
                    </p>

                    <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px;">
                        <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #888;">Administrative Context</h4>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                            <span>Brand: <strong id="modal-brand" style="color: white;">-</strong></span>
                            <span>Seller ID: <strong id="modal-seller-id" style="color: white;">-</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('productModal');

        function openProductModal(data) {
            // Populate Data
            document.getElementById('modal-name').innerText = data.name;
            document.getElementById('modal-vintage').innerText = (data.vintage_year || 'NV') + ' Vintage';
            document.getElementById('modal-varietal').innerText = data.varietal;
            document.getElementById('modal-price').innerText = '₱' + parseFloat(data.price).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('modal-desc').innerText = data.description || 'No description provided.';
            document.getElementById('modal-brand').innerText = data.brand_name || 'Admin (House)';
            document.getElementById('modal-seller-id').innerText = data.seller_id || 'N/A (Official)';

            // Stock Logic
            const qty = parseInt(data.stock_qty);
            const stockEl = document.getElementById('modal-stock');
            stockEl.innerText = qty + ' In Stock';
            stockEl.className = 'stock-badge ' + (qty > 5 ? 'stock-high' : (qty > 0 ? 'stock-low' : 'stock-out'));

            // Image Logic
            const imgEl = document.getElementById('modal-img');
            const phEl = document.getElementById('modal-placeholder');
            const typePh = document.getElementById('modal-type-ph');
            const colorPh = document.getElementById('modal-color-ph');

            if (data.image_path) {
                let img = data.image_path;
                let src = (img.startsWith('uploads/')) ? '../' + img : '../uploads/' + img;
                imgEl.src = src;
                imgEl.style.display = 'block';
                phEl.style.display = 'none';
            } else {
                imgEl.style.display = 'none';
                phEl.style.display = 'flex';
                typePh.innerText = data.type;
                colorPh.style.background = data.color_style || 'rgba(100,0,0,0.5)';
            }

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeProductModal() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                closeProductModal();
            }
        }
    </script>
</body>

</html>