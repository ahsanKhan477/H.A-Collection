<?php
require_once '../includes/config.php';
$pageTitle = 'Order Placed';

if (!isset($_SESSION['order_success'])) {
    redirect(SITE_URL);
}

$orderNumber = $_SESSION['order_success'];
unset($_SESSION['order_success']);

require_once '../includes/header.php';
?>

<section class="success-section">
    <div class="container">
        <div class="success-card scroll-animate">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your order. Your order number is:</p>
            <div class="order-number"><?= sanitize($orderNumber) ?></div>
            <p>We will process your order shortly. You can track your order status in "My Orders".</p>
            <div class="success-actions">
                <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-primary">View My Orders</a>
                <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
