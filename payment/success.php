<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$orderId = (int)($_GET['order_id'] ?? 0);
$sessionId = trim($_GET['session_id'] ?? '');

if ($orderId <= 0 || $sessionId === '') {

    flash(
        'error',
        'Invalid Stripe payment response.'
    );

    redirect('orders/orders.php');
}

$pdo = db();

/*
 * Make sure the order belongs to the
 * currently logged-in customer.
 */
$stmt = $pdo->prepare(
    'SELECT
        o.*,
        p.payment_id,
        p.amount AS payment_amount,
        p.status AS payment_status
     FROM `order` o
     JOIN payment p
        ON p.order_id = o.order_id
     WHERE o.order_id = ?
       AND o.user_id = ?'
);

$stmt->execute([
    $orderId,
    $user['user_id']
]);

$order = $stmt->fetch();

if (!$order) {

    flash(
        'error',
        'Order not found.'
    );

    redirect('orders/orders.php');
}

/*
 * Ask Stripe directly for the Checkout Session.
 *
 * We do NOT trust a simple browser redirect
 * as proof that payment succeeded.
 */
$ch = curl_init(
    'https://api.stripe.com/v1/checkout/sessions/' .
    rawurlencode($sessionId)
);

curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [

        'Authorization: Bearer ' .
        STRIPE_SECRET_KEY
    ],

    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);

$curlError = curl_error($ch);

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);

if ($response === false || $curlError) {

    flash(
        'error',
        'Could not verify the Stripe payment.'
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

$session = json_decode(
    $response,
    true
);

if (
    $httpCode < 200 ||
    $httpCode >= 300 ||
    !is_array($session)
) {

    flash(
        'error',
        'Stripe payment verification failed.'
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

/*
 * Verify that this Checkout Session belongs
 * to this Sweet Crumbs order.
 */
$metadataOrderId =
    (string)($session['metadata']['order_id'] ?? '');

if ($metadataOrderId !== (string)$orderId) {

    flash(
        'error',
        'Payment verification failed.'
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

/*
 * Verify the amount.
 */
$expectedAmount = (int)round(
    ((float)$order['total_amount']) * 100
);

$paidAmount =
    (int)($session['amount_total'] ?? 0);

if ($paidAmount !== $expectedAmount) {

    flash(
        'error',
        'Payment amount does not match the order.'
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

/*
 * Stripe Checkout payment status should be paid.
 */
if (
    ($session['payment_status'] ?? '') !== 'paid'
) {

    flash(
        'error',
        'The Stripe payment was not completed.'
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

/*
 * Confirm the order and payment.
 *
 * Transaction + row locking prevents the
 * stock from being reduced twice if the
 * success page is refreshed.
 */
$pdo->beginTransaction();

try {

    $stmt = $pdo->prepare(
        'SELECT
            o.status AS order_status,
            o.total_amount,
            p.status AS payment_status
         FROM `order` o
         JOIN payment p
            ON p.order_id = o.order_id
         WHERE o.order_id = ?
         FOR UPDATE'
    );

    $stmt->execute([$orderId]);

    $current = $stmt->fetch();

    if (!$current) {
        throw new Exception(
            'Order payment record not found.'
        );
    }

    /*
     * Only update stock the first time
     * the payment becomes successful.
     */
    if ($current['payment_status'] !== 'success') {

        $stmt = $pdo->prepare(
            'SELECT
                product_id,
                quantity
             FROM order_item
             WHERE order_id = ?'
        );

        $stmt->execute([$orderId]);

        $items = $stmt->fetchAll();

        foreach ($items as $item) {

            $update = $pdo->prepare(
                'UPDATE product
                 SET stock_qty = stock_qty - ?
                 WHERE product_id = ?
                   AND stock_qty >= ?
                   AND is_available = 1'
            );

            $update->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']
            ]);

            if ($update->rowCount() !== 1) {

                throw new Exception(
                    'Insufficient stock while confirming payment.'
                );
            }
        }

        $pdo->prepare(
            "UPDATE `order`
             SET status = 'confirmed'
             WHERE order_id = ?
               AND status = 'pending'"
        )->execute([$orderId]);

        $pdo->prepare(
            "UPDATE payment
             SET status = 'success',
                 paid_at = CURRENT_TIMESTAMP
             WHERE order_id = ?"
        )->execute([$orderId]);
    }

    $pdo->commit();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash(
        'error',
        $e->getMessage()
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

$page_title = 'Payment successful';

require __DIR__ . '/../includes/header.php';

?>

<div class="container section">

    <div
        class="form-card"
        style="text-align:center"
    >

        <div
            style="
                font-size:2.5rem;
                color:var(--blue-700);
            "
        >
            <?= icon('check', 44) ?>
        </div>

        <h1>Payment Successful</h1>

        <p class="muted">
            Your Stripe payment was successfully
            verified.
        </p>

        <p>
            Order #<?= e((string)$orderId) ?>
        </p>

        <p>
            <strong>
                Total:
                <?= e(money((float)$order['total_amount'])) ?>
            </strong>
        </p>

        <p class="notice">
            Your order has been confirmed.
        </p>

        <a
            class="btn"
            href="<?= e(app_url('orders/order.php?id=' . $orderId)) ?>"
        >
            View Order
        </a>

        <a
            class="btn btn-secondary"
            style="margin-left:8px"
            href="<?= e(app_url('catalog/products.php')) ?>"
        >
            Continue Shopping
        </a>

    </div>

</div>

<?php

require __DIR__ . '/../includes/footer.php';