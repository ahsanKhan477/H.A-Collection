<?php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect(SITE_URL . '/admin/login.php');
$pageTitle = 'Manage Categories';

$error = '';
$editCat = null;

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() > 0) {
        $error = 'Cannot delete: category has products.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        redirect(SITE_URL . '/admin/categories.php?msg=deleted');
    }
}

// Edit mode
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editCat = $stmt->fetch();
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catId = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $sortOrder = intval($_POST['sort_order'] ?? 0);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $slug = trim($slug, '-');

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        // Check slug unique
        $slugCheck = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $slugCheck->execute([$slug, $catId]);
        if ($slugCheck->fetch()) $slug .= '-' . time();

        if ($catId > 0) {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, sort_order=? WHERE id=?");
            $stmt->execute([$name, $slug, $description, $sortOrder, $catId]);
            redirect(SITE_URL . '/admin/categories.php?msg=updated');
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, sort_order) VALUES (?,?,?,?)");
            $stmt->execute([$name, $slug, $description, $sortOrder]);
            redirect(SITE_URL . '/admin/categories.php?msg=added');
        }
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.sort_order ASC")->fetchAll();

require_once 'includes/admin-header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i>
        <?= match($_GET['msg']) { 'added'=>'Category added!', 'updated'=>'Category updated!', 'deleted'=>'Category deleted!', default=>'Done.' } ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="admin-grid-half">
    <!-- Form -->
    <div class="admin-card">
        <div class="card-header"><h2><?= $editCat ? 'Edit' : 'Add' ?> Category</h2></div>
        <form method="POST" class="admin-form">
            <input type="hidden" name="id" value="<?= $editCat['id'] ?? 0 ?>">
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" value="<?= sanitize($editCat['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= sanitize($editCat['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" value="<?= $editCat['sort_order'] ?? 0 ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editCat ? 'Update' : 'Add' ?></button>
                <?php if ($editCat): ?>
                    <a href="<?= SITE_URL ?>/admin/categories.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- List -->
    <div class="admin-card">
        <div class="card-header"><h2>All Categories</h2></div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Name</th><th>Products</th><th>Order</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong><?= sanitize($cat['name']) ?></strong></td>
                            <td><?= $cat['product_count'] ?></td>
                            <td><?= $cat['sort_order'] ?></td>
                            <td class="actions-cell">
                                <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this category?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
