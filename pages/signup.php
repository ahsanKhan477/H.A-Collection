<?php
require_once '../includes/config.php';
$pageTitle = 'Sign Up';

if (isLoggedIn()) {
    redirect(SITE_URL);
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullName, $email, $phone, $hashedPassword]);

            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['is_admin'] = 0;

            // Merge session cart
            $sessionId = session_id();
            $stmt = $pdo->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?");
            $stmt->execute([$userId, $sessionId]);

            redirect(SITE_URL);
        }
    }
}

require_once '../includes/header.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-card scroll-animate">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join H.A Collection today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="full_name"><i class="fas fa-user"></i> Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required placeholder="Your full name">
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="+92 300 1234567">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password *</label>
                        <input type="password" id="password" name="password" required placeholder="Min 6 characters">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?= SITE_URL ?>/pages/login.php">Login</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
