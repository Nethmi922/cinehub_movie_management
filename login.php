<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'Log In';
$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } elseif ($user['role'] === 'counter_staff') {
            header('Location: counter/scan.php');
        } else {
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
        }
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

include 'includes/header.php';
?>
<div class="auth-shell">
    <div class="card auth-card">
        <h2>Welcome back</h2>
        <p class="auth-sub">Log in to manage your bookings and tickets.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>
        <div class="form-footer-link">New to CineHub? <a href="register.php">Create an account</a></div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
