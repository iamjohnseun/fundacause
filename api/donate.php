<?php
/**
 * API: Record a Donation
 * Called via AJAX when a PayPal payment is completed
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$hexId = $data['campaign_hex'] ?? '';
$amount = floatval($data['amount'] ?? 0);
$donorName = trim($data['donor_name'] ?? 'Anonymous');
$donorEmail = trim($data['donor_email'] ?? '');
$transactionId = trim($data['transaction_id'] ?? '');

if (empty($hexId) || $amount <= 0) {
    jsonResponse(['success' => false, 'message' => 'Invalid donation data.'], 400);
}

$db = getDB();

// Get campaign
$stmt = $db->prepare("SELECT * FROM campaigns WHERE hex_id = :hex AND status = 'active'");
$stmt->execute(['hex' => $hexId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    jsonResponse(['success' => false, 'message' => 'Campaign not found or inactive.'], 404);
}

// Record donation
$stmt = $db->prepare("INSERT INTO donations (campaign_id, donor_name, donor_email, amount, paypal_transaction_id, status) VALUES (:cid, :name, :email, :amount, :txn, 'completed')");
$stmt->execute([
    'cid' => $campaign['id'],
    'name' => $donorName ?: 'Anonymous',
    'email' => $donorEmail,
    'amount' => $amount,
    'txn' => $transactionId,
]);

// Update campaign stats
$stmt = $db->prepare("UPDATE campaigns SET raised_amount = raised_amount + :amount, supporter_count = supporter_count + 1 WHERE id = :id");
$stmt->execute(['amount' => $amount, 'id' => $campaign['id']]);

// Return updated stats
$stmt = $db->prepare("SELECT raised_amount, supporter_count, goal_amount FROM campaigns WHERE id = :id");
$stmt->execute(['id' => $campaign['id']]);
$updated = $stmt->fetch();

jsonResponse([
    'success' => true,
    'message' => 'Thank you for your donation!',
    'raised_amount' => floatval($updated['raised_amount']),
    'supporter_count' => intval($updated['supporter_count']),
    'goal_amount' => floatval($updated['goal_amount']),
    'progress' => progressPercent($updated['raised_amount'], $updated['goal_amount']),
]);
