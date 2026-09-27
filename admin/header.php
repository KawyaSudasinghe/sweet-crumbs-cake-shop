<?php
require_once __DIR__ . '/../includes/admin.php';
$admin = require_admin();
$page_title = $page_title ?? 'Admin';
$flashes = get_flashes();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> | Sweet Crumbs Admin</title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
    <style>
        .admin-nav{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
        .admin-nav a{padding:8px 12px;border-radius:10px}
        .admin-nav a:hover{background:#eef6ff}
        .admin-shell{border-bottom:1px solid #e7edf5;background:#fff}
        .admin-table{width:100%;border-collapse:collapse}
        .admin-table th,.admin-table td{padding:12px 10px;border-bottom:1px solid #edf1f5;text-align:left;vertical-align:top}
        .admin-table th{font-size:.82rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b}
        .status-confirmed{background:#e8f3ff}
        .status-shipped{background:#eef4ff}
        .status-delivered{background:#eaf8ef}
        .status-cancelled{background:#fff0f0}
    </style>
</head>
<body>
<header class="site-header admin-shell">
    <div class="container nav-wrap">
        <a class="brand" href="<?= e(app_url('admin/index.php')) ?>">
            <div class="brand-mark"></div>
            <span>Sweet Crumbs<small>Administrator</small></span>
        </a>
        <nav class="admin-nav">
            <a href="<?= e(app_url('admin/index.php')) ?>">Dashboard</a>
            <a href="<?= e(app_url('admin/orders.php')) ?>">Orders</a>
            <a href="<?= e(app_url('index.php')) ?>">Shop</a>
            <a href="<?= e(app_url('admin/logout.php')) ?>">Logout</a>
        </nav>
    </div>
</header>
<main>
<?php foreach ($flashes as $flash): ?>
    <div class="container flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
