<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Home';
$featured = db()->query('
    SELECT menu_items.*, categories.name AS category_name
    FROM menu_items
    JOIN categories ON categories.id = menu_items.category_id
    WHERE is_featured = 1 AND is_available = 1
    ORDER BY menu_items.id DESC
    LIMIT 6
')->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">Hot meals delivered fast</p>
        <h1>Order restaurant favorites from your sofa.</h1>
        <p>QuickBite brings fresh pizza, burgers, curries, desserts, and drinks to your door with secure checkout and live order status.</p>
        <div class="hero-actions">
            <a class="button button-primary" href="<?= app_url('menu.php') ?>">Order Now</a>
            <a class="button button-light" href="<?= app_url('about.php') ?>">How It Works</a>
        </div>
        <div class="hero-stats" aria-label="QuickBite highlights">
            <span><strong>30 min</strong> average delivery</span>
            <span><strong>50+</strong> daily dishes</span>
            <span><strong>4.9</strong> customer rating</span>
        </div>
    </div>
    <div class="hero-media">
        <img src="<?= app_url('assets/img/hero-food.png') ?>" alt="Fresh food prepared for delivery">
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <p class="eyebrow">Popular today</p>
        <h2>Featured meals</h2>
        <a href="<?= app_url('menu.php') ?>">View full menu</a>
    </div>
<div class="food-grid">
    <?php foreach ($featured as $item): ?>

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
                <div class="card-row">
                    <strong><?= money((float) $item['price']) ?></strong>
                    <form method="post" action="<?= app_url('menu.php') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                        <button class="icon-button" type="submit" aria-label="Add <?= e($item['name']) ?> to cart">+</button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>        
<section class="band">
    <div class="section-heading">
        <p class="eyebrow">Simple process</p>
        <h2>Dinner in three steps</h2>
    </div>
    <div class="steps">
        <div>
            <span>1</span>
            <h3>Choose your food</h3>
            <p>Filter by category, search favorites, and add meals to your cart.</p>
        </div>
        <div>
            <span>2</span>
            <h3>Checkout securely</h3>
            <p>Login, confirm delivery details, and place the order safely.</p>
        </div>
        <div>
            <span>3</span>
            <h3>Track status</h3>
            <p>Your order moves from pending to delivered inside your account.</p>
        </div>
    </div>
</section>

<section class="section split-section">
    <div>
        <p class="eyebrow">Why QuickBite</p>
        <h2>Built for busy lunch breaks and relaxed nights in.</h2>
        <p>Every order is saved in your account, the admin can update delivery status, and the database keeps menu, users, messages, and order history organized.</p>
    </div>
    <div class="feature-list">
        <span>Fresh local menu</span>
        <span>Secure password login</span>
        <span>Admin order control</span>
        <span>Responsive mobile design</span>
    </div>
</section>
    </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>

