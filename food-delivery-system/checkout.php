<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$cart = cart_details();
if (!$cart['items']) {
    set_flash('warning', 'Your cart is empty.');
    redirect('menu.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $payment = (string) ($_POST['payment_method'] ?? 'Cash on Delivery');
    $notes = trim((string) ($_POST['notes'] ?? ''));
    $allowedPayments = ['Cash on Delivery', 'Card on Delivery', 'UPI on Delivery'];

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $address === '' || !in_array($payment, $allowedPayments, true)) {
        set_flash('danger', 'Please complete all checkout details.');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('
                INSERT INTO orders
                    (user_id, customer_name, customer_email, customer_phone, delivery_address, payment_method, notes, subtotal, delivery_fee, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                (int) $user['id'],
                $name,
                $email,
                $phone,
                $address,
                $payment,
                $notes,
                $cart['subtotal'],
                $cart['delivery_fee'],
                $cart['total'],
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare('
                INSERT INTO order_items (order_id, menu_item_id, item_name, quantity, price)
                VALUES (?, ?, ?, ?, ?)
            ');
            foreach ($cart['items'] as $item) {
                $itemStmt->execute([$orderId, (int) $item['id'], $item['name'], (int) $item['quantity'], (float) $item['price']]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            set_flash('success', 'Order placed successfully.');
            redirect('orders.php');
        } catch (Throwable $throwable) {
            $pdo->rollBack();
            set_flash('danger', 'Unable to place order. Please try again.');
        }
    }
}

$pageTitle = 'Checkout';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact-hero">
    <p class="eyebrow">Checkout</p>
    <h1>Confirm delivery details.</h1>
    <p>Your order will be saved to your account after payment selection.</p>
</section>
<section class="section checkout-layout">
    <form class="auth-card" method="post">
        <?= csrf_field() ?>
        <label>Name<input type="text" name="name" value="<?= e($user['name']) ?>" required></label>
        <label>Email<input type="email" name="email" value="<?= e($user['email']) ?>" required></label>
        <label>Phone<input type="tel" name="phone" value="<?= e($user['phone']) ?>" required></label>
        <label>Delivery Address<textarea name="address" rows="4" required><?= e($user['address']) ?></textarea></label>
        <label>Payment Method
            <select name="payment_method" required>
                <option>Cash on Delivery</option>
                <option>Card on Delivery</option>
                <option>UPI on Delivery</option>
            </select>
        </label>
        <label>Order Notes<textarea name="notes" rows="3" placeholder="Allergies, landmark, delivery instructions"></textarea></label>
        <button class="button button-primary full" type="submit">Place Order</button>
    </form>
    <aside class="summary-box">
        <h2>Your order</h2>
        <?php foreach ($cart['items'] as $item): ?>
            <div><span><?= e($item['name']) ?> x <?= (int) $item['quantity'] ?></span><strong><?= money((float) $item['line_total']) ?></strong></div>
        <?php endforeach; ?>
        <div><span>Delivery</span><strong><?= money((float) $cart['delivery_fee']) ?></strong></div>
        <div class="summary-total"><span>Total</span><strong><?= money((float) $cart['total']) ?></strong></div>
    </aside>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

