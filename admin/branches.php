<?php
require_once '../config/db.php';
require_once 'guard.php';
$pageTitle = 'Branches & Halls';
$activeNav = 'branches';
$error = '';

// Add branch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_branch'])) {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    if ($name === '' || $location === '') {
        $error = 'Branch name and location are required.';
    } else {
        $pdo->prepare("INSERT INTO branch (name, email, location, contact_number) VALUES (?,?,?,?)")
            ->execute([$name, $email, $location, $contact]);
        header('Location: branches.php');
        exit;
    }
}

// Add hall + auto-generate seats
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hall'])) {
    $branchId = (int)$_POST['branch_id'];
    $hallName = trim($_POST['hall_name'] ?? '');
    $hallLocation = trim($_POST['hall_location'] ?? '');
    $type = trim($_POST['type'] ?? '2D');
    $rows = max(1, (int)($_POST['rows'] ?? 5));
    $seatsPerRow = max(1, (int)($_POST['seats_per_row'] ?? 10));
    $vipRows = max(0, (int)($_POST['vip_rows'] ?? 1)); // last N rows are VIP

    if ($hallName === '' || $branchId <= 0) {
        $error = 'Please select a branch and enter a hall name.';
    } else {
        $capacity = $rows * $seatsPerRow;
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO hall (branch_id, name, location, capacity, type) VALUES (?,?,?,?,?)")
            ->execute([$branchId, $hallName, $hallLocation, $capacity, $type]);
        $hallId = $pdo->lastInsertId();

        $rowLetters = range('A', 'Z');
        $stmt = $pdo->prepare("INSERT INTO seat (hall_id, seat_number, row_label, category) VALUES (?,?,?,?)");
        for ($r = 0; $r < $rows; $r++) {
            $rowLabel = $rowLetters[$r];
            $isVip = $r >= ($rows - $vipRows);
            for ($n = 1; $n <= $seatsPerRow; $n++) {
                $stmt->execute([$hallId, $rowLabel . $n, $rowLabel, $isVip ? 'VIP' : 'Standard']);
            }
        }
        $pdo->commit();
        header('Location: branches.php');
        exit;
    }
}

if (isset($_GET['delete_hall'])) {
    $pdo->prepare("DELETE FROM hall WHERE hall_id = ?")->execute([(int)$_GET['delete_hall']]);
    header('Location: branches.php');
    exit;
}
if (isset($_GET['delete_branch'])) {
    $pdo->prepare("DELETE FROM branch WHERE branch_id = ?")->execute([(int)$_GET['delete_branch']]);
    header('Location: branches.php');
    exit;
}

$branches = $pdo->query("SELECT * FROM branch ORDER BY name")->fetchAll();
$halls = $pdo->query("
    SELECT h.*, b.name AS branch_name, (SELECT COUNT(*) FROM seat s WHERE s.hall_id = h.hall_id) AS seat_count
    FROM hall h JOIN branch b ON b.branch_id = h.branch_id ORDER BY b.name, h.name
")->fetchAll();

include 'admin_header.php';
?>
<h2 class="mb-24">Branches & Halls</h2>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;" class="mb-24">
    <div class="card">
        <h3 class="mb-16">Add Branch</h3>
        <form method="POST">
            <div class="form-group"><label>Branch name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Location</label><input type="text" name="location" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email"></div>
            <div class="form-group"><label>Contact number</label><input type="text" name="contact_number"></div>
            <button type="submit" name="add_branch" class="btn btn-primary">Add Branch</button>
        </form>
    </div>
    <div class="card">
        <h3 class="mb-16">Add Hall (auto-generates seats)</h3>
        <form method="POST">
            <div class="form-group">
                <label>Branch</label>
                <select name="branch_id" required>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['branch_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Hall name</label><input type="text" name="hall_name" placeholder="e.g. Hall 3" required></div>
            <div class="form-group"><label>Location (floor)</label><input type="text" name="hall_location" placeholder="e.g. 2nd Floor"></div>
            <div class="form-group">
                <label>Experience type</label>
                <select name="type"><option>2D</option><option>3D</option><option>IMAX</option><option>Recliner</option></select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div class="form-group"><label>Rows</label><input type="number" name="rows" value="6" min="1"></div>
                <div class="form-group"><label>Seats/row</label><input type="number" name="seats_per_row" value="10" min="1"></div>
                <div class="form-group"><label>VIP rows (last N)</label><input type="number" name="vip_rows" value="1" min="0"></div>
            </div>
            <button type="submit" name="add_hall" class="btn btn-primary">Add Hall</button>
        </form>
    </div>
</div>

<div class="card mb-24">
    <h3 class="mb-16">Branches</h3>
    <table>
        <thead><tr><th>Name</th><th>Location</th><th>Contact</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($branches as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['name']) ?></td>
                <td><?= htmlspecialchars($b['location']) ?></td>
                <td><?= htmlspecialchars($b['contact_number']) ?></td>
                <td><a href="branches.php?delete_branch=<?= $b['branch_id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Delete this branch and all its halls?');">Delete</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3 class="mb-16">Halls</h3>
    <table>
        <thead><tr><th>Hall</th><th>Branch</th><th>Type</th><th>Capacity</th><th>Seats Configured</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($halls as $h): ?>
            <tr>
                <td><?= htmlspecialchars($h['name']) ?></td>
                <td><?= htmlspecialchars($h['branch_name']) ?></td>
                <td><?= htmlspecialchars($h['type']) ?></td>
                <td><?= (int)$h['capacity'] ?></td>
                <td><?= (int)$h['seat_count'] ?></td>
                <td><a href="branches.php?delete_hall=<?= $h['hall_id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Delete this hall and its seats?');">Delete</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($halls)): ?><tr><td colspan="6" class="text-dim">No halls yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>
