<?php
require_once '../config/db.php';
require_once 'guard.php';
$pageTitle = 'Offers';
$activeNav = 'offers';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_offer'])) {
    $title = trim($_POST['title'] ?? '');
    $discount = (float)($_POST['discount_pct'] ?? 0);
    $start = $_POST['start_date'];
    $expiry = $_POST['expiry_date'];

    if ($title === '' || $discount <= 0 || $discount > 100 || !$start || !$expiry) {
        $error = 'Please fill all fields with a valid discount percentage (1-100).';
    } elseif ($expiry < $start) {
        $error = 'Expiry date must be after the start date.';
    } else {
        $pdo->prepare("INSERT INTO offer (title, discount_pct, start_date, expiry_date, is_active) VALUES (?,?,?,?,1)")
            ->execute([$title, $discount, $start, $expiry]);
        header('Location: offers.php');
        exit;
    }
}

if (isset($_GET['toggle'])) {
    $pdo->prepare("UPDATE offer SET is_active = 1 - is_active WHERE offer_id = ?")->execute([(int)$_GET['toggle']]);
    header('Location: offers.php');
    exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM offer WHERE offer_id = ?")->execute([(int)$_GET['delete']]);
    header('Location: offers.php');
    exit;
}

$offers = $pdo->query("SELECT * FROM offer ORDER BY expiry_date DESC")->fetchAll();

include 'admin_header.php';
?>
<h2 class="mb-24">Offers & Discounts</h2>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card mb-24">
    <h3 class="mb-16">Create Offer</h3>
    <form method="POST">
        <div class="form-group"><label>Title (customers enter this exact text as their code)</label><input type="text" name="title" placeholder="e.g. Weekday Special" required></div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
            <div class="form-group"><label>Discount %</label><input type="number" step="0.01" min="1" max="100" name="discount_pct" required></div>
            <div class="form-group"><label>Start date</label><input type="date" name="start_date" required></div>
            <div class="form-group"><label>Expiry date</label><input type="date" name="expiry_date" required></div>
        </div>
        <button type="submit" name="add_offer" class="btn btn-primary">Create Offer</button>
    </form>
</div>

<div class="card" style="padding:0;">
    <table>
        <thead><tr><th>Title</th><th>Discount</th><th>Valid</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($offers as $o): ?>
            <?php
                $expired = $o['expiry_date'] < date('Y-m-d');
                $statusLabel = $expired ? 'Expired' : ($o['is_active'] ? 'Active' : 'Disabled');
                $badgeClass = $expired ? 'badge-cancelled' : ($o['is_active'] ? 'badge-success' : 'badge-pending');
            ?>
            <tr>
                <td><?= htmlspecialchars($o['title']) ?></td>
                <td><?= $o['discount_pct'] ?>%</td>
                <td><?= date('M j', strtotime($o['start_date'])) ?> – <?= date('M j, Y', strtotime($o['expiry_date'])) ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
                <td class="flex gap-8">
                    <a href="offers.php?toggle=<?= $o['offer_id'] ?>" class="btn btn-ghost btn-sm"><?= $o['is_active'] ? 'Disable' : 'Enable' ?></a>
                    <a href="offers.php?delete=<?= $o['offer_id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Delete this offer?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($offers)): ?><tr><td colspan="5" class="text-dim">No offers created yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>
