<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$pdo = db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'save') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $imageClass = preg_replace('/[^a-z]/', '', strtolower((string) ($_POST['image_class'] ?? 'meal')));
        $featured = isset($_POST['is_featured']) ? 1 : 0;
        $available = isset($_POST['is_available']) ? 1 : 0;

        if ($name !== '' && $description !== '' && $price > 0 && $categoryId > 0) {
            if ($id > 0) {
                $stmt = $pdo->prepare('
                    UPDATE menu_items
                    SET category_id = ?, name = ?, description = ?, price = ?, image_class = ?, is_featured = ?, is_available = ?
                    WHERE id = ?
                ');
                $stmt->execute([$categoryId, $name, $description, $price, $imageClass ?: 'meal', $featured, $available, $id]);
                set_flash('success', 'Menu item updated.');
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO menu_items (category_id, name, description, price, image_class, is_featured, is_available)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([$categoryId, $name, $description, $price, $imageClass ?: 'meal', $featured, $available]);
                set_flash('success', 'Menu item added.');
            }
        } else {
            set_flash('danger', 'Please complete all menu item fields.');
        }
        redirect('admin/menu.php');
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Menu item deleted.');
        redirect('admin/menu.php');
    }
}

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ?');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch() ?: null;
}

$items = $pdo->query('
    SELECT menu_items.*, categories.name AS category_name
    FROM menu_items
    JOIN categories ON categories.id = menu_items.category_id
    ORDER BY menu_items.created_at DESC
')->fetchAll();

$pageTitle = 'Admin Menu';
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
                <p class="eyebrow">Kitchen catalog</p>
                <h2><?= $editItem ? 'Edit item' : 'Menu items' ?></h2>
            </div>
        </div>
        <form class="admin-form" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) ($editItem['id'] ?? 0) ?>">
            <label>Name<input type="text" name="name" value="<?= e($editItem['name'] ?? '') ?>" required></label>
            <label>Category
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) ($editItem['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Price<input type="number" step="0.01" min="0.01" name="price" value="<?= e((string) ($editItem['price'] ?? '')) ?>" required></label>
            <label>Image Style
                <select name="image_class">
                    <?php foreach (['pizza', 'burger', 'curry', 'wrap', 'dessert', 'drink', 'meal'] as $style): ?>
                        <option value="<?= e($style) ?>" <?= ($editItem['image_class'] ?? '') === $style ? 'selected' : '' ?>><?= e(ucfirst($style)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="wide">Description<textarea name="description" rows="3" required><?= e($editItem['description'] ?? '') ?></textarea></label>
            <label class="check"><input type="checkbox" name="is_featured" <?= (int) ($editItem['is_featured'] ?? 0) === 1 ? 'checked' : '' ?>> Featured</label>
            <label class="check"><input type="checkbox" name="is_available" <?= (int) ($editItem['is_available'] ?? 1) === 1 ? 'checked' : '' ?>> Available</label>
            <button class="button button-primary" type="submit"><?= $editItem ? 'Save Changes' : 'Add Item' ?></button>
            <?php if ($editItem): ?><a class="button button-light" href="<?= app_url('admin/menu.php') ?>">Cancel</a><?php endif; ?>
        </form>

        <div class="table-card">
            <h3>All menu items</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Featured</th><th>Available</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['name']) ?></td>
                            <td><?= e($item['category_name']) ?></td>
                            <td><?= money((float) $item['price']) ?></td>
                            <td><?= $item['is_featured'] ? 'Yes' : 'No' ?></td>
                            <td><?= $item['is_available'] ? 'Yes' : 'No' ?></td>
                            <td class="actions">
                                <a class="button button-light" href="<?= app_url('admin/menu.php?edit=' . (int) $item['id']) ?>">Edit</a>
                                <form method="post" data-confirm="Delete this menu item?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button class="button button-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

