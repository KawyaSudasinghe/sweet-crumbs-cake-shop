<?php
require_once __DIR__ . '/../includes/auth.php';
$user=require_login();
$stmt=db()->prepare('SELECT * FROM `order` WHERE user_id=? ORDER BY placed_at DESC');
$stmt->execute([$user['user_id']]);
$orders=$stmt->fetchAll();
$page_title='My orders';
require __DIR__.'/../includes/header.php';

?><div class="container section"><div class="section-head"><div><span class="eyebrow">Orders</span><h2>My orders</h2></div></div><?php if(!$orders): ?><div class="empty"><h3>No orders yet</h3><a class="btn" href="<?=e(app_url('catalog/products.php'))?>">Start shopping</a></div><?php else: foreach($orders as $o): ?><div class="order-card"><div class="order-head"><div><strong>Order #<?=e((string)$o['order_id'])?></strong><p class="muted" style="margin:4px 0"><?=e(date('d M Y, h:i A',strtotime($o['placed_at'])))?></p></div><span class="status"><?=e($o['status'])?></span></div><div class="summary-row"><span>Delivery</span><span><?=e($o['delivery_type']==='local_pickup'?'Local pickup':'Nationwide shipping')?></span></div><div class="summary-row"><span>Total</span><strong><?=e(money((float)$o['total_amount']))?></strong></div><a class="back-link" href="<?=e(app_url('orders/order.php?id='.$o['order_id']))?>">View order <?=icon('arrow',18)?></a></div><?php endforeach;
 endif;
 ?></div><?php require __DIR__.'/../includes/footer.php';
 ?>
