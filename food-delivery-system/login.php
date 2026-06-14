<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] === 'admin') {
            $otp = create_admin_otp($user);
            set_flash('success', 'Admin OTP sent. Local demo OTP: ' . $otp);
            redirect('admin-otp.php');
        }

        login_user($user);
        set_flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect('menu.php');
    }

    set_flash('danger', 'Invalid email or password.');
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <div>
        <p class="eyebrow">Login</p>
        <h1>Access your orders and checkout securely.</h1>
        <p>Demo admin: <strong>admin@quickbite.test</strong> / <strong>admin123</strong></p>
        <p>Demo customer: <strong>customer@quickbite.test</strong> / <strong>customer123</strong></p>
    </div>
    <form class="auth-card" method="post">
        <?= csrf_field() ?>
        <label>Email<input type="email" name="email" required></label>
        <label>Password<input type="password" name="password" required></label>
        <button class="button button-primary full" type="submit">Login</button>
        <p class="small-text"><a href="<?= app_url('forgot-admin-password.php') ?>">Forgot admin password?</a></p>
        <p class="small-text">New customer? <a href="<?= app_url('register.php') ?>">Create account</a></p>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
