<?php

require_once __DIR__ . '/../includes/admin.php';

require_admin();

$pdo = db();

/*
|--------------------------------------------------------------------------
| Admin can only see these statuses
|--------------------------------------------------------------------------
*/

$allowed = [
    'confirmed',
    'shipped',
    'delivered'
];

$filter = $_GET['status'] ?? '';

$sql = "
    SELECT
        o.order_id,
        o.total_amount,
        o.delivery_type,
        o.status,
        o.placed_at,
        u.name AS customer_name,
        u.email AS customer_email,
        p.status AS payment_status
    FROM `order` o
    JOIN `user` u
      ON u.user_id = o.user_id
    LEFT JOIN payment p
      ON p.order_id = o.order_id
    WHERE o.status IN ('confirmed', 'shipped', 'delivered')
";

$params = [];


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

if (in_array($filter, $allowed, true)) {

    $sql .= " AND o.status = ?";

    $params[] = $filter;
}

$sql .= "
    ORDER BY o.placed_at DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$orders = $stmt->fetchAll();


$page_title = 'Manage Orders';

require __DIR__ . '/header.php';

?>

<div class="container section">

    <div class="section-head">

        <div>

            <span class="eyebrow">
                Administration
            </span>

            <h1>
                Orders
            </h1>

            <p class="muted">
                Track confirmed, shipped and delivered orders.
            </p>

        </div>

    </div>


    <!-- =========================================================
         FILTERS
    ========================================================== -->

    <div
        class="admin-nav"
        style="margin-bottom:20px">

        <a
            class="btn btn-small"
            href="<?= e(
                        app_url('admin/orders.php')
                    ) ?>">
            All
        </a>


        <?php foreach ($allowed as $status): ?>

            <a
                class="btn btn-small"
                href="<?= e(
                            app_url(
                                'admin/orders.php?status=' .
                                    $status
                            )
                        ) ?>">
                <?= e(ucfirst($status)) ?>
            </a>

        <?php endforeach; ?>

    </div>


    <!-- =========================================================
         ORDERS TABLE
    ========================================================== -->

    <div
        class="panel"
        style="overflow-x:auto">

        <?php if (!$orders): ?>

            <div class="empty">

                <h3>
                    No orders found
                </h3>

                <p class="muted">
                    There are no confirmed, shipped or delivered
                    orders matching this filter.
                </p>

            </div>

        <?php else: ?>

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>
                            Order
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Payment
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Placed
                        </th>

                        <th>
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($orders as $o): ?>

                        <tr>

                            <td>

                                <strong>
                                    #<?= e(
                                            (string)$o['order_id']
                                        ) ?>
                                </strong>

                            </td>


                            <td>

                                <?= e($o['customer_name']) ?>

                                <br>

                                <span class="muted">
                                    <?= e($o['customer_email']) ?>
                                </span>

                            </td>


                            <td>

                                <?= e(
                                    $o['payment_status'] ?? '—'
                                ) ?>

                            </td>


                            <td>

                                <?= e(
                                    money(
                                        (float)$o['total_amount']
                                    )
                                ) ?>

                            </td>


                            <td>

                                <span
                                    class="status status-<?= e(
                                                                $o['status']
                                                            ) ?>">
                                    <?= e($o['status']) ?>
                                </span>

                            </td>


                            <td>

                                <?= e(
                                    date(
                                        'd M Y, h:i A',
                                        strtotime(
                                            $o['placed_at']
                                        )
                                    )
                                ) ?>

                            </td>


                            <td>

                                <a
                                    class="back-link"
                                    href="<?= e(
                                                app_url(
                                                    'admin/order.php?id=' .
                                                        $o['order_id']
                                                )
                                            ) ?>">
                                    View
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

</div>

<?php

require __DIR__ . '/footer.php';

?>