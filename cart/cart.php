<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$pdo = db();

$cartId = get_or_create_cart(
    $user['user_id']
);


/*
|--------------------------------------------------------------------------
| Handle cart actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $action = $_POST['action'] ?? '';

    $itemId = (int)(
        $_POST['cart_item_id'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | Remove item
    |--------------------------------------------------------------------------
    */

    if ($action === 'remove') {

        $stmt = $pdo->prepare(
            'DELETE ci
             FROM cart_item ci
             JOIN cart c
               ON c.cart_id = ci.cart_id
             WHERE ci.cart_item_id = ?
               AND c.user_id = ?'
        );

        $stmt->execute([
            $itemId,
            $user['user_id']
        ]);

        flash(
            'success',
            'Item removed from cart.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update quantity
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'update') {

        $qty = (int)(
            $_POST['quantity'] ?? 1
        );

        $qty = max(
            1,
            min(
                MAX_CART_QTY,
                $qty
            )
        );


        $stmt = $pdo->prepare(
            'SELECT
                p.stock_qty
             FROM cart_item ci
             JOIN cart c
               ON c.cart_id = ci.cart_id
             JOIN product p
               ON p.product_id = ci.product_id
             WHERE ci.cart_item_id = ?
               AND c.user_id = ?'
        );

        $stmt->execute([
            $itemId,
            $user['user_id']
        ]);

        $stock = $stmt->fetchColumn();


        if ($stock === false) {

            flash(
                'error',
                'Cart item not found.'
            );

        } elseif ($stock <= 0) {

            flash(
                'error',
                'This product is currently out of stock.'
            );

        } elseif ($qty > $stock) {

            flash(
                'error',
                'Quantity exceeds available stock.'
            );

        } else {

            $update = $pdo->prepare(
                'UPDATE cart_item ci
                 JOIN cart c
                   ON c.cart_id = ci.cart_id
                 SET ci.quantity = ?
                 WHERE ci.cart_item_id = ?
                   AND c.user_id = ?'
            );

            $update->execute([
                $qty,
                $itemId,
                $user['user_id']
            ]);

            flash(
                'success',
                'Cart updated.'
            );
        }
    }


    redirect('cart/cart.php');
}


/*
|--------------------------------------------------------------------------
| Get cart items
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    'SELECT
        ci.*,
        p.name,
        p.price,
        p.stock_qty,
        p.image_url,
        p.size
     FROM cart_item ci
     JOIN cart c
       ON c.cart_id = ci.cart_id
     JOIN product p
       ON p.product_id = ci.product_id
     WHERE c.user_id = ?
     ORDER BY ci.cart_item_id DESC'
);

$stmt->execute([
    $user['user_id']
]);

$items = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/

$subtotal = array_reduce(
    $items,
    fn($sum, $i) =>
        $sum +
        (float)$i['price'] *
        (int)$i['quantity'],
    0
);

$shipping = $items
    ? SHIPPING_FEE
    : 0;

$total = $subtotal + $shipping;


$page_title = 'Your cart';

require __DIR__ . '/../includes/header.php';

?>

<div class="container section">

    <div class="section-head">

        <div>

            <span class="eyebrow">
                Shopping cart
            </span>

            <h2>
                Your cart
            </h2>

        </div>

    </div>


    <?php if (!$items): ?>

        <div class="empty">

            <h3>
                Your cart is empty
            </h3>

            <p class="muted">
                Choose something delicious from the catalogue.
            </p>

            <a
                class="btn"
                href="<?= e(
                    app_url(
                        'catalog/products.php'
                    )
                ) ?>"
            >
                Browse products
            </a>

        </div>


    <?php else: ?>


        <div class="cart-layout">


            <!-- =====================================================
                 CART ITEMS
            ====================================================== -->

            <section class="panel">

                <?php foreach ($items as $i): ?>

                    <?php

                    $maxQty = min(
                        MAX_CART_QTY,
                        (int)$i['stock_qty']
                    );

                    ?>

                    <div class="cart-item">

                        <img
                            src="<?= e(
                                safe_image(
                                    $i['image_url']
                                )
                            ) ?>"
                            alt="<?= e($i['name']) ?>"
                        >


                        <div>

                            <strong>
                                <?= e($i['name']) ?>
                            </strong>


                            <div class="muted">

                                <?= e(
                                    money(
                                        (float)$i['price']
                                    )
                                ) ?>

                                ·

                                <?= e(
                                    $i['size']
                                    ?: 'Standard'
                                ) ?>

                            </div>


                            <a
                                class="back-link"
                                href="<?= e(
                                    app_url(
                                        'catalog/product.php?id=' .
                                        $i['product_id']
                                    )
                                ) ?>"
                            >
                                View product
                            </a>

                        </div>


                        <!-- =================================================
                             QUANTITY CONTROLS
                        ================================================== -->

                        <div>

                            <form
                                method="post"
                                class="cart-item-form"
                            >

                                <input
                                    type="hidden"
                                    name="csrf"
                                    value="<?= e(
                                        csrf_token()
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="cart_item_id"
                                    value="<?= e(
                                        (string)$i['cart_item_id']
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="update"
                                >
								
                                <div class="qty">

                                    <input
                                        type="number"
                                        name="quantity"
                                        value="<?= e(
                                            (string)$i['quantity']
                                        ) ?>"
                                        min="1"
                                        max="<?= e(
                                            (string)max(
                                                1,
                                                $maxQty
                                            )
                                        ) ?>"
                                        class="cart-quantity"
                                        aria-label="Quantity"
                                    >

                                </div>    
                            </form>


                            <!-- =================================================
                                 REMOVE
                            ================================================== -->

                            <form
                                method="post"
                                style="margin-top:8px"
                            >

                                <input
                                    type="hidden"
                                    name="csrf"
                                    value="<?= e(
                                        csrf_token()
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="cart_item_id"
                                    value="<?= e(
                                        (string)$i['cart_item_id']
                                    ) ?>"
                                >

                                <button
                                    type="submit"
                                    name="action"
                                    value="remove"
                                    class="btn btn-secondary btn-small"
                                >
                                    Remove
                                </button>

                            </form>

                        </div>

                    </div>

                <?php endforeach; ?>

            </section>


            <!-- =====================================================
                 SUMMARY
            ====================================================== -->

            <aside class="panel">

                <h3>
                    Summary
                </h3>


                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>
                        <?= e(
                            money($subtotal)
                        ) ?>
                    </strong>

                </div>


                <div class="summary-row">

                    <span>
                        Nationwide delivery
                    </span>

                    <strong>
                        <?= e(
                            money($shipping)
                        ) ?>
                    </strong>

                </div>


                <div class="summary-row total">

                    <span>
                        Total
                    </span>

                    <strong>
                        <?= e(
                            money($total)
                        ) ?>
                    </strong>

                </div>


                <a
                    class="btn btn-block"
                    style="margin-top:16px"
                    href="<?= e(
                        app_url(
                            'cart/checkout.php'
                        )
                    ) ?>"
                >
                    Proceed to checkout
                    <?= icon('arrow', 18) ?>
                </a>


                <p class="help">

                    Local pickup is available at checkout
                    and has no delivery fee.

                </p>

            </aside>

        </div>

    <?php endif; ?>

</div>

<?php

require __DIR__ . '/../includes/footer.php';

?>