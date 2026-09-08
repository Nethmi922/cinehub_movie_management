<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
$userId = $_SESSION['user_id'];
$bookingId = (int)($_POST['booking_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT b.*, MIN(st.date) as show_date, MIN(st.start_time) as show_time
    FROM booking b
    JOIN showtime_seat ss ON ss.booking_id = b.booking_id
    JOIN show_time st ON st.show_id = ss.show_id
    WHERE b.booking_id = ? AND b.user_id = ?
    GROUP BY b.booking_id
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking || $booking['status'] !== 'Confirmed') {
    header('Location: my_bookings.php');
    exit;
}

$showDateTime = strtotime($booking['show_date'] . ' ' . $booking['show_time']);
if ($showDateTime < time()) {
    $_SESSION['booking_msg'] = 'This showtime has already passed and cannot be cancelled.';
    header('Location: my_bookings.php');
    exit;
}

try {
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE booking SET status = 'Cancelled' WHERE booking_id = ?")->execute([$bookingId]);
    $pdo->prepare("
        UPDATE showtime_seat SET status = 'Available', booking_id = NULL, hold_expires_at = NULL
        WHERE booking_id = ?
    ")->execute([$bookingId]);
    $pdo->commit();
    $_SESSION['booking_msg'] = 'Booking #' . $bookingId . ' has been cancelled. Seats are now released.';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['booking_msg'] = 'Could not cancel this booking. Please try again.';
}

header('Location: my_bookings.php');
exit;
