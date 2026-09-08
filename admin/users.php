<?php
require_once '../config/db.php';
require_once 'guard.php';
$pageTitle = 'Users & Staff';
$activeNav = 'users';
$error = '';
$success = '';

// Create a new staff/admin account directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_staff'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'counter_staff';

    if ($name === '' || $email === '' || strlen($password) < 6) {
        $error = 'Name, email, and a password of at least 6 characters are required.';
    } elseif (!in_array($role, ['counter_staff', 'admin'])) {
        $error = 'Invalid role.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'A user with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO user (name, email, password, role) VALUES (?,?,?,?)")
                ->execute([$name, $email, $hash, $role]);
            $success = 'Staff account created.';
        }
    }
}

// Change an existing user's role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['new_role'];
    if (in_array($newRole, ['customer','admin','counter_staff']) && $userId !== (int)$_SESSION['user_id']) {
        $pdo->prepare("UPDATE user SET role = ? WHERE user_id = ?")->execute([$newRole, $userId]);
        $success = 'Role updated.';
    } elseif ($userId === (int)$_SESSION['user_id']) {
        $error = "You can't change your own role.";
    }
}

$users = $pdo->query("SELECT user_id, name, email, phone, role, created_at FROM user ORDER BY role, name")->fetchAll();

include 'admin_header.php';
?>
<h2 class="mb-24">Users & Staff</h2>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="card mb-24">
    <h3 class="mb-16">Create Staff / Admin Account</h3>
    <form method="POST">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group"><label>Password</label><input type="password" name="password" minlength="6" required></div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="counter_staff">Counter Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <button type="submit" name="create_staff" class="btn btn-primary">Create Account</button>
    </form>
</div>

<div class="card" style="padding:0;">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Change Role</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td><span class="badge <?= $u['role']==='admin'?'badge-success':($u['role']==='counter_staff'?'badge-pending':'') ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                    <form method="POST" class="flex gap-8">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <select name="new_role">
                            <option value="customer" <?= $u['role']==='customer'?'selected':'' ?>>Customer</option>
                            <option value="counter_staff" <?= $u['role']==='counter_staff'?'selected':'' ?>>Counter Staff</option>
                            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
                        </select>
                        <button type="submit" name="change_role" class="btn btn-ghost btn-sm">Update</button>
                    </form>
                    <?php else: ?>
                        <span class="text-dim" style="font-size:0.85rem;">(you)</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>
