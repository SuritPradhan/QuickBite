<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || $password !== $confirm) {
        set_flash('danger', 'Please enter valid details. Password must be at least 6 characters and match confirmation.');
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $phone, $address, password_hash($password, PASSWORD_DEFAULT)]);
            $userId = (int) db()->lastInsertId();
            login_user(['id' => $userId]);
            set_flash('success', 'Account created. Welcome to QuickBite.');
            redirect('menu.php');
        } catch (PDOException $exception) {
            set_flash('danger', 'That email is already registered.');
        }
    }
}

$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <div>
        <p class="eyebrow">Create account</p>
        <h1>Save your delivery details and order faster.</h1>
        <p>Register once, then use your account to checkout and track your orders.</p>
    </div>
    <form class="auth-card" method="post">
        <?= csrf_field() ?>
        <label>Name<input type="text" name="name" required></label>
        <label>Email<input type="email" name="email" required></label>
        <label>Phone<input type="tel" name="phone"></label>
        <label>Address<textarea name="address" rows="3"></textarea></label>
        <label>Password<input type="password" name="password" minlength="6" required></label>
        <label>Confirm Password<input type="password" name="confirm_password" minlength="6" required></label>
        <button class="button button-primary full" type="submit">Register</button>
        <p class="small-text">Already have an account? <a href="<?= app_url('login.php') ?>">Login</a></p>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

