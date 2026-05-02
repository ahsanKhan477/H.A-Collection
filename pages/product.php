<?php
require_once '../includes/config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) redirect(SITE_URL . '/pages/shop.php');

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.slug = ? AND p.status = 'active'");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) redirect(SITE_URL . '/pages/shop.php');

$pageTitle = $product['title'];
$sizes = $product['sizes'] ? explode(',', $product['sizes']) : [];
$colors = $product['colors'] ? explode(',', $product['colors']) : [];
$images = $product['images'] ? explode(',', $product['images']) : [];
array_unshift($images, $product['image'] ?: '');
$images = array_filter($images);

// Get reviews
$reviewStmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$reviewStmt->execute([$product['id']]);
$reviews = $reviewStmt->fetchAll();
$avgRating = 0;
if (count($reviews) > 0) {
    $avgRating = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
}

// Related products
$relatedStmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' ORDER BY RAND() LIMIT 4");
$relatedStmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $relatedStmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li><a href="<?= SITE_URL ?>/pages/shop.php">Shop</a></li>
            <li><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_slug'] ?>"><?= sanitize($product['category_name']) ?></a></li>
            <li><?= sanitize($product['title']) ?></li>
        </ul>
    </div>
</div>

<section class="product-detail-section">
    <div class="container">
        <div class="product-detail scroll-animate">
            <!-- Image Gallery -->
            <div class="product-gallery">
                <div class="gallery-main" id="galleryMain">
                    <?php if (!empty($images)): ?>
                        <img src="<?= SITE_URL ?>/assets/images/products/<?= $images[0] ?>" alt="<?= sanitize($product['title']) ?>" id="mainImage">
                    <?php else: ?>
                        <div class="product-placeholder large"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    <?php if ($product['discount_price']): ?>
                        <span class="badge badge-sale badge-lg">
                            -<?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <div class="thumb <?= $i === 0 ? 'active' : '' ?>" onclick="changeImage('<?= $img ?>', this)">
                                <img src="<?= SITE_URL ?>/assets/images/products/<?= $img ?>" alt="Thumbnail <?= $i + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-detail-info">
                <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                <h1><?= sanitize($product['title']) ?></h1>

                <!-- Rating -->
                <div class="product-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= round($avgRating) ? 'filled' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span><?= $avgRating ?> (<?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?>)</span>
                </div>

                <!-- Price -->
                <div class="product-detail-price">
                    <?php if ($product['discount_price']): ?>
                        <span class="price-current"><?= formatPrice($product['discount_price']) ?></span>
                        <span class="price-old"><?= formatPrice($product['price']) ?></span>
                        <span class="price-save">You save <?= formatPrice($product['price'] - $product['discount_price']) ?></span>
                    <?php else: ?>
                        <span class="price-current"><?= formatPrice($product['price']) ?></span>
                    <?php endif; ?>
                </div>

                <p class="product-detail-desc"><?= nl2br(sanitize($product['description'])) ?></p>

                <!-- Variants -->
                <form id="addToCartForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <?php if (!empty($sizes)): ?>
                        <div class="variant-group">
                            <label>Size:</label>
                            <div class="variant-options">
                                <?php foreach ($sizes as $s): $s = trim($s); ?>
                                    <label class="variant-option">
                                        <input type="radio" name="size" value="<?= sanitize($s) ?>" required>
                                        <span><?= sanitize($s) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($colors)): ?>
                        <div class="variant-group">
                            <label>Color:</label>
                            <div class="variant-options">
                                <?php foreach ($colors as $c): $c = trim($c); ?>
                                    <label class="variant-option color-variant">
                                        <input type="radio" name="color" value="<?= sanitize($c) ?>" required>
                                        <span><?= sanitize($c) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quantity -->
                    <div class="variant-group">
                        <label>Quantity:</label>
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>">
                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                        <span class="stock-info"><?= $product['stock'] ?> in stock</span>
                    </div>

                    <div class="product-actions">
                        <button type="button" class="btn btn-primary btn-lg" onclick="addToCartDetail()">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                    </div>
                </form>

                <!-- Features -->
                <div class="product-features">
                    <div class="feature"><i class="fas fa-shipping-fast"></i> Free shipping over Rs. 5,000</div>
                    <div class="feature"><i class="fas fa-undo"></i> 7-day easy returns</div>
                    <div class="feature"><i class="fas fa-shield-alt"></i> Secure checkout</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews Section -->
