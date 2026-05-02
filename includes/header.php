<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?><?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <span><i class="fas fa-shipping-fast"></i> Free Shipping on Orders Over Rs. 5,000</span>
                <div class="top-bar-right">
                    <span><i class="fas fa-phone"></i> +92 300 1234567</span>
                    <span><i class="fas fa-envelope"></i> info@hacollection.com</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header" id="mainHeader">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?= SITE_URL ?>" class="logo">
                    <span class="logo-text">H.A</span>
                    <span class="logo-sub">Collection</span>
                </a>

                <!-- Search Bar -->
                <div class="search-wrapper">
                    <div class="search-bar">
                        <select class="search-category" id="searchCategory">
                            <option value="">All Categories</option>
                            <?php
                            $cats = getCategories($pdo);
                            foreach ($cats as $cat): ?>
                                <option value="<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="searchInput" placeholder="Search for products..." autocomplete="off">
                        <button class="search-btn" id="searchBtn"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>

                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>

                    <!-- Account -->
                    <?php if (isLoggedIn()): ?>
                        <div class="header-dropdown">
                            <button class="header-action-btn">
                                <i class="fas fa-user"></i>
                                <span><?= sanitize($_SESSION['user_name']) ?></span>
                            </button>
                            <div class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <a href="<?= SITE_URL ?>/admin/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                <?php endif; ?>
                                <a href="<?= SITE_URL ?>/pages/orders.php"><i class="fas fa-box"></i> My Orders</a>
                                <a href="<?= SITE_URL ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/pages/login.php" class="header-action-btn">
                            <i class="fas fa-user"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>

                    <!-- Cart -->
                    <a href="<?= SITE_URL ?>/pages/cart.php" class="header-action-btn cart-btn">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="cart-count" id="cartCount"><?= getCartCount($pdo) ?></span>
                        <span>Cart</span>
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="main-nav" id="mainNav">
            <div class="container">
                <ul class="nav-list">
                    <li><a href="<?= SITE_URL ?>/" class="nav-link">Home</a></li>
                    <li class="has-dropdown">
                        <a href="<?= SITE_URL ?>/pages/shop.php" class="nav-link">Shop <i class="fas fa-chevron-down"></i></a>
                        <div class="mega-dropdown">
                            <?php foreach ($cats as $cat): ?>
                                <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li><a href="<?= SITE_URL ?>/pages/shop.php?filter=new" class="nav-link">New Arrivals</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/shop.php?filter=sale" class="nav-link sale-link">Sale</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/shop.php?filter=featured" class="nav-link">Featured</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <span class="logo-text">H.A Collection</span>
            <button class="mobile-nav-close" id="mobileNavClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="mobile-nav-search">
            <input type="text" placeholder="Search products..." id="mobileSearchInput">
        </div>
        <ul class="mobile-nav-list">
            <li><a href="<?= SITE_URL ?>/">Home</a></li>
            <li><a href="<?= SITE_URL ?>/pages/shop.php">Shop All</a></li>
            <?php foreach ($cats as $cat): ?>
                <li><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a></li>
            <?php endforeach; ?>
            <li><a href="<?= SITE_URL ?>/pages/shop.php?filter=sale">Sale</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?= SITE_URL ?>/pages/orders.php">My Orders</a></li>
                <li><a href="<?= SITE_URL ?>/pages/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/pages/login.php">Login / Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <main class="main-content">
