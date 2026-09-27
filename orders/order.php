<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$id = (int)($_GET['id'] ?? 0);

$pdo = db();


/*
|--------------------------------------------------------------------------
| Get order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    'SELECT
        o.*,
        a.street,
        a.city,
        a.postal_code,
        pc.code AS promo_code
     FROM `order` o
     LEFT JOIN address a
       ON a.address_id = o.address_id
     LEFT JOIN promo_code pc
       ON pc.promo_id = o.promo_id
     WHERE o.order_id = ?
       AND o.user_id = ?'
);

$stmt->execute([
    $id,
    $user['user_id']
]);

$order = $stmt->fetch();


if (!$order) {

    http_response_code(404);

    redirect('orders/orders.php');
}


/*
|--------------------------------------------------------------------------
| Get order items
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    'SELECT
        oi.*,
        p.name,
        p.image_url
     FROM order_item oi
     JOIN product p
       ON p.product_id = oi.product_id
     WHERE oi.order_id = ?'
);

$stmt->execute([
    $id
]);

$items = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Find products already reviewed
|--------------------------------------------------------------------------
*/

$reviewedProducts = [];

if (!empty($items)) {

    $productIds = array_map(
        fn($item) => (int)$item['product_id'],
        $items
    );

    $productIds = array_values(
        array_unique($productIds)
    );

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($productIds),
            '?'
        )
    );


    $reviewStmt = $pdo->prepare(
        "SELECT product_id
         FROM review
         WHERE user_id = ?
           AND product_id IN ($placeholders)"
    );


    $reviewStmt->execute(
        array_merge(
            [$user['user_id']],
            $productIds
        )
    );


    $reviewedProducts = array_map(
        'intval',
        $reviewStmt->fetchAll(
            PDO::FETCH_COLUMN
        )
    );
}


/*
|--------------------------------------------------------------------------
| Delivery information
|--------------------------------------------------------------------------
*/

if (
    $order['delivery_type'] ===
    'local_pickup'
) {

    $deliveryText = 'Local pickup';
} else {

    $addressParts = [];


    if (!empty($order['street'])) {

        $addressParts[] =
            trim($order['street']);
    }


    if (!empty($order['city'])) {

        $addressParts[] =
            trim($order['city']);
    }


    if (!empty($order['postal_code'])) {

        $addressParts[] =
            trim($order['postal_code']);
    }


    if (!empty($addressParts)) {

        $deliveryText =
            implode(
                ', ',
                $addressParts
            );
    } else {

        $deliveryText =
            'Nationwide shipping';
    }
}


$page_title =
    'Order #' . $id;

require __DIR__ . '/../includes/header.php';

?>

