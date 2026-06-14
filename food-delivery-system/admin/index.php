<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$pageTitle = 'Admin Dashboard';
$stats = [
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'revenue' => (float) db()->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'Cancelled'")->fetchColumn(),
    'customers' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'items' => (int) db()->query('SELECT COUNT(*) FROM menu_items')->fetchColumn(),
];
$recentOrders = db()->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 6')->fetchAll();

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
                <p class="eyebrow">Control room</p>
                <h2>Dashboard</h2>
            </div>
            <a class="button button-primary" href="<?= app_url('admin/menu.php') ?>">Manage Menu</a>
        </div>
        <div class="stat-grid">
            <div><span>Total Orders</span><strong><?= $stats['orders'] ?></strong></div>
            <div><span>Revenue</span><strong><?= money($stats['revenue']) ?></strong></div>
            <div><span>Customers</span><strong><?= $stats['customers'] ?></strong></div>
            <div><span>Menu Items</span><strong><?= $stats['items'] ?></strong></div>
        </div>
        <div class="table-card">
            <h3>Recent orders</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= money((float) $order['total']) ?></td>
                            <td><span class="status"><?= e($order['status']) ?></span></td>
                            <td><?= e(date('M d, Y', strtotime($order['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

