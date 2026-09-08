<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$showId = (int)($_POST['show_id'] ?? 0);
$seatIdsRaw = $_POST['seat_ids'] ?? '';
$seatIds = array_filter(array_map('intval', explode(',', $seatIdsRaw)));

if (!$showId || empty($seatIds)) {
    header('Location: seat_selection.php?show_id=' . $showId);
    exit;
}

// Must be logged in to book — preserve intended destination
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'seat_selection.php?show_id=' . $showId;
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

$standardPrice = 1200.00;
$vipPrice = 1800.00;

try {
    $pdo->beginTransaction();

    // Release expired holds first
    $pdo->prepare("
        UPDATE showtime_seat SET status = 'Available', booking_id = NULL, hold_expires_at = NULL
        WHERE show_id = ? AND status = 'Hold' AND hold_expires_at < NOW()
    ")->execute([$showId]);

    // Lock and verify requested seats are still available
    $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
    $stmt = $pdo->prepare("
        SELECT ss.showtime_seat_id, ss.status, s.category
        FROM showtime_seat ss
        JOIN seat s ON s.seat_id = ss.seat_id
        WHERE ss.show_id = ? AND ss.showtime_seat_id IN ($placeholders)
        FOR UPDATE
    ");
    $stmt->execute(array_merge([$showId], $seatIds));
    $rows = $stmt->fetchAll();

    if (count($rows) !== count($seatIds)) {
        throw new Exception('Some selected seats are invalid.');
    }

    $total = 0;
    foreach ($rows as $r) {
        if ($r['status'] !== 'Available') {
            throw new Exception('SEATS_TAKEN');
        }
        $total += ($r['category'] === 'VIP') ? $vipPrice : $standardPrice;
    }

    // Create the booking (Pending until payment succeeds)
    $stmt = $pdo->prepare("INSERT INTO booking (user_id, total_amount, status) VALUES (?, ?, 'Pending')");
    $stmt->execute([$userId, $total]);
    $bookingId = $pdo->lastInsertId();

    // Hold the seats for 10 minutes, tied to this booking
    $stmt = $pdo->prepare("
        UPDATE showtime_seat
        SET status = 'Hold', booking_id = ?, hold_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
        WHERE showtime_seat_id = ?
    ");
    foreach ($seatIds as $sid) {
        $stmt->execute([$bookingId, $sid]);
    }

    $pdo->commit();
    header('Location: checkout.php?booking_id=' . $bookingId);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    if ($e->getMessage() === 'SEATS_TAKEN') {
        $_SESSION['seat_error'] = 'Sorry, one or more of your selected seats were just taken by another customer. Please choose again.';
    } else {
        $_SESSION['seat_error'] = 'Something went wrong while holding your seats. Please try again.';
    }
    header('Location: seat_selection.php?show_id=' . $showId);
    exit;
}
