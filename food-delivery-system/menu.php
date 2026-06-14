<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);

    $stmt = db()->prepare('SELECT id FROM menu_items WHERE id = ? AND is_available = 1');
    $stmt->execute([$itemId]);
    if ($stmt->fetch()) {
        cart_add($itemId, $quantity);
        set_flash('success', 'Item added to cart.');
    } else {
        set_flash('danger', 'That item is not available.');
    }
    redirect('menu.php');
}

$pageTitle = 'Menu';
$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$search = trim((string) ($_GET['search'] ?? ''));
$category = (int) ($_GET['category'] ?? 0);

$where = ['menu_items.is_available = 1'];
$params = [];
if ($search !== '') {
    $where[] = '(menu_items.name LIKE ? OR menu_items.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category > 0) {
    $where[] = 'menu_items.category_id = ?';
    $params[] = $category;
}

$stmt = db()->prepare('
    SELECT menu_items.*, categories.name AS category_name
    FROM menu_items
    JOIN categories ON categories.id = menu_items.category_id
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY categories.name, menu_items.name
');
$stmt->execute($params);
$items = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact-hero">
    <p class="eyebrow">Explore the menu</p>
    <h1>Fresh meals ready for checkout.</h1>
    <p>Search dishes, filter by category, and build your perfect order.</p>
</section>

<section class="section">
    <form class="toolbar" method="get">
        <input type="search" name="search" placeholder="Search food..." value="<?= e($search) ?>">
        <select name="category">
            <option value="0">All categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= $category === (int) $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="button button-primary" type="submit">Filter</button>
        <a class="button button-light" href="<?= app_url('menu.php') ?>">Reset</a>
    </form>

    <?php if (!$items): ?>
        <div class="empty-state">No food matched your search.</div>
    <?php endif; ?>

<div class="food-grid">
    <?php foreach ($items as $item): ?>

        <?php
        $imageName = strtolower($item['name']);
        $imageName = preg_replace('/[^a-z0-9]+/', '-', $imageName);
        $imageName = trim($imageName, '-');
        ?>

        <article class="food-card">
            <img class="food-photo"
                 src="<?= app_url('assets/img/' . $imageName . '.jpg') ?>"
                 alt="<?= e($item['name']) ?>">

            <div class="food-card-body">
                <span class="pill"><?= e($item['category_name']) ?></span>
                <h3><?= e($item['name']) ?></h3>
                <p><?= e($item['description']) ?></p>
                <form class="card-row" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                    <strong><?= money((float) $item['price']) ?></strong>
                    <input class="qty-input" type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity">
                    <button class="button button-primary" type="submit">Add</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

