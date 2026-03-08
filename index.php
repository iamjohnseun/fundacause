<?php
/**
 * Homepage
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

define('PAGE_TITLE', SITE_NAME . ' - Help People Fund Their Causes');
define('PAGE_DESCRIPTION', 'Discover active fundraising campaigns, support urgent causes, and help people make meaningful change through FundACause.');

// Get active campaigns for homepage
$db = getDB();
$stmt = $db->query("SELECT * FROM campaigns WHERE status = 'active' ORDER BY created_at DESC LIMIT 12");
$campaigns = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 text-white">
    <div class="max-w-6xl mx-auto px-4 py-16 sm:py-24 text-center">
        <h1 class="text-3xl sm:text-5xl font-bold mb-4 leading-tight">Help People. Fund Causes.<br>Change Lives.</h1>
        <p class="text-lg sm:text-xl text-brand-100 mb-8 max-w-2xl mx-auto">
            A platform for anyone to raise funds and donate to causes that solve real-life issues.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?php echo SITE_URL; ?>/admin/login.php"
               class="bg-white text-brand-600 px-8 py-3.5 rounded-full font-bold text-lg hover:bg-gray-100 transition shadow-lg">
                Start a Campaign
            </a>
            <a href="<?php echo SITE_URL; ?>/mission"
               class="border-2 border-white/50 text-white px-8 py-3.5 rounded-full font-bold text-lg hover:bg-white/10 transition">
                Our Mission
            </a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 text-center mb-12">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-brand-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">1. Create a Campaign</h3>
                <p class="text-gray-500">Sign up, add photos, describe your cause, and set your fundraising goal.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">2. Share Your Link</h3>
                <p class="text-gray-500">Share your unique campaign link with friends, family, and social media.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">3. Receive Donations</h3>
                <p class="text-gray-500">Donors pay directly to your PayPal. Track progress in real-time.</p>
            </div>
        </div>
    </div>
</section>

<!-- Active Campaigns -->
<?php if (!empty($campaigns)): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 text-center mb-12">Active Campaigns</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($campaigns as $c):
                $media = getCampaignMedia($c['id']);
                $thumb = !empty($media) ? SITE_URL . '/uploads/campaigns/' . $media[0]['file_path'] : '';
                $pct = progressPercent($c['raised_amount'], $c['goal_amount']);
            ?>
                <a href="<?php echo SITE_URL; ?>/<?php echo h($c['hex_id']); ?>"
                   class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition group">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <?php if ($thumb): ?>
                            <img src="<?php echo h($thumb); ?>" alt="<?php echo h($c['title']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2"><?php echo h($c['title']); ?></h3>
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo $pct; ?>%"></div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600 font-semibold"><?php echo formatMoney($c['raised_amount']); ?></span>
                            <span class="text-gray-400">of <?php echo formatMoney($c['goal_amount']); ?></span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2"><?php echo number_format($c['supporter_count']); ?> supporters</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-16 bg-white">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4">Ready to Make a Difference?</h2>
        <p class="text-gray-500 mb-8">Start your campaign today and let the world help you achieve your goal.</p>
        <a href="<?php echo SITE_URL; ?>/admin/login.php"
           class="inline-block bg-brand-500 text-white px-8 py-3.5 rounded-full font-bold text-lg hover:bg-brand-600 transition shadow-lg">
            Start a Campaign
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
