<?php
/**
 * Admin Login Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($login) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = authenticate($login, $password);
        if ($result === true) {
            header('Location: ' . SITE_URL . '/admin/');
            exit;
        } else {
            $error = $result;
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
    <title>Admin Login - FundACause</title>
    <meta name="description" content="Secure admin login for FundACause campaign creators.">
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

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Admin Login</h2>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    <?php echo h($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                    <input type="text" name="login" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                           placeholder="Enter your username or email"
                           value="<?php echo h($_POST['login'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                           placeholder="Enter your password">
                </div>

                <button type="submit"
                        class="w-full bg-brand-500 text-white py-3 rounded-lg font-semibold hover:bg-brand-600 transition focus:ring-4 focus:ring-brand-200">
                    Sign In
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-4">
                <a href="<?php echo SITE_URL; ?>/admin/forgot-password.php" class="text-brand-600 hover:underline">Forgot your password?</a>
            </p>

            <p class="text-center text-sm text-gray-500 mt-3">
                Don't have an account? <a href="<?php echo SITE_URL; ?>/admin/register.php" class="text-brand-600 hover:underline">Register</a>
            </p>
        </div>

        <p class="text-center text-sm text-gray-400 mt-6">
            <a href="<?php echo SITE_URL; ?>/" class="hover:text-brand-500">&larr; Back to home</a>
        </p>
    </div>
</body>
</html>
