<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $message === '') {
        set_flash('danger', 'Please complete all contact fields with a valid email.');
    } else {
        $stmt = db()->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $subject, $message]);
        set_flash('success', 'Thanks. Your message has been saved.');
        redirect('contact.php');
    }
}

$pageTitle = 'Contact';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact-hero">
    <p class="eyebrow">Contact</p>
    <h1>Need help with an order?</h1>
    <p>Send a message and the admin can view it from the dashboard.</p>
</section>
<section class="section form-section">
    <form class="auth-card" method="post">
        <?= csrf_field() ?>
        <label>Name<input type="text" name="name" required></label>
        <label>Email<input type="email" name="email" required></label>
        <label>Subject<input type="text" name="subject" required></label>
        <label>Message<textarea name="message" rows="5" required></textarea></label>
        <button class="button button-primary full" type="submit">Send Message</button>
    </form>
    <div class="contact-panel">
        <h2>Quick support</h2>
        <p><strong>Email:</strong> hello@quickbite.test</p>
        <p><strong>Phone:</strong> +1 555 0199</p>
        <p><strong>Hours:</strong> 10:00 AM - 11:00 PM</p>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

