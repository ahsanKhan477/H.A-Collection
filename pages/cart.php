<?php
require_once '../includes/config.php';
$pageTitle = 'Shopping Cart';

// Get cart items
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT c.*, p.title, p.price, p.discount_price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT c.*, p.title, p.price, p.discount_price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ?");
    $stmt->execute([session_id()]);
}
$cartItems = $stmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal >= 5000 ? 0 : SHIPPING_FEE;
$total = $subtotal + $shipping;

require_once '../includes/header.php';
?>

<section class="cart-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <h1><i class="fas fa-shopping-bag"></i> Shopping Cart</h1>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state scroll-animate">
                <i class="fas fa-shopping-bag"></i>
                <h3>Your Cart is Empty</h3>
                <p>Looks like you haven't added anything to your cart yet.</p>
                <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout scroll-animate">
                <div class="cart-items" id="cartItems">
                    <?php foreach ($cartItems as $item):
                        $itemPrice = $item['discount_price'] ?? $item['price'];
                    ?>
                        <div class="cart-item" id="cartItem-<?= $item['id'] ?>">
                            <div class="cart-item-image">
                                <?php if ($item['image']): ?>
                                    <img src="<?= SITE_URL ?>/assets/images/products/<?= $item['image'] ?>" alt="<?= sanitize($item['title']) ?>">
                                <?php else: ?>
                                    <div class="product-placeholder"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="cart-item-info">
                                <h3><?= sanitize($item['title']) ?></h3>
                                <?php if ($item['size']): ?>
                                    <span class="cart-variant">Size: <?= sanitize($item['size']) ?></span>
                                <?php endif; ?>
                                <?php if ($item['color']): ?>
                                    <span class="cart-variant">Color: <?= sanitize($item['color']) ?></span>
                                <?php endif; ?>
                                <div class="cart-item-price"><?= formatPrice($itemPrice) ?></div>
                            </div>
                            <div class="cart-item-actions">
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['id'] ?>, -1)">−</button>
                                    <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" id="qty-<?= $item['id'] ?>" onchange="setCartQty(<?= $item['id'] ?>, this.value)">
                                    <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['id'] ?>, 1)">+</button>
                                </div>
                                <div class="cart-item-total"><?= formatPrice($itemPrice * $item['quantity']) ?></div>
                                <button class="cart-remove-btn" onclick="removeFromCart(<?= $item['id'] ?>)" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cartSubtotal"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span id="cartShipping"><?= $shipping > 0 ? formatPrice($shipping) : 'Free' ?></span>
                    </div>
                    <?php if ($subtotal < 5000): ?>
                        <div class="shipping-notice">
                            <i class="fas fa-info-circle"></i> Add <?= formatPrice(5000 - $subtotal) ?> more for free shipping
                        </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="cartTotal"><?= formatPrice($total) ?></span>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= SITE_URL ?>/pages/checkout.php" class="btn btn-primary btn-full btn-lg">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/pages/login.php" class="btn btn-primary btn-full btn-lg" onclick="sessionStorage.setItem('redirect', '<?= SITE_URL ?>/pages/checkout.php')">
                            <i class="fas fa-sign-in-alt"></i> Login to Checkout
                        </a>
                        <p class="checkout-login-note">You need to login before placing an order.</p>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline btn-full">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
