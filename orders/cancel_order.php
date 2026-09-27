<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();


/*
|--------------------------------------------------------------------------
| Only POST requests are allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect('orders/orders.php');
}


verify_csrf();


/*
|--------------------------------------------------------------------------
| Get order ID
|--------------------------------------------------------------------------
*/

$orderId = (int)(
    $_POST['order_id'] ?? 0
);


if ($orderId <= 0) {

    flash(
        'error',
        'Invalid order.'
    );

    redirect('orders/orders.php');
}


$pdo = db();


/*
|--------------------------------------------------------------------------
| Find the customer's order
|--------------------------------------------------------------------------
|
| The user_id condition is important.
| A customer cannot cancel another customer's order.
|
*/

$stmt = $pdo->prepare(
    "SELECT
        order_id,
        status
     FROM `order`
     WHERE order_id = ?
       AND user_id = ?"
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
|--------------------------------------------------------------------------
| Only pending orders can be cancelled
|--------------------------------------------------------------------------
*/

if ($order['status'] !== 'pending') {

    flash(
        'error',
        'Only pending orders can be cancelled.'
    );

    redirect(
        'orders/order.php?id=' .
            $orderId
    );
}


/*
|--------------------------------------------------------------------------
| Cancel the order
|--------------------------------------------------------------------------
*/

$update = $pdo->prepare(
    "UPDATE `order`
     SET status = 'cancelled'
     WHERE order_id = ?
       AND user_id = ?
       AND status = 'pending'"
);

$update->execute([
    $orderId,
    $user['user_id']
]);


/*
|--------------------------------------------------------------------------
| Result
|--------------------------------------------------------------------------
*/

if ($update->rowCount() === 1) {

    /*
    |--------------------------------------------------------------------------
    | If there is a pending payment record, mark it as failed.
    |--------------------------------------------------------------------------
    */

    $paymentUpdate = $pdo->prepare(
        "UPDATE payment
         SET status = 'failed'
         WHERE order_id = ?
           AND status = 'pending'"
    );

    $paymentUpdate->execute([
        $orderId
    ]);


    flash(
        'success',
        'Order #' .
            $orderId .
            ' has been cancelled.'
    );
} else {

    flash(
        'error',
        'The order could not be cancelled.'
    );
}


redirect(
    'orders/order.php?id=' .
        $orderId
);
