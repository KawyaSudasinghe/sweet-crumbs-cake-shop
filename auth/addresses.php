<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$pdo = db();

$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();


    /*
     * Make an existing address the default.
     */

    if (
        ($_POST['action'] ?? '') ===
        'make_default'
    ) {

        $addressId =
            (int)($_POST['address_id'] ?? 0);


        $stmt = $pdo->prepare(
            'SELECT address_id
             FROM address
             WHERE address_id = ?
               AND user_id = ?'
        );


        $stmt->execute([

            $addressId,

            $user['user_id']

        ]);


        if (!$stmt->fetch()) {

            flash(
                'error',
                'Invalid address selected.'
            );

            redirect(
                'auth/addresses.php'
            );
        }


        $pdo->beginTransaction();


        try {

            /*
             * Remove default from all
             * addresses belonging to user.
             */

            $pdo->prepare(
                'UPDATE address
                 SET is_default = 0
                 WHERE user_id = ?'
            )->execute([
                $user['user_id']
            ]);


            /*
             * Make selected address default.
             */

            $pdo->prepare(
                'UPDATE address
                 SET is_default = 1
                 WHERE address_id = ?
                   AND user_id = ?'
            )->execute([

                $addressId,

                $user['user_id']

            ]);


            $pdo->commit();


            flash(
                'success',
                'Default address updated.'
            );


        } catch (Throwable $e) {

            if (
                $pdo->inTransaction()
            ) {

                $pdo->rollBack();
            }


            flash(
                'error',
                'Could not update the default address.'
            );
        }


        redirect(
            'auth/addresses.php'
        );
    }


    /*
     * Add a new address.
     */

    $street =
        trim($_POST['street'] ?? '');

    $city =
        trim($_POST['city'] ?? '');

    $postal =
        trim($_POST['postal_code'] ?? '');

    $default =
        isset($_POST['is_default']);


    if (
        $street === '' ||
        mb_strlen($street) > 200
    ) {

        $errors[] =
            'Enter a valid street address.';
    }


    if (
        $city === '' ||
        mb_strlen($city) > 100
    ) {

        $errors[] =
            'Enter a valid city.';
    }


    if (
        $postal === '' ||
        mb_strlen($postal) > 20
    ) {

        $errors[] =
            'Enter a valid postal code.';
    }


    if (!$errors) {

        $pdo->beginTransaction();


        try {

            /*
             * Check whether this is the
             * first address.
             */

            $countStmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM address
                 WHERE user_id = ?'
            );


            $countStmt->execute([
                $user['user_id']
            ]);


            $hasAddresses =
                (int)$countStmt->fetchColumn() > 0;


            /*
             * If this is the first address,
             * automatically make it default.
             *
             * If checkbox is selected,
             * also make it default.
             */

            if (
                $default ||
                !$hasAddresses
            ) {

                $pdo->prepare(
                    'UPDATE address
                     SET is_default = 0
                     WHERE user_id = ?'
                )->execute([
                    $user['user_id']
                ]);


                $default = true;
            }


            $pdo->prepare(
                'INSERT INTO address
                (
                    user_id,
                    street,
                    city,
                    postal_code,
                    is_default
                )
                VALUES (?, ?, ?, ?, ?)'
            )->execute([

                $user['user_id'],

                $street,

                $city,

                $postal,

                $default ? 1 : 0

            ]);


            $pdo->commit();


            flash(
                'success',
                'Address saved.'
            );


            redirect(
                'auth/addresses.php'
            );


        } catch (Throwable $e) {

            if (
                $pdo->inTransaction()
            ) {

                $pdo->rollBack();
            }


            $errors[] =
                'Could not save the address. Please try again.';
        }
    }
}


/*
 * Delete address.
 */

if (isset($_GET['delete'])) {

    $id =
        (int)$_GET['delete'];


    $stmt = $pdo->prepare(
        'DELETE FROM address
         WHERE address_id = ?
           AND user_id = ?'
    );


    $stmt->execute([

        $id,

        $user['user_id']

    ]);


    flash(
        'success',
        'Address removed.'
    );


    redirect(
        'auth/addresses.php'
    );
}


