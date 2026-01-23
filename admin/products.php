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

// Fetch Products
$stmt = $pdo->query("SELECT * FROM products WHERE is_deleted = 0 ORDER BY id DESC");
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
                                        <img src="../uploads/<?php echo htmlspecialchars($p['image_path']); ?>"
                                            class="product-thumb">
                                    <?php else: ?>
                                        <div class="product-thumb" style="background: <?php echo $p['color_style']; ?>"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['type']); ?>
                                </td>
                                <td style="<?php echo ($p['stock_qty'] < 5) ? 'color: #ff9800; font-weight:bold;' : ''; ?>">
                                    <?php echo htmlspecialchars($p['stock_qty']); ?>
                                </td>
                                <td>$
                                    <?php echo number_format($p['price'], 2); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['vintage_year']); ?>
                                </td>
                                <td>
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
</body>

</html>