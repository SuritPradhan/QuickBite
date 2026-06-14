# QuickBite Online Food Delivery System

QuickBite is a complete PHP, HTML, CSS, and basic JavaScript food delivery website. It includes customer pages, login/register, cart, checkout, order tracking, contact messages, and an admin panel.

## Features

- Large responsive home page
- Searchable and filterable food menu
- Cart with quantity updates
- Register, login, logout
- Admin OTP verification before opening admin panel
- Admin forgot password with OTP reset
- Secure password hashing
- CSRF protection on forms
- Prepared database statements
- Customer checkout and order history
- Admin dashboard
- Admin order status updates
- Admin menu add, edit, and delete
- Admin users and contact messages
- SQLite database auto-created on first page load

## Default Login Details

Admin:

- Email: `admin@quickbite.test`
- Password: `admin123`

Customer:

- Email: `customer@quickbite.test`
- Password: `customer123`

Change these passwords after first login if you use this beyond a demo.

## Admin OTP And Forgot Password

Admin login now works in two steps:

1. Login with the admin email and password.
2. Enter the 6-digit OTP shown in the success message.
3. After OTP verification, the admin panel opens.

The forgot password link is available on `login.php`. For local XAMPP demos, the OTP is shown on screen because email/SMS sending is not configured. For live hosting, connect the generated OTP to SMTP email or an SMS API.

## How To Run

Option 1: XAMPP, WAMP, Laragon, or MAMP

1. Copy the `food-delivery-system` folder into your local web server folder, such as `htdocs`.
2. Start Apache.
3. Open `http://localhost/food-delivery-system/index.php`.
4. The SQLite database will be created automatically in `database/food_delivery.sqlite`.

Option 2: PHP built-in server

```bash
cd food-delivery-system
php -S localhost:8000
```

Then open `http://localhost:8000/index.php`.

## Requirements

- PHP 8.0 or newer
- PHP PDO SQLite extension enabled
- Apache recommended for `.htaccess` folder protection

## Important Files

- `index.php` - home page
- `menu.php` - menu and add-to-cart
- `cart.php` - cart management
- `checkout.php` - customer checkout
- `orders.php` - customer order history
- `admin/index.php` - admin dashboard
- `admin/orders.php` - order management
- `admin/menu.php` - menu management
- `config/database.php` - database setup and seed data
- `assets/css/style.css` - frontend styling
- `assets/js/app.js` - basic frontend JavaScript

## Security Notes

This project includes core demo security practices: hashed passwords, prepared statements, session regeneration on login, CSRF tokens, role checks for admin pages, escaped output, and Apache rules that block direct access to config and database folders.

For production, also use HTTPS, a stronger database server, rate limiting, audit logging, backups, and server-level access rules.
