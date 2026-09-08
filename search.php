<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'Search Movies';

$type = $_GET['type'] ?? 'name';
$q = trim($_GET['q'] ?? '');
if (!in_array($type, ['name', 'actor', 'date'])) {
    $type = 'name';
}

$results = [];
$showtimesByMovie = [];
$hasSearched = ($q !== '');

if ($hasSearched) {
    if ($type === 'name') {
        $stmt = $pdo->prepare("SELECT DISTINCT m.* FROM movie m WHERE m.title LIKE ? ORDER BY m.title");
        $stmt->execute(['%' . $q . '%']);
        $results = $stmt->fetchAll();

    } elseif ($type === 'actor') {
        $stmt = $pdo->prepare("
            SELECT DISTINCT m.* FROM movie m
            JOIN movie_actor ma ON ma.movie_id = m.movie_id
            JOIN actor a ON a.actor_id = ma.actor_id
            WHERE a.name LIKE ?
            ORDER BY m.title
        ");
        $stmt->execute(['%' . $q . '%']);
        $results = $stmt->fetchAll();

    } elseif ($type === 'date') {
        $stmt = $pdo->prepare("
            SELECT DISTINCT m.* FROM movie m
            JOIN show_time st ON st.movie_id = m.movie_id
            WHERE st.date = ?
            ORDER BY m.title
        ");
        $stmt->execute([$q]);
        $results = $stmt->fetchAll();

        // Also pull the specific showtimes on that date, per movie, for display
        if (!empty($results)) {
            $stmt = $pdo->prepare("
                SELECT st.movie_id, st.start_time, st.experience_type, h.name AS hall_name, b.name AS branch_name
                FROM show_time st
                JOIN hall h ON h.hall_id = st.hall_id
                JOIN branch b ON b.branch_id = h.branch_id
                WHERE st.date = ?
                ORDER BY st.start_time
            ");
            $stmt->execute([$q]);
            foreach ($stmt->fetchAll() as $row) {
                $showtimesByMovie[$row['movie_id']][] = $row;
            }
        }
    }
}

$typeLabels = ['name' => 'movie name', 'actor' => 'actor', 'date' => 'date'];

include 'includes/header.php';
?>
<section class="section" style="padding-top:48px;">
    <div class="container">
        <h2 class="mb-24">Search Movies</h2>

        <?php if (!$hasSearched): ?>
            <p>Use the search bar at the top &mdash; pick <strong>Movie Name</strong>, <strong>Actor</strong>, or <strong>Date</strong> from the menu, then type your search.</p>
        <?php else: ?>
            <p class="mb-24 text-dim">
                <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> found by <?= $typeLabels[$type] ?>
                &mdash; "<?= htmlspecialchars($type === 'date' ? date('M j, Y', strtotime($q)) : $q) ?>"
            </p>

            <div class="movie-grid">
                <?php foreach ($results as $movie): ?>
                <a href="movie_details.php?id=<?= $movie['movie_id'] ?>" class="movie-card">
                    <div class="movie-poster" <?php if (!empty($movie['poster_path']) && file_exists(__DIR__ . '/' . $movie['poster_path'])): ?>style="background-image:url('<?= htmlspecialchars($movie['poster_path']) ?>'); background-size:cover; background-position:center;"<?php endif; ?>>
                        <?php if (empty($movie['poster_path']) || !file_exists(__DIR__ . '/' . $movie['poster_path'])): ?>
                            <?= htmlspecialchars($movie['title']) ?>
                        <?php endif; ?>
                        <span class="movie-rate">&#9733; <?= htmlspecialchars($movie['imdb_rate']) ?></span>
                    </div>
                    <div class="movie-body">
                        <div class="movie-title"><?= htmlspecialchars($movie['title']) ?></div>
                        <div class="movie-genre"><?= htmlspecialchars($movie['genre']) ?></div>
                        <?php if ($type === 'date' && !empty($showtimesByMovie[$movie['movie_id']])): ?>
                            <div class="mt-8" style="font-size:0.78rem; color:var(--text-dim);">
                                <?php foreach ($showtimesByMovie[$movie['movie_id']] as $s): ?>
                                    <div><?= date('g:i A', strtotime($s['start_time'])) ?> · <?= htmlspecialchars($s['branch_name']) ?> (<?= htmlspecialchars($s['experience_type']) ?>)</div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($results)): ?>
                <p>No movies matched your search. Try a different <?= $typeLabels[$type] ?>.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
