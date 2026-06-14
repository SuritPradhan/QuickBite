<?php
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <meta name="description" content="Order fresh meals online with QuickBite food delivery.">
    <link rel="stylesheet" href="<?= app_url('assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= app_url('index.php') ?>" aria-label="<?= APP_NAME ?> home">
        <span class="brand-mark">QB</span>
        <span><?= APP_NAME ?></span>
    </a>
    <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open navigation">&equiv;</button>
    <nav class="site-nav" data-nav>
        <a href="<?= app_url('index.php') ?>">Home</a>
        <a href="<?= app_url('menu.php') ?>">Menu</a>
        <a href="<?= app_url('about.php') ?>">About</a>
        <a href="<?= app_url('contact.php') ?>">Contact</a>
        <a class="cart-link" href="<?= app_url('cart.php') ?>">Cart <span><?= cart_count() ?></span></a>
        <?php if ($user): ?>
            <a href="<?= app_url('orders.php') ?>">My Orders</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="<?= app_url('admin/index.php') ?>">Admin</a>
            <?php endif; ?>
            <a href="<?= app_url('logout.php') ?>">Logout</a>
        <?php else: ?>
            <a href="<?= app_url('login.php') ?>">Login</a>
            <a class="nav-cta" href="<?= app_url('register.php') ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<?php foreach (get_flash() as $flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
<main>
