<?php
include 'includes/header.php';

// Mock Data Source - To be replaced by Database later
$products = [
    [
        'id' => 1,
        'name' => 'Crimson Impact',
        'type' => 'Red',
        'varietal' => 'Cabernet Sauvignon',
        'price' => 89,
        'year' => 2024,
        'desc' => 'A full-bodied giant with notes of dark cherry, leather, and smoked oak.',
        'color' => 'linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent)'
    ],
    [
        'id' => 2,
        'name' => 'Midnight Reserve',
        'type' => 'Red',
        'varietal' => 'Syrah Blend',
        'price' => 120,
        'year' => 2022,
        'desc' => 'Velvety texture meets intense spice. Aged in charred barrels.',
        'color' => 'linear-gradient(45deg, rgba(80, 80, 80, 0.1), transparent)'
    ],
    [
        'id' => 3,
        'name' => 'Liquid Gold',
        'type' => 'White',
        'varietal' => 'Chardonnay',
        'price' => 95,
        'year' => 2023,
        'desc' => 'Unexpectedly crisp with a steel backbone. Notes of granite and lemon zest.',
        'color' => 'linear-gradient(45deg, rgba(212, 175, 55, 0.1), transparent)'
    ],
    [
        'id' => 4,
        'name' => 'Obsidian Rose',
        'type' => 'Rose',
        'varietal' => 'Grenache',
        'price' => 75,
        'year' => 2024,
        'desc' => 'Dry, tart, and dangerously drinkable. Not your average summer water.',
        'color' => 'linear-gradient(45deg, rgba(255, 105, 180, 0.1), transparent)'
    ],
    [
        'id' => 5,
        'name' => 'Volcanic Ash',
        'type' => 'Red',
        'varietal' => 'Pinot Noir',
        'price' => 110,
        'year' => 2021,
        'desc' => 'Grown in volcanic soil, earthy and complex with a smokey finish.',
        'color' => 'linear-gradient(45deg, rgba(100, 30, 22, 0.1), transparent)'
    ],
    [
        'id' => 6,
        'name' => 'Frost Bite',
        'type' => 'White',
        'varietal' => 'Ice Wine',
        'price' => 150,
        'year' => 2023,
        'desc' => 'Sweetness with a sharp edge. Harvested at the first deep freeze.',
        'color' => 'linear-gradient(45deg, rgba(200, 240, 255, 0.1), transparent)'
    ]
];

// Simple Filter Logic (Mock)
$filter = isset($_GET['type']) ? $_GET['type'] : 'All';
$filtered_products = $products;

if ($filter != 'All') {
    $filtered_products = array_filter($products, function ($p) use ($filter) {
        return $p['type'] === $filter;
    });
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
                    style="opacity: 0; transform: translateY(20px); transition: 1s ease;">

                    <!-- Image Placeholder -->
                    <div
                        style="height: 300px; background: #1a1a1a; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px; position: relative; overflow: hidden;">
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

                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; min-height: 3em;">
                        <?php echo $product['desc']; ?>
                    </p>

                    <a href="#" class="btn" style="width: 100%; text-align: center;">Add to Cellar</a>
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