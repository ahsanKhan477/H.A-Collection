<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

$query = sanitize($_GET['q'] ?? '');
$category = sanitize($_GET['category'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$where = ["p.status = 'active'", "(p.title LIKE ? OR p.short_description LIKE ?)"];
$params = ["%$query%", "%$query%"];

if ($category) {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

$whereClause = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT p.title, p.slug, p.price, p.discount_price, p.image, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereClause ORDER BY p.title ASC LIMIT 8");
$stmt->execute($params);
$results = $stmt->fetchAll();

$formatted = [];
foreach ($results as $r) {
    $formatted[] = [
        'title' => $r['title'],
        'slug' => $r['slug'],
        'price' => formatPrice($r['discount_price'] ?? $r['price']),
        'image' => $r['image'] ? SITE_URL . '/assets/images/products/' . $r['image'] : '',
        'category' => $r['category_name'],
        'url' => SITE_URL . '/pages/product.php?slug=' . $r['slug']
    ];
}

echo json_encode(['results' => $formatted]);
