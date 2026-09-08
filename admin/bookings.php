<?php
require_once '../config/db.php';
require_once 'guard.php';
$pageTitle = 'Bookings';
$activeNav = 'bookings';

$statusFilter = $_GET['status'] ?? '';
$sql = "
    SELECT b.booking_id, b.booking_date, b.total_amount, b.status, u.name AS customer, u.email,
           GROUP_CONCAT(DISTINCT m.title SEPARATOR ', ') AS movies,
           COUNT(DISTINCT ss.showtime_seat_id) AS seat_count
    FROM booking b
    JOIN user u ON u.user_id = b.user_id
    LEFT JOIN showtime_seat ss ON ss.booking_id = b.booking_id
    LEFT JOIN show_time st ON st.show_id = ss.show_id
    LEFT JOIN movie m ON m.movie_id = st.movie_id
";
$params = [];
if (in_array($statusFilter, ['Pending','Confirmed','Cancelled'])) {
    $sql .= " WHERE b.status = ?";
    $params[] = $statusFilter;
}
$sql .= " GROUP BY b.booking_id ORDER BY b.booking_date DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

include 'admin_header.php';
?>
<div class="flex-between mb-24">
    <h2>Bookings</h2>
    <div class="flex gap-8">
        <a href="bookings.php" class="btn btn-ghost btn-sm <?= $statusFilter==''?'active':'' ?>">All</a>
        <a href="bookings.php?status=Confirmed" class="btn btn-ghost btn-sm">Confirmed</a>
        <a href="bookings.php?status=Pending" class="btn btn-ghost btn-sm">Pending</a>
        <a href="bookings.php?status=Cancelled" class="btn btn-ghost btn-sm">Cancelled</a>
    </div>
</div>

<div class="card" style="padding:0;">
    <table>
        <thead><tr><th>#</th><th>Customer</th><th>Movie(s)</th><th>Seats</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <?php $badge = $b['status']==='Confirmed'?'badge-success':($b['status']==='Cancelled'?'badge-cancelled':'badge-pending'); ?>
            <tr>
                <td>#<?= $b['booking_id'] ?></td>
                <td><?= htmlspecialchars($b['customer']) ?><br><span class="text-dim" style="font-size:0.8rem;"><?= htmlspecialchars($b['email']) ?></span></td>
                <td><?= htmlspecialchars($b['movies'] ?? '—') ?></td>
                <td><?= (int)$b['seat_count'] ?></td>
                <td>LKR <?= number_format($b['total_amount'], 2) ?></td>
                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                <td><?= date('M j, Y g:i A', strtotime($b['booking_date'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?><tr><td colspan="7" class="text-dim">No bookings found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>
