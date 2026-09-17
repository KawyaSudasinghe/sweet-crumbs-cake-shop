<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$id = (int)($_GET['order_id'] ?? 0);

$stmt = db()->prepare(
    'SELECT *
     FROM `order`
     WHERE order_id = ?
       AND user_id = ?'
);

$stmt->execute([
    $id,
    $user['user_id']
]);

$order = $stmt->fetch();

$page_title = 'Order confirmation';

require __DIR__ . '/../includes/header.php';

?>

<div class="container section">

    <div
        class="form-card"
        style="text-align:center">

        <div
            style="
                font-size:2.5rem;
                color:var(--blue-700);
            ">
            <?= icon('check', 44) ?>
        </div>

        <h1>Thanks for your order</h1>

        <p class="muted">
            Order #<?= e((string)$id) ?> has been created.
        </p>

        <?php if ($order): ?>

            <p>
                <strong>
                    Total:
                    <?= e(money((float)$order['total_amount'])) ?>
                </strong>
            </p>

            <p class="muted">
                Payment is processed securely through Stripe.
            </p>

        <?php endif; ?>

        <a
            class="btn"
            href="<?= e(app_url('orders/order.php?id=' . $id)) ?>">
            View Order
        </a>

        <a
            class="btn btn-secondary"
            style="margin-left:8px"
            href="<?= e(app_url('catalog/products.php')) ?>">
            Continue Shopping
        </a>

    </div>

</div>

<?php

require __DIR__ . '/../includes/footer.php';
