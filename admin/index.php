<?php

require_once __DIR__ . '/../includes/admin.php';

$admin = require_admin();
$pdo = db();

/*
|--------------------------------------------------------------------------
| Admin-visible order statistics
|--------------------------------------------------------------------------
| Pending and cancelled orders are intentionally excluded.
*/

$stats = [
    'confirmed' => 0,
    'shipped'   => 0,
    'delivered' => 0,
];

$rows = $pdo->query(
    "SELECT status, COUNT(*) AS total
     FROM `order`
     WHERE status IN ('confirmed', 'shipped', 'delivered')
     GROUP BY status"
)->fetchAll();

foreach ($rows as $row) {
    if (array_key_exists($row['status'], $stats)) {
        $stats[$row['status']] = (int)$row['total'];
    }
}

/*
|--------------------------------------------------------------------------
| Revenue
|--------------------------------------------------------------------------
*/

$revenue = (float)$pdo->query(
    "SELECT COALESCE(SUM(o.total_amount), 0)
     FROM `order` o
     JOIN payment p
       ON p.order_id = o.order_id
     WHERE p.status = 'success'
       AND o.status IN ('confirmed', 'shipped', 'delivered')"
)->fetchColumn();

/*
|--------------------------------------------------------------------------
| Recent admin-visible orders
|--------------------------------------------------------------------------
*/

$recent = $pdo->query(
    "SELECT
        o.order_id,
        o.total_amount,
        o.status,
        o.placed_at,
        u.name AS customer_name,
        u.email AS customer_email
     FROM `order` o
     JOIN `user` u
       ON u.user_id = o.user_id
     WHERE o.status IN ('confirmed', 'shipped', 'delivered')
     ORDER BY o.placed_at DESC
     LIMIT 8"
)->fetchAll();

$page_title = 'Admin Dashboard';

require __DIR__ . '/header.php';

?>

<div class="container section">

    <div class="section-head">

        <div>

            <span class="eyebrow">
                Administration
            </span>

            <h1>Dashboard</h1>

            <p class="muted">
                Welcome, <?= e($admin['name']) ?>.
            </p>

        </div>

        <a
            class="btn"
            href="<?= e(app_url('admin/orders.php')) ?>"
        >
            Manage orders
        </a>

    </div>


    <!-- =========================================================
         ORDER STATISTICS
    ========================================================== -->

    <div
        class="category-grid"
        style="margin-bottom:28px"
    >

        <div class="panel">

            <span class="muted">
                Confirmed
            </span>

            <h2>
                <?= e((string)$stats['confirmed']) ?>
            </h2>

        </div>


        <div class="panel">

            <span class="muted">
                Shipped
            </span>

            <h2>
                <?= e((string)$stats['shipped']) ?>
            </h2>

        </div>


        <div class="panel">

            <span class="muted">
                Delivered
            </span>

            <h2>
                <?= e((string)$stats['delivered']) ?>
            </h2>

        </div>

    </div>


    <!-- =========================================================
         REVENUE
    ========================================================== -->

    <div
        class="panel"
        style="margin-bottom:24px"
    >

        <div class="summary-row">

            <span>
                Total paid order value
            </span>

            <strong>
                <?= e(money($revenue)) ?>
            </strong>

        </div>

    </div>


    <!-- =========================================================
         RECENT ORDERS
    ========================================================== -->

    <section class="panel">

        <div
            class="section-head"
            style="margin-bottom:14px"
        >

            <div>

                <h3>
                    Recent orders
                </h3>

                <p class="muted">
                    Confirmed, shipped and delivered orders.
                </p>

            </div>

            <a
                class="back-link"
                href="<?= e(app_url('admin/orders.php')) ?>"
            >
                View all
            </a>

        </div>


        <?php if (!$recent): ?>

            <div class="empty">

                <p class="muted">
                    No orders available.
                </p>

            </div>

        <?php else: ?>

            <?php foreach ($recent as $order): ?>

                <div
                    class="summary-row"
                    style="align-items:center"
                >

                    <div>

                        <strong>
                            Order #<?= e((string)$order['order_id']) ?>
                        </strong>

                        <div class="muted">

                            <?= e($order['customer_name']) ?>

                            ·

                            <?= e(
                                date(
                                    'd M Y, h:i A',
                                    strtotime($order['placed_at'])
                                )
                            ) ?>

                        </div>

                    </div>


                    <div style="text-align:right">

                        <span
                            class="status status-<?= e($order['status']) ?>"
                        >
                            <?= e($order['status']) ?>
                        </span>

                        <div>

                            <strong>
                                <?= e(
                                    money(
                                        (float)$order['total_amount']
                                    )
                                ) ?>
                            </strong>

                        </div>

                        <a
                            class="back-link"
                            href="<?= e(
                                app_url(
                                    'admin/order.php?id=' .
                                    $order['order_id']
                                )
                            ) ?>"
                        >
                            Open
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</div>

<?php

require __DIR__ . '/footer.php';

?>