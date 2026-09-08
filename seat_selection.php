<?php
require_once 'config/db.php';
$basePath = '';

$showId = (int)($_GET['show_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT st.*, m.title, m.duration, h.name AS hall_name, h.hall_id, b.name AS branch_name
    FROM show_time st
    JOIN movie m ON m.movie_id = st.movie_id
    JOIN hall h ON h.hall_id = st.hall_id
    JOIN branch b ON b.branch_id = h.branch_id
    WHERE st.show_id = ?
");
$stmt->execute([$showId]);
$show = $stmt->fetch();

if (!$show) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'Select Seats — ' . $show['title'];

// Release any expired holds before showing seat map
$pdo->prepare("
    UPDATE showtime_seat SET status = 'Available', booking_id = NULL, hold_expires_at = NULL
    WHERE show_id = ? AND status = 'Hold' AND hold_expires_at < NOW()
")->execute([$showId]);

// Fetch seats for this showtime, grouped by row
$stmt = $pdo->prepare("
    SELECT ss.showtime_seat_id, ss.status, s.seat_number, s.row_label, s.category, s.seat_id
    FROM showtime_seat ss
    JOIN seat s ON s.seat_id = ss.seat_id
    WHERE ss.show_id = ?
    ORDER BY s.row_label, s.seat_id
");
$stmt->execute([$showId]);
$seats = $stmt->fetchAll();

$rows = [];
foreach ($seats as $s) {
    $rows[$s['row_label']][] = $s;
}
ksort($rows);

// Pricing: simple flat rule, VIP costs more (adjust as needed)
$standardPrice = 1200.00;
$vipPrice = 1800.00;

include 'includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container">
        <div class="mb-24">
            <h2><?= htmlspecialchars($show['title']) ?></h2>
            <p><?= htmlspecialchars($show['branch_name']) ?> · <?= htmlspecialchars($show['hall_name']) ?> ·
               <?= date('D, M j', strtotime($show['date'])) ?> at <?= date('g:i A', strtotime($show['start_time'])) ?> ·
               <?= htmlspecialchars($show['experience_type']) ?></p>
        </div>

        <?php if (!empty($_SESSION['seat_error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['seat_error']) ?></div>
            <?php unset($_SESSION['seat_error']); ?>
        <?php endif; ?>

        <div class="screen-indicator"></div>
        <div class="screen-label">SCREEN THIS WAY</div>

        <div class="seat-map" id="seatMap">
            <?php foreach ($rows as $rowLabel => $rowSeats): ?>
                <div class="seat-row">
                    <div class="seat-row-label"><?= htmlspecialchars($rowLabel) ?></div>
                    <?php foreach ($rowSeats as $seat): ?>
                        <?php
                            $isBooked = in_array($seat['status'], ['Booked', 'Hold', 'CheckedIn']);
                            $classes = 'seat';
                            if ($seat['category'] === 'VIP') $classes .= ' vip';
                            if ($isBooked) $classes .= ' booked';
                        ?>
                        <div class="<?= $classes ?>"
                             data-id="<?= $seat['showtime_seat_id'] ?>"
                             data-seat="<?= htmlspecialchars($seat['seat_number']) ?>"
                             data-price="<?= $seat['category'] === 'VIP' ? $vipPrice : $standardPrice ?>"
                             <?= $isBooked ? '' : 'onclick="toggleSeat(this)"' ?>>
                            <?= htmlspecialchars($seat['seat_number']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="seat-legend">
            <span><span class="legend-box" style="background:var(--bg-raised); border:1px solid var(--border);"></span> Standard (LKR <?= number_format($standardPrice, 0) ?>)</span>
            <span><span class="legend-box" style="background:var(--bg-raised); border:1px solid var(--gold);"></span> VIP (LKR <?= number_format($vipPrice, 0) ?>)</span>
            <span><span class="legend-box" style="background:var(--red);"></span> Selected</span>
            <span><span class="legend-box" style="background:#26262f;"></span> Unavailable</span>
        </div>
    </div>

    <form method="POST" action="hold_seats.php" id="bookingForm">
        <input type="hidden" name="show_id" value="<?= $showId ?>">
        <input type="hidden" name="seat_ids" id="seatIdsInput">
        <div class="summary-bar">
            <div class="container summary-inner">
                <div>
                    <div id="selectedCount" class="text-dim">No seats selected</div>
                    <div id="selectedTotal" style="font-family: var(--font-display); font-size:1.6rem;"></div>
                </div>
                <button type="submit" class="btn btn-primary" id="continueBtn" disabled>Continue to Payment</button>
            </div>
        </div>
    </form>
</section>

<script>
let selected = [];

function toggleSeat(el) {
    const id = el.dataset.id;
    const idx = selected.findIndex(s => s.id === id);
    if (idx > -1) {
        selected.splice(idx, 1);
        el.classList.remove('selected');
    } else {
        selected.push({ id, seat: el.dataset.seat, price: parseFloat(el.dataset.price) });
        el.classList.add('selected');
    }
    updateSummary();
}

function updateSummary() {
    const countEl = document.getElementById('selectedCount');
    const totalEl = document.getElementById('selectedTotal');
    const btn = document.getElementById('continueBtn');
    const input = document.getElementById('seatIdsInput');

    if (selected.length === 0) {
        countEl.textContent = 'No seats selected';
        totalEl.textContent = '';
        btn.disabled = true;
    } else {
        const seatNames = selected.map(s => s.seat).join(', ');
        countEl.textContent = selected.length + ' seat(s): ' + seatNames;
        const total = selected.reduce((sum, s) => sum + s.price, 0);
        totalEl.textContent = 'LKR ' + total.toLocaleString();
        btn.disabled = false;
    }
    input.value = selected.map(s => s.id).join(',');
}
</script>

<?php include 'includes/footer.php'; ?>
