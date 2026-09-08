<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'Your Ticket';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];
$bookingId = (int)($_GET['booking_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT b.*, t.unique_qr_code, t.generated_at, p.method, p.transaction_id
    FROM booking b
    JOIN ticket t ON t.booking_id = b.booking_id
    LEFT JOIN payment p ON p.booking_id = b.booking_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: my_bookings.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.seat_number, s.category, st.date, st.start_time, st.experience_type,
           m.title, h.name AS hall_name, b.name AS branch_name
    FROM showtime_seat ss
    JOIN seat s ON s.seat_id = ss.seat_id
    JOIN show_time st ON st.show_id = ss.show_id
    JOIN movie m ON m.movie_id = st.movie_id
    JOIN hall h ON h.hall_id = st.hall_id
    JOIN branch b ON b.branch_id = h.branch_id
    WHERE ss.booking_id = ?
");
$stmt->execute([$bookingId]);
$seats = $stmt->fetchAll();
$first = $seats[0];

include 'includes/header.php';
?>
<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="alert alert-success mb-24">Booking confirmed! Show this ticket's QR code at the counter to collect your printed tickets.</div>

        <div class="ticket">
            <div class="ticket-header">
                <div>
                    <div class="text-dim" style="font-size:0.8rem;">CINEHUB E-TICKET</div>
                    <h3><?= htmlspecialchars($first['title']) ?></h3>
                </div>
                <span class="badge badge-success">Confirmed</span>
            </div>

            <p class="text-dim"><?= htmlspecialchars($first['branch_name']) ?> · <?= htmlspecialchars($first['hall_name']) ?></p>
            <p class="text-dim"><?= date('D, M j, Y', strtotime($first['date'])) ?> at <?= date('g:i A', strtotime($first['start_time'])) ?> · <?= htmlspecialchars($first['experience_type']) ?></p>

            <div class="ticket-seats">
                <?php foreach ($seats as $s): ?>
                    <span class="seat-chip"><?= htmlspecialchars($s['seat_number']) ?> (<?= htmlspecialchars($s['category']) ?>)</span>
                <?php endforeach; ?>
            </div>

            <div class="ticket-qr">
                <img src="assets/qrcodes/<?= htmlspecialchars($booking['unique_qr_code']) ?>.png" alt="Booking QR code" style="margin:0 auto;">
                <p class="mt-8 text-dim" style="font-size:0.8rem;"><?= htmlspecialchars($booking['unique_qr_code']) ?></p>
            </div>

            <div class="flex-between">
                <span class="text-dim">Booking ID</span>
                <span>#<?= $booking['booking_id'] ?></span>
            </div>
            <div class="flex-between mt-8">
                <span class="text-dim">Amount Paid</span>
                <span>LKR <?= number_format($booking['total_amount'], 2) ?></span>
            </div>
            <div class="flex-between mt-8">
                <span class="text-dim">Payment Method</span>
                <span><?= htmlspecialchars($booking['method']) ?></span>
            </div>
        </div>

        <div class="text-center mt-32">
            <button onclick="window.print()" class="btn btn-ghost">Print / Save as PDF</button>
            <a href="my_bookings.php" class="btn btn-primary">View My Bookings</a>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
