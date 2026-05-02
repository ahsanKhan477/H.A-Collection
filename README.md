# H.A Collection — Premium Feminine E-Commerce Website

A complete, advanced e-commerce website built with **PHP, MySQL, HTML, CSS, and JavaScript** (no frameworks). Features a premium feminine design with dark/light mode, glassmorphism effects, and a fully responsive layout.

## Features

### Frontend
- **Dark/Light Mode** with toggle button and localStorage persistence
- **Sticky Header** with logo, live search, category dropdown, cart counter
- **Hero Slider** with animated promotional banners
- **Product Cards** — Portrait-style with hover zoom, discount badges, scroll animations
- **Shop Page** — Sidebar filters (category, price range, size, color), sorting, pagination
- **Product Detail** — Image gallery, size/color variants, quantity selector, reviews
- **AJAX Cart** — Add/update/remove without page reload, toast notifications
- **Checkout** — Requires login, shipping form, order summary, Cash on Delivery
- **Authentication** — Signup/Login with password hashing, session management
- **Responsive Design** — Mobile-first, works on all screen sizes

### Admin Dashboard
- Dashboard with stats (products, orders, users, revenue)
- Full CRUD for Products (add, edit, delete, image upload)
- Category management
- Order management with status updates
- User management

### Design
- Feminine aesthetic with soft pink, lavender, deep purple palette
- Glassmorphism cards with backdrop blur
- Smooth scroll animations (fade/slide on scroll)
- Modern typography (Playfair Display + Poppins)
- Gradient accents and hover effects

## Setup Instructions

### Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Web browser

### Installation

1. **Clone the repository:**
   ```bash
   git clone <repo-url> ha-collection
   cd ha-collection
   ```

2. **Import the database:**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Configure database connection:**
   Edit `includes/config.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ha_collection');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('SITE_URL', 'http://localhost:8000');
   ```

4. **Start the PHP development server:**
   ```bash
   php -S localhost:8000
   ```

5. **Open in browser:**
   Visit `http://localhost:8000`

### Admin Access
- URL: `http://localhost:8000/admin/`
- Email: `admin@hacollection.com`
- Password: `password`

## Folder Structure

```
ha-collection/
├── index.php                  # Homepage
├── database.sql               # MySQL schema + sample data
├── README.md
├── includes/
│   ├── config.php             # DB connection, helpers
│   ├── header.php             # Shared header
│   └── footer.php             # Shared footer
├── pages/
│   ├── shop.php               # Product listing with filters
│   ├── product.php            # Product detail page
│   ├── cart.php               # Shopping cart
│   ├── checkout.php           # Checkout page
│   ├── order-success.php      # Order confirmation
│   ├── orders.php             # User order history
│   ├── login.php              # Login
│   ├── signup.php             # Registration
│   └── logout.php             # Logout
├── api/
│   ├── cart.php               # AJAX cart operations
│   ├── search.php             # Live search API
│   └── review.php             # Submit review API
├── admin/
│   ├── index.php              # Dashboard
│   ├── login.php              # Admin login
│   ├── products.php           # Product management
│   ├── product-form.php       # Add/Edit product
│   ├── categories.php         # Category management
│   ├── orders.php             # Order management
│   ├── users.php              # User management
│   └── includes/
│       ├── admin-header.php
│       └── admin-footer.php
└── assets/
    ├── css/
    │   ├── style.css          # Frontend styles
    │   └── admin.css          # Admin styles
    ├── js/
    │   ├── main.js            # Frontend JavaScript
    │   └── admin.js           # Admin JavaScript
    └── images/
        ├── products/          # Product images
        └── banners/           # Banner images
```

## Currency
All prices are displayed in **PKR (Rs.)**.

## Database Tables
- `users` — Customer and admin accounts
- `products` — Product catalog
- `categories` — Product categories
- `cart` — Shopping cart items
- `orders` — Customer orders
- `order_items` — Individual items in each order
- `reviews` — Product reviews and ratings
