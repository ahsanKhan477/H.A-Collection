<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/admin/login.php');
}

$pageTitle = 'Dashboard';

// Stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Recent orders
$recentOrders = $pdo->query("SELECT o.*, u.full_name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-stats">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #c850c0, #4158d0);">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalProducts ?></h3>
            <p>Products</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalOrders ?></h3>
            <p>Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalUsers ?></h3>
            <p>Customers</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3><?= formatPrice($totalRevenue) ?></h3>
            <p>Revenue</p>
        </div>
    </div>
</div>

<div class="admin-grid">
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-clock"></i> Recent Orders</h2>
            <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                            <td><?= sanitize($order['user_name']) ?></td>
                            <td><?= formatPrice($order['total']) ?></td>
                            <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" class="text-center">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-bell"></i> Quick Actions</h2>
        </div>
        <div class="quick-actions">
            <a href="<?= SITE_URL ?>/admin/product-form.php" class="quick-action-btn">
                <i class="fas fa-plus-circle"></i> Add Product
            </a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="quick-action-btn">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <a href="<?= SITE_URL ?>/admin/orders.php" class="quick-action-btn">
                <i class="fas fa-truck"></i> Pending Orders (<?= $pendingOrders ?>)
            </a>
            <a href="<?= SITE_URL ?>/admin/users.php" class="quick-action-btn">
                <i class="fas fa-user-cog"></i> Manage Users
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
