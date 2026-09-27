<?php

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$errors = [];


/* =========================
   HANDLE PROFILE UPDATE
   ========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name =
        trim($_POST['name'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');


    /* Validate name */

    if (
        $name === '' ||
        mb_strlen($name) > 100
    ) {

        $errors[] =
            'Please enter a valid name.';
    }


    /* Validate phone */

    if (
        $phone === '' ||
        !valid_phone($phone)
    ) {

        $errors[] =
            'Enter a valid Sri Lankan mobile number such as 0771234567.';
    }


    /* Update profile */

    if (!$errors) {

        $stmt = db()->prepare(
            'UPDATE `user`
             SET name = ?,
                 phone = ?
             WHERE user_id = ?'
        );


        $stmt->execute([
            $name,
            $phone,
            $user['user_id']
        ]);


        flash(
            'success',
            'Profile updated successfully.'
        );


        redirect(
            'auth/profile.php'
        );
    }


    /*
     * Keep the values entered by the user
     * when validation fails.
     */

    $user['name'] =
        $name;

    $user['phone'] =
        $phone;
}


$page_title = 'Profile';

require __DIR__ . '/../includes/header.php';

?>


<div class="container section">


    <!-- =========================
         PAGE HEADER
         ========================= -->

    <div class="section-head">

        <div>

            <span class="eyebrow">
                Account
            </span>

            <h2>
                Your profile
            </h2>

            <p class="muted">
                Manage your account details and saved addresses.
            </p>

        </div>

    </div>


    <div
        class="cart-layout"
        style="padding-top:0"
    >


        <!-- =========================
             ACCOUNT INFORMATION
             ========================= -->

        <section class="panel">

            <h3>
                Account information
            </h3>


            <!-- Validation errors -->

            <?php foreach ($errors as $error): ?>

                <div class="flash error">
                    <?= e($error) ?>
                </div>

            <?php endforeach; ?>


            <form
                method="post"
                id="profileForm"
            >

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= e(csrf_token()) ?>"
                >


                <!-- =========================
                     FULL NAME
                     ========================= -->

                <div class="field">

                    <label for="name">
                        Full name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        maxlength="100"
                        required
                        value="<?= e($user['name']) ?>"
                        readonly
                    >

                </div>


                <!-- =========================
                     EMAIL
                     ========================= -->

                <div
                    class="field"
                    style="margin-top:14px"
                >

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        value="<?= e($user['email']) ?>"
                        readonly
                    >

                </div>


                <!-- =========================
                     PHONE
                     ========================= -->

                <div
                    class="field"
                    style="margin-top:14px"
                >

                    <label for="phone">
                        Phone
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        maxlength="20"
                        required
                        value="<?= e($user['phone'] ?? '') ?>"
                        readonly
                    >

                    <span class="help">
                        A valid phone number is required
                        for the Stripe payment request.
                    </span>

                </div>


                <!-- =========================
                     BUTTONS
                     ========================= -->

                <div
                    id="profileButtons"
                    style="
                        display:flex;
                        gap:10px;
                        flex-wrap:wrap;
                        margin-top:16px;
                    "
                >

                    <!-- Edit button -->

                    <button
                        type="button"
                        class="btn"
                        id="editDetailsBtn"
                    >
                        Edit details
                    </button>


                    <!-- Save button -->

                    <button
                        type="submit"
                        class="btn"
                        id="saveChangesBtn"
                        style="display:none"
                    >
                        Save changes
                    </button>


                    <!-- Cancel button -->

                    <button
                        type="button"
                        class="btn btn-secondary"
                        id="cancelEditBtn"
                        style="display:none"
                    >
                        Cancel
                    </button>

                </div>


            </form>

        </section>


        <!-- =========================
             QUICK LINKS
             ========================= -->

        <section class="panel">

            <h3>
                Quick links
            </h3>


            <a
                class="btn btn-block"
                href="<?= e(
                    app_url(
                        'auth/addresses.php'
                    )
                ) ?>"
            >
                Saved addresses
            </a>

        </section>


    </div>

</div>


<script>

/*
 * Profile edit controls
 */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const nameInput =
            document.getElementById('name');

        const phoneInput =
            document.getElementById('phone');

        const editButton =
            document.getElementById(
                'editDetailsBtn'
            );

        const saveButton =
            document.getElementById(
                'saveChangesBtn'
            );

        const cancelButton =
            document.getElementById(
                'cancelEditBtn'
            );


        /*
         * Store the original values.
         * These are used if the user clicks Cancel.
         */

        const originalName =
            nameInput.value;

        const originalPhone =
            phoneInput.value;


        /*
         * Enable editing.
         */

        editButton.addEventListener(
            'click',
            function () {

                nameInput.readOnly =
                    false;

                phoneInput.readOnly =
                    false;


                /*
                 * Put the cursor in the
                 * name field.
                 */

                nameInput.focus();


                /*
                 * Hide Edit details.
                 */

                editButton.style.display =
                    'none';


                /*
                 * Show Save and Cancel.
                 */

                saveButton.style.display =
                    'inline-flex';

                cancelButton.style.display =
                    'inline-flex';

            }
        );


        /*
         * Cancel editing.
         */

        cancelButton.addEventListener(
            'click',
            function () {

                /*
                 * Restore original values.
                 */

                nameInput.value =
                    originalName;

                phoneInput.value =
                    originalPhone;


                /*
                 * Make fields read-only again.
                 */

                nameInput.readOnly =
                    true;

                phoneInput.readOnly =
                    true;


                /*
                 * Hide Save and Cancel.
                 */

                saveButton.style.display =
                    'none';

                cancelButton.style.display =
                    'none';


                /*
                 * Show Edit details again.
                 */

                editButton.style.display =
                    'inline-flex';

            }
        );

    }
);

</script>


<?php require __DIR__ . '/../includes/footer.php'; ?>