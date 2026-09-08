<?php
require_once '../config/db.php';
require_once 'guard.php';

$bookingId = (int)($_GET['booking_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT ss.showtime_seat_id, s.seat_number, s.category,
           st.date, st.start_time, st.experience_type, m.title, h.name AS hall_name, br.name AS branch_name,
           t.unique_qr_code
    FROM showtime_seat ss
    JOIN seat s ON s.seat_id = ss.seat_id
    JOIN show_time st ON st.show_id = ss.show_id
    JOIN movie m ON m.movie_id = st.movie_id
    JOIN hall h ON h.hall_id = st.hall_id
    JOIN branch br ON br.branch_id = h.branch_id
    JOIN ticket t ON t.booking_id = ss.booking_id
    WHERE ss.booking_id = ?
    ORDER BY s.seat_number
");
$stmt->execute([$bookingId]);
$seats = $stmt->fetchAll();

if (empty($seats)) {
    die('No seats found for this booking.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Tickets — Booking #<?= $bookingId ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
    body { padding: 20px; background: #fff; color: #111; }
    .stub {
        border: 2px dashed #333;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        page-break-inside: avoid;
    }
    .stub h2 { font-size: 1.3rem; margin-bottom: 6px; }
    .stub p { font-size: 0.9rem; color: #444; margin: 2px 0; }
    .seat-badge { font-size: 2rem; font-weight: bold; border: 3px solid #111; border-radius: 8px; padding: 10px 18px; text-align: center; }
    .qr-code { text-align: center; }
    .qr-code img { width: 90px; height: 90px; }
    .print-bar { margin-bottom: 20px; }
    @media print { .print-bar { display: none; } }
</style>
</head>
<body>
<div class="print-bar">
    <button onclick="window.print()">Print All Stubs</button>
</div>

<?php foreach ($seats as $s): ?>
<div class="stub">
    <div>
        <h2><?= htmlspecialchars($s['title']) ?></h2>
        <p><?= htmlspecialchars($s['branch_name']) ?> · <?= htmlspecialchars($s['hall_name']) ?> · <?= htmlspecialchars($s['experience_type']) ?></p>
        <p><?= date('D, M j, Y', strtotime($s['date'])) ?> at <?= date('g:i A', strtotime($s['start_time'])) ?></p>
        <p>Category: <?= htmlspecialchars($s['category']) ?></p>
        <p>Booking #<?= $bookingId ?></p>
    </div>
    <div class="seat-badge"><?= htmlspecialchars($s['seat_number']) ?></div>
    <div class="qr-code">
        <img src="../assets/qrcodes/<?= htmlspecialchars($s['unique_qr_code']) ?>.png" alt="QR">
        <p style="font-size:0.65rem;">#<?= htmlspecialchars($s['unique_qr_code']) ?></p>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>
