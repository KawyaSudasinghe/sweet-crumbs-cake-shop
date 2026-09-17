<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId > 0) {

    $stmt = db()->prepare(
        'SELECT order_id
         FROM `order`
         WHERE order_id = ?
           AND user_id = ?'
    );

    $stmt->execute([
        $orderId,
        $user['user_id']
    ]);

    $order = $stmt->fetch();

    if ($order) {

        flash(
            'info',
            'Payment was cancelled. Your order remains pending, so you can try again.'
        );

        redirect(
            'orders/order.php?id=' . $orderId
        );
    }
}

flash(
    'info',
    'Payment was cancelled.'
);

redirect('orders/orders.php');