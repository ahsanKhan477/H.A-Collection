<?php
require_once '../includes/config.php';
$pageTitle = 'My Orders';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/pages/orders.php';
    redirect(SITE_URL . '/pages/login.php');
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<section class="orders-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <h1><i class="fas fa-box"></i> My Orders</h1>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state scroll-animate">
                <i class="fas fa-box-open"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card scroll-animate">
                        <div class="order-header">
                            <div>
                                <strong>Order #<?= sanitize($order['order_number']) ?></strong>
                                <span class="order-date"><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></span>
                            </div>
                            <span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        </div>
                        <div class="order-body">
                            <?php
                            $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                            $itemsStmt->execute([$order['id']]);
                            $items = $itemsStmt->fetchAll();
                            ?>
                            <div class="order-items-list">
                                <?php foreach ($items as $item): ?>
                                    <div class="order-item-row">
                                        <span><?= sanitize($item['product_title']) ?> × <?= $item['quantity'] ?></span>
                                        <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="order-total">
                                <span>Total: <strong><?= formatPrice($order['total']) ?></strong></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
