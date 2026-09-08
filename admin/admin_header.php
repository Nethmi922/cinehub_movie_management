<?php
// Expects $pageTitle and $activeNav to be set before including.
$currentNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — CineHub Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="dashboard.php" class="brand">Cine<span style="color:var(--red);">Hub</span></a>
        <nav>
            <a href="dashboard.php" class="<?= $currentNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="movies.php" class="<?= $currentNav === 'movies' ? 'active' : '' ?>">Movies & Cast</a>
            <a href="branches.php" class="<?= $currentNav === 'branches' ? 'active' : '' ?>">Branches & Halls</a>
            <a href="showtimes.php" class="<?= $currentNav === 'showtimes' ? 'active' : '' ?>">Showtimes</a>
            <a href="offers.php" class="<?= $currentNav === 'offers' ? 'active' : '' ?>">Offers</a>
            <a href="bookings.php" class="<?= $currentNav === 'bookings' ? 'active' : '' ?>">Bookings</a>
            <a href="users.php" class="<?= $currentNav === 'users' ? 'active' : '' ?>">Users & Staff</a>
        </nav>
        <div style="padding:20px 24px; margin-top:20px; border-top:1px solid var(--border);">
            <a href="../index.php" class="text-dim" style="font-size:0.85rem;">&larr; Back to site</a><br>
            <a href="../logout.php" class="text-dim" style="font-size:0.85rem;">Log out</a>
        </div>
    </aside>
    <main class="admin-main">
