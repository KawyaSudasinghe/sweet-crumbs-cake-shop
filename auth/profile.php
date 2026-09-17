<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Please enter a valid name.';
    }

    if ($phone === '' || !valid_phone($phone)) {
        $errors[] = 'Enter a valid Sri Lankan mobile number such as 0771234567.';
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE `user` SET name = ?, phone = ? WHERE user_id = ?');
        $stmt->execute([$name, $phone, $user['user_id']]);
        flash('success', 'Profile updated successfully.');
        redirect('auth/profile.php');
    }
}

$page_title = 'Profile';
require __DIR__ . '/../includes/header.php';
?>
<div class="container section">
    <div class="section-head">
        <div>
            <span class="eyebrow">Account</span>
            <h2>Your profile</h2>
            <p class="muted">Manage your account details and saved addresses.</p>
        </div>
    </div>

    <div class="cart-layout" style="padding-top:0">
        <section class="panel">
            <h3>Account information</h3>
            <?php foreach ($errors as $error): ?>
                <div class="flash error"><?= e($error) ?></div>
            <?php endforeach; ?>

            <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" maxlength="100" required value="<?= e($user['name']) ?>">
                </div>

                <div class="field" style="margin-top:14px">
                    <label>Email</label>
                    <input value="<?= e($user['email']) ?>" disabled>
                </div>

                <div class="field" style="margin-top:14px">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" maxlength="20" required value="<?= e($user['phone'] ?? '') ?>">
                    <span class="help">A valid phone number is required by the PayHere payment request.</span>
                </div>

                <button class="btn" style="margin-top:16px" type="submit">Save changes</button>
            </form>
        </section>

        <section class="panel">
            <h3>Quick links</h3>
            <a class="btn btn-block" href="<?= e(app_url('auth/addresses.php')) ?>">Saved addresses</a>
            <a class="btn btn-secondary btn-block" style="margin-top:10px" href="<?= e(app_url('orders/orders.php')) ?>">My orders</a>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
