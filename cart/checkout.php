<?php
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();
$pdo = db();

$cartId = get_or_create_cart($user['user_id']);

$stmt = $pdo->prepare(
    'SELECT ci.*, p.name, p.price, p.stock_qty
     FROM cart_item ci
     JOIN cart c ON c.cart_id = ci.cart_id
     JOIN product p ON p.product_id = ci.product_id
     WHERE c.user_id = ?'
);
$stmt->execute([$user['user_id']]);
$items = $stmt->fetchAll();

if (!$items) {
    flash('error', 'Your cart is empty.');
    redirect('cart/cart.php');
}

$addressesStmt = $pdo->prepare(
    'SELECT * FROM address
     WHERE user_id = ?
     ORDER BY is_default DESC, address_id DESC'
);
$addressesStmt->execute([$user['user_id']]);
$addresses = $addressesStmt->fetchAll();

$checkoutError = $_SESSION['checkout_error'] ?? '';
$oldAddress = $_SESSION['checkout_address'] ?? [];

unset(
    $_SESSION['checkout_error'],
    $_SESSION['checkout_address']
);

$subtotal = array_reduce(
    $items,
    fn($s, $i) => $s + (float)$i['price'] * (int)$i['quantity'],
    0
);

$page_title = 'Checkout';
require __DIR__ . '/../includes/header.php';
?>

<div class="container section">

    <div class="section-head">
        <div>
            <span class="eyebrow">Checkout</span>
            <h2>Complete your order</h2>
            <p class="muted">
                Review delivery, optional promo code and cake inscriptions.
            </p>
        </div>
    </div>

    <form
        class="cart-layout"
        method="post"
        action="<?= e(app_url('cart/place_order.php')) ?>">

        <input
            type="hidden"
            name="csrf"
            value="<?= e(csrf_token()) ?>">

        <section class="panel">

            <h3>Delivery</h3>

            <div class="form-grid">

                <!-- Delivery type -->
                <div class="field">

                    <label for="deliveryType">
                        Delivery type
                    </label>

                    <select
                        name="delivery_type"
                        id="deliveryType"
                        required>
                        <option value="nationwide_shipping">
                            Nationwide shipping — LKR 500.00
                        </option>

                        <option value="local_pickup">
                            Local pickup — LKR 0.00
                        </option>
                    </select>

                </div>

                <?php if ($addresses): ?>

                    <!-- Saved address dropdown -->
                    <div class="field">

                        <label for="addressId">
                            Saved address
                        </label>

                        <select
                            name="address_id"
                            id="addressId">

                            <option value="">
                                Select address
                            </option>

                            <?php foreach ($addresses as $a): ?>

                                <option
                                    value="<?= e((string)$a['address_id']) ?>"
                                    <?= $a['is_default'] ? 'selected' : '' ?>>
                                    <?= e($a['street'] . ' — ' . $a['city']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <?php if ($checkoutError): ?>

                            <div
                                class="error-text"
                                style="margin-top:7px">
                                <?= e($checkoutError) ?>
                            </div>

                        <?php endif; ?>

                    </div>

                <?php else: ?>

                    <!-- No saved address -->
                    <div class="field">

                        <label>
                            Delivery address
                        </label>

                        <span class="help">
                            No saved address found.
                            Enter your address below.
                            It will be saved to your account.
                        </span>

                    </div>

                    <?php if ($checkoutError): ?>

                        <div class="field full">

                            <div class="error-text">
                                <?= e($checkoutError) ?>
                            </div>

                        </div>

                    <?php endif; ?>

                    <div class="field full">

                        <label for="checkoutStreet">
                            Street
                        </label>

                        <textarea
                            id="checkoutStreet"
                            name="checkout_street"
                            rows="3"><?= e($oldAddress['street'] ?? '') ?></textarea>

                    </div>

                    <div class="field">

                        <label for="checkoutCity">
                            City
                        </label>

                        <input
                            id="checkoutCity"
                            name="checkout_city"
                            value="<?= e($oldAddress['city'] ?? '') ?>">

                    </div>

                    <div class="field">

                        <label for="checkoutPostal">
                            Postal code
                        </label>

                        <input
                            id="checkoutPostal"
                            name="checkout_postal_code"
                            value="<?= e($oldAddress['postal_code'] ?? '') ?>">

                    </div>

                <?php endif; ?>

            </div>

            <?php if ($addresses && !$checkoutError): ?>

                <div
                    class="help"
                    style="margin-top:8px">
                    You can manage your saved addresses from
                    <a
                        class="back-link"
                        href="<?= e(app_url('auth/addresses.php')) ?>">
                        Saved addresses
                    </a>.
                </div>

            <?php endif; ?>


            <!-- Cake inscriptions -->

            <h3 style="margin-top:28px">
                Cake inscriptions
            </h3>

            <?php foreach ($items as $i): ?>

                <div
                    class="field"
                    style="margin-bottom:12px">

                    <label>
                        <?= e($i['name']) ?>
                        ×
                        <?= e((string)$i['quantity']) ?>
                    </label>

                    <input
                        maxlength="200"
                        name="inscription[<?= e((string)$i['product_id']) ?>]"
                        placeholder="Optional message on the cake">

                </div>

            <?php endforeach; ?>


            <!-- Promo -->

            <h3 style="margin-top:28px">
                Promo code
            </h3>

            <div class="promo-row">

                <input
                    name="promo_code"
                    placeholder="Enter promo code if you have one">

                <span
                    class="help"
                    style="align-self:center">
                    Demo seeds include WELCOME10 and SWEET500.
                </span>

            </div>

        </section>


        <!-- Order summary -->

        <aside class="panel">

            <h3>Order summary</h3>

            <?php foreach ($items as $i): ?>

                <div class="summary-row">

                    <span>
                        <?= e($i['name']) ?>
                        ×
                        <?= e((string)$i['quantity']) ?>
                    </span>

                    <strong>
                        <?= e(
                            money(
                                (float)$i['price'] *
                                    (int)$i['quantity']
                            )
                        ) ?>
                    </strong>

                </div>

            <?php endforeach; ?>


            <div class="summary-row">

                <span>Subtotal</span>

                <strong>
                    <?= e(money($subtotal)) ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>Delivery</span>

                <strong id="deliveryFee">
                    LKR 500.00
                </strong>

            </div>


            <div class="summary-row total">

                <span>Payment</span>

                <strong id="checkoutTotal">
                    <?= e(
                        money(
                            $subtotal + SHIPPING_FEE
                        )
                    ) ?>
                </strong>

            </div>


            <button
                class="btn btn-block"
                type="submit">
                Place order &amp; continue to payment
            </button>

        </aside>

    </form>

</div>


<script>
    document.getElementById('deliveryType').addEventListener(
        'change',
        function() {

            const isPickup =
                this.value === 'local_pickup';

            document.getElementById('deliveryFee').textContent =
                isPickup ?
                'LKR 0.00' :
                'LKR 500.00';


            const addressId =
                document.getElementById('addressId');

            if (addressId) {
                addressId.disabled = isPickup;
            }

        }
    );

    document.getElementById('deliveryType')
        .dispatchEvent(new Event('change'));
</script>


<?php require __DIR__ . '/../includes/footer.php'; ?>