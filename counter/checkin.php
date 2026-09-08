<?php
require_once '../config/db.php';
require_once 'guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: scan.php');
    exit;
}

$bookingId = (int)($_POST['booking_id'] ?? 0);
$seatIds = array_filter(array_map('intval', $_POST['seat_ids'] ?? []));

if ($bookingId && !empty($seatIds)) {
    $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
    $stmt = $pdo->prepare("
        UPDATE showtime_seat SET status = 'CheckedIn'
        WHERE booking_id = ? AND showtime_seat_id IN ($placeholders) AND status = 'Booked'
    ");
    $stmt->execute(array_merge([$bookingId], $seatIds));
}

header('Location: scan.php');
exit;
