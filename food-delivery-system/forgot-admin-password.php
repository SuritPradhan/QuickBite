<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));

    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $otp = create_admin_reset_otp($user);
        set_flash('success', 'Password reset OTP sent. Local demo OTP: ' . $otp);
        redirect('reset-admin-password.php');
    }

    set_flash('danger', 'No admin account found with this email.');
}

$pageTitle = 'Forgot Admin Password';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <div>
        <p class="eyebrow">Admin recovery</p>
        <h1>Reset the admin password using an OTP.</h1>
        <p>Enter your admin email. On local XAMPP, the reset OTP will show on the next message.</p>
    </div>
    <form class="auth-card" method="post">
        <?= csrf_field() ?>
        <label>Admin Email<input type="email" name="email" required></label>
        <button class="button button-primary full" type="submit">Send Reset OTP</button>
        <p class="small-text"><a href="<?= app_url('login.php') ?>">Back to login</a></p>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
