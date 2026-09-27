<?php

require_once __DIR__ . '/../includes/admin.php';

require_admin();

$pdo = db();

$id = (int)($_GET['id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Get order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT
        o.*,
        u.name AS customer_name,
        u.email AS customer_email,
        u.phone AS customer_phone,
        a.street,
        a.city,
        a.postal_code,
        p.status AS payment_status,
        p.method AS payment_method,
        p.paid_at
     FROM `order` o
     JOIN `user` u
       ON u.user_id = o.user_id
     LEFT JOIN address a
       ON a.address_id = o.address_id
     LEFT JOIN payment p
       ON p.order_id = o.order_id
     WHERE o.order_id = ?
       AND o.status IN ('confirmed', 'shipped', 'delivered')"
);

$stmt->execute([$id]);

$order = $stmt->fetch();


if (!$order) {

    http_response_code(404);

    exit('Order not found or this order is not available in the admin panel.');
}


/*
|--------------------------------------------------------------------------
| Get order items
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT
        oi.*,
        p.name,
        p.image_url
     FROM order_item oi
     JOIN product p
       ON p.product_id = oi.product_id
     WHERE oi.order_id = ?"
);

$stmt->execute([$id]);

$items = $stmt->fetchAll();


$page_title = 'Admin Order #' . $id;

require __DIR__ . '/header.php';

?>

<div class="container section">

    <a
        class="back-link"
        href="<?= e(app_url('admin/orders.php')) ?>">
        ← Back to orders
    </a>


    <div
        class="section-head"
        style="margin-top:18px">

        <div>

            <span class="eyebrow">
                Order #<?= e((string)$id) ?>
            </span>

            <h1>
                Order details
            </h1>

        </div>


        <span
            class="status status-<?= e($order['status']) ?>">
            <?= e($order['status']) ?>
        </span>

    </div>


    <div
        class="cart-layout"
        style="padding-top:0">


        <!-- =====================================================
             CUSTOMER + ITEMS
        ====================================================== -->

        <section class="panel">

            <h3>
                Customer
            </h3>


            <div class="summary-row">

                <span>
                    Name
                </span>

                <strong>
                    <?= e($order['customer_name']) ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Email
                </span>

                <span>
                    <?= e($order['customer_email']) ?>
                </span>

            </div>


            <div class="summary-row">

                <span>
                    Phone
                </span>

                <span>
                    <?= e(
                        $order['customer_phone'] ?: '—'
                    ) ?>
                </span>

            </div>


            <h3 style="margin-top:28px">
                Items
            </h3>


            <?php foreach ($items as $item): ?>

                <div class="cart-item">

                    <img
                        src="<?= e(
                                    safe_image(
                                        $item['image_url']
                                    )
                                ) ?>"
                        alt="<?= e($item['name']) ?>">


                    <div>

                        <strong>
                            <?= e($item['name']) ?>
                        </strong>


                        <div class="muted">

                            Qty
                            <?= e(
                                (string)$item['quantity']
                            ) ?>

                            ·

                            <?= e(
                                money(
                                    (float)$item['unit_price']
                                )
                            ) ?>

                        </div>


                        <?php if ($item['inscription']): ?>

                            <div
                                class="notice"
                                style="margin-top:8px">

                                Inscription:
                                <?= e(
                                    $item['inscription']
                                ) ?>

                            </div>

                        <?php endif; ?>

                    </div>


                    <strong>

                        <?= e(
                            money(
                                (float)$item['unit_price']
                                    *
                                    (int)$item['quantity']
                            )
                        ) ?>

                    </strong>

                </div>

            <?php endforeach; ?>

        </section>


        <!-- =====================================================
             FULFILMENT
        ====================================================== -->

        <aside class="panel">

            <h3>
                Fulfilment
            </h3>


            <div class="summary-row">

                <span>
                    Payment
                </span>

                <span>
                    <?= e(
                        $order['payment_status'] ?? '—'
                    ) ?>
                </span>

            </div>


            <div class="summary-row">

                <span>
                    Method
                </span>

                <span>
                    <?= e(
                        $order['payment_method'] ?? '—'
                    ) ?>
                </span>

            </div>


            <div class="summary-row">

                <span>
                    Total
                </span>

                <strong>
                    <?= e(
                        money(
                            (float)$order['total_amount']
                        )
                    ) ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Delivery
                </span>

                <span>

                    <?= e(
                        $order['delivery_type'] === 'local_pickup'
                            ? 'Local pickup'
                            : 'Nationwide shipping'
                    ) ?>

                </span>

            </div>


            <?php if (
                $order['delivery_type'] !== 'local_pickup'
            ): ?>

                <div
                    class="notice"
                    style="margin-top:12px">

                    <?= e(
                        $order['street'] ?? '—'
                    ) ?>

                    <br>

                    <?= e(
                        trim(
                            ($order['city'] ?? '') .
                                ' ' .
                                ($order['postal_code'] ?? '')
                        )
                    ) ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 STATUS
            ================================================== -->

            <h3 style="margin-top:28px">
                Update status
            </h3>


            <?php if ($order['status'] === 'confirmed'): ?>

                <form
                    method="post"
                    action="<?= e(
                                app_url(
                                    'admin/update_order.php'
                                )
                            ) ?>">

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= e(csrf_token()) ?>">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= e((string)$id) ?>">

                    <input
                        type="hidden"
                        name="status"
                        value="shipped">

                    <button
                        class="btn btn-block"
                        type="submit">
                        Mark as shipped
                    </button>

                </form>


                <p
                    class="muted"
                    style="margin-top:8px">
                    Use this after the shop has handed the order
                    to the delivery service.
                </p>


            <?php elseif ($order['status'] === 'shipped'): ?>

                <form
                    method="post"
                    action="<?= e(
                                app_url(
                                    'admin/update_order.php'
                                )
                            ) ?>">

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= e(csrf_token()) ?>">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= e((string)$id) ?>">

                    <input
                        type="hidden"
                        name="status"
                        value="delivered">

                    <button
                        class="btn btn-block"
                        type="submit">
                        Mark as delivered
                    </button>

                </form>


                <p
                    class="muted"
                    style="margin-top:8px">
                    After this, the customer becomes eligible
                    to review purchased products.
                </p>


            <?php elseif ($order['status'] === 'delivered'): ?>

                <div class="notice">

                    This order has been delivered.
                    Customer reviews are now enabled for
                    its products.

                </div>

            <?php endif; ?>

        </aside>

    </div>

</div>

<?php

require __DIR__ . '/footer.php';

?>