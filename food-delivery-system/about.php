<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'About';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <p class="eyebrow">About us</p>
    <h1>Food delivery designed for everyday cravings.</h1>
    <p>QuickBite is a complete demo online food delivery system built with PHP, SQLite, HTML, CSS, and JavaScript.</p>
</section>
<section class="section split-section">
    <div>
        <h2>What the system includes</h2>
        <p>The website supports customer registration, secure login, searchable menu, cart, checkout, customer order history, contact messages, and a full admin panel for managing orders, menu items, and users.</p>
    </div>
    <div class="feature-list">
        <span>Prepared statements</span>
        <span>Password hashing</span>
        <span>CSRF form tokens</span>
        <span>Role-based admin access</span>
        <span>Responsive layouts</span>
        <span>Seeded database</span>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

