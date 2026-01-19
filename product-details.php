<?php
include 'includes/header.php';
include 'includes/db.php';

// Get Product ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT 
                                    id, 
                                    name, 
                                    type, 
                                    varietal, 
                                    price, 
                                    vintage_year as year, 
                                    description as `desc`, 
                                    color_style as color 
                               FROM products WHERE id = ?");
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
            <div class="glass-card animate-on-scroll"
                style="padding: 0; overflow: hidden; opacity: 0; transform: translateY(20px); transition: 1s ease;">
                <div
                    style="height: 500px; background: #1a1a1a; display: flex; align-items: center; justify-content: center; position: relative;">
                    <span style="font-size: 6rem; opacity: 0.1; font-weight: 700; text-transform: uppercase;">
                        <?php echo $product['type']; ?>
                    </span>
                    <div style="position: absolute; inset: 0; background: <?php echo $product['color']; ?>;"></div>
                </div>
            </div>

            <!-- Details Side -->
            <div class="animate-on-scroll"
                style="opacity: 0; transform: translateY(20px); transition: 1s ease; transition-delay: 0.2s;">
                <p
                    style="color: var(--color-accent); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 0.5rem;">
                    <?php echo $product['year']; ?> Vintage
                </p>
                <h1 style="font-size: 3.5rem; margin-bottom: 0.5rem; line-height: 1.1;">
                    <?php echo $product['name']; ?>
                </h1>
                <p style="font-size: 1.5rem; color: var(--color-text-muted); margin-bottom: 2rem;">
                    <?php echo $product['varietal']; ?>
                </p>

                <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #ccc;">
                        <?php echo $product['desc']; ?>
                    </p>
                </div>

                <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem;">
                    <span style="font-size: 2.5rem; font-weight: 700;">$
                        <?php echo $product['price']; ?>
                    </span>

                    <div
                        style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border-radius: 4px;">
                        <button
                            style="background: none; border: none; color: white; padding: 10px 15px; cursor: pointer; font-size: 1.2rem;">-</button>
                        <input type="number" value="1"
                            style="background: none; border: none; color: white; width: 40px; text-align: center; font-size: 1rem;">
                        <button
                            style="background: none; border: none; color: white; padding: 10px 15px; cursor: pointer; font-size: 1.2rem;">+</button>
                    </div>
                </div>

                <button class="btn btn-primary" style="width: 100%; max-width: 300px; font-size: 1.1rem;">Add to
                    Cellar</button>

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