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

<style>
    body {
        padding-top: 80px;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 1rem;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        color: var(--color-text-main);
    }

    .products-table th,
    .products-table td {
        text-align: left;
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .products-table th {
        color: var(--color-text-muted);
        text-transform: uppercase;
        font-size: 0.8rem;
    }

    .products-table tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .product-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        background: #333;
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
</style>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header">
            <div>
                <h1 style="font-family: 'Playfair Display', serif;">My Products</h1>
                <p style="color: var(--color-text-muted);">Manage your wine collection.</p>
            </div>
            <a href="add_product.php" class="btn btn-primary">+ Add New Product</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <p style="color: var(--color-text-muted); margin-bottom: 1rem;">You haven't added any products yet.</p>
                    <a href="add_product.php" class="btn btn-primary">Add Your First Wine</a>
                </div>
            <?php else: ?>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['image_path']):
                                        $display_path = strpos($p['image_path'], 'uploads/') === 0 ? '../' . $p['image_path'] : '../uploads/' . $p['image_path'];
                                        ?>
                                        <img src="<?php echo htmlspecialchars($display_path); ?>" class="product-thumb"
                                            alt="Product">
                                    <?php else: ?>
                                        <div class="product-thumb"></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 600;">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </td>
                                <td class="text-accent">$
                                    <?php echo number_format($p['price'], 2); ?>
                                </td>
                                <td>
                                    <?php echo $p['stock_qty']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['type']); ?>
                                </td>
                                <td>
                                    <!-- Using edit_product.php (need to make sure it handles seller checks) -->
                                    <a href="edit_product.php?id=<?php echo $p['id']; ?>"
                                        style="color: white; margin-right: 10px;">Edit</a>
                                    <a href="?delete=<?php echo $p['id']; ?>" style="color: #f44336;"
                                        onclick="return confirm('Are you sure you want to remove this product?');">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>