<?php
/**
 * Admin Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$user = getCurrentUser();
$campaigns = getUserCampaigns($user['id']);
$uploadWarning = $_SESSION['upload_warning'] ?? '';
unset($_SESSION['upload_warning']);
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

// Calculate totals
$totalRaised = 0;
$totalSupporters = 0;
foreach ($campaigns as $c) {
    $totalRaised += $c['raised_amount'];
    $totalSupporters += $c['supporter_count'];
}

define('PAGE_TITLE', 'Dashboard - ' . SITE_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h(PAGE_TITLE); ?></title>
    <meta name="description" content="FundACause admin dashboard to track campaign performance, supporters, and donations.">
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
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center h-16">
        <a href="<?php echo SITE_URL; ?>/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-brand-400 to-brand-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
            <span class="text-lg font-bold text-gray-800">Fund<span class="text-brand-500">ACause</span></span>
        </a>
        <div class="flex items-center space-x-4">
            <?php if (isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="text-sm text-brand-600 hover:text-brand-800 transition hidden sm:inline">Manage Users</a>
            <?php endif; ?>
            <a href="<?php echo SITE_URL; ?>/admin/change-password.php" class="text-sm text-gray-500 hover:text-gray-700 transition hidden sm:inline">Password</a>
            <span class="text-sm text-gray-500 hidden sm:inline">Hi, <?php echo h($user['username']); ?></span>
            <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="text-sm text-red-500 hover:text-red-700 transition">Logout</a>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-8">
    <?php if ($flashError): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm"><?php echo h($flashError); ?></div>
    <?php endif; ?>

    <?php if ($user['status'] === 'pending'): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg mb-6 text-sm">
            Your account is pending approval. An administrator must activate your account before you can create campaigns.
        </div>
    <?php endif; ?>

    <?php if ($uploadWarning): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg mb-6 text-sm"><?php echo h($uploadWarning); ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Total Campaigns</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo count($campaigns); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Total Raised</p>
            <p class="text-3xl font-bold text-green-600"><?php echo formatMoney($totalRaised); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-500 mb-1">Total Supporters</p>
            <p class="text-3xl font-bold text-brand-600"><?php echo number_format($totalSupporters); ?></p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Your Campaigns</h2>
        <a href="<?php echo SITE_URL; ?>/admin/campaign-create.php"
           class="bg-brand-500 text-white px-5 py-2.5 rounded-lg hover:bg-brand-600 transition flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>New Campaign</span>
        </a>
    </div>

    <!-- Campaign List -->
    <?php if (empty($campaigns)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <p class="text-gray-500 mb-4">You haven't created any campaigns yet.</p>
            <a href="<?php echo SITE_URL; ?>/admin/campaign-create.php" class="text-brand-600 hover:underline font-medium">Create your first campaign &rarr;</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($campaigns as $campaign): ?>
                <?php
                    $media = getCampaignMedia($campaign['id']);
                    $thumb = !empty($media) ? SITE_URL . '/uploads/campaigns/' . $media[0]['file_path'] : '';
                    $pct = progressPercent($campaign['raised_amount'], $campaign['goal_amount']);
                ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col sm:flex-row">
                    <!-- Thumbnail -->
                    <div class="sm:w-48 h-40 sm:h-auto bg-gray-200 flex-shrink-0">
                        <?php if ($thumb): ?>
                            <img src="<?php echo h($thumb); ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Details -->
                    <div class="flex-1 p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800"><?php echo h($campaign['title']); ?></h3>
                                <p class="text-xs text-gray-400 mt-0.5">ID: <?php echo h($campaign['hex_id']); ?> &middot; <?php echo timeAgo($campaign['created_at']); ?></p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php echo $campaign['status'] === 'active' ? 'bg-green-100 text-green-800' : ($campaign['status'] === 'paused' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo ucfirst($campaign['status']); ?>
                            </span>
                        </div>

                        <!-- Progress -->
                        <div class="mb-3">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-green-600 font-semibold"><?php echo formatMoney($campaign['raised_amount']); ?></span>
                                <span class="text-gray-400">of <?php echo formatMoney($campaign['goal_amount']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full progress-bar" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="<?php echo SITE_URL; ?>/<?php echo h($campaign['hex_id']); ?>" target="_blank"
                               class="text-sm px-3 py-1.5 bg-brand-50 text-brand-700 rounded-lg hover:bg-brand-100 transition">View Page</a>
                            <a href="<?php echo SITE_URL; ?>/admin/campaign-edit.php?id=<?php echo $campaign['id']; ?>"
                               class="text-sm px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Edit</a>
                            <button onclick="deleteCampaign(<?php echo $campaign['id']; ?>)"
                                    class="text-sm px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteCampaign(id) {
    if (!confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) return;
    fetch('<?php echo SITE_URL; ?>/api/campaign-delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete campaign.');
        }
    })
    .catch(() => alert('An error occurred.'));
}
</script>
<style>.progress-bar { transition: width 1s ease-in-out; }</style>
</body>
</html>
