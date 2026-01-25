<?php
include 'includes/header.php';
include 'includes/db.php';

// Gatekeeping: Redirect Sellers
if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller') {
    header('Location: seller/index.php');
    exit;
}

$filter = isset($_GET['type']) ? $_GET['type'] : 'All';
$filtered_products = [];

try {
    $sql = "SELECT 
                p.id, 
                p.name, 
                p.type, 
                p.varietal, 
                p.price, 
                p.stock_qty, 
                p.vintage_year as year, 
                p.description as `desc`, 
                p.color_style as color,
                p.image_path,
                sp.brand_name
            FROM products p
            LEFT JOIN seller_profiles sp ON p.seller_id = sp.user_id 
            WHERE p.is_deleted = 0";

    if ($filter != 'All') {
        $sql .= " AND type = :type";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['type' => $filter]);
    } else {
        $stmt = $pdo->query($sql);
    }

    $filtered_products = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<div class='container' style='padding: 20px; color: red;'>Error fetching products: " . $e->getMessage() . "</div>";
}
?>

<main style="padding-top: 100px; padding-bottom: var(--spacing-xl);">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center fade-in" style="margin-bottom: var(--spacing-lg);">
            <h1 style="font-size: 3rem;">The Collection</h1>
            <p style="color: var(--color-text-muted);">Curated for intensity and depth.</p>
        </div>

        <!-- Filters -->
        <div class="fade-in"
            style="display: flex; justify-content: center; gap: 1rem; margin-bottom: var(--spacing-lg);">
            <a href="products.php?type=All" class="btn <?php echo $filter == 'All' ? 'btn-primary' : ''; ?>"
                style="padding: 8px 24px; font-size: 0.9rem;">All</a>
            <a href="products.php?type=Red" class="btn <?php echo $filter == 'Red' ? 'btn-primary' : ''; ?>"
                style="padding: 8px 24px; font-size: 0.9rem;">Red</a>
            <a href="products.php?type=White" class="btn <?php echo $filter == 'White' ? 'btn-primary' : ''; ?>"
                style="padding: 8px 24px; font-size: 0.9rem;">White</a>
        </div>

        <!-- Product Grid -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-md);">
            <?php foreach ($filtered_products as $product): ?>
                <div class="glass-card animate-on-scroll" style="">

                    <a href="product-details.php?id=<?php echo $product['id']; ?>"
                        style="text-decoration: none; color: inherit;">
                        <!-- Image Placeholder -->
                        <div
                            style="height: 300px; background: #1a1a1a; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px; position: relative; overflow: hidden; transition: transform 0.3s ease;">

                            <?php if ($product['stock_qty'] <= 0): ?>
                                <div style="
            position: absolute; 
            top: 10px; 
            right: 10px; 
            background: #ff4444; 
            color: white; 
            padding: 5px 10px; 
            font-weight: bold; 
            z-index: 10; 
            border-radius: 4px;
            font-size: 0.8rem;
            text-transform: uppercase;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        ">Sold Out</div>
                            <?php endif; ?>
                            <?php if (!empty($product['image_path'])):
                                $img_src = (strpos($product['image_path'], 'uploads/') === 0) ? $product['image_path'] : 'uploads/' . $product['image_path'];
                                ?>
                                <img src="<?php echo htmlspecialchars($img_src); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <span style="font-size: 3rem; opacity: 0.1; font-weight: 700; text-transform: uppercase;">
                                    <?php echo $product['type']; ?>
                                </span>
                                <div style="position: absolute; inset: 0; background: <?php echo $product['color']; ?>;"></div>
                            <?php endif; ?>

                        </div>

                        <!-- Content -->
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div>
                                <h3 style="font-size: 1.5rem; margin-bottom: 0.25rem;">
                                    <?php echo $product['name']; ?>
                                </h3>
                                <p style="color: var(--color-accent); font-size: 0.9rem;">
                                    <?php echo $product['varietal']; ?> •
                                    <?php echo $product['year']; ?>
                                </p>
                            </div>
                            <span style="font-size: 1.25rem; font-weight: 600;">₱
                                <?php echo $product['price']; ?>
                            </span>
                        </div>

                        <p style="font-size: 0.8rem; color: #888; margin-bottom: 0.5rem; margin-top: -0.5rem;">
                            Sold by: <span
                                style="color: #ccc;"><?php echo htmlspecialchars($product['brand_name'] ?? 'LM Hard Wine (Official)'); ?></span>
                        </p>
                    </a>

                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; min-height: 3em;">
                        <?php echo $product['desc']; ?>
                    </p>

                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn"
                            style="flex: 1; text-align: center; font-size: 0.8rem; padding: 10px 0;">Details</a>

                        <?php
                        $hidePurchase = false;
                        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
                            $hidePurchase = true;
                        if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller')
                            $hidePurchase = true;

                        if (!$hidePurchase): ?>
                            <?php if ($product['stock_qty'] > 0): ?>
                                <button
                                    onclick="Cart.add(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo $product['type']; ?>')"
                                    class="btn btn-primary"
                                    style="flex: 1; text-align: center; font-size: 0.8rem; padding: 10px 0; border: none; cursor: pointer;">
                                    Add
                                </button>
                            <?php else: ?>
                                <button disabled class="btn"
                                    style="flex: 1; text-align: center; font-size: 0.8rem; padding: 10px 0; border: 1px solid #444; color: #666; background: transparent; cursor: not-allowed;">
                                    Sold Out
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($filtered_products)): ?>
            <div class="text-center" style="padding: var(--spacing-lg);">
                <p>No wines found in this category.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>