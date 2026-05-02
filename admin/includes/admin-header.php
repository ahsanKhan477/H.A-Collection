<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' : '' ?>Admin - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <span class="logo-text">H.A</span>
            <span class="logo-sub">Admin</span>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= SITE_URL ?>/admin/" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/admin/products.php" class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'product-form.php']) ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Products
            </a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a href="<?= SITE_URL ?>/admin/orders.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <a href="<?= SITE_URL ?>/admin/users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <div class="sidebar-divider"></div>
            <a href="<?= SITE_URL ?>" class="nav-item" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
            <a href="<?= SITE_URL ?>/pages/logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Admin Main -->
    <div class="admin-main">
        <header class="admin-header">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
            <div class="admin-header-actions">
                <button class="theme-toggle" id="adminThemeToggle" title="Toggle Theme">
                    <i class="fas fa-moon" id="adminThemeIcon"></i>
                </button>
                <span class="admin-user"><i class="fas fa-user-shield"></i> <?= sanitize($_SESSION['user_name']) ?></span>
            </div>
        </header>
        <div class="admin-content">
