<?php
require_once '../config/db.php';
require_once 'guard.php';
$pageTitle = 'Movies';
$activeNav = 'movies';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM movie WHERE movie_id = ?")->execute([$id]);
    header('Location: movies.php');
    exit;
}

$movies = $pdo->query("SELECT * FROM movie ORDER BY created_at DESC")->fetchAll();

include 'admin_header.php';
?>
<div class="flex-between mb-24">
    <h2>Movies</h2>
    <a href="movie_form.php" class="btn btn-primary">+ Add Movie</a>
</div>

<div class="card" style="padding:0;">
    <table>
        <thead><tr><th></th><th>Title</th><th>Genre</th><th>Duration</th><th>IMDB</th><th>Language</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($movies as $m): ?>
            <tr>
                <td>
                    <?php if (!empty($m['poster_path']) && file_exists(__DIR__ . '/../' . $m['poster_path'])): ?>
                        <img src="../<?= htmlspecialchars($m['poster_path']) ?>" alt="" style="width:40px; height:56px; object-fit:cover; border-radius:4px;">
                    <?php else: ?>
                        <div style="width:40px; height:56px; background:var(--bg); border-radius:4px; border:1px solid var(--border);"></div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($m['title']) ?></td>
                <td><?= htmlspecialchars($m['genre']) ?></td>
                <td><?= (int)$m['duration'] ?> min</td>
                <td><?= htmlspecialchars($m['imdb_rate']) ?></td>
                <td><?= htmlspecialchars($m['language']) ?></td>
                <td class="flex gap-8">
                    <a href="movie_form.php?id=<?= $m['movie_id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                    <a href="movies.php?delete=<?= $m['movie_id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Delete this movie? This also removes its showtimes.');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($movies)): ?><tr><td colspan="7" class="text-dim">No movies yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>
