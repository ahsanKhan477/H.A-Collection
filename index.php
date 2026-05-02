<?php
require_once 'includes/config.php';
$pageTitle = 'Premium Feminine Fashion';

// Get featured products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 'active' ORDER BY p.created_at DESC LIMIT 8");
$featuredProducts = $stmt->fetchAll();

// Get new arrivals
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_new = 1 AND p.status = 'active' ORDER BY p.created_at DESC LIMIT 8");
$newArrivals = $stmt->fetchAll();

// Get sale products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.discount_price IS NOT NULL AND p.status = 'active' ORDER BY p.created_at DESC LIMIT 4");
$saleProducts = $stmt->fetchAll();

// Get categories
$categories = getCategories($pdo);

require_once 'includes/header.php';
?>

<!-- Hero Slider -->
<section class="hero-slider" id="heroSlider">
    <div class="slider-wrapper">
        <div class="slide active" style="background: linear-gradient(135deg, #1a0a2e 0%, #3d1a6e 50%, #c850c0 100%);">
            <div class="slide-content">
                <span class="slide-tag">New Season</span>
                <h1>Summer Collection <span>2025</span></h1>
                <p>Discover the latest trends in feminine fashion. Elegant pieces designed to make you shine.</p>
                <a href="<?= SITE_URL ?>/pages/shop.php?filter=new" class="btn btn-primary">Shop New Arrivals</a>
            </div>
            <div class="slide-decoration">
                <div class="floating-circle c1"></div>
                <div class="floating-circle c2"></div>
                <div class="floating-circle c3"></div>
            </div>
        </div>
        <div class="slide" style="background: linear-gradient(135deg, #2d1b4e 0%, #8e44ad 50%, #f8b4d9 100%);">
            <div class="slide-content">
                <span class="slide-tag">Up to 40% Off</span>
                <h1>Mega Summer <span>Sale</span></h1>
                <p>Grab your favorite pieces at amazing prices. Limited time offer on premium collection.</p>
                <a href="<?= SITE_URL ?>/pages/shop.php?filter=sale" class="btn btn-primary">Shop Sale</a>
            </div>
            <div class="slide-decoration">
                <div class="floating-circle c1"></div>
                <div class="floating-circle c2"></div>
                <div class="floating-circle c3"></div>
            </div>
        </div>
        <div class="slide" style="background: linear-gradient(135deg, #0d0d0d 0%, #4a1942 50%, #e91e90 100%);">
            <div class="slide-content">
                <span class="slide-tag">Premium Quality</span>
                <h1>Exclusive <span>Designs</span></h1>
                <p>Handpicked styles for the modern woman. Premium quality fabrics and exquisite craftsmanship.</p>
                <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary">Explore Collection</a>
            </div>
            <div class="slide-decoration">
                <div class="floating-circle c1"></div>
                <div class="floating-circle c2"></div>
                <div class="floating-circle c3"></div>
            </div>
        </div>
    </div>
    <button class="slider-btn prev" id="sliderPrev"><i class="fas fa-chevron-left"></i></button>
    <button class="slider-btn next" id="sliderNext"><i class="fas fa-chevron-right"></i></button>
    <div class="slider-dots" id="sliderDots"></div>
</section>

