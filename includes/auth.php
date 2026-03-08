<?php
/**
 * Authentication helper functions
 */

session_start();

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication — redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Require admin role — redirect to dashboard with error if not admin
 */
function requireAdmin() {
    requireAuth();
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'admin') {
        $_SESSION['flash_error'] = 'You do not have permission to access that page.';
        header('Location: ' . SITE_URL . '/admin/');
        exit;
    }
}

/**
 * Check if current user is an admin
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

/**
 * Check if current user account is active (approved)
 */
function isApproved() {
    $user = getCurrentUser();
    return $user && $user['status'] === 'active';
}

/**
 * Authenticate user with username/email and password
 * Returns: true on success, or a string error message on failure
 */
function authenticate($login, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :login1 OR email = :login2 LIMIT 1");
    $stmt->execute(['login1' => $login, 'login2' => $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return 'Invalid username or password.';
    }

    if ($user['status'] === 'pending') {
        return 'Your account is awaiting admin approval. Please check back later.';
    }

    if ($user['status'] === 'disabled') {
        return 'Your account has been disabled. Please contact an administrator.';
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    return true;
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

/**
 * Get current logged-in user
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, role, status, created_at FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate unique 8-digit hex ID for campaign
 */
function generateHexId() {
    $db = getDB();
    do {
        $hexId = bin2hex(random_bytes(4)); // 8 hex characters
        $stmt = $db->prepare("SELECT COUNT(*) FROM campaigns WHERE hex_id = :hex_id");
        $stmt->execute(['hex_id' => $hexId]);
    } while ($stmt->fetchColumn() > 0);
    return $hexId;
}

/**
 * Generate a secure password recovery token for a user
 */
function generateRecoveryToken($email) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND status != 'disabled' LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    if (!$user) return null;

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $db->prepare("UPDATE users SET recovery_token = :token, recovery_expires = :expires WHERE id = :id");
    $stmt->execute(['token' => $token, 'id' => $user['id'], 'expires' => $expires]);

    return $token;
}

/**
 * Validate a recovery token and return the user if valid
 */
function validateRecoveryToken($token) {
    if (empty($token)) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE recovery_token = :token AND recovery_expires > NOW() LIMIT 1");
    $stmt->execute(['token' => $token]);
    return $stmt->fetch();
}

/**
 * Reset a user's password using a valid recovery token
 */
function resetPasswordWithToken($token, $newPassword) {
    $user = validateRecoveryToken($token);
    if (!$user) return false;

    $db = getDB();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = :pw, recovery_token = NULL, recovery_expires = NULL WHERE id = :id");
    $stmt->execute(['pw' => $hash, 'id' => $user['id']]);
    return true;
}