<section class="reviews-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <h2>Customer Reviews</h2>
            <p><?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?></p>
        </div>

        <?php if (isLoggedIn()): ?>
            <div class="review-form-wrapper scroll-animate">
                <h3>Write a Review</h3>
                <form id="reviewForm" class="review-form">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="form-group">
                        <label>Rating:</label>
                        <div class="star-rating" id="starRating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" data-rating="<?= $i ?>" onclick="setRating(<?= $i ?>)"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="5">
                    </div>
                    <div class="form-group">
                        <label>Your Review:</label>
                        <textarea name="comment" rows="4" placeholder="Share your experience with this product..." required></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitReview()">Submit Review</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <div class="empty-state small">
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card scroll-animate">
                        <div class="review-header">
                            <div class="review-user">
                                <div class="review-avatar"><?= strtoupper(substr($review['full_name'], 0, 1)) ?></div>
                                <div>
                                    <strong><?= sanitize($review['full_name']) ?></strong>
                                    <span class="review-date"><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="review-comment"><?= nl2br(sanitize($review['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<section class="section products-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <h2>You May Also Like</h2>
        </div>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $index => $rp): ?>
                <div class="product-card scroll-animate" style="animation-delay: <?= $index * 0.1 ?>s">
                    <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $rp['slug'] ?>" class="product-image-link">
                        <div class="product-image">
                            <?php if ($rp['image']): ?>
                                <img src="<?= SITE_URL ?>/assets/images/products/<?= $rp['image'] ?>" alt="<?= sanitize($rp['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="product-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <?php if ($rp['discount_price']): ?>
                                <span class="badge badge-sale">-<?= round((($rp['price'] - $rp['discount_price']) / $rp['price']) * 100) ?>%</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?= sanitize($rp['category_name']) ?></span>
                        <h3 class="product-title"><a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $rp['slug'] ?>"><?= sanitize($rp['title']) ?></a></h3>
                        <div class="product-price">
                            <?php if ($rp['discount_price']): ?>
                                <span class="price-current"><?= formatPrice($rp['discount_price']) ?></span>
                                <span class="price-old"><?= formatPrice($rp['price']) ?></span>
                            <?php else: ?>
                                <span class="price-current"><?= formatPrice($rp['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-add-cart" onclick="addToCart(<?= $rp['id'] ?>)">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function changeImage(img, thumb) {
    document.getElementById('mainImage').src = '<?= SITE_URL ?>/assets/images/products/' + img;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

function changeQty(delta) {
    const input = document.getElementById('qtyInput');
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(val, parseInt(input.max)));
    input.value = val;
}

function addToCartDetail() {
    const form = document.getElementById('addToCartForm');
    const formData = new FormData(form);
    addToCart(formData.get('product_id'), formData.get('quantity'), formData.get('size'), formData.get('color'));
}

function setRating(rating) {
    document.getElementById('ratingInput').value = rating;
    document.querySelectorAll('#starRating .fa-star').forEach((star, i) => {
        star.classList.toggle('filled', i < rating);
    });
}

function submitReview() {
    const form = document.getElementById('reviewForm');
    const formData = new FormData(form);
    fetch('<?= SITE_URL ?>/api/review.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Review submitted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Error submitting review', 'error');
        }
    })
    .catch(() => showToast('Error submitting review', 'error'));
}
</script>

<?php require_once '../includes/footer.php'; ?>