<!-- Features Bar -->
<section class="features-bar">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item scroll-animate">
                <i class="fas fa-shipping-fast"></i>
                <div>
                    <h4>Free Shipping</h4>
                    <p>On orders over Rs. 5,000</p>
                </div>
            </div>
            <div class="feature-item scroll-animate">
                <i class="fas fa-undo"></i>
                <div>
                    <h4>Easy Returns</h4>
                    <p>7-day return policy</p>
                </div>
            </div>
            <div class="feature-item scroll-animate">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <h4>Secure Payment</h4>
                    <p>100% secure checkout</p>
                </div>
            </div>
            <div class="feature-item scroll-animate">
                <i class="fas fa-headset"></i>
                <div>
                    <h4>24/7 Support</h4>
                    <p>Dedicated support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section categories-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <span class="section-tag">Browse</span>
            <h2>Shop by Category</h2>
            <p>Find exactly what you're looking for</p>
        </div>
        <div class="categories-grid">
            <?php foreach (array_slice($categories, 0, 6) as $index => $cat): ?>
                <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>" class="category-card scroll-animate" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="category-icon">
                        <?php
                        $icons = ['fa-dress', 'fa-shirt', 'fa-vest-patches', 'fa-gem', 'fa-bag-shopping', 'fa-ring'];
                        $fallbackIcons = ['fa-star', 'fa-heart', 'fa-crown', 'fa-wand-magic-sparkles', 'fa-butterfly', 'fa-feather'];
                        ?>
                        <i class="fas <?= $fallbackIcons[$index] ?? 'fa-tag' ?>"></i>
                    </div>
                    <h3><?= sanitize($cat['name']) ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section products-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <span class="section-tag">Trending</span>
            <h2>Featured Products</h2>
            <p>Our most popular picks for you</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $index => $product): ?>
                <div class="product-card scroll-animate" style="animation-delay: <?= $index * 0.1 ?>s">
                    <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>" class="product-image-link">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?= SITE_URL ?>/assets/images/products/<?= $product['image'] ?>" alt="<?= sanitize($product['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="product-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <?php if ($product['discount_price']): ?>
                                <span class="badge badge-sale">
                                    -<?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>%
                                </span>
                            <?php endif; ?>
                            <?php if ($product['is_new']): ?>
                                <span class="badge badge-new">New</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                        <h3 class="product-title">
                            <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>"><?= sanitize($product['title']) ?></a>
                        </h3>
                        <p class="product-desc"><?= sanitize($product['short_description']) ?></p>
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-current"><?= formatPrice($product['discount_price']) ?></span>
                                <span class="price-old"><?= formatPrice($product['price']) ?></span>
                            <?php else: ?>
                                <span class="price-current"><?= formatPrice($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-add-cart" onclick="addToCart(<?= $product['id'] ?>)">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="section-footer scroll-animate">
            <a href="<?= SITE_URL ?>/pages/shop.php?filter=featured" class="btn btn-outline">View All Featured</a>
        </div>
    </div>
</section>

<!-- Promotional Banner -->
<section class="promo-banner scroll-animate">
    <div class="container">
        <div class="promo-content">
            <div class="promo-text">
                <span class="promo-tag">Limited Time</span>
                <h2>Summer Sale Up to <span>40% Off</span></h2>
                <p>Don't miss out on our biggest sale of the season. Shop your favorite styles at unbeatable prices.</p>
                <a href="<?= SITE_URL ?>/pages/shop.php?filter=sale" class="btn btn-primary btn-lg">Shop Now</a>
            </div>
            <div class="promo-decoration">
                <div class="promo-circle"></div>
                <div class="promo-circle small"></div>
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="section products-section">
    <div class="container">
        <div class="section-header scroll-animate">
            <span class="section-tag">Just In</span>
            <h2>New Arrivals</h2>
            <p>Be the first to grab our latest styles</p>
        </div>
        <div class="products-grid">
            <?php foreach ($newArrivals as $index => $product): ?>
                <div class="product-card scroll-animate" style="animation-delay: <?= $index * 0.1 ?>s">
                    <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>" class="product-image-link">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?= SITE_URL ?>/assets/images/products/<?= $product['image'] ?>" alt="<?= sanitize($product['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="product-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <?php if ($product['discount_price']): ?>
                                <span class="badge badge-sale">
                                    -<?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>%
                                </span>
                            <?php endif; ?>
                            <span class="badge badge-new">New</span>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                        <h3 class="product-title">
                            <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>"><?= sanitize($product['title']) ?></a>
                        </h3>
                        <p class="product-desc"><?= sanitize($product['short_description']) ?></p>
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-current"><?= formatPrice($product['discount_price']) ?></span>
                                <span class="price-old"><?= formatPrice($product['price']) ?></span>
                            <?php else: ?>
                                <span class="price-current"><?= formatPrice($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-add-cart" onclick="addToCart(<?= $product['id'] ?>)">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="section-footer scroll-animate">
            <a href="<?= SITE_URL ?>/pages/shop.php?filter=new" class="btn btn-outline">View All New Arrivals</a>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter-section scroll-animate">
    <div class="container">
        <div class="newsletter-content">
            <h2>Stay in Style</h2>
            <p>Subscribe to our newsletter for exclusive deals, new arrivals, and fashion tips.</p>
            <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
