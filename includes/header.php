<?php
/**
 * Public header template
 */
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', SITE_NAME);
if (!defined('PAGE_DESCRIPTION')) define('PAGE_DESCRIPTION', 'FundACause helps people raise funds for meaningful causes and connect with donors who want to make a real impact.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h(PAGE_TITLE); ?></title>
    <meta name="description" content="<?php echo h(PAGE_DESCRIPTION); ?>">
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/logo.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef9ff',
                            100: '#d8f1ff',
                            200: '#b9e7ff',
                            300: '#89d9ff',
                            400: '#52c2ff',
                            500: '#2aa3ff',
                            600: '#1484f5',
                            700: '#0d6de1',
                            800: '#1158b6',
                            900: '#144b8f',
                            950: '#112f57',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        .swiper-button-next, .swiper-button-prev { color: #fff !important; }
        .swiper-pagination-bullet-active { background: #2aa3ff !important; }
        .hamburger-line { transition: all 0.3s ease; }
        .menu-open .hamburger-line:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
        .menu-open .hamburger-line:nth-child(2) { opacity: 0; }
        .menu-open .hamburger-line:nth-child(3) { transform: rotate(-45deg) translate(7px, -6px); }
        .mobile-menu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .mobile-menu.open { max-height: 500px; }
        .progress-bar { transition: width 1s ease-in-out; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- Navigation -->
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>/" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-brand-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-800">Fund<span class="text-brand-500">ACause</span></span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="<?php echo SITE_URL; ?>/" class="text-gray-600 hover:text-brand-600 transition">Home</a>
                <a href="<?php echo SITE_URL; ?>/mission" class="text-gray-600 hover:text-brand-600 transition">Our Mission</a>
                <a href="<?php echo SITE_URL; ?>/terms" class="text-gray-600 hover:text-brand-600 transition">Terms</a>
                <a href="<?php echo SITE_URL; ?>/privacy" class="text-gray-600 hover:text-brand-600 transition">Privacy</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/" class="bg-brand-500 text-white px-4 py-2 rounded-lg hover:bg-brand-600 transition">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/admin/login.php" class="bg-brand-500 text-white px-4 py-2 rounded-lg hover:bg-brand-600 transition">Admin Login</a>
                <?php endif; ?>
            </div>

            <!-- Hamburger Menu (Mobile) -->
            <button id="hamburgerBtn" class="md:hidden flex flex-col space-y-1.5 p-2" onclick="toggleMenu()">
                <span class="hamburger-line block w-6 h-0.5 bg-gray-600"></span>
                <span class="hamburger-line block w-6 h-0.5 bg-gray-600"></span>
                <span class="hamburger-line block w-6 h-0.5 bg-gray-600"></span>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="mobile-menu md:hidden">
            <div class="pb-4 space-y-2">
                <a href="<?php echo SITE_URL; ?>/" class="block py-2 px-4 text-gray-600 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition">Home</a>
                <a href="<?php echo SITE_URL; ?>/mission" class="block py-2 px-4 text-gray-600 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition">Our Mission</a>
                <a href="<?php echo SITE_URL; ?>/terms" class="block py-2 px-4 text-gray-600 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition">Terms</a>
                <a href="<?php echo SITE_URL; ?>/privacy" class="block py-2 px-4 text-gray-600 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition">Privacy</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/" class="block py-2 px-4 bg-brand-500 text-white rounded-lg text-center">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/admin/login.php" class="block py-2 px-4 bg-brand-500 text-white rounded-lg text-center">Admin Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleMenu() {
    const btn = document.getElementById('hamburgerBtn');
    const menu = document.getElementById('mobileMenu');
    btn.classList.toggle('menu-open');
    menu.classList.toggle('open');
}
</script>
