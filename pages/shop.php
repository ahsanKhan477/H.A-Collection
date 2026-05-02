<?php
require_once '../includes/config.php';
$pageTitle = 'Shop';

// Get filter parameters
$category = $_GET['category'] ?? '';
$filter = $_GET['filter'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$size = $_GET['size'] ?? '';
$color = $_GET['color'] ?? '';
$search = $_GET['q'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$where = ["p.status = 'active'"];
$params = [];

if ($category) {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

if ($filter === 'new') {
    $where[] = "p.is_new = 1";
    $pageTitle = 'New Arrivals';
} elseif ($filter === 'sale') {
    $where[] = "p.discount_price IS NOT NULL";
    $pageTitle = 'Sale';
} elseif ($filter === 'featured') {
    $where[] = "p.featured = 1";
    $pageTitle = 'Featured Products';
}

if ($minPrice !== '') {
    $where[] = "COALESCE(p.discount_price, p.price) >= ?";
    $params[] = floatval($minPrice);
}
if ($maxPrice !== '') {
    $where[] = "COALESCE(p.discount_price, p.price) <= ?";
    $params[] = floatval($maxPrice);
}

if ($size) {
    $where[] = "FIND_IN_SET(?, p.sizes) > 0";
    $params[] = $size;
}

if ($color) {
    $where[] = "FIND_IN_SET(?, p.colors) > 0";
    $params[] = $color;
}

if ($search) {
    $where[] = "(p.title LIKE ? OR p.short_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $pageTitle = 'Search: ' . $search;
}

$whereClause = implode(' AND ', $where);

// Sort
$orderBy = match($sort) {
    'price_low' => 'COALESCE(p.discount_price, p.price) ASC',
    'price_high' => 'COALESCE(p.discount_price, p.price) DESC',
    'name' => 'p.title ASC',
    default => 'p.created_at DESC'
};

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereClause");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereClause ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for filter
$categories = getCategories($pdo);

// Get unique colors & sizes for filters
$colorsStmt = $pdo->query("SELECT DISTINCT colors FROM products WHERE colors IS NOT NULL AND colors != ''");
$allColors = [];
while ($row = $colorsStmt->fetch()) {
    foreach (explode(',', $row['colors']) as $c) {
        $c = trim($c);
        if ($c && !in_array($c, $allColors)) $allColors[] = $c;
    }
}

require_once '../includes/header.php';
?>

<section class="shop-section">
    <div class="container">
        <div class="shop-header scroll-animate">
            <h1><?= sanitize($pageTitle) ?></h1>
            <p><?= $totalProducts ?> product<?= $totalProducts !== 1 ? 's' : '' ?> found</p>
        </div>

        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar" id="shopSidebar">
                <div class="sidebar-header">
                    <h3><i class="fas fa-filter"></i> Filters</h3>
                    <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
                </div>

                <form method="GET" action="" id="filterForm">
                    <?php if ($category): ?><input type="hidden" name="category" value="<?= sanitize($category) ?>"><?php endif; ?>
                    <?php if ($filter): ?><input type="hidden" name="filter" value="<?= sanitize($filter) ?>"><?php endif; ?>
                    <?php if ($search): ?><input type="hidden" name="q" value="<?= sanitize($search) ?>"><?php endif; ?>

                    <!-- Categories -->
                    <div class="filter-group">
                        <h4>Categories</h4>
                        <ul class="filter-list">
                            <li><a href="<?= SITE_URL ?>/pages/shop.php" class="<?= !$category ? 'active' : '' ?>">All Products</a></li>
                            <?php foreach ($categories as $cat): ?>
                                <li><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>" class="<?= $category === $cat['slug'] ? 'active' : '' ?>"><?= sanitize($cat['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <h4>Price Range (Rs.)</h4>
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="Min" value="<?= sanitize($minPrice) ?>" class="price-input">
                            <span>—</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?= sanitize($maxPrice) ?>" class="price-input">
                        </div>
                    </div>

                    <!-- Size Filter -->
                    <div class="filter-group">
                        <h4>Size</h4>
                        <div class="size-options">
                            <?php foreach (['S', 'M', 'L', 'XL'] as $s): ?>
                                <label class="size-option <?= $size === $s ? 'active' : '' ?>">
                                    <input type="radio" name="size" value="<?= $s ?>" <?= $size === $s ? 'checked' : '' ?>>
                                    <span><?= $s ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Color Filter -->
                    <div class="filter-group">
                        <h4>Color</h4>
                        <div class="color-options">
                            <?php foreach ($allColors as $c): ?>
                                <label class="color-option <?= $color === $c ? 'active' : '' ?>" title="<?= $c ?>">
                                    <input type="radio" name="color" value="<?= sanitize($c) ?>" <?= $color === $c ? 'checked' : '' ?>>
                                    <span><?= sanitize($c) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Apply Filters</button>
                    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline btn-full" style="margin-top:8px;">Clear All</a>
                </form>
            </aside>

            <!-- Products Grid -->
            <div class="shop-main">
                <div class="shop-toolbar">
                    <button class="filter-toggle-btn" id="filterToggleBtn">
                        <i class="fas fa-filter"></i> Filters
                    </button>
                    <div class="sort-wrapper">
                        <label>Sort by:</label>
                        <select id="sortSelect" onchange="updateSort(this.value)">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>No Products Found</h3>
                        <p>Try adjusting your filters or search terms.</p>
                        <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $index => $product): ?>
                            <div class="product-card scroll-animate" style="animation-delay: <?= ($index % 4) * 0.1 ?>s">
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

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $queryParams = $_GET;
                            if ($page > 1):
                                $queryParams['page'] = $page - 1;
                            ?>
                                <a href="?<?= http_build_query($queryParams) ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++):
                                $queryParams['page'] = $i;
                            ?>
                                <a href="?<?= http_build_query($queryParams) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages):
                                $queryParams['page'] = $page + 1;
                            ?>
                                <a href="?<?= http_build_query($queryParams) ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function updateSort(value) {
    const url = new URL(window.location);
    url.searchParams.set('sort', value);
    url.searchParams.delete('page');
    window.location = url;
}
</script>

<?php require_once '../includes/footer.php'; ?>
