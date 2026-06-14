<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databaseDir = __DIR__ . '/../database';
    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0755, true);
    }

    $pdo = new PDO('sqlite:' . $databaseDir . '/food_delivery.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    initialize_database($pdo);

    return $pdo;
}

function initialize_database(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT,
            address TEXT,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'customer',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT
        );

        CREATE TABLE IF NOT EXISTS menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            price REAL NOT NULL,
            image_class TEXT NOT NULL DEFAULT 'meal',
            is_featured INTEGER NOT NULL DEFAULT 0,
            is_available INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            customer_name TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            customer_phone TEXT NOT NULL,
            delivery_address TEXT NOT NULL,
            payment_method TEXT NOT NULL,
            notes TEXT,
            subtotal REAL NOT NULL,
            delivery_fee REAL NOT NULL,
            total REAL NOT NULL,
            status TEXT NOT NULL DEFAULT 'Pending',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            menu_item_id INTEGER,
            item_name TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $adminPassword = password_hash('Surit2006', PASSWORD_DEFAULT);
    $customerPassword = password_hash('customer123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, address, password, role) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Surit Pradhan', 'pradhansurit78@gmail.com', '+91 70013 37485', 'Admin Office', $adminPassword, 'admin']);
    $stmt->execute(['Demo Customer', 'customer@quickbite.test', '+1 555 0188', '24 Market Street, Food City', $customerPassword, 'customer']);

    $categories = [
        ['Pizza', 'Fresh crusts, sauces, cheese, and toppings.'],
        ['Burgers', 'Stacked burgers, crispy sides, and meal combos.'],
        ['Indian', 'Comforting curries, biryani, and tandoori favorites.'],
        ['Desserts', 'Sweet finishes for every order.'],
        ['Drinks', 'Fresh juices, coolers, and classic beverages.'],
    ];

    $catStmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
    foreach ($categories as $category) {
        $catStmt->execute($category);
    }

    $catIds = [];
    foreach ($pdo->query('SELECT id, name FROM categories') as $category) {
        $catIds[$category['name']] = (int) $category['id'];
    }

    $items = [
        ['Pizza', 'Margherita Melt', 'San Marzano tomato, basil, and mozzarella on a stone-baked crust.', 12.99, 'pizza', 1],
        ['Pizza', 'Pepperoni Rush', 'Loaded pepperoni, chili flakes, oregano, and stretchy cheese.', 15.49, 'pizza', 1],
        ['Burgers', 'Classic Smash Burger', 'Double beef patty, cheddar, lettuce, pickles, and house sauce.', 13.75, 'burger', 1],
        ['Burgers', 'Crispy Veggie Burger', 'Golden veggie patty, slaw, tomato, and garlic mayo.', 11.50, 'burger', 0],
        ['Indian', 'Butter Chicken Bowl', 'Creamy butter chicken served with steamed rice and naan.', 14.25, 'curry', 1],
        ['Indian', 'Paneer Tikka Wrap', 'Tandoori paneer, peppers, mint chutney, and crisp salad.', 10.99, 'wrap', 0],
        ['Desserts', 'Chocolate Lava Cake', 'Warm chocolate cake with a soft molten center.', 6.50, 'dessert', 1],
        ['Desserts', 'Mango Cheesecake Cup', 'Creamy cheesecake layered with mango compote.', 5.99, 'dessert', 0],
        ['Drinks', 'Mint Lime Cooler', 'Fresh lime, mint, soda, and crushed ice.', 3.99, 'drink', 0],
        ['Drinks', 'Berry Smoothie', 'Mixed berries blended with yogurt and honey.', 4.75, 'drink', 0],
    ];

    $itemStmt = $pdo->prepare('
        INSERT INTO menu_items (category_id, name, description, price, image_class, is_featured)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    foreach ($items as $item) {
        $itemStmt->execute([$catIds[$item[0]], $item[1], $item[2], $item[3], $item[4], $item[5]]);
    }
}

