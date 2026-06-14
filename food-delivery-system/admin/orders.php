<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');

    if (in_array($status, order_statuses(), true)) {
        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        set_flash('success', 'Order status updated.');
    }
    redirect('admin/orders.php');
}

$orders = db()->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
$itemsStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');

$pageTitle = 'Admin Orders';
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
                <p class="eyebrow">Fulfillment</p>
                <h2>Orders</h2>
            </div>
        </div>
        <div class="order-list">
            <?php foreach ($orders as $order): ?>
                <?php $itemsStmt->execute([(int) $order['id']]); $items = $itemsStmt->fetchAll(); ?>
                <article class="order-card">
                    <div class="order-head">
                        <div>
                            <h2>Order #<?= (int) $order['id'] ?> - <?= e($order['customer_name']) ?></h2>
                            <p><?= e($order['customer_phone']) ?> | <?= e($order['delivery_address']) ?></p>
                        </div>
                        <span class="status"><?= e($order['status']) ?></span>
                    </div>
                    <ul>
                        <?php foreach ($items as $item): ?>
                            <li><?= e($item['item_name']) ?> x <?= (int) $item['quantity'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <form class="admin-row-form" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <select name="status">
                            <?php foreach (order_statuses() as $status): ?>
                                <option <?= $status === $order['status'] ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="button button-primary" type="submit">Update</button>
                        <strong><?= money((float) $order['total']) ?></strong>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

