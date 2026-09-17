<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart/cart.php');
}

verify_csrf();

$pdo = db();

$delivery = $_POST['delivery_type'] ?? 'nationwide_shipping';
$addressId = (int)($_POST['address_id'] ?? 0);
$promoCode = trim($_POST['promo_code'] ?? '');
$inscriptions = $_POST['inscription'] ?? [];

if (!in_array($delivery, ['nationwide_shipping', 'local_pickup'], true)) {
    flash('error', 'Invalid delivery option.');
    redirect('cart/checkout.php');
}

if ($delivery === 'nationwide_shipping') {
    $stmt = $pdo->prepare(
        'SELECT address_id
         FROM address
         WHERE address_id = ? AND user_id = ?'
    );

    $stmt->execute([
        $addressId,
        $user['user_id']
    ]);

    if (!$stmt->fetch()) {
        flash('error', 'Please select a valid saved delivery address.');
        redirect('cart/checkout.php');
    }
} else {
    $addressId = 0;
}

$cartId = get_or_create_cart($user['user_id']);

$stmt = $pdo->prepare(
    'SELECT
        ci.product_id,
        ci.quantity,
        p.name,
        p.price,
        p.stock_qty
     FROM cart_item ci
     JOIN cart c ON c.cart_id = ci.cart_id
     JOIN product p ON p.product_id = ci.product_id
     WHERE c.user_id = ?
       AND p.is_available = 1
     FOR UPDATE'
);

$pdo->beginTransaction();

try {

    $stmt->execute([$user['user_id']]);

    $items = $stmt->fetchAll();

    if (!$items) {
        throw new Exception('Your cart is empty.');
    }

    $subtotal = 0;

    foreach ($items as $item) {

        if ((int)$item['quantity'] > (int)$item['stock_qty']) {
            throw new Exception(
                'Insufficient stock for ' . $item['name'] . '.'
            );
        }

        $subtotal +=
            (float)$item['price'] *
            (int)$item['quantity'];
    }

    $shipping =
        $delivery === 'nationwide_shipping'
        ? SHIPPING_FEE
        : 0;

    $promoId = null;
    $discount = 0;

    if ($promoCode !== '') {

        $p = $pdo->prepare(
            'SELECT *
             FROM promo_code
             WHERE code = ?
               AND is_active = 1
               AND (
                    valid_until IS NULL
                    OR valid_until >= CURDATE()
               )'
        );

        $p->execute([$promoCode]);

        $promo = $p->fetch();

        if (!$promo) {
            throw new Exception(
                'Invalid or expired promo code.'
            );
        }

        $promoId = (int)$promo['promo_id'];

        if ($promo['discount_type'] === 'percentage') {

            $discount = round(
                $subtotal *
                    ((float)$promo['discount_value'] / 100),
                2
            );
        } else {

            $discount = min(
                $subtotal,
                (float)$promo['discount_value']
            );
        }
    }

    $total = max(
        0,
        $subtotal + $shipping - $discount
    );

    /*
     * Create the order as pending.
     * The order will be confirmed after Stripe
     * reports a successful Checkout Session.
     */
    $stmt = $pdo->prepare(
        "INSERT INTO `order`
        (
            user_id,
            address_id,
            promo_id,
            total_amount,
            delivery_type,
            status
        )
        VALUES (?, ?, ?, ?, ?, 'pending')"
    );

    $stmt->execute([
        $user['user_id'],
        $addressId ?: null,
        $promoId,
        $total,
        $delivery
    ]);

    $orderId = (int)$pdo->lastInsertId();

    /*
     * Save order item price snapshots.
     */
    $oi = $pdo->prepare(
        'INSERT INTO order_item
        (
            order_id,
            product_id,
            quantity,
            unit_price,
            inscription
        )
        VALUES (?, ?, ?, ?, ?)'
    );

    foreach ($items as $item) {

        $message = trim(
            (string)($inscriptions[$item['product_id']] ?? '')
        );

        $message =
            $message !== ''
            ? mb_substr($message, 0, 200)
            : null;

        $oi->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $message
        ]);
    }

    /*
     * Create a pending Stripe payment record.
     */
    $payment = $pdo->prepare(
        "INSERT INTO payment
        (
            order_id,
            method,
            amount,
            status
        )
        VALUES (?, 'Stripe', ?, 'pending')"
    );

    $payment->execute([
        $orderId,
        $total
    ]);

    /*
     * Empty the cart after the order has been created.
     */
    $pdo->prepare(
        'DELETE FROM cart_item
         WHERE cart_id = ?'
    )->execute([$cartId]);

    $pdo->commit();

    redirect(
        'payment/payment.php?order_id=' . $orderId
    );
} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('error', $e->getMessage());

    redirect('cart/checkout.php');
}