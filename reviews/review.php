<?php
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();
$productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$pdo = db();
$errors = [];

$stmt = $pdo->prepare(
    'SELECT *
     FROM product
     WHERE product_id = ? AND is_available = 1'
);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Product not found.');
    redirect('catalog/products.php');
}

/*
 * A review is allowed only when the customer has a delivered order
 * containing this product. Payment confirmation alone is not enough.
 */
$eligibility = $pdo->prepare(
    "SELECT oi.order_id
     FROM order_item oi
     JOIN `order` o ON o.order_id = oi.order_id
     JOIN payment p ON p.order_id = o.order_id
     WHERE o.user_id = ?
       AND oi.product_id = ?
       AND o.status = 'delivered'
       AND p.status = 'success'
     LIMIT 1"
);
$eligibility->execute([$user['user_id'], $productId]);

if (!$eligibility->fetch()) {
    flash(
        'error',
        'You can review this product after an order containing it has been delivered.'
    );
    redirect('catalog/product.php?id=' . $productId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Rating must be between 1 and 5.';
    }

    if (mb_strlen($comment) > 1000) {
        $errors[] = 'Review is too long.';
    }

    $check = $pdo->prepare(
        'SELECT review_id
         FROM review
         WHERE user_id = ? AND product_id = ?'
    );
    $check->execute([$user['user_id'], $productId]);

    if ($check->fetch()) {
        $errors[] = 'You have already reviewed this product.';
    }

    if (!$errors) {
        $insert = $pdo->prepare(
            'INSERT INTO review(product_id, user_id, rating, comment)
             VALUES(?,?,?,?)'
        );
        $insert->execute([
            $productId,
            $user['user_id'],
            $rating,
            $comment !== '' ? $comment : null
        ]);

        flash('success', 'Your review was added.');
        redirect('catalog/product.php?id=' . $productId);
    }
}

$page_title = 'Write a review';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="form-card">
        <span class="eyebrow">Delivered order</span>
        <h1>Review <?= e($product['name']) ?></h1>
        <p class="muted">Share your experience with this product.</p>

        <?php foreach ($errors as $err): ?>
            <div class="flash error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="product_id" value="<?= e((string)$productId) ?>">

            <div class="field">
                <label for="rating">Rating</label>
                <select id="rating" name="rating" required>
                    <option value="">Select</option>
                    <option value="5">5 — Excellent</option>
                    <option value="4">4 — Very good</option>
                    <option value="3">3 — Good</option>
                    <option value="2">2 — Fair</option>
                    <option value="1">1 — Poor</option>
                </select>
            </div>

            <div class="field" style="margin-top:14px">
                <label for="comment">Comment</label>
                <textarea id="comment" name="comment" rows="5" maxlength="1000"></textarea>
            </div>

            <button class="btn btn-block" style="margin-top:16px" type="submit">
                Submit review
            </button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
