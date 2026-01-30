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
    <title>Inventory Management | Admin Dashboard</title>
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.05);
            --accent-gold: #d4af37;
            --accent-red: #720e1e;
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.6);
        }

        body {
            padding-top: 100px;
            background-color: #0a0a0a;
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            animation: fadeIn 0.6s ease-out;
        }

        /* Header Section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .header-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin: 0 0 0.5rem 0;
            background: linear-gradient(45deg, #fff, #cacaca);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-title p {
            color: #888;
            margin: 0;
            font-size: 1rem;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--accent-gold) 0%, #b4932a 100%);
            color: #000;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3);
            background: linear-gradient(135deg, #e5bd3c 0%, #c4a02d 100%);
        }

        /* Products Grid/Table Card */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        /* Custom Table Styling */
        .styled-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .styled-table th {
            text-align: left;
            padding: 1.5rem;
            color: #888;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--glass-border);
            font-weight: 500;
        }

        .styled-table td {
            padding: 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--glass-border);
            transition: background 0.2s;
        }

        .styled-table tr:last-child td {
            border-bottom: none;
        }

        .styled-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Product Cell */
        .product-cell {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .product-img-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            position: relative;
            background: #1a1a1a;
        }

        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-img-wrapper:hover img {
            transform: scale(1.1);
        }

        .product-info h3 {
            margin: 0 0 4px 0;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
        }

        .product-info span {
            font-size: 0.85rem;
            color: #666;
            background: rgba(255, 255, 255, 0.05);
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Price */
        .price-tag {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: var(--accent-gold);
            font-weight: 700;
        }

        /* Stock Badge */
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

        /* Actions */
        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-view {
            background: rgba(255, 255, 255, 0.05);
            color: #ccc;
            margin-right: 8px;
        }

        .btn-view:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .btn-edit {
            background: rgba(212, 175, 55, 0.1);
            color: var(--accent-gold);
            margin-right: 8px;
        }

        .btn-edit:hover {
            background: rgba(212, 175, 55, 0.2);
        }

        .btn-delete {
            background: rgba(244, 67, 54, 0.05);
            color: #ef5350;
        }

        .btn-delete:hover {
            background: rgba(244, 67, 54, 0.15);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <header class="page-header">
            <div class="header-title">
                <h1>Inventory <span style="color: var(--accent-gold)">Management</span></h1>
                <p>Overview of all products across the estate.</p>
                <div style="margin-top: 10px;">
                    <a href="index.php" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none;">←
                        Back to Dashboard</a>
                </div>
            </div>
            <a href="add_product.php" class="btn-add">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add New Product
            </a>
        </header>

        <div class="glass-panel">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Seller / Brand</th>
                        <th>Inventory</th>
                        <th>Price</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 5rem;">
                                <span style="font-size: 3rem; opacity: 0.1; display: block; margin-bottom: 1rem;">🍷</span>
                                <p style="color: #666;">No products found in the database.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <div class="product-img-wrapper">
                                            <?php
                                            $img_src = '';
                                            $is_default = false;
                                            if ($p['image_path']) {
                                                $img = $p['image_path'];
                                                $img_src = (strpos($img, 'uploads/') === 0) ? '../' . $img : '../uploads/' . $img;
                                            } else {
                                                $is_default = true;
                                                $type = strtolower($p['type']);
                                                // Root-relative for JS/CSS usually, but here we are in /admin/
                                                if (strpos($type, 'red') !== false)
                                                    $img_src = '../assets/images/defaults/red_default.png';
                                                elseif (strpos($type, 'white') !== false)
                                                    $img_src = '../assets/images/defaults/white_default.png';
                                                elseif (strpos($type, 'rose') !== false)
                                                    $img_src = '../assets/images/defaults/rose_default.png';
                                                elseif (strpos($type, 'sparkling') !== false)
                                                    $img_src = '../assets/images/defaults/sparkling_default.png';
                                                else
                                                    $img_src = '../assets/images/defaults/red_default.png';
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($img_src); ?>"
                                                alt="<?php echo htmlspecialchars($p['name']); ?>">
                                        </div>
                                        <div class="product-info">
                                            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                                            <span><?php echo htmlspecialchars($p['type']); ?> •
                                                <?php echo htmlspecialchars($p['vintage_year']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($p['brand_name']): ?>
                                        <div style="display: flex; flex-direction: column;">
                                            <span
                                                style="color: #fff; font-weight: 500;"><?php echo htmlspecialchars($p['brand_name']); ?></span>
                                            <span style="color: #666; font-size: 0.8rem;">Seller ID:
                                                #<?php echo $p['seller_id']; ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="color: var(--accent-gold); font-weight: 500;">LM Hard Wine</span>
                                            <span style="color: #666; font-size: 0.8rem;">Official House Collection</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $qty = (int) $p['stock_qty'];
                                    $class = ($qty > 10) ? 'stock-high' : ($qty > 0 ? 'stock-low' : 'stock-out');
                                    $label = ($qty > 0) ? $qty . ' in stock' : 'Sold Out';
                                    ?>
                                    <span class="stock-badge <?php echo $class; ?>">
                                        <?php echo $label; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="price-tag">₱<?php echo number_format($p['price'], 2); ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="action-btn btn-view"
                                        onclick='openProductModal(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>View</button>

                                    <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="action-btn btn-edit">Edit</a>

                                    <form method="POST" style="display: inline-block;"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="delete_product_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path d="M3 6h18"></path>
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                </path>
                                            </svg>
                                        </button>
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
            style="margin: 5% auto; width: 90%; max-width: 800px; padding: 0; position: relative; border-radius: 16px;">
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
                        style="font-size: 2.5rem; margin-bottom: 0.5rem; font-family: 'Playfair Display', serif;"></h2>
                    <p id="modal-varietal" style="font-size: 1.2rem; color: #aaa; margin-bottom: 1.5rem;"></p>

                    <div style="display: flex; gap: 2rem; margin-bottom: 2rem; align-items: center;">
                        <span id="modal-price" style="font-size: 2rem; font-weight: 700; color: #fff;"></span>
                        <span id="modal-stock" class="stock-badge"></span>
                    </div>

                    <p id="modal-desc"
                        style="color: #ccc; line-height: 1.6; margin-bottom: 2rem; max-height: 150px; overflow-y: auto;">
                    </p>

                    <div
                        style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <h4
                            style="margin: 0 0 0.8rem 0; font-size: 0.9rem; color: #888; text-transform: uppercase; letter-spacing: 1px;">
                            Administrative Context</h4>
                        <div style="display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.95rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Brand Source</span>
                                <strong id="modal-brand" style="color: white;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Seller ID</span>
                                <strong id="modal-seller-id" style="color: white;">-</strong>
                            </div>
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
            document.getElementById('modal-price').innerText = '₱' + parseFloat(data.price).toLocaleString(undefined, { minimumFractionDigits: 2 });
            document.getElementById('modal-desc').innerText = data.description || 'No description provided.';
            document.getElementById('modal-brand').innerText = data.brand_name || 'LM Hard Wine (Official)';
            document.getElementById('modal-seller-id').innerText = data.seller_id ? '#' + data.seller_id : 'House Account';

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
                phEl.style.display = 'none';
            } else {
                let type = (data.type || '').toLowerCase();
                let src = '../assets/images/defaults/red_default.png';
                if (type.includes('red')) src = '../assets/images/defaults/red_default.png';
                else if (type.includes('white')) src = '../assets/images/defaults/white_default.png';
                else if (type.includes('rose')) src = '../assets/images/defaults/rose_default.png';
                else if (type.includes('sparkling')) src = '../assets/images/defaults/sparkling_default.png';

                imgEl.src = src;
                phEl.style.display = 'none';
            }
            imgEl.style.display = 'block';

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