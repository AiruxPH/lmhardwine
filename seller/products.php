<?php
include 'auth.php';
include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("UPDATE products SET is_deleted = 1 WHERE id = ? AND seller_id = ?");
        $stmt->execute([$id, $user_id]);
        $success = "Product removed successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting product: " . $e->getMessage();
    }
}

// Fetch Seller's Products
$stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? AND is_deleted = 0 ORDER BY id DESC");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cellar | Seller Dashboard</title>
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
        }

        .btn-edit {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            margin-right: 8px;
        }

        .btn-edit:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-delete {
            color: #ef5350;
            padding: 8px;
        }

        .btn-delete:hover {
            background: rgba(244, 67, 54, 0.1);
            border-radius: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: var(--glass-border);
            margin-bottom: 1.5rem;
            display: block;
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

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border-left: 4px solid #4caf50;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 4px;
            color: #a5d6a7;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            border-left: 4px solid #f44336;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 4px;
            color: #ef9a9a;
        }
    </style>
</head>

<body>



    <div class="dashboard-container">

        <!-- Animated Header -->
        <div class="page-header">
            <div class="header-title">
                <h1>My Collection</h1>
                <p>Curate your portfolio of premium wines.</p>
            </div>
            <a href="add_product.php" class="btn-add">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Bottle
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="glass-panel">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <span class="empty-icon">🍷</span>
                    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #fff;">Your cellar is empty</h3>
                    <p style="color: #888; margin-bottom: 2rem;">Start building your legacy by adding your first vintage.
                    </p>
                    <a href="add_product.php" class="btn-add">Add Your First Wine</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th width="40%">Wine Details</th>
                                <th width="20%">Price</th>
                                <th width="20%">Status</th>
                                <th width="20%" style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-img-wrapper">
                                                <?php
                                                $img_src = '';
                                                $is_default = false;
                                                if (!empty($p['image_path'])) {
                                                    $img_src = (strpos($p['image_path'], 'uploads/') === 0) ? '../' . $p['image_path'] : '../uploads/' . $p['image_path'];
                                                } else {
                                                    $is_default = true;
                                                    $type = strtolower($p['type']);
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
                                                    alt="<?php echo htmlspecialchars($p['name']); ?>"
                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="product-info">
                                                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                                                <span><?php echo htmlspecialchars($p['type']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="price-tag">₱<?php echo number_format($p['price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $qty = (int) $p['stock_qty'];
                                        $badgeClass = 'stock-high';
                                        $statusText = 'In Stock (' . $qty . ')';

                                        if ($qty === 0) {
                                            $badgeClass = 'stock-out';
                                            $statusText = 'Sold Out';
                                        } elseif ($qty < 5) {
                                            $badgeClass = 'stock-low';
                                            $statusText = 'Low Stock (' . $qty . ')';
                                        }
                                        ?>
                                        <span class="stock-badge <?php echo $badgeClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="button" class="action-btn"
                                            style="background: rgba(255, 255, 255, 0.1); color: #ccc; margin-right: 8px; border: none; cursor: pointer;"
                                            onclick='openProductModal(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>View</button>
                                        <a href="edit_product.php?id=<?php echo $p['id']; ?>"
                                            class="action-btn btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $p['id']; ?>" class="action-btn btn-delete"
                                            title="Remove Product"
                                            onclick="return confirm('Are you sure you want to remove this product? This action cannot be undone.');">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path d="M3 6h18"></path>
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                </path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Preview Modal -->
    <div id="productModal"
        style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.85); backdrop-filter: blur(8px);">
        <div class="glass-panel"
            style="margin: 5% auto; width: 90%; max-width: 800px; padding: 0; position: relative; animation: fadeIn 0.3s ease-out;">
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
                        style="color: var(--accent-gold); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 0.5rem;">
                    </p>
                    <h2 id="modal-name"
                        style="font-size: 2.5rem; margin-bottom: 0.5rem; font-family: 'Playfair Display', serif;">
                    </h2>
                    <p id="modal-varietal" style="font-size: 1.2rem; color: #aaa; margin-bottom: 1.5rem;"></p>

                    <div style="display: flex; gap: 2rem; margin-bottom: 2rem; align-items: center;">
                        <span id="modal-price" style="font-size: 2rem; font-weight: 700; color: #fff;"></span>
                        <span id="modal-stock" class="stock-badge"></span>
                    </div>

                    <p id="modal-desc"
                        style="color: #ccc; line-height: 1.6; margin-bottom: 2rem; max-height: 150px; overflow-y: auto;">
                    </p>

                    <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px;">
                        <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #888;">Product Status</h4>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Visibility: <strong style="color: white;">Public</strong></span>
                            <span>Seller ID: <strong style="color: white;">
                                    <?php echo $user_id; ?>
                                </strong></span>
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
            document.getElementById('modal-price').innerText = '$' + parseFloat(data.price).toFixed(2);
            document.getElementById('modal-desc').innerText = data.description;

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
                let src = data.image_path;
                if (!src.startsWith('uploads/')) src = 'uploads/' + src;
                // Fix path relative to seller/ folder which is one level deep
                // Actually the PHP loop used ../uploads logic.
                // But here we are setting src for the img tag. The brower reads it from current URL seller/something
                // So we need ../uploads/
                imgEl.src = '../' + (data.image_path.startsWith('uploads/') ? data.image_path : 'uploads/' + data.image_path);

                imgEl.style.display = 'block';
                phEl.style.display = 'none';
            } else {
                imgEl.src = src;
                phEl.style.display = 'none';
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