/*
 * Get addresses.
 */

$addressesStmt = $pdo->prepare(
    'SELECT *
     FROM address
     WHERE user_id = ?
     ORDER BY is_default DESC,
              address_id DESC'
);


$addressesStmt->execute([
    $user['user_id']
]);


$addresses =
    $addressesStmt->fetchAll();


$page_title =
    'Saved addresses';

require __DIR__ .
    '/../includes/header.php';

?>

<div class="container section">

    <div class="section-head">

        <div>

            <h2>Saved addresses</h2>

            <p class="muted">
                Choose a default address for faster checkout.
            </p>

        </div>

    </div>


    <div
        class="cart-layout"
        style="padding-top:0"
    >

        <div>

            <?php foreach ($addresses as $a): ?>

                <div class="order-card">

                    <div class="order-head">

                        <strong>
                            <?= e($a['street']) ?>
                        </strong>


                        <?php if ($a['is_default']): ?>

                            <span class="status">
                                Default
                            </span>

                        <?php endif; ?>

                    </div>


                    <p class="muted">

                        <?= e($a['city']) ?>

                        ·

                        <?= e($a['postal_code']) ?>

                    </p>


                    <div
                        style="
                            display:flex;
                            gap:10px;
                            flex-wrap:wrap;
                            align-items:center
                        "
                    >

                        <?php if (!$a['is_default']): ?>

                            <form
                                method="post"
                                style="margin:0"
                            >

                                <input
                                    type="hidden"
                                    name="csrf"
                                    value="<?= e(csrf_token()) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="make_default"
                                >

                                <input
                                    type="hidden"
                                    name="address_id"
                                    value="<?= e((string)$a['address_id']) ?>"
                                >

                                <button
                                    class="btn btn-secondary btn-small"
                                    type="submit"
                                >
                                    Make default
                                </button>

                            </form>

                        <?php endif; ?>


                        <a
                            class="back-link"
                            data-confirm="Remove this address?"
                            href="<?= e(
                                app_url(
                                    'auth/addresses.php?delete=' .
                                    $a['address_id']
                                )
                            ) ?>"
                        >
                            Remove
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>


            <?php if (!$addresses): ?>

                <div class="empty">

                    <h3>
                        No saved addresses
                    </h3>

                    <p class="muted">
                        Add one using the form.
                    </p>

                </div>

            <?php endif; ?>

        </div>


        <div class="panel">

            <h3>
                Add address
            </h3>


            <?php foreach ($errors as $err): ?>

                <div class="flash error">
                    <?= e($err) ?>
                </div>

            <?php endforeach; ?>


            <form method="post">

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= e(csrf_token()) ?>"
                >


                <div class="field">

                    <label>
                        Street
                    </label>

                    <textarea
                        name="street"
                        rows="3"
                        required
                    ><?= e($_POST['street'] ?? '') ?></textarea>

                </div>


                <div
                    class="field"
                    style="margin-top:12px"
                >

                    <label>
                        City
                    </label>

                    <input
                        name="city"
                        required
                        value="<?= e($_POST['city'] ?? '') ?>"
                    >

                </div>


                <div
                    class="field"
                    style="margin-top:12px"
                >

                    <label>
                        Postal code
                    </label>

                    <input
                        name="postal_code"
                        required
                        value="<?= e($_POST['postal_code'] ?? '') ?>"
                    >

                </div>


                <label
                    style="
                        display:flex;
                        gap:8px;
                        margin:14px 0
                    "
                >

                    <input
                        type="checkbox"
                        name="is_default"
                    >

                    Set as default

                </label>


                <button
                    class="btn btn-block"
                    type="submit"
                >
                    Save address
                </button>

            </form>

        </div>

    </div>

</div>


<?php require __DIR__ . '/../includes/footer.php'; ?>