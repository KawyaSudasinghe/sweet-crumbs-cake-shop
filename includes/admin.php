<?php
require_once __DIR__ . '/auth.php';

/**
 * Allow only authenticated users whose database role is admin.
 */
function require_admin(): array
{
    $user = current_user();

    if (!$user) {
        $_SESSION['after_admin_login'] = $_SERVER['REQUEST_URI'] ?? 'admin/index.php';
        flash('error', 'Please sign in as an administrator.');
        header('Location: ' . app_url('admin/login.php'));
        exit;
    }

    if (($user['role'] ?? 'customer') !== 'admin') {
        http_response_code(403);
        exit('Access denied. Administrator privileges are required.');
    }

    return $user;
}

/**
 * Status changes are deliberately one-way so staff cannot accidentally
 * move an order backwards in the normal interface.
 */
function admin_status_transition_allowed(string $current, string $next): bool
{
    return match ($current) {
        'confirmed' => $next === 'shipped',
        'shipped'   => $next === 'delivered',
        default     => false,
    };
}
