<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'Sign Up';
$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO user (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $hash, $phone]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['name'] = $name;
            $_SESSION['role'] = 'customer';
            header('Location: index.php');
            exit;
        }
    }
}

include 'includes/header.php';
?>
<div class="auth-shell">
    <div class="card auth-card">
        <h2>Create your account</h2>
        <p class="auth-sub">Join CineHub to book seats, earn loyalty points, and leave reviews.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone number</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </form>
        <div class="form-footer-link">Already have an account? <a href="login.php">Log in</a></div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
