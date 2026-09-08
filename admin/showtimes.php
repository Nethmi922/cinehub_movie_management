<?php
require_once '../config/db.php';
require_once 'guard.php';
$pageTitle = 'Showtimes';
$activeNav = 'showtimes';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_showtime'])) {
    $movieId = (int)$_POST['movie_id'];
    $hallId = (int)$_POST['hall_id'];
    $date = $_POST['date'];
    $startTime = $_POST['start_time'];
    $experienceType = trim($_POST['experience_type'] ?? '2D');

    $stmt = $pdo->prepare("SELECT duration FROM movie WHERE movie_id = ?");
    $stmt->execute([$movieId]);
    $duration = $stmt->fetchColumn();

    if (!$movieId || !$hallId || !$date || !$startTime) {
        $error = 'All fields are required.';
    } else {
        $endTime = date('H:i:s', strtotime($startTime) + ($duration * 60));

        // Check for overlapping showtime in the same hall
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM show_time
            WHERE hall_id = ? AND date = ?
            AND NOT (end_time <= ? OR start_time >= ?)
        ");
        $stmt->execute([$hallId, $date, $startTime, $endTime]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'This hall already has an overlapping showtime at that date/time.';
        } else {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO show_time (movie_id, hall_id, experience_type, date, start_time, end_time) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$movieId, $hallId, $experienceType, $date, $startTime, $endTime]);
            $showId = $pdo->lastInsertId();

            // Populate showtime_seat for every seat in that hall
            $pdo->prepare("
                INSERT INTO showtime_seat (show_id, seat_id, status)
                SELECT ?, seat_id, 'Available' FROM seat WHERE hall_id = ?
            ")->execute([$showId, $hallId]);

            $pdo->commit();
            header('Location: showtimes.php');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM show_time WHERE show_id = ?")->execute([(int)$_GET['delete']]);
    header('Location: showtimes.php');
    exit;
}

$movies = $pdo->query("SELECT movie_id, title, duration FROM movie ORDER BY title")->fetchAll();
$halls = $pdo->query("
    SELECT h.hall_id, h.name, b.name AS branch_name FROM hall h JOIN branch b ON b.branch_id = h.branch_id ORDER BY b.name, h.name
")->fetchAll();

$showtimes = $pdo->query("
    SELECT st.*, m.title, h.name AS hall_name, b.name AS branch_name,
           (SELECT COUNT(*) FROM showtime_seat ss WHERE ss.show_id = st.show_id AND ss.status != 'Available') AS seats_taken,
           (SELECT COUNT(*) FROM showtime_seat ss WHERE ss.show_id = st.show_id) AS total_seats
    FROM show_time st
    JOIN movie m ON m.movie_id = st.movie_id
    JOIN hall h ON h.hall_id = st.hall_id
    JOIN branch b ON b.branch_id = h.branch_id
    ORDER BY st.date DESC, st.start_time DESC
")->fetchAll();

include 'admin_header.php';
?>
<h2 class="mb-24">Showtimes</h2>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card mb-24">
    <h3 class="mb-16">Schedule a Showtime</h3>
    <form method="POST">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Movie</label>
                <select name="movie_id" required>
                    <?php foreach ($movies as $m): ?>
                        <option value="<?= $m['movie_id'] ?>"><?= htmlspecialchars($m['title']) ?> (<?= (int)$m['duration'] ?> min)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Hall</label>
                <select name="hall_id" required>
                    <?php foreach ($halls as $h): ?>
                        <option value="<?= $h['hall_id'] ?>"><?= htmlspecialchars($h['branch_name']) ?> — <?= htmlspecialchars($h['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
            <div class="form-group"><label>Date</label><input type="date" name="date" required></div>
            <div class="form-group"><label>Start time</label><input type="time" name="start_time" required></div>
            <div class="form-group">
                <label>Experience</label>
                <select name="experience_type"><option>2D</option><option>3D</option><option>IMAX</option><option>Recliner</option></select>
            </div>
        </div>
        <p class="text-dim mb-16" style="font-size:0.85rem;">End time is calculated automatically from the movie's duration. Seats are auto-populated for this showtime.</p>
        <button type="submit" name="add_showtime" class="btn btn-primary">Schedule Showtime</button>
    </form>
</div>

<div class="card" style="padding:0;">
    <table>
        <thead><tr><th>Movie</th><th>Branch / Hall</th><th>Date</th><th>Time</th><th>Type</th><th>Occupancy</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($showtimes as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['branch_name']) ?> — <?= htmlspecialchars($s['hall_name']) ?></td>
                <td><?= date('M j, Y', strtotime($s['date'])) ?></td>
                <td><?= date('g:i A', strtotime($s['start_time'])) ?> – <?= date('g:i A', strtotime($s['end_time'])) ?></td>
                <td><?= htmlspecialchars($s['experience_type']) ?></td>
                <td><?= (int)$s['seats_taken'] ?> / <?= (int)$s['total_seats'] ?></td>
                <td><a href="showtimes.php?delete=<?= $s['show_id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Delete this showtime?');">Delete</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($showtimes)): ?><tr><td colspan="7" class="text-dim">No showtimes scheduled yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>
