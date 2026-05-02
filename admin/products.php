<?php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect(SITE_URL . '/admin/login.php');
$pageTitle = 'Manage Products';

// Delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    redirect(SITE_URL . '/admin/products.php?msg=deleted');
}

$search = sanitize($_GET['q'] ?? '');
$where = '';
$params = [];
if ($search) {
    $where = "WHERE p.title LIKE ?";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once 'includes/admin-header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i>
        <?php echo match($_GET['msg']) {
            'added' => 'Product added successfully!',
            'updated' => 'Product updated successfully!',
            'deleted' => 'Product deleted successfully!',
            default => 'Action completed.'
        }; ?>
    </div>
<?php endif; ?>

<div class="admin-toolbar">
    <form class="admin-search" method="GET">
        <input type="text" name="q" placeholder="Search products..." value="<?= sanitize($search) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <a href="<?= SITE_URL ?>/admin/product-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <div class="table-img">
                                <?php if ($p['image']): ?>
                                    <img src="<?= SITE_URL ?>/assets/images/products/<?= $p['image'] ?>" alt="">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><strong><?= sanitize($p['title']) ?></strong></td>
                        <td><?= sanitize($p['category_name']) ?></td>
                        <td>
                            <?php if ($p['discount_price']): ?>
                                <span class="price-current"><?= formatPrice($p['discount_price']) ?></span>
                                <span class="price-old"><?= formatPrice($p['price']) ?></span>
                            <?php else: ?>
                                <?= formatPrice($p['price']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $p['stock'] ?></td>
                        <td><span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td class="actions-cell">
                            <a href="<?= SITE_URL ?>/admin/product-form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= SITE_URL ?>/admin/products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-delete" title="Delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr><td colspan="7" class="text-center">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
