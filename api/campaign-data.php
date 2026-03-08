<?php
/**
 * API: Get Campaign Data (for real-time updates)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

$hexId = $_GET['hex'] ?? '';

if (empty($hexId)) {
    jsonResponse(['success' => false, 'message' => 'Missing campaign ID.'], 400);
}

$campaign = getCampaignByHex($hexId);
if (!$campaign) {
    jsonResponse(['success' => false, 'message' => 'Campaign not found.'], 404);
}

$media = getCampaignMedia($campaign['id']);
$donations = getCampaignDonations($campaign['id'], 20);

jsonResponse([
    'success' => true,
    'campaign' => [
        'hex_id' => $campaign['hex_id'],
        'title' => $campaign['title'],
        'description' => $campaign['description'],
        'goal_amount' => floatval($campaign['goal_amount']),
        'raised_amount' => floatval($campaign['raised_amount']),
        'supporter_count' => intval($campaign['supporter_count']),
        'paypal_email' => $campaign['paypal_email'],
        'progress' => progressPercent($campaign['raised_amount'], $campaign['goal_amount']),
    ],
    'media' => $media,
    'donations' => array_map(function($d) {
        return [
            'donor_name' => $d['donor_name'],
            'amount' => floatval($d['amount']),
            'date' => timeAgo($d['created_at']),
        ];
    }, $donations),
]);