<div class="container section">


    <!-- =========================================================
         BACK LINK
    ========================================================== -->

    <a
        class="back-link"
        href="<?= e(
                    app_url(
                        'orders/orders.php'
                    )
                ) ?>">
        ← My orders
    </a>


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="section-head">

        <div>

            <span class="eyebrow">

                Order #<?= e(
                            (string)$id
                        ) ?>

            </span>

            <h2>
                Order details
            </h2>

        </div>


        <span
            class="status status-<?= e(
                                        $order['status']
                                    ) ?>">
            <?= e(
                strtoupper(
                    $order['status']
                )
            ) ?>
        </span>

    </div>


    <div
        class="cart-layout"
        style="padding-top:0">


        <!-- =====================================================
             ITEMS
        ====================================================== -->

        <section class="panel">

            <h3>
                Items
            </h3>


            <?php foreach ($items as $i): ?>

                <div class="cart-item">

                    <img
                        src="<?= e(
                                    safe_image(
                                        $i['image_url']
                                    )
                                ) ?>"
                        alt="<?= e(
                                    $i['name']
                                ) ?>">


                    <div>

                        <strong>
                            <?= e(
                                $i['name']
                            ) ?>
                        </strong>


                        <div class="muted">

                            Qty
                            <?= e(
                                (string)$i['quantity']
                            ) ?>

                            ·

                            <?= e(
                                money(
                                    (float)$i['unit_price']
                                )
                            ) ?>

                        </div>


                        <?php if (
                            !empty($i['inscription'])
                        ): ?>

                            <div
                                class="notice"
                                style="margin-top:8px">

                                Inscription:

                                <?= e(
                                    $i['inscription']
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <!-- =========================================
                             REVIEW
                        ========================================== -->

                        <?php if (
                            $order['status'] ===
                            'delivered'
                        ): ?>


                            <?php if (
                                in_array(
                                    (int)$i['product_id'],
                                    $reviewedProducts,
                                    true
                                )
                            ): ?>

                                <div
                                    class="muted"
                                    style="
                                        margin-top:10px;
                                        font-weight:600;
                                    ">
                                    ✓ Review submitted
                                </div>


                            <?php else: ?>

                                <a
                                    class="btn"
                                    style="
                                        display:inline-block;
                                        margin-top:10px;
                                    "
                                    href="<?= e(
                                                app_url(
                                                    'reviews/review.php?product_id=' .
                                                        (int)$i['product_id']
                                                )
                                            ) ?>">
                                    Write a Review
                                </a>

                            <?php endif; ?>

                        <?php endif; ?>

                    </div>


                    <strong>

                        <?= e(
                            money(
                                (float)$i['unit_price']
                                    *
                                    (int)$i['quantity']
                            )
                        ) ?>

                    </strong>

                </div>

            <?php endforeach; ?>

        </section>


        <!-- =====================================================
             ORDER SUMMARY
        ====================================================== -->

        <aside class="panel">

            <h3>
                Order summary
            </h3>


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
                    Payment
                </span>

                <span>
                    Stripe
                </span>

            </div>


            <div class="summary-row">

                <span>
                    Delivery
                </span>

                <span>
                    <?= e(
                        $deliveryText
                    ) ?>
                </span>

            </div>


            <?php if (
                !empty($order['promo_code'])
            ): ?>

                <div class="summary-row">

                    <span>
                        Promo
                    </span>

                    <span>
                        <?= e(
                            $order['promo_code']
                        ) ?>
                    </span>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 PENDING ORDER
            ================================================== -->

            <?php if (
                $order['status'] ===
                'pending'
            ): ?>

                <a
                    class="btn btn-block"
                    href="<?= e(
                                app_url(
                                    'payment/payment.php?order_id=' .
                                        $id
                                )
                            ) ?>">
                    Continue to payment
                </a>


                <form
                    method="post"
                    action="<?= e(
                                app_url(
                                    'orders/cancel_order.php'
                                )
                            ) ?>"
                    data-confirm="Are you sure you want to cancel this order?"
                    style="margin-top:10px">

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= e(
                                    csrf_token()
                                ) ?>">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= e(
                                    (string)$id
                                ) ?>">

                    <button
                        type="submit"
                        class="btn btn-secondary btn-block">
                        Cancel Order
                    </button>

                </form>


                <p class="help">

                    You can cancel this order while
                    payment is still pending.

                </p>

            <?php endif; ?>


            <!-- =================================================
                 CONFIRMED
            ================================================== -->

            <?php if (
                $order['status'] ===
                'confirmed'
            ): ?>

                <div
                    class="notice"
                    style="margin-top:16px">

                    Your payment has been confirmed.
                    Your order is being prepared.

                </div>

            <?php endif; ?>


            <!-- =================================================
                 SHIPPED
            ================================================== -->

            <?php if (
                $order['status'] ===
                'shipped'
            ): ?>

                <div
                    class="notice"
                    style="margin-top:16px">

                    Your order has been shipped
                    and is on its way.

                </div>

            <?php endif; ?>


            <!-- =================================================
                 DELIVERED
            ================================================== -->

            <?php if (
                $order['status'] ===
                'delivered'
            ): ?>

                <div
                    class="notice"
                    style="margin-top:16px">

                    Your order has been delivered.
                    You can now review the products
                    you purchased.

                </div>

            <?php endif; ?>


            <!-- =================================================
                 CANCELLED
            ================================================== -->

            <?php if (
                $order['status'] ===
                'cancelled'
            ): ?>

                <div
                    class="notice"
                    style="margin-top:16px">

                    This order has been cancelled.

                </div>

            <?php endif; ?>

        </aside>

    </div>

</div>

<?php

require __DIR__ . '/../includes/footer.php';

?>