<?php
/**
 * Forgot Password Page
 * Generates a recovery token and displays the reset link
 * (since there's no email server, the link is shown directly)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

$error = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $token = generateRecoveryToken($email);
        if ($token) {
            $resetLink = SITE_URL . '/admin/reset-password.php?token=' . $token;
        } else {
            // Don't reveal whether the email exists — show generic message
            $error = 'If that email is registered, a recovery link has been generated. Contact your administrator if you need help.';
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
    <title>Forgot Password - FundACause</title>
    <meta name="description" content="Recover your FundACause account password.">
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
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-2">Forgot Password</h2>
            <p class="text-sm text-gray-500 text-center mb-6">Enter your email to generate a password reset link.</p>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm"><?php echo h($error); ?></div>
            <?php endif; ?>

            <?php if ($resetLink): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    <p class="font-semibold mb-2">Password reset link generated!</p>
                    <p class="text-xs mb-2">Copy this link and open it in your browser. It expires in 1 hour.</p>
                    <div class="flex items-center gap-2">
                        <input type="text" value="<?php echo h($resetLink); ?>" readonly id="resetLinkInput"
                               class="flex-1 text-xs px-3 py-2 bg-white border border-green-300 rounded-lg text-green-800 font-mono">
                        <button onclick="copyResetLink()" type="button"
                                class="px-3 py-2 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">
                            Copy
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="you@example.com"
                               value="<?php echo h($_POST['email'] ?? ''); ?>">
                    </div>

                    <button type="submit"
                            class="w-full bg-brand-500 text-white py-3 rounded-lg font-semibold hover:bg-brand-600 transition focus:ring-4 focus:ring-brand-200">
                        Generate Reset Link
                    </button>
                </form>
            <?php endif; ?>

            <p class="text-center text-sm text-gray-500 mt-6">
                <a href="<?php echo SITE_URL; ?>/admin/login.php" class="text-brand-600 hover:underline">&larr; Back to login</a>
            </p>
        </div>
    </div>

    <script>
    function copyResetLink() {
        const input = document.getElementById('resetLinkInput');
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = input.nextElementSibling;
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = 'Copy', 2000);
        }).catch(() => {
            // Fallback
            document.execCommand('copy');
        });
    }
    </script>
</body>
</html>
