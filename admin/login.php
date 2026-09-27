<?php
require_once __DIR__ . '/../includes/auth.php';

$user = current_user();
if ($user && ($user['role'] ?? '') === 'admin') {
    redirect('admin/index.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!valid_email($email) || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        $stmt = db()->prepare(
            'SELECT user_id, name, email, password_hash, phone, role
             FROM `user`
             WHERE email = ?
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $account = $stmt->fetch();

        if (
            !$account ||
            !password_verify($password, $account['password_hash']) ||
            $account['role'] !== 'admin'
        ) {
            $error = 'Invalid administrator email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$account['user_id'];

            $next = $_SESSION['after_admin_login'] ?? 'admin/index.php';
            unset($_SESSION['after_admin_login']);

            redirect($next);
        }
    }
}

$page_title = 'Admin Login';
require __DIR__ . '/../includes/header.php';
?>
<div class="container section">
    <div class="form-card" style="max-width:520px;margin:0 auto">
        <span class="eyebrow">Sweet Crumbs</span>
        <h1>Administrator login</h1>
        <p class="muted">Manage orders and monitor customer reviews.</p>

        <?php if ($error): ?>
            <div class="flash error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="field">
                <label for="adminEmail">Email</label>
                <input id="adminEmail" type="email" name="email"
                       value="<?= e($email) ?>" required autocomplete="username">
            </div>

            <div class="field" style="margin-top:16px">
                <label for="adminPassword">Password</label>
                <input id="adminPassword" type="password" name="password"
                       required autocomplete="current-password">
            </div>

            <button class="btn btn-block" style="margin-top:18px" type="submit">
                <?= icon('lock', 18) ?> Sign in as admin
            </button>
        </form>

        <p class="muted" style="margin-top:18px">
            <a class="back-link" href="<?= e(app_url('index.php')) ?>">← Back to shop</a>
        </p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
