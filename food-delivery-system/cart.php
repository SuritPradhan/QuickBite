<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $itemId = (int) ($_POST['item_id'] ?? 0);

    if ($action === 'update') {
        cart_update($itemId, (int) ($_POST['quantity'] ?? 0));
        set_flash('success', 'Cart updated.');
    }

    if ($action === 'clear') {
        unset($_SESSION['cart']);
        set_flash('success', 'Cart cleared.');
    }

    redirect('cart.php');
}

$pageTitle = 'Cart';
$cart = cart_details();
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact-hero">
    <p class="eyebrow">Your cart</p>
    <h1>Review your order.</h1>
    <p>Update quantities before moving to secure checkout.</p>
</section>

<section class="section cart-layout">
    <div class="cart-panel">
        <?php if (!$cart['items']): ?>
            <div class="empty-state">Your cart is empty. <a href="<?= app_url('menu.php') ?>">Browse the menu</a>.</div>
        <?php endif; ?>

        <?php foreach ($cart['items'] as $item): ?>
            <article class="cart-item">
                <?php
                    $imageName = strtolower($item['name']);
                    $imageName = preg_replace('/[^a-z0-9]+/', '-', $imageName);
                    $imageName = trim($imageName, '-');
                ?>

                    <img class="cart-photo"
                         src="<?= app_url('assets/img/' . $imageName . '.jpg') ?>"
                         alt="<?= e($item['name']) ?>">
                <div>
                    <h3><?= e($item['name']) ?></h3>
                    <p><?= money((float) $item['price']) ?> each</p>
                </div>
                <form method="post" class="qty-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                    <input class="qty-input" type="number" name="quantity" min="0" max="20" value="<?= (int) $item['quantity'] ?>">
                    <button class="button button-light" type="submit">Update</button>
                </form>
                <strong><?= money((float) $item['line_total']) ?></strong>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="summary-box">
        <h2>Order summary</h2>
        <div><span>Subtotal</span><strong><?= money((float) $cart['subtotal']) ?></strong></div>
        <div><span>Delivery</span><strong><?= money((float) $cart['delivery_fee']) ?></strong></div>
        <div class="summary-total"><span>Total</span><strong><?= money((float) $cart['total']) ?></strong></div>
        <a class="button button-primary full" href="<?= app_url('checkout.php') ?>">Checkout</a>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="clear">
            <button class="button button-light full" type="submit">Clear Cart</button>
        </form>
    </aside>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

