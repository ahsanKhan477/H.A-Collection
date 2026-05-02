<?php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect(SITE_URL . '/admin/login.php');
$pageTitle = 'Manage Users';

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
        $stmt->execute([$id]);
    }
    redirect(SITE_URL . '/admin/users.php?msg=deleted');
}

$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u ORDER BY u.created_at DESC")->fetchAll();

require_once 'includes/admin-header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> User deleted.</div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= sanitize($u['full_name']) ?></strong></td>
                        <td><?= sanitize($u['email']) ?></td>
                        <td><?= sanitize($u['phone'] ?? '—') ?></td>
                        <td><?= $u['order_count'] ?></td>
                        <td><span class="status-badge <?= $u['is_admin'] ? 'status-admin' : 'status-user' ?>"><?= $u['is_admin'] ? 'Admin' : 'Customer' ?></span></td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td class="actions-cell">
                            <?php if (!$u['is_admin']): ?>
                                <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
