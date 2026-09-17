<?php
require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? APP_NAME;

$user = current_user();

$flashes = get_flashes();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= e(app_url('index.php')) ?>">
            <span class="brand-mark">S</span>
            <span>Sweet Crumbs<small>Freshly made in Sri Lanka</small></span>
        </a>
        <button class="mobile-menu" type="button" aria-label="Open menu" data-mobile-menu><?= icon('menu') ?></button>
        <div class="header-search"><form action="<?= e(app_url('catalog/products.php')) ?>" method="get"><span><?= icon('search',18) ?></span><input name="search" placeholder="Search cakes, cupcakes..." aria-label="Search products"></form></div>
        <nav class="main-nav" data-nav>
            <a href="<?= e(app_url('index.php')) ?>">Shop</a>
            <a href="<?= e(app_url('catalog/products.php')) ?>">Products</a>
            <?php if ($user): ?>
                <a href="<?= e(app_url('cart/cart.php')) ?>" class="nav-icon" aria-label="Cart">
                    <?= icon('cart') ?><span>Cart</span><b class="cart-count"><?= cart_count() ?></b>
                </a>
                <a href="<?= e(app_url('auth/profile.php')) ?>" class="nav-icon"><?= icon('user') ?><span><?= e(explode(' ', $user['name'])[0]) ?></span></a>
                <a href="<?= e(app_url('auth/logout.php')) ?>">Logout</a>
            <?php else: ?>
                <a href="<?= e(app_url('auth/login.php')) ?>">Login</a>
                <a class="btn btn-small" href="<?= e(app_url('auth/register.php')) ?>">Create account</a>
            <?php endif;
 ?>
        </nav>
    </div>
</header>
<main>
<?php foreach ($flashes as $flash): ?>
    <div class="container flash <?= e($flash['type']) ?>" data-flash><?= e($flash['message']) ?></div>
<?php endforeach;
 ?>
