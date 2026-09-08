<?php
require_once '../config/db.php';
require_once 'guard.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM booking WHERE status='Confirmed'")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE status='Confirmed'")->fetchColumn();
$totalMovies = $pdo->query("SELECT COUNT(*) FROM movie")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM user WHERE role='customer'")->fetchColumn();
$upcomingShows = $pdo->query("SELECT COUNT(*) FROM show_time WHERE date >= CURDATE()")->fetchColumn();

$topMovies = $pdo->query("
    SELECT m.title, COUNT(ss.showtime_seat_id) AS seats_sold
    FROM showtime_seat ss
    JOIN booking b ON b.booking_id = ss.booking_id AND b.status = 'Confirmed'
    JOIN show_time st ON st.show_id = ss.show_id
    JOIN movie m ON m.movie_id = st.movie_id
    GROUP BY m.movie_id
    ORDER BY seats_sold DESC
    LIMIT 5
")->fetchAll();

$recentBookings = $pdo->query("
    SELECT b.booking_id, b.booking_date, b.total_amount, b.status, u.name AS customer
    FROM booking b JOIN user u ON u.user_id = b.user_id
    ORDER BY b.booking_date DESC LIMIT 8
")->fetchAll();

include 'admin_header.php';
?>
<h2 class="mb-24">Dashboard</h2>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value">LKR <?= number_format($totalRevenue, 0) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)$totalBookings ?></div>
        <div class="stat-label">Confirmed Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)$totalMovies ?></div>
        <div class="stat-label">Movies Listed</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)$totalUsers ?></div>
        <div class="stat-label">Registered Customers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)$upcomingShows ?></div>
        <div class="stat-label">Upcoming Showtimes</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
    <div class="card">
        <h3 class="mb-16">Top Selling Movies</h3>
        <table>
            <thead><tr><th>Movie</th><th>Seats Sold</th></tr></thead>
            <tbody>
            <?php foreach ($topMovies as $m): ?>
                <tr><td><?= htmlspecialchars($m['title']) ?></td><td><?= (int)$m['seats_sold'] ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($topMovies)): ?><tr><td colspan="2" class="text-dim">No sales yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3 class="mb-16">Recent Bookings</h3>
        <table>
            <thead><tr><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentBookings as $b): ?>
                <?php $badge = $b['status']==='Confirmed'?'badge-success':($b['status']==='Cancelled'?'badge-cancelled':'badge-pending'); ?>
                <tr>
                    <td><?= htmlspecialchars($b['customer']) ?></td>
                    <td>LKR <?= number_format($b['total_amount'],0) ?></td>
                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recentBookings)): ?><tr><td colspan="3" class="text-dim">No bookings yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
