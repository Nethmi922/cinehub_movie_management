<?php
require_once 'config/db.php';
require_once 'vendor/phpqrcode/qrlib.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$bookingId = (int)($_POST['booking_id'] ?? 0);
$method = $_POST['method'] ?? 'Card';

$stmt = $pdo->prepare("SELECT * FROM booking WHERE booking_id = ? AND user_id = ?");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking || $booking['status'] !== 'Pending') {
    header('Location: index.php');
    exit;
}

// Confirm the seats are still held for this booking (not expired)
$stmt = $pdo->prepare("SELECT * FROM showtime_seat WHERE booking_id = ? AND status = 'Hold' AND hold_expires_at > NOW()");
$stmt->execute([$bookingId]);
$heldSeats = $stmt->fetchAll();

if (empty($heldSeats)) {
    $_SESSION['seat_error'] = 'Your seat hold expired before payment was completed. Please select seats again.';
    header('Location: index.php');
    exit;
}

// Apply offer discount if one was chosen at checkout
$appliedOffer = $_SESSION['checkout_offer_' . $bookingId] ?? null;
$discount = $appliedOffer ? ($booking['total_amount'] * ($appliedOffer['discount_pct'] / 100)) : 0;
$finalTotal = $booking['total_amount'] - $discount;

try {
    $pdo->beginTransaction();

    // Update booking: confirm, apply offer, lock in final amount
    $stmt = $pdo->prepare("UPDATE booking SET status = 'Confirmed', total_amount = ?, offer_id = ? WHERE booking_id = ?");
    $stmt->execute([$finalTotal, $appliedOffer['offer_id'] ?? null, $bookingId]);

    // Create payment record (dummy gateway — no real card processing)
    $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
    $stmt = $pdo->prepare("
        INSERT INTO payment (booking_id, amount, method, transaction_id, payment_date, payment_time)
        VALUES (?, ?, ?, ?, CURDATE(), CURTIME())
    ");
    $stmt->execute([$bookingId, $finalTotal, $method, $transactionId]);

    // Confirm all held seats as Booked
    $pdo->prepare("UPDATE showtime_seat SET status = 'Booked' WHERE booking_id = ?")->execute([$bookingId]);

    // Generate ONE ticket + ONE QR code covering the whole booking
    $qrCode = 'CINEHUB-' . $bookingId . '-' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = $pdo->prepare("INSERT INTO ticket (booking_id, unique_qr_code) VALUES (?, ?)");
    $stmt->execute([$bookingId, $qrCode]);

    // Generate the QR image file
    $qrPath = 'assets/qrcodes/' . $qrCode . '.png';
    QRcode::png($qrCode, __DIR__ . '/' . $qrPath, QR_ECLEVEL_L, 6, 2);

    // Award loyalty points: 1 point per LKR 100 spent
    $pointsEarned = (int)floor($finalTotal / 100);
    if ($pointsEarned > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO loyalty (user_id, points, type, time, date, booking_id)
            VALUES (?, ?, 'Earn', CURTIME(), CURDATE(), ?)
        ");
        $stmt->execute([$userId, $pointsEarned, $bookingId]);
    }

    $pdo->commit();
    unset($_SESSION['checkout_offer_' . $bookingId]);

    header('Location: ticket.php?booking_id=' . $bookingId);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['seat_error'] = 'Payment could not be completed. Please try again.';
    header('Location: checkout.php?booking_id=' . $bookingId);
    exit;
}
