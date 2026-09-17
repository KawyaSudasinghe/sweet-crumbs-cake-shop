<?php
require_once __DIR__ . '/../config/database.php';

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function icon(string $name, int $size = 20): string
{
    $paths = [
        'search' => '<circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path>',
        'cart' => '<path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 8H7"></path><circle cx="10" cy="20" r="1.5"></circle><circle cx="18" cy="20" r="1.5"></circle>',
        'user' => '<circle cx="12" cy="7" r="4"></circle><path d="M4 21c.8-4.1 3.4-6 8-6s7.2 1.9 8 6"></path>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"></path>',
        'heart' => '<path d="M20.8 8.7c0 5.1-8.8 10.3-8.8 10.3S3.2 13.8 3.2 8.7A4.7 4.7 0 0 1 12 6.4a4.7 4.7 0 0 1 8.8 2.3Z"></path>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"></path>',
        'plus' => '<path d="M12 5v14M5 12h14"></path>',
        'minus' => '<path d="M5 12h14"></path>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v6M14 11v6"></path>',
        'lock' => '<rect x="5" y="10" width="14" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>',
        'check' => '<path d="m5 12 4 4L19 6"></path>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"></path>',
        'box' => '<path d="m4 8 8-4 8 4-8 4-8-4Z"></path><path d="M4 8v8l8 4 8-4V8M12 12v8"></path>',
    ];
    $body = $paths[$name] ?? $paths['check'];
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid request token. Please go back and try again.');
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT user_id, name, email, phone, role FROM `user` WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Please sign in to continue.');
        redirect('auth/login.php');
    }
    return $user;
}

function cart_count(): int
{
    if (empty($_SESSION['user_id'])) return 0;
    $stmt = db()->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart_item ci JOIN cart c ON c.cart_id = ci.cart_id WHERE c.user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}

function get_or_create_cart(int $userId): int
{
    $stmt = db()->prepare('SELECT cart_id FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;
    $stmt = db()->prepare('INSERT INTO cart (user_id) VALUES (?)');
    $stmt->execute([$userId]);
    return (int)db()->lastInsertId();
}

function money(float $amount): string
{
    return 'LKR ' . number_format($amount, 2);
}

function valid_email(string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function valid_phone(string $phone): bool
{
    return (bool)preg_match('/^(?:\\+94|0)7[0-9]{8}$/', preg_replace('/\\s+/', '', $phone));
}

function valid_password(string $password): bool
{
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password);
}

function safe_image(?string $image): string
{
    $image = trim((string)$image);
    if ($image === '') {
        return app_url('assets/images/cakes.svg');
    }
    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }
    return app_url($image);
}

function app_url(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}
