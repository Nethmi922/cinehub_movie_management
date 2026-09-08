<?php
require_once '../config/db.php';
require_once 'guard.php';
$basePath = '../';
$pageTitle = 'Counter — Ticket Lookup';

$ticket = null;
$booking = null;
$seats = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $code = trim($_POST['qr_code']);

    $stmt = $pdo->prepare("
        SELECT t.*, b.booking_id, b.status AS booking_status, b.total_amount, u.name AS customer_name, u.phone
        FROM ticket t
        JOIN booking b ON b.booking_id = t.booking_id
        JOIN user u ON u.user_id = b.user_id
        WHERE t.unique_qr_code = ?
    ");
    $stmt->execute([$code]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        $error = 'No booking found for this QR code. Double-check the code and try again.';
    } elseif ($ticket['booking_status'] !== 'Confirmed') {
        $error = 'This booking is "' . $ticket['booking_status'] . '" and is not valid for check-in.';
    } else {
        $stmt = $pdo->prepare("
            SELECT ss.showtime_seat_id, ss.status, s.seat_number, s.category,
                   st.date, st.start_time, st.experience_type, m.title, h.name AS hall_name, br.name AS branch_name
            FROM showtime_seat ss
            JOIN seat s ON s.seat_id = ss.seat_id
            JOIN show_time st ON st.show_id = ss.show_id
            JOIN movie m ON m.movie_id = st.movie_id
            JOIN hall h ON h.hall_id = st.hall_id
            JOIN branch br ON br.branch_id = h.branch_id
            WHERE ss.booking_id = ?
            ORDER BY s.seat_number
        ");
        $stmt->execute([$ticket['booking_id']]);
        $seats = $stmt->fetchAll();
    }
}

include '../includes/header.php';
?>
<section class="section" style="padding-top:40px;">
    <div class="container" style="max-width:760px;">
        <h2 class="mb-24">Counter — Ticket Check-In</h2>

        <div class="card mb-24">
            <form method="POST" class="flex gap-12">
                <input type="text" name="qr_code" placeholder="Scan or type the QR code (e.g. CINEHUB-1-0DAB4228)"
                       value="<?= htmlspecialchars($_POST['qr_code'] ?? '') ?>" autofocus style="flex:1;">
                <button type="submit" class="btn btn-primary">Look Up</button>
            </form>
            <p class="text-dim mt-8" style="font-size:0.85rem;">Tip: a USB QR scanner types the code directly into this field and submits automatically.</p>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($ticket && empty($error)): ?>
        <div class="card mb-24">
            <div class="flex-between mb-16">
                <div>
                    <h3><?= htmlspecialchars($seats[0]['title']) ?></h3>
                    <p class="text-dim mt-8"><?= htmlspecialchars($seats[0]['branch_name']) ?> · <?= htmlspecialchars($seats[0]['hall_name']) ?> ·
                       <?= date('D, M j, Y', strtotime($seats[0]['date'])) ?> at <?= date('g:i A', strtotime($seats[0]['start_time'])) ?></p>
                </div>
                <span class="badge badge-success">Confirmed</span>
            </div>
            <p class="text-dim">Customer: <?= htmlspecialchars($ticket['customer_name']) ?> · <?= htmlspecialchars($ticket['phone']) ?></p>
            <p class="text-dim">Booking #<?= $ticket['booking_id'] ?> · LKR <?= number_format($ticket['total_amount'], 2) ?></p>
        </div>

        <div class="card mb-24">
            <h3 class="mb-16">Seats on this booking</h3>
            <form method="POST" action="checkin.php">
                <input type="hidden" name="booking_id" value="<?= $ticket['booking_id'] ?>">
                <table>
                    <thead><tr><th></th><th>Seat</th><th>Category</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($seats as $s): ?>
                        <tr>
                            <td>
                                <?php if ($s['status'] !== 'CheckedIn'): ?>
                                <input type="checkbox" name="seat_ids[]" value="<?= $s['showtime_seat_id'] ?>" checked>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['seat_number']) ?></td>
                            <td><?= htmlspecialchars($s['category']) ?></td>
                            <td><?= $s['status'] === 'CheckedIn' ? '<span class="badge badge-success">Collected</span>' : '<span class="badge badge-pending">Not collected</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="flex gap-12 mt-24">
                    <button type="submit" class="btn btn-primary">Mark Selected as Collected</button>
                    <a href="print_ticket.php?booking_id=<?= $ticket['booking_id'] ?>" target="_blank" class="btn btn-ghost">Print Seat Stubs</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
