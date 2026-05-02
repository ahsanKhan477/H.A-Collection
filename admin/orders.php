<?php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect(SITE_URL . '/admin/login.php');
$pageTitle = 'Manage Orders';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([sanitize($_POST['status']), intval($_POST['order_id'])]);
    redirect(SITE_URL . '/admin/orders.php?msg=updated');
}

$statusFilter = sanitize($_GET['status'] ?? '');
$where = '';
$params = [];
if ($statusFilter) {
    $where = "WHERE o.status = ?";
    $params[] = $statusFilter;
}

$orders = $pdo->prepare("SELECT o.*, u.full_name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id $where ORDER BY o.created_at DESC");
$orders->execute($params);
$orders = $orders->fetchAll();

require_once 'includes/admin-header.php';
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> Order status updated!</div>
<?php endif; ?>

<div class="admin-toolbar">
    <div class="filter-tabs">
        <a href="?status=" class="tab <?= !$statusFilter ? 'active' : '' ?>">All</a>
        <a href="?status=pending" class="tab <?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending</a>
        <a href="?status=processing" class="tab <?= $statusFilter === 'processing' ? 'active' : '' ?>">Processing</a>
        <a href="?status=shipped" class="tab <?= $statusFilter === 'shipped' ? 'active' : '' ?>">Shipped</a>
        <a href="?status=delivered" class="tab <?= $statusFilter === 'delivered' ? 'active' : '' ?>">Delivered</a>
        <a href="?status=cancelled" class="tab <?= $statusFilter === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order):
                    $itemCount = $pdo->prepare("SELECT SUM(quantity) FROM order_items WHERE order_id = ?");
                    $itemCount->execute([$order['id']]);
                    $count = $itemCount->fetchColumn();
                ?>
                    <tr>
                        <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                        <td>
                            <?= sanitize($order['user_name']) ?><br>
                            <small><?= sanitize($order['user_email']) ?></small>
                        </td>
                        <td><?= $count ?> item<?= $count > 1 ? 's' : '' ?></td>
                        <td><strong><?= formatPrice($order['total']) ?></strong></td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select status-<?= $order['status'] ?>">
                                    <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?><br><small><?= date('h:i A', strtotime($order['created_at'])) ?></small></td>
                        <td>
                            <button class="btn btn-sm btn-edit" onclick="toggleOrderDetails(<?= $order['id'] ?>)"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr class="order-details-row" id="orderDetails-<?= $order['id'] ?>" style="display:none">
                        <td colspan="7">
                            <div class="order-details-panel">
                                <div class="detail-grid">
                                    <div>
                                        <h4>Shipping Details</h4>
                                        <p><strong><?= sanitize($order['full_name']) ?></strong></p>
                                        <p><?= sanitize($order['phone']) ?></p>
                                        <p><?= sanitize($order['address']) ?></p>
                                        <p><?= sanitize($order['city']) ?> <?= $order['postal_code'] ? '- ' . sanitize($order['postal_code']) : '' ?></p>
                                        <?php if ($order['notes']): ?><p><em>Notes: <?= sanitize($order['notes']) ?></em></p><?php endif; ?>
                                    </div>
                                    <div>
                                        <h4>Order Items</h4>
                                        <?php
                                        $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                        $items->execute([$order['id']]);
                                        foreach ($items->fetchAll() as $item): ?>
                                            <p><?= sanitize($item['product_title']) ?> × <?= $item['quantity'] ?>
                                            <?= $item['size'] ? '(' . sanitize($item['size']) . ')' : '' ?>
                                            <?= $item['color'] ? '[' . sanitize($item['color']) . ']' : '' ?>
                                            — <?= formatPrice($item['price'] * $item['quantity']) ?></p>
                                        <?php endforeach; ?>
                                        <hr>
                                        <p>Subtotal: <?= formatPrice($order['subtotal']) ?></p>
                                        <p>Shipping: <?= $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : 'Free' ?></p>
                                        <p><strong>Total: <?= formatPrice($order['total']) ?></strong></p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleOrderDetails(id) {
    const row = document.getElementById('orderDetails-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
