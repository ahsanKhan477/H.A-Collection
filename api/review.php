<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review.']);
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$rating = max(1, min(5, intval($_POST['rating'] ?? 5)));
$comment = sanitize($_POST['comment'] ?? '');

if (!$productId || !$comment) {
    echo json_encode(['success' => false, 'message' => 'Please provide a rating and comment.']);
    exit;
}

// Check if already reviewed
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
$stmt->execute([$productId, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->execute([$productId, $_SESSION['user_id'], $rating, $comment]);

echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
