<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([(int) $user['id']]);
$orders = $stmt->fetchAll();

$itemsStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');

$pageTitle = 'My Orders';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact-hero">
    <p class="eyebrow">My orders</p>
    <h1>Track your food history.</h1>
    <p>Order statuses are updated by the admin panel.</p>
</section>
<section class="section">
    <?php if (!$orders): ?>
        <div class="empty-state">No orders yet. <a href="<?= app_url('menu.php') ?>">Start your first order</a>.</div>
    <?php endif; ?>
    <div class="order-list">
        <?php foreach ($orders as $order): ?>
            <?php $itemsStmt->execute([(int) $order['id']]); $items = $itemsStmt->fetchAll(); ?>
            <article class="order-card">
                <div class="order-head">
                    <div>
                        <h2>Order #<?= (int) $order['id'] ?></h2>
                        <p><?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></p>
                    </div>
                    <span class="status"><?= e($order['status']) ?></span>
                </div>
                <ul>
                    <?php foreach ($items as $item): ?>
                        <li><?= e($item['item_name']) ?> x <?= (int) $item['quantity'] ?> - <?= money((float) $item['price'] * (int) $item['quantity']) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="order-foot">
                    <span><?= e($order['payment_method']) ?></span>
                    <strong><?= money((float) $order['total']) ?></strong>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

