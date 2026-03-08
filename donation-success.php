<?php
/**
 * Donation Success Page
 * User returns here after PayPal payment
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$hexId = $_GET['hex'] ?? '';
$campaign = null;

if (!empty($hexId)) {
    $campaign = getCampaignByHex($hexId);
}

define('PAGE_TITLE', 'Thank You - ' . SITE_NAME);
define('PAGE_DESCRIPTION', 'Thank you for donating on FundACause. Your support helps campaign creators move closer to their goals.');
include __DIR__ . '/includes/header.php';
?>

<main class="flex-1 flex items-center justify-center px-4 py-16">
    <div class="max-w-md w-full text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-3">Thank You!</h1>
        <p class="text-gray-500 mb-8">Your generous donation has been received. Together we can make a difference.</p>

        <?php if ($campaign): ?>
            <a href="<?php echo SITE_URL; ?>/<?php echo h($campaign['hex_id']); ?>"
               class="inline-block bg-brand-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-600 transition">
                Back to Campaign
            </a>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/"
               class="inline-block bg-brand-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-600 transition">
                Go Home
            </a>
        <?php endif; ?>
    </div>
</main>

<!-- Record donation via AJAX if data exists in session storage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const donationData = sessionStorage.getItem('donation_data');
    if (donationData) {
        const data = JSON.parse(donationData);
        data.transaction_id = 'paypal_' + Date.now(); // Placeholder, real ID comes from PayPal IPN

        fetch('<?php echo SITE_URL; ?>/api/donate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            console.log('Donation recorded:', res);
            sessionStorage.removeItem('donation_data');
        })
        .catch(err => console.error('Failed to record donation:', err));
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
