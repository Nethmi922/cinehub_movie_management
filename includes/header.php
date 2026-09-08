<?php
// Expects $pageTitle to optionally be set before including this file.
// Expects config/db.php to already be included (for session + $pdo).
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — CineHub' : 'CineHub' ?></title>
<link rel="stylesheet" href="<?= $basePath ?? '' ?>css/style.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-inner">
        <a href="<?= $basePath ?? '' ?>index.php" class="brand">Cine<span>Hub</span></a>
        <ul class="nav-links">
            <li><a href="<?= $basePath ?? '' ?>index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Now Showing</a></li>
            <?php if ($isLoggedIn): ?>
            <li><a href="<?= $basePath ?? '' ?>my_bookings.php" class="<?= $currentPage === 'my_bookings.php' ? 'active' : '' ?>">My Bookings</a></li>
            <li><a href="<?= $basePath ?? '' ?>loyalty.php" class="<?= $currentPage === 'loyalty.php' ? 'active' : '' ?>">Loyalty</a></li>
            <?php endif; ?>
        </ul>

        <?php
            $navSearchType = in_array($_GET['type'] ?? '', ['name','actor','date']) ? $_GET['type'] : 'name';
            $navSearchQ = $_GET['q'] ?? '';
            $navSearchLabels = ['name' => 'Movie Name', 'actor' => 'Actor', 'date' => 'Date'];
        ?>
        <form action="<?= $basePath ?? '' ?>search.php" method="GET" class="nav-search" id="navSearchForm">
            <div style="position:relative;">
                <button type="button" class="nav-search-toggle" id="navSearchToggle" onclick="toggleSearchMenu(event)">
                    <span class="hamburger-icon">&#9776;</span>
                    <span id="navSearchTypeLabel"><?= $navSearchLabels[$navSearchType] ?></span>
                </button>
                <div class="nav-search-menu" id="navSearchMenu">
                    <button type="button" class="<?= $navSearchType === 'name' ? 'active' : '' ?>" data-type="name" data-label="Movie Name" onclick="setSearchType(this)">Movie Name</button>
                    <button type="button" class="<?= $navSearchType === 'actor' ? 'active' : '' ?>" data-type="actor" data-label="Actor" onclick="setSearchType(this)">Actor</button>
                    <button type="button" class="<?= $navSearchType === 'date' ? 'active' : '' ?>" data-type="date" data-label="Date" onclick="setSearchType(this)">Date</button>
                </div>
            </div>
            <input type="hidden" name="type" id="navSearchType" value="<?= htmlspecialchars($navSearchType) ?>">
            <input type="<?= $navSearchType === 'date' ? 'date' : 'text' ?>" name="q" id="navSearchInput"
                   placeholder="<?= $navSearchType === 'actor' ? 'Search by actor...' : 'Search movies...' ?>"
                   value="<?= htmlspecialchars($navSearchQ) ?>">
            <button type="submit" class="nav-search-submit" aria-label="Search">&#128269;</button>
        </form>

        <div class="nav-actions">
            <?php if ($isLoggedIn): ?>
                <?php if ($userRole === 'admin'): ?>
                <a href="<?= $basePath ?? '' ?>admin/dashboard.php" class="btn btn-ghost btn-sm">Admin Panel</a>
                <?php elseif ($userRole === 'counter_staff'): ?>
                <a href="<?= $basePath ?? '' ?>counter/scan.php" class="btn btn-ghost btn-sm">Counter</a>
                <?php endif; ?>
                <a href="<?= $basePath ?? '' ?>logout.php" class="btn btn-ghost btn-sm">Log out</a>
            <?php else: ?>
                <a href="<?= $basePath ?? '' ?>login.php" class="btn btn-ghost btn-sm">Log in</a>
                <a href="<?= $basePath ?? '' ?>register.php" class="btn btn-primary btn-sm">Sign up</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<script>
function toggleSearchMenu(e) {
    e.stopPropagation();
    document.getElementById('navSearchMenu').classList.toggle('open');
}
function setSearchType(btn) {
    const type = btn.dataset.type;
    const label = btn.dataset.label;
    document.getElementById('navSearchType').value = type;
    document.getElementById('navSearchTypeLabel').textContent = label;

    document.querySelectorAll('#navSearchMenu button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const input = document.getElementById('navSearchInput');
    if (type === 'date') {
        input.type = 'date';
        input.placeholder = '';
    } else {
        input.type = 'text';
        input.placeholder = type === 'actor' ? 'Search by actor...' : 'Search movies...';
    }
    document.getElementById('navSearchMenu').classList.remove('open');
    input.focus();
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('navSearchMenu');
    const toggle = document.getElementById('navSearchToggle');
    if (menu && !menu.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
        menu.classList.remove('open');
    }
});
</script>
