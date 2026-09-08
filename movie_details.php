<?php
require_once 'config/db.php';
$basePath = '';

$movieId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM movie WHERE movie_id = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: index.php');
    exit;
}
$pageTitle = $movie['title'];

// Cast
$stmt = $pdo->prepare("
    SELECT a.name, ma.character_type FROM movie_actor ma
    JOIN actor a ON a.actor_id = ma.actor_id
    WHERE ma.movie_id = ?
    ORDER BY ma.character_type ASC
");
$stmt->execute([$movieId]);
$cast = $stmt->fetchAll();

// Showtimes grouped by branch + date
$stmt = $pdo->prepare("
    SELECT st.show_id, st.date, st.start_time, st.experience_type,
           h.name AS hall_name, b.name AS branch_name, b.branch_id
    FROM show_time st
    JOIN hall h ON h.hall_id = st.hall_id
    JOIN branch b ON b.branch_id = h.branch_id
    WHERE st.movie_id = ? AND st.date >= CURDATE()
    ORDER BY b.name, st.date, st.start_time
");
$stmt->execute([$movieId]);
$showtimes = $stmt->fetchAll();

$grouped = [];
foreach ($showtimes as $s) {
    $grouped[$s['branch_name']][] = $s;
}

// Reviews
$stmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.name
    FROM review r JOIN user u ON u.user_id = r.user_id
    WHERE r.movie_id = ? ORDER BY r.created_at DESC LIMIT 10
");
$stmt->execute([$movieId]);
$reviews = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT AVG(rating) avg_rating, COUNT(*) total FROM review WHERE movie_id = ?");
$stmt->execute([$movieId]);
$ratingSummary = $stmt->fetch();

$reviewError = '';
$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $reviewError = 'Please log in to leave a review.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) {
            $reviewError = 'Please select a rating between 1 and 5.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO review (user_id, movie_id, rating, comment)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
            ");
            $stmt->execute([$_SESSION['user_id'], $movieId, $rating, $comment]);
            $reviewSuccess = 'Thanks for your review!';
            // refresh reviews + summary
            $stmt = $pdo->prepare("SELECT r.rating, r.comment, r.created_at, u.name FROM review r JOIN user u ON u.user_id = r.user_id WHERE r.movie_id = ? ORDER BY r.created_at DESC LIMIT 10");
            $stmt->execute([$movieId]);
            $reviews = $stmt->fetchAll();
            $stmt = $pdo->prepare("SELECT AVG(rating) avg_rating, COUNT(*) total FROM review WHERE movie_id = ?");
            $stmt->execute([$movieId]);
            $ratingSummary = $stmt->fetch();
        }
    }
}

include 'includes/header.php';
?>

<section class="section" style="padding-top:48px;">
    <div class="container">
        <div style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">
            <div class="movie-poster" style="aspect-ratio:2/3; border-radius:6px; <?php if (!empty($movie['poster_path']) && file_exists(__DIR__ . '/' . $movie['poster_path'])): ?>background-image:url('<?= htmlspecialchars($movie['poster_path']) ?>'); background-size:cover; background-position:center;<?php endif; ?>">
                <?php if (empty($movie['poster_path']) || !file_exists(__DIR__ . '/' . $movie['poster_path'])): ?>
                    <?= htmlspecialchars($movie['title']) ?>
                <?php endif; ?>
            </div>
            <div>
                <h1><?= htmlspecialchars($movie['title']) ?></h1>
                <div class="hero-meta">
                    <span>&#9733; <?= htmlspecialchars($movie['imdb_rate']) ?> IMDB</span>
                    <span><?= htmlspecialchars($movie['genre']) ?></span>
                    <span><?= (int)$movie['duration'] ?> min</span>
                    <span><?= htmlspecialchars($movie['language']) ?></span>
                    <?php if ($ratingSummary['total'] > 0): ?>
                    <span class="text-gold">&#9733; <?= round($ratingSummary['avg_rating'], 1) ?> (<?= $ratingSummary['total'] ?> reviews)</span>
                    <?php endif; ?>
                </div>
                <p class="mt-16"><?= htmlspecialchars($movie['description']) ?></p>

                <?php if ($cast): ?>
                <h3 class="mt-32 mb-16">Cast</h3>
                <p><?php
                    $names = array_map(fn($c) => htmlspecialchars($c['name']) . ($c['character_type'] === 'main' ? '' : ' <span class="text-dim">(supporting)</span>'), $cast);
                    echo implode(', ', $names);
                ?></p>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="mt-32 mb-24">Showtimes</h2>
        <?php if (empty($grouped)): ?>
            <p>No upcoming showtimes scheduled for this movie yet.</p>
        <?php endif; ?>
        <?php foreach ($grouped as $branchName => $shows): ?>
            <div class="card mb-16">
                <h3 class="mb-16"><?= htmlspecialchars($branchName) ?></h3>
                <div class="flex gap-12" style="flex-wrap:wrap;">
                    <?php foreach ($shows as $s): ?>
                        <a href="seat_selection.php?show_id=<?= $s['show_id'] ?>" class="btn btn-ghost btn-sm">
                            <?= date('D, M j', strtotime($s['date'])) ?> · <?= date('g:i A', strtotime($s['start_time'])) ?> · <?= htmlspecialchars($s['experience_type']) ?> · <?= htmlspecialchars($s['hall_name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <h2 class="mt-32 mb-24">Reviews</h2>

        <?php if ($reviewError): ?><div class="alert alert-error"><?= htmlspecialchars($reviewError) ?></div><?php endif; ?>
        <?php if ($reviewSuccess): ?><div class="alert alert-success"><?= htmlspecialchars($reviewSuccess) ?></div><?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="card mb-24">
            <form method="POST">
                <div class="form-group">
                    <label for="rating">Your rating</label>
                    <select name="rating" id="rating" required>
                        <option value="">Select a rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Terrible</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="comment">Your review</label>
                    <textarea name="comment" id="comment" rows="3" placeholder="What did you think of the movie?"></textarea>
                </div>
                <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
        <?php else: ?>
            <p class="mb-24"><a href="login.php" class="text-gold">Log in</a> to leave a review.</p>
        <?php endif; ?>

        <?php foreach ($reviews as $r): ?>
            <div class="card mb-16">
                <div class="flex-between">
                    <strong><?= htmlspecialchars($r['name']) ?></strong>
                    <span class="text-gold">&#9733; <?= $r['rating'] ?>/5</span>
                </div>
                <?php if ($r['comment']): ?><p class="mt-8"><?= htmlspecialchars($r['comment']) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?><p>No reviews yet. Be the first to share your thoughts.</p><?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
