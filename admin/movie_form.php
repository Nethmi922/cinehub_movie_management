<?php
require_once '../config/db.php';
require_once 'guard.php';

$movieId = (int)($_GET['id'] ?? 0);
$movie = ['title'=>'','duration'=>'','imdb_rate'=>'','genre'=>'','description'=>'','language'=>'','poster_path'=>''];
$isEdit = false;

if ($movieId) {
    $stmt = $pdo->prepare("SELECT * FROM movie WHERE movie_id = ?");
    $stmt->execute([$movieId]);
    $found = $stmt->fetch();
    if ($found) { $movie = $found; $isEdit = true; }
}
$pageTitle = $isEdit ? 'Edit Movie' : 'Add Movie';
$activeNav = 'movies';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_actor']) && !isset($_POST['remove_actor'])) {
    $title = trim($_POST['title'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $imdbRate = $_POST['imdb_rate'] !== '' ? (float)$_POST['imdb_rate'] : null;
    $genre = trim($_POST['genre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $language = trim($_POST['language'] ?? '');
    $poster = $movie['poster_path']; // keep existing unless a new file is uploaded

    if ($title === '' || $duration <= 0) {
        $error = 'Title and a valid duration are required.';
    } elseif (!empty($_FILES['poster_file']['name'])) {
        // Validate and handle the uploaded poster image
        $file = $_FILES['poster_file'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'There was a problem uploading the file. Please try again.';
        } elseif ($file['size'] > 3 * 1024 * 1024) {
            $error = 'Poster image must be smaller than 3MB.';
        } elseif (!isset($allowed[$file['type']])) {
            $error = 'Poster must be a JPG, PNG, or WEBP image.';
        } else {
            $ext = $allowed[$file['type']];
            $safeName = 'movie_' . uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../assets/uploads/' . $safeName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $poster = 'assets/uploads/' . $safeName;
            } else {
                $error = 'Could not save the uploaded poster. Please try again.';
            }
        }
    }

    if ($error === '') {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE movie SET title=?, duration=?, imdb_rate=?, genre=?, description=?, language=?, poster_path=? WHERE movie_id=?");
            $stmt->execute([$title, $duration, $imdbRate, $genre, $description, $language, $poster, $movieId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO movie (title, duration, imdb_rate, genre, description, language, poster_path) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$title, $duration, $imdbRate, $genre, $description, $language, $poster]);
            $movieId = $pdo->lastInsertId();
        }
        header('Location: movies.php');
        exit;
    }
}

// Cast management (only relevant once the movie exists)
if ($isEdit && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_actor'])) {
    $actorName = trim($_POST['actor_name'] ?? '');
    $charType = $_POST['character_type'] ?? 'main';
    if ($actorName !== '') {
        // Reuse existing actor by name if present, else create
        $stmt = $pdo->prepare("SELECT actor_id FROM actor WHERE name = ?");
        $stmt->execute([$actorName]);
        $actor = $stmt->fetch();
        if ($actor) {
            $actorId = $actor['actor_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO actor (name) VALUES (?)");
            $stmt->execute([$actorName]);
            $actorId = $pdo->lastInsertId();
        }
        $stmt = $pdo->prepare("INSERT IGNORE INTO movie_actor (movie_id, actor_id, character_type) VALUES (?,?,?)");
        $stmt->execute([$movieId, $actorId, $charType]);
    }
    header('Location: movie_form.php?id=' . $movieId);
    exit;
}
if ($isEdit && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_actor'])) {
    $actorId = (int)$_POST['remove_actor'];
    $pdo->prepare("DELETE FROM movie_actor WHERE movie_id = ? AND actor_id = ?")->execute([$movieId, $actorId]);
    header('Location: movie_form.php?id=' . $movieId);
    exit;
}

$cast = [];
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT a.actor_id, a.name, ma.character_type FROM movie_actor ma JOIN actor a ON a.actor_id = ma.actor_id WHERE ma.movie_id = ?");
    $stmt->execute([$movieId]);
    $cast = $stmt->fetchAll();
}

include 'admin_header.php';
?>
<h2 class="mb-24"><?= $isEdit ? 'Edit Movie' : 'Add Movie' ?></h2>

<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card mb-24">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Duration (minutes)</label>
                <input type="number" name="duration" value="<?= htmlspecialchars($movie['duration']) ?>" required>
            </div>
            <div class="form-group">
                <label>IMDB Rating</label>
                <input type="number" step="0.1" min="0" max="10" name="imdb_rate" value="<?= htmlspecialchars($movie['imdb_rate']) ?>">
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Genre</label>
                <input type="text" name="genre" value="<?= htmlspecialchars($movie['genre']) ?>" placeholder="e.g. Action, Drama">
            </div>
            <div class="form-group">
                <label>Language</label>
                <input type="text" name="language" value="<?= htmlspecialchars($movie['language']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($movie['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Poster image (JPG, PNG, or WEBP — max 3MB)</label>
            <?php if (!empty($movie['poster_path']) && file_exists(__DIR__ . '/../' . $movie['poster_path'])): ?>
                <img src="../<?= htmlspecialchars($movie['poster_path']) ?>" alt="Current poster" style="width:120px; border-radius:6px; margin-bottom:10px; display:block;">
                <p class="text-dim mb-8" style="font-size:0.8rem;">Current poster shown above. Choose a new file below to replace it.</p>
            <?php endif; ?>
            <input type="file" name="poster_file" accept="image/jpeg,image/png,image/webp">
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Movie' ?></button>
    </form>
</div>

<?php if ($isEdit): ?>
<div class="card">
    <h3 class="mb-16">Cast</h3>
    <table class="mb-16">
        <thead><tr><th>Name</th><th>Role</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($cast as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['character_type']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="remove_actor" value="<?= $c['actor_id'] ?>">
                        <button type="submit" class="btn btn-ghost btn-sm">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($cast)): ?><tr><td colspan="3" class="text-dim">No cast added yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <form method="POST" class="flex gap-12" style="align-items:flex-end;">
        <div class="form-group" style="flex:1; margin-bottom:0;">
            <label>Actor name</label>
            <input type="text" name="actor_name" placeholder="e.g. Elena Marsh" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Role</label>
            <select name="character_type">
                <option value="main">Main</option>
                <option value="sub">Supporting</option>
            </select>
        </div>
        <button type="submit" name="add_actor" class="btn btn-ghost">Add to Cast</button>
    </form>
</div>
<?php endif; ?>

<?php include 'admin_footer.php'; ?>
