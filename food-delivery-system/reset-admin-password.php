<?php
require_once __DIR__ . '/includes/init.php';

if (empty($_SESSION['reset_admin_id']) || empty($_SESSION['reset_admin_otp_hash'])) {
    set_flash('warning', 'Please request an admin password reset first.');
    redirect('forgot-admin-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $otp = trim((string) ($_POST['otp'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $_SESSION['reset_admin_otp_attempts'] = (int) ($_SESSION['reset_admin_otp_attempts'] ?? 0) + 1;

    if ((int) ($_SESSION['reset_admin_otp_attempts'] ?? 0) > 5) {
        clear_admin_reset_otp();
        set_flash('danger', 'Too many wrong OTP attempts. Please request a new reset OTP.');
        redirect('forgot-admin-password.php');
    }

    if (time() > (int) ($_SESSION['reset_admin_otp_expires'] ?? 0)) {
        clear_admin_reset_otp();
        set_flash('danger', 'Reset OTP expired. Please request a new OTP.');
        redirect('forgot-admin-password.php');
    }

    if (!password_verify($otp, (string) $_SESSION['reset_admin_otp_hash'])) {
        set_flash('danger', 'Invalid reset OTP.');
    } elseif (strlen($password) < 8) {
        set_flash('danger', 'Password must be at least 8 characters.');
    } elseif ($password !== $confirmPassword) {
        set_flash('danger', 'Passwords do not match.');
    } else {
        $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ? AND role = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), (int) $_SESSION['reset_admin_id'], 'admin']);
        clear_admin_reset_otp();
        set_flash('success', 'Admin password changed. Please login with your new password.');
        redirect('login.php');
    }
}

$pageTitle = 'Reset Admin Password';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <div>
        <p class="eyebrow">Admin recovery</p>
        <h1>Create a new admin password.</h1>
        <p>Use the reset OTP and choose a strong password with at least 8 characters.</p>
    </div>
    <form class="auth-card" method="post">
        <?= csrf_field() ?>
        <label>Reset OTP<input type="text" name="otp" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required></label>
        <label>New Password<input type="password" name="password" minlength="8" required></label>
        <label>Confirm Password<input type="password" name="confirm_password" minlength="8" required></label>
        <button class="button button-primary full" type="submit">Change Admin Password</button>
        <p class="small-text"><a href="<?= app_url('forgot-admin-password.php') ?>">Request new OTP</a></p>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
