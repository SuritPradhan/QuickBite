<?php
require_once __DIR__ . '/includes/init.php';

if (empty($_SESSION['pending_admin_id']) || empty($_SESSION['admin_otp_hash'])) {
    set_flash('warning', 'Please login as admin first.');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $otp = trim((string) ($_POST['otp'] ?? ''));
    $_SESSION['admin_otp_attempts'] = (int) ($_SESSION['admin_otp_attempts'] ?? 0) + 1;

    if ((int) ($_SESSION['admin_otp_attempts'] ?? 0) > 5) {
        clear_admin_otp();
        set_flash('danger', 'Too many wrong OTP attempts. Please login again.');
        redirect('login.php');
    }

    if (time() > (int) ($_SESSION['admin_otp_expires'] ?? 0)) {
        clear_admin_otp();
        set_flash('danger', 'OTP expired. Please login again.');
        redirect('login.php');
    }

    if (password_verify($otp, (string) $_SESSION['admin_otp_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $_SESSION['pending_admin_id'];
        $_SESSION['admin_otp_verified'] = true;
        clear_admin_otp();
        set_flash('success', 'Admin verified successfully.');
        redirect('admin/index.php');
    }

    set_flash('danger', 'Invalid OTP. Please try again.');
}

$pageTitle = 'Admin OTP';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <div>
        <p class="eyebrow">Admin security</p>
        <h1>Enter the 6-digit OTP to open the admin panel.</h1>
        <p>For this XAMPP demo, the OTP appears in the success message after admin login. In live hosting, connect this page with email or SMS sending.</p>
    </div>
    <form class="auth-card" method="post">
        <?= csrf_field() ?>
        <label>Admin OTP<input type="text" name="otp" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required></label>
        <button class="button button-primary full" type="submit">Verify OTP</button>
        <p class="small-text"><a href="<?= app_url('login.php') ?>">Back to login</a></p>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
