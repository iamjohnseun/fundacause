<?php
/**
 * Change Password Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } else {
        // Verify current password
        $db = getDB();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($currentPassword, $row['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = :pw WHERE id = :id")->execute(['pw' => $hash, 'id' => $user['id']]);
            $success = 'Password changed successfully!';
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
    <title>Change Password - FundACause</title>
    <meta name="description" content="Change your FundACause account password.">
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
<body class="bg-gray-50 min-h-screen">

<!-- Admin Nav -->
<nav class="bg-white shadow-sm">
    <div class="max-w-4xl mx-auto px-4 flex justify-between items-center h-16">
        <a href="<?php echo SITE_URL; ?>/admin/" class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-gray-600">Back to Dashboard</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="text-sm text-red-500 hover:text-red-700">Logout</a>
    </div>
</nav>

<div class="max-w-lg mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Change Password</h1>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm"><?php echo h($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm"><?php echo h($success); ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                       placeholder="Enter your current password">
            </div>

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
        </div>

        <button type="submit"
                class="w-full bg-brand-500 text-white py-3 rounded-lg font-semibold hover:bg-brand-600 transition focus:ring-4 focus:ring-brand-200">
            Update Password
        </button>
    </form>
</div>

</body>
</html>
