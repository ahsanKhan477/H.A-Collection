<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $size = sanitize($_POST['size'] ?? '');
        $color = sanitize($_POST['color'] ?? '');

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        // Check product exists
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        // Check if already in cart
        if (isLoggedIn()) {
            $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND (size = ? OR (size IS NULL AND ? = '')) AND (color = ? OR (color IS NULL AND ? = ''))");
            $stmt->execute([$_SESSION['user_id'], $productId, $size, $size, $color, $color]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM cart WHERE session_id = ? AND product_id = ? AND (size = ? OR (size IS NULL AND ? = '')) AND (color = ? OR (color IS NULL AND ? = ''))");
            $stmt->execute([session_id(), $productId, $size, $size, $color, $color]);
        }
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQty, $existing['id']]);
        } else {
            if (isLoggedIn()) {
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $productId, $quantity, $size ?: null, $color ?: null]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([session_id(), $productId, $quantity, $size ?: null, $color ?: null]);
            }
        }

        $count = getCartCount($pdo);
        echo json_encode(['success' => true, 'message' => 'Added to cart!', 'cartCount' => $count]);
        break;

    case 'update':
        $cartId = intval($_POST['cart_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));

        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $cartId]);

        echo json_encode(['success' => true, 'cartCount' => getCartCount($pdo)]);
        break;

    case 'remove':
        $cartId = intval($_POST['cart_id'] ?? 0);

        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cartId]);

        echo json_encode(['success' => true, 'cartCount' => getCartCount($pdo)]);
        break;

    case 'count':
        echo json_encode(['success' => true, 'cartCount' => getCartCount($pdo)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
