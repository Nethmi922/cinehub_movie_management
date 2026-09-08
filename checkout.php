<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'Checkout';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];
$bookingId = (int)($_GET['booking_id'] ?? 0);

// Release expired holds globally (cheap safety net) then validate this booking's hold
$pdo->query("UPDATE showtime_seat SET status='Available', booking_id=NULL, hold_expires_at=NULL WHERE status='Hold' AND hold_expires_at < NOW()");

$stmt = $pdo->prepare("SELECT * FROM booking WHERE booking_id = ? AND user_id = ?");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking || $booking['status'] !== 'Pending') {
    header('Location: index.php');
    exit;
}

// Fetch held seats + show info
$stmt = $pdo->prepare("
    SELECT ss.showtime_seat_id, s.seat_number, s.category, st.date, st.start_time, st.experience_type,
           m.title, h.name AS hall_name, b.name AS branch_name, ss.hold_expires_at
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

if (empty($seats)) {
    header('Location: index.php');
    exit;
}
$first = $seats[0];

$error = '';
$appliedOffer = null;
if (isset($_SESSION['checkout_offer_' . $bookingId])) {
    $appliedOffer = $_SESSION['checkout_offer_' . $bookingId];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_offer'])) {
    $code = trim($_POST['offer_title'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM offer WHERE title = ? AND is_active = 1 AND CURDATE() BETWEEN start_date AND expiry_date");
    $stmt->execute([$code]);
    $offer = $stmt->fetch();
    if ($offer) {
        $_SESSION['checkout_offer_' . $bookingId] = $offer;
        $appliedOffer = $offer;
    } else {
        $error = 'That offer code is invalid or expired.';
    }
}

$discount = $appliedOffer ? ($booking['total_amount'] * ($appliedOffer['discount_pct'] / 100)) : 0;
$finalTotal = $booking['total_amount'] - $discount;

include 'includes/header.php';
?>
<section class="section" style="padding-top:40px;">
    <div class="container" style="max-width:720px;">
        <h2 class="mb-24">Checkout</h2>

        <div class="card mb-24">
            <h3 class="mb-8"><?= htmlspecialchars($first['title']) ?></h3>
            <p class="mb-16"><?= htmlspecialchars($first['branch_name']) ?> · <?= htmlspecialchars($first['hall_name']) ?> ·
               <?= date('D, M j', strtotime($first['date'])) ?> at <?= date('g:i A', strtotime($first['start_time'])) ?> ·
               <?= htmlspecialchars($first['experience_type']) ?></p>
            <div class="ticket-seats">
                <?php foreach ($seats as $s): ?>
                    <span class="seat-chip"><?= htmlspecialchars($s['seat_number']) ?> (<?= htmlspecialchars($s['category']) ?>)</span>
                <?php endforeach; ?>
            </div>
            <p class="mt-16 text-dim">Seats held until <?= date('g:i A', strtotime($first['hold_expires_at'])) ?> — complete payment before then.</p>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card mb-24">
            <h3 class="mb-16">Have an offer code?</h3>
            <?php if ($appliedOffer): ?>
                <div class="alert alert-success">"<?= htmlspecialchars($appliedOffer['title']) ?>" applied — <?= $appliedOffer['discount_pct'] ?>% off</div>
            <?php else: ?>
            <form method="POST" class="flex gap-12">
                <input type="text" name="offer_title" placeholder="e.g. Weekday Special" style="flex:1;">
                <button type="submit" name="apply_offer" class="btn btn-ghost">Apply</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="card mb-24">
            <div class="flex-between mb-8"><span class="text-dim">Subtotal</span><span>LKR <?= number_format($booking['total_amount'], 2) ?></span></div>
            <?php if ($appliedOffer): ?>
            <div class="flex-between mb-8"><span class="text-dim">Discount</span><span class="text-gold">- LKR <?= number_format($discount, 2) ?></span></div>
            <?php endif; ?>
            <div class="flex-between mt-16" style="font-size:1.3rem; font-family:var(--font-display);"><span>Total</span><span>LKR <?= number_format($finalTotal, 2) ?></span></div>
        </div>

        <div class="card">
            <h3 class="mb-16">Payment</h3>
            <form method="POST" action="process_payment.php">
                <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                <div class="form-group">
                    <label for="method">Payment method</label>
                    <select name="method" id="method" required>
                        <option value="Card">Credit / Debit Card</option>
                        <option value="Wallet">Mobile Wallet</option>
                        <option value="Cash">Pay at Counter (Cash)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="card_number">Card number (demo only, not stored)</label>
                    <input type="text" id="card_number" placeholder="4242 4242 4242 4242" maxlength="19">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Pay LKR <?= number_format($finalTotal, 2) ?></button>
            </form>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
