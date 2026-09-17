<?php
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Sweet moments, delivered beautifully';
$pdo = db();
$categories = $pdo->query('SELECT c.*, COUNT(p.product_id) AS product_count FROM category c LEFT JOIN product p ON p.category_id=c.category_id AND p.is_available=1 GROUP BY c.category_id ORDER BY c.category_id')->fetchAll();
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM product p JOIN category c ON c.category_id=p.category_id WHERE p.is_available=1 ORDER BY p.product_id LIMIT 12')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="hero hero-photo">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="hero-copy">
            <span class="eyebrow">Freshly baked, always special</span>
            <h1>Sweet moments,<br>delivered <em>beautifully.</em></h1>
            <p>Explore delicious cakes, cupcakes, puddings, cookies, cheesecakes and brownies — made with care for every celebration.</p>
            <div class="hero-actions">
                <a class="btn" href="<?= e(app_url('catalog/products.php')) ?>">Shop now <?= icon('arrow',18) ?></a>
                <a class="btn btn-light" href="#categories">View categories</a>
            </div>
        </div>
    </div>
</section>

<section class="section pastel-section" id="categories">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow soft-eyebrow">Sweet choices</span><h2>Shop by category</h2><p class="muted">Find the perfect treat for every mood and occasion.</p></div>
            <a class="back-link" href="<?= e(app_url('catalog/products.php')) ?>">View all <?= icon('arrow',18) ?></a>
        </div>
        <div class="category-grid category-photo-grid">
        <?php foreach ($categories as $cat): ?>
            <a class="category-card category-photo-card" href="<?= e(app_url('catalog/products.php?category=' . urlencode($cat['name']))) ?>">
                <img src="<?= e(safe_image($cat['image_url'])) ?>" alt="<?= e($cat['name']) ?>">
                <strong><?= e($cat['name']) ?></strong>
                <span><?= e((string)$cat['product_count']) ?>+ products</span>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section featured-section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow soft-eyebrow">Handpicked favourites</span><h2>Featured products</h2><p class="muted">A taste of what is waiting in the Sweet Crumbs catalogue.</p></div>
            <a class="back-link" href="<?= e(app_url('catalog/products.php')) ?>">Browse 75+ products <?= icon('arrow',18) ?></a>
        </div>
        <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card product-photo-card" data-product-card>
                <div class="product-image-wrap">
                    <img src="<?= e(safe_image($product['image_url'])) ?>" alt="<?= e($product['name']) ?>">
                    <span class="heart-badge"><?= icon('heart',18) ?></span>
                </div>
                <div class="product-body">
                    <span class="product-category"><?= e($product['category_name']) ?></span>
                    <h3><?= e($product['name']) ?></h3>
                    <p><?= e(mb_strimwidth($product['description'] ?? '',0,82,'…')) ?></p>
                    <div class="product-bottom"><span class="price"><?= e(money((float)$product['price'])) ?></span><a class="btn btn-small" href="<?= e(app_url('catalog/product.php?id=' . $product['product_id'])) ?>"><?= icon('cart',16) ?> Add to cart</a></div>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
