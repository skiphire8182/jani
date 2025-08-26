<?php
require_once '../includes/db.php';

$party_id = $_GET['party_id'] ?? 0;

if (empty($party_id)) {
    die('Invalid party ID.');
}

$party = null;
$payments = [];

try {
    $pdo = getDbConnection();

    // Get party details
    $stmt = $pdo->prepare("SELECT * FROM parties WHERE id = ?");
    $stmt->execute([$party_id]);
    $party = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get payment history
    $stmt = $pdo->prepare("SELECT * FROM party_payments WHERE party_id = ? ORDER BY payment_date DESC");
    $stmt->execute([$party_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>
<div class="modal-header gradient-4 text-white">
    <h5 class="modal-title">Payment History for <?php echo htmlspecialchars($party['party_name']); ?></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Payment Date</th>
                <th>Amount Paid</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                    <td><?php echo htmlspecialchars($payment['amount_paid']); ?></td>
                    <td><?php echo htmlspecialchars($payment['note']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>