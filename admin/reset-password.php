<?php
/**
 * Reset Password Page
 * Accepts a recovery token via URL and allows setting a new password
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$tokenValid = false;
$tokenUser = null;

// Validate token on page load
if (!empty($token)) {
    $tokenUser = validateRecoveryToken($token);
    $tokenValid = !empty($tokenUser);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request. Please try again.';
        $tokenValid = true; // keep form visible
    } elseif (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
        $tokenValid = true;
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
        $tokenValid = true;
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
        $tokenValid = true;
    } else {
        if (resetPasswordWithToken($token, $newPassword)) {
            $success = 'Password reset successfully! You can now log in with your new password.';
            $tokenValid = false; // hide form
        } else {
            $error = 'Invalid or expired reset link. Please request a new one.';
        }
    }
}

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FundACause</title>
    <meta name="description" content="Set a new password for your FundACause account.">
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/logo.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef9ff', 100: '#d8f1ff', 200: '#b9e7ff', 300: '#89d9ff',
                            400: '#52c2ff', 500: '#2aa3ff', 600: '#1484f5', 700: '#0d6de1',
                            800: '#1158b6', 900: '#144b8f', 950: '#112f57',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="<?php echo SITE_URL; ?>/" class="inline-flex items-center space-x-2">
                <div class="w-12 h-12 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800">Fund<span class="text-brand-500">ACause</span></span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Reset Password</h2>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm"><?php echo h($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm"><?php echo h($success); ?></div>
                <a href="<?php echo SITE_URL; ?>/admin/login.php"
                   class="block w-full bg-brand-500 text-white py-3 rounded-lg font-semibold text-center hover:bg-brand-600 transition">
                    Go to Login
                </a>
            <?php elseif ($tokenValid): ?>
                <p class="text-sm text-gray-500 text-center mb-4">
                    Resetting password for <strong><?php echo h($tokenUser['username'] ?? ''); ?></strong>
                </p>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">
                    <input type="hidden" name="token" value="<?php echo h($token); ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password" required minlength="6"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="Min 6 characters">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="confirm_password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="Re-enter new password">
                    </div>

                    <button type="submit"
                            class="w-full bg-brand-500 text-white py-3 rounded-lg font-semibold hover:bg-brand-600 transition focus:ring-4 focus:ring-brand-200">
                        Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    Invalid or expired reset link. Please request a new one.
                </div>
                <a href="<?php echo SITE_URL; ?>/admin/forgot-password.php"
                   class="block w-full bg-brand-500 text-white py-3 rounded-lg font-semibold text-center hover:bg-brand-600 transition">
                    Request New Link
                </a>
            <?php endif; ?>

            <p class="text-center text-sm text-gray-500 mt-6">
                <a href="<?php echo SITE_URL; ?>/admin/login.php" class="text-brand-600 hover:underline">&larr; Back to login</a>
            </p>
        </div>
    </div>
</body>
</html>
