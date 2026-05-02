<?php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect(SITE_URL . '/admin/login.php');

$id = intval($_GET['id'] ?? 0);
$isEdit = $id > 0;
$pageTitle = $isEdit ? 'Edit Product' : 'Add Product';
$product = null;

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) redirect(SITE_URL . '/admin/products.php');
}

$categories = getCategories($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $shortDesc = sanitize($_POST['short_description'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = $_POST['discount_price'] ? floatval($_POST['discount_price']) : null;
    $sizes = sanitize($_POST['sizes'] ?? '');
    $colors = sanitize($_POST['colors'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'active');

    // Generate slug
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $slug = trim($slug, '-');

    if (empty($title) || !$categoryId || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check slug uniqueness
        $slugCheck = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $slugCheck->execute([$slug, $id]);
        if ($slugCheck->fetch()) {
            $slug .= '-' . time();
        }

        // Handle image upload
        $imageName = $isEdit ? $product['image'] : '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed)) {
                $imageName = uniqid('prod_') . '.' . $ext;
                $uploadDir = '../assets/images/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE products SET category_id=?, title=?, slug=?, short_description=?, description=?, price=?, discount_price=?, sizes=?, colors=?, stock=?, image=?, featured=?, is_new=?, status=? WHERE id=?");
            $stmt->execute([$categoryId, $title, $slug, $shortDesc, $description, $price, $discountPrice, $sizes, $colors, $stock, $imageName, $featured, $isNew, $status, $id]);
            redirect(SITE_URL . '/admin/products.php?msg=updated');
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, title, slug, short_description, description, price, discount_price, sizes, colors, stock, image, featured, is_new, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$categoryId, $title, $slug, $shortDesc, $description, $price, $discountPrice, $sizes, $colors, $stock, $imageName, $featured, $isNew, $status]);
            redirect(SITE_URL . '/admin/products.php?msg=added');
        }
    }
}

require_once 'includes/admin-header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <div class="form-row">
            <div class="form-group">
                <label>Product Title *</label>
                <input type="text" name="title" value="<?= sanitize($product['title'] ?? $_POST['title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Short Description</label>
            <input type="text" name="short_description" value="<?= sanitize($product['short_description'] ?? '') ?>" maxlength="500">
        </div>

        <div class="form-group">
            <label>Full Description</label>
            <textarea name="description" rows="5"><?= sanitize($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Price (Rs.) *</label>
                <input type="number" name="price" step="0.01" min="0" value="<?= $product['price'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Discount Price (Rs.)</label>
                <input type="number" name="discount_price" step="0.01" min="0" value="<?= $product['discount_price'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" min="0" value="<?= $product['stock'] ?? 0 ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Sizes (comma separated)</label>
                <input type="text" name="sizes" value="<?= sanitize($product['sizes'] ?? '') ?>" placeholder="S,M,L,XL">
            </div>
            <div class="form-group">
                <label>Colors (comma separated)</label>
                <input type="text" name="colors" value="<?= sanitize($product['colors'] ?? '') ?>" placeholder="Black,White,Pink">
            </div>
        </div>

        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" accept="image/*" class="file-input">
            <?php if ($isEdit && $product['image']): ?>
                <div class="current-image">
                    <img src="<?= SITE_URL ?>/assets/images/products/<?= $product['image'] ?>" alt="" style="max-height:100px;margin-top:8px;border-radius:8px;">
                    <span>Current image</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group checkbox-group">
                <label><input type="checkbox" name="featured" <?= ($product['featured'] ?? 0) ? 'checked' : '' ?>> Featured Product</label>
                <label><input type="checkbox" name="is_new" <?= ($product['is_new'] ?? 0) ? 'checked' : '' ?>> Mark as New Arrival</label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Add' ?> Product</button>
            <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
