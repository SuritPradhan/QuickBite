<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$messages = db()->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Admin Messages';
include __DIR__ . '/../includes/header.php';
?>
<section class="admin-shell">
    <aside class="admin-sidebar">
        <h1>Admin</h1>
        <a href="<?= app_url('admin/index.php') ?>">Dashboard</a>
        <a href="<?= app_url('admin/orders.php') ?>">Orders</a>
        <a href="<?= app_url('admin/menu.php') ?>">Menu Items</a>
        <a href="<?= app_url('admin/users.php') ?>">Users</a>
        <a href="<?= app_url('admin/messages.php') ?>">Messages</a>
    </aside>
    <div class="admin-content">
        <div class="admin-title">
            <div>
                <p class="eyebrow">Inbox</p>
                <h2>Contact messages</h2>
            </div>
        </div>
        <div class="message-grid">
            <?php if (!$messages): ?>
                <div class="empty-state">No messages yet.</div>
            <?php endif; ?>
            <?php foreach ($messages as $message): ?>
                <article class="message-card">
                    <div>
                        <h3><?= e($message['subject']) ?></h3>
                        <p><?= e($message['name']) ?> - <?= e($message['email']) ?></p>
                    </div>
                    <p><?= e($message['message']) ?></p>
                    <span><?= e(date('M d, Y h:i A', strtotime($message['created_at']))) ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

