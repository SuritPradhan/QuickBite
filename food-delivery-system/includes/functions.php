<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    return APP_BASE . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . app_url($path));
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid security token. Please go back and try again.');
    }
}

function money(float $amount): string
{
    return CURRENCY . number_format($amount, 2);
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, name, email, phone, address, role, created_at FROM users WHERE id = ?');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function admin_otp_verified(): bool
{
    return !empty($_SESSION['admin_otp_verified']);
}

function create_admin_otp(array $user): string
{
    $otp = (string) random_int(100000, 999999);
    $_SESSION['pending_admin_id'] = (int) $user['id'];
    $_SESSION['pending_admin_email'] = (string) $user['email'];
    $_SESSION['admin_otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
    $_SESSION['admin_otp_expires'] = time() + 300;
    $_SESSION['admin_otp_attempts'] = 0;
    unset($_SESSION['admin_otp_verified']);

    return $otp;
}

function clear_admin_otp(): void
{
    unset(
        $_SESSION['pending_admin_id'],
        $_SESSION['pending_admin_email'],
        $_SESSION['admin_otp_hash'],
        $_SESSION['admin_otp_expires'],
        $_SESSION['admin_otp_attempts']
    );
}

function create_admin_reset_otp(array $user): string
{
    $otp = (string) random_int(100000, 999999);
    $_SESSION['reset_admin_id'] = (int) $user['id'];
    $_SESSION['reset_admin_email'] = (string) $user['email'];
    $_SESSION['reset_admin_otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
    $_SESSION['reset_admin_otp_expires'] = time() + 300;
    $_SESSION['reset_admin_otp_attempts'] = 0;

    return $otp;
}

function clear_admin_reset_otp(): void
{
    unset(
        $_SESSION['reset_admin_id'],
        $_SESSION['reset_admin_email'],
        $_SESSION['reset_admin_otp_hash'],
        $_SESSION['reset_admin_otp_expires'],
        $_SESSION['reset_admin_otp_attempts']
    );
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please login to continue.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        set_flash('danger', 'Admin access required.');
        redirect('login.php');
    }

    if (!admin_otp_verified()) {
        set_flash('warning', 'Please verify admin OTP first.');
        redirect('admin-otp.php');
    }
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart_items()));
}

function cart_add(int $itemId, int $quantity): void
{
    $quantity = max(1, min(20, $quantity));
    $_SESSION['cart'][$itemId] = min(20, ($_SESSION['cart'][$itemId] ?? 0) + $quantity);
}

function cart_update(int $itemId, int $quantity): void
{
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$itemId]);
        return;
    }
    $_SESSION['cart'][$itemId] = min(20, $quantity);
}

function cart_details(): array
{
    $cart = cart_items();
    if (!$cart) {
        return ['items' => [], 'subtotal' => 0.0, 'delivery_fee' => 0.0, 'total' => 0.0];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders) AND is_available = 1");
    $stmt->execute($ids);
    $items = [];
    $subtotal = 0.0;

    foreach ($stmt->fetchAll() as $item) {
        $quantity = (int) ($cart[$item['id']] ?? 0);
        $lineTotal = (float) $item['price'] * $quantity;
        $item['quantity'] = $quantity;
        $item['line_total'] = $lineTotal;
        $items[] = $item;
        $subtotal += $lineTotal;
    }

    $deliveryFee = $subtotal >= 40 || $subtotal <= 0 ? 0.0 : 4.99;

    return [
        'items' => $items,
        'subtotal' => $subtotal,
        'delivery_fee' => $deliveryFee,
        'total' => $subtotal + $deliveryFee,
    ];
}

function order_statuses(): array
{
    return ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
}
