<?php
require_once 'config/db.php';
$basePath = '';
$pageTitle = 'Loyalty Points';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
      COALESCE(SUM(CASE WHEN type='Earn' THEN points ELSE 0 END), 0) -
      COALESCE(SUM(CASE WHEN type='Redeem' THEN points ELSE 0 END), 0) AS balance
    FROM loyalty WHERE user_id = ?
");
$stmt->execute([$userId]);
$balance = $stmt->fetch()['balance'];

$stmt = $pdo->prepare("SELECT * FROM loyalty WHERE user_id = ? ORDER BY date DESC, time DESC LIMIT 50");
$stmt->execute([$userId]);
$history = $stmt->fetchAll();

include 'includes/header.php';
?>
<section class="section" style="padding-top:40px;">
    <div class="container">
        <h2 class="mb-24">Loyalty Points</h2>

        <div class="card mb-32 text-center" style="padding:40px;">
            <div class="text-dim mb-8">Your current balance</div>
            <div style="font-family:var(--font-display); font-size:3.4rem; color:var(--gold);"><?= (int)$balance ?> pts</div>
            <p class="mt-16">Earn 1 point for every LKR 100 spent on bookings. Points can be redeemed for discounts on future bookings (ask counter staff or admin to apply a redemption).</p>
        </div>

        <h3 class="mb-16">Transaction History</h3>
        <?php if (empty($history)): ?>
            <p>No loyalty activity yet — book a ticket to start earning points.</p>
        <?php else: ?>
        <div class="card" style="padding:0;">
            <table>
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Points</th><th>Booking</th></tr>
                </thead>
                <tbody>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($h['date'])) ?></td>
                        <td><?= $h['type'] === 'Earn' ? '<span class="text-gold">Earned</span>' : '<span class="text-dim">Redeemed</span>' ?></td>
                        <td><?= $h['type'] === 'Earn' ? '+' : '-' ?><?= (int)$h['points'] ?></td>
                        <td><?= $h['booking_id'] ? '#' . $h['booking_id'] : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
