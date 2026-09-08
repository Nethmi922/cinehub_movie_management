<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'My Bookings';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.booking_id, b.booking_date, b.total_amount, b.status,
           m.title, st.date, st.start_time, h.name AS hall_name, br.name AS branch_name,
           t.unique_qr_code
    FROM booking b
    JOIN showtime_seat ss ON ss.booking_id = b.booking_id
    JOIN show_time st ON st.show_id = ss.show_id
    JOIN movie m ON m.movie_id = st.movie_id
    JOIN hall h ON h.hall_id = st.hall_id
    JOIN branch br ON br.branch_id = h.branch_id
    LEFT JOIN ticket t ON t.booking_id = b.booking_id
    WHERE b.user_id = ?
    GROUP BY b.booking_id
    ORDER BY b.booking_date DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

include 'includes/header.php';
?>
<section class="section" style="padding-top:40px;">
    <div class="container">
        <h2 class="mb-24">My Bookings</h2>

        <?php if (!empty($_SESSION['booking_msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['booking_msg']) ?></div>
            <?php unset($_SESSION['booking_msg']); ?>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <p>You haven't booked any tickets yet. <a href="index.php" class="text-gold">Browse movies</a> to get started.</p>
        <?php endif; ?>

        <?php foreach ($bookings as $b): ?>
            <?php
                $badgeClass = $b['status'] === 'Confirmed' ? 'badge-success' : ($b['status'] === 'Cancelled' ? 'badge-cancelled' : 'badge-pending');
                $isPast = strtotime($b['date'] . ' ' . $b['start_time']) < time();
                $canCancel = $b['status'] === 'Confirmed' && !$isPast;
            ?>
            <div class="card mb-16">
                <div class="flex-between">
                    <div>
                        <h3><?= htmlspecialchars($b['title']) ?></h3>
                        <p class="text-dim mt-8"><?= htmlspecialchars($b['branch_name']) ?> · <?= htmlspecialchars($b['hall_name']) ?> ·
                           <?= date('D, M j, Y', strtotime($b['date'])) ?> at <?= date('g:i A', strtotime($b['start_time'])) ?></p>
                        <p class="text-dim mt-8">Booking #<?= $b['booking_id'] ?> · LKR <?= number_format($b['total_amount'], 2) ?></p>
                    </div>
                    <div class="text-center">
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($b['status']) ?></span>
                        <div class="flex gap-8 mt-16">
                            <?php if ($b['status'] === 'Confirmed' && $b['unique_qr_code']): ?>
                                <a href="ticket.php?booking_id=<?= $b['booking_id'] ?>" class="btn btn-ghost btn-sm">View Ticket</a>
                            <?php endif; ?>
                            <?php if ($canCancel): ?>
                                <form method="POST" action="cancel_booking.php" onsubmit="return confirm('Cancel this booking? This cannot be undone.');">
                                    <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
