<?php
/**
 * Common helper functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize output for HTML
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function formatMoney($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Calculate progress percentage
 */
function progressPercent($raised, $goal) {
    if ($goal <= 0) return 0;
    $pct = ($raised / $goal) * 100;
    return min(100, round($pct, 1));
}

/**
 * Get campaign by hex ID
 */
function getCampaignByHex($hexId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE hex_id = :hex_id AND status = 'active'");
    $stmt->execute(['hex_id' => $hexId]);
    return $stmt->fetch();
}

/**
 * Get campaign media
 */
function getCampaignMedia($campaignId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM campaign_media WHERE campaign_id = :id ORDER BY sort_order ASC");
    $stmt->execute(['id' => $campaignId]);
    return $stmt->fetchAll();
}

/**
 * Get recent donations for a campaign
 */
function getCampaignDonations($campaignId, $limit = 10) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM donations WHERE campaign_id = :id AND status = 'completed' ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindValue('id', $campaignId, PDO::PARAM_INT);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get all campaigns for a user
 */
function getUserCampaigns($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE user_id = :id ORDER BY created_at DESC");
    $stmt->execute(['id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

/**
 * JSON response helper
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
