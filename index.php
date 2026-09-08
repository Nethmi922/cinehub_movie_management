<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'Now Showing';

// Fetch movies that have at least one upcoming showtime today or later
$stmt = $pdo->query("
    SELECT m.*, MIN(st.date) AS next_date,
           GROUP_CONCAT(DISTINCT st.experience_type) AS experiences
    FROM movie m
    LEFT JOIN show_time st ON st.movie_id = m.movie_id AND st.date >= CURDATE()
    GROUP BY m.movie_id
    ORDER BY m.created_at DESC
");
$movies = $stmt->fetchAll();

// Featured movie = highest rated for the hero
$stmt = $pdo->query("SELECT * FROM movie ORDER BY imdb_rate DESC LIMIT 1");
$featured = $stmt->fetch();

include 'includes/header.php';
?>

<?php if ($featured): ?>
<section class="hero" <?php if (!empty($featured['poster_path']) && file_exists(__DIR__ . '/' . $featured['poster_path'])): ?>style="background-image: linear-gradient(0deg, rgba(13,13,18,1) 5%, rgba(13,13,18,0.55) 55%, rgba(13,13,18,0.15) 100%), url('<?= htmlspecialchars($featured['poster_path']) ?>'); background-size: cover; background-position: center 20%;"<?php endif; ?>>
    <div class="hero-content">
        <div class="hero-eyebrow">FEATURED THIS WEEK</div>
        <h1><?= htmlspecialchars($featured['title']) ?></h1>
        <div class="hero-meta">
            <span>&#9733; <?= htmlspecialchars($featured['imdb_rate']) ?> IMDB</span>
            <span><?= htmlspecialchars($featured['genre']) ?></span>
            <span><?= (int)$featured['duration'] ?> min</span>
            <span><?= htmlspecialchars($featured['language']) ?></span>
        </div>
        <p><?= htmlspecialchars($featured['description']) ?></p>
        <div class="hero-actions mt-24">
            <a href="movie_details.php?id=<?= $featured['movie_id'] ?>" class="btn btn-primary">Book Tickets</a>
            <a href="movie_details.php?id=<?= $featured['movie_id'] ?>" class="btn btn-ghost">More Details</a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Now Showing</h2>
        </div>
        <div class="movie-grid">
            <?php foreach ($movies as $movie): ?>
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
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($movies)): ?>
                <p>No movies available right now. Check back soon.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
