<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$users = db()->query('SELECT id, name, email, phone, address, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Admin Users';
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
                <p class="eyebrow">Accounts</p>
                <h2>Users</h2>
            </div>
        </div>
        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['phone']) ?></td>
                            <td><span class="status"><?= e($user['role']) ?></span></td>
                            <td><?= e(date('M d, Y', strtotime($user['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

