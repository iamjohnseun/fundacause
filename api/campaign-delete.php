<?php
/**
 * API: Delete Campaign
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$campaignId = intval($data['id'] ?? 0);

if (!$campaignId) {
    jsonResponse(['success' => false, 'message' => 'Invalid campaign ID.'], 400);
}

$db = getDB();
$user = getCurrentUser();

// Verify ownership
$stmt = $db->prepare("SELECT * FROM campaigns WHERE id = :id AND user_id = :uid");
$stmt->execute(['id' => $campaignId, 'uid' => $user['id']]);
$campaign = $stmt->fetch();

if (!$campaign) {
    jsonResponse(['success' => false, 'message' => 'Campaign not found.'], 404);
}

// Delete media files
$media = getCampaignMedia($campaignId);
foreach ($media as $m) {
    $filePath = __DIR__ . '/../uploads/campaigns/' . $m['file_path'];
    if (file_exists($filePath)) unlink($filePath);
}

// Delete campaign (cascades to media and donations)
$stmt = $db->prepare("DELETE FROM campaigns WHERE id = :id");
$stmt->execute(['id' => $campaignId]);

jsonResponse(['success' => true, 'message' => 'Campaign deleted.']);
