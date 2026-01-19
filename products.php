<?php
include 'includes/header.php';
include 'includes/db.php';

$filter = isset($_GET['type']) ? $_GET['type'] : 'All';
$filtered_products = [];

try {
    $sql = "SELECT 
                id, 
                name, 
                type, 
                varietal, 
                price, 
                vintage_year as year, 
                description as `desc`, 
                color_style as color 
            FROM products";

    if ($filter != 'All') {
        $sql .= " WHERE type = :type";
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
                <div class="glass-card animate-on-scroll"
                    style="">

                    <a href="product-details.php?id=<?php echo $product['id']; ?>"
                        style="text-decoration: none; color: inherit;">
                        <!-- Image Placeholder -->
                        <div
                            style="height: 300px; background: #1a1a1a; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px; position: relative; overflow: hidden; transition: transform 0.3s ease;">
                            <span style="font-size: 3rem; opacity: 0.1; font-weight: 700; text-transform: uppercase;">
                                <?php echo $product['type']; ?>
                            </span>
                            <div style="position: absolute; inset: 0; background: <?php echo $product['color']; ?>;"></div>
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
                            <span style="font-size: 1.25rem; font-weight: 600;">$
                                <?php echo $product['price']; ?>
                            </span>
                        </div>
                    </a>

                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; min-height: 3em;">
                        <?php echo $product['desc']; ?>
                    </p>

                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn"
                        style="width: 100%; text-align: center;">View Details</a>
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
