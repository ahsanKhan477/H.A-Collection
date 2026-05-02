<?php
// H.A Collection - Configuration File
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ha_collection');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'H.A Collection');
define('SITE_URL', 'http://localhost:8000');
define('CURRENCY', 'Rs.');
define('SHIPPING_FEE', 200.00);

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function getUser($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCartCount($pdo) {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE session_id = ?");
        $stmt->execute([session_id()]);
    }
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function formatPrice($price) {
    return CURRENCY . ' ' . number_format($price, 0);
}

function generateOrderNumber() {
    return 'HA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

function flashMessage($key, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
    } elseif (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}
