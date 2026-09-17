<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();
$pdo = db();
$cartId = get_or_create_cart($user['user_id']);
$stmt = $pdo->prepare('SELECT ci.*,p.name,p.price,p.stock_qty FROM cart_item ci JOIN cart c ON c.cart_id=ci.cart_id JOIN product p ON p.product_id=ci.product_id WHERE c.user_id=?');
$stmt->execute([$user['user_id']]);
$items = $stmt->fetchAll();
if (!$items) {
    flash('error', 'Your cart is empty.');
    redirect('cart/cart.php');
}
$addresses = $pdo->prepare('SELECT * FROM address WHERE user_id=? ORDER BY is_default DESC,address_id DESC');
$addresses->execute([$user['user_id']]);
$addresses = $addresses->fetchAll();
$subtotal = array_reduce($items, fn($s, $i) => $s + (float)$i['price'] * (int)$i['quantity'], 0);
$page_title = 'Checkout';
require __DIR__ . '/../includes/header.php';

?><div class="container section">
    <div class="section-head">
        <div><span class="eyebrow">Checkout</span>
            <h2>Complete your order</h2>
            <p class="muted">Review delivery, optional promo code and cake inscriptions.</p>
        </div>
    </div>
    <form class="cart-layout" method="post" action="<?= e(app_url('cart/place_order.php')) ?>"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <section class="panel">
            <h3>Delivery</h3>
            <div class="form-grid">
                <div class="field"><label>Delivery type</label><select name="delivery_type" id="deliveryType" required>
                        <option value="nationwide_shipping">Nationwide shipping — LKR 500.00</option>
                        <option value="local_pickup">Local pickup — LKR 0.00</option>
                    </select></div>
                <div class="field"><label>Saved address</label><select name="address_id">
                        <option value="">Select address</option><?php foreach ($addresses as $a): ?><option value="<?= e((string)$a['address_id']) ?>" <?= $a['is_default'] ? 'selected' : '' ?>><?= e($a['street'] . ' — ' . $a['city']) ?></option><?php endforeach;
                                                                                                                                                                                                                                        ?>
                    </select></div>
            </div><?php if (!$addresses): ?><div class="notice" style="margin-top:14px">For nationwide shipping, add a saved address first. <a class="back-link" href="<?= e(app_url('auth/addresses.php')) ?>">Add address</a></div><?php endif;
                                                                                                                                                                                                                                    ?><h3 style="margin-top:28px">Cake inscriptions</h3><?php foreach ($items as $i): ?><div class="field" style="margin-bottom:12px"><label><?= e($i['name']) ?> × <?= e((string)$i['quantity']) ?></label><input maxlength="200" name="inscription[<?= e((string)$i['product_id']) ?>]" placeholder="Optional message on the cake"></div><?php endforeach;
                                                                                                                                                                                                                                                                                                                                    ?><h3 style="margin-top:28px">Promo code</h3>
            <div class="promo-row"><input name="promo_code" placeholder="Enter promo code if you have one"><span class="help" style="align-self:center">Demo seeds include WELCOME10 and SWEET500.</span></div>
        </section>
        <aside class="panel">
            <h3>Order summary</h3><?php foreach ($items as $i): ?><div class="summary-row"><span><?= e($i['name']) ?> × <?= e((string)$i['quantity']) ?></span><strong><?= e(money((float)$i['price'] * (int)$i['quantity'])) ?></strong></div><?php endforeach;
                                                                                                                                                                                                                                        ?><div class="summary-row"><span>Subtotal</span><strong><?= e(money($subtotal)) ?></strong></div>
            <div class="summary-row"><span>Delivery</span><strong id="deliveryFee">LKR 500.00</strong></div>
            <div class="summary-row total"><span>Payment</span><strong><?= e(money($subtotal + SHIPPING_FEE)) ?></strong></div><button class="btn btn-block" type="submit">Place order &amp; continue to payment</button>
        </aside>
    </form>
</div>
<script>
    document.getElementById('deliveryType').addEventListener('change', function() {
        document.getElementById('deliveryFee').textContent = this.value === 'local_pickup' ? 'LKR 0.00' : 'LKR 500.00';
    });
</script><?php require __DIR__ . '/../includes/footer.php';
            ?>