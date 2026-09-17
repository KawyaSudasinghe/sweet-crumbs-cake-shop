<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();
$id = (int)($_GET['id'] ?? 0);
$pdo = db();
$stmt = $pdo->prepare('SELECT o.*,a.street,a.city,a.postal_code,pc.code promo_code FROM `order` o LEFT JOIN address a ON a.address_id=o.address_id LEFT JOIN promo_code pc ON pc.promo_id=o.promo_id WHERE o.order_id=? AND o.user_id=?');
$stmt->execute([$id, $user['user_id']]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    redirect('orders/orders.php');
}
$stmt = $pdo->prepare('SELECT oi.*,p.name,p.image_url FROM order_item oi JOIN product p ON p.product_id=oi.product_id WHERE oi.order_id=?');
$stmt->execute([$id]);
$items = $stmt->fetchAll();
$page_title = 'Order #' . $id;
require __DIR__ . '/../includes/header.php';

?><div class="container section"><a class="back-link" href="<?= e(app_url('orders/orders.php')) ?>">← My orders</a>
    <div class="section-head">
        <div><span class="eyebrow">Order #<?= e((string)$id) ?></span>
            <h2>Order details</h2>
        </div><span class="status"><?= e($order['status']) ?></span>
    </div>
    <div class="cart-layout" style="padding-top:0">
        <section class="panel">
            <h3>Items</h3><?php foreach ($items as $i): ?><div class="cart-item"><img src="<?= e(safe_image($i['image_url'])) ?>" alt="<?= e($i['name']) ?>">
                    <div><strong><?= e($i['name']) ?></strong>
                        <div class="muted">Qty <?= e((string)$i['quantity']) ?> · <?= e(money((float)$i['unit_price'])) ?></div><?php if ($i['inscription']): ?><div class="notice" style="margin-top:8px">Inscription: <?= e($i['inscription']) ?></div><?php endif;
                                                                                                                                                                                                                                                    ?>
                    </div><strong><?= e(money((float)$i['unit_price'] * (int)$i['quantity'])) ?></strong>
                </div><?php endforeach;
                        ?>
        </section>
        <aside class="panel">
            <h3>Order summary</h3>
            <div class="summary-row"><span>Total</span><strong><?= e(money((float)$order['total_amount'])) ?></strong></div>
            <div class="summary-row"><span>Payment</span><span>Stripe</span></div>
            <div class="summary-row"><span>Delivery</span><span><?= e($order['delivery_type'] === 'local_pickup' ? 'Local pickup' : $order['street'] . ', ' . $order['city'] . ' ' . $order['postal_code']) ?></span></div><?php if ($order['promo_code']): ?><div class="summary-row"><span>Promo</span><span><?= e($order['promo_code']) ?></span></div><?php endif;
                                                                                                                                                                                                                                                                                                                                        ?>
            <?php if ($order['status'] === 'pending'): ?>
                <a
                    class="btn btn-block"
                    href="<?= e(app_url('payment/payment.php?order_id=' . $id)) ?>">
                    Continue to payment
                </a>
            <?php endif; ?>
        </aside>
    </div>
</div><?php require __DIR__ . '/../includes/footer.php';
        ?>