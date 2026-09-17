<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    flash('error', 'Invalid order.');
    redirect('orders/orders.php');
}

$pdo = db();

$stmt = $pdo->prepare(
    'SELECT
        o.*,
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
    flash('error', 'Order not found.');
    redirect('orders/orders.php');
}

/*
 * If payment is already successful,
 * don't create another Checkout Session.
 */
if ($order['payment_status'] === 'success') {
    redirect(
        'payment/success.php?order_id=' . $orderId
    );
}

/*
 * Stripe secret key must be configured.
 */
if (
    STRIPE_SECRET_KEY === '' ||
    STRIPE_SECRET_KEY === 'sk_test_YOUR_SECRET_KEY'
) {
    $page_title = 'Payment setup required';

    require __DIR__ . '/../includes/header.php';
?>

<div class="container section">

    <div class="form-card">

        <h1>Stripe Payment Setup</h1>

        <div class="notice">
            Stripe has not been configured yet.
            Please add your Stripe test secret key
            to <code>config/config.php</code>.
        </div>

        <p class="muted">
            Your order has been saved as pending.
            You can return here after configuring Stripe.
        </p>

        <a
            class="btn"
            href="<?= e(app_url('orders/order.php?id=' . $orderId)) ?>"
        >
            Back to order
        </a>

    </div>

</div>

<?php

    require __DIR__ . '/../includes/footer.php';

    exit;
}

/*
 * Stripe Checkout uses the smallest currency unit.
 *
 * LKR uses two decimal places here, so:
 *
 * LKR 2500.00
 *
 * becomes:
 *
 * 250000
 */
$amount = (int)round(
    ((float)$order['total_amount']) * 100
);

/*
 * Stripe Checkout Session data.
 */
$successUrl =
    app_url(
        'payment/success.php?order_id=' .
        $orderId .
        '&session_id={CHECKOUT_SESSION_ID}'
    );

$cancelUrl =
    app_url(
        'payment/cancel.php?order_id=' .
        $orderId
    );

$postData = [

    'mode' => 'payment',

    'success_url' => $successUrl,

    'cancel_url' => $cancelUrl,

    'customer_email' => $user['email'],

    'line_items[0][price_data][currency]' =>
        strtolower(CURRENCY),

    'line_items[0][price_data][product_data][name]' =>
        'Sweet Crumbs Order #' . $orderId,

    'line_items[0][price_data][product_data][description]' =>
        'Online cake order from Sweet Crumbs',

    'line_items[0][price_data][unit_amount]' =>
        $amount,

    'line_items[0][quantity]' => 1,

    'metadata[order_id]' =>
        (string)$orderId,

    'metadata[user_id]' =>
        (string)$user['user_id']
];

/*
 * Send the Checkout Session request
 * directly to Stripe's API.
 *
 * No Stripe SDK or Stripe CLI is required.
 */
$ch = curl_init(
    'https://api.stripe.com/v1/checkout/sessions'
);

curl_setopt_array($ch, [

    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS =>
        http_build_query($postData),

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [

        'Authorization: Bearer ' .
        STRIPE_SECRET_KEY,

        'Content-Type: application/x-www-form-urlencoded'
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
        'Unable to connect to Stripe. Please try again.'
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

$data = json_decode(
    $response,
    true
);

if (
    $httpCode < 200 ||
    $httpCode >= 300 ||
    empty($data['url'])
) {

    $message =
        $data['error']['message']
        ?? 'Stripe payment session could not be created.';

    flash(
        'error',
        $message
    );

    redirect(
        'orders/order.php?id=' . $orderId
    );
}

/*
 * Stripe has returned the hosted Checkout URL.
 */
header(
    'Location: ' . $data['url']
);

exit;