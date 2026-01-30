<?php
include 'includes/header.php';
include 'includes/db.php';

// Get Product ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT 
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
                               WHERE p.id = ? AND p.is_deleted = 0");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error loading product.";
    }
}

if (!$product) {
    echo "<main style='padding-top: 150px; text-align: center;'>
            <div class='container'>
                <h1>Product Not Found</h1>
                <p>The wine you are looking for does not exist or has been moved.</p>
                <a href='products.php' class='btn btn-primary' style='margin-top: 20px;'>Back to Collection</a>
            </div>
          </main>";
    include 'includes/footer.php';
    exit;
}
?>

<main style="padding-top: 100px; padding-bottom: var(--spacing-xl);">
    <div class="container">
        <div style="margin-bottom: 2rem;">
            <a href="products.php" style="color: var(--color-text-muted); font-size: 0.9rem;">&larr; Back to
                Collection</a>
        </div>

        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: var(--spacing-xl); align-items: start;">

            <!-- Visual Side -->
            <div class="glass-card animate-on-scroll" style="padding: 0; overflow: hidden; ">
                <div
                    style="height: 500px; background: #1a1a1a; display: flex; align-items: center; justify-content: center; position: relative;">

                    <?php 
                        $img_src = '';
                        $is_default = false;
                        if (!empty($product['image_path'])) {
                            $img_src = (strpos($product['image_path'], 'uploads/') === 0) ? $product['image_path'] : 'uploads/' . $product['image_path'];
                        } else {
                            $is_default = true;
                            $type = strtolower($product['type']);
                            if (strpos($type, 'red') !== false) $img_src = 'assets/images/defaults/red_default.png';
                            elseif (strpos($type, 'white') !== false) $img_src = 'assets/images/defaults/white_default.png';
                            elseif (strpos($type, 'rose') !== false) $img_src = 'assets/images/defaults/rose_default.png';
                            elseif (strpos($type, 'sparkling') !== false) $img_src = 'assets/images/defaults/sparkling_default.png';
                            else $img_src = 'assets/images/defaults/red_default.png';
                        }
                    ?>
                    <img src="<?php echo htmlspecialchars($img_src); ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                    <?php if ($is_default): ?>
                        <div class="default-badge">House Placeholder Image</div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Details Side -->
            <div class="animate-on-scroll" style=" transition-delay: 0.2s;">
                <p
                    style="color: var(--color-accent); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 0.5rem;">
                    <?php echo $product['year']; ?> Vintage
                </p>
                <h1 style="font-size: 3.5rem; margin-bottom: 0.5rem; line-height: 1.1;">
                    <?php echo $product['name']; ?>
                </h1>
                <p style="font-size: 1rem; color: #888; margin-bottom: 1rem;">
                    Sold by: <span
                        style="color: var(--color-accent); font-weight: bold;"><?php echo htmlspecialchars($product['brand_name'] ?? 'LM Hard Wine (Official)'); ?></span>
                </p>
                <p style="font-size: 1.5rem; color: var(--color-text-muted); margin-bottom: 2rem;">
                    <?php echo $product['varietal']; ?>
                </p>

                <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #ccc;">
                        <?php echo $product['desc']; ?>
                    </p>
                </div>

                <?php
                $hidePurchase = false;
                if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
                    $hidePurchase = true;
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller')
                    $hidePurchase = true;

                if (!$hidePurchase): ?>
                    <?php if ($product['stock_qty'] > 0): ?>
                        <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem;">
                            <span style="font-size: 2.5rem; font-weight: 700;">₱
                                <?php echo $product['price']; ?>
                            </span>

                            <div
                                style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border-radius: 4px;">
                                <button onclick="document.getElementById('qty-input').stepDown()"
                                    style="background: none; border: none; color: white; padding: 10px 15px; cursor: pointer; font-size: 1.2rem;">-</button>
                                <input type="number" id="qty-input" value="1" min="1" max="<?php echo $product['stock_qty']; ?>"
                                    onchange="if(this.value > <?php echo $product['stock_qty']; ?>) this.value = <?php echo $product['stock_qty']; ?>; if(this.value < 1) this.value = 1;"
                                    style="background: none; border: none; color: white; width: 40px; text-align: center; font-size: 1rem;">
                                <button onclick="document.getElementById('qty-input').stepUp()"
                                    style="background: none; border: none; color: white; padding: 10px 15px; cursor: pointer; font-size: 1.2rem;">+</button>
                            </div>
                        </div>
                        <p style="color: #d4af37; font-size: 0.9rem; margin-bottom: 1rem;">
                            Only <?php echo $product['stock_qty']; ?> bottles left!
                        </p>
                        <button
                            onclick="Cart.add(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo $product['type']; ?>', document.getElementById('qty-input').value)"
                            class="btn btn-primary" style="width: 100%; max-width: 300px; font-size: 1.1rem; cursor: pointer;">
                            Add to Cellar
                        </button>
                    <?php else: ?>
                        <div style="margin-bottom: 2rem;">
                            <span
                                style="font-size: 2.5rem; font-weight: 700; color: #666;">₱<?php echo $product['price']; ?></span>
                        </div>
                        <button disabled class="btn"
                            style="width: 100%; border-color: #666; color: #666; cursor: not-allowed;">Sold Out</button>
                    <?php endif; ?>
                <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div style="margin-bottom: 2rem;">
                        <span style="font-size: 2.5rem; font-weight: 700;">₱<?php echo $product['price']; ?></span>
                    </div>
                    <a href="admin/edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary"
                        style="display: inline-block; text-decoration: none;">Edit this Product</a>
                    <div class="alert-error"
                        style="display:inline-block; margin: 1rem 0 0 0; background: rgba(114, 14, 30, 0.2); border-color: rgba(114, 14, 30, 0.3);">
                        Administrator Mode: Some shopper features are hidden.
                    </div>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'seller'): ?>
                    <div style="margin-bottom: 2rem;">
                        <span style="font-size: 2.5rem; font-weight: 700;">₱<?php echo $product['price']; ?></span>
                    </div>
                    <div class="alert-error" style="display:inline-block; margin-bottom: 0;">
                        You are viewing this product as a Seller.
                    </div>
                    <br><br>
                    <a href="seller/index.php" class="btn" style="border: 1px solid #666;">Back to Dashboard</a>
                <?php endif; ?>

                <div style="margin-top: 3rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <h4 style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">Alcohol
                        </h4>
                        <p>14.5%</p>
                    </div>
                    <div>
                        <h4 style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">Region
                        </h4>
                        <p>Volcanic Valley</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>