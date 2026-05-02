<?php
require_once '../includes/config.php';
$pageTitle = 'Checkout';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/pages/checkout.php';
    redirect(SITE_URL . '/pages/login.php');
}

// Get cart items
$stmt = $pdo->prepare("SELECT c.*, p.title, p.price, p.discount_price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    redirect(SITE_URL . '/pages/cart.php');
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal >= 5000 ? 0 : SHIPPING_FEE;
$total = $subtotal + $shipping;

// Get user info
$user = getUser($pdo, $_SESSION['user_id']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $postalCode = sanitize($_POST['postal_code'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');

    if (empty($fullName) || empty($email) || empty($phone) || empty($address) || empty($city)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $pdo->beginTransaction();

            $orderNumber = generateOrderNumber();
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, full_name, email, phone, address, city, postal_code, notes, subtotal, shipping_fee, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $orderNumber, $fullName, $email, $phone, $address, $city, $postalCode, $notes, $subtotal, $shipping, $total]);
            $orderId = $pdo->lastInsertId();

            // Add order items
            foreach ($cartItems as $item) {
                $itemPrice = $item['discount_price'] ?? $item['price'];
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_title, price, quantity, size, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $item['product_id'], $item['title'], $itemPrice, $item['quantity'], $item['size'], $item['color']]);

                // Update stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            $pdo->commit();

            $_SESSION['order_success'] = $orderNumber;
            redirect(SITE_URL . '/pages/order-success.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'An error occurred while placing your order. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <h1><i class="fas fa-lock"></i> Checkout</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="checkout-layout scroll-animate">
            <div class="checkout-form">
                <h2>Shipping Details</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="<?= sanitize($user['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= sanitize($user['email']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" required placeholder="+92 300 1234567">
                    </div>
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" value="<?= sanitize($user['city'] ?? '') ?>" required placeholder="Lahore">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Complete Address *</label>
                    <textarea id="address" name="address" rows="3" required placeholder="House/Flat No, Street, Area"><?= sanitize($user['address'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" placeholder="54000">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Order Notes (optional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Any special instructions for delivery..."></textarea>
                </div>

                <div class="payment-method">
                    <h3>Payment Method</h3>
                    <label class="payment-option active">
                        <input type="radio" name="payment" value="cod" checked>
                        <i class="fas fa-money-bill-wave"></i>
                        <div>
                            <strong>Cash on Delivery</strong>
                            <span>Pay when you receive your order</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="checkout-summary">
                <h2>Order Summary</h2>
                <div class="checkout-items">
                    <?php foreach ($cartItems as $item):
                        $itemPrice = $item['discount_price'] ?? $item['price'];
                    ?>
                        <div class="checkout-item">
                            <div class="checkout-item-image">
                                <?php if ($item['image']): ?>
                                    <img src="<?= SITE_URL ?>/assets/images/products/<?= $item['image'] ?>" alt="">
                                <?php endif; ?>
                                <span class="checkout-qty"><?= $item['quantity'] ?></span>
                            </div>
                            <div class="checkout-item-info">
                                <h4><?= sanitize($item['title']) ?></h4>
                                <?php if ($item['size']): ?><span>Size: <?= sanitize($item['size']) ?></span><?php endif; ?>
                                <?php if ($item['color']): ?><span>Color: <?= sanitize($item['color']) ?></span><?php endif; ?>
                            </div>
                            <div class="checkout-item-price"><?= formatPrice($itemPrice * $item['quantity']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
                <div class="summary-row"><span>Shipping</span><span><?= $shipping > 0 ? formatPrice($shipping) : 'Free' ?></span></div>
                <div class="summary-row total"><span>Total</span><span><?= formatPrice($total) ?></span></div>

                <button type="submit" class="btn btn-primary btn-full btn-lg">
                    <i class="fas fa-check-circle"></i> Place Order — <?= formatPrice($total) ?>
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
