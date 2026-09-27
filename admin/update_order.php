<?php
require_once __DIR__ . '/../includes/admin.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/orders.php');
}

verify_csrf();

$orderId = (int)($_POST['order_id'] ?? 0);
$nextStatus = $_POST['status'] ?? '';

if ($orderId <= 0 || !in_array($nextStatus, ['shipped', 'delivered'], true)) {
    flash('error', 'Invalid order status request.');
    redirect('admin/orders.php');
}

$pdo = db();

$stmt = $pdo->prepare(
    'SELECT status FROM `order` WHERE order_id = ? FOR UPDATE'
);
$stmt->execute([$orderId]);
$current = $stmt->fetchColumn();

if (!$current) {
    flash('error', 'Order not found.');
    redirect('admin/orders.php');
}

if (!admin_status_transition_allowed($current, $nextStatus)) {
    flash(
        'error',
        'That status change is not allowed. Orders must move from confirmed to shipped, then shipped to delivered.'
    );
    redirect('admin/order.php?id=' . $orderId);
}

$update = $pdo->prepare(
    'UPDATE `order` SET status = ? WHERE order_id = ? AND status = ?'
);
$update->execute([$nextStatus, $orderId, $current]);

if ($update->rowCount() !== 1) {
    flash('error', 'The order status could not be updated.');
} else {
    flash('success', 'Order #' . $orderId . ' marked as ' . $nextStatus . '.');
}

redirect('admin/order.php?id=' . $orderId);